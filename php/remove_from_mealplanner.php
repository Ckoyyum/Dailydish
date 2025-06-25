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
    $category = $_POST['category'];

    // Validate input (optional but recommended)
    if (!$recipeId || !$mealDate || !$category) {
        // Handle invalid input, maybe redirect with an error message
        header('Location: ../mealplanner.php?error=invalid_input');
        exit();
    }

    // Delete the meal from the database for the specific user, date, category, and recipe
    $deleteQuery = "DELETE FROM meal_planner WHERE userid = ? AND recipeid = ? AND meal_date = ? AND meal_type = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("iiss", $userid, $recipeId, $mealDate, $category);

    if ($deleteStmt->execute()) {
        // Success
        header('Location: ../mealplanner.php?message=removed_success');
        exit();
    } else {
        // Error during deletion
        // You might want to log the error or display a user-friendly message
        header('Location: ../mealplanner.php?error=db_error');
        exit();
    }

    $deleteStmt->close();
    $conn->close();
} else {
    // Not a POST request, maybe redirect to the meal planner page
    header('Location: ../mealplanner.php');
    exit();
}
?>
