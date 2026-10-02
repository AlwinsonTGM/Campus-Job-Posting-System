<?php
declare(strict_types=1);

/**
 * Campus Job Posting System - SessionGuard
 * Unified authentication, role authorization, return-to routing, and verification state machine.
 */

require_once __DIR__ . '/data-helper.php';

/**
 * Whether registration may dispatch a real verification email.
 *
 * A QA/test server must never send mail: the harness creates throwaway accounts
 * on every run, and those would become real deliveries. Blanking MAIL_USERNAME
 * in a launcher is NOT reliable - load_env() calls putenv() for every key in
 * .env, and an emptied variable reads back as false, so real credentials
 * reappear as soon as .env contains them.
 *
 * Declared here (not in user-service.php) because SessionGuard calls it and
 * this file must not depend on load order. Set QA_MAIL_DISABLED=1 on any such
 * server; qa/serve-qa.cmd does. When the gate is closed the app takes the
 * ordinary "SMTP not configured" path: the code is stored in the session and
 * rendered on screen, so the full OTP journey stays testable without email.
 */
function registration_mail_allowed(): bool {
    return trim((string)getenv('QA_MAIL_DISABLED')) !== '1';
}

class SessionGuard {
    /** @var callable|null Test interceptor: fn(string $url, int $statusCode, string $reason): void */
    private static $redirectHandler = null;

    /**
     * Set a custom redirect handler for testing/CLI interception.
     */
    public static function setRedirectHandler(?callable $handler): void {
        self::$redirectHandler = $handler;
    }

    /**
     * Get the active authenticated user, or null if unauthenticated.
     */
    public static function user(): ?array {
        return get_logged_user();
    }

    /**
     * Check if a valid session exists.
     */
    public static function check(): bool {
        return is_logged_in();
    }

    /**
     * Check if the active user possesses any of the specified roles.
     */
    public static function hasRole(string ...$roles): bool {
        $u = self::user();
        if (!$u || empty($u['role'])) {
            return false;
        }
        return in_array($u['role'], $roles, true);
    }

    /**
     * Get the canonical role dashboard URL.
     */
    public static function getRoleDashboardUrl(?string $role): string {
        return match ($role) {
            'student'  => 'student/dashboard.php',
            'employer' => 'employer/dashboard.php',
            'admin'    => 'admin/reports.php',
            default    => 'index.php'
        };
    }

    /**
     * Compute relative path prefix based on SCRIPT_NAME nesting.
     */
    public static function getDirectoryPrefix(): string {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        return (strpos($script, '/admin/') !== false ||
                strpos($script, '/employer/') !== false ||
                strpos($script, '/student/') !== false) ? '../' : '';
    }

    /**
     * Sanitize return-to target to prevent open redirect vulnerabilities.
     */
    public static function sanitizeReturnTo(?string $target): ?string {
        if ($target === null || $target === '') {
            return null;
        }
        $target = trim($target);
        if (preg_match('#^(https?:|//)#i', $target)) {
            return null;
        }
        $target = ltrim($target, '/');
        if (preg_match('#^(student|employer|admin|index\.php|jobs\.php|faqs\.php|about-us\.php|updates\.php|update-detail\.php)\b#i', $target)) {
            return $target;
        }
        return null;
    }

