-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2025 at 02:48 PM
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
(1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-11 20:54:57');

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
  `expires_at` datetime DEFAULT NULL,
  `author` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, '2025-11-11 15:03:56', NULL, '2025-11-11', NULL, 0, NULL, NULL, NULL);

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
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_date` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `proof_upload` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `released_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `document_type_id` int(11) NOT NULL,
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
(1, 1, NULL, NULL, 'Permanent', NULL, NULL, NULL, '2025-11-09 10:04:31', '2025-11-11 20:54:48'),
(2, 1, NULL, NULL, NULL, NULL, NULL, '2025-11-11', '2025-11-11 20:54:48', '2025-11-11 20:55:07'),
(3, 1, NULL, NULL, NULL, NULL, NULL, '2025-11-11', '2025-11-11 20:55:07', '2025-11-11 20:55:07'),
(4, 1, NULL, NULL, NULL, NULL, NULL, '2025-11-11', '2025-11-11 20:55:07', '2025-11-11 20:55:11'),
(5, 1, NULL, NULL, NULL, NULL, NULL, '2025-11-11', '2025-11-11 20:55:11', '2025-11-11 20:55:17'),
(6, 1, NULL, NULL, NULL, NULL, NULL, '2025-11-11', '2025-11-11 20:55:17', '2025-11-11 21:33:31'),
(7, 1, NULL, 1, NULL, NULL, NULL, '2025-11-11', '2025-11-11 21:33:31', '2025-11-11 21:33:31');

--
-- Triggers `families`
--
DELIMITER $$
CREATE TRIGGER `trg_families_bi` BEFORE INSERT ON `families` FOR EACH ROW BEGIN
  IF NEW.head_person_id IS NOT NULL THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Set head_person_id only after the family row exists (use UPDATE).';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_families_bu` BEFORE UPDATE ON `families` FOR EACH ROW BEGIN
  IF NEW.head_person_id IS NOT NULL THEN
    IF NOT EXISTS (
      SELECT 1
      FROM persons p
      WHERE p.id = NEW.head_person_id
        AND p.family_id = NEW.id
    ) THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'head_person_id must reference a person whose persons.family_id equals this families.id';
    END IF;
  END IF;
END
$$
DELIMITER ;

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
(1, 1, 0, 0, 0, 0, 0, 0, 0, '2025-11-11');

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
(1, NULL, NULL, 'Pending - To be updated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-09 10:04:31', '2025-11-09 10:04:31');

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
(1, 1, NULL, '', NULL, NULL, '', NULL, NULL, NULL, 0, 0, NULL);

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
(1, 'Juan Dela Cruz', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Administrator', '09123456789', 'admin@lumbangan.gov.ph', NULL, 1, '2025-11-09 09:22:28', '2025-11-09 09:22:28');

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

CREATE TABLE `persons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `family_id` bigint(20) UNSIGNED NOT NULL,
  `last_name` varchar(128) NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `middle_name` varchar(128) DEFAULT NULL,
  `suffix` varchar(32) DEFAULT NULL,
  `family_position` varchar(64) DEFAULT NULL,
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

INSERT INTO `persons` (`id`, `family_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `family_position`, `sex`, `birthdate`, `marital_status`, `blood_type`, `disability`, `highest_educ_attainment`, `occupation`, `religion`, `is_pregnant`, `is_deceased`, `created_at`, `updated_at`) VALUES
(1, 7, 'Gludo', 'David Alfred', 'Cabali', NULL, 'Head', 'M', '2004-08-12', 'Single', 'O+', NULL, 'College', 'Student', 'Roman Catholic', NULL, 0, '2025-11-09 10:04:31', '2025-11-11 21:33:31');

--
-- Triggers `persons`
--
DELIMITER $$
CREATE TRIGGER `trg_persons_au_head_cleanup` AFTER UPDATE ON `persons` FOR EACH ROW BEGIN
  IF OLD.id = NEW.id AND OLD.family_id <> NEW.family_id THEN
    UPDATE families f
      SET f.head_person_id = NULL
      WHERE f.head_person_id = NEW.id
        AND f.id <> NEW.family_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `person_relationships`
