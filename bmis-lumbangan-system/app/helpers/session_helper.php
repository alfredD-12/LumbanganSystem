<?php
/**
 * Session helper functions for authentication
 */

// Start session if not already started
require_once __DIR__ . '/session_bootstrap.php';

bmis_start_session();

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
 * Check if logged in official has police role
 */
function isPolice() {
    if (!isOfficial()) {
        return false;
    }

    $role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
    return $role === 'police';
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
 * Get email address
 */
function getEmail() {
    return $_SESSION['email'] ?? '';
}

/**
 * Get contact number
 */
function getContactNumber() {
    return $_SESSION['contact_no'] ?? '';
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
 * Require police login - redirect if not logged in as police
 */
function requirePolice() {
    if (!isPolice()) {
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
