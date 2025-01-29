<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expense_id'])) {
    $expense_id = $_POST['expense_id'];

    // Delete expense from database
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $expense_id);

    if ($stmt->execute()) {
        // Redirect back to the user's expenses page with a success message
        $_SESSION['success_message'] = 'Expense deleted successfully.';
        header("Location: user_expenses.php?user_id=" . $_SESSION['user_id']);
        exit();
    } else {
        // Handle error - maybe set an error message in session and redirect
        $_SESSION['error_message'] = 'Error deleting expense.';
        header("Location: user_expenses.php?user_id=" . $_SESSION['user_id']);
        exit();
    }
} else {
    // Redirect to users list if accessed directly without POST request or expense_id
    header("Location: users.php");
    exit();
}
?>