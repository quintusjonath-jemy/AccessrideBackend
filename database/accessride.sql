-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 22, 2026 at 02:48 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET SESSION sql_require_primary_key = 0;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `accessride`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `profile_image`, `password`, `created_at`) VALUES
(1, 'Admins', 'jemysparrow@gmail.com', '0771245896', '1780929939_Anime boy.jpeg', '$2y$10$WrRcN.r9jKxIhXCAVOoczu1C6l2b6TrINPOMtAlEeWvDXQcexkp2S', '2026-05-29 19:36:20');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `type`, `message`, `is_read`, `created_at`) VALUES
(132, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-13 00:57:02'),
(133, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-13 01:52:15'),
(134, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-13 01:54:18'),
(135, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-13 06:06:03'),
(136, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-13 06:46:46'),
(137, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 10:36:37'),
(138, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 10:38:23'),
(139, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 10:39:50'),
(140, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 10:42:04'),
(141, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 10:42:58'),
(142, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 11:00:12'),
(143, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 11:04:02'),
(144, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 11:09:34'),
(145, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 11:23:19'),
(146, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 11:24:50'),
(147, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-13 11:28:55'),
(148, 'Driver', 'Driver Kasun Fernando\'s monthly membership subscription has expired.', 1, '2026-07-17 11:11:59'),
(149, 'SOS', 'Alert: Emergency SOS Alert', 1, '2026-07-17 11:13:11'),
(150, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-18 05:01:57'),
(151, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-18 05:19:27'),
(152, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-18 05:21:45'),
(153, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 1, '2026-07-18 05:23:53'),
(154, 'Driver', 'Driver Jemy\'s monthly membership subscription has expired.', 1, '2026-07-20 09:57:01'),
(155, 'Driver', 'Warning: Driver Kasun Fernando\'s membership subscription expired on 2026-07-16 (over 3 days ago). Automated activation warning sent.', 1, '2026-07-20 09:57:18'),
(156, 'Driver', 'Driver Mike Johnson\'s monthly membership subscription has expired.', 1, '2026-07-21 12:04:47'),
(157, 'Driver', 'Warning: Driver Jemy\'s membership subscription expired on 2026-07-18 (over 3 days ago). Automated activation warning sent.', 1, '2026-07-21 12:04:48'),
(158, 'Driver', 'Warning: Driver Mike Johnson\'s membership subscription expired on 2026-07-20 (over 3 days ago). Automated activation warning sent.', 1, '2026-07-30 11:56:08'),
(159, 'Ride', 'New ride booked: Mannar Town, Mannar, Sri Lanka to Mannar Town, Mannar, Sri Lanka', 0, '2026-08-08 11:15:59'),
(160, 'Ride', 'New ride booked: Mannar Town, Mannar, Sri Lanka to Mannar Town, Mannar, Sri Lanka', 0, '2026-08-08 11:27:35'),
(161, 'Ride', 'New ride booked: Mannar Town, Mannar, Sri Lanka to main marta hun', 0, '2026-08-08 11:41:53'),
(162, 'Ride', 'New ride booked: Colombo to Kandy', 0, '2026-08-08 09:43:27'),
(163, 'Ride', 'New ride booked: Colombo to Kandy', 0, '2026-08-08 09:43:38'),
(164, 'Ride', 'New ride booked: colombo to central medical plaza', 0, '2026-08-12 20:15:23'),
(165, 'Ride', 'New ride booked: Thoddaveli, Mannar Town, Mannar, Sri Lanka to Mannar Town, Mannar, Sri Lanka', 0, '2026-08-12 20:18:38'),
(166, 'User', 'New user registered: Jannat Edward', 0, '2026-08-15 13:29:39'),
(167, 'Ride', 'New ride booked: Mannar to Amarnath Town', 0, '2026-08-15 11:13:57'),
(168, 'Ride', 'New ride booked: mannar to mannar town', 0, '2026-08-15 11:27:57'),
(169, 'Ride', 'New ride booked: Central Library to Mannar Town', 0, '2026-08-15 12:34:05'),
(170, 'Ride', 'New ride booked: Your current location to Manna Central Library', 0, '2026-08-15 13:46:26'),
(171, 'Ride', 'New ride booked: Your current location to Manna Central Library', 0, '2026-08-15 13:57:29'),
(172, 'Ride', 'New ride booked: current location to Manna Central Library', 0, '2026-08-15 14:00:33'),
(173, 'Ride', 'New ride booked: Colombo to Kandy', 0, '2026-08-15 15:28:09'),
(174, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-15 19:14:59'),
(175, 'Ride', 'New ride booked: MNR to Manna Central Library', 0, '2026-08-17 10:30:00'),
(176, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 14:31:29'),
(177, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 14:31:30'),
(178, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:43:29'),
(179, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:43:30'),
(180, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:44:05'),
(181, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:44:41'),
(182, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:45:47'),
(183, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:52:51'),
(184, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:52:53'),
(185, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:53:14'),
(186, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:53:15'),
(187, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:55:32'),
(188, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:55:49'),
(189, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:55:50'),
(190, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 15:59:29'),
(191, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:00:00'),
(192, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:00:01'),
(193, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:04:29'),
(194, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:04:31'),
(195, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:06:36'),
(196, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:06:52'),
(197, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:06:53'),
(198, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:07:46'),
(199, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:07:47'),
(200, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:10:33'),
(201, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:10:35'),
(202, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:17:54'),
(203, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:17:54'),
(204, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:18:30'),
(205, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:18:31'),
(206, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:20:35'),
(207, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:20:36'),
(208, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:21:52'),
(209, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:21:54'),
(210, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:28:27'),
(211, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:28:28'),
(212, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:44:08'),
(213, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-16 16:44:09'),
(214, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 12:14:28'),
(215, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 12:14:29'),
(216, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 12:27:41'),
(217, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 12:27:42'),
(218, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 12:28:46'),
(219, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 12:28:47'),
(220, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 13:26:11'),
(221, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 13:26:13'),
(222, 'SOS', 'Alert: Emergency SOS Alert', 0, '2026-08-18 13:26:14'),
(223, 'Ride', 'New ride booked: current location to Manna library', 0, '2026-08-18 12:15:49'),
(224, 'Ride', 'New ride booked: current location to Mana library', 0, '2026-08-18 14:53:48'),
(225, 'Ride', 'New ride booked: Mannar Town, Mannar, Sri Lanka to Mannar, Sri Lanka', 0, '2026-08-18 17:01:53'),
(226, 'Ride', 'New ride booked: Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka', 0, '2026-08-21 08:28:40');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `alert_type` enum('sos','low_battery','navigation','driver_emergency','system') NOT NULL,
  `message` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `user_id`, `driver_id`, `alert_type`, `message`, `latitude`, `longitude`, `status`, `created_at`) VALUES
(1, 1, 1, 'sos', 'User requested emergency assistance', NULL, NULL, 'resolved', '2026-05-28 07:14:22'),
(2, 2, 3, 'low_battery', 'Battery level below 10%', NULL, NULL, 'resolved', '2026-05-28 07:14:22'),
(3, 1, 2, 'driver_emergency', 'Driver reported vehicle issue', NULL, NULL, 'resolved', '2026-05-28 07:14:22'),
(4, 2, 1, 'sos', 'User requested emergency assistance', NULL, NULL, 'resolved', '2026-06-01 14:22:39'),
(16, 1, 1, 'sos', 'Emergency SOS Alert', 6.98189410, 81.07578810, 'resolved', '2026-07-02 15:02:11'),
(17, 1, 10, 'sos', 'Emergency SOS Alert', 6.98188820, 81.07578990, 'resolved', '2026-07-03 10:23:37'),
(18, 16, 1, 'sos', 'Emergency SOS Alert', 6.98188250, 81.07578400, 'resolved', '2026-07-03 13:11:08'),
(19, 16, 1, 'sos', 'Emergency SOS Alert', 6.98187540, 81.07581060, 'resolved', '2026-07-06 11:42:41'),
(20, 16, NULL, 'sos', 'Emergency SOS Alert', 6.92707860, 79.86124300, 'resolved', '2026-07-11 04:20:13'),
(21, 16, NULL, 'sos', 'Emergency SOS Alert', 6.92707860, 79.86124300, 'resolved', '2026-07-11 04:21:47'),
(22, 16, NULL, 'sos', 'Emergency SOS Alert', 6.92707860, 79.86124300, 'resolved', '2026-07-11 04:22:13'),
(23, 16, 1, 'sos', 'Emergency SOS Alert', 6.92707860, 79.86124300, 'resolved', '2026-07-11 16:12:29'),
(24, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98193060, 81.07569960, 'resolved', '2026-07-12 10:28:12'),
(25, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98189650, 81.07582200, 'resolved', '2026-07-12 10:53:37'),
(26, 16, 1, 'sos', 'Emergency SOS Alert', 6.98187710, 81.07583640, 'resolved', '2026-07-12 11:05:34'),
(27, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98190590, 81.07577750, 'resolved', '2026-07-12 11:22:20'),
(28, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98192820, 81.07575050, 'resolved', '2026-07-12 11:31:36'),
(29, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98189190, 81.07579310, 'resolved', '2026-07-13 10:36:37'),
(30, 1, 9, 'sos', 'Emergency SOS Alert', 6.92710000, 79.86120000, 'resolved', '2026-07-13 10:38:23'),
(31, 1, 9, 'sos', 'Emergency SOS Alert', 6.92710000, 79.86120000, 'resolved', '2026-07-13 10:39:50'),
(32, 1, 9, 'sos', 'Emergency SOS Alert', 6.92710000, 79.86120000, 'resolved', '2026-07-13 10:42:04'),
(33, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98191100, 81.07578180, 'resolved', '2026-07-13 10:42:58'),
(34, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98192630, 81.07585600, 'resolved', '2026-07-13 11:00:12'),
(35, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98192850, 81.07583820, 'resolved', '2026-07-13 11:04:02'),
(36, 1, 9, 'sos', 'Emergency SOS Alert', 6.92710000, 79.86120000, 'resolved', '2026-07-13 11:09:34'),
(37, 1, 9, 'sos', 'Emergency SOS Alert', 6.92710000, 79.86120000, 'resolved', '2026-07-13 11:23:19'),
(38, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98191020, 81.07583330, 'resolved', '2026-07-13 11:24:50'),
(39, 1, 9, 'sos', 'Emergency SOS Alert', 6.92710000, 79.86120000, 'resolved', '2026-07-13 11:28:55'),
(40, 16, NULL, 'sos', 'Emergency SOS Alert', 6.98189560, 81.07581810, 'resolved', '2026-07-17 11:13:11'),
(41, 16, NULL, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-15 19:14:59'),
(42, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555250, 79.90562510, 'resolved', '2026-08-16 14:31:29'),
(43, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555250, 79.90562510, 'resolved', '2026-08-16 14:31:30'),
(44, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562040, 'resolved', '2026-08-16 15:43:29'),
(45, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555530, 79.90562080, 'resolved', '2026-08-16 15:43:30'),
(46, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555530, 79.90562080, 'resolved', '2026-08-16 15:44:05'),
(47, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555530, 79.90562080, 'resolved', '2026-08-16 15:44:41'),
(48, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 15:45:47'),
(49, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 15:52:51'),
(50, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 15:52:53'),
(51, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 15:53:14'),
(52, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 15:53:15'),
(53, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 15:55:32'),
(54, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 15:55:49'),
(55, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 15:55:50'),
(56, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 15:59:29'),
(57, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:00:00'),
(58, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:00:01'),
(59, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:04:29'),
(60, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:04:31'),
(61, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:06:36'),
(62, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:06:52'),
(63, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:06:53'),
(64, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:07:46'),
(65, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:07:47'),
(66, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:10:33'),
(67, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:10:35'),
(68, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:17:54'),
(69, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:17:54'),
(70, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:18:30'),
(71, 16, 2, 'sos', 'Emergency SOS Alert', 8.98555540, 79.90562090, 'resolved', '2026-08-16 16:18:31'),
(72, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:20:35'),
(73, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:20:36'),
(74, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:21:52'),
(75, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:21:54'),
(76, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:28:27'),
(77, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:28:28'),
(78, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:44:08'),
(79, 16, 2, 'sos', 'Emergency SOS Alert', 9.79107840, 80.16691200, 'resolved', '2026-08-16 16:44:09'),
(80, 16, 2, 'sos', 'Emergency SOS Alert', 6.93698560, 79.85233920, 'resolved', '2026-08-18 12:14:28'),
(81, 16, 2, 'sos', 'Emergency SOS Alert', 6.93698560, 79.85233920, 'resolved', '2026-08-18 12:14:29'),
(82, 16, 2, 'sos', 'Emergency SOS Alert', 8.98521800, 79.90496660, 'resolved', '2026-08-18 12:27:41'),
(83, 16, 2, 'sos', 'Emergency SOS Alert', 8.98521800, 79.90496660, 'resolved', '2026-08-18 12:27:42'),
(84, 16, 2, 'sos', 'Emergency SOS Alert', 8.98521800, 79.90496660, 'resolved', '2026-08-18 12:28:46'),
(85, 16, 2, 'sos', 'Emergency SOS Alert', 8.98522290, 79.90496690, 'resolved', '2026-08-18 12:28:47'),
(86, 16, 2, 'sos', 'Emergency SOS Alert', 6.93698560, 79.85233920, 'resolved', '2026-08-18 13:26:11'),
(87, 16, 2, 'sos', 'Emergency SOS Alert', 6.93698560, 79.85233920, 'resolved', '2026-08-18 13:26:13'),
(88, 16, 2, 'sos', 'Emergency SOS Alert', 6.93698560, 79.85233920, 'resolved', '2026-08-18 13:26:14');



-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `status` varchar(100) DEFAULT 'offline',
  `current_location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `nic` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `town` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `registration_expiry` date DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rating` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `first_name`, `last_name`, `email`, `profile_image`, `phone`, `status`, `current_location`, `created_at`, `latitude`, `longitude`, `nic`, `dob`, `gender`, `street`, `town`, `district`, `province`, `postal_code`, `license_number`, `license_expiry`, `registration_expiry`, `insurance_expiry`, `password`, `rating`) VALUES
(1, 'John', 'Silva', 'john@gmail.com', NULL, '0760219257', 'online', 'Mannar Town, Mannar, Sri Lanka', '2026-05-28 07:09:10', 8.99481600, 79.90804480, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', 3.00),
(2, 'Nimal', 'Perera', 'nimal@gmail.com', NULL, '0719876543', 'online', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-05-28 07:09:10', 6.98188280, 81.07579110, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', 3.00),
(3, 'Kasun', 'Fernando', 'kasun@gmail.com', NULL, '0754567890', 'offline', 'Galle', '2026-05-28 07:10:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', NULL),
(9, 'Jemy', '', 'jemy@gmail.com', NULL, '0772679548', 'online', 'Mannar', '2026-06-08 13:20:24', 6.92900000, 79.86200000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', NULL),
(10, 'Kabilan', '', 'kabil@gmail.com', NULL, '0775689154', 'online', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-06-08 13:23:24', 6.98188170, 81.07577460, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', 3.00),
(33, 'Mike', 'Johnson', 'mike@gmail.com', NULL, '0771234567', 'online', 'Tringomalee', '2026-06-20 15:24:40', 6.92800000, 79.86300000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', NULL),
(35, 'David', 'Millar', '0775689123@accessride.com', '1783166086_Animeboy.jpeg', '0775689123', 'online', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-07-04 11:54:46', 6.93500000, 79.86500000, '1234567890', '2009-06-04', 'Male', 'Mannar', 'Mannar', 'Mannar', 'Northern', '40000', '123456789 V', '2026-07-15', '2026-07-22', '2026-07-22', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', NULL),
(36, 'Quintus', 'Jonath', '0772679514@accessride.com', '1783791485_Animeboy.jpeg', '0704054011', 'online', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-07-11 17:38:05', 6.98191590, 81.07578610, '1234567895', '2026-07-11', 'Male', 'Mannar', 'Mannar', 'Mannar', 'Northern', '40000', '15996234785', '2021-06-11', '2026-07-07', '2026-07-24', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.', 3.00);

--


-- --------------------------------------------------------

--
-- Table structure for table `driver_cards`
--

CREATE TABLE `driver_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `driver_id` int(11) NOT NULL,
  `cardholder_name` varchar(100) NOT NULL,
  `card_brand` varchar(20) NOT NULL,
  `masked_number` varchar(30) NOT NULL,
  `expiry_date` varchar(10) NOT NULL,
  `token` varchar(255) NOT NULL,
  `is_default` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_documents`
--

CREATE TABLE `driver_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `driver_id` int(11) NOT NULL,
  `license_front` varchar(255) DEFAULT NULL,
  `license_back` varchar(255) DEFAULT NULL,
  `registration_image` varchar(255) DEFAULT NULL,
  `insurance_image` varchar(255) DEFAULT NULL,
  `nic_front` varchar(255) DEFAULT NULL,
  `nic_back` varchar(255) DEFAULT NULL,
  `vehicle_front` varchar(255) DEFAULT NULL,
  `vehicle_rear` varchar(255) DEFAULT NULL,
  `vehicle_interior` varchar(255) DEFAULT NULL,
  `dashboard_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_documents`
--

INSERT INTO `driver_documents` (`id`, `driver_id`, `license_front`, `license_back`, `registration_image`, `insurance_image`, `nic_front`, `nic_back`, `vehicle_front`, `vehicle_rear`, `vehicle_interior`, `dashboard_photo`, `created_at`) VALUES
(2, 35, '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '1783166086_Animeboy.jpeg', '2026-07-04 11:54:46'),
(3, 36, '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '1783791485_Animeboy.jpeg', '2026-07-11 17:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `driver_notifications`
--

CREATE TABLE `driver_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `driver_id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','payment','ride','system') DEFAULT 'info',
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_notifications`
--

INSERT INTO `driver_notifications` (`id`, `driver_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 'Payment Successful', 'Your subscription payment of Rs. 1,500.00 was processed successfully via local_sandbox. You are now active. [#PAY-57]', 'payment', 1, '2026-07-17 13:58:07'),
(2, 1, 'Payment Successful', 'Your subscription payment of Rs. 3,000.00 was processed successfully via local_sandbox. You are now active. [#PAY-55]', 'payment', 1, '2026-07-18 08:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `driver_otps`
--

CREATE TABLE `driver_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `phone` varchar(20) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_otps`
--

INSERT INTO `driver_otps` (`id`, `phone`, `otp_code`, `is_verified`, `expires_at`, `created_at`) VALUES
(1, '0771234567', '6678', 0, '2026-08-12 19:57:28', '2026-08-12 17:47:28'),
(2, '0771234567', '3710', 0, '2026-08-12 23:27:49', '2026-08-12 17:47:49'),
(3, '0771234567', '242661', 1, '2026-08-13 00:53:22', '2026-08-12 19:13:22'),
(4, '0772679514', '198181', 0, '2026-08-13 00:58:42', '2026-08-12 19:18:42'),
(5, '0772679514', '767062', 0, '2026-08-13 01:00:49', '2026-08-12 19:20:49'),
(6, '0772679514', '636208', 0, '2026-08-13 01:02:30', '2026-08-12 19:22:30'),
(7, '0772679514', '202845', 0, '2026-08-13 01:03:51', '2026-08-12 19:23:51'),
(8, '0772679514', '180205', 1, '2026-08-13 01:07:56', '2026-08-12 19:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergency_contacts`
--

INSERT INTO `emergency_contacts` (`id`, `user_id`, `contact_name`, `relationship`, `phone_number`, `created_at`) VALUES
(1, 7, 'Sarah Connor', 'Mother', '0775986123', '2026-06-21 13:41:56'),
(2, 1, 'John Doe Sr.', 'Father', '0764628596', '2026-06-21 13:41:56'),
(3, 3, 'Jane Smith', 'Guardian', '0748912357', '2026-06-21 13:41:56'),
(9, 12, 'Swan', 'Guardian', '07768957465', '2026-07-03 11:29:05'),
(10, 16, 'Swan', 'Guardian', '+94772679514', '2026-07-03 11:34:00'),
(11, 17, 'Swan', 'Guardian', '07768957465', '2026-07-03 11:38:09'),
(12, 18, 'joli', 'guardian', '0765412369', '2026-07-12 12:14:20'),
(13, 19, 'kingway', 'guardian', '0772679515', '2026-08-15 13:29:39');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ride_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `ride_id`, `user_id`, `driver_id`, `amount`, `payment_method`, `status`, `transaction_id`, `created_at`) VALUES
(1, 11, 1, 9, 164.00, 'cash', 'completed', NULL, '2026-06-13 18:39:27'),
(2, 12, 1, 9, 6120.00, 'cash', 'completed', NULL, '2026-06-13 18:54:26'),
(4, 13, 1, 9, 168.00, 'cash', 'completed', NULL, '2026-06-13 19:03:50'),
(5, 14, 1, 9, 336.00, 'cash', 'completed', NULL, '2026-06-13 19:27:24'),
(6, 15, 1, 9, 164.00, 'cash', 'completed', NULL, '2026-06-13 19:28:30'),
(7, 16, 1, 9, 164.00, 'cash', 'completed', NULL, '2026-06-13 19:32:49'),
(11, 20, 1, 9, 164.00, 'cash', 'completed', NULL, '2026-06-13 20:01:04'),
(13, 22, 1, 9, 244.00, 'cash', 'completed', NULL, '2026-06-14 06:05:45'),
(20, 30, 3, 10, 228.00, 'cash', 'completed', NULL, '2026-06-20 17:21:58'),
(22, 32, 3, 2, 330.00, 'cash', 'completed', NULL, '2026-06-21 14:18:26'),
(23, 33, 3, 10, 272.00, 'cash', 'completed', NULL, '2026-06-25 10:31:59'),
(24, 34, 3, 10, 164.00, 'cash', 'completed', NULL, '2026-06-25 10:33:09'),
(27, 37, 7, 10, 220.00, 'cash', 'completed', NULL, '2026-07-02 11:51:12'),
(31, 41, 1, 10, 68.00, 'cash', 'completed', NULL, '2026-07-03 10:21:40'),
(32, 42, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-03 12:44:44'),
(33, 43, 16, 2, 136.00, 'cash', 'completed', NULL, '2026-07-04 12:42:27'),
(34, 44, 1, 9, 400.00, 'cash', 'completed', NULL, '2026-07-06 10:45:41'),
(35, 45, 16, 1, 376.00, 'cash', 'completed', NULL, '2026-07-06 10:49:58'),
(36, 46, 16, 1, 376.00, 'cash', 'completed', NULL, '2026-07-06 10:58:08'),
(37, 47, 16, 1, 376.00, 'cash', 'completed', NULL, '2026-07-06 11:20:09'),
(38, 48, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-06 11:41:30'),
(39, 49, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-06 11:48:24'),
(40, 50, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-06 11:53:44'),
(41, 51, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-06 11:58:29'),
(42, 52, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-06 12:00:30'),
(43, 53, 16, 1, 168.00, 'cash', 'completed', NULL, '2026-07-11 04:06:41'),
(44, 54, 16, 35, 168.00, 'cash', 'completed', NULL, '2026-07-11 04:32:40'),
(45, 55, 16, 1, 224.00, 'cash', 'completed', NULL, '2026-07-11 09:47:17'),
(46, 56, 16, 1, 168.00, 'cash', 'completed', NULL, '2026-07-11 16:11:55'),
(48, 58, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-12 11:03:23'),
(49, 59, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-12 11:25:43'),
(50, 60, 16, 1, 216.00, 'cash', 'pending', NULL, '2026-07-13 04:27:02'),
(52, 62, 16, 1, 208.00, 'cash', 'completed', NULL, '2026-07-13 05:24:18'),
(53, 63, 16, 1, 328.00, 'cash', 'completed', NULL, '2026-07-13 09:36:03'),
(54, 64, 16, 1, 216.00, 'cash', 'completed', NULL, '2026-07-13 10:16:46'),
(55, NULL, NULL, 1, 3000.00, 'local_sandbox', 'completed', 'LOCAL_6A5A294DE2550', '2026-07-17 13:08:29'),
(56, NULL, NULL, 2, 3000.00, 'local_sandbox', 'completed', 'LOCAL_6A5A29C52BA7F', '2026-07-17 13:10:29'),
(57, NULL, NULL, 2, 1500.00, 'local_sandbox', 'completed', 'LOCAL_6A5A2D9322704', '2026-07-17 13:26:43'),
(58, 65, 12, 10, 108.00, 'cash', 'completed', NULL, '2026-07-18 08:31:57'),
(60, NULL, NULL, 36, 1500.00, 'local_sandbox', 'completed', 'LOCAL_6A5B3E7444CFB', '2026-07-18 08:51:00'),
(61, 67, 12, 36, 108.00, 'cash', 'completed', NULL, '2026-07-18 08:51:45'),
(62, 68, 12, 36, 108.00, 'cash', 'completed', NULL, '2026-07-18 08:53:53'),
(63, NULL, NULL, 10, 1500.00, 'local_sandbox', 'completed', 'LOCAL_6A6B3D230E701', '2026-07-30 12:01:39'),
(64, 69, 16, 1, 176.00, 'cash', 'completed', NULL, '2026-08-08 14:45:59'),
(65, 70, 16, 1, 176.00, 'cash', 'completed', NULL, '2026-08-08 14:57:35'),
(66, 71, 16, 1, 11680.00, 'cash', 'pending', NULL, '2026-08-08 15:11:53'),
(67, 73, 1, 1, 800.00, 'cash', 'pending', NULL, '2026-08-08 15:13:38'),
(68, 76, 16, 10, 0.00, 'cash', 'pending', NULL, '2026-08-15 14:43:57'),
(69, 77, 16, 2, 0.00, 'cash', 'pending', NULL, '2026-08-15 14:57:57'),
(72, 80, 16, 1, 0.00, 'cash', 'completed', NULL, '2026-08-15 17:27:29'),
(73, 81, 16, 1, 0.00, 'cash', 'completed', NULL, '2026-08-15 17:30:33'),
(75, 83, 16, 36, 272.00, 'cash', 'pending', NULL, '2026-08-16 14:13:24'),
(76, 84, 16, 1, 0.00, 'cash', 'pending', NULL, '2026-08-18 15:45:49'),
(77, 85, 16, 1, 0.00, 'cash', 'pending', NULL, '2026-08-18 18:23:48'),
(78, 86, 16, 2, 60.00, 'cash', 'pending', NULL, '2026-08-18 20:31:53'),
(79, 87, 16, 2, 60.00, 'cash', 'completed', NULL, '2026-08-21 11:58:40');

-- --------------------------------------------------------

--
-- Table structure for table `rides`
--

CREATE TABLE `rides` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `driver_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `ride_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','accepted','active','completed','cancelled','scheduled','emergency') DEFAULT 'pending',
  `fare` decimal(10,2) DEFAULT 0.00,
  `distance_km` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT 'cash',
  `vehicle_type` varchar(50) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rides`
--

INSERT INTO `rides` (`id`, `driver_id`, `user_id`, `pickup_location`, `dropoff_location`, `ride_date`, `status`, `fare`, `distance_km`, `payment_method`, `vehicle_type`, `rating`) VALUES
(2, 2, 2, 'Kandy Town', 'Peradeniya', '2026-03-17 21:00:18', 'completed', 656.00, 8.20, 'cash', NULL, NULL),
(6, 2, 1, 'Mannar', 'Vavuniya', '2026-06-05 09:38:01', 'cancelled', 2400.00, 30.00, 'cash', NULL, NULL),
(10, 9, 1, 'Mannar', 'badulla', '2026-06-25 10:50:00', 'cancelled', 25304.00, 316.30, 'cash', NULL, NULL),
(11, 9, 1, 'Central Library', 'Central Medical Plaza', '2026-06-13 15:09:27', 'accepted', 164.00, 4.10, 'cash', NULL, NULL),
(12, 9, 1, 'Mannar', 'Vanuniya', '2026-06-13 15:24:26', 'cancelled', 6120.00, 76.50, 'cash', NULL, NULL),
(13, 9, 1, 'Central Library ', 'colombo', '2026-06-17 10:50:00', 'active', 168.00, 2.10, 'cash', NULL, NULL),
(14, 9, 1, 'kolonawa', 'Central Library', '2026-06-13 15:57:24', 'cancelled', 336.00, 4.20, 'cash', NULL, NULL),
(15, 9, 1, 'Central Library', 'Central Medical Plaza', '2026-06-13 15:58:30', 'active', 164.00, 4.10, 'cash', NULL, NULL),
(16, 9, 1, 'Central Library ', 'Central Medical Plaza', '2026-06-13 16:02:49', 'cancelled', 164.00, 4.10, 'cash', NULL, NULL),
(20, 9, 1, 'Central Library', 'Central Medical Plaza', '2026-06-13 16:31:04', 'cancelled', 164.00, 4.10, 'cash', NULL, NULL),
(22, 9, 1, 'Wellampitiya', 'Central Medical Plaza', '2026-06-14 02:35:45', 'active', 244.00, 6.10, 'cash', NULL, NULL),
(27, 2, 2, 'Mannar', 'Colombo', '2026-06-15 13:17:54', 'cancelled', 26000.00, 325.00, 'cash', NULL, NULL),
(30, 35, 3, 'Colombo, Sri Lanka', 'Central Medical Plaza', '2026-06-20 13:51:58', 'cancelled', 228.00, 5.70, 'cash', 'bike', NULL),
(32, 35, 3, 'Central Medical Plaza', 'Colombo, Sri Lanka', '2026-06-21 10:48:26', 'cancelled', 330.00, 5.50, 'cash', 'three wheeler', NULL),
(33, 10, 3, 'Colombo, Sri Lanka', 'Maligawatta, Colombo, Colombo, Sri Lanka', '2026-06-29 10:31:00', 'cancelled', 272.00, 3.40, 'cash', '0', NULL),
(34, 34, 3, 'My Current Location (Central Library)', 'Central Medical Plaza', '2026-06-25 07:03:09', 'cancelled', 164.00, 4.10, 'cash', 'bike', NULL),
(37, 34, 7, 'Central Medical Plaza', 'Colombo, Colombo, Sri Lanka', '2026-07-02 08:21:12', 'active', 220.00, 5.50, 'cash', 'bike', NULL),
(41, 10, 1, 'Hindagoda, Badulla, Badulla, Sri Lanka', 'Badulla, Sri Lanka', '2026-07-03 06:51:40', 'cancelled', 68.00, 1.70, 'cash', 'bike', NULL),
(42, 1, 1, 'My Current Location (Glen Alpin)', 'Badulla, Sri Lanka', '2026-07-03 09:14:44', 'cancelled', 216.00, 2.70, 'cash', 'car', NULL),
(43, 2, 16, 'Hindagoda, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-06 12:30:00', 'cancelled', 136.00, 1.70, 'cash', '0', NULL),
(44, 9, 1, 'Central Library', 'Central Medical Plaza', '2026-07-06 07:15:41', 'active', 400.00, 5.00, 'cash', 'car', NULL),
(45, 1, 1, 'My Current Location (Udawela)', 'Badulla, Badulla, Sri Lanka', '2026-07-06 07:19:58', 'completed', 376.00, 4.70, 'cash', 'car', NULL),
(46, 1, 1, 'My Current Location (Udawela)', 'Badulla, Badulla, Sri Lanka', '2026-07-06 07:28:08', 'completed', 376.00, 4.70, 'cash', 'car', NULL),
(47, 1, 1, 'Udawela', 'Badulla, Badulla, Sri Lanka', '2026-07-06 07:50:09', 'completed', 376.00, 4.70, 'cash', 'car', NULL),
(48, 1, 1, 'Glen Alpin', 'Badulla, Badulla, Sri Lanka', '2026-07-06 08:11:30', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(49, 1, 1, 'Glen Alpin', 'Badulla, Badulla, Sri Lanka', '2026-07-06 08:18:24', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(50, 1, 1, 'Glen Alpin', 'Badulla, Sri Lanka', '2026-07-06 08:23:44', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(51, 1, 1, 'Glen Alpin', 'Badulla, Badulla, Sri Lanka', '2026-07-06 08:28:29', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(52, 1, 1, 'Glen Alpin', 'Badulla, Badulla, Sri Lanka', '2026-07-06 08:30:30', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(53, 1, 1, 'Suduwella', 'Colombo, Colombo, Sri Lanka', '2026-07-11 00:36:41', 'completed', 168.00, 2.10, 'cash', 'car', NULL),
(54, 35, 16, 'My Current Location (Central Library)', 'Colombo, Colombo, Sri Lanka', '2026-07-11 06:32:00', 'scheduled', 168.00, 2.10, 'cash', '0', NULL),
(55, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-11 06:17:17', 'completed', 224.00, 2.80, 'cash', 'car', NULL),
(56, 1, 16, 'Suduwella, Colombo, Colombo, Sri Lanka', 'Colombo, Colombo, Sri Lanka', '2026-07-11 12:41:55', 'completed', 168.00, 2.10, 'cash', 'car', NULL),
(58, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-12 07:33:23', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(59, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-12 07:55:43', 'completed', 216.00, 2.70, 'cash', 'car', NULL),
(60, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-13 00:57:02', 'cancelled', 216.00, 2.70, 'cash', 'car', NULL),
(62, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-13 01:54:18', 'completed', 208.00, 2.60, 'cash', 'car', NULL),
(63, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-13 06:06:03', 'completed', 328.00, 4.10, 'cash', 'car', NULL),
(64, 1, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-13 06:46:46', 'completed', 216.00, 2.70, 'cash', 'car', 3),
(65, 10, 12, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-18 05:01:57', 'completed', 108.00, 2.70, 'cash', 'bike', 3),
(67, 36, 12, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-18 05:21:45', 'completed', 108.00, 2.70, 'cash', 'bike', NULL),
(68, 36, 12, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-07-18 05:23:53', 'completed', 108.00, 2.70, 'cash', 'bike', 3),
(69, 1, 16, 'Mannar Town, Mannar, Sri Lanka', 'Mannar Town, Mannar, Sri Lanka', '2026-08-08 11:15:59', 'completed', 176.00, 2.20, 'cash', 'car', 3),
(70, 1, 16, 'Mannar Town, Mannar, Sri Lanka', 'Mannar Town, Mannar, Sri Lanka', '2026-08-08 11:27:35', 'completed', 176.00, 2.20, 'cash', 'car', 3),
(71, 1, 16, 'Mannar Town, Mannar, Sri Lanka', 'main marta hun', '2026-08-08 11:41:53', 'cancelled', 11680.00, 146.00, 'cash', 'car', NULL),
(72, 2, 1, 'Colombo', 'Kandy', '2026-08-08 09:43:27', 'cancelled', 500.00, 10.00, 'cash', 'car', NULL),
(73, 1, 1, 'Colombo', 'Kandy', '2026-08-08 09:43:38', 'cancelled', 800.00, 10.00, 'cash', 'car', NULL),
(74, 2, 2, 'colombo', 'central medical plaza', '2026-08-12 20:15:23', 'cancelled', 360.00, 4.50, 'cash', NULL, NULL),
(75, 2, 1, 'Thoddaveli, Mannar Town, Mannar, Sri Lanka', 'Mannar Town, Mannar, Sri Lanka', '2026-08-12 20:18:38', 'cancelled', 768.00, 9.60, 'cash', NULL, NULL),
(76, 2, 16, 'Mannar', 'Amarnath Town', '2026-08-15 11:13:57', 'cancelled', 0.00, 0.00, 'cash', 'bike', NULL),
(77, 2, 16, 'mannar', 'mannar town', '2026-08-15 11:27:57', 'cancelled', 0.00, 0.00, 'cash', 'three wheeler', NULL),
(80, 2, 16, 'Your current location', 'Manna Central Library', '2026-08-15 13:57:29', 'completed', 0.00, 0.00, 'cash', 'three-wheeler', NULL),
(81, 2, 16, 'current location', 'Manna Central Library', '2026-08-15 14:00:33', 'completed', 0.00, 0.00, 'cash', 'three-wheeler', NULL),
(83, 36, 16, 'MNR', 'Manna Central Library', '2026-08-17 10:30:00', 'scheduled', 272.00, 3.40, 'cash', '0', NULL),
(84, 2, 16, 'current location', 'Manna library', '2026-08-18 12:15:49', 'cancelled', 0.00, 0.00, 'cash', 'three-wheeler', NULL),
(85, 2, 16, 'current location', 'Mana library', '2026-08-18 14:53:48', 'cancelled', 0.00, 0.00, 'cash', 'three-wheeler', NULL),
(86, 2, 16, 'Mannar Town, Mannar, Sri Lanka', 'Mannar, Sri Lanka', '2026-08-18 17:01:53', 'cancelled', 60.00, 1.00, 'cash', 'three wheeler', NULL),
(87, 2, 16, 'Glen Alpin, Badulla, Badulla, Sri Lanka', 'Badulla, Badulla, Sri Lanka', '2026-08-21 08:28:40', 'completed', 60.00, 1.00, 'cash', 'three wheeler', 3);



-- --------------------------------------------------------

--
-- Table structure for table `ride_requests`
--

CREATE TABLE `ride_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `ride_id` int(11) DEFAULT NULL,
  `user_status` enum('pending','cancelled','completed') DEFAULT 'pending',
  `driver_status` enum('pending','accepted','rejected','arrived') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ride_requests`
--

INSERT INTO `ride_requests` (`id`, `user_id`, `driver_id`, `ride_id`, `user_status`, `driver_status`, `created_at`, `accepted_at`) VALUES
(1, 3, 10, NULL, 'cancelled', 'rejected', '2026-06-21 14:10:00', NULL),
(2, 3, 2, 32, 'cancelled', 'rejected', '2026-06-21 14:18:26', NULL),
(3, 3, 10, 34, 'cancelled', 'rejected', '2026-06-25 10:33:09', NULL),
(4, 7, 10, NULL, 'cancelled', 'rejected', '2026-07-01 11:17:38', NULL),
(5, 7, 10, NULL, 'cancelled', 'rejected', '2026-07-02 10:39:32', NULL),
(6, 7, 10, 37, 'cancelled', 'rejected', '2026-07-02 11:51:12', NULL),
(7, 1, 10, NULL, 'cancelled', 'rejected', '2026-07-02 15:06:03', NULL),
(8, 1, 10, NULL, 'cancelled', 'rejected', '2026-07-02 15:09:32', NULL),
(9, 1, 10, NULL, 'cancelled', 'rejected', '2026-07-02 15:11:16', NULL),
(10, 1, 10, 41, 'pending', 'accepted', '2026-07-03 10:21:40', '2026-07-06 10:38:21'),
(11, 1, 1, 42, 'cancelled', 'rejected', '2026-07-03 12:44:44', NULL),
(12, 1, 9, 44, 'cancelled', 'rejected', '2026-07-06 10:45:41', NULL),
(13, 1, 1, 45, 'pending', 'accepted', '2026-07-06 10:49:58', '2026-07-06 10:50:08'),
(14, 1, 1, 46, 'pending', 'arrived', '2026-07-06 10:58:08', '2026-07-06 10:58:15'),
(15, 1, 1, 47, 'pending', 'arrived', '2026-07-06 11:20:09', '2026-07-06 11:20:19'),
(16, 1, 1, 48, 'completed', 'arrived', '2026-07-06 11:41:30', '2026-07-06 11:41:35'),
(17, 1, 1, 49, 'completed', 'arrived', '2026-07-06 11:48:24', '2026-07-06 11:48:29'),
(18, 1, 1, 50, 'completed', 'arrived', '2026-07-06 11:53:44', '2026-07-06 11:53:58'),
(19, 1, 1, 51, 'completed', 'arrived', '2026-07-06 11:58:29', '2026-07-06 11:58:31'),
(20, 1, 1, 52, 'completed', 'arrived', '2026-07-06 12:00:30', '2026-07-06 12:00:41'),
(21, 1, 1, 53, 'completed', 'arrived', '2026-07-11 04:06:41', '2026-07-11 04:06:50'),
(22, 16, 1, 55, 'completed', 'arrived', '2026-07-11 09:47:17', '2026-07-11 09:47:28'),
(23, 16, 1, 56, 'completed', 'arrived', '2026-07-11 16:11:55', '2026-07-11 16:12:02'),
(24, 16, 35, NULL, 'cancelled', 'pending', '2026-07-12 11:01:59', NULL),
(25, 16, 1, 58, 'completed', 'arrived', '2026-07-12 11:03:23', '2026-07-12 11:03:37'),
(26, 16, 1, 59, 'completed', 'arrived', '2026-07-12 11:25:43', '2026-07-12 11:25:54'),
(27, 16, 1, 60, 'pending', 'accepted', '2026-07-13 04:27:02', '2026-07-13 04:27:06'),
(28, 16, 35, NULL, 'cancelled', 'pending', '2026-07-13 05:22:15', NULL),
(29, 16, 1, 62, 'completed', 'arrived', '2026-07-13 05:24:18', '2026-07-13 05:24:27'),
(30, 16, 1, 63, 'completed', 'arrived', '2026-07-13 09:36:03', '2026-07-13 09:45:25'),
(31, 16, 1, 64, 'completed', 'arrived', '2026-07-13 10:16:46', '2026-07-13 10:16:56'),
(32, 12, 10, 65, 'completed', 'arrived', '2026-07-18 08:31:57', '2026-07-18 08:33:04'),
(33, 12, 33, NULL, 'cancelled', 'pending', '2026-07-18 08:49:27', NULL),
(34, 12, 36, 67, 'completed', 'arrived', '2026-07-18 08:51:45', '2026-07-18 08:51:53'),
(35, 12, 36, 68, 'completed', 'arrived', '2026-07-18 08:53:53', '2026-07-18 08:53:57'),
(36, 16, 1, 69, 'completed', 'arrived', '2026-08-08 14:45:59', '2026-08-08 14:47:18'),
(37, 16, 1, 70, 'completed', 'arrived', '2026-08-08 14:57:35', '2026-08-08 14:57:41'),
(38, 16, 1, 71, 'pending', 'accepted', '2026-08-08 15:11:53', '2026-08-08 15:12:44'),
(39, 1, 1, 73, 'pending', 'rejected', '2026-08-08 15:13:38', NULL),
(40, 16, 10, 76, 'pending', 'pending', '2026-08-15 14:43:57', NULL),
(41, 16, 2, 77, 'pending', 'accepted', '2026-08-15 14:57:57', '2026-08-15 17:17:41'),
(42, 16, 10, NULL, 'cancelled', 'pending', '2026-08-15 16:04:05', NULL),
(43, 16, 1, NULL, 'cancelled', 'pending', '2026-08-15 17:16:26', NULL),
(44, 16, 1, 80, 'completed', 'pending', '2026-08-15 17:27:29', NULL),
(45, 16, 1, 81, 'completed', 'pending', '2026-08-15 17:30:33', NULL),
(46, 16, 10, 76, 'pending', 'pending', '2026-08-15 17:54:02', NULL),
(47, 16, 10, 76, 'pending', 'pending', '2026-08-15 17:54:03', NULL),
(48, 16, 10, 76, 'pending', 'pending', '2026-08-15 17:54:03', NULL),
(49, 16, 10, 76, 'pending', 'pending', '2026-08-15 17:54:03', NULL),
(50, 16, 10, 76, 'pending', 'pending', '2026-08-15 17:54:04', NULL),
(51, 16, 10, 76, 'pending', 'pending', '2026-08-15 17:54:04', NULL),
(52, 16, 10, 76, 'pending', 'accepted', '2026-08-15 17:54:04', '2026-08-16 14:03:30'),
(53, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:08', NULL),
(54, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:09', NULL),
(55, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:09', NULL),
(56, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:09', NULL),
(57, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:10', NULL),
(58, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:10', NULL),
(59, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:18', NULL),
(60, 1, 10, 75, 'pending', 'pending', '2026-08-15 17:54:18', NULL),
(61, 1, 3, 75, 'pending', 'pending', '2026-08-15 17:58:07', NULL),
(62, 1, 3, 75, 'pending', 'pending', '2026-08-15 17:58:11', NULL),
(63, 1, 3, 75, 'pending', 'pending', '2026-08-15 17:58:12', NULL),
(64, 1, 3, 75, 'pending', 'pending', '2026-08-15 17:58:12', NULL),
(65, 1, 3, 75, 'pending', 'accepted', '2026-08-15 17:58:12', '2026-08-21 11:57:15'),
(66, 2, 3, 74, 'pending', 'pending', '2026-08-15 17:58:32', NULL),
(67, 2, 3, 74, 'pending', 'pending', '2026-08-15 17:58:32', NULL),
(68, 2, 3, 74, 'pending', 'pending', '2026-08-15 17:58:33', NULL),
(69, 2, 3, 74, 'pending', 'pending', '2026-08-15 17:58:33', NULL),
(70, 2, 3, 74, 'pending', 'pending', '2026-08-15 17:58:33', NULL),
(71, 2, 3, 74, 'completed', 'arrived', '2026-08-15 17:58:33', NULL),
(72, 16, 1, NULL, 'cancelled', 'pending', '2026-08-15 18:58:09', NULL),
(73, 16, 1, 84, 'pending', 'pending', '2026-08-18 15:45:49', NULL),
(74, 16, 1, 85, 'pending', 'pending', '2026-08-18 18:23:48', NULL),
(75, 16, 2, 86, 'pending', 'accepted', '2026-08-18 20:31:53', '2026-08-19 14:48:22'),
(76, 16, 2, 87, 'completed', 'arrived', '2026-08-21 11:58:40', '2026-08-21 11:59:58');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `admin_id` int(11) NOT NULL,
  `sos_alert` tinyint(1) DEFAULT 1,
  `ride_alert` tinyint(1) DEFAULT 1,
  `driver_alert` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `theme` varchar(20) DEFAULT 'light',
  `refresh_rate` int(11) DEFAULT 5,
  `sos_enabled` tinyint(1) DEFAULT 1,
  `tracking_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `smtp_host` varchar(255) DEFAULT 'smtp.gmail.com',
  `smtp_port` int(11) DEFAULT 465,
  `smtp_user` varchar(255) DEFAULT '',
  `smtp_pass` varchar(255) DEFAULT '',
  `smtp_secure` varchar(50) DEFAULT 'ssl',
  `twilio_sid` varchar(255) DEFAULT NULL,
  `twilio_token` varchar(255) DEFAULT NULL,
  `twilio_from` varchar(50) DEFAULT NULL,
  `rate_bike` decimal(10,2) DEFAULT 40.00,
  `rate_three_wheeler` decimal(10,2) DEFAULT 60.00,
  `rate_car` decimal(10,2) DEFAULT 80.00,
  `rate_van` decimal(10,2) DEFAULT 100.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `admin_id`, `sos_alert`, `ride_alert`, `driver_alert`, `email_notifications`, `theme`, `refresh_rate`, `sos_enabled`, `tracking_enabled`, `created_at`, `updated_at`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_secure`, `twilio_sid`, `twilio_token`, `twilio_from`, `rate_bike`, `rate_three_wheeler`, `rate_car`, `rate_van`) VALUES
(1, 1, 1, 1, 1, 1, 'light', 5, 1, 1, '2026-06-20 14:00:38', '2026-07-11 04:23:13', 'smtp.gmail.com', 587, 'jemysparrow@gmail.com', 'TJyZ2nm5Lj2AypSIfyPOi4IFFQfbzDz4QactBvH7xnzr1ihy1WLOmSIGaLFgLq163u6eidX0W+oeeJwjtB1v+4GZQTdcj9c709Lf4BLDsiA=', 'ssl', NULL, NULL, NULL, 40.00, 60.00, 80.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `driver_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'none',
  `expires_at` date DEFAULT NULL,
  `last_payment_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 29.99,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `warning_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `driver_id`, `status`, `expires_at`, `last_payment_date`, `amount`, `created_at`, `updated_at`, `warning_sent`) VALUES
(1, 1, 'active', '2026-09-19', '2026-07-17', 3000.00, '2026-06-20 15:28:38', '2026-07-17 13:08:29', 0),
(2, 2, 'active', '2026-09-15', '2026-07-17', 1500.00, '2026-06-20 15:28:38', '2026-07-17 13:26:43', 0),
(3, 3, 'expired', '2026-07-16', '2026-06-16', 200.00, '2026-06-20 15:28:38', '2026-07-20 09:57:30', 1),
(4, 9, 'expired', '2026-07-18', '2026-06-18', 200.00, '2026-06-20 15:28:38', '2026-07-21 12:04:56', 1),
(5, 10, 'active', '2026-08-29', '2026-07-30', 1500.00, '2026-06-20 15:28:38', '2026-07-30 12:01:39', 0),
(6, 33, 'expired', '2026-07-20', '2026-06-20', 300.00, '2026-06-20 15:28:38', '2026-07-30 11:56:13', 1),
(7, 35, 'expired', '2026-07-04', '2026-08-04', 300.00, '2026-07-04 12:06:47', '2026-07-11 04:08:48', 1),
(9, 36, 'active', '2026-08-17', '2026-07-18', 1500.00, '2026-07-11 17:38:52', '2026-07-18 08:51:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `profile_image`, `status`, `location`, `created_at`, `phone`, `password_hash`) VALUES
(1, 'John', 'Doe', 'john@gmail.com', NULL, 'active', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-05-27 13:38:35', '0771234567', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(2, 'Sarah', 'Smith', 'sarah@gmail.com', NULL, 'active', 'Kandy', '2026-03-10 09:36:15', '0719876543', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(3, 'Mike', 'Johnson', 'mike@gmail.com', NULL, 'active', 'Galle', '2026-03-24 02:54:00', '0775689154', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(7, 'Jemy', '', 'jemy@gmail.com', NULL, 'active', 'Mannar', '2026-06-08 13:34:32', '0719871548', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(8, 'Vinoth', 'fernando', 'vinoth@gmail.com', NULL, 'active', 'Ampara', '2026-06-08 13:51:06', '0775689154', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(12, 'David', 'Millar', 'david@gmail.com', NULL, 'active', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-07-03 11:29:05', '0771234567', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(16, 'David', 'Millar', 'abcd@gmail.com', NULL, 'active', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-07-03 11:34:00', '0771234567', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(17, 'kulam', 'Kabil', 'kabil@gmail.com', NULL, 'active', NULL, '2026-07-03 11:38:09', '0754567890', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(18, 'kamal', 'rajini', 'kamalrajini@gmail.com', NULL, 'active', 'Glen Alpin, Badulla, Badulla, Sri Lanka', '2026-07-12 12:14:20', '0774589621', '$2y$12$Gx/PA/ZJHUlAT7N2FWk.tOMJORO0ydqDSECrJYBLgQL2gpgfsbfE.'),
(19, 'Jannat', 'Edward', 'jannatedward@gmail.com', NULL, NULL, NULL, '2026-08-15 13:29:39', '0772679515', '$2y$10$Uj2yqMVqmAD7zu52rdSubOIUmPY0B0iLjtxcA44mv3z36yjxN6XDe');



-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','ride','payment','system') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 16, 'Ride Booked', 'Your ride from Mannar Town, Mannar, Sri Lanka to Mannar Town, Mannar, Sri Lanka has been booked. Fare: LKR 176.00. Status: Pending.', 'ride', 1, '2026-08-08 14:45:59'),
(2, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-08 14:47:18'),
(3, 16, 'Ride Booked', 'Your ride from Mannar Town, Mannar, Sri Lanka to Mannar Town, Mannar, Sri Lanka has been booked. Fare: LKR 176.00. Status: Pending.', 'ride', 1, '2026-08-08 14:57:35'),
(4, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-08 14:57:41'),
(5, 16, 'Ride Booked', 'Your ride from Mannar Town, Mannar, Sri Lanka to main marta hun has been booked. Fare: LKR 11,680.00. Status: Pending.', 'ride', 1, '2026-08-08 15:11:53'),
(6, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-08 15:12:44'),
(7, 1, 'Ride Booked', 'Test', 'ride', 0, '2026-08-08 15:13:38'),
(8, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 1, '2026-08-08 15:16:14'),
(9, 16, 'Ride Booked', 'Your ride from Mannar to Amarnath Town has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-15 14:43:57'),
(10, 16, 'Ride Booked', 'Your ride from mannar to mannar town has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-15 14:57:57'),
(11, 16, 'Ride Booked', 'Your ride from Central Library to Mannar Town has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-15 16:04:05'),
(12, 16, 'Ride Cancelled', 'Your ride has been cancelled successfully.', 'warning', 1, '2026-08-15 16:04:37'),
(13, 16, 'Ride Booked', 'Your ride from Your current location to Manna Central Library has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-15 17:16:26'),
(14, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-15 17:17:41'),
(15, 16, 'Ride Cancelled', 'Your ride has been cancelled successfully.', 'warning', 1, '2026-08-15 17:26:05'),
(16, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 1, '2026-08-15 17:26:17'),
(17, 16, 'Ride Booked', 'Your ride from Your current location to Manna Central Library has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-15 17:27:29'),
(18, 16, 'Ride Booked', 'Your ride from current location to Manna Central Library has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-15 17:30:33'),
(19, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-15 17:42:15'),
(20, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-15 17:51:42'),
(21, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 1, '2026-08-15 17:54:05'),
(22, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 1, '2026-08-15 17:54:06'),
(23, 1, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-15 17:58:13'),
(24, 1, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-15 17:58:21'),
(25, 2, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-15 17:58:34'),
(26, 2, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-15 17:58:35'),
(27, 1, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-15 17:58:37'),
(28, 1, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-15 17:58:38'),
(29, 2, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-15 17:58:40'),
(30, 2, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-15 17:58:41'),
(31, 1, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-15 17:58:44'),
(32, 1, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-15 17:58:46'),
(33, 16, 'Ride Booked', 'Your ride from Colombo to Kandy has been booked. Fare: LKR 80.00. Status: Pending.', 'ride', 1, '2026-08-15 18:58:09'),
(34, 16, 'Ride Cancelled', 'Your ride has been cancelled successfully.', 'warning', 1, '2026-08-15 19:01:05'),
(35, 16, 'Ride Cancelled', 'Your ride has been cancelled successfully.', 'warning', 1, '2026-08-16 15:44:33'),
(36, 16, 'Ride Booked', 'Your ride from current location to Manna library has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-18 15:45:49'),
(37, 16, 'Ride Booked', 'Your ride from current location to Mana library has been booked. Fare: LKR 0.00. Status: Pending.', 'ride', 1, '2026-08-18 18:23:48'),
(38, 16, 'Ride Booked', 'Your ride from Mannar Town, Mannar, Sri Lanka to Mannar, Sri Lanka has been booked. Fare: LKR 60.00. Status: Pending.', 'ride', 0, '2026-08-18 20:31:53'),
(39, 16, 'Ride Booked', 'Your ride from Glen Alpin, Badulla, Badulla, Sri Lanka to Badulla, Badulla, Sri Lanka has been booked. Fare: LKR 60.00. Status: Pending.', 'ride', 0, '2026-08-21 11:58:40'),
(40, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-21 11:59:58'),
(41, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-21 12:00:55'),
(42, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-21 12:00:58'),
(43, 16, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride', 0, '2026-08-21 12:01:04'),
(44, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-21 12:01:05'),
(45, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-21 12:01:09'),
(46, 16, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-21 12:01:13'),
(47, 1, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning', 0, '2026-08-21 12:01:17');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `driver_id` int(11) NOT NULL,
  `vehicle_number` varchar(50) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `vehicle_brand` varchar(100) DEFAULT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `vehicle_color` varchar(50) DEFAULT NULL,
  `year_manufacture` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `driver_id`, `vehicle_number`, `vehicle_type`, `vehicle_brand`, `vehicle_model`, `vehicle_color`, `year_manufacture`, `created_at`, `updated_at`) VALUES
(1, 1, 'CAB-4521', 'Car', NULL, NULL, NULL, NULL, '2026-06-20 14:16:50', '2026-06-20 14:16:50'),
(2, 2, 'WP-BAA-8899', 'Three Wheeler', NULL, NULL, NULL, NULL, '2026-06-20 14:16:50', '2026-06-20 14:16:50'),
(3, 3, 'NC-7788', 'Van', NULL, NULL, NULL, NULL, '2026-06-20 14:16:50', '2026-06-20 14:16:50'),
(4, 9, 'CBV-0989', 'Car', NULL, NULL, NULL, NULL, '2026-06-20 14:16:50', '2026-06-20 14:16:50'),
(5, 10, 'CBV-9857', 'Bike', NULL, NULL, NULL, NULL, '2026-06-20 14:16:50', '2026-06-20 14:16:50'),
(8, 33, 'CBV-0989', 'Bike', NULL, NULL, NULL, NULL, '2026-06-20 15:24:40', '2026-06-20 15:24:40'),
(11, 35, 'CAA-1234', 'Car', 'Toyota', 'Prius', 'White', 2020, '2026-07-04 11:54:46', '2026-07-04 11:54:46'),
(14, 36, 'CAA-1234', 'bike', 'TVS', 'GAM', 'Black', 2020, '2026-07-11 17:38:05', '2026-07-11 17:38:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_notifications`
--
--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `driver_cards`
--
ALTER TABLE `driver_cards`
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD UNIQUE KEY `driver_id` (`driver_id`);

--
-- Indexes for table `driver_notifications`
--
ALTER TABLE `driver_notifications`
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `driver_otps`
--
ALTER TABLE `driver_otps`
  ADD KEY `phone` (`phone`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD KEY `ride_id` (`ride_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `rides`
--
ALTER TABLE `rides`
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ride_requests`
--
ALTER TABLE `ride_requests`
  ADD KEY `fk_requests_user` (`user_id`),
  ADD KEY `fk_requests_driver` (`driver_id`),
  ADD KEY `fk_requests_ride` (`ride_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD UNIQUE KEY `admin_id` (`admin_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD UNIQUE KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD UNIQUE KEY `driver_id` (`driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `driver_cards`
--
ALTER TABLE `driver_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_documents`
--
ALTER TABLE `driver_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `driver_notifications`
--
ALTER TABLE `driver_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `driver_otps`
--
ALTER TABLE `driver_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `rides`
--
ALTER TABLE `rides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `ride_requests`
--
ALTER TABLE `ride_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_cards`
--
ALTER TABLE `driver_cards`
  ADD CONSTRAINT `driver_cards_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD CONSTRAINT `fk_driver_documents_drivers` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rides`
--
ALTER TABLE `rides`
  ADD CONSTRAINT `rides_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ride_requests`
--
ALTER TABLE `ride_requests`
  ADD CONSTRAINT `fk_requests_driver` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_requests_ride` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
