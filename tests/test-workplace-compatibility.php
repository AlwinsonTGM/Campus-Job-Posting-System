<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/data-helper.php';

function check_workplace(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo "PASS: {$message}\n";
}

check_workplace(array_keys(get_work_setups('approved_partner')) === ['Near-Campus', 'Hybrid', 'Remote'], 'Partners can choose their office, hybrid, or remote work only');
check_workplace(array_keys(get_work_setups('university_office')) === ['On-Campus', 'Hybrid', 'Remote'], 'University offices can choose campus, hybrid, or remote work only');
check_workplace(count(get_work_setups()) === 4, 'Student search retains all workplace filters');

$pdo = get_db_connection();
$partnerId = $pdo->query("SELECT user_id FROM employer_profiles WHERE employer_type = 'approved_partner' LIMIT 1")->fetchColumn();
check_workplace((bool)$partnerId, 'An existing partner is available for service checks');
$pdo->beginTransaction();
try {
    $jobId = create_job(['title' => 'Workplace compatibility regression', 'employer_id' => $partnerId, 'employer_type' => 'university_office', 'work_setup' => 'On-Campus']);
    check_workplace($jobId === 0, 'Create rejects on-campus work even when a partner spoofs the employer type');
    $jobId = create_job(['title' => 'Workplace compatibility regression', 'employer_id' => $partnerId, 'work_setup' => 'Near-Campus']);
    check_workplace($jobId > 0, 'Partner can create a compatible vacancy');
    check_workplace(!update_job($jobId, ['work_setup' => 'On-Campus', 'employer_type' => 'university_office']), 'Edit rejects incompatible workplace changes');
    check_workplace(get_job_by_id($jobId)['work_setup'] === 'Near-Campus', 'Rejected edit preserves the saved workplace');
    check_workplace(!update_job($jobId, ['work_setup' => 'unknown']), 'Unknown workplace values are rejected');
    check_workplace(update_job($jobId, ['work_setup' => 'Remote']), 'Compatible workplace changes can be saved');
    check_workplace(get_job_by_id($jobId)['work_setup'] === 'Remote', 'Compatible edit persists');
    check_workplace(update_job($jobId, ['description' => 'Edit without changing the workplace']), 'Partial edits retain a compatible workplace');
    $defaultId = create_job(['title' => 'Default partner workplace regression', 'employer_id' => $partnerId]);
    check_workplace($defaultId > 0 && get_job_by_id($defaultId)['work_setup'] === 'Near-Campus', 'Partner vacancies default to the partner office');
    $officeId = $pdo->query("SELECT user_id FROM employer_profiles WHERE employer_type = 'university_office' LIMIT 1")->fetchColumn();
    check_workplace(create_job(['title' => 'Invalid office workplace regression', 'employer_id' => $officeId, 'work_setup' => 'Near-Campus']) === 0, 'University offices cannot publish partner-office workplaces');
} finally {
    $pdo->rollBack();
}
