<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function getTimeBasedGreeting() {
    $hour = date('H');
    if ($hour >= 5 && $hour < 12) {
        return "Good Morning";
    } elseif ($hour >= 12 && $hour < 17) {
        return "Good Afternoon";
    } else {
        return "Good Evening";
    }
}

// Financial tips array
$financialTips = [
    "Budgeting and Saving Tips: Use the 50/30/20 rule: 50% needs, 30% wants, 20% savings.",
    "Spending Tips: Always compare prices before making major purchases.",
    "Debt Management Tips: Pay off high-interest debt first (debt avalanche method).",
    "Investing Tips: Start investing early to benefit from compound interest.",
    "Income Generation Tips: Build multiple income streams to reduce reliance on one source.",
    "Retirement Planning Tips: Start contributing to retirement funds as early as possible.",
    "Insurance Tips: Have adequate health, home, and auto insurance.",
    "Risk Management Tips: Protect valuable items with specific insurance riders.",
    "Financial Planning Tips: Set clear financial goals with specific timelines.",
    "Saving Tips: Review your subscriptions and cancel unnecessary ones.",
    "Spending Tips: Limit dining out to special occasions.",
    "Debt Tips: Make more than the minimum payment on your credit cards.",
    "Retirement Planning Tips:Increase your retirement contributions annually.",
    "Retirement Planning Tips: Take advantage of employer-matching contributions.",
    "Retirement Planning Tips: Calculate how much you’ll need for retirement and plan accordingly.",
    "Retirement Planning Tips: Keep your retirement funds diversified.",
    "Retirement Planning Tips: Avoid withdrawing from retirement savings unless absolutely necessary.",
    "Retirement Planning Tips: Delay taking Social Security benefits to maximize payouts.",
    "Spending Tips: Use a shopping list to avoid unnecessary spending.",
    "Spending Tips: Buy used or refurbished items when possible.",
    "Spending Tips: Avoid emotional spending by setting spending limits.",
    "Spending Tips: Wait for end-of-season sales for clothing or holiday items.",
    "Spending Tips: Unsubscribe from marketing emails that tempt you to spend.",
    "Budgeting and Saving Tips: Automate your savings to ensure consistency.",
    "Budgeting and Saving Tips: Create a detailed monthly budget and stick to it.",
    "Budgeting and Saving Tips: Review your subscriptions and cancel unnecessary ones.",
    "Budgeting and Saving Tips: Set aside an emergency fund with at least 3–6 months’ expenses.",
    "Budgeting and Saving Tips: Use cash instead of credit cards to limit spending.",
    "Budgeting and Saving Tips: Avoid lifestyle inflation when your income increases.",
];

// Randomly select a tip
$randomTip = $financialTips[array_rand($financialTips)];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Financial Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_design.css">
</head>
<body>
    <div class="main-container">
        <?php include 'sidebar.php'; ?>
        <div class="content">
            <header class="content-header">
                <h1><?php echo getTimeBasedGreeting(); ?>, 
                    <?php echo htmlspecialchars(isset($_SESSION['username']) ? ucfirst(strtolower($_SESSION['username'])) : 'User'); ?>!</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>
            <main class="content-body">
                <h2>Welcome to Home Page</h2>
                <p>This is the overview of your financial dashboard.</p>
                
                <!-- Random Financial Tip -->
                <div class="financial-tip">
                    <h3>Financial Tip of the Day</h3>
                    <p><?php echo $randomTip; ?></p>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
