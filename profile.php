<?php
session_start(); // Start the session to access user id

// Include your database connection file
include 'php/db.php'; // Make sure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user personal information
$userSql = "SELECT name, email FROM user WHERE userid = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$stmt->close();

// Fetch user profile information
// 'initial_weight' is NOT selected here because your database doesn't have it.
$profileSql = "SELECT height, current_weight, target_weight, age, gender, dietary_restrictions, fitness_goal, activity_level, bmi, daily_calories FROM user_profile WHERE userid = ?";
$stmt = $conn->prepare($profileSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$profileResult = $stmt->get_result();
$profileData = $profileResult->fetch_assoc();
$stmt->close();

// Close the database connection
$conn->close();

// Helper function to format dietary restrictions
function formatDietaryRestrictions($restrictions) {
    if (empty($restrictions)) return [];

    $restrictionMap = [
        'vegan' => 'Vegan',
        'vegetarian' => 'Vegetarian',
        'gluten_free' => 'Gluten Free',
        'dairy_free' => 'Dairy Free',
        'low_carb' => 'Low Carb',
        'low_fat' => 'Low Fat',
        'halal' => 'Halal',
        'kosher' => 'Kosher'
    ];

    $restrictionList = explode(',', $restrictions);
    $formatted = [];

    foreach ($restrictionList as $restriction) {
        $restriction = trim($restriction);
        // Only add if not empty after trimming
        if (!empty($restriction)) {
             $formatted[] = isset($restrictionMap[$restriction]) ? $restrictionMap[$restriction] : ucfirst(str_replace('_', ' ', $restriction));
        }
    }
    return $formatted;
}

// Helper function to get activity level description
function getActivityLevelDescription($level) {
    $levels = [
        1 => 'Sedentary (little or no exercise)',
        2 => 'Lightly active (light exercise/sports 1-3 days/week)',
        3 => 'Moderately active (moderate exercise/sports 3-5 days/week)',
        4 => 'Very active (hard exercise/sports 6-7 days a week)',
        5 => 'Extra active (very hard exercise/sports & physical job)'
    ];

    return isset($levels[$level]) ? $levels[$level] : 'Unknown';
}

// Helper function to get BMI category
function getBMICategory($bmi) {
    if ($bmi < 18.5) return ['category' => 'Underweight', 'color' => '#3498db']; // Blue
    if ($bmi < 25) return ['category' => 'Normal weight', 'color' => '#2ecc71']; // Green
    if ($bmi < 30) return ['category' => 'Overweight', 'color' => '#f39c12']; // Orange
    return ['category' => 'Obese', 'color' => '#e74c3c']; // Red
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - DailyDish</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts - Poppins for headings/logo, Nunito for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Global Styles & Variables (Consistent with DailyDish theme) */
        :root {
            --primary-accent: #FF8800; /* Your main orange */
            --secondary-accent: #FFB22C; /* Lighter orange */
            --dark-text: #333333;
            --light-text: #666666;
            --background-light: #FDFDFD; /* Lighter background for elements */
            --background-darker: #F7F7F7; /* Main page background */
            --border-color: #E0E0E0;

            --border-radius-base: 8px;
            --border-radius-large: 12px;
            --box-shadow-light: 0 4px 10px rgba(0,0,0,0.05);
            --box-shadow-medium: 0 8px 20px rgba(0,0,0,0.08);
            --box-shadow-heavy: 0 10px 25px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif; /* Consistent body font */
            background-color: var(--background-darker);
            min-height: 100vh;
            color: var(--dark-text);
            display: flex; /* Use flex for main layout */
            flex-direction: column;
        }

        /* Navbar (re-using styles from index.php/mealplanner.php) */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%;
            background-color: var(--background-light);
            box-shadow: var(--box-shadow-light);
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid var(--border-color);
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-text);
            font-weight: 600;
            transition: color 0.3s ease-in-out;
        }

        .nav-links a:hover {
            color: var(--primary-accent);
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
        }

        .logo span {
            color: var(--primary-accent);
        }

        .nav-icon {
            position: relative;
            cursor: pointer;
            padding: 8px;
            border-radius: var(--border-radius-base);
            transition: background-color 0.2s;
        }

        .nav-icon:hover {
            background-color: var(--border-color);
        }

        .nav-icon i {
            font-size: 22px;
            color: var(--primary-accent);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 45px;
            background-color: var(--background-light);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-radius: var(--border-radius-base);
            overflow: hidden;
            z-index: 100;
            min-width: 150px;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }

        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-menu a,
        .dropdown-menu button {
            display: block;
            padding: 0.8rem 1.2rem;
            text-decoration: none;
            color: var(--dark-text);
            transition: background 0.3s ease-in-out;
            font-weight: 400;
            width: 100%;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-menu a:hover,
        .dropdown-menu button:hover {
            background-color: var(--background-darker);
            color: var(--primary-accent);
        }


        /* Profile Page Specific Styles */
        .page-header {
            background-color: var(--primary-accent);
            color: white;
            padding: 40px 5%;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            box-shadow: inset 0 -5px 15px rgba(0,0,0,0.1); /* Inner shadow */
        }

        .page-header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-header .user-name {
            font-size: 1.5em;
            opacity: 0.9;
        }

        .profile-wrapper {
            display: flex;
            max-width: 1400px;
            margin: 30px auto;
            background: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            overflow: hidden; /* Ensures rounded corners on children */
            min-height: 70vh; /* Ensure it takes up enough vertical space */
        }

        /* Sidebar Navigation */
        .sidebar {
            flex: 0 0 280px; /* Fixed width sidebar */
            background-color: white;
            padding: 30px 0;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .sidebar h3 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-accent);
            font-size: 1.3em;
            padding: 0 30px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            flex-grow: 1; /* Allows navigation to expand */
        }

        .sidebar-nav li {
            margin-bottom: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 30px;
            text-decoration: none;
            color: var(--dark-text);
            font-weight: 600;
            font-size: 1.05em;
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent;
        }

        .sidebar-nav a i {
            color: var(--light-text);
            font-size: 1.1em;
            transition: color 0.2s ease-in-out;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: var(--background-darker);
            color: var(--primary-accent);
            border-left-color: var(--primary-accent);
        }

        .sidebar-nav a:hover i,
        .sidebar-nav a.active i {
            color: var(--primary-accent);
        }

        /* Profile Content Area */
        .profile-content-area {
            flex-grow: 1;
            padding: 40px;
            position: relative; /* For smooth scrolling anchors */
            overflow-y: auto; /* Enable scrolling for content if it overflows */
            scroll-behavior: smooth; /* Smooth scroll */
        }

        .profile-section {
            background: var(--background-darker);
            border-radius: var(--border-radius-large);
            padding: 30px;
            margin-bottom: 30px; /* Space between sections */
            box-shadow: var(--box-shadow-light);
            border-left: 5px solid var(--secondary-accent); /* Accent border */
        }

        .profile-section:last-child {
            margin-bottom: 0;
        }

        .profile-section h2 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-accent);
            margin-bottom: 25px;
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 15px;
        }

        .profile-section h2 i {
            color: var(--dark-text);
            font-size: 1.2em;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-text);
            flex: 1;
            font-size: 1.05em;
        }

        .info-value {
            font-weight: 500;
            color: var(--light-text);
            text-align: right;
            flex: 1;
            font-size: 1.05em;
        }

        .bmi-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-end; /* Align to the right */
        }

        .bmi-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
        }

        .dietary-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .dietary-tag {
            background-color: var(--secondary-accent);
            color: white;
            padding: 7px 15px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-base);
            text-align: center;
            border: 1px solid var(--border-color);
            box-shadow: var(--box-shadow-light);
            transition: transform 0.2s ease;
        }
        .stat-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8em;
            font-weight: 700;
            color: var(--primary-accent);
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 0.9em;
            color: var(--light-text);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            justify-content: center;
            padding-bottom: 20px; /* Space at the bottom */
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1em;
            box-shadow: var(--box-shadow-light);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
            color: white;
        }

        .btn-secondary {
            background: var(--background-light);
            color: var(--primary-accent);
            border: 2px solid var(--primary-accent);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        }

        .no-data {
            text-align: center;
            color: var(--light-text);
            font-style: italic;
            padding: 30px;
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius-large);
            margin-top: 20px;
        }
        .no-data h3 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-accent);
            margin-bottom: 10px;
        }
        .no-data p {
            margin-bottom: 20px;
        }

        .progress-bar {
            background: var(--border-color);
            border-radius: var(--border-radius-base);
            height: 10px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(90deg, #2ecc71, #28a745); /* Green gradient for progress */
            height: 100%;
            border-radius: var(--border-radius-base);
            transition: width 0.5s ease;
        }

        /* Footer (consistent with other pages) */
        footer {
            text-align: center;
            padding: 2rem;
            background-color: var(--background-light);
            border-top: 1px solid var(--border-color);
            color: var(--light-text);
            font-size: 0.9rem;
            margin-top: auto; /* Pushes footer to the bottom */
        }


        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-wrapper {
                flex-direction: column; /* Stack sidebar and content */
                margin: 20px;
            }

            .sidebar {
                flex: none; /* Remove fixed width */
                width: 100%; /* Full width */
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                padding: 20px 0;
            }
            .sidebar h3 {
                padding: 0 20px 15px;
                text-align: center;
            }
            .sidebar-nav {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 5px;
                padding: 0 10px;
            }
            .sidebar-nav li {
                margin-bottom: 0;
                flex: 1 1 auto; /* Allow items to grow/shrink */
                max-width: 180px; /* Limit individual nav item width */
            }
            .sidebar-nav a {
                padding: 10px 15px;
                font-size: 0.95em;
                justify-content: center; /* Center text and icon */
                border-left: none; /* Remove left border */
                border-bottom: 3px solid transparent; /* Add bottom border for active state */
            }
            .sidebar-nav a:hover,
            .sidebar-nav a.active {
                border-left-color: transparent; /* Remove left border hover effect */
                border-bottom-color: var(--primary-accent); /* Activate bottom border */
            }

            .profile-content-area {
                padding: 30px;
            }

            .profile-section {
                padding: 25px;
                margin-bottom: 25px;
            }
            .profile-section h2 {
                font-size: 1.5em;
                margin-bottom: 20px;
                flex-direction: column;
                text-align: center;
            }
            .profile-section h2 i {
                margin-bottom: 10px;
            }
        }

        @media (max-width: 768px) {
            .navbar .nav-links {
                display: none; /* Hide main nav links on smaller screens */
            }
            .page-header {
                padding: 30px 5%;
            }
            .page-header h1 {
                font-size: 2.2em;
            }
            .page-header .user-name {
                font-size: 1.1em;
            }
            .profile-content-area {
                padding: 20px;
            }
            .profile-section {
                padding: 20px;
            }
            .profile-section h2 {
                font-size: 1.3em;
            }
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 0;
            }
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
            .info-value {
                width: 100%;
                text-align: left;
            }
            .bmi-indicator {
                justify-content: flex-start;
            }
            .stats-grid {
                grid-template-columns: 1fr; /* Stack stats vertically */
                gap: 10px;
            }
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            .btn {
                width: 100%;
                max-width: 250px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar (consistent with other DailyDish pages) -->
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="recipe.php">Recipes</a>
            <a href="mealplanner.php">Meal Planner</a>
            <a href="php/calories_tracking.php">Calories Tracking</a>
            <a href="about.php">About Us</a>
        </div>
        <div class="logo">Daily<span>Dish</span></div>
        <div class="nav-icon" id="profileIcon">
            <i class="fa-solid fa-user"></i>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php">Profile</a>
                <button onclick="window.location.href='php/logout.php'">Logout</button>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1>Your Profile</h1>
        <?php if ($userData): ?>
            <div class="user-name">Welcome, <?php echo htmlspecialchars($userData['name']); ?>!</div>
        <?php endif; ?>
    </div>

    <div class="profile-wrapper">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <h3>Profile Sections</h3>
            <ul class="sidebar-nav">
                <li><a href="#personal-info" class="sidebar-link active"><i class="fas fa-user-circle"></i> Personal Information</a></li>
                <li><a href="#body-stats" class="sidebar-link"><i class="fas fa-ruler-vertical"></i> Body Statistics</a></li>
                <li><a href="#health-goals" class="sidebar-link"><i class="fas fa-bullseye"></i> Health Goals</a></li>
                <?php
                // Only show dietary preferences link if there are restrictions or if profile data exists
                $dietaryRestrictions = ($profileData && !empty($profileData['dietary_restrictions'])) ? formatDietaryRestrictions($profileData['dietary_restrictions']) : [];
                if ($profileData || !empty($dietaryRestrictions)):
                ?>
                <li><a href="#dietary-prefs" class="sidebar-link"><i class="fas fa-carrot"></i> Dietary Preferences</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Main Profile Content Area -->
        <div class="profile-content-area">
            <?php if ($userData): ?>
                <!-- Personal Information Section -->
                <div id="personal-info" class="profile-section">
                    <h2><i class="fas fa-info-circle"></i> Personal Information</h2>
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['email']); ?></span>
                    </div>
                    <?php if ($profileData): ?>
                        <div class="info-item">
                            <span class="info-label">Age</span>
                            <span class="info-value"><?php echo htmlspecialchars($profileData['age'] ?? 'N/A'); ?> years</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender</span>
                            <span class="info-value"><?php echo htmlspecialchars(ucfirst($profileData['gender'] ?? 'N/A')); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="no-data-inline">No additional personal details found. Please complete your profile.</p>
                    <?php endif; ?>
                </div>

                <?php if ($profileData): ?>
                    <!-- Body Statistics Section -->
                    <div id="body-stats" class="profile-section">
                        <h2><i class="fas fa-weight-scale"></i> Body Statistics</h2>
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-value"><?php echo htmlspecialchars($profileData['height'] ?? 'N/A'); ?></div>
                                <div class="stat-label">Height (cm)</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value"><?php echo htmlspecialchars($profileData['current_weight'] ?? 'N/A'); ?></div>
                                <div class="stat-label">Current Weight (kg)</div>
                            </div>
                            <?php if ($profileData['bmi'] !== null && $profileData['bmi'] !== ''): ?>
                                <?php $bmiInfo = getBMICategory($profileData['bmi']); ?>
                                <div class="stat-box">
                                    <div class="stat-value"><?php echo htmlspecialchars(number_format((float)$profileData['bmi'], 1)); ?></div>
                                    <div class="stat-label">BMI</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($profileData['bmi'] !== null && $profileData['bmi'] !== ''): ?>
                            <div class="info-item">
                                <span class="info-label">BMI Category</span>
                                <span class="info-value bmi-indicator">
                                    <span class="bmi-dot" style="background-color: <?php echo htmlspecialchars($bmiInfo['color']); ?>"></span>
                                    <?php echo htmlspecialchars($bmiInfo['category']); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="info-item">
                            <span class="info-label">Activity Level</span>
                            <span class="info-value"><?php echo htmlspecialchars(getActivityLevelDescription($profileData['activity_level'] ?? 0)); ?></span>
                        </div>
                    </div>

                    <!-- Goals Section -->
                    <div id="health-goals" class="profile-section">
                        <h2><i class="fas fa-bullseye"></i> Health Goals</h2>
                        <div class="info-item">
                            <span class="info-label">Fitness Goal</span>
                            <span class="info-value"><?php echo htmlspecialchars(str_replace('_', ' ', ucwords($profileData['fitness_goal'] ?? 'N/A', '_'))); ?></span>
                        </div>

                        <?php
                        $currentWeight = $profileData['current_weight'];
                        $targetWeight = $profileData['target_weight'];
                        $progressPercent = 0;
                        $showProgressBar = false;

                        // Check if essential data for progress calculation is available
                        if ($targetWeight !== null && $targetWeight > 0 && $currentWeight !== null && $currentWeight > 0) {
                            $showProgressBar = true;

                            if ($profileData['fitness_goal'] == 'weight_loss') {
                                if ($currentWeight <= $targetWeight) {
                                    // Goal met or surpassed
                                    $progressPercent = 100;
                                } else {
                                    // If current weight is still above target, calculate progress
                                    // This heuristic aims to show a bar that fills as current_weight approaches target_weight.
                                    // It assumes a starting point for the bar's display, which is higher than current_weight.
                                    // A simple way to get a "journey" length without an actual initial_weight:
                                    // Consider the starting point for the bar to be a fixed percentage above the target weight,
                                    // or current weight if current is higher than that calculated start.

                                    // Let's define the bar's "max starting point" as target + a fixed difference
                                    // This makes the bar consistent for a given target, regardless of how high current_weight is
                                    $fixedMaxDifference = 50; // e.g., max 50kg difference to track for the bar's length
                                    $barStartWeight = $targetWeight + $fixedMaxDifference;

                                    // Ensure the actual starting point for the bar is at least the current weight
                                    // This prevents the bar from showing 100% if current is just slightly above target
                                    if ($currentWeight > $barStartWeight) {
                                        $barStartWeight = $currentWeight;
                                    }

                                    $totalJourneyLength = $barStartWeight - $targetWeight;
                                    $currentProgressLength = $barStartWeight - $currentWeight;

                                    if ($totalJourneyLength > 0) {
                                        $progressPercent = ($currentProgressLength / $totalJourneyLength) * 100;
                                    } else {
                                        $progressPercent = 100; // Should not happen if totalJourneyLength is calculated correctly
                                    }
                                }
                            } elseif ($profileData['fitness_goal'] == 'weight_gain') {
                                if ($currentWeight >= $targetWeight) {
                                    // Goal met or surpassed
                                    $progressPercent = 100;
                                } else {
                                    // Similar for weight gain: bar fills as current_weight approaches target_weight
                                    $fixedMaxDifference = 50; // e.g., max 50kg difference to track for the bar's length
                                    $barStartWeight = $targetWeight - $fixedMaxDifference;

                                    // Ensure the actual starting point for the bar is at most the current weight
                                    if ($currentWeight < $barStartWeight) {
                                        $barStartWeight = $currentWeight;
                                    }

                                    $totalJourneyLength = $targetWeight - $barStartWeight;
                                    $currentProgressLength = $currentWeight - $barStartWeight;

                                    if ($totalJourneyLength > 0) {
                                        $progressPercent = ($currentProgressLength / $totalJourneyLength) * 100;
                                    } else {
                                        $progressPercent = 100;
                                    }
                                }
                            } else { // Fitness goal is 'maintenance' or not recognized
                                $progressPercent = 100; // For maintenance, goal is generally always "met" in terms of progress bar
                            }

                            // Clamp the percentage between 0 and 100
                            $progressPercent = max(0, min(100, $progressPercent));
                        }
                        ?>

                        <div class="info-item">
                            <span class="info-label">Target Weight</span>
                            <span class="info-value"><?php echo htmlspecialchars($targetWeight ?? 'N/A'); ?> kg</span>
                        </div>

                        <?php if ($showProgressBar): ?>
                            <div class="info-item">
                                <span class="info-label">Progress to Goal</span>
                                <span class="info-value"><?php echo number_format($progressPercent, 1); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progressPercent; ?>%"></div>
                            </div>
                        <?php else: ?>
                            <p class="no-data-inline">No target weight or progress data available.</p>
                        <?php endif; ?>

                        <?php
                        // Fetch the daily_calories from user_profile.
                        // Apply the default if it's null or 0.
                        $dailyCaloriesGoal = $profileData['daily_calories'] ?? null;
                        if (empty($dailyCaloriesGoal)) { // This handles 0, null, or empty string
                             $dailyCaloriesGoal = 2000; // Default value as in calories_tracking.php
                        }
                        ?>
                        <div class="info-item">
                            <span class="info-label">Daily Calorie Goal</span>
                            <span class="info-value"><?php echo htmlspecialchars($dailyCaloriesGoal); ?> kcal</span>
                        </div>
                    </div>

                    <!-- Dietary Restrictions Section -->
                    <?php
                    $dietaryRestrictions = formatDietaryRestrictions($profileData['dietary_restrictions'] ?? '');
                    if (!empty($dietaryRestrictions)):
                    ?>
                        <div id="dietary-prefs" class="profile-section">
                            <h2><i class="fas fa-utensils"></i> Dietary Preferences</h2>
                            <div class="dietary-tags">
                                <?php
                                foreach ($dietaryRestrictions as $restriction) {
                                    echo '<span class="dietary-tag">' . htmlspecialchars($restriction) . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div id="dietary-prefs" class="profile-section">
                            <h2><i class="fas fa-utensils"></i> Dietary Preferences</h2>
                            <p class="no-data-inline">No specific dietary preferences set.</p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="profile-section no-profile-data">
                        <div class="no-data">
                            <h3>Complete Your Profile!</h3>
                            <p>It looks like your profile information is missing. Please click the button below to add your health and dietary details to get the most out of DailyDish!</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="php/edit_profile.php" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Profile</a>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Back to Home</a>
                </div>

            <?php else: ?>
                <div class="profile-section full-width">
                    <div class="no-data">
                        <h3>Profile Not Found</h3>
                        <p>We couldn't retrieve your user profile information. This might be due to an issue with your account or the database.</p>
                        <p>Please try logging in again or contact support if the problem persists.</p>
                        <div class="action-buttons">
                            <a href="index.php" class="btn btn-primary">Back to Home</a>
                            <a href="php/logout.php" class="btn btn-secondary">Log Out</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div> <!-- End profile-content-area -->
    </div> <!-- End profile-wrapper -->

    <footer>
        &copy; <?= date('Y') ?> DailyDish. All rights reserved.
    </footer>

    <script>
        // Dropdown menu functionality
        document.getElementById('profileIcon').addEventListener('click', function (e) {
            e.stopPropagation();
            var menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            var menu = document.getElementById('dropdownMenu');
            if (menu.classList.contains('show') && !document.getElementById('profileIcon').contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        document.getElementById('dropdownMenu').addEventListener('click', function (e) {
            e.stopPropagation();
        });

        // Smooth scrolling for sidebar navigation
        document.querySelectorAll('.sidebar-link').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent default jump

                // Remove 'active' from all links
                document.querySelectorAll('.sidebar-link').forEach(link => {
                    link.classList.remove('active');
                });
                // Add 'active' to the clicked link
                this.classList.add('active');

                const targetId = this.getAttribute('href').substring(1); // Get target ID from href
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const headerOffset = document.querySelector('.navbar').offsetHeight; // Height of fixed navbar
                    const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                    const offsetPosition = elementPosition - headerOffset - 20; // 20px extra padding

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });
                }
            });
        });

        // Optional: Update active sidebar link on scroll
        // This is a more advanced feature and can be tricky with fixed headers.
        // For simplicity, I'm omitting it initially, but if you want it, let me know.
        // It involves using Intersection Observer API or checking scroll position frequently.

        // Ensure the first link is active on page load
        document.addEventListener('DOMContentLoaded', () => {
            const firstLink = document.querySelector('.sidebar-nav .sidebar-link');
            if (firstLink) {
                firstLink.classList.add('active');
            }
        });
    </script>
</body>
</html>