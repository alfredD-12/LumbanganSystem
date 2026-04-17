<?php

/*
 * Browser-style coverage for camera access, blink detection, head-turn detection,
 * and inline/modal interaction is intentionally deferred because this repo does
 * not currently include a browser automation stack. These feature regressions
 * protect adjacent auth behavior around the current facial registration flow.
 */

if (!function_exists('facial_feature_make_auth_dependencies')) {
    function facial_feature_make_auth_dependencies(PDO $pdo, FakeClock $clock, FakeCaptchaVerifier $captcha, FakeSecurityAlertService $alerts)
    {
        $logger = new LoginAttemptLogger($pdo, $clock);
        $rateLimits = new RateLimitService($pdo, $clock);
        $lockouts = new AccountLockoutService($pdo, $clock);
        $security = new AuthSecurityService($pdo, $logger, $rateLimits, $lockouts, $alerts, $captcha, $clock);

        return [$logger, $rateLimits, $lockouts, $security];
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

function facial_auth_make_controller(PDO $pdo)
{
    $clock = new FakeClock('2026-04-12 10:00:00');
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $mailSender = new FakeRegistrationMailSender();
    $smsSender = new FakeRegistrationSmsSender();

    $captcha->setTokenResult('valid-captcha', true);

    [, , , $security] = facial_feature_make_auth_dependencies($pdo, $clock, $captcha, $alerts);

    $controller = new EmailVerificationController([
        'db' => $pdo,
        'verificationModel' => new EmailVerification($pdo, $clock),
        'userModel' => new User($pdo),
        'authSecurityService' => $security,
        'mailSender' => $mailSender,
        'smsSender' => $smsSender,
        'clock' => $clock,
    ]);

    return [$controller, $clock, $captcha, $alerts, $mailSender, $smsSender];
}

function facial_auth_registration_payload($username = 'featureface', $email = 'featureface@example.com')
{
    return [
        'username' => $username,
        'email' => $email,
        'password' => 'secret123',
        'confirm_password' => 'secret123',
        'first_name' => 'Feature',
        'last_name' => 'Face',
    ];
}

function facial_auth_embedding($seed = 0.15)
{
    $embedding = [];

    for ($i = 0; $i < 128; $i++) {
        $embedding[] = round(((float) $seed) + ($i * 0.00125), 6);
    }

    return $embedding;
}

it('preserves standard username password login behavior after facial verification changes', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);

    $response = test_capture_front_controller_action_with_csrf('login', [
        'username' => $seed['username'],
        'password' => $seed['password'],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect($_SESSION['logged_in'])->toBeTrue();
    expect($_SESSION['user_type'])->toBe('user');
    expect((int) test_query_value($pdo, 'SELECT face_enrolled FROM users WHERE id = :id', [
        ':id' => (int) $seed['user_id'],
    ]))->toBe(0);
});

it('keeps the signup pipeline working after successful facial verification handoff', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_auth_make_controller($pdo);
    $payload = facial_auth_registration_payload('featurehandoff', 'featurehandoff@example.com');

    $sent = feature_send_verification_code($controller, $payload, 'valid-captcha');
    expect($sent['json']['success'])->toBeTrue();

    $code = test_query_value($pdo, 'SELECT code FROM email_verifications WHERE email = :email', [
        ':email' => $sent['json']['target'],
    ]);
    $verified = feature_verify_registration_code($controller, $sent['json']['target'], $code);

    expect($verified['json']['success'])->toBeTrue();

    $completed = feature_complete_registration($controller, $verified['json']['token'], [
        'face_embedding' => json_encode(facial_auth_embedding(0.24)),
    ]);

    expect($completed['json']['success'])->toBeTrue();
    expect($completed['json']['code'])->toBe('registration_completed');
    expect($completed['json']['redirect'])->toContain('dashboard_resident');
    expect($_SESSION['logged_in'])->toBeTrue();
    expect($_SESSION['username'])->toBe('featurehandoff');
    expect((int) test_query_value($pdo, "SELECT face_enrolled FROM users WHERE username = 'featurehandoff'"))->toBe(1);
});

it('does not let invalid face payload mark a user as enrolled while still completing signup', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_auth_make_controller($pdo);
    $payload = facial_auth_registration_payload('featureinvalidface', 'featureinvalidface@example.com');

    $sent = feature_send_verification_code($controller, $payload, 'valid-captcha');
    expect($sent['json']['success'])->toBeTrue();

    $code = test_query_value($pdo, 'SELECT code FROM email_verifications WHERE email = :email', [
        ':email' => $sent['json']['target'],
    ]);
    $verified = feature_verify_registration_code($controller, $sent['json']['target'], $code);
    expect($verified['json']['success'])->toBeTrue();

    $completed = feature_complete_registration($controller, $verified['json']['token'], [
        'face_embedding' => json_encode([0.1, 0.2]),
        'face_image_b64' => 'data:image/jpeg;base64,' . base64_encode('invalid-face-should-not-enroll'),
    ]);

    expect($completed['json']['success'])->toBeTrue();
    expect($_SESSION['logged_in'])->toBeTrue();

    $user = $pdo->query("SELECT face_embedding, face_image_path, face_verified_at, face_enrolled FROM users WHERE username = 'featureinvalidface'")
        ->fetch(PDO::FETCH_ASSOC);

    expect($user['face_embedding'])->toBeNull();
    expect($user['face_image_path'])->toBeNull();
    expect($user['face_verified_at'])->toBeNull();
    expect((int) $user['face_enrolled'])->toBe(0);
});
