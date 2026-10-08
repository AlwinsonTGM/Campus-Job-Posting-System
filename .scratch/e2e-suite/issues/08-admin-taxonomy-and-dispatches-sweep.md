# Ticket 08: Taxonomy & Dispatch Governance (Categories & System Updates Lifecycle)

<!-- triage-labels: ready-for-agent -->

## Objective
Verify `/admin/categories.php` taxonomy management (creation, archival, restoration) and `/admin/updates.php` bulletin dispatches management across the continuous viewport sweep, plus agentic hybrid navigation.

## Scope
1. Category listing and hero showcase banner layout.
2. New Category creation modal workflow with icon, badge, and description fields.
3. Category archival and restoration lifecycle actions.
4. Career updates / system dispatches publishing and tab navigation (`?tab=archived`).
5. Continuous 11-breakpoint sliding sweep across categories and dispatches surfaces.
6. Hybrid Agent navigation scenario (`tests/admin-agent.e2e.ts`) validating AI-driven administrative execution with cached replay.

## Acceptance Criteria
- [x] Category cards display correctly without overflow across all 11 breakpoints. (Verified via `runContinuousViewportSweep`)
- [x] New category can be created, verified in the DOM, and subsequently archived/restored. (Verified via Category Creation test)
- [x] Career dispatches surface correctly displays active and archived bulletins. (Verified via active & archived tab sweep)
- [x] Zero-Overflow Invariant passes across all screen sizes. (Verified: all 11 breakpoints green)
- [x] Hybrid AI Agent runs and replays successfully. (Verified: `tests/admin-agent.e2e.ts` passed desktop/mobile and cached replay in 4.71s)
