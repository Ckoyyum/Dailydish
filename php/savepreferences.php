<?php
header('Content-Type: application/json');
// Start session at the very beginning
session_start();

include 'db.php';

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = "Not logged in";
    echo json_encode($response);
    exit();
}

$userid = $_SESSION['user_id'];

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user profile data from POST
    $height = $_POST['height'];
    $current_weight = $_POST['current_weight'];
    $target_weight = $_POST['target_weight'] ?? null; // Optional
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $dietary_restrictions = $_POST['dietary_restrictions'] ?? null; // Optional
    $fitness_goal = $_POST['fitness_goal'];
    $activity_level = $_POST['activity_level'] ?? 3; // Default to moderate activity

    // Validate the data
    if (empty($height) || empty($current_weight) || empty($age) || empty($gender) || empty($fitness_goal)) {
        $response['message'] = "All required fields must be filled out";
        echo json_encode($response);
        exit();
    }

    // Input validation for numeric values
    if (!is_numeric($height) || !is_numeric($current_weight) || !is_numeric($age) || !is_numeric($activity_level)) {
        $response['message'] = "Invalid numeric values provided";
        echo json_encode($response);
        exit();
    }

    // Additional validation
    if ($height <= 0 || $current_weight <= 0 || $age <= 0 || $age > 120) {
        $response['message'] = "Please enter valid values for height, weight, and age";
        echo json_encode($response);
        exit();
    }

    // Calculate BMI
    // Formula: weight (kg) / (height (m))^2
    $height_in_meters = $height / 100; // Convert cm to meters
    $bmi = $current_weight / ($height_in_meters * $height_in_meters);
    $bmi = round($bmi, 2); // Round to 2 decimal places

    // Calculate daily calorie needs using the Mifflin-St Jeor Equation
    if ($gender == 'male') {
        $bmr = (10 * $current_weight) + (6.25 * $height) - (5 * $age) + 5;
    } else { // female or other
        $bmr = (10 * $current_weight) + (6.25 * $height) - (5 * $age) - 161;
    }

    // Apply activity level multiplier to get total daily energy expenditure (TDEE)
    $activity_multipliers = [
        1 => 1.2,  // Sedentary: little or no exercise
        2 => 1.375, // Light: exercise 1-3 times/week
        3 => 1.55,  // Moderate: exercise 3-5 times/week
        4 => 1.725, // Active: exercise 6-7 times/week
        5 => 1.9    // Very active: hard exercise & physical job or 2x training
    ];

    $tdee = round($bmr * $activity_multipliers[$activity_level]);

    // Adjust calories based on fitness goal
    $daily_calories = $tdee; // Start with maintenance calories

    switch ($fitness_goal) {
        case 'weight_loss':
            $daily_calories = round($tdee * 0.8); // 20% deficit
            break;
        case 'weight_gain':
            $daily_calories = round($tdee * 1.15); // 15% surplus
            break;
        case 'muscle_gain':
            $daily_calories = round($tdee * 1.1); // 10% surplus
            break;
        // For 'maintenance', we keep the TDEE value
    }

    // Check if user profile already exists
    $stmt = $conn->prepare("SELECT profileid FROM user_profile WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE user_profile SET 
            height = ?, 
            current_weight = ?, 
            target_weight = ?, 
            age = ?, 
            gender = ?, 
            dietary_restrictions = ?, 
            fitness_goal = ?, 
            activity_level = ?,
            bmi = ?,
            daily_calories = ?,
            updated_at = CURRENT_TIMESTAMP 
            WHERE userid = ?");
        
        $stmt->bind_param("dddisssiidi", 
            $height, 
            $current_weight, 
            $target_weight, 
            $age, 
            $gender, 
            $dietary_restrictions, 
            $fitness_goal, 
            $activity_level,
            $bmi,
            $daily_calories,
            $userid
        );
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO user_profile 
            (userid, height, current_weight, target_weight, age, gender, dietary_restrictions, fitness_goal, activity_level, bmi, daily_calories) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("iddiisssidi", 
            $userid, 
            $height, 
            $current_weight, 
            $target_weight, 
            $age, 
            $gender, 
            $dietary_restrictions, 
            $fitness_goal, 
            $activity_level,
            $bmi,
            $daily_calories
        );
    }

    if ($stmt->execute()) {
        // Upsert into user_preferences
        $preference_name = 'daily_calories';
        $value = $daily_calories;
        $pref_stmt = $conn->prepare("INSERT INTO user_preferences (user_id, preference_name, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value)");
        $pref_stmt->bind_param("iss", $userid, $preference_name, $value);
        $pref_stmt->execute();

        // Mark preferences as completed
        $update_stmt = $conn->prepare("UPDATE user SET has_completed_preferences = 1 WHERE userid = ?");
        $update_stmt->bind_param("i", $userid);
        $update_stmt->execute();

        $response['success'] = true;
        $response['message'] = "User preferences saved successfully";
        echo json_encode($response);
        exit();
    } else {
        $response['message'] = "Error saving preferences: " . $stmt->error;
        echo json_encode($response);
        exit();
    }
} else {
    $response['message'] = "Invalid request method";
    echo json_encode($response);
    exit();
}
?>
<script>
window.location.href = '../index.php';
</script>
