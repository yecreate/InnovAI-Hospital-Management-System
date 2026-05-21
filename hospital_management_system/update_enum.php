<?php
require_once 'includes/db_connection.php';
$conn->query("ALTER TABLE Appointment MODIFY COLUMN Status ENUM('Scheduled', 'Checked-In', 'Completed', 'Cancelled', 'Ready', 'Pending') DEFAULT 'Scheduled'");
echo "ENUM updated.\n";
?>