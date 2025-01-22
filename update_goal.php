<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        $goal_id = filter_var($data['goal_id'], FILTER_VALIDATE_INT);
        $current_amount = filter_var($data['current_amount'], FILTER_VALIDATE_FLOAT);
    } else {
        $goal_id = filter_var($_POST['goal_id'], FILTER_VALIDATE_INT);
        $current_amount = filter_var($_POST['current_amount'], FILTER_VALIDATE_FLOAT);
    }

    if (!$goal_id || !$current_amount) {
        throw new Exception("Invalid input data");
    }

    $sql = "UPDATE financial_goals SET current_amount = ? 
            WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dii", $current_amount, $goal_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'goal' => [
                'id' => $goal_id,
                'current_amount' => $current_amount
            ]
        ]);
    } else {
        throw new Exception("Error updating goal");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}