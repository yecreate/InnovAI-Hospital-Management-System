<?php
require_once 'includes/db_connection.php';

// Execute authorized schema modifications
$queries = [
    "ALTER TABLE Appointment MODIFY COLUMN Status ENUM('Scheduled', 'Checked-In', 'Completed', 'Cancelled', 'Ready') DEFAULT 'Scheduled'",
    "ALTER TABLE AI_Analysis ADD COLUMN Summary_Text TEXT"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Successfully executed: $sql\n";
    } else {
        echo "Error executing $sql: " . $conn->error . "\n";
    }
}
?>