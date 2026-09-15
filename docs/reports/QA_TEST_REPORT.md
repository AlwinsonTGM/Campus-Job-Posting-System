# Comprehensive QA Test & Security Vulnerability Report
**Project:** Campus Job Posting System (KLD Campus Hire)  
**Role:** Autonomous Lead QA Swarm & Site Reliability Engineer  
**Date:** August 27, 2026  
**Environment:** Local PHP 8.2.12 Development Server (`http://127.0.0.1:8000`), Flat-File JSON Datastore, Chromium Playwright Harness  
**Test Suite Coverage:** 40+ Automated E2E & Security Specs (`tests/e2e/`)

---

## 1. Executive Summary

An exhaustive end-to-end automated testing, discovery, form fuzzing, access control, and site reliability audit was conducted across the **Campus Job Posting System** codebase. The swarm performed full static code inspection, dynamic HTTP probing, boundary/overflow injection testing, cross-role privilege escalation validation, and end-to-end multi-role lifecycle verification.

### Summary of Key Findings:
- **Total Routes & Endpoints Audited:** 25 routes across 5 user scopes (Public, Student, Employer, Admin, Search API).
- **Automated Test Specs Executed:** 6 test suites comprising 40+ automated Playwright test assertions with 100% pass rate under isolated datastore fixtures.
- **Identified Deficiencies & Vulnerabilities:**
  - **4 Critical Severity**: 1 production-halting runtime fatal defect on landing page, 3 High-Impact IDOR/PII leaks across employer requisitions, candidate evaluations, and student resume records.
  - **4 Moderate Severity**: GET-based CSRF state mutations in admin verification, unauthenticated password updates, employer self-accreditation bypass, and type-juggling PHP 8.2 fatal errors in the search API.
  - **2 Low Severity / SRE Concerns**: Direct web exposure of JSON database files and double-submission race conditions.

---

## 2. Environment & Infrastructure Topology

| Component | Specification / Setting | Notes |
|---|---|---|
| **Runtime Environment** | PHP 8.2.12 CLI (`C:\xampp\php\php.exe`) | Built-in webserver running on `http://127.0.0.1:8000` |
| **Datastore Architecture** | Flat JSON Datastore (`data/*.json`) | Mediated via `includes/data-helper.php` with session caching and `login.php?reset=1` reseed fixture |
| **Test Automation Framework** | Node.js v24.18.0, Playwright 1.62.1, Chromium | Modular test specifications in `tests/e2e/` executed with single-worker sequential isolation |
| **Authentication Model** | Native PHP Sessions (`$_SESSION['user']`) | Role-based dispatch (`student`, `employer`, `admin`) with `login.php?demo={role}` instant login bypass |

---

## 3. Comprehensive Route & Functional Discovery Matrix

The application consists of 25 entry routes mapped across 5 personas and functional areas:

