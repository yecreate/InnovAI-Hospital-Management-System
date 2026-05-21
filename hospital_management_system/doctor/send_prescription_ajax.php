<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

session_start();
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Doctor') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$app_id = (int)($_POST['app_id'] ?? 0);
$doc_id = $_SESSION['Doctor_ID'];

// Fetch patient phone and info
$stmt = $conn->prepare("
    SELECT a.Date, a.Time, p.Fname as PFname, p.Lname as PLname, ph.PhoneNumber,
           d.Fname as DFname, d.Lname as DLname
    FROM Appointment a 
    JOIN Patient p ON a.Patient_ID = p.Patient_ID 
    LEFT JOIN Patient_phones ph ON p.Patient_ID = ph.Patient_ID 
    JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID 
    WHERE a.Appointment_ID = ? AND a.Doctor_ID = ? LIMIT 1
");
$stmt->bind_param("ii", $app_id, $doc_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data || empty($data['PhoneNumber'])) {
    die(json_encode(['status' => 'error', 'message' => 'Patient phone number not found.']));
}

$localFilePath = generate_prescription_pdf($app_id, $conn);

if ($localFilePath) {
    $doctor_name = "Dr. " . $data['DFname'] . " " . $data['DLname'];
    $message = "🏥 *InnovAI Medical Center*\n\nHello {$data['PFname']},\nAttached is the official clinical summary and prescription from your recent consultation with $doctor_name on " . date('M d, Y', strtotime($data['Date'])) . ".\n\nWe wish you a speedy recovery!";
    
    $cleanPhone = preg_replace('/[^0-9]/', '', $data['PhoneNumber']);
    if (substr($cleanPhone, 0, 2) !== '20') {
        $cleanPhone = (substr($cleanPhone, 0, 1) === '0') ? '20' . substr($cleanPhone, 1) : '20' . $cleanPhone;
    }

    $payload = [
        'number' => $cleanPhone,
        'message' => $message,
        'fileUrl' => $localFilePath,
        'fileType' => 'document'
    ];

    $ch_node = curl_init('http://localhost:3000/send-msg');
    curl_setopt($ch_node, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch_node, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch_node, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_node, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch_node);
    curl_close($ch_node);

    echo json_encode(['status' => 'success', 'message' => 'Prescription sent to WhatsApp!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate Prescription PDF.']);
}
?>