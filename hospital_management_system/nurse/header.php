<?php
require_once '../includes/session_handler.php';
require_once '../includes/constants.php';
require_once '../includes/db_connection.php';
check_role('Nurse');

$nurse_id = $_SESSION['Nurse_ID'];
$n_query = $conn->query("SELECT Fname FROM Nurse WHERE Nurse_ID = $nurse_id");
$nurse_name = $n_query->fetch_assoc()['Fname'] ?? 'Nurse';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Portal - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Attentive Green (Mint/Soft Gray) Theme */
        body { background-color: #f8fcf9; color: #2c3e35; }
        .navbar { background-color: #20c997 !important; border-bottom: 2px solid #1aa179; }
        .navbar-brand { font-weight: bold; color: #fff !important; }
        .nav-link { color: #f1fdf9 !important; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: #fff !important; text-decoration: underline; }
        .card { border: 1px solid #d1e7dd; box-shadow: 0 4px 6px rgba(32, 201, 151, 0.05); border-radius: 8px; }
        .card-header { background-color: #d1e7dd; border-bottom: 1px solid #badbcc; font-weight: bold; color: #0f5132; }
        
        /* Heatmap Styles */
        .room-box {
            height: 100px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .room-box:hover { transform: scale(1.05); }
        .room-available { background-color: #198754; } /* Green */
        .room-occupied { background-color: #dc3545; } /* Red */
        .room-maintenance { background-color: #ffc107; color: #000; } /* Yellow */
        
        .room-title { font-size: 1.2rem; }
        .room-subtitle { font-size: 0.8rem; font-weight: normal; opacity: 0.9; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-hand-holding-medical me-2"></i> Care Wing</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nurseNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nurseNav">
            <ul class="navbar-nav me-auto">
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Floor Heatmap</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'ward_view.php' ? 'active' : ''; ?>" href="ward_view.php">My Ward Assignments</a></li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3 text-light">
                    Hi, <?php echo htmlspecialchars($nurse_name); ?>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm mt-1" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid px-4">
