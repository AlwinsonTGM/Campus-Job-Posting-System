<?php
/**
 * Campus Job Posting System - Secure AI Robot Chat Gateway
 * 
 * Proxies user prompts from the 3D robot companion to NVIDIA NIM free models.
 * Enforces rate limiting, prompt sanitization, model whitelisting, and auto-fallback.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../includes/ai-config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. POST is required.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check Rate Limiting (Session + IP sliding window)
$rate_status = check_ai_rate_limit();
if (!$rate_status['allowed']) {
    http_response_code(429);
    echo json_encode([
        'status' => 'error',
        'error' => 'RATE_LIMIT_EXCEEDED',
        'code' => 'RATE_LIMIT_EXCEEDED',
        'message' => '🛡️ Anti-Spam Active: To keep AI free and fast for all students, requests are limited to ' . ($rate_status['limit_max'] ?? 10) . ' questions/min. Please cooldown for ' . $rate_status['retry_after'] . 's.',
        'retry_after' => $rate_status['retry_after'],
        'rate_remaining' => 0,
        'rate_limit_max' => $rate_status['limit_max'] ?? 10
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Parse request payload (supports JSON or Form POST)
$raw_body = file_get_contents('php://input');
$json_data = json_decode($raw_body, true);

if (is_array($json_data)) {
    $raw_prompt = $json_data['message'] ?? $json_data['prompt'] ?? '';
    $raw_mode = $json_data['mode'] ?? 'talk';
    $raw_model = $json_data['model'] ?? null;
    $raw_history = $json_data['history'] ?? [];
} else {
    $raw_prompt = $_POST['message'] ?? $_POST['prompt'] ?? '';
    $raw_mode = $_POST['mode'] ?? 'talk';
    $raw_model = $_POST['model'] ?? null;
    $raw_history = (isset($_POST['history']) && is_array($_POST['history'])) ? $_POST['history'] : [];
}

// Ensure session is available to identify user role
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
$user_role = $_SESSION['user']['role'] ?? 'guest';

// Sanitize user prompt
$raw_trimmed = trim((string)$raw_prompt);
$prompt = sanitize_ai_prompt($raw_prompt, 800);
$model = validate_ai_model($raw_model);

// Validate and sanitize sliding conversation history (capped at last 6 messages)
$history = [];
if (is_array($raw_history) && !empty($raw_history)) {
    $sliced_history = array_slice($raw_history, -6);
    foreach ($sliced_history as $item) {
        if (!is_array($item)) continue;
        $role = ($item['role'] ?? '') === 'assistant' ? 'assistant' : (($item['role'] ?? '') === 'user' ? 'user' : '');
        $raw_content = (string)($item['content'] ?? $item['text'] ?? '');
        $clean_content = sanitize_ai_prompt($raw_content, 800);
        if ($role !== '' && $clean_content !== '') {
            $history[] = [
                'role' => $role,
                'content' => $clean_content
            ];
        }
    }
}

if (!empty($raw_trimmed) && empty($prompt)) {
    // User sent tags or stripped control chars (e.g. <script>alert(1)</script>)
    $prompt = 'Disallowed code tags or script injection detected.';
    $detected_intent = 'offtopic';
} elseif (empty($prompt)) {
    $prompt = 'Share a helpful tip about working as a student assistant while keeping high grades in university!';
    $detected_intent = 'faq';
} else {
    // Automatically detect topic and intent from input
    $detected_intent = detect_ai_prompt_intent($raw_trimmed);
}

// Build messages payload with system prompt, history turns, and active prompt
$system_prompt = build_robot_system_prompt($detected_intent, $user_role);
$messages = [
    ['role' => 'system', 'content' => $system_prompt]
];
foreach ($history as $h) {
    $messages[] = $h;
}
$messages[] = ['role' => 'user', 'content' => $prompt];

// Call NVIDIA NIM API with auto-fallback and role-awareness
$result = call_nvidia_nim_chat($messages, $model, ['user_role' => $user_role]);
$model_name = get_model_display_name($result['model']);

echo json_encode([
    'status' => 'success',
    'reply' => $result['reply'],
    'model' => $result['model'],
    'model_name' => $model_name,
    'detected_intent' => $detected_intent,
    'mode' => $detected_intent,
    'latency_ms' => $result['latency_ms'] ?? 0,
    'is_fallback' => $result['is_fallback'] ?? false,
    'notice' => $result['notice'] ?? null,
    'rate_remaining' => $rate_status['remaining'] ?? 0,
    'rate_limit_max' => $rate_status['limit_max'] ?? 10
], JSON_UNESCAPED_UNICODE);
