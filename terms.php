<?php
/**
 * Campus Job Posting System - Terms of Service
 * Archetype F: Prose (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Terms of Service & Campus Work Guidelines';
$from_register = isset($_GET['from']) && $_GET['from'] === 'register';
$fallback_url = $from_register ? 'register.php' : 'index.php';

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-4 py-lg-5">
            <div class="container-paper">

                <?php if ($from_register): ?>
                <!-- Contextual Registration Notice -->
                <div class="card-paper bg-cream p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border border-line">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle icon-circle-dark flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-check-fill text-accent"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-ink small">Account Registration in Progress</div>
                            <div class="text-muted-custom small">Review the campus employment guidelines below. When ready, return to complete your account submission.</div>
                        </div>
                    </div>
                    <button type="button" onclick="smartGoBack('register.php')" class="btn-pill btn-sm d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.85rem;">
                        <i class="bi bi-arrow-left"></i> Return to Registration
                    </button>
                </div>
                <?php endif; ?>

                <!-- Page Header with Smart Return Action -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-line">
                    <div>
                        <h1 class="page-head-title mb-1">Terms of Service &amp; Campus Work Guidelines</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Effective Date:</strong> August 2026 &bull; Institutional Rules Governing On-Campus Student Assistantships
                        </p>
                    </div>
                    <div>
                        <button type="button" onclick="smartGoBack('<?= $fallback_url ?>')" class="btn-pill-outline d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.9rem;">
                            <i class="bi bi-arrow-left"></i> <?= $from_register ? 'Return to Registration' : 'Go Back' ?>
                        </button>
                    </div>
                </div>

                <!-- Two-Column Layout Utilizing Workspace Free Space -->
                <div class="row g-4 g-xl-5">

                    <!-- Left Column: Comprehensive Legal Guidelines -->
                    <div class="col-lg-8">
                        <div class="prose prose-wide">

                            <!-- Policy Introduction -->
                            <p class="lead text-muted-custom">
                                These Terms of Service govern the access and usage of the KLD Campus Job Posting System by students, campus academic departments, administrative divisions, and accredited hiring units. By accessing the platform or submitting applications, all parties agree to comply with the institutional policies detailed below and the official Kolehiyo ng Lungsod ng Dasmariñas (KLD) Student Handbook.
                            </p>

                            <!-- Section 1: Acceptance of Terms -->
                            <div id="sec-1" class="pt-2">
                                <h2 class="h4">1. Acceptance of Terms</h2>
                                <p>
                                    By registering for, accessing, or submitting applications through the KLD Campus Job Posting System, you agree to comply with all rules, policies, and directives outlined in this agreement and the official Kolehiyo ng Lungsod ng Dasmariñas (KLD) Student Handbook.
                                </p>
                            </div>

                            <!-- Section 2: Student Assistant Eligibility Requirements -->
                            <div id="sec-2" class="pt-3">
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
                            </div>

                            <!-- Section 3: Work Hour Regulations (20-Hour Weekly Cap) -->
                            <div id="sec-3" class="pt-3">
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
                            </div>

                            <!-- Section 4: User Account Security & Responsibilities -->
                            <div id="sec-4" class="pt-3">
                                <h2 class="h4">4. User Account Security &amp; Responsibilities</h2>
                                <ul>
                                    <li>Accounts are non-transferable. Users are responsible for maintaining the confidentiality of their login credentials.</li>
                                    <li>Allowing another person to access your dashboard or apply on your behalf is strictly prohibited.</li>
                                </ul>
                            </div>

                            <!-- Section 5: Hiring Units & Department Supervisor Rules -->
                            <div id="sec-5" class="pt-3">
                                <h2 class="h4">5. Hiring Units &amp; Department Supervisor Rules</h2>
                                <ul>
                                    <li>Authorized university departments must provide clear, accurate job descriptions and duties.</li>
                                    <li>Supervisors cannot assign work exceeding the <strong>20-hour weekly limit</strong> or require duties outside official school policy and schedules.</li>
                                </ul>
                            </div>

                            <!-- Section 6: Application Status & Selection -->
                            <div id="sec-6" class="pt-3">
                                <h2 class="h4">6. Application Status &amp; Selection</h2>
                                <ul>
                                    <li>Submitting an <strong>application does not guarantee placement</strong>. Hiring units retain the authority to review candidates, conduct interviews, and select qualified applicants based on department needs.</li>
                                    <li>The system reserves the right to automatically filter out applications that fail to meet basic eligibility standards.</li>
                                </ul>
                            </div>

                            <!-- Section 7: Service Availability & Policy Updates -->
                            <div id="sec-7" class="pt-3">
                                <h2 class="h4">7. Service Availability &amp; Policy Updates</h2>
                                <p>
                                    The university reserves the right to update system features, modify platform terms, or perform scheduled maintenance resulting in temporary downtime without prior notice. Continued use of the portal after updates constitutes agreement to the modified terms.
                                </p>
                            </div>

                            <!-- Section 8: Attendance, Daily Time Records (DTR) & Compensation -->
                            <div id="sec-8" class="pt-3">
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
                            </div>

                            <!-- Section 9: Performance Evaluations & Work Standards -->
                            <div id="sec-9" class="pt-3">
                                <h2 class="h4">9. Performance Evaluations &amp; Work Standards</h2>
                                <ul>
                                    <li>Student Assistants are expected to maintain professional conduct, fulfill assigned duties, and respect office rules.</li>
                                    <li>Hiring supervisors conduct periodic performance evaluations. Poor duty performance or unexcused absences may lead to contract cancellation.</li>
                                </ul>
                            </div>

                            <!-- Section 10: Examination & Academic Priority Safeguard -->
                            <div id="sec-10" class="pt-3">
                                <h2 class="h4">10. Examination &amp; Academic Priority Safeguard</h2>
                                <p>
                                    Academics remain the primary priority. Students are entitled to request temporary shift adjustments or leave during official midterm and final examination weeks without penalty, provided advance notice is given to their supervisor.
                                </p>
                            </div>

                            <!-- Section 11: Certificate of Service & Recommendations -->
                            <div id="sec-11" class="pt-3">
                                <div class="card-paper bg-cream p-4 my-3 border border-line">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-patch-check-fill text-accent fs-5"></i>
                                            <strong class="text-ink">11. Certificate of Service &amp; Recommendations</strong>
                                        </div>
                                        <span class="small text-muted-custom"><i class="bi bi-award text-accent me-1"></i>Official SASO Credential</span>
                                    </div>
                                    <p class="small text-muted-custom mb-0">
                                        Upon successful completion of an assistantship term with satisfactory ratings, students will receive an official <strong>Certificate of Service</strong> issued by the Student Affairs &amp; Services Office (SASO).
                                    </p>
                                </div>
                            </div>

                            <!-- Bottom Return Action -->
                            <div class="pt-4 mt-4 border-top border-line d-flex flex-wrap justify-content-center gap-3">
                                <button type="button" onclick="smartGoBack('<?= $fallback_url ?>')" class="btn-pill d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-arrow-left"></i> <?= $from_register ? 'Return to Registration' : 'Go Back to Previous Page' ?>
                                </button>
                                <a href="index.php" class="btn-pill-outline d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-house"></i> Home
                                </a>
                            </div>

                        </div>
                    </div>

                    <!-- Right Column: Sticky Sidebar & Information Hub -->
                    <div class="col-lg-4">
                        <div class="d-flex flex-column gap-4" style="position: sticky; top: 96px;">

                            <!-- Table of Contents Card -->
                            <div class="card-paper p-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-line">
                                    <i class="bi bi-list-nested text-accent fs-5"></i>
                                    <h5 class="card-paper-title mb-0" style="font-size: 1.05rem;">Table of Contents</h5>
                                </div>
                                <nav class="toc-nav">
                                    <a href="#sec-1" class="toc-link"><i class="bi bi-chevron-right"></i> 1. Acceptance of Terms</a>
                                    <a href="#sec-2" class="toc-link"><i class="bi bi-chevron-right"></i> 2. Eligibility Criteria</a>
                                    <a href="#sec-3" class="toc-link"><i class="bi bi-chevron-right"></i> 3. 20-Hour Weekly Cap</a>
                                    <a href="#sec-4" class="toc-link"><i class="bi bi-chevron-right"></i> 4. Account Security</a>
                                    <a href="#sec-5" class="toc-link"><i class="bi bi-chevron-right"></i> 5. Supervisor Rules</a>
                                    <a href="#sec-6" class="toc-link"><i class="bi bi-chevron-right"></i> 6. Application Status</a>
                                    <a href="#sec-7" class="toc-link"><i class="bi bi-chevron-right"></i> 7. Service Availability</a>
                                    <a href="#sec-8" class="toc-link"><i class="bi bi-chevron-right"></i> 8. DTR &amp; Compensation</a>
                                    <a href="#sec-9" class="toc-link"><i class="bi bi-chevron-right"></i> 9. Work Standards</a>
                                    <a href="#sec-10" class="toc-link"><i class="bi bi-chevron-right"></i> 10. Exam Safeguard</a>
                                    <a href="#sec-11" class="toc-link"><i class="bi bi-chevron-right"></i> 11. Certificate of Service</a>
                                </nav>
                            </div>

                            <!-- Key Highlights Card -->
                            <div class="card-paper bg-cream p-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-line">
                                    <i class="bi bi-check2-circle text-accent fs-5"></i>
                                    <h5 class="card-paper-title mb-0" style="font-size: 1.05rem;">Policy Highlights</h5>
                                </div>
                                <div class="d-flex flex-column gap-3 small text-muted-custom">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-clock-history text-accent fs-6 mt-1"></i>
                                        <div><strong>20h Weekly Limit:</strong> Designed to protect your studies and lecture hours.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-mortarboard text-accent fs-6 mt-1"></i>
                                        <div><strong>Academic Standing:</strong> Minimum 2.50 GWA and zero failing grades required.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-cash-stack text-accent fs-6 mt-1"></i>
                                        <div><strong>Regular Stipends:</strong> Disbursed semi-monthly through the university cashier.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-calendar-event text-accent fs-6 mt-1"></i>
                                        <div><strong>Exam Week Leave:</strong> Shift adjustments permitted during midterm and final exams.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Support & Help Office Card -->
                            <div class="card-paper p-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-building text-accent fs-5"></i>
                                    <h5 class="card-paper-title mb-0" style="font-size: 1.05rem;">Student Services Help</h5>
                                </div>
                                <p class="small text-muted-custom mb-3">
                                    Have questions about assistantships, eligibility, or work hours? Reach out to SASO.
                                </p>
                                <div class="d-flex flex-column gap-2 small text-muted-custom">
                                    <div><i class="bi bi-geo-alt text-accent me-2"></i>Room 102, Student Affairs Building</div>
                                    <div><i class="bi bi-envelope text-accent me-2"></i>saso@kld.edu.ph</div>
                                    <div><i class="bi bi-question-circle text-accent me-2"></i><a href="<?= $base_url ?>faqs.php" class="text-ink fw-bold text-decoration-none">Read FAQs &amp; Help Hub</a></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
