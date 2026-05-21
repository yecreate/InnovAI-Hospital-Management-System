<?php
require_once 'header.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

$msg = '';
$recp_id = $_SESSION['Receptionist_ID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['issue_bill'])) {
    $patient_id = $_POST['patient_id'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];
    $status = $_POST['payment_status'];
    $date = ($status == 'Paid') ? date('Y-m-d') : null;

    $stmt = $conn->prepare("INSERT INTO Billing (Amount, Payment_Date, Payment_Method, Payment_Status, Patient_ID, Receptionist_ID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("dsssii", $amount, $date, $method, $status, $patient_id, $recp_id);
    
    if ($stmt->execute()) {
        $bill_id = $conn->insert_id;
        $msg = "<div class='alert alert-success'>Bill #$bill_id generated successfully. <a href='print_bill.php?id=$bill_id' target='_blank' class='alert-link'>Print PDF Receipt</a></div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error generating bill.</div>";
    }
}

// Get unbilled patients or all patients
$patients = $conn->query("SELECT Patient_ID, Fname, Lname FROM Patient ORDER BY Fname ASC");

// Billing History Query
$filter_name = isset($_GET['patient_name']) ? sanitize_input($_GET['patient_name']) : '';
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default to 1st of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Default to today

$history_sql = "
    SELECT b.Bill_ID, b.Amount, b.Payment_Date, b.Payment_Status, b.Payment_Method, 
           CONCAT(p.Fname, ' ', p.Lname) as PatientName
    FROM Billing b
    JOIN Patient p ON b.Patient_ID = p.Patient_ID
    WHERE (b.Payment_Date BETWEEN ? AND ? OR b.Payment_Date IS NULL) 
    AND CONCAT(p.Fname, ' ', p.Lname) LIKE ?
    ORDER BY b.Bill_ID DESC
";
$search_name = "%{$filter_name}%";
$stmt_hist = $conn->prepare($history_sql);
$stmt_hist->bind_param("sss", $start_date, $end_date, $search_name);
$stmt_hist->execute();
$history = $stmt_hist->get_result();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-primary-dark pb-2 border-bottom border-info"><i class="fas fa-file-invoice-dollar me-2"></i> Billing & Finance</h2>
    </div>
</div>

<?php echo $msg; ?>

<div class="row mb-5">
    <!-- Issue New Bill -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white"><i class="fas fa-plus me-2"></i> Create New Invoice</div>
            <div class="card-body bg-light">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Patient</label>
                        <input type="text" id="patientSearchDropdown" class="form-control mb-2" placeholder="Search patient name..." onkeyup="filterPatientDropdown()">
                        <select name="patient_id" id="patientSelect" class="form-select" size="4" required style="overflow-y: auto;">
                            <?php while($p = $patients->fetch_assoc()): ?>
                                <option value="<?php echo $p['Patient_ID']; ?>"><?php echo htmlspecialchars($p['Fname'] . ' ' . $p['Lname']); ?> (ID: <?php echo $p['Patient_ID']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Amount (EGP)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">None (If Unpaid)</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Insurance">Insurance</option>
                        </select>
                    </div>
                    <button type="submit" name="issue_bill" class="btn btn-success w-100 py-2 fw-bold"><i class="fas fa-check-circle me-2"></i> Generate & Save Bill</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Billing History & Export -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i> Billing History</span>
            </div>
            <div class="card-body p-0">
                <!-- Filter Form -->
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">Patient Name</label>
                            <input type="text" name="patient_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_name); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                    </form>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary sticky-top">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $history->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['Bill_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                                    <td><?php echo $row['Payment_Date'] ?: '-'; ?></td>
                                    <td>EGP <?php echo number_format($row['Amount'], 2); ?></td>
                                    <td>
                                        <?php if($row['Payment_Status'] == 'Paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif($row['Payment_Status'] == 'Unpaid'): ?>
                                            <span class="badge bg-danger">Unpaid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="print_bill.php?id=<?php echo $row['Bill_ID']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Print Bill"><i class="fas fa-print"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if($history->num_rows == 0) echo "<tr><td colspan='6' class='text-center py-4'>No records found for this period.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Export PDF Form -->
            <div class="card-footer bg-white border-top text-end">
                <form method="GET" action="../admin/print_financial_pdf.php" target="_blank" class="d-inline">
                    <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                    <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-file-pdf me-2"></i> Export Filtered PDF Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function filterPatientDropdown() {
    let input = document.getElementById("patientSearchDropdown").value.toLowerCase();
    let options = document.getElementById("patientSelect").options;
    
    // We start from index 0 since we removed the blank "Choose..." to make it a listbox
    for (let i = 0; i < options.length; i++) {
        let txt = options[i].text.toLowerCase();
        options[i].style.display = txt.includes(input) ? "" : "none";
    }
}
</script>

<?php require_once 'footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>