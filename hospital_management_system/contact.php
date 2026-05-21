<?php
require_once 'includes/db_connection.php';
require_once 'includes/public_header.php';
?>

<style>
    .team-card {
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 16px;
        background: white;
        transition: all 0.3s ease;
    }
    .team-card:hover {
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
        transform: translateY(-8px);
        border-color: var(--accent-cyan);
    }
    .avatar-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: var(--primary-blue);
        border: 3px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .leader-card {
        border-color: #ffd700;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
        background: linear-gradient(to bottom, #ffffff, #fffaf0);
    }
    .leader-card:hover {
        border-color: #ffc107;
        box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);
    }
    .leader-avatar {
        color: #ffc107 !important;
        border: 3px solid #ffd700;
        background: #fff8e1;
    }
    .github-link:hover {
        text-decoration: underline !important;
    }
    .contributor-pill {
        background: white; border: 1px solid rgba(0,0,0,0.08);
        border-radius: 50px; padding: 10px 20px;
        display: inline-flex; align-items: center; gap: 12px;
        transition: all 0.2s ease;
    }
    .contributor-pill:hover {
        border-color: var(--accent-cyan); box-shadow: 0 4px 12px rgba(14,165,233,0.1); transform: translateY(-2px);
    }
</style>

<!-- Contact Hero -->
<div class="bg-primary-dark text-white py-5 position-relative overflow-hidden mb-5">
    <div class="position-absolute top-0 end-0 w-50 h-100" style="background: radial-gradient(circle, rgba(14,165,233,0.2) 0%, transparent 70%);"></div>
    <div class="container py-5 position-relative z-2 text-center">
        <h6 class="text-cyan fw-bold text-uppercase tracking-wider mb-2">Get in Touch</h6>
        <h1 class="display-4 fw-bold mb-3">We're Here to Help</h1>
        <p class="lead text-light mb-0" style="max-width: 600px; margin: 0 auto;">Connect with our world-class engineering team or find resources to help navigate our intelligent healthcare ecosystem.</p>
    </div>
</div>

<div class="container py-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: var(--primary-blue);">The Engineering Team</h2>
        <p class="text-muted">The architects behind the <?php echo APP_NAME; ?> platform.</p>
    </div>

    <!-- Step A (The Architect) -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6 col-lg-5">
            <div class="card team-card leader-card h-100 text-center p-4 position-relative">
                <div class="card-body p-0">
                    <div class="avatar-wrapper leader-avatar">
                        <i class="fas fa-user-astronaut fa-3x"></i>
                    </div>
                    
                    <div class="mb-3">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 0.85rem;"><i class="fas fa-star me-1 text-dark"></i> Team Leader & Core Developer</span>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-2">Youssef Sameh Ahmed Noby</h5>
                    <p class="text-muted small mb-3">ID: 24030126</p>
                    
                    <a href="mailto:youssef.ahmed.24030126@iu.edu.eg" class="btn btn-sm btn-outline-dark rounded-pill px-3 mb-2"><i class="fas fa-envelope text-cyan me-1"></i> Email Contact</a>
                    <br>
                    <a href="https://www.linkedin.com/in/yecreate/" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 mt-1" style="background-color: #0a66c2; border-color: #0a66c2;"><i class="fab fa-linkedin me-1"></i> LinkedIn Profile</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Step B (The Roster) -->
    <?php
    $roster = [
        ['id' => '24030055', 'name' => 'Moatasem Mohamed Mostafa Saied', 'email' => 'moatasem.mostafa.24030055@iu.edu.eg'],
        ['id' => '24030005', 'name' => 'Ahmed Hesham Ramadan Ismail', 'email' => 'ahmed.ramadan.24030005@iu.edu.eg'],
        ['id' => '24030150', 'name' => 'Eyad Ayman Mohamed Talaat', 'email' => 'eyad.mohamed.24030150@iu.edu.eg'],
        ['id' => '24030217', 'name' => 'Youssef Wael Atef Mohamed', 'email' => 'youssef.atef.24030217@iu.edu.eg'],
        ['id' => '24030006', 'name' => 'Marwan Bahaa Mohammed Elsayed', 'email' => 'marwan.mohammed.24030006@iu.edu.eg']
    ];
    ?>
    <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
        <?php foreach($roster as $member): ?>
            <div class="contributor-pill">
                <i class="fas fa-user text-cyan fs-5"></i>
                <div class="text-start">
                    <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?php echo $member['name']; ?></div>
                    <div class="text-muted" style="font-size: 0.8rem;">ID: <?php echo $member['id']; ?> | <a href="mailto:<?php echo $member['email']; ?>" class="text-decoration-none text-muted"><i class="fas fa-envelope ms-1 text-cyan"></i></a></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


    <!-- Academic & Project Context Section -->
    <div class="row mt-5 mb-4 justify-content-center">
        <div class="col-lg-10">
            <div class="glass-card p-5 text-center shadow-sm" style="background: linear-gradient(135deg, rgba(13,148,136,0.05), rgba(15,23,42,0.05)); border: 1px solid var(--accent-cyan); border-radius: 20px;">
                <div class="mb-3">
                    <i class="fas fa-university fa-3x text-cyan mb-3"></i>
                </div>
                <h3 class="fw-bold mb-3 text-dark">Academic Context</h3>
                <p class="lead mb-0 text-muted" style="font-weight: 500;">
                    This project was developed as a Database Course Project, Spring Semester - Level 2 - Faculty of Computers and Information Technology (FCIT), Innovation University - Egypt.
                </p>
            </div>
        </div>
    </div>

    <!-- GitHub Repository Section -->
    <div class="row mb-5 justify-content-center">
        <div class="col-lg-10">
            <div class="glass-card p-5 d-flex flex-column align-items-center text-center shadow-sm" style="border-radius: 20px; background: white; border: 1px solid rgba(0,0,0,0.08);">
                <i class="fab fa-github fa-4x mb-3 text-dark"></i>
                <h3 class="fw-bold mb-3">Open Source Repository</h3>
                <p class="text-muted mb-4">Explore the source code, documentation, and project history on GitHub.</p>
                <a href="https://github.com/yecreate/InnovAI-Hospital-Management-System" target="_blank" class="text-cyan fw-bold mb-4 github-link" style="text-decoration: none; font-size: 1.2rem; word-break: break-all;">
                    <i class="fas fa-link me-2"></i>github.com/yecreate/InnovAI-Hospital-Management-System
                </a>
                <div class="p-3 bg-light rounded-4 shadow-sm border border-light">
                    <img src="assets/github-qr-code.png" alt="GitHub Repository QR Code" class="img-fluid rounded" style="max-width: 160px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Info Cards -->
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="glass-card p-4 h-100 d-flex align-items-center shadow-sm border-0" style="border-radius: 16px;">
                <div class="bg-light p-3 rounded-circle me-4 text-cyan fs-3">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Main Headquarters</h5>
                    <p class="text-muted mb-0">InnovAI Technology Park<br>Global Healthcare District</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card p-4 h-100 d-flex align-items-center shadow-sm border-0" style="border-radius: 16px;">
                <div class="bg-light p-3 rounded-circle me-4 text-cyan fs-3">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">24/7 Patient Support</h5>
                    <p class="text-muted mb-0">Need assistance navigating the portal?<br><a href="login.php" class="text-cyan text-decoration-none fw-bold">Access the Patient Portal</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/public_footer.php'; ?>