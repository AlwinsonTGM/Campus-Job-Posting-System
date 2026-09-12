# AI Slop Cleanup — Before / After

Date: 2026-09-10 (Round 2 added same day)
Scope Round 1: high-priority batch only (security backdoor, banned pill eyebrows, emojis in prod UI, fake domains).
Scope Round 2: chatbot label purge, table status pills → paper dot+text (dark/light safe), job card 1-overlay-max, accept congratulations + withdraw-others prompt.
All 9 files pass `php -l`. E2E still works on `127.0.0.1`; remote/prod `?reset` / `?demo` now return `403`.

Files changed (9):
`login.php`, `includes/components.php`, `index.php`, `forgot-pass.php`, `includes/footer.php`, `admin/categories.php`, `includes/ai-config.php`, `api/robot-chat.php`, `privacy.php`

---

## 1. `login.php:9-52` — Unauthenticated `?reset` / `?demo` backdoor (CRITICAL)

**Why slop:** vibecode demo scaffold left in prod. Anyone could wipe the DB (`GET /login.php?reset=1`) or log in passwordless as admin (`GET /login.php?demo=admin`).

**BEFORE:**
```php
// Handle Datastore Reset from URL params (used by E2E test suites & demo fixtures)
if (isset($_GET['reset'])) {
    // ... session_destroy + execute_migration_and_seed() — no auth check
}

// Handle Quick Demo Login from URL params
if (isset($_GET['demo'])) {
    $role = $_GET['demo'];          // unvalidated
    $user = quick_login($role);     // no password
```

**AFTER:**
```php
// Test hooks (?reset / ?demo) — LOCAL-ONLY. Blocked in production and for remote clients.
// E2E suite runs on 127.0.0.1 so tests keep working. Set APP_ENV=production in prod .env to hard-close.
$__app_env = strtolower(trim((string)(getenv('APP_ENV') ?: '')));
$__is_production = ($__app_env === 'production' || $__app_env === 'prod');
$__remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
$__is_loopback = in_array($__remote_addr, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);
$__test_hooks_enabled = !$__is_production && $__is_loopback;

if (isset($_GET['reset'])) {
    if (!$__test_hooks_enabled) { http_response_code(403); exit('Forbidden'); }
    // ... existing reset body unchanged
}

if (isset($_GET['demo'])) {
    if (!$__test_hooks_enabled) { http_response_code(403); exit('Forbidden'); }
    $role = $_GET['demo'];
    if (!in_array($role, ['student', 'employer', 'admin'], true)) {
        header('Location: login.php'); exit;
    }
```

**What to check:** open `/login.php?demo=admin` from your laptop (loopback) → still logs in. From prod with `APP_ENV=production` (or any non-loopback IP) → `403 Forbidden`. Add `APP_ENV=production` to prod `.env` to hard-close.

---

## 2. Banned decorative pill eyebrows above headings (AGENTS.md violation)

Rule: badges are for **dynamic status data only** — never decorative pills above `h1`/`h2`.

### 2a. `includes/components.php:11-18` — the factory default

**BEFORE:**
```php
$badgeClass = !empty($eyebrowClass) ? $eyebrowClass : 'badge rounded-pill d-inline-flex align-items-center gap-1 bg-success text-white shadow-sm text-wrap text-start lh-sm py-2 px-3 border-0';
...
<span class="<?= htmlspecialchars($badgeClass) ?>" style="max-width: 100%; white-space: normal; font-size: 12px; ...">
```

**AFTER:**
```php
// Plain-text kicker only — never a badge pill above h1 (per AGENTS.md badges-for-data-only rule).
$badgeClass = !empty($eyebrowClass) ? $eyebrowClass : 'eyebrow-badge text-muted-custom';
...
<span class="<?= htmlspecialchars($badgeClass) ?>">
```

**What to check:** every page using `render_page_head()` now shows a plain uppercase kicker instead of a green pill. No inline `style=""` left on the span.

### 2b. `index.php:626-630` — "Our Impact" pill above metrics `h2`

**BEFORE:**
```html
<span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-white text-dark mb-3">
  <i class="bi bi-bar-chart-fill text-accent"></i> Our Impact
</span>
<h2 ...>Together, We're Building Careers</h2>
```

