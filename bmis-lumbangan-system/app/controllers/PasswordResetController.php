<?php

@require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/PasswordReset.php';
require_once dirname(__DIR__) . '/helpers/csrf_helper.php';
require_once dirname(__DIR__) . '/services/AuthSecurityContext.php';
require_once dirname(__DIR__) . '/services/AuthSecurityService.php';
require_once dirname(__DIR__) . '/services/PasswordResetMailer.php';

class PasswordResetController
{
    private $db;
    private $userModel;
    private $resetModel;
    private $authSecurityService;
    private $mailer;

    public function __construct(array $dependencies = [])
    {
        $this->db = $dependencies['db'] ?? (new Database($dependencies['db_config'] ?? []))->getConnection();
        $this->userModel = $dependencies['userModel'] ?? new User($this->db);
        $clock = $dependencies['clock'] ?? null;
        $this->resetModel = $dependencies['resetModel'] ?? new PasswordReset($this->db, $clock);
        $this->mailer = $dependencies['mailer'] ?? new PasswordResetMailer();
        $this->authSecurityService = $dependencies['authSecurityService'] ?? new AuthSecurityService(
            $this->db,
            $dependencies['loginAttemptLogger'] ?? null,
            $dependencies['rateLimitService'] ?? null,
            $dependencies['accountLockoutService'] ?? null,
            $dependencies['securityAlertService'] ?? ($dependencies['adminAlertService'] ?? null),
            $dependencies['captchaVerifier'] ?? null,
            $clock
        );
    }

    public function requestReset()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson($this->invalidCsrfResponse(), 403);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $captchaToken = trim($_POST['captcha_token'] ?? '');
        $context = AuthSecurityContext::forPasswordResetRequest(
            $email,
            $this->getClientIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $captchaToken
        );

        if ($email === '') {
            $this->respondJson(['success' => false, 'message' => 'Email is required'], 422);
            return;
        }

        $guard = $this->authSecurityService->guard($context);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $resetData = $this->resetModel->createToken($user['id'], $email);
            if (!empty($resetData['success'])) {
                $this->mailer->sendResetCode($user, $resetData['code']);
            }
        }

        $this->authSecurityService->recordSuccess($context);
        $this->respondJson([
            'success' => true,
            'message' => 'If the email exists, a reset code will be sent.',
        ]);
    }

    public function verifyCode()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson($this->invalidCsrfResponse(), 403);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $captchaToken = trim($_POST['captcha_token'] ?? '');
        $context = AuthSecurityContext::forPasswordResetVerify(
            $email,
            $this->getClientIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $captchaToken
        );

        if ($email === '' || $code === '') {
            $this->respondJson(['success' => false, 'message' => 'Email and code are required'], 422);
            return;
        }

        $guard = $this->authSecurityService->guard($context);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        $reset = $this->resetModel->verifyCode($email, $code);
        if (!$reset) {
            $this->authSecurityService->recordFailure($context, 'invalid_reset_code');
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_reset_code',
                'message' => 'Invalid or expired code.',
            ], 422);
            return;
        }

        $this->authSecurityService->recordSuccess($context);
        $this->respondJson([
            'success' => true,
            'message' => 'Code verified successfully.',
            'token' => $reset['token'],
        ]);
    }

    public function resetPassword()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson($this->invalidCsrfResponse(), 403);
            return;
        }

        $token = trim($_POST['token'] ?? '');
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $captchaToken = trim($_POST['captcha_token'] ?? '');
        $context = AuthSecurityContext::forPasswordResetSubmit(
            $token,
            $this->getClientIpAddress(),
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $captchaToken
        );

        if ($token === '' || $newPassword === '' || $confirmPassword === '') {
            $this->respondJson(['success' => false, 'message' => 'All fields are required'], 422);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->respondJson(['success' => false, 'message' => 'Passwords do not match'], 422);
            return;
        }

        if (strlen($newPassword) < 6) {
            $this->respondJson(['success' => false, 'message' => 'Password must be at least 6 characters'], 422);
            return;
        }

        $guard = $this->authSecurityService->guard($context);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        $reset = $this->resetModel->getByToken($token);
        if (!$reset) {
            $this->authSecurityService->recordFailure($context, 'invalid_reset_token');
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_reset_token',
                'message' => 'Invalid or expired reset token.',
            ], 422);
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $query = "UPDATE users SET password_hash = :password WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':user_id', $reset['user_id']);

        if ($stmt->execute()) {
            $this->resetModel->markAsUsed($reset['id']);
            $this->authSecurityService->recordSuccess($context);
            $this->respondJson([
                'success' => true,
                'message' => 'Password reset successfully. Please login with your new password.',
            ]);
            return;
        }

        $this->respondJson(['success' => false, 'message' => 'Failed to reset password'], 500);
    }

    private function getClientIpAddress()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '0.0.0.0';
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
