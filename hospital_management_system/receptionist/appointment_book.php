<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';
require_once '../includes/whatsapp_helper.php'; // Injects our background notification function

$msg = '';
$recp_id = $_SESSION['Receptionist_ID'];

// Handle unified form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    $conn->begin_transaction();
    try {
        $patient_id = $_POST['patient_id'];
        
        // If patient_id is empty, it means we need to register a new patient first
        if (empty($patient_id)) {
            $fname = sanitize_input($_POST['new_fname']);
            $lname = sanitize_input($_POST['new_lname']);
            $email = sanitize_input($_POST['new_email']);
            $dob = $_POST['new_dob'];
            $gender = $_POST['new_gender'];
            $phone = sanitize_input($_POST['new_phone']);
            $blood = sanitize_input($_POST['new_blood']);
            $city = sanitize_input($_POST['new_city']);
            $street = sanitize_input($_POST['new_street']);
            $building = sanitize_input($_POST['new_building']);
            $emergency = sanitize_input($_POST['new_emergency']);
            
            $password = password_hash('Password123', PASSWORD_DEFAULT); // Default password for recp-registered patients
            
            // Register Login
            $stmt1 = $conn->prepare("INSERT INTO User_Login (Email, Password, Role) VALUES (?, ?, 'Patient')");
            $stmt1->bind_param("ss", $email, $password);
            $stmt1->execute();
            $user_id = $conn->insert_id;
            
            // Register Patient (Age omitted for DB Trigger)
            $stmt2 = $conn->prepare("INSERT INTO Patient (Fname, Lname, Gender, Date_of_Birth, City, Street, Building, Blood_Group, Emergency_Contact, Receptionist_ID, User_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssssssssii", $fname, $lname, $gender, $dob, $city, $street, $building, $blood, $emergency, $recp_id, $user_id);
            $stmt2->execute();
            $patient_id = $conn->insert_id;
            
            // Register Phone
            if(!empty($phone)) {
                $stmt3 = $conn->prepare("INSERT INTO Patient_phones (Patient_ID, PhoneNumber) VALUES (?, ?)");
                $stmt3->bind_param("is", $patient_id, $phone);
                $stmt3->execute();
            }
            $msg .= "Patient registered successfully. ";
        }

        // Now book the appointment
        $doc_id = $_POST['doctor_id'];
        $date = $_POST['app_date'];
        $time = $_POST['app_time'];
        
        $stmt_app = $conn->prepare("INSERT INTO Appointment (Date, Time, Patient_ID, Doctor_ID, Receptionist_ID) VALUES (?, ?, ?, ?, ?)");
        $stmt_app->bind_param("ssiii", $date, $time, $patient_id, $doc_id, $recp_id);
        $stmt_app->execute();
        $app_id = $conn->insert_id;

        // Commit transaction to Database
        $conn->commit();

        // --- BACKGROUND WHATSAPP NOTIFICATION INJECTION ---
        // Fetch Patient name details, multi-valued mobile record rows, and Doctor name
        $lookup_sql = "SELECT p.Fname AS PatientFname, ph.PhoneNumber, d.Fname AS DocFname, d.Lname AS DocLname 
                       FROM Patient p
                       JOIN Patient_phones ph ON p.Patient_ID = ph.Patient_ID 
                       JOIN Doctor d ON d.Doctor_ID = " . intval($doc_id) . "
                       WHERE p.Patient_ID = " . intval($patient_id) . " LIMIT 1";
        
        $lookup_result = $conn->query($lookup_sql);
        if ($lookup_result && $lookup_result->num_rows > 0) {
            $patient_record = $lookup_result->fetch_assoc();
            
            $patient_first_name = $patient_record['PatientFname']; 
            $target_phone       = $patient_record['PhoneNumber'];
            $doc_fname          = $patient_record['DocFname'];
            $doc_lname          = $patient_record['DocLname'];

            // Generate the physical PDF file first so Node.js can read it from the hard drive
            $localFilePath = generate_appointment_slip_pdf($app_id, $conn);

            if ($localFilePath) {
                // Trigger background microservice post verification pipeline
                sendWhatsAppMessage($target_phone, $patient_first_name, $date, $time, "Dr. " . $doc_fname . " " . $doc_lname, $localFilePath, "document");
            } else {
                // Fallback to text only if PDF generation fails
                sendWhatsAppMessage($target_phone, $patient_first_name, $date, $time, "Dr. " . $doc_fname . " " . $doc_lname);
            }
        }
        // --- END WHATSAPP NOTIFICATION INJECTION ---

        // Success Alert UI
        $msg .= "<div class='alert alert-success border-0 border-start border-4 border-success shadow-sm mt-3'>
                    <h5 class='fw-bold mb-1'><i class='fas fa-check-circle me-2'></i> Success</h5>
                    Appointment securely booked and WhatsApp notification dispatched to patient. 
                    <a href='print_slip.php?id=$app_id' target='_blank' class='alert-link text-decoration-underline ms-2'>Print Local Slip</a>
                 </div>";
        
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "<div class='alert alert-danger border-0 border-start border-4 border-danger shadow-sm mt-3'><i class='fas fa-exclamation-triangle me-2'></i> System Error during transaction. Please check logs.</div>";
    }
}

