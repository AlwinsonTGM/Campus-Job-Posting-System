<?php
/**
 * Campus Job Posting System - Terms of Service & Campus Work Guidelines
 * Archetype F: Prose (COAL101 Blueprint)
 * Updated for Academic Year 2026-2027 (KLD SASO Regulatory Standards)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Terms of Service & Campus Work Guidelines';
$from_register = isset($_GET['from']) && $_GET['from'] === 'register';
$fallback_url = $from_register ? 'register.php' : 'index.php';

require_once __DIR__ . '/includes/header.php';
?>

<style>
.prose, .prose p, .prose li {
    font-size: 1.0625rem;
    line-height: 1.85;
}
.prose p {
    text-align: justify;
    text-justify: inter-word;
    hyphens: auto;
    -webkit-hyphens: auto;
}
.prose p.lead {
    font-size: 1.15rem;
    line-height: 1.85;
    text-align: justify;
    text-justify: inter-word;
}
.prose .card-paper p {
    font-size: 1.02rem;
    line-height: 1.8;
    text-align: justify;
    text-justify: inter-word;
    hyphens: auto;
    -webkit-hyphens: auto;
}
.prose .card-paper h2,
.prose .card-paper h3,
.prose .card-paper h4 {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
</style>

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
                            <strong>Effective Date:</strong> September 26, 2026 &bull; Institutional Rules Governing On-Campus Student Assistantships &amp; Hiring Units
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
                                These Terms of Service and Campus Work Guidelines constitute a legally binding institutional agreement governing the access, operation, administrative administration, and utilization of the <strong>KLD Campus Job Posting System (Campus Hire)</strong>. This agreement applies unconditionally to all enrolled student applicants, active student assistants (SAs), faculty coordinators, department heads, administrative division supervisors, and accredited campus partner employers of <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>. Promulgated under the joint regulatory authority of the KLD Office of the Vice President for Academic and Student Affairs (OVPASA) and the Student Affairs and Services Office (SASO), these guidelines embody the university's statutory mandate to balance meaningful experiential work-study opportunities with uncompromised academic excellence, student labor protection, and institutional integrity in accordance with national education, labor, and municipal statutes.
                            </p>

                            <!-- Section 1: Acceptance of Terms & Institutional Scope -->
                            <div id="sec-1" class="pt-2">
                                <h2 class="h4">1. Acceptance of Terms &amp; Institutional Scope</h2>
                                <p>
                                    <strong>1.1 Contractual Nature and Binding Assent.</strong> By accessing, browsing, registering an institutional profile upon, or submitting digital documentation through the Platform, you formally certify that you possess the requisite legal capacity, institutional standing, and authority to enter into this agreement, and that you have read, comprehended, and unconditionally agreed to be bound by these Terms of Service. These terms constitute a legally enforceable pact between each registered user and Kolehiyo ng Lungsod ng Dasmariñas. Accessing system modules, publishing employment vacancies, submitting assistantship dossiers, or verifying student timekeeping records serves as affirmative and legally binding assent to adhere to these rules, the official KLD Student Handbook, and applicable national labor and educational statutes. Continued utilization of the portal following any administrative revisions constitutes continuous ratifying acceptance of all modified provisions.
                                </p>
                                <p>
                                    <strong>1.2 Institutional Scope, Administrative Oversight, and Invalidation.</strong> These guidelines are formulated and enforced under the direct statutory authority of the KLD Board of Trustees, the Office of the College President, the Office of the Vice President for Academic and Student Affairs (OVPASA), and the Student Affairs and Services Office (SASO). They govern all on-campus student assistantships, work-study stipends, faculty evaluations, and departmental hiring workflows across all colleges, institutes, administrative divisions, and affiliated research units of the university. If any applicant, supervisor, or administrative personnel does not consent to, understand, or agree with any covenant, limitation, or disciplinary procedure set forth within these Terms of Service, they are strictly and immediately required to desist from creating an account, discontinue portal access, and withdraw any pending job applications or requisitions.
                                </p>
                            </div>

                            <!-- Section 2: Student Assistant Eligibility & Academic Load Requirements -->
                            <div id="sec-2" class="pt-3">
                                <h2 class="h4">2. Student Assistant Eligibility &amp; Academic Load Requirements</h2>
                                <p>
                                    The Student Assistantship Program at Kolehiyo ng Lungsod ng Dasmariñas is established and maintained strictly as an experiential work-study educational grant and student financial assistance initiative rather than conventional commercial or full-time employment. To preserve the primacy of degree completion and ensure equitable access to institutional grants, an applicant must satisfy all of the following statutory eligibility benchmarks:
                                </p>
                                <p>
                                    <strong>2.1 Undergraduate Matriculation and Minimum Academic Study Load.</strong> The applicant must be a currently enrolled, bona fide undergraduate student matriculated in any of the accredited Academic Institutes of Kolehiyo ng Lungsod ng Dasmariñas carrying an active, regular academic study load of not less than twelve (12) units during the active semester, or the officially certified terminal curriculum load for graduating senior students. Because the assistantship program is intended to complement rather than interrupt degree progression, non-enrolled individuals, students on leaves of absence (LOA), academic dropouts, and external individuals not officially matriculated in the College are strictly ineligible for assistantship appointments.
                                </p>
                                <p>
                                    <strong>2.2 Academic Standing and Grade Point Thresholds.</strong> To qualify for appointment and maintain continuing assistantship status, a student must demonstrate consistent academic competence by maintaining a cumulative and preceding-semester General Weighted Average (GWA) of <strong>2.50 or higher</strong> (or its institutional grade equivalent). Students who have incurred unresolved failing marks (5.0), unexcused dropped subjects without formal university approval, or excessive uncompleted (INC) marks from preceding academic terms are barred from appointment until academic deficiencies are completely resolved and cleared by the Office of the University Registrar.
                                </p>
                                <p>
                                    <strong>2.3 Disciplinary Record Clearance and Good Moral Character.</strong> Every applicant must be in good moral standing with the university community, possessing a validated Certificate of Good Moral Character issued by SASO. Applicants who have been found guilty of, or are currently subject to active formal investigations regarding, major behavioral infractions, academic dishonesty (such as cheating or plagiarism), physical violence, harassment, vandalism, or campus disruptions before the KLD Student Disciplinary Board or Office of Student Conduct are strictly disqualified from serving as campus student assistants.
                                </p>
                                <p>
                                    <strong>2.4 Mandatory Verification of Registration and Study Load Schedules.</strong> Applicants are obligated to upload a verified digital copy of their official Certificate of Registration (COR) and detailed study load schedule issued by the University Registrar upon profile registration and prior to submitting any job application. The submitted COR must clearly depict official subject enrollment, course unit allocations, physical classroom room assignments, and enrolled lecture or laboratory timeslots. Hiring departments and SASO administrators utilize this verified documentation to confirm enrollment authenticity and ensure that work assignments do not collide with classroom commitments.
                                </p>
                                <p>
                                    <strong>2.5 Mandatory Underload Notification and Workload Review.</strong> In the event that a student assistant officially drops, withdraws from, or experiences an administrative cancellation of enrolled subjects during an ongoing work semester that reduces their academic load below the mandatory twelve (12) unit threshold, the student is legally and procedurally bound to submit formal written notification to both their department supervisor and the SASO Director within five (5) working days. SASO shall evaluate whether the curriculum reduction warrants workload rescheduling, a temporary reduction in rendered assistantship hours, or contract suspension to prioritize the student's academic recovery.
                                </p>
                            </div>

                            <!-- Section 3: Work Hour Regulations (20-Hour Weekly Cap) -->
                            <div id="sec-3" class="pt-3">
                                <h2 class="h4">3. Work Hour Regulations (20-Hour Weekly Cap)</h2>
                                <div class="card-paper bg-cream p-3 mb-3 border border-line">
                                    <div class="d-flex align-items-center gap-2 text-ink fw-bold mb-1" style="font-size: 1.05rem;">
                                        <i class="bi bi-exclamation-triangle-fill text-accent fs-5"></i>
                                        <span>Strict Academic Priority &amp; Anti-Overwork Safeguard</span>
                                    </div>
                                    <p class="text-muted-custom mb-0">
                                        Student Assistants are legally and institutionally restricted to working a maximum of <strong>20 hours per week</strong> during regular instructional terms. Employment shifts must never conflict with enrolled lecture blocks, laboratory sessions, or university academic requirements.
                                    </p>
                                </div>
                                <p>
                                    <strong>3.1 Mandatory Twenty-Hour Weekly Ceiling and Academic Priority.</strong> Student assistants are legally and institutionally restricted to rendering a maximum of twenty (20) work hours per week during regular instructional semesters. This ceiling is an immutable institutional safeguard enacted to guarantee that campus labor never compromises lecture attendance, exam performance, laboratory research, or essential student physical and mental rest. Under no circumstances may a student assistant be scheduled, permitted, or encouraged to render work shifts during hours where they are enrolled in scheduled lectures, seminars, laboratory blocks, or institutional academic activities.
                                </p>
                                <p>
                                    <strong>3.2 Strict Prohibition of Overtime and Supervisor Coercion.</strong> Academic deans, department chairpersons, laboratory heads, and office supervisors are categorically forbidden from assigning, scheduling, demanding, approving, or coercing student assistants into rendering shifts in excess of the twenty (20) hour weekly cap during class weeks. Any supervisor who forces a student assistant to skip academic classes or work beyond approved hours shall be subject to administrative reprimand and removal from the student assistantship hosting program. Student assistants possess the unrestricted right to decline shifts that encroach upon instructional blocks or exceed twenty hours without fear of negative evaluation or retaliation.
                                </p>
                                <p>
                                    <strong>3.3 Semester Break Exceptions and Maximum Shift Expansions.</strong> During official university semester breaks, mid-year summer periods, and academic recesses where classes are not in session, student assistants may render up to forty (40) hours per week, conditioned upon prior written requisition from the hiring department and budgetary endorsement by SASO. This expanded schedule permits campus units to complete inventory audits, laboratory overhauls, and registration logistics while providing student assistants with enhanced income opportunities. All extended shifts remain subject to standard daily rest periods and occupational safety regulations.
                                </p>
                                <p>
                                    <strong>3.4 Programmatic Conflict Prevention via Availability Matrices.</strong> The Platform enforces academic scheduling safeguards algorithmically through its eighteen-slot Weekly Shift Matrix, which maps student availability across Morning (8:00 AM to 12:00 PM), Afternoon (1:00 PM to 5:00 PM), and Evening (5:00 PM to 8:00 PM) intervals from Monday through Saturday. The system programmatically blocks department supervisors from scheduling shifts that overlap with time blocks marked as instructional in the student's validated study load, rendering it technically impossible to finalize conflicting appointments on the platform.
                                </p>
                            </div>

                            <!-- Section 4: User Account Security & Single-Account Policy -->
                            <div id="sec-4" class="pt-3">
                                <h2 class="h4">4. User Account Security &amp; Single-Account Policy</h2>
                                <p>
                                    <strong>4.1 Single Institutional Identity and Duplicate Account Bans.</strong> Every registered student, faculty supervisor, and department administrator is authorized to establish and maintain exactly one (1) unique account on the Platform. The creation of duplicate accounts, dummy profiles, secondary registrations, or the unauthorized impersonation of fellow students or university officials constitutes serious identity fraud. System security monitoring daemons actively identify and flag correlated email addresses, duplicate student numbers, and shared device footprints; any user detected operating multiple accounts shall face immediate, permanent account termination and referral to the Student Disciplinary Board.
                                </p>
                                <p>
                                    <strong>4.2 Institutional Domain Authentication and Corporate Validation.</strong> All student applicants and university personnel must register and authenticate using their official institutional email address ending strictly in <code>@kld.edu.ph</code>. External accredited employer representatives and corporate partners must register using validated corporate domains and provide verified documentation of legal registration, such as business permits, Department of Trade and Industry (DTI) certificates, or Securities and Exchange Commission (SEC) certificates of incorporation, prior to publishing any vacancy requisitions.
                                </p>
                                <p>
                                    <strong>4.3 Non-Transferability and Shared Credential Prohibitions.</strong> Account privileges, login credentials, and user access rights are strictly non-transferable and personal to the registered individual. Users are expressly prohibited from sharing system passwords, lending authentication sessions, delegating dashboard controls, or authorizing third parties, including peers, family members, or colleagues, to submit job applications, modify availability matrices, conduct candidate reviews, or endorse Daily Time Records on their behalf. Any action executed under an authenticated account shall be legally deemed the personal action of the registered account owner.
                                </p>
                                <p>
                                    <strong>4.4 Duty to Report Security Compromise and Credential Leakage.</strong> Registered users bear the continuous responsibility to maintain the secrecy and security of their platform credentials. In the event of a suspected security intrusion, unauthorized session activity, device theft, or accidental password disclosure, the user must immediately execute a password reset and notify system administrators via <code>careers@kld.edu.ph</code>. The College disclaims administrative and financial liability for unauthorized actions executed prior to receipt of formal breach notification from the affected user.
                                </p>
                            </div>

                            <!-- Section 5: Campus Employer & Department Supervisor Obligations -->
                            <div id="sec-5" class="pt-3">
                                <h2 class="h4">5. Campus Employer &amp; Department Supervisor Obligations</h2>
                                <p>
                                    University academic departments, administrative divisions, research centers, and accredited corporate partners operating as hiring units on the Platform enter into an institutional compact with the College and agree to uphold the following operational and ethical obligations:
                                </p>
                                <p>
                                    <strong>5.1 Truthful, Transparent, and Non-Misleading Requisitions.</strong> All published job postings must provide an accurate, transparent, and comprehensive description of the position, including specific administrative or technical tasks, required student competencies, physical campus workstation locations, work modalities (On-Campus, Hybrid, or Remote), required weekly hour commitments, and applicable hourly stipend allowances. Hiring units are strictly barred from publishing fictitious positions, inflating job duties, or advertising ghost requisitions for which no approved department budget or genuine labor requirement exists.
                                </p>
                                <p>
                                    <strong>5.2 Educational Purpose, Safety, and Dignity of Student Labor.</strong> All assigned assistantship tasks must be substantive, educational, administrative, technical, laboratory-support, or research-oriented in character, designed to augment the student's co-curricular growth and workplace readiness. Supervisors are strictly prohibited from assigning menial personal errands, private chores, hazardous or heavy industrial labor, duties that violate campus health and safety protocols, or off-campus excursions lacking advance written authorization and insurance clearances from the SASO Director.
                                </p>
                                <p>
                                    <strong>5.3 Non-Discrimination, Inclusivity, and Equal Opportunity.</strong> Candidate screening, dossier evaluations, interview selections, and hiring decisions must be conducted strictly on the basis of academic merit, demonstrated skillsets, timetable availability alignment, and objective interview performance. Hiring supervisors shall not discriminate against any applicant on the grounds of gender identity, sexual orientation, religion, socio-economic background, physical disability that does not impede task execution, academic institute affiliation, or political beliefs.
                                </p>
                                <p>
                                    <strong>5.4 Candidate Evaluation Timelines and Review Obligations.</strong> Hiring units are institutionally obligated to manage their recruitment pipelines expeditiously to respect student time and academic planning. Supervisors must review incoming applications, conduct structured candidate interviews, and record formal hiring dispositions (Acceptance, Declination, or Shortlisting) within fourteen (14) calendar days of submission. Inactive requisitions that fail to record candidate movement within thirty days shall be administratively archived by SASO to preserve vacancy freshness.
                                </p>
                            </div>

                            <!-- Section 6: Application Workflow, Selection Gate, & No-Guarantee Disclaimer -->
                            <div id="sec-6" class="pt-3">
                                <h2 class="h4">6. Application Workflow, Selection Gate, &amp; No-Guarantee Disclaimer</h2>
                                <p>
                                    <strong>6.1 Structured Five-Stage Progressive Application Lifecycle.</strong> All applications submitted through the Platform advance through a transparent, auditable five-stage progression pipeline: (a) <em>Pending Review</em>, wherein the initial dossier is awaiting departmental screening; (b) <em>Under Evaluation</em>, indicating that the supervisor has accessed and shortlisted the candidate portfolio; (c) <em>Interview Scheduled</em>, signifying that the student has been invited for an on-campus or virtual interview; (d) <em>Accepted / Declined</em>, representing the final departmental hiring disposition; and (e) <em>Withdrawn</em>, reflecting voluntary applicant cancellation. System audit logs capture timestamps and supervisor IDs at each stage transition to maintain absolute administrative transparency.
                                </p>
                                <p>
                                    <strong>6.2 Absence of Placement Guarantee and Departmental Prerogative.</strong> Submitting an application through this portal does not create an entitlement to, or guarantee of, employment, interview invitation, or assistantship appointment. Hiring decisions reside within the sole administrative discretion of the recruiting campus department, subject to budgetary allocations approved by SASO, departmental vacancy ceilings, and competitive merit screening. The university disclaims any warranty that an applicant will secure an assistantship appointment during any given academic semester.
                                </p>
                                <p>
                                    <strong>6.3 Unrestricted Student Right of Voluntary Withdrawal.</strong> Student applicants possess the unrestricted, unilateral right to withdraw any pending job application prior to the execution of a formal assistantship agreement without incurring negative administrative record, academic penalty, or prejudice toward future applications. Upon clicking the withdrawal action, the student's dossier is immediately removed from the department's active review drawer, restoring the student's application capacity.
                                </p>
                                <p>
                                    <strong>6.4 Single Active Appointment Restriction and Dual-Contract Prohibition.</strong> While student applicants are permitted to submit concurrent applications to multiple published requisitions to maximize their opportunities, no student may hold more than one (1) active student assistantship contract in any given academic semester. Upon receiving and accepting an official assistantship offer from a campus department, all other pending applications across other departments are automatically closed by the system to prevent double-contracting, overwork, and municipal payroll duplication.
                                </p>
                            </div>

                            <!-- Section 7: Daily Time Records (DTR), Attendance Verification, & Allowance Disbursement -->
                            <div id="sec-7" class="pt-3">
                                <h2 class="h4">7. Daily Time Records (DTR), Attendance Verification, &amp; Allowance Disbursement</h2>
                                <div class="card-paper bg-cream p-3 mb-3 border border-line">
                                    <div class="d-flex align-items-center gap-2 text-danger fw-bold mb-1" style="font-size: 1.05rem;">
                                        <i class="bi bi-shield-x fs-5"></i>
                                        <span>Zero Tolerance for Attendance Falsification</span>
                                    </div>
                                    <p class="text-muted-custom mb-0">
                                        Submitting false hours, logging shifts not physically rendered, or forging supervisor signatures constitutes academic and administrative fraud resulting in immediate contract termination, restitution of unearned stipends, and referral to the Student Disciplinary Board.
                                    </p>
                                </div>
                                <p>
                                    <strong>7.1 Mandatory Daily Timekeeping and Multi-Factor Verification.</strong> Student assistants must record their daily arrival and departure times accurately through official departmental sign-in registries and digital portal timekeeping interfaces. Time records must reflect the exact hours physically rendered at designated campus workstations. Supervisors are obligated to inspect and verify student attendance daily to confirm physical presence and task execution before validating semi-monthly summaries.
                                </p>
                                <p>
                                    <strong>7.2 Zero-Tolerance Policy and Penalties for Attendance Falsification.</strong> The falsification of timekeeping records, including logging hours not physically rendered, pre-signing future shifts, buddy punching, or forging supervisor approval signatures, constitutes severe academic, administrative, and criminal fraud under university rules and state law. Any student assistant found to have engaged in attendance tampering shall suffer immediate contract revocation, forfeiture and mandatory restitution of unearned stipends, permanent debarment from campus financial aid programs, and formal referral to the KLD Student Disciplinary Board for suspension or expulsion.
                                </p>
                                <p>
                                    <strong>7.3 Semi-Monthly Cut-off Deadlines and Endorsement Manifests.</strong> In alignment with municipal payroll processing calendars, DTR summaries must be consolidated, supervisor-certified, endorsed by department heads, and submitted to SASO on or before the 15th and penultimate calendar day of each month. Late submissions resulting from departmental delays shall be automatically processed in the succeeding payroll cycle without forfeiture of earned compensation.
                                </p>
                                <p>
                                    <strong>7.4 Allowance Disbursement Modalities and Financial Channels.</strong> Student assistantship stipends, meal allowances, and tuition grants are disbursed semi-monthly following municipal audit verification. Payments are processed through the University Cashier Office, officially accredited partner municipal banking institutions, or registered student digital wallets in strict adherence to City Government of Dasmariñas accounting circulars and Commission on Audit (COA) guidelines.
                                </p>
                            </div>

                            <!-- Section 8: Examination Moratorium & Academic Priority Safeguards -->
                            <div id="sec-8" class="pt-3">
                                <h2 class="h4">8. Examination Moratorium &amp; Academic Priority Safeguards</h2>
                                <p>
                                    <strong>8.1 Mandatory Shift Suspension During University Examination Weeks.</strong> Academic excellence remains the uncompromised priority of Kolehiyo ng Lungsod ng Dasmariñas. To safeguard students during major academic evaluation periods, all student assistants are entitled to temporary shift suspensions or schedule adjustments during official University Midterm and Final Examination Weeks without loss of assistantship standing, contract cancellation, or disciplinary penalty.
                                </p>
                                <p>
                                    <strong>8.2 Advance Departmental Coordination and Scheduling Notice.</strong> To ensure orderly administrative office operations and front-desk coverage, student assistants intending to invoke examination shift adjustments must provide written notification of their examination timetable to their immediate supervisor at least three (3) working days prior to the commencement of official examination week. Supervisors shall adjust office duty rosters accordingly.
                                </p>
                                <p>
                                    <strong>8.3 Examination Period Recruitment Blackout.</strong> Campus hiring units and external employers are strictly prohibited from scheduling mandatory candidate interviews, screening examinations, or orientation sessions during designated university examination weeks. Any recruitment deadline falling within an examination week shall be automatically extended by the system to the succeeding instructional school week.
                                </p>
                            </div>

                            <!-- Section 9: Code of Conduct, Workplace Ethics, & Disciplinary Matrix -->
                            <div id="sec-9" class="pt-3">
                                <h2 class="h4">9. Code of Conduct, Workplace Ethics, &amp; Disciplinary Matrix</h2>
                                <p>
                                    <strong>9.1 Professional Standards, Punctuality, and Campus Decorum.</strong> Student assistants serve as institutional ambassadors and frontline representatives of the College. While on duty, student assistants must observe the official KLD Student Dress Code, wear their institutional identification badges prominently, maintain punctual attendance, treat faculty, administrators, fellow students, and campus visitors with utmost courtesy, and perform assigned tasks with diligence, honesty, and professional integrity.
                                </p>
                                <p>
                                    <strong>9.2 Post Abandonment, AWOL Classifications, and Disciplinary Review.</strong> Incurring three (3) consecutive unexcused work absences without prior supervisor notification and SASO clearance shall be formally classified as abandonment of post (Absence Without Official Leave, or AWOL). Post abandonment constitutes grounds for immediate contract cancellation, notification to academic institute deans, and assignment of the vacancy to replacement candidates on the applicant waitlist.
                                </p>
                                <p>
                                    <strong>9.3 Performance Appraisals and Continuous Quality Assessment.</strong> Supervisors conduct structured mid-term and end-of-term performance appraisals evaluating the student's technical competence, initiative, punctuality, and workplace cooperation. Appraisals are reviewed by SASO; satisfactory appraisals are prerequisite for contract renewal, while documented substandard evaluations, chronic absenteeism, or behavioral insubordination may warrant immediate contract termination or non-renewal.
                                </p>
                            </div>

                            <!-- Section 10: Office Confidentiality, Non-Disclosure, & Institutional Records -->
                            <div id="sec-10" class="pt-3">
                                <h2 class="h4">10. Office Confidentiality, Non-Disclosure, &amp; Institutional Records</h2>
                                <p>
                                    <strong>10.1 Binding Non-Disclosure and Institutional Confidentiality.</strong> Student assistants assigned to campus divisions handling sensitive or privileged materials, including the Office of the University Registrar, Student Affairs, Deans' Offices, Guidance and Counseling, Libraries, and IT infrastructure, must maintain strict confidentiality. SAs shall not inspect, disclose, reproduce, photograph, copy, extract, or discuss any student academic records, grades, examination questionnaires, disciplinary memos, financial aid records, or personal data encountered during their duties, whether during their appointment or following separation from the university.
                                </p>
                                <p>
                                    <strong>10.2 Statutory Penalties for Breach under RA 10173 and RA 10175.</strong> Unauthorized disclosure, extraction, photograph taking, or social media publication of official university documents constitutes a severe criminal and administrative violation under the <strong>Data Privacy Act of 2012 (RA 10173)</strong>, the <strong>Cybercrime Prevention Act of 2012 (RA 10175)</strong>, and the KLD Student Handbook. Any student assistant who breaches confidentiality covenants shall be subject to immediate dismissal from assistantship, permanent expulsion from the College, and civil or criminal prosecution before competent judicial courts.
                                </p>
                            </div>

                            <!-- Section 11: Safe Spaces, Anti-Harassment, & Gender Equality (RA 11313) -->
                            <div id="sec-11" class="pt-3">
                                <h2 class="h4">11. Safe Spaces, Anti-Harassment, &amp; Gender Equality (RA 11313)</h2>
                                <p>
                                    <strong>11.1 Zero-Tolerance Anti-Harassment and Safe Workplace Commitment.</strong> In strict accordance with the <strong>Safe Spaces Act (Republic Act No. 11313)</strong> and university gender-fair policies, Kolehiyo ng Lungsod ng Dasmariñas is committed to maintaining a safe, inclusive, dignified, and harassment-free workplace for all student assistants. The College maintains zero tolerance for any form of sexual, psychological, physical, or verbal harassment, stalking, bullying, inappropriate touching, unsolicited sexual comments, homophobic or transphobic remarks, or gender-based discrimination perpetrated by supervisors, co-workers, faculty members, or students.
                                </p>
                                <p>
                                    <strong>11.2 Confidential Reporting Channels, CODI Jurisdiction, and Anti-Retaliation.</strong> Any student assistant who experiences, witnesses, or is subjected to workplace harassment or discrimination may lodge a confidential complaint directly with the KLD Committee on Decorum and Investigation (CODI) or the SASO Director. All complaints are investigated swiftly under strict confidentiality. Any act of retaliation, intimidation, or negative evaluation against a student who reports a grievance is strictly prohibited and constitutes independent grounds for immediate administrative termination and disciplinary sanction.
                                </p>
                            </div>

                            <!-- Section 12: Acceptable Use of AI Assistant & Digital Tools -->
                            <div id="sec-12" class="pt-3">
                                <h2 class="h4">12. Acceptable Use of AI Assistant &amp; Digital Tools</h2>
                                <p>
                                    <strong>12.1 Permitted Guidance, Navigation, and Preparation Functions.</strong> The Platform features an interactive conversational AI Assistant (&ldquo;Campus AI&rdquo;) designed to enhance student navigation, assist in understanding assistantship regulations, provide advice on cover letter formatting, and facilitate simulated interview preparation. Students and applicants are encouraged to utilize the assistant for legitimate educational, guidance, and navigation purposes in accordance with university academic integrity standards.
                                </p>
                                <p>
                                    <strong>12.2 Prohibited AI Manipulation, Automation, and Prompt Injection.</strong> Users are strictly prohibited from utilizing automated scripts, bots, scrapers, or browser extensions to manipulate the AI interface, spam job applications, generate fraudulent resumes, or execute prompt injection attacks designed to subvert system instructions. Furthermore, users must strictly adhere to the directive never to transmit personal, financial, academic, or confidential institutional records into the AI chat interface, as cloud telemetry may be monitored and retained for model training by third-party infrastructure providers.
                                </p>
                                <p>
                                    <strong>12.3 Third-Party Cloud Processing Notice and Data Boundaries.</strong> Users acknowledge that interactions with the AI assistant are routed through external cloud inference microservices (NVIDIA NIM APIs). All interactions are governed by the statutory disclosures, model training disclaimers, and data protection boundaries set forth in the University <a href="<?= $base_url ?>privacy.php" class="text-ink fw-bold text-decoration-underline">Data Privacy Policy</a>. Engagement with the AI assistant is entirely optional; electing not to use the AI tool carries zero penalty or disadvantage.
                                </p>
                            </div>

                            <!-- Section 13: Assistantship Duration, Resignation, Grievances, & Termination -->
                            <div id="sec-13" class="pt-3">
                                <h2 class="h4">13. Assistantship Duration, Resignation, Grievances, &amp; Termination</h2>
                                <p>
                                    <strong>13.1 Semester-Bound Contract Duration and Non-Automatic Rollover.</strong> Every student assistantship appointment is contractually bound to the duration of one (1) academic semester and terminates automatically upon the conclusion of the semester's final examination period. Assistantship appointments do not automatically roll over or renew. Continuation into a succeeding semester requires the submission of a new application, re-verification of academic standing (minimum 2.50 GWA), submission of an updated study load, and issuance of a new contract endorsed by SASO.
                                </p>
                                <p>
                                    <strong>13.2 Voluntary Resignation Protocol and Transition Notice.</strong> A student assistant who finds it necessary to resign from their post prior to the conclusion of the academic semester, whether due to academic load demands, health conditions, or personal circumstances, must tender a written ten (10) working days notice to their immediate supervisor and the SASO Director. This transition period ensures an orderly turnover of office tasks, return of university property, and recruitment of replacement candidates without disrupting departmental operations.
                                </p>
                                <p>
                                    <strong>13.3 Summary Termination for Cause.</strong> The College and SASO reserve the right to execute summary termination of an assistantship contract for cause at any point during the academic term. Grounds for summary termination include: dropping below the 12-unit academic minimum, cumulative GWA falling below 2.50, DTR falsification, gross misconduct or insubordination, breach of office confidentiality, disciplinary suspension by the university, or unexcused post abandonment.
                                </p>
                                <p>
                                    <strong>13.4 Grievance Redress, Institutional Mediation, and SASO Review Board.</strong> In the event of workplace disagreements, unfair labor demands, unsafe work environments, or disputes concerning task allocations between a student assistant and their office supervisor, either party may file a formal grievance before the SASO Student Labor Review Board. The Board shall convene an impartial conciliation conference within seven (7) working days to mediate the dispute, recommend workload adjustments, or facilitate a constructive transfer to another campus department if deemed appropriate.
                                </p>
                            </div>

                            <!-- Section 14: Certificate of Service & Academic Credentialing -->
                            <div id="sec-14" class="pt-3">
                                <div class="card-paper bg-cream p-4 my-3 border border-line">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-patch-check-fill text-accent fs-5"></i>
                                            <h4 class="card-paper-title mb-0" style="font-size: 1.05rem;">14. Certificate of Service &amp; Recommendations</h4>
                                        </div>
                                        <span class="text-muted-custom" style="font-size: 0.95rem;"><i class="bi bi-award text-accent me-1"></i>Official SASO Credential</span>
                                    </div>
                                    <p class="text-muted-custom mb-2">
                                        <strong>14.1 Issuance of Official Certificate of Service.</strong> Upon the satisfactory completion of an assistantship semester and verification of all rendered timekeeping logs, student assistants receive an official <strong>Certificate of Service</strong> issued under the seal of the Student Affairs &amp; Services Office (SASO). This certificate documents the total audited hours rendered, the specific campus department served, and the core administrative, technical, or research competencies demonstrated by the student.
                                    </p>
                                    <p class="text-muted-custom mb-0">
                                        <strong>14.2 Co-Curricular Recognition and Pre-Graduation Career Value.</strong> The Certificate of Service is officially integrated into the student's co-curricular transcript and pre-graduation portfolio. Recognized by accredited corporate partners, civil service accrediting bodies, and graduate admissions committees, this credential provides verifiable proof of professional workplace competencies, teamwork, and institutional service prior to college graduation.
                                    </p>
                                </div>
                            </div>

                            <!-- Section 15: Platform Availability, Scheduled Maintenance, & Audit Trails -->
                            <div id="sec-15" class="pt-3">
                                <h2 class="h4">15. Platform Availability, Scheduled Maintenance, &amp; Audit Trails</h2>
                                <p>
                                    <strong>15.1 System Availability and Maintenance Windows.</strong> While Kolehiyo ng Lungsod ng Dasmariñas endeavors to maintain continuous, uninterrupted portal availability, the university reserves the right to conduct scheduled maintenance, database optimizations, security vulnerability patching, and infrastructure upgrades that may result in temporary system downtime. Whenever practicable, advance notice of scheduled maintenance windows will be announced through the portal banner and official institutional communication channels.
                                </p>
                                <p>
                                    <strong>15.2 Tamper-Evident Cryptographic Audit Trails.</strong> To guarantee administrative transparency, prevent fraud, and establish non-repudiation, the Platform automatically captures tamper-evident cryptographic audit trails of all system transactions. All user authentications, application state transitions, document uploads, DTR endorsements, and profile modifications are recorded with immutable timestamps, user IDs, and network IP addresses, serving as conclusive evidentiary records in any administrative review or dispute.
                                </p>
                            </div>

                            <!-- Section 16: Amendments, Governing Law, & Legal Jurisdiction -->
                            <div id="sec-16" class="pt-3">
                                <h2 class="h4">16. Amendments, Governing Law, &amp; Legal Jurisdiction</h2>
                                <p>
                                    <strong>16.1 Right of Policy Amendment and Notification.</strong> Kolehiyo ng Lungsod ng Dasmariñas reserves the institutional right to update, amend, modify, or revise these Terms of Service at any time to align with new institutional policies, City Ordinances, Department of Labor and Employment (DOLE) circulars, or Commission on Higher Education (CHED) memoranda. Formal notice of material revisions will be published in the <a href="<?= $base_url ?>updates.php" class="text-ink fw-bold text-decoration-underline">Career Updates Hub</a> and heralded via portal notification banners. Continued access or utilization of the platform following the publication of revisions constitutes unconditional acceptance of the amended terms.
                                </p>
                                <p>
                                    <strong>16.2 Governing Law and Exclusive Judicial Jurisdiction.</strong> These Terms of Service, their interpretation, and any disputes arising from or connected with the operation of the Platform shall be governed by and construed in accordance with the laws of the Republic of the Philippines. Any legal action, suit, or judicial proceeding arising out of or relating to these terms shall be instituted exclusively in the proper courts of competent jurisdiction located within the <strong>City of Dasmariñas, Province of Cavite</strong>, to the exclusion of all other courts and venues.
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
                                        <div><strong class="text-ink d-block">20-Hour Weekly Maximum</strong> Mandatory university standard enforcing academic priority over work shifts.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-mortarboard text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Academic Standing Threshold</strong> Minimum 2.50 GWA, 12 active units, and good moral character required.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-shield-lock text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Strict Non-Disclosure Duty</strong> Campus office and student records protected under RA 10173 and RA 10175.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-cash-stack text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Semi-Monthly Allowance Release</strong> Endorsed on the 15th and month-end via the University Cashier.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-calendar-event text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Exam Moratorium Protection</strong> Shift adjustments permitted during midterm and final exams with zero penalty.</div>
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
