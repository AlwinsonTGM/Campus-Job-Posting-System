# QA Coverage Report

What the agent-driven suite actually exercised, what it deliberately skipped,
and — importantly — what it **cannot** tell you.

**Run date:** 2026-10-02
**Target:** Campus Job Posting System (KLD Campus Hire)
**Method:** Playwright browser simulation, 5 agent teams + independent adversarial verification

---

## 1. Headline numbers

| Suite | Checks | Passed | Failed | Candidate bugs |
|---|---|---|---|---|
| `00-containment` (environment gate) | 8 | 6 | 2 | 1 (later resolved as environment-only) |
| `10-public` (visitor) | 167 | 141 | 26 | 5 |
| `20-lifecycle` (student + employer) | 95 | 80 | 15 | 4 |
| `30-admin-adversarial` | 75 | 70 | 5 | 6 |
| `01-bug01-characterization` | 4 | 4 | 0 | 1 |
| `02-bug03-sweep` (13-route sweep) | 1 | 0 | 1 | 1 |
| **Team total** | **350** | **301** | **49** | **18** |
| Independent verification probes | 27 | 27 | 0 | — |
| **Pinned regression suite** (`specs-regression/confirmed-bugs.mjs`) | 21 | **8** | 13 | **10 confirmed, 2 fixed** |

The regression suite is a **gate**: each check pins one confirmed bug. It
started at 2 passed / 19 failed. After the BUG-04 and BUG-05 fixes it stands at
**8 passed / 13 failed** — the two fixed bugs' checks flipped green and nothing
else moved.

**Of the 18 candidate bugs raised by the teams, 10 were confirmed and 4 main
claims were refuted** (plus test-defect failures discarded). Details in
`BUG_REPORT.md`.

> **Fix status: 2 of 10 fixed.** BUG-04 (unauthenticated dataset wipe) and
> BUG-05 (employer dashboard fatal) are fixed and verified by 12/12 + 6/6
> dedicated probes and a 9/9 non-regression sweep. The remaining 8 are open —
> see the status table in `BUG_REPORT.md`.

---

## 2. Routes exercised

### Public (anonymous)
`/index.php` · `/about-us.php` · `/faqs.php` · `/privacy.php` · `/terms.php` ·
`/login.php` · `/register.php` · `/forgot-pass.php` · `/reset-password.php` ·
`/verify-email.php` · `/updates.php` · `/update-detail.php` ·
`/student/jobs.php` · `/student/job-details.php` · `/api/search-jobs.php` ·
`/dbtest.php` · `/data-toggle.php`

### Student
`/student/dashboard.php` · `/student/jobs.php` · `/student/job-details.php` ·
`/student/apply.php` · `/student/my-applications.php` · `/settings.php` ·
`/notifications.php` · `/view-resume.php`

### Employer
`/employer/dashboard.php` · `/employer/create-job.php` · `/employer/edit-job.php` ·
`/employer/applicants.php` · `/employer/review-app.php` · `/employer/updates.php` ·
`/employer/reports.php`

### Admin
`/admin/users.php` · `/admin/categories.php` · `/admin/reports.php` ·
`/admin/updates.php` · `/admin/settings.php`

---

## 3. What was verified as working

These are positive results, not absences of testing:

- **Authorization holds under attack.** Cross-employer IDOR was probed from both
  directions with real CSRF tokens and a working control — the foreign job title
  and foreign application status were unchanged. Student→other-student resume
  access returns `403`; role escalation returns `302` to login.
- **CSRF on admin accreditation is fixed.** The state change is a POST carrying
  `action`, `id`, and a 64-char `csrf_token`; `?approve_id=` is explicitly
  rejected. (This was a finding in the previous security report.)
- **SQL injection produced nothing.** Three payloads across the login form, the
  job-search box, and the search API — no auth bypass, no SQL error text.
- **Stored and reflected XSS are handled.** The settings name field and the
  search keyword re-render escaped; the API sends `nosniff` + `application/json`
  and the front-end routes fields through `escapeHtml()`.
- **Search API input validation is solid.** `?q[]=`, empty, `%00`, 5,000-char,
  and `job_type[]=` all return clean JSON — it is the one place the array-param
  class was properly hardened.
- **Duplicate applications are blocked by the server**, not by a DB constraint
  error, leaving exactly one row per (job, student).
- **The full application lifecycle works**: apply → tracker → withdraw →
  re-apply, and a full-slot vacancy correctly refuses new applications.
- **Concurrency/state integrity**: sessions are isolated across browser contexts.
- **Responsive layout**: no horizontal overflow on `/index.php` or
  `/student/jobs.php` at 375 / 768 / 1440 px.
