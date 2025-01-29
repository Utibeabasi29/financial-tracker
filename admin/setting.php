<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once '../config.php';
include 'includes/admin_header.php';

// Example setting: Default currency
$defaultCurrency = '$'; // You might fetch this from the database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update settings in the database (you'll need a settings table)
    $defaultCurrency = $_POST['default_currency'];

    // Example query (you'll need to adapt this to your database structure)
    $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE setting_name = 'default_currency'");
    $stmt->bind_param("s", $defaultCurrency);
    $stmt->execute();

    $message = "Settings updated successfully.";
}
?>

<div class="settings-container">
    <h2>System Settings</h2>

    <?php if (isset($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="settings.php" method="post">
        <div class="form-group">
            <label for="default_currency">Default Currency:</label>
            <input type="text" name="default_currency" id="default_currency" value="<?php echo $defaultCurrency; ?>">
        </div>
        <button type="submit" class="submit-btn">Save Settings</button>
    </form>
</div>

<?php include 'includes/admin_footer.php'; ?>