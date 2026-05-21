<?php
require_once '../includes/db_connection.php';
require_once '../includes/constants.php';
require_once '../includes/functions.php';
session_start();
if(!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Patient') die('Unauthorized');

$patient_id = $_SESSION['Patient_ID'];

// Simply use the shared function to generate the PDF file
$localFilePath = generate_health_passport_pdf($patient_id, $conn);

if ($localFilePath && file_exists($localFilePath)) {
    // Stream the generated file to the browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Health_Passport.pdf"');
    header('Content-Length: ' . filesize($localFilePath));
    readfile($localFilePath);
    exit;
} else {
    die("Error generating Health Passport.");
}
?>