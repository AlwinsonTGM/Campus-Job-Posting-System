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
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'profile') {
            $phone = trim($_POST['phone'] ?? '');
            $availability = $_POST['availability'] ?? [];

            if (($user['role'] ?? '') === 'student' && (empty($availability) || count($availability) === 0)) {
                $error = 'Candidate Shift Availability is required and cannot be empty. Please select at least one weekly timeslot.';
            } else {
                try {
                    $pdo = get_db_connection();
                    $updates = ['phone' => htmlspecialchars($phone)];

                    if (($user['role'] ?? '') === 'student') {
                        $updates['availability'] = json_encode($availability);
                    }

                    if ($user['role'] === 'employer') {
                        $name = trim($_POST['name'] ?? '');
                        $office_loc = trim($_POST['office_location'] ?? '');
                        if (!empty($name)) $updates['name'] = htmlspecialchars($name);
                        if (!empty($office_loc)) $updates['office_location'] = htmlspecialchars($office_loc);
                        // Organization name and department cannot be self-modified to prevent IDOR spoofing
                    } elseif ($user['role'] === 'admin') {
                        $name = trim($_POST['name'] ?? '');
                        $department = trim($_POST['department'] ?? '');
                        if (!empty($name)) $updates['name'] = htmlspecialchars($name);
                        if (!empty($department)) $updates['department'] = htmlspecialchars($department);
                    }

                    $set_clauses = [];
                    $params = [':id' => (int)$user['id']];
                    foreach ($updates as $col => $val) {
                        $set_clauses[] = "`$col` = :$col";
                        $params[":$col"] = $val;
                    }
                    $sql = "UPDATE `users` SET " . implode(', ', $set_clauses) . " WHERE `id` = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    // Refresh session user
                    $fresh = get_user_by_id($user['id']);
                    if ($fresh) {
                        unset($fresh['password']);
                        $_SESSION['user'] = $fresh;
                    }

                    set_flash('success', 'Contact information and weekly availability settings have been updated successfully.');
                    header('Location: settings.php');
                    exit;
                } catch (Exception $e) {
                    $error = 'Failed to update profile: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'request_profile_change') {
            $reason = trim($_POST['reason'] ?? '');
            $proof_path = null;
            if (isset($_FILES['proof_file']) && ($_FILES['proof_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $proof_path = save_uploaded_proof($_FILES['proof_file']);
            }

            if (!$proof_path) {
                $error = 'Please attach an official Certificate of Registration (COR), Student ID, or PSA document to verify your request.';
            } else {
                $res = create_profile_request($user['id'], $_POST, $proof_path, $reason);
                if ($res['success']) {
                    set_flash('success', 'Your official profile change request has been submitted to the University Admin / Registrar for review.');
                    header('Location: settings.php');
                    exit;
                } else {
                    $error = $res['message'];
                }
            }
        } elseif ($action === 'dismiss_notice') {
            dismiss_profile_request_notice($user['id']);
            header('Location: settings.php');
            exit;
        } elseif ($action === 'password') {
            $current_pass = $_POST['current_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            $fresh_user = get_user_by_id($user['id']);
            $stored_pass = $fresh_user['password'] ?? '';
            $is_current_valid = password_verify($current_pass, $stored_pass) || ($stored_pass === $current_pass);

            if (empty($current_pass)) {
                $error = 'Please enter your current password to confirm your identity.';
            } elseif (!$is_current_valid) {
                $error = 'The current password you entered is incorrect.';
            } elseif (strlen($new_pass) < 8) {
                $error = 'New password must contain at least 8 characters.';
            } elseif ($new_pass !== $confirm_pass) {
                $error = 'New password and confirm password do not match.';
            } else {
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                update_user_password($user['id'], $hashed_password);
                set_flash('success', 'Your password has been updated successfully.');
                header('Location: settings.php');
                exit;
            }
        }
    }
}

$user_availability = $user['availability'] ?? [
    'Mon - Morning (8AM–12NN)',
    'Wed - Morning (8AM–12NN)',
    'Fri - Afternoon (1PM–5PM)'
];

$pending_req = (($user['role'] ?? '') === 'student') ? get_pending_profile_request($user['id']) : null;
$recent_notice = (($user['role'] ?? '') === 'student') ? get_recent_profile_request_notice($user['id']) : null;

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
                    '',
                    'Profile & Account Settings',
                    'Update your contact details, weekly free shift availability, and security credentials.'
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

                <?php if ($pending_req): ?>
                    <div class="alert-paper alert-paper--warning mb-4 reveal-fade-rise">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-circle icon-circle-sm icon-circle-warning flex-shrink-0 mt-1">
                                    <i class="bi bi-hourglass-split fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-ink fs-6 d-block mb-1">Official Profile Change Request Pending Review</strong>
                                    <span class="small text-muted-custom">
                                        Submitted on <?= date('M d, Y h:i A', strtotime($pending_req['created_at'])) ?> &bull; Document: <span class="fw-semibold text-ink"><?= htmlspecialchars(basename($pending_req['proof_file'])) ?></span>
                                    </span>
                                    <div class="mt-2 small bg-white p-2 px-3 rounded-3 border border-line text-ink">
                                        <strong>Requested Updates:</strong>
                                        <span class="ms-1"><?= htmlspecialchars($pending_req['requested_profile']['course']) ?></span> &bull;
                                        <span><?= htmlspecialchars($pending_req['requested_profile']['year_level']) ?></span> &bull;
                                        <span><?= htmlspecialchars($pending_req['requested_profile']['sex']) ?></span>
                                        <?php if (!empty($pending_req['reason'])): ?>
                                            <div class="text-muted-custom mt-1"><em>"<?= htmlspecialchars($pending_req['reason']) ?>"</em></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="badge-status--pending flex-shrink-0">Pending Verification</span>
                        </div>
                    </div>
                <?php elseif ($recent_notice): ?>
                    <?php if ($recent_notice['status'] === 'approved'): ?>
                        <div class="alert-paper alert-paper--success mb-4 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success flex-shrink-0">
                                        <i class="bi bi-check-lg fs-5"></i>
                                    </div>
                                    <div>
                                        <strong class="text-ink d-block">Profile Update Request Approved!</strong>
                                        <span class="small text-muted-custom">Your institutional profile changes have been verified and applied to your official student record.</span>
                                        <?php if (!empty($recent_notice['admin_notes'])): ?>
                                            <div class="small text-ink mt-1"><strong>Admin Notes:</strong> <?= htmlspecialchars($recent_notice['admin_notes']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form action="settings.php" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="dismiss_notice">
                                    <button type="submit" class="btn-pill-outline btn-pill-sm">Dismiss</button>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($recent_notice['status'] === 'rejected'): ?>
                        <div class="alert-paper alert-paper--danger mb-4 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-danger flex-shrink-0 mt-1">
                                        <i class="bi bi-x-lg fs-5"></i>
                                    </div>
                                    <div>
                                        <strong class="text-ink d-block mb-1">Profile Update Request Declined</strong>
                                        <span class="small text-muted-custom">Your submitted verification document did not match institutional records or requires revision.</span>
                                        <?php if (!empty($recent_notice['admin_notes'])): ?>
                                            <div class="small text-ink mt-2 bg-white p-2 px-3 rounded-3 border border-line">
                                                <strong>Feedback from Registrar / Admin:</strong> <?= htmlspecialchars($recent_notice['admin_notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <button type="button" class="btn-pill btn-pill-sm" data-bs-toggle="modal" data-bs-target="#requestProfileModal">
                                        Submit New Proof
                                    </button>
                                    <form action="settings.php" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="dismiss_notice">
                                        <button type="submit" class="btn-pill-outline btn-pill-sm">Dismiss</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <!-- Left 7-col: Personal Profile & Availability -->
                    <div class="col-lg-7">
                        <div class="card-paper p-4 p-md-4 h-100 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title fs-5 mb-0">
                                    <i class="bi bi-person-circle text-accent me-2"></i> Profile &amp; Preferences
                                </h3>
                                <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle"><?= ucfirst($user['role'] ?? 'student') ?></span>
                            </div>

                            <form action="settings.php" method="POST" class="form-paper">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="profile">

                                <div class="mb-3">
                                    <label class="form-label">Institutional Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background-color: var(--cream);">
                                    </div>
                                    <span class="small text-muted-custom" style="font-size: 11px;">Primary institutional login email managed by University Registrar / MIS.</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="settings-phone">Contact Phone Number <span class="badge-status--accepted ms-1" style="font-size: 10px;">Self-Service</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" id="settings-phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+63 917 123 4567">
                                    </div>
                                    <span class="small text-muted-custom" style="font-size: 11px;">Used by department supervisors for scheduling interviews and duty dispatch notices.</span>
                                </div>

                                <?php if (($user['role'] ?? '') === 'student'): ?>
                                    <!-- Institutional & Academic Identity Lock Section -->
                                    <div class="p-3 bg-cream rounded-4 border border-line mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-shield-lock-fill text-accent fs-5"></i>
                                                <span class="small fw-bold text-ink">Institutional &amp; Academic Identity Record</span>
                                            </div>
                                            <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-toggle="modal" data-bs-target="#requestProfileModal" <?= $pending_req ? 'disabled' : '' ?>>
                                                <i class="bi bi-pencil-square"></i> Request Record Update
                                            </button>
                                        </div>
                                        <p class="small text-muted-custom mb-3" style="font-size: 11.5px;">
                                            To prevent credential misrepresentation in student assistantship assignments, modifications to your institutional records require submitting a valid Certificate of Registration (COR) or ID for administrative verification.
                                        </p>

                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="p-2 px-3 bg-white rounded-3 border border-line">
                                                    <span class="small text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-lock-fill text-muted-custom me-1"></i>Full Name</span>
                                                    <strong class="text-ink small"><?= htmlspecialchars($user['name']) ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 px-3 bg-white rounded-3 border border-line">
                                                    <span class="small text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-lock-fill text-muted-custom me-1"></i>Student ID Number</span>
                                                    <strong class="text-ink small"><?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 px-3 bg-white rounded-3 border border-line">
                                                    <span class="small text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-lock-fill text-muted-custom me-1"></i>Academic Institute</span>
                                                    <span class="text-ink small fw-semibold"><?= htmlspecialchars($user['department'] ?? 'Institute of Computing and Digital Innovation (ICDI)') ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 px-3 bg-white rounded-3 border border-line">
                                                    <span class="small text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-lock-fill text-muted-custom me-1"></i>Degree Program</span>
                                                    <span class="text-ink small fw-semibold"><?= htmlspecialchars($user['course'] ?? 'BS Information Systems (BSIS)') ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 px-3 bg-white rounded-3 border border-line">
                                                    <span class="small text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-lock-fill text-muted-custom me-1"></i>Year Level / Standing</span>
                                                    <span class="text-ink small fw-semibold"><?= htmlspecialchars($user['year_level'] ?? '2nd Year') ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 px-3 bg-white rounded-3 border border-line">
                                                    <span class="small text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-lock-fill text-muted-custom me-1"></i>Sex &amp; Age</span>
                                                    <span class="text-ink small fw-semibold">
                                                        <?= htmlspecialchars($user['sex'] ?? 'Male') ?> &bull; 
                                                        <?= htmlspecialchars((string)($user['age'] ?? (isset($user['birthdate']) ? calculate_age($user['birthdate']) : 20))) ?> yrs old
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if (!empty($user['proof_file'])): ?>
                                                <div class="col-12 mt-1">
                                                    <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-white rounded-3 border border-line small">
                                                        <span class="text-muted-custom"><i class="bi bi-file-earmark-check text-accent me-1"></i>Verified Attachment on File</span>
                                                        <span class="fw-semibold text-ink"><?= htmlspecialchars(basename($user['proof_file'])) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (($user['role'] ?? '') === 'employer'): ?>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="settings-name">Representative Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="settings-name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company / Office Name</label>
                                            <input type="text" name="organization_name" class="form-control" value="<?= htmlspecialchars($user['organization_name'] ?? ($user['department'] ?? '')) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Workplace Location</label>
                                            <input type="text" name="office_location" class="form-control" value="<?= htmlspecialchars($user['office_location'] ?? '') ?>" placeholder="Campus Office / Tech Park">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="settings-dept">Department / Division</label>
                                            <input type="text" name="department" id="settings-dept" class="form-control" value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (($user['role'] ?? '') === 'student'): ?>
                                    <!-- Availability Matrix Editor for Students -->
                                    <div class="mt-4 pt-3 border-top border-line" id="availability">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h4 class="card-paper-title fs-6 mb-0">
                                                <i class="bi bi-calendar-week text-accent me-2"></i> Weekly Free Shift Availability <span class="text-danger">*</span>
                                            </h4>
                                            <span class="badge-status--accepted" style="font-size: 10px;">&le; 20 hrs/wk</span>
                                        </div>
                                        <p class="small text-muted-custom mb-3">
                                            Keep this matrix up to date with your class-free periods so department supervisors can assign duty shifts:
                                        </p>
                                        
                                        <div class="card-paper p-3 bg-surface border border-line mb-3" id="settingsMatrixContainer">
                                            <div id="settingsAvailabilityErrorAlert" class="alert-paper alert-paper--danger mb-3" style="display: none;">
                                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                                <span>Candidate Shift Availability cannot be empty. Please select at least one weekly timeslot.</span>
                                            </div>
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
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="password">

                                <div class="mb-3">
                                    <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
                                        <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter existing password" required>
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('current_password', 'toggle-cur-pw')" aria-label="Toggle current password visibility">
                                            <i class="bi bi-eye" id="toggle-cur-pw"></i>
                                        </button>
                                    </div>
                                </div>

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
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mb-0">
                                    <input class="form-check-input flex-shrink-0 ms-0 mt-0" type="checkbox" id="emailNotif" checked>
                                    <label class="form-check-label small text-ink cursor-pointer mb-0" for="emailNotif">
                                        Email alerts for application status changes
                                    </label>
                                </div>
                                <div class="form-check form-switch d-flex align-items-center gap-3 ps-0 mb-0">
                                    <input class="form-check-input flex-shrink-0 ms-0 mt-0" type="checkbox" id="smsNotif" checked>
                                    <label class="form-check-label small text-ink cursor-pointer mb-0" for="smsNotif">
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

<?php if (($user['role'] ?? '') === 'student'): ?>
<!-- Student Profile Update Request Modal -->
<div class="modal fade" id="requestProfileModal" tabindex="-1" aria-labelledby="requestProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="settings.php" method="POST" enctype="multipart/form-data" class="modal-content rounded-4 border-line shadow-lg form-paper m-0">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="request_profile_change">

            <div class="modal-header bg-cream border-bottom border-line py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-circle icon-circle-sm icon-circle-success" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-ink mb-0" id="requestProfileModalLabel">Official Profile Change Request</h6>
                        <span class="small text-muted-custom" style="font-size: 11px;">Update institutional records with official proof document</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <div class="row g-3">
                    <!-- Left Column: Academic & Identity Fields -->
                    <div class="col-md-6 border-end-md border-line pe-md-3">
                        <div class="small fw-bold text-ink text-uppercase mb-2 pb-1 border-bottom border-line" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="bi bi-person-lines-fill text-accent me-1"></i> Requested Student Identity
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1" for="req-name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="req-name" class="form-control form-control-sm" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1" for="req-dept">Academic Institute <span class="text-danger">*</span></label>
                            <select name="department" id="req-dept" class="form-select form-select-sm" required>
                                <option value="">Select Academic Institute</option>
                                <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                    <option value="<?= htmlspecialchars($inst) ?>" <?= (isset($user['department']) && ($user['department'] === $inst || strpos($inst, $user['department']) !== false)) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1" for="req-course">Degree Program / Course <span class="text-danger">*</span></label>
                            <select name="course" id="req-course" class="form-select form-select-sm" required>
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

                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <label class="form-label small mb-1" for="req-year-level">Year Level <span class="text-danger">*</span></label>
                                <select name="year_level" id="req-year-level" class="form-select form-select-sm" required>
                                    <?php foreach (get_year_levels() as $val => $label): ?>
                                        <option value="<?= htmlspecialchars($val) ?>" <?= (isset($user['year_level']) && $user['year_level'] === $val) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="form-label small mb-1" for="req-sex">Sex / Gender <span class="text-danger">*</span></label>
                                <select name="sex" id="req-sex" class="form-select form-select-sm" required>
                                    <option value="" disabled <?= empty($user['sex']) ? 'selected' : '' ?>>Select Gender</option>
                                    <?php foreach (get_sex_options() as $val => $label): ?>
                                        <option value="<?= htmlspecialchars($val) ?>" <?= (isset($user['sex']) && $user['sex'] === $val) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-0">
                            <div class="col-7">
                                <label class="form-label small mb-1" for="req-birthdate">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="birthdate" id="req-birthdate" class="form-control form-control-sm" value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>" max="<?= date('Y-m-d', strtotime('-15 years')) ?>" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label small mb-1" for="req-age">Age</label>
                                <input type="number" name="age" id="req-age" class="form-control form-control-sm" value="<?= htmlspecialchars((string)($user['age'] ?? (isset($user['birthdate']) ? calculate_age($user['birthdate']) : ''))) ?>" readonly style="background-color: var(--cream);">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Document Proof & Justification -->
                    <div class="col-md-6 ps-md-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="small fw-bold text-ink text-uppercase mb-2 pb-1 border-bottom border-line" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="bi bi-file-earmark-check text-accent me-1"></i> Verification Document &amp; Reason
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-cream rounded-3 border border-line mb-2 small">
                                <span class="text-muted-custom" style="font-size: 11px;">Student ID:</span>
                                <strong class="text-ink"><?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?></strong>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small mb-1" for="req-proof-file">Proof Attachment (COR / ID / PSA) <span class="text-danger">*</span></label>
                                <input type="file" name="proof_file" id="req-proof-file" class="form-control form-control-sm" accept="image/*,application/pdf" required>
                                <span class="small text-muted-custom mt-1 d-block" style="font-size: 10.5px;">
                                    Attach clear PDF or image of your latest COR/ID (Max 10MB).
                                </span>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small mb-1" for="req-reason">Remarks / Justification</label>
                                <textarea name="reason" id="req-reason" rows="2" class="form-control form-control-sm" placeholder="e.g. Enrolled in 3rd Year BSIS; attached latest COR."></textarea>
                            </div>
                        </div>

                        <div class="p-2 px-3 bg-cream rounded-3 border border-line small text-muted-custom" style="font-size: 11px;">
                            <i class="bi bi-shield-lock-fill text-accent me-1"></i>
                            Subject to Admin / Registrar approval before taking effect.
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-surface border-top border-line py-2 px-3">
                <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pill btn-pill-sm">
                    <i class="bi bi-send-fill"></i> Submit Verification
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="assets/js/password-strength.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function bindAgeCalc(dobId, ageId) {
        const dob = document.getElementById(dobId);
        const age = document.getElementById(ageId);
        if (dob && age) {
            dob.addEventListener('change', function() {
                if (!this.value) return;
                const d = new Date(this.value);
                const now = new Date();
                let a = now.getFullYear() - d.getFullYear();
                const m = now.getMonth() - d.getMonth();
                if (m < 0 || (m === 0 && now.getDate() < d.getDate())) {
                    a--;
                }
                if (a >= 0 && a < 120) {
                    age.value = a;
                }
            });
        }
    }

    bindAgeCalc('settings-birthdate', 'settings-age');
    bindAgeCalc('req-birthdate', 'req-age');

    // Student Shift Availability Validation
    const profileForm = document.querySelector('form.form-paper input[name="action"][value="profile"]')?.closest('form');
    const settingsAlertBox = document.getElementById('settingsAvailabilityErrorAlert');
    const settingsMatrixContainer = document.getElementById('settingsMatrixContainer');

    if (profileForm && settingsMatrixContainer) {
        profileForm.addEventListener('submit', function(e) {
            const checkedBoxes = profileForm.querySelectorAll('input[name="availability[]"]:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                if (settingsAlertBox) {
                    settingsAlertBox.style.display = 'flex';
                }
                settingsMatrixContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                settingsMatrixContainer.style.borderColor = 'var(--st-declined, #E5484D)';
                return false;
            } else {
                if (settingsAlertBox) {
                    settingsAlertBox.style.display = 'none';
                }
                settingsMatrixContainer.style.borderColor = '';
            }
        });

        profileForm.querySelectorAll('input[name="availability[]"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                const checked = profileForm.querySelectorAll('input[name="availability[]"]:checked');
                if (checked.length > 0) {
                    if (settingsAlertBox) settingsAlertBox.style.display = 'none';
                    if (settingsMatrixContainer) settingsMatrixContainer.style.borderColor = '';
                }
            });
        });
    }
});
</script>
