<?php
declare(strict_types=1);

/**
 * AI Config — model catalog & selection
 * Extracted from includes/ai-config.php
 */

function get_nvidia_free_models(): array {
    return [
        'nvidia/nemotron-3.5-lightning-30b-a3b' => [
            'id' => 'nvidia/nemotron-3.5-lightning-30b-a3b',
            'name' => 'NVIDIA Nemotron 3.5 Lightning (30B)',
            'provider' => 'NVIDIA',
            'category' => 'recommended',
            'badge' => 'Domain Fast MoE',
            'badge_color' => 'success',
            'description' => 'Fastest 30B MoE model with top domain accuracy for portal navigation and student assistant FAQs.',
            'max_tokens' => 1024,
            'temperature' => 0.5,
            'priority' => 1,
            'is_recommended' => true
        ],
        'meta/llama-3.2-11b-vision-instruct' => [
            'id' => 'meta/llama-3.2-11b-vision-instruct',
            'name' => 'Meta Llama 3.2 11B',
            'provider' => 'Meta',
            'category' => 'fast',
            'badge' => 'Fast & Friendly',
            'badge_color' => 'primary',
            'description' => 'Lightweight, ultra-fast model providing responsive, approachable guidance for campus job seekers.',
            'max_tokens' => 1024,
            'temperature' => 0.6,
            'priority' => 2,
            'is_recommended' => true
        ],
        'deepseek-ai/deepseek-v4-flash-0731' => [
            'id' => 'deepseek-ai/deepseek-v4-flash-0731',
            'name' => 'DeepSeek V4 Flash (MoE)',
            'provider' => 'DeepSeek AI',
            'category' => 'fast',
            'badge' => 'Lightning MoE',
            'badge_color' => 'info',
            'description' => '284B MoE (13B active) high-throughput model optimized for quick chat and portal troubleshooting.',
            'max_tokens' => 1024,
            'temperature' => 0.5,
            'priority' => 3,
            'is_recommended' => true
        ],
        'google/gemma-4-31b-it' => [
            'id' => 'google/gemma-4-31b-it',
            'name' => 'Google Gemma 4 (31B)',
            'provider' => 'Google',
            'category' => 'reasoning',
            'badge' => 'Deep Reasoning',
            'badge_color' => 'warning',
            'description' => 'Dense 31B reasoning model for in-depth work-study policies, scheduling questions, and interview answers.',
            'max_tokens' => 1024,
            'temperature' => 0.6,
            'priority' => 4,
            'is_recommended' => false
        ],
        'mistralai/mistral-nemotron' => [
            'id' => 'mistralai/mistral-nemotron',
            'name' => 'Mistral NeMo-Tron',
            'provider' => 'Mistral & NVIDIA',
            'category' => 'reasoning',
            'badge' => 'Precise Instruction',
            'badge_color' => 'secondary',
            'description' => 'Co-developed by Mistral & NVIDIA for precise instruction-following and concise help desk answers.',
            'max_tokens' => 1024,
            'temperature' => 0.6,
            'priority' => 5,
            'is_recommended' => false
        ],
        'openai/gpt-oss-20b' => [
            'id' => 'openai/gpt-oss-20b',
            'name' => 'OpenAI GPT-OSS 20B',
            'provider' => 'OpenAI',
            'category' => 'fast',
            'badge' => 'Compact MoE',
            'badge_color' => 'dark',
            'description' => 'Compact Mixture of Experts text model delivering swift, concise answers to site inquiries.',
            'max_tokens' => 1024,
            'temperature' => 0.5,
            'priority' => 6,
            'is_recommended' => false
        ],
        'meta/llama-3.2-90b-vision-instruct' => [
            'id' => 'meta/llama-3.2-90b-vision-instruct',
            'name' => 'Meta Llama 3.2 90B',
            'provider' => 'Meta',
            'category' => 'reasoning',
            'badge' => '90B Powerhouse',
            'badge_color' => 'primary',
            'description' => 'High-capacity 90-billion parameter model for thorough applicant mentoring and mock interview critique.',
            'max_tokens' => 1024,
            'temperature' => 0.6,
            'priority' => 7,
            'is_recommended' => false
        ],
        'moonshotai/kimi-k3' => [
            'id' => 'moonshotai/kimi-k3',
            'name' => 'Moonshot Kimi K3',
            'provider' => 'Moonshot AI',
            'category' => 'reasoning',
            'badge' => 'Agentic MoE',
            'badge_color' => 'info',
            'description' => 'Multi-modal agentic model suited for structured student career walkthroughs.',
            'max_tokens' => 1024,
            'temperature' => 0.6,
            'priority' => 8,
            'is_recommended' => false
        ]
    ];
}

/**
 * Alias for get_nvidia_free_models
 */
function get_nvidia_models(): array {
    return get_nvidia_free_models();
}

/**
 * Validate model ID against allowed catalog
 */
function validate_ai_model(?string $model_id): string {
    $catalog = get_nvidia_free_models();
    $default = (string)get_ai_env('NVIDIA_DEFAULT_MODEL', 'nvidia/nemotron-3.5-lightning-30b-a3b');

    if (empty($model_id) || $model_id === 'auto') {
        return $default;
    }

    if (isset($catalog[$model_id])) {
        return $model_id;
    }

    return $default;
}

/**
 * Get validated secondary/fallback model ID
 */
function get_ai_fallback_model(?string $exclude_model = null): string {
    $catalog = get_nvidia_free_models();
    $configured_fallback = (string)get_ai_env('NVIDIA_FALLBACK_MODEL', 'openai/gpt-oss-20b');

    if (!empty($configured_fallback) && isset($catalog[$configured_fallback])) {
        if ($exclude_model === null || $configured_fallback !== $exclude_model) {
            return $configured_fallback;
        }
    }

    // High-reliability ordered defaults for cloud fallback
    $reliable_defaults = [
        'openai/gpt-oss-20b',
        'meta/llama-3.2-11b-vision-instruct',
        'nvidia/nemotron-3.5-lightning-30b-a3b',
        'mistralai/mistral-nemotron'
    ];

    foreach ($reliable_defaults as $cand) {
        if ($exclude_model === null || $cand !== $exclude_model) {
            return $cand;
        }
    }

    return 'openai/gpt-oss-20b';
}

/**
 * Format model display name for UI
 */
function get_model_display_name(?string $model_id): string {
    if (empty($model_id)) {
        return 'Campus AI';
    }
    $clean_id = preg_replace('/\s*\(.*?\)$/', '', $model_id) ?? $model_id;
    $catalog = get_nvidia_free_models();
    $has_local_suffix = str_contains($model_id, '(Local Guided Mode)');
    if (isset($catalog[$clean_id]['name'])) {
        return $catalog[$clean_id]['name'] . ($has_local_suffix ? ' (Local Guided Mode)' : '');
    }
    $parts = explode('/', $clean_id);
    $base_name = end($parts);
    return $base_name . ($has_local_suffix ? ' (Local Guided Mode)' : '');
}
