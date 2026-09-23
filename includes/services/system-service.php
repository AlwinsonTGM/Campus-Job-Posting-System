<?php
declare(strict_types=1);

/**
 * System service: metrics, data mode, career updates, devblogs, notifications.
 * Extracted from includes/data-helper.php — 100% function contract preserved.
 */

function get_metrics_total_active_jobs(): int {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM `jobs` 
            WHERE `status` = 'active' 
              AND (`deadline` IS NULL OR `deadline` >= CURDATE())
        ");
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_metrics_partnered_offices(): int {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT COUNT(DISTINCT `department`) FROM `jobs` WHERE `department` IS NOT NULL AND `department` != ''");
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function get_metrics_students_hired(): int {
    try {
        $pdo = get_db_connection();
        $stmt_jobs = $pdo->query("SELECT SUM(`slots_filled`) FROM `jobs`");
        $job_filled = (int)$stmt_jobs->fetchColumn();

        $stmt_apps = $pdo->query("SELECT COUNT(*) FROM `applications` WHERE `status` = 'accepted'");
        $app_accepted = (int)$stmt_apps->fetchColumn();

        return max($job_filled, $app_accepted);
    } catch (Exception $e) {
        return 0;
    }
}

function get_metrics_avg_hourly_pay(): string {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT `pay_rate` FROM `jobs`");
        $rates = [];
        while ($row = $stmt->fetch()) {
            if (preg_match('/(\d+(?:\.\d+)?)/', $row['pay_rate'] ?? '', $matches)) {
                $rates[] = (float)$matches[1];
            }
        }
        if (empty($rates)) {
            return '₱85';
        }
        $avg = round(array_sum($rates) / count($rates));
        return '₱' . $avg;
    } catch (Exception $e) {
        return '₱85';
    }
}

function get_system_data_mode(): string {
    $mode_file = DATA_DIR . '/system_mode.json';
    if (file_exists($mode_file)) {
        $data = json_decode(file_get_contents($mode_file), true);
        return ($data['active_mode'] ?? 'demo');
    }
    return 'demo';
}

function switch_system_data_mode(string $mode, string $switched_by = 'User'): bool {
    $seed_dir = DATA_DIR . '/seeds/' . $mode;
    if (!is_dir($seed_dir)) {
        return false;
    }

    // 1. Sync seed files to data/
    $data_files = ['users.json', 'jobs.json', 'applications.json', 'categories.json',
                   'profile_requests.json', 'updates.json', 'devblogs.json'];

    foreach ($data_files as $file) {
        $src = $seed_dir . '/' . $file;
        $dst = DATA_DIR . '/' . $file;
        if (file_exists($src)) {
            copy($src, $dst);
        }
    }

    // 2. Re-import into MySQL
    try {
        require_once dirname(__DIR__) . '/database/migrate.php';
        execute_migration_and_seed(false, DATA_DIR, false, true);
    } catch (Exception $e) {
        error_log("switch_system_data_mode db error: " . $e->getMessage());
    }

    // Record mode change
    $mode_data = [
        'active_mode' => $mode,
        'last_switched_at' => date('Y-m-d H:i:s'),
        'switched_by' => $switched_by
    ];
    file_put_contents(DATA_DIR . '/system_mode.json', json_encode($mode_data, JSON_PRETTY_PRINT));

    // Clear session user if not admin; refresh admin user from newly loaded database
    $was_admin = (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');
    if ($was_admin) {
        $fresh_admin = get_user_by_email('admin@kld.edu.ph');
        if ($fresh_admin) {
            unset($fresh_admin['password']);
            $_SESSION['user'] = $fresh_admin;
        } else {
            unset($_SESSION['user']);
        }
    } else {
        unset($_SESSION['user']);
    }

    return true;
}

function reset_current_data_mode(string $switched_by = 'User'): bool {
    $current_mode = get_system_data_mode();
    return switch_system_data_mode($current_mode, $switched_by);
}

function wipe_real_data_fresh(): bool {
    return switch_system_data_mode('real', 'System Wipe');
}

function reset_demo_data(): bool {
    switch_system_data_mode('demo', 'System Reset');
    set_flash('info', 'Demo dataset has been reset to default campus state.');
    return true;
}

function get_career_updates(): array {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("
            SELECT 
                up.*,
                COALESCE(u.name, 'Career Development Office') AS author_name,
                COALESCE(ep.office_location, u.role, 'Coordinator') AS author_role,
                COALESCE(ep.organization_name, 'KLD Career Development & Placement Office') AS author_office
            FROM `updates` up
            LEFT JOIN `users` u ON u.id = up.author_id
            LEFT JOIN `employer_profiles` ep ON ep.user_id = u.id
            ORDER BY up.`published_at` DESC, up.`id` DESC
        ");
        $rows = $stmt->fetchAll();
        return array_map('hydrate_update', $rows);
    } catch (Exception $e) {
        error_log("get_career_updates error: " . $e->getMessage());
        return [];
    }
}

function get_career_update_by_id(int|string|null $id): ?array {
    if ($id === null || $id === '' || (int)$id <= 0) { return null; }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                up.*,
                COALESCE(u.name, 'Career Development Office') AS author_name,
                COALESCE(ep.office_location, u.role, 'Coordinator') AS author_role,
                COALESCE(ep.organization_name, 'KLD Career Development & Placement Office') AS author_office
            FROM `updates` up
            LEFT JOIN `users` u ON u.id = up.author_id
            LEFT JOIN `employer_profiles` ep ON ep.user_id = u.id
            WHERE up.`id` = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_update($row) : null;
    } catch (Exception $e) {
        error_log("get_career_update_by_id error: " . $e->getMessage());
        return null;
    }
}

function get_latest_career_updates(int $limit = 3, int|string|null $exclude_id = null): array {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                up.*,
                COALESCE(u.name, 'Career Development Office') AS author_name,
                COALESCE(ep.office_location, u.role, 'Coordinator') AS author_role,
                COALESCE(ep.organization_name, 'KLD Career Development & Placement Office') AS author_office
            FROM `updates` up
            LEFT JOIN `users` u ON u.id = up.author_id
            LEFT JOIN `employer_profiles` ep ON ep.user_id = u.id
        ";
        $params = [];
        if ($exclude_id !== null) {
            $sql .= " WHERE up.`id` != :ex_id";
            $params[':ex_id'] = (int)$exclude_id;
        }
        $sql .= " ORDER BY up.`published_at` DESC, up.`id` DESC LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map('hydrate_update', $rows);
    } catch (Exception $e) {
        error_log("get_latest_career_updates error: " . $e->getMessage());
        return [];
    }
}

