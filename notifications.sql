-- ========================================================
-- BARANGAY LUMBANGAN NOTIFICATION SYSTEM
-- Database Schema and Triggers
-- ========================================================
-- This file creates the notification infrastructure without
-- modifying existing controllers. Compatible with lumbangan.sql
-- ========================================================

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'NULL = notify all users',
  `user_type` ENUM('user','official','all') NOT NULL DEFAULT 'all' COMMENT 'Target user type: user=resident, official=admin/official, all=everyone',
  `notification_type` VARCHAR(50) NOT NULL COMMENT 'Type: announcement, complaint, document_request',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(500) NULL DEFAULT NULL COMMENT 'URL to navigate when clicked',
  `reference_id` INT(11) NULL DEFAULT NULL COMMENT 'ID of the related record',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_type` (`user_id`, `user_type`),
  INDEX `idx_created` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System notifications for residents and officials';

-- ========================================================
-- Create notification_reads table
-- Tracks which users have read which notifications
-- This allows per-user read status - same notification can be read by one user but unread for another
-- ========================================================
CREATE TABLE IF NOT EXISTS `notification_reads` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_id` BIGINT(20) UNSIGNED NOT NULL,
  `read_by_user_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'User who read this notification',
  `read_by_user_type` ENUM('user','official') NOT NULL COMMENT 'Type of user who read',
  `read_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_read` (`notification_id`, `read_by_user_id`, `read_by_user_type`),
  INDEX `idx_notification_id` (`notification_id`),
  INDEX `idx_read_by` (`read_by_user_id`, `read_by_user_type`),
  FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks read status per user for each notification';

-- ========================================================
-- Create notification_deletions table
-- Tracks which users have deleted which notifications
-- This allows soft delete - notification remains for others
-- ========================================================
CREATE TABLE IF NOT EXISTS `notification_deletions` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_id` BIGINT(20) UNSIGNED NOT NULL,
  `deleted_by_user_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'User who deleted this notification',
  `deleted_by_user_type` ENUM('user','official') NOT NULL COMMENT 'Type of user who deleted',
  `deleted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_notification` (`notification_id`, `deleted_by_user_id`, `deleted_by_user_type`),
  INDEX `idx_notification_id` (`notification_id`),
  INDEX `idx_deleted_by` (`deleted_by_user_id`, `deleted_by_user_type`),
  FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks soft-deleted notifications per user';

-- ========================================================
-- TRIGGERS FOR AUTOMATIC NOTIFICATION CREATION
-- ========================================================

DELIMITER $$

-- --------------------------------------------------------
-- Trigger 1: New Announcement Notification
-- Notifies users based on announcement audience
-- audience = 'all' -> notify everyone
-- audience = 'residents' -> notify residents (user_type = 'user')
-- audience = 'officials' -> notify officials (user_type = 'official')
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS `notify_new_announcement`$$
CREATE TRIGGER `notify_new_announcement`
AFTER INSERT ON `announcements`
FOR EACH ROW
BEGIN
    DECLARE target_type VARCHAR(10);
    
    IF NEW.status = 'published' THEN
        -- Map announcement audience to notification user_type
        SET target_type = CASE NEW.audience
            WHEN 'residents' THEN 'user'
            WHEN 'officials' THEN 'official'
            ELSE 'all'
        END;
        
        -- Notify targeted audience
        INSERT INTO `notifications` 
        (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
        VALUES 
        (NULL, target_type, 'announcement', 
         CONCAT('New Announcement: ', NEW.title),
         LEFT(NEW.message, 150),
         CONCAT('?page=public_announcement#announcement-', NEW.id),
         NEW.id);
    END IF;
END$$

-- --------------------------------------------------------
-- Trigger 2: New Complaint/Incident Notification
-- Notifies officials when a new complaint is filed
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS `notify_new_complaint`$$
CREATE TRIGGER `notify_new_complaint`
AFTER INSERT ON `incidents`
FOR EACH ROW
BEGIN
    -- Notify officials only (admins handle complaints)
    INSERT INTO `notifications` 
    (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
    VALUES 
    (NULL, 'official', 'complaint', 
     CONCAT('New Complaint: ', NEW.incident_title),
     CONCAT('From: ', NEW.complainant_name, ' - ', LEFT(NEW.narrative, 100)),
     CONCAT('?page=admin_complaints#incident-', NEW.id),
     NEW.id);
END$$

-- --------------------------------------------------------
-- Trigger 3: Complaint Status Update Notification
-- Notifies residents when their complaint status changes
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS `notify_complaint_update`$$
CREATE TRIGGER `notify_complaint_update`
AFTER UPDATE ON `incidents`
FOR EACH ROW
BEGIN
    DECLARE status_text VARCHAR(50);
    
    -- Only notify if status changed
    IF OLD.status_id <> NEW.status_id THEN
        -- Map status_id to readable text (adjust based on your status table)
        SET status_text = CASE NEW.status_id
            WHEN 1 THEN 'Pending'
            WHEN 2 THEN 'Under Investigation'
            WHEN 3 THEN 'Resolved'
            WHEN 4 THEN 'Closed'
            ELSE 'Updated'
        END;
        
        -- Notify officials about status change
        INSERT INTO `notifications` 
        (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
        VALUES 
        (NULL, 'official', 'complaint', 
         CONCAT('Complaint Status Updated: ', NEW.incident_title),
         CONCAT('Status changed to: ', status_text),
         CONCAT('?page=admin_complaints#incident-', NEW.id),
         NEW.id);
    END IF;
END$$

-- --------------------------------------------------------
-- Trigger 4: New Document Request Notification
-- Notifies officials when a resident requests a document
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS `notify_new_document_request`$$
CREATE TRIGGER `notify_new_document_request`
AFTER INSERT ON `document_requests`
FOR EACH ROW
BEGIN
    DECLARE doc_name VARCHAR(100);
    DECLARE requester_name VARCHAR(150);
    
    -- Get document type name
    SELECT document_name INTO doc_name 
    FROM document_types 
    WHERE document_type_id = NEW.document_type_id 
    LIMIT 1;
    
    -- Get requester name from users table
    SELECT CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, ''))
    INTO requester_name
    FROM users u
    LEFT JOIN persons p ON u.person_id = p.id
    WHERE u.id = NEW.user_id
    LIMIT 1;
    
    -- Notify officials
    INSERT INTO `notifications` 
    (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
    VALUES 
    (NULL, 'official', 'document_request', 
     CONCAT('New Document Request: ', COALESCE(doc_name, 'Document')),
     CONCAT('Requested by: ', COALESCE(requester_name, 'User'), ' - Purpose: ', COALESCE(NEW.purpose, 'N/A')),
     CONCAT('?page=admin_document_requests#request-', NEW.request_id),
     NEW.request_id);
END$$

-- --------------------------------------------------------
-- Trigger 5: Document Request Status Update Notification
-- Notifies residents when their document request status changes
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS `notify_document_status`$$
CREATE TRIGGER `notify_document_status`
AFTER UPDATE ON `document_requests`
FOR EACH ROW
BEGIN
    DECLARE doc_name VARCHAR(100);
    
    -- Only notify if status changed
    IF OLD.status <> NEW.status THEN
        -- Get document type name
        SELECT document_name INTO doc_name 
        FROM document_types 
        WHERE document_type_id = NEW.document_type_id 
        LIMIT 1;
        
        -- Notify the specific user who made the request
        INSERT INTO `notifications` 
        (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
        VALUES 
        (NEW.user_id, 'user', 'document_request', 
         CONCAT('Document Request ', NEW.status),
         CONCAT('Your request for ', COALESCE(doc_name, 'document'), ' has been ', LOWER(NEW.status)),
         CONCAT('?page=document_request#request-', NEW.request_id),
         NEW.request_id);
         
        -- Also notify officials
        INSERT INTO `notifications` 
        (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
        VALUES 
        (NULL, 'official', 'document_request', 
         CONCAT('Document Request ', NEW.status),
         CONCAT('Request #', NEW.request_id, ' for ', COALESCE(doc_name, 'document'), ' - ', NEW.status),
         CONCAT('?page=admin_document_requests#request-', NEW.request_id),
         NEW.request_id);
    END IF;
END$$

DELIMITER ;

-- ========================================================
-- VERIFICATION QUERIES (Optional - for testing)
-- ========================================================
-- Run these after installation to verify:
-- SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;
-- SELECT * FROM notification_reads ORDER BY read_at DESC LIMIT 10;
-- SELECT user_type, COUNT(*) as count FROM notifications GROUP BY user_type;
