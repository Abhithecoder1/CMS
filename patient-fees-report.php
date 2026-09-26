<?php
// patient-fees-report.php
$page_title = "Patient Fees Report";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

$fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$toDate = $_GET['to_date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT t.id, t.treatment_date, t.fee,
           p.name as patient_name, p.contact
    FROM treatments t
    JOIN patients p ON t.patient_id = p.id
    WHERE t.treatment_date BETWEEN ? AND ?
    ORDER BY t.treatment_date DESC
");
$stmt->execute([$fromDate, $toDate]);
$records = $stmt->fetchAll();

$totalPatients = count(array_unique(array_column($records, 'patient_name')));
$totalFees = array_sum(array_column($records, 'fee'));
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Patient Fees Report</h1>
        <p class="page-subtitle">Patient consultation fees logs between selected dates.</p>
    </div>
    <div class="d-flex gap-2">
        <button id="exportExcelBtn" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-file-excel me-1"></i> Excel Export</button>
        <button id="exportPdfBtn" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> PDF Export</button>
    </div>
</div>

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
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter Report</button>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-title">Total Patients</div>
                <div class="stat-value"><?= number_format($totalPatients) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-success-subtle text-success">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div>
                <div class="stat-title">Total Fees</div>
                <div class="stat-value"><?= formatCurrency($totalFees) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Table Area -->
<div class="card-custom p-3 p-md-4" id="patientFeesArea">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">Fee Logs (<?= formatDate($fromDate) ?> - <?= formatDate($toDate) ?>)</h5>
        <div class="input-group input-group-sm" style="max-width: 250px;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" id="feesSearchInput" class="form-control" placeholder="Search logs...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom align-middle" id="patientFeesTable">
            <thead>
                <tr>
                    <th style="width: 70px;">Sr No</th>
                    <th>Patient Name</th>
                    <th>Contact</th>
                    <th class="text-end">Fee</th>
                    <th class="text-end">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No fee records found.</td></tr>
                <?php else: ?>
                    <?php $sr = 1; foreach ($records as $r): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $sr++ ?></td>
                        <td><strong class="text-primary"><?= htmlspecialchars($r['patient_name']) ?></strong></td>
                        <td><i class="fa-solid fa-phone me-1 text-muted fs-9"></i><?= htmlspecialchars($r['contact']) ?></td>
                        <td class="text-end fw-bold text-success"><?= formatCurrency($r['fee']) ?></td>
                        <td class="text-end fw-semibold text-dark"><?= formatDate($r['treatment_date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#feesSearchInput').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $("#patientFeesTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    $('#exportExcelBtn').on('click', function() {
        const wb = XLSX.utils.table_to_book(document.getElementById('patientFeesTable'), { sheet: "Patient Fees" });
        XLSX.writeFile(wb, 'Patient_Fees_Report_<?= $fromDate ?>_to_<?= $toDate ?>.xlsx');
    });

    $('#exportPdfBtn').on('click', function() {
        const element = document.getElementById('patientFeesArea');
        const opt = {
          margin:       0.4,
          filename:     'Patient_Fees_Report_<?= $fromDate ?>_to_<?= $toDate ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
