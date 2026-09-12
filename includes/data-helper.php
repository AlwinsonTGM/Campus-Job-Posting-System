<?php
/**
 * Campus Job Posting System - Data Helper & Persistence Engine
 * Powers the entire KLD Campus Hire data layer using MySQL / MariaDB via PDO.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

define('DATA_DIR', dirname(__DIR__) . '/data');

// ============================================================================
// ROW HYDRATION HELPERS
// ============================================================================

function hydrate_user($row) {
    if (!$row) return null;
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
    return $row;
}

function hydrate_job($row) {
    if (!$row) return null;
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

function hydrate_application($row) {
    if (!$row) return null;
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

function hydrate_category($row) {
    if (!$row) return null;
    $row['id'] = (int)$row['id'];
    $row['job_count'] = (int)($row['job_count'] ?? 0);
    if (isset($row['popular_roles']) && is_string($row['popular_roles'])) {
        $row['popular_roles'] = json_decode($row['popular_roles'], true) ?: [];
    } elseif (!isset($row['popular_roles'])) {
        $row['popular_roles'] = [];
    }
    return $row;
}

function hydrate_profile_request($row) {
    if (!$row) return null;
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

function hydrate_update($row) {
    if (!$row) return null;
    $row['id'] = (int)$row['id'];
    $row['author'] = [
        'name'   => $row['author_name'] ?? 'Career Development Office',
        'role'   => $row['author_role'] ?? 'Coordinator',
        'office' => $row['author_office'] ?? 'KLD Career Development & Placement Office',
        'avatar' => $row['author_avatar'] ?? 'CC'
    ];
    return $row;
}

function hydrate_devblog($row) {
    if (!$row) return null;
    if (isset($row['daily_logs']) && is_string($row['daily_logs'])) {
        $row['daily_logs'] = json_decode($row['daily_logs'], true) ?: [];
    } elseif (!isset($row['daily_logs'])) {
        $row['daily_logs'] = [];
    }
    return $row;
}

function hydrate_notification($row) {
    if (!$row) return null;
    $row['id'] = (int)$row['id'];
    $row['user_id'] = (int)$row['user_id'];
    $row['is_read'] = !empty($row['is_read']);
    return $row;
}

// ============================================================================
// ENUMS & SELECT OPTION HELPERS
// ============================================================================

function get_year_levels() {
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

function get_sex_options() {
    return [
        'Male' => 'Male',
        'Female' => 'Female'
    ];
}

function calculate_age($birthdate) {
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

function get_job_types() {
    return [
        'Student Assistant' => 'Student Assistant (On-Campus SA)',
        'Part-Time Job' => 'Part-Time Job',
        'Internship / OJT' => 'Internship / OJT (Academic Practicum)',
        'Peer Tutor' => 'Peer Tutoring & Academic Coach',
        'Project-Based' => 'Project-Based / Short-Term Gig'
    ];
}

function get_employer_types() {
    return [
        'university_office' => 'University Academic / Administrative Office',
        'approved_partner' => 'Approved Industry / Campus Partner'
    ];
}

function get_work_setups() {
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

function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ============================================================================
// AUTH & SESSION HELPERS
// ============================================================================

function get_logged_user() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        return null;
    }

    // Live sync from database so administrative approvals / profile changes take effect immediately
    try {
        $fresh = get_user_by_id((int)$_SESSION['user']['id']);
        if ($fresh) {
            unset($fresh['password']);
            $_SESSION['user'] = $fresh;
            return $fresh;
        }
    } catch (Exception $e) {
        // Fallback to session cache if DB query encounters an issue
    }

    return $_SESSION['user'];
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function has_role($role) {
    $user = get_logged_user();
    return $user && ($user['role'] ?? '') === $role;
}

function require_auth($allowed_roles = []) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $prefix = (strpos($script, '/admin/') !== false || strpos($script, '/employer/') !== false || strpos($script, '/student/') !== false) ? '../' : '';

    if (!is_logged_in()) {
        set_flash('warning', 'Please sign in to access this page.');
        header('Location: ' . $prefix . 'login.php');
        exit;
    }

    if (!empty($allowed_roles)) {
        $user = get_logged_user();
        if (!in_array($user['role'] ?? '', $allowed_roles)) {
            set_flash('danger', 'Unauthorized access for your account role.');
            header('Location: ' . $prefix . 'index.php');
            exit;
        }
    }
}

// ============================================================================
// CSRF PROTECTION
// ============================================================================

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (empty($_SESSION['csrf_token']) || empty($token) || !is_string($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        return verify_csrf_token($token);
    }
}

// ============================================================================
// AUTHORIZATION CONTRACTS
// ============================================================================

function can_manage_job($job_or_id, $user = null) {
    if ($user === null) {
        $user = get_logged_user();
    }
    if (!$user) {
        return false;
    }
    if (($user['role'] ?? '') === 'admin') {
        return true;
    }
    if (($user['role'] ?? '') !== 'employer') {
        return false;
    }

    $job = is_array($job_or_id) ? $job_or_id : get_job_by_id($job_or_id);
    if (!$job) {
        return false;
    }

    return isset($job['employer_id']) && (int)$job['employer_id'] === (int)($user['id'] ?? 0);
}

function can_review_application($app_or_id, $user = null) {
    if ($user === null) {
        $user = get_logged_user();
    }
    if (!$user) {
        return false;
    }
    if (($user['role'] ?? '') === 'admin') {
        return true;
    }
    if (($user['role'] ?? '') !== 'employer') {
        return false;
    }

    $app = is_array($app_or_id) ? $app_or_id : get_application_by_id($app_or_id);
    if (!$app) {
        return false;
    }

    $job = get_job_by_id($app['job_id'] ?? 0);
    return $job && can_manage_job($job, $user);
}

function can_view_student_resume($app = null, $student_user_id = null, $user = null) {
    if ($user === null) {
        $user = get_logged_user();
    }
    if (!$user) {
        return false;
    }
    if (($user['role'] ?? '') === 'admin') {
        return true;
    }

    if (($user['role'] ?? '') === 'student') {
        if ($app !== null) {
            $target_app = is_array($app) ? $app : get_application_by_id($app);
            return $target_app && isset($target_app['student_id']) && (int)$target_app['student_id'] === (int)($user['id'] ?? 0);
        }
        if ($student_user_id !== null) {
            return (int)$student_user_id === (int)($user['id'] ?? 0);
        }
        return true;
    }

    if (($user['role'] ?? '') === 'employer') {
        if ($app !== null) {
            return can_review_application($app, $user);
        }
        if ($student_user_id !== null) {
            $apps = get_applications($student_user_id);
            foreach ($apps as $a) {
                if (can_review_application($a, $user)) {
                    return true;
                }
            }
            return false;
        }
    }

    return false;
}

// ============================================================================
// USER MANAGEMENT & AUTH ACTIONS
// ============================================================================

function get_user_base_query() {
    return "
        SELECT 
            u.`id`, u.`email`, u.`password`, u.`role`, u.`name`, u.`phone`, u.`status`, u.`created_at`, u.`updated_at`,
            sp.`student_id`,
            sp.`department` AS `student_department`,
            COALESCE(sp.`department`, ep.`organization_name`, 'General Academics') AS `department`,
            sp.`course`,
            sp.`year_level`,
            sp.`sex`,
            sp.`birthdate`,
            sp.`age`,
            sp.`availability`,
            sp.`registration_proof`,
            COALESCE(sp.`verification_status`, ep.`verification_status`, 'verified') AS `verification_status`,
            COALESCE(sp.`rejection_reason`, ep.`rejection_reason`) AS `rejection_reason`,
            ep.`employer_type`,
            COALESCE(ep.`organization_name`, sp.`department`, u.`name`) AS `organization_name`,
            ep.`office_location`,
            ep.`contact_person`,
            ep.`accreditation_number`,
            ep.`business_permit`
        FROM `users` u
        LEFT JOIN `student_profiles` sp ON u.`id` = sp.`user_id`
        LEFT JOIN `employer_profiles` ep ON u.`id` = ep.`user_id`
    ";
}

function get_all_users($role = null, $keyword = null, $emp_type = null, $ver_status = null) {
    try {
        $pdo = get_db_connection();
        $sql = get_user_base_query() . " WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND u.`role` = :role";
            $params[':role'] = $role;
        }
        if ($emp_type) {
            $sql .= " AND ep.`employer_type` = :emp_type";
            $params[':emp_type'] = $emp_type;
        }
        if ($ver_status) {
            $sql .= " AND (sp.`verification_status` = :ver_status OR ep.`verification_status` = :ver_status2)";
            $params[':ver_status'] = $ver_status;
            $params[':ver_status2'] = $ver_status;
        }
        if ($keyword) {
            $kw_val = '%' . trim($keyword) . '%';
            $sql .= " AND (u.`name` LIKE :kw1 OR u.`email` LIKE :kw2 OR sp.`student_id` LIKE :kw3 OR sp.`department` LIKE :kw4 OR ep.`organization_name` LIKE :kw5)";
            $params[':kw1'] = $kw_val;
            $params[':kw2'] = $kw_val;
            $params[':kw3'] = $kw_val;
            $params[':kw4'] = $kw_val;
            $params[':kw5'] = $kw_val;
        }

        $sql .= " ORDER BY u.`id` ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map('hydrate_user', $rows);
    } catch (Exception $e) {
        error_log("get_all_users error: " . $e->getMessage());
        return [];
    }
}

function get_user_by_id($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(get_user_base_query() . " WHERE u.`id` = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_user($row) : null;
    } catch (Exception $e) {
        error_log("get_user_by_id error: " . $e->getMessage());
        return null;
    }
}

function login_user($email, $password) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(get_user_base_query() . " WHERE LOWER(u.`email`) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        $row = $stmt->fetch();

        if ($row) {
            $user = hydrate_user($row);
            $stored_hash = $user['password'] ?? '';
            $is_valid = false;

            if (password_verify($password, $stored_hash)) {
                $is_valid = true;
                if (password_needs_rehash($stored_hash, PASSWORD_DEFAULT)) {
                    update_user_password($user['id'], password_hash($password, PASSWORD_DEFAULT));
                }
            } elseif ($stored_hash === $password) {
                // Legacy plaintext fallback: upgrade immediately to secure hash
                $is_valid = true;
                update_user_password($user['id'], password_hash($password, PASSWORD_DEFAULT));
            }

            if ($is_valid) {
                session_regenerate_id(true);
                unset($user['password']);
                $_SESSION['user'] = $user;
                return ['success' => true, 'user' => $user];
            }
            return ['success' => false, 'message' => 'Invalid password credentials.'];
        }
        return ['success' => false, 'message' => 'No account found with this email address.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function quick_login($role, $user_id = null) {
    try {
        $pdo = get_db_connection();
        if ($user_id) {
            $stmt = $pdo->prepare(get_user_base_query() . " WHERE u.`id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare(get_user_base_query() . " WHERE u.`role` = :role ORDER BY u.`id` ASC LIMIT 1");
            $stmt->execute([':role' => $role]);
        }
        $row = $stmt->fetch();
        if ($row) {
            $user = hydrate_user($row);
            $_SESSION['user'] = $user;
            return $user;
        }
        return null;
    } catch (Exception $e) {
        error_log("quick_login error: " . $e->getMessage());
        return null;
    }
}

// File Upload Handlers with MIME Validation
function validate_upload_mime($tmp_name, array $allowed_mimes) {
    if (!is_uploaded_file($tmp_name)) {
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) return false;
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    return in_array($mime, $allowed_mimes, true);
}

function save_uploaded_permit($file) {
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
    $upload_dir = dirname(__DIR__) . '/uploads/permits';
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

function save_uploaded_proof($file) {
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
    $upload_dir = dirname(__DIR__) . '/uploads/proofs';
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

function save_uploaded_resume($file) {
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
    $upload_dir = dirname(__DIR__) . '/uploads/resumes';
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

function save_uploaded_job_photo($file) {
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
    $upload_dir = dirname(__DIR__) . '/uploads/jobs';
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

function save_uploaded_category_photo($file) {
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
    $upload_dir = dirname(__DIR__) . '/uploads/categories';
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

function update_user_verification($id, $status, $notes = '') {
    try {
        $pdo = get_db_connection();
        $target_user = get_user_by_id($id);
        if (!$target_user) return false;

        $id = (int)$id;
        $role = $target_user['role'] ?? 'student';

        if ($role === 'student') {
            $stmt = $pdo->prepare("
                UPDATE `student_profiles`
                SET `verification_status` = :status, `rejection_reason` = :notes, `updated_at` = NOW()
                WHERE `user_id` = :id
            ");
            $stmt->execute([
                ':status' => $status,
                ':notes'  => $notes,
                ':id'     => $id
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE `employer_profiles`
                SET `verification_status` = :status, `rejection_reason` = :notes, `updated_at` = NOW()
                WHERE `user_id` = :id
            ");
            $stmt->execute([
                ':status' => $status,
                ':notes'  => $notes,
                ':id'     => $id
            ]);
        }

        $success = $stmt->rowCount() > 0 || ($target_user['verification_status'] === $status);

        if ($target_user && $success) {
            if ($status === 'verified') {
                $msg = ($target_user['role'] === 'employer') 
                    ? "Congratulations! Your partner organization accreditation has been approved. You can now post campus job vacancies."
                    : "Your account verification has been approved. You now have full access to campus opportunities.";
                $link = ($target_user['role'] === 'employer') ? "employer/create-job.php" : "student/jobs.php";
                create_notification(
                    $id,
                    'verification',
                    'Account Verified & Approved! 🎉',
                    $msg,
                    $link,
                    'bi-patch-check-fill',
                    'success'
                );
            } elseif ($status === 'rejected') {
                $reason_snippet = !empty($notes) ? " Note: {$notes}" : "";
                $msg = "Your verification request requires attention or was declined.{$reason_snippet} Please check your profile credentials.";
                create_notification(
                    $id,
                    'verification',
                    'Verification Status Update',
                    $msg,
                    'settings.php',
                    'bi-exclamation-triangle-fill',
                    'danger'
                );
            }
        }

        return $success;
    } catch (Exception $e) {
        error_log("update_user_verification error: " . $e->getMessage());
        return false;
    }
}

function update_employer_verification($id, $status, $notes = '') {
    return update_user_verification($id, $status, $notes);
}

/**
 * Check if an email address is already registered
 */
