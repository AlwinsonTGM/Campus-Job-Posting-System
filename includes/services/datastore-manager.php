<?php
declare(strict_types=1);

/**
 * DatastoreManager - Encapsulated Fixture Lifecycle & Schema Management
 *
 * Deep module providing single-point control over:
 * 1. System data mode querying ('demo' vs 'real')
 * 2. Fixture resets and dataset synchronization
 * 3. Session state lifecycle transitions during datastore wipes
 * 4. Lazy runtime schema guarantees without hot-path DDL overhead
 */
class DatastoreManager {
    public const SEED_FILES = [
        'users.json',
        'jobs.json',
        'applications.json',
        'categories.json',
        'profile_requests.json',
        'updates.json',
        'devblogs.json',
        'notifications.json'
    ];

    private static bool $schemaEnsured = false;

    /**
     * Query the currently active dataset mode.
     * Permanently operating on unified relational MySQL datastore.
     */
    public static function getMode(): string {
        return 'real';
    }

    /**
     * Whether the dataset switcher may be operated at all in this environment.
     * Permanently disabled: MySQL is the sole authoritative datastore.
     */
    public static function isToggleAvailable(): bool {
        return false;
    }

    /**
     * Ensure database tables and dynamic columns exist without executing DDL on every query.
     * Guarded by a static in-process memo flag so it only runs once per request when invoked.
     */
    public static function ensureSchema(bool $force = false): void {
        if (self::$schemaEnsured && !$force) {
            return;
        }

        $dataDir = defined('DATA_DIR') ? DATA_DIR : (dirname(__DIR__, 2) . '/data');
        $lockFile = $dataDir . '/.schema_verified.lock';
        if (!$force && file_exists($lockFile) && (time() - (int)@filemtime($lockFile)) < 3600) {
            self::$schemaEnsured = true;
            return;
        }

        try {
            if (!function_exists('get_db_connection')) {
                return;
            }
            $pdo = get_db_connection();
            if (!$pdo) {
                return;
            }

            // Quick check if users table exists before checking columns
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            if (!$tableCheck) {
                // Database not initialized; run schema.sql
                $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
                    foreach ($statements as $stmt) {
                        if (!empty($stmt)) {
                            $pdo->exec($stmt);
                        }
                    }
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
                }
            } else {
                // Ensure email verification column and table if missing
                $colCheck = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_email_verified'")->fetch();
                if (!$colCheck) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_email_verified` TINYINT(1) NOT NULL DEFAULT 1");
                    $pdo->exec("UPDATE `users` SET `is_email_verified` = 1 WHERE `is_email_verified` IS NULL");
                }

                // Ensure email_verifications table exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS `email_verifications` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `code_hash` VARCHAR(255) NOT NULL,
                    `expires_at` DATETIME NOT NULL,
                    `attempts` INT NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_verify_user` (`user_id`),
                    INDEX `idx_verify_expires` (`expires_at`),
                    CONSTRAINT `fk_verify_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                // Ensure password_resets table exists
                $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `token_hash` VARCHAR(255) NOT NULL UNIQUE,
                    `expires_at` DATETIME NOT NULL,
                    `used_at` DATETIME NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_resets_user` (`user_id`),
                    INDEX `idx_resets_expires` (`expires_at`),
                    CONSTRAINT `fk_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                // Ensure jobs archive columns
                $jobArchCheck = $pdo->query("SHOW COLUMNS FROM `jobs` LIKE 'is_archived'")->fetch();
                if (!$jobArchCheck) {
                    $pdo->exec("ALTER TABLE `jobs` ADD COLUMN `is_archived` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `archived_at` DATETIME NULL, ADD INDEX `idx_jobs_archived` (`is_archived`)");
                }

                // Ensure categories archive columns
                $catArchCheck = $pdo->query("SHOW COLUMNS FROM `categories` LIKE 'is_archived'")->fetch();
                if (!$catArchCheck) {
                    $pdo->exec("ALTER TABLE `categories` ADD COLUMN `is_archived` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `archived_at` DATETIME NULL, ADD INDEX `idx_categories_archived` (`is_archived`)");
                }

                // Ensure updates archive columns
                $updArchCheck = $pdo->query("SHOW COLUMNS FROM `updates` LIKE 'is_archived'")->fetch();
                if (!$updArchCheck) {
                    $pdo->exec("ALTER TABLE `updates` ADD COLUMN `is_archived` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `archived_at` DATETIME NULL, ADD INDEX `idx_updates_archived` (`is_archived`)");
                }

                // Ensure applications status includes 'withdrawn'
                $appStatusCheck = $pdo->query("SHOW COLUMNS FROM `applications` LIKE 'status'")->fetch();
                if ($appStatusCheck && strpos($appStatusCheck['Type'] ?? '', 'withdrawn') === false) {
                    $pdo->exec("ALTER TABLE `applications` MODIFY COLUMN `status` ENUM('pending', 'under_review', 'interview_scheduled', 'accepted', 'declined', 'withdrawn') NOT NULL DEFAULT 'pending'");
                }

                // Ensure student_profiles has resume_file column
                $resumeColCheck = $pdo->query("SHOW COLUMNS FROM `student_profiles` LIKE 'resume_file'")->fetch();
                if (!$resumeColCheck) {
                    $pdo->exec("ALTER TABLE `student_profiles` ADD COLUMN `resume_file` VARCHAR(255) NULL AFTER `registration_proof`");
                }

                // Ensure users has is_archived and archived_at
                $userArchCheck = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_archived'")->fetch();
                if (!$userArchCheck) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_archived` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`, ADD COLUMN `archived_at` DATETIME NULL AFTER `is_archived`, ADD INDEX `idx_users_archived` (`is_archived`)");
                    $pdo->exec("UPDATE `users` SET `is_archived` = 1, `archived_at` = `updated_at` WHERE `status` = 'suspended'");
                }

                // Ensure users has is_revoked, revoked_at, revocation_reason, and verification_status
                $userRevCheck = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_revoked'")->fetch();
                if (!$userRevCheck) {
                    $pdo->exec("ALTER TABLE `users` 
                        ADD COLUMN `is_revoked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `archived_at`, 
                        ADD COLUMN `revoked_at` DATETIME NULL AFTER `is_revoked`, 
                        ADD COLUMN `revocation_reason` TEXT NULL AFTER `revoked_at`, 
                        ADD COLUMN `verification_status` ENUM('verified', 'pending_approval', 'rejected', 'revoked') NOT NULL DEFAULT 'verified' AFTER `revocation_reason`,
                        ADD INDEX `idx_users_revoked` (`is_revoked`),
                        ADD INDEX `idx_users_verification` (`verification_status`)
                    ");
                }

                // Ensure student_profiles.verification_status ENUM includes 'revoked'
                $spVerCheck = $pdo->query("SHOW COLUMNS FROM `student_profiles` LIKE 'verification_status'")->fetch();
                if ($spVerCheck && strpos($spVerCheck['Type'] ?? '', 'revoked') === false) {
                    $pdo->exec("ALTER TABLE `student_profiles` MODIFY COLUMN `verification_status` ENUM('verified', 'pending_approval', 'rejected', 'revoked') NOT NULL DEFAULT 'pending_approval'");
                }

                // Ensure employer_profiles.verification_status ENUM includes 'revoked'
                $epVerCheck = $pdo->query("SHOW COLUMNS FROM `employer_profiles` LIKE 'verification_status'")->fetch();
                if ($epVerCheck && strpos($epVerCheck['Type'] ?? '', 'revoked') === false) {
                    $pdo->exec("ALTER TABLE `employer_profiles` MODIFY COLUMN `verification_status` ENUM('verified', 'pending_approval', 'rejected', 'revoked') NOT NULL DEFAULT 'pending_approval'");
                }
                // Ensure 1,000-user scalability composite indexes
                try {
                    $pdo->exec("ALTER TABLE `applications` ADD INDEX `idx_app_job_status` (`job_id`, `status`)");
                } catch (Throwable $e) {}
                try {
                    $pdo->exec("ALTER TABLE `applications` ADD INDEX `idx_app_student_status` (`student_id`, `status`)");
                } catch (Throwable $e) {}
                try {
                    $pdo->exec("ALTER TABLE `notifications` ADD INDEX `idx_notif_user_read` (`user_id`, `is_read`, `created_at`)");
                } catch (Throwable $e) {}
                try {
                    $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_role_ver_status` (`role`, `verification_status`, `status`)");
                } catch (Throwable $e) {}

                if (!is_dir($dataDir)) {
                    @mkdir($dataDir, 0777, true);
                }
                @touch($lockFile);
            }

            self::$schemaEnsured = true;
        } catch (Throwable $e) {
            error_log("DatastoreManager::ensureSchema notice: " . $e->getMessage());
        }
    }

    /**
     * Switch dataset mode ('demo' or 'real') and sync fixture files to storage.
     */
    public static function switchMode(string $mode, string $switchedBy = 'User', bool $purgeSession = false): bool {
        // Mode switching is permanently disabled. The system runs exclusively on MySQL.
        error_log("DatastoreManager::switchMode is disabled. Datastore is permanently locked to MySQL.");
        return true;
    }

    /**
     * Reset the active datastore to demo baseline fixtures.
     * Permanently disarmed to prevent overwriting MySQL state with static fixture files.
     */
    public static function resetToDemo(string $switchedBy = 'System Reset', bool $purgeSession = false): bool {
        error_log("DatastoreManager::resetToDemo is disabled to prevent accidental datastore wipes.");
        return false;
    }

    /**
     * Wipe the active datastore to real / clean-slate state.
     * Permanently disarmed to protect live MySQL records.
     */
    public static function wipeReal(string $switchedBy = 'System Wipe'): bool {
        error_log("DatastoreManager::wipeReal is disabled to protect database records.");
        return false;
    }

    /**
     * Reset current active mode to baseline.
     */
    public static function resetCurrent(string $switchedBy = 'User'): bool {
        return false;
    }

    /**
     * Fully tear down and regenerate the active session cookie and state.
     */
    public static function purgeSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies") && !headers_sent()) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            if (!headers_sent() && session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
        }
    }

    /**
     * Reconcile current session identity after datastore re-seeding.
     */
    private static function reconcileSessionUser(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $wasAdmin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');
        if ($wasAdmin && function_exists('get_user_by_email')) {
            $freshAdmin = get_user_by_email('admin@kld.edu.ph');
            if ($freshAdmin) {
                unset($freshAdmin['password']);
                $_SESSION['user'] = $freshAdmin;
                return;
            }
        }
        unset($_SESSION['user']);
    }
}
