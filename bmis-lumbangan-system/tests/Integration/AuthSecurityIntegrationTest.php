<?php

if (!function_exists('integration_make_auth_dependencies')) {
    function integration_make_auth_dependencies(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts)
    {
        $logger = new LoginAttemptLogger($pdo, $clock);
        $rateLimits = new RateLimitService($pdo, $clock);
        $lockouts = new AccountLockoutService($pdo, $clock);
        $security = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

        return [$logger, $rateLimits, $lockouts, $security];
    }
}

if (!function_exists('integration_make_auth_controller')) {
    function integration_make_auth_controller(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts)
    {
        [, , , $security] = integration_make_auth_dependencies($pdo, $clock, $captcha, $alerts);

        return new AuthController([
            'db' => $pdo,
            'userModel' => new User($pdo),
            'officialModel' => new Official($pdo),
            'authSecurityService' => $security,
        ]);
    }
}

if (!function_exists('integration_make_password_reset_controller')) {
    function integration_make_password_reset_controller(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts, FakePasswordResetMailer $mailer)
    {
        [, , , $security] = integration_make_auth_dependencies($pdo, $clock, $captcha, $alerts);

        return new PasswordResetController([
            'db' => $pdo,
            'userModel' => new User($pdo),
            'resetModel' => new PasswordReset($pdo, $clock),
            'mailer' => $mailer,
            'authSecurityService' => $security,
            'clock' => $clock,
        ]);
    }
}

if (!function_exists('integration_login_attempt')) {
    function integration_login_attempt(AuthController $controller, $username, $password, $captchaToken = '')
    {
        test_apply_post_request([
            'username' => $username,
            'password' => $password,
            'captcha_token' => $captchaToken,
        ]);

        return test_capture_json(function () use ($controller) {
            $controller->login();
        });
    }
}

it('covers the full login failure to lockout to recovery lifecycle on mysql', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    $clock = new FakeClock('2026-04-12 08:00:00');
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    MysqlAuthTestDatabase::seedResidentUser($pdo);
    $controller = integration_make_auth_controller($pdo, $clock, $captcha, $alerts);

    for ($i = 0; $i < 4; $i++) {
        $response = integration_login_attempt($controller, 'resident', 'wrong-password');
        expect($response['json']['code'])->toBe('invalid_credentials');
    }

    $captchaRequired = integration_login_attempt($controller, 'resident', 'wrong-password');
    expect($captchaRequired['json']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $locked = integration_login_attempt($controller, 'resident', 'wrong-password', 'valid-captcha');
    expect($locked['json']['code'])->toBe('account_locked');

    $clock->travelMinutes(ACCOUNT_LOCKOUT_BASE_MINUTES + 1);
    $recovered = integration_login_attempt($controller, 'resident', 'secret123');

    expect($recovered['json']['success'])->toBeTrue();
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM login_attempts WHERE attempt_result = 'success'"))->toBe(1);
    expect((int) test_query_value($pdo, "SELECT consecutive_failures FROM account_lockouts WHERE username = 'resident'"))->toBe(0);
    expect(test_query_value($pdo, "SELECT locked_until FROM account_lockouts WHERE username = 'resident'"))->toBeNull();
});

it('covers the full password reset lifecycle on mysql with fake mail and captcha adapters', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    $clock = new FakeClock('2026-04-12 10:00:00');
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $mailer = new FakePasswordResetMailer();

    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);
    $controller = integration_make_password_reset_controller($pdo, $clock, $captcha, $alerts, $mailer);

    test_apply_post_request(['email' => $seed['email']]);
    $request = test_capture_json(function () use ($controller) {
        $controller->requestReset();
    });

    expect($request['json']['success'])->toBeTrue();
    expect(count($mailer->sent))->toBe(1);

    $resetCode = $mailer->sent[0]['code'];
    test_apply_post_request(['email' => $seed['email'], 'code' => $resetCode]);
    $verified = test_capture_json(function () use ($controller) {
        $controller->verifyCode();
    });

    expect($verified['json']['success'])->toBeTrue();
    expect($verified['json']['token'])->not->toBeEmpty();

    test_apply_post_request([
        'token' => $verified['json']['token'],
        'password' => 'newsecret123',
        'confirm_password' => 'newsecret123',
    ]);
    $reset = test_capture_json(function () use ($controller) {
        $controller->resetPassword();
    });

    expect($reset['json']['success'])->toBeTrue();

    $passwordHash = test_query_value($pdo, "SELECT password_hash FROM users WHERE username = 'resident'");
    expect(password_verify('newsecret123', $passwordHash))->toBeTrue();
    expect(test_query_value($pdo, "SELECT used_at FROM password_resets WHERE email = :email", [':email' => $seed['email']]))->not->toBeNull();

    test_apply_post_request([
        'token' => $verified['json']['token'],
        'password' => 'anothersecret123',
        'confirm_password' => 'anothersecret123',
    ]);
    $reused = test_capture_json(function () use ($controller) {
        $controller->resetPassword();
    });

    expect($reused['json']['code'])->toBe('invalid_reset_token');
});

