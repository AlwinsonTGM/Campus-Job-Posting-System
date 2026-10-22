<?php
/**
 * Migration Script: Convert Pending Verification Accounts from @kld.edu.ph to @gmail.com
 * Institutional @kld.edu.ph emails are reserved for auto-accepted / verified users.
 * Pending accounts in the verification queue must use personal / external email domains.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/data-helper.php';

echo "=================================================================\n";
echo "CONVERTING PENDING ACCOUNTS FROM @kld.edu.ph TO @gmail.com\n";
echo "=================================================================\n\n";

$pdo = get_db_connection();
$pdo->beginTransaction();

// 1. Fetch all pending accounts currently using @kld.edu.ph
$stmt = $pdo->query("
    SELECT u.id, u.name, u.email, u.role, sp.student_id, sp.department, sp.course, sp.year_level, sp.resume_file,
           ep.employer_type, ep.organization_name
    FROM users u
    LEFT JOIN student_profiles sp ON u.id = sp.user_id
    LEFT JOIN employer_profiles ep ON u.id = ep.user_id
    WHERE (u.verification_status = 'pending_approval' OR sp.verification_status = 'pending_approval' OR ep.verification_status = 'pending_approval')
      AND u.email LIKE '%@kld.edu.ph'
    ORDER BY u.id ASC
");
$pendingKldUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($pendingKldUsers) . " pending accounts with @kld.edu.ph emails.\n";

$emailUpdates = [];
$updateUserStmt = $pdo->prepare("UPDATE `users` SET `email` = :email, `name` = :name WHERE `id` = :id");
$updateEmpStmt = $pdo->prepare("UPDATE `employer_profiles` SET `employer_type` = :type, `organization_name` = :name, `accreditation_number` = :accred WHERE `user_id` = :id");

// Minimal PDF generator helper for updating resumes
function escape_pdf(string $text): string {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function build_pdf(array $ops): string {
    $objects = [];
    $offsets = [];
    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
    $objects[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>';
    $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
    
    $stream = implode("\n", $ops);
    $len = strlen($stream);
    $objects[6] = "<< /Length {$len} >>\nstream\n{$stream}\nendstream";
    
    $pdf = "%PDF-1.4\n";
    for ($i = 1; $i <= 6; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= "{$i} 0 obj\n" . $objects[$i] . "\nendobj\n";
    }
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 7\n0000000000 65535 f \n";
    for ($i = 1; $i <= 6; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    return $pdf;
}

function generate_student_resume(array $st, string $newEmail): string {
    $ops = [];
    $e = 'escape_pdf';
    
    $ops[] = "0.08 0.38 0.22 rg";
    $ops[] = "36 718 540 46 re f";
    $ops[] = "0.85 0.65 0.12 rg";
    $ops[] = "36 714 540 3 re f";
    
    $ops[] = "BT\n1 1 1 rg\n/F2 15 Tf\n45 738 Td (" . $e(strtoupper($st['name'])) . ") Tj\nET";
    $ops[] = "BT\n0.95 0.95 0.95 rg\n/F1 8.5 Tf\n45 724 Td (" . $e("KLD Student Candidate · " . ($st['course'] ?? 'Undergraduate Degree') . " (" . ($st['year_level'] ?? 'Student') . ")") . ") Tj\nET";
    
    $ops[] = "0.75 0.80 0.85 RG\n0.75 w";
    $ops[] = "36 655 540 46 re S";
    $ops[] = "0.96 0.97 0.98 rg";
    $ops[] = "36.5 655.5 539 45 re f";
    
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf";
    $ops[] = "44 685 Td (" . $e("Personal Email: ") . ") Tj\n/F2 8 Tf (" . $e($newEmail) . ") Tj\n/F1 8 Tf";
    $ops[] = "200 0 Td (" . $e("Student ID: ") . ") Tj\n/F2 8 Tf (" . $e($st['student_id'] ?? 'N/A') . ") Tj\nET";
    
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf";
    $ops[] = "44 669 Td (" . $e("Contact Number: ") . ") Tj\n/F2 8 Tf (" . $e("+63 920 411 " . $st['id']) . ") Tj\n/F1 8 Tf";
    $ops[] = "200 0 Td (" . $e("Academic Standing: ") . ") Tj\n/F2 8 Tf (" . $e("GWA " . ($st['gwa'] ?? '1.50') . " · Dean's List Standing") . ") Tj\nET";
    
    $ops[] = "0.08 0.38 0.22 rg";
    $ops[] = "36 625 540 16 re f";
    $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 630 Td (" . $e("ACADEMIC PROFILE & ENROLLMENT AT KLD") . ") Tj\nET";
    
    $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 9 Tf\n44 606 Td (" . $e("Kolehiyo ng Lungsod ng Dasmarinas") . ") Tj\nET";
    $ops[] = "BT\n0.2 0.2 0.2 rg\n/F1 8.5 Tf\n44 593 Td (" . $e(($st['department'] ?? 'Institute') . " · " . ($st['course'] ?? 'Degree Program')) . ") Tj\nET";
    $ops[] = "BT\n0.3 0.3 0.3 rg\n/F1 8 Tf\n44 580 Td (" . $e("Current Standing: " . ($st['year_level'] ?? 'Student') . " · Matriculated AY 2026-2027 · Fully Subsidized under R.A. 10931") . ") Tj\nET";
    
    $ops[] = "0.08 0.38 0.22 rg";
    $ops[] = "36 550 540 16 re f";
    $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 555 Td (" . $e("CORE COMPETENCIES & TECHNICAL ATTRIBUTES") . ") Tj\nET";
    
    $ops[] = "BT\n0.15 0.15 0.15 rg\n/F1 8.5 Tf\n44 530 Td (" . $e("• Applied Domain Skills: Office Record Administration, Technical Writing, Workflow Documentation") . ") Tj\nET";
    $ops[] = "BT\n0.15 0.15 0.15 rg\n/F1 8.5 Tf\n44 515 Td (" . $e("• Productivity Platforms: Google Workspace, Microsoft 365, Cloud Database Navigation, Team Collaboration") . ") Tj\nET";
    $ops[] = "BT\n0.15 0.15 0.15 rg\n/F1 8.5 Tf\n44 500 Td (" . $e("• Work Ethic: Academic Rigor, Professional Ethics, Punctual Shift Attendance, Confidential Data Handling") . ") Tj\nET";
    
    $ops[] = "0.08 0.38 0.22 rg";
    $ops[] = "36 470 540 16 re f";
    $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 475 Td (" . $e("TERMWORK & PRACTICUM EXPERIENCE") . ") Tj\nET";
    
    $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 9 Tf\n44 450 Td (" . $e("KLD Academic Practicum Project") . ") Tj\nET";
    $ops[] = "BT\n0.25 0.25 0.25 rg\n/F1 8 Tf\n44 437 Td (" . $e("Demonstrated reliable problem-solving and institutional procedural adherence in a rigorous academic setting.") . ") Tj\nET";
    
    $ops[] = "0.96 0.96 0.96 rg";
    $ops[] = "36 340 540 70 re f";
    $ops[] = "0.75 0.80 0.85 RG\n0.5 w";
    $ops[] = "36 340 540 70 re S";
    
    $ops[] = "BT\n0.1 0.2 0.35 rg\n/F2 8 Tf\n44 394 Td (" . $e("VERIFICATION NOTE & STATUS DECLARATION") . ") Tj\nET";
    $ops[] = "BT\n0.3 0.3 0.3 rg\n/F1 7.5 Tf\n44 380 Td (" . $e("This applicant registered using a personal webmail account (" . $newEmail . ").") . ") Tj";
    $ops[] = "44 368 Td (" . $e("Pending verification by KLD Administrator against official Registrar enrollment records.") . ") Tj\nET";
    
    return build_pdf($ops);
}

function generate_foundation_permit(string $name, string $tin, string $loc, int $id): string {
    $ops = [];
    $e = 'escape_pdf';
    
    $ops[] = "0.08 0.22 0.38 rg";
    $ops[] = "36 718 540 46 re f";
    $ops[] = "0.85 0.65 0.12 rg";
    $ops[] = "36 714 540 3 re f";
    
    $ops[] = "BT\n1 1 1 rg\n/F2 7.5 Tf\n185 750 Td (" . $e("REPUBLIC OF THE PHILIPPINES · DEPARTMENT OF FINANCE") . ") Tj\nET";
    $ops[] = "BT\n1 1 1 rg\n/F2 12 Tf\n190 734 Td (" . $e("BUREAU OF INTERNAL REVENUE") . ") Tj\nET";
    $ops[] = "BT\n1 1 1 rg\n/F1 8 Tf\n200 722 Td (" . $e("REVENUE DISTRICT OFFICE NO. 054B - DASMARINAS") . ") Tj\nET";
    
    $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 11 Tf\n36 695 Td (" . $e("BIR FORM 2303 - CERTIFICATE OF REGISTRATION") . ") Tj";
    $ops[] = "/F1 8 Tf\n380 0 Td (" . $e("OCN: 9RC000" . sprintf('%06d', $id)) . ") Tj\nET";
    
    $ops[] = "0.75 0.80 0.85 RG\n0.5 w";
    $ops[] = "36 570 540 115 re S";
    $ops[] = "0.98 0.98 0.99 rg";
    $ops[] = "36.5 570.5 539 114 re f";
    
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf\n44 670 Td (" . $e("TIN: ") . ") Tj\n/F2 8.5 Tf (" . $e($tin) . ") Tj\nET";
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf\n44 654 Td (" . $e("TAXPAYER NAME: ") . ") Tj\n/F2 9 Tf (" . $e($name) . ") Tj\nET";
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf\n44 638 Td (" . $e("REGISTERED ADDRESS: ") . ") Tj\n/F2 8.5 Tf (" . $e($loc) . ") Tj\nET";
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf\n44 622 Td (" . $e("LINE OF BUSINESS: ") . ") Tj\n/F2 8.5 Tf (" . $e("Educational Endowment & Youth Scholarship Aid") . ") Tj\nET";
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf\n44 606 Td (" . $e("CONTACT EMAIL: ") . ") Tj\n/F2 8.5 Tf (" . $e("scholarships.kld.foundation@gmail.com") . ") Tj\nET";
    $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf\n44 590 Td (" . $e("REGISTRATION DATE: ") . ") Tj\n/F2 8.5 Tf (" . $e("2026-10-22 · Status: Pending KLD MOA Accreditation") . ") Tj\nET";
    
    return build_pdf($ops);
}

// 2. Perform updates
foreach ($pendingKldUsers as $u) {
    $uid = (int)$u['id'];
    $name = $u['name'];
    $role = $u['role'];
    
    if ($uid === 1020) {
        $newName = 'KLD Alumni Scholarship & Educational Aid Foundation';
        $newEmail = 'scholarships.kld.foundation@gmail.com';
        $updateUserStmt->execute([':email' => $newEmail, ':name' => $newName, ':id' => $uid]);
        $updateEmpStmt->execute([
            ':type' => 'approved_partner',
            ':name' => $newName,
            ':accred' => 'MOA-2026-IND140',
            ':id' => $uid
        ]);
        
        $permitPath = __DIR__ . "/../uploads/permits/permit_partner_1020.pdf";
        $pdfContent = generate_foundation_permit($newName, '005-182-943-000', 'City Hall Compound, Palapala, Dasmariñas City', 1020);
        file_put_contents($permitPath, $pdfContent);
        
        $emailUpdates[$uid] = [
            'old' => $u['email'],
            'new' => $newEmail,
            'name' => $newName,
            'role' => 'employer',
            'type' => 'approved_partner'
        ];
        echo "  [EMP] #1020: {$u['email']} -> {$newEmail}\n";
    } else {
        // Students
        $cleanFirst = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $name)[0]));
        $parts = explode(' ', $name);
        $cleanLast = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', end($parts)));
        $newEmail = "{$cleanFirst}.{$cleanLast}{$uid}@gmail.com";
        
        $updateUserStmt->execute([':email' => $newEmail, ':name' => $name, ':id' => $uid]);
        
        // Regenerate resume PDF if exists
        $resumeFile = $u['resume_file'] ?? ('resume_' . ($u['student_id'] ?? '2026-1-000000') . '.pdf');
        $resumeBasename = basename($resumeFile);
        $resumePath = __DIR__ . "/../uploads/resumes/{$resumeBasename}";
        $resumePdf = generate_student_resume($u, $newEmail);
        file_put_contents($resumePath, $resumePdf);
        
        $emailUpdates[$uid] = [
            'old' => $u['email'],
            'new' => $newEmail,
            'name' => $name,
            'role' => 'student'
        ];
    }
}

$pdo->commit();
echo "\n>>> Database updated successfully! Committed " . count($emailUpdates) . " email transformations.\n";

// 3. Synchronize to users.json files
$jsonFiles = [
    __DIR__ . '/../data/users.json',
    __DIR__ . '/../data/seeds/demo/users.json',
    __DIR__ . '/../data/seeds/realistic_1k/users.json'
];

foreach ($jsonFiles as $jf) {
    if (!file_exists($jf)) continue;
    $data = json_decode(file_get_contents($jf), true);
    if (!is_array($data)) continue;
    
    $changed = 0;
    foreach ($data as &$row) {
        $rid = (int)($row['id'] ?? 0);
        if (isset($emailUpdates[$rid])) {
            $row['email'] = $emailUpdates[$rid]['new'];
            if (isset($emailUpdates[$rid]['name'])) {
                $row['name'] = $emailUpdates[$rid]['name'];
            }
            if ($rid === 1020) {
                $row['employer_type'] = 'approved_partner';
                $row['organization_name'] = $emailUpdates[$rid]['name'];
                $row['department'] = $emailUpdates[$rid]['name'];
            }
            $changed++;
        }
    }
    unset($row);
    file_put_contents($jf, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo ">>> Synchronized {$changed} records into " . basename(dirname($jf)) . '/' . basename($jf) . "\n";
}

echo "\n=================================================================\n";
echo "SUCCESSFULLY CONVERTED " . count($emailUpdates) . " PENDING ACCOUNTS TO @gmail.com\n";
echo "=================================================================\n";
