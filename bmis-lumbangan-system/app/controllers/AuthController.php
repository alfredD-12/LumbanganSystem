<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

@require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Official.php';
require_once dirname(__DIR__) . '/helpers/csrf_helper.php';
require_once dirname(__DIR__) . '/services/AuthSecurityContext.php';
require_once dirname(__DIR__) . '/services/AuthSecurityService.php';

class AuthController
{
    private $db;
    private $userModel;
    private $officialModel;
    private $authSecurityService;
    private $dummyPasswordHash;

    public function __construct(array $dependencies = [])
    {
        $this->db = $dependencies['db'] ?? (new Database($dependencies['db_config'] ?? []))->getConnection();
        $this->userModel = $dependencies['userModel'] ?? new User($this->db);
        $this->officialModel = $dependencies['officialModel'] ?? new Official($this->db);
        $this->dummyPasswordHash = '$2y$10$g7B2U4QY9vDYbh1Psj0G6OVj3Lh9d9nIXfEyx8jGljN1g7q.QhC9u';
        $this->authSecurityService = $dependencies['authSecurityService'] ?? new AuthSecurityService(
            $this->db,
            $dependencies['loginAttemptLogger'] ?? null,
            $dependencies['rateLimitService'] ?? null,
            $dependencies['accountLockoutService'] ?? null,
            $dependencies['securityAlertService'] ?? ($dependencies['adminAlertService'] ?? null),
            $dependencies['captchaVerifier'] ?? null,
            $dependencies['clock'] ?? null
        );
    }

