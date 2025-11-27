-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 03:15 AM
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
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Foreign key to users table for registered complainants',
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
(10, 'Johnnjn', 'Complaint', 2, 'HAHAHAHAAHA', 'Resident', '0485945949854', 'male', '2025-11-19', 'hahahahaha', 'resident', 'male', 'HAHAHAHAHAHAA', 'hahahahaha', 'HAHAHAHAHA', '2025-11-19', '00:44:00', 'hahahahaha', 'HAHAHAHA', 2, '2025-11-20 15:43:47', '2025-11-26 13:03:44', NULL),
(11, 'gfhjfgdhsfghfghfhf', 'Complaint', 1, 'asadasdsadasdasda', 'Resident', '67867867967969', 'male', '2025-11-19', 'sdjkgiergfuigesbdcgifg', 'resident', 'male', 'asdsadasas', 'asdadasdasdada', 'asdadasddsad', '2025-11-20', '09:58:00', 'asdasdwerwergeggsdfsdg', 'asdasdasdasdsadsdasdasdsasafa', 3, '2025-11-21 01:58:38', '2025-11-21 01:58:51', '2025-11-21 09:58:51'),
(12, 'RALLYs', 'Complaint', 2, 'bato', 'Resident', '898957485445', 'male', '2025-11-05', 'Graba', 'resident', 'male', 'Marcos', 'HAHAHAHAHAHA', 'hahahahah', '2025-11-07', '10:50:00', 'HAHAHAHAHA DIKO ALAM', 'hahahahahahahaha ean', 3, '2025-11-21 14:49:54', '2025-11-26 11:05:30', '2025-11-26 19:05:30');

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

--
-- Indexes for dumped tables
--

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
  ADD KEY `idx_incident_location` (`location`(100)),
  ADD KEY `idx_incident_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidents_ibfk_2` FOREIGN KEY (`case_type_id`) REFERENCES `case_types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `incidents_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
