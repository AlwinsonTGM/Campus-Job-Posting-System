<?php
declare(strict_types=1);

/**
 * Campus Job Posting System - ApplicationService
 * Deep module encapsulating candidate eligibility, application lifecycle, and applicant search pipeline.
 */

require_once dirname(__DIR__) . '/db.php';

class RequisitionEligibility {
    public function __construct(
        private readonly bool $allowed,
        private readonly ?string $reason = null,
        private readonly string $message = '',
        private readonly ?string $applicationStatus = null,
        private readonly ?array $existingApplication = null
    ) {}

    public function isAllowed(): bool {
        return $this->allowed;
    }

    public function reason(): ?string {
        return $this->reason;
    }

    public function message(): string {
        return $this->message;
    }

    public function applicationStatus(): ?string {
        return $this->applicationStatus;
    }

    public function existingApplication(): ?array {
        return $this->existingApplication;
    }

    public function badge(): array {
        return match ($this->reason) {
            'archived' => [
                'bg'    => 'bg-warning-subtle border-warning text-warning-emphasis',
                'label' => 'Requisition Archived',
                'icon'  => 'bi-archive-fill',
                'desc'  => 'This posting has been archived by the university.'
            ],
            'closed' => [
                'bg'    => 'bg-secondary-subtle border-secondary text-secondary',
                'label' => 'Requisition Closed',
                'icon'  => 'bi-slash-circle',
                'desc'  => 'This office is no longer accepting new submissions.'
            ],
            'deadline_passed' => [
                'bg'    => 'bg-danger-subtle border-danger text-danger',
                'label' => 'Deadline Passed',
                'icon'  => 'bi-clock-history',
                'desc'  => 'The cutoff date for applications has expired.'
            ],
            'slots_filled' => [
                'bg'    => 'bg-warning-subtle border-warning text-warning-emphasis',
                'label' => 'Position Filled',
                'icon'  => 'bi-people-fill',
                'desc'  => 'All approved vacancy slots have been filled.'
            ],
            'already_applied' => [
                'bg'    => 'bg-info-subtle border-info text-info-emphasis',
                'label' => 'Application Active',
                'icon'  => 'bi-check-circle-fill',
                'desc'  => 'You have already applied for this vacancy.'
            ],
            'unverified_student' => [
                'bg'    => 'bg-warning-subtle border-warning text-warning-emphasis',
                'label' => 'Verification Required',
                'icon'  => 'bi-shield-exclamation',
                'desc'  => 'Your student account is awaiting administrative review.'
            ],
            'unauthorized_role' => [
                'bg'    => 'bg-secondary-subtle border-secondary text-secondary',
                'label' => 'Restricted Role',
                'icon'  => 'bi-lock-fill',
                'desc'  => 'Applications are reserved for student accounts.'
            ],
            default => [
                'bg'    => 'bg-success-subtle border-success text-success',
                'label' => 'Open for Applications',
                'icon'  => 'bi-check2-circle',
                'desc'  => 'Eligible for student submission.'
            ]
        };
    }
}

class ApplicationService {
    /**
     * Evaluate candidate eligibility against requisition status, deadline, capacity, and account state.
     */
    public static function checkEligibility(int|array $jobOrId, ?array $user = null): RequisitionEligibility {
        $job = is_array($jobOrId) ? $jobOrId : get_job_by_id($jobOrId);
        if (!$job) {
            return new RequisitionEligibility(
                false,
                'not_found',
                'The requested opportunity could not be found or has been closed.'
            );
        }

        // 0. Enforce Requisition Archive Gating
        if (!empty($job['is_archived'])) {
            return new RequisitionEligibility(
                false,
                'archived',
                'This requisition has been moved to Archive and is not accepting applications.'
            );
        }

        // 1. Enforce Requisition Status Gating
        if (strtolower($job['status'] ?? '') !== 'active') {
            return new RequisitionEligibility(
                false,
                'closed',
                'This requisition has been closed or paused and is not accepting applications.'
            );
        }

        // 2. Enforce Application Deadline Gating
        if (!empty($job['deadline'])) {
            $today = strtotime(date('Y-m-d'));
            $deadline = strtotime($job['deadline']);
            if ($deadline && $deadline < $today) {
                return new RequisitionEligibility(
                    false,
                    'deadline_passed',
                    'The application deadline for this position has passed.'
                );
            }
        }

        // 3. Enforce Vacancy Slot Capacity Gating
        $slotsTotal = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
        $slotsFilled = (int)($job['slots_filled'] ?? 0);
        if ($slotsTotal > 0 && $slotsFilled >= $slotsTotal) {
            return new RequisitionEligibility(
                false,
                'slots_filled',
                'All vacancy slots for this position have been filled.'
            );
        }

        // 4. Authenticated User Validations
        if ($user !== null && !empty($user['id'])) {
            $role = $user['role'] ?? '';
            if ($role !== 'student') {
                $msg = ($role === 'employer')
                    ? 'Access Restricted: Only enrolled students can submit job applications. Employer accounts cannot apply for campus vacancies.'
                    : 'Admin Preview Mode: Applications are reserved for students.';
                return new RequisitionEligibility(false, 'unauthorized_role', $msg);
            }

            // Student Administrative Verification Status
            if (($user['verification_status'] ?? 'verified') !== 'verified') {
                return new RequisitionEligibility(
                    false,
                    'unverified_student',
                    'Account Verification Required: Your student registration is currently awaiting administrative review. The administrator must verify your account before you can submit applications.'
                );
            }

            // Duplicate Submission Check (ignore withdrawn applications)
            $existing = self::getStudentApplication((int)$job['id'], (int)$user['id']);
            if ($existing && strtolower($existing['status'] ?? '') !== 'withdrawn') {
                return new RequisitionEligibility(
                    false,
                    'already_applied',
                    'You have already submitted an application for this requisition.',
                    $existing['status'] ?? 'pending',
                    $existing
                );
            }
        }

        return new RequisitionEligibility(
            true,
            null,
            'Eligible to submit application.'
        );
    }

