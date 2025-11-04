-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2025 at 03:20 PM
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
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `encounter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `household_id` bigint(20) UNSIGNED DEFAULT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_uri` varchar(1024) NOT NULL,
  `file_type` varchar(128) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
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
  `encounter_id` bigint(20) UNSIGNED NOT NULL,
  `obesity` tinyint(1) DEFAULT NULL,
  `central_adiposity` tinyint(1) DEFAULT NULL,
  `raised_bp` tinyint(1) DEFAULT NULL,
  `raised_blood_sugar` tinyint(1) DEFAULT NULL,
  `dyslipidemia` tinyint(1) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `assessed_at` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `encounter_id` bigint(20) UNSIGNED NOT NULL,
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
  `released_by` int(11) DEFAULT NULL,
  `requested_for` varchar(150) DEFAULT NULL,
  `relation_to_requestee` varchar(100) DEFAULT NULL
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
-- Table structure for table `encounters`
--

CREATE TABLE `encounters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `household_id` bigint(20) UNSIGNED DEFAULT NULL,
  `encounter_date` date NOT NULL,
  `encounter_type` enum('Survey','Clinic','Home Visit','Follow-up') NOT NULL DEFAULT 'Survey',
  `interviewer` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE `families` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `household_id` bigint(20) UNSIGNED NOT NULL,
  `family_number` varchar(64) DEFAULT NULL,
  `residency_status` enum('Permanent','Present') DEFAULT NULL,
  `length_of_residency_months` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `complete_address` varchar(512) DEFAULT NULL,
  `primary_care_provider` varchar(255) DEFAULT NULL,
  `primary_care_contact` varchar(64) DEFAULT NULL,
  `residence_to_pcp_minutes` int(11) DEFAULT NULL,
  `mode_of_transport` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `families`
--

INSERT INTO `families` (`id`, `household_id`, `family_number`, `residency_status`, `length_of_residency_months`, `email`, `complete_address`, `primary_care_provider`, `primary_care_contact`, `residence_to_pcp_minutes`, `mode_of_transport`, `created_at`, `updated_at`) VALUES
(1, 1, 'FAM-0001', 'Permanent', 60, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-23 19:59:25', '2025-10-23 19:59:25');

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
  `recorded_at` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 'HH-0001', 'Sitio Example', 14.074321, 120.613210, 'Owned', NULL, 'Mixed', NULL, 'Electricity', NULL, 'Level III', 'Waterworks', 'Covered container', NULL, 'Covered', 1, 'Garbage Collection', NULL, 'Sanitary', NULL, '2025-10-23 19:59:25', '2025-10-23 19:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `lab_results`
--

CREATE TABLE `lab_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `encounter_id` bigint(20) UNSIGNED NOT NULL,
  `test_type` varchar(64) DEFAULT NULL,
  `value_text` varchar(255) DEFAULT NULL,
  `value_num` decimal(12,3) DEFAULT NULL,
  `unit` varchar(32) DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lifestyle_risk`
--

CREATE TABLE `lifestyle_risk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `encounter_id` bigint(20) UNSIGNED NOT NULL,
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
  `exercise_intensity` enum('Light','Moderate','Vigorous') DEFAULT NULL,
  `recorded_at` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `morbidity_logs`
--

