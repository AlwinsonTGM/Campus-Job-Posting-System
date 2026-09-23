<?php
declare(strict_types=1);

/**
 * Job & application domain service: listings, applications, categories.
 * Extracted from includes/data-helper.php — 100% function contract preserved.
 */

function get_jobs(string|array|null $category = null, ?string $keyword = null, ?string $department = null, ?string $pay_type = null, ?string $job_type = null, ?string $employer_type = null, ?string $work_setup = null, int|string|null $employer_id = null): array {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                j.*,
                c.`name` AS `category`,
                COALESCE(ep.`organization_name`, j.`department`) AS `organization_name`,
                u.`name` AS `employer_name`,
                COALESCE(ep.`employer_type`, 'university_office') AS `employer_type`,
                CASE WHEN ep.`verification_status` = 'verified' OR u.`role` = 'admin' THEN 1 ELSE 0 END AS `verified_employer`
            FROM `jobs` j
            LEFT JOIN `categories` c ON j.`category_id` = c.`id`
            LEFT JOIN `users` u ON j.`employer_id` = u.`id`
            LEFT JOIN `employer_profiles` ep ON j.`employer_id` = ep.`user_id`
            WHERE 1=1
        ";
        $params = [];

        if ($employer_id !== null) {
            $sql .= " AND j.`employer_id` = :emp_owner_id";
            $params[':emp_owner_id'] = (int)$employer_id;
        }

        if ($category) {
            if (is_array($category)) {
                $cat_clauses = [];
                $i = 0;
                foreach ($category as $cat_val) {
                    if (empty($cat_val) || !is_scalar($cat_val)) continue;
                    $p_name = ":cat_" . ($i++);
                    $cat_clauses[] = "(c.`name` LIKE $p_name OR j.`category_id` = $p_name)";
                    $params[$p_name] = '%' . trim((string)$cat_val) . '%';
                }
                if (!empty($cat_clauses)) {
                    $sql .= " AND (" . implode(' OR ', $cat_clauses) . ")";
                }
            } else {
                $sql .= " AND (c.`name` LIKE :cat OR j.`category_id` = :cat_exact)";
                $params[':cat'] = '%' . trim($category) . '%';
                $params[':cat_exact'] = trim($category);
            }
        }

        if ($job_type) {
            if (is_array($job_type)) {
                $jt_clauses = [];
                $j = 0;
                foreach ($job_type as $jt_val) {
                    if (empty($jt_val) || !is_scalar($jt_val)) continue;
                    $p_name = ":jt_" . ($j++);
                    $jt_clauses[] = "j.`job_type` LIKE $p_name";
                    $params[$p_name] = '%' . trim((string)$jt_val) . '%';
                }
                if (!empty($jt_clauses)) {
                    $sql .= " AND (" . implode(' OR ', $jt_clauses) . ")";
                }
            } else {
                $sql .= " AND j.`job_type` LIKE :jt";
                $params[':jt'] = '%' . trim($job_type) . '%';
            }
        }

        if ($employer_type) {
            $sql .= " AND ep.`employer_type` = :employer_type";
            $params[':employer_type'] = $employer_type;
        }

        if ($work_setup) {
            $sql .= " AND j.`work_setup` = :work_setup";
            $params[':work_setup'] = $work_setup;
        }

        if ($department) {
            $dept_val = '%' . trim($department) . '%';
            $sql .= " AND (j.`department` LIKE :dept1 OR ep.`organization_name` LIKE :dept2)";
            $params[':dept1'] = $dept_val;
            $params[':dept2'] = $dept_val;
        }

        if ($pay_type) {
            $pt_val = '%' . trim($pay_type) . '%';
            $sql .= " AND (j.`pay_type` LIKE :pt1 OR j.`pay_rate` LIKE :pt2)";
            $params[':pt1'] = $pt_val;
            $params[':pt2'] = $pt_val;
        }

        if ($keyword) {
            $kw = '%' . trim($keyword) . '%';
            $sql .= " AND (j.`title` LIKE :kw1 OR j.`department` LIKE :kw2 OR ep.`organization_name` LIKE :kw3 OR j.`description` LIKE :kw4 OR j.`location` LIKE :kw5 OR j.`tags` LIKE :kw6 OR c.`name` LIKE :kw7)";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
            $params[':kw5'] = $kw;
            $params[':kw6'] = $kw;
            $params[':kw7'] = $kw;
        }

        $sql .= " ORDER BY j.`id` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map('hydrate_job', $rows);
    } catch (Exception $e) {
        error_log("get_jobs error: " . $e->getMessage());
        return [];
    }
}

