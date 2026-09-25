<?php
declare(strict_types=1);

/**
 * Campus Job Posting System - Institutional System Checks & Schedule Heuristics
 * 
 * Houses deterministic business logic, schedule calculations, verification
 * checklist evaluations, and department analytics.
 * 
 * Separated from ML decision services to keep genuine AI distinct from
 * standard procedural calculations.
 */

if (!defined('SYS_STALLED_APPS_MIN')) {
    define('SYS_STALLED_APPS_MIN', 5);
}
if (!defined('SYS_TRIAGE_MAX_ITEMS')) {
    define('SYS_TRIAGE_MAX_ITEMS', 8);
}
if (!defined('SYS_REASON_MIN_CHARS')) {
    define('SYS_REASON_MIN_CHARS', 10);
}

/**
 * Compute candidate schedule availability summary and compatibility tier.
 * Purely deterministic arithmetic over declared Mon–Sat shift matrix slots.
 *
 * @param array $application Target applicant record
 * @param array|null $job Target job requisition (optional)
 * @return array Schedule summary containing score, tier, label, badge styling, and summary
 */
function get_schedule_summary(array $application, ?array $job = null): array {
    $raw_avail = $application['availability'] ?? [];
    $slots = function_exists('normalize_availability_slots')
        ? normalize_availability_slots($raw_avail)
        : (is_array($raw_avail) ? $raw_avail : []);

    $slot_count = count($slots);
    $hours = $slot_count * 4;

    if ($slot_count === 0) {
        $tier = 'conflict';
        $status_class = 'danger';
        $badge_bg = 'bg-danger-subtle text-danger border-danger-subtle';
        $label = 'No Shift Input';
        $hours_desc = '0 hrs/wk';
        $summary = 'High Schedule Risk';
        $score = 10;
        $icon = 'bi-exclamation-octagon-fill';
        $note = 'No availability slots provided';
    } elseif ($slot_count <= 2) {
        $tier = 'limited';
        $status_class = 'warning';
        $badge_bg = 'bg-warning-subtle text-warning border-warning-subtle';
        $label = "Limited ({$hours}h)";
        $hours_desc = "{$slot_count} slots ({$hours}h/wk)";
        $summary = "Limited Availability ({$hours}h/wk)";
        $score = 40 + ($slot_count * 10);
        $icon = 'bi-clock-history';
        $note = "{$slot_count} shift slots (~{$hours} hrs/wk)";
    } elseif ($slot_count <= 4) {
        $tier = 'moderate';
        $status_class = 'success';
        $badge_bg = 'bg-success-subtle text-success border-success-subtle';
        $label = "Moderate ({$hours}h)";
        $hours_desc = "{$slot_count} slots ({$hours}h/wk)";
        $summary = "Moderate Availability ({$hours}h/wk)";
        $score = 70 + (($slot_count - 2) * 5);
        $icon = 'bi-calendar-check';
        $note = "{$slot_count} shift slots (~{$hours} hrs/wk)";
    } else {
        $tier = 'optimal';
        $status_class = 'primary';
        $badge_bg = 'bg-primary-subtle text-primary border-primary-subtle';
        $label = "Strong Fit ({$hours}h+)";
        $hours_desc = "{$slot_count} slots ({$hours}h/wk)";
        $summary = "Open Schedule ({$hours}h+/wk)";
        $score = 90 + min(10, ($slot_count - 4) * 2);
        $icon = 'bi-shield-check';
        $note = "{$slot_count} shift slots (~{$hours} hrs/wk)";
    }

    return [
        'tier'          => $tier,
        'status_class'  => $status_class,
        'badge_bg'      => $badge_bg,
        'label'         => $label,
        'hours_desc'    => $hours_desc,
        'summary'       => $summary,
        'slot_count'    => $slot_count,
        'hours'         => $hours,
        'score'         => $score,
        'icon'          => $icon,
        'advisory_note' => $note,
    ];
}

/**
 * Backward compatibility alias for candidate schedule evaluation
 */
function laya_get_candidate_fit(array $application, ?array $job = null): array {
    $res = get_schedule_summary($application, $job);
    $res['program_fit'] = 'Candidate Profile Evaluated';
    $app_id = $application['id'] ?? 0;
    if ($app_id && !empty($_SESSION['laya_guidance'][$app_id]['qualification_assessment']['summary'])) {
        $res['program_fit'] = $_SESSION['laya_guidance'][$app_id]['qualification_assessment']['summary'];
    }
    $res['has_laya_dossier'] = !empty($_SESSION['laya_guidance'][$app_id]);
    return $res;
}

