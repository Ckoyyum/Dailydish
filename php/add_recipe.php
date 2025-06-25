<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$name = $_POST['name'];
$description = $_POST['description'];
$calories = $_POST['calories'];
$prep_time = $_POST['prep_time'];
$ingredients = implode("\n", $_POST['ingredients']);
$instructions = implode("\n", $_POST['instructions']);
$category = $_POST['category'];

    
$photo = '';
if ($_FILES['photo']['name']) {
    $target_dir = "../image/";
    $photo = basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $photo;
    move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
}

$sql = "INSERT INTO recipes (name, photo, description, calories, prep_time, ingredients, instructions, category)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssissss", $name, $photo, $description, $calories, $prep_time, $ingredients, $instructions, $category);

if ($stmt->execute()) {
    header("Location: manage_recipes.php");
    exit();
} else {
    echo "Error adding recipe: " . $stmt->error;
}
?>