function get_job_by_id(int|string|null $id): ?array {
    if ($id === null || $id === '' || (int)$id <= 0) { return null; }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                c.`name` AS `category`,
                COALESCE(ep.`organization_name`, j.`department`) AS `organization_name`,
                u.`name` AS `employer_name`,
                COALESCE(ep.`employer_type`, 'university_office') AS `employer_type`,
                CASE WHEN ep.`verification_status` = 'verified' OR u.`role` = 'admin' THEN 1 ELSE 0 END AS `verified_employer`
            FROM `jobs` j
            LEFT JOIN `categories` c ON j.`category_id` = c.`id`
            LEFT JOIN `users` u ON j.`employer_id` = u.`id`
            LEFT JOIN `employer_profiles` ep ON j.`employer_id` = ep.`user_id`
            WHERE j.`id` = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_job($row) : null;
    } catch (Exception $e) {
        error_log("get_job_by_id error: " . $e->getMessage());
        return null;
    }
}

function prepare_job_attributes(PDO $pdo, array $data, ?array $photo_file = null, ?array $existing = null): array {
    $image_path = $existing['image'] ?? null;
    if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $new_img = save_uploaded_job_photo($photo_file);
        if ($new_img) {
            $image_path = $new_img;
        }
    } elseif (!empty($data['remove_photo'])) {
        $image_path = null;
    } elseif (isset($data['image'])) {
        $image_path = $data['image'];
    }

    $responsibilities = isset($data['responsibilities'])
        ? (is_array($data['responsibilities']) ? $data['responsibilities'] : array_filter(array_map('trim', explode("\n", (string)$data['responsibilities']))))
        : ($existing['responsibilities'] ?? []);

    $qualifications = isset($data['qualifications'])
        ? (is_array($data['qualifications']) ? $data['qualifications'] : array_filter(array_map('trim', explode("\n", (string)$data['qualifications']))))
        : ($existing['qualifications'] ?? []);

    $vacancies_count = max(1, (int)($data['vacancies'] ?? ($existing['vacancies'] ?? 1)));

    $category_name = $data['category'] ?? ($existing['category'] ?? 'Administrative & Clerical');
    $category_id = (int)($data['category_id'] ?? 0);
    if ($category_id <= 0) {
        $stmt_cat = $pdo->prepare("SELECT `id` FROM `categories` WHERE `name` = :cname LIMIT 1");
        $stmt_cat->execute([':cname' => $category_name]);
        $found_id = $stmt_cat->fetchColumn();
        $category_id = $found_id ? (int)$found_id : ($existing['category_id'] ?? 3);
    }

    $job_type = $data['job_type'] ?? ($existing['job_type'] ?? 'Student Assistant');
    $work_setup = $data['work_setup'] ?? ($existing['work_setup'] ?? 'On-Campus');
    $employer_type = $data['employer_type'] ?? ($existing['employer_type'] ?? 'university_office');

    $tags = !empty($data['tags'])
        ? (is_array($data['tags']) ? $data['tags'] : explode(',', (string)$data['tags']))
        : [$job_type, $work_setup, $employer_type === 'university_office' ? 'University Office' : 'Approved Partner'];

    $badges = [$job_type, $work_setup];

    return [
        'image_path'       => $image_path,
        'responsibilities' => $responsibilities,
        'qualifications'   => $qualifications,
        'vacancies_count'  => $vacancies_count,
        'category_id'      => $category_id,
        'job_type'         => $job_type,
        'work_setup'       => $work_setup,
        'tags'             => $tags,
        'badges'           => $badges
    ];
}

