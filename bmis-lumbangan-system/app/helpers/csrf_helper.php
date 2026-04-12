<?php

require_once __DIR__ . '/../config/config.php';

if (!function_exists('csrf_request_token')) {
    function csrf_request_token()
    {
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper(csrf_header_name()));
        if (!empty($_SERVER[$headerName])) {
            return (string) $_SERVER[$headerName];
        }

        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        $fieldName = csrf_field_name();
        if (!empty($_POST[$fieldName])) {
            return (string) $_POST[$fieldName];
        }

        if (!empty($_POST['csrf_token'])) {
            return (string) $_POST['csrf_token'];
        }

        $rawInput = function_exists('csrf_raw_input') ? csrf_raw_input() : '';
        if (!empty($rawInput)) {
            $json = json_decode($rawInput, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $fieldName = csrf_field_name();

                if (!empty($json[$fieldName])) {
                    return (string) $json[$fieldName];
                }

                if (!empty($json['csrf_token'])) {
                    return (string) $json['csrf_token'];
                }
            }
        }

        return '';
    }
}

if (!function_exists('csrf_raw_input')) {
    function csrf_raw_input()
    {
        if (array_key_exists('__csrf_raw_input', $GLOBALS)) {
            return (string) $GLOBALS['__csrf_raw_input'];
        }

        return (string) file_get_contents('php://input');
    }
}

if (!function_exists('csrf_request_is_valid')) {
    function csrf_request_is_valid()
    {
        $token = csrf_request_token();

        return csrf_validate($token, csrf_field_name()) || csrf_validate($token, 'csrf_token');
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
            echo json_encode([
                'success' => false,
                'code' => 'invalid_csrf',
                'message' => $message,
                'retry_after' => null,
            ]);
        } else {
            header('Content-Type: text/plain; charset=UTF-8');
            echo $message;
        }

        // In test runs we cannot `exit`, because that kills the entire Pest process mid-suite.
        // Throwing lets tests capture the response and continue executing.
        if (defined('APP_ENV') && APP_ENV === 'testing') {
            if (!class_exists('CsrfRejectedException')) {
                class CsrfRejectedException extends RuntimeException
                {
                }
            }

            throw new CsrfRejectedException($message);
        }

        exit;
    }
}

if (!function_exists('csrf_require_valid_token')) {
    function csrf_require_valid_token()
    {
        if (!csrf_request_is_valid()) {
            csrf_reject_request();
        }
    }
}
