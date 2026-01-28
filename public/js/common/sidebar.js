/**
 * Sidebar Module
 * Handles sidebar toggle, collapse/expand, and menu navigation
 * Used by: layouts/admin.blade.php
 */

const Sidebar = (function() {
  'use strict';

  // Configuration
  const CONFIG = {
    storageKey: 'sidebarCollapsed',
    selectors: {
      toggler: '.navbar-toggler',
      collapseLinks: '.sidebar .nav-link[data-toggle="collapse"]',
      navLinks: '.sidebar .nav-link:not([data-toggle="collapse"])',
      openMenus: '.sidebar .collapse.show',
      subMenu: '.sub-menu',
      collapse: '.collapse',
      menuItems: '.menu-items',
      navItem: '.nav-item'
    },
    classes: {
      sidebarIconOnly: 'sidebar-icon-only',
      show: 'show',
      active: 'active'
    }
  };

  /**
   * Check if sidebar should be collapsed (from localStorage)
   * @returns {boolean}
   */
  function getSavedState() {
    return localStorage.getItem(CONFIG.storageKey) === 'true';
  }

  /**
   * Save sidebar state to localStorage
   * @param {boolean} collapsed
   */
  function saveState(collapsed) {
    localStorage.setItem(CONFIG.storageKey, collapsed);
  }

  /**
   * Toggle sidebar collapsed state
   */
  function toggle() {
    document.body.classList.toggle(CONFIG.classes.sidebarIconOnly);
    saveState(document.body.classList.contains(CONFIG.classes.sidebarIconOnly));
  }

  /**
   * Collapse sidebar
   */
  function collapse() {
    document.body.classList.add(CONFIG.classes.sidebarIconOnly);
    saveState(true);
  }

  /**
   * Expand sidebar
   */
  function expand() {
    document.body.classList.remove(CONFIG.classes.sidebarIconOnly);
    saveState(false);
  }

  /**
   * Apply saved state on page load
   */
  function applySavedState() {
    if (getSavedState()) {
      document.body.classList.add(CONFIG.classes.sidebarIconOnly);
    }
  }

  /**
   * Setup sidebar toggle button
   */
  function setupToggler() {
    const toggler = document.querySelector(CONFIG.selectors.toggler);
    
    if (toggler) {
      toggler.addEventListener('click', function(e) {
        e.preventDefault();
        toggle();
      });
    }
  }

  /**
   * Setup submenu toggle functionality
   */
  function setupSubmenuToggle() {
    const toggleLinks = document.querySelectorAll(CONFIG.selectors.collapseLinks);

    toggleLinks.forEach(function(element) {
      element.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const targetSelector = this.getAttribute('href');
        const target = document.querySelector(targetSelector);
        const parent = this.closest(CONFIG.selectors.menuItems);

        if (!target) return;

        // Toggle collapse
        if (target.classList.contains(CONFIG.classes.show)) {
          target.classList.remove(CONFIG.classes.show);
          parent.classList.remove(CONFIG.classes.active);
        } else {
          // Close other open menus first
          document.querySelectorAll(CONFIG.selectors.openMenus).forEach(function(openMenu) {
            openMenu.classList.remove(CONFIG.classes.show);
            const parentItem = openMenu.closest(CONFIG.selectors.menuItems);
            if (parentItem && !parentItem.querySelector(`${CONFIG.selectors.subMenu} ${CONFIG.selectors.navItem}.${CONFIG.classes.active}`)) {
              parentItem.classList.remove(CONFIG.classes.active);
            }
          });

          target.classList.add(CONFIG.classes.show);
          parent.classList.add(CONFIG.classes.active);
        }
      });
    });
  }

  /**
   * Set active menu based on current URL
   */
  function setActiveMenu() {
    const currentPath = window.location.pathname;

    document.querySelectorAll(CONFIG.selectors.navLinks).forEach(function(link) {
      const href = link.getAttribute('href');
      if (!href || href === '#') return;

      try {
        const linkPath = new URL(href, window.location.origin).pathname;
        const isActive = currentPath === linkPath ||
                        (linkPath !== '/' && currentPath.startsWith(linkPath));

        if (isActive) {
          const navItem = link.closest(CONFIG.selectors.navItem);
          if (navItem) {
            navItem.classList.add(CONFIG.classes.active);
          }

          // If inside submenu, open parent collapse
          const subMenu = link.closest(CONFIG.selectors.subMenu);
          if (subMenu) {
            const collapseEl = subMenu.closest(CONFIG.selectors.collapse);
            if (collapseEl) {
              collapseEl.classList.add(CONFIG.classes.show);
            }
          }
        }
      } catch (e) {
        // Skip invalid URLs
      }
    });
  }

  /**
   * Initialize sidebar functionality
   */
  function init() {
    applySavedState();
    setupToggler();
    setupSubmenuToggle();
    setActiveMenu();
  }

  // Public API
  return {
    init,
    toggle,
    collapse,
    expand,
    getSavedState,
    saveState,
    setActiveMenu
  };
})();

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  Sidebar.init();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = Sidebar;
}
