<?php
declare(strict_types=1);

/**
 * Campus Job Posting System - Laya Decision Engine Client
 * 
 * Provides human-centered, ML-powered advisory guidance for university supervisors
 * and students evaluating assistantship applications and opportunities.
 * 
 * Core Design Principle:
 * - AI serves as an advisory guide to augment human efficiency.
 * - Laya NEVER makes automated hiring decisions or mutates applicant statuses.
 * - All decision-making authority remains strictly with the human evaluator.
 * - Uses ModernBERT non-autoregressive decision engine via local daemon on port 8100.
 */

if (!defined('LAYA_ENDPOINT')) {
    define('LAYA_ENDPOINT', 'http://127.0.0.1:8100');
}

/**
 * Check if the local Laya decision engine daemon is online.
 * Uses request-scoped static cache and session-level TTL cache to eliminate latency traps.
 *
 * @param float $timeout Connection timeout in seconds
 * @return bool True if daemon is responsive
 */
function laya_is_available(float $timeout = 0.5): bool {
    static $status_cache = null;
    if ($status_cache !== null) {
        return $status_cache;
    }

    $cache_key = 'laya_health';
    $cache_ttl = 60; // seconds
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$cache_key]) && is_array($_SESSION[$cache_key])) {
        $cached = $_SESSION[$cache_key];
        if ((time() - (int)($cached['ts'] ?? 0)) < $cache_ttl) {
            $status_cache = (bool)$cached['online'];
            return $status_cache;
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $timeout,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents(LAYA_ENDPOINT . '/health', false, $ctx);
    $online = (is_string($response) && str_contains($response, 'laya'));

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION[$cache_key] = ['online' => $online, 'ts' => time()];
    }
    $status_cache = $online;
    return $status_cache;
}

/**
 * Fetch human-centric advisory guidance dossier for an applicant.
 * Evaluates semantic skills match, letter specificity, role readiness, and interview probes.
 * Results are cached in session by applicant ID for instantaneous sub-requests.
 *
 * @param array $application Target application record from data/applications.json
 * @param array $job Target requisition record from data/jobs.json
 * @param bool $force_refresh Set true to bypass cache and re-query Laya
 * @return array|null Advisory guidance structure or null if Laya is unavailable
 */
function laya_get_applicant_guidance(array $application, array $job, bool $force_refresh = false): ?array {
    $app_id = $application['id'] ?? null;

    if (!$force_refresh && $app_id && isset($_SESSION['laya_guidance'][$app_id])) {
        if (isset($_SESSION['laya_guidance'][$app_id]['schedule_assessment']['status'])) {
            return $_SESSION['laya_guidance'][$app_id];
        }
    }

    if (!laya_is_available()) {
        return null;
    }

    $payload = json_encode([
        'applicant' => [
            'student_name' => $application['student_name'] ?? 'Candidate',
            'course'       => $application['course'] ?? '',
            'year_level'   => $application['year_level'] ?? '',
            'cover_letter' => $application['cover_letter'] ?? '',
            'availability' => $application['availability'] ?? [],
        ],
        'job' => [
            'title'        => $job['title'] ?? ($application['job_title'] ?? 'Assistantship'),
            'department'   => $job['department'] ?? ($application['department'] ?? 'Campus Office'),
            'description'  => $job['description'] ?? '',
            'requirements' => $job['qualifications'] ?? ($job['requirements'] ?? ''),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 25.0,
            'ignore_errors' => true,
        ],
    ]);

    $raw = @file_get_contents(LAYA_ENDPOINT . '/evaluate-applicant', false, $ctx);
    if (!is_string($raw) || empty($raw)) {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || ($decoded['status'] ?? '') !== 'success') {
        return null;
    }

    $decoded['evaluated_at'] = date('g:i:s A');

    if ($app_id && session_status() === PHP_SESSION_ACTIVE) {
        if (!isset($_SESSION['laya_guidance'])) {
            $_SESSION['laya_guidance'] = [];
        }
        $_SESSION['laya_guidance'][$app_id] = $decoded;
    }

    return $decoded;
}

