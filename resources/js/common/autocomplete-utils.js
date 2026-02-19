/**
 * Universal Autocomplete Utility
 * 
 * Provides reusable autocomplete functionality with autofill support.
 * Can be used across different pages and forms.
 * 
 * @example
 * AutocompleteUtils.init({
 *   inputElement: document.getElementById('nameInput'),
 *   apiUrl: '/api/clients/autocomplete',
 *   onSelect: (item) => {
 *     document.getElementById('age').value = item.age;
 *     document.getElementById('contact').value = item.contact;
 *   }
 * });
 */

const AutocompleteUtils = (function() {
  'use strict';

  let activeInstances = new Map();
  let debounceTimers = new Map();

  /**
   * Initialize autocomplete on an input element
   * @param {Object} options - Configuration options
   * @param {HTMLElement} options.inputElement - The input field to attach autocomplete to
   * @param {string} options.apiUrl - API endpoint for fetching suggestions
   * @param {Function} options.onSelect - Callback when an item is selected
   * @param {number} [options.minChars=1] - Minimum characters before triggering search
   * @param {number} [options.debounceMs=100] - Debounce delay in milliseconds
   * @param {Function} [options.renderItem] - Custom item renderer function
   * @param {Function} [options.formatLabel] - Custom label formatter
   * @returns {Object} Instance with destroy method
   */
  function init(options) {
    const {
      inputElement,
      apiUrl,
      onSelect,
      minChars = 1,
      debounceMs = 100,
      renderItem = defaultRenderItem,
      formatLabel = defaultFormatLabel
    } = options;

    if (!inputElement || !apiUrl || !onSelect) {
      return null;
    }

    // Disable browser autocomplete to prevent overlap
    inputElement.setAttribute('autocomplete', 'off');
    
    // Create dropdown container
    const dropdown = createDropdown(inputElement);
    
    // Store instance reference
    const instanceId = generateId();
    activeInstances.set(instanceId, {
      inputElement,
      dropdown,
      apiUrl,
      onSelect,
      minChars,
      debounceMs,
      renderItem,
      formatLabel,
      selectedIndex: -1,
      items: []
    });

    // Attach event listeners
    setupEventListeners(instanceId);

    // Return destroy method
    return {
      destroy: () => destroy(instanceId)
    };
  }

  /**
   * Create dropdown container
   */
  function createDropdown(inputElement) {
    const dropdown = document.createElement('div');
    dropdown.className = 'autocomplete-dropdown';
    dropdown.style.display = 'none';
    
    // Position dropdown relative to input
    const parent = inputElement.parentElement;
    parent.style.position = parent.style.position || 'relative';
    parent.insertBefore(dropdown, inputElement.nextSibling);
    
    return dropdown;
  }

  /**
   * Setup event listeners for autocomplete instance
   */
  function setupEventListeners(instanceId) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    const { inputElement, dropdown, debounceMs } = instance;

    // Input event - trigger search
    const inputHandler = (e) => {
      const value = e.target.value.trim();
      
      // Clear previous debounce timer
      if (debounceTimers.has(instanceId)) {
        clearTimeout(debounceTimers.get(instanceId));
      }

      if (value.length < instance.minChars) {
        hideDropdown(instanceId);
        return;
      }

      // Debounce API call
      const timer = setTimeout(() => {
        fetchSuggestions(instanceId, value);
      }, debounceMs);
      
      debounceTimers.set(instanceId, timer);
    };

    // Keyboard navigation
    const keydownHandler = (e) => {
      if (!instance.items.length) return;

      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          navigateDropdown(instanceId, 1);
          break;
        case 'ArrowUp':
          e.preventDefault();
          navigateDropdown(instanceId, -1);
          break;
        case 'Enter':
          e.preventDefault();
          if (instance.selectedIndex >= 0) {
            selectItem(instanceId, instance.selectedIndex);
          }
          break;
        case 'Escape':
          hideDropdown(instanceId);
          break;
      }
    };

    // Click outside to close
    const documentClickHandler = (e) => {
      if (!inputElement.contains(e.target) && !dropdown.contains(e.target)) {
        hideDropdown(instanceId);
      }
    };

    // Store handlers for cleanup
    instance.handlers = {
      input: inputHandler,
      keydown: keydownHandler,
      documentClick: documentClickHandler
    };

    inputElement.addEventListener('input', inputHandler);
    inputElement.addEventListener('keydown', keydownHandler);
    document.addEventListener('click', documentClickHandler);
  }

  /**
   * Fetch suggestions from API
   */
  async function fetchSuggestions(instanceId, query) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    try {
      const url = `${instance.apiUrl}?query=${encodeURIComponent(query)}`;
      const response = await fetch(url);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const items = await response.json();
      instance.items = items;
      instance.selectedIndex = -1;

      if (items.length > 0) {
        renderDropdown(instanceId, items);
        showDropdown(instanceId);
      } else {
        hideDropdown(instanceId);
      }
    } catch (error) {
      hideDropdown(instanceId);
    }
  }

  /**
   * Render dropdown with items
   */
  function renderDropdown(instanceId, items) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    const { dropdown, renderItem, formatLabel } = instance;
    dropdown.innerHTML = '';

    items.forEach((item, index) => {
      const itemElement = document.createElement('div');
      itemElement.className = 'autocomplete-item';
      itemElement.innerHTML = renderItem(item, formatLabel);
      itemElement.dataset.index = index;

      // Click handler
      itemElement.addEventListener('click', () => {
        selectItem(instanceId, index);
      });

      // Hover handler
      itemElement.addEventListener('mouseenter', () => {
        highlightItem(instanceId, index);
      });

      dropdown.appendChild(itemElement);
    });
  }

  /**
   * Default item renderer
   */
  function defaultRenderItem(item, formatLabel) {
    return `
      <div class="autocomplete-item-content">
        <div class="autocomplete-item-name">${formatLabel(item.name)}</div>
        <div class="autocomplete-item-meta">
          ${item.age ? `Age: ${item.age}` : ''}
          ${item.contact ? ` • ${item.contact}` : ''}
        </div>
      </div>
    `;
  }

  /**
   * Default label formatter (highlights matching text)
   */
  function defaultFormatLabel(text) {
    return escapeHtml(text);
  }

  /**
   * Navigate dropdown with keyboard
   */
  function navigateDropdown(instanceId, direction) {
    const instance = activeInstances.get(instanceId);
    if (!instance || !instance.items.length) return;

    const newIndex = instance.selectedIndex + direction;
    
    if (newIndex >= 0 && newIndex < instance.items.length) {
      highlightItem(instanceId, newIndex);
    }
  }

  /**
   * Highlight item in dropdown
   */
  function highlightItem(instanceId, index) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    instance.selectedIndex = index;
    
    const items = instance.dropdown.querySelectorAll('.autocomplete-item');
    items.forEach((item, i) => {
      if (i === index) {
        item.classList.add('active');
        // Ensure item is visible in scrollable dropdown
        item.scrollIntoView({ block: 'nearest' });
      } else {
        item.classList.remove('active');
      }
    });
  }

  /**
   * Select item from dropdown
   */
  function selectItem(instanceId, index) {
    const instance = activeInstances.get(instanceId);
    if (!instance || !instance.items[index]) return;

    const selectedItem = instance.items[index];
    
    // Update input value
    instance.inputElement.value = selectedItem.name;
    
    // Call onSelect callback
    instance.onSelect(selectedItem);
    
    // Hide dropdown
    hideDropdown(instanceId);
  }

  /**
   * Show dropdown
   */
  function showDropdown(instanceId) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    instance.dropdown.style.display = 'block';
  }

  /**
   * Hide dropdown
   */
  function hideDropdown(instanceId) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    instance.dropdown.style.display = 'none';
    instance.selectedIndex = -1;
  }

  /**
   * Destroy autocomplete instance
   */
  function destroy(instanceId) {
    const instance = activeInstances.get(instanceId);
    if (!instance) return;

    // Remove event listeners
    if (instance.handlers) {
      instance.inputElement.removeEventListener('input', instance.handlers.input);
      instance.inputElement.removeEventListener('keydown', instance.handlers.keydown);
      document.removeEventListener('click', instance.handlers.documentClick);
    }

    // Remove dropdown element
    if (instance.dropdown && instance.dropdown.parentElement) {
      instance.dropdown.parentElement.removeChild(instance.dropdown);
    }

    // Clear debounce timer
    if (debounceTimers.has(instanceId)) {
      clearTimeout(debounceTimers.get(instanceId));
      debounceTimers.delete(instanceId);
    }

    // Remove instance
    activeInstances.delete(instanceId);
  }

  /**
   * Utility: Escape HTML
   */
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Utility: Generate unique ID
   */
  function generateId() {
    return 'ac-' + Math.random().toString(36).substr(2, 9);
  }

  // Public API
  return {
    init,
    escapeHtml
  };
})();

// Export for use in other modules
window.AutocompleteUtils = AutocompleteUtils;
