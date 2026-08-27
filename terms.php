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
                                Student Assistants are legally restricted to working a maximum of <strong>20 hours per week</strong> during regular instructional terms to ensure employment does not interfere with study hours or lecture attendance.
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
                                <li>Applicants must submit accurate and authentic information. Uploading falsified GWA records, altered CORs, or fake identity details will result in immediate system suspension and disciplinary referral.</li>
                                <li>Posting off-campus, unapproved, or fraudulent job listings is strictly prohibited.</li>
                            </ul>
                        </section>

                        <section>
                            <h4 class="fw-bold text-kld-green mb-2">6. Hiring Units & Department Supervisor Rules</h4>
                            <ul class="text-secondary">
                                <li>Authorized university departments must provide clear, accurate job descriptions and duties.</li>
                                <li>Supervisors cannot assign work exceeding the <strong>20-hour weekly limit</strong> or require duties outside official school policy and schedules.</li>
                            </ul>
                        </section>

                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">7. Application Status & Selection</h4>
                            <ul class="text-secondary">
                                <li>Submitting an <b>application does not guarantee placement</b>. Hiring units retain the authority to review candidates, conduct interviews, and select qualified applicants based on department needs.</li>
                                <li>The system reserves the right to automatically filter out applications that fail to meet basic eligibility standards.</li>
                            </ul>
                        </section>

                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">8. Service Availability & Policy Updates</h4>
                            <ul class="text-secondary">
                                <li>The university reserves the right to update system features, modify platform terms, or perform scheduled maintenance resulting in temporary downtime without prior notice. Continued use of the portal after updates constitutes agreement to the modified terms.</li>
                            </ul>
                        </section>

                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">9. Attendance, Daily Time Records (DTR) & Compensation</h4>
                            <div class="alert alert-warning border-0 p-3 rounded-3 mb-3">
                                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Immediate Termination:</strong>
                                Submitting false hours or logging shifts not worked constitutes fraud and will result in immediate termination.
                            </div>
                            
                            <ul class="text-secondary">
                                <li>Employees are required to maintain accurate attendance records and submit Daily Time Records (DTR) as per university policy.</li>
                                <li>Compensation will be processed according to the university's payroll schedule and applicable labor laws.</li>
                                <li>Allowances are disbursed on a semi-monthly or monthly schedule via the University Cashier Office or registered student bank/e-wallet accounts.</li>
                            </ul>
                        </section>

                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">10. Performance Evaluations & Work Standards</h4>
                            <ul class="text-secondary">
                            <li><i class="bi bi-mortarboard-fill"></i> Student Assistants are expected to maintain professional conduct, fulfill assigned duties, and respect office rules.</li> 
                            <li><i class="bi bi-briefcase-fill"></i> Hiring supervisors conduct periodic performance evaluations. Poor duty performance or unexcused absences may lead to contract cancellation.</li>
                            </ul>
                        </section>

                        <section class="border-top pt-4">
                            <h4 class="fw-bold text-kld-green mb-2">11. Examination & Academic Priority Safeguard</h4>
                            <p class="text-secondary mb-2">Academics remain the primary priority. Students are entitled to request temporary shift adjustments or leave during official midterm and final examination weeks without penalty, provided advance notice is given to their supervisor.</p>
                        </section>

                        <div class="p-3 bg-light rounded-2 border border-success border-opacity-25 shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-patch-check-fill text-kld-green fs-5 me-2"></i>
                            <strong class="text-dark">12. Certificate of Service & Recommendations</strong>
                            <span class="badge bg-success bg-opacity-10 text-success ms-auto small">Official Award</span>
                        </div>
                            <p class="mb-0 text-secondary small">
                                Upon successful completion of an assistantship term with satisfactory ratings, 
                                <strong class="text-dark">students will receive an official Certificate of Service</strong> 
                                    issued by the Student Affairs & Services Office (SASO).
                                </p>
                        </div>
                    

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
