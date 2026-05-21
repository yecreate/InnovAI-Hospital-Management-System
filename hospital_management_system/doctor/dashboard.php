<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$doc_id = $_SESSION['Doctor_ID'];
$today = date('Y-m-d');

// Handle "Mark as Ready" action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_ready'])) {
    $app_id = $_POST['appointment_id'];
    $stmt_ready = $conn->prepare("UPDATE Appointment SET Status = 'Ready' WHERE Appointment_ID = ? AND Doctor_ID = ?");
    $stmt_ready->bind_param("ii", $app_id, $doc_id);
    $stmt_ready->execute();
    header("Location: dashboard.php");
    exit;
}

// AI Priority Queue Query
$queue_query = "
    SELECT a.Appointment_ID, a.Time, a.Status, p.Patient_ID, p.Fname, p.Lname, p.Age, p.Blood_Group,
           ai.Risk_Score, ai.Input_Symptoms, ai.Predicted_Specialization
    FROM Appointment a
    JOIN Patient p ON a.Patient_ID = p.Patient_ID
    LEFT JOIN AI_Analysis ai ON p.Patient_ID = ai.Patient_ID AND DATE(ai.Timestamp) = '$today'
    WHERE a.Doctor_ID = ? AND a.Date = ? AND a.Status IN ('Scheduled', 'Checked-In', 'Ready')
    ORDER BY 
        CASE 
            WHEN a.Status = 'Ready' THEN 1 
            WHEN a.Status = 'Checked-In' THEN 2 
            ELSE 3 
        END ASC,
        IFNULL(ai.Risk_Score, 0) DESC, 
        a.Time ASC
";

$stmt = $conn->prepare($queue_query);
$stmt->bind_param("is", $doc_id, $today);
$stmt->execute();
$queue = $stmt->get_result();

// Function to fetch history inline
function getPatientHistory($conn, $patient_id) {
    $res = $conn->query("SELECT Date, Diagnosis, Treatment FROM Medical_History WHERE Patient_ID = $patient_id ORDER BY Date DESC LIMIT 3");
    $history = [];
    while($row = $res->fetch_assoc()) $history[] = $row;
    return $history;
}

function getPatientPrescriptions($conn, $patient_id) {
    $res = $conn->query("SELECT pr.Medications, pr.Dosage, a.Date FROM Prescription pr JOIN Appointment a ON pr.Appointment_ID = a.Appointment_ID WHERE a.Patient_ID = $patient_id ORDER BY a.Date DESC LIMIT 3");
    $prescriptions = [];
    while($row = $res->fetch_assoc()) $prescriptions[] = $row;
    return $prescriptions;
}
?>

