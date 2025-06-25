<?php
// Start session at the very beginning
session_start();

include 'db.php';

// For debugging
$debug = false;
$debug_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // First check if it's an admin account
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Admin login successful
        $_SESSION['admin_id'] = $admin['adminid']; // Changed from 'id' to 'adminid'
        $_SESSION['email'] = $admin['email'];
        $_SESSION['is_admin'] = true;
        
        if ($debug) {
            $debug_message = "Admin login successful. Redirecting to dashboard...";
        } else {
            // Make sure there's no output before this header
            header("Location: ../admin_dashboard.php");
            exit();
        }
    } else {
        // Not an admin, check if regular user
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // User login successful
            $_SESSION['user_id'] = $user['userid']; // Changed from 'id' to 'userid'
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = false;
            
            // Check if the user has completed their preferences
            $stmt = $conn->prepare("SELECT has_completed_preferences FROM user WHERE userid = ?"); // Changed from 'id' to 'userid'
            $stmt->bind_param("i", $user['userid']); // Changed from 'id' to 'userid'
            $stmt->execute();
            $result = $stmt->get_result();
            $user_prefs = $result->fetch_assoc();

            $redirect_url = "../index.php";

            // If preferences flag doesn't exist or is set to 0, redirect to preferences page
            if (!isset($user_prefs['has_completed_preferences']) || $user_prefs['has_completed_preferences'] == 0) {
                $redirect_url = "userpref.html";
            }
            
            if ($debug) {
                $debug_message = "User login successful. Redirecting to " . $redirect_url;
            } else {
                header("Location: " . $redirect_url);
                exit();
            }
        } else {
            // Invalid credentials
            $error_message = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyDish - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .login-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
        }

        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }

        .logo span {
            color: #ff8800;
        }
        
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .error-message {
            background-color: #ffe0e0;
            color: #d32f2f;
        }
        
        .debug-message {
            background-color: #e0f7fa;
            color: #0288d1;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .input-field {
            position: relative;
        }

        .input-field i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #0077b6;
            box-shadow: 0 0 0 2px rgba(0, 119, 182, 0.2);
        }

        .btn-login {
            width: 100%;
            background-color: #ff8800;
            color: white;
            border: none;
            padding: 0.9rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-login:hover {
            background-color: #e67a00;
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #0077b6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #005b8a;
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: #ddd;
        }

        .divider span {
            padding: 0 1rem;
            color: #888;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">Daily<span>Dish</span></div>
        
        <?php if (isset($error_message)): ?>
            <div class="message error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($debug && isset($debug_message)): ?>
            <div class="message debug-message">
                <?php echo $debug_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-field">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <p class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
    </div>
</body>
</html>