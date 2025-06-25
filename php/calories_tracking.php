<?php
session_start();
include 'db.php'; // Ensure your database connection is included

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header('Location: login.php'); // Or your login page
    exit();
}

$user_id = $_SESSION['user_id'];

// Set default week offset if not set
if (!isset($_SESSION['week_offset'])) {
    $_SESSION['week_offset'] = 0;
}

// Set default view (daily or weekly)
if (!isset($_SESSION['calorie_view'])) {
    $_SESSION['calorie_view'] = 'weekly';
}

// Handle week navigation
if (isset($_GET['week'])) {
    if ($_GET['week'] === 'prev') {
        $_SESSION['week_offset']--;
    } elseif ($_GET['week'] === 'next') {
        $_SESSION['week_offset']++;
    } elseif ($_GET['week'] === 'reset') {
        $_SESSION['week_offset'] = 0;
    }
}

// Handle view toggle
if (isset($_GET['view'])) {
    if ($_GET['view'] === 'daily' || $_GET['view'] === 'weekly') {
        $_SESSION['calorie_view'] = $_GET['view'];
    }
}

$weekOffset = $_SESSION['week_offset'];
$calorieView = $_SESSION['calorie_view'];

// Calculate the start date of the week based on the offset
$today = date('Y-m-d');
$currentDateTimestamp = strtotime($today);
// Find the Sunday of the current week
$startOfWeek = date('Y-m-d', strtotime("{$today} -" . date('w', $currentDateTimestamp) . " days +{$weekOffset} weeks"));

$dates = [];
for ($i = 0; $i < 7; $i++) {
    $dates[] = date('Y-m-d', strtotime("{$startOfWeek} +{$i} days"));
}

$startDateQuery = $dates[0];
$endDateQuery = $dates[6];

// Fetch meal data for the current user and the displayed week from the database
$sql = "SELECT
            mp.meal_date,
            mp.meal_type AS category,
            r.calories
        FROM
            meal_planner mp
        JOIN
            recipes r ON mp.recipeid = r.recipeid
        WHERE
            mp.userid = ? AND mp.meal_date BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $startDateQuery, $endDateQuery);
$stmt->execute();
$result = $stmt->get_result();

// Calculate calories for each day and meal category
$dailyCalories = [];
$categoryCalories = [];
$weeklyTotalCalories = 0;

// Initialize arrays
foreach ($dates as $date) {
    $dailyCalories[$date] = 0;

    // Meal categories
    $categories = ['breakfast', 'lunch', 'dinner', 'snack'];
    foreach ($categories as $category) {
        $categoryCalories[$date][$category] = 0;
    }
}

// Process meal data from the database result
if ($result->num_rows > 0) {
    while ($meal = $result->fetch_assoc()) {
        // Only count if calories are available and numeric
        if (isset($meal['calories']) && is_numeric($meal['calories'])) {
            $calories = (int)$meal['calories'];
            // Ensure the meal_date exists in our $dates array
            if (in_array($meal['meal_date'], $dates)) {
                 $dailyCalories[$meal['meal_date']] += $calories;
                 // Ensure the category exists before adding
                 if (array_key_exists($meal['category'], $categoryCalories[$meal['meal_date']])) {
                     $categoryCalories[$meal['meal_date']][$meal['category']] += $calories;
                 }
                 $weeklyTotalCalories += $calories;
            }
        }
    }
}

$stmt->close();

// Fetch the daily_calories from the database for the logged-in user from user_profile
$stmt = $conn->prepare("SELECT daily_calories FROM user_profile WHERE userid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// If the user profile exists and has daily_calories, use that
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $targetDailyCalories = $row['daily_calories'];
    // If daily_calories is null or 0 in the profile, use a default
    if (empty($targetDailyCalories)) {
         $targetDailyCalories = 2000; // Default value if not set in profile
    }
} else {
    // If no profile exists, default to 2000 daily calories
    $targetDailyCalories = 2000;
}

