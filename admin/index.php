<?php
session_start();

// Check if admin is logged in, redirect to login page if not
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once '../config.php'; // Adjust path to config.php as needed
include 'includes/admin_header.php'; 
?>

<div class="dashboard-container">
    <h2>Admin Dashboard</h2>
    <p>Welcome to the admin dashboard. Here, you can manage users, view reports, and configure settings.</p>
    <a href="users.php" class="dashboard-link">
        <i class="fas fa-users"></i> Manage Users
    </a>
    </div>

<?php include 'includes/admin_footer.php'; ?>