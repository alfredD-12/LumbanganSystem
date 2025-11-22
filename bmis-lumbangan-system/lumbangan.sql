-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2025 at 10:55 AM
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
(1, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-01 09:13:00'),
(2, 2, 0, 0, 0, NULL, NULL, NULL, 0, 0, '2025-11-01 09:14:00'),
(3, 3, 0, 0, 0, NULL, NULL, NULL, 0, 0, '2025-11-01 09:15:00'),
(4, 4, 0, 0, 0, NULL, NULL, NULL, 0, 0, '2025-11-01 09:16:00'),
(5, 5, 1, 1, 1, NULL, NULL, NULL, 1, 1, '2025-11-01 09:17:00'),
(6, 6, 0, 0, 0, NULL, NULL, NULL, 0, 0, '2025-11-01 09:18:00'),
(7, 7, 0, 0, 0, NULL, NULL, NULL, 0, 0, '2025-11-01 09:19:00'),
(8, 8, 0, 0, 0, NULL, NULL, NULL, 0, 0, '2025-11-01 09:20:00');

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
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
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
(1, 1, '2025-10-01 09:10:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', 'OK'),
(2, 2, '2025-11-01 09:11:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(3, 3, '2025-11-01 09:12:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(4, 4, '2025-11-01 09:13:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(5, 5, '2025-11-01 09:14:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(6, 6, '2025-11-01 09:15:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(7, 7, '2025-11-01 09:16:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(8, 8, '2025-11-01 09:17:00', 1, '2025-11-01', 'Monthly', 1, 1, '2025-11-01 10:00:00', NULL),
(9, 1, '2025-11-22 11:18:07', NULL, '2025-11-22', NULL, 0, NULL, NULL, NULL);

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
(1, 1, 0, 0, 0, NULL, NULL, NULL, NULL, 95.50, 90.00, 5.20, NULL, NULL, NULL, '2025-11-22 10:29:38'),
(2, 2, 0, 0, 0, NULL, NULL, NULL, NULL, 96.00, 91.00, 5.30, NULL, NULL, 0, '2025-11-01 09:13:00'),
(3, 3, 0, 0, 0, NULL, NULL, NULL, NULL, 92.00, 88.00, 5.00, NULL, NULL, 0, '2025-11-01 09:14:00'),
(4, 4, 0, 0, 0, NULL, NULL, NULL, NULL, 99.00, 95.00, 5.25, NULL, NULL, 0, '2025-11-01 09:15:00'),
(5, 5, 0, 0, 1, NULL, NULL, NULL, NULL, 110.00, 100.00, 5.80, NULL, NULL, 0, '2025-11-01 09:16:00'),
(6, 6, 0, 0, 0, NULL, NULL, NULL, NULL, 88.00, 82.00, 4.90, NULL, NULL, 0, '2025-11-01 09:17:00'),
(7, 7, 0, 0, 0, NULL, NULL, NULL, NULL, 85.00, 80.00, 4.80, NULL, NULL, 0, '2025-11-01 09:18:00'),
(8, 8, 0, 0, 0, NULL, NULL, NULL, NULL, 94.00, 90.00, 5.10, NULL, NULL, 0, '2025-11-01 09:19:00');

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
  `released_by` int(11) DEFAULT NULL,
  `requested_for` varchar(150) DEFAULT NULL,
  `relation_to_requestee` varchar(150) DEFAULT NULL
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
(1, 1, '0917-0000-001', 1, 'Permanent', 0, 'ramon.s@example.local', '2025-11-22', '2025-11-01 09:00:00', '2025-11-22 10:25:24'),
(2, 2, NULL, 9, 'Permanent', NULL, NULL, NULL, '2025-11-21 18:40:13', '2025-11-21 18:40:13');

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
(1, 1, 0, 0, 0, 0, 0, 0, 0, '2025-11-22'),
(2, 2, 1, 0, 0, 0, 0, 0, 0, '2025-11-01'),
(3, 3, 0, 0, 0, 0, 0, 0, 0, '2025-11-01'),
(4, 4, 0, 0, 0, 0, 0, 0, 0, '2025-11-01'),
(5, 5, 0, 0, 0, 0, 0, 0, 0, '2025-11-01'),
(6, 6, 0, 0, 0, 0, 0, 0, 0, '2025-11-01'),
(7, 7, 0, 0, 0, 0, 0, 0, 0, '2025-11-01'),
(8, 8, 0, 0, 0, 0, 0, 0, 0, '2025-11-01');

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
(1, 2, 'CA-001', 'Blk 2 Lt 25, Bougainvillea Street, Camia Homes', NULL, NULL, 'Rented', '', 'Light', '', 'Electricity', '', 'Level III', '', 'Both', '', 'Uncovered', 0, 'Garbage Collection', '', 'Sanitary', '', '2025-11-01 09:00:00', '2025-11-21 17:48:28'),
(2, NULL, NULL, 'Pending - To be updated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-21 18:40:13', '2025-11-21 18:40:13');

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
(2, 2, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 2, 20, 'Light'),
(3, 3, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 2, 20, 'Light'),
(4, 4, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 30, 'Moderate'),
(5, 5, 'Current', NULL, 'Current', 1, NULL, 1, 0, 0, 1, 20, 'Light'),
(6, 6, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 0, 0, 'Light'),
(7, 7, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 0, 0, 'Light'),
(8, 8, 'Never', NULL, 'Never', 0, NULL, 0, 1, 1, 3, 30, 'Moderate');

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
(1, 'Dr. Liza Santos', 'liza.santos', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', '2025-11-22 14:47:03', 'Baranggay Health Worker President', '09171234567', 'liza.santos@example.local', NULL, 1, '2025-01-01 08:00:00', '2025-11-22 14:47:03'),
(2, 'Juan Dela Cruz', 'bhw_president', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', NULL, 'Baranggay Health Worker', '09171234567', 'bhwpresident@example.com', NULL, 1, '2025-11-22 13:01:19', '2025-11-22 13:03:19'),
(3, 'Maria Santos', 'barangay_secretary', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', NULL, 'Baranggay Secretary', '09172345678', 'secretary@example.com', NULL, 1, '2025-11-22 13:01:19', '2025-11-22 13:01:19'),
(4, 'Pedro Ramos', 'bhw', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', NULL, 'Baranggay Health Worker', '09173456789', 'bhw@example.com', NULL, 1, '2025-11-22 13:01:19', '2025-11-22 13:01:19'),
(5, 'Carlos Ignacio', 'barangay_captain', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', NULL, 'Baranggay Captain', '09174567890', 'captain@example.com', NULL, 1, '2025-11-22 13:01:19', '2025-11-22 13:01:19');

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
(1, 1, 'Santos', 'Ramon', 'De Castro', NULL, 'Head', 'M', '1970-05-10', 'Married', 'O+', NULL, 'High School', 'Farmer', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-20 18:14:22'),
(2, 1, 'Santos', 'Carlos', 'A', NULL, 'Parent', 'M', '1945-03-03', 'Widowed', NULL, NULL, 'Elementary', 'Retired', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(3, 1, 'Santos', 'Lucia', 'B', NULL, 'Parent', 'F', '1948-08-20', 'Widowed', NULL, NULL, 'Elementary', 'Retired', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(4, 1, 'Santos', 'Maribel', 'M', NULL, 'Spouse', 'F', '1972-11-02', 'Married', NULL, NULL, 'College', 'Teacher', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(5, 1, 'Santos', 'Daniel', NULL, NULL, 'Child', 'M', '1998-02-14', 'Married', NULL, NULL, 'High School', 'Driver', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(6, 1, 'Santos', 'Miguel', NULL, NULL, 'Grandchild', 'M', '2016-01-10', 'Single', NULL, NULL, 'None', 'Child', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(7, 1, 'Santos', 'Juan', NULL, NULL, 'Grandchild', 'M', '2018-03-15', 'Single', NULL, NULL, 'None', 'Child', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(8, 1, 'Santos', 'Elena', NULL, NULL, 'Child', 'F', '2001-07-20', 'Single', NULL, NULL, 'Vocational', 'Nurse', 'Catholic', 0, 0, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(9, 2, 'Gludo', 'David Alfred', 'Cabali', NULL, 'Head', NULL, NULL, 'Single', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-11-21 18:40:13', '2025-11-21 18:40:13');

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
-- Dumping data for table `person_relationships`
--

INSERT INTO `person_relationships` (`id`, `person_id`, `related_person_id`, `relationship_type`, `family_id`, `is_inverse`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'parent', 1, 0, '2025-11-01 09:45:00', '2025-11-01 09:45:00'),
(2, 1, 2, 'child', 1, 1, '2025-11-01 09:45:00', '2025-11-01 09:45:00'),
(3, 3, 1, 'parent', 1, 0, '2025-11-01 09:46:00', '2025-11-01 09:46:00'),
(4, 1, 3, 'child', 1, 1, '2025-11-01 09:46:00', '2025-11-01 09:46:00'),
(5, 1, 4, 'spouse', 1, 0, '2025-11-01 09:47:00', '2025-11-01 09:47:00'),
(6, 4, 1, 'spouse', 1, 1, '2025-11-01 09:47:00', '2025-11-01 09:47:00'),
(7, 1, 5, 'parent', 1, 0, '2025-11-01 09:48:00', '2025-11-01 09:48:00'),
(8, 5, 1, 'child', 1, 1, '2025-11-01 09:48:00', '2025-11-01 09:48:00'),
(9, 1, 8, 'parent', 1, 0, '2025-11-01 09:49:00', '2025-11-01 09:49:00'),
(10, 8, 1, 'child', 1, 1, '2025-11-01 09:49:00', '2025-11-01 09:49:00'),
(11, 4, 5, 'parent', 1, 0, '2025-11-01 09:50:00', '2025-11-01 09:50:00'),
(12, 5, 4, 'child', 1, 1, '2025-11-01 09:50:00', '2025-11-01 09:50:00'),
(13, 4, 8, 'parent', 1, 0, '2025-11-01 09:51:00', '2025-11-01 09:51:00'),
(14, 8, 4, 'child', 1, 1, '2025-11-01 09:51:00', '2025-11-01 09:51:00'),
(15, 5, 8, 'sibling', 1, 0, '2025-11-19 14:08:44', '2025-11-19 14:08:44'),
(16, 8, 5, 'sibling', 1, 1, '2025-11-19 14:08:44', '2025-11-19 14:08:44'),
(17, 5, 6, 'parent', 1, 0, '2025-11-19 14:08:44', '2025-11-19 14:08:44'),
(18, 6, 5, 'child', 1, 1, '2025-11-19 14:08:44', '2025-11-19 14:08:44'),
(19, 5, 7, 'parent', 1, 0, '2025-11-19 14:08:44', '2025-11-19 14:08:44'),
(20, 7, 5, 'child', 1, 1, '2025-11-19 14:08:44', '2025-11-19 14:08:44');

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
(1, 1, 'ramon.s', 'ramon.s@example.local', '0917-0002-112', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', '2025-11-22 16:16:44', '2025-11-01 09:00:00', '2025-11-22 16:16:44'),
(2, 2, 'carlos.s', 'carlos.s@example.local', '09170000002', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', NULL, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(3, 3, 'lucia.s', 'lucia.s@example.local', '09170000003', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', '2025-11-19 14:42:25', '2025-11-01 09:00:00', '2025-11-19 14:42:25'),
(4, 4, 'maribel.s', 'maribel.s@example.local', '09170000004', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', NULL, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(5, 5, 'daniel.s', 'daniel.s@example.local', '09170000005', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', '2025-11-19 14:43:31', '2025-11-01 09:00:00', '2025-11-19 14:43:31'),
(6, 6, 'miguel.s', 'miguel.s@example.local', '09170000006', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', NULL, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(7, 7, 'juan.s', 'juan.s@example.local', '09170000007', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', NULL, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(8, 8, 'elena.s', 'elena.s@example.local', '09170000008', '$2y$10$/1bBfv0yAdk3Y2jHv6xq0eEUPTYH8Do31g71CovKKMprwItmAOCFK', 'active', NULL, '2025-11-01 09:00:00', '2025-11-01 09:00:00'),
(9, 9, 'alf_red_c', 'davidgludo@gmail.com', NULL, '$2y$10$F.fyBS.tjU9PN9A5Y1FH4u3wyTL9smL3kYi2rhlQAtDKa9EHcgpma', 'active', '2025-11-22 16:17:36', '2025-11-21 18:40:13', '2025-11-22 16:17:36');

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
(1, 1, 168.00, 70.00, NULL, NULL, NULL, NULL, NULL, 20.00, 110, 80, 74, 20, 36.6),
(2, 2, 165.00, 68.00, NULL, NULL, NULL, NULL, NULL, 84.00, 122, 80, 70, 16, 36.6),
(3, 3, 160.00, 60.00, NULL, NULL, NULL, NULL, NULL, 80.00, 120, 78, 68, 16, 36.6),
(4, 4, 158.00, 62.00, NULL, NULL, NULL, NULL, NULL, 79.00, 118, 76, 69, 16, 36.6),
(5, 5, 170.00, 78.00, NULL, NULL, NULL, NULL, NULL, 90.00, 130, 85, 75, 16, 36.7),
(6, 6, 120.00, 25.00, NULL, NULL, NULL, NULL, NULL, 50.00, 95, 60, 100, 20, 36.5),
(7, 7, 110.00, 22.00, NULL, NULL, NULL, NULL, NULL, 48.00, 90, 58, 98, 20, 36.5),
(8, 8, 155.00, 55.00, NULL, NULL, NULL, NULL, NULL, 72.00, 115, 75, 66, 16, 36.6),
(9, 9, 168.00, 78.00, NULL, NULL, NULL, NULL, NULL, 22.00, NULL, NULL, NULL, NULL, NULL);

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
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `deaths`
--
ALTER TABLE `deaths`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_family_history`
--
ALTER TABLE `health_family_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `persons`
--
ALTER TABLE `persons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `person_relationships`
--
ALTER TABLE `person_relationships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vitals`
--
ALTER TABLE `vitals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
