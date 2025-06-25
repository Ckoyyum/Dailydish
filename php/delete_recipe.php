<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php"); // Redirect non-admins
    exit();
}

include 'db.php'; // Include your existing mysqli connection from db.php

// Check if recipe ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $recipe_id = $conn->real_escape_string($_GET['id']); // Sanitize input

    // Basic validation
    if (filter_var($recipe_id, FILTER_VALIDATE_INT)) {
        // First, fetch the photo path to delete the file from the server
        $photo_path = null;
        $sql_fetch_photo = "SELECT photo FROM recipes WHERE recipeid = ?";
        if ($stmt_fetch = $conn->prepare($sql_fetch_photo)) {
            $stmt_fetch->bind_param("i", $recipe_id);
            $stmt_fetch->execute();
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row = $result_fetch->fetch_assoc();
                $photo_path = $row['photo'];
            }
            $stmt_fetch->close();
        }

        // Prepare a delete statement for the database record
        $sql_delete = "DELETE FROM recipes WHERE recipeid = ?";

        if ($stmt_delete = $conn->prepare($sql_delete)) {
            // Bind parameters
            $stmt_delete->bind_param("i", $recipe_id);

            // Attempt to execute the prepared statement
            if ($stmt_delete->execute()) {
                // If DB record deleted successfully, try to delete the photo file
                if (!empty($photo_path)) {
                    $full_path = "../" . $photo_path; // Adjust relative path as needed
                    if (file_exists($full_path) && is_file($full_path)) {
                        if (!unlink($full_path)) {
                            error_log("Failed to delete recipe photo file: " . $full_path);
                            // Set a warning message, but still success for DB deletion
                            $_SESSION['warning_message'] = "Recipe deleted from database, but failed to delete photo file.";
                        }
                    }
                }
                $_SESSION['success_message'] = "Recipe deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Error deleting recipe: " . $stmt_delete->error;
                error_log("Error deleting recipe (DB): " . $stmt_delete->error);
            }
            $stmt_delete->close();
        } else {
            $_SESSION['error_message'] = "Error preparing delete statement: " . $conn->error;
            error_log("Error preparing delete statement: " . $conn->error);
        }
    } else {
        $_SESSION['error_message'] = "Invalid recipe ID for deletion.";
    }
} else {
    $_SESSION['error_message'] = "No recipe ID specified for deletion.";
}

// Close connection (optional, as PHP script will terminate soon anyway)
$conn->close();

// Redirect back to the recipe management page
header("Location: manage_recipes.php");
exit();
?>