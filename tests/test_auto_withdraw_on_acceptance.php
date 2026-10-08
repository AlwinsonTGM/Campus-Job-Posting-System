<?php
/**
 * Test Suite: Auto-Withdrawal of Concurrent Applications on Hire
 *
 * Verifies that when a student is accepted/hired for a job opening:
 * 1. Their other pending, under_review, and interview_scheduled applications are automatically transitioned to 'withdrawn'.
 * 2. Already declined applications are left untouched.
 * 3. Supervisor notes on auto-withdrawn applications clearly document the reason and preserve any prior notes.
 * 4. The student receives both the acceptance notification and an auto-withdrawal explanation notification.
 * 5. Affected employers of the other jobs receive cancellation notifications alerting them that the candidate was hired elsewhere.
 * 6. Re-saving an already accepted application is idempotent and does not produce duplicate auto-withdrawals.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/data-helper.php';

echo "========================================================\n";
echo "  TEST: AUTO-WITHDRAW OTHER APPLICATIONS ON HIRE        \n";
echo "========================================================\n\n";

$pdo = get_db_connection();

// Create isolated test student
$test_email = 'test_autowithdraw_candidate_' . time() . '@kld.edu.ph';
$pdo->exec("
    INSERT INTO users (email, password, role, name, phone, status, is_email_verified)
    VALUES ('{$test_email}', 'hashed_pw', 'student', 'AutoWithdraw Candidate', '09123456789', 'active', 1)
");
$student_id = (int)$pdo->lastInsertId();

$pdo->exec("
    INSERT INTO student_profiles (user_id, student_id, department, course, year_level, sex, verification_status)
    VALUES ({$student_id}, 'KLD-AW-" . time() . "', 'Institute of Computing', 'BS Information Technology', '3rd Year', 'Male', 'verified')
");

echo "Created Isolated Test Student: ID={$student_id}, Email={$test_email}\n";

// Employers & Jobs
// We need 4 distinct jobs with employer_id
$stmt_jobs = $pdo->query("
    SELECT j.id, j.title, j.department, j.employer_id, j.status, j.slots_filled, j.slots_total
    FROM jobs j
    WHERE j.status = 'active' AND (j.slots_total - j.slots_filled) >= 1
    LIMIT 4
");
$jobs = $stmt_jobs->fetchAll(PDO::FETCH_ASSOC);
if (count($jobs) < 4) {
    die("Error: Need at least 4 active jobs with available slots for test fixtures.\n");
}

$job_a = $jobs[0]; // Accepted job
$job_b = $jobs[1]; // Pending -> should become withdrawn
$job_c = $jobs[2]; // Interview Scheduled -> should become withdrawn
$job_d = $jobs[3]; // Declined -> should remain declined

echo "Job A (To be accepted): [{$job_a['id']}] {$job_a['title']} ({$job_a['department']})\n";
echo "Job B (Pending):        [{$job_b['id']}] {$job_b['title']}\n";
echo "Job C (Interview):      [{$job_c['id']}] {$job_c['title']}\n";
echo "Job D (Declined):       [{$job_d['id']}] {$job_d['title']}\n\n";

// Clean up employer notifications for testing
$emp_ids = array_unique(array_filter([(int)$job_a['employer_id'], (int)$job_b['employer_id'], (int)$job_c['employer_id'], (int)$job_d['employer_id']]));
$notif_user_ids = $emp_ids;
if (!empty($notif_user_ids)) {
    $notif_user_clause = implode(',', $notif_user_ids);
    $pdo->exec("DELETE FROM notifications WHERE user_id IN ({$notif_user_clause}) AND type = 'application_withdrawn'");
}

// Create 4 applications for our test student
$ins = $pdo->prepare("
    INSERT INTO applications (job_id, student_id, cover_letter, availability, status, supervisor_notes, applied_at, updated_at)
    VALUES (:job_id, :student_id, 'Test application letter', '[\"Monday 08:00 AM - 12:00 PM\"]', :status, :notes, NOW(), NOW())
");

// App A: pending
$ins->execute([
    ':job_id' => $job_a['id'],
    ':student_id' => $student_id,
    ':status' => 'pending',
    ':notes' => 'Awaiting evaluation.'
]);
$app_a_id = (int)$pdo->lastInsertId();

// App B: pending
$ins->execute([
    ':job_id' => $job_b['id'],
    ':student_id' => $student_id,
    ':status' => 'pending',
    ':notes' => 'Application queued.'
]);
$app_b_id = (int)$pdo->lastInsertId();

// App C: interview_scheduled
$ins->execute([
    ':job_id' => $job_c['id'],
    ':student_id' => $student_id,
    ':status' => 'interview_scheduled',
    ':notes' => 'Initial interview scheduled for next week.'
]);
$app_c_id = (int)$pdo->lastInsertId();

// App D: declined
$ins->execute([
    ':job_id' => $job_d['id'],
    ':student_id' => $student_id,
    ':status' => 'declined',
    ':notes' => 'Position filled by another applicant.'
]);
$app_d_id = (int)$pdo->lastInsertId();

echo "Created test applications: A=#{$app_a_id}, B=#{$app_b_id}, C=#{$app_c_id}, D=#{$app_d_id}\n\n";

// --- EXECUTE STATUS UPDATE: Accept Job A ---
echo "--- 1. Accepting Application A (#{$app_a_id}) ---\n";
$accept_result = update_application_status($app_a_id, 'accepted', 'Hired for semester assistantship.');
assert($accept_result === true, "update_application_status must succeed for Application A");
echo "Application A accepted successfully!\n\n";

// --- VERIFY POST-ACCEPTANCE APPLICATION STATES ---
echo "--- 2. Verifying Application States ---\n";
$app_a_fresh = get_application_by_id($app_a_id);
$app_b_fresh = get_application_by_id($app_b_id);
$app_c_fresh = get_application_by_id($app_c_id);
$app_d_fresh = get_application_by_id($app_d_id);

// Check App A
echo "App A Status: {$app_a_fresh['status']} (Expected: accepted)\n";
assert($app_a_fresh['status'] === 'accepted', "App A must be accepted");

// Check App B (was pending)
echo "App B Status: {$app_b_fresh['status']} (Expected: withdrawn)\n";
echo "App B Notes:  {$app_b_fresh['supervisor_notes']}\n";
assert($app_b_fresh['status'] === 'withdrawn', "App B must be auto-withdrawn");
assert(str_contains($app_b_fresh['supervisor_notes'], 'Auto-withdrawn: Candidate accepted position'), "App B notes must state auto-withdrawn");
assert(str_contains($app_b_fresh['supervisor_notes'], 'Prior note: Application queued.'), "App B notes must preserve prior notes");

// Check App C (was interview_scheduled)
echo "App C Status: {$app_c_fresh['status']} (Expected: withdrawn)\n";
echo "App C Notes:  {$app_c_fresh['supervisor_notes']}\n";
assert($app_c_fresh['status'] === 'withdrawn', "App C must be auto-withdrawn");
assert(str_contains($app_c_fresh['supervisor_notes'], 'Auto-withdrawn'), "App C notes must state auto-withdrawn");
assert(str_contains($app_c_fresh['supervisor_notes'], 'Initial interview scheduled'), "App C notes must preserve prior interview notes");

// Check App D (was already declined)
echo "App D Status: {$app_d_fresh['status']} (Expected: declined)\n";
assert($app_d_fresh['status'] === 'declined', "App D must remain declined (untouched)");
echo "  [PASS] Application state transitions verified.\n\n";

// --- VERIFY NOTIFICATIONS ---
echo "--- 3. Verifying Notification Dispatches ---\n";
// Student notifications
$stmt_stu_notif = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = :uid 
    ORDER BY id DESC
");
$stmt_stu_notif->execute([':uid' => $student_id]);
$stu_notifs = $stmt_stu_notif->fetchAll(PDO::FETCH_ASSOC);

$accepted_notif_found = false;
$auto_withdrawn_notif_found = false;
foreach ($stu_notifs as $n) {
    if (str_contains($n['title'], 'Application Accepted')) {
        $accepted_notif_found = true;
        echo "Found Student Acceptance Alert: {$n['title']} -> {$n['message']}\n";
    }
    if (str_contains($n['title'], 'Concurrent Applications Auto-Withdrawn')) {
        $auto_withdrawn_notif_found = true;
        echo "Found Student Auto-Withdrawal Notice: {$n['title']} -> {$n['message']}\n";
        assert(str_contains($n['message'], '2 other active applications have been automatically withdrawn'), "Count of withdrawn apps must be 2");
    }
}
assert($accepted_notif_found, "Student must receive acceptance notification");
assert($auto_withdrawn_notif_found, "Student must receive auto-withdrawal notification");

// Employer notifications for Job B and Job C
$emp_b_id = (int)$job_b['employer_id'];
$emp_c_id = (int)$job_c['employer_id'];

if ($emp_b_id > 0) {
    $stmt_emp_b = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = :uid AND type = 'application_withdrawn' 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt_emp_b->execute([':uid' => $emp_b_id]);
    $emp_b_notif = $stmt_emp_b->fetch(PDO::FETCH_ASSOC);
    assert($emp_b_notif !== false, "Employer for Job B must receive withdrawal notification");
    echo "Found Employer B Notice: {$emp_b_notif['title']} -> {$emp_b_notif['message']}\n";
    assert(str_contains($emp_b_notif['message'], 'AutoWithdraw Candidate'), "Employer notification must include student name");
}

if ($emp_c_id > 0) {
    $stmt_emp_c = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = :uid AND type = 'application_withdrawn' 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt_emp_c->execute([':uid' => $emp_c_id]);
    $emp_c_notif = $stmt_emp_c->fetch(PDO::FETCH_ASSOC);
    assert($emp_c_notif !== false, "Employer for Job C must receive withdrawal notification");
    echo "Found Employer C Notice: {$emp_c_notif['title']} -> {$emp_c_notif['message']}\n";
}
echo "  [PASS] All student and employer notifications verified.\n\n";

// --- 4. VERIFY IDEMPOTENCY ---
echo "--- 4. Verifying Idempotency ---\n";
$notif_count_before = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE type = 'application_withdrawn'")->fetchColumn();
$re_accept_res = update_application_status($app_a_id, 'accepted', 'Updated supervisor note on hire.');
assert($re_accept_res === true, "Re-saving accepted status should succeed");
$notif_count_after = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE type = 'application_withdrawn'")->fetchColumn();
assert($notif_count_before === $notif_count_after, "No new auto-withdrawal notifications should be generated on re-save");
echo "Idempotency check: No new auto-withdrawal notifications generated on re-save.\n";
echo "  [PASS] Idempotency verified.\n\n";

// --- CLEANUP TEST FIXTURES ---
echo "--- 5. Cleanup Test Fixtures ---\n";
// Re-open job slot for Job A
sync_job_slot_capacity($pdo, (int)$job_a['id'], 'accepted', 'withdrawn');
// Deleting parent test user cascades down to applications and student profile
$pdo->exec("DELETE FROM users WHERE id = {$student_id}");
if (!empty($notif_user_ids)) {
    $pdo->exec("DELETE FROM notifications WHERE user_id IN ({$notif_user_clause}) AND type = 'application_withdrawn'");
}
echo "Test fixtures successfully cleaned up.\n\n";

echo "========================================================\n";
echo "  ALL AUTO-WITHDRAWAL TESTS PASSED (100% SUCCESS)       \n";
echo "========================================================\n";