/**
 * Batch-rank candidates for a specific job using ML composite scoring.
 * Uses agent.predict_batch() on daemon for single-pass evaluation across all applicants.
 *
 * @param array $candidates Array of applicant records
 * @param array $job Requisition record
 * @return array|null Array of ranked items with composite scores or null if offline
 */
function laya_rank_candidates(array $candidates, array $job): ?array {
    if (empty($candidates) || !laya_is_available()) {
        return null;
    }

    $job_id = (int)($job['id'] ?? 0);
    $cache_key = 'laya_rankings_' . $job_id;
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$cache_key]) && is_array($_SESSION[$cache_key])) {
        $cached = $_SESSION[$cache_key];
        if ((time() - (int)($cached['ts'] ?? 0)) < 300) {
            return $cached['rankings'];
        }
    }

    $clean_candidates = [];
    foreach ($candidates as $c) {
        $clean_candidates[] = [
            'id'           => $c['id'] ?? 0,
            'student_name' => $c['student_name'] ?? 'Candidate',
            'course'       => $c['course'] ?? '',
            'year_level'   => $c['year_level'] ?? '',
            'cover_letter' => $c['cover_letter'] ?? '',
            'availability' => $c['availability'] ?? [],
        ];
    }

    $payload = json_encode([
        'candidates' => $clean_candidates,
        'job' => [
            'title'        => $job['title'] ?? '',
            'department'   => $job['department'] ?? '',
            'description'  => $job['description'] ?? '',
            'requirements' => $job['qualifications'] ?? ($job['requirements'] ?? ''),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 15.0,
            'ignore_errors' => true,
        ],
    ]);

    $raw = @file_get_contents(LAYA_ENDPOINT . '/rank-candidates', false, $ctx);
    if (!is_string($raw) || empty($raw)) {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || ($decoded['status'] ?? '') !== 'success') {
        return null;
    }

    $rankings = $decoded['rankings'] ?? [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION[$cache_key] = [
            'ts'       => time(),
            'rankings' => $rankings,
        ];
    }

    return $rankings;
}

/**
 * ML-powered program-to-job fit assessment for student-facing pages.
 * Evaluates degree program relevance and career skill development.
 *
 * @param array $student Viewing student profile
 * @param array $job Target requisition
 * @return array|null Fit assessment structure or null if offline
 */
function laya_assess_student_fit(array $student, array $job): ?array {
    if (empty($student['course']) || !laya_is_available()) {
        return null;
    }

    $job_id = (int)($job['id'] ?? 0);
    $fingerprint = md5(($student['course'] ?? '') . '_' . ($student['year_level'] ?? '') . '_' . $job_id);
    $cache_key = 'laya_student_fit';

    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$cache_key][$fingerprint])) {
        $cached = $_SESSION[$cache_key][$fingerprint];
        if ((time() - (int)($cached['ts'] ?? 0)) < 600) {
            return $cached['data'];
        }
    }

    $payload = json_encode([
        'student' => [
            'course'     => $student['course'] ?? '',
            'year_level' => $student['year_level'] ?? '',
        ],
        'job' => [
            'title'        => $job['title'] ?? '',
            'department'   => $job['department'] ?? '',
            'description'  => $job['description'] ?? '',
            'requirements' => $job['qualifications'] ?? ($job['requirements'] ?? ''),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 8.0,
            'ignore_errors' => true,
        ],
    ]);

    $raw = @file_get_contents(LAYA_ENDPOINT . '/assess-student-fit', false, $ctx);
    if (!is_string($raw) || empty($raw)) {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || ($decoded['status'] ?? '') !== 'success') {
        return null;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        if (!isset($_SESSION[$cache_key])) {
            $_SESSION[$cache_key] = [];
        }
        $_SESSION[$cache_key][$fingerprint] = [
            'ts'   => time(),
            'data' => $decoded,
        ];
        // Keep session cache bounded
        if (count($_SESSION[$cache_key]) > 20) {
            array_shift($_SESSION[$cache_key]);
        }
    }

    return $decoded;
}
