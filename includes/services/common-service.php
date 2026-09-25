<?php
declare(strict_types=1);

/**
 * Common service: dates, hydration, enums, flash, uploads, KLD maps, time helpers, legacy shims.
 * Extracted from includes/data-helper.php — 100% function contract preserved.
 */

/**
 * Format a date string or timestamp into standard institutional format ('December 5, 2026').
 */
function format_display_date(string|int|null $datetime, bool $include_time = false): string {
    if ($datetime === null || $datetime === '' || $datetime === '0000-00-00' || $datetime === '0000-00-00 00:00:00' || strcasecmp((string)$datetime, 'open') === 0) {
        return 'Open';
    }
    $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime((string)$datetime);
    if (!$timestamp || $timestamp <= 0) {
        return (string)$datetime;
    }
    return date($include_time ? 'F j, Y \a\t g:i A' : 'F j, Y', $timestamp);
}

// ============================================================================
// ROW HYDRATION HELPERS
// ============================================================================

function hydrate_user(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    if (isset($row['availability']) && is_string($row['availability'])) {
        $decoded = json_decode($row['availability'], true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        $row['availability'] = is_array($decoded) ? $decoded : [];
    } elseif (!isset($row['availability']) || !is_array($row['availability'])) {
        $row['availability'] = [];
    }
    $row['id'] = (int)$row['id'];
    if (isset($row['age'])) $row['age'] = $row['age'] !== null ? (int)$row['age'] : null;

    // Aliases & compatibility
    if (!isset($row['permit_file'])) {
        $row['permit_file'] = $row['business_permit'] ?? null;
    }
    if (!isset($row['proof_file'])) {
        $row['proof_file'] = $row['registration_proof'] ?? null;
    }
    if (!isset($row['organization_name']) && isset($row['department'])) {
        $row['organization_name'] = $row['department'];
    }
    $row['is_email_verified'] = isset($row['is_email_verified']) ? (int)$row['is_email_verified'] : 1;
    return $row;
}

function hydrate_job(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    $row['id'] = (int)$row['id'];
    $row['category_id'] = isset($row['category_id']) ? (int)$row['category_id'] : null;
    $row['employer_id'] = isset($row['employer_id']) ? (int)$row['employer_id'] : null;
    $row['vacancies'] = (int)($row['vacancies'] ?? 1);
    $row['slots_total'] = (int)($row['slots_total'] ?? $row['vacancies']);
    $row['slots_filled'] = (int)($row['slots_filled'] ?? 0);
    $row['verified_employer'] = !empty($row['verified_employer']);
    $row['is_featured'] = !empty($row['image']);

    // Default synthesized aliases
    $row['category'] = $row['category'] ?? 'General';
    $row['employer_name'] = $row['employer_name'] ?? 'Hiring Supervisor';
    $row['organization_name'] = $row['organization_name'] ?? ($row['department'] ?? 'Campus Department');
    $row['employer_type'] = $row['employer_type'] ?? 'university_office';
    $row['deadline_formatted'] = format_display_date($row['deadline'] ?? 'Open');

    foreach (['tags', 'badges', 'responsibilities', 'qualifications'] as $field) {
        if (isset($row[$field]) && is_string($row[$field])) {
            $decoded = json_decode($row[$field], true);
            $row[$field] = is_array($decoded) ? $decoded : [];
        } elseif (!isset($row[$field])) {
            $row[$field] = [];
        }
    }
    return $row;
}

function hydrate_application(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    $row['id'] = (int)$row['id'];
    $row['job_id'] = (int)$row['job_id'];
    $row['student_id'] = (int)$row['student_id'];
    if (isset($row['age'])) $row['age'] = $row['age'] !== null ? (int)$row['age'] : null;
    if (isset($row['availability']) && is_string($row['availability'])) {
        $decoded = json_decode($row['availability'], true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        $row['availability'] = is_array($decoded) ? $decoded : [];
    } elseif (!isset($row['availability']) || !is_array($row['availability'])) {
        $row['availability'] = [];
    }
    if (function_exists('normalize_availability_slots')) {
        $row['availability'] = normalize_availability_slots($row['availability']);
    }

    $status_map = [
        'pending'             => 'Pending Review',
        'under_review'        => 'Under Review',
        'interview_scheduled' => 'Interview Scheduled',
        'accepted'            => 'Accepted / Hired',
        'declined'            => 'Declined / Filled'
    ];
    $badge_map = [
        'pending'             => 'warning',
        'under_review'        => 'primary',
        'interview_scheduled' => 'info',
        'accepted'            => 'success',
        'declined'            => 'danger'
    ];
    $status = $row['status'] ?? 'pending';
    if (!isset($row['status_label'])) {
        $row['status_label'] = $status_map[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
    if (!isset($row['status_badge'])) {
        $row['status_badge'] = $badge_map[$status] ?? 'secondary';
    }

    // Aliases
    if (!isset($row['student_number']) && isset($row['student_id_code'])) {
        $row['student_number'] = $row['student_id_code'];
    }

    return $row;
}

function hydrate_category(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    $row['id'] = (int)$row['id'];
    $row['job_count'] = (int)($row['job_count'] ?? 0);
    if (isset($row['popular_roles']) && is_string($row['popular_roles'])) {
        $row['popular_roles'] = json_decode($row['popular_roles'], true) ?: [];
    } elseif (!isset($row['popular_roles'])) {
        $row['popular_roles'] = [];
    }
    return $row;
}

function hydrate_profile_request(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    $row['id'] = (int)$row['id'];
    $row['user_id'] = (int)$row['user_id'];
    $row['dismissed_by_user'] = !empty($row['dismissed_by_user']);
    if (isset($row['current_profile']) && is_string($row['current_profile'])) {
        $row['current_profile'] = json_decode($row['current_profile'], true) ?: [];
    }
    if (isset($row['requested_profile']) && is_string($row['requested_profile'])) {
        $row['requested_profile'] = json_decode($row['requested_profile'], true) ?: [];
    }
    return $row;
}

function hydrate_update(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    $row['id'] = (int)$row['id'];
    $row['author'] = [
        'name'   => $row['author_name'] ?? 'Career Development Office',
        'role'   => $row['author_role'] ?? 'Coordinator',
        'office' => $row['author_office'] ?? 'KLD Career Development & Placement Office',
        'avatar' => $row['author_avatar'] ?? 'CC'
    ];
    return $row;
}

function hydrate_devblog(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    if (isset($row['daily_logs']) && is_string($row['daily_logs'])) {
        $row['daily_logs'] = json_decode($row['daily_logs'], true) ?: [];
    } elseif (!isset($row['daily_logs'])) {
        $row['daily_logs'] = [];
    }
    return $row;
}

function hydrate_notification(mixed $row): ?array {
    if (!$row || !is_array($row)) return null;
    $row['id'] = (int)$row['id'];
    $row['user_id'] = (int)$row['user_id'];
    $row['is_read'] = !empty($row['is_read']);
    return $row;
}

// ============================================================================
// ENUMS & SELECT OPTION HELPERS
// ============================================================================

function get_year_levels(): array {
    return [
        '1st Year' => '1st Year (Undergraduate)',
        '2nd Year' => '2nd Year (Undergraduate)',
        '3rd Year' => '3rd Year (Undergraduate)',
        '4th Year' => '4th Year (Undergraduate)',
        '5th Year' => '5th Year (Undergraduate / Senior)',
        'Graduate Student' => 'Graduate Student (Master’s / Post-Graduate)',
        'Alumnus / Graduated' => 'Alumnus / School Graduate'
    ];
}

function get_sex_options(): array {
    return [
        'Male' => 'Male',
        'Female' => 'Female'
    ];
}

function calculate_age(?string $birthdate): ?int {
    if (empty($birthdate) || $birthdate === '0000-00-00') {
        return null;
    }
    try {
        $dob = new DateTime($birthdate);
        $now = new DateTime();
        $interval = $now->diff($dob);
        return $interval->y;
    } catch (Exception $e) {
        return null;
    }
}

function get_job_types(): array {
    return [
        'Student Assistant' => 'Student Assistant (On-Campus SA)',
        'Part-Time Job' => 'Part-Time Job',
        'Internship / OJT' => 'Internship / OJT (Academic Practicum)',
        'Peer Tutor' => 'Peer Tutoring & Academic Coach',
        'Project-Based' => 'Project-Based / Short-Term Gig'
    ];
}

function get_employer_types(): array {
    return [
        'university_office' => 'University Academic / Administrative Office',
        'approved_partner' => 'Approved Industry / Campus Partner'
    ];
}

function get_work_setups(): array {
    return [
        'On-Campus' => 'On-Campus (University Premises)',
        'Near-Campus' => 'Near-Campus (Partner Office)',
        'Hybrid' => 'Hybrid (Campus + Remote)',
        'Remote' => 'Remote / Online'
    ];
}

// ============================================================================
// FLASH MESSAGING
// ============================================================================

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// File Upload Handlers with MIME Validation
function validate_upload_mime(string $tmp_name, array $allowed_mimes): bool {
    if (!is_uploaded_file($tmp_name)) {
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) return false;
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    return in_array($mime, $allowed_mimes, true);
}

function save_uploaded_permit(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true) || !validate_upload_mime($file['tmp_name'], $allowed_mimes)) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $upload_dir = dirname(__DIR__, 2) . '/uploads/permits';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = 'permit_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/permits/' . $filename;
    }
    return null;
}

function save_uploaded_proof(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true) || !validate_upload_mime($file['tmp_name'], $allowed_mimes)) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $upload_dir = dirname(__DIR__, 2) . '/uploads/proofs';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = 'proof_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/proofs/' . $filename;
    }
    return null;
}

