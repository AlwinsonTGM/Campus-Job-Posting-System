# 05: Master E2E Suite Orchestration & CI Gate

**What to build:** An integrated script and test orchestrator that runs the entire verified hybrid E2E suite and continuous sliding sweep via standard commands (`bun run test:e2e` and `bun run test:sweep`), ensuring fast cached replays and actionable reporting on layout violations.

**Blocked by:** 02: Student Requisition Journey & Mobile Sliding Sweep, 03: Employer Requisition Lifecycle & Candidate Pipeline Sweep, 04: Public Registration, OTP Verification Quarantine & Role Gating Sweep

**Status:** ready-for-agent

- [x] `package.json` includes convenient script entries (`test:e2e` and `test:sweep`) utilizing Bun and Playwright / `@e2e-dev/web`.
- [x] Running `bun run test:e2e` executes all user journey tests with deterministic cached replays in under 20 seconds.
- [x] Running `bun run test:sweep` executes the 11-point Continuous Viewport Sweep across all three user roles and public pages.
- [x] Clear diagnostic output summarizes tested breakpoints, pass/fail status, and pinpointed culprit elements for any layout defect.
