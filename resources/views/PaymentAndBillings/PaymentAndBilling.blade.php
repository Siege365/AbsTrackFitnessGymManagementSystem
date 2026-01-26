@extends('layouts.admin')

@section('title', 'Payments & Billing')

@push('styles')
<style>

  .card-body:hover {
    transform: translateY(-2px);
  }

  .table-responsive::-webkit-scrollbar {
    height: 8px;
  }
  
  .table-responsive::-webkit-scrollbar-track {
    background: #191C24;
  }
  
  .table-responsive::-webkit-scrollbar-thumb {
    background-color: #555;
    border-radius: 4px;
  }

  .pagination .page-item.active .page-link {
    background-color: #191C24;
    border-color: #191C24;
  }
  
  .pagination .page-link {
    color: #555;
  }
  
  .pagination .page-link:hover {
    background-color: #191C24;
    border-color: #191C24;
    color: #000000;
  }

  .form-control[readonly] {
    background-color: #2A3038 !important;
    color: #495057 !important;
  }

  .table thead th,
  .table tbody td {
    color: #ffffff !important;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }

  #itemsTableBody tr {
    height: 53px;
  }

  #searchClearBtn {
    min-width: 80px;
  }

  #addItemBtn {
    min-width: 80px;
    background-color: #17a2b8;
    border-color: #17a2b8;
  }
  #addItemBtn:hover {
    background-color: #138496;
    border-color: #117a8b;
  }
  .search-results {
    position: absolute;
    background: #2A3038;
    border: 1px solid #555;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    border-radius: 4px;
    margin-top: 2px;
  }

  .search-result-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #555;
    color: #ffffff;
  }

  .search-result-item:hover {
    background-color: #191C24;
  }

  .search-result-item:last-child {
    border-bottom: none;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($totalRevenueMonth ?? 0, 2) }}</h3>
              <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-success ">
              <span class="mdi mdi-arrow-top-right icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Total Revenue This Month</h6>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($retailSalesRevenue ?? 0, 2) }}</h3>
              <p class="text-success ml-2 mb-0 font-weight-medium">+11%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-success">
              <span class="mdi mdi-arrow-top-right icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Retail Sales Revenue</h6>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($dailyIncome ?? 0, 2) }}</h3>
              <p class="text-danger ml-2 mb-0 font-weight-medium">-2.4%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-danger">
              <span class="mdi mdi-arrow-bottom-left icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Daily Income</h6>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($weeklyIncome ?? 0, 2) }}</h3>
              <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-success ">
              <span class="mdi mdi-arrow-top-right icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Weekly Income</h6>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Payment Details Form</h4>
          <div class="d-flex" style="position: relative; width: 420px;">
            <input type="text" id="searchItem" class="form-control form-control-sm mr-2" placeholder="Search items..." style="width: 300px;">
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
                <label for="customerName">Customer Name</label>
                <div style="position: relative;">
                  <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Name" autocomplete="off" required>
                  <input type="hidden" id="customerId" name="customer_id">
                  <div id="customerResults" class="search-results" style="display:none;"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="totalAmount">Total Amount</label>
                <input type="number" class="form-control" id="totalAmount" name="total_amount" placeholder="₱0.00" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="transactionType">Transaction Type</label>
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
                <label for="paidAmount">Paid Amount</label>
                <input type="number" class="form-control" id="paidAmount" name="paid_amount" placeholder="₱0.00" step="0.01">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="paymentMethod">Payment Method</label>
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
                <label for="returnAmount">Return Amount</label>
                <input type="number" class="form-control readonly-field" id="returnAmount" placeholder="₱0.00" readonly>
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
              <button type="button" class="btn btn-secondary mr-2" id="clearBtn">Clear</button>
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Transaction History</h4>
          <div class="d-flex">
            <button class="btn btn-sm btn-outline-secondary mr-2">
              <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <input type="text" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
          </div>
        </div>
        <div class="table-responsive" style="min-height: 600px;">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="min-width: 50px;">
                  <div class="form-check form-check-muted m-0">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input">
                    </label>
                  </div>
                </th>
                <th style="min-width: 80px;">Receipt#</th>
                <th style="min-width: 180px;">Customer Name</th>
                <th style="min-width: 150px;">Date & Time</th>
                <th style="min-width: 120px;">Payment Type</th>
                <th style="min-width: 80px;">Quantity</th>
                <th style="min-width: 120px;">Total Price (₱)</th>
                <th style="min-width: 150px;">Cashier</th>
                <th style="min-width: 80px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
                $transactionCount = isset($transactions) ? $transactions->count() : 0;
                $maxRows = 10;
              @endphp
              
              @if(isset($transactions) && $transactions->count() > 0)
                @foreach($transactions as $transaction)
                <tr>
                  <td>
                    <div class="form-check form-check-muted m-0">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                      </label>
                    </div>
                  </td>
                  <td>{{ $transaction->receipt_number }}</td>
                  <td>{{ $transaction->customer_name }}</td>
                  <td>{{ $transaction->created_at->format('Y-m-d, H:i') }}</td>
                  <td>{{ $transaction->payment_method }}</td>
                  <td>{{ $transaction->total_quantity }}</td>
                  <td>₱{{ number_format($transaction->total_amount, 2) }}</td>
                  <td>{{ $transaction->cashier_name }}</td>
                  <td>
                    <button class="btn btn-sm btn-link">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                  </td>
                </tr>
                @endforeach
              @endif
              
              @for($i = $transactionCount; $i < $maxRows; $i++)
              <tr>
                <td style="height: 53px;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>
                  @if($i == 0)
                    <span class="text-muted">No transactions found</span>
                  @endif
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              @endfor
            </tbody>
          </table>
        </div>
        
        @if(isset($transactions) && $transactions->total() > 0)
        <div class="d-flex justify-content-between align-items-center mt-3">
          <button class="btn btn-danger btn-sm">
            <i class="mdi mdi-delete"></i>
          </button>
          <div>
            {{ $transactions->links() }}
          </div>
        </div>
        @else
        <div class="d-flex justify-content-between align-items-center mt-3">
          <button class="btn btn-danger btn-sm">
            <i class="mdi mdi-delete"></i>
          </button>
          <div class="text-muted">
            Showing 0 to 0 of 0 entries
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let cartItems = [];
  let inventoryItems = @json($inventoryItems ?? []);
  let selectedSearchItem = null; // currently selected item from search (not yet added)
  let isSubmitting = false;
  // Persistence keys
  const STORAGE_KEY = 'paymentFormState_v1';

  // Save current form + cart state to localStorage
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

  // Load saved state from localStorage
  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const state = JSON.parse(raw);
      if (state) {
        cartItems = Array.isArray(state.cartItems) ? state.cartItems : [];
        if (document.getElementById('customerName')) document.getElementById('customerName').value = state.customer_name || '';
        if (document.getElementById('customerId')) document.getElementById('customerId').value = state.customer_id || '';
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

  // Clear saved state
  function clearState() {
    try { localStorage.removeItem(STORAGE_KEY); } catch (e) { }
  }

  // Restore state when page loads
  document.addEventListener('DOMContentLoaded', function() {
    loadState();
  });

  // Save state before leaving the page (skip while submitting)
  window.addEventListener('beforeunload', function() {
    if (!isSubmitting) saveState();
  });
  
  console.log('Inventory Items Loaded:', inventoryItems.length);
  
  // Search functionality
  document.getElementById('searchItem').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const searchResults = document.getElementById('searchResults');
    
    console.log('Searching for:', searchTerm);
    
    if (searchTerm.length < 2) {
      searchResults.style.display = 'none';
      return;
    }
    
    const filtered = inventoryItems.filter(item => 
      item.product_name.toLowerCase().includes(searchTerm) ||
      item.product_number.toLowerCase().includes(searchTerm)
    );
    
    console.log('Found items:', filtered.length);
    
    if (filtered.length > 0) {
      searchResults.innerHTML = filtered.map(item => `
        <div class="search-result-item" data-id="${item.id}" data-name="${item.product_name}" 
             data-price="${item.unit_price}" data-stock="${item.stock_qty}">
          <strong>${item.product_name}</strong> - ₱${parseFloat(item.unit_price).toFixed(2)} 
          <span class="text-muted">(Stock: ${item.stock_qty})</span>
        </div>
      `).join('');
      searchResults.style.display = 'block';
      
      // Add click handlers: select item into search bar but do not add to cart
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

          // Store selected item (user must click 'Add Item' to add to cart)
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

  // Search top Clear button: clear the details form and cart (do not clear the search input)
  document.getElementById('searchClearBtn').addEventListener('click', function() {
    cartItems = [];
    renderCart();
    const form = document.getElementById('paymentForm');
    if (form) form.reset();
    document.getElementById('totalAmount').value = '';
    document.getElementById('returnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  });
  
  // Add item to cart
  function addItemToCart(item) {
    // Guard against adding items with no stock
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
  
  // Render cart
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
  
  // Update quantity
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
  
  // Remove item
  function removeItem(index) {
    cartItems.splice(index, 1);
    renderCart();
    calculateTotals();
    saveState();
  }
  
  // Calculate totals
  function calculateTotals() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    document.getElementById('totalAmount').value = total.toFixed(2);
    
    const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
    const returnAmount = paidAmount - total;
    document.getElementById('returnAmount').value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
  }
  
  // Update return amount on paid amount change
  document.getElementById('paidAmount').addEventListener('input', calculateTotals);
  // Also persist paid amount changes
  document.getElementById('paidAmount').addEventListener('input', saveState);
  
  // Clear form button: clear cart and form fields but keep search input intact
  document.getElementById('clearBtn').addEventListener('click', function() {
    cartItems = [];
    renderCart();
    document.getElementById('paymentForm').reset();
    document.getElementById('totalAmount').value = '';
    document.getElementById('returnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  });

  // Add Item button: add the currently selected search item to cart
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

    // Add the selected item
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
  
  // Submit form
  // NOTE: backend must also enforce that paid amount >= total before recording the transaction.
  document.getElementById('paymentForm').addEventListener('submit', function(e) {
    if (cartItems.length === 0) {
      e.preventDefault();
      alert('Please add at least one item to the cart!');
      return;
    }

    // Prevent submission when paid is less than total
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    if (paid < total) {
      e.preventDefault();
      alert('Payment incomplete: Paid amount must be equal to or greater than the total amount.');
      const paidEl = document.getElementById('paidAmount');
      if (paidEl) paidEl.focus();
      return;
    }

    document.getElementById('itemsData').value = JSON.stringify(cartItems);
    // Prevent beforeunload from re-saving state during submission
    isSubmitting = true;
    // Clear saved state after successful submission (page will redirect)
    clearState();
  });
  
  // Close search results when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#searchItem') && !e.target.closest('#searchResults')) {
      document.getElementById('searchResults').style.display = 'none';
    }
    if (!e.target.closest('#customerName') && !e.target.closest('#customerResults')) {
      const cr = document.getElementById('customerResults');
      if (cr) cr.style.display = 'none';
    }
  });

  // Membership autocomplete for Customer Name
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
</script>
@endpush