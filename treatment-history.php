<?php
// treatment-history.php
$page_title = "Treatment History";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$patientId = (int)($_GET['patient_id'] ?? 0);

$selectedPatient = null;
$treatments = [];

if ($patientId > 0) {
    $pStmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $pStmt->execute([$patientId]);
    $selectedPatient = $pStmt->fetch();

    $tStmt = $pdo->prepare("
        SELECT t.*, d.disease
        FROM treatments t
        LEFT JOIN diagnoses d ON t.diagnosis_id = d.id
        WHERE t.patient_id = ?
        ORDER BY t.treatment_date DESC, t.treatment_time DESC
    ");
    $tStmt->execute([$patientId]);
    $treatments = $tStmt->fetchAll();
}
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Patient Treatment History</h1>
        <p class="page-subtitle">Complete timeline of consultations, diagnoses, medicines, and fees for a patient.</p>
    </div>
    <div>
        <a href="search-patient.php" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i> Search Patient</a>
    </div>
</div>

<?php if (!$selectedPatient): ?>
<div class="card-custom p-5 text-center">
    <i class="fa-solid fa-id-card fs-1 text-muted mb-3 d-block"></i>
    <h4 class="fw-bold">No Patient Selected</h4>
    <p class="text-muted">Please search and select a patient to view their complete clinical history.</p>
    <div>
        <a href="search-patient.php" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i> Go to Search Patient</a>
    </div>
</div>
<?php else: ?>

<!-- Patient Header Card -->
<div class="card-custom p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-primary mb-1"><?= htmlspecialchars($selectedPatient['name']) ?></h3>
            <div class="text-muted fs-7">
                <span><i class="fa-solid fa-venus-mars me-1"></i><?= htmlspecialchars($selectedPatient['sex']) ?></span> |
                <span><i class="fa-solid fa-cake-candles me-1"></i><?= htmlspecialchars($selectedPatient['age'] ?? 'N/A') ?> Yrs</span> |
                <span><i class="fa-solid fa-weight-scale me-1"></i><?= htmlspecialchars($selectedPatient['weight'] ?? 'N/A') ?> kg</span> |
                <span><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($selectedPatient['contact']) ?></span>
            </div>
        </div>
        <div>
            <a href="treatment.php?patient_id=<?= $selectedPatient['id'] ?>" class="btn btn-success"><i class="fa-solid fa-plus me-1"></i> New Treatment</a>
        </div>
    </div>
</div>

<!-- Treatments Timeline -->
<?php if (empty($treatments)): ?>
<div class="card-custom p-4 text-center text-muted">
    <p class="mb-0">No treatment records found for this patient.</p>
</div>
<?php else: ?>
    <?php foreach ($treatments as $t):
        // Fetch medicines for this treatment
        $mStmt = $pdo->prepare("SELECT * FROM treatment_medicines WHERE treatment_id = ?");
        $mStmt->execute([$t['id']]);
        $meds = $mStmt->fetchAll();

        $settings = getClinicSettings();
        $waMsg = buildWhatsAppMessage($settings['clinic_name'], $selectedPatient['name'], $t['treatment_date'], $t['disease'], $meds, $t['followup_date'], $t['fee'], $settings['doctor_name'], $settings['contact_no']);
        $waUrl = buildWhatsAppUrl($selectedPatient['contact'], $waMsg);
    ?>
    <div class="card-custom p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3 flex-wrap gap-2">
            <div>
                <span class="badge bg-primary fs-7 px-3 py-2 mb-1">
                    <i class="fa-solid fa-calendar me-1"></i> <?= formatDate($t['treatment_date']) ?> at <?= formatTime($t['treatment_time']) ?>
                </span>
                <h5 class="fw-bold text-dark mt-2 mb-0">
                    Diagnosis: <span class="text-primary"><?= htmlspecialchars($t['disease'] ?: 'General Consult') ?></span>
                </h5>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="prescription.php?treatment_id=<?= $t['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-print me-1"></i> Prescription</a>
                <a href="<?= $waUrl ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="fa-brands fa-whatsapp me-1"></i> WhatsApp</a>
                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-t" data-id="<?= $t['id'] ?>"><i class="fa-solid fa-trash me-1"></i> Delete</button>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-3">
                <small class="text-muted d-block fs-8">Doctor Details</small>
                <div class="fw-semibold fs-7"><?= htmlspecialchars($t['doctor_details'] ?: 'N/A') ?></div>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block fs-8">DT Status</small>
                <span class="badge bg-secondary-subtle text-body fw-bold"><?= htmlspecialchars($t['dt']) ?></span>
            </div>
            <div class="col-6 col-md-3">
                <small class="text-muted d-block fs-8">Treatment Fee</small>
                <div class="fw-bold text-success fs-6"><?= formatCurrency($t['fee']) ?></div>
            </div>
            <div class="col-12 col-md-3">
                <small class="text-muted d-block fs-8">Follow-up Date</small>
                <div class="fw-bold text-info fs-7"><?= formatDate($t['followup_date']) ?></div>
            </div>
        </div>

        <!-- Prescribed Medicines Table -->
        <h6 class="fw-bold fs-7 text-uppercase text-muted mb-2">Prescribed Medicines</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light fs-8">
                    <tr>
                        <th style="width: 80px;">Type</th>
                        <th>Medicine Name</th>
                        <th style="width: 100px;">Dose</th>
                        <th style="width: 150px;">Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($meds)): ?>
                        <tr><td colspan="4" class="text-muted text-center fs-8">No medicines recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($meds as $m): ?>
                        <tr class="fs-7">
                            <td><code><?= htmlspecialchars($m['medicine_type']) ?></code></td>
                            <td class="fw-bold"><?= htmlspecialchars($m['medicine_name']) ?></td>
                            <td><?= htmlspecialchars($m['dose']) ?></td>
                            <td><code><?= htmlspecialchars($m['schedule']) ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('.btn-delete-t').on('click', function() {
        const id = $(this).data('id');
        confirmDelete('Delete Treatment Record?', 'Are you sure you want to delete this treatment history entry?', function() {
            $.post('api/ajax.php?action=delete_treatment', { id: id }, function(res) {
                if (res.status === 'success') {
                    showToast(res.message);
                    location.reload();
                } else {
                    showToast(res.message, 'error');
                }
            }, 'json');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
