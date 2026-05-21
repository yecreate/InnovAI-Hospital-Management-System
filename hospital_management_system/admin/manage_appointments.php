<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$query = "SELECT a.Appointment_ID, a.Date, a.Time, a.Status, p.Fname as p_fname, p.Lname as p_lname, d.Fname as d_fname, d.Lname as d_lname 
          FROM Appointment a
          JOIN Patient p ON a.Patient_ID = p.Patient_ID
          JOIN Doctor d ON a.Doctor_ID = d.Doctor_ID
          ORDER BY a.Date DESC, a.Time DESC";
$result = $conn->query($query);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-warning border-bottom border-secondary pb-2"><i class="fas fa-calendar-check me-2"></i> Manage Appointments</h2>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 bg-dark text-light">
    <div class="card-header bg-dark text-warning border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-list me-2"></i> Appointment List</h5>
        <input type="text" id="appointmentSearch" class="form-control w-25 bg-secondary text-light border-0" placeholder="Search appointments..." onkeyup="filterAppointments()">
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-dark mb-0" id="appointmentsTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0, 'appointmentsTable')" style="cursor:pointer;">ID <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(1, 'appointmentsTable')" style="cursor:pointer;">Date <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(2, 'appointmentsTable')" style="cursor:pointer;">Time <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(3, 'appointmentsTable')" style="cursor:pointer;">Patient <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(4, 'appointmentsTable')" style="cursor:pointer;">Doctor <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(5, 'appointmentsTable')" style="cursor:pointer;">Status <i class="fas fa-sort"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Appointment_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Date']); ?></td>
                        <td><?php echo htmlspecialchars($row['Time']); ?></td>
                        <td><?php echo htmlspecialchars($row['p_fname'] . ' ' . $row['p_lname']); ?></td>
                        <td>Dr. <?php echo htmlspecialchars($row['d_fname'] . ' ' . $row['d_lname']); ?></td>
                        <td>
                            <?php 
                            $status = $row['Status'];
                            $badge = match($status) {
                                'Completed' => 'bg-success',
                                'Scheduled' => 'bg-info',
                                'Cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            echo "<span class='badge $badge'>$status</span>";
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if($result->num_rows == 0) echo "<tr><td colspan='6' class='text-center'>No appointments found.</td></tr>"; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterAppointments() {
    let input = document.getElementById("appointmentSearch").value.toLowerCase();
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
