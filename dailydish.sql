-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Jun 24, 2025 at 09:37 AM
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
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
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
  `meal_type` enum('breakfast','lunch','dinner','snack') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `meal_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_planner`
--

INSERT INTO `meal_planner` (`plannerid`, `userid`, `recipeid`, `meal_type`, `day_of_week`, `meal_date`, `created_at`) VALUES
(14, 1, 36, 'breakfast', 'friday', '2025-06-20', '2025-06-20 07:26:03'),
(15, 1, 37, 'snack', 'monday', '2025-06-16', '2025-06-20 07:26:21'),
(16, 1, 40, 'lunch', 'wednesday', '2025-06-18', '2025-06-20 07:26:40'),
(17, 1, 12, 'breakfast', 'thursday', '2025-06-19', '2025-06-20 07:26:55'),
(18, 1, 21, 'lunch', 'sunday', '2025-06-15', '2025-06-20 07:27:13'),
(19, 1, 32, 'dinner', 'wednesday', '2025-06-18', '2025-06-20 07:27:50'),
(20, 9, 27, 'lunch', 'tuesday', '2025-06-24', '2025-06-24 09:06:36'),
(21, 9, 36, 'dinner', 'tuesday', '2025-06-24', '2025-06-24 09:11:26'),
(22, 9, 12, 'breakfast', 'tuesday', '2025-06-24', '2025-06-24 09:11:41');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipeid` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `calories` int NOT NULL,
  `prep_time` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ingredients` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` enum('breakfast','lunch','dinner','snack') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipeid`, `name`, `photo`, `description`, `calories`, `prep_time`, `ingredients`, `instructions`, `category`, `created_at`) VALUES
