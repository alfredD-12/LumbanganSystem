<?php

if (!function_exists('feature_make_auth_dependencies')) {
    function feature_make_auth_dependencies(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts)
    {
        $logger = new LoginAttemptLogger($pdo, $clock);
        $rateLimits = new RateLimitService($pdo, $clock);
        $lockouts = new AccountLockoutService($pdo, $clock);
        $security = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

        return [$logger, $rateLimits, $lockouts, $security];
    }
}

if (!function_exists('feature_make_auth_controller')) {
    function feature_make_auth_controller(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts)
    {
        [, , , $security] = feature_make_auth_dependencies($pdo, $clock, $captcha, $alerts);

        return new AuthController([
            'db' => $pdo,
            'userModel' => new User($pdo),
            'officialModel' => new Official($pdo),
            'authSecurityService' => $security,
        ]);
    }
}

if (!function_exists('feature_make_password_reset_controller')) {
    function feature_make_password_reset_controller(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts, FakePasswordResetMailer $mailer)
    {
        [, , , $security] = feature_make_auth_dependencies($pdo, $clock, $captcha, $alerts);

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

if (!function_exists('feature_make_email_verification_controller')) {
    function feature_make_email_verification_controller(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts, FakeRegistrationMailSender $mailSender, FakeRegistrationSmsSender $smsSender)
    {
        [, , , $security] = feature_make_auth_dependencies($pdo, $clock, $captcha, $alerts);

        return new EmailVerificationController([
            'db' => $pdo,
            'verificationModel' => new EmailVerification($pdo, $clock),
            'userModel' => new User($pdo),
            'authSecurityService' => $security,
            'mailSender' => $mailSender,
            'smsSender' => $smsSender,
            'clock' => $clock,
        ]);
    }
}

if (!function_exists('feature_login_attempt')) {
    function feature_login_attempt(AuthController $controller, $username, $password, $captchaToken = '')
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

if (!function_exists('feature_request_reset')) {
    function feature_request_reset(PasswordResetController $controller, $email, $captchaToken = '')
    {
        test_apply_post_request([
            'email' => $email,
            'captcha_token' => $captchaToken,
        ]);

        return test_capture_json(function () use ($controller) {
            $controller->requestReset();
        });
    }
}

if (!function_exists('feature_verify_code')) {
    function feature_verify_code(PasswordResetController $controller, $email, $code, $captchaToken = '', $validCsrf = true)
    {
        if ($validCsrf) {
            test_apply_post_request([
                'email' => $email,
                'code' => $code,
                'captcha_token' => $captchaToken,
            ]);
        } else {
            test_reset_http_state();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'Pest';
            $_POST = [
                'email' => $email,
                'code' => $code,
                'csrf_token' => 'invalid-token',
                'captcha_token' => $captchaToken,
            ];
            test_seed_csrf_token('valid-token');
        }

        return test_capture_json(function () use ($controller) {
            $controller->verifyCode();
        });
    }
}

if (!function_exists('feature_reset_password')) {
    function feature_reset_password(PasswordResetController $controller, $token, $password, $confirmPassword, $captchaToken = '', $validCsrf = true)
    {
        if ($validCsrf) {
            test_apply_post_request([
                'token' => $token,
                'password' => $password,
                'confirm_password' => $confirmPassword,
                'captcha_token' => $captchaToken,
            ]);
        } else {
            test_reset_http_state();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'Pest';
            $_POST = [
                'token' => $token,
                'password' => $password,
                'confirm_password' => $confirmPassword,
                'csrf_token' => 'invalid-token',
                'captcha_token' => $captchaToken,
            ];
            test_seed_csrf_token('valid-token');
        }

        return test_capture_json(function () use ($controller) {
            $controller->resetPassword();
        });
    }
}

if (!function_exists('feature_send_verification_code')) {
    function feature_send_verification_code(EmailVerificationController $controller, array $payload, $captchaToken = '', $validCsrf = true)
    {
        if ($validCsrf) {
            test_apply_post_request(array_merge($payload, ['captcha_token' => $captchaToken]));
        } else {
            test_reset_http_state();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'Pest';
            $_POST = array_merge($payload, [
                'csrf_token' => 'invalid-token',
                'captcha_token' => $captchaToken,
            ]);
            test_seed_csrf_token('valid-token');
        }

        return test_capture_json(function () use ($controller) {
            $controller->sendVerificationCode();
        });
    }
}

if (!function_exists('feature_resend_verification_code')) {
    function feature_resend_verification_code(EmailVerificationController $controller, $identifier, $captchaToken = '', $validCsrf = true)
    {
        $payload = [
            'verification_target' => $identifier,
            'email' => $identifier,
            'captcha_token' => $captchaToken,
        ];

        if ($validCsrf) {
            test_apply_post_request($payload);
        } else {
            test_reset_http_state();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'Pest';
            $_POST = array_merge($payload, ['csrf_token' => 'invalid-token']);
            test_seed_csrf_token('valid-token');
        }

        return test_capture_json(function () use ($controller) {
            $controller->resendCode();
        });
    }
}

if (!function_exists('feature_verify_registration_code')) {
    function feature_verify_registration_code(EmailVerificationController $controller, $identifier, $code, $captchaToken = '', $validCsrf = true)
    {
        $payload = [
            'verification_target' => $identifier,
            'email' => $identifier,
            'code' => $code,
            'captcha_token' => $captchaToken,
        ];

        if ($validCsrf) {
            test_apply_post_request($payload);
        } else {
            test_reset_http_state();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'Pest';
            $_POST = array_merge($payload, ['csrf_token' => 'invalid-token']);
            test_seed_csrf_token('valid-token');
        }

        return test_capture_json(function () use ($controller) {
            $controller->verifyCode();
        });
    }
}

if (!function_exists('feature_complete_registration')) {
    function feature_complete_registration(EmailVerificationController $controller, $token, array $extra = [], $validCsrf = true)
    {
        $payload = array_merge(['token' => $token], $extra);

        if ($validCsrf) {
            test_apply_post_request($payload);
        } else {
            test_reset_http_state();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'Pest';
            $_POST = array_merge($payload, ['csrf_token' => 'invalid-token']);
            test_seed_csrf_token('valid-token');
        }

        return test_capture_json(function () use ($controller) {
            $controller->completeRegistration();
        });
    }
}

it('creates session state, logs success, clears lockout state, and avoids poisoning the ip bucket on valid logins', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    SqliteAuthTestDatabase::seedResidentUser($pdo);
    $pdo->exec("INSERT INTO account_lockouts (username, consecutive_failures, lockout_count, last_failure_at, created_at, updated_at) VALUES ('resident', 3, 0, '2026-04-12 09:00:00', '2026-04-12 09:00:00', '2026-04-12 09:00:00')");

    $controller = feature_make_auth_controller($pdo, $clock, $captcha, $alerts);

    $first = feature_login_attempt($controller, 'resident', 'secret123');
    $second = feature_login_attempt($controller, 'resident', 'secret123');

    expect($first['json']['success'])->toBeTrue();
    expect($second['json']['success'])->toBeTrue();
    expect($_SESSION['logged_in'])->toBeTrue();
    expect($_SESSION['user_type'])->toBe('user');
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM ip_rate_limits"))->toBe(0);
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM login_attempts WHERE attempt_result = 'success'"))->toBe(2);
    expect((int) test_query_value($pdo, "SELECT consecutive_failures FROM account_lockouts WHERE username = 'resident'"))->toBe(0);
});

it('requires captcha after three failed logins and allows a valid captcha plus correct password to recover immediately', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    SqliteAuthTestDatabase::seedResidentUser($pdo);
    $controller = feature_make_auth_controller($pdo, $clock, $captcha, $alerts);

    $responses = [];
    for ($i = 0; $i < CAPTCHA_TRIGGER_THRESHOLD; $i++) {
        $responses[] = feature_login_attempt($controller, 'resident', 'wrong-password');
    }

    expect($responses[0]['json']['code'])->toBe('invalid_credentials');
    expect($responses[CAPTCHA_TRIGGER_THRESHOLD - 1]['json']['code'])->toBe('invalid_credentials');

    $captchaRequired = feature_login_attempt($controller, 'resident', 'secret123');
    expect($captchaRequired['json']['code'])->toBe('captcha_required');
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM login_attempts WHERE failure_reason = 'captcha_failed'"))->toBe(0);

    $captcha->setTokenResult('valid-captcha', true);
    $recovered = feature_login_attempt($controller, 'resident', 'secret123', 'valid-captcha');

    expect($recovered['json']['success'])->toBeTrue();
    expect($_SESSION['logged_in'])->toBeTrue();
});

it('continues the brute-force counter only after captcha is solved and the password is still wrong', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    SqliteAuthTestDatabase::seedResidentUser($pdo);
    $controller = feature_make_auth_controller($pdo, $clock, $captcha, $alerts);

    for ($i = 0; $i < CAPTCHA_TRIGGER_THRESHOLD; $i++) {
        $response = feature_login_attempt($controller, 'resident', 'wrong-password');
        expect($response['json']['code'])->toBe('invalid_credentials');
    }

    $captchaRequired = feature_login_attempt($controller, 'resident', 'wrong-password');
    expect($captchaRequired['json']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $fourthFailure = feature_login_attempt($controller, 'resident', 'wrong-password', 'valid-captcha');
    expect($fourthFailure['json']['code'])->toBe('invalid_credentials');
    expect($fourthFailure['json']['attempts_remaining'])->toBe(1);

    $locked = feature_login_attempt($controller, 'resident', 'wrong-password', 'valid-captcha');
    expect($locked['json']['code'])->toBe('account_locked');
    expect($locked['json']['retry_after'])->toBeGreaterThan(0);
});

it('returns a generic message for disabled accounts while recording the right failure reason', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();

    SqliteAuthTestDatabase::seedResidentUser($pdo, ['user' => ['status' => 'disabled']]);
    $controller = feature_make_auth_controller($pdo, $clock, $captcha, $alerts);

    $response = feature_login_attempt($controller, 'resident', 'secret123');

    expect($response['json']['success'])->toBeFalse();
    expect($response['json']['code'])->toBe('invalid_credentials');
    expect($response['json']['message'])->toBe('Unable to sign in with the provided credentials.');
    expect(test_query_value($pdo, "SELECT failure_reason FROM login_attempts ORDER BY id DESC LIMIT 1"))->toBe('account_disabled');
});

