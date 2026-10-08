# 03: Employer Requisition Lifecycle & Candidate Pipeline Sweep

**What to build:** An end-to-end verified Employer user journey covering authentication, posting a new job requisition, reviewing applicant submissions, changing candidate statuses, and opening dispatch modals across mobile and desktop viewports with zero horizontal overflow.

**Blocked by:** 01: Reusable Invariant Engine & Continuous Viewport Sweep Harness

**Status:** ready-for-agent

- [x] Employer login via demo shortcut establishes a valid authenticated session landing on `/employer/dashboard.php`.
- [x] Employer Dashboard and Requisition Management pages pass the Continuous Viewport Sweep across all 11 breakpoints.
- [x] Employer can successfully fill out and submit the job creation form (`/employer/create-job.php`) with clean mobile and desktop alignment.
- [x] Employer can view applicant submissions (`/employer/applicants.php`), open applicant review (`/employer/review-app.php`), and update candidate hiring status.
- [x] Employer announcements modal (`/employer/updates.php`) opens, centers within viewport bounds, and dismisses without horizontal overflow.
