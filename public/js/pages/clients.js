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
   * Validate form and show confirmation modal
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
      alert(result);
      return;
    }

    if (!endDate) {
      alert(FormUtils.MESSAGES.endDateRequired);
      elements.startDateInput.focus();
      return;
    }

    // Populate confirmation modal
    document.getElementById('confirmClientNameText').textContent = name;
    document.getElementById('confirmClientAge').textContent = age;
    document.getElementById('confirmClientContact').textContent = contact;
    document.getElementById('confirmClientPlan').textContent = plan;
    document.getElementById('confirmClientStartDate').textContent = startDate;
    document.getElementById('confirmClientEndDate').textContent = endDate;

    // Set avatar preview
    AvatarUtils.setConfirmationAvatar({
      fileInput: elements.fileInput,
      urlInput: elements.urlInput,
      avatarLarge: document.getElementById('confirmClientAvatarLarge'),
      avatarSmall: document.getElementById('confirmClientAvatarSmall'),
      noAvatarText: document.getElementById('noClientAvatarText')
    }, state);

    // Transition modals
    FormUtils.transitionModals('addClientModal', 'confirmClientModal');
  }

  /**
   * Go back to add form from confirmation
   */
  function backToClientAddForm() {
    FormUtils.transitionModals('confirmClientModal', 'addClientModal');
  }

  /**
   * Submit the client form via AJAX
   */
  function submitClientForm() {
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
          $('#confirmClientModal').modal('hide');
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
    bulkDelete
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ClientsPage;
}
