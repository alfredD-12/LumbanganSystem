<?php
session_start();

// Include database and models
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Official.php';

class AuthController {
    private $db;
    private $userModel;
    private $officialModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->officialModel = new Official($this->db);
    }

    /**
     * Handle login request
     */
    public function login() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            return;
        }
/*
        // TEMPORARY: Backdoor login for testing
        if ($username === 'alf_red_c' && $password === '123456') {
            $_SESSION['user_id'] = 9999;
            $_SESSION['person_id'] = 9999;
            $_SESSION['username'] = 'alf_red_c';
            $_SESSION['first_name'] = 'Test';
            $_SESSION['full_name'] = 'Test User Account';
            $_SESSION['email'] = 'test@example.com';
            $_SESSION['mobile'] = '+63 912-345-6789';
            $_SESSION['user_type'] = 'user';
            $_SESSION['logged_in'] = true;

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user_type' => 'user',
                'redirect' => '../../views/Dashboard/dashboard.php'
            ]);
            return;
        }
*/
        // Try to find user first
        $user = $this->userModel->findByUsername($username);
        
        if ($user) {
            // User found - verify password
            if (password_verify($password, $user['password_hash'])) {
                // Check if user is active
                if ($user['status'] !== 'active') {
                    echo json_encode(['success' => false, 'message' => 'Your account has been disabled. Please contact the administrator.']);
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

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user_type' => 'user',
                    'redirect' => '../../views/Dashboard/dashboard.php'
                ]);
                return;
            }
        }

        // If user not found or password incorrect, try official
        $official = $this->officialModel->findByUsername($username);
        
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

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user_type' => 'official',
                    'redirect' => '../../views/Admin/admin_dashboard.php' // Create this later
                ]);
                return;
            }
        }

        // If we get here, credentials are invalid
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }

    /**
     * Handle registration request
     */
    public function register() {
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

        // Prepare user data
        $userData = [
            'username' => $username,
            'email' => $email,
            'mobile' => $mobile ?: null,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
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

                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Redirecting to dashboard...',
                        'redirect' => '../../views/Dashboard/dashboard.php'
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
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ../views/landing/landing.php');
        exit();
    }

    /**
     * Check if username is available (AJAX endpoint)
     */
    public function checkUsername() {
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
