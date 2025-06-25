<?php
// php/get_notifications.php

header('Content-Type: application/json');

$response = ['success' => false, 'unread_count' => 0, 'error' => ''];

// Database connection details (REPLACE WITH YOUR ACTUAL CREDENTIALS)
$servername = "db";
$username = "php_docker";              // Usually 'root' for XAMPP/WAMP
$password = "password";                  // Usually empty for XAMPP/WAMP
$dbname = "php_docker";          // Your database name from the SQL dump

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $response['error'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit;
}

// Get unread feedback count
$sql = "SELECT COUNT(*) AS unread_count FROM feedback WHERE is_read = FALSE";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $response['success'] = true;
    $response['unread_count'] = (int)$row['unread_count']; // Cast to int
} else {
    $response['error'] = 'Error fetching unread count: ' . $conn->error;
}

$conn->close();

echo json_encode($response);
?>