<?php

require_once dirname(__DIR__) . '/app/config/config.php';
require_once dirname(__DIR__) . '/app/config/Database.php';

$database = new Database();
$conn = $database->getConnection();

$checks = [
        'table_login_attempts' => "SHOW TABLES LIKE 'login_attempts'",
        'table_account_lockouts' => "SHOW TABLES LIKE 'account_lockouts'",
        'table_ip_rate_limits' => "SHOW TABLES LIKE 'ip_rate_limits'",
        'table_brute_force_alerts' => "SHOW TABLES LIKE 'brute_force_alerts'"
];

$allOk = true;

echo "Login Security Smoke Test\n";
echo "Feature flag enabled: " . (defined('BRUTE_FORCE_PROTECTION_ENABLED') && BRUTE_FORCE_PROTECTION_ENABLED ? 'yes' : 'no') . "\n";

echo "\nDatabase checks:\n";
foreach ($checks as $name => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $exists = (bool) $stmt->fetch();
        echo sprintf("- %s: %s\n", $name, $exists ? 'ok' : 'missing');
        if (!$exists) {
                $allOk = false;
        }
}

echo "\nConfig checks:\n";
$configChecks = [
        'RATE_LIMIT_IP_MAX_ATTEMPTS' => defined('RATE_LIMIT_IP_MAX_ATTEMPTS'),
        'ACCOUNT_RATE_LIMIT_MAX_FAILURES' => defined('ACCOUNT_RATE_LIMIT_MAX_FAILURES'),
        'ACCOUNT_LOCKOUT_THRESHOLD' => defined('ACCOUNT_LOCKOUT_THRESHOLD'),
        'CAPTCHA_TRIGGER_THRESHOLD' => defined('CAPTCHA_TRIGGER_THRESHOLD')
];

foreach ($configChecks as $key => $ok) {
        echo sprintf("- %s: %s\n", $key, $ok ? 'ok' : 'missing');
        if (!$ok) {
                $allOk = false;
        }
}

echo "\nResult: " . ($allOk ? 'PASS' : 'FAIL') . "\n";
exit($allOk ? 0 : 1);
