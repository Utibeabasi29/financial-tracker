<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up page</title>
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
    <form method="POST" action="">
        <h3>Sign Up Here</h3>

        <label for="username">Full Name</label>
        <input type="text" placeholder="Enter your full name" id="username" name="username">

        <label for="email">Email</label>
        <input type="email" placeholder="Enter your email" id="email" name="email">

        <label for="password">Password</label>
        <input type="password" placeholder="Create password" id="password" name="password">

        <label for="confirm-password">Confirm Password</label>
        <input type="password" placeholder="Confirm password" id="confirm-password" name="confirm-password">

        <button type="submit">Sign Up</button>
        
        <div class="forgot-password">
            <a href="index.html">Already have an account? Login</a>
        </div>
        
        <div class="switch-form">
            <p>Already have an account? <a href="Login.php">Login</a></p>
        </div>
    </form>
    <?php
    require 'db.php'; // Include database connection

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo '<div class="alert alert-error">Email already exists!</div>';
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $fullname, $email, $password);

            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                echo '<div class="alert alert-error">Error: ' . $stmt->error . '</div>';
            }
        }

        $stmt->close();
    }
    ?>


    
</body>
</html>
