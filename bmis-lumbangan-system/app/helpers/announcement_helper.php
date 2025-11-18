<?php
// announcement_helper.php
// Small helper utilities for building announcement-related URLs

// Make sure config constants are available
if (!defined('BASE_URL')) {
    // config.php is one directory up from helpers
    require_once __DIR__ . '/../config/config.php';
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, "/") . "/";
        if ($path === '') return $base;
        return $base . ltrim($path, "/");
    }
}

if (!function_exists('assets_url')) {
    function assets_url(string $path = ''): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('uploads_url')) {
    function uploads_url(string $path = ''): string
    {
        return base_url('uploads/' . ltrim($path, '/'));
    }
}

if (!function_exists('announcement_image_url')) {
    function announcement_image_url(?string $filename): string
    {
        if (!$filename) return '';
        return uploads_url('announcementimage/' . ltrim($filename, '/'));
    }
}

?>
