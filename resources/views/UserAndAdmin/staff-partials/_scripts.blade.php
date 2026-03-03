<!-- Common Utilities -->
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/bulk-selection.js'])
<!-- Page Module -->
@vite(['resources/js/pages/staff.js'])
<script>
  // Initialize staff page
  document.addEventListener('DOMContentLoaded', function() {
    StaffPage.init({
      csrfToken: '{{ csrf_token() }}',
      storeUrl: '{{ route("staff.store") }}'
    });

    const searchInput = document.getElementById('searchInputStaff');
    const searchForm = document.getElementById('searchFormStaff');
    if (searchInput && searchForm) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchForm.submit();
        }
      });
    }
  });

  // ============================================
  // Global function wrappers for onclick handlers
  // ============================================

  // --- Add Staff ---
  function showStaffConfirmModal() {
    // Validate contact before showing confirm
    const contactInput = document.getElementById('newStaffContact');
    const contactValue = contactInput.value.trim();
    if (contactValue) {
      const digitsOnly = contactValue.replace(/\D/g, '');
      if (contactValue.startsWith('+63')) {
        if (digitsOnly.length !== 12) {
          ToastUtils.showError('Phone number with +63 must have exactly 12 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      } else {
        if (digitsOnly.length !== 11) {
          ToastUtils.showError('Phone number must have exactly 11 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
        if (!digitsOnly.startsWith('09')) {
          ToastUtils.showError('Phone number must start with 09', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      }
    }

    // Validate passwords match
    const password = document.getElementById('newStaffPassword').value;
    const passwordConfirm = document.getElementById('newStaffPasswordConfirm').value;
    if (password !== passwordConfirm) {
      ToastUtils.showError('Passwords do not match', 'Validation Error');
      document.getElementById('newStaffPasswordConfirm').focus();
      return;
    }
    if (password.length < 8) {
      ToastUtils.showError('Password must be at least 8 characters', 'Validation Error');
      document.getElementById('newStaffPassword').focus();
      return;
    }

    StaffPage.showStaffConfirmModal();
  }

  function backToStaffAddForm() { StaffPage.backToStaffAddForm(); }
  function submitStaffForm() { StaffPage.submitStaffForm(); }

  // --- Edit Staff ---
  function showEditStaffConfirmModal(staffId) {
    // Validate contact before showing confirm
    const contactInput = document.getElementById('editStaffContact' + staffId);
    const contactValue = contactInput.value.trim();
    if (contactValue) {
      const digitsOnly = contactValue.replace(/\D/g, '');
      if (contactValue.startsWith('+63')) {
        if (digitsOnly.length !== 12) {
          ToastUtils.showError('Phone number with +63 must have exactly 12 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      } else {
        if (digitsOnly.length !== 11) {
          ToastUtils.showError('Phone number must have exactly 11 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
        if (!digitsOnly.startsWith('09')) {
          ToastUtils.showError('Phone number must start with 09', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      }
    }

    // Validate passwords match if provided
    const password = document.getElementById('editStaffPassword' + staffId).value;
    const passwordConfirm = document.getElementById('editStaffPasswordConfirm' + staffId).value;
    if (password && password !== passwordConfirm) {
      ToastUtils.showError('Passwords do not match', 'Validation Error');
      document.getElementById('editStaffPasswordConfirm' + staffId).focus();
      return;
    }
    if (password && password.length < 8) {
      ToastUtils.showError('Password must be at least 8 characters', 'Validation Error');
      document.getElementById('editStaffPassword' + staffId).focus();
      return;
    }

    StaffPage.showEditStaffConfirmModal(staffId);
  }

  function backToEditStaffForm(staffId) { StaffPage.backToEditStaffForm(staffId); }
  function submitEditStaffForm(staffId) { StaffPage.submitEditStaffForm(staffId); }

  // --- Delete Staff ---
  function openDeleteStaffModal(staffId, staffName, staffEmail) {
    StaffPage.openDeleteStaffModal(staffId, staffName, staffEmail);
  }
  function confirmDeleteStaff() { StaffPage.confirmDeleteStaff(); }

  // --- Bulk Delete ---
  function bulkDeleteStaff() {
    const checkedBoxes = document.querySelectorAll('.staff-checkbox:checked');
    if (checkedBoxes.length === 0) {
      ToastUtils.showWarning('Please select at least one staff to delete.', 'Warning');
      return false;
    }
    document.getElementById('bulkDeleteCount').textContent = checkedBoxes.length;
    $('#bulkDeleteStaffConfirmModal').modal('show');
  }

  function confirmBulkDeleteStaff() {
    const submitBtn = event.target;
    const form = document.getElementById('bulkDeleteForm');
    const checkedBoxes = document.querySelectorAll('.staff-checkbox:checked');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
    form.querySelectorAll('input[name="staff_ids[]"]').forEach(el => el.remove());
    checkedBoxes.forEach(checkbox => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'staff_ids[]';
      input.value = checkbox.value;
      form.appendChild(input);
    });
    $('#bulkDeleteStaffConfirmModal').modal('hide');
    form.submit();
  }

  // --- Password Toggle ---
  function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('mdi-eye-off');
      icon.classList.add('mdi-eye');
    } else {
      input.type = 'password';
      icon.classList.remove('mdi-eye');
      icon.classList.add('mdi-eye-off');
    }
  }

  // --- Phone Formatting ---
  function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substring(0, 11);
    let formatted = '';
    if (value.length > 0) {
      formatted = value.substring(0, 4);
      if (value.length > 4) formatted += '-' + value.substring(4, 7);
      if (value.length > 7) formatted += '-' + value.substring(7, 11);
    }
    input.value = formatted;

    const errorDiv = document.getElementById(input.id + 'Error');
    let errorMessage = '';
    if (value.length === 0) {
      input.setCustomValidity('');
      if (errorDiv) { errorDiv.style.display = 'none'; input.classList.remove('is-invalid', 'is-valid'); }
    } else if (value.length < 11) {
      errorMessage = `Phone number must have exactly 11 digits. Current: ${value.length} digit${value.length !== 1 ? 's' : ''}`;
      input.setCustomValidity(errorMessage);
      if (errorDiv) { errorDiv.textContent = errorMessage; errorDiv.style.display = 'block'; input.classList.add('is-invalid'); input.classList.remove('is-valid'); }
    } else if (!value.startsWith('09')) {
      errorMessage = 'Phone number must start with 09';
      input.setCustomValidity(errorMessage);
      if (errorDiv) { errorDiv.textContent = errorMessage; errorDiv.style.display = 'block'; input.classList.add('is-invalid'); input.classList.remove('is-valid'); }
    } else {
      input.setCustomValidity('');
      if (errorDiv) { errorDiv.style.display = 'none'; input.classList.remove('is-invalid'); input.classList.add('is-valid'); }
    }
  }

  // Dropdown toggle
  document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        if (menu !== this.nextElementSibling) menu.classList.remove('show');
      });
      const menu = this.nextElementSibling;
      if (menu?.classList.contains('dropdown-menu')) menu.classList.toggle('show');
    });
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => menu.classList.remove('show'));
    }
  });
</script>
