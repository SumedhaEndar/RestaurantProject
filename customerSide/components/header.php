<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

// Initialize variables for member info if logged in
$member_name = '';
$points = 0;
$vip_status = '';
$vip_tooltip = '';

$account_id = $_SESSION['account_id'] ?? null;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $account_id) {
    $stmt = $link->prepare("SELECT member_name, points FROM Memberships WHERE account_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $member_name = $row['member_name'];
            $points = (int)$row['points'];
            if ($points >= 1000) {
                $vip_status = 'VIP';
            } else {
                $vip_status = 'Regular';
                $vip_tooltip = (1000 - $points) . ' points to VIP';
            }
        }
        $stmt->close();
    }
}

// Define current URL for menu logic
$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Johnny's</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <!-- Other external CSS libs can go here -->
</head>

<body>
    <!-- Header -->
    <section id="header">
        <div class="header container">
            <div class="nav-bar">
                <div class="brand">
                    <a class="nav-link" href="../home/home.php#hero">
                        <h1 class="text-center" style="font-family:Copperplate; color:whitesmoke;"> JOHNNY'S</h1>
                        <span class="sr-only"></span>
                    </a>
                </div>
                <div class="nav-list">
                    <div class="hamburger"><div class="bar"></div></div>
                    <div class="navbar-container">
                        <div class="navbar">
                            <ul>
                                <li>
                                    <a href="<?= strpos($current_url, "localhost/customerSide/home/home.php") !== false ? "#hero" : "/customerSide/home/home.php" ?>" data-after="Home">Home</a>
                                </li>

                                <?php if (strpos($current_url, "localhost/customerSide/home/home.php") !== false): ?>
                                    <li><a href="#projects" data-after="Projects">Menu</a></li>
                                    <li><a href="#about" data-after="About">About</a></li>
                                    <li><a href="#contact" data-after="Contact">Contact</a></li>
                                <?php else: ?>
                                    <li><a href="../CustomerReservation/reservePage.php" data-after="Service">Reservation</a></li>
                                    <li><a href="../../adminSide/StaffLogin/login.php" data-after="Staff">Staff</a></li>
                                <?php endif; ?>

                                <div class="dropdown">
                                    <button class="dropbtn">ACCOUNT <i class="fa fa-caret-down" aria-hidden="true"></i></button>
                                    <div class="dropdown-content">
                                        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $account_id): ?>
                                            <p class="logout-link" style="font-size:1.3em; margin-left:15px; padding:5px; color:white;">
                                                <?= htmlspecialchars($member_name) ?>
                                            </p>
                                            <p class="logout-link" style="font-size:1.3em; margin-left:15px; padding:5px; color:white;">
                                                <?= htmlspecialchars($points) ?> Points
                                            </p>
                                            <p class="logout-link" style="font-size:1.3em; margin-left:15px; padding:5px; color:white;">
                                                <?= htmlspecialchars($vip_status) ?>
                                                <?php if ($vip_status === 'Regular'): ?>
                                                    <span class="tooltip"><?= htmlspecialchars($vip_tooltip) ?></span>
                                                <?php endif; ?>
                                            </p>
                                            <a class="logout-link" style="color: white; font-size:1.3em;" href="../customerLogin/logout.php">Logout</a>
                                        <?php else: ?>
                                            <a class="signin-link" style="color: white; font-size:15px;" href="../customerLogin/register.php">Sign Up</a>
                                            <a class="login-link" style="color: white; font-size:15px;" href="../customerLogin/login.php">Log In</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