/**
 * Schedule overlap and workload calculation for a student viewing a job requisition.
 * Pure + deterministic. Read-only.
 *
 * @param array $student Viewing student record
 * @param array $job Target requisition record
 * @param int $applicant_count Live applicant count
 * @return array Schedule statistics and contextual indicators
 */
function get_student_schedule_fit(array $student, array $job, int $applicant_count = 0): array {
    $raw_avail = $student['availability'] ?? [];
    $slots = function_exists('normalize_availability_slots')
        ? normalize_availability_slots($raw_avail)
        : (is_array($raw_avail) ? array_values($raw_avail) : []);

    $job_id = (int)($job['id'] ?? 0);
    $is_guest = empty($student['id']);
    $applicants = max(0, $applicant_count);

    $slot_count = count($slots);
    $hours = $slot_count * 4;
    $overlap_pct = min(100, (int)round(($hours / 20) * 100));

    $evening_count = 0;
    foreach ($slots as $s) {
        if (stripos((string)$s, 'evening') !== false) {
            $evening_count++;
        }
    }

    if ($slot_count === 0) {
        $tier = 'conflict';
        $score = 10;
        $badge_bg = 'bg-danger-subtle text-danger border-danger-subtle';
        $icon = 'bi-exclamation-octagon-fill';
        $label = 'Add availability to see fit';
        $summary = 'No shift input declared';
    } elseif ($slot_count <= 2) {
        $tier = 'limited';
        $score = 40 + ($slot_count * 10);
        $badge_bg = 'bg-warning-subtle text-warning border-warning-subtle';
        $icon = 'bi-clock-history';
        $label = "Limited ({$hours}h)";
        $summary = "Limited schedule — ~{$hours} hrs/wk available (20h cap)";
    } elseif ($slot_count <= 4) {
        $tier = 'moderate';
        $score = 70 + (($slot_count - 2) * 5);
        $badge_bg = 'bg-success-subtle text-success border-success-subtle';
        $icon = 'bi-calendar-check';
        $label = "Moderate ({$hours}h)";
        $summary = "Moderate schedule — ~{$hours} hrs/wk available";
    } else {
        $tier = 'optimal';
        $score = 90 + min(10, ($slot_count - 4) * 2);
        $badge_bg = 'bg-primary-subtle text-primary border-primary-subtle';
        $icon = 'bi-shield-check';
        $label = "Strong fit ({$hours}h)";
        $summary = "Open schedule — ~{$hours} hrs/wk available";
    }

    $slots_total = max(1, (int)($job['slots_total'] ?? $job['vacancies'] ?? 1));
    $slots_filled = (int)($job['slots_filled'] ?? 0);
    $demand_ratio = $applicants / $slots_total;
    if ($applicants === 0 || $demand_ratio < 1.0) {
        $competition = 'low';
    } elseif ($demand_ratio < 2.0) {
        $competition = 'medium';
    } else {
        $competition = 'high';
    }
    $demand_desc = "{$applicants} applicant(s) for {$slots_total} slot(s)";

    $days_left = null;
    $deadline_risk = 'low';
    if (!empty($job['deadline'])) {
        $diff = strtotime((string)$job['deadline']) - strtotime(date('Y-m-d'));
        $days_left = (int)floor($diff / 86400);
        $deadline_risk = ($days_left <= 7) ? 'high' : 'low';
    }

    $workload_warning = null;
    if ($hours > 20) {
        $workload_warning = 'Availability exceeds the 20 hrs/week academic cap — prioritize coursework.';
    } elseif ($evening_count > 0 && $slot_count <= 2) {
        $workload_warning = 'Evening-only availability may limit on-campus scheduling options.';
    }

    return [
        'tier'             => $tier,
        'score'            => $score,
        'badge_bg'         => $badge_bg,
        'icon'             => $icon,
        'label'            => $label,
        'summary'          => $summary,
        'slot_count'       => $slot_count,
        'hours'            => $hours,
        'overlap_pct'      => $overlap_pct,
        'evening_count'    => $evening_count,
        'competition'      => $competition,
        'applicants'       => $applicants,
        'demand_desc'      => $demand_desc,
        'slots_filled'     => $slots_filled,
        'slots_total'      => $slots_total,
        'days_left'        => $days_left,
        'deadline_risk'    => $deadline_risk,
        'workload_warning' => $workload_warning,
        'is_guest'         => $is_guest,
        'choices'          => [
            ['label' => 'Browse similar openings', 'href' => 'jobs.php'],
            ['label' => 'Update availability in Settings', 'href' => '../settings.php'],
        ],
    ];
}

