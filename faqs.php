<?php
/**
 * Campus Job Posting System - Frequently Asked Questions (10 Q&As)
 * Archetype F: Help & Accordion (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Frequently Asked Questions (10 FAQs)';

// 10 Verbatim Blueprint Q&As
$faqs = [
    [
        'id' => 'eligibility',
        'category' => 'Eligibility & Rules',
        'q' => 'Who is eligible to apply for on-campus jobs?',
        'a' => 'Any currently enrolled undergraduate or graduate student carrying at least 12 academic units, <strong>maintaining a General Weighted Average (GWA) of 2.50 or better</strong>, and having <strong>no disciplinary records</strong> is eligible to apply for campus positions.'
    ],
    [
        'id' => 'work-limits',
        'category' => 'Work Hours & Academics',
        'q' => 'How many hours per week can a Student Assistant (SA) work?',
        'a' => 'To safeguard your academic studies, Student Assistants are restricted to a maximum of <strong>20 hours per week</strong> during regular school semesters. During official summer/semester breaks, working hours may extend up to 40 hours per week upon office approval.'
    ],
    [
        'id' => 'multiple-jobs',
        'category' => 'Applications',
        'q' => 'Can I apply for multiple campus jobs at the same time?',
        'a' => 'Yes, you may submit applications to multiple departments simultaneously to increase your chances. However, once officially hired and contracted by a department, you can only hold <strong>one active on-campus position</strong> per semester.'
    ],
    [
        'id' => 'track-status',
        'category' => 'Applications',
        'q' => 'How do I know if my application was approved or shortlisted for an interview?',
        'a' => 'You can track real-time status updates directly inside your <a href="student/my-applications.php" class="text-ink fw-bold">Student Dashboard &gt; My Applications</a> page. Whenever a supervisor updates your status (e.g. <em>Under Review</em>, <em>Interview Scheduled</em>, or <em>Accepted</em>), your portal dashboard reflects the change instantly.'
    ],
    [
        'id' => 'required-docs',
        'category' => 'Requirements',
        'q' => 'What documents are required when submitting an application?',
        'a' => 'Standard application requirements include: (1) An updated Student Resume / CV in PDF format, (2) Current Certificate of Registration (COR) / Study Load showing your class schedule, and (3) A brief Statement of Intent / Cover Letter indicating your available hours.'
    ],
    [
        'id' => 'stipend-payroll',
        'category' => 'Payroll & Stipends',
        'q' => 'How and when are student assistant stipends disbursed?',
        'a' => 'Stipends are computed based on approved Daily Time Records (DTR) and disbursed on a semi-monthly (every 15th and 30th) or monthly basis through the University Cashier Office or credited directly to registered student bank/e-wallet accounts.'
    ],
    [
        'id' => 'post-job',
        'category' => 'Employer / Office',
        'q' => 'How can campus departments or offices post new job openings?',
        'a' => 'Authorized institute deans, office supervisors, and laboratory custodians can register with their official KLD email (<code>@kld.edu.ph</code>), navigate to the <a href="employer/create-job.php" class="text-ink fw-bold">Employer Dashboard</a>, and fill out the vacancy requisition form.'
    ],
    [
        'id' => 'forgot-password',
        'category' => 'Account & Security',
        'q' => 'What should I do if I forget my account password?',
        'a' => 'Click on the <a href="forgot-pass.php" class="text-ink fw-bold">Forgot Password</a> link on the sign-in page, enter your registered institutional email address, and submit. You will receive instructions to reset your access credentials.'
    ],
    [
        'id' => 'exam-flexibility',
        'category' => 'Work Hours & Academics',
        'q' => 'Can I adjust my work schedule during midterm or final exam weeks?',
        'a' => 'Yes! Campus policy mandates that all hiring departments provide flexible work-shift adjustments during official midterm and final examination periods so student assistants can focus on their exams.'
    ],
    [
        'id' => 'data-privacy',
        'category' => 'Privacy & Security',
        'q' => 'How is my personal academic and contact data protected?',
        'a' => 'All student profiles, resumes, and contact numbers are managed in strict compliance with Republic Act No. 10173 (Philippine Data Privacy Act of 2012). Your data is accessed only by authorized hiring supervisors and is never shared with third parties.'
    ]
];

// Determine which FAQ is opened using native PHP $_GET parameters
$open_index = 0; // Default to Question 1

$target_faq = $_GET['open'] ?? $_GET['q'] ?? $_GET['topic'] ?? null;
if ($target_faq !== null) {
    $target_faq = trim((string)$target_faq);
    if (is_numeric($target_faq)) {
        $req_idx = (int)$target_faq - 1;
        if (isset($faqs[$req_idx])) {
            $open_index = $req_idx;
        }
    } else {
        $found = false;
        foreach ($faqs as $i => $item) {
            if (isset($item['id']) && $item['id'] === $target_faq) {
                $open_index = $i;
                $found = true;
                break;
            }
        }
        if (!$found && in_array($target_faq, ['work-regulation', 'work-hours', 'work-limits', '20-hour', 'regulation'])) {
            $open_index = 1; // 2nd question: 20-Hour Work Regulations
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <!-- Page Head -->
                <?php
                render_page_head(
                    '',
                    'Frequently Asked Questions',
                    'Everything you need to know about student assistant eligibility, work hour limits, stipend payouts, and departmental application workflows.'
                );
                ?>

                <!-- 10 FAQs Accordion -->
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="accordion accordion-flush mb-5" id="faqAccordion">
                            <?php foreach ($faqs as $idx => $item): 
                                $is_open = ($idx === $open_index);
                                $q_num = $idx + 1;
                                $faq_id = $item['id'] ?? ('faq-' . $q_num);
                            ?>
                                <div class="faq-item-card mb-3 <?= $is_open ? 'is-active-item' : '' ?>" id="faq-<?= $q_num ?>" style="scroll-margin-top: 100px;">
                                    <?php if (!empty($item['id'])): ?>
                                        <span id="<?= htmlspecialchars($item['id']) ?>" style="display: block; position: relative; top: -100px; visibility: hidden;"></span>
                                    <?php endif; ?>
                                    <h2 class="accordion-header m-0" id="heading<?= $idx ?>">
                                        <button 
                                            class="faq-accordion-btn <?= $is_open ? '' : 'collapsed' ?>" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?= $idx ?>" 
                                            aria-expanded="<?= $is_open ? 'true' : 'false' ?>" 
                                            aria-controls="collapse<?= $idx ?>"
                                        >
                                            <span class="faq-q-pill">
                                                Q<?= $q_num ?>
                                            </span>
                                            <span class="faq-q-title">
                                                <?= htmlspecialchars($item['q']) ?>
                                            </span>
                                            <span class="faq-toggle-icon-wrap">
                                                <i class="bi bi-chevron-down"></i>
                                            </span>
                                        </button>
                                    </h2>
                                    <div 
                                        id="collapse<?= $idx ?>" 
                                        class="accordion-collapse collapse <?= $is_open ? 'show' : '' ?>" 
                                        aria-labelledby="heading<?= $idx ?>" 
                                        data-bs-parent="#faqAccordion"
                                    >
                                        <div class="faq-answer-body">
                                            <p class="mb-0">
                                                <?= $item['a'] ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bottom Help CTA (Simple, Clean Card) -->
                        <div class="faq-help-card">
                            <div class="faq-help-icon-box">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <h3 class="h4 fw-bold text-ink mb-2">Still Have Unanswered Questions?</h3>
                            <p class="text-muted-custom small col-lg-8 mx-auto mb-4">
                                Reach out to the Student Affairs &amp; Career Services Office or connect with our student development team.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="student/jobs.php" class="btn-accent-pill">
                                    <i class="bi bi-search"></i> EXPLORE VACANCIES
                                </a>
                                <a href="about-us.php" class="btn-outline-pill">
                                    MEET THE TEAM <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
