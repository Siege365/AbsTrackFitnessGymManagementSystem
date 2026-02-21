/**
 * Form Utilities Module
 * Reusable form handling, validation, and AJAX submission
 * Used by: memberships, clients, payments pages
 */

const FormUtils = (function() {
  'use strict';

  // Validation patterns
  const PATTERNS = {
    contact: /^[+]?[0-9() -]+$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    name: /^[a-zA-Z\s'-]+$/
  };

  // Validation messages
  const MESSAGES = {
    required: (field) => `Please enter ${field}`,
    invalidAge: 'Please enter a valid age (1-120)',
    invalidContact: 'Contact number can only contain numbers, +, (), -, and spaces',
    invalidEmail: 'Please enter a valid email address',
    selectRequired: (field) => `Please select ${field}`,
    endDateRequired: 'End date is required. Please select a start date to auto-calculate.',
    networkError: 'An error occurred while submitting the form. Please check your internet connection and try again.',
    unexpectedError: 'An unexpected error occurred. Please try again.'
  };

  /**
   * Calculate end date (add days to start date)
   * @param {HTMLElement} startDateInput - Start date input element
   * @param {HTMLElement} endDateInput - End date input element
   * @param {number} days - Number of days to add (default: 30)
   */
  function calculateEndDate(startDateInput, endDateInput, days = 30) {
    if (startDateInput.value) {
      const startDate = new Date(startDateInput.value);
      const endDate = new Date(startDate);
      endDate.setDate(endDate.getDate() + days);

      // Format date as YYYY-MM-DD
      const year = endDate.getFullYear();
      const month = String(endDate.getMonth() + 1).padStart(2, '0');
      const day = String(endDate.getDate()).padStart(2, '0');
      endDateInput.value = `${year}-${month}-${day}`;
    }
  }

  /**
   * Format date as YYYY-MM-DD
   * @param {Date} date - Date object
   * @returns {string} Formatted date string
   */
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  /**
   * Validate required field
   * @param {string} value - Field value
   * @param {string} fieldName - Field display name
   * @param {HTMLElement} element - Element to focus on error
   * @returns {boolean|string} True if valid, error message if invalid
   */
  function validateRequired(value, fieldName, element) {
    if (!value || !value.trim()) {
      if (element) element.focus();
      return MESSAGES.required(fieldName);
    }
    return true;
  }

  /**
   * Validate age field
   * @param {number|string} value - Age value
   * @param {HTMLElement} element - Element to focus on error
   * @returns {boolean|string} True if valid, error message if invalid
   */
  function validateAge(value, element) {
    const age = parseInt(value);
    if (!value || isNaN(age) || age < 1 || age > 120) {
      if (element) element.focus();
      return MESSAGES.invalidAge;
    }
    return true;
  }

  /**
   * Validate contact field
   * @param {string} value - Contact value
   * @param {HTMLElement} element - Element to focus on error
   * @returns {boolean|string} True if valid, error message if invalid
   */
  function validateContact(value, element) {
    if (!PATTERNS.contact.test(value)) {
      if (element) element.focus();
      return MESSAGES.invalidContact;
    }
    return true;
  }

  /**
   * Validate select field
   * @param {string} value - Selected value
   * @param {string} fieldName - Field display name
   * @param {HTMLElement} element - Element to focus on error
   * @returns {boolean|string} True if valid, error message if invalid
   */
  function validateSelect(value, fieldName, element) {
    if (!value) {
      if (element) element.focus();
      return MESSAGES.selectRequired(fieldName);
    }
    return true;
  }

  /**
   * Run multiple validations
   * @param {Array} validations - Array of validation results
   * @returns {boolean|string} True if all valid, first error message if any invalid
   */
  function runValidations(validations) {
    for (const result of validations) {
      if (result !== true) {
        return result;
      }
    }
    return true;
  }

  /**
   * Set button to loading state
   * @param {HTMLElement} button - Button element
   * @param {string} loadingText - Loading text to display
   */
  function setButtonLoading(button, loadingText = 'Processing...') {
    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.innerHTML = `<i class="mdi mdi-loading mdi-spin"></i> ${loadingText}`;
  }

  /**
   * Reset button from loading state
   * @param {HTMLElement} button - Button element
   */
  function resetButton(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || 'Submit';
  }

  /**
   * Submit form via AJAX
   * @param {Object} options - Configuration options
   * @param {string} options.url - Request URL
   * @param {FormData} options.formData - Form data
   * @param {string} options.csrfToken - CSRF token
   * @param {HTMLElement} options.submitBtn - Submit button element
   * @param {Function} options.onSuccess - Success callback
   * @param {Function} options.onError - Error callback
   */
  function submitFormAjax(options) {
    const { url, formData, csrfToken, submitBtn, onSuccess, onError } = options;

    // Add CSRF token
    formData.append('_token', csrfToken);

    // Set button loading state
    if (submitBtn) {
      setButtonLoading(submitBtn);
    }

    fetch(url, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        if (onSuccess) {
          onSuccess(data);
        } else {
          location.reload();
        }
      } else {
        if (submitBtn) resetButton(submitBtn);

        let errorMessage = data.message || 'Something went wrong';
        if (data.errors) {
          errorMessage += '\n' + Object.values(data.errors).flat().join('\n');
        }

        if (onError) {
          onError(errorMessage, data);
        } else {
          alert('Error: ' + errorMessage);
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      if (submitBtn) resetButton(submitBtn);

      if (onError) {
        onError(MESSAGES.networkError, error);
      } else {
        alert(MESSAGES.networkError);
      }
    });
  }

  /**
   * Setup search input with enter key submission
   * @param {HTMLElement} searchInput - Search input element
   * @param {HTMLElement} searchForm - Search form element
   */
  function setupSearchEnter(searchInput, searchForm) {
    if (searchInput && searchForm) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchForm.submit();
        }
      });
    }
  }

  /**
   * Modal transition helper
   * @param {string} hideModalId - Modal to hide
   * @param {string} showModalId - Modal to show
   */
  function transitionModals(hideModalId, showModalId) {
    $(`#${hideModalId}`).modal('hide');
    $(`#${showModalId}`).modal('show');
  }

  /**
   * Initialize modal accessibility fixes
   * Fixes aria-hidden attribute to prevent accessibility warnings
   * Should be called once on page load
   */
  function initModalAccessibility() {
    // Fix aria-hidden on modal show
    $(document).on('show.bs.modal', '.modal', function() {
      $(this).removeAttr('aria-hidden');
    });

    // Set aria-hidden on modal hide
    $(document).on('hidden.bs.modal', '.modal', function() {
      $(this).attr('aria-hidden', 'true');
    });
  }

  // Initialize on document ready
  $(document).ready(function() {
    initModalAccessibility();
  });

  // Public API
  return {
    PATTERNS,
    MESSAGES,
    calculateEndDate,
    formatDate,
    validateRequired,
    validateAge,
    validateContact,
    validateSelect,
    runValidations,
    setButtonLoading,
    resetButton,
    submitFormAjax,
    setupSearchEnter,
    transitionModals,
    initModalAccessibility
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FormUtils;
}

// Make globally accessible for inline scripts
window.FormUtils = FormUtils;
