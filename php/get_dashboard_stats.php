<?php
// php/get_dashboard_stats.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$servername = "db";
$username = "php_docker";
$password = "password";
$dbname = "php_docker";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Total Users
    $userStmt = $conn->prepare("SELECT COUNT(*) as user_count FROM user");
    $userStmt->execute();
    $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userCount = $userResult['user_count'];

    // Total Recipes
    $recipeStmt = $conn->prepare("SELECT COUNT(*) as recipe_count FROM recipes");
    $recipeStmt->execute();
    $recipeResult = $recipeStmt->fetch(PDO::FETCH_ASSOC);
    $recipeCount = $recipeResult['recipe_count'];

    // Average Calories (from recipes)
    $avgStmt = $conn->prepare("SELECT AVG(calories) as avg_calories FROM recipes WHERE calories > 0");
    $avgStmt->execute();
    $avgResult = $avgStmt->fetch(PDO::FETCH_ASSOC);
    $avgCalories = round($avgResult['avg_calories'] ?? 0, 1);

    // Total Meal Plans
    $mealPlanStmt = $conn->prepare("SELECT COUNT(*) as meal_plan_count FROM meal_planner");
    $mealPlanStmt->execute();
    $mealPlanResult = $mealPlanStmt->fetch(PDO::FETCH_ASSOC);
    $mealPlanCount = $mealPlanResult['meal_plan_count'];

    echo json_encode([
        'success' => true,
        'users' => (int)$userCount,
        'recipes' => (int)$recipeCount,
        'avgCalories' => (float)$avgCalories,
        'mealPlans' => (int)$mealPlanCount
    ]);

} catch(PDOException $e) {
    error_log("Database connection or query failed in get_dashboard_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Server error: Could not retrieve dashboard data.'
    ]);
} finally {
    $conn = null;
}
?>