<style>
    .patient-card { transition: all 0.3s ease; border-left: 5px solid transparent; }
    .patient-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .card-urgent { border-left-color: #dc3545; background-color: #fffafb; }
    .card-ready { border-left-color: #198754; }
    .card-waiting { border-left-color: #0dcaf0; }
    
    .collapse-content { background-color: #f8f9fa; border-top: 1px solid #e9ecef; }
</style>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="pb-2 border-bottom text-primary"><i class="fas fa-clipboard-list me-2"></i> AI Priority Patient Queue</h2>
        <p class="text-muted">Patients are sorted by Status (Ready > Checked-In) and AI-determined Risk Score.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php if($queue->num_rows == 0): ?>
            <div class="alert alert-info text-center py-5 shadow-sm">
                <i class="fas fa-mug-hot fa-3x mb-3 text-muted"></i>
                <h5>Queue is empty.</h5>
                <p class="mb-0 text-muted">You have no pending consultations at the moment.</p>
            </div>
        <?php endif; ?>

        <div class="accordion" id="queueAccordion">
            <?php 
            $i = 0;
            while($row = $queue->fetch_assoc()): 
                $i++;
                $is_urgent = ($row['Risk_Score'] >= 8);
                $card_class = 'card-waiting';
                if ($row['Status'] == 'Ready') $card_class = 'card-ready';
                elseif ($is_urgent) $card_class = 'card-urgent';
            ?>
                <div class="card patient-card mb-3 shadow-sm <?php echo $card_class; ?>">
                    <div class="card-header bg-white p-0 border-0" id="heading<?php echo $i; ?>">
                        <div class="d-flex justify-content-between align-items-center p-3" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="false" aria-controls="collapse<?php echo $i; ?>" style="cursor: pointer;">
                            <div class="d-flex align-items-center">
                                <div class="me-4 text-center" style="width: 80px;">
                                    <small class="text-muted d-block">Time</small>
                                    <strong><?php echo date('h:i A', strtotime($row['Time'])); ?></strong>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-primary fw-bold"><?php echo htmlspecialchars($row['Fname'] . ' ' . $row['Lname']); ?></h5>
                                    <span class="text-muted small">Age: <?php echo $row['Age']; ?> | Blood: <span class="text-danger fw-bold"><?php echo $row['Blood_Group'] ?: '?'; ?></span></span>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3">
                                <?php if($is_urgent): ?>
                                    <span class="badge badge-urgent"><i class="fas fa-exclamation-triangle"></i> URGENT (<?php echo $row['Risk_Score']; ?>/10)</span>
                                <?php elseif($row['Risk_Score']): ?>
                                    <span class="badge bg-secondary text-light">Risk: <?php echo $row['Risk_Score']; ?>/10</span>
                                <?php endif; ?>

                                <?php if($row['Status'] == 'Ready'): ?>
                                    <span class="badge bg-success px-3 py-2"><i class="fas fa-door-open me-1"></i> Ready for Doctor</span>
                                <?php elseif($row['Status'] == 'Checked-In'): ?>
                                    <span class="badge bg-info text-dark px-3 py-2"><i class="fas fa-check-circle me-1"></i> Checked-In</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-clock me-1"></i> Scheduled</span>
                                <?php endif; ?>
                                
                                <i class="fas fa-chevron-down text-muted ms-2"></i>
                            </div>
                        </div>
                    </div>

                    <div id="collapse<?php echo $i; ?>" class="collapse" aria-labelledby="heading<?php echo $i; ?>" data-bs-parent="#queueAccordion">
                        <div class="card-body collapse-content p-4">
                            <div class="row">
                                <!-- Triage Summary -->
                                <div class="col-md-4 border-end">
                                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-robot me-2"></i> AI Triage Summary</h6>
                                    <?php if($row['Input_Symptoms']): ?>
                                        <div class="bg-white p-3 rounded shadow-sm border border-light mb-3">
                                            <p class="fst-italic text-muted mb-2 small">"<?php echo htmlspecialchars($row['Input_Symptoms']); ?>"</p>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between small">
                                                <span><strong>Specialization:</strong></span>
                                                <span class="text-primary"><?php echo $row['Predicted_Specialization']; ?></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small fst-italic">No AI Triage data for this visit.</p>
                                    <?php endif; ?>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <?php if($row['Status'] == 'Checked-In'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['Appointment_ID']; ?>">
                                                <button type="submit" name="mark_ready" class="btn btn-outline-success w-100"><i class="fas fa-bullhorn me-2"></i> Mark "Ready for Doctor"</button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if(in_array($row['Status'], ['Checked-In', 'Ready'])): ?>
                                            <a href="consultation.php?app_id=<?php echo $row['Appointment_ID']; ?>" class="btn btn-primary w-100"><i class="fas fa-stethoscope me-2"></i> Start Consultation</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>Patient Not Arrived</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Past History & Prescriptions -->
                                <div class="col-md-8 px-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-history me-2"></i> Recent History</h6>
                                            <ul class="list-unstyled">
                                                <?php 
                                                $history = getPatientHistory($conn, $row['Patient_ID']);
                                                if(empty($history)) echo "<li class='text-muted small'>No past history found.</li>";
                                                foreach($history as $h): ?>
                                                    <li class="mb-2 bg-white p-2 rounded shadow-sm border border-light">
                                                        <div class="d-flex justify-content-between">
                                                            <strong class="text-dark small"><?php echo htmlspecialchars($h['Diagnosis']); ?></strong>
                                                            <span class="text-muted" style="font-size:0.75rem;"><?php echo date('M d, Y', strtotime($h['Date'])); ?></span>
                                                        </div>
                                                        <div class="text-muted" style="font-size:0.8rem;"><?php echo htmlspecialchars($h['Treatment']); ?></div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-pills me-2"></i> Recent Prescriptions</h6>
                                            <ul class="list-unstyled">
                                                <?php 
                                                $presc = getPatientPrescriptions($conn, $row['Patient_ID']);
                                                if(empty($presc)) echo "<li class='text-muted small'>No recent prescriptions.</li>";
                                                foreach($presc as $p): ?>
                                                    <li class="mb-2 bg-white p-2 rounded shadow-sm border border-light">
                                                        <div class="d-flex justify-content-between">
                                                            <strong class="text-success small"><?php echo htmlspecialchars($p['Medications']); ?></strong>
                                                            <span class="text-muted" style="font-size:0.75rem;"><?php echo date('M d, Y', strtotime($p['Date'])); ?></span>
                                                        </div>
                                                        <div class="text-muted" style="font-size:0.8rem;">Dose: <?php echo htmlspecialchars($p['Dosage']); ?></div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>