$doctors = $conn->query("SELECT d.Doctor_ID, d.Fname, d.Lname, d.Specialization, dept.Name as DeptName FROM Doctor d JOIN Department dept ON d.Department_ID = dept.Department_ID ORDER BY d.Fname ASC");
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold" style="color: var(--primary-blue-dark);"><i class="fas fa-user-plus me-2 text-primary"></i> Patient Registration & Booking</h2>
        <p class="text-muted fs-6 mb-0">Search existing patients or securely register new arrivals to create an appointment.</p>
    </div>
</div>

<?php echo $msg; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h5 class="fw-bold text-dark"><i class="fas fa-search me-2 text-primary"></i> 1. Select Patient</h5>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="input-group mb-4 shadow-sm border rounded-pill overflow-hidden">
                    <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="patientSearchInput" class="form-control border-0 py-3" placeholder="Search by Phone, Name or Email..." onkeyup="searchPatients()">
                </div>
                
                <div id="searchResults" class="list-group mb-4 shadow-sm border-0" style="max-height: 350px; overflow-y: auto; border-radius: 12px;">
                    <div class="p-4 text-center bg-light text-muted" id="searchHint">
                        <i class="fas fa-users mb-2 fs-4 opacity-50"></i><br>
                        Type to search database...
                    </div>
                </div>
                
                <div class="text-center mt-auto pt-3 border-top">
                    <p class="text-muted small fw-semibold text-uppercase tracking-wider mb-3">Patient Not Found?</p>
                    <button type="button" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-bold" onclick="toggleNewPatientForm()"><i class="fas fa-user-plus me-2"></i> Register New Patient</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                <h5 class="fw-bold text-dark"><i class="fas fa-calendar-alt me-2 text-primary"></i> 2. Appointment Configuration</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" id="bookingForm">
                    <input type="hidden" name="patient_id" id="selectedPatientId" value="">
                    
                    <div id="selectedPatientAlert" class="alert d-flex align-items-center mb-4 border-0 shadow-sm" style="display:none; background-color: var(--soft-blue); border-left: 4px solid var(--primary-blue) !important;">
                        <i class="fas fa-user-check fa-2x text-primary me-3"></i>
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Selected Patient</small>
                            <h5 class="mb-0 fw-bold text-dark" id="selectedPatientName"></h5>
                        </div>
                    </div>

                    <div id="newPatientForm" class="bg-light p-4 rounded-4 mb-4 border" style="display:none;">
                        <h6 class="text-primary fw-bold text-uppercase mb-3 pb-2 border-bottom"><i class="fas fa-id-card me-2"></i> Registration Demographics</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="new_fname" id="new_fname" class="form-control" placeholder="First Name" required>
                                    <label>First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="new_lname" id="new_lname" class="form-control" placeholder="Last Name" required>
                                    <label>Last Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="new_email" id="new_email" class="form-control" placeholder="Email" required>
                                    <label>Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="new_phone" id="new_phone" class="form-control" placeholder="Phone" required>
                                    <label>Mobile Number</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="date" name="new_dob" id="new_dob" class="form-control" required>
                                    <label>Date of Birth</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select name="new_gender" id="new_gender" class="form-select" required>
                                        <option value="" disabled selected>Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <label>Sex</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select name="new_blood" id="new_blood" class="form-select">
                                        <option value="" selected>Unknown</option>
                                        <option value="A+">A+</option><option value="A-">A-</option>
                                        <option value="B+">B+</option><option value="B-">B-</option>
                                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                        <option value="O+">O+</option><option value="O-">O-</option>
                                    </select>
                                    <label>Blood Type</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="new_city" id="new_city" class="form-control" placeholder="City">
                                    <label>City</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="new_street" id="new_street" class="form-control" placeholder="Street">
                                    <label>Street</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="new_building" id="new_building" class="form-control" placeholder="Building">
                                    <label>Bldg / Apt</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating">
                            <input type="text" name="new_emergency" id="new_emergency" class="form-control" placeholder="Emergency Contact Name/Phone">
                            <label>Emergency Contact (Name/Phone)</label>
                        </div>
                    </div>

                    <h6 class="text-dark fw-bold text-uppercase mb-3 pb-2 border-bottom"><i class="fas fa-user-md me-2 text-muted"></i> Clinical Routing</h6>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small">Target Specialist</label>
                        <select name="doctor_id" class="form-select py-3 bg-light border-0 shadow-sm" required style="border-radius: 12px;">
                            <option value="" disabled selected>Select Physician...</option>
                            <?php 
                            $current_dept = '';
                            while($doc = $doctors->fetch_assoc()): 
                                if($doc['DeptName'] != $current_dept) {
                                    if($current_dept != '') echo "</optgroup>";
                                    echo "<optgroup label='".htmlspecialchars($doc['DeptName'])."'>";
                                    $current_dept = $doc['DeptName'];
                                }
                            ?>
                                <option value="<?php echo $doc['Doctor_ID']; ?>">Dr. <?php echo htmlspecialchars($doc['Fname'] . ' ' . $doc['Lname']); ?> (<?php echo htmlspecialchars($doc['Specialization']); ?>)</option>
                            <?php endwhile; ?>
                            <?php if($current_dept != '') echo "</optgroup>"; ?>
                        </select>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small">Date</label>
                            <input type="date" name="app_date" class="form-control py-3 bg-light border-0 shadow-sm" required min="<?php echo date('Y-m-d'); ?>" style="border-radius: 12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted small">Time</label>
                            <input type="time" name="app_time" class="form-control py-3 bg-light border-0 shadow-sm" required style="border-radius: 12px;">
                        </div>
                    </div>
                    
                    <button type="submit" name="book_appointment" id="submitBtn" class="btn btn-primary w-100 py-3 mt-2 rounded-pill fw-bold text-uppercase" style="letter-spacing: 1px;"><i class="fas fa-check-double me-2"></i> Finalize Booking & Dispatch Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Load all patients on page load for the list
