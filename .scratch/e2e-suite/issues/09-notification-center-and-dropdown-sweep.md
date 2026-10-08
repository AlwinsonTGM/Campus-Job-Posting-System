# Ticket 09: Central Notification Center & Navigation Dropdown Sweep

<!-- triage-labels: ready-for-agent -->

## Objective
Verify the central notification center (`/notifications.php`), unread filtering, notification bell triggers (mobile link vs desktop dropdown `#navNotificationDropdown`), and notification item rendering across the continuous viewport sweep (`360px` to `1280px`) with zero layout overflow.

## Scope
1. Notification Center loading for authenticated student and employer accounts.
2. Filter tabs (`all` vs `unread`) and unread badge count indicators.
3. Mobile header notification icon direct routing (`/notifications.php`).
4. Desktop notification bell dropdown menu (#navNotificationDropdown) alignment and padding.
5. In-app notification item cards with time stamps, badges, and read-state indicators.
6. 11-breakpoint continuous sweep enforcing the Zero-Overflow Invariant (`assertZeroHorizontalOverflow`).

## Acceptance Criteria
- [x] Notification center renders all notification cards cleanly without horizontal overflow (`scrollWidth <= clientWidth`). (Verified: 11 breakpoints, zero overflow)
- [x] Desktop dropdown menu displays cleanly without overflowing the viewport width on desktop viewports. (Verified: #navNotificationDropdown at 1280px)
- [x] Mobile navigation control provides direct access to `/notifications.php`. (Verified: mobile header bell anchor)
- [x] Filter tab switching renders appropriate active and unread notifications. (Verified: ?filter=unread sweep)
- [x] Continuous viewport sweep passes across all 11 breakpoints. (Verified: all 11 breakpoints green)
