<!-- ====== PERSONAL TRAINING PAYMENTS PAGE ====== -->
<div class="page-panel" id="ptPage">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>PT Payments</h4>
        <div class="d-flex align-items-center">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" id="ptSearchForm">
            @foreach(request()->except(['pt_search', 'pt_page']) as $key => $value)
              @if(!is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endif
            @endforeach
            <div class="search-wrapper mr-2">
              <input type="text"
                name="pt_search"
                class="form-control form-control-sm"
                placeholder="Search..."
                value="{{ request('pt_search') }}"
                id="ptSearchInput">
              @if(request('pt_search'))
              <button type="button" class="search-clear-btn" onclick="clearSearch('ptSearchInput', 'ptSearchForm')">&times;</button>
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
                <a href="{{ route('payments.history', request()->except(['pt_type_filter', 'pt_sort', 'pt_page'])) }}" class="filter-clear-all">Clear All</a>
              </div>

              <!-- Payment Type Filter -->
              <div class="filter-section">
                <div class="filter-section-header" onclick="PaymentHistoryPage.toggleFilterSection(this, event)">
                  <div class="filter-section-title">
                    <i class="mdi mdi-cash"></i>
                    <span>Payment Type</span>
                  </div>
                  <i class="mdi mdi-chevron-down filter-chevron"></i>
                </div>
                <div class="filter-section-content">
                  <a class="filter-option {{ request('pt_type_filter') === 'new' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_type_filter', 'pt_page']), ['pt_type_filter' => 'new'])) }}">
                    <i class="mdi mdi-account-plus"></i> New
                  </a>
                  <a class="filter-option {{ request('pt_type_filter') === 'renewal' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_type_filter', 'pt_page']), ['pt_type_filter' => 'renewal'])) }}">
                    <i class="mdi mdi-autorenew"></i> Renewal
                  </a>
                  <a class="filter-option {{ request('pt_type_filter') === 'extension' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_type_filter', 'pt_page']), ['pt_type_filter' => 'extension'])) }}">
                    <i class="mdi mdi-calendar-plus"></i> Extension
                  </a>
                </div>
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
                  <a class="filter-option {{ request('pt_sort', 'newest') === 'newest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_sort', 'pt_page']), ['pt_sort' => 'newest'])) }}">
                    <i class="mdi mdi-sort-descending"></i> Newest First
                  </a>
                  <a class="filter-option {{ request('pt_sort') === 'oldest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_sort', 'pt_page']), ['pt_sort' => 'oldest'])) }}">
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
                    <input type="checkbox" class="form-check-input" id="selectAllPt">
                  </label>
                </div>
              </th>
              <th class="text-left">Receipt #</th>
              <th class="text-left">Client</th>
              <th class="text-left">PT Plan</th>
              <th class="text-left">Payment Type</th>
              <th class="text-left">Date</th>
              <th class="text-left">Amount</th>
              <th class="text-left">Cashier</th>
              <th class="text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ptPayments ?? [] as $pt)
            <tr>
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input pt-checkbox" value="{{ $pt->id }}">
                  </label>
                </div>
              </td>
              <td>{{ $pt->receipt_number }}</td>
              <td>{{ $pt->member_name }}</td>
              <td>
                <span class="badge badge-warning">{{ $pt->plan_type }}</span>
              </td>
              <td>
                <span class="badge badge-{{ $pt->payment_type === 'new' ? 'success' : ($pt->payment_type === 'renewal' ? 'primary' : 'info') }}">
                  {{ ucfirst($pt->payment_type) }}
                </span>
              </td>
              <td>{{ $pt->created_at->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($pt->amount, 2) }}</td>
              <td>{{ $pt->processed_by }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-display="static" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('pt', {{ $pt->id }})">
                      <i class="mdi mdi-eye mr-2"></i> View Receipt
                    </button>
                    <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('pt', {{ $pt->id }}, '{{ $pt->receipt_number }}', {{ $pt->amount }}, '{{ addslashes($pt->member_name) }}')">
                      <i class="mdi mdi-cash-refund mr-2"></i> Refund
                    </button>
                    <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('pt', {{ $pt->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center">No PT payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <button type="button" onclick="bulkDeletePts()" class="btn btn-sm btn-delete-selected" id="deletePtBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="ptCount">0</span>)
        </button>
        {{ $ptPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>
</div><!-- /ptPage -->
