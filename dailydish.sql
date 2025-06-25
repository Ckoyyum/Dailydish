-- --------------------------------------------------------
-- Table: admin
-- --------------------------------------------------------
CREATE TABLE `admin` (
  `adminid` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`adminid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` (`adminid`, `email`, `password`) VALUES
(2, 'boss@gmail.com', '$2y$10$W1QOYvHBVXDL92U5W/PA8epyy2QOxfCp7CFpmeSHVKBD.yPeKFrle');

-- --------------------------------------------------------
-- Table: recipes
-- --------------------------------------------------------
CREATE TABLE `recipes` (
  `recipeid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `calories` int(11) NOT NULL,
  `prep_time` varchar(50) NOT NULL,
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `category` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recipeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recipes` (`recipeid`, `name`, `photo`, `description`, `calories`, `prep_time`, `ingredients`, `instructions`, `category`, `created_at`) VALUES
(1, 'ddddddddddddddddddddddd', 'b-1.jpg', 'ddddddddddddddddddd', 567, 'dddddddddddd', 'ddddddddddddd', 'ddddddddddd', 'breakfast', '2025-05-13 05:09:22'),
(2, 'amirul', 'WhatsApp Image 2025-05-13 at 01.57.26_0c8fd26a(1).jpg', 'sssssssssss', 456, 'ddddddddddddd', 'sssssssssssssssssssssssssssssssssssssssss', 'ssssssssssssssssssssssssssssssssssssssss', 'breakfast', '2025-05-13 06:15:32'),
(3, 'Toast with Egg', 'b-1.jpg', 'Toast and Egg is a classic, quick, and satisfying breakfast that’s perfect for busy mornings or a light meal anytime.', 250, '10 minutes', '1 slice of bread\n1 egg\n1 tsp butter or oil\nSalt\nPepper\nOptional: Cheese, chives, avocado', 'Toast the bread.\nFry or scramble the egg.\nSeason with salt and pepper.\nServe on toast.', 'breakfast', '2025-05-13 08:18:35'),
(4, 'Creamy Garlic Chicken with Rice', '', 'Creamy Garlic Chicken with Rice is a comforting and flavorful dinner.', 600, '30 minute', 'Combine\nCombine\nCombine\nCombine', 'Combine\nCombine\nCombine\nCombine', 'breakfast', '2025-05-13 08:30:30');

-- --------------------------------------------------------
-- Table: user
-- --------------------------------------------------------
CREATE TABLE `user` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `has_completed_preferences` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`userid`, `name`, `email`, `password`, `has_completed_preferences`) VALUES
(1, 'amirul', 'qayyum010614@gmail.com', '$2y$10$DgpvpESAzaXRVuYtTIaR4.Y3uQwzQBCNxpMhvlbzbPQe3RmkQVzMu', 1),
(2, 'qayyum', 'amirul@gmail.com', '$2y$10$zmRw6PeOPXWXL3A0nesdeuO.zo7t2VvvI3BSVPUzOEBMkpaxwytV2', 0),
(3, 'Ahmad Buhra', 'bush@gmail.com', '$2y$10$KiA2eacWXaLxmt3o5h7.4uZE/zkkr.Pv5d5eZ64v/luq/UN9Ixw.i', 1),
(4, 'ahmad', 'ahmad@gmail.com', '$2y$10$NrdfBHHv/95xGhV6mtVYruvWlRmxvOraMYGNjy0ClGZbwchw.1kba', 0);

-- --------------------------------------------------------
-- Table: user_preferences
-- --------------------------------------------------------
CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preference_name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`preference_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_preferences` (`id`, `user_id`, `preference_name`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 'daily_calorie_goal', '2000', '2025-05-13 17:21:25', '2025-05-13 17:21:25');

-- --------------------------------------------------------
-- Table: user_profile
-- --------------------------------------------------------
CREATE TABLE `user_profile` (
  `profileid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `current_weight` decimal(5,2) NOT NULL,
  `target_weight` decimal(5,2) DEFAULT NULL,
  `age` int(3) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `dietary_restrictions` varchar(255) DEFAULT NULL,
  `fitness_goal` varchar(50) NOT NULL,
  `activity_level` int(1) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`profileid`),
  UNIQUE KEY `userid` (`userid`),
  FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_profile` (`profileid`, `userid`, `height`, `current_weight`, `target_weight`, `age`, `gender`, `dietary_restrictions`, `fitness_goal`, `activity_level`, `created_at`, `updated_at`) VALUES
(1, 1, 100.30, 38.30, 55.00, 24, 'male', 'gluten_free,dairy_free,low_fat', 'weight_gain', 2, '2025-05-08 20:36:18', '2025-05-08 20:41:46'),
(2, 3, 178.00, 90.00, 75.00, 26, 'male', 'vegan,gluten_free,dairy_free,low_carb', 'weight_gain', 2, '2025-05-10 20:03:16', '2025-05-10 20:03:16');
