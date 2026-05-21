<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$patient_id = $_SESSION['Patient_ID'];
$msg = '';

// Handle Manual Booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    $doc_id = $_POST['doctor_id'];
    $date = $_POST['app_date'];
    $time = $_POST['app_time'];
    
    // Check for double booking (same doctor, same date/time)
    $check = $conn->prepare("SELECT Appointment_ID FROM Appointment WHERE Doctor_ID = ? AND Date = ? AND Time = ? AND Status != 'Cancelled'");
    $check->bind_param("iss", $doc_id, $date, $time);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $msg = "<div class='alert alert-danger border-0 border-start border-4 border-danger shadow-sm'>This time slot is already booked. Please choose another time.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO Appointment (Date, Time, Patient_ID, Doctor_ID, Status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssii", $date, $time, $patient_id, $doc_id);
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success border-0 border-start border-4 border-success shadow-sm'><i class='fas fa-check-circle me-2'></i> Appointment securely requested! Your clinical slot is pending review.</div>";
        } else {
            $msg = "<div class='alert alert-danger border-0 border-start border-4 border-danger shadow-sm'>Error processing your request.</div>";
        }
    }
}

// Handle Cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_appointment'])) {
    $app_id = (int)$_POST['appointment_id'];
    $stmt = $conn->prepare("UPDATE Appointment SET Status = 'Cancelled' WHERE Appointment_ID = ? AND Patient_ID = ? AND Status IN ('Pending', 'Scheduled')");
    $stmt->bind_param("ii", $app_id, $patient_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $msg = "<div class='alert alert-success border-0 border-start border-4 border-success shadow-sm'>Your appointment has been successfully cancelled.</div>";
    } else {
        $msg = "<div class='alert alert-danger border-0 border-start border-4 border-danger shadow-sm'>Unable to cancel this appointment. It may already be processed.</div>";
    }
}

// Fetch Doctors for Booking Dropdown
$doctors = $conn->query("SELECT d.Doctor_ID, d.Fname, d.Lname, d.Specialization, dept.Name as DeptName FROM Doctor d JOIN Department dept ON d.Department_ID = dept.Department_ID ORDER BY dept.Name ASC, d.Fname ASC");

// Fetch All Appointments
$stmt = $conn->prepare("
    SELECT a.*, d.Fname as DocFname, d.Lname as DocLname, d.Specialization, r.Room_ID 
    FROM Appointment a
    JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID
    LEFT JOIN Room r ON a.Room_ID = r.Room_ID
    WHERE a.Patient_ID = ?
    ORDER BY a.Date DESC, a.Time DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<div class="row mb-5">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-teal);"><i class="fas fa-calendar-alt me-2"></i> Clinical Appointments</h2>
        <p class="text-muted fs-5">Manage your upcoming care schedule and review past consultations securely.</p>
    </div>
</div>

<?php echo $msg; ?>