it('keeps password reset requests generic while escalating to captcha and throttling abusive repetition', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $mailer = new FakePasswordResetMailer();

    SqliteAuthTestDatabase::seedResidentUser($pdo);
    $controller = feature_make_password_reset_controller($pdo, $clock, $captcha, $alerts, $mailer);

    $first = feature_request_reset($controller, 'resident@example.com');
    $second = feature_request_reset($controller, 'resident@example.com');
    $third = feature_request_reset($controller, 'resident@example.com');

    expect($first['json']['success'])->toBeTrue();
    expect($first['json']['message'])->toBe('If the email exists, a reset code will be sent.');
    expect($second['json']['success'])->toBeTrue();
    expect($third['json']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $withCaptcha = feature_request_reset($controller, 'resident@example.com', 'valid-captcha');
    expect($withCaptcha['json']['success'])->toBeTrue();

    $rateLimited = feature_request_reset($controller, 'resident@example.com', 'valid-captcha');
    expect($rateLimited['json']['code'])->toBe('rate_limit_exceeded');
    expect(count($mailer->sent))->toBe(3);
});

it('honors csrf, captcha escalation, and token invalidation across verify and reset steps', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $mailer = new FakePasswordResetMailer();

    $seed = SqliteAuthTestDatabase::seedResidentUser($pdo);
    $resetModel = new PasswordReset($pdo, $clock);
    $tokenData = $resetModel->createToken($seed['user_id'], $seed['email']);

    $controller = feature_make_password_reset_controller($pdo, $clock, $captcha, $alerts, $mailer);

    $csrfFailure = feature_verify_code($controller, $seed['email'], $tokenData['code'], '', false);
    expect($csrfFailure['json']['code'])->toBe('invalid_csrf');

    for ($i = 0; $i < PASSWORD_RESET_VERIFY_CAPTCHA_TRIGGER_THRESHOLD; $i++) {
        $invalid = feature_verify_code($controller, $seed['email'], '000000');
    }
    expect($invalid['json']['code'])->toBe('invalid_reset_code');

    $captchaRequired = feature_verify_code($controller, $seed['email'], $tokenData['code']);
    expect($captchaRequired['json']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $verified = feature_verify_code($controller, $seed['email'], $tokenData['code'], 'valid-captcha');
    expect($verified['json']['success'])->toBeTrue();

    $resetCsrfFailure = feature_reset_password($controller, $verified['json']['token'], 'newsecret123', 'newsecret123', '', false);
    expect($resetCsrfFailure['json']['code'])->toBe('invalid_csrf');

    $resetSuccess = feature_reset_password($controller, $verified['json']['token'], 'newsecret123', 'newsecret123');
    expect($resetSuccess['json']['success'])->toBeTrue();

    $reused = feature_reset_password($controller, $verified['json']['token'], 'anothersecret123', 'anothersecret123');
    expect($reused['json']['code'])->toBe('invalid_reset_token');
});