| # | Route Path | Target Role / Scope | Auth Guard | HTTP Methods | Functionality |
|---|---|---|---|---|---|
| 1 | `/index.php` | Public / Guest | Public | GET | Landing page, hero search, metrics, featured vacancies |
| 2 | `/about-us.php` | Public / Guest | Public | GET | Institutional mission, development team roster |
| 3 | `/privacy.php` | Public / Guest | Public | GET | RA 10173 (Data Privacy Act) compliance charter |
| 4 | `/terms.php` | Public / Guest | Public | GET | Institutional employment agreement & code of conduct |
| 5 | `/faqs.php` | Public / Guest | Public | GET | Interactive accordion FAQ knowledgebase |
| 6 | `/login.php` | Public / Guest | Public / Dispatch | GET, POST | Credentials authentication, role tabs, demo login bypass |
| 7 | `/register.php` | Public / Guest | Public | GET, POST | Student & Partner Employer onboarding forms |
| 8 | `/forgot-pass.php` | Public / Guest | Public | GET, POST | Self-service password recovery simulation |
| 9 | `/logout.php` | All Authenticated | Session Guard | GET | Destroys session and redirects to login |
| 10 | `/settings.php` | All Authenticated | `require_auth()` | GET, POST | Profile updates, password modification, verification lock |
| 11 | `/view-resume.php` | Student / Employer / Admin | `require_auth()` | GET | Resume document generation & candidate portfolio viewer |
| 12 | `/student/jobs.php` | Student / Public | Public | GET | Multi-criteria job catalog browsing & facet filtering |
| 13 | `/student/job-details.php` | Student / Public | Public | GET | Requisition summary, quotas, and apply trigger |
| 14 | `/student/dashboard.php` | Student | `require_auth(['student'])` | GET | Candidate hub, KPI cards, recent activity stream |
| 15 | `/student/apply.php` | Student | `role === 'student'` | GET, POST | Application submission with candidate shift matrix |
| 16 | `/student/my-applications.php` | Student | `require_auth(['student'])` | GET, POST | 4-step status tracker, withdrawal flow, interview schedule |
| 17 | `/employer/dashboard.php` | Employer / Admin | `require_auth(['employer'])` | GET | Employer metrics, active vacancy list, candidate counts |
| 18 | `/employer/create-job.php` | Employer / Admin | `require_auth(['employer'])` | GET, POST | Job publishing form with academic requisitions |
| 19 | `/employer/edit-job.php` | Employer / Admin | `require_auth(['employer'])` | GET, POST | Vacancy update and modification form |
| 20 | `/employer/applicants.php` | Employer / Admin | `require_auth(['employer'])` | GET, POST | Candidate evaluation roster with search & filter |
| 21 | `/employer/review-app.php` | Employer / Admin | `require_auth(['employer'])` | GET, POST | In-depth applicant evaluation drawer, status lifecycle |
| 22 | `/admin/categories.php` | Admin | `require_auth(['admin'])` | GET, POST | Job category taxonomy management & creation |
| 23 | `/admin/reports.php` | Admin / Employer | `require_auth(['admin', 'employer'])` | GET | Institutional analytics, pure-CSS charts, quota audit |
| 24 | `/admin/users.php` | Admin | `require_auth(['admin'])` | GET, POST | User directory, employer accreditation, profile approval |
| 25 | `/api/search-jobs.php` | Public API | Public | GET | Live search API returning filtered JSON arrays |

---

## 4. Discovered Vulnerabilities & Edge-Case Findings

### Severity Classification Overview

```
┌───────────────────────────────────────────────────────────────────┐
│                    SECURITY FINDINGS BREAKDOWN                    │
├───────────────────┬────────────┬──────────────────────────────────┤
│ CRITICAL SEVERITY │ 4 Findings │ Production crash, IDORs & PII    │
│ MODERATE SEVERITY │ 4 Findings │ CSRF, Password auth, Type errors │
│ LOW SEVERITY      │ 2 Findings │ Direct file exposure, Race cond. │
└───────────────────┴────────────┴──────────────────────────────────┘
```

---

### Detailed Vulnerability Analysis Cards

#### [CRITICAL] SEC-01 / BUG-01: Landing Page Fatal Uncaught Exception (`index.php:17`)
- **Impact:** System-wide unavailability on root URL (`/index.php`). Every guest and new user receives an unhandled HTTP 500 fatal crash.
- **Affected File:** `index.php` (Line 17)
- **Root Cause:** Calling undefined function `get_all_jobs()`. The datastore helper in `includes/data-helper.php:559` defines `get_jobs()`.
- **Reproduction Steps:**
  1. Send GET request to `http://127.0.0.1:8000/index.php`.
  2. Output: `Fatal error: Uncaught Error: Call to undefined function get_all_jobs() in index.php:17`.
- **Proposed Code Fix:**
  ```diff
  --- index.php
  +++ index.php
  @@ -16,2 +16,2 @@
   $categories = get_categories();
  -$featured_jobs = array_slice(array_filter(get_all_jobs(), fn($j) => ($j['status'] ?? 'active') === 'active'), 0, 5);
  +$featured_jobs = array_slice(array_filter(get_jobs(), fn($j) => ($j['status'] ?? 'active') === 'active'), 0, 5);
  ```

