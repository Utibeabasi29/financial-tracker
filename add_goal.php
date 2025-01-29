<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the request method is POST and if the action is 'add'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        // Sanitize and validate input data
        $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
        $target_amount = filter_var($_POST['target_amount'], FILTER_VALIDATE_FLOAT);
        $current_amount = filter_var($_POST['current_amount'], FILTER_VALIDATE_FLOAT);
        $target_date = $_POST['target_date']; // No need to sanitize date with htmlspecialchars here
        $user_id = $_SESSION['user_id'];

        // Validate the date on the server side
        if (strtotime($target_date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Target date must be in the future");
        }
        
        // Check if all required fields are valid
        if (!$name || !$target_amount || !$current_amount || !$target_date) {
            throw new Exception("All fields are required and must be valid");
        }

        // Insert new financial goal into the database
        $stmt = $conn->prepare("INSERT INTO financial_goals (user_id, name, target_amount, current_amount, target_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdds", $user_id, $name, $target_amount, $current_amount, $target_date);
        
        if ($stmt->execute()) {
            // Success response
            echo json_encode([
                'success' => true,
                'message' => 'Goal added successfully!',
                'goal' => [
                    'id' => $stmt->insert_id,
                    'name' => $name,
                    'target_amount' => $target_amount,
                    'current_amount' => $current_amount,
                    'target_date' => $target_date
                ]
            ]);
        } else {
            // Error response if the database insert fails
            throw new Exception("Error adding goal: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Catch any exceptions and return an error response
        http_response_code(400); // Bad request
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    // Respond with an error if the request method is not POST or action is not 'add'
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>