function is_email_registered($email) {
    $email = strtolower(trim((string)$email));
    if (empty($email)) return false;
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(`email`) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        $users = get_users();
        foreach ($users as $u) {
            if (isset($u['email']) && strtolower(trim($u['email'])) === $email) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Check if a student ID is already registered
 */
function is_student_id_registered($student_id) {
    $sid = strtolower(trim((string)$student_id));
    if (empty($sid)) return false;
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT `user_id` FROM `student_profiles` WHERE LOWER(TRIM(`student_id`)) = LOWER(TRIM(:sid)) LIMIT 1");
        $stmt->execute([':sid' => $sid]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        $users = get_users();
        foreach ($users as $u) {
            if (isset($u['student_id']) && strtolower(trim($u['student_id'])) === $sid && ($u['role'] ?? '') === 'student') {
                return true;
            }
        }
        return false;
    }
}

function register_user($data, $permit_file = null, $proof_file = null) {
    try {
        $pdo = get_db_connection();
        $email = strtolower(trim($data['email'] ?? ''));

        // Check if email already exists
        if (is_email_registered($email)) {
            return ['success' => false, 'message' => 'This KLD account / email address is already registered.'];
        }

        $allowed_roles = ['student', 'employer'];
        $role = in_array($data['role'] ?? '', $allowed_roles, true) ? $data['role'] : 'student';

        // Check if student ID already exists
        if ($role === 'student' && !empty($data['student_id'])) {
            if (is_student_id_registered($data['student_id'])) {
                return ['success' => false, 'message' => 'This Student ID Number is already registered to an existing account.'];
            }
        }

        $employer_type = $data['employer_type'] ?? 'university_office';
        $org_name = $data['organization_name'] ?? ($data['department'] ?? 'Campus Organization');
        $is_kld_email = (bool)preg_match('/@kld\.edu\.ph$/i', $email);

        if ($role === 'employer') {
            if ($employer_type === 'university_office' && $is_kld_email) {
                $verification = 'verified';
                $accreditation = 'INTERNAL-UNIV';
            } else {
                $verification = 'pending_approval';
                $accreditation = $data['accreditation_number'] ?? 'PENDING-VERIFICATION';
            }
        } else {
            // University students registering with @kld.edu.ph institutional email or valid credentials are auto-verified
            $verification = $is_kld_email ? 'verified' : 'pending_approval';
            $accreditation = $data['student_id'] ?? ('STUDENT-' . rand(10000, 99999));
        }

        $sex = $data['sex'] ?? ($role === 'student' ? 'Male' : '');
        $birthdate = (!empty($data['birthdate']) && $data['birthdate'] !== '0000-00-00') ? $data['birthdate'] : null;
        $age = !empty($birthdate) ? calculate_age($birthdate) : ($data['age'] ?? ($role === 'student' ? 20 : null));
        $year_level = $data['year_level'] ?? '1st Year';
        $proof_path = $proof_file ?? ($data['proof_file'] ?? null);
        $permit_path = $permit_file ?? ($data['permit_file'] ?? null);

        $raw_pass = $data['password'] ?? 'Password123!';
        $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        $stmt_user = $pdo->prepare("
            INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `status`, `created_at`)
            VALUES (:name, :email, :password, :role, :phone, 'active', NOW())
        ");
        $stmt_user->execute([
            ':name'     => trim($data['name'] ?? ''),
            ':email'    => $email,
            ':password' => $hashed_pass,
            ':role'     => $role,
            ':phone'    => $data['phone'] ?? null
        ]);

        $new_id = (int)$pdo->lastInsertId();

        if ($role === 'student') {
            $student_id_val = !empty($data['student_id']) ? $data['student_id'] : ('KLD-' . str_pad($new_id, 6, '0', STR_PAD_LEFT));
            $availability = isset($data['availability']) ? (is_array($data['availability']) ? json_encode($data['availability']) : $data['availability']) : json_encode([]);
            $stmt_student = $pdo->prepare("
                INSERT INTO `student_profiles` (
                    `user_id`, `student_id`, `department`, `course`, `year_level`,
                    `sex`, `birthdate`, `age`, `availability`, `verification_status`, `registration_proof`, `created_at`
                ) VALUES (
                    :user_id, :student_id, :department, :course, :year_level,
                    :sex, :birthdate, :age, :availability, :verification_status, :registration_proof, NOW()
                )
            ");
            $stmt_student->execute([
                ':user_id'             => $new_id,
                ':student_id'          => $student_id_val,
                ':department'          => $data['department'] ?? 'Institute of Computing and Digital Innovation (ICDI)',
                ':course'              => $data['course'] ?? 'BS Information Systems (BSIS)',
                ':year_level'          => $year_level,
                ':sex'                 => $sex,
                ':birthdate'           => $birthdate,
                ':age'                 => $age,
                ':availability'        => $availability,
                ':verification_status' => $verification,
                ':registration_proof'  => $proof_path
            ]);
        } elseif ($role === 'employer') {
            $stmt_employer = $pdo->prepare("
                INSERT INTO `employer_profiles` (
                    `user_id`, `employer_type`, `organization_name`, `office_location`,
                    `contact_person`, `accreditation_number`, `verification_status`, `business_permit`, `created_at`
                ) VALUES (
                    :user_id, :employer_type, :organization_name, :office_location,
                    :contact_person, :accreditation_number, :verification_status, :business_permit, NOW()
                )
            ");
            $stmt_employer->execute([
                ':user_id'              => $new_id,
                ':employer_type'        => $employer_type,
                ':organization_name'    => trim($org_name),
                ':office_location'      => $data['office_location'] ?? 'Campus Main Office',
                ':contact_person'       => trim($data['name'] ?? ''),
                ':accreditation_number' => $accreditation,
                ':verification_status'  => $verification,
                ':business_permit'      => $permit_path
            ]);
        }

        $pdo->commit();
        $new_user = get_user_by_id($new_id);
        if ($new_user) unset($new_user['password']);
        $_SESSION['user'] = $new_user;

        if ($verification === 'pending_approval') {
            $applicant_name = trim($data['name'] ?? 'New Partner');
            $type_label = $role === 'employer' ? 'Employer Accreditation' : 'Student Verification';
            notify_all_admins(
                'verification_request',
                "New {$type_label} Pending",
                "{$applicant_name} ({$role}) registered and is awaiting credential verification.",
                'admin/users.php?ver_status=pending_approval',
                'bi-shield-exclamation',
                'warning'
            );
        }

        return ['success' => true, 'user' => $new_user];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

// ============================================================================
// STUDENT PROFILE CHANGE REQUESTS
// ============================================================================

function get_profile_requests($user_id = null, $status = null) {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                pr.*,
                u.`name` AS `user_name`,
                u.`email` AS `user_email`,
                sp.`student_id`
            FROM `profile_requests` pr
            INNER JOIN `users` u ON pr.`user_id` = u.`id`
            LEFT JOIN `student_profiles` sp ON pr.`user_id` = sp.`user_id`
            WHERE 1=1
        ";
        $params = [];

        if ($user_id) {
            $sql .= " AND pr.`user_id` = :user_id";
            $params[':user_id'] = (int)$user_id;
        }
        if ($status) {
            $sql .= " AND pr.`status` = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY pr.`created_at` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map('hydrate_profile_request', $rows);
    } catch (Exception $e) {
        error_log("get_profile_requests error: " . $e->getMessage());
        return [];
    }
}

function get_pending_profile_request($user_id) {
    $reqs = get_profile_requests($user_id, 'pending');
    return !empty($reqs) ? $reqs[0] : null;
}

function get_recent_profile_request_notice($user_id) {
    $reqs = get_profile_requests($user_id);
    foreach ($reqs as $r) {
        if (in_array($r['status'] ?? '', ['approved', 'rejected']) && empty($r['dismissed_by_user'])) {
            return $r;
        }
    }
    return null;
}

function dismiss_profile_request_notice($user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            UPDATE `profile_requests` 
            SET `dismissed_by_user` = 1 
            WHERE `user_id` = :user_id AND `status` IN ('approved', 'rejected')
        ");
        $stmt->execute([':user_id' => (int)$user_id]);
        return true;
    } catch (Exception $e) {
        error_log("dismiss_profile_request_notice error: " . $e->getMessage());
        return false;
    }
}

function create_profile_request($user_id, $requested_data, $proof_file, $reason = '') {
    try {
        $pdo = get_db_connection();

        // Check if user already has an active pending request
        $pending = get_pending_profile_request($user_id);
        if ($pending) {
            return ['success' => false, 'message' => 'You already have an active profile update request awaiting review.'];
        }

        $current_user = get_user_by_id($user_id);
        if (!$current_user) {
            return ['success' => false, 'message' => 'User account not found.'];
        }

        $req_birthdate = trim($requested_data['birthdate'] ?? ($current_user['birthdate'] ?? ''));
        $req_age = !empty($req_birthdate) ? calculate_age($req_birthdate) : (!empty($requested_data['age']) ? (int)$requested_data['age'] : ($current_user['age'] ?? 20));

        $req_email = strtolower(trim($requested_data['email'] ?? ($current_user['email'] ?? '')));
        if (empty($req_email) || !filter_var($req_email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please provide a valid institutional / student email address.'];
        }

        // If email is changing, check uniqueness against other users
        if ($req_email !== strtolower($current_user['email'])) {
            $stmt_chk = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(`email`) = :email AND `id` != :id LIMIT 1");
            $stmt_chk->execute([':email' => $req_email, ':id' => (int)$user_id]);
            if ($stmt_chk->fetch()) {
                return ['success' => false, 'message' => 'The requested email address is already in use by another account.'];
            }
        }

        $current_profile = [
            'name'       => $current_user['name'] ?? '',
            'email'      => $current_user['email'] ?? '',
            'department' => $current_user['department'] ?? '',
            'course'     => $current_user['course'] ?? '',
            'year_level' => $current_user['year_level'] ?? '1st Year',
            'sex'        => $current_user['sex'] ?? 'Male',
            'birthdate'  => $current_user['birthdate'] ?? '',
            'age'        => $current_user['age'] ?? 20,
        ];

        $requested_profile = [
            'name'       => trim($requested_data['name'] ?? $current_user['name']),
            'email'      => $req_email,
            'department' => trim($requested_data['department'] ?? $current_user['department']),
            'course'     => trim($requested_data['course'] ?? $current_user['course']),
            'year_level' => trim($requested_data['year_level'] ?? $current_user['year_level']),
            'sex'        => trim($requested_data['sex'] ?? ($current_user['sex'] ?? 'Male')),
            'birthdate'  => trim($req_birthdate),
            'age'        => $req_age,
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `profile_requests` (
                `user_id`, `current_profile`, `requested_profile`,
                `proof_file`, `reason`, `status`, `admin_notes`,
                `dismissed_by_user`, `created_at`
            ) VALUES (
                :user_id, :current_profile, :requested_profile,
                :proof_file, :reason, 'pending', '', 0, NOW()
            )
        ");

        $stmt->execute([
            ':user_id'           => (int)$user_id,
            ':current_profile'   => json_encode($current_profile),
            ':requested_profile' => json_encode($requested_profile),
            ':proof_file'        => $proof_file,
            ':reason'            => trim($reason)
        ]);

        $new_id = (int)$pdo->lastInsertId();
        $new_req = [
            'id'                => $new_id,
            'user_id'           => (int)$user_id,
            'user_name'         => $current_user['name'],
            'user_email'        => $current_user['email'],
            'student_id'        => $current_user['student_id'] ?? '2024-00123',
            'current_profile'   => $current_profile,
            'requested_profile' => $requested_profile,
            'proof_file'        => $proof_file,
            'reason'            => trim($reason),
            'status'            => 'pending',
            'admin_notes'       => '',
            'dismissed_by_user' => false,
            'created_at'        => date('Y-m-d H:i:s'),
            'resolved_at'       => null
        ];

        notify_all_admins(
            'profile_request',
            'Profile Correction Request',
            "{$current_user['name']} submitted a profile change request for administrative review.",
            'admin/users.php?ver_status=all',
            'bi-person-gear',
            'info'
        );

        return ['success' => true, 'request' => $new_req];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Request creation failed: ' . $e->getMessage()];
    }
}

function approve_profile_request($request_id, $admin_notes = '') {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM `profile_requests` WHERE `id` = :id AND `status` = 'pending' LIMIT 1");
        $stmt->execute([':id' => (int)$request_id]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $target_req = hydrate_profile_request($row);
        $user_id = $target_req['user_id'];
        $requested = $target_req['requested_profile'];

        $pdo->beginTransaction();

        // 1. Update users identity table (name, email)
        $user_updates = [];
        $user_params = [':id' => $user_id];

        if (!empty($requested['name'])) { 
            $user_updates[] = "`name` = :name"; 
            $user_params[':name'] = $requested['name']; 
        }
        
        if (!empty($requested['email'])) {
            $candidate_email = strtolower(trim($requested['email']));
            $chk_email = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(`email`) = :email AND `id` != :id LIMIT 1");
            $chk_email->execute([':email' => $candidate_email, ':id' => $user_id]);
            if (!$chk_email->fetch()) {
                $user_updates[] = "`email` = :email";
                $user_params[':email'] = $candidate_email;
            }
        }

        if (!empty($user_updates)) {
            $user_sql = "UPDATE `users` SET " . implode(', ', $user_updates) . ", `updated_at` = NOW() WHERE `id` = :id";
            $upd_user_stmt = $pdo->prepare($user_sql);
            $upd_user_stmt->execute($user_params);
        }

        // 2. Update student_profiles subtype table
        $student_updates = [];
        $student_params = [':user_id' => $user_id];

        if (!empty($requested['department'])) { $student_updates[] = "`department` = :department"; $student_params[':department'] = $requested['department']; }
        if (!empty($requested['course'])) { $student_updates[] = "`course` = :course"; $student_params[':course'] = $requested['course']; }
        if (!empty($requested['year_level'])) { $student_updates[] = "`year_level` = :year_level"; $student_params[':year_level'] = $requested['year_level']; }
        if (!empty($requested['sex'])) { $student_updates[] = "`sex` = :sex"; $student_params[':sex'] = $requested['sex']; }
        if (!empty($requested['birthdate'])) { $student_updates[] = "`birthdate` = :birthdate"; $student_params[':birthdate'] = $requested['birthdate']; }
        if (!empty($requested['age'])) { $student_updates[] = "`age` = :age"; $student_params[':age'] = (int)$requested['age']; }
        if (!empty($target_req['proof_file'])) { $student_updates[] = "`registration_proof` = :proof"; $student_params[':proof'] = $target_req['proof_file']; }

        // If the student's registration was rejected or awaiting revision, approving their official correction restores full verified status
        $cur_user_row = get_user_by_id($user_id);
        if ($cur_user_row && in_array($cur_user_row['verification_status'] ?? '', ['rejected', 'pending_approval'])) {
            $student_updates[] = "`verification_status` = 'verified'";
            $student_updates[] = "`rejection_reason` = NULL";
        }

        if (!empty($student_updates)) {
            $student_sql = "UPDATE `student_profiles` SET " . implode(', ', $student_updates) . ", `updated_at` = NOW() WHERE `user_id` = :user_id";
            $upd_student_stmt = $pdo->prepare($student_sql);
            $upd_student_stmt->execute($student_params);
        }

        // 3. Mark request as approved
        $stmt_req_upd = $pdo->prepare("
            UPDATE `profile_requests` 
            SET `status` = 'approved', `admin_notes` = :notes, `resolved_at` = NOW(), `dismissed_by_user` = 0 
            WHERE `id` = :id AND `status` = 'pending'
        ");
        $stmt_req_upd->execute([
            ':notes' => trim($admin_notes),
            ':id'    => (int)$request_id
        ]);

        $pdo->commit();

        // Refresh session if the modified user is currently logged in
        if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === $user_id) {
            $_SESSION['user'] = get_user_by_id($user_id);
        }

        // Notify student of approval
        create_notification(
            $user_id,
            'profile_request',
            'Profile Update Approved! 🎉',
            'Your institutional record change request has been verified and updated by the administrator.',
            'settings.php',
            'bi-person-check-fill',
            'success'
        );

        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("approve_profile_request error: " . $e->getMessage());
        return false;
    }
}

function reject_profile_request($request_id, $admin_notes = '') {
    try {
        $pdo = get_db_connection();
        $chk_stmt = $pdo->prepare("SELECT `user_id` FROM `profile_requests` WHERE `id` = :id LIMIT 1");
        $chk_stmt->execute([':id' => (int)$request_id]);
        $user_id = (int)$chk_stmt->fetchColumn();

        $stmt = $pdo->prepare("
            UPDATE `profile_requests` 
            SET `status` = 'rejected', `admin_notes` = :notes, `resolved_at` = NOW(), `dismissed_by_user` = 0 
            WHERE `id` = :id AND `status` = 'pending'
        ");
        $stmt->execute([
            ':notes' => trim($admin_notes),
            ':id'    => (int)$request_id
        ]);
        $success = $stmt->rowCount() > 0;

        if ($success && $user_id > 0) {
            $notes_snip = !empty($admin_notes) ? " Note: " . trim($admin_notes) : "";
            create_notification(
                $user_id,
                'profile_request',
                'Profile Update Request Declined',
                "Your profile change request was declined by the administrator.{$notes_snip}",
                'settings.php',
                'bi-person-x-fill',
                'warning'
            );
        }

        return $success;
    } catch (Exception $e) {
        error_log("reject_profile_request error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// JOB REQUISITIONS MANAGEMENT
// ============================================================================

function get_jobs($category = null, $keyword = null, $department = null, $pay_type = null, $job_type = null, $employer_type = null, $work_setup = null, $employer_id = null) {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                j.*,
                c.`name` AS `category`,
                COALESCE(ep.`organization_name`, j.`department`) AS `organization_name`,
                u.`name` AS `employer_name`,
                COALESCE(ep.`employer_type`, 'university_office') AS `employer_type`,
                CASE WHEN ep.`verification_status` = 'verified' OR u.`role` = 'admin' THEN 1 ELSE 0 END AS `verified_employer`
            FROM `jobs` j
            LEFT JOIN `categories` c ON j.`category_id` = c.`id`
            LEFT JOIN `users` u ON j.`employer_id` = u.`id`
            LEFT JOIN `employer_profiles` ep ON j.`employer_id` = ep.`user_id`
            WHERE 1=1
        ";
        $params = [];

        if ($employer_id !== null) {
            $sql .= " AND j.`employer_id` = :emp_owner_id";
            $params[':emp_owner_id'] = (int)$employer_id;
        }

        if ($category) {
            if (is_array($category)) {
                $cat_clauses = [];
                $i = 0;
                foreach ($category as $cat_val) {
                    if (empty($cat_val) || !is_scalar($cat_val)) continue;
                    $p_name = ":cat_" . ($i++);
                    $cat_clauses[] = "(c.`name` LIKE $p_name OR j.`category_id` = $p_name)";
                    $params[$p_name] = '%' . trim((string)$cat_val) . '%';
                }
                if (!empty($cat_clauses)) {
                    $sql .= " AND (" . implode(' OR ', $cat_clauses) . ")";
                }
            } else {
                $sql .= " AND (c.`name` LIKE :cat OR j.`category_id` = :cat_exact)";
                $params[':cat'] = '%' . trim($category) . '%';
                $params[':cat_exact'] = trim($category);
            }
        }

        if ($job_type) {
            if (is_array($job_type)) {
                $jt_clauses = [];
                $j = 0;
                foreach ($job_type as $jt_val) {
                    if (empty($jt_val) || !is_scalar($jt_val)) continue;
                    $p_name = ":jt_" . ($j++);
                    $jt_clauses[] = "j.`job_type` LIKE $p_name";
                    $params[$p_name] = '%' . trim((string)$jt_val) . '%';
                }
                if (!empty($jt_clauses)) {
                    $sql .= " AND (" . implode(' OR ', $jt_clauses) . ")";
                }
            } else {
                $sql .= " AND j.`job_type` LIKE :jt";
                $params[':jt'] = '%' . trim($job_type) . '%';
            }
        }

        if ($employer_type) {
            $sql .= " AND ep.`employer_type` = :employer_type";
            $params[':employer_type'] = $employer_type;
        }

        if ($work_setup) {
            $sql .= " AND j.`work_setup` = :work_setup";
            $params[':work_setup'] = $work_setup;
        }

        if ($department) {
            $dept_val = '%' . trim($department) . '%';
            $sql .= " AND (j.`department` LIKE :dept1 OR ep.`organization_name` LIKE :dept2)";
            $params[':dept1'] = $dept_val;
            $params[':dept2'] = $dept_val;
        }

        if ($pay_type) {
            $pt_val = '%' . trim($pay_type) . '%';
            $sql .= " AND (j.`pay_type` LIKE :pt1 OR j.`pay_rate` LIKE :pt2)";
            $params[':pt1'] = $pt_val;
            $params[':pt2'] = $pt_val;
        }

        if ($keyword) {
            $kw = '%' . trim($keyword) . '%';
            $sql .= " AND (j.`title` LIKE :kw1 OR j.`department` LIKE :kw2 OR ep.`organization_name` LIKE :kw3 OR j.`description` LIKE :kw4 OR j.`location` LIKE :kw5 OR j.`tags` LIKE :kw6 OR c.`name` LIKE :kw7)";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
            $params[':kw5'] = $kw;
            $params[':kw6'] = $kw;
            $params[':kw7'] = $kw;
        }

        $sql .= " ORDER BY j.`id` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map('hydrate_job', $rows);
    } catch (Exception $e) {
        error_log("get_jobs error: " . $e->getMessage());
        return [];
    }
}

function get_job_by_id($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                c.`name` AS `category`,
                COALESCE(ep.`organization_name`, j.`department`) AS `organization_name`,
                u.`name` AS `employer_name`,
                COALESCE(ep.`employer_type`, 'university_office') AS `employer_type`,
                CASE WHEN ep.`verification_status` = 'verified' OR u.`role` = 'admin' THEN 1 ELSE 0 END AS `verified_employer`
            FROM `jobs` j
            LEFT JOIN `categories` c ON j.`category_id` = c.`id`
            LEFT JOIN `users` u ON j.`employer_id` = u.`id`
            LEFT JOIN `employer_profiles` ep ON j.`employer_id` = ep.`user_id`
            WHERE j.`id` = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_job($row) : null;
    } catch (Exception $e) {
        error_log("get_job_by_id error: " . $e->getMessage());
        return null;
    }
}

function create_job($data, $photo_file = null) {
    try {
        $pdo = get_db_connection();
        $user = get_logged_user();
        $employer_type = $user['employer_type'] ?? ($data['employer_type'] ?? 'university_office');
        $org_name = $user['organization_name'] ?? ($user['department'] ?? ($data['department'] ?? 'Campus Department'));
        $work_setup = $data['work_setup'] ?? 'On-Campus';
        $job_type = $data['job_type'] ?? 'Student Assistant';
        $vacancies_count = max(1, (int)($data['vacancies'] ?? 1));

        // Handle optional photo upload
        $image_path = null;
        if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $image_path = save_uploaded_job_photo($photo_file);
        }
        if (empty($image_path) && !empty($data['image'])) {
            $image_path = $data['image'];
        }

        $tags = !empty($data['tags']) ? (is_array($data['tags']) ? $data['tags'] : explode(',', $data['tags'])) : [$job_type, $work_setup, $employer_type === 'university_office' ? 'University Office' : 'Approved Partner'];
        $badges = [$job_type, $work_setup];
        $responsibilities = !empty($data['responsibilities']) ? (is_array($data['responsibilities']) ? $data['responsibilities'] : array_filter(array_map('trim', explode("\n", $data['responsibilities'])))) : [];
        $qualifications = !empty($data['qualifications']) ? (is_array($data['qualifications']) ? $data['qualifications'] : array_filter(array_map('trim', explode("\n", $data['qualifications'])))) : [];

        // Dynamically resolve category_id from category name if not provided
        $category_name = $data['category'] ?? 'Administrative & Clerical';
        $category_id = (int)($data['category_id'] ?? 0);
        if ($category_id <= 0) {
            $stmt_cat = $pdo->prepare("SELECT `id` FROM `categories` WHERE `name` = :cname LIMIT 1");
            $stmt_cat->execute([':cname' => $category_name]);
            $found_id = $stmt_cat->fetchColumn();
            $category_id = $found_id ? (int)$found_id : 3;
        }

        $stmt = $pdo->prepare("
            INSERT INTO `jobs` (
                `title`, `department`, `category_id`,
                `employer_id`, `job_type`, `work_setup`,
                `location`, `pay_rate`, `pay_type`, `hours_per_week`,
                `vacancies`, `slots_total`, `slots_filled`, `deadline`, `status`, `image`,
                `tags`, `badges`, `description`, `responsibilities`, `qualifications`, `created_at`
            ) VALUES (
                :title, :department, :category_id,
                :employer_id, :job_type, :work_setup,
                :location, :pay_rate, :pay_type, :hours_per_week,
                :vacancies, :slots_total, 0, :deadline, 'active', :image,
                :tags, :badges, :description, :responsibilities, :qualifications, NOW()
            )
        ");

        $stmt->execute([
            ':title'             => $data['title'] ?? '',
            ':department'        => $data['department'] ?? $org_name,
            ':category_id'       => $category_id,
            ':employer_id'       => (int)($user['id'] ?? 0),
            ':job_type'          => $job_type,
            ':work_setup'        => $work_setup,
            ':location'          => $data['location'] ?? 'Campus Main Office',
            ':pay_rate'          => $data['pay_rate'] ?? '₱80.00 / hour',
            ':pay_type'          => $data['pay_type'] ?? 'Hourly',
            ':hours_per_week'    => $data['hours_per_week'] ?? '10 - 20 hrs/week',
            ':vacancies'         => $vacancies_count,
            ':slots_total'       => $vacancies_count,
            ':deadline'          => !empty($data['deadline']) ? $data['deadline'] : date('Y-m-d', strtotime('+30 days')),
            ':image'             => $image_path,
            ':tags'              => json_encode($tags),
            ':badges'            => json_encode($badges),
            ':description'       => $data['description'] ?? '',
            ':responsibilities'  => json_encode($responsibilities),
            ':qualifications'    => json_encode($qualifications)
        ]);

        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_job error: " . $e->getMessage());
        return 0;
    }
}

function update_job($id, $data, $photo_file = null) {
    try {
        $pdo = get_db_connection();
        $existing = get_job_by_id($id);
        if (!$existing) return false;

        $image_path = $existing['image'];
        if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $new_img = save_uploaded_job_photo($photo_file);
            if ($new_img) $image_path = $new_img;
        } elseif (!empty($data['remove_photo'])) {
            $image_path = null;
        } elseif (isset($data['image'])) {
            $image_path = $data['image'];
        }

        $responsibilities = isset($data['responsibilities']) ? (is_array($data['responsibilities']) ? $data['responsibilities'] : array_filter(array_map('trim', explode("\n", $data['responsibilities'])))) : $existing['responsibilities'];
        $qualifications = isset($data['qualifications']) ? (is_array($data['qualifications']) ? $data['qualifications'] : array_filter(array_map('trim', explode("\n", $data['qualifications'])))) : $existing['qualifications'];

        $vacancies_count = max(1, (int)($data['vacancies'] ?? $existing['vacancies']));

        // Dynamically resolve category_id from category name
        $category_name = $data['category'] ?? $existing['category'];
        $category_id = (int)($data['category_id'] ?? 0);
        if ($category_id <= 0) {
            $stmt_cat = $pdo->prepare("SELECT `id` FROM `categories` WHERE `name` = :cname LIMIT 1");
            $stmt_cat->execute([':cname' => $category_name]);
            $found_id = $stmt_cat->fetchColumn();
            $category_id = $found_id ? (int)$found_id : ($existing['category_id'] ?? 3);
        }

        $stmt = $pdo->prepare("
            UPDATE `jobs` SET
                `title` = :title,
                `department` = :department,
                `category_id` = :category_id,
                `job_type` = :job_type,
                `work_setup` = :work_setup,
                `location` = :location,
                `pay_rate` = :pay_rate,
                `hours_per_week` = :hours_per_week,
                `vacancies` = :vacancies,
                `slots_total` = :slots_total,
                `deadline` = :deadline,
                `status` = :status,
                `description` = :description,
                `image` = :image,
                `responsibilities` = :responsibilities,
                `qualifications` = :qualifications,
                `updated_at` = NOW()
            WHERE `id` = :id
        ");

        $stmt->execute([
            ':title'            => $data['title'] ?? $existing['title'],
            ':department'       => $data['department'] ?? $existing['department'],
            ':category_id'      => $category_id,
            ':job_type'         => $data['job_type'] ?? $existing['job_type'],
            ':work_setup'       => $data['work_setup'] ?? $existing['work_setup'],
            ':location'         => $data['location'] ?? $existing['location'],
            ':pay_rate'         => $data['pay_rate'] ?? $existing['pay_rate'],
            ':hours_per_week'   => $data['hours_per_week'] ?? $existing['hours_per_week'],
            ':vacancies'        => $vacancies_count,
            ':slots_total'      => $vacancies_count,
            ':deadline'         => $data['deadline'] ?? $existing['deadline'],
            ':status'           => $data['status'] ?? $existing['status'],
            ':description'      => $data['description'] ?? $existing['description'],
            ':image'            => $image_path,
            ':responsibilities' => json_encode($responsibilities),
            ':qualifications'   => json_encode($qualifications),
            ':id'               => (int)$id
        ]);

        return true;
    } catch (Exception $e) {
        error_log("update_job error: " . $e->getMessage());
        return false;
    }
}

function delete_job($id) {
    try {
        $pdo = get_db_connection();
        $pdo->beginTransaction();

        // 1. Delete dependent applications for this job to prevent orphaned candidate applications
        $stmt_app = $pdo->prepare("DELETE FROM `applications` WHERE `job_id` = :job_id");
        $stmt_app->execute([':job_id' => (int)$id]);

        // 2. Delete the job record
        $stmt = $pdo->prepare("DELETE FROM `jobs` WHERE `id` = :id");
        $stmt->execute([':id' => (int)$id]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("delete_job error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// APPLICATIONS MANAGEMENT
// ============================================================================

function get_applications($student_id = null, $job_id = null, $department = null, $employer_id = null) {
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

        if ($student_id !== null && $student_id !== '') {
            $sql .= " AND a.`student_id` = :student_id";
            $params[':student_id'] = (int)$student_id;
        }
        if ($job_id) {
            $sql .= " AND a.`job_id` = :job_id";
            $params[':job_id'] = (int)$job_id;
        }
        if ($department) {
            $sql .= " AND j.`department` LIKE :dept";
            $params[':dept'] = '%' . trim($department) . '%';
        }
        if ($employer_id !== null) {
            $sql .= " AND j.`employer_id` = :emp_id";
            $params[':emp_id'] = (int)$employer_id;
        }

        $sql .= " ORDER BY a.`applied_at` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map('hydrate_application', $rows);
    } catch (Exception $e) {
        error_log("get_applications error: " . $e->getMessage());
        return [];
    }
}

function get_application_by_id($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
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
            WHERE a.`id` = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_application($row) : null;
    } catch (Exception $e) {
        error_log("get_application_by_id error: " . $e->getMessage());
        return null;
    }
}

function create_application($data) {
    try {
        $pdo = get_db_connection();
        $user = get_logged_user();
        $job = get_job_by_id($data['job_id'] ?? 0);

        if (!$job) {
            return ['success' => false, 'message' => 'Job posting not found.'];
        }

        // 1. Enforce Job Status Gating (closed, paused, etc.)
        if (strtolower($job['status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'This job vacancy has been closed or paused and is no longer accepting applications.'];
        }

        // 2. Enforce Application Deadline Gating
        if (!empty($job['deadline'])) {
            $today = strtotime(date('Y-m-d'));
            $deadline = strtotime($job['deadline']);
            if ($deadline && $deadline < $today) {
                return ['success' => false, 'message' => 'The application deadline for this position has passed.'];
            }
        }

        // 3. Enforce Vacancy Slot Gating
        $slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
        $slots_filled = (int)($job['slots_filled'] ?? 0);
        if ($slots_total > 0 && $slots_filled >= $slots_total) {
            return ['success' => false, 'message' => 'All available vacancy slots for this position have already been filled.'];
        }

        $student_id = (int)($user['id'] ?? 0);
        if ($student_id <= 0) {
            return ['success' => false, 'message' => 'Invalid student authentication session.'];
        }

        // 4. Duplicate Check
        $check_stmt = $pdo->prepare("SELECT `id` FROM `applications` WHERE `job_id` = :job_id AND `student_id` = :student_id LIMIT 1");
        $check_stmt->execute([
            ':job_id'     => $job['id'],
            ':student_id' => $student_id
        ]);
        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'You have already submitted an application for this position.'];
        }

        $availability = !empty($data['availability']) ? (is_array($data['availability']) ? $data['availability'] : explode(',', $data['availability'])) : ['Flexible Weekdays'];
        $resume_file = !empty($data['resume_file']) ? htmlspecialchars($data['resume_file']) : ('Student_Resume_' . $student_id . '.pdf');
        $study_load = 'Study_Load_' . $student_id . '.pdf';

        $stmt = $pdo->prepare("
            INSERT INTO `applications` (
                `job_id`, `student_id`, `cover_letter`, `availability`,
                `resume_file`, `study_load_file`, `status`,
                `supervisor_notes`, `applied_at`, `updated_at`
            ) VALUES (
                :job_id, :student_id, :cover_letter, :availability,
                :resume_file, :study_load_file, 'pending',
                'Application submitted and queued for evaluation.', NOW(), NOW()
            )
        ");

        $stmt->execute([
            ':job_id'          => $job['id'],
            ':student_id'      => $student_id,
            ':cover_letter'    => $data['cover_letter'] ?? '',
            ':availability'    => json_encode($availability),
            ':resume_file'     => $resume_file,
            ':study_load_file' => $study_load
        ]);

        $new_id = (int)$pdo->lastInsertId();

        // Dispatch notification to hiring employer
        if ($new_id > 0 && !empty($job['employer_id'])) {
            $student_disp = $user['name'] ?? 'A student';
            create_notification(
                (int)$job['employer_id'],
                'new_application',
                'New Candidate Application',
                "{$student_disp} submitted an application for '{$job['title']}'.",
                "employer/review-app.php?id={$new_id}",
                'bi-file-earmark-person',
                'info'
            );
        }

        return ['success' => true, 'id' => $new_id];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unique_student_job') !== false) {
            return ['success' => false, 'message' => 'You have already submitted an application for this position.'];
        }
        return ['success' => false, 'message' => 'Application failed: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Application failed: ' . $e->getMessage()];
    }
}

function update_application_status($id, $status, $notes = '', $interview_data = []) {
    try {
        $pdo = get_db_connection();
        $target_app = get_application_by_id($id);
        if (!$target_app) return false;

        $old_status = $target_app['status'] ?? 'pending';
        $job_id = (int)($target_app['job_id'] ?? 0);

        $badge_map = [
            'pending'             => 'warning',
            'under_review'        => 'primary',
            'interview_scheduled' => 'info',
            'accepted'            => 'success',
            'declined'            => 'danger'
        ];
        $label_map = [
            'pending'             => 'Pending Review',
            'under_review'        => 'Under Review',
            'interview_scheduled' => 'Interview Scheduled',
            'accepted'            => 'Accepted / Hired',
            'declined'            => 'Declined / Filled'
        ];

        $status_label = $label_map[$status] ?? ucfirst(str_replace('_', ' ', $status));
        $status_badge = $badge_map[$status] ?? 'secondary';

        $interview_date = null;
        $interview_time = null;
        $interview_venue = null;

        if ($status === 'interview_scheduled' && !empty($interview_data)) {
            $raw_date = trim($interview_data['date'] ?? '');
            if (!empty($raw_date) && strtotime($raw_date) < strtotime(date('Y-m-d'))) {
                return false; // Cannot schedule in the past
            }
            $interview_date = $raw_date ?: null;
            $interview_time = $interview_data['time'] ?? null;
            $interview_venue = trim($interview_data['venue'] ?? '');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE `applications` SET
                `status` = :status,
                `supervisor_notes` = :notes,
                `interview_date` = COALESCE(:interview_date, `interview_date`),
                `interview_time` = COALESCE(:interview_time, `interview_time`),
                `interview_venue` = COALESCE(:interview_venue, `interview_venue`),
                `updated_at` = NOW()
            WHERE `id` = :id
        ");

        $stmt->execute([
            ':status'          => $status,
            ':notes'           => $notes,
            ':interview_date'  => $interview_date,
            ':interview_time'  => $interview_time,
            ':interview_venue' => $interview_venue,
            ':id'              => (int)$id
        ]);

        // Synchronize slots_filled and job status
        if ($job_id > 0) {
            if ($old_status !== 'accepted' && $status === 'accepted') {
                // Capacity ceiling guard (Defect 2 Fix): prevent overfilling
                $stmt_check = $pdo->prepare("SELECT `slots_filled`, `slots_total`, `vacancies` FROM `jobs` WHERE `id` = :job_id FOR UPDATE");
                $stmt_check->execute([':job_id' => $job_id]);
                $job_quota = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if (!$job_quota) {
                    $pdo->rollBack();
                    return false;
                }

                $slots_max = max(1, (int)($job_quota['slots_total'] ?? 0), (int)($job_quota['vacancies'] ?? 0));
                $slots_curr = (int)($job_quota['slots_filled'] ?? 0);
                if ($slots_curr >= $slots_max) {
                    $pdo->rollBack();
                    return false; // Prevent overfilling
                }

                // Corrected SQL evaluation (Defect 1 Fix): remove redundant '+ 1' inside CASE clause
                $stmt_inc = $pdo->prepare("
                    UPDATE `jobs` SET
                        `slots_filled` = `slots_filled` + 1,
                        `status` = CASE WHEN `slots_filled` >= `slots_total` THEN 'closed' ELSE `status` END,
                        `updated_at` = NOW()
                    WHERE `id` = :job_id
                ");
                $stmt_inc->execute([':job_id' => $job_id]);
            } elseif ($old_status === 'accepted' && $status !== 'accepted') {
                // Decrement: evaluate status against new decremented value without double-decrementing
                $stmt_dec = $pdo->prepare("
                    UPDATE `jobs` SET
                        `slots_filled` = GREATEST(0, `slots_filled` - 1),
                        `status` = CASE WHEN `status` = 'closed' AND (`slots_filled` < `slots_total`) THEN 'active' ELSE `status` END,
                        `updated_at` = NOW()
                    WHERE `id` = :job_id
                ");
                $stmt_dec->execute([':job_id' => $job_id]);
            }
        }

        $pdo->commit();

        // Dispatch notification to student regarding application status
        $student_id = (int)($target_app['student_id'] ?? 0);
        $job_title = $target_app['job_title'] ?? 'Campus Job';
        if ($student_id > 0) {
            $notif_title = "Application Update: " . $job_title;
            $notif_icon = 'bi-bell';
            $notif_badge = 'primary';
            $notif_msg = "Your application for '{$job_title}' has been updated to {$status_label}.";

            if ($status === 'interview_scheduled') {
                $notif_title = "Interview Scheduled: " . $job_title;
                $notif_icon = 'bi-calendar-check-fill';
                $notif_badge = 'info';
                $date_str = $interview_date ?: 'TBA';
                $time_str = $interview_time ? ' at ' . $interview_time : '';
                $venue_str = $interview_venue ? ' (' . $interview_venue . ')' : '';
                $notif_msg = "You have an interview scheduled on {$date_str}{$time_str}{$venue_str}.";
            } elseif ($status === 'accepted') {
                $notif_title = "Application Accepted! 🎉";
                $notif_icon = 'bi-check-circle-fill';
                $notif_badge = 'success';
                $notif_msg = "Congratulations! You have been accepted for the position '{$job_title}'.";
            } elseif ($status === 'declined') {
                $notif_title = "Application Update: " . $job_title;
                $notif_icon = 'bi-x-circle';
                $notif_badge = 'secondary';
                $notif_msg = "The hiring supervisor has completed review for '{$job_title}'. The position has been filled or closed.";
            } elseif ($status === 'under_review') {
                $notif_title = "Application Under Review";
                $notif_icon = 'bi-hourglass-split';
                $notif_badge = 'primary';
                $notif_msg = "Your application for '{$job_title}' is now being evaluated by the hiring department.";
            }

            if (!empty($notes)) {
                $notif_msg .= " Supervisor Note: " . trim($notes);
            }

            create_notification(
                $student_id,
                'application_status',
                $notif_title,
                $notif_msg,
                'student/my-applications.php',
                $notif_icon,
                $notif_badge
            );
        }

        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("update_application_status error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// ACADEMIC INSTITUTES & TAXONOMY
// ============================================================================

function get_kld_institutes_and_courses() {
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

function get_kld_courses_flat() {
    $institutes = get_kld_institutes_and_courses();
    $flat = [];
    foreach ($institutes as $inst => $courses) {
        foreach ($courses as $c) {
            $flat[] = $c;
        }
    }
    return $flat;
}

// Categories
function get_categories() {
    try {
        $pdo = get_db_connection();
        $stmt_cat = $pdo->query("SELECT * FROM `categories` ORDER BY `id` ASC");
        $cats = array_map('hydrate_category', $stmt_cat->fetchAll());

        // Dynamic active job counts
        $stmt_counts = $pdo->query("
            SELECT j.`category_id`, c.`name` AS category_name, COUNT(*) AS cnt 
            FROM `jobs` j
            JOIN `categories` c ON j.`category_id` = c.`id`
            WHERE j.`status` = 'active' 
            GROUP BY j.`category_id`, c.`name`
        ");
        $counts_by_id = [];
        $counts_by_name = [];
        while ($row = $stmt_counts->fetch()) {
            $counts_by_id[(int)$row['category_id']] = (int)$row['cnt'];
            $counts_by_name[$row['category_name']] = (int)$row['cnt'];
        }

        foreach ($cats as &$cat) {
            $cat['job_count'] = $counts_by_id[(int)$cat['id']] ?? ($counts_by_name[$cat['name']] ?? 0);
        }

        return $cats;
    } catch (Exception $e) {
        error_log("get_categories error: " . $e->getMessage());
        return [];
    }
}

function create_category($data, $photo_file = null) {
    try {
        $pdo = get_db_connection();
        $name = trim($data['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $image_path = null;
        if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $image_path = save_uploaded_category_photo($photo_file);
        }
        if (empty($image_path) && !empty($data['image'])) {
            $image_path = trim($data['image']);
        }

        $stmt = $pdo->prepare("
            INSERT INTO `categories` (`name`, `slug`, `icon`, `description`, `theme`, `badge_tag`, `badge_icon`, `job_count`, `hourly_range`, `image`, `created_at`)
            VALUES (:name, :slug, :icon, :description, :theme, :badge_tag, :badge_icon, 0, :hourly_range, :image, NOW())
        ");

        $stmt->execute([
            ':name'         => $name,
            ':slug'         => $slug,
            ':icon'         => $data['icon'] ?? 'bi-briefcase',
            ':description'  => trim($data['description'] ?? ''),
            ':theme'        => $data['theme'] ?? 'kld-green',
            ':badge_tag'    => $data['badge_tag'] ?? null,
            ':badge_icon'   => $data['badge_icon'] ?? null,
            ':hourly_range' => $data['hourly_range'] ?? null,
            ':image'        => $image_path
        ]);

        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_category error: " . $e->getMessage());
        return 0;
    }
}

function update_category($id, $data, $photo_file = null) {
    try {
        $pdo = get_db_connection();
        $id = (int)$id;
        if ($id <= 0) return false;

        $name = trim($data['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $image_path = null;
        if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $image_path = save_uploaded_category_photo($photo_file);
        }
        if (empty($image_path) && isset($data['image']) && $data['image'] !== '') {
            $image_path = trim($data['image']);
        }

        $params = [
            ':id'           => $id,
            ':name'         => $name,
            ':slug'         => $slug,
            ':icon'         => $data['icon'] ?? 'bi-briefcase',
            ':description'  => trim($data['description'] ?? ''),
            ':theme'        => $data['theme'] ?? 'kld-green',
            ':badge_tag'    => $data['badge_tag'] ?? null,
            ':badge_icon'   => $data['badge_icon'] ?? null,
            ':hourly_range' => $data['hourly_range'] ?? null
        ];

        $image_clause = "";
        if ($image_path !== null) {
            $image_clause = ", `image` = :image";
            $params[':image'] = $image_path;
        }

        $sql = "
            UPDATE `categories`
            SET `name` = :name,
                `slug` = :slug,
                `icon` = :icon,
                `description` = :description,
                `theme` = :theme,
                `badge_tag` = :badge_tag,
                `badge_icon` = :badge_icon,
                `hourly_range` = :hourly_range
                {$image_clause}
            WHERE `id` = :id
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (Exception $e) {
        error_log("update_category error: " . $e->getMessage());
        return false;
    }
}

function delete_category($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM `categories` WHERE `id` = :id");
        return $stmt->execute([':id' => (int)$id]);
    } catch (Exception $e) {
        error_log("delete_category error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// METRICS & REPORTING
// ============================================================================

function get_metrics_total_active_jobs() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM `jobs` 
            WHERE `status` = 'active' 
              AND (`deadline` IS NULL OR `deadline` >= CURDATE())
        ");
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_metrics_partnered_offices() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT COUNT(DISTINCT `department`) FROM `jobs` WHERE `department` IS NOT NULL AND `department` != ''");
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_metrics_students_hired() {
    try {
        $pdo = get_db_connection();
        $stmt_jobs = $pdo->query("SELECT SUM(`slots_filled`) FROM `jobs`");
        $job_filled = (int)$stmt_jobs->fetchColumn();

        $stmt_apps = $pdo->query("SELECT COUNT(*) FROM `applications` WHERE `status` = 'accepted'");
        $app_accepted = (int)$stmt_apps->fetchColumn();

        return max($job_filled, $app_accepted);
    } catch (Exception $e) {
        return 0;
    }
}

function get_metrics_avg_hourly_pay() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT `pay_rate` FROM `jobs`");
        $rates = [];
        while ($row = $stmt->fetch()) {
            if (preg_match('/(\d+(?:\.\d+)?)/', $row['pay_rate'] ?? '', $matches)) {
                $rates[] = (float)$matches[1];
            }
        }
        if (empty($rates)) {
            return '₱85';
        }
        $avg = round(array_sum($rates) / count($rates));
        return '₱' . $avg;
    } catch (Exception $e) {
        return '₱85';
    }
}

// ============================================================================
// SYSTEM DATA MODE SWITCHING (Demo / Real Toggle)
// ============================================================================

function get_system_data_mode() {
    $mode_file = DATA_DIR . '/system_mode.json';
    if (file_exists($mode_file)) {
        $data = json_decode(file_get_contents($mode_file), true);
        return ($data['active_mode'] ?? 'demo');
    }
    return 'demo';
}

function switch_system_data_mode($mode, $switched_by = 'User') {
    $seed_dir = DATA_DIR . '/seeds/' . $mode;
    if (!is_dir($seed_dir)) {
        return false;
    }

    // 1. Sync seed files to data/
    $data_files = ['users.json', 'jobs.json', 'applications.json', 'categories.json',
                   'profile_requests.json', 'updates.json', 'devblogs.json'];

    foreach ($data_files as $file) {
        $src = $seed_dir . '/' . $file;
        $dst = DATA_DIR . '/' . $file;
        if (file_exists($src)) {
            copy($src, $dst);
        }
    }

    // 2. Re-import into MySQL
    try {
        require_once dirname(__DIR__) . '/database/migrate.php';
        execute_migration_and_seed(false, DATA_DIR, false, true);
    } catch (Exception $e) {
        error_log("switch_system_data_mode db error: " . $e->getMessage());
    }

    // Record mode change
    $mode_data = [
        'active_mode' => $mode,
        'last_switched_at' => date('Y-m-d H:i:s'),
        'switched_by' => $switched_by
    ];
    file_put_contents(DATA_DIR . '/system_mode.json', json_encode($mode_data, JSON_PRETTY_PRINT));

    // Clear session user
    unset($_SESSION['user']);
    unset($_SESSION['flash']);

    return true;
}

function reset_current_data_mode($switched_by = 'User') {
    $current_mode = get_system_data_mode();
    return switch_system_data_mode($current_mode, $switched_by);
}

function wipe_real_data_fresh() {
    return switch_system_data_mode('real', 'System Wipe');
}

function reset_demo_data() {
    switch_system_data_mode('demo', 'System Reset');
    set_flash('info', 'Demo dataset has been reset to default campus state.');
}

// ============================================================================
// CAREER CENTER UPDATES & BLOG ENGINE
// ============================================================================

function get_career_updates() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("
            SELECT 
                up.*,
                COALESCE(u.name, 'Career Development Office') AS author_name,
                COALESCE(ep.office_location, u.role, 'Coordinator') AS author_role,
                COALESCE(ep.organization_name, 'KLD Career Development & Placement Office') AS author_office
            FROM `updates` up
            LEFT JOIN `users` u ON u.id = up.author_id
            LEFT JOIN `employer_profiles` ep ON ep.user_id = u.id
            ORDER BY up.`published_at` DESC, up.`id` DESC
        ");
        $rows = $stmt->fetchAll();
        return array_map('hydrate_update', $rows);
    } catch (Exception $e) {
        error_log("get_career_updates error: " . $e->getMessage());
        return [];
    }
}

function get_career_update_by_id($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                up.*,
                COALESCE(u.name, 'Career Development Office') AS author_name,
                COALESCE(ep.office_location, u.role, 'Coordinator') AS author_role,
                COALESCE(ep.organization_name, 'KLD Career Development & Placement Office') AS author_office
            FROM `updates` up
            LEFT JOIN `users` u ON u.id = up.author_id
            LEFT JOIN `employer_profiles` ep ON ep.user_id = u.id
            WHERE up.`id` = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_update($row) : null;
    } catch (Exception $e) {
        error_log("get_career_update_by_id error: " . $e->getMessage());
        return null;
    }
}

function get_latest_career_updates($limit = 3, $exclude_id = null) {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                up.*,
                COALESCE(u.name, 'Career Development Office') AS author_name,
                COALESCE(ep.office_location, u.role, 'Coordinator') AS author_role,
                COALESCE(ep.organization_name, 'KLD Career Development & Placement Office') AS author_office
            FROM `updates` up
            LEFT JOIN `users` u ON u.id = up.author_id
            LEFT JOIN `employer_profiles` ep ON ep.user_id = u.id
        ";
        $params = [];
        if ($exclude_id !== null) {
            $sql .= " WHERE up.`id` != :ex_id";
            $params[':ex_id'] = (int)$exclude_id;
        }
        $sql .= " ORDER BY up.`published_at` DESC, up.`id` DESC LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map('hydrate_update', $rows);
    } catch (Exception $e) {
        error_log("get_latest_career_updates error: " . $e->getMessage());
        return [];
    }
}

function add_career_update($data) {
    try {
        $pdo = get_db_connection();
        $title = trim($data['title'] ?? 'Campus Career Dispatch');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        $content = trim($data['content'] ?? '');
        $word_count = str_word_count(strip_tags($content));
        $read_time = max(1, ceil($word_count / 200)) . ' min read';

        $author_id = isset($data['author_id']) ? (int)$data['author_id'] : (int)($_SESSION['user']['id'] ?? 5);
        $author_name = trim($data['author_name'] ?? 'Career Development Office');
        
        $initials = '';
        $name_parts = explode(' ', $author_name);
        foreach ($name_parts as $np) {
            if (!empty($np)) $initials .= strtoupper(substr($np, 0, 1));
        }
        $initials = substr($initials, 0, 2) ?: 'CC';

        $image = !empty($data['image']) ? trim($data['image']) : 'assets/img/updates/update-01.jpg';
        $summary = trim($data['summary'] ?? (substr(strip_tags($content), 0, 160) . '...'));

        $stmt = $pdo->prepare("
            INSERT INTO `updates` (
                `slug`, `title`, `category`, `published_at`, `read_time`,
                `author_id`, `author_avatar`, `image`, `summary`, `content`, `created_at`
            ) VALUES (
                :slug, :title, :category, :published_at, :read_time,
                :author_id, :author_avatar, :image, :summary, :content, NOW()
            )
        ");

        $stmt->execute([
            ':slug'          => $slug,
            ':title'         => $title,
            ':category'      => trim($data['category'] ?? 'Campus News'),
            ':published_at'  => $data['published_at'] ?? date('Y-m-d H:i:s'),
            ':read_time'     => $data['read_time'] ?? $read_time,
            ':author_id'     => $author_id,
            ':author_avatar' => $initials,
            ':image'         => $image,
            ':summary'       => $summary,
            ':content'       => $content
        ]);

        $new_id = (int)$pdo->lastInsertId();
        return get_career_update_by_id($new_id);
    } catch (Exception $e) {
        error_log("add_career_update error: " . $e->getMessage());
        return null;
    }
}

function update_career_update($id, $data) {
    try {
        $pdo = get_db_connection();
        $existing = get_career_update_by_id($id);
        if (!$existing) return false;

        $updates = [];
        $params = [':id' => (int)$id];

        if (isset($data['title'])) {
            $updates[] = "`title` = :title";
            $updates[] = "`slug` = :slug";
            $params[':title'] = trim($data['title']);
            $params[':slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']), '-'));
        }
        if (isset($data['category'])) {
            $updates[] = "`category` = :category";
            $params[':category'] = trim($data['category']);
        }
        if (isset($data['summary'])) {
            $updates[] = "`summary` = :summary";
            $params[':summary'] = trim($data['summary']);
        }
        if (isset($data['content'])) {
            $updates[] = "`content` = :content";
            $updates[] = "`read_time` = :read_time";
            $params[':content'] = trim($data['content']);
            $word_count = str_word_count(strip_tags($data['content']));
            $params[':read_time'] = max(1, ceil($word_count / 200)) . ' min read';
        }
        if (isset($data['image']) && !empty($data['image'])) {
            $updates[] = "`image` = :image";
            $params[':image'] = trim($data['image']);
        }
        if (isset($data['author_id'])) {
            $updates[] = "`author_id` = :author_id";
            $params[':author_id'] = (int)$data['author_id'];
        }

        if (!empty($updates)) {
            $updates[] = "`updated_at` = NOW()";
            $sql = "UPDATE `updates` SET " . implode(', ', $updates) . " WHERE `id` = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        return true;
    } catch (Exception $e) {
        error_log("update_career_update error: " . $e->getMessage());
        return false;
    }
}

function delete_career_update($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM `updates` WHERE `id` = :id");
        $stmt->execute([':id' => (int)$id]);
        return true;
    } catch (Exception $e) {
        error_log("delete_career_update error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// DEVBLOG & SPRINT CHRONICLES ENGINE
// ============================================================================

function get_devblogs() {
    $file = __DIR__ . '/../data/devblogs.json';
    if (file_exists($file)) {
        $json = json_decode(file_get_contents($file), true);
        if (is_array($json) && !empty($json)) {
            return $json;
        }
    }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT * FROM `devblogs` ORDER BY `sprint_number` ASC");
        $rows = $stmt->fetchAll();
        return array_map('hydrate_devblog', $rows);
    } catch (Exception $e) {
        error_log("get_devblogs error: " . $e->getMessage());
        return [];
    }
}

function get_devblog_by_id($id) {
    $blogs = get_devblogs();
    foreach ($blogs as $b) {
        if (($b['id'] ?? '') === (string)$id || ($b['sprint_number'] ?? '') === (string)$id) {
            return $b;
        }
    }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM `devblogs` WHERE `id` = :id OR `sprint_number` = :sprint LIMIT 1");
        $stmt->execute([':id' => (string)$id, ':sprint' => (string)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_devblog($row) : null;
    } catch (Exception $e) {
        error_log("get_devblog_by_id error: " . $e->getMessage());
        return null;
    }
}

function update_user_password($user_id, $hashed_password) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE `users` SET `password` = :pass, `updated_at` = NOW() WHERE `id` = :id");
        $stmt->execute([':pass' => $hashed_password, ':id' => (int)$user_id]);
        if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $user_id) {
            $_SESSION['user']['password'] = $hashed_password;
        }
        return true;
    } catch (Exception $e) {
        error_log("update_user_password error: " . $e->getMessage());
        return false;
    }
}

function delete_application($id, $student_id = null) {
    try {
        $pdo = get_db_connection();
        if ($student_id) {
            $stmt = $pdo->prepare("DELETE FROM `applications` WHERE `id` = :id AND `student_id` = :sid");
            $stmt->execute([':id' => (int)$id, ':sid' => (int)$student_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM `applications` WHERE `id` = :id");
            $stmt->execute([':id' => (int)$id]);
        }
        return true;
    } catch (Exception $e) {
        error_log("delete_application error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// NOTIFICATIONS API & DATA HELPERS
// ============================================================================

function create_notification($user_id, $type, $title, $message, $link = null, $icon = 'bi-bell', $badge_color = 'primary') {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `icon`, `badge_color`, `is_read`, `created_at`)
            VALUES (:user_id, :type, :title, :message, :link, :icon, :badge_color, 0, NOW())
        ");
        $stmt->execute([
            ':user_id'     => (int)$user_id,
            ':type'        => substr($type, 0, 50),
            ':title'       => substr($title, 0, 255),
            ':message'     => $message,
            ':link'        => $link ? substr($link, 0, 255) : null,
            ':icon'        => substr($icon, 0, 100),
            ':badge_color' => substr($badge_color, 0, 50)
        ]);
        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_notification error: " . $e->getMessage());
        return 0;
    }
}

function notify_all_admins($type, $title, $message, $link = null, $icon = 'bi-shield-check', $badge_color = 'warning') {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT `id` FROM `users` WHERE `role` = 'admin'");
        $admin_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $count = 0;
        foreach ($admin_ids as $aid) {
            if (create_notification($aid, $type, $title, $message, $link, $icon, $badge_color)) {
                $count++;
            }
        }
        return $count;
    } catch (Exception $e) {
        error_log("notify_all_admins error: " . $e->getMessage());
        return 0;
    }
}

function get_user_notifications($user_id, $limit = 30, $unread_only = false) {
    try {
        $pdo = get_db_connection();
        $sql = "SELECT * FROM `notifications` WHERE `user_id` = :user_id";
        if ($unread_only) {
            $sql .= " AND `is_read` = 0";
        }
        $sql .= " ORDER BY `created_at` DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map('hydrate_notification', $rows);
    } catch (Exception $e) {
        error_log("get_user_notifications error: " . $e->getMessage());
        return [];
    }
}

function get_unread_notifications_count($user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `notifications` WHERE `user_id` = :user_id AND `is_read` = 0");
        $stmt->execute([':user_id' => (int)$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("get_unread_notifications_count error: " . $e->getMessage());
        return 0;
    }
}

function mark_notification_as_read($notification_id, $user_id = null) {
    try {
        $pdo = get_db_connection();
        if ($user_id !== null) {
            $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `id` = :id AND `user_id` = :user_id");
            return $stmt->execute([':id' => (int)$notification_id, ':user_id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$notification_id]);
        }
    } catch (Exception $e) {
        error_log("mark_notification_as_read error: " . $e->getMessage());
        return false;
    }
}

function mark_all_notifications_as_read($user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `user_id` = :user_id AND `is_read` = 0");
        return $stmt->execute([':user_id' => (int)$user_id]);
    } catch (Exception $e) {
        error_log("mark_all_notifications_as_read error: " . $e->getMessage());
        return false;
    }
}

function delete_notification($notification_id, $user_id = null) {
    try {
        $pdo = get_db_connection();
        if ($user_id !== null) {
            $stmt = $pdo->prepare("DELETE FROM `notifications` WHERE `id` = :id AND `user_id` = :user_id");
            return $stmt->execute([':id' => (int)$notification_id, ':user_id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM `notifications` WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$notification_id]);
        }
    } catch (Exception $e) {
        error_log("delete_notification error: " . $e->getMessage());
        return false;
    }
}

function get_notification_by_id($notification_id, $user_id = null) {
    try {
        $pdo = get_db_connection();
        if ($user_id !== null) {
            $stmt = $pdo->prepare("SELECT * FROM `notifications` WHERE `id` = :id AND `user_id` = :user_id LIMIT 1");
            $stmt->execute([':id' => (int)$notification_id, ':user_id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `notifications` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$notification_id]);
        }
        $row = $stmt->fetch();
        return $row ? hydrate_notification($row) : null;
    } catch (Exception $e) {
        error_log("get_notification_by_id error: " . $e->getMessage());
        return null;
    }
}

function time_ago_short($datetime) {
    if (!$datetime) return '';
    $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    if (!$timestamp) return '';
    $diff = time() - $timestamp;
    if ($diff < 0) $diff = 0;
    if ($diff < 60) return '1m ago';
    if ($diff < 3600) return max(1, floor($diff / 60)) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $timestamp);
}

// Legacy JSON Compatibility Shims
function load_json_file($filename) {
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

function save_json_file($filename, $data) {
    $path = DATA_DIR . '/' . $filename;
    if (is_dir(DATA_DIR)) {
        @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
