<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php"); // Redirect non-admins
    exit();
}

include 'db.php'; // Include your existing mysqli connection from db.php

$recipe = null;
$error_message = '';
$success_message = '';

// Handle fetching recipe data for editing (GET request)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && !empty($_GET['id'])) {
    $recipe_id = $conn->real_escape_string($_GET['id']); // Sanitize input

    if (filter_var($recipe_id, FILTER_VALIDATE_INT)) {
        // Select the recipe data, including ingredients and instructions columns
        // Assuming your columns are named 'ingredients' and 'instructions' directly
        $sql = "SELECT recipeid, name, photo, description, calories, prep_time, category, ingredients, instructions FROM recipes WHERE recipeid = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $recipe_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $recipe = $result->fetch_assoc();

                // Parse comma-separated ingredients and instructions into arrays
                $recipe['ingredients_array'] = !empty($recipe['ingredients']) ? array_map('trim', explode(',', $recipe['ingredients'])) : ['']; // Ensure at least one empty string if none
                $recipe['instructions_array'] = !empty($recipe['instructions']) ? array_map('trim', explode(',', $recipe['instructions'])) : ['']; // Ensure at least one empty string if none

            } else {
                $error_message = "Recipe not found.";
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing fetch statement: " . $conn->error;
        }
    } else {
        $error_message = "Invalid recipe ID format.";
    }
    
    // If recipe not found or error, redirect
    if (!$recipe && empty($error_message)) {
        $_SESSION['error_message'] = "Recipe not found or invalid ID.";
        header("Location: recipe_management.php");
        exit();
    }
}