--

CREATE TABLE `person_relationships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `related_person_id` bigint(20) UNSIGNED NOT NULL,
  `relationship_type` enum('parent','child','spouse','sibling','guardian','ward','grandparent','grandchild','step_parent','step_child','adoptive_parent','adopted_child','other') NOT NULL,
  `family_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_inverse` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `person_relationships`
--
DELIMITER $$
CREATE TRIGGER `trg_pr_after_insert` AFTER INSERT ON `person_relationships` FOR EACH ROW BEGIN
  -- Declarations must come first in the block
  DECLARE inv_type VARCHAR(32);

  IF NEW.is_inverse = 0 THEN
    SET inv_type = CASE NEW.relationship_type
      WHEN 'parent'           THEN 'child'
      WHEN 'child'            THEN 'parent'
      WHEN 'guardian'         THEN 'ward'
      WHEN 'ward'             THEN 'guardian'
      WHEN 'grandparent'      THEN 'grandchild'
      WHEN 'grandchild'       THEN 'grandparent'
      WHEN 'step_parent'      THEN 'step_child'
      WHEN 'step_child'       THEN 'step_parent'
      WHEN 'adoptive_parent'  THEN 'adopted_child'
      WHEN 'adopted_child'    THEN 'adoptive_parent'
      WHEN 'spouse'           THEN 'spouse'
      WHEN 'sibling'          THEN 'sibling'
      ELSE 'other'
    END;

    INSERT IGNORE INTO person_relationships
      (person_id, related_person_id, relationship_type, family_id, is_inverse, created_at, updated_at)
    VALUES
      (NEW.related_person_id, NEW.person_id, inv_type, NEW.family_id, 1, NOW(), NOW());
  END IF;
END
$$
DELIMITER ;

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

--
-- Triggers `site_profile`
--
DELIMITER $$
CREATE TRIGGER `trg_site_profile_singleton` BEFORE INSERT ON `site_profile` FOR EACH ROW BEGIN
  SET NEW.id = 1;
  IF (SELECT COUNT(*) FROM site_profile) > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'site_profile is singleton (only one row allowed)';
  END IF;
END
$$
DELIMITER ;

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
(1, 1, 'alf_red_c', 'davidgludo@gmail.com', NULL, '$2y$10$I4nDEJTEdqiWD8.Azf6YEe7mgUsfNLNYWWDXOmXEadXU6WDvb.hzK', 'active', '2025-11-11 21:08:02', '2025-11-09 10:04:31', '2025-11-11 21:08:02');

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
-- Triggers `vitals`
--
DELIMITER $$
CREATE TRIGGER `trg_vitals_bmi_bi` BEFORE INSERT ON `vitals` FOR EACH ROW BEGIN
  IF NEW.height_cm IS NOT NULL AND NEW.weight_kg IS NOT NULL AND NEW.height_cm > 0 THEN
    SET NEW.bmi = ROUND(NEW.weight_kg / POW(NEW.height_cm/100, 2), 2);
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_vitals_bmi_bu` BEFORE UPDATE ON `vitals` FOR EACH ROW BEGIN
  IF NEW.height_cm IS NOT NULL AND NEW.weight_kg IS NOT NULL AND NEW.height_cm > 0 THEN
    SET NEW.bmi = ROUND(NEW.weight_kg / POW(NEW.height_cm/100, 2), 2);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_household_profile`
-- (See below for the actual view)
--
CREATE TABLE `v_household_profile` (
`household_id` bigint(20) unsigned
,`barangay` varchar(255)
,`purok` varchar(255)
,`household_no` varchar(64)
,`address` varchar(512)
,`home_ownership` enum('Owned','Rented','Others')
,`construction_material` enum('Light','Strong','Mixed','Others')
,`lighting_facility` enum('Electricity','Kerosene','Others')
,`water_level` enum('Level I','Level II','Level III')
,`water_source` varchar(255)
,`water_storage` enum('Covered container','Uncovered container','None','Both')
,`garbage_container` enum('Covered','Uncovered')
,`garbage_segregated` tinyint(1)
,`garbage_disposal_method` enum('Hog Feeding','Burial Pit','Sanitary','Open Burning','Composting','Unsanitary','Open Dumping','Garbage Collection','Others','None')
,`toilet_type` enum('Sanitary','Unsanitary','None','Others')
,`latest_family_survey` date
,`families` bigint(21)
,`persons` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_latest_vitals`
-- (See below for the actual view)
--
CREATE TABLE `v_latest_vitals` (
`person_id` bigint(20) unsigned
,`survey_date` date
,`height_cm` decimal(5,2)
,`weight_kg` decimal(5,2)
,`bmi` decimal(5,2)
,`waist_circumference_cm` decimal(5,2)
,`bp_systolic` int(11)
,`bp_diastolic` int(11)
,`pulse` int(11)
,`temperature_c` decimal(4,1)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_person_health_history`
-- (See below for the actual view)
--
CREATE TABLE `v_person_health_history` (
`survey_id` bigint(20) unsigned
,`person_id` bigint(20) unsigned
,`survey_date` date
,`height_cm` decimal(5,2)
,`weight_kg` decimal(5,2)
,`bmi` decimal(5,2)
,`obesity_flag` tinyint(1)
,`waist_circumference_cm` decimal(5,2)
,`central_adiposity` tinyint(1)
,`bp_systolic` int(11)
,`bp_diastolic` int(11)
,`raised_bp` tinyint(1)
,`pulse` int(11)
,`temperature_c` decimal(4,1)
,`raised_blood_sugar` tinyint(1)
,`dyslipidemia` tinyint(1)
,`smoking_status` enum('Never','Stopped_gt_1yr','Current','Stopped_lt_1yr','Passive')
,`alcohol_use` enum('Never','Current','Former')
,`excessive_alcohol` tinyint(1)
,`eats_processed_weekly` tinyint(1)
,`fruits_3_servings_daily` tinyint(1)
,`vegetables_3_servings_daily` tinyint(1)
,`exercise_days_per_week` int(11)
,`exercise_minutes_per_day` int(11)
,`exercise_intensity` enum('Light','Moderate','Vigorous')
,`known_diabetes` tinyint(1)
,`on_medications` tinyint(1)
,`family_history` tinyint(1)
,`polyuria` tinyint(1)
,`polydipsia` tinyint(1)
,`polyphagia` tinyint(1)
,`weight_loss` tinyint(1)
,`rbs_mg_dl` decimal(6,2)
,`fbs_mg_dl` decimal(6,2)
,`hba1c_percent` decimal(4,2)
,`urine_ketone` tinyint(1)
,`urine_protein` tinyint(1)
,`screen_positive` tinyint(1)
,`is_approved` tinyint(1)
,`approved_by_official_id` bigint(20) unsigned
,`approved_at` datetime
,`notes` text
,`review_notes` varchar(512)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_person_risk_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_person_risk_summary` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_purok_monthly_events`
-- (See below for the actual view)
--
CREATE TABLE `v_purok_monthly_events` (
`purok_id` bigint(20) unsigned
,`purok_name` varchar(255)
,`period` date
,`births` bigint(21)
,`deaths` bigint(21)
,`moves_in` bigint(21)
,`moves_out` bigint(21)
,`net_change` bigint(24)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_purok_population_snapshot`
-- (See below for the actual view)
--
CREATE TABLE `v_purok_population_snapshot` (
`purok_id` bigint(20) unsigned
,`purok_name` varchar(255)
,`living_population` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `v_household_profile`
--
DROP TABLE IF EXISTS `v_household_profile`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_household_profile`  AS SELECT `h`.`id` AS `household_id`, (select `sp`.`barangay_name` from `site_profile` `sp` where `sp`.`id` = 1) AS `barangay`, `p`.`name` AS `purok`, `h`.`household_no` AS `household_no`, `h`.`address` AS `address`, `h`.`home_ownership` AS `home_ownership`, `h`.`construction_material` AS `construction_material`, `h`.`lighting_facility` AS `lighting_facility`, `h`.`water_level` AS `water_level`, `h`.`water_source` AS `water_source`, `h`.`water_storage` AS `water_storage`, `h`.`garbage_container` AS `garbage_container`, `h`.`garbage_segregated` AS `garbage_segregated`, `h`.`garbage_disposal_method` AS `garbage_disposal_method`, `h`.`toilet_type` AS `toilet_type`, max(`f`.`survey_date`) AS `latest_family_survey`, count(distinct `f`.`id`) AS `families`, count(distinct `pr`.`id`) AS `persons` FROM (((`households` `h` left join `puroks` `p` on(`p`.`id` = `h`.`purok_id`)) left join `families` `f` on(`f`.`household_id` = `h`.`id`)) left join `persons` `pr` on(`pr`.`family_id` = `f`.`id`)) GROUP BY `h`.`id`, `p`.`name`, `h`.`household_no`, `h`.`address`, `h`.`home_ownership`, `h`.`construction_material`, `h`.`lighting_facility`, `h`.`water_level`, `h`.`water_source`, `h`.`water_storage`, `h`.`garbage_container`, `h`.`garbage_segregated`, `h`.`garbage_disposal_method`, `h`.`toilet_type` ;

-- --------------------------------------------------------

--
-- Structure for view `v_latest_vitals`
--
DROP TABLE IF EXISTS `v_latest_vitals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_latest_vitals`  AS SELECT `t`.`person_id` AS `person_id`, `t`.`survey_date` AS `survey_date`, `t`.`height_cm` AS `height_cm`, `t`.`weight_kg` AS `weight_kg`, `t`.`bmi` AS `bmi`, `t`.`waist_circumference_cm` AS `waist_circumference_cm`, `t`.`bp_systolic` AS `bp_systolic`, `t`.`bp_diastolic` AS `bp_diastolic`, `t`.`pulse` AS `pulse`, `t`.`temperature_c` AS `temperature_c` FROM (select `c`.`person_id` AS `person_id`,`c`.`survey_date` AS `survey_date`,`v`.`height_cm` AS `height_cm`,`v`.`weight_kg` AS `weight_kg`,`v`.`bmi` AS `bmi`,`v`.`waist_circumference_cm` AS `waist_circumference_cm`,`v`.`bp_systolic` AS `bp_systolic`,`v`.`bp_diastolic` AS `bp_diastolic`,`v`.`pulse` AS `pulse`,`v`.`temperature_c` AS `temperature_c`,row_number() over ( partition by `c`.`person_id` order by `c`.`survey_date` desc,`v`.`id` desc) AS `rn` from (`cvd_ncd_risk_assessments` `c` join `vitals` `v` on(`v`.`cvd_id` = `c`.`id`))) AS `t` WHERE `t`.`rn` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `v_person_health_history`
--
DROP TABLE IF EXISTS `v_person_health_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_person_health_history`  AS SELECT `c`.`id` AS `survey_id`, `c`.`person_id` AS `person_id`, `c`.`survey_date` AS `survey_date`, `v`.`height_cm` AS `height_cm`, `v`.`weight_kg` AS `weight_kg`, `v`.`bmi` AS `bmi`, `v`.`obesity_flag` AS `obesity_flag`, `v`.`waist_circumference_cm` AS `waist_circumference_cm`, `v`.`central_adiposity` AS `central_adiposity`, `v`.`bp_systolic` AS `bp_systolic`, `v`.`bp_diastolic` AS `bp_diastolic`, `v`.`raised_bp` AS `raised_bp`, `v`.`pulse` AS `pulse`, `v`.`temperature_c` AS `temperature_c`, `v`.`raised_blood_sugar` AS `raised_blood_sugar`, `v`.`dyslipidemia` AS `dyslipidemia`, `lr`.`smoking_status` AS `smoking_status`, `lr`.`alcohol_use` AS `alcohol_use`, `lr`.`excessive_alcohol` AS `excessive_alcohol`, `lr`.`eats_processed_weekly` AS `eats_processed_weekly`, `lr`.`fruits_3_servings_daily` AS `fruits_3_servings_daily`, `lr`.`vegetables_3_servings_daily` AS `vegetables_3_servings_daily`, `lr`.`exercise_days_per_week` AS `exercise_days_per_week`, `lr`.`exercise_minutes_per_day` AS `exercise_minutes_per_day`, `lr`.`exercise_intensity` AS `exercise_intensity`, `ds`.`known_diabetes` AS `known_diabetes`, `ds`.`on_medications` AS `on_medications`, `ds`.`family_history` AS `family_history`, `ds`.`polyuria` AS `polyuria`, `ds`.`polydipsia` AS `polydipsia`, `ds`.`polyphagia` AS `polyphagia`, `ds`.`weight_loss` AS `weight_loss`, `ds`.`rbs_mg_dl` AS `rbs_mg_dl`, `ds`.`fbs_mg_dl` AS `fbs_mg_dl`, `ds`.`hba1c_percent` AS `hba1c_percent`, `ds`.`urine_ketone` AS `urine_ketone`, `ds`.`urine_protein` AS `urine_protein`, `ds`.`screen_positive` AS `screen_positive`, `c`.`is_approved` AS `is_approved`, `c`.`approved_by_official_id` AS `approved_by_official_id`, `c`.`approved_at` AS `approved_at`, `c`.`notes` AS `notes`, `c`.`review_notes` AS `review_notes` FROM (((`cvd_ncd_risk_assessments` `c` left join `vitals` `v` on(`v`.`cvd_id` = `c`.`id`)) left join `lifestyle_risk` `lr` on(`lr`.`cvd_id` = `c`.`id`)) left join `diabetes_screening` `ds` on(`ds`.`cvd_id` = `c`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_person_risk_summary`
--
DROP TABLE IF EXISTS `v_person_risk_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_person_risk_summary`  AS WITH latest_cvd AS (SELECT `c`.`id` AS `id`, `c`.`person_id` AS `person_id`, `c`.`surveyed_by_official_id` AS `surveyed_by_official_id`, `c`.`survey_date` AS `survey_date`, `c`.`obesity` AS `obesity`, `c`.`central_adiposity` AS `central_adiposity`, `c`.`raised_bp` AS `raised_bp`, `c`.`raised_blood_sugar` AS `raised_blood_sugar`, `c`.`dyslipidemia` AS `dyslipidemia`, `c`.`notes` AS `notes`, `c`.`is_approved` AS `is_approved`, `c`.`approved_by_official_id` AS `approved_by_official_id`, `c`.`approved_at` AS `approved_at`, `c`.`review_notes` AS `review_notes`, row_number() over ( partition by `c`.`person_id` order by `c`.`survey_date` desc,`c`.`id` desc) AS `rn` FROM `cvd_ncd_risk_assessments` AS `c`)  SELECT `p`.`id` AS `person_id`, `p`.`last_name` AS `last_name`, `p`.`first_name` AS `first_name`, `p`.`sex` AS `sex`, `p`.`birthdate` AS `birthdate`, `lc`.`survey_date` AS `latest_survey_date`, `v`.`height_cm` AS `height_cm`, `v`.`weight_kg` AS `weight_kg`, `v`.`bmi` AS `bmi`, `v`.`waist_circumference_cm` AS `waist_circumference_cm`, `v`.`bp_systolic` AS `bp_systolic`, `v`.`bp_diastolic` AS `bp_diastolic`, `v`.`pulse` AS `pulse`, `v`.`temperature_c` AS `temperature_c`, `v`.`central_adiposity` AS `central_adiposity`, `v`.`raised_bp` AS `raised_bp`, `v`.`raised_blood_sugar` AS `raised_blood_sugar`, `v`.`dyslipidemia` AS `dyslipidemia`, `v`.`obesity_flag` AS `obesity_flag`, `lr`.`smoking_status` AS `smoking_status`, `lr`.`alcohol_use` AS `alcohol_use`, `lr`.`excessive_alcohol` AS `excessive_alcohol`, `lr`.`eats_processed_weekly` AS `eats_processed_weekly`, `lr`.`fruits_3_servings_daily` AS `fruits_3_servings_daily`, `lr`.`vegetables_3_servings_daily` AS `vegetables_3_servings_daily`, `lr`.`exercise_days_per_week` AS `exercise_days_per_week`, `lr`.`exercise_minutes_per_day` AS `exercise_minutes_per_day`, `lr`.`exercise_intensity` AS `exercise_intensity`, `ds`.`known_diabetes` AS `known_diabetes`, `ds`.`on_medications` AS `on_medications`, `ds`.`family_history` AS `family_history`, `ds`.`polyuria` AS `polyuria`, `ds`.`polydipsia` AS `polydipsia`, `ds`.`polyphagia` AS `polyphagia`, `ds`.`weight_loss` AS `weight_loss`, `ds`.`rbs_mg_dl` AS `rbs_mg_dl`, `ds`.`fbs_mg_dl` AS `fbs_mg_dl`, `ds`.`hba1c_percent` AS `hba1c_percent`, `ds`.`urine_ketone` AS `urine_ketone`, `ds`.`urine_protein` AS `urine_protein`, `ds`.`screen_positive` AS `screen_positive` FROM ((((`persons` `p` left join `latest_cvd` `lc` on(`lc`.`person_id` = `p`.`id` and `lc`.`rn` = 1)) left join `vitals` `v` on(`v`.`cvd_id` = `lc`.`id`)) left join `lifestyle_risk` `lr` on(`lr`.`cvd_id` = `lc`.`id`)) left join `diabetes_screening` `ds` on(`ds`.`cvd_id` = `lc`.`id`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `v_purok_monthly_events`
--
DROP TABLE IF EXISTS `v_purok_monthly_events`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_purok_monthly_events`  AS WITH periods AS (SELECT `h`.`purok_id` AS `purok_id`, str_to_date(date_format(`bi`.`delivery_date`,'%Y-%m-01'),'%Y-%m-%d') AS `period` FROM (((`births` `bi` join `persons` `mom` on(`mom`.`id` = `bi`.`mother_id`)) join `families` `f` on(`f`.`id` = `mom`.`family_id`)) join `households` `h` on(`h`.`id` = `f`.`household_id`)) WHERE `bi`.`delivery_date` is not null UNION SELECT `h`.`purok_id` AS `purok_id`, str_to_date(date_format(`de`.`death_date`,'%Y-%m-01'),'%Y-%m-%d') AS `period` FROM (((`deaths` `de` join `persons` `pr` on(`pr`.`id` = `de`.`person_id`)) join `families` `f` on(`f`.`id` = `pr`.`family_id`)) join `households` `h` on(`h`.`id` = `f`.`household_id`)) WHERE `de`.`death_date` is not null UNION SELECT `m`.`to_purok_id` AS `to_purok_id`, str_to_date(date_format(`m`.`moved_at`,'%Y-%m-01'),'%Y-%m-%d') AS `period` FROM `migrations` AS `m` WHERE `m`.`moved_at` is not null AND `m`.`to_purok_id` is not null UNION SELECT `m`.`from_purok_id` AS `from_purok_id`, str_to_date(date_format(`m`.`moved_at`,'%Y-%m-01'),'%Y-%m-%d') AS `period` FROM `migrations` AS `m` WHERE `m`.`moved_at` is not null AND `m`.`from_purok_id` is not null), b AS (SELECT `h`.`purok_id` AS `purok_id`, str_to_date(date_format(`bi`.`delivery_date`,'%Y-%m-01'),'%Y-%m-%d') AS `period`, count(0) AS `births` FROM (((`births` `bi` join `persons` `mom` on(`mom`.`id` = `bi`.`mother_id`)) join `families` `f` on(`f`.`id` = `mom`.`family_id`)) join `households` `h` on(`h`.`id` = `f`.`household_id`)) WHERE `bi`.`delivery_date` is not null GROUP BY `h`.`purok_id`, str_to_date(date_format(`bi`.`delivery_date`,'%Y-%m-01'),'%Y-%m-%d')), d AS (SELECT `h`.`purok_id` AS `purok_id`, str_to_date(date_format(`de`.`death_date`,'%Y-%m-01'),'%Y-%m-%d') AS `period`, count(0) AS `deaths` FROM (((`deaths` `de` join `persons` `pr` on(`pr`.`id` = `de`.`person_id`)) join `families` `f` on(`f`.`id` = `pr`.`family_id`)) join `households` `h` on(`h`.`id` = `f`.`household_id`)) WHERE `de`.`death_date` is not null GROUP BY `h`.`purok_id`, str_to_date(date_format(`de`.`death_date`,'%Y-%m-01'),'%Y-%m-%d')), mi AS (SELECT `m`.`to_purok_id` AS `purok_id`, str_to_date(date_format(`m`.`moved_at`,'%Y-%m-01'),'%Y-%m-%d') AS `period`, count(0) AS `moves_in` FROM `migrations` AS `m` WHERE `m`.`moved_at` is not null AND `m`.`to_purok_id` is not null GROUP BY `m`.`to_purok_id`, str_to_date(date_format(`m`.`moved_at`,'%Y-%m-01'),'%Y-%m-%d')), mo AS (SELECT `m`.`from_purok_id` AS `purok_id`, str_to_date(date_format(`m`.`moved_at`,'%Y-%m-01'),'%Y-%m-%d') AS `period`, count(0) AS `moves_out` FROM `migrations` AS `m` WHERE `m`.`moved_at` is not null AND `m`.`from_purok_id` is not null GROUP BY `m`.`from_purok_id`, str_to_date(date_format(`m`.`moved_at`,'%Y-%m-01'),'%Y-%m-%d'))  SELECT `p`.`id` AS `purok_id`, `p`.`name` AS `purok_name`, `pr`.`period` AS `period`, coalesce(`b`.`births`,0) AS `births`, coalesce(`d`.`deaths`,0) AS `deaths`, coalesce(`mi`.`moves_in`,0) AS `moves_in`, coalesce(`mo`.`moves_out`,0) AS `moves_out`, coalesce(`b`.`births`,0) - coalesce(`d`.`deaths`,0) + coalesce(`mi`.`moves_in`,0) - coalesce(`mo`.`moves_out`,0) AS `net_change` FROM (((((`puroks` `p` join `periods` `pr` on(`pr`.`purok_id` = `p`.`id`)) left join `b` on(`b`.`purok_id` = `p`.`id` and `b`.`period` = `pr`.`period`)) left join `d` on(`d`.`purok_id` = `p`.`id` and `d`.`period` = `pr`.`period`)) left join `mi` on(`mi`.`purok_id` = `p`.`id` and `mi`.`period` = `pr`.`period`)) left join `mo` on(`mo`.`purok_id` = `p`.`id` and `mo`.`period` = `pr`.`period`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `v_purok_population_snapshot`
--
DROP TABLE IF EXISTS `v_purok_population_snapshot`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_purok_population_snapshot`  AS SELECT `p`.`id` AS `purok_id`, `p`.`name` AS `purok_name`, count(distinct `pr`.`id`) AS `living_population` FROM (((`puroks` `p` left join `households` `h` on(`h`.`purok_id` = `p`.`id`)) left join `families` `f` on(`f`.`household_id` = `h`.`id`)) left join `persons` `pr` on(`pr`.`family_id` = `f`.`id` and ifnull(`pr`.`is_deceased`,0) = 0)) GROUP BY `p`.`id`, `p`.`name` ;

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_births_mother` (`mother_id`),
  ADD KEY `idx_births_child` (`child_person_id`);

--
-- Indexes for table `cvd_ncd_risk_assessments`
--
ALTER TABLE `cvd_ncd_risk_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cvd_person_date` (`person_id`,`survey_date`),
  ADD KEY `idx_cvd_is_approved` (`is_approved`),
  ADD KEY `fk_cvd_approved_by_official` (`approved_by_official_id`),
  ADD KEY `fk_cvd_surveyed_by_official` (`surveyed_by_official_id`);

--
-- Indexes for table `deaths`
--
ALTER TABLE `deaths`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deaths_person` (`person_id`);

--
-- Indexes for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_diabetes_cvd` (`cvd_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `document_type_id` (`document_type_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`document_type_id`);

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
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_officials_username` (`username`),
  ADD KEY `idx_officials_role_active` (`role`,`active`);

--
-- Indexes for table `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_persons_family` (`family_id`),
  ADD KEY `idx_persons_name` (`last_name`,`first_name`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `births`
--
ALTER TABLE `births`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cvd_ncd_risk_assessments`
--
ALTER TABLE `cvd_ncd_risk_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deaths`
--
ALTER TABLE `deaths`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `document_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `health_family_history`
--
ALTER TABLE `health_family_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `persons`
--
ALTER TABLE `persons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `person_relationships`
--
ALTER TABLE `person_relationships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vitals`
--
ALTER TABLE `vitals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `angina_stroke_screening`
--
ALTER TABLE `angina_stroke_screening`
  ADD CONSTRAINT `fk_angina_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `births`
--
ALTER TABLE `births`
  ADD CONSTRAINT `fk_births_child` FOREIGN KEY (`child_person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_births_mother` FOREIGN KEY (`mother_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cvd_ncd_risk_assessments`
--
ALTER TABLE `cvd_ncd_risk_assessments`
  ADD CONSTRAINT `fk_cvd_approved_by_official` FOREIGN KEY (`approved_by_official_id`) REFERENCES `officials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cvd_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cvd_surveyed_by_official` FOREIGN KEY (`surveyed_by_official_id`) REFERENCES `officials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `deaths`
--
ALTER TABLE `deaths`
  ADD CONSTRAINT `fk_deaths_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  ADD CONSTRAINT `fk_diabetes_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `document_requests_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`document_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `families`
--
ALTER TABLE `families`
  ADD CONSTRAINT `fk_families_head_person` FOREIGN KEY (`head_person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_families_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `health_family_history`
--
ALTER TABLE `health_family_history`
  ADD CONSTRAINT `fk_hfh_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `households`
--
ALTER TABLE `households`
  ADD CONSTRAINT `fk_households_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  ADD CONSTRAINT `fk_lifestyle_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `migrations`
--
ALTER TABLE `migrations`
  ADD CONSTRAINT `fk_mig_from` FOREIGN KEY (`from_purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mig_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mig_to` FOREIGN KEY (`to_purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `morbidity_logs`
--
ALTER TABLE `morbidity_logs`
  ADD CONSTRAINT `fk_morbidity_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_morbidity_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_morbidity_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `persons`
--
ALTER TABLE `persons`
  ADD CONSTRAINT `fk_persons_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `person_relationships`
--
ALTER TABLE `person_relationships`
  ADD CONSTRAINT `fk_pr_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_related` FOREIGN KEY (`related_person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pregnancies`
--
ALTER TABLE `pregnancies`
  ADD CONSTRAINT `fk_pregnancies_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vitals`
--
ALTER TABLE `vitals`
  ADD CONSTRAINT `fk_vitals_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