**AFTER:**
```html
<span class="eyebrow-badge text-white-50">Our Impact</span>
<h2 ...>Together, We're Building Careers</h2>
```

### 2c. `index.php:905-909` — "TAKE ACTION" pill above CTA `h2`

**BEFORE:**
```html
<span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle mb-3">
  <i class="bi bi-lightning-charge-fill text-accent"></i> TAKE ACTION
</span>
```

**AFTER:**
```html
<span class="eyebrow-badge text-muted-custom">Take Action</span>
```

### 2d. `forgot-pass.php:48-52` — "Account Recovery" pill above auth `h2`

**BEFORE:**
```html
<span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle mb-3">
  <i class="bi bi-key-fill text-accent"></i> Account Recovery
</span>
```

**AFTER:**
```html
<span class="eyebrow-badge text-muted-custom">Account Recovery</span>
```

### 2e. `includes/footer.php:42,55,66` — `eyebrow-badge` misused on footer `h6`s

**BEFORE:**
```html
<h6 class="eyebrow-badge text-ink mb-3">Quick Links</h6>
<h6 class="eyebrow-badge text-ink mb-3">Legal & Guidelines</h6>
<h6 class="eyebrow-badge text-ink mb-3">Campus Career Center</h6>
```

**AFTER:**
```html
<h6 class="fw-bold text-ink mb-3 small text-uppercase">Quick Links</h6>
<h6 class="fw-bold text-ink mb-3 small text-uppercase">Legal & Guidelines</h6>
<h6 class="fw-bold text-ink mb-3 small text-uppercase">Campus Career Center</h6>
```

**What to check:** headings stand on clean typography, no floating pill clutter. Kept `badge rounded-pill` only where it shows real data (e.g. `admin/users.php:388` role chip, `student/my-applications.php:128` status) — those are allowed.

---

## 3. Emojis in production UI → Bootstrap Icons / plain text

### 3a. `admin/categories.php:489-520,624-655` — preset buttons (16x, new + edit modals)

**BEFORE:**
```html
<button ... data-url="assets/img/categories/cat-tech.jpg" data-icon="bi-laptop">💻 Tech / IT</button>
<button ... data-icon="bi-book">📚 Library</button>
<button ... data-icon="bi-folder2-open">🗄️ Office / Admin</button>
<!-- + 🔬 🎓 🏆 🎨 ☕, duplicated in edit modal -->
```

**AFTER:**
```html
<button ... data-url="assets/img/categories/cat-tech.jpg" data-icon="bi-laptop"><i class="bi bi-laptop me-1"></i>Tech / IT</button>
<button ... data-icon="bi-book"><i class="bi bi-book me-1"></i>Library</button>
<button ... data-icon="bi-folder2-open"><i class="bi bi-folder2-open me-1"></i>Office / Admin</button>
<!-- + bi-radioactive / bi-mortarboard / bi-trophy / bi-camera-reels / bi-cup-hot -->
```

**What to check:** open Admin → Categories → New/Edit modal. Preset buttons show crisp `bi-*` icons (same set as `data-icon`), no OS-dependent emoji rendering.

### 3b. `includes/ai-config.php:808-906` — chatbot replies (12 templates)

**BEFORE:**
```php
$reply = "🏢 **UPDATING EMPLOYER PROFILE:**\n\n" ... "apply immediately! 💼";
$reply = "👤 **HOW TO CHANGE YOUR PERSONAL DETAILS ...**" ... "apply the updates! 📄✨";
$reply = "🔒 **UPDATING YOUR PASSWORD ...**" ... "secure your account! 🛡️";
// + 📋🎓⏰🏢🧭📝⚡🔥🛡️👋🤖 across all 12 branches
```

**AFTER:**
```php
$reply = "**UPDATING EMPLOYER PROFILE:**\n\n" ... "apply immediately.";
$reply = "**HOW TO CHANGE YOUR PERSONAL DETAILS ...**" ... "apply the updates.";
$reply = "**UPDATING YOUR PASSWORD ...**" ... "secure your account.";
// all 12 branches: bold heading kept, leading/trailing emojis removed, "! 🎯" → "."
```