/**
 * Backward compatibility alias for student schedule fit
 */
function laya_job_fit_for_student(array $student, array $job, int $applicant_count = 0): array {
    return get_student_schedule_fit($student, $job, $applicant_count);
}

/**
 * Verification queue triage scoring document completeness.
 * Pure deterministic field checking.
 *
 * @param array $pending_users Pending user records
 * @param array $pending_profile_requests Pending student profile requests
 * @return array Triage summary with per-item status and checklist
 */
function get_verification_triage(array $pending_users, array $pending_profile_requests): array {
    $items = [];

    foreach ($pending_users as $u) {
        $role = (string)($u['role'] ?? '');
        $name = (string)($u['name'] ?? 'Account');
        $flags = [];
        $passed = 0;
        $total = 0;

        if ($role === 'employer') {
            $permit = $u['business_permit'] ?? $u['permit_file'] ?? '';
            $total++;
            if (!empty($permit)) {
                $passed++;
            } else {
                $flags[] = 'Business permit / MOA document missing.';
            }
            $total++;
            $accred = trim((string)($u['accreditation_number'] ?? ''));
            if ($accred !== '' && strcasecmp($accred, 'PENDING-VERIFICATION') !== 0) {
                $passed++;
            } else {
                $flags[] = 'Accreditation number missing or placeholder.';
            }
            $total++;
            if (trim((string)($u['organization_name'] ?? '')) !== '') {
                $passed++;
            } else {
                $flags[] = 'Organization name empty.';
            }
            $total++;
            if (trim((string)($u['contact_person'] ?? '')) !== '') {
                $passed++;
            } else {
                $flags[] = 'Contact person empty.';
            }
            $kind_label = 'Partner / office account';
            $href = 'users.php?ver_status=pending_approval';
        } else {
            $proof = $u['registration_proof'] ?? $u['proof_file'] ?? '';
            $total++;
            if (!empty($proof)) {
                $passed++;
            } else {
                $flags[] = 'COR / Student ID proof missing.';
            }
            $total++;
            if (trim((string)($u['student_id'] ?? '')) !== '') {
                $passed++;
            } else {
                $flags[] = 'Student ID number empty.';
            }
            $total++;
            if (trim((string)($u['course'] ?? '')) !== '') {
                $passed++;
            } else {
                $flags[] = 'Degree program empty.';
            }
            $kind_label = 'Student account';
            $href = 'users.php?ver_status=pending_approval';
        }

        if (trim((string)($u['rejection_reason'] ?? '')) !== '') {
            $flags[] = 'Previously returned for revision — compare against prior notes.';
        }

        $score = $total > 0 ? (int)round(($passed / $total) * 100) : 0;
        $tier = ($score === 100) ? 'ready' : ((count(array_filter($flags, fn($f) => str_contains($f, 'missing'))) > 0) ? 'blocked' : 'review');

        $items[] = [
            'ref' => 'user-' . (int)($u['id'] ?? 0),
            'name' => $name,
            'kind' => $role,
            'kind_label' => $kind_label,
            'score' => $score,
            'tier' => $tier,
            'flags' => $flags,
            'choices' => [
                ['label' => 'Review in queue', 'href' => $href],
            ],
        ];
    }

    foreach ($pending_profile_requests as $req) {
        $flags = [];
        $passed = 0;
        $total = 2;
        $has_proof = !empty($req['proof_file'] ?? '');
        if ($has_proof) {
            $passed++;
        } else {
            $flags[] = 'COR / ID proof file missing.';
        }
        if (strlen(trim((string)($req['reason'] ?? ''))) >= SYS_REASON_MIN_CHARS) {
            $passed++;
        } else {
            $flags[] = 'Reason statement very short.';
        }
        $score = (int)round(($passed / $total) * 100);
        $tier = ($score === 100) ? 'ready' : (!$has_proof ? 'blocked' : 'review');
        $items[] = [
            'ref' => 'req-' . (int)($req['id'] ?? 0),
            'name' => 'Profile change request #' . (int)($req['id'] ?? 0),
            'kind' => 'profile_request',
            'kind_label' => 'Student profile change',
            'score' => $score,
            'tier' => $tier,
            'flags' => $flags,
            'choices' => [
                ['label' => 'Open requests', 'href' => '#student-requests-section'],
            ],
        ];
    }

    usort($items, function ($a, $b) {
        $order = ['blocked' => 0, 'review' => 1, 'ready' => 2];
        $diff = ($order[$a['tier']] ?? 1) <=> ($order[$b['tier']] ?? 1);
        if ($diff !== 0) return $diff;
        return ($a['score'] ?? 0) <=> ($b['score'] ?? 0);
    });

    $total_count = count($items);
    $blocked = count(array_filter($items, fn($i) => ($i['tier'] ?? '') === 'blocked'));
    $summary = $total_count === 0
        ? 'Verification queues are clear.'
        : "{$total_count} pending item(s), {$blocked} missing documents.";

    return [
        'items' => array_slice($items, 0, SYS_TRIAGE_MAX_ITEMS),
        'item_count' => $total_count,
        'blocked_count' => $blocked,
        'summary' => $summary,
    ];
}

