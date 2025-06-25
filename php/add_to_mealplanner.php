<?php
session_start();
include 'db.php'; // Ensure your database connection is included

if (!isset($_SESSION['user_id'])) {
    // Redirect or show an error if the user is not logged in
    header('Location: login.php'); // Or your login page
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION['user_id'];
    $recipeId = $_POST['recipeid'];
    $mealDate = $_POST['meal_date'];
    $category = $_POST['category']; // meal_type: breakfast, lunch, etc.

    // Validate input (optional but recommended)
    if (!$recipeId || !$mealDate || !$category) {
        // Handle invalid input, maybe redirect with an error message
        header('Location: ../mealplanner.php?error=invalid_input');
        exit();
    }

    // Get the day of the week from the date
    $dayOfWeek = strtolower(date('l', strtotime($mealDate))); // e.g., 'monday'

    // Check if this meal is already planned for this user on this date and category
    $checkQuery = "SELECT * FROM meal_planner WHERE userid = ? AND meal_date = ? AND meal_type = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("iss", $userid, $mealDate, $category);
    $checkStmt->execute();
    $existing = $checkStmt->get_result();

    if ($existing->num_rows > 0) {
        // Meal already exists, you can redirect or display a message
        header('Location: ../mealplanner.php?message=already_planned');
        exit();
    } else {
        // Insert the meal into the database
        $insertQuery = "INSERT INTO meal_planner (userid, recipeid, meal_type, day_of_week, meal_date) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iisss", $userid, $recipeId, $category, $dayOfWeek, $mealDate);

        if ($insertStmt->execute()) {
            // Success
            header('Location: ../mealplanner.php?message=success');
            exit();
        } else {
            // Error during insertion
            // You might want to log the error or display a user-friendly message
            header('Location: ../mealplanner.php?error=db_error');
            exit();
        }
    }

    $checkStmt->close();
    if (isset($insertStmt)) {
        $insertStmt->close();
    }
    $conn->close();
} else {
    // Not a POST request, maybe redirect to the meal planner page
    header('Location: ../mealplanner.php');
    exit();
}
?>
