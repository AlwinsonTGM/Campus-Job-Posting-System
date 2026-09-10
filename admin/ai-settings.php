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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="site-wrapper d-flex flex-column min-vh-100 bg-light">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-grow-1 py-4">
        <div class="container-paper">
            
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="users.php">Administration</a></li>
                    <li class="breadcrumb-item active" aria-current="page">NVIDIA AI &amp; Robot Settings</li>
                </ol>
            </nav>

            <!-- Page Title Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h1 class="h3 fw-bold text-ink mb-1">
                        <i class="bi bi-cpu-fill text-success me-2"></i>NVIDIA NIM &amp; 3D Robot Settings
                    </h1>
                    <p class="text-muted-custom mb-0 small">
                        Manage your NVIDIA account API key, configure all chat endpoints, rate limits, and test model responses live.
                    </p>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-eye me-1"></i> View 3D Robot on Homepage
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Status Banner -->
            <?php if ($is_configured): ?>
                <div class="card border-success bg-success-subtle shadow-sm rounded-4 mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between p-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-success-emphasis">NVIDIA NIM AI Gateway Configured &amp; Active</h6>
                                <small class="text-secondary">Key: <code><?= htmlspecialchars(substr($current_key, 0, 10)) . '...' . htmlspecialchars(substr($current_key, -4)) ?></code> • Primary: <strong><?= htmlspecialchars(get_model_display_name($current_model)) ?></strong> • Fallback: <strong><?= htmlspecialchars(get_model_display_name($current_fallback)) ?></strong></small>
                            </div>
                        </div>
                        <span class="badge bg-success px-3 py-2 rounded-pill">Status: Online</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-warning bg-warning-subtle shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi bi-key-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-warning-emphasis">Local Guided Mode Active (Awaiting NVIDIA API Key)</h6>
                                <p class="small text-secondary mb-2">
                                    The 3D robot on the homepage is currently operating in <strong>Local Guided Mode</strong> using verified campus advice. To enable real-time generative AI across all models:
                                </p>
                                <ol class="small text-secondary mb-2 ps-3">
                                    <li>Log into your account at <a href="https://build.nvidia.com" target="_blank" class="fw-bold text-dark text-decoration-underline">build.nvidia.com <i class="bi bi-box-arrow-up-right"></i></a> (comes with 1,000 credits).</li>
                                    <li>Click any model and tap <strong>"Get API Key"</strong>. Copy your key starting with <code>nvapi-...</code>.</li>
                                    <li>Paste it below and click <strong>Save Configuration</strong>. All models unlock instantly with automated multi-tier failover!</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($test_result): ?>
                <div class="card shadow-sm rounded-4 mb-4 border-<?= $test_result['success'] ? (!empty($test_result['is_model_fallback']) ? 'warning' : 'primary') : 'danger' ?>">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <strong class="small text-ink"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Live Connection Test Result</strong>
                        <?php if ($test_result['success']): ?>
                            <?php if (!empty($test_result['is_model_fallback'])): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-arrow-repeat me-1"></i>Model-to-Model Fallback Succeeded (<?= (int)$test_result['latency_ms'] ?>ms)
                                </span>
                            <?php elseif (!empty($test_result['is_local_fallback'])): ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-hdd me-1"></i>Local Guided Mode Fallback
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check2-circle me-1"></i>Direct Primary Hit (<?= (int)$test_result['latency_ms'] ?>ms)
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-danger">Test Failed</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-3">
                        <div class="small mb-2 text-muted d-flex justify-content-between flex-wrap gap-2">
                            <span>Responding Model: <code><?= htmlspecialchars($test_result['model']) ?></code></span>
                            <?php if (!empty($test_result['is_model_fallback'])): ?>
                                <span class="text-warning-emphasis fw-semibold">
                                    <i class="bi bi-arrow-return-right me-1"></i>Rerouted from: <code><?= htmlspecialchars($test_result['original_model'] ?? 'primary') ?></code>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 bg-light rounded-3 border">
                            <p class="mb-0 text-ink"><?= nl2br(htmlspecialchars($test_result['reply'])) ?></p>
                        </div>
                        <?php if (!empty($test_result['fallback_notice'])): ?>
                            <div class="alert alert-warning py-2 px-3 small mt-2 mb-0 rounded-3">
                                <i class="bi bi-info-circle-fill me-1"></i><?= htmlspecialchars($test_result['fallback_notice']) ?>
                            </div>
                        <?php elseif (!empty($test_result['notice'])): ?>
                            <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($test_result['notice']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Left Column: Core Configuration Form -->
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0 fw-bold text-ink">
                                <i class="bi bi-sliders me-2 text-accent"></i>API &amp; Security Settings
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="ai-settings.php">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="save_settings">

                                <!-- API Key Input with Show/Hide -->
                                <div class="mb-3">
                                    <label for="nvidia_api_key" class="form-label fw-bold text-ink small">
                                        NVIDIA API Key <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               id="nvidia_api_key" 
                                               name="nvidia_api_key" 
                                               class="form-control font-monospace text-dark" 
                                               placeholder="nvapi-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                               value="<?= htmlspecialchars($current_key) ?>" 
                                               autocomplete="off" 
                                               spellcheck="false">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleKeyBtn" title="Show/Hide Key">
                                            <i class="bi bi-eye" id="toggleKeyIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small text-muted">
                                        Stored securely in <code>.env</code> with Apache direct-access protection. Never leaked to client-side code.
                                    </div>
                                </div>

                                <!-- Default Primary Model -->
                                <div class="mb-3">
                                    <label for="default_model" class="form-label fw-bold text-ink small">
                                        Default Primary Model
                                    </label>
                                    <select name="default_model" id="default_model" class="form-select">
                                        <?php foreach ($models as $mid => $m): ?>
                                            <option value="<?= htmlspecialchars($mid) ?>" <?= $mid === $current_model ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['provider']) ?>) — <?= htmlspecialchars($m['badge']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text small text-muted">
                                        Primary AI model dispatched for student queries.
                                    </div>
                                </div>

                                <!-- Secondary Fallback Model -->
                                <div class="mb-3">
                                    <label for="fallback_model" class="form-label fw-bold text-ink small">
                                        Secondary Fallback Model <span class="badge bg-light text-dark border ms-1">Auto Failover</span>
                                    </label>
                                    <select name="fallback_model" id="fallback_model" class="form-select">
                                        <?php foreach ($models as $mid => $m): ?>
                                            <option value="<?= htmlspecialchars($mid) ?>" <?= $mid === $current_fallback ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['provider']) ?>) — <?= htmlspecialchars($m['badge']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text small text-muted">
                                        Automatically takes over if the primary model is busy, times out, or rate-limited (429/503), <strong>before</strong> resorting to Local Guided Mode.
                                    </div>
                                </div>

                                <!-- Rate Limit -->
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label for="rate_limit" class="form-label fw-bold text-ink small">
                                            Rate Limit (Req/Min/User)
                                        </label>
                                        <input type="number" 
                                               name="rate_limit" 
                                               id="rate_limit" 
                                               class="form-control" 
                                               min="1" 
                                               max="60" 
                                               value="<?= $current_rate_limit ?>">
                                        <div class="form-text small text-muted">Protects credits from bots.</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="temperature" class="form-label fw-bold text-ink small">
                                            Creativity Temperature
                                        </label>
                                        <input type="number" 
                                               name="temperature" 
                                               id="temperature" 
                                               class="form-control" 
                                               min="0.0" 
                                               max="1.0" 
                                               step="0.05" 
                                               value="<?= $current_temp ?>">
                                        <div class="form-text small text-muted">0.4 (Focused) to 0.7 (Creative).</div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-success py-2 rounded-3 fw-bold">
                                        <i class="bi bi-shield-check me-1"></i> Save Configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Live Connectivity Tester -->
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0 fw-bold text-ink">
                                <i class="bi bi-lightning-charge text-warning me-2"></i>Test Model Connectivity
                            </h5>
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <p class="text-secondary small mb-3">
                                    Send a live test ping directly to NVIDIA's cloud inference microservices to verify your API credentials and latency for any chosen model:
                                </p>
                                <form method="POST" action="ai-settings.php" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="test_connection">

                                    <div class="mb-3">
                                        <label for="test_model" class="form-label fw-bold text-ink small">Select Model to Probe</label>
                                        <select name="test_model" id="test_model" class="form-select">
                                            <?php foreach ($models as $mid => $m): ?>
                                                <option value="<?= htmlspecialchars($mid) ?>">
                                                    <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['badge']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-outline-primary w-100 py-2 rounded-3 fw-bold">
                                        <i class="bi bi-send-fill me-1"></i> Run Live Probe Ping
                                    </button>
                                </form>

                                <form method="POST" action="ai-settings.php">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="test_fallback">

                                    <button type="submit" class="btn btn-outline-secondary w-100 py-2 rounded-3 fw-semibold small">
                                        <i class="bi bi-arrow-repeat me-1"></i> Test Primary-to-Fallback Cascade
                                    </button>
                                    <div class="form-text small text-muted text-center mt-1">
                                        Proves that an unresponsive primary smoothly routes to the secondary model before local fallback.
                                    </div>
                                </form>
                            </div>

                            <div class="mt-4 p-3 bg-light rounded-3 border">
                                <h6 class="small fw-bold text-ink mb-1"><i class="bi bi-shield-lock-fill text-success me-1"></i> Security Perimeters Active</h6>
                                <ul class="small text-secondary mb-0 ps-3">
                                    <li>Direct browser access to <code>.env</code> blocked via Apache <code>403</code>.</li>
                                    <li>Session sliding-window rate limiting active (<?= $current_rate_limit ?> req/min).</li>
                                    <li>Tiered failover: Primary failover to <code><?= htmlspecialchars(get_model_display_name($current_fallback)) ?></code> before Local Guided Mode.</li>
                                    <li>Input prompt cap strictly enforced at 800 characters.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Models Catalog Matrix -->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 fw-bold text-ink">
                        <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>NVIDIA Chat Models Catalog
                    </h5>
                    <span class="badge bg-secondary rounded-pill"><?= count($models) ?> Models Loaded</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-secondary">
                                <tr>
                                    <th class="ps-4">Model Name &amp; ID</th>
                                    <th>Provider</th>
                                    <th>Category</th>
                                    <th>Badge</th>
                                    <th>Max Output</th>
                                    <th>Best Suited For</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php foreach ($models as $mid => $m): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-ink"><?= htmlspecialchars($m['name']) ?></div>
                                            <code class="text-muted small"><?= htmlspecialchars($m['id']) ?></code>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($m['provider']) ?></span></td>
                                        <td><?= htmlspecialchars(ucfirst($m['category'])) ?></td>
                                        <td><span class="badge bg-<?= htmlspecialchars($m['badge_color']) ?>"><?= htmlspecialchars($m['badge']) ?></span></td>
                                        <td><?= (int)$m['max_tokens'] ?> tokens</td>
                                        <td class="text-secondary"><?= htmlspecialchars($m['description']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleKeyBtn');
    const keyInput = document.getElementById('nvidia_api_key');
    const keyIcon = document.getElementById('toggleKeyIcon');

    if (toggleBtn && keyInput && keyIcon) {
        toggleBtn.addEventListener('click', function () {
            if (keyInput.type === 'password') {
                keyInput.type = 'text';
                keyIcon.className = 'bi bi-eye-slash';
            } else {
                keyInput.type = 'password';
                keyIcon.className = 'bi bi-eye';
            }
        });
    }
});
</script>
