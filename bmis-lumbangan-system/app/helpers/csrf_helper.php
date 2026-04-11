<?php

require_once __DIR__ . '/../config/config.php';

if (!function_exists('csrf_request_token')) {
    function csrf_request_token()
    {
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!empty($_POST['csrf_token'])) {
            return (string) $_POST['csrf_token'];
        }

        return '';
    }
}

if (!function_exists('csrf_reject_request')) {
    function csrf_reject_request($message = 'Invalid CSRF token.')
    {
        http_response_code(403);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            header('Content-Type: text/plain; charset=UTF-8');
            echo $message;
        }

        exit;
    }
}

if (!function_exists('csrf_require_valid_token')) {
    function csrf_require_valid_token()
    {
        $token = csrf_request_token();
        if (!csrf_validate($token)) {
            csrf_reject_request();
        }
    }
}
