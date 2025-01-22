<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    $action = $_POST['action'] ?? 'update';
    $user_id = $_SESSION['user_id'];

    switch($action) {
        case 'delete':
            if (!isset($_POST['category'])) {
                throw new Exception('Category required for deletion');
            }
            
            $category = $_POST['category'];
            $month = date('Y-m');
            
            $sql = "DELETE FROM budgets WHERE user_id = ? AND category = ? AND month = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $category, $month);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Budget deleted successfully'
            ]);
            break;

        case 'update':
        default:
            if (!isset($_POST['category']) || !isset($_POST['amount'])) {
                throw new Exception('Missing required fields');
            }

            $category = $_POST['category'];
            $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
            $month = date('Y-m');

            $sql = "INSERT INTO budgets (user_id, category, amount, month) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE amount = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isdss", $user_id, $category, $amount, $month, $amount);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            echo json_encode([
                'success' => true,
                'budget' => [
                    'category' => $category,
                    'amount' => $amount,
                    'month' => $month
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Budget update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>