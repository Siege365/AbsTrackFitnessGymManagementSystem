/**
 * Clients Page Module
 * Page-specific JavaScript for clients/index.blade.php
 * Uses: AvatarUtils, FormUtils, BulkSelection
 */

const ClientsPage = (function() {
  'use strict';

  // Module state
  let state = {
    avatarFile: null,
    avatarUrl: null
  };

  // Configuration passed from Laravel (set via ClientsPage.init())
  let config = {
    csrfToken: '',
    storeUrl: ''
  };

  /**
   * Get element references for add modal
   * @returns {Object} Element references
   */
  function getAddModalElements() {
    return {
      fileInput: document.getElementById('newClientAvatar'),
      urlInput: document.getElementById('newClientAvatarUrl'),
      preview: document.getElementById('newClientAvatarPreview'),
      nameInput: document.getElementById('newClientName'),
      ageInput: document.getElementById('newClientAge'),
      contactInput: document.getElementById('newClientContact'),
      planInput: document.getElementById('newClientPlan'),
      startDateInput: document.getElementById('newClientStartDate'),
      endDateInput: document.getElementById('newClientEndDate')
    };
  }

  /**
   * Toggle avatar input between file and URL
   * @param {string} type - 'file' or 'url'
   */
  function toggleClientAvatarInput(type) {
    const elements = getAddModalElements();
    AvatarUtils.toggleAvatarInput({
      fileInput: elements.fileInput,
      urlInput: elements.urlInput,
      preview: elements.preview
    }, type, state);
  }

  /**
   * Calculate end date from start date
   */
  function calculateClientEndDate() {
    const elements = getAddModalElements();
    FormUtils.calculateEndDate(elements.startDateInput, elements.endDateInput, 30);
  }

  /**
   * Preview avatar from file or URL input
   */
  function previewNewClientAvatar() {
    const elements = getAddModalElements();
    AvatarUtils.previewAvatar({
      fileInput: elements.fileInput,
      urlInput: elements.urlInput,
      preview: elements.preview
    }, state);
  }

  /**
   * Validate form and show confirmation overlay
   */
  function showClientConfirmModal() {
    const elements = getAddModalElements();
    const name = elements.nameInput.value.trim();
    const age = elements.ageInput.value;
    const contact = elements.contactInput.value.trim();
    const plan = elements.planInput.value;
    const startDate = elements.startDateInput.value;
    const endDate = elements.endDateInput.value;

    // Run validations
    const validations = [
      FormUtils.validateRequired(name, 'a name', elements.nameInput),
      FormUtils.validateAge(age, elements.ageInput),
      FormUtils.validateRequired(contact, 'a contact number', elements.contactInput),
      FormUtils.validateContact(contact, elements.contactInput),
      FormUtils.validateSelect(plan, 'a membership plan', elements.planInput),
      FormUtils.validateRequired(startDate, 'a start date', elements.startDateInput)
    ];

    const result = FormUtils.runValidations(validations);
    if (result !== true) {
      ToastUtils.showError(result, 'Validation Error');
      return;
    }

    if (!endDate) {
      ToastUtils.showError(FormUtils.MESSAGES.endDateRequired, 'Validation Error');
      elements.startDateInput.focus();
      return;
    }

    // Populate confirmation overlay
    document.getElementById('confirmClientNameText').textContent = name;
    document.getElementById('confirmClientPlanText').textContent = plan;
    document.getElementById('confirmClientDurationText').textContent = startDate + ' to ' + endDate;

    // Show confirmation overlay
    document.getElementById('addClientConfirmOverlay').style.display = 'flex';
  }

  /**
   * Go back to add form from confirmation overlay
   */
  function backToClientAddForm() {
    document.getElementById('addClientConfirmOverlay').style.display = 'none';
  }

  /**
   * Submit the client form via AJAX
   * @param {boolean} confirmSimilar - Whether to confirm similar name submission
   */
  function submitClientForm(confirmSimilar = false) {
    const submitBtn = document.querySelector('#addClientConfirmOverlay .btn-primary');
    const elements = getAddModalElements();

    try {
      // Create FormData
      const formData = new FormData();
      formData.append('name', elements.nameInput.value.trim());
      formData.append('age', elements.ageInput.value);
      formData.append('contact', elements.contactInput.value.trim());
      formData.append('plan_type', elements.planInput.value);
      formData.append('start_date', elements.startDateInput.value);
      formData.append('due_date', elements.endDateInput.value);

      // Add confirm_similar flag if confirming
      if (confirmSimilar) {
        formData.append('confirm_similar', '1');
      }

      // Append avatar
      AvatarUtils.appendAvatarToFormData(formData, {
        fileInput: elements.fileInput,
        urlInput: elements.urlInput
      }, state);

      // Submit via AJAX with custom handling for 409
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
      }

      fetch(config.storeUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': config.csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => {
        return response.json().then(data => ({ status: response.status, data }));
      })
      .then(({ status, data }) => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Confirm';
        }

        if (status === 200 || status === 201) {
          // Success
          document.getElementById('addClientConfirmOverlay').style.display = 'none';
          $('#addClientModal').modal('hide');
          ToastUtils.showSuccess(data.message || 'Client added successfully!', 'Success');
          setTimeout(() => location.reload(), 1000);
        } else if (status === 409 && data.requires_confirmation) {
          // Similar name found - show confirmation
          document.getElementById('addClientConfirmOverlay').style.display = 'none';
          
          if (confirm(`A client with a similar name "${data.existing}" already exists.\n\nDo you want to proceed anyway?`)) {
            // User confirmed, resubmit with confirm flag
            submitClientForm(true);
          }
        } else if (status === 400 && data.type === 'exact') {
          // Exact duplicate - block submission
          ToastUtils.showError(data.message || 'A client with this exact name already exists.', 'Duplicate Entry');
        } else {
          // Other errors (validation, etc.)
          ToastUtils.showError(data.message || 'Failed to add client', 'Error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Confirm';
        }
        ToastUtils.showError('An unexpected error occurred', 'Error');
      });
    } catch (error) {
      console.error('Error:', error);
      ToastUtils.showError(FormUtils.MESSAGES.unexpectedError, 'Error');
    }
  }

  // Edit modal functions
  /**
   * Toggle avatar input for edit modal
   * @param {number} clientId - Client ID
   * @param {string} type - 'file' or 'url'
   */
  function toggleEditClientAvatarInput(clientId, type) {
    const fileInput = document.getElementById('avatarInput' + clientId);
    const urlInput = document.getElementById('avatarUrl' + clientId);

    if (type === 'file') {
      fileInput.style.display = 'block';
      urlInput.style.display = 'none';
      urlInput.value = '';
    } else {
      fileInput.style.display = 'none';
      urlInput.style.display = 'block';
      fileInput.value = '';
    }
  }

  /**
   * Calculate end date for edit modal
   * @param {number} clientId - Client ID
   */
  function calculateEditClientEndDate(clientId) {
    const startDateInput = document.getElementById('editClientStartDate' + clientId);
    const endDateInput = document.getElementById('editClientEndDate' + clientId);
    FormUtils.calculateEndDate(startDateInput, endDateInput, 30);
  }

  /**
   * Preview avatar file for edit modal
   * @param {number} clientId - Client ID
   */
  function previewAvatar(clientId) {
    const input = document.getElementById('avatarInput' + clientId);
    const preview = document.getElementById('avatarPreview' + clientId);

    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.innerHTML = AvatarUtils.createPreviewImage(e.target.result);
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  /**
   * Preview avatar URL for edit modal
   * @param {number} clientId - Client ID
   */
  function previewClientAvatarUrl(clientId) {
    const urlInput = document.getElementById('avatarUrl' + clientId);
    const preview = document.getElementById('avatarPreview' + clientId);

    if (urlInput.value) {
      AvatarUtils.validateAndPreviewUrl(urlInput.value, preview, null);
    }
  }

  /**
   * Show edit confirmation overlay
   * @param {number} clientId - Client ID
   */
  function showEditClientConfirmModal(clientId) {
    const name = document.getElementById('editClientName' + clientId).value;
    const planType = document.getElementById('editClientPlanType' + clientId).value;
    const startDate = document.getElementById('editClientStartDate' + clientId).value;
    const endDate = document.getElementById('editClientEndDate' + clientId).value;

    // Validate
    if (!name.trim()) {
      ToastUtils.showError('Please enter a name.', 'Validation Error');
      return;
    }

    // Populate confirmation overlay
    document.getElementById('confirmEditClientName' + clientId).textContent = name;
    document.getElementById('confirmEditClientPlan' + clientId).textContent = planType;
    document.getElementById('confirmEditClientDuration' + clientId).textContent = `${startDate} to ${endDate}`;

    // Show overlay
    document.getElementById('editClientConfirmOverlay' + clientId).style.display = 'flex';
  }

  /**
   * Go back to edit form from confirmation
   * @param {number} clientId - Client ID
   */
  function backToEditClientForm(clientId) {
    document.getElementById('editClientConfirmOverlay' + clientId).style.display = 'none';
  }

  /**
   * Submit edit form via AJAX
   * @param {number} clientId - Client ID
   */
  function submitEditClientForm(clientId) {
    const submitBtn = document.querySelector('#editClientConfirmOverlay' + clientId + ' .btn-update');
    const form = document.getElementById('editClientForm' + clientId);
    const url = form.dataset.action;

    try {
      // Create FormData
      const formData = new FormData();
      formData.append('_method', 'PUT');
      formData.append('name', document.getElementById('editClientName' + clientId).value.trim());
      formData.append('age', document.getElementById('editClientAge' + clientId).value);
      formData.append('contact', document.getElementById('editClientContact' + clientId).value.trim());
      formData.append('plan_type', document.getElementById('editClientPlanType' + clientId).value);
      formData.append('start_date', document.getElementById('editClientStartDate' + clientId).value);
      formData.append('due_date', document.getElementById('editClientEndDate' + clientId).value);

      // Append avatar if present
      const fileInput = document.getElementById('avatarInput' + clientId);
      const urlInput = document.getElementById('avatarUrl' + clientId);
      if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('avatar', fileInput.files[0]);
      } else if (urlInput && urlInput.value.trim()) {
        formData.append('avatar_url', urlInput.value.trim());
      }

      // Submit via AJAX
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
      }

      fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': config.csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return response.json().then(data => ({ status: response.status, data }));
        } else {
          // Non-JSON response (likely a redirect)
          if (response.ok || response.status === 302) {
            return { status: 200, data: { success: true, message: 'Client updated successfully!' } };
          }
          throw new Error('Unexpected response format');
        }
      })
      .then(({ status, data }) => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
        }

        if (status === 200 || status === 201 || data.success) {
          document.getElementById('editClientConfirmOverlay' + clientId).style.display = 'none';
          $('#viewModal' + clientId).modal('hide');
          ToastUtils.showSuccess(data.message || 'Client updated successfully!', 'Success');
          setTimeout(() => location.reload(), 1000);
        } else {
          ToastUtils.showError(data.message || 'Failed to update client', 'Error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
        }
        ToastUtils.showError('An unexpected error occurred', 'Error');
      });
    } catch (error) {
      console.error('Error:', error);
      ToastUtils.showError(FormUtils.MESSAGES.unexpectedError, 'Error');
    }
  }

  /**
   * Bulk delete selected clients
   */
  function bulkDelete() {
    BulkSelection.bulkDelete({
      checkboxClass: 'client-checkbox',
      formId: 'bulkDeleteForm',
      inputName: 'client_ids[]',
      itemType: 'client'
    });
  }

  /**
   * Open renew subscription modal
   * @param {number} clientId - Client ID
   * @param {string} clientName - Client name
   * @param {string} planType - Plan type
   * @param {string} startDate - Current start date
   * @param {string} dueDate - Current due date
   */
  function openRenewClientModal(clientId, clientName, planType, startDate, dueDate) {
    // Store renewal data
    document.getElementById('renewClientId').value = clientId;
    document.getElementById('renewClientName').value = clientName;
    document.getElementById('renewClientPlanType').value = planType;
    
    // Display client info
    document.getElementById('renewClientNameDisplay').value = clientName;
    document.getElementById('renewClientPlanTypeDisplay').value = planType;
    
    // Set today's date as default start date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('renewClientStartDate').value = today;
    
    // Calculate end date
    calculateRenewClientEndDate();
    
    // Show modal
    $('#renewClientModal').modal('show');
  }

  /**
   * Calculate end date for renewal based on plan type
   */
  function calculateRenewClientEndDate() {
    const startDateInput = document.getElementById('renewClientStartDate');
    const endDateInput = document.getElementById('renewClientEndDate');
    const planType = document.getElementById('renewClientPlanType').value;
    
    if (!startDateInput.value) return;
    
    // Determine duration based on plan type
    let durationDays = 30; // Default to 1 month
    
    if (planType) {
      const planLower = planType.toLowerCase();
      if (planLower.includes('week')) {
        durationDays = 7;
      } else if (planLower.includes('day')) {
        durationDays = 1;
      } else if (planLower.includes('6 month') || planLower.includes('6month')) {
        durationDays = 180;
      } else if (planLower.includes('1 year') || planLower.includes('year')) {
        durationDays = 365;
      }
    }
    
    FormUtils.calculateEndDate(startDateInput, endDateInput, durationDays);
  }

  /**
   * Show renewal confirmation overlay
   */
  function showRenewClientConfirmModal() {
    const startDate = document.getElementById('renewClientStartDate').value;
    const endDate = document.getElementById('renewClientEndDate').value;
    const clientName = document.getElementById('renewClientName').value;
    const planType = document.getElementById('renewClientPlanType').value;
    
    // Validate
    if (!startDate) {
      ToastUtils.showError('Please select a start date.', 'Validation Error');
      document.getElementById('renewClientStartDate').focus();
      return;
    }
    
    if (!endDate) {
      ToastUtils.showError('End date is required. Please select a start date to auto-calculate.', 'Validation Error');
      document.getElementById('renewClientStartDate').focus();
      return;
    }
    
    // Populate confirmation overlay
    document.getElementById('confirmRenewClientNameText').textContent = clientName;
    document.getElementById('confirmRenewClientPlanText').textContent = planType;
    document.getElementById('confirmRenewClientDurationText').textContent = `${startDate} to ${endDate}`;
    
    // Show overlay
    document.getElementById('renewClientConfirmOverlay').style.display = 'flex';
  }

  /**
   * Go back to renew form from confirmation
   */
  function backToRenewClientForm() {
    document.getElementById('renewClientConfirmOverlay').style.display = 'none';
  }

  /**
   * Submit renewal form via AJAX
   */
  function submitRenewClientForm() {
    const submitBtn = document.querySelector('#renewClientConfirmOverlay .btn-update');
    const clientId = document.getElementById('renewClientId').value;
    const startDate = document.getElementById('renewClientStartDate').value;
    const endDate = document.getElementById('renewClientEndDate').value;
    
    const formData = new FormData();
    formData.append('start_date', startDate);
    formData.append('due_date', endDate);
    
    FormUtils.submitFormAjax({
      url: `/clients/${clientId}/renew`,
      formData: formData,
      csrfToken: config.csrfToken,
      submitBtn: submitBtn,
      onSuccess: function(data) {
        document.getElementById('renewClientConfirmOverlay').style.display = 'none';
        $('#renewClientModal').modal('hide');
        
        // Show success toast
        ToastUtils.showSuccess('Client subscription renewed successfully!', 'Success');
        updateKPIs();
        
        // Reload after toast animation
        setTimeout(() => location.reload(), 1000);
      },
      onError: function(error) {
        ToastUtils.showError('Failed to renew subscription: ' + (error.message || 'Unknown error'), 'Error');
      }
    });
  }

  /**
   * Open delete confirmation modal
   * @param {number} clientId - Client ID
   * @param {string} clientName - Client name
   * @param {string} planType - Plan type
   * @param {string} status - Current status
   */
  function openDeleteClientModal(clientId, clientName, planType, status) {
    // Store client ID for delete action
    const deleteForm = document.getElementById('deleteClientForm');
    deleteForm.action = `/clients/${clientId}`;
    deleteForm.dataset.clientId = clientId;
    
    // Populate modal
    document.getElementById('deleteClientName').textContent = clientName;
    document.getElementById('deleteClientPlan').textContent = planType;
    document.getElementById('deleteClientStatus').textContent = status;
    
    // Show modal
    $('#deleteClientConfirmModal').modal('show');
  }

  /**
   * Confirm and execute delete
   */
  function confirmDeleteClient() {
    const deleteForm = document.getElementById('deleteClientForm');
    const submitBtn = event.target;
    
    FormUtils.setButtonLoading(submitBtn, true, 'Deleting...');
    
    // Submit form
    fetch(deleteForm.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: new FormData(deleteForm)
    })
    .then(response => {
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return response.json();
      }
      // If not JSON (redirect response), assume success
      return { success: true };
    })
    .then(data => {
      if (data.success) {
        $('#deleteClientConfirmModal').modal('hide');
        ToastUtils.showSuccess('Client deleted successfully!', 'Success');
        updateKPIs();
        
        // Reload after toast animation
        setTimeout(() => location.reload(), 1000);
      } else {
        throw new Error(data.message || 'Delete failed');
      }
    })
    .catch(error => {
      FormUtils.setButtonLoading(submitBtn, false);
      ToastUtils.showError('Failed to delete client: ' + error.message, 'Error');
    });
  }

  /**
   * Update KPI cards with real-time data from server
   * Fetches fresh data from backend to ensure accuracy
   */
  function updateKPIs() {
    fetch('/clients/kpis', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(result => {
      if (result.success) {
        const data = result.data;
        const kpiCards = document.querySelectorAll('.stats-card h2');
        
        // Update KPI numbers with server data
        if (kpiCards[0]) kpiCards[0].textContent = data.total;
        if (kpiCards[1]) kpiCards[1].textContent = data.active;
        if (kpiCards[2]) kpiCards[2].textContent = data.expiring;
        if (kpiCards[3]) kpiCards[3].textContent = data.new_signups;
      } else {
        console.error('Failed to update KPIs:', result.message);
      }
    })
    .catch(error => {
      console.error('Error fetching KPIs:', error);
    });
  }

  /**
   * Setup auto-refresh timer for midnight rollover
   * KPIs are date-based, so they change at midnight
   */
  function setupMidnightRefresh() {
    // Calculate milliseconds until next midnight
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setHours(24, 0, 0, 0);
    const msUntilMidnight = tomorrow - now;
    
    // Refresh at midnight
    setTimeout(() => {
      updateKPIs();
      // Setup daily recurring refresh
      setInterval(updateKPIs, 24 * 60 * 60 * 1000);
    }, msUntilMidnight);
    
    // Also refresh every 5 minutes to keep data fresh
    setInterval(updateKPIs, 5 * 60 * 1000);
  }

  /**
   * Filter table by status
   * @param {string} status - Filter status: 'all', 'active', 'expired', 'due_soon'
   */
  function applyStatusFilter(status) {
    const rows = document.querySelectorAll('tbody tr[data-status]');
    const cards = document.querySelectorAll('.stats-card');
    
    // Remove active class from all cards
    cards.forEach(card => card.classList.remove('active'));
    
    // Show all rows
    rows.forEach(row => {
      row.style.display = '';
    });
    
    // Apply filter
    if (status !== 'all') {
      rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status').toLowerCase();
        if (status === 'active' && rowStatus !== 'active') {
          row.style.display = 'none';
        } else if (status === 'expired' && rowStatus !== 'expired') {
          row.style.display = 'none';
        } else if (status === 'due_soon' && rowStatus !== 'due soon') {
          row.style.display = 'none';
        }
      });
    }
  }

  /**
   * Initialize the page
   * @param {Object} options - Configuration options
   * @param {string} options.csrfToken - CSRF token
   * @param {string} options.storeUrl - Store URL for AJAX submission
   */
  function init(options) {
    config = { ...config, ...options };

    // Initialize bulk selection
    BulkSelection.init({
      selectAllId: 'selectAll',
      checkboxClass: 'client-checkbox',
      selectedCountId: 'selectedCount'
    });

    // Setup search enter key
    FormUtils.setupSearchEnter(
      document.getElementById('searchInput'),
      document.getElementById('searchForm')
    );

  }

  // Public API
  return {
    init,
    toggleClientAvatarInput,
    calculateClientEndDate,
    previewNewClientAvatar,
    showClientConfirmModal,
    backToClientAddForm,
    submitClientForm,
    toggleEditClientAvatarInput,
    calculateEditClientEndDate,
    previewAvatar,
    previewClientAvatarUrl,
    bulkDelete,
    openRenewClientModal,
    calculateRenewClientEndDate,
    showRenewClientConfirmModal,
    backToRenewClientForm,
    submitRenewClientForm,
    showEditClientConfirmModal,
    backToEditClientForm,
    submitEditClientForm,
    openDeleteClientModal,
    confirmDeleteClient,
    updateKPIs,
    applyStatusFilter,
    setupMidnightRefresh
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ClientsPage;
}
