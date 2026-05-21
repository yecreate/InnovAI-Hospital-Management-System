<?php
require_once 'constants.php';
// Determine AI Engine Status based on API Key configuration
$ai_online = (defined('GROQ_API_KEY') && GROQ_API_KEY !== 'YOUR_GROQ_API_KEY');
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Intelligent Healthcare</title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #0f172a;
            --accent-cyan: #0ea5e9;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.3);
            --bg-light: #f8fafc;
            --text-dark: #334155;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar & Header */
        .navbar-custom {
            background: var(--primary-blue);
            border-bottom: 3px solid var(--accent-cyan);
            padding: 15px 0;
            backdrop-filter: blur(10px);
            background-color: rgba(15, 23, 42, 0.95);
        }
        .navbar-brand { font-weight: 800; letter-spacing: 0.5px; font-size: 1.5rem; }
        .nav-link { font-weight: 500; padding: 0.5rem 1rem !important; transition: all 0.2s; color: rgba(255,255,255,0.8); }
        .nav-link:hover, .nav-link.active { color: var(--accent-cyan) !important; }
        .ai-badge { font-size: 0.8rem; padding: 0.5rem 0.8rem; letter-spacing: 0.5px; border-radius: 8px; }

        .btn-cyan {
            background-color: var(--accent-cyan);
            color: white;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-cyan:hover {
            background-color: #0284c7;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.1);
        }

        /* Utility */
        .text-cyan { color: var(--accent-cyan) !important; }
        .bg-primary-dark { background-color: var(--primary-blue) !important; }
        .main-content { flex: 1; }
    </style>
</head>
<body>

<!-- Global Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand text-white" href="index.php">
            <i class="fas fa-heartbeat text-cyan me-2"></i> <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="services.php">Specialties</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'about.php' ? 'active' : ''; ?>" href="about.php">Our Vision</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center flex-wrap gap-3 mt-3 mt-lg-0">
                <?php if($ai_online): ?>
                    <span class="badge bg-light text-dark ai-badge border shadow-sm"><i class="fas fa-robot text-cyan me-1"></i> AI Active</span>
                <?php endif; ?>
                <a href="login.php" class="btn btn-cyan rounded-pill px-4"><i class="fas fa-user-circle me-2"></i> Patient Portal</a>
            </div>
        </div>
    </div>
</nav>
<main class="main-content">