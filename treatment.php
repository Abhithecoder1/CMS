<?php
// treatment.php
$page_title = "Clinical Treatment";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$patientId = (int)($_GET['patient_id'] ?? 0);
$selectedPatient = null;

if ($patientId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $selectedPatient = $stmt->fetch();
}

$settings = getClinicSettings();
?>

<div class="page-header">
    <h1 class="page-title"><i class="fa-solid fa-stethoscope text-primary me-2"></i>Patient Clinical Treatment Workflow</h1>
    <p class="page-subtitle">Select a patient, record diagnosis, prescribe medicines, and specify follow-up details.</p>
</div>

<form id="treatmentForm">
    <input type="hidden" name="patient_id" id="treatmentPatientId" value="<?= $selectedPatient['id'] ?? '' ?>">

    <!-- Patient Selection Section -->
    <div class="card-custom p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-user-check text-primary me-2"></i>1. Patient Details</h5>

        <div class="position-relative mb-3">
            <label class="form-label fw-semibold">Search Patient (Name or Contact)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="treatPatientSearch" class="form-control" placeholder="Type name or phone number..." autocomplete="off" value="<?= $selectedPatient ? htmlspecialchars($selectedPatient['name'] . ' (' . $selectedPatient['contact'] . ')') : '' ?>">
            </div>
            <div id="treatPatientSuggestions" class="autocomplete-suggestions d-none"></div>
        </div>

        <div id="selectedPatientBanner" class="p-3 border rounded bg-body-tertiary <?= $selectedPatient ? '' : 'd-none' ?>">
            <div class="row g-2">
                <div class="col-12 col-md-3">
                    <small class="text-muted d-block fs-8">Name</small>
                    <strong id="dispTreatName" class="text-primary fs-6"><?= $selectedPatient['name'] ?? '' ?></strong>
                </div>
                <div class="col-6 col-md-2">
                    <small class="text-muted d-block fs-8">Sex / Age</small>
                    <span id="dispTreatSexAge"><?= $selectedPatient ? htmlspecialchars($selectedPatient['sex'] . ' / ' . ($selectedPatient['age'] ?? 'N/A') . ' yrs') : '' ?></span>
                </div>
                <div class="col-6 col-md-2">
                    <small class="text-muted d-block fs-8">Weight</small>
                    <span id="dispTreatWeight"><?= $selectedPatient ? htmlspecialchars(($selectedPatient['weight'] ?? 'N/A') . ' kg') : '' ?></span>
                </div>
                <div class="col-6 col-md-2">
                    <small class="text-muted d-block fs-8">Contact</small>
                    <span id="dispTreatContact"><?= $selectedPatient['contact'] ?? '' ?></span>
                </div>
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block fs-8">Allergies</small>
                    <span id="dispTreatAllergies" class="text-danger fw-semibold"><?= $selectedPatient['allergies'] ?? 'None' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatment Information Section -->
    <div class="card-custom p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-notes-medical text-primary me-2"></i>2. Diagnosis & Settings</h5>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 position-relative">
                <label class="form-label fw-semibold">Diagnosis / Disease</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-virus"></i></span>
                    <input type="text" name="disease_name" id="diseaseAutocomplete" class="form-control" placeholder="Select or type diagnosis..." autocomplete="off">
                </div>
                <div id="diseaseSuggestions" class="autocomplete-suggestions d-none"></div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold">Treatment Date</label>
                <input type="date" name="treatment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold">Treatment Time</label>
                <input type="time" name="treatment_time" class="form-control" value="<?= date('H:i') ?>" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Doctor Details</label>
                <input type="text" name="doctor_details" class="form-control" value="<?= htmlspecialchars($settings['doctor_name'] . ' (' . $settings['qualification'] . ')') ?>">
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Medical Store Details</label>
                <input type="text" name="medical_store_details" class="form-control" value="<?= htmlspecialchars($settings['medical_store_details']) ?>">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6 col-md-4">
                <label class="form-label fw-semibold">DT (Dispensary Treatment)</label>
                <select name="dt" class="form-select">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="col-6 col-md-4">
                <label class="form-label fw-semibold">Treatment Fee (₹)</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" min="0" name="fee" class="form-control" placeholder="300.00" value="300.00">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Follow-up Date</label>
                <input type="date" name="followup_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
            </div>
        </div>
    </div>

    <!-- Medicines Section -->
    <div class="card-custom p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-capsules text-primary me-2"></i>3. Medicines Prescribed</h5>
            <button type="button" id="addTreatmentMedBtn" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Add Medicine Row</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="treatmentMedsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Type</th>
                        <th>Medicine Name</th>
                        <th style="width: 120px;">Dose</th>
                        <th style="width: 150px;">Schedule</th>
                        <th style="width: 70px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="treatmentMedsBody">
                    <!-- Default 1 empty row -->
                    <tr>
                        <td>
                            <select name="med_type[]" class="form-select form-select-sm">
                                <option value="TAB">TAB</option>
                                <option value="CAP">CAP</option>
                                <option value="SYP">SYP</option>
                                <option value="INJ">INJ</option>
                                <option value="CRM">CRM</option>
                                <option value="DRP">DRP</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="med_name[]" class="form-control form-select-sm med-autocomplete" placeholder="Type medicine name..." required>
                        </td>
                        <td>
                            <input type="text" name="med_dose[]" class="form-control form-select-sm" placeholder="e.g. 4">
                        </td>
                        <td>
                            <input type="text" name="med_schedule[]" class="form-control form-select-sm" placeholder="e.g. 1-0-1">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-tmed-row"><i class="fa-solid fa-trash fs-9"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Submit Section -->
    <div class="d-flex justify-content-end gap-3 mb-5">
        <a href="index.php" class="btn btn-light px-4">Cancel</a>
        <button type="submit" id="submitTreatmentBtn" class="btn btn-success btn-lg px-5 fw-bold shadow-sm">
            <i class="fa-solid fa-paper-plane me-2"></i> Submit Treatment
        </button>
    </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Patient Autocomplete
    const pSearch = $('#treatPatientSearch');
    const pSugg = $('#treatPatientSuggestions');

    pSearch.on('input', function() {
        const q = $(this).val().trim();
        if (q.length < 1) {
            pSugg.addClass('d-none').empty();
            return;
        }
        $.getJSON('api/ajax.php?action=search_patients_autocomplete&q=' + encodeURIComponent(q), function(data) {
            pSugg.empty();
            if (data.length === 0) {
                pSugg.append('<div class="autocomplete-item text-muted">No patient found</div>');
            } else {
                data.forEach(p => {
                    const item = $(`<div class="autocomplete-item">
                        <strong>${p.name}</strong> (${p.sex}, ${p.age || 'N/A'} yrs) - <small class="text-muted">${p.contact}</small>
                    </div>`);
                    item.on('click', function() {
                        $('#treatmentPatientId').val(p.id);
                        pSearch.val(p.name + ' (' + p.contact + ')');
                        $('#dispTreatName').text(p.name);
                        $('#dispTreatSexAge').text(p.sex + ' / ' + (p.age || 'N/A') + ' yrs');
                        $('#dispTreatWeight').text((p.weight || 'N/A') + ' kg');
                        $('#dispTreatContact').text(p.contact);
                        $('#dispTreatAllergies').text(p.allergies || 'None');
                        $('#selectedPatientBanner').removeClass('d-none');
                        pSugg.addClass('d-none');
                    });
                    pSugg.append(item);
                });
            }
            pSugg.removeClass('d-none');
        });
    });

    // 2. Diagnosis Autocomplete
    const dSearch = $('#diseaseAutocomplete');
    const dSugg = $('#diseaseSuggestions');

    dSearch.on('input', function() {
        const q = $(this).val().trim();
        if (q.length < 1) {
            dSugg.addClass('d-none').empty();
            return;
        }
        $.getJSON('api/ajax.php?action=search_diagnoses_autocomplete&q=' + encodeURIComponent(q), function(data) {
            dSugg.empty();
            data.forEach(d => {
                const item = $(`<div class="autocomplete-item"><strong>${d.disease}</strong></div>`);
                item.on('click', function() {
                    dSearch.val(d.disease);
                    dSugg.addClass('d-none');
                    // Fetch medicines associated with this diagnosis
                    $.getJSON('api/ajax.php?action=get_diagnosis_medicines&diagnosis_id=' + d.id, function(meds) {
                        if (meds && meds.length > 0) {
                            $('#treatmentMedsBody').empty();
                            meds.forEach(m => {
                                addMedRow(m.medicine_type, m.medicine_name, m.dose, m.schedule);
                            });
                        }
                    });
                });
                dSugg.append(item);
            });
            dSugg.removeClass('d-none');
        });
    });

    // 3. Medicine Rows Dynamic Addition
    function addMedRow(type = 'TAB', name = '', dose = '', schedule = '') {
        const row = $(`
            <tr>
                <td>
                    <select name="med_type[]" class="form-select form-select-sm">
                        <option value="TAB" ${type==='TAB'?'selected':''}>TAB</option>
                        <option value="CAP" ${type==='CAP'?'selected':''}>CAP</option>
                        <option value="SYP" ${type==='SYP'?'selected':''}>SYP</option>
                        <option value="INJ" ${type==='INJ'?'selected':''}>INJ</option>
                        <option value="CRM" ${type==='CRM'?'selected':''}>CRM</option>
                        <option value="DRP" ${type==='DRP'?'selected':''}>DRP</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="med_name[]" class="form-control form-select-sm med-autocomplete" value="${name}" placeholder="Type medicine name..." required>
                </td>
                <td>
                    <input type="text" name="med_dose[]" class="form-control form-select-sm" value="${dose}" placeholder="e.g. 4">
                </td>
                <td>
                    <input type="text" name="med_schedule[]" class="form-control form-select-sm" value="${schedule}" placeholder="e.g. 1-0-1">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-tmed-row"><i class="fa-solid fa-trash fs-9"></i></button>
                </td>
            </tr>
        `);
        $('#treatmentMedsBody').append(row);
    }

    $('#addTreatmentMedBtn').on('click', function() {
        addMedRow();
    });

    $(document).on('click', '.remove-tmed-row', function() {
        if ($('#treatmentMedsBody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            showToast('At least one medicine is required', 'error');
        }
    });

    // 4. Form Submission
    $('#treatmentForm').on('submit', function(e) {
        e.preventDefault();
        const pId = $('#treatmentPatientId').val();
        if (!pId || pId <= 0) {
            showToast('Please search and select a patient first!', 'error');
            return;
        }

        const submitBtn = $('#submitTreatmentBtn');
        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving...');

        $.post('api/ajax.php?action=save_treatment', $(this).serialize(), function(res) {
            submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Submit Treatment');
            if (res.status === 'success') {
                showToast(res.message);
                Swal.fire({
                    title: 'Treatment Recorded Successfully!',
                    text: 'Would you like to print/view the prescription or share via WhatsApp now?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'View Prescription',
                    cancelButtonText: 'WhatsApp Prescription'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'prescription.php?treatment_id=' + res.treatment_id;
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.open(res.whatsapp_url, '_blank');
                        window.location.href = 'index.php';
                    } else {
                        window.location.href = 'index.php';
                    }
                });
            } else {
                showToast(res.message, 'error');
            }
        }, 'json').fail(function() {
            submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Submit Treatment');
            showToast('Failed to save treatment.', 'error');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
