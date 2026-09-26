<?php
// monthly-fees-report.php
$page_title = "Monthly Fees Report";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

$selectedMonth = isset($_GET['month']) ? sprintf('%02d', (int)$_GET['month']) : date('m');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$monthYearKey = $selectedYear . '-' . $selectedMonth;

$stmt = $pdo->prepare("
    SELECT t.id, t.treatment_date, t.treatment_time, t.fee,
           p.name as patient_name, p.address, p.contact,
           d.disease
    FROM treatments t
    JOIN patients p ON t.patient_id = p.id
    LEFT JOIN diagnoses d ON t.diagnosis_id = d.id
    WHERE strftime('%Y-%m', t.treatment_date) = ?
    ORDER BY t.treatment_date DESC, t.treatment_time DESC
");
$stmt->execute([$monthYearKey]);
$records = $stmt->fetchAll();

$totalRecords = count($records);
$totalFees = array_sum(array_column($records, 'fee'));

$monthsList = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-file-csv text-primary me-2"></i>Monthly Fees Report</h1>
        <p class="page-subtitle">Monthly breakdown of fee collections, patient address, disease, and consultation timestamp.</p>
    </div>
    <div class="d-flex gap-2">
        <button id="exportExcelBtn" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i> Export Excel</button>
        <button id="exportPdfBtn" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> Export PDF</button>
    </div>
</div>

<!-- Month & Year Filter -->
<div class="card-custom p-3 p-md-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">Select Month</label>
            <select name="month" class="form-select">
                <?php foreach ($monthsList as $num => $name): ?>
                    <option value="<?= $num ?>" <?= $num === $selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">Select Year</label>
            <select name="year" class="form-select">
                <?php for ($y = date('Y'); $y >= 2016; $y--): ?>
                    <option value="<?= $y ?>" <?= $y === $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-12 col-sm-2 col-md-4">
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Load Monthly Fees</button>
        </div>
    </form>
</div>

<!-- Dashboard Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="stat-title">Total Records</div>
                <div class="stat-value"><?= number_format($totalRecords) ?></div>
                <div class="stat-subtext">Consultations logged</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-success-subtle text-success">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div>
                <div class="stat-title">Total Fees Collected</div>
                <div class="stat-value"><?= formatCurrency($totalFees) ?></div>
                <div class="stat-subtext">Sum for <?= $monthsList[$selectedMonth] ?> <?= $selectedYear ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-info-subtle text-info">
                <i class="fa-solid fa-calendar"></i>
            </div>
            <div>
                <div class="stat-title">Selected Month</div>
                <div class="stat-value fs-4"><?= $monthsList[$selectedMonth] ?></div>
                <div class="stat-subtext">Year <?= $selectedYear ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Fees Table -->
<div class="card-custom p-3 p-md-4" id="monthlyFeesArea">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">Fee Details - <?= $monthsList[$selectedMonth] ?> <?= $selectedYear ?></h5>
        <div class="input-group input-group-sm" style="max-width:250px;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" id="mFeesSearchInput" class="form-control" placeholder="Search table...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom align-middle" id="monthlyFeesTable">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Patient Name</th>
                    <th>Address</th>
                    <th>Disease</th>
                    <th class="text-end">Fee (₹)</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No fee records found for <?= $monthsList[$selectedMonth] ?> <?= $selectedYear ?>.</td></tr>
                <?php else: ?>
                    <?php $sr = 1; foreach ($records as $r): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $sr++ ?></td>
                        <td><strong class="text-primary"><?= htmlspecialchars($r['patient_name']) ?></strong></td>
                        <td><?= htmlspecialchars($r['address'] ?: 'N/A') ?></td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">
                                <?= htmlspecialchars($r['disease'] ?: 'General Consult') ?>
                            </span>
                        </td>
                        <td class="text-end fw-bold text-success"><?= formatCurrency($r['fee']) ?></td>
                        <td>
                            <strong><?= formatDate($r['treatment_date']) ?></strong>
                            <small class="text-muted d-block"><?= formatTime($r['treatment_time']) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#mFeesSearchInput').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $("#monthlyFeesTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    $('#exportExcelBtn').on('click', function() {
        const wb = XLSX.utils.table_to_book(document.getElementById('monthlyFeesTable'), { sheet: "Monthly Fees" });
        XLSX.writeFile(wb, 'Monthly_Fees_Report_<?= $selectedYear ?>_<?= $selectedMonth ?>.xlsx');
    });

    $('#exportPdfBtn').on('click', function() {
        const element = document.getElementById('monthlyFeesArea');
        const opt = {
          margin:       0.4,
          filename:     'Monthly_Fees_Report_<?= $selectedYear ?>_<?= $selectedMonth ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
