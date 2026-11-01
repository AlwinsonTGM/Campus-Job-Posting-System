<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/services/system-service.php';

$content = sanitize_dispatch_content('<h3 class="h4 fw-bold">Opportunities</h3><p>Join us 🙂 <strong>today</strong>.</p><ul><li>Bring your resume</li></ul>');
foreach (['class="h4 fw-bold"', '<strong>today</strong>', '<li>Bring your resume</li>', '🙂'] as $expected) {
    if (!str_contains($content, $expected)) throw new RuntimeException("Missing supported content: {$expected}");
}
$unsafe = sanitize_dispatch_content('<script>alert(1)</script><p onclick="alert(1)">Hello <a href="javascript:alert(1)">link</a><img src="https://example.com/a.png" onerror="alert(1)"></p>');
foreach (['<script', 'onclick', 'javascript:', 'onerror'] as $forbidden) {
    if (stripos($unsafe, $forbidden) !== false) throw new RuntimeException("Unsafe content retained: {$forbidden}");
}
if (!str_contains(sanitize_dispatch_content("First line\nSecond line"), '<br')) throw new RuntimeException('Plain text line breaks were lost.');
echo "Dispatch content checks passed.\n";
