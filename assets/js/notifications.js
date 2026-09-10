/**
 * Campus Job Posting System - Real-time In-App Notifications Engine
 * Handles background polling, unread badges, interactive dropdown, and floating toast alerts.
 */

(function () {
    'use strict';

    // Guard: Only run if authenticated user elements exist
    const badgeDesktop = document.getElementById('navNotificationBadge');
    const badgeMobile = document.getElementById('navNotificationBadgeMobile');
    const dropdownList = document.getElementById('navNotificationList');
    const markAllBtn = document.getElementById('navNotificationMarkAllBtn');

    if (!badgeDesktop && !badgeMobile && !dropdownList) {
        return; // Guest user, do nothing
    }

    // Determine base URL dynamically from current path depth
    function getBaseUrl() {
        const path = window.location.pathname;
        const segments = path.split('/').filter(Boolean);
        // Find if we are inside /admin/, /employer/, /student/, etc.
        if (segments.some(s => ['admin', 'employer', 'student', 'data'].includes(s.toLowerCase()))) {
            return '../';
        }
        return '';
    }

    const baseUrl = getBaseUrl();
    const apiUrl = baseUrl + 'api/notifications.php';
    let previousUnreadCount = parseInt(badgeDesktop?.textContent || badgeMobile?.textContent || '0', 10);
    if (isNaN(previousUnreadCount)) previousUnreadCount = 0;

    let isPolling = false;
    let pollInterval = null;

    // Build or get floating toast container
    function getToastContainer() {
        let container = document.getElementById('notificationToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationToastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1095';
            document.body.appendChild(container);
        }
        return container;
    }

    // Display non-intrusive floating toast preview
    function showNotificationToast(title, message, link) {
        const container = getToastContainer();
        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center shadow-lg border-line rounded-3 bg-white mb-2';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.style.minWidth = '300px';
        toastEl.style.maxWidth = '380px';

        const clickUrl = link ? (baseUrl + 'api/notifications.php?action=click&id=' + encodeURIComponent(link.id || '')) : (baseUrl + 'notifications.php');

        toastEl.innerHTML = `
            <div class="toast-header bg-cream border-bottom border-line py-2 px-3">
                <i class="bi bi-bell-fill text-accent me-2"></i>
                <strong class="me-auto text-ink small fw-bold">${escapeHtml(title)}</strong>
                <small class="text-muted-custom">1m ago</small>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body p-3">
                <p class="small text-muted-custom mb-2">${escapeHtml(message)}</p>
                <div class="d-flex justify-content-end">
                    <a href="${clickUrl}" class="btn btn-sm btn-paper-primary py-1 px-3 d-inline-flex align-items-center" style="height: 32px !important; font-size: 12px !important;">View Update <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        `;

        container.appendChild(toastEl);
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toastEl, { delay: 6000 });
            bsToast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        } else {
            setTimeout(() => toastEl.remove(), 6000);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Update UI Badges
    function updateBadges(unreadCount) {
        [badgeDesktop, badgeMobile].forEach(badge => {
            if (!badge) return;
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.classList.remove('d-none');
            } else {
                badge.textContent = '0';
                badge.classList.add('d-none');
            }
        });
    }

    // Render Dropdown items HTML
    function renderDropdown(notifications, unreadCount) {
        if (!dropdownList) return;

        if (!notifications || notifications.length === 0) {
            dropdownList.innerHTML = `
                <div class="p-4 text-center text-muted-custom">
                    <i class="bi bi-bell-slash fs-2 mb-2 d-block text-muted opacity-50"></i>
                    <p class="small mb-0">No notifications yet</p>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(item => {
            const isUnread = !item.is_read;
            const clickUrl = baseUrl + 'api/notifications.php?action=click&id=' + item.id;
            const iconClass = item.icon || 'bi-bell';
            const badgeColor = item.badge_color || 'primary';
            const unreadDot = isUnread ? `<span class="badge-dot-unread" title="Unread"></span>` : '';

            html += `
                <a href="${clickUrl}" class="notification-item d-flex align-items-start gap-3 p-3 border-bottom border-line text-decoration-none ${isUnread ? 'bg-cream-tint' : ''}">
                    <div class="notif-icon-circle bg-${badgeColor}-subtle text-${badgeColor} flex-shrink-0">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h6 class="small fw-bold text-ink mb-0 text-truncate pe-2">${escapeHtml(item.title)}</h6>
                            <span class="text-muted-custom x-small">${escapeHtml(item.time_ago)}</span>
                        </div>
                        <p class="small text-muted-custom mb-0 line-clamp-2">${escapeHtml(item.message)}</p>
                    </div>
                    ${unreadDot}
                </a>
            `;
        });

        dropdownList.innerHTML = html;
    }

    // Fetch notifications from server
    async function fetchNotifications(isPeriodic = true) {
        if (isPolling) return;
        isPolling = true;

        try {
            const res = await fetch(apiUrl + '?limit=5', {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                isPolling = false;
                return;
            }

            const data = await res.json();
            if (data.success) {
                const newUnread = parseInt(data.unread_count, 10) || 0;

                // If unread count increased during polling, trigger non-intrusive toast
                if (isPeriodic && newUnread > previousUnreadCount && data.notifications && data.notifications.length > 0) {
                    const latest = data.notifications[0];
                    showNotificationToast(latest.title, latest.message, latest);
                }

                previousUnreadCount = newUnread;
                updateBadges(newUnread);

                // Only refresh dropdown if not currently open
                const dropdownMenu = dropdownList?.closest('.dropdown-menu');
                const isOpen = dropdownMenu && dropdownMenu.classList.contains('show');
                if (!isOpen) {
                    renderDropdown(data.notifications, newUnread);
                }
            }
        } catch (err) {
            console.debug('Notification poll failed:', err);
        } finally {
            isPolling = false;
        }
    }

    // Handle "Mark all as read"
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            try {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ action: 'mark_all_read' })
                });

                if (res.ok) {
                    previousUnreadCount = 0;
                    updateBadges(0);
                    // Remove unread indicators in dropdown
                    dropdownList.querySelectorAll('.badge-dot-unread').forEach(dot => dot.remove());
                    dropdownList.querySelectorAll('.bg-cream-tint').forEach(el => el.classList.remove('bg-cream-tint'));
                }
            } catch (err) {
                console.error('Mark all read error:', err);
            }
        });
    }

    // Polling setup: 30 seconds interval
    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(() => {
            if (!document.hidden) {
                fetchNotifications(true);
            }
        }, 30000);
    }

    // Window visibility handler (resume polling when user returns to tab)
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            fetchNotifications(true);
            startPolling();
        } else if (pollInterval) {
            clearInterval(pollInterval);
        }
    });

    // Initial background sync
    startPolling();

})();
