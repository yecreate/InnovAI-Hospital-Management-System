<?php
require_once '../includes/db_connection.php';
require_once '../includes/constants.php';
session_start();
if(!isset($_SESSION['Role']) || ($_SESSION['Role'] !== 'Receptionist' && $_SESSION['Role'] !== 'Admin')) die('Unauthorized');

$bill_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("
    SELECT b.*, p.Fname, p.Lname, p.City 
    FROM Billing b 
    JOIN Patient p ON b.Patient_ID = p.Patient_ID 
    WHERE b.Bill_ID = ?
");
$stmt->bind_param("i", $bill_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) die('Bill not found.');
$bill = $result->fetch_assoc();

// Assuming FPDF is available as instructed
if (file_exists('../includes/fpdf/fpdf.php')) {
    require('../includes/fpdf/fpdf.php');
} else {
    die("FPDF library missing. Please install it in /includes/fpdf/ to view this receipt.");
}

class PDF_Bill extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, APP_NAME, 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Official Medical Invoice', 0, 1, 'C');
        $this->Ln(10);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Thank you for trusting us with your healthcare.', 0, 0, 'C');
    }
    function Watermark($text) {
        $this->SetFont('Arial', 'B', 60);
        $this->SetTextColor(230, 230, 230); // Light gray
        $this->Text(40, 150, $text);
        $this->SetTextColor(0, 0, 0); // Reset text color
    }
}

$pdf = new PDF_Bill();
$pdf->AddPage();

if ($bill['Payment_Status'] === 'Paid') {
    $pdf->Watermark('PAID IN FULL');
}

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(100, 10, 'Bill To:', 0, 0);
$pdf->Cell(90, 10, 'Invoice Details:', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 8, $bill['Fname'] . ' ' . $bill['Lname'], 0, 0);
$pdf->Cell(90, 8, 'Invoice #: ' . $bill['Bill_ID'], 0, 1);
$pdf->Cell(100, 8, 'Location: ' . ($bill['City'] ? $bill['City'] : 'N/A'), 0, 0);
$pdf->Cell(90, 8, 'Date: ' . ($bill['Payment_Date'] ? $bill['Payment_Date'] : date('Y-m-d')), 0, 1);
$pdf->Ln(15);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(130, 10, 'Description', 1, 0, 'L', true);
$pdf->Cell(60, 10, 'Amount', 1, 1, 'R', true);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(130, 10, 'Medical Consultation & Services', 1, 0, 'L');
$pdf->Cell(60, 10, 'EGP ' . number_format($bill['Amount'], 2), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'Total Due', 0, 0, 'R');
$pdf->Cell(60, 10, 'EGP ' . number_format($bill['Amount'], 2), 1, 1, 'R');

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Payment Status: ' . $bill['Payment_Status'], 0, 1);
$pdf->Cell(0, 10, 'Payment Method: ' . ($bill['Payment_Method'] ? $bill['Payment_Method'] : 'N/A'), 0, 1);

$pdf->Output('I', 'Bill_'.$bill['Bill_ID'].'.pdf');
?>