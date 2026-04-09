<?php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

$database = new Database();
$conn = $database->getConnection();

$cleanupQueries = [
        "DELETE FROM ip_rate_limits WHERE window_start < (NOW() - INTERVAL 1 HOUR)",
        "DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 90 DAY)",
        "DELETE FROM brute_force_alerts WHERE alert_sent_at < (NOW() - INTERVAL 90 DAY)",
        "DELETE FROM account_lockouts WHERE locked_until IS NULL AND last_failure_at < (NOW() - INTERVAL 90 DAY)"
];

foreach ($cleanupQueries as $query) {
        $stmt = $conn->prepare($query);
        $stmt->execute();
}

echo "Brute-force cleanup completed at " . date('Y-m-d H:i:s') . PHP_EOL;
