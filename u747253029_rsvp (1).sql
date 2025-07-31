-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 30, 2025 at 05:44 PM
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
-- Database: `u747253029_rsvp`
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
  `qr_brand_text_ar` varchar(255) DEFAULT 'دعواتي',
  `qr_brand_text_en` varchar(255) DEFAULT 'Daawati',
  `qr_website` varchar(255) DEFAULT 'daawati.sa',
  `n8n_confirm_webhook` varchar(1024) DEFAULT NULL,
  `n8n_initial_invite_webhook` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `bride_name_ar`, `bride_name_en`, `groom_name_ar`, `groom_name_en`, `event_date_ar`, `event_date_en`, `venue_ar`, `venue_en`, `Maps_link`, `event_paragraph_ar`, `event_paragraph_en`, `background_image_url`, `qr_card_title_ar`, `qr_card_title_en`, `qr_show_code_instruction_ar`, `qr_show_code_instruction_en`, `qr_brand_text_ar`, `qr_brand_text_en`, `qr_website`, `n8n_confirm_webhook`, `n8n_initial_invite_webhook`, `created_at`) VALUES
(1, 'حفل زفاف ايمن و دانيا', '', '', '', '', 'المواف 12-10-2024 الساعه 9:00 مسائا', '', 'فندق الحجاوي', '', 'https://maps.app.goo.gl/Fy6depwfvbFqEDY7A', 'مرحبااا', '', './uploads/afca92dd7fa002a8fe083072decd7792.jpg', 'بطاقة دخول شخصية', 'Personal Entry Card', 'يرجى إبراز الكود للدخول', 'Please show code to enter', 'دعواتي', 'Daawati', 'daawati.sa', 'https://n8n.clouditech-me.com/webhook/confirm-rsvp-qr', 'https://n8n.clouditech-me.com/webhook/send-invitations', '2025-07-27 14:11:18'),
(4, 'حفلاتي', 'يرجى التحديث من لوحة التحكم', '', 'يرجى التحديث من لوحة التحكم', '', 'المواف 12-10-2024 الساعه 9:00 مسائا', '', 'فندق الريتز كارلتون - عمان', '', 'https://maps.app.goo.gl/Fy6depwfvbFqEDY7A', 'تتشرف عائلة ال حجاوي وعائلة ال شكارة لحضور زفاف ابنيهما امل ومحم\r\nوذلك في تمام الساعه 12:00 يوم الاثلاثاء', 'تتشرف عائلة ال حجاوي وعائلة ال شكارة لحضور زفاف ابنيهما امل ومحم\r\nوذلك في تمام الساعه 12:00 يوم الاثلاثاء', './uploads/543219c1854ce7f8a194f0f615d94ffe.jpeg', 'بطاقة دخول شخصية', 'Wedding Invitation', 'يرجى إبراز الكود للدخول', 'Please show code to enter', 'دعواتي', 'Daawati', 'www.daawati.ai', 'https://n8n.clouditech-me.com/webhook/confirm-rsvp-qr', 'https://n8n.clouditech-me.com/webhook/send-invitations', '2025-07-28 23:22:19'),
(6, 'Sam & Sarab Wedding', 'يرجى التحديث من لوحة التحكم', NULL, 'يرجى التحديث من لوحة التحكم', NULL, '28 / 8 / 2025 Thursday', '', 'Papillon Venue', '', 'https://maps.app.goo.gl/TdB5v7vMJyL5tKwS9?g_st=ipc', 'تتشرف عائلة محمد الحجاوي وعائلة شكارى\r\nلحضور زفاف ابنيهما\r\nأمل\r\n&\r\nمحمد', '', './uploads/event_6_1753838364.jpg', 'بطاقة دخول شخصية', 'Personal Entry Card', 'يرجى إبراز الكود للدخول', 'Please show code to enter', 'دعواتي', 'Daawati', 'd3waty.com', 'https://n8n.clouditech-me.com/webhook/confirm-rsvp-qr', 'https://n8n.clouditech-me.com/webhook/send-invitations', '2025-07-29 22:52:37');

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

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `event_id`, `guest_id`, `name_ar`, `name_en`, `phone_number`, `guests_count`, `table_number`, `assigned_location`, `status`, `invitation_sent`, `checkin_status`, `checkin_time`, `created_at`, `last_invite_sent`, `invite_count`, `last_invite_status`) VALUES
(312, 6, '99ce', 'سام١', NULL, '962798797794', 1, '1', NULL, 'pending', 0, 'not_checked_in', NULL, '2025-07-30 16:07:25', '2025-07-30 17:15:00', 2, 'sent'),
(314, 4, '5a1a', 'حجاوي ١', NULL, '962798797794', 1, '0', NULL, 'pending', 0, 'not_checked_in', NULL, '2025-07-30 16:08:21', '2025-07-30 16:09:21', 1, 'sent'),
(315, 1, '5629', 'ايمن ١', NULL, '962798797794', 1, '1', NULL, 'pending', 0, 'not_checked_in', NULL, '2025-07-30 16:08:49', '2025-07-30 17:28:38', 6, 'sent'),
(316, 1, '8203', 'ايمن٢', NULL, '962798797794', 1, '1', NULL, 'pending', 0, 'not_checked_in', NULL, '2025-07-30 16:09:05', NULL, 0, 'pending'),
(317, 6, '8ff8', 'سام2', NULL, '962798797794', 1, '2', NULL, 'pending', 0, 'not_checked_in', NULL, '2025-07-30 17:20:53', NULL, 0, 'pending'),
(318, 6, '1945', 'حجاوي4', NULL, '962798797794', 1, '3', NULL, '', 0, 'not_checked_in', NULL, '2025-07-30 17:21:05', '2025-07-30 17:33:25', 1, 'sent');

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

