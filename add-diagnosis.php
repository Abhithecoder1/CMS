<?php
// add-diagnosis.php
$page_title = "Add Diagnosis & Medicines";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Stats
$totalDiseases = (int)$pdo->query("SELECT COUNT(*) FROM diagnoses")->fetchColumn();
$totalMedicines = (int)$pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();

// Fetch all diagnoses for display
$stmt = $pdo->query("SELECT * FROM diagnoses ORDER BY disease ASC");
$allDiagnoses = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-pills text-primary me-2"></i>Diagnosis & Medicine Management</h1>
        <p class="page-subtitle">Configure diseases and standard prescription medicine templates.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-primary fs-7 px-3 py-2">
            <i class="fa-solid fa-virus me-1"></i> Total Diseases: <?= $totalDiseases ?>
        </span>
        <span class="badge bg-success fs-7 px-3 py-2">
            <i class="fa-solid fa-capsules me-1"></i> Total Medicines: <?= $totalMedicines ?>
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Form Column -->
    <div class="col-12 col-lg-5">
        <div class="card-custom p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-plus text-success me-2"></i>Add / Update Diagnosis</h5>
            <form id="diagnosisForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">Disease / Diagnosis Name <span class="text-danger">*</span></label>
                    <input type="text" name="disease" id="diseaseInput" class="form-control" placeholder="e.g. VIRAL FEVER (? DF)" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold mb-0">Prescribed Medicines</label>
                    <button type="button" id="addMedRowBtn" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Add Row</button>
                </div>

                <div id="medicineRowsContainer">
                    <!-- Default 1 row -->
                    <div class="medicine-row border p-2 rounded mb-2 bg-body-tertiary">
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <select name="med_type[]" class="form-select form-select-sm">
                                    <option value="TAB">TAB</option>
                                    <option value="CAP">CAP</option>
                                    <option value="SYP">SYP</option>
                                    <option value="INJ">INJ</option>
                                    <option value="CRM">CRM</option>
                                    <option value="DRP">DRP</option>
                                </select>
                            </div>
                            <div class="col-8">
                                <input type="text" name="med_name[]" class="form-control form-select-sm med-name-input" placeholder="Medicine Name" required>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-5">
                                <input type="text" name="med_dose[]" class="form-control form-select-sm" placeholder="Dose (e.g. 4)">
                            </div>
                            <div class="col-5">
                                <input type="text" name="med_schedule[]" class="form-control form-select-sm" placeholder="Schedule (1-0-1)">
                            </div>
                            <div class="col-2 text-end">
                                <button type="button" class="btn btn-danger btn-sm w-100 remove-row-btn"><i class="fa-solid fa-trash fs-8"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Diagnosis Template</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Diseases Display Column -->
    <div class="col-12 col-lg-7">
        <div class="card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0">Saved Diagnosis Templates</h5>
                <div class="input-group input-group-sm" style="max-width:250px;">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="diseaseSearchInput" class="form-control" placeholder="Search disease...">
                </div>
            </div>

            <div id="diseaseCardsContainer" class="d-flex flex-column gap-3" style="max-height: 600px; overflow-y: auto;">
                <?php if (empty($allDiagnoses)): ?>
                    <p class="text-muted text-center py-4">No diagnoses created yet.</p>
                <?php else: ?>
                    <?php foreach ($allDiagnoses as $d):
                        $medStmt = $pdo->prepare("SELECT dm.*, m.medicine_type, m.medicine_name
                            FROM diagnosis_medicines dm
                            JOIN medicines m ON dm.medicine_id = m.id
                            WHERE dm.diagnosis_id = ?");
                        $medStmt->execute([$d['id']]);
                        $meds = $medStmt->fetchAll();
                    ?>
                    <div class="border rounded p-3 bg-body disease-card-item">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                            <div>
                                <h6 class="fw-bold text-primary mb-1 disease-title-text"><?= htmlspecialchars($d['disease']) ?></h6>
                                <small class="text-muted fs-8">
                                    <i class="fa-regular fa-clock me-1"></i>Updated: <?= formatDate($d['updated_at']) ?> |
                                    <span class="badge bg-secondary-subtle text-body"><?= count($meds) ?> Medicine(s)</span>
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-diag" data-id="<?= $d['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>

                        <div class="row g-2">
                            <?php foreach ($meds as $m): ?>
                            <div class="col-12 col-sm-6">
                                <div class="p-2 border rounded bg-body-tertiary fs-8">
                                    <strong class="text-dark d-block"><?= htmlspecialchars($m['medicine_name']) ?></strong>
                                    <span class="text-muted">Type: <code><?= htmlspecialchars($m['medicine_type']) ?></code> | Dose: <?= htmlspecialchars($m['dose']) ?> | Schedule: <code><?= htmlspecialchars($m['schedule']) ?></code></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function createRow() {
        return $(`
            <div class="medicine-row border p-2 rounded mb-2 bg-body-tertiary">
                <div class="row g-2 mb-2">
                    <div class="col-4">
                        <select name="med_type[]" class="form-select form-select-sm">
                            <option value="TAB">TAB</option>
                            <option value="CAP">CAP</option>
                            <option value="SYP">SYP</option>
                            <option value="INJ">INJ</option>
                            <option value="CRM">CRM</option>
                            <option value="DRP">DRP</option>
                        </select>
                    </div>
                    <div class="col-8">
                        <input type="text" name="med_name[]" class="form-control form-select-sm med-name-input" placeholder="Medicine Name" required>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-5">
                        <input type="text" name="med_dose[]" class="form-control form-select-sm" placeholder="Dose (e.g. 4)">
                    </div>
                    <div class="col-5">
                        <input type="text" name="med_schedule[]" class="form-control form-select-sm" placeholder="Schedule (1-0-1)">
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-danger btn-sm w-100 remove-row-btn"><i class="fa-solid fa-trash fs-8"></i></button>
                    </div>
                </div>
            </div>
        `);
    }

    $('#addMedRowBtn').on('click', function() {
        $('#medicineRowsContainer').append(createRow());
    });

    $(document).on('click', '.remove-row-btn', function() {
        if ($('.medicine-row').length > 1) {
            $(this).closest('.medicine-row').remove();
        } else {
            showToast('At least one medicine row is required', 'error');
        }
    });

    $('#diagnosisForm').on('submit', function(e) {
        e.preventDefault();
        $.post('api/ajax.php?action=save_diagnosis', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                showToast(res.message);
                location.reload();
            } else {
                showToast(res.message, 'error');
            }
        }, 'json');
    });

    $('.btn-delete-diag').on('click', function() {
        const id = $(this).data('id');
        confirmDelete('Delete Diagnosis?', 'Are you sure you want to delete this diagnosis template?', function() {
            $.post('api/ajax.php?action=delete_diagnosis', { id: id }, function(res) {
                if (res.status === 'success') {
                    showToast(res.message);
                    location.reload();
                } else {
                    showToast(res.message, 'error');
                }
            }, 'json');
        });
    });

    $('#diseaseSearchInput').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('.disease-card-item').each(function() {
            const txt = $(this).find('.disease-title-text').text().toLowerCase();
            $(this).toggle(txt.indexOf(val) > -1);
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
