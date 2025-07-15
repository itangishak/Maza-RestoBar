-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2025 at 01:53 PM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mazarestobar`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `activity_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `activity`, `activity_time`) VALUES
(1, 8, 'User Boss logged in successfully.', '2024-12-12 10:16:54'),
(2, 8, 'User Boss logged in successfully.', '2024-12-12 10:17:54'),
(3, 8, 'User Boss logged in successfully.', '2024-12-12 10:21:42'),
(4, 8, 'User Boss logged in successfully.', '2024-12-12 11:17:31'),
(5, 8, 'User Boss logged in successfully.', '2024-12-12 11:18:36'),
(6, 8, 'User Boss logged in successfully.', '2024-12-12 11:26:12'),
(7, 8, 'User Boss logged in successfully.', '2024-12-12 12:11:01'),
(8, 8, 'User Boss logged in successfully.', '2024-12-12 14:30:23'),
(9, 8, 'User Boss logged in successfully.', '2024-12-12 15:02:31'),
(10, 8, 'User Boss logged in successfully.', '2024-12-12 15:39:40'),
(11, 8, 'User Boss logged in successfully.', '2024-12-15 08:36:29'),
(12, 8, 'User Boss logged in successfully.', '2024-12-15 09:01:40'),
(13, 8, 'User Boss logged in successfully.', '2024-12-15 11:01:28'),
(14, 8, 'User Boss logged in successfully.', '2024-12-16 14:25:20'),
(15, 8, 'User Boss logged in successfully.', '2024-12-16 15:10:33'),
(16, 8, 'User Boss logged in successfully.', '2024-12-16 15:46:38'),
(17, 8, 'User Boss logged in successfully.', '2024-12-17 07:58:03'),
(18, 8, 'User Boss logged in successfully.', '2024-12-17 08:42:33'),
(19, 8, 'User Boss logged in successfully.', '2024-12-17 08:55:14'),
(20, 8, 'User Boss logged in successfully.', '2024-12-17 09:09:48'),
(21, 8, 'User Boss logged in successfully.', '2024-12-17 10:25:25'),
(22, 8, 'User Boss logged in successfully.', '2024-12-17 11:07:09'),
(23, 8, 'User Boss logged in successfully.', '2024-12-17 11:51:41'),
(24, 8, 'User Boss logged in successfully.', '2024-12-17 12:05:26'),
(25, 8, 'User Boss logged in successfully.', '2024-12-17 13:04:53'),
(26, 8, 'User Boss logged in successfully.', '2024-12-17 13:17:10'),
(27, 8, 'User Boss logged in successfully.', '2024-12-17 13:43:07'),
(28, 8, 'User Boss logged in successfully.', '2024-12-17 14:24:32'),
(29, 8, 'User Boss logged in successfully.', '2024-12-17 14:39:45'),
(30, 8, 'User Boss logged in successfully.', '2024-12-19 15:42:08'),
(31, 8, 'User Boss logged in successfully.', '2024-12-19 16:16:03'),
(32, 8, 'User Boss logged in successfully.', '2024-12-19 16:30:35'),
(33, 8, 'User Boss logged in successfully.', '2024-12-19 16:52:14'),
(34, 8, 'User Boss logged in successfully.', '2024-12-19 18:07:58'),
(35, 8, 'User Boss logged in successfully.', '2024-12-19 18:21:37'),
(36, 8, 'User Boss logged in successfully.', '2024-12-19 18:52:25'),
(37, 8, 'User Boss logged in successfully.', '2024-12-20 06:28:24'),
(38, 8, 'User Boss logged in successfully.', '2024-12-20 06:31:09'),
(39, 8, 'User Boss logged in successfully.', '2024-12-20 07:23:23'),
(40, 8, 'User Boss logged in successfully.', '2024-12-20 08:45:40'),
(41, 8, 'User Boss logged in successfully.', '2024-12-23 11:41:59'),
(42, 8, 'User Boss logged in successfully.', '2024-12-23 11:44:47'),
(43, 8, 'User Boss logged in successfully.', '2024-12-23 12:00:35'),
(44, 8, 'User Boss logged in successfully.', '2024-12-23 12:26:18'),
(45, 8, 'User Boss logged in successfully.', '2024-12-23 13:16:39'),
(46, 8, 'User Boss logged in successfully.', '2024-12-26 17:12:42'),
(47, 8, 'User Boss logged in successfully.', '2024-12-27 08:51:20'),
(48, 8, 'User Boss logged in successfully.', '2024-12-27 12:08:09'),
(49, 8, 'User Boss logged in successfully.', '2024-12-27 13:09:16'),
(50, 8, 'User Boss logged in successfully.', '2024-12-27 13:11:45'),
(51, 8, 'User Boss logged in successfully.', '2024-12-27 13:25:02'),
(52, 8, 'User Boss logged in successfully.', '2024-12-27 14:03:34'),
(53, 8, 'User Boss logged in successfully.', '2025-01-06 16:56:54'),
(54, 8, 'User Boss logged in successfully.', '2025-01-06 17:50:31'),
(55, 8, 'User Boss logged in successfully.', '2025-02-11 13:25:12'),
(56, 8, 'User Boss logged in successfully.', '2025-02-11 13:26:31'),
(57, 8, 'User Boss logged in successfully.', '2025-02-12 13:42:32'),
(58, 8, 'User Boss logged in successfully.', '2025-02-12 13:58:12'),
(59, 8, 'User Boss logged in successfully.', '2025-02-14 09:19:46'),
(60, 8, 'User Boss logged in successfully.', '2025-02-16 09:36:45'),
(61, 8, 'User Boss logged in successfully.', '2025-02-16 09:48:46'),
(62, 8, 'User Boss logged in successfully.', '2025-02-16 10:14:07'),
(63, 8, 'User Boss logged in successfully.', '2025-02-17 07:26:45'),
(64, 8, 'User Boss logged in successfully.', '2025-02-17 08:12:47'),
(65, 8, 'User Boss logged in successfully.', '2025-02-17 08:39:00'),
(66, 8, 'User Boss logged in successfully.', '2025-02-17 08:59:02'),
(67, 15, 'User Manager logged in successfully.', '2025-02-17 09:10:33'),
(68, 15, 'User Manager logged in successfully.', '2025-02-17 09:23:35'),
(69, 15, 'User Manager logged in successfully.', '2025-02-17 09:30:10'),
(70, 16, 'User User logged in successfully.', '2025-02-17 09:39:23'),
(71, 16, 'User User logged in successfully.', '2025-02-17 09:40:12'),
(72, 16, 'User User logged in successfully.', '2025-02-17 09:43:44'),
(73, 16, 'User User logged in successfully.', '2025-02-17 09:51:01'),
(74, 16, 'User User logged in successfully.', '2025-02-17 09:54:36'),
(75, 16, 'User User logged in successfully.', '2025-02-17 09:54:47'),
(76, 16, 'User User logged in successfully.', '2025-02-17 09:56:43'),
(77, 8, 'User Boss logged in successfully.', '2025-02-17 10:04:21'),
(78, 16, 'User User logged in successfully.', '2025-02-17 10:32:24'),
(79, 16, 'User User logged in successfully.', '2025-02-17 10:36:46'),
(80, 16, 'User User logged in successfully.', '2025-02-17 10:41:06'),
(81, 15, 'User Manager logged in successfully.', '2025-02-17 10:54:35'),
(82, 15, 'User Manager logged in successfully.', '2025-02-17 14:55:44'),
(83, 8, 'User Boss logged in successfully.', '2025-02-17 17:19:46'),
(84, 8, 'User Boss logged in successfully.', '2025-02-18 09:25:22'),
(85, 15, 'User Manager logged in successfully.', '2025-02-18 09:37:36'),
(86, 16, 'User User logged in successfully.', '2025-02-18 09:53:41'),
(87, 15, 'User Manager logged in successfully.', '2025-02-18 09:54:29'),
(88, 8, 'User Boss logged in successfully.', '2025-02-18 09:59:23'),
(89, 8, 'User Boss logged in successfully.', '2025-02-18 10:22:50'),
(90, 8, 'User Boss logged in successfully.', '2025-02-18 10:52:48'),
(91, 8, 'User Boss logged in successfully.', '2025-02-18 11:42:40'),
(92, 8, 'User Boss logged in successfully.', '2025-02-18 12:07:06'),
(93, 8, 'User Boss logged in successfully.', '2025-02-18 12:40:40'),
(94, 8, 'User Boss logged in successfully.', '2025-02-18 13:17:25'),
(95, 8, 'User Boss logged in successfully.', '2025-02-18 13:48:05'),
(96, 8, 'User Boss logged in successfully.', '2025-02-18 14:35:57'),
(97, 8, 'User Boss logged in successfully.', '2025-02-18 14:54:20'),
(98, 8, 'User Boss logged in successfully.', '2025-02-18 15:27:19'),
(99, 8, 'User Boss logged in successfully.', '2025-02-18 15:54:32'),
(100, 8, 'User Boss logged in successfully.', '2025-02-25 12:47:01'),
(101, 8, 'User Boss logged in successfully.', '2025-02-25 14:57:56'),
(102, 8, 'User Boss logged in successfully.', '2025-02-25 16:04:45'),
(103, 8, 'User Boss logged in successfully.', '2025-02-25 17:10:51'),
(104, 8, 'User Boss logged in successfully.', '2025-02-25 17:51:11'),
(105, 8, 'User Boss logged in successfully.', '2025-02-27 12:10:13'),
(106, 8, 'User Boss logged in successfully.', '2025-02-27 14:39:18'),
(107, 8, 'User Boss logged in successfully.', '2025-02-27 15:11:18'),
(108, 8, 'User Boss logged in successfully.', '2025-02-27 15:14:32'),
(109, 8, 'User Boss logged in successfully.', '2025-02-27 15:21:35'),
(110, 15, 'User Manager logged in successfully.', '2025-02-27 15:25:46'),
(111, 8, 'User Boss logged in successfully.', '2025-02-27 15:41:27'),
(112, 15, 'User Manager logged in successfully.', '2025-02-27 15:44:50'),
(113, 8, 'User Boss logged in successfully.', '2025-02-27 17:18:45'),
(114, 15, 'User Manager logged in successfully.', '2025-02-27 17:19:06'),
(115, 8, 'User Boss logged in successfully.', '2025-02-27 17:19:58'),
(116, 16, 'User User logged in successfully.', '2025-02-27 17:21:04'),
(117, 8, 'User Boss logged in successfully.', '2025-02-27 17:52:23'),
(118, 15, 'User Manager logged in successfully.', '2025-02-27 18:07:30'),
(119, 8, 'User Boss logged in successfully.', '2025-02-27 18:11:12'),
(120, 8, 'User Boss logged in successfully.', '2025-02-27 19:24:27'),
(121, 15, 'User Manager logged in successfully.', '2025-02-27 19:28:59'),
(122, 16, 'User User logged in successfully.', '2025-02-27 19:40:33'),
(123, 8, 'User Boss logged in successfully.', '2025-02-27 20:00:03'),
(124, 8, 'User Boss logged in successfully.', '2025-02-28 09:02:50'),
(125, 8, 'User Boss logged in successfully.', '2025-02-28 10:53:44'),
(126, 8, 'User Boss logged in successfully.', '2025-02-28 11:23:50'),
(127, 8, 'User Boss logged in successfully.', '2025-02-28 11:39:29'),
(128, 8, 'User Boss logged in successfully.', '2025-02-28 14:39:41'),
(129, 8, 'User Boss logged in successfully.', '2025-02-28 14:58:30'),
(130, 8, 'User Boss logged in successfully.', '2025-03-04 13:06:02'),
(131, 8, 'User Boss logged in successfully.', '2025-03-04 13:46:28'),
(132, 8, 'User Boss logged in successfully.', '2025-03-05 17:34:17'),
(133, 8, 'User Boss logged in successfully.', '2025-03-05 22:01:54'),
(134, 8, 'User Boss logged in successfully.', '2025-03-06 12:30:34'),
(135, 8, 'User Boss logged in successfully.', '2025-03-06 14:32:42'),
(136, 15, 'User Manager logged in successfully.', '2025-03-06 14:44:54'),
(137, 16, 'User User logged in successfully.', '2025-03-06 14:49:06'),
(138, 15, 'User Manager logged in successfully.', '2025-03-06 14:53:15'),
(139, 8, 'User Boss logged in successfully.', '2025-03-06 14:53:34'),
(140, 16, 'User User logged in successfully.', '2025-03-06 15:10:20'),
(141, 15, 'User Manager logged in successfully.', '2025-03-06 15:10:32'),
(142, 8, 'User Boss logged in successfully.', '2025-03-07 09:21:29'),
(143, 8, 'User Boss logged in successfully.', '2025-03-07 09:47:08'),
(144, 8, 'User Boss logged in successfully.', '2025-03-07 10:26:13'),
(145, 8, 'User Boss logged in successfully.', '2025-03-07 11:20:44'),
(146, 8, 'User Boss logged in successfully.', '2025-03-07 11:49:41'),
(147, 8, 'User Boss logged in successfully.', '2025-03-07 11:52:41'),
(148, 8, 'User Boss logged in successfully.', '2025-03-07 13:13:05'),
(149, 8, 'User Boss logged in successfully.', '2025-03-26 13:25:46'),
(150, 8, 'User Boss logged in successfully.', '2025-03-26 13:47:53'),
(151, 8, 'User Boss logged in successfully.', '2025-03-27 08:38:47'),
(152, 8, 'User Boss logged in successfully.', '2025-03-27 10:27:30'),
(153, 8, 'User Boss logged in successfully.', '2025-03-27 10:30:45'),
(154, 8, 'User Boss logged in successfully.', '2025-03-27 10:33:12'),
(155, 8, 'User Boss logged in successfully.', '2025-03-27 11:08:32'),
(156, 8, 'User Boss logged in successfully.', '2025-03-27 11:36:07'),
(157, 8, 'User Boss logged in successfully.', '2025-03-27 11:37:21'),
(158, 8, 'User Boss logged in successfully.', '2025-03-27 11:44:22'),
(159, 8, 'User Boss logged in successfully.', '2025-03-28 06:09:29'),
(160, 8, 'User Boss logged in successfully.', '2025-03-28 06:23:36'),
(161, 8, 'User Boss logged in successfully.', '2025-03-28 07:35:44'),
(162, 8, 'User Boss logged in successfully.', '2025-03-28 07:53:50'),
(163, 8, 'User Boss logged in successfully.', '2025-03-28 08:42:20'),
(164, 8, 'User Boss logged in successfully.', '2025-03-30 15:10:22'),
(165, 8, 'User Boss logged in successfully.', '2025-03-30 15:21:30'),
(166, 8, 'User Boss logged in successfully.', '2025-03-31 17:48:54'),
(167, 8, 'User Boss logged in successfully.', '2025-04-01 14:51:16'),
(168, 8, 'User Boss logged in successfully.', '2025-04-22 15:01:31'),
(169, 8, 'User Boss logged in successfully.', '2025-05-06 12:06:30'),
(170, 8, 'User Boss logged in successfully.', '2025-05-06 12:09:20'),
(171, 8, 'User Boss logged in successfully.', '2025-05-06 12:37:14'),
(172, 8, 'User Boss logged in successfully.', '2025-05-06 12:42:43'),
(173, 8, 'User Boss logged in successfully.', '2025-05-06 12:45:21'),
(174, 8, 'User Boss logged in successfully.', '2025-05-06 13:15:17'),
(175, 8, 'User Boss logged in successfully.', '2025-05-06 13:30:20'),
(176, 8, 'User Boss logged in successfully.', '2025-05-06 14:05:40'),
(177, 8, 'User Boss logged in successfully.', '2025-05-06 14:10:59'),
(178, 8, 'User Boss logged in successfully.', '2025-05-06 14:28:40'),
(179, 8, 'User Boss logged in successfully.', '2025-05-06 14:44:28'),
(180, 8, 'User Boss logged in successfully.', '2025-05-06 19:43:37'),
(181, 8, 'User Boss logged in successfully.', '2025-05-12 13:51:40'),
(182, 8, 'User Boss logged in successfully.', '2025-05-12 16:07:09'),
(183, 8, 'User Boss logged in successfully.', '2025-05-14 12:55:22'),
(184, 8, 'User Boss logged in successfully.', '2025-05-14 13:44:53'),
(185, 8, 'User Boss logged in successfully.', '2025-05-18 10:33:50'),
(186, 8, 'User Boss logged in successfully.', '2025-05-18 10:54:33'),
(187, 8, 'User Boss logged in successfully.', '2025-05-23 07:46:04'),
(188, 8, 'User Boss logged in successfully.', '2025-05-23 11:51:10'),
(189, 8, 'User Boss logged in successfully.', '2025-05-26 15:32:16'),
(190, 8, 'User Boss logged in successfully.', '2025-05-26 16:14:19'),
(191, 8, 'User Boss logged in successfully.', '2025-05-26 16:43:19'),
(192, 8, 'User Boss logged in successfully.', '2025-05-26 17:24:23'),
(193, 8, 'User Boss logged in successfully.', '2025-05-27 07:05:27'),
(194, 8, 'User Boss logged in successfully.', '2025-05-27 07:11:01'),
(195, 8, 'User Boss logged in successfully.', '2025-05-27 07:16:04'),
(196, 8, 'User Boss logged in successfully.', '2025-05-27 12:12:10'),
(197, 8, 'User Boss logged in successfully.', '2025-06-03 07:03:23'),
(198, 8, 'User Boss logged in successfully.', '2025-06-03 07:57:11'),
(199, 8, 'User Boss logged in successfully.', '2025-06-03 08:36:39'),
(200, 8, 'User Boss logged in successfully.', '2025-06-03 09:38:39'),
(201, 8, 'User Boss logged in successfully.', '2025-06-03 12:42:37'),
(202, 8, 'User Boss logged in successfully.', '2025-06-03 12:44:21'),
(203, 8, 'User Boss logged in successfully.', '2025-06-03 17:17:23'),
(204, 8, 'User Boss logged in successfully.', '2025-06-10 13:16:00'),
(205, 8, 'User Boss logged in successfully.', '2025-06-16 13:41:19'),
(206, 8, 'User Boss logged in successfully.', '2025-06-16 13:59:12'),
(207, 8, 'User Boss logged in successfully.', '2025-06-17 14:56:19'),
(208, 8, 'User Boss logged in successfully.', '2025-06-17 16:13:46'),
(209, 8, 'User Boss logged in successfully.', '2025-06-17 17:15:51'),
(210, 8, 'User Boss logged in successfully.', '2025-06-17 18:41:42'),
(211, 15, 'User Manager logged in successfully.', '2025-06-17 18:46:28'),
(212, 15, 'User Manager logged in successfully.', '2025-06-17 19:06:17'),
(213, 8, 'User Boss logged in successfully.', '2025-06-17 19:07:02'),
(214, 8, 'User Boss logged in successfully.', '2025-07-14 17:03:06'),
(215, 16, 'User User logged in successfully.', '2025-07-14 17:07:59'),
(216, 15, 'User Manager logged in successfully.', '2025-07-14 17:10:58'),
(217, 15, 'User Manager logged in successfully.', '2025-07-14 17:22:08'),
(218, 15, 'User Manager logged in successfully.', '2025-07-14 17:23:09'),
(219, 15, 'User Manager logged in successfully.', '2025-07-14 17:25:18'),
(220, 16, 'User User logged in successfully.', '2025-07-14 17:39:37'),
(221, 8, 'User Boss logged in successfully.', '2025-07-14 17:49:33'),
(222, 22, 'User Storekeeper logged in successfully.', '2025-07-15 08:37:51'),
(223, 15, 'User Manager logged in successfully.', '2025-07-15 08:38:06'),
(224, 16, 'User User logged in successfully.', '2025-07-15 08:53:35'),
(225, 15, 'User Manager logged in successfully.', '2025-07-15 10:18:06'),
(226, 16, 'User User logged in successfully.', '2025-07-15 10:33:20'),
(227, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:18:54'),
(228, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:21:38'),
(229, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:22:14'),
(230, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:22:44'),
(231, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:23:21'),
(232, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:25:39'),
(233, 22, 'User Storekeeper logged in successfully.', '2025-07-15 11:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `attendance_type` enum('Manual','QR Code') DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `clock_in_time` time NOT NULL,
  `clock_out_time` time DEFAULT NULL,
  `proofofclockin` enum('camera','biometric','off') DEFAULT NULL,
  `proofofclockout` enum('camera','biometric','off') DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`attendance_id`, `employee_id`, `schedule_id`, `attendance_type`, `attendance_date`, `clock_in_time`, `clock_out_time`, `proofofclockin`, `proofofclockout`, `notes`) VALUES
(19, 61, 19, 'Manual', '2025-06-03', '21:59:00', '21:59:23', 'off', NULL, ''),
(20, 3, 15, 'Manual', '2025-06-04', '15:51:00', NULL, 'off', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_accompaniments`
--

CREATE TABLE `buffet_accompaniments` (
  `accompaniment_id` int(11) NOT NULL,
  `buffet_item_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `accompaniment_price` decimal(10,2) NOT NULL,
  `status` enum('active','canceled') DEFAULT 'active',
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `buffet_accompaniments`
--

INSERT INTO `buffet_accompaniments` (`accompaniment_id`, `buffet_item_id`, `menu_id`, `accompaniment_price`, `status`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 28, 11, '16000.00', 'active', NULL, '2025-05-27 13:50:06', '2025-05-27 13:50:06');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_preferences`
--

CREATE TABLE `buffet_preferences` (
  `period_id` int(11) NOT NULL,
  `period_name` enum('Morning','Noon','Evening') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `fixed_discount` decimal(10,2) DEFAULT NULL,
  `percentage_discount` decimal(5,2) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buffet_preferences`
--

INSERT INTO `buffet_preferences` (`period_id`, `period_name`, `start_time`, `end_time`, `base_price`, `fixed_discount`, `percentage_discount`, `valid_from`, `valid_to`, `is_active`) VALUES
(1, 'Noon', '11:00:00', '15:00:00', '10000.00', '5000.00', NULL, NULL, NULL, 1),
(2, 'Morning', '05:00:00', '11:00:00', '15000.00', '6000.00', NULL, NULL, NULL, 1),
(3, 'Evening', '15:00:00', '23:59:00', '20000.00', '10000.00', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sales`
--

CREATE TABLE `buffet_sales` (
  `sale_id` int(11) NOT NULL,
  `sale_date` datetime NOT NULL,
  `dishes_sold` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','canceled') DEFAULT 'active',
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buffet_sales`
--

INSERT INTO `buffet_sales` (`sale_id`, `sale_date`, `dishes_sold`, `total_price`, `status`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(15, '2025-05-27 14:57:05', 1, '5000.00', 'active', NULL, '2025-05-27 12:57:05', '2025-05-27 12:57:05'),
(24, '2025-05-27 15:13:39', 1, '10000.00', 'active', NULL, '2025-05-27 13:13:39', '2025-05-27 13:13:39'),
(25, '2025-05-27 15:13:50', 1, '10000.00', 'active', NULL, '2025-05-27 13:13:50', '2025-05-27 13:13:50'),
(26, '2025-05-27 15:22:14', 1, '10000.00', 'active', NULL, '2025-05-27 13:22:14', '2025-05-27 13:22:14'),
(27, '2025-05-27 15:40:24', 1, '20000.00', 'active', NULL, '2025-05-27 13:40:24', '2025-05-27 13:40:24'),
(28, '2025-05-27 15:50:06', 1, '20000.00', 'canceled', 'test', '2025-05-27 13:50:06', '2025-06-03 06:11:54'),
(29, '2025-06-03 10:06:42', 1, '15000.00', 'active', NULL, '2025-06-03 08:06:42', '2025-06-03 08:06:42'),
(30, '2025-06-03 10:17:50', 1, '9000.00', 'active', NULL, '2025-06-03 08:17:50', '2025-06-03 08:17:50'),
(31, '2025-06-03 10:24:27', 1, '15000.00', 'active', NULL, '2025-06-03 08:24:27', '2025-06-03 08:24:27'),
(32, '2025-06-03 11:28:03', 1, '10000.00', 'active', NULL, '2025-06-03 09:28:03', '2025-06-03 09:28:03'),
(33, '2025-06-03 11:38:50', 1, '10000.00', 'active', NULL, '2025-06-03 09:38:50', '2025-06-03 09:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sale_adjustments`
--

CREATE TABLE `buffet_sale_adjustments` (
  `adjustment_id` int(11) NOT NULL,
  `buffet_item_id` int(11) NOT NULL,
  `adjustment_type` enum('Discount','Refund','Other') NOT NULL,
  `adjustment_amount` decimal(10,2) NOT NULL,
  `adjustment_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `buffet_sale_adjustments`
--

INSERT INTO `buffet_sale_adjustments` (`adjustment_id`, `buffet_item_id`, `adjustment_type`, `adjustment_amount`, `adjustment_reason`, `created_at`, `updated_at`) VALUES
(1, 15, 'Discount', '5000.00', '', '2025-05-27 12:57:05', '2025-05-27 12:57:05'),
(2, 24, 'Discount', '10000.00', '', '2025-05-27 13:13:39', '2025-05-27 13:13:39'),
(3, 25, 'Discount', '10000.00', '', '2025-05-27 13:13:50', '2025-05-27 13:13:50'),
(4, 26, 'Discount', '10000.00', '', '2025-05-27 13:22:14', '2025-05-27 13:22:14'),
(5, 30, 'Discount', '6000.00', '', '2025-06-03 08:17:50', '2025-06-03 08:17:50');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sale_item`
--

CREATE TABLE `buffet_sale_item` (
  `buffet_item_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `sale_date` date NOT NULL,
  `time_of_day` enum('Morning','Evening','Noon') NOT NULL,
  `status` enum('active','canceled') DEFAULT 'active',
  `cancellation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buffet_sale_item`
--

INSERT INTO `buffet_sale_item` (`buffet_item_id`, `item_id`, `quantity_used`, `sale_date`, `time_of_day`, `status`, `cancellation_reason`) VALUES
(32, 16, '1.00', '2025-06-03', 'Morning', 'active', NULL),
(33, 16, '2.00', '2025-06-03', 'Morning', 'active', NULL),
(34, 16, '2.00', '2025-06-03', 'Morning', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sale_items`
--

CREATE TABLE `buffet_sale_items` (
  `buffet_item_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `time_of_day` enum('Morning','Noon','Evening') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','canceled') DEFAULT 'active',
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `buffet_sale_items`
--

INSERT INTO `buffet_sale_items` (`buffet_item_id`, `sale_date`, `time_of_day`, `price`, `status`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(15, '2025-05-27', 'Noon', '10000.00', 'active', NULL, '2025-05-27 12:57:05', '2025-05-27 12:57:05'),
(24, '2025-05-27', 'Evening', '20000.00', 'active', NULL, '2025-05-27 13:13:39', '2025-05-27 13:13:39'),
(25, '2025-05-27', 'Evening', '20000.00', 'active', NULL, '2025-05-27 13:13:50', '2025-05-27 13:13:50'),
(26, '2025-05-27', 'Evening', '20000.00', 'active', NULL, '2025-05-27 13:22:14', '2025-05-27 13:22:14'),
(27, '2025-05-27', 'Evening', '20000.00', 'active', NULL, '2025-05-27 13:40:24', '2025-05-27 13:40:24'),
(28, '2025-05-27', 'Evening', '20000.00', 'active', NULL, '2025-05-27 13:50:06', '2025-05-27 13:50:06'),
(29, '2025-06-03', 'Morning', '15000.00', 'active', NULL, '2025-06-03 08:06:42', '2025-06-03 08:06:42'),
(30, '2025-06-03', 'Morning', '15000.00', 'active', NULL, '2025-06-03 08:17:50', '2025-06-03 08:17:50'),
(31, '2025-06-03', 'Morning', '15000.00', 'active', NULL, '2025-06-03 08:24:27', '2025-06-03 08:24:27'),
(32, '2025-06-03', 'Morning', '9000.00', 'active', NULL, '2025-06-03 08:25:05', '2025-06-03 08:25:05'),
(33, '2025-06-03', 'Morning', '9000.00', 'active', NULL, '2025-06-03 08:26:52', '2025-06-03 08:26:52'),
(34, '2025-06-03', 'Morning', '9000.00', 'active', NULL, '2025-06-03 08:35:46', '2025-06-03 08:35:46'),
(35, '2025-06-03', 'Noon', '10000.00', 'active', NULL, '2025-06-03 09:28:03', '2025-06-03 09:28:03'),
(36, '2025-06-03', 'Noon', '10000.00', 'active', NULL, '2025-06-03 09:38:50', '2025-06-03 09:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `cancellation_reasons`
--

CREATE TABLE `cancellation_reasons` (
  `id` int(11) NOT NULL,
  `buffet_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Drink'),
(4, 'Equipment'),
(3, 'Food');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `created_at`, `password`) VALUES
(8, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique@gmail.com', '68072105', 'Av. Ntare Rugamba', '2024-12-15 11:16:39', NULL),
(9, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique1@gmail.com', '68072107', 'Av. Ntare Rugamba,kirundo', '2024-12-15 11:16:48', NULL),
(13, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique10@gmail.com', '68072109', 'Av. Ntare Rugamba', '2024-12-15 13:47:35', NULL),
(14, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique11@gmail.com', '68072110', 'Av. Ntare Rugamba,kirundo', '2024-12-15 13:47:35', NULL),
(15, 'John Esterique1', 'Itangishaka', 'itangishakajohnesterique21@gmail.com', '68072111', 'Av. Ntare Rugamba', '2024-12-15 13:47:35', NULL),
(17, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique12@gmail.com', '68072105', 'Av. Ntare Rugamba', '2025-02-21 00:16:50', '12345678'),
(18, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique@gmail.co', '68072105', 'Av. Ntare Rugamba', '2025-02-21 00:21:40', 'Virgi1962!'),
(19, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique13@gmail.com', '68072105', 'Av. Ntare Rugamba', '2025-02-21 00:45:02', 'Virgi1962!'),
(22, 'Guest2', 'Irakoze', 'emeryirakoze10@gmail.com', '79796567', 'Kirundo', '2025-02-28 14:42:27', NULL),
(23, 'John Esterique', 'Itangishaka', 'itangishakajohnesterique111@gmail.com', '6807210511', 'Av. Ntare Rugamba', '2025-03-06 13:11:13', NULL),
(25, 'John Esterique1', 'Itangishaka1', 'itangishakajohnesterique15@gmail.com', '680721051', 'Av. Ntare Rugamba', '2025-03-28 10:57:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `debts`
--

CREATE TABLE `debts` (
  `debt_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','partial','paid','overdue') NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `debts`
--

INSERT INTO `debts` (`debt_id`, `customer_id`, `amount`, `due_date`, `status`, `created_by`, `created_at`, `updated_at`, `notes`) VALUES
(1, 22, '1000.00', '0000-00-00', 'paid', 8, '2025-05-18 14:21:41', '2025-05-18 15:49:02', 'text'),
(2, 25, '100000.00', '2025-05-18', 'partial', 8, '2025-05-18 14:26:51', '2025-05-18 16:13:13', 'Yariye ibiraya n\'amagi abiri'),
(4, 25, '5000.00', '2025-05-18', 'paid', 8, '2025-05-18 17:30:40', '2025-05-18 17:31:27', ''),
(5, 25, '10000.00', '2025-05-18', 'pending', 8, '2025-05-18 17:40:05', '2025-05-18 17:40:05', ''),
(6, 22, '100000.00', '2025-06-16', 'pending', 8, '2025-06-16 16:01:32', '2025-06-16 16:01:32', 'testing...');

-- --------------------------------------------------------

--
-- Table structure for table `debt_payments`
--

CREATE TABLE `debt_payments` (
  `payment_id` int(11) NOT NULL,
  `debt_id` int(11) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `paid_date` datetime NOT NULL DEFAULT current_timestamp(),
  `method` enum('cash','bank','electronic') NOT NULL DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `debt_payments`
--

INSERT INTO `debt_payments` (`payment_id`, `debt_id`, `paid_amount`, `paid_date`, `method`, `notes`, `created_by`) VALUES
(1, 1, '5.00', '2025-05-18 14:43:39', 'cash', '', 8),
(2, 1, '3.00', '2025-05-18 14:47:27', 'cash', '', 8),
(3, 1, '992.00', '2025-05-18 15:49:02', 'cash', '', 8),
(4, 2, '50000.00', '2025-05-18 15:53:03', 'cash', '', 8),
(5, 4, '2000.00', '2025-05-18 17:30:59', 'cash', '', 8),
(6, 4, '3000.00', '2025-05-18 17:31:26', 'cash', '', 8);

-- --------------------------------------------------------

--
-- Table structure for table `drink_sales`
--

CREATE TABLE `drink_sales` (
  `sale_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','canceled') DEFAULT 'active',
  `cancellation_reason` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `drink_sales`
--

INSERT INTO `drink_sales` (`sale_id`, `inventory_id`, `quantity_sold`, `sale_price`, `sale_date`, `status`, `cancellation_reason`, `updated_at`) VALUES
(1, 16, 1, '3500.00', '2025-05-27 14:22:13', 'canceled', 'test', '2025-06-03 05:59:42');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `position`, `salary`, `hire_date`, `created_at`) VALUES
(3, 1, 'Manager', '600000.00', '2024-12-05', '2024-12-23 14:04:52'),
(61, 17, 'Manager', '1000000.00', '2025-05-15', '2025-05-15 09:51:11');

-- --------------------------------------------------------

--
-- Table structure for table `employee_loans`
--

CREATE TABLE `employee_loans` (
  `loan_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL,
  `loan_date` date NOT NULL,
  `purpose` text DEFAULT NULL,
  `outstanding_balance` decimal(10,2) NOT NULL,
  `status` enum('active','repaid','defaulted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employee_loans`
--

INSERT INTO `employee_loans` (`loan_id`, `employee_id`, `loan_amount`, `loan_date`, `purpose`, `outstanding_balance`, `status`) VALUES
(1, 3, '100000.00', '2025-06-16', 'test', '0.00', 'repaid'),
(2, 3, '100000.00', '2025-06-16', '', '60000.00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `description`, `amount`, `expense_date`, `category`, `notes`, `created_at`) VALUES
(4, 'Rent', '20000.00', '2025-06-03', 'Rent', '', '2025-06-03 09:25:08'),
(5, 'water', '20000.00', '2025-06-03', 'Water', '', '2025-06-03 09:25:35'),
(7, 'Commande de 03/06/2025', '20000.00', '2025-06-03', 'Supplies', '', '2025-06-03 13:58:17');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity_in_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) NOT NULL,
  `reorder_level` decimal(10,2) DEFAULT 0.00,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`inventory_id`, `item_name`, `category`, `description`, `quantity_in_stock`, `unit`, `reorder_level`, `supplier_id`, `created_at`, `updated_at`, `unit_cost`) VALUES
(24, 'primus', '1', NULL, '19.00', '5', '12.00', 4, '2025-04-01 16:35:14', '2025-05-08 17:58:28', '3450.00'),
(25, 'Bajou', '1', '0', '25.00', '5', '16.00', 4, '2025-04-01 16:42:33', '2025-04-01 17:04:19', '3000.00'),
(27, 'Amstel B', '1', NULL, '18.00', '5', '12.00', 4, '2025-04-01 16:47:03', '2025-04-01 17:04:49', '6000.00'),
(28, 'Bechou', '1', NULL, '19.00', '5', '10.00', 4, '2025-04-01 16:48:59', '2025-04-01 17:05:22', '5000.00'),
(29, 'Fanta', '1', NULL, '62.00', '5', '48.00', 4, '2025-04-01 16:50:45', '2025-04-01 17:06:18', '2500.00'),
(30, 'monac', '1', NULL, '16.00', '5', '15.00', 4, '2025-04-01 16:51:59', '2025-04-01 17:06:38', '2500.00'),
(31, 'vital', '1', NULL, '13.00', '5', '12.00', 4, '2025-04-01 16:53:04', '2025-04-01 17:06:55', '1500.00'),
(32, 'G kinju', '1', NULL, '12.00', '5', '13.00', 4, '2025-04-01 16:54:09', '2025-04-01 17:07:18', '3000.00'),
(33, 'Kandi', '1', '0', '15.00', '2', '11.00', 2, '2025-04-01 16:55:14', '2025-06-03 16:21:19', '2500.00'),
(34, 'P kinju', '1', NULL, '13.00', '5', '10.00', 4, '2025-04-01 16:56:21', '2025-04-01 17:08:08', '2000.00'),
(35, 'TW', '1', NULL, '19.00', '5', '13.00', 4, '2025-04-01 16:57:23', '2025-04-01 17:08:29', '1000.00'),
(36, 'Bread', '3', NULL, '10.00', '3', '5.00', 4, '2025-04-02 10:11:40', '2025-04-02 10:11:40', '1100.00'),
(37, 'lettuce,tomato', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-02 10:13:12', '2025-04-02 10:13:12', '500.00'),
(39, 'Concombre', '3', NULL, '4.00', '1', '2.00', 4, '2025-04-02 10:18:30', '2025-04-02 10:18:30', '2500.00'),
(40, 'poirreau', '3', NULL, '1.00', '6', '0.00', 4, '2025-04-02 10:19:33', '2025-04-02 10:19:33', '1500.00'),
(41, 'poivron', '3', NULL, '1.00', '6', '0.00', 4, '2025-04-02 10:21:31', '2025-04-02 10:21:31', '1500.00'),
(42, 'cellelit', '3', NULL, '1.00', '6', '0.00', 4, '2025-04-02 10:23:05', '2025-04-02 10:23:05', '1500.00'),
(43, 'Tomato', '3', NULL, '5.00', '1', '2.00', 4, '2025-04-02 10:24:29', '2025-04-02 10:24:29', '2500.00'),
(44, 'Haricot vert', '3', NULL, '1.00', '6', '0.00', 4, '2025-04-02 10:29:52', '2025-04-02 10:29:52', '4500.00'),
(45, 'ognion blanc', '3', NULL, '8.00', '1', '4.00', 4, '2025-04-02 10:31:39', '2025-04-02 10:31:39', '4000.00'),
(46, 'pomme de terre', '3', NULL, '40.00', '1', '20.00', 4, '2025-04-02 10:35:38', '2025-04-02 10:35:38', '1900.00'),
(47, 'salsa', '3', NULL, '12.00', '6', '6.00', 4, '2025-04-02 10:37:14', '2025-04-02 10:37:14', '1916.00'),
(48, 'petit poids', '3', NULL, '8.00', '1', '4.00', 4, '2025-04-02 10:39:07', '2025-04-02 10:39:07', '10000.00'),
(49, 'viande', '3', NULL, '3.00', '1', '2.00', 4, '2025-04-02 10:40:27', '2025-04-02 10:40:27', '33000.00'),
(50, 'riz client', '3', NULL, '16.00', '1', '2.00', 4, '2025-04-02 10:43:33', '2025-05-08 18:20:28', '7000.00'),
(51, 'poisson', '3', NULL, '3.00', '1', '0.00', 4, '2025-04-02 10:47:56', '2025-04-02 10:47:56', '59000.00'),
(52, 'riz pour le personnelle', '3', NULL, '4.00', '1', '2.00', 4, '2025-04-02 10:49:12', '2025-04-02 10:49:12', '5500.00'),
(53, 'epice', '1', NULL, '1.00', '7', '0.00', 4, '2025-04-02 10:50:53', '2025-04-02 10:50:53', '1000.00'),
(54, 'serviette', '4', NULL, '1.00', '8', '0.00', 4, '2025-04-02 10:52:43', '2025-04-02 10:52:43', '13500.00'),
(55, 'choux', '1', NULL, '1.00', '3', '0.00', 4, '2025-04-02 10:53:15', '2025-04-02 10:53:15', '3000.00'),
(56, 'tourne sol', '3', NULL, '5.00', '2', '2.00', 4, '2025-04-02 10:55:06', '2025-04-02 10:55:06', '80500.00'),
(57, 'farine', '3', NULL, '4.00', '1', '2.00', 4, '2025-04-02 10:56:34', '2025-04-02 10:56:34', '7000.00'),
(58, 'soy-sauce', '3', NULL, '1.00', '5', '0.00', 4, '2025-04-02 10:57:30', '2025-04-02 10:57:30', '26000.00'),
(59, 'omo', '4', NULL, '1.00', '8', '0.00', 4, '2025-04-02 10:59:01', '2025-04-02 10:59:01', '4000.00'),
(60, 'sel', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-02 11:00:14', '2025-04-02 11:00:14', '3000.00'),
(61, 'vinaigre', '3', NULL, '1.00', '5', '0.00', 4, '2025-04-02 11:03:32', '2025-04-02 11:03:32', '2000.00'),
(62, 'Avocat', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-02 11:04:54', '2025-04-02 11:04:54', '10000.00'),
(63, 'Carrot', '3', NULL, '7.00', '1', '3.00', 4, '2025-04-02 11:06:03', '2025-04-02 11:06:03', '1000.00'),
(64, 'Banane vert', '3', NULL, '18.00', '1', '2.00', 4, '2025-04-02 11:10:13', '2025-04-02 11:10:13', '2300.00'),
(65, 'farine manioc', '3', '0', '16.00', '7', '0.00', 4, '2025-04-02 11:11:35', '2025-04-16 10:22:32', '4000.00'),
(66, 'lengalenga', '3', NULL, '1.00', '6', '0.00', 4, '2025-04-02 11:13:51', '2025-04-02 11:13:51', '30000.00'),
(67, 'aubergine african', '1', '0', '5.00', '7', '0.00', 4, '2025-04-02 11:15:39', '2025-04-02 11:18:36', '1220.00'),
(68, 'macaronie', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-02 11:20:02', '2025-04-02 11:20:02', '18000.00'),
(69, 'arachide', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-02 11:25:36', '2025-04-02 11:25:36', '34000.00'),
(70, 'huile', '1', NULL, '1.00', '7', '0.00', 4, '2025-04-02 11:26:55', '2025-04-02 11:26:55', '305000.00'),
(71, 'aubergine local', '3', NULL, '2.00', '1', '0.00', 4, '2025-04-02 11:30:45', '2025-04-02 11:30:45', '1000.00'),
(72, 'maggy', '3', NULL, '34.00', '3', '14.00', 4, '2025-04-02 11:33:49', '2025-04-02 11:33:49', '8500.00'),
(73, 'farnine de mais', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-02 11:35:24', '2025-04-02 11:35:24', '10000.00'),
(74, 'aie', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-02 11:38:10', '2025-04-02 11:38:10', '6000.00'),
(75, 'Pain coupe', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-02 11:39:16', '2025-04-02 11:39:16', '8000.00'),
(76, 'ragout', '3', '0', '1.00', '3', '0.00', 4, '2025-04-02 11:39:52', '2025-04-15 10:53:19', '10000.00'),
(77, 'jambo', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-02 11:40:33', '2025-04-02 11:40:33', '6000.00'),
(78, 'Epinard', '3', NULL, '1.00', '6', '0.00', 4, '2025-04-02 11:41:18', '2025-04-02 11:41:18', '2000.00'),
(81, 'milk', '1', NULL, '30.00', '9', '10.00', 4, '2025-04-09 09:59:29', '2025-04-09 09:59:29', '1200.00'),
(83, 'poudre de caffe', '1', NULL, '1000.00', '4', '500.00', 4, '2025-04-09 10:21:47', '2025-04-09 10:21:47', '200.00'),
(84, 'gigimber', '1', NULL, '1.00', '4', '0.00', 4, '2025-04-09 12:20:44', '2025-04-09 12:20:44', '100.00'),
(85, 'chocolat en poudre', '1', NULL, '30.00', '4', '0.00', 4, '2025-04-09 12:59:12', '2025-04-09 12:59:12', '40000.00'),
(86, 'tea bag', '1', NULL, '1.00', '8', '0.00', 4, '2025-04-09 13:20:14', '2025-04-09 13:20:14', '7000.00'),
(87, 'oignon rouge', '3', '0', '1.00', '7', '0.00', 4, '2025-04-09 13:50:58', '2025-04-09 13:53:40', '3500.00'),
(88, 'honey', '3', NULL, '20.00', '4', '0.00', 4, '2025-04-09 13:57:08', '2025-04-09 13:57:08', '1000.00'),
(89, 'lemon', '3', NULL, '2.00', '3', '0.00', 4, '2025-04-09 13:57:45', '2025-04-09 13:57:45', '200.00'),
(90, 'the vert', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-09 14:01:12', '2025-04-09 14:01:12', '500.00'),
(91, 'pastec', '3', NULL, '5.00', '3', '2.00', 4, '2025-04-09 14:20:57', '2025-04-09 14:20:57', '2000.00'),
(92, 'ananas', '3', NULL, '5.00', '3', '2.00', 4, '2025-04-09 14:21:28', '2025-04-09 14:21:28', '1000.00'),
(93, 'bananas', '3', NULL, '5.00', '3', '0.00', 4, '2025-04-09 14:22:08', '2025-04-09 14:22:08', '500.00'),
(94, 'papaye', '3', NULL, '5.00', '3', '2.00', 4, '2025-04-09 14:22:46', '2025-04-09 14:22:46', '1000.00'),
(95, 'mangue', '1', '0', '5.00', '3', '0.00', 4, '2025-04-09 14:23:23', '2025-04-09 14:44:43', '5000.00'),
(96, 'beterave', '3', NULL, '5.00', '3', '2.00', 4, '2025-04-09 14:24:44', '2025-04-09 14:24:44', '200.00'),
(97, 'carotte', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-09 14:35:02', '2025-04-09 14:35:02', '1000.00'),
(98, 'orange', '3', NULL, '5.00', '3', '2.00', 4, '2025-04-09 14:38:54', '2025-04-09 14:38:54', '3000.00'),
(99, 'fraise', '3', NULL, '5.00', '3', '0.00', 4, '2025-04-09 15:25:05', '2025-04-09 15:25:05', '1000.00'),
(100, 'yaourt', '3', NULL, '1.00', '7', '0.00', 4, '2025-04-09 15:26:01', '2025-04-09 15:26:01', '5000.00'),
(101, 'datte', '3', NULL, '5.00', '3', '0.00', 4, '2025-04-14 12:04:50', '2025-04-14 12:04:50', '2000.00'),
(102, 'Beurre de cacaoute', '3', NULL, '20.00', '4', '0.00', 4, '2025-04-14 12:08:27', '2025-04-14 12:08:27', '5000.00'),
(103, 'chocolat', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-14 12:10:42', '2025-04-14 12:10:42', '10000.00'),
(104, 'ice cream', '3', NULL, '1.00', '7', '0.00', 4, '2025-04-14 12:26:29', '2025-04-14 12:26:29', '10000.00'),
(105, 'oreo', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-14 12:27:09', '2025-04-14 12:27:09', '15000.00'),
(106, 'nutella', '3', NULL, '1.00', '7', '0.00', 4, '2025-04-14 12:33:31', '2025-04-14 12:33:31', '30000.00'),
(107, 'oeuf', '3', NULL, '28.00', '3', '0.00', 4, '2025-04-14 12:48:10', '2025-05-08 19:39:06', '1200.00'),
(108, 'fromage', '3', NULL, '450.00', '4', '0.00', 4, '2025-04-14 12:54:48', '2025-04-14 12:54:48', '40000.00'),
(109, 'ketchup', '3', NULL, '1.00', '5', '0.00', 4, '2025-04-14 13:03:22', '2025-04-14 13:03:22', '10000.00'),
(110, 'mayonaise', '3', NULL, '30000.00', '5', '0.00', 4, '2025-04-14 13:04:29', '2025-04-14 13:04:29', '1.00'),
(111, 'champignon', '3', NULL, '20.00', '4', '0.00', 4, '2025-04-14 13:55:53', '2025-04-14 13:55:53', '2000.00'),
(112, 'pomme de terre commande', '3', NULL, '1000.00', '4', '0.00', 4, '2025-04-14 14:24:12', '2025-04-14 14:24:12', '2000.00'),
(113, 'filet de boeuf', '3', NULL, '200.00', '4', '0.00', 4, '2025-04-15 10:40:54', '2025-04-15 10:40:54', '7000.00'),
(115, 'saussisson', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-15 10:48:32', '2025-04-15 10:48:32', '3500.00'),
(116, 'chevre', '3', NULL, '200.00', '4', '0.00', 4, '2025-04-15 10:49:30', '2025-04-15 10:49:30', '7000.00'),
(117, 'vin rouge', '1', NULL, '1.00', '5', '0.00', 4, '2025-04-15 10:51:56', '2025-04-15 10:51:56', '10000.00'),
(118, 'jaret', '3', NULL, '200.00', '4', '0.00', 4, '2025-04-15 10:54:30', '2025-04-15 10:54:30', '15000.00'),
(119, 'Boeuf', '3', NULL, '200.00', '4', '0.00', 4, '2025-04-15 11:22:17', '2025-04-15 11:22:17', '10000.00'),
(120, 'pain hamburger', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-15 11:25:12', '2025-04-15 11:25:12', '20000.00'),
(121, 'carbonate de boeuf', '3', NULL, '200.00', '4', '0.00', 4, '2025-04-15 11:39:53', '2025-04-15 11:39:53', '9000.00'),
(122, 'pain', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-15 11:57:58', '2025-04-15 11:57:58', '5000.00'),
(123, 'sucre', '3', NULL, '200.00', '4', '0.00', 4, '2025-04-16 09:58:43', '2025-04-16 09:58:43', '1600.00'),
(124, 'chocolat sire', '3', NULL, '1.00', '5', '0.00', 4, '2025-04-16 09:59:50', '2025-04-16 09:59:50', '30000.00'),
(125, 'atunda', '3', NULL, '7.00', '3', '0.00', 4, '2025-04-16 10:04:04', '2025-04-16 10:04:04', '500.00'),
(126, 'citron', '3', NULL, '12.00', '3', '0.00', 4, '2025-04-16 10:06:02', '2025-04-16 10:06:02', '2000.00'),
(127, 'petit banane', '3', NULL, '8.00', '3', '0.00', 4, '2025-04-16 10:06:57', '2025-04-16 10:06:57', '2000.00'),
(128, '1/4 de poulet', '5', '0', '1.00', '7', '0.00', 4, '2025-04-16 10:08:42', '2025-05-08 18:11:12', '50000.00'),
(129, 'magi', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-16 10:09:19', '2025-04-16 10:09:19', '500.00'),
(130, 'poivre blanc', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-16 10:10:03', '2025-04-16 10:10:03', '500.00'),
(131, 'cuhe', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-16 10:10:57', '2025-04-16 10:10:57', '30000.00'),
(132, 'Mukeke', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:11:53', '2025-04-16 10:11:53', '55000.00'),
(133, 'sangala', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:12:59', '2025-04-16 10:12:59', '59000.00'),
(134, 'creme', '3', NULL, '1.00', '7', '0.00', 4, '2025-04-16 10:13:35', '2025-04-16 10:13:35', '7000.00'),
(135, 'curry', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-16 10:14:06', '2025-04-16 10:14:06', '1000.00'),
(136, 'pain sandwich', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-16 10:15:19', '2025-04-16 10:15:19', '2000.00'),
(137, 'blanc de poulet', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:16:35', '2025-04-16 10:16:35', '54000.00'),
(139, 'capitaine', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:19:05', '2025-04-16 10:19:05', '63000.00'),
(140, 'courgette', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:19:57', '2025-04-16 10:19:57', '2000.00'),
(141, 'frite banane', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:20:49', '2025-04-16 10:20:49', '2500.00'),
(142, 'farine de ble', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-16 10:24:41', '2025-04-16 10:24:41', '6500.00'),
(143, 'pain croissant', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-16 10:28:54', '2025-04-16 10:28:54', '3000.00'),
(144, 'ndagala', '5', NULL, '200.00', '7', '0.00', 4, '2025-04-17 11:54:21', '2025-04-17 11:54:21', '7000.00'),
(145, 'viande hache', '3', NULL, '1.00', '1', '0.00', 4, '2025-04-17 13:36:06', '2025-04-17 13:36:06', '18000.00'),
(146, 'pate de pizza', '3', NULL, '1.00', '3', '0.00', 4, '2025-04-17 13:38:22', '2025-04-17 13:38:22', '2000.00'),
(147, 'olive', '3', NULL, '5.00', '3', '0.00', 4, '2025-04-17 13:39:18', '2025-04-17 13:39:18', '1000.00'),
(148, 'sphaghetti', '3', NULL, '1.00', '8', '0.00', 4, '2025-04-17 13:40:49', '2025-04-17 13:40:49', '2000.00'),
(149, 'pate', '3', NULL, '2.00', '3', '0.00', 4, '2025-04-17 13:46:32', '2025-04-17 13:46:32', '2000.00'),
(150, 'jinjimbre', '1', NULL, '10.00', '3', '5.00', 4, '2025-05-20 17:28:23', '2025-05-20 17:28:23', '500.00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items1`
--

CREATE TABLE `inventory_items1` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity_in_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) NOT NULL,
  `reorder_level` decimal(10,2) DEFAULT 0.00,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inventory_items1`
--

INSERT INTO `inventory_items1` (`inventory_id`, `item_name`, `category`, `description`, `quantity_in_stock`, `unit`, `reorder_level`, `supplier_id`, `created_at`, `updated_at`, `unit_cost`) VALUES
(1, 'Rice', '3', '0', '990.00', '4', '5.00', 2, '2024-12-16 08:24:26', '2025-06-03 06:04:07', '1000.00'),
(2, 'Bean', '3', '0', '50.00', '4', '7.00', 2, '2024-12-16 08:26:00', '2025-05-12 14:00:44', '5000.00'),
(3, 'Meat', '3', '0', '0.00', '4', '3.00', 2, '2024-12-16 08:46:26', '2025-05-12 14:01:01', '32000.00'),
(9, 'Primus', '1', '0', '6.00', '2', '5.00', 2, '2024-12-16 09:23:25', '2025-05-12 14:01:31', '35000.00'),
(16, 'Boke', '1', '0', '0.00', '1', '2.00', 2, '2025-03-03 14:39:19', '2025-06-03 08:35:46', '3500.00'),
(20, 'Rice maora', '3', NULL, '10000.00', '1', '3.00', 2, '2025-03-30 15:14:23', '2025-05-27 13:13:14', '5000.00'),
(21, 'fish', '3', NULL, '10.00', '1', '4.00', 2, '2025-03-30 15:22:48', '2025-03-30 15:22:48', '10000.00'),
(22, 'lettuce', '3', NULL, '10.00', '3', '5.00', 2, '2025-03-30 15:26:28', '2025-03-30 15:26:28', '2000.00'),
(23, 'cheese', '3', NULL, '5.00', '1', '2.00', 2, '2025-03-30 15:29:02', '2025-03-30 15:29:02', '1000.00'),
(24, 'bread', '3', NULL, '8.00', '3', '4.00', 2, '2025-03-30 15:30:21', '2025-03-30 15:30:21', '5000.00');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_orders`
--

CREATE TABLE `kitchen_orders` (
  `kot_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `kot_status` enum('pending','cooking','ready','delivered','canceled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kitchen_orders`
--

INSERT INTO `kitchen_orders` (`kot_id`, `order_id`, `kot_status`, `created_at`) VALUES
(2, 24, 'pending', '2025-02-28 15:32:02'),
(3, 23, 'pending', '2025-03-06 11:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_order_items`
--

CREATE TABLE `kitchen_order_items` (
  `kot_item_id` int(11) NOT NULL,
  `kot_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kitchen_order_items`
--

INSERT INTO `kitchen_order_items` (`kot_item_id`, `kot_id`, `menu_id`, `quantity`) VALUES
(2, 2, 10, 1),
(3, 3, 10, 3);

-- --------------------------------------------------------

--
-- Table structure for table `loan_repayments`
--

CREATE TABLE `loan_repayments` (
  `repayment_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `payroll_id` int(11) DEFAULT NULL,
  `repayment_amount` decimal(10,2) NOT NULL,
  `repayment_date` date NOT NULL,
  `repayment_method` enum('salary_deduction','cash','other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `loan_repayments`
--

INSERT INTO `loan_repayments` (`repayment_id`, `loan_id`, `payroll_id`, `repayment_amount`, `repayment_date`, `repayment_method`) VALUES
(1, 1, NULL, '100000.00', '2025-06-09', 'cash'),
(2, 2, 30, '20000.00', '2025-06-16', 'salary_deduction'),
(3, 2, 30, '20000.00', '2025-06-16', 'salary_deduction');

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Breakfast', '', '2025-01-06 17:04:25'),
(5, 'Dinner', '', '2025-01-06 18:34:26'),
(8, 'Supper', '', '2025-02-28 08:59:02');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `menu_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `availability` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`menu_id`, `name`, `category_id`, `price`, `description`, `image`, `availability`, `created_at`) VALUES
(12, 'cafe noir', 1, '5000.00', NULL, '../Admin/assets/img/uploads/222aeff5cf807f41f0f15a58c19aaf58.png', 'available', '2025-04-09 10:24:35'),
(13, 'cafe au lait', 1, '6000.00', NULL, '../Admin/assets/img/uploads/f8dc7bcf02f2448ac8a57f105eb170de.png', 'available', '2025-04-09 10:27:15'),
(14, 'Cafe African', 1, '8000.00', NULL, '../Admin/assets/img/uploads/9272f7b129ed8038ebf946050bdd136f.png', 'available', '2025-04-09 12:23:47'),
(16, 'cafe latte', 1, '5000.00', NULL, '../Admin/assets/img/uploads/0c1b3d40cba913c2d4636598435c7756.png', 'available', '2025-04-09 12:30:08'),
(17, 'caffe latte glace', 1, '5000.00', NULL, '../Admin/assets/img/uploads/63e6af7830f391d8330279fa9982416e.png', 'available', '2025-04-09 12:33:39'),
(18, 'cafe  gigimber', 1, '7000.00', NULL, '../Admin/assets/img/uploads/5abb9938d314d5ddfcfdebda66bfbe31.png', 'available', '2025-04-09 12:38:50'),
(19, 'lait chaud/froid', 1, '3000.00', NULL, '../Admin/assets/img/uploads/37f05333b1979c46b3b195279472a460.png', 'available', '2025-04-09 12:43:24'),
(20, 'Americano', 1, '6000.00', NULL, '../Admin/assets/img/uploads/988e046f6038415f090dee5150f5f0c4.png', 'available', '2025-04-09 12:49:06'),
(21, 'Mochaccino', 1, '6000.00', NULL, '../Admin/assets/img/uploads/a0277177837cb9b07de51d220db7cd97.png', 'available', '2025-04-09 13:03:49'),
(22, 'Machiato', 1, '5000.00', NULL, '../Admin/assets/img/uploads/9ead4f0e5c0d972451dd55c540be6d9f.png', 'available', '2025-04-09 13:06:33'),
(23, 'Espresso', 1, '4000.00', NULL, '../Admin/assets/img/uploads/0b6f68e578560c7b8ee10dd7fc7a67c0.png', 'available', '2025-04-09 13:09:04'),
(24, 'black tea', 1, '4000.00', NULL, '../Admin/assets/img/uploads/c4f705f91032f518bdf65c0ae22ea585.png', 'available', '2025-04-09 13:23:21'),
(25, 'the au lait', 1, '4500.00', NULL, '../Admin/assets/img/uploads/d570ef9c94b8551e8a76305d52dac5b6.png', 'available', '2025-04-09 13:25:25'),
(26, 'the russe noir', 1, '4000.00', NULL, '../Admin/assets/img/uploads/b65aacc733cbdd457b7cbbafb4695a3e.png', 'available', '2025-04-09 13:28:26'),
(27, 'the russe au lait', 1, '4500.00', NULL, '../Admin/assets/img/uploads/2ec554e76252d9532595dcd92fbdb20d.png', 'available', '2025-04-09 13:31:45'),
(28, 'the africain', 1, '7000.00', NULL, '../Admin/assets/img/uploads/88e63e76cf0f4a60fe123ab846a52e88.png', 'available', '2025-04-09 13:36:27'),
(29, 'the gigimber', 1, '6000.00', NULL, '../Admin/assets/img/uploads/c8d3efd9f4a2bd823f0ecd5abeec2f69.png', 'available', '2025-04-09 13:38:39'),
(30, 'Dawa the', 1, '8000.00', NULL, '../Admin/assets/img/uploads/5d1f6c90a4e30d977bdeffb759f11c75.png', 'available', '2025-04-09 14:00:01'),
(31, 'the vert', 1, '5000.00', NULL, '../Admin/assets/img/uploads/ebdc50539893bdc08e0c612edf7fcb3a.png', 'available', '2025-04-09 14:03:01'),
(33, 'the vert glace', 1, '4000.00', NULL, '../Admin/assets/img/uploads/4884df0968bfc18f2c258bd12df779e1.png', 'available', '2025-04-09 14:11:26'),
(34, 'lait froid', 1, '3000.00', NULL, '../Admin/assets/img/uploads/2714b10f43d8c540ecfc73307645c928.png', 'available', '2025-04-09 14:12:51'),
(36, 'chocolat chaud', 1, '6000.00', NULL, '../Admin/assets/img/uploads/02dc88a351a64719e7b4858d8121adc9.png', 'available', '2025-04-09 14:15:20'),
(37, 'jus cocktail', 8, '8000.00', NULL, '../Admin/assets/img/uploads/2301813c27c970b4b1703d6a5a381571.png', 'available', '2025-04-09 14:28:28'),
(38, 'jus d\'ananas', 1, '5000.00', NULL, '../Admin/assets/img/uploads/91fd4fc285087500646fc9b1a9cbed66.png', 'available', '2025-04-09 14:29:39'),
(40, 'jus d\'ananas beterave carotte', 8, '8000.00', NULL, '../Admin/assets/img/uploads/a971b3ecd99785b70ac714377b376062.png', 'available', '2025-04-09 14:36:58'),
(41, 'jus d\'orange', 8, '8000.00', NULL, '../Admin/assets/img/uploads/4b8737a843a1be6e49e0aef8678c0401.png', 'available', '2025-04-09 14:40:41'),
(42, 'jus de pasteque', 8, '5000.00', NULL, '../Admin/assets/img/uploads/bb1ec2f003de294122388171038d23eb.png', 'available', '2025-04-09 14:41:56'),
(43, 'jus de mangue', 8, '12000.00', NULL, '../Admin/assets/img/uploads/7983a439fb8831f94a4eb9e5387ab1a0.png', 'available', '2025-04-09 14:45:37'),
(44, 'jus de mangue', 8, '12000.00', NULL, '../Admin/assets/img/uploads/af3c75518444c63d1ee7dc449663e9ce.png', 'available', '2025-04-09 14:45:51'),
(45, 'smothies fraise', 8, '14000.00', NULL, '../Admin/assets/img/uploads/2e4c986f04e5f3f0a5d5d6ef45a2550c.png', 'available', '2025-04-09 15:27:58'),
(46, 'Smothie fraise-banane-yaourt', 8, '14000.00', NULL, '../Admin/assets/img/uploads/d9b4748435abd6567d8d08dcbd8647a7.png', 'available', '2025-04-14 11:58:47'),
(47, 'Mocha madness', 8, '14000.00', NULL, '../Admin/assets/img/uploads/9590715edbd2775c0cc8fa416f0acdbc.png', 'available', '2025-04-14 12:06:57'),
(49, 'Cheeky monkey', 8, '14000.00', NULL, '../Admin/assets/img/uploads/9f1ecf48ff488be037ae1b8ab185b20b.png', 'available', '2025-04-14 12:14:11'),
(50, 'Maza', 8, '14000.00', NULL, '../Admin/assets/img/uploads/489ac3aa3f0bab1fa4a6d737f45625da.png', 'available', '2025-04-14 12:16:37'),
(51, 'Caramel', 8, '14000.00', NULL, '../Admin/assets/img/uploads/3d8c84451d0d3f4befb7e9a5ed2ad547.png', 'available', '2025-04-14 12:23:07'),
(52, 'MilkShake oreo', 8, '15000.00', NULL, '../Admin/assets/img/uploads/0938d8c1d5de5fe99e715bcb42d6d206.png', 'available', '2025-04-14 12:29:27'),
(53, 'MilkShake fraise', 8, '15000.00', NULL, '../Admin/assets/img/uploads/12bc209f05fe2d82f44f576f4e8d24cb.png', 'available', '2025-04-14 12:32:36'),
(54, 'MilkShake nutella', 8, '15000.00', NULL, '../Admin/assets/img/uploads/4e767015260a0d2f4f5b83ec5c496b94.png', 'available', '2025-04-14 12:35:20'),
(55, 'MilkShake mangue', 8, '15000.00', NULL, '../Admin/assets/img/uploads/17173a099412b7865572a66882155c0d.png', 'available', '2025-04-14 12:39:24'),
(57, 'MilkShake', 8, '15000.00', NULL, '../Admin/assets/img/uploads/418c5e202ae3f85337ade0ebf5ae72d6.png', 'available', '2025-04-14 12:42:19'),
(58, 'MilkShake chocolat', 8, '15000.00', NULL, '../Admin/assets/img/uploads/1861b20c9d039cf02d6eec888a5f7282.png', 'available', '2025-04-14 12:43:47'),
(59, 'Salad mixte', 1, '7000.00', NULL, '../Admin/assets/img/uploads/a1ccc92285478340f38f9447a724c1d9.png', 'available', '2025-04-14 12:51:15'),
(61, 'Salad du chef', 1, '10000.00', NULL, '../Admin/assets/img/uploads/ba7ed4319062a3e185ac07e6e06bc0e2.png', 'available', '2025-04-14 13:01:38'),
(62, 'Tomate farcie au ndagala frais', 1, '10000.00', NULL, '../Admin/assets/img/uploads/eccc564d25201f11b48233c03d17b138.png', 'available', '2025-04-14 13:06:58'),
(64, 'omelette nature', 1, '7000.00', NULL, '../Admin/assets/img/uploads/4cfff50b06c04656fb38e53e147a1f46.png', 'available', '2025-04-14 13:51:23'),
(65, 'omelette champignon', 1, '10000.00', NULL, '../Admin/assets/img/uploads/243c57b6294b331a80457eacbe65ba48.png', 'available', '2025-04-14 13:59:40'),
(66, 'omelette jambon fromage', 1, '10000.00', NULL, '../Admin/assets/img/uploads/aeb95aebeb8fb284fc11cc40a34a507a.png', 'available', '2025-04-14 14:03:10'),
(67, 'omelette jambon', 1, '9000.00', NULL, '../Admin/assets/img/uploads/3e330e3557777fdfe02174a6f3cc0cb6.png', 'available', '2025-04-14 14:06:03'),
(68, 'omelette fromage', 1, '9000.00', NULL, '../Admin/assets/img/uploads/5d68bf40240a6d5c1eafa3e74631c47d.png', 'available', '2025-04-14 14:08:31'),
(69, 'omelette oignon', 1, '9000.00', NULL, '../Admin/assets/img/uploads/7f339758d0f7a1c8bbbc8ca94283eb11.png', 'available', '2025-04-14 14:11:12'),
(70, 'omelette tomotes', 1, '9000.00', NULL, '../Admin/assets/img/uploads/6cb7cec2a0f31f874a75479f9820a776.png', 'available', '2025-04-14 14:12:43'),
(72, 'omelette espagnole', 1, '12000.00', NULL, '../Admin/assets/img/uploads/3d7d55f69d109cff768e11d9d19fadbd.png', 'available', '2025-04-14 14:30:01'),
(73, 'omelette speciale', 1, '12000.00', NULL, '../Admin/assets/img/uploads/89240f9bf1bffaef6ff9cbeb8090f2ac.png', 'available', '2025-04-14 14:34:20'),
(74, 'omelette sur plat tourne', 1, '7000.00', NULL, '../Admin/assets/img/uploads/da1b9f352c1d67718e7da3461b851a6e.png', 'available', '2025-04-14 14:35:57'),
(75, 'omelette non tourne', 1, '7000.00', NULL, '../Admin/assets/img/uploads/63fd8cba314bab807afd2604766e9131.png', 'available', '2025-04-14 14:36:42'),
(76, 'brochette de poulet', 5, '15000.00', NULL, '../Admin/assets/img/uploads/8421c465c55fdf00e49e446241f10763.png', 'available', '2025-04-15 11:00:36'),
(78, 'Brochette de saussisson', 5, '12000.00', NULL, '../Admin/assets/img/uploads/313c91882dc70411d1e1484792b1c852.png', 'available', '2025-04-15 11:04:25'),
(79, 'Brochette de chevre', 5, '15000.00', NULL, '../Admin/assets/img/uploads/1e2fce920e5b48d53fce11e7f5b94028.png', 'available', '2025-04-15 11:06:44'),
(80, 'Gigot de chevre', 5, '30000.00', NULL, '../Admin/assets/img/uploads/c20de1963b358a5819236af522777d6d.png', 'available', '2025-04-15 11:09:02'),
(81, 'Steak grille', 5, '20000.00', NULL, '../Admin/assets/img/uploads/56e289f89236103150c2051a06d5ea34.png', 'available', '2025-04-15 11:11:18'),
(82, 'steak poile', 5, '20000.00', NULL, '../Admin/assets/img/uploads/278b6d21296c66e77f261158141479bf.png', 'available', '2025-04-15 11:17:26'),
(85, 'Steak marchand de vin', 5, '25000.00', NULL, '../Admin/assets/img/uploads/3d074e0ef9fe574ab1bd77677d8217be.png', 'available', '2025-04-15 11:37:16'),
(86, 'Twatundi', 5, '15000.00', NULL, '../Admin/assets/img/uploads/b5b734052f694514f41aeca447d64951.png', 'available', '2025-04-15 11:41:56'),
(87, 'ragout grille', 5, '25000.00', NULL, '../Admin/assets/img/uploads/c06573a013685ec497467059f56d39bb.png', 'available', '2025-04-15 11:44:34'),
(88, 'ragout roti', 5, '25000.00', NULL, '../Admin/assets/img/uploads/2123907f685dc69923cb55e0710b95ac.png', 'available', '2025-04-15 11:46:35'),
(89, 'jaret grille', 5, '25000.00', NULL, '../Admin/assets/img/uploads/9bf429947cc7a255f7cd15be68ba2296.png', 'available', '2025-04-15 11:50:11'),
(90, 'jaret roti', 5, '25000.00', NULL, '../Admin/assets/img/uploads/251f528d5398ce2f40ad3e564ecf9e3b.png', 'available', '2025-04-15 11:52:12'),
(91, 'filet de boeuf au poivre concasse', 5, '25000.00', NULL, '../Admin/assets/img/uploads/8d2a505c80f5d3760ebf097f4195c5fb.png', 'available', '2025-04-15 11:56:30'),
(92, 'Escalope cordon bleu', 5, '25000.00', NULL, '../Admin/assets/img/uploads/6b383810bb86180a512b8891c32c166b.png', 'available', '2025-04-15 12:00:18'),
(93, 'sandwich vide', 1, '3000.00', NULL, '../Admin/assets/img/uploads/304128a253b38250825fb49d599621d0.png', 'available', '2025-04-16 10:27:17'),
(94, 'croissant vide', 1, '3000.00', NULL, '../Admin/assets/img/uploads/25584ac6951a8c27b82db14933ab43c8.png', 'available', '2025-04-16 10:31:18'),
(95, 'club sandwich', 1, '12000.00', NULL, '../Admin/assets/img/uploads/78fff20f255e686018c6d3175b8a9ed2.png', 'available', '2025-04-16 10:35:19'),
(96, 'sandwich au poulet', 1, '12000.00', NULL, '../Admin/assets/img/uploads/3b35d04ba9316a900f59e8c902e064e3.png', 'available', '2025-04-16 10:42:15'),
(97, 'sandwich americain', 1, '12000.00', NULL, '../Admin/assets/img/uploads/4ef454e392d29712b8a1350976e7e4f3.png', 'available', '2025-04-16 10:46:30'),
(98, 'sandwich jambon fromage', 1, '8000.00', NULL, '../Admin/assets/img/uploads/2053bc27be5baaa2c3b1aa1e86adb786.png', 'available', '2025-04-16 10:48:22'),
(99, 'croque monsieur', 1, '6000.00', NULL, '../Admin/assets/img/uploads/662e732422013f388c53ddf48aa311c1.png', 'available', '2025-04-16 10:49:29'),
(100, 'croque madame', 1, '8000.00', NULL, '../Admin/assets/img/uploads/352445bf566d06fb1356e4eb8c5f5649.png', 'available', '2025-04-16 10:51:06'),
(101, 'pain toaste', 1, '2500.00', NULL, '../Admin/assets/img/uploads/c92460f7f9ade4f1e540af7213cf1868.png', 'available', '2025-04-16 10:51:59'),
(102, 'rolex chicken', 1, '15000.00', NULL, '../Admin/assets/img/uploads/cb6efb9bf493039666e79c600993103f.png', 'available', '2025-04-16 10:56:35'),
(103, 'rolex boeuf', 5, '12000.00', NULL, '../Admin/assets/img/uploads/2416ed4c496da5edc7c17150e6f597d9.png', 'available', '2025-04-16 11:00:06'),
(104, 'Hamburger boeuf', 5, '10000.00', NULL, '../Admin/assets/img/uploads/5c3b1c9e4988cb6f24bcdf1a0b31c373.png', 'available', '2025-04-16 11:03:29'),
(105, 'hamburger poulet', 5, '12000.00', NULL, '../Admin/assets/img/uploads/2bf46a9a92bdac3dde88ae13576b236e.png', 'available', '2025-04-16 11:07:02'),
(106, 'cheese burger', 5, '12000.00', NULL, '../Admin/assets/img/uploads/936ce22613fd4ad8674e3b4413479f1e.png', 'available', '2025-04-16 11:09:18'),
(107, 'crepe nature', 1, '6000.00', NULL, '../Admin/assets/img/uploads/7bc855aa636de9ebd4f1e2d868f8c138.png', 'available', '2025-04-16 11:11:36'),
(108, 'crepe au chocolat', 1, '8000.00', NULL, '../Admin/assets/img/uploads/c1996519fab4b6d4f05f9395e731577b.png', 'available', '2025-04-16 11:15:49'),
(109, 'crepe au miel', 1, '8000.00', NULL, '../Admin/assets/img/uploads/63737b16236ee0deaff24f7547e08496.png', 'available', '2025-04-16 11:18:28'),
(110, 'salad de fruit', 1, '8000.00', NULL, '../Admin/assets/img/uploads/e41cb88b5b7f0f7f6c087005a690ff93.png', 'available', '2025-04-16 11:22:04'),
(111, 'fruit de saison', 1, '10000.00', NULL, '../Admin/assets/img/uploads/f860392d9912f70b2bdf5a93245b3a56.png', 'available', '2025-04-16 11:22:51'),
(112, '1/4 de poult grille', 5, '25000.00', NULL, '../Admin/assets/img/uploads/e722cf3ad699fd34b4914bd115bfd588.png', 'available', '2025-04-16 11:26:28'),
(113, '1/4 de poult grille', 5, '25000.00', NULL, '../Admin/assets/img/uploads/73e25a7cfb09964b2a1283a8479df460.png', 'available', '2025-04-16 11:27:03'),
(114, '1/4 de poulet roti', 5, '25000.00', NULL, '../Admin/assets/img/uploads/e19708251c41b3dbf9c90931735197e4.png', 'available', '2025-04-16 11:27:35'),
(115, 'emince de poulet', 5, '30000.00', NULL, '../Admin/assets/img/uploads/3736fb2d887ebe1911da66e667e9b15f.png', 'available', '2025-04-16 11:29:12'),
(116, 'emince de poulet aux champignons', 5, '32000.00', NULL, '../Admin/assets/img/uploads/bf0d8ea1acb722e85dcfcf479879a050.png', 'available', '2025-04-16 11:30:51'),
(118, 'steak de poulet poiles', 5, '3000.00', NULL, '../Admin/assets/img/uploads/a6454fd92df7aceb934aa83df7ab8f29.png', 'available', '2025-04-16 11:32:44'),
(119, 'cuhe grille', 5, '50000.00', NULL, '../Admin/assets/img/uploads/7e1446b2d6842cd4b8358017efee0281.png', 'available', '2025-04-16 11:40:53'),
(120, 'cuhe crille aux oignons', 5, '52000.00', NULL, '../Admin/assets/img/uploads/084833feb507bdb760a88632798bd13f.png', 'available', '2025-04-16 11:41:59'),
(121, 'mukeke crille', 5, '40000.00', NULL, '../Admin/assets/img/uploads/1cc4366e924007f7b572ade8876afbc7.png', 'available', '2025-04-16 11:43:40'),
(122, 'mukeke crille aux oignons', 5, '40000.00', NULL, '../Admin/assets/img/uploads/9050cbefb844134227ece4f1eb49e3e0.png', 'available', '2025-04-16 11:44:22'),
(123, 'sangagala grille', 5, '30000.00', NULL, '../Admin/assets/img/uploads/b0af64c8967cbc9a1cdc73bef9504169.png', 'available', '2025-04-16 11:46:16'),
(124, 'sangagala meuniere', 5, '32000.00', NULL, '../Admin/assets/img/uploads/c222c0e95f70cce4a59d29f94159b5c7.png', 'available', '2025-04-16 11:47:11'),
(125, 'sangagala champignon creme', 5, '35000.00', NULL, '../Admin/assets/img/uploads/d5bf7c43fa261596bc7430b24089569e.png', 'available', '2025-04-16 11:48:57'),
(126, 'sangagala bonne femme', 5, '35000.00', NULL, '../Admin/assets/img/uploads/e716dda519e7a4dea022684a873ea731.png', 'available', '2025-04-16 11:51:43'),
(127, 'sangagala gratine aux epinards', 5, '45000.00', NULL, '../Admin/assets/img/uploads/e8e8fe42ad74353288ee547d81ee0129.png', 'available', '2025-04-16 11:54:35'),
(128, 'mukeke desosse aux oignons', 5, '40000.00', NULL, '../Admin/assets/img/uploads/5971373582a524d177fbff87a990932c.png', 'available', '2025-04-16 11:57:21'),
(129, 'brochette de poisson', 5, '32000.00', NULL, '../Admin/assets/img/uploads/593ad6b2d794448d8e3a4c462f6793a5.png', 'available', '2025-04-16 11:58:38'),
(130, 'assiette de courgette carotte', 5, '5000.00', NULL, '../Admin/assets/img/uploads/f38c3ab402c30010e2bc0d937d8e3e5f.png', 'available', '2025-04-16 12:00:07'),
(131, 'assiette de riz', 5, '5000.00', NULL, '../Admin/assets/img/uploads/6511859a562f4d1f941fc35a6fd39734.png', 'available', '2025-04-16 12:00:40'),
(132, 'assiette de frite banane', 5, '5000.00', NULL, '../Admin/assets/img/uploads/722cbdba3e8f50c5913319878453e9c9.png', 'available', '2025-04-16 12:01:35'),
(133, 'assiette de frite pomme de terre', 5, '5000.00', NULL, '../Admin/assets/img/uploads/b817e3c626c0f8dd3b7437601e360a78.png', 'available', '2025-04-16 12:02:20'),
(134, 'assiette de haricot vert', 5, '5000.00', NULL, '../Admin/assets/img/uploads/0d32cc1b86631489135fad662be9109d.png', 'available', '2025-04-16 12:03:29'),
(135, 'assiette de pomme de terre nature', 5, '5000.00', NULL, '../Admin/assets/img/uploads/c8ad8a82cb23364a6de21beabc10dcb3.png', 'available', '2025-04-16 12:04:10'),
(136, 'assiette de fritte de salade', 5, '5000.00', NULL, '../Admin/assets/img/uploads/a4d2580263d45ff800702094586381f5.png', 'available', '2025-04-16 12:05:35'),
(137, 'pate de manioc', 5, '4000.00', NULL, '../Admin/assets/img/uploads/0af84b17a579d037ceec657b6f863553.png', 'available', '2025-04-16 12:08:01'),
(138, 'pate de mais', 5, '4000.00', NULL, '../Admin/assets/img/uploads/85b69d6707d22ef0f7fc9bbf032af685.png', 'available', '2025-04-16 12:08:44'),
(139, 'pate de ble', 5, '5000.00', NULL, '../Admin/assets/img/uploads/841e1f47b36cc989a85a2c67b296b963.png', 'available', '2025-04-16 12:09:32'),
(140, 'Sambusaa', 1, '8000.00', NULL, '../Admin/assets/img/uploads/0f6d91547e898e95843070a8d52450e4.png', 'available', '2025-04-17 13:47:27'),
(141, 'saussion', 1, '10000.00', NULL, '../Admin/assets/img/uploads/a80b33d2cf1f39d61e4637e538d39fca.png', 'available', '2025-04-17 13:49:23'),
(142, 'fromage', 1, '10000.00', NULL, '../Admin/assets/img/uploads/146c616a82d99792742fcf7c7e688e52.png', 'available', '2025-04-17 13:50:05'),
(143, 'Ndagala aux oignons', 1, '10000.00', NULL, '../Admin/assets/img/uploads/e081c3623e1955fea25d4757c8fe6858.png', 'available', '2025-04-17 13:51:18'),
(144, 'pizza mafiozo', 5, '25000.00', NULL, '../Admin/assets/img/uploads/199aa61a5ace3b26b0396cb0f3f6d9cd.png', 'available', '2025-04-17 13:58:35'),
(145, 'pizza au poulet', 5, '28000.00', NULL, '../Admin/assets/img/uploads/47f31d10014579e295b9762e9ee0e07e.png', 'available', '2025-04-17 13:59:51'),
(146, 'pizza hawaienne', 5, '25000.00', NULL, '../Admin/assets/img/uploads/3ce88c0694cb9d31004787a40df1831a.png', 'available', '2025-04-17 14:00:59'),
(147, 'pizza quatre saison', 5, '30000.00', NULL, '../Admin/assets/img/uploads/2c5419b3bbf31092aaa719e6ecc97a42.png', 'available', '2025-04-17 14:02:41'),
(148, 'spaghetti bolognaise', 5, '15000.00', NULL, '../Admin/assets/img/uploads/251e80ff4ab30e87451ad462d8f5d5b9.png', 'available', '2025-04-17 14:04:38'),
(149, 'Jus d\'ananas jinjimbre', 1, '8000.00', NULL, '../Admin/assets/img/uploads/1379e59042546a23b0d44efc86833b67.png', 'available', '2025-05-20 17:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items1`
--

CREATE TABLE `menu_items1` (
  `menu_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `availability` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_items1`
--

INSERT INTO `menu_items1` (`menu_id`, `name`, `category_id`, `price`, `description`, `image`, `availability`, `created_at`) VALUES
(10, 'Riz', 5, '5000.00', NULL, '../Admin/assets/img/uploads/7e67c2e245832a6fe7aae06871c2d9bc.jpeg', 'available', '2025-02-28 14:40:44'),
(11, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/16a7e62f91d991685591ffb538476633.jpeg', 'available', '2025-03-30 15:32:12'),
(12, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/a3b68e2988ce89d2a4a9072415e0bbec.jpeg', 'available', '2025-03-30 15:32:35'),
(13, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/e2246c6aeb765303e5f8fc4374e72bce.jpeg', 'available', '2025-03-30 15:32:46'),
(14, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/fd9900aebae91475aafecc6fd6d60c06.jpeg', 'available', '2025-03-30 15:32:46'),
(15, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/9e3d4be103185465fcd8a55e8d49e5bd.jpeg', 'available', '2025-03-30 15:33:04'),
(16, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/3ca2b30fb59160c793ca2eedcc612cac.jpeg', 'available', '2025-03-30 15:33:04'),
(17, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/9d52003c7518b90d849fcd8b9e5a2282.jpeg', 'available', '2025-03-30 15:33:04'),
(18, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/215065493c5210fedb611d6d03dc1c3d.jpeg', 'available', '2025-03-30 15:33:17'),
(19, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/215065493c5210fedb611d6d03dc1c3d.jpeg', 'available', '2025-03-30 15:33:17'),
(20, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/215065493c5210fedb611d6d03dc1c3d.jpeg', 'available', '2025-03-30 15:33:17'),
(21, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/724c7595712297946d0c76f2b4005125.jpeg', 'available', '2025-03-30 15:33:18'),
(22, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/724c7595712297946d0c76f2b4005125.jpeg', 'available', '2025-03-30 15:33:18'),
(23, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/724c7595712297946d0c76f2b4005125.jpeg', 'available', '2025-03-30 15:33:18'),
(24, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/724c7595712297946d0c76f2b4005125.jpeg', 'available', '2025-03-30 15:33:18'),
(25, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/724c7595712297946d0c76f2b4005125.jpeg', 'available', '2025-03-30 15:33:18'),
(26, 'cheese burger', 5, '16000.00', NULL, '../Admin/assets/img/uploads/4b3e3b67618f2cd70be39e3b01cc1178.png', 'available', '2025-03-30 15:34:16'),
(27, 'Test', 1, '10000.00', NULL, '../Admin/assets/img/uploads/b05608d9122f73b2b165717027427d2c.png', 'available', '2025-04-01 12:38:08'),
(28, 'Test', 1, '10000.00', NULL, '../Admin/assets/img/uploads/0034c38c525640b9df0e06b24ed46410.png', 'available', '2025-04-01 12:47:48'),
(29, 'test2', 1, '1000.00', NULL, '../Admin/assets/img/uploads/2e1eb0558ece2e6e7a03343530ecd381.png', 'available', '2025-04-01 12:48:46'),
(30, 'test2', 1, '1000.00', NULL, '../Admin/assets/img/uploads/e0445d8e5f9eaef88fb548943c4df7b6.png', 'available', '2025-04-01 12:49:37'),
(31, 'test2', 1, '1000.00', NULL, '../Admin/assets/img/uploads/d7ced22931ffc763ce211c1007119b73.png', 'available', '2025-04-01 12:53:30'),
(32, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/d3adba95181a3b5248faa412fe9ea7c8.png', 'available', '2025-04-01 12:54:41'),
(33, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/b882bc8aeee9ffc402d0d75a017314b2.png', 'available', '2025-04-01 12:56:14'),
(34, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/c9e35a923217d8aa49e330e0e576303d.png', 'available', '2025-04-01 12:56:30'),
(35, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/73dbb8b4ffbd66d79c710f44b5aaea3c.png', 'available', '2025-04-01 12:56:35'),
(36, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/73dbb8b4ffbd66d79c710f44b5aaea3c.png', 'available', '2025-04-01 12:56:35'),
(37, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/08641859c0ba26ddba6a902893722423.png', 'available', '2025-04-01 12:56:36'),
(38, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/fd95ad2d75f49c1aaba8cf71d4693ac4.png', 'available', '2025-04-01 12:56:39'),
(39, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/fd95ad2d75f49c1aaba8cf71d4693ac4.png', 'available', '2025-04-01 12:56:39'),
(40, 'test2', 1, '10000.00', NULL, '../Admin/assets/img/uploads/fd95ad2d75f49c1aaba8cf71d4693ac4.png', 'available', '2025-04-01 12:56:39'),
(41, 'test2', 5, '10000.00', NULL, '../Admin/assets/img/uploads/b09874f455ebb2a883f7fa7bdd2cf0bb.png', 'available', '2025-04-01 12:58:33'),
(42, 'test', 1, '10000.00', NULL, '../Admin/assets/img/uploads/001fe79ff33844e484ba03b94d6cd304.png', 'available', '2025-04-01 15:16:59');

-- --------------------------------------------------------

--
-- Table structure for table `menu_sales`
--

CREATE TABLE `menu_sales` (
  `sale_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','canceled') DEFAULT 'active',
  `cancellation_reason` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_sales`
--

INSERT INTO `menu_sales` (`sale_id`, `menu_id`, `quantity_sold`, `sale_price`, `sale_date`, `status`, `cancellation_reason`, `updated_at`) VALUES
(2, 10, 1, '5000.00', '2025-05-27 13:16:37', 'active', NULL, '2025-05-27 13:16:37'),
(3, 10, 1, '5000.00', '2025-05-27 13:58:20', 'canceled', 'test', '2025-06-03 06:04:07'),
(4, 10, 2, '10000.00', '2025-05-27 14:30:29', 'canceled', 'error', '2025-06-03 05:58:58'),
(5, 10, 2, '10000.00', '2025-05-27 14:30:45', 'canceled', 'change', '2025-06-03 05:56:06'),
(8, 12, 2, '10000.00', '2025-06-03 13:09:12', 'active', NULL, '2025-06-03 13:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `menu_stock_items`
--

CREATE TABLE `menu_stock_items` (
  `id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `stock_item_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_stock_items`
--

INSERT INTO `menu_stock_items` (`id`, `menu_id`, `stock_item_id`, `quantity_used`, `created_at`) VALUES
(22, 10, 1, '1.00', '2025-03-04 14:13:29'),
(23, 11, 1, '1.00', '2025-03-30 15:32:12'),
(24, 11, 1, '1.00', '2025-03-30 15:32:12'),
(25, 11, 1, '1.00', '2025-03-30 15:32:12'),
(26, 11, 1, '1.00', '2025-03-30 15:32:12'),
(28, 13, 1, '1.00', '2025-03-30 15:32:50'),
(30, 14, 1, '1.00', '2025-03-30 15:32:52'),
(31, 13, 1, '1.00', '2025-03-30 15:33:08'),
(32, 14, 1, '1.00', '2025-03-30 15:33:11'),
(33, 15, 1, '1.00', '2025-03-30 15:33:17'),
(34, 17, 1, '1.00', '2025-03-30 15:33:17'),
(35, 16, 1, '1.00', '2025-03-30 15:33:17'),
(36, 18, 1, '1.00', '2025-03-30 15:33:17'),
(37, 15, 1, '1.00', '2025-03-30 15:33:17'),
(38, 16, 1, '1.00', '2025-03-30 15:33:17'),
(39, 17, 1, '1.00', '2025-03-30 15:33:17'),
(40, 19, 1, '1.00', '2025-03-30 15:33:17'),
(41, 15, 1, '1.00', '2025-03-30 15:33:17'),
(42, 16, 1, '1.00', '2025-03-30 15:33:17'),
(43, 17, 1, '1.00', '2025-03-30 15:33:17'),
(44, 18, 1, '1.00', '2025-03-30 15:33:17'),
(45, 20, 1, '1.00', '2025-03-30 15:33:17'),
(46, 19, 1, '1.00', '2025-03-30 15:33:17'),
(47, 20, 1, '1.00', '2025-03-30 15:33:17'),
(48, 16, 1, '1.00', '2025-03-30 15:33:17'),
(49, 17, 1, '1.00', '2025-03-30 15:33:17'),
(50, 20, 1, '1.00', '2025-03-30 15:33:17'),
(51, 15, 1, '1.00', '2025-03-30 15:33:18'),
(52, 20, 1, '1.00', '2025-03-30 15:33:18'),
(53, 21, 1, '1.00', '2025-03-30 15:33:18'),
(54, 19, 1, '1.00', '2025-03-30 15:33:18'),
(55, 18, 1, '1.00', '2025-03-30 15:33:18'),
(56, 18, 1, '1.00', '2025-03-30 15:33:18'),
(57, 21, 1, '1.00', '2025-03-30 15:33:18'),
(58, 22, 1, '1.00', '2025-03-30 15:33:18'),
(59, 19, 1, '1.00', '2025-03-30 15:33:18'),
(60, 21, 1, '1.00', '2025-03-30 15:33:18'),
(61, 23, 1, '1.00', '2025-03-30 15:33:18'),
(62, 24, 1, '1.00', '2025-03-30 15:33:18'),
(63, 22, 1, '1.00', '2025-03-30 15:33:18'),
(64, 21, 1, '1.00', '2025-03-30 15:33:18'),
(65, 24, 1, '1.00', '2025-03-30 15:33:18'),
(66, 23, 1, '1.00', '2025-03-30 15:33:18'),
(67, 22, 1, '1.00', '2025-03-30 15:33:18'),
(68, 23, 1, '1.00', '2025-03-30 15:33:18'),
(69, 24, 1, '1.00', '2025-03-30 15:33:18'),
(70, 25, 1, '1.00', '2025-03-30 15:33:18'),
(71, 22, 1, '1.00', '2025-03-30 15:33:18'),
(72, 23, 1, '1.00', '2025-03-30 15:33:18'),
(73, 25, 1, '1.00', '2025-03-30 15:33:18'),
(74, 24, 1, '1.00', '2025-03-30 15:33:18'),
(75, 25, 1, '1.00', '2025-03-30 15:33:18'),
(76, 25, 1, '1.00', '2025-03-30 15:33:19'),
(77, 26, 1, '1.00', '2025-03-30 15:34:20'),
(78, 26, 1, '1.00', '2025-03-30 15:34:21'),
(79, 27, 1, '1.00', '2025-04-01 12:38:08'),
(80, 28, 1, '1.00', '2025-04-01 12:47:49'),
(81, 29, 1, '1.00', '2025-04-01 12:48:48'),
(82, 30, 1, '1.00', '2025-04-01 12:49:39'),
(83, 31, 1, '1.00', '2025-04-01 12:53:30'),
(84, 32, 1, '1.00', '2025-04-01 12:54:42'),
(85, 33, 1, '1.00', '2025-04-01 12:56:17'),
(86, 34, 1, '1.00', '2025-04-01 12:56:30'),
(87, 35, 1, '1.00', '2025-04-01 12:56:36'),
(88, 36, 1, '1.00', '2025-04-01 12:56:36'),
(89, 37, 1, '1.00', '2025-04-01 12:56:36'),
(90, 38, 1, '1.00', '2025-04-01 12:56:39'),
(91, 39, 1, '1.00', '2025-04-01 12:56:39'),
(92, 40, 1, '1.00', '2025-04-01 12:56:39'),
(93, 41, 1, '1.00', '2025-04-01 12:58:33'),
(94, 42, 1, '1.00', '2025-04-01 15:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','canceled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `total_price`, `order_date`, `status`) VALUES
(4, 15, '19000.00', '2025-01-14 19:49:33', 'confirmed'),
(5, 8, '10000.00', '2025-02-11 15:05:29', 'confirmed'),
(6, 14, '20000.00', '2025-02-11 15:06:54', 'canceled'),
(7, 15, '5000.00', '2025-02-11 15:11:35', 'confirmed'),
(8, 8, '10000.00', '2025-02-11 15:15:52', 'canceled'),
(17, 19, '30000.00', '2025-02-21 00:02:04', 'pending'),
(18, 19, '5500.00', '2025-02-21 00:38:27', 'pending'),
(19, 19, '8000.00', '2025-02-21 00:39:36', 'pending'),
(22, 22, '15000.00', '2025-02-28 13:42:28', 'pending'),
(23, 22, '15000.00', '2025-02-28 13:42:28', 'confirmed'),
(24, 22, '5000.00', '2025-02-28 15:29:36', 'confirmed'),
(26, 19, '5000.00', '2025-03-27 11:45:35', 'pending'),
(27, 8, '50000.00', '2025-06-17 18:42:40', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `menu_id`, `quantity`, `price`) VALUES
(24, 22, 10, 3, '5000.00'),
(25, 23, 10, 3, '5000.00'),
(26, 24, 10, 1, '5000.00'),
(28, 26, 10, 1, '5000.00'),
(29, 27, 12, 10, '5000.00');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_records`
--

CREATE TABLE `payroll_records` (
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `gross_pay` decimal(10,2) NOT NULL,
  `bonus` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `loan_repayment` decimal(10,2) DEFAULT 0.00,
  `loan_repayment_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payroll_records`
--

INSERT INTO `payroll_records` (`payroll_id`, `employee_id`, `pay_period_start`, `pay_period_end`, `gross_pay`, `bonus`, `deductions`, `net_pay`, `payment_date`, `notes`, `loan_repayment`, `loan_repayment_updated_at`) VALUES
(25, 61, '2025-03-31', '2025-04-29', '1000000.00', '0.00', '0.00', '1000000.00', '2025-05-15', '', '0.00', '2025-06-17 13:42:43'),
(29, 61, '2025-04-30', '2025-05-30', '1000000.00', '0.00', '100000.00', '900000.00', '2025-06-03', '', '0.00', '2025-06-17 13:43:13'),
(30, 3, '2025-04-30', '2025-05-30', '600000.00', '0.00', '0.00', '580000.00', '2025-06-16', '', '20000.00', '2025-06-17 13:44:07');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `purchase_order_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `po_item_id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`po_item_id`, `purchase_order_id`, `inventory_id`, `quantity`, `unit_price`) VALUES
(45, 45, 24, '10.00', '3450.00'),
(49, 49, 24, '10.00', '3450.00'),
(57, 57, 24, '10.00', '3450.00'),
(59, 59, 24, '10.00', '3450.00');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_records`
--

CREATE TABLE `purchase_records` (
  `purchase_id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `order_date` datetime NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `purchase_records`
--

INSERT INTO `purchase_records` (`purchase_id`, `po_number`, `order_date`, `payment_method`, `created_by`, `total`) VALUES
(45, 'PO-20250603-683ef9bc85a5e', '2025-06-17 18:03:02', 'cash', 'Boss Boss', '109800.00'),
(49, 'PO-20250603-683f20acd2992', '2025-06-17 18:09:29', 'cash', 'Boss Boss', '34500.00'),
(57, 'PO-20250603-683f2dc85da39', '2025-06-03 19:15:52', 'EspÃ¨ces', 'Boss Boss', '34500.00'),
(59, 'PO-20250617-6851930cb6147', '2025-06-17 18:14:22', 'cash', 'Boss Boss', '40500.00');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `guests` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `customer_name`, `email`, `phone`, `reservation_date`, `reservation_time`, `guests`, `status`, `created_at`) VALUES
(13, 'Itangishaka John Esterique', 'itangishakajohnesterique@gmail.com', '68072105', '2025-02-28', '05:06:00', 1, 'Pending', '2025-02-28 15:06:15'),
(14, 'Itangishaka John Esterique', 'itangishakajohnesterique@gmail.com', '68072105', '2025-05-06', '21:42:00', 3, 'Pending', '2025-05-06 19:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `status` enum('Absent','Present','DayOff','Ill','Justified') NOT NULL,
  `work_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `employee_id`, `shift_id`, `status`, `work_date`) VALUES
(4, 61, 1, 'Present', '2025-05-19'),
(5, 61, 2, 'Present', '2025-05-20'),
(6, 61, 2, 'Present', '2025-05-21'),
(7, 61, 1, 'Present', '2025-05-20'),
(8, 61, 1, 'Present', '2025-05-21'),
(9, 61, 1, 'Present', '2025-05-22'),
(10, 61, 1, 'Present', '2025-05-23'),
(11, 61, 2, 'Present', '2025-05-22'),
(13, 61, 2, 'Present', '2025-06-04'),
(15, 3, 1, 'Present', '2025-06-04'),
(19, 61, 3, 'Present', '2025-06-03');

-- --------------------------------------------------------

--
-- Table structure for table `shift_templates`
--

CREATE TABLE `shift_templates` (
  `shift_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_period` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `shift_templates`
--

INSERT INTO `shift_templates` (`shift_id`, `name`, `start_time`, `end_time`, `grace_period`) VALUES
(1, 'Day', '07:30:00', '14:00:00', 15),
(2, 'After noon', '14:00:00', '18:00:00', 15),
(3, 'Night', '18:00:00', '23:59:00', 15);

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `adjustment_id` int(11) NOT NULL,
  `type` enum('Add','Reduce') NOT NULL,
  `reason` varchar(255) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`adjustment_id`, `type`, `reason`, `supplier_id`, `notes`, `user_id`, `created_at`) VALUES
(1, 'Add', 'Received', 1, '', 1, '2024-12-17 08:55:36'),
(2, 'Add', 'Received', 1, '', 1, '2024-12-17 08:56:06'),
(3, 'Reduce', 'Expired', 1, '', 1, '2024-12-17 09:09:26'),
(4, 'Add', 'Correction', 1, '', 1, '2024-12-17 09:11:01'),
(7, 'Add', 'Received', 1, '', 1, '2024-12-17 09:16:38'),
(8, 'Add', 'Received', 1, '', 1, '2024-12-17 09:17:51'),
(9, 'Add', 'Received', 1, '', 1, '2024-12-17 09:24:15'),
(10, 'Add', 'Received', 2, '', 1, '2025-02-18 13:09:43'),
(11, 'Reduce', 'Expired', 2, '', 1, '2025-02-18 13:11:11'),
(12, 'Add', 'Received', 1, '', 1, '2025-02-25 16:00:45'),
(13, 'Reduce', 'Used', 2, '', 1, '2025-02-25 16:01:43'),
(14, 'Reduce', 'Used', 1, '', 1, '2025-02-27 14:55:57'),
(15, 'Add', 'Received', 1, '', 1, '2025-02-27 14:58:56'),
(16, 'Add', 'Received', 1, 'test', 1, '2025-02-28 09:02:19'),
(17, 'Add', 'Received', 1, '', 1, '2025-03-04 14:56:31'),
(18, 'Add', 'Received', 1, '', 1, '2025-03-04 15:07:40'),
(19, 'Add', 'Received', 1, '', 1, '2025-03-04 17:42:38'),
(20, 'Add', 'Received', 1, '', 1, '2025-03-07 11:57:55'),
(21, 'Add', 'Received', 1, '', 1, '2025-03-27 11:39:30'),
(22, 'Reduce', 'Used', 1, '', 1, '2025-03-30 15:17:47'),
(23, 'Add', 'Received', 1, '', 1, '2025-05-06 14:37:43'),
(24, 'Add', 'Received', 1, '', 1, '2025-05-06 14:38:02'),
(25, 'Add', 'Received', 1, '', 1, '2025-05-06 16:32:09'),
(26, 'Add', 'Received', 1, '', 1, '2025-05-06 16:32:53'),
(27, 'Add', 'Received', 1, '', 1, '2025-05-27 13:12:44'),
(28, 'Add', 'Received', 1, '', 1, '2025-05-27 13:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_items`
--

CREATE TABLE `stock_adjustment_items` (
  `adjustment_item_id` int(11) NOT NULL,
  `adjustment_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity_before` decimal(10,2) NOT NULL,
  `quantity_change` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `quantity_after` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `stock_adjustment_items`
--

INSERT INTO `stock_adjustment_items` (`adjustment_item_id`, `adjustment_id`, `inventory_id`, `quantity_before`, `quantity_change`, `unit_cost`, `quantity_after`, `created_at`) VALUES
(1, 1, 1, '0.00', '10.00', '1000.00', '10.00', '2024-12-17 08:55:36'),
(2, 2, 1, '10.00', '10.00', '1000.00', '20.00', '2024-12-17 08:56:07'),
(3, 3, 1, '20.00', '100.00', '1000.00', '-80.00', '2024-12-17 09:09:26'),
(4, 4, 1, '-80.00', '80.00', '1000.00', '0.00', '2024-12-17 09:11:01'),
(5, 7, 2, '0.00', '10.00', '0.00', '10.00', '2024-12-17 09:16:38'),
(6, 8, 1, '0.00', '10.00', '1000.00', '10.00', '2024-12-17 09:17:52'),
(7, 9, 1, '10.00', '20.00', '1000.00', '30.00', '2024-12-17 09:24:15'),
(8, 9, 3, '0.00', '2.00', '0.00', '2.00', '2024-12-17 09:24:15'),
(9, 9, 2, '10.00', '30.00', '0.00', '40.00', '2024-12-17 09:24:15'),
(10, 10, 3, '2.00', '10.00', '32000.00', '12.00', '2025-02-18 13:09:43'),
(11, 11, 3, '12.00', '10.00', '32000.00', '2.00', '2025-02-18 13:11:12'),
(12, 12, 1, '30.00', '10.00', '1000.00', '40.00', '2025-02-25 16:00:45'),
(13, 13, 1, '40.00', '20.00', '1000.00', '20.00', '2025-02-25 16:01:43'),
(14, 14, 2, '40.00', '10.00', '5000.00', '30.00', '2025-02-27 14:55:57'),
(15, 15, 2, '30.00', '20.00', '5000.00', '50.00', '2025-02-27 14:58:57'),
(16, 16, 1, '20.00', '10.00', '1000.00', '30.00', '2025-02-28 09:02:19'),
(17, 17, 1, '-1.00', '11.00', '1000.00', '10.00', '2025-03-04 14:56:31'),
(18, 18, 1, '0.00', '10.00', '1000.00', '10.00', '2025-03-04 15:07:40'),
(19, 19, 2, '0.00', '50.00', '5000.00', '50.00', '2025-03-04 17:42:38'),
(20, 20, 1, '0.00', '10.00', '1000.00', '10.00', '2025-03-07 11:57:55'),
(21, 21, 1, '0.00', '10.00', '1000.00', '10.00', '2025-03-27 11:39:31'),
(22, 22, 20, '10.00', '8.00', '5000.00', '2.00', '2025-03-30 15:17:47'),
(23, 23, 1, '0.00', '14.00', '1000.00', '14.00', '2025-05-06 14:37:43'),
(24, 24, 1, '14.00', '10.00', '1000.00', '24.00', '2025-05-06 14:38:03'),
(25, 25, 16, '0.00', '10.00', '3500.00', '10.00', '2025-05-06 16:32:09'),
(26, 26, 9, '0.00', '10.00', '35000.00', '10.00', '2025-05-06 16:32:53'),
(27, 27, 1, '0.00', '1000.00', '1000.00', '1000.00', '2025-05-27 13:12:44'),
(28, 28, 20, '0.00', '10000.00', '5000.00', '10000.00', '2025-05-27 13:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'john', 'Itangishaka John Esterique', '68072105', 'itangishakajohnesterique@gmail.com', 'Av. Ntare Rugamba,kirundo', '2024-12-15 14:10:24'),
(2, 'HAKIZIMANA', 'HAKIZIMANA', '65291874', 'hake@gmail.com', 'test1', '2025-02-18 11:44:34');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `unit_name`) VALUES
(4, 'g'),
(1, 'Kg'),
(5, 'kkk'),
(2, 'L'),
(3, 'Piece');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserId` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `privilege` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserId`, `firstname`, `lastname`, `email`, `password`, `pin_code`, `privilege`, `image`, `reg_date`) VALUES
(1, 'Itangishaka', 'John Esterique', 'itangishakajohnesterique@gmail.com', '$2y$10$ftoNvadRCL0dtLMJ/Hw8r.VlF6FLZvLbDrOkPNnO.pQbQGT89.urK', NULL, 'Administrateur', './assets/img/uploads/esterique.jpeg', '2024-09-27 06:53:50'),
(8, 'Boss', 'Boss', 'boss@gmail.com', '$2y$10$2SFR0gYymzvo7L1VMa7OkOk0hoPp2JbgF0lLUEhjR7RhNSmwx3jrW', NULL, 'Boss', './assets/img/uploads/menu.png', '2024-10-15 15:29:03'),
(15, 'Manager', 'Manager', 'manager@gmail.com', '$2y$10$5gdb/qj4RYklXZ6dYmt9gOxngg952VHex2/I1Eux0KCRgvq1.TOLm', NULL, 'Manager', './assets/img/default-profile.jpg', '2025-02-17 09:07:59'),
(16, 'User', 'User', 'user@gmail.com', '$2y$10$9VrnO2DL.VbBEc.Q59rhHul2ta4w2d7H2TeuU9SssKO9CMhrd0edK', NULL, 'User', './assets/img/default-profile.jpg', '2025-02-17 09:09:39'),
(17, 'Hakizimana', 'Nadjati', 'hak@gmail.com', '$2y$10$Bvrj55rzaq.AsS.GPIzNTuOYnmUM1/k61f5sFdjEYUtBEQ9UuQ7FC', NULL, 'User', './assets/img/default-profile.jpg', '2025-02-18 15:49:59'),
(22, 'Storekeeper', 'Storekeeper', 'storekeeper@gmail.com', '$2y$10$GGHnuhWSM2jVjzygE1xG9.7wiv7jts6pNo8L8QF6XqnuDiKPbDD6O', NULL, 'Storekeeper', './assets/img/default-profile.jpg', '2025-07-15 08:37:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_activity_user` (`user_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `idx_employee_date` (`employee_id`,`attendance_date`),
  ADD KEY `fk_attendance_schedule` (`schedule_id`);

--
-- Indexes for table `buffet_accompaniments`
--
ALTER TABLE `buffet_accompaniments`
  ADD PRIMARY KEY (`accompaniment_id`),
  ADD KEY `buffet_item_id` (`buffet_item_id`);

--
-- Indexes for table `buffet_preferences`
--
ALTER TABLE `buffet_preferences`
  ADD PRIMARY KEY (`period_id`);

--
-- Indexes for table `buffet_sales`
--
ALTER TABLE `buffet_sales`
  ADD PRIMARY KEY (`sale_id`);

--
-- Indexes for table `buffet_sale_adjustments`
--
ALTER TABLE `buffet_sale_adjustments`
  ADD PRIMARY KEY (`adjustment_id`),
  ADD KEY `buffet_item_id` (`buffet_item_id`);

--
-- Indexes for table `buffet_sale_items`
--
ALTER TABLE `buffet_sale_items`
  ADD PRIMARY KEY (`buffet_item_id`);

--
-- Indexes for table `cancellation_reasons`
--
ALTER TABLE `cancellation_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`debt_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_debt_payments_debt_id` (`debt_id`),
  ADD KEY `idx_debt_payments_created_by` (`created_by`);

--
-- Indexes for table `drink_sales`
--
ALTER TABLE `drink_sales`
  ADD PRIMARY KEY (`sale_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `employee_loans`
--
ALTER TABLE `employee_loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `fk_feedback_customer` (`customer_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_inventory_supplier` (`supplier_id`);

--
-- Indexes for table `inventory_items1`
--
ALTER TABLE `inventory_items1`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_inventory_supplier` (`supplier_id`);

--
-- Indexes for table `kitchen_orders`
--
ALTER TABLE `kitchen_orders`
  ADD PRIMARY KEY (`kot_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `kitchen_order_items`
--
ALTER TABLE `kitchen_order_items`
  ADD PRIMARY KEY (`kot_item_id`),
  ADD KEY `kot_id` (`kot_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `loan_repayments`
--
ALTER TABLE `loan_repayments`
  ADD PRIMARY KEY (`repayment_id`),
  ADD KEY `loan_id` (`loan_id`),
  ADD KEY `payroll_id` (`payroll_id`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `menu_items1`
--
ALTER TABLE `menu_items1`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `menu_sales`
--
ALTER TABLE `menu_sales`
  ADD PRIMARY KEY (`sale_id`);

--
-- Indexes for table `menu_stock_items`
--
ALTER TABLE `menu_stock_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `stock_item_id` (`stock_item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `payroll_records`
--
ALTER TABLE `payroll_records`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `fk_payroll_employee` (`employee_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`purchase_order_id`),
  ADD KEY `fk_po_supplier` (`supplier_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`po_item_id`),
  ADD KEY `fk_poi_inventory` (`inventory_id`),
  ADD KEY `fk_poi_po` (`purchase_order_id`);

--
-- Indexes for table `purchase_records`
--
ALTER TABLE `purchase_records`
  ADD PRIMARY KEY (`purchase_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_employee_work_date` (`employee_id`,`work_date`),
  ADD KEY `fk_schedules_shift` (`shift_id`);

--
-- Indexes for table `shift_templates`
--
ALTER TABLE `shift_templates`
  ADD PRIMARY KEY (`shift_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`adjustment_id`);

--
-- Indexes for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD PRIMARY KEY (`adjustment_item_id`),
  ADD KEY `adjustment_id` (`adjustment_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `buffet_accompaniments`
--
ALTER TABLE `buffet_accompaniments`
  MODIFY `accompaniment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buffet_preferences`
--
ALTER TABLE `buffet_preferences`
  MODIFY `period_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `buffet_sales`
--
ALTER TABLE `buffet_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `buffet_sale_adjustments`
--
ALTER TABLE `buffet_sale_adjustments`
  MODIFY `adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `buffet_sale_items`
--
ALTER TABLE `buffet_sale_items`
  MODIFY `buffet_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `cancellation_reasons`
--
ALTER TABLE `cancellation_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `debts`
--
ALTER TABLE `debts`
  MODIFY `debt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `debt_payments`
--
ALTER TABLE `debt_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `drink_sales`
--
ALTER TABLE `drink_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `employee_loans`
--
ALTER TABLE `employee_loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `inventory_items1`
--
ALTER TABLE `inventory_items1`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `kitchen_orders`
--
ALTER TABLE `kitchen_orders`
  MODIFY `kot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kitchen_order_items`
--
ALTER TABLE `kitchen_order_items`
  MODIFY `kot_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loan_repayments`
--
ALTER TABLE `loan_repayments`
  MODIFY `repayment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `menu_items1`
--
ALTER TABLE `menu_items1`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `menu_sales`
--
ALTER TABLE `menu_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu_stock_items`
--
ALTER TABLE `menu_stock_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `payroll_records`
--
ALTER TABLE `payroll_records`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `po_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `purchase_records`
--
ALTER TABLE `purchase_records`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `shift_templates`
--
ALTER TABLE `shift_templates`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  MODIFY `adjustment_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserId`);

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `buffet_accompaniments`
--
ALTER TABLE `buffet_accompaniments`
  ADD CONSTRAINT `buffet_accompaniments_ibfk_1` FOREIGN KEY (`buffet_item_id`) REFERENCES `buffet_sale_items` (`buffet_item_id`);

--
-- Constraints for table `buffet_sale_adjustments`
--
ALTER TABLE `buffet_sale_adjustments`
  ADD CONSTRAINT `buffet_sale_adjustments_ibfk_1` FOREIGN KEY (`buffet_item_id`) REFERENCES `buffet_sale_items` (`buffet_item_id`);

--
-- Constraints for table `debts`
--
ALTER TABLE `debts`
  ADD CONSTRAINT `debts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `debts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`UserId`);

--
-- Constraints for table `debt_payments`
--
ALTER TABLE `debt_payments`
  ADD CONSTRAINT `fk_debt_payments_created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`UserId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_debt_payments_debt` FOREIGN KEY (`debt_id`) REFERENCES `debts` (`debt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employee_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserId`);

--
-- Constraints for table `employee_loans`
--
ALTER TABLE `employee_loans`
  ADD CONSTRAINT `employee_loans_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `inventory_items1`
--
ALTER TABLE `inventory_items1`
  ADD CONSTRAINT `fk_inventory_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `kitchen_orders`
--
ALTER TABLE `kitchen_orders`
  ADD CONSTRAINT `kitchen_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `kitchen_order_items`
--
ALTER TABLE `kitchen_order_items`
  ADD CONSTRAINT `kitchen_order_items_ibfk_1` FOREIGN KEY (`kot_id`) REFERENCES `kitchen_orders` (`kot_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kitchen_order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu_items1` (`menu_id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_repayments`
--
ALTER TABLE `loan_repayments`
  ADD CONSTRAINT `loan_repayments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `employee_loans` (`loan_id`),
  ADD CONSTRAINT `loan_repayments_ibfk_2` FOREIGN KEY (`payroll_id`) REFERENCES `payroll_records` (`payroll_id`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items1`
--
ALTER TABLE `menu_items1`
  ADD CONSTRAINT `menu_items1_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_stock_items`
--
ALTER TABLE `menu_stock_items`
  ADD CONSTRAINT `menu_stock_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menu_items1` (`menu_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_stock_items_ibfk_2` FOREIGN KEY (`stock_item_id`) REFERENCES `inventory_items1` (`inventory_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu_items1` (`menu_id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_records`
--
ALTER TABLE `payroll_records`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_poi_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_items1` (`inventory_id`),
  ADD CONSTRAINT `fk_poi_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_records` (`purchase_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `fk_schedules_shift` FOREIGN KEY (`shift_id`) REFERENCES `shift_templates` (`shift_id`);

--
-- Constraints for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD CONSTRAINT `stock_adjustment_items_ibfk_1` FOREIGN KEY (`adjustment_id`) REFERENCES `stock_adjustments` (`adjustment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_items_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_items1` (`inventory_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
