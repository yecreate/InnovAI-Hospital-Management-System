<?php
require_once '../includes/session_handler.php';
require_once '../includes/constants.php';
check_role('Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dark Mode (Charcoal/Indigo/Gold) Theme */
        body { background-color: #212529; color: #f8f9fa; }
        .navbar { background-color: #1a1e21 !important; border-bottom: 2px solid #6610f2; }
        .navbar-brand { color: #f8f9fa !important; font-weight: bold; }
        .nav-link { color: #adb5bd !important; }
        .nav-link:hover, .nav-link.active { color: #ffc107 !important; }
        .nav-tabs .nav-link.active { background-color: #343a40 !important; color: #ffc107 !important; border-color: #495057 #495057 #343a40 !important; }
        .nav-tabs { border-bottom: 1px solid #495057; }
        .card { background-color: #343a40; border: 1px solid #495057; color: #f8f9fa; }
        .card-header { background-color: #1a1e21; border-bottom: 1px solid #495057; font-weight: bold; }
        .table { color: #f8f9fa; }
        .table-hover tbody tr:hover { color: #ffc107; }
        .table thead th { border-bottom: 2px solid #6610f2; background-color: #212529; color: #ffc107; }
        .table td { border-bottom: 1px solid #495057; }
        .btn-gold { background-color: #ffc107; color: #212529; font-weight: bold; }
        .btn-gold:hover { background-color: #e0a800; color: #212529; }
        .form-control, .form-select { background-color: #495057; color: #fff; border: 1px solid #6c757d; }
        .form-control:focus, .form-select:focus { background-color: #495057; color: #fff; border-color: #ffc107; box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-user-shield me-2"></i> Admin Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'manage_staff.php' ? 'active' : ''; ?>" href="manage_staff.php">Manage Staff</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'manage_departments.php' ? 'active' : ''; ?>" href="manage_departments.php">Departments</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'manage_patients.php' ? 'active' : ''; ?>" href="manage_patients.php">Patients</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'manage_appointments.php' ? 'active' : ''; ?>" href="manage_appointments.php">Appointments</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'financial_pulse.php' ? 'active' : ''; ?>" href="financial_pulse.php">Financial Pulse</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'project_docs.php' ? 'active' : ''; ?>" href="project_docs.php"><i class="fas fa-file-code me-1"></i> Blueprints</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="btn btn-outline-danger btn-sm mt-1" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid px-4">
