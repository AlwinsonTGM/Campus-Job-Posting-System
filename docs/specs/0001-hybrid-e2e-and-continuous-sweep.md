# Specification: Hybrid Verification & Continuous Viewport Sweep E2E Suite

<!-- triage-labels: ready-for-agent -->

## Problem Statement

The campus job posting system suffers from severe test distrust. Automated tests have historically provided "the green lie"—tests pass on CI or local terminal runs, but the application exhibits noticeable visual defects, text clipping, element collisions, and horizontal scrollbars when evaluators or professors manually test the interface. Specifically, when an evaluator dynamically resizes or "slides" the browser window, responsive gutter transitions (between 360px and 1280px) break. Additionally, prior attempts at purely autonomous AI testing produced unguided, flaky runs, while raw static locators proved fragile and disconnected from real user journeys.

## Solution

A resilient **Hybrid Verification Harness** that bridges agentic intent navigation and deterministic layout geometry checks:
1. High-level user journeys (Student application, Employer job requisition management, Authentication quarantine) are driven through semantic role actions and cached replays using `@e2e-dev/web`.
2. Every page transition and critical user flow is subjected to an automated **Continuous Viewport Sweep** stepping across eleven responsive gutter widths (`360px` through `1280px`).
3. Layout integrity is enforced deterministically on every step via the **Zero-Overflow Invariant** (`scrollWidth <= clientWidth`), bounding box collision detection, and text clipping verification.
4. Recorded user journeys are cached after initial verification, providing blistering subsequent execution speeds (sub-3 seconds) with zero recurring token costs.

## User Stories

1. As a mobile student, I want to access the job portal on a narrow smartphone viewport (360px - 375px), so that I can browse vacancies without horizontal scrollbars or clipped text.
2. As a student evaluating job listings, I want to filter opportunities by category and search terms on any screen width, so that the search controls remain visible, functional, and properly aligned.
3. As a student viewing job details, I want all typography, badges, and company details to wrap comfortably without colliding with action buttons, so that all requisition information is legible.
4. As a student applicant, I want to submit an application using my stored profile resume with a single tap, so that applying is immediate and verified in the application records.
5. As a student using a smartphone, I want to tap the navigation menu toggle to open the drawer, so that navigation links expand cleanly without pushing the page width beyond 100vw.
6. As a student reviewing past applications, I want my submitted, approved, and withdrawn statuses clearly displayed in responsive cards, so that I can track my hiring pipeline on both mobile and desktop.
7. As an employer creating a vacancy, I want to fill out job requisition forms on tablet or desktop viewports, so that form inputs, labels, and file upload zones remain properly stacked and accessible.
8. As an employer reviewing candidates, I want to view submitted student applicants, download their resumes, and update application statuses, so that hiring decisions are instantly reflected in the portal datastore.
9. As an employer posting announcements, I want modal dialogs to center cleanly and remain scrollable on narrow viewports, so that dialog actions are never pushed offscreen.
10. As a career services administrator, I want to review system-wide reports and manage user statuses, so that administrative dashboards adjust seamlessly across split-screen workstations.
11. As a newly registered candidate, I want to encounter verification quarantine upon registration, so that I am guided to enter my institutional OTP before gaining active session privileges.
12. As a course professor or evaluator dynamically sliding the browser window, I want the layout across all roles to transition fluidly without awkward gutter collisions, so that the portal demonstrates professional responsive design standards.

## Implementation Decisions

- **Architectural Seam**: The test suite operates at the highest possible architectural seam: the live **Browser Surface HTTP Interface** (`http://127.0.0.1:8000/`) driven through `@e2e-dev/web` and Playwright. No internal class mocking is used.
- **Fixture Lifecycle Seam**: Baseline fixture synchronization (`restorePristineData` against `data/seeds/demo`) resets flat-file datastore fixtures before each test suite run, guaranteeing hermetic, contamination-free state.
- **Continuous Viewport Sweep Engine**: A stepped sweep algorithm programmatically resizes the browser across 11 discrete transition gutters (`360px`, `375px`, `414px`, `480px`, `576px`, `680px`, `768px`, `850px`, `992px`, `1100px`, `1280px`).
- **DOM Geometry Invariant Inspection**: Evaluates `document.documentElement.scrollWidth <= window.innerWidth` and pinpoints any visible element whose bounding box extends beyond the viewport. From our prototype, the verification logic is:
  ```ts
  const hasOverflow = await page.evaluate(() => 
    document.documentElement.scrollWidth > window.innerWidth || 
    (document.body ? document.body.scrollWidth > window.innerWidth : false)
  );
  expect(hasOverflow).toBe(false);
  ```
- **Autonomous Intent & Replay Cache**: High-level agent actions utilize `agent.act(...)` powered by OpenAI-compatible endpoints (Logfare / OpenRouter), recording deterministic replay artifacts under `.e2e/cache/` to eliminate token costs on recurring runs.
- **Rollout Phasing**:
  - Phase 1: Core Engine (Student Requisition flow, Employer Vacancy posting & review, Authentication quarantine, and Continuous Sweep).
  - Phase 2: Administrative Governance (Admin Category & User Management, System Reports, Archival & Restoration).
  - Phase 3: Auxiliary Features (Notifications, Settings, Password Reset, FAQs).

## Testing Decisions

- **What makes a good test**: Tests must strictly assert observable external behavior, accessible DOM roles (`getByRole`), visible geometry, and durable datastore records. Tests must never assert private internal PHP variables or implementation details.
- **Modules Tested**:
  - Student Role Surface (`/student/dashboard.php`, `/student/jobs.php`, `/student/job-details.php`, `/student/apply.php`, `/student/my-applications.php`).
  - Employer Role Surface (`/employer/dashboard.php`, `/employer/create-job.php`, `/employer/applicants.php`, `/employer/review-app.php`, `/employer/updates.php`).
  - Public & Authentication Surface (`/login.php`, `/register.php`, `/verify-email.php`, `/logout.php`).
- **Prior Art**: Existing regression specs under `tests/e2e/` (`half-screen.spec.ts`, `smoke.spec.ts`) provided the starting baseline, but lacked true mobile widths (< 680px) and deterministic geometry assertions. The prototype spike (`tests/e2e/prototype-student-sweep.spec.ts` and `tests/prototype-agent.e2e.ts`) establishes the new gold standard.

## Out of Scope

- **Automated Penetration & DAST Testing**: SQL injection fuzzing, cross-site scripting payload injection, and server security exploits remain strictly outside the scope of browser E2E tests and are handled via dedicated security scanners.
- **Third-Party Email Gateway Delivery**: Tests verify OTP handling via internal datastore verification rather than external SMTP network delivery.
- **Performance Load & Stress Testing**: Concurrency load generation (e.g. Apache Bench / k6) is decoupled from functional UI test runs.

## Further Notes

- The test harness is bun-first, running via `bunx e2e run` and `bunx playwright test`.
- Local execution auto-spawns the local PHP development server via `app.command` (`php -S 127.0.0.1:8000`), ensuring zero manual setup is required before executing tests.
