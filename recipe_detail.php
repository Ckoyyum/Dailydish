<?php
session_start();
include 'php/db.php';

if (!isset($_GET['recipeid'])) {
    echo "No recipe selected.";
    exit();
}

$recipeId = intval($_GET['recipeid']);

$query = "SELECT * FROM recipes WHERE recipeid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $recipeId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $recipeName = htmlspecialchars($row['name']);
    $prepTime = htmlspecialchars($row['prep_time']);
    $calories = htmlspecialchars($row['calories']);
    $description = htmlspecialchars($row['description']);
    $ingredients = nl2br(htmlspecialchars($row['ingredients']));
    $instructions = nl2br(htmlspecialchars($row['instructions']));
} else {
    echo "Recipe not found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $recipeName; ?> - Recipe Detail</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .meta {
            font-size: 1rem;
            color: #888;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
        }

        .section {
            margin-bottom: 35px;
        }

        .section h2 {
            font-size: 1.5rem;
            color: #34495e;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .section p {
            font-size: 1.1rem;
            line-height: 1.6;
            white-space: pre-line;
        }

        .actions {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .actions a,
        .actions button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        .actions a:hover,
        .actions button:hover {
            background-color: #2980b9;
        }

        @media print {
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $recipeName; ?></h1>
        <div class="meta">
            <div class="meta-item">⏱️ Preparation Time: <?php echo $prepTime; ?> minutes</div>
            <div class="meta-item">🔥 Calories: <?php echo !empty($calories) ? $calories : 'Not specified'; ?></div>
        </div>

        <div class="section">
            <h2>Description</h2>
            <p><?php echo $description; ?></p>
        </div>

        <div class="section">
            <h2>Ingredients</h2>
            <p><?php echo $ingredients; ?></p>
        </div>

        <div class="section">
            <h2>Instructions</h2>
            <p><?php echo $instructions; ?></p>
        </div>

        <div class="actions">
            <a href="mealplanner.php">← Back to Meal Planner</a>
            <button onclick="window.print()">🖨️ Print Recipe</button>
        </div>
    </div>
</body>
</html>