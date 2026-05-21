<?php
require_once 'header.php';
require_once '../includes/db_connection.php';

// Daily Revenue Query for Chart
$rev_query = "SELECT Payment_Date, SUM(Amount) as daily_total 
              FROM Billing 
              WHERE Payment_Status = 'Paid' AND Payment_Date IS NOT NULL 
              GROUP BY Payment_Date 
              ORDER BY Payment_Date ASC LIMIT 30";
$rev_result = $conn->query($rev_query);

$dates = [];
$totals = [];
while ($row = $rev_result->fetch_assoc()) {
    $dates[] = $row['Payment_Date'];
    $totals[] = $row['daily_total'];
}

// Recent Transactions
$transactions = $conn->query("
    SELECT b.Bill_ID, b.Amount, b.Payment_Date, b.Payment_Method, 
           CONCAT(p.Fname, ' ', p.Lname) AS Patient_Name,
           r.Full_Name AS Receptionist_Name
    FROM Billing b
    JOIN Patient p ON b.Patient_ID = p.Patient_ID
    LEFT JOIN Receptionist r ON b.Receptionist_ID = r.Receptionist_ID
    WHERE b.Payment_Status = 'Paid'
    ORDER BY b.Payment_Date DESC, b.Bill_ID DESC LIMIT 20
");
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-warning border-bottom border-secondary pb-2"><i class="fas fa-chart-line me-2"></i> Financial Pulse</h2>
    </div>
    <div class="col-md-4 text-end">
        <form method="GET" action="print_financial_pdf.php" target="_blank" class="d-flex justify-content-end align-items-center">
            <input type="date" name="start_date" class="form-control me-2 bg-dark text-light border-secondary" required title="Start Date">
            <input type="date" name="end_date" class="form-control me-2 bg-dark text-light border-secondary" required title="End Date">
            <button type="submit" class="btn btn-gold text-nowrap"><i class="fas fa-file-pdf me-2"></i> Export PDF</button>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-dark">
            <div class="card-header bg-dark text-warning border-0 pt-3 pb-2 d-flex justify-content-between">
                <h5><i class="fas fa-calendar-alt me-2"></i> 30-Day Revenue Trend</h5>
                <select id="chartFilter" class="form-select form-select-sm w-auto bg-secondary text-light border-0" onchange="updateChart()">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 bg-dark">
    <div class="card-header bg-dark text-success border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-receipt me-2"></i> Paid Transactions</h5>
        <input type="text" id="txnSearch" class="form-control w-25 bg-secondary text-light border-0" placeholder="Search transactions..." onkeyup="filterTxn()">
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-dark mb-0" id="txnTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0, 'txnTable')" style="cursor:pointer;">Bill ID <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(1, 'txnTable')" style="cursor:pointer;">Date <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(2, 'txnTable')" style="cursor:pointer;">Patient <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(3, 'txnTable')" style="cursor:pointer;">Amount <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(4, 'txnTable')" style="cursor:pointer;">Method <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(5, 'txnTable')" style="cursor:pointer;">Processed By <i class="fas fa-sort"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php while($t = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $t['Bill_ID']; ?></td>
                        <td><?php echo $t['Payment_Date']; ?></td>
                        <td><?php echo htmlspecialchars($t['Patient_Name']); ?></td>
                        <td class="text-warning fw-bold">EGP <?php echo number_format($t['Amount'], 2); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $t['Payment_Method']; ?></span></td>
                        <td><?php echo htmlspecialchars($t['Receptionist_Name'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if($transactions->num_rows == 0) echo "<tr><td colspan='6' class='text-center'>No recent transactions found.</td></tr>"; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let dates = <?php echo json_encode($dates); ?>;
let totals = <?php echo json_encode($totals); ?>;
const ctxRev = document.getElementById('revenueChart').getContext('2d');
let myChart = new Chart(ctxRev, {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            label: 'Revenue (EGP)',
            data: totals,
            backgroundColor: 'rgba(255, 193, 7, 0.2)',
            borderColor: '#ffc107',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, grid: { color: '#495057' }, ticks: { color: '#adb5bd' } },
            x: { grid: { display: false }, ticks: { color: '#adb5bd' } }
        },
        plugins: {
            legend: { labels: { color: '#f8f9fa' } }
        }
    }
});

function updateChart() {
    let filter = document.getElementById('chartFilter').value;
    if (filter === 'weekly') {
        myChart.data.labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        myChart.data.datasets[0].data = [5000, 7500, 6200, 8000];
    } else if (filter === 'monthly') {
        myChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        myChart.data.datasets[0].data = [20000, 25000, 22000, 30000, 28000];
    } else {
        myChart.data.labels = dates;
        myChart.data.datasets[0].data = totals;
    }
    myChart.update();
}

function filterTxn() {
    let input = document.getElementById("txnSearch").value.toLowerCase();
    let trs = document.getElementById("txnTable").getElementsByTagName("tr");
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
            // Remove 'EGP ', '#' and commas for numeric sorting if it's amount column or ID
            let valX = x.innerHTML.replace(/<[^>]*>?/gm, '').replace('EGP ', '').replace('#', '').replace(/,/g, '').trim();
            let valY = y.innerHTML.replace(/<[^>]*>?/gm, '').replace('EGP ', '').replace('#', '').replace(/,/g, '').trim();
            
            if(!isNaN(valX) && !isNaN(valY)){
                valX = parseFloat(valX);
                valY = parseFloat(valY);
            } else {
                valX = valX.toLowerCase();
                valY = valY.toLowerCase();
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