--
-- Dumping data for table `send_results`
--

INSERT INTO `send_results` (`id`, `event_id`, `action_type`, `success_count`, `failed_count`, `total_processed`, `target_count`, `response_data`, `http_code`, `created_at`) VALUES
(1, 6, 'send_selected', 0, 0, 0, 1, 'null', 404, '2025-07-30 14:47:34'),
(2, 6, 'send_selected', 0, 0, 0, 1, 'null', 200, '2025-07-30 14:47:51'),
(3, 6, 'send_selected', 0, 0, 0, 1, 'null', 200, '2025-07-30 14:49:04'),
(4, 6, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T14:53:56.922Z\"}}', 200, '2025-07-30 14:53:56'),
(5, 4, 'send_selected', 0, 0, 0, 1, 'null', 200, '2025-07-30 15:03:48'),
(6, 4, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:04:33.583Z\"}}', 200, '2025-07-30 15:04:33'),
(7, 6, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:05:08.029Z\"}}', 200, '2025-07-30 15:05:08'),
(8, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:05:17'),
(9, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:06:26'),
(10, NULL, 'send_global_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:06:46'),
(11, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:12:54'),
(12, 6, 'send_selected', 0, 0, 0, 1, 'null', 200, '2025-07-30 15:17:44'),
(13, 6, 'send_selected', 0, 0, 0, 1, 'null', 200, '2025-07-30 15:19:06'),
(14, 6, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:29:06.345Z\"}}', 200, '2025-07-30 15:29:06'),
(15, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:29:16'),
(16, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:30:50'),
(17, NULL, 'send_global_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:32:39'),
(18, 6, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:35:55.361Z\"}}', 200, '2025-07-30 15:35:55'),
(19, 4, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:36:17.166Z\"}}', 200, '2025-07-30 15:36:17'),
(20, 4, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:36:27'),
(21, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:38:06'),
(22, NULL, 'send_global_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 15:38:25'),
(23, NULL, 'send_global_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:38:51.047Z\"}}', 200, '2025-07-30 15:38:51'),
(24, 4, 'send_event_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T15:39:38.491Z\"}}', 200, '2025-07-30 15:39:38'),
(25, 4, 'send_event_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T16:09:21.499Z\"}}', 200, '2025-07-30 16:09:21'),
(26, 6, 'send_event_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T16:10:06.152Z\"}}', 200, '2025-07-30 16:10:06'),
(27, 1, 'send_event_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T16:10:39.404Z\"}}', 200, '2025-07-30 16:10:39'),
(28, NULL, 'send_global_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T16:11:14.075Z\"}}', 200, '2025-07-30 16:11:14'),
(29, 6, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T17:13:30.323Z\"}}', 200, '2025-07-30 17:13:30'),
(30, 6, 'send_event_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 17:13:48'),
(31, 6, 'send_event_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T17:15:00.379Z\"}}', 200, '2025-07-30 17:15:00'),
(32, 1, 'send_event_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T17:15:36.153Z\"}}', 200, '2025-07-30 17:15:36'),
(33, NULL, 'send_global_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 17:17:24'),
(34, NULL, 'send_global_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 17:18:47'),
(35, NULL, 'send_global_all', 0, 0, 0, NULL, 'null', 200, '2025-07-30 17:19:34'),
(36, NULL, 'send_global_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T17:24:16.904Z\"}}', 200, '2025-07-30 17:24:16'),
(37, NULL, 'send_global_all', 0, 1, 1, NULL, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T17:28:38.913Z\"}}', 200, '2025-07-30 17:28:39'),
(38, 6, 'send_selected', 0, 1, 1, 1, '{\"success\":true,\"message\":\"\\u062a\\u0645 \\u0625\\u0631\\u0633\\u0627\\u0644 0 \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0628\\u0646\\u062c\\u0627\\u062d \\u0645\\u0646 \\u0623\\u0635\\u0644 1 (0%)\",\"summary\":{\"totalProcessed\":1,\"successCount\":0,\"failureCount\":1,\"successRate\":0,\"eventsAffected\":1,\"eventIds\":[null]},\"details\":{\"successfulSends\":[],\"failedSends\":[[]],\"processedAt\":\"2025-07-30T17:33:25.647Z\"}}', 200, '2025-07-30 17:33:25');

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
(3, 'user2', '$2y$10$BUGM0h.ehX2a3aJpDe800OJVW9XiVP8rqROK5vYn07IJAjnE7lsyu', 'viewer', 1, '2025-07-27 18:06:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure for view `event_send_stats`
--
DROP TABLE IF EXISTS `event_send_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u747253029_hijjawi`@`127.0.0.1` SQL SECURITY DEFINER VIEW `event_send_stats`  AS SELECT `e`.`id` AS `event_id`, `e`.`event_name` AS `event_name`, count(`g`.`id`) AS `total_guests`, sum(case when `g`.`status` = 'confirmed' then 1 else 0 end) AS `confirmed_guests`, sum(case when `g`.`status` = 'pending' then 1 else 0 end) AS `pending_guests`, sum(case when `g`.`last_invite_status` = 'sent' then 1 else 0 end) AS `invited_guests`, max(`g`.`last_invite_sent`) AS `last_invitation_time`, coalesce(`sr`.`last_send_success`,0) AS `last_send_success`, coalesce(`sr`.`last_send_failed`,0) AS `last_send_failed` FROM ((`events` `e` left join `guests` `g` on(`e`.`id` = `g`.`event_id`)) left join (select `send_results`.`event_id` AS `event_id`,max(`send_results`.`success_count`) AS `last_send_success`,max(`send_results`.`failed_count`) AS `last_send_failed` from `send_results` where `send_results`.`created_at` >= current_timestamp() - interval 1 day group by `send_results`.`event_id`) `sr` on(`e`.`id` = `sr`.`event_id`)) GROUP BY `e`.`id`, `e`.`event_name` ;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=319;

--
-- AUTO_INCREMENT for table `message_logs`
--
ALTER TABLE `message_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `send_results`
--
ALTER TABLE `send_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
