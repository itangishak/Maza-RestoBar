-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 06:46 PM
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
(184, 8, 'User Boss logged in successfully.', '2025-05-14 13:44:53');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_type` enum('Manual','QR Code') DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Holiday','Ill') NOT NULL,
  `clock_in_time` time NOT NULL,
  `clock_out_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`attendance_id`, `employee_id`, `attendance_type`, `attendance_date`, `status`, `clock_in_time`, `clock_out_time`, `notes`) VALUES
(1, 3, 'Manual', '2024-12-24', 'Present', '08:51:00', '11:59:00', ''),
(7, 3, 'Manual', '2025-05-14', 'Present', '18:31:00', '00:00:00', ''),
(8, 3, 'Manual', '2025-05-14', 'Present', '18:42:00', '18:43:00', ''),
(9, 3, 'Manual', '2025-05-14', 'Present', '18:43:00', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sales`
--

CREATE TABLE `buffet_sales` (
  `sale_id` int(11) NOT NULL,
  `sale_date` datetime NOT NULL,
  `dishes_sold` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buffet_sales`
--

INSERT INTO `buffet_sales` (`sale_id`, `sale_date`, `dishes_sold`, `created_at`, `total_price`) VALUES
(10, '2025-03-28 00:00:00', 15, '2025-03-28 12:09:09', '150.00'),
(11, '2025-03-28 00:00:00', 15, '2025-03-28 12:10:06', '15.00'),
(12, '2025-03-30 00:00:00', 1, '2025-03-30 15:19:38', '10000.00'),
(13, '2025-05-06 00:00:00', 1, '2025-05-06 16:30:40', '10000.00'),
(14, '2025-05-06 00:00:00', 20, '2025-05-06 16:50:19', '200000.00'),
(15, '2025-05-06 00:00:00', 6, '2025-05-06 19:20:11', '60000.00');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sale_item`
--

CREATE TABLE `buffet_sale_item` (
  `buffet_item_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `sale_date` date NOT NULL,
  `time_of_day` enum('Morning','Evening','Noon') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buffet_sale_item`
--

INSERT INTO `buffet_sale_item` (`buffet_item_id`, `sale_id`, `item_id`, `quantity_used`, `sale_date`, `time_of_day`) VALUES
(0, 10, 1, '1.00', '0000-00-00', 'Noon'),
(0, 11, 1, '1.00', '0000-00-00', 'Noon'),
(0, 12, 20, '2.00', '0000-00-00', 'Noon'),
(0, 13, 11, '1.00', '0000-00-00', 'Noon'),
(0, 14, 14, '1.00', '0000-00-00', 'Noon'),
(0, 15, 1, '1.00', '0000-00-00', 'Noon');

-- --------------------------------------------------------

--
-- Table structure for table `buffet_sale_items`
--

CREATE TABLE `buffet_sale_items` (
  `buffet_item_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `time_of_day` enum('Morning','Noon','Evening') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `buffet_sale_items`
--

INSERT INTO `buffet_sale_items` (`buffet_item_id`, `sale_date`, `time_of_day`, `created_at`) VALUES
(1, '2025-03-28', 'Morning', '2025-03-28 08:18:11'),
(2, '2025-03-28', 'Morning', '2025-03-28 08:18:12'),
(3, '2025-03-28', 'Noon', '2025-03-28 09:47:21'),
(4, '2025-03-28', 'Noon', '2025-03-28 09:48:10'),
(5, '2025-03-28', 'Noon', '2025-03-28 09:48:31'),
(6, '2025-03-21', 'Evening', '2025-03-28 09:50:04'),
(7, '2025-03-28', 'Noon', '2025-03-28 10:07:30'),
(8, '2025-03-28', 'Noon', '2025-03-28 10:07:45'),
(9, '2025-03-28', 'Noon', '2025-03-28 10:13:11'),
(10, '2025-03-28', 'Noon', '2025-03-28 10:18:19'),
(11, '2025-03-28', 'Noon', '2025-03-28 10:18:19'),
(12, '2025-03-28', 'Noon', '2025-03-28 10:18:34'),
(13, '2025-03-28', 'Noon', '2025-03-28 10:18:34'),
(14, '2025-03-28', 'Noon', '2025-03-28 10:31:53'),
(15, '2025-03-28', 'Noon', '2025-03-28 10:32:08'),
(16, '2025-03-28', 'Noon', '2025-03-28 10:32:14'),
(17, '2025-03-28', 'Noon', '2025-03-28 10:32:19'),
(18, '2025-03-28', 'Noon', '2025-03-28 10:55:54'),
(19, '2025-04-22', 'Evening', '2025-04-22 15:02:31'),
(20, '2025-05-06', 'Noon', '2025-05-06 12:09:41'),
(21, '2025-05-06', 'Noon', '2025-05-06 12:37:26'),
(22, '2025-05-06', 'Noon', '2025-05-06 12:42:56'),
(23, '2025-05-06', 'Noon', '2025-05-06 12:45:45'),
(24, '2025-05-06', 'Noon', '2025-05-06 12:58:27'),
(25, '2025-05-06', 'Noon', '2025-05-06 13:00:28'),
(26, '2025-05-06', 'Noon', '2025-05-06 13:15:26'),
(27, '2025-05-06', 'Noon', '2025-05-06 13:19:29'),
(28, '2025-05-06', 'Noon', '2025-05-06 13:20:05'),
(29, '2025-05-06', 'Noon', '2025-05-06 13:30:40'),
(30, '2025-05-06', 'Noon', '2025-05-06 13:39:08'),
(31, '2025-05-06', 'Noon', '2025-05-06 13:51:48'),
(32, '2025-05-06', 'Noon', '2025-05-06 13:58:25'),
(33, '2025-05-06', 'Noon', '2025-05-06 14:06:01'),
(34, '2025-05-06', 'Noon', '2025-05-06 14:08:10'),
(35, '2025-05-06', 'Noon', '2025-05-06 14:08:26'),
(36, '2025-05-06', 'Noon', '2025-05-06 14:11:04'),
(37, '2025-05-06', 'Noon', '2025-05-06 14:18:02'),
(38, '2025-05-06', 'Noon', '2025-05-06 14:25:12'),
(39, '2025-05-06', 'Evening', '2025-05-06 15:42:54'),
(40, '2025-05-06', 'Evening', '2025-05-06 16:54:01'),
(41, '2025-05-06', 'Evening', '2025-05-06 17:06:09'),
(42, '2025-05-06', 'Evening', '2025-05-06 17:07:08'),
(43, '2025-05-06', 'Evening', '2025-05-06 19:19:21'),
(44, '2025-05-06', 'Evening', '2025-05-06 19:19:33'),
(45, '2025-05-06', 'Evening', '2025-05-06 19:43:54'),
(46, '2025-05-12', 'Noon', '2025-05-12 13:54:34');

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
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `debts`
--

INSERT INTO `debts` (`debt_id`, `customer_id`, `amount`, `due_date`, `status`, `notes`) VALUES
(3, 13, '100000.00', '2024-12-10', 'Paid', ''),
(4, 15, '400000.00', '2024-12-19', 'Pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `drink_sales`
--

CREATE TABLE `drink_sales` (
  `sale_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `drink_sales`
--

INSERT INTO `drink_sales` (`sale_id`, `inventory_id`, `quantity_sold`, `sale_price`, `sale_date`) VALUES
(1, 1, 2, '2000.00', '2025-03-04 14:16:17'),
(2, 1, 1, '1000.00', '2025-03-04 14:16:40'),
(3, 1, 1, '1000.00', '2025-03-28 11:09:52'),
(5, 1, 1, '1000.00', '2025-05-06 15:32:44'),
(6, 1, 1, '1000.00', '2025-05-06 15:35:16'),
(7, 1, 1, '1000.00', '2025-05-06 16:17:27'),
(12, 9, 3, '105000.00', '2025-05-06 16:33:23'),
(13, 16, 5, '17500.00', '2025-05-06 16:33:23'),
(14, 9, 1, '35000.00', '2025-05-06 19:45:24');

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
(3, 1, 'Manager', '600000.00', '2024-12-05', '2024-12-23 14:04:52');

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
(1, 'Rice', '3', '0', '3.00', '4', '5.00', 2, '2024-12-16 08:24:26', '2025-05-12 14:01:12', '1000.00'),
(2, 'Bean', '3', '0', '50.00', '4', '7.00', 2, '2024-12-16 08:26:00', '2025-05-12 14:00:44', '5000.00'),
(3, 'Meat', '3', '0', '0.00', '4', '3.00', 2, '2024-12-16 08:46:26', '2025-05-12 14:01:01', '32000.00'),
(9, 'Primus', '1', '0', '6.00', '2', '5.00', 2, '2024-12-16 09:23:25', '2025-05-12 14:01:31', '35000.00'),
(16, 'Boke', '1', '0', '5.00', '1', '2.00', 2, '2025-03-03 14:39:19', '2025-05-06 16:33:23', '3500.00'),
(20, 'Rice maora', '3', NULL, '0.00', '1', '3.00', 2, '2025-03-30 15:14:23', '2025-03-30 15:19:38', '5000.00'),
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
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu_sales`
--

INSERT INTO `menu_sales` (`sale_id`, `menu_id`, `quantity_sold`, `sale_price`, `sale_date`) VALUES
(13, 10, 4, '20000.00', '2025-02-28 14:16:25'),
(14, 10, 1, '5000.00', '2025-02-28 15:32:02'),
(22, 10, 5, '25000.00', '2025-03-04 14:08:06'),
(24, 10, 1, '5000.00', '2025-03-04 14:16:27'),
(25, 10, 3, '5000.00', '2025-03-06 11:19:08'),
(26, 10, 10, '5000.00', '2025-03-06 11:20:19'),
(27, 10, 1, '5000.00', '2025-03-07 08:22:43'),
(29, 10, 10, '50000.00', '2025-03-07 10:58:12'),
(31, 10, 2, '10000.00', '2025-03-27 10:39:58'),
(32, 10, 1, '5000.00', '2025-03-28 10:24:30'),
(33, 10, 1, '5000.00', '2025-03-28 11:03:10'),
(34, 10, 1, '5000.00', '2025-04-22 15:02:00'),
(35, 10, 1, '5000.00', '2025-05-06 12:07:01'),
(36, 10, 1, '5000.00', '2025-05-06 14:27:46'),
(37, 10, 1, '0.00', '2025-05-06 14:44:56'),
(38, 10, 1, '5000.00', '2025-05-06 15:18:17'),
(39, 10, 1, '5000.00', '2025-05-06 15:19:21'),
(40, 10, 1, '5000.00', '2025-05-06 15:42:39'),
(41, 10, 1, '5000.00', '2025-05-06 15:49:53'),
(42, 10, 1, '5000.00', '2025-05-06 15:50:05'),
(43, 10, 1, '5000.00', '2025-05-06 16:10:17'),
(44, 10, 1, '5000.00', '2025-05-06 16:34:31'),
(45, 10, 1, '5000.00', '2025-05-06 16:35:01'),
(46, 10, 1, '5000.00', '2025-05-06 16:36:34'),
(47, 10, 1, '5000.00', '2025-05-06 16:44:41');

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
(27, 12, 1, '1.00', '2025-03-30 15:32:38'),
(28, 13, 1, '1.00', '2025-03-30 15:32:50'),
(29, 12, 1, '1.00', '2025-03-30 15:32:52'),
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
(26, 19, '5000.00', '2025-03-27 11:45:35', 'pending');

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
(28, 26, 10, 1, '5000.00');

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
  `deductions` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payroll_records`
--

INSERT INTO `payroll_records` (`payroll_id`, `employee_id`, `pay_period_start`, `pay_period_end`, `gross_pay`, `deductions`, `net_pay`, `payment_date`, `notes`) VALUES
(5, 3, '2025-03-28', '2025-03-28', '100000.00', '5000.00', '95000.00', '2025-03-28', '');

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
(29, 31, 1, '3.00', '1000.00'),
(30, 32, 1, '10.00', '1000.00'),
(31, 33, 1, '5.00', '1000.00'),
(32, 34, 1, '5.00', '1000.00'),
(33, 35, 1, '3.00', '1000.00'),
(34, 36, 1, '3.00', '1000.00'),
(35, 37, 1, '3.00', '1000.00'),
(39, 41, 1, '10.00', '1000.00'),
(40, 41, 2, '10.00', '5000.00'),
(41, 42, 1, '10.00', '1000.00'),
(42, 42, 3, '15.00', '32000.00'),
(43, 43, 1, '10.00', '1000.00'),
(44, 44, 1, '10.00', '1000.00');

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
(31, 'PO-20250306-67c9a01f367bd', '2025-03-06 14:16:15', 'Cash', 'Boss Boss', '3000.00'),
(32, 'PO-20250306-67c9ad76122d9', '2025-03-06 15:13:10', 'Cash', 'Boss Boss', '10000.00'),
(33, 'PO-20250306-67c9adbe01d6a', '2025-03-06 15:14:22', 'Cash', 'Boss Boss', '5000.00'),
(34, 'PO-20250306-67c9ae980a57e', '2025-03-06 15:18:00', 'Cash', 'Boss Boss', '5000.00'),
(35, 'PO-20250306-67c9aea875cd3', '2025-03-06 15:18:16', 'Cash', 'Boss Boss', '3000.00'),
(36, 'PO-20250306-67c9afd2a028b', '2025-03-06 15:23:14', 'Cash', 'Boss Boss', '3000.00'),
(37, 'PO-20250306-67c9afe4c449a', '2025-03-06 15:23:32', 'Cash', 'Boss Boss', '3000.00'),
(41, 'PO-20250506-681a529b2c9ec', '2025-05-06 20:19:07', 'EspÃ¨ces', 'Boss Boss', '60000.00'),
(42, 'PO-20250506-681a54c80be4b', '2025-05-06 20:28:24', 'EspÃ¨ces', 'Boss Boss', '490000.00'),
(43, 'PO-20250506-681a57f50e9d5', '2025-05-06 20:41:57', 'EspÃ¨ces', 'Boss Boss', '10000.00'),
(44, 'PO-20250506-681a5985a1af9', '2025-05-06 20:48:37', 'EspÃ¨ces', 'Boss Boss', '10000.00');

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
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `shift_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(26, 'Add', 'Received', 1, '', 1, '2025-05-06 16:32:53');

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
(26, 26, 9, '0.00', '10.00', '35000.00', '10.00', '2025-05-06 16:32:53');

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
(17, 'Hakizimana', 'Nadjati', 'hak@gmail.com', '$2y$10$Bvrj55rzaq.AsS.GPIzNTuOYnmUM1/k61f5sFdjEYUtBEQ9UuQ7FC', NULL, 'User', './assets/img/default-profile.jpg', '2025-02-18 15:49:59');

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
  ADD KEY `fk_attendance_employee` (`employee_id`);

--
-- Indexes for table `buffet_sales`
--
ALTER TABLE `buffet_sales`
  ADD PRIMARY KEY (`sale_id`);

--
-- Indexes for table `buffet_sale_items`
--
ALTER TABLE `buffet_sale_items`
  ADD PRIMARY KEY (`buffet_item_id`);

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
  ADD KEY `fk_debt_customer` (`customer_id`);

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
-- Indexes for table `menu_sales`
--
ALTER TABLE `menu_sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `menu_id` (`menu_id`);

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
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`shift_id`),
  ADD KEY `fk_shifts_employee` (`employee_id`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `buffet_sales`
--
ALTER TABLE `buffet_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `buffet_sale_items`
--
ALTER TABLE `buffet_sale_items`
  MODIFY `buffet_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

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
-- AUTO_INCREMENT for table `drink_sales`
--
ALTER TABLE `drink_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
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
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `menu_sales`
--
ALTER TABLE `menu_sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `menu_stock_items`
--
ALTER TABLE `menu_stock_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payroll_records`
--
ALTER TABLE `payroll_records`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `po_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `purchase_records`
--
ALTER TABLE `purchase_records`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `adjustment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  MODIFY `adjustment_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `debts`
--
ALTER TABLE `debts`
  ADD CONSTRAINT `fk_debt_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employee_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserId`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
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
  ADD CONSTRAINT `kitchen_order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu_items` (`menu_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_sales`
--
ALTER TABLE `menu_sales`
  ADD CONSTRAINT `menu_sales_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menu_items` (`menu_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_stock_items`
--
ALTER TABLE `menu_stock_items`
  ADD CONSTRAINT `menu_stock_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menu_items` (`menu_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_stock_items_ibfk_2` FOREIGN KEY (`stock_item_id`) REFERENCES `inventory_items` (`inventory_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu_items` (`menu_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_poi_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_items` (`inventory_id`),
  ADD CONSTRAINT `fk_poi_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_records` (`purchase_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `fk_shifts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD CONSTRAINT `stock_adjustment_items_ibfk_1` FOREIGN KEY (`adjustment_id`) REFERENCES `stock_adjustments` (`adjustment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_items_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_items` (`inventory_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
