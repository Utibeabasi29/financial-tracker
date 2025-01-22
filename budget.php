<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle budget submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $month = $_POST['month'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO budgets (user_id, category, amount, month) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE amount = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issds", $user_id, $category, $amount, $month, $amount);
    $stmt->execute();
}

// Fetch existing budgets
$user_id = $_SESSION['user_id'];
$current_month = date('Y-m');
$sql = "SELECT b.*, 
        COALESCE(SUM(e.amount), 0) as spent 
        FROM budgets b 
        LEFT JOIN expenses e ON b.category = e.category 
        AND DATE_FORMAT(e.date, '%Y-%m') = b.month 
        WHERE b.user_id = ? AND b.month = ? 
        GROUP BY b.category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$budgets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_design.css">
    <link rel="stylesheet" href="budget_style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Budget Management</h1>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <div class="budget-container">
            <!-- Set Budget Form -->
            <div class="budget-form">
                <h2>Set Monthly Budget</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" required>
                            <option value="groceries">Groceries</option>
                            <option value="utilities">Utilities</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="transport">Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amount">Budget Amount</label>
                        <input type="number" step="0.01" name="amount" required>
                    </div>

                    <div class="form-group">
                        <label for="month">Month</label>
                        <input type="month" name="month" value="<?php echo date('Y-m'); ?>" required>
                    </div>

                    <button type="submit" class="submit-btn">Set Budget</button>
                </form>
            </div>

            <!-- Budget Overview -->
            <div class="budget-overview">
                <h2>Current Month's Budget</h2>
                <div class="budget-cards">
                    <?php while ($budget = $budgets->fetch_assoc()): ?>
                        <div class="budget-card" 
                             data-category="<?php echo htmlspecialchars($budget['category']); ?>"
                             data-month="<?php echo htmlspecialchars($budget['month']); ?>">
                            <div class="budget-category">
                                <i class="fas fa-folder"></i>
                                <?php echo ucfirst(htmlspecialchars($budget['category'])); ?>
                                <button class="delete-btn" 
                                        data-category="<?php echo htmlspecialchars($budget['category']); ?>"
                                        data-month="<?php echo htmlspecialchars($budget['month']); ?>"
                                        onclick="deleteBudget('<?php echo htmlspecialchars($budget['category']); ?>', '<?php echo htmlspecialchars($budget['month']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="budget-progress">
                                <?php 
                                $percentage = ($budget['spent'] / $budget['amount']) * 100;
                                $status_class = $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'good');
                                ?>
                                <div class="progress-bar">
                                    <div class="progress <?php echo $status_class; ?>" 
                                         style="width: <?php echo min(100, $percentage); ?>%">
                                    </div>
                                </div>
                                <div class="budget-numbers">
                                    <span>$<?php echo number_format($budget['spent'], 2); ?> spent</span>
                                    <span>of $<?php echo number_format($budget['amount'], 2); ?></span>
                                </div>
                            </div>
                            <?php if ($percentage > 90): ?>
                                <div class="budget-alert">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Budget limit almost reached!
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="budget.js"></script>
</body>
</html>