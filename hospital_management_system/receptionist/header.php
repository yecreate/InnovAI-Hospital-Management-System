<?php
require_once '../includes/session_handler.php';
require_once '../includes/constants.php';
require_once '../includes/db_connection.php';
check_role('Receptionist');

$recp_id = $_SESSION['Receptionist_ID'];
$r_query = $conn->query("SELECT Full_Name FROM Receptionist WHERE Receptionist_ID = $recp_id");
$recp_name = $r_query->fetch_assoc()['Full_Name'] ?? 'Receptionist';
$recp_first_name = explode(' ', trim($recp_name))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Engine - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0ea5e9;
            --primary-blue-dark: #0284c7;
            --soft-blue: #e0f2fe;
            --text-dark: #0f172a;
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
            background: rgba(255, 255, 255, 0.95) !important; 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 2px solid var(--primary-blue); 
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .navbar-brand { font-weight: 800; color: var(--primary-blue-dark) !important; letter-spacing: 0.5px; }
        .nav-link { color: #64748b !important; font-weight: 600; padding: 0.5rem 1.2rem !important; transition: all 0.2s; border-radius: 8px; }
        .nav-link:hover, .nav-link.active { color: var(--primary-blue-dark) !important; background: var(--soft-blue); }
        
        /* Interactive Cards */
        .card { 
            border: none; 
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.05); 
            border-radius: 16px; 
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 15px 40px rgba(14, 165, 233, 0.1);
        }
        .card-header { 
            background-color: transparent; 
            border-bottom: 1px solid rgba(0,0,0,0.05); 
            font-weight: 700; 
            color: var(--primary-blue-dark); 
            padding: 1.5rem;
            border-radius: 16px 16px 0 0 !important; 
        }

        /* Modern Buttons */
        .btn-primary { background-color: var(--primary-blue); color: white; border-radius: 10px; font-weight: 600; border: none; transition: all 0.3s; }
        .btn-primary:hover { background-color: var(--primary-blue-dark); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3); }
        
        /* Form Inputs */
        .form-control, .form-select { border-radius: 10px; border: 1px solid #cbd5e1; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); }

        .main-content { flex: 1; }

        /* Receptionist Specific Tweaks */
        .table-hover tbody tr:hover { background-color: var(--soft-blue); }
        .custom-nav-pills .nav-link { color: #64748b !important; background-color: rgba(0,0,0,0.05); font-weight: 600; margin-right: 5px; border-radius: 10px; }
        .custom-nav-pills .nav-link:hover { background-color: rgba(0,0,0,0.1); }
        .custom-nav-pills .nav-link.active { background-color: var(--primary-blue) !important; color: white !important; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-layer-group me-2"></i> Receptionist Engine</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#recpNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="recpNav">
            <ul class="navbar-nav me-auto ms-lg-4 gap-2 mt-3 mt-lg-0">
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php"><i class="fas fa-chart-pie me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'appointment_book.php' ? 'active' : ''; ?>" href="appointment_book.php"><i class="fas fa-calendar-plus me-1"></i> Register & Book</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'qr_scanner.php' ? 'active' : ''; ?>" href="qr_scanner.php"><i class="fas fa-qrcode me-1"></i> Fast-Track Check-In</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'billing_issue.php' ? 'active' : ''; ?>" href="billing_issue.php"><i class="fas fa-file-invoice-dollar me-1"></i> Billing</a></li>
            </ul>
            <ul class="navbar-nav align-items-center gap-3">
                <li class="nav-item d-none d-lg-block">
                    <span class="text-muted fw-bold"><i class="fas fa-user-circle me-1"></i> Hi, <?php echo htmlspecialchars($recp_first_name); ?></span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i> Secure Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="main-content container-fluid px-lg-5 py-4">