it('removes expired security records without deleting active locks or valid reset tokens', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    $clock = new FakeClock('2026-04-12 12:00:00');
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);

    $pdo->exec("INSERT INTO ip_rate_limits (ip_address, attempt_count, window_start, last_attempt_at) VALUES ('1.1.1.1', 3, '2026-04-12 09:30:00', '2026-04-12 09:30:00')");
    $pdo->exec("INSERT INTO ip_rate_limits (ip_address, attempt_count, window_start, last_attempt_at) VALUES ('2.2.2.2', 3, '2026-04-12 11:30:00', '2026-04-12 11:30:00')");

    $pdo->exec("INSERT INTO login_attempts (username, ip_address, user_agent, attempt_result, failure_reason, geolocation_hint, attempted_at) VALUES ('login:resident', '1.1.1.1', 'Pest', 'failure', 'invalid_credentials', NULL, '2025-12-01 10:00:00')");
    $pdo->exec("INSERT INTO login_attempts (username, ip_address, user_agent, attempt_result, failure_reason, geolocation_hint, attempted_at) VALUES ('login:resident', '2.2.2.2', 'Pest', 'failure', 'invalid_credentials', NULL, '2026-04-12 11:50:00')");

    $pdo->exec("INSERT INTO brute_force_alerts (alert_type, target, attempt_count, alert_sent_at, email_sent, details) VALUES ('ip_threshold', '1.1.1.1', 10, '2025-12-01 10:00:00', 0, 'old')");
    $pdo->exec("INSERT INTO brute_force_alerts (alert_type, target, attempt_count, alert_sent_at, email_sent, details) VALUES ('ip_threshold', '2.2.2.2', 10, '2026-04-12 11:50:00', 0, 'recent')");

    $pdo->exec("INSERT INTO account_lockouts (username, consecutive_failures, lockout_count, locked_until, last_failure_at, created_at, updated_at) VALUES ('stale-user', 0, 1, NULL, '2025-12-01 10:00:00', '2025-12-01 10:00:00', '2025-12-01 10:00:00')");
    $pdo->exec("INSERT INTO account_lockouts (username, consecutive_failures, lockout_count, locked_until, last_failure_at, created_at, updated_at) VALUES ('active-user', 0, 1, '2026-04-12 12:30:00', '2026-04-12 11:59:00', '2026-04-12 11:59:00', '2026-04-12 11:59:00')");

    $pdo->exec("INSERT INTO password_resets (user_id, email, code, token, expires_at, used_at, created_at) VALUES ({$seed['user_id']}, 'used@example.com', '123456', 'used-token', '2026-04-12 11:00:00', '2026-04-12 11:05:00', '2026-04-12 10:00:00')");
    $pdo->exec("INSERT INTO password_resets (user_id, email, code, token, expires_at, used_at, created_at) VALUES ({$seed['user_id']}, 'valid@example.com', '654321', 'valid-token', '2026-04-12 13:00:00', NULL, '2026-04-12 11:00:00')");

    $cleanup = new SecurityCleanupService($pdo, $clock);
    $cleanup->cleanup();

    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM ip_rate_limits WHERE ip_address = '1.1.1.1'"))->toBe(0);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM ip_rate_limits WHERE ip_address = '2.2.2.2'"))->toBe(1);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM login_attempts WHERE attempted_at = '2025-12-01 10:00:00'"))->toBe(0);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM login_attempts WHERE attempted_at = '2026-04-12 11:50:00'"))->toBe(1);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM brute_force_alerts WHERE details = 'old'"))->toBe(0);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM brute_force_alerts WHERE details = 'recent'"))->toBe(1);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM account_lockouts WHERE username = 'stale-user'"))->toBe(0);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM account_lockouts WHERE username = 'active-user'"))->toBe(1);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM password_resets WHERE email = 'used@example.com'"))->toBe(0);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM password_resets WHERE email = 'valid@example.com'"))->toBe(1);
});
