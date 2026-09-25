<?php
declare(strict_types=1);

/**
 * User service: session/auth, CSRF, authorization, user CRUD, registration, profile requests, password reset, email verification.
 * Extracted from includes/data-helper.php — 100% function contract preserved.
 */

// ============================================================================
// AUTH & SESSION HELPERS
// ============================================================================

function get_logged_user(): ?array {
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

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function has_role(string $role): bool {
    $user = get_logged_user();
    return $user && ($user['role'] ?? '') === $role;
}

function require_auth(array $allowed_roles = []): void {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $prefix = (strpos($script, '/admin/') !== false || strpos($script, '/employer/') !== false || strpos($script, '/student/') !== false) ? '../' : '';

    if (!is_logged_in()) {
        set_flash('warning', 'Please sign in to access this page.');
        header('Location: ' . $prefix . 'login.php');
        exit;
    }

    $user = get_logged_user();
    if ($user && isset($user['is_email_verified']) && (int)$user['is_email_verified'] === 0 && ($user['role'] ?? '') !== 'admin') {
        $_SESSION['pending_verification'] = [
            'user_id' => (int)$user['id'],
            'email'   => $user['email'],
            'name'    => $user['name'],
            'role'    => $user['role']
        ];
        unset($_SESSION['user']);
        set_flash('warning', 'Please verify your institutional email address to continue.');
        header('Location: ' . $prefix . 'verify-email.php');
        exit;
    }

    if (!empty($allowed_roles)) {
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
    function generate_csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token) || !is_string($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token(string $token): bool {
        return verify_csrf_token($token);
    }
}

// ============================================================================
// AUTHORIZATION CONTRACTS
// ============================================================================

function can_manage_job(array|int|string $job_or_id, ?array $user = null): bool {
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

function can_review_application(array|int|string $app_or_id, ?array $user = null): bool {
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

function can_view_student_resume(mixed $app = null, int|string|null $student_user_id = null, ?array $user = null): bool {
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

function can_access_resume_file(?array $user, string $filename): bool {
    if (!$user) {
        return false;
    }
    $role = $user['role'] ?? '';
    if ($role === 'admin') {
        return true;
    }
    $requested_file = basename($filename);
    $user_id = (int)($user['id'] ?? 0);
    if ($user_id <= 0) {
        return false;
    }

    try {
        $pdo = get_db_connection();
        if ($role === 'student') {
            $profile_name = ($user['name'] ?? '') . '_Resume.pdf';
            if ($requested_file === $profile_name) {
                return true;
            }
            $chk = $pdo->prepare("SELECT `id` FROM `applications` WHERE `student_id` = :sid AND `resume_file` = :rfile LIMIT 1");
            $chk->execute([':sid' => $user_id, ':rfile' => $requested_file]);
            return (bool)$chk->fetch();
        }

        if ($role === 'employer') {
            $chk = $pdo->prepare("SELECT a.`id` FROM `applications` a INNER JOIN `jobs` j ON j.`id` = a.`job_id` WHERE j.`employer_id` = :eid AND a.`resume_file` = :rfile LIMIT 1");
            $chk->execute([':eid' => $user_id, ':rfile' => $requested_file]);
            return (bool)$chk->fetch();
        }
    } catch (Exception $e) {
        error_log("can_access_resume_file error: " . $e->getMessage());
    }

    return false;
}

// ============================================================================
// USER MANAGEMENT & AUTH ACTIONS
// ============================================================================

function get_user_base_query(): string {
    ensure_email_verifications_table();
    return "
        SELECT 
            u.`id`, u.`email`, u.`password`, u.`role`, u.`name`, u.`phone`, u.`status`, u.`is_email_verified`, u.`created_at`, u.`updated_at`,
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

function get_all_users(?string $role = null, ?string $keyword = null, ?string $emp_type = null, ?string $ver_status = null): array {
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

function get_user_by_id(int|string|null $id): ?array {
    if ($id === null || $id === '' || (int)$id <= 0) {
        return null;
    }
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

function get_user_by_email(string $email): ?array {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(get_user_base_query() . " WHERE LOWER(u.`email`) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        $row = $stmt->fetch();
        return $row ? hydrate_user($row) : null;
    } catch (Exception $e) {
        error_log("get_user_by_email error: " . $e->getMessage());
        return null;
    }
}

function login_user(string $email, string $password): array {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(get_user_base_query() . " WHERE LOWER(u.`email`) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['success' => false, 'message' => 'No account found with this email address.'];
        }

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

        if (!$is_valid) {
            return ['success' => false, 'message' => 'Invalid password credentials.'];
        }

        if (isset($user['is_email_verified']) && (int)$user['is_email_verified'] === 0 && ($user['role'] ?? '') !== 'admin') {
            return [
                'success' => false,
                'unverified' => true,
                'user' => $user,
                'message' => 'Your institutional email is not verified yet. Please enter the verification code.'
            ];
        }

        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        unset($user['password']);
        $_SESSION['user'] = $user;
        return ['success' => true, 'user' => $user];
    } catch (Exception $e) {
        error_log("login_user error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred during login.'];
    }
}

function quick_login(string $role, int|string|null $user_id = null): ?array {
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
function update_user_verification(int|string $id, string $status, string $notes = ''): bool {
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

function update_employer_verification(int|string $id, string $status, string $notes = ''): bool {
    return update_user_verification($id, $status, $notes);
}

/**
 * Validate email format and check whether the domain/subdomain has active DNS mail records.
 *
 * @param string $email The email address to check.
 * @return array ['valid' => bool, 'error' => string|null, 'domain' => string]
 */
function validate_email_domain_dns($email): array {
    $clean_email = trim((string)$email);

    if ($clean_email === '' || !filter_var($clean_email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'error' => 'Please provide a valid email address format (e.g. name@kld.edu.ph).',
            'domain' => ''
        ];
    }

    $at_pos = strrpos($clean_email, '@');
    if ($at_pos === false) {
        return [
            'valid' => false,
            'error' => 'Invalid email address structure.',
            'domain' => ''
        ];
    }

    $domain = strtolower(substr($clean_email, $at_pos + 1));

    if (!str_contains($domain, '.') || !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/i', $domain)) {
        return [
            'valid' => false,
            'error' => "The email domain \"{$domain}\" is invalid.",
            'domain' => $domain
        ];
    }

    // Verify whether the domain or subdomain has active MX or A/AAAA DNS records
    $has_mx = checkdnsrr($domain, 'MX');
    $has_a  = checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA');

    if (!$has_mx && !$has_a) {
        return [
            'valid' => false,
            'error' => "The email domain or subdomain \"{$domain}\" does not exist or has no active mail servers.",
            'domain' => $domain
        ];
    }

    return [
        'valid' => true,
        'error' => null,
        'domain' => $domain
    ];
}

/**
 * Check if an email address is already registered
 */
function is_email_registered(string $email): bool {
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
function is_student_id_registered(string $student_id): bool {
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

function validate_registration_payload(array $data, string $email, string $role): ?string {
    if (is_email_registered($email)) {
        return 'This KLD account / email address is already registered.';
    }

    if ($role === 'student' && !empty($data['student_id'])) {
        if (is_student_id_registered($data['student_id'])) {
            return 'This Student ID Number is already registered to an existing account.';
        }
    }

    return null;
}

function insert_user_base_record(PDO $pdo, array $data, string $email, string $role): int {
    $raw_pass = $data['password'] ?? 'Password123!';
    $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);

    $stmt_user = $pdo->prepare("
        INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `status`, `is_email_verified`, `created_at`)
        VALUES (:name, :email, :password, :role, :phone, 'active', 0, NOW())
    ");
    $stmt_user->execute([
        ':name'     => trim($data['name'] ?? ''),
        ':email'    => $email,
        ':password' => $hashed_pass,
        ':role'     => $role,
        ':phone'    => $data['phone'] ?? null
    ]);

    return (int)$pdo->lastInsertId();
}

function insert_student_profile(PDO $pdo, int $user_id, array $data, string $verification, ?string $proof_path): void {
    $birthdate = (!empty($data['birthdate']) && $data['birthdate'] !== '0000-00-00') ? $data['birthdate'] : null;
    $age = !empty($birthdate) ? calculate_age($birthdate) : ($data['age'] ?? 20);
    $sex = $data['sex'] ?? 'Male';
    $year_level = $data['year_level'] ?? '1st Year';
    $student_id_val = !empty($data['student_id']) ? $data['student_id'] : ('KLD-' . str_pad((string)$user_id, 6, '0', STR_PAD_LEFT));
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
        ':user_id'             => $user_id,
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
}

function insert_employer_profile(PDO $pdo, int $user_id, array $data, string $org_name, string $employer_type, string $verification, string $accreditation, ?string $permit_path): void {
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
        ':user_id'              => $user_id,
        ':employer_type'        => $employer_type,
        ':organization_name'    => trim($org_name),
        ':office_location'      => $data['office_location'] ?? 'Campus Main Office',
        ':contact_person'       => trim($data['name'] ?? ''),
        ':accreditation_number' => $accreditation,
        ':verification_status'  => $verification,
        ':business_permit'      => $permit_path
    ]);
}

function register_user(array $data, ?array $permit_file = null, ?array $proof_file = null): array {
    try {
        $pdo = get_db_connection();
        $email = strtolower(trim($data['email'] ?? ''));

        $allowed_roles = ['student', 'employer'];
        $role = in_array($data['role'] ?? '', $allowed_roles, true) ? $data['role'] : 'student';

        $val_error = validate_registration_payload($data, $email, $role);
        if ($val_error !== null) {
            return ['success' => false, 'message' => $val_error];
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
            $verification = $is_kld_email ? 'verified' : 'pending_approval';
            $accreditation = $data['student_id'] ?? ('STUDENT-' . rand(10000, 99999));
        }

        $proof_path = $proof_file ?? ($data['proof_file'] ?? null);
        $permit_path = $permit_file ?? ($data['permit_file'] ?? null);

        $pdo->beginTransaction();

        $new_id = insert_user_base_record($pdo, $data, $email, $role);

        if ($role === 'student') {
            insert_student_profile($pdo, $new_id, $data, $verification, $proof_path);
        } elseif ($role === 'employer') {
            insert_employer_profile($pdo, $new_id, $data, $org_name, $employer_type, $verification, $accreditation, $permit_path);
        }

        $pdo->commit();
        $new_user = get_user_by_id($new_id);
        if ($new_user) unset($new_user['password']);

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
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

// ============================================================================
// STUDENT PROFILE CHANGE REQUESTS
// ============================================================================

function get_profile_requests(int|string|null $user_id = null, ?string $status = null): array {
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

function get_pending_profile_request(int|string $user_id): ?array {
    $reqs = get_profile_requests($user_id, 'pending');
    return !empty($reqs) ? $reqs[0] : null;
}

function get_recent_profile_request_notice(int|string $user_id): ?array {
    $reqs = get_profile_requests($user_id);
    foreach ($reqs as $r) {
        if (in_array($r['status'] ?? '', ['approved', 'rejected']) && empty($r['dismissed_by_user'])) {
            return $r;
        }
    }
    return null;
}

function dismiss_profile_request_notice(int|string $user_id): bool {
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

function validate_profile_request_payload(PDO $pdo, int $user_id, array $requested_data, array $current_user): ?string {
    $pending = get_pending_profile_request($user_id);
    if ($pending) {
        return 'You already have an active profile update request awaiting review.';
    }

    $req_email = strtolower(trim($requested_data['email'] ?? ($current_user['email'] ?? '')));
    if (empty($req_email) || !filter_var($req_email, FILTER_VALIDATE_EMAIL)) {
        return 'Please provide a valid institutional / student email address.';
    }

    if ($req_email !== strtolower($current_user['email'])) {
        $stmt_chk = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(`email`) = :email AND `id` != :id LIMIT 1");
        $stmt_chk->execute([':email' => $req_email, ':id' => $user_id]);
        if ($stmt_chk->fetch()) {
            return 'The requested email address is already in use by another account.';
        }
    }

    $target_dept = trim((string)($requested_data['department'] ?? ($current_user['department'] ?? '')));
    $target_course = trim((string)($requested_data['course'] ?? ($current_user['course'] ?? '')));
    $institutes = get_kld_institutes_and_courses();
    if ($target_dept !== '' && $target_course !== '' && isset($institutes[$target_dept]) && !in_array($target_course, $institutes[$target_dept], true)) {
        return "The selected degree program does not belong to {$target_dept}.";
    }

    return null;
}

function dispatch_profile_request_notification(string $user_name): void {
    notify_all_admins(
        'profile_request',
        'Profile Correction Request',
        "{$user_name} submitted a profile change request for administrative review.",
        'admin/users.php?ver_status=all',
        'bi-person-gear',
        'info'
    );
}

function create_profile_request(int|string $user_id, array $requested_data, array|string|null $proof_file = null, string $reason = ''): array {
    try {
        $pdo = get_db_connection();
        $user_id = (int)$user_id;

        $current_user = get_user_by_id($user_id);
        if (!$current_user) {
            return ['success' => false, 'message' => 'User account not found.'];
        }

        $val_error = validate_profile_request_payload($pdo, $user_id, $requested_data, $current_user);
        if ($val_error !== null) {
            return ['success' => false, 'message' => $val_error];
        }

        $req_birthdate = trim($requested_data['birthdate'] ?? ($current_user['birthdate'] ?? ''));
        $req_age = !empty($req_birthdate) ? calculate_age($req_birthdate) : (!empty($requested_data['age']) ? (int)$requested_data['age'] : ($current_user['age'] ?? 20));
        $req_email = strtolower(trim($requested_data['email'] ?? ($current_user['email'] ?? '')));

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
            ':user_id'           => $user_id,
            ':current_profile'   => json_encode($current_profile),
            ':requested_profile' => json_encode($requested_profile),
            ':proof_file'        => $proof_file,
            ':reason'            => trim($reason)
        ]);

        $new_id = (int)$pdo->lastInsertId();
        $new_req = [
            'id'                => $new_id,
            'user_id'           => $user_id,
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

        dispatch_profile_request_notification($current_user['name']);

        return ['success' => true, 'request' => $new_req];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Request creation failed: ' . $e->getMessage()];
    }
}

function apply_profile_request_changes(PDO $pdo, int $user_id, array $requested, ?string $proof_file): void {
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
    if (!empty($proof_file)) { $student_updates[] = "`registration_proof` = :proof"; $student_params[':proof'] = $proof_file; }

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
}

function dispatch_profile_approval_notification(int $user_id): void {
    create_notification(
        $user_id,
        'profile_request',
        'Profile Update Approved! 🎉',
        'Your institutional record change request has been verified and updated by the administrator.',
        'settings.php',
        'bi-person-check-fill',
        'success'
    );
}

function approve_profile_request(int|string $request_id, string $admin_notes = ''): bool {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM `profile_requests` WHERE `id` = :id AND `status` = 'pending' LIMIT 1");
        $stmt->execute([':id' => (int)$request_id]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $target_req = hydrate_profile_request($row);
        $user_id = (int)$target_req['user_id'];
        $requested = $target_req['requested_profile'];

        $pdo->beginTransaction();

        apply_profile_request_changes($pdo, $user_id, $requested, $target_req['proof_file'] ?? null);

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

        if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === $user_id) {
            $_SESSION['user'] = get_user_by_id($user_id);
        }

        dispatch_profile_approval_notification($user_id);

        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("approve_profile_request error: " . $e->getMessage());
        return false;
    }
}

function reject_profile_request(int|string $request_id, string $admin_notes = ''): bool {
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

function update_user_profile(int $user_id, string $role, array $data): bool {
    try {
        $pdo = get_db_connection();
        $pdo->beginTransaction();

        $phone = trim($data['phone'] ?? '');
        $user_updates = ["`phone` = :phone"];
        $user_params = [
            ':phone' => htmlspecialchars($phone),
            ':id'    => $user_id
        ];

        if ($role === 'employer' || $role === 'admin') {
            $name = trim($data['name'] ?? '');
            if (!empty($name)) {
                $user_updates[] = "`name` = :name";
                $user_params[':name'] = htmlspecialchars($name);
            }
        }

        $stmt_u = $pdo->prepare("UPDATE `users` SET " . implode(', ', $user_updates) . ", `updated_at` = NOW() WHERE `id` = :id");
        $stmt_u->execute($user_params);

        if ($role === 'student' && isset($data['availability'])) {
            $stmt_sp = $pdo->prepare("UPDATE `student_profiles` SET `availability` = :avail, `updated_at` = NOW() WHERE `user_id` = :id");
            $stmt_sp->execute([
                ':avail' => json_encode($data['availability']),
                ':id'    => $user_id
            ]);
        } elseif ($role === 'employer' && !empty($data['office_location'])) {
            $stmt_ep = $pdo->prepare("UPDATE `employer_profiles` SET `office_location` = :office_location, `updated_at` = NOW() WHERE `user_id` = :id");
            $stmt_ep->execute([
                ':office_location' => htmlspecialchars(trim($data['office_location'])),
                ':id'              => $user_id
            ]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("update_user_profile error: " . $e->getMessage());
        return false;
    }
}
function update_user_password(int|string $user_id, string $hashed_password): bool {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE `users` SET `password` = :pass, `updated_at` = NOW() WHERE `id` = :id");
        $stmt->execute([':pass' => $hashed_password, ':id' => (int)$user_id]);
        if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$user_id) {
            $_SESSION['user']['password'] = $hashed_password;
        }
        return true;
    } catch (Exception $e) {
        error_log("update_user_password error: " . $e->getMessage());
        return false;
    }
}

function ensure_password_resets_table(): void {
    try {
        $pdo = get_db_connection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `token_hash` VARCHAR(255) NOT NULL UNIQUE,
            `expires_at` DATETIME NOT NULL,
            `used_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_resets_user` (`user_id`),
            INDEX `idx_resets_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Exception $e) {
        error_log("ensure_password_resets_table error: " . $e->getMessage());
    }
}

function create_password_reset(int|string $user_id): ?string {
    ensure_password_resets_table();
    $pdo = get_db_connection();
    $pdo->prepare("DELETE FROM `password_resets` WHERE `user_id` = :u OR `expires_at` < NOW()")
        ->execute([':u' => (int)$user_id]);
    $token = bin2hex(random_bytes(32));
    $pdo->prepare("INSERT INTO `password_resets` (`user_id`, `token_hash`, `expires_at`)
                   VALUES (:u, :h, DATE_ADD(NOW(), INTERVAL 1 HOUR))")
        ->execute([':u' => (int)$user_id, ':h' => hash('sha256', $token)]);
    return $token;
}

function get_password_reset_by_token(string $token): ?array {
    ensure_password_resets_table();
    if (!is_string($token) || strlen($token) !== 64 || !ctype_xdigit($token)) return null;
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT r.*, u.`email` FROM `password_resets` r
            JOIN `users` u ON u.`id` = r.`user_id`
            WHERE r.`token_hash` = :h AND r.`used_at` IS NULL AND r.`expires_at` > NOW() LIMIT 1");
        $stmt->execute([':h' => hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    } catch (Exception $e) {
        error_log("get_password_reset_by_token error: " . $e->getMessage());
        return null;
    }
}

function consume_password_reset(int|string $reset_id, int|string $user_id, string $new_password): bool {
    ensure_password_resets_table();
    try {
        $pdo = get_db_connection();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE `users` SET `password` = :pass, `updated_at` = NOW() WHERE `id` = :id");
        $stmt->execute([':pass' => password_hash($new_password, PASSWORD_DEFAULT), ':id' => (int)$user_id]);
        $pdo->prepare("UPDATE `password_resets` SET `used_at` = NOW() WHERE `id` = :i")
            ->execute([':i' => (int)$reset_id]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        error_log("consume_password_reset error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate password complexity requirements across authentication flows.
 *
 * @param string $password
 * @return array{valid: bool, error: ?string}
 */
function validate_password_strength(string $password): array {
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'error' => 'Password must contain at least 8 characters.'
        ];
    }

    $has_lower = (bool)preg_match('/[a-z]/', $password);
    $has_upper = (bool)preg_match('/[A-Z]/', $password);
    $has_number = (bool)preg_match('/[0-9]/', $password);
    $has_special = (bool)preg_match('/[^A-Za-z0-9]/', $password);

    $categories = ($has_lower ? 1 : 0) + ($has_upper ? 1 : 0) + ($has_number ? 1 : 0) + ($has_special ? 1 : 0);

    if ($categories < 2) {
        return [
            'valid' => false,
            'error' => 'Please choose a stronger password (at least 8 characters with a mix of letters, numbers, or symbols).'
        ];
    }

    return [
        'valid' => true,
        'error' => null
    ];
}

function ensure_email_verifications_table(): void {
    static $ensured = false;
    if ($ensured) {
        return;
    }
    try {
        $pdo = get_db_connection();
        $col_check = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_email_verified'")->fetch();
        if (!$col_check) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_email_verified` TINYINT(1) NOT NULL DEFAULT 1");
            $pdo->exec("UPDATE `users` SET `is_email_verified` = 1 WHERE `is_email_verified` IS NULL");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS `email_verifications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `code_hash` VARCHAR(255) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `attempts` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_verify_user` (`user_id`),
            INDEX `idx_verify_expires` (`expires_at`),
            CONSTRAINT `fk_verify_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $ensured = true;
    } catch (Exception $e) {
        error_log("ensure_email_verifications_table error: " . $e->getMessage());
    }
}

function create_email_verification_code(int $user_id): string {
    ensure_email_verifications_table();
    try {
        $pdo = get_db_connection();

        // Guard against non-existent / purged users to prevent FK constraint violations
        $user_check = $pdo->prepare("SELECT id FROM `users` WHERE `id` = :u LIMIT 1");
        $user_check->execute([':u' => $user_id]);
        if (!$user_check->fetchColumn()) {
            return '';
        }

        $pdo->prepare("DELETE FROM `email_verifications` WHERE `user_id` = :u OR `expires_at` < NOW()")
            ->execute([':u' => $user_id]);

        $code = (string)random_int(100000, 999999);
        $stmt = $pdo->prepare("INSERT INTO `email_verifications` (`user_id`, `code_hash`, `expires_at`)
                               VALUES (:u, :h, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $stmt->execute([':u' => $user_id, ':h' => hash('sha256', $code)]);

        return $code;
    } catch (\Throwable $e) {
        error_log("create_email_verification_code error: " . $e->getMessage());
        return '';
    }
}

function can_resend_email_code(int $user_id): array {
    ensure_email_verifications_table();
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, `created_at`, NOW()) FROM `email_verifications` WHERE `user_id` = :u ORDER BY `id` DESC LIMIT 1");
        $stmt->execute([':u' => $user_id]);
        $elapsed = $stmt->fetchColumn();
        if ($elapsed === false) {
            return ['allowed' => true, 'remaining_seconds' => 0];
        }
        $remaining = 60 - (int)$elapsed;
        return ['allowed' => $remaining <= 0, 'remaining_seconds' => max(0, $remaining)];
    } catch (Exception) {
        return ['allowed' => true, 'remaining_seconds' => 0];
    }
}

function verify_email_code(int $user_id, string $code): array {
    ensure_email_verifications_table();
    $clean_code = preg_replace('/\D/', '', trim($code));
    if (strlen($clean_code) !== 6) {
        return ['success' => false, 'message' => 'Please enter a valid 6-digit verification code.'];
    }

    try {
        $pdo = get_db_connection();

        // Verify user exists and check current verification status
        $user_check = $pdo->prepare("SELECT id, is_email_verified FROM `users` WHERE `id` = :u LIMIT 1");
        $user_check->execute([':u' => $user_id]);
        $user_row = $user_check->fetch();
        if (!$user_row) {
            return ['success' => false, 'message' => 'Account not found or session expired. Please register again.'];
        }
        if ((int)$user_row['is_email_verified'] === 1) {
            return ['success' => true, 'already_verified' => true, 'message' => 'Your email has already been verified!'];
        }

        $stmt = $pdo->prepare("SELECT `id`, `code_hash`, `attempts` FROM `email_verifications` WHERE `user_id` = :u AND `expires_at` > NOW() ORDER BY `id` DESC LIMIT 1");
        $stmt->execute([':u' => $user_id]);
        $record = $stmt->fetch();

        if (!$record) {
            return ['success' => false, 'message' => 'The verification code has expired or is invalid. Please request a new code.'];
        }

        if ((int)$record['attempts'] >= 5) {
            $pdo->prepare("DELETE FROM `email_verifications` WHERE `id` = :id")->execute([':id' => $record['id']]);
            return ['success' => false, 'message' => 'Maximum verification attempts exceeded. Please request a new code.'];
        }

        if (!hash_equals($record['code_hash'], hash('sha256', $clean_code))) {
            $pdo->prepare("UPDATE `email_verifications` SET `attempts` = `attempts` + 1 WHERE `id` = :id")->execute([':id' => $record['id']]);
            $remaining = 4 - (int)$record['attempts'];
            return [
                'success' => false,
                'message' => $remaining > 0
                    ? "Incorrect verification code. {$remaining} attempt" . ($remaining === 1 ? "" : "s") . " remaining."
                    : "Incorrect verification code. Maximum attempts reached. Please request a new code."
            ];
        }

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE `users` SET `is_email_verified` = 1, `updated_at` = NOW() WHERE `id` = :u")->execute([':u' => $user_id]);
        $pdo->prepare("DELETE FROM `email_verifications` WHERE `user_id` = :u")->execute([':u' => $user_id]);
        $pdo->commit();

        return ['success' => true, 'message' => 'Your email has been verified successfully!'];
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("verify_email_code error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Verification failed due to a database error. Please try again.'];
    }
}

function is_user_email_verified(int $user_id): bool {
    ensure_email_verifications_table();
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT `is_email_verified` FROM `users` WHERE `id` = :u LIMIT 1");
        $stmt->execute([':u' => $user_id]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception) {
        return true;
    }
}
