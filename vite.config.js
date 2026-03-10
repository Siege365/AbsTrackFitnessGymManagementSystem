import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Global / Layout CSS
                'resources/css/core.css',
                'resources/css/sidebar.css',
                'resources/css/custom-fonts.css',
                'resources/css/notification-bell.css',
                'resources/css/global-theme.css',
                'resources/css/pagination.css',
                'resources/css/autocomplete.css',

                // Page-specific CSS
                'resources/css/dashboard.css',
                'resources/css/clients.css',
                'resources/css/memberships.css',
                'resources/css/sessions.css',
                'resources/css/inventory.css',
                'resources/css/transaction-history.css',
                'resources/css/payment.css',
                'resources/css/product-payment.css',
                'resources/css/membership-payment.css',
                'resources/css/reports.css',
                'resources/css/payment-history.css',
                'resources/css/activity-logs.css',
                'resources/css/configuration.css',
                'resources/css/account-settings.css',
                'resources/css/staff-management.css',
                'resources/css/notifications-page.css',

                // Auth CSS
                'resources/css/auth-login.css',
                'resources/css/auth-register.css',

                // Common JS utilities
                'resources/js/common/kpi-utils.js',
                'resources/js/common/toast-utils.js',
                'resources/js/common/avatar-utils.js',
                'resources/js/common/form-utils.js',
                'resources/js/common/bulk-selection.js',
                'resources/js/common/sidebar.js',
                'resources/js/common/table-dropdown.js',
                'resources/js/common/autocomplete-utils.js',
                'resources/js/common/notification-bell.js',

                // Page-specific JS
                'resources/js/pages/dashboard.js',
                'resources/js/pages/clients.js',
                'resources/js/pages/memberships.js',
                'resources/js/pages/payments.js',
                'resources/js/pages/payment-system.js',
                'resources/js/pages/membership-payment.js',
                'resources/js/pages/pt-payment.js',
                'resources/js/pages/product-payment.js',
                'resources/js/pages/payment-history.js',
                'resources/js/pages/reports.js',
                'resources/js/pages/pt-sessions.js',
                'resources/js/pages/customer-attendance.js',
                'resources/js/pages/inventory.js',
                'resources/js/pages/inventory-logs.js',
                'resources/js/sessions.js',
                'resources/js/pages/configuration.js',
                'resources/js/pages/staff.js',
                'resources/js/pages/trainers.js',
                'resources/js/notifications/page.js',

                // Auth JS
                'resources/js/auth-login.js',
                'resources/js/auth-register.js',
            ],
            refresh: true,
        }),
    ],
});
