<!-- Common Utilities -->
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/avatar-utils.js'])
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/bulk-selection.js'])
@vite(['resources/js/common/autocomplete-utils.js'])
@vite(['resources/css/autocomplete.css'])
<!-- Page Module -->
@vite(['resources/js/pages/clients.js'])
<script>
  // Initialize clients page with Laravel data
  document.addEventListener('DOMContentLoaded', function() {
    ClientsPage.init({
      csrfToken: '{{ csrf_token() }}',
      storeUrl: '{{ route("clients.store") }}'
    });
    
    // Setup midnight auto-refresh for KPIs
    ClientsPage.setupMidnightRefresh();
    
    // Setup client search enter key (uses different IDs than memberships)
    const searchInput = document.getElementById('searchInputClients');
    const searchForm = document.getElementById('searchFormClients');
    if (searchInput && searchForm) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchForm.submit();
        }
      });
    }
  });

  // Global function wrappers for onclick handlers in HTML
  function toggleClientAvatarInput(type) {
    ClientsPage.toggleClientAvatarInput(type);
  }

  function calculateClientEndDate() {
    ClientsPage.calculateClientEndDate();
  }

  function previewNewClientAvatar() {
    ClientsPage.previewNewClientAvatar();
  }

  function showClientConfirmModal() {
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('newClientContact');
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
    
    ClientsPage.showClientConfirmModal();
  }

  function backToClientAddForm() {
    ClientsPage.backToClientAddForm();
  }

  function submitClientForm() {
    ClientsPage.submitClientForm();
  }

  function toggleEditClientAvatarInput(clientId, type) {
    ClientsPage.toggleEditClientAvatarInput(clientId, type);
  }

  function calculateEditClientEndDate(clientId) {
    ClientsPage.calculateEditClientEndDate(clientId);
  }

  function previewAvatar(clientId) {
    ClientsPage.previewAvatar(clientId);
  }

  function previewClientAvatarUrl(clientId) {
    ClientsPage.previewClientAvatarUrl(clientId);
  }

  function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.client-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
      ToastUtils.showWarning('Please select at least one client to delete.', 'Warning');
      return false;
    }
    
    // Update count in modal
    document.getElementById('bulkDeleteCount').textContent = checkedBoxes.length;
    
    // Show confirmation modal
    $('#bulkDeleteConfirmModal').modal('show');
  }
  
  /**
   * Confirm and execute bulk delete
   */
  function confirmBulkDelete() {
    const form = document.getElementById('bulkDeleteForm');
    const checkedBoxes = document.querySelectorAll('.client-checkbox:checked');
    
    // Remove any existing hidden inputs
    form.querySelectorAll('input[name="client_ids[]"]').forEach(el => el.remove());
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'client_ids[]';
      input.value = checkbox.value;
      form.appendChild(input);
    });
    
    // Close modal and submit form
    $('#bulkDeleteConfirmModal').modal('hide');
    form.submit();
  }

  function openRenewClientModal(clientId, clientName, planName, planKey, durationDays, startDate, dueDate) {
    ClientsPage.openRenewClientModal(clientId, clientName, planName, planKey, durationDays, startDate, dueDate);
  }

  function calculateRenewClientEndDate() {
    ClientsPage.calculateRenewClientEndDate();
  }

  function showRenewClientConfirmModal() {
    ClientsPage.showRenewClientConfirmModal();
  }

  function backToRenewClientForm() {
    ClientsPage.backToRenewClientForm();
  }

  function submitRenewClientForm() {
    ClientsPage.submitRenewClientForm();
  }

  function showEditClientConfirmModal(clientId) {
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('editClientContact' + clientId);
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
    
    ClientsPage.showEditClientConfirmModal(clientId);
  }

  function backToEditClientForm(clientId) {
    ClientsPage.backToEditClientForm(clientId);
  }

  function submitEditClientForm(clientId) {
    ClientsPage.submitEditClientForm(clientId);
  }

  function openDeleteClientModal(clientId, clientName, planType, status) {
    ClientsPage.openDeleteClientModal(clientId, clientName, planType, status);
  }

  function confirmDeleteClient() {
    ClientsPage.confirmDeleteClient();
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
