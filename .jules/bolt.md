## 2024-10-31 - [N+1 Admin Notification Loop Optimization]
**Learning:** Found an N+1 problem in `includes/services/system-service.php` where notifying admins executed N separate `INSERT` statements for N administrators. This was triggered on every student registration and employer accreditation resubmission.
**Action:** Replaced the PHP `foreach` loop that called `create_notification()` with a single atomic `INSERT INTO ... SELECT` query, reducing database write operations from O(N) to O(1) for system-wide admin notifications.
