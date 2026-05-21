<?php
session_start();

function check_login() {
    if (!isset($_SESSION['User_ID'])) {
        header("Location: /hospital_management_system/login.php");
        exit();
    }
}

function check_role($required_role) {
    check_login();
    if ($_SESSION['Role'] !== $required_role) {
        header("Location: /hospital_management_system/login.php?error=unauthorized");
        exit();
    }
}
?>