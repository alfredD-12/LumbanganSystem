<?php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/services/SecurityCleanupService.php';

$database = new Database();
$conn = $database->getConnection();
$cleanupService = new SecurityCleanupService($conn);
$cleanupService->cleanup();

echo "Brute-force cleanup completed at " . date('Y-m-d H:i:s') . PHP_EOL;
