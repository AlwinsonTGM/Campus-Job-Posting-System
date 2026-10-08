# Ticket 10: Account Settings, Security & Availability Matrix Sweep

<!-- triage-labels: ready-for-agent -->

## Objective
Verify `/settings.php` across student, employer, and admin roles with special focus on the Candidate Shift Availability Matrix, password change security form, and modal dialogs across 11 continuous sliding breakpoints with zero layout collisions.

## Scope
1. Profile settings forms for authenticated users.
2. Candidate Shift Availability Matrix (Monday-Saturday, Morning/Afternoon/Evening) responsive grid wrapping.
3. Security & Password update controls with CSRF protection.
4. Student profile update request modal (#requestProfileModal) responsive fitting on 360px smartphones.
5. Notification preference toggle controls.
6. 11-breakpoint continuous sweep enforcing the Zero-Overflow Invariant (`assertZeroHorizontalOverflow`).

## Acceptance Criteria
- [x] Availability matrix adapts without breaking out of the container or overflowing horizontally on mobile (`scrollWidth <= clientWidth`). (Verified: 11 breakpoints, zero overflow)
- [x] Form input fields, buttons, and switches remain within visible bounds without clipped text. (Verified: profile and security inputs)
- [x] Profile change request modal dialog fits comfortably on narrow viewports (360px-375px). (Verified at 375px)
- [x] Continuous viewport sweep passes across all 11 breakpoints. (Verified: all 11 breakpoints green)
