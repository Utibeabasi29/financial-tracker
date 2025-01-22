<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function getTimeBasedGreeting() {
    $hour = date('H');
    if ($hour >= 5 && $hour < 12) {
        return "Good Morning";
    } elseif ($hour >= 12 && $hour < 17) {
        return "Good Afternoon";
    } else {
        return "Good Evening";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Financial Portal</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dashboard_design.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="welcome-text"><?php echo getTimeBasedGreeting(); ?>, 
                <?php echo htmlspecialchars(isset($_SESSION['username']) ? ucfirst(strtolower($_SESSION['username'])) : 'User'); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-item" onclick="location.href='home.php'">
                <i class="fas fa-home icon-blue" style="color: #007bff;"></i>
                <h2 class="item-title">Home</h2>
                <p class="item-description">View your dashboard overview and recent activities</p>
            </div>

            <div class="dashboard-item" onclick="location.href='expenses.php'">
                <i class="fas fa-receipt icon-red" style="color: #ff0000;"></i>
                <h2 class="item-title">Expenses</h2>
                <p class="item-description">Track and manage your expenses</p>
            </div>

            <div class="dashboard-item" onclick="location.href='budget.php'">
                <i class="fas fa-wallet icon-green" style="color: #28a745;"></i>
                <h2 class="item-title">Budget</h2>
                <p class="item-description">Set and monitor your budgets</p>
            </div>

            <div class="dashboard-item" onclick="location.href='goals.php'">
                <i class="fas fa-bullseye icon-orange" style="color: #ffc107;"></i>
                <h2 class="item-title">Financial Goals</h2>
                <p class="item-description">Set and track your financial goals</p>
            </div>

            <div class="dashboard-item" onclick="location.href='reports.php'">
                <i class="fas fa-chart-pie icon-purple" style="color: #9b59b6;"></i>
                <h2 class="item-title">Reports</h2>
                <p class="item-description">View financial reports and analytics</p>
            </div>

            <div class="dashboard-item" onclick="location.href='account.php'">
                <i class="fas fa-user icon-teal" style="color: #1abc9c;"></i>
                <h2 class="item-title">Account</h2>
                <p class="item-description">Manage your profile and settings</p>
            </div>
        </div>
    </div>
</body>
</html>