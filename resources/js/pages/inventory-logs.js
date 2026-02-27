/**
 * Inventory Logs Page
 * Handles search with debounce, filter toggles, and dropdown behavior.
 */
const InventoryLogsPage = (function() {
    'use strict';

    function init() {
        initSearch();
        initDropdownToggles();
    }

    // ============================================
    // Real-time Search with Debounce
    // ============================================
    function initSearch() {
        let searchTimeout;
        const searchInput = document.getElementById('logsSearchInput');
        const searchForm = document.getElementById('logsSearchForm');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchForm.submit();
                }, 750);
            });
        }
    }

    // ============================================
    // Dropdown Toggle
    // ============================================
    function initDropdownToggles() {
        document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.remove('show');
                    }
                });
                
                const menu = this.nextElementSibling;
                if (menu?.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('show');
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    return { init };
})();

// ============================================
// Global Functions (called from onclick attributes)
// ============================================

function toggleFilterSection(header, event) {
    event.preventDefault();
    event.stopPropagation();
    const section = header.parentElement;
    section.classList.toggle('active');
}

function clearLogsSearch() {
    const input = document.getElementById('logsSearchInput');
    if (input) {
        input.value = '';
        document.getElementById('logsSearchForm').submit();
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    InventoryLogsPage.init();
});

// Backward compatibility alias
window.InventoryLogsPage = InventoryLogsPage;
