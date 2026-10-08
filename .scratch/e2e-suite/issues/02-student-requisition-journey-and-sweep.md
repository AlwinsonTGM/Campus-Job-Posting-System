# 02: Student Requisition Journey & Mobile Sliding Sweep

**What to build:** An end-to-end verified Student user journey covering authentication, job vacancy search and category filtering, viewing vacancy details, applying with stored profile resume, and inspecting application status, verified across the 11-point sliding screen sweep without layout collisions.

**Blocked by:** 01: Reusable Invariant Engine & Continuous Viewport Sweep Harness

**Status:** ready-for-agent

- [x] Student login via demo shortcut establishes a valid authenticated session landing on `/student/dashboard.php`.
- [x] Student Dashboard passes the Continuous Viewport Sweep across all 11 breakpoints with zero horizontal overflow.
- [x] Mobile navigation drawer opens and closes cleanly on 375px viewport without causing horizontal document scrollbars.
- [x] Student Jobs Directory (`/student/jobs.php`) allows searching and category filtering on mobile (375px) and desktop (1280px).
- [x] Job Details (`/student/job-details.php`) displays all requisition requirements without overlapping action buttons.
- [x] Student submits application with stored profile resume; application successfully persists and reflects in `/student/my-applications.php`.
