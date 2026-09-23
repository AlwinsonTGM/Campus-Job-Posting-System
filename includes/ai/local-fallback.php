<?php
declare(strict_types=1);

/**
 * AI Config — local offline guided-mode replies
 * Extracted from includes/ai-config.php (verbatim function moves)
 */
function get_curated_local_reply(array $messages, string $model = 'nvidia/nemotron-3.5-lightning-30b-a3b', ?string $notice = null, string $user_role = 'guest'): array {
    $user_messages = [];
    foreach ($messages as $m) {
        if (($m['role'] ?? '') === 'user' && !empty($m['content'])) {
            $user_messages[] = strtolower(trim($m['content']));
        }
    }

    $last_user_msg = end($user_messages) ?: '';
    $prev_user_msg = (count($user_messages) >= 2) ? $user_messages[count($user_messages) - 2] : '';

    // Check if the current message is a follow-up or referencing the previous question
    $is_followup = preg_match('/\b(that question|answer that|can you answer|could you answer|tell me|where|how|what about that|how about)\b/i', $last_user_msg)
        && strlen($last_user_msg) < 55;

    // Search query combines last message and previous message if follow-up
    $query = $is_followup && !empty($prev_user_msg) ? ($prev_user_msg . ' ' . $last_user_msg) : $last_user_msg;

    // 1. Personal Details, Profile, Name, Student ID, COR Proof & Verification
    if (preg_match('/\b(personal details?|change details?|update details?|my details?|account details?|profile|change name|edit name|student ids?|cor|certificate of registration|change course|change department|change year|academic records?|edit profile|update profile|request record update|proofs?)\b/i', $query)) {
        if ($user_role === 'employer') {
            $reply = "**UPDATING EMPLOYER PROFILE:**\n\n" .
                     "1. Go to **[Account Settings](settings.php)**.\n" .
                     "2. Update your **Representative Name**, **Office Location**, and **Contact Phone Number**.\n" .
                     "3. Click **Save Profile Changes** to apply immediately.";
        } else {
            $reply = "**HOW TO CHANGE YOUR PERSONAL DETAILS & PROCESS:**\n\n" .
                     "1. Go to **[Account Settings](settings.php)** from the top navigation.\n\n" .
                     "2. **Direct Updates (No approval needed):**\n" .
                     "• **Contact Phone Number** and **Weekly Shift Availability** can be edited directly.\n" .
                     "• Click **Save Profile Changes** to save immediately.\n\n" .
                     "3. **Official Student Records (Requires verification):**\n" .
                     "• To protect academic integrity, changes to your *Full Name*, *Student ID*, *Institute*, *Degree Program*, or *Year Level* require admin review.\n" .
                     "• Click the **Request Record Update** button on the Settings page.\n" .
                     "• Enter your corrections, state your reason, and attach an official **Certificate of Registration (COR)** or **Student ID**.\n" .
                     "• The University Registrar / Admin will verify your documents and apply the updates.";
        }
    }
    // 2. Settings, Password, Security
    elseif (preg_match('/\b(passwords?|change passwords?|security|reset passwords?|credentials?|account settings?)\b/i', $query)) {
        $reply = "**UPDATING YOUR PASSWORD & SECURITY:**\n\n" .
                 "1. Open **[Account Settings](settings.php)**.\n" .
                 "2. Scroll down to the **Change Password & Security** section.\n" .
                 "3. Enter your **Current Password** to verify identity.\n" .
                 "4. Enter your **New Password** (minimum 8 characters) and confirm it.\n" .
                 "5. Click **Update Password** to secure your account.";
    }
    // 3. Application Tracking & Status
    elseif (preg_match('/\b(my applications?|application status|track(ing)?|shortlists?|hired|review stages?|applicants? status|my status|interview results?)\b/i', $query)) {
        $reply = "**TRACKING YOUR APPLICATIONS:**\n\n" .
                 "1. Open **[My Applications](applications.php)**.\n" .
                 "2. View all your submitted student assistantship applications.\n" .
                 "3. Track your real-time stage: **Applied** ➔ **Reviewing** ➔ **Shortlisted** ➔ **Accepted / Hired** (or Declined).\n" .
                 "4. View supervisor notes, interview schedules, and feedback directly on your application card.";
    }
    // 4. How to Apply, Vacancies, Job Search
    elseif (preg_match('/\b(apply|how to apply|vacanc(y|ies)|find jobs?|browse jobs?|search jobs?|open roles?|job lists?|positions?)\b/i', $query)) {
        $reply = "**HOW TO APPLY FOR ASSISTANTSHIPS:**\n\n" .
                 "1. Head to **[Explore Vacancies](jobs.php)**.\n" .
                 "2. Filter by department, pay rate, or work schedule (roles are strictly &le;20 hrs/week to keep your grades first!).\n" .
                 "3. Click **Apply Now** on your desired role.\n" .
                 "4. Attach your student resume and submit. You can track your status under **[My Applications](applications.php)**.";
    }
    // 5. Shift Scheduling & Availability Matrix
    elseif (preg_match('/\b(shifts?|schedules?|availabilit(y|ies)|matrix|work-study|free times?|class schedules?|hours? per week|20 hours?|20 hrs?)\b/i', $query)) {
        $reply = "**WORK-STUDY SHIFTS & AVAILABILITY:**\n\n" .
                 "1. Campus assistantships are structured strictly around your lecture blocks (&le;20 hrs/week).\n" .
                 "2. To set when you are free to work, visit **[Account Settings](settings.php)**.\n" .
                 "3. Check your lecture-free periods in the **Weekly Shift Availability** matrix (Morning/Afternoon across Mon–Fri).\n" .
                 "4. Click **Save Profile Changes** so campus offices know your exact open timeslots.";
    }
    // 6. Campus Offices & Employers
    elseif (preg_match('/\b(employers?|offices?|post jobs?|post vacanc(y|ies)|hire|supervisors?|department postings?)\b/i', $query)) {
        $reply = "**FOR CAMPUS DEPARTMENTS & EMPLOYERS:**\n\n" .
                 "1. Department heads and supervisors can access the **[Employer Portal](employer/dashboard.php)**.\n" .
                 "2. Go to **[Post a Vacancy](employer/post-job.php)** to list assistantships with duties and stipend rates.\n" .
                 "3. Review student candidates, inspect weekly shift availability, and assign student workers.";
    }
    // 7. Portal Navigation & Site Map
    elseif (preg_match('/\b(navigate|navigation|where is|where can i|pages|site maps?|how to get to|website guides?)\b/i', $query)) {
        $reply = "**CAMPUS PORTAL QUICK DIRECTORY:**\n\n" .
                 "• **[Explore Vacancies](jobs.php)** — Search and apply for on-campus student assistantships.\n" .
                 "• **[My Applications](applications.php)** — Real-time tracking of submitted applications.\n" .
                 "• **[Account Settings](settings.php)** — Update contact phone, availability matrix, password, or request official record changes.\n" .
                 "• **[FAQs & Guidelines](faqs.php)** — Assistantship policies, work-hour limits, and stipend info.\n" .
                 "• **[Career Dispatches](updates.php)** — Campus office news and university announcements.";
    }
    // 8. Resume & CV Tips
    elseif (preg_match('/\b(resume|cv|curriculum vitae)\b/i', $query)) {
        $reply = "**RESUME PRO-TIP:**\n\n" .
                 "Keep your student resume concise (1 page)! Highlight class projects, software proficiencies, and your shift availability clearly upfront. You can attach your resume directly when applying under **[Explore Vacancies](jobs.php)**.";
    }
    // 9. Career Boost / Motivation
    elseif (preg_match('/\b(boost|motivat|confiden|nervous|scared|anxious|can i do it|inspire)\b/i', $query)) {
        $reply = "**CAREER BOOST:**\n\n" .
                 "You are capable of more than you realize! Every lecture, project, and club activity has built your problem-solving grit. Campus offices prioritize proactive students who are eager to learn. Head over to **[Explore Vacancies](jobs.php)** and submit your application with pride.";
    }
    // 10. Mock Interview
    elseif (preg_match('/\b(interview|grill|star method|mock|practice question)\b/i', $query)) {
        $reply = "**MOCK INTERVIEW:**\n\n" .
                 "*'Tell me about a time when you had to balance a tight midterm deadline with an unexpected office rush.'*\n\n" .
                 "Structure your answer using **STAR**:\n" .
                 "• **S**ituation: The academic and office situation.\n" .
                 "• **T**ask: What needed to get done.\n" .
                 "• **A**ction: The proactive steps you took.\n" .
                 "• **R**esult: The positive result achieved.";
    }
    // 11. Anti-Spam
    elseif (preg_match('/\b(spam|limit|rate)\b/i', $query)) {
        $reply = "**ANTI-SPAM ACTIVE:** To keep AI assistant services fast, fair, and free for all students, questions are limited to 10 queries per minute per visitor. Thank you for keeping it friendly.";
    }
    // 12. Smart Navigation Fallback Prompt
    else {
        $reply = "**CAMPUS AI:** I am your dedicated FAQ, Help & Navigation assistant for the Campus Job Posting System.\n\n" .
                 "Here are quick ways I can help you:\n" .
                 "• *'How can I change my personal details and what is the process?'*\n" .
                 "• *'How do I apply for open assistantship roles?'*\n" .
                 "• *'Where do I update my weekly shift availability?'*\n" .
                 "• *'How do I track my submitted applications?'*\n\n" .
                 "You can also jump directly to **[Explore Vacancies](jobs.php)** or **[Account Settings](settings.php)**!";
    }

    return [
        'success' => true,
        'reply' => $reply,
        'model' => $model . ' (Local Guided Mode)',
        'original_model' => $model,
        'latency_ms' => 45,
        'is_fallback' => true,
        'is_model_fallback' => false,
        'is_local_fallback' => true,
        'notice' => $notice,
        'fallback_notice' => $notice,
        'error' => null
    ];
}
