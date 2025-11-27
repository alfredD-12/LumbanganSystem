<?php
// password_reset.php - endpoint for password reset actions
require_once __DIR__ . '/app/controllers/PasswordResetController.php';

if (!empty($_GET['action'])) {
    $controller = new PasswordResetController();

    switch ($_GET['action']) {
        case 'request_reset':
            $controller->requestReset();
            break;
        case 'verify_code':
            $controller->verifyCode();
            break;
        case 'reset_password':
            $controller->resetPassword();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}
