/**
 * Avatar Utilities Module
 * Reusable avatar handling for file uploads and URL inputs
 * Used by: memberships, clients pages
 */

const AvatarUtils = (function() {
  'use strict';

  // Configuration
  const CONFIG = {
    validTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
    maxFileSize: 2 * 1024 * 1024, // 2MB
    imageExtensions: ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
    previewStyle: 'width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255, 255, 255, 0.2);'
  };

  // Error message templates
  const MESSAGES = {
    invalidFileType: 'Please select a valid image file (JPEG, PNG, or GIF)',
    fileTooLarge: 'File size must be less than 2MB',
    fileReadError: 'Error reading file',
    invalidUrl: 'Please enter a valid URL',
    urlShouldBeImage: 'URL should point to an image file',
    loadingImage: 'Loading image...',
    urlLoadFailed: 'Failed to load image from URL. Please check the URL and try again.'
  };

  /**
   * Create HTML for error message
   * @param {string} message - Error message
   * @param {string} type - 'error', 'warning', 'info'
   * @returns {string} HTML string
   */
  function createMessage(message, type = 'error') {
    const colors = {
      error: '#F44336',
      warning: '#FFA726',
      info: '#64B5F6'
    };
    const icons = {
      error: 'mdi-alert-circle',
      warning: 'mdi-alert',
      info: 'mdi-loading mdi-spin'
    };
    return `<p style="color: ${colors[type]}; font-size: 0.9rem; margin-top: 0.5rem;"><i class="mdi ${icons[type]}"></i> ${message}</p>`;
  }

  /**
   * Create image preview HTML
   * @param {string} src - Image source URL or data URI
   * @returns {string} HTML string
   */
  function createPreviewImage(src) {
    return `<img src="${src}" alt="Preview" style="${CONFIG.previewStyle}">`;
  }

  /**
   * Toggle between file input and URL input for avatar
   * @param {Object} options - Configuration options
   * @param {HTMLElement} options.fileInput - File input element
   * @param {HTMLElement} options.urlInput - URL input element
   * @param {HTMLElement} options.preview - Preview container element
   * @param {string} type - 'file' or 'url'
   * @param {Object} state - State object to update (avatarFile, avatarUrl)
   */
  function toggleAvatarInput(options, type, state) {
    const { fileInput, urlInput, preview } = options;

    if (type === 'file') {
      fileInput.style.display = 'block';
      urlInput.style.display = 'none';
      urlInput.value = '';
      if (state) state.avatarUrl = null;
    } else {
      fileInput.style.display = 'none';
      urlInput.style.display = 'block';
      fileInput.value = '';
      if (state) state.avatarFile = null;
    }
    preview.innerHTML = '';
  }

  /**
   * Validate and preview file input
   * @param {File} file - File object
   * @param {HTMLElement} preview - Preview container element
   * @param {Object} state - State object to update
   * @returns {boolean} - Whether file is valid
   */
  function validateAndPreviewFile(file, preview, state) {
    // Validate file type
    if (!CONFIG.validTypes.includes(file.type)) {
      preview.innerHTML = createMessage(MESSAGES.invalidFileType, 'error');
      if (state) state.avatarFile = null;
      return false;
    }

    // Validate file size
    if (file.size > CONFIG.maxFileSize) {
      preview.innerHTML = createMessage(MESSAGES.fileTooLarge, 'error');
      if (state) state.avatarFile = null;
      return false;
    }

    // Store file reference
    if (state) {
      state.avatarFile = file;
      state.avatarUrl = null;
    }

    // Read and preview
    const reader = new FileReader();

    reader.onerror = function() {
      preview.innerHTML = createMessage(MESSAGES.fileReadError, 'error');
      if (state) state.avatarFile = null;
    };

    reader.onload = function(e) {
      preview.innerHTML = createPreviewImage(e.target.result);
    };

    reader.readAsDataURL(file);
    return true;
  }

  /**
   * Validate and preview URL input
   * @param {string} url - Image URL
   * @param {HTMLElement} preview - Preview container element
   * @param {Object} state - State object to update
   * @returns {boolean} - Whether URL is initially valid (actual image load is async)
   */
  function validateAndPreviewUrl(url, preview, state) {
    url = url.trim();

    // Validate URL format
    try {
      new URL(url);
    } catch (e) {
      preview.innerHTML = createMessage(MESSAGES.invalidUrl, 'error');
      if (state) state.avatarUrl = null;
      return false;
    }

    // Check if URL ends with image extension (warning only)
    const urlLower = url.toLowerCase();
    const hasImageExtension = CONFIG.imageExtensions.some(ext => urlLower.includes(ext));

    if (!hasImageExtension) {
      preview.innerHTML = createMessage(MESSAGES.urlShouldBeImage, 'warning');
    }

    // Store URL reference
    if (state) {
      state.avatarUrl = url;
      state.avatarFile = null;
    }

    // Show loading state
    preview.innerHTML = createMessage(MESSAGES.loadingImage, 'info');

    // Test if URL loads as image
    const img = new Image();
    img.onload = function() {
      preview.innerHTML = createPreviewImage(url);
    };
    img.onerror = function() {
      preview.innerHTML = createMessage(MESSAGES.urlLoadFailed, 'error');
      if (state) state.avatarUrl = null;
    };
    img.src = url;

    return true;
  }

  /**
   * Preview avatar from file or URL input
   * @param {Object} options - Configuration options
   * @param {HTMLElement} options.fileInput - File input element
   * @param {HTMLElement} options.urlInput - URL input element
   * @param {HTMLElement} options.preview - Preview container element
   * @param {Object} state - State object to update
   */
  function previewAvatar(options, state) {
    const { fileInput, urlInput, preview } = options;

    if (fileInput.style.display !== 'none' && fileInput.files && fileInput.files[0]) {
      const file = fileInput.files[0];
      if (!validateAndPreviewFile(file, preview, state)) {
        fileInput.value = '';
      }
    } else if (urlInput.style.display !== 'none' && urlInput.value) {
      validateAndPreviewUrl(urlInput.value, preview, state);
    } else {
      if (state) {
        state.avatarFile = null;
        state.avatarUrl = null;
      }
      preview.innerHTML = '';
    }
  }

  /**
   * Set avatar preview in confirmation modal
   * @param {Object} options - Configuration options
   * @param {HTMLElement} options.fileInput - File input element
   * @param {HTMLElement} options.urlInput - URL input element
   * @param {HTMLElement} options.avatarLarge - Large avatar image element
   * @param {HTMLElement} options.avatarSmall - Small avatar image element
   * @param {HTMLElement} options.noAvatarText - No avatar text element
   * @param {Object} state - State object with avatarUrl
   */
  function setConfirmationAvatar(options, state) {
    const { fileInput, urlInput, avatarLarge, avatarSmall, noAvatarText } = options;

    if (fileInput.style.display !== 'none' && fileInput.files && fileInput.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        avatarLarge.src = e.target.result;
        avatarSmall.src = e.target.result;
        avatarLarge.style.display = 'block';
        avatarSmall.style.display = 'block';
        noAvatarText.style.display = 'none';
      };
      reader.readAsDataURL(fileInput.files[0]);
    } else if (urlInput.style.display !== 'none' && urlInput.value && state.avatarUrl) {
      avatarLarge.src = urlInput.value;
      avatarSmall.src = urlInput.value;
      avatarLarge.style.display = 'block';
      avatarSmall.style.display = 'block';
      noAvatarText.style.display = 'none';
    } else {
      avatarLarge.style.display = 'none';
      avatarSmall.style.display = 'none';
      noAvatarText.style.display = 'block';
    }
  }

  /**
   * Append avatar data to FormData
   * @param {FormData} formData - FormData object
   * @param {Object} options - Configuration options
   * @param {HTMLElement} options.fileInput - File input element
   * @param {HTMLElement} options.urlInput - URL input element
   * @param {Object} state - State object with avatarUrl
   */
  function appendAvatarToFormData(formData, options, state) {
    const { fileInput, urlInput } = options;

    if (fileInput.style.display !== 'none' && fileInput.files && fileInput.files[0]) {
      formData.append('avatar', fileInput.files[0]);
    } else if (urlInput.style.display !== 'none' && urlInput.value && state.avatarUrl) {
      formData.append('avatar_url', urlInput.value);
    }
  }

  // Public API
  return {
    CONFIG,
    MESSAGES,
    toggleAvatarInput,
    validateAndPreviewFile,
    validateAndPreviewUrl,
    previewAvatar,
    setConfirmationAvatar,
    appendAvatarToFormData,
    createMessage,
    createPreviewImage
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AvatarUtils;
}
