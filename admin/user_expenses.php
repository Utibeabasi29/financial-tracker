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

// Fetch user's expenses
$stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);

include 'includes/admin_header.php';
?>

<div class="container">
    <h2>User Expenses</h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($expense['date'])); ?></td>
                    <td><?php echo htmlspecialchars($expense['category']); ?></td>
                    <td><?php echo htmlspecialchars($expense['description']); ?></td>
                    <td>₦<?php echo number_format($expense['amount'], 2); ?></td>
                    <td>
                        <form action="delete_expense.php" method="post" style="display: inline-block;">
                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this expense?');">
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