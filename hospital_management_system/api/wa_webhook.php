<?php
/**
 * InnovAI Medical Center - WhatsApp AI Webhook
 * Bulletproof JSON & Regex Extraction Edition
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain");

// 1. Catch payload
$json_payload = file_get_contents("php://input");
$data = json_decode($json_payload, true);

if (!$data || !isset($data['message'])) {
    http_response_code(400);
    echo "Error: Missing JSON payload.";
    exit;
}

$patient_number = $data['number'];
$symptoms = $data['message'];

require_once '../includes/db_connection.php';
require_once '../includes/ai_engine.php';
require_once '../includes/whatsapp_helper.php'; 

try {
    // 2. Query Llama 3 Engine
    $ai_result_raw = analyze_symptoms_with_groq($symptoms);

    // 3. Standardize to Array
    if (is_array($ai_result_raw)) {
        $ai_data = $ai_result_raw;
    } else {
        $clean_json = str_replace(['```json', '```'], '', $ai_result_raw);
        $ai_data = json_decode(trim($clean_json), true);
    }

    // 4. API Error Catch
    if (isset($ai_data['error'])) {
        echo "⚠️ *InnovAI System Alert*\nThe Groq AI API returned an error: " . $ai_data['error']['message'];
        exit;
    }

    // 5. DEEP SEARCH ALGORITHM (Immune to LLM formatting hallucinations)
    $flat_data = json_encode($ai_data);
    $risk = 'Moderate';
    $department = 'General Practice';

    // Regex searches for ANY key containing the word "risk" and grabs its value
    if (preg_match('/"[^"]*risk[^"]*"\s*:\s*"([^"]+)"/i', $flat_data, $matches)) {
        $risk = ucfirst(trim($matches[1]));
    }
    // Regex searches for ANY key containing the word "department" and grabs its value
    if (preg_match('/"[^"]*department[^"]*"\s*:\s*"([^"]+)"/i', $flat_data, $matches)) {
        $department = ucfirst(trim($matches[1]));
    }

    // 6. Format Final Response
    $reply = "🏥 *InnovAI Medical Triage*\n\n";
    $reply .= "We have analyzed your symptoms using our Llama 3 Engine.\n\n";
    $reply .= "🚨 *Risk Level:* " . $risk . "\n";
    $reply .= "👨‍⚕️ *Recommended Dept:* " . $department . "\n\n";

    // 7. Code Red Logic (Now triggers if string contains the word, not just exact match)
    if (stripos($risk, 'High') !== false || stripos($risk, 'Critical') !== false || stripos($risk, 'Severe') !== false) {
        $reply .= "⚠️ *URGENT:* Your symptoms indicate a potentially critical condition. Please visit the Emergency Room immediately.\n";
        
        if (function_exists('sendCodeRedAlert')) {
            // REPLACE THIS with your actual phone number so you get the Doctor alert too!
            $doctor_phone = "201000000000"; 
            sendCodeRedAlert($doctor_phone, $patient_number, $symptoms);
        }
    } else {
        $reply .= "To officially book an appointment with this department, please log in to your Patient Portal.";
    }

    echo $reply;

} catch (Exception $e) {
    echo "🏥 *InnovAI System Alert*\nOur AI triage system is currently unavailable.";
}
?>