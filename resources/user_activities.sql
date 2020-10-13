-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2020 at 08:54 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dev_srepfrelndbmn2`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `id` bigint(20) NOT NULL COMMENT 'Log ID (Auto Increment)',
  `uidx` bigint(20) DEFAULT NULL COMMENT 'User Index ID',
  `user_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Funcation user did',
  `url` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Current URL',
  `controller_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'user access controller FK to controller table',
  `action_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'user perform action FK to action table',
  `parameters` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL Parameters',
  `status` int(11) NOT NULL COMMENT 'success-1,fail - 0',
  `ip_addr` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP Address ',
  `date_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Date and time of the activity',
  `time_diff` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'difference of login and logout',
  `imi` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mobile imi number',
  `dev_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mobile device name',
  `ua_browser` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User''s Web Browser',
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mobile gps location latitude',
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mobile gps location longitude',
  `branch_id` bigint(20) NOT NULL COMMENT 'Branch id FK to branch table',
  `modified_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'updated at'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `UIDX` (`uidx`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Log ID (Auto Increment)';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `bi_branch` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
