<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update the goal
        $goal_id = intval($_POST['goal_id']);
        $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
        $target_amount = filter_var($_POST['target_amount'], FILTER_VALIDATE_FLOAT);
        $current_amount = filter_var($_POST['current_amount'], FILTER_VALIDATE_FLOAT);
        $target_date = $_POST['target_date'];
        $user_id = $_SESSION['user_id'];

        if (!$name || !$target_amount || !$current_amount || !$target_date) {
            throw new Exception("All fields are required and must be valid.");
        }

        $today = date('Y-m-d');
        if ($target_date < $today) {
            throw new Exception("Target date must be in the future.");
        }

        $sql = "UPDATE financial_goals
                SET name = ?, target_amount = ?, current_amount = ?, target_date = ?
                WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sddsii", $name, $target_amount, $current_amount, $target_date, $goal_id, $user_id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $message = "Goal updated successfully!";
    } elseif (isset($_GET['goal_id'])) {
        // Fetch the goal for pre-filling the form
        $goal_id = intval($_GET['goal_id']);
        $user_id = $_SESSION['user_id'];

        $sql = "SELECT * FROM financial_goals WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ii", $goal_id, $user_id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Goal not found or access denied.");
        }

        $goal = $result->fetch_assoc();
    } else {
        throw new Exception("Invalid access.");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Goal</title>
    <link rel="stylesheet" href="dashboard_design.css">
    <link rel="stylesheet" href="goals_style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Goal</h1>
            <a href="goals.php" class="back-btn">Back to Goals</a>
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

        <?php if (!empty($goal)): ?>
            <form method="POST" class="edit-form">
                <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                <div class="form-group">
                    <label for="name">Goal Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($goal['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="target_amount">Target Amount</label>
                    <input type="number" step="0.01" id="target_amount" name="target_amount" value="<?php echo $goal['target_amount']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="current_amount">Current Amount</label>
                    <input type="number" step="0.01" id="current_amount" name="current_amount" value="<?php echo $goal['current_amount']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="target_date">Target Date</label>
                    <input type="date" id="target_date" name="target_date" value="<?php echo $goal['target_date']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <button type="submit" class="submit-btn">Update Goal</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