function add_career_update(array $data): ?array {
    try {
        $pdo = get_db_connection();
        $title = trim($data['title'] ?? 'Campus Career Dispatch');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        $content = trim($data['content'] ?? '');
        $word_count = str_word_count(strip_tags($content));
        $read_time = max(1, ceil($word_count / 200)) . ' min read';

        $author_id = isset($data['author_id']) ? (int)$data['author_id'] : (int)($_SESSION['user']['id'] ?? 5);
        $author_name = trim($data['author_name'] ?? 'Career Development Office');
        
        $initials = '';
        $name_parts = explode(' ', $author_name);
        foreach ($name_parts as $np) {
            if (!empty($np)) $initials .= strtoupper(substr($np, 0, 1));
        }
        $initials = substr($initials, 0, 2) ?: 'CC';

        $image = !empty($data['image']) ? trim($data['image']) : 'assets/img/updates/update-01.jpg';
        $summary = trim($data['summary'] ?? (substr(strip_tags($content), 0, 160) . '...'));

        $stmt = $pdo->prepare("
            INSERT INTO `updates` (
                `slug`, `title`, `category`, `published_at`, `read_time`,
                `author_id`, `author_avatar`, `image`, `summary`, `content`, `created_at`
            ) VALUES (
                :slug, :title, :category, :published_at, :read_time,
                :author_id, :author_avatar, :image, :summary, :content, NOW()
            )
        ");

        $stmt->execute([
            ':slug'          => $slug,
            ':title'         => $title,
            ':category'      => trim($data['category'] ?? 'Campus News'),
            ':published_at'  => $data['published_at'] ?? date('Y-m-d H:i:s'),
            ':read_time'     => $data['read_time'] ?? $read_time,
            ':author_id'     => $author_id,
            ':author_avatar' => $initials,
            ':image'         => $image,
            ':summary'       => $summary,
            ':content'       => $content
        ]);

        $new_id = (int)$pdo->lastInsertId();
        return get_career_update_by_id($new_id);
    } catch (Exception $e) {
        error_log("add_career_update error: " . $e->getMessage());
        return null;
    }
}

function update_career_update(int|string $id, array $data): bool {
    try {
        $pdo = get_db_connection();
        $existing = get_career_update_by_id($id);
        if (!$existing) return false;

        $updates = [];
        $params = [':id' => (int)$id];

        if (isset($data['title'])) {
            $updates[] = "`title` = :title";
            $updates[] = "`slug` = :slug";
            $params[':title'] = trim($data['title']);
            $params[':slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']), '-'));
        }
        if (isset($data['category'])) {
            $updates[] = "`category` = :category";
            $params[':category'] = trim($data['category']);
        }
        if (isset($data['summary'])) {
            $updates[] = "`summary` = :summary";
            $params[':summary'] = trim($data['summary']);
        }
        if (isset($data['content'])) {
            $updates[] = "`content` = :content";
            $updates[] = "`read_time` = :read_time";
            $params[':content'] = trim($data['content']);
            $word_count = str_word_count(strip_tags($data['content']));
            $params[':read_time'] = max(1, ceil($word_count / 200)) . ' min read';
        }
        if (isset($data['image']) && !empty($data['image'])) {
            $updates[] = "`image` = :image";
            $params[':image'] = trim($data['image']);
        }
        if (isset($data['author_id'])) {
            $updates[] = "`author_id` = :author_id";
            $params[':author_id'] = (int)$data['author_id'];
        }

        if (!empty($updates)) {
            $updates[] = "`updated_at` = NOW()";
            $sql = "UPDATE `updates` SET " . implode(', ', $updates) . " WHERE `id` = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        return true;
    } catch (Exception $e) {
        error_log("update_career_update error: " . $e->getMessage());
        return false;
    }
}

