<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$msg = '';
$admin_id = $_SESSION['Admin_ID'];

// Handle Register
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_staff'])) {
    $role = $_POST['role'];
    $fname = sanitize_input($_POST['fname']);
    $lname = sanitize_input($_POST['lname']);
    $email = sanitize_input($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = sanitize_input($_POST['phone']);
    $dept_id = $_POST['department_id'] ?? null;

    $check = $conn->prepare("SELECT User_ID FROM User_Login WHERE Email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $msg = "<div class='alert alert-danger'>Email already exists!</div>";
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO User_Login (Email, Password, Role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $password, $role);
            $stmt->execute();
            $user_id = $conn->insert_id;

            if ($role === 'Doctor') {
                $spec = sanitize_input($_POST['specialization']);
                $license = sanitize_input($_POST['license_no']);
                $salary = $_POST['salary'];
                $stmt2 = $conn->prepare("INSERT INTO Doctor (Fname, Lname, Specialization, Email, License_No, Phone, Salary, Department_ID, User_ID, Admin_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("ssssssdiii", $fname, $lname, $spec, $email, $license, $phone, $salary, $dept_id, $user_id, $admin_id);
                $stmt2->execute();
                $doc_id = $conn->insert_id;
                
                $stmt3 = $conn->prepare("INSERT INTO Doctor_phones (Doctor_ID, PhoneNumber) VALUES (?, ?)");
                $stmt3->bind_param("is", $doc_id, $phone);
                $stmt3->execute();

            } elseif ($role === 'Nurse') {
                $stmt2 = $conn->prepare("INSERT INTO Nurse (Fname, Lname, Phone, Department_ID, User_ID, Admin_ID) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("sssiii", $fname, $lname, $phone, $dept_id, $user_id, $admin_id);
                $stmt2->execute();

            } elseif ($role === 'Receptionist') {
                $full_name = $fname . ' ' . $lname;
                $shift = sanitize_input($_POST['shift_time']);
                $stmt2 = $conn->prepare("INSERT INTO Receptionist (Full_Name, Phone, Shift_Time, User_ID, Admin_ID) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("sssii", $full_name, $phone, $shift, $user_id, $admin_id);
                $stmt2->execute();
            }

            $conn->commit();
            $msg = "<div class='alert alert-success'>$role successfully registered.</div>";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_staff'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM User_Login WHERE User_ID = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Staff member successfully deleted.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error deleting staff member. They may be linked to active records.</div>";
    }
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_staff'])) {
    $role = $_POST['edit_role'];
    $user_id = $_POST['edit_user_id'];
    $email = sanitize_input($_POST['edit_email']);
    $phone = sanitize_input($_POST['edit_phone']);
    $dept_id = $_POST['edit_department_id'] ?? null;
    
    // Update Email
    $stmt_email = $conn->prepare("UPDATE User_Login SET Email = ? WHERE User_ID = ?");
    $stmt_email->bind_param("si", $email, $user_id);
    $stmt_email->execute();

    if ($role === 'doctor') {
        $fname = sanitize_input($_POST['edit_fname']);
        $lname = sanitize_input($_POST['edit_lname']);
        $spec = sanitize_input($_POST['edit_specialization']);
        $stmt = $conn->prepare("UPDATE Doctor SET Fname=?, Lname=?, Specialization=?, Email=?, Phone=?, Department_ID=? WHERE User_ID=?");
        $stmt->bind_param("sssssii", $fname, $lname, $spec, $email, $phone, $dept_id, $user_id);
        $stmt->execute();
        
        // Update Doctor_phones
        $doc_id = $conn->query("SELECT Doctor_ID FROM Doctor WHERE User_ID=$user_id")->fetch_assoc()['Doctor_ID'];
        $conn->query("DELETE FROM Doctor_phones WHERE Doctor_ID = $doc_id");
        $stmt_ph = $conn->prepare("INSERT INTO Doctor_phones (Doctor_ID, PhoneNumber) VALUES (?, ?)");
        $stmt_ph->bind_param("is", $doc_id, $phone);
        $stmt_ph->execute();
        
    } elseif ($role === 'nurse') {
        $fname = sanitize_input($_POST['edit_fname']);
        $lname = sanitize_input($_POST['edit_lname']);
        $stmt = $conn->prepare("UPDATE Nurse SET Fname=?, Lname=?, Phone=?, Department_ID=? WHERE User_ID=?");
        $stmt->bind_param("sssii", $fname, $lname, $phone, $dept_id, $user_id);
        $stmt->execute();
    } elseif ($role === 'receptionist') {
        $full_name = sanitize_input($_POST['edit_full_name']);
        $shift = sanitize_input($_POST['edit_shift_time']);
        $stmt = $conn->prepare("UPDATE Receptionist SET Full_Name=?, Phone=?, Shift_Time=? WHERE User_ID=?");
        $stmt->bind_param("sssi", $full_name, $phone, $shift, $user_id);
        $stmt->execute();
    }
    
    $msg = "<div class='alert alert-success'>Staff member successfully updated.</div>";
}

$departments = $conn->query("SELECT Department_ID, Name FROM Department");
$depts_array = [];
while($d = $departments->fetch_assoc()){ $depts_array[] = $d; }

// Fetch existing staff based on search
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$search_query = "%{$search}%";

// Doctors Query
$sql_doctors = "SELECT d.Doctor_ID, d.Fname, d.Lname, d.Specialization, d.Email, d.Phone, dep.Name as DeptName, d.Department_ID, d.User_ID 
                FROM Doctor d LEFT JOIN Department dep ON d.Department_ID = dep.Department_ID 
                WHERE d.Fname LIKE ? OR d.Lname LIKE ? OR d.Email LIKE ? OR dep.Name LIKE ?";
$stmt_doc = $conn->prepare($sql_doctors);
$stmt_doc->bind_param("ssss", $search_query, $search_query, $search_query, $search_query);
$stmt_doc->execute();
$doctors = $stmt_doc->get_result();

// Nurses Query
$sql_nurses = "SELECT n.Nurse_ID, n.Fname, n.Lname, n.Phone, dep.Name as DeptName, u.Email, n.Department_ID, n.User_ID 
               FROM Nurse n LEFT JOIN Department dep ON n.Department_ID = dep.Department_ID 
               JOIN User_Login u ON n.User_ID = u.User_ID 
               WHERE n.Fname LIKE ? OR n.Lname LIKE ? OR u.Email LIKE ? OR dep.Name LIKE ?";
$stmt_nur = $conn->prepare($sql_nurses);
$stmt_nur->bind_param("ssss", $search_query, $search_query, $search_query, $search_query);
$stmt_nur->execute();
$nurses = $stmt_nur->get_result();

// Receptionists Query
$sql_receptionists = "SELECT r.Receptionist_ID, r.Full_Name, r.Phone, r.Shift_Time, u.Email, r.User_ID 
                      FROM Receptionist r JOIN User_Login u ON r.User_ID = u.User_ID 
                      WHERE r.Full_Name LIKE ? OR u.Email LIKE ?";
$stmt_rec = $conn->prepare($sql_receptionists);
$stmt_rec->bind_param("ss", $search_query, $search_query);
$stmt_rec->execute();
$receptionists = $stmt_rec->get_result();

// Determine active tab
$active_tab = $_GET['role'] ?? 'doctor';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-warning border-bottom border-secondary pb-2"><i class="fas fa-users-cog me-2"></i> Manage Staff</h2>
    </div>
</div>

<?php echo $msg; ?>

<ul class="nav nav-tabs mb-4" id="staffTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $active_tab == 'doctor' ? 'active' : ''; ?>" id="doctors-tab" data-bs-toggle="tab" data-bs-target="#doctors" type="button" role="tab"><i class="fas fa-user-md"></i> Doctors</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $active_tab == 'nurse' ? 'active' : ''; ?>" id="nurses-tab" data-bs-toggle="tab" data-bs-target="#nurses" type="button" role="tab"><i class="fas fa-user-nurse"></i> Nurses</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $active_tab == 'receptionist' ? 'active' : ''; ?>" id="receptionists-tab" data-bs-toggle="tab" data-bs-target="#receptionists" type="button" role="tab"><i class="fas fa-desktop"></i> Receptionists</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#addStaff" type="button" role="tab"><i class="fas fa-user-plus text-success"></i> Register New Staff</button>
    </li>
</ul>

<div class="tab-content" id="staffTabsContent">
    
    <!-- Search Bar for Staff Views -->
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" id="staffSearch" class="form-control bg-dark text-light border-secondary" placeholder="Live search within current tab..." onkeyup="filterStaff()">
        </div>
    </div>

    <!-- Doctors Tab -->
    <div class="tab-pane fade <?php echo $active_tab == 'doctor' ? 'show active' : ''; ?>" id="doctors" role="tabpanel">
        <div class="card bg-dark border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0" id="doctorsTable">
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Specialization</th><th>Department</th><th>Email</th><th>Phone</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $doctors->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['Doctor_ID']; ?></td>
                                <td>Dr. <?php echo htmlspecialchars($row['Fname'] . ' ' . $row['Lname']); ?></td>
                                <td><?php echo htmlspecialchars($row['Specialization']); ?></td>
                                <td><?php echo htmlspecialchars($row['DeptName']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-info" onclick="editDoc(<?php echo $row['User_ID']; ?>, '<?php echo addslashes($row['Fname']); ?>', '<?php echo addslashes($row['Lname']); ?>', '<?php echo addslashes($row['Email']); ?>', '<?php echo addslashes($row['Phone']); ?>', '<?php echo addslashes($row['Specialization']); ?>', <?php echo $row['Department_ID'] ?: 'null'; ?>)"><i class="fas fa-edit"></i></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this Doctor?');">
                                        <input type="hidden" name="user_id" value="<?php echo $row['User_ID']; ?>">
                                        <button type="submit" name="delete_staff" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($doctors->num_rows == 0) echo "<tr><td colspan='7' class='text-center'>No doctors found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Nurses Tab -->
    <div class="tab-pane fade <?php echo $active_tab == 'nurse' ? 'show active' : ''; ?>" id="nurses" role="tabpanel">
        <div class="card bg-dark border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0" id="nursesTable">
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Department</th><th>Email</th><th>Phone</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $nurses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['Nurse_ID']; ?></td>
                                <td><?php echo htmlspecialchars($row['Fname'] . ' ' . $row['Lname']); ?></td>
                                <td><?php echo htmlspecialchars($row['DeptName']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-info" onclick="editNurse(<?php echo $row['User_ID']; ?>, '<?php echo addslashes($row['Fname']); ?>', '<?php echo addslashes($row['Lname']); ?>', '<?php echo addslashes($row['Email']); ?>', '<?php echo addslashes($row['Phone']); ?>', <?php echo $row['Department_ID'] ?: 'null'; ?>)"><i class="fas fa-edit"></i></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this Nurse?');">
                                        <input type="hidden" name="user_id" value="<?php echo $row['User_ID']; ?>">
                                        <button type="submit" name="delete_staff" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($nurses->num_rows == 0) echo "<tr><td colspan='6' class='text-center'>No nurses found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Receptionists Tab -->
    <div class="tab-pane fade <?php echo $active_tab == 'receptionist' ? 'show active' : ''; ?>" id="receptionists" role="tabpanel">
        <div class="card bg-dark border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-dark table-hover mb-0" id="receptionistsTable">
                    <thead>
                        <tr>
                            <th>ID</th><th>Full Name</th><th>Shift Time</th><th>Email</th><th>Phone</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $receptionists->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['Receptionist_ID']; ?></td>
                                <td><?php echo htmlspecialchars($row['Full_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Shift_Time']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-info" onclick="editRec(<?php echo $row['User_ID']; ?>, '<?php echo addslashes($row['Full_Name']); ?>', '<?php echo addslashes($row['Email']); ?>', '<?php echo addslashes($row['Phone']); ?>', '<?php echo addslashes($row['Shift_Time']); ?>')"><i class="fas fa-edit"></i></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this Receptionist?');">
                                        <input type="hidden" name="user_id" value="<?php echo $row['User_ID']; ?>">
                                        <button type="submit" name="delete_staff" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($receptionists->num_rows == 0) echo "<tr><td colspan='6' class='text-center'>No receptionists found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Register Staff Tab -->
    <div class="tab-pane fade" id="addStaff" role="tabpanel">
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-dark text-info border-0 pt-3 pb-2">
                <h5><i class="fas fa-user-plus me-2"></i> Register New Staff Member</h5>
            </div>
            <div class="card-body bg-dark text-light">
                <form method="POST" id="staffForm">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Role</label>
                            <select name="role" id="roleSelect" class="form-select" required onchange="toggleFields()">
                                <option value="">Select Role...</option>
                                <option value="Doctor">Doctor</option>
                                <option value="Nurse">Nurse</option>
                                <option value="Receptionist">Receptionist</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email (Login ID)</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" name="fname" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lname" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>

                    <!-- Department (For Doctors and Nurses) -->
                    <div class="row mb-3" id="deptField" style="display:none;">
                        <div class="col-md-12">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">Select Department...</option>
                                <?php foreach($depts_array as $d): ?>
                                    <option value="<?php echo $d['Department_ID']; ?>"><?php echo htmlspecialchars($d['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Doctor Specific Fields -->
                    <div class="row mb-3" id="doctorFields" style="display:none;">
                        <div class="col-md-4">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" id="specialization" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">License Number</label>
                            <input type="text" name="license_no" id="license_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Salary</label>
                            <input type="number" step="0.01" name="salary" id="salary" class="form-control">
                        </div>
                    </div>

                    <!-- Receptionist Specific Fields -->
                    <div class="row mb-3" id="receptionistFields" style="display:none;">
                        <div class="col-md-12">
                            <label class="form-label">Shift Time</label>
                            <input type="text" name="shift_time" id="shift_time" class="form-control" placeholder="e.g. Day (8 AM - 4 PM)">
                        </div>
                    </div>

                    <button type="submit" name="register_staff" class="btn btn-gold w-100 py-2"><i class="fas fa-save me-2"></i> Register Staff</button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title text-warning"><i class="fas fa-edit me-2"></i> Edit Staff</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <input type="hidden" name="edit_role" id="edit_role">
              <input type="hidden" name="edit_user_id" id="edit_user_id">
              
              <div id="nameFields">
                  <div class="mb-3">
                      <label class="form-label">First Name</label>
                      <input type="text" name="edit_fname" id="edit_fname" class="form-control bg-secondary text-light border-0">
                  </div>
                  <div class="mb-3">
                      <label class="form-label">Last Name</label>
                      <input type="text" name="edit_lname" id="edit_lname" class="form-control bg-secondary text-light border-0">
                  </div>
              </div>
              <div id="fullNameField" style="display:none;">
                  <div class="mb-3">
                      <label class="form-label">Full Name</label>
                      <input type="text" name="edit_full_name" id="edit_full_name" class="form-control bg-secondary text-light border-0">
                  </div>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="edit_email" id="edit_email" class="form-control bg-secondary text-light border-0" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Phone</label>
                  <input type="text" name="edit_phone" id="edit_phone" class="form-control bg-secondary text-light border-0" required>
              </div>
              
              <div class="mb-3" id="editDeptContainer">
                  <label class="form-label">Department</label>
                  <select name="edit_department_id" id="edit_department_id" class="form-select bg-secondary text-light border-0">
                      <option value="">Select Department...</option>
                      <?php foreach($depts_array as $d): ?>
                          <option value="<?php echo $d['Department_ID']; ?>"><?php echo htmlspecialchars($d['Name']); ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              
              <div class="mb-3" id="editSpecContainer" style="display:none;">
                  <label class="form-label">Specialization</label>
                  <input type="text" name="edit_specialization" id="edit_specialization" class="form-control bg-secondary text-light border-0">
              </div>
              
              <div class="mb-3" id="editShiftContainer" style="display:none;">
                  <label class="form-label">Shift Time</label>
                  <input type="text" name="edit_shift_time" id="edit_shift_time" class="form-control bg-secondary text-light border-0">
              </div>

          </div>
          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="edit_staff" class="btn btn-gold">Save Changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleFields() {
    const role = document.getElementById('roleSelect').value;
    const docFields = document.getElementById('doctorFields');
    const recFields = document.getElementById('receptionistFields');
    const deptField = document.getElementById('deptField');

    // Reset required attributes
    document.getElementById('department_id').required = false;
    document.getElementById('specialization').required = false;
    document.getElementById('license_no').required = false;
    document.getElementById('shift_time').required = false;

    if (role === 'Doctor') {
        docFields.style.display = 'flex';
        recFields.style.display = 'none';
        deptField.style.display = 'flex';
        document.getElementById('department_id').required = true;
        document.getElementById('specialization').required = true;
        document.getElementById('license_no').required = true;
    } else if (role === 'Nurse') {
        docFields.style.display = 'none';
        recFields.style.display = 'none';
        deptField.style.display = 'flex';
        document.getElementById('department_id').required = true;
    } else if (role === 'Receptionist') {
        docFields.style.display = 'none';
        recFields.style.display = 'flex';
        deptField.style.display = 'none';
        document.getElementById('shift_time').required = true;
    } else {
        docFields.style.display = 'none';
        recFields.style.display = 'none';
        deptField.style.display = 'none';
    }
}

function filterStaff() {
    let input = document.getElementById("staffSearch").value.toLowerCase();
    
    // Determine which tab is active
    let activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target');
    let tableId = '';
    if(activeTab === '#doctors') tableId = 'doctorsTable';
    else if(activeTab === '#nurses') tableId = 'nursesTable';
    else if(activeTab === '#receptionists') tableId = 'receptionistsTable';
    
    if(tableId) {
        let trs = document.getElementById(tableId).getElementsByTagName("tr");
        for (let i = 1; i < trs.length; i++) {
            trs[i].style.display = trs[i].innerText.toLowerCase().includes(input) ? "" : "none";
        }
    }
}

function showEditModal() {
    var editModal = new bootstrap.Modal(document.getElementById('editStaffModal'));
    editModal.show();
}

function editDoc(uid, fname, lname, email, phone, spec, dept_id) {
    document.getElementById('edit_role').value = 'doctor';
    document.getElementById('edit_user_id').value = uid;
    document.getElementById('edit_fname').value = fname;
    document.getElementById('edit_lname').value = lname;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_specialization').value = spec;
    document.getElementById('edit_department_id').value = dept_id || '';
    
    document.getElementById('nameFields').style.display = 'block';
    document.getElementById('fullNameField').style.display = 'none';
    document.getElementById('editDeptContainer').style.display = 'block';
    document.getElementById('editSpecContainer').style.display = 'block';
    document.getElementById('editShiftContainer').style.display = 'none';
    
    showEditModal();
}

function editNurse(uid, fname, lname, email, phone, dept_id) {
    document.getElementById('edit_role').value = 'nurse';
    document.getElementById('edit_user_id').value = uid;
    document.getElementById('edit_fname').value = fname;
    document.getElementById('edit_lname').value = lname;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_department_id').value = dept_id || '';
    
    document.getElementById('nameFields').style.display = 'block';
    document.getElementById('fullNameField').style.display = 'none';
    document.getElementById('editDeptContainer').style.display = 'block';
    document.getElementById('editSpecContainer').style.display = 'none';
    document.getElementById('editShiftContainer').style.display = 'none';
    
    showEditModal();
}

function editRec(uid, fullname, email, phone, shift) {
    document.getElementById('edit_role').value = 'receptionist';
    document.getElementById('edit_user_id').value = uid;
    document.getElementById('edit_full_name').value = fullname;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_shift_time').value = shift;
    
    document.getElementById('nameFields').style.display = 'none';
    document.getElementById('fullNameField').style.display = 'block';
    document.getElementById('editDeptContainer').style.display = 'none';
    document.getElementById('editSpecContainer').style.display = 'none';
    document.getElementById('editShiftContainer').style.display = 'block';
    
    showEditModal();
}
</script>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>