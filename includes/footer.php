<?php
/**
 * Campus Job Posting System - Shared Footer Partial
 */
?>
<footer class="footer-academic mt-auto">
    <div class="container">
        <div class="row g-4 pb-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-mortarboard-fill text-warning fs-3"></i>
                    <h5 class="text-white fw-bold mb-0">Campus Job Posting System</h5>
                </div>
                <p class="text-secondary small">
                    An official campus-focused employment portal connecting enrolled students with approved university departments, administrative offices, laboratories, and peer tutoring opportunities.
                </p>
                <div class="d-flex gap-3 text-secondary">
                    <span class="badge bg-secondary-subtle text-dark border">Native PHP</span>
                    <span class="badge bg-secondary-subtle text-dark border">Bootstrap 5.3</span>
                    <span class="badge bg-secondary-subtle text-dark border">Vanilla JS</span>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white fw-semibold mb-3">Quick Links</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= $base_url ?>index.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Home</a></li>
                    <li><a href="<?= $base_url ?>student/jobs.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Browse Vacancies</a></li>
                    <li><a href="<?= $base_url ?>faqs.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Campus FAQs</a></li>
                    <li><a href="<?= $base_url ?>about-us.php"><i class="bi bi-chevron-right me-1 text-warning"></i> About Developers</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="text-white fw-semibold mb-3">Legal & Governance</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= $base_url ?>privacy.php"><i class="bi bi-shield-check me-1 text-warning"></i> Data Privacy Policy (RA 10173)</a></li>
                    <li><a href="<?= $base_url ?>terms.php"><i class="bi bi-file-earmark-ruled me-1 text-warning"></i> Terms of Service</a></li>
                    <li><a href="<?= $base_url ?>faqs.php#sa-guidelines"><i class="bi bi-info-circle me-1 text-warning"></i> 20-Hour Work Limit Rules</a></li>
                    <li><a href="<?= $base_url ?>about-us.php#team"><i class="bi bi-people me-1 text-warning"></i> 6-Member Dev Team</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-semibold mb-3">Campus Office Support</h6>
                <p class="text-secondary small mb-2">
                    <i class="bi bi-geo-alt-fill text-warning me-2"></i> Office of Student Affairs & Services<br>
                    Student Center Building, 2nd Floor
                </p>
                <p class="text-secondary small mb-2">
                    <i class="bi bi-envelope-fill text-warning me-2"></i> studentaffairs@university.edu.ph
                </p>
                <p class="text-secondary small mb-0">
                    <i class="bi bi-telephone-fill text-warning me-2"></i> (02) 8888-1234 loc 201
                </p>
            </div>
        </div>

        <div class="border-top border-secondary pt-3 mt-2 d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
            <div>
                &copy; <?= date('Y') ?> <strong>Campus Job Posting System</strong>. All rights reserved.
            </div>
            <div class="mt-2 mt-md-0">
                <span>BSIS 2nd Year Capstone Activity | COAL101 Web Systems & Tech</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Global Main JS -->
<script src="<?= $base_url ?>assets/js/main.js"></script>

<?php if (isset($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $script): ?>
        <script src="<?= $base_url ?><?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</div> <!-- End d-flex flex-column min-vh-100 -->
</body>
</html>
