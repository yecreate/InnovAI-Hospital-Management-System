<?php
require_once 'includes/ai_engine.php';
$res = generate_medical_summary("John Doe", "Date: 2026-05-17 Diagnosis: Headache Treatment: Rest");
echo "Summary: " . $res . "\n";
?>