$stmt->close();
$conn->close();

$targetWeeklyCalories = $targetDailyCalories * 7; // Calculate target weekly calories

// Meal categories
$categories = ['breakfast', 'lunch', 'dinner', 'snack'];
$weekNumber = $weekOffset === 0 ? "Current Week" : ($weekOffset > 0 ? "Week +{$weekOffset}" : "Week {$weekOffset}");
$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Category colors for the chart and table
$categoryColors = [
    'breakfast' => '#BB3E00',
    'lunch' => '#F3C623',
    'dinner' => '#FFB22C',
    'snack' => '#FA812F'
];

// Current day for highlighting
$currentDay = date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calories Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <!-- Google Fonts - Poppins for headings/logo, Nunito for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
       /* Global Styles & Variables (Copied from home.html) */
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
            font-family: 'Nunito', sans-serif; /* Default body font */
            background-color: var(--background-darker);
            min-height: 100vh;
            scroll-behavior: smooth;
            overflow-x: hidden;
            color: var(--dark-text); /* Default text color */
        }

        /* Navbar (Copied from home.html) */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%; /* Consistent padding */
            background-color: var(--background-light);
            box-shadow: var(--box-shadow-light); /* Softer shadow */
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid var(--border-color); /* Subtle border */
        }
        .nav-links {
            display: flex;
            gap: 2.5rem; /* Increased gap */
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
            font-family: 'Poppins', sans-serif; /* Consistent logo font */
            font-size: 1.8rem; /* Larger logo */
            font-weight: 700;
            color: var(--dark-text);
        }
        .logo span {
            color: var(--primary-accent);
        }
        .nav-icon {
            position: relative;
            cursor: pointer;
            padding: 8px; /* Make clickable area larger */
            border-radius: var(--border-radius-base);
            transition: background-color 0.2s;
        }
        .nav-icon:hover {
            background-color: var(--border-color);
        }
        .nav-icon i {
            font-size: 22px; /* Consistent icon size */
            color: var(--primary-accent); /* Use your theme color from :root */
        }
        .dropdown-menu {
            display: none; /* Hidden by default */
            position: absolute;
            top: 45px; /* Adjust based on navbar height */
            right: 0;
            background-color: var(--background-light);
            min-width: 150px; /* Consistent width */
            box-shadow: 0 5px 15px rgba(0,0,0,0.15); /* Stronger shadow */
            border-radius: var(--border-radius-base);
            z-index: 1000;
            overflow: hidden; /* Ensures border-radius */
            opacity: 0; /* For smooth fade-in */
            transform: translateY(10px); /* For smooth slide-down */
            transition: opacity 0.3s ease-out, transform 0.3s ease-out; /* Transition properties */
        }
        .dropdown-menu.show { /* This class will be toggled by JS */
            display: block; /* Show when 'show' class is present */
            opacity: 1; /* Fade in */
            transform: translateY(0); /* Slide up */
        }

        .dropdown-menu a,
        .dropdown-menu button {
            padding: 0.8rem 1.2rem; /* More padding */
            text-align: left;
            text-decoration: none;
            display: block;
            width: 100%;
            background: none;
            border: none;
            font-size: 1rem; /* Consistent font size */
            color: var(--dark-text);
            cursor: pointer;
            transition: background 0.3s ease-in-out;
            font-weight: 400; /* Lighter font weight */
        }
        .dropdown-menu a:hover,
        .dropdown-menu button:hover {
            background-color: var(--background-darker); /* Lighter hover */
            color: var(--primary-accent);
        }

       /* Keep your existing CSS here, adjust colors to use variables */
       h2 {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            color: var(--primary-accent);
            margin: 20px 0;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .week-navigation {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .week-navigation a {
            text-decoration: none;
            color: var(--primary-accent);
            font-size: 24px;
            font-weight: bold;
        }

        .week-navigation span {
            font-size: 18px;
            font-weight: bold;
            color: var(--dark-text);
        }

        .week-navigation button {
            background-color: var(--primary-accent);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .view-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .view-toggle a {
            padding: 8px 20px;
            text-decoration: none;
            background-color: var(--border-color);
            color: var(--dark-text);
            border-radius: 5px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .view-toggle a.active {
            background-color: var(--primary-accent);
            color: white;
        }

        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background-color: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            padding: 20px;
            flex: 1;
            min-width: 250px;
            text-align: center;
        }

        .summary-card h3 {
            margin-top: 0;
            color: var(--dark-text);
        }

        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-accent);
            margin: 15px 0;
        }

        .summary-card .target {
            color: var(--light-text);
            font-size: 14px;
        }

        .progress-bar {
            height: 10px;
            background-color: var(--border-color);
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background-color: var(--primary-accent);
            transition: width 0.5s ease;
        }

        .chart-container {
            background-color: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            padding: 20px;
            margin-bottom: 30px;
        }

        .calorie-details {
            background-color: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            padding: 20px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--background-darker);
            color: var(--dark-text);
        }

        .today {
            background-color: #fff3e0; /* A light orange, keep this specific if you want */
        }

        .meal-category-label {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .breakfast-color {
            background-color: #BB3E00;
        }

        .lunch-color {
            background-color: #F3C623;
        }

        .dinner-color {
            background-color: #FFB22C;
        }

        .snack-color {
            background-color: #FA812F;
        }

        .total-row {
            font-weight: bold;
            background-color: var(--background-darker);
        }

        .calorie-value {
            font-weight: bold;
        }

        .over-target {
            color: #ff4444; /* Red */
        }

        .under-target {
            color: #4CAF50; /* Green */
        }

        .at-target {
            color: var(--primary-accent);
        }

        /* Responsive adjustments (Copied from home.html) */
        @media (max-width: 768px) {
            .nav-links {
                display: none; /* Hide nav links on small screens */
            }
            .dashboard {
                flex-direction: column;
            }

            .summary-card {
                min-width: 100%;
            }

            .hero h1 { /* These hero rules are not applicable to this page */
                font-size: 3rem;
            }
            .hero p {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 { /* These hero rules are not applicable to this page */
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="../recipe.php">Recipes</a>
        <a href="../mealplanner.php">Meal Planner</a>
        <a href="calories_tracking.php">Calories Tracking</a>
        <a href="../about.php">About Us</a>
    </div>
    <div class="logo">Daily<span>Dish</span></div>
    <div class="nav-icon" id="profileIcon">
        <i class="fa-solid fa-user"></i>
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="profile.php">Profile</a>
            <button onclick="window.location.href='login.php'">Logout</button>
        </div>
    </div>
</nav>

<div class="content-container">
    <h2>Calories Tracking</h2>

    <div class="week-navigation">
        <a href="?week=prev<?= $calorieView ? "&view=$calorieView" : "" ?>">&lt;</a>
        <span><?= $weekNumber ?></span>
        <a href="?week=next<?= $calorieView ? "&view=$calorieView" : "" ?>">&gt;</a>
        <a href="?week=reset<?= $calorieView ? "&view=$calorieView" : "" ?>"><button>Reset</button></a>
    </div>

    <div class="view-toggle">
        <a href="?view=weekly<?= $weekOffset != 0 ? "&week=$weekOffset" : "" ?>" class="<?= $calorieView === 'weekly' ? 'active' : '' ?>">Weekly View</a>
        <a href="?view=daily<?= $weekOffset != 0 ? "&week=$weekOffset" : "" ?>" class="<?= $calorieView === 'daily' ? 'active' : '' ?>">Daily View</a>
    </div>

    <?php if ($calorieView === 'weekly'): ?>
    <!-- Weekly View -->
    <div class="dashboard">
        <div class="summary-card">
            <h3>Weekly Calories</h3>
            <div class="value"><?= number_format($weeklyTotalCalories) ?></div>
            <div class="target">Target: <?= number_format($targetWeeklyCalories) ?> calories</div>
            <div class="progress-bar">
                <div class="progress" style="width: <?= $targetWeeklyCalories > 0 ? min(($weeklyTotalCalories / $targetWeeklyCalories) * 100, 100) : 0 ?>%;"></div>
            </div>
            <?php
            $percentage = $targetWeeklyCalories > 0 ? ($weeklyTotalCalories / $targetWeeklyCalories) * 100 : 0;
            $status = 'at-target';
            if ($percentage < 90 && $weeklyTotalCalories > 0) { // Only show under-target if there are meals
                $status = 'under-target';
            } elseif ($percentage > 110) {
                $status = 'over-target';
            } elseif ($weeklyTotalCalories == 0) {
                 $status = ''; // No meals, no status
            }
            ?>
            <p class="<?= $status ?>">
                <?php
                if ($weeklyTotalCalories == 0) {
                    echo "No meals logged this week.";
                } elseif ($percentage < 90) {
                    echo "You're " . number_format($targetWeeklyCalories - $weeklyTotalCalories) . " calories under your weekly target";
                } elseif ($percentage > 110) {
                    echo "You're " . number_format($weeklyTotalCalories - $targetWeeklyCalories) . " calories over your weekly target";
                } else {
                    echo "You're on track with your weekly target";
                }
                ?>
            </p>
        </div>

        <div class="summary-card">
            <h3>Daily Average</h3>
            <?php
            $daysWithMeals = count(array_filter($dailyCalories, function($cal) { return $cal > 0; }));
            $avgDailyCalories = $daysWithMeals > 0 ? $weeklyTotalCalories / $daysWithMeals : 0;
            ?>
            <div class="value"><?= number_format($avgDailyCalories) ?></div>
            <div class="target">Target: <?= number_format($targetDailyCalories) ?> calories per day</div>
            <div class="progress-bar">
                <div class="progress" style="width: <?= $targetDailyCalories > 0 ? min(($avgDailyCalories / $targetDailyCalories) * 100, 100) : 0 ?>%;"></div>
            </div>
            <?php
            $percentage = $targetDailyCalories > 0 ? ($avgDailyCalories / $targetDailyCalories) * 100 : 0;
            $status = 'at-target';
            if ($percentage < 90 && $daysWithMeals > 0) { // Only show under-target if there are meals
                $status = 'under-target';
            } elseif ($percentage > 110) {
                $status = 'over-target';
            } elseif ($daysWithMeals == 0) {
                 $status = ''; // No meals, no status
            }
            ?>
            <p class="<?= $status ?>">
                <?php
                if ($daysWithMeals == 0) {
                    echo "No meals logged this week.";
                } elseif ($percentage < 90) {
                    echo "Your average is " . number_format($targetDailyCalories - $avgDailyCalories) . " calories under your daily target";
                } elseif ($percentage > 110) {
                    echo "Your average is " . number_format($avgDailyCalories - $targetDailyCalories) . " calories over your daily target";
                } else {
                    echo "Your daily average is within target range";
                }
                ?>
            </p>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="weeklyCaloriesChart"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="categoryBreakdownChart"></canvas>
    </div>

    <div class="calorie-details">
        <h3>Detailed Calorie Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>
                        <span class="meal-category-label breakfast-color"></span>
                        Breakfast
                    </th>
                    <th>
                        <span class="meal-category-label lunch-color"></span>
                        Lunch
                    </th>
                    <th>
                        <span class="meal-category-label dinner-color"></span>
                        Dinner
                    </th>
                    <th>
                        <span class="meal-category-label snack-color"></span>
                        Snack
                    </th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dates as $date): ?>
                    <tr class="<?= $date === $currentDay ? 'today' : '' ?>">
                        <td><?= $dayNames[date('w', strtotime($date))] ?>, <?= date('M j', strtotime($date)) ?></td>
                        <td><?= number_format($categoryCalories[$date]['breakfast'] ?? 0) ?></td>
                        <td><?= number_format($categoryCalories[$date]['lunch'] ?? 0) ?></td>
                        <td><?= number_format($categoryCalories[$date]['dinner'] ?? 0) ?></td>
                        <td><?= number_format($categoryCalories[$date]['snack'] ?? 0) ?></td>
                        <td class="calorie-value
                            <?php
                            $dailyTotal = $dailyCalories[$date] ?? 0;
                            $dailyPercentage = $targetDailyCalories > 0 ? ($dailyTotal / $targetDailyCalories) * 100 : 0;
                            if ($dailyPercentage < 90 && $dailyTotal > 0) echo 'under-target';
                            elseif ($dailyPercentage > 110) echo 'over-target';
                             elseif ($dailyTotal > 0) echo 'at-target'; // Apply at-target if total calories > 0 and within range
                            ?>">
                            <?= number_format($dailyTotal) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td>Weekly Total</td>
                    <?php
                    $categoryTotals = [
                        'breakfast' => 0,
                        'lunch' => 0,
                        'dinner' => 0,
                        'snack' => 0
                    ];

                    foreach ($dates as $date) {
                        foreach ($categories as $category) {
                            $categoryTotals[$category] += ($categoryCalories[$date][$category] ?? 0);
                        }
                    }
                    ?>
                    <td><?= number_format($categoryTotals['breakfast']) ?></td>
                    <td><?= number_format($categoryTotals['lunch']) ?></td>
                    <td><?= number_format($categoryTotals['dinner']) ?></td>
                    <td><?= number_format($categoryTotals['snack']) ?></td>
                    <td class="calorie-value
                        <?php
                        $weeklyPercentage = $targetWeeklyCalories > 0 ? ($weeklyTotalCalories / $targetWeeklyCalories) * 100 : 0;
                        if ($weeklyPercentage < 90 && $weeklyTotalCalories > 0) echo 'under-target';
                        elseif ($weeklyPercentage > 110) echo 'over-target';
                         elseif ($weeklyTotalCalories > 0) echo 'at-target'; // Apply at-target if total calories > 0 and within range
                        ?>">
                        <?= number_format($weeklyTotalCalories) ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <!-- Daily View -->
    <?php
    // Select today's date by default, or the first day of the week if today isn't in the current week view
    $selectedDate = $currentDay;
    if (!in_array($selectedDate, $dates)) {
        $selectedDate = $dates[0];
    }

    // Allow selection via GET
    if (isset($_GET['date']) && in_array($_GET['date'], $dates)) {
        $selectedDate = $_GET['date'];
    }

    $selectedDayName = $dayNames[date('w', strtotime($selectedDate))];
    $selectedDayFormatted = date('M j', strtotime($selectedDate));
    $todayTotalCalories = $dailyCalories[$selectedDate] ?? 0;
    ?>

    <div class="day-navigation" style="text-align: center; margin-bottom: 20px;">
        <?php foreach ($dates as $date): ?>
            <a href="?view=daily&date=<?= $date ?><?= $weekOffset != 0 ? "&week=$weekOffset" : "" ?>"
               style="display: inline-block; padding: 8px; margin: 0 5px;
                     border-radius: 5px; text-decoration: none;
                     background-color: <?= $date === $selectedDate ? 'var(--primary-accent)' : 'var(--border-color)' ?>;
                     color: <?= $date === $selectedDate ? 'white' : 'var(--dark-text)' ?>;">
                <?= $dayNames[date('w', strtotime($date))] ?><br>
                <?= date('M j', strtotime($date)) ?>
                <?= $date === $currentDay ? '<br>(Today)' : '' ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="dashboard">
        <div class="summary-card">
            <h3><?= $selectedDayName ?>, <?= $selectedDayFormatted ?> Calories</h3>
            <div class="value"><?= number_format($todayTotalCalories) ?></div>
            <div class="target">Target: <?= number_format($targetDailyCalories) ?> calories</div>
            <div class="progress-bar">
                <div class="progress" style="width: <?= $targetDailyCalories > 0 ? min(($todayTotalCalories / $targetDailyCalories) * 100, 100) : 0 ?>%;"></div>
            </div>
            <?php
            $percentage = $targetDailyCalories > 0 ? ($todayTotalCalories / $targetDailyCalories) * 100 : 0;
            $status = 'at-target';
            if ($percentage < 90 && $todayTotalCalories > 0) {
                $status = 'under-target';
            } elseif ($percentage > 110) {
                $status = 'over-target';
            } elseif ($todayTotalCalories == 0) {
                 $status = ''; // No meals, no status
            }
            ?>
            <p class="<?= $status ?>">
                <?php
                 if ($todayTotalCalories == 0) {
                     echo "No meals logged for this day.";
                 } elseif ($percentage < 90) {
                    echo "You're " . number_format($targetDailyCalories - $todayTotalCalories) . " calories under your daily target";
                } elseif ($percentage > 110) {
                    echo "You're " . number_format($todayTotalCalories - $targetDailyCalories) . " calories over your daily target";
                } else {
                    echo "You're on track with your daily target";
                }
                ?>
            </p>
        </div>

        <div class="summary-card">
            <h3>Category Breakdown</h3>
            <table style="width: 100%; margin-top: 10px;">
                <tr>
                    <td><span class="meal-category-label breakfast-color"></span> Breakfast</td>
                    <td class="calorie-value"><?= number_format($categoryCalories[$selectedDate]['breakfast'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td><span class="meal-category-label lunch-color"></span> Lunch</td>
                    <td class="calorie-value"><?= number_format($categoryCalories[$selectedDate]['lunch'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td><span class="meal-category-label dinner-color"></span> Dinner</td>
                    <td class="calorie-value"><?= number_format($categoryCalories[$selectedDate]['dinner'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td><span class="meal-category-label snack-color"></span> Snack</td>
                    <td class="calorie-value"><?= number_format($categoryCalories[$selectedDate]['snack'] ?? 0) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="dailyCategoryChart"></canvas>
    </div>

    <?php endif; ?>
</div>

<script>
    // Toggle dropdown on icon click
    document.getElementById('profileIcon').addEventListener('click', function (e) {
        e.stopPropagation(); // Prevent the click from propagating to the document body
        var menu = document.getElementById('dropdownMenu');
        menu.classList.toggle('show'); // Use classList.toggle
    });

    // Hide dropdown when clicking anywhere else on the document
    document.addEventListener('click', function (e) {
        var menu = document.getElementById('dropdownMenu');
        // If the menu is shown AND the click is NOT inside the profile icon AND the click is NOT inside the dropdown menu itself
        if (menu.classList.contains('show') && !document.getElementById('profileIcon').contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('show');
        }
    });

    // Prevent dropdown from closing when clicking inside it (already handled by stopping propagation on the dropdown itself)
    document.getElementById('dropdownMenu').addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Function to update chart data safely (handles null/undefined)
        function safeChartData(dataArray) {
             return dataArray.map(value => value === null || typeof value === 'undefined' ? 0 : value);
        }

        // Create the weekly chart (only in weekly view)
        <?php if ($calorieView === 'weekly'): ?>
        const weeklyChartCtx = document.getElementById('weeklyCaloriesChart').getContext('2d');
        const weeklyChart = new Chart(weeklyChartCtx, {
            type: 'bar',
            data: {
                labels: [<?php
                    $formattedLabels = [];
                    foreach ($dates as $date) {
                        $formattedLabels[] = "'" . $dayNames[date('w', strtotime($date))] . ", " . date('M j', strtotime($date)) . "'";
                    }
                    echo implode(', ', $formattedLabels);
                ?>],
                datasets: [{
                    label: 'Daily Calories',
                    data: safeChartData([<?= implode(', ', array_values($dailyCalories)) ?>]),
                    backgroundColor: function(context) {
                        const index = context.dataIndex;
                        const date = <?php echo json_encode($dates); ?>[index];
                        return date === <?php echo json_encode($currentDay); ?> ? 'var(--primary-accent)' : 'var(--secondary-accent)'; // Use CSS variables
                    },
                    borderColor: 'var(--primary-accent)',
                    borderWidth: 1
                }, {
                    label: 'Daily Target',
                    data: [<?= str_repeat("$targetDailyCalories, ", 6) . $targetDailyCalories ?>],
                    type: 'line',
                    fill: false,
                    borderColor: '#999',
                    borderDash: [5, 5],
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                 height: 300, // Set a fixed height for better responsiveness
                 plugins: {
                    title: {
                        display: true,
                        text: 'Daily Calorie Intake vs Target',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                     tooltip: {
                         callbacks: {
                             label: function(context) {
                                 let label = context.dataset.label || '';
                                 if (label) {
                                     label += ': ';
                                 }
                                 label += context.raw.toLocaleString() + ' calories';
                                 return label;
                             },
                             afterLabel: function(context) {
                                 if (context.datasetIndex === 0) { // Only for the calorie bars
                                     const dailyCal = context.raw;
                                     const target = <?= $targetDailyCalories ?>;
                                     if (target > 0) {
                                         const percentage = (dailyCal / target * 100).toFixed(1);
                                         return `(${percentage} % of target)`;
                                     }
                                 }
                             }
                         }
                     }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Calories'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Day'
                        }
                    }
                }
            }
        });

        // Create category breakdown chart
        const categoryBreakdownCtx = document.getElementById('categoryBreakdownChart').getContext('2d');
        const categoryBreakdownChart = new Chart(categoryBreakdownCtx, {
            type: 'pie',
            data: {
                labels: ['Breakfast', 'Lunch', 'Dinner', 'Snack'],
                datasets: [{
                    data: safeChartData([
                        <?= $categoryTotals['breakfast'] ?? 0 ?>,
                        <?= $categoryTotals['lunch'] ?? 0 ?>,
                        <?= $categoryTotals['dinner'] ?? 0 ?>,
                        <?= $categoryTotals['snack'] ?? 0 ?>
                    ]),
                    backgroundColor: [
                        '#BB3E00',
                        '#F3C623',
                        '#FFB22C',
                        '#FA812F'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                height: 300, // Set a fixed height for better responsiveness
                plugins: {
                    title: {
                        display: true,
                        text: 'Weekly Calories by Meal Category',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = <?= $weeklyTotalCalories ?: 1 ?>;
                                const percentage = (value / total * 100).toFixed(1);
                                return `${label}: ${value} calories (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        <?php else: ?>
        // Create daily category chart for the daily view
        const dailyCategoryChartCtx = document.getElementById('dailyCategoryChart').getContext('2d');
        const dailyCategoryChart = new Chart(dailyCategoryChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Breakfast', 'Lunch', 'Dinner', 'Snack'],
                datasets: [{
                    data: safeChartData([
                        <?= $categoryCalories[$selectedDate]['breakfast'] ?? 0 ?>,
                        <?= $categoryCalories[$selectedDate]['lunch'] ?? 0 ?>,
                        <?= $categoryCalories[$selectedDate]['dinner'] ?? 0 ?>,
                        <?= $categoryCalories[$selectedDate]['snack'] ?? 0 ?>
                    ]),
                    backgroundColor: [
                        '#BB3E00',
                        '#F3C623',
                        '#FFB22C',
                        '#FA812F'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                height: 300, // Set a fixed height for better responsiveness
                plugins: {
                    title: {
                        display: true,
                        text: '<?= $selectedDayName ?>, <?= $selectedDayFormatted ?> Calories by Meal Category',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = <?= $todayTotalCalories ?: 1 ?>; // Avoid division by zero
                                const percentage = (value / total * 100).toFixed(1);
                                return `${label}: ${value} calories (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>

</body>
</html>