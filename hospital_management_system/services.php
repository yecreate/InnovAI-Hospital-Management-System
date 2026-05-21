<?php
require_once 'includes/db_connection.php';
require_once 'includes/public_header.php';

$query = "SELECT Name, Location FROM Department ORDER BY Name ASC";
$result = $conn->query($query);
?>

<style>
    /* Departments Grid */
    .dept-card {
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 16px;
        background: white;
        transition: all 0.3s ease;
    }
    .dept-card:hover {
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
        transform: translateY(-8px);
        border-color: var(--accent-cyan);
    }
    .dept-card .icon-wrapper {
        background: var(--bg-light);
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: var(--primary-blue);
        font-size: 2.2rem;
        transition: all 0.3s ease;
    }
    .dept-card:hover .icon-wrapper {
        background: var(--primary-blue);
        color: var(--accent-cyan);
        transform: rotate(5deg) scale(1.1);
    }
</style>

<div class="container py-5 mt-4">
    <div class="text-center mb-5 mx-auto" style="max-width: 700px;">
        <h6 class="text-cyan fw-bold text-uppercase tracking-wider mb-2">Centers of Excellence</h6>
        <h1 class="fw-bold mb-4" style="color: var(--primary-blue); font-size: 2.5rem;">Our Medical Specialties</h1>
        <p class="lead text-muted">Comprehensive care facilities tailored to your health needs, staffed by world-class specialists and equipped with state-of-the-art technology.</p>
    </div>

    <div class="row g-4">
        <?php if($result && $result->num_rows > 0): ?>
            <?php 
            // Array of icons to cycle through for dynamic visual appeal
            $icons = ['fa-heartbeat', 'fa-brain', 'fa-bone', 'fa-baby', 'fa-eye', 'fa-x-ray', 'fa-lungs', 'fa-tooth'];
            $i = 0;
            while($row = $result->fetch_assoc()): 
                $icon = $icons[$i % count($icons)];
                $i++;
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 dept-card p-4 text-center">
                        <div class="card-body p-0">
                            <div class="icon-wrapper"><i class="fas <?php echo $icon; ?>"></i></div>
                            <h4 class="card-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($row['Name']); ?></h4>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt text-cyan me-2"></i> <?php echo htmlspecialchars($row['Location']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="glass-card p-5 d-inline-block">
                    <i class="fas fa-clinic-medical fa-3x text-muted mb-3 opacity-50"></i>
                    <p class="text-muted fs-5 mb-0">Our clinical departments are currently undergoing updates.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container pb-5">
    <div class="glass-card bg-primary-dark text-white p-5 rounded-4 mt-5 text-center position-relative overflow-hidden">
        <!-- Decorative blobs -->
        <div class="position-absolute top-0 start-0 w-50 h-100" style="background: radial-gradient(circle, rgba(14,165,233,0.2) 0%, transparent 70%);"></div>
        
        <div class="position-relative z-2">
            <h3 class="fw-bold mb-3">Need help choosing the right department?</h3>
            <p class="mb-4 text-light" style="max-width: 600px; margin: 0 auto;">Our intelligent routing system will automatically analyze your symptoms and direct you to the correct specialist.</p>
            <a href="register.php" class="btn btn-cyan btn-lg px-5 rounded-pill fw-bold text-white">Start Intelligent Triage</a>
        </div>
    </div>
</div>

<?php require_once 'includes/public_footer.php'; ?>