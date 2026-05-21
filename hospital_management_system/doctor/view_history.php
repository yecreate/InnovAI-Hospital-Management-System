<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

if (!isset($_GET['patient_id'])) {
    die("Patient ID not provided.");
}

$patient_id = (int)$_GET['patient_id'];

// Get Patient Info
$p_stmt = $conn->prepare("SELECT * FROM Patient WHERE Patient_ID = ?");
$p_stmt->bind_param("i", $patient_id);
$p_stmt->execute();
$patient = $p_stmt->get_result()->fetch_assoc();

if (!$patient) {
    die("Patient not found.");
}

// Get Medical History
$mh_stmt = $conn->prepare("
    SELECT mh.Record_ID, mh.Date, mh.Diagnosis, mh.Treatment, mh.Notes, d.Fname, d.Lname 
    FROM Medical_History mh
    LEFT JOIN Doctor d ON mh.Doctor_ID = d.Doctor_ID
    WHERE mh.Patient_ID = ?
    ORDER BY mh.Date DESC
");
$mh_stmt->bind_param("i", $patient_id);
$mh_stmt->execute();
$history = $mh_stmt->get_result();

// Get Prescriptions (through appointments)
$pr_stmt = $conn->prepare("
    SELECT p.Prescription_ID, p.Medications, p.Dosage, p.Instructions, a.Date, d.Fname, d.Lname
    FROM Prescription p
    JOIN Appointment a ON p.Appointment_ID = a.Appointment_ID
    LEFT JOIN Doctor d ON p.Doctor_ID = d.Doctor_ID
    WHERE a.Patient_ID = ?
    ORDER BY a.Date DESC
");
$pr_stmt->bind_param("i", $patient_id);
$pr_stmt->execute();
$prescriptions = $pr_stmt->get_result();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-primary border-bottom pb-2"><i class="fas fa-user-injured me-2"></i> Patient Profile: <?php echo htmlspecialchars($patient['Fname'] . ' ' . $patient['Lname']); ?></h2>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <a href="my_patients.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to My Patients</a>
            <a href="export_history_pdf.php?patient_id=<?php echo $patient_id; ?>" target="_blank" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> Export Medical History</a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Patient Details -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h5 class="text-primary fw-bold"><i class="fas fa-info-circle me-2"></i> Details</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><strong>Age:</strong> <span><?php echo htmlspecialchars($patient['Age'] ?? 'N/A'); ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Gender:</strong> <span><?php echo htmlspecialchars($patient['Gender'] ?? 'N/A'); ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Blood Group:</strong> <span class="text-danger fw-bold"><?php echo htmlspecialchars($patient['Blood_Group'] ?? 'N/A'); ?></span></li>
                    <li class="list-group-item d-flex justify-content-between"><strong>Emergency Contact:</strong> <span><?php echo htmlspecialchars($patient['Emergency_Contact'] ?? 'N/A'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <!-- Medical History -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white border-0 d-flex justify-content-between align-items-center pt-3 pb-2">
                <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i> Medical History</h5>
                <input type="text" id="histSearch" class="form-control form-control-sm w-25" placeholder="Search..." onkeyup="filterTable('histSearch', 'historyTable')">
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="historyTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'historyTable')" style="cursor:pointer;">Date <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1, 'historyTable')" style="cursor:pointer;">Diagnosis <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2, 'historyTable')" style="cursor:pointer;">Treatment <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(3, 'historyTable')" style="cursor:pointer;">Notes <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(4, 'historyTable')" style="cursor:pointer;">Doctor <i class="fas fa-sort"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($h = $history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($h['Date']); ?></td>
                                <td><strong class="text-dark"><?php echo htmlspecialchars($h['Diagnosis']); ?></strong></td>
                                <td><?php echo htmlspecialchars($h['Treatment']); ?></td>
                                <td><?php echo htmlspecialchars($h['Notes'] ?? ''); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($h['Fname'] . ' ' . $h['Lname']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($history->num_rows == 0) echo "<tr><td colspan='5' class='text-center'>No medical history found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Prescriptions -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white border-0 d-flex justify-content-between align-items-center pt-3 pb-2">
                <h5 class="mb-0"><i class="fas fa-pills me-2"></i> Prescriptions</h5>
                <input type="text" id="prescSearch" class="form-control form-control-sm w-25" placeholder="Search..." onkeyup="filterTable('prescSearch', 'prescTable')">
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="prescTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'prescTable')" style="cursor:pointer;">Date <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1, 'prescTable')" style="cursor:pointer;">Medications <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2, 'prescTable')" style="cursor:pointer;">Dosage <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(3, 'prescTable')" style="cursor:pointer;">Instructions <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(4, 'prescTable')" style="cursor:pointer;">Doctor <i class="fas fa-sort"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pr = $prescriptions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pr['Date']); ?></td>
                                <td><strong class="text-success"><?php echo htmlspecialchars($pr['Medications']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pr['Dosage']); ?></td>
                                <td><?php echo htmlspecialchars($pr['Instructions']); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($pr['Fname'] . ' ' . $pr['Lname']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($prescriptions->num_rows == 0) echo "<tr><td colspan='5' class='text-center'>No prescriptions found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable(inputId, tableId) {
    let input = document.getElementById(inputId).value.toLowerCase();
    let trs = document.getElementById(tableId).getElementsByTagName("tr");
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
            
            let valX = x.innerHTML.toLowerCase();
            let valY = y.innerHTML.toLowerCase();
            
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