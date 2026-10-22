<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';

try {
    $pdo = get_db_connection();
    if (!$pdo) {
        throw new RuntimeException("Database connection unavailable");
    }

    // 1. Remove non-baseline applications created during test runs
    // Baseline application IDs from demo seeds are 1, 2, 3, 4 (job 9), 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22
    // We clean any application where student_id = 1 and job_id NOT IN (1, 2) so test applications are hermetic
    $pdo->exec("DELETE FROM applications WHERE student_id = 1 AND job_id NOT IN (1, 2)");

    // Reset slots_filled & vacancies dynamically on jobs that were touched by tests
    $pdo->exec("UPDATE jobs SET slots_filled = (SELECT COUNT(*) FROM applications a WHERE a.job_id = jobs.id AND a.status = 'accepted')");
    $pdo->exec("UPDATE jobs SET vacancies = slots_total - slots_filled");

    echo json_encode(['status' => 'success', 'message' => 'Test fixtures restored to baseline']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
