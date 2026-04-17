<?php

/*
 * Browser-style coverage for camera access, blink detection, head-turn detection,
 * and inline/modal interaction is intentionally deferred because this repo does
 * not currently include a browser automation stack. These tests focus on the
 * DB-backed registration persistence rules behind the existing facial flow.
 */

if (!function_exists('integration_send_verification_code')) {
    function integration_send_verification_code(EmailVerificationController $controller, array $payload, $captchaToken = '')
    {
        test_apply_post_request(array_merge($payload, ['captcha_token' => $captchaToken]));

        return test_capture_json(function () use ($controller) {
            $controller->sendVerificationCode();
        });
    }
}

if (!function_exists('integration_verify_registration_code')) {
    function integration_verify_registration_code(EmailVerificationController $controller, $identifier, $code, $captchaToken = '')
    {
        test_apply_post_request([
            'verification_target' => $identifier,
            'email' => $identifier,
            'code' => $code,
            'captcha_token' => $captchaToken,
        ]);

        return test_capture_json(function () use ($controller) {
            $controller->verifyCode();
        });
    }
}

if (!function_exists('integration_complete_registration')) {
    function integration_complete_registration(EmailVerificationController $controller, $token, array $extra = [])
    {
        test_apply_post_request(array_merge(['token' => $token], $extra));

        return test_capture_json(function () use ($controller) {
            $controller->completeRegistration();
        });
    }
}

function facial_registration_make_dependencies(PDO $pdo)
{
    $clock = new FakeClock('2026-04-12 10:00:00');
    $captcha = new FakeCaptchaVerifier();
    $alerts = new FakeSecurityAlertService();
    $mailSender = new FakeRegistrationMailSender();
    $smsSender = new FakeRegistrationSmsSender();

    $captcha->setTokenResult('valid-captcha', true);

    $controller = new EmailVerificationController([
        'db' => $pdo,
        'verificationModel' => new EmailVerification($pdo, $clock),
        'userModel' => new User($pdo),
        'authSecurityService' => new AuthSecurityService(
            $pdo,
            new LoginAttemptLogger($pdo, $clock),
            new RateLimitService($pdo, $clock),
            new AccountLockoutService($pdo, $clock),
            $alerts,
            $captcha,
            $clock
        ),
        'mailSender' => $mailSender,
        'smsSender' => $smsSender,
        'clock' => $clock,
    ]);

    return [$controller, $clock, $captcha, $alerts, $mailSender, $smsSender];
}

function facial_registration_payload($username = 'facialresident', $email = 'facialresident@example.com')
{
    return [
        'username' => $username,
        'email' => $email,
        'password' => 'secret123',
        'confirm_password' => 'secret123',
        'first_name' => 'Facial',
        'last_name' => 'Resident',
    ];
}

function facial_registration_embedding($seed = 0.1)
{
    $embedding = [];

    for ($i = 0; $i < 128; $i++) {
        $embedding[] = round(((float) $seed) + ($i * 0.00175), 6);
    }

    return $embedding;
}

function facial_registration_face_upload_dir()
{
    return dirname(__DIR__, 2) . '/app/uploads/faces';
}

function facial_registration_snapshot_face_uploads()
{
    $directory = facial_registration_face_upload_dir();
    if (!is_dir($directory)) {
        return [];
    }

    $items = scandir($directory);
    if (!is_array($items)) {
        return [];
    }

    return array_values(array_filter($items, function ($item) {
        return $item !== '.' && $item !== '..';
    }));
}

function facial_registration_cleanup_face_uploads(array $before)
{
    $directory = facial_registration_face_upload_dir();
    if (!is_dir($directory)) {
        return;
    }

    $existing = facial_registration_snapshot_face_uploads();
    $newFiles = array_diff($existing, $before);

    foreach ($newFiles as $file) {
        $path = $directory . DIRECTORY_SEPARATOR . $file;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

function facial_registration_complete_with_face(EmailVerificationController $controller, PDO $pdo, array $payload, array $extra = [])
{
    $sent = integration_send_verification_code($controller, $payload, 'valid-captcha');
    expect($sent['json']['success'])->toBeTrue();

    $identifier = $sent['json']['target'];
    $code = test_query_value($pdo, 'SELECT code FROM email_verifications WHERE email = :email', [
        ':email' => $identifier,
    ]);

    $verified = integration_verify_registration_code($controller, $identifier, $code);
    expect($verified['json']['success'])->toBeTrue();

    return integration_complete_registration($controller, $verified['json']['token'], $extra);
}

it('stores face enrollment fields during successful registration with valid face embedding', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_registration_make_dependencies($pdo);
    $beforeUploads = facial_registration_snapshot_face_uploads();

    try {
        $payload = facial_registration_payload('facevalid', 'facevalid@example.com');
        $embedding = facial_registration_embedding(0.11);

        $completed = facial_registration_complete_with_face($controller, $pdo, $payload, [
            'face_embedding' => json_encode($embedding),
        ]);

        expect($completed['json']['success'])->toBeTrue();
        expect($_SESSION['logged_in'])->toBeTrue();

        $user = $pdo->query("SELECT face_embedding, face_image_path, face_verified_at, face_enrolled FROM users WHERE username = 'facevalid'")
            ->fetch(PDO::FETCH_ASSOC);

        expect($user['face_embedding'])->toBe(json_encode($embedding));
        expect($user['face_image_path'])->toBeNull();
        expect($user['face_verified_at'])->not->toBeNull();
        expect((int) $user['face_enrolled'])->toBe(1);
    } finally {
        facial_registration_cleanup_face_uploads($beforeUploads);
    }
});

it('stores face image path when registration includes a valid face snapshot', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_registration_make_dependencies($pdo);
    $beforeUploads = facial_registration_snapshot_face_uploads();

    try {
        $payload = facial_registration_payload('faceimage', 'faceimage@example.com');
        $embedding = facial_registration_embedding(0.21);
        $image = 'data:image/jpeg;base64,' . base64_encode('facial-regression-image');

        $completed = facial_registration_complete_with_face($controller, $pdo, $payload, [
            'face_embedding' => json_encode($embedding),
            'face_image_b64' => $image,
        ]);

        expect($completed['json']['success'])->toBeTrue();

        $user = $pdo->query("SELECT face_image_path, face_enrolled FROM users WHERE username = 'faceimage'")
            ->fetch(PDO::FETCH_ASSOC);

        expect($user['face_image_path'])->not->toBeNull();
        expect($user['face_image_path'])->toStartWith('faces/');
        expect(is_file(dirname(__DIR__, 2) . '/app/uploads/' . $user['face_image_path']))->toBeTrue();
        expect((int) $user['face_enrolled'])->toBe(1);
    } finally {
        facial_registration_cleanup_face_uploads($beforeUploads);
    }
});

