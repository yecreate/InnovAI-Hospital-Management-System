<?php
require_once '../includes/session_handler.php';
require_once '../includes/constants.php';
require_once '../includes/db_connection.php';
check_role('Doctor');

$doc_id = $_SESSION['Doctor_ID'];
$doc_query = $conn->query("SELECT Fname FROM Doctor WHERE Doctor_ID = $doc_id");
$doc_name = $doc_query->fetch_assoc()['Fname'] ?? 'Doctor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Portal - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Clinical Blue (Hospital Blue/White) Theme */
        body { background-color: #f4f9f9; color: #333; }
        .navbar { background-color: #0056b3 !important; }
        .navbar-brand { font-weight: bold; color: #fff !important; }
        .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 8px; }
        .card-header { background-color: #e3f2fd; border-bottom: 2px solid #0056b3; font-weight: bold; color: #004085; }
        .btn-primary { background-color: #0056b3; border-color: #004085; }
        .btn-primary:hover { background-color: #004085; }
        
        /* Urgent Flashing Badge */
        @keyframes flash {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .badge-urgent { background-color: #dc3545; animation: flash 1.5s infinite; font-size: 0.9em; padding: 5px 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-stethoscope me-2"></i> Clinical Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#docNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="docNav">
            <ul class="navbar-nav me-auto">
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">AI Priority Queue</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'my_patients.php' ? 'active' : ''; ?>" href="my_patients.php">My Patients</a></li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3 text-light align-self-center">
                    <?php if(defined('GROQ_API_KEY') && GROQ_API_KEY !== 'YOUR_GROQ_API_KEY'): ?>
                        <span class="badge bg-success" title="AI Services Connected"><i class="fas fa-brain"></i> AI Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary" title="AI Services Disconnected"><i class="fas fa-brain"></i> AI Offline</span>
                    <?php endif; ?>
                </li>
                <li class="nav-item me-3 text-light align-self-center">
                    Hi, Dr. <?php echo htmlspecialchars($doc_name); ?>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm mt-1" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid px-4">
