<?php
require_once '../includes/db_connection.php';
require_once '../includes/constants.php';
session_start();
if(!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Admin', 'Receptionist'])) die('Unauthorized');

$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT b.Bill_ID, b.Amount, b.Payment_Date, b.Payment_Method, 
           CONCAT(p.Fname, ' ', p.Lname) AS Patient_Name,
           r.Full_Name AS Receptionist_Name
    FROM Billing b
    JOIN Patient p ON b.Patient_ID = p.Patient_ID
    LEFT JOIN Receptionist r ON b.Receptionist_ID = r.Receptionist_ID
    WHERE b.Payment_Status = 'Paid' AND b.Payment_Date BETWEEN ? AND ?
    ORDER BY b.Payment_Date ASC
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$transactions = $stmt->get_result();

if (file_exists('../includes/fpdf/fpdf.php')) {
    require('../includes/fpdf/fpdf.php');
} else {
    die("FPDF library missing.");
}

class PDF_Financial extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'InnovAI Medical Center', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Financial Report', 0, 1, 'C');
        $this->Ln(5);
        $this->Cell(0, 0, '', 'T'); // Horizontal line
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->PageNo().' - Generated: '.date('Y-m-d H:i:s').' | InnovAI Medical Center - Official Document', 0, 0, 'C');
    }
}

$pdf = new PDF_Financial();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Period: $start_date to $end_date", 0, 1);
$pdf->Ln(5);

$pdf->SetFillColor(200, 220, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 10, 'Bill ID', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Patient Name', 1, 0, 'L', true);
$pdf->Cell(40, 10, 'Method', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Amount (EGP)', 1, 1, 'R', true);

$pdf->SetFont('Arial', '', 10);
$total = 0;

while ($row = $transactions->fetch_assoc()) {
    // Check if we need a new page
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        $pdf->SetFillColor(200, 220, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 10, 'Bill ID', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
        $pdf->Cell(60, 10, 'Patient Name', 1, 0, 'L', true);
        $pdf->Cell(40, 10, 'Method', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Amount (EGP)', 1, 1, 'R', true);
        $pdf->SetFont('Arial', '', 10);
    }
    
    $pdf->Cell(20, 10, $row['Bill_ID'], 1, 0, 'C');
    $pdf->Cell(30, 10, $row['Payment_Date'], 1, 0, 'C');
    $pdf->Cell(60, 10, $row['Patient_Name'], 1, 0, 'L');
    $pdf->Cell(40, 10, $row['Payment_Method'], 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($row['Amount'], 2), 1, 1, 'R');
    
    $total += $row['Amount'];
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Total Revenue:', 0, 0, 'R');
$pdf->Cell(40, 10, number_format($total, 2) . ' EGP', 1, 1, 'R');

$pdf->Output('I', 'Financial_Report_'.$start_date.'_to_'.$end_date.'.pdf');
?>