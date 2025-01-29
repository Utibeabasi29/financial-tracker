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
        if ($amount <= 0) {
            throw new Exception("Amount must be a positive number");
        }
        
        $category = trim(htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8'));
        if (strlen($category) > 50) {
            throw new Exception("Category is too long");
        }

        $description = trim(htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8'));
        if (strlen($description) > 255) {
            throw new Exception("Description is too long");
        }
        
        // Validate and sanitize the date
        $date = DateTime::createFromFormat('Y-m-d', $_POST['date']);
        if ($date) {
            $date = $date->format('Y-m-d'); // Format to a standard date string
        } else {
            throw new Exception("Invalid date format");
        }

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