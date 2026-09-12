<?php
/**
 * Campus Job Posting System - Database Migration Utility
 * Imports and synchronizes data from data/*.json into MySQL / MariaDB tables.
 * Can be run via CLI (`php database/migrate.php`), accessed in browser, or called programmatically.
 */

require_once dirname(__DIR__) . '/includes/db.php';

function execute_migration_and_seed($verbose = false, $source_dir = null, $run_ddl = true, $truncate = false) {
    $is_cli = (php_sapi_name() === 'cli');
    $log_fn = function($message, $type = 'info') use ($verbose, $is_cli) {
        if (!$verbose) return;
        $timestamp = date('H:i:s');
        if ($is_cli) {
            $prefix = match($type) {
                'success' => "\033[32m[SUCCESS]\033[0m",
                'error'   => "\033[31m[ERROR]\033[0m",
                'warning' => "\033[33m[WARN]\033[0m",
                default   => "\033[36m[INFO]\033[0m"
            };
            echo "[$timestamp] $prefix $message\n";
        } else {
            $badge = match($type) {
                'success' => 'badge bg-success',
                'error'   => 'badge bg-danger',
                'warning' => 'badge bg-warning text-dark',
                default   => 'badge bg-info text-dark'
            };
            echo "<div class='mb-2 font-monospace'><span class='text-muted small me-2'>[$timestamp]</span><span class='$badge me-2'>" . strtoupper($type) . "</span> " . htmlspecialchars($message) . "</div>";
        }
    };

    try {
        $log_fn("Connecting to MySQL Database (" . DB_HOST . ":" . DB_PORT . ")...");
        $pdo = get_db_connection();
        $log_fn("Connected to database `" . DB_NAME . "` successfully.", "success");

        // 1. Run schema.sql if requested
        if ($run_ddl) {
            $schema_file = __DIR__ . '/schema.sql';
            if (!file_exists($schema_file)) {
                throw new Exception("schema.sql not found at: " . $schema_file);
            }

            $log_fn("Executing schema.sql DDL statements...");
            $sql = file_get_contents($schema_file);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $log_fn("Tables created/verified successfully.", "success");
        }

        // Truncate tables if requested for a clean datastore reset
        if ($truncate) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $allowed_tables = ['notifications', 'devblogs', 'updates', 'profile_requests', 'applications', 'jobs', 'student_profiles', 'employer_profiles', 'categories', 'users'];
            foreach ($allowed_tables as $t) {
                // Strict whitelist validation for security audit compliance
                if (in_array($t, $allowed_tables, true) && preg_match('/^[a-z0-9_]+$/i', $t)) {
                    $pdo->exec("TRUNCATE TABLE `" . $t . "`;");
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $log_fn("All tables truncated.", "info");
        }

        $data_dir = $source_dir ?: (dirname(__DIR__) . '/data');

        // Begin transaction for high-performance atomic seed batch
        $pdo->beginTransaction();

        // Helper to read JSON safely
        $read_json = function($filename) use ($data_dir) {
            $path = $data_dir . '/' . $filename;
            if (!file_exists($path)) return [];
            $data = json_decode(file_get_contents($path), true);
            return is_array($data) ? $data : [];
        };

        // 2. Migrate Categories
        $log_fn("Migrating categories...");
        $categories = $read_json('categories.json');
        $stmt_cat = $pdo->prepare("
            INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `theme`, `badge_tag`, `badge_icon`, `job_count`, `hourly_range`, `image`, `popular_roles`)
            VALUES (:id, :name, :slug, :icon, :description, :theme, :badge_tag, :badge_icon, :job_count, :hourly_range, :image, :popular_roles)
            ON DUPLICATE KEY UPDATE 
                `name` = VALUES(`name`),
                `slug` = VALUES(`slug`),
                `icon` = VALUES(`icon`),
                `description` = VALUES(`description`),
                `theme` = VALUES(`theme`),
                `badge_tag` = VALUES(`badge_tag`),
                `badge_icon` = VALUES(`badge_icon`),
                `job_count` = VALUES(`job_count`),
                `hourly_range` = VALUES(`hourly_range`),
                `image` = VALUES(`image`),
                `popular_roles` = VALUES(`popular_roles`)
        ");

        $count_cats = 0;
        foreach ($categories as $cat) {
            $stmt_cat->execute([
                ':id'           => $cat['id'],
                ':name'         => $cat['name'] ?? '',
                ':slug'         => $cat['slug'] ?? ('cat-' . $cat['id']),
                ':icon'         => $cat['icon'] ?? 'bi-briefcase',
                ':description'  => $cat['description'] ?? '',
                ':theme'        => $cat['theme'] ?? 'kld-green',
                ':badge_tag'    => $cat['badge_tag'] ?? null,
                ':badge_icon'   => $cat['badge_icon'] ?? null,
                ':job_count'    => (int)($cat['job_count'] ?? 0),
                ':hourly_range' => $cat['hourly_range'] ?? null,
                ':image'        => $cat['image'] ?? null,
                ':popular_roles'=> isset($cat['popular_roles']) ? json_encode($cat['popular_roles']) : null
            ]);
            $count_cats++;
        }
        $log_fn("Imported $count_cats categories.", "success");

        // 3. Migrate Users (Class Table Inheritance: users, student_profiles, employer_profiles)
        $log_fn("Migrating users and subtypes (student_profiles, employer_profiles)...");
        $users = $read_json('users.json');
        $stmt_user = $pdo->prepare("
            INSERT INTO `users` (`id`, `email`, `password`, `role`, `name`, `phone`, `status`, `created_at`)
            VALUES (:id, :email, :password, :role, :name, :phone, :status, :created_at)
            ON DUPLICATE KEY UPDATE
                `email` = VALUES(`email`),
                `password` = VALUES(`password`),
                `role` = VALUES(`role`),
                `name` = VALUES(`name`),
                `phone` = VALUES(`phone`),
                `status` = VALUES(`status`)
        ");

        $stmt_student = $pdo->prepare("
            INSERT INTO `student_profiles` (
                `user_id`, `student_id`, `department`, `course`, `year_level`, 
                `sex`, `birthdate`, `age`, `availability`, `verification_status`, 
                `rejection_reason`, `registration_proof`, `created_at`
            ) VALUES (
                :user_id, :student_id, :department, :course, :year_level,
                :sex, :birthdate, :age, :availability, :verification_status,
                :rejection_reason, :registration_proof, :created_at
            )
            ON DUPLICATE KEY UPDATE
                `student_id` = VALUES(`student_id`),
                `department` = VALUES(`department`),
                `course` = VALUES(`course`),
                `year_level` = VALUES(`year_level`),
                `sex` = VALUES(`sex`),
                `birthdate` = VALUES(`birthdate`),
                `age` = VALUES(`age`),
                `availability` = VALUES(`availability`),
                `verification_status` = VALUES(`verification_status`),
                `rejection_reason` = VALUES(`rejection_reason`),
                `registration_proof` = VALUES(`registration_proof`)
        ");

        $stmt_employer = $pdo->prepare("
            INSERT INTO `employer_profiles` (
                `user_id`, `employer_type`, `organization_name`, `office_location`, 
                `contact_person`, `accreditation_number`, `verification_status`, 
                `rejection_reason`, `business_permit`, `created_at`
            ) VALUES (
                :user_id, :employer_type, :organization_name, :office_location,
                :contact_person, :accreditation_number, :verification_status,
                :rejection_reason, :business_permit, :created_at
            )
            ON DUPLICATE KEY UPDATE
                `employer_type` = VALUES(`employer_type`),
                `organization_name` = VALUES(`organization_name`),
                `office_location` = VALUES(`office_location`),
                `contact_person` = VALUES(`contact_person`),
                `accreditation_number` = VALUES(`accreditation_number`),
                `verification_status` = VALUES(`verification_status`),
                `rejection_reason` = VALUES(`rejection_reason`),
                `business_permit` = VALUES(`business_permit`)
        ");

        $count_users = 0;
        $count_students = 0;
        $count_employers = 0;
        foreach ($users as $u) {
            $created_at = $u['created_at'] ?? date('Y-m-d H:i:s');
            if (strlen($created_at) === 10) $created_at .= ' 00:00:00';

            $stmt_user->execute([
                ':id'         => $u['id'],
                ':email'      => strtolower(trim($u['email'])),
                ':password'   => $u['password'] ?? 'Password123!',
                ':role'       => $u['role'] ?? 'student',
                ':name'       => $u['name'] ?? '',
                ':phone'      => $u['phone'] ?? null,
                ':status'     => $u['status'] ?? 'active',
                ':created_at' => $created_at
            ]);
            $count_users++;

            if (($u['role'] ?? '') === 'student') {
                $bdate = (!empty($u['birthdate']) && $u['birthdate'] !== '0000-00-00') ? $u['birthdate'] : null;
                $student_id = !empty($u['student_id']) ? $u['student_id'] : ('KLD-' . str_pad($u['id'], 6, '0', STR_PAD_LEFT));
                $stmt_student->execute([
                    ':user_id'             => $u['id'],
                    ':student_id'          => $student_id,
                    ':department'          => $u['department'] ?? 'Institute of Computing and Digital Innovation (ICDI)',
                    ':course'              => $u['course'] ?? 'BS Information Systems (BSIS)',
                    ':year_level'          => $u['year_level'] ?? '1st Year',
                    ':sex'                 => $u['sex'] ?? null,
                    ':birthdate'           => $bdate,
                    ':age'                 => isset($u['age']) ? (int)$u['age'] : null,
                    ':availability'        => isset($u['availability']) ? (is_array($u['availability']) ? json_encode($u['availability']) : $u['availability']) : null,
                    ':verification_status' => $u['verification_status'] ?? 'verified',
                    ':rejection_reason'    => $u['rejection_reason'] ?? null,
                    ':registration_proof'  => $u['proof_file'] ?? ($u['registration_proof'] ?? null),
                    ':created_at'          => $created_at
                ]);
                $count_students++;
            } elseif (($u['role'] ?? '') === 'employer') {
                $stmt_employer->execute([
                    ':user_id'              => $u['id'],
                    ':employer_type'        => $u['employer_type'] ?? 'university_office',
                    ':organization_name'    => $u['organization_name'] ?? ($u['name'] ?? 'University Department'),
                    ':office_location'      => $u['office_location'] ?? 'Campus Main Building',
                    ':contact_person'       => $u['contact_person'] ?? ($u['name'] ?? null),
                    ':accreditation_number' => $u['accreditation_number'] ?? null,
                    ':verification_status'  => $u['verification_status'] ?? 'verified',
                    ':rejection_reason'     => $u['rejection_reason'] ?? null,
                    ':business_permit'      => $u['permit_file'] ?? ($u['business_permit'] ?? null),
                    ':created_at'           => $created_at
                ]);
                $count_employers++;
            }
        }
        $log_fn("Imported $count_users users ($count_students student profiles, $count_employers employer profiles).", "success");

        // 4. Migrate Jobs (3NF Requisition Header)
        $log_fn("Migrating jobs...");
        $jobs = $read_json('jobs.json');
        $stmt_job = $pdo->prepare("
            INSERT INTO `jobs` (
                `id`, `title`, `department`, `category_id`, 
                `employer_id`, `job_type`, `work_setup`, 
                `location`, `pay_rate`, `pay_type`, `hours_per_week`, 
                `vacancies`, `slots_total`, `slots_filled`, `deadline`, `status`, `image`, 
                `tags`, `badges`, `description`, `responsibilities`, `qualifications`, `created_at`
            ) VALUES (
                :id, :title, :department, :category_id,
                :employer_id, :job_type, :work_setup,
                :location, :pay_rate, :pay_type, :hours_per_week,
                :vacancies, :slots_total, :slots_filled, :deadline, :status, :image,
                :tags, :badges, :description, :responsibilities, :qualifications, :created_at
            )
            ON DUPLICATE KEY UPDATE
                `title` = VALUES(`title`),
                `department` = VALUES(`department`),
                `category_id` = VALUES(`category_id`),
                `employer_id` = VALUES(`employer_id`),
                `job_type` = VALUES(`job_type`),
                `work_setup` = VALUES(`work_setup`),
                `location` = VALUES(`location`),
                `pay_rate` = VALUES(`pay_rate`),
                `pay_type` = VALUES(`pay_type`),
                `hours_per_week` = VALUES(`hours_per_week`),
                `vacancies` = VALUES(`vacancies`),
                `slots_total` = VALUES(`slots_total`),
                `slots_filled` = VALUES(`slots_filled`),
                `deadline` = VALUES(`deadline`),
                `status` = VALUES(`status`),
                `image` = VALUES(`image`),
                `tags` = VALUES(`tags`),
                `badges` = VALUES(`badges`),
                `description` = VALUES(`description`),
                `responsibilities` = VALUES(`responsibilities`),
                `qualifications` = VALUES(`qualifications`)
        ");

        $count_jobs = 0;
        foreach ($jobs as $j) {
            $deadline = (!empty($j['deadline']) && $j['deadline'] !== '0000-00-00') ? $j['deadline'] : null;
            $created_at = $j['created_at'] ?? date('Y-m-d H:i:s');
            if (strlen($created_at) === 10) $created_at .= ' 00:00:00';

            $stmt_job->execute([
                ':id'                => $j['id'],
                ':title'             => $j['title'] ?? '',
                ':department'        => $j['department'] ?? '',
                ':category_id'       => isset($j['category_id']) ? (int)$j['category_id'] : null,
                ':employer_id'       => isset($j['employer_id']) ? (int)$j['employer_id'] : null,
                ':job_type'          => $j['job_type'] ?? 'Student Assistant',
                ':work_setup'        => $j['work_setup'] ?? 'On-Campus',
                ':location'          => $j['location'] ?? null,
                ':pay_rate'          => $j['pay_rate'] ?? '₱85.00 / hour',
                ':pay_type'          => $j['pay_type'] ?? 'Hourly',
                ':hours_per_week'    => $j['hours_per_week'] ?? '15 - 20 hrs/week',
                ':vacancies'         => (int)($j['vacancies'] ?? 1),
                ':slots_total'       => (int)($j['slots_total'] ?? ($j['vacancies'] ?? 1)),
                ':slots_filled'      => (int)($j['slots_filled'] ?? 0),
                ':deadline'          => $deadline,
                ':status'            => $j['status'] ?? 'active',
                ':image'             => $j['image'] ?? null,
                ':tags'              => isset($j['tags']) ? json_encode($j['tags']) : null,
                ':badges'            => isset($j['badges']) ? json_encode($j['badges']) : null,
                ':description'       => $j['description'] ?? '',
                ':responsibilities'  => isset($j['responsibilities']) ? json_encode($j['responsibilities']) : null,
                ':qualifications'    => isset($j['qualifications']) ? json_encode($j['qualifications']) : null,
                ':created_at'        => $created_at
            ]);
            $count_jobs++;
        }
        $log_fn("Imported $count_jobs jobs.", "success");

        // 5. Migrate Applications (Pure Junction)
        $log_fn("Migrating applications...");
        $applications = $read_json('applications.json');
        $stmt_app = $pdo->prepare("
            INSERT INTO `applications` (
                `id`, `job_id`, `student_id`, 
                `cover_letter`, `availability`, `resume_file`, `study_load_file`, 
                `status`, `interview_date`, `interview_time`, 
                `interview_venue`, `supervisor_notes`, `applied_at`, `updated_at`
            ) VALUES (
                :id, :job_id, :student_id,
                :cover_letter, :availability, :resume_file, :study_load_file,
                :status, :interview_date, :interview_time,
                :interview_venue, :supervisor_notes, :applied_at, :updated_at
            )
            ON DUPLICATE KEY UPDATE
                `job_id` = VALUES(`job_id`),
                `student_id` = VALUES(`student_id`),
                `cover_letter` = VALUES(`cover_letter`),
                `availability` = VALUES(`availability`),
                `resume_file` = VALUES(`resume_file`),
                `study_load_file` = VALUES(`study_load_file`),
                `status` = VALUES(`status`),
                `interview_date` = VALUES(`interview_date`),
                `interview_time` = VALUES(`interview_time`),
                `interview_venue` = VALUES(`interview_venue`),
                `supervisor_notes` = VALUES(`supervisor_notes`),
                `updated_at` = VALUES(`updated_at`)
        ");

        $count_apps = 0;
        foreach ($applications as $app) {
            $idate = (!empty($app['interview_date']) && $app['interview_date'] !== '0000-00-00') ? $app['interview_date'] : null;
            $applied_at = $app['applied_at'] ?? date('Y-m-d H:i:s');
            $updated_at = $app['updated_at'] ?? $applied_at;

            $stmt_app->execute([
                ':id'               => $app['id'],
                ':job_id'           => (int)$app['job_id'],
                ':student_id'       => (int)($app['student_id'] ?? 0),
                ':cover_letter'     => $app['cover_letter'] ?? null,
                ':availability'     => isset($app['availability']) ? (is_array($app['availability']) ? json_encode($app['availability']) : $app['availability']) : null,
                ':resume_file'      => $app['resume_file'] ?? null,
                ':study_load_file'  => $app['study_load_file'] ?? null,
                ':status'           => $app['status'] ?? 'pending',
                ':interview_date'   => $idate,
                ':interview_time'   => $app['interview_time'] ?? null,
                ':interview_venue'  => $app['interview_venue'] ?? null,
                ':supervisor_notes' => $app['supervisor_notes'] ?? null,
                ':applied_at'       => $applied_at,
                ':updated_at'       => $updated_at
            ]);
            $count_apps++;
        }
        $log_fn("Imported $count_apps applications.", "success");

        // 6. Migrate Profile Requests
        $log_fn("Migrating profile requests...");
        $requests = $read_json('profile_requests.json');
        $stmt_req = $pdo->prepare("
            INSERT INTO `profile_requests` (
                `id`, `user_id`, `current_profile`, 
                `requested_profile`, `proof_file`, `reason`, `status`, `admin_notes`, 
                `dismissed_by_user`, `created_at`, `resolved_at`
            ) VALUES (
                :id, :user_id, :current_profile,
                :requested_profile, :proof_file, :reason, :status, :admin_notes,
                :dismissed_by_user, :created_at, :resolved_at
            )
            ON DUPLICATE KEY UPDATE
                `user_id` = VALUES(`user_id`),
                `current_profile` = VALUES(`current_profile`),
                `requested_profile` = VALUES(`requested_profile`),
                `proof_file` = VALUES(`proof_file`),
                `reason` = VALUES(`reason`),
                `status` = VALUES(`status`),
                `admin_notes` = VALUES(`admin_notes`),
                `dismissed_by_user` = VALUES(`dismissed_by_user`),
                `resolved_at` = VALUES(`resolved_at`)
        ");

        $count_reqs = 0;
        foreach ($requests as $r) {
            $stmt_req->execute([
                ':id'                => $r['id'],
                ':user_id'           => (int)$r['user_id'],
                ':current_profile'   => isset($r['current_profile']) ? json_encode($r['current_profile']) : null,
                ':requested_profile' => isset($r['requested_profile']) ? json_encode($r['requested_profile']) : null,
                ':proof_file'        => $r['proof_file'] ?? null,
                ':reason'            => $r['reason'] ?? '',
                ':status'            => $r['status'] ?? 'pending',
                ':admin_notes'       => $r['admin_notes'] ?? '',
                ':dismissed_by_user' => !empty($r['dismissed_by_user']) ? 1 : 0,
                ':created_at'        => $r['created_at'] ?? date('Y-m-d H:i:s'),
                ':resolved_at'       => $r['resolved_at'] ?? null
            ]);
            $count_reqs++;
        }
        $log_fn("Imported $count_reqs profile requests.", "success");

        // 7. Migrate Updates
        $log_fn("Migrating career updates...");
        $updates = $read_json('updates.json');
        $stmt_upd = $pdo->prepare("
            INSERT INTO `updates` (
                `id`, `slug`, `title`, `category`, `published_at`, `read_time`, 
                `author_id`, `author_avatar`, 
                `image`, `summary`, `content`, `created_at`, `updated_at`
            ) VALUES (
                :id, :slug, :title, :category, :published_at, :read_time,
                :author_id, :author_avatar,
                :image, :summary, :content, :created_at, :updated_at
            )
            ON DUPLICATE KEY UPDATE
                `slug` = VALUES(`slug`),
                `title` = VALUES(`title`),
                `category` = VALUES(`category`),
                `published_at` = VALUES(`published_at`),
                `read_time` = VALUES(`read_time`),
                `author_id` = VALUES(`author_id`),
                `author_avatar` = VALUES(`author_avatar`),
                `image` = VALUES(`image`),
                `summary` = VALUES(`summary`),
                `content` = VALUES(`content`),
                `updated_at` = VALUES(`updated_at`)
        ");

        $count_upds = 0;
        foreach ($updates as $upd) {
            $author = $upd['author'] ?? [];
            $published_at = $upd['published_at'] ?? date('Y-m-d H:i:s');
            $created_at = $upd['created_at'] ?? $published_at;
            $updated_at = $upd['updated_at'] ?? $published_at;
            $stmt_upd->execute([
                ':id'            => $upd['id'],
                ':slug'          => $upd['slug'] ?? ('update-' . $upd['id']),
                ':title'         => $upd['title'] ?? '',
                ':category'      => $upd['category'] ?? 'Campus News',
                ':published_at'  => $published_at,
                ':read_time'     => $upd['read_time'] ?? '3 min read',
                ':author_id'     => isset($upd['author_id']) ? (int)$upd['author_id'] : 5,
                ':author_avatar' => $author['avatar'] ?? ($upd['author_avatar'] ?? 'CC'),
                ':image'         => $upd['image'] ?? null,
                ':summary'       => $upd['summary'] ?? '',
                ':content'       => $upd['content'] ?? '',
                ':created_at'    => $created_at,
                ':updated_at'    => $updated_at
            ]);
            $count_upds++;
        }
        $log_fn("Imported $count_upds career updates.", "success");

        // 8. Migrate DevBlogs
        $log_fn("Migrating devblogs...");
        $devblogs = $read_json('devblogs.json');
        $stmt_blog = $pdo->prepare("
            INSERT INTO `devblogs` (`id`, `sprint_number`, `sprint_title`, `sprint_dates`, `sprint_focus`, `daily_logs`)
            VALUES (:id, :sprint_number, :sprint_title, :sprint_dates, :sprint_focus, :daily_logs)
            ON DUPLICATE KEY UPDATE
                `sprint_number` = VALUES(`sprint_number`),
                `sprint_title` = VALUES(`sprint_title`),
                `sprint_dates` = VALUES(`sprint_dates`),
                `sprint_focus` = VALUES(`sprint_focus`),
                `daily_logs` = VALUES(`daily_logs`)
        ");

        $count_blogs = 0;
        foreach ($devblogs as $blog) {
            $stmt_blog->execute([
                ':id'            => (string)($blog['id'] ?? $blog['sprint_number']),
                ':sprint_number' => (string)($blog['sprint_number'] ?? '1'),
                ':sprint_title'  => $blog['sprint_title'] ?? ($blog['title'] ?? ''),
                ':sprint_dates'  => $blog['sprint_dates'] ?? ($blog['date'] ?? null),
                ':sprint_focus'  => $blog['sprint_focus'] ?? ($blog['summary_excerpt'] ?? null),
                ':daily_logs'    => isset($blog['daily_logs']) ? (is_string($blog['daily_logs']) ? $blog['daily_logs'] : json_encode($blog['daily_logs'])) : json_encode($blog)
            ]);
            $count_blogs++;
        }
        $log_fn("Imported $count_blogs devblog sprints.", "success");
        $pdo->commit();
        $log_fn("ALL DATA MIGRATED TO MYSQL SUCCESSFULLY!", "success");

        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $log_fn("Migration failed: " . $e->getMessage(), "error");
        return false;
    }
}

// If accessed directly (not included by another file)
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $is_cli = (php_sapi_name() === 'cli');
    if (!$is_cli) {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Database Migration - KLD Campus Hire</title>
            <link href="../assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #fdfbf7; color: #1f2937; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
                .card-paper { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 30px; }
            </style>
        </head>
        <body>
        <div class="container" style="max-width: 850px;">
            <div class="card-paper">
                <h2 class="h4 fw-bold mb-3 text-success"><i class="bi bi-database-fill-gear me-2"></i>MySQL Database Migration Engine</h2>
                <p class="text-muted small mb-4">Migrating flat-file JSON datastores to relational MySQL/MariaDB database: <code>' . DB_NAME . '</code></p>
                <div class="bg-dark text-light p-3 rounded mb-4" style="max-height: 450px; overflow-y: auto;">';
    }

    $truncate = in_array('--truncate', $argv ?? []) || in_array('--clean', $argv ?? []) || in_array('-c', $argv ?? []);
    execute_migration_and_seed(true, null, true, $truncate);

    if (!$is_cli) {
        echo '      </div>
                <div class="d-flex justify-content-between">
                    <a href="../index.php" class="btn btn-primary"><i class="bi bi-house me-1"></i>Return to Homepage</a>
                    <a href="../login.php" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-in-right me-1"></i>Go to Login</a>
                </div>
            </div>
        </div>
        </body>
        </html>';
    }
}
