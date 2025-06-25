<?php

// about.php - DailyDish About Us Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - DailyDish</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts - Poppins for headings/logo, Nunito for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Global Styles & Variables (Copied from index.php) */
        :root {
            --primary-accent: #FF8800; /* Your main orange */
            --secondary-accent: #FFB22C; /* Lighter orange */
            --dark-text: #333333;
            --light-text: #666666;
            --background-light: #FDFDFD; /* Lighter background for elements */
            --background-darker: #F7F7F7; /* Main page background */
            --border-color: #E0E0E0;

            --border-radius-base: 8px;
            --border-radius-large: 12px;
            --box-shadow-light: 0 4px 10px rgba(0,0,0,0.05);
            --box-shadow-medium: 0 8px 20px rgba(0,0,0,0.08);
            --box-shadow-heavy: 0 10px 25px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Nunito', sans-serif; /* Default body font */
            background-color: var(--background-darker);
            min-height: 100vh;
            scroll-behavior: smooth;
            overflow-x: hidden;
            color: var(--dark-text); /* Default text color */
        }

        /* Navbar (Copied from index.php) */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%; /* Consistent padding */
            background-color: var(--background-light);
            box-shadow: var(--box-shadow-light); /* Softer shadow */
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid var(--border-color); /* Subtle border */
        }
        .nav-links {
            display: flex;
            gap: 2.5rem; /* Increased gap */
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
            font-family: 'Poppins', sans-serif; /* Consistent logo font */
            font-size: 1.8rem; /* Larger logo */
            font-weight: 700;
            color: var(--dark-text);
        }
        .logo span {
            color: var(--primary-accent);
        }
        .nav-icon {
            position: relative;
            cursor: pointer;
            padding: 8px; /* Make clickable area larger */
            border-radius: var(--border-radius-base);
            transition: background-color 0.2s;
        }
        .nav-icon:hover {
            background-color: var(--border-color);
        }
        .nav-icon i {
            font-size: 22px; /* Consistent icon size */
            color: var(--primary-accent); /* Use your theme color from :root */
        }
        .dropdown-menu {
            display: none; /* Hidden by default */
            position: absolute;
            top: 45px; /* Adjust based on navbar height */
            right: 0;
            background-color: var(--background-light);
            min-width: 150px; /* Consistent width */
            box-shadow: 0 5px 15px rgba(0,0,0,0.15); /* Stronger shadow */
            border-radius: var(--border-radius-base);
            z-index: 1000;
            overflow: hidden; /* Ensures border-radius */
            opacity: 0; /* For smooth fade-in */
            transform: translateY(10px); /* For smooth slide-down */
            transition: opacity 0.3s ease-out, transform 0.3s ease-out; /* Transition properties */
        }
        .dropdown-menu.show {
            display: block; /* Show when 'show' class is present */
            opacity: 1; /* Fade in */
            transform: translateY(0); /* Slide up */
        }
        .dropdown-menu a,
        .dropdown-menu button {
            padding: 0.8rem 1.2rem; /* More padding */
            text-align: left;
            text-decoration: none;
            display: block;
            width: 100%;
            background: none;
            border: none;
            font-size: 1rem; /* Consistent font size */
            color: var(--dark-text);
            cursor: pointer;
            transition: background 0.3s ease-in-out;
            font-weight: 400; /* Lighter font weight */
        }
        .dropdown-menu a:hover,
        .dropdown-menu button:hover {
            background-color: var(--background-darker); /* Lighter hover */
            color: var(--primary-accent);
        }

        /* About Page Specific Styles */
        .about-container {
            max-width: 900px;
            margin: 40px auto;
            background: var(--background-light);
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            padding: 2.5rem 2rem;
        }
        .about-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.8rem; /* Larger heading */
            font-weight: bold;
            color: var(--dark-text);
            margin-bottom: 1rem;
            text-align: center;
        }
        .about-subtitle {
            font-family: 'Nunito', sans-serif;
            font-size: 1.3rem; /* Slightly larger */
            color: var(--primary-accent);
            text-align: center;
            margin-bottom: 2rem;
        }
        .about-content {
            font-family: 'Nunito', sans-serif;
            font-size: 1.1rem;
            color: var(--light-text);
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .about-content ul {
            list-style: disc; /* Use discs for bullet points */
            padding-left: 25px; /* Indent list */
            margin-top: 15px;
        }
        .about-content ul li {
            margin-bottom: 8px; /* Spacing between list items */
        }
        .about-team { /* Renamed to .about-creator for single person */
            margin-top: 2.5rem;
            text-align: center; /* Center the entire section */
        }
        .creator-title { /* New class for the developer's title */
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem; /* Larger heading for the developer section */
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 1.5rem;
        }
        .creator-card { /* New class for the single developer card */
            background: var(--background-darker);
            border-radius: var(--border-radius-large);
            padding: 1.5rem 2rem;
            box-shadow: var(--box-shadow-light);
            display: inline-block; /* Allows auto-centering with text-align: center on parent */
            max-width: 250px; /* Constrain width for a card-like appearance */
            margin-top: 1rem; /* Space below title */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .creator-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-medium);
        }
        .creator-card i {
            font-size: 3rem; /* Larger icon */
            color: var(--primary-accent);
            margin-bottom: 1rem;
        }
        .creator-card .name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--dark-text);
            margin-bottom: 0.2rem;
        }
        .creator-card .role {
            font-family: 'Nunito', sans-serif;
            font-size: 1rem;
            color: var(--light-text);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .navbar .nav-links {
                display: none; /* Hide nav links on small screens */
            }
            .about-container {
                margin: 20px auto;
                padding: 1.5rem 1rem;
            }
            .about-title { font-size: 2.2rem; }
            .about-subtitle { font-size: 1.1rem; }
            .about-content { font-size: 1rem; }
            .creator-card {
                width: 90%; /* Allow card to take more width */
                max-width: 300px; /* Restrict max width */
                padding: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .about-title { font-size: 2rem; }
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
                <a href="profile.php">Profile</a>
                <button onclick="window.location.href='php/login.php'">Logout</button>
            </div>
        </div>
    </nav>
    <div class="about-container">
        <div class="about-title">About DailyDish</div>
        <div class="about-subtitle">Your Personalized Meal Planner & Recipe Companion</div>
        <div class="about-content">
            DailyDish was created to make healthy eating and meal planning simple, fun, and accessible for everyone. Whether you're a busy professional, a student, or a family chef, our platform helps you discover new recipes, plan your meals, and track your nutrition with ease.<br><br>
            <b>Our Mission:</b> To empower people to eat better, save time, and enjoy cooking by providing smart tools and a vibrant recipe community.<br><br>
            <b>What We Offer:</b>
            <ul>
                <li>Personalized meal planning based on your goals and preferences</li>
                <li>Easy calorie and nutrition tracking</li>
                <li>Thousands of curated recipes for every taste and diet</li>
                <li>Modern, user-friendly interface</li>
            </ul>
        </div>
        <div class="about-creator"> <!-- Changed class name -->
            <div class="creator-title">The Creator</div> <!-- Changed title -->
            <div class="creator-card"> <!-- New class for single card -->
                <i class="fas fa-terminal"></i> <!-- Icon related to development -->
                <div class="name">Amirul Qayyum </div> <!-- Your Name -->
                <div class="role">Final Year Project Student / Developer</div> <!-- Your Role -->
            </div>
        </div>
    </div>
    <script>
        // Toggle dropdown on icon click
        document.getElementById('profileIcon').addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent the click from propagating to the document body
            var menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('show'); // Use classList.toggle
        });

        // Hide dropdown when clicking anywhere else on the document
        document.addEventListener('click', function (e) {
            var menu = document.getElementById('dropdownMenu');
            // If the menu is shown AND the click is NOT inside the profile icon AND the click is NOT inside the dropdown menu itself
            if (menu.classList.contains('show') && !document.getElementById('profileIcon').contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        // Prevent dropdown from closing when clicking inside it (already handled by stopping propagation on the dropdown itself)
        document.getElementById('dropdownMenu').addEventListener('click', function (e) {
            e.stopPropagation();
        });
    </script>
</body>
</html> 