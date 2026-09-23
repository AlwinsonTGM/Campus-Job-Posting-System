<?php
error_reporting(E_ALL);

$pages = [
    'index.php',
    'about-us.php',
    'faqs.php',
    'login.php',
    'register.php',
    'notifications.php',
    'updates.php',
    'settings.php',
    'verify-email.php',
    'view-resume.php',
    'update-detail.php',
    'forgot-pass.php',
    'reset-password.php',
    'employer/dashboard.php',
    'employer/create-job.php',
    'employer/edit-job.php',
    'employer/applicants.php',
    'employer/review-app.php',
    'employer/updates.php',
    'student/dashboard.php',
    'student/jobs.php',
    'student/job-details.php',
    'student/my-applications.php',
    'student/apply.php',
    'admin/reports.php',
    'admin/users.php',
    'admin/categories.php',
    'admin/updates.php',
    'admin/ai-settings.php'
];

$issues = [];
$passed = 0;

foreach ($pages as $page) {
    $role = 'student';
    if (str_starts_with($page, 'admin/')) {
        $role = 'admin';
    } elseif (str_starts_with($page, 'employer/')) {
        $role = 'employer';
    }

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $testScript = '<?php
        error_reporting(E_ALL);
        ini_set("display_errors", "1");
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["REQUEST_URI"] = "/' . $page . '";
        $_SERVER["PHP_SELF"] = "/' . $page . '";
        $_SERVER["SCRIPT_NAME"] = "/' . $page . '";
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION["user"] = [
            "id" => "1",
            "student_id" => "2024-0001",
            "name" => "Test User",
            "email" => "test@kld.edu.ph",
            "role" => "' . $role . '",
            "department" => "Institute of Computing and Applied Technologies",
            "year_level" => "3rd Year",
            "status" => "active",
            "email_verified" => 1,
            "created_at" => "2026-01-01 00:00:00"
        ];
        
        ob_start();
        try {
            require __DIR__ . "/' . $page . '";
            $out = ob_get_clean();
            echo "OK: " . strlen($out) . " bytes";
        } catch (\Throwable $e) {
            ob_end_clean();
            echo "FATAL: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
        }
    ';

    $process = proc_open('php', $descriptors, $pipes, dirname(__DIR__));
    fwrite($pipes[0], $testScript);
    fclose($pipes[0]);

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $status = proc_close($process);

    $combined = $stdout . "\n" . $stderr;
    $hasFatal = strpos($combined, 'FATAL:') !== false || strpos($combined, 'Fatal error') !== false;
    $hasWarning = preg_match('/(Warning|Notice|Parse error|Deprecated):/i', $combined, $matches);

    if ($hasFatal || $hasWarning || $status !== 0) {
        $issueText = [];
        if ($hasFatal) $issueText[] = 'FATAL ERROR';
        if ($hasWarning) $issueText[] = $matches[0];
        if ($status !== 0) $issueText[] = "Exit code {$status}";
        
        $issues[$page] = [
            'type' => implode(', ', $issueText),
            'output' => trim($combined)
        ];
        echo "❌ {$page}: " . implode(', ', $issueText) . "\n";
        echo "   Detail: " . trim($combined) . "\n";
    } else {
        $passed++;
        echo "✅ {$page}: OK\n";
    }
}

echo "\n============================================\n";
echo "Results: {$passed} passed, " . count($issues) . " had warnings/errors.\n";
if (!empty($issues)) {
    exit(1);
}
