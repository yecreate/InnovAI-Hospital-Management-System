<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$patient_id = $_SESSION['Patient_ID'];

// Fetch next appointment
$stmt = $conn->prepare("
    SELECT a.*, d.Lname as DocLname, d.Specialization, r.Room_ID 
    FROM Appointment a
    JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID
    LEFT JOIN Room r ON a.Room_ID = r.Room_ID
    WHERE a.Patient_ID = ? AND a.Date >= CURRENT_DATE AND a.Status IN ('Scheduled', 'Checked-In')
    ORDER BY a.Date ASC, a.Time ASC LIMIT 1
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$next_app = $stmt->get_result()->fetch_assoc();

// Fetch AI Analysis results (Recent)
$stmt_ai = $conn->prepare("SELECT * FROM AI_Analysis WHERE Patient_ID = ? ORDER BY Timestamp DESC LIMIT 1");
$stmt_ai->bind_param("i", $patient_id);
$stmt_ai->execute();
$ai_data = $stmt_ai->get_result()->fetch_assoc();
?>

<div class="row mb-5">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-teal);"><i class="fas fa-chart-line me-2"></i> Health Command Center</h2>
        <p class="text-muted fs-5">Welcome back. Here is a quick overview of your current clinical status.</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card h-100 text-center p-4" style="border-top: 4px solid var(--primary-teal);">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="bg-light p-3 rounded-circle mb-3 text-teal" style="color: var(--primary-teal);">
                    <i class="fas fa-calendar-plus fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-3">Schedule Care</h5>
                <p class="text-muted small mb-4">Book a new consultation with our specialists.</p>
                <a href="appointments.php" class="btn btn-teal w-100 mt-auto rounded-pill">Book Appointment</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4" style="border-top: 4px solid #8b5cf6;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="bg-light p-3 rounded-circle mb-3" style="color: #8b5cf6;">
                    <i class="fas fa-robot fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-3">AI-Powered Clinical Triage</h5>
                <p class="text-muted small mb-4">Evaluate your symptoms and find the right department instantly.</p>
                <a href="ai_symptom_checker.php" class="btn text-white w-100 mt-auto rounded-pill" style="background-color: #8b5cf6;">Start Triage</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4" style="border-top: 4px solid #3b82f6;">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="bg-light p-3 rounded-circle mb-3" style="color: #3b82f6;">
                    <i class="fas fa-file-medical-alt fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-3">Digital Health Passport</h5>
                <p class="text-muted small mb-4">Access your complete clinical history and AI-generated summary.</p>
                <div class="w-100 mt-auto d-flex gap-2">
                    <a href="health_passport.php" target="_blank" class="btn text-white flex-grow-1 rounded-pill" style="background-color: #3b82f6;" title="View PDF"><i class="fas fa-eye me-1"></i> View</a>
                    <button type="button" id="btn-passport" class="btn btn-success rounded-pill px-4" onclick="sendToWhatsApp('passport')" title="Send to WhatsApp"><i class="fab fa-whatsapp"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Next Appointment Timeline -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-check me-2"></i> Upcoming Care Plan</span>
            </div>
            <div class="card-body p-4">
                <?php if($next_app): ?>
                    <div class="position-relative ps-4 border-start border-3 border-teal pb-3" style="border-color: var(--primary-teal) !important;">
                        <div class="position-absolute bg-teal rounded-circle" style="width: 16px; height: 16px; left: -9px; top: 0; background-color: var(--primary-teal);"></div>
                        <h5 class="fw-bold text-dark mb-1"><?php echo date('l, M d, Y', strtotime($next_app['Date'])); ?></h5>
                        <p class="text-primary fw-semibold mb-3"><i class="far fa-clock me-1"></i> <?php echo date('h:i A', strtotime($next_app['Time'])); ?></p>
                        
                        <div class="bg-light rounded p-3 mb-4">
                            <p class="mb-1"><i class="fas fa-user-md text-muted me-2"></i> <strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($next_app['DocLname']); ?> (<?php echo htmlspecialchars($next_app['Specialization']); ?>)</p>
                            <p class="mb-0"><i class="fas fa-map-marker-alt text-muted me-2"></i> <strong>Location:</strong> <?php echo $next_app['Room_ID'] ? 'Room ' . $next_app['Room_ID'] : 'Main Facility'; ?></p>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <a href="../receptionist/print_slip.php?id=<?php echo $next_app['Appointment_ID']; ?>" target="_blank" class="btn btn-lavender rounded-pill px-4 flex-grow-1"><i class="fas fa-qrcode me-2"></i> Digital Arrival Slip</a>
                            <button type="button" class="btn btn-outline-success rounded-pill px-4" id="btn-slip-<?php echo $next_app['Appointment_ID']; ?>" onclick="sendToWhatsApp('slip', <?php echo $next_app['Appointment_ID']; ?>)"><i class="fab fa-whatsapp me-2"></i> Send to Mobile</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-calendar-times fa-2x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted fw-bold">No Scheduled Care</h5>
                        <p class="text-muted small">You are all caught up. Schedule an appointment if you need medical assistance.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- AI Clinical Status -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-heartbeat me-2"></i> Clinical Profile Status</span>
            </div>
            <div class="card-body p-4">
                <?php if($ai_data && !empty($ai_data['Summary_Text'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">AI Executive Summary</h6>
                        <div class="p-3 rounded shadow-sm" style="background-color: var(--soft-lavender); border-left: 4px solid var(--primary-teal);">
                            <p class="mb-0 text-dark fw-medium" style="line-height: 1.6;">"<?php echo htmlspecialchars($ai_data['Summary_Text']); ?>"</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if($ai_data && $ai_data['Input_Symptoms']): ?>
                    <div>
                        <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.8rem; letter-spacing: 1px;">Recent Triage Record</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge" style="background-color: var(--primary-teal);"><?php echo htmlspecialchars($ai_data['Predicted_Specialization']); ?></span>
                        </div>
                        <p class="small text-muted fst-italic mb-3 border p-2 rounded bg-light shadow-sm">"<?php echo htmlspecialchars($ai_data['Input_Symptoms']); ?>"</p>
                        
                        <?php 
                            $risk_percent = ($ai_data['Risk_Score'] / 10) * 100;
                            $bg_class = ($ai_data['Risk_Score'] >= 8) ? 'bg-danger' : (($ai_data['Risk_Score'] >= 5) ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>Clinical Urgency Level</span>
                            <span class="<?php echo str_replace('bg-', 'text-', $bg_class); ?>"><?php echo $ai_data['Risk_Score']; ?>/10</span>
                        </div>
                        <div class="progress shadow-sm" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar <?php echo $bg_class; ?>" role="progressbar" style="width: <?php echo $risk_percent; ?>%"></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-file-medical fa-2x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted fw-bold">No Triage Records</h5>
                        <p class="text-muted small">Your profile lacks recent clinical triage data. Utilize our AI engine if experiencing symptoms.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function sendToWhatsApp(type, appId = null) {
    const btnId = type === 'slip' ? 'btn-slip-' + appId : 'btn-passport';
    const btn = document.getElementById(btnId);
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('type', type);
    if (appId) formData.append('app_id', appId);

    fetch('send_whatsapp_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if (data.status === 'success') {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.replace('btn-success', 'btn-outline-success');
            if(btn.classList.contains('btn-outline-success') && type==='slip') {
                // Keep classes clean
            }
            setTimeout(() => {
                btn.innerHTML = originalText;
                if(type === 'passport') {
                    btn.classList.replace('btn-outline-success', 'btn-success');
                } else {
                    btn.classList.remove('btn-outline-success');
                }
            }, 3000);
        } else {
            btn.innerHTML = originalText;
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('A network error occurred while sending the message.');
    });
}
</script>

<?php require_once 'footer.php'; ?>