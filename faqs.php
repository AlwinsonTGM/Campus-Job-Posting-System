<?php
/**
 * Campus Job Posting System - Frequently Asked Questions (10 Q&As)
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'Frequently Asked Questions (FAQs)';

$faqs = [
    [
        'category' => 'Eligibility & Rules',
        'q' => 'Who is eligible to apply for on-campus jobs?',
        'a' => 'Any currently enrolled undergraduate or graduate student carrying at least 12 academic units, maintaining a General Weighted Average (GWA) of 2.50 or better, and having no disciplinary records is eligible to apply for campus positions.'
    ],
    [
        'category' => 'Work Hours & Academics',
        'q' => 'How many hours per week can a Student Assistant (SA) work?',
        'a' => 'To safeguard your academic studies, Student Assistants are restricted to a maximum of <strong>20 hours per week</strong> during regular school semesters. During official summer/semester breaks, working hours may extend up to 40 hours per week upon office approval.'
    ],
    [
        'category' => 'Applications',
        'q' => 'Can I apply for multiple campus jobs at the same time?',
        'a' => 'Yes, you may submit applications to multiple departments simultaneously to increase your chances. However, once officially hired and contracted by a department, you can only hold <strong>one active on-campus position</strong> per semester.'
    ],
    [
        'category' => 'Applications',
        'q' => 'How do I know if my application was approved or shortlisted for an interview?',
        'a' => 'You can track real-time status updates directly inside your <a href="student/my-applications.php">Student Dashboard &gt; My Applications</a> page. Whenever a supervisor updates your status (e.g. <em>Under Review</em>, <em>Interview Scheduled</em>, or <em>Accepted</em>), your portal dashboard reflects the change instantly.'
    ],
    [
        'category' => 'Requirements',
        'q' => 'What documents are required when submitting an application?',
        'a' => 'Standard application requirements include: (1) An updated Student Resume / CV in PDF format, (2) Current Certificate of Registration (COR) / Study Load showing your class schedule, and (3) A brief Statement of Intent / Cover Letter indicating your available hours.'
    ],
    [
        'category' => 'Payroll & Stipends',
        'q' => 'How and when are student assistant stipends disbursed?',
        'a' => 'Stipends are computed based on approved Daily Time Records (DTR) and disbursed on a semi-monthly (every 15th and 30th) or monthly basis through the University Cashier Office or credited directly to registered student bank/e-wallet accounts.'
    ],
    [
        'category' => 'Employer / Office',
        'q' => 'How can campus departments or offices post new job openings?',
        'a' => 'Authorized department chairs, office supervisors, and laboratory custodians can register with their official university email (`@university.edu.ph`), navigate to the <a href="employer/create-job.php">Employer Dashboard</a>, and fill out the vacancy requisition form.'
    ],
    [
        'category' => 'Account & Security',
        'q' => 'What should I do if I forget my account password?',
        'a' => 'Click on the <a href="forgot-pass.php">Forgot Password</a> link on the sign-in page, enter your registered institutional email address, and submit. You will receive instructions to reset your access credentials.'
    ],
    [
        'category' => 'Work Hours & Academics',
        'q' => 'Can I adjust my work schedule during midterm or final exam weeks?',
        'a' => 'Yes! Campus policy mandates that all hiring departments provide flexible work-shift adjustments during official midterm and final examination periods so student assistants can focus on their exams.'
    ],
    [
        'category' => 'Privacy & Security',
        'q' => 'How is my personal academic and contact data protected?',
        'a' => 'All student profiles, resumes, and contact numbers are managed in strict compliance with Republic Act No. 10173 (Philippine Data Privacy Act of 2012). Your data is accessed only by authorized hiring supervisors and is never shared with third parties.'
    ]
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase mb-2">
                <i class="bi bi-question-diamond-fill"></i> Help Center
            </span>
            <h1 class="fw-bold text-dark mb-3">Frequently Asked Questions</h1>
            <p class="text-muted lead fs-6">
                Have questions about student assistant eligibility, work hour limits, stipend payouts, or application processes? Find answers to the 10 most common questions below.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <!-- Accordion Container -->
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden border" id="faqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?> py-3 px-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                    <div class="d-flex align-items-center gap-3 w-100 me-3">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small flex-shrink-0">
                                            Q<?= $index + 1 ?>
                                        </span>
                                        <span class="text-dark"><?= htmlspecialchars($faq['q']) ?></span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-4 py-3 bg-light text-secondary">
                                    <div class="mb-2">
                                        <span class="badge bg-secondary-subtle text-dark small mb-2">
                                            <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($faq['category']) ?>
                                        </span>
                                    </div>
                                    <p class="mb-0 fs-6 lh-base">
                                        <?= $faq['a'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Still Have Questions Box -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mt-5 text-center bg-white">
                    <div class="stat-icon bg-primary text-white mx-auto mb-3">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Still need assistance with your application?</h5>
                    <p class="text-muted small mb-3">
                        Our student services desk and office administrators are ready to help you during regular campus office hours.
                    </p>
                    <div>
                        <a href="about-us.php" class="btn btn-outline-academic btn-sm px-4 me-2">
                            <i class="bi bi-people me-1"></i> Contact Developers
                        </a>
                        <a href="student/jobs.php" class="btn btn-academic btn-sm px-4">
                            <i class="bi bi-search me-1"></i> Start Browsing Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
