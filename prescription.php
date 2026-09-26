<?php
// prescription.php
$page_title = "Prescription Details";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$treatmentId = (int)($_GET['treatment_id'] ?? 0);

if ($treatmentId <= 0) {
    echo '<div class="alert alert-danger">Invalid Treatment ID specified.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.*, d.disease,
           p.name as patient_name, p.sex, p.age, p.weight, p.contact, p.address
    FROM treatments t
    JOIN patients p ON t.patient_id = p.id
    LEFT JOIN diagnoses d ON t.diagnosis_id = d.id
    WHERE t.id = ?
");
$stmt->execute([$treatmentId]);
$treatment = $stmt->fetch();

if (!$treatment) {
    echo '<div class="alert alert-danger">Treatment record not found.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch medicines
$mStmt = $pdo->prepare("SELECT * FROM treatment_medicines WHERE treatment_id = ?");
$mStmt->execute([$treatmentId]);
$medicines = $mStmt->fetchAll();

$settings = getClinicSettings();

// Build WhatsApp Link
$waMsg = buildWhatsAppMessage(
    $settings['clinic_name'],
    $treatment['patient_name'],
    $treatment['treatment_date'],
    $treatment['disease'],
    $medicines,
    $treatment['followup_date'],
    $treatment['fee'],
    $settings['doctor_name'],
    $settings['contact_no']
);
$waUrl = buildWhatsAppUrl($treatment['contact'], $waMsg);
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2 no-print">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-file-prescription text-primary me-2"></i>Medical Prescription</h1>
        <p class="page-subtitle">View, print, download PDF, or send via WhatsApp.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-print me-1"></i> Print</button>
        <button id="btnDownloadPdf" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i> Download PDF</button>
        <a href="<?= $waUrl ?>" target="_blank" class="btn btn-success btn-sm"><i class="fa-brands fa-whatsapp me-1"></i> Share WhatsApp</a>
    </div>
</div>

<!-- Printable Prescription Container -->
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div id="prescriptionContent" class="card-custom p-4 p-md-5 bg-white text-dark shadow-sm">

            <!-- Clinic & Doctor Header -->
            <div class="border-bottom border-2 border-primary pb-3 mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-primary mb-1" style="letter-spacing:-0.5px;"><?= htmlspecialchars($settings['clinic_name']) ?></h2>
                    <p class="text-muted mb-0 fs-7"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($settings['address']) ?></p>
                    <p class="text-muted mb-0 fs-7"><i class="fa-solid fa-phone me-1"></i>Contact: <?= htmlspecialchars($settings['contact_no']) ?></p>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($settings['doctor_name']) ?></h5>
                    <div class="badge bg-primary-subtle text-primary mb-1"><?= htmlspecialchars($settings['qualification']) ?></div>
                    <p class="text-muted mb-0 fs-8">Reg. No: <?= htmlspecialchars($settings['registration_no']) ?></p>
                </div>
            </div>

            <!-- Patient Information Box -->
            <div class="p-3 border rounded mb-4 bg-light">
                <div class="row g-2 fs-7">
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block fs-8 text-uppercase">Patient Name</span>
                        <strong class="fs-6 text-primary"><?= htmlspecialchars($treatment['patient_name']) ?></strong>
                    </div>
                    <div class="col-3 col-md-2">
                        <span class="text-muted d-block fs-8 text-uppercase">Sex / Age</span>
                        <strong><?= htmlspecialchars($treatment['sex']) ?> / <?= htmlspecialchars($treatment['age'] ?? 'N/A') ?> yrs</strong>
                    </div>
                    <div class="col-3 col-md-2">
                        <span class="text-muted d-block fs-8 text-uppercase">Weight</span>
                        <strong><?= htmlspecialchars(($treatment['weight'] ?? 'N/A') . ' kg') ?></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block fs-8 text-uppercase">Contact</span>
                        <strong><?= htmlspecialchars($treatment['contact']) ?></strong>
                    </div>
                    <div class="col-6 col-md-2 text-end">
                        <span class="text-muted d-block fs-8 text-uppercase">Date & Time</span>
                        <strong><?= formatDate($treatment['treatment_date']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Diagnosis Row -->
            <div class="mb-4">
                <h6 class="fw-bold text-uppercase text-muted fs-8 mb-1">Diagnosis / Clinical Findings</h6>
                <div class="p-2 border-start border-4 border-primary bg-light fs-6 fw-bold text-dark">
                    <?= htmlspecialchars($treatment['disease'] ?: 'General Examination') ?>
                </div>
            </div>

            <!-- Rx / Medicines Table -->
            <div class="mb-4">
                <div class="d-flex align-items-center mb-2">
                    <span class="fs-3 fw-bold text-primary me-2" style="font-family: serif;">Rx</span>
                    <span class="fw-bold text-uppercase text-muted fs-8">Prescribed Medicines</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 90px;">Type</th>
                                <th>Medicine Name</th>
                                <th style="width: 100px;">Dose</th>
                                <th style="width: 140px;">Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($medicines)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No medicines prescribed.</td></tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($medicines as $m): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $i++ ?></td>
                                    <td><span class="badge bg-secondary-subtle text-dark"><?= htmlspecialchars($m['medicine_type']) ?></span></td>
                                    <td class="fw-bold fs-6"><?= htmlspecialchars($m['medicine_name']) ?></td>
                                    <td><?= htmlspecialchars($m['dose']) ?></td>
                                    <td><code><?= htmlspecialchars($m['schedule']) ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Details (Follow-up, Fees, Doctor Signature) -->
            <div class="row g-3 border-top pt-4 mt-4 align-items-end">
                <div class="col-6 col-md-4">
                    <small class="text-muted d-block fs-8 text-uppercase">Follow-up Date</small>
                    <div class="fw-bold text-primary fs-6"><?= formatDate($treatment['followup_date']) ?></div>
                </div>
                <div class="col-6 col-md-4">
                    <small class="text-muted d-block fs-8 text-uppercase">Treatment Fee Paid</small>
                    <div class="fw-bold text-success fs-6"><?= formatCurrency($treatment['fee']) ?></div>
                </div>
                <div class="col-12 col-md-4 text-end">
                    <div style="height: 40px;"></div>
                    <div class="border-top d-inline-block pt-1 text-center" style="min-width: 160px;">
                        <small class="fw-bold text-dark d-block"><?= htmlspecialchars($settings['doctor_name']) ?></small>
                        <small class="text-muted fs-9">Doctor Signature</small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    .no-print, #sidebar, .topbar, footer {
        display: none !important;
    }
    #main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    #prescriptionContent {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#btnDownloadPdf').on('click', function() {
        const element = document.getElementById('prescriptionContent');
        const opt = {
          margin:       0.5,
          filename:     'Prescription_<?= $treatment['id'] ?>_<?= str_replace(' ', '_', $treatment['patient_name']) ?>.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2 },
          jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
