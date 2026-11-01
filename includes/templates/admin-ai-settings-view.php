<?php
/**
 * View template for admin/ai-settings.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>
<link rel="stylesheet" href="<?= $base_url ?>assets/css/features/ai-settings.css?v=<?= filemtime(__DIR__ . '/../../assets/css/features/ai-settings.css') ?>">

<div class="site-wrapper d-flex flex-column min-vh-100 bg-light">
    <?php require_once __DIR__ . '/../navbar.php'; ?>

    <main class="flex-grow-1 py-4 ai-settings">
        <div class="container-paper">
            
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="users.php">Administration</a></li>
                    <li class="breadcrumb-item active" aria-current="page">AI &amp; Robot Settings</li>
                </ol>
            </nav>

            <!-- Page Title Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h1 class="h3 fw-bold text-ink mb-1">
                        <i class="bi bi-cpu-fill text-success me-2"></i>AI &amp; Robot Settings
                    </h1>
                    <p class="text-muted-custom mb-0 small">
                        Manage the assistant’s connection, models, and usage limits in one place.
                    </p>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-eye me-1"></i> View assistant
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
                <div class="card ai-status ai-status--configured rounded-4 mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between p-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ai-status-icon rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-ink">API key saved</h6>
                                <small class="text-secondary">Key: <code><?= htmlspecialchars($masked_key) ?></code> • Primary: <strong><?= htmlspecialchars(get_model_display_name($current_model)) ?></strong> • Fallback: <strong><?= htmlspecialchars(get_model_display_name($current_fallback)) ?></strong></small>
                            </div>
                        </div>
                        <span class="badge ai-badge-accent px-3 py-2 rounded-pill">Configured</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="card ai-status rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="ai-status-icon rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                <i class="bi bi-key-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-ink">Local guided mode</h6>
                                <p class="small text-secondary mb-0">
                                    The assistant is using local campus guidance. Add an API key below to enable responses from the current cloud integration.
                                </p>
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

            <p class="ai-settings-compatibility"><i class="bi bi-info-circle" aria-hidden="true"></i> This installation uses a fixed API integration and model catalog. Connecting another provider may require changes to the integration.</p>

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

                                <!-- API Key Input with Protected Write-Only Pattern -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="nvidia_api_key" class="form-label fw-bold text-ink small mb-0">
                                            API key <?php if (!$is_configured): ?><span class="text-danger">*</span><?php endif; ?>
                                        </label>
                                    </div>

                                    <?php if ($is_configured): ?>
                                        <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-light rounded-3 border">
                                            <div class="d-flex align-items-center gap-2 small text-secondary">
                                                <i class="bi bi-key-fill text-success"></i>
                                                <span class="text-muted">Current:</span>
                                                <code class="text-dark fw-bold"><?= htmlspecialchars($masked_key) ?></code>
                                            </div>
                                            <div class="form-check form-check-inline m-0">
                                                <input class="form-check-input" type="checkbox" name="clear_api_key" id="clear_api_key" value="1">
                                                <label class="form-check-label small text-danger fw-semibold" for="clear_api_key">Remove Key</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="input-group">
                                        <input type="password" 
                                               id="nvidia_api_key" 
                                               name="nvidia_api_key" 
                                               class="form-control font-monospace text-dark" 
                                               placeholder="<?= $is_configured ? 'Enter new key to replace, or leave blank to keep current' : 'Enter your API key' ?>"
                                               value="" 
                                               autocomplete="new-password" 
                                               spellcheck="false">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleKeyBtn" aria-label="Show API key" aria-pressed="false">
                                            <i class="bi bi-eye" id="toggleKeyIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small text-muted mt-1">
                                        <i class="bi bi-shield-check text-success me-1"></i>Stored in the server environment. Leave blank to keep the current key.
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
                                    <button type="submit" class="btn ai-btn-primary py-2 rounded-pill fw-bold">
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
                                <i class="bi bi-lightning-charge text-accent me-2"></i>Test Model Connectivity
                            </h5>
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <p class="text-secondary small mb-3">
                                    Send a test message to check the saved credentials and selected model. Save any changes before testing.
                                </p>
                                <form method="POST" action="ai-settings.php" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="test_connection">

                                    <div class="mb-3">
                                        <label for="test_model" class="form-label fw-bold text-ink small">Model to test</label>
                                        <select name="test_model" id="test_model" class="form-select">
                                            <?php foreach ($models as $mid => $m): ?>
                                                <option value="<?= htmlspecialchars($mid) ?>">
                                                    <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['badge']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn ai-btn-outline w-100 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-send-fill me-1"></i> Test connection
                                    </button>
                                </form>

                                <form method="POST" action="ai-settings.php">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="test_fallback">

                                    <button type="submit" class="btn ai-btn-outline w-100 py-2 rounded-pill fw-semibold small">
                                        <i class="bi bi-arrow-repeat me-1"></i> Test fallback
                                    </button>
                                    <div class="form-text small text-muted text-center mt-1">
                                        Attempts a short-timeout request to exercise the fallback path; results depend on the connection.
                                    </div>
                                </form>
                            </div>

                            <div class="mt-4 p-3 bg-light rounded-3 border">
                                <h6 class="small fw-bold text-ink mb-1"><i class="bi bi-shield-lock-fill text-success me-1"></i> How the assistant responds</h6>
                                <ul class="small text-secondary mb-0 ps-3">
                                    <li>The API key is stored in the server environment.</li>
                                    <li>Session rate limit (<?= $current_rate_limit ?> req/min).</li>
                                    <li>Tiered failover: Primary failover to <code><?= htmlspecialchars(get_model_display_name($current_fallback)) ?></code> before Local Guided Mode.</li>
                                    <li>If cloud models are unavailable, local guidance can respond.</li>
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
                        <i class="bi bi-grid-3x3-gap-fill text-accent me-2"></i>Available models
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
                                        <td><span class="badge ai-badge-neutral"><?= htmlspecialchars($m['badge']) ?></span></td>
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

    <?php require_once __DIR__ . '/../footer.php'; ?>
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
                toggleBtn.setAttribute('aria-label', 'Hide API key');
                toggleBtn.setAttribute('aria-pressed', 'true');
            } else {
                keyInput.type = 'password';
                keyIcon.className = 'bi bi-eye';
                toggleBtn.setAttribute('aria-label', 'Show API key');
                toggleBtn.setAttribute('aria-pressed', 'false');
            }
        });
    }
});
</script>

