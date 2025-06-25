<?php
session_start();
include 'php/db.php'; // Ensure your database connection is included

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header('Location: login.php'); // Or your login page
    exit();
}

$userid = $_SESSION['user_id'];

// Set default week offset if not set
if (!isset($_SESSION['week_offset'])) {
    $_SESSION['week_offset'] = 0;
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

$weekOffset = $_SESSION['week_offset'];

// Calculate the start date as today, adjusted by the week offset
// We'll find the Sunday of the current week based on today and the offset
$today = date('Y-m-d');
$currentDateTimestamp = strtotime($today);
// date('w', $currentDateTimestamp) gives 0 for Sunday, 1 for Monday, etc.
// Subtracting this many days from today gives us the Sunday of the current week.
// Then add the weekOffset.
$startOfWeek = date('Y-m-d', strtotime("{$today} -" . date('w', $currentDateTimestamp) . " days +{$weekOffset} weeks"));

$dates = [];
for ($i = 0; $i < 7; $i++) {
    $dates[] = date('Y-m-d', strtotime("{$startOfWeek} +{$i} days"));
}

$startDateQuery = $dates[0];
$endDateQuery = $dates[6];


// Fetch meals for the current user and the displayed week from the database
$sql = "SELECT
            mp.plannerid,
            mp.meal_date,
            mp.meal_type AS category,
            r.recipeid,
            r.name,
            r.prep_time,
            r.calories,
            r.description,
            r.ingredients
        FROM
            meal_planner mp
        JOIN
            recipes r ON mp.recipeid = r.recipeid
        WHERE
            mp.userid = ? AND mp.meal_date BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userid, $startDateQuery, $endDateQuery);
$stmt->execute();
$result = $stmt->get_result();

$organizedMeals = [];

// Initialize all dates for the week with empty arrays
foreach ($dates as $date) {
    $organizedMeals[$date] = [];
}

// Populate meals from the database result
if ($result->num_rows > 0) {
    while ($meal = $result->fetch_assoc()) {
         // Ensure ingredients are treated as an array
        if (!empty($meal['ingredients'])) {
             // Assuming ingredients are stored as a newline-separated string
             $meal['ingredients'] = array_map('trim', explode("\n", $meal['ingredients']));
             // Filter out any empty strings that might result from extra newlines
             $meal['ingredients'] = array_filter($meal['ingredients']);
        } else {
             $meal['ingredients'] = [];
        }
        $organizedMeals[$meal['meal_date']][] = $meal;
    }
}

$stmt->close();
$conn->close();

