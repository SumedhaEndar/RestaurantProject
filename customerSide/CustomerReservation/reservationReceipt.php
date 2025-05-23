<?php
require('../../adminSide/posBackend/fpdf186/fpdf.php');
require_once '../config.php';
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

$reservation_id = $_GET['reservation_id'] ?? 1;

// Function to fetch reservation information by reservation ID
function getReservationInfoById($link, $reservation_id) {
    $query = "SELECT * FROM Reservations WHERE reservation_id='$reservation_id'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Fetch reservation information based on the reservation ID
$reservationInfo = getReservationInfoById($link, $reservation_id);

if ($reservationInfo) {
    // Create a PDF using FPDF
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 20);
            $this->Cell(0, 10, "JOHNNY'S DINING & BAR", 0, 1, 'C');
            $this->Ln(10);
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, 'Reservation Information', 1, 1, 'C');
        }
    }

    // Generate the PDF
    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    $pdf->Cell(40, 10, 'Reservation ID:', 1);
    $pdf->Cell(150, 10, $reservationInfo['reservation_id'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Customer Name:', 1);
    $pdf->Cell(150, 10, $reservationInfo['customer_name'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Table ID:', 1);
    $pdf->Cell(150, 10, $reservationInfo['table_id'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Reservation Time:', 1);
    $pdf->Cell(150, 10, $reservationInfo['reservation_time'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Reservation Date:', 1);
    $pdf->Cell(150, 10, $reservationInfo['reservation_date'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Head Count:', 1);
    $pdf->Cell(150, 10, $reservationInfo['head_count'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Special Request:', 1);
    $pdf->MultiCell(150, 10, $reservationInfo['special_request'], 1);
    $pdf->Ln(10);

    // Save to temporary file
    $filename = 'Reservation-Copy-ID' . $reservationInfo['reservation_id'] . '.pdf';
    $filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
    $pdf->Output($filepath, 'F'); // Save PDF temporarily on server

    // Force download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filepath);

    // Cleanup temporary file
    unlink($filepath);

    // ✅ After download, auto-redirect back to reservePage
    echo "<script>
        setTimeout(function() {
            window.location.href = 'reservePage.php';
        }, 500);
    </script>";
    exit();
} else {
    echo '<p style="color:red; text-align:center; margin-top:40px;">Invalid reservation ID or reservation not found.</p>';
}
?>
