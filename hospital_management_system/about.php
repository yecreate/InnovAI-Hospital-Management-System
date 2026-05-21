<?php
require_once 'includes/db_connection.php';
require_once 'includes/public_header.php';
?>

<div class="container py-5 mt-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
            <h6 class="text-cyan fw-bold text-uppercase tracking-wider mb-2">Our Vision</h6>
            <h1 class="display-4 fw-bold mb-4" style="color: var(--primary-blue);">Redefining Modern Healthcare</h1>
            <p class="lead text-muted mb-4">Built on the foundation of advanced technology and compassionate care, <?php echo APP_NAME; ?> represents a paradigm shift in medical facility management.</p>
            <p class="text-muted mb-4">Our core philosophy revolves around integrating cutting-edge intelligent triage, secure digital health records, and real-time operational workflows to deliver unprecedented efficiency and patient care.</p>
            
            <div class="row mt-5 g-4">
                <div class="col-sm-6">
                    <div class="d-flex align-items-start">
                        <div class="bg-light p-3 rounded-circle me-3 text-cyan">
                            <i class="fas fa-shield-alt fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Strict Security</h5>
                            <p class="small text-muted mb-0">Enterprise-grade data protection.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex align-items-start">
                        <div class="bg-light p-3 rounded-circle me-3 text-cyan">
                            <i class="fas fa-microchip fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">AI Integration</h5>
                            <p class="small text-muted mb-0">Intelligent clinical routing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="position-relative">
                <div class="glass-card border-0 p-2 position-relative z-2 shadow-lg">
                    <img src="https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&q=80&w=800" alt="Hospital Interior" class="img-fluid rounded-4">
                </div>
                <!-- Decorative background elements -->
                <div class="position-absolute top-0 end-0 rounded-circle" style="width: 300px; height: 300px; background: linear-gradient(45deg, var(--accent-cyan), transparent); opacity: 0.1; transform: translate(10%, -10%); z-index: 1;"></div>
                <div class="position-absolute bottom-0 start-0 rounded-circle" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-blue), transparent); opacity: 0.1; transform: translate(-20%, 20%); z-index: 1;"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/public_footer.php'; ?>