- **Sensitive files are protected by the shipped configuration.** Apache on
  port 80 returns `403` for `.env`, `data/*.json` (including `seeds/` and
  `.pristine_backup/`), `database/*.sql`, and the `.htaccess` files themselves.

---

## 4. Deliberate non-coverage

Stated plainly so the report is not read as broader than it is.

| Not covered | Why | Residual risk |
|---|---|---|
| **Real email delivery** | SMTP is blinded so no mail can be sent. The OTP *code path* is fully exercised (the code is generated, stored, rendered, submitted, and verified) but the SMTP handshake and PHPMailer delivery are not. | A delivery-only defect would not be caught. Do one manual send before your defense. |
| **AI / robot endpoints** (`api/robot-chat.php`, `api/ai-companion.php`, `api/robot-models.php`) | They call the paid NVIDIA API and are non-deterministic. | Not exercised at all beyond reachability. |
| **Load / performance / concurrency at scale** | Correctness-focused suite, single worker. The previous report's 50-request load test was not repeated. | No throughput or latency numbers in this report. |
| **The 7.45 MB hero GLB / Three.js rendering** | Recon found `index.php` loads a 7.45 MB model by default. Deliberately not asserted; `?mode_photo=1` is the flake-free path. | 3D asset loading is untested. |
| **Cross-browser** | Chromium only. | No Firefox/WebKit results. |
| **Registration → OTP → dashboard end-to-end** | **Blocked by BUG-01.** New registration crashes, so the journey cannot start. `verify-email.php` is unreachable through normal use because all 30 seeded accounts are pre-verified. | The OTP UI is only exercised via the containment probe's synthetic path. |
| **Brand-new employer registration** | Same BUG-01 root cause (permit path passed where an array is declared). | The `approved_partner` signup journey is unverified. |
| **`forgot-pass.php` → `reset-password.php` happy path** | The token is stored only as `sha256`, so the plaintext cannot be recovered, and the only delivery channel is email. | Only the invalid/expired-token branch is testable. |
| **Password change in `settings.php`** | Two probes returned `302` without persisting; the form's exact precondition (likely current-password re-entry) was not isolated. | Recorded as *inconclusive*, not as a bug. |

---

## 5. Environment caveats (not product bugs)

1. **PHP's built-in server ignores `.htaccess`.** The QA server runs on `:8099`,
   so `/data/*.json`, `/database/*.sql` and `/.env` return `200` there. Apache on
   `:80` returns `403` for all of them. Two teams initially reported this as
   critical; it is a test-server artefact and was verified in both directions.
2. **`C:\xampp\tmp` is unwritable here**, which breaks `session_start()` and
   PHP's upload staging. Both are redirected by `qa/serve-qa.cmd`. Before the
   fix, *every* login failed and *every* upload reported "Failed to persist
   Certificate of Registration to storage" — neither was a product defect.
3. **The QA harness runs unconfined.** The sandbox blocks browser launch
   (`spawn EPERM`), so the runner needs full access. `qa/.htaccess` denies web
   access to the harness directory, which holds real SMTP credentials, raw
   session files, and database dumps.

---

## 6. Reproducing this run

```cmd
REM 1. isolated app server (port 8099, test DB, SMTP blinded)
qa\serve-qa.cmd

REM 2. any team, or the pinned regression gate
qa\run-batch.cmd qa\teams\10-public.mjs
qa\run-batch.cmd qa\teams\20-lifecycle.mjs
qa\run-batch.cmd qa\teams\30-admin-adversarial.mjs
qa\run-batch.cmd qa\specs-regression\confirmed-bugs.mjs
```

Every batch prints a containment verdict, so you can confirm your live database
was untouched:

```
CONTAINMENT: PASS - live database untouched (delta 0).
ISOLATION:   PASS - test database received the new data (+3).
```

Fixtures reset at any time with `login.php?reset=1` (test database only).

---

## 7. How much to trust this

**High confidence** — the 10 confirmed bugs. Each has a deterministic
reproduction, and the security-relevant ones were re-verified by a *different*
agent or by direct measurement.

**Medium confidence** — the positive security results. They held under
deliberate attack with working controls, but "no bypass found" is not "no bypass
exists"; the adversarial team was one agent with one hour, not a fuzzer.

**Not verified** — anything in §4.

**Explicitly wrong at first, then corrected** — worth knowing because it shows
the verification step earned its place:
- A team reported a **critical** authorization bypass on
  `/student/my-applications.php`. Refuted: the detector matched the string
  `my-applications` inside a harmless login-page link.
- A team reported a **high** cross-employer IDOR. Refuted: its only "evidence"
  was `status=""`, which is pre-existing corrupt seed data (BUG-06), not an
  unauthorized write. An independent probe with a valid CSRF token and a working
  control confirmed authorization holds.
- Two "API XSS" failures were defects in the test's own code.