function delete_career_update(int|string $id): bool {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM `updates` WHERE `id` = :id");
        $stmt->execute([':id' => (int)$id]);
        return true;
    } catch (Exception $e) {
        error_log("delete_career_update error: " . $e->getMessage());
        return false;
    }
}

function get_devblogs(): array {
    $file = __DIR__ . '/../data/devblogs.json';
    if (file_exists($file)) {
        $json = json_decode(file_get_contents($file), true);
        if (is_array($json) && !empty($json)) {
            return $json;
        }
    }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT * FROM `devblogs` ORDER BY `sprint_number` ASC");
        $rows = $stmt->fetchAll();
        return array_map('hydrate_devblog', $rows);
    } catch (Exception $e) {
        error_log("get_devblogs error: " . $e->getMessage());
        return [];
    }
}

function get_devblog_by_id(int|string|null $id): ?array {
    if ($id === null || $id === '') { return null; }
    $blogs = get_devblogs();
    foreach ($blogs as $b) {
        if (($b['id'] ?? '') === (string)$id || ($b['sprint_number'] ?? '') === (string)$id) {
            return $b;
        }
    }
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM `devblogs` WHERE `id` = :id OR `sprint_number` = :sprint LIMIT 1");
        $stmt->execute([':id' => (string)$id, ':sprint' => (string)$id]);
        $row = $stmt->fetch();
        return $row ? hydrate_devblog($row) : null;
    } catch (Exception $e) {
        error_log("get_devblog_by_id error: " . $e->getMessage());
        return null;
    }
}

