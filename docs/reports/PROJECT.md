# Project: Campus Job Posting System (KLD Campus Hire) - E2E Stabilization & MySQL Integration

## Architecture & Topology
- **Backend**: PHP 8.2 with modular request handling, session management, and MySQL relational datastore (`campus_job_portal`) with PDO abstraction in `includes/db.php` and `includes/data-helper.php`.
- **Datastore**: Dual-mode data management with flat-file JSON fixtures in `data/seeds/` and live MySQL database with transactional consistency, ACID operations, and auto-seeding in `database/migrate.php`.
- **Frontend**: Server-Side Rendered (SSR) HTML5 with Bootstrap 5.3, Bootstrap Icons, tactile **Paper Sheet Design System** (`assets/css/custom.css`), and vanilla JavaScript (`assets/js/`).
- **Testing**: Playwright End-to-End test suite in TypeScript (`tests/e2e/`) running sequentially (`workers: 1`) with session/datastore reset fixtures across 6 comprehensive suites.
- **Security Posture**: Multi-role RBAC (`student`, `employer`, `admin`), CSRF token protection on all state-mutating POST actions, IDOR authorization guards, Bcrypt password hashing, institutional domain validation (`@kld.edu.ph`), web server datastore access controls, and client-side double-submit debouncing.

---

## Feature Inventory & Implementation Status

| # | Feature | Description | Milestone | Status |
|---|:---|:---|:---:|:---:|
| 1 | **Landing Page & Hero Flow** | Streamlined hero with student & employer CTAs, live counters, Bento Grid category explorer, and empty state rendering for Real Clean Slate mode | M1 | **IN_PROGRESS** |
| 2 | **Database ACID & Fast Re-Seeding** | Transaction-wrapped database migration and re-seeding (`database/migrate.php`), transactional profile request approval, and cascade cleanup in `delete_job` | M1 | **IN_PROGRESS** |
| 3 | **Auth Helper & Single-Hop Demo Login** | Streamlined `login.php` parameter routing and `tests/e2e/helpers/auth.ts` helper routines | M1 | **IN_PROGRESS** |
| 4 | **CSRF Hardening on Administrative Forms** | Anti-CSRF verification across `admin/categories.php`, `admin/updates.php`, `employer/review-app.php`, `employer/applicants.php`, and `settings.php` | M1 | **IN_PROGRESS** |
| 5 | **Real-Time Data Mode Test Stabilization** | `tests/e2e/data-mode-and-realtime.spec.ts` dual-context state sync and dataset switcher verification | M2 | **PLANNED** |
| 6 | **Comprehensive Audit Test Stabilization** | `tests/e2e/comprehensive-audit.spec.ts` multi-role governance and 8-viewport audit verification | M2 | **PLANNED** |
| 7 | **Half-Screen Viewport Test Stabilization** | `tests/e2e/half-screen.spec.ts` responsive viewports and `domcontentloaded` optimization | M2 | **PLANNED** |
| 8 | **Full E2E Regression Suite Verification** | Complete 100% pass across all 6 test suites (`smoke`, `security`, `student`, `data-mode-and-realtime`, `comprehensive-audit`, `half-screen`) | M3 | **PLANNED** |
| 9 | **Database Integrity & Relational Verification** | ACID verification, zero orphaned rows, zero constraint errors in MySQL logs | M3 | **PLANNED** |
| 10 | **Adversarial & Forensic Integrity Audit** | Forensic Auditor and Challenger validation confirming zero cheating and authentic implementation | M4 | **PLANNED** |

---

## Milestones Summary

| # | Milestone Name | Key Deliverables & Scope | Dependencies | Status |
|---|:---|:---|:---:|:---:|
| **M1** | Backend ACID, Auth & Reset Optimization | `includes/data-helper.php`, `database/migrate.php`, `login.php`, `index.php`, `tests/e2e/helpers/auth.ts`, governance CSRF | none | **IN_PROGRESS** |
| **M2** | Targeted E2E Test Suite Stabilization | `tests/e2e/data-mode-and-realtime.spec.ts`, `tests/e2e/comprehensive-audit.spec.ts`, `tests/e2e/half-screen.spec.ts` | M1 | **PLANNED** |
| **M3** | Full Regression Suite & Database Integrity | Complete 6-suite verification via `npx playwright test` with zero MySQL errors | M2 | **PLANNED** |
| **M4** | Review, Adversarial Challenge & Forensic Audit | Reviewers, Challengers, and Forensic Auditor verification | M3 | **PLANNED** |

---

## Interface Contracts & Authorization Helpers (`includes/data-helper.php`)

- `can_manage_job($job_or_id, $user = null): bool`: Returns true if admin OR if employer id/department matches the requisition.
- `can_review_application($app_or_id, $user = null): bool`: Returns true if admin OR if employer id/department matches the applied job.
- `can_view_student_resume($app = null, $student_user_id = null, $user = null): bool`: Returns true if admin, or student viewing self, or employer who owns an active application from the student.
- `generate_csrf_token(): string`: Returns `$_SESSION['csrf_token']` (generated with `bin2hex(random_bytes(32))`).
- `verify_csrf_token($token): bool`: Validates `$token` against `$_SESSION['csrf_token']` using `hash_equals()`.
- `validate_csrf_token($token): bool`: Alias to `verify_csrf_token`.

---

## Code Layout & File Ownership

| File | Module / Area | Primary Owner | Status |
|:---|:---|:---|:---:|
| `index.php` | Landing Page & Featured Requisitions Empty State | Worker M1 | IN_PROGRESS |
| `database/migrate.php` | Transactional Database Re-Seeding | Worker M1 | IN_PROGRESS |
| `includes/data-helper.php` | ACID Transactions, Data Mode Switch, Orphan Cleanup | Worker M1 | IN_PROGRESS |
| `login.php` | Single-Hop Demo Auth & Reset Routing | Worker M1 | IN_PROGRESS |
| `tests/e2e/helpers/auth.ts` | Test Auth Helpers & Pristine Reset | Worker M1 | IN_PROGRESS |
| `admin/categories.php`, `admin/updates.php`, `employer/*.php`, `settings.php` | CSRF Token Verification on POST | Worker M1 | IN_PROGRESS |
| `tests/e2e/data-mode-and-realtime.spec.ts` | Dual Context & Real-time State Sync Tests | Worker M2 | PLANNED |
| `tests/e2e/comprehensive-audit.spec.ts` | Multi-role Governance & Viewport Audit Tests | Worker M2 | PLANNED |
| `tests/e2e/half-screen.spec.ts` | Responsive Half-Screen Viewport Tests | Worker M2 | PLANNED |
| `tests/e2e/*.spec.ts` | Full Playwright E2E Test Suite | Reviewer / Challenger | PLANNED |
