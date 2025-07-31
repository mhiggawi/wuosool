-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 31, 2025 at 06:19 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u747253029_wosuol`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `bride_name_ar` varchar(255) DEFAULT NULL,
  `bride_name_en` varchar(255) DEFAULT NULL,
  `groom_name_ar` varchar(255) DEFAULT NULL,
  `groom_name_en` varchar(255) DEFAULT NULL,
  `event_date_ar` text DEFAULT NULL,
  `event_date_en` text DEFAULT NULL,
  `venue_ar` varchar(255) DEFAULT NULL,
  `venue_en` varchar(255) DEFAULT NULL,
  `Maps_link` varchar(1024) DEFAULT NULL,
  `event_paragraph_ar` text DEFAULT NULL,
  `event_paragraph_en` text DEFAULT NULL,
  `background_image_url` varchar(1024) DEFAULT NULL,
  `qr_card_title_ar` varchar(255) DEFAULT 'بطاقة دخول شخصية',
  `qr_card_title_en` varchar(255) DEFAULT 'Personal Entry Card',
  `qr_show_code_instruction_ar` varchar(255) DEFAULT 'يرجى إبراز الكود للدخول',
  `qr_show_code_instruction_en` varchar(255) DEFAULT 'Please show code to enter',
  `qr_brand_text_ar` varchar(255) DEFAULT 'Wosuol.com',
  `qr_brand_text_en` varchar(255) DEFAULT 'Wosuol.com',
  `qr_website` varchar(255) DEFAULT 'Wosuol.com',
  `n8n_confirm_webhook` varchar(1024) DEFAULT NULL,
  `n8n_initial_invite_webhook` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `event_send_stats`
-- (See below for the actual view)
--
CREATE TABLE `event_send_stats` (
`event_id` int(11)
,`event_name` varchar(255)
,`total_guests` bigint(21)
,`confirmed_guests` decimal(22,0)
,`pending_guests` decimal(22,0)
,`invited_guests` decimal(22,0)
,`last_invitation_time` datetime
,`last_send_success` int(11)
,`last_send_failed` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `guest_id` varchar(10) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `guests_count` int(11) DEFAULT 1,
  `table_number` varchar(50) DEFAULT NULL,
  `assigned_location` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','canceled') NOT NULL DEFAULT 'pending',
  `invitation_sent` tinyint(1) NOT NULL DEFAULT 0,
  `checkin_status` enum('not_checked_in','checked_in') NOT NULL DEFAULT 'not_checked_in',
  `checkin_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_invite_sent` datetime DEFAULT NULL,
  `invite_count` int(11) DEFAULT 0,
  `last_invite_status` enum('sent','failed','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_logs`
--

CREATE TABLE `message_logs` (
  `id` int(11) NOT NULL,
  `workflow_id` varchar(255) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `total_processed` int(11) DEFAULT NULL,
  `success_count` int(11) DEFAULT NULL,
  `failure_count` int(11) DEFAULT NULL,
  `success_rate` decimal(5,2) DEFAULT NULL,
  `event_ids` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `send_results`
--

CREATE TABLE `send_results` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `success_count` int(11) DEFAULT 0,
  `failed_count` int(11) DEFAULT 0,
  `total_processed` int(11) DEFAULT 0,
  `target_count` int(11) DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `http_code` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','viewer','checkin_user') NOT NULL DEFAULT 'viewer',
  `event_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `permissions` varchar(255) DEFAULT NULL,
  `allowed_pages` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `event_id`, `created_at`, `permissions`, `allowed_pages`) VALUES
(1, 'hijjawi', '$2y$10$vzZddozxIY0zgAItU1DKHO6DARAEtWsslMjDgZuKYE4iWYZ5kAHWm', 'admin', NULL, '2025-07-27 14:11:41', NULL, NULL),
(2, 'user', '$2y$10$DC2PvdfvwMvQe0yT4TZhaeXar.UAB2fxlSNMavoWzGC6JeUCW7AAm', 'checkin_user', 1, '2025-07-27 18:05:50', NULL, NULL),
(3, 'user2', '$2y$10$BUGM0h.ehX2a3aJpDe800OJVW9XiVP8rqROK5vYn07IJAjnE7lsyu', 'viewer', 1, '2025-07-27 18:06:01', NULL, NULL),
(5, 'GG', '$2y$10$nJLii/1J85w73hynruCoCexDXe8b7EnlM4DTHsSz4DwgrMLB2UKWS', 'admin', NULL, '2025-07-30 18:26:57', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guest_id` (`guest_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `idx_event_status` (`event_id`,`status`),
  ADD KEY `idx_last_invite` (`last_invite_sent`);

--
-- Indexes for table `message_logs`
--
ALTER TABLE `message_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `send_results`
--
ALTER TABLE `send_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;

--
-- AUTO_INCREMENT for table `message_logs`
--
ALTER TABLE `message_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `send_results`
--
ALTER TABLE `send_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `event_send_stats`
--
DROP TABLE IF EXISTS `event_send_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u747253029_dbhijjawi`@`127.0.0.1` SQL SECURITY DEFINER VIEW `event_send_stats`  AS SELECT `e`.`id` AS `event_id`, `e`.`event_name` AS `event_name`, count(`g`.`id`) AS `total_guests`, sum(case when `g`.`status` = 'confirmed' then 1 else 0 end) AS `confirmed_guests`, sum(case when `g`.`status` = 'pending' then 1 else 0 end) AS `pending_guests`, sum(case when `g`.`last_invite_status` = 'sent' then 1 else 0 end) AS `invited_guests`, max(`g`.`last_invite_sent`) AS `last_invitation_time`, coalesce(`sr`.`last_send_success`,0) AS `last_send_success`, coalesce(`sr`.`last_send_failed`,0) AS `last_send_failed` FROM ((`events` `e` left join `guests` `g` on(`e`.`id` = `g`.`event_id`)) left join (select `send_results`.`event_id` AS `event_id`,max(`send_results`.`success_count`) AS `last_send_success`,max(`send_results`.`failed_count`) AS `last_send_failed` from `send_results` where `send_results`.`created_at` >= current_timestamp() - interval 1 day group by `send_results`.`event_id`) `sr` on(`e`.`id` = `sr`.`event_id`)) GROUP BY `e`.`id`, `e`.`event_name` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `guests_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `send_results`
--
ALTER TABLE `send_results`
  ADD CONSTRAINT `send_results_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
