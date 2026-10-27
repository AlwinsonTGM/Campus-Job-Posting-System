## 2026-10-27 - Index page job loading optimization
**Learning:** PHP `array_filter` on an unpaginated PDO SQL result `get_jobs()` fetches the entire table into memory, leading to an O(N) memory and time bottleneck on the highest-traffic landing page.
**Action:** Use a dedicated, limited SQL query (`get_featured_jobs`) that filters by status and limit at the database level instead of in-memory.
