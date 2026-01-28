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
      alert(result);
      return;
    }

    if (!endDate) {
      alert(FormUtils.MESSAGES.endDateRequired);
      elements.startDateInput.focus();
      return;
    }

    // Populate confirmation modal
    document.getElementById('confirmNameText').textContent = name;
    document.getElementById('confirmAge').textContent = age;
    document.getElementById('confirmContact').textContent = contact;
    document.getElementById('confirmPlan').textContent = plan;
    document.getElementById('confirmStartDate').textContent = startDate;
    document.getElementById('confirmEndDate').textContent = endDate;

    // Set avatar preview
    AvatarUtils.setConfirmationAvatar({
      fileInput: elements.fileInput,
      urlInput: elements.urlInput,
      avatarLarge: document.getElementById('confirmAvatarLarge'),
      avatarSmall: document.getElementById('confirmAvatarSmall'),
      noAvatarText: document.getElementById('noAvatarText')
    }, state);

    // Transition modals
    FormUtils.transitionModals('addMemberModal', 'confirmMemberModal');
  }

  /**
   * Go back to add form from confirmation
   */
  function backToAddForm() {
    FormUtils.transitionModals('confirmMemberModal', 'addMemberModal');
  }

  /**
   * Submit the member form via AJAX
   */
  function submitMemberForm() {
    const submitBtn = event.target;
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

      // Append avatar
      AvatarUtils.appendAvatarToFormData(formData, {
        fileInput: elements.fileInput,
        urlInput: elements.urlInput
      }, state);

      // Submit via AJAX
      FormUtils.submitFormAjax({
        url: config.storeUrl,
        formData: formData,
        csrfToken: config.csrfToken,
        submitBtn: submitBtn,
        onSuccess: function() {
          $('#confirmMemberModal').modal('hide');
          location.reload();
        }
      });
    } catch (error) {
      console.error('Error:', error);
      FormUtils.resetButton(submitBtn);
      alert(FormUtils.MESSAGES.unexpectedError);
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
    bulkDelete
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = MembershipsPage;
}
