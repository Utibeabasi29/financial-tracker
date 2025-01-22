<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
        $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
        $user_id = $_SESSION['user_id'];

        if (!$amount || !$category || !$description || !$date) {
            throw new Exception("All fields are required");
        }

        // Insert expense
        $sql = "INSERT INTO expenses (user_id, amount, category, description, date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idsss", $user_id, $amount, $category, $description, $date);
        
        if ($stmt->execute()) {
            $expense_id = $stmt->insert_id;
            
            // Return newly created expense
            echo json_encode([
                'success' => true,
                'expense' => [
                    'id' => $expense_id,
                    'amount' => $amount,
                    'category' => $category,
                    'description' => $description,
                    'date' => $date
                ]
            ]);
        } else {
            throw new Exception("Error inserting expense");
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>