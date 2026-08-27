<?php
/**
 * Campus Job Posting System - Terms of Service
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'Terms of Service';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 bg-surface flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Terms of Service</li>
                    </ol>
                </nav>

                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-line">
                        <div class="stat-icon bg-accent-soft text-ink fs-3">
                            <i class="bi bi-file-earmark-ruled"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold text-ink mb-1">Terms of Service & Campus Work Guidelines</h2>
                            <p class="text-muted-custom small mb-0">
                                Institutional Rules Governing On-Campus Student Assistantships | Effective August 2026
                            </p>
                        </div>
                    </div>

                    <div class="terms-content d-flex flex-column gap-4">
                        <section>
                            <h4 class="fw-bold text-ink mb-2">1. Acceptance of Terms</h4>
                            <p class="text-muted-custom">
                                By registering for, accessing, or submitting applications through the Campus Job Posting System, you agree to comply with all rules, policies, and directives outlined in this agreement and the official Student Handbook.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">2. Student Assistant Eligibility Requirements</h4>
                            <p class="text-muted-custom mb-2">To qualify for campus employment, student applicants must satisfy the following criteria:</p>
                            <ul class="text-muted-custom">
                                <li>Must be a currently registered undergraduate student carrying at least 12 academic units.</li>
                                <li>Must maintain a minimum General Weighted Average (GWA) of <strong>2.50 or better</strong> with no failing grades in the preceding semester.</li>
                                <li>Must have no active disciplinary offenses or pending cases before the Disciplinary Board.</li>
                                <li>Must present a valid Study Load / Certificate of Registration (COR) during application.</li>
                            </ul>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">3. Work Hour Regulations (20-Hour Weekly Cap)</h4>
                            <div class="alert alert-light border-line bg-cream p-3 rounded-3 mb-3 text-ink">
                                <strong><i class="bi bi-exclamation-triangle-fill text-accent me-1"></i> Strict Academic Safeguard:</strong>
                                Student Assistants are legally restricted to working a maximum of <strong>20 hours per week</strong> during regular instructional terms to ensure employment does not interfere with study hours or lecture attendance.
                            </div>
                            <p class="text-muted-custom">
                                Working hours may be expanded up to a maximum of 40 hours per week only during official semester breaks, subject to prior written approval from the Student Affairs Office.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">4. Campus Employer & Supervisor Obligations</h4>
                            <p class="text-muted-custom mb-2">Authorized institute deans and office heads agree to:</p>
                            <ol class="text-muted-custom">
                                <li>Provide clear, accurate, and ethical job descriptions and duties.</li>
                                <li>Adjust duty shifts flexibly during scheduled midterm and final examination weeks.</li>
                                <li>Submit signed monthly Daily Time Records (DTR) promptly to avoid stipend disbursement delays.</li>
                                <li>Foster a safe, harassment-free, and supportive professional learning environment for student assistants.</li>
                            </ol>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">5. Application Integrity & Code of Conduct</h4>
                            <p class="text-muted-custom">
                                Any falsification of academic grades, forgery of study load documents, or fraudulent logging of Daily Time Records ("ghost attendance") will result in immediate termination of the assistantship contract, forfeiture of unreleased stipends, and referral to the Disciplinary Committee.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">6. Contract Termination & Resignation</h4>
                            <p class="text-muted-custom">
                                A student assistant may resign by submitting a written 1-week notice to their department supervisor. Campus offices reserve the right to terminate an assignment due to persistent tardiness, unexcused absences, or breach of department confidentiality.
                            </p>
                        </section>

                        <section class="border-top border-line pt-4">
                            <h4 class="fw-bold text-ink mb-2">7. Inquiries & Oversight</h4>
                            <p class="text-muted-custom small mb-0">
                                This portal is administered by the <strong>Student Affairs & Career Services Office</strong>. For disputes or policy clarifications, visit Room 201, Main Academic Building or email <code>careers@campus-hire.edu</code>.
                            </p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
