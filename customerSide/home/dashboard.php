<?php
session_start();
require_once '../../config.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../customerLogin/login.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch the account details
$sql_customer = "SELECT member_name, loyalty_points FROM Memberships WHERE account_id = ?";
$stmt = mysqli_prepare($link, $sql_customer);
mysqli_stmt_bind_param($stmt, "i", $account_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);

//Fetch recent orders
$sql_orders = "SELECT order_id, order_date, status FROM Orders WHERE account_id = ? ORDER BY order_date DESC LIMIT 5";
$stmt2 = mysqli_prepare($link, $sql_orders);
mysqli_stmt_bind_param($stmt2, "i", $account_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$orders = mysqli_fetch_all($result2, MYSQLI_ASSOC);

include('../components/header.php');
?>

<main>
    <h1>Welcome, <?php echo htmlspecialchars($customer['member_name']); ?>!</h1>
    <p>Your Loyalty Points: <strong><?php echo htmlspecialchars($customer['loyalty_points']); ?></strong></p>

    <section>
        <h2>Recent Orders</h2>
        <?php if (count($orders) > 0): ?>
            <p>You have no recent orders.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($orders as $order): ?>
                    <li>
                        Order #<?php echo $order['order_id']; ?> -
                        <?php echo date ("d M Y", strtotime($order['order_date'])); ?> -
                        Status: <?php echo htmlspecialchars($order['status']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

</body>
</html>