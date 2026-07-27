-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2026 at 09:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Disable foreign key checks while creating tables to avoid order issues
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- Re-enable foreign key checks after all tables and constraints are created
SET FOREIGN_KEY_CHECKS = 1;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mailregister`
--

-- --------------------------------------------------------

--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

(4, 'IT ریاست', 'د لوړو زده کړو وزارت', '2026-07-26 03:00:13'),
(5, 'داسنادو دویش او سوابقو د تنظیم ریاست', 'د دیني جامعاتو اوو تخصصاتو عمومی ریاست', '2026-07-26 04:16:46'),
(6, 'د معلوماتی ټکنالوژی آمریت', 'د دیني جامعاتو اوو تخصصاتو عمومی ریاست', '2026-07-26 04:17:15'),
(7, 'د اسنادو ارزونی او معادلت ریاست', 'د دیني جامعاتو اوو تخصصاتو عمومی ریاست', '2026-07-26 04:18:26'),
(8, 'د دینی جامعاتو د ثبت او ایحاد ریاست', 'د دیني جامعاتو اوو تخصصاتو عمومی ریاست', '2026-07-26 04:19:01'),
(9, 'د نشراتو او عامه اړیکو ریاست', 'د لوړو زده کړو وزارت', '2026-07-26 04:28:44'),

(17, 'عمومی اجرایه', 'د دیني جامعاتو اوو تخصصاتو عمومی ریاست', '2026-07-26 04:41:09'),
(18, 'دمصوبو، حکمونو او فرمانونو عمومی مدیریت', 'د لوړو زده کړو وزارت', '2026-07-26 04:44:33');

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('admin','editor','viewer') NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `is_active`, `last_login_at`, `created_at`) VALUES
(1, 'admin', '$2y$10$VuasIjylRsepeP4vAjfn8eElYe7LjWCq5l5qZ8/QC.Eaj/q20Zoua', 'سیسټم مدیر', 'admin', 1, '2026-07-26 11:49:26', '2026-07-25 13:14:53'),
(2, 'user', '$2y$10$rNQfhqgQNwCy05g1noOQlObOiI.qkAStH/oVyFAujrpzIcLfj0aKC', 'Danish', 'editor', 1, '2026-07-26 12:10:39', '2026-07-26 11:50:31'),
(3, 'Viewer', '$2y$10$pHgVG1q4NMp5fk2fRBdlAuEDdy8fji5d41gfqMXaYv.qIMbZEk3b.', 'Wiewer', 'viewer', 1, NULL, '2026-07-26 12:09:51');

-- Indexes for table `users`
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

-- AUTO_INCREMENT for table `users`
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------

--
-- Table structure for table `incoming_letters`
--

CREATE TABLE `incoming_letters` (
  `id` int(10) UNSIGNED NOT NULL,
  `serial_no` varchar(50) NOT NULL,
  `incoming_date` varchar(20) DEFAULT NULL,
  `letter_date` varchar(20) DEFAULT NULL,
  `incoming_no` varchar(100) DEFAULT NULL,
  `sent_to_dep_id` int(11) DEFAULT NULL,
  `origin_dep_id` int(11) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `doc_count` int(11) DEFAULT NULL,
  `pages_no` varchar(50) DEFAULT NULL,
  `action_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dossier_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incoming_letters`
--

INSERT INTO `incoming_letters` (`id`, `serial_no`, `incoming_date`, `letter_date`, `incoming_no`, `sent_to_dep_id`, `origin_dep_id`, `subject`, `doc_count`, `pages_no`, `action_no`, `remarks`, `created_by`, `created_at`, `updated_at`, `dossier_no`) VALUES
(1, '1', '1448/01/01', '1448/01/01', '382', 3, 8, 'د سالانه ازمویني د فاضل ثبت فورمو په اړه', 0, '1', '14', '', 1, '2026-07-25 14:14:03', '2026-07-26 09:01:24', '14'),
(2, '2', '1448/01/01', '1448/01/01', '150', 3, 5, 'د اسنادو ویش ریاست ته د چاپ په اړه د سیستم سپارلو په اړه', 0, '1', '4', '', 1, '2026-07-25 14:20:35', '2026-07-26 09:02:34', '14'),
(3, '6', '1448/01/05', '1448/01/05', '5', 3, 7, 'د تخصص کتابونو د سیستم له لاری لیږلو لپاره د یوزر جوړولو په اړه!', 0, '2', '', 'جوړ شوی', 1, '2026-07-25 15:58:35', '2026-07-26 09:03:19', '2'),
(4, '7', '1448/01/04', '1448/01/04', '2', 3, 11, 'د کارکوونکو د هویت کارتونو د نوي کولو په هکله!', 0, '2', '2-2', '', 1, '2026-07-25 16:01:49', '2026-07-26 09:04:06', '2'),
(5, '9', '1448/01/25', '1448/01/15', '249', 3, 10, 'د دیني جامعاتو او تخصصاتو ریاست او معینیت تعلیمات اسلامی تر مینځ ګډه ناسته او پریکړي', 0, '0', '2', '', 1, '2026-07-25 16:09:09', '2026-07-26 09:04:31', '2'),
(6, '10', '1448/01/25', '1448/01/20', '361', 3, 9, 'د وزارت او ریاستونو د شعبو د لوحو د نوی کولو په هکله!', 0, '1', '2', 'نوي شوي', 1, '2026-07-25 16:12:07', '2026-07-26 09:04:58', '2'),
(7, '11', '1448/01/26', '1448/01/20', '540', 12, 6, 'د سرطان میاشتی تر پایه د الکترونیکی تذکرو ثبت کول د معاشاتو مدیریت کې', NULL, '', '2', '', 1, '2026-07-26 09:20:08', '2026-07-26 09:20:08', '2'),
(8, '12', '1448/01/26', '1448/01/21', '590', 6, 13, 'د 1406 ه ش کال د قرطاسیه باب او تنظیفاتی موادو د نیازبندۍ په اړه!', NULL, '', '2', '', 1, '2026-07-26 09:22:56', '2026-07-26 09:22:56', '2'),
(9, '13', '1448/01/26', '1448/01/22', '129', 6, 14, 'د کارکوونکو د دیني موضوعاتو د تقرری ازموینې په اړه!', NULL, '', '2', '', 1, '2026-07-26 09:24:59', '2026-07-26 09:24:59', '2'),
(10, '15', '1448/01/27', '1448/01/21', '99', 6, 15, 'د جامعاتو د ریس صاحبانو سره د ناستې پریکړو د تشکیلونو په هکله!', NULL, '7', '', '', 1, '2026-07-26 09:27:44', '2026-07-26 09:27:44', '14'),
(11, '16', '1448/01/28', '1448/01/26', '6', 6, 16, 'د عزت الله ولد نورالله د تخصص زده کړو د اطمینان په اړه!', NULL, '', '10', 'د ایمیل له لاري يي رالیږلی دی', 1, '2026-07-26 09:30:26', '2026-07-26 09:30:26', '10'),
(12, '17', '1448/02/06', '1448/02/01', '111', 6, 1, 'د دیني جامعاتو عمومی ریس او نورو ریس صاحبانو، امرانو او کارمندانو سره د وزیر شیخ صاحب د مجلس په اړه!', NULL, '1', '', '', 1, '2026-07-26 09:35:38', '2026-07-26 09:35:38', '14'),
(13, '18', '1448/02/06', '1448/02/03', '113', 6, 1, 'د دیني جامعاتو او تخصصاتو عمومی ریاست د اداری مجلس کې د ترسره شویو پریکړو د تکثیر په اړه!', NULL, '5', '', '', 1, '2026-07-26 09:37:39', '2026-07-26 09:37:39', '14'),
(14, '19', '1448/02/06', '1448/02/03', '114', 6, 1, 'د دیني جامعاتو او تخصصاتو عمومی ریاست د اداری مجلس کې د ترسره شویو پریکړو د تکثیر په اړه!', NULL, '4', '', '', 1, '2026-07-26 09:38:18', '2026-07-26 09:38:18', '14'),
(15, '20', '1448/02/06', '1448/01/29', '353', 6, 18, 'د 1448-01-28 ه ق نیټې (1) ګڼې رهبرې شورا د پرېکړو ابلاغ په اړه!', NULL, '13', '', '', 1, '2026-07-26 09:41:08', '2026-07-26 09:41:08', '9');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `username`, `ip_address`, `success`, `created_at`) VALUES
(1, 1, 'admin', '::1', 1, '2026-07-25 08:45:03'),
(2, 1, 'admin', '::1', 0, '2026-07-25 12:13:17'),
(3, 1, 'admin', '::1', 1, '2026-07-25 12:13:33'),
(4, 1, 'admin', '::1', 0, '2026-07-25 12:18:21'),
(5, 1, 'admin', '::1', 0, '2026-07-25 12:18:28'),
(6, 1, 'admin', '::1', 1, '2026-07-25 12:18:36'),
(7, 1, 'admin', '::1', 0, '2026-07-26 02:22:24'),
(8, 1, 'admin', '::1', 1, '2026-07-26 02:22:32'),
(9, 1, 'admin', '::1', 0, '2026-07-26 07:19:18'),
(10, 1, 'admin', '::1', 1, '2026-07-26 07:19:26'),
(11, 2, 'user', '::1', 1, '2026-07-26 07:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `outgoing_letters`
--

CREATE TABLE `outgoing_letters` (
  `id` int(10) UNSIGNED NOT NULL,
  `serial_no` varchar(50) NOT NULL,
  `receipts_no` varchar(100) DEFAULT NULL,
  `dossier_no` varchar(100) DEFAULT NULL,
  `issue_date` varchar(20) DEFAULT NULL,
  `letter_date` varchar(20) DEFAULT NULL,
  `sent_to_dep_id` int(11) DEFAULT NULL,
  `reference_dep_id` int(11) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `records_signature` tinyint(1) NOT NULL DEFAULT 0,
  `records_attachment` tinyint(1) NOT NULL DEFAULT 0,
  `records_attachment_count` int(11) DEFAULT NULL,
  `records_attachment_pages` int(11) DEFAULT NULL,
  `records_original` tinyint(1) NOT NULL DEFAULT 0,
  `exec_signature` tinyint(1) NOT NULL DEFAULT 0,
  `exec_attachment` tinyint(1) NOT NULL DEFAULT 0,
  `exec_attachment_count` int(11) DEFAULT NULL,
  `exec_attachment_pages` int(11) DEFAULT NULL,
  `exec_original` tinyint(1) NOT NULL DEFAULT 0,
  `distribution_notes` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `outgoing_letters`
--

INSERT INTO `outgoing_letters` (`id`, `serial_no`, `receipts_no`, `dossier_no`, `issue_date`, `letter_date`, `sent_to_dep_id`, `reference_dep_id`, `subject`, `records_signature`, `records_attachment`, `records_attachment_count`, `records_attachment_pages`, `records_original`, `exec_signature`, `exec_attachment`, `exec_attachment_count`, `exec_attachment_pages`, `exec_original`, `distribution_notes`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '3', '', '1', '1448/01/05', '1448/01/05', 1, 3, 'د دیټابیس آمریت ته د یو باب اطاق ورکولو په اړه!', 0, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 0, 'تر اوسه اطاق نه دی تسلیم شوی', '', 1, '2026-07-25 14:37:22', '2026-07-26 08:49:54'),
(2, '4', '2', '1', '1448/01/05', '1448/01/05', 5, 3, 'د اسنادو توزیع ریاست ته د ډیټابیس سیستم د اکاونټونو سپارلو په هکله', 1, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 0, 'اکاونټونه ورته تسلیم شول', '', 1, '2026-07-25 14:39:57', '2026-07-26 08:49:27'),
(3, '5', '3', '1', '1448/01/06', '1448/01/06', 1, 3, 'د صادرې او واردې سیستم په هکله ټولو داخلې ریاستونو ته!', 1, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 1, 'رسیدلی', '', 1, '2026-07-25 14:52:41', '2026-07-26 08:44:56'),
(4, '8', '7', '1‍', '1448/01/18', '1448/01/18', 4, 3, 'د امنیتی کمرو د انتقال و ترسیم په اړه!', 1, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 1, 'انتقال شوي', '', 1, '2026-07-25 16:04:28', '2026-07-26 08:42:44'),
(5, '14', '8', '1', '1448/01/27', '1448/01/27', 17, 6, 'د معلوماتی ټکنالوژۍ امریت د لوحی په اړه!', 1, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 1, 'مکتوب تسلیم شوی', '', 1, '2026-07-26 09:48:52', '2026-07-26 09:48:52'),
(6, '21', '10,11,12,13', '2', '1448/02/10', '1448/02/10', 1, 6, 'د صادرې او واردې سیستم د ټرینینګ په اړه!', 1, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 1, 'کاپي:\r\nد تحصیلي اسنادو دارزونې او برابرۍ محترم ریاست ته!\r\nد اسنادو د ویش او سوابقو د تنظیم محترم ریاست ته!\r\nد ثبت او ایجاد محترم ریاست ته!', '', 1, '2026-07-26 09:52:39', '2026-07-26 10:27:57'),
(7, '22', '', '1', '1448/02/11', '1448/02/11', 6, 1, 'د معلوماتی ټکنالوژی امریت کارکوونکو لپاره د اړینو توکو د ضرورت اړوند!', 1, 0, NULL, NULL, 1, 1, 0, NULL, NULL, 1, '', '', 1, '2026-07-26 10:33:05', '2026-07-26 10:33:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incoming_letters`
--
ALTER TABLE `incoming_letters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_in_serial` (`serial_no`),
  ADD KEY `fk_incoming_sent_department` (`sent_to_dep_id`),
  ADD KEY `fk_incoming_origin_department` (`origin_dep_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_created` (`ip_address`,`created_at`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `outgoing_letters`
--
ALTER TABLE `outgoing_letters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_out_serial` (`serial_no`),
  ADD KEY `fk_outgoing_sent_department` (`sent_to_dep_id`),
  ADD KEY `fk_outgoing_reference_department` (`reference_dep_id`);

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
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `incoming_letters`
--
ALTER TABLE `incoming_letters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `outgoing_letters`
--
ALTER TABLE `outgoing_letters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `incoming_letters`
--
ALTER TABLE `incoming_letters`
  ADD CONSTRAINT `fk_incoming_origin_department` FOREIGN KEY (`origin_dep_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incoming_sent_department` FOREIGN KEY (`sent_to_dep_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `incoming_letters_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `outgoing_letters`
--
ALTER TABLE `outgoing_letters`
  ADD CONSTRAINT `fk_outgoing_reference_department` FOREIGN KEY (`reference_dep_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_outgoing_sent_department` FOREIGN KEY (`sent_to_dep_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `outgoing_letters_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
