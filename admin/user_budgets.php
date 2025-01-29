<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Check if user_id is provided in the URL
if (!isset($_GET['user_id'])) {
    header("Location: users.php"); // Redirect to users list if no user_id
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user's budgets
$stmt = $conn->prepare("SELECT * FROM budgets WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$budgets = $result->fetch_all(MYSQLI_ASSOC);

include 'includes/admin_header.php';
?>

<div class="container">
    <h2>User Budgets</h2>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Month</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($budgets as $budget): ?>
                <tr>
                    <td><?php echo htmlspecialchars($budget['category']); ?></td>
                    <td>₦<?php echo number_format($budget['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($budget['month']); ?></td>
                    <td>
                        <form action="delete_budget.php" method="post" style="display: inline-block;">
                            <input type="hidden" name="budget_id" value="<?php echo $budget['id']; ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this budget?');">
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