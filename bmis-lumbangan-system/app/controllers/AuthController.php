<?php
session_start();

// Include config, database and models
@require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Official.php';
require_once dirname(__DIR__) . '/models/RateLimitService.php';
require_once dirname(__DIR__) . '/models/AccountLockoutService.php';
require_once dirname(__DIR__) . '/models/LoginAttemptLogger.php';
require_once dirname(__DIR__) . '/models/AdminAlertService.php';
require_once dirname(__DIR__) . '/helpers/CaptchaHelper.php';

class AuthController
{
    private $db;
    private $userModel;
    private $officialModel;
    private $rateLimitService;
    private $accountLockoutService;
    private $loginAttemptLogger;
    private $adminAlertService;
    private $dummyPasswordHash;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->officialModel = new Official($this->db);
        $this->dummyPasswordHash = '$2y$10$g7B2U4QY9vDYbh1Psj0G6OVj3Lh9d9nIXfEyx8jGljN1g7q.QhC9u';

        if ($this->isBruteForceProtectionEnabled()) {
            $this->rateLimitService = new RateLimitService($this->db);
            $this->accountLockoutService = new AccountLockoutService($this->db);
            $this->loginAttemptLogger = new LoginAttemptLogger($this->db);
            $this->adminAlertService = new AdminAlertService($this->db);
        }
    }

    /**
     * Handle login request
     */
    public function login()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $captchaToken = trim($_POST['captcha_token'] ?? '');
        $ipAddress = $this->getClientIpAddress();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $protectionEnabled = $this->isBruteForceProtectionEnabled();

        // Validate inputs
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            return;
        }

        if ($protectionEnabled) {
            $ipCheck = $this->rateLimitService->checkIpRateLimit($ipAddress);
            if (!empty($ipCheck['blocked'])) {
                echo json_encode([
                    'success' => false,
                    'code' => 'rate_limit_exceeded',
                    'retry_after' => (int) ($ipCheck['retry_after'] ?? 60),
                    'message' => 'Too many login attempts from your network. Please try again shortly.'
                ]);
                return;
            }

            $lockCheck = $this->accountLockoutService->isAccountLocked($username);
            if (!empty($lockCheck['locked'])) {
                echo json_encode([
                    'success' => false,
                    'code' => 'account_locked',
                    'retry_after' => (int) ($lockCheck['retry_after'] ?? 900),
                    'message' => 'Too many failed sign-in attempts. This account is temporarily locked.'
                ]);
                return;
            }

            $accountRateMax = defined('ACCOUNT_RATE_LIMIT_MAX_FAILURES') ? (int) ACCOUNT_RATE_LIMIT_MAX_FAILURES : 8;
            $accountRateWindow = defined('ACCOUNT_RATE_LIMIT_WINDOW_MINUTES') ? (int) ACCOUNT_RATE_LIMIT_WINDOW_MINUTES : 15;
            $accountFailures = $this->loginAttemptLogger->countRecentFailuresByUsername($username, $accountRateWindow);
            if ($accountFailures >= $accountRateMax) {
                $retryAfter = $this->loginAttemptLogger->getUsernameWindowRetryAfter($username, $accountRateWindow);
                echo json_encode([
                    'success' => false,
                    'code' => 'account_rate_limited',
                    'retry_after' => (int) $retryAfter,
                    'message' => 'Too many failed attempts for this account. Please try again later.'
                ]);
                return;
            }

            $captchaRequired = CaptchaHelper::shouldRequireCaptcha($this->loginAttemptLogger, $username, $ipAddress);
            if ($captchaRequired) {
                $captchaOk = CaptchaHelper::verifyToken($captchaToken, $ipAddress, 'login');
                if (!$captchaOk) {
                    $this->rateLimitService->recordAttempt($ipAddress);
                    $this->loginAttemptLogger->logAttempt($username, $ipAddress, $userAgent, 'failure', 'captcha_failed');
                    echo json_encode([
                        'success' => false,
                        'code' => 'captcha_required',
                        'captcha_mode' => 'v2_checkbox',
                        'message' => 'Additional verification is required. Please complete the reCAPTCHA challenge.'
                    ]);
                    return;
                }
            }
        }

        $loginSuccess = false;
        $authenticatedUserType = null;
        $redirectUrl = null;
        // Try to find user first
        $user = $this->userModel->findByUsername($username);

        if ($user) {
            // User found - verify password
            if (password_verify($password, $user['password_hash'])) {
                // Check if user is active
                if ($user['status'] !== 'active') {
                    if ($protectionEnabled) {
                        $this->rateLimitService->recordAttempt($ipAddress);
                        $this->loginAttemptLogger->logAttempt($username, $ipAddress, $userAgent, 'failure', 'account_disabled');
                        $failureState = $this->accountLockoutService->recordFailure($username);
                        $this->adminAlertService->evaluateAndSend($ipAddress, $username, $this->loginAttemptLogger, !empty($failureState['locked']));
                    }
                    echo json_encode(['success' => false, 'message' => 'Unable to sign in with the provided credentials.']);
                    return;
                }

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['person_id'] = $user['person_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['full_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'] . ' ' . ($user['suffix'] ?? ''));
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['mobile'] = $user['mobile'] ?? '';
                $_SESSION['user_type'] = 'user';
                $_SESSION['logged_in'] = true;

                // Update last login
                $this->userModel->updateLastLogin($user['id']);

                $loginSuccess = true;
                $authenticatedUserType = 'user';
                $redirectUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_resident';
            }
        }

        // If user not found or password incorrect, try official
        $official = !$loginSuccess ? $this->officialModel->findByUsername($username) : null;

        if ($official) {
            // Official found - verify password
            if (password_verify($password, $official['password_hash'])) {
                // Set session variables for official
                $_SESSION['official_id'] = $official['id'];
                $_SESSION['username'] = $official['username'];
                $_SESSION['full_name'] = $official['full_name'];
                $_SESSION['role'] = $official['role'];
                $_SESSION['user_type'] = 'official';
                $_SESSION['logged_in'] = true;

                // Update last login
                $this->officialModel->updateLastLogin($official['id']);

                // Redirect officials to the official dashboard route handled by the front controller
                $loginSuccess = true;
                $authenticatedUserType = 'official';
                $redirectUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_official';
            }
        }

        if ($loginSuccess) {
            if ($protectionEnabled) {
                $this->rateLimitService->recordAttempt($ipAddress);
                $this->loginAttemptLogger->logAttempt($username, $ipAddress, $userAgent, 'success');
                $this->accountLockoutService->recordSuccess($username);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user_type' => $authenticatedUserType,
                'redirect' => $redirectUrl
            ]);
            return;
        }

        // Keep response timing closer for unknown usernames.
        password_verify($password, $this->dummyPasswordHash);

        if ($protectionEnabled) {
            $this->rateLimitService->recordAttempt($ipAddress);
            $this->loginAttemptLogger->logAttempt($username, $ipAddress, $userAgent, 'failure', 'invalid_credentials');
            $failureState = $this->accountLockoutService->recordFailure($username);
            $justLocked = !empty($failureState['locked']);
            $this->adminAlertService->evaluateAndSend($ipAddress, $username, $this->loginAttemptLogger, $justLocked);

            if ($justLocked) {
                echo json_encode([
                    'success' => false,
                    'code' => 'account_locked',
                    'retry_after' => (int) ($failureState['retry_after'] ?? 900),
                    'message' => 'Too many failed sign-in attempts. This account is temporarily locked.'
                ]);
                return;
            }

            $attemptsRemaining = (int) ($failureState['attempts_remaining'] ?? 0);
            $message = 'Invalid username or password.';
            if ($attemptsRemaining > 0) {
                $message .= ' ' . $attemptsRemaining . ' attempt(s) remaining before temporary lockout.';
            }

            echo json_encode([
                'success' => false,
                'code' => 'invalid_credentials',
                'attempts_remaining' => $attemptsRemaining,
                'message' => $message
            ]);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
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

    /**
     * Handle registration request
     */
    public function register()
    {
        try {
            header('Content-Type: application/json');

            // Enable error display for debugging
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            return;
        }

        // Get form data
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '') ?: null;
        $sex = $_POST['sex'] ?? null; // Optional - will be filled in survey
        $birthdate = $_POST['birthdate'] ?? null; // Optional - will be filled in survey
        $marital_status = $_POST['marital_status'] ?? 'Single';

        // Log received data for debugging
        error_log("Registration attempt - Username: $username, Email: $email, First: $first_name, Last: $last_name");

        // Validate required fields (sex and birthdate are now optional)
        if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            return;
        }

        // Validate password match
        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }

        // Validate password strength (minimum 6 characters)
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            return;
        }

        // Validate email/contact format (accept both email and phone number)
        $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        $isPhone = preg_match('/^(09|\+639)\d{9}$/', $email); // Philippine mobile format

        if (!$isEmail && !$isPhone && strlen($email) < 3) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid email or contact number']);
            return;
        }

        // Check if username already exists (case-insensitive)
        if ($this->userModel->usernameExists($username)) {
            echo json_encode(['success' => false, 'message' => 'This username is already taken. Please choose another.']);
            return;
        }

        // Check if email/contact already exists (case-insensitive)
        if ($this->userModel->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'This email or contact number is already registered.']);
            return;
        }

        // Prepare person data
        $personData = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name ?: null,
            'suffix' => $suffix,
            'sex' => $sex,
            'birthdate' => $birthdate,
            'marital_status' => $marital_status
        ];

        // Handle face embedding and image
        $faceEmbedding = null;
        $faceImagePath = null;

        $faceEmbeddingRaw = trim($_POST['face_embedding'] ?? '');
        $faceImageB64     = trim($_POST['face_image_b64'] ?? '');

        if (!empty($faceEmbeddingRaw)) {
            $decoded = json_decode($faceEmbeddingRaw, true);
            if (is_array($decoded) && count($decoded) === 128) {
                $faceEmbedding = $faceEmbeddingRaw;
            }
        }

        // Save face image if provided
        if (!empty($faceImageB64) && $faceEmbedding) {
            try {
                $uploadDir = dirname(__DIR__) . '/uploads/faces/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                // Strip data URI prefix
                $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $faceImageB64);
                $imgBytes = base64_decode($imgData);
                if ($imgBytes !== false) {
                    $filename = 'face_' . uniqid('', true) . '.jpg';
                    file_put_contents($uploadDir . $filename, $imgBytes);
                    $faceImagePath = 'faces/' . $filename;
                }
            } catch (Exception $imgEx) {
                error_log('Face image save error: ' . $imgEx->getMessage());
            }
        }

        // Prepare user data
        $userData = [
            'username'       => $username,
            'email'          => $email,
            'mobile'         => $mobile ?: null,
            'password_hash'  => password_hash($password, PASSWORD_DEFAULT),
            'face_embedding' => $faceEmbedding,
            'face_image_path' => $faceImagePath,
        ];

        // Create user
        try {
            error_log("About to create user with data: " . json_encode($personData) . " | " . json_encode($userData));
            $user_id = $this->userModel->create($personData, $userData);
            error_log("User creation returned ID: " . ($user_id ? $user_id : 'false'));

            if ($user_id) {
                // Get the created user details
                $user = $this->userModel->findByUsername($username);
                error_log("Found user after creation: " . ($user ? json_encode($user) : 'null'));

                if ($user) {
                    // Set session variables and auto-login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['person_id'] = $user['person_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['full_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name'] . ' ' . ($user['suffix'] ?? ''));
                    $_SESSION['email'] = $user['email'] ?? '';
                    $_SESSION['mobile'] = $user['mobile'] ?? '';
                    $_SESSION['user_type'] = 'user';
                    $_SESSION['logged_in'] = true;

                    // Update last login
                    $this->userModel->updateLastLogin($user['id']);

                    $redirectUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_resident';
                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Redirecting to dashboard...',
                        'redirect' => $redirectUrl
                    ]);
                } else {
                    error_log("ERROR: User created but not found in database");
                    echo json_encode(['success' => false, 'message' => 'Registration completed but unable to retrieve user data.']);
                }
            } else {
                error_log("ERROR: User creation returned false");
                echo json_encode(['success' => false, 'message' => 'Registration failed. Please check all fields and try again.']);
            }
        } catch (Exception $e) {
            error_log("EXCEPTION during user creation: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
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
        $isXhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isXhr) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Logged out']);
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

// Route the request
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