function create_job(array $data, ?array $photo_file = null): int {
    try {
        $pdo = get_db_connection();
        $user = get_logged_user();
        $org_name = $user['organization_name'] ?? ($user['department'] ?? ($data['department'] ?? 'Campus Department'));
        $data['employer_type'] = $user['employer_type'] ?? ($data['employer_type'] ?? 'university_office');

        $attrs = prepare_job_attributes($pdo, $data, $photo_file);

        $stmt = $pdo->prepare("
            INSERT INTO `jobs` (
                `title`, `department`, `category_id`,
                `employer_id`, `job_type`, `work_setup`,
                `location`, `pay_rate`, `pay_type`, `hours_per_week`,
                `vacancies`, `slots_total`, `slots_filled`, `deadline`, `status`, `image`,
                `tags`, `badges`, `description`, `responsibilities`, `qualifications`, `created_at`
            ) VALUES (
                :title, :department, :category_id,
                :employer_id, :job_type, :work_setup,
                :location, :pay_rate, :pay_type, :hours_per_week,
                :vacancies, :slots_total, 0, :deadline, 'active', :image,
                :tags, :badges, :description, :responsibilities, :qualifications, NOW()
            )
        ");

        $stmt->execute([
            ':title'             => $data['title'] ?? '',
            ':department'        => $data['department'] ?? $org_name,
            ':category_id'       => $attrs['category_id'],
            ':employer_id'       => (int)($user['id'] ?? 0),
            ':job_type'          => $attrs['job_type'],
            ':work_setup'        => $attrs['work_setup'],
            ':location'          => $data['location'] ?? 'Campus Main Office',
            ':pay_rate'          => $data['pay_rate'] ?? '₱80.00 / hour',
            ':pay_type'          => $data['pay_type'] ?? 'Hourly',
            ':hours_per_week'    => $data['hours_per_week'] ?? '10 - 20 hrs/week',
            ':vacancies'         => $attrs['vacancies_count'],
            ':slots_total'       => $attrs['vacancies_count'],
            ':deadline'          => !empty($data['deadline']) ? $data['deadline'] : date('Y-m-d', strtotime('+30 days')),
            ':image'             => $attrs['image_path'],
            ':tags'              => json_encode($attrs['tags']),
            ':badges'            => json_encode($attrs['badges']),
            ':description'       => $data['description'] ?? '',
            ':responsibilities'  => json_encode($attrs['responsibilities']),
            ':qualifications'    => json_encode($attrs['qualifications'])
        ]);

        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_job error: " . $e->getMessage());
        return 0;
    }
}

function update_job(int|string $id, array $data, ?array $photo_file = null): bool {
    try {
        $pdo = get_db_connection();
        $existing = get_job_by_id($id);
        if (!$existing) return false;

        $attrs = prepare_job_attributes($pdo, $data, $photo_file, $existing);

        $stmt = $pdo->prepare("
            UPDATE `jobs` SET
                `title` = :title,
                `department` = :department,
                `category_id` = :category_id,
                `job_type` = :job_type,
                `work_setup` = :work_setup,
                `location` = :location,
                `pay_rate` = :pay_rate,
                `hours_per_week` = :hours_per_week,
                `vacancies` = :vacancies,
                `slots_total` = :slots_total,
                `deadline` = :deadline,
                `status` = :status,
                `description` = :description,
                `image` = :image,
                `responsibilities` = :responsibilities,
                `qualifications` = :qualifications,
                `updated_at` = NOW()
            WHERE `id` = :id
        ");

        $stmt->execute([
            ':title'            => $data['title'] ?? $existing['title'],
            ':department'       => $data['department'] ?? $existing['department'],
            ':category_id'      => $attrs['category_id'],
            ':job_type'         => $attrs['job_type'],
            ':work_setup'       => $attrs['work_setup'],
            ':location'         => $data['location'] ?? $existing['location'],
            ':pay_rate'         => $data['pay_rate'] ?? $existing['pay_rate'],
            ':hours_per_week'   => $data['hours_per_week'] ?? $existing['hours_per_week'],
            ':vacancies'        => $attrs['vacancies_count'],
            ':slots_total'      => $attrs['vacancies_count'],
            ':deadline'         => $data['deadline'] ?? $existing['deadline'],
            ':status'           => $data['status'] ?? $existing['status'],
            ':description'      => $data['description'] ?? $existing['description'],
            ':image'            => $attrs['image_path'],
            ':responsibilities' => json_encode($attrs['responsibilities']),
            ':qualifications'   => json_encode($attrs['qualifications']),
            ':id'               => (int)$id
        ]);

        return true;
    } catch (Exception $e) {
        error_log("update_job error: " . $e->getMessage());
        return false;
    }
}

function delete_job(int|string $id): bool {
    try {
        $pdo = get_db_connection();
        $pdo->beginTransaction();

        // 1. Delete dependent applications for this job to prevent orphaned candidate applications
        $stmt_app = $pdo->prepare("DELETE FROM `applications` WHERE `job_id` = :job_id");
        $stmt_app->execute([':job_id' => (int)$id]);

        // 2. Delete the job record
        $stmt = $pdo->prepare("DELETE FROM `jobs` WHERE `id` = :id");
        $stmt->execute([':id' => (int)$id]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("delete_job error: " . $e->getMessage());
        return false;
    }
}

function get_applications(int|string|null $student_id = null, int|string|null $job_id = null, ?string $department = null, int|string|null $employer_id = null): array {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                a.*,
                j.`title` AS `job_title`,
                j.`department` AS `department`,
                j.`employer_id`,
                j.`pay_rate`,
                j.`pay_type`,
                j.`job_type`,
                j.`work_setup`,
                j.`status` AS `job_status`,
                u.`name` AS `student_name`,
                u.`email` AS `student_email`,
                u.`phone`,
                sp.`student_id` AS `student_number`,
                sp.`department` AS `student_department`,
                sp.`course`,
                sp.`year_level`,
                sp.`sex`,
                sp.`birthdate`,
                sp.`age`
            FROM `applications` a
            INNER JOIN `jobs` j ON a.`job_id` = j.`id`
            INNER JOIN `users` u ON a.`student_id` = u.`id`
            LEFT JOIN `student_profiles` sp ON a.`student_id` = sp.`user_id`
            WHERE 1=1
        ";
        $params = [];

        if ($student_id !== null && $student_id !== '') {
            $sql .= " AND a.`student_id` = :student_id";
            $params[':student_id'] = (int)$student_id;
        }
        if ($job_id) {
            $sql .= " AND a.`job_id` = :job_id";
            $params[':job_id'] = (int)$job_id;
        }
        if ($department) {
            $sql .= " AND j.`department` LIKE :dept";
            $params[':dept'] = '%' . trim($department) . '%';
        }
        if ($employer_id !== null) {
            $sql .= " AND j.`employer_id` = :emp_id";
            $params[':emp_id'] = (int)$employer_id;
        }

        $sql .= " ORDER BY a.`applied_at` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map('hydrate_application', $rows);
    } catch (Exception $e) {
        error_log("get_applications error: " . $e->getMessage());
        return [];
    }
}

function get_application_by_id(int|string|null $id): ?array {
    if ($id === null || $id === '' || (int)$id <= 0) { return null; }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                a.*,
                j.`title` AS `job_title`,
                j.`department` AS `department`,
                j.`employer_id`,
                j.`pay_rate`,
                j.`pay_type`,
                j.`job_type`,
                j.`work_setup`,
                j.`status` AS `job_status`,
                u.`name` AS `student_name`,
                u.`email` AS `student_email`,
                u.`phone`,
                sp.`student_id` AS `student_number`,
                sp.`department` AS `student_department`,
                sp.`course`,
                sp.`year_level`,
                sp.`sex`,
                sp.`birthdate`,
                sp.`age`
            FROM `applications` a
            INNER JOIN `jobs` j ON a.`job_id` = j.`id`
            INNER JOIN `users` u ON a.`student_id` = u.`id`
            LEFT JOIN `student_profiles` sp ON a.`student_id` = sp.`user_id`
            WHERE a.`id` = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_application($row) : null;
    } catch (Exception $e) {
        error_log("get_application_by_id error: " . $e->getMessage());
        return null;
    }
}

function create_application(array $data): array {
    try {
        $pdo = get_db_connection();
        $user = get_logged_user();
        $job = get_job_by_id($data['job_id'] ?? 0);

        if (!$job) {
            return ['success' => false, 'message' => 'Job posting not found.'];
        }

        // 1. Enforce Job Status Gating (closed, paused, etc.)
        if (strtolower($job['status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'This job vacancy has been closed or paused and is no longer accepting applications.'];
        }

        // 2. Enforce Application Deadline Gating
        if (!empty($job['deadline'])) {
            $today = strtotime(date('Y-m-d'));
            $deadline = strtotime($job['deadline']);
            if ($deadline && $deadline < $today) {
                return ['success' => false, 'message' => 'The application deadline for this position has passed.'];
            }
        }

        // 3. Enforce Vacancy Slot Gating
        $slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
        $slots_filled = (int)($job['slots_filled'] ?? 0);
        if ($slots_total > 0 && $slots_filled >= $slots_total) {
            return ['success' => false, 'message' => 'All available vacancy slots for this position have already been filled.'];
        }

        $student_id = (int)($user['id'] ?? 0);
        if ($student_id <= 0) {
            return ['success' => false, 'message' => 'Invalid student authentication session.'];
        }

        // 4. Duplicate Check
        $check_stmt = $pdo->prepare("SELECT `id` FROM `applications` WHERE `job_id` = :job_id AND `student_id` = :student_id LIMIT 1");
        $check_stmt->execute([
            ':job_id'     => $job['id'],
            ':student_id' => $student_id
        ]);
        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'You have already submitted an application for this position.'];
        }

        $availability = !empty($data['availability']) ? (is_array($data['availability']) ? $data['availability'] : explode(',', $data['availability'])) : ['Flexible Weekdays'];
        $resume_file = !empty($data['resume_file']) ? htmlspecialchars($data['resume_file']) : ('Student_Resume_' . $student_id . '.pdf');
        $study_load = 'Study_Load_' . $student_id . '.pdf';

        $stmt = $pdo->prepare("
            INSERT INTO `applications` (
                `job_id`, `student_id`, `cover_letter`, `availability`,
                `resume_file`, `study_load_file`, `status`,
                `supervisor_notes`, `applied_at`, `updated_at`
            ) VALUES (
                :job_id, :student_id, :cover_letter, :availability,
                :resume_file, :study_load_file, 'pending',
                'Application submitted and queued for evaluation.', NOW(), NOW()
            )
        ");

        $stmt->execute([
            ':job_id'          => $job['id'],
            ':student_id'      => $student_id,
            ':cover_letter'    => $data['cover_letter'] ?? '',
            ':availability'    => json_encode($availability),
            ':resume_file'     => $resume_file,
            ':study_load_file' => $study_load
        ]);

        $new_id = (int)$pdo->lastInsertId();

        // Dispatch notification to hiring employer
        if ($new_id > 0 && !empty($job['employer_id'])) {
            $student_disp = $user['name'] ?? 'A student';
            create_notification(
                (int)$job['employer_id'],
                'new_application',
                'New Candidate Application',
                "{$student_disp} submitted an application for '{$job['title']}'.",
                "employer/review-app.php?id={$new_id}",
                'bi-file-earmark-person',
                'info'
            );
        }

        return ['success' => true, 'id' => $new_id];
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000' || strpos($e->getMessage(), 'unique_student_job') !== false) {
            return ['success' => false, 'message' => 'You have already submitted an application for this position.'];
        }
        return ['success' => false, 'message' => 'Application failed: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Application failed: ' . $e->getMessage()];
    }
}

function sync_job_slot_capacity(PDO $pdo, int $job_id, string $old_status, string $new_status): bool {
    if ($old_status !== 'accepted' && $new_status === 'accepted') {
        $stmt_check = $pdo->prepare("SELECT `slots_filled`, `slots_total`, `vacancies` FROM `jobs` WHERE `id` = :job_id FOR UPDATE");
        $stmt_check->execute([':job_id' => $job_id]);
        $job_quota = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$job_quota) {
            return false;
        }

        $slots_max = max(1, (int)($job_quota['slots_total'] ?? 0), (int)($job_quota['vacancies'] ?? 0));
        $slots_curr = (int)($job_quota['slots_filled'] ?? 0);
        if ($slots_curr >= $slots_max) {
            return false;
        }

        $stmt_inc = $pdo->prepare("
            UPDATE `jobs` SET
                `slots_filled` = `slots_filled` + 1,
                `status` = CASE WHEN `slots_filled` >= `slots_total` THEN 'closed' ELSE `status` END,
                `updated_at` = NOW()
            WHERE `id` = :job_id
        ");
        $stmt_inc->execute([':job_id' => $job_id]);
        return true;
    }

    if ($old_status === 'accepted' && $new_status !== 'accepted') {
        $stmt_dec = $pdo->prepare("
            UPDATE `jobs` SET
                `slots_filled` = GREATEST(0, `slots_filled` - 1),
                `status` = CASE WHEN `status` = 'closed' AND (`slots_filled` < `slots_total`) THEN 'active' ELSE `status` END,
                `updated_at` = NOW()
            WHERE `id` = :job_id
        ");
        $stmt_dec->execute([':job_id' => $job_id]);
        return true;
    }

    return true;
}

function dispatch_application_status_notification(
    int $student_id,
    string $job_title,
    string $status,
    string $status_label,
    string $notes,
    ?string $interview_date,
    ?string $interview_time,
    ?string $interview_venue
): void {
    if ($student_id <= 0) {
        return;
    }

    $notif_title = "Application Update: " . $job_title;
    $notif_icon = 'bi-bell';
    $notif_badge = 'primary';
    $notif_msg = "Your application for '{$job_title}' has been updated to {$status_label}.";

    if ($status === 'interview_scheduled') {
        $notif_title = "Interview Scheduled: " . $job_title;
        $notif_icon = 'bi-calendar-check-fill';
        $notif_badge = 'info';
        $date_str = $interview_date ?: 'TBA';
        $time_str = $interview_time ? ' at ' . $interview_time : '';
        $venue_str = $interview_venue ? ' (' . $interview_venue . ')' : '';
        $notif_msg = "You have an interview scheduled on {$date_str}{$time_str}{$venue_str}.";
    } elseif ($status === 'accepted') {
        $notif_title = "Application Accepted! 🎉";
        $notif_icon = 'bi-check-circle-fill';
        $notif_badge = 'success';
        $notif_msg = "Congratulations! You have been accepted for the position '{$job_title}'.";
    } elseif ($status === 'declined') {
        $notif_title = "Application Update: " . $job_title;
        $notif_icon = 'bi-x-circle';
        $notif_badge = 'secondary';
        $notif_msg = "The hiring supervisor has completed review for '{$job_title}'. The position has been filled or closed.";
    } elseif ($status === 'under_review') {
        $notif_title = "Application Under Review";
        $notif_icon = 'bi-hourglass-split';
        $notif_badge = 'primary';
        $notif_msg = "Your application for '{$job_title}' is now being evaluated by the hiring department.";
    }

    if (!empty($notes)) {
        $notif_msg .= " Supervisor Note: " . trim($notes);
    }

    create_notification(
        $student_id,
        'application_status',
        $notif_title,
        $notif_msg,
        'student/my-applications.php',
        $notif_icon,
        $notif_badge
    );
}

function update_application_status(int|string $id, string $status, string $notes = '', array $interview_data = []): bool {
    try {
        $pdo = get_db_connection();
        $target_app = get_application_by_id($id);
        if (!$target_app) return false;

        $old_status = $target_app['status'] ?? 'pending';
        $job_id = (int)($target_app['job_id'] ?? 0);

        $badge_map = [
            'pending'             => 'warning',
            'under_review'        => 'primary',
            'interview_scheduled' => 'info',
            'accepted'            => 'success',
            'declined'            => 'danger'
        ];
        $label_map = [
            'pending'             => 'Pending Review',
            'under_review'        => 'Under Review',
            'interview_scheduled' => 'Interview Scheduled',
            'accepted'            => 'Accepted / Hired',
            'declined'            => 'Declined / Filled'
        ];

        $status_label = $label_map[$status] ?? ucfirst(str_replace('_', ' ', $status));
        $status_badge = $badge_map[$status] ?? 'secondary';

        $interview_date = null;
        $interview_time = null;
        $interview_venue = null;

        if ($status === 'interview_scheduled' && !empty($interview_data)) {
            $raw_date = trim($interview_data['date'] ?? '');
            if (!empty($raw_date) && strtotime($raw_date) < strtotime(date('Y-m-d'))) {
                return false;
            }
            $interview_date = $raw_date ?: null;
            $interview_time = $interview_data['time'] ?? null;
            $interview_venue = trim($interview_data['venue'] ?? '');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE `applications` SET
                `status` = :status,
                `supervisor_notes` = :notes,
                `interview_date` = COALESCE(:interview_date, `interview_date`),
                `interview_time` = COALESCE(:interview_time, `interview_time`),
                `interview_venue` = COALESCE(:interview_venue, `interview_venue`),
                `updated_at` = NOW()
            WHERE `id` = :id
        ");

        $stmt->execute([
            ':status'          => $status,
            ':notes'           => $notes,
            ':interview_date'  => $interview_date,
            ':interview_time'  => $interview_time,
            ':interview_venue' => $interview_venue,
            ':id'              => (int)$id
        ]);

        if ($job_id > 0 && !sync_job_slot_capacity($pdo, $job_id, $old_status, $status)) {
            $pdo->rollBack();
            return false;
        }

        $pdo->commit();

        $student_id = (int)($target_app['student_id'] ?? 0);
        $job_title = $target_app['job_title'] ?? 'Campus Job';
        dispatch_application_status_notification(
            $student_id,
            $job_title,
            $status,
            $status_label,
            $notes,
            $interview_date,
            $interview_time,
            $interview_venue
        );

        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("update_application_status error: " . $e->getMessage());
        return false;
    }
}

function get_categories(): array {
    try {
        $pdo = get_db_connection();
        $stmt_cat = $pdo->query("SELECT * FROM `categories` ORDER BY `id` ASC");
        $cats = array_map('hydrate_category', $stmt_cat->fetchAll());

        // Dynamic active job counts
        $stmt_counts = $pdo->query("
            SELECT j.`category_id`, c.`name` AS category_name, COUNT(*) AS cnt 
            FROM `jobs` j
            JOIN `categories` c ON j.`category_id` = c.`id`
            WHERE j.`status` = 'active' 
            GROUP BY j.`category_id`, c.`name`
        ");
        $counts_by_id = [];
        $counts_by_name = [];
        while ($row = $stmt_counts->fetch()) {
            $counts_by_id[(int)$row['category_id']] = (int)$row['cnt'];
            $counts_by_name[$row['category_name']] = (int)$row['cnt'];
        }

        foreach ($cats as &$cat) {
            $cat['job_count'] = $counts_by_id[(int)$cat['id']] ?? ($counts_by_name[$cat['name']] ?? 0);
        }

        return $cats;
    } catch (Exception $e) {
        error_log("get_categories error: " . $e->getMessage());
        return [];
    }
}

function create_category(array $data, ?array $photo_file = null): int {
    try {
        $pdo = get_db_connection();
        $name = trim($data['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $image_path = null;
        if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $image_path = save_uploaded_category_photo($photo_file);
        }
        if (empty($image_path) && !empty($data['image'])) {
            $image_path = trim($data['image']);
        }

        $stmt = $pdo->prepare("
            INSERT INTO `categories` (`name`, `slug`, `icon`, `description`, `theme`, `badge_tag`, `badge_icon`, `job_count`, `hourly_range`, `image`, `created_at`)
            VALUES (:name, :slug, :icon, :description, :theme, :badge_tag, :badge_icon, 0, :hourly_range, :image, NOW())
        ");

        $stmt->execute([
            ':name'         => $name,
            ':slug'         => $slug,
            ':icon'         => $data['icon'] ?? 'bi-briefcase',
            ':description'  => trim($data['description'] ?? ''),
            ':theme'        => $data['theme'] ?? 'kld-green',
            ':badge_tag'    => $data['badge_tag'] ?? null,
            ':badge_icon'   => $data['badge_icon'] ?? null,
            ':hourly_range' => $data['hourly_range'] ?? null,
            ':image'        => $image_path
        ]);

        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_category error: " . $e->getMessage());
        return 0;
    }
}

function update_category(int|string $id, array $data, ?array $photo_file = null): bool {
    try {
        $pdo = get_db_connection();
        $id = (int)$id;
        if ($id <= 0) return false;

        $name = trim($data['name'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $image_path = null;
        if ($photo_file !== null && is_array($photo_file) && ($photo_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $image_path = save_uploaded_category_photo($photo_file);
        }
        if (empty($image_path) && isset($data['image']) && $data['image'] !== '') {
            $image_path = trim($data['image']);
        }

        $params = [
            ':id'           => $id,
            ':name'         => $name,
            ':slug'         => $slug,
            ':icon'         => $data['icon'] ?? 'bi-briefcase',
            ':description'  => trim($data['description'] ?? ''),
            ':theme'        => $data['theme'] ?? 'kld-green',
            ':badge_tag'    => $data['badge_tag'] ?? null,
            ':badge_icon'   => $data['badge_icon'] ?? null,
            ':hourly_range' => $data['hourly_range'] ?? null
        ];

        $image_clause = "";
        if ($image_path !== null) {
            $image_clause = ", `image` = :image";
            $params[':image'] = $image_path;
        }

        $sql = "
            UPDATE `categories`
            SET `name` = :name,
                `slug` = :slug,
                `icon` = :icon,
                `description` = :description,
                `theme` = :theme,
                `badge_tag` = :badge_tag,
                `badge_icon` = :badge_icon,
                `hourly_range` = :hourly_range
                {$image_clause}
            WHERE `id` = :id
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (Exception $e) {
        error_log("update_category error: " . $e->getMessage());
        return false;
    }
}

function delete_category(int|string $id): bool {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM `categories` WHERE `id` = :id");
        return $stmt->execute([':id' => (int)$id]);
    } catch (Exception $e) {
        error_log("delete_category error: " . $e->getMessage());
        return false;
    }
}

function delete_application(int|string $id, int|string|null $student_id = null): bool {
    try {
        $pdo = get_db_connection();
        if ($student_id) {
            $stmt = $pdo->prepare("DELETE FROM `applications` WHERE `id` = :id AND `student_id` = :sid");
            $stmt->execute([':id' => (int)$id, ':sid' => (int)$student_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM `applications` WHERE `id` = :id");
            $stmt->execute([':id' => (int)$id]);
        }
        return true;
    } catch (Exception $e) {
        error_log("delete_application error: " . $e->getMessage());
        return false;
    }
}