document.addEventListener("DOMContentLoaded", function() {
    searchPatients(''); // Empty search returns top 50
});

function searchPatients(query = null) {
    const input = query !== null ? query : document.getElementById('patientSearchInput').value;
    const resultsContainer = document.getElementById('searchResults');
    const hint = document.getElementById('searchHint');
    if (hint) hint.style.display = 'none';
    
    fetch(`search_patients_ajax.php?q=${encodeURIComponent(input)}`)
        .then(response => response.json())
        .then(data => {
            resultsContainer.innerHTML = '';
            if(data.length === 0) {
                resultsContainer.innerHTML = '<div class="p-4 text-center bg-light text-muted border-0 rounded-3">No matching patient records found.</div>';
                return;
            }
            data.forEach(patient => {
                const a = document.createElement('a');
                a.href = "javascript:void(0)";
                a.className = "list-group-item list-group-item-action border-0 border-bottom p-3 hover-bg-light";
                a.onclick = () => selectPatient(patient.Patient_ID, patient.Fname + ' ' + patient.Lname);
                a.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 fw-bold" style="color: var(--primary-blue-dark);">${patient.Fname} ${patient.Lname}</h6>
                        <span class="badge bg-secondary rounded-pill">ID: ${patient.Patient_ID}</span>
                    </div>
                    <div class="small text-muted">
                        <span class="me-3"><i class="fas fa-phone me-1"></i> ${patient.PhoneNumber || 'N/A'}</span>
                        <span><i class="fas fa-envelope me-1"></i> ${patient.Email}</span>
                    </div>
                `;
                resultsContainer.appendChild(a);
            });
        })
        .catch(err => {
            resultsContainer.innerHTML = '<div class="text-danger p-3 text-center">Connection error.</div>';
        });
}

function toggleNewPatientForm() {
    document.getElementById('newPatientForm').style.display = 'block';
    document.getElementById('selectedPatientAlert').style.display = 'none';
    document.getElementById('selectedPatientId').value = '';
    
    // Make registration fields required when creating a new patient
    document.getElementById('new_fname').required = true;
    document.getElementById('new_lname').required = true;
    document.getElementById('new_email').required = true;
    document.getElementById('new_phone').required = true;
    document.getElementById('new_dob').required = true;
    document.getElementById('new_gender').required = true;
}

function selectPatient(id, name) {
    document.getElementById('selectedPatientId').value = id;
    document.getElementById('selectedPatientName').innerText = name;
    document.getElementById('selectedPatientAlert').style.display = 'flex';
    document.getElementById('newPatientForm').style.display = 'none';
    
    // Remove required tags from new patient fields when selecting an existing one
    document.getElementById('new_fname').required = false;
    document.getElementById('new_lname').required = false;
    document.getElementById('new_email').required = false;
    document.getElementById('new_phone').required = false;
    document.getElementById('new_dob').required = false;
    document.getElementById('new_gender').required = false;
}
</script>

<style>
.hover-bg-light:hover { background-color: var(--soft-blue) !important; transition: background-color 0.2s; }
</style>

<?php require_once 'footer.php'; ?>