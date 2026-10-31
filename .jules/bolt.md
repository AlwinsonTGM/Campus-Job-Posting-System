
## 2026-10-31 - Jobs List Pagination Optimization
**Learning:** In PHP applications using MySQL, retrieving the entire filtered dataset just to use `array_slice` for pagination causes massive memory spikes and slow load times on large datasets (O(N) fetching bottleneck).
**Action:** Always push pagination down to the database using `LIMIT` and `OFFSET`, and use a separate `SELECT COUNT(*)` query (or `SQL_CALC_FOUND_ROWS`) to obtain the total row count for calculating `$total_pages`. I implemented a helper function to share the `WHERE` clauses between the data and count queries to avoid code duplication.
