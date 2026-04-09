CREATE TABLE `login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `attempt_result` ENUM('success', 'failure') NOT NULL,
  `failure_reason` VARCHAR(100) DEFAULT NULL,
  `geolocation_hint` VARCHAR(100) DEFAULT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username_time` (`username`, `attempted_at`),
  INDEX `idx_ip_time` (`ip_address`, `attempted_at`),
  INDEX `idx_result_time` (`attempt_result`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `account_lockouts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `consecutive_failures` INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `lockout_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_failure_at` DATETIME DEFAULT NULL,
  `last_success_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  INDEX `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ip_rate_limits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempt_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `window_start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_attempt_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ip_address` (`ip_address`),
  INDEX `idx_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `brute_force_alerts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alert_type` ENUM('ip_threshold', 'account_lockout', 'distributed_attack') NOT NULL,
  `target` VARCHAR(64) NOT NULL,
  `attempt_count` INT UNSIGNED NOT NULL,
  `alert_sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
  `details` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_alert_type_time` (`alert_type`, `alert_sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
