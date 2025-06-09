<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Seating</title>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5 text-center">
    <?php
    require_once '../config.php';

    date_default_timezone_set('Asia/Kuala_Lumpur');

    if (isset($_GET['new_customer']) && $_GET['new_customer'] === 'true') {
        $table_id = $_GET['table_id'];
        $bill_time = date('Y-m-d H:i:s');
        $today_date = date('Y-m-d');
        $now = date('H:i:s');

        $insertQuery = "INSERT INTO Bills (table_id, bill_time) VALUES ('$table_id', '$bill_time')";
        
        if ($link->query($insertQuery) === TRUE) {
            $bill_id = $link->insert_id;

            // Step 2: Find the nearest reservation for today at this table
            $findReservation = "SELECT reservation_id
                FROM Reservations
                WHERE table_id = '$table_id'
                AND reservation_date = '$today_date'
                AND attended IS NULL
                AND reservation_time <= '$now'
                ORDER BY ABS(TIMESTAMPDIFF(SECOND, reservation_time, '$now')) ASC
                LIMIT 1
            ";

            $result = $link->query($findReservation);

            if ($result && $row = $result->fetch_assoc()) {
                $reservationId = $row['reservation_id'];
                // echo "<p style='color:green;'> Marking reservation $reservationId as attended.</p>";

                $update = "UPDATE Reservations SET attended = TRUE WHERE reservation_id = $reservationId";
                $link->query($update);
            } 

            // Confirmation content
            echo "<h2>Johnny's Restaurant</h2>";
            echo "<p>You're now seated at Table ID: $table_id</p>";
            echo "<p>Your bill has been created with Bill ID: $bill_id</p>";
            echo '<a href="orderItem.php?bill_id=' . $bill_id . '&table_id=' . $table_id . '" class="btn btn-primary">Back</a>';
        } else {
            echo "<div class='alert alert-danger'>Error inserting data into Bills table: " . $link->error . "</div>";
        }
    }
    ?>

</div>

<!-- Add Bootstrap JS and jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
