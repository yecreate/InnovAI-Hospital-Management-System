<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$msg = '';

// Handle Create / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_dept'])) {
        $name = sanitize_input($_POST['name']);
        $location = sanitize_input($_POST['location']);
        $stmt = $conn->prepare("INSERT INTO Department (Name, Location) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $location);
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Department added successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error adding department.</div>";
        }
    } elseif (isset($_POST['edit_dept'])) {
        $id = $_POST['dept_id'];
        $name = sanitize_input($_POST['name']);
        $location = sanitize_input($_POST['location']);
        $stmt = $conn->prepare("UPDATE Department SET Name = ?, Location = ? WHERE Department_ID = ?");
        $stmt->bind_param("ssi", $name, $location, $id);
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Department updated successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error updating department.</div>";
        }
    } elseif (isset($_POST['delete_dept'])) {
        $id = $_POST['dept_id'];
        
        // Validation check for foreign keys before deletion
        $doc_check = $conn->query("SELECT COUNT(*) as c FROM Doctor WHERE Department_ID = $id")->fetch_assoc()['c'];
        $nurse_check = $conn->query("SELECT COUNT(*) as c FROM Nurse WHERE Department_ID = $id")->fetch_assoc()['c'];
        
        if ($doc_check > 0 || $nurse_check > 0) {
            $msg = "<div class='alert alert-danger'>Cannot delete department: staff (Doctors/Nurses) are assigned. Reassign them first.</div>";
        } else {
            $stmt = $conn->prepare("DELETE FROM Department WHERE Department_ID = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $msg = "<div class='alert alert-success'>Department deleted.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Cannot delete. Department is in use elsewhere.</div>";
            }
        }
    }
}

$departments = $conn->query("
    SELECT d.Department_ID, d.Name, d.Location, COUNT(doc.Doctor_ID) as DocCount
    FROM Department d
    LEFT JOIN Doctor doc ON d.Department_ID = doc.Department_ID
    GROUP BY d.Department_ID, d.Name, d.Location
");

$docs_query = $conn->query("SELECT Department_ID, CONCAT(Fname, ' ', Lname) AS DocName FROM Doctor WHERE Department_ID IS NOT NULL");
$dept_docs = [];
while($dr = $docs_query->fetch_assoc()){
    $dept_docs[$dr['Department_ID']][] = 'Dr. ' . $dr['DocName'];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-warning border-bottom border-secondary pb-2"><i class="fas fa-building me-2"></i> Manage Departments</h2>
    </div>
</div>

<?php echo $msg; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4 bg-dark">
            <div class="card-header bg-dark text-info border-0"><i class="fas fa-plus-circle me-2"></i> Add New Department</div>
            <div class="card-body bg-dark text-light">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location / Floor</label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                    <button type="submit" name="add_dept" class="btn btn-gold w-100">Add Department</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm bg-dark">
            <div class="card-header bg-dark text-success border-0 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i> Existing Departments</span>
                <input type="text" id="deptSearch" class="form-control w-50 bg-secondary text-light border-0" placeholder="Search departments..." onkeyup="filterDepartments()">
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-dark mb-0" id="deptTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0, 'deptTable')" style="cursor:pointer;">ID <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(1, 'deptTable')" style="cursor:pointer;">Name <i class="fas fa-sort"></i></th>
                            <th onclick="sortTable(2, 'deptTable')" style="cursor:pointer;">Location <i class="fas fa-sort"></i></th>
                            <th>Assigned Doctors</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $departments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['Department_ID']; ?></td>
                                <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Location']); ?></td>
                                <td>
                                    <span class="badge bg-info" style="cursor:pointer;" 
                                          data-bs-toggle="modal" data-bs-target="#docsModal"
                                          data-id="<?php echo $row['Department_ID']; ?>"
                                          data-name="<?php echo htmlspecialchars($row['Name']); ?>"
                                          onclick="fillDocsModal(this)">
                                        <?php echo $row['DocCount']; ?> Doctors
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" data-bs-target="#editDeptModal"
                                            data-id="<?php echo $row['Department_ID']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['Name']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['Location']); ?>"
                                            onclick="fillEditModal(this)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                        <input type="hidden" name="dept_id" value="<?php echo $row['Department_ID']; ?>">
                                        <button type="submit" name="delete_dept" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($departments->num_rows == 0) echo "<tr><td colspan='5' class='text-center'>No departments found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-warning" id="editDeptModalLabel"><i class="fas fa-edit me-2"></i> Edit Department</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <input type="hidden" name="dept_id" id="edit_dept_id">
              <div class="mb-3">
                  <label class="form-label">Department Name</label>
                  <input type="text" name="name" id="edit_name" class="form-control bg-secondary text-light border-0" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Location / Floor</label>
                  <input type="text" name="location" id="edit_location" class="form-control bg-secondary text-light border-0" required>
              </div>
          </div>
          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="edit_dept" class="btn btn-gold">Save Changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- View Doctors Modal -->
<div class="modal fade" id="docsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-info" id="docsModalLabel"><i class="fas fa-user-md me-2"></i> Assigned Doctors</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="docsModalBody">
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
const deptDocs = <?php echo json_encode($dept_docs); ?>;

function fillDocsModal(btn) {
    let deptId = btn.getAttribute('data-id');
    let deptName = btn.getAttribute('data-name');
    let docs = deptDocs[deptId] || [];
    let html = docs.length ? '<ul class="list-group list-group-flush"><li class="list-group-item bg-dark text-light border-secondary">' + docs.join('</li><li class="list-group-item bg-dark text-light border-secondary">') + '</li></ul>' : '<p>No doctors assigned to this department.</p>';
    document.getElementById('docsModalBody').innerHTML = html;
    document.getElementById('docsModalLabel').innerHTML = '<i class="fas fa-user-md me-2"></i> Doctors in ' + deptName;
}

function fillEditModal(btn) {
    document.getElementById('edit_dept_id').value = btn.getAttribute('data-id');
    document.getElementById('edit_name').value = btn.getAttribute('data-name');
    document.getElementById('edit_location').value = btn.getAttribute('data-location');
}

function filterDepartments() {
    let input = document.getElementById("deptSearch").value.toLowerCase();
    let trs = document.getElementById("deptTable").getElementsByTagName("tr");
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
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) { shouldSwitch = true; break; }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) { shouldSwitch = true; break; }
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