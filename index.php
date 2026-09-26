<?php
// index.php
$page_title = "Dashboard";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$today = date('Y-m-d');
$currentMonth = date('Y-m');

// Stat 1: Total Patients
$totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();

// Stat 2: Patients Treated Today
$stmtTreatedToday = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) FROM treatments WHERE treatment_date = ?");
$stmtTreatedToday->execute([$today]);
$treatedTodayCount = (int)$stmtTreatedToday->fetchColumn();

// Stat 3: Patients Added Today
$stmtAddedToday = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = ?");
$stmtAddedToday->execute([$today]);
$addedTodayCount = (int)$stmtAddedToday->fetchColumn();

// Stat 4: Monthly Treatments
$stmtMonthlyTreatments = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE strftime('%Y-%m', treatment_date) = ?");
$stmtMonthlyTreatments->execute([$currentMonth]);
$monthlyTreatmentsCount = (int)$stmtMonthlyTreatments->fetchColumn();

// Stat 5: Total Fees
$totalFees = (float)$pdo->query("SELECT COALESCE(SUM(fee), 0) FROM treatments")->fetchColumn();

// Stat 6: Male Patients
$malePatients = (int)$pdo->query("SELECT COUNT(*) FROM patients WHERE LOWER(sex) = 'male'")->fetchColumn();

// Stat 7: Female Patients
$femalePatients = (int)$pdo->query("SELECT COUNT(*) FROM patients WHERE LOWER(sex) = 'female'")->fetchColumn();

// Follow-up notification check for today
$stmtDueFollowups = $pdo->prepare("SELECT COUNT(*) FROM followups WHERE followup_date <= ? AND status = 'Pending'");
$stmtDueFollowups->execute([$today]);
$dueFollowupsCount = (int)$stmtDueFollowups->fetchColumn();
?>

<!-- Due Follow-ups Alert Banner -->
<?php if ($dueFollowupsCount > 0): ?>
<div class="alert alert-warning alert-dismissible fade show border-warning d-flex align-items-center gap-3 mb-4 shadow-sm" role="alert">
    <div class="fs-2 text-warning">
        <i class="fa-solid fa-bell fa-bounce"></i>
    </div>
    <div class="flex-grow-1">
        <h6 class="fw-bold mb-1">Follow-up Reminder Alert!</h6>
        <p class="mb-0 fs-7">You have <strong><?= $dueFollowupsCount ?></strong> patient follow-up(s) due today or pending.
            <a href="followups.php" class="fw-bold text-decoration-underline text-warning-emphasis ms-1">View Follow-ups <i class="fa-solid fa-arrow-right fs-8"></i></a>
        </p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title">Doctor Dashboard</h1>
        <p class="page-subtitle mb-0">Overview of clinic statistics, treatments, and daily activity.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="add-patient.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus me-1"></i> Add Patient</a>
        <a href="treatment.php" class="btn btn-success btn-sm"><i class="fa-solid fa-stethoscope me-1"></i> Add Treatment</a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Total Patients -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-title">Total Patients</div>
                <div class="stat-value"><?= number_format($totalPatients) ?></div>
                <div class="stat-subtext">Registered in clinic</div>
            </div>
        </div>
    </div>

    <!-- Patients Treated Today -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-success-subtle text-success">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="stat-title">Treated Today</div>
                <div class="stat-value"><?= number_format($treatedTodayCount) ?></div>
                <div class="stat-subtext"><?= date('d M Y') ?></div>
            </div>
        </div>
    </div>

    <!-- Patients Added Today -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-info-subtle text-info">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <div class="stat-title">Added Today</div>
                <div class="stat-value"><?= number_format($addedTodayCount) ?></div>
                <div class="stat-subtext">New consultations</div>
            </div>
        </div>
    </div>

    <!-- Monthly Treatments -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                <i class="fa-solid fa-file-medical"></i>
            </div>
            <div>
                <div class="stat-title">Monthly Treatments</div>
                <div class="stat-value"><?= number_format($monthlyTreatmentsCount) ?></div>
                <div class="stat-subtext"><?= date('F Y') ?></div>
            </div>
        </div>
    </div>

    <!-- Total Fees -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-success-subtle text-success">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div>
                <div class="stat-title">Total Revenue / Fees</div>
                <div class="stat-value"><?= formatCurrency($totalFees) ?></div>
                <div class="stat-subtext">Lifetime total earnings</div>
            </div>
        </div>
    </div>

    <!-- Male Patients -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                <i class="fa-solid fa-mars"></i>
            </div>
            <div>
                <div class="stat-title">Male Patients</div>
                <div class="stat-value"><?= number_format($malePatients) ?></div>
                <div class="stat-subtext"><?= $totalPatients > 0 ? round(($malePatients/$totalPatients)*100, 1).'%' : '0%' ?> of total</div>
            </div>
        </div>
    </div>

    <!-- Female Patients -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card-custom stat-card">
            <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                <i class="fa-solid fa-venus"></i>
            </div>
            <div>
                <div class="stat-title">Female Patients</div>
                <div class="stat-value"><?= number_format($femalePatients) ?></div>
                <div class="stat-subtext"><?= $totalPatients > 0 ? round(($femalePatients/$totalPatients)*100, 1).'%' : '0%' ?> of total</div>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Today's Summary -->