**What to check:** ask the campus chatbot "how do I apply", "change password", "track applications" — answers read formal/institutional, no emoji.

### 3c. `api/robot-chat.php:32` — emoji inside JSON API contract

**BEFORE:**
```php
'message' => '🛡️ Anti-Spam Active: To keep AI free and fast ...',
```

**AFTER:**
```php
'message' => 'Anti-Spam Active: To keep AI free and fast ...',
```

**What to check:** API returns plain text; frontend adds the icon. Logs stay clean.

---

## 4. Fake `campus-hire.edu` domain → real `kld.edu.ph` (6 spots)

| File:line | BEFORE | AFTER |
|---|---|---|
| `login.php:180` placeholder | `username@campus-hire.edu` | `username@kld.edu.ph` |
| `forgot-pass.php:121` placeholder | `username@campus-hire.edu` | `username@kld.edu.ph` |
| `forgot-pass.php:86` helpdesk | `support@campus-hire.edu` | `support@kld.edu.ph` |
| `includes/footer.php:72` | `careers@campus-hire.edu` | `careers@kld.edu.ph` (matches `updates.php:253`) |
| `privacy.php:142` DPO contact | `dataprivacy@campus-hire.edu` | `dataprivacy@kld.edu.ph` |
| `privacy.php:211` `mailto:` | `mailto:dataprivacy@campus-hire.edu` | `mailto:dataprivacy@kld.edu.ph` |

**What to check:** `grep campus-hire.edu` returns zero hits in PHP now. Legal/DPO contact is consistent.

---

## Left for later (not touched)

- `assets/js/hero-robot.js:198-255,633,672` — same emoji-tone issue in frontend robot tips (`⚡ BOOST`, `🔥 GRILL-ME`, `🛡️ Anti-Spam`). Same fix pattern as 3b when you want it.
- `README.md`, `Campus Job Posting System - Project Blueprint.md` emojis — docs only, harmless.
- Status pills in `admin/users.php:388`, `student/my-applications.php:128`, `student/jobs.php:352` — these show real data (roles/statuses), allowed by the badges-for-data-only rule.
- Pre-existing `?? uploads/resumes/resume_1789036513_eb5e7385.pdf` untracked file — uploaded test resume, not part of this cleanup.

---

# Round 2 — Chatbot labels, table pills, job card, accept flow (2026-09-10)

## 5. Fullscreen chatbot labels purged (`index.php`, `faqs.php`)

Removed (all verified, JS null-guarded so no errors):
- `CAMPUS FAQ` intent badge — `index.php:937`, `faqs.php:217` (round 1)
- `AI Companion` model tag + `Student Career Copilot` sub-line — `index.php:938-945`, `faqs.php:218-225`
- Small-bubble `AI Companion` footer label — `index.php:145-148` (Expand / Next-tip buttons kept, right-aligned)

**BEFORE:**
```html
<h5>Campus AI Assistant</h5>
<span class="fs-intent-badge">CAMPUS FAQ</span>
...
<span class="fs-model-tag"><i class="bi bi-cpu-fill"></i><span>AI Companion</span></span>
<span class="text-muted small">&bull;</span>
<span class="text-muted small">Student Career Copilot</span>
```

**AFTER:**
```html
<h5>Campus AI Assistant</h5>
```
Title stands alone. `hero-robot.js` eye-color themes + `badgeText` cycle left intact (elements no longer exist, assignments no-op) — robot eye colors still change per mode.

## 6. Table status pills → paper dot+text (`assets/css/custom.css:2590-2680`)

**Why:** 5 neon filled pills (amber/blue/sky/green/red) + hardcoded text colors (`#925400`, `#125ea8`…) that break in dark mode. Green `Accepted` was also misused for neutral `≤ 20 hrs/wk` labels.

**BEFORE:** filled `background: var(--st-*-bg)` + hardcoded text color + status-colored border.
**AFTER:** transparent background, `color: var(--ink)`, `1px solid var(--line)` border; status meaning carried by the existing icon tinted `var(--st-*)`:
```css
.badge-status--accepted { background: transparent; color: var(--ink); border: 1px solid var(--line); }
.badge-status--accepted i { color: var(--st-accepted); }
/* same pattern for pending / review / interview / declined */
```
Dark/light safe: `--ink`/`--line` adapt, `--st-*` tokens already have dark values in `tokens.css:133-142`. No markup changes needed.

