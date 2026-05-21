<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$recp_id = $_SESSION['Receptionist_ID'];
$today = date('Y-m-d');
$msg = '';

// Handle Manual Check-In
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_checkin'])) {
    $app_id = $_POST['appointment_id'];
    $stmt = $conn->prepare("UPDATE Appointment SET Status = 'Checked-In' WHERE Appointment_ID = ?");
    $stmt->bind_param("i", $app_id);
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success border-0 border-start border-4 border-success shadow-sm'><i class='fas fa-check-circle me-2'></i> Patient for Appointment #$app_id successfully checked in.</div>";
    } else {
        $msg = "<div class='alert alert-danger border-0 border-start border-4 border-danger shadow-sm'>Failed to check in appointment.</div>";
    }
}

// Handle Patient Pending Confirmations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_pending'])) {
    $app_id = $_POST['appointment_id'];
    $stmt = $conn->prepare("UPDATE Appointment SET Status = 'Scheduled' WHERE Appointment_ID = ?");
    $stmt->bind_param("i", $app_id);
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success border-0 border-start border-4 border-success shadow-sm'><i class='fas fa-calendar-check me-2'></i> Appointment #$app_id confirmed and Scheduled.</div>";
    } else {
        $msg = "<div class='alert alert-danger border-0 border-start border-4 border-danger shadow-sm'>Failed to confirm appointment.</div>";
    }
}

// Stats (Treating empty string or NULL as 'Pending' if they exist, but generally querying the specific statuses)
$stats = [
    'scheduled' => $conn->query("SELECT COUNT(*) as c FROM Appointment WHERE Date = '$today' AND Status = 'Scheduled'")->fetch_assoc()['c'],
    'checked_in' => $conn->query("SELECT COUNT(*) as c FROM Appointment WHERE Date = '$today' AND Status = 'Checked-In'")->fetch_assoc()['c'],
    'completed' => $conn->query("SELECT COUNT(*) as c FROM Appointment WHERE Date = '$today' AND Status = 'Completed'")->fetch_assoc()['c']
];

// Query Builder for Appointments
function getAppointments($conn, $condition, $orderBy = "a.Date ASC, a.Time ASC") {
    $sql = "SELECT a.Appointment_ID, a.Date, a.Time, a.Status, p.Fname as p_Fname, p.Lname as p_Lname, d.Fname as DocFname, d.Lname as DocLname, r.Room_ID 
            FROM Appointment a 
            JOIN Patient p ON a.Patient_ID = p.Patient_ID 
            JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID 
            LEFT JOIN Room r ON a.Room_ID = r.Room_ID 
            WHERE $condition 
            ORDER BY $orderBy LIMIT 50";
    return $conn->query($sql);
}

$apps_today = getAppointments($conn, "a.Date = '$today'");
$apps_upcoming = getAppointments($conn, "a.Date > '$today' AND a.Status != 'Cancelled'");
$apps_past = getAppointments($conn, "a.Date < '$today' OR a.Status IN ('Completed', 'Cancelled')", "a.Date DESC, a.Time DESC");

$active_tab = $_GET['tab'] ?? 'today';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold" style="color: var(--primary-blue-dark);"><i class="fas fa-satellite-dish me-2 text-primary"></i> Front Desk Operations</h2>
        <p class="text-muted fs-6 mb-0">Manage daily patient flow, check-ins, and appointments rapidly.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <div class="d-inline-flex align-items-center bg-white px-4 py-2 rounded-pill shadow-sm border">
            <i class="far fa-clock text-primary me-2 fs-5"></i>
            <span class="fs-4 fw-bold text-dark" id="live-clock" style="font-variant-numeric: tabular-nums;">--:-- --</span>
        </div>
    </div>
</div>

