SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS brute_force_alerts;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS account_lockouts;
DROP TABLE IF EXISTS ip_rate_limits;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS officials;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS persons;

CREATE TABLE persons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    family_id BIGINT UNSIGNED DEFAULT NULL,
    household_id BIGINT UNSIGNED DEFAULT NULL,
    last_name VARCHAR(128) NOT NULL,
    first_name VARCHAR(128) NOT NULL,
    middle_name VARCHAR(128) DEFAULT NULL,
    suffix VARCHAR(32) DEFAULT NULL,
    is_head TINYINT(1) NOT NULL DEFAULT 0,
    sex ENUM('M', 'F') DEFAULT NULL,
    birthdate DATE DEFAULT NULL,
    marital_status VARCHAR(32) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    person_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(64) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    mobile VARCHAR(13) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    face_embedding LONGTEXT DEFAULT NULL,
    face_image_path VARCHAR(255) DEFAULT NULL,
    face_verified_at DATETIME DEFAULT NULL,
    face_enrolled TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_person (person_id),
    UNIQUE KEY uq_users_email (email),
    CONSTRAINT fk_users_person FOREIGN KEY (person_id) REFERENCES persons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE officials (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(64) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    role VARCHAR(64) NOT NULL,
    contact_no VARCHAR(64) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_officials_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_password_resets_email (email),
    UNIQUE KEY uq_password_resets_token (token),
    UNIQUE KEY uq_password_resets_user_code (user_id, code),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_verifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    token VARCHAR(64) NOT NULL,
    person_data LONGTEXT NOT NULL,
    user_data LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email_verifications_email (email),
    UNIQUE KEY uq_email_verifications_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ip_rate_limits (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 0,
    window_start DATETIME NOT NULL,
    last_attempt_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ip_rate_limits_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE account_lockouts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    consecutive_failures INT NOT NULL DEFAULT 0,
    lockout_count INT NOT NULL DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_failure_at DATETIME DEFAULT NULL,
    last_success_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_account_lockouts_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    attempt_result ENUM('success', 'failure') NOT NULL,
    failure_reason VARCHAR(100) DEFAULT NULL,
    geolocation_hint VARCHAR(100) DEFAULT NULL,
    attempted_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_login_attempts_username_attempted_at (username, attempted_at),
    KEY idx_login_attempts_ip_attempted_at (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE brute_force_alerts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    alert_type VARCHAR(100) NOT NULL,
    target VARCHAR(255) NOT NULL,
    attempt_count INT NOT NULL DEFAULT 0,
    alert_sent_at DATETIME NOT NULL,
    email_sent TINYINT(1) NOT NULL DEFAULT 0,
    details TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_brute_force_alerts_type_target_time (alert_type, target, alert_sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;
