<?php
/**
 * Notifications API Endpoint
 * Handles fetching, marking as read, and deleting notifications
 * Works for both admin (officials) and residents (users)
 */

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/session_helper.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get user info from session
$user_id = getUserId();
$user_type = $_SESSION['user_type'] ?? null; // 'user' or 'official'

if (!$user_type) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid session']);
    exit;
}

// Initialize database
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? 'fetch';

try {
    switch ($action) {
        
        // ========================================================
        // FETCH NOTIFICATIONS
        // ========================================================
        case 'fetch':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            
            // Build query based on user type
            // Fetch notifications for:
            // 1. Specific user_id match AND user_type match (personal notifications for this exact user)
            // 2. user_id IS NULL AND user_type match (broadcast to all users of this type)
            // 3. user_id IS NULL AND user_type = 'all' (broadcast to everyone)
            // Exclude notifications that this user has deleted (soft delete)
            // Check read status per user via notification_reads table
            $sql = "SELECT n.*, 
                    IF(nr.id IS NOT NULL, 1, 0) as is_read,
                    nr.read_at
                    FROM notifications n
                    LEFT JOIN notification_deletions nd ON (
                        n.id = nd.notification_id 
                        AND nd.deleted_by_user_id = :user_id 
                        AND nd.deleted_by_user_type = :user_type
                    )
                    LEFT JOIN notification_reads nr ON (
                        n.id = nr.notification_id
                        AND nr.read_by_user_id = :user_id
                        AND nr.read_by_user_type = :user_type
                    )
                    WHERE nd.id IS NULL
                    AND (
                        (n.user_id = :user_id AND n.user_type = :user_type)
                        OR (n.user_id IS NULL AND (n.user_type = :user_type OR n.user_type = 'all'))
                    )";
            
            if ($unread_only) {
                $sql .= " AND nr.id IS NULL";
            }
            
            $sql .= " ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get unread count (excluding deleted notifications)
            // A notification is unread if there's no record in notification_reads for this user
            $count_sql = "SELECT COUNT(*) as count FROM notifications n
                         LEFT JOIN notification_deletions nd ON (
                             n.id = nd.notification_id 
                             AND nd.deleted_by_user_id = :user_id 
                             AND nd.deleted_by_user_type = :user_type
                         )
                         LEFT JOIN notification_reads nr ON (
                             n.id = nr.notification_id
                             AND nr.read_by_user_id = :user_id
                             AND nr.read_by_user_type = :user_type
                         )
                         WHERE nd.id IS NULL
                         AND nr.id IS NULL
                         AND (
                             (n.user_id = :user_id AND n.user_type = :user_type)
                             OR (n.user_id IS NULL AND (n.user_type = :user_type OR n.user_type = 'all'))
                         )";
            $count_stmt = $db->prepare($count_sql);
            $count_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $count_stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $count_stmt->execute();
            $unread_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$unread_count,
                'user_type' => $user_type
            ]);
            break;
        
        // ========================================================
        // MARK AS READ
        // ========================================================
        case 'mark_read':
            $notification_id = $_POST['id'] ?? null;
            
            if (!$notification_id) {
                throw new Exception('Notification ID required');
            }
            
            // Verify notification exists and user has access to it
            $verify_sql = "SELECT id FROM notifications 
                          WHERE id = :id 
                          AND (
                              (user_id = :user_id AND user_type = :user_type)
                              OR (user_id IS NULL AND (user_type = :user_type OR user_type = 'all'))
                          )";
            $verify_stmt = $db->prepare($verify_sql);
            $verify_stmt->bindValue(':id', $notification_id, PDO::PARAM_INT);
            $verify_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $verify_stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $verify_stmt->execute();
            
            if ($verify_stmt->rowCount() === 0) {
                throw new Exception('Notification not found or access denied');
            }
            
            // Insert read record (INSERT IGNORE prevents duplicate entries)
            $sql = "INSERT IGNORE INTO notification_reads 
                    (notification_id, read_by_user_id, read_by_user_type) 
                    VALUES (:notification_id, :user_id, :user_type)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':notification_id', $notification_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            break;
        
        // ========================================================
        // MARK ALL AS READ
        // ========================================================
        case 'mark_all_read':
            // Get all notification IDs that this user can see and haven't been deleted
            $fetch_sql = "SELECT n.id FROM notifications n
                         LEFT JOIN notification_deletions nd ON (
                             n.id = nd.notification_id 
                             AND nd.deleted_by_user_id = :user_id 
                             AND nd.deleted_by_user_type = :user_type
                         )
                         LEFT JOIN notification_reads nr ON (
                             n.id = nr.notification_id
                             AND nr.read_by_user_id = :user_id
                             AND nr.read_by_user_type = :user_type
                         )
                         WHERE nd.id IS NULL
                         AND nr.id IS NULL
                         AND (
                             (n.user_id = :user_id AND n.user_type = :user_type)
                             OR (n.user_id IS NULL AND (n.user_type = :user_type OR n.user_type = 'all'))
                         )";
            
            $fetch_stmt = $db->prepare($fetch_sql);
            $fetch_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $fetch_stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $fetch_stmt->execute();
            $notification_ids = $fetch_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $affected = 0;
            if (!empty($notification_ids)) {
                // Insert read records for all unread notifications
                $placeholders = str_repeat('(?, ?, ?),', count($notification_ids) - 1) . '(?, ?, ?)';
                $sql = "INSERT IGNORE INTO notification_reads 
                        (notification_id, read_by_user_id, read_by_user_type) 
                        VALUES $placeholders";
                
                $stmt = $db->prepare($sql);
                $params = [];
                foreach ($notification_ids as $notif_id) {
                    $params[] = $notif_id;
                    $params[] = $user_id;
                    $params[] = $user_type;
                }
                $stmt->execute($params);
                $affected = $stmt->rowCount();
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Marked {$affected} notification(s) as read"
            ]);
            break;
        
        // ========================================================
        // DELETE NOTIFICATION (SOFT DELETE)
        // ========================================================
        case 'delete':
            $notification_id = $_POST['id'] ?? null;
            
            if (!$notification_id) {
                throw new Exception('Notification ID required');
            }
            
            // Verify notification exists and user has access to it
            $verify_sql = "SELECT id FROM notifications 
                          WHERE id = :id 
                          AND (
                              (user_id = :user_id AND user_type = :user_type)
                              OR (user_id IS NULL AND (user_type = :user_type OR user_type = 'all'))
                          )";
            $verify_stmt = $db->prepare($verify_sql);
            $verify_stmt->bindValue(':id', $notification_id, PDO::PARAM_INT);
            $verify_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $verify_stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $verify_stmt->execute();
            
            if ($verify_stmt->rowCount() === 0) {
                throw new Exception('Notification not found or access denied');
            }
            
            // Soft delete: Insert into notification_deletions table
            // This hides the notification only for this specific user
            $sql = "INSERT IGNORE INTO notification_deletions 
                    (notification_id, deleted_by_user_id, deleted_by_user_type) 
                    VALUES (:notification_id, :user_id, :user_type)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':notification_id', $notification_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Notification deleted for you only'
            ]);
            break;
        
        // ========================================================
        // GET UNREAD COUNT ONLY (for badge)
        // ========================================================
        case 'count':
            // Count only notifications not deleted by this user and not read by this user
            $count_sql = "SELECT COUNT(*) as count FROM notifications n
                         LEFT JOIN notification_deletions nd ON (
                             n.id = nd.notification_id 
                             AND nd.deleted_by_user_id = :user_id 
                             AND nd.deleted_by_user_type = :user_type
                         )
                         LEFT JOIN notification_reads nr ON (
                             n.id = nr.notification_id
                             AND nr.read_by_user_id = :user_id
                             AND nr.read_by_user_type = :user_type
                         )
                         WHERE nd.id IS NULL
                         AND nr.id IS NULL
                         AND (
                             (n.user_id = :user_id AND n.user_type = :user_type)
                             OR (n.user_id IS NULL AND (n.user_type = :user_type OR n.user_type = 'all'))
                         )";
            $count_stmt = $db->prepare($count_sql);
            $count_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $count_stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
            $count_stmt->execute();
            $unread_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'success' => true,
                'unread_count' => (int)$unread_count
            ]);
            break;
        
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
