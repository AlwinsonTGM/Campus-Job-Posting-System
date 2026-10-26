<?php
/** Focused authentication shell; controllers continue to own authentication. */
$auth_page = true;
$auth_partner = $auth_partner ?? false;
$auth_register = $auth_register ?? false;
require __DIR__ . '/../header.php';
$auth_login_url = $auth_partner ? 'portal-login.php' : 'login.php';
?>
<div class="paper">
    <header class="site-header">
        <a class="brand" href="index.php"><img data-brand-logo src="assets/img/logo-d4-light-64.png" width="36" height="36" alt=""><span><?= htmlspecialchars(SITE_NAME) ?></span></a>
        <div class="header-actions">
            <a class="back-site" href="index.php"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> Back to website</a>
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch theme"><i class="bi bi-moon" data-theme-toggle-icon aria-hidden="true"></i></button>
        </div>
    </header>
    <main>
        <div class="auth-layout <?= $auth_partner ? 'partner' : 'student' ?> <?= $auth_register ? 'register' : '' ?>">
            <section class="story" aria-labelledby="story-title">
                <div class="story-top"><span><i class="bi bi-<?= $auth_partner ? 'buildings' : 'mortarboard' ?>" aria-hidden="true"></i> <?= $auth_partner ? 'Partner &amp; staff space' : 'Student space' ?></span></div>
                <div class="story-copy">
                    <h2 id="story-title"><?= htmlspecialchars($auth_story_title ?? ($auth_partner ? 'Connect talent with opportunity.' : 'Your next chapter starts on campus.')) ?></h2>
                    <p><?= htmlspecialchars($auth_story_description ?? ($auth_partner ? 'Your workspace for campus hiring, student placements, and the people behind them.' : 'A space to find opportunities, build experience, and take your next step.')) ?></p>
                </div>
                <div class="story-bottom">
                    <ul>
                        <?php foreach ($auth_story_benefits ?? ($auth_partner ? [['briefcase', 'Manage your job postings'], ['people', 'Review student applications'], ['building-check', 'Coordinate campus placements']] : [['search', 'Find student assistantships'], ['folder2-open', 'Keep track of your applications'], ['calendar3', 'Find work around your classes']]) as [$icon, $text]): ?>
                            <li><i class="bi bi-<?= $icon ?>" aria-hidden="true"></i> <?= $text ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="story-foot"><span><?= htmlspecialchars($auth_story_footnote ?? ($auth_partner ? 'Department offices · Industry partners · Administrators' : 'For KLD students & alumni')) ?></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></div>
                </div>
            </section>
            <section class="form-area" aria-labelledby="form-title">
