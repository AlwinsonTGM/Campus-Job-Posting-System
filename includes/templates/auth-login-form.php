<?php require __DIR__ . '/auth-start.php'; ?>
<nav class="audience-nav" aria-label="Choose sign-in portal">
    <a href="login.php<?= $safe_next !== '' ? '?next=' . urlencode($safe_next) : '' ?>" id="link-student-login" <?= !$auth_partner ? 'aria-current="page"' : '' ?>><i class="bi bi-mortarboard" aria-hidden="true"></i> Student</a>
    <a href="portal-login.php<?= $safe_next !== '' ? '?next=' . urlencode($safe_next) : '' ?>" id="link-portal-login" <?= $auth_partner ? 'aria-current="page"' : '' ?>><i class="bi bi-buildings" aria-hidden="true"></i> Partner &amp; staff</a>
</nav>
<h1 id="form-title"><?= $auth_partner ? 'Partner &amp; staff sign in' : 'Student sign in' ?></h1>
<p class="intro"><?= $auth_partner ? 'For department offices, industry partners, and administrators.' : 'Welcome back. Pick up where you left off.' ?></p>
<?php if ($error): ?>
    <div class="error-summary" role="alert" tabindex="-1" data-server-error>
        <p><?= htmlspecialchars($error) ?></p>
        <?php if (!empty($portal_redirect_hint) || !empty($student_redirect_hint)): ?>
            <a href="<?= $auth_partner ? 'login.php' : 'portal-login.php' ?>?email=<?= urlencode($email ?? '') ?><?= $safe_next !== '' ? '&amp;next=' . urlencode($safe_next) : '' ?>">Go to <?= $auth_partner ? 'student sign in' : 'the partner &amp; staff portal' ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<form action="<?= $auth_login_url ?>" method="POST" class="auth-login-form">
    <?php if ($safe_next !== ''): ?><input type="hidden" name="next" value="<?= htmlspecialchars($safe_next) ?>"><?php endif; ?>
    <div class="field">
        <label for="login-email"><?= $auth_partner ? 'Work email' : 'Email address' ?></label>
        <input type="email" name="email" id="login-email" autocomplete="username" placeholder="<?= $auth_partner ? 'name@organization.com' : 'you@kld.edu.ph' ?>" value="<?= htmlspecialchars($email ?? '') ?>" required>
    </div>
    <div class="field">
        <div class="label-row"><label for="login-password">Password</label><a href="forgot-pass.php">Forgot password?</a></div>
        <div class="password-wrap"><input type="password" name="password" id="login-password" autocomplete="current-password" placeholder="Enter your password" required><button class="password-toggle" type="button" data-password="login-password" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div>
    </div>
    <button class="primary" type="submit"><span><?= $auth_partner ? 'Sign in to portal' : 'Sign in as student' ?></span><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
</form>
<p class="alternate"><?= $auth_partner ? 'New industry partner?' : 'New to Campus Hire?' ?> <a href="register.php?role=<?= $auth_partner ? 'employer' : 'student' ?>"><?= $auth_partner ? 'Apply for accreditation' : 'Create an account' ?></a></p>
<?php if ($auth_partner): ?><p class="office-note">KLD office and administrator accounts are provided by Career Services. Only external industry partners need to register.</p><?php endif; ?>
<?php require __DIR__ . '/auth-end.php'; ?>