function save_uploaded_resume(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed_exts = ['pdf', 'doc', 'docx'];
    $allowed_mimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true) || !validate_upload_mime($file['tmp_name'], $allowed_mimes)) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $upload_dir = dirname(__DIR__, 2) . '/uploads/resumes';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = 'resume_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/resumes/' . $filename;
    }
    return null;
}

function save_uploaded_job_photo(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true) || !validate_upload_mime($file['tmp_name'], $allowed_mimes)) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $upload_dir = dirname(__DIR__, 2) . '/uploads/jobs';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = 'job_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/jobs/' . $filename;
    }
    return null;
}

function save_uploaded_category_photo(?array $file): ?string {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts, true) || !validate_upload_mime($file['tmp_name'], $allowed_mimes)) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    $upload_dir = dirname(__DIR__, 2) . '/uploads/categories';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = 'cat_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/categories/' . $filename;
    }
    return null;
}

// ============================================================================
// ACADEMIC INSTITUTES & TAXONOMY
// ============================================================================

function get_kld_institutes_and_courses(): array {
    return [
        'Institute of Computing and Digital Innovation (ICDI)' => [
            'BS Information Systems (BSIS)',
            'BS Computer Science (BSCS)',
            'BS Data Science (BSDS)'
        ],
        'Institute of Engineering (IE)' => [
            'BS Civil Engineering (BSCE)'
        ],
        'Institute of Nursing (IN)' => [
            'BS Nursing (BSN)'
        ],
        'Institute of Medical Laboratory Science (IMLS)' => [
            'BS Medical Laboratory Science (BSMLS)'
        ],
        'Institute of Midwifery (IM)' => [
            'BS Midwifery (BSM)'
        ],
        'Institute of Science and Mathematics (ISM)' => [
            'BS Life Sciences (BSLS)'
        ],
        'Institute of Behavioral Sciences (IBS)' => [
            'BS Psychology (BSP)'
        ],
        'Institute of Governance and Development Studies (IGDS)' => [
            'BS Social Work (BSSW)'
        ]
    ];
}

