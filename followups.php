<?php
// followups.php
$page_title = "Follow-up Management";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$today = date('Y-m-d');

// Fetch follow-ups
$stmt = $pdo->prepare("
    SELECT f.*, p.name as patient_name, p.contact, p.sex, p.age,
           t.treatment_date, d.disease
    FROM followups f
    JOIN patients p ON f.patient_id = p.id
    JOIN treatments t ON f.treatment_id = t.id
    LEFT JOIN diagnoses d ON t.diagnosis_id = d.id
    ORDER BY f.followup_date ASC
");
$stmt->execute();
$followups = $stmt->fetchAll();

$settings = getClinicSettings();
?>

<div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-notes-medical text-primary me-2"></i>Patient Follow-up Management</h1>
        <p class="page-subtitle">Track today's, upcoming, and overdue patient consultation follow-ups.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-warning text-dark fs-7 px-3 py-2">
            <i class="fa-solid fa-clock me-1"></i> Today/Overdue Pending:
            <?= count(array_filter($followups, fn($f) => $f['followup_date'] <= $today && $f['status'] === 'Pending')) ?>
        </span>
    </div>
</div>

<div class="card-custom p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" id="followupSearchInput" class="form-control" placeholder="Search follow-up list...">
        </div>
        <div class="btn-group btn-group-sm" id="followupFilterBtns">
            <button class="btn btn-outline-primary active" data-filter="all">All</button>
            <button class="btn btn-outline-primary" data-filter="today">Today / Pending</button>
            <button class="btn btn-outline-primary" data-filter="completed">Completed</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom align-middle" id="followupsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Contact</th>
                    <th>Disease / Diagnosis</th>
                    <th>Follow-up Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($followups)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No follow-ups recorded yet.</td></tr>
                <?php else: ?>
                    <?php $sr = 1; foreach ($followups as $f):
                        $isTodayOrPast = ($f['followup_date'] <= $today);
                        $isPending = ($f['status'] === 'Pending');

                        // Fetch medicines for WhatsApp reminder
                        $mStmt = $pdo->prepare("SELECT * FROM treatment_medicines WHERE treatment_id = ?");
                        $mStmt->execute([$f['treatment_id']]);
                        $meds = $mStmt->fetchAll();

                        $waRem = "🏥 *" . $settings['clinic_name'] . "*\n\nHello *" . $f['patient_name'] . "*,\nThis is a friendly reminder for your medical follow-up consultation scheduled on *" . formatDate($f['followup_date']) . "* with *" . $settings['doctor_name'] . "*.\n\nPhone: " . $settings['contact_no'];
                        $waUrl = buildWhatsAppUrl($f['contact'], $waRem);
                    ?>
                    <tr class="followup-row" data-status="<?= strtolower($f['status']) ?>" data-is-due="<?= ($isTodayOrPast && $isPending) ? '1' : '0' ?>">
                        <td class="fw-bold text-muted"><?= $sr++ ?></td>
                        <td>
                            <strong class="text-primary fs-6"><?= htmlspecialchars($f['patient_name']) ?></strong>
                            <small class="text-muted d-block"><?= htmlspecialchars($f['sex']) ?>, <?= htmlspecialchars($f['age'] ?? 'N/A') ?> yrs</small>
                        </td>
                        <td><i class="fa-solid fa-phone me-1 text-muted fs-9"></i><?= htmlspecialchars($f['contact']) ?></td>
                        <td>
                            <span class="badge bg-info-subtle text-info fw-semibold">
                                <?= htmlspecialchars($f['disease'] ?: 'General Consult') ?>
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold <?= ($isTodayOrPast && $isPending) ? 'text-danger' : 'text-primary' ?>">
                                <?= formatDate($f['followup_date']) ?>
                            </span>
                            <?php if ($f['followup_date'] === $today && $isPending): ?>
                                <span class="badge bg-danger ms-1">DUE TODAY</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($f['status'] === 'Completed'): ?>
                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check me-1"></i> Completed</span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <?php if ($f['status'] === 'Pending'): ?>
                                    <button type="button" class="btn btn-outline-success btn-mark-complete" data-id="<?= $f['id'] ?>" title="Mark as Completed"><i class="fa-solid fa-check"></i></button>
                                <?php endif; ?>
                                <a href="<?= $waUrl ?>" target="_blank" class="btn btn-outline-success" title="Send WhatsApp Reminder"><i class="fa-brands fa-whatsapp"></i></a>
                                <a href="prescription.php?treatment_id=<?= $f['treatment_id'] ?>" class="btn btn-outline-primary" title="View Prescription"><i class="fa-solid fa-file-prescription"></i></a>
                                <button type="button" class="btn btn-outline-danger btn-delete-f" data-id="<?= $f['id'] ?>" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
    $('#followupSearchInput').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $("#followupsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    $('#followupFilterBtns button').on('click', function() {
        $('#followupFilterBtns button').removeClass('active');
        $(this).addClass('active');
        const filter = $(this).data('filter');

        $('.followup-row').each(function() {
            if (filter === 'all') {
                $(this).show();
            } else if (filter === 'today') {
                $(this).toggle($(this).data('is-due') == '1');
            } else if (filter === 'completed') {
                $(this).toggle($(this).data('status') === 'completed');
            }
        });
    });

    $('.btn-mark-complete').on('click', function() {
        const id = $(this).data('id');
        $.post('api/ajax.php?action=update_followup_status', { id: id, status: 'Completed' }, function(res) {
            if (res.status === 'success') {
                showToast(res.message);
                location.reload();
            }
        }, 'json');
    });

    $('.btn-delete-f').on('click', function() {
        const id = $(this).data('id');
        confirmDelete('Delete Follow-up?', 'Are you sure you want to delete this follow-up entry?', function() {
            $.post('api/ajax.php?action=delete_followup', { id: id }, function(res) {
                if (res.status === 'success') {
                    showToast(res.message);
                    location.reload();
                }
            }, 'json');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
