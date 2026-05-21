<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

// Quick Stats
$stats = [
    'doctors' => $conn->query("SELECT COUNT(*) as c FROM Doctor")->fetch_assoc()['c'],
    'patients' => $conn->query("SELECT COUNT(*) as c FROM Patient")->fetch_assoc()['c'],
    'appointments' => $conn->query("SELECT COUNT(*) as c FROM Appointment WHERE Status != 'Completed'")->fetch_assoc()['c'],
    'revenue' => $conn->query("SELECT SUM(Amount) as total FROM Billing WHERE Payment_Status = 'Paid'")->fetch_assoc()['total'] ?? 0
];

// Department Load Balancer Data
$dept_query = "SELECT d.Name, COUNT(doc.Doctor_ID) as doc_count 
               FROM Department d 
               LEFT JOIN Doctor doc ON d.Department_ID = doc.Department_ID 
               GROUP BY d.Department_ID";
$dept_result = $conn->query($dept_query);
$labels = [];
$data = [];
while ($row = $dept_result->fetch_assoc()) {
    $labels[] = $row['Name'];
    $data[] = $row['doc_count'];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-warning border-bottom border-secondary pb-2"><i class="fas fa-tachometer-alt me-2"></i> System Dashboard</h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <a href="manage_staff.php?role=doctor" class="text-decoration-none">
            <div class="card text-center h-100 py-3 shadow-sm border-0" style="border-left: 4px solid #0dcaf0 !important;">
                <div class="card-body">
                    <i class="fas fa-user-md fa-2x text-info mb-2"></i>
                    <h5 class="card-title text-light">Total Doctors</h5>
                    <h2 class="fw-bold text-white"><?php echo $stats['doctors']; ?></h2>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="manage_patients.php" class="text-decoration-none">
            <div class="card text-center h-100 py-3 shadow-sm border-0" style="border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <i class="fas fa-procedures fa-2x text-success mb-2"></i>
                    <h5 class="card-title text-light">Total Patients</h5>
                    <h2 class="fw-bold text-white"><?php echo $stats['patients']; ?></h2>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="manage_appointments.php" class="text-decoration-none">
            <div class="card text-center h-100 py-3 shadow-sm border-0" style="border-left: 4px solid #fd7e14 !important;">
                <div class="card-body">
                    <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                    <h5 class="card-title text-light">Active Appointments</h5>
                    <h2 class="fw-bold text-white"><?php echo $stats['appointments']; ?></h2>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="financial_pulse.php" class="text-decoration-none">
            <div class="card text-center h-100 py-3 shadow-sm border-0" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body text-warning">
                    <i class="fas fa-coins fa-2x mb-2"></i>
                    <h5 class="card-title text-light">Total Revenue</h5>
                    <h2 class="fw-bold text-white">EGP <?php echo number_format($stats['revenue'], 2); ?></h2>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-warning border-0 pt-3 pb-2">
                <h5><i class="fas fa-balance-scale me-2"></i> Dept. Load-Balancer (Staff Distribution)</h5>
            </div>
            <div class="card-body bg-dark">
                <canvas id="loadBalancerChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-info border-0 pt-3 pb-2">
                <h5><i class="fas fa-cogs me-2"></i> Quick Actions</h5>
            </div>
            <div class="card-body bg-dark">
                <a href="manage_staff.php" class="btn btn-outline-info w-100 mb-3 text-start"><i class="fas fa-user-plus me-2"></i> Register New Staff</a>
                <a href="manage_departments.php" class="btn btn-outline-success w-100 mb-3 text-start"><i class="fas fa-building me-2"></i> Manage Departments</a>
                <a href="financial_pulse.php" class="btn btn-gold w-100 text-start"><i class="fas fa-chart-line me-2"></i> View Financial Pulse</a>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('loadBalancerChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Number of Doctors',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(102, 16, 242, 0.7)',
            borderColor: '#6610f2',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, grid: { color: '#495057' }, ticks: { stepSize: 1, color: '#adb5bd' } },
            x: { grid: { display: false }, ticks: { color: '#adb5bd' } }
        },
        plugins: {
            legend: { labels: { color: '#f8f9fa' } }
        }
    }
});
</script>

</div> <!-- Close container from header -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>