/**
 * Notification Bell System
 * Handles fetching, displaying, and managing notifications in the navbar bell dropdown.
 * Uses AJAX polling to keep notifications up to date.
 */
const NotificationBell = (function() {
    'use strict';

    const POLL_INTERVAL = 30000; // 30 seconds
    let pollTimer = null;

    /**
     * Initialize the notification bell system
     */
    function init() {
        fetchNotifications();
        startPolling();
        bindEvents();
    }

    /**
     * Bind click events for notification actions
     */
    function bindEvents() {
        // Mark all as read
        $(document).on('click', '.notification-mark-all-read', function(e) {
            e.preventDefault();
            e.stopPropagation();
            markAllAsRead();
        });

        // Click on individual notification
        $(document).on('click', '.notification-item[data-id]', function(e) {
            const id = $(this).data('id');
            const link = $(this).data('link');
            markAsRead(id, link);
        });

        // Refresh on dropdown open
        $(document).on('show.bs.dropdown', '#notificationDropdown', function() {
            fetchNotifications();
        });
    }

    /**
     * Fetch notifications from the server
     */
    function fetchNotifications() {
        $.ajax({
            url: '/notifications',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                renderNotifications(response.notifications);
                updateBadge(response.unread_count);
            },
            error: function() {
                // Silently fail - notifications are non-critical
            }
        });
    }

    /**
     * Render notification items in the dropdown
     */
    function renderNotifications(notifications) {
        const container = $('#notificationList');
        if (!container.length) return;

        container.empty();

        if (!notifications || notifications.length === 0) {
            container.html(
                '<div class="notification-empty">' +
                    '<i class="mdi mdi-bell-off-outline"></i>' +
                    '<p>No notifications yet</p>' +
                '</div>'
            );
            return;
        }

        notifications.forEach(function(n) {
            const readClass = n.is_read ? 'notification-read' : 'notification-unread';
            const colorClass = 'bg-' + n.color;
            
            const item = $(
                '<a class="dropdown-item preview-item notification-item ' + readClass + '" ' +
                    'data-id="' + n.id + '" ' +
                    'data-link="' + (n.link || '') + '">' +
                    '<div class="preview-thumbnail">' +
                        '<div class="preview-icon ' + colorClass + '">' +
                            '<i class="mdi ' + n.icon + '"></i>' +
                        '</div>' +
                    '</div>' +
                    '<div class="preview-item-content">' +
                        '<h6 class="preview-subject font-weight-normal mb-1">' + escapeHtml(n.title) + '</h6>' +
                        '<p class="font-weight-light small-text mb-0 text-muted">' + escapeHtml(n.message) + '</p>' +
                        '<p class="notification-time">' + escapeHtml(n.time_ago) + '</p>' +
                    '</div>' +
                '</a>' +
                '<div class="dropdown-divider"></div>'
            );

            container.append(item);
        });
    }

    /**
     * Update the badge counter on the bell icon
     */
    function updateBadge(count) {
        const badge = $('#notificationDropdown .count');
        if (count > 0) {
            badge.text(count > 99 ? '99+' : count).addClass('show-count').show();
        } else {
            badge.text('').removeClass('show-count').hide();
        }
    }

    /**
     * Mark a single notification as read and navigate
     */
    function markAsRead(id, link) {
        $.ajax({
            url: '/notifications/' + id + '/read',
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function() {
                // Visually mark as read
                $('.notification-item[data-id="' + id + '"]')
                    .removeClass('notification-unread')
                    .addClass('notification-read');

                // Update badge
                const badge = $('#notificationDropdown .count');
                const currentCount = parseInt(badge.text()) || 0;
                if (currentCount > 0) {
                    updateBadge(currentCount - 1);
                }

                // Navigate if link exists
                if (link) {
                    window.location.href = link;
                }
            }
        });
    }

    /**
     * Mark all notifications as read
     */
    function markAllAsRead() {
        $.ajax({
            url: '/notifications/mark-all-read',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function() {
                $('.notification-item')
                    .removeClass('notification-unread')
                    .addClass('notification-read');
                updateBadge(0);
            }
        });
    }

    /**
     * Start polling for new notifications
     */
    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(fetchNotifications, POLL_INTERVAL);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        init();
    });

    return {
        init: init,
        refresh: fetchNotifications
    };
})();
