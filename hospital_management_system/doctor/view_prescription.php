<?php
require_once '../includes/db_connection.php';
require_once '../includes/constants.php';
require_once '../includes/functions.php';
session_start();
if(!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Doctor') die('Unauthorized');

$app_id = (int)$_GET['app_id'];

// Simply use the shared function to generate the PDF file
$localFilePath = generate_prescription_pdf($app_id, $conn);

if ($localFilePath && file_exists($localFilePath)) {
    // Stream the generated file to the browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Prescription_'.$app_id.'.pdf"');
    header('Content-Length: ' . filesize($localFilePath));
    readfile($localFilePath);
    exit;
} else {
    die("Error generating Prescription PDF.");
}
?>