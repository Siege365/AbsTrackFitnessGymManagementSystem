/**
 * Toast Notification Utility
 * Reusable toast notification system for success, error, warning, and info messages
 * Uses Bootstrap Toast component with auto-dismiss and animations
 */

const ToastUtils = (function() {
  'use strict';

  // Toast container ID
  const TOAST_CONTAINER_ID = 'toastContainer';
  
  // Auto-dismiss timeout (milliseconds)
  const AUTO_DISMISS_DELAY = 5000; // 5 seconds
  
  // Maximum number of toasts to display at once
  const MAX_TOASTS = 3;
  
  // Toast counter for unique IDs
  let toastCounter = 0;

  /**
   * Ensure toast container exists in the DOM
   */
  function ensureToastContainer() {
    let container = document.getElementById(TOAST_CONTAINER_ID);
    
    if (!container) {
      container = document.createElement('div');
      container.id = TOAST_CONTAINER_ID;
      container.className = 'toast-container';
      container.style.cssText = `
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 999999;
        min-width: 300px;
        max-width: 400px;
        pointer-events: none;
      `;
      document.body.appendChild(container);
    }
    
    return container;
  }

  /**
   * Create and show a toast notification
   * @param {Object} options - Toast configuration
   * @param {string} options.type - Toast type: 'success', 'error', 'warning', 'info'
   * @param {string} options.title - Toast title
   * @param {string} options.message - Toast message
   * @param {number} options.delay - Auto-dismiss delay in milliseconds (default: 5000)
   * @param {boolean} options.autohide - Whether to auto-hide (default: true)
   */
  function createToast(options) {
    const {
      type = 'info',
      title = '',
      message = '',
      delay = AUTO_DISMISS_DELAY,
      autohide = true
    } = options;

    // Get or create container
    const container = ensureToastContainer();
    
    // Enforce max toast limit - remove oldest if at capacity
    const existingToasts = container.querySelectorAll('.toast');
    if (existingToasts.length >= MAX_TOASTS) {
      const oldestToast = existingToasts[0];
      $(oldestToast).toast('hide');
      oldestToast.remove();
    }
    
    // Generate unique ID
    const toastId = `toast-${++toastCounter}`;
    
    // Determine icon and colors based on type
    const typeConfig = getTypeConfig(type);
    
    // Convert newlines to <br> tags for HTML display
    const formattedMessage = message.replace(/\n/g, '<br>');
    
    // Create toast HTML - simple filled background design
    const toastHtml = `
      <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="${delay}" data-autohide="${autohide}">
        <div class="toast-content">
          <i class="${typeConfig.icon} toast-icon"></i>
          <span class="toast-message">${formattedMessage}</span>
          <button type="button" class="toast-close" data-dismiss="toast" aria-label="Close">&times;</button>
        </div>
      </div>
    `;
    
    // Insert toast into container
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    // Get the toast element
    const toastElement = document.getElementById(toastId);
    
    // Enable pointer events on individual toast
    toastElement.style.pointerEvents = 'auto';
    
    // Initialize Bootstrap toast
    $(toastElement).toast({
      autohide: autohide,
      delay: delay
    });
    
    // Show the toast
    $(toastElement).toast('show');
    
    // Remove toast from DOM after it's hidden
    $(toastElement).on('hidden.bs.toast', function() {
      toastElement.remove();
    });
    
    return toastElement;
  }

  /**
   * Get configuration for toast type
   * @param {string} type - Toast type
   * @returns {Object} Configuration object
   */
  function getTypeConfig(type) {
    const configs = {
      success: {
        icon: 'mdi mdi-check-circle',
        defaultTitle: 'Success',
        bg: '#28a745'
      },
      error: {
        icon: 'mdi mdi-alert-circle',
        defaultTitle: 'Error',
        bg: '#dc3545'
      },
      warning: {
        icon: 'mdi mdi-alert',
        defaultTitle: 'Warning',
        bg: '#e6a117'
      },
      info: {
        icon: 'mdi mdi-information',
        defaultTitle: 'Info',
        bg: '#17a2b8'
      }
    };
    
    return configs[type] || configs.info;
  }

  /**
   * Show a success toast
   * @param {string} message - Success message
   * @param {string} title - Optional custom title
   * @param {Object} options - Additional options
   */
  function showSuccess(message, title, options = {}) {
    return createToast({
      type: 'success',
      title: title,
      message: message,
      ...options
    });
  }

  /**
   * Show an error toast
   * @param {string} message - Error message
   * @param {string} title - Optional custom title
   * @param {Object} options - Additional options
   */
  function showError(message, title, options = {}) {
    return createToast({
      type: 'error',
      title: title,
      message: message,
      delay: 7000, // Errors stay longer
      ...options
    });
  }

  /**
   * Show a warning toast
   * @param {string} message - Warning message
   * @param {string} title - Optional custom title
   * @param {Object} options - Additional options
   */
  function showWarning(message, title, options = {}) {
    return createToast({
      type: 'warning',
      title: title,
      message: message,
      delay: 6000, // Warnings stay a bit longer
      ...options
    });
  }

  /**
   * Show an info toast
   * @param {string} message - Info message
   * @param {string} title - Optional custom title
   * @param {Object} options - Additional options
   */
  function showInfo(message, title, options = {}) {
    return createToast({
      type: 'info',
      title: title,
      message: message,
      ...options
    });
  }

  /**
   * Clear all visible toasts
   */
  function clearAll() {
    const container = document.getElementById(TOAST_CONTAINER_ID);
    if (container) {
      const toasts = container.querySelectorAll('.toast');
      toasts.forEach(toast => {
        $(toast).toast('hide');
      });
    }
  }

  // Public API
  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    clearAll,
    createToast
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ToastUtils;
}

// Make globally accessible for inline scripts
window.ToastUtils = ToastUtils;
