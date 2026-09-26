<?php
// date-range-treatment-report.php
$page_title = "Date Range Treatment Report";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

$fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$toDate = $_GET['to_date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT t.id, t.treatment_date, t.treatment_time, t.fee,
           p.name as patient_name, p.sex, p.age, p.contact, p.address,
           d.disease
    FROM treatments t
    JOIN patients p ON t.patient_id = p.id
    LEFT JOIN diagnoses d ON t.diagnosis_id = d.id
    WHERE t.treatment_date BETWEEN ? AND ?
    ORDER BY t.treatment_date DESC, t.treatment_time DESC
");
$stmt->execute([$fromDate, $toDate]);
$treatments = $stmt->fetchAll();

$totalCount = count($treatments);
$totalFees = array_sum(array_column($treatments, 'fee'));

$settings = getClinicSettings();
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-calendar-range text-primary me-2"></i>Date Range Treatment Report</h1>
        <p class="page-subtitle">Generate custom clinical treatment reports between any two specified dates.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-print me-1"></i> Print</button>
        <button id="exportExcelBtn" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i> Excel Export</button>
        <button id="exportPdfBtn" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> PDF Export</button>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card-custom p-3 p-md-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" required>
        </div>
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($toDate) ?>" required>
        </div>
        <div class="col-12 col-sm-2 col-md-4">
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-bolt me-1"></i> Generate Report</button>
        </div>
    </form>
</div>

<!-- Report Content Area -->
<div class="card-custom p-4 p-md-5" id="dateRangeReportContent">
    <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-primary mb-1"><?= htmlspecialchars($settings['clinic_name']) ?></h3>
            <h5 class="fw-semibold text-dark mb-0">Treatment Report</h5>
            <small class="text-muted">Date Range: <strong><?= formatDate($fromDate) ?></strong> to <strong><?= formatDate($toDate) ?></strong></small>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">Report Generated On:</small>
            <strong class="fs-7 text-dark"><?= date('d/m/Y') ?> <?= date('h:i:s A') ?></strong>
        </div>
    </div>

    <!-- Summary Badges -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="p-3 border rounded bg-body-tertiary">
                <small class="text-muted text-uppercase fw-bold fs-8 d-block">Total Consultations</small>
                <div class="fs-4 fw-bold text-primary"><?= number_format($totalCount) ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 border rounded bg-body-tertiary">
                <small class="text-muted text-uppercase fw-bold fs-8 d-block">Total Revenue Collected</small>
                <div class="fs-4 fw-bold text-success"><?= formatCurrency($totalFees) ?></div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="dateRangeTable">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 140px;">Date & Time</th>
                    <th>Patient Name</th>
                    <th>Contact / Address</th>
                    <th>Disease / Diagnosis</th>
                    <th class="text-end" style="width: 120px;">Fee (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($treatments)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No treatments found for selected date range.</td></tr>
                <?php else: ?>
                    <?php $sr = 1; foreach ($treatments as $t): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $sr++ ?></td>
                        <td>
                            <strong><?= formatDate($t['treatment_date']) ?></strong>
                            <small class="text-muted d-block"><?= formatTime($t['treatment_time']) ?></small>
                        </td>
                        <td class="fw-bold text-primary"><?= htmlspecialchars($t['patient_name']) ?></td>
                        <td>
                            <div><i class="fa-solid fa-phone me-1 fs-9 text-muted"></i><?= htmlspecialchars($t['contact']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($t['address'] ?: 'N/A') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">
                                <?= htmlspecialchars($t['disease'] ?: 'General Consult') ?>
                            </span>
                        </td>
                        <td class="text-end fw-bold text-success"><?= formatCurrency($t['fee']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#exportExcelBtn').on('click', function() {
        const wb = XLSX.utils.table_to_book(document.getElementById('dateRangeTable'), { sheet: "Date Range Report" });
        XLSX.writeFile(wb, 'Date_Range_Treatment_Report_<?= $fromDate ?>_to_<?= $toDate ?>.xlsx');
    });

    $('#exportPdfBtn').on('click', function() {
        const element = document.getElementById('dateRangeReportContent');
        const opt = {
          margin:       0.4,
          filename:     'Date_Range_Treatment_Report_<?= $fromDate ?>_to_<?= $toDate ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
