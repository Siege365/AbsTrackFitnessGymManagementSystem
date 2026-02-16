/**
 * Bulk Selection Module
 * Reusable table selection and bulk operations
 * Used by: memberships, clients pages
 */

const BulkSelection = (function() {
  'use strict';

  /**
   * Initialize bulk selection for a table
   * @param {Object} config - Configuration options
   * @param {string} config.selectAllId - ID of select all checkbox
   * @param {string} config.checkboxClass - Class of row checkboxes
   * @param {string} config.selectedCountId - ID of selected count display element
   * @returns {Object} Bulk selection controller
   */
  function init(config) {
    const { selectAllId, checkboxClass, selectedCountId } = config;

    const selectAllCheckbox = document.getElementById(selectAllId);
    const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
    const selectedCountSpan = selectedCountId ? document.getElementById(selectedCountId) : null;

    /**
     * Update the selected count display
     */
    function updateSelectedCount() {
      const count = document.querySelectorAll(`.${checkboxClass}:checked`).length;
      if (selectedCountSpan) {
        selectedCountSpan.textContent = count;
      }
      return count;
    }

    /**
     * Get array of selected values
     * @returns {Array} Array of selected checkbox values
     */
    function getSelectedValues() {
      return Array.from(document.querySelectorAll(`.${checkboxClass}:checked`)).map(cb => cb.value);
    }

    /**
     * Get count of selected items
     * @returns {number} Number of selected items
     */
    function getSelectedCount() {
      return document.querySelectorAll(`.${checkboxClass}:checked`).length;
    }

    /**
     * Select or deselect all checkboxes
     * @param {boolean} checked - Whether to check or uncheck
     */
    function selectAll(checked) {
      checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
      });
      updateSelectedCount();
    }

    /**
     * Update select all checkbox state based on individual checkboxes
     */
    function updateSelectAllState() {
      if (!selectAllCheckbox) return;

      const allChecked = Array.from(checkboxes).every(cb => cb.checked);
      const someChecked = Array.from(checkboxes).some(cb => cb.checked);

      selectAllCheckbox.checked = allChecked;
      selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }

    // Setup event listeners
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        selectAll(this.checked);
      });
    }

    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateSelectedCount();
        updateSelectAllState();
      });
    });

    // Return controller object
    return {
      updateSelectedCount,
      getSelectedValues,
      getSelectedCount,
      selectAll,
      updateSelectAllState
    };
  }

  /**
   * Execute bulk delete operation
   * @param {Object} options - Configuration options
   * @param {string} options.checkboxClass - Class of row checkboxes
   * @param {string} options.formId - ID of the bulk delete form
   * @param {string} options.inputName - Name for hidden inputs (e.g., 'membership_ids[]')
   * @param {string} options.itemType - Item type for confirmation message (e.g., 'membership', 'client')
   * @returns {boolean} Whether delete was initiated
   */
  function bulkDelete(options) {
    const { checkboxClass, formId, inputName, itemType } = options;

    const checkedBoxes = document.querySelectorAll(`.${checkboxClass}:checked`);

    if (checkedBoxes.length === 0) {
      alert(`Please select at least one ${itemType} to delete.`);
      return false;
    }

    const count = checkedBoxes.length;
    const confirmation = confirm(
      `Are you sure you want to delete ${count} ${itemType}(s)? This action cannot be undone.`
    );

    if (confirmation) {
      const form = document.getElementById(formId);

      // Remove any existing hidden inputs
      form.querySelectorAll(`input[name="${inputName}"]`).forEach(el => el.remove());

      // Add selected IDs to form
      checkedBoxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = checkbox.value;
        form.appendChild(input);
      });

      form.submit();
      return true;
    }

    return false;
  }

  // Public API
  return {
    init,
    bulkDelete
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = BulkSelection;
}

// Make globally accessible for inline scripts
window.BulkSelection = BulkSelection;
