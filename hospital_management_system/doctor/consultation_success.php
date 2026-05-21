<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$app_id = (int)($_GET['app_id'] ?? 0);
$doc_id = $_SESSION['Doctor_ID'];

// Validate
$stmt = $conn->prepare("SELECT p.Fname, p.Lname, p.Patient_ID FROM Appointment a JOIN Patient p ON a.Patient_ID = p.Patient_ID WHERE a.Appointment_ID = ? AND a.Doctor_ID = ?");
$stmt->bind_param("ii", $app_id, $doc_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<div class='alert alert-danger'>Invalid appointment.</div>";
    require_once 'footer.php';
    exit;
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
        <div class="card shadow border-success">
            <div class="card-body py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h2 class="text-success mb-3">Consultation Complete!</h2>
                <p class="text-muted mb-4">You have successfully completed the consultation for <strong><?php echo htmlspecialchars($data['Fname'] . ' ' . $data['Lname']); ?></strong>.</p>
                
                <div class="d-grid gap-3 col-8 mx-auto">
                    <button class="btn btn-lg btn-success" id="btn-wa" onclick="sendPrescriptionToWhatsApp(<?php echo $app_id; ?>)">
                        <i class="fab fa-whatsapp me-2"></i> Send Prescription to WhatsApp
                    </button>
                    <a href="view_prescription.php?app_id=<?php echo $app_id; ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf me-2"></i> View/Print Prescription PDF
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Return to Queue
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendPrescriptionToWhatsApp(appId) {
    const btn = document.getElementById('btn-wa');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('app_id', appId);

    fetch('send_prescription_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if (data.status === 'success') {
            btn.innerHTML = '<i class="fas fa-check me-2"></i> Sent!';
            btn.classList.replace('btn-success', 'btn-outline-success');
        } else {
            btn.innerHTML = originalText;
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('A network error occurred while sending.');
    });
}
</script>

<?php require_once 'footer.php'; ?>