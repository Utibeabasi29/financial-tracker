<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle budget submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $month = $_POST['month']; // Assuming format YYYY-MM
    $user_id = $_SESSION['user_id'];

    // Convert $month to YYYY-MM-DD format for DATE type in database
    $month = date('Y-m-d', strtotime($month . '-01'));

    $sql = "INSERT INTO budgets (user_id, category, amount, month) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE amount = VALUES(amount)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $category, $amount, $month);

    if ($stmt->execute()) {
        $message = "Budget for " . ucfirst($category) . " in " . date('F Y', strtotime($month)) . " set successfully!";
    } else {
        $error = "Error setting budget: " . $stmt->error;
    }
}

// Fetch existing budgets for the selected month
$user_id = $_SESSION['user_id'];
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Validate selected_month format
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selected_month)) {
    $selected_month = date('Y-m');  // Default to the current month if invalid
}

// Convert $selected_month to YYYY-MM-DD format for DATE type in database
$selected_month = date('Y-m-d', strtotime($selected_month . '-01'));

// Corrected SQL query to filter expenses by user_id
$sql = "SELECT b.*, 
        COALESCE(SUM(e.amount), 0) as spent 
        FROM budgets b 
        LEFT JOIN expenses e ON b.category = e.category 
        AND b.month = DATE_FORMAT(e.date, '%Y-%m-01') AND b.user_id = e.user_id 
        WHERE b.user_id = ? AND b.month = ? 
        GROUP BY b.category, b.amount, b.month"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $selected_month);
$stmt->execute();
$result = $stmt->get_result();
$budgets = [];
while ($row = $result->fetch_assoc()) {
    $budgets[] = $row;
}
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
            <a href="home.php" class="back-btn">Home</a>
        </div>

        <div class="budget-container">
            <div class="budget-form">
                <h2>Set Monthly Budget</h2>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
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

            <div class="budget-overview">
                <h2>Budget for <?php echo date('F Y', strtotime($selected_month)); ?></h2>
                <form method="GET">
                    <div class="form-group">
                        <label for="month">Select Month:</label>
                        <input type="month" name="month" value="<?php echo date('Y-m', strtotime($selected_month)); ?>">
                        <button type="submit" class="filter-btn">Show Budget</button>
                    </div>
                </form>

                <div class="budget-cards">
                    <?php if (count($budgets) > 0): ?>
                        <?php foreach ($budgets as $budget): ?>
                            <div class="budget-card" data-category="<?php echo htmlspecialchars($budget['category']); ?>" data-month="<?php echo htmlspecialchars($budget['month']); ?>">
                                <div class="budget-header">
                                    <div class="budget-category">
                                        <i class="fas fa-folder"></i>
                                        <?php echo ucfirst(htmlspecialchars($budget['category'])); ?>
                                    </div>
                                    <button class="delete-btn" onclick="deleteBudget('<?php echo htmlspecialchars($budget['category']); ?>', '<?php echo htmlspecialchars($budget['month']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="budget-progress">
                                    <?php
                                    $percentage = ($budget['spent'] / $budget['amount']) * 100;
                                    $status_class = $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'good');
                                    ?>
                                    <div class="progress-bar">
                                        <div class="progress <?php echo $status_class; ?>" style="width: <?php echo min(100, $percentage); ?>%"></div>
                                    </div>
                                    <div class="budget-numbers">
                                        <span>₦<?php echo number_format($budget['spent'], 2); ?> spent</span>
                                        <span>of ₦<?php echo number_format($budget['amount'], 2); ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($percentage > 90): ?>
                                    <div class="budget-alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Budget limit almost reached!
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No budget set for this month.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteBudget(category, month) {
            if (confirm('Are you sure you want to delete the budget for ' + category + ' for ' + month + '?')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete_budget.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status == 200) {
                        alert('Budget deleted successfully');
                        location.reload();
                    } else {
                        alert('Error deleting budget');
                    }
                };
                xhr.send('category=' + encodeURIComponent(category) + '&month=' + encodeURIComponent(month));
            }
        }
    </script>
</body>
</html>