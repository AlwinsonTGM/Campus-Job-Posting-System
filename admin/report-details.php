<?php
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$types = [
    'vacancies' => 'Total Vacancies',
    'applications' => 'Applications Filed',
    'interviews' => 'Interviews Scheduled',
    'hired' => 'Officially Hired',
];
$type = is_string($_GET['metric'] ?? null) ? $_GET['metric'] : '';
if (!isset($types[$type])) {
    http_response_code(404);
    exit('Report not found.');
}
$page_title = $types[$type] . ' | Reports';
if ($type === 'vacancies') {
    $rows = get_jobs();
} else {
    $rows = get_applications();
    if ($type === 'interviews' || $type === 'hired') {
        $statuses = $type === 'interviews'
            ? ['interview_scheduled', 'interview scheduled', 'interview']
            : ['accepted', 'accepted / hired', 'hired'];
        $rows = array_values(array_filter($rows, fn($row) => in_array(strtolower($row['status'] ?? ''), $statuses, true)));
    }
}
$total = count($rows);
$per_page = 25;
$total_pages = max(1, (int)ceil($total / $per_page));
$page = min($total_pages, max(1, (int)($_GET['page'] ?? 1)));
$visible_rows = array_slice(array_values($rows), ($page - 1) * $per_page, $per_page);
$escape = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
require_once __DIR__ . '/../includes/header.php';
?>
<div class="sheet-perspective-wrapper"><div class="sheet flat-sheet">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    <main class="py-5"><div class="container-paper">
        <a href="reports.php" class="text-ink text-decoration-none fw-semibold"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to reports</a>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mt-4 mb-4">
            <div><h1 class="h2 fw-bold text-ink mb-2"><?= $escape($types[$type]) ?></h1><p class="text-muted-custom mb-0">Records included in this report metric.</p></div>
            <strong class="text-ink"><?= number_format($total) ?> <?= $total === 1 ? 'record' : 'records' ?></strong>
        </div>
        <div class="card-paper p-0 overflow-hidden"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr>
                    <?php if ($type === 'vacancies'): ?><th scope="col">Vacancy</th><th scope="col">Organization</th><th scope="col">Category</th><th scope="col">Status</th>
                    <?php else: ?><th scope="col">Applicant</th><th scope="col">Vacancy</th><th scope="col">Department</th><th scope="col">Status</th><th scope="col">Applied</th><?php endif; ?>
                </tr></thead><tbody>
                <?php foreach ($visible_rows as $row): ?><tr>
                    <?php if ($type === 'vacancies'): ?><td><?= $escape($row['title'] ?? '—') ?></td><td><?= $escape($row['organization_name'] ?? $row['department'] ?? '—') ?></td><td><?= $escape($row['category'] ?? '—') ?></td><td><?= $escape(ucwords(str_replace('_', ' ', $row['status'] ?? ''))) ?></td>
                    <?php else: ?><td><?= $escape($row['student_name'] ?? '—') ?></td><td><?= $escape($row['job_title'] ?? '—') ?></td><td><?= $escape($row['department'] ?? '—') ?></td><td><?= $escape(ucwords(str_replace('_', ' ', $row['status'] ?? ''))) ?></td><td><?= $escape(!empty($row['applied_at']) ? format_display_date($row['applied_at']) : '—') ?></td><?php endif; ?>
                </tr><?php endforeach; ?>
                <?php if (!$visible_rows): ?><tr><td colspan="<?= $type === 'vacancies' ? 4 : 5 ?>" class="text-center text-muted-custom py-4">No records found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div></div>
        <?php if ($total_pages > 1): ?><nav aria-label="Report pages" class="d-flex align-items-center justify-content-between gap-3 mt-4">
            <?php if ($page > 1): ?><a class="btn-pill-outline" href="?metric=<?= $escape($type) ?>&amp;page=<?= $page - 1 ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
            <span class="text-muted-custom">Page <?= $page ?> of <?= $total_pages ?></span>
            <?php if ($page < $total_pages): ?><a class="btn-pill-outline" href="?metric=<?= $escape($type) ?>&amp;page=<?= $page + 1 ?>">Next</a><?php else: ?><span></span><?php endif; ?>
        </nav><?php endif; ?>
    </div></main>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
