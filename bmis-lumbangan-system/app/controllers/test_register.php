<?php
// Simple test script to debug registration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Registration Debug Test ===\n\n";

// Test 1: Include files
echo "1. Testing file includes...\n";
try {
    require_once dirname(__DIR__) . '/config/Database.php';
    echo "   ✓ Database.php included\n";
    require_once dirname(__DIR__) . '/models/User.php';
    echo "   ✓ User.php included\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Database connection
echo "\n2. Testing database connection...\n";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "   ✓ Database connected\n";
    echo "   Connection type: " . get_class($db) . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Create User model
echo "\n3. Creating User model...\n";
try {
    $userModel = new User($db);
    echo "   ✓ User model created\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Test data
echo "\n4. Preparing test data...\n";
$personData = [
    'first_name' => 'Test',
    'last_name' => 'User',
    'middle_name' => 'Middle',
    'suffix' => null,
    'sex' => null,
    'birthdate' => null,
    'marital_status' => 'Single'
];

$userData = [
    'username' => 'testuser_' . time(),
    'email' => 'test_' . time() . '@example.com',
    'mobile' => null,
    'password_hash' => password_hash('password123', PASSWORD_DEFAULT)
];

echo "   Username: " . $userData['username'] . "\n";
echo "   Email: " . $userData['email'] . "\n";

// Test 5: Create user
echo "\n5. Creating user in database...\n";
try {
    $user_id = $userModel->create($personData, $userData);
    if ($user_id) {
        echo "   ✓ User created successfully!\n";
        echo "   User ID: " . $user_id . "\n";
        
        // Test 6: Retrieve user
        echo "\n6. Retrieving created user...\n";
        $user = $userModel->findByUsername($userData['username']);
        if ($user) {
            echo "   ✓ User retrieved successfully!\n";
            echo "   Data: " . json_encode($user, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "   ✗ User not found after creation\n";
        }
    } else {
        echo "   ✗ User creation failed (returned false)\n";
    }
} catch (Exception $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
