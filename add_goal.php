<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $target_amount = filter_var($_POST['target_amount'], FILTER_VALIDATE_FLOAT);
    $current_amount = filter_var($_POST['current_amount'], FILTER_VALIDATE_FLOAT);
    $target_date = filter_var($_POST['target_date'], FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];

    if (!$name || !$target_amount || !$current_amount || !$target_date) {
        throw new Exception("All fields are required");
    }

    $sql = "INSERT INTO financial_goals (user_id, name, target_amount, current_amount, target_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdds", $user_id, $name, $target_amount, $current_amount, $target_date);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'goal' => [
                'id' => $stmt->insert_id,
                'name' => $name,
                'target_amount' => $target_amount,
                'current_amount' => $current_amount,
                'target_date' => $target_date
            ]
        ]);
    } else {
        throw new Exception("Error adding goal");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}