CREATE TABLE `morbidity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_date` date NOT NULL DEFAULT curdate(),
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
  `blood_type` varchar(8) DEFAULT NULL,
  `disability` varchar(255) DEFAULT NULL,
  `highest_educ_attainment` varchar(64) DEFAULT NULL,
  `occupation` varchar(128) DEFAULT NULL,
  `contact_no` varchar(64) DEFAULT NULL,
  `religion` varchar(64) DEFAULT NULL,
  `nhip_membership` varchar(64) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `waist_circumference_cm` decimal(5,2) DEFAULT NULL,
  `is_pregnant` tinyint(1) DEFAULT NULL,
  `is_deceased` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`id`, `family_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `family_position`, `sex`, `birthdate`, `marital_status`, `blood_type`, `disability`, `highest_educ_attainment`, `occupation`, `contact_no`, `religion`, `nhip_membership`, `height_cm`, `weight_kg`, `waist_circumference_cm`, `is_pregnant`, `is_deceased`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dela Cruz', 'Juan', NULL, NULL, NULL, 'M', '1990-01-15', 'M', NULL, NULL, 'College', 'Farmer', '0917XXXXXXX', 'Roman Catholic', 'Member-direct', NULL, NULL, NULL, NULL, 0, '2025-10-23 19:59:26', '2025-10-23 19:59:26'),
(2, 1, 'Dela Cruz', 'Maria', NULL, NULL, NULL, 'F', '1992-05-02', 'M', NULL, NULL, 'College', 'Vendor', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-10-23 19:59:26', '2025-10-23 19:59:26');

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
(1, 'Purok 1', NULL, NULL, '2025-10-23 19:59:25', '2025-10-23 19:59:25'),
(2, 'Purok 2', NULL, NULL, '2025-10-23 19:59:25', '2025-10-23 19:59:25'),
(3, 'Purok 3', NULL, NULL, '2025-10-23 19:59:25', '2025-10-23 19:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `screening`
--

CREATE TABLE `screening` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `encounter_id` bigint(20) UNSIGNED NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `service_progress_notes`
--

CREATE TABLE `service_progress_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `household_id` bigint(20) UNSIGNED DEFAULT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date_of_service` date NOT NULL DEFAULT curdate(),
  `health_condition_problem` varchar(255) DEFAULT NULL,
  `observations_actions_outcomes` text DEFAULT NULL,
  `provider_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Lumbangan', 'Nasugbu', 'Batangas', 'Region IV-A (CALABARZON)', NULL, 'Barangay Hall, Lumbangan, Nasugbu, Batangas', '0999-000-0000', 'info@lumbangan.local', '2025-10-23 19:59:25', '2025-10-23 19:59:25');

--
-- Triggers `site_profile`
--
DELIMITER $$
CREATE TRIGGER `trg_site_profile_singleton` BEFORE INSERT ON `site_profile` FOR EACH ROW BEGIN
  SET NEW.id = 1;
  IF (SELECT COUNT(*) FROM site_profile) > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'site_profile is singleton';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vitals`
--

CREATE TABLE `vitals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `encounter_id` bigint(20) UNSIGNED NOT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `waist_circumference_cm` decimal(5,2) DEFAULT NULL,
  `bp_systolic` int(11) DEFAULT NULL,
  `bp_diastolic` int(11) DEFAULT NULL,
  `pulse` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `taken_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_household_profile`
