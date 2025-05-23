<?php
$reservation_id = $_GET['reservation_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h2>Reservation Successful!</h2>
    <p>Click the button below to download your receipt.</p>
    <button onclick="downloadAndRedirect()">Download Receipt</button>

    <script>
        function downloadAndRedirect() {
            window.open("reservationReceipt.php?reservation_id=<?= $reservation_id ?>", "_blank");

            setTimeout(function () {
                window.location.href = "reservePage.php";
            }, 1500);
        }
    </script>
</body>
</html>
