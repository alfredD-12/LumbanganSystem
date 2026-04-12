<?php

date_default_timezone_set('Asia/Shanghai');

function test_set_env($key, $value)
{
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv($key . '=' . $value);
}

test_set_env('APP_ENV', $_ENV['APP_ENV'] ?? 'testing');
test_set_env('BMIS_USE_TEST_DB', $_ENV['BMIS_USE_TEST_DB'] ?? '1');
test_set_env('BMIS_TEST_DB_HOST', $_ENV['BMIS_TEST_DB_HOST'] ?? 'localhost');
test_set_env('BMIS_TEST_DB_PORT', $_ENV['BMIS_TEST_DB_PORT'] ?? '3306');
test_set_env('BMIS_TEST_DB_NAME', $_ENV['BMIS_TEST_DB_NAME'] ?? 'lumbangansystem_test');
test_set_env('BMIS_TEST_DB_USERNAME', $_ENV['BMIS_TEST_DB_USERNAME'] ?? 'root');
test_set_env('BMIS_TEST_DB_PASSWORD', $_ENV['BMIS_TEST_DB_PASSWORD'] ?? '');
test_set_env('RECAPTCHA_SITE_KEY', $_ENV['RECAPTCHA_SITE_KEY'] ?? 'test-site-key');
test_set_env('RECAPTCHA_SECRET_KEY', $_ENV['RECAPTCHA_SECRET_KEY'] ?? 'test-secret-key');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/helpers/csrf_helper.php';
require_once __DIR__ . '/../app/services/ClockInterface.php';
require_once __DIR__ . '/../app/services/SystemClock.php';
require_once __DIR__ . '/../app/services/CaptchaVerifierInterface.php';
require_once __DIR__ . '/../app/services/GoogleCaptchaVerifier.php';
require_once __DIR__ . '/../app/services/SecurityAlertServiceInterface.php';
require_once __DIR__ . '/../app/services/PasswordResetMailerInterface.php';
require_once __DIR__ . '/../app/services/PasswordResetMailer.php';
require_once __DIR__ . '/../app/services/AuthSecurityContext.php';
require_once __DIR__ . '/../app/services/SecurityCleanupService.php';
require_once __DIR__ . '/../app/models/RateLimitService.php';
require_once __DIR__ . '/../app/models/AccountLockoutService.php';
require_once __DIR__ . '/../app/models/LoginAttemptLogger.php';
require_once __DIR__ . '/../app/models/AdminAlertService.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Official.php';
require_once __DIR__ . '/../app/models/PasswordReset.php';
require_once __DIR__ . '/../app/services/AuthSecurityService.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/PasswordResetController.php';

function test_reset_http_state()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_GET = [];
    $_POST = [];
    $_FILES = [];
    $_COOKIE = [];
    $_REQUEST = [];
    $_SESSION = [];

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'Pest';
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';

    http_response_code(200);
}

function test_seed_csrf_token($token = 'test-csrf-token')
{
    $_SESSION[csrf_field_name()] = $token;
    $_SESSION['csrf_token'] = $token;

    return $token;
}

function test_apply_post_request(array $post = [], array $server = [])
{
    $token = test_seed_csrf_token();

    $_SERVER = array_merge($_SERVER, [
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_USER_AGENT' => 'Pest',
        'HTTP_ACCEPT' => 'application/json',
    ], $server);

    $_POST = array_merge([csrf_field_name() => $token], $post);

    return $token;
}

function test_capture_json(callable $callback)
{
    http_response_code(200);
    ob_start();
    $callback();
    $raw = trim((string) ob_get_clean());

    return [
        'status' => http_response_code(),
        'raw' => $raw,
        'json' => $raw !== '' ? json_decode($raw, true) : null,
    ];
}

function test_query_value(PDO $pdo, $sql, array $params = [])
{
    $stmt = $pdo->prepare($sql);
    foreach ($params as $name => $value) {
        $stmt->bindValue($name, $value);
    }
    $stmt->execute();
    $value = $stmt->fetchColumn();

    return $value === false ? null : $value;
}
