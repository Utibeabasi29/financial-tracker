<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="icon" type="image/x-icon" href="images/Favicon/favicon_io/favicon-32x32.png">
    <link rel="stylesheet" href="styles.css">
 
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h3>Login Here</h3>

        <label for="email">Email</label>
        <input type="email" placeholder="Enter your email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" placeholder="Password" id="password" name="password" required>

        <button type="submit">Log In</button>
        
        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
        
        <div class="switch-form">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </form>
    <?php
    require 'config.php';
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: home.php");
                exit();
            } else {
                echo "<div class='error-message'>Invalid password.</div>";
            }
        } else {
            echo "<div class='error-message'>No user found with this email.</div>";
        }

        $stmt->close();
    }
    $conn->close();
    ?>

</body>
</html>
