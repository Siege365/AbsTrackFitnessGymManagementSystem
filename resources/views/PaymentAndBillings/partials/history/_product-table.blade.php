<!-- ====== PRODUCT PAYMENTS PAGE ====== -->
<div class="page-panel" id="productPage">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Product Payments</h4>
        <div class="d-flex align-items-center">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" id="productSearchForm">
            @foreach(request()->except(['product_search', 'product_page']) as $key => $value)
              @if(!is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endif
            @endforeach
            <div class="search-wrapper mr-2">
              <input type="text" 
                name="product_search" 
                class="form-control form-control-sm" 
                placeholder="Search..." 
                value="{{ request('product_search') }}"
                id="productSearchInput">
              @if(request('product_search'))
              <button type="button" class="search-clear-btn" onclick="clearSearch('productSearchInput', 'productSearchForm')">&times;</button>
              @endif
            </div>
          </form>
          <div class="dropdown d-inline-block mr-2">
            <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
              <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <div class="dropdown-menu dropdown-menu-right filter-accordion">
              <div class="filter-header">
                <span class="filter-title">Filter By</span>
                <a href="{{ route('payments.history', request()->except(['product_sort', 'product_page'])) }}" class="filter-clear-all">Clear All</a>
              </div>

              <!-- Sort Filter -->
              <div class="filter-section">
                <div class="filter-section-header" onclick="PaymentHistoryPage.toggleFilterSection(this, event)">
                  <div class="filter-section-title">
                    <i class="mdi mdi-sort"></i>
                    <span>Sort</span>
                  </div>
                  <i class="mdi mdi-chevron-down filter-chevron"></i>
                </div>
                <div class="filter-section-content">
                  <a class="filter-option {{ request('product_sort', 'newest') === 'newest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['product_sort', 'product_page']), ['product_sort' => 'newest'])) }}">
                    <i class="mdi mdi-sort-descending"></i> Newest First
                  </a>
                  <a class="filter-option {{ request('product_sort') === 'oldest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['product_sort', 'product_page']), ['product_sort' => 'oldest'])) }}">
                    <i class="mdi mdi-sort-ascending"></i> Oldest First
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" id="selectAllProduct">
                  </label>
                </div>
              </th>
              <th class="text-left">Receipt #</th>
              <th class="text-left">Customer</th>
              <th class="text-left">Date</th>
              <th class="text-left">Amount</th>
              <th class="text-left">Cashier</th>
              <th class="text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($productPayments ?? [] as $p)
            <tr>
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input product-checkbox" value="{{ $p->id }}">
                  </label>
                </div>
              </td>
              <td>{{ $p->receipt_number }}</td>
              <td>{{ $p->customer_name }}</td>
              <td>{{ $p->created_at->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($p->total_amount,2) }}</td>
              <td>{{ $p->cashier_name }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-display="static" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('product', {{ $p->id }})">
                      <i class="mdi mdi-eye mr-2"></i> View Receipt
                    </button>
                    <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('product', {{ $p->id }}, '{{ $p->receipt_number }}', {{ $p->total_amount }}, '{{ addslashes($p->customer_name) }}')">
                      <i class="mdi mdi-cash-refund mr-2"></i> Refund
                    </button>
                    <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('product', {{ $p->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center">No product payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <button type="button" onclick="bulkDeleteProducts()" class="btn btn-sm btn-delete-selected" id="deleteProductBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="productCount">0</span>)
        </button>
        {{ $productPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>
</div><!-- /productPage -->
