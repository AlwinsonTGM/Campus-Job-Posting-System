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
        $row['availability'] = json_decode($row['availability'], true) ?: [];
    } elseif (!isset($row['availability'])) {
        $row['availability'] = [];
    }
    $row['id'] = (int)$row['id'];
    if (isset($row['age'])) $row['age'] = $row['age'] !== null ? (int)$row['age'] : null;
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
        $row['availability'] = json_decode($row['availability'], true) ?: [];
    } elseif (!isset($row['availability'])) {
        $row['availability'] = [];
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

function get_all_users($role = null, $keyword = null, $emp_type = null, $ver_status = null) {
    try {
        $pdo = get_db_connection();
        $sql = "SELECT * FROM `users` WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND `role` = :role";
            $params[':role'] = $role;
        }
        if ($emp_type) {
            $sql .= " AND `employer_type` = :emp_type";
            $params[':emp_type'] = $emp_type;
        }
        if ($ver_status) {
            $sql .= " AND `verification_status` = :ver_status";
            $params[':ver_status'] = $ver_status;
        }
        if ($keyword) {
            $kw_val = '%' . trim($keyword) . '%';
            $sql .= " AND (`name` LIKE :kw1 OR `email` LIKE :kw2 OR `student_id` LIKE :kw3 OR `department` LIKE :kw4 OR `organization_name` LIKE :kw5)";
            $params[':kw1'] = $kw_val;
            $params[':kw2'] = $kw_val;
            $params[':kw3'] = $kw_val;
            $params[':kw4'] = $kw_val;
            $params[':kw5'] = $kw_val;
        }

        $sql .= " ORDER BY `id` ASC";
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
        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = :id LIMIT 1");
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
        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE LOWER(`email`) = LOWER(:email) LIMIT 1");
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
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `role` = :role ORDER BY `id` ASC LIMIT 1");
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

function update_user_verification($id, $status, $notes = '') {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            UPDATE `users` 
            SET `verification_status` = :status, `rejection_reason` = :notes, `updated_at` = NOW() 
            WHERE `id` = :id
        ");
        $stmt->execute([
            ':status' => $status,
            ':notes'  => $notes,
            ':id'     => (int)$id
        ]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("update_user_verification error: " . $e->getMessage());
        return false;
    }
}

function update_employer_verification($id, $status, $notes = '') {
    return update_user_verification($id, $status, $notes);
}

function register_user($data, $permit_file = null, $proof_file = null) {
    try {
        $pdo = get_db_connection();
        $email = strtolower(trim($data['email'] ?? ''));

        // Check if email already exists
        $check_stmt = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(`email`) = LOWER(:email) LIMIT 1");
        $check_stmt->execute([':email' => $email]);
        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'Email address is already registered.'];
        }

        // Strictly prevent public registration of admin accounts
        $allowed_roles = ['student', 'employer'];
        $role = in_array($data['role'] ?? '', $allowed_roles, true) ? $data['role'] : 'student';

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
            // New student registrations start in pending_approval for admin acceptance
            $verification = 'pending_approval';
            $accreditation = $data['student_id'] ?? ('STUDENT-' . rand(10000, 99999));
        }

        $sex = $data['sex'] ?? ($role === 'student' ? 'Male' : '');
        $birthdate = (!empty($data['birthdate']) && $data['birthdate'] !== '0000-00-00') ? $data['birthdate'] : null;
        $age = !empty($birthdate) ? calculate_age($birthdate) : ($data['age'] ?? ($role === 'student' ? 20 : null));
        $year_level = $data['year_level'] ?? '1st Year';
        $proof_path = $proof_file ?? ($data['proof_file'] ?? null);
        $permit_path = $permit_file ?? ($data['permit_file'] ?? null);

        $stmt = $pdo->prepare("
            INSERT INTO `users` (
                `name`, `email`, `password`, `role`, `employer_type`, `organization_name`,
                `student_id`, `department`, `course`, `year_level`, `sex`, `birthdate`,
                `age`, `phone`, `office_location`, `accreditation_number`, `business_permit`,
                `registration_proof`, `verification_status`, `rejection_reason`, `status`, `created_at`
            ) VALUES (
                :name, :email, :password, :role, :employer_type, :organization_name,
                :student_id, :department, :course, :year_level, :sex, :birthdate,
                :age, :phone, :office_location, :accreditation_number, :business_permit,
                :registration_proof, :verification_status, '', 'active', NOW()
            )
        ");

        $raw_pass = $data['password'] ?? 'Password123!';
        $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);

        $stmt->execute([
            ':name'                 => htmlspecialchars($data['name'] ?? ''),
            ':email'                => $email,
            ':password'             => $hashed_pass,
            ':role'                 => $role,
            ':employer_type'        => $employer_type,
            ':organization_name'    => htmlspecialchars($org_name),
            ':student_id'           => $data['student_id'] ?? ('2026-' . rand(10000, 99999)),
            ':department'           => $data['department'] ?? 'General Academics',
            ':course'               => $data['course'] ?? '',
            ':year_level'           => $year_level,
            ':sex'                  => $sex,
            ':birthdate'            => $birthdate,
            ':age'                  => $age,
            ':phone'                => $data['phone'] ?? '',
            ':office_location'      => $data['office_location'] ?? 'Campus Main Office',
            ':accreditation_number' => $accreditation,
            ':business_permit'      => $permit_path,
            ':registration_proof'   => $proof_path,
            ':verification_status'  => $verification
        ]);

        $new_id = (int)$pdo->lastInsertId();
        $new_user = get_user_by_id($new_id);
        if ($new_user) unset($new_user['password']);
        $_SESSION['user'] = $new_user;

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
        $sql = "SELECT * FROM `profile_requests` WHERE 1=1";
        $params = [];

        if ($user_id) {
            $sql .= " AND `user_id` = :user_id";
            $params[':user_id'] = (int)$user_id;
        }
        if ($status) {
            $sql .= " AND `status` = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY `created_at` DESC";
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

        $current_profile = [
            'name'       => $current_user['name'] ?? '',
            'department' => $current_user['department'] ?? '',
            'course'     => $current_user['course'] ?? '',
            'year_level' => $current_user['year_level'] ?? '1st Year',
            'sex'        => $current_user['sex'] ?? 'Male',
            'birthdate'  => $current_user['birthdate'] ?? '',
            'age'        => $current_user['age'] ?? 20,
        ];

        $requested_profile = [
            'name'       => htmlspecialchars($requested_data['name'] ?? $current_user['name']),
            'department' => htmlspecialchars($requested_data['department'] ?? $current_user['department']),
            'course'     => htmlspecialchars($requested_data['course'] ?? $current_user['course']),
            'year_level' => htmlspecialchars($requested_data['year_level'] ?? $current_user['year_level']),
            'sex'        => htmlspecialchars($requested_data['sex'] ?? ($current_user['sex'] ?? 'Male')),
            'birthdate'  => htmlspecialchars($req_birthdate),
            'age'        => $req_age,
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `profile_requests` (
                `user_id`, `user_name`, `user_email`, `student_id`, `current_profile`,
                `requested_profile`, `proof_file`, `reason`, `status`, `admin_notes`,
                `dismissed_by_user`, `created_at`
            ) VALUES (
                :user_id, :user_name, :user_email, :student_id, :current_profile,
                :requested_profile, :proof_file, :reason, 'pending', '', 0, NOW()
            )
        ");

        $stmt->execute([
            ':user_id'           => (int)$user_id,
            ':user_name'         => $current_user['name'],
            ':user_email'        => $current_user['email'],
            ':student_id'        => $current_user['student_id'] ?? '2024-00123',
            ':current_profile'   => json_encode($current_profile),
            ':requested_profile' => json_encode($requested_profile),
            ':proof_file'        => $proof_file,
            ':reason'            => htmlspecialchars($reason)
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
            'reason'            => htmlspecialchars($reason),
            'status'            => 'pending',
            'admin_notes'       => '',
            'dismissed_by_user' => false,
            'created_at'        => date('Y-m-d H:i:s'),
            'resolved_at'       => null
        ];

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

        // Update user record
        $user_updates = [];
        $user_params = [':id' => $user_id];

        if (!empty($requested['name'])) { $user_updates[] = "`name` = :name"; $user_params[':name'] = $requested['name']; }
        if (!empty($requested['department'])) { $user_updates[] = "`department` = :department"; $user_params[':department'] = $requested['department']; }
        if (!empty($requested['course'])) { $user_updates[] = "`course` = :course"; $user_params[':course'] = $requested['course']; }
        if (!empty($requested['year_level'])) { $user_updates[] = "`year_level` = :year_level"; $user_params[':year_level'] = $requested['year_level']; }
        if (!empty($requested['sex'])) { $user_updates[] = "`sex` = :sex"; $user_params[':sex'] = $requested['sex']; }
        if (!empty($requested['birthdate'])) { $user_updates[] = "`birthdate` = :birthdate"; $user_params[':birthdate'] = $requested['birthdate']; }
        if (!empty($requested['age'])) { $user_updates[] = "`age` = :age"; $user_params[':age'] = (int)$requested['age']; }
        if (!empty($target_req['proof_file'])) { $user_updates[] = "`registration_proof` = :proof"; $user_params[':proof'] = $target_req['proof_file']; }

        if (!empty($user_updates)) {
            $user_sql = "UPDATE `users` SET " . implode(', ', $user_updates) . ", `updated_at` = NOW() WHERE `id` = :id";
            $upd_user_stmt = $pdo->prepare($user_sql);
            $upd_user_stmt->execute($user_params);
        }

        // Update applications for this student
        $app_updates = [];
        $app_params = [':student_id' => $user_id];
        if (!empty($requested['name'])) { $app_updates[] = "`student_name` = :name"; $app_params[':name'] = $requested['name']; }
        if (!empty($requested['course'])) { $app_updates[] = "`course` = :course"; $app_params[':course'] = $requested['course']; }
        if (!empty($requested['year_level'])) { $app_updates[] = "`year_level` = :year_level"; $app_params[':year_level'] = $requested['year_level']; }
        if (!empty($requested['sex'])) { $app_updates[] = "`sex` = :sex"; $app_params[':sex'] = $requested['sex']; }
        if (!empty($requested['age'])) { $app_updates[] = "`age` = :age"; $app_params[':age'] = (int)$requested['age']; }

        if (!empty($app_updates)) {
            $app_sql = "UPDATE `applications` SET " . implode(', ', $app_updates) . ", `updated_at` = NOW() WHERE `student_id` = :student_id";
            $upd_app_stmt = $pdo->prepare($app_sql);
            $upd_app_stmt->execute($app_params);
        }

        // Mark request as approved
        $stmt_req_upd = $pdo->prepare("
            UPDATE `profile_requests` 
            SET `status` = 'approved', `admin_notes` = :notes, `resolved_at` = NOW(), `dismissed_by_user` = 0 
            WHERE `id` = :id AND `status` = 'pending'
        ");
        $stmt_req_upd->execute([
            ':notes' => htmlspecialchars($admin_notes),
            ':id'    => (int)$request_id
        ]);

        $pdo->commit();

        // Refresh session if the modified user is currently logged in
        if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === $user_id) {
            $_SESSION['user'] = get_user_by_id($user_id);
        }

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
        $stmt = $pdo->prepare("
            UPDATE `profile_requests` 
            SET `status` = 'rejected', `admin_notes` = :notes, `resolved_at` = NOW(), `dismissed_by_user` = 0 
            WHERE `id` = :id AND `status` = 'pending'
        ");
        $stmt->execute([
            ':notes' => htmlspecialchars($admin_notes),
            ':id'    => (int)$request_id
        ]);
        return $stmt->rowCount() > 0;
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
        $sql = "SELECT * FROM `jobs` WHERE 1=1";
        $params = [];

        if ($employer_id !== null) {
            $sql .= " AND `employer_id` = :emp_owner_id";
            $params[':emp_owner_id'] = (int)$employer_id;
        }

        if ($category) {
            if (is_array($category)) {
                $cat_clauses = [];
                $i = 0;
                foreach ($category as $cat_val) {
                    if (empty($cat_val) || !is_scalar($cat_val)) continue;
                    $p_name = ":cat_" . ($i++);
                    $cat_clauses[] = "(`category` LIKE $p_name OR `category_id` = $p_name)";
                    $params[$p_name] = '%' . trim((string)$cat_val) . '%';
                }
                if (!empty($cat_clauses)) {
                    $sql .= " AND (" . implode(' OR ', $cat_clauses) . ")";
                }
            } else {
                $sql .= " AND (`category` LIKE :cat OR `category_id` = :cat_exact)";
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
                    $jt_clauses[] = "`job_type` LIKE $p_name";
                    $params[$p_name] = '%' . trim((string)$jt_val) . '%';
                }
                if (!empty($jt_clauses)) {
                    $sql .= " AND (" . implode(' OR ', $jt_clauses) . ")";
                }
            } else {
                $sql .= " AND `job_type` LIKE :jt";
                $params[':jt'] = '%' . trim($job_type) . '%';
            }
        }

        if ($employer_type) {
            $sql .= " AND `employer_type` = :employer_type";
            $params[':employer_type'] = $employer_type;
        }

        if ($work_setup) {
            $sql .= " AND `work_setup` = :work_setup";
            $params[':work_setup'] = $work_setup;
        }

        if ($department) {
            $dept_val = '%' . trim($department) . '%';
            $sql .= " AND (`department` LIKE :dept1 OR `organization_name` LIKE :dept2)";
            $params[':dept1'] = $dept_val;
            $params[':dept2'] = $dept_val;
        }

        if ($pay_type) {
            $pt_val = '%' . trim($pay_type) . '%';
            $sql .= " AND (`pay_type` LIKE :pt1 OR `pay_rate` LIKE :pt2)";
            $params[':pt1'] = $pt_val;
            $params[':pt2'] = $pt_val;
        }

        if ($keyword) {
            $kw = '%' . trim($keyword) . '%';
            $sql .= " AND (`title` LIKE :kw1 OR `department` LIKE :kw2 OR `organization_name` LIKE :kw3 OR `description` LIKE :kw4 OR `location` LIKE :kw5 OR `tags` LIKE :kw6 OR `category` LIKE :kw7)";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
            $params[':kw5'] = $kw;
            $params[':kw6'] = $kw;
            $params[':kw7'] = $kw;
        }

        $sql .= " ORDER BY `id` DESC";
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
        $stmt = $pdo->prepare("SELECT * FROM `jobs` WHERE `id` = :id LIMIT 1");
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
        $is_verified_employer = (($user['role'] ?? '') === 'admin' || ($user['verification_status'] ?? 'verified') === 'verified') ? 1 : 0;
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
                `title`, `department`, `organization_name`, `category`, `category_id`,
                `employer_id`, `employer_name`, `employer_type`, `job_type`, `work_setup`,
                `verified_employer`, `location`, `pay_rate`, `pay_type`, `hours_per_week`,
                `vacancies`, `slots_total`, `slots_filled`, `deadline`, `status`, `image`,
                `tags`, `badges`, `description`, `responsibilities`, `qualifications`, `created_at`
            ) VALUES (
                :title, :department, :organization_name, :category, :category_id,
                :employer_id, :employer_name, :employer_type, :job_type, :work_setup,
                :verified_employer, :location, :pay_rate, :pay_type, :hours_per_week,
                :vacancies, :slots_total, 0, :deadline, 'active', :image,
                :tags, :badges, :description, :responsibilities, :qualifications, NOW()
            )
        ");

        $stmt->execute([
            ':title'             => $data['title'] ?? '',
            ':department'        => $data['department'] ?? $org_name,
            ':organization_name' => $org_name,
            ':category'          => $category_name,
            ':category_id'       => $category_id,
            ':employer_id'       => (int)($user['id'] ?? 0),
            ':employer_name'     => $user['name'] ?? 'Office Supervisor',
            ':employer_type'     => $employer_type,
            ':job_type'          => $job_type,
            ':work_setup'        => $work_setup,
            ':verified_employer' => $is_verified_employer,
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
                `category` = :category,
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
            ':category'         => $category_name,
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
        $sql = "SELECT a.* FROM `applications` a";
        if ($employer_id !== null) {
            $sql .= " INNER JOIN `jobs` j ON j.`id` = a.`job_id`";
        }
        $sql .= " WHERE 1=1";
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
            $sql .= " AND a.`department` LIKE :dept";
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
        $stmt = $pdo->prepare("SELECT * FROM `applications` WHERE `id` = :id LIMIT 1");
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
                `job_id`, `job_title`, `department`, `student_id`, `student_name`,
                `student_number`, `student_email`, `course`, `year_level`, `sex`,
                `age`, `phone`, `cover_letter`, `availability`, `resume_file`,
                `study_load_file`, `status`, `status_label`, `status_badge`,
                `supervisor_notes`, `applied_at`, `updated_at`
            ) VALUES (
                :job_id, :job_title, :department, :student_id, :student_name,
                :student_number, :student_email, :course, :year_level, :sex,
                :age, :phone, :cover_letter, :availability, :resume_file,
                :study_load_file, 'pending', 'Pending Review', 'warning',
                'Application submitted and queued for evaluation.', NOW(), NOW()
            )
        ");

        $stmt->execute([
            ':job_id'          => $job['id'],
            ':job_title'       => $job['title'],
            ':department'      => $job['department'],
            ':student_id'      => $student_id,
            ':student_name'    => $user['name'] ?? 'Juan Dela Cruz',
            ':student_number'  => $user['student_id'] ?? '2024-00123',
            ':student_email'   => $user['email'] ?? 'student@kld.edu.ph',
            ':course'          => $user['course'] ?? 'BS Information Systems (BSIS)',
            ':year_level'      => $user['year_level'] ?? '2nd Year',
            ':sex'             => $user['sex'] ?? 'Male',
            ':age'             => $user['age'] ?? (isset($user['birthdate']) ? calculate_age($user['birthdate']) : 20),
            ':phone'           => $data['phone'] ?? ($user['phone'] ?? '+63 917 123 4567'),
            ':cover_letter'    => $data['cover_letter'] ?? '',
            ':availability'    => json_encode($availability),
            ':resume_file'     => $resume_file,
            ':study_load_file' => $study_load
        ]);

        $new_id = (int)$pdo->lastInsertId();
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
            $interview_venue = htmlspecialchars($interview_data['venue'] ?? '');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE `applications` SET
                `status` = :status,
                `status_label` = :status_label,
                `status_badge` = :status_badge,
                `supervisor_notes` = :notes,
                `interview_date` = COALESCE(:interview_date, `interview_date`),
                `interview_time` = COALESCE(:interview_time, `interview_time`),
                `interview_venue` = COALESCE(:interview_venue, `interview_venue`),
                `updated_at` = NOW()
            WHERE `id` = :id
        ");

        $stmt->execute([
            ':status'          => $status,
            ':status_label'    => $status_label,
            ':status_badge'    => $status_badge,
            ':notes'           => htmlspecialchars($notes),
            ':interview_date'  => $interview_date,
            ':interview_time'  => $interview_time,
            ':interview_venue' => $interview_venue,
            ':id'              => (int)$id
        ]);

        // Synchronize slots_filled and job status
        if ($job_id > 0) {
            if ($old_status !== 'accepted' && $status === 'accepted') {
                $stmt_inc = $pdo->prepare("
                    UPDATE `jobs` SET
                        `slots_filled` = `slots_filled` + 1,
                        `status` = CASE WHEN (`slots_filled` + 1) >= `slots_total` THEN 'filled' ELSE `status` END,
                        `updated_at` = NOW()
                    WHERE `id` = :job_id
                ");
                $stmt_inc->execute([':job_id' => $job_id]);
            } elseif ($old_status === 'accepted' && $status !== 'accepted') {
                $stmt_dec = $pdo->prepare("
                    UPDATE `jobs` SET
                        `slots_filled` = GREATEST(0, `slots_filled` - 1),
                        `status` = CASE WHEN `status` = 'filled' AND (GREATEST(0, `slots_filled` - 1) < `slots_total`) THEN 'active' ELSE `status` END,
                        `updated_at` = NOW()
                    WHERE `id` = :job_id
                ");
                $stmt_dec->execute([':job_id' => $job_id]);
            }
        }

        $pdo->commit();
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
            SELECT `category`, COUNT(*) as cnt 
            FROM `jobs` 
            WHERE `status` = 'active' 
            GROUP BY `category`
        ");
        $counts = [];
        while ($row = $stmt_counts->fetch()) {
            $counts[$row['category']] = (int)$row['cnt'];
        }

        foreach ($cats as &$cat) {
            if (isset($counts[$cat['name']])) {
                $cat['job_count'] = $counts[$cat['name']];
            }
        }

        return $cats;
    } catch (Exception $e) {
        error_log("get_categories error: " . $e->getMessage());
        return [];
    }
}

function create_category($data) {
    try {
        $pdo = get_db_connection();
        $name = htmlspecialchars($data['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $stmt = $pdo->prepare("
            INSERT INTO `categories` (`name`, `slug`, `icon`, `description`, `theme`, `badge_tag`, `badge_icon`, `job_count`, `hourly_range`, `created_at`)
            VALUES (:name, :slug, :icon, :description, :theme, :badge_tag, :badge_icon, 0, :hourly_range, NOW())
        ");

        $stmt->execute([
            ':name'         => $name,
            ':slug'         => $slug,
            ':icon'         => $data['icon'] ?? 'bi-briefcase',
            ':description'  => htmlspecialchars($data['description'] ?? ''),
            ':theme'        => $data['theme'] ?? 'kld-green',
            ':badge_tag'    => $data['badge_tag'] ?? null,
            ':badge_icon'   => $data['badge_icon'] ?? null,
            ':hourly_range' => $data['hourly_range'] ?? null
        ]);

        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_category error: " . $e->getMessage());
        return 0;
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
// UNIFIED SYSTEM DATA MODE (Coexisting Demo & Real Data)
// ============================================================================

function get_system_data_mode() {
    return 'live';
}

function switch_system_data_mode($mode, $switched_by = 'User') {
    // Mode switching is permanently unified: demo fixtures and real accounts coexist.
    return true;
}

function reset_current_data_mode($switched_by = 'User') {
    return true;
}

function wipe_real_data_fresh() {
    return true;
}

function reset_demo_data() {
    return true;
}

// ============================================================================
// CAREER CENTER UPDATES & BLOG ENGINE
// ============================================================================

function get_career_updates() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT * FROM `updates` ORDER BY `published_at` DESC, `id` DESC");
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
        $stmt = $pdo->prepare("SELECT * FROM `updates` WHERE `id` = :id LIMIT 1");
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
        $sql = "SELECT * FROM `updates`";
        $params = [];
        if ($exclude_id !== null) {
            $sql .= " WHERE `id` != :ex_id";
            $params[':ex_id'] = (int)$exclude_id;
        }
        $sql .= " ORDER BY `published_at` DESC, `id` DESC LIMIT " . (int)$limit;
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

        $author_name = trim($data['author_name'] ?? 'Career Development Office');
        $author_role = trim($data['author_role'] ?? 'Coordinator');
        $author_office = trim($data['author_office'] ?? 'KLD Career Development & Placement Office');
        
        $initials = '';
        $name_parts = explode(' ', $author_name);
        foreach ($name_parts as $np) {
            if (!empty($np)) $initials .= strtoupper(substr($np, 0, 1));
        }
        $initials = substr($initials, 0, 2) ?: 'CC';

        $image = !empty($data['image']) ? trim($data['image']) : 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=1200&auto=format&fit=crop';
        $summary = trim($data['summary'] ?? (substr(strip_tags($content), 0, 160) . '...'));

        $stmt = $pdo->prepare("
            INSERT INTO `updates` (
                `slug`, `title`, `category`, `published_at`, `read_time`,
                `author_name`, `author_role`, `author_office`, `author_avatar`,
                `image`, `summary`, `content`, `created_at`
            ) VALUES (
                :slug, :title, :category, :published_at, :read_time,
                :author_name, :author_role, :author_office, :author_avatar,
                :image, :summary, :content, NOW()
            )
        ");

        $stmt->execute([
            ':slug'          => $slug,
            ':title'         => $title,
            ':category'      => trim($data['category'] ?? 'Campus News'),
            ':published_at'  => $data['published_at'] ?? date('Y-m-d H:i:s'),
            ':read_time'     => $data['read_time'] ?? $read_time,
            ':author_name'   => $author_name,
            ':author_role'   => $author_role,
            ':author_office' => $author_office,
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
        if (isset($data['author_name'])) {
            $updates[] = "`author_name` = :author_name";
            $updates[] = "`author_avatar` = :author_avatar";
            $params[':author_name'] = trim($data['author_name']);
            $initials = '';
            $name_parts = explode(' ', $data['author_name']);
            foreach ($name_parts as $np) {
                if (!empty($np)) $initials .= strtoupper(substr($np, 0, 1));
            }
            $params[':author_avatar'] = substr($initials, 0, 2) ?: 'CC';
        }
        if (isset($data['author_role'])) {
            $updates[] = "`author_role` = :author_role";
            $params[':author_role'] = trim($data['author_role']);
        }
        if (isset($data['author_office'])) {
            $updates[] = "`author_office` = :author_office";
            $params[':author_office'] = trim($data['author_office']);
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