it('requires captcha for registration send and resend, escalates verify, and completes signup through email verification', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $mailSender = new FakeRegistrationMailSender();
    $smsSender = new FakeRegistrationSmsSender();

    $controller = feature_make_email_verification_controller($pdo, $clock, $captcha, $alerts, $mailSender, $smsSender);
    $payload = [
        'username' => 'newresident',
        'email' => 'newresident@example.com',
        'password' => 'secret123',
        'confirm_password' => 'secret123',
        'first_name' => 'New',
        'last_name' => 'Resident',
    ];

    $csrfFailure = feature_send_verification_code($controller, $payload, '', false);
    expect($csrfFailure['json']['code'])->toBe('invalid_csrf');

    $captchaRequired = feature_send_verification_code($controller, $payload);
    expect($captchaRequired['json']['code'])->toBe('captcha_required');

    $captcha->setTokenResult('valid-captcha', true);
    $sent = feature_send_verification_code($controller, $payload, 'valid-captcha');
    expect($sent['json']['success'])->toBeTrue();
    expect($sent['json']['code'])->toBe('verification_sent');
    expect(count($mailSender->sent))->toBe(1);

    $identifier = $sent['json']['target'];
    $verificationCode = test_query_value($pdo, "SELECT code FROM email_verifications WHERE email = :identifier", [':identifier' => $identifier]);

    $resendCaptchaRequired = feature_resend_verification_code($controller, $identifier);
    expect($resendCaptchaRequired['json']['code'])->toBe('captcha_required');

    $resent = feature_resend_verification_code($controller, $identifier, 'valid-captcha');
    expect($resent['json']['success'])->toBeTrue();
    expect($resent['json']['code'])->toBe('verification_resent');
    expect(count($mailSender->sent))->toBe(2);

    $latestCode = test_query_value($pdo, "SELECT code FROM email_verifications WHERE email = :identifier", [':identifier' => $identifier]);

    for ($i = 0; $i < REGISTRATION_VERIFY_CAPTCHA_TRIGGER_THRESHOLD; $i++) {
        $invalid = feature_verify_registration_code($controller, $identifier, '000000');
    }
    expect($invalid['json']['code'])->toBe('invalid_verification_code');

    $verifyCaptchaRequired = feature_verify_registration_code($controller, $identifier, $latestCode);
    expect($verifyCaptchaRequired['json']['code'])->toBe('captcha_required');

    $verified = feature_verify_registration_code($controller, $identifier, $latestCode, 'valid-captcha');
    expect($verified['json']['success'])->toBeTrue();
    expect($verified['json']['token'])->not->toBeEmpty();

    $completed = feature_complete_registration($controller, $verified['json']['token']);
    expect($completed['json']['success'])->toBeTrue();
    expect($_SESSION['logged_in'])->toBeTrue();
    expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM users WHERE username = 'newresident'"))->toBe(1);
    expect(test_query_value($pdo, "SELECT verified_at FROM email_verifications WHERE email = :identifier", [':identifier' => $identifier]))->not->toBeNull();
});