    /**
     * Execute HTTP redirect or delegate to test interceptor.
     */
    public static function redirect(string $url, string $reason = 'redirect'): void {
        if (self::$redirectHandler !== null) {
            (self::$redirectHandler)($url, 302, $reason);
            return;
        }

        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }
        echo '<script>window.location.href = ' . json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>';
        exit;
    }

    /**
     * Redirect active user to their corresponding role dashboard.
     */
    public static function redirectByRole(?string $role = null): void {
        $user = self::user();
        $targetRole = $role ?? ($user['role'] ?? null);
        $prefix = self::getDirectoryPrefix();
        self::redirect($prefix . self::getRoleDashboardUrl($targetRole), 'role_dashboard');
    }

    /**
     * Protect route with authentication and role gating.
     * Auto-captures return-to deep link unless overridden.
     */
    public static function protect(
        array $allowedRoles = [],
        ?string $returnTo = null,
        ?string $loginMessage = 'Please sign in to access this page.'
    ): array {
        $prefix = self::getDirectoryPrefix();

        if (!self::check()) {
            if ($loginMessage !== null) {
                set_flash('warning', $loginMessage);
            }

            if ($returnTo === null) {
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $scriptRel = basename($scriptName);
                $query = $_SERVER['QUERY_STRING'] ?? '';
                $querySuffix = $query !== '' ? '?' . $query : '';

                if (strpos($scriptName, '/student/') !== false) {
                    $returnTo = 'student/' . $scriptRel . $querySuffix;
                } elseif (strpos($scriptName, '/employer/') !== false) {
                    $returnTo = 'employer/' . $scriptRel . $querySuffix;
                } elseif (strpos($scriptName, '/admin/') !== false) {
                    $returnTo = 'admin/' . $scriptRel . $querySuffix;
                }
            }

            $sanitized = self::sanitizeReturnTo($returnTo);
            $loginUrl = $prefix . 'login.php' . ($sanitized ? '?next=' . urlencode($sanitized) : '');
            self::redirect($loginUrl, 'unauthenticated');
            return [];
        }

        $user = self::user();

        // Verification Quarantine check: non-admin unverified email
        if (isset($user['is_email_verified']) && (int)$user['is_email_verified'] === 0 && ($user['role'] ?? '') !== 'admin') {
            self::quarantineForVerification($user);
            return [];
        }

        // Role authorization check
        if (!empty($allowedRoles)) {
            $userRole = $user['role'] ?? '';
            if (!in_array($userRole, $allowedRoles, true)) {
                set_flash('danger', 'Unauthorized access for your account role.');
                self::redirectByRole($userRole);
                return [];
            }
        }

        return $user;
    }

    /**
     * Quarantine an unverified user account into pending verification state,
     * generate a 6-digit OTP code, send the email, and redirect to verify-email.php.
     */
    public static function quarantineForVerification(array $user, ?string $roleMessage = null): void {
        require_once __DIR__ . '/mailer.php';

        $prefix = self::getDirectoryPrefix();
        $_SESSION['pending_verification'] = [
            'user_id'      => (int)$user['id'],
            'email'        => $user['email'],
            'name'         => $user['name'] ?? 'User',
            'role'         => $user['role'] ?? 'student',
            'role_message' => $roleMessage
        ];
        unset($_SESSION['user']);

        $code = create_email_verification_code((int)$user['id']);

        // On a QA/test server, never dispatch real mail: throwaway accounts are
        // created on every run. Closing the gate here makes the flow behave
        // exactly like an unconfigured SMTP host, so the code is shown on screen
        // and the OTP journey stays fully testable. See registration_mail_allowed().
        $mailRes = registration_mail_allowed()
            ? send_verification_code_email($user['email'], $user['name'] ?? 'User', $code)
            : ['success' => false, 'smtp_configured' => false, 'message' => 'Mail dispatch disabled on this QA server.'];

        if (!$mailRes['smtp_configured']) {
            $_SESSION['pending_verification']['dev_code'] = $code;
            set_flash('warning', 'Notice: Outbound SMTP is not configured in .env. Enter the verification passcode displayed on screen to continue.');
        } else {
            set_flash('info', 'Please verify your institutional email address to continue.');
        }

        self::redirect($prefix . 'verify-email.php', 'quarantined');
    }

    /**
     * Graduate a quarantined user to fully authenticated session upon successful verification.
     */
    public static function graduateVerification(int $userId): ?array {
        $user = get_user_by_id($userId);
        if (!$user) {
            unset($_SESSION['pending_verification']);
            return null;
        }
        unset($user['password']);
        $_SESSION['user'] = $user;
        unset($_SESSION['pending_verification']);
        return $user;
    }

    /**
     * Terminate the active user session and pending verification state.
     */
    public static function logout(): void {
        unset($_SESSION['user'], $_SESSION['pending_verification']);
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }
}

// ============================================================================
// Backwards Compatibility Shims
// ============================================================================

if (!function_exists('get_role_dashboard_url')) {
    function get_role_dashboard_url(?string $role): string {
        return SessionGuard::getRoleDashboardUrl($role);
    }
}

if (!function_exists('redirect_by_role')) {
    function redirect_by_role(?string $role = null): void {
        SessionGuard::redirectByRole($role);
    }
}

if (!function_exists('require_auth')) {
    function require_auth(array $allowed_roles = []): void {
        SessionGuard::protect($allowed_roles);
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool {
        return SessionGuard::hasRole($role);
    }
}