<?php echo $msg; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 p-3 text-center stat-card border-0" style="border-bottom: 5px solid #f59e0b !important; cursor: pointer;" onclick="filterDashByStatus('Scheduled')">
            <div class="card-body">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; color: #f59e0b;">
                    <i class="fas fa-calendar-day fa-2x"></i>
                </div>
                <h5 class="fw-bold text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">Scheduled Today</h5>
                <h2 class="fw-bold text-dark mb-0 display-5"><?php echo $stats['scheduled']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-3 text-center stat-card border-0" style="border-bottom: 5px solid var(--primary-blue) !important; cursor: pointer;" onclick="filterDashByStatus('Checked-In')">
            <div class="card-body">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; color: var(--primary-blue);">
                    <i class="fas fa-walking fa-2x"></i>
                </div>
                <h5 class="fw-bold text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">Patients Waiting</h5>
                <h2 class="fw-bold text-dark mb-0 display-5"><?php echo $stats['checked_in']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 p-3 text-center stat-card border-0" style="border-bottom: 5px solid #10b981 !important; cursor: pointer;" onclick="filterDashByStatus('Completed')">
            <div class="card-body">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; color: #10b981;">
                    <i class="fas fa-check-double fa-2x"></i>
                </div>
                <h5 class="fw-bold text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">Completed Today</h5>
                <h2 class="fw-bold text-dark mb-0 display-5"><?php echo $stats['completed']; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-2 d-flex flex-wrap justify-content-between align-items-center">
                <ul class="nav nav-pills custom-nav-pills mb-2 mb-md-0" id="appTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 <?php echo $active_tab == 'today' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab-today" type="button" role="tab"><i class="fas fa-calendar-day me-1"></i> Today</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 <?php echo $active_tab == 'upcoming' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab-upcoming" type="button" role="tab"><i class="fas fa-forward me-1"></i> Upcoming</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 <?php echo $active_tab == 'past' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab-past" type="button" role="tab"><i class="fas fa-history me-1"></i> Past</button>
                    </li>
                </ul>
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-0 rounded-start-pill"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="dashSearch" class="form-control bg-light border-0 rounded-end-pill" placeholder="Live search..." onkeyup="filterDash()">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="appTabsContent">
                    <!-- Today Tab -->
                    <div class="tab-pane fade <?php echo $active_tab == 'today' ? 'show active' : ''; ?>" id="tab-today" role="tabpanel">
                        <div class="table-responsive" style="max-height: 500px;">
                            <?php renderTable($apps_today, true); ?>
                        </div>
                    </div>
                    
                    <!-- Upcoming Tab -->
                    <div class="tab-pane fade <?php echo $active_tab == 'upcoming' ? 'show active' : ''; ?>" id="tab-upcoming" role="tabpanel">
                        <div class="table-responsive" style="max-height: 500px;">
                            <?php renderTable($apps_upcoming, true); ?>
                        </div>
                    </div>

                    <!-- Past Tab -->
                    <div class="tab-pane fade <?php echo $active_tab == 'past' ? 'show active' : ''; ?>" id="tab-past" role="tabpanel">
                        <div class="table-responsive" style="max-height: 500px;">
                            <?php renderTable($apps_past, false); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white pt-4 pb-3"><h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bolt text-primary me-2"></i> Fast Actions</h5></div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="appointment_book.php" class="btn btn-primary btn-lg text-start py-3 shadow-sm rounded-3"><i class="fas fa-user-plus me-3 fs-5 align-middle"></i> Register New Patient</a>
                    <a href="qr_scanner.php" class="btn btn-outline-dark btn-lg text-start py-3 rounded-3" style="background-color: var(--soft-blue); border-color: var(--primary-blue); color: var(--primary-blue-dark);"><i class="fas fa-camera me-3 fs-5 align-middle"></i> QR Fast-Track Check-In</a>
                    <a href="billing_issue.php" class="btn btn-outline-secondary btn-lg text-start py-3 rounded-3"><i class="fas fa-file-invoice-dollar me-3 fs-5 align-middle text-success"></i> Issue Invoice</a>
                    <button class="btn btn-light btn-lg text-start py-3 rounded-3 mt-3 text-muted fw-bold border" onclick="filterDashByStatus('')"><i class="fas fa-sync-alt me-3 fs-5 align-middle"></i> Reset Board Filters</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function renderTable($result, $show_actions) {
    echo '<table class="table table-hover align-middle mb-0 dash-table"><thead class="table-light sticky-top"><tr><th class="ps-4 fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(0, this)">Date/Time <i class="fas fa-sort ms-1"></i></th><th class="fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(1, this)">Patient <i class="fas fa-sort ms-1"></i></th><th class="fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(2, this)">Doctor <i class="fas fa-sort ms-1"></i></th><th class="fw-semibold text-muted text-uppercase" style="font-size: 0.8rem; cursor:pointer;" onclick="sortTable(3, this)">Status <i class="fas fa-sort ms-1"></i></th>';
    if ($show_actions) echo '<th class="pe-4 text-end fw-semibold text-muted text-uppercase" style="font-size: 0.8rem;">Action</th>';
    echo '</tr></thead><tbody class="border-top-0">';
    
    if($result->num_rows == 0) {
        echo "<tr><td colspan='5' class='text-center py-5 text-muted'><div class='mb-3'><i class='fas fa-clipboard-check fa-3x opacity-25'></i></div>No records found.</td></tr>";
    } else {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='ps-4 py-3'><span class='fw-bold text-dark d-block'>" . date('M d', strtotime($row['Date'])) . "</span><span class='text-muted small'><i class='far fa-clock me-1'></i>" . date('h:i A', strtotime($row['Time'])) . "</span></td>";
            echo "<td><span class='fw-bold text-dark'>" . htmlspecialchars($row['p_Fname'] . ' ' . $row['p_Lname']) . "</span></td>";
            echo "<td>Dr. " . htmlspecialchars($row['DocLname']) . "</td>";
            
            // Fix blank status
            $status = empty($row['Status']) ? 'Pending' : $row['Status'];
            
            echo "<td data-status='$status'>";
            if($status == 'Scheduled') echo "<span class='badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm'>Scheduled</span>";
            elseif($status == 'Checked-In') echo "<span class='badge px-3 py-2 rounded-pill shadow-sm' style='background-color: var(--primary-blue); color: white;'>Checked-In</span>";
            elseif($status == 'Completed') echo "<span class='badge bg-success px-3 py-2 rounded-pill shadow-sm'>Completed</span>";
            elseif($status == 'Pending') echo "<span class='badge bg-secondary px-3 py-2 rounded-pill shadow-sm'>Pending</span>";
            else echo "<span class='badge bg-dark px-3 py-2 rounded-pill shadow-sm'>".$status."</span>";
            echo "</td>";

            if ($show_actions) {
                echo "<td class='pe-4 text-end'>";
                if ($status == 'Scheduled') {
                    echo "<form method='POST' style='display:inline;'>
                            <input type='hidden' name='appointment_id' value='".$row['Appointment_ID']."'>
                            <button type='submit' name='manual_checkin' class='btn btn-sm rounded-pill fw-bold text-primary' style='background-color: var(--soft-blue); border: 1px solid var(--primary-blue);'><i class='fas fa-walking me-1'></i> Check-In</button>
                          </form>";
                } elseif ($status == 'Pending') {
                    echo "<form method='POST' style='display:inline;'>
                            <input type='hidden' name='appointment_id' value='".$row['Appointment_ID']."'>
                            <button type='submit' name='confirm_pending' class='btn btn-sm btn-light text-success border shadow-sm rounded-pill fw-bold'><i class='fas fa-thumbs-up me-1'></i> Confirm</button>
                          </form>";
                } else {
                    echo "<button class='btn btn-sm btn-light rounded-pill border' disabled><i class='fas fa-check text-muted'></i></button>";
                }
                echo "</td>";
            }
            echo "</tr>";
        }
    }
    echo '</tbody></table>';
}
?>

