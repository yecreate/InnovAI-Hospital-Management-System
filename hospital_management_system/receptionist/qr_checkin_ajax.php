<?php
require_once '../includes/db_connection.php';
session_start();
if(!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Receptionist') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $app_id = $data['appointment_id'] ?? null;
    
    if($app_id) {
        $stmt = $conn->prepare("UPDATE Appointment SET Status = 'Checked-In' WHERE Appointment_ID = ?");
        $stmt->bind_param("i", $app_id);
        if($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Appointment #'.$app_id.' successfully checked in!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Appointment ID or already checked in.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No ID provided.']);
    }
}
?>