it('disables the legacy direct register endpoint', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $controller = feature_make_auth_controller($pdo, $clock, $captcha, $alerts);

    test_apply_post_request([
        'username' => 'legacy',
        'email' => 'legacy@example.com',
    ]);

    $response = test_capture_json(function () use ($controller) {
        $controller->register();
    });

    expect($response['status'])->toBe(410);
    expect($response['json']['code'])->toBe('legacy_route_disabled');
});

it('requires post plus csrf for logout and clears the session on success', function () {
    $pdo = SqliteAuthTestDatabase::createPdo();
    $clock = new FakeClock();
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $controller = feature_make_auth_controller($pdo, $clock, $captcha, $alerts);

    test_reset_http_state();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $invalidMethod = test_capture_json(function () use ($controller) {
        $controller->logout();
    });

    expect($invalidMethod['status'])->toBe(405);
    expect($invalidMethod['json']['code'])->toBe('invalid_request_method');

    test_reset_http_state();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'resident';
    $_POST = [csrf_field_name() => 'wrong-token'];
    test_seed_csrf_token('valid-token');

    $invalidCsrf = test_capture_json(function () use ($controller) {
        $controller->logout();
    });

    expect($invalidCsrf['status'])->toBe(403);
    expect($invalidCsrf['json']['code'])->toBe('invalid_csrf');

    test_reset_http_state();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'resident';
    $token = test_seed_csrf_token('logout-token');
    $_POST = [csrf_field_name() => $token];

    $success = test_capture_json(function () use ($controller) {
        $controller->logout();
    });

    expect($success['json']['success'])->toBeTrue();
    expect($success['json']['code'])->toBe('logged_out');
    expect($_SESSION)->toBeEmpty();
});
