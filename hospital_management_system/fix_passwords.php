<?php
require_once 'includes/db_connection.php';

echo "Starting password hash update...\n";

$result = $conn->query("SELECT User_ID, Password FROM User_Login");
$updated_count = 0;

while ($row = $result->fetch_assoc()) {
    $current_password = $row['Password'];
    $user_id = $row['User_ID'];
    
    // Check if the password is NOT already a bcrypt hash (bcrypt hashes usually start with $2y$)
    if (strpos($current_password, '$2y$') !== 0) {
        $hashed_password = password_hash($current_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE User_Login SET Password = ? WHERE User_ID = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $updated_count++;
            echo "Updated User_ID $user_id (hashed plain text)\n";
        } else {
            echo "Failed to update User_ID $user_id\n";
        }
    }
}

echo "Finished. Updated $updated_count passwords.\n";
?>