Also fixed misuse: `≤ 20 hrs/wk` labels in `settings.php:431`, `student/apply.php:225`, `student/job-details.php:172`, `student/dashboard.php:222`, `employer/review-app.php:139` changed from `badge-status--accepted` (implies hired) to neutral `chip`.

## 7. Job card photo: 4 pills → 1 overlay max (`includes/components.php:198-280`)

Agreed — slop confirmed. The photo carried `University Office` + `Featured`/`On-Campus` + category + pay pills, hiding the image and duplicating body content.

**BEFORE:** top row (employer-type pill + featured/work-setup pill) + bottom row (category pill + pay pill) over a dark gradient.
**AFTER:**
- Photo overlay: `Featured` ribbon only, and only when the posting is actually featured. Gradient softened since no text sits on it.
- Card body gains a plain metadata row (no pills): `University Office • Sports & Athletics Aide • ₱85.00 / hour` with small icons.
- Non-featured cards: clean photo, zero overlays.

Category admin cards (`admin/categories.php:319-346`) left as-is — their pills carry distinct data (tag, vacancy count, hourly range), not duplicates.

## 8. Accept flow: congratulations + withdraw-others prompt (`student/my-applications.php`)

No auto-withdraw existed — `update_application_status()` (`includes/data-helper.php:1634`) only bumped `slots_filled` + sent a notification. Deliberately did **not** build silent auto-withdraw (student may hold OJT + part-time, or prefer choosing; silent deletes risk RA 10173 complaints and employer confusion).

**What was built (prompt-to-withdraw, opt-in):**
- New `withdraw_others` POST handler (CSRF-checked, only `pending`/`pending review` apps, ownership-checked, count flash).
- New banner (paper card, no pills) shown only when the student has ≥1 accepted AND ≥1 withdrawable pending app:
  - `Congratulations, {name}!` + accepted job title + pending count + why withdrawing helps.
  - `Withdraw all N pending` (confirm-guarded) + `Review them below` anchor.
- Existing per-card accepted callout (`My Applications:169-179`) kept as the refresh-persistent congratulations.

**How it shows on login/refresh:** student signs in → opens My Applications → banner sits under the filter tabs on every visit until no pending apps remain. Screenshot proof (demo data, since removed): `Downloads/accept-demo-full.png` + `Downloads/accept-demo-banner.png` — verified rendering, then demo rows deleted and app #4 reverted to `pending` (DB restored, `dbcheck` confirmed rows 1-5 original).

---

# Round 3 — Apply Now auto-login-as-Dela-Cruz fix (2026-09-10)

**Root cause:** `student/apply.php:12` redirected guests to `login.php?demo=student`, which silently signed them in as the first student seed (Juan Dela Cruz) on loopback. Wrong on two levels: identity bug (acting as someone else) + demo hook reachable from a public button.

**BEFORE:** guest clicks Apply Now on index → `student/apply.php?id=N` → `302 ../login.php?demo=student` → logged in as Dela Cruz.
**AFTER:**
- `student/apply.php:10-16` → guests go to `../login.php?next=student/apply.php?id=N` (plain form, no auto-login).
- `login.php` gained a `next` return-to: strict whitelist (must match `^(student|employer|admin)/…`, no `..`, no `:`, no `//`), preserved through POST via hidden field, honored after manual sign-in and in the already-logged-in branch (role-prefix matched).
- Verified with curl: guest apply → `302 .../login.php?next=...`; form renders with hidden `next`; `next=https://evil.com` rejected (no field → role dashboard fallback).

---

# Round 4 — Reports table flag consistency (2026-09-10)

**Root causes of the screenshot inconsistencies:**
1. `admin/reports.php:227` — Filled Positions swapped classes per value (`badge-status--accepted` when full, `--pending` when partial, `chip` when zero), so `1 / 5` rendered amber/outlined while `0 / 6` rendered as a filled chip.
2. `admin/reports.php:238-246` — Compliance mixed three `badge-status--*` pills with a plain `chip`, so `IN PROGRESS` dwarfed `Open Quota`. Label casing also mixed (`Quota Exceeded` vs `IN PROGRESS`).
3. Card header `Term:` pill was decorative badge clutter — now plain muted text.

