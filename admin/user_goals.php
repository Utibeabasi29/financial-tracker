<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user's goals
$stmt = $conn->prepare("SELECT * FROM financial_goals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$goals = $result->fetch_all(MYSQLI_ASSOC);

include 'includes/admin_header.php';
?>

<div class="container">
    <h2>User Financial Goals</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Target Amount</th>
                <th>Current Amount</th>
                <th>Target Date</th>
                <th>Progress</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($goals as $goal): ?>
                <tr>
                    <td><?php echo htmlspecialchars($goal['name']); ?></td>
                    <td>₦<?php echo number_format($goal['target_amount'], 2); ?></td>
                    <td>₦<?php echo number_format($goal['current_amount'], 2); ?></td>
                    <td><?php echo date('M d, Y', strtotime($goal['target_date'])); ?></td>
                    <td><?php echo number_format($goal['current_amount'] / $goal['target_amount'] * 100, 2); ?>%</td>
                    <td>
                        <form action="delete_goal.php" method="post" style="display: inline-block;">
                            <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this goal?');">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="users.php" class="back-btn">Back to Users</a>
</div>

<?php include 'includes/admin_footer.php'; ?>