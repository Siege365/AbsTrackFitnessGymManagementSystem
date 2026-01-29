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

  // Configuration passed from Laravel (set via MembershipsPage.init())
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
    FormUtils.calculateEndDate(elements.startDateInput, elements.endDateInput, 30);
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
   * Validate form and show confirmation modal
   */
  function showConfirmModal() {
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

    // Populate overlay confirmation
    document.getElementById('confirmNameText').textContent = name;
    document.getElementById('confirmPlanText').textContent = plan;
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
    const submitBtn = document.querySelector('#addMemberConfirmOverlay .btn-primary');
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
    FormUtils.calculateEndDate(startDateInput, endDateInput, 30);
  }

  /**
   * Preview avatar file for edit modal
   * @param {number} membershipId - Membership ID
   */
  function previewAvatar(membershipId) {
    const input = document.getElementById('avatarInput' + membershipId);
    const preview = document.getElementById('avatarPreview' + membershipId);

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
    
    // Populate overlay
    document.getElementById('confirmEditName' + membershipId).textContent = name;
    document.getElementById('confirmEditPlan' + membershipId).textContent = planType;
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
    
    FormUtils.setButtonLoading(submitBtn, true, 'Updating...');
    
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
      FormUtils.setButtonLoading(submitBtn, false);
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
   * @param {string} planType - Plan type
   * @param {string} startDate - Current start date
   * @param {string} dueDate - Current due date
   */
  function openRenewModal(membershipId, memberName, planType, startDate, dueDate) {
    // Store renewal data
    document.getElementById('renewMembershipId').value = membershipId;
    document.getElementById('renewMembershipName').value = memberName;
    document.getElementById('renewPlanType').value = planType;
    
    // Display member info
    document.getElementById('renewMemberNameDisplay').value = memberName;
    document.getElementById('renewPlanTypeDisplay').value = planType;
    
    // Set today's date as default start date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('renewStartDate').value = today;
    
    // Calculate end date
    calculateRenewEndDate();
    
    // Show modal
    $('#renewMembershipModal').modal('show');
  }

  /**
   * Calculate end date for renewal based on plan type
   */
  function calculateRenewEndDate() {
    const startDateInput = document.getElementById('renewStartDate');
    const endDateInput = document.getElementById('renewEndDate');
    const planType = document.getElementById('renewPlanType').value;
    
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
    
    // Populate confirmation overlay
    document.getElementById('confirmRenewNameText').textContent = memberName;
    document.getElementById('confirmRenewPlanText').textContent = planType;
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
   * @param {string} planType - Plan type
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
      FormUtils.setButtonLoading(submitBtn, false);
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
      checkboxClass: 'membership-checkbox',
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
    setupMidnightRefresh
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = MembershipsPage;
}
