<?php
declare(strict_types=1);

/**
 * AI Config — local offline guided-mode replies
 * Extracted from includes/ai-config.php (verbatim function moves)
 */

function get_local_curated_catalog(string $user_role): array {
    return [
        [
            'pattern' => '/\b(personal details?|change details?|update details?|my details?|account details?|profile|change name|edit name|student ids?|cor|certificate of registration|change course|change department|change year|academic records?|edit profile|update profile|request record update|proofs?)\b/i',
            'reply' => ($user_role === 'employer')
                ? "**UPDATING EMPLOYER PROFILE:**\n\n1. Go to **[Account Settings](settings.php)**.\n2. Update your **Representative Name**, **Office Location**, and **Contact Phone Number**.\n3. Click **Save Profile Changes** to apply immediately."
                : "**HOW TO CHANGE YOUR PERSONAL DETAILS & PROCESS:**\n\n1. Go to **[Account Settings](settings.php)** from the top navigation.\n\n2. **Direct Updates (No approval needed):**\n• **Contact Phone Number** and **Weekly Shift Availability** can be edited directly.\n• Click **Save Profile Changes** to save immediately.\n\n3. **Official Student Records (Requires verification):**\n• To protect academic integrity, changes to your *Full Name*, *Student ID*, *Institute*, *Degree Program*, or *Year Level* require admin review.\n• Click the **Request Record Update** button on the Settings page.\n• Enter your corrections, state your reason, and attach an official **Certificate of Registration (COR)** or **Student ID**.\n• The University Registrar / Admin will verify your documents and apply the updates."
        ],
        [
            'pattern' => '/\b(passwords?|change passwords?|security|reset passwords?|credentials?|account settings?)\b/i',
            'reply' => "**UPDATING YOUR PASSWORD & SECURITY:**\n\n1. Open **[Account Settings](settings.php)**.\n2. Scroll down to the **Change Password & Security** section.\n3. Enter your **Current Password** to verify identity.\n4. Enter your **New Password** (minimum 8 characters) and confirm it.\n5. Click **Update Password** to secure your account."
        ],
        [
            'pattern' => '/\b(my applications?|application status|track(ing)?|shortlists?|hired|review stages?|applicants? status|my status|interview results?)\b/i',
            'reply' => "**TRACKING YOUR APPLICATIONS:**\n\n1. Open **[My Applications](applications.php)**.\n2. View all your submitted student assistantship applications.\n3. Track your real-time stage: **Applied** ➔ **Reviewing** ➔ **Shortlisted** ➔ **Accepted / Hired** (or Declined).\n4. View supervisor notes, interview schedules, and feedback directly on your application card."
        ],
        [
            'pattern' => '/\b(apply|how to apply|vacanc(y|ies)|find jobs?|browse jobs?|search jobs?|open roles?|job lists?|positions?)\b/i',
            'reply' => "**HOW TO APPLY FOR ASSISTANTSHIPS:**\n\n1. Head to **[Explore Vacancies](jobs.php)**.\n2. Filter by department, pay rate, or work schedule (roles are strictly &le;20 hrs/week to keep your grades first!).\n3. Click **Apply Now** on your desired role.\n4. Attach your student resume and submit. You can track your status under **[My Applications](applications.php)**."
        ],
        [
            'pattern' => '/\b(shifts?|schedules?|availabilit(y|ies)|matrix|work-study|free times?|class schedules?|hours? per week|20 hours?|20 hrs?)\b/i',
            'reply' => "**WORK-STUDY SHIFTS & AVAILABILITY:**\n\n1. Campus assistantships are structured strictly around your lecture blocks (&le;20 hrs/week).\n2. To set when you are free to work, visit **[Account Settings](settings.php)**.\n3. Check your lecture-free periods in the **Weekly Shift Availability** matrix (Morning/Afternoon across Mon–Fri).\n4. Click **Save Profile Changes** so campus offices know your exact open timeslots."
        ],
        [
            'pattern' => '/\b(employers?|offices?|post jobs?|post vacanc(y|ies)|hire|supervisors?|department postings?)\b/i',
            'reply' => "**FOR CAMPUS DEPARTMENTS & EMPLOYERS:**\n\n1. Department heads and supervisors can access the **[Employer Portal](employer/dashboard.php)**.\n2. Go to **[Post a Vacancy](employer/post-job.php)** to list assistantships with duties and stipend rates.\n3. Review student candidates, inspect weekly shift availability, and assign student workers."
        ],
        [
            'pattern' => '/\b(navigate|navigation|where is|where can i|pages|site maps?|how to get to|website guides?)\b/i',
            'reply' => "**CAMPUS PORTAL QUICK DIRECTORY:**\n\n• **[Explore Vacancies](jobs.php)** — Search and apply for on-campus student assistantships.\n• **[My Applications](applications.php)** — Real-time tracking of submitted applications.\n• **[Account Settings](settings.php)** — Update contact phone, availability matrix, password, or request official record changes.\n• **[FAQs & Guidelines](faqs.php)** — Assistantship policies, work-hour limits, and stipend info.\n• **[Career Dispatches](updates.php)** — Campus office news and university announcements."
        ],
        [
            'pattern' => '/\b(resume|cv|curriculum vitae)\b/i',
            'reply' => "**RESUME PRO-TIP:**\n\nKeep your student resume concise (1 page)! Highlight class projects, software proficiencies, and your shift availability clearly upfront. You can attach your resume directly when applying under **[Explore Vacancies](jobs.php)**."
        ],
        [
            'pattern' => '/\b(boost|motivat|confiden|nervous|scared|anxious|can i do it|inspire)\b/i',
            'reply' => "**CAREER BOOST:**\n\nYou are capable of more than you realize! Every lecture, project, and club activity has built your problem-solving grit. Campus offices prioritize proactive students who are eager to learn. Head over to **[Explore Vacancies](jobs.php)** and submit your application with pride."
        ],
        [
            'pattern' => '/\b(interview|grill|star method|mock|practice question)\b/i',
            'reply' => "**MOCK INTERVIEW:**\n\n*'Tell me about a time when you had to balance a tight midterm deadline with an unexpected office rush.'*\n\nStructure your answer using **STAR**:\n• **S**ituation: The academic and office situation.\n• **T**ask: What needed to get done.\n• **A**ction: The proactive steps you took.\n• **R**esult: The positive result achieved."
        ],
        [
            'pattern' => '/\b(spam|limit|rate)\b/i',
            'reply' => "**ANTI-SPAM ACTIVE:** To keep AI assistant services fast, fair, and free for all students, questions are limited to 10 queries per minute per visitor. Thank you for keeping it friendly."
        ]
    ];
}

