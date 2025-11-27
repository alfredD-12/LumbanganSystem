-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 03:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lumbangansystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `angina_stroke_screening`
--

CREATE TABLE `angina_stroke_screening` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cvd_id` bigint(20) UNSIGNED NOT NULL,
  `q1_chest_discomfort` tinyint(1) DEFAULT NULL,
  `q2_pain_location_left_arm_neck_back` tinyint(1) DEFAULT NULL,
  `q3_pain_on_exertion` tinyint(1) DEFAULT NULL,
  `q4_pain_relieved_by_rest_or_nitro` tinyint(1) DEFAULT NULL,
  `q5_pain_lasting_10min_plus` tinyint(1) DEFAULT NULL,
  `q6_pain_front_of_chest_half_hour` tinyint(1) DEFAULT NULL,
  `screen_positive` tinyint(1) DEFAULT NULL,
  `needs_doctor_referral` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `angina_stroke_screening`
--

INSERT INTO `angina_stroke_screening` (`id`, `cvd_id`, `q1_chest_discomfort`, `q2_pain_location_left_arm_neck_back`, `q3_pain_on_exertion`, `q4_pain_relieved_by_rest_or_nitro`, `q5_pain_lasting_10min_plus`, `q6_pain_front_of_chest_half_hour`, `screen_positive`, `needs_doctor_referral`, `created_at`) VALUES
(1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-22 11:19:00'),
(2, 2, 0, 1, 1, 0, 1, 0, 1, 1, '2025-10-05 09:10:00'),
(3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-01 09:11:00'),
(4, 4, 1, 1, 1, 0, 1, 1, 1, 1, '2025-09-15 14:25:00'),
(5, 5, 0, 0, 0, 0, 0, 0, 0, 0, '2025-08-20 08:35:00'),
(6, 6, 0, 0, 0, 0, 0, 0, 0, 0, '2025-07-05 10:08:00'),
(7, 7, 0, 1, 0, 0, 0, 0, 0, 0, '2025-11-10 13:18:00'),
(8, 8, 1, 1, 1, 1, 1, 1, 1, 1, '2025-10-03 09:55:00'),
(9, 9, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-03 16:45:00'),
(10, 10, 0, 0, 0, 0, 0, 0, 0, 0, '2025-08-11 07:58:00'),
(11, 11, 0, 0, 0, 0, 0, 0, 0, 0, '2025-07-29 12:05:00'),
(12, 12, 0, 1, 0, 0, 0, 0, 0, 0, '2025-11-05 09:10:00'),
(13, 13, 0, 0, 1, 0, 0, 0, 0, 0, '2025-10-08 14:05:00'),
(14, 14, 1, 0, 1, 0, 1, 0, 1, 1, '2025-09-18 11:15:00'),
(15, 15, 0, 0, 0, 0, 0, 0, 0, 0, '2025-08-02 10:15:00'),
(16, 16, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-12 09:50:00'),
(17, 17, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-20 15:25:00'),
(18, 18, 0, 1, 1, 0, 1, 0, 1, 1, '2025-10-30 08:05:00'),
(19, 19, 0, 0, 0, 0, 0, 0, 0, 0, '2025-09-25 13:38:00'),
(20, 20, 0, 0, 0, 0, 0, 0, 0, 0, '2025-08-28 07:25:00'),
(21, 24, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-24 19:01:32'),
(22, 25, 1, 1, 1, 1, 1, 1, 0, 0, '2025-11-26 09:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `audience` varchar(50) DEFAULT 'all',
  `status` varchar(50) DEFAULT 'published',
  `type` varchar(64) DEFAULT 'general',
  `expires_at` datetime DEFAULT NULL,
  `author` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `image`, `audience`, `status`, `type`, `expires_at`, `author`, `created_at`) VALUES
(1, 'hff', 'ugbh', NULL, 'all', 'published', 'project', NULL, 'Official', '2025-11-23 00:50:02'),
(2, 'adfdg', 'safad', NULL, 'all', 'published', 'event', NULL, 'Official', '2025-11-23 00:51:48'),
(3, 'ftgfgh', 'jgtykyu', NULL, 'all', 'published', 'general', NULL, 'Official', '2025-11-24 01:05:50');

--
-- Triggers `announcements`
--
DELIMITER $$
CREATE TRIGGER `notify_new_announcement` AFTER INSERT ON `announcements` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `births`
--

CREATE TABLE `births` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mother_id` bigint(20) UNSIGNED NOT NULL,
  `child_person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_place` varchar(255) DEFAULT NULL,
  `attendant` varchar(255) DEFAULT NULL,
  `outcome` varchar(64) DEFAULT NULL,
  `birth_weight_kg` decimal(4,2) DEFAULT NULL,
  `sex` enum('M','F') DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_types`
--

CREATE TABLE `case_types` (
  `id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_types`
--

INSERT INTO `case_types` (`id`, `label`, `created_at`) VALUES
(1, 'Criminal', '2025-11-18 18:53:01'),
(2, 'Civil', '2025-11-18 18:53:01'),
(3, 'Others', '2025-11-18 18:53:01');

-- --------------------------------------------------------

--
-- Table structure for table `cvd_ncd_risk_assessments`
--

CREATE TABLE `cvd_ncd_risk_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `answered_at` datetime DEFAULT NULL,
  `surveyed_by_official_id` bigint(20) UNSIGNED DEFAULT NULL,
  `survey_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by_official_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `review_notes` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cvd_ncd_risk_assessments`
--

