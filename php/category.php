<?php
include 'db.php';

$category = $_GET['category'];
$query = "SELECT * FROM recipes WHERE category='$category'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($category) ?> Recipes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts - Poppins for headings/logo, Nunito for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Global Styles & Variables (Copied from index.php / calories_tracking.php) */
        :root {
            --primary-accent: #FF8800; /* Your main orange */
            --secondary-accent: #FFB22C; /* Lighter orange */
            --dark-text: #333333;
            --light-text: #666666;
            --background-light: #FDFDFD; /* Lighter background for elements */
            --background-darker: #F7F7F7; /* Main page background */
            --border-color: #E0E0E0;
            --blue-button: #2196F3; /* Added for the view button */

            --border-radius-base: 8px;
            --border-radius-large: 12px;
            --box-shadow-light: 0 4px 10px rgba(0,0,0,0.05);
            --box-shadow-medium: 0 8px 20px rgba(0,0,0,0.08);
            --box-shadow-heavy: 0 10px 25px rgba(0,0,0,0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', sans-serif; /* Default body font */
            background-color: var(--background-darker);
            padding: 20px;
            color: var(--dark-text);
        }

        h2 {
            font-family: 'Poppins', sans-serif; /* Consistent heading font */
            text-align: center;
            color: var(--primary-accent); /* Use primary accent for headings */
            margin-bottom: 30px;
            font-size: 2.5em; /* Make headings more prominent */
        }

        .recipe-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px; /* Increased gap between cards */
            max-width: 1200px;
            margin: 0 auto;
        }

        .recipe-card {
            background-color: var(--background-light);
            border-radius: var(--border-radius-large); /* More rounded corners */
            box-shadow: var(--box-shadow-medium); /* Consistent shadow */
            width: 100%;
            max-width: 360px; /* Slightly wider cards */
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .recipe-card:hover {
            transform: translateY(-8px); /* Lift effect on hover */
            box-shadow: var(--box-shadow-heavy); /* Stronger shadow on hover */
        }

        .recipe-image-container {
            position: relative;
            height: 220px; /* Slightly taller images */
            overflow: hidden;
        }

        .recipe-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .recipe-card:hover .recipe-image {
            transform: scale(1.05); /* Slight zoom on image hover */
        }

        .recipe-time, .recipe-calories {
            position: absolute;
            bottom: 10px; /* Slightly more space from bottom */
            background-color: rgba(0, 0, 0, 0.75); /* Darker overlay for readability */
            color: white;
            padding: 6px 12px; /* More padding */
            border-radius: 5px; /* Rounded corners for labels */
            font-size: 0.9em;
            font-weight: 600;
        }

        .recipe-time {
            left: 10px;
        }

        .recipe-calories {
            right: 10px;
        }

        .recipe-content {
            padding: 20px; /* More padding */
        }

        .recipe-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.6em; /* Larger title */
            margin-bottom: 12px;
            color: var(--dark-text);
        }

        .recipe-description {
            color: var(--light-text);
            margin-bottom: 20px; /* More space */
            font-size: 1em; /* Slightly larger description */
            line-height: 1.6; /* Better readability */
        }

        .recipe-tags {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 10px; /* More gap */
        }

        .recipe-tag {
            background-color: var(--border-color); /* Use border color for tags */
            border-radius: 25px; /* More rounded pill shape */
            padding: 6px 15px;
            font-size: 0.85em;
            color: var(--light-text);
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .recipe-tag:hover {
            background-color: #d8d8d8; /* Slightly darker on hover */
        }

        .button-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px; /* Add some margin */
        }

        .view-button, .add-button {
            padding: 10px 20px; /* More padding for buttons */
            border: none;
            border-radius: var(--border-radius-base); /* Consistent button radius */
            cursor: pointer;
            font-weight: 700; /* Bold text */
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-family: 'Nunito', sans-serif;
            flex-grow: 1; /* Allow buttons to grow */
            margin: 0 5px; /* Small gap between buttons */
        }

        .view-button {
            background-color: var(--blue-button);
            color: white;
        }

        .view-button:hover {
            background-color: #1976D2; /* Darker blue */
            transform: translateY(-2px);
        }

        .add-button {
            background-color: var(--primary-accent);
            color: white;
        }

        .add-button:hover {
            background-color: #e67e22; /* Darker orange */
            transform: translateY(-2px);
        }
         /* Added a fix for button gap on smaller screens if they wrap */
         .button-row {
             gap: 10px; /* Add gap for flex items */
             flex-wrap: wrap;
         }
         .button-row .view-button,
         .button-row .add-button {
             flex-basis: calc(50% - 5px); /* Almost 50% width with gap */
             max-width: unset; /* Override max-width if set by flex-basis */
         }


        .recipe-details {
            display: none;
            margin-top: 20px; /* More space */
            border-top: 1px solid var(--border-color); /* Separator line */
            padding-top: 20px;
        }

        .recipe-details h4 {
            font-family: 'Poppins', sans-serif;
            color: var(--blue-button); /* Use blue accent for subheadings */
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 1.1em;
        }

        .recipe-details ul, .recipe-details ol {
            padding-left: 25px; /* More indentation */
            margin-bottom: 20px;
            color: var(--light-text);
        }

        .recipe-details li {
            margin-bottom: 8px; /* More space between list items */
            line-height: 1.5;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px; /* More space below link */
            text-decoration: none;
            color: var(--blue-button);
            font-weight: 700; /* Bold link */
            font-size: 1.1em;
            transition: color 0.2s ease;
        }
        .back-link:hover {
            color: #1976D2; /* Darker blue on hover */
        }

        /* Modal Styles */
        #mealModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); /* Slightly darker overlay */
            z-index: 1000;
            display: flex; /* Use flexbox to center content */
            align-items: center;
            justify-content: center;
        }

        #mealModalContent {
            background: var(--background-light);
            padding: 30px; /* More padding */
            border-radius: var(--border-radius-large); /* Consistent border radius */
            width: 90%;
            max-width: 450px; /* Slightly wider modal */
            position: relative;
            box-shadow: var(--box-shadow-heavy); /* Stronger shadow for modal */
            animation: fadeInScale 0.3s ease-out; /* Add animation */
        }

        #mealModalContent h3 {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 20px;
            font-size: 1.6em;
            color: var(--dark-text);
            text-align: center;
        }

        #mealModalContent p {
            margin-bottom: 25px;
            color: var(--light-text);
            text-align: center;
        }

        #mealModalContent p strong {
            color: var(--primary-accent);
        }

        #mealModalContent span.close-button { /* Renamed for clarity */
            position: absolute;
            top: 15px; right: 20px;
            cursor: pointer;
            font-size: 30px; /* Larger close button */
            font-weight: normal; /* Normal weight for 'x' */
            color: var(--light-text);
            transition: color 0.2s ease;
        }
        #mealModalContent span.close-button:hover {
            color: var(--dark-text);
        }

        #mealModalContent input[type="date"],
        #mealModalContent select {
            width: 100%;
            padding: 12px; /* More padding */
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
            font-size: 1em;
            color: var(--dark-text);
            background-color: var(--background-darker); /* Subtle background */
        }

        #mealModalContent label {
            display: block;
            margin-bottom: 8px; /* More space */
            font-weight: 700;
            color: var(--dark-text);
            font-size: 0.95em;
        }

        #mealModalContent button.add-button { /* Target specifically modal button */
            width: 100%;
            padding: 12px 20px; /* Consistent padding with other buttons */
            font-size: 1.1em;
            margin-top: 10px; /* Space from inputs */
        }

        /* Animation for modal */
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @media (max-width: 768px) {
            .recipe-container {
                flex-direction: column;
                align-items: center;
                gap: 25px;
            }

            .recipe-card {
                max-width: 90%; /* Allow cards to take more width */
            }

            h2 {
                font-size: 2em;
            }

            .recipe-title {
                font-size: 1.4em;
            }

            .recipe-content {
                padding: 15px;
            }

            .button-row {
                flex-direction: column;
                gap: 10px;
            }
            .button-row .view-button,
            .button-row .add-button {
                flex-basis: auto; /* Reset flex-basis for column layout */
                width: 100%; /* Full width buttons */
                margin: 0; /* Remove horizontal margin */
            }

            #mealModalContent {
                margin: 50px auto; /* Adjust modal position for smaller screens */
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <a href="../recipe.php" class="back-link">← Back to Recipes</a>
    <h2><?= ucfirst($category) ?> Recipes</h2>

    <div class="recipe-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="recipe-card">
                    <div class="recipe-image-container">
                        <?php if ($row['photo']): ?>
                            <img class="recipe-image" src="../image/<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <?php else: ?>
                            <img class="recipe-image" src="../image/placeholder.jpg" alt="Recipe Image">
                        <?php endif; ?>
                        <div class="recipe-time"><?= htmlspecialchars($row['prep_time']) ?></div>
                        <div class="recipe-calories"><?= htmlspecialchars($row['calories']) ?> calories</div>
                    </div>

                    <div class="recipe-content">
                        <h3 class="recipe-title"><?= htmlspecialchars($row['name']) ?></h3>
                        <p class="recipe-description"><?= htmlspecialchars($row['description']) ?></p>

                        <div class="recipe-tags">
                            <?php
                            // Example: Check for specific keywords in ingredients for tags
                            // You might want a dedicated 'tags' column in your database for better management
                            $ingredients_lower = strtolower($row['ingredients']);
                            if (strpos($ingredients_lower, 'chicken') !== false || strpos($ingredients_lower, 'beef') !== false || strpos($ingredients_lower, 'pork') !== false || strpos($ingredients_lower, 'fish') !== false): ?>
                                <span class="recipe-tag">Contains Meat</span>
                            <?php else: ?>
                                <span class="recipe-tag">Vegetarian</span>
                            <?php endif; ?>

                            <?php if ($row['calories'] && (int)$row['calories'] < 400): ?>
                                <span class="recipe-tag">Low Calorie</span>
                            <?php endif; ?>
                            <?php // Add more tags based on your recipe data/logic ?>
                        </div>

                        <div class="button-row">
                            <button class="view-button" onclick="toggleDetails('details-<?= $row['recipeid'] ?>')">View Details</button>
                            <button class="add-button" onclick="openModal(<?= $row['recipeid'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')">Add to Meal</button>
                        </div>

                        <div id="details-<?= $row['recipeid'] ?>" class="recipe-details">
                            <h4>Ingredients:</h4>
                            <ul>
                                <?php
                                $ingredients = explode(',', $row['ingredients']);
                                foreach ($ingredients as $ingredient):
                                    $ingredient = trim($ingredient);
                                    if (!empty($ingredient)): ?>
                                        <li><?= htmlspecialchars($ingredient) ?></li>
                                    <?php endif;
                                endforeach; ?>
                            </ul>

                            <h4>Instructions:</h4>
                            <ol>
                                <?php
                                // Split instructions by period and filter out empty steps
                                $instructions = array_filter(array_map('trim', explode('.', $row['instructions'])));
                                foreach ($instructions as $instruction): ?>
                                    <li><?= htmlspecialchars($instruction) ?>.</li>
                                <?php endforeach; ?>
                            </ol>

                            <button class="view-button" style="margin-top: 15px;" onclick="toggleDetails('details-<?= $row['recipeid'] ?>')">Hide Details</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; color: var(--light-text); font-size: 1.2em;">No recipes found for this category.</p>
        <?php endif; ?>
    </div>

    <!-- Modal HTML -->
    <div id="mealModal">
        <div id="mealModalContent">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h3>Add to Meal Planner</h3>
            <p>Adding: <strong id="modalRecipeName"></strong></p>

            <form action="add_to_mealplanner.php" method="POST">
                <input type="hidden" name="recipeid" id="modalRecipeId">

                <label for="meal_date">Select Date:</label>
                <input type="date" name="meal_date" id="meal_date" required>

                <label for="category_modal">Select Meal Time:</label>
                <select name="category" id="category_modal" required>
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

    <script>
        function toggleDetails(detailsId) {
            var details = document.getElementById(detailsId);
            if (details.style.display === "block") {
                details.style.display = "none";
            } else {
                details.style.display = "block";
            }
        }

        function openModal(recipeId, recipeName) {
            document.getElementById('modalRecipeId').value = recipeId;
            document.getElementById('modalRecipeName').textContent = recipeName;
            // Set today's date as default in the date input
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months start at 0!
            const dd = String(today.getDate()).padStart(2, '0');
            document.getElementById('meal_date').value = `${yyyy}-${mm}-${dd}`;
            document.getElementById('mealModal').style.display = 'flex'; // Use flex for centering
        }

        function closeModal() {
            document.getElementById('mealModal').style.display = 'none';
        }

        // Close modal when clicking outside the content box
        window.onclick = function(event) {
            let modal = document.getElementById('mealModal');
            let modalContent = document.getElementById('mealModalContent');
            // If the clicked target is the modal backdrop itself and not inside the modal content
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>