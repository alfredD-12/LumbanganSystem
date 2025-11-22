<?php
// Detect protocol correctly even behind ngrok or proxies
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://';
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
}

// Current host
$host = $_SERVER['HTTP_HOST'];

// Path to your project
$baseFolder = '/Lumbangan_BMIS/bmis-lumbangan-system/';

// Define dynamic URLs
define('BASE_URL', $protocol . $host . $baseFolder . 'app/');
define('BASE_PUBLIC', $protocol . $host . $baseFolder . 'public/');
?>
