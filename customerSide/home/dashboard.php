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

$customer_name = $member['member_name'] ?? null;
if (!$customer_name) {
    die("Customer name not found.");
}

$member_id = $member['member_id'];

// Fetch recent bills/orders
$stmt2 = $link->prepare("SELECT bill_id, bill_time, payment_method FROM Bills WHERE member_id = ? ORDER BY bill_time DESC LIMIT 5");
$stmt2->bind_param("i", $member_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$bills = $result2->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// Fetch reservation history
var_dump($customer_name);

$stmt3 = $link->prepare("SELECT reservation_id, customer_name, table_id, reservation_time, reservation_date, head_count, special_request FROM Reservations ORDER BY reservation_date DESC, reservation_time DESC LIMIT 5");
$stmt3->bind_param("i", $customer_name);
$stmt3->execute();
$result3 = $stmt3->get_result();
$reservations = $result3->fetch_all(MYSQLI_ASSOC);
$stmt3->close();


include('../components/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Johnny's</title>
    
    <link rel="stylesheet" href="../css/dashboard.css" />
</head>
<body class="dashboard-page">

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
<section>
  <h2>Your Reservation History</h2>
  <?php if (empty($reservations)): ?>
    <p>No reservation history found.</p>
  <?php else: ?>
    <ul>
    <?php foreach ($reservations as $res): ?>
      <li>Reservation #<span style="<?= ($res['customer_name'] === $member_name) ? 'font-weight:bold; color:crimson;' : '' ?>">
        <?= htmlspecialchars($res['reservation_id']); ?>
    </span></h3>
        <p><span>Date:</span> <?= htmlspecialchars($res['reservation_date']); ?></p>
        <p><span>Time:</span> <?= htmlspecialchars($res['reservation_time']); ?></p>
        <p><span>Table:</span> <?= htmlspecialchars($res['table_id']); ?></p>
        <p><span>Guests:</span> <?= htmlspecialchars($res['head_count']); ?></p>
        <p><span>Special Request:</span> <?= htmlspecialchars($res['special_request']); ?></p>
    </li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

</body>
</html>
