<?php

it('does not increment the ip failure bucket on successful login recovery', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    $logger = new LoginAttemptLogger($pdo, $clock);
    $rateLimits = new RateLimitService($pdo, $clock);
    $lockouts = new AccountLockoutService($pdo, $clock);
    $service = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

    $context = AuthSecurityContext::forLogin('resident', '203.0.113.10', 'Pest', 'captcha-ok');

    $service->recordFailure($context, 'invalid_credentials');
    expect($rateLimits->checkIpRateLimit('203.0.113.10')['attempt_count'])->toBe(1);

    $service->recordSuccess($context);
    expect($rateLimits->checkIpRateLimit('203.0.113.10')['attempt_count'])->toBe(1);
    expect(test_query_value($pdo, "SELECT consecutive_failures FROM account_lockouts WHERE username = 'resident'"))->toBe(0);
});

it('applies lockout backoff and clears expired locks', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $lockouts = new AccountLockoutService($pdo, $clock);

    for ($i = 0; $i < ACCOUNT_LOCKOUT_THRESHOLD; $i++) {
        $state = $lockouts->recordFailure('resident');
    }

    expect($state['locked'])->toBeTrue();
    expect($state['retry_after'])->toBe(ACCOUNT_LOCKOUT_BASE_MINUTES * 60);
    expect($lockouts->isAccountLocked('resident')['locked'])->toBeTrue();

    $clock->travelMinutes(ACCOUNT_LOCKOUT_BASE_MINUTES + 1);
    expect($lockouts->isAccountLocked('resident')['locked'])->toBeFalse();

    for ($i = 0; $i < ACCOUNT_LOCKOUT_THRESHOLD; $i++) {
        $state = $lockouts->recordFailure('resident');
    }

    expect($state['locked'])->toBeTrue();
    expect($state['retry_after'])->toBe((ACCOUNT_LOCKOUT_BASE_MINUTES * ACCOUNT_LOCKOUT_BACKOFF_MULTIPLIER) * 60);
});

it('skips rate limiting for whitelisted ips', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $whitelistPath = dirname(__DIR__, 2) . '/app/config/ip_whitelist.php';
    $existingContents = file_exists($whitelistPath) ? file_get_contents($whitelistPath) : null;

    file_put_contents($whitelistPath, "<?php\nreturn ['10.10.10.10'];\n");

    try {
        $rateLimits = new RateLimitService($pdo, $clock);
        for ($i = 0; $i < 10; $i++) {
            $rateLimits->recordFailure('10.10.10.10');
        }

        $state = $rateLimits->checkIpRateLimit('10.10.10.10');
        expect($state['blocked'])->toBeFalse();
        expect($state['attempt_count'])->toBe(0);
        expect(test_query_value($pdo, "SELECT COUNT(*) FROM ip_rate_limits WHERE ip_address = '10.10.10.10'"))->toBe(0);
    } finally {
        if ($existingContents !== null) {
            file_put_contents($whitelistPath, $existingContents);
        } elseif (file_exists($whitelistPath)) {
            unlink($whitelistPath);
        }
    }
});

it('respects duplicate alert cooldowns', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $logger = new LoginAttemptLogger($pdo, $clock);
    $alerts = new AdminAlertService($pdo, $clock);

    for ($i = 0; $i < ADMIN_ALERT_THRESHOLD_IP; $i++) {
        $logger->logAttempt('resident', '127.0.0.1', 'Pest', 'failure', 'invalid_credentials', 'login');
    }

    $alerts->evaluateAndSend('127.0.0.1', 'login:resident', 'login', $logger, false);
    $alerts->evaluateAndSend('127.0.0.1', 'login:resident', 'login', $logger, false);

    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM brute_force_alerts WHERE alert_type = 'ip_threshold'"))->toBe(1);

    $clock->travelMinutes(61);
    for ($i = 0; $i < ADMIN_ALERT_THRESHOLD_IP; $i++) {
        $logger->logAttempt('resident', '127.0.0.1', 'Pest', 'failure', 'invalid_credentials', 'login');
    }

    $alerts->evaluateAndSend('127.0.0.1', 'login:resident', 'login', $logger, false);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM brute_force_alerts WHERE alert_type = 'ip_threshold'"))->toBe(2);
});

it('requires captcha after suspicious login failures and accepts valid captcha on retry', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    $logger = new LoginAttemptLogger($pdo, $clock);
    $rateLimits = new RateLimitService($pdo, $clock);
    $lockouts = new AccountLockoutService($pdo, $clock);
    $service = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

    $failureContext = AuthSecurityContext::forLogin('resident', '127.0.0.1', 'Pest', '');
    for ($i = 0; $i < CAPTCHA_TRIGGER_THRESHOLD; $i++) {
        $service->recordFailure($failureContext, 'invalid_credentials');
    }

    $guard = $service->guard(AuthSecurityContext::forLogin('resident', '127.0.0.1', 'Pest', ''));
    expect($guard['allowed'])->toBeFalse();
    expect($guard['response']['code'])->toBe('captcha_required');
    expect($logger->countRecentFailuresByIdentifier('resident', ACCOUNT_RATE_LIMIT_WINDOW_MINUTES, 'login'))
        ->toBe(CAPTCHA_TRIGGER_THRESHOLD);

    $captcha->setTokenResult('valid-captcha', true);
    $guard = $service->guard(AuthSecurityContext::forLogin('resident', '127.0.0.1', 'Pest', 'valid-captcha'));
    expect($guard['allowed'])->toBeTrue();
});

