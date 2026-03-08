<?php
// Detect protocol correctly even behind ngrok or proxies
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://';
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
}

// Current host
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Path to your project
$baseFolder = '/Lumbangan_BMIS/bmis-lumbangan-system/';

// Define dynamic URLs
define('BASE_URL', $protocol . $host . $baseFolder . 'app/');
define('BASE_PUBLIC', $protocol . $host . $baseFolder . 'public/');

define('FAVICON_PATH', $protocol . $host . $baseFolder . 'favicon.ico');

// Brute-force protection feature flag and thresholds
define('BRUTE_FORCE_PROTECTION_ENABLED', true);
define('RATE_LIMIT_IP_MAX_ATTEMPTS', 5);
define('RATE_LIMIT_IP_WINDOW_MINUTES', 1);
define('ACCOUNT_RATE_LIMIT_MAX_FAILURES', 8);
define('ACCOUNT_RATE_LIMIT_WINDOW_MINUTES', 15);
define('ACCOUNT_LOCKOUT_THRESHOLD', 5);
define('ACCOUNT_LOCKOUT_BASE_MINUTES', 15);
define('ACCOUNT_LOCKOUT_MAX_MINUTES', 120);
define('ACCOUNT_LOCKOUT_BACKOFF_MULTIPLIER', 2);
define('CAPTCHA_TRIGGER_THRESHOLD', 3);
define('RECAPTCHA_SITE_KEY', '6Lcrd4MsAAAAAB3kNjq83RHlacnaKS139-ZqZWUQ');
define('RECAPTCHA_SECRET_KEY', '6Lcrd4MsAAAAAGRQtVggrOxrOkFe7hhB2ZVFnvyn');
define('RECAPTCHA_SCORE_THRESHOLD', 0.5);
define('ADMIN_ALERT_THRESHOLD_IP', 10);
define('DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD', 5);

function render_favicon()
{
    echo '<link rel="icon" type="image/x-icon" href="' . FAVICON_PATH . '">' . "\n";
    echo '    <link rel="shortcut icon" type="image/x-icon" href="' . FAVICON_PATH . '">' . "\n";
}