INSERT INTO `cvd_ncd_risk_assessments` (`id`, `person_id`, `answered_at`, `surveyed_by_official_id`, `survey_date`, `notes`, `is_approved`, `approved_by_official_id`, `approved_at`, `review_notes`) VALUES
(1, 1, '2025-11-22 11:18:07', 2, '2025-11-22', 'Monthly check', 1, 19, '2025-11-22 12:00:00', 'OK'),
(2, 2, '2025-10-05 09:05:00', 3, '2025-10-05', 'October monthly - visit 1', 1, 20, '2025-10-05 10:00:00', 'Approved'),
(3, 2, '2025-10-27 14:12:00', 4, '2025-10-27', 'October monthly - visit 2', 1, 21, '2025-10-27 15:00:00', 'Approved - duplicate'),
(4, 3, '2025-10-01 09:10:00', 5, '2025-10-01', 'Initial entry', 0, NULL, NULL, NULL),
(5, 4, '2025-09-15 14:22:00', 6, '2025-09-15', 'Follow-up', 1, 22, '2025-09-15 15:00:00', 'Reviewed'),
(6, 5, '2025-08-20 08:30:00', 2, '2025-08-20', 'Monthly', 0, NULL, NULL, NULL),
(7, 6, '2025-07-05 10:05:00', 7, '2025-07-05', 'Baseline', 1, 23, '2025-07-05 11:00:00', 'OK'),
(8, 7, '2025-11-10 13:15:00', 8, '2025-11-10', 'Monthly', 0, NULL, NULL, 'Needs review'),
(9, 8, '2025-10-03 09:50:00', 9, '2025-10-03', 'October screening A', 1, 24, '2025-10-03 10:30:00', 'OK'),
(10, 8, '2025-10-21 11:20:00', 10, '2025-10-21', 'October screening B', 1, 25, '2025-10-21 12:00:00', 'OK - repeat'),
(11, 9, '2025-09-03 16:40:00', 2, '2025-09-03', 'Check', 0, NULL, NULL, NULL),
(12, 10, '2025-08-11 07:55:00', 1, '2025-08-11', 'Community visit', 1, 26, '2025-08-11 08:30:00', 'OK'),
(13, 11, '2025-07-29 12:00:00', 4, '2025-07-29', 'Baseline', 0, NULL, NULL, NULL),
(14, 12, '2025-11-05 09:05:00', 5, '2025-11-05', 'Monthly', 1, 27, '2025-11-05 10:00:00', NULL),
(15, 13, '2025-10-08 14:00:00', 6, '2025-10-08', 'October follow-up 1', 1, 28, '2025-10-08 15:00:00', 'OK'),
(16, 13, '2025-10-29 09:30:00', 3, '2025-10-29', 'October follow-up 2', 1, 29, '2025-10-29 10:15:00', 'OK'),
(17, 14, '2025-09-18 11:11:00', 11, '2025-09-18', 'Routine', 0, NULL, NULL, NULL),
(18, 15, '2025-08-02 10:10:00', 12, '2025-08-02', 'Monthly', 0, NULL, NULL, NULL),
(19, 16, '2025-10-12 09:45:00', 13, '2025-10-12', 'October visit', 1, 30, '2025-10-12 10:30:00', 'Approved'),
(20, 17, '2025-11-20 15:20:00', 14, '2025-11-20', 'Monthly', 0, NULL, NULL, NULL),
(21, 18, '2025-10-30 08:00:00', 15, '2025-10-30', 'Follow-up', 1, 19, '2025-10-30 09:00:00', 'Follow up required'),
(22, 19, '2025-09-25 13:33:00', 16, '2025-09-25', 'Community screening', 0, NULL, NULL, NULL),
(23, 20, '2025-08-28 07:22:00', 17, '2025-08-28', 'Monthly', 1, 21, '2025-08-28 08:00:00', NULL),
(24, 51, '2025-11-24 18:58:51', NULL, '2025-11-24', NULL, 0, NULL, NULL, NULL),
(25, 53, '2025-11-26 09:26:27', NULL, '2025-11-26', NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deaths`
--

CREATE TABLE `deaths` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name_free` varchar(255) DEFAULT NULL,
  `age_years` int(11) DEFAULT NULL,
  `cause_of_death` varchar(255) DEFAULT NULL,
  `place_of_death` varchar(255) DEFAULT NULL,
  `death_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diabetes_screening`
--

CREATE TABLE `diabetes_screening` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cvd_id` bigint(20) UNSIGNED NOT NULL,
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
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diabetes_screening`
--

INSERT INTO `diabetes_screening` (`id`, `cvd_id`, `known_diabetes`, `on_medications`, `family_history`, `polyuria`, `polydipsia`, `polyphagia`, `weight_loss`, `rbs_mg_dl`, `fbs_mg_dl`, `hba1c_percent`, `urine_ketone`, `urine_protein`, `screen_positive`, `created_at`) VALUES
(1, 1, 0, 0, 0, 0, 0, 0, 0, 95.50, 90.00, 5.20, NULL, NULL, NULL, '2025-11-25 14:30:09'),
(2, 2, 1, 1, 1, 1, 1, 0, 1, 180.00, 160.00, 9.20, 1, 1, 1, '2025-10-05 09:12:00'),
(3, 3, 0, 0, 0, 0, 0, 0, 0, 100.00, 95.00, 5.40, NULL, NULL, 0, '2025-10-01 09:12:00'),
(4, 4, 0, 0, 1, 0, 0, 0, 0, 110.00, 101.00, 6.10, NULL, NULL, 0, '2025-09-15 14:27:00'),
(5, 5, 0, 0, 0, 0, 0, 0, 0, 92.00, 88.00, 5.00, NULL, NULL, 0, '2025-08-20 08:33:00'),
(6, 6, 0, 0, 0, 0, 0, 0, 0, 98.00, 94.00, 5.30, NULL, NULL, 0, '2025-07-05 10:10:00'),
(7, 7, 0, 0, 1, 0, 0, 0, 0, 130.00, 120.00, 7.00, NULL, 1, 1, '2025-11-10 13:17:00'),
(8, 8, 0, 0, 0, 0, 0, 0, 0, 85.00, 80.00, 4.80, NULL, NULL, 0, '2025-10-03 09:58:00'),
(9, 9, 0, 0, 0, 0, 0, 0, 0, 105.00, 99.00, 5.60, NULL, NULL, 0, '2025-09-03 16:42:00'),
(10, 10, 0, 0, 1, 0, 0, 0, 0, 115.00, 108.00, 6.00, NULL, NULL, 0, '2025-08-11 07:59:00'),
(11, 11, 0, 0, 0, 0, 0, 0, 0, 97.00, 92.00, 5.10, NULL, NULL, 0, '2025-07-29 12:02:00'),
(12, 12, 0, 0, 0, 0, 0, 0, 0, 89.00, 85.00, 4.90, NULL, NULL, 0, '2025-11-05 09:08:00'),
(13, 13, 0, 0, 1, 0, 0, 0, 0, 140.00, 130.00, 7.50, NULL, 1, 1, '2025-10-08 14:03:00'),
(14, 14, 0, 0, 0, 0, 0, 0, 0, 102.00, 96.00, 5.50, NULL, NULL, 0, '2025-09-18 11:13:00'),
(15, 15, 0, 0, 0, 0, 0, 0, 0, 93.00, 88.00, 5.00, NULL, NULL, 0, '2025-08-02 10:12:00'),
(16, 16, 1, 1, 1, 1, 1, 1, 1, 190.00, 170.00, 10.10, 1, 1, 1, '2025-10-12 09:48:00'),
(17, 17, 0, 0, 0, 0, 0, 0, 0, 96.00, 91.00, 5.20, NULL, NULL, 0, '2025-11-20 15:22:00'),
(18, 18, 0, 0, 1, 0, 0, 0, 0, 125.00, 115.00, 6.90, NULL, NULL, 0, '2025-10-30 08:03:00'),
(19, 19, 0, 0, 0, 0, 0, 0, 0, 99.00, 94.00, 5.30, NULL, NULL, 0, '2025-09-25 13:35:00'),
(20, 20, 0, 0, 0, 0, 0, 0, 0, 88.00, 83.00, 4.80, NULL, NULL, 0, '2025-08-28 07:23:00'),
(21, 24, 0, 0, 0, 0, 0, 0, 0, 70.00, 70.00, 5.00, 0, 0, NULL, '2025-11-25 19:28:39'),
(24, 25, 1, 1, 1, 1, 1, 1, 1, 70.00, 70.00, 5.00, 1, 1, NULL, '2025-11-26 09:35:27');

-- --------------------------------------------------------

--
-- Table structure for table `document_categories`
--

CREATE TABLE `document_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Released','Rejected') DEFAULT 'Pending',
  `pdf_file_path` varchar(255) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_date` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `proof_upload` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `released_by` int(11) DEFAULT NULL,
  `requested_for` varchar(150) DEFAULT NULL,
  `relation_to_requestee` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `document_requests`
--
DELIMITER $$
CREATE TRIGGER `notify_document_status` AFTER UPDATE ON `document_requests` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `notify_new_document_request` AFTER INSERT ON `document_requests` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `template_html` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `document_type_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `document_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE `families` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `household_id` bigint(20) UNSIGNED NOT NULL,
  `family_number` varchar(64) DEFAULT NULL,
  `head_person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `residency_status` enum('Permanent','Temporary') DEFAULT NULL,
  `length_of_residency_months` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `survey_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `families`
--

INSERT INTO `families` (`id`, `household_id`, `family_number`, `head_person_id`, `residency_status`, `length_of_residency_months`, `email`, `survey_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'FAM-1', 1, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(2, 2, 'FAM-2', 3, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(3, 3, 'FAM-3', 5, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(4, 4, 'FAM-4', 7, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(5, 5, 'FAM-5', 11, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(6, 6, 'FAM-6', 15, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(7, 7, 'FAM-7', 16, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(8, 8, 'FAM-8', 23, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(9, 9, 'FAM-9', 27, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(10, 10, 'FAM-10', 31, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(11, 11, 'FAM-11', 34, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(12, 12, 'FAM-12', 38, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(13, 13, 'FAM-13', 41, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(14, 14, 'FAM-14', 43, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(15, 15, 'FAM-15', 46, 'Permanent', NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(16, 16, '0995-3373-692', 51, 'Permanent', 26, 'davidgludo@gmail.com', '2025-11-26', '2025-11-24 19:02:38', '2025-11-26 11:10:36'),
(31, 31, '0995-3373-693', 53, 'Permanent', 36, 'ley@gmail.com', '2025-11-26', '2025-11-26 11:11:25', '2025-11-26 11:11:25');

-- --------------------------------------------------------

--
-- Table structure for table `families_address_archive`
--

CREATE TABLE `families_address_archive` (
  `family_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `household_id` bigint(20) UNSIGNED NOT NULL,
  `complete_address` varchar(512) DEFAULT NULL,
  `household_address` varchar(512) DEFAULT NULL,
  `archived_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Camia', 'Bawal Lumabas!', '691e82f6cce52_1763607286.jpg', 0, 1, '2025-11-20 02:54:46', '2025-11-20 02:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `health_family_history`
--

CREATE TABLE `health_family_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `hypertension` tinyint(1) DEFAULT NULL,
  `stroke` tinyint(1) DEFAULT NULL,
  `heart_attack` tinyint(1) DEFAULT NULL,
  `asthma` tinyint(1) DEFAULT NULL,
  `diabetes` tinyint(1) DEFAULT NULL,
  `cancer` tinyint(1) DEFAULT NULL,
  `kidney_disease` tinyint(1) DEFAULT NULL,
  `recorded_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_family_history`
--

INSERT INTO `health_family_history` (`id`, `person_id`, `hypertension`, `stroke`, `heart_attack`, `asthma`, `diabetes`, `cancer`, `kidney_disease`, `recorded_at`) VALUES
(1, 1, 0, 0, 0, 0, 0, 0, 0, '2025-11-25'),
(2, 2, 1, 0, 0, 0, 1, 0, 0, '2025-10-05'),
(3, 3, 0, 0, 0, 0, 0, 0, 0, '2025-10-01'),
(4, 4, 1, 1, 0, 0, 0, 0, 0, '2025-09-15'),
(5, 5, 0, 0, 0, 0, 0, 0, 0, '2025-08-20'),
(6, 6, 0, 0, 0, 0, 0, 0, 0, '2025-07-05'),
(7, 7, 0, 0, 0, 0, 1, 0, 0, '2025-11-10'),
(8, 8, 1, 0, 0, 0, 0, 0, 0, '2025-10-03'),
(9, 9, 0, 0, 0, 0, 0, 0, 0, '2025-09-03'),
(10, 10, 0, 0, 0, 0, 0, 0, 0, '2025-08-11'),
(11, 11, 0, 0, 0, 0, 0, 0, 0, '2025-07-29'),
(12, 12, 0, 0, 0, 0, 0, 0, 0, '2025-11-05'),
(13, 13, 1, 0, 0, 0, 1, 0, 0, '2025-10-08'),
(14, 14, 0, 0, 0, 0, 0, 0, 0, '2025-09-18'),
(15, 15, 0, 0, 0, 0, 0, 0, 0, '2025-08-02'),
(16, 16, 1, 1, 0, 0, 1, 0, 0, '2025-10-12'),
(17, 17, 0, 0, 0, 0, 0, 0, 0, '2025-11-20'),
(18, 18, 0, 0, 0, 0, 0, 0, 0, '2025-10-30'),
(19, 19, 0, 0, 0, 0, 0, 0, 0, '2025-09-25'),
(20, 20, 0, 0, 0, 0, 0, 0, 0, '2025-08-28'),
(21, 51, 0, 0, 0, 0, 1, 0, 1, '2025-11-26'),
(22, 53, 0, 0, 0, 0, 1, 0, 1, '2025-11-26');

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `household_no` varchar(64) DEFAULT NULL,
  `address` varchar(512) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `home_ownership` enum('Owned','Rented','Others') DEFAULT NULL,
  `home_ownership_other` varchar(255) DEFAULT NULL,
  `construction_material` enum('Light','Strong','Mixed','Others') DEFAULT NULL,
  `construction_material_other` varchar(255) DEFAULT NULL,
  `lighting_facility` enum('Electricity','Kerosene','Others') DEFAULT NULL,
  `lighting_facility_other` varchar(255) DEFAULT NULL,
  `water_level` enum('Level I','Level II','Level III') DEFAULT NULL,
  `water_source` varchar(255) DEFAULT NULL,
  `water_storage` enum('Covered container','Uncovered container','None','Both') DEFAULT NULL,
  `drinking_water_other_source` varchar(255) DEFAULT NULL,
  `garbage_container` enum('Covered','Uncovered') DEFAULT NULL,
  `garbage_segregated` tinyint(1) DEFAULT NULL,
  `garbage_disposal_method` enum('Hog Feeding','Burial Pit','Sanitary','Open Burning','Composting','Unsanitary','Open Dumping','Garbage Collection','Others','None') DEFAULT NULL,
  `garbage_disposal_other` varchar(255) DEFAULT NULL,
  `toilet_type` enum('Sanitary','Unsanitary','None','Others') DEFAULT NULL,
  `toilet_type_other` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`id`, `purok_id`, `household_no`, `address`, `latitude`, `longitude`, `home_ownership`, `home_ownership_other`, `construction_material`, `construction_material_other`, `lighting_facility`, `lighting_facility_other`, `water_level`, `water_source`, `water_storage`, `drinking_water_other_source`, `garbage_container`, `garbage_segregated`, `garbage_disposal_method`, `garbage_disposal_other`, `toilet_type`, `toilet_type_other`, `created_at`, `updated_at`) VALUES
(1, 2, 'CA-001', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:44:48'),
(2, 2, 'CA-002', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:44:59'),
(3, 2, 'CA-003', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:02'),
(4, 2, 'CA-004', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:03'),
(5, 2, 'CA-005', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:05'),
(6, 2, 'CA-006', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:07'),
(7, 2, 'CA-007', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:08'),
(8, 2, 'CA-008', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:11'),
(9, 2, 'CA-009', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:12'),
(10, 2, 'CA-010', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:13'),
(11, 2, 'CA-011', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:15'),
(12, 2, 'CA-012', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:16'),
(13, 2, 'CA-013', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:19'),
(14, 2, 'CA-014', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:20'),
(15, 2, 'CA-015', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-24 10:59:44', '2025-11-24 18:45:21'),
(16, 2, 'CA-016', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, 'Owned', '', 'Strong', '', 'Electricity', '', 'Level III', '', 'Covered container', '', 'Covered', 1, 'Garbage Collection', '', 'Sanitary', '', '2025-11-24 19:02:38', '2025-11-24 19:02:38'),
(31, 2, 'CA-017', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, 'Owned', '', 'Strong', '', 'Electricity', '', 'Level III', '', 'Covered container', '', 'Covered', 1, 'Garbage Collection', '', 'Sanitary', '', '2025-11-26 11:11:25', '2025-11-26 11:11:25');

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL,
  `incident_title` varchar(255) NOT NULL,
  `blotter_type` varchar(50) NOT NULL,
  `case_type_id` int(11) NOT NULL,
  `complainant_name` varchar(255) NOT NULL,
  `complainant_type` varchar(50) NOT NULL,
  `complainant_contact` varchar(50) DEFAULT NULL,
  `complainant_gender` varchar(20) NOT NULL,
  `complainant_birthday` date DEFAULT NULL,
  `complainant_address` text DEFAULT NULL,
  `offender_type` varchar(50) DEFAULT NULL,
  `offender_gender` varchar(20) DEFAULT NULL,
  `offender_name` varchar(255) DEFAULT NULL,
  `offender_address` text DEFAULT NULL,
  `offender_description` text DEFAULT NULL,
  `date_of_incident` date NOT NULL,
  `time_of_incident` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `narrative` text NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL COMMENT 'Timestamp when complaint was marked as resolved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`id`, `incident_title`, `blotter_type`, `case_type_id`, `complainant_name`, `complainant_type`, `complainant_contact`, `complainant_gender`, `complainant_birthday`, `complainant_address`, `offender_type`, `offender_gender`, `offender_name`, `offender_address`, `offender_description`, `date_of_incident`, `time_of_incident`, `location`, `narrative`, `status_id`, `created_at`, `updated_at`, `resolved_at`) VALUES
(1, 'Illegal Parking', 'Complaint', 2, 'Gerald Mendoza', 'Resident', '09473428871', 'male', '2025-11-19', 'Sagbat Lumbangan', 'resident', 'female', 'Janna Mae', 'Elpaso', 'Near at sambungan house location', '2025-11-12', '10:50:00', 'Tabing bahay', 'Nakiparada si Janna sa tapat ng bahay nila Gerald', 1, '2025-11-19 00:46:38', '2025-11-19 00:46:38', NULL),
(2, 'Unpaid Rents', 'Complaint', 2, 'Aling Puring', 'Resident', '09342516635', 'male', '2025-11-10', 'Role Subdivision', 'resident', 'male', 'Joseph', 'Role Subdivision', 'Secret', '2025-11-19', '10:00:00', 'Role Covered Court', 'Ilang buwan na hindi nagbabayad si Joesph kaya binabarangay na ni Aling Puring', 3, '2025-11-19 01:00:54', '2025-11-19 04:53:15', '2025-11-19 11:16:06'),
(3, 'Carnappings', 'Complaint', 2, 'Joselito ', 'Resident', '09453789346', 'male', '2025-11-19', 'Lumbangan, Nasugbu Batangas', 'resident', 'male', 'Johnmark Marqi', 'Cogunan', 'Payat', '2025-11-19', '15:00:00', 'Sa tabing highway ', 'BAHALAA SILAAA', 1, '2025-11-19 05:28:23', '2025-11-19 16:16:59', NULL),
(4, 'Drag Racing ', 'Complaint', 2, 'Melany', 'Resident', '12345678912', 'female', '2025-11-19', 'Role ', 'non-resident', 'male', '', '', '', '2025-11-18', '12:00:00', 'Lumbangan Highway', 'HAHAHAHAHAHAHAHA', 1, '2025-11-19 16:34:34', '2025-11-20 01:48:57', NULL),
(5, 'HAHAJBCHBD', 'Complaint', 2, 'GGDHHHH', 'Resident', '1445565', 'male', '2025-11-19', 'TRTERYETYTY', 'resident', 'male', 'TTRYTRY', 'TFRU', 'YRYUTR', '2025-11-19', '10:07:00', 'TFTGFUUUYR', 'TRRUYTRURUY', 1, '2025-11-20 02:05:07', '2025-11-20 02:05:07', NULL),
(6, 'Illegal Parking', 'Complaint', 2, 'Joel Salanguit', 'Resident', '09342516257', 'male', '2025-11-05', 'Sagbat Lumbangan', 'resident', 'male', 'Myla Mallari', 'Role Subdivision', 'taga looban ang  bahay', '2025-11-01', '02:16:00', 'Sagbat ', 'SIJBIHBCIHBIHSDBCIHSDBCIHBHISBHISBICBS', 2, '2025-11-20 04:15:06', '2025-11-20 05:39:24', NULL),
(7, 'Test Complaint 2025-11-20 15:23:55', 'Complaint', 1, 'Test Complainant', 'Resident', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-20', '15:23:00', 'Test Location', 'This is a test complaint to verify the save functionality', 1, '2025-11-20 15:23:55', '2025-11-20 15:23:55', NULL),
(9, 'HAHAHAHAHAHA', 'Complaint', 2, 'hahahahaha', 'Resident', '347847556352', 'male', '2025-11-12', 'HAHAHAHAHAHAHA', 'resident', 'male', 'hahahahahaha', 'AHAHAHAHAHAHA', 'hahahahaha', '2025-11-13', '11:45:00', 'HAHAHAHAHA', 'hahahahaha', 1, '2025-11-20 15:41:23', '2025-11-20 15:41:23', NULL),
(10, 'Johnnjn', 'Complaint', 2, 'HAHAHAHAAHA', 'Resident', '0485945949854', 'male', '2025-11-19', 'hahahahaha', 'resident', 'male', 'HAHAHAHAHAHAA', 'hahahahaha', 'HAHAHAHAHA', '2025-11-19', '00:44:00', 'hahahahaha', 'HAHAHAHA', 1, '2025-11-20 15:43:47', '2025-11-20 15:43:47', NULL),
(11, 'gfhjfgdhsfghfghfhf', 'Complaint', 1, 'asadasdsadasdasda', 'Resident', '67867867967969', 'male', '2025-11-19', 'sdjkgiergfuigesbdcgifg', 'resident', 'male', 'asdsadasas', 'asdadasdasdada', 'asdadasddsad', '2025-11-20', '09:58:00', 'asdasdwerwergeggsdfsdg', 'asdasdasdasdsadsdasdasdsasafa', 3, '2025-11-21 01:58:38', '2025-11-21 01:58:51', '2025-11-21 09:58:51'),
(12, 'RALLYs', 'Complaint', 2, 'bato', 'Resident', '898957485445', 'male', '2025-11-05', 'Graba', 'resident', 'male', 'Marcos', 'HAHAHAHAHAHA', 'hahahahah', '2025-11-07', '10:50:00', 'HAHAHAHAHA DIKO ALAM', 'hahahahahahahaha ean', 2, '2025-11-21 14:49:54', '2025-11-22 05:35:40', NULL);

--
-- Triggers `incidents`
--
DELIMITER $$
CREATE TRIGGER `notify_complaint_update` AFTER UPDATE ON `incidents` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `notify_new_complaint` AFTER INSERT ON `incidents` FOR EACH ROW BEGIN
    -- Notify officials only (admins handle complaints)
    INSERT INTO `notifications` 
    (`user_id`, `user_type`, `notification_type`, `title`, `message`, `link`, `reference_id`)
    VALUES 
    (NULL, 'official', 'complaint', 
     CONCAT('New Complaint: ', NEW.incident_title),
     CONCAT('From: ', NEW.complainant_name, ' - ', LEFT(NEW.narrative, 100)),
     CONCAT('?page=admin_complaints#incident-', NEW.id),
     NEW.id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lifestyle_risk`
--

CREATE TABLE `lifestyle_risk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cvd_id` bigint(20) UNSIGNED NOT NULL,
  `smoking_status` enum('Never','Stopped_gt_1yr','Current','Stopped_lt_1yr','Passive') DEFAULT NULL,
  `smoking_comments` varchar(255) DEFAULT NULL,
  `alcohol_use` enum('Never','Current','Former') DEFAULT NULL,
  `excessive_alcohol` tinyint(1) DEFAULT NULL,
  `alcohol_notes` varchar(255) DEFAULT NULL,
  `eats_processed_weekly` tinyint(1) DEFAULT NULL,
  `fruits_3_servings_daily` tinyint(1) DEFAULT NULL,
  `vegetables_3_servings_daily` tinyint(1) DEFAULT NULL,
  `exercise_days_per_week` int(11) DEFAULT NULL,
  `exercise_minutes_per_day` int(11) DEFAULT NULL,
  `exercise_intensity` enum('Light','Moderate','Vigorous') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lifestyle_risk`
--

INSERT INTO `lifestyle_risk` (`id`, `cvd_id`, `smoking_status`, `smoking_comments`, `alcohol_use`, `excessive_alcohol`, `alcohol_notes`, `eats_processed_weekly`, `fruits_3_servings_daily`, `vegetables_3_servings_daily`, `exercise_days_per_week`, `exercise_minutes_per_day`, `exercise_intensity`) VALUES
(1, 1, 'Never', '', 'Never', 0, '', 0, 1, 1, 3, 30, 'Moderate'),
(2, 2, 'Current', 'Smokes 5/day', 'Current', 1, 'Occasional binge', 1, 0, 0, 1, 20, 'Light'),
(3, 3, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 4, 40, 'Moderate'),
(4, 4, 'Stopped_gt_1yr', NULL, 'Former', 0, NULL, 0, 1, 1, 2, 25, 'Light'),
(5, 5, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 30, 'Moderate'),
(6, 6, 'Never', NULL, 'Never', 0, NULL, 0, 1, 0, 0, 0, 'Light'),
(7, 7, 'Current', 'Heavy smoker', 'Current', 1, 'Daily', 1, 0, 0, 1, 15, 'Light'),
(8, 8, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 5, 45, 'Vigorous'),
(9, 9, 'Stopped_lt_1yr', NULL, 'Former', 0, NULL, 0, 1, 0, 1, 20, 'Light'),
(10, 10, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 30, 'Moderate'),
(11, 11, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 2, 20, 'Light'),
(12, 12, 'Never', NULL, 'Never', 0, NULL, 1, 1, 1, 4, 35, 'Moderate'),
(13, 13, 'Current', 'Smokes socially', 'Current', 0, NULL, 1, 0, 0, 1, 15, 'Light'),
(14, 14, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 30, 'Moderate'),
(15, 15, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 0, 0, 'Light'),
(16, 16, 'Current', 'Pack-a-day', 'Current', 1, 'Frequent drinker', 1, 0, 0, 0, 0, 'Light'),
(17, 17, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 25, 'Moderate'),
(18, 18, 'Stopped_gt_1yr', NULL, 'Former', 0, NULL, 0, 1, 1, 2, 20, 'Light'),
(19, 19, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 30, 'Moderate'),
(20, 20, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 1, 15, 'Light'),
(21, 24, 'Current', '', 'Never', 1, '', 1, 1, 1, 1, 15, 'Moderate'),
(24, 25, 'Passive', '', 'Never', 1, '', 1, 1, 1, 2, 60, 'Moderate');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `from_purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `moved_at` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `morbidity_logs`
--

CREATE TABLE `morbidity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `household_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purok_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name_free` varchar(255) DEFAULT NULL,
  `age_years` int(11) DEFAULT NULL,
  `diagnosis` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL = notify all users',
  `user_type` enum('user','official','all') NOT NULL DEFAULT 'all' COMMENT 'Target user type: user=resident, official=admin/official, all=everyone',
  `notification_type` varchar(50) NOT NULL COMMENT 'Type: announcement, complaint, document_request',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(500) DEFAULT NULL COMMENT 'URL to navigate when clicked',
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID of the related record',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='System notifications for residents and officials';

-- --------------------------------------------------------

--
-- Table structure for table `notification_deletions`
--

CREATE TABLE `notification_deletions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_by_user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'User who deleted this notification',
  `deleted_by_user_type` enum('user','official') NOT NULL COMMENT 'Type of user who deleted',
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks soft-deleted notifications per user';

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `read_by_user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'User who read this notification',
  `read_by_user_type` enum('user','official') NOT NULL COMMENT 'Type of user who read',
  `read_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks read status per user for each notification';

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(64) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `role` varchar(64) NOT NULL,
  `contact_no` varchar(64) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `photo_url` varchar(1024) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officials`
--

INSERT INTO `officials` (`id`, `full_name`, `username`, `password_hash`, `last_login_at`, `role`, `contact_no`, `email`, `photo_url`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Ramon Santos', 'ramon.santos', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', '2025-11-27 09:44:59', 'Barangay Captain', '09170000001', 'ramon.santos@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-27 09:44:59'),
(2, 'Maria Reyes', 'maria.reyes', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Secretary', '09170000002', 'maria.reyes@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(3, 'Liza Santos', 'liza.santos', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker President', '09170000003', 'liza.santos@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(4, 'Pedro Ramos', 'pedro.ramos', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Conciliation Panel', '09170000004', 'pedro.ramos@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(5, 'Ana Villanueva', 'ana.villanueva', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Conciliation Panel', '09170000005', 'ana.villanueva@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(6, 'Julio Mercado', 'julio.mercado', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Conciliation Panel', '09170000006', 'julio.mercado@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(7, 'Rogelio Tanod1', 'rogelio.tanod1', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Tanod', '09170000007', 'rogelio.tanod1@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(8, 'Manuel Tanod2', 'manuel.tanod2', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Tanod', '09170000008', 'manuel.tanod2@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(9, 'Enrique Tanod3', 'enrique.tanod3', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Tanod', '09170000009', 'enrique.tanod3@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(10, 'Pedro Tanod4', 'pedro.tanod4', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Tanod', '09170000010', 'pedro.tanod4@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(11, 'Carlos Tanod5', 'carlos.tanod5', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Tanod', '09170000011', 'carlos.tanod5@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(12, 'Anna Beltran', 'anna.beltran', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000012', 'anna.beltran@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(13, 'Betsy Cruz', 'betsy.cruz', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000013', 'betsy.cruz@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(14, 'Cecilia DelaRosa', 'cecilia.delarosa', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000014', 'cecilia.delarosa@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(15, 'Daniel Eusebio', 'daniel.eusebio', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000015', 'daniel.eusebio@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(16, 'Evelyn Fernandez', 'evelyn.fernandez', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000016', 'evelyn.fernandez@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(17, 'Felix Gonzales', 'felix.gonzales', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000017', 'felix.gonzales@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(18, 'Gloria Hernandez', 'gloria.hernandez', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000018', 'gloria.hernandez@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(19, 'Horace Ignacio', 'horace.ignacio', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000019', 'horace.ignacio@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(20, 'Ida Javier', 'ida.javier', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000020', 'ida.javier@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(21, 'Josefa Katigbak', 'josefa.katigbak', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000021', 'josefa.katigbak@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(22, 'Kevin Lardizabal', 'kevin.lardizabal', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000022', 'kevin.lardizabal@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(23, 'Lucia Manalastas', 'lucia.manalastas', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000023', 'lucia.manalastas@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(24, 'Mario Navarro', 'mario.navarro', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000024', 'mario.navarro@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(25, 'Nina Ocampo', 'nina.ocampo', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000025', 'nina.ocampo@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(26, 'Oscar Pacheco', 'oscar.pacheco', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000026', 'oscar.pacheco@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(27, 'Pia Quiambao', 'pia.quiambao', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000027', 'pia.quiambao@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(28, 'Rico Ramos', 'rico.ramos', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000028', 'rico.ramos@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(29, 'Sally Tolentino', 'sally.tolentino', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000029', 'sally.tolentino@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03'),
(30, 'Tony Umali', 'tony.umali', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', NULL, 'Barangay Health Worker', '09170000030', 'tony.umali@example.local', NULL, 1, '2025-11-24 09:37:03', '2025-11-24 09:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

CREATE TABLE `persons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `family_id` bigint(20) UNSIGNED DEFAULT NULL,
  `household_id` bigint(20) UNSIGNED DEFAULT NULL,
  `last_name` varchar(128) NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `middle_name` varchar(128) DEFAULT NULL,
  `suffix` varchar(32) DEFAULT NULL,
  `is_head` tinyint(1) NOT NULL DEFAULT 0,
  `sex` enum('M','F') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `marital_status` varchar(32) DEFAULT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `disability` varchar(255) DEFAULT NULL,
  `highest_educ_attainment` varchar(64) DEFAULT NULL,
  `occupation` varchar(128) DEFAULT NULL,
  `religion` varchar(64) DEFAULT NULL,
  `is_pregnant` tinyint(1) DEFAULT NULL,
  `is_deceased` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`id`, `family_id`, `household_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `is_head`, `sex`, `birthdate`, `marital_status`, `blood_type`, `disability`, `highest_educ_attainment`, `occupation`, `religion`, `is_pregnant`, `is_deceased`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Cruz', 'Adrian', 'P', NULL, 1, 'M', '1990-04-12', 'Single', 'A+', NULL, 'College', 'Engineer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(2, 1, 1, 'Reyes', 'Bianca', 'M', NULL, 0, 'F', '1995-11-03', 'Single', 'O+', NULL, 'College', 'Nurse', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(3, 2, 2, 'Mendoza', 'Carlo', 'R', NULL, 1, 'M', '1982-06-20', 'Married', 'B+', NULL, 'College', 'Driver', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(4, 2, 2, 'Lopez', 'Diana', 'S', NULL, 0, 'F', '1988-09-08', 'Married', 'AB+', NULL, 'College', 'Teacher', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(5, 3, 3, 'Garcia', 'Ernesto', 'L', NULL, 1, 'M', '1975-01-30', 'Married', 'O-', NULL, 'High School', 'Farmer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(6, 3, 3, 'Ramos', 'Fiona', 'A', NULL, 0, 'F', '1992-12-15', 'Single', 'A-', NULL, 'College', 'Clerk', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(7, 4, 4, 'Aquino', 'Gabriel', 'T', NULL, 1, 'M', '1986-05-05', 'Married', 'B-', NULL, 'College', 'Accountant', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(8, 4, 4, 'Castillo', 'Hannah', 'L', NULL, 0, 'F', '1998-07-21', 'Single', 'O+', NULL, 'College', 'Graphic Designer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(9, 1, 1, 'Delos Santos', 'Ian', 'C', NULL, 0, 'M', '2000-03-10', 'Single', NULL, NULL, 'Vocational', 'Student', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(10, 1, 1, 'Flores', 'Jasmine', 'E', NULL, 0, 'F', '1993-10-02', 'Single', 'AB-', NULL, 'College', 'Pharmacist', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(11, 5, 5, 'Navarro', 'Kevin', 'J', NULL, 1, 'M', '1989-08-18', 'Married', 'A+', NULL, 'College', 'Sales', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(12, 5, 5, 'Mercado', 'Liza', 'B', NULL, 0, 'F', '1978-02-25', 'Widowed', 'O+', NULL, 'College', 'Midwife', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(13, 1, 1, 'Domingo', 'Miguel', 'R', NULL, 0, 'M', '2001-06-11', 'Single', NULL, NULL, 'High School', 'Driver', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(14, 2, 2, 'Valdez', 'Nicole', 'F', NULL, 0, 'F', '1996-01-09', 'Single', 'B+', NULL, 'College', 'IT Support', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(15, 6, 6, 'Pineda', 'Oscar', 'M', NULL, 1, 'M', '1970-11-30', 'Married', 'AB+', NULL, 'High School', 'Mechanic', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(16, 7, 7, 'Santos', 'Paula', 'G', NULL, 1, 'F', '1984-04-04', 'Married', 'O+', NULL, 'College', 'Business Owner', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(17, 4, 4, 'Torres', 'Quincy', 'V', NULL, 0, 'M', '1991-02-14', 'Single', 'A+', NULL, 'College', 'Researcher', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(18, 5, 5, 'Gonzales', 'Rhea', 'K', NULL, 0, 'F', '1999-05-30', 'Single', 'B-', NULL, 'College', 'Designer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(19, 6, 6, 'Herrera', 'Samuel', 'D', NULL, 0, 'M', '1980-12-01', 'Married', 'O+', NULL, 'College', 'Police', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(20, 6, 6, 'Ilagan', 'Teresita', 'N', NULL, 0, 'F', '1965-07-07', 'Widowed', 'AB-', NULL, 'High School', 'Retired', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(21, 7, 7, 'Jacinto', 'Ulysses', 'R', NULL, 0, 'M', '1994-03-22', 'Single', 'B+', NULL, 'College', 'Teacher', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(22, 7, 7, 'Kahulugan', 'Valeria', 'S', NULL, 0, 'F', '1997-08-29', 'Single', 'A-', NULL, 'College', 'Research Asst', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(23, 8, 8, 'Lorenzo', 'Wesley', 'M', NULL, 1, 'M', '1983-10-17', 'Married', 'O+', NULL, 'College', 'Chef', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(24, 2, 2, 'Manalo', 'Xandra', 'P', NULL, 0, 'F', '1990-09-09', 'Single', 'AB+', NULL, 'College', 'Architect', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(25, 3, 3, 'Noble', 'Yves', 'L', NULL, 0, 'M', '1992-02-02', 'Single', 'A+', NULL, 'College', 'Photographer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(26, 9, 9, 'Ortega', 'Zara', 'Q', NULL, 0, 'F', '1999-12-12', 'Single', 'O-', NULL, 'College', 'Barista', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(27, 9, 9, 'Padilla', 'Aaron', 'V', NULL, 1, 'M', '1987-06-06', 'Married', 'B+', NULL, 'College', 'Electrician', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(28, 8, 8, 'Quintos', 'Bea', 'R', NULL, 0, 'F', '1994-04-18', 'Single', 'AB-', NULL, 'College', 'Analyst', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(29, 8, 8, 'Rafael', 'Cesar', 'G', NULL, 0, 'M', '1981-03-03', 'Married', 'A-', NULL, 'College', 'Carpenter', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(30, 9, 9, 'Serrano', 'Denise', 'H', NULL, 0, 'F', '1990-05-05', 'Single', 'O+', NULL, 'College', 'Lawyer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(31, 10, 10, 'Taboada', 'Ethan', 'K', NULL, 1, 'M', '1993-11-11', 'Single', 'B+', NULL, 'College', 'Consultant', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(32, 9, 9, 'Uban', 'Faith', 'M', NULL, 0, 'F', '1997-07-07', 'Single', 'AB+', NULL, 'College', 'Marketing', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(33, 10, 10, 'Velez', 'Gabrielle', 'S', NULL, 0, 'F', '1991-01-01', 'Single', 'O-', NULL, 'College', 'Event Planner', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(34, 11, 11, 'Wong', 'Hector', 'L', NULL, 1, 'M', '1985-08-08', 'Married', 'A+', NULL, 'College', 'IT Manager', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(35, 10, 10, 'Xavier', 'Isla', 'P', NULL, 0, 'F', '1996-06-06', 'Single', 'B-', NULL, 'College', 'Researcher', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(36, 11, 11, 'Yap', 'Jason', 'R', NULL, 0, 'M', '1992-09-19', 'Single', 'AB-', NULL, 'College', 'Consultant', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(37, 11, 11, 'Zamora', 'Katrina', 'L', NULL, 0, 'F', '1998-10-10', 'Single', 'O+', NULL, 'College', 'Social Worker', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(38, 12, 12, 'Alcantara', 'Loren', 'G', NULL, 1, 'M', '1984-04-04', 'Married', 'B+', NULL, 'College', 'Planner', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(39, 12, 12, 'Barrameda', 'Maya', 'T', NULL, 0, 'F', '1990-02-20', 'Single', 'A+', NULL, 'College', 'Trainer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(40, 12, 12, 'Cabal', 'Noel', 'S', NULL, 0, 'M', '1977-07-07', 'Married', 'O-', NULL, 'High School', 'Fisherman', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(41, 13, 13, 'Dizon', 'Olivia', 'M', NULL, 1, 'F', '1995-05-05', 'Single', 'B-', NULL, 'College', 'Analyst', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(42, 13, 13, 'Eusebio', 'Paul', 'N', NULL, 0, 'M', '1988-08-08', 'Married', 'AB+', NULL, 'College', 'Tutor', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(43, 14, 14, 'Floresca', 'Rico', 'L', NULL, 1, 'M', '1990-09-09', 'Single', 'O+', NULL, 'College', 'Developer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(44, 13, 13, 'Gamboa', 'Sonia', 'P', NULL, 0, 'F', '1979-03-03', 'Married', 'B+', NULL, 'College', 'Shop Owner', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(45, 14, 14, 'Hidalgo', 'Tristan', 'Q', NULL, 0, 'M', '1991-01-21', 'Single', 'A-', NULL, 'College', 'Plumber', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(46, 15, 15, 'Ibarra', 'Ursula', 'V', NULL, 1, 'F', '1986-12-12', 'Married', 'AB-', NULL, 'College', 'Nurse', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(47, 14, 14, 'Julian', 'Victor', 'S', NULL, 0, 'M', '1982-02-02', 'Married', 'B-', NULL, 'College', 'Technician', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(48, 15, 15, 'Kendrick', 'Willa', 'A', NULL, 0, 'F', '1995-05-05', 'Single', 'O+', NULL, 'College', 'Writer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(49, 15, 15, 'Lozada', 'Xavier', 'B', NULL, 0, 'M', '1993-03-03', 'Single', 'A+', NULL, 'College', 'Engineer', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(50, 15, 15, 'Mata', 'Yolanda', 'C', NULL, 0, 'F', '1987-07-07', 'Married', 'B+', NULL, 'College', 'Administrator', 'Catholic', 0, 0, '2025-11-24 09:26:54', '2025-11-24 10:59:44'),
(51, 16, 16, 'Vazques', 'John Ley Lucky', 'Medyor', NULL, 1, 'M', '2004-08-12', 'Single', 'O+', NULL, 'College', 'Student', 'Roman Catholic', NULL, 0, '2025-11-24 18:51:37', '2025-11-26 09:50:51'),
(52, 16, 16, 'Malata', 'Ronel Lance', 'Sumama', NULL, 0, NULL, NULL, 'Single', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-11-24 19:03:16', '2025-11-25 19:28:25'),
(53, 31, 31, 'Vazques', 'John Ley Lucky', 'Medyor', NULL, 1, 'M', '2004-08-12', 'Single', 'O+', NULL, 'College', 'Student', 'Roman Catholic', NULL, 0, '2025-11-26 09:21:41', '2025-11-26 11:11:25'),
(54, 0, NULL, 'Condicion', 'Marlo', 'Humarang', NULL, 0, NULL, NULL, 'Single', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-11-26 09:23:26', '2025-11-26 09:23:26');

-- --------------------------------------------------------

--
-- Table structure for table `person_relationships`
--

CREATE TABLE `person_relationships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `related_person_id` bigint(20) UNSIGNED NOT NULL,
  `relationship_type` enum('parent','child','spouse','other') NOT NULL,
  `family_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_inverse` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `person_relationships`
--

INSERT INTO `person_relationships` (`id`, `person_id`, `related_person_id`, `relationship_type`, `family_id`, `is_inverse`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'spouse', 1, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(2, 2, 1, 'spouse', 1, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(5, 2, 9, 'parent', 1, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(6, 9, 2, 'child', 1, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(9, 2, 10, 'parent', 1, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(10, 10, 2, 'child', 1, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(11, 9, 13, 'parent', 1, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(12, 13, 9, 'child', 1, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(13, 3, 4, 'spouse', 2, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(14, 4, 3, 'spouse', 2, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(15, 3, 14, 'parent', 2, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(16, 14, 3, 'child', 2, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(17, 4, 14, 'parent', 2, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(18, 14, 4, 'child', 2, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(19, 3, 24, 'parent', 2, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(20, 24, 3, 'child', 2, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(21, 4, 24, 'parent', 2, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(22, 24, 4, 'child', 2, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(23, 5, 6, 'spouse', 3, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(24, 6, 5, 'spouse', 3, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(25, 5, 25, 'parent', 3, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(26, 25, 5, 'child', 3, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(27, 6, 25, 'parent', 3, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(28, 25, 6, 'child', 3, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(29, 7, 8, 'spouse', 4, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(30, 8, 7, 'spouse', 4, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(31, 7, 17, 'parent', 4, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(32, 17, 7, 'child', 4, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(33, 8, 17, 'parent', 4, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(34, 17, 8, 'child', 4, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(35, 11, 12, 'spouse', 5, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(36, 12, 11, 'spouse', 5, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(37, 11, 18, 'parent', 5, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(38, 18, 11, 'child', 5, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(39, 12, 18, 'parent', 5, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(40, 18, 12, 'child', 5, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(41, 15, 19, 'spouse', 6, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(42, 19, 15, 'spouse', 6, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(43, 15, 20, 'parent', 6, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(44, 20, 15, 'child', 6, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(45, 19, 20, 'parent', 6, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(46, 20, 19, 'child', 6, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(47, 16, 21, 'spouse', 7, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(48, 21, 16, 'spouse', 7, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(49, 16, 22, 'parent', 7, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(50, 22, 16, 'child', 7, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(51, 21, 22, 'parent', 7, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(52, 22, 21, 'child', 7, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(53, 23, 28, 'spouse', 8, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(54, 28, 23, 'spouse', 8, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(55, 23, 29, 'parent', 8, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(56, 29, 23, 'child', 8, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(57, 28, 29, 'parent', 8, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(58, 29, 28, 'child', 8, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(59, 27, 30, 'spouse', 9, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(60, 30, 27, 'spouse', 9, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(61, 27, 32, 'parent', 9, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(62, 32, 27, 'child', 9, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(63, 30, 32, 'parent', 9, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(64, 32, 30, 'child', 9, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(65, 27, 26, 'parent', 9, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(66, 26, 27, 'child', 9, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(67, 30, 26, 'parent', 9, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(68, 26, 30, 'child', 9, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(69, 31, 33, 'spouse', 10, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(70, 33, 31, 'spouse', 10, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(71, 31, 35, 'parent', 10, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(72, 35, 31, 'child', 10, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(73, 33, 35, 'parent', 10, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(74, 35, 33, 'child', 10, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(75, 34, 36, 'spouse', 11, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(76, 36, 34, 'spouse', 11, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(77, 34, 37, 'parent', 11, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(78, 37, 34, 'child', 11, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(79, 36, 37, 'parent', 11, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(80, 37, 36, 'child', 11, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(81, 38, 39, 'spouse', 12, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(82, 39, 38, 'spouse', 12, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(83, 38, 40, 'parent', 12, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(84, 40, 38, 'child', 12, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(85, 39, 40, 'parent', 12, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(86, 40, 39, 'child', 12, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(87, 41, 42, 'spouse', 13, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(88, 42, 41, 'spouse', 13, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(89, 41, 44, 'parent', 13, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(90, 44, 41, 'child', 13, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(91, 42, 44, 'parent', 13, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(92, 44, 42, 'child', 13, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(93, 43, 45, 'spouse', 14, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(94, 45, 43, 'spouse', 14, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(95, 43, 47, 'parent', 14, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(96, 47, 43, 'child', 14, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(97, 45, 47, 'parent', 14, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(98, 47, 45, 'child', 14, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(99, 46, 48, 'spouse', 15, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(100, 48, 46, 'spouse', 15, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(101, 46, 49, 'parent', 15, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(102, 49, 46, 'child', 15, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(103, 48, 49, 'parent', 15, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(104, 49, 48, 'child', 15, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(105, 46, 50, 'parent', 15, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(106, 50, 46, 'child', 15, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(107, 48, 50, 'parent', 15, 0, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(108, 50, 48, 'child', 15, 1, '2025-11-24 10:59:44', '2025-11-24 10:59:44'),
(109, 51, 52, 'child', 16, 0, '2025-11-24 19:03:46', '2025-11-24 19:03:46'),
(110, 52, 51, 'parent', 16, 1, '2025-11-24 19:03:46', '2025-11-24 19:03:46');

-- --------------------------------------------------------

--
-- Table structure for table `pregnancies`
--

CREATE TABLE `pregnancies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `lmp_date` date DEFAULT NULL,
  `edc_date` date DEFAULT NULL,
  `gravidity` int(11) DEFAULT NULL,
  `parity` int(11) DEFAULT NULL,
  `aog_weeks` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `puroks`
--

CREATE TABLE `puroks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `centroid_lat` decimal(9,6) DEFAULT NULL,
  `centroid_lng` decimal(9,6) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `puroks`
--

INSERT INTO `puroks` (`id`, `name`, `centroid_lat`, `centroid_lng`, `created_at`, `updated_at`) VALUES
(1, 'Sagbat', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(2, 'Campo Avejar', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(3, 'Roxas Village', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(4, 'Central', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(5, 'Camachilihan', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(6, 'El Paso', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(7, 'Calamundingan', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(8, 'Role', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(9, 'Mambugan', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30'),
(10, 'Malangaw', NULL, NULL, '2025-11-09 09:22:30', '2025-11-09 09:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `site_profile`
--

CREATE TABLE `site_profile` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `barangay_name` varchar(255) NOT NULL DEFAULT 'Lumbangan',
  `municipality_name` varchar(255) NOT NULL DEFAULT 'Nasugbu',
  `province_name` varchar(255) NOT NULL DEFAULT 'Batangas',
  `region_name` varchar(255) NOT NULL DEFAULT 'Region IV-A (CALABARZON)',
  `psa_code` varchar(64) DEFAULT NULL,
  `address_line` varchar(512) DEFAULT NULL,
  `hotline` varchar(64) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_profile`
--

INSERT INTO `site_profile` (`id`, `barangay_name`, `municipality_name`, `province_name`, `region_name`, `psa_code`, `address_line`, `hotline`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Lumbangan', 'Nasugbu', 'Batangas', 'Region IV-A (CALABARZON)', NULL, NULL, '043-123-4567', 'barangaylumbangan@nasugbu.gov.ph', '2025-11-09 09:22:35', '2025-11-09 09:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `label`, `created_at`) VALUES
(1, 'Pending', '2025-11-18 18:53:01'),
(2, 'Investigating', '2025-11-18 18:53:01'),
(3, 'Resolved', '2025-11-18 18:53:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(13) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `person_id`, `username`, `email`, `mobile`, `password_hash`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'adrian.cruz', 'adrian.cruz01@example.local', '0917-0000-001', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', '2025-11-27 09:57:50', '2025-11-24 09:26:54', '2025-11-27 09:57:50'),
(2, 2, 'bianca.reyes', 'bianca.reyes02@example.local', '09170000002', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(3, 3, 'carlo.mendoza', 'carlo.mendoza03@example.local', '09170000003', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(4, 4, 'diana.lopez', 'diana.lopez04@example.local', '09170000004', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(5, 5, 'ernesto.garcia', 'ernesto.garcia05@example.local', '09170000005', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(6, 6, 'fiona.ramos', 'fiona.ramos06@example.local', '09170000006', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(7, 7, 'gabriel.aquino', 'gabriel.aquino07@example.local', '09170000007', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(8, 8, 'hannah.castillo', 'hannah.castillo08@example.local', '09170000008', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(9, 9, 'ian.delossantos', 'ian.delossantos09@example.local', '09170000009', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', '2025-11-24 18:25:16', '2025-11-24 09:26:54', '2025-11-24 18:25:16'),
(10, 10, 'jasmine.flores', 'jasmine.flores10@example.local', '09170000010', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(11, 11, 'kevin.navarro', 'kevin.navarro11@example.local', '09170000011', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(12, 12, 'liza.mercado', 'liza.mercado12@example.local', '09170000012', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(13, 13, 'miguel.domingo', 'miguel.domingo13@example.local', '09170000013', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(14, 14, 'nicole.valdez', 'nicole.valdez14@example.local', '09170000014', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(15, 15, 'oscar.pineda', 'oscar.pineda15@example.local', '09170000015', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(16, 16, 'paula.santos', 'paula.santos16@example.local', '09170000016', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(17, 17, 'quincy.torres', 'quincy.torres17@example.local', '09170000017', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(18, 18, 'rhea.gonzales', 'rhea.gonzales18@example.local', '09170000018', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(19, 19, 'samuel.herrera', 'samuel.herrera19@example.local', '09170000019', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(20, 20, 'teresita.ilagan', 'teresita.ilagan20@example.local', '09170000020', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(21, 21, 'ulysses.jacinto', 'ulysses.jacinto21@example.local', '09170000021', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(22, 22, 'valeria.kahulugan', 'valeria.kahulugan22@example.local', '09170000022', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(23, 23, 'wesley.lorenzo', 'wesley.lorenzo23@example.local', '09170000023', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(24, 24, 'xandra.manalo', 'xandra.manalo24@example.local', '09170000024', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(25, 25, 'yves.noble', 'yves.noble25@example.local', '09170000025', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(26, 26, 'zara.ortega', 'zara.ortega26@example.local', '09170000026', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(27, 27, 'aaron.padilla', 'aaron.padilla27@example.local', '09170000027', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(28, 28, 'bea.quintos', 'bea.quintos28@example.local', '09170000028', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(29, 29, 'cesar.rafael', 'cesar.rafael29@example.local', '09170000029', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(30, 30, 'denise.serrano', 'denise.serrano30@example.local', '09170000030', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(31, 31, 'ethan.taboada', 'ethan.taboada31@example.local', '09170000031', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(32, 32, 'faith.uban', 'faith.uban32@example.local', '09170000032', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(33, 33, 'gabrielle.velez', 'gabrielle.velez33@example.local', '09170000033', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(34, 34, 'hector.wong', 'hector.wong34@example.local', '09170000034', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(35, 35, 'isla.xavier', 'isla.xavier35@example.local', '09170000035', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(36, 36, 'jason.yap', 'jason.yap36@example.local', '09170000036', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(37, 37, 'katrina.zamora', 'katrina.zamora37@example.local', '09170000037', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(38, 38, 'loren.alcantara', 'loren.alcantara38@example.local', '09170000038', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(39, 39, 'maya.barrameda', 'maya.barrameda39@example.local', '09170000039', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(40, 40, 'noel.cabal', 'noel.cabal40@example.local', '09170000040', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(41, 41, 'olivia.dizon', 'olivia.dizon41@example.local', '09170000041', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(42, 42, 'paul.eusebio', 'paul.eusebio42@example.local', '09170000042', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(43, 43, 'rico.floresca', 'rico.floresca43@example.local', '09170000043', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(44, 44, 'sonia.gamboa', 'sonia.gamboa44@example.local', '09170000044', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(45, 45, 'tristan.hidalgo', 'tristan.hidalgo45@example.local', '09170000045', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(46, 46, 'ursula.ibarra', 'ursula.ibarra46@example.local', '09170000046', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(47, 47, 'victor.julian', 'victor.julian47@example.local', '09170000047', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(48, 48, 'willa.kendrick', 'willa.kendrick48@example.local', '09170000048', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(49, 49, 'xavier.lozada', 'xavier.lozada49@example.local', '09170000049', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(50, 50, 'yolanda.mata', 'yolanda.mata50@example.local', '09170000050', '$2y$10$OJoXuFUCQJLBRRuJ3SZRZutkiKj9tNLneLj9CqBHN8XF/Y7z6Hqk2', 'active', NULL, '2025-11-24 09:26:54', '2025-11-24 09:26:54'),
(51, 51, 'alf_red_c', 'davidgludo@gmail.com', '0995-3373-692', '$2y$10$TRFluf/ZAo7NSe1862inK.NJ0dz1ZO986wR7/KGoq7LdgFgMPeWHq', 'active', '2025-11-27 09:57:38', '2025-11-24 18:51:37', '2025-11-27 09:57:38'),
(52, 52, 'lancey', 'ronel@gmail.com', NULL, '$2y$10$eWxedBtB7eE8QvzqF/cXGOXq0uhLduwvEMZTNGcbgCXgLg1Xh.mSW', 'active', '2025-11-24 20:12:08', '2025-11-24 19:03:16', '2025-11-24 20:12:08'),
(53, 53, 'vayqiz', 'ley@gmail.com', '0995-3373-693', '$2y$10$PicgUG0gYrOj29JEXRUTkumsqEWW3xnFKhCbQWCMVDLUKjn3ouiIe', 'active', '2025-11-26 11:10:46', '2025-11-26 09:21:41', '2025-11-26 11:10:46'),
(54, 54, 'condiiii', 'marlo@gmail.com', NULL, '$2y$10$xuG95KX3afPDtjJH/SgXten16JacX.Sv1S8In6VNpNWGoyO9ygXc6', 'active', '2025-11-26 09:43:38', '2025-11-26 09:23:26', '2025-11-26 09:43:38');

-- --------------------------------------------------------

--
-- Table structure for table `vitals`
--

CREATE TABLE `vitals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cvd_id` bigint(20) UNSIGNED NOT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `central_adiposity` tinyint(1) DEFAULT NULL,
  `raised_bp` tinyint(1) DEFAULT NULL,
  `raised_blood_sugar` tinyint(1) DEFAULT NULL,
  `dyslipidemia` tinyint(1) DEFAULT NULL,
  `waist_circumference_cm` decimal(5,2) DEFAULT NULL,
  `bp_systolic` int(11) DEFAULT NULL,
  `bp_diastolic` int(11) DEFAULT NULL,
  `pulse` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `obesity_flag` tinyint(1) GENERATED ALWAYS AS (case when `bmi` is not null and `bmi` >= 30 then 1 else 0 end) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vitals`
--

INSERT INTO `vitals` (`id`, `cvd_id`, `height_cm`, `weight_kg`, `bmi`, `central_adiposity`, `raised_bp`, `raised_blood_sugar`, `dyslipidemia`, `waist_circumference_cm`, `bp_systolic`, `bp_diastolic`, `pulse`, `respiratory_rate`, `temperature_c`) VALUES
(1, 1, 168.00, 70.00, 24.80, 0, 0, 0, 0, 82.00, 110, 75, 74, 16, 36.6),
(2, 2, 165.00, 95.00, 34.90, 1, 1, 1, 1, 102.00, 150, 95, 88, 18, 37.0),
(3, 3, 170.00, 75.00, 25.90, 0, 0, 0, 0, 86.00, 118, 78, 72, 16, 36.6),
(4, 4, 158.00, 60.00, 24.00, 0, 0, 0, 0, 74.00, 115, 76, 70, 16, 36.7),
(5, 5, 172.00, 80.00, 27.00, 0, 0, 0, 0, 88.00, 120, 80, 76, 16, 36.6),
(6, 6, 160.00, 62.00, 24.20, 0, 0, 0, 0, 78.00, 112, 72, 70, 16, 36.6),
(7, 7, 168.00, 90.00, 31.90, 1, 1, 1, 1, 96.00, 138, 90, 86, 18, 36.9),
(8, 8, 155.00, 50.00, 20.80, 0, 0, 0, 0, 72.00, 108, 70, 68, 16, 36.6),
(9, 9, 171.00, 82.00, 28.10, 1, 1, 0, 0, 92.00, 125, 82, 78, 16, 36.7),
(10, 10, 165.00, 68.00, 25.00, 0, 0, 0, 0, 84.00, 116, 76, 74, 16, 36.6),
(11, 11, 169.00, 74.00, 25.90, 0, 0, 0, 0, 88.00, 118, 78, 75, 16, 36.6),
(12, 12, 162.00, 58.00, 22.10, 0, 0, 0, 0, 76.00, 110, 70, 72, 16, 36.6),
(13, 13, 174.00, 95.00, 31.40, 1, 1, 1, 1, 96.00, 145, 92, 90, 18, 37.1),
(14, 14, 160.00, 65.00, 25.40, 0, 0, 0, 0, 80.00, 120, 80, 78, 16, 36.6),
(15, 15, 167.00, 72.00, 25.80, 0, 0, 0, 0, 85.00, 118, 78, 74, 16, 36.6),
(16, 16, 155.00, 96.00, 39.90, 1, 1, 1, 1, 102.00, 160, 100, 92, 18, 37.4),
(17, 17, 170.00, 68.00, 23.50, 0, 0, 0, 0, 84.00, 112, 74, 72, 16, 36.7),
(18, 18, 168.00, 88.00, 31.20, 1, 1, 1, 1, 98.00, 140, 94, 88, 18, 37.0),
(19, 19, 162.00, 70.00, 26.60, 0, 0, 0, 0, 86.00, 120, 80, 76, 16, 36.6),
(20, 20, 175.00, 78.00, 25.50, 0, 0, 0, 0, 90.00, 122, 82, 76, 16, 36.6),
(21, 24, 171.00, 70.00, NULL, NULL, NULL, NULL, NULL, 25.00, 120, 80, 72, 16, 37.0),
(22, 25, 171.00, 70.00, NULL, NULL, NULL, NULL, NULL, 25.00, 120, 80, 72, 16, 37.0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `angina_stroke_screening`
--
ALTER TABLE `angina_stroke_screening`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_angina_cvd` (`cvd_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `births`
--
ALTER TABLE `births`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `case_types`
--
ALTER TABLE `case_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `label` (`label`);

--
-- Indexes for table `cvd_ncd_risk_assessments`
--
ALTER TABLE `cvd_ncd_risk_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cvd_person_date` (`person_id`,`survey_date`),
  ADD KEY `idx_cvd_is_approved` (`is_approved`);

--
-- Indexes for table `deaths`
--
ALTER TABLE `deaths`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_diabetes_cvd` (`cvd_id`);

--
-- Indexes for table `document_categories`
--
ALTER TABLE `document_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_type_id` (`document_type_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`document_type_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_families_head_person` (`head_person_id`),
  ADD KEY `idx_families_household` (`household_id`),
  ADD KEY `idx_families_survey` (`survey_date`);

--
-- Indexes for table `families_address_archive`
--
ALTER TABLE `families_address_archive`
  ADD KEY `idx_faa_household` (`household_id`),
  ADD KEY `idx_faa_family` (`family_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_family_history`
--
ALTER TABLE `health_family_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_hfh_person_date` (`person_id`,`recorded_at`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_households_purok` (`purok_id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_incident` (`incident_title`(100),`complainant_name`(100),`date_of_incident`,`location`(100)),
  ADD KEY `idx_incident_status` (`status_id`),
  ADD KEY `idx_incident_case_type` (`case_type_id`),
  ADD KEY `idx_incident_date` (`date_of_incident`),
  ADD KEY `idx_incident_created` (`created_at`),
  ADD KEY `idx_incident_complainant` (`complainant_name`(100)),
  ADD KEY `idx_incident_location` (`location`(100));

--
-- Indexes for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lifestyle_cvd` (`cvd_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_migrations_person` (`person_id`),
  ADD KEY `idx_migrations_from` (`from_purok_id`),
  ADD KEY `idx_migrations_to` (`to_purok_id`);

--
-- Indexes for table `morbidity_logs`
--
ALTER TABLE `morbidity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_morbidity_person` (`person_id`),
  ADD KEY `idx_morbidity_household` (`household_id`),
  ADD KEY `idx_morbidity_purok` (`purok_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_id`,`user_type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `notification_deletions`
--
ALTER TABLE `notification_deletions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_notification` (`notification_id`,`deleted_by_user_id`,`deleted_by_user_type`),
  ADD KEY `idx_notification_id` (`notification_id`),
  ADD KEY `idx_deleted_by` (`deleted_by_user_id`,`deleted_by_user_type`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_read` (`notification_id`,`read_by_user_id`,`read_by_user_type`),
  ADD KEY `idx_notification_id` (`notification_id`),
  ADD KEY `idx_read_by` (`read_by_user_id`,`read_by_user_type`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_officials_username` (`username`),
  ADD KEY `idx_officials_role_active` (`role`,`active`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `unique_user_code` (`user_id`,`code`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_persons_family` (`family_id`),
  ADD KEY `idx_persons_name` (`last_name`,`first_name`),
  ADD KEY `idx_persons_household` (`household_id`);

--
-- Indexes for table `person_relationships`
--
ALTER TABLE `person_relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pr_direct` (`person_id`,`related_person_id`,`relationship_type`),
  ADD KEY `idx_pr_person` (`person_id`,`relationship_type`),
  ADD KEY `idx_pr_related` (`related_person_id`,`relationship_type`),
  ADD KEY `idx_pr_family` (`family_id`);

--
-- Indexes for table `pregnancies`
--
ALTER TABLE `pregnancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pregnancies_person` (`person_id`);

--
-- Indexes for table `puroks`
--
ALTER TABLE `puroks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_purok_name` (`name`);

--
-- Indexes for table `site_profile`
--
ALTER TABLE `site_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `label` (`label`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_person` (`person_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_mobile` (`mobile`);

--
-- Indexes for table `vitals`
--
ALTER TABLE `vitals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vitals_cvd` (`cvd_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `angina_stroke_screening`
--
ALTER TABLE `angina_stroke_screening`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `births`
--
ALTER TABLE `births`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_types`
--
ALTER TABLE `case_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cvd_ncd_risk_assessments`
--
ALTER TABLE `cvd_ncd_risk_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `deaths`
--
ALTER TABLE `deaths`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `document_categories`
--
ALTER TABLE `document_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `document_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_family_history`
--
ALTER TABLE `health_family_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `morbidity_logs`
--
ALTER TABLE `morbidity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_deletions`
--
ALTER TABLE `notification_deletions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `persons`
--
ALTER TABLE `persons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `person_relationships`
--
ALTER TABLE `person_relationships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `pregnancies`
--
ALTER TABLE `pregnancies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `puroks`
--
ALTER TABLE `puroks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `vitals`
--
ALTER TABLE `vitals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `document_types`
--
ALTER TABLE `document_types`
  ADD CONSTRAINT `document_types_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`category_id`);

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`case_type_id`) REFERENCES `case_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `notification_deletions`
--
ALTER TABLE `notification_deletions`
  ADD CONSTRAINT `notification_deletions_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `persons`
--
ALTER TABLE `persons`
  ADD CONSTRAINT `fk_persons_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
