<?php
session_start();
require_once 'config.php';

$goals = null;
$goals_array = [];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle goal submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add') {
                // Validate user exists
                $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
                $check_user->bind_param("i", $_SESSION['user_id']);
                $check_user->execute();
                $user_result = $check_user->get_result();

                if ($user_result->num_rows === 0) {
                    throw new Exception("Invalid user");
                }

                $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
                $target_amount = filter_var($_POST['target_amount'], FILTER_VALIDATE_FLOAT);
                $current_amount = filter_var($_POST['current_amount'], FILTER_VALIDATE_FLOAT);
                $target_date = $_POST['target_date'];
                $user_id = $_SESSION['user_id'];

                // Server-side validation for the date
                $today = date('Y-m-d');
                if ($target_date < $today) {
                    throw new Exception("Target date must be in the future.");
                }

                if (!$name || !$target_amount || !$current_amount || !$target_date) {
                    throw new Exception("All fields are required and must be valid");
                }

                $conn->select_db("savings_tracker");

                $sql = "INSERT INTO financial_goals (user_id, name, target_amount, current_amount, target_date) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("isdds", $user_id, $name, $target_amount, $current_amount, $target_date);

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $message = "Goal added successfully!";
                $_SESSION['flash_message'] = $message; // Add flash message
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } elseif ($_POST['action'] === 'delete') {
                // Handle goal deletion
                $goal_id = intval($_POST['goal_id']);
                $user_id = $_SESSION['user_id'];

                $delete_sql = "DELETE FROM financial_goals WHERE id = ? AND user_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);

                if (!$delete_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $delete_stmt->bind_param("ii", $goal_id, $user_id);

                if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
                    $message = "Goal deleted successfully!";
                } else {
                    throw new Exception("Failed to delete goal. It may not exist or belong to another user.");
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch existing goals
try {
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT *, 
            DATEDIFF(target_date, CURDATE()) as days_remaining,
            (current_amount/target_amount * 100) as progress 
            FROM financial_goals 
            WHERE user_id = ? 
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $goals_array = [];

    while ($row = $result->fetch_assoc()) {
        $goals_array[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching goals: " . $e->getMessage());
    $error = "Error fetching goals: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Goals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_design.css">
    <link rel="stylesheet" href="goals_style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Financial Goals</h1>
            <a href="home.php" class="back-btn">Home</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success fade-in">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger fade-in">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="goals-container">
            <div class="goals-form">
                <h2>Set New Goal</h2>
                <form method="POST" id="goalForm">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="name">Goal Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="target_amount">Target Amount</label>
                        <input type="number" step="0.01" name="target_amount" required>
                    </div>

                    <div class="form-group">
                        <label for="current_amount">Current Amount</label>
                        <input type="number" step="0.01" name="current_amount" required>
                    </div>

                    <div class="form-group">
                        <label for="target_date">Target Date</label>
                        <input type="date" name="target_date" id="target_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" class="submit-btn">Create Goal</button>
                </form>
                <div id="formFeedback" class="alert" style="display: none;"></div>
            </div>

            <div class="goals-overview">
                <h2>Your Financial Goals</h2>
                <div class="goals-grid">
                    <?php if (!empty($goals_array)): ?>
                        <?php foreach ($goals_array as $goal): ?>
                            <div class="goal-card fade-in">
                            <div class="goal-header">
                                <h3><?php echo htmlspecialchars($goal['name']); ?></h3>
                                <div class="goal-actions">
                                    <a href="edit_goal.php?goal_id=<?php echo $goal['id']; ?>" class="edit-btn" title="Edit Goal">
                                        <i class="fas fa-edit"></i> Edit 
                                    </a>
                                    <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this goal?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                                        <button type="submit" class="delete-btn" title="Delete Goal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                                <div class="goal-progress">
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo min(100, $goal['progress']); ?>%"></div>
                                    </div>
                                    <div class="progress-numbers">
                                        <span>₦<?php echo number_format($goal['current_amount'], 2); ?></span>
                                        <span>of ₦<?php echo number_format($goal['target_amount'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="goal-status">
                                    <span><?php echo round($goal['progress'], 1); ?>% Complete</span>
                                    <span><?php echo $goal['days_remaining']; ?> days left</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-goals-message">No financial goals found. Add one to get started!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
