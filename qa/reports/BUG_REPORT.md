# Agent-Driven QA: Confirmed Findings

**Target:** Campus Job Posting System (KLD Campus Hire)
**Method:** Playwright browser simulation driven by autonomous agents, with independent adversarial re-verification of every candidate bug.
**Environment:** isolated test cluster (see *Test environment* below) — no email dispatched, live database never written to.

---

## Test environment

The suite runs against a deliberately isolated instance so that destructive and
repetitive testing is safe.

| Control | Mechanism | Verified by |
|---|---|---|
| Separate database | `qa/serve-qa.cmd` pins `DB_NAME=campus_job_portal_e2e` (a clone) | `SELECT DATABASE()` → `campus_job_portal_e2e` |
| No email can be sent | `MAIL_USERNAME=` / `MAIL_PASSWORD=` blanked for the run; `load_env()` only applies a `.env` value when `!getenv($key)`, so the process value wins | `is_smtp_configured()` → `false`; `send_campus_email()` → `false` without opening SMTP |
| OTP still testable | Because SMTP is unconfigured, `SessionGuard::quarantineForVerification()` stores the code in `$_SESSION['pending_verification']['dev_code']` and `verify-email-view.php` renders it on screen | full 6-digit OTP journey executed in-browser |
| Live data untouched | Every batch snapshots row counts for both databases before and after | live `campus_job_portal` delta `0` on every run |
| Reproducible fixtures | `login.php?reset=1` re-seeds from `data/seeds/demo/*.json` into the **test** DB only | probe row inserted → `?reset=1` → probe row gone, test DB still 30 users, live DB unchanged |

**Why the harness is bespoke:** this environment blocks browser launch under the
confined sandbox (`browserType.launch: spawn EPERM`), and `@playwright/test`'s
runner additionally forks workers with piped stdio (`WorkerHost.startRunner:
spawn EPERM`). The harness therefore launches Chromium in-process and asserts
directly (`qa/lib/harness.mjs`) — same browser, same selectors, no runner.

---

## Findings

**10 confirmed bugs.** Every one has a deterministic reproduction and a pinned
regression check in `qa/specs-regression/confirmed-bugs.mjs`.

> ### Fix status
> **BUG-01, BUG-04, and BUG-05 are FIXED and independently verified** — 10/10,
> 12/12, and 6/6 checks, plus the 13/13 containment suite and 9/9 non-regression
> sweep; all three now pass in the pinned regression gate. The remaining 7 bugs
> are tracked below.

