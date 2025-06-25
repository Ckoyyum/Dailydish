<?php
session_start();
include 'db.php'; // Your database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$successMessage = '';
$errorMessage = '';

// --- FETCH CURRENT USER DATA ---
$userSql = "SELECT name, email FROM user WHERE userid = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$stmt->close();

$profileSql = "SELECT height, current_weight, target_weight, age, gender, dietary_restrictions, fitness_goal, activity_level, bmi, daily_calories FROM user_profile WHERE userid = ?";
$stmt = $conn->prepare($profileSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$profileResult = $stmt->get_result();
$profileData = $profileResult->fetch_assoc();
$stmt->close();

// Set default values if profile data is missing (for form pre-population)
$profileData = $profileData ?? [
    'height' => '',
    'current_weight' => '',
    'target_weight' => '',
    'age' => '',
    'gender' => '',
    'dietary_restrictions' => '',
    'fitness_goal' => '',
    'activity_level' => '',
    'bmi' => '', // BMI is calculated, not edited, but useful to have it for context
    'daily_calories' => ''
];

// --- PROCESS FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $height = filter_var($_POST['height'], FILTER_VALIDATE_FLOAT);
    $current_weight = filter_var($_POST['current_weight'], FILTER_VALIDATE_FLOAT);
    $target_weight = filter_var($_POST['target_weight'], FILTER_VALIDATE_FLOAT);
    $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
    $gender = htmlspecialchars(trim($_POST['gender']));
    $dietary_restrictions_array = isset($_POST['dietary_restrictions']) ? $_POST['dietary_restrictions'] : [];
    $dietary_restrictions = implode(',', array_map('htmlspecialchars', $dietary_restrictions_array));
    $fitness_goal = htmlspecialchars(trim($_POST['fitness_goal']));
    $activity_level = filter_var($_POST['activity_level'], FILTER_VALIDATE_INT);
    $daily_calories = filter_var($_POST['daily_calories'], FILTER_VALIDATE_INT);

    $errors = [];

    // Basic validation
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if ($height === false || $height <= 0) $errors[] = "Valid height is required.";
    if ($current_weight === false || $current_weight <= 0) $errors[] = "Valid current weight is required.";
    if ($target_weight === false || $target_weight <= 0) $errors[] = "Valid target weight is required.";
    if ($age === false || $age <= 0) $errors[] = "Valid age is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($fitness_goal)) $errors[] = "Fitness goal is required.";
    if ($activity_level === false || $activity_level < 1 || $activity_level > 5) $errors[] = "Valid activity level is required.";
    if ($daily_calories === false || $daily_calories <= 0) $errors[] = "Valid daily calorie goal is required.";

    // If email is changed, check for uniqueness
    if ($email !== $userData['email']) {
        $checkEmailSql = "SELECT userid FROM user WHERE email = ? AND userid != ?";
        $stmt = $conn->prepare($checkEmailSql);
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $checkEmailResult = $stmt->get_result();
        if ($checkEmailResult->num_rows > 0) {
            $errors[] = "Email already in use by another account.";
        }
        $stmt->close();
    }


    if (empty($errors)) {
        // --- Calculate BMI ---
        // BMI = weight (kg) / (height (m))^2
        $heightInMeters = $height / 100; // Convert cm to meters
        $bmi = ($heightInMeters > 0) ? round($current_weight / ($heightInMeters * $heightInMeters), 1) : 0;

        // --- Update 'user' table ---
        $conn->begin_transaction(); // Start transaction for atomicity

        try {
            $updateUserSql = "UPDATE user SET name = ?, email = ? WHERE userid = ?";
            $stmt = $conn->prepare($updateUserSql);
            $stmt->bind_param("ssi", $name, $email, $userId);
            $stmt->execute();

            // --- Update or Insert into 'user_profile' table ---
            // Check if a profile exists for the user
            $checkProfileSql = "SELECT userid FROM user_profile WHERE userid = ?";
            $stmtCheck = $conn->prepare($checkProfileSql);
            $stmtCheck->bind_param("i", $userId);
            $stmtCheck->execute();
            $existingProfile = $stmtCheck->get_result()->fetch_assoc();
            $stmtCheck->close();

            if ($existingProfile) {
                // Update existing profile
                $updateProfileSql = "UPDATE user_profile SET height = ?, current_weight = ?, target_weight = ?, age = ?, gender = ?, dietary_restrictions = ?, fitness_goal = ?, activity_level = ?, bmi = ?, daily_calories = ? WHERE userid = ?";
                $stmtProfile = $conn->prepare($updateProfileSql);
                $stmtProfile->bind_param("dddissisidi", $height, $current_weight, $target_weight, $age, $gender, $dietary_restrictions, $fitness_goal, $activity_level, $bmi, $daily_calories, $userId);
            } else {
                // Insert new profile
                $insertProfileSql = "INSERT INTO user_profile (userid, height, current_weight, target_weight, age, gender, dietary_restrictions, fitness_goal, activity_level, bmi, daily_calories) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtProfile = $conn->prepare($insertProfileSql);
                $stmtProfile->bind_param("idddissisid", $userId, $height, $current_weight, $target_weight, $age, $gender, $dietary_restrictions, $fitness_goal, $activity_level, $bmi, $daily_calories);
            }
            $stmtProfile->execute();

            // Commit transaction
            $conn->commit();
            $successMessage = "Profile updated successfully!";

            // Re-fetch updated data to pre-populate form for next display
            // This is important to reflect changes immediately without redirect.
            $stmt = $conn->prepare($userSql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $stmt = $conn->prepare($profileSql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $profileData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Set default values again in case some were empty and are now filled
            $profileData = $profileData ?? [
                'height' => '', 'current_weight' => '', 'target_weight' => '',
                'age' => '', 'gender' => '', 'dietary_restrictions' => '',
                'fitness_goal' => '', 'activity_level' => '', 'bmi' => '', 'daily_calories' => ''
            ];


        } catch (mysqli_sql_exception $e) {
            $conn->rollback(); // Rollback on error
            $errorMessage = "Database error: " . $e->getMessage();
        } finally {
            if (isset($stmtProfile)) $stmtProfile->close();
        }
    } else {
        $errorMessage = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - DailyDish</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-accent: #FF8800;
            --secondary-accent: #FFB22C;
            --dark-text: #333333;
            --light-text: #666666;
            --background-light: #FDFDFD;
            --background-darker: #F7F7F7;
            --border-color: #E0E0E0;
            --border-radius-base: 8px;
            --border-radius-large: 12px;
            --box-shadow-light: 0 4px 10px rgba(0,0,0,0.05);
            --box-shadow-medium: 0 8px 20px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--background-darker);
            min-height: 100vh;
            color: var(--dark-text);
            display: flex;
            flex-direction: column;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%;
            background-color: var(--background-light);
            box-shadow: var(--box-shadow-light);
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid var(--border-color);
        }
        .nav-links {
            display: flex;
            gap: 2.5rem;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--dark-text);
            font-weight: 600;
            transition: color 0.3s ease-in-out;
        }
        .nav-links a:hover {
            color: var(--primary-accent);
        }
        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-text);
        }
        .logo span {
            color: var(--primary-accent);
        }
        .nav-icon {
            position: relative;
            cursor: pointer;
            padding: 8px;
            border-radius: var(--border-radius-base);
            transition: background-color 0.2s;
        }
        .nav-icon:hover {
            background-color: var(--border-color);
        }
        .nav-icon i {
            font-size: 22px;
            color: var(--primary-accent);
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 45px;
            background-color: var(--background-light);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-radius: var(--border-radius-base);
            overflow: hidden;
            z-index: 100;
            min-width: 150px;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }
        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .dropdown-menu a, .dropdown-menu button {
            display: block;
            padding: 0.8rem 1.2rem;
            text-decoration: none;
            color: var(--dark-text);
            transition: background 0.3s ease-in-out;
            font-weight: 400;
            width: 100%;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
        }
        .dropdown-menu a:hover, .dropdown-menu button:hover {
            background-color: var(--background-darker);
            color: var(--primary-accent);
        }

        /* Form Specific Styles */
        .edit-profile-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            padding: 40px;
        }

        .edit-profile-container h1 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-accent);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius-base);
            text-align: center;
            font-weight: 600;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
            font-size: 1rem;
            color: var(--dark-text);
            background-color: var(--background-darker);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 136, 0, 0.2);
        }

        .form-group.radio-group label {
            display: inline-block;
            margin-right: 20px;
        }
        .form-group.radio-group input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px 0;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            font-weight: 400;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            width: auto;
            transform: scale(1.1); /* Slightly larger checkboxes */
        }

        .form-section-title {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-accent);
            font-size: 1.5em;
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--border-color);
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1em;
            box-shadow: var(--box-shadow-light);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
            color: white;
        }

        .btn-secondary {
            background: var(--background-light);
            color: var(--primary-accent);
            border: 2px solid var(--primary-accent);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        }

        footer {
            text-align: center;
            padding: 2rem;
            background-color: var(--background-light);
            border-top: 1px solid var(--border-color);
            color: var(--light-text);
            font-size: 0.9rem;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .navbar .nav-links {
                display: none;
            }
            .edit-profile-container {
                margin: 20px;
                padding: 25px;
            }
            .edit-profile-container h1 {
                font-size: 2em;
            }
            .button-group {
                flex-direction: column;
                gap: 15px;
            }
            .btn {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="recipe.php">Recipes</a>
            <a href="mealplanner.php">Meal Planner</a>
            <a href="php/calories_tracking.php">Calories Tracking</a>
            <a href="about.php">About Us</a>
        </div>
        <div class="logo">Daily<span>Dish</span></div>
        <div class="nav-icon" id="profileIcon">
            <i class="fa-solid fa-user"></i>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="../profile.php">Profile</a>
                <button onclick="window.location.href='php/logout.php'">Logout</button>
            </div>
        </div>
    </nav>

    <div class="edit-profile-container">
        <h1>Edit Your Profile</h1>

        <?php if ($successMessage): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form action="edit_profile.php" method="POST">
            <div class="form-section-title">Personal Information</div>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="age">Age (years)</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($profileData['age'] ?? ''); ?>" min="1" max="120" required>
            </div>
            <div class="form-group radio-group">
                <label>Gender</label>
                <label><input type="radio" name="gender" value="male" <?php echo ($profileData['gender'] ?? '') == 'male' ? 'checked' : ''; ?> required> Male</label>
                <label><input type="radio" name="gender" value="female" <?php echo ($profileData['gender'] ?? '') == 'female' ? 'checked' : ''; ?>> Female</label>
                <label><input type="radio" name="gender" value="other" <?php echo ($profileData['gender'] ?? '') == 'other' ? 'checked' : ''; ?>> Other</label>
            </div>

            <div class="form-section-title">Body Statistics</div>
            <div class="form-group">
                <label for="height">Height (cm)</label>
                <input type="number" id="height" name="height" value="<?php echo htmlspecialchars($profileData['height'] ?? ''); ?>" min="50" max="250" step="0.1" required>
            </div>
            <div class="form-group">
                <label for="current_weight">Current Weight (kg)</label>
                <input type="number" id="current_weight" name="current_weight" value="<?php echo htmlspecialchars($profileData['current_weight'] ?? ''); ?>" min="20" max="400" step="0.1" required>
            </div>
            <div class="form-group">
                <label for="target_weight">Target Weight (kg)</label>
                <input type="number" id="target_weight" name="target_weight" value="<?php echo htmlspecialchars($profileData['target_weight'] ?? ''); ?>" min="20" max="400" step="0.1" required>
            </div>
            <div class="form-group">
                <label for="activity_level">Activity Level</label>
                <select id="activity_level" name="activity_level" required>
                    <option value="">Select an activity level</option>
                    <option value="1" <?php echo ($profileData['activity_level'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sedentary (little or no exercise)</option>
                    <option value="2" <?php echo ($profileData['activity_level'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Lightly active (light exercise/sports 1-3 days/week)</option>
                    <option value="3" <?php echo ($profileData['activity_level'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Moderately active (moderate exercise/sports 3-5 days/week)</option>
                    <option value="4" <?php echo ($profileData['activity_level'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Very active (hard exercise/sports 6-7 days a week)</option>
                    <option value="5" <?php echo ($profileData['activity_level'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Extra active (very hard exercise/sports & physical job)</option>
                </select>
            </div>

            <div class="form-section-title">Health Goals & Preferences</div>
            <div class="form-group">
                <label for="fitness_goal">Fitness Goal</label>
                <select id="fitness_goal" name="fitness_goal" required>
                    <option value="">Select a fitness goal</option>
                    <option value="weight_loss" <?php echo ($profileData['fitness_goal'] ?? '') == 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                    <option value="weight_gain" <?php echo ($profileData['fitness_goal'] ?? '') == 'weight_gain' ? 'selected' : ''; ?>>Weight Gain</option>
                    <option value="maintenance" <?php echo ($profileData['fitness_goal'] ?? '') == 'maintenance' ? 'selected' : ''; ?>>Weight Maintenance</option>
                </select>
            </div>
            <div class="form-group">
                <label for="daily_calories">Daily Calorie Goal (kcal)</label>
                <input type="number" id="daily_calories" name="daily_calories" value="<?php echo htmlspecialchars($profileData['daily_calories'] ?? ''); ?>" min="1000" max="5000" required>
            </div>
            <div class="form-group">
                <label>Dietary Restrictions (Select all that apply)</label>
                <div class="checkbox-group">
                    <?php
                    $allRestrictions = [
                        'vegan' => 'Vegan',
                        'vegetarian' => 'Vegetarian',
                        'gluten_free' => 'Gluten Free',
                        'dairy_free' => 'Dairy Free',
                        'low_carb' => 'Low Carb',
                        'low_fat' => 'Low Fat',
                        'halal' => 'Halal',
                        'kosher' => 'Kosher'
                    ];
                    $currentRestrictions = explode(',', $profileData['dietary_restrictions'] ?? '');
                    $currentRestrictions = array_map('trim', $currentRestrictions); // Ensure no whitespace issues

                    foreach ($allRestrictions as $value => $label) {
                        $checked = in_array($value, $currentRestrictions) ? 'checked' : '';
                        echo "<label><input type='checkbox' name='dietary_restrictions[]' value='{$value}' {$checked}> {$label}</label>";
                    }
                    ?>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="profile.php" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cancel</a>
            </div>
        </form>
    </div>

    <footer>
        &copy; <?= date('Y') ?> DailyDish. All rights reserved.
    </footer>

    <script>
        // Dropdown menu functionality (copied from profile.php)
        document.getElementById('profileIcon').addEventListener('click', function (e) {
            e.stopPropagation();
            var menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            var menu = document.getElementById('dropdownMenu');
            if (menu.classList.contains('show') && !document.getElementById('profileIcon').contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        document.getElementById('dropdownMenu').addEventListener('click', function (e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>