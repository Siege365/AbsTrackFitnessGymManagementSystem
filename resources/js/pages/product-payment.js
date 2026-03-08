// Read server-provided config from data attributes
const prodConfigEl = document.getElementById('productPaymentConfig');
let cartItems = [];
let inventoryItems = prodConfigEl ? JSON.parse(prodConfigEl.dataset.inventoryItems || '[]') : [];
const MEMBER_SEARCH_URL = prodConfigEl ? prodConfigEl.dataset.memberSearchUrl : '/members/search';
let selectedSearchItem = null;
let prodIsSubmitting = false;
const STORAGE_KEY = 'paymentFormState_v1';

// Save state
function saveProdState() {
  try {
    const state = {
      cartItems: cartItems,
      customer_name: document.getElementById('prodCustomerName')?.value || '',
      customer_id: document.getElementById('prodCustomerId')?.value || '',
      payment_method: document.getElementById('prodPaymentMethod')?.value || '',
      paid_amount: document.getElementById('prodPaidAmount')?.value || '',
      total_amount: document.getElementById('prodTotalAmount')?.value || ''
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
  } catch (e) {
    console.warn('Failed to save payment form state', e);
  }
}

// Load state
function loadProdState() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    const state = JSON.parse(raw);
    if (state) {
      cartItems = Array.isArray(state.cartItems) ? state.cartItems : [];
      if (document.getElementById('prodCustomerName')) document.getElementById('prodCustomerName').value = state.customer_name || '';
      if (document.getElementById('prodCustomerId')) document.getElementById('prodCustomerId').value = state.customer_id || '';

      if ((state.customer_id || state.customer_name) && window.fetch) {
        fetch(MEMBER_SEARCH_URL + '?q=' + encodeURIComponent(state.customer_name || ''))
          .then(r => r.json())
          .then(data => {
            const exists = Array.isArray(data) && data.some(m => String(m.id) === String(state.customer_id) || String(m.name).toLowerCase() === String((state.customer_name||'')).toLowerCase());
            if (!exists) {
              if (document.getElementById('prodCustomerName')) document.getElementById('prodCustomerName').value = '';
              if (document.getElementById('prodCustomerId')) document.getElementById('prodCustomerId').value = '';
              state.customer_name = '';
              state.customer_id = '';
              try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
            }
          })
          .catch(() => {});
      }
      if (document.getElementById('prodPaymentMethod')) document.getElementById('prodPaymentMethod').value = state.payment_method || '';
      if (document.getElementById('prodPaidAmount')) document.getElementById('prodPaidAmount').value = state.paid_amount || '';
      if (document.getElementById('prodTotalAmount')) document.getElementById('prodTotalAmount').value = state.total_amount || '';
      renderCart();
      calculateTotals();
    }
  } catch (e) {
    console.warn('Failed to load payment form state', e);
  }
}

function clearProdState() {
  try { localStorage.removeItem(STORAGE_KEY); } catch (e) { }
}

document.addEventListener('DOMContentLoaded', function() {
  // Wait for payment-system.js to load helper functions
  const checkPageActive = () => {
    return typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
  };
  
  loadProdState();
  initializeTransactionControls();
});

window.addEventListener('beforeunload', function() {
  if (!prodIsSubmitting) saveProdState();
});

