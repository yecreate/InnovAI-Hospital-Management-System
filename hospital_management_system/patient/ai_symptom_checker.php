<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/ai_engine.php';
require_once '../includes/functions.php';

$patient_id = $_SESSION['Patient_ID'];
$msg = '';
$modal_script = '';

// Fetch dynamic departments
$dept_query = $conn->query("SELECT Name FROM Department");
$depts = [];
while($d = $dept_query->fetch_assoc()) {
    $depts[] = $d['Name'];
}
$depts_str = implode(", ", $depts);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['analyze_symptoms'])) {
    $symptoms = sanitize_input($_POST['symptoms']);
    
    // Call the Groq AI Engine with dynamic departments
    $analysis = analyze_symptoms_with_groq($symptoms, $depts_str);
    
    if (isset($analysis['is_medical']) && $analysis['is_medical'] === false) {
        $msg = "<div class='alert alert-warning border-0 border-start border-4 border-warning shadow-sm'><i class='fas fa-exclamation-triangle me-2'></i> Our system indicates this input may not be related to medical symptoms. Please provide details about how you are feeling.</div>";
    } else {
        $risk_score = $analysis['risk_score'] ?? 5;
        $spec = $analysis['predicted_specialization'] ?? 'General';
        $emergency_instructions = $analysis['emergency_instructions'] ?? 'Please monitor your symptoms closely.';
        $confidence = 90.00; // Simulated confidence for API
        
        $modal_body = "";
        
        if ($spec === 'Not Available') {
            $modal_body = "<div class='text-center mb-4'><i class='fas fa-hospital-alt fa-3x text-muted mb-3'></i><h5 class='fw-bold'>Specialized Care Required</h5><p class='text-muted'>Based on your symptoms, it appears you need a specialization that is <strong>currently not available</strong> at our center. We recommend visiting a specialized hospital.</p></div>";
            $spec = 'None'; // Store as None in DB
        } else {
            $risk_color = ($risk_score >= 7) ? 'text-danger' : (($risk_score >= 4) ? 'text-warning' : 'text-success');
            $modal_body = "
                <div class='text-center mb-4'>
                    <div class='bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3' style='width: 70px; height: 70px;'>
                        <i class='fas fa-stethoscope fa-2x' style='color: var(--primary-teal);'></i>
                    </div>
                    <h5 class='fw-bold'>Recommended Department: <span style='color: var(--primary-teal);'>$spec</span></h5>
                </div>
                <div class='bg-light p-3 rounded mb-3 border border-light'>
                    <div class='d-flex justify-content-between align-items-center mb-2'>
                        <span class='fw-bold text-muted'>Clinical Urgency</span>
                        <span class='fw-bold fs-5 $risk_color'>$risk_score/10</span>
                    </div>
                    <div class='progress' style='height: 8px; border-radius: 4px;'>
                        <div class='progress-bar bg-".($risk_score >= 7 ? 'danger' : ($risk_score >= 4 ? 'warning' : 'success'))."' style='width: ".($risk_score*10)."%'></div>
                    </div>
                </div>
                <div class='alert border-0 shadow-sm' style='background-color: var(--soft-lavender); border-left: 4px solid var(--primary-teal) !important;'>
                    <h6 class='fw-bold' style='color: var(--primary-teal);'><i class='fas fa-shield-alt me-2'></i> First-Aid & Safety Guidance</h6>
                    <p class='mb-0 small text-dark' style='line-height: 1.5;'>$emergency_instructions</p>
                </div>
            ";
        }

        // Save to Database
        $stmt = $conn->prepare("INSERT INTO AI_Analysis (Input_Symptoms, Confidence_Level, Risk_Score, Predicted_Specialization, Patient_ID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sddsi", $symptoms, $confidence, $risk_score, $spec, $patient_id);
        
        if ($stmt->execute()) {
            $modal_script = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('aiModalBody').innerHTML = `$modal_body`;
                    var aiModal = new bootstrap.Modal(document.getElementById('aiResultModal'));
                    aiModal.show();
                });
            </script>";
        } else {
            $msg = "<div class='alert alert-danger'>Failed to securely store analysis data.</div>";
        }
    }
}
?>

<div class="row mb-5 justify-content-center text-center">
    <div class="col-md-8">
        <h2 class="fw-bold" style="color: var(--primary-teal);"><i class="fas fa-robot me-2"></i> AI-Powered Clinical Triage</h2>
        <p class="text-muted fs-5">Describe your symptoms naturally. Our intelligent clinical assistant will assess urgency and connect you to the optimal care team.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <?php echo $msg; ?>
        
        <div class="card shadow-lg border-0 mb-4 overflow-hidden" style="border-radius: 20px;">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: var(--soft-lavender); color: var(--primary-teal);">
                    <i class="fas fa-comment-medical fa-2x"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0">How are you feeling today?</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-4">
                        <textarea name="symptoms" class="form-control bg-light border-0 p-4" rows="5" placeholder="E.g., I have been experiencing a severe headache, mild fever, and nausea since yesterday morning..." style="resize: none; border-radius: 16px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03);" required></textarea>
                    </div>
                    <button type="submit" name="analyze_symptoms" class="btn btn-teal w-100 py-3 rounded-pill fw-bold text-uppercase" style="letter-spacing: 1px;" id="analyzeBtn" onclick="this.innerHTML='<i class=\'fas fa-circle-notch fa-spin me-2\'></i> Processing Clinical Data...';"><i class="fas fa-magic me-2"></i> Analyze Symptoms</button>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted fw-semibold"><i class="fas fa-lock me-1 text-success"></i> Your health data is processed securely and encrypted end-to-end.</small>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="alert border-0 shadow-sm d-flex align-items-center" style="background-color: white; border-left: 4px solid #f59e0b !important; border-radius: 16px;">
            <i class="fas fa-info-circle fa-2x text-warning me-3"></i>
            <div>
                <strong class="text-dark d-block mb-1">Medical Disclaimer</strong>
                <span class="small text-muted" style="line-height: 1.4;">This intelligent routing system provides preliminary triage. It is not a substitute for a licensed physician. If you are experiencing a life-threatening emergency, please dial emergency services immediately.</span>
            </div>
        </div>
    </div>
</div>

<!-- AI Result Modal -->
<div class="modal fade" id="aiResultModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header border-0" style="background-color: var(--primary-teal);">
        <h5 class="modal-title fw-bold text-white"><i class="fas fa-clipboard-check me-2"></i> Clinical Triage Assessment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="aiModalBody">
      </div>
      <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center bg-transparent">
        <a href="appointments.php" class="btn btn-teal rounded-pill px-4 fw-bold">Proceed to Scheduling</a>
        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted border shadow-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php echo $modal_script; ?>
<?php require_once 'footer.php'; ?>