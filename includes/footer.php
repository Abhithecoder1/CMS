<?php
// includes/footer.php
?>
        </main>
        <footer class="mt-auto py-3 px-4 border-top text-center text-muted fs-8">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; <?= date('Y') ?> <strong><?= htmlspecialchars($settings['clinic_name']) ?></strong>. All rights reserved.
                </div>
                <div>
                    Doctor: <strong><?= htmlspecialchars($settings['doctor_name']) ?></strong> (<?= htmlspecialchars($settings['qualification']) ?>)
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SheetJS for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- html2pdf.js for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- Custom App JS -->
<script src="assets/js/app.js"></script>
</body>
</html>
