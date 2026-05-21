<?php
// Function to dynamically calculate age from Date of Birth
function calculate_age($dob) {
    $bday = new DateTime($dob);
    $today = new DateTime('today');
    $diff = $today->diff($bday);
    return $diff->y;
}

// Function to sanitize user inputs for security
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Generate physical PDF for background delivery
function generate_appointment_slip_pdf($app_id, $conn) {
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
    $app = $stmt->get_result()->fetch_assoc();
    if (!$app) return null;

    $base_dir = $_SERVER['DOCUMENT_ROOT'] . '/hospital_management_system';
    $qr_temp_dir = $base_dir . '/assets/qr_temp/';
    $slips_dir = $base_dir . '/patient/slips/';
    
    if (!file_exists($qr_temp_dir)) mkdir($qr_temp_dir, 0777, true);
    if (!file_exists($slips_dir)) mkdir($slips_dir, 0777, true);

    $qr_filename = $qr_temp_dir . 'app_' . $app_id . '.png';
    $pdf_filename = $slips_dir . 'slip_' . $app_id . '.pdf';

    if (file_exists($base_dir . '/includes/phpqrcode/qrlib.php')) {
        require_once($base_dir . '/includes/phpqrcode/qrlib.php');
        QRcode::png((string)$app_id, $qr_filename, QR_ECLEVEL_L, 4);
    } else {
        $qr_filename = null;
    }

    if (!class_exists('FPDF')) {
        if (file_exists($base_dir . '/includes/fpdf/fpdf.php')) {
            require_once($base_dir . '/includes/fpdf/fpdf.php');
        } else {
            return null;
        }
    }

    $pdf = new FPDF('P', 'mm', array(100, 150));
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();
    $pdf->SetMargins(10, 10, 10);

    // Header
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 8, 'InnovAI Medical Center', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Patient Arrival Slip', 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->Line(10, $pdf->GetY(), 90, $pdf->GetY());
    $pdf->Ln(3);

    // Body
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Appointment #: ' . $app['Appointment_ID'], 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Date: ' . date('M d, Y', strtotime($app['Date'])), 0, 1, 'C');
    $pdf->Cell(0, 6, 'Time: ' . date('h:i A', strtotime($app['Time'])), 0, 1, 'C');

    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Patient:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, $app['PFname'] . ' ' . $app['PLname'], 0, 1, 'L');

    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Doctor:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Dr. ' . $app['DFname'] . ' ' . $app['DLname'] . ' (' . $app['Specialization'] . ')', 0, 1, 'L');

    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Location:', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Room ' . ($app['Room_ID'] ? $app['Room_ID'] : 'TBD (Check at Reception)'), 0, 1, 'L');

    if ($qr_filename && file_exists($qr_filename)) {
        $pdf->Ln(4);
        $pdf->Image($qr_filename, 32.5, $pdf->GetY(), 35, 35);
        $pdf->SetY($pdf->GetY() + 37);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 4, 'Please present this QR code', 0, 1, 'C');
        $pdf->Cell(0, 4, 'to the scanner upon arrival.', 0, 1, 'C');
    }

    $pdf->Output('F', $pdf_filename);
    return $pdf_filename;
}