// Handle form submission for updating recipe (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_id = $conn->real_escape_string($_POST['recipeid'] ?? '');
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $calories = $conn->real_escape_string($_POST['calories'] ?? '');
    $prep_time = $conn->real_escape_string(trim($_POST['prep_time'] ?? ''));
    $category = $conn->real_escape_string(trim($_POST['category'] ?? ''));
    
    // Filter and sanitize array inputs
    $ingredients_raw = $_POST['ingredients'] ?? [];
    $instructions_raw = $_POST['instructions'] ?? [];

    $ingredients_array = array_map(function($item) use ($conn) {
        return $conn->real_escape_string(trim($item));
    }, array_filter($ingredients_raw));
    
    $instructions_array = array_map(function($item) use ($conn) {
        return $conn->real_escape_string(trim($item));
    }, array_filter($instructions_raw));

    if (filter_var($recipe_id, FILTER_VALIDATE_INT) === false) {
        $error_message = "Invalid recipe ID for update.";
    } elseif (empty($name) || empty($description) || filter_var($calories, FILTER_VALIDATE_INT) === false || empty($prep_time) || empty($category) || empty($ingredients_array) || empty($instructions_array)) {
        $error_message = "All required fields must be filled.";
    } else {
        // Convert arrays back to comma-separated strings
        $ingredients_string = implode(',', $ingredients_array);
        $instructions_string = implode(',', $instructions_array);

        $photo_sql_part = '';
        $photo_value = null;

        // Handle file upload if a new photo is provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../uploads/"; // Adjust this path if your uploads folder is elsewhere relative to edit_recipe.php
            // Ensure uploads directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
            }
            $imageFileType = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $new_file_name = uniqid('recipe_') . '.' . $imageFileType;
            $target_file = $target_dir . $new_file_name;

            // Check file type and size
            $check = getimagesize($_FILES['photo']['tmp_name']);
            if ($check === false) {
                $error_message = "File is not an image.";
            } elseif ($_FILES['photo']['size'] > 5000000) { // 5MB max
                $error_message = "Sorry, your file is too large.";
            } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                    $photo_sql_part = ", photo = ?";
                    $photo_value = "uploads/" . $new_file_name; // Store path relative to project root
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            }
        }

        if (empty($error_message)) {
            // Update recipe details
            // IMPORTANT: Verify these column names in your `recipes` table.
            $sql = "UPDATE recipes SET name = ?, description = ?, calories = ?, prep_time = ?, category = ?, ingredients = ?, instructions = ?" . $photo_sql_part . " WHERE recipeid = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $param_types = "ssissssi"; // s:string, i:integer. Order: name, desc, cal, prep, cat, ing, inst, photo (if exists), id
                $params = [&$name, &$description, &$calories, &$prep_time, &$category, &$ingredients_string, &$instructions_string];
                
                if ($photo_value !== null) {
                    $param_types .= "s"; // Add string type for photo
                    $params[] = &$photo_value;
                }
                $params[] = &$recipe_id; // Add recipe_id last

                // Call bind_param dynamically
                call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $params));

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Recipe updated successfully!";
                    header("Location: manage_recipes.php");
                    exit();
                } else {
                    $error_message = "Error updating recipe: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Error preparing update statement: " . $conn->error;
            }
        }
    }
    // Re-fetch recipe data if it was a POST request that failed, to re-populate the form
    if (!empty($error_message) && $recipe_id !== false) {
        $sql = "SELECT recipeid, name, photo, description, calories, prep_time, category, ingredients, instructions FROM recipes WHERE recipeid = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $recipe_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $recipe = $result->fetch_assoc();
                $recipe['ingredients_array'] = !empty($recipe['ingredients']) ? array_map('trim', explode(',', $recipe['ingredients'])) : [''];
                $recipe['instructions_array'] = !empty($recipe['instructions']) ? array_map('trim', explode(',', $recipe['instructions'])) : [''];
            }
            $stmt->close();
        }
    }
}
// Ensure $recipe is set, even if a POST failed on a valid ID
if (!$recipe && isset($recipe_id) && $recipe_id !== false && $_SERVER["REQUEST_METHOD"] == "POST") {
    // If it was a failed POST but a valid ID, try to load the original recipe data for display
    $sql = "SELECT recipeid, name, photo, description, calories, prep_time, category, ingredients, instructions FROM recipes WHERE recipeid = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $recipe = $result->fetch_assoc();
            $recipe['ingredients_array'] = !empty($recipe['ingredients']) ? array_map('trim', explode(',', $recipe['ingredients'])) : [''];
            $recipe['instructions_array'] = !empty($recipe['instructions']) ? array_map('trim', explode(',', $recipe['instructions'])) : [''];
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
     <style>
        /* Include the CSS from your recipe_management.php file here */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px; /* Adjusted max-width for the edit form */
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #fd746c, #ff9068);
            padding: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(15deg);
            border-radius: 50px;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .nav-section {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }

        .edit-form-section {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #fd746c;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px; /* Increased textarea height */
        }

        .dynamic-inputs {
            border: 2px dashed #e9ecef;
            border-radius: 10px;
            padding: 20px;
            background: #f8f9fa;
        }

        .dynamic-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .dynamic-input-group input {
            flex: 1;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
        }

        .add-item-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            transition: background 0.3s ease;
        }

        .add-item-btn:hover {
            background: #218838;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .btn-primary {
            background: linear-gradient(135deg, #fd746c, #ff9068);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(253, 116, 108, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 1rem;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Add responsive adjustments if needed */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            .container {
                padding: 10px;
            }
            .edit-form-section {
                padding: 20px;
            }
            .form-actions {
                flex-direction: column;
                align-items: flex-end;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-edit"></i> Edit Recipe</h1>
                <p>Modify the details of your recipe</p>
            </div>
        </div>

        <!-- Navigation Section -->
        <div class="nav-section">
            <a href="manage_recipes.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Recipe Management
            </a>
        </div>

        <!-- Edit Form Section -->
        <div class="edit-form-section">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>

            <?php if ($recipe): ?>
            <form action="edit_recipe.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="recipeid" value="<?= htmlspecialchars($recipe['recipeid']) ?>">

                <div class="form-group">
                    <label for="recipeName">Recipe Name *</label>
                    <input type="text" id="recipeName" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" required>
                </div>

                 <div class="form-group">
                        <label for="recipePhoto">Recipe Photo (Leave blank to keep current)</label>
                        <input type="file" id="recipePhoto" name="photo" accept="image/*">
                         <?php if (!empty($recipe['photo'])): ?>
                            <p style="margin-top: 10px;">Current photo: <img src="../<?= htmlspecialchars($recipe['photo']) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>" style="max-width: 100px; height: auto; vertical-align: middle; border-radius: 5px;"></p>
                        <?php endif; ?>
                    </div>

                <div class="form-group">
                    <label for="recipeDescription">Description *</label>
                    <textarea id="recipeDescription" name="description" required placeholder="Describe your recipe..."><?= htmlspecialchars($recipe['description']) ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="recipeCalories">Calories *</label>
                        <input type="number" id="recipeCalories" name="calories" value="<?= htmlspecialchars($recipe['calories']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="recipePrepTime">Preparation Time *</label>
                        <input type="text" id="recipePrepTime" name="prep_time" value="<?= htmlspecialchars($recipe['prep_time']) ?>" required placeholder="e.g., 30 minutes">
                    </div>
                </div>

                <div class="form-group">
                    <label for="recipeCategory">Category *</label>
                    <select id="recipeCategory" name="category" required>
                        <option value="">Select Category</option>
                        <option value="breakfast" <?= ($recipe['category'] == 'breakfast') ? 'selected' : '' ?>>Breakfast</option>
                        <option value="lunch" <?= ($recipe['category'] == 'lunch') ? 'selected' : '' ?>>Lunch</option>
                        <option value="dinner" <?= ($recipe['category'] == 'dinner') ? 'selected' : '' ?>>Dinner</option>
                        <option value="snack" <?= ($recipe['category'] == 'snack') ? 'selected' : '' ?>>Snack</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ingredients *</label>
                    <div class="dynamic-inputs" id="ingredientsContainer">
                        <?php if (!empty($recipe['ingredients_array'])): ?>
                            <?php foreach ($recipe['ingredients_array'] as $ingredient): ?>
                                <div class="dynamic-input-group">
                                    <input type="text" name="ingredients[]" value="<?= htmlspecialchars($ingredient) ?>" required placeholder="Enter ingredient...">
                                    <button type="button" class="remove-btn" onclick="removeInput(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <!-- Start with one empty input if no ingredients -->
                             <div class="dynamic-input-group">
                                <input type="text" name="ingredients[]" required placeholder="Enter ingredient...">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addIngredient()">
                        <i class="fas fa-plus"></i> Add Ingredient
                    </button>
                </div>

                <div class="form-group">
                    <label>Instructions *</label>
                    <div class="dynamic-inputs" id="instructionsContainer">
                         <?php if (!empty($recipe['instructions_array'])): ?>
                            <?php foreach ($recipe['instructions_array'] as $instruction): ?>
                                <div class="dynamic-input-group">
                                    <input type="text" name="instructions[]" value="<?= htmlspecialchars($instruction) ?>" required placeholder="Enter instruction step...">
                                    <button type="button" class="remove-btn" onclick="removeInput(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Start with one empty input if no instructions -->
                            <div class="dynamic-input-group">
                                <input type="text" name="instructions[]" required placeholder="Enter instruction step...">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="add-item-btn" onclick="addInstruction()">
                        <i class="fas fa-plus"></i> Add Step
                    </button>
                </div>

                <div class="form-actions">
                    <a href="manage_recipes.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Update Recipe
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Recipe not found or invalid ID.</h3>
                    <p>Please return to the recipe management page.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dynamic input functions (same as in recipe_management.php)
        function addIngredient() {
            const container = document.getElementById('ingredientsContainer');
            const div = document.createElement('div');
            div.className = 'dynamic-input-group';
            div.innerHTML = `
                <input type="text" name="ingredients[]" required placeholder="Enter ingredient...">
                <button type="button" class="remove-btn" onclick="removeInput(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        }

        function addInstruction() {
            const container = document.getElementById('instructionsContainer');
            const div = document.createElement('div');
            div.className = 'dynamic-input-group';
            div.innerHTML = `
                <input type="text" name="instructions[]" required placeholder="Enter instruction step...">
                <button type="button" class="remove-btn" onclick="removeInput(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        }

        function removeInput(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>