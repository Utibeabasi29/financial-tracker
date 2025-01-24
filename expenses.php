<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle expense submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    $category = trim(htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8'));
    
    $date = DateTime::createFromFormat('Y-m-d', $_POST['date']);
    if ($date) {
        $date = $date->format('Y-m-d');
    } else {
        $date = null;
    }
    
    $user_id = $_SESSION['user_id'];

    // Check if all fields are valid
    if ($amount && $category && $description && $date) {
        try {
            // Prepare statement
            $stmt = $conn->prepare("INSERT INTO expenses (user_id, amount, category, description, date) VALUES (?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Bind parameters
            if (!$stmt->bind_param("idsss", $user_id, $amount, $category, $description, $date)) {
                throw new Exception("Binding parameters failed: " . $stmt->error);
            }
            
            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $message = "Expense added successfully!";
            $stmt->close();
            
        } catch (Exception $e) {
            $error = "Error adding expense: " . $e->getMessage();
        }
    } else {
        $error = "Please fill all fields correctly";
    }
}

// Fetch existing expenses
try {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $expenses = $result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    $error = "Error fetching expenses: " . $e->getMessage();
    $expenses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_design.css">
    <link rel="stylesheet" href="expenses_style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Expense Tracking</h1>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="expense-container">
            <!-- Add Expense Form -->
            <div class="expense-form">
                <h2>Add New Expense</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" name="amount" required>
                    </div>

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
                        <label for="description">Description</label>
                        <input type="text" name="description" required>
                    </div>

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" required>
                    </div>

                    <button type="submit" class="submit-btn">Add Expense</button>
                </form>
            </div>

            <!-- Expense List -->
            <div class="expense-list">
                <h2>Recent Expenses</h2>
                <div class="expense-items">
                    <?php if (!empty($expenses)): ?>
                        <?php foreach ($expenses as $expense): ?>
                            <div class="expense-item">
                                <div class="expense-date"><?php echo date('M d, Y', strtotime($expense['date'])); ?></div>
                                <div class="expense-details">
                                    <div class="expense-category"><?php echo htmlspecialchars($expense['category']); ?></div>
                                    <div class="expense-description"><?php echo htmlspecialchars($expense['description']); ?></div>
                                </div>
                                <div class="expense-amount">$<?php echo number_format($expense['amount'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No expenses found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="expenses.js"></script>
</body>
</html>