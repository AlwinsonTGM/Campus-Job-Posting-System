<?php
/**
 * Campus Job Posting System - Terms of Service & Campus Work Guidelines
 * Archetype F: Prose (COAL101 Blueprint)
 * Updated for Academic Year 2026–2027 (KLD SASO Regulatory Standards)
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
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom border-line">
                    <div>
                        <h1 class="page-head-title mb-1">Terms of Service &amp; Campus Work Guidelines</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Effective Date:</strong> September 26, 2026 &bull; Institutional Rules Governing On-Campus Student Assistantships &amp; Hiring Units
                        </p>
                    </div>
                    <div>
                        <button type="button" onclick="smartGoBack('<?= $fallback_url ?>')" class="btn-pill-outline d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.9rem;">
                            <i class="bi bi-arrow-left"></i> <?= $from_register ? 'Return to Registration' : 'Go Back' ?>
                        </button>
                    </div>
                </div>

                <!-- Structured Versioning & Regulatory Metadata Bar -->
                <div class="card-paper bg-cream p-3 mb-4 border border-line">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span class="badge bg-ink text-white px-2 py-1 small">
                                <i class="bi bi-file-earmark-check-fill text-accent me-1"></i> Policy Version 2.4
                            </span>
                            <span class="small text-muted-custom">
                                <i class="bi bi-calendar2-check text-accent me-1"></i><strong>Last Updated:</strong> September 26, 2026
                            </span>
                            <span class="small text-muted-custom">
                                <i class="bi bi-mortarboard-fill text-accent me-1"></i><strong>Academic Term:</strong> First Semester, A.Y. 2026–2027
                            </span>
                        </div>
                        <span class="small text-muted-custom">
                            <i class="bi bi-shield-check text-accent me-1"></i>KLD SASO &bull; Statutory &amp; Institutional Compliance
                        </span>
                    </div>
                </div>

                <!-- Two-Column Layout Utilizing Workspace Free Space -->
                <div class="row g-4 g-xl-5">

                    <!-- Left Column: Comprehensive Legal Guidelines -->
                    <div class="col-lg-8">
                        <div class="prose prose-wide">

                            <!-- Policy Introduction -->
                            <p class="lead text-muted-custom">
                                These Terms of Service and Campus Work Guidelines constitute a legally binding agreement governing the access, operation, and utilization of the <strong>KLD Campus Job Posting System (Campus Hire)</strong>. This agreement applies to all enrolled student applicants, student assistants (SAs), academic department heads, administrative division supervisors, and accredited campus partner employers of <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>. By registering an account, publishing requisitions, or submitting job applications, all users agree to adhere unconditionally to these guidelines, the official KLD Student Handbook, and applicable national labor and education statutes.
                            </p>

                            <!-- Section 1: Acceptance of Terms & Institutional Scope -->
                            <div id="sec-1" class="pt-2">
                                <h2 class="h4">1. Acceptance of Terms &amp; Institutional Scope</h2>
                                <p>
                                    By accessing, registering an account on, or submitting documentation through this portal, you confirm that you have read, understood, and agreed to be bound by these Terms of Service. These guidelines have been formulated under the authority of the KLD Office of the Vice President for Academic and Student Affairs (OVPASA) and the Student Affairs and Services Office (SASO).
                                </p>
                                <p>
                                    If you do not agree with any provision set forth in this document, you must immediately refrain from creating an account or using the job application and recruitment services provided by this platform.
                                </p>
                            </div>

                            <!-- Section 2: Student Assistant Eligibility & Academic Load Requirements -->
                            <div id="sec-2" class="pt-3">
                                <h2 class="h4">2. Student Assistant Eligibility &amp; Academic Load Requirements</h2>
                                <p>
                                    The Student Assistantship Program is designed as an experiential work-study grant rather than full-time commercial employment. To qualify for and maintain on-campus student assistantship status, an applicant must satisfy all of the following requirements:
                                </p>
                                <ul>
                                    <li><strong>Current Undergraduate Matriculation:</strong> Must be an officially enrolled undergraduate student in any of the accredited KLD Academic Institutes carrying a minimum active academic study load of <strong>12 units</strong> (or the prescribed curriculum load for graduating seniors).</li>
                                    <li><strong>Academic Standing &amp; GWA:</strong> Must maintain a cumulative and preceding-semester General Weighted Average (GWA) of <strong>2.50 or higher</strong>, with no unresolved failing grades (5.0), dropped subjects without cause, or unauthorized incomplete marks.</li>
                                    <li><strong>Disciplinary Record Clearance:</strong> Must be in good moral standing with no active disciplinary offenses, suspensions, or pending cases before the KLD Student Disciplinary Board or Office of Student Conduct.</li>
                                    <li><strong>Proof of Enrollment:</strong> Must present and upload a verified Certificate of Registration (COR) / Official Study Load for the active academic term upon application or profile registration.</li>
                                    <li><strong>Underload / Dropping Notification:</strong> If a student assistant drops below the 12-unit minimum during an active work semester, they must report the curriculum modification to SASO within five (5) working days for contract evaluation.</li>
                                </ul>
                            </div>

                            <!-- Section 3: Work Hour Regulations (20-Hour Weekly Cap) -->
                            <div id="sec-3" class="pt-3">
                                <h2 class="h4">3. Work Hour Regulations (20-Hour Weekly Cap)</h2>
                                <div class="card-paper bg-cream p-3 mb-3 border border-line">
                                    <div class="d-flex align-items-center gap-2 text-ink fw-bold small mb-1">
                                        <i class="bi bi-exclamation-triangle-fill text-accent fs-5"></i>
                                        <span>Strict Academic Priority &amp; Anti-Overwork Safeguard</span>
                                    </div>
                                    <p class="small text-muted-custom mb-0">
                                        Student Assistants are legally and institutionally restricted to working a maximum of <strong>20 hours per week</strong> during regular instructional terms. Employment shifts must never conflict with enrolled lecture blocks, laboratory sessions, or university academic requirements.
                                    </p>
                                </div>
                                <p>
                                    The 20-hour weekly cap is an immutable safeguard enforced to guarantee that student employment does not hinder academic performance, study hours, or physical wellbeing:
                                </p>
                                <ul>
                                    <li><strong>No Overtime During Class Weeks:</strong> Supervisors are strictly forbidden from assigning, approving, or coercing student assistants into rendering shifts in excess of 20 hours per week or during scheduled class hours.</li>
                                    <li><strong>Semester Break Exceptions:</strong> During official university semester breaks, mid-year summer periods, or holidays when regular classes are not in session, student assistants may render up to a maximum of <strong>40 hours per week</strong>, subject to prior written requisition and budget approval from SASO. (Read our <a href="<?= $base_url ?>faqs.php?open=2#faq-2" class="text-ink fw-bold text-decoration-underline">20-Hour Work Regulations FAQ</a>).</li>
                                    <li><strong>Automated Matrix Validation:</strong> The portal's interactive 18-slot Weekly Shift Matrix automatically checks and flags student availability against department vacancy schedules to prevent scheduling overlaps.</li>
                                </ul>
                            </div>

                            <!-- Section 4: User Account Security & Single-Account Policy -->
                            <div id="sec-4" class="pt-3">
                                <h2 class="h4">4. User Account Security &amp; Single-Account Policy</h2>
                                <p>
                                    All registered users are responsible for safeguarding the integrity and confidentiality of their platform credentials:
                                </p>
                                <ul>
                                    <li><strong>Single Institutional Identity:</strong> Each student and department representative is permitted to register and operate exactly one (1) account. Multi-account registration, duplicate profiles, or impersonation of campus officials will result in immediate permanent account termination.</li>
                                    <li><strong>Institutional Email Verification:</strong> Students and campus department heads must register using their official university email addresses (<code>@kld.edu.ph</code>). External partner employers must provide validated corporate emails accompanied by legal SEC/DTI documentation.</li>
                                    <li><strong>Non-Transferability:</strong> Accounts are non-transferable. Users are strictly prohibited from sharing login credentials, delegating account access, or permitting another individual to submit job applications on their behalf.</li>
                                    <li><strong>Security Breach Reporting:</strong> Users must promptly notify system administrators via <code>careers@kld.edu.ph</code> if they detect unauthorized access, suspicious session activity, or compromised passwords.</li>
                                </ul>
                            </div>

                            <!-- Section 5: Campus Employer & Department Supervisor Obligations -->
                            <div id="sec-5" class="pt-3">
                                <h2 class="h4">5. Campus Employer &amp; Department Supervisor Obligations</h2>
                                <p>
                                    University academic departments, administrative offices, and accredited external partners operating hiring portals agree to the following operational standards:
                                </p>
                                <ul>
                                    <li><strong>Accurate Requisitions:</strong> Vacancy listings must provide truthful, transparent descriptions of duties, required skillsets, physical workstations, work arrangements (On-Campus, Hybrid, or Remote), and hourly stipend rates.</li>
                                    <li><strong>Dignity of Student Labor:</strong> Assigned duties must be educational, administrative, technical, or research-oriented. Supervisors shall not assign personal chores, hazardous duties, errands outside campus grounds without SASO travel authorization, or tasks violating university safety codes.</li>
                                    <li><strong>Non-Discrimination:</strong> Candidate evaluation must be conducted solely on the basis of academic merit, relevant skills, schedule availability, and interview performance. Discrimination based on gender, sexual orientation, religion, disability, or socio-economic background is strictly prohibited.</li>
                                    <li><strong>Timely Candidate Review:</strong> Hiring units must evaluate submitted applications, conduct interviews, and record recruitment decisions within fourteen (14) calendar days of receiving submissions.</li>
                                </ul>
                            </div>

                            <!-- Section 6: Application Workflow, Selection Gate, & No-Guarantee Disclaimer -->
                            <div id="sec-6" class="pt-3">
                                <h2 class="h4">6. Application Workflow, Selection Gate, &amp; No-Guarantee Disclaimer</h2>
                                <ul>
                                    <li><strong>5-Stage Progressive Stepper:</strong> Applications advance through structured stages: <em>Pending Review &rarr; Under Evaluation &rarr; Interview Scheduled &rarr; Accepted / Declined / Withdrawn</em>.</li>
                                    <li><strong>No Guarantee of Placement:</strong> Submitting an application through this portal does not guarantee selection, interview invitation, or appointment. Hiring decisions remain within the sole discretion of the recruiting campus department based on departmental quotas and applicant qualifications.</li>
                                    <li><strong>Right of Withdrawal:</strong> Student applicants possess the unrestricted right to withdraw pending applications prior to evaluation without academic penalty or negative record.</li>
                                    <li><strong>Concurrent Applications:</strong> Students may submit applications to multiple vacancies but may only accept and hold one (1) active student assistantship contract per semester.</li>
                                </ul>
                            </div>

                            <!-- Section 7: Daily Time Records (DTR), Attendance Verification, & Allowance Disbursement -->
                            <div id="sec-7" class="pt-3">
                                <h2 class="h4">7. Daily Time Records (DTR), Attendance Verification, &amp; Allowance Disbursement</h2>
                                <div class="card-paper bg-cream p-3 mb-3 border border-line">
                                    <div class="d-flex align-items-center gap-2 text-danger fw-bold small mb-1">
                                        <i class="bi bi-shield-x fs-5"></i>
                                        <span>Zero Tolerance for Attendance Falsification</span>
                                    </div>
                                    <p class="small text-muted-custom mb-0">
                                        Submitting false hours, logging shifts not physically rendered, or forging supervisor signatures constitutes academic and administrative fraud resulting in immediate contract termination, restitution of unearned stipends, and referral to the Student Disciplinary Board.
                                    </p>
                                </div>
                                <ul>
                                    <li><strong>Accurate Daily Logging:</strong> Student assistants must record their daily time in/out through the official department logbook and digital timekeeping records as verified by their immediate supervisor.</li>
                                    <li><strong>Cut-off Schedules:</strong> DTR summaries must be endorsed by department heads and submitted to SASO on or before the 15th and 30th/31st of every month for stipend clearance.</li>
                                    <li><strong>Disbursement Method:</strong> Student stipends and tuition grants are disbursed semi-monthly via the University Cashier Office or registered student ATM/e-wallet accounts in accordance with city government payroll cycles.</li>
                                </ul>
                            </div>

                            <!-- Section 8: Examination Moratorium & Academic Priority Safeguards -->
                            <div id="sec-8" class="pt-3">
                                <h2 class="h4">8. Examination Moratorium &amp; Academic Priority Safeguards</h2>
                                <p>
                                    Academic excellence remains the uncompromised priority of Kolehiyo ng Lungsod ng Dasmariñas. To safeguard students during assessment periods:
                                </p>
                                <ul>
                                    <li><strong>Exam Week Shift Adjustments:</strong> Student assistants are entitled to temporary shift suspensions or schedule adjustments during official University Midterm and Final Examination Weeks without loss of assistantship standing.</li>
                                    <li><strong>Advance Notice:</strong> Students must notify their office supervisor at least three (3) working days prior to exam week to coordinate office coverage.</li>
                                    <li><strong>Interview Blackout:</strong> Hiring departments are prohibited from scheduling mandatory job interviews during active university exam weeks.</li>
                                </ul>
                            </div>

                            <!-- Section 9: Code of Conduct, Workplace Ethics, & Disciplinary Matrix -->
                            <div id="sec-9" class="pt-3">
                                <h2 class="h4">9. Code of Conduct, Workplace Ethics, &amp; Disciplinary Matrix</h2>
                                <p>
                                    Student Assistants represent the university and are expected to uphold the highest standards of professional decorum, punctuality, and institutional integrity:
                                </p>
                                <ul>
                                    <li><strong>Professional Etiquette:</strong> SAs must adhere to the prescribed university dress code, communicate respectfully with faculty, staff, and visitors, and execute assigned tasks diligently.</li>
                                    <li><strong>Unexcused Absences:</strong> Three (3) consecutive unexcused absences without prior supervisor notification shall be deemed abandonment of post (AWOL) and may result in contract cancellation.</li>
                                    <li><strong>Periodic Evaluations:</strong> Supervisors conduct mid-term and end-of-term performance appraisals. Unsatisfactory evaluations may result in non-renewal or reassignment.</li>
                                </ul>
                            </div>

                            <!-- Section 10: Office Confidentiality, Non-Disclosure, & Institutional Records -->
                            <div id="sec-10" class="pt-3">
                                <h2 class="h4">10. Office Confidentiality, Non-Disclosure, &amp; Institutional Records</h2>
                                <p>
                                    Student assistants assigned to campus offices (such as the Registrar, Guidance, Library, Dean's Offices, or IT Labs) frequently encounter confidential records. SAs are bound by strict non-disclosure obligations:
                                </p>
                                <ul>
                                    <li><strong>Absolute Non-Disclosure:</strong> SAs shall not disclose, copy, photograph, extract, or discuss any student records, grades, examination questionnaires, disciplinary memos, or personal data encountered during their duties.</li>
                                    <li><strong>Statutory Liability:</strong> Unauthorized duplication or distribution of official records constitutes a criminal violation under the <strong>Data Privacy Act of 2012 (RA 10173)</strong> and the <strong>Cybercrime Prevention Act of 2012 (RA 10175)</strong>, subjecting the violator to university expulsion and statutory legal prosecution.</li>
                                </ul>
                            </div>

                            <!-- Section 11: Safe Spaces, Anti-Harassment, & Gender Equality (RA 11313) -->
                            <div id="sec-11" class="pt-3">
                                <h2 class="h4">11. Safe Spaces, Anti-Harassment, &amp; Gender Equality (RA 11313)</h2>
                                <p>
                                    In strict accordance with the <strong>Safe Spaces Act (Republic Act No. 11313)</strong>, Kolehiyo ng Lungsod ng Dasmariñas is committed to maintaining a safe, inclusive, and harassment-free workplace for all student assistants:
                                </p>
                                <ul>
                                    <li><strong>Zero Tolerance for Harassment:</strong> Any form of verbal, psychological, physical, sexual, or gender-based harassment, bullying, or coercion by supervisors, co-workers, or students will not be tolerated.</li>
                                    <li><strong>Direct Redress Channel:</strong> Incidents may be reported immediately and confidentially to the KLD Committee on Decorum and Investigation (CODI) or the SASO Director. Retaliation against any student who reports a grievance is grounds for immediate administrative sanction.</li>
                                </ul>
                            </div>

                            <!-- Section 12: Acceptable Use of AI Assistant & Digital Tools -->
                            <div id="sec-12" class="pt-3">
                                <h2 class="h4">12. Acceptable Use of AI Assistant &amp; Digital Tools</h2>
                                <p>
                                    The platform features an integrated interactive AI Assistant ("Campus AI") designed to assist students with navigation, assistantship FAQs, and interview preparation:
                                </p>
                                <ul>
                                    <li><strong>Permitted Uses:</strong> Seeking guidance on job requirements, brainstorming cover letter formatting, and preparing for campus interviews.</li>
                                    <li><strong>Prohibited AI Conduct:</strong> Using automated scripts or AI bots to spam applications, fabricating credentials or academic records, attempting prompt injection attacks, or inputting confidential university data into the chat interface.</li>
                                    <li><strong>Third-Party Cloud Processing Notice:</strong> AI chat interactions are processed through external cloud endpoints (NVIDIA NIM APIs) and are subject to the disclosures detailed in our <a href="<?= $base_url ?>privacy.php" class="text-ink fw-bold text-decoration-underline">Data Privacy Policy</a>.</li>
                                </ul>
                            </div>

                            <!-- Section 13: Assistantship Duration, Resignation, Grievances, & Termination -->
                            <div id="sec-13" class="pt-3">
                                <h2 class="h4">13. Assistantship Duration, Resignation, Grievances, &amp; Termination</h2>
                                <ul>
                                    <li><strong>Contract Duration:</strong> Assistantship appointments are valid for the duration of one (1) academic semester and do not automatically roll over. Renewal requires a new application and eligibility clearance.</li>
                                    <li><strong>Voluntary Resignation:</strong> A student assistant wishing to resign before the term concludes must submit a written ten (10) working days notice to their supervisor and SASO to allow for orderly turnover.</li>
                                    <li><strong>Termination for Cause:</strong> Assistantship contracts may be terminated immediately for academic deficiency (falling below 2.50 GWA), gross misconduct, attendance fraud, breach of confidentiality, or violation of university rules.</li>
                                    <li><strong>Grievance Mechanism:</strong> In the event of workplace disputes or unjust treatment, student assistants may submit a formal complaint to the SASO Student Labor Review Board for mediation.</li>
                                </ul>
                            </div>

                            <!-- Section 14: Certificate of Service & Academic Credentialing -->
                            <div id="sec-14" class="pt-3">
                                <div class="card-paper bg-cream p-4 my-3 border border-line">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-patch-check-fill text-accent fs-5"></i>
                                            <strong class="text-ink">14. Certificate of Service &amp; Recommendations</strong>
                                        </div>
                                        <span class="small text-muted-custom"><i class="bi bi-award text-accent me-1"></i>Official SASO Credential</span>
                                    </div>
                                    <p class="small text-muted-custom mb-2">
                                        Upon successful completion of an assistantship semester with a satisfactory rating, students receive an official <strong>Certificate of Service</strong> issued by the Student Affairs &amp; Services Office (SASO) indicating the total verified hours rendered and department assignment.
                                    </p>
                                    <p class="small text-muted-custom mb-0">
                                        This credential counts toward student co-curricular activity portfolios and serves as formal pre-graduation professional experience recognized by prospective corporate and civil service employers.
                                    </p>
                                </div>
                            </div>

                            <!-- Section 15: Platform Availability, Scheduled Maintenance, & Audit Trails -->
                            <div id="sec-15" class="pt-3">
                                <h2 class="h4">15. Platform Availability, Scheduled Maintenance, &amp; Audit Trails</h2>
                                <p>
                                    While the university endeavors to maintain uninterrupted portal access, system administrators reserve the right to perform scheduled maintenance, database index updates, or emergency security patches resulting in temporary downtime.
                                </p>
                                <p>
                                    All platform transactions—including account logins, job creation, application state transitions, document downloads, and password resets—are cryptographically logged in system audit trails to prevent fraud and ensure absolute accountability.
                                </p>
                            </div>

                            <!-- Section 16: Amendments, Governing Law, & Legal Jurisdiction -->
                            <div id="sec-16" class="pt-3">
                                <h2 class="h4">16. Amendments, Governing Law, &amp; Legal Jurisdiction</h2>
                                <p>
                                    Kolehiyo ng Lungsod ng Dasmariñas reserves the right to modify, amend, or update these Terms of Service as mandated by institutional policies or national legislation. Notices of material modifications will be posted prominently in the <a href="<?= $base_url ?>updates.php" class="text-ink fw-bold text-decoration-underline">Career Updates Hub</a>.
                                </p>
                                <p>
                                    These Terms shall be interpreted and governed in accordance with the laws of the Republic of the Philippines. Any legal disputes arising from or connected with this platform shall be subject to the exclusive jurisdiction of the competent courts of the <strong>City of Dasmariñas, Province of Cavite</strong>.
                                </p>
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
                                <nav class="toc-nav d-flex flex-column gap-1" style="max-height: 480px; overflow-y: auto; padding-right: 4px;">
                                    <a href="#sec-1" class="toc-link small"><i class="bi bi-chevron-right"></i> 1. Acceptance &amp; Scope</a>
                                    <a href="#sec-2" class="toc-link small"><i class="bi bi-chevron-right"></i> 2. Eligibility Criteria</a>
                                    <a href="#sec-3" class="toc-link small"><i class="bi bi-chevron-right"></i> 3. 20-Hour Weekly Cap</a>
                                    <a href="#sec-4" class="toc-link small"><i class="bi bi-chevron-right"></i> 4. Account Security</a>
                                    <a href="#sec-5" class="toc-link small"><i class="bi bi-chevron-right"></i> 5. Supervisor Rules</a>
                                    <a href="#sec-6" class="toc-link small"><i class="bi bi-chevron-right"></i> 6. Application Workflow</a>
                                    <a href="#sec-7" class="toc-link small"><i class="bi bi-chevron-right"></i> 7. DTR &amp; Compensation</a>
                                    <a href="#sec-8" class="toc-link small"><i class="bi bi-chevron-right"></i> 8. Exam Moratorium</a>
                                    <a href="#sec-9" class="toc-link small"><i class="bi bi-chevron-right"></i> 9. Code of Conduct</a>
                                    <a href="#sec-10" class="toc-link small"><i class="bi bi-chevron-right"></i> 10. Confidentiality &amp; NDA</a>
                                    <a href="#sec-11" class="toc-link small"><i class="bi bi-chevron-right"></i> 11. Safe Spaces (RA 11313)</a>
                                    <a href="#sec-12" class="toc-link small"><i class="bi bi-chevron-right"></i> 12. AI Assistant Usage</a>
                                    <a href="#sec-13" class="toc-link small"><i class="bi bi-chevron-right"></i> 13. Resignation &amp; Grievances</a>
                                    <a href="#sec-14" class="toc-link small"><i class="bi bi-chevron-right"></i> 14. Certificate of Service</a>
                                    <a href="#sec-15" class="toc-link small"><i class="bi bi-chevron-right"></i> 15. Maintenance &amp; Audits</a>
                                    <a href="#sec-16" class="toc-link small"><i class="bi bi-chevron-right"></i> 16. Amendments &amp; Law</a>
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
                                        <div><strong>20h Weekly Limit:</strong> Mandatory university standard to protect your studies and lecture hours.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-mortarboard text-accent fs-6 mt-1"></i>
                                        <div><strong>Academic Standing:</strong> Minimum 2.50 GWA, 12 units enrolled, and zero failing grades required.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-shield-lock text-accent fs-6 mt-1"></i>
                                        <div><strong>Strict Non-Disclosure:</strong> Confidential campus office documents protected under RA 10173.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-cash-stack text-accent fs-6 mt-1"></i>
                                        <div><strong>Semi-Monthly Stipends:</strong> Processed on the 15th/30th via the University Cashier.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-calendar-event text-accent fs-6 mt-1"></i>
                                        <div><strong>Exam Week Leave:</strong> Shift adjustments permitted during midterm and final exams with zero penalty.</div>
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
