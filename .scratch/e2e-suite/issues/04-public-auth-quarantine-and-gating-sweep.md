# 04: Public Registration, OTP Verification Quarantine & Role Gating Sweep

**What to build:** An end-to-end verified Candidate Registration journey through verification quarantine, OTP validation, and role-based redirect, asserting clean form layout, legible error alerts, and zero horizontal overflow on all viewports.

**Blocked by:** 01: Reusable Invariant Engine & Continuous Viewport Sweep Harness

**Status:** ready-for-agent

- [x] Public landing (`/index.php`), About (`/about-us.php`), and FAQs (`/faqs.php`) pass the Continuous Viewport Sweep with zero horizontal overflow.
- [x] User registration (`/register.php`) displays valid mobile and desktop form layouts with stacked inputs and accessible labels.
- [x] Submitting valid candidate registration holds the session in Verification Quarantine and redirects to `/verify-email.php`.
- [x] Entering valid institutional OTP clears quarantine and promotes session to active student role landing on `/student/dashboard.php`.
- [x] Unauthenticated attempts to access role-gated routes (`/student/*`, `/employer/*`, `/admin/*`) cleanly redirect to login with preserved `return-to` target.