// Generate physical Health Passport PDF for background delivery
function generate_health_passport_pdf($patient_id, $conn) {
    // 1. Fetch Patient Info
    $stmt_p = $conn->prepare("SELECT Fname, Lname, Date_of_Birth, Age, Blood_Group FROM Patient WHERE Patient_ID = ?");
    $stmt_p->bind_param("i", $patient_id);
    $stmt_p->execute();
    $patient = $stmt_p->get_result()->fetch_assoc();
    if (!$patient) return null;
    $patient_name = $patient['Fname'] . ' ' . $patient['Lname'];

    // 2. Fetch Medical History
    $stmt_h = $conn->prepare("
        SELECT mh.Date, mh.Diagnosis, mh.Notes, mh.Treatment, d.Lname as DocLname, d.Specialization,
               GROUP_CONCAT(CONCAT(pr.Medications, ' (', pr.Dosage, ')') SEPARATOR ', ') as Prescriptions
        FROM Medical_History mh
        JOIN Doctor d ON mh.Doctor_ID = d.Doctor_ID
        LEFT JOIN Prescription pr ON mh.Appointment_ID = pr.Appointment_ID
        WHERE mh.Patient_ID = ?
        GROUP BY mh.Record_ID
        ORDER BY mh.Date DESC
    ");
    $stmt_h->bind_param("i", $patient_id);
    $stmt_h->execute();
    $history_result = $stmt_h->get_result();

    $history_records = [];
    $medical_data_text = "";
    while($row = $history_result->fetch_assoc()) {
        $history_records[] = $row;
        $medical_data_text .= "Date: {$row['Date']}. Diagnosis: {$row['Diagnosis']}. Treatment: {$row['Treatment']}. Prescriptions: {$row['Prescriptions']}. ";
    }

    // 3. Handle AI Summary
    require_once 'ai_engine.php';
    $stmt_ai_check = $conn->prepare("SELECT Analysis_ID, Summary_Text FROM AI_Analysis WHERE Patient_ID = ? ORDER BY Timestamp DESC LIMIT 1");
    $stmt_ai_check->bind_param("i", $patient_id);
    $stmt_ai_check->execute();
    $ai_row = $stmt_ai_check->get_result()->fetch_assoc();

    $summary_text = "No clinical history available to summarize.";
    if (!empty($history_records)) {
        if (!$ai_row || empty($ai_row['Summary_Text'])) {
            $summary_text = generate_medical_summary($patient_name, $medical_data_text);
            if ($ai_row) {
                $update_ai = $conn->prepare("UPDATE AI_Analysis SET Summary_Text = ? WHERE Analysis_ID = ?");
                $update_ai->bind_param("si", $summary_text, $ai_row['Analysis_ID']);
                $update_ai->execute();
            } else {
                $insert_ai = $conn->prepare("INSERT INTO AI_Analysis (Input_Symptoms, Patient_ID, Summary_Text) VALUES ('System generated history summary', ?, ?)");
                $insert_ai->bind_param("is", $patient_id, $summary_text);
                $insert_ai->execute();
            }
        } else {
            $summary_text = $ai_row['Summary_Text'];
        }
    }

    // 4. Generate PDF using FPDF
    if (!class_exists('FPDF')) {
        require_once 'fpdf/fpdf.php';
    }

    // Define a local class if not already defined (using standard FPDF for simplicity in shared function)
    $pdf = new FPDF();
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Header logic (Simplified for shared function)
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(0, 128, 128);
    $pdf->Cell(0, 10, 'InnovAI Medical Center', 0, 1, 'C');
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 8, 'Official Digital Health Passport', 0, 1, 'C');
    $pdf->Ln(5);

    // Patient Details
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(230, 240, 240);
    $pdf->Cell(0, 8, ' Patient Demographics', 0, 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, "Name: $patient_name | DOB: {$patient['Date_of_Birth']} (Age: {$patient['Age']}) | Blood: " . ($patient['Blood_Group'] ?: 'Unknown'), 0, 1);
    $pdf->Ln(5);

    // AI Summary
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(255, 245, 230);
    $pdf->Cell(0, 8, ' AI-Generated Medical Summary', 0, 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->MultiCell(0, 6, $summary_text);
    $pdf->Ln(8);

    // Records
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(230, 240, 240);
    $pdf->Cell(0, 8, ' Detailed Clinical History', 0, 1, 'L', true);
    $pdf->Ln(4);

    foreach($history_records as $record) {
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(0, 100, 100);
        $pdf->Cell(0, 6, $record['Date'] . ' | Dr. ' . $record['DocLname'], 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->MultiCell(0, 5, "Diagnosis: " . $record['Diagnosis'] . "\nTreatment: " . ($record['Treatment'] ?: 'N/A') . "\nPrescriptions: " . ($record['Prescriptions'] ?: 'None'));
        $pdf->Ln(3);
    }

    $slips_dir = $_SERVER['DOCUMENT_ROOT'] . '/hospital_management_system/patient/slips/';
    if (!file_exists($slips_dir)) mkdir($slips_dir, 0777, true);
    $pdf_filename = $slips_dir . 'passport_' . $patient_id . '.pdf';
    $pdf->Output('F', $pdf_filename);
    return $pdf_filename;
}

// Generate physical Prescription PDF for background delivery
function generate_prescription_pdf($app_id, $conn) {
    $stmt = $conn->prepare("
        SELECT a.Date, a.Diagnosis, p.Fname as PFname, p.Lname as PLname, p.Age, p.Gender,
               d.Fname as DFname, d.Lname as DLname, d.Specialization,
               mh.Notes, mh.Treatment
        FROM Appointment a
        JOIN Patient p ON a.Patient_ID = p.Patient_ID
        JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID
        LEFT JOIN Medical_History mh ON a.Appointment_ID = mh.Appointment_ID
        WHERE a.Appointment_ID = ?
    ");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if (!$data) return null;

    $stmt_presc = $conn->prepare("SELECT Medications, Dosage, Instructions FROM Prescription WHERE Appointment_ID = ?");
    $stmt_presc->bind_param("i", $app_id);
    $stmt_presc->execute();
    $prescriptions = $stmt_presc->get_result();

    if (!class_exists('FPDF')) {
        require_once 'fpdf/fpdf.php';
    }

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', 'B', 18);
    $pdf->SetTextColor(0, 128, 128);
    $pdf->Cell(0, 10, 'InnovAI Medical Center', 0, 1, 'C');
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 8, 'Clinical Consultation & Prescription', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, "Patient: {$data['PFname']} {$data['PLname']} | Age: {$data['Age']} | Gender: {$data['Gender']}", 0, 1);
    $pdf->Cell(0, 6, "Doctor: Dr. {$data['DFname']} {$data['DLname']} ({$data['Specialization']})", 0, 1);
    $pdf->Cell(0, 6, "Date: " . date('M d, Y', strtotime($data['Date'])), 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetFillColor(230, 240, 240);
    $pdf->Cell(0, 8, ' Clinical Findings', 0, 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(25, 6, 'Diagnosis:', 0, 0);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell(0, 6, $data['Diagnosis']);
    
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(25, 6, 'Notes:', 0, 0);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell(0, 6, $data['Notes'] ?: 'N/A');

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(25, 6, 'Treatment:', 0, 0);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell(0, 6, $data['Treatment'] ?: 'N/A');
    $pdf->Ln(5);

    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 8, ' Prescription details', 0, 1, 'L', true);
    $pdf->Ln(2);

    if ($prescriptions->num_rows > 0) {
        // Table Header
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(60, 8, 'Medication', 1);
        $pdf->Cell(40, 8, 'Dosage', 1);
        $pdf->Cell(90, 8, 'Instructions', 1);
        $pdf->Ln();

        $pdf->SetFont('Helvetica', '', 10);
        while ($row = $prescriptions->fetch_assoc()) {
            $pdf->Cell(60, 8, $row['Medications'], 1);
            $pdf->Cell(40, 8, $row['Dosage'], 1);
            $pdf->Cell(90, 8, $row['Instructions'], 1);
            $pdf->Ln();
        }
    } else {
        $pdf->SetFont('Helvetica', 'I', 10);
        $pdf->Cell(0, 6, 'No medications prescribed.', 0, 1);
    }
    
    $pdf->Ln(15);
    $pdf->SetFont('Helvetica', 'I', 8);
    $pdf->Cell(0, 4, 'Electronically generated by InnovAI Medical Center. Valid without signature.', 0, 1, 'C');

    $slips_dir = $_SERVER['DOCUMENT_ROOT'] . '/hospital_management_system/patient/slips/';
    if (!file_exists($slips_dir)) mkdir($slips_dir, 0777, true);
    
    $pdf_filename = $slips_dir . 'prescription_' . $app_id . '.pdf';
    $pdf->Output('F', $pdf_filename);
    
    return $pdf_filename;
}
?>