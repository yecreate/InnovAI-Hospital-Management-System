<?php
// Handle AJAX AI Summary Refresh
if (isset($_POST['ajax_refresh_summary'])) {
    require_once '../includes/db_connection.php';
    require_once '../includes/ai_engine.php';
    $patient_id = (int)$_POST['patient_id'];
    
    // Fetch Medical History
    $stmt_h = $conn->prepare("
        SELECT mh.Date, mh.Diagnosis, mh.Treatment, GROUP_CONCAT(CONCAT(pr.Medications, ' (', pr.Dosage, ')') SEPARATOR ', ') as Prescriptions
        FROM Medical_History mh
        LEFT JOIN Prescription pr ON mh.Appointment_ID = pr.Appointment_ID
        WHERE mh.Patient_ID = ?
        GROUP BY mh.Record_ID
        ORDER BY mh.Date DESC LIMIT 10
    ");
    $stmt_h->bind_param("i", $patient_id);
    $stmt_h->execute();
    $history_result = $stmt_h->get_result();
    
    $medical_data_text = "";
    while($row = $history_result->fetch_assoc()) {
        $medical_data_text .= "Date: {$row['Date']}. Diagnosis: {$row['Diagnosis']}. Treatment: {$row['Treatment']}. Prescriptions: {$row['Prescriptions']}. ";
    }
    
    // Fetch Patient Name
    $stmt_p = $conn->prepare("SELECT Fname, Lname FROM Patient WHERE Patient_ID = ?");
    $stmt_p->bind_param("i", $patient_id);
    $stmt_p->execute();
    $p = $stmt_p->get_result()->fetch_assoc();
    $patient_name = $p['Fname'] . ' ' . $p['Lname'];
    
    $summary_text = generate_medical_summary($patient_name, $medical_data_text);
    
    // Update or Insert into AI_Analysis
    $stmt_ai_check = $conn->prepare("SELECT Analysis_ID FROM AI_Analysis WHERE Patient_ID = ? ORDER BY Timestamp DESC LIMIT 1");
    $stmt_ai_check->bind_param("i", $patient_id);
    $stmt_ai_check->execute();
    $ai_row = $stmt_ai_check->get_result()->fetch_assoc();
    
    if ($ai_row) {
        $update_ai = $conn->prepare("UPDATE AI_Analysis SET Summary_Text = ? WHERE Analysis_ID = ?");
        $update_ai->bind_param("si", $summary_text, $ai_row['Analysis_ID']);
        $update_ai->execute();
    } else {
        $insert_ai = $conn->prepare("INSERT INTO AI_Analysis (Input_Symptoms, Patient_ID, Summary_Text) VALUES ('Doctor requested summary', ?, ?)");
        $insert_ai->bind_param("is", $patient_id, $summary_text);
        $insert_ai->execute();
    }
    
    echo $summary_text;
    exit;
}

// Handle AJAX AI Prescription
if (isset($_POST['ajax_ai_prescription'])) {
    require_once '../includes/db_connection.php';
    require_once '../includes/ai_engine.php';
    header('Content-Type: application/json');
    $patient_id = (int)$_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $treatment = $_POST['treatment'] ?? '';
    
    // Fetch brief history
    global $conn;
    $hist_stmt = $conn->prepare("SELECT Date, Diagnosis, Treatment FROM Medical_History WHERE Patient_ID = ? ORDER BY Date DESC LIMIT 5");
    $hist_stmt->bind_param("i", $patient_id);
    $hist_stmt->execute();
    $res = $hist_stmt->get_result();
    $history_text = "";
    while($row = $res->fetch_assoc()) {
        $history_text .= "Date: {$row['Date']}, Diagnosis: {$row['Diagnosis']}, Treatment: {$row['Treatment']}\n";
    }
    if(empty($history_text)) $history_text = "No prior history.";
    
    $clinical_exam = "Diagnosis: $diagnosis\nNotes: $notes\nTreatment: $treatment";
    
    $ai_response = generate_ai_prescription($history_text, $clinical_exam);
    echo json_encode($ai_response);
    exit;
}

require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$app_id = $_GET['app_id'] ?? 0;
$doc_id = $_SESSION['Doctor_ID'];
$msg = '';

// Process Consultation Form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finish_consultation'])) {
    $diagnosis = sanitize_input($_POST['diagnosis']);
    $notes = sanitize_input($_POST['notes']);
    $treatment = sanitize_input($_POST['treatment']);
    
    $med_list = $_POST['medications'] ?? [];
    $dosage_list = $_POST['dosage'] ?? [];
    $inst_list = $_POST['instructions'] ?? [];
    $patient_id = $_POST['patient_id'];

    $conn->begin_transaction();
    try {
        // 1. Insert Medical History
        $today = date('Y-m-d');
        $stmt_hist = $conn->prepare("INSERT INTO Medical_History (Date, Diagnosis, Notes, Treatment, Patient_ID, Doctor_ID, Appointment_ID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_hist->bind_param("ssssiii", $today, $diagnosis, $notes, $treatment, $patient_id, $doc_id, $app_id);
        $stmt_hist->execute();

        // 2. Insert Prescription (loop through arrays)
        if (!empty($med_list) && is_array($med_list)) {
            $stmt_presc = $conn->prepare("INSERT INTO Prescription (Medications, Dosage, Instructions, Appointment_ID, Doctor_ID) VALUES (?, ?, ?, ?, ?)");
            foreach ($med_list as $index => $med) {
                if (empty(trim($med))) continue;
                
                $m = sanitize_input($med);
                $d = sanitize_input($dosage_list[$index] ?? '');
                $i = sanitize_input($inst_list[$index] ?? '');
                
                $stmt_presc->bind_param("sssii", $m, $d, $i, $app_id, $doc_id);
                $stmt_presc->execute();
            }
        }

        // 3. Update Appointment Status
        $stmt_app = $conn->prepare("UPDATE Appointment SET Status = 'Completed', Diagnosis = ? WHERE Appointment_ID = ?");
        $stmt_app->bind_param("si", $diagnosis, $app_id);
        $stmt_app->execute();

        $conn->commit();
        
        // Use Javascript redirect because headers are already sent by header.php
        echo "<script>window.location.replace('consultation_success.php?app_id=" . $app_id . "');</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch Appointment and Patient Data
$stmt = $conn->prepare("
    SELECT a.*, p.*, ai.Input_Symptoms, ai.Risk_Score, ai.Predicted_Specialization, d.Specialization as DocSpec
    FROM Appointment a
    JOIN Patient p ON a.Patient_ID = p.Patient_ID
    JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID
    LEFT JOIN AI_Analysis ai ON p.Patient_ID = ai.Patient_ID AND DATE(ai.Timestamp) = a.Date
    WHERE a.Appointment_ID = ? AND a.Doctor_ID = ?
");
$stmt->bind_param("ii", $app_id, $doc_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die('Invalid or inaccessible appointment.');

// Fetch Past Medical History
$hist_stmt = $conn->prepare("SELECT Date, Diagnosis, Notes, Treatment FROM Medical_History WHERE Patient_ID = ? ORDER BY Date DESC LIMIT 5");
$hist_stmt->bind_param("i", $data['Patient_ID']);
$hist_stmt->execute();
$history_result = $hist_stmt->get_result();
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="text-primary"><i class="fas fa-user-injured me-2"></i> Clinical Consultation</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Queue</a>
    </div>
</div>

<?php echo $msg; ?>

<div class="row">
    <!-- Left Column: Patient Info & AI Triage & Medical History -->
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm border-info">
            <div class="card-header bg-info text-white"><i class="fas fa-id-card me-2"></i> Patient Overview</div>
            <div class="card-body">
                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($data['Fname'] . ' ' . $data['Lname']); ?></h5>
                <p class="mb-1"><strong>Age:</strong> <?php echo $data['Age']; ?> yrs | <strong>Gender:</strong> <?php echo $data['Gender']; ?></p>
                <p class="mb-1"><strong>Blood Group:</strong> <?php echo $data['Blood_Group'] ?: 'N/A'; ?></p>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-danger">
            <div class="card-header bg-danger text-white"><i class="fas fa-robot me-2"></i> AI Triage Data</div>
            <div class="card-body bg-light">
                <?php if($data['Input_Symptoms']): ?>
                    <h6>Reported Symptoms:</h6>
                    <p class="fst-italic border-start border-3 border-danger ps-2">"<?php echo htmlspecialchars($data['Input_Symptoms']); ?>"</p>
                    <hr>
                    <p class="mb-1"><strong>Risk Score:</strong> <span class="badge bg-danger fs-6"><?php echo $data['Risk_Score']; ?>/10</span></p>
                    <p class="mb-0"><strong>AI Predicted Dept:</strong> <?php echo $data['Predicted_Specialization']; ?></p>
                <?php else: ?>
                    <p class="text-muted fst-italic">No AI Triage data available for this encounter.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- NEW: Past Medical History -->
        <div class="card shadow-sm border-secondary mb-4">
            <div class="card-header bg-secondary text-white"><i class="fas fa-history me-2"></i> Past Medical History</div>
            <div class="card-body bg-light p-0" style="max-height: 300px; overflow-y: auto;">
                <?php if($history_result->num_rows > 0): ?>
                    <ul class="list-group list-group-flush">
                    <?php while($h = $history_result->fetch_assoc()): ?>
                        <li class="list-group-item bg-transparent px-3 py-2 border-bottom">
                            <strong class="text-primary"><?php echo $h['Date']; ?></strong>: <?php echo htmlspecialchars($h['Diagnosis']); ?><br>
                            <small class="text-muted"><strong>Treatment:</strong> <?php echo htmlspecialchars($h['Treatment']); ?></small><br>
                            <small class="text-muted"><strong>Notes:</strong> <?php echo htmlspecialchars($h['Notes']); ?></small>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted fst-italic p-3 mb-0">No prior medical history found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Medical History Form & Prescription -->
    <div class="col-md-8">
        <form method="POST">
            <input type="hidden" name="patient_id" value="<?php echo $data['Patient_ID']; ?>">
            
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><i class="fas fa-notes-medical me-2"></i> 1. Clinical Examination</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Diagnosis</label>
                        <input type="text" name="diagnosis" class="form-control" placeholder="Primary diagnosis..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Clinical Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Observations, vital signs, etc..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Treatment Plan (Non-Medication)</label>
                        <input type="text" name="treatment" class="form-control" placeholder="Rest, physical therapy, etc...">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-success mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-pills me-2"></i> 2. Prescription</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-light text-success fw-bold me-2 shadow-sm" onclick="suggestMedications('<?php echo $data['DocSpec']; ?>')">🤖 Auto-Generate AI Prescription</button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="addMedicationBlock()"><i class="fas fa-plus"></i> Add Med</button>
                    </div>
                </div>
                <div class="card-body" id="prescriptionContainer">
                    <div class="prescription-block border-bottom pb-3 mb-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Medication</label>
                            <input type="text" name="medications[]" class="form-control med-input" placeholder="e.g. Amoxicillin">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dosage</label>
                                <input type="text" name="dosage[]" class="form-control dose-input" placeholder="e.g. 500mg">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Instructions</label>
                                <input type="text" name="instructions[]" class="form-control inst-input" placeholder="e.g. 3 times daily after meals">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" name="finish_consultation" class="btn btn-primary btn-lg w-100"><i class="fas fa-check-double me-2"></i> Complete Consultation</button>
        </form>
    </div>
</div>

<script>
function addMedicationBlock(med = '', dose = '', inst = '') {
    const container = document.getElementById('prescriptionContainer');
    const block = document.createElement('div');
    block.className = 'prescription-block border-bottom pb-3 mb-3';
    block.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">Medication</label>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest('.prescription-block').remove()"><i class="fas fa-times"></i></button>
        </div>
        <input type="text" name="medications[]" class="form-control med-input mb-3" placeholder="e.g. Amoxicillin" value="${med}">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Dosage</label>
                <input type="text" name="dosage[]" class="form-control dose-input" placeholder="e.g. 500mg" value="${dose}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Instructions</label>
                <input type="text" name="instructions[]" class="form-control inst-input" placeholder="e.g. 3 times daily after meals" value="${inst}">
            </div>
        </div>
    `;
    container.appendChild(block);
    return block;
}

// Innovation: AI Prescription Helper (UI Logic)
function suggestMedications(specialization) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Thinking...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('ajax_ai_prescription', '1');
    formData.append('patient_id', document.querySelector('input[name="patient_id"]').value);
    formData.append('diagnosis', document.querySelector('input[name="diagnosis"]').value);
    formData.append('notes', document.querySelector('textarea[name="notes"]').value);
    formData.append('treatment', document.querySelector('input[name="treatment"]').value);

    fetch('consultation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if (!Array.isArray(data) || data.length === 0) {
            alert('AI returned an empty prescription.');
            return;
        }
        
        // Clear existing empty blocks
        const container = document.getElementById('prescriptionContainer');
        const existingMeds = container.querySelectorAll('.med-input');
        if (existingMeds.length === 1 && existingMeds[0].value === '') {
            container.innerHTML = ''; 
        }

        data.forEach(s => {
            let newBlock = addMedicationBlock(s.med || '', s.dose || '', s.inst || '');
            newBlock.style.backgroundColor = "#d1e7dd";
            setTimeout(() => { newBlock.style.backgroundColor = ""; }, 800);
        });
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Failed to connect to AI Engine.');
    });
}

function refreshAISummary(patientId) {
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('ajax_refresh_summary', '1');
    formData.append('patient_id', patientId);

    fetch('consultation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        document.getElementById('aiSummaryText').innerText = '"' + text.trim() + '"';
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Failed to update summary.');
    });
}
</script>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>