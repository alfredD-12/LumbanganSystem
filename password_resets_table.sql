-- Password Reset Feature - Database Migration
-- Run this SQL in your database to create the password_resets table
-- 
-- Steps:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Select your Lumbangan database
-- 3. Click SQL tab
-- 4. Copy and paste this entire file
-- 5. Click Go/Execute
--
-- After running this, the forget password feature will work!

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL UNIQUE,
  `token` varchar(255) NOT NULL UNIQUE,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_code` (`user_id`, `code`),
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`token`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table created successfully!
-- This table stores:
-- - user_id: Links to the users table
-- - email: User's email for password reset
-- - code: 6-digit reset code
-- - token: 32-byte random token
-- - expires_at: Code expires after 1 hour
-- - used_at: Tracks if code was already used (one-time use)
-- - created_at: When the reset was requested
