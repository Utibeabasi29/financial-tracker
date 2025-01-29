<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php'); // Get the current page name
?>

<nav class="side-menu">
    <h2 class="menu-title">Menu</h2>
    <ul class="menu-list">
        <li class="<?php echo $current_page === 'home' ? 'active' : ''; ?>">
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
        </li>
        <li class="<?php echo $current_page === 'expenses' ? 'active' : ''; ?>">
            <a href="expenses.php"><i class="fas fa-receipt"></i> Expenses</a>
        </li>
        <li class="<?php echo $current_page === 'budget' ? 'active' : ''; ?>">
            <a href="budget.php"><i class="fas fa-wallet"></i> Budget</a>
        </li>
        <li class="<?php echo $current_page === 'goals' ? 'active' : ''; ?>">
            <a href="goals.php"><i class="fas fa-bullseye"></i> Financial Goals</a>
        </li>
        <li class="<?php echo $current_page === 'reports' ? 'active' : ''; ?>">
            <a href="reports.php"><i class="fas fa-chart-pie"></i> Reports</a>
        </li>
        <li class="<?php echo $current_page === 'account' ? 'active' : ''; ?>">
            <a href="account.php"><i class="fas fa-user"></i> Account</a>
        </li>
    </ul>
</nav>
