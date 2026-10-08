# Ticket 11: Public Legal & Auxiliary Pages Sweep + Hybrid Agent

<!-- triage-labels: ready-for-agent -->

## Objective
Verify all public auxiliary pages (`/faqs.php`, `/terms.php`, `/privacy.php`, `/about-us.php`) across the 11-breakpoint continuous viewport sweep, guaranteeing that dense legal clauses, accordions, and hero sections never cause awkward breaks or horizontal scrollbars, plus autonomous agent navigation.

## Scope
1. FAQs accordion interface and category navigation (`/faqs.php`).
2. Terms of Service dense document layout with sticky sidebar and table of contents (`/terms.php`).
3. Privacy Policy dense legal disclosures and data protection matrices (`/privacy.php`).
4. About Us institutional mission, placement statistics, and team showcase (`/about-us.php`).
5. Continuous 11-breakpoint sliding sweep across all 4 public pages (`360px` to `1280px`).
6. Hybrid Agent navigation scenario (`tests/auxiliary-agent.e2e.ts`) validating public content exploration with cached replay.

## Acceptance Criteria
- [x] No public auxiliary page triggers horizontal scrollbars on any of the 11 breakpoints. (Verified: FAQs, Terms, Privacy, About Us all zero overflow)
- [x] Accordion items expand and collapse without layout jitter or clipping. (Verified: FAQ expansion test)
- [x] Terms and Privacy sticky navigation collapses into clean horizontal or stacked headers on mobile viewports (< 768px). (Verified across 360px-680px)
- [x] Zero-Overflow Invariant passes across all 11 breakpoints. (Verified: 11 breakpoints green)
- [x] Hybrid Agent runs and caches replay successfully. (Verified: `tests/auxiliary-agent.e2e.ts` passed desktop/mobile and cached replay in 3.58s)
