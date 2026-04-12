<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('config_project_root')) {
    function config_project_root()
    {
        return dirname(__DIR__, 2);
    }
}

if (!function_exists('config_strip_wrapping_quotes')) {
    function config_strip_wrapping_quotes($value)
    {
        $value = trim((string) $value);
        $length = strlen($value);

        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }
}

if (!function_exists('config_load_env_file')) {
    function config_load_env_file($path)
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || strpos($trimmed, '=') === false) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
            if ($key === '') {
                continue;
            }

            $alreadyDefined = array_key_exists($key, $_ENV)
                || array_key_exists($key, $_SERVER)
                || getenv($key) !== false;

            if ($alreadyDefined) {
                continue;
            }

            $value = config_strip_wrapping_quotes($value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

if (!defined('BMIS_ENV_BOOTSTRAPPED')) {
    define('BMIS_ENV_BOOTSTRAPPED', true);

    $projectRoot = config_project_root();
    config_load_env_file($projectRoot . '/.env');
    config_load_env_file($projectRoot . '/.env.local');

    $loadedEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: '';
    if ($loadedEnv === 'testing') {
        config_load_env_file($projectRoot . '/.env.testing');
        config_load_env_file($projectRoot . '/.env.testing.local');
    }
}

if (!function_exists('config_env')) {
    function config_env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('config_env_bool')) {
    function config_env_bool($key, $default = false)
    {
        $value = config_env($key, null);
        if ($value === null) {
            return (bool) $default;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return (bool) $default;
    }
}

if (!function_exists('config_env_int')) {
    function config_env_int($key, $default = 0)
    {
        $value = config_env($key, null);
        if ($value === null || !is_numeric($value)) {
            return (int) $default;
        }

        return (int) $value;
    }
}

if (!function_exists('config_env_float')) {
    function config_env_float($key, $default = 0.0)
    {
        $value = config_env($key, null);
        if ($value === null || !is_numeric($value)) {
            return (float) $default;
        }

        return (float) $value;
    }
}

if (!function_exists('config_define')) {
    function config_define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

if (!function_exists('config_detect_base_folder')) {
    function config_detect_base_folder()
    {
        $candidates = [
            $_SERVER['SCRIPT_NAME'] ?? '',
            $_SERVER['PHP_SELF'] ?? '',
            $_SERVER['REQUEST_URI'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            $normalized = str_replace('\\', '/', (string) $candidate);
            if ($normalized === '') {
                continue;
            }

            if (preg_match('#^(.*?)/(public|app)(?:/|$)#', $normalized, $matches)) {
                $prefix = trim((string) ($matches[1] ?? ''), '/');
                return $prefix === '' ? '/' : '/' . $prefix . '/';
            }
        }

        return null;
    }
}

if (!function_exists('csrf_field_name')) {
    function csrf_field_name()
    {
        return (string) config_env('CSRF_FIELD_NAME', 'csrf_token');
    }
}

if (!function_exists('csrf_header_name')) {
    function csrf_header_name()
    {
        return (string) config_env('CSRF_HEADER_NAME', 'X-CSRF-TOKEN');
    }
}

if (!function_exists('csrf_session_key')) {
    function csrf_session_key($fieldName = null)
    {
        $name = $fieldName ?: csrf_field_name();
        return (string) $name;
    }
}

if (empty($_SESSION[csrf_session_key()])) {
    $_SESSION[csrf_session_key()] = bin2hex(random_bytes(32));
}

if (!function_exists('csrf_token')) {
    function csrf_token($fieldName = null)
    {
        $sessionKey = csrf_session_key($fieldName);

        if (empty($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$sessionKey];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input($fieldName = null)
    {
        $fieldName = $fieldName ?: csrf_field_name();
        $token = htmlspecialchars(csrf_token($fieldName), ENT_QUOTES, 'UTF-8');
        $field = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="' . $field . '" value="' . $token . '">';
    }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate($submittedToken, $fieldName = null)
    {
        $sessionKey = csrf_session_key($fieldName);
        if (!isset($_SESSION[$sessionKey])) {
            return false;
        }

        return hash_equals((string) $_SESSION[$sessionKey], (string) $submittedToken);
    }
}

$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$protocol = $forwardedProto !== ''
    ? $forwardedProto . '://'
    : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://');

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$configuredBaseFolder = trim((string) config_env('BMIS_BASE_FOLDER', '/bmis-lumbangan-system/'));
$detectedBaseFolder = config_detect_base_folder();
$baseFolder = $detectedBaseFolder !== null
    ? $detectedBaseFolder
    : ('/' . trim($configuredBaseFolder, '/') . '/');

config_define('APP_ENV', (string) config_env('APP_ENV', 'production'));
config_define('BASE_URL', $protocol . $host . $baseFolder . 'app/');
config_define('BASE_PUBLIC', $protocol . $host . $baseFolder . 'public/');
config_define('FAVICON_PATH', $protocol . $host . $baseFolder . 'favicon.ico');
config_define('BMIS_LOG_DIR', config_project_root() . '/app/logs');

config_define('BRUTE_FORCE_PROTECTION_ENABLED', config_env_bool('BRUTE_FORCE_PROTECTION_ENABLED', true));
config_define('RATE_LIMIT_IP_MAX_ATTEMPTS', config_env_int('RATE_LIMIT_IP_MAX_ATTEMPTS', 5));
config_define('RATE_LIMIT_IP_WINDOW_MINUTES', config_env_int('RATE_LIMIT_IP_WINDOW_MINUTES', 1));
config_define('ACCOUNT_RATE_LIMIT_MAX_FAILURES', config_env_int('ACCOUNT_RATE_LIMIT_MAX_FAILURES', 8));
config_define('ACCOUNT_RATE_LIMIT_WINDOW_MINUTES', config_env_int('ACCOUNT_RATE_LIMIT_WINDOW_MINUTES', 15));
config_define('ACCOUNT_LOCKOUT_THRESHOLD', config_env_int('ACCOUNT_LOCKOUT_THRESHOLD', 5));
config_define('ACCOUNT_LOCKOUT_BASE_MINUTES', config_env_int('ACCOUNT_LOCKOUT_BASE_MINUTES', 15));
config_define('ACCOUNT_LOCKOUT_MAX_MINUTES', config_env_int('ACCOUNT_LOCKOUT_MAX_MINUTES', 120));
config_define('ACCOUNT_LOCKOUT_BACKOFF_MULTIPLIER', config_env_int('ACCOUNT_LOCKOUT_BACKOFF_MULTIPLIER', 2));
config_define('CAPTCHA_TRIGGER_THRESHOLD', config_env_int('CAPTCHA_TRIGGER_THRESHOLD', 3));
config_define('PASSWORD_RESET_REQUEST_MAX_ATTEMPTS', config_env_int('PASSWORD_RESET_REQUEST_MAX_ATTEMPTS', 3));
config_define('PASSWORD_RESET_REQUEST_WINDOW_MINUTES', config_env_int('PASSWORD_RESET_REQUEST_WINDOW_MINUTES', 15));
config_define('PASSWORD_RESET_REQUEST_CAPTCHA_TRIGGER_THRESHOLD', config_env_int('PASSWORD_RESET_REQUEST_CAPTCHA_TRIGGER_THRESHOLD', 2));
config_define('REGISTRATION_REQUEST_MAX_ATTEMPTS', config_env_int('REGISTRATION_REQUEST_MAX_ATTEMPTS', 3));
config_define('REGISTRATION_REQUEST_WINDOW_MINUTES', config_env_int('REGISTRATION_REQUEST_WINDOW_MINUTES', 15));
config_define('REGISTRATION_REQUEST_CAPTCHA_ALWAYS', config_env_bool('REGISTRATION_REQUEST_CAPTCHA_ALWAYS', true));
config_define('REGISTRATION_VERIFY_MAX_FAILURES', config_env_int('REGISTRATION_VERIFY_MAX_FAILURES', 5));
config_define('REGISTRATION_VERIFY_WINDOW_MINUTES', config_env_int('REGISTRATION_VERIFY_WINDOW_MINUTES', 15));
config_define('REGISTRATION_VERIFY_CAPTCHA_TRIGGER_THRESHOLD', config_env_int('REGISTRATION_VERIFY_CAPTCHA_TRIGGER_THRESHOLD', 3));
config_define('PASSWORD_RESET_VERIFY_MAX_FAILURES', config_env_int('PASSWORD_RESET_VERIFY_MAX_FAILURES', 5));
config_define('PASSWORD_RESET_VERIFY_WINDOW_MINUTES', config_env_int('PASSWORD_RESET_VERIFY_WINDOW_MINUTES', 15));
config_define('PASSWORD_RESET_VERIFY_CAPTCHA_TRIGGER_THRESHOLD', config_env_int('PASSWORD_RESET_VERIFY_CAPTCHA_TRIGGER_THRESHOLD', 3));
config_define('PASSWORD_RESET_SUBMIT_MAX_FAILURES', config_env_int('PASSWORD_RESET_SUBMIT_MAX_FAILURES', 5));
config_define('PASSWORD_RESET_SUBMIT_WINDOW_MINUTES', config_env_int('PASSWORD_RESET_SUBMIT_WINDOW_MINUTES', 15));
config_define('PASSWORD_RESET_SUBMIT_CAPTCHA_TRIGGER_THRESHOLD', config_env_int('PASSWORD_RESET_SUBMIT_CAPTCHA_TRIGGER_THRESHOLD', 3));
config_define('PASSWORD_RESET_TOKEN_EXPIRY_MINUTES', config_env_int('PASSWORD_RESET_TOKEN_EXPIRY_MINUTES', 60));
config_define('RECAPTCHA_SITE_KEY', (string) config_env('RECAPTCHA_SITE_KEY', ''));
config_define('RECAPTCHA_SECRET_KEY', (string) config_env('RECAPTCHA_SECRET_KEY', ''));
config_define('RECAPTCHA_SCORE_THRESHOLD', config_env_float('RECAPTCHA_SCORE_THRESHOLD', 0.5));
config_define('ADMIN_ALERT_THRESHOLD_IP', config_env_int('ADMIN_ALERT_THRESHOLD_IP', 10));
config_define('DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD', config_env_int('DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD', 5));

config_define('EMAIL_METHOD', (string) config_env('EMAIL_METHOD', 'smtp'));
config_define('SMTP_HOST', (string) config_env('SMTP_HOST', 'smtp.gmail.com'));
config_define('SMTP_PORT', config_env_int('SMTP_PORT', 587));
config_define('SMTP_SECURE', (string) config_env('SMTP_SECURE', 'tls'));
config_define('SMTP_USERNAME', (string) config_env('SMTP_USERNAME', ''));
config_define('SMTP_PASSWORD', (string) config_env('SMTP_PASSWORD', ''));
config_define('SENDER_EMAIL', (string) config_env('SENDER_EMAIL', 'noreply@barangaylumbangan.gov.ph'));
config_define('SENDER_NAME', (string) config_env('SENDER_NAME', 'Barangay Lumbangan'));
config_define('SECURITY_ALERT_EMAIL', (string) config_env('SECURITY_ALERT_EMAIL', SENDER_EMAIL));

function render_favicon()
{
    echo '<link rel="icon" type="image/x-icon" href="' . FAVICON_PATH . '">' . "\n";
    echo '    <link rel="shortcut icon" type="image/x-icon" href="' . FAVICON_PATH . '">' . "\n";
}
