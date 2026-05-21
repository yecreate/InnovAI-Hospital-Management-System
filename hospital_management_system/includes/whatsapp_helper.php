<?php
/**
 * InnovAI Medical Center - Baileys WhatsApp API Helper
 * Sends an invisible HTTP POST request to the local Node.js gateway on Port 3000.
 */

function sendWhatsAppMessage($phoneNumber, $patientName, $date, $time, $doctorName, $localFilePath = null, $fileType = null) {
    // 1. Clean the phone number (Strip all non-numeric characters)
    $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
    
    // 2. Format for Egyptian country code (20) if not already present
    if (substr($cleanPhone, 0, 2) !== '20') {
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '20' . substr($cleanPhone, 1);
        } else {
            $cleanPhone = '20' . $cleanPhone;
        }
    }

    // 3. Construct the clinical notification template
    $message = "🏥 *InnovAI Medical Center*\n\n"
             . "Hello $patientName,\n"
             . "Your appointment is confirmed.\n\n"
             . "📅 *Date:* $date\n"
             . "⏰ *Time:* $time\n"
             . "👨‍⚕️ *Doctor:* $doctorName\n\n"
             . "Please arrive 10 minutes early. We wish you a speedy recovery!";

    // 4. Prepare the JSON payload
    $payload = [
        'number' => $cleanPhone,
        'message' => $message
    ];

    // Append file data if provided for Direct PDF/QR Delivery
    if ($localFilePath && $fileType) {
        $payload['fileUrl'] = $localFilePath; // Keeping the JSON key as 'fileUrl' for Node.js backwards compatibility
        $payload['fileType'] = $fileType;
    }

    $data = json_encode($payload);

    // 5. Fire the asynchronous cURL request to the Node.js Server
    $ch = curl_init('http://localhost:3000/send-msg');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    // 6. Execute silently in the background and close
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

/**
 * Sends a Code Red Triage Alert to a specific doctor.
 */
function sendCodeRedAlert($doctorPhone, $patientName, $symptoms) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $doctorPhone);
    if (substr($cleanPhone, 0, 2) !== '20') {
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '20' . substr($cleanPhone, 1);
        } else {
            $cleanPhone = '20' . $cleanPhone;
        }
    }

    $message = "🚨 *URGENT: CODE RED TRIAGE ALERT* 🚨\n\n"
             . "Patient: *$patientName*\n"
             . "Critical Symptoms Reported:\n_$symptoms_\n\n"
             . "Immediate clinical review required.";

    $data = json_encode([
        'number' => $cleanPhone,
        'message' => $message
    ]);

    $ch = curl_init('http://localhost:3000/send-msg');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
?>