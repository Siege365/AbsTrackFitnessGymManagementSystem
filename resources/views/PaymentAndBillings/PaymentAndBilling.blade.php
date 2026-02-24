<!-- ====== PRODUCT PAYMENT PAGE ====== -->
<div class="page-panel" id="productPage">
  <div class="card">
    <div class="card-body">

      <!-- Section Header -->
      <div class="section-header">
        <h2 class="card-title">Process Product Payment</h2>
      </div>

      <form action="{{ route('payments.store') }}" method="POST" id="productPaymentForm">
        @csrf

        <!-- Customer & Item Search Section -->
        <div class="product-form-section">
          <div class="product-form-row">
            <div class="product-form-col product-form-col-2">
              <label for="productCustomerName" class="form-label">Customer Name</label>
              <div style="position: relative;">
                <input type="text" class="form-control" id="productCustomerName" name="customer_name" placeholder="Search customer or enter name..." autocomplete="off" required>
                <input type="hidden" id="productCustomerId" name="customer_id">
                <div id="productCustomerResults" class="autocomplete-results" style="display:none; position: absolute; width: 100%; z-index: 9999;"></div>
              </div>
            </div>
            <div class="product-form-col product-form-col-2">
              <label class="form-label">Search & Add Items</label>
              <div class="product-search-wrapper">
                <div class="product-search-input-group">
                  <input type="text" id="productSearchItem" class="form-control" placeholder="Search by product name or number..." autocomplete="off">
                  <button type="button" class="btn btn-primary product-add-btn" id="productAddItemBtn">
                    <i class="mdi mdi-plus"></i> Add
                  </button>
                </div>
                <div id="productSearchResults" class="autocomplete-results" style="display: none; z-index: 9999;"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart / Items Table -->
        <div class="product-form-section">
          <div class="product-cart-header">
            <h4 class="product-cart-title"><i class="mdi mdi-cart-outline"></i> Cart Items</h4>
            <span class="product-cart-badge" id="productCartCount">0 items</span>
          </div>
          <div class="table-responsive product-table-wrapper">
            <table class="product-items-table" id="productItemsTable">
              <thead>
                <tr>
                  <th style="width: 28%; text-align: center;">Product</th>
                  <th style="width: 18%; text-align: center;">Quantity</th>
                  <th style="width: 18%; text-align: right;">Unit Price</th>
                  <th style="width: 18%; text-align: right;">Subtotal</th>
                  <th style="width: 18%; text-align: center;">Remove</th>
                </tr>
              </thead>
              <tbody id="productItemsTableBody">
                <tr class="product-empty-row">
                  <td colspan="5">
                    <div class="product-empty-cart">
                      <i class="mdi mdi-cart-off"></i>
                      <p>No items added yet</p>
                      <span>Search and add products above</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <input type="hidden" id="productItemsData" name="items_data">

        <!-- Payment Summary Section -->
        <div class="product-form-section">
          <div class="product-payment-summary">
            <div class="product-form-row">
              <div class="product-form-col product-form-col-3">
                <label for="productPaymentMethod" class="form-label">Payment Method</label>
                <select class="form-select" id="productPaymentMethod" name="payment_method" required>
                  <option value="Cash">Cash</option>
                  <option value="Credit Card">Credit Card</option>
                  <option value="Debit Card">Debit Card</option>
                  <option value="GCash">GCash</option>
                  <option value="PayMaya">PayMaya</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                </select>
              </div>
              <div class="product-form-col product-form-col-3">
                <label for="productPaidAmount" class="form-label">Paid Amount</label>
                <input type="number" class="form-control" id="productPaidAmount" name="paid_amount" placeholder="₱0.00" step="0.01">
              </div>
              <div class="product-form-col product-form-col-3">
                <label for="productTotalAmount" class="form-label">Total Amount</label>
                <input type="number" class="form-control" id="productTotalAmount" name="total_amount" readonly placeholder="₱0.00">
              </div>
              <div class="product-form-col product-form-col-3"></div>
              <div class="product-form-col product-form-col-3"></div>
              <div class="product-form-col product-form-col-3">
                <label class="form-label">Change</label>
                <input type="number" class="form-control product-change-field" id="productReturnAmount" readonly placeholder="₱0.00">
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
          <button type="button" class="btn btn-secondary" id="productClearBtn">
            <i class="mdi mdi-close"></i> Clear
          </button>
          <button type="button" class="btn btn-primary" id="productProcessPaymentBtn">
            <i class="mdi mdi-check"></i> Process Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div><!-- /productPage -->

