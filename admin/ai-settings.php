<?php
/**
 * Campus Job Posting System - Admin NVIDIA NIM & Robot AI Settings
 * 
 * Allows system administrators to securely configure the NVIDIA API Key,
 * choose default reasoning models, adjust rate limits, and test connectivity.
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/ai-config.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'NVIDIA NIM AI & 3D Robot Configuration';

$error = null;
$test_result = null;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'save_settings';

        if ($action === 'save_settings') {
            $api_key = trim($_POST['nvidia_api_key'] ?? '');
            $default_model = trim($_POST['default_model'] ?? 'openai/gpt-oss-20b');
            $fallback_model = trim($_POST['fallback_model'] ?? 'meta/llama-3.2-11b-vision-instruct');
            $rate_limit = max(1, min(60, (int)($_POST['rate_limit'] ?? 10)));
            $temperature = max(0.0, min(1.0, (float)($_POST['temperature'] ?? 0.6)));

            save_ai_env('NVIDIA_API_KEY', $api_key);
            save_ai_env('NVIDIA_DEFAULT_MODEL', $default_model);
            save_ai_env('NVIDIA_FALLBACK_MODEL', $fallback_model);
            save_ai_env('AI_RATE_LIMIT_PER_MINUTE', (string)$rate_limit);
            save_ai_env('AI_TEMPERATURE', (string)$temperature);

            set_flash('success', 'NVIDIA NIM AI configuration saved successfully to secure environment!');
            header('Location: ai-settings.php');
            exit;
        } elseif ($action === 'test_connection') {
            $test_model = trim($_POST['test_model'] ?? 'nvidia/nemotron-3.5-lightning-30b-a3b');
            $test_messages = [
                ['role' => 'system', 'content' => 'You are Campus AI. Reply with a short 1-sentence confirmation.'],
                ['role' => 'user', 'content' => 'Test connection ping. Are you online?']
            ];
            $test_result = call_nvidia_nim_chat($test_messages, $test_model);
        } elseif ($action === 'test_fallback') {
            // Deliberately test fallback with a short timeout to prove seamless failover to secondary cloud model
            $test_messages = [
                ['role' => 'system', 'content' => 'You are Campus AI. Reply with a short 1-sentence confirmation.'],
                ['role' => 'user', 'content' => 'Test fallback resilience ping. Are you online?']
            ];
            // Test with a heavy queue model and 2.0s timeout to trigger immediate cascade to configured fallback model
            $test_result = call_nvidia_nim_chat($test_messages, 'google/gemma-4-31b-it', ['timeout' => 2.0]);
            $test_result['is_test_cascade'] = true;
        }
    }
}

$current_key = get_ai_env('NVIDIA_API_KEY', '');
$is_configured = !empty($current_key) && !str_contains($current_key, 'YOUR_API_KEY') && !str_contains($current_key, 'YOUR_KEY');
$current_model = get_ai_env('NVIDIA_DEFAULT_MODEL', 'openai/gpt-oss-20b');
$current_fallback = get_ai_fallback_model($current_model);
$current_rate_limit = (int)get_ai_env('AI_RATE_LIMIT_PER_MINUTE', 10);
$current_temp = (float)get_ai_env('AI_TEMPERATURE', 0.6);
$models = get_nvidia_models();
// Last line: view template
require __DIR__ . '/../includes/templates/admin-ai-settings-view.php';

