<?php require_once 'includes/constants.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Architecture - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="/hospital_management_system/assets/IU_Logo_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .feature-icon { font-size: 2.5rem; color: #0d6efd; margin-bottom: 1rem; }
        .arch-card { border-radius: 12px; border: 1px solid #e9ecef; }
        .arch-card:hover { border-color: #0d6efd; box-shadow: 0 .5rem 1rem rgba(13,110,253,.15)!important; transition: all 0.3s ease;}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-hospital-user me-2"></i> <?php echo APP_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="architecture.php">Architecture</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item ms-lg-3"><a class="btn btn-light text-primary fw-bold px-4" href="login.php">Portal Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-dark">Strict System Architecture</h1>
        <p class="lead text-muted w-75 mx-auto">Built according to the rigorous HMS V4 ERD and schema standards, integrating edge devices, Artificial Intelligence, and distinct multi-role portal architectures.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card arch-card shadow-sm h-100 p-4 text-center">
                <i class="fas fa-brain feature-icon"></i>
                <h4 class="fw-bold">Groq AI Symptom Engine</h4>
                <p class="text-muted">A dedicated module communicating with the Groq API via cURL to analyze symptoms in real-time, yielding a calculated Risk Score and Predicted Specialization.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card arch-card shadow-sm h-100 p-4 text-center">
                <i class="fas fa-database feature-icon text-success"></i>
                <h4 class="fw-bold">Strict ERD Compliance</h4>
                <p class="text-muted">Over 15 tables perfectly mapping complex entity relationships. Multi-valued attributes (phones) are isolated, and calculated columns (Age) are driven by robust DB logic.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card arch-card shadow-sm h-100 p-4 text-center">
                <i class="fas fa-qrcode feature-icon text-dark"></i>
                <h4 class="fw-bold">OSI Edge-Device Integrations</h4>
                <p class="text-muted">HTML5-QRCode scanner acts as a clinical edge device mapping patient arrivals directly to the Application Layer via automated AJAX HTTP requests.</p>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card arch-card shadow-sm h-100 p-4">
                <h4 class="fw-bold border-bottom pb-2 mb-3"><i class="fas fa-users-cog text-primary me-2"></i> 5 Distinct Portals</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <strong>Admin:</strong> Charcoal Dark Theme, Dept Load-Balancer, Financial Pulse</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <strong>Receptionist:</strong> Productivity Light, AJAX Patient Search, QR Mirror Scanner</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <strong>Doctor:</strong> Clinical Blue, AI Priority Patient Queue, Triage Consults</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> <strong>Nurse:</strong> Attentive Green, Live Room Heatmap, Mock Vitals Log</li>
                    <li><i class="fas fa-check text-success me-2"></i> <strong>Patient:</strong> Wellness Teal, Groq Triage Form, AI PDF Health Passport</li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card arch-card shadow-sm h-100 p-4">
                <h4 class="fw-bold border-bottom pb-2 mb-3"><i class="fas fa-file-pdf text-danger me-2"></i> FPDF Engine Integrations</h4>
                <p class="text-muted">The system utilizes the FPDF library to generate professional, multi-page dynamically structured documents:</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-file-invoice-dollar text-secondary me-2"></i> <strong>Invoices:</strong> Watermarked "Paid" PDF Bills</li>
                    <li class="mb-2"><i class="fas fa-ticket-alt text-secondary me-2"></i> <strong>Arrival Slips:</strong> Encoded PHPQRCode slips for scanner validation</li>
                    <li><i class="fas fa-file-medical text-secondary me-2"></i> <strong>Health Passports:</strong> Iterative history compilation with AI summaries.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Built to Strict Architectural Standards.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>