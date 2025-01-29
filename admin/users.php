<?php
session_start();

// Check if admin is logged in, redirect to login page if not
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once '../config.php';
include 'includes/admin_header.php';

// Fetch all users from the database
$stmt = $conn->prepare("SELECT id, username, email FROM users");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="user-management-container">
    <h2>User Management</h2>
    <h3>Add New User</h3>
    <form action="add_user.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Add User</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="edit-btn">Edit</a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        <a href="user_budgets.php?user_id=<?php echo $user['id']; ?>" class="view-btn">View Budgets</a>
                        <a href="user_expenses.php?user_id=<?php echo $user['id']; ?>" class="view-btn">View Expenses</a>
                        <a href="user_goals.php?user_id=<?php echo $user['id']; ?>" class="view-btn">View Goals</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>
