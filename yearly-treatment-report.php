<?php
// yearly-treatment-report.php
$page_title = "Year Wise Treated Patients";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Stats for the year
$stmtTotalTreatments = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE strftime('%Y', treatment_date) = ?");
$stmtTotalTreatments->execute([(string)$selectedYear]);
$totalTreatmentsCount = (int)$stmtTotalTreatments->fetchColumn();

$stmtTotalPatients = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) FROM treatments WHERE strftime('%Y', treatment_date) = ?");
$stmtTotalPatients->execute([(string)$selectedYear]);
$totalPatientsCount = (int)$stmtTotalPatients->fetchColumn();

// Monthly breakdown array for Chart.js
$monthlyCounts = array_fill(1, 12, 0);
$mStmt = $pdo->prepare("
    SELECT CAST(strftime('%m', treatment_date) AS INTEGER) as month_num, COUNT(*) as cnt
    FROM treatments
    WHERE strftime('%Y', treatment_date) = ?
    GROUP BY month_num
");
$mStmt->execute([(string)$selectedYear]);
while ($row = $mStmt->fetch()) {
    $monthlyCounts[(int)$row['month_num']] = (int)$row['cnt'];
}

// Top diseases for the year
$dStmt = $pdo->prepare("
    SELECT d.disease, COUNT(*) as cnt
    FROM treatments t
    JOIN diagnoses d ON t.diagnosis_id = d.id
    WHERE strftime('%Y', t.treatment_date) = ?
    GROUP BY d.disease
    ORDER BY cnt DESC
    LIMIT 10
");
$dStmt->execute([(string)$selectedYear]);
$topDiseases = $dStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Year Wise Treated Patients</h1>
        <p class="page-subtitle">Annual patient treatment trends, disease distributions, and monthly statistics.</p>
    </div>
</div>

<!-- Select Year -->
<div class="card-custom p-3 p-md-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-sm-8 col-md-6">
            <label class="form-label fw-bold">Select Year</label>
            <select name="year" class="form-select">
                <?php for ($y = date('Y'); $y >= 2016; $y--): ?>
                    <option value="<?= $y ?>" <?= $y === $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-12 col-sm-4 col-md-6">
            <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-chart-bar me-1"></i> Load Annual Report</button>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-title">Unique Treated Patients</div>
                <div class="stat-value"><?= number_format($totalPatientsCount) ?></div>
                <div class="stat-subtext">Year <?= $selectedYear ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-success-subtle text-success">
                <i class="fa-solid fa-file-medical"></i>
            </div>
            <div>
                <div class="stat-title">Total Consultations / Treatments</div>
                <div class="stat-value"><?= number_format($totalTreatmentsCount) ?></div>
                <div class="stat-subtext">Year <?= $selectedYear ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Top Diseases -->
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card-custom p-4 h-100">
            <h5 class="fw-bold mb-3">Monthly Treatment Trend (<?= $selectedYear ?>)</h5>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="yearlyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card-custom p-4 h-100">
            <h5 class="fw-bold mb-3">Top Diagnosed Diseases (<?= $selectedYear ?>)</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light fs-8">
                        <tr>
                            <th>#</th>
                            <th>Disease Name</th>
                            <th class="text-end">Treatments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topDiseases)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No data recorded for <?= $selectedYear ?></td></tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($topDiseases as $d): ?>
                            <tr>
                                <td class="fw-bold text-muted"><?= $i++ ?></td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($d['disease']) ?></td>
                                <td class="text-end fw-bold"><span class="badge bg-secondary-subtle text-body"><?= $d['cnt'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const monthlyData = <?= json_encode(array_values($monthlyCounts)) ?>;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? '#1e2d53' : '#e2e8f0';

    const ctx = document.getElementById('yearlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Treated Patients',
                data: monthlyData,
                backgroundColor: '#0284c7',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
