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
                    '<i class="bi bi-question-circle-fill text-accent me-1"></i> Knowledge Base &amp; Guidelines',
                    'Frequently Asked Questions',
                    'Have questions about student assistant eligibility, work hour limits, stipend payouts, or application processes? Find answers to the 10 most common questions below.'
                );
                ?>

                <!-- 10 FAQs Accordion inside Card Paper -->
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card-paper p-3 p-md-4 mb-5">
                            <div class="accordion accordion-flush" id="faqAccordion">
                                <?php foreach ($faqs as $idx => $item): 
                                    $is_open = ($idx === $open_index);
                                    $q_num = $idx + 1;
                                    $faq_id = $item['id'] ?? ('faq-' . $q_num);
                                ?>
                                    <div class="accordion-item bg-transparent border-0 mb-3" id="faq-<?= $q_num ?>" style="scroll-margin-top: 100px;">
                                        <?php if (!empty($item['id'])): ?>
                                            <span id="<?= htmlspecialchars($item['id']) ?>" style="display: block; position: relative; top: -100px; visibility: hidden;"></span>
                                        <?php endif; ?>
                                        <h2 class="accordion-header" id="heading<?= $idx ?>">
                                            <button 
                                                class="accordion-button <?= $is_open ? '' : 'collapsed' ?> card-paper card-hover p-3 d-flex gap-3 align-items-center" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?= $idx ?>" 
                                                aria-expanded="<?= $is_open ? 'true' : 'false' ?>" 
                                                aria-controls="collapse<?= $idx ?>"
                                            >
                                                <span class="pill-badge pill-badge-ink flex-shrink-0" style="font-size: 11px; padding: 0.25rem 0.65rem;">
                                                    Q<?= $q_num ?>
                                                </span>
                                                <span class="fw-bold text-ink fs-6 text-start flex-grow-1">
                                                    <?= htmlspecialchars($item['q']) ?>
                                                </span>
                                            </button>
                                        </h2>
                                        <div 
                                            id="collapse<?= $idx ?>" 
                                            class="accordion-collapse collapse <?= $is_open ? 'show' : '' ?>" 
                                            aria-labelledby="heading<?= $idx ?>" 
                                            data-bs-parent="#faqAccordion"
                                        >
                                            <div class="accordion-body px-4 py-3 text-muted-custom bg-surface rounded-3 mt-1 border border-line">
                                                <?php if (!empty($item['category'])): ?>
                                                    <div class="mb-2">
                                                        <span class="pill-badge pill-badge-ink small" style="font-size: 11px;">
                                                            <i class="bi bi-tag-fill me-1 text-accent"></i> <?= htmlspecialchars($item['category']) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <p class="mb-0 fs-6 lh-base text-ink">
                                                    <?= $item['a'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Bottom Help CTA -->
                        <div class="card-paper bg-cream p-4 text-center">
                            <h3 class="card-paper-title mb-2">Still Have Questions?</h3>
                            <p class="text-muted-custom small mb-4">
                                Reach out to the Student Affairs &amp; Career Services Office or connect with our student development team.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="student/jobs.php" class="btn-pill">
                                    <i class="bi bi-search"></i> Explore Campus Vacancies
                                </a>
                                <a href="about-us.php" class="btn-pill-outline">
                                    <i class="bi bi-people"></i> Meet The Developers
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
