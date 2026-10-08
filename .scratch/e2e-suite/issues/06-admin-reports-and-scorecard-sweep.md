# Ticket 06: Administrative Reports, Scorecard & Continuous Viewport Sweep

<!-- triage-labels: ready-for-agent -->

## Objective
Verify `/admin/reports.php` institutional analytics, executive metric cards, visual charts, and department placement tables across the entire 11-breakpoint continuous viewport sweep (`360px` to `1280px`) with zero horizontal overflow violations.

## Scope
1. Institutional Analytics Dashboard loading for authenticated administrator.
2. 5 KPI Scorecard Metrics (`Total Users`, `Total Vacancies`, `Applications Filed`, `Interviews Scheduled`, `Officially Hired`).
3. Visual analytics charts (Donut Chart & Candidate Pipeline Funnel).
4. Responsive table wrapping for Department Hiring Quotas vs Filled Positions.
5. Print media header verification (`.d-print-block`).
6. Continuous 11-breakpoint sweep asserting the Zero-Overflow Invariant (`assertZeroHorizontalOverflow`).

## Acceptance Criteria
- [x] Admin login succeeds and routes cleanly to `/admin/reports.php`. (Verified via `admin-governance-and-sweep.spec.ts`)
- [x] All 5 metric cards render legible numerical values and badges on every breakpoint. (Verified via Scorecard inspection)
- [x] No element exceeds viewport width (`scrollWidth <= clientWidth`). (Verified via `runContinuousViewportSweep`)
- [x] Action buttons (`Print Report`, `Export PDF`) wrap cleanly without text clipping. (Verified via DOM inspection)
- [x] The full 11-breakpoint sweep executes and passes without assertions failing. (Verified: 11 breakpoints, zero violations)
