/**
 * Sidebar Module
 * Handles sidebar toggle, collapse/expand, and menu navigation
 * Used by: layouts/admin.blade.php
 */

const Sidebar = (function() {
  'use strict';

  // Configuration
  const CONFIG = {
    storageKey: 'sidebar-state',
    selectors: {
      toggler: '[data-toggle="minimize"]',
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
    return localStorage.getItem(CONFIG.storageKey) === 'collapsed';
  }

  /**
   * Save sidebar state to localStorage
   * @param {boolean} collapsed
   */
  function saveState(collapsed) {
    localStorage.setItem(CONFIG.storageKey, collapsed ? 'collapsed' : 'expanded');
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
   * Uses direct DOM manipulation (no Bootstrap Collapse dependency)
   */
  function setupSubmenuToggle() {
    const toggleLinks = document.querySelectorAll(CONFIG.selectors.collapseLinks);

    toggleLinks.forEach(function(element) {
      const targetSelector = element.getAttribute('href');
      const target = document.querySelector(targetSelector);
      const parent = element.closest(CONFIG.selectors.menuItems);
      
      if (!target) return;

      element.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = target.classList.contains(CONFIG.classes.show);

        // Close all open menus first
        document.querySelectorAll(CONFIG.selectors.openMenus).forEach(function(openMenu) {
          openMenu.classList.remove(CONFIG.classes.show);
          const openParent = openMenu.closest(CONFIG.selectors.menuItems);
          if (openParent) {
            // Check if this dropdown has an active subpage
            const hasActiveChild = openMenu.querySelector('.sub-menu .nav-link.active');
            
            // Only remove active class if no active subpage inside
            if (!hasActiveChild) {
              openParent.classList.remove(CONFIG.classes.active);
            }
            
            const openLink = openParent.querySelector('[data-toggle="collapse"]');
            if (openLink) openLink.setAttribute('aria-expanded', 'false');
          }
        });

        // Toggle current menu (if it was closed, open it)
        if (!isOpen) {
          target.classList.add(CONFIG.classes.show);
          parent.classList.add(CONFIG.classes.active);
          element.setAttribute('aria-expanded', 'true');
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
        // Exact match OR child route (requires trailing slash boundary)
        const isActive = currentPath === linkPath ||
                        (linkPath !== '/' && currentPath.startsWith(linkPath + '/'));

        if (isActive) {
          const navItem = link.closest(CONFIG.selectors.navItem);
          if (navItem) {
            navItem.classList.add(CONFIG.classes.active);
          }

          // If inside submenu, handle parent dropdown and link highlighting
          const subMenu = link.closest(CONFIG.selectors.subMenu);
          if (subMenu) {
            // Add active class to the submenu link itself
            link.classList.add(CONFIG.classes.active);
            
            // Open parent collapse
            const collapseEl = subMenu.closest(CONFIG.selectors.collapse);
            if (collapseEl) {
              collapseEl.classList.add(CONFIG.classes.show);
              
              // Add active class to parent menu-items and set aria-expanded
              const parentMenuItem = collapseEl.closest(CONFIG.selectors.menuItems);
              if (parentMenuItem) {
                parentMenuItem.classList.add(CONFIG.classes.active);
                const parentLink = parentMenuItem.querySelector('[data-toggle="collapse"]');
                if (parentLink) {
                  parentLink.setAttribute('aria-expanded', 'true');
                }
              }
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

// Make globally accessible for inline scripts
window.Sidebar = Sidebar;
