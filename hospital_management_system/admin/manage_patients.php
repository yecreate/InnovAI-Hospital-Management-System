<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$query = "SELECT Patient.Patient_ID, Patient.Fname, Patient.Lname, Patient.Gender, Patient.Age, Patient_phones.PhoneNumber as Phone, Patient.Registration_Date 
          FROM Patient 
          LEFT JOIN Patient_phones ON Patient.Patient_ID = Patient_phones.Patient_ID
          GROUP BY Patient.Patient_ID
          ORDER BY Patient.Registration_Date DESC";
$result = $conn->query($query);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-warning border-bottom border-secondary pb-2"><i class="fas fa-procedures me-2"></i> Manage Patients</h2>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 bg-dark text-light">
    <div class="card-header bg-dark text-success border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-list me-2"></i> Patient List</h5>
        <input type="text" id="patientSearch" class="form-control w-25 bg-secondary text-light border-0" placeholder="Search patients..." onkeyup="filterPatients()">
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-dark mb-0" id="patientsTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0, 'patientsTable')" style="cursor:pointer;">ID <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(1, 'patientsTable')" style="cursor:pointer;">Name <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(2, 'patientsTable')" style="cursor:pointer;">Gender <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(3, 'patientsTable')" style="cursor:pointer;">Age <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(4, 'patientsTable')" style="cursor:pointer;">Phone <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(5, 'patientsTable')" style="cursor:pointer;">Registration Date <i class="fas fa-sort"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Patient_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Fname'] . ' ' . $row['Lname']); ?></td>
                        <td><?php echo htmlspecialchars($row['Gender'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Age'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['Registration_Date']); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if($result->num_rows == 0) echo "<tr><td colspan='6' class='text-center'>No patients found.</td></tr>"; ?>
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
