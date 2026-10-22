<?php
/**
 * Campus Job Posting System - High-Velocity Verification Deck API
 * Handles card actions: approve, reject, request_revision, rollback (undo), and fetch deck.
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$admin = get_logged_user();
if (!$admin || ($admin['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Administrator privileges required.'
    ]);
    exit;
}

// Fetch pending cards deck via GET or POST action=get_deck
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$raw_input = file_get_contents('php://input');
$json_input = json_decode($raw_input, true) ?? [];
if (!empty($json_input['action'])) {
    $action = $json_input['action'];
}

if ($action === 'get_deck' || ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($action))) {
    try {
        $pdo = get_db_connection();
        $sql = "
            SELECT 
                u.`id`,
                u.`name`,
                u.`email`,
                u.`role`,
                u.`phone`,
                u.`created_at`,
                u.`verification_status`,
                sp.`student_id`,
                sp.`department`,
                sp.`course`,
                sp.`year_level`,
                sp.`rejection_reason`,
                sp.`registration_proof`,
                ep.`organization_name`,
                ep.`employer_type`,
                ep.`business_permit`,
                ep.`office_location`,
                ep.`accreditation_number`
            FROM `users` u
            LEFT JOIN `student_profiles` sp ON u.`id` = sp.`user_id` AND u.`role` = 'student'
            LEFT JOIN `employer_profiles` ep ON u.`id` = ep.`user_id` AND u.`role` = 'employer'
            WHERE (u.`verification_status` = 'pending_approval' OR sp.`verification_status` = 'pending_approval' OR ep.`verification_status` = 'pending_approval')
              AND (u.`status` IS NULL OR u.`status` = 'active')
              AND (u.`is_archived` = 0 OR u.`is_archived` IS NULL)
            ORDER BY u.`created_at` DESC
        ";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $deck = [];
        foreach ($rows as $r) {
            $email = strtolower($r['email'] ?? '');
            $domain = substr(strrchr($email, "@"), 1) ?: '';
            $is_kld = ($domain === 'kld.edu.ph' || str_ends_with($domain, '.kld.edu.ph'));
            $is_gmail = ($domain === 'gmail.com');

            $raw_proof = ($r['role'] === 'employer')
                ? ($r['business_permit'] ?? '')
                : ($r['registration_proof'] ?? '');

            $proof_url = '';
            $proof_is_pdf = false;
            if (!empty($raw_proof)) {
                $proof_url = '../' . ltrim(str_replace('\\', '/', $raw_proof), '/');
                $proof_is_pdf = (bool)preg_match('/\.pdf$/i', $raw_proof);
            }

            $id_num = ($r['role'] === 'employer')
                ? ($r['accreditation_number'] ?: 'PARTNER-' . $r['id'])
                : ($r['student_id'] ?: 'STUDENT-' . $r['id']);

            $deck[] = [
                'id'              => (int)$r['id'],
                'name'            => htmlspecialchars($r['name'] ?? 'Applicant'),
                'email'           => htmlspecialchars($r['email'] ?? ''),
                'domain'          => htmlspecialchars($domain),
                'is_kld'          => $is_kld,
                'is_gmail'        => $is_gmail,
                'role'            => $r['role'],
                'phone'           => htmlspecialchars($r['phone'] ?? 'N/A'),
                'identifier'      => htmlspecialchars($id_num),
                'department'      => htmlspecialchars($r['department'] ?? ($r['organization_name'] ?? 'General')),
                'course'          => htmlspecialchars($r['course'] ?? ''),
                'year_level'      => htmlspecialchars($r['year_level'] ?? ''),
                'org_name'        => htmlspecialchars($r['organization_name'] ?? ''),
                'employer_type'   => $r['employer_type'] ?? '',
                'proof_url'       => $proof_url,
                'proof_is_pdf'    => $proof_is_pdf,
                'rejection_notes' => htmlspecialchars($r['rejection_reason'] ?? ''),
                'created_at'      => date('M d, Y g:i A', strtotime($r['created_at'] ?? 'now')),
                'created_ago'     => time_ago($r['created_at'] ?? 'now'),
            ];
        }

        echo json_encode([
            'success' => true,
            'count'   => count($deck),
            'deck'    => $deck
        ]);
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load verification deck: ' . $e->getMessage()]);
        exit;
    }
}

// All modification actions must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = !empty($json_input) ? $json_input : $_POST;
$action = $input['action'] ?? '';
$target_id = (int)($input['id'] ?? 0);
$notes = trim((string)($input['notes'] ?? ''));

if ($target_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing applicant target ID.']);
    exit;
}

$target_user = get_user_by_id($target_id);
if (!$target_user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Target user account not found.']);
    exit;
}

if (($target_user['role'] ?? '') === 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized operation: Administrator accounts cannot be modified.']);
    exit;
}

$user_name = $target_user['name'] ?? 'Applicant';

try {
    switch ($action) {
        case 'approve':
            $ok = update_user_verification($target_id, 'verified');
            if ($ok) {
                create_notification(
                    $target_id,
                    'verification_approved',
                    'Account Approved & Verified',
                    'Congratulations! Your campus verification has been officially approved. You now have full access to campus career opportunities.',
                    'login.php',
                    'bi-check-circle-fill',
                    'success'
                );
                echo json_encode([
                    'success' => true,
                    'action'  => 'approve',
                    'id'      => $target_id,
                    'name'    => $user_name,
                    'message' => "Successfully verified account: {$user_name}"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not update verification status in database.']);
            }
            break;

        case 'reject':
            $clean_notes = $notes !== '' ? $notes : 'Submitted proof did not meet verification criteria.';
            $ok = update_user_verification($target_id, 'rejected', $clean_notes);
            if ($ok) {
                create_notification(
                    $target_id,
                    'verification_rejected',
                    'Registration Verification Declined',
                    "Your registration verification was declined. Reason: {$clean_notes}",
                    'pending-approval.php',
                    'bi-x-circle-fill',
                    'danger'
                );
                echo json_encode([
                    'success' => true,
                    'action'  => 'reject',
                    'id'      => $target_id,
                    'name'    => $user_name,
                    'message' => "Rejected registration for: {$user_name}"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not update rejection status in database.']);
            }
            break;

        case 'request_revision':
            $clean_notes = $notes !== '' ? $notes : 'Revision requested: Please upload a clearer copy of your document or alumni ID.';
            $ok = update_user_verification($target_id, 'rejected', $clean_notes);
            if ($ok) {
                create_notification(
                    $target_id,
                    'verification_revision',
                    'Action Required: Document Revision Requested',
                    "The university administrator requested a document revision for your registration: {$clean_notes}",
                    'pending-approval.php',
                    'bi-pencil-square',
                    'warning'
                );
                echo json_encode([
                    'success' => true,
                    'action'  => 'request_revision',
                    'id'      => $target_id,
                    'name'    => $user_name,
                    'notes'   => $clean_notes,
                    'message' => "Revision request dispatched for: {$user_name}"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not record revision request in database.']);
            }
            break;

        case 'rollback':
            // Multi-level Undo handler: restores user to pending_approval state
            $ok = update_user_verification($target_id, 'pending_approval', '');
            if ($ok) {
                echo json_encode([
                    'success' => true,
                    'action'  => 'rollback',
                    'id'      => $target_id,
                    'name'    => $user_name,
                    'message' => "Restored {$user_name} back to pending review."
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not rollback user status in database.']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Unknown action '{$action}'."]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server execution error: ' . $e->getMessage()]);
}
