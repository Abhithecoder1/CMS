<?php
// includes/topbar.php
?>
<header class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button id="sidebarToggle" class="btn-sidebar-toggle" title="Toggle Navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="d-none d-md-block ms-2 fw-semibold text-secondary">
            <?= htmlspecialchars($settings['clinic_name']) ?>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Quick Actions Dropdown -->
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                <i class="fa-solid fa-bolt"></i>
                <span class="d-none d-sm-inline">Quick Actions</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="add-patient.php"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add Patient</a></li>
                <li><a class="dropdown-item" href="treatment.php"><i class="fa-solid fa-stethoscope me-2 text-success"></i>Add Treatment</a></li>
                <li><a class="dropdown-item" href="search-patient.php"><i class="fa-solid fa-magnifying-glass me-2 text-info"></i>Search Patient</a></li>
                <li><a class="dropdown-item" href="todays-patients.php"><i class="fa-solid fa-calendar-day me-2 text-warning"></i>Today's Patients</a></li>
                <li><a class="dropdown-item" href="add-diagnosis.php"><i class="fa-solid fa-pills me-2 text-secondary"></i>Add Diagnosis</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="fees-dashboard.php"><i class="fa-solid fa-indian-rupee-sign me-2 text-success"></i>Fees Report</a></li>
            </ul>
        </div>

        <!-- Light/Dark Mode Toggle Button -->
        <button id="themeToggle" class="btn btn-outline-secondary btn-sm rounded-circle p-2" style="width:36px; height:36px;" title="Toggle Light/Dark Theme">
            <i id="themeIcon" class="fa-solid fa-moon"></i>
        </button>

        <!-- Doctor Profile Badge -->
        <div class="d-flex align-items-center gap-2 ps-2 border-start">
            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                <i class="fa-solid fa-user-md"></i>
            </div>
            <div class="d-none d-lg-block text-end">
                <div class="fw-semibold fs-7 lh-1 text-primary"><?= htmlspecialchars($_SESSION['doctor_name'] ?? $settings['doctor_name']) ?></div>
                <small class="text-muted fs-8"><?= htmlspecialchars($settings['qualification']) ?></small>
            </div>
        </div>
    </div>
</header>
