<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$response = ['success' => false, 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $month = $_POST['month']; // Assuming format YYYY-MM
    $user_id = $_SESSION['user_id'];

    try {
        // Convert $month to YYYY-MM-DD format for DATE type in database
        $month = date('Y-m-d', strtotime($month . '-01'));

        $sql = "INSERT INTO budgets (user_id, category, amount, month) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE amount = VALUES(amount)";

        $stmt = $conn->prepare($sql);

        // Use 's' for month to bind as a string representing the date
        $stmt->bind_param("isss", $user_id, $category, $amount, $month);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Budget for " . ucfirst($category) . " in " . date('F Y', strtotime($month)) . " set successfully!";
        } else {
            throw new Exception("Error setting budget: " . $stmt->error);
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
?>