<?php
// todays-patients.php
$page_title = "Today's Patients";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$today = date('Y-m-d');

// Fetch today's treatments joined with patients and diagnoses
$stmt = $pdo->prepare("
    SELECT t.id as treatment_id, t.treatment_time, t.fee,
           p.id as patient_id, p.name as patient_name, p.sex, p.age, p.contact,
           d.disease
    FROM treatments t
    JOIN patients p ON t.patient_id = p.id
    LEFT JOIN diagnoses d ON t.diagnosis_id = d.id
    WHERE t.treatment_date = ?
    ORDER BY t.treatment_time DESC
");
$stmt->execute([$today]);
$patientsList = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-calendar-day text-primary me-2"></i>Today's Patients</h1>
        <p class="page-subtitle">List of all patients treated or consulted today (<?= date('d M Y') ?>).</p>
    </div>
    <div>
        <a href="treatment.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> New Treatment</a>
    </div>
</div>

<div class="card-custom p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" id="tableFilterInput" class="form-control" placeholder="Filter today's list...">
        </div>
        <span class="badge bg-primary fs-7 px-3 py-2">
            Total Today: <?= count($patientsList) ?> Patient(s)
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom align-middle" id="todaysPatientsTable">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Patient Name</th>
                    <th>Sex</th>
                    <th>Age</th>
                    <th>Contact</th>
                    <th>Disease / Diagnosis</th>
                    <th>Treatment Time</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patientsList)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-user-slash fs-2 d-block mb-2"></i>
                        No patients treated or registered today yet.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $sr = 1; foreach ($patientsList as $p): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $sr++ ?></td>
                        <td>
                            <strong class="text-primary fs-6"><?= htmlspecialchars($p['patient_name']) ?></strong>
                        </td>
                        <td><span class="badge bg-secondary-subtle text-body"><?= htmlspecialchars($p['sex']) ?></span></td>
                        <td><?= htmlspecialchars($p['age'] ?? 'N/A') ?> yrs</td>
                        <td><i class="fa-solid fa-phone fs-9 text-muted me-1"></i><?= htmlspecialchars($p['contact']) ?></td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">
                                <?= htmlspecialchars($p['disease'] ?: 'General Consult') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fa-regular fa-clock me-1"></i><?= formatTime($p['treatment_time']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="treatment.php?patient_id=<?= $p['patient_id'] ?>" class="btn btn-outline-success" title="Add New Treatment"><i class="fa-solid fa-stethoscope"></i></a>
                                <a href="prescription.php?treatment_id=<?= $p['treatment_id'] ?>" class="btn btn-outline-primary" title="View Prescription"><i class="fa-solid fa-file-prescription"></i></a>
                                <a href="search-patient.php?patient_id=<?= $p['patient_id'] ?>" class="btn btn-outline-info" title="View Patient Details"><i class="fa-solid fa-eye"></i></a>
                                <button type="button" class="btn btn-outline-danger btn-delete-t" data-id="<?= $p['treatment_id'] ?>" title="Delete Record"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#tableFilterInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $("#todaysPatientsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('.btn-delete-t').on('click', function() {
        const tId = $(this).data('id');
        confirmDelete('Delete Treatment?', 'Are you sure you want to delete this treatment record?', function() {
            $.post('api/ajax.php?action=delete_treatment', { id: tId }, function(res) {
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
