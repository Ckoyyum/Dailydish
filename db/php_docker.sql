-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Jun 17, 2025 at 06:56 AM
-- Server version: 9.3.0
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_docker`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminid` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminid`, `email`, `password`) VALUES
(2, 'boss@gmail.com', '$2y$10$W1QOYvHBVXDL92U5W/PA8epyy2QOxfCp7CFpmeSHVKBD.yPeKFrle');

-- --------------------------------------------------------

--
-- Table structure for table `meal_planner`
--

CREATE TABLE `meal_planner` (
  `plannerid` int NOT NULL,
  `userid` int NOT NULL,
  `recipeid` int NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') COLLATE utf8mb4_general_ci NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_general_ci NOT NULL,
  `meal_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_planner`
--

INSERT INTO `meal_planner` (`plannerid`, `userid`, `recipeid`, `meal_type`, `day_of_week`, `meal_date`, `created_at`) VALUES
(3, 5, 1, 'dinner', 'friday', '2025-05-09', '2025-05-14 19:29:10'),
(4, 5, 1, 'breakfast', 'thursday', '2025-05-15', '2025-05-14 19:55:54'),
(5, 5, 3, 'dinner', 'friday', '2025-05-16', '2025-05-14 20:46:16'),
(6, 5, 3, 'lunch', 'friday', '2025-05-16', '2025-05-14 20:47:02'),
(7, 5, 3, 'breakfast', 'friday', '2025-05-16', '2025-05-14 20:57:40'),
(8, 5, 3, 'breakfast', 'saturday', '2025-05-17', '2025-05-14 20:59:51'),
(10, 6, 3, 'lunch', 'saturday', '2025-05-17', '2025-05-14 22:56:56'),
(11, 6, 5, 'lunch', 'friday', '2025-05-16', '2025-05-14 23:14:16'),
(12, 6, 4, 'lunch', 'thursday', '2025-05-29', '2025-05-24 07:52:23'),
(13, 1, 2, 'breakfast', 'saturday', '2025-05-24', '2025-05-24 07:55:23');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipeid` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `calories` int NOT NULL,
  `prep_time` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `ingredients` text COLLATE utf8mb4_general_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_general_ci NOT NULL,
  `category` enum('breakfast','lunch','dinner','snack') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipeid`, `name`, `photo`, `description`, `calories`, `prep_time`, `ingredients`, `instructions`, `category`, `created_at`) VALUES
(1, 'ddddddddddddddddddddddd', 'b-1.jpg', 'ddddddddddddddddddd', 567, 'dddddddddddd', 'ddddddddddddd', 'ddddddddddd', 'breakfast', '2025-05-12 21:09:22'),
(2, 'amirul', 'WhatsApp Image 2025-05-13 at 01.57.26_0c8fd26a(1).jpg', 'sssssssssss', 456, 'ddddddddddddd', 'sssssssssssssssssssssssssssssssssssssssss', 'ssssssssssssssssssssssssssssssssssssssss', 'breakfast', '2025-05-12 22:15:32'),
(3, 'Toast with Egg', 'b-1.jpg', 'Toast and Egg is a classic, quick, and satisfying breakfast that’s perfect for busy mornings or a light meal anytime.', 250, '10 minutes', '1 slice of bread\n1 egg\n1 tsp butter or oil\nSalt\nPepper\nOptional: Cheese, chives, avocado', 'Toast the bread.\nFry or scramble the egg.\nSeason with salt and pepper.\nServe on toast.', 'breakfast', '2025-05-13 00:18:35'),
(4, 'Creamy Garlic Chicken with Rice', '', 'Creamy Garlic Chicken with Rice is a comforting and flavorful dinner.', 600, '30 minute', 'Combine\nCombine\nCombine\nCombine', 'Combine\nCombine\nCombine\nCombine', 'breakfast', '2025-05-13 00:30:30'),
(5, 'puteri', '', 'sedap', 678, '80 min', 'fyy', 'hhhh', 'breakfast', '2025-05-14 23:12:39'),
(6, 'amirul', '', 'kkkkkkkkkkkkk', 4656, 'qf', 'fffffff', 'ffff', 'breakfast', '2025-05-14 23:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userid` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `has_completed_preferences` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userid`, `name`, `email`, `password`, `has_completed_preferences`) VALUES
(1, 'amirul', 'qayyum010614@gmail.com', '$2y$10$DgpvpESAzaXRVuYtTIaR4.Y3uQwzQBCNxpMhvlbzbPQe3RmkQVzMu', 1),
(2, 'qayyum', 'amirul@gmail.com', '$2y$10$zmRw6PeOPXWXL3A0nesdeuO.zo7t2VvvI3BSVPUzOEBMkpaxwytV2', 0),
(3, 'Ahmad Buhra', 'bush@gmail.com', '$2y$10$KiA2eacWXaLxmt3o5h7.4uZE/zkkr.Pv5d5eZ64v/luq/UN9Ixw.i', 1),
(4, 'ahmad', 'ahmad@gmail.com', '$2y$10$NrdfBHHv/95xGhV6mtVYruvWlRmxvOraMYGNjy0ClGZbwchw.1kba', 0),
(5, 'aqil', 'aqil@gmail.com', '$2y$10$caWkgUG4g3qI/KdTBUC0k.nTLeoJNqycI2CswuLRCjyorxZ4FWlF6', 1),
(6, 'puteri', 'puteri@gmail.com', '$2y$10$0e0WcYOM/PYm/qIxcTYsCOCLEqgz4I8wR9jvFjymPrhUWqkEgNd5C', 1),
(7, 'ammar', 'ammar@gmail.com', '$2y$10$XReHmNQR.EWoVkKPYDfcYeA0F5iLTnYJLPRMaTAaWdXJikLt0SHMq', 0),
(8, 'yum', 'yum@gmail.com', '$2y$10$Cem7bAu9of.ZX4E/koHmD.yZd3Es9iXt/8tZx8SxfpoiOeE9.frGG', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `preference_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `preference_name`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 'daily_calorie_goal', '2000', '2025-05-13 09:21:25', '2025-05-13 09:21:25'),
(2, 5, 'daily_calories', '2747', '2025-05-13 18:57:02', '2025-05-13 18:57:02'),
(3, 6, 'daily_calories', '1452', '2025-05-13 19:02:12', '2025-05-13 19:02:12'),
(4, 8, 'daily_calories', '1904', '2025-05-14 18:11:03', '2025-05-14 18:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `profileid` int NOT NULL,
  `userid` int NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `current_weight` decimal(5,2) NOT NULL,
  `target_weight` decimal(5,2) DEFAULT NULL,
  `age` int NOT NULL,
  `gender` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `dietary_restrictions` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fitness_goal` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `activity_level` int NOT NULL DEFAULT '3',
  `bmi` decimal(5,2) DEFAULT NULL,
  `daily_calories` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`profileid`, `userid`, `height`, `current_weight`, `target_weight`, `age`, `gender`, `dietary_restrictions`, `fitness_goal`, `activity_level`, `bmi`, `daily_calories`, `created_at`, `updated_at`) VALUES
(1, 1, 100.30, 38.30, 55.00, 24, 'male', 'gluten_free,dairy_free,low_fat', 'weight_gain', 2, NULL, NULL, '2025-05-08 12:36:18', '2025-05-08 12:41:46'),
(2, 3, 178.00, 90.00, 75.00, 26, 'male', 'vegan,gluten_free,dairy_free,low_carb', 'weight_gain', 2, NULL, NULL, '2025-05-10 12:03:16', '2025-05-10 12:03:16'),
(3, 5, 190.00, 67.00, 90.00, 25, 'male', 'halal', 'weight_gain', 2, 18.56, 2747, '2025-05-13 18:57:02', '2025-05-13 18:57:02'),
(4, 6, 165.00, 56.00, 50.00, 22, 'female', 'halal', 'weight_loss', 2, 20.57, 1452, '2025-05-13 19:02:12', '2025-05-13 19:02:12'),
(5, 8, 189.00, 67.00, 75.00, 25, 'male', 'halal', 'weight_loss', 2, 18.00, 1904, '2025-05-14 18:11:03', '2025-05-14 18:11:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `meal_planner`
--
ALTER TABLE `meal_planner`
  ADD PRIMARY KEY (`plannerid`),
  ADD UNIQUE KEY `unique_meal` (`userid`,`meal_date`,`meal_type`),
  ADD KEY `recipeid` (`recipeid`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipeid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`preference_name`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`profileid`),
  ADD UNIQUE KEY `userid` (`userid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `meal_planner`
--
ALTER TABLE `meal_planner`
  MODIFY `plannerid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipeid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_profile`
--
ALTER TABLE `user_profile`
  MODIFY `profileid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meal_planner`
--
ALTER TABLE `meal_planner`
  ADD CONSTRAINT `meal_planner_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_planner_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipes` (`recipeid`) ON DELETE CASCADE;

--
-- Constraints for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
