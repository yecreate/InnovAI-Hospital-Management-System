<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$nurse_id = $_SESSION['Nurse_ID'];
$today = date('Y-m-d');

// Fetch Nurse Department
$stmt_n = $conn->prepare("SELECT Department_ID FROM Nurse WHERE Nurse_ID = ?");
$stmt_n->bind_param("i", $nurse_id);
$stmt_n->execute();
$nurse_dept = $stmt_n->get_result()->fetch_assoc()['Department_ID'] ?? 0;

// Fetch all rooms for the heatmap
$rooms = $conn->query("
    SELECT r.Room_ID, r.Room_Type, r.Status, r.Phone, 
           p.Patient_ID, p.Fname, p.Lname, p.Age, p.Blood_Group, d.Lname as DocLname
    FROM Room r 
    LEFT JOIN Patient p ON r.Room_ID = p.Room_ID
    LEFT JOIN Doctor d ON r.Doctor_ID = d.Doctor_ID
");

$counts = ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0];
$grid = [];
$room_details_json = [];

while($row = $rooms->fetch_assoc()) {
    $counts[$row['Status']]++;
    $grid[] = $row;
    $room_details_json[$row['Room_ID']] = $row;
}

// Fetch Task List (Checked-In appointments for doctors in the same department)
$tasks_query = "
    SELECT a.Appointment_ID, a.Time, a.Status, p.Patient_ID, p.Fname as p_Fname, p.Lname as p_Lname, d.Doctor_ID, d.Fname as DocFname, d.Lname as DocLname,
           (SELECT Notes FROM Medical_History WHERE Appointment_ID = a.Appointment_ID LIMIT 1) as Notes
    FROM Appointment a
    JOIN Patient p ON a.Patient_ID = p.Patient_ID
    JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID
    WHERE a.Date = '$today' AND a.Status = 'Checked-In' AND d.Department_ID = $nurse_dept
    ORDER BY a.Time ASC
";
$tasks = $conn->query($tasks_query);

