<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Financial Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="home_design.css">
</head>
<body>
    <div class="home-container">
        <div class="overview-cards">
            <div class="card">
                <h3>Total Balance</h3>
                <p class="amount">$10,000</p>
                <i class="fas fa-dollar-sign icon"></i>
            </div>
            <div class="card">
                <h3>Monthly Expenses</h3>
                <p class="amount">$2,500</p>
                <i class="fas fa-receipt icon"></i>
            </div>
            <div class="card">
                <h3>Savings Goal</h3>
                <p class="amount">75%</p>
                <i class="fas fa-piggy-bank icon"></i>
            </div>
        </div>

        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <div class="activity-item">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="activity-details">
                        <p class="activity-title">Grocery Shopping</p>
                        <p class="activity-amount">-$150.00</p>
                        <p class="activity-date">Today</p>
                    </div>
                </div>
                <div class="activity-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <div class="activity-details">
                        <p class="activity-title">Salary Deposit</p>
                        <p class="activity-amount">+$3,000.00</p>
                        <p class="activity-date">Yesterday</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>