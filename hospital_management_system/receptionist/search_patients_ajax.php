<?php
require_once '../includes/db_connection.php';
session_start();
if(!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Receptionist') die('Unauthorized');

$query = $_GET['q'] ?? '';

if ($query === '') {
    $sql = "SELECT p.Patient_ID, p.Fname, p.Lname, p.Date_of_Birth, ph.PhoneNumber, u.Email 
            FROM Patient p 
            JOIN User_Login u ON p.User_ID = u.User_ID 
            LEFT JOIN Patient_phones ph ON p.Patient_ID = ph.Patient_ID 
            GROUP BY p.Patient_ID ORDER BY p.Fname ASC LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    $search = "%{$query}%";
    $sql = "SELECT p.Patient_ID, p.Fname, p.Lname, p.Date_of_Birth, ph.PhoneNumber, u.Email 
            FROM Patient p 
            JOIN User_Login u ON p.User_ID = u.User_ID 
            LEFT JOIN Patient_phones ph ON p.Patient_ID = ph.Patient_ID 
            WHERE p.Fname LIKE ? OR p.Lname LIKE ? OR ph.PhoneNumber LIKE ? OR u.Email LIKE ? 
            GROUP BY p.Patient_ID LIMIT 50";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $search, $search, $search, $search);
    $stmt->execute();
}
$result = $stmt->get_result();

$patients = [];
while($row = $result->fetch_assoc()){
    $patients[] = $row;
}
echo json_encode($patients);
?>