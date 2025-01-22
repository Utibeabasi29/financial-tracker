<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'savings_tracker'; // Changed from financial_portal to match your database name

// Create database connection
try {
    // Add connection options for better security and performance
    $conn = new mysqli(
        $db_host,
        $db_username,
        $db_password,
        $db_name
    );

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset properly with error checking
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Test connection
try {
    $conn->query("SELECT 1");
} catch (Exception $e) {
    die("Database test failed: " . $e->getMessage());
}
?>