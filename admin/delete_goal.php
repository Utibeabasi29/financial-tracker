<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_id'])) {
    $goal_id = $_POST['goal_id'];

    // Delete goal from database
    $stmt = $conn->prepare("DELETE FROM financial_goals WHERE id = ?");
    $stmt->bind_param("i", $goal_id);

    if ($stmt->execute()) {
        // Redirect back to the user's goals page with a success message
        $_SESSION['success_message'] = 'Goal deleted successfully.';
        header("Location: user_goals.php?user_id=" . $_SESSION['user_id']);
        exit();
    } else {
        // Handle error - maybe set an error message in session and redirect
        $_SESSION['error_message'] = 'Error deleting goal.';
        header("Location: user_goals.php?user_id=" . $_SESSION['user_id']);
        exit();
    }
} else {
    // Redirect to users list if accessed directly without POST request or goal_id
    header("Location: users.php");
    exit();
}
?>