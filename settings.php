<?php
/**
 * Campus Job Posting System - Account Settings
 * Archetype E: Settings & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

require_auth();
$user = get_logged_user();
$page_title = 'Account Settings & Preferences';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $availability = $_POST['availability'] ?? ($user['availability'] ?? []);

        if (empty($name)) {
            $error = 'Full name cannot be empty.';
        } else {
            $org_name = trim($_POST['organization_name'] ?? '');
            $office_loc = trim($_POST['office_location'] ?? '');
            
            // Update in session and users.json
            $users = $_SESSION['users'] ?? load_json_file('users.json');
            foreach ($users as $k => $u) {
                if ($u['id'] == $user['id']) {
                    $users[$k]['name'] = htmlspecialchars($name);
                    $users[$k]['phone'] = htmlspecialchars($phone);
                    $users[$k]['course'] = htmlspecialchars($course);
                    $users[$k]['department'] = htmlspecialchars($department);
                    $users[$k]['availability'] = $availability;
                    if ($u['role'] === 'employer') {
                        if (!empty($org_name)) $users[$k]['organization_name'] = htmlspecialchars($org_name);
                        if (!empty($office_loc)) $users[$k]['office_location'] = htmlspecialchars($office_loc);
                    }
                    $_SESSION['user'] = $users[$k];
                    break;
                }
            }
            $_SESSION['users'] = $users;
            save_json_file('users.json', $users);
            set_flash('success', 'Profile and shift availability settings have been updated successfully.');
            header('Location: settings.php');
            exit;
        }
    } elseif ($action === 'password') {
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (strlen($new_pass) < 8) {
            $error = 'New password must contain at least 8 characters.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'New password and confirm password do not match.';
        } else {
            $users = $_SESSION['users'] ?? load_json_file('users.json');
            foreach ($users as $k => $u) {
                if ($u['id'] == $user['id']) {
                    $users[$k]['password'] = $new_pass;
                    $_SESSION['user']['password'] = $new_pass;
                    break;
                }
            }
            $_SESSION['users'] = $users;
            save_json_file('users.json', $users);
            set_flash('success', 'Your password has been updated successfully.');
            header('Location: settings.php');
            exit;
        }
    }
}

$user_availability = $user['availability'] ?? [
    'Mon - Morning (8AM–12NN)',
    'Wed - Morning (8AM–12NN)',
    'Fri - Afternoon (1PM–5PM)'
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                render_page_head(
                    '<i class="bi bi-gear-fill text-accent me-1"></i> Account Management · ' . ucfirst($user['role'] ?? 'User'),
                    'Profile & Account Settings',
                    'Update your profile information, free class schedule matrix, and security credentials.'
                );
                ?>

                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <!-- Left 6-col: Personal Profile & Availability -->
                    <div class="col-lg-7">
                        <div class="card-paper p-4 p-md-4 h-100 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title fs-5 mb-0">
                                    <i class="bi bi-person-circle text-accent me-2"></i> Profile Information
                                </h3>
                                <span class="pill-badge"><?= ucfirst($user['role'] ?? 'student') ?></span>
                            </div>

                            <form action="settings.php" method="POST" class="form-paper">
                                <input type="hidden" name="action" value="profile">

                                <div class="mb-3">
                                    <label class="form-label">Institutional Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background-color: var(--cream);">
                                    </div>
                                    <span class="small text-muted-custom" style="font-size: 11px;">Primary login email managed by University Registrar / MIS.</span>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="settings-name">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="settings-name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="settings-phone">Contact Phone</label>
                                        <input type="text" name="phone" id="settings-phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+63 917 123 4567">
                                    </div>
                                </div>

                                <?php if (($user['role'] ?? '') === 'student'): ?>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Student ID Number</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?>" readonly style="background-color: var(--cream);">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="settings-course">Degree Program / Course</label>
                                            <select name="course" id="settings-course" class="form-select">
                                                <option value="">Select Degree Program</option>
                                                <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                                    <optgroup label="<?= htmlspecialchars($inst) ?>">
                                                        <?php foreach ($courses as $c): ?>
                                                            <option value="<?= htmlspecialchars($c) ?>" <?= (isset($user['course']) && ($user['course'] === $c || strpos($c, $user['course']) !== false)) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label" for="settings-dept">Academic Institute / Department</label>
                                    <select name="department" id="settings-dept" class="form-select">
                                        <option value="">Select Institute / Office</option>
                                        <optgroup label="Academic Institutes">
                                            <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                                <option value="<?= htmlspecialchars($inst) ?>" <?= (isset($user['department']) && ($user['department'] === $inst || strpos($inst, $user['department']) !== false)) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="Administrative Offices">
                                            <option value="Office of the University Registrar" <?= (isset($user['department']) && strpos($user['department'], 'Registrar') !== false) ? 'selected' : '' ?>>Office of the University Registrar</option>
                                            <option value="Student Affairs & Services Office (SASO)" <?= (isset($user['department']) && strpos($user['department'], 'Student Affairs') !== false) ? 'selected' : '' ?>>Student Affairs & Services Office (SASO)</option>
                                            <option value="Management Information Systems (MIS)" <?= (isset($user['department']) && strpos($user['department'], 'MIS') !== false) ? 'selected' : '' ?>>Management Information Systems (MIS)</option>
                                            <option value="KLD University Library" <?= (isset($user['department']) && strpos($user['department'], 'Library') !== false) ? 'selected' : '' ?>>KLD University Library</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <?php if (($user['role'] ?? '') === 'employer'): ?>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Company / Office Name</label>
                                            <input type="text" name="organization_name" class="form-control" value="<?= htmlspecialchars($user['organization_name'] ?? ($user['department'] ?? '')) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Workplace Location</label>
                                            <input type="text" name="office_location" class="form-control" value="<?= htmlspecialchars($user['office_location'] ?? '') ?>" placeholder="Campus Office / Tech Park">
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (($user['role'] ?? '') === 'student'): ?>
                                    <!-- Availability Matrix Editor for Students -->
                                    <div class="mt-4 pt-3 border-top border-line" id="availability">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h4 class="card-paper-title fs-6 mb-0">
                                                <i class="bi bi-calendar-week text-accent me-2"></i> Weekly Free Shift Availability
                                            </h4>
                                            <span class="badge-status--accepted" style="font-size: 10px;">&le; 20 hrs/wk</span>
                                        </div>
                                        <p class="small text-muted-custom mb-3">
                                            Keep this matrix up to date with your class-free periods so department supervisors can assign duty shifts:
                                        </p>
                                        
                                        <div class="card-paper p-3 bg-surface border border-line mb-3">
                                            <?php render_availability_matrix($user_availability, 'availability[]', false); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="pt-2">
                                    <button type="submit" class="btn-pill">
                                        <i class="bi bi-check-circle-fill"></i> Save Profile Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right 5-col: Password & Notification Settings -->
                    <div class="col-lg-5">
                        <div class="card-paper p-4 p-md-4 h-100 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title fs-5 mb-0">
                                    <i class="bi bi-shield-lock text-accent me-2"></i> Security & Password
                                </h3>
                            </div>

                            <form action="settings.php" method="POST" class="form-paper" id="register-form">
                                <input type="hidden" name="action" value="password">

                                <div class="mb-3">
                                    <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="password" name="new_password" id="password" class="form-control" placeholder="Minimum 8 characters" required>
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', 'toggle-set-pw')" aria-label="Toggle password visibility">
                                            <i class="bi bi-eye" id="toggle-set-pw"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Dynamic Strength Bar -->
                                    <div class="password-meter-bar">
                                        <div id="password-meter-fill" class="password-meter-fill"></div>
                                    </div>
                                    <div id="password-strength-text" class="small text-muted-custom mt-1">
                                        Enter new password to evaluate strength
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type new password" required>
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('confirm_password', 'toggle-set-cpw')" aria-label="Toggle password visibility">
                                            <i class="bi bi-eye" id="toggle-set-cpw"></i>
                                        </button>
                                    </div>
                                    <div id="confirm-feedback" class="mt-1"></div>
                                </div>

                                <button type="submit" class="btn-pill-outline w-100 mb-4">
                                    <i class="bi bi-shield-check"></i> Update Password
                                </button>
                            </form>

                            <hr class="border-line my-4">

                            <h4 class="card-paper-title fs-6 mb-3">
                                <i class="bi bi-bell text-accent me-2"></i> Notification Channels
                            </h4>
                            <div class="d-flex flex-column gap-2 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                    <label class="form-check-label small text-ink" for="emailNotif">
                                        Email alerts for application status changes
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="smsNotif" checked>
                                    <label class="form-check-label small text-ink" for="smsNotif">
                                        SMS notices for interview appointments
                                    </label>
                                </div>
                            </div>

                            <div class="p-3 bg-cream rounded-3 border border-line small text-muted-custom">
                                <i class="bi bi-shield-check text-accent me-1"></i>
                                Account data is strictly governed under the <strong>Data Privacy Act of 2012 (RA 10173)</strong>.
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>

<script src="assets/js/password-strength.js"></script>
