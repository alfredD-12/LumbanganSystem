<?php
/**
 * Session helper functions for authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if logged in as user (resident)
 */
function isUser() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'user';
}

/**
 * Check if logged in as official
 */
function isOfficial() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'official';
}

/**
 * Get logged in user/official ID
 */
function getUserId() {
    if (isUser()) {
        return $_SESSION['user_id'] ?? null;
    } elseif (isOfficial()) {
        return $_SESSION['official_id'] ?? null;
    }
    return null;
}

/**
 * Get logged in user's full name
 */
function getFullName() {
    return $_SESSION['full_name'] ?? 'Guest';
}

/**
 * Get username
 */
function getUsername() {
    return $_SESSION['username'] ?? '';
}

/**
 * Get first name
 */
function getFirstName() {
    return $_SESSION['first_name'] ?? 'Guest';
}

/**
 * Require user login - redirect if not logged in
 */
function requireUser() {
    if (!isUser()) {
        $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=landing';
        header('Location: ' . $redirect);
        exit();
    }
}

/**
 * Require official login - redirect if not logged in
 */
function requireOfficial() {
    if (!isOfficial()) {
        $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=landing';
        header('Location: ' . $redirect);
        exit();
    }
}

/**
 * Require any login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=landing';
        header('Location: ' . $redirect);
        exit();
    }
}
