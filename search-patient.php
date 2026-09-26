<?php
// search-patient.php
$page_title = "Search Patient";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>Search Patient</h1>
    <p class="page-subtitle">Search registered patients by Name or Contact Number with instant autocomplete.</p>
</div>

<!-- Search Input Card -->
<div class="card-custom p-4 mb-4">
    <div class="position-relative">
        <label class="form-label fw-bold">Search Patient</label>
        <div class="input-group input-group-lg">
            <span class="input-group-text"><i class="fa-solid fa-user-check text-muted"></i></span>
            <input type="text" id="patientSearchInput" class="form-control" placeholder="Start typing patient name or contact number..." autocomplete="off" autofocus>
            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="patientSuggestions" class="autocomplete-suggestions d-none"></div>
    </div>
</div>

<!-- Patient Details Display Card -->
<div id="patientDetailCard" class="card-custom p-4 d-none">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary fw-bold fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                <i class="fa-solid fa-user-injured"></i>
            </div>
            <div>
                <h3 id="dispName" class="fw-bold mb-1 text-primary"></h3>
                <span id="dispRegDate" class="badge bg-secondary-subtle text-secondary fs-8"></span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a id="btnAddTreatment" href="#" class="btn btn-success btn-sm"><i class="fa-solid fa-stethoscope me-1"></i> Add Treatment</a>
            <a id="btnViewHistory" href="#" class="btn btn-info btn-sm text-white"><i class="fa-solid fa-history me-1"></i> View History</a>
            <button id="btnEditPatient" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen-to-square me-1"></i> Edit</button>
            <button id="btnDeletePatient" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash me-1"></i> Delete</button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="p-3 bg-body-tertiary rounded">
                <small class="text-muted d-block text-uppercase fw-semibold fs-8">Sex / Age / Weight</small>
                <div class="fw-bold fs-6 mt-1">
                    <span id="dispSex"></span> / <span id="dispAge"></span> yrs / <span id="dispWeight"></span> kg
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-body-tertiary rounded">
                <small class="text-muted d-block text-uppercase fw-semibold fs-8">Contact Number</small>
                <div id="dispContact" class="fw-bold fs-6 mt-1"></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="p-3 bg-body-tertiary rounded">
                <small class="text-muted d-block text-uppercase fw-semibold fs-8">Total Fees Paid</small>
                <div id="dispPendingFees" class="fw-bold fs-6 mt-1 text-success"></div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-3 bg-body-tertiary rounded">
                <small class="text-muted d-block text-uppercase fw-semibold fs-8">Known Allergies</small>
                <div id="dispAllergies" class="fw-semibold text-danger mt-1"></div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-3 bg-body-tertiary rounded">
                <small class="text-muted d-block text-uppercase fw-semibold fs-8">Address</small>
                <div id="dispAddress" class="fw-semibold mt-1"></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2 text-warning"></i>Edit Patient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPatientForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Patient Name *</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Sex *</label>
                            <select name="sex" id="editSex" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Age</label>
                            <input type="number" name="age" id="editAge" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight" id="editWeight" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Contact *</label>
                            <input type="text" name="contact" id="editContact" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Allergies</label>
                        <input type="text" name="allergies" id="editAllergies" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = $('#patientSearchInput');
    const suggestionsBox = $('#patientSuggestions');
    let currentPatientId = null;

    searchInput.on('input', function() {
        const query = $(this).val().trim();
        if (query.length < 1) {
            suggestionsBox.addClass('d-none').empty();
            return;
        }

        $.getJSON('api/ajax.php?action=search_patients_autocomplete&q=' + encodeURIComponent(query), function(data) {
            suggestionsBox.empty();
            if (data.length === 0) {
                suggestionsBox.append('<div class="autocomplete-item text-muted">No matching patient found</div>');
            } else {
                data.forEach(p => {
                    const item = $(`
                        <div class="autocomplete-item">
                            <div class="fw-bold">${p.name} <small class="text-primary">(${p.sex}, ${p.age || 'N/A'} yrs)</small></div>
                            <small class="text-muted"><i class="fa-solid fa-phone me-1"></i>${p.contact} | ${p.address || 'No Address'}</small>
                        </div>
                    `);
                    item.on('click', function() {
                        selectPatient(p.id);
                        suggestionsBox.addClass('d-none');
                        searchInput.val(p.name);
                    });
                    suggestionsBox.append(item);
                });
            }
            suggestionsBox.removeClass('d-none');
        });
    });

    $('#clearSearchBtn').on('click', function() {
        searchInput.val('').focus();
        suggestionsBox.addClass('d-none');
        $('#patientDetailCard').addClass('d-none');
        currentPatientId = null;
    });

    function selectPatient(patientId) {
        currentPatientId = patientId;
        $.getJSON('api/ajax.php?action=get_patient_details&id=' + patientId, function(res) {
            if (res.status === 'success') {
                const p = res.data;
                $('#dispName').text(p.name);
                $('#dispRegDate').text('Registered: ' + (p.created_at ? p.created_at.split(' ')[0] : 'N/A'));
                $('#dispSex').text(p.sex);
                $('#dispAge').text(p.age || 'N/A');
                $('#dispWeight').text(p.weight || 'N/A');
                $('#dispContact').text(p.contact);
                $('#dispPendingFees').text('₹' + parseFloat(p.total_fees || 0).toFixed(2));
                $('#dispAllergies').text(p.allergies || 'None reported');
                $('#dispAddress').text(p.address || 'N/A');

                $('#btnAddTreatment').attr('href', 'treatment.php?patient_id=' + p.id);
                $('#btnViewHistory').attr('href', 'treatment-history.php?patient_id=' + p.id);

                // Populate edit form
                $('#editId').val(p.id);
                $('#editName').val(p.name);
                $('#editSex').val(p.sex);
                $('#editAge').val(p.age);
                $('#editWeight').val(p.weight);
                $('#editContact').val(p.contact);
                $('#editAllergies').val(p.allergies);
                $('#editAddress').val(p.address);

                $('#patientDetailCard').removeClass('d-none');
            }
        });
    }

    $('#btnEditPatient').on('click', function() {
        if (currentPatientId) {
            const modal = new bootstrap.Modal(document.getElementById('editPatientModal'));
            modal.show();
        }
    });

    $('#editPatientForm').on('submit', function(e) {
        e.preventDefault();
        $.post('api/ajax.php?action=save_patient', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                showToast(res.message);
                bootstrap.Modal.getInstance(document.getElementById('editPatientModal')).hide();
                selectPatient(currentPatientId);
            } else {
                showToast(res.message, 'error');
            }
        }, 'json');
    });

    $('#btnDeletePatient').on('click', function() {
        if (!currentPatientId) return;
        confirmDelete(
            'Delete Patient?',
            'Are you sure you want to delete this patient? This action will also affect associated treatment records.',
            function() {
                $.post('api/ajax.php?action=delete_patient', { id: currentPatientId }, function(res) {
                    if (res.status === 'success') {
                        showToast(res.message);
                        $('#patientDetailCard').addClass('d-none');
                        searchInput.val('');
                        currentPatientId = null;
                    } else {
                        showToast(res.message, 'error');
                    }
                }, 'json');
            }
        );
    });

    // Check if patient_id passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const pid = urlParams.get('patient_id');
    if (pid) {
        selectPatient(pid);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