    /**
     * Fast atomic check to determine if a student has active application for a job vacancy.
     */
    public static function hasStudentApplied(int $jobId, int $studentId): bool {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT 1 FROM `applications` WHERE `job_id` = :job_id AND `student_id` = :student_id AND `status` != 'withdrawn' LIMIT 1");
            $stmt->execute([':job_id' => $jobId, ':student_id' => $studentId]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            $file = DATA_DIR . '/applications.json';
            if (file_exists($file)) {
                $apps = json_decode((string)file_get_contents($file), true) ?? [];
                foreach ($apps as $a) {
                    if ((int)($a['job_id'] ?? 0) === $jobId && (int)($a['student_id'] ?? 0) === $studentId) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    /**
     * Retrieve a student's active application record for a specific job requisition.
     */
    public static function getStudentApplication(int $jobId, int $studentId): ?array {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT * FROM `applications` WHERE `job_id` = :job_id AND `student_id` = :student_id ORDER BY `id` DESC LIMIT 1");
            $stmt->execute([':job_id' => $jobId, ':student_id' => $studentId]);
            $row = $stmt->fetch();
            return $row ? (function_exists('hydrate_application') ? hydrate_application($row) : $row) : null;
        } catch (Exception $e) {
            $file = DATA_DIR . '/applications.json';
            if (file_exists($file)) {
                $apps = json_decode((string)file_get_contents($file), true) ?? [];
                foreach ($apps as $a) {
                    if ((int)($a['job_id'] ?? 0) === $jobId && (int)($a['student_id'] ?? 0) === $studentId) {
                        return $a;
                    }
                }
            }
            return null;
        }
    }

    /**
     * Fast atomic count of total applicants for a job requisition.
     */
    public static function getApplicantCount(int $jobId): int {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `applications` WHERE `job_id` = :job_id");
            $stmt->execute([':job_id' => $jobId]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $file = DATA_DIR . '/applications.json';
            if (file_exists($file)) {
                $apps = json_decode((string)file_get_contents($file), true) ?? [];
                $count = 0;
                foreach ($apps as $a) {
                    if ((int)($a['job_id'] ?? 0) === $jobId) {
                        $count++;
                    }
                }
                return $count;
            }
            return 0;
        }
    }

    /**
     * Query, filter, and rank applications for employer review and administrative auditing.
     */
    public static function search(array $filters = []): array {
        $studentId = $filters['student_id'] ?? null;
        $jobId = $filters['job_id'] ?? null;
        $department = $filters['department'] ?? null;
        $employerId = $filters['employer_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;
        $fitFilter = $filters['fit_tier'] ?? null;
        $rankSort = $filters['rank_sort'] ?? null;

        $results = [];

        try {
            $pdo = get_db_connection();
            $sql = "
                SELECT 
                    a.*,
                    j.`title` AS `job_title`,
                    j.`department` AS `department`,
                    j.`employer_id`,
                    j.`pay_rate`,
                    j.`pay_type`,
                    j.`job_type`,
                    j.`work_setup`,
                    j.`status` AS `job_status`,
                    u.`name` AS `student_name`,
                    u.`email` AS `student_email`,
                    u.`phone`,
                    sp.`student_id` AS `student_number`,
                    sp.`department` AS `student_department`,
                    sp.`course`,
                    sp.`year_level`,
                    sp.`sex`,
                    sp.`birthdate`,
                    sp.`age`
                FROM `applications` a
                INNER JOIN `jobs` j ON a.`job_id` = j.`id`
                INNER JOIN `users` u ON a.`student_id` = u.`id`
                LEFT JOIN `student_profiles` sp ON a.`student_id` = sp.`user_id`
                WHERE 1=1
            ";
            $params = [];

            if ($studentId !== null && $studentId !== '') {
                $sql .= " AND a.`student_id` = :student_id";
                $params[':student_id'] = (int)$studentId;
            }
            if ($jobId) {
                $sql .= " AND a.`job_id` = :job_id";
                $params[':job_id'] = (int)$jobId;
            }
            if ($department) {
                $sql .= " AND j.`department` LIKE :dept";
                $params[':dept'] = '%' . trim((string)$department) . '%';
            }
            if ($employerId !== null) {
                $sql .= " AND j.`employer_id` = :emp_id";
                $params[':emp_id'] = (int)$employerId;
            }

            if (!empty($statusFilter)) {
                $st = strtolower((string)$statusFilter);
                if ($st === 'pending') {
                    $sql .= " AND LOWER(a.`status`) IN ('pending', 'pending review')";
                } elseif ($st === 'review') {
                    $sql .= " AND LOWER(a.`status`) IN ('under_review', 'under review', 'under evaluation')";
                } elseif ($st === 'interview') {
                    $sql .= " AND LOWER(a.`status`) IN ('interview_scheduled', 'interview scheduled')";
                } elseif ($st === 'accepted') {
                    $sql .= " AND LOWER(a.`status`) IN ('accepted', 'accepted / hired')";
                } elseif ($st === 'declined') {
                    $sql .= " AND LOWER(a.`status`) IN ('declined', 'rejected', 'declined / position filled')";
                } else {
                    $sql .= " AND LOWER(a.`status`) = :status";
                    $params[':status'] = $st;
                }
            }

            if (!empty($search)) {
                $sql .= " AND (u.`name` LIKE :search OR u.`email` LIKE :search OR sp.`course` LIKE :search OR j.`title` LIKE :search)";
                $params[':search'] = '%' . trim((string)$search) . '%';
            }

            $sql .= " ORDER BY a.`applied_at` DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            $results = array_map(function($r) {
                return function_exists('hydrate_application') ? hydrate_application($r) : $r;
            }, $rows);
        } catch (Exception $e) {
            // Fallback to legacy get_applications() if custom query fails
            if (function_exists('get_applications')) {
                $results = get_applications($studentId, $jobId, $department, $employerId);
            }
        }

        // Calculate candidate schedule availability summary
        if (function_exists('get_schedule_summary')) {
            foreach ($results as &$app) {
                $app['schedule_summary'] = get_schedule_summary($app);
            }
            unset($app);
        }

        // In-memory filter for schedule availability tier (since availability is computed)
        if (!empty($fitFilter)) {
            $results = array_values(array_filter($results, function($a) use ($fitFilter) {
                return ($a['schedule_summary']['tier'] ?? '') === $fitFilter;
            }));
        }

        // In-memory keyword fallback if DB query fell back
        if (!empty($search) && empty($rows)) {
            $results = array_values(array_filter($results, function($a) use ($search) {
                return stripos($a['student_name'] ?? '', $search) !== false
                    || stripos($a['student_email'] ?? '', $search) !== false
                    || stripos($a['course'] ?? '', $search) !== false
                    || stripos($a['job_title'] ?? '', $search) !== false;
            }));
        }

        // Rank & Sort Candidates
        if (!empty($rankSort)) {
            uasort($results, function($a, $b) use ($rankSort) {
                if ($rankSort === 'sched_desc') {
                    $diff = ($b['schedule_summary']['score'] ?? 0) <=> ($a['schedule_summary']['score'] ?? 0);
                    if ($diff !== 0) return $diff;
                    return strcmp($b['applied_at'] ?? '', $a['applied_at'] ?? '');
                }
                if ($rankSort === 'sched_asc') {
                    $diff = ($a['schedule_summary']['score'] ?? 0) <=> ($b['schedule_summary']['score'] ?? 0);
                    if ($diff !== 0) return $diff;
                    return strcmp($b['applied_at'] ?? '', $a['applied_at'] ?? '');
                }
                if ($rankSort === 'name_asc') {
                    return strcasecmp($a['student_name'] ?? '', $b['student_name'] ?? '');
                }
                if ($rankSort === 'name_desc') {
                    return strcasecmp($b['student_name'] ?? '', $a['student_name'] ?? '');
                }
                if ($rankSort === 'date_asc') {
                    return strcmp($a['applied_at'] ?? '', $b['applied_at'] ?? '');
                }
                return strcmp($b['applied_at'] ?? '', $a['applied_at'] ?? '');
            });
            $results = array_values($results);
        }

        return $results;
    }
}
