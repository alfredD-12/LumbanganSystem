<?php
session_start();

@require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/PasswordReset.php';

class PasswordResetController {
    private $db;
    private $userModel;
    private $resetModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->resetModel = new PasswordReset($this->db);
    }

    /**
     * Request password reset - send code to email
     */
    public function requestReset() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            return;
        }

        // Find user by email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // Don't reveal if email exists for security
            echo json_encode([
                'success' => true,
                'message' => 'If the email exists, a reset code will be sent'
            ]);
            return;
        }

        // Create reset token
        $resetData = $this->resetModel->createToken($user['id'], $email);

        if (!$resetData['success']) {
            echo json_encode(['success' => false, 'message' => 'Failed to create reset token']);
            return;
        }

        // Send email with code
        $code = $resetData['code'];
        $subject = 'Password Reset Code - Barangay Lumbangan';
        $body = $this->getEmailTemplate($user['first_name'], $code);

        if ($this->sendEmail($email, $subject, $body)) {
            echo json_encode([
                'success' => true,
                'message' => 'Reset code sent to your email',
                'email' => $email
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ]);
        }
    }

    /**
     * Verify reset code
     */
    public function verifyCode() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if (empty($email) || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Email and code are required']);
            return;
        }

        // Verify code
        $reset = $this->resetModel->verifyCode($email, $code);

        if (!$reset) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired code']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Code verified successfully',
            'token' => $reset['token']
        ]);
    }

    /**
     * Reset password with token
     */
    public function resetPassword() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $token = trim($_POST['token'] ?? '');
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }

        // Verify token
        $reset = $this->resetModel->getByToken($token);

        if (!$reset) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $query = "UPDATE users SET password_hash = :password WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':user_id', $reset['user_id']);

        if ($stmt->execute()) {
            // Mark reset as used
            $this->resetModel->markAsUsed($reset['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Password reset successfully. Please login with your new password.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
        }
    }

    /**
     * Send email via SMTP or PHP mail
     */
    private function sendEmail($to, $subject, $body) {
        // Load email configuration
        @require_once dirname(__DIR__) . '/config/email_config.php';

        if (defined('EMAIL_METHOD') && EMAIL_METHOD === 'smtp') {
            return $this->sendEmailViaSMTP($to, $subject, $body);
        } else {
            return $this->sendEmailViaPHPMail($to, $subject, $body);
        }
    }

    /**
     * Send email via PHP mail() function (basic fallback)
     */
    private function sendEmailViaPHPMail($to, $subject, $body) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@barangaylumbangan.gov.ph\r\n";

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Send email via SMTP (Gmail or other SMTP servers)
     */
    private function sendEmailViaSMTP($to, $subject, $body) {
        try {
            $host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
            $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
            $username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $secure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
            $senderEmail = defined('SENDER_EMAIL') ? SENDER_EMAIL : 'noreply@barangaylumbangan.gov.ph';
            $senderName = defined('SENDER_NAME') ? SENDER_NAME : 'Barangay Lumbangan';

            // Create SMTP connection
            $smtp = fsockopen($host, $port, $errno, $errstr, 10);
            
            if (!$smtp) {
                // SMTP connection failed, fallback to PHP mail
                return $this->sendEmailViaPHPMail($to, $subject, $body);
            }

            // Read SMTP response
            $response = fgets($smtp, 1024);

            // Send HELO command
            fputs($smtp, "HELO localhost\r\n");
            $response = fgets($smtp, 1024);

            // Upgrade to TLS if required
            if ($secure === 'tls') {
                fputs($smtp, "STARTTLS\r\n");
                $response = fgets($smtp, 1024);

                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($smtp);
                    return $this->sendEmailViaPHPMail($to, $subject, $body);
                }

                // Send HELO again after STARTTLS
                fputs($smtp, "HELO localhost\r\n");
                $response = fgets($smtp, 1024);
            }

            // Authenticate
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, base64_encode($username) . "\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, base64_encode($password) . "\r\n");
            $response = fgets($smtp, 1024);

            if (strpos($response, '235') === false) {
                fclose($smtp);
                return $this->sendEmailViaPHPMail($to, $subject, $body);
            }

            // Send email
            fputs($smtp, "MAIL FROM: <{$senderEmail}>\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, "RCPT TO: <{$to}>\r\n");
            $response = fgets($smtp, 1024);

            fputs($smtp, "DATA\r\n");
            $response = fgets($smtp, 1024);

            // Compose email headers and body
            $emailHeaders = "From: {$senderName} <{$senderEmail}>\r\n";
            $emailHeaders .= "To: {$to}\r\n";
            $emailHeaders .= "Subject: {$subject}\r\n";
            $emailHeaders .= "MIME-Version: 1.0\r\n";
            $emailHeaders .= "Content-type: text/html; charset=UTF-8\r\n";
            $emailHeaders .= "\r\n";

            fputs($smtp, $emailHeaders . $body . "\r\n.\r\n");
            $response = fgets($smtp, 1024);

            // Send QUIT command
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);

            return true;
        } catch (Exception $e) {
            // Fallback to PHP mail on any error
            return $this->sendEmailViaPHPMail($to, $subject, $body);
        }
    }

    /**
     * Email template
     */
    private function getEmailTemplate($firstName, $code) {
        $barangayName = 'Barangay Lumbangan';
        $appUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : 'https://example.com';

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .code-box { background: white; border: 2px solid #667eea; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
                .code-box .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
                .footer { background: #f0f0f0; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .note { color: #d32f2f; font-size: 12px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                    <p>{$barangayName}</p>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$firstName}</strong>,</p>
                    <p>We received a request to reset your password. Please use the code below to proceed:</p>
                    
                    <div class='code-box'>
                        <div class='code'>{$code}</div>
                    </div>
                    
                    <p><strong>How to use this code:</strong></p>
                    <ol>
                        <li>Go back to the password reset form</li>
                        <li>Enter this 6-digit code</li>
                        <li>Create your new password</li>
                        <li>Click 'Reset Password'</li>
                    </ol>
                    
                    <div class='note'>
                        <strong>⚠️ Important:</strong> This code expires in 1 hour. If you didn't request this reset, please ignore this email and your account remains secure.
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " {$barangayName}, Nasugbu, Batangas. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

// Handle requests
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
}
