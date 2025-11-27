<?php
/**
 * API endpoint to search for registered users
 * Used for autofilling complaint forms
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
    
    // Get search query
    $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (empty($searchTerm)) {
        echo json_encode([]);
        exit;
    }
    
    // Search for users by name
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
              WHERE u.status = 'active'
                AND (
                    CONCAT(p.first_name, ' ', COALESCE(p.middle_name, ''), ' ', p.last_name) LIKE :search1
                    OR CONCAT(p.first_name, ' ', p.last_name) LIKE :search2
                    OR p.first_name LIKE :search3
                    OR p.last_name LIKE :search4
                )
              ORDER BY p.last_name, p.first_name
              LIMIT 20";
    
    $stmt = $pdo->prepare($query);
    $searchPattern = '%' . $searchTerm . '%';
    $stmt->bindParam(':search1', $searchPattern);
    $stmt->bindParam(':search2', $searchPattern);
    $stmt->bindParam(':search3', $searchPattern);
    $stmt->bindParam(':search4', $searchPattern);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format results
    $formattedResults = array_map(function($user) {
        return [
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
    }, $results);
    
    echo json_encode($formattedResults);
    
} catch (Exception $e) {
    error_log('User search error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}
