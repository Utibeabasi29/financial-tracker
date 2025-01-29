<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get start and end dates from the query parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Validate the dates (add more robust validation as needed)
if (!strtotime($start_date) || !strtotime($end_date)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid date format']);
    exit();
}

// Fetch expense summary by category
$sql = "SELECT category, SUM(amount) as total 
        FROM expenses 
        WHERE user_id = ? AND date BETWEEN ? AND ?
        GROUP BY category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$expense_summary_result = $stmt->get_result();
$expense_summary = [];
while ($row = $expense_summary_result->fetch_assoc()) {
    $expense_summary[] = $row;
}

// Fetch monthly spending trend
$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total 
        FROM expenses 
        WHERE user_id = ? AND date BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(date, '%Y-%m')
        ORDER BY month DESC 
        LIMIT 6";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$monthly_trend_result = $stmt->get_result();
$monthly_trend = [];
while ($row = $monthly_trend_result->fetch_assoc()) {
    $monthly_trend[] = $row;
}

// Fetch budget vs actual spending (for the current month)
$current_month = date('Y-m'); // Use parameter for consistency
$sql = "SELECT b.category, b.amount as budget, COALESCE(SUM(e.amount), 0) as spent
        FROM budgets b
        LEFT JOIN expenses e ON b.category = e.category 
        AND DATE_FORMAT(e.date, '%Y-%m') = b.month
        WHERE b.user_id = ? AND b.month = ?
        GROUP BY b.category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$budget_comparison_result = $stmt->get_result();
$budget_comparison = [];
while ($row = $budget_comparison_result->fetch_assoc()) {
    $budget_comparison[] = $row;
}

// Prepare data for the charts
$category_data = [
    'labels' => array_column($expense_summary, 'category'),
    'values' => array_column($expense_summary, 'total')
];

$trend_data = [
    'labels' => array_column($monthly_trend, 'month'),
    'values' => array_column($monthly_trend, 'total')
];

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'categories' => $category_data,
    'trends' => $trend_data,
    'budgets' => $budget_comparison
]);
?>