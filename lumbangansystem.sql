
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `account_lockouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_lockouts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `consecutive_failures` int unsigned NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `lockout_count` int unsigned NOT NULL DEFAULT '0',
  `last_failure_at` datetime DEFAULT NULL,
  `last_success_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `angina_stroke_screening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `angina_stroke_screening` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint unsigned NOT NULL,
  `q1_chest_discomfort` tinyint(1) DEFAULT NULL,
  `q2_pain_location_left_arm_neck_back` tinyint(1) DEFAULT NULL,
  `q3_pain_on_exertion` tinyint(1) DEFAULT NULL,
  `q4_pain_relieved_by_rest_or_nitro` tinyint(1) DEFAULT NULL,
  `q5_pain_lasting_10min_plus` tinyint(1) DEFAULT NULL,
  `q6_pain_front_of_chest_half_hour` tinyint(1) DEFAULT NULL,
  `screen_positive` tinyint(1) DEFAULT NULL,
  `needs_doctor_referral` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_angina_cvd` (`cvd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `audience` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'all',
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'published',
  `type` varchar(64) COLLATE utf8mb4_general_ci DEFAULT 'general',
  `expires_at` datetime DEFAULT NULL,
  `author` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `notify_new_announcement` AFTER INSERT ON `announcements` FOR EACH ROW BEGIN

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

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `notify_announcement_update` AFTER UPDATE ON `announcements` FOR EACH ROW BEGIN

    DECLARE target_type VARCHAR(10);

    DECLARE old_target_type VARCHAR(10);

    

    -- Only process if announcement is published

    IF NEW.status = 'published' THEN

        -- Map NEW audience to notification user_type

        SET target_type = CASE NEW.audience

            WHEN 'residents' THEN 'user'

            WHEN 'officials' THEN 'official'

            ELSE 'all'

        END;

        

        -- Map OLD audience to notification user_type

        SET old_target_type = CASE OLD.audience

            WHEN 'residents' THEN 'user'

            WHEN 'officials' THEN 'official'

            ELSE 'all'

        END;

        

        -- If audience changed, delete old notifications and create new ones

        IF OLD.audience <> NEW.audience THEN

            -- Delete old notifications for the previous audience

            DELETE FROM `notifications` 

            WHERE notification_type = 'announcement' 

            AND reference_id = NEW.id 

            AND user_type = old_target_type;

            

            -- Create new notification for the new audience

            INSERT INTO `notifications` 

            (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)

            VALUES 

            (NULL, target_type, 'announcement', 

             CONCAT('New Announcement: ', NEW.title),

             LEFT(NEW.message, 150),

             CONCAT('?page=public_announcement#announcement-', NEW.id),

             NEW.id);

        

        -- If audience didn't change but title or message changed, update existing notification

        ELSEIF OLD.title <> NEW.title OR OLD.message <> NEW.message THEN

            UPDATE `notifications` 

            SET title = CONCAT('Updated Announcement: ', NEW.title),

                message = LEFT(NEW.message, 150)

            WHERE notification_type = 'announcement' 

            AND reference_id = NEW.id 

            AND user_type = target_type;

        END IF;

    

    -- If status changed from published to draft/archived, delete all notifications

    ELSEIF OLD.status = 'published' AND NEW.status <> 'published' THEN

        DELETE FROM `notifications` 

        WHERE notification_type = 'announcement' 

        AND reference_id = NEW.id;

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `births`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `births` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mother_id` bigint unsigned NOT NULL,
  `child_person_id` bigint unsigned DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_place` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attendant` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `outcome` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birth_weight_kg` decimal(4,2) DEFAULT NULL,
  `sex` enum('M','F') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `brute_force_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brute_force_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_type` enum('ip_threshold','account_lockout','distributed_attack') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempt_count` int unsigned NOT NULL,
  `alert_sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_sent` tinyint(1) NOT NULL DEFAULT '0',
  `details` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_alert_type_time` (`alert_type`,`alert_sent_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `case_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `case_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cvd_ncd_risk_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cvd_ncd_risk_assessments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `answered_at` datetime DEFAULT NULL,
  `surveyed_by_official_id` bigint unsigned DEFAULT NULL,
  `survey_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by_official_id` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `review_notes` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cvd_person_date` (`person_id`,`survey_date`),
  KEY `idx_cvd_is_approved` (`is_approved`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `deaths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deaths` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned DEFAULT NULL,
  `name_free` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `age_years` int DEFAULT NULL,
  `cause_of_death` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `place_of_death` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `death_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diabetes_screening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diabetes_screening` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint unsigned NOT NULL,
  `known_diabetes` tinyint(1) DEFAULT NULL,
  `on_medications` tinyint(1) DEFAULT NULL,
  `family_history` tinyint(1) DEFAULT NULL,
  `polyuria` tinyint(1) DEFAULT NULL,
  `polydipsia` tinyint(1) DEFAULT NULL,
  `polyphagia` tinyint(1) DEFAULT NULL,
  `weight_loss` tinyint(1) DEFAULT NULL,
  `rbs_mg_dl` decimal(6,2) DEFAULT NULL,
  `fbs_mg_dl` decimal(6,2) DEFAULT NULL,
  `hba1c_percent` decimal(4,2) DEFAULT NULL,
  `urine_ketone` tinyint(1) DEFAULT NULL,
  `urine_protein` tinyint(1) DEFAULT NULL,
  `screen_positive` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_diabetes_cvd` (`cvd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `document_type_id` int NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Released','Rejected') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `pdf_file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approval_date` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_general_ci,
  `proof_upload` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `released_by` int DEFAULT NULL,
  `requested_for` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `relation_to_requestee` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `notify_new_document_request` AFTER INSERT ON `document_requests` FOR EACH ROW BEGIN

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

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `notify_document_status` AFTER UPDATE ON `document_requests` FOR EACH ROW BEGIN

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

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `document_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_type_id` int NOT NULL,
  `template_html` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_type_id` (`document_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_types` (
  `document_type_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `document_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `requirements` text COLLATE utf8mb4_general_ci,
  `fee` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`document_type_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `document_types_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Stores first_name, last_name, middle_name, suffix, sex, birthdate, marital_status',
  `user_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Stores username, email, mobile, password_hash',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT ((now() + interval 1 hour)),
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_code` (`code`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_verified_at` (`verified_at`),
  KEY `idx_pending` (`email`,`verified_at`,`expires_at`),
  CONSTRAINT `email_verifications_chk_1` CHECK (json_valid(`person_data`)),
  CONSTRAINT `email_verifications_chk_2` CHECK (json_valid(`user_data`))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores temporary email verification data during registration';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `families` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `household_id` bigint unsigned NOT NULL,
  `family_number` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `head_person_id` bigint unsigned DEFAULT NULL,
  `residency_status` enum('Permanent','Temporary') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `length_of_residency_months` int DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `survey_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_families_head_person` (`head_person_id`),
  KEY `idx_families_household` (`household_id`),
  KEY `idx_families_survey` (`survey_date`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `families_address_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `families_address_archive` (
  `family_id` bigint unsigned NOT NULL DEFAULT '0',
  `household_id` bigint unsigned NOT NULL,
  `complete_address` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `household_address` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `archived_at` datetime NOT NULL,
  KEY `idx_faa_household` (`household_id`),
  KEY `idx_faa_family` (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `image_path` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `health_family_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `health_family_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `hypertension` tinyint(1) DEFAULT NULL,
  `stroke` tinyint(1) DEFAULT NULL,
  `heart_attack` tinyint(1) DEFAULT NULL,
  `asthma` tinyint(1) DEFAULT NULL,
  `diabetes` tinyint(1) DEFAULT NULL,
  `cancer` tinyint(1) DEFAULT NULL,
  `kidney_disease` tinyint(1) DEFAULT NULL,
  `recorded_at` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hfh_person_date` (`person_id`,`recorded_at`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `households`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `households` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purok_id` bigint unsigned DEFAULT NULL,
  `household_no` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `home_ownership` enum('Owned','Rented','Others') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `home_ownership_other` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `construction_material` enum('Light','Strong','Mixed','Others') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `construction_material_other` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lighting_facility` enum('Electricity','Kerosene','Others') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lighting_facility_other` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `water_level` enum('Level I','Level II','Level III') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `water_source` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `water_storage` enum('Covered container','Uncovered container','None','Both') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `drinking_water_other_source` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `garbage_container` enum('Covered','Uncovered') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `garbage_segregated` tinyint(1) DEFAULT NULL,
  `garbage_disposal_method` enum('Hog Feeding','Burial Pit','Sanitary','Open Burning','Composting','Unsanitary','Open Dumping','Garbage Collection','Others','None') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `garbage_disposal_other` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `toilet_type` enum('Sanitary','Unsanitary','None','Others') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `toilet_type_other` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_households_purok` (`purok_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incidents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Foreign key to users table for registered complainants',
  `incident_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `blotter_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `case_type_id` int NOT NULL,
  `complainant_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complainant_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complainant_contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complainant_gender` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complainant_birthday` date DEFAULT NULL,
  `complainant_address` text COLLATE utf8mb4_unicode_ci,
  `offender_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offender_gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offender_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offender_address` text COLLATE utf8mb4_unicode_ci,
  `offender_description` text COLLATE utf8mb4_unicode_ci,
  `date_of_incident` date NOT NULL,
  `time_of_incident` time NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `narrative` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL COMMENT 'Timestamp when complaint was marked as resolved',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique_incident` (`incident_title`(100),`complainant_name`(100),`date_of_incident`,`location`(100)),
  KEY `idx_incident_status` (`status_id`),
  KEY `idx_incident_case_type` (`case_type_id`),
  KEY `idx_incident_date` (`date_of_incident`),
  KEY `idx_incident_created` (`created_at`),
  KEY `idx_incident_complainant` (`complainant_name`(100)),
  KEY `idx_incident_location` (`location`(100)),
  KEY `idx_incident_user_id` (`user_id`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`case_type_id`) REFERENCES `case_types` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `incidents_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `notify_new_complaint` AFTER INSERT ON `incidents` FOR EACH ROW BEGIN

    -- Notify officials only (admins handle complaints)

    INSERT INTO `notifications` 

    (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)

    VALUES 

    (NULL, 'official', 'complaint', 

     CONCAT('New Complaint: ', NEW.incident_title),

     CONCAT('From: ', NEW.complainant_name, ' - ', LEFT(NEW.narrative, 100)),

     CONCAT('?page=admin_complaints#incident-', NEW.id),

     NEW.id);

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `notify_complaint_update` AFTER UPDATE ON `incidents` FOR EACH ROW BEGIN

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

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `ip_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_rate_limits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempt_count` int unsigned NOT NULL DEFAULT '1',
  `window_start` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_attempt_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ip_address` (`ip_address`),
  KEY `idx_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lifestyle_risk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lifestyle_risk` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint unsigned NOT NULL,
  `smoking_status` enum('Never','Stopped_gt_1yr','Current','Stopped_lt_1yr','Passive') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `smoking_comments` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alcohol_use` enum('Never','Current','Former') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `excessive_alcohol` tinyint(1) DEFAULT NULL,
  `alcohol_notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `eats_processed_weekly` tinyint(1) DEFAULT NULL,
  `fruits_3_servings_daily` tinyint(1) DEFAULT NULL,
  `vegetables_3_servings_daily` tinyint(1) DEFAULT NULL,
  `exercise_days_per_week` int DEFAULT NULL,
  `exercise_minutes_per_day` int DEFAULT NULL,
  `exercise_intensity` enum('Light','Moderate','Vigorous') COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lifestyle_cvd` (`cvd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt_result` enum('success','failure') COLLATE utf8mb4_unicode_ci NOT NULL,
  `failure_reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geolocation_hint` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username_time` (`username`,`attempted_at`),
  KEY `idx_ip_time` (`ip_address`,`attempted_at`),
  KEY `idx_result_time` (`attempt_result`,`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migration_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_predictions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned DEFAULT NULL,
  `timeframe` enum('day','month','year') COLLATE utf8mb4_general_ci NOT NULL,
  `prediction` tinyint(1) NOT NULL,
  `probability` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `model_version` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `migration_predictions_chk_1` CHECK (json_valid(`probability`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `from_purok_id` bigint unsigned DEFAULT NULL,
  `to_purok_id` bigint unsigned DEFAULT NULL,
  `moved_at` date NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_migrations_person` (`person_id`),
  KEY `idx_migrations_from` (`from_purok_id`),
  KEY `idx_migrations_to` (`to_purok_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ml_migration_dataset_all`;
/*!50001 DROP VIEW IF EXISTS `ml_migration_dataset_all`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `ml_migration_dataset_all` AS SELECT 
 1 AS `migration_id`,
 1 AS `person_id`,
 1 AS `birthdate`,
 1 AS `sex`,
 1 AS `household_id`,
 1 AS `household_size`,
 1 AS `age`,
 1 AS `to_purok_id`,
 1 AS `from_purok_id`,
 1 AS `moved_at`,
 1 AS `reason`,
 1 AS `is_synthetic`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `morbidity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `morbidity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_date` date NOT NULL,
  `person_id` bigint unsigned DEFAULT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `purok_id` bigint unsigned DEFAULT NULL,
  `name_free` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `age_years` int DEFAULT NULL,
  `diagnosis` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_morbidity_person` (`person_id`),
  KEY `idx_morbidity_household` (`household_id`),
  KEY `idx_morbidity_purok` (`purok_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification_deletions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_deletions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` bigint unsigned NOT NULL,
  `deleted_by_user_id` bigint unsigned NOT NULL COMMENT 'User who deleted this notification',
  `deleted_by_user_type` enum('user','official') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Type of user who deleted',
  `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_notification` (`notification_id`,`deleted_by_user_id`,`deleted_by_user_type`),
  KEY `idx_notification_id` (`notification_id`),
  KEY `idx_deleted_by` (`deleted_by_user_id`,`deleted_by_user_type`),
  CONSTRAINT `notification_deletions_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks soft-deleted notifications per user';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_reads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` bigint unsigned NOT NULL,
  `read_by_user_id` bigint unsigned NOT NULL COMMENT 'User who read this notification',
  `read_by_user_type` enum('user','official') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Type of user who read',
  `read_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_read` (`notification_id`,`read_by_user_id`,`read_by_user_type`),
  KEY `idx_notification_id` (`notification_id`),
  KEY `idx_read_by` (`read_by_user_id`,`read_by_user_type`),
  CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks read status per user for each notification';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'NULL = notify all users',
  `user_type` enum('user','official','all') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'all' COMMENT 'Target user type: user=resident, official=admin/official, all=everyone',
  `notification_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Type: announcement, complaint, document_request',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'URL to navigate when clicked',
  `reference_id` int DEFAULT NULL COMMENT 'ID of the related record',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_type` (`user_id`,`user_type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System notifications for residents and officials';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `officials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `officials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `role` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_no` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo_url` varchar(1024) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_officials_username` (`username`),
  KEY `idx_officials_role_active` (`role`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `unique_user_code` (`user_id`,`code`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_relationships` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `related_person_id` bigint unsigned NOT NULL,
  `relationship_type` enum('parent','child','spouse','other') COLLATE utf8mb4_general_ci NOT NULL,
  `family_id` bigint unsigned DEFAULT NULL,
  `is_inverse` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pr_direct` (`person_id`,`related_person_id`,`relationship_type`),
  KEY `idx_pr_person` (`person_id`,`relationship_type`),
  KEY `idx_pr_related` (`related_person_id`,`relationship_type`),
  KEY `idx_pr_family` (`family_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `family_id` bigint unsigned DEFAULT NULL,
  `household_id` bigint unsigned DEFAULT NULL,
  `last_name` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `suffix` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_head` tinyint(1) NOT NULL DEFAULT '0',
  `sex` enum('M','F') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `marital_status` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disability` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `highest_educ_attainment` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `occupation` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `religion` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_pregnant` tinyint(1) DEFAULT NULL,
  `is_deceased` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_persons_family` (`family_id`),
  KEY `idx_persons_name` (`last_name`,`first_name`),
  KEY `idx_persons_household` (`household_id`),
  CONSTRAINT `fk_persons_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pregnancies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pregnancies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `lmp_date` date DEFAULT NULL,
  `edc_date` date DEFAULT NULL,
  `gravidity` int DEFAULT NULL,
  `parity` int DEFAULT NULL,
  `aog_weeks` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pregnancies_person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `puroks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `puroks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `centroid_lat` decimal(9,6) DEFAULT NULL,
  `centroid_lng` decimal(9,6) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_purok_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resident_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resident_migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `from_purok_id` bigint unsigned DEFAULT NULL,
  `to_purok_id` bigint unsigned DEFAULT NULL,
  `moved_at` date NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `is_synthetic` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_moved_at` (`moved_at`),
  KEY `idx_person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `site_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_profile` (
  `id` tinyint unsigned NOT NULL,
  `barangay_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Lumbangan',
  `municipality_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Nasugbu',
  `province_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Batangas',
  `region_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Region IV-A (CALABARZON)',
  `psa_code` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address_line` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hotline` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `username` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobile` varchar(13) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','disabled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `face_embedding` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '128-float face descriptor from face-api.js',
  `face_image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Relative path to saved face capture, e.g. uploads/faces/42.jpg',
  `face_verified_at` datetime DEFAULT NULL,
  `face_enrolled` tinyint(1) NOT NULL DEFAULT '0',
  `face_attempts` int NOT NULL DEFAULT '0',
  `face_locked_until` datetime DEFAULT NULL,
  `face_registered_at` datetime DEFAULT NULL COMMENT 'Timestamp when face was first scanned and stored',
  `face_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = face scan step completed during signup',
  `face_consent_given` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = user explicitly consented to biometric data collection',
  `face_consent_at` datetime DEFAULT NULL COMMENT 'Timestamp when biometric consent was given',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_person` (`person_id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_mobile` (`mobile`),
  CONSTRAINT `users_chk_1` CHECK (json_valid(`face_embedding`))
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vitals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint unsigned NOT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `central_adiposity` tinyint(1) DEFAULT NULL,
  `raised_bp` tinyint(1) DEFAULT NULL,
  `raised_blood_sugar` tinyint(1) DEFAULT NULL,
  `dyslipidemia` tinyint(1) DEFAULT NULL,
  `waist_circumference_cm` decimal(5,2) DEFAULT NULL,
  `bp_systolic` int DEFAULT NULL,
  `bp_diastolic` int DEFAULT NULL,
  `pulse` int DEFAULT NULL,
  `respiratory_rate` int DEFAULT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `obesity_flag` tinyint(1) GENERATED ALWAYS AS ((case when ((`bmi` is not null) and (`bmi` >= 30)) then 1 else 0 end)) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vitals_cvd` (`cvd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP VIEW IF EXISTS `ml_migration_dataset_all`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `ml_migration_dataset_all` AS select `rm`.`id` AS `migration_id`,`p`.`id` AS `person_id`,`p`.`birthdate` AS `birthdate`,`p`.`sex` AS `sex`,`p`.`household_id` AS `household_id`,coalesce(`hh_count`.`total_members`,1) AS `household_size`,timestampdiff(YEAR,`p`.`birthdate`,`rm`.`moved_at`) AS `age`,`rm`.`to_purok_id` AS `to_purok_id`,`rm`.`from_purok_id` AS `from_purok_id`,`rm`.`moved_at` AS `moved_at`,`rm`.`reason` AS `reason`,`rm`.`is_synthetic` AS `is_synthetic` from ((`resident_migrations` `rm` join `persons` `p` on((`p`.`id` = `rm`.`person_id`))) left join (select `persons`.`household_id` AS `household_id`,count(0) AS `total_members` from `persons` group by `persons`.`household_id`) `hh_count` on((`hh_count`.`household_id` = `p`.`household_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

