<?php
session_start();
include 'db.php'; // Your database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit();
}

$userId = $_SESSION['user_id'];

// Start a transaction for atomicity
$conn->begin_transaction();

try {
    // 1. Delete from user_preferences (if applicable)
    $stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // 2. Delete from meal_planner (if applicable)
    $stmt = $conn->prepare("DELETE FROM meal_planner WHERE userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // 3. Delete from user_profile (important, as it's linked to user)
    $stmt = $conn->prepare("DELETE FROM user_profile WHERE userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // 4. Delete from user (main user record - this should be last)
    $stmt = $conn->prepare("DELETE FROM user WHERE userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // If all successful, commit the transaction
    $conn->commit();

    // Destroy session and redirect
    session_unset();
    session_destroy();
    header("Location: ../index.php?message=account_deleted"); // Redirect to home with success message
    exit();

} catch (mysqli_sql_exception $e) {
    // If any error, rollback the transaction
    $conn->rollback();
    error_log("Account deletion failed for user ID $userId: " . $e->getMessage()); // Log the error
    $_SESSION['error_message'] = "Failed to delete account. Please try again or contact support.";
    header("Location: ../profile.php"); // Redirect back to profile with error
    exit();
} finally {
    $conn->close();
}
?>