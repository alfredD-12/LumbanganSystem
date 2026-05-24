<?php

/**
 * Central session bootstrap for BMIS auth.
 *
 * The app does not implement "Remember Me", so the PHP session cookie must be
 * a browser-session cookie instead of a persistent cookie.
 */
if (!function_exists('bmis_configure_session_cookie')) {
    function bmis_configure_session_cookie()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_lifetime', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        $params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $params['path'] ?: '/',
            'domain' => $params['domain'] ?? '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (!function_exists('bmis_start_session')) {
    function bmis_start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            bmis_configure_session_cookie();
            session_start();
        }
    }
}

if (!function_exists('bmis_expire_session_cookie')) {
    function bmis_expire_session_cookie()
    {
        if (!ini_get('session.use_cookies')) {
            return;
        }

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
}
