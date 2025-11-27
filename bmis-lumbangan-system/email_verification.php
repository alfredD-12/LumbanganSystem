<?php
/**
 * Email Verification API Endpoint
 * Handles AJAX requests for email verification during registration
 */

// Suppress errors in output
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/app/controllers/EmailVerificationController.php';

$controller = new EmailVerificationController();

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'send_code':
        $controller->sendVerificationCode();
        break;
        
    case 'verify_code':
        $controller->verifyCode();
        break;
        
    case 'complete_registration':
        $controller->completeRegistration();
        break;
        
    case 'resend_code':
        $controller->resendCode();
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
