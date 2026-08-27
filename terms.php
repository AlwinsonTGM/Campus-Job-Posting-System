<?php
/**
 * Campus Job Posting System - Terms of Service
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'Terms of Service';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Terms of Service</li>
                    </ol>
                </nav>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="stat-icon bg-warning text-dark fs-3">
                            <i class="bi bi-file-earmark-ruled"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold text-dark mb-1">Terms of Service & Campus Work Guidelines</h2>
                            <p class="text-muted small mb-0">
                                Institutional Rules Governing On-Campus Student Assistantships | Effective August 2026
                            </p>
                        </div>
                    </div>

                    <div class="terms-content d-flex flex-column gap-4">
                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">1. Acceptance of Terms</h4>
                            <p class="text-secondary">
                                By registering for, accessing, or submitting applications through the KLD Campus Job Posting System, you agree to comply with all rules, policies, and directives outlined in this agreement and the official Kolehiyo ng Lungsod ng Dasmariñas (KLD) Student Handbook.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">2. Student Assistant Eligibility Requirements</h4>
                            <p class="text-secondary mb-2">To qualify for campus employment, student applicants must satisfy the following criteria:</p>
                            <ul class="text-secondary">
                                <li>Must be a currently registered undergraduate student in any of the 8 KLD Academic Institutes carrying at least 12 academic units.</li>
                                <li>Must maintain a minimum General Weighted Average (GWA) of <strong>2.50 or better</strong> with no failing grades in the preceding semester.</li>
                                <li>Must have no active disciplinary offenses or pending cases before the KLD Disciplinary Board.</li>
                                <li>Must present a valid Study Load / Certificate of Registration (COR) during application.</li>
                            </ul>
                        </section>

                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">3. Work Hour Regulations (20-Hour Weekly Cap)</h4>
                            <div class="alert alert-warning border-0 p-3 rounded-3 mb-3">
                                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Strict Academic Safeguard:</strong>
                                Student Assistants are legally restricted to working a maximum of <b>20 hours per week</b> during regular instructional terms to ensure employment does not interfere with study hours or lecture attendance.
                            </div>
                            <p class="text-secondary">
                                Working hours may be expanded up to a maximum of 40 hours per week only during official semester breaks, subject to prior written approval from the Student Affairs & Services Office (SASO).
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">4. User Account Security & Responsibilities</h4>
                            <ul class="text-secondary">
                                <li>Accounts are non-transferable. Users are responsible for maintaining the confidentiality of their login credentials.</li>
                                <li>Allowing another person to access your dashboard or apply on your behalf is strictly prohibited.</li>
                            </ul>
                        </section>

                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">5. Code of Conduct & Integrity</h4>
                            <ul class="text-secondary">
                                <li>Applicants must submit accurate documents. Uploading fake, falsified, or tampered GWA records, CORs, or personal identity details will cause immediate disqualification and lead to disciplinary action.</li>
                                <li>Posting off-campus, unapproved, or fraudulent job listings is strictly prohibited.</li>
                            </ul>
                        </section>

                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">6. Hiring Units & Department Supervisor Rules</h4>
                            <ul class="text-secondary">
                                <li>Authorized university departments must provide clear, accurate job descriptions and duties.</li>
                                <li>Supervisors cannot assign work exceeding the <b>20-hour weekly limit</b> or require duties outside official school policy and schedules.</li>
                            </ul>
                        </section>

                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">7. Non-Disclosure & Data Sharing Policy</h4>
                            <p class="text-secondary small mb-0">
                                Under no circumstances will student resumes or contact records be sold, rented, or transferred to third-party commercial marketing firms. Access is restricted exclusively to authorized KLD department supervisors, Student Affairs & Services Office (SASO) administrators, and KLD accounting officers.
                            </p>
                        </section>
                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">8. Data Retention & Storage Security</h4>
                            <p class="text-secondary small mb-0">
                                Application submissions are retained for the duration of the current academic semester and archived in accordance with university document retention schedules. Secure authentication tokens and role-based access control prevent unauthorized viewing of sensitive student files.
                            </p>
                        </section>
                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">9. Rights of the Data Subject</h4>
                            <p class="text-secondary mb-2">Under Republic Act 10173, students and users are entitled to the following privacy rights:</p>
                            <ul class="text-secondary">
                                <li><b>Right to Access:</b> View all submitted applications and profile records at any time through the student dashboard.</li>
                                <li><b>Right to Reactify:</b> Update or correct outdated contact numbers, resume files, and availability matrices.</li>
                                <li><b>Right to Erasure / Withdraw:</b> Cancel active job applications or request account deactivation.</li>
                                <li><b>Right to File to Complaints:</b> Report unauthorized data access to the University Data Protection Officer.</li>
                            </ul>
                        </section>
                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">10. System Availability & Service Disclaimer</h4>
                            <p class="text-secondary small mb-0">
                            The university reserves the right to update system features, modify policies, or conduct maintenance resulting in temporary downtime without prior notice.
                            </p>
                        </section>
                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">11. Data Protection Officer (DPO) Contact</h4>
                            <p class="text-secondary mb-2">For inquiries, corrections, or concerns regarding your personal data rights:</p>
                            <p class="text-secondary mb-2">Kolehiyo ng Lungsod ng Dasmariñas - Data Protection Office (DPO)
                            <p class="text-secondary mb-2">KLD Administration Building, City of Dasmariñas, Cavite
                            <p class="text-secondary mb-2">Email: dataprivacy@kld.edu.ph | Direct Line: (046) 416-0000 loc 105
                        </section>
                        

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
