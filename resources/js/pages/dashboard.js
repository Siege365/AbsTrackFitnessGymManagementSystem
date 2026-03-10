/**
 * Dashboard Page JavaScript
 * Handles KPI formatting and interactive elements for the dashboard.
 */

document.addEventListener('DOMContentLoaded', function () {
    // KPI formatting is handled globally by kpi-utils.js (loaded in layout)
    // This file handles dashboard-specific interactions

    initSubsystemCardLinks();
});

/**
 * Makes entire subsystem cards clickable, navigating to the "View All" link.
 */
function initSubsystemCardLinks() {
    document.querySelectorAll('.subsystem-card').forEach(function (card) {
        const link = card.querySelector('.subsystem-link');
        if (!link) return;

        card.style.cursor = 'pointer';
        card.addEventListener('click', function (e) {
            // Don't navigate if user clicked the link itself (let it handle naturally)
            if (e.target.closest('.subsystem-link')) return;
            window.location.href = link.href;
        });
    });
}
