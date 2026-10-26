<?php
declare(strict_types=1);
// Read-only database and live API checks; no students or profiles are inserted.
require __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
$indexes = $pdo->query("SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'student_profiles' AND NON_UNIQUE = 0 GROUP BY INDEX_NAME HAVING COUNT(*) = 1 AND MAX(COLUMN_NAME) = 'student_id'")->fetchAll();
if (!$indexes) throw new RuntimeException('student_profiles needs a unique student_id index.');
echo "PASS: Database enforces a unique Student ID.\n";
$duplicates = (int)$pdo->query('SELECT COUNT(*) FROM (SELECT LOWER(TRIM(student_id)) FROM student_profiles GROUP BY LOWER(TRIM(student_id)) HAVING COUNT(*) > 1) duplicate_ids')->fetchColumn();
if ($duplicates !== 0) throw new RuntimeException('Duplicate student IDs already exist.');
echo "PASS: No duplicate Student IDs exist.\n";
$existing = $pdo->query('SELECT student_id FROM student_profiles LIMIT 1')->fetchColumn();
if (!$existing) throw new RuntimeException('An existing profile is needed for the duplicate lookup check.');
$base = getenv('AUTH_PREVIEW_URL') ?: 'http://127.0.0.1:8001';
$context = stream_context_create(['http' => ['timeout' => 15]]);
$result = json_decode(file_get_contents($base . '/api/check-account.php?' . http_build_query(['student_id' => $existing]), false, $context), true, 512, JSON_THROW_ON_ERROR);
if (($result['student_id_exists'] ?? null) !== true || ($result['available'] ?? null) !== false) throw new RuntimeException('An existing ID must be unavailable.');
echo "PASS: Live availability API rejects an existing Student ID.\n";
$result = json_decode(file_get_contents($base . '/api/check-account.php?' . http_build_query(['student_id' => ' ' . $existing . ' ']), false, $context), true, 512, JSON_THROW_ON_ERROR);
if (($result['student_id_exists'] ?? null) !== true) throw new RuntimeException('Whitespace must not bypass uniqueness.');
echo "PASS: Whitespace does not bypass the duplicate check.\n";
