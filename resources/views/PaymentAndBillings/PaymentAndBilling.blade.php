@extends('layouts.admin')

@section('title', 'Payments & Billing -> Product Payment')

@push('styles')
@vite(['resources/css/product-payment.css'])
@endpush

@section('content')
<div class="container-fluid">

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
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-check"></i> Process Payment
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Transaction History Card -->
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Transaction History</h4>
        <div class="d-flex align-items-center">
          <!-- Filter Dropdown -->
          <div class="dropdown mr-2">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <div class="dropdown-menu" aria-labelledby="filterDropdownBtn">
              <h6 class="dropdown-header">Sort By</h6>
              <a class="dropdown-item filter-option {{ request('filter') == 'date_newest' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['filter' => 'date_newest', 'search' => request('search'), 'payment_method' => request('payment_method')]) }}">
                <i class="mdi mdi-calendar-clock"></i> Date (Newest)
              </a>
              <a class="dropdown-item filter-option {{ request('filter') == 'date_oldest' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['filter' => 'date_oldest', 'search' => request('search'), 'payment_method' => request('payment_method')]) }}">
                <i class="mdi mdi-calendar"></i> Date (Oldest)
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item filter-option {{ request('filter') == 'name_asc' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['filter' => 'name_asc', 'search' => request('search'), 'payment_method' => request('payment_method')]) }}">
                <i class="mdi mdi-sort-alphabetical-ascending"></i> Customer (A-Z)
              </a>
              <a class="dropdown-item filter-option {{ request('filter') == 'name_desc' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['filter' => 'name_desc', 'search' => request('search'), 'payment_method' => request('payment_method')]) }}">
                <i class="mdi mdi-sort-alphabetical-descending"></i> Customer (Z-A)
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item filter-option {{ request('filter') == 'amount_asc' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['filter' => 'amount_asc', 'search' => request('search'), 'payment_method' => request('payment_method')]) }}">
                <i class="mdi mdi-sort-numeric-ascending"></i> Amount (Low to High)
              </a>
              <a class="dropdown-item filter-option {{ request('filter') == 'amount_desc' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['filter' => 'amount_desc', 'search' => request('search'), 'payment_method' => request('payment_method')]) }}">
                <i class="mdi mdi-sort-numeric-descending"></i> Amount (High to Low)
              </a>
              <div class="dropdown-divider"></div>
              <h6 class="dropdown-header">Payment Method</h6>
              <a class="dropdown-item filter-option {{ request('payment_method') == 'Cash' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['payment_method' => 'Cash', 'filter' => request('filter'), 'search' => request('search')]) }}">
                <i class="mdi mdi-cash"></i> Cash
              </a>
              <a class="dropdown-item filter-option {{ request('payment_method') == 'Credit Card' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['payment_method' => 'Credit Card', 'filter' => request('filter'), 'search' => request('search')]) }}">
                <i class="mdi mdi-credit-card"></i> Credit Card
              </a>
              <a class="dropdown-item filter-option {{ request('payment_method') == 'Debit Card' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['payment_method' => 'Debit Card', 'filter' => request('filter'), 'search' => request('search')]) }}">
                <i class="mdi mdi-credit-card-outline"></i> Debit Card
              </a>
              <a class="dropdown-item filter-option {{ request('payment_method') == 'GCash' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['payment_method' => 'GCash', 'filter' => request('filter'), 'search' => request('search')]) }}">
                <i class="mdi mdi-cellphone"></i> GCash
              </a>
              <a class="dropdown-item filter-option {{ request('payment_method') == 'Online Payment' ? 'active' : '' }}" 
                 href="{{ route('payments.index', ['payment_method' => 'Online Payment', 'filter' => request('filter'), 'search' => request('search')]) }}">
                <i class="mdi mdi-web"></i> Online Payment
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item filter-option text-danger" 
                 href="{{ route('payments.index') }}">
                <i class="mdi mdi-close-circle"></i> Clear Filter
              </a>
            </div>
          </div>
          
          <!-- Search Bar -->
          <form action="{{ route('payments.index') }}" method="GET" id="transactionSearchForm" class="mb-0">
            <input type="hidden" name="filter" value="{{ request('filter') }}">
            <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
            <input type="text" 
                   id="transactionSearchInput" 
                   name="search" 
                   class="form-control form-control-sm" 
                   placeholder="Search transactions..." 
                   style="width: 200px;" 
                   value="{{ request('search') }}">
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="min-width: 50px;">
                <div class="form-check form-check-muted m-0">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" id="selectAllTransactions">
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
                      <input type="checkbox" class="form-check-input transaction-checkbox" value="{{ $transaction->id }}">
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
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" 
                              class="dropdown-item" 
                              onclick="loadReceiptModal({{ $transaction->id }})">
                        <i class="mdi mdi-eye mr-2"></i> View
                      </button>
                      <form action="{{ route('payments.destroy', $transaction->id) }}" method="POST" class="d-inline delete-form-payment">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                          <i class="mdi mdi-delete mr-2"></i> Delete
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            @endif
            
            @for($i = $transactionCount; $i < $maxRows; $i++)
            <tr>
              <td colspan="9" style="height: 53px; text-align: center;">
                @if($i == 0)
                  <span class="text-muted">No transactions found</span>
                @endif
              </td>
            </tr>
            @endfor
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="pagination-container">
        <button class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
        </button>
        
        @if(isset($transactions) && $transactions->total() > 0)
        <div class="d-flex flex-column align-items-center">
          <nav aria-label="Page navigation">
            <ul class="pagination mb-2">
              @if ($transactions->onFirstPage())
                <li class="page-item disabled">
                  <span class="page-link">‹</span>
                </li>
              @else
                <li class="page-item">
                  <a class="page-link" href="{{ $transactions->appends(request()->query())->previousPageUrl() }}" rel="prev">‹</a>
                </li>
              @endif

              @php
                $currentPage = $transactions->currentPage();
                $lastPage = $transactions->lastPage();
                
                if ($lastPage <= 3) {
                  $start = 1;
                  $end = $lastPage;
                } else {
                  if ($currentPage <= 2) {
                    $start = 1;
                    $end = 3;
                  } elseif ($currentPage >= $lastPage - 1) {
                    $start = $lastPage - 2;
                    $end = $lastPage;
                  } else {
                    $start = $currentPage - 1;
                    $end = $currentPage + 1;
                  }
                }
              @endphp

              @if ($start > 1)
                <li class="page-item">
                  <a class="page-link" href="{{ $transactions->appends(request()->query())->url(1) }}">1</a>
                </li>
                @if ($start > 2)
                  <li class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>
                @endif
              @endif

              @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                  <li class="page-item active">
                    <span class="page-link">{{ $page }}</span>
                  </li>
                @else
                  <li class="page-item">
                    <a class="page-link" href="{{ $transactions->appends(request()->query())->url($page) }}">{{ $page }}</a>
                  </li>
                @endif
              @endfor

              @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                  <li class="page-item disabled">
                    <span class="page-link">...</span>
                  </li>
                @endif
                <li class="page-item">
                  <a class="page-link" href="{{ $transactions->appends(request()->query())->url($lastPage) }}">{{ $lastPage }}</a>
                </li>
              @endif

              @if ($transactions->hasMorePages())
                <li class="page-item">
                  <a class="page-link" href="{{ $transactions->appends(request()->query())->nextPageUrl() }}" rel="next">›</a>
                </li>
              @else
                <li class="page-item disabled">
                  <span class="page-link">›</span>
                </li>
              @endif
            </ul>
          </nav>
          <span class="pagination-info">
            Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
          </span>
        </div>
        @else
        <nav aria-label="Page navigation">
          <ul class="pagination mb-2">
            <li class="page-item disabled"><span class="page-link">‹</span></li>
            <li class="page-item active"><span class="page-link">1</span></li>
            <li class="page-item disabled"><span class="page-link">2</span></li>
            <li class="page-item disabled"><span class="page-link">3</span></li>
            <li class="page-item disabled"><span class="page-link">›</span></li>
          </ul>
        </nav>
        <span class="pagination-info">Showing 0 to 0 of 0 entries</span>
        @endif
        
        <div style="width: 150px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulkDeleteForm" action="{{ route('payments.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

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
  
  document.getElementById('paymentForm').addEventListener('submit', function(e) {
    if (cartItems.length === 0) {
      e.preventDefault();
      alert('Please add at least one item to the cart!');
      return;
    }

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
    isSubmitting = true;
    clearState();
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

  // Real-time transaction search with debounce
  let transactionSearchTimeout;
  const transactionSearchInput = document.getElementById('transactionSearchInput');
  const transactionSearchForm = document.getElementById('transactionSearchForm');

  if (transactionSearchInput) {
      transactionSearchInput.addEventListener('keyup', function() {
          clearTimeout(transactionSearchTimeout);
          transactionSearchTimeout = setTimeout(function() {
              transactionSearchForm.submit();
          }, 500);
      });
  }

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
          <p>Thank you!</p>
        </div>
      </div>
    `;
  }

  function closeReceiptModal() {
    document.getElementById('receiptModal').classList.remove('show');
  }

  function printReceipt() {
    window.print();
  }

  window.onclick = function(event) {
    const modal = document.getElementById('receiptModal');
    if (event.target === modal) {
      closeReceiptModal();
    }
  }
</script>
@endpush