(11, 'Roti Canai with Curry Dhal', 'B1.jpg', 'Flaky Malaysian flatbread served with aromatic lentil curry', 380, '45 minutes', 'Plain flour, ghee\nsalt, water\nred lentils\n turmeric, cumin\nonion, garlic, curry leaves', 'Mix flour with ghee and water, knead and rest\nRoll thin, cook on griddle.\nBoil lentils with spices and aromatics until creamy.', 'breakfast', '2025-06-20 06:26:53'),
(12, 'Full English Breakfast', 'B2.jpg', 'Traditional hearty British breakfast with all the classics', 650, '25 minutes', 'Bacon, eggs\nsausages, baked beans\nblack pudding, grilled tomatoes, mushrooms, toast', 'Grill bacon and sausages, fry eggs, heat beans, grill tomatoes and mushrooms, toast bread.', 'breakfast', '2025-06-20 06:28:38'),
(13, 'Nasi Lemak', 'B3.png', ' Malaysia\'s national dish - coconut rice with sambal, anchovies, and accompaniments', 420, '35 minutes', 'Jasmine rice, coconut milk\npandan leaves, dried anchovies\npeanuts, cucumber\nhard-boiled egg, sambal', 'Cook rice with coconut milk and pandan.\nFry anchovies and peanuts. \nServe with fresh cucumber, egg, and spicy sambal.', 'breakfast', '2025-06-20 06:35:18'),
(14, 'English Crumpets with Jam', 'B4.jpg', 'Soft, holey griddle cakes perfect for absorbing butter and jam', 280, '20 minutes', 'Strong flour, yeast, milk, water, salt\nbutter, strawberry jam', 'Make batter with flour, yeast, and liquids.\nCook in crumpet rings on griddle\nServe warm with butter and jam.', 'breakfast', '2025-06-20 06:36:11'),
(15, 'Char Kway Teow', 'B5.jpg', 'Stir-fried rice noodles with dark soy sauce, prawns, and Chinese sausage', 450, '15 minutes', 'Fresh rice noodles, prawns,\n sausage,\neggs, bean sprouts\nchives, dark soy sauce, light soy sauce', 'Heat wok, stir-fry noodles with sauces, add proteins and vegetables, toss over high heat.', 'breakfast', '2025-06-20 06:37:28'),
(16, 'Porridge with Golden Syrup', 'B6.jpg', 'Creamy English oats served with traditional golden syrup', 320, '12 minutes', 'Rolled oats, milk, water, salt\ngolden syrup, butter', 'Cook oats with milk and water until creamy.\nStir in butter, serve with golden syrup drizzled on top.', 'breakfast', '2025-06-20 06:38:24'),
(17, 'Mee Goreng', 'B7.jpg', 'Spicy stir-fried yellow noodles with vegetables and protein', 390, '18 minutes', 'Yellow noodles, tofu, prawns, eggs,\n bean sprouts, tomato, chili paste\nsweet soy sauce, lime', 'Stir-fry noodles with chili paste\nadd proteins and vegetables, season with sauces, garnish with lime.', 'breakfast', '2025-06-20 06:39:23'),
(18, 'Kedgeree', 'B8.jpg', ' Anglo-Indian rice dish with smoked fish, eggs, and curry spices', 410, '30 minutes', 'Basmati rice, smoked haddock, hard-boiled eggs\nbutter, onion, curry powder, parsley, cream', ' Cook rice separately\nFlake fish, sauté onion with curry powder, combine with rice\nchopped eggs', 'breakfast', '2025-06-20 06:40:53'),
(19, 'Dim Sum Selection', 'B9.jpg', 'Assorted Malaysian-Chinese steamed dumplings and buns', 350, ' 60 minutes', ' Dumpling wrappers\nprawns, chicken, mushrooms,\nsoy sauce, sesame oil, char siu bun dough', 'Prepare various fillings\nwrap in dough\nsteam in bamboo baskets for 12-15 minutes', 'breakfast', '2025-06-20 06:42:01'),
(20, 'Cornish Pasty', 'L8.jpg', ' Pastry filled with beef, potato, and vegetables', 520, ' 45 minutes', 'Shortcrust pastry\nbeef skirt\npotato, swede\nonion, butter, egg wash', 'Make pastry\ndice filling ingredients\nwrap in pastry\ncrimp edges, brush with egg\n bake until golden', 'lunch', '2025-06-20 06:47:04'),
(21, 'Rendang with Rice', 'L7.jpg', ' Slow-cooked spicy beef in coconut curry paste', 580, '2 hours', 'Beef chuck, coconut milk,\nrendang paste, galangal\nlemongrass\n tamarind\npalm sugar, jasmine rice', 'Slow-cook beef with spices and coconut milk until tender and dry.\nServe with steamed rice', 'lunch', '2025-06-20 06:48:57'),
(22, ' Ploughman\'s Lunch', 'L6.jpg', ' Traditional British cold meal with cheese, bread, and pickles', 420, '10 minutes', 'Mature cheddar, crusty bread\nham, pickled onions\nchutney, apple\nhard-boiled egg, lettuce', 'Arrange all components on a plate\nServe with butter and additional chutney on the side', 'lunch', '2025-06-20 06:50:17'),
(23, 'Hokkien Mee', 'L5.jpeg', 'Dark, rich noodle dish with prawns and pork in thick sauce', 450, ' 25 minutes', 'Fresh yellow noodles, prawns\nsquid, dark soy sauce\negg, bean sprouts, lime', 'Stir-fry noodles with proteins, add dark soy sauce for color, finish with egg and bean sprouts.', 'lunch', '2025-06-20 06:51:29'),
(24, 'Shepherd\'s Pie', 'L4.jpg', 'Comfort food classic with minced lamb and mashed potato topping', 480, ' 50 minutes', 'Ground lamb, onions, carrots, peas\nWorcestershire sauce, tomato paste\npotatoes, butter, milk', 'Brown lamb with vegetables\nadd seasonings.\nTop with mashed potatoes, bake until golden', 'lunch', '2025-06-20 06:52:33'),
(25, 'Laksa Penang', 'L3.jpg', ' Tangy, spicy noodle soup with fish and tamarind broth', 380, ' 45 minutes', 'Rice noodles, mackerel, tamarind paste\nchili paste, pineapple\ncucumber, mint, torch ginger flower', 'Prepare spicy-sour broth with fish and tamarind.\n Serve over noodles with fresh herbs and vegetables.', 'lunch', '2025-06-20 06:53:36'),
(26, 'Fish and Chips', 'L2.jpg', 'Classic British battered fish with chunky chips', 680, '35 minutes', 'White fish fillets, plain flour\nbaking powder,\npotatoes\nvegetable oil, mushy peas', ' Make batter with flour and beer\nCoat fish, deep fry until golden\nServe with thick-cut chips and mushy peas', 'lunch', '2025-06-20 06:54:47'),
(27, ' Chicken Curry with Rice', 'L1.jpg', 'Rich Malaysian curry with tender chicken in coconut milk', 520, '40 minutes', 'Chicken thighs\ncoconut milk\ncurry paste\n lemongrass, galangal\nlime leaves\njasmine rice', 'Fry curry paste, add chicken, coconut milk, and aromatics\nSimmer until tender. Serve with steamed rice.', 'lunch', '2025-06-20 06:56:08'),
(28, ' Tom Yum Soup with Prawns', 'D1.jpeg', ' Light, aromatic Thai-Malaysian soup packed with vegetables and lean protein', 180, ' 30 minutes', 'Prawns, lemongrass, galangal\n lime leaves, mushrooms, tomatoes, chili\nlime juice, fish sauce', 'Make aromatic broth with herbs, add vegetables and prawns, season with lime juice and fish sauce', 'dinner', '2025-06-20 07:07:48'),
(29, 'Poached Salmon with Steamed Vegetables', 'D2.jpg', 'Delicate poached salmon with seasonal British vegetables', 285, ' 20 minutes', 'Salmon fillet\nleeks, carrots, broccoli\n cauliflower, fresh dill\n lemon, olive oil', 'Poach salmon in seasoned water with lemon\nSteam vegetables until tender-crisp. Serve with herbs and lemon', 'dinner', '2025-06-20 07:08:45'),
(30, 'Herb-Crusted Chicken with Roasted Vegetables', 'D3.jpeg', 'Lean chicken breast with mixed British herbs and colorful roasted vegetables\r\n', 340, ' 35 minutes', 'Chicken breast\n rosemary, thyme\nparsley, sweet potatoes\n Brussels sprouts, carrots, olive oil', 'Coat chicken with herbs, bake with seasoned vegetables until chicken reaches 165°F and vegetables are tender', 'dinner', '2025-06-20 07:09:41'),
(31, 'Malaysian Fish Curry (Light Version)', 'D4.jpg', 'Aromatic fish curry made with light coconut milk and plenty of vegetables', 290, '25 minutes', 'White fish, light coconut milk\ncurry paste, eggplant, okra\n tomatoes, curry leaves, brown rice', 'Make curry base, add vegetables first, then fish\nSimmer gently until fish is cooked\nServe with brown rice', 'dinner', '2025-06-20 07:10:35'),
(32, 'Grilled Lamb with Mint and Quinoa', 'D5.jpeg', 'Lean grilled lamb with fresh mint sauce and protein-rich quinoa', 380, '30 minutes', 'Lamb leg steaks, fresh mint\nquinoa, cucumber\npeas, lemon, olive oil, garlic', ' Marinate lamb with herbs, grill to medium\nCook quinoa, mix with peas and cucumber\nServe with mint sauce', 'dinner', '2025-06-20 07:11:45'),
(33, ' Steamed Whole Fish with Ginger', 'D6.jpg', ' Traditional Chinese-Malaysian steamed fish with aromatic ginger and soy', 220, '20 minutes', 'Whole fish\n ginger, spring onions\nlight soy sauce, sesame oil\ncilantro, shiitake mushrooms', 'Steam fish with ginger slices for 12-15 minutes\nHeat oil, pour over fish with soy sauce and herbs.', 'dinner', '2025-06-20 07:12:35'),
(34, ' Roasted Vegetable and Lentil Curry', 'D7.jpg', ' Hearty vegetarian curry with roasted vegetables and protein-rich lentils', 310, '40 minutes', 'Red lentils, butternut squash\n cauliflower, spinach\ncoconut milk\ncurry spices, brown rice', 'Roast vegetables, cook lentils with spices, combine with coconut milk and spinach\nServe with brown rice', 'dinner', '2025-06-20 07:13:29'),
(35, 'Grilled Chicken Satay Salad', 'D8.jpg', 'Deconstructed satay with grilled chicken over fresh mixed greens', 280, '25 minutes', 'Chicken breast, mixed greens\ncucumber, bean sprouts\n peanut dressing (light), \nlime\nherbs', 'Marinate and grill chicken with satay spices\nServe sliced over salad with light peanut dressing', 'dinner', '2025-06-20 07:14:27'),
(36, ' Baked Cod with Asian Greens', 'D9.jpg', 'Flaky white fish baked with nutritious Asian vegetables\r\n', 250, '18 minutes', 'Cod fillet\n bok choy\nChinese broccoli\n ginger, garlic\nlight soy sauce, sesame oil, brown rice', ' Bake cod with ginger and garlic\n Stir-fry greens quickly with minimal oil\nServe with brown rice.', 'dinner', '2025-06-20 07:15:30'),
(37, ' Fresh Fruit Rojak', 'S1.png', ' Malaysian fruit salad with tangy tamarind dressing and crushed peanuts', 120, ' 15 minutes', ' Pineapple, apple, cucumber\nbean sprouts\ntofu, tamarind water, palm sugar, peanuts, chili', 'Cut fruits and vegetables\n make light tamarind dressing\ntoss together with crushed peanuts', 'snack', '2025-06-20 07:16:35'),
(38, 'Cucumber Tea Sandwiches', 'S2.jpg', 'Light English finger sandwiches with fresh cucumber', 85, ' 10 minutes', 'Whole grain bread, cucumber, cream cheese (light)\nfresh dill\nblack pepper', 'Slice cucumber thinly\nspread light cream cheese on bread\nlayer cucumber with herbs\n cut into fingers', 'snack', '2025-06-20 07:17:38'),
(39, 'Steamed Sweet Potato with Coconut', 'S3.jpg', 'Natural sweet potato with fresh grated coconut and a pinch of salt', 110, '20 minute', 'Orange sweet potato\nfresh coconut\nsea salt, pandan leaves ', 'Steam sweet potato until tender\nserve with fresh grated coconut and a pinch of sea salt', 'snack', '2025-06-20 07:18:26'),
(40, ' Oatcakes with Hummus', 'S4.jpeg', ' Scottish oatcakes topped with homemade chickpea hummus', 140, '15 minutes', 'Oatcakes, chickpeas\ntahini, lemon juice, garlic\n olive oil, paprika', 'Blend chickpeas with tahini lemon\n and garlic to make hummus. Serve on oatcakes with paprika', 'snack', '2025-06-20 07:19:27'),
(41, 'Mixed Nuts and Dried Fruits', 'S5.jpeg', 'Malaysian-style trail mix with tropical dried fruits', 160, '5 minutes', 'Almonds, cashews\ndried mango, dried pineapple\n pumpkin seeds\n coconut flakes', 'Mix all ingredients in equal portions\nStore in airtight container\nServe in small portions', 'snack', '2025-06-20 07:20:18'),
(42, 'Greek Yogurt with Berries', 'S6.jpeg', 'Protein-rich yogurt topped with fresh British berries', 95, '5 minutes', 'Greek yogurt (low-fat)\nstrawberries\nblueberries, raspberries\n honey (minimal)\nmint', 'Layer yogurt with fresh berries\ndrizzle with small amount of honey\ngarnish with mint', 'snack', '2025-06-20 07:21:21'),
(43, ' Vegetable Spring Rolls (Fresh)', 'S7.jpeg', 'Light rice paper rolls filled with fresh vegetables and herb', 80, '20 minutes', 'Rice paper, lettuce\ncarrot, cucumber\n mint, cilantro\nbean sprouts, rice noodles, peanut dipping sauce (light)', 'Soften rice paper\n fill with vegetables and herbs\nroll tightly, serve with light peanut sauce', 'snack', '2025-06-20 07:22:23'),
(44, 'Apple Slices with Almond Butter', 'S8.jpg', 'Crisp English apples with natural almond butter', 130, '5 minutes', ' Green apples, natural almond butter (unsweetened)\ncinnamon', 'Slice apples, serve with small portion of almond butter for dipping\nsprinkle with cinnamon', 'snack', '2025-06-20 07:23:10'),
(45, 'Seaweed Snack with Cherry Tomatoes', 'S9.jpeg', ' Nutrient-rich roasted seaweed with fresh tomatoes', 45, '5 minutes', 'Roasted seaweed sheets, cherry tomatoes\n sesame seeds\nlight soy sauce for dipping', 'Arrange seaweed and tomatoes on plate, sprinkle with sesame seeds\nserve with light soy sauce', 'snack', '2025-06-20 07:23:57');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userid` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `has_completed_preferences` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userid`, `name`, `email`, `password`, `has_completed_preferences`) VALUES
(1, 'AMIRUL QAYYUM', 'qayyum010614@gmail.com', '$2y$10$DgpvpESAzaXRVuYtTIaR4.Y3uQwzQBCNxpMhvlbzbPQe3RmkQVzMu', 1),
(2, 'qayyum', 'amirul@gmail.com', '$2y$10$zmRw6PeOPXWXL3A0nesdeuO.zo7t2VvvI3BSVPUzOEBMkpaxwytV2', 0),
(3, 'Ahmad Buhra', 'bush@gmail.com', '$2y$10$KiA2eacWXaLxmt3o5h7.4uZE/zkkr.Pv5d5eZ64v/luq/UN9Ixw.i', 1),
(4, 'ahmad', 'ahmad@gmail.com', '$2y$10$NrdfBHHv/95xGhV6mtVYruvWlRmxvOraMYGNjy0ClGZbwchw.1kba', 0),
(5, 'aqil', 'aqil@gmail.com', '$2y$10$caWkgUG4g3qI/KdTBUC0k.nTLeoJNqycI2CswuLRCjyorxZ4FWlF6', 1),
(6, 'puteri', 'puteri@gmail.com', '$2y$10$0e0WcYOM/PYm/qIxcTYsCOCLEqgz4I8wR9jvFjymPrhUWqkEgNd5C', 1),
(7, 'ammar', 'ammar@gmail.com', '$2y$10$XReHmNQR.EWoVkKPYDfcYeA0F5iLTnYJLPRMaTAaWdXJikLt0SHMq', 0),
(8, 'yum', 'yum@gmail.com', '$2y$10$Cem7bAu9of.ZX4E/koHmD.yZd3Es9iXt/8tZx8SxfpoiOeE9.frGG', 1),
(9, 'Puteri Nur Izzati', 'puteri123@gmail.com', '$2y$12$rIxAVxVqEu5KjYRF7c3SfuZ6b2weRUujVzCRWg8zvdM058EN8zU2u', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `preference_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
(4, 8, 'daily_calories', '1904', '2025-05-14 18:11:03', '2025-05-14 18:11:03'),
(6, 9, 'daily_calories', '1446', '2025-06-24 08:39:19', '2025-06-24 08:39:19');

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
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dietary_restrictions` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fitness_goal` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
(1, 1, 100.30, 60.00, 55.00, 25, 'male', 'dairy_free,low_carb', '0', 2, 59.00, 1700, '2025-05-08 12:36:18', '2025-06-20 07:59:22'),
(2, 3, 178.00, 90.00, 75.00, 26, 'male', 'vegan,gluten_free,dairy_free,low_carb', 'weight_gain', 2, NULL, NULL, '2025-05-10 12:03:16', '2025-05-10 12:03:16'),
(3, 5, 190.00, 67.00, 90.00, 25, 'male', 'halal', 'weight_gain', 2, 18.56, 2747, '2025-05-13 18:57:02', '2025-05-13 18:57:02'),
(4, 6, 165.00, 56.00, 50.00, 22, 'female', 'halal', 'weight_loss', 2, 20.57, 1452, '2025-05-13 19:02:12', '2025-05-13 19:02:12'),
(5, 8, 189.00, 67.00, 75.00, 25, 'male', 'halal', 'weight_loss', 2, 18.00, 1904, '2025-05-14 18:11:03', '2025-05-14 18:11:04'),
(6, 9, 160.00, 60.00, 55.00, 25, 'female', '', 'weight_loss', 2, 23.44, 1446, '2025-06-24 08:39:19', '2025-06-24 08:39:19');

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
  MODIFY `plannerid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipeid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_profile`
--
ALTER TABLE `user_profile`
  MODIFY `profileid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