| # | Severity | Bug | Route | Auth needed | Status |
|---|---|---|---|---|---|
| [BUG-01](#bug-01--critical--student-registration-crashes-with-an-uncaught-typeerror--fixed) | **CRITICAL** | Student registration crashes — feature entirely dead | `POST /register.php:145` | none | **FIXED** |
| [BUG-04](#bug-04--critical--unauthenticated-get-wipes-the-entire-dataset--fixed) | **CRITICAL** | Unauthenticated GET wipes the whole dataset | `/data-toggle.php` | **none** | **FIXED** |
| [BUG-05](#bug-05--critical--employer-dashboard-and-job-editor-fatal-error-mid-render--fixed) | **CRITICAL** | Employer dashboard + job editor fatal mid-render | `/employer/dashboard.php`, `/employer/edit-job.php` | employer | **FIXED** |
| [BUG-03](#bug-03--high--array-query-parameters-crash-7-routes-with-uncaught-typeerror) | **HIGH** | Array query params crash 7 routes, leak server paths | 7 routes (3 need no auth) | mixed | open |
| [BUG-02](#bug-02--low--failed-registrations-leave-orphaned-files-on-disk) | LOW | Failed registrations orphan files on disk | `POST /register.php` | none | open |
| [BUG-06](#bug-06--medium--seed-data-violates-its-own-status-enum-producing-blank-and-misleading-states) | MEDIUM | Seed data violates its own `status` ENUM | `/student/my-applications.php` | student | open |
| [BUG-07](#bug-07--medium--listing-filters-advertise-values-that-match-no-vacancy) | MEDIUM | Listing filters match no vacancy | `/student/jobs.php` | none | open |
| [BUG-08](#bug-08--medium--dbtestphp-is-publicly-reachable-and-discloses-internals) | MEDIUM | `dbtest.php` publicly discloses DB internals | `/dbtest.php` | **none** | open |
| [BUG-09](#bug-09--low--about-usphp-requests-a-devblog-image-that-does-not-exist) | LOW | Broken devblog image | `/about-us.php` | none | open |
| [BUG-10](#bug-10--low--faq-accordion-can-be-closed-to-a-state-where-nothing-is-open) | LOW | FAQ accordion closes to an empty state | `/faqs.php` | none | open |

**Remaining fix order:** BUG-03 → the rest. BUG-03 leaks server paths on three
unauthenticated routes.

---

### BUG-01 — CRITICAL — Student registration crashes with an uncaught `TypeError` ✅ FIXED

**Route:** `POST /register.php` (call site line 145) → `includes/services/user-service.php:736`
**Role:** public / student
**Status:** CONFIRMED, then **FIXED and verified**

> **Fix applied.**
> 1. Parameter type hints in `register_user()` corrected from `?array $permit_file`
>    and `?array $proof_file` to `?string $permit_file = null, ?string $proof_file = null`.
>    The callers pass relative stored file paths from `AttachmentStore`, and the
>    persistence functions (`insert_student_profile`, `insert_employer_profile`) expect
>    `?string`.
> 2. Error handling broadened to `catch (Throwable $e)` so any future type or
>    runtime errors inside the registration transaction trigger a clean rollback
>    rather than escaping as unhandled 500-fatal crashes.
> 3. QA mail gate added (`registration_mail_allowed()` in `includes/auth-check.php`
>    and `includes/mailer.php`) so QA/test environments setting `QA_MAIL_DISABLED=1`
>    safely route OTP codes to on-screen presentation without dispatching outbound
>    emails.
>
> **Verification** — `qa/verification/verify-bug01-fix.mjs` (10/10), `qa/teams/00-containment.mjs`
> (13/13), and `qa/specs-regression/confirmed-bugs.mjs` (4/4 for BUG-01): student registration
> wizard completes cleanly, redirects 302 without PHP fatal, persists both the `users`
> row and `student_profiles` row with COR path, displays on-screen OTP without sending mail,
> successfully verifies OTP to the student dashboard, and handles duplicates cleanly.

**Impact.** No new student account can be created, at all, through the UI. The
crash occurs on every submission that reaches persistence. Because the failure
is an uncaught `Error` (not `Exception`), the user receives a raw PHP fatal
error page instead of a redirect — so this is both a broken feature and an
information-disclosure issue (absolute server paths are printed to the browser).

**Reproduction.**
1. Open `/register.php` and complete all three wizard steps as a student.
2. Attach the mandatory Certificate of Registration / Student ID document.
3. Submit.

**Expected.** Redirect to `/verify-email.php` with a pending-verification session.

**Actual.** `HTTP 200` containing:
```
Fatal error: Uncaught TypeError: register_user(): Argument #3 ($proof_file)
must be of type ?array, string given, called in
...\register.php on line 145 and defined in
...\includes\services\user-service.php:736
```

**Root cause.** The declaration and the call disagree about types:

```php
// includes/services/user-service.php:736
function register_user(array $data, ?array $permit_file = null, ?array $proof_file = null): array {

// register.php:145 — passes file PATHS
$res = register_user([...], $permit_file_path, $proof_file_path);
```

`$permit_file_path` / `$proof_file_path` are produced by
`AttachmentStore::storePermit()` / `::storeProof()`, whose success value is
`StorageResult::path()` — a `string` — or `null`. The body then uses them as
paths, not upload arrays:

```php
// user-service.php:766-767
$proof_path  = $proof_file  ?? ($data['proof_file']  ?? null);
$permit_path = $permit_file ?? ($data['permit_file'] ?? null);
```

and hands them straight to functions that already expect a **string**:

```php
insert_student_profile(..., ?string $proof_path)          // line 774
insert_employer_profile(..., ?string $permit_path)        // line 776
```

So the type hint is wrong, not the caller. The sibling function
`create_profile_request(..., array|string|null $proof_file = null, ...)`
(line 917) accepts a string, confirming the intended contract.

**Suggested fix.**
```diff
- function register_user(array $data, ?array $permit_file = null, ?array $proof_file = null): array {
+ function register_user(array $data, ?string $permit_file = null, ?string $proof_file = null): array {
```

**Secondary defect (same failure).** `register_user()` wraps its body in
`catch (Exception $e)`, but `TypeError` extends `Error`, not `Exception`
(`user-service.php:797`). Any type/argument error therefore escapes the handler
and reaches the client as a fatal error page rather than a clean message.
Widening to `catch (Throwable $e)` would contain this class of failure.

**Blast radius.** Employer registration passes the same arguments
(`register_user([...], $permit_file_path, $proof_file_path)`), so
`approved_partner` / `university_office` signup crashes identically unless no
permit is uploaded — then `null` is passed and the call succeeds. Student
registration is unconditionally broken because the COR document is mandatory
server-side (`AttachmentStore::storeProof()` returns an error if absent).

**Evidence.** `qa/artifacts/bug01/student-register-fatal.html`,
`qa/artifacts/bug01/bug01-register-user-fatal.json`

---

### BUG-02 — LOW — Failed registrations leave orphaned files on disk

**Route:** `POST /register.php` → `includes/services/attachment-store.php`
**Role:** public
**Status:** CONFIRMED

**Impact.** The upload is persisted to `uploads/proofs/` *before*
`register_user()` is called. When registration then fails, no database row is
created, so the file is orphaned — unreferenced by any record and never cleaned
up. Every failed attempt leaks one file. The directory also accumulates these
over time.

**Evidence.** Four 70-byte files were written by the BUG-01 reproduction runs:

```
uploads\proofs\proof_1790906637_d6f3ce2d.png   70 bytes   10/2/2026 10:03:57 AM
uploads\proofs\proof_1790906610_712e6a1a.png   70 bytes   10/2/2026 10:03:30 AM
uploads\proofs\proof_1790906596_a468f4a2.png   70 bytes   10/2/2026 10:03:16 AM
uploads\proofs\proof_1790906556_9492900e.png   70 bytes   10/2/2026 10:02:36 AM
```

Pre-existing orphans from 9/26 and 9/30 show this is not new.

**Suggested fix.** Persist attachments *after* the database transaction commits,
or delete the stored file in a rollback path when registration fails.

---

### BUG-03 — HIGH — Array query parameters crash 7 routes with uncaught `TypeError`

**Routes (7 of 13 probed):**
| Route | Auth needed | Failing call |
|---|---|---|
| `/student/job-details.php?id[]=1` | **none** | `get_job_by_id(): Argument #1` |
| `/update-detail.php?id[]=1` | **none** | `get_career_update_by_id(): Argument #1` |
| `/student/jobs.php?job_type[]=a` | **none** | `trim(): Argument #1` |
| `/student/apply.php?job_id[]=1` | student | `get_job_by_id(): Argument #1` |
| `/view-resume.php?user_id[]=1` | student | `can_view_student_resume(): Argument #2` |
| `/employer/edit-job.php?id[]=1` | employer | `get_job_by_id(): Argument #1` |
| `/employer/review-app.php?id[]=1` | employer | `get_application_by_id(): Argument #1` |

**Status:** CONFIRMED

**Impact.** Two things at once:
1. **Unhandled 500-class failure.** `HTTP 200` carrying a raw PHP fatal, so the
   failure is invisible to status-code monitoring while being completely broken
   to a user.
2. **Information disclosure.** Every fatal prints absolute server paths, e.g.
   `C:\xampp\htdocs\Final-Campus-Job-Posting-System\includes\...` — useful
   reconnaissance for an attacker. Three of the routes need **no
   authentication**, so this is reachable by anyone.

**Reproduction.** Append `[]` to any id-like query parameter:
```
GET /student/job-details.php?id[]=1     → <b>Fatal error</b>: Uncaught TypeError:
  get_job_by_id(): Argument #1 ($id) must be of type string|int|null, array given
```

**Root cause.** PHP parses `?id[]=1` as an array. Each route passes
`$_GET['id']` straight into a function whose parameter is typed
`string|int|null`, or into `trim()`, neither of which accepts an array.

**Why this is notable.** The project *already fixed this exact class once* — for
the search API only (report `SEC-08`). The guard exists in one file and nowhere
else:

```php
// api/search-jobs.php:13   ← hardened
$query = is_string($raw_q) ? trim($raw_q) : '';

// student/jobs.php:15      ← not hardened
$job_type = trim($_GET['job_type'] ?? '');
```

`/api/search-jobs.php?q[]=a` returns clean JSON; `/student/jobs.php?job_type[]=a`
crashes. The remediation was applied at one call site instead of at the seam
where query input is read.

**Suggested fix.** Centralize the coercion rather than repeating `is_string()`
at every caller — e.g. a single accessor used by all routes:

```php
function query_string(string $key, string $default = ''): string {
    $v = $_GET[$key] ?? null;
    return is_string($v) ? trim($v) : $default;
}
function query_int(string $key, ?int $default = null): ?int {
    $v = $_GET[$key] ?? null;
    return (is_string($v) || is_int($v)) && $v !== '' ? (int)$v : $default;
}
```

Then `get_job_by_id(query_int('id'))` on every route. Additionally, converting
the `TypeError`s into a handled 404/redirect would stop the path disclosure even
if a call site is missed.

**Evidence.** `qa/artifacts/bug03/array-param-sweep.json`,
`qa/artifacts/admin-adversarial/30-admin-adversarial.json`

---

### BUG-04 — CRITICAL — Unauthenticated GET wipes the entire dataset ✅ FIXED

**Route:** `/data-toggle.php`
**Role:** **anonymous — no login required** (as found)
**Status:** CONFIRMED, then **FIXED and verified**

> **Fix applied.** `data-toggle.php` now enforces, in order:
> 1. `require_auth(['admin'])` — administrators only.
> 2. **POST only** — a GET returns `405 Method Not Allowed`.
> 3. CSRF token **always** verified (previously skipped whenever the token was
>    absent, and unreachable entirely on GET).
> 4. `DatastoreManager::isToggleAvailable()` — the capability disappears when
>    `APP_ENV=production`.
> 5. `action`/`mode` read from `$_POST` **only**; the query string is no longer
>    trusted. The redirect target is restricted to a same-origin referer (or a
>    relative fallback), closing a latent open redirect.
>
> The footer modal and the admin settings control now render only when the
> capability is available **and** the viewer is an administrator.
>
> **Verification** — `qa/verification/verify-bug04-datatoggle-fix.mjs`, 12/12:
> all six original attack URLs now redirect to login with the dataset intact;
> an authenticated **student** is refused; an admin POST with a **bad CSRF
> token** is refused; an admin POST with a **valid token still works**; demo
> fixtures restore cleanly. A separate 9/9 non-regression sweep confirms public
> pages still render and the switcher is offered to nobody but admins.

---

**Impact, reproduction and root cause (as found).**


**Route:** `/data-toggle.php`
**Role:** **anonymous — no login required**

**Impact.** Any unauthenticated visitor can destroy the whole dataset with a
single GET request. There is no login, no role check, and no CSRF token
involved. The same endpoint also resets or re-seeds the dataset at will.

**Reproduction (no cookie, no token, not logged in).**
```
GET /data-toggle.php?action=switch_mode&mode=real
```

Measured on the isolated cluster:

| | users | jobs |
|---|---|---|
| before | 30 | 20 |
| **after the anonymous GET** | **0** | **0** |

The live database was untouched (20 jobs before and after) because the probe ran
against the clone — which is exactly why this was safe to prove.

Equivalent payloads, all accepted by GET:
`?action=switch_mode&mode=real`, `?mode=real` (the `action` is not even needed),
`?action=wipe_real`, `?action=reset`, `?action=reset_current`.

**Root cause.**

```php
// data-toggle.php:9-11 — reads from GET as well as POST
$action     = $_POST['action']     ?? $_GET['action']     ?? '';
$mode       = $_POST['mode']       ?? $_GET['mode']       ?? '';
$csrf_token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

// data-toggle.php:15 — the CSRF check is unreachable for GET,
// and is skipped even on POST when the token is empty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($csrf_token)) {
    if (!verify_csrf_token($csrf_token)) { ... }
}

// data-toggle.php:23 — no require_auth(), no role check anywhere in the file
if ($action === 'switch_mode' || !empty($mode)) {
    ...
    DatastoreManager::switchMode($target_mode, $userName);   // rewrites all data
```

`$userName` is set from the session purely as an *audit label*
(`is_logged_in() ? ... : 'User'`) — it is never used to authorise. The two
conditions that make this exploitable are independent: the missing auth guard
**and** the GET-reachable CSRF bypass. Either one alone would be serious.

**Aggravating factor — discoverability.** The endpoint is reachable from every
page, including fully public ones: `includes/footer.php` renders a
`#dataModeModal` dataset switcher on *every* page. An attacker does not need to
guess the URL; it is advertised in the markup. The UI only ever submits a
CSRF-protected POST, so a form-filling crawler would look safe — the
vulnerability is in hitting the URL directly.

```php
require_auth(['admin']);                       // 1. authorise first

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {   // 2. POST only
    http_response_code(405);
    exit('Method Not Allowed');
}
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {   // 3. always verify
    set_flash('danger', 'Security verification failed.');
    header('Location: ' . $referer);
    exit;
}
```
Also stop reading `$action`/`$mode` from `$_GET` entirely. Consider gating the
switcher behind `APP_ENV !== 'production'` so it cannot exist in a deployed
build.

**Public-exposure note.** `tools/start-tunnel.bat` exposes this site through a
Cloudflare tunnel. If that tunnel is ever running, this is a remote,
unauthenticated data-destruction vector reachable by anyone with the URL.

**Evidence.** `qa/artifacts/bug04/bug04-data-toggle.json`

---

### BUG-05 — CRITICAL — Employer dashboard and job editor fatal-error mid-render ✅ FIXED

**Routes:** `/employer/dashboard.php`, `/employer/edit-job.php`
**Role:** employer
**Status:** CONFIRMED, then **FIXED and verified**

> **Fix applied.** All three `csrf_field()` call sites now use the same
> pattern as the other ~40 templates: `<?= generate_csrf_token() ?>`.
> Because the call sat inside the vacancy loop, the fatal fired on the
> first row and truncated the page; the dashboard now renders every
> vacancy the employer owns.
>
> **Verification** — `qa/verification/verify-bug05-fix.mjs`, 6/6: both
> screens render without a fatal, **both** vacancies (2 and 3) are listed
> instead of one, every form still carries a 64-char CSRF token, and the
> edit page shows the correct title. The pre-defense smoke test surfaced
> this on the live Apache server, which is how it was caught end to end.

**Impact.** Both core employer screens crash with an uncaught fatal. Because the
call sits **inside the vacancy table loop**, the dashboard dies *on the first
row* and silently truncates everything after it — so the page looks partly fine
while the employer's remaining vacancies and all their action buttons are simply
gone.

Most instructive symptom: an employer who owns **two** active vacancies sees
only **one** (`job 3`), and nothing on the page suggests anything is missing.

```
Fatal error: Uncaught Error: Call to undefined function csrf_field()
  in includes/templates/employer-dashboard-view.php:191
Stack trace:
  #0 employer/dashboard.php(79): require()
```

**Reproduction.**
1. Log in as an employer (`/login.php?demo=employer`).
2. Open `/employer/dashboard.php` → fatal at first vacancy row; only one
   `edit-job.php?id=` link exists in the rendered HTML though the account owns two.
3. Open `/employer/edit-job.php?id=2` (or `3`) → fatal at line 304.

Measured: `dashboard.php` returned `200` with 22,002 bytes and the fatal text;
`edit-job.php?id=2` → 41,546 bytes with the fatal.

**Root cause.** `csrf_field()` is **called but never defined anywhere in the
codebase**. Only `generate_csrf_token()` and `verify_csrf_token()` exist
(`includes/services/user-service.php:91,100`).

Every other template in the project emits the token through the real helper —
roughly 40 call sites — so these three are stragglers:

| File | Line | Context |
|---|---|---|
| `includes/templates/employer-dashboard-view.php` | 191 | inside the vacancy loop — **kills the page** |
| `includes/templates/employer-dashboard-view.php` | 201 | archived-restore form (currently unreachable) |
| `includes/templates/employer-edit-job-view.php` | 304 | hidden `#archive-job-form` — kills the page |

`employer-dashboard-view.php` contains **zero** `generate_csrf_token()` calls —
it is the only template that relies on the missing helper.

**Suggested fix.** Match the rest of the codebase, or define the helper once:
```diff
- <?= csrf_field() ?>
+ <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
```
```php
// or, centrally in includes/components.php:
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(generate_csrf_token(), ENT_QUOTES) . '">';
}
```
Add a lint/CI check that fails on any call to an undefined function — this class
of bug should never reach a browser.

**Evidence.** `qa/artifacts/verify-emp-dash/employer-dashboard.html`,
`qa/artifacts/verify-emp-dash/verify-employer-dashboard-fatal.json`

---

### BUG-06 — MEDIUM — Seed data violates its own `status` ENUM, producing blank and misleading states

**Route:** `/student/my-applications.php` (anywhere `applications.status` is read)
**Status:** CONFIRMED

**Impact.** `applications.status` is
`ENUM('pending','under_review','interview_scheduled','accepted','declined','withdrawn')`,
but `data/seeds/demo/applications.json` stores `'reviewed'` (app 18) and
`'rejected'` (app 21). MySQL coerces both to the **empty string** on import
(`''` is the ENUM's implicit error value, since strict mode is off).

Consequences:
- The tracker renders those rows with an **empty status pill** and no stage.
- The stepper paints stage 1 ("Pending Review") active, so the UI asserts a
  stage the database does not hold — actively misleading.
- Those rows match no status filter, so they are invisible to every stage tab.

Measured in the test DB:
```
| status              |  n |
| accepted            | 11 |
| interview_scheduled |  5 |
| pending             |  4 |
|                     |  2 |   <-- empty string
| under_review        |  1 |
applications with status='': ids 18, 21
```

**Why this matters beyond cosmetics.** This data defect *caused a false
positive during this very QA run*: a team read the resulting `status=""` as
evidence of a successful cross-employer IDOR write. It was nothing of the kind
(see the refuted section). Corrupt fixture state generates phantom security
findings.

**Suggested fix.** Either add the missing values to the ENUM, or map the seed
values to real ones (`reviewed → under_review`, `rejected → declined`). Turn on
strict mode so an out-of-range ENUM value fails loudly instead of silently
becoming `''`.

---

### BUG-07 — MEDIUM — Listing filters advertise values that match no vacancy

**Routes:** `/student/jobs.php` (Job Type dropdown + quick-filter chips)
**Status:** CONFIRMED

**Impact.** The page offers two filters that are guaranteed to return nothing, so
a visitor who uses the UI as presented gets "No matching opportunities found"
for a category the page itself advertised. It reads as an empty job board.

**Reproduction.**
1. Open `/student/jobs.php` (20 active vacancies).
2. Set **Job Type = "Part-Time Job"** → 0 results, although 5 active vacancies
   store `job_type='Part-Time'` (ids 5, 6, 15, 16, 20).
3. Click the **"Peer Tutor"** quick-filter chip → 0 results; the
   **"Peer Tutor" category** chip on the same page → 2 results (jobs 4, 11).

**Root cause.** The filter vocabulary and the stored data disagree, and one chip
queries the wrong column.

```php
// includes/services/job-service.php - get_job_types() offers:
'Part-Time Job', 'Peer Tutor', 'Internship / OJT', 'Project-Based' ...
// but jobs.job_type only ever contains:
'Part-Time', 'Student Assistant'
```
The match is `j.job_type LIKE '%<value>%'`, so `'Part-Time Job'` and
`'Peer Tutor'` match no row.

The chip is the clearer defect — it filters the wrong field entirely:
```html
<!-- student-jobs-view.php:286  filters job_type ... -->
<a href="jobs.php?job_type=Peer+Tutor" ...>
<!-- ...:289  while the equivalent, working chip correctly uses category -->
<a href="jobs.php?category=Science+%26+Computer+Lab+Assistant" ...>
```
Jobs 4 and 11 sit in `categories.name = 'Peer Tutor'` but carry
`job_type = 'Student Assistant'`.

**Suggested fix.** Drive the filter options from the data
(`SELECT DISTINCT job_type FROM jobs WHERE status='active' AND is_archived=0`)
rather than a hardcoded list, and change the Peer Tutor chip to
`?category=Peer Tutor` like its sibling.

---

### BUG-08 — MEDIUM — `dbtest.php` is publicly reachable and discloses internals

**Route:** `/dbtest.php`
**Role:** anonymous
**Status:** CONFIRMED on the shipped Apache configuration (`:80`)

**Impact.** An unauthenticated diagnostic script is deployed in the web root. It
prints server internals and confirms live database access.

Measured anonymously over Apache:
```
=== PATH CHECKS ===
Path 2 (DOCUMENT_ROOT): C:/xampp/htdocs/.env   exists: NO   readable: NO
...
Attempting: mysql:host=127.0.0.1;dbname=campus_job_portal as root
PDO CONNECTION: SUCCESS!
Users table rows: 30
=== open_basedir ===
```

Disclosed: the document root, `.env` probing results, the DB host/name/user, a
successful connection as **root**, and live row counts. That is a ready-made
reconnaissance report for an attacker and a standing confirmation that the
database is reachable.

**Suggested fix.** Delete `dbtest.php` (it is a development leftover), or gate
it behind `APP_ENV !== 'production'` plus an admin session. Also review the
other development leftovers in the web root.

---

### BUG-09 — LOW — `/about-us.php` requests a devblog image that does not exist

**Status:** CONFIRMED

**Impact.** A broken image for visitors. The carousel only preloads the covers
it displays, so an `<img>`-based check passes while the request still 404s —
it was caught by watching network traffic, not the DOM.

```
404  GET /assets/img/devblog/day-25.jpg
```
`data/devblogs.json` references `day-01` … `day-25`, but
`assets/img/devblog/` contains only `day-01` … `day-24`.

**Suggested fix.** Add the missing image, or stop referencing day 25.

---

### BUG-10 — LOW — FAQ accordion can be closed to a state where nothing is open

**Route:** `/faqs.php`
**Status:** CONFIRMED (reproduced consistently across repeated trials)

**Impact.** Clicking the question that is **already open** closes it and opens
nothing else, leaving every panel collapsed. In an accordion group
(`data-bs-parent`) the documented behaviour is that one panel stays open — the
user is left with a wall of closed questions and no indication of where they
were.

Measured across three fresh page loads:
```
before : open=01000000000     (Q1 open by default)
click Q1 (already open) -> open=00000000000   <-- everything closed
click Q2 (closed)       -> open=00100000000   <-- works
click Q3 (closed)       -> open=00010000000   <-- works
```
ARIA stays truthful (`aria-expanded` tracks the panel), so this is a behaviour
defect rather than an accessibility-state desync.

**Suggested fix.** Ensure the Bootstrap collapse group is wired with
`data-bs-parent` so a panel is always active, or guard the toggle so the last
open panel cannot be closed.

---

## Note on environment-only failures (NOT product bugs)

Recorded so they are not mistaken for defects, and so the suite is not
misread as passing/failing for the wrong reason.

1. **`session_start()` failed → blocked every login.** `C:\xampp\tmp` is not
   writable in this environment, so PHP could not write session files. Fixed by
   pointing `session.save_path` at a writable directory in the launcher.
2. **"Failed to persist Certificate of Registration to storage" on every
   upload.** Same root cause: PHP's `upload_tmp_dir` was also `C:\xampp\tmp`,
   so `move_uploaded_file()` failed. Fixed via `-d upload_tmp_dir=...`.
   `uploads/` itself was writable throughout.
3. **`.htaccess` protections do not apply on `127.0.0.1:8099`.** PHP's built-in
   server ignores `.htaccess`, so a `200` for `/data/*.json` there is an
   artefact of the test server, not evidence that Apache (port 80) is exposed.

---

## Verified as still holding (previous hardening re-tested)

The adversarial team independently re-tested the fixes from the earlier
security report. These **passed** and are not regressions:

| Control | Probe | Result |
|---|---|---|
| Resume PII / IDOR | student → `/view-resume.php?user_id=2` (also with `&render_html=1`) | `403 Access Denied: You are not authorized to inspect other student records.` |
| Resume authorization | employer → resume of a student who never applied to them | `403 Access Denied: You are not authorized to view this candidate credential.` |
| Cross-employer job edit (IDOR) | employer → `/employer/edit-job.php?id=<other employer's job>` | blocked |
| Cross-employer candidate review | employer → `/employer/review-app.php?id=<other employer's application>` | blocked |
| Role escalation | student → `/employer/applicants.php`, `/employer/edit-job.php`, `/admin/users.php` | all `302` to login |
| CSRF on accreditation | observed request when approving an employer | `POST` with `action`, `id`, and a 64-char `csrf_token` — **no longer a GET** |
| SQL injection | `' OR '1'='1`, `%'--`, `' OR 1=1 -- -` via login form, job search, and search API | no auth bypass, no SQL error leakage |
| XSS (stored) | `<script>` and `"><img onerror>` in the settings name field | stored and re-rendered escaped, never executed |
| XSS (reflected) | both payloads in the job-search keyword | not reflected unescaped |
| Path traversal | `/view-resume.php?file=../../.env` (+ URL-encoded, + `render_html=1`) | `403`, no secret material |
| Session isolation | second context logs in as another role | first context's session unaffected |

---

## Refuted / not a bug (verification outcomes)

Recorded so the filtering is visible rather than silent.

1. **"Anonymous visitor is not blocked from `/student/my-applications.php`"** —
   reported **critical** by the public-visitor team. **REFUTED.** Following the
   redirect lands on the login page (`<title>Sign In to Campus Hire</title>`,
   30,044 bytes) and no protected content renders. The detector matched the
   substring `my-applications`, which occurs in ordinary page markup. The raw
   response also leaked no protected markup.
2. **`/data/*.json`, `/database/*.sql`, `/.env` served with HTTP 200** —
   reported **high/critical** by two teams. **REFUTED as a product bug, real as
   an environment artefact.** The QA server is PHP's built-in server, which does
   not read `.htaccess`. On Apache (port 80, the configuration this app actually
   ships for) the identical paths return **`403`**, including
   `/data/seeds/demo/users.json` and `/data/.pristine_backup/users.json`.
   Verified in both directions.
3. **"API XSS probe did not complete"** — two `FAIL`s. **REFUTED as a test
   defect**, not app behaviour: the probe threw
   `Cannot read properties of undefined (reading 'get')` in its own code.

---