<!-- Product Payment Confirmation Modal -->
<div id="productConfirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-check-circle-outline"></i>
      <h5>Confirm Payment</h5>
      <button type="button" class="close" onclick="closeProductConfirmation()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the payment details before proceeding.</p>
      <div class="confirm-details" id="productConfirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeProductConfirmation()">Cancel</button>
      <button type="button" class="btn btn-update" id="confirmProductPaymentBtn">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Product Receipt Modal -->
<div id="productReceiptModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Receipt Details</h3>
      <button class="modal-close" onclick="closeProductReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="productReceiptModalBody">
      <div class="loading-spinner"><div class="spinner"></div></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeProductReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printProductReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>
</div>
@push('scripts')
<script>
// ========================================
// PRODUCT PAYMENT LOGIC
// ========================================
(function() {
  let cartItems = [];
  let inventoryItems = @json($inventoryItems ?? []);
  let selectedSearchItem = null;
  let isProductSubmitting = false;
  const STORAGE_KEY = 'paymentFormState_v1';

  function saveState() {
    try {
      const state = {
        cartItems: cartItems,
        customer_name: document.getElementById('productCustomerName')?.value || '',
        customer_id: document.getElementById('productCustomerId')?.value || '',
        payment_method: document.getElementById('productPaymentMethod')?.value || '',
        paid_amount: document.getElementById('productPaidAmount')?.value || '',
        total_amount: document.getElementById('productTotalAmount')?.value || ''
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
      console.warn('Failed to save payment form state', e);
    }
  }

  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const state = JSON.parse(raw);
      if (state) {
        cartItems = Array.isArray(state.cartItems) ? state.cartItems : [];
        if (document.getElementById('productCustomerName')) document.getElementById('productCustomerName').value = state.customer_name || '';
        if (document.getElementById('productCustomerId')) document.getElementById('productCustomerId').value = state.customer_id || '';

        if ((state.customer_id || state.customer_name) && window.fetch) {
          fetch('{{ url("/members/search") }}?q=' + encodeURIComponent(state.customer_name || ''))
            .then(r => r.json())
            .then(data => {
              const exists = Array.isArray(data) && data.some(m => String(m.id) === String(state.customer_id) || String(m.name).toLowerCase() === String((state.customer_name||'')).toLowerCase());
              if (!exists) {
                if (document.getElementById('productCustomerName')) document.getElementById('productCustomerName').value = '';
                if (document.getElementById('productCustomerId')) document.getElementById('productCustomerId').value = '';
                state.customer_name = '';
                state.customer_id = '';
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
              }
            })
            .catch(() => {});
        }
        if (document.getElementById('productPaymentMethod')) document.getElementById('productPaymentMethod').value = state.payment_method || '';
        if (document.getElementById('productPaidAmount')) document.getElementById('productPaidAmount').value = state.paid_amount || '';
        if (document.getElementById('productTotalAmount')) document.getElementById('productTotalAmount').value = state.total_amount || '';
        renderCart();
        calculateTotals();
      }
    } catch (e) {
      console.warn('Failed to load payment form state', e);
    }
  }

  function clearState() {
    try { localStorage.removeItem(STORAGE_KEY); } catch (e) { }
  }

  loadState();

  window.addEventListener('beforeunload', function() {
    if (!isProductSubmitting) saveState();
  });

  console.log('Inventory Items Loaded:', inventoryItems.length);

  function updateCartBadge() {
    const badge = document.getElementById('productCartCount');
    const total = cartItems.reduce((sum, i) => sum + i.qty, 0);
    badge.textContent = total + (total === 1 ? ' item' : ' items');
  }

  // Search functionality
  document.getElementById('productSearchItem').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const searchResults = document.getElementById('productSearchResults');

    if (searchTerm.length < 2) {
      searchResults.style.display = 'none';
      return;
    }

    const filtered = inventoryItems.filter(item =>
      item.product_name.toLowerCase().includes(searchTerm) ||
      item.product_number.toLowerCase().includes(searchTerm)
    );

    if (filtered.length > 0) {
      searchResults.innerHTML = filtered.map(item => {
        const outOfStock = item.stock_qty <= 0;
        return '<div class="autocomplete-item product-search-result-item ' + (outOfStock ? 'out-of-stock' : '') + '" data-id="' + item.id + '" data-name="' + item.product_name + '" data-price="' + item.unit_price + '" data-stock="' + item.stock_qty + '">' +
          '<div class="product-search-item-info"><strong>' + item.product_name + '</strong><span class="product-search-item-price">' + String.fromCharCode(8369) + parseFloat(item.unit_price).toFixed(2) + '</span></div>' +
          '<div class="product-search-item-meta"><span class="product-search-item-code">' + item.product_number + '</span>' +
          '<span class="product-search-item-stock ' + (outOfStock ? 'out' : '') + '">' + (outOfStock ? '<i class="mdi mdi-close-circle"></i> Out of stock' : '<i class="mdi mdi-check-circle"></i> Stock: ' + item.stock_qty) + '</span></div></div>';
      }).join('');
      searchResults.style.display = 'block';

      searchResults.querySelectorAll('.product-search-result-item').forEach(item => {
        item.addEventListener('click', function() {
          const stock = parseInt(this.dataset.stock);
          if (isNaN(stock) || stock <= 0) {
            ToastUtils.showWarning('This item is out of stock and cannot be selected.');
            return;
          }
          selectedSearchItem = { id: this.dataset.id, name: this.dataset.name, price: parseFloat(this.dataset.price), stock: stock };
          document.getElementById('productSearchItem').value = this.dataset.name;
          searchResults.style.display = 'none';
        });
      });
    } else {
      searchResults.innerHTML = '<div class="autocomplete-item" style="color: #999;">No items found</div>';
      searchResults.style.display = 'block';
    }
  });

  document.getElementById('productSearchItem').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('productAddItemBtn').click(); }
  });

  function addItemToCart(item) {
    if (!item || typeof item.stock === 'undefined' || item.stock <= 0) {
      ToastUtils.showError('Cannot add item \u2014 Insufficient stock.');
      return;
    }
    const existingItem = cartItems.find(i => i.id == item.id);
    if (existingItem) {
      if (existingItem.qty < item.stock) { existingItem.qty++; }
      else { ToastUtils.showWarning('Cannot add more. Insufficient stock!'); return; }
    } else {
      cartItems.push({ id: item.id, name: item.name, price: item.price, qty: 1, stock: item.stock });
    }
    renderCart();
    calculateTotals();
    saveState();
  }

  function renderCart() {
    const tbody = document.getElementById('productItemsTableBody');
    if (cartItems.length === 0) {
      tbody.innerHTML = '<tr class="product-empty-row"><td colspan="5"><div class="product-empty-cart"><i class="mdi mdi-cart-off"></i><p>No items added yet</p><span>Search and add products above</span></div></td></tr>';
      updateCartBadge();
      return;
    }
    tbody.innerHTML = cartItems.map((item, index) =>
      '<tr class="product-cart-row">' +
        '<td style="text-align: center;"><div class="product-item-name">' + item.name + '</div><div class="product-item-stock">Available: ' + item.stock + '</div></td>' +
        '<td style="text-align: center;"><input type="number" class="form-control form-control-sm product-qty-input" value="' + item.qty + '" min="1" max="' + item.stock + '" onchange="window._productUpdateQty(' + index + ', this.value)"></td>' +
        '<td style="text-align: right;">' + String.fromCharCode(8369) + item.price.toFixed(2) + '</td>' +
        '<td style="text-align: right; font-weight: 600;">' + String.fromCharCode(8369) + (item.price * item.qty).toFixed(2) + '</td>' +
        '<td style="text-align: center;"><button type="button" class="btn btn-sm product-remove-btn" onclick="window._productRemoveItem(' + index + ')" title="Remove item"><i class="mdi mdi-close"></i></button></td>' +
      '</tr>'
    ).join('');
    updateCartBadge();
  }

  window._productUpdateQty = function(index, newQty) {
    newQty = parseInt(newQty);
    if (newQty > 0 && newQty <= cartItems[index].stock) {
      cartItems[index].qty = newQty;
      renderCart();
      calculateTotals();
    } else {
      ToastUtils.showWarning('Invalid quantity or insufficient stock!');
      renderCart();
    }
    saveState();
  };

  window._productRemoveItem = function(index) {
    const itemName = cartItems[index].name;
    cartItems.splice(index, 1);
    renderCart();
    calculateTotals();
    saveState();
    ToastUtils.showInfo('"' + itemName + '" removed from cart.');
  };

  function calculateTotals() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    document.getElementById('productTotalAmount').value = total.toFixed(2);
    const paidAmount = parseFloat(document.getElementById('productPaidAmount').value) || 0;
    const returnAmount = paidAmount - total;
    document.getElementById('productReturnAmount').value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
  }

  document.getElementById('productPaidAmount').addEventListener('input', function() {
    calculateTotals();
    saveState();
  });

  document.getElementById('productClearBtn').addEventListener('click', function() {
    cartItems = [];
    renderCart();
    document.getElementById('productPaymentForm').reset();
    document.getElementById('productTotalAmount').value = '';
    document.getElementById('productReturnAmount').value = '';
    selectedSearchItem = null;
    isProductSubmitting = false;
    clearState();
    const btn = document.getElementById('productProcessPaymentBtn');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="mdi mdi-check"></i> Process Payment'; }
  });

  document.getElementById('productAddItemBtn').addEventListener('click', function() {
    if (!selectedSearchItem) {
      const name = document.getElementById('productSearchItem').value.trim();
      if (!name) { ToastUtils.showWarning('Please select an item first from the search results.'); return; }
      const found = inventoryItems.find(i => i.product_name.toLowerCase() === name.toLowerCase());
      if (!found) { ToastUtils.showError('Selected item not found. Please choose from the search results.'); return; }
      if (found.stock_qty <= 0) { ToastUtils.showError('This item is out of stock and cannot be added.'); return; }
      selectedSearchItem = { id: found.id, name: found.product_name, price: parseFloat(found.unit_price), stock: found.stock_qty };
    }
    addItemToCart(selectedSearchItem);
    const searchEl = document.getElementById('productSearchItem');
    if (searchEl) { searchEl.value = ''; searchEl.focus(); }
    const sr = document.getElementById('productSearchResults');
    if (sr) sr.style.display = 'none';
    selectedSearchItem = null;
  });

  // Process Payment
  document.getElementById('productProcessPaymentBtn').addEventListener('click', function(e) {
    e.preventDefault();
    if (isProductSubmitting) return;
    if (cartItems.length === 0) { ToastUtils.showWarning('Please add at least one item to the cart!'); return; }

    const total = parseFloat(document.getElementById('productTotalAmount').value) || 0;
    const paid = parseFloat(document.getElementById('productPaidAmount').value) || 0;
    if (paid < total) {
      ToastUtils.showError('Payment incomplete: Paid amount must be equal to or greater than the total amount.');
      document.getElementById('productPaidAmount').focus();
      return;
    }

    document.getElementById('productItemsData').value = JSON.stringify(cartItems);

    const itemsHtml = cartItems.map(i =>
      '<div class="confirm-row"><span class="confirm-label">' + i.name + ' \u00D7 ' + i.qty + '</span><span class="confirm-value">' + String.fromCharCode(8369) + (i.price * i.qty).toFixed(2) + '</span></div>'
    ).join('');

    const customerName = document.getElementById('productCustomerName')?.value || 'Walk-in Customer';
    const paymentMethod = document.getElementById('productPaymentMethod')?.value || 'Cash';

    document.getElementById('productConfirmationDetails').innerHTML =
      '<div class="confirm-row"><span class="confirm-label">Customer:</span><span class="confirm-value">' + customerName + '</span></div>' +
      '<div class="confirm-row"><span class="confirm-label">Payment Type:</span><span class="confirm-value">PRODUCT</span></div>' +
      '<div class="confirm-row"><span class="confirm-label" style="font-weight:600;">Items (' + cartItems.length + '):</span><span></span></div>' +
      itemsHtml +
      '<div class="confirm-row"><span class="confirm-label">Payment Method:</span><span class="confirm-value">' + paymentMethod + '</span></div>' +
      '<div class="confirm-row"><span class="confirm-label">Total Amount:</span><span class="confirm-value" style="font-weight:700;">' + String.fromCharCode(8369) + total.toFixed(2) + '</span></div>' +
      '<div class="confirm-row"><span class="confirm-label">Paid Amount:</span><span class="confirm-value">' + String.fromCharCode(8369) + paid.toFixed(2) + '</span></div>' +
      '<div class="confirm-row"><span class="confirm-label">Change:</span><span class="confirm-value" style="color: #28a745;">' + String.fromCharCode(8369) + (paid - total).toFixed(2) + '</span></div>';
    document.getElementById('productConfirmationModal').classList.add('show');
  });

  // Close search results on outside click
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#productSearchItem') && !e.target.closest('#productSearchResults')) {
      document.getElementById('productSearchResults').style.display = 'none';
    }
    if (!e.target.closest('#productCustomerName') && !e.target.closest('#productCustomerResults')) {
      var cr = document.getElementById('productCustomerResults');
      if (cr) cr.style.display = 'none';
    }
  });

  // Customer autocomplete
  (function() {
    var input = document.getElementById('productCustomerName');
    var resultsEl = document.getElementById('productCustomerResults');
    var customerIdEl = document.getElementById('productCustomerId');
    var debounceTimer;
    if (!input) return;

    input.addEventListener('input', function() {
      var q = this.value.trim();
      customerIdEl.value = '';
      clearTimeout(debounceTimer);
      if (q.length < 1) { resultsEl.style.display = 'none'; return; }

      debounceTimer = setTimeout(function() {
        fetch('{{ url("/members/search") }}?q=' + encodeURIComponent(q))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (!Array.isArray(data) || data.length === 0) {
              resultsEl.innerHTML = '<div class="autocomplete-item" style="color: #999;">No members found</div>';
              resultsEl.style.display = 'block';
              return;
            }
            resultsEl.innerHTML = data.map(function(m) {
              return '<div class="autocomplete-item" data-id="' + m.id + '" data-name="' + m.name + '"><strong>' + m.name + '</strong> <span style="color: #999; font-size: 0.85rem;">' + (m.contact || '') + '</span></div>';
            }).join('');
            resultsEl.style.display = 'block';
            resultsEl.querySelectorAll('.autocomplete-item').forEach(function(el) {
              el.addEventListener('click', function() {
                input.value = this.dataset.name;
                customerIdEl.value = this.dataset.id;
                resultsEl.style.display = 'none';
              });
            });
          })
          .catch(function(err) { console.error('Member search error', err); resultsEl.style.display = 'none'; });
      }, 250);
    });
  })();

  window.closeProductConfirmation = function() {
    var m = document.getElementById('productConfirmationModal');
    if (m) m.classList.remove('show');
  };

  function confirmProductPayment() {
    if (isProductSubmitting) return;
    isProductSubmitting = true;

    var btn = document.getElementById('confirmProductPaymentBtn');
    if (!btn) return;
    btn.disabled = true;
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';

    var form = document.getElementById('productPaymentForm');
    if (!form) return;
    var fd = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        closeProductConfirmation();
        setTimeout(function() { loadProductReceiptModal(data.payment.id); }, 300);
        clearFormData();
      } else {
        ToastUtils.showError(data.message || 'Failed to process payment');
        isProductSubmitting = false;
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    })
    .catch(function(err) {
      console.error('Product payment error', err);
      ToastUtils.showError('Failed to process payment. Please try again.');
      isProductSubmitting = false;
      btn.disabled = false;
      btn.innerHTML = originalText;
    });
  }

  document.getElementById('confirmProductPaymentBtn').addEventListener('click', confirmProductPayment);

  function clearFormData() {
    cartItems = [];
    renderCart();
    document.getElementById('productPaymentForm').reset();
    document.getElementById('productTotalAmount').value = '';
    document.getElementById('productReturnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  }

  function loadProductReceiptModal(paymentId) {
    var modal = document.getElementById('productReceiptModal');
    var modalBody = document.getElementById('productReceiptModalBody');
    modal.classList.add('show');
    modalBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';

    fetch('/payments/' + paymentId + '/receipt-data')
      .then(function(response) { return response.json(); })
      .then(function(data) {
        modalBody.innerHTML = generateProductReceiptHTML(data);
        ToastUtils.showSuccess('Payment processed successfully!');
      })
      .catch(function(error) {
        console.error('Error loading receipt:', error);
        ToastUtils.showError('Failed to load receipt.');
        modalBody.innerHTML = '<div style="padding:2rem;color:#dc3545;text-align:center;"><i class="mdi mdi-alert-circle" style="font-size:48px;"></i><p>Failed to load receipt.</p></div>';
      });
  }

  function generateProductReceiptHTML(data) {
    var items = data.items || [];
    var itemsHTML = items.map(function(item) {
      return '<tr><td>' + item.product_name + '</td><td style="text-align: center;">' + item.quantity + '</td><td style="text-align: right;">' + String.fromCharCode(8369) + parseFloat(item.unit_price).toFixed(2) + '</td><td style="text-align: right;">' + String.fromCharCode(8369) + parseFloat(item.subtotal || item.total_price || (item.unit_price * item.quantity)).toFixed(2) + '</td></tr>';
    }).join('');

    return '<div class="receipt-container">' +
      '<div class="receipt-header"><h2>PRODUCT PAYMENT RECEIPT</h2><p><strong>Abstrack Fitness Gym</strong></p><p>Toril, Davao Del Sur</p></div>' +
      '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">' +
      '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong><span style="display: block; font-weight: 600;">#' + data.receipt_number + '</span></div>' +
      '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong><span style="display: block; font-weight: 600;">' + (data.formatted_date || new Date(data.created_at).toLocaleString()) + '</span></div>' +
      '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Customer Name</strong><span style="display: block; font-weight: 600;">' + (data.customer_name || 'N/A') + '</span></div>' +
      '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Cashier</strong><span style="display: block; font-weight: 600;">' + (data.cashier_name || '') + '</span></div>' +
      '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong><span style="display: block; font-weight: 600;">' + (data.payment_method || 'N/A') + '</span></div>' +
      '</div>' +
      '<table class="receipt-table"><thead><tr><th>Item</th><th style="text-align: center;">Qty</th><th style="text-align: right;">Unit Price</th><th style="text-align: right;">Subtotal</th></tr></thead><tbody>' + itemsHTML + '</tbody></table>' +
      '<div class="receipt-total">' +
      '<div class="receipt-row" style="font-size: 1.3rem;"><span><strong>Total:</strong></span><span><strong>' + String.fromCharCode(8369) + parseFloat(data.total_amount || 0).toFixed(2) + '</strong></span></div>' +
      '<div class="receipt-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;"><span>Paid Amount:</span><span>' + String.fromCharCode(8369) + parseFloat(data.paid_amount || data.paid || 0).toFixed(2) + '</span></div>' +
      '<div class="receipt-row"><span>Change:</span><span>' + String.fromCharCode(8369) + parseFloat(data.return_amount || data.change || 0).toFixed(2) + '</span></div>' +
      '</div>' +
      '<div class="receipt-footer"><p><strong>Thank you for your purchase!</strong></p><p style="font-size: 0.875rem;">Please keep this receipt for your records.</p></div></div>';
  }

  window.closeProductReceiptModal = function() {
    document.getElementById('productReceiptModal').classList.remove('show');
    setTimeout(function() { window.location.reload(); }, 300);
  };

  window.printProductReceipt = function() {
    var content = document.getElementById('productReceiptModalBody').innerHTML;
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<!DOCTYPE html><html><head><title>Receipt</title><style>body{font-family:"Courier New",monospace}.receipt-container{max-width:600px;margin:0 auto;padding:20px}.receipt-header{text-align:center;margin-bottom:30px;padding-bottom:20px;border-bottom:2px dashed #333}.receipt-table{width:100%;border-collapse:collapse;margin:20px 0}.receipt-table th{background:#333;color:#fff;padding:10px;text-align:left}.receipt-table td{padding:10px;border-bottom:1px solid #ddd}.receipt-row{display:flex;justify-content:space-between;margin-bottom:8px}.receipt-total{margin-top:20px;padding-top:20px;border-top:2px solid #333}.receipt-footer{margin-top:30px;padding-top:20px;border-top:2px dashed #333;text-align:center}</style></head><body>' + content + '</body></html>');
    printWindow.document.close();
    printWindow.print();
  };
})(); // End Product Payment IIFE
</script>
@endpush