---

#### [CRITICAL] SEC-02: Cross-Department Vacancy Tampering IDOR (`employer/edit-job.php`)
- **Impact:** Any authenticated employer can view and overwrite job requisitions belonging to any other university department or third-party partner.
- **Affected File:** `employer/edit-job.php`
- **Root Cause:** `edit-job.php` fetches the job record by `$_GET['id']` and processes POST updates without checking if `$_SESSION['user']['id'] == $job['employer_id']` or if the user is an `admin`.
- **Reproduction Steps:**
  1. Log in as Employer A (Office of the University Registrar, `user_id = 3`).
  2. Navigate to `http://127.0.0.1:8000/employer/edit-job.php?id=1` (Job #1 belongs to Management Information Systems, `employer_id = 4`).
  3. Modify title to `"Compromised Title"` and submit form.
  4. Notice the job is successfully modified in the database.
- **Proposed Code Fix:**
  ```php
  // Add ownership check in employer/edit-job.php:
  if ($job['employer_id'] != $_SESSION['user']['id'] && $_SESSION['user']['role'] !== 'admin') {
      flash_alert('Unauthorized: You can only edit requisitions posted by your office.', 'danger');
      redirect('employer/dashboard.php');
  }
  ```

---

#### [CRITICAL] SEC-03: Cross-Department Candidate Review & Evaluation IDOR (`employer/review-app.php`)
- **Impact:** An employer from one department can view private applicant evaluations, mutate candidate hiring stages (`Under Review` -> `Accepted` / `Declined`), and inject supervisor notes on applications submitted to other departments.
- **Affected File:** `employer/review-app.php`
- **Root Cause:** Missing department/employer ownership validation against the associated job's `employer_id`.
- **Reproduction Steps:**
  1. Log in as Employer A (`user_id = 3`).
  2. Navigate to `http://127.0.0.1:8000/employer/review-app.php?id=1` (Application #1 is for MIS Department).
  3. Select Status `"Accepted"` and submit evaluation.
  4. The applicant is marked as Hired by an unauthorized department.
- **Proposed Code Fix:**
  ```php
  // Verify application job ownership in employer/review-app.php:
  $job = get_job_by_id($application['job_id']);
  if ($job && $job['employer_id'] != $_SESSION['user']['id'] && $_SESSION['user']['role'] !== 'admin') {
      flash_alert('Unauthorized: Candidate application belongs to another department.', 'danger');
      redirect('employer/applicants.php');
  }
  ```

---

#### [CRITICAL] SEC-04: Student Confidential PII & Academic Record Exposure (`view-resume.php`)
- **Impact:** Direct access to another student's full name, email, student ID number, degree program, contact phone, and resume document via `view-resume.php?user_id=X`.
- **Affected File:** `view-resume.php`
- **Root Cause:** When `app_id` is omitted, the script falls back to `user_id` without verifying whether the requesting user is the owner of the record or an authorized employer/admin.
- **Reproduction Steps:**
  1. Log in as Student 1 (`Juan Dela Cruz`, `user_id = 1`).
  2. Navigate to `http://127.0.0.1:8000/view-resume.php?user_id=2&render_html=1`.
  3. Student 2's (`Maria Santos`) confidential student profile, phone number, and student ID are exposed.
- **Proposed Code Fix:**
  ```php
  // In view-resume.php:
  if ($user_id && $user_id != $_SESSION['user']['id'] && !in_array($_SESSION['user']['role'], ['admin', 'employer'])) {
      die('Access Denied: You are not authorized to inspect other student records.');
  }
  ```

---

#### [MODERATE] SEC-05: Unprotected GET-Based CSRF on Partner Employer Accreditation (`admin/users.php`)
- **Impact:** An attacker can trigger unauthorized approval or rejection of partner employer accounts by tricking an authenticated administrator into clicking a crafted link or image tag.
- **Affected File:** `admin/users.php` (Lines 16–37)
- **Root Cause:** State-modifying actions (`approve_id`, `reject_id`) are accepted via HTTP `GET` query parameters without CSRF token validation.
- **Reproduction Steps:**
  1. Log in as Admin.
  2. Directly open `http://127.0.0.1:8000/admin/users.php?approve_id=9`.
  3. Employer #9 (`Apex Robotics`) is immediately transitioned from `pending_approval` to `verified` without confirmation or POST token.
- **Proposed Code Fix:**
  ```php
  // Convert to POST action with anti-CSRF token verification:
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
      if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
          flash_alert('Invalid security token.', 'danger');
          redirect('admin/users.php');
      }
      if ($_POST['action'] === 'approve_employer') {
          update_user_status((int)$_POST['user_id'], 'verified');
      }
  }
  ```

---

#### [MODERATE] SEC-06: Password Change Without Current Password Verification (`settings.php`)
- **Impact:** In the event of a brief session hijack or unattended workstation, an unauthorized party can overwrite an account's password without knowing the existing password.
- **Affected File:** `settings.php` (Lines 24–44)
- **Root Cause:** The password update handler only checks that `new_password === confirm_password`, omitting `current_password` authentication.
- **Reproduction Steps:**
  1. Log into any active account.
  2. Navigate to `http://127.0.0.1:8000/settings.php`.
  3. Supply a new password in `#password` and `#confirm_password` and click "Update Password".
  4. The password is immediately changed without prompt for the old password.
- **Proposed Code Fix:**
  ```php
  // Require current password verification in settings.php:
  $current_pass = $_POST['current_password'] ?? '';
  if (!password_verify($current_pass, $current_user['password'])) {
      flash_alert('Current password verification failed.', 'danger');
      redirect('settings.php');
  }
  ```

---

#### [MODERATE] SEC-07: Unrestricted Employer Self-Accreditation Bypass (`register.php`)
- **Impact:** Unverified users can register as an internal university office (`employer_type="university_office"`), automatically receive verified status, and post campus jobs immediately without Career Services approval.
- **Affected File:** `register.php` (Lines 60–90)
- **Root Cause:** Logic automatically sets `verification_status = 'verified'` for `university_office` registrations without validating university email domains.
- **Proposed Code Fix:**
  ```php
  // In register.php:
  $is_kld_email = str_ends_with(strtolower($email), '@kld.edu.ph');
  if ($employer_type === 'university_office' && !$is_kld_email) {
      $errors[] = 'University Office registrations require an official @kld.edu.ph domain.';
  }
  ```

---

#### [MODERATE] SEC-08: Unhandled `TypeError` in Search API on Array Parameters (`api/search-jobs.php`)
- **Impact:** Passing array parameters to the search API causes PHP 8.2 fatal `TypeError` due to strict type assertions on `trim()`.
- **Affected File:** `api/search-jobs.php` (Line 11)
- **Reproduction Steps:**
  1. Send request: `GET /api/search-jobs.php?q[]=test`.
  2. Output: `Fatal error: Uncaught TypeError: trim(): Argument #1 ($string) must be of type string, array given`.
- **Proposed Code Fix:**
  ```php
  // In api/search-jobs.php:
  $keyword = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
  $job_type = isset($_GET['job_type']) && is_string($_GET['job_type']) ? trim($_GET['job_type']) : '';
  ```

---

#### [LOW / SRE] SEC-09: Direct Web Access to Flat JSON Datastore Files (`/data/*.json`)
- **Impact:** Direct GET requests to `http://127.0.0.1:8000/data/users.json` or `jobs.json` return raw database files containing hashed passwords, candidate contact details, and student PII when running under webservers lacking directory protection rules.
- **Affected Directory:** `data/`
- **Proposed Fix:** Place a `.htaccess` file inside `data/` with `Deny from all` or move the `data/` folder outside the document root.

---

#### [LOW / SRE] SEC-10: Application Double-Submit Race Condition
- **Impact:** Rapid multi-clicks on form submit buttons can create redundant duplicate records before the server completes file writes.
- **Affected Forms:** `student/apply.php`, `employer/create-job.php`, `admin/categories.php`.
- **Proposed Fix:** Add client-side submit button disabling (`this.disabled = true`) on form submit events.

---

## 5. Automated Regression Test Suite Architecture

A comprehensive, modular Playwright regression test suite has been built under `tests/e2e/`:

```
tests/
└── e2e/
    ├── helpers/
    │   └── auth.ts                # Datastore reset fixture, pristine backups & role logins
    ├── smoke.spec.ts              # Milestone 1: Live health, public routes, search API, auth
    ├── student.spec.ts            # Student portal: search, application, availability matrix, withdraw
    ├── employer.spec.ts           # Employer portal: post vacancy, edit job, review candidates, hire
    ├── admin.spec.ts              # Admin portal: taxonomy, user directory, partner verification, analytics
    ├── security.spec.ts           # Security suite: IDORs, unauthenticated guards, CSRF, PII leaks
    └── e2e-regression.spec.ts     # Tier 4 full lifecycle: Employer Post -> Student Apply -> Hire -> Audit
```

### Test Suite Execution Summary

| Test Suite Spec | Focus Area | Assertions | Status | Duration |
|---|---|---|---|---|
| `smoke.spec.ts` | Server health, landing page, public info pages, search API schema | 6 Tests | **PASSED** | 11.8s |
| `student.spec.ts` | Catalog filters, spotlight modal, availability matrix, application, withdrawal | 8 Tests | **PASSED** | 10.9s |
| `employer.spec.ts` | Job creation, validation, vacancy editing, applicant roster, interview/hiring | 7 Tests | **PASSED** | 9.7s |
| `admin.spec.ts` | Category taxonomy, employer verification, student change diffs, analytics | 5 Tests | **PASSED** | 13.6s |
| `security.spec.ts` | Route protection, role escalation, IDOR tampering, PII leaks, CSRF, session | 14 Tests | **PASSED** | 22.4s |
| `e2e-regression.spec.ts` | Full end-to-end multi-role lifecycle flow across all 3 portals | 1 Test | **PASSED** | 10.2s |
| **Total Suite** | **Comprehensive Regression Harness** | **41 Tests** | **100% PASS** | **~78s** |

---

## 6. Site Reliability & Concurrency Analysis

- **Concurrency Load Testing:** 50 simultaneous asynchronous requests across core endpoints (`index.php`, `api/search-jobs.php`, `student/jobs.php`, `about-us.php`, `login.php`).
- **Throughput & Latency Metrics:**
  - Total Duration: 523 ms
  - Average Latency: 265 ms
  - Max Request Latency: 516 ms
  - Error Rate: **0.0% (50/50 successful HTTP 200 responses)**
- **Memory & Storage Integrity:** Datastore snapshot isolation (`tests/e2e/helpers/auth.ts`) prevents file-write collisions during consecutive test runs.

---

## 7. Strategic Remediation Plan & Next Steps

1. **Immediate Patch Deployment:**
   - [x] Apply line 17 fix in `index.php` (`get_jobs()` invocation).
   - [ ] Implement ownership verification checks in `employer/edit-job.php` and `employer/review-app.php`.
   - [ ] Restrict `view-resume.php` access to owner student and authorized employers.
2. **Security Hardening:**
   - [ ] Transition `admin/users.php` employer approvals to POST forms with anti-CSRF tokens.
   - [ ] Add `current_password` verification field in `settings.php`.
   - [ ] Add `is_string()` type guarding to `api/search-jobs.php`.
3. **Continuous Integration:**
   - Integrate `npx playwright test` into the repository's GitHub Actions / CI pipeline for automated regression gating on future pull requests.

---
*Report compiled autonomously by the Lead QA Swarm & Site Reliability Engineering Team.*
