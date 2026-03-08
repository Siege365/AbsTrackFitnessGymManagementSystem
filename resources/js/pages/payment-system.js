// ========================================
// PAYMENT SYSTEM - Main Page Controller
// ========================================
// Handles tab switching, animations, URL history, and modal interactions

document.addEventListener('DOMContentLoaded', function() {
  
  // ========================================
  // Read configuration from blade
  // ========================================
  const config = document.getElementById('paymentSystemConfig');
  if (!config) {
    console.error('Payment system config element not found');
    return;
  }

  const initialTab = config.dataset.initialTab || 'membership';
  const routes = {
    'membership': config.dataset.membershipRoute,
    'pt': config.dataset.ptRoute,
    'product': config.dataset.productRoute
  };

  // Flash messages
  const flashSuccess = config.dataset.flashSuccess;
  const flashError = config.dataset.flashError;
  const flashErrors = config.dataset.flashErrors;

  // ========================================
  // PAGE TOGGLE (Membership / PT / Product)
  // ========================================
  const pageToggleBtns = document.querySelectorAll('.page-toggle-btn');
  const pageMap = { 
    'membership': 'membershipPage', 
    'pt': 'ptPage', 
    'product': 'productPage' 
  };
  const pageOrder = ['membership', 'pt', 'product'];

  // ========================================
  // CLEANUP ON PAGE SWITCH
  // ========================================
  function cleanupOnPageSwitch(leavingPage) {
    if (leavingPage === 'product') {
      // Clear product cart localStorage when leaving product page
      try {
        localStorage.removeItem('paymentFormState_v1');
      } catch (e) {
        console.warn('Failed to clear product cart state', e);
      }
      
      // Clear Customer & Payment section fields
      const prodCustomerName = document.getElementById('prodCustomerName');
      const prodCustomerId = document.getElementById('prodCustomerId');
      const prodPaymentMethod = document.getElementById('prodPaymentMethod');
      const prodPaidAmount = document.getElementById('prodPaidAmount');
      const prodTotalAmount = document.getElementById('prodTotalAmount');
      const prodTotalDisplay = document.getElementById('prodTotalDisplay');
      const prodReturnAmount = document.getElementById('prodReturnAmount');
      const prodItemCount = document.getElementById('prodItemCount');
      
      if (prodCustomerName) prodCustomerName.value = '';
      if (prodCustomerId) prodCustomerId.value = '';
      if (prodPaymentMethod) prodPaymentMethod.value = 'Cash';
      if (prodPaidAmount) prodPaidAmount.value = '';
      if (prodTotalAmount) prodTotalAmount.value = '';
      if (prodTotalDisplay) prodTotalDisplay.value = '0.00';
      if (prodReturnAmount) prodReturnAmount.value = '';
      if (prodItemCount) prodItemCount.textContent = '0';
      
      // Clear cart table
      const prodItemsTableBody = document.getElementById('prodItemsTableBody');
      if (prodItemsTableBody) {
        prodItemsTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding: 2rem; color: #666;">No items added</td></tr>';
      }
    }
    // Can add cleanup for other pages if needed in the future
  }

  // ========================================
  // RESET TO DEFAULT PAYMENT TYPE ON TAB SWITCH
  // ========================================
  function resetToDefaultPaymentType(page) {
    if (page === 'membership') {
      // Reset to New Member (first pill)
      const membershipPills = document.querySelectorAll('#membershipPage .pay-type-pill');
      if (membershipPills.length > 0) {
        membershipPills.forEach(p => p.classList.remove('active'));
        const newMemberPill = document.querySelector('#membershipPage .pay-type-pill[data-type="new"]');
        if (newMemberPill) {
          newMemberPill.classList.add('active');
          // Trigger showing new member section
          const paymentTypeInput = document.getElementById('paymentType');
          if (paymentTypeInput) paymentTypeInput.value = 'new';
          const memberSelectionSection = document.getElementById('memberSelectionSection');
          const newMemberSection = document.getElementById('newMemberSection');
          if (memberSelectionSection && newMemberSection) {
            memberSelectionSection.classList.remove('member-section-visible');
            newMemberSection.classList.add('member-section-visible');
          }
        }
      }
    } else if (page === 'pt') {
      // Reset to New Client (first pill)
      const ptPills = document.querySelectorAll('#ptPage .pay-type-pill');
      if (ptPills.length > 0) {
        ptPills.forEach(p => p.classList.remove('active'));
        const newClientPill = document.querySelector('#ptPage .pay-type-pill[data-type="new"]');
        if (newClientPill) {
          newClientPill.classList.add('active');
          // Trigger showing new client section
          const ptPaymentTypeInput = document.getElementById('ptPaymentType');
          if (ptPaymentTypeInput) ptPaymentTypeInput.value = 'new';
          const ptClientSearchSection = document.getElementById('ptClientSearchSection');
          const ptNewClientSection = document.getElementById('ptNewClientSection');
          if (ptClientSearchSection && ptNewClientSection) {
            ptClientSearchSection.classList.remove('client-section-visible');
            ptNewClientSection.classList.add('client-section-visible');
          }
        }
      }
    }
    // Product page doesn't have payment type pills, so no need to reset
  }

  // ========================================
  // PAGE TOGGLE (Membership / PT / Product)
  // ========================================
  pageToggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetPage = this.dataset.page;
      const targetPanelId = pageMap[targetPage];
      
      // Null safety check
      if (!targetPanelId) {
        console.error('Invalid target page:', targetPage);
        return;
      }
      
      const currentActive = document.querySelector('.page-panel.active');
      const targetPanel = document.getElementById(targetPanelId);
      
      // Null safety checks
      if (!currentActive || !targetPanel) {
        console.error('Page panels not found');
        return;
      }
      
      if (currentActive === targetPanel) return;
      
      // Update button states
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      // Determine slide direction
      const currentPageKey = Object.keys(pageMap).find(k => pageMap[k] === currentActive.id);
      const currentIndex = pageOrder.indexOf(currentPageKey);
      const targetIndex = pageOrder.indexOf(targetPage);
      const goingRight = targetIndex > currentIndex;
      
      // Apply slide animations
      currentActive.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');
      targetPanel.classList.add(goingRight ? 'slide-in-right' : 'slide-in-left');
      targetPanel.classList.add('active');
      
      // Cleanup animations after transition and release lock
      setTimeout(() => {
        currentActive.classList.remove('active', 'slide-out-left', 'slide-out-right');
        targetPanel.classList.remove('slide-in-right', 'slide-in-left');
        
        // Cleanup old page data (clear cart, etc.)
        cleanupOnPageSwitch(currentPageKey);
        
        // Reset to default payment type after animation completes
        resetToDefaultPaymentType(targetPage);
      }, 450); // Slightly longer than animation duration

      // Update browser URL without page reload
      if (routes[targetPage]) {
        window.history.pushState({ paymentType: targetPage }, '', routes[targetPage]);
      }
    });
  });

  // ========================================
  // AUTO-SWITCH TO INITIAL TAB
  // ========================================
  if (initialTab && pageMap[initialTab]) {
    const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${initialTab}"]`);
    if (targetBtn) {
      // Set initial state without animations
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      targetBtn.classList.add('active');
      document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
      document.getElementById(pageMap[initialTab]).classList.add('active');
      
      // Reset to default payment type on initial load
      resetToDefaultPaymentType(initialTab);
    }
  }

  // ========================================
  // BROWSER BACK/FORWARD NAVIGATION
  // ========================================
  window.addEventListener('popstate', function(event) {
    if (event.state && event.state.paymentType) {
      const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${event.state.paymentType}"]`);
      if (targetBtn) {
        targetBtn.click();
      }
    }
  });

  // ========================================
  // GLOBAL MODAL HANDLERS
  // ========================================
  
  // Escape key to close modals
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      // Close any open modals
      if (typeof closeModal === 'function') closeModal();
      if (typeof closeConfirmationModal === 'function') closeConfirmationModal();
      if (typeof closePtConfirmation === 'function') closePtConfirmation();
      if (typeof closeProdConfirmation === 'function') closeProdConfirmation();
      if (typeof closeProductReceiptModal === 'function') closeProductReceiptModal();
      if (typeof closeProductRefundModal === 'function') closeProductRefundModal();
    }
  });

  // Click outside modal to close
  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('show');
    });
  });

  // ========================================
  // DISPLAY FLASH MESSAGES
  // ========================================
  if (typeof ToastUtils !== 'undefined') {
    if (flashSuccess) {
      ToastUtils.showSuccess(flashSuccess);
    }
    if (flashError) {
      ToastUtils.showError(flashError);
    }
    if (flashErrors) {
      ToastUtils.showError(flashErrors);
    }
  } else {
    console.warn('ToastUtils not available for flash messages');
  }
  
  // ========================================
  // EXPORT UTILITY FUNCTIONS FOR OTHER SCRIPTS
  // ========================================
  // Helper function to check if a specific page is currently active
  window.isPageActive = function(pageName) {
    const pageId = pageMap[pageName];
    if (!pageId) return false;
    const panel = document.getElementById(pageId);
    return panel && panel.classList.contains('active');
  };
  
  // Helper function to safely get element if page is active
  window.safeGetElement = function(elementId, pageName) {
    if (!window.isPageActive(pageName)) return null;
    return document.getElementById(elementId);
  };
});
