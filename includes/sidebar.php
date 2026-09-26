<?php
// includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand-icon">
            <i class="fa-solid fa-user-doctor"></i>
        </div>
        <div class="sidebar-brand-text">
            <span class="clinic-title"><?= htmlspecialchars($settings['clinic_name']) ?></span>
            <span class="doctor-sub"><?= htmlspecialchars($settings['doctor_name']) ?></span>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-header">Main Menu</li>

        <li>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="search-patient.php" class="<?= $currentPage === 'search-patient.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Search Patient</span>
            </a>
        </li>

        <li>
            <a href="todays-patients.php" class="<?= $currentPage === 'todays-patients.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-day"></i>
                <span>Today's Patients</span>
            </a>
        </li>

        <li>
            <a href="add-patient.php" class="<?= $currentPage === 'add-patient.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add Patient</span>
            </a>
        </li>

        <li class="menu-header">Clinical & Workflow</li>

        <li>
            <a href="treatment.php" class="<?= $currentPage === 'treatment.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-stethoscope"></i>
                <span>Treatment</span>
            </a>
        </li>

        <li>
            <a href="treatment-history.php" class="<?= $currentPage === 'treatment-history.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Treatment History</span>
            </a>
        </li>

        <li>
            <a href="add-diagnosis.php" class="<?= $currentPage === 'add-diagnosis.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-pills"></i>
                <span>Add Diagnosis & Meds</span>
            </a>
        </li>

        <li>
            <a href="followups.php" class="<?= $currentPage === 'followups.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-notes-medical"></i>
                <span>Follow-ups</span>
            </a>
        </li>

        <li class="menu-header">Treatment Reports</li>

        <li>
            <a href="monthly-treatment-report.php" class="<?= $currentPage === 'monthly-treatment-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Monthly Treatment Report</span>
            </a>
        </li>

        <li>
            <a href="yearly-treatment-report.php" class="<?= $currentPage === 'yearly-treatment-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Year Wise Treated Patients</span>
            </a>
        </li>

        <li>
            <a href="date-range-treatment-report.php" class="<?= $currentPage === 'date-range-treatment-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-range"></i>
                <span>Date Range Treatment Report</span>
            </a>
        </li>

        <li>
            <a href="treated-disease-report.php" class="<?= $currentPage === 'treated-disease-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-virus"></i>
                <span>Treated Disease Report</span>
            </a>
        </li>

        <li>
            <a href="month-disease-report.php" class="<?= $currentPage === 'month-disease-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-column"></i>
                <span>Month Disease Report</span>
            </a>
        </li>

        <li class="menu-header">Fees Management</li>

        <li>
            <a href="fees-dashboard.php" class="<?= $currentPage === 'fees-dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-indian-rupee-sign"></i>
                <span>Fees Dashboard</span>
            </a>
        </li>

        <li>
            <a href="patient-fees-report.php" class="<?= $currentPage === 'patient-fees-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Patient Fees Report</span>
            </a>
        </li>

        <li>
            <a href="monthly-fees-report.php" class="<?= $currentPage === 'monthly-fees-report.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-csv"></i>
                <span>Monthly Fees Report</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm text-start ps-3">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>
    </div>
</aside>