function get_kld_courses_flat(): array {
    $institutes = get_kld_institutes_and_courses();
    $flat = [];
    foreach ($institutes as $courses) {
        foreach ($courses as $c) {
            $flat[] = $c;
        }
    }
    return $flat;
}

function time_ago_short(string|int|null $datetime): string {
    if (!$datetime) return '';
    $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime((string)$datetime);
    if (!$timestamp) return '';
    $diff = time() - $timestamp;
    if ($diff < 0) $diff = 0;
    if ($diff < 60) return '1m ago';
    if ($diff < 3600) return max(1, (int)floor($diff / 60)) . 'm ago';
    if ($diff < 86400) return (string)(int)floor($diff / 3600) . 'h ago';
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return (string)(int)floor($diff / 86400) . 'd ago';
    return date('M j', $timestamp);
}

// Legacy JSON Compatibility Shims
function load_json_file(string $filename): array {
    $base = basename($filename, '.json');
    return match($base) {
        'users' => get_all_users(),
        'jobs' => get_jobs(),
        'applications' => get_applications(),
        'categories' => get_categories(),
        'profile_requests' => get_profile_requests(),
        'updates' => get_career_updates(),
        'devblogs' => get_devblogs(),
        default => []
    };
}

function save_json_file(string $filename, array $data): bool {
    $path = DATA_DIR . '/' . $filename;
    if (is_dir(DATA_DIR) && is_writable(DATA_DIR)) {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded !== false) {
            return file_put_contents($path, $encoded) !== false;
        }
    }
    return false;
}
