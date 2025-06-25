<?php
include 'php/db.php';

$query = "SELECT * FROM recipes ORDER BY name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyDish - Recipes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-accent: #FF8800;
            --secondary-accent: #FFB22C;
            --dark-text: #333333;
            --light-text: #666666;
            --background-light: #FDFDFD;
            --background-darker: #F7F7F7;
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
            font-family: 'Nunito', sans-serif;
            background-color: var(--background-darker);
            min-height: 100vh;
            scroll-behavior: smooth;
            overflow-x: hidden;
            color: var(--dark-text);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%;
            background-color: var(--background-light);
            box-shadow: var(--box-shadow-light);
            position: sticky;
            top: 0;
            z-index: 10;
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
            top: 45px;
            right: 0;
            background-color: var(--background-light);
            min-width: 150px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-radius: var(--border-radius-base);
            z-index: 1000;
            overflow: hidden;
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
            padding: 0.8rem 1.2rem;
            text-align: left;
            text-decoration: none;
            display: block;
            width: 100%;
            background: none;
            border: none;
            font-size: 1rem;
            color: var(--dark-text);
            cursor: pointer;
            transition: background 0.3s ease-in-out;
            font-weight: 400;
        }
        .dropdown-menu a:hover,
        .dropdown-menu button:hover {
            background-color: var(--background-darker); 
            color: var(--primary-accent);
        }

    
        .back-button {
            display: inline-flex; 
            align-items: center;
            text-decoration: none;
            color: var(--primary-accent); 
            margin: 1.5rem 0 1rem 5%;
            font-weight: 600; 
            transition: color 0.3s ease;
        }
        .back-button:hover {
            color: var(--secondary-accent);
        }
        .back-button svg {
            margin-right: 0.5rem;
            width: 20px; 
            height: 20px;
            stroke-width: 2; 
        }

        
        .search-container {
            width: 90%;
            max-width: 700px; 
            margin: 2.5rem auto; 
            position: relative;
            box-shadow: var(--box-shadow-light);
            border-radius: 50px;
            background-color: var(--background-light);
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
        }

        .search-box {
            flex-grow: 1;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            border: none;
            font-size: 1rem;
            outline: none;
            background-color: transparent;
            color: var(--dark-text);
        }

        .search-box::placeholder {
            color: var(--light-text);
            opacity: 0.7;
        }

        .search-box:focus {
            box-shadow: 0 0 0 2px var(--primary-accent);
        }

        .search-button {
            padding: 0 1.5rem;
            background-color: var(--primary-accent);
            border: none;
            border-radius: 0 50px 50px 0;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-button:hover {
            background-color: #e67e00;
        }
        .search-button svg {
            width: 20px;
            height: 20px;
            stroke: white;
            fill: none;
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 3.5rem;
            color: var(--primary-accent);
        }

        .categories-container {
            width: 90%;
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1rem;
        }

        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            justify-content: center;
        }

        .category-card {
            background-color: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            padding: 1.8rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--box-shadow-heavy);
        }
        .category-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: var(--border-radius-base);
            margin-bottom: 1rem;
        }
        .category-title {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            margin: 0.5rem 0;
            font-size: 1.6rem;
            font-weight: 700;
        }
        .category-desc {
            color: var(--light-text);
            font-size: 1rem;
            line-height: 1.6;
        }

        .recipes-container {
            width: 90%;
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 1rem;
        }

        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            justify-content: center;
        }

        .recipe-card {
            background-color: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--box-shadow-heavy);
        }

        .recipe-image-container {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .recipe-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .recipe-time, .recipe-calories {
            position: absolute;
            bottom: 10px;
            background-color: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 5px 10px;
            font-size: 0.9em;
            border-radius: var(--border-radius-base);
        }

        .recipe-time {
            left: 10px;
        }

        .recipe-calories {
            right: 10px;
        }

        .recipe-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .recipe-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5em;
            margin-bottom: 0.8rem;
            color: var(--dark-text);
        }

        .recipe-description {
            color: var(--light-text);
            margin-bottom: 1.2rem;
            font-size: 0.95em;
            line-height: 1.6;
            flex-grow: 1;
        }

        .recipe-tags {
            display: flex;
            flex-wrap: wrap;
            margin-top: 0.5rem;
            margin-bottom: 1.2rem;
            gap: 8px;
        }

        .recipe-tag {
            background-color: var(--background-darker);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.8em;
            color: var(--light-text);
            font-weight: 600;
        }

        .button-row {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            margin-top: 1.5rem;
        }

        .view-button, .add-button {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }

        .view-button {
            background-color: var(--primary-accent);
            color: white;
            box-shadow: 0 3px 8px rgba(255, 136, 0, 0.2);
        }

        .view-button:hover {
            background-color: #e67e00;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(255, 136, 0, 0.3);
        }

        .add-button {
            background-color: var(--secondary-accent);
            color: white;
            box-shadow: 0 3px 8px rgba(255, 178, 44, 0.2);
        }

        .add-button:hover {
            background-color: #e89b27;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(255, 178, 44, 0.3);
        }

        .recipe-details {
            display: none;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
        }

        .recipe-details h4 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-accent);
            margin-bottom: 0.8rem;
            font-weight: 700;
            font-size: 1.1em;
        }

        .recipe-details ul, .recipe-details ol {
            padding-left: 25px;
            margin-bottom: 1rem;
            font-size: 0.95em;
            color: var(--dark-text);
            line-height: 1.7;
        }

        .recipe-details li {
            margin-bottom: 0.4rem;
        }

        .loading-message {
            text-align: center;
            color: var(--light-text);
            margin: 3rem 0;
            font-size: 1.1em;
        }

        #noRecipesFound {
            text-align: center;
            color: #e74c3c;
            margin: 3rem 0;
            font-size: 1.2em;
            font-weight: bold;
        }

        #mealModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        #mealModalContent {
            background: var(--background-light);
            padding: 2.5rem;
            border-radius: var(--border-radius-large);
            width: 90%;
            max-width: 450px;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            animation: fadeInScale 0.3s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        #mealModalContent h3 {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 1.5rem;
            font-size: 1.8em;
            color: var(--dark-text);
            text-align: center;
        }

        #mealModalContent p {
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.1em;
            color: var(--light-text);
        }

        #mealModalContent strong {
            color: var(--primary-accent);
            font-weight: 700;
        }

        #mealModalContent .close-button {
            position: absolute;
            top: 15px; right: 20px;
            cursor: pointer;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            transition: color 0.2s;
        }

        #mealModalContent .close-button:hover {
            color: #777;
        }

        #mealModalContent label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 1em;
        }

        #mealModalContent input[type="date"],
        #mealModalContent select {
            width: 100%;
            padding: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
            font-size: 1em;
            color: var(--dark-text);
            background-color: var(--background-darker);
        }

        #mealModalContent input[type="date"]:focus,
        #mealModalContent select:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 2px rgba(255, 136, 0, 0.3);
        }

        #mealModalContent .add-button {
            width: 100%;
            padding: 12px;
            font-size: 1.1em;
            margin-top: 1rem;
        }


        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .navbar {
                flex-wrap: wrap;
                justify-content: center;
                padding: 1rem 3%;
            }
            .logo {
                width: 100%;
                text-align: center;
                margin-bottom: 1rem;
            }
            .nav-icon {
                position: static;
            }
            .dropdown-menu {
                top: auto;
                left: 50%;
                transform: translateX(-50%) translateY(10px);
                width: 90%;
                max-width: 250px;
            }
            .dropdown-menu.show {
                transform: translateX(-50%) translateY(0);
            }

            .back-button {
                margin-left: 3%;
            }

            .search-container {
                width: 95%;
                margin: 2rem auto;
            }
            .search-box {
                padding: 0.8rem 1rem;
            }
            .search-button {
                padding: 0 1rem;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 2.5rem;
            }

            .categories, .recipes-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .category-card, .recipe-card {
                padding: 1.2rem;
            }
            .recipe-title {
                font-size: 1.3em;
            }
            .recipe-description {
                font-size: 0.9em;
            }
            .button-row {
                flex-direction: column;
                gap: 0.8rem;
            }
            .view-button, .add-button {
                width: 100%;
                text-align: center;
                padding: 0.9rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .section-title {
                font-size: 1.8rem;
            }
            #mealModalContent {
                padding: 1.5rem;
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
        <div class="nav-icon" id="profileIcon">
            <i class="fa-solid fa-user"></i>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php">Profile</a>
                <button onclick="window.location.href='php/login.php'">Logout</button>
            </div>
        </div>
    </nav>

    <a href="index.php" class="back-button">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"></path>
        </svg>
        <span>&nbsp;Back to Home</span>
    </a>

    <div class="search-container">
        <input type="text" class="search-box" id="recipeSearch" placeholder="Search recipes by ingredients, name, or cuisine...">
        <button class="search-button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="#FF8800" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
    </div>

    <div class="categories-container" id="categorySection">
        <h2 class="section-title">Browse by Category</h2>
        <div class="categories">
            <a href="php/category.php?category=breakfast" class="category-card">
                <img src="image/breakfast.jpg" alt="Breakfast" class="category-image">
                <h3 class="category-title">Breakfast</h3>
                <p class="category-desc">Start your day right with these morning recipes</p>
            </a>
            <a href="php/category.php?category=lunch" class="category-card">
                <img src="image/lunch.jpg" alt="Lunch" class="category-image">
                <h3 class="category-title">Lunch</h3>
                <p class="category-desc">Quick and nutritious midday meal ideas</p>
            </a>
            <a href="php/category.php?category=dinner" class="category-card">
                <img src="image/dinner.jpg" alt="Dinner" class="category-image">
                <h3 class="category-title">Dinner</h3>
                <p class="category-desc">Delicious recipes for your evening meals</p>
            </a>
            <a href="php/category.php?category=snack" class="category-card">
                <img src="image/snack.jpg" alt="Snacks" class="category-image">
                <h3 class="category-title">Snacks</h3>
                <p class="category-desc">Tasty bites for any time of day</p>
            </a>
        </div>
    </div>

    <div class="recipes-container" id="allRecipesContainer">
        <h2 class="section-title">All Recipes</h2>
        <div class="recipes-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="recipe-card">
                        <div class="recipe-image-container">
                            <?php if (!empty($row['photo']) && file_exists('image/' . $row['photo'])): ?>
                                <img class="recipe-image" src="image/<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                            <?php else: ?>
                                <img class="recipe-image" src="image/placeholder.jpg" alt="Recipe Image">
                            <?php endif; ?>
                            <div class="recipe-time"><?= htmlspecialchars($row['prep_time']) ?></div>
                            <div class="recipe-calories"><?= htmlspecialchars($row['calories']) ?> calories</div>
                        </div>

                        <div class="recipe-content">
                            <h3 class="recipe-title"><?= htmlspecialchars($row['name']) ?></h3>
                            <p class="recipe-description"><?= htmlspecialchars($row['description']) ?></p>

                            <div class="recipe-tags">
                                <?php
                                $ingredients_lower = strtolower($row['ingredients']);
                                $description_lower = strtolower($row['description']);
                                $name_lower = strtolower($row['name']);

                                if (strpos($ingredients_lower, 'chicken') !== false || strpos($name_lower, 'chicken') !== false): ?>
                                    <span class="recipe-tag">Chicken</span>
                                <?php endif; ?>
                                <?php if (strpos($ingredients_lower, 'beef') !== false || strpos($name_lower, 'beef') !== false): ?>
                                    <span class="recipe-tag">Beef</span>
                                <?php endif; ?>
                                <?php if (strpos($ingredients_lower, 'fish') !== false || strpos($name_lower, 'fish') !== false || strpos($name_lower, 'seafood') !== false): ?>
                                    <span class="recipe-tag">Seafood</span>
                                <?php endif; ?>
                                <?php if (strpos($ingredients_lower, 'vegetable') !== false || strpos($ingredients_lower, 'vegetarian') !== false || (strpos($ingredients_lower, 'meat') === false && strpos($ingredients_lower, 'chicken') === false && strpos($ingredients_lower, 'beef') === false && strpos($ingredients_lower, 'fish') === false && strpos($ingredients_lower, 'pork') === false)): ?>
                                    <span class="recipe-tag">Vegetarian-Friendly</span>
                                <?php endif; ?>
                                <?php if (strpos($ingredients_lower, 'spicy') !== false || strpos($name_lower, 'spicy') !== false): ?>
                                    <span class="recipe-tag">Spicy</span>
                                <?php endif; ?>
                                <?php if ($row['calories'] && $row['calories'] < 400): ?>
                                    <span class="recipe-tag">Low Calorie</span>
                                <?php endif; ?>
                                <?php if ($row['prep_time'] && (strpos($row['prep_time'], '30 mins') !== false || strpos($row['prep_time'], '20 mins') !== false || strpos($row['prep_time'], '15 mins') !== false)): ?>
                                     <span class="recipe-tag">Quick Prep</span>
                                <?php endif; ?>

                                <?php if (!empty($row['category'])): ?>
                                    <span class="recipe-tag"><?= ucfirst(htmlspecialchars($row['category'])) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="button-row">
                                <button class="view-button" onclick="toggleDetails('details-<?= $row['recipeid'] ?>', this)">View Details</button>
                                <button class="add-button" onclick="openModal(<?= $row['recipeid'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')">Add to Meal</button>
                            </div>

                            <div id="details-<?= $row['recipeid'] ?>" class="recipe-details">
                                <h4>Ingredients:</h4>
                                <ul>
                                    <?php
                                    $ingredients = explode(',', $row['ingredients']);
                                    foreach ($ingredients as $ingredient):
                                        $trimmed_ingredient = trim($ingredient);
                                        if (!empty($trimmed_ingredient)): ?>
                                            <li><?= htmlspecialchars($trimmed_ingredient) ?></li>
                                        <?php endif;
                                    endforeach; ?>
                                </ul>

                                <h4>Instructions:</h4>
                                <ol>
                                    <?php
                                    $instructions = preg_split('/(?<=[.?!])\s+(?=[A-Z])/', $row['instructions']);
                                    foreach ($instructions as $instruction):
                                        $instruction = trim($instruction);
                                        if (!empty($instruction)): ?>
                                            <li><?= htmlspecialchars($instruction) ?></li>
                                        <?php endif;
                                    endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="loading-message">No recipes available in the database.</p>
            <?php endif; ?>
        </div>
        <div class="loading-message" id="noRecipesFound" style="display: none;">No recipes found matching your search.</div>
    </div>

    <div id="mealModal">
        <div id="mealModalContent">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h3>Add to Meal Planner</h3>
            <p>Adding: <strong id="modalRecipeName"></strong></p>

            <form action="php/add_to_mealplanner.php" method="POST">
                <input type="hidden" name="recipeid" id="modalRecipeId">

                <label for="meal_date">Select Date:</label>
                <input type="date" name="meal_date" id="meal_date" required>

                <label for="category">Select Meal Time:</label>
                <select name="category" id="category" required>
                    <option value="">-- Choose --</option>
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                    <option value="snack">Snack</option>
                </select>

                <button type="submit" class="add-button">Confirm</button>
            </form>
        </div>
    </div>

    <footer style="text-align: center; padding: 2rem; background-color: var(--background-light); border-top: 1px solid var(--border-color); color: var(--light-text); font-size: 0.9rem;">
        &copy; <?= date('Y') ?> DailyDish. All rights reserved.
    </footer>

    <script>
        document.getElementById('profileIcon').addEventListener('click', function (e) {
            e.stopPropagation();
            var menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            var menu = document.getElementById('dropdownMenu');
            var profileIcon = document.getElementById('profileIcon');
            if (menu.classList.contains('show') && !profileIcon.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

       
        document.getElementById('dropdownMenu').addEventListener('click', function (e) {
            e.stopPropagation();
        });


    
        function toggleDetails(detailsId, buttonElement) {
            var details = document.getElementById(detailsId);
            

            if (details.style.display === "block") {
                details.style.display = "none";
                buttonElement.textContent = "View Details";
                buttonElement.style.backgroundColor = 'var(--primary-accent)'; 
            } else {
                details.style.display = "block";
                buttonElement.textContent = "Hide Details";
                buttonElement.style.backgroundColor = '#e67e00'; 
            }
        }

      
        function openModal(recipeId, recipeName) {
            document.getElementById('modalRecipeId').value = recipeId;
            document.getElementById('modalRecipeName').textContent = recipeName;

          
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0'); 
            const dd = String(today.getDate()).padStart(2, '0');
            document.getElementById('meal_date').value = `${yyyy}-${mm}-${dd}`;

            document.getElementById('mealModal').style.display = 'flex'; 
        }

        function closeModal() {
            document.getElementById('mealModal').style.display = 'none';
        }

       
        window.onclick = function(event) {
            let modal = document.getElementById('mealModal');
          
             if (event.target === modal) {
                closeModal();
            }
        }


       
        const searchInput = document.getElementById('recipeSearch');
        const recipeCards = document.querySelectorAll('.recipes-grid .recipe-card'); 
        const noRecipesFoundMessage = document.getElementById('noRecipesFound');
        const categorySection = document.getElementById('categorySection');

        function filterRecipes() {
            const searchTerm = searchInput.value.toLowerCase();
            let foundMatch = false;

            recipeCards.forEach(card => {
                const title = card.querySelector('.recipe-title').textContent.toLowerCase();
                const description = card.querySelector('.recipe-description').textContent.toLowerCase();
                const ingredientsElement = card.querySelector('.recipe-details ul'); 
                const instructionsElement = card.querySelector('.recipe-details ol'); 
                const tagsElement = card.querySelector('.recipe-tags'); x

                const ingredients = ingredientsElement ? ingredientsElement.textContent.toLowerCase() : '';
                const instructions = instructionsElement ? instructionsElement.textContent.toLowerCase() : '';
                const tags = tagsElement ? tagsElement.textContent.toLowerCase() : '';


                if (title.includes(searchTerm) || description.includes(searchTerm) || ingredients.includes(searchTerm) || instructions.includes(searchTerm) || tags.includes(searchTerm)) {
                    card.style.display = 'flex';
                    foundMatch = true;
                } else {
                    card.style.display = 'none'; 
                }
            });

          
            if (foundMatch) {
                noRecipesFoundMessage.style.display = 'none';
            } else {
                noRecipesFoundMessage.style.display = 'block';
            }

            
            if (searchTerm.length > 0) {
                 categorySection.style.display = 'none';
            } else {
                 categorySection.style.display = 'block';
            }
        }

        
        searchInput.addEventListener('input', filterRecipes);

   
        document.querySelector('.search-button').addEventListener('click', filterRecipes);

         
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); 
                filterRecipes();
            }
        });
    </script>
</body>
</html>