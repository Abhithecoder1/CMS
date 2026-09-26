<?php
// add-patient.php
$page_title = "Add Patient";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add New Patient</h1>
    <p class="page-subtitle">Register a new patient into the clinic records system.</p>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card-custom p-4">
            <form id="addPatientForm" autocomplete="off">
                <input type="hidden" name="id" value="0">

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Patel" required autofocus>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">Sex <span class="text-danger">*</span></label>
                        <select name="sex" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">Age (Years)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-cake-candles text-muted"></i></span>
                            <input type="number" min="0" max="130" name="age" class="form-control" placeholder="e.g. 35">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">Weight (kg)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-weight-scale text-muted"></i></span>
                            <input type="number" step="0.1" min="0" max="300" name="weight" class="form-control" placeholder="e.g. 68.5">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold">Contact Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="text" name="contact" class="form-control" placeholder="e.g. +91 9876543210" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Known Allergies / Pre-existing Conditions</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-hand-dots text-muted"></i></span>
                        <input type="text" name="allergies" class="form-control" placeholder="e.g. Dust allergy, Penicillin, Asthmatic">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Address / City</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-location-dot text-muted"></i></span>
                        <textarea name="address" class="form-control" rows="2" placeholder="Patient's residential address"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <button type="reset" class="btn btn-light px-4"><i class="fa-solid fa-rotate-left me-1"></i> Reset Form</button>
                    <button type="submit" id="savePatientBtn" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#addPatientForm').on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#savePatientBtn');
        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

        $.post('api/ajax.php?action=save_patient', $(this).serialize(), function(res) {
            submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save Patient');
            if (res.status === 'success') {
                showToast(res.message, 'success');
                Swal.fire({
                    title: 'Patient Added Successfully!',
                    text: 'Would you like to add a treatment for this patient now?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Add Treatment',
                    cancelButtonText: 'Add Another Patient'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'treatment.php?patient_id=' + res.id;
                    } else {
                        $('#addPatientForm')[0].reset();
                    }
                });
            } else {
                showToast(res.message, 'error');
            }
        }, 'json').fail(function() {
            submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save Patient');
            showToast('Server communication error', 'error');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
