<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_username'])) {
        $new_username = trim($_POST['new_username']);
        if (!empty($new_username)) {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $new_username, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $username_message = "Username updated successfully!";
            } else {
                $username_error = "Error updating username.";
            }
        }
    }

    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $password_message = "Password updated successfully!";
                }
            } else {
                $password_error = "Current password is incorrect.";
            }
        } else {
            $password_error = "New passwords do not match.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Financial Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="account_design.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Settings</h1>
            <a href="home.php" class="btn">Home</a>
        </div>

        <div class="account-container">
            <!-- Change Username Section -->
            <div class="account-section">
                <h2><i class="fas fa-user"></i> Change Username</h2>
                <?php if (isset($username_message)): ?>
                    <div class="message success"><?php echo $username_message; ?></div>
                <?php endif; ?>
                <?php if (isset($username_error)): ?>
                    <div class="message error"><?php echo $username_error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="new_username">New Username:</label>
                        <input type="text" id="new_username" name="new_username" required>
                    </div>
                    <button type="submit" name="update_username" class="btn">Update Username</button>
                </form>
            </div>

            <!-- Change Password Section -->
            <div class="account-section">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
                <?php if (isset($password_message)): ?>
                    <div class="message success"><?php echo $password_message; ?></div>
                <?php endif; ?>
                <?php if (isset($password_error)): ?>
                    <div class="message error"><?php echo $password_error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn">Update Password</button>
                </form>
            </div>

            <!-- Contact Support Section -->
            <div class="account-section">
                <h2><i class="fas fa-headset"></i> Contact Support</h2>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <p>Email: utibeabasiitoro29@gmail.com</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone"></i>
                    <p>Phone: +2348085934414</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-clock"></i>
                    <p>Hours: Monday - Friday, 9:00 AM - 5:00 PM EST</p>
                </div>
            </div>

            <!-- Logout Section -->
            <div class="account-section">
                <h2><i class="fas fa-sign-out-alt"></i> Logout</h2>
                <p>Click the button below to safely log out of your account.</p>
                <a href="logout.php" class="btn" style="display: inline-block; margin-top: 10px;">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>