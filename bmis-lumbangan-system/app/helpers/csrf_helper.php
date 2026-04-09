<?php
/**
 * Centralized CSRF helper utilities.
 *
 * This file provides token generation, rendering helpers, and request validation
 * primitives so controllers can opt into CSRF protection consistently.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('CSRF_SESSION_KEY')) {
    define('CSRF_SESSION_KEY', '_csrf_token');
}
if (!defined('CSRF_FIELD_NAME')) {
    define('CSRF_FIELD_NAME', 'csrf_token');
}
if (!defined('CSRF_HEADER_NAME')) {
    define('CSRF_HEADER_NAME', 'X-CSRF-Token');
}

if (!function_exists('csrf_field_name')) {
    function csrf_field_name() {
        return CSRF_FIELD_NAME;
    }
}

if (!function_exists('csrf_header_name')) {
    function csrf_header_name() {
        return CSRF_HEADER_NAME;
    }
}

if (!function_exists('csrf_generate_token_value')) {
    function csrf_generate_token_value() {
        try {
            return bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            return hash('sha256', uniqid('csrf_', true) . mt_rand());
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token($regenerate = false) {
        if (
            $regenerate ||
            !isset($_SESSION[CSRF_SESSION_KEY]) ||
            !is_string($_SESSION[CSRF_SESSION_KEY]) ||
            $_SESSION[CSRF_SESSION_KEY] === ''
        ) {
            $_SESSION[CSRF_SESSION_KEY] = csrf_generate_token_value();
        }

        return $_SESSION[CSRF_SESSION_KEY];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field($fieldName = null, $token = null) {
        $fieldName = $fieldName ?: csrf_field_name();
        $token = $token ?: csrf_token();

        $safeField = htmlspecialchars((string)$fieldName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeToken = htmlspecialchars((string)$token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<input type="hidden" name="' . $safeField . '" value="' . $safeToken . '">';
    }
}

if (!function_exists('csrf_meta_tags')) {
    function csrf_meta_tags() {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $field = htmlspecialchars(csrf_field_name(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $header = htmlspecialchars(csrf_header_name(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<meta name="csrf-token" content="' . $token . '">' . "\n"
            . '<meta name="csrf-field" content="' . $field . '">' . "\n"
            . '<meta name="csrf-header" content="' . $header . '">';
    }
}

if (!function_exists('csrf_is_safe_method')) {
    function csrf_is_safe_method($method = null) {
        $method = strtoupper($method ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        return in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
    }
}

if (!function_exists('csrf_get_request_token')) {
    function csrf_get_request_token($fieldName = null) {
        $fieldName = $fieldName ?: csrf_field_name();

        if (isset($_POST[$fieldName]) && is_string($_POST[$fieldName])) {
            return trim($_POST[$fieldName]);
        }

        $headerServerKey = 'HTTP_' . strtoupper(str_replace('-', '_', csrf_header_name()));
        if (isset($_SERVER[$headerServerKey]) && is_string($_SERVER[$headerServerKey])) {
            return trim($_SERVER[$headerServerKey]);
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                foreach ($headers as $name => $value) {
                    if (strcasecmp($name, csrf_header_name()) === 0 && is_string($value)) {
                        return trim($value);
                    }
                }
            }
        }

        return null;
    }
}

if (!function_exists('csrf_validate')) {
    function csrf_validate($candidateToken) {
        if (!is_string($candidateToken) || $candidateToken === '') {
            return false;
        }

        $sessionToken = isset($_SESSION[CSRF_SESSION_KEY]) ? (string)$_SESSION[CSRF_SESSION_KEY] : '';
        if ($sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $candidateToken);
    }
}

if (!function_exists('csrf_is_ajax_request')) {
    function csrf_is_ajax_request() {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (is_string($requestedWith) && strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return is_string($accept) && stripos($accept, 'application/json') !== false;
    }
}

if (!function_exists('csrf_require_valid_token')) {
    function csrf_require_valid_token(array $options = []) {
        if (csrf_is_safe_method()) {
            return true;
        }

        $fieldName = isset($options['field']) ? (string)$options['field'] : csrf_field_name();
        $candidate = csrf_get_request_token($fieldName);

        if (csrf_validate($candidate)) {
            return true;
        }

        if (!empty($options['regenerate_on_failure'])) {
            csrf_token(true);
        }

        $statusCode = isset($options['status']) ? (int)$options['status'] : 419;
        if ($statusCode < 400) {
            $statusCode = 419;
        }

        $message = isset($options['message'])
            ? (string)$options['message']
            : 'Invalid or missing CSRF token.';

        $responseType = isset($options['response']) ? (string)$options['response'] : '';
        if ($responseType === '') {
            $responseType = csrf_is_ajax_request() ? 'json' : 'plain';
        }

        if ($responseType === 'redirect') {
            $redirectTo = isset($options['redirect']) ? (string)$options['redirect'] : '';
            if ($redirectTo !== '') {
                $separator = strpos($redirectTo, '?') === false ? '?' : '&';
                header('Location: ' . $redirectTo . $separator . 'csrf=invalid');
                exit;
            }
            $responseType = 'plain';
        }

        if ($responseType === 'json') {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => $message,
                'code' => 'invalid_csrf'
            ]);
            exit;
        }

        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
        exit;
    }
}