function create_notification(int|string $user_id, string $type, string $title, string $message, ?string $link = null, string $icon = 'bi-bell', string $badge_color = 'primary'): int {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `icon`, `badge_color`, `is_read`, `created_at`)
            VALUES (:user_id, :type, :title, :message, :link, :icon, :badge_color, 0, NOW())
        ");
        $stmt->execute([
            ':user_id'     => (int)$user_id,
            ':type'        => substr($type, 0, 50),
            ':title'       => substr($title, 0, 255),
            ':message'     => $message,
            ':link'        => $link ? substr($link, 0, 255) : null,
            ':icon'        => substr($icon, 0, 100),
            ':badge_color' => substr($badge_color, 0, 50)
        ]);
        return (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("create_notification error: " . $e->getMessage());
        return 0;
    }
}

function notify_all_admins(string $type, string $title, string $message, ?string $link = null, string $icon = 'bi-shield-check', string $badge_color = 'warning'): int {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT `id` FROM `users` WHERE `role` = 'admin'");
        $admin_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $count = 0;
        foreach ($admin_ids as $aid) {
            if (create_notification($aid, $type, $title, $message, $link, $icon, $badge_color)) {
                $count++;
            }
        }
        return $count;
    } catch (Exception $e) {
        error_log("notify_all_admins error: " . $e->getMessage());
        return 0;
    }
}

function get_user_notifications(int|string $user_id, int $limit = 30, bool $unread_only = false): array {
    try {
        $pdo = get_db_connection();
        $sql = "SELECT * FROM `notifications` WHERE `user_id` = :user_id";
        if ($unread_only) {
            $sql .= " AND `is_read` = 0";
        }
        $sql .= " ORDER BY `created_at` DESC LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map('hydrate_notification', $rows);
    } catch (Exception $e) {
        error_log("get_user_notifications error: " . $e->getMessage());
        return [];
    }
}

function get_unread_notifications_count(int|string $user_id): int {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `notifications` WHERE `user_id` = :user_id AND `is_read` = 0");
        $stmt->execute([':user_id' => (int)$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("get_unread_notifications_count error: " . $e->getMessage());
        return 0;
    }
}

function mark_notification_as_read(int|string $notification_id, int|string|null $user_id = null): bool {
    try {
        $pdo = get_db_connection();
        if ($user_id !== null) {
            $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `id` = :id AND `user_id` = :user_id");
            return $stmt->execute([':id' => (int)$notification_id, ':user_id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$notification_id]);
        }
    } catch (Exception $e) {
        error_log("mark_notification_as_read error: " . $e->getMessage());
        return false;
    }
}

function mark_all_notifications_as_read(int|string $user_id): bool {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `user_id` = :user_id AND `is_read` = 0");
        return $stmt->execute([':user_id' => (int)$user_id]);
    } catch (Exception $e) {
        error_log("mark_all_notifications_as_read error: " . $e->getMessage());
        return false;
    }
}

function delete_notification(int|string $notification_id, int|string|null $user_id = null): bool {
    try {
        $pdo = get_db_connection();
        if ($user_id !== null) {
            $stmt = $pdo->prepare("DELETE FROM `notifications` WHERE `id` = :id AND `user_id` = :user_id");
            return $stmt->execute([':id' => (int)$notification_id, ':user_id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM `notifications` WHERE `id` = :id");
            return $stmt->execute([':id' => (int)$notification_id]);
        }
    } catch (Exception $e) {
        error_log("delete_notification error: " . $e->getMessage());
        return false;
    }
}

function get_notification_by_id(int|string|null $notification_id, int|string|null $user_id = null): ?array {
    try {
        $pdo = get_db_connection();
        if ($user_id !== null) {
            $stmt = $pdo->prepare("SELECT * FROM `notifications` WHERE `id` = :id AND `user_id` = :user_id LIMIT 1");
            $stmt->execute([':id' => (int)$notification_id, ':user_id' => (int)$user_id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `notifications` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => (int)$notification_id]);
        }
        $row = $stmt->fetch();
        return $row ? hydrate_notification($row) : null;
    } catch (Exception $e) {
        error_log("get_notification_by_id error: " . $e->getMessage());
        return null;
    }
}