it('uses accurate failure counts in admin lockout alerts', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $logger = new LoginAttemptLogger($pdo, $clock);
    $alerts = new AdminAlertService($pdo, $clock);

    for ($i = 0; $i < ACCOUNT_LOCKOUT_THRESHOLD; $i++) {
        $logger->logAttempt('resident', '127.0.0.1', 'Pest', 'failure', 'invalid_credentials', 'login');
    }

    $alerts->evaluateAndSend('127.0.0.1', 'resident', 'login', $logger, true);

    expect((int) test_query_value($pdo, "SELECT attempt_count FROM brute_force_alerts WHERE alert_type = 'account_lockout' ORDER BY id DESC LIMIT 1"))
        ->toBe(ACCOUNT_LOCKOUT_THRESHOLD);
    expect((string) test_query_value($pdo, "SELECT details FROM brute_force_alerts WHERE alert_type = 'account_lockout' ORDER BY id DESC LIMIT 1"))
        ->toContain((string) ACCOUNT_LOCKOUT_THRESHOLD);
});

it('throttles repeated password reset requests without depending on account existence', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    $logger = new LoginAttemptLogger($pdo, $clock);
    $rateLimits = new RateLimitService($pdo, $clock);
    $lockouts = new AccountLockoutService($pdo, $clock);
    $service = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

    $context = AuthSecurityContext::forPasswordResetRequest('unknown@example.com', '127.0.0.1', 'Pest', '');

    $service->recordSuccess($context);
    $service->recordSuccess($context);

    $guard = $service->guard($context);
    expect($guard['allowed'])->toBeFalse();
    expect($guard['response']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $guard = $service->guard(AuthSecurityContext::forPasswordResetRequest('unknown@example.com', '127.0.0.1', 'Pest', 'valid-captcha'));
    expect($guard['allowed'])->toBeTrue();

    $service->recordSuccess(AuthSecurityContext::forPasswordResetRequest('unknown@example.com', '127.0.0.1', 'Pest', 'valid-captcha'));
    $guard = $service->guard(AuthSecurityContext::forPasswordResetRequest('unknown@example.com', '127.0.0.1', 'Pest', 'valid-captcha'));
    expect($guard['allowed'])->toBeFalse();
    expect($guard['response']['code'])->toBe('rate_limit_exceeded');
});

it('requires captcha on every registration request and shares limits between send and resend', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    $logger = new LoginAttemptLogger($pdo, $clock);
    $rateLimits = new RateLimitService($pdo, $clock);
    $lockouts = new AccountLockoutService($pdo, $clock);
    $service = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

    $requestContext = AuthSecurityContext::forRegistrationRequest('resident@example.com', '127.0.0.1', 'Pest', '');
    $guard = $service->guard($requestContext);
    expect($guard['allowed'])->toBeFalse();
    expect($guard['response']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $allowedContext = AuthSecurityContext::forRegistrationRequest('resident@example.com', '127.0.0.1', 'Pest', 'valid-captcha');
    expect($service->guard($allowedContext)['allowed'])->toBeTrue();

    $service->recordSuccess($allowedContext);
    $service->recordSuccess($allowedContext);
    $service->recordSuccess($allowedContext);

    $nextAttempt = $service->guard($allowedContext);
    expect($nextAttempt['allowed'])->toBeFalse();
    expect($nextAttempt['response']['code'])->toBe('rate_limit_exceeded');
});

it('resets registration verification captcha pressure after a successful verification', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    $logger = new LoginAttemptLogger($pdo, $clock);
    $rateLimits = new RateLimitService($pdo, $clock);
    $lockouts = new AccountLockoutService($pdo, $clock);
    $service = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

    $context = AuthSecurityContext::forRegistrationVerify('resident@example.com', '127.0.0.1', 'Pest', '');
    for ($i = 0; $i < REGISTRATION_VERIFY_CAPTCHA_TRIGGER_THRESHOLD; $i++) {
        $service->recordFailure($context, 'invalid_verification_code');
    }

    $guard = $service->guard($context);
    expect($guard['allowed'])->toBeFalse();
    expect($guard['response']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $captchaContext = AuthSecurityContext::forRegistrationVerify('resident@example.com', '127.0.0.1', 'Pest', 'valid-captcha');
    expect($service->guard($captchaContext)['allowed'])->toBeTrue();

    $service->recordSuccess($captchaContext);

    $afterSuccess = $service->guard(AuthSecurityContext::forRegistrationVerify('resident@example.com', '127.0.0.1', 'Pest', ''));
    expect($afterSuccess['allowed'])->toBeTrue();
});
