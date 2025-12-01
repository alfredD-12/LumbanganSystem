<?php

@require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/EmailVerification.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/sms_helper.php';

class EmailVerificationController {
    private $db;
    private $verificationModel;
    private $userModel;
    private $smsHelper;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->verificationModel = new EmailVerification($this->db);
        $this->userModel = new User($this->db);
        $this->smsHelper = new SMSHelper();
    }

    /**
     * Send verification code to email (Step 1)
     * This stores the registration data temporarily and sends the code
     */
    public function sendVerificationCode() {
        try {
            header('Content-Type: application/json');

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }

            // Get all registration data from the form
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $middle_name = trim($_POST['middle_name'] ?? '');
            $suffix = trim($_POST['suffix'] ?? '') ?: null;
            $sex = $_POST['sex'] ?? null;
            $birthdate = $_POST['birthdate'] ?? null;
            $marital_status = $_POST['marital_status'] ?? 'Single';

            // Validate required fields (email is required at this point - will be moved to mobile if it's a phone number)
            if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
                echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
                return;
            }

            // Validate password match
            if ($password !== $confirm_password) {
                echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
                return;
            }

            // Validate password strength
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
                return;
            }

            // Validate email format (accept both email and phone number)
            // Check if it's a valid email OR a valid phone number
            $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            $isPhone = preg_match('/^(09|\+639)\d{9}$/', $email); // Philippine mobile format
            
            if (!$isEmail && !$isPhone) {
                echo json_encode(['success' => false, 'message' => 'Please provide a valid email address or phone number']);
                return;
            }
            
            // If it's a phone number, store it in mobile field instead
            if ($isPhone && !$isEmail) {
                $mobile = $email;
                $email = ''; // Clear email since user provided phone number
            }

            // Check if username already exists
            if ($this->userModel->usernameExists($username)) {
                echo json_encode(['success' => false, 'message' => 'This username is already taken. Please choose another.']);
                return;
            }

            // Check if email already exists (only if email was provided)
            if (!empty($email) && $this->userModel->emailExists($email)) {
                echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
                return;
            }

            // Hash the password for temporary storage
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Prepare registration data for temporary storage
            $registrationData = [
                'username' => $username,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'middle_name' => $middle_name,
                'suffix' => $suffix,
                'sex' => $sex,
                'birthdate' => $birthdate,
                'marital_status' => $marital_status,
                'mobile' => $mobile,
                'password_hash' => $passwordHash
            ];

            // Create verification record
            $result = $this->verificationModel->createVerification($email, $registrationData);

            if (!$result['success']) {
                echo json_encode(['success' => false, 'message' => 'Failed to create verification. Please try again.']);
                return;
            }

            // Send verification code via email AND/OR SMS
            $code = $result['code'];
            $emailSent = false;
            $smsSent = false;
            
            // Send via Email
            if (!empty($email)) {
                $subject = 'Email Verification Code - Barangay Lumbangan';
                $body = $this->getEmailTemplate($first_name, $code);
                $emailSent = $this->sendEmail($email, $subject, $body);
            }
            
            // Send via SMS if mobile number is provided
            if (!empty($mobile)) {
                $message = "Your Barangay Lumbangan verification code is: {$code}. Valid for 1 hour. Do not share this code.";
                $smsResult = $this->smsHelper->sendSMS($mobile, $message);
                $smsSent = $smsResult['success'];
            }
            
            // Determine success message based on what was sent
            if ($emailSent && $smsSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification code sent to your email and mobile number',
                    'email' => $email,
                    'mobile' => $mobile
                ]);
            } elseif ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification code sent to your email',
                    'email' => $email
                ]);
            } elseif ($smsSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification code sent to your mobile number',
                    'mobile' => $mobile
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send verification code. Please try again later.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]);
        }
    }

    /**
     * Verify the code entered by user (Step 2)
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

        // Verify the code
        $verification = $this->verificationModel->verifyCode($email, $code);

        if (!$verification) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
            return;
        }

        // Return success with token for next step
        echo json_encode([
            'success' => true,
            'message' => 'Code verified successfully',
            'token' => $verification['token']
        ]);
    }

    /**
     * Complete registration after code verification (Step 3)
     * This actually creates the user account
     */
    public function completeRegistration() {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }

            $token = trim($_POST['token'] ?? '');

            if (empty($token)) {
                echo json_encode(['success' => false, 'message' => 'Invalid verification token']);
                return;
            }

            // Get verification data
            $verification = $this->verificationModel->getByToken($token);

            if (!$verification) {
                echo json_encode(['success' => false, 'message' => 'Invalid or expired verification']);
                return;
            }

            // Decode stored data
            $personData = json_decode($verification['person_data'], true);
            $userData = json_decode($verification['user_data'], true);

            // Start transaction
            $this->db->beginTransaction();

            // Create person record
            $personQuery = "INSERT INTO persons (first_name, last_name, middle_name, suffix, sex, birthdate, marital_status) 
                           VALUES (:first_name, :last_name, :middle_name, :suffix, :sex, :birthdate, :marital_status)";
            
            $personStmt = $this->db->prepare($personQuery);
            $personStmt->bindParam(':first_name', $personData['first_name']);
            $personStmt->bindParam(':last_name', $personData['last_name']);
            $personStmt->bindParam(':middle_name', $personData['middle_name']);
            $personStmt->bindParam(':suffix', $personData['suffix']);
            $personStmt->bindParam(':sex', $personData['sex']);
            $personStmt->bindParam(':birthdate', $personData['birthdate']);
            $personStmt->bindParam(':marital_status', $personData['marital_status']);
            
            if (!$personStmt->execute()) {
                throw new Exception('Failed to create person record');
            }

            $personId = $this->db->lastInsertId();

            // Create user record - handle empty mobile and email fields
            $mobile = !empty($userData['mobile']) ? $userData['mobile'] : NULL;
            $email = !empty($userData['email']) ? $userData['email'] : NULL;
            
            $userQuery = "INSERT INTO users (person_id, username, email, mobile, password_hash) 
                         VALUES (:person_id, :username, :email, :mobile, :password_hash)";
            
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':person_id', $personId);
            $userStmt->bindParam(':username', $userData['username']);
            $userStmt->bindParam(':email', $email);
            $userStmt->bindParam(':mobile', $mobile);
            $userStmt->bindParam(':password_hash', $userData['password_hash']);
            
            if (!$userStmt->execute()) {
                throw new Exception('Failed to create user record');
            }

            $userId = $this->db->lastInsertId();

            // Mark verification as complete
            $this->verificationModel->markAsVerified($token);

            // Commit transaction
            $this->db->commit();

            // Auto-login the user
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $userId;
            $_SESSION['person_id'] = $personId;
            $_SESSION['username'] = $userData['username'];
            $_SESSION['first_name'] = $personData['first_name'];
            $_SESSION['full_name'] = trim($personData['first_name'] . ' ' . 
                                          ($personData['middle_name'] ? substr($personData['middle_name'], 0, 1) . '. ' : '') . 
                                          $personData['last_name'] . 
                                          ($personData['suffix'] ? ' ' . $personData['suffix'] : ''));
            $_SESSION['email'] = $userData['email'];
            $_SESSION['mobile'] = !empty($userData['mobile']) ? $userData['mobile'] : '';
            $_SESSION['user_type'] = 'user';
            $_SESSION['logged_in'] = true;

            // Build proper redirect URL
            if (defined('BASE_PUBLIC') && !empty(BASE_PUBLIC)) {
                $redirectUrl = rtrim(BASE_PUBLIC, '/') . '/index.php?page=dashboard_resident';
            } else {
                // Fallback: construct from current script location
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $basePath = dirname($_SERVER['SCRIPT_NAME']);
                $redirectUrl = $protocol . '://' . $host . $basePath . '/public/index.php?page=dashboard_resident';
            }

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! Redirecting to dashboard...',
                'redirect' => $redirectUrl
            ]);

        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
        }
    }

    /**
     * Resend verification code
     */
    public function resendCode() {
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

        // Check if there's a pending verification
        if (!$this->verificationModel->hasPendingVerification($email)) {
            echo json_encode(['success' => false, 'message' => 'No pending verification found for this email']);
            return;
        }

        // Get the existing verification to retrieve stored data
        $verification = $this->verificationModel->verifyCode($email, '000000'); // Dummy code to get data
        
        if (!$verification) {
            // If not found with dummy, try to get by email directly
            echo json_encode(['success' => false, 'message' => 'Verification session expired. Please register again.']);
            return;
        }

        $userData = json_decode($verification['user_data'], true);
        $personData = json_decode($verification['person_data'], true);

        // Create new verification (will delete old one and create new code)
        $registrationData = array_merge($personData, $userData);
        $result = $this->verificationModel->createVerification($email, $registrationData);

        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => 'Failed to resend code']);
            return;
        }

        // Send new code
        $code = $result['code'];
        $subject = 'Email Verification Code - Barangay Lumbangan';
        $body = $this->getEmailTemplate($personData['first_name'], $code);

        if ($this->sendEmail($email, $subject, $body)) {
            echo json_encode([
                'success' => true,
                'message' => 'New verification code sent to your email'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
    }

    /**
     * Email template for verification code
     */
    private function getEmailTemplate($firstName, $code) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 8px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Email Verification</h1>
                    <p>Barangay Lumbangan Management System</p>
                </div>
                <div class="content">
                    <p>Hello ' . htmlspecialchars($firstName) . ',</p>
                    <p>Thank you for registering! Please use the verification code below to complete your registration:</p>
                    
                    <div class="code-box">
                        <div class="code">' . $code . '</div>
                    </div>
                    
                    <p><strong>This code will expire in 1 hour.</strong></p>
                    <p>If you did not request this registration, please ignore this email.</p>
                    
                    <div class="footer">
                        <p>Barangay Lumbangan, Nasugbu, Batangas</p>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ';
    }

    /**
     * Send email via SMTP or PHP mail
     */
    private function sendEmail($to, $subject, $body) {
        @require_once dirname(__DIR__) . '/config/email_config.php';

        if (defined('EMAIL_METHOD') && EMAIL_METHOD === 'smtp') {
            return $this->sendEmailViaSMTP($to, $subject, $body);
        } else {
            return $this->sendEmailViaPHPMail($to, $subject, $body);
        }
    }

    /**
     * Send email via PHP mail()
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

            // Send MAIL FROM
            fputs($smtp, "MAIL FROM: <{$senderEmail}>\r\n");
            $response = fgets($smtp, 1024);

            // Send RCPT TO
            fputs($smtp, "RCPT TO: <{$to}>\r\n");
            $response = fgets($smtp, 1024);

            // Send DATA command
            fputs($smtp, "DATA\r\n");
            $response = fgets($smtp, 1024);

            // Send email headers and body
            $headers = "From: {$senderName} <{$senderEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";

            fputs($smtp, $headers . $body . "\r\n.\r\n");
            $response = fgets($smtp, 1024);

            // Send QUIT
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);

            return true;

        } catch (Exception $e) {
            // If SMTP fails, try PHP mail as fallback
            return $this->sendEmailViaPHPMail($to, $subject, $body);
        }
    }
}