<div class="row g-4">
    <!-- Chart Column -->
    <div class="col-12 col-lg-8">
        <div class="card-custom p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0">Patients Treated Report</h5>
                    <small class="text-muted">Visual breakdown of patient consultations over time</small>
                </div>
                <div class="btn-group btn-group-sm" role="group" id="chartRangeToggle">
                    <button type="button" class="btn btn-outline-primary active" data-range="daily">Daily</button>
                    <button type="button" class="btn btn-outline-primary" data-range="weekly">Weekly</button>
                    <button type="button" class="btn btn-outline-primary" data-range="monthly">Monthly</button>
                </div>
            </div>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="treatedPatientsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Today's Summary Column -->
    <div class="col-12 col-lg-4">
        <div class="card-custom p-3 p-md-4 h-100 d-flex flex-direction-column justify-content-between">
            <div>
                <h5 class="fw-bold mb-3">Quick Actions</h5>
                <div class="d-grid gap-2 mb-4">
                    <a href="add-patient.php" class="btn btn-outline-primary text-start d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-user-plus me-2"></i> Add New Patient</span>
                        <i class="fa-solid fa-chevron-right fs-8"></i>
                    </a>
                    <a href="treatment.php" class="btn btn-outline-success text-start d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-stethoscope me-2"></i> New Clinical Treatment</span>
                        <i class="fa-solid fa-chevron-right fs-8"></i>
                    </a>
                    <a href="search-patient.php" class="btn btn-outline-info text-start d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-magnifying-glass me-2"></i> Search Patient Records</span>
                        <i class="fa-solid fa-chevron-right fs-8"></i>
                    </a>
                    <a href="todays-patients.php" class="btn btn-outline-warning text-start d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-calendar-day me-2"></i> View Today's Queue</span>
                        <i class="fa-solid fa-chevron-right fs-8"></i>
                    </a>
                    <a href="fees-dashboard.php" class="btn btn-outline-secondary text-start d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-indian-rupee-sign me-2"></i> Fees Dashboard</span>
                        <i class="fa-solid fa-chevron-right fs-8"></i>
                    </a>
                </div>
            </div>

            <div class="border-top pt-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted d-block">Today's Clinic Summary</small>
                        <strong class="fs-6"><?= date('l, d F Y') ?></strong>
                    </div>
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                        <i class="fa-solid fa-circle me-1 fs-9"></i> Active Session
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let treatedChart = null;

    function renderChart(range) {
        $.getJSON('api/ajax.php?action=get_chart_data&range=' + range, function(res) {
            const ctx = document.getElementById('treatedPatientsChart').getContext('2d');

            if (treatedChart) {
                treatedChart.destroy();
            }

            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? '#1e2d53' : '#e2e8f0';

            treatedChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: res.labels,
                    datasets: [{
                        label: 'Patients Treated',
                        data: res.values,
                        backgroundColor: 'rgba(2, 132, 199, 0.15)',
                        borderColor: '#0284c7',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0284c7',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: gridColor },
                            ticks: { color: textColor }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: textColor, precision: 0 }
                        }
                    }
                }
            });
        });
    }

    renderChart('daily');

    $('#chartRangeToggle button').on('click', function() {
        $('#chartRangeToggle button').removeClass('active');
        $(this).addClass('active');
        renderChart($(this).data('range'));
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
