<?php
/**
 * Campus Job Posting System - Terms of Service
 * Archetype F: Prose (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Terms of Service & Campus Work Guidelines';

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <div class="prose">
                    <!-- Top Back Navigation Button -->
                    <div class="mb-3">
                        <a href="javascript:history.back()" class="btn-pill-outline d-inline-flex align-items-center gap-2" style="padding: 0.4rem 1.1rem; font-size: 0.9rem;">
                            <i class="bi bi-arrow-left"></i> Go Back
                        </a>
                    </div>

                    <!-- Page Head & Last Updated Line -->
                    <div class="mb-4 pb-3 border-bottom border-line">
                        <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle mb-2">
                            <i class="bi bi-file-earmark-ruled text-accent"></i> Campus Employment Guidelines
                        </span>
                        <h1 class="page-head-title">Terms of Service &amp; Campus Work Guidelines</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Effective Date:</strong> August 2026 &bull; Institutional Rules Governing On-Campus Student Assistantships
                        </p>
                    </div>

                    <!-- Policy Introduction -->
                    <p class="lead text-muted-custom">
                        These Terms of Service govern the access and usage of the KLD Campus Job Posting System by students, campus academic departments, administrative divisions, and accredited hiring units. By accessing the platform or submitting applications, all parties agree to comply with the institutional policies detailed below and the official Kolehiyo ng Lungsod ng Dasmariñas (KLD) Student Handbook.
                    </p>

                    <!-- Section 1: Acceptance of Terms -->
                    <h2 class="h4">1. Acceptance of Terms</h2>
                    <p>
                        By registering for, accessing, or submitting applications through the KLD Campus Job Posting System, you agree to comply with all rules, policies, and directives outlined in this agreement and the official Kolehiyo ng Lungsod ng Dasmariñas (KLD) Student Handbook.
                    </p>

                    <!-- Section 2: Student Assistant Eligibility Requirements -->
                    <h2 class="h4">2. Student Assistant Eligibility Requirements</h2>
                    <p>
                        To qualify for on-campus student assistantships, student applicants must satisfy the following institutional criteria:
                    </p>
                    <ul>
                        <li>Must be a currently registered undergraduate student in any of the 8 KLD Academic Institutes carrying at least 12 academic units.</li>
                        <li>Must maintain a minimum General Weighted Average (GWA) of <strong>2.50 or better</strong> with no failing grades in the preceding semester.</li>
                        <li>Must have no active disciplinary offenses or pending cases before the KLD Disciplinary Board.</li>
                        <li>Must present a valid Study Load / Certificate of Registration (COR) during application.</li>
                    </ul>

                    <!-- Section 3: Work Hour Regulations (20-Hour Weekly Cap) -->
                    <h2 class="h4">3. Work Hour Regulations (20-Hour Weekly Cap)</h2>
                    <div class="card-paper bg-cream p-3 mb-3 border border-line">
                        <div class="d-flex align-items-center gap-2 text-ink fw-bold small mb-1">
                            <i class="bi bi-exclamation-triangle-fill text-accent fs-5"></i>
                            <span>Strict Academic Safeguard Policy</span>
                        </div>
                        <p class="small text-muted-custom mb-0">
                            Student Assistants are legally restricted to working a maximum of <strong>20 hours per week</strong> during regular instructional terms to ensure employment does not interfere with study hours or lecture attendance.
                        </p>
                    </div>
                    <p>
                        Working hours may be expanded up to a maximum of <strong>40 hours per week</strong> only during official semester breaks, subject to prior written approval from the Student Affairs &amp; Services Office (SASO). (Learn more in our <a href="<?= $base_url ?>faqs.php?open=2#faq-2" class="text-ink fw-bold text-decoration-underline">20-Hour Work Regulations FAQ</a>).
                    </p>

                    <!-- Section 4: User Account Security & Responsibilities -->
                    <h2 class="h4">4. User Account Security &amp; Responsibilities</h2>
                    <ul>
                        <li>Accounts are non-transferable. Users are responsible for maintaining the confidentiality of their login credentials.</li>
                        <li>Allowing another person to access your dashboard or apply on your behalf is strictly prohibited.</li>
                    </ul>

                    <!-- Section 5: Hiring Units & Department Supervisor Rules -->
                    <h2 class="h4">5. Hiring Units &amp; Department Supervisor Rules</h2>
                    <ul>
                        <li>Authorized university departments must provide clear, accurate job descriptions and duties.</li>
                        <li>Supervisors cannot assign work exceeding the <strong>20-hour weekly limit</strong> or require duties outside official school policy and schedules.</li>
                    </ul>

                    <!-- Section 6: Application Status & Selection -->
                    <h2 class="h4">6. Application Status &amp; Selection</h2>
                    <ul>
                        <li>Submitting an <strong>application does not guarantee placement</strong>. Hiring units retain the authority to review candidates, conduct interviews, and select qualified applicants based on department needs.</li>
                        <li>The system reserves the right to automatically filter out applications that fail to meet basic eligibility standards.</li>
                    </ul>

                    <!-- Section 7: Service Availability & Policy Updates -->
                    <h2 class="h4">7. Service Availability &amp; Policy Updates</h2>
                    <p>
                        The university reserves the right to update system features, modify platform terms, or perform scheduled maintenance resulting in temporary downtime without prior notice. Continued use of the portal after updates constitutes agreement to the modified terms.
                    </p>

                    <!-- Section 8: Attendance, Daily Time Records (DTR) & Compensation -->
                    <h2 class="h4">8. Attendance, Daily Time Records (DTR) &amp; Compensation</h2>
                    <div class="card-paper bg-cream p-3 mb-3 border border-line">
                        <div class="d-flex align-items-center gap-2 text-danger fw-bold small mb-1">
                            <i class="bi bi-shield-x fs-5"></i>
                            <span>Immediate Termination Warning</span>
                        </div>
                        <p class="small text-muted-custom mb-0">
                            Submitting false hours or logging shifts not worked constitutes fraud and will result in immediate termination and disciplinary referral.
                        </p>
                    </div>
                    <ul>
                        <li>Employees are required to maintain accurate attendance records and submit Daily Time Records (DTR) as per university policy.</li>
                        <li>Compensation will be processed according to the university's payroll schedule and applicable labor laws.</li>
                        <li>Allowances are disbursed on a semi-monthly (every 15th and 30th) or monthly schedule via the University Cashier Office or registered student bank/e-wallet accounts.</li>
                    </ul>

                    <!-- Section 9: Performance Evaluations & Work Standards -->
                    <h2 class="h4">9. Performance Evaluations &amp; Work Standards</h2>
                    <ul>
                        <li>Student Assistants are expected to maintain professional conduct, fulfill assigned duties, and respect office rules.</li>
                        <li>Hiring supervisors conduct periodic performance evaluations. Poor duty performance or unexcused absences may lead to contract cancellation.</li>
                    </ul>

                    <!-- Section 10: Examination & Academic Priority Safeguard -->
                    <h2 class="h4">10. Examination &amp; Academic Priority Safeguard</h2>
                    <p>
                        Academics remain the primary priority. Students are entitled to request temporary shift adjustments or leave during official midterm and final examination weeks without penalty, provided advance notice is given to their supervisor.
                    </p>

                    <!-- Section 11: Certificate of Service & Recommendations -->
                    <div class="card-paper bg-cream p-3 my-4 border border-line">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-patch-check-fill text-accent fs-5"></i>
                            <strong class="text-ink">11. Certificate of Service &amp; Recommendations</strong>
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-dark-subtle text-dark-emphasis border-dark-subtle ms-auto small">Official Award</span>
                        </div>
                        <p class="small text-muted-custom mb-0">
                            Upon successful completion of an assistantship term with satisfactory ratings, students will receive an official <strong>Certificate of Service</strong> issued by the Student Affairs &amp; Services Office (SASO).
                        </p>
                    </div>

                    <!-- Return CTA -->
                    <div class="pt-4 mt-4 border-top border-line d-flex justify-content-center gap-3">
                        <a href="javascript:history.back()" class="btn-pill">
                            <i class="bi bi-arrow-left"></i> Go Back to Previous Page
                        </a>
                        <a href="index.php" class="btn-pill-outline">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
