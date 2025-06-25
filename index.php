<?php
// index.php - DailyDish Home Page with Splash Screen
// This page typically doesn't need session_start() unless you display user-specific data directly on the landing page
// or need to check login status immediately. For a public home page, it's often optional.
// If you uncomment session_start(), ensure it's at the very top of the file.
// session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>DailyDish - Home</title>
    <!-- Google Fonts - Poppins for headings/logo, Nunito for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Global Styles & Variables */
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

        /* Splash Screen Styles */
        .splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--background-light) 0%, #f8f8f8 50%, var(--background-light) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .splash-screen.fade-out {
            opacity: 0;
            transform: scale(1.1);
            pointer-events: none;
        }

        .splash-logo-text {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(30px) scale(0.9);
            animation: logoFadeIn 1.2s ease-out 0.3s forwards;
        }

        .splash-daily {
            font-family: 'Poppins', sans-serif;
            font-size: 105px;
            font-weight: 400;
            color: var(--dark-text);
            margin: 0;
            line-height: 1;
            animation: slideInLeft 1s ease-out 0.5s both;
        }

        .splash-dish {
            font-family: 'Poppins', sans-serif;
            font-size: 62px;
            font-weight: 600;
            color: #FF8800;
            margin: 0;
            line-height: 1;
            animation: slideInRight 1s ease-out 0.8s both;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .splash-tagline {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            color: var(--light-text);
            margin-top: 2rem;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            animation: taglineFadeIn 1s ease-out 1s forwards;
        }

        .loading-dots {
            display: flex;
            gap: 8px;
            margin-top: 3rem;
            opacity: 0;
            animation: dotsFadeIn 0.8s ease-out 1.5s forwards;
        }

        .loading-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-accent);
            animation: dotBounce 1.4s infinite ease-in-out;
        }

        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        .loading-dot:nth-child(3) { animation-delay: 0s; }

        @keyframes logoFadeIn {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes taglineFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes dotsFadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes dotBounce {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        /* Main Website Content - Hidden initially */
        .main-website {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease-out 0.2s, transform 0.8s ease-out 0.2s;
        }

        .main-website.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Navbar */
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

        /* Hero Section */
        .hero {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh; /* Make it a bit taller */
            text-align: center;
            padding: 4rem 5%; /* More padding */
            background: linear-gradient(135deg, var(--background-light) 0%, #f8f8f8 100%);
            animation: fadeInUp 1s ease-out; /* Existing animation */
        }
        .hero-content {
            max-width: 900px; /* Slightly wider content */
        }
        .hero h1 {
            font-family: 'Poppins', sans-serif; /* Consistent heading font */
            font-size: clamp(2.8rem, 5vw, 4.5rem); /* Larger and more responsive */
            margin-bottom: 1.5rem; /* More space */
            color: var(--dark-text);
            line-height: 1.2;
        }
        .hero p {
            font-size: clamp(1.1rem, 1.8vw, 1.2rem); /* Slightly adjusted */
            margin-bottom: 2.5rem; /* More space */
            color: var(--light-text);
            line-height: 1.6;
        }
        .cta-buttons {
            display: flex;
            gap: 1.5rem; /* Increased gap */
            justify-content: center;
            flex-wrap: wrap;
        }
        .cta-btn {
            padding: 1.1rem 2.5rem; /* More padding */
            font-size: 1.1rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex; /* Use flex for icon & text alignment */
            align-items: center;
            justify-content: center;
            font-weight: 600;
            gap: 0.75rem; /* Space between icon and text */
        }
        .primary-btn {
            background: linear-gradient(45deg, #ff6b6b, var(--primary-accent)); /* Gradient with accent */
            color: white;
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.2);
        }
        .primary-btn:hover {
            transform: translateY(-3px); /* More pronounced lift */
            box-shadow: 0 8px 18px rgba(255, 107, 107, 0.35); /* Stronger shadow */
        }
        .secondary-btn {
            background: var(--background-light);
            color: var(--dark-text);
            border: 1px solid var(--border-color);
            box-shadow: var(--box-shadow-light);
        }
        .secondary-btn:hover {
            background: var(--background-darker);
            border-color: #ccc;
            transform: translateY(-3px); /* More pronounced lift */
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        /* Features Section */
        .features {
            padding: 5rem 5%; /* More padding */
            background: var(--background-darker);
        }
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .features h2 {
            font-family: 'Poppins', sans-serif; /* Consistent heading font */
            text-align: center;
            font-size: 2.8rem; /* Larger heading */
            margin-bottom: 3.5rem; /* More space */
            color: var(--primary-accent); /* Accent color for section heading */
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Slightly smaller min-width for better fit */
            gap: 2.5rem; /* More gap */
        }
        .feature-card {
            background: var(--background-light);
            padding: 2.5rem; /* More padding */
            border-radius: var(--border-radius-large); /* More rounded */
            text-align: center;
            box-shadow: var(--box-shadow-medium); /* Consistent shadow */
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex; /* Flexbox for content alignment */
            flex-direction: column;
            align-items: center;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .feature-card:hover::before {
            left: 100%;
        }
        .feature-card:hover {
            transform: translateY(-8px); /* More pronounced lift */
            box-shadow: var(--box-shadow-heavy); /* Stronger shadow */
        }
        .feature-icon {
            font-size: 3rem; /* Larger icon */
            margin-bottom: 1.2rem;
            color: var(--primary-accent);
        }
        .feature-card h3 {
            font-family: 'Poppins', sans-serif; /* Consistent heading font */
            font-size: 1.5rem; /* Larger heading */
            margin-bottom: 0.8rem;
            color: var(--dark-text);
        }
        .feature-card p {
            color: var(--light-text);
            line-height: 1.7; /* Better readability */
        }

        /* Gallery Section */
        .gallery {
            padding: 5rem 5%; /* More padding */
            background: var(--background-light);
        }
        .gallery h2 {
            font-family: 'Poppins', sans-serif; /* Consistent heading font */
            text-align: center;
            font-size: 2.8rem; /* Larger heading */
            margin-bottom: 3.5rem;
            color: var(--primary-accent); /* Accent color for section heading */
        }
        .gallery-slider {
            display: flex;
            overflow-x: auto;
            gap: 1.5rem;
            scroll-snap-type: x mandatory;
            padding: 1rem 0 2.5rem 0; /* More padding below for scrollbar */
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .gallery-slider::-webkit-scrollbar {
            display: none;
        }
        .gallery-slider img {
            height: 320px; /* Slightly taller images */
            width: 260px; /* Slightly wider images */
            flex-shrink: 0;
            object-fit: cover;
            border-radius: var(--border-radius-large); /* More rounded */
            scroll-snap-align: start;
            box-shadow: var(--box-shadow-medium); /* Consistent shadow */
            transition: transform 0.3s ease;
        }
        .gallery-slider img:hover {
            transform: scale(1.04); /* More pronounced scale */
        }

        /* Contact Section */
        .contact {
            padding: 5rem 5%;
            background: var(--background-darker);
            text-align: center;
        }

        .contact h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.8rem;
            margin-bottom: 3.5rem;
            color: var(--primary-accent);
        }

        .contact-form {
            max-width: 700px;
            margin: 0 auto;
            background: var(--background-light);
            padding: 3rem;
            border-radius: var(--border-radius-large);
            box-shadow: var(--box-shadow-medium);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .contact-form label {
            text-align: left;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Apply consistent styling to all form inputs and selects */
        .contact-form input[type="text"],
        .contact-form input[type="email"],
        .contact-form select, /* ADDED: For consistent design of select */
        .contact-form textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-base);
            font-family: 'Nunito', sans-serif;
            font-size: 1rem;
            color: var(--dark-text);
            background-color: #fcfcfc;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            -webkit-appearance: none; /* ADDED: Removes default browser select styles */
            -moz-appearance: none;    /* ADDED: Removes default browser select styles */
            appearance: none;         /* ADDED: Removes default browser select styles */
        }

        /* Add a custom arrow for the select dropdown */
        .contact-form select {
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23FF8800" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
            background-size: 1.2rem;
            padding-right: 2.5rem; /* Make space for the custom arrow */
        }

        /* Apply consistent focus styling */
        .contact-form input[type="text"]:focus,
        .contact-form input[type="email"]:focus,
        .contact-form select:focus, /* ADDED: For consistent focus design of select */
        .contact-form textarea:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(255, 136, 0, 0.2);
        }

        .contact-form textarea {
            min-height: 150px;
            resize: vertical;
        }

        .contact-form button {
            background: linear-gradient(45deg, #ff6b6b, var(--primary-accent));
            color: white;
            padding: 1.1rem 2.5rem;
            font-size: 1.1rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            margin-top: 1rem;
            align-self: center; /* Center the button */
            width: fit-content;
        }

        .contact-form button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(255, 107, 107, 0.35);
        }


        /* General Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .hero {
                min-height: 60vh;
                padding: 3rem 5%;
            }
            .cta-buttons {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
            .cta-btn {
                width: 80%; /* Make buttons full width on small screens */
                max-width: 300px;
                padding: 1rem 2rem;
            }
            .features,
            .gallery,
            .contact { /* Added contact here */
                padding: 4rem 5%;
            }
            .features h2,
            .gallery h2,
            .contact h2 { /* Added contact here */
                font-size: 2.2rem;
            }
            .hero h1 {
                font-size: 3rem;
            }
            .hero p {
                font-size: 1rem;
            }
            .splash-daily {
                font-size: 70px;
            }
            .splash-dish {
                font-size: 42px;
            }
            .splash-tagline {
                font-size: 1rem;
                padding: 0 2rem;
            }
            .contact-form {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            .splash-logo {
                max-width: 250px;
            }
        }
    </style>
</head>
<body>

    <!-- Splash Screen -->
    <div class="splash-screen" id="splashScreen">
        <img src="image/Daily.png" alt="DailyDish Logo" class="splash-logo">
        <div class="splash-tagline">Your personalized meal planning experience</div>
        <div class="loading-dots">
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
        </div>
    </div>

    <!-- Main Website Content -->
    <div class="main-website" id="mainWebsite">
        <nav class="navbar">
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="recipe.php">Recipes</a>
                <a href="mealplanner.php">Meal Planner</a>
                <a href="php/calories_tracking.php">Calories Tracking</a>
                <a href="about.php">About Us</a>
                <a href="#contact">Contact Us</a> <!-- New link for contact section -->
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

        <section class="hero" id="home">
            <div class="hero-content">
                <h1>Welcome to DailyDish!</h1>
                <p>Your personalized meal planner assistant that helps you discover amazing recipes, plan nutritious meals, and track calories with complete ease and style.</p>
                <div class="cta-buttons">
                    <a href="recipe.php" class="cta-btn primary-btn">
                        <i class="fas fa-utensils"></i> Explore Recipes
                    </a>
                    <a href="mealplanner.php" class="cta-btn secondary-btn">
                        <i class="fas fa-calendar-alt"></i> Plan Meals
                    </a>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="features-container">
                <h2>Why Choose DailyDish?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Recipe Discovery</h3>
                        <p>Discover thousands of delicious recipes from around the world, carefully curated to match your taste preferences and dietary needs.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3>Meal Planning</h3>
                        <p>Plan your weekly meals effortlessly with our intelligent meal planner that considers your schedule, preferences, and nutritional goals.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Calorie Tracking</h3>
                        <p>Keep track of your daily calorie intake and nutritional information to maintain a healthy lifestyle and achieve your fitness goals.</p>
                    </div>
                </div>
            </div>
        </section>

        

        <!-- New Contact Section -->
        <section class="contact" id="contact">
            <h2>Contact Us</h2>
            <form id="contactForm" class="contact-form">
                <p>Have a question, suggestion, or a feature request? Let us know!</p>
                <div>
                    <label for="name">Your Name:</label>
                    <input type="text" id="name" name="user_name" required>
                </div>
                <div>
                    <label for="email">Your Email:</label>
                    <input type="email" id="email" name="user_email" required>
                </div>
                <div>
                    <label for="subject">Subject of your message:</label>
                    <select id="subject" name="message_subject" required>
                        <option value="">-- Please choose a subject --</option>
                        <option value="General Inquiry">General Inquiry</option>
                        <option value="Feature Request">Feature Request</option>
                        <option value="Bug Report">Bug Report</option>
                        <option value="Partnership Opportunities">Partnership Opportunities</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="message">Your Message:</label>
                    <textarea id="message" name="user_message" required></textarea>
                </div>
                <button type="submit">Send Message</button>
                <div id="formMessage" style="margin-top: 15px; font-weight: 600;"></div>
            </form>
        </section>
        <!-- End New Contact Section -->

        <footer style="text-align: center; padding: 2rem; background-color: var(--background-light); border-top: 1px solid var(--border-color); color: var(--light-text); font-size: 0.9rem;">
            &copy; <?= date('Y') ?> DailyDish. All rights reserved.
        </footer>

    </div>

    <script>
        // Add this new section in your <script> tag within index.php
        document.getElementById('contactForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);
            const formMessage = document.getElementById('formMessage');
            const submitButton = form.querySelector('button[type="submit"]');

            formMessage.textContent = 'Sending...';
            formMessage.style.color = '#FF8800'; // Orange for sending
            submitButton.disabled = true; // Disable button during submission

            try {
                const response = await fetch('php/process_contact.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    formMessage.textContent = 'Message sent successfully! Thank you for your feedback.';
                    formMessage.style.color = 'green';
                    form.reset(); // Clear the form
                } else {
                    formMessage.textContent = 'Error: ' + (result.message || 'Something went wrong.');
                    formMessage.style.color = 'red';
                }
            } catch (error) {
                console.error('Error:', error);
                formMessage.textContent = 'Network error. Please try again.';
                formMessage.style.color = 'red';
            } finally {
                submitButton.disabled = false; // Re-enable button
                setTimeout(() => {
                    formMessage.textContent = ''; // Clear message after some time
                }, 5000);
            }
        });

        // Splash Screen Logic
        document.addEventListener('DOMContentLoaded', function() {
            const splashScreen = document.getElementById('splashScreen');
            const mainWebsite = document.getElementById('mainWebsite');

            // Hide the splash screen after 3.5 seconds
            setTimeout(function() {
                splashScreen.classList.add('fade-out');

                // Show main website content after splash fades out
                setTimeout(function() {
                    splashScreen.style.display = 'none';
                    mainWebsite.classList.add('show');
                }, 800); // Wait for fade-out animation to complete
            }, 3500); // Show splash for 3.5 seconds
        });

        // Toggle dropdown on icon click
        document.getElementById('profileIcon').addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent the click from propagating to the document body
            var menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('show');
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