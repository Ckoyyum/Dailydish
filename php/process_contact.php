<?php
// php/process_contact.php

header('Content-Type: application/json'); // Tell browser to expect JSON response

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic input validation
    $name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['user_email'] ?? '');
    $subject = trim($_POST['message_subject'] ?? '');
    $message = trim($_POST['user_message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Database connection details (REPLACE WITH YOUR ACTUAL CREDENTIALS)
    $servername = "db";
$username = "php_docker";              // Usually 'root' for XAMPP/WAMP
$password = "password";                  // Usually empty for XAMPP/WAMP
$dbname = "php_docker";          // Your database name from the SQL dump
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed: ' . $conn->connect_error;
        echo json_encode($response);
        exit;
    }

    // Prepare and bind
    // Using prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO feedback (name, email, subject, message) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit;
    }

    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    // Execute the statement
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Feedback submitted successfully!';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>