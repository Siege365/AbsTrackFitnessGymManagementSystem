/**
 * Memberships Page Module
 * Page-specific JavaScript for memberships/index.blade.php
 * Uses: AvatarUtils, FormUtils, BulkSelection
 */

const MembershipsPage = (function() {
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

  // Configuration passed from Laravel (set via MembershipsPage.init())
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
      fileInput: document.getElementById('newMemberAvatar'),
      urlInput: document.getElementById('newMemberAvatarUrl'),
      preview: document.getElementById('newAvatarPreview'),
      nameInput: document.getElementById('newMemberName'),
      ageInput: document.getElementById('newMemberAge'),
      contactInput: document.getElementById('newMemberContact'),
      planInput: document.getElementById('newMemberPlan'),
      startDateInput: document.getElementById('newMemberStartDate'),
      endDateInput: document.getElementById('newMemberEndDate')
    };
  }

  /**
   * Toggle avatar input between file and URL
   * @param {string} type - 'file' or 'url'
   */
  function toggleAvatarInput(type) {
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
  function calculateEndDate() {
    const elements = getAddModalElements();
    const days = getPlanDurationFromSelect(elements.planInput);
    FormUtils.calculateEndDate(elements.startDateInput, elements.endDateInput, days);
  }

  /**
   * Preview avatar from file or URL input
   */
  function previewNewAvatar() {
    const elements = getAddModalElements();
    AvatarUtils.previewAvatar({
      fileInput: elements.fileInput,
      urlInput: elements.urlInput,
      preview: elements.preview
    }, state);
  }

  /**
   * Validate avatar file size and type
   * @param {HTMLInputElement} fileInput - The file input element
   * @returns {string|null} Error message or null if valid
   */
  function validateAvatarFile(fileInput) {
    if (!fileInput || !fileInput.files || !fileInput.files[0]) return null;
    const file = fileInput.files[0];

    // Check file type
    const ext = file.name.split('.').pop().toLowerCase();
    if (!ALLOWED_AVATAR_TYPES.includes(file.type) && !ALLOWED_AVATAR_EXTENSIONS.includes(ext)) {
      return 'Avatar must be a JPEG, JPG, PNG, or GIF file.';
    }

    // Check file size (must be strictly less than 2MB)
    if (file.size >= MAX_AVATAR_SIZE) {
      const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
      return 'Avatar file size must be less than 2MB. Selected file is ' + sizeMB + 'MB.';
    }

    return null;
  }

  /**
   * Validate form and show confirmation modal
   */
  function showConfirmModal() {
    const elements = getAddModalElements();
    const sexInput = document.getElementById('newMemberSex');

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

    // Validate avatar file if one is selected
    const avatarError = validateAvatarFile(elements.fileInput);
    if (avatarError) {
      ToastUtils.showError(avatarError, 'Validation Error');
      elements.fileInput.focus();
      return;
    }

    // Populate overlay confirmation
    const planSelect = elements.planInput;
    const planText = planSelect.options[planSelect.selectedIndex].text;
    document.getElementById('confirmNameText').textContent = name;
    document.getElementById('confirmPlanText').textContent = planText;
    document.getElementById('confirmDurationText').textContent = startDate + ' to ' + endDate;

    // Show confirmation overlay
    document.getElementById('addMemberConfirmOverlay').style.display = 'flex';
  }

  /**
   * Go back to add form from confirmation
   */
  function backToAddForm() {
    document.getElementById('addMemberConfirmOverlay').style.display = 'none';
  }

  /**
   * Submit the member form via AJAX
   * @param {boolean} confirmSimilar - Whether to confirm similar name submission
   */
  function submitMemberForm(confirmSimilar = false) {
    const submitBtn = document.querySelector('#addMemberConfirmOverlay .btn-update');
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
      formData.append('sex', document.getElementById('newMemberSex').value);
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
          document.getElementById('addMemberConfirmOverlay').style.display = 'none';
          $('#addMemberModal').modal('hide');
          ToastUtils.showSuccess(data.message || 'Member added successfully!', 'Success');
          setTimeout(() => location.reload(), 1000);
        } else if (status === 409 && data.requires_confirmation) {
          // Similar name found - show confirmation
          document.getElementById('addMemberConfirmOverlay').style.display = 'none';
          
          if (confirm(`A member with a similar name "${data.existing}" already exists.\n\nDo you want to proceed anyway?`)) {
            // User confirmed, resubmit with confirm flag
            submitMemberForm(true);
          }
        } else if (status === 400 && data.type === 'exact') {
          // Exact duplicate - block submission
          ToastUtils.showError(data.message || 'A member with this exact name already exists.', 'Duplicate Entry');
        } else {
          // Other errors (validation, etc.)
          ToastUtils.showError(data.message || 'Failed to add member', 'Error');
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
   * @param {number} membershipId - Membership ID
   * @param {string} type - 'file' or 'url'
   */
  function toggleEditAvatarInput(membershipId, type) {
    const fileInput = document.getElementById('avatarInput' + membershipId);
    const urlInput = document.getElementById('avatarUrl' + membershipId);

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
   * @param {number} membershipId - Membership ID
   */
  function calculateEditEndDate(membershipId) {
    const startDateInput = document.getElementById('editStartDate' + membershipId);
    const endDateInput = document.getElementById('editEndDate' + membershipId);
    const planSelect = document.getElementById('editPlanType' + membershipId);
    const days = getPlanDurationFromSelect(planSelect);
    FormUtils.calculateEndDate(startDateInput, endDateInput, days);
  }

  /**
   * Preview avatar file for edit modal
   * @param {number} membershipId - Membership ID
   */
  function previewAvatar(membershipId) {
    const input = document.getElementById('avatarInput' + membershipId);
    const preview = document.getElementById('avatarPreview' + membershipId);

    if (input.files && input.files[0]) {
      const file = input.files[0];

      // Validate file type
      const ext = file.name.split('.').pop().toLowerCase();
      if (!ALLOWED_AVATAR_TYPES.includes(file.type) && !ALLOWED_AVATAR_EXTENSIONS.includes(ext)) {
        preview.innerHTML = AvatarUtils.createMessage('Avatar must be a JPEG, JPG, PNG, or GIF file.', 'error');
        input.value = '';
        return;
      }

      // Validate file size (must be strictly less than 2MB)
      if (file.size >= MAX_AVATAR_SIZE) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        preview.innerHTML = AvatarUtils.createMessage('Avatar file size must be less than 2MB. Selected file is ' + sizeMB + 'MB.', 'error');
        input.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        preview.innerHTML = AvatarUtils.createPreviewImage(e.target.result);
      };
      reader.readAsDataURL(file);
    }
  }

  /**
   * Preview avatar URL for edit modal
   * @param {number} membershipId - Membership ID
   */
  function previewAvatarUrl(membershipId) {
    const urlInput = document.getElementById('avatarUrl' + membershipId);
    const preview = document.getElementById('avatarPreview' + membershipId);

    if (urlInput.value) {
      AvatarUtils.validateAndPreviewUrl(urlInput.value, preview, null);
    }
  }

  /**
   * Show edit confirmation overlay
   * @param {number} membershipId - Membership ID
   */
  function showEditConfirmModal(membershipId) {
    const name = document.getElementById('editName' + membershipId).value.trim();
    const planType = document.getElementById('editPlanType' + membershipId).value;
    const startDate = document.getElementById('editStartDate' + membershipId).value;
    const endDate = document.getElementById('editEndDate' + membershipId).value;
    
    // Basic validation
    if (!name) {
      ToastUtils.showError('Please enter a name.', 'Validation Error');
      return;
    }

    // Validate avatar file if one is selected
    const avatarFileInput = document.getElementById('avatarInput' + membershipId);
    const avatarError = validateAvatarFile(avatarFileInput);
    if (avatarError) {
      ToastUtils.showError(avatarError, 'Validation Error');
      avatarFileInput.focus();
      return;
    }
    
    // Populate overlay
    const planSelect = document.getElementById('editPlanType' + membershipId);
    const planText = planSelect.options[planSelect.selectedIndex].text;
    document.getElementById('confirmEditName' + membershipId).textContent = name;
    document.getElementById('confirmEditPlan' + membershipId).textContent = planText;
    document.getElementById('confirmEditDuration' + membershipId).textContent = startDate + ' to ' + endDate;
    
    // Show overlay
    document.getElementById('editConfirmOverlay' + membershipId).style.display = 'flex';
  }

  /**
   * Go back to edit form from confirmation overlay
   * @param {number} membershipId - Membership ID
   */
  function backToEditForm(membershipId) {
    document.getElementById('editConfirmOverlay' + membershipId).style.display = 'none';
  }

  /**
   * Submit edit form via AJAX
   * @param {number} membershipId - Membership ID
   */
  function submitEditForm(membershipId) {
    const form = document.getElementById('editMemberForm' + membershipId);
    const submitBtn = event.target;
    const actionUrl = form.dataset.action;
    
    const formData = new FormData(form);
    
    FormUtils.setButtonLoading(submitBtn, 'Updating...');
    
    fetch(actionUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return response.json();
      }
      return { success: true };
    })
    .then(data => {
      document.getElementById('editConfirmOverlay' + membershipId).style.display = 'none';
      $('#viewModal' + membershipId).modal('hide');
      
      ToastUtils.showSuccess('Member updated successfully!', 'Success');
      updateKPIs();
      
      setTimeout(() => location.reload(), 1000);
    })
    .catch(error => {
      FormUtils.resetButton(submitBtn);
      ToastUtils.showError('Failed to update member: ' + (error.message || 'Unknown error'), 'Error');
    });
  }

  /**
   * Bulk delete selected memberships
   */
  function bulkDelete() {
    BulkSelection.bulkDelete({
      checkboxClass: 'membership-checkbox',
      formId: 'bulkDeleteForm',
      inputName: 'membership_ids[]',
      itemType: 'membership'
    });
  }

  /**
   * Open renew subscription modal
   * @param {number} membershipId - Membership ID
   * @param {string} memberName - Member name
   * @param {string} planName - Plan display name
   * @param {string} planKey - Plan key
   * @param {number} durationDays - Plan duration in days
   * @param {string} startDate - Current start date
   * @param {string} dueDate - Current due date
   */
  function openRenewModal(membershipId, memberName, planName, planKey, durationDays, startDate, dueDate) {
    // Store renewal data
    document.getElementById('renewMembershipId').value = membershipId;
    document.getElementById('renewMembershipName').value = memberName;
    document.getElementById('renewPlanType').value = planName;
    document.getElementById('renewPlanKey').value = planKey;
    document.getElementById('renewDurationDays').value = durationDays;
    
    // Display member info
    document.getElementById('renewMemberNameDisplay').value = memberName;
    document.getElementById('renewPlanTypeDisplay').value = planName;
    
    // Set today's date as default start date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('renewStartDate').value = today;
    
    // Calculate end date
    calculateRenewEndDate();
    
    // Show modal
    $('#renewMembershipModal').modal('show');
  }

  /**
   * Calculate end date for renewal based on plan duration
   */
  function calculateRenewEndDate() {
    const startDateInput = document.getElementById('renewStartDate');
    const endDateInput = document.getElementById('renewEndDate');
    const durationDays = parseInt(document.getElementById('renewDurationDays').value) || 30;
    
    if (!startDateInput.value) return;
    
    FormUtils.calculateEndDate(startDateInput, endDateInput, durationDays);
  }

  /**
   * Show renewal confirmation overlay
   */
  function showRenewConfirmModal() {
    const startDate = document.getElementById('renewStartDate').value;
    const endDate = document.getElementById('renewEndDate').value;
    const memberName = document.getElementById('renewMembershipName').value;
    const planType = document.getElementById('renewPlanType').value;
    
    // Validate
    if (!startDate) {
      ToastUtils.showError('Please select a start date.', 'Validation Error');
      document.getElementById('renewStartDate').focus();
      return;
    }
    
    if (!endDate) {
      ToastUtils.showError('End date is required. Please select a start date to auto-calculate.', 'Validation Error');
      document.getElementById('renewStartDate').focus();
      return;
    }

    // Validate: due date must be strictly after start date
    if (new Date(endDate) <= new Date(startDate)) {
      ToastUtils.showError('Due date must be after the start date.', 'Validation Error');
      document.getElementById('renewStartDate').focus();
      return;
    }

    // Validate: start date cannot be more than 30 days in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const start = new Date(startDate);
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    if (start < thirtyDaysAgo) {
      ToastUtils.showError('Start date cannot be more than 30 days in the past.', 'Validation Error');
      document.getElementById('renewStartDate').focus();
      return;
    }
    
    // Populate confirmation overlay (renewPlanType is a hidden input, not a select)
    const planText = document.getElementById('renewPlanType').value;
    document.getElementById('confirmRenewNameText').textContent = memberName;
    document.getElementById('confirmRenewPlanText').textContent = planText;
    document.getElementById('confirmRenewDurationText').textContent = startDate + ' to ' + endDate;
    
    // Show overlay
    document.getElementById('renewConfirmOverlay').style.display = 'flex';
  }

  /**
   * Go back to renew form from confirmation overlay
   */
  function backToRenewForm() {
    document.getElementById('renewConfirmOverlay').style.display = 'none';
  }

  /**
   * Submit renewal form via AJAX
   */
  function submitRenewForm() {
    const submitBtn = event.target;
    const membershipId = document.getElementById('renewMembershipId').value;
    const startDate = document.getElementById('renewStartDate').value;
    const endDate = document.getElementById('renewEndDate').value;
    
    const formData = new FormData();
    formData.append('start_date', startDate);
    formData.append('due_date', endDate);
    
    FormUtils.submitFormAjax({
      url: `/memberships/${membershipId}/renew`,
      formData: formData,
      csrfToken: config.csrfToken,
      submitBtn: submitBtn,
      onSuccess: function(data) {
        document.getElementById('renewConfirmOverlay').style.display = 'none';
        $('#renewMembershipModal').modal('hide');
        
        // Show success toast
        ToastUtils.showSuccess('Membership renewed successfully!', 'Success');
        updateKPIs();
        
        // Reload after toast animation
        setTimeout(() => location.reload(), 1000);
      },
      onError: function(error) {
        ToastUtils.showError('Failed to renew membership: ' + (error.message || 'Unknown error'), 'Error');
      }
    });
  }

  /**
   * Open delete confirmation modal
   * @param {number} membershipId - Membership ID
   * @param {string} memberName - Member name
   * @param {string} planType - Subscription type
   * @param {string} status - Current status
   */
  function openDeleteModal(membershipId, memberName, planType, status) {
    // Store membership ID for delete action
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/memberships/${membershipId}`;
    deleteForm.dataset.membershipId = membershipId;
    
    // Populate modal
    document.getElementById('deleteMemberName').textContent = memberName;
    document.getElementById('deleteMemberPlan').textContent = planType;
    document.getElementById('deleteMemberStatus').textContent = status;
    
    // Show modal
    $('#deleteConfirmModal').modal('show');
  }

  /**
   * Confirm and execute delete
   */
  function confirmDelete() {
    const deleteForm = document.getElementById('deleteForm');
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
      // If not JSON but OK status, assume success (redirect response)
      if (response.ok || response.redirected) {
        return { success: true };
      }
      throw new Error('Server error');
    })
    .then(data => {
      if (data.success) {
        $('#deleteConfirmModal').modal('hide');
        ToastUtils.showSuccess('Member deleted successfully!', 'Success');
        updateKPIs();
        
        // Reload after toast animation
        setTimeout(() => location.reload(), 1000);
      } else {
        throw new Error(data.message || 'Delete failed');
      }
    })
    .catch(error => {
      FormUtils.resetButton(submitBtn);
      ToastUtils.showError('Failed to delete member: ' + error.message, 'Error');
    });
  }

  /**
   * Update KPI cards with real-time data from server
   * Fetches fresh data from backend to ensure accuracy
   */
  function updateKPIs() {
    fetch('/memberships/kpis', {
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
      checkboxClass: 'membership-checkbox',
      selectedCountId: 'selectedCount'
    });

    // Setup search enter key
    FormUtils.setupSearchEnter(
      document.getElementById('searchInput'),
      document.getElementById('searchForm')
    );

    // Initialize autocomplete for name field (fetches from Clients table)
    const nameInput = document.getElementById('newMemberName');
    if (nameInput && typeof AutocompleteUtils !== 'undefined') {
      AutocompleteUtils.init({
        inputElement: nameInput,
        apiUrl: '/api/memberships/autocomplete',
        onSelect: (item) => {
          // Autofill fields from selected client
          const elements = getAddModalElements();
          if (item.age) elements.ageInput.value = item.age;
          if (item.sex) {
            const sexSelect = document.getElementById('newMemberSex');
            if (sexSelect) sexSelect.value = item.sex;
          }
          if (item.contact) elements.contactInput.value = item.contact;
          if (item.plan_type) {
            elements.planInput.value = item.plan_type;
            calculateEndDate(); // Recalculate end date based on plan
          }
          
          // Autofill avatar if available
          if (item.avatar) {
            // Switch to URL input mode
            toggleAvatarInput('url');
            elements.urlInput.value = item.avatar;
            state.avatarUrl = item.avatar;
            // Preview the avatar
            AvatarUtils.previewAvatar({
              fileInput: elements.fileInput,
              urlInput: elements.urlInput,
              preview: elements.preview
            }, state);
          }
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
    toggleAvatarInput,
    calculateEndDate,
    previewNewAvatar,
    showConfirmModal,
    backToAddForm,
    submitMemberForm,
    toggleEditAvatarInput,
    calculateEditEndDate,
    previewAvatar,
    previewAvatarUrl,
    showEditConfirmModal,
    backToEditForm,
    submitEditForm,
    bulkDelete,
    openRenewModal,
    calculateRenewEndDate,
    showRenewConfirmModal,
    backToRenewForm,
    submitRenewForm,
    openDeleteModal,
    confirmDelete,
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
  module.exports = MembershipsPage;
}

// Make globally accessible for inline scripts
window.MembershipsPage = MembershipsPage;
