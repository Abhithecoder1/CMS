<?php
// fees-dashboard.php
$page_title = "Fees Dashboard";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$today = date('Y-m-d');
$settings = getClinicSettings();
$cutoffTime = $settings['cutoff_time'] ?? '16:25'; // Default 4:25 PM

// 1. Before cutoff time today
$stmt1 = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM treatments WHERE treatment_date = ? AND treatment_time < ?");
$stmt1->execute([$today, $cutoffTime]);
$feeBeforeCutoff = (float)$stmt1->fetchColumn();

// 2. After cutoff time today
$stmt2 = $pdo->prepare("SELECT COALESCE(SUM(fee), 0) FROM treatments WHERE treatment_date = ? AND treatment_time >= ?");
$stmt2->execute([$today, $cutoffTime]);
$feeAfterCutoff = (float)$stmt2->fetchColumn();

// 3. Grand total today
$grandTotalToday = $feeBeforeCutoff + $feeAfterCutoff;

// Date Range Fees Query
$fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$toDate = $_GET['to_date'] ?? date('Y-m-d');

$stmtRange = $pdo->prepare("
    SELECT COUNT(*) as total_treatments,
           COUNT(DISTINCT patient_id) as total_patients,
           COALESCE(SUM(fee), 0) as total_fees
    FROM treatments
    WHERE treatment_date BETWEEN ? AND ?
");
$stmtRange->execute([$fromDate, $toDate]);
$rangeStats = $stmtRange->fetch();
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-indian-rupee-sign text-success me-2"></i>Fees Dashboard</h1>
        <p class="page-subtitle">Track clinic revenue, time cutoff revenue splits, and date range financial statistics.</p>
    </div>
</div>

<!-- Time Cutoff Today Breakdown -->
<div class="card-custom p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">Today's Fees Split (<?= date('d M Y') ?>)</h5>
        <span class="badge bg-secondary-subtle text-dark fs-7">Configured Time Cutoff: <?= formatTime($cutoffTime) ?></span>
    </div>

    <div class="row g-3">
        <!-- Before Cutoff -->
        <div class="col-12 col-md-4">
            <div class="p-3 border rounded bg-body-tertiary">
                <small class="text-muted d-block text-uppercase fw-bold fs-8">Before <?= formatTime($cutoffTime) ?></small>
                <div class="fw-bold fs-3 text-primary mt-1"><?= formatCurrency($feeBeforeCutoff) ?></div>
            </div>
        </div>

        <!-- After Cutoff -->
        <div class="col-12 col-md-4">
            <div class="p-3 border rounded bg-body-tertiary">
                <small class="text-muted d-block text-uppercase fw-bold fs-8">After <?= formatTime($cutoffTime) ?></small>
                <div class="fw-bold fs-3 text-info mt-1"><?= formatCurrency($feeAfterCutoff) ?></div>
            </div>
        </div>

        <!-- Grand Total Today -->
        <div class="col-12 col-md-4">
            <div class="p-3 border rounded bg-success-subtle text-success">
                <small class="text-success-emphasis text-uppercase fw-bold fs-8 d-block">Grand Total Today</small>
                <div class="fw-bold fs-3 text-success mt-1"><?= formatCurrency($grandTotalToday) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Fees Report -->
<div class="card-custom p-4 mb-4">
    <h5 class="fw-bold mb-3">Date Range Fees Summary</h5>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" required>
        </div>
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($toDate) ?>" required>
        </div>
        <div class="col-12 col-sm-2 col-md-4">
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-calculator me-1"></i> Calculate Fees</button>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card-custom stat-card">
                <div class="stat-icon-wrapper bg-success-subtle text-success">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div>
                    <div class="stat-title">Total Fees</div>
                    <div class="stat-value"><?= formatCurrency($rangeStats['total_fees']) ?></div>
                    <div class="stat-subtext"><?= formatDate($fromDate) ?> - <?= formatDate($toDate) ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card-custom stat-card">
                <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
                <div>
                    <div class="stat-title">Total Treatments</div>
                    <div class="stat-value"><?= number_format($rangeStats['total_treatments']) ?></div>
                    <div class="stat-subtext">Total consultations</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card-custom stat-card">
                <div class="stat-icon-wrapper bg-info-subtle text-info">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="stat-title">Total Patients</div>
                    <div class="stat-value"><?= number_format($rangeStats['total_patients']) ?></div>
                    <div class="stat-subtext">Unique patients</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
