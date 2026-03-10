/**
 * Table Dropdown Positioning Utility
 * Handles dynamic positioning of dropdown menus in tables to prevent overflow issues
 * and auto-flip upward on bottom rows
 */

const TableDropdown = {
  /**
   * Initialize table dropdown positioning
   */
  init: function() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupDropdowns());
    } else {
      this.setupDropdowns();
    }
  },

  /**
   * Setup dropdown positioning for all table dropdowns
   */
  setupDropdowns: function() {
    if (typeof window.$ === 'undefined') {
      console.error('[TableDropdown] jQuery not found!');
      return;
    }
    
    const tableDropdowns = document.querySelectorAll('.table .dropdown');
    
    tableDropdowns.forEach((dropdown) => {
      const button = dropdown.querySelector('[data-toggle="dropdown"]');
      const menu = dropdown.querySelector('.dropdown-menu');
      
      if (!button || !menu) return;

      // Bootstrap emits dropdown events on the .dropdown parent.
      window.$(dropdown).on('shown.bs.dropdown', () => {
        this.positionDropdown(button, menu);
        this.setupRepositioning(button, menu);
      });

      // Cleanup on hide
      window.$(dropdown).on('hide.bs.dropdown', () => {
        this.cleanupRepositioning();
      });

      // Extra fallback: position shortly after direct toggle click.
      button.addEventListener('click', () => {
        setTimeout(() => {
          if (window.$(menu).hasClass('show')) {
            this.positionDropdown(button, menu);
            this.setupRepositioning(button, menu);
          }
        }, 0);
      });
    });
  },

  /**
   * Position dropdown menu relative to trigger button
   * @param {HTMLElement} button - The dropdown trigger button
   * @param {HTMLElement} menu - The dropdown menu element
   */
  positionDropdown: function(button, menu) {
    const buttonRect = button.getBoundingClientRect();
    const menuHeight = menu.offsetHeight;
    const menuWidth = menu.offsetWidth;

    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const spaceBelow = viewportHeight - buttonRect.bottom;
    const spaceAbove = buttonRect.top;

    const shouldFlipUp = spaceBelow < menuHeight && spaceAbove > spaceBelow;

    let top;
    if (shouldFlipUp) {
      top = buttonRect.top - menuHeight - 4;
    } else {
      top = buttonRect.bottom + 4;
    }

    let left = buttonRect.right - menuWidth;
    if (left < 0) {
      left = buttonRect.left;
    }
    if (left + menuWidth > viewportWidth) {
      left = viewportWidth - menuWidth - 10;
    }

    menu.style.setProperty('position', 'fixed', 'important');
    menu.style.setProperty('top', `${top}px`, 'important');
    menu.style.setProperty('left', `${left}px`, 'important');
    menu.style.setProperty('bottom', 'auto', 'important');
    menu.style.setProperty('right', 'auto', 'important');
    menu.style.setProperty('transform', 'none', 'important');
    menu.style.setProperty('margin', '0', 'important');
    menu.style.setProperty('z-index', '100000', 'important');
    menu.style.setProperty('will-change', 'transform');
  },

  /**
   * Setup repositioning on scroll and resize
   * @param {HTMLElement} button - The dropdown trigger button
   * @param {HTMLElement} menu - The dropdown menu element
   */
  setupRepositioning: function(button, menu) {
    // Store references for cleanup
    this.activeButton = button;
    this.activeMenu = menu;
    
    // Reposition on scroll (throttled)
    this.scrollHandler = this.throttle(() => {
      if (window.$(menu).hasClass('show')) {
        this.positionDropdown(button, menu);
      }
    }, 10);

    // Reposition on resize (throttled)
    this.resizeHandler = this.throttle(() => {
      if (window.$(menu).hasClass('show')) {
        this.positionDropdown(button, menu);
      }
    }, 100);

    window.addEventListener('scroll', this.scrollHandler, true); // Use capture for all scrolls
    window.addEventListener('resize', this.resizeHandler);
  },

  /**
   * Cleanup event listeners
   */
  cleanupRepositioning: function() {
    if (this.scrollHandler) {
      window.removeEventListener('scroll', this.scrollHandler, true);
    }
    if (this.resizeHandler) {
      window.removeEventListener('resize', this.resizeHandler);
    }
    
    this.activeButton = null;
    this.activeMenu = null;
  },

  /**
   * Throttle function to limit execution rate
   * @param {Function} func - Function to throttle
   * @param {number} wait - Wait time in milliseconds
   * @returns {Function} Throttled function
   */
  throttle: function(func, wait) {
    let timeout = null;
    let previous = 0;

    return function(...args) {
      const now = Date.now();
      const remaining = wait - (now - previous);

      if (remaining <= 0 || remaining > wait) {
        if (timeout) {
          clearTimeout(timeout);
          timeout = null;
        }
        previous = now;
        func.apply(this, args);
      } else if (!timeout) {
        timeout = setTimeout(() => {
          previous = Date.now();
          timeout = null;
          func.apply(this, args);
        }, remaining);
      }
    };
  }
};

// Initialize on load
TableDropdown.init();

// Export for global access
window.TableDropdown = TableDropdown;

export default TableDropdown;
