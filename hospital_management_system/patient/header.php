<?php
require_once '../includes/session_handler.php';
require_once '../includes/constants.php';
require_once '../includes/db_connection.php';
check_role('Patient');

$patient_id = $_SESSION['Patient_ID'];
$p_query = $conn->query("SELECT Fname, Lname FROM Patient WHERE Patient_ID = $patient_id");
$p_data = $p_query->fetch_assoc();
$patient_name = $p_data ? ($p_data['Fname'] . ' ' . $p_data['Lname']) : 'Patient';

$ai_online = (defined('GROQ_API_KEY') && GROQ_API_KEY !== 'YOUR_GROQ_API_KEY');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-teal: #0d9488;
            --primary-teal-hover: #0f766e;
            --soft-lavender: #f3e8ff;
            --text-dark: #334155;
            --bg-light: #f8fafc;
            --glass-bg: rgba(255, 255, 255, 0.9);
        }

        body { 
            background-color: var(--bg-light); 
            color: var(--text-dark); 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Navigation - Premium Glassmorphism */
        .navbar-custom { 
            background: rgba(13, 148, 136, 0.95) !important; 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 4px solid var(--soft-lavender); 
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .navbar-brand { font-weight: 800; color: #fff !important; letter-spacing: 0.5px; }
        .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 600; padding: 0.5rem 1.2rem !important; transition: all 0.2s; border-radius: 8px; }
        .nav-link:hover, .nav-link.active { color: var(--primary-teal) !important; background: var(--soft-lavender); }
        
        /* Interactive Cards */
        .card { 
            border: none; 
            box-shadow: 0 10px 30px rgba(13, 148, 136, 0.05); 
            border-radius: 16px; 
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(13, 148, 136, 0.1);
        }
        .card-header { 
            background-color: transparent; 
            border-bottom: 1px solid rgba(0,0,0,0.05); 
            font-weight: 700; 
            color: var(--primary-teal); 
            padding: 1.5rem;
            border-radius: 16px 16px 0 0 !important; 
        }

        /* Modern Buttons */
        .btn-teal { background-color: var(--primary-teal); color: white; border-radius: 10px; font-weight: 600; transition: all 0.3s; }
        .btn-teal:hover { background-color: var(--primary-teal-hover); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 148, 136, 0.3); }
        .btn-lavender { background-color: var(--soft-lavender); color: var(--primary-teal); border: none; border-radius: 10px; font-weight: 600; transition: all 0.3s; }
        .btn-lavender:hover { background-color: #e9d5ff; color: var(--primary-teal); transform: translateY(-2px); }
        
        /* Form Inputs */
        .form-control, .form-select { border-radius: 10px; border: 1px solid #cbd5e1; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-teal); box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); }

        /* AI Indicator */
        .ai-indicator { font-size: 0.85rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 20px; letter-spacing: 0.5px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); }

        .main-content { flex: 1; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-leaf me-2 text-light"></i> <?php echo APP_NAME; ?> Patient Portal</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#patientNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="patientNav">
            <ul class="navbar-nav mx-auto gap-2 mt-3 mt-lg-0">
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'ai_symptom_checker.php' ? 'active' : ''; ?>" href="ai_symptom_checker.php"><i class="fas fa-robot me-1"></i> AI Triage</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'appointments.php' ? 'active' : ''; ?>" href="appointments.php"><i class="fas fa-calendar-alt me-1"></i> Appointments</a></li>
            </ul>
            <ul class="navbar-nav align-items-center gap-3">
                <li class="nav-item d-none d-lg-block">
                    <?php if($ai_online): ?>
                        <span class="badge bg-light text-success ai-indicator"><i class="fas fa-microchip me-1 fa-pulse"></i> AI Triage Engine: Online</span>
                    <?php else: ?>
                        <span class="badge bg-light text-danger ai-indicator"><i class="fas fa-microchip me-1"></i> AI Triage Engine: Offline</span>
                    <?php endif; ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle bg-white bg-opacity-25 rounded-pill px-4" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fs-5 align-middle me-2"></i> <?php echo htmlspecialchars($patient_name); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="profileDropdown" style="border-radius: 12px; min-width: 200px;">
                        <li><a class="dropdown-item py-2 fw-semibold" href="profile.php"><i class="fas fa-id-badge me-2 text-teal"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger fw-semibold" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Secure Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="main-content container-fluid px-lg-5 py-4">