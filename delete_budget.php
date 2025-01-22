<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log incoming data
error_log("POST data: " . print_r($_POST, true));

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

if (!isset($_POST['category']) || !isset($_POST['month'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$category = trim($_POST['category']);
$month = trim($_POST['month']);

// Log the SQL parameters
error_log("Deleting budget - User ID: $user_id, Category: $category, Month: $month");

// First check if the budget exists
$check_sql = "SELECT id FROM budgets WHERE user_id = ? AND category = ? AND month = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iss", $user_id, $category, $month);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Budget not found']);
    exit;
}

$sql = "DELETE FROM budgets WHERE user_id = ? AND category = ? AND month = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $category, $month);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Delete error: " . $conn->error);
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$check_stmt->close();
$stmt->close();
$conn->close();
?>