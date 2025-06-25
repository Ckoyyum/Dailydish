<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM user WHERE userid = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        // Successful deletion
        $message = "User with ID #{$userId} deleted successfully.";
        // Redirect back to the user management page with a success message
        header("Location: manage_users.php?message=" . urlencode($message) . "&type=success");
        exit();
    } else {
        // Error during deletion
        $message = "Error deleting user with ID #{$userId}: " . $stmt->error;
        // Redirect back to the user management page with an error message
        header("Location: manage_users.php?message=" . urlencode($message) . "&type=error");
        exit();
    }

    $stmt->close();
} else {
    // No user ID provided
    $message = "No user ID specified for deletion.";
    // Redirect back to the user management page with an error message
    header("Location: manage_users.php?message=" . urlencode($message) . "&type=error");
    exit();
}

$conn->close();
?>
