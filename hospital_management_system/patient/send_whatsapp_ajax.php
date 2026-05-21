<?php
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';
require_once '../includes/whatsapp_helper.php';

session_start();
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Patient') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$patient_id = $_SESSION['Patient_ID'];

// Fetch patient phone and name
$stmt_p = $conn->prepare("SELECT p.Fname, p.Lname, ph.PhoneNumber FROM Patient p LEFT JOIN Patient_phones ph ON p.Patient_ID = ph.Patient_ID WHERE p.Patient_ID = ? LIMIT 1");
$stmt_p->bind_param("i", $patient_id);
$stmt_p->execute();
$patient = $stmt_p->get_result()->fetch_assoc();

if (!$patient || empty($patient['PhoneNumber'])) {
    die(json_encode(['status' => 'error', 'message' => 'No phone number on record. Please update your profile.']));
}

$target_phone = $patient['PhoneNumber'];
$type = $_POST['type'] ?? '';

if ($type === 'slip') {
    $app_id = (int)($_POST['app_id'] ?? 0);
    
    $stmt_v = $conn->prepare("
        SELECT a.Date, a.Time, d.Fname as DocFname, d.Lname as DocLname 
        FROM Appointment a 
        JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID 
        WHERE a.Appointment_ID = ? AND a.Patient_ID = ?
    ");
    $stmt_v->bind_param("ii", $app_id, $patient_id);
    $stmt_v->execute();
    $app = $stmt_v->get_result()->fetch_assoc();
    
    if (!$app) {
        die(json_encode(['status' => 'error', 'message' => 'Appointment not found or unauthorized.']));
    }
    
    $localFilePath = generate_appointment_slip_pdf($app_id, $conn);
    
    if ($localFilePath) {
        $doctor_name = "Dr. " . $app['DocFname'] . " " . $app['DocLname'];
        sendWhatsAppMessage($target_phone, $patient['Fname'], $app['Date'], $app['Time'], $doctor_name, $localFilePath, 'document');
        echo json_encode(['status' => 'success', 'message' => 'Arrival slip sent to your WhatsApp!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate PDF.']);
    }

} elseif ($type === 'passport') {
    // Generate Health Passport PDF directly using the function
    $localFilePath = generate_health_passport_pdf($patient_id, $conn);
    
    if ($localFilePath) {
        $message = "🏥 *InnovAI Medical Center*\n\nHello {$patient['Fname']},\nHere is your official Digital Health Passport.";
        
        $cleanPhone = preg_replace('/[^0-9]/', '', $target_phone);
        if (substr($cleanPhone, 0, 2) !== '20') {
            $cleanPhone = (substr($cleanPhone, 0, 1) === '0') ? '20' . substr($cleanPhone, 1) : '20' . $cleanPhone;
        }

        $payload = [
            'number' => $cleanPhone,
            'message' => $message,
            'fileUrl' => $localFilePath,
            'fileType' => 'document'
        ];

        // Direct call to Node Gateway
        $ch_node = curl_init('http://localhost:3000/send-msg');
        curl_setopt($ch_node, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch_node, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch_node, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_node, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch_node);
        curl_close($ch_node);

        echo json_encode(['status' => 'success', 'message' => 'Health Passport sent to your WhatsApp!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate Health Passport.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request type.']);
}
?>