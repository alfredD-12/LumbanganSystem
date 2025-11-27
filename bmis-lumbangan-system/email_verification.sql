-- Email Verification Table for User Registration
-- This table stores temporary email verification codes and registration data
-- until the user confirms their email address

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `person_data` json NOT NULL COMMENT 'Stores first_name, last_name, middle_name, suffix, sex, birthdate, marital_status',
  `user_data` json NOT NULL COMMENT 'Stores username, email, mobile, password_hash',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 1 HOUR),
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_code` (`code`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_verified_at` (`verified_at`),
  KEY `idx_pending` (`email`, `verified_at`, `expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores temporary email verification data during registration';

-- OPTIONAL: Enable Event Scheduler for automatic cleanup (if needed)
-- If you get "event scheduler is disabled" error, run this first:
-- SET GLOBAL event_scheduler = ON;
--
-- Then uncomment the event below:
/*
DELIMITER //
CREATE EVENT IF NOT EXISTS `cleanup_expired_email_verifications`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
  DELETE FROM `email_verifications`
  WHERE `expires_at` < NOW() AND `verified_at` IS NULL;
//
DELIMITER ;
*/

-- Alternative: Manual cleanup query (run this periodically via cron or manually)
-- DELETE FROM `email_verifications` WHERE `expires_at` < NOW() AND `verified_at` IS NULL;