function get_curated_local_reply(array $messages, string $model = 'nvidia/nemotron-3.5-lightning-30b-a3b', ?string $notice = null, string $user_role = 'guest'): array {
    $user_messages = [];
    foreach ($messages as $m) {
        if (($m['role'] ?? '') === 'user' && !empty($m['content'])) {
            $user_messages[] = strtolower(trim((string)$m['content']));
        }
    }

    $last_user_msg = end($user_messages) ?: '';
    $prev_user_msg = (count($user_messages) >= 2) ? $user_messages[count($user_messages) - 2] : '';

    // Check if current message is a follow-up or referencing the previous question
    $is_followup = preg_match('/\b(that question|answer that|can you answer|could you answer|tell me|where|how|what about that|how about)\b/i', $last_user_msg)
        && strlen($last_user_msg) < 55;

    $query = $is_followup && !empty($prev_user_msg) ? ($prev_user_msg . ' ' . $last_user_msg) : $last_user_msg;

    $reply = null;
    foreach (get_local_curated_catalog($user_role) as $entry) {
        if (preg_match($entry['pattern'], $query)) {
            $reply = $entry['reply'];
            break;
        }
    }

    if ($reply === null) {
        $reply = "**CAMPUS AI:** I am your dedicated FAQ, Help & Navigation assistant for the Campus Job Posting System.\n\n" .
                 "Here are quick ways I can help you:\n" .
                 "• *'How can I change my personal details and what is the process?'*\n" .
                 "• *'How do I apply for open assistantship roles?'*\n" .
                 "• *'Where do I update my weekly shift availability?'*\n" .
                 "• *'How do I track my submitted applications?'*\n\n" .
                 "You can also jump directly to **[Explore Vacancies](jobs.php)** or **[Account Settings](settings.php)**!";
    }

    return [
        'success'           => true,
        'reply'             => $reply,
        'model'             => $model . ' (Local Guided Mode)',
        'original_model'    => $model,
        'latency_ms'        => 45,
        'is_fallback'       => true,
        'is_model_fallback' => false,
        'is_local_fallback' => true,
        'notice'            => $notice,
        'fallback_notice'   => $notice,
        'error'             => null
    ];
}
