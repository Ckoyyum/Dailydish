<?php
// php/manage_feedback.php

// Database connection details (REPLACE WITH YOUR ACTUAL CREDENTIALS)
$servername = "db";
$username = "php_docker";              // Usually 'root' for XAMPP/WAMP
$password = "password";                  // Usually empty for XAMPP/WAMP
$dbname = "php_docker";          // Your database name from the SQL dump

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle actions (mark as read/unread, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['mark_read'])) {
        $id = intval($_POST['mark_read']);
        $sql = "UPDATE feedback SET is_read = TRUE WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['mark_unread'])) {
        $id = intval($_POST['mark_unread']);
        $sql = "UPDATE feedback SET is_read = FALSE WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        $sql = "DELETE FROM feedback WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    // Redirect to prevent re-submission on refresh
    header("Location: manage_feedback.php");
    exit();
}

// Fetch all feedback messages
$sql = "SELECT id, name, email, subject, message, created_at, is_read FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);

$feedback_messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedback_messages[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - DailyDish Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-accent: #FF8800;
            --background-light: #FDFDFD;
            --background-darker: #F7F7F7;
            --dark-text: #333333;
            --light-text: #666666;
            --border-color: #E0E0E0;
            --border-radius-base: 8px;
            --box-shadow-light: 0 4px 10px rgba(0,0,0,0.05);
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--background-darker);
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            background-color: var(--background-light);
            border-radius: 12px;
            box-shadow: var(--box-shadow-medium);
            padding: 30px;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            color: var(--primary-accent);
            margin-bottom: 30px;
            font-size: 2.5rem;
        }

        .feedback-list {
            display: grid;
            gap: 20px;
        }

        .feedback-item {
            background-color: #fff;
            padding: 25px;
            border-radius: var(--border-radius-base);
            box-shadow: var(--box-shadow-light);
            border-left: 5px solid var(--primary-accent);
            position: relative;
            transition: transform 0.2s ease;
        }

        .feedback-item:hover {
            transform: translateY(-3px);
        }

        .feedback-item.read {
            background-color: #f0f0f0;
            border-left-color: var(--border-color);
            opacity: 0.8;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .feedback-subject {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
            flex-basis: 100%; /* Take full width on smaller screens */
        }

        .feedback-meta {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-top: 5px; /* For flex-wrap spacing */
        }

        .feedback-meta span {
            margin-right: 15px;
        }

        .feedback-message {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed var(--border-color);
            color: var(--dark-text);
        }

        .feedback-actions {
            margin-top: 20px;
            text-align: right;
        }

        .feedback-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: var(--border-radius-base);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 10px;
            transition: background-color 0.2s ease;
        }

        .btn-mark {
            background-color: #4CAF50; /* Green for mark read */
            color: white;
        }

        .btn-mark.unread {
            background-color: #FF8800; /* Orange for mark unread */
        }

        .btn-delete {
            background-color: #f44336; /* Red for delete */
            color: white;
        }

        .btn-mark:hover {
            opacity: 0.9;
        }
        .btn-delete:hover {
            opacity: 0.9;
        }

        .no-feedback {
            text-align: center;
            padding: 50px;
            color: var(--light-text);
            font-size: 1.2rem;
            background-color: #fff;
            border-radius: var(--border-radius-base);
            box-shadow: var(--box-shadow-light);
        }

        .back-to-dashboard {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #667eea; /* A nice blue/purple */
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.2);
        }

        .back-to-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Feedback</h1>

        <?php if (empty($feedback_messages)): ?>
            <div class="no-feedback">
                <p>No feedback messages received yet.</p>
                <i class="far fa-comments" style="font-size: 3rem; margin-top: 20px; color: #ccc;"></i>
            </div>
        <?php else: ?>
            <div class="feedback-list">
                <?php foreach ($feedback_messages as $feedback): ?>
                    <div class="feedback-item <?= $feedback['is_read'] ? 'read' : '' ?>">
                        <div class="feedback-header">
                            <h3 class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></h3>
                            <div class="feedback-meta">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($feedback['name']) ?></span>
                                <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($feedback['email']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= date('F j, Y, g:i a', strtotime($feedback['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="feedback-message">
                            <p><?= nl2br(htmlspecialchars($feedback['message'])) ?></p>
                        </div>
                        <div class="feedback-actions">
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="<?= $feedback['is_read'] ? 'mark_unread' : 'mark_read' ?>" value="<?= $feedback['id'] ?>">
                                <button type="submit" class="btn-mark <?= $feedback['is_read'] ? 'unread' : '' ?>">
                                    <i class="fas fa-eye<?= $feedback['is_read'] ? '-slash' : '' ?>"></i>
                                    <?= $feedback['is_read'] ? 'Mark Unread' : 'Mark Read' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                <input type="hidden" name="delete" value="<?= $feedback['id'] ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="../admin_dashboard.php" class="back-to-dashboard">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>