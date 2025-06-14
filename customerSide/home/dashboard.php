<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../config.php';  // Ensure this is at the very top, and $link is valid


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../customerLogin/login.php");
    exit();
}

$account_id = $_SESSION['account_id'] ?? null;
if (!$account_id) {
    die("Account ID not found in session.");
}

// Fetch membership details
$stmt = $link->prepare("SELECT member_id, member_name, points FROM Memberships WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    die("Membership info not found.");
}

$member_id = $member['member_id'];

// Fetch recent bills/orders
$stmt2 = $link->prepare("SELECT bill_id, bill_time, payment_method FROM Bills WHERE member_id = ? ORDER BY bill_time DESC LIMIT 5");
$stmt2->bind_param("i", $member_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$bills = $result2->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

include('../components/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Johnny's</title>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
</head>
<body>

<main>
    <h1>Welcome, <?php echo htmlspecialchars($member['member_name']); ?>!</h1>
    <p>Your Loyalty Points: <strong><?php echo htmlspecialchars($member['points']); ?></strong></p>

    <section>
        <h2>Recent Orders</h2>
        <?php if (empty($bills)): ?>
            <p>You have no recent orders.</p>
        <?php else: ?>
            <?php foreach ($bills as $bill): ?>
                <div class="order">
                    <h3>Order #<?php echo $bill['bill_id']; ?> - 
                        <?php echo date("d M Y H:i", strtotime($bill['bill_time'])); ?> 
                        (Payment: <?php echo htmlspecialchars($bill['payment_method']); ?>)
                    </h3>
                    <ul>
                        <?php
                        $stmt3 = $link->prepare("SELECT Menu.item_name, Bill_Items.quantity FROM Bill_Items JOIN Menu ON Bill_Items.item_id = Menu.item_id WHERE Bill_Items.bill_id = ?");
                        if (!$stmt3) {
                            echo "<li>Error preparing statement for bill items.</li>";
                        } else {
                            $stmt3->bind_param("i", $bill['bill_id']);
                            $stmt3->execute();
                            $result3 = $stmt3->get_result();
                            $items = $result3->fetch_all(MYSQLI_ASSOC);
                            $stmt3->close();

                            if (empty($items)) {
                                echo "<li>No items found for this order.</li>";
                            } else {
                                foreach ($items as $item) {
                                    echo "<li>" . htmlspecialchars($item['item_name']) . " x " . htmlspecialchars($item['quantity']) . "</li>";
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
