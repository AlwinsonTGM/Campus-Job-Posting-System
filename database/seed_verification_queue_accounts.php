<?php
/**
 * Campus Job Posting System - Verification Queue Test Accounts Seeder
 * Injects 20 fresh, realistic student & employer accounts in pending_approval state
 * with authentic PDF CORs, Resumes, and BIR Business Permits for interactive verification testing.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';

// Reuse KldPdfBuilder if not already loaded
if (!class_exists('KldPdfBuilder')) {
    class KldPdfBuilder {
        public static function escape(string $text): string {
            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        }
        
        public static function buildPdf(array $ops): string {
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

        public static function generateCor(array $student, array $subjects): string {
            $ops = [];
            $e = [self::class, 'escape'];
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 718 540 46 re f";
            $ops[] = "0.85 0.65 0.12 rg";
            $ops[] = "36 714 540 3 re f";
            
            $ops[] = "BT\n1 1 1 rg\n/F2 7.5 Tf\n170 750 Td (" . $e("REPUBLIC OF THE PHILIPPINES · CITY OF DASMARINAS, CAVITE") . ") Tj\nET";
            $ops[] = "BT\n1 1 1 rg\n/F2 13 Tf\n175 733 Td (" . $e("KOLEHIYO NG LUNGSOD NG DASMARINAS") . ") Tj\nET";
            $ops[] = "BT\n1 1 1 rg\n/F1 8 Tf\n212 721 Td (" . $e("OFFICE OF THE UNIVERSITY REGISTRAR") . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.15 0.2 rg\n/F2 11 Tf\n36 696 Td (" . $e("OFFICIAL CERTIFICATE OF REGISTRATION (COR)") . ") Tj";
            $ops[] = "/F1 8.5 Tf\n360 0 Td (" . $e("Ref No: KLD-COR-2026-" . substr(md5($student['student_id']), 0, 6)) . ") Tj\nET";
            
            $ops[] = "BT\n0.3 0.35 0.4 rg\n/F2 8.5 Tf\n36 684 Td (" . $e("ACADEMIC YEAR 2026-2027 · FIRST SEMESTER") . ") Tj";
            $ops[] = "/F1 8 Tf\n410 0 Td (" . $e("Date Issued: October 22, 2026") . ") Tj\nET";
            
            $ops[] = "0.75 0.80 0.85 RG\n0.75 w";
            $ops[] = "36 575 540 98 re S";
            $ops[] = "0.93 0.95 0.97 rg";
            $ops[] = "36.5 653 539 19.5 re f";
            
            $ops[] = "BT\n0.15 0.25 0.35 rg\n/F2 8.5 Tf\n44 659 Td (" . $e("STUDENT MATRICULATION PROFILE") . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F1 8.5 Tf\n44 642 Td (" . $e("Student ID Number: ") . ") Tj\n/F2 8.5 Tf (" . $e($student['student_id']) . ") Tj\n/F1 8.5 Tf";
            $ops[] = "230 0 Td (" . $e("Academic Institute: ") . ") Tj\n/F2 8.5 Tf (" . $e($student['department']) . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F1 8.5 Tf\n44 626 Td (" . $e("Student Name: ") . ") Tj\n/F2 8.5 Tf (" . $e(strtoupper($student['name'])) . ") Tj\n/F1 8.5 Tf";
            $ops[] = "230 0 Td (" . $e("Degree Program: ") . ") Tj\n/F2 8.5 Tf (" . $e($student['course']) . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F1 8.5 Tf\n44 610 Td (" . $e("Curriculum Year: ") . ") Tj\n/F2 8.5 Tf (" . $e($student['year_level'] . " · Regular") . ") Tj\n/F1 8.5 Tf";
            $ops[] = "230 0 Td (" . $e("Cumulative GWA: ") . ") Tj\n/F2 8.5 Tf (" . $e($student['gwa'] . " (Good Standing)") . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F1 8.5 Tf\n44 594 Td (" . $e("Tuition Status: ") . ") Tj\n/F2 8.5 Tf (" . $e("Subsidized Higher Education (R.A. No. 10931)") . ") Tj\n/F1 8.5 Tf";
            $ops[] = "230 0 Td (" . $e("Matriculation Status: ") . ") Tj\n/F2 8.5 Tf (" . $e("OFFICIALLY ENROLLED") . ") Tj\nET";
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 545 540 18 re f";
            
            $ops[] = "BT\n1 1 1 rg\n/F2 8 Tf";
            $ops[] = "44 551 Td (" . $e("COURSE CODE") . ") Tj";
            $ops[] = "65 0 Td (" . $e("COURSE DESCRIPTIVE TITLE") . ") Tj";
            $ops[] = "215 0 Td (" . $e("UNITS") . ") Tj";
            $ops[] = "45 0 Td (" . $e("DAYS & TIME SCHEDULE") . ") Tj";
            $ops[] = "130 0 Td (" . $e("ROOM") . ") Tj\nET";
            
            $yRow = 525;
            $totalUnits = 0.0;
            foreach ($subjects as $idx => $subj) {
                [$code, $desc, $units, $sched] = $subj;
                $totalUnits += (float)$units;
                $room = "BLDG-" . chr(65 + ($idx % 3)) . " RM-" . (201 + $idx);
                
                if ($idx % 2 === 1) {
                    $ops[] = "0.96 0.97 0.99 rg";
                    $ops[] = "36 {$yRow} 540 20 re f";
                }
                $ops[] = "0.88 0.90 0.92 RG\n0.5 w";
                $ops[] = "36 {$yRow} 540 20 re S";
                
                $textY = $yRow + 6;
                $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 8 Tf\n44 {$textY} Td (" . $e($code) . ") Tj\nET";
                $ops[] = "BT\n0.15 0.15 0.15 rg\n/F1 8 Tf\n109 {$textY} Td (" . $e($desc) . ") Tj\nET";
                $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 8 Tf\n324 {$textY} Td (" . sprintf("%.1f", $units) . ") Tj\nET";
                $ops[] = "BT\n0.2 0.2 0.2 rg\n/F1 8 Tf\n369 {$textY} Td (" . $e($sched) . ") Tj";
                $ops[] = "499 {$textY} Td (" . $e($room) . ") Tj\nET";
                
                $yRow -= 20;
            }
            
            $ops[] = "0.90 0.93 0.96 rg";
            $ops[] = "36 {$yRow} 540 18 re f";
            $ops[] = "0.75 0.80 0.85 RG\n0.5 w";
            $ops[] = "36 {$yRow} 540 18 re S";
            
            $totY = $yRow + 5;
            $ops[] = "BT\n0.1 0.2 0.1 rg\n/F2 8 Tf\n44 {$totY} Td (" . $e("TOTAL ACADEMIC LOAD: " . sprintf("%.1f", $totalUnits) . " UNITS") . ") Tj";
            $ops[] = "280 0 Td (" . $e("TOTAL ASSESSED TUITION & MISC: PHP 0.00 (100% RA 10931 SUBSIDIZED)") . ") Tj\nET";
            
            $sealY = $yRow - 105;
            $ops[] = "0.75 0.80 0.85 RG\n0.75 w";
            $ops[] = "36 {$sealY} 540 95 re S";
            $ops[] = "0.97 0.98 0.99 rg";
            $ops[] = "36.5 {$sealY} 539 94 re f";
            
            $ops[] = "BT\n0.1 0.2 0.35 rg\n/F2 8 Tf\n45 " . ($sealY + 75) . " Td (" . $e("OFFICIAL ENROLLMENT CERTIFICATION & VERIFICATION NOTICE") . ") Tj\nET";
            $ops[] = "BT\n0.3 0.35 0.4 rg\n/F1 7.5 Tf";
            $ops[] = "45 " . ($sealY + 62) . " Td (" . $e("This document is an authentic electronic Certificate of Registration (e-COR) officially validated") . ") Tj";
            $ops[] = "45 " . ($sealY + 52) . " Td (" . $e("by the Kolehiyo ng Lungsod ng Dasmarinas Office of the University Registrar. It certifies that") . ") Tj";
            $ops[] = "45 " . ($sealY + 42) . " Td (" . $e("the bearer is actively matriculated and eligible for campus student assistantships and internships.") . ") Tj";
            $ops[] = "/F2 7.5 Tf\n45 " . ($sealY + 28) . " Td (" . $e("Security Serial Barcode: *KLD-REG-2026-COR-" . strtoupper(substr(md5($student['student_id']), 0, 10)) . "*") . ") Tj\nET";
            
            $ops[] = "0.08 0.38 0.22 RG\n1 w";
            $ops[] = "380 " . ($sealY + 14) . " 180 50 re S";
            $ops[] = "BT\n0.08 0.38 0.22 rg\n/F2 7.5 Tf\n390 " . ($sealY + 48) . " Td (" . $e("KLD REGISTRAR ELECTRONIC SEAL") . ") Tj\nET";
            $ops[] = "BT\n0.3 0.3 0.3 rg\n/F1 7 Tf\n390 " . ($sealY + 36) . " Td (" . $e("AUTHENTICATED RECORD DIVISION") . ") Tj\nET";
            $ops[] = "BT\n0.2 0.2 0.2 rg\n/F2 7 Tf\n390 " . ($sealY + 22) . " Td (" . $e("SERIAL: " . strtoupper(substr(md5($student['student_id']), 0, 12))) . ") Tj\nET";
            
            return self::buildPdf($ops);
        }

        public static function generateResume(array $student, array $skills, array $projects): string {
            $ops = [];
            $e = [self::class, 'escape'];
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 718 540 46 re f";
            $ops[] = "0.85 0.65 0.12 rg";
            $ops[] = "36 714 540 3 re f";
            
            $ops[] = "BT\n1 1 1 rg\n/F2 15 Tf\n45 738 Td (" . $e(strtoupper($student['name'])) . ") Tj\nET";
            $ops[] = "BT\n0.95 0.95 0.95 rg\n/F1 8.5 Tf\n45 724 Td (" . $e("KLD Student Candidate · " . $student['course'] . " (" . $student['year_level'] . ")") . ") Tj\nET";
            
            $ops[] = "0.75 0.80 0.85 RG\n0.75 w";
            $ops[] = "36 655 540 46 re S";
            $ops[] = "0.96 0.97 0.98 rg";
            $ops[] = "36.5 655.5 539 45 re f";
            
            $emailLabel = str_ends_with(strtolower($student['email']), '@kld.edu.ph') ? "Institutional Email: " : "Candidate Email: ";
            $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf";
            $ops[] = "44 685 Td (" . $e($emailLabel) . ") Tj\n/F2 8 Tf (" . $e($student['email']) . ") Tj\n/F1 8 Tf";
            $ops[] = "200 0 Td (" . $e("Student ID: ") . ") Tj\n/F2 8 Tf (" . $e($student['student_id']) . ") Tj\nET";
            
            $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8 Tf";
            $ops[] = "44 669 Td (" . $e("Contact Number: ") . ") Tj\n/F2 8 Tf (" . $e($student['phone']) . ") Tj\n/F1 8 Tf";
            $ops[] = "200 0 Td (" . $e("Academic Standing: ") . ") Tj\n/F2 8 Tf (" . $e("GWA " . $student['gwa'] . " · Good Standing") . ") Tj\nET";
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 625 540 16 re f";
            $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 630 Td (" . $e("ACADEMIC PROFILE & ENROLLMENT AT KLD") . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 9 Tf\n44 606 Td (" . $e("Kolehiyo ng Lungsod ng Dasmarinas") . ") Tj\nET";
            $ops[] = "BT\n0.2 0.2 0.2 rg\n/F1 8.5 Tf\n44 593 Td (" . $e($student['department'] . " · " . $student['course']) . ") Tj\nET";
            $ops[] = "BT\n0.3 0.3 0.3 rg\n/F1 8 Tf\n44 580 Td (" . $e("Current Standing: " . $student['year_level'] . " · Matriculated AY 2026-2027 · Fully Subsidized under R.A. 10931") . ") Tj\nET";
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 550 540 16 re f";
            $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 555 Td (" . $e("TECHNICAL COMPETENCIES & PROFESSIONAL SKILLS") . ") Tj\nET";
            
            $ySkill = 530;
            $skillStr = implode("   •   ", $skills);
            $ops[] = "BT\n0.15 0.20 0.25 rg\n/F1 8.5 Tf\n44 {$ySkill} Td (" . $e("•   " . $skillStr) . ") Tj\nET";
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 495 540 16 re f";
            $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 500 Td (" . $e("KEY ACADEMIC PROJECTS & APPLIED PRACTICUM") . ") Tj\nET";
            
            $yProj = 475;
            foreach ($projects as $proj) {
                $ops[] = "BT\n0.10 0.15 0.20 rg\n/F2 8.5 Tf\n44 {$yProj} Td (" . $e("•  " . $proj['title']) . ") Tj\nET";
                $yProj -= 12;
                $ops[] = "BT\n0.30 0.35 0.40 rg\n/F1 8 Tf\n54 {$yProj} Td (" . $e($proj['description']) . ") Tj\nET";
                $yProj -= 16;
            }
            
            $ops[] = "0.10 0.20 0.38 rg";
            $ops[] = "36 385 540 16 re f";
            $ops[] = "BT\n1 1 1 rg\n/F2 8.5 Tf\n44 390 Td (" . $e("WORK-STUDY AVAILABILITY & INSTITUTIONAL COMPLIANCE") . ") Tj\nET";
            
            $ops[] = "0.80 0.85 0.90 RG\n0.5 w";
            $ops[] = "36 315 540 60 re S";
            $ops[] = "0.97 0.98 0.99 rg";
            $ops[] = "36.5 315.5 539 59 re f";
            
            $ops[] = "BT\n0.15 0.20 0.25 rg\n/F2 8.5 Tf\n46 357 Td (" . $e("Declared Shift Availability: ") . ") Tj\n/F1 8.5 Tf (" . $e($student['avail_summary']) . ") Tj\nET";
            $ops[] = "BT\n0.25 0.30 0.35 rg\n/F1 8 Tf";
            $ops[] = "46 343 Td (" . $e("Schedule Feasibility: Validated zero class conflict against official KLD Certificate of Registration (COR).") . ") Tj";
            $ops[] = "46 329 Td (" . $e("Work-Study Readiness: Prepared to assume 15-20 hours/week on-campus assignment upon departmental endorsement.") . ") Tj\nET";
            
            $ops[] = "BT\n0.5 0.5 0.5 rg\n/F1 7 Tf\n165 40 Td (" . $e("KLD Campus Job Posting System · Official Student Digital Portfolio · Verified Record") . ") Tj\nET";
            
            return self::buildPdf($ops);
        }

        public static function generateBirForm2303(string $name, string $tin, string $loc, string $industry, int $uid, string $status, ?string $reason): string {
            $ops = [];
            $e = [self::class, 'escape'];
            
            $ops[] = "0.07 0.18 0.36 rg";
            $ops[] = "36 718 540 46 re f";
            $ops[] = "0.85 0.65 0.12 rg";
            $ops[] = "36 714 540 3 re f";
            
            $ops[] = "BT\n1 1 1 rg\n/F2 7.5 Tf\n175 750 Td (" . $e("REPUBLIC OF THE PHILIPPINES · DEPARTMENT OF FINANCE") . ") Tj\nET";
            $ops[] = "BT\n1 1 1 rg\n/F2 12 Tf\n170 733 Td (" . $e("BUREAU OF INTERNAL REVENUE") . ") Tj\nET";
            $ops[] = "BT\n1 1 1 rg\n/F1 8 Tf\n150 721 Td (" . $e("REVENUE REGION NO. 9A - SOUTH CAVITE · RDO NO. 54B EAST CAVITE") . ") Tj\nET";
            
            $ops[] = "BT\n0.07 0.18 0.36 rg\n/F2 11 Tf\n36 696 Td (" . $e("BIR FORM NO. 2303 — CERTIFICATE OF REGISTRATION") . ") Tj\nET";
            $ops[] = "BT\n0.3 0.35 0.4 rg\n/F1 8 Tf\n36 684 Td (" . $e("OCN: 9RC000" . sprintf('%07d', $uid * 137) . " · Date Issued: October 22, 2026") . ") Tj\nET";
            
            $ops[] = "0.75 0.80 0.85 RG\n0.75 w";
            $ops[] = "36 490 540 180 re S";
            $ops[] = "0.93 0.95 0.97 rg";
            $ops[] = "36.5 648 539 21.5 re f";
            
            $ops[] = "BT\n0.10 0.20 0.38 rg\n/F2 8.5 Tf\n44 654 Td (" . $e("TAXPAYER IDENTIFICATION & CORPORATE PROFILE") . ") Tj\nET";
            
            $fields = [
                ['Taxpayer Name:', $name],
                ['Taxpayer Identification No. (TIN):', $tin],
                ['Registered Commercial Address:', $loc],
                ['Primary Line of Business:', $industry],
                ['Philippine Standard Industry Code:', 'PSIC-6201 (Information Technology & Engineering)'],
                ['Tax Registration Status:', 'ACTIVE TAXPAYER IN GOOD STANDING']
            ];
            
            $yF = 630;
            foreach ($fields as [$label, $val]) {
                $ops[] = "BT\n0.2 0.25 0.3 rg\n/F2 8.5 Tf\n45 {$yF} Td (" . $e($label) . ") Tj\nET";
                $ops[] = "BT\n0.1 0.1 0.1 rg\n/F1 8.5 Tf\n220 {$yF} Td (" . $e($val) . ") Tj\nET";
                $yF -= 22;
            }
            
            $ops[] = "0.80 0.85 0.90 RG\n0.5 w";
            $ops[] = "36 340 540 130 re S";
            $ops[] = "0.97 0.98 0.99 rg";
            $ops[] = "36.5 340.5 539 129 re f";
            
            $ops[] = "BT\n0.07 0.18 0.36 rg\n/F2 8.5 Tf\n45 450 Td (" . $e("CITY GOVERNMENT OF DASMARINAS — BUSINESS PERMIT & LICENSING") . ") Tj\nET";
            
            $bploLines = [
                "Mayor's Business Permit No: BP-DAS-2026-" . sprintf('%06d', $uid * 41),
                "BPLO Official Receipt Number: OR-BPLO-" . sprintf('%08d', $uid * 893),
                "Premises Inspection Status: PASSED / COMPLIANT WITH LOCAL FIRE & SANITARY ORDINANCES",
                "Permit Validity Period: January 01, 2026 to December 31, 2026"
            ];
            $yB = 432;
            foreach ($bploLines as $bl) {
                $ops[] = "BT\n0.25 0.30 0.35 rg\n/F1 8 Tf\n45 {$yB} Td (" . $e($bl) . ") Tj\nET";
                $yB -= 16;
            }
            
            $ops[] = "0.07 0.18 0.36 RG\n1 w";
            $ops[] = "360 215 216 100 re S";
            $ops[] = "BT\n0.07 0.18 0.36 rg\n/F2 7.5 Tf\n372 298 Td (" . $e("CITY OF DASMARINAS — BPLO") . ") Tj\nET";
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 9 Tf\n372 278 Td (" . $e("ATTY. BENJAMIN CRISTOBAL") . ") Tj\nET";
            $ops[] = "BT\n0.3 0.3 0.3 rg\n/F1 8 Tf\n372 264 Td (" . $e("Head, Business Permits & Licensing Office") . ") Tj\nET";
            $ops[] = "BT\n0.07 0.18 0.36 rg\n/F2 7 Tf\n372 230 Td (" . $e("ACCREDITED MOA PARTNER ENTITY") . ") Tj\nET";
            
            $ops[] = "BT\n0.5 0.5 0.5 rg\n/F1 7 Tf\n140 40 Td (" . $e("Kolehiyo ng Lungsod ng Dasmarinas · Career Services Office · Partner Accreditation Record") . ") Tj\nET";
            return self::buildPdf($ops);
        }

        public static function generateUnivCharter(string $name, string $dept, string $loc, string $contact, string $rate, string $accreditation): string {
            $ops = [];
            $e = [self::class, 'escape'];
            
            $ops[] = "0.08 0.38 0.22 rg";
            $ops[] = "36 718 540 46 re f";
            $ops[] = "0.85 0.65 0.12 rg";
            $ops[] = "36 714 540 3 re f";
            
            $ops[] = "BT\n1 1 1 rg\n/F2 7.5 Tf\n170 750 Td (" . $e("REPUBLIC OF THE PHILIPPINES · CITY OF DASMARINAS, CAVITE") . ") Tj\nET";
            $ops[] = "BT\n1 1 1 rg\n/F2 13 Tf\n175 733 Td (" . $e("KOLEHIYO NG LUNGSOD NG DASMARINAS") . ") Tj\nET";
            $ops[] = "BT\n1 1 1 rg\n/F1 8 Tf\n190 721 Td (" . $e("OFFICE OF STUDENT AFFAIRS & SERVICES (OSAS)") . ") Tj\nET";
            
            $ops[] = "BT\n0.1 0.15 0.2 rg\n/F2 11 Tf\n36 696 Td (" . $e("OFFICIAL INTERNAL UNIT CHARTER & WORK-STUDY STATION DESIGNATION") . ") Tj";
            $ops[] = "/F1 8.5 Tf\n360 0 Td (" . $e("Ref: " . $accreditation) . ") Tj\nET";
            
            $ops[] = "BT\n0.3 0.35 0.4 rg\n/F1 8 Tf\n36 684 Td (" . $e("Academic Year 2026-2027 · Institutional On-Campus Employment Authorization") . ") Tj";
            $ops[] = "/F1 8 Tf\n410 0 Td (" . $e("Effective: October 22, 2026") . ") Tj\nET";
            
            $ops[] = "0.75 0.80 0.85 RG\n0.75 w";
            $ops[] = "36 490 540 180 re S";
            $ops[] = "0.93 0.95 0.97 rg";
            $ops[] = "36.5 648 539 21.5 re f";
            
            $ops[] = "BT\n0.15 0.25 0.35 rg\n/F2 8.5 Tf\n44 654 Td (" . $e("DESIGNATED INTERNAL CAMPUS UNIT SPECIFICATIONS") . ") Tj\nET";
            
            $fields = [
                ['Authorized Department / Station:', $name],
                ['Administrative Operating Division:', $dept],
                ['Official Physical Station Location:', $loc],
                ['Supervising Administrator / Chair:', $contact],
                ['Authorized Student Compensation:', $rate . ' (KLD Work-Study Subsidized Fund)'],
                ['Institutional Accreditation Code:', $accreditation . ' · STATUS: VALID & ACTIVE']
            ];
            
            $yF = 630;
            foreach ($fields as [$label, $val]) {
                $ops[] = "BT\n0.2 0.25 0.3 rg\n/F2 8.5 Tf\n45 {$yF} Td (" . $e($label) . ") Tj\nET";
                $ops[] = "BT\n0.1 0.1 0.1 rg\n/F1 8.5 Tf\n220 {$yF} Td (" . $e($val) . ") Tj\nET";
                $yF -= 22;
            }
            
            $ops[] = "0.80 0.85 0.90 RG\n0.5 w";
            $ops[] = "36 340 540 130 re S";
            $ops[] = "0.97 0.98 0.99 rg";
            $ops[] = "36.5 340.5 539 129 re f";
            
            $ops[] = "BT\n0.08 0.38 0.22 rg\n/F2 8.5 Tf\n45 450 Td (" . $e("WORK-STUDY STUDENT ASSISTANT SUPERVISION MANDATE") . ") Tj\nET";
            
            $mandateLines = [
                "1. Compliance with Republic Act No. 10931 and City of Dasmariñas Student Welfare Guidelines.",
                "2. Student assistants must not be assigned to duty during their official matriculated lecture/laboratory hours.",
                "3. Maximum allowable duty hours: 20 hours per week during regular academic terms.",
                "4. Supervisors are required to verify timesheets and submit monthly performance evaluations to OSAS.",
                "5. Stations provide hands-on mentorship, skills training, and official Certificate of Completion upon term exit."
            ];
            $yM = 432;
            foreach ($mandateLines as $ml) {
                $ops[] = "BT\n0.25 0.30 0.35 rg\n/F1 8 Tf\n45 {$yM} Td (" . $e($ml) . ") Tj\nET";
                $yM -= 16;
            }
            
            $ops[] = "0.08 0.38 0.22 RG\n1 w";
            $ops[] = "360 215 216 100 re S";
            $ops[] = "BT\n0.08 0.38 0.22 rg\n/F2 7.5 Tf\n372 298 Td (" . $e("KOLEHIYO NG LUNGSOD NG DASMARINAS") . ") Tj\nET";
            $ops[] = "BT\n0.1 0.1 0.1 rg\n/F2 9 Tf\n372 278 Td (" . $e("DR. MARLON ALCANTARA, Ed.D.") . ") Tj\nET";
            $ops[] = "BT\n0.3 0.3 0.3 rg\n/F1 8 Tf\n372 264 Td (" . $e("Dean, Office of Student Affairs & Services") . ") Tj\nET";
            $ops[] = "BT\n0.08 0.38 0.22 rg\n/F2 7 Tf\n372 230 Td (" . $e("AUTHENTICATED INTERNAL CHARTER") . ") Tj\nET";
            
            $ops[] = "BT\n0.5 0.5 0.5 rg\n/F1 7 Tf\n140 40 Td (" . $e("Kolehiyo ng Lungsod ng Dasmariñas · Administrative Records Directorate · Official Document") . ") Tj\nET";
            return self::buildPdf($ops);
        }
    }
}

// ----------------------------------------------------------------------------
// Curriculum Subjects by Course
// ----------------------------------------------------------------------------
$curriculum_subjects = [
    'BS Information Systems (BSIS)' => [
        ['IS-201', 'Enterprise Architecture & Governance', 3.0, 'M/Th 08:00 AM - 10:00 AM'],
        ['IS-202', 'Database Administration & Big Data SQL', 3.0, 'T/F 10:00 AM - 12:00 NN'],
        ['IS-203', 'Systems Analysis, Modeling & Design', 3.0, 'W/S 01:00 PM - 03:00 PM'],
        ['IS-204', 'Cloud Infrastructure & Linux Systems', 3.0, 'M/Th 01:00 PM - 03:00 PM'],
        ['GE-105', 'Purposive Communication in Business', 3.0, 'T/F 08:00 AM - 09:30 AM'],
        ['GE-108', 'Ethics and Professional Accountability', 3.0, 'W 08:00 AM - 11:00 AM']
    ],
    'BS Computer Science (BSCS)' => [
        ['CS-201', 'Advanced Data Structures & Algorithms', 3.0, 'M/Th 09:00 AM - 11:00 AM'],
        ['CS-202', 'Operating Systems Theory & Architecture', 3.0, 'T/F 01:00 PM - 03:00 PM'],
        ['CS-203', 'Automata Theory and Formal Languages', 3.0, 'W/S 08:00 AM - 10:00 AM'],
        ['CS-204', 'Software Engineering & Microservices', 3.0, 'M/Th 02:00 PM - 04:00 PM'],
        ['GE-106', 'Art Appreciation & Digital Aesthetics', 3.0, 'T/F 10:30 AM - 12:00 NN'],
        ['MATH-21', 'Linear Algebra & Discrete Computations', 3.0, 'W 01:00 PM - 04:00 PM']
    ],
    'BS Data Science (BSDS)' => [
        ['DS-201', 'Statistical Inference & Machine Learning', 3.0, 'M/Th 08:30 AM - 10:30 AM'],
        ['DS-202', 'Data Mining & Python Engineering (Pandas)', 3.0, 'T/F 01:00 PM - 03:00 PM'],
        ['DS-203', 'Data Visualization & Executive Dashboards', 3.0, 'W 08:00 AM - 11:00 AM'],
        ['DS-204', 'Relational Databases & Data Warehousing', 3.0, 'T/F 09:00 AM - 11:00 AM'],
        ['GE-107', 'Science, Technology, and Modern Society', 3.0, 'M/Th 01:00 PM - 02:30 PM']
    ],
    'BS Civil Engineering (BSCE)' => [
        ['CE-301', 'Structural Theory & Finite Element Analysis', 4.0, 'M/Th 08:00 AM - 11:00 AM'],
        ['CE-302', 'Reinforced Concrete Design & Lab Assays', 4.0, 'T/F 01:00 PM - 04:00 PM'],
        ['CE-303', 'Geotechnical Engineering & Soil Mechanics', 3.0, 'W 08:00 AM - 12:00 NN'],
        ['CE-304', 'Hydraulics & Water Resources Engineering', 3.0, 'M/Th 01:00 PM - 03:00 PM'],
        ['CE-305', 'Quantity Surveying & Cost Estimation', 3.0, 'T/F 09:00 AM - 11:00 AM']
    ],
    'BS Nursing (BSN)' => [
        ['NUR-301', 'Care of Clients with Acute Medical Alterations', 5.0, 'M/W 07:00 AM - 12:00 NN'],
        ['NUR-302', 'Clinical Practicum & Hospital Duty (Ward)', 4.0, 'Th/F 07:00 AM - 03:00 PM'],
        ['NUR-303', 'Pharmacology & Safe Drug Administration', 3.0, 'T 08:00 AM - 12:00 NN'],
        ['NUR-304', 'Community Health Nursing Immersion II', 3.0, 'S 08:00 AM - 02:00 PM'],
        ['NUR-305', 'Nursing Leadership, Management & Ethics', 2.0, 'T 01:00 PM - 03:30 PM']
    ],
    'BS Medical Laboratory Science (BSMLS)' => [
        ['MLS-201', 'Clinical Chemistry I & Diagnostic Assays', 4.0, 'M/Th 08:00 AM - 12:00 NN'],
        ['MLS-202', 'Hematology I & Complete Blood Counts', 4.0, 'T/F 08:00 AM - 12:00 NN'],
        ['MLS-203', 'Clinical Parasitology & Stool Diagnostics', 3.0, 'W 08:00 AM - 12:00 NN'],
        ['MLS-204', 'Clinical Microscopy & Urinalysis', 3.0, 'T/F 01:30 PM - 04:30 PM'],
        ['GE-105', 'Purposive Technical Communication', 3.0, 'M/Th 01:30 PM - 03:00 PM']
    ],
    'BS Midwifery (BSM)' => [
        ['MID-201', 'Care of the Mother and Child in Normal Labor', 4.0, 'M/Th 08:00 AM - 12:00 NN'],
        ['MID-202', 'Clinical Field Duty: Lying-In Clinic Assays', 4.0, 'T/F 08:00 AM - 04:00 PM'],
        ['MID-203', 'Neonatal Resuscitation & Newborn Health', 3.0, 'W 08:00 AM - 12:00 NN'],
        ['MID-204', 'Community Health & Maternal Counseling', 3.0, 'M/Th 01:00 PM - 03:00 PM']
    ],
    'BS Life Sciences (BSLS)' => [
        ['LS-201', 'General Microbiology & Sterile Culturing', 4.0, 'M/Th 08:00 AM - 12:00 NN'],
        ['LS-202', 'Cellular & Molecular Genetics Laboratory', 4.0, 'T/F 08:00 AM - 12:00 NN'],
        ['LS-203', 'Plant Biology & Ecology Field Surveying', 3.0, 'W 08:00 AM - 12:00 NN'],
        ['GE-107', 'Science, Environmental Systems & Society', 3.0, 'T/F 01:30 PM - 03:00 PM']
    ],
    'BS Psychology (BSP)' => [
        ['PSY-201', 'Psychological Assessment & Psychometrics', 3.0, 'M/Th 09:00 AM - 11:00 AM'],
        ['PSY-202', 'Experimental Psychology & Lab Inquiries', 4.0, 'T/F 08:00 AM - 11:30 AM'],
        ['PSY-203', 'Theories of Personality & Human Behavior', 3.0, 'W 08:00 AM - 11:00 AM'],
        ['PSY-204', 'Industrial/Organizational Psychology', 3.0, 'M/Th 01:00 PM - 03:00 PM']
    ],
    'BS Social Work (BSSW)' => [
        ['SW-201', 'Social Work Practice with Individuals & Families', 3.0, 'M/Th 08:30 AM - 10:30 AM'],
        ['SW-202', 'Social Welfare Policies, Programs & Services', 3.0, 'T/F 09:00 AM - 11:00 AM'],
        ['SW-203', 'Community Immersion & Participatory Organizing', 4.0, 'W/S 08:00 AM - 01:00 PM'],
        ['SW-204', 'Social Work Statistics & Social Data Inquiry', 3.0, 'T/F 01:30 PM - 03:30 PM']
    ]
];

$skills_by_course = [
    'BS Information Systems (BSIS)' => ['Systems Analysis', 'Database SQL', 'Enterprise Architecture', 'Web Development (PHP/JS)', 'Technical Support', 'IT Project Management'],
    'BS Computer Science (BSCS)' => ['Algorithm Design', 'Python & C++', 'Data Structures', 'Git & Linux', 'REST API Architecture', 'Backend Engineering'],
    'BS Data Science (BSDS)' => ['Python (Pandas, NumPy)', 'SQL Data Mining', 'Statistical Modeling', 'Tableau & PowerBI', 'Machine Learning Basics', 'Data Cleansing'],
    'BS Civil Engineering (BSCE)' => ['AutoCAD Drafting', 'Structural Analysis', 'Quantity Surveying', 'Site Quality Inspection', 'Material Testing', 'Construction Supervision'],
    'BS Nursing (BSN)' => ['Vital Signs Monitoring', 'Patient Assessment', 'Medication Administration Support', 'Clinical Triage', 'Basic Life Support (BLS)', 'Medical Charting'],
    'BS Medical Laboratory Science (BSMLS)' => ['Phlebotomy & Sample Prep', 'Hematology Analysis', 'Clinical Chemistry Assays', 'Autoclave & Lab Safety', 'Microscopic Examination'],
    'BS Midwifery (BSM)' => ['Prenatal Assessment', 'Labor Monitoring Assistance', 'Neonatal Vital Signs', 'Community Health Surveying', 'Maternal Health Education'],
    'BS Life Sciences (BSLS)' => ['Laboratory Specimen Prep', 'Microbiology Culturing', 'Field Ecological Surveying', 'Spectrophotometry', 'Scientific Research Writing'],
    'BS Psychology (BSP)' => ['Psychological Testing Assistance', 'Interviewing Techniques', 'HR Recruitment Screening', 'Behavioral Observation', 'Statistical Analysis (SPSS)'],
    'BS Social Work (BSSW)' => ['Case Study Documentation', 'Community Organizing', 'Social Welfare Assistance Intake', 'Crisis Intervention Support', 'Field Assessment']
];

$projects_by_course = [
    'BS Information Systems (BSIS)' => [
        ['title' => 'Campus Facility Reservation System', 'description' => 'Architected a normalized MySQL schema and responsive web UI for automated room bookings.'],
        ['title' => 'Institutional Asset Tracker', 'description' => 'Developed barcode inventory tracking system reducing equipment audit cycles by 40%.']
    ],
    'BS Computer Science (BSCS)' => [
        ['title' => 'Algorithmic Graph Pathfinder for Campus Logistics', 'description' => 'Implemented Dijkstra and A* pathfinding models to optimize building navigation.'],
        ['title' => 'High-Concurrency In-Memory Key-Value Cache', 'description' => 'Built lightweight multithreaded caching service with LRU eviction policy.']
    ],
    'BS Data Science (BSDS)' => [
        ['title' => 'Student Attrition & Enrollment Predictive Model', 'description' => 'Cleaned historical academic records using Pandas and trained Random Forest classifier.'],
        ['title' => 'Cavite Local Commodity Price Index Scraper', 'description' => 'Automated daily price monitoring pipeline using BeautifulSoup and SQLite.']
    ],
    'BS Civil Engineering (BSCE)' => [
        ['title' => 'Structural Stress Analysis of Multi-Story Campus Annex', 'description' => 'Calculated dead/live load distributions and seismic shear moments using AutoCAD and ETABS.'],
        ['title' => 'Sustainable Geopolymer Concrete Aggregate Testing', 'description' => 'Conducted compressive cylinder testing on eco-friendly cement replacement mixtures.']
    ],
    'BS Nursing (BSN)' => [
        ['title' => 'Barangay Maternal Health Community Intervention', 'description' => 'Administered prenatal vitals screenings and conducted infant nutrition seminars for 85 mothers.'],
        ['title' => 'Emergency Room Pediatric Triage Protocol Review', 'description' => 'Documented clinical charting workflows during tertiary hospital rotations.']
    ],
    'BS Medical Laboratory Science (BSMLS)' => [
        ['title' => 'Clinical Blood Specimen Centrifugation Quality Audit', 'description' => 'Evaluated pre-analytical hemolyzation rates across routine outpatient phlebotomy draws.'],
        ['title' => 'Antimicrobial Susceptibility Profiling of Enteric Isolates', 'description' => 'Performed Kirby-Bauer disk diffusion assays on clinical laboratory bacterial cultures.']
    ],
    'BS Midwifery (BSM)' => [
        ['title' => 'Community Prenatal Health Record Digitization', 'description' => 'Cataloged maternal care logs across primary rural health units in Dasmariñas.'],
        ['title' => 'Postpartum Neonatal Care Protocol Simulation', 'description' => 'Executed newborn screening, APGAR scoring, and thermal care workflows.']
    ],
    'BS Life Sciences (BSLS)' => [
        ['title' => 'Riparian Ecosystem Flora Survey of Ylang-Ylang River', 'description' => 'Cataloged 45 riparian plant specimens and measured dissolved oxygen levels.'],
        ['title' => 'Bacterial Growth Curve Kinetics under Thermal Stress', 'description' => 'Measured spectrophotometric optical density of Bacillus subtilis over 24-hour cycles.']
    ],
    'BS Psychology (BSP)' => [
        ['title' => 'Work-Study Cognitive Load & Academic Resiliency Survey', 'description' => 'Administered psychometric questionnaires to 120 student assistants; processed correlations in SPSS.'],
        ['title' => 'Corporate Workplace Well-Being Onboarding Assessment', 'description' => 'Developed structured behavioral interview guidelines for student applicant screening.']
    ],
    'BS Social Work (BSSW)' => [
        ['title' => 'Comprehensive Family Intake Assessment Dossier', 'description' => 'Conducted in-depth social case studies for indigent beneficiaries under local municipal welfare programs.'],
        ['title' => 'Youth Leadership Community Development Workshop', 'description' => 'Facilitated participatory needs assessments across three urban-poor barangays.']
    ]
];

// ----------------------------------------------------------------------------
// Candidate Test Accounts Specification (20 Accounts)
// ----------------------------------------------------------------------------
$new_students = [
    [
        'id' => 1001,
        'name' => 'Joshua Gabriel Alcantara',
        'email' => 'joshua.alcantara1001@gmail.com',
        'phone' => '+63 920 411 1001',
        'dept' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Information Systems (BSIS)',
        'year' => '1st Year',
        'sid' => '2026-1-000301',
        'sex' => 'Male',
        'age' => 18,
        'gwa' => '1.25',
        'created_at' => '2026-10-22 19:40:00'
    ],
    [
        'id' => 1002,
        'name' => 'Alyssa Mae Bautista',
        'email' => 'alyssa.bautista1002@gmail.com',
        'phone' => '+63 920 411 1002',
        'dept' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Computer Science (BSCS)',
        'year' => '1st Year',
        'sid' => '2026-1-000302',
        'sex' => 'Female',
        'age' => 18,
        'gwa' => '1.18',
        'created_at' => '2026-10-22 19:35:00'
    ],
    [
        'id' => 1003,
        'name' => 'Christian Dave Mendoza',
        'email' => 'christian.mendoza1003@gmail.com',
        'phone' => '+63 920 411 1003',
        'dept' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Data Science (BSDS)',
        'year' => '2nd Year',
        'sid' => '2025-1-000303',
        'sex' => 'Male',
        'age' => 19,
        'gwa' => '1.40',
        'created_at' => '2026-10-22 19:20:00'
    ],
    [
        'id' => 1004,
        'name' => 'Samantha Nicole Reyes',
        'email' => 'samantha.reyes1004@gmail.com',
        'phone' => '+63 920 411 1004',
        'dept' => 'Institute of Engineering (IE)',
        'course' => 'BS Civil Engineering (BSCE)',
        'year' => '2nd Year',
        'sid' => '2025-2-000304',
        'sex' => 'Female',
        'age' => 19,
        'gwa' => '1.32',
        'created_at' => '2026-10-22 19:10:00'
    ],
    [
        'id' => 1005,
        'name' => 'John Patrick Ramos',
        'email' => 'john.ramos1005@gmail.com',
        'phone' => '+63 920 411 1005',
        'dept' => 'Institute of Nursing (IN)',
        'course' => 'BS Nursing (BSN)',
        'year' => '3rd Year',
        'sid' => '2024-1-000305',
        'sex' => 'Male',
        'age' => 20,
        'gwa' => '1.45',
        'created_at' => '2026-10-22 19:00:00'
    ],
    [
        'id' => 1006,
        'name' => 'Bea Bianca Corpuz',
        'email' => 'bea.corpuz1006@gmail.com',
        'phone' => '+63 920 411 1006',
        'dept' => 'Institute of Medical Laboratory Science (IMLS)',
        'course' => 'BS Medical Laboratory Science (BSMLS)',
        'year' => '3rd Year',
        'sid' => '2024-2-000306',
        'sex' => 'Female',
        'age' => 20,
        'gwa' => '1.28',
        'created_at' => '2026-10-22 18:45:00'
    ],
    [
        'id' => 1007,
        'name' => 'Mark Anthony Navarro',
        'email' => 'mark.navarro1007@gmail.com',
        'phone' => '+63 920 411 1007',
        'dept' => 'Institute of Midwifery (IM)',
        'course' => 'BS Midwifery (BSM)',
        'year' => '4th Year',
        'sid' => '2023-1-000307',
        'sex' => 'Male',
        'age' => 21,
        'gwa' => '1.50',
        'created_at' => '2026-10-22 18:30:00'
    ],
    [
        'id' => 1008,
        'name' => 'Stephanie Joy Santos',
        'email' => 'stephanie.santos1008@gmail.com',
        'phone' => '+63 920 411 1008',
        'dept' => 'Institute of Science and Mathematics (ISM)',
        'course' => 'BS Life Sciences (BSLS)',
        'year' => '2nd Year',
        'sid' => '2025-1-000308',
        'sex' => 'Female',
        'age' => 19,
        'gwa' => '1.35',
        'created_at' => '2026-10-22 18:15:00'
    ],
    [
        'id' => 1009,
        'name' => 'Kevin Paul Castaneda',
        'email' => 'kevin.castaneda1009@gmail.com',
        'phone' => '+63 920 411 1009',
        'dept' => 'Institute of Behavioral Sciences (IBS)',
        'course' => 'BS Psychology (BSP)',
        'year' => '3rd Year',
        'sid' => '2024-1-000309',
        'sex' => 'Male',
        'age' => 20,
        'gwa' => '1.60',
        'created_at' => '2026-10-22 18:00:00'
    ],
    [
        'id' => 1010,
        'name' => 'Danielle Marie Flores',
        'email' => 'danielle.flores1010@gmail.com',
        'phone' => '+63 920 411 1010',
        'dept' => 'Institute of Governance and Development Studies (IGDS)',
        'course' => 'BS Social Work (BSSW)',
        'year' => '2nd Year',
        'sid' => '2025-2-000310',
        'sex' => 'Female',
        'age' => 19,
        'gwa' => '1.30',
        'created_at' => '2026-10-22 17:45:00'
    ],
    [
        'id' => 1011,
        'name' => 'Miguel Angelo De Guzman',
        'email' => 'miguel.deguzman1011@gmail.com',
        'phone' => '+63 920 411 1011',
        'dept' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Information Systems (BSIS)',
        'year' => '2nd Year',
        'sid' => '2025-2-000961',
        'sex' => 'Male',
        'age' => 20,
        'gwa' => '1.42',
        'created_at' => '2026-10-22 17:30:00'
    ],
    [
        'id' => 1012,
        'name' => 'Princess Diana Villanueva',
        'email' => 'princess.villanueva1012@gmail.com',
        'phone' => '+63 920 411 1012',
        'dept' => 'Institute of Nursing (IN)',
        'course' => 'BS Nursing (BSN)',
        'year' => '1st Year',
        'sid' => '2026-1-000312',
        'sex' => 'Female',
        'age' => 18,
        'gwa' => '1.15',
        'created_at' => '2026-10-22 17:15:00'
    ],
    [
        'id' => 1013,
        'name' => 'Rafael Luis Mercado',
        'email' => 'rafael.mercado1013@gmail.com',
        'phone' => '+63 920 411 1013',
        'dept' => 'Institute of Engineering (IE)',
        'course' => 'BS Civil Engineering (BSCE)',
        'year' => '3rd Year',
        'sid' => '2024-1-000313',
        'sex' => 'Male',
        'age' => 21,
        'gwa' => '1.68',
        'created_at' => '2026-10-22 17:00:00'
    ],
    [
        'id' => 1014,
        'name' => 'Katrina Isabela Gonzales',
        'email' => 'katrina.gonzales1014@gmail.com',
        'phone' => '+63 920 411 1014',
        'dept' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Computer Science (BSCS)',
        'year' => '2nd Year',
        'sid' => '2025-1-000314',
        'sex' => 'Female',
        'age' => 19,
        'gwa' => '1.12',
        'created_at' => '2026-10-22 16:45:00'
    ],
    [
        'id' => 1015,
        'name' => 'Jan Carlo Valdez',
        'email' => 'jan.valdez1015@gmail.com',
        'phone' => '+63 920 411 1015',
        'dept' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Information Systems (BSIS)',
        'year' => '4th Year',
        'sid' => '2023-1-000315',
        'sex' => 'Male',
        'age' => 22,
        'gwa' => '1.38',
        'created_at' => '2026-10-22 16:30:00'
    ]
];

$new_employers = [
    [
        'id' => 1016,
        'name' => 'Cavite Advanced Robotics & Mechatronics Hub',
        'email' => 'hr@cavitexrobotics.ph',
        'phone' => '+63 46 477 1016',
        'type' => 'approved_partner',
        'accred_no' => 'MOA-2026-IND136',
        'loc' => 'FCIE Special Economic Zone, Governor\'s Drive, Dasmariñas City',
        'contact' => 'Engr. Leandro Soriano (Engineering Director)',
        'tin' => '009-418-522-000',
        'industry' => 'Industrial Robotics & Automation Engineering',
        'created_at' => '2026-10-22 16:15:00'
    ],
    [
        'id' => 1017,
        'name' => 'Dasmariñas Diagnostic Specialists & Molecular Lab',
        'email' => 'careers@dasmadiagnostics.ph',
        'phone' => '+63 46 477 1017',
        'type' => 'approved_partner',
        'accred_no' => 'MOA-2026-IND137',
        'loc' => 'Salawag-Paliparan Road, Dasmariñas City',
        'contact' => 'Dr. Michelle Arboleda (Laboratory Head)',
        'tin' => '008-721-394-000',
        'industry' => 'Clinical Pathology & Molecular Diagnostics',
        'created_at' => '2026-10-22 16:00:00'
    ],
    [
        'id' => 1018,
        'name' => 'South Luzon Green Energy & Sustainable Engineering Corp.',
        'email' => 'talent@southluzongreen.ph',
        'phone' => '+63 46 477 1018',
        'type' => 'approved_partner',
        'accred_no' => 'MOA-2026-IND138',
        'loc' => 'Aguinaldo Highway, Zone 4, Dasmariñas City',
        'contact' => 'Arch. Emmanuel Pineda (Operations Manager)',
        'tin' => '007-934-118-000',
        'industry' => 'Renewable Solar Power & Environmental Engineering',
        'created_at' => '2026-10-22 15:45:00'
    ],
    [
        'id' => 1019,
        'name' => 'First Cavite Global Supply Chain & Logistics Center',
        'email' => 'recruitment@fcielogistics.ph',
        'phone' => '+63 46 477 1019',
        'type' => 'approved_partner',
        'accred_no' => 'MOA-2026-IND139',
        'loc' => 'Golden Mile Business Park, Carmona-Dasmariñas Corridor',
        'contact' => 'Mr. Dominic Ocampo (HR Senior Lead)',
        'tin' => '006-258-741-000',
        'industry' => 'E-Commerce Logistics & Cold Chain Supply Operations',
        'created_at' => '2026-10-22 15:30:00'
    ],
    [
        'id' => 1020,
        'name' => 'KLD Alumni Scholarship & Educational Aid Foundation',
        'email' => 'scholarships.kld.foundation@gmail.com',
        'phone' => '+63 46 477 1020',
        'type' => 'approved_partner',
        'accred_no' => 'MOA-2026-IND140',
        'loc' => 'City Hall Compound, Palapala, Dasmariñas City',
        'contact' => 'Atty. Cristina Valenzuela (Executive Director)',
        'tin' => '005-182-943-000',
        'industry' => 'Educational Endowment & Youth Scholarship Aid',
        'created_at' => '2026-10-22 15:15:00'
    ]
];

echo "=================================================================\n";
echo "GENERATING 20 FRESH VERIFICATION QUEUE TEST CANDIDATES\n";
echo "=================================================================\n\n";

$pdo = get_db_connection();
$pdo->beginTransaction();

$pwdStudent = password_hash('Student2026!', PASSWORD_BCRYPT, ['cost' => 10]);
$pwdPartner = password_hash('Partner2026!', PASSWORD_BCRYPT, ['cost' => 10]);

$stmtUser = $pdo->prepare("
    INSERT INTO `users` (
        `id`, `email`, `password`, `role`, `name`, `phone`, `status`,
        `is_archived`, `verification_status`, `is_email_verified`, `created_at`, `updated_at`
    ) VALUES (
        :id, :email, :password, :role, :name, :phone, 'active',
        0, 'pending_approval', 1, :created_at, :updated_at
    )
    ON DUPLICATE KEY UPDATE
        `verification_status` = 'pending_approval',
        `status` = 'active',
        `is_archived` = 0,
        `name` = VALUES(`name`),
        `phone` = VALUES(`phone`),
        `updated_at` = VALUES(`updated_at`)
");

$stmtStudent = $pdo->prepare("
    INSERT INTO `student_profiles` (
        `user_id`, `student_id`, `department`, `course`, `year_level`, `sex`,
        `birthdate`, `age`, `availability`, `verification_status`, `rejection_reason`,
        `registration_proof`, `resume_file`, `created_at`, `updated_at`
    ) VALUES (
        :user_id, :student_id, :department, :course, :year_level, :sex,
        :birthdate, :age, :availability, 'pending_approval', NULL,
        :registration_proof, :resume_file, :created_at, :updated_at
    )
    ON DUPLICATE KEY UPDATE
        `verification_status` = 'pending_approval',
        `rejection_reason` = NULL,
        `department` = VALUES(`department`),
        `course` = VALUES(`course`),
        `year_level` = VALUES(`year_level`),
        `registration_proof` = VALUES(`registration_proof`),
        `resume_file` = VALUES(`resume_file`),
        `updated_at` = VALUES(`updated_at`)
");

$stmtEmployer = $pdo->prepare("
    INSERT INTO `employer_profiles` (
        `user_id`, `employer_type`, `organization_name`, `office_location`,
        `contact_person`, `accreditation_number`, `verification_status`, `rejection_reason`,
        `business_permit`, `created_at`, `updated_at`
    ) VALUES (
        :user_id, :employer_type, :organization_name, :office_location,
        :contact_person, :accreditation_number, 'pending_approval', NULL,
        :business_permit, :created_at, :updated_at
    )
    ON DUPLICATE KEY UPDATE
        `verification_status` = 'pending_approval',
        `rejection_reason` = NULL,
        `employer_type` = VALUES(`employer_type`),
        `organization_name` = VALUES(`organization_name`),
        `office_location` = VALUES(`office_location`),
        `contact_person` = VALUES(`contact_person`),
        `accreditation_number` = VALUES(`accreditation_number`),
        `business_permit` = VALUES(`business_permit`),
        `updated_at` = VALUES(`updated_at`)
");

$new_users_json = [];

// 1. Process 15 Students
echo ">>> Generating 15 Realistic Student Candidates (IDs 1001-1015)...\n";
foreach ($new_students as $st) {
    $uid = $st['id'];
    $sid = $st['sid'];
    $course = $st['course'];
    
    // Generate e-COR PDF
    $corFile = "cor_{$sid}.pdf";
    $corPath = __DIR__ . "/../uploads/proofs/{$corFile}";
    $subjects = $curriculum_subjects[$course] ?? $curriculum_subjects['BS Information Systems (BSIS)'];
    $corPdf = KldPdfBuilder::generateCor([
        'student_id' => $sid,
        'name'       => $st['name'],
        'department' => $st['dept'],
        'course'     => $course,
        'year_level' => $st['year'],
        'gwa'        => $st['gwa']
    ], $subjects);
    file_put_contents($corPath, $corPdf);
    
    // Generate Resume PDF
    $resumeFile = "resume_{$sid}.pdf";
    $resumePath = __DIR__ . "/../uploads/resumes/{$resumeFile}";
    $skills = $skills_by_course[$course] ?? ['Problem Solving', 'Communication', 'Research'];
    $projects = $projects_by_course[$course] ?? [
        ['title' => 'Term Academic Capstone Project', 'description' => 'Applied course concepts and domain-specific methodologies to address operational challenges.']
    ];
    $resumePdf = KldPdfBuilder::generateResume([
        'student_id'    => $sid,
        'name'          => $st['name'],
        'email'         => $st['email'],
        'phone'         => $st['phone'],
        'department'    => $st['dept'],
        'course'        => $course,
        'year_level'    => $st['year'],
        'gwa'           => $st['gwa'],
        'avail_summary' => 'Mon, Wed, Fri (1:00 PM – 5:00 PM) · 12-15 hrs/wk'
    ], $skills, $projects);
    file_put_contents($resumePath, $resumePdf);
    
    // Insert User
    $stmtUser->execute([
        ':id' => $uid,
        ':email' => $st['email'],
        ':password' => $pwdStudent,
        ':role' => 'student',
        ':name' => $st['name'],
        ':phone' => $st['phone'],
        ':created_at' => $st['created_at'],
        ':updated_at' => $st['created_at']
    ]);
    
    // Insert Student Profile
    $stmtStudent->execute([
        ':user_id' => $uid,
        ':student_id' => $sid,
        ':department' => $st['dept'],
        ':course' => $course,
        ':year_level' => $st['year'],
        ':sex' => $st['sex'],
        ':birthdate' => date('Y-m-d', strtotime("-{$st['age']} years")),
        ':age' => $st['age'],
        ':availability' => json_encode(['Mon - Afternoon (1PM–5PM)', 'Wed - Afternoon (1PM–5PM)']),
        ':registration_proof' => "uploads/proofs/{$corFile}",
        ':resume_file' => "uploads/resumes/{$resumeFile}",
        ':created_at' => $st['created_at'],
        ':updated_at' => $st['created_at']
    ]);
    
    $new_users_json[$uid] = [
        'id' => $uid,
        'email' => $st['email'],
        'password' => $pwdStudent,
        'role' => 'student',
        'name' => $st['name'],
        'student_id' => $sid,
        'department' => $st['dept'],
        'course' => $course,
        'year_level' => $st['year'],
        'sex' => $st['sex'],
        'birthdate' => date('Y-m-d', strtotime("-{$st['age']} years")),
        'age' => $st['age'],
        'phone' => $st['phone'],
        'status' => 'active',
        'employer_type' => null,
        'organization_name' => null,
        'office_location' => null,
        'contact_person' => null,
        'accreditation_number' => null,
        'verification_status' => 'pending_approval',
        'rejection_reason' => null,
        'business_permit' => null,
        'registration_proof' => "uploads/proofs/{$corFile}",
        'availability' => ['Mon - Afternoon (1PM–5PM)', 'Wed - Afternoon (1PM–5PM)'],
        'created_at' => $st['created_at'],
        'updated_at' => $st['created_at'],
        'bio' => "Bona fide KLD {$course} student. Awaiting institutional credential verification for work-study eligibility.",
        'skills' => $skills,
        'resume' => "uploads/resumes/{$resumeFile}",
        'resume_file' => "uploads/resumes/{$resumeFile}",
        'proof_file' => "uploads/proofs/{$corFile}",
        'is_archived' => 0,
        'is_email_verified' => 1
    ];
}

// 2. Process 5 Employers
echo ">>> Generating 5 Realistic Employer / Campus Unit Candidates (IDs 1016-1020)...\n";
foreach ($new_employers as $emp) {
    $uid = $emp['id'];
    $type = $emp['type'];
    
    $permitFile = "permit_partner_{$uid}.pdf";
    $permitPath = __DIR__ . "/../uploads/permits/{$permitFile}";
    
    if ($type === 'university_office') {
        $pdf = KldPdfBuilder::generateUnivCharter($emp['name'], 'Campus Student Support', $emp['loc'], $emp['contact'], $emp['rate'] ?? '₱85.00 / hour', $emp['accred_no']);
    } else {
        $pdf = KldPdfBuilder::generateBirForm2303($emp['name'], $emp['tin'], $emp['loc'], $emp['industry'], $uid, 'pending_approval', null);
    }
    file_put_contents($permitPath, $pdf);
    
    // Insert User
    $stmtUser->execute([
        ':id' => $uid,
        ':email' => $emp['email'],
        ':password' => $pwdPartner,
        ':role' => 'employer',
        ':name' => $emp['name'],
        ':phone' => $emp['phone'],
        ':created_at' => $emp['created_at'],
        ':updated_at' => $emp['created_at']
    ]);
    
    // Insert Employer Profile
    $stmtEmployer->execute([
        ':user_id' => $uid,
        ':employer_type' => $type,
        ':organization_name' => $emp['name'],
        ':office_location' => $emp['loc'],
        ':contact_person' => $emp['contact'],
        ':accreditation_number' => $emp['accred_no'],
        ':business_permit' => "uploads/permits/{$permitFile}",
        ':created_at' => $emp['created_at'],
        ':updated_at' => $emp['created_at']
    ]);
    
    $new_users_json[$uid] = [
        'id' => $uid,
        'email' => $emp['email'],
        'password' => $pwdPartner,
        'role' => 'employer',
        'name' => $emp['name'],
        'student_id' => null,
        'department' => $emp['name'],
        'course' => null,
        'year_level' => null,
        'sex' => null,
        'birthdate' => null,
        'age' => null,
        'phone' => $emp['phone'],
        'status' => 'active',
        'employer_type' => $type,
        'organization_name' => $emp['name'],
        'office_location' => $emp['loc'],
        'contact_person' => $emp['contact'],
        'accreditation_number' => $emp['accred_no'],
        'verification_status' => 'pending_approval',
        'rejection_reason' => null,
        'business_permit' => "uploads/permits/{$permitFile}",
        'registration_proof' => null,
        'availability' => null,
        'created_at' => $emp['created_at'],
        'updated_at' => $emp['created_at'],
        'bio' => "Accredited partner entity awaiting registration verification.",
        'skills' => [],
        'resume' => null,
        'resume_file' => null,
        'proof_file' => "uploads/permits/{$permitFile}",
        'is_archived' => 0,
        'is_email_verified' => 1
    ];
}

$pdo->commit();
echo ">>> MySQL Datastore successfully updated with 20 pending verification accounts!\n";

// 3. Synchronize to data/users.json
echo ">>> Synchronizing to data/users.json and data/seeds/demo/users.json...\n";
$usersFile = __DIR__ . '/../data/users.json';
$existingJson = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$jsonMap = [];
foreach ($existingJson as $item) {
    $jsonMap[$item['id']] = $item;
}
foreach ($new_users_json as $uid => $row) {
    $jsonMap[$uid] = $row;
}
ksort($jsonMap);
$mergedUsers = array_values($jsonMap);

file_put_contents($usersFile, json_encode($mergedUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents(__DIR__ . '/../data/seeds/demo/users.json', json_encode($mergedUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents(__DIR__ . '/../data/seeds/realistic_1k/users.json', json_encode($mergedUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$manifestFile = __DIR__ . '/../data/mock_users_manifest.json';
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if (is_array($manifest)) {
        $st_count = 0;
        $emp_count = 0;
        $adm_count = 0;
        foreach ($mergedUsers as $u) {
            $r = $u['role'] ?? '';
            if ($r === 'student') $st_count++;
            elseif ($r === 'employer') $emp_count++;
            elseif ($r === 'admin') $adm_count++;
        }
        $manifest['total_users'] = count($mergedUsers);
        $manifest['students_count'] = $st_count;
        $manifest['employers_count'] = $emp_count;
        $manifest['admins_count'] = $adm_count;
        file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

echo ">>> Done! 20 fresh verification candidates ready in admin/verification-deck.php.\n";
