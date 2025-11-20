<?php
/**
 * Dashboard Helper - Fetch dynamic data from database
 */

require_once dirname(__DIR__) . '/config/Database.php';

/**
 * Get user's pending document requests count
 */
function getPendingRequestsCount($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM document_requests 
                  WHERE user_id = :user_id AND status = 'Pending'";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get user's completed document requests count
 */
function getCompletedRequestsCount($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT COUNT(*) as count FROM document_requests 
                  WHERE user_id = :user_id AND status = 'Released'";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get user's account creation year
 */
function getMemberSinceYear($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT YEAR(created_at) as year FROM users WHERE id = :user_id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['year'] ?? date('Y');
    } catch (Exception $e) {
        return date('Y');
    }
}

/**
 * Get all user stats at once (more efficient)
 */
function getUserDashboardStats($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM document_requests WHERE user_id = :user_id AND status = 'Pending') as pending_requests,
                (SELECT COUNT(*) FROM document_requests WHERE user_id = :user_id AND status = 'Released') as completed_requests,
                (SELECT YEAR(created_at) FROM users WHERE id = :user_id) as member_since
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return [
            'pending_requests' => $result['pending_requests'] ?? 0,
            'completed_requests' => $result['completed_requests'] ?? 0,
            'member_since' => $result['member_since'] ?? date('Y')
        ];
    } catch (Exception $e) {
        return [
            'pending_requests' => 0,
            'completed_requests' => 0,
            'member_since' => date('Y')
        ];
    }
}
?>
