<?php
/** Registration uses the existing controller and institution catalogue. */
$auth_partner = $selected_persona === 'approved_partner';
$auth_register = true;
$value = static fn(string $key): string => htmlspecialchars((string)($_POST[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$max_birthdate = (new DateTimeImmutable('today'))->modify('-18 years')->format('Y-m-d');
require __DIR__ . '/auth-start.php';
?>
<a class="back-login" href="<?= $auth_login_url ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to <?= $auth_partner ? 'partner &amp; staff' : 'student' ?> sign in</a>
<h1 id="form-title"><?= $auth_partner ? 'Become an industry partner' : 'Create your student account' ?></h1>
<p class="intro"><?= $auth_partner ? 'Apply to hire KLD talent. Career Services reviews each application.' : 'Tell us a little about yourself to get started.' ?></p>
<ol class="steps" aria-label="Registration progress" id="registration-progress">
    <li aria-current="step"><span>1</span> Your details</li>
    <li><span>2</span> <?= $auth_partner ? 'Organization' : 'Academic profile' ?></li>
    <li><span>3</span> Verify &amp; secure</li>
</ol>
<div class="error-summary" id="step-error-alert" role="alert" tabindex="-1" <?= !$error ? 'hidden' : 'data-server-error' ?>>
    <p id="step-error-message"><?= htmlspecialchars($error ?? '') ?></p>
</div>
<form action="register.php?role=<?= $auth_partner ? 'employer' : 'student' ?>" method="POST" enctype="multipart/form-data" id="register-form" data-initial-step="<?= (int)$initial_step ?>" data-role="<?= $auth_partner ? 'employer' : 'student' ?>" novalidate>
    <input type="hidden" name="role" value="<?= $auth_partner ? 'employer' : 'student' ?>">
    <input type="hidden" name="employer_type" value="<?= $auth_partner ? 'approved_partner' : '' ?>">
    <section class="registration-step" id="step-pane-1" data-step="1" aria-labelledby="step-title-1">
        <h2 class="form-section-title" id="step-title-1" tabindex="-1">Your details</h2>
        <?php if ($auth_partner): ?>
            <div class="field"><label for="reg-name">Contact person</label><input id="reg-name" name="name" autocomplete="name" value="<?= $value('name') ?>" placeholder="Full name" required></div>
        <?php else: ?>
            <div class="three-col">
                <div class="field"><label for="reg-first-name">First name</label><input id="reg-first-name" name="first_name" autocomplete="given-name" value="<?= $value('first_name') ?>" required></div>
                <div class="field"><label for="reg-middle-name">Middle name <span class="optional">(optional)</span></label><input id="reg-middle-name" name="middle_name" autocomplete="additional-name" value="<?= $value('middle_name') ?>"></div>
                <div class="field"><label for="reg-last-name">Last name</label><input id="reg-last-name" name="last_name" autocomplete="family-name" value="<?= $value('last_name') ?>" required></div>
            </div>
        <?php endif; ?>
        <div class="field">
            <label for="reg-email"><?= $auth_partner ? 'Company email' : 'Email address' ?></label>
            <input id="reg-email" name="email" type="email" autocomplete="email" value="<?= $value('email') ?>" placeholder="<?= $auth_partner ? 'name@company.com' : 'you@kld.edu.ph' ?>" aria-describedby="email-hint email-duplicate-feedback" required>
            <p class="hint" id="email-hint"><?= $auth_partner ? 'Use your business email. Career Services provides KLD office accounts.' : 'Use your KLD email if you have one. Personal emails require a KLD diploma for verification.' ?></p>
            <p class="hint" id="email-duplicate-feedback" aria-live="polite"></p>
        </div>
        <div class="field"><label for="reg-phone">Phone number</label><input id="reg-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" value="<?= $value('phone') ?>" placeholder="0917 123 4567" pattern="\+?[0-9][0-9 \(\)\-]{5,18}[0-9]" aria-describedby="phone-hint" required><p id="phone-hint" class="hint">7–15 digits; spaces, parentheses and hyphens are allowed. Use + for an international prefix.</p></div>
        <div class="step-actions"><button class="primary" type="button" data-next><span>Continue to <?= $auth_partner ? 'organization' : 'profile' ?></span><i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
    </section>
    <section class="registration-step" id="step-pane-2" data-step="2" aria-labelledby="step-title-2">
        <h2 class="form-section-title" id="step-title-2" tabindex="-1"><?= $auth_partner ? 'Organization' : 'Academic profile' ?></h2>
        <?php if ($auth_partner): ?>
            <div class="field"><label for="organization_name">Company / organization</label><input id="organization_name" name="organization_name" autocomplete="organization" value="<?= $value('organization_name') ?>" required></div>
            <div class="field"><label for="accreditation_number">MOA reference / permit number <span class="optional">(optional)</span></label><input id="accreditation_number" name="accreditation_number" value="<?= $value('accreditation_number') ?>"></div>
            <div class="field"><label for="partner_office_location">Workplace / office address <span class="optional">(optional)</span></label><input id="partner_office_location" name="office_location" autocomplete="street-address" value="<?= $value('office_location') ?>"></div>
        <?php else: ?>
            <div class="field">
                <label for="department-select">Academic institute</label>
                <select name="department" id="department-select" required>
                    <option value="">Select academic institute</option>
                    <?php foreach ($kld_institutes as $inst => $courses): ?><option value="<?= htmlspecialchars($inst) ?>" <?= isset($department) && $department === $inst ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option><?php endforeach; ?>
                    <option value="Other Institute / Outsider" <?= $is_other_institute_selected ? 'selected' : '' ?>>Other institute / university</option>
                </select>
            </div>
            <div class="field" id="other-institute-wrap"><label for="other_institute">Institute / university name</label><input id="other_institute" name="other_institute" value="<?= htmlspecialchars($other_institute_value) ?>"></div>
            <div class="field" id="course-wrap">
                <label for="course-select">Degree program</label>
                <select name="course" id="course-select" required>
                    <option value="">Select degree program</option>
                    <?php foreach ($kld_institutes as $inst => $courses): ?>
                        <optgroup label="<?= htmlspecialchars($inst) ?>"><?php foreach ($courses as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= isset($course) && $course === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?></optgroup>
                    <?php endforeach; ?>
                    <option value="Other Degree Program" <?= ($course ?? '') === 'Other Degree Program' ? 'selected' : '' ?>>Other degree program</option>
                </select>
            </div>
            <div class="two-col">
                <div class="field"><label for="student_id">Student ID</label><input id="student_id" name="student_id" value="<?= $value('student_id') ?>" placeholder="2025-2-000065" pattern="[0-9]{4}-[0-9]-[0-9]{6}" maxlength="13" aria-describedby="student-id-hint student-id-duplicate-feedback" required><p class="hint" id="student-id-hint">YYYY-T-XXXXXX · e.g. 2025-2-000065</p><p class="hint" id="student-id-duplicate-feedback" aria-live="polite"></p></div>
                <div class="field"><label for="year_level">Year level / status</label><select id="year_level" name="year_level" required><option value="">Select year / status</option><?php foreach ($year_levels_list as $val => $label): ?><option value="<?= htmlspecialchars($val) ?>" <?= ($year_level ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="two-col">
                <div class="field"><label for="reg-sex">Biological sex</label><select id="reg-sex" name="sex" required><option value="">Select biological sex</option><?php foreach ($sex_options_list as $val => $label): ?><option value="<?= htmlspecialchars($val) ?>" <?= ($sex ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option><?php endforeach; ?></select></div>
                <div class="field"><label for="reg-birthdate">Date of birth</label><input id="reg-birthdate" name="birthdate" type="date" autocomplete="bday" max="<?= $max_birthdate ?>" value="<?= $value('birthdate') ?>" aria-describedby="birthdate-hint" required><p class="hint" id="birthdate-hint">You must be at least 18 years old.</p></div>
            </div>
        <?php endif; ?>
        <div class="step-actions"><button class="text-button" type="button" data-back>Back</button><button class="primary" type="button" data-next><span>Continue to verification</span><i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
    </section>
    <section class="registration-step" id="step-pane-3" data-step="3" aria-labelledby="step-title-3">
        <h2 class="form-section-title" id="step-title-3">Verify &amp; secure</h2>
        <div class="field">
            <?php if ($auth_partner): ?>
                <label for="reg-permit-photo">Business permit / MOA <span class="optional">(optional)</span></label><input type="file" name="permit_photo" id="reg-permit-photo" accept=".pdf,.jpg,.jpeg,.png" aria-describedby="proof-hint">
            <?php else: ?>
                <label for="reg-student-proof" id="student-proof-label">Certificate of Registration (e-COR)</label><input type="file" name="student_proof" id="reg-student-proof" accept=".pdf,.jpg,.jpeg,.png" aria-describedby="proof-hint student-proof-desc" required><p class="hint" id="student-proof-desc">Required to verify your current enrollment. Alumni must upload their official KLD diploma.</p>
            <?php endif; ?>
            <p class="hint" id="proof-hint">PDF, JPG or PNG · Up to 5 MB. <?= $auth_partner ? 'Career Services reviews your accreditation documents.' : 'Your credential is shared with employers when you apply.' ?></p>
            <?php if ($error): ?><p class="hint">Please select your document again after correcting the form.</p><?php endif; ?>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <div class="password-wrap"><input type="password" name="password" id="password" autocomplete="new-password" placeholder="At least 8 characters" minlength="8" aria-describedby="password-minimum password-strength-text password-requirements" required><button class="password-toggle" type="button" data-password="password" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div>
            <div class="password-guidance">
                <div class="password-strength-heading"><span>Password strength</span><strong id="password-strength-text" role="status" aria-live="polite">Not entered</strong></div>
                <div class="password-meter" id="password-strength-meter" role="meter" aria-label="Password strength" aria-valuemin="0" aria-valuemax="4" aria-valuenow="0" aria-valuetext="No password entered"><span id="password-meter-fill"></span></div>
                <p class="hint" id="password-minimum">Minimum: 8 characters. Add uppercase, numbers, or symbols to exceed weak strength.</p>
                <ul class="password-requirements" id="password-requirements">
                    <?php foreach (['length' => '8+ characters (minimum)', 'upper' => 'One uppercase letter', 'lower' => 'One lowercase letter', 'number' => 'One number', 'special' => 'One symbol'] as $key => $label): ?>
                        <li data-requirement="<?= $key ?>" data-met="false"><i class="bi bi-circle" aria-hidden="true"></i><span class="requirement-state sr-only">Not yet met: </span><span><?= $label ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="field"><label for="confirm_password">Confirm password</label><div class="password-wrap"><input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password="confirm_password" aria-label="Show confirm password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
        <label class="terms"><input type="checkbox" id="termsCheck" name="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?> required><span>I agree to the <a href="terms.php?from=register" target="_blank" rel="noopener">Terms of Service</a> and have read the <a href="privacy.php?from=register" target="_blank" rel="noopener">Privacy Policy</a>.</span></label>
        <div class="step-actions"><button class="text-button" type="button" data-back>Back</button><button class="primary" type="submit" id="btn-submit-registration"><span><?= $auth_partner ? 'Submit application' : 'Create student account' ?></span><i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
    </section>
    <noscript><style>.step-actions button[type="button"], .steps { display: none; }</style><p class="hint">Complete all three sections, then submit your registration.</p></noscript>
</form>
<?php require __DIR__ . '/auth-end.php'; ?>
