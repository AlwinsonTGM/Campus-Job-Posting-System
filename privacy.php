<?php
/**
 * Campus Job Posting System - Data Privacy Policy
 * Compliant with Philippine Republic Act No. 10173 (Data Privacy Act of 2012)
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'Data Privacy Policy';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 bg-surface flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Page Breadcrumb & Header -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Privacy Policy</li>
                    </ol>
                </nav>

                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-line">
                        <div class="stat-icon bg-accent-soft text-ink fs-3">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold text-ink mb-1">Data Privacy Policy</h2>
                            <p class="text-muted-custom small mb-0">
                                In Compliance with Republic Act No. 10173 (Philippine Data Privacy Act of 2012) | Last Updated: August 2026
                            </p>
                        </div>
                    </div>

                    <div class="alert alert-light border-line bg-cream p-3 rounded-3 mb-4 text-ink">
                        <i class="bi bi-info-circle-fill text-accent me-2"></i>
                        The <strong>Campus Job Posting System</strong> is committed to safeguarding personal, academic, and sensitive information submitted by students and campus department representatives.
                    </div>

                    <div class="privacy-content d-flex flex-column gap-4">
                        <section>
                            <h4 class="fw-bold text-ink mb-2">1. Scope and Applicability</h4>
                            <p class="text-muted-custom">
                                This policy governs all personal data collected through the online Campus Job Posting Portal from enrolled students seeking student assistantships, student tutors, and campus department personnel evaluating employment applications.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">2. Information We Collect</h4>
                            <p class="text-muted-custom mb-2">We collect only necessary information required to evaluate employment suitability:</p>
                            <ul class="text-muted-custom">
                                <li><strong>Personal Identity:</strong> Full name, institutional email address, contact phone numbers, and student identification numbers.</li>
                                <li><strong>Academic Credentials:</strong> Enrolled academic institute, degree program, academic year level, General Weighted Average (GWA), and official Certificate of Registration / Study Load.</li>
                                <li><strong>Application Files:</strong> Digital resumes, curriculum vitae, statements of interest/cover letters, and weekly class schedule vacant slots.</li>
                                <li><strong>Employer & Supervisor Data:</strong> Campus office department, official room location, and supervisor institutional contact details.</li>
                            </ul>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">3. Purpose of Data Processing</h4>
                            <p class="text-muted-custom mb-2">Your information is processed strictly for the following legitimate campus purposes:</p>
                            <ol class="text-muted-custom">
                                <li>Assessing applicant qualification against posted department requirements.</li>
                                <li>Scheduling technical interviews and coordinating duty shift timetables that do not conflict with lecture hours.</li>
                                <li>Transmitting verified Daily Time Records (DTR) and payroll documents to the University Cashier and Accounting Office.</li>
                                <li>Maintaining official university records of completed student assistantship certificates.</li>
                            </ol>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">4. Non-Disclosure & Data Sharing Policy</h4>
                            <p class="text-muted-custom">
                                Under no circumstances will student resumes or contact records be sold, rented, or transferred to third-party commercial marketing firms. Access is restricted exclusively to authorized campus department supervisors, Student Affairs administrators, and accounting officers.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">5. Data Retention & Storage Security</h4>
                            <p class="text-muted-custom">
                                Application submissions are retained for the duration of the current academic semester and archived in accordance with university document retention schedules. Secure authentication tokens and role-based access control prevent unauthorized viewing of sensitive student files.
                            </p>
                        </section>

                        <section>
                            <h4 class="fw-bold text-ink mb-2">6. Rights of the Data Subject</h4>
                            <p class="text-muted-custom mb-2">Under Republic Act 10173, students and users are entitled to the following privacy rights:</p>
                            <div class="row g-3 text-muted-custom small">
                                <div class="col-md-6">
                                    <div class="p-3 bg-surface rounded-3 border-line border">
                                        <strong class="text-ink"><i class="bi bi-eye-fill text-accent me-1"></i> Right to Access:</strong> View all submitted applications and profile records at any time through the student dashboard.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-surface rounded-3 border-line border">
                                        <strong class="text-ink"><i class="bi bi-pencil-fill text-ink me-1"></i> Right to Rectify:</strong> Update or correct outdated contact numbers, resume files, and availability matrices.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-surface rounded-3 border-line border">
                                        <strong class="text-ink"><i class="bi bi-trash-fill text-danger me-1"></i> Right to Erasure / Withdraw:</strong> Cancel active job applications or request account deactivation.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-surface rounded-3 border-line border">
                                        <strong class="text-ink"><i class="bi bi-envelope-exclamation-fill text-accent me-1"></i> Right to File Complaints:</strong> Report unauthorized data access to the University Data Protection Officer.
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="border-top border-line pt-4">
                            <h4 class="fw-bold text-ink mb-2">7. Data Protection Officer (DPO) Contact</h4>
                            <p class="text-muted-custom small mb-1">
                                For inquiries, corrections, or concerns regarding your personal data rights:
                            </p>
                            <div class="bg-surface p-3 rounded-3 border-line border text-muted-custom small">
                                <strong class="text-ink">Campus Job Posting Network - Data Protection Office (DPO)</strong><br>
                                University Administration Building, Room 201<br>
                                Email: <code>dataprivacy@campus-hire.edu</code> | Direct Line: (02) 8920-1000 loc 105
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