// Search functionality
const prodSearchItem = document.getElementById('prodSearchItem');
if (prodSearchItem) {
  prodSearchItem.addEventListener('input', function(e) {
    // Guard: Only run if page is active
    const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
    if (!checkPageActive()) return;
    
    const searchTerm = e.target.value.toLowerCase();
    const searchResults = document.getElementById('prodSearchResults');
    
    // Null safety check
    if (!searchResults) {
      console.warn('Product search results container not found');
      return;
    }
    
    if (searchTerm.length < 2) {
      searchResults.classList.add('hidden');
      return;
    }
    
    // Filter inventory items safely
    const filtered = inventoryItems.filter(item => 
      item && item.product_name && item.product_number &&
      (item.product_name.toLowerCase().includes(searchTerm) ||
       item.product_number.toLowerCase().includes(searchTerm))
    );
    
    if (filtered.length > 0) {
      searchResults.innerHTML = filtered.map(item => `
        <div class="autocomplete-item" 
             data-id="${item.id || ''}" 
             data-name="${item.product_name || ''}" 
             data-price="${item.unit_price || 0}" 
             data-stock="${item.stock_qty || 0}">
          <strong>${item.product_name}</strong> - ₱${parseFloat(item.unit_price || 0).toFixed(2)} 
          <span class="text-stock-muted">(Stock: ${item.stock_qty || 0})</span>
        </div>
      `).join('');
      searchResults.classList.remove('hidden');
      
      searchResults.querySelectorAll('.autocomplete-item').forEach(item => {
        item.addEventListener('click', function() {
          // Guard: Check page is still active when clicking
          if (!checkPageActive()) return;
          
          const stock = parseInt(this.dataset.stock);
          const name = this.dataset.name;
          const id = this.dataset.id;
          const price = parseFloat(this.dataset.price);

          if (isNaN(stock) || stock <= 0) {
            ToastUtils.showWarning('Item out of stock');
            return;
          }

          selectedSearchItem = { id: id, name: name, price: price, stock: stock };
          const searchInput = document.getElementById('prodSearchItem');
          if (searchInput) searchInput.value = name;
          searchResults.classList.add('hidden');
        });
      });
    } else {
      searchResults.innerHTML = '<div class="autocomplete-item text-stock-muted">No items found</div>';
      searchResults.classList.remove('hidden');
    }
  });
}

function addItemToCart(item) {
  if (!item || typeof item.stock === 'undefined' || item.stock <= 0) {
    ToastUtils.showWarning('Insufficient stock available');
    return;
  }

  const existingItem = cartItems.find(i => i.id == item.id);

  if (existingItem) {
    if (existingItem.qty < item.stock) {
      existingItem.qty++;
    } else {
      ToastUtils.showWarning('Stock limit reached');
      return;
    }
  } else {
    cartItems.push({
      id: item.id,
      name: item.name,
      price: item.price,
      qty: 1,
      stock: item.stock
    });
  }
  
  renderCart();
  calculateTotals();
  saveProdState();
}