-- (See below for the actual view)
--
CREATE TABLE `v_household_profile` (
`household_id` bigint(20) unsigned
,`barangay` varchar(9)
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
,`encounter_date` date
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
-- Stand-in structure for view `v_person_risk_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_person_risk_summary` (
`person_id` bigint(20) unsigned
,`last_name` varchar(128)
,`first_name` varchar(128)
,`sex` enum('M','F')
,`birthdate` date
,`encounter_date` date
,`height_cm` decimal(5,2)
,`weight_kg` decimal(5,2)
,`bmi` decimal(5,2)
,`waist_circumference_cm` decimal(5,2)
,`bp_systolic` int(11)
,`bp_diastolic` int(11)
,`pulse` int(11)
,`temperature_c` decimal(4,1)
,`smoking_status` enum('Never','Stopped_gt_1yr','Current','Stopped_lt_1yr','Passive')
,`alcohol_use` enum('Never','Current','Former')
,`excessive_alcohol` tinyint(1)
,`eats_processed_weekly` tinyint(1)
,`fruits_3_servings_daily` tinyint(1)
,`vegetables_3_servings_daily` tinyint(1)
,`obesity` tinyint(1)
,`central_adiposity` tinyint(1)
,`raised_bp` tinyint(1)
,`raised_blood_sugar` tinyint(1)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_encounter` (`encounter_id`),
  ADD KEY `idx_att_household` (`household_id`),
  ADD KEY `idx_att_person` (`person_id`);

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
  ADD KEY `idx_cvd_encounter` (`encounter_id`);

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
  ADD KEY `idx_diabetes_encounter` (`encounter_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `released_by` (`released_by`),
  ADD KEY `document_type_id` (`document_type_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`document_type_id`);

--
-- Indexes for table `encounters`
--
ALTER TABLE `encounters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_encounters_person` (`person_id`),
  ADD KEY `idx_encounters_household` (`household_id`),
  ADD KEY `idx_encounters_date` (`encounter_date`);

--
-- Indexes for table `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_families_household` (`household_id`);

--
-- Indexes for table `health_family_history`
--
ALTER TABLE `health_family_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_family_history_person_date` (`person_id`,`recorded_at`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_households_purok` (`purok_id`);

--
-- Indexes for table `lab_results`
--
ALTER TABLE `lab_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_labs_encounter` (`encounter_id`),
  ADD KEY `idx_labs_type` (`test_type`);

--
-- Indexes for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lifestyle_encounter` (`encounter_id`);

--
-- Indexes for table `morbidity_logs`
--
ALTER TABLE `morbidity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_morbidity_person` (`person_id`),
  ADD KEY `idx_morbidity_household` (`household_id`),
  ADD KEY `idx_morbidity_purok` (`purok_id`);

--
-- Indexes for table `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_persons_family` (`family_id`),
  ADD KEY `idx_persons_name` (`last_name`,`first_name`);

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
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `screening`
--
ALTER TABLE `screening`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_angina_encounter` (`encounter_id`);

--
-- Indexes for table `service_progress_notes`
--
ALTER TABLE `service_progress_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_spn_household` (`household_id`),
  ADD KEY `idx_spn_person` (`person_id`);

--
-- Indexes for table `site_profile`
--
ALTER TABLE `site_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `vitals`
--
ALTER TABLE `vitals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vitals_encounter` (`encounter_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `births`
--
ALTER TABLE `births`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cvd_ncd_risk_assessments`
--
ALTER TABLE `cvd_ncd_risk_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `encounters`
--
ALTER TABLE `encounters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_family_history`
--
ALTER TABLE `health_family_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_results`
--
ALTER TABLE `lab_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `morbidity_logs`
--
ALTER TABLE `morbidity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `persons`
--
ALTER TABLE `persons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pregnancies`
--
ALTER TABLE `pregnancies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `puroks`
--
ALTER TABLE `puroks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `screening`
--
ALTER TABLE `screening`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_progress_notes`
--
ALTER TABLE `service_progress_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vitals`
--
ALTER TABLE `vitals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `v_household_profile`
--
DROP TABLE IF EXISTS `v_household_profile`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_household_profile`  AS SELECT `h`.`id` AS `household_id`, 'Lumbangan' AS `barangay`, `p`.`name` AS `purok`, `h`.`household_no` AS `household_no`, `h`.`address` AS `address`, `h`.`home_ownership` AS `home_ownership`, `h`.`construction_material` AS `construction_material`, `h`.`lighting_facility` AS `lighting_facility`, `h`.`water_level` AS `water_level`, `h`.`water_source` AS `water_source`, `h`.`water_storage` AS `water_storage`, `h`.`garbage_container` AS `garbage_container`, `h`.`garbage_segregated` AS `garbage_segregated`, `h`.`garbage_disposal_method` AS `garbage_disposal_method`, `h`.`toilet_type` AS `toilet_type`, count(distinct `f`.`id`) AS `families`, count(distinct `pr`.`id`) AS `persons` FROM (((`households` `h` left join `puroks` `p` on(`p`.`id` = `h`.`purok_id`)) left join `families` `f` on(`f`.`household_id` = `h`.`id`)) left join `persons` `pr` on(`pr`.`family_id` = `f`.`id`)) GROUP BY `h`.`id`, `p`.`name`, `h`.`household_no`, `h`.`address`, `h`.`home_ownership`, `h`.`construction_material`, `h`.`lighting_facility`, `h`.`water_level`, `h`.`water_source`, `h`.`water_storage`, `h`.`garbage_container`, `h`.`garbage_segregated`, `h`.`garbage_disposal_method`, `h`.`toilet_type` ;

-- --------------------------------------------------------

--
-- Structure for view `v_latest_vitals`
--
DROP TABLE IF EXISTS `v_latest_vitals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_latest_vitals`  AS SELECT `t`.`person_id` AS `person_id`, `t`.`encounter_date` AS `encounter_date`, `t`.`height_cm` AS `height_cm`, `t`.`weight_kg` AS `weight_kg`, `t`.`bmi` AS `bmi`, `t`.`waist_circumference_cm` AS `waist_circumference_cm`, `t`.`bp_systolic` AS `bp_systolic`, `t`.`bp_diastolic` AS `bp_diastolic`, `t`.`pulse` AS `pulse`, `t`.`temperature_c` AS `temperature_c` FROM (select `e`.`person_id` AS `person_id`,`e`.`encounter_date` AS `encounter_date`,`v`.`height_cm` AS `height_cm`,`v`.`weight_kg` AS `weight_kg`,`v`.`bmi` AS `bmi`,`v`.`waist_circumference_cm` AS `waist_circumference_cm`,`v`.`bp_systolic` AS `bp_systolic`,`v`.`bp_diastolic` AS `bp_diastolic`,`v`.`pulse` AS `pulse`,`v`.`temperature_c` AS `temperature_c`,row_number() over ( partition by `e`.`person_id` order by `e`.`encounter_date` desc,`v`.`taken_at` desc) AS `rn` from (`encounters` `e` join `vitals` `v` on(`v`.`encounter_id` = `e`.`id`)) where `e`.`person_id` is not null) AS `t` WHERE `t`.`rn` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `v_person_risk_summary`
--
DROP TABLE IF EXISTS `v_person_risk_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_person_risk_summary`  AS WITH latest_enc AS (SELECT `encounters`.`id` AS `id`, `encounters`.`person_id` AS `person_id`, `encounters`.`encounter_date` AS `encounter_date`, row_number() over ( partition by `encounters`.`person_id` order by `encounters`.`encounter_date` desc,`encounters`.`id` desc) AS `rn` FROM `encounters` WHERE `encounters`.`person_id` is not null) SELECT `pe`.`id` AS `person_id`, `pe`.`last_name` AS `last_name`, `pe`.`first_name` AS `first_name`, `pe`.`sex` AS `sex`, `pe`.`birthdate` AS `birthdate`, `e`.`encounter_date` AS `encounter_date`, `vv`.`height_cm` AS `height_cm`, `vv`.`weight_kg` AS `weight_kg`, `vv`.`bmi` AS `bmi`, `vv`.`waist_circumference_cm` AS `waist_circumference_cm`, `vv`.`bp_systolic` AS `bp_systolic`, `vv`.`bp_diastolic` AS `bp_diastolic`, `vv`.`pulse` AS `pulse`, `vv`.`temperature_c` AS `temperature_c`, `lr`.`smoking_status` AS `smoking_status`, `lr`.`alcohol_use` AS `alcohol_use`, `lr`.`excessive_alcohol` AS `excessive_alcohol`, `lr`.`eats_processed_weekly` AS `eats_processed_weekly`, `lr`.`fruits_3_servings_daily` AS `fruits_3_servings_daily`, `lr`.`vegetables_3_servings_daily` AS `vegetables_3_servings_daily`, `cn`.`obesity` AS `obesity`, `cn`.`central_adiposity` AS `central_adiposity`, `cn`.`raised_bp` AS `raised_bp`, `cn`.`raised_blood_sugar` AS `raised_blood_sugar` FROM ((((`latest_enc` `e` join `persons` `pe` on(`pe`.`id` = `e`.`person_id`)) left join `vitals` `vv` on(`vv`.`encounter_id` = `e`.`id`)) left join `lifestyle_risk` `lr` on(`lr`.`encounter_id` = `e`.`id`)) left join `cvd_ncd_risk_assessments` `cn` on(`cn`.`encounter_id` = `e`.`id`)) WHERE `e`.`rn` = 1111  ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `fk_att_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_att_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_att_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_cvd_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `deaths`
--
ALTER TABLE `deaths`
  ADD CONSTRAINT `fk_deaths_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `diabetes_screening`
--
ALTER TABLE `diabetes_screening`
  ADD CONSTRAINT `fk_diabetes_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `document_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_requests_ibfk_3` FOREIGN KEY (`released_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_requests_ibfk_4` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`document_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `encounters`
--
ALTER TABLE `encounters`
  ADD CONSTRAINT `fk_encounters_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_encounters_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `families`
--
ALTER TABLE `families`
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
-- Constraints for table `lab_results`
--
ALTER TABLE `lab_results`
  ADD CONSTRAINT `fk_labs_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lifestyle_risk`
--
ALTER TABLE `lifestyle_risk`
  ADD CONSTRAINT `fk_lifestyle_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `pregnancies`
--
ALTER TABLE `pregnancies`
  ADD CONSTRAINT `fk_pregnancies_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `screening`
--
ALTER TABLE `screening`
  ADD CONSTRAINT `fk_angina_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service_progress_notes`
--
ALTER TABLE `service_progress_notes`
  ADD CONSTRAINT `fk_spn_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_spn_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;

--
-- Constraints for table `vitals`
--
ALTER TABLE `vitals`
  ADD CONSTRAINT `fk_vitals_encounter` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
