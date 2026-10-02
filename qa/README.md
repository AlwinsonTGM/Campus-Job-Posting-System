# QA Harness — Agent-Driven Browser Testing

Browser-driven test harness for the Campus Job Posting System. It simulates
real user journeys with Playwright and asserts on what actually happens, with
two hard guarantees:

1. **No email is ever sent.** OTP codes render on screen instead.
2. **Your live database is never written to.** Tests run against a clone.

---

## Why this harness is bespoke

The stock `@playwright/test` runner does not work in this environment:

| Blocker | Symptom |
|---|---|
| Runner forks workers with piped stdio | `WorkerHost.startRunner` → `spawn EPERM` |
| Sandbox blocks browser launch | `browserType.launch` → `spawn EPERM` |

So the harness launches Chromium **in-process** and asserts directly. Same
browser, same selectors — only the runner is replaced.

---

## Quick start

```cmd
REM 0. before presenting: confirm the demo is intact and the switcher is locked
qa\smoke-test.cmd

REM 1. start the isolated app server (port 8099)
qa\serve-qa.cmd

REM 2. in another terminal, run a batch
qa\run-batch.cmd qa\teams\00-containment.mjs
qa\run-batch.cmd qa\specs-regression\confirmed-bugs.mjs
```

`smoke-test.cmd` is the one to run before a demo or defense. It checks the demo
dataset counts, that all three roles can sign in, that every key page renders
without a PHP error, that the dataset switcher is locked to admins, and that
live SMTP is configured. It exits non-zero on any failure.

`run-batch.cmd` snapshots both databases before and after, and prints a
containment verdict:

```
CONTAINMENT: PASS - live database untouched (delta 0).
ISOLATION:   PASS - test database received the new data (+1).
```

---

## How isolation is achieved

| Control | Mechanism |
|---|---|
| Separate database | `serve-qa.cmd` sets `DB_NAME=campus_job_portal_e2e` |
| No email | `MAIL_USERNAME=` / `MAIL_PASSWORD=` set empty for the process |

Both rely on one property of `includes/ai/env.php`:

```php
if (!getenv($key)) { putenv("$key=$val"); }   // .env only fills UNSET vars
```

A process-level value therefore always beats `.env`, without editing any
application file.

Because `is_smtp_configured()` then returns `false`,
`SessionGuard::quarantineForVerification()` stores the OTP in
`$_SESSION['pending_verification']['dev_code']` and `verify-email-view.php`
renders it on screen. The **entire 6-digit OTP flow is still exercised** — only
the email transport is removed.

---

## Layout

```
qa/
  serve-qa.cmd          start the isolated server (:8099)
  run-batch.cmd/.ps1    run harness scripts + verify containment
  lib/
    harness.mjs         browser + assertion primitives
    register.mjs        3-step registration wizard driver
  teams/                one script per QA team
  specs-regression/     one spec per confirmed bug (fails until fixed)
  reports/              BUG_REPORT.md, COVERAGE.md
  artifacts/            screenshots, HTML captures, JSON results
  backups/              database dump taken before the run
  env-backup/           original .env + its SHA-256
  sessions/             PHP session files (redirected from C:\xampp\tmp)
  uploads-tmp/          PHP upload staging (redirected from C:\xampp\tmp)
```

---

## Writing a new team script

```js
import * as path from 'node:path';
import { launch, watchErrors, Results, QA_BASE, httpGet, sleep } from '../lib/harness.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'myteam');
const res = new Results('myteam');

const { browser, page } = await launch();
const errors = watchErrors(page);   // collects pageerror / console.error / 5xx

try {
  await page.goto('/login.php?demo=student');       // role login hook
  res.check('dashboard renders', (await page.title()).length > 0);
  // ... more checks ...
  res.finding({                                    // only with real evidence
    title: '...', severity: 'high', route: '/x.php', role: 'student',
    steps: ['...'], expected: '...', actual: '...', evidence: [],
  });
  res.save(ART);
} catch (e) {
  res.check('completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await browser.close();
}
```

**Never let an app defect throw out of the script.** A failed assertion is a
result; a crashed script loses every result after it.

### Rules learned the hard way

- **Scope clicks to the visible pane.** The registration wizard keeps earlier
  panes in the DOM, so `.btn-step-next` matches a hidden button first. Use
  `.reg-step-pane.is-visible .btn-step-next`.
- **Verify every fill.** `page.fill()` can silently no-op; read the value back.
- **Read the right error element.** `#step-error-message` is the *client* box
  and always contains default text. The server's message is in
  `#server-error-alert`.
- **File inputs need `setInputFiles`**, and a server reload wipes them.

---

## Fixtures

`login.php?reset=1` re-seeds the **test** database from `data/seeds/demo/*.json`.
Use it for a clean slate:

```js
import { resetDatastore } from '../lib/harness.mjs';
await resetDatastore(page);
```

Verified: a row inserted into `campus_job_portal_e2e` disappears after this
call while `campus_job_portal` is unchanged.

Role logins use the loopback-only demo hook:

```js
await page.goto('/login.php?demo=student');   // or employer | admin
```

---

## Known environment caveats

These are **not** product bugs — do not report them as such:

1. `C:\xampp\tmp` is unwritable here, which breaks `session_start()` and PHP's
   upload staging. `serve-qa.cmd` redirects both via
   `-d session.save_path=... -d upload_tmp_dir=...`.
2. PHP's built-in server (used on `:8099`) **ignores `.htaccess`**, so a `200`
   for `/data/*.json` there is a test-server artefact. On Apache (`:80`) those
   paths correctly return `403`.

---

## Security

`qa/.htaccess` denies all web access to this subtree. It holds real SMTP
credentials (`env-backup/`), raw session files, and full database dumps — none
of which may ever be web-reachable. Keep the deny rule in place.
