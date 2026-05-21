<?php
require_once '../includes/db_connection.php';
require_once '../includes/constants.php';
session_start();
if(!isset($_SESSION['Role'])) die('Unauthorized');

$app_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT a.*, p.Fname as PFname, p.Lname as PLname, d.Fname as DFname, d.Lname as DLname, d.Specialization, r.Room_ID 
    FROM Appointment a 
    JOIN Patient p ON a.Patient_ID = p.Patient_ID 
    JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID 
    LEFT JOIN Room r ON a.Room_ID = r.Room_ID
    WHERE a.Appointment_ID = ?
");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) die('Appointment not found.');
$app = $result->fetch_assoc();

// QR Code Generation
$qr_temp_dir = '../assets/qr_temp/';
if (!file_exists($qr_temp_dir)) {
    mkdir($qr_temp_dir, 0777, true);
}
$qr_filename = $qr_temp_dir . 'app_' . $app_id . '.png';

if (file_exists('../includes/phpqrcode/qrlib.php')) {
    require_once('../includes/phpqrcode/qrlib.php');
    // Generate QR code containing exactly the Appointment ID for the scanner to read
    QRcode::png((string)$app_id, $qr_filename, QR_ECLEVEL_L, 4);
} else {
    // Fallback if library missing
    $qr_filename = null;
}

if (file_exists('../includes/fpdf/fpdf.php')) {
    require('../includes/fpdf/fpdf.php');
} else {
    die("FPDF library missing. Please install it in /includes/fpdf/");
}

class PDF_Slip extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, APP_NAME, 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Patient Arrival Slip', 0, 1, 'C');
        $this->Ln(3);
        $this->Line(10, $this->GetY(), 90, $this->GetY());
        $this->Ln(3);
    }
}

$pdf = new PDF_Slip();
// Disable auto page break to prevent unnecessary extra pages for slip size
$pdf->SetAutoPageBreak(false);
$pdf->AddPage('P', array(100, 150)); 
$pdf->SetMargins(10, 10, 10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Appointment #: ' . $app['Appointment_ID'], 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Date: ' . date('M d, Y', strtotime($app['Date'])), 0, 1, 'C');
$pdf->Cell(0, 6, 'Time: ' . date('h:i A', strtotime($app['Time'])), 0, 1, 'C');

$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'Patient:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, htmlspecialchars($app['PFname'] . ' ' . $app['PLname']), 0, 1, 'L');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'Doctor:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Dr. ' . htmlspecialchars($app['DFname'] . ' ' . $app['DLname']) . ' (' . htmlspecialchars($app['Specialization']) . ')', 0, 1, 'L');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'Location:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Room ' . ($app['Room_ID'] ? $app['Room_ID'] : 'TBD (Check at Reception)'), 0, 1, 'L');

if ($qr_filename && file_exists($qr_filename)) {
    $pdf->Ln(4);
    // Center the QR code (100mm width - 35mm image width = 65mm / 2 = 32.5mm margin)
    $pdf->Image($qr_filename, 32.5, $pdf->GetY(), 35, 35);
    $pdf->SetY($pdf->GetY() + 37);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 4, 'Please present this QR code', 0, 1, 'C');
    $pdf->Cell(0, 4, 'to the scanner upon arrival.', 0, 1, 'C');
} else {
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, '[ QR Code Library Missing ]', 0, 1, 'C');
}

$pdf->Output('I', 'Slip_'.$app['Appointment_ID'].'.pdf');
?>