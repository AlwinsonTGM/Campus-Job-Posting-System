<?php
/**
 * Campus Job Posting System - Robot Models Catalog API
 * 
 * Returns list of verified free chat models available on NVIDIA NIM
 * along with connection health status.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../includes/ai-config.php';

$models_map = get_nvidia_free_models();
$models_list = array_values($models_map);

$api_key = trim(get_ai_env('NVIDIA_API_KEY', ''));
$is_configured = !empty($api_key) && !str_contains($api_key, 'YOUR_API_KEY') && !str_contains($api_key, 'YOUR_KEY');
$default_model = get_ai_env('NVIDIA_DEFAULT_MODEL', 'nvidia/nemotron-3.5-lightning-30b-a3b');
$fallback_model = get_ai_fallback_model($default_model);
$rate_limit = (int)get_ai_env('AI_RATE_LIMIT_PER_MINUTE', 10);
$rate_status = get_ai_rate_limit_status($rate_limit);

echo json_encode([
    'status' => 'success',
    'is_configured' => $is_configured,
    'default_model' => $default_model,
    'default_model_name' => get_model_display_name($default_model),
    'fallback_model' => $fallback_model,
    'fallback_model_name' => get_model_display_name($fallback_model),
    'rate_limit_per_minute' => $rate_limit,
    'rate_remaining' => $rate_status['remaining'],
    'rate_limit_max' => $rate_status['limit_max'],
    'total_models' => count($models_list),
    'models' => $models_list,
    'categories' => [
        'recommended' => 'Recommended for Campus FAQs (Domain MoE)',
        'fast' => 'Fast & Responsive Daily Drivers',
        'reasoning' => 'Deep Reasoning & Interview Prep'
    ]
], JSON_UNESCAPED_UNICODE);
