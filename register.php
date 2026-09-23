<?php
/**
 * Campus Job Posting System - User Registration
 * Archetype A: Auth Split Card Shell & Live Password Meter (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/includes/mailer.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
$preselected_role = $_GET['role'] ?? 'student';
$initial_step = 1;
$selected_persona = 'student';
if ($preselected_role === 'employer') {
    $emp_t_get = $_GET['type'] ?? ($_GET['employer_type'] ?? 'university_office');
    $selected_persona = ($emp_t_get === 'approved_partner') ? 'approved_partner' : 'university_office';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $raw_name = trim($_POST['name'] ?? '');

    if (!empty($raw_name) && empty($first_name)) {
        $parts = explode(' ', $raw_name);
        $first_name = array_shift($parts);
        $last_name = !empty($parts) ? implode(' ', $parts) : $first_name;
        $name = $raw_name;
    } else {
        $name = trim($first_name . ($middle_name !== '' ? ' ' . $middle_name : '') . ' ' . $last_name);
    }
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $raw_role = $_POST['role'] ?? 'student';
    $employer_type = $_POST['employer_type'] ?? '';
    if (!in_array($raw_role, ['student', 'employer'], true)) {
        $role = (!empty($employer_type) || !empty($_POST['office_department']) || !empty($_POST['organization_name'])) ? 'employer' : 'student';
    } else {
        $role = $raw_role;
    }
    if (empty($employer_type)) {
        $employer_type = 'university_office';
    }
    $preselected_role = $role;
    if ($role === 'employer') {
        $selected_persona = ($employer_type === 'approved_partner') ? 'approved_partner' : 'university_office';
    } else {
        $selected_persona = 'student';
    }
    $organization_name = trim($_POST['organization_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    
    if ($role === 'student') {
        if ($department === 'Other Institute / Outsider' && !empty($_POST['other_institute'])) {
            $department = trim($_POST['other_institute']);
        }
    } elseif ($role === 'employer') {
        if ($employer_type === 'university_office') {
            $office_dept = trim($_POST['office_department'] ?? '');
            if (!empty($office_dept)) {
                $department = $office_dept;
                $organization_name = $office_dept;
            }
            $office_location = trim($_POST['office_location'] ?? '');
            $accreditation_number = trim($_POST['office_accreditation_number'] ?? '');
        }
    }
    
    $course = trim($_POST['course'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $sex = trim($_POST['sex'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = !empty($birthdate) ? calculate_age($birthdate) : 20;
    $phone = trim($_POST['phone'] ?? '');
    $office_location = trim($_POST['office_location'] ?? '');
    $accreditation_number = trim($_POST['accreditation_number'] ?? '');

    // Server-side validation
    if ((empty($first_name) && empty($raw_name)) || empty($email) || empty($password)) {
        $error = 'Please fill in all mandatory fields (Name, Email, Password).';
        $initial_step = empty($email) || (empty($first_name) && empty($raw_name)) ? 1 : 3;
    } elseif (empty($phone)) {
        $error = 'Please provide a valid contact phone number.';
        $initial_step = 1;
    } elseif (preg_match('/[a-zA-Z]/', $phone) || !preg_match('/^[\+]?[0-9\s\-()]{7,20}$/', $phone) || strlen(preg_replace('/[^0-9]/', '', $phone)) < 7 || strlen(preg_replace('/[^0-9]/', '', $phone)) > 15) {
        $error = 'Contact phone number must contain valid numbers only (e.g. 09171234567 or +63 917 123 4567). Alphabetic characters are not allowed.';
        $initial_step = 1;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
        $initial_step = 1;
    } elseif (!($domain_validation = validate_email_domain_dns($email))['valid']) {
        $error = $domain_validation['error'];
        $initial_step = 1;
    } elseif (is_email_registered($email)) {
        $error = 'This KLD account / email address is already registered. Please sign in or use another email.';
        $initial_step = 1;
    } elseif ($role === 'employer' && $employer_type === 'university_office' && !preg_match('/@kld\.edu\.ph$/i', $email)) {
        $error = 'University Office accounts must use an official @kld.edu.ph institutional email address. External partners must select "Industry Partner".';
        $initial_step = 1;
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
        $initial_step = 3;
    } elseif ($password !== $confirm_password) {
        $error = 'Password and Confirm Password do not match.';
        $initial_step = 3;
    } elseif ($role === 'student' && (empty($student_id) || empty($department) || empty($course) || empty($year_level) || empty($sex) || empty($birthdate))) {
        $error = 'Please complete all student profile fields (Student ID, Academic Institute, Degree Program, Year Level, Biological Sex, and Date of Birth).';
        $initial_step = 2;
    } elseif ($role === 'student' && is_student_id_registered($student_id)) {
        $error = 'This Student ID Number (' . htmlspecialchars($student_id) . ') is already registered. If this is your account, please sign in.';
        $initial_step = 2;
    } else {
        $permit_file_path = null;
        if ($role === 'employer' && isset($_FILES['permit_photo']) && ($_FILES['permit_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $permit_file_path = save_uploaded_permit($_FILES['permit_photo']);
        }

        $proof_file_path = null;
        if ($role === 'student') {
            if (isset($_FILES['student_proof']) && ($_FILES['student_proof']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $proof_file_path = save_uploaded_proof($_FILES['student_proof']);
            }
            if (!$proof_file_path) {
                $error = 'Please upload a valid Certificate of Registration (COR) or Student ID attachment (PDF, JPG, PNG).';
                $initial_step = 3;
            }
        }

        if (!$error) {
            $res = register_user([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'employer_type' => $employer_type,
                'organization_name' => $organization_name ?: ($department ?: $name),
                'student_id' => $student_id,
                'department' => $department ?: ($organization_name ?: 'General'),
                'course' => $course,
                'year_level' => $year_level,
                'sex' => $sex,
                'birthdate' => $birthdate,
                'age' => $age,
                'phone' => $phone,
                'office_location' => $office_location,
                'accreditation_number' => $accreditation_number,
                'permit_file' => $permit_file_path,
                'proof_file' => $proof_file_path
            ], $permit_file_path, $proof_file_path);

            if ($res['success']) {
                $new_u = $res['user'];
                $role_msg = ($role === 'employer')
                    ? (($employer_type === 'approved_partner')
                        ? 'Your business permit and partner profile will be reviewed by Career Services.'
                        : 'Your organization credentials have been submitted for administrative verification.')
                    : 'Your student registration and attached credentials are under review by the Administrator.';

                $_SESSION['pending_verification'] = [
                    'user_id'      => (int)$new_u['id'],
                    'email'        => $new_u['email'],
                    'name'         => $new_u['name'],
                    'role'         => $new_u['role'],
                    'role_message' => $role_msg
                ];

                $code = create_email_verification_code((int)$new_u['id']);
                $mail_res = send_verification_code_email($new_u['email'], $new_u['name'], $code);

                if ($mail_res['smtp_configured']) {
                    set_flash('info', 'We sent a 6-digit verification code to your institutional email.');
                } else {
                    set_flash('warning', 'Notice: Outbound SMTP is not configured in .env. Please configure MAIL_USERNAME and MAIL_PASSWORD.');
                }

                header('Location: verify-email.php');
                exit;
            } else {
                $error = $res['message'];
                if (stripos($error, 'email') !== false) {
                    $initial_step = 1;
                }
            }
        }
    }
}

$page_title = 'Create an Account';

// Preload view data — no DB/service calls in the template
$kld_institutes = get_kld_institutes_and_courses();
$year_levels_list = get_year_levels();
$sex_options_list = get_sex_options();
$is_other_institute_selected = isset($department) && (
    $department === 'Other Institute / Outsider'
    || (!empty($department) && !array_key_exists($department, $kld_institutes))
);
$other_institute_value = $_POST['other_institute']
    ?? ((isset($department) && !array_key_exists($department, $kld_institutes)) ? $department : '');

require __DIR__ . '/includes/templates/register-view.php';
