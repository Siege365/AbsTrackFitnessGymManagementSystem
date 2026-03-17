/**
 * Staff Accounts Page Module
 */
window.StaffPage = (function() {
    let config = {};
    let pendingToggleId = null;
    let turnstileWidgetId = null;
    let turnstileToken = null;

    function init(options) {
        config = options || {};
    }

    function toggleFilterSection(header, event) {
        event.preventDefault();
        event.stopPropagation();
        const content = header.nextElementSibling;
        if (content) {
            content.classList.toggle('show');
        }
    }

    function openEditModal(user) {
        document.getElementById('editStaffName').value = user.name || '';
        document.getElementById('editStaffEmail').value = user.email || '';
        document.getElementById('editStaffContact').value = user.contact || '';
        document.getElementById('editStaffAddress').value = user.address || '';
        document.getElementById('editStaffRole').value = user.role || 'cashier';
        document.getElementById('editStaffStatus').value = user.status || 'active';

        const form = document.getElementById('editStaffForm');
        form.action = '/staff-management/staff/' + user.id;

        $('#editStaffModal').modal('show');
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteStaffName').textContent = name;
        const form = document.getElementById('deleteStaffForm');
        form.action = '/staff-management/staff/' + id;
        $('#deleteStaffModal').modal('show');
    }

    function confirmDelete() {
        document.getElementById('deleteStaffForm').submit();
    }

    function toggleStatus(id, name, currentStatus) {
        pendingToggleId = id;
        turnstileToken = null;
        const action = currentStatus === 'active' ? 'Deactivate' : 'Activate';
        document.getElementById('toggleStatusTitle').textContent = 'Confirm ' + action;
        document.getElementById('toggleStatusMessage').textContent =
            'Are you sure you want to ' + action.toLowerCase() + ' this staff account' + (name ? ' (' + name + ')' : '') + '?';
        document.getElementById('confirmToggleStatusBtn').disabled = true;

        // Reset and render Turnstile widget
        const container = document.getElementById('toggleStatusTurnstile');
        container.innerHTML = '';
        if (typeof turnstile !== 'undefined' && config.turnstileSiteKey) {
            turnstileWidgetId = turnstile.render(container, {
                sitekey: config.turnstileSiteKey,
                theme: 'dark',
                callback: function(token) {
                    turnstileToken = token;
                    document.getElementById('confirmToggleStatusBtn').disabled = false;
                },
                'expired-callback': function() {
                    turnstileToken = null;
                    document.getElementById('confirmToggleStatusBtn').disabled = true;
                }
            });
        }

        $('#toggleStatusModal').modal('show');
    }

    function confirmToggleStatus() {
        if (!pendingToggleId || !turnstileToken) return;
        document.getElementById('confirmToggleStatusBtn').disabled = true;

        fetch('/staff-management/staff/' + pendingToggleId + '/toggle-status', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 'cf-turnstile-response': turnstileToken })
        })
        .then(response => response.json())
        .then(data => {
            $('#toggleStatusModal').modal('hide');
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update status.');
            }
        })
        .catch(() => {
            $('#toggleStatusModal').modal('hide');
            alert('An error occurred. Please try again.');
        });
    }

    // Clean up turnstile widget when modal is hidden
    $(document).on('hidden.bs.modal', '#toggleStatusModal', function() {
        if (turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
            turnstile.remove(turnstileWidgetId);
            turnstileWidgetId = null;
        }
        turnstileToken = null;
        pendingToggleId = null;
    });

    return {
        init: init,
        toggleFilterSection: toggleFilterSection,
        openEditModal: openEditModal,
        openDeleteModal: openDeleteModal,
        confirmDelete: confirmDelete,
        toggleStatus: toggleStatus,
        confirmToggleStatus: confirmToggleStatus
    };
})();
