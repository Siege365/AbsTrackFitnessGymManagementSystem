@extends('layouts.admin')

@section('title', 'Payments & Billing -> Product Payment')

@push('styles')
@vite(['resources/css/product-payment.css'])
@endpush

@section('content')

  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Product Payment</h2>
              <p class="page-header-subtitle">Process product sales and manage payment transactions.</p>
          </div>
      </div>
  </div>

  <!-- Stats Grid -->
  <div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($totalRevenueMonth ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Total Revenue This Month</p>
            </div>
            <div class="stat-change positive">
              <i class="mdi mdi-arrow-up"></i> +3.5%
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($retailSalesRevenue ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Retail Sales Revenue</p>
            </div>
            <div class="stat-change positive">
              <i class="mdi mdi-arrow-up"></i> +11%
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($dailyIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Daily Income</p>
            </div>
            <div class="stat-change negative">
              <i class="mdi mdi-arrow-down"></i> -2.4%
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($weeklyIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Weekly Income</p>
            </div>
            <div class="stat-change positive">
              <i class="mdi mdi-arrow-up"></i> +3.5%
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment Form Card -->
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="card-title mb-0">Payment Details Form</h4>
        <div class="d-flex" style="position: relative; width: 420px;">
          <input type="text" id="searchItem" class="form-control form-control-sm mr-2" placeholder="Search items...">
          <button type="button" class="btn btn-sm btn-primary mr-2" id="addItemBtn">
            Add Item
          </button>
          <button type="button" class="btn btn-sm btn-warning" id="searchClearBtn">
            Clear
          </button>
          <div id="searchResults" class="search-results" style="display: none;"></div>
        </div>
      </div>

      <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
        @csrf

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="customerName" class="form-label">Customer Name</label>
              <div style="position: relative;">
                <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Name" autocomplete="off" required>
                <input type="hidden" id="customerId" name="customer_id">
                <div id="customerResults" class="search-results" style="display:none;"></div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="totalAmount" class="form-label">Total Amount</label>
              <input type="number" class="form-control" id="totalAmount" name="total_amount" placeholder="₱0.00" readonly>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="transactionType" class="form-label">Transaction Type</label>
              <select class="form-control" id="transactionType" name="transaction_type">
                <option>Mixed</option>
                <option>Cash</option>
                <option>Credit Card</option>
                <option>Online Payment</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="paidAmount" class="form-label">Paid Amount</label>
              <input type="number" class="form-control" id="paidAmount" name="paid_amount" placeholder="₱0.00" step="0.01">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="paymentMethod" class="form-label">Payment Method</label>
              <select class="form-control" id="paymentMethod" name="payment_method">
                <option>Cash</option>
                <option>Credit Card</option>
                <option>Debit Card</option>
                <option>GCash</option>
                <option>Online Payment</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="returnAmount" class="form-label">Return Amount</label>
              <input type="number" class="form-control" id="returnAmount" placeholder="₱0.00" readonly>
            </div>
          </div>
        </div>
        
        <div class="row mt-3">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Items</h5>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered" id="itemsTable">
                <thead>
                  <tr>
                    <th style="min-width: 200px;">Item</th>
                    <th style="min-width: 80px;">Qty</th>
                    <th style="min-width: 120px;">Unit Price (₱)</th>
                    <th style="min-width: 120px;">Subtotal (₱)</th>
                    <th style="min-width: 100px;">Actions</th>
                  </tr>
                </thead>
                <tbody id="itemsTableBody">
                  <tr><td colspan="5" class="text-center text-muted">No items added</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <input type="hidden" id="itemsData" name="items_data">
        
        <div class="row mt-3">
          <div class="col-12">
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
              <button type="button" class="btn btn-secondary" id="clearBtn">
                <i class="mdi mdi-close"></i> Clear
              </button>
              <button type="button" class="btn btn-primary" id="processPaymentBtn">
                <i class="mdi mdi-check"></i> Process Payment
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulkDeleteForm" action="{{ route('payments.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Refund Form (Hidden) -->
<form id="productRefundForm" action="" method="POST" style="display: none;">
  @csrf
  <input type="hidden" name="reason" id="productRefundReasonInput">
</form>

<!-- Payment Confirmation Modal -->
<div id="paymentConfirmationModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Confirm Payment</h3>
      <button class="modal-close" onclick="closePaymentConfirmation()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="confirmation-icon warning">
        <i class="mdi mdi-alert-circle-outline"></i>
      </div>
      <p class="confirmation-message">Please review the payment details before proceeding.</p>
      <div class="confirmation-details" id="paymentConfirmationDetails"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closePaymentConfirmation()">
        <i class="mdi mdi-close"></i> Cancel
      </button>
      <button type="button" class="btn btn-primary" id="confirmProductPaymentBtn">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="receipt-modal">
  <div class="receipt-modal-content">
    <div class="receipt-modal-header">
      <h3>Receipt</h3>
      <button class="receipt-modal-close" onclick="closeReceiptModal()">&times;</button>
    </div>
    <div class="receipt-modal-body" id="receiptModalBody">
      <div class="text-center" style="padding: 40px; color: #666;">
        <i class="mdi mdi-loading mdi-spin" style="font-size: 48px;"></i>
        <p>Loading receipt...</p>
      </div>
    </div>
    <div class="receipt-modal-footer">
      <button type="button" class="btn btn-primary" onclick="printReceipt()">
        <i class="mdi mdi-printer"></i> Print Receipt
      </button>
      <button type="button" class="btn btn-secondary" onclick="closeReceiptModal()">
        Close
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
@vite(['resources/js/common/table-dropdown.js'])
<script>
  let cartItems = [];
  let inventoryItems = @json($inventoryItems ?? []);
  let selectedSearchItem = null;
  let isSubmitting = false;
  const STORAGE_KEY = 'paymentFormState_v1';

  // Save state
  function saveState() {
    try {
      const state = {
        cartItems: cartItems,
        customer_name: document.getElementById('customerName')?.value || '',
        customer_id: document.getElementById('customerId')?.value || '',
        transaction_type: document.getElementById('transactionType')?.value || '',
        payment_method: document.getElementById('paymentMethod')?.value || '',
        paid_amount: document.getElementById('paidAmount')?.value || '',
        total_amount: document.getElementById('totalAmount')?.value || ''
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
      console.warn('Failed to save payment form state', e);
    }
  }

  // Load state
  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const state = JSON.parse(raw);
      if (state) {
        cartItems = Array.isArray(state.cartItems) ? state.cartItems : [];
        if (document.getElementById('customerName')) document.getElementById('customerName').value = state.customer_name || '';
        if (document.getElementById('customerId')) document.getElementById('customerId').value = state.customer_id || '';

        if ((state.customer_id || state.customer_name) && window.fetch) {
          fetch(`{{ url('/members/search') }}?q=${encodeURIComponent(state.customer_name || '')}`)
            .then(r => r.json())
            .then(data => {
              const exists = Array.isArray(data) && data.some(m => String(m.id) === String(state.customer_id) || String(m.name).toLowerCase() === String((state.customer_name||'')).toLowerCase());
              if (!exists) {
                if (document.getElementById('customerName')) document.getElementById('customerName').value = '';
                if (document.getElementById('customerId')) document.getElementById('customerId').value = '';
                state.customer_name = '';
                state.customer_id = '';
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
              }
            })
            .catch(() => {});
        }
        if (document.getElementById('transactionType')) document.getElementById('transactionType').value = state.transaction_type || '';
        if (document.getElementById('paymentMethod')) document.getElementById('paymentMethod').value = state.payment_method || '';
        if (document.getElementById('paidAmount')) document.getElementById('paidAmount').value = state.paid_amount || '';
        if (document.getElementById('totalAmount')) document.getElementById('totalAmount').value = state.total_amount || '';
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

  document.addEventListener('DOMContentLoaded', function() {
    loadState();
    initializeTransactionControls();
  });

  window.addEventListener('beforeunload', function() {
    if (!isSubmitting) saveState();
  });
  
  console.log('Inventory Items Loaded:', inventoryItems.length);
  
  // Search functionality
  document.getElementById('searchItem').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const searchResults = document.getElementById('searchResults');
    
    if (searchTerm.length < 2) {
      searchResults.style.display = 'none';
      return;
    }
    
    const filtered = inventoryItems.filter(item => 
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
      
      document.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('click', function() {
          const stock = parseInt(this.dataset.stock);
          const name = this.dataset.name;
          const id = this.dataset.id;
          const price = parseFloat(this.dataset.price);

          if (isNaN(stock) || stock <= 0) {
            alert('This item is out of stock and cannot be selected.');
            return;
          }

          selectedSearchItem = { id: id, name: name, price: price, stock: stock };
          document.getElementById('searchItem').value = name;
          searchResults.style.display = 'none';
        });
      });
    } else {
      searchResults.innerHTML = '<div class="search-result-item">No items found</div>';
      searchResults.style.display = 'block';
    }
  });

  function addItemToCart(item) {
    if (!item || typeof item.stock === 'undefined' || item.stock <= 0) {
      alert('Cannot add item — Insufficient stock.');
      return;
    }

    const existingItem = cartItems.find(i => i.id == item.id);

    if (existingItem) {
      if (existingItem.qty < item.stock) {
        existingItem.qty++;
      } else {
        alert('Cannot add more. Insufficient stock!');
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
    saveState();
  }
  
  function renderCart() {
    const tbody = document.getElementById('itemsTableBody');
    
    if (cartItems.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>';
      return;
    }
    
    tbody.innerHTML = cartItems.map((item, index) => `
      <tr>
        <td>${item.name}</td>
        <td>
          <input type="number" class="form-control form-control-sm" value="${item.qty}" 
                 min="1" max="${item.stock}" onchange="updateQty(${index}, this.value)">
        </td>
        <td>₱${item.price.toFixed(2)}</td>
        <td>₱${(item.price * item.qty).toFixed(2)}</td>
        <td>
          <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
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
      alert('Invalid quantity or insufficient stock!');
      renderCart();
    }
    saveState();
  }
  
  function removeItem(index) {
    cartItems.splice(index, 1);
    renderCart();
    calculateTotals();
    saveState();
  }
  
  function calculateTotals() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    document.getElementById('totalAmount').value = total.toFixed(2);
    
    const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
    const returnAmount = paidAmount - total;
    document.getElementById('returnAmount').value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
  }
  
  document.getElementById('paidAmount').addEventListener('input', calculateTotals);
  document.getElementById('paidAmount').addEventListener('input', saveState);
  
  // Clear form button
  document.getElementById('clearBtn').addEventListener('click', function() {
    cartItems = [];
    renderCart();
    document.getElementById('paymentForm').reset();
    document.getElementById('totalAmount').value = '';
    document.getElementById('returnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  });

  document.getElementById('addItemBtn').addEventListener('click', function() {
    if (!selectedSearchItem) {
      const name = document.getElementById('searchItem').value.trim();
      if (!name) {
        alert('Please select an item first from the search results.');
        return;
      }
      const found = inventoryItems.find(i => i.product_name.toLowerCase() === name.toLowerCase());
      if (!found) {
        alert('Selected item not found. Please choose from the search results.');
        return;
      }
      if (found.stock_qty <= 0) {
        alert('This item is out of stock and cannot be added.');
        return;
      }
      selectedSearchItem = { id: found.id, name: found.product_name, price: parseFloat(found.unit_price), stock: found.stock_qty };
    }

    addItemToCart(selectedSearchItem);
    const searchEl = document.getElementById('searchItem');
    if (searchEl) {
      searchEl.value = '';
      searchEl.focus();
    }
    const sr = document.getElementById('searchResults');
    if (sr) sr.style.display = 'none';
    selectedSearchItem = null;
  });

  document.getElementById('searchClearBtn').addEventListener('click', function() {
    document.getElementById('searchItem').value = '';
    document.getElementById('searchResults').style.display = 'none';
    selectedSearchItem = null;
  });
  
  // Process Payment Button Handler
  document.getElementById('processPaymentBtn').addEventListener('click', function(e) {
    e.preventDefault();

    if (cartItems.length === 0) {
      alert('Please add at least one item to the cart!');
      return;
    }

    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    if (paid < total) {
      alert('Payment incomplete: Paid amount must be equal to or greater than the total amount.');
      const paidEl = document.getElementById('paidAmount');
      if (paidEl) paidEl.focus();
      return;
    }

    // Prepare items data
    document.getElementById('itemsData').value = JSON.stringify(cartItems);

    // Build confirmation details
    const itemsHtml = cartItems.map(i => `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">${i.name} x ${i.qty}</span>
        <span class="confirmation-detail-value">₱${(i.price * i.qty).toFixed(2)}</span>
      </div>
    `).join('');

    const customerName = document.getElementById('customerName')?.value || 'Walk-in Customer';
    const paymentMethod = document.getElementById('paymentMethod')?.value || 'Cash';

    const details = `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Customer:</span>
        <span class="confirmation-detail-value">${customerName}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Type:</span>
        <span class="confirmation-detail-value">PRODUCT</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Items:</span>
        <span class="confirmation-detail-value">${cartItems.length} item(s)</span>
      </div>
      ${itemsHtml}
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Method:</span>
        <span class="confirmation-detail-value">${paymentMethod}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Total Amount:</span>
        <span class="confirmation-detail-value">₱${total.toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Paid Amount:</span>
        <span class="confirmation-detail-value">₱${paid.toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Change:</span>
        <span class="confirmation-detail-value">₱${(paid - total).toFixed(2)}</span>
      </div>
    `;

    document.getElementById('paymentConfirmationDetails').innerHTML = details;
    document.getElementById('paymentConfirmationModal').classList.add('show');
  });
  
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#searchItem') && !e.target.closest('#searchResults')) {
      document.getElementById('searchResults').style.display = 'none';
    }
    if (!e.target.closest('#customerName') && !e.target.closest('#customerResults')) {
      const cr = document.getElementById('customerResults');
      if (cr) cr.style.display = 'none';
    }
  });

  // Membership autocomplete
  (function() {
    const input = document.getElementById('customerName');
    const resultsEl = document.getElementById('customerResults');
    const customerIdEl = document.getElementById('customerId');
    let debounceTimer;

    if (!input) return;

    input.addEventListener('input', function(e) {
      const q = this.value.trim();
      customerIdEl.value = '';

      clearTimeout(debounceTimer);
      if (q.length < 1) {
        resultsEl.style.display = 'none';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`{{ url('/members/search') }}?q=${encodeURIComponent(q)}`)
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
      
      if (countSpan) {
        countSpan.textContent = selectedCount;
      }
      
      if (selectedCount > 0) {
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.classList.remove('disabled');
      } else {
        bulkDeleteBtn.disabled = true;
        bulkDeleteBtn.classList.add('disabled');
      }
    }

    if (bulkDeleteBtn) {
      bulkDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        
        if (ids.length === 0) {
          alert('Please select at least one transaction to delete.');
          return;
        }

        if (confirm(`Are you sure you want to delete ${ids.length} transaction(s)? This action cannot be undone.`)) {
          document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
          document.getElementById('bulkDeleteForm').submit();
        }
      });
    }

    document.querySelectorAll('.delete-form-payment').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
  }

  function closePaymentConfirmation() {
    const m = document.getElementById('paymentConfirmationModal');
    if (m) m.classList.remove('show');
  }

  function confirmProductPayment() {
    const btn = document.getElementById('confirmProductPaymentBtn');
    if (!btn) return;
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Processing...';

    const form = document.getElementById('paymentForm');
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
        // Close confirmation modal
        closePaymentConfirmation();
        
        // Show receipt modal immediately
        setTimeout(() => {
          loadReceiptModal(data.payment.id);
        }, 300);
        
        // Clear form and state after showing receipt
        clearFormData();
        
      } else {
        alert(data.message || 'Failed to process payment');
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    })
    .catch(err => {
      console.error('Product payment error', err);
      alert('Failed to process payment. Please try again.');
      btn.disabled = false;
      btn.innerHTML = originalText;
    });
  }

  // Attach confirm handler to button
  document.getElementById('confirmProductPaymentBtn').addEventListener('click', confirmProductPayment);

  function clearFormData() {
    cartItems = [];
    renderCart();
    document.getElementById('paymentForm').reset();
    document.getElementById('totalAmount').value = '';
    document.getElementById('returnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  }

  // Receipt Modal Functions
  function loadReceiptModal(paymentId) {
    const modal = document.getElementById('receiptModal');
    const modalBody = document.getElementById('receiptModalBody');
    
    modal.classList.add('show');
    modalBody.innerHTML = `
      <div class="text-center" style="padding: 40px; color: #666;">
        <i class="mdi mdi-loading mdi-spin" style="font-size: 48px;"></i>
        <p>Loading receipt...</p>
      </div>
    `;
    
    fetch(`/payments/${paymentId}/receipt-data`)
      .then(response => response.json())
      .then(data => {
        modalBody.innerHTML = generateReceiptHTML(data);
      })
      .catch(error => {
        console.error('Error loading receipt:', error);
        modalBody.innerHTML = `
          <div class="text-center" style="padding: 40px; color: #dc3545;">
            <i class="mdi mdi-alert-circle" style="font-size: 48px;"></i>
            <p>Failed to load receipt. Please try again.</p>
          </div>
        `;
      });
  }

  function generateReceiptHTML(payment) {
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
          <div class="receipt-info-item">
            <strong>Transaction Type</strong>
            <span>${payment.transaction_type}</span>
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
          <div class="receipt-total-row" style="margin-top: 20px;">
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
          <p style="font-size: 14px; margin-top: 10px;">Please come again!</p>
        </div>
      </div>
    `;
  }

  function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    modal.classList.remove('show');
    
    // Reload page after closing receipt to refresh the transaction list
    setTimeout(() => {
      window.location.reload();
    }, 300);
  }

  function printReceipt() {
    window.print();
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const confirmModal = document.getElementById('paymentConfirmationModal');
    const receiptModal = document.getElementById('receiptModal');
    
    if (event.target === confirmModal) {
      closePaymentConfirmation();
    }
    if (event.target === receiptModal) {
      closeReceiptModal();
    }
  }

  // Product Refund Modal Functions
  let currentProductRefundId = null;
  function openProductRefundModal(id, receiptNumber, amount, customerName) {
    currentProductRefundId = id;
    // create modal if not exists
    if (!document.getElementById('productRefundModal')) {
      const html = `
      <div id="productRefundModal" class="modal-overlay">
        <div class="modal-content small">
          <div class="modal-header">
            <h3 class="modal-title">Process Refund</h3>
            <button class="modal-close" onclick="closeProductRefundModal()">&times;</button>
          </div>
          <div class="modal-body">
            <div class="refund-warning" style="background: rgba(220, 53, 69, 0.1); padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
              <i class="mdi mdi-alert" style="color: #dc3545;"></i>
              <strong>Warning:</strong> This will mark this transaction as refunded.
            </div>
            <div class="confirmation-details" id="productRefundDetails"></div>
            <div class="form-group">
              <label class="form-label">Refund Reason (Optional)</label>
              <textarea class="form-control" id="productRefundReason" rows="3" placeholder="Enter reason for refund..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProductRefundModal()">Cancel</button>
            <button type="button" class="btn btn-warning" onclick="confirmProductRefund()">Process Refund</button>
          </div>
        </div>
      </div>`;
      document.body.insertAdjacentHTML('beforeend', html);
    }

    document.getElementById('productRefundDetails').innerHTML = `
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Receipt:</span><span class="confirmation-detail-value">#${receiptNumber}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Customer:</span><span class="confirmation-detail-value">${customerName}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Refund Amount:</span><span class="confirmation-detail-value" style="color:#dc3545;">₱${parseFloat(amount).toFixed(2)}</span></div>`;

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
          alert(data.message || 'Refund processed');
          closeProductRefundModal();
          setTimeout(() => window.location.reload(), 500);
        } else {
          alert(data.message || 'Failed to process refund');
        }
      })
      .catch(err => { console.error(err); alert('Failed to process refund'); });
  }
</script>
@endpush