// Meal categories
$categories = ['breakfast', 'lunch', 'dinner', 'snack'];
$weekNumber = $weekOffset === 0 ? "Current Week" : ($weekOffset > 0 ? "Week +{$weekOffset}" : "Week {$weekOffset}");
$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planner - DailyDish</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* Global Styles & Variables */
        :root {
            --primary-accent: #FF8800; /* Your main orange */
            --secondary-accent: #FFB22C; /* Lighter orange */
            --dark-text: #333333;
            --light-text: #666666;
            --background-light: #FDFDFD;
            --background-darker: #F7F7F7;
            --border-color: #E0E0E0;

            /* Category Colors - More harmonious */
            --color-breakfast: #A0522D; /* Sienna */
            --color-lunch: #5FAD41;    /* Green */
            --color-dinner: #3C6990;   /* Steel Blue */
            --color-snack: #F9A03F;    /* Deep Saffron */

            --border-radius-base: 8px;
            --border-radius-large: 12px;
        }

        body {
            font-family: 'Nunito', sans-serif; /* Using Nunito for body */
            background-color: var(--background-darker);
            padding: 0;
            margin: 0;
            color: var(--dark-text); /* Default text color */
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%; /* Increased padding */
            background-color: var(--background-light);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08); /* Softer, deeper shadow */
            position: sticky; /* Make it sticky */
            top: 0;
            z-index: 999;
            border-bottom: 1px solid var(--border-color); /* Subtle border */
        }

        .nav-links {
            display: flex;
            gap: 2.5rem; /* Increased gap */
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-text);
            font-weight: 600; /* Slightly bolder */
            transition: color 0.3s ease-in-out;
        }

        .nav-links a:hover {
            color: var(--primary-accent); /* Hover color */
        }

        .logo {
            font-family: 'Poppins', sans-serif; /* Different font for logo */
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
            font-size: 22px; /* Larger icon */
            color: var(--primary-accent);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 45px; /* Adjust based on navbar height */
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15); /* Stronger shadow */
            border-radius: var(--border-radius-base); /* Rounded corners */
            overflow: hidden;
            z-index: 100;
            min-width: 150px; /* Wider dropdown */
        }

        .dropdown-menu a {
            display: block;
            padding: 0.8rem 1.2rem; /* More padding */
            text-decoration: none;
            color: var(--dark-text);
            transition: background 0.3s ease-in-out;
            font-weight: 400;
        }

        .dropdown-menu a:hover {
            background-color: var(--background-darker); /* Lighter hover */
            color: var(--primary-accent);
        }

        h2 {
            text-align: center;
            color: var(--primary-accent);
            margin: 30px 0 25px; /* More vertical spacing */
            font-size: 2.2rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        .content-container {
            padding: 20px 5%; /* Padding on sides */
            max-width: 1400px; /* Max width for large screens */
            margin: 0 auto; /* Center the container */
        }

        /* Week Navigation */
        .week-navigation {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px; /* Increased gap */
        }

        .week-navigation a {
            text-decoration: none;
            color: var(--primary-accent);
            font-size: 30px; /* Larger arrows */
            font-weight: bold;
            padding: 5px 10px;
            border-radius: var(--border-radius-base);
            transition: background-color 0.2s, color 0.2s;
        }
        .week-navigation a:hover {
            background-color: var(--secondary-accent);
            color: white;
        }

        .week-navigation span {
            font-size: 22px; /* Larger week text */
            font-weight: 700;
            color: var(--dark-text);
        }

        .week-navigation button {
            background-color: var(--primary-accent);
            color: white;
            border: none;
            padding: 8px 15px; /* Larger button */
            border-radius: var(--border-radius-base);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .week-navigation button:hover {
            background-color: #e07b00; /* Slightly darker orange */
        }

        /* Week & Day Containers */
        .week-container {
            display: grid; /* Use CSS Grid for better responsiveness */
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Auto-fit columns */
            gap: 20px; /* Consistent gap */
            padding-bottom: 20px;
        }

        .day-container {
            background-color: white;
            border-radius: var(--border-radius-large); /* More rounded */
            box-shadow: 0 8px 20px rgba(0,0,0,0.08); /* More prominent shadow */
            overflow: hidden; /* Ensures border-radius applies to header */
            display: flex;
            flex-direction: column; /* Stacks header and categories */
        }

        .day-header {
            text-align: center;
            padding: 15px; /* More padding */
            background-color: var(--background-darker); /* Softer background */
            border-bottom: 3px solid var(--secondary-accent); /* Thicker accent border */
        }

        .day-header.today { /* Class for today's header */
            background-color: #fff3e0; /* A very light orange */
            border-color: var(--primary-accent);
        }

        .day-name {
            font-weight: 700;
            color: var(--primary-accent); /* Day name in accent color */
            margin: 0;
            font-size: 1.3rem;
        }

        .day-date {
            color: var(--light-text);
            margin: 5px 0 0;
            font-size: 0.95rem;
        }

        .meal-categories {
            padding: 15px;
            flex-grow: 1; /* Allows it to expand */
            display: flex;
            flex-direction: column;
        }

        .meal-category {
            margin-bottom: 20px;
        }

        .category-header {
            color: white;
            padding: 8px 12px; /* More padding */
            border-radius: 6px; /* Slightly rounded */
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px; /* More space below header */
            text-shadow: 0 1px 2px rgba(0,0,0,0.1); /* Subtle text shadow */
        }

        /* Specific Category Colors */
        .category-breakfast { background-color: var(--color-breakfast); }
        .category-lunch { background-color: var(--color-lunch); }
        .category-dinner { background-color: var(--color-dinner); }
        .category-snack { background-color: var(--color-snack); }

        .meal-item {
            background-color: #fcfcfc; /* Lighter background */
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
            margin-bottom: 8px; /* More space */
            padding: 10px 12px; /* More padding */
            cursor: pointer;
            transition: all 0.2s ease-in-out; /* Smooth transition for multiple properties */
            position: relative;
        }

        .meal-item:hover {
            background-color: var(--secondary-accent); /* Hover effect */
            color: white; /* Text color on hover */
            transform: translateY(-3px); /* Lift effect */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow on hover */
            border-color: var(--primary-accent);
        }
        .meal-item:hover .meal-details { /* Make details white on hover */
            color: white;
        }

        .meal-name {
            font-weight: 700; /* Bolder */
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .meal-details {
            font-size: 0.9rem;
            color: var(--light-text);
            line-height: 1.4;
        }

        .no-meal {
            color: #aaaaaa;
            font-style: italic;
            font-size: 0.9rem;
            padding: 10px;
            text-align: center;
            border: 1px dashed var(--border-color);
            border-radius: var(--border-radius-base);
            margin-top: 5px;
            background-color: var(--background-light);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6); /* Darker overlay */
            animation: fadeIn 0.3s forwards; /* Use forwards to keep last state */
        }

        @keyframes fadeIn {
            from {opacity: 0}
            to {opacity: 1}
        }

        .modal-content {
            background-color: white;
            margin: 7% auto; /* Higher on screen */
            padding: 30px; /* More padding */
            border: none; /* No visible border */
            border-radius: var(--border-radius-large); /* Rounded corners */
            width: 90%;
            max-width: 650px; /* Slightly wider */
            box-shadow: 0 10px 30px rgba(0,0,0,0.25); /* Stronger shadow */
            animation: slideDown 0.4s forwards cubic-bezier(0.25, 0.46, 0.45, 0.94); /* Smoother animation */
        }

        @keyframes slideDown {
            from {transform: translateY(-80px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        .close-modal {
            color: var(--light-text);
            float: right;
            font-size: 32px; /* Larger close button */
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: var(--primary-accent);
        }

        .meal-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary-accent); /* Accent color border */
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .meal-popup-header h3 {
            margin: 0;
            color: var(--primary-accent);
            font-size: 2rem; /* Larger title */
            font-family: 'Poppins', sans-serif;
        }

        .meal-popup-category {
            display: inline-block;
            background-color: var(--secondary-accent); /* General accent for tag */
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 15px; /* Space from title */
        }
        /* Specific category colors for the modal tag */
        .meal-popup-category.cat-breakfast { background-color: var(--color-breakfast); }
        .meal-popup-category.cat-lunch { background-color: var(--color-lunch); }
        .meal-popup-category.cat-dinner { background-color: var(--color-dinner); }
        .meal-popup-category.cat-snack { background-color: var(--color-snack); }


        .meal-popup-details p {
            margin: 8px 0;
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .meal-popup-details strong {
            color: var(--dark-text);
            display: inline-block;
            width: 140px; /* Align labels */
        }

        .meal-description, .meal-ingredients-section {
            background-color: var(--background-darker);
            border-radius: var(--border-radius-base);
            padding: 15px;
            margin-bottom: 20px;
            max-height: 180px;
            overflow-y: auto;
            line-height: 1.6;
            color: var(--dark-text);
        }

        .meal-description p {
            margin: 0;
        }

        .meal-ingredients-section h4 {
            margin-top: 0;
            color: var(--primary-accent);
            font-size: 1.15rem;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .meal-ingredients {
            list-style: none; /* Remove default bullet */
            padding-left: 0;
            margin-bottom: 0;
        }

        .meal-ingredients li {
            padding: 5px 0;
            position: relative;
            padding-left: 25px; /* Space for custom bullet */
        }
        .meal-ingredients li::before {
            content: '\2022'; /* Unicode bullet point */
            color: var(--primary-accent); /* Accent color bullet */
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
            position: absolute;
            left: 0;
            top: 5px;
        }


        .meal-popup-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .meal-popup-button {
            padding: 12px 25px; /* Larger buttons */
            border: none;
            border-radius: var(--border-radius-base);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.2s, transform 0.1s;
        }

        .view-recipe-button {
            background-color: #0077b6; /* Consistent with your initial blue */
            color: white;
        }

        .view-recipe-button:hover {
            background-color: #005a8c;
            transform: translateY(-2px);
        }

        .remove-meal-button {
            background-color: #dc3545; /* Bootstrap-like danger red */
            color: white;
        }

        .remove-meal-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .nav-links {
                display: none; /* Hide nav links on small screens */
            }
            .navbar {
                justify-content: space-between;
            }
            .logo {
                font-size: 1.5rem;
            }

            .week-container {
                grid-template-columns: 1fr; /* Stack days vertically on small screens */
            }

            .day-container {
                min-width: unset; /* Remove min-width restriction */
            }

            .modal-content {
                margin: 5% auto;
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="recipe.php">Recipes</a>
        <a href="mealplanner.php">Meal Planner</a>
        <a href="php/calories_tracking.php">Calories Tracking</a>
        <a href="about.php">About Us</a>
    </div>
    <div class="logo">Daily<span>Dish</span></div>
    <div class="nav-icon" onclick="toggleDropdown()">
        <i class="fa-solid fa-user"></i>
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="profile.php">Profile</a>
            <a href="php/login.php">Logout</a> <!-- Link to your logout script -->
        </div>
    </div>
</nav>

<div class="content-container">
    <h2>Meal Planner</h2>

    <div class="week-navigation">
        <a href="?week=prev" aria-label="Previous Week">&lt;</a>
        <span><?= htmlspecialchars($weekNumber) ?></span>
        <a href="?week=next" aria-label="Next Week">&gt;</a>
        <a href="?week=reset"><button>Reset</button></a>
    </div>

    <div class="week-container">
        <?php foreach ($dates as $index => $date): ?>
            <div class="day-container">
                <div class="day-header <?= date('Y-m-d') === $date ? 'today' : '' ?>">
                    <p class="day-name"><?= htmlspecialchars($dayNames[date('w', strtotime($date))]) ?></p>
                    <p class="day-date"><?= htmlspecialchars(date('M j, Y', strtotime($date))) ?><?= date('Y-m-d') === $date ? ' (Today)' : '' ?></p>
                </div>

                <div class="meal-categories">
                    <?php foreach ($categories as $category): ?>
                        <div class="meal-category">
                            <div class="category-header category-<?= htmlspecialchars($category) ?>">
                                <?= htmlspecialchars(ucfirst($category)) ?>
                            </div>

                            <?php
                                $found = false;
                                if (isset($organizedMeals[$date])) {
                                    foreach ($organizedMeals[$date] as $meal) {
                                        if ($meal['category'] === $category) {
                                            $found = true;

                                            // Ensure ingredients is always an array for JSON encoding
                                            $ingredientsArray = is_array($meal['ingredients']) ? $meal['ingredients'] : [];

                                            // Convert ingredients to JSON for JavaScript
                                            $ingredientsJson = htmlspecialchars(json_encode($ingredientsArray), ENT_QUOTES, 'UTF-8');

                                            // Sanitize other string outputs for HTML attributes and content
                                            $mealName = htmlspecialchars($meal['name'], ENT_QUOTES, 'UTF-8');
                                            $mealCategory = htmlspecialchars($meal['category'], ENT_QUOTES, 'UTF-8');
                                            $mealPrepTime = htmlspecialchars($meal['prep_time'], ENT_QUOTES, 'UTF-8');
                                            $mealCalories = htmlspecialchars($meal['calories'] ?? '', ENT_QUOTES, 'UTF-8'); // Null coalesce for safety
                                            $mealDescription = htmlspecialchars($meal['description'] ?? '', ENT_QUOTES, 'UTF-8'); // Null coalesce for safety
                                            $mealRecipeId = htmlspecialchars($meal['recipeid'], ENT_QUOTES, 'UTF-8');
                                            $mealDateString = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
                            ?>
                                <div class="meal-item"
                                    onclick="showMealDetails(
                                        '<?= $mealRecipeId ?>',
                                        '<?= $mealName ?>',
                                        '<?= $mealCategory ?>',
                                        '<?= $mealPrepTime ?>',
                                        '<?= $mealCalories ?>',
                                        '<?= $mealDescription ?>',
                                        <?= $ingredientsJson ?>,
                                        '<?= $mealDateString ?>'
                                    )">
                                    <div class="meal-name"><?= $mealName ?></div>
                                    <div class="meal-details">
                                        Prep: <?= $mealPrepTime ?>
                                        <?php if (!empty($meal['calories']) && $meal['calories'] !== '0'): // Check for non-zero calories too ?>
                                            <br>Calories: <?= $mealCalories ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php
                                        }
                                    }
                                }

                                if (!$found):
                            ?>
                                <div class="no-meal">No meal planned</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Meal Details Modal -->
    <div id="mealDetailsModal" class="modal">
        <div class="modal-content">
            <div class="meal-popup-header">
                <h3 id="meal-popup-title">Meal Name</h3>
                <span class="meal-popup-category" id="meal-popup-category">Category</span>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>

            <div class="meal-popup-details">
                <p><strong>Preparation Time:</strong> <span id="meal-popup-preptime"></span></p>
                <p><strong>Calories:</strong> <span id="meal-popup-calories"></span></p>
            </div>

            <div id="meal-description" class="meal-description" style="display: none;">
                <!-- Description will appear here, or div will be hidden if empty -->
            </div>

            <div id="ingredients-container" class="meal-ingredients-section" style="display: none;">
                <h4>Ingredients:</h4>
                <ul id="meal-ingredients" class="meal-ingredients">
                    <!-- Ingredients will be added here dynamically -->
                </ul>
            </div>

            <div class="meal-popup-actions">
                 <!-- The onclick is set dynamically in showMealDetails -->
                <button class="meal-popup-button view-recipe-button">View Full Recipe</button>

                <form action="php/remove_from_mealplanner.php" method="POST">
                    <input type="hidden" name="recipeid" id="remove-recipeid" value="">
                    <input type="hidden" name="meal_date" id="remove-meal-date" value="">
                    <input type="hidden" name="category" id="remove-category" value="">
                    <button type="submit" class="meal-popup-button remove-meal-button">Remove from Plan</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show meal details popup
        function showMealDetails(mealId, mealName, category, prepTime, calories, description, ingredients, mealDate) {
            // Populate modal with meal details
            document.getElementById('meal-popup-title').innerText = mealName;

            const mealPopupCategoryElement = document.getElementById('meal-popup-category');
            mealPopupCategoryElement.innerText = category.charAt(0).toUpperCase() + category.slice(1);
            // Add specific class for category styling (e.g., 'cat-breakfast')
            mealPopupCategoryElement.className = 'meal-popup-category cat-' + category.toLowerCase(); // Ensure lowercase for class matching

            document.getElementById('meal-popup-preptime').innerText = prepTime;

            const caloriesValue = document.getElementById('meal-popup-calories');
            // Check for valid calories data (not empty, null, undefined, or just whitespace)
            if (calories && calories !== "undefined" && calories !== "null" && calories.toString().trim() !== '' && calories.toString().trim() !== '0') {
                caloriesValue.innerText = calories;
            } else {
                caloriesValue.innerText = 'Not specified';
            }

            const descriptionElement = document.getElementById('meal-description');
            if (description && description.trim() !== '') {
                descriptionElement.innerText = description;
                descriptionElement.style.display = 'block'; // Show description if available
            } else {
                descriptionElement.style.display = 'none'; // Hide if no description
            }

            const ingredientsList = document.getElementById('meal-ingredients');
            ingredientsList.innerHTML = ''; // Clear previous ingredients

            const ingredientsContainer = document.getElementById('ingredients-container');
            // Check if ingredients is an array and has actual items
            if (ingredients && Array.isArray(ingredients) && ingredients.length > 0) {
                let hasValidIngredients = false;
                ingredients.forEach(ingredient => {
                    if (ingredient.trim() !== '') { // Only add non-empty ingredients
                        const li = document.createElement('li');
                        li.innerText = ingredient.trim();
                        ingredientsList.appendChild(li);
                        hasValidIngredients = true;
                    }
                });
                ingredientsContainer.style.display = hasValidIngredients ? 'block' : 'none'; // Show container only if there are valid ingredients
            } else {
                ingredientsContainer.style.display = 'none'; // Hide if no ingredients
            }

            // Set hidden form values for remove action
            document.getElementById('remove-recipeid').value = mealId;
            document.getElementById('remove-meal-date').value = mealDate;
            document.getElementById('remove-category').value = category;

            // Set the View Full Recipe button link
            document.querySelector('.view-recipe-button').onclick = function() {
                window.location.href = 'recipe_detail.php?recipeid=' + mealId;
            };

            // Show the modal
            document.getElementById('mealDetailsModal').style.display = 'block';
        }

        // Close the modal
        function closeModal() {
            document.getElementById('mealDetailsModal').style.display = 'none';
        }

        // Close modal when clicking outside the content
        window.onclick = function(event) {
            const modal = document.getElementById('mealDetailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Toggle Navbar Dropdown
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            // If currently block (visible), hide it. Else, show it.
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Close dropdown if click occurs outside of it
        document.addEventListener('click', function(event) {
            const navIcon = document.querySelector('.nav-icon');
            const dropdown = document.getElementById('dropdownMenu');
            // If the clicked element is NOT the nav icon AND the dropdown is visible, hide it.
            if (!navIcon.contains(event.target) && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>