<?php

@require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/EmailVerification.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/csrf_helper.php';
require_once dirname(__DIR__) . '/services/AuthSecurityContext.php';
require_once dirname(__DIR__) . '/services/AuthSecurityService.php';
require_once dirname(__DIR__) . '/services/RegistrationMailSender.php';
require_once dirname(__DIR__) . '/services/RegistrationSmsSender.php';

class EmailVerificationController
{
    private $db;
    private $verificationModel;
    private $userModel;
    private $authSecurityService;
    private $mailSender;
    private $smsSender;

    public function __construct(array $dependencies = [])
    {
        $this->db = $dependencies['db'] ?? (new Database($dependencies['db_config'] ?? []))->getConnection();
        $clock = $dependencies['clock'] ?? null;
        $this->verificationModel = $dependencies['verificationModel'] ?? new EmailVerification($this->db, $clock);
        $this->userModel = $dependencies['userModel'] ?? new User($this->db);
        $this->authSecurityService = $dependencies['authSecurityService'] ?? new AuthSecurityService(
            $this->db,
            $dependencies['loginAttemptLogger'] ?? null,
            $dependencies['rateLimitService'] ?? null,
            $dependencies['accountLockoutService'] ?? null,
            $dependencies['securityAlertService'] ?? ($dependencies['adminAlertService'] ?? null),
            $dependencies['captchaVerifier'] ?? null,
            $clock
        );
        $this->mailSender = $dependencies['mailSender'] ?? new RegistrationMailSender();
        $this->smsSender = $dependencies['smsSender'] ?? new RegistrationSmsSender($dependencies['smsHelper'] ?? null);
    }

