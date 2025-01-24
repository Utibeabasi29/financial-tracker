<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


$db_host = 'myproject.local';
$db_username = 'root';
$db_password = '';
$db_name = 'savings_tracker'; 

try {
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

    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

try {
    $conn->query("SELECT 1");
} catch (Exception $e) {
    die("Database test failed: " . $e->getMessage());
}
?>