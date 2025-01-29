<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['budget_id'])) {
    $budget_id = $_POST['budget_id'];

    // Delete budget from database
    $stmt = $conn->prepare("DELETE FROM budgets WHERE id = ?");
    $stmt->bind_param("i", $budget_id);

    if ($stmt->execute()) {
        // Redirect back to the user's budget page with a success message
        $_SESSION['success_message'] = 'Budget deleted successfully.';
        header("Location: user_budgets.php?user_id=" . $_SESSION['user_id']);
        exit();
    } else {
        // Handle error - maybe set an error message in session and redirect
        $_SESSION['error_message'] = 'Error deleting budget.';
        header("Location: user_budgets.php?user_id=" . $_SESSION['user_id']);
        exit();
    }
} else {
    // Redirect to users list if accessed directly without POST request or budget_id
    header("Location: users.php");
    exit();
}
?>