it('does not mark face enrolled when facial data is absent', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_registration_make_dependencies($pdo);
    $beforeUploads = facial_registration_snapshot_face_uploads();

    try {
        $payload = facial_registration_payload('nofaceenroll', 'nofaceenroll@example.com');
        $completed = facial_registration_complete_with_face($controller, $pdo, $payload);

        expect($completed['json']['success'])->toBeTrue();

        $user = $pdo->query("SELECT face_embedding, face_image_path, face_verified_at, face_enrolled FROM users WHERE username = 'nofaceenroll'")
            ->fetch(PDO::FETCH_ASSOC);

        expect($user['face_embedding'])->toBeNull();
        expect($user['face_image_path'])->toBeNull();
        expect($user['face_verified_at'])->toBeNull();
        expect((int) $user['face_enrolled'])->toBe(0);
    } finally {
        facial_registration_cleanup_face_uploads($beforeUploads);
    }
});

it('does not mark face enrolled when facial payload is invalid', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_registration_make_dependencies($pdo);
    $beforeUploads = facial_registration_snapshot_face_uploads();

    try {
        $payload = facial_registration_payload('invalidface', 'invalidface@example.com');
        $completed = facial_registration_complete_with_face($controller, $pdo, $payload, [
            'face_embedding' => json_encode([0.1, 0.2, 0.3]),
            'face_image_b64' => 'data:image/jpeg;base64,' . base64_encode('should-not-save'),
        ]);

        expect($completed['json']['success'])->toBeTrue();

        $user = $pdo->query("SELECT face_embedding, face_image_path, face_verified_at, face_enrolled FROM users WHERE username = 'invalidface'")
            ->fetch(PDO::FETCH_ASSOC);

        expect($user['face_embedding'])->toBeNull();
        expect($user['face_image_path'])->toBeNull();
        expect($user['face_verified_at'])->toBeNull();
        expect((int) $user['face_enrolled'])->toBe(0);
    } finally {
        facial_registration_cleanup_face_uploads($beforeUploads);
    }
});

it('does not persist person or user records when registration completion fails', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    [$controller] = facial_registration_make_dependencies($pdo);
    $beforeUploads = facial_registration_snapshot_face_uploads();

    try {
        $payload = facial_registration_payload('rollbackface', 'rollbackface@example.com');
        $sent = integration_send_verification_code($controller, $payload, 'valid-captcha');
        expect($sent['json']['success'])->toBeTrue();

        $identifier = $sent['json']['target'];
        $code = test_query_value($pdo, 'SELECT code FROM email_verifications WHERE email = :email', [
            ':email' => $identifier,
        ]);
        $verified = integration_verify_registration_code($controller, $identifier, $code);
        expect($verified['json']['success'])->toBeTrue();

        MysqlAuthTestDatabase::seedResidentUser($pdo, [
            'user' => [
                'username' => 'conflictinguser',
                'email' => 'conflicting@example.com',
            ],
        ]);

        $override = $pdo->prepare(
            "UPDATE email_verifications
             SET user_data = :user_data
             WHERE token = :token"
        );
        $override->execute([
            ':user_data' => json_encode([
                'username' => 'conflictinguser',
                'email' => 'rollbackface@example.com',
                'mobile' => '',
                'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            ]),
            ':token' => $verified['json']['token'],
        ]);

        $failed = integration_complete_registration($controller, $verified['json']['token'], [
            'face_embedding' => json_encode(facial_registration_embedding(0.33)),
        ]);

        expect($failed['status'])->toBe(500);
        expect($failed['json']['code'])->toBe('registration_failed');
        expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM users WHERE email = 'rollbackface@example.com'"))->toBe(0);
        expect((int) test_query_value($pdo, "SELECT COUNT(*) FROM persons WHERE first_name = 'Facial' AND last_name = 'Resident'"))->toBe(0);
    } finally {
        facial_registration_cleanup_face_uploads($beforeUploads);
    }
});
