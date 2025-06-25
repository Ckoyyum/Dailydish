<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

include 'db.php'; // Use your existing mysqli connection from db.php

// Fetch recipes using mysqli
$sql = "SELECT recipeid, name, photo, description, calories, prep_time, category FROM recipes";
$result = $conn->query($sql);

$recipes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
} else if (!$result) {
    // Handle query error
    error_log("Error fetching recipes: " . $conn->error);
    echo "<div class='alert alert-danger'>Error loading recipes. Please try again later.</div>";
}
// Note: $conn->close() should ideally be done at the very end of the main script,
// but for simplicity in included files, it's often omitted.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Management - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Your existing CSS here */
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
            max-width: 1400px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-icon.recipes { color: #fd746c; }
        .stat-icon.new { color: #28a745; }
        .stat-icon.popular { color: #ffc107; }
        .stat-icon.categories { color: #17a2b8; }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .table-section {
            padding: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-filter-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #fd746c;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }

        .filter-select {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #fd746c;
        }

        .add-recipe-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .add-recipe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .recipes-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .recipes-table thead {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .recipes-table th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .recipes-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .recipes-table tbody tr {
            transition: background-color 0.3s ease;
        }

        .recipes-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .recipe-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .recipe-image {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fd746c, #ff9068); /* Placeholder background */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            flex-shrink: 0;
            overflow: hidden; /* Ensure image fits */
        }
        .recipe-image img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Cover the area */
        }


        .recipe-details h4 {
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 1rem;
        }

        .recipe-details p {
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        .category-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .category-breakfast { background: #fff3cd; color: #856404; }
        .category-lunch { background: #d4edda; color: #155724; }
        .category-dinner { background: #d1ecf1; color: #0c5460; }
        .category-snack { background: #f8d7da; color: #721c24; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: #17a2b8;
            color: white;
        }

        .btn-edit:hover {
            background: #138496;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
            animation: slideInFromTop 0.3s ease-out;
        }

        .modal-header {
            background: linear-gradient(135deg, #fd746c, #ff9068);
            color: white;
            padding: 25px 30px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
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
            min-height: 100px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
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


        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-filter-container {
                flex-direction: column;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .recipes-table {
                font-size: 0.9rem;
            }
            
            .recipes-table th,
            .recipes-table td {
                padding: 15px 10px;
            }
            
            .recipe-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .modal-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-utensils"></i> Recipe Management</h1>
                <p>Create, edit, and organize your culinary masterpieces</p>
            </div>
        </div>

        <!-- Navigation Section -->
        <div class="nav-section">
            <a href="../admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon recipes">
                    <i class="fas fa-utensils"></i>
                </div>
                <!-- Corrected to use count($recipes) for consistency with mysqli fetch -->
                <div class="stat-number"><?= count($recipes) ?></div>
                <div class="stat-label">Total Recipes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon new">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="stat-number">8</div>
                <div class="stat-label">New This Week</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon categories">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-number">4</div>
                <div class="stat-label">Categories</div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <div class="search-filter-container">
                    <div class="search-box">
                        <input type="text" placeholder="Search recipes..." id="searchInput">
                        <i class="fas fa-search"></i>
                    </div>
                    <select class="filter-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                        <option value="snack">Snack</option>
                    </select>
                </div>
                <button class="add-recipe-btn" onclick="openModal()">
                    <i class="fas fa-plus"></i>
                    Add New Recipe
                </button>
            </div>

            <?php
            // Check for success/error messages from other pages (e.g., after edit/delete)
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>


            <?php if (count($recipes) > 0): ?>
            <table class="recipes-table" id="recipesTable">
                <thead>
                    <tr>
                        <th>Recipe</th>
                        <th>Category</th>
                        <th>Prep Time</th>
                        <th>Calories</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipes as $row): ?>
                    <tr data-category="<?= strtolower($row['category'] ?? 'other') ?>">
                        <td>
                            <div class="recipe-info">
                                <div class="recipe-image">
                                    <?php if (!empty($row['photo']) && file_exists('../' . $row['photo'])): ?>
                                        <img src="../<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                    <?php else: ?>
                                        <!-- Fallback to first letter if no photo or photo not found -->
                                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="recipe-details">
                                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                                    <p>Recipe ID: #<?= $row['recipeid'] ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="category-badge category-<?= strtolower($row['category'] ?? 'other') ?>">
                                <?= ucfirst($row['category'] ?? 'Other') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['prep_time']) ?></td>
                        <td><?= $row['calories'] ?> cal</td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_recipe.php?id=<?= $row['recipeid'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a href="delete_recipe.php?id=<?= $row['recipeid'] ?>" 
                                   class="btn btn-delete"
                                   data-recipe-name="<?= htmlspecialchars($row['name']) ?>"> <!-- Added data attribute -->
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-utensils"></i>
                <h3>No Recipes Found</h3>
                <p>Start by adding your first delicious recipe to the collection.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Recipe Modal -->
    <div id="addRecipeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add New Recipe</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form action="add_recipe.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="recipeName">Recipe Name *</label>
                        <input type="text" id="recipeName" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="recipePhoto">Recipe Photo</label>
                        <input type="file" id="recipePhoto" name="photo" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="recipeDescription">Description *</label>
                        <textarea id="recipeDescription" name="description" required placeholder="Describe your recipe..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="recipeCalories">Calories *</label>
                            <input type="number" id="recipeCalories" name="calories" required>
                        </div>

                        <div class="form-group">
                            <label for="recipePrepTime">Preparation Time *</label>
                            <input type="text" id="recipePrepTime" name="prep_time" required placeholder="e.g., 30 minutes">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="recipeCategory">Category *</label>
                        <select id="recipeCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                            <option value="snack">Snack</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ingredients *</label>
                        <div class="dynamic-inputs" id="ingredientsContainer">
                            <div class="dynamic-input-group">
                                <input type="text" name="ingredients[]" required placeholder="Enter ingredient...">
                            </div>
                        </div>
                        <button type="button" class="add-item-btn" onclick="addIngredient()">
                            <i class="fas fa-plus"></i> Add Ingredient
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Instructions *</label>
                        <div class="dynamic-inputs" id="instructionsContainer">
                            <div class="dynamic-input-group">
                                <input type="text" name="instructions[]" required placeholder="Enter instruction step...">
                            </div>
                        </div>
                        <button type="button" class="add-item-btn" onclick="addInstruction()">
                            <i class="fas fa-plus"></i> Add Step
                        </button>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Add Recipe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('addRecipeModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addRecipeModal').style.display = 'none';
            // Reset form
            document.querySelector('#addRecipeModal form').reset();
            resetDynamicInputs();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addRecipeModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Dynamic input functions
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

        function resetDynamicInputs() {
            // Reset ingredients
            const ingredientsContainer = document.getElementById('ingredientsContainer');
            ingredientsContainer.innerHTML = `
                <div class="dynamic-input-group">
                    <input type="text" name="ingredients[]" required placeholder="Enter ingredient...">
                </div>
            `;

            // Reset instructions
            const instructionsContainer = document.getElementById('instructionsContainer');
            instructionsContainer.innerHTML = `
                <div class="dynamic-input-group">
                    <input type="text" name="instructions[]" required placeholder="Enter instruction step...">
                </div>
            `;
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            filterTable();
        });

        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', function() {
            filterTable();
        });

        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const categoryValue = document.getElementById('categoryFilter').value.toLowerCase();
            const tableRows = document.querySelectorAll('#recipesTable tbody tr');
            
            tableRows.forEach(row => {
                const recipeName = row.querySelector('.recipe-details h4').textContent.toLowerCase();
                const recipeCategory = row.dataset.category;
                
                const matchesSearch = recipeName.includes(searchValue);
                const matchesCategory = categoryValue === '' || recipeCategory === categoryValue;
                
                if (matchesSearch && matchesCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Enhanced delete confirmation using event delegation
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                e.preventDefault();
                const deleteButton = e.target.closest('.btn-delete');
                const recipeName = deleteButton.dataset.recipeName; // Get from data attribute
                
                if (confirm(`Are you sure you want to delete "${recipeName}"?\n\nThis action cannot be undone and will permanently remove the recipe and all its data.`)) {
                    window.location.href = deleteButton.href;
                }
            }
        });
    </script>
</body>
</html>