<div class="row g-4 mb-4">
    <!-- Booking Form -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header border-bottom-0 pt-4 pb-0 bg-transparent">
                <h5 class="fw-bold text-dark"><i class="fas fa-plus-circle me-2" style="color: var(--primary-teal);"></i> Request Care</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Select Specialist & Department</label>
                        <select name="doctor_id" class="form-select bg-light border-0" required style="border-radius: 10px;">
                            <option value="" disabled selected>Choose a Care Provider...</option>
                            <?php 
                            $current_dept = '';
                            while($doc = $doctors->fetch_assoc()) {
                                if($doc['DeptName'] != $current_dept) {
                                    if($current_dept != '') echo "</optgroup>";
                                    echo "<optgroup label='".htmlspecialchars($doc['DeptName'])."'>";
                                    $current_dept = $doc['DeptName'];
                                }
                                echo "<option value='".$doc['Doctor_ID']."'>Dr. ".htmlspecialchars($doc['Fname'] . ' ' . $doc['Lname'])." (".$doc['Specialization'].")</option>";
                            }
                            if($current_dept != '') echo "</optgroup>";
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Date</label>
                        <input type="date" name="app_date" class="form-control bg-light border-0" required min="<?php echo date('Y-m-d'); ?>" style="border-radius: 10px;">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Time</label>
                        <select name="app_time" class="form-select bg-light border-0" required style="border-radius: 10px;">
                            <option value="" disabled selected>Select Time Slot...</option>
                            <?php
                            $start_time = strtotime("08:00");
                            $end_time = strtotime("19:30");
                            while ($start_time <= $end_time) {
                                echo "<option value='".date("H:i", $start_time)."'>".date("h:i A", $start_time)."</option>";
                                $start_time = strtotime('+30 minutes', $start_time);
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="book_appointment" class="btn btn-teal w-100 py-3 rounded-pill fw-bold"><i class="fas fa-calendar-check me-2"></i> Submit Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Appointment History -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100 overflow-hidden">
            <div class="card-header bg-transparent pt-4 pb-3 border-bottom d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2" style="color: var(--primary-teal);"></i> Care History</h5>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-0 rounded-start-pill"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="appSearch" class="form-control bg-light border-0 rounded-end-pill" placeholder="Search..." onkeyup="filterAppointments()">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px;">
                    <table class="table table-hover align-middle mb-0" id="appointmentsTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-4 fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(0, 'appointmentsTable')">Date & Time <i class="fas fa-sort ms-1"></i></th>
                                <th class="fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(1, 'appointmentsTable')">Care Provider <i class="fas fa-sort ms-1"></i></th>
                                <th class="fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(2, 'appointmentsTable')">Location <i class="fas fa-sort ms-1"></i></th>
                                <th class="fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(3, 'appointmentsTable')">Status <i class="fas fa-sort ms-1"></i></th>
                                <th class="pe-4 text-end fw-semibold text-muted text-uppercase" style="font-size: 0.8rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php while($app = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark d-block"><?php echo date('M d, Y', strtotime($app['Date'])); ?></span>
                                        <span class="text-muted small"><i class="far fa-clock me-1"></i> <?php echo date('h:i A', strtotime($app['Time'])); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">Dr. <?php echo htmlspecialchars($app['DocLname']); ?></span>
                                        <span class="text-muted small"><?php echo htmlspecialchars($app['Specialization']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-muted me-1"></i> <?php echo $app['Room_ID'] ? 'Rm ' . $app['Room_ID'] : 'Facility'; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            if($app['Status'] == 'Scheduled') echo "<span class='badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm'>Scheduled</span>";
                                            elseif($app['Status'] == 'Checked-In') echo "<span class='badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm'>Checked-In</span>";
                                            elseif($app['Status'] == 'Ready') echo "<span class='badge bg-primary px-3 py-2 rounded-pill shadow-sm'>Ready</span>";
                                            elseif($app['Status'] == 'Completed') echo "<span class='badge px-3 py-2 rounded-pill shadow-sm' style='background-color: var(--primary-teal);'>Completed</span>";
                                            elseif($app['Status'] == 'Pending') echo "<span class='badge bg-secondary px-3 py-2 rounded-pill shadow-sm'>Pending</span>";
                                            else echo "<span class='badge bg-dark px-3 py-2 rounded-pill shadow-sm'>".$app['Status']."</span>";
                                        ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-light text-teal rounded-circle shadow-sm" onclick='showDetails(<?php echo json_encode($app); ?>)' title="View Details" style="width: 35px; height: 35px;"><i class="fas fa-eye"></i></button>
                                            <?php if(in_array($app['Status'], ['Pending', 'Scheduled'])): ?>
                                                <button type="button" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" style="width: 35px; height: 35px;" title="Cancel" onclick="confirmCancel(<?php echo $app['Appointment_ID']; ?>)"><i class="fas fa-times"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if($appointments->num_rows == 0) echo "<tr><td colspan='5' class='text-center py-5 text-muted'><div class='mb-3'><i class='fas fa-calendar-times fa-3x opacity-25'></i></div>No clinical history found.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appDetailsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header border-0" style="background-color: var(--primary-teal);">
        <h5 class="modal-title fw-bold text-white"><i class="fas fa-clipboard-list me-2"></i> Clinical Record Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" id="appDetailsBody">
      </div>
      <div class="modal-footer border-0 pt-0 pb-4 px-4 bg-light justify-content-between" id="appDetailsFooter">
      </div>
    </div>
  </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-body text-center p-4">
        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
        <h5 class="fw-bold mb-3">Cancel Appointment?</h5>
        <p class="text-muted small mb-4">Are you sure you want to cancel this care request? This action cannot be undone.</p>
        <form method="POST">
            <input type="hidden" name="appointment_id" id="cancelAppId" value="">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light w-50 rounded-pill fw-bold" data-bs-dismiss="modal">Keep It</button>
                <button type="submit" name="cancel_appointment" class="btn btn-danger w-50 rounded-pill fw-bold">Yes, Cancel</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function showDetails(app) {
    let body = `
        <div class="bg-light p-4 text-center border-bottom">
            <h4 class="fw-bold text-dark mb-1">Dr. ${app.DocFname} ${app.DocLname}</h4>
            <span class="badge bg-secondary mb-2">${app.Specialization}</span>
            <p class="mb-0 text-muted"><i class="far fa-calendar-alt me-1"></i> ${app.Date} at ${app.Time}</p>
        </div>
        <div class="p-4">
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Record ID</small>
                    <p class="fw-bold mb-0">#${app.Appointment_ID}</p>
                </div>
                <div class="col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Status</small>
                    <p class="fw-bold mb-0">${app.Status}</p>
                </div>
                <div class="col-6">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                    <p class="fw-bold mb-0">Room ${app.Room_ID || 'TBD'}</p>
                </div>
            </div>
            ${app.Diagnosis ? `
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Clinical Diagnosis</small>
                <p class="mb-0 text-dark">"${app.Diagnosis}"</p>
            </div>` : ''}
        </div>
    `;
    
    let footer = `
        <button type="button" class="btn btn-light rounded-pill fw-bold border" data-bs-dismiss="modal">Close</button>
        <div class="d-flex gap-2">
            <a href="../receptionist/print_slip.php?id=${app.Appointment_ID}" target="_blank" class="btn btn-lavender rounded-pill px-3" title="View Digital Slip"><i class="fas fa-qrcode"></i></a>
            <button type="button" id="btn-slip-${app.Appointment_ID}" class="btn btn-teal rounded-pill px-4 fw-bold" onclick="sendToWhatsApp('slip', ${app.Appointment_ID})"><i class="fab fa-whatsapp me-2"></i> Mobile Push</button>
        </div>
    `;

    document.getElementById('appDetailsBody').innerHTML = body;
    document.getElementById('appDetailsFooter').innerHTML = footer;
    var detailsModal = new bootstrap.Modal(document.getElementById('appDetailsModal'));
    detailsModal.show();
}

function confirmCancel(appId) {
    document.getElementById('cancelAppId').value = appId;
    var cModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    cModal.show();
}

function sendToWhatsApp(type, appId = null) {
    const btnId = 'btn-slip-' + appId;
    const btn = document.getElementById(btnId);
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i> Sending...';
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
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Sent!';
            btn.classList.replace('btn-teal', 'btn-outline-success');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('btn-outline-success', 'btn-teal');
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

function filterAppointments() {
    let input = document.getElementById("appSearch").value.toLowerCase();
    let trs = document.getElementById("appointmentsTable").getElementsByTagName("tr");
    for (let i = 1; i < trs.length; i++) {
        trs[i].style.display = trs[i].innerText.toLowerCase().includes(input) ? "" : "none";
    }
}

function sortTable(n, tableId) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById(tableId);
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            
            let valX = x.innerHTML.replace(/<[^>]*>?/gm, '').toLowerCase();
            let valY = y.innerHTML.replace(/<[^>]*>?/gm, '').toLowerCase();
            
            if(!isNaN(valX) && !isNaN(valY)){
                valX = parseFloat(valX);
                valY = parseFloat(valY);
            }

            if (dir == "asc") {
                if (valX > valY) { shouldSwitch = true; break; }
            } else if (dir == "desc") {
                if (valX < valY) { shouldSwitch = true; break; }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount ++;
        } else {
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}
</script>

<?php require_once 'footer.php'; ?>