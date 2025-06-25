<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DailyDish</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts - Poppins for headings/logo, Nunito for body -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* General Reset & Body */
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
            display: flex; /* Use flexbox to center content */
            justify-content: center;
            align-items: center;
        }

        /* Dashboard Container */
        .dashboard-container {
            max-width: 1200px;
            width: 100%; /* Ensure it takes full width up to max */
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column; /* Stack header and main content */
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            padding: 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden; /* For pseudo-element grain effect */
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .header h1 {
            font-family: 'Poppins', sans-serif; /* Apply Poppins font */
            font-size: 2.8rem; /* Slightly larger */
            font-weight: 800; /* Bolder */
            margin-bottom: 12px; /* More space */
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle text shadow */
        }

        .header p {
            font-family: 'Nunito', sans-serif; /* Apply Nunito font */
            font-size: 1.2rem; /* Slightly larger */
            opacity: 0.95; /* Less transparent */
            position: relative;
            z-index: 1;
        }

        /* Main Content Area */
        .main-content {
            padding: 50px 40px;
        }

        /* Stats Grid (REMOVED - but keeping the empty class for now, good to clean up if not needed) */
        .stats-grid {
            /* This section is now empty as the stat cards are removed */
            margin-bottom: 0; /* Remove bottom margin if no content */
        }

        /* Admin Actions Grid */
        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); /* Min width slightly larger */
            gap: 30px; /* Increased gap */
            margin-top: 0; /* Adjust as there's no stats grid above */
        }

        .action-card {
            background: white;
            border-radius: 18px; /* More rounded corners */
            padding: 35px; /* Increased padding */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); /* Stronger shadow */
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            display: flex; /* Use flex for layout */
            flex-direction: column;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px; /* Thicker accent line */
            background: linear-gradient(90deg, #ff6b6b, #ff8e53);
            transform: scaleX(0);
            transform-origin: left; /* Animation starts from left */
            transition: transform 0.3s ease;
        }

        .action-card:hover::before {
            transform: scaleX(1);
        }

        .action-card:hover {
            transform: translateY(-12px); /* More pronounced lift */
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.18); /* Stronger hover shadow */
        }

        .action-icon {
            font-size: 3.5rem; /* Larger icon */
            margin-bottom: 25px; /* More space below icon */
            display: block;
            color: #764ba2; /* Color the icon */
            text-align: left; /* Align icon to left */
        }

        .action-title {
            font-family: 'Poppins', sans-serif; /* Poppins for title */
            font-size: 1.5rem; /* Larger title */
            font-weight: 700; /* Bolder */
            margin-bottom: 12px;
            color: #333;
        }

        .action-description {
            font-family: 'Nunito', sans-serif; /* Nunito for description */
            color: #666;
            margin-bottom: 25px; /* More space below description */
            line-height: 1.7; /* Increased line height */
            flex-grow: 1; /* Allows description to take available space */
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 10px; /* Increased gap */
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 14px 28px; /* Larger padding */
            border-radius: 10px; /* More rounded */
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05rem; /* Slightly larger font */
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            align-self: flex-start; /* Align button to start of flex container */
        }

        .action-button:hover {
            transform: translateY(-3px); /* More pronounced lift */
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.45); /* Stronger shadow */
            background: linear-gradient(135deg, #764ba2, #667eea); /* Slight gradient shift on hover */
        }

        /* Logout Section */
        .logout-section {
            margin-top: 60px; /* More space above logout */
            text-align: center;
            padding-top: 40px; /* More padding */
            border-top: 1px solid #eee;
        }

        .logout-button {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 18px 35px; /* Larger padding */
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700; /* Bolder */
            font-size: 1.1rem; /* Larger font */
            display: inline-flex;
            align-items: center;
            gap: 12px; /* Increased gap */
            transition: all 0.3s ease;
        }

        .logout-button:hover {
            transform: translateY(-3px); /* More pronounced lift */
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.5); /* Stronger shadow */
            background: linear-gradient(135deg, #ee5a52, #ff6b6b); /* Slight gradient shift on hover */
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            /* Stats grid rules can be removed or kept, they won't apply to non-existent elements */
            .admin-actions {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px; /* Less padding on body */
            }
            .header {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 2.2rem;
            }
            .header p {
                font-size: 1rem;
            }
            .main-content {
                padding: 30px 20px;
            }
            /* Stats grid rules can be removed or kept */
            .admin-actions {
                grid-template-columns: 1fr; /* Stack columns on small screens */
                gap: 20px;
            }
            .action-card {
                padding: 30px;
            }
            .action-icon {
                font-size: 3rem;
            }
            .action-button {
                width: 100%; /* Full width buttons */
                justify-content: center; /* Center content in button */
            }
            .logout-button {
                width: 80%; /* Wider logout button */
                padding: 15px 20px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8rem;
            }
            .header p {
                font-size: 0.9rem;
            }
            .action-card {
                padding: 25px;
            }
            .action-title {
                font-size: 1.3rem;
            }
            .action-icon {
                font-size: 2.5rem;
            }
            .action-description {
                font-size: 0.9rem;
            }
            .action-button {
                font-size: 0.95rem;
                padding: 12px 20px;
            }
            .logout-button {
                font-size: 1rem;
                padding: 12px 25px;
            }
        }


        /* Animations */
        .welcome-animation {
            animation: slideInFromTop 0.8s ease-out;
        }

        .card-animation {
            animation: slideInFromBottom 0.8s ease-out;
            animation-fill-mode: both;
        }

        /* Removed specific delays for stat cards, adjusted for action cards */
        .admin-actions .card-animation:nth-child(1) { animation-delay: 0.1s; } /* Starts earlier */
        .admin-actions .card-animation:nth-child(2) { animation-delay: 0.2s; }
        .admin-actions .card-animation:nth-child(3) { animation-delay: 0.3s; }


        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInFromBottom {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Notification Icon Styles */
        .notification-bell {
            position: absolute;
            top: 25px;
            right: 30px;
            font-size: 1.8rem;
            color: white;
            cursor: pointer;
            z-index: 2;
            transition: transform 0.2s ease-in-out;
        }

        .notification-bell:hover {
            transform: scale(1.1);
        }

        .notification-bell .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #f00;
            color: white;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 0.75rem;
            font-weight: bold;
            line-height: 1;
            transform: translate(50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .notification-bell .badge.show {
            opacity: 1;
            transform: scale(1) translate(50%, -50%);
        }
        .notification-bell .badge:not(.show) {
            transform: scale(0.5) translate(50%, -50%);
        }


        /* Responsive Adjustments for notification bell */
        @media (max-width: 768px) {
            .notification-bell {
                top: 20px;
                right: 20px;
                font-size: 1.5rem;
            }
            .notification-bell .badge {
                min-width: 18px;
                height: 18px;
                font-size: 0.65rem;
                top: -5px;
                right: -5px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header welcome-animation">
            <h1>👋 Welcome Admin</h1>
            <p>Manage your DailyDish platform with ease</p>

            <!-- Notification Bell Icon -->
            <div class="notification-bell" id="notificationBell">
                <i class="fas fa-bell"></i>
                <span class="badge" id="notificationBadge">0</span>
            </div>
            <!-- End Notification Bell Icon -->
        </div>

        <div class="main-content">
            <!-- The entire stats-grid div is removed -->
            <!-- <div class="stats-grid">
                ... stat cards were here ...
            </div> -->

            <div class="admin-actions">
                <div class="action-card card-animation">
                    <span class="action-icon">👤</span>
                    <h3 class="action-title">Manage Users</h3>
                    <p class="action-description">View, edit, and manage user accounts. Monitor user activity and handle account-related tasks.</p>
                    <a href="php/manage_users.php" class="action-button">
                        <span>Manage Users</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="action-card card-animation">
                    <span class="action-icon">📖</span>
                    <h3 class="action-title">Manage Recipes</h3>
                    <p class="action-description">Review, approve, and manage recipe content. Ensure quality standards and moderate submissions.</p>
                    <a href="php/manage_recipes.php" class="action-button">
                        <span>Manage Recipes</span>
                        <span>→</span>
                    </a>
                </div>

                <!-- Manage Feedback Action Card -->
                <div class="action-card card-animation">
                    <span class="action-icon">💬</span>
                    <h3 class="action-title">Manage Feedback</h3>
                    <p class="action-description">Review and respond to user messages and suggestions. Keep track of all incoming feedback.</p>
                    <a href="php/manage_feedback.php" class="action-button">
                        <span>View Feedback</span>
                        <span>→</span>
                    </a>
                </div>
                <!-- END NEW -->
            </div>

            <div class="logout-section">
                <a href="php/login.php" class="logout-button">
                    <span>🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Removed animateCount and animateValue functions as they are no longer needed for stats
        // Removed loadDashboardStats function as it's no longer needed

        // Function to load notification count (STILL NEEDED FOR BELL)
        async function loadNotificationCount() {
            try {
                // The path remains 'php/get_notifications.php'
                const response = await fetch('php/get_notifications.php');
                const data = await response.json();

                const badge = document.getElementById('notificationBadge');
                if (data.success) {
                    badge.textContent = data.unread_count;
                    if (data.unread_count > 0) {
                        badge.classList.add('show'); // Show the badge
                    } else {
                        badge.classList.remove('show'); // Hide the badge
                    }
                } else {
                    console.error('Failed to load notifications:', data.error);
                    badge.textContent = '0';
                    badge.classList.remove('show');
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
                const badge = document.getElementById('notificationBadge');
                badge.textContent = '0';
                badge.classList.remove('show');
            }
        }


        // Load ONLY notifications when page loads
        document.addEventListener('DOMContentLoaded', () => {
            // loadDashboardStats(); // REMOVED THIS LINE
            loadNotificationCount();
            // Refresh notification count every 30 seconds
            setInterval(loadNotificationCount, 30000);
        });

        // Redirect to feedback management page when bell is clicked
        document.getElementById('notificationBell').addEventListener('click', function() {
            window.location.href = 'php/manage_feedback.php';
        });
    </script>
</body>
</html>