/**
 * Backward compatibility alias for verification triage
 */
function laya_verification_triage(array $pending_users, array $pending_profile_requests): array {
    return get_verification_triage($pending_users, $pending_profile_requests);
}

/**
 * Quota and conversion analytics over department rollups.
 * Pure mathematical aggregation.
 *
 * @param array $departments Department rollups
 * @param int $total_jobs Total requisitions
 * @param int $total_apps Total applications
 * @param int $total_hired Total hires
 * @param int $total_interviews Total interviews
 * @return array Department summary analytics
 */
function get_quota_narrative(array $departments, int $total_jobs, int $total_apps, int $total_hired, int $total_interviews): array {
    $funnel = [
        'applied' => $total_apps,
        'interview_rate' => $total_apps > 0 ? (int)round(($total_interviews / $total_apps) * 100) : 0,
        'hire_rate' => $total_apps > 0 ? (int)round(($total_hired / $total_apps) * 100) : 0,
    ];

    $notes = [];
    foreach ($departments as $dept_name => $stats) {
        $jobs = (int)($stats['jobs'] ?? 0);
        $apps = (int)($stats['apps'] ?? 0);
        $hired = (int)($stats['hired'] ?? 0);
        $quota = max(1, (int)($stats['quota'] ?? 1));
        $fill_pct = (int)round(($hired / $quota) * 100);
        $conversion = $apps > 0 ? (int)round(($hired / $apps) * 100) : 0;
        $flags = [];

        if ($hired > $quota) {
            $flags[] = 'Quota exceeded — review headcount before new postings.';
        }
        if ($hired === 0 && $apps >= SYS_STALLED_APPS_MIN) {
            $flags[] = "{$apps} applicants yet zero hires — pipeline may be stalled in review.";
        }
        if ($jobs === 0 && $apps === 0) {
            $flags[] = 'Dormant this term — no postings, no applicants.';
        }
        if ($hired <= $quota && $fill_pct >= 90) {
            $flags[] = "Quota {$fill_pct}% filled — plan next-term headcount early.";
        }
        if ($apps >= SYS_STALLED_APPS_MIN && $conversion > 0 && $conversion < 25) {
            $flags[] = "Low applicant conversion ({$conversion}%) — review shortlisting.";
        }

        $notes[] = [
            'dept' => (string)$dept_name,
            'fill_pct' => $fill_pct,
            'conversion' => $conversion,
            'flags' => $flags,
        ];
    }

    usort($notes, function ($a, $b) {
        $diff = count($b['flags']) <=> count($a['flags']);
        if ($diff !== 0) return $diff;
        return ($a['fill_pct'] ?? 0) <=> ($b['fill_pct'] ?? 0);
    });

    $flagged = count(array_filter($notes, fn($n) => count($n['flags']) > 0));
    $summary = $flagged === 0
        ? 'All departments track within quota with healthy conversion.'
        : "{$flagged} department(s) flagged for review.";

    return ['funnel' => $funnel, 'notes' => $notes, 'note_count' => $flagged, 'summary' => $summary];
}

/**
 * Backward compatibility alias for quota narrative
 */
function laya_quota_narrative(array $departments, int $total_jobs, int $total_apps, int $total_hired, int $total_interviews): array {
    return get_quota_narrative($departments, $total_jobs, $total_apps, $total_hired, $total_interviews);
}