    /**
     * Handle login request
     */
    public function login()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_csrf',
                'message' => 'Security validation failed. Refresh the page and try again.',
            ], 403);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $captchaToken = trim($_POST['captcha_token'] ?? '');
        $ipAddress = $this->getClientIpAddress();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $securityContext = AuthSecurityContext::forLogin($username, $ipAddress, $userAgent, $captchaToken);

        if (empty($username) || empty($password)) {
            $this->respondJson(['success' => false, 'message' => 'Username and password are required'], 422);
            return;
        }

        $guard = $this->authSecurityService->guard($securityContext);
        if (empty($guard['allowed'])) {
            $this->respondJson($guard['response'], $this->statusCodeForAuthError($guard['response']['code'] ?? null));
            return;
        }

        $loginSuccess = false;
        $authenticatedUserType = null;
        $redirectUrl = null;
        $user = $this->userModel->findByUsername($username);

        if ($user) {
            if (password_verify($password, $user['password_hash'])) {
                if ($user['status'] !== 'active') {
                    $failureState = $this->authSecurityService->recordFailure($securityContext, 'account_disabled');
                    if (!empty($failureState['locked'])) {
                        $this->respondJson([
                            'success' => false,
                            'code' => 'account_locked',
                            'retry_after' => (int) ($failureState['retry_after'] ?? 900),
                            'message' => 'Too many failed sign-in attempts. This account is temporarily locked.',
                        ], 423);
                        return;
                    }

                    $this->respondJson([
                        'success' => false,
                        'code' => 'invalid_credentials',
                        'attempts_remaining' => (int) ($failureState['attempts_remaining'] ?? 0),
                        'message' => 'Unable to sign in with the provided credentials.',
                    ], 401);
                    return;
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['person_id'] = $user['person_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['full_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'] . ' ' . ($user['suffix'] ?? ''));
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['mobile'] = $user['mobile'] ?? '';
                $_SESSION['user_type'] = 'user';
                $_SESSION['logged_in'] = true;

                $this->userModel->updateLastLogin($user['id']);

                $loginSuccess = true;
                $authenticatedUserType = 'user';
                $redirectUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_resident';
            }
        }

        $official = !$loginSuccess ? $this->officialModel->findByUsername($username) : null;

        if ($official) {
            if (password_verify($password, $official['password_hash'])) {
                $_SESSION['official_id'] = $official['id'];
                $_SESSION['username'] = $official['username'];
                $_SESSION['full_name'] = $official['full_name'];
                $_SESSION['role'] = $official['role'];
                $_SESSION['user_type'] = 'official';
                $_SESSION['logged_in'] = true;

                $this->officialModel->updateLastLogin($official['id']);

                $loginSuccess = true;
                $authenticatedUserType = 'official';
                $redirectUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_official';
            }
        }

        if ($loginSuccess) {
            $this->authSecurityService->recordSuccess($securityContext);

            $this->respondJson([
                'success' => true,
                'message' => 'Login successful',
                'user_type' => $authenticatedUserType,
                'redirect' => $redirectUrl,
            ]);
            return;
        }

        password_verify($password, $this->dummyPasswordHash);

        if ($this->isBruteForceProtectionEnabled()) {
            $failureState = $this->authSecurityService->recordFailure($securityContext, 'invalid_credentials');

            if (!empty($failureState['locked'])) {
                $this->respondJson([
                    'success' => false,
                    'code' => 'account_locked',
                    'retry_after' => (int) ($failureState['retry_after'] ?? 900),
                    'message' => 'Too many failed sign-in attempts. This account is temporarily locked.',
                ], 423);
                return;
            }

            $attemptsRemaining = (int) ($failureState['attempts_remaining'] ?? 0);
            $message = 'Invalid username or password.';
            if ($attemptsRemaining > 0) {
                $message .= ' ' . $attemptsRemaining . ' attempt(s) remaining before temporary lockout.';
            }

            $this->respondJson([
                'success' => false,
                'code' => 'invalid_credentials',
                'attempts_remaining' => $attemptsRemaining,
                'message' => $message,
            ], 401);
            return;
        }

        $this->respondJson(['success' => false, 'message' => 'Invalid username or password'], 401);
    }

    private function isBruteForceProtectionEnabled()
    {
        return defined('BRUTE_FORCE_PROTECTION_ENABLED') && BRUTE_FORCE_PROTECTION_ENABLED === true;
    }

    private function getClientIpAddress()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '0.0.0.0';
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

        if ($code === 'account_locked') {
            return 423;
        }

        if ($code === 'rate_limit_exceeded') {
            return 429;
        }

        if ($code === 'captcha_required') {
            return 422;
        }

        return 400;
    }

    /**
     * Handle registration request
     */
    public function register()
    {
        header('Content-Type: application/json');
        $this->respondJson([
            'success' => false,
            'code' => 'legacy_route_disabled',
            'message' => 'Direct registration is disabled. Use the verification-based signup flow.',
        ], 410);
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_request_method',
                'message' => 'Logout must be submitted via POST.',
                'retry_after' => null,
            ], 405);
            return;
        }

        if (!csrf_request_is_valid()) {
            $this->respondJson([
                'success' => false,
                'code' => 'invalid_csrf',
                'message' => 'Invalid CSRF token.',
                'retry_after' => null,
            ], 403);
            return;
        }

        // Start session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Clear session variables and destroy session data
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            // Remove the session cookie on client
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_unset();
        session_destroy();

        // If request is AJAX/XHR, return JSON success so client fetch() can act on it
        $isXhr = (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            !empty($_SERVER['HTTP_ACCEPT']) &&
            stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        );

        if ($isXhr) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'code' => 'logged_out',
                'message' => 'Logged out',
                'retry_after' => null,
            ]);
            return;
        }

        // Otherwise, redirect the browser to the landing page via index router
        $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=landing';
        header('Location: ' . $redirect);
        exit();
    }

    /**
     * Check if username is available (AJAX endpoint)
     */
    public function checkUsername()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $username = trim($_POST['username'] ?? '');

        // Validate username
        if (empty($username)) {
            echo json_encode(['available' => false, 'message' => 'Username is required']);
            return;
        }

        if (strlen($username) < 3) {
            echo json_encode(['available' => false, 'message' => 'Username must be at least 3 characters']);
            return;
        }

        // Check if username exists
        $exists = $this->userModel->usernameExists($username);

        if ($exists) {
            echo json_encode(['available' => false, 'message' => 'Username is already taken']);
        } else {
            echo json_encode(['available' => true, 'message' => 'Username is available']);
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $controller = new AuthController();

    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        switch ($action) {
            case 'login':
                $controller->login();
                break;
            case 'register':
                $controller->register();
                break;
            case 'logout':
                $controller->logout();
                break;
            case 'checkUsername':
            case 'check_username':
                $controller->checkUsername();
                break;
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No action specified']);
    }
}
