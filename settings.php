<?php
/**
 * Campus Job Posting System - Account Settings
 */
require_once __DIR__ . '/includes/data-helper.php';

require_auth();
$user = get_logged_user();
$page_title = 'Account Settings';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $department = trim($_POST['department'] ?? '');

        if (empty($name)) {
            $error = 'Name cannot be empty.';
        } else {
            // Update in session and users.json
            $users = $_SESSION['users'] ?? load_json_file('users.json');
            foreach ($users as $k => $u) {
                if ($u['id'] == $user['id']) {
                    $users[$k]['name'] = htmlspecialchars($name);
                    $users[$k]['phone'] = htmlspecialchars($phone);
                    $users[$k]['course'] = htmlspecialchars($course);
                    $users[$k]['department'] = htmlspecialchars($department);
                    $_SESSION['user'] = $users[$k];
                    break;
                }
            }
            $_SESSION['users'] = $users;
            save_json_file('users.json', $users);
            set_flash('success', 'Profile information updated successfully.');
            header('Location: settings.php');
            exit;
        }
    } elseif ($action === 'password') {
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'New password and confirmation do not match.';
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
            set_flash('success', 'Password updated successfully.');
            header('Location: settings.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Account Settings</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-ink mb-0">Account Settings</h2>
            <span class="pill-badge pill-badge-ink text-uppercase"><?= htmlspecialchars($user['role']) ?></span>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4 rounded-3">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left 6-col: Personal Profile Settings -->
            <div class="col-lg-6">
                <div class="card border-line shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-ink mb-3"><i class="bi bi-person-circle text-accent me-2"></i> Profile Details</h5>
                    
                    <form action="settings.php" method="POST">
                        <input type="hidden" name="action" value="profile">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Institutional Email (Read Only)</label>
                            <input type="email" class="form-control bg-cream" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <?php if ($user['role'] === 'student'): ?>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-ink">Student ID</label>
                                    <input type="text" class="form-control bg-cream" value="<?= htmlspecialchars($user['student_id'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-ink">Degree Program</label>
                                    <select name="course" class="form-select">
                                        <option value="">Select Degree Program</option>
                                        <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                            <optgroup label="<?= htmlspecialchars($inst) ?>">
                                                <?php foreach ($courses as $c): ?>
                                                    <option value="<?= htmlspecialchars($c) ?>" <?= (isset($user['course']) && ($user['course'] === $c || strpos($c, $user['course']) !== false || strpos($user['course'], $c) !== false)) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Academic Institute / Campus Office</label>
                            <select name="department" class="form-select">
                                <option value="">Select Institute / Office</option>
                                <optgroup label="Academic Institutes">
                                    <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                        <option value="<?= htmlspecialchars($inst) ?>" <?= (isset($user['department']) && ($user['department'] === $inst || strpos($inst, $user['department']) !== false || strpos($user['department'], $inst) !== false)) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Administrative Offices">
                                    <option value="Office of the University Registrar" <?= (isset($user['department']) && strpos($user['department'], 'Registrar') !== false) ? 'selected' : '' ?>>Office of the University Registrar</option>
                                    <option value="Student Affairs & Services Office (SASO)" <?= (isset($user['department']) && (strpos($user['department'], 'Student Affairs') !== false || strpos($user['department'], 'SASO') !== false || strpos($user['department'], 'OSA') !== false)) ? 'selected' : '' ?>>Student Affairs & Services Office (SASO)</option>
                                    <option value="Management Information Systems (MIS)" <?= (isset($user['department']) && (strpos($user['department'], 'MIS') !== false || strpos($user['department'], 'Management Information') !== false)) ? 'selected' : '' ?>>Management Information Systems (MIS)</option>
                                    <option value="KLD University Library" <?= (isset($user['department']) && strpos($user['department'], 'Library') !== false) ? 'selected' : '' ?>>KLD University Library</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-ink">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+63 917 123 4567">
                        </div>

                        <button type="submit" class="btn-accent-pill py-2 px-4">
                            <i class="bi bi-save me-1"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right 6-col: Change Password & Preferences -->
            <div class="col-lg-6">
                <div class="card border-line shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-ink mb-3"><i class="bi bi-shield-lock text-accent me-2"></i> Security & Password</h5>
                    
                    <form action="settings.php" method="POST">
                        <input type="hidden" name="action" value="password">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-ink">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                        </div>

                        <button type="submit" class="btn-outline-pill py-2 px-4 mb-4">
                            <i class="bi bi-key me-1"></i> Update Password
                        </button>
                    </form>

                    <hr class="border-line">

                    <h6 class="fw-bold text-ink mb-2 small text-uppercase">Notification Preferences</h6>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                        <label class="form-check-label small text-muted-custom" for="emailNotif">Receive email updates on application status changes</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="smsNotif" checked>
                        <label class="form-check-label small text-muted-custom" for="smsNotif">Receive SMS interview reminders</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
