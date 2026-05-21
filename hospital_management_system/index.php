<?php
require_once 'includes/db_connection.php';
require_once 'includes/public_header.php';

// Fetch Live Database Stats
$doc_count = $conn->query("SELECT COUNT(*) as c FROM Doctor")->fetch_assoc()['c'];
$pat_count = $conn->query("SELECT COUNT(*) as c FROM Patient")->fetch_assoc()['c'];
$app_count = $conn->query("SELECT COUNT(*) as c FROM Appointment WHERE Status = 'Completed'")->fetch_assoc()['c'];
$dept_count = $conn->query("SELECT COUNT(*) as c FROM Department")->fetch_assoc()['c'];
?>

<style>
    /* Hero Section */
    .hero-section {
        background: url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&q=80&w=2000') no-repeat center center;
        background-size: cover;
        position: relative;
        padding: 140px 0 100px;
        color: white;
    }
    .hero-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.85);
        z-index: 1;
    }
    .hero-content { position: relative; z-index: 2; }
    .hero-title { font-weight: 900; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }

    .stat-icon {
        font-size: 3.5rem;
        background: -webkit-linear-gradient(45deg, var(--primary-blue), var(--accent-cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Smart Patient Journey Timeline */
    .pipeline-container {
        position: relative;
        padding-left: 30px;
    }
    .pipeline-step {
        border-left: 3px solid var(--accent-cyan);
        padding-left: 25px;
        position: relative;
        padding-bottom: 40px;
    }
    .pipeline-step:last-child {
        border-left: 3px solid transparent;
        padding-bottom: 0;
    }
    .pipeline-step::before {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: white;
        font-size: 0.8rem;
        position: absolute;
        left: -13px;
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--accent-cyan);
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--primary-blue);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pipeline-title { color: var(--primary-blue); font-weight: 700; margin-bottom: 5px; }
</style>

<!-- Hero Section -->
<header class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content text-center">
        <h1 class="display-3 hero-title mb-4">Next-Generation Clinical Intelligence</h1>
        <p class="lead mb-5 mx-auto" style="max-width: 800px; font-weight: 300; font-size: 1.3rem;">
            Experience the future of healthcare. <?php echo APP_NAME; ?> integrates autonomous AI care routing, intelligent facility management, and seamless mobile updates into a single unified ecosystem.
        </p>
        <div>
            <a href="register.php" class="btn btn-cyan btn-lg px-5 py-3 me-md-3 mb-3 shadow"><i class="fas fa-user-plus me-2"></i> Register as Patient</a>
            <a href="#journey" class="btn btn-outline-light btn-lg px-5 py-3 mb-3 border-2"><i class="fas fa-compass me-2"></i> Explore The Journey</a>
        </div>
    </div>
</header>

<!-- Advanced High-Fidelity Stats Section -->
<section class="py-5 bg-white shadow-sm" style="position: relative; z-index: 10; margin-top: -50px; border-radius: 20px; max-width: 1200px; margin-left: auto; margin-right: auto;">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <i class="fas fa-hospital-user stat-icon mb-3"></i>
                    <h2 class="fw-bold counter text-dark" data-target="<?php echo $pat_count; ?>">0</h2>
                    <p class="text-muted fw-bold mb-0">Registered Patients</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <i class="fas fa-user-md stat-icon mb-3"></i>
                    <h2 class="fw-bold counter text-dark" data-target="<?php echo $doc_count; ?>">0</h2>
                    <p class="text-muted fw-bold mb-0">Specialist Providers</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <i class="fas fa-check-double stat-icon mb-3"></i>
                    <h2 class="fw-bold counter text-dark" data-target="<?php echo $app_count; ?>">0</h2>
                    <p class="text-muted fw-bold mb-0">Successful Consultations</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <i class="fas fa-hospital-alt stat-icon mb-3"></i>
                    <h2 class="fw-bold counter text-dark" data-target="<?php echo $dept_count; ?>">0</h2>
                    <p class="text-muted fw-bold mb-0">Clinical Departments</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Smart Patient Journey Tracker -->
<section id="journey" class="py-5 mt-4">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h6 class="text-cyan fw-bold text-uppercase tracking-wider mb-2">Workflow Innovation</h6>
                <h2 class="fw-bold mb-4" style="color: var(--primary-blue); font-size: 2.5rem;">The Smart Patient Journey</h2>
                <p class="text-muted fs-5 mb-4">Our proprietary ecosystem eliminates waiting rooms and manual data entry. Follow the automated pipeline from home to the doctor's office.</p>
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&q=80&w=800" alt="Medical Technology" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 end-0 bg-white p-3 rounded-start-4 shadow-lg" style="transform: translate(20px, 20px);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-cyan text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--accent-cyan);">
                                <i class="fas fa-bolt fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Zero-Wait</h5>
                                <small class="text-muted">Digital Workflows</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 offset-lg-1 mt-5 mt-lg-0">
                <div class="pipeline-container">
                    <div class="pipeline-step">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-robot text-cyan fs-4 me-3"></i>
                            <h5 class="pipeline-title mb-0">1. Instant AI-Powered Care Routing</h5>
                        </div>
                        <p class="text-muted small ms-5">Patients register online and utilize our intelligent triage assistant to analyze symptoms, calculate medical urgency, and instantly connect to the optimal department.</p>
                    </div>
                    <div class="pipeline-step">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-qrcode text-cyan fs-4 me-3"></i>
                            <h5 class="pipeline-title mb-0">2. Zero-Wait QR Code Check-ins</h5>
                        </div>
                        <p class="text-muted small ms-5">Upon booking, receive a secure, personalized digital arrival slip. Scan it at the reception desk to instantly notify your doctor of your arrival.</p>
                    </div>
                    <div class="pipeline-step">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-mobile-alt text-cyan fs-4 me-3"></i>
                            <h5 class="pipeline-title mb-0">3. Seamless Mobile Confirmations</h5>
                        </div>
                        <p class="text-muted small ms-5">Receive real-time appointment confirmations and official clinical prescriptions securely delivered directly to your mobile device.</p>
                    </div>
                    <div class="pipeline-step">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-file-medical-alt text-cyan fs-4 me-3"></i>
                            <h5 class="pipeline-title mb-0">4. Your Complete Digital Health Passport</h5>
                        </div>
                        <p class="text-muted small ms-5">Access a continuously updated, AI-summarized medical history and secure PDFs of all your clinical records from anywhere in the world.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Action Hub -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: var(--primary-blue);">Ready to Experience Better Care?</h2>
            <p class="lead text-muted">Join the thousands of patients who trust <?php echo APP_NAME; ?>.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-5">
                <div class="glass-card p-5 text-center h-100 border-top border-4" style="border-top-color: var(--accent-cyan) !important;">
                    <i class="fas fa-laptop-medical fa-3x text-cyan mb-4"></i>
                    <h4 class="fw-bold mb-3">Existing Patients</h4>
                    <p class="text-muted mb-4">Access your health passport, book new appointments, and view clinical histories.</p>
                    <a href="login.php" class="btn btn-outline-dark w-100 rounded-pill py-2 fw-bold">Secure Login</a>
                </div>
            </div>
            <div class="col-md-5">
                <div class="glass-card p-5 text-center h-100 border-top border-4" style="border-top-color: var(--primary-blue) !important;">
                    <i class="fas fa-user-plus fa-3x text-cyan mb-4"></i>
                    <h4 class="fw-bold mb-3">New Patients</h4>
                    <p class="text-muted mb-4">Register in minutes and get instantly routed to the right specialist using our AI triage.</p>
                    <a href="register.php" class="btn btn-cyan w-100 rounded-pill py-2 fw-bold text-white">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Intersection Observer for Animating Counters
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    const animateCounters = () => {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(animateCounters, 10);
            } else {
                counter.innerText = target.toLocaleString();
            }
        });
    }

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        observer.observe(counter);
    });
</script>

<?php require_once 'includes/public_footer.php'; ?>