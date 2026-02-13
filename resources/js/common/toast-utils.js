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
        z-index: 10500;
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
    
    // Generate unique ID
    const toastId = `toast-${++toastCounter}`;
    
    // Determine icon and colors based on type
    const typeConfig = getTypeConfig(type);
    
    // Create toast HTML
    const toastHtml = `
      <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="${delay}" data-autohide="${autohide}" style="margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border-radius: 8px; border: 1px solid ${typeConfig.borderColor}; overflow: hidden;">
        <div class="toast-header" style="background: linear-gradient(135deg, ${typeConfig.headerBg}, ${typeConfig.headerGradient}); color: ${typeConfig.headerColor}; border-bottom: 2px solid ${typeConfig.borderColor}; padding: 12px 16px; border-radius: 8px 8px 0 0;">
          <i class="${typeConfig.icon}" style="font-size: 1.25rem; margin-right: 10px;"></i>
          <strong class="mr-auto" style="font-size: 0.95rem; font-weight: 600;">${title || typeConfig.defaultTitle}</strong>
          <small class="text-muted" style="font-size: 0.75rem; opacity: 0.7;">just now</small>
          <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close" style="color: ${typeConfig.headerColor}; opacity: 0.9; font-size: 1.2rem;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="toast-body" style="background-color: ${typeConfig.bodyBg}; color: ${typeConfig.bodyColor}; padding: 14px 16px; font-size: 0.9rem; line-height: 1.5; border-radius: 0 0 8px 8px;">
          ${message}
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
        headerBg: '#1e3a1e',
        headerGradient: '#2d5a2d',
        headerColor: '#66BB6A',
        borderColor: '#66BB6A',
        bodyBg: '#1a1d23',
        bodyColor: '#e0e0e0'
      },
      error: {
        icon: 'mdi mdi-alert-circle',
        defaultTitle: 'Error',
        headerBg: '#3a1e1e',
        headerGradient: '#5a2d2d',
        headerColor: '#EF5350',
        borderColor: '#EF5350',
        bodyBg: '#1a1d23',
        bodyColor: '#e0e0e0'
      },
      warning: {
        icon: 'mdi mdi-alert',
        defaultTitle: 'Warning',
        headerBg: '#3a2f1e',
        headerGradient: '#5a4a2d',
        headerColor: '#FFA726',
        borderColor: '#FFA726',
        bodyBg: '#1a1d23',
        bodyColor: '#e0e0e0'
      },
      info: {
        icon: 'mdi mdi-information',
        defaultTitle: 'Info',
        headerBg: '#1e2a3a',
        headerGradient: '#2d4a5a',
        headerColor: '#42A5F5',
        borderColor: '#42A5F5',
        bodyBg: '#1a1d23',
        bodyColor: '#e0e0e0'
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
