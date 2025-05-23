<?php
require_once '../config.php';
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Customer Reservation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
    <style>
    body {
        font-family: 'Montserrat', sans-serif;
        background-color: rgb(37, 42, 52);
        display: flex;
        color: white;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .reserve-container {
        max-width: 100%; /* 更宽一点防止被压扁 */
    }
    .row {
        display: flex;
        flex-wrap: wrap;
    }
    .column {
        padding: 10px;
        width: 36.4em;
    }
</style>

</head>
<body>

<?php
$reservationStatus = $_GET['reservation'] ?? null;
if ($reservationStatus === 'success') {
    $reservation_id = $_GET['reservation_id'] ?? null;
    echo '<script>alert("Table Successfully Reserved. Click OK to view your reservation receipt."); window.location.href = "reservationReceipt.php?reservation_id=' . $reservation_id . '";</script>';
}

$reservation_date = $_GET['reservation_date'] ?? date("Y-m-d");
$reservation_time = $_GET['reservation_time'] ?? "";
$head_count = $_GET['head_count'] ?? 1;
?>

<div class="reserve-container">
    <a class="nav-link" href="../home/home.php#hero">
        <h1 class="text-center" style="font-family: Copperplate; color: whitesmoke;">JOHNNY'S</h1>
    </a>

    <div class="row">
        <div class="column">
            <div id="Search Table">
                <h2 style="color:white;">Search for Time</h2>
                <form id="reservation-form"><br>
                    <div class="form-group">
                        <label for="reservation_date">Select Date</label><br>
                        <input class="form-control" type="date" id="reservation_date" name="reservation_date" required
                          min="<?= date('Y-m-d') ?>" value="<?= $reservation_date ?>">
                    </div>
                    <div class="form-group">
                        <label for="reservation_time">Available Reservation Times</label>
                        <div id="availability-table">
                            <select name="reservation_time" id="reservation_time" style="width:10em;" class="form-control" required>
                                <option value="" disabled <?= $reservation_time == "" ? 'selected' : '' ?>>Select a Time</option>
                                <?php
                               for ($hour = 10; $hour <= 20; $hour++) {
                                    $value = sprintf('%02d:00:00', $hour); 
                                    $display = date("g:i A", strtotime($value)); 

                                    $now = new DateTime();
                                    $currentDate = $now->format('Y-m-d');
                                    $timeToCheck = new DateTime("$reservation_date $value");

                                    if ($reservation_date == $currentDate && $timeToCheck <= $now) {
                                        continue; 
                                    }

                                    $selected = ($value == $reservation_time) ? 'selected' : '';
                                    echo "<option value='$value' $selected>$display</option>";
                                }

                                ?>
                            </select>
                        </div>
                    </div>
                    <input type="number" id="head_count" name="head_count" value="<?= $head_count ?>" hidden required>
                </form>
            </div>
        </div>

        <div class="column right-column">
            <div id="insert-reservation-into-table">
                <h2 style="color:white;">Make Reservation</h2>
                <form method="POST" action="insertReservation.php">
                    <br>
                    <div class="form-group">
                        <label for="customer_name">Customer Name</label><br>
                        <input class="form-control" type="text" id="customer_name" name="customer_name" required>
                    </div>

                    <div class="form-group">
                        <label for="reservation_date">Reservation Date</label><br>
                        <input type="date" id="reservation_date_display" name="reservation_date" value="<?= $reservation_date ?>" readonly required>
                        <input type="time" id="reservation_time_display" name="reservation_time" value="<?= $reservation_time ?>" readonly required>
                    </div>

                    <div class="form-group">
                        <label for="table_id_reserve">Available Tables</label>
                        <select class="form-control" name="table_id" id="table_id_reserve" style="width:10em;" required>
                            <option value="" selected disabled>Select a Table</option>
                            <?php
                            if ($reservation_date && $reservation_time) {
                                $query = "
                                    SELECT * FROM restaurant_tables
                                    WHERE capacity >= '$head_count'
                                    AND table_id NOT IN (
                                        SELECT table_id FROM reservations
                                        WHERE reservation_date = '$reservation_date'
                                        AND reservation_time = '$reservation_time'
                                    )
                                ";
                                $result_tables = mysqli_query($link, $query);
                                $resultCheckTables = mysqli_num_rows($result_tables);

                                if ($resultCheckTables > 0) {
                                    while ($row = mysqli_fetch_assoc($result_tables)) {
                                        echo '<option value="' . $row['table_id'] . '">For ' . $row['capacity'] . ' people. (Table Id: ' . $row['table_id'] . ')</option>';
                                    }
                                } else {
                                    echo '<option disabled>No tables available, please choose another time.</option>';
                                    echo '<script>alert("No reservation tables found for the selected time. Please choose another time.");</script>';
                                }
                            }
                            ?>
                        </select>
                        <input type="number" id="head_count" name="head_count" value="<?= $head_count ?>" required hidden>
                    </div>

                    <div class="form-group mb-3">
                        <label for="special_request">Special request</label><br>
                        <textarea class="form-control" id="special_request" name="special_request"></textarea><br>
                        <button type="submit" class="btn" style="background-color: black; color: rgb(234, 234, 234);" name="submit">Make Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const dateInput = document.getElementById("reservation_date");
    const timeInput = document.getElementById("reservation_time");
    const headCount = document.getElementById("head_count").value;

    function autoUpdateReservation() {
        if (dateInput.value && timeInput.value) {
            const date = encodeURIComponent(dateInput.value);
            const time = encodeURIComponent(timeInput.value);
            window.location.href = `reservePage.php?reservation_date=${date}&reservation_time=${time}&head_count=${headCount}`;
        }
    }

    dateInput.addEventListener("change", autoUpdateReservation);
    timeInput.addEventListener("change", autoUpdateReservation);
</script>

</body>
</html>