**Fix — new `.cell-flag` (`assets/css/custom.css`):** one box for every state (`min-height: 28px`, same font/padding/border), meaning carried only by a 7px status dot (`--ok` accent, `--warn` amber, `--bad` red, `--idle` muted). All vars already have dark-mode tokens. Filled Positions is now always a neutral `chip` (the ratio bar + flag already encode state — the count doesn't need its own color).
**Verified:** `Downloads/reports-table.png` — all 13 rows, `OPEN QUOTA` and `IN PROGRESS` identical size.

---

# Round 5 — Small duplicates (2026-09-10)

- `student/job-details.php:273-275` — two Back-to-Vacancies controls (top breadcrumb + bottom sidebar button). Removed the bottom button; top breadcrumb stays as the single way back.
- `settings.php:555-560` — two dark-mode toggles in Appearance (segmented Light/Dark + green switch). Removed the green switch; segmented control stays. `theme-toggle.js` already null-guards the removed checkbox, no JS change needed.

---

# Round 6 — Double scrollbar + category card pills (2026-09-11)

- **Twin scrollbars (Chrome):** `overflow-x: hidden` on both `html` and `body` (`assets/css/base.css`) turned each into its own scroll container → two vertical scrollbars. Changed to `overflow-x: clip` (plus `main`), which blocks horizontal spill without creating a scroller. Verified via Playwright: only `documentElement` scrolls now; hero screenshot pixel-identical otherwise (`Downloads/index-top.png`, `Downloads/index-dark.png`).
- **Cache-bust fix:** the corrected `base.css` never reached browsers because `custom.css` imports it with a hardcoded `?v=1.5`. Bumped to `?v=1.6` (custom.css itself is `filemtime`-versioned, so the new URL chain refetches everywhere with a plain reload).
- **Category card (`admin/categories.php:304-353`):** photo carried 4 pills (badge tag, vacancy count, floating icon, hourly rate). Photo is now clean (gradient + overlays removed); icon sits inline with the title and tag/count/pay form a plain body metadata row. Verified: `Downloads/cat-card.png`.

---

# Round 7 — Back-to-top button (2026-09-11)

- New global `#back-to-top` (in `includes/footer.php`, so every page gets it): 40px circle bottom-right, 36px on mobile, paper styling via theme vars (dark-mode safe), appears after 600px of scroll, smooth-scrolls to hero (instant when `prefers-reduced-motion`). `aria-label` + focus ring included; sits at z-index 900, below modals.
- Verified: hidden at top, visible after scroll, click lands at `scrollY === 0`; mobile measures exactly 36×36 (`Downloads/backtotop-desktop.png`, `Downloads/backtotop-mobile.png`).

---

# Round 8 — Mobile dark navbar (2026-09-11)

- **Invisible hamburger:** Bootstrap's `.navbar-toggler-icon` is a hardcoded dark SVG (only `filter: invert(1)` patched it for dark mode — fragile). Replaced with a `bi-list` glyph in `includes/navbar.php`, which inherits `color: var(--ink)` and is visible in both modes by construction (`assets/css/custom.css:463`).
- **Menu alignment:** mobile `.nav-link` was `justify-content: space-between` (reads left). Now `flex-end` + `text-align: right` under the mobile breakpoint only — desktop grid untouched.
- Verified on 390px dark viewport: hamburger crisp, FIND JOBS / FAQS / ABOUT right-aligned (`Downloads/hamburger.png`, `Downloads/mobile-menu.png`).
- *Correction:* links must be centered, not right — mobile `.nav-link` now `justify-content: center` + `text-align: center` (desktop grid untouched).

---

# Round 9 — Spotlight results breathing room (2026-09-11)

- Search modal result cards were compressed (`10px 14px` padding, `10px` gaps, tight `py-2` footer).
- Loosened: item padding → `14px 16px`, list gap → `14px`, footer → `py-3` / `gap-3` (`assets/css/custom.css`, `includes/search-modal.php`).
- Verified on 390px dark viewport: `Downloads/spotlight-room.png`.