// Handle Quick Status Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_task'])) {
    $app_id = $_POST['appointment_id'];
    $pat_id = $_POST['patient_id'];
    $doc_id = $_POST['doctor_id'];
    $note_append = date('H:i') . " - " . sanitize_input($_POST['task_note']) . "\n";
    
    // Check if Medical_History exists for this appointment
    $mh_check = $conn->query("SELECT Record_ID, Notes FROM Medical_History WHERE Appointment_ID = $app_id LIMIT 1");
    if ($mh_check->num_rows > 0) {
        $mh = $mh_check->fetch_assoc();
        $new_notes = $mh['Notes'] . $note_append;
        $stmt_upd = $conn->prepare("UPDATE Medical_History SET Notes = ? WHERE Record_ID = ?");
        $stmt_upd->bind_param("si", $new_notes, $mh['Record_ID']);
        $stmt_upd->execute();
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO Medical_History (Date, Notes, Patient_ID, Doctor_ID, Appointment_ID) VALUES (?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("ssiii", $today, $note_append, $pat_id, $doc_id, $app_id);
        $stmt_ins->execute();
    }
    header("Location: dashboard.php");
    exit;
}
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center border-bottom border-success pb-2">
        <h2 class="text-success m-0"><i class="fas fa-th-large me-2"></i> Live Floor Heatmap & Tasks</h2>
        <span class="text-muted"><i class="fas fa-sync fa-spin me-1"></i> Auto-refreshes every 30s</span>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-map me-2"></i> Interactive Heatmap</span>
                <div>
                    <span class="badge bg-light text-success me-1">Available: <?php echo $counts['Available']; ?></span>
                    <span class="badge bg-danger me-1">Occupied: <?php echo $counts['Occupied']; ?></span>
                    <span class="badge bg-warning text-dark">Maintenance: <?php echo $counts['Maintenance']; ?></span>
                </div>
            </div>
            <div class="card-body bg-light p-4">
                <div class="row g-3">
                    <?php foreach($grid as $room): ?>
                        <?php 
                            $css_class = '';
                            $icon = '';
                            if($room['Status'] == 'Available') { $css_class = 'room-available'; $icon = 'fa-door-open'; }
                            elseif($room['Status'] == 'Occupied') { $css_class = 'room-occupied'; $icon = 'fa-bed'; }
                            elseif($room['Status'] == 'Maintenance') { $css_class = 'room-maintenance'; $icon = 'fa-tools'; }
                        ?>
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="room-box <?php echo $css_class; ?>" style="cursor:pointer;" onclick='showRoomDetails(<?php echo $room['Room_ID']; ?>)'>
                                <div class="room-title"><i class="fas <?php echo $icon; ?> me-2"></i> <?php echo $room['Room_ID']; ?></div>
                                <div class="room-subtitle mt-1"><?php echo $room['Room_Type']; ?></div>
                                <?php if($room['Status'] == 'Occupied' && $room['Fname']): ?>
                                    <div class="mt-2" style="font-size: 0.75rem;"><i class="fas fa-user-injured me-1"></i> <?php echo htmlspecialchars(substr($room['Fname'], 0, 1) . '. ' . $room['Lname']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Room Details Panel -->
        <div class="card shadow-sm border-0 mb-4" id="roomDetailCard" style="display:none;">
            <div class="card-header bg-dark text-white"><i class="fas fa-info-circle me-2"></i> Room Details</div>
            <div class="card-body" id="roomDetailContent">
                Select a room on the heatmap to view details.
            </div>
        </div>
        
        <!-- Today's Task List -->
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-info text-dark"><i class="fas fa-tasks me-2"></i> Active Patient Queue</div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <ul class="list-group list-group-flush">
                    <?php while($task = $tasks->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($task['p_Fname'] . ' ' . $task['p_Lname']); ?></h6>
                                <small><?php echo date('h:i A', strtotime($task['Time'])); ?></small>
                            </div>
                            <p class="mb-1 small text-muted">Dr. <?php echo htmlspecialchars($task['DocFname'] . ' ' . $task['DocLname']); ?></p>
                            <?php if($task['Notes']): ?>
                                <div class="small bg-light p-1 mb-2 border rounded text-muted" style="white-space: pre-wrap; font-family: monospace; max-height: 60px; overflow-y: auto;"><?php echo htmlspecialchars($task['Notes']); ?></div>
                            <?php endif; ?>
                            <form method="POST" class="d-flex mt-2 gap-1">
                                <input type="hidden" name="appointment_id" value="<?php echo $task['Appointment_ID']; ?>">
                                <input type="hidden" name="patient_id" value="<?php echo $task['Patient_ID']; ?>">
                                <input type="hidden" name="doctor_id" value="<?php echo $task['Doctor_ID']; ?>">
                                <button type="submit" name="update_task" value="1" class="btn btn-sm btn-outline-primary py-0" onclick="document.getElementById('task_note_<?php echo $task['Appointment_ID']; ?>').value = 'Vitals Taken';"><i class="fas fa-heartbeat"></i> Vitals</button>
                                <button type="submit" name="update_task" value="1" class="btn btn-sm btn-outline-success py-0" onclick="document.getElementById('task_note_<?php echo $task['Appointment_ID']; ?>').value = 'Medication Given';"><i class="fas fa-pills"></i> Meds</button>
                                <input type="hidden" name="task_note" id="task_note_<?php echo $task['Appointment_ID']; ?>" value="">
                            </form>
                        </li>
                    <?php endwhile; ?>
                    <?php if($tasks->num_rows == 0): ?>
                        <li class="list-group-item text-center text-muted py-4">No active checked-in patients in your department.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
const roomData = <?php echo json_encode($room_details_json); ?>;

function showRoomDetails(roomId) {
    const data = roomData[roomId];
    const card = document.getElementById('roomDetailCard');
    const content = document.getElementById('roomDetailContent');
    
    let html = `<h4 class="text-success mb-3">Room ${roomId} <small class="text-muted fs-6">(${data.Room_Type})</small></h4>`;
    html += `<p><strong>Status:</strong> ${data.Status}</p>`;
    html += `<p><strong>Extension:</strong> ${data.Phone || 'N/A'}</p><hr>`;
    
    if (data.Status === 'Occupied' && data.Patient_ID) {
        html += `<h6 class="fw-bold"><i class="fas fa-user-injured text-primary me-2"></i>Patient: ${data.Fname} ${data.Lname}</h6>`;
        html += `<ul class="list-unstyled mt-2 ms-4">
                    <li>Age: ${data.Age} yrs</li>
                    <li>Blood Group: <span class="badge bg-danger">${data.Blood_Group || '?'}</span></li>
                    <li>Attending: Dr. ${data.DocLname || 'N/A'}</li>
                 </ul>`;
    } else if (data.Status === 'Available') {
        html += `<p class="text-muted fst-italic">Room is ready for admission.</p>`;
    } else {
        html += `<p class="text-warning text-dark fst-italic"><i class="fas fa-tools me-1"></i> Maintenance in progress.</p>`;
    }
    
    content.innerHTML = html;
    card.style.display = 'block';
}

// Auto Refresh every 30 seconds
setTimeout(() => {
    window.location.reload();
}, 30000);
</script>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>one || 'N/A'}</p><hr>`;
    
    if (data.Status === 'Occupied' && data.Patient_ID) {
        html += `<h6 class="fw-bold"><i class="fas fa-user-injured text-primary me-2"></i>Patient: ${data.Fname} ${data.Lname}</h6>`;
        html += `<ul class="list-unstyled mt-2 ms-4">
                    <li>Age: ${data.Age} yrs</li>
                    <li>Blood Group: <span class="badge bg-danger">${data.Blood_Group || '?'}</span></li>
                    <li>Attending: Dr. ${data.DocLname || 'N/A'}</li>
                 </ul>`;
    } else if (data.Status === 'Available') {
        html += `<p class="text-muted fst-italic">Room is ready for admission.</p>`;
    } else {
        html += `<p class="text-warning text-dark fst-italic"><i class="fas fa-tools me-1"></i> Maintenance in progress.</p>`;
    }
    
    content.innerHTML = html;
    card.style.display = 'block';
}

// Auto Refresh every 30 seconds
setTimeout(() => {
    window.location.reload();
}, 30000);
</script>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>