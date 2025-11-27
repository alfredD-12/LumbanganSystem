<?php
/**
 * Get current logged-in user information
 * Used for auto-filling complaint forms when user is filing a complaint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/session_helper.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $userId = getUserId();
    
    // Get current user info
    $query = "SELECT 
                u.id as user_id,
                u.person_id,
                u.mobile,
                u.email,
                p.first_name,
                p.middle_name,
                p.last_name,
                p.suffix,
                p.sex,
                p.birthdate,
                CONCAT(
                    TRIM(p.first_name), 
                    ' ', 
                    TRIM(COALESCE(p.middle_name, '')),
                    IF(TRIM(COALESCE(p.middle_name, '')) != '', ' ', ''),
                    TRIM(p.last_name),
                    IF(TRIM(COALESCE(p.suffix, '')) != '', ' ', ''),
                    TRIM(COALESCE(p.suffix, ''))
                ) as full_name,
                h.address as household_address
              FROM users u
              INNER JOIN persons p ON u.person_id = p.id
              LEFT JOIN families f ON p.family_id = f.id
              LEFT JOIN households h ON f.household_id = h.id
              WHERE u.id = :user_id
              LIMIT 1";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Format response
    $response = [
        'user_id' => (int)$user['user_id'],
        'person_id' => (int)$user['person_id'],
        'full_name' => trim($user['full_name']),
        'first_name' => $user['first_name'],
        'middle_name' => $user['middle_name'],
        'last_name' => $user['last_name'],
        'suffix' => $user['suffix'],
        'mobile' => $user['mobile'],
        'email' => $user['email'],
        'gender' => strtolower($user['sex'] ?? ''),
        'birthdate' => $user['birthdate'],
        'address' => $user['household_address']
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Get user info error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve user information']);
}
