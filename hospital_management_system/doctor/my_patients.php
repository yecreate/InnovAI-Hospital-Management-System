<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$doc_id = $_SESSION['Doctor_ID'];

// Fetch patients who have an appointment with this doctor
$query = "SELECT p.Patient_ID, p.Fname, p.Lname, p.Age, p.Gender, p.Blood_Group, 
                 GROUP_CONCAT(DISTINCT ph.PhoneNumber SEPARATOR ', ') as Phones
          FROM Patient p
          JOIN Appointment a ON p.Patient_ID = a.Patient_ID
          LEFT JOIN Patient_phones ph ON p.Patient_ID = ph.Patient_ID
          WHERE a.Doctor_ID = ?
          GROUP BY p.Patient_ID";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$patients = $stmt->get_result();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary border-bottom pb-2"><i class="fas fa-users me-2"></i> My Patients</h2>
    </div>
    <div class="col-md-4">
        <input type="text" id="patientSearch" class="form-control mt-2" placeholder="Search patients..." onkeyup="filterPatients()">
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="patientsTable">
            <thead class="table-primary">
                <tr>
                    <th onclick="sortTable(0, 'patientsTable')" style="cursor:pointer;">ID <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(1, 'patientsTable')" style="cursor:pointer;">Patient Name <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(2, 'patientsTable')" style="cursor:pointer;">Age <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(3, 'patientsTable')" style="cursor:pointer;">Gender <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(4, 'patientsTable')" style="cursor:pointer;">Blood Group <i class="fas fa-sort"></i></th>
                    <th>Phones</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $patients->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Patient_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Fname'] . ' ' . $row['Lname']); ?></td>
                        <td><?php echo htmlspecialchars($row['Age'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Gender'] ?? 'N/A'); ?></td>
                        <td><span class="text-danger fw-bold"><?php echo htmlspecialchars($row['Blood_Group'] ?? 'N/A'); ?></span></td>
                        <td><?php echo htmlspecialchars($row['Phones'] ?? 'N/A'); ?></td>
                        <td class="text-end">
                            <a href="view_history.php?patient_id=<?php echo $row['Patient_ID']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-medical me-1"></i> View History</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if($patients->num_rows == 0) echo "<tr><td colspan='7' class='text-center'>No patients found.</td></tr>"; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterPatients() {
    let input = document.getElementById("patientSearch").value.toLowerCase();
    let trs = document.getElementById("patientsTable").getElementsByTagName("tr");
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