<script>
function filterDash() {
    let input = document.getElementById("dashSearch").value.toLowerCase();
    let tables = document.querySelectorAll(".dash-table");
    tables.forEach(table => {
        let trs = table.getElementsByTagName("tr");
        for (let i = 1; i < trs.length; i++) {
            trs[i].style.display = trs[i].innerText.toLowerCase().includes(input) ? "" : "none";
        }
    });
}

function filterDashByStatus(status) {
    let tables = document.querySelectorAll(".dash-table");
    tables.forEach(table => {
        let trs = table.getElementsByTagName("tr");
        for (let i = 1; i < trs.length; i++) {
            let rowStatus = trs[i].querySelector('td[data-status]')?.getAttribute('data-status');
            if (status === '' || rowStatus === status) {
                trs[i].style.display = "";
            } else {
                trs[i].style.display = "none";
            }
        }
    });
    // Ensure we are on the 'Today' tab if they click a status card
    if (status !== '') {
        let todayTab = new bootstrap.Tab(document.querySelector('#appTabs button[data-bs-target="#tab-today"]'));
        todayTab.show();
    }
}

function sortTable(n, thElement) {
    var table = thElement.closest('table');
    var rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            
            let valX = x.innerText.toLowerCase();
            let valY = y.innerText.toLowerCase();

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

function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('live-clock');
    if(clockEl) clockEl.innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
}
setInterval(updateClock, 1000);
updateClock(); // Initial call
</script>

<?php require_once 'footer.php'; ?>