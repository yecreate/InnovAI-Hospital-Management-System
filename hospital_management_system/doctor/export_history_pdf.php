<?php
require_once '../includes/db_connection.php';
require_once '../includes/constants.php';
session_start();
if(!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Doctor'])) die('Unauthorized');

$patient_id = $_GET['patient_id'] ?? 0;
$doc_id = $_SESSION['Doctor_ID'];

// Validate doctor has seen this patient (or just allow if they are a doctor)
// For simplicity, we allow any doctor to view any patient's history in the system.
$p_stmt = $conn->prepare("SELECT Fname, Lname, Age, Gender, Blood_Group FROM Patient WHERE Patient_ID = ?");
$p_stmt->bind_param("i", $patient_id);
$p_stmt->execute();
$patient = $p_stmt->get_result()->fetch_assoc();

if (!$patient) die('Patient not found.');

$mh_stmt = $conn->prepare("
    SELECT mh.Date, mh.Diagnosis, mh.Treatment, mh.Notes, d.Fname, d.Lname 
    FROM Medical_History mh
    LEFT JOIN Doctor d ON mh.Doctor_ID = d.Doctor_ID
    WHERE mh.Patient_ID = ?
    ORDER BY mh.Date DESC
");
$mh_stmt->bind_param("i", $patient_id);
$mh_stmt->execute();
$history = $mh_stmt->get_result();

$pr_stmt = $conn->prepare("
    SELECT p.Medications, p.Dosage, p.Instructions, a.Date, d.Fname, d.Lname
    FROM Prescription p
    JOIN Appointment a ON p.Appointment_ID = a.Appointment_ID
    LEFT JOIN Doctor d ON p.Doctor_ID = d.Doctor_ID
    WHERE a.Patient_ID = ?
    ORDER BY a.Date DESC
");
$pr_stmt->bind_param("i", $patient_id);
$pr_stmt->execute();
$prescriptions = $pr_stmt->get_result();

if (file_exists('../includes/fpdf/fpdf.php')) {
    require('../includes/fpdf/fpdf.php');
} else {
    die("FPDF library missing.");
}

class PDF_Medical_History extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, APP_NAME, 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Comprehensive Medical History & Prescriptions', 0, 1, 'C');
        $this->Ln(5);
        $this->Cell(0, 0, '', 'T'); // Horizontal line
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->PageNo().' - Generated: '.date('Y-m-d H:i:s'), 0, 0, 'C');
    }
}

$pdf = new PDF_Medical_History();
$pdf->AddPage();

// Patient Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Patient Profile", 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, "Name:", 0, 0);
$pdf->Cell(0, 8, htmlspecialchars($patient['Fname'] . ' ' . $patient['Lname']), 0, 1);
$pdf->Cell(40, 8, "Age/Gender:", 0, 0);
$pdf->Cell(0, 8, $patient['Age'] . " / " . $patient['Gender'], 0, 1);
$pdf->Cell(40, 8, "Blood Group:", 0, 0);
$pdf->Cell(0, 8, $patient['Blood_Group'] ?: 'Unknown', 0, 1);
$pdf->Ln(5);

// Medical History Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Medical History", 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(25, 8, 'Date', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Diagnosis', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Treatment', 1, 0, 'C', true);
$pdf->Cell(65, 8, 'Doctor', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
while ($h = $history->fetch_assoc()) {
    $y = $pdf->GetY();
    $x = $pdf->GetX();
    $pdf->MultiCell(25, 6, $h['Date'], 1, 'C');
    $y2 = $pdf->GetY();
    $h1 = $y2 - $y;
    
    $pdf->SetXY($x + 25, $y);
    $pdf->MultiCell(50, 6, htmlspecialchars($h['Diagnosis']), 1, 'L');
    $y3 = $pdf->GetY();
    $h2 = $y3 - $y;

    $pdf->SetXY($x + 75, $y);
    $pdf->MultiCell(50, 6, htmlspecialchars($h['Treatment']), 1, 'L');
    $y4 = $pdf->GetY();
    $h3 = $y4 - $y;

    $pdf->SetXY($x + 125, $y);
    $pdf->MultiCell(65, 6, "Dr. " . htmlspecialchars($h['Fname'] . ' ' . $h['Lname']), 1, 'L');
    $y5 = $pdf->GetY();
    $h4 = $y5 - $y;

    $maxH = max($h1, $h2, $h3, $h4);
    $pdf->SetY($y + $maxH);
}
$pdf->Ln(5);

// Prescriptions Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Prescriptions", 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 8, 'Date', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Medication', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Dosage', 1, 0, 'C', true);
$pdf->Cell(65, 8, 'Instructions', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
while ($pr = $prescriptions->fetch_assoc()) {
    $y = $pdf->GetY();
    $x = $pdf->GetX();
    $pdf->MultiCell(25, 6, $pr['Date'], 1, 'C');
    $y2 = $pdf->GetY();
    $h1 = $y2 - $y;
    
    $pdf->SetXY($x + 25, $y);
    $pdf->MultiCell(50, 6, htmlspecialchars($pr['Medications']), 1, 'L');
    $y3 = $pdf->GetY();
    $h2 = $y3 - $y;

    $pdf->SetXY($x + 75, $y);
    $pdf->MultiCell(50, 6, htmlspecialchars($pr['Dosage']), 1, 'L');
    $y4 = $pdf->GetY();
    $h3 = $y4 - $y;

    $pdf->SetXY($x + 125, $y);
    $pdf->MultiCell(65, 6, htmlspecialchars($pr['Instructions']), 1, 'L');
    $y5 = $pdf->GetY();
    $h4 = $y5 - $y;

    $maxH = max($h1, $h2, $h3, $h4);
    $pdf->SetY($y + $maxH);
}

$pdf->Output('I', 'Medical_History_Patient_'.$patient_id.'.pdf');
?>