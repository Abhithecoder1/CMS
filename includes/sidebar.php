<?php
// includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);

// Define active states for dropdown groups
$patientPages = ['search-patient.php', 'todays-patients.php', 'add-patient.php'];
$clinicalPages = ['treatment.php', 'treatment-history.php', 'add-diagnosis.php', 'followups.php', 'prescription.php'];
$reportPages = ['monthly-treatment-report.php', 'yearly-treatment-report.php', 'date-range-treatment-report.php', 'treated-disease-report.php', 'month-disease-report.php'];
$feesPages = ['fees-dashboard.php', 'patient-fees-report.php', 'monthly-fees-report.php'];

$isPatientActive = in_array($currentPage, $patientPages);
$isClinicalActive = in_array($currentPage, $clinicalPages);
$isReportActive = in_array($currentPage, $reportPages);
$isFeesActive = in_array($currentPage, $feesPages);
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

        <!-- Patient Management Dropdown -->
        <li class="nav-item-dropdown">
            <a class="dropdown-toggle-nav <?= $isPatientActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#patientMenu" role="button" aria-expanded="<?= $isPatientActive ? 'true' : 'false' ?>">
                <i class="fa-solid fa-users"></i>
                <span class="flex-grow-1">Patient Management</span>
                <i class="fa-solid fa-chevron-down toggle-arrow"></i>
            </a>
            <div class="collapse <?= $isPatientActive ? 'show' : '' ?>" id="patientMenu">
                <ul class="sidebar-submenu">
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
                </ul>
            </div>
        </li>

        <!-- Clinical & Workflow Dropdown -->
        <li class="nav-item-dropdown">
            <a class="dropdown-toggle-nav <?= $isClinicalActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#clinicalMenu" role="button" aria-expanded="<?= $isClinicalActive ? 'true' : 'false' ?>">
                <i class="fa-solid fa-stethoscope"></i>
                <span class="flex-grow-1">Clinical & Treatment</span>
                <i class="fa-solid fa-chevron-down toggle-arrow"></i>
            </a>
            <div class="collapse <?= $isClinicalActive ? 'show' : '' ?>" id="clinicalMenu">
                <ul class="sidebar-submenu">
                    <li>
                        <a href="treatment.php" class="<?= $currentPage === 'treatment.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-notes-medical"></i>
                            <span>Add Treatment</span>
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
                            <span>Diagnosis & Meds</span>
                        </a>
                    </li>
                    <li>
                        <a href="followups.php" class="<?= $currentPage === 'followups.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-bell"></i>
                            <span>Follow-ups</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Treatment Reports Dropdown -->
        <li class="nav-item-dropdown">
            <a class="dropdown-toggle-nav <?= $isReportActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#reportMenu" role="button" aria-expanded="<?= $isReportActive ? 'true' : 'false' ?>">
                <i class="fa-solid fa-chart-column"></i>
                <span class="flex-grow-1">Treatment Reports</span>
                <i class="fa-solid fa-chevron-down toggle-arrow"></i>
            </a>
            <div class="collapse <?= $isReportActive ? 'show' : '' ?>" id="reportMenu">
                <ul class="sidebar-submenu">
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
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Month Disease Report</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Fees Management Dropdown -->
        <li class="nav-item-dropdown">
            <a class="dropdown-toggle-nav <?= $isFeesActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#feesMenu" role="button" aria-expanded="<?= $isFeesActive ? 'true' : 'false' ?>">
                <i class="fa-solid fa-indian-rupee-sign"></i>
                <span class="flex-grow-1">Fees Management</span>
                <i class="fa-solid fa-chevron-down toggle-arrow"></i>
            </a>
            <div class="collapse <?= $isFeesActive ? 'show' : '' ?>" id="feesMenu">
                <ul class="sidebar-submenu">
                    <li>
                        <a href="fees-dashboard.php" class="<?= $currentPage === 'fees-dashboard.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span>Fees Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="patient-fees-report.php" class="<?= $currentPage === 'patient-fees-report.php' ? 'active' : '' ?>">
                            <i class="fa-solid fa-user-tag"></i>
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
            </div>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm text-start ps-3">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>
    </div>
</aside>
