/**
 * Payments Page Module
 * Page-specific JavaScript for PaymentAndBillings/PaymentAndBilling.blade.php
 * Handles cart management, item search, and payment processing
 */

const PaymentsPage = (function() {
  'use strict';

  // Module state
  let state = {
    cartItems: [],
    inventoryItems: [],
    selectedSearchItem: null,
    isSubmitting: false
  };

  // Configuration
  let config = {
    memberSearchUrl: '/members/search'
  };

  // Persistence key
  const STORAGE_KEY = 'paymentFormState_v1';

  // DOM element references (cached after init)
  let elements = {};

  /**
   * Cache DOM elements
   */
  function cacheElements() {
    elements = {
      searchItem: document.getElementById('searchItem'),
      searchResults: document.getElementById('searchResults'),
      searchClearBtn: document.getElementById('searchClearBtn'),
      addItemBtn: document.getElementById('addItemBtn'),
      clearBtn: document.getElementById('clearBtn'),
      itemsTableBody: document.getElementById('itemsTableBody'),
      customerName: document.getElementById('customerName'),
      customerId: document.getElementById('customerId'),
      customerResults: document.getElementById('customerResults'),
      transactionType: document.getElementById('transactionType'),
      paymentMethod: document.getElementById('paymentMethod'),
      paidAmount: document.getElementById('paidAmount'),
      totalAmount: document.getElementById('totalAmount'),
      returnAmount: document.getElementById('returnAmount'),
      paymentForm: document.getElementById('paymentForm'),
      itemsData: document.getElementById('itemsData')
    };
  }

  /**
   * Save current form + cart state to localStorage
   */
  function saveState() {
    try {
      const data = {
        cartItems: state.cartItems,
        customer_name: elements.customerName?.value || '',
        customer_id: elements.customerId?.value || '',
        transaction_type: elements.transactionType?.value || '',
        payment_method: elements.paymentMethod?.value || '',
        paid_amount: elements.paidAmount?.value || '',
        total_amount: elements.totalAmount?.value || ''
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
      console.warn('Failed to save payment form state', e);
    }
  }

  /**
   * Load saved state from localStorage
   */
  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;

      const data = JSON.parse(raw);
      if (data) {
        state.cartItems = Array.isArray(data.cartItems) ? data.cartItems : [];

        if (elements.customerName) elements.customerName.value = data.customer_name || '';
        if (elements.customerId) elements.customerId.value = data.customer_id || '';
        if (elements.transactionType) elements.transactionType.value = data.transaction_type || '';
        if (elements.paymentMethod) elements.paymentMethod.value = data.payment_method || '';
        if (elements.paidAmount) elements.paidAmount.value = data.paid_amount || '';
        if (elements.totalAmount) elements.totalAmount.value = data.total_amount || '';

        renderCart();
        calculateTotals();
      }
    } catch (e) {
      console.warn('Failed to load payment form state', e);
    }
  }

  /**
   * Clear saved state
   */
  function clearState() {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      // Ignore
    }
  }

  /**
   * Add item to cart
   * @param {Object} item - Item to add
   */
  function addItemToCart(item) {
    if (!item || typeof item.stock === 'undefined' || item.stock <= 0) {
      alert('Cannot add item — Insufficient stock.');
      return;
    }

    const existingItem = state.cartItems.find(i => i.id == item.id);

    if (existingItem) {
      if (existingItem.qty < item.stock) {
        existingItem.qty++;
      } else {
        alert('Cannot add more. Insufficient stock!');
        return;
      }
    } else {
      state.cartItems.push({
        id: item.id,
        name: item.name,
        price: item.price,
        qty: 1,
        stock: item.stock
      });
    }

    renderCart();
    calculateTotals();
    saveState();
  }

  /**
   * Render cart table
   */
  function renderCart() {
    const tbody = elements.itemsTableBody;

    if (state.cartItems.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>';
      return;
    }

    tbody.innerHTML = state.cartItems.map((item, index) => `
      <tr>
        <td>${item.name}</td>
        <td>
          <input type="number" class="form-control form-control-sm" value="${item.qty}" 
                 min="1" max="${item.stock}" onchange="PaymentsPage.updateQty(${index}, this.value)">
        </td>
        <td>₱${item.price.toFixed(2)}</td>
        <td>₱${(item.price * item.qty).toFixed(2)}</td>
        <td>
          <button type="button" class="btn btn-sm btn-danger" onclick="PaymentsPage.removeItem(${index})">
            <i class="mdi mdi-delete"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }

  /**
   * Update item quantity
   * @param {number} index - Cart item index
   * @param {number|string} newQty - New quantity
   */
  function updateQty(index, newQty) {
    newQty = parseInt(newQty);
    if (newQty > 0 && newQty <= state.cartItems[index].stock) {
      state.cartItems[index].qty = newQty;
      renderCart();
      calculateTotals();
    } else {
      alert('Invalid quantity or insufficient stock!');
      renderCart();
    }
    saveState();
  }

  /**
   * Remove item from cart
   * @param {number} index - Cart item index
   */
  function removeItem(index) {
    state.cartItems.splice(index, 1);
    renderCart();
    calculateTotals();
    saveState();
  }

  /**
   * Calculate totals and return amount
   */
  function calculateTotals() {
    const total = state.cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    elements.totalAmount.value = total.toFixed(2);

    const paidAmount = parseFloat(elements.paidAmount.value) || 0;
    const returnAmount = paidAmount - total;
    elements.returnAmount.value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
  }

  /**
   * Clear form and cart
   */
  function clearForm() {
    state.cartItems = [];
    renderCart();
    elements.paymentForm.reset();
    elements.totalAmount.value = '';
    elements.returnAmount.value = '';
    state.selectedSearchItem = null;
    clearState();
  }

  /**
   * Setup item search functionality
   */
  function setupItemSearch() {
    elements.searchItem.addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const searchResults = elements.searchResults;

      if (searchTerm.length < 2) {
        searchResults.style.display = 'none';
        return;
      }

      const filtered = state.inventoryItems.filter(item =>
        item.product_name.toLowerCase().includes(searchTerm) ||
        item.product_number.toLowerCase().includes(searchTerm)
      );

      if (filtered.length > 0) {
        searchResults.innerHTML = filtered.map(item => `
          <div class="search-result-item" data-id="${item.id}" data-name="${item.product_name}" 
               data-price="${item.unit_price}" data-stock="${item.stock_qty}">
            <strong>${item.product_name}</strong> - ₱${parseFloat(item.unit_price).toFixed(2)} 
            <span class="text-muted">(Stock: ${item.stock_qty})</span>
          </div>
        `).join('');
        searchResults.style.display = 'block';

        // Add click handlers
        searchResults.querySelectorAll('.search-result-item').forEach(item => {
          item.addEventListener('click', function() {
            const stock = parseInt(this.dataset.stock);
            const name = this.dataset.name;
            const id = this.dataset.id;
            const price = parseFloat(this.dataset.price);

            if (isNaN(stock) || stock <= 0) {
              alert('This item is out of stock and cannot be selected.');
              return;
            }

            state.selectedSearchItem = { id, name, price, stock };
            elements.searchItem.value = name;
            searchResults.style.display = 'none';
          });
        });
      } else {
        searchResults.innerHTML = '<div class="search-result-item">No items found</div>';
        searchResults.style.display = 'block';
      }
    });
  }

  /**
   * Setup customer name autocomplete
   */
  function setupCustomerSearch() {
    const input = elements.customerName;
    const resultsEl = elements.customerResults;
    const customerIdEl = elements.customerId;
    let debounceTimer;

    if (!input || !resultsEl) return;

    input.addEventListener('input', function() {
      const q = this.value.trim();
      customerIdEl.value = '';

      clearTimeout(debounceTimer);
      if (q.length < 1) {
        resultsEl.style.display = 'none';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`${config.memberSearchUrl}?q=${encodeURIComponent(q)}`)
          .then(r => r.json())
          .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
              resultsEl.innerHTML = '<div class="search-result-item">No members found</div>';
              resultsEl.style.display = 'block';
              return;
            }

            resultsEl.innerHTML = data.map(m => `
              <div class="search-result-item" data-id="${m.id}" data-name="${m.name}">
                <strong>${m.name}</strong> <span class="text-muted">${m.contact || ''}</span>
              </div>
            `).join('');
            resultsEl.style.display = 'block';

            resultsEl.querySelectorAll('.search-result-item').forEach(el => {
              el.addEventListener('click', function() {
                input.value = this.dataset.name;
                if (customerIdEl) customerIdEl.value = this.dataset.id;
                resultsEl.style.display = 'none';
              });
            });
          })
          .catch(err => {
            console.error('Member search error', err);
            resultsEl.style.display = 'none';
          });
      }, 250);
    });
  }

  /**
   * Setup event listeners
   */
  function setupEventListeners() {
    // Search clear button
    elements.searchClearBtn.addEventListener('click', function() {
      clearForm();
    });

    // Add item button
    elements.addItemBtn.addEventListener('click', function() {
      if (!state.selectedSearchItem) {
        const name = elements.searchItem.value.trim();
        if (!name) {
          alert('Please select an item first from the search results.');
          return;
        }
        const found = state.inventoryItems.find(i => 
          i.product_name.toLowerCase() === name.toLowerCase()
        );
        if (!found) {
          alert('Selected item not found. Please choose from the search results.');
          return;
        }
        if (found.stock_qty <= 0) {
          alert('This item is out of stock and cannot be added.');
          return;
        }
        state.selectedSearchItem = {
          id: found.id,
          name: found.product_name,
          price: parseFloat(found.unit_price),
          stock: found.stock_qty
        };
      }

      addItemToCart(state.selectedSearchItem);
      elements.searchItem.value = '';
      elements.searchItem.focus();
      elements.searchResults.style.display = 'none';
      state.selectedSearchItem = null;
    });

    // Clear button
    elements.clearBtn.addEventListener('click', function() {
      clearForm();
    });

    // Paid amount change
    elements.paidAmount.addEventListener('input', function() {
      calculateTotals();
      saveState();
    });

    // Form submit
    elements.paymentForm.addEventListener('submit', function(e) {
      if (state.cartItems.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the cart!');
        return;
      }

      const total = parseFloat(elements.totalAmount.value) || 0;
      const paid = parseFloat(elements.paidAmount.value) || 0;
      if (paid < total) {
        e.preventDefault();
        alert('Payment incomplete: Paid amount must be equal to or greater than the total amount.');
        elements.paidAmount.focus();
        return;
      }

      elements.itemsData.value = JSON.stringify(state.cartItems);
      state.isSubmitting = true;
      clearState();
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
      if (!e.target.closest('#searchItem') && !e.target.closest('#searchResults')) {
        elements.searchResults.style.display = 'none';
      }
      if (!e.target.closest('#customerName') && !e.target.closest('#customerResults')) {
        if (elements.customerResults) {
          elements.customerResults.style.display = 'none';
        }
      }
    });

    // Save state before leaving (unless submitting)
    window.addEventListener('beforeunload', function() {
      if (!state.isSubmitting) saveState();
    });
  }

  /**
   * Initialize the page
   * @param {Object} options - Configuration options
   * @param {Array} options.inventoryItems - Inventory items array
   * @param {string} options.memberSearchUrl - Member search URL
   */
  function init(options) {
    config = { ...config, ...options };
    state.inventoryItems = options.inventoryItems || [];

    cacheElements();
    setupItemSearch();
    setupCustomerSearch();
    setupEventListeners();
    loadState();

    console.log('Inventory Items Loaded:', state.inventoryItems.length);
  }

  // Public API
  return {
    init,
    addItemToCart,
    updateQty,
    removeItem,
    calculateTotals,
    clearForm,
    renderCart
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = PaymentsPage;
}

// Make globally accessible for inline scripts
window.PaymentsPage = PaymentsPage;
