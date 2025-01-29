<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get date range from query parameters or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

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

// Fetch budget information for the selected date range
$sql = "SELECT category, amount as budget, month
        FROM budgets
        WHERE user_id = ? AND DATE_FORMAT(month, '%Y-%m') BETWEEN DATE_FORMAT(?, '%Y-%m') AND DATE_FORMAT(?, '%Y-%m')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$budget_result = $stmt->get_result();
$budgets = [];
while ($row = $budget_result->fetch_assoc()) {
    $budgets[] = $row;
}

// Fetch financial goals for the selected date range
$sql = "SELECT name, target_amount, current_amount, target_date, created_at
        FROM financial_goals
        WHERE user_id = ? AND ((target_date >= ? AND target_date <= ?) OR (created_at >= ? AND created_at <= ?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $user_id, $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$goals_result = $stmt->get_result();
$financial_goals = [];
while ($row = $goals_result->fetch_assoc()) {
    $financial_goals[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Overview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_design.css">
    <link rel="stylesheet" href="reports_style.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Financial Reports</h1>
            <a href="home.php" class="back-btn">Home</a>
        </div>

        <div class="date-filter">
            <form method="GET">
                <div class="filter-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="filter-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <button type="submit" class="filter-btn">Apply Filter</button>
            </form>
        </div>

        <div class="reports-grid">
            <div class="report-card">
                <h2>Expenses by Category</h2>
                <?php if (count($expense_summary) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total Amount (₦)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expense_summary as $expense): ?>
                                <tr>
                                    <td><?php echo $expense['category']; ?></td>
                                    <td>₦<?php echo number_format($expense['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No expenses recorded for the selected period.</p>
                <?php endif; ?>
            </div>

            <div class="report-card">
                <h2>Budgets</h2>
                <?php if (count($budgets) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Budget (₦)</th>
                                <th>Month</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $budget): ?>
                                <tr>
                                    <td><?php echo $budget['category']; ?></td>
                                    <td>₦<?php echo number_format($budget['budget'], 2); ?></td>
                                    <td><?php echo date('F Y', strtotime($budget['month'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No budgets set for the selected period.</p>
                <?php endif; ?>
            </div>

            <div class="report-card">
                <h2>Financial Goals</h2>
                <?php if (count($financial_goals) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Goal Name</th>
                                <th>Target Amount (₦)</th>
                                <th>Saved Amount (₦)</th>
                                <th>Target Date</th>
                                <th>Created On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financial_goals as $goal): ?>
                                <tr>
                                    <td><?php echo $goal['name']; ?></td>
                                    <td>₦<?php echo number_format($goal['target_amount'], 2); ?></td>
                                    <td>₦<?php echo number_format($goal['current_amount'], 2); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($goal['target_date'])); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($goal['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No financial goals recorded for the selected period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>