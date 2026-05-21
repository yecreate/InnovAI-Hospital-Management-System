<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

$nurse_id = $_SESSION['Nurse_ID'];

// Fetch rooms specifically assigned to this Nurse via FK
$stmt = $conn->prepare("
    SELECT r.Room_ID, r.Room_Type, r.Status, r.Phone, 
           p.Patient_ID, p.Fname, p.Lname, p.Age, p.Blood_Group,
           d.Lname as DocLname
    FROM Room r
    LEFT JOIN Patient p ON r.Room_ID = p.Room_ID
    LEFT JOIN Doctor d ON r.Doctor_ID = d.Doctor_ID
    WHERE r.Nurse_ID = ?
");
$stmt->bind_param("i", $nurse_id);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-success pb-2 border-bottom border-success"><i class="fas fa-user-nurse me-2"></i> My Ward Assignments</h2>
        <p class="text-muted">Rooms and patients currently under your direct care.</p>
    </div>
</div>

<div class="row g-4">
    <?php while($room = $assignments->fetch_assoc()): ?>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm <?php echo ($room['Status'] == 'Occupied') ? 'border-danger' : 'border-success'; ?>">
                <div class="card-header <?php echo ($room['Status'] == 'Occupied') ? 'bg-danger text-white' : 'bg-success text-white'; ?>">
                    <h5 class="mb-0">
                        <i class="fas <?php echo ($room['Status'] == 'Occupied') ? 'fa-procedures' : 'fa-check'; ?> me-2"></i> 
                        Room <?php echo $room['Room_ID']; ?> (<?php echo $room['Room_Type']; ?>)
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <p class="mb-2"><strong>Extension:</strong> <?php echo $room['Phone'] ?: 'N/A'; ?></p>
                    <hr>
                    <?php if($room['Status'] == 'Occupied' && $room['Patient_ID']): ?>
                        <h6 class="text-dark fw-bold mb-3"><i class="fas fa-user-injured text-primary me-1"></i> Patient Info</h6>
                        <ul class="list-unstyled">
                            <li><strong>Name:</strong> <?php echo htmlspecialchars($room['Fname'] . ' ' . $room['Lname']); ?></li>
                            <li><strong>Age:</strong> <?php echo $room['Age']; ?></li>
                            <li><strong>Blood Group:</strong> <span class="badge bg-danger"><?php echo $room['Blood_Group'] ?: 'Unknown'; ?></span></li>
                            <li><strong>Attending Doctor:</strong> Dr. <?php echo htmlspecialchars($room['DocLname']); ?></li>
                        </ul>
                        <button class="btn btn-sm btn-outline-success w-100 mt-2"><i class="fas fa-clipboard-check me-2"></i> Log Vitals (Mock)</button>
                    <?php elseif($room['Status'] == 'Maintenance'): ?>
                        <div class="text-center text-warning py-3">
                            <i class="fas fa-tools fa-3x mb-2"></i>
                            <h5>Room Under Maintenance</h5>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-success py-3">
                            <i class="fas fa-bed fa-3x mb-2 opacity-50"></i>
                            <h5>Room is Available</h5>
                            <p class="text-muted small">Ready for new admission.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    <?php if($assignments->num_rows == 0): ?>
        <div class="col-12">
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                You do not have any specific rooms assigned to you at the moment.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>