    public function sendVerificationCode()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_request_method',
                'message' => 'Invalid request method',
            ], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson($this->invalidCsrfResponse(), 403);
            return;
        }

        $payload = $this->normalizeRegistrationPayload($_POST);
        if (!$payload['valid']) {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_registration_payload',
                'message' => $payload['message'],
            ], 422);
            return;
        }

        $context = AuthSecurityContext::forRegistrationRequest(
            $payload['identifier'],
            $this->getClientIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            trim($_POST['captcha_token'] ?? '')
        );

        $guard = $this->authSecurityService->guard($context);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        if ($this->userModel->usernameExists($payload['username'])) {
            $this->respondJson([
                'success' => false,
                'code' => 'username_taken',
                'message' => 'This username is already taken. Please choose another.',
            ], 422);
            return;
        }

        if ($payload['email'] !== '' && $this->userModel->emailExists($payload['email'])) {
            $this->respondJson([
                'success' => false,
                'code' => 'email_taken',
                'message' => 'This email is already registered.',
            ], 422);
            return;
        }

        $registrationData = [
            'username' => $payload['username'],
            'email' => $payload['email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'middle_name' => $payload['middle_name'],
            'suffix' => $payload['suffix'],
            'sex' => $payload['sex'],
            'birthdate' => $payload['birthdate'],
            'marital_status' => $payload['marital_status'],
            'mobile' => $payload['mobile'],
            'password_hash' => password_hash($payload['password'], PASSWORD_BCRYPT),
        ];

        try {
            $verification = $this->verificationModel->createVerification($payload['identifier'], $registrationData);
            if (empty($verification['success'])) {
                $this->respondJson([
                    'success' => false,
                    'code' => 'verification_creation_failed',
                    'message' => 'Failed to create verification. Please try again.',
                ], 500);
                return;
            }

            $sent = $this->deliverVerificationCode(
                $payload['email'],
                $payload['mobile'],
                $payload['first_name'],
                $verification['code']
            );

            if (!$sent['email_sent'] && !$sent['sms_sent']) {
                $this->respondJson([
                    'success' => false,
                    'code' => 'verification_delivery_failed',
                    'message' => 'Failed to send verification code. Please try again later.',
                ], 500);
                return;
            }

            $this->authSecurityService->recordSuccess($context);
            $message = $sent['email_sent'] && $sent['sms_sent']
                ? 'Verification code sent to your email and mobile number.'
                : ($sent['email_sent']
                    ? 'Verification code sent to your email.'
                    : 'Verification code sent to your mobile number.');

            $this->respondJson([
                'success' => true,
                'code' => 'verification_sent',
                'message' => $message,
                'target' => $payload['identifier'],
            ]);
        } catch (Exception $e) {
            error_log('[EmailVerification::sendVerificationCode] ' . $e->getMessage());
            $this->respondJson([
                'success' => false,
                'code' => 'verification_send_failed',
                'message' => 'An error occurred while preparing verification. Please try again.',
            ], 500);
        }
    }

    public function verifyCode()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_request_method',
                'message' => 'Invalid request method',
            ], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson($this->invalidCsrfResponse(), 403);
            return;
        }

        $identifier = trim($_POST['email'] ?? $_POST['verification_target'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $context = AuthSecurityContext::forRegistrationVerify(
            $identifier,
            $this->getClientIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            trim($_POST['captcha_token'] ?? '')
        );

        if ($identifier === '' || $code === '') {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_verification_payload',
                'message' => 'Verification target and code are required.',
            ], 422);
            return;
        }

        $guard = $this->authSecurityService->guard($context);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        $verification = $this->verificationModel->verifyCode($identifier, $code);
        if (!$verification) {
            $this->authSecurityService->recordFailure($context, 'invalid_verification_code');
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_verification_code',
                'message' => 'Invalid or expired verification code.',
            ], 422);
            return;
        }

        $this->authSecurityService->recordSuccess($context);
        $this->respondJson([
            'success' => true,
            'code' => 'verification_verified',
            'message' => 'Code verified successfully.',
            'token' => $verification['token'],
        ]);
    }

    public function completeRegistration()
    {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->respondJson([
                    'success' => false,
                    'code' => 'invalid_request_method',
                    'message' => 'Invalid request method',
                ], 405);
                return;
            }

            if (!csrf_request_is_valid()) {
                $this->respondJson($this->invalidCsrfResponse(), 403);
                return;
            }

            $token = trim($_POST['token'] ?? '');
            if ($token === '') {
                $this->respondJson([
                    'success' => false,
                    'code' => 'invalid_verification_token',
                    'message' => 'Invalid verification token.',
                ], 422);
                return;
            }

            $verification = $this->verificationModel->getByToken($token);
            if (!$verification) {
                $this->respondJson([
                    'success' => false,
                    'code' => 'invalid_verification_token',
                    'message' => 'Invalid or expired verification token.',
                ], 422);
                return;
            }

            $personData = json_decode($verification['person_data'], true);
            $userData = json_decode($verification['user_data'], true);

            $this->db->beginTransaction();

            $personStmt = $this->db->prepare(
                "INSERT INTO persons (first_name, last_name, middle_name, suffix, sex, birthdate, marital_status)
                 VALUES (:first_name, :last_name, :middle_name, :suffix, :sex, :birthdate, :marital_status)"
            );
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
            [$faceEmbedding, $faceImagePath, $faceVerifiedAt, $faceEnrolled] = $this->extractFaceData();

            $userStmt = $this->db->prepare(
                "INSERT INTO users
                    (person_id, username, email, mobile, password_hash, face_embedding, face_image_path, face_verified_at, face_enrolled)
                 VALUES
                    (:person_id, :username, :email, :mobile, :password_hash, :face_embedding, :face_image_path, :face_verified_at, :face_enrolled)"
            );
            $mobile = !empty($userData['mobile']) ? $userData['mobile'] : null;
            $email = !empty($userData['email']) ? $userData['email'] : null;
            $userStmt->bindParam(':person_id', $personId);
            $userStmt->bindParam(':username', $userData['username']);
            $userStmt->bindParam(':email', $email);
            $userStmt->bindParam(':mobile', $mobile);
            $userStmt->bindParam(':password_hash', $userData['password_hash']);
            $userStmt->bindValue(':face_embedding', $faceEmbedding, $faceEmbedding ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $userStmt->bindValue(':face_image_path', $faceImagePath, $faceImagePath ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $userStmt->bindValue(':face_verified_at', $faceVerifiedAt, $faceVerifiedAt ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $userStmt->bindParam(':face_enrolled', $faceEnrolled, PDO::PARAM_INT);

            if (!$userStmt->execute()) {
                throw new Exception('Failed to create user record');
            }

            $userId = $this->db->lastInsertId();
            $this->verificationModel->markAsVerified($token);
            $this->db->commit();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $userId;
            $_SESSION['person_id'] = $personId;
            $_SESSION['username'] = $userData['username'];
            $_SESSION['first_name'] = $personData['first_name'];
            $_SESSION['full_name'] = trim(
                $personData['first_name'] . ' '
                . ($personData['middle_name'] ? substr($personData['middle_name'], 0, 1) . '. ' : '')
                . $personData['last_name']
                . ($personData['suffix'] ? ' ' . $personData['suffix'] : '')
            );
            $_SESSION['email'] = $userData['email'];
            $_SESSION['mobile'] = !empty($userData['mobile']) ? $userData['mobile'] : '';
            $_SESSION['user_type'] = 'user';
            $_SESSION['logged_in'] = true;

            $redirectUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_resident';
            $this->respondJson([
                'success' => true,
                'code' => 'registration_completed',
                'message' => 'Registration successful! Redirecting to dashboard...',
                'redirect' => $redirectUrl,
            ]);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log('[EmailVerification::completeRegistration] ' . $e->getMessage());
            $this->respondJson([
                'success' => false,
                'code' => 'registration_failed',
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

    public function resendCode()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_request_method',
                'message' => 'Invalid request method',
            ], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson($this->invalidCsrfResponse(), 403);
            return;
        }

        $identifier = trim($_POST['email'] ?? $_POST['verification_target'] ?? '');
        if ($identifier === '') {
            $this->respondJson([
                'success' => false,
                'code' => 'verification_target_required',
                'message' => 'Verification target is required.',
            ], 422);
            return;
        }

        $context = AuthSecurityContext::forRegistrationRequest(
            $identifier,
            $this->getClientIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            trim($_POST['captcha_token'] ?? '')
        );

        $guard = $this->authSecurityService->guard($context);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        $verification = $this->verificationModel->getPendingVerification($identifier);
        if (!$verification) {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_verification_session',
                'message' => 'Verification session expired. Please register again.',
            ], 422);
            return;
        }

        $personData = json_decode($verification['person_data'], true);
        $userData = json_decode($verification['user_data'], true);
        $registrationData = array_merge($personData, $userData);

        $newVerification = $this->verificationModel->createVerification($identifier, $registrationData);
        if (empty($newVerification['success'])) {
            $this->respondJson([
                'success' => false,
                'code' => 'verification_creation_failed',
                'message' => 'Failed to resend verification code.',
            ], 500);
            return;
        }

        $sent = $this->deliverVerificationCode(
            $userData['email'] ?? '',
            $userData['mobile'] ?? '',
            $personData['first_name'] ?? '',
            $newVerification['code']
        );

        if (!$sent['email_sent'] && !$sent['sms_sent']) {
            $this->respondJson([
                'success' => false,
                'code' => 'verification_delivery_failed',
                'message' => 'Failed to send verification code. Please try again later.',
            ], 500);
            return;
        }

        $this->authSecurityService->recordSuccess($context);
        $this->respondJson([
            'success' => true,
            'code' => 'verification_resent',
            'message' => 'New verification code sent.',
            'target' => $identifier,
        ]);
    }

    private function normalizeRegistrationPayload(array $source)
    {
        $username = trim($source['username'] ?? '');
        $contact = trim($source['email'] ?? '');
        $mobile = trim($source['mobile'] ?? '');
        $password = $source['password'] ?? '';
        $confirmPassword = $source['confirm_password'] ?? '';
        $firstName = trim($source['first_name'] ?? '');
        $lastName = trim($source['last_name'] ?? '');
        $middleName = trim($source['middle_name'] ?? '');
        $suffix = trim($source['suffix'] ?? '') ?: null;
        $sex = $source['sex'] ?? null;
        $birthdate = $source['birthdate'] ?? null;
        $maritalStatus = $source['marital_status'] ?? 'Single';

        if ($username === '' || $contact === '' || $password === '' || $firstName === '' || $lastName === '') {
            return ['valid' => false, 'message' => 'Please fill in all required fields.'];
        }

        if ($password !== $confirmPassword) {
            return ['valid' => false, 'message' => 'Passwords do not match.'];
        }

        if (strlen($password) < 6) {
            return ['valid' => false, 'message' => 'Password must be at least 6 characters long.'];
        }

        $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = preg_match('/^(09|\+639)\d{9}$/', $contact) === 1;
        if (!$isEmail && !$isPhone) {
            return ['valid' => false, 'message' => 'Please provide a valid email address or phone number.'];
        }

        if ($isPhone) {
            $mobile = $contact;
        }

        $email = $isEmail ? $contact : '';
        $identifier = $isEmail ? $email : $mobile;

        return [
            'valid' => true,
            'identifier' => $identifier,
            'username' => $username,
            'email' => $email,
            'mobile' => $mobile,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $middleName,
            'suffix' => $suffix,
            'sex' => $sex,
            'birthdate' => $birthdate,
            'marital_status' => $maritalStatus,
        ];
    }

    private function deliverVerificationCode($email, $mobile, $firstName, $code)
    {
        $emailSent = $email !== '' ? (bool) $this->mailSender->sendVerificationCode($email, $firstName, $code) : false;
        $smsSent = $mobile !== '' ? (bool) $this->smsSender->sendVerificationCode($mobile, $code) : false;

        return [
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
        ];
    }

    private function extractFaceData()
    {
        $faceEmbedding = null;
        $faceImagePath = null;
        $faceVerifiedAt = null;
        $faceEnrolled = 0;

        $faceEmbeddingRaw = trim($_POST['face_embedding'] ?? '');
        $faceImageB64 = trim($_POST['face_image_b64'] ?? '');

        if ($faceEmbeddingRaw !== '') {
            $decoded = json_decode($faceEmbeddingRaw, true);
            if (is_array($decoded) && count($decoded) === 128) {
                $faceEmbedding = $faceEmbeddingRaw;
                $faceVerifiedAt = date('Y-m-d H:i:s');
                $faceEnrolled = 1;
            }
        }

        if ($faceImageB64 !== '' && $faceEmbedding !== null) {
            try {
                $uploadDir = dirname(__DIR__) . '/uploads/faces/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $faceImageB64);
                $imgBytes = base64_decode($imgData);
                if ($imgBytes !== false) {
                    $filename = 'face_' . uniqid('', true) . '.jpg';
                    file_put_contents($uploadDir . $filename, $imgBytes);
                    $faceImagePath = 'faces/' . $filename;
                }
            } catch (Exception $e) {
                error_log('Face image save error: ' . $e->getMessage());
            }
        }

        return [$faceEmbedding, $faceImagePath, $faceVerifiedAt, $faceEnrolled];
    }

    private function getClientIpAddress()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    private function invalidCsrfResponse()
    {
        return [
            'success' => false,
            'code' => 'invalid_csrf',
            'message' => 'Security validation failed. Refresh the page and try again.',
        ];
    }

    private function respondJson(array $payload, $statusCode = 200)
    {
        if (!array_key_exists('code', $payload)) {
            $payload['code'] = !empty($payload['success']) ? 'ok' : null;
        }

        if (!array_key_exists('retry_after', $payload)) {
            $payload['retry_after'] = null;
        }

        http_response_code((int) $statusCode);
        echo json_encode($payload);
    }

    private function statusCodeForAuthError($code)
    {
        if ($code === 'invalid_csrf') {
            return 403;
        }

        if ($code === 'rate_limit_exceeded') {
            return 429;
        }

        if ($code === 'captcha_required') {
            return 422;
        }

        return 400;
    }
}
