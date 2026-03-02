/**
 * Staff Accounts Page Module
 * Handles CRUD operations for staff management
 */
const StaffPage = (function() {
  'use strict';

  let config = {
    csrfToken: '',
    storeUrl: ''
  };

  let state = {
    deleteStaffId: null
  };

  // ============================================
  // Initialization
  // ============================================
  function init(options) {
    config = { ...config, ...options };

    // Setup bulk selection
    if (typeof BulkSelection !== 'undefined') {
      BulkSelection.init({
        selectAllId: 'selectAll',
        checkboxClass: 'staff-checkbox',
        selectedCountId: 'selectedCount'
      });
    }

    // Reset modals on close
    $('.modal').on('hidden.bs.modal', function() {
      const form = $(this).find('form')[0];
      if (form && form.id === 'addStaffForm') {
        form.reset();
        // Reset password visibility
        $(this).find('input[type="text"][id*="Password"]').attr('type', 'password');
        $(this).find('.toggle-password i').removeClass('mdi-eye').addClass('mdi-eye-off');
      }
      // Hide all overlays
      $(this).find('.confirm-overlay').hide();
    });
  }

  // ============================================
  // Add Staff
  // ============================================
  function showStaffConfirmModal() {
    const name = document.getElementById('newStaffName').value.trim();
    const email = document.getElementById('newStaffEmail').value.trim();
    const contact = document.getElementById('newStaffContact').value.trim();

    // Basic validation
    if (!name || !email || !contact) {
    ToastUtils.showError('Please fill in all required fields', 'Validation Error');
    return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
    ToastUtils.showError('Please enter a valid email address', 'Validation Error');
    document.getElementById('newStaffEmail').focus();
    return;
    }

    // Set confirm overlay text
    document.getElementById('confirmStaffNameText').textContent = name;
    document.getElementById('confirmStaffEmailText').textContent = email;
    document.getElementById('confirmStaffContactText').textContent = contact;

    // Show overlay
    document.getElementById('addStaffConfirmOverlay').style.display = 'flex';
}

function backToStaffAddForm() {
    document.getElementById('addStaffConfirmOverlay').style.display = 'none';
}

 function submitStaffForm() {
    const btn = document.getElementById('confirmAddStaffBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Creating...';

    const formData = new FormData();
    formData.append('name', document.getElementById('newStaffName').value.trim());
    formData.append('email', document.getElementById('newStaffEmail').value.trim());
    formData.append('password', document.getElementById('newStaffPassword').value);
    formData.append('password_confirmation', document.getElementById('newStaffPasswordConfirm').value);
    formData.append('contact_number', document.getElementById('newStaffContact').value.trim());
    formData.append('emergency_contact', document.getElementById('newStaffEmergencyContact').value.trim());
    formData.append('address', document.getElementById('newStaffAddress').value.trim());

    fetch(config.storeUrl, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'Accept': 'application/json'
    },
    body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
    if (status === 200 || status === 201) {
    ToastUtils.showSuccess(data.message || 'Staff account created successfully!', 'Success');
    setTimeout(() => location.reload(), 1000);
    } else if (status === 422 && data.errors) {
    const firstError = Object.values(data.errors)[0][0];
    ToastUtils.showError(firstError, 'Validation Error');
    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
    } else {
    ToastUtils.showError(data.message || 'An error occurred', 'Error');
    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
    }
    })
    .catch(error => {
    console.error('Error:', error);
    ToastUtils.showError('An unexpected error occurred', 'Error');
    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
    });
}

  // ============================================
  // Edit Staff
  // ============================================
function showEditStaffConfirmModal(staffId) {
    const name = document.getElementById('editStaffName' + staffId).value.trim();
    const email = document.getElementById('editStaffEmail' + staffId).value.trim();
    const password = document.getElementById('editStaffPassword' + staffId).value;

    if (!name || !email) {
    ToastUtils.showError('Please fill in all required fields', 'Validation Error');
    return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
    ToastUtils.showError('Please enter a valid email address', 'Validation Error');
    return;
    }

    document.getElementById('confirmEditStaffName' + staffId).textContent = name;
    document.getElementById('confirmEditStaffEmail' + staffId).textContent = email;
    document.getElementById('confirmEditStaffPassword' + staffId).textContent = password ? '••••••••  (will be changed)' : 'No change';

    document.getElementById('editStaffConfirmOverlay' + staffId).style.display = 'flex';
}

function backToEditStaffForm(staffId) {
    document.getElementById('editStaffConfirmOverlay' + staffId).style.display = 'none';
}

function submitEditStaffForm(staffId) {
    const btn = document.getElementById('confirmEditStaffBtn' + staffId);
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Updating...';

    const form = document.getElementById('editStaffForm' + staffId);
    const actionUrl = form.dataset.action;

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', document.getElementById('editStaffName' + staffId).value.trim());
    formData.append('email', document.getElementById('editStaffEmail' + staffId).value.trim());
    formData.append('contact_number', document.getElementById('editStaffContact' + staffId).value.trim());
    formData.append('emergency_contact', document.getElementById('editStaffEmergencyContact' + staffId).value.trim());
    formData.append('address', document.getElementById('editStaffAddress' + staffId).value.trim());

    const password = document.getElementById('editStaffPassword' + staffId).value;
    if (password) {
      formData.append('password', password);
      formData.append('password_confirmation', document.getElementById('editStaffPasswordConfirm' + staffId).value);
    }

    fetch(actionUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
      if (status === 200 || status === 201) {
        ToastUtils.showSuccess(data.message || 'Staff account updated successfully!', 'Success');
        setTimeout(() => location.reload(), 1000);
      } else if (status === 422 && data.errors) {
        const firstError = Object.values(data.errors)[0][0];
        ToastUtils.showError(firstError, 'Validation Error');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Update';
      } else {
        ToastUtils.showError(data.message || 'An error occurred', 'Error');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Update';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An unexpected error occurred', 'Error');
      btn.disabled = false;
      btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Update';
    });
  }

  // ============================================
  // Delete Staff
  // ============================================
  function openDeleteStaffModal(staffId, staffName, staffEmail) {
    state.deleteStaffId = staffId;
    document.getElementById('deleteStaffName').textContent = staffName;
    document.getElementById('deleteStaffEmail').textContent = staffEmail;

    const form = document.getElementById('deleteStaffForm');
    form.action = '/staff/' + staffId;

    $('#deleteStaffConfirmModal').modal('show');
  }

  function confirmDeleteStaff() {
    if (!state.deleteStaffId) return;

    const btn = document.getElementById('confirmDeleteStaffBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Deleting...';

    fetch('/staff/' + state.deleteStaffId, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'Accept': 'application/json'
      }
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
      if (status === 200) {
        ToastUtils.showSuccess(data.message || 'Staff account deleted successfully!', 'Success');
        $('#deleteStaffConfirmModal').modal('hide');
        setTimeout(() => location.reload(), 1000);
      } else {
        ToastUtils.showError(data.message || 'An error occurred', 'Error');
        btn.disabled = false;
        btn.innerHTML = 'Delete Staff';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An unexpected error occurred', 'Error');
      btn.disabled = false;
      btn.innerHTML = 'Delete Staff';
    });
  }

  // ============================================
  // Public API
  // ============================================
  return {
    init,
    showStaffConfirmModal,
    backToStaffAddForm,
    submitStaffForm,
    showEditStaffConfirmModal,
    backToEditStaffForm,
    submitEditStaffForm,
    openDeleteStaffModal,
    confirmDeleteStaff
  };
})();

// Expose globally
window.StaffPage = StaffPage;
