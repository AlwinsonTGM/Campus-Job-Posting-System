<?php
/**
 * Campus Job Posting System - Data Helper & Session Engine
 * Handles JSON persistence and $_SESSION runtime state without MySQL database.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DATA_DIR', dirname(__DIR__) . '/data');

// Helper to read JSON file safely
function load_json_file($filename) {
    $path = DATA_DIR . '/' . $filename;
    if (!file_exists($path)) {
        return [];
    }
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Helper to write JSON file safely
function save_json_file($filename, $data) {
    $path = DATA_DIR . '/' . $filename;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Initialize runtime state from JSON if not set in session
function init_app_data() {
    $current_schema_version = '2.5_profile_verification_workflow';
    if (!isset($_SESSION['app_initialized']) || ($_SESSION['schema_version'] ?? '') !== $current_schema_version) {
        $_SESSION['users'] = load_json_file('users.json');
        $_SESSION['jobs'] = load_json_file('jobs.json');
        $_SESSION['applications'] = load_json_file('applications.json');
        $_SESSION['categories'] = load_json_file('categories.json');
        $_SESSION['profile_requests'] = load_json_file('profile_requests.json');
        $_SESSION['schema_version'] = $current_schema_version;
        $_SESSION['app_initialized'] = true;
    }
}

init_app_data();

// Year Level / Academic Status & Biological Sex Helpers
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
    if (empty($birthdate)) {
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

// Job Type & Employer Type Enums & Helpers
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

// Flash Messages
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

// Auth Helpers
function get_logged_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function has_role($role) {
    $user = get_logged_user();
    return $user && $user['role'] === $role;
}

function require_auth($allowed_roles = []) {
    if (!is_logged_in()) {
        set_flash('warning', 'Please sign in to access this page.');
        header('Location: ../login.php');
        exit;
    }

    if (!empty($allowed_roles)) {
        $user = get_logged_user();
        if (!in_array($user['role'], $allowed_roles)) {
            set_flash('danger', 'Unauthorized access for your account role.');
            header('Location: ../index.php');
            exit;
        }
    }
}

// CSRF Protection Helpers
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

// Authorization Helpers
/**
 * Check if a user has authorization to manage / edit a job requisition.
 * 
 * @param array|int|string $job_or_id Job array or Job ID
 * @param array|null $user User array (defaults to get_logged_user())
 * @return bool
 */
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

    if (isset($job['employer_id']) && (int)$job['employer_id'] === (int)($user['id'] ?? 0)) {
        return true;
    }

    $user_org = trim($user['organization_name'] ?? '');
    $user_dept = trim($user['department'] ?? '');
    $job_dept = trim($job['department'] ?? '');
    $job_org = trim($job['organization_name'] ?? '');

    if (!empty($job_dept)) {
        if (!empty($user_dept) && strcasecmp($job_dept, $user_dept) === 0) {
            return true;
        }
        if (!empty($user_org) && strcasecmp($job_dept, $user_org) === 0) {
            return true;
        }
    }

    if (!empty($job_org)) {
        if (!empty($user_dept) && strcasecmp($job_org, $user_dept) === 0) {
            return true;
        }
        if (!empty($user_org) && strcasecmp($job_org, $user_org) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Check if a user has authorization to review or update candidate application status.
 * 
 * @param array|int|string $app_or_id Application array or Application ID
 * @param array|null $user User array (defaults to get_logged_user())
 * @return bool
 */
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
    if ($job && can_manage_job($job, $user)) {
        return true;
    }

    $user_org = trim($user['organization_name'] ?? '');
    $user_dept = trim($user['department'] ?? '');
    $app_dept = trim($app['department'] ?? '');

    if (!empty($app_dept)) {
        if (!empty($user_org) && strcasecmp($app_dept, $user_org) === 0) {
            return true;
        }
        if (!empty($user_dept) && strcasecmp($app_dept, $user_dept) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Check if a user has authorization to view a student resume / personal details.
 * 
 * @param array|int|string|null $app Application array or ID (if accessing via app_id)
 * @param int|string|null $student_user_id Target student user ID (if accessing via user_id)
 * @param array|null $user Requesting user (defaults to get_logged_user())
 * @return bool
 */
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
            $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
            foreach ($apps as $a) {
                if (isset($a['student_id']) && (int)$a['student_id'] === (int)$student_user_id) {
                    if (can_review_application($a, $user)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    return false;
}

// User Actions
function login_user($email, $password) {
    $users = $_SESSION['users'] ?? load_json_file('users.json');
    foreach ($users as $u) {
        if (strtolower($u['email']) === strtolower(trim($email))) {
            if ($u['password'] === $password || password_verify($password, $u['password'])) {
                $_SESSION['user'] = $u;
                return ['success' => true, 'user' => $u];
            }
            return ['success' => false, 'message' => 'Invalid password credentials.'];
        }
    }
    return ['success' => false, 'message' => 'No account found with this email address.'];
}

function quick_login($role, $user_id = null) {
    $users = $_SESSION['users'] ?? load_json_file('users.json');
    if ($user_id) {
        foreach ($users as $u) {
            if ($u['id'] == $user_id) {
                $_SESSION['user'] = $u;
                return $u;
            }
        }
    }
    foreach ($users as $u) {
        if ($u['role'] === $role) {
            $_SESSION['user'] = $u;
            return $u;
        }
    }
    return null;
}

// Helper to handle permit/MOA file uploads
function save_uploaded_permit($file) {
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $permit_dir = dirname(__DIR__) . '/uploads/permits';
    if (!is_dir($permit_dir)) {
        @mkdir($permit_dir, 0777, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        return null;
    }

    // Limit file size to 10MB
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        return null;
    }

    $safe_name = 'permit_' . time() . '_' . substr(md5(uniqid((string)rand(), true)), 0, 8) . '.' . $file_ext;
    $target_path = $permit_dir . '/' . $safe_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'uploads/permits/' . $safe_name;
    }

    return null;
}

// Helper to handle student COR / School ID / PSA proof file uploads
function save_uploaded_proof($file) {
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $proof_dir = dirname(__DIR__) . '/uploads/proofs';
    if (!is_dir($proof_dir)) {
        @mkdir($proof_dir, 0777, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        return null;
    }

    // Limit file size to 10MB
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        return null;
    }

    $safe_name = 'proof_' . time() . '_' . substr(md5(uniqid((string)rand(), true)), 0, 8) . '.' . $file_ext;
    $target_path = $proof_dir . '/' . $safe_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'uploads/proofs/' . $safe_name;
    }

    return null;
}

// Helper to handle student Resume / CV document uploads
function save_uploaded_resume($file) {
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $resume_dir = dirname(__DIR__) . '/uploads/resumes';
    if (!is_dir($resume_dir)) {
        @mkdir($resume_dir, 0777, true);
    }

    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        return null;
    }

    // Limit file size to 10MB
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        return null;
    }

    $safe_name = 'resume_' . time() . '_' . substr(md5(uniqid((string)rand(), true)), 0, 8) . '.' . $file_ext;
    $target_path = $resume_dir . '/' . $safe_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'uploads/resumes/' . $safe_name;
    }

    return null;
}

function update_employer_verification($id, $status, $notes = '') {
    $users = $_SESSION['users'] ?? load_json_file('users.json');
    foreach ($users as $key => $u) {
        if ($u['id'] == $id) {
            $users[$key]['verification_status'] = $status;
            $users[$key]['verification_notes'] = htmlspecialchars($notes);
            $users[$key]['verified_at'] = date('Y-m-d H:i:s');
            $_SESSION['users'] = $users;
            save_json_file('users.json', $users);
            return true;
        }
    }
    return false;
}

function register_user($data, $permit_file = null, $proof_file = null) {
    $users = $_SESSION['users'] ?? load_json_file('users.json');
    
    // Check if email exists
    foreach ($users as $u) {
        if (strtolower($u['email']) === strtolower(trim($data['email']))) {
            return ['success' => false, 'message' => 'Email address is already registered.'];
        }
    }

    $role = $data['role'] ?? 'student';
    $employer_type = $data['employer_type'] ?? 'university_office';
    $org_name = $data['organization_name'] ?? ($data['department'] ?? 'Campus Organization');
    
    $is_kld_email = (bool)preg_match('/@kld\.edu\.ph$/i', $data['email'] ?? '');
    
    if ($role === 'employer') {
        if ($employer_type === 'university_office' && $is_kld_email) {
            $verification = 'verified';
            $accreditation = 'INTERNAL-UNIV';
        } else {
            $verification = 'pending_approval';
            $accreditation = $data['accreditation_number'] ?? 'PENDING-VERIFICATION';
        }
    } else {
        $verification = 'verified';
        $accreditation = 'STUDENT-INTERNAL';
    }

    $sex = $data['sex'] ?? ($role === 'student' ? 'Male' : '');
    $birthdate = $data['birthdate'] ?? '';
    $age = !empty($birthdate) ? calculate_age($birthdate) : ($data['age'] ?? ($role === 'student' ? 20 : null));
    $year_level = $data['year_level'] ?? '1st Year';
    $proof_path = $proof_file ?? ($data['proof_file'] ?? null);

    $new_id = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
    $new_user = [
        'id' => $new_id,
        'name' => htmlspecialchars($data['name']),
        'email' => strtolower(trim($data['email'])),
        'password' => $data['password'],
        'role' => $role,
        'employer_type' => $employer_type,
        'organization_name' => htmlspecialchars($org_name),
        'student_id' => $data['student_id'] ?? ('2026-' . rand(10000, 99999)),
        'department' => $data['department'] ?? 'General Academics',
        'course' => $data['course'] ?? '',
        'year_level' => $year_level,
        'sex' => $sex,
        'birthdate' => $birthdate,
        'age' => $age,
        'phone' => $data['phone'] ?? '',
        'office_location' => $data['office_location'] ?? 'Campus Main Office',
        'accreditation_number' => $accreditation,
        'permit_file' => $permit_file ?? ($data['permit_file'] ?? null),
        'proof_file' => $proof_path,
        'verification_status' => $verification,
        'verification_notes' => '',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $users[] = $new_user;
    $_SESSION['users'] = $users;
    save_json_file('users.json', $users);

    $_SESSION['user'] = $new_user;
    return ['success' => true, 'user' => $new_user];
}

// Student Profile Change Request Helpers
function get_profile_requests($user_id = null, $status = null) {
    $reqs = $_SESSION['profile_requests'] ?? load_json_file('profile_requests.json');
    if ($user_id) {
        $reqs = array_filter($reqs, fn($r) => ($r['user_id'] ?? 0) == $user_id);
    }
    if ($status) {
        $reqs = array_filter($reqs, fn($r) => ($r['status'] ?? '') === $status);
    }
    usort($reqs, fn($a, $b) => strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now'));
    return array_values($reqs);
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
    $reqs = $_SESSION['profile_requests'] ?? load_json_file('profile_requests.json');
    foreach ($reqs as $k => $r) {
        if (($r['user_id'] ?? 0) == $user_id && in_array($r['status'] ?? '', ['approved', 'rejected'])) {
            $reqs[$k]['dismissed_by_user'] = true;
        }
    }
    $_SESSION['profile_requests'] = $reqs;
    save_json_file('profile_requests.json', $reqs);
    return true;
}

function create_profile_request($user_id, $requested_data, $proof_file, $reason = '') {
    $reqs = $_SESSION['profile_requests'] ?? load_json_file('profile_requests.json');
    
    // Check if user already has an active pending request
    foreach ($reqs as $r) {
        if (($r['user_id'] ?? 0) == $user_id && ($r['status'] ?? '') === 'pending') {
            return ['success' => false, 'message' => 'You already have an active profile update request awaiting review.'];
        }
    }

    $users = $_SESSION['users'] ?? load_json_file('users.json');
    $current_user = null;
    foreach ($users as $u) {
        if ($u['id'] == $user_id) {
            $current_user = $u;
            break;
        }
    }

    if (!$current_user) {
        return ['success' => false, 'message' => 'User account not found.'];
    }

    $req_birthdate = trim($requested_data['birthdate'] ?? ($current_user['birthdate'] ?? ''));
    $req_age = !empty($req_birthdate) ? calculate_age($req_birthdate) : (!empty($requested_data['age']) ? (int)$requested_data['age'] : ($current_user['age'] ?? 20));

    $new_id = count($reqs) > 0 ? max(array_column($reqs, 'id')) + 1 : 1;
    $new_req = [
        'id' => $new_id,
        'user_id' => (int)$user_id,
        'user_name' => $current_user['name'],
        'user_email' => $current_user['email'],
        'student_id' => $current_user['student_id'] ?? '2024-00123',
        'current_profile' => [
            'name' => $current_user['name'] ?? '',
            'department' => $current_user['department'] ?? '',
            'course' => $current_user['course'] ?? '',
            'year_level' => $current_user['year_level'] ?? '1st Year',
            'sex' => $current_user['sex'] ?? 'Male',
            'birthdate' => $current_user['birthdate'] ?? '',
            'age' => $current_user['age'] ?? 20,
        ],
        'requested_profile' => [
            'name' => htmlspecialchars($requested_data['name'] ?? $current_user['name']),
            'department' => htmlspecialchars($requested_data['department'] ?? $current_user['department']),
            'course' => htmlspecialchars($requested_data['course'] ?? $current_user['course']),
            'year_level' => htmlspecialchars($requested_data['year_level'] ?? $current_user['year_level']),
            'sex' => htmlspecialchars($requested_data['sex'] ?? ($current_user['sex'] ?? 'Male')),
            'birthdate' => htmlspecialchars($req_birthdate),
            'age' => $req_age,
        ],
        'proof_file' => $proof_file,
        'reason' => htmlspecialchars($reason),
        'status' => 'pending',
        'admin_notes' => '',
        'dismissed_by_user' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'resolved_at' => null
    ];

    $reqs[] = $new_req;
    $_SESSION['profile_requests'] = $reqs;
    save_json_file('profile_requests.json', $reqs);
    return ['success' => true, 'request' => $new_req];
}

function approve_profile_request($request_id, $admin_notes = '') {
    $reqs = $_SESSION['profile_requests'] ?? load_json_file('profile_requests.json');
    $target_req = null;
    $target_idx = null;

    foreach ($reqs as $k => $r) {
        if ($r['id'] == $request_id) {
            $target_req = $r;
            $target_idx = $k;
            break;
        }
    }

    if (!$target_req) {
        return false;
    }

    $user_id = $target_req['user_id'];
    $requested = $target_req['requested_profile'];

    // Apply changes to user profile
    $users = $_SESSION['users'] ?? load_json_file('users.json');
    foreach ($users as $uk => $u) {
        if ($u['id'] == $user_id) {
            if (!empty($requested['name'])) $users[$uk]['name'] = $requested['name'];
            if (!empty($requested['department'])) $users[$uk]['department'] = $requested['department'];
            if (!empty($requested['course'])) $users[$uk]['course'] = $requested['course'];
            if (!empty($requested['year_level'])) $users[$uk]['year_level'] = $requested['year_level'];
            if (!empty($requested['sex'])) $users[$uk]['sex'] = $requested['sex'];
            if (!empty($requested['birthdate'])) $users[$uk]['birthdate'] = $requested['birthdate'];
            if (!empty($requested['age'])) $users[$uk]['age'] = $requested['age'];
            if (!empty($target_req['proof_file'])) $users[$uk]['proof_file'] = $target_req['proof_file'];
            
            if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $user_id) {
                $_SESSION['user'] = $users[$uk];
            }
            break;
        }
    }
    $_SESSION['users'] = $users;
    save_json_file('users.json', $users);

    // Update applications of this student to reflect new verified data
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    foreach ($apps as $ak => $a) {
        if (($a['student_id'] ?? 0) == $user_id) {
            if (!empty($requested['name'])) $apps[$ak]['student_name'] = $requested['name'];
            if (!empty($requested['course'])) $apps[$ak]['course'] = $requested['course'];
            if (!empty($requested['year_level'])) $apps[$ak]['year_level'] = $requested['year_level'];
            if (!empty($requested['sex'])) $apps[$ak]['sex'] = $requested['sex'];
            if (!empty($requested['age'])) $apps[$ak]['age'] = $requested['age'];
        }
    }
    $_SESSION['applications'] = $apps;
    save_json_file('applications.json', $apps);

    // Mark request as approved
    $reqs[$target_idx]['status'] = 'approved';
    $reqs[$target_idx]['admin_notes'] = htmlspecialchars($admin_notes);
    $reqs[$target_idx]['resolved_at'] = date('Y-m-d H:i:s');
    $reqs[$target_idx]['dismissed_by_user'] = false;
    $_SESSION['profile_requests'] = $reqs;
    save_json_file('profile_requests.json', $reqs);

    return true;
}

function reject_profile_request($request_id, $admin_notes = '') {
    $reqs = $_SESSION['profile_requests'] ?? load_json_file('profile_requests.json');
    $target_idx = null;

    foreach ($reqs as $k => $r) {
        if ($r['id'] == $request_id) {
            $target_idx = $k;
            break;
        }
    }

    if ($target_idx === null) {
        return false;
    }

    $reqs[$target_idx]['status'] = 'rejected';
    $reqs[$target_idx]['admin_notes'] = htmlspecialchars($admin_notes);
    $reqs[$target_idx]['resolved_at'] = date('Y-m-d H:i:s');
    $reqs[$target_idx]['dismissed_by_user'] = false;
    $_SESSION['profile_requests'] = $reqs;
    save_json_file('profile_requests.json', $reqs);

    return true;
}

// Jobs Management
function get_jobs($category = null, $keyword = null, $department = null, $pay_type = null, $job_type = null, $employer_type = null, $work_setup = null) {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    
    if ($category) {
        $jobs = array_filter($jobs, function($j) use ($category) {
            if (is_array($category)) {
                foreach ($category as $cat_item) {
                    if (empty($cat_item)) continue;
                    if (strcasecmp($j['category'] ?? '', $cat_item) === 0 || 
                        (isset($j['category_id']) && (string)$j['category_id'] === (string)$cat_item) ||
                        stripos($j['category'] ?? '', $cat_item) !== false) {
                        return true;
                    }
                }
                return false;
            }
            return strcasecmp($j['category'] ?? '', $category) === 0 || 
                   (isset($j['category_id']) && (string)$j['category_id'] === (string)$category) ||
                   stripos($j['category'] ?? '', $category) !== false;
        });
    }

    if ($job_type) {
        $jobs = array_filter($jobs, function($j) use ($job_type) {
            if (is_array($job_type)) {
                foreach ($job_type as $jt) {
                    if (empty($jt)) continue;
                    if (strcasecmp($j['job_type'] ?? '', $jt) === 0 || stripos($j['job_type'] ?? '', $jt) !== false) {
                        return true;
                    }
                }
                return false;
            }
            return strcasecmp($j['job_type'] ?? '', $job_type) === 0 || stripos($j['job_type'] ?? '', $job_type) !== false;
        });
    }

    if ($employer_type) {
        $jobs = array_filter($jobs, function($j) use ($employer_type) {
            return ($j['employer_type'] ?? 'university_office') === $employer_type;
        });
    }

    if ($work_setup) {
        $jobs = array_filter($jobs, function($j) use ($work_setup) {
            return strcasecmp($j['work_setup'] ?? '', $work_setup) === 0;
        });
    }

    if ($department) {
        $jobs = array_filter($jobs, function($j) use ($department) {
            return stripos($j['department'] ?? '', $department) !== false || stripos($j['organization_name'] ?? '', $department) !== false;
        });
    }

    if ($pay_type) {
        $jobs = array_filter($jobs, function($j) use ($pay_type) {
            return stripos($j['pay_type'] ?? '', $pay_type) !== false || stripos($j['pay_rate'] ?? '', $pay_type) !== false;
        });
    }

    if ($keyword) {
        $kw = strtolower(trim($keyword));
        $kw_words = array_filter(explode(' ', $kw), function($w) { return strlen($w) >= 3; });
        $jobs = array_filter($jobs, function($j) use ($kw, $kw_words) {
            $tags_str = is_array($j['tags'] ?? null) ? implode(' ', $j['tags']) : ($j['tags'] ?? '');
            $haystack = strtolower(($j['title'] ?? '') . ' ' . ($j['department'] ?? '') . ' ' . ($j['organization_name'] ?? '') . ' ' . ($j['description'] ?? '') . ' ' . ($j['location'] ?? '') . ' ' . ($j['job_type'] ?? '') . ' ' . $tags_str . ' ' . ($j['category'] ?? ''));
            
            if (stripos($haystack, $kw) !== false) {
                return true;
            }
            
            foreach ($kw_words as $word) {
                if (stripos($haystack, $word) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    return array_values($jobs);
}

function get_job_by_id($id) {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    foreach ($jobs as $j) {
        if ($j['id'] == $id) {
            return $j;
        }
    }
    return null;
}

function create_job($data) {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    $new_id = count($jobs) > 0 ? max(array_column($jobs, 'id')) + 1 : 1;
    
    $user = get_logged_user();
    $employer_type = $user['employer_type'] ?? ($data['employer_type'] ?? 'university_office');
    $org_name = $user['organization_name'] ?? ($user['department'] ?? ($data['department'] ?? 'Campus Department'));
    $work_setup = $data['work_setup'] ?? 'On-Campus';
    $job_type = $data['job_type'] ?? 'Student Assistant';

    $new_job = [
        'id' => $new_id,
        'title' => htmlspecialchars($data['title']),
        'department' => $data['department'] ?? $org_name,
        'organization_name' => $org_name,
        'category' => $data['category'] ?? 'Administrative & Clerical',
        'category_id' => (int)($data['category_id'] ?? 3),
        'employer_id' => $user['id'] ?? 3,
        'employer_name' => $user['name'] ?? 'Office Supervisor',
        'employer_type' => $employer_type,
        'job_type' => $job_type,
        'work_setup' => $work_setup,
        'verified_employer' => true,
        'location' => htmlspecialchars($data['location'] ?? 'Campus Main Office'),
        'pay_rate' => htmlspecialchars($data['pay_rate'] ?? '₱80.00 / hour'),
        'pay_type' => $data['pay_type'] ?? 'Hourly',
        'hours_per_week' => htmlspecialchars($data['hours_per_week'] ?? '10 - 20 hrs/week'),
        'vacancies' => (int)($data['vacancies'] ?? 1),
        'slots_total' => (int)($data['vacancies'] ?? 1),
        'slots_filled' => 0,
        'deadline' => $data['deadline'] ?? date('Y-m-d', strtotime('+30 days')),
        'status' => 'active',
        'image' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=900&auto=format&fit=crop',
        'created_at' => date('Y-m-d'),
        'tags' => !empty($data['tags']) ? (is_array($data['tags']) ? $data['tags'] : explode(',', $data['tags'])) : [$job_type, $work_setup, $employer_type === 'university_office' ? 'University Office' : 'Approved Partner'],
        'badges' => [$job_type, $work_setup],
        'description' => htmlspecialchars($data['description'] ?? ''),
        'responsibilities' => !empty($data['responsibilities']) ? (is_array($data['responsibilities']) ? $data['responsibilities'] : array_filter(array_map('trim', explode("\n", $data['responsibilities'])))) : [],
        'qualifications' => !empty($data['qualifications']) ? (is_array($data['qualifications']) ? $data['qualifications'] : array_filter(array_map('trim', explode("\n", $data['qualifications'])))) : []
    ];

    $jobs[] = $new_job;
    $_SESSION['jobs'] = $jobs;
    save_json_file('jobs.json', $jobs);
    return $new_id;
}

function update_job($id, $data) {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    foreach ($jobs as $key => $j) {
        if ($j['id'] == $id) {
            $jobs[$key]['title'] = htmlspecialchars($data['title'] ?? $j['title']);
            $jobs[$key]['category'] = $data['category'] ?? $j['category'];
            $jobs[$key]['job_type'] = $data['job_type'] ?? ($j['job_type'] ?? 'Student Assistant');
            $jobs[$key]['work_setup'] = $data['work_setup'] ?? ($j['work_setup'] ?? 'On-Campus');
            $jobs[$key]['location'] = htmlspecialchars($data['location'] ?? $j['location']);
            $jobs[$key]['pay_rate'] = htmlspecialchars($data['pay_rate'] ?? $j['pay_rate']);
            $jobs[$key]['hours_per_week'] = htmlspecialchars($data['hours_per_week'] ?? $j['hours_per_week']);
            $jobs[$key]['vacancies'] = (int)($data['vacancies'] ?? $j['vacancies']);
            $jobs[$key]['slots_total'] = (int)($data['vacancies'] ?? $j['slots_total'] ?? 1);
            $jobs[$key]['deadline'] = $data['deadline'] ?? $j['deadline'];
            $jobs[$key]['status'] = $data['status'] ?? $j['status'];
            $jobs[$key]['description'] = htmlspecialchars($data['description'] ?? $j['description']);
            
            $_SESSION['jobs'] = $jobs;
            save_json_file('jobs.json', $jobs);
            return true;
        }
    }
    return false;
}

function delete_job($id) {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    $filtered = array_filter($jobs, function($j) use ($id) {
        return $j['id'] != $id;
    });
    $_SESSION['jobs'] = array_values($filtered);
    save_json_file('jobs.json', $_SESSION['jobs']);
    return true;
}

// Applications Management
function get_applications($student_id = null, $job_id = null, $department = null) {
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    
    if ($student_id) {
        $apps = array_filter($apps, function($a) use ($student_id) {
            return $a['student_id'] == $student_id;
        });
    }

    if ($job_id) {
        $apps = array_filter($apps, function($a) use ($job_id) {
            return $a['job_id'] == $job_id;
        });
    }

    if ($department) {
        $apps = array_filter($apps, function($a) use ($department) {
            return stripos($a['department'], $department) !== false;
        });
    }

    return array_values($apps);
}

function get_application_by_id($id) {
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    foreach ($apps as $a) {
        if ($a['id'] == $id) {
            return $a;
        }
    }
    return null;
}

function create_application($data) {
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    $user = get_logged_user();
    $job = get_job_by_id($data['job_id']);

    if (!$job) {
        return ['success' => false, 'message' => 'Job posting not found.'];
    }

    // Check if student already applied
    foreach ($apps as $a) {
        if ($a['job_id'] == $job['id'] && $a['student_id'] == ($user['id'] ?? 1)) {
            return ['success' => false, 'message' => 'You have already submitted an application for this position.'];
        }
    }

    $new_id = count($apps) > 0 ? max(array_column($apps, 'id')) + 1 : 1;
    $new_app = [
        'id' => $new_id,
        'job_id' => $job['id'],
        'job_title' => $job['title'],
        'department' => $job['department'],
        'student_id' => $user['id'] ?? 1,
        'student_name' => $user['name'] ?? 'Juan Dela Cruz',
        'student_number' => $user['student_id'] ?? '2024-00123',
        'student_email' => $user['email'] ?? 'student@kld.edu.ph',
        'course' => $user['course'] ?? 'BS Information Systems (BSIS)',
        'year_level' => $user['year_level'] ?? '2nd Year',
        'sex' => $user['sex'] ?? 'Male',
        'age' => $user['age'] ?? (isset($user['birthdate']) ? calculate_age($user['birthdate']) : 20),
        'phone' => $data['phone'] ?? ($user['phone'] ?? '+63 917 123 4567'),
        'cover_letter' => htmlspecialchars($data['cover_letter'] ?? ''),
        'availability' => !empty($data['availability']) ? (is_array($data['availability']) ? $data['availability'] : explode(',', $data['availability'])) : ['Flexible Weekdays'],
        'resume_file' => !empty($data['resume_file']) ? htmlspecialchars($data['resume_file']) : 'Student_Resume_' . ($user['id'] ?? 1) . '.pdf',
        'study_load_file' => 'Study_Load_' . ($user['id'] ?? 1) . '.pdf',
        'status' => 'pending',
        'status_label' => 'Pending Review',
        'status_badge' => 'warning',
        'interview_date' => null,
        'interview_time' => null,
        'interview_venue' => null,
        'supervisor_notes' => 'Application submitted and queued for evaluation.',
        'applied_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $apps[] = $new_app;
    $_SESSION['applications'] = $apps;
    save_json_file('applications.json', $apps);
    return ['success' => true, 'id' => $new_id];
}

function update_application_status($id, $status, $notes = '', $interview_data = []) {
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    $badge_map = [
        'pending' => 'warning',
        'under_review' => 'primary',
        'interview_scheduled' => 'info',
        'accepted' => 'success',
        'declined' => 'danger'
    ];
    $label_map = [
        'pending' => 'Pending Review',
        'under_review' => 'Under Review',
        'interview_scheduled' => 'Interview Scheduled',
        'accepted' => 'Accepted / Hired',
        'declined' => 'Declined / Filled'
    ];

    foreach ($apps as $key => $a) {
        if ($a['id'] == $id) {
            $apps[$key]['status'] = $status;
            $apps[$key]['status_label'] = $label_map[$status] ?? ucfirst(str_replace('_', ' ', $status));
            $apps[$key]['status_badge'] = $badge_map[$status] ?? 'secondary';
            $apps[$key]['supervisor_notes'] = htmlspecialchars($notes);
            $apps[$key]['updated_at'] = date('Y-m-d H:i:s');

            if ($status === 'interview_scheduled' && !empty($interview_data)) {
                $apps[$key]['interview_date'] = $interview_data['date'] ?? null;
                $apps[$key]['interview_time'] = $interview_data['time'] ?? null;
                $apps[$key]['interview_venue'] = htmlspecialchars($interview_data['venue'] ?? '');
            }

            $_SESSION['applications'] = $apps;
            save_json_file('applications.json', $apps);
            return true;
        }
    }
    return false;
}

// Official KLD Institutes and Degree Programs
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
    $cats = $_SESSION['categories'] ?? load_json_file('categories.json');
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    
    // Tally active jobs per category
    $counts = [];
    foreach ($jobs as $j) {
        if (($j['status'] ?? 'active') === 'active') {
            $cat_name = $j['category'] ?? '';
            $counts[$cat_name] = ($counts[$cat_name] ?? 0) + 1;
        }
    }
    
    foreach ($cats as $idx => $cat) {
        if (isset($counts[$cat['name']])) {
            $cats[$idx]['job_count'] = $counts[$cat['name']];
        }
    }
    
    return $cats;
}

function create_category($data) {
    $cats = $_SESSION['categories'] ?? load_json_file('categories.json');
    $new_id = count($cats) > 0 ? max(array_column($cats, 'id')) + 1 : 1;
    $new_cat = [
        'id' => $new_id,
        'name' => htmlspecialchars($data['name']),
        'icon' => $data['icon'] ?? 'bi-briefcase',
        'description' => htmlspecialchars($data['description'] ?? ''),
        'color' => $data['color'] ?? 'primary',
        'job_count' => 0
    ];
    $cats[] = $new_cat;
    $_SESSION['categories'] = $cats;
    save_json_file('categories.json', $cats);
    return $new_id;
}

// Key Metrics & Summary Functions (for Index & Reports)
function get_metrics_total_active_jobs() {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    $today = date('Y-m-d');
    $active = array_filter($jobs, function($j) use ($today) {
        $is_active = ($j['status'] ?? 'active') === 'active';
        $deadline_future = !empty($j['deadline']) ? ($j['deadline'] >= $today) : true;
        return $is_active && $deadline_future;
    });
    return count($active);
}

function get_metrics_partnered_offices() {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    $depts = [];
    foreach ($jobs as $j) {
        if (!empty($j['department'])) {
            $dept_clean = trim($j['department']);
            $depts[$dept_clean] = true;
        }
    }
    return count($depts);
}

function get_metrics_students_hired() {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    $total_hired = 0;
    foreach ($jobs as $j) {
        $total_hired += (int)($j['slots_filled'] ?? $j['filled_vacancies'] ?? 0);
    }
    // Also include accepted applications if any
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    $accepted_apps = array_filter($apps, function($a) {
        return ($a['status'] ?? '') === 'accepted';
    });
    return max($total_hired, count($accepted_apps));
}

function get_metrics_avg_hourly_pay() {
    $jobs = $_SESSION['jobs'] ?? load_json_file('jobs.json');
    $rates = [];
    foreach ($jobs as $j) {
        $pay_rate_str = $j['pay_rate'] ?? '';
        // Extract numeric value from string like "₱85.00 / hour" or "85"
        if (preg_match('/(\d+(?:\.\d+)?)/', $pay_rate_str, $matches)) {
            $rates[] = (float)$matches[1];
        }
    }
    if (empty($rates)) {
        return '₱85';
    }
    $avg = round(array_sum($rates) / count($rates));
    return '₱' . $avg;
}

// Reset Demo Data helper
function reset_demo_data() {
    $_SESSION['users'] = load_json_file('users.json');
    $_SESSION['jobs'] = load_json_file('jobs.json');
    $_SESSION['applications'] = load_json_file('applications.json');
    $_SESSION['categories'] = load_json_file('categories.json');
    $_SESSION['app_initialized'] = true;
    set_flash('info', 'Demo dataset has been reset to default campus state.');
}

