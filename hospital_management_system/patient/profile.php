<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$patient_id = $_SESSION['Patient_ID'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fname = sanitize_input($_POST['fname']);
    $lname = sanitize_input($_POST['lname']);
    $gender = sanitize_input($_POST['gender']);
    $dob = sanitize_input($_POST['dob']);
    $city = sanitize_input($_POST['city']);
    $street = sanitize_input($_POST['street']);
    $building = sanitize_input($_POST['building']);
    $blood_group = sanitize_input($_POST['blood_group']);
    $emergency_contact = sanitize_input($_POST['emergency_contact']);
    $phone = sanitize_input($_POST['phone']);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE Patient SET Fname=?, Lname=?, Gender=?, Date_of_Birth=?, City=?, Street=?, Building=?, Blood_Group=?, Emergency_Contact=? WHERE Patient_ID=?");
        $stmt->bind_param("sssssssssi", $fname, $lname, $gender, $dob, $city, $street, $building, $blood_group, $emergency_contact, $patient_id);
        $stmt->execute();

        // Update Phone (assuming one main phone for profile editing simplicity)
        $conn->query("DELETE FROM Patient_phones WHERE Patient_ID = $patient_id");
        if (!empty($phone)) {
            $stmt_ph = $conn->prepare("INSERT INTO Patient_phones (Patient_ID, PhoneNumber) VALUES (?, ?)");
            $stmt_ph->bind_param("is", $patient_id, $phone);
            $stmt_ph->execute();
        }

        $conn->commit();
        $msg = "<div class='alert alert-success'>Profile updated successfully.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "<div class='alert alert-danger'>Error updating profile.</div>";
    }
}

// Fetch current details
$stmt = $conn->prepare("SELECT p.*, (SELECT PhoneNumber FROM Patient_phones WHERE Patient_ID = p.Patient_ID LIMIT 1) as Phone FROM Patient p WHERE p.Patient_ID = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="text-teal border-bottom pb-2 mb-4" style="color:#008080;"><i class="fas fa-user-circle me-2"></i> My Profile</h2>
        <?php echo $msg; ?>
        
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-lavender text-teal border-0 pt-3 pb-2" style="background-color: #e6e6fa; color: #008080;">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Personal Information</h5>
            </div>
            <div class="card-body bg-white p-4">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($patient['Fname']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" name="lname" class="form-control" value="<?php echo htmlspecialchars($patient['Lname']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($patient['Date_of_Birth']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?php echo ($patient['Gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($patient['Gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($patient['Gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Primary Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($patient['Phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control" value="<?php echo htmlspecialchars($patient['Emergency_Contact'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">Select...</option>
                            <?php 
                            $bgs = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            foreach($bgs as $bg) {
                                $sel = ($patient['Blood_Group'] == $bg) ? 'selected' : '';
                                echo "<option value='$bg' $sel>$bg</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <h5 class="mt-4 mb-3 border-bottom pb-2">Address</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">City</label>
                            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($patient['City'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Street</label>
                            <input type="text" name="street" class="form-control" value="<?php echo htmlspecialchars($patient['Street'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Building</label>
                            <input type="text" name="building" class="form-control" value="<?php echo htmlspecialchars($patient['Building'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-teal w-100 py-2 mt-3 fs-5"><i class="fas fa-save me-2"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>