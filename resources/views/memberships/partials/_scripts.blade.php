<!-- Common Utilities -->
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/avatar-utils.js'])
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/bulk-selection.js'])
@vite(['resources/js/common/autocomplete-utils.js'])
@vite(['resources/css/autocomplete.css'])
<!-- Page Module -->
@vite(['resources/js/pages/memberships.js'])
<script>
  // Initialize memberships page with Laravel data
  document.addEventListener('DOMContentLoaded', function() {
    MembershipsPage.init({
      csrfToken: '{{ csrf_token() }}',
      storeUrl: '{{ route("memberships.store") }}'
    });
    
    // Setup midnight auto-refresh for KPIs
    MembershipsPage.setupMidnightRefresh();
  });

  // Global function wrappers for onclick handlers in HTML
  function toggleAvatarInput(type) {
    MembershipsPage.toggleAvatarInput(type);
  }

  function calculateEndDate() {
    MembershipsPage.calculateEndDate();
  }

  function previewNewAvatar() {
    MembershipsPage.previewNewAvatar();
  }

  function showConfirmModal() {
    MembershipsPage.showConfirmModal();
  }

  function backToAddForm() {
    MembershipsPage.backToAddForm();
  }

  function submitMemberForm() {
    MembershipsPage.submitMemberForm();
  }

  function toggleEditAvatarInput(membershipId, type) {
    MembershipsPage.toggleEditAvatarInput(membershipId, type);
  }

  function calculateEditEndDate(membershipId) {
    MembershipsPage.calculateEditEndDate(membershipId);
  }

  function previewAvatar(membershipId) {
    MembershipsPage.previewAvatar(membershipId);
  }

  function previewAvatarUrl(membershipId) {
    MembershipsPage.previewAvatarUrl(membershipId);
  }

  function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.membership-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
      ToastUtils.showWarning('No members selected for deletion', 'Warning');
      return false;
    }
    
    // Update count in modal
    document.getElementById('bulkDeleteCount').textContent = checkedBoxes.length;
    
    // Reset confirm input
    const confirmInput = document.getElementById('bulkDeleteConfirmInput');
    const confirmBtn = document.getElementById('bulkDeleteConfirmBtn');
    const confirmError = document.getElementById('bulkDeleteConfirmError');
    if (confirmInput) confirmInput.value = '';
    if (confirmBtn) confirmBtn.disabled = true;
    if (confirmError) confirmError.classList.add('d-none');
    
    // Show confirmation modal
    $('#bulkDeleteConfirmModal').modal('show');
  }

  // Wire up type-to-confirm for bulk delete and single delete
  document.addEventListener('DOMContentLoaded', function() {
    const bulkInput = document.getElementById('bulkDeleteConfirmInput');
    const bulkBtn = document.getElementById('bulkDeleteConfirmBtn');
    if (bulkInput && bulkBtn) {
      bulkInput.addEventListener('input', function() {
        bulkBtn.disabled = this.value.trim().toLowerCase() !== 'delete';
      });
    }

    const deleteInput = document.getElementById('deleteMemberConfirmInput');
    const deleteBtn = document.getElementById('deleteMemberConfirmBtn');
    if (deleteInput && deleteBtn) {
      deleteInput.addEventListener('input', function() {
        deleteBtn.disabled = this.value.trim().toLowerCase() !== 'delete';
      });
    }

    $('#deleteConfirmModal').on('show.bs.modal', function() {
      const inp = document.getElementById('deleteMemberConfirmInput');
      const btn = document.getElementById('deleteMemberConfirmBtn');
      if (inp) inp.value = '';
      if (btn) btn.disabled = true;
    });

    $('#bulkDeleteConfirmModal').on('hidden.bs.modal', function() {
      const inp = document.getElementById('bulkDeleteConfirmInput');
      const btn = document.getElementById('bulkDeleteConfirmBtn');
      if (inp) inp.value = '';
      if (btn) btn.disabled = true;
    });
  });
  
  /**
   * Confirm and execute bulk delete
   */
  function confirmBulkDelete() {
    const submitBtn = event.target;
    const confirmInput = document.getElementById('bulkDeleteConfirmInput');
    if (!confirmInput || confirmInput.value.trim().toLowerCase() !== 'delete') {
      const err = document.getElementById('bulkDeleteConfirmError');
      if (err) err.classList.remove('d-none');
      return;
    }
    const form = document.getElementById('bulkDeleteForm');
    const checkedBoxes = document.querySelectorAll('.membership-checkbox:checked');
    
    // Set button loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
    
    // Remove any existing hidden inputs
    form.querySelectorAll('input[name="membership_ids[]"]').forEach(el => el.remove());
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'membership_ids[]';
      input.value = checkbox.value;
      form.appendChild(input);
    });
    
    // Close modal and submit form
    $('#bulkDeleteConfirmModal').modal('hide');
    form.submit();
  }

  function openRenewModal(membershipId, memberName, planName, planKey, durationDays, startDate, dueDate) {
    MembershipsPage.openRenewModal(membershipId, memberName, planName, planKey, durationDays, startDate, dueDate);
  }

  function calculateRenewEndDate() {
    MembershipsPage.calculateRenewEndDate();
  }

  function showRenewConfirmModal() {
    MembershipsPage.showRenewConfirmModal();
  }

  function backToRenewForm() {
    MembershipsPage.backToRenewForm();
  }

  function submitRenewForm() {
    MembershipsPage.submitRenewForm();
  }

  function showEditConfirmModal(membershipId) {
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('editContact' + membershipId);
    const contactValue = contactInput.value.trim();
    const digitsOnly = contactValue.replace(/\D/g, '');
    
    // Check if contact is valid
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
    
    MembershipsPage.showEditConfirmModal(membershipId);
  }

  function backToEditForm(membershipId) {
    MembershipsPage.backToEditForm(membershipId);
  }

  function submitEditForm(membershipId) {
    MembershipsPage.submitEditForm(membershipId);
  }

  function openDeleteModal(membershipId, memberName, planType, status) {
    MembershipsPage.openDeleteModal(membershipId, memberName, planType, status);
  }

  function confirmDelete() {
    MembershipsPage.confirmDelete();
  }

  // Contact number validation
  function formatPhoneNumber(input) {
    // Remove all non-numeric characters
    let value = input.value.replace(/\D/g, '');
    
    // Limit to 11 digits
    if (value.length > 11) {
      value = value.substring(0, 11);
    }
    
    // Format as 09XX-XXX-XXXX
    let formatted = '';
    if (value.length > 0) {
      formatted = value.substring(0, 4);
      if (value.length > 4) {
        formatted += '-' + value.substring(4, 7);
      }
      if (value.length > 7) {
        formatted += '-' + value.substring(7, 11);
      }
    }
    
    input.value = formatted;

    // Get error message element
    const errorDiv = document.getElementById(input.id + 'Error');
    let errorMessage = '';

    // Validate
    if (value.length === 0) {
      // Empty - required validation will handle this
      input.setCustomValidity('');
      if (errorDiv) {
        errorDiv.style.display = 'none';
        input.classList.remove('is-invalid');
        input.classList.remove('is-valid');
      }
    } else if (value.length < 11) {
      errorMessage = `Phone number must have exactly 11 digits. Current: ${value.length} digit${value.length !== 1 ? 's' : ''}`;
      input.setCustomValidity(errorMessage);
      if (errorDiv) {
        errorDiv.textContent = errorMessage;
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
      }
    } else if (!value.startsWith('09')) {
      errorMessage = 'Phone number must start with 09';
      input.setCustomValidity(errorMessage);
      if (errorDiv) {
        errorDiv.textContent = errorMessage;
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
      }
    } else {
      // Valid phone number
      input.setCustomValidity('');
      if (errorDiv) {
        errorDiv.style.display = 'none';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
      }
    }
  }

</script>
