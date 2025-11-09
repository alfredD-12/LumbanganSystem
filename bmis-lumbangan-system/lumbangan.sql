-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: lumbangansystem
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `angina_stroke_screening`
--

DROP TABLE IF EXISTS `angina_stroke_screening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `angina_stroke_screening` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint(20) unsigned NOT NULL,
  `q1_chest_discomfort` tinyint(1) DEFAULT NULL,
  `q2_pain_location_left_arm_neck_back` tinyint(1) DEFAULT NULL,
  `q3_pain_on_exertion` tinyint(1) DEFAULT NULL,
  `q4_pain_relieved_by_rest_or_nitro` tinyint(1) DEFAULT NULL,
  `q5_pain_lasting_10min_plus` tinyint(1) DEFAULT NULL,
  `q6_pain_front_of_chest_half_hour` tinyint(1) DEFAULT NULL,
  `screen_positive` tinyint(1) DEFAULT NULL,
  `needs_doctor_referral` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_angina_cvd` (`cvd_id`),
  CONSTRAINT `fk_angina_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `births`
--

DROP TABLE IF EXISTS `births`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `births` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mother_id` bigint(20) unsigned NOT NULL,
  `child_person_id` bigint(20) unsigned DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_place` varchar(255) DEFAULT NULL,
  `attendant` varchar(255) DEFAULT NULL,
  `outcome` varchar(64) DEFAULT NULL,
  `birth_weight_kg` decimal(4,2) DEFAULT NULL,
  `sex` enum('M','F') DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_births_mother` (`mother_id`),
  KEY `idx_births_child` (`child_person_id`),
  CONSTRAINT `fk_births_child` FOREIGN KEY (`child_person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_births_mother` FOREIGN KEY (`mother_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cvd_ncd_risk_assessments`
--

DROP TABLE IF EXISTS `cvd_ncd_risk_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cvd_ncd_risk_assessments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `surveyed_by_official_id` bigint(20) unsigned DEFAULT NULL,
  `survey_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by_official_id` bigint(20) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `review_notes` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cvd_person_date` (`person_id`,`survey_date`),
  KEY `idx_cvd_is_approved` (`is_approved`),
  KEY `fk_cvd_approved_by_official` (`approved_by_official_id`),
  KEY `fk_cvd_surveyed_by_official` (`surveyed_by_official_id`),
  CONSTRAINT `fk_cvd_approved_by_official` FOREIGN KEY (`approved_by_official_id`) REFERENCES `officials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cvd_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cvd_surveyed_by_official` FOREIGN KEY (`surveyed_by_official_id`) REFERENCES `officials` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deaths`
--

DROP TABLE IF EXISTS `deaths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deaths` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned DEFAULT NULL,
  `name_free` varchar(255) DEFAULT NULL,
  `age_years` int(11) DEFAULT NULL,
  `cause_of_death` varchar(255) DEFAULT NULL,
  `place_of_death` varchar(255) DEFAULT NULL,
  `death_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_deaths_person` (`person_id`),
  CONSTRAINT `fk_deaths_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `diabetes_screening`
--

DROP TABLE IF EXISTS `diabetes_screening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diabetes_screening` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint(20) unsigned NOT NULL,
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
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_diabetes_cvd` (`cvd_id`),
  CONSTRAINT `fk_diabetes_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `families`
--

DROP TABLE IF EXISTS `families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `families` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `household_id` bigint(20) unsigned NOT NULL,
  `family_number` varchar(64) DEFAULT NULL,
  `head_person_id` bigint(20) unsigned DEFAULT NULL,
  `residency_status` enum('Permanent','Temporary') DEFAULT NULL,
  `length_of_residency_months` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `survey_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_families_head_person` (`head_person_id`),
  KEY `idx_families_household` (`household_id`),
  KEY `idx_families_survey` (`survey_date`),
  CONSTRAINT `fk_families_head_person` FOREIGN KEY (`head_person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_families_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_families_bi
BEFORE INSERT ON families
FOR EACH ROW
BEGIN
  IF NEW.head_person_id IS NOT NULL THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Set head_person_id only after the family row exists (use UPDATE).';
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
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_families_bu
BEFORE UPDATE ON families
FOR EACH ROW
BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `families_address_archive`
--

DROP TABLE IF EXISTS `families_address_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `families_address_archive` (
  `family_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `household_id` bigint(20) unsigned NOT NULL,
  `complete_address` varchar(512) DEFAULT NULL,
  `household_address` varchar(512) DEFAULT NULL,
  `archived_at` datetime NOT NULL,
  KEY `idx_faa_household` (`household_id`),
  KEY `idx_faa_family` (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `health_family_history`
--

DROP TABLE IF EXISTS `health_family_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_family_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `hypertension` tinyint(1) DEFAULT NULL,
  `stroke` tinyint(1) DEFAULT NULL,
  `heart_attack` tinyint(1) DEFAULT NULL,
  `asthma` tinyint(1) DEFAULT NULL,
  `diabetes` tinyint(1) DEFAULT NULL,
  `cancer` tinyint(1) DEFAULT NULL,
  `kidney_disease` tinyint(1) DEFAULT NULL,
  `recorded_at` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hfh_person_date` (`person_id`,`recorded_at`),
  CONSTRAINT `fk_hfh_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `households`
--

DROP TABLE IF EXISTS `households`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `households` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purok_id` bigint(20) unsigned DEFAULT NULL,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_households_purok` (`purok_id`),
  CONSTRAINT `fk_households_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lifestyle_risk`
--

DROP TABLE IF EXISTS `lifestyle_risk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lifestyle_risk` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cvd_id` bigint(20) unsigned NOT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lifestyle_cvd` (`cvd_id`),
  CONSTRAINT `fk_lifestyle_cvd` FOREIGN KEY (`cvd_id`) REFERENCES `cvd_ncd_risk_assessments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `from_purok_id` bigint(20) unsigned DEFAULT NULL,
  `to_purok_id` bigint(20) unsigned DEFAULT NULL,
  `moved_at` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_migrations_person` (`person_id`),
  KEY `idx_migrations_from` (`from_purok_id`),
  KEY `idx_migrations_to` (`to_purok_id`),
  CONSTRAINT `fk_mig_from` FOREIGN KEY (`from_purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_mig_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mig_to` FOREIGN KEY (`to_purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morbidity_logs`
--

DROP TABLE IF EXISTS `morbidity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morbidity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_date` date NOT NULL,
  `person_id` bigint(20) unsigned DEFAULT NULL,
  `household_id` bigint(20) unsigned DEFAULT NULL,
  `purok_id` bigint(20) unsigned DEFAULT NULL,
  `name_free` varchar(255) DEFAULT NULL,
  `age_years` int(11) DEFAULT NULL,
  `diagnosis` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_morbidity_person` (`person_id`),
  KEY `idx_morbidity_household` (`household_id`),
  KEY `idx_morbidity_purok` (`purok_id`),
  CONSTRAINT `fk_morbidity_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_morbidity_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_morbidity_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `officials`
--

DROP TABLE IF EXISTS `officials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_officials_username` (`username`),
  KEY `idx_officials_role_active` (`role`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_relationships`
--

DROP TABLE IF EXISTS `person_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person_relationships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `related_person_id` bigint(20) unsigned NOT NULL,
  `relationship_type` enum('parent','child','spouse','sibling','guardian','ward','grandparent','grandchild','step_parent','step_child','adoptive_parent','adopted_child','other') NOT NULL,
  `family_id` bigint(20) unsigned DEFAULT NULL,
  `is_inverse` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pr_direct` (`person_id`,`related_person_id`,`relationship_type`),
  KEY `idx_pr_person` (`person_id`,`relationship_type`),
  KEY `idx_pr_related` (`related_person_id`,`relationship_type`),
  KEY `idx_pr_family` (`family_id`),
  CONSTRAINT `fk_pr_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pr_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pr_related` FOREIGN KEY (`related_person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_pr_after_insert
AFTER INSERT ON person_relationships
FOR EACH ROW
BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_persons_family` (`family_id`),
  KEY `idx_persons_name` (`last_name`,`first_name`),
  CONSTRAINT `fk_persons_family` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_persons_au_head_cleanup
AFTER UPDATE ON persons
FOR EACH ROW
BEGIN
  IF OLD.id = NEW.id AND OLD.family_id <> NEW.family_id THEN
    UPDATE families f
      SET f.head_person_id = NULL
      WHERE f.head_person_id = NEW.id
        AND f.id <> NEW.family_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pregnancies`
--

DROP TABLE IF EXISTS `pregnancies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pregnancies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `lmp_date` date DEFAULT NULL,
  `edc_date` date DEFAULT NULL,
  `gravidity` int(11) DEFAULT NULL,
  `parity` int(11) DEFAULT NULL,
  `aog_weeks` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pregnancies_person` (`person_id`),
  CONSTRAINT `fk_pregnancies_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `puroks`
--

DROP TABLE IF EXISTS `puroks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `puroks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `centroid_lat` decimal(9,6) DEFAULT NULL,
  `centroid_lng` decimal(9,6) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_purok_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_profile`
--

DROP TABLE IF EXISTS `site_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_profile` (
  `id` tinyint(3) unsigned NOT NULL,
  `barangay_name` varchar(255) NOT NULL DEFAULT 'Lumbangan',
  `municipality_name` varchar(255) NOT NULL DEFAULT 'Nasugbu',
  `province_name` varchar(255) NOT NULL DEFAULT 'Batangas',
  `region_name` varchar(255) NOT NULL DEFAULT 'Region IV-A (CALABARZON)',
  `psa_code` varchar(64) DEFAULT NULL,
  `address_line` varchar(512) DEFAULT NULL,
  `hotline` varchar(64) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_site_profile_singleton
BEFORE INSERT ON site_profile FOR EACH ROW
BEGIN
  SET NEW.id = 1;
  IF (SELECT COUNT(*) FROM site_profile) > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'site_profile is singleton (only one row allowed)';
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(13) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_person` (`person_id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_mobile` (`mobile`),
  CONSTRAINT `fk_users_person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `v_household_profile`
--

DROP TABLE IF EXISTS `v_household_profile`;
/*!50001 DROP VIEW IF EXISTS `v_household_profile`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_household_profile` AS SELECT
 1 AS `household_id`,
  1 AS `barangay`,
  1 AS `purok`,
  1 AS `household_no`,
  1 AS `address`,
  1 AS `home_ownership`,
  1 AS `construction_material`,
  1 AS `lighting_facility`,
  1 AS `water_level`,
  1 AS `water_source`,
  1 AS `water_storage`,
  1 AS `garbage_container`,
  1 AS `garbage_segregated`,
  1 AS `garbage_disposal_method`,
  1 AS `toilet_type`,
  1 AS `latest_family_survey`,
  1 AS `families`,
  1 AS `persons` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_latest_vitals`
--

DROP TABLE IF EXISTS `v_latest_vitals`;
/*!50001 DROP VIEW IF EXISTS `v_latest_vitals`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_latest_vitals` AS SELECT
 1 AS `person_id`,
  1 AS `survey_date`,
  1 AS `height_cm`,
  1 AS `weight_kg`,
  1 AS `bmi`,
  1 AS `waist_circumference_cm`,
  1 AS `bp_systolic`,
  1 AS `bp_diastolic`,
  1 AS `pulse`,
  1 AS `temperature_c` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_person_health_history`
--

DROP TABLE IF EXISTS `v_person_health_history`;
/*!50001 DROP VIEW IF EXISTS `v_person_health_history`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_person_health_history` AS SELECT
 1 AS `survey_id`,
  1 AS `person_id`,
  1 AS `survey_date`,
  1 AS `height_cm`,
  1 AS `weight_kg`,
  1 AS `bmi`,
  1 AS `obesity_flag`,
  1 AS `waist_circumference_cm`,
  1 AS `central_adiposity`,
  1 AS `bp_systolic`,
  1 AS `bp_diastolic`,
  1 AS `raised_bp`,
  1 AS `pulse`,
  1 AS `temperature_c`,
  1 AS `raised_blood_sugar`,
  1 AS `dyslipidemia`,
  1 AS `smoking_status`,
  1 AS `alcohol_use`,
  1 AS `excessive_alcohol`,
  1 AS `eats_processed_weekly`,
  1 AS `fruits_3_servings_daily`,
  1 AS `vegetables_3_servings_daily`,
  1 AS `exercise_days_per_week`,
  1 AS `exercise_minutes_per_day`,
  1 AS `exercise_intensity`,
  1 AS `known_diabetes`,
  1 AS `on_medications`,
  1 AS `family_history`,
  1 AS `polyuria`,
  1 AS `polydipsia`,
  1 AS `polyphagia`,
  1 AS `weight_loss`,
  1 AS `rbs_mg_dl`,
  1 AS `fbs_mg_dl`,
  1 AS `hba1c_percent`,
  1 AS `urine_ketone`,
  1 AS `urine_protein`,
  1 AS `screen_positive`,
  1 AS `is_approved`,
  1 AS `approved_by_official_id`,
  1 AS `approved_at`,
  1 AS `notes`,
  1 AS `review_notes` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_person_risk_summary`
--

DROP TABLE IF EXISTS `v_person_risk_summary`;
