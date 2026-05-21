<?php
// Include our upgraded helper
require_once 'includes/whatsapp_helper.php';

$target_phone = "201005155364"; 
$message = "🏥 *InnovAI Media Test*\nHere is your requested digital asset!";

// A sample public image to test the Baileys image engine
$fileUrl = "https://raw.githubusercontent.com/github/explore/main/topics/php/php.png";
$fileType = "image"; 

// Call the upgraded function
echo "Attempting to send media...<br>";
$result = sendWhatsAppMessage($target_phone, 'Test Patient', date('Y-m-d'), date('H:i'), 'Dr. Test', $fileUrl, $fileType);

echo "Node Gateway Response: " . $result;
?>