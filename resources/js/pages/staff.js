/**
 * Staff Accounts Page Module
 */
window.StaffPage = (function() {
    let config = {};

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

    function toggleStatus(id) {
        fetch('/staff-management/staff/' + id + '/toggle-status', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update status.');
            }
        })
        .catch(() => alert('An error occurred. Please try again.'));
    }

    return {
        init: init,
        toggleFilterSection: toggleFilterSection,
        openEditModal: openEditModal,
        openDeleteModal: openDeleteModal,
        confirmDelete: confirmDelete,
        toggleStatus: toggleStatus
    };
})();
