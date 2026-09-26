<?php
// treated-disease-report.php
$page_title = "Treated Disease Report";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

$fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$toDate = $_GET['to_date'] ?? date('Y-m-d');

// Query disease counts
$stmt = $pdo->prepare("
    SELECT d.disease, COUNT(*) as cnt
    FROM treatments t
    JOIN diagnoses d ON t.diagnosis_id = d.id
    WHERE t.treatment_date BETWEEN ? AND ?
    GROUP BY d.disease
    ORDER BY cnt DESC
");
$stmt->execute([$fromDate, $toDate]);
$diseaseStats = $stmt->fetchAll();

$totalDiseasesCount = array_sum(array_column($diseaseStats, 'cnt'));

$chartLabels = [];
$chartData = [];
foreach ($diseaseStats as $ds) {
    $chartLabels[] = $ds['disease'];
    $chartData[] = (int)$ds['cnt'];
}
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-virus text-primary me-2"></i>Treated Disease Report</h1>
        <p class="page-subtitle">Disease-wise consultation analytics and proportion distribution chart.</p>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card-custom p-3 p-md-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">Start Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" required>
        </div>
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label fw-bold">End Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($toDate) ?>" required>
        </div>
        <div class="col-12 col-sm-2 col-md-4">
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter Report</button>
        </div>
    </form>
</div>

<!-- Chart & Table View -->
<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card-custom p-4 h-100 text-center">
            <h5 class="fw-bold mb-3">Disease Distribution Chart</h5>
            <p class="text-muted fs-8">Proportion of treated diseases from <?= formatDate($fromDate) ?> to <?= formatDate($toDate) ?></p>
            <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center; align-items: center;">
                <?php if (empty($diseaseStats)): ?>
                    <span class="text-muted">No data available for chart</span>
                <?php else: ?>
                    <canvas id="diseaseDoughnutChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card-custom p-4 h-100">
            <h5 class="fw-bold mb-3">Disease Statistics Breakdown</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light fs-8">
                        <tr>
                            <th>#</th>
                            <th>Disease Name</th>
                            <th class="text-end">Treated Count</th>
                            <th class="text-end">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($diseaseStats)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No disease records found.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($diseaseStats as $ds):
                                $pct = $totalDiseasesCount > 0 ? round(($ds['cnt'] / $totalDiseasesCount) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td class="fw-bold text-muted"><?= $i++ ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($ds['disease']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($ds['cnt']) ?></td>
                                <td class="text-end">
                                    <span class="badge bg-primary-subtle text-primary fw-bold"><?= $pct ?>%</span>
                                </td>
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
    <?php if (!empty($diseaseStats)): ?>
    const labels = <?= json_encode($chartLabels) ?>;
    const data = <?= json_encode($chartData) ?>;

    const ctx = document.getElementById('diseaseDoughnutChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#0284c7', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
