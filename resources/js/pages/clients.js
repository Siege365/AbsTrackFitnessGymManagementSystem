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

  // Allowed avatar file types and max size
  const ALLOWED_AVATAR_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  const ALLOWED_AVATAR_EXTENSIONS = ['jpeg', 'jpg', 'png', 'gif'];
  const MAX_AVATAR_SIZE = 2 * 1024 * 1024; // 2MB in bytes

  // Configuration passed from Laravel (set via ClientsPage.init())
  let config = {
    csrfToken: '',
    storeUrl: ''
  };

  /**
   * Get duration in days from select option data attribute
   * @param {HTMLSelectElement} selectElement
   * @returns {number}
   */
  function getPlanDurationFromSelect(selectElement) {
    if (!selectElement || !selectElement.value) return 30;
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const duration = selectedOption ? parseInt(selectedOption.getAttribute('data-duration')) : null;
    return duration || 30;
  }

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
    const days = getPlanDurationFromSelect(elements.planInput);
    FormUtils.calculateEndDate(elements.startDateInput, elements.endDateInput, days);
  }

  /**
   * Preview avatar from file or URL input
   */
  function previewNewClientAvatar() {
    const elements = getAddModalElements();
    const fileInput = elements.fileInput;

    if (fileInput && fileInput.files && fileInput.files[0]) {
      const file = fileInput.files[0];
      const ext = file.name.split('.').pop().toLowerCase();

      if (!ALLOWED_AVATAR_TYPES.includes(file.type) && !ALLOWED_AVATAR_EXTENSIONS.includes(ext)) {
        ToastUtils.showError('Avatar must be a JPEG, JPG, PNG, or GIF file.', 'Invalid File Type');
        fileInput.value = '';
        return;
      }

      if (file.size >= MAX_AVATAR_SIZE) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        ToastUtils.showError('Avatar file size must be less than 2MB. Selected file is ' + sizeMB + 'MB.', 'File Too Large');
        fileInput.value = '';
        return;
      }
    }

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
    const sexInput = document.getElementById('newClientSex');

    // Clear all previous invalid highlights
    [elements.nameInput, elements.ageInput, sexInput, elements.contactInput, elements.planInput, elements.startDateInput]
      .forEach(el => el && el.classList.remove('is-invalid'));

    const name = elements.nameInput.value.trim();
    const age = elements.ageInput.value;
    const sex = sexInput.value;
    const contact = elements.contactInput.value.trim();
    const plan = elements.planInput.value;
    const startDate = elements.startDateInput.value;
    const endDate = elements.endDateInput.value;

    // Helper: mark field invalid, focus it, and show toast
    function fail(message, el) {
      if (el) { el.classList.add('is-invalid'); el.focus(); }
      ToastUtils.showError(message, 'Validation Error');
    }

    // 1. Name
    const nameResult = FormUtils.validateRequired(name, 'a name', elements.nameInput);
    if (nameResult !== true) { fail(nameResult, elements.nameInput); return; }

    // 2. Age
    const ageResult = FormUtils.validateAge(age, elements.ageInput);
    if (ageResult !== true) { fail(ageResult, elements.ageInput); return; }

    // 3. Sex
    const sexResult = FormUtils.validateRequired(sex, 'a sex', sexInput);
    if (sexResult !== true) { fail(sexResult, sexInput); return; }

    // 4. Contact – required
    const contactRequiredResult = FormUtils.validateRequired(contact, 'a contact number', elements.contactInput);
    if (contactRequiredResult !== true) { fail(contactRequiredResult, elements.contactInput); return; }

    // 4b. Contact – valid characters
    const contactFormatResult = FormUtils.validateContact(contact, elements.contactInput);
    if (contactFormatResult !== true) { fail(contactFormatResult, elements.contactInput); return; }

    // 4c. Contact – digit count / prefix rules
    const digitsOnly = contact.replace(/\D/g, '');
    if (contact.startsWith('+63')) {
      if (digitsOnly.length !== 12) { fail('Phone number with +63 must have exactly 12 digits', elements.contactInput); return; }
    } else {
      if (digitsOnly.length !== 11) { fail('Phone number must have exactly 11 digits', elements.contactInput); return; }
      if (!digitsOnly.startsWith('09')) { fail('Phone number must start with 09', elements.contactInput); return; }
    }

    // 5. Membership plan
    const planResult = FormUtils.validateSelect(plan, 'a membership plan', elements.planInput);
    if (planResult !== true) { fail(planResult, elements.planInput); return; }

    // 6. Start date
    const startDateResult = FormUtils.validateRequired(startDate, 'a start date', elements.startDateInput);
    if (startDateResult !== true) { fail(startDateResult, elements.startDateInput); return; }

    if (!endDate) {
      fail(FormUtils.MESSAGES.endDateRequired, elements.startDateInput);
      return;
    }

    // Populate confirmation overlay
    const planSelect = elements.planInput;
    const planText = planSelect.options[planSelect.selectedIndex].text;
    document.getElementById('confirmClientNameText').textContent = name;
    document.getElementById('confirmClientPlanText').textContent = planText;
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
    const submitBtn = document.querySelector('#addClientConfirmOverlay .btn-update');
    const elements = getAddModalElements();

    try {
      // Validate start date is not in the past
      const startDate = new Date(elements.startDateInput.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      if (startDate < today) {
        ToastUtils.showError('Start date cannot be in the past. Please select today or a future date.', 'Invalid Date');
        return;
      }

      // Create FormData
      const formData = new FormData();
      formData.append('name', elements.nameInput.value.trim());
      formData.append('age', elements.ageInput.value);
      formData.append('sex', document.getElementById('newClientSex').value);
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
    const planSelect = document.getElementById('editClientPlanType' + clientId);
    const days = getPlanDurationFromSelect(planSelect);
    FormUtils.calculateEndDate(startDateInput, endDateInput, days);
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
    const planSelect = document.getElementById('editClientPlanType' + clientId);
    const planText = planSelect.options[planSelect.selectedIndex].text;
    document.getElementById('confirmEditClientName' + clientId).textContent = name;
    document.getElementById('confirmEditClientPlan' + clientId).textContent = planText;
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
      formData.append('sex', document.getElementById('editClientSex' + clientId).value);
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
   * @param {string} planName - Plan display name
   * @param {string} planKey - Plan key
   * @param {number} durationDays - Plan duration in days
   * @param {string} startDate - Current start date
   * @param {string} dueDate - Current due date
   */
  function openRenewClientModal(clientId, clientName, planName, planKey, durationDays, startDate, dueDate) {
    // Store renewal data
    document.getElementById('renewClientId').value = clientId;
    document.getElementById('renewClientName').value = clientName;
    document.getElementById('renewClientPlanType').value = planName;
    document.getElementById('renewClientPlanKey').value = planKey;
    document.getElementById('renewClientDurationDays').value = durationDays;
    
    // Display client info
    document.getElementById('renewClientNameDisplay').value = clientName;
    document.getElementById('renewClientPlanTypeDisplay').value = planName;
    
    // Set today's date as default start date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('renewClientStartDate').value = today;
    
    // Calculate end date
    calculateRenewClientEndDate();
    
    // Show modal
    $('#renewClientModal').modal('show');
  }

  /**
   * Calculate end date for renewal based on plan duration
   */
  function calculateRenewClientEndDate() {
    const startDateInput = document.getElementById('renewClientStartDate');
    const endDateInput = document.getElementById('renewClientEndDate');
    const durationDays = parseInt(document.getElementById('renewClientDurationDays').value) || 30;
    
    if (!startDateInput.value) return;
    
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
   * @param {string} planType - Subscription type
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
    
    FormUtils.setButtonLoading(submitBtn, 'Deleting...');
    
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
      FormUtils.resetButton(submitBtn);
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
        if (kpiCards[0]) kpiCards[0].textContent = formatKPINumber(data.total);
        if (kpiCards[1]) kpiCards[1].textContent = formatKPINumber(data.active);
        if (kpiCards[2]) kpiCards[2].textContent = formatKPINumber(data.expiring);
        if (kpiCards[3]) kpiCards[3].textContent = formatKPINumber(data.new_signups);
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

  // Track current filter state
  const filterState = {
    status: 'all',
    plan: 'all',
    gender: 'all'
  };

  /**
   * Initialize filters from URL parameters
   */
  function initializeFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    filterState.status = urlParams.get('status') || 'all';
    filterState.plan = urlParams.get('plan') || 'all';
    filterState.gender = urlParams.get('gender') || 'all';
  }

  /**
   * Apply filter to table by updating URL
   * @param {string} filterType - Filter type: 'status', 'plan', 'gender'
   * @param {string} value - Filter value
   */
  function applyFilter(filterType, value) {
    // Update filter state
    filterState[filterType] = value;
    
    // Build URL with all current filters
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Update or remove filter parameters
    if (value !== 'all') {
      params.set(filterType, value);
    } else {
      params.delete(filterType);
    }
    
    // Preserve search parameter if it exists
    const currentSearch = params.get('search');
    
    // Navigate to filtered URL
    window.location.href = `${url.pathname}?${params.toString()}`;
  }

  /**
   * Clear all filters
   */
  function clearAllFilters() {
    // Build URL without filter parameters
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Remove filter parameters but keep search
    params.delete('status');
    params.delete('plan');
    params.delete('gender');
    
    // Navigate to URL without filters
    window.location.href = `${url.pathname}?${params.toString()}`;
  }

  /**
   * Toggle filter section accordion
   * @param {HTMLElement} headerElement - The clicked filter section header
   * @param {Event} event - The click event
   */
  function toggleFilterSection(headerElement, event) {
    // Prevent the dropdown from closing
    if (event) {
      event.stopPropagation();
    }
    const section = headerElement.closest('.filter-section');
    section.classList.toggle('active');
  }

  /**
   * Legacy function for backward compatibility
   * @param {string} status - Filter status: 'all', 'active', 'expired', 'due_soon'
   */
  function applyStatusFilter(status) {
    applyFilter('status', status);
  }

  /**
   * Initialize the page
   * @param {Object} options - Configuration options
   * @param {string} options.csrfToken - CSRF token
   * @param {string} options.storeUrl - Store URL for AJAX submission
   */
  function init(options) {
    config = { ...config, ...options };

    // Initialize filters from URL parameters
    initializeFilters();

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

    // Initialize autocomplete for client name field
    const nameInput = document.getElementById('newClientName');
    if (nameInput && typeof AutocompleteUtils !== 'undefined') {
      AutocompleteUtils.init({
        inputElement: nameInput,
        apiUrl: '/api/clients/autocomplete',
        onSelect: (item) => {
          // Autofill fields from selected membership
          const elements = getAddModalElements();
          
          // Set basic info
          if (item.age) elements.ageInput.value = item.age;
          if (item.sex) {
            const sexSelect = document.getElementById('newClientSex');
            if (sexSelect) sexSelect.value = item.sex;
          }
          if (item.contact) elements.contactInput.value = item.contact;
              
          
          // Autofill avatar if available
          if (item.avatar) {
            // Switch to URL input mode
            toggleClientAvatarInput('url');
            elements.urlInput.value = item.avatar;
            state.avatarUrl = item.avatar;
            // Preview the avatar
            AvatarUtils.previewAvatar({
              fileInput: elements.fileInput,
              urlInput: elements.urlInput,
              preview: elements.preview
            }, state);
          }
          
          // Recalculate end date based on plan duration
          calculateClientEndDate();
        }
      });
    }

    // Reset modals on close — clear forms, validation, and confirm overlays
    $('.modal').on('hidden.bs.modal', function() {
      const $modal = $(this);
      // Reset all forms inside the modal
      $modal.find('form').each(function() { this.reset(); });
      // Remove validation states
      $modal.find('.is-invalid').removeClass('is-invalid');
      $modal.find('.invalid-feedback').remove();
      // Hide any confirm overlays
      $modal.find('[id$="ConfirmOverlay"], [id$="confirmOverlay"]').hide();
      // Re-enable any disabled submit buttons and restore original text
      $modal.find('button[type="submit"], .btn-primary, .btn-update, .btn-delete').each(function() {
        this.disabled = false;
        if (this.dataset.originalText) {
          this.innerHTML = this.dataset.originalText;
        }
      });
      // Reset avatar previews
      $modal.find('.avatar-preview').attr('src', '').hide();
      $modal.find('.avatar-input-container').show();
    });

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
    applyFilter,
    clearAllFilters,
    toggleFilterSection,
    setupMidnightRefresh
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ClientsPage;
}

// Make globally accessible for inline scripts
window.ClientsPage = ClientsPage;
