<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once '../config.php';
include 'includes/admin_header.php';

// Example: Fetch all expenses
$stmt = $conn->prepare("SELECT e.*, u.username FROM expenses e JOIN users u ON e.user_id = u.id ORDER BY e.date DESC");
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="data-management-container">
    <h2>View Expenses</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?php echo $expense['id']; ?></td>
                    <td><?php echo $expense['username']; ?></td>
                    <td>₦<?php echo number_format($expense['amount'], 2); ?></td>
                    <td><?php echo $expense['category']; ?></td>
                    <td><?php echo $expense['description']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($expense['date'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>