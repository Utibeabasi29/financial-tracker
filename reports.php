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
$expense_summary = $stmt->get_result();

// Fetch monthly spending trend
$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total 
        FROM expenses 
        WHERE user_id = ? 
        GROUP BY DATE_FORMAT(date, '%Y-%m')
        ORDER BY month DESC 
        LIMIT 6";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$monthly_trend = $stmt->get_result();

// Fetch budget vs actual spending
$sql = "SELECT b.category, b.amount as budget, COALESCE(SUM(e.amount), 0) as spent
        FROM budgets b
        LEFT JOIN expenses e ON b.category = e.category 
        AND DATE_FORMAT(e.date, '%Y-%m') = b.month
        WHERE b.user_id = ? AND b.month = DATE_FORMAT(CURRENT_DATE, '%Y-%m')
        GROUP BY b.category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$budget_comparison = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dashboard_design.css">
    <link rel="stylesheet" href="reports_style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Financial Reports</h1>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <!-- Date Range Filter -->
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
            <!-- Expense by Category -->
            <div class="report-card">
                <h2>Expenses by Category</h2>
                <canvas id="categoryChart"></canvas>
            </div>

            <!-- Monthly Spending Trend -->
            <div class="report-card">
                <h2>Monthly Spending Trend</h2>
                <canvas id="trendChart"></canvas>
            </div>

            <!-- Budget vs Actual -->
            <div class="report-card">
                <h2>Budget vs Actual Spending</h2>
                <div class="budget-comparison">
                    <?php while ($row = $budget_comparison->fetch_assoc()): ?>
                        <div class="comparison-item">
                            <div class="category-label">
                                <?php echo ucfirst($row['category']); ?>
                            </div>
                            <div class="comparison-bar">
                                <div class="budget-bar" style="width: 100%">
                                    $<?php echo number_format($row['budget'], 2); ?>
                                </div>
                                <?php 
                                $percentage = ($row['spent'] / $row['budget']) * 100;
                                $bar_class = $percentage > 100 ? 'over' : 'under';
                                ?>
                                <div class="actual-bar <?php echo $bar_class; ?>" 
                                     style="width: <?php echo min(100, $percentage); ?>%">
                                    $<?php echo number_format($row['spent'], 2); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for charts
        const categoryLabels = [<?php 
            $expense_summary->data_seek(0);
            while ($row = $expense_summary->fetch_assoc()) {
                echo "'" . ucfirst($row['category']) . "',";
            }
        ?>];
        
        const categoryValues = [<?php 
            $expense_summary->data_seek(0);
            while ($row = $expense_summary->fetch_assoc()) {
                echo $row['total'] . ",";
            }
        ?>];

        const trendLabels = [<?php 
            $monthly_trend->data_seek(0);
            while ($row = $monthly_trend->fetch_assoc()) {
                echo "'" . date('M Y', strtotime($row['month'] . '-01')) . "',";
            }
        ?>];

        const trendValues = [<?php 
            $monthly_trend->data_seek(0);
            while ($row = $monthly_trend->fetch_assoc()) {
                echo $row['total'] . ",";
            }
        ?>];
    </script>
    <script src="reports.js"></script>
</body>
</html> 