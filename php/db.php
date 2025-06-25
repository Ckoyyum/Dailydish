<?php
// Railway environment variables (when deployed) or fallback to local Docker (when developing locally)
$servername = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? "db";
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? "php_docker";
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? "password";
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? "php_docker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Connection failed in db.php: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>