function renderCart() {
  const tbody = document.getElementById('prodItemsTableBody');
  
  if (cartItems.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding: 2rem; color: #666;">No items added</td></tr>';
    return;
  }
  
  tbody.innerHTML = cartItems.map((item, index) => `
    <tr>
      <td>${item.name}</td>
      <td>
        <input type="number" class="form-control form-control-sm" value="${item.qty}" 
               min="1" max="${item.stock}" onchange="updateQty(${index}, this.value)" style="width: 60px;">
      </td>
      <td>₱${item.price.toFixed(2)}</td>
      <td>₱${(item.price * item.qty).toFixed(2)}</td>
      <td>
        <button type="button" class="btn btn-sm btn-delete-item" onclick="removeItem(${index})">
          <i class="mdi mdi-delete"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function updateQty(index, newQty) {
  newQty = parseInt(newQty);
  if (newQty > 0 && newQty <= cartItems[index].stock) {
    cartItems[index].qty = newQty;
    renderCart();
    calculateTotals();
  } else {
    ToastUtils.showWarning('Invalid quantity entered');
    renderCart();
  }
  saveProdState();
}

function removeItem(index) {
  cartItems.splice(index, 1);
  renderCart();
  calculateTotals();
  saveProdState();
}

// Expose functions globally for onclick handlers
window.updateQty = updateQty;
window.removeItem = removeItem;

function calculateTotals() {
  const total = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
  document.getElementById('prodTotalAmount').value = total.toFixed(2);
  document.getElementById('prodTotalDisplay').value = total.toFixed(2);
  document.getElementById('prodItemCount').textContent = cartItems.reduce((sum, item) => sum + item.qty, 0);
  
  const paidAmount = parseFloat(document.getElementById('prodPaidAmount').value) || 0;
  const returnAmount = paidAmount - total;
  document.getElementById('prodReturnAmount').value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
}

document.getElementById('prodPaidAmount').addEventListener('input', calculateTotals);
document.getElementById('prodPaidAmount').addEventListener('input', saveProdState);

// Clear form button
const prodClearBtn = document.getElementById('prodClearBtn');
if (prodClearBtn) {
  prodClearBtn.addEventListener('click', function() {
    const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
    if (!checkPageActive()) return;
    
    cartItems = [];
    renderCart();
    const form = document.getElementById('prodPaymentForm');
    if (form) form.reset();
    const totalAmt = document.getElementById('prodTotalAmount');
    if (totalAmt) totalAmt.value = '';
    const totalDisplay = document.getElementById('prodTotalDisplay');
    if (totalDisplay) totalDisplay.value = '0.00';
    const returnAmt = document.getElementById('prodReturnAmount');
    if (returnAmt) returnAmt.value = '';
    const itemCount = document.getElementById('prodItemCount');
    if (itemCount) itemCount.textContent = '0';
    selectedSearchItem = null;
    clearProdState();
  });
}

const prodAddItemBtn = document.getElementById('prodAddItemBtn');
if (prodAddItemBtn) {
  prodAddItemBtn.addEventListener('click', function() {
    const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
    if (!checkPageActive()) return;
    
    if (!selectedSearchItem) {
      const searchInput = document.getElementById('prodSearchItem');
      const name = searchInput ? searchInput.value.trim() : '';
      if (!name) {
        ToastUtils.showWarning('Please select an item');
        return;
      }
      const found = inventoryItems.find(i => i && i.product_name && i.product_name.toLowerCase() === name.toLowerCase());
      if (!found) {
        ToastUtils.showWarning('Item not found in search results');
        return;
      }
      if (!found.stock_qty || found.stock_qty <= 0) {
        ToastUtils.showWarning('Item out of stock');
        return;
      }
      selectedSearchItem = { id: found.id, name: found.product_name, price: parseFloat(found.unit_price || 0), stock: found.stock_qty };
    }

    addItemToCart(selectedSearchItem);
    
    // Clear and reset button state
    const addBtn = document.getElementById('prodAddItemBtn');
    if (addBtn) {
      addBtn.blur(); // Remove focus to clear hover state
    }
    
    const searchEl = document.getElementById('prodSearchItem');
    if (searchEl) {
      searchEl.value = '';
      searchEl.focus();
    }
    const sr = document.getElementById('prodSearchResults');
    if (sr) sr.classList.add('hidden');
    selectedSearchItem = null;
  });
}

// Process Payment Button Handler
const prodProcessPaymentBtn = document.getElementById('prodProcessPaymentBtn');
if (prodProcessPaymentBtn) {
  prodProcessPaymentBtn.addEventListener('click', function(e) {
    e.preventDefault();
    const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
    if (!checkPageActive()) return;

    if (cartItems.length === 0) {
      ToastUtils.showWarning('Cart is empty');
      return;
    }

    const totalEl = document.getElementById('prodTotalAmount');
    const paidEl = document.getElementById('prodPaidAmount');
    if (!totalEl || !paidEl) return;
    
    const total = parseFloat(totalEl.value) || 0;
    const paid = parseFloat(paidEl.value) || 0;
    if (paid < total) {
      ToastUtils.showError('Insufficient payment amount');
      if (paidEl) paidEl.focus();
      return;
    }

    // Prepare items data
    const itemsDataEl = document.getElementById('prodItemsData');
    if (itemsDataEl) {
      itemsDataEl.value = JSON.stringify(cartItems);
    }

    // Build confirmation details
    const itemsHtml = cartItems.map(i => `
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">${i.name} x ${i.qty}</span>
        <span class="confirm-detail-value">₱${(i.price * i.qty).toFixed(2)}</span>
      </div>
    `).join('');

    const customerName = document.getElementById('prodCustomerName')?.value || 'Walk-in Customer';
    const payMethod = document.getElementById('prodPaymentMethod')?.value || 'Cash';

    const details = `
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Customer:</span>
        <span class="confirm-detail-value">${customerName}</span>
      </div>
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Payment Type:</span>
        <span class="confirm-detail-value">PRODUCT</span>
      </div>
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Items:</span>
        <span class="confirm-detail-value">${cartItems.length} item(s)</span>
      </div>
      ${itemsHtml}
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Payment Method:</span>
        <span class="confirm-detail-value">${payMethod}</span>
      </div>
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Total Amount:</span>
        <span class="confirm-detail-value">₱${total.toFixed(2)}</span>
      </div>
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Paid Amount:</span>
        <span class="confirm-detail-value">₱${paid.toFixed(2)}</span>
      </div>
      <div class="confirm-detail-row">
        <span class="confirm-detail-label">Change:</span>
        <span class="confirm-detail-value">₱${(paid - total).toFixed(2)}</span>
      </div>
    `;

    const detailsEl = document.getElementById('prodConfirmationDetails');
    const modalEl = document.getElementById('prodConfirmationModal');
    if (detailsEl) detailsEl.innerHTML = details;
    if (modalEl) modalEl.classList.add('show');
  });
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
  const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
  if (!checkPageActive()) return;
  
  const searchResults = document.getElementById('prodSearchResults');
  if (searchResults && !e.target.closest('#prodSearchItem') && !e.target.closest('#prodSearchResults')) {
    searchResults.classList.add('hidden');
  }
  const customerResults = document.getElementById('prodCustomerResults');
  if (customerResults && !e.target.closest('#prodCustomerName') && !e.target.closest('#prodCustomerResults')) {
    customerResults.classList.add('hidden');
  }
});

// Customer autocomplete
(function() {
  const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
  const input = document.getElementById('prodCustomerName');
  const resultsEl = document.getElementById('prodCustomerResults');
  const customerIdEl = document.getElementById('prodCustomerId');
  let debounceTimer;

  if (!input) return;

  input.addEventListener('input', function(e) {
    if (!checkPageActive()) {
      clearTimeout(debounceTimer);
      return;
    }
    
    const q = this.value.trim();
    if (customerIdEl) customerIdEl.value = '';

    clearTimeout(debounceTimer);
    if (q.length < 1) {
      if (resultsEl) resultsEl.classList.add('hidden');
      return;
    }

    debounceTimer = setTimeout(() => {
      if (!checkPageActive()) return;
      
      fetch(MEMBER_SEARCH_URL + '?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          if (!checkPageActive()) return;
          if (!resultsEl) return;
          
          if (!Array.isArray(data) || data.length === 0) {
            resultsEl.innerHTML = '<div class="autocomplete-item text-stock-muted">No members found</div>';
            resultsEl.classList.remove('hidden');
            return;
          }

          const validData = data.filter(m => m && m.id && m.name);
          resultsEl.innerHTML = validData.map(m => `
            <div class="autocomplete-item" data-id="${m.id}" data-name="${m.name}">
              <strong>${m.name}</strong> <span class="text-stock-muted">${m.contact || ''}</span>
            </div>
          `).join('');
          resultsEl.classList.remove('hidden');

          resultsEl.querySelectorAll('.autocomplete-item').forEach(el => {
            el.addEventListener('click', function() {
              if (!checkPageActive()) return;
              if (input) input.value = this.dataset.name;
              if (customerIdEl) customerIdEl.value = this.dataset.id;
              if (resultsEl) resultsEl.classList.add('hidden');
            });
          });
        })
        .catch(err => {
          console.error('Member search error', err);
          if (resultsEl) resultsEl.classList.add('hidden');
        });
    }, 250);
  });
})();

// Transaction History Controls
function initializeTransactionControls() {
  const selectAllCheckbox = document.getElementById('selectAllTransactions');
  const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      transactionCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
      updateBulkDeleteButton();
    });
  }

  transactionCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      updateBulkDeleteButton();
      const allChecked = Array.from(transactionCheckboxes).every(cb => cb.checked);
      const someChecked = Array.from(transactionCheckboxes).some(cb => cb.checked);
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
      }
    });
  });

  function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
    const selectedCount = checkedBoxes.length;
    const countSpan = document.getElementById('selectedCount');
    if (countSpan) countSpan.textContent = selectedCount;
    if (bulkDeleteBtn) {
      if (selectedCount > 0) { bulkDeleteBtn.disabled = false; bulkDeleteBtn.classList.remove('disabled'); }
      else { bulkDeleteBtn.disabled = true; bulkDeleteBtn.classList.add('disabled'); }
    }
  }

  if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function() {
      const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
      if (!checkPageActive()) return;
      
      const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
      const ids = Array.from(checkedBoxes).map(cb => cb.value);
      if (ids.length === 0) { ToastUtils.showWarning('No items selected for deletion'); return; }
      if (confirm(`Are you sure you want to delete ${ids.length} transaction(s)? This action cannot be undone.`)) {
        const idsEl = document.getElementById('bulkDeleteIds');
        const formEl = document.getElementById('bulkDeleteForm');
        if (idsEl) idsEl.value = JSON.stringify(ids);
        if (formEl) formEl.submit();
      }
    });
  }

  document.querySelectorAll('.delete-form-payment').forEach(form => {
    form.addEventListener('submit', function(e) {
      const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
      if (!checkPageActive()) {
        e.preventDefault();
        return;
      }
      
      e.preventDefault();
      if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
        this.submit();
      }
    });
  });
}

function closeProdConfirmation() {
  const m = document.getElementById('prodConfirmationModal');
  if (m) m.classList.remove('show');
}

function confirmProductPayment() {
  const btn = document.getElementById('prodConfirmPaymentBtn');
  if (!btn) return;
  
  btn.disabled = true;
  const originalText = btn.innerHTML;
  btn.innerHTML = '<span class="loading-spinner"></span> Processing...';

  const form = document.getElementById('prodPaymentForm');
  if (!form) return;
  const fd = new FormData(form);

  fetch(form.action, {
    method: 'POST',
    body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeProdConfirmation();
      setTimeout(() => { loadProductReceiptModal(data.payment.id); }, 300);
      clearProdFormData();
    } else {
      ToastUtils.showError(data.message || 'Payment processing failed');
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  })
  .catch(err => {
    console.error('Product payment error', err);
    ToastUtils.showError('Payment processing failed');
    btn.disabled = false;
    btn.innerHTML = originalText;
  });
}

// Attach confirm handler to button
const prodConfirmPaymentBtn = document.getElementById('prodConfirmPaymentBtn');
if (prodConfirmPaymentBtn) {
  prodConfirmPaymentBtn.addEventListener('click', function() {
    const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
    if (!checkPageActive()) return;
    confirmProductPayment();
  });
}

function clearProdFormData() {
  cartItems = [];
  renderCart();
  document.getElementById('prodPaymentForm').reset();
  document.getElementById('prodTotalAmount').value = '';
  document.getElementById('prodTotalDisplay').value = '0.00';
  document.getElementById('prodReturnAmount').value = '';
  document.getElementById('prodItemCount').textContent = '0';
  selectedSearchItem = null;
  clearProdState();
}

// Receipt Modal Functions
function loadProductReceiptModal(paymentId) {
  const modal = document.getElementById('productReceiptModal');
  const modalBody = document.getElementById('productReceiptBody');
  
  modal.classList.add('show');
  modalBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
  
  fetch(`/payments/${paymentId}/receipt-data`)
    .then(response => response.json())
    .then(data => {
      modalBody.innerHTML = generateProductReceiptHTML(data);
    })
    .catch(error => {
      console.error('Error loading receipt:', error);
      modalBody.innerHTML = `
        <div class="receipt-error-state">
          <i class="mdi mdi-alert-circle"></i>
          <p>Failed to load receipt. Please try again.</p>
        </div>
      `;
    });
}

function generateProductReceiptHTML(payment) {
  const itemsHTML = payment.items.map(item => `
    <tr>
      <td>${item.product_name}</td>
      <td style="text-align: center;">${item.quantity}</td>
      <td style="text-align: right;">₱${parseFloat(item.unit_price).toFixed(2)}</td>
      <td style="text-align: right;">₱${parseFloat(item.subtotal).toFixed(2)}</td>
    </tr>
  `).join('');

  return `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>RECEIPT</h2>
        <p>Abstrack Fitness Gym</p>
        <p>Toril, Davao Del Sur</p>
        <p>Phone: (123) 456-7890</p>
      </div>

      <div class="receipt-info">
        <div class="receipt-info-item">
          <strong>Receipt Number</strong>
          <span>#${payment.receipt_number}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Date & Time</strong>
          <span>${payment.formatted_date}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Customer Name</strong>
          <span>${payment.customer_name}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Cashier</strong>
          <span>${payment.cashier_name}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Payment Method</strong>
          <span>${payment.payment_method}</span>
        </div>
      </div>

      <table class="receipt-table">
        <thead>
          <tr>
            <th>Item</th>
            <th style="text-align: center;">Quantity</th>
            <th style="text-align: right;">Unit Price</th>
            <th style="text-align: right;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          ${itemsHTML}
        </tbody>
      </table>

      <div class="receipt-total">
        <div class="receipt-total-row">
          <strong>Subtotal:</strong>
          <span>₱${parseFloat(payment.total_amount).toFixed(2)}</span>
        </div>
        <div class="receipt-total-row grand-total">
          <strong>Total:</strong>
          <span>₱${parseFloat(payment.total_amount).toFixed(2)}</span>
        </div>
        <div class="receipt-total-row receipt-paid-section">
          <strong>Paid Amount:</strong>
          <span>₱${parseFloat(payment.paid_amount).toFixed(2)}</span>
        </div>
        <div class="receipt-total-row">
          <strong>Change:</strong>
          <span>₱${parseFloat(payment.return_amount).toFixed(2)}</span>
        </div>
      </div>

      <div class="receipt-footer">
        <p>Thank you for your purchase!</p>
        <p class="receipt-footer-sub">Please come again!</p>
      </div>
    </div>
  `;
}

function closeProductReceiptModal() {
  const modal = document.getElementById('productReceiptModal');
  modal.classList.remove('show');
  setTimeout(() => { window.location.reload(); }, 300);
}

function printProductReceipt() {
  window.print();
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
  const checkPageActive = () => typeof window.isPageActive === 'function' ? window.isPageActive('product') : true;
  if (!checkPageActive()) return;
  
  const confirmModal = document.getElementById('prodConfirmationModal');
  const receiptModal = document.getElementById('productReceiptModal');
  if (event.target === confirmModal) closeProdConfirmation();
  if (event.target === receiptModal) closeProductReceiptModal();
});

// Product Refund Modal Functions
let currentProductRefundId = null;
function openProductRefundModal(id, receiptNumber, amount, customerName) {
  currentProductRefundId = id;
  if (!document.getElementById('productRefundModal')) {
    const html = `
    <div id="productRefundModal" class="modal-overlay">
      <div class="confirm-overlay-content">
        <div class="confirm-overlay-header">
          <i class="mdi mdi-cash-refund refund-warning-icon"></i>
          <h5>Process Refund</h5>
          <button type="button" class="close" onclick="closeProductRefundModal()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="confirm-overlay-body">
          <div class="refund-warning-box">
            <i class="mdi mdi-alert refund-warning-icon"></i>
            <strong>Warning:</strong> This will mark this transaction as refunded.
          </div>
          <div class="confirm-details" id="productRefundDetails"></div>
          <div class="refund-reason-section">
            <label class="form-label form-label-sm">Refund Reason (Optional)</label>
            <textarea class="form-control" id="productRefundReason" rows="3" placeholder="Enter reason for refund..."></textarea>
          </div>
        </div>
        <div class="confirm-overlay-footer">
          <button type="button" class="btn btn-cancel" onclick="closeProductRefundModal()">Cancel</button>
          <button type="button" class="btn btn-update btn-refund" onclick="confirmProductRefund()">Process Refund</button>
        </div>
      </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
  }

  document.getElementById('productRefundDetails').innerHTML = `
    <div class="confirm-detail-row"><span class="confirm-detail-label">Receipt:</span><span class="confirm-detail-value">#${receiptNumber}</span></div>
    <div class="confirm-detail-row"><span class="confirm-detail-label">Customer:</span><span class="confirm-detail-value">${customerName}</span></div>
    <div class="confirm-detail-row"><span class="confirm-detail-label">Refund Amount:</span><span class="confirm-detail-value refund-amount">₱${parseFloat(amount).toFixed(2)}</span></div>`;

  document.getElementById('productRefundModal').classList.add('show');
}

function closeProductRefundModal() {
  const m = document.getElementById('productRefundModal');
  if (m) m.classList.remove('show');
}

function confirmProductRefund() {
  if (!currentProductRefundId) return;
  const reason = document.getElementById('productRefundReason').value || '';
  const url = `/payments/${currentProductRefundId}/refund`;
  const fd = new FormData();
  fd.append('reason', reason);

  fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        ToastUtils.showSuccess(data.message || 'Refund completed');
        closeProductRefundModal();
        setTimeout(() => window.location.reload(), 500);
      } else {
        ToastUtils.showError(data.message || 'Refund processing failed');
      }
    })
    .catch(err => { console.error(err); ToastUtils.showError('Refund processing failed'); });
}
