<?php
require_once '../config.php';
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST["customer_name"];
    $table_id = intval($_POST["table_id"]);
    $reservation_time = $_POST["reservation_time"];
    $reservation_date = $_POST["reservation_date"];
    $special_request = $_POST["special_request"];

    $select_query_capacity = "SELECT capacity FROM restaurant_tables WHERE table_id='$table_id';";
    $results_capacity = mysqli_query($link, $select_query_capacity);

    if ($results_capacity) {
        $row = mysqli_fetch_assoc($results_capacity);
        $head_count = $row['capacity'];

        $insert_query1 = "INSERT INTO Reservations (customer_name, table_id, reservation_time, reservation_date, head_count, special_request) 
                          VALUES ('$customer_name', '$table_id', '$reservation_time', '$reservation_date', '$head_count', '$special_request');";
        
        if (mysqli_query($link, $insert_query1)) {
            $reservation_id = mysqli_insert_id($link);

                $insert_query2 = "INSERT INTO Table_Availability (table_id, reservation_date, reservation_time, status) 
                              VALUES ('$table_id', '$reservation_date', '$reservation_time', 'no');";
            mysqli_query($link, $insert_query2);

            $_SESSION['customer_name'] = $customer_name;
           header("Location: reservationRedirect.php?reservation_id=$reservation_id");
            exit();
        } else {
            echo "Error inserting reservation: " . mysqli_error($link);
        }
    } else {
        echo "Error fetching table capacity: " . mysqli_error($link);
    }
}
?>
