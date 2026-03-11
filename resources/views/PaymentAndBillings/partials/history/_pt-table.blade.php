<!-- ====== PERSONAL TRAINING PAYMENTS PAGE ====== -->
<div class="page-panel" id="ptPage">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Personal Training Payments</h4>
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
            <button type="button" class="btn btn-sm filter-button dropdown-toggle" id="ptHistoryFilterDropdown" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
              <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <div class="dropdown-menu dropdown-menu-right filter-accordion" aria-labelledby="ptHistoryFilterDropdown">
              <div class="filter-header">
                <span class="filter-title">Filter By</span>
                <a href="{{ route('payments.history', request()->except(['pt_method_filter', 'pt_status_filter', 'pt_sort', 'pt_page'])) }}" class="filter-clear-all">
                  Clear All
                </a>
              </div>

              <div class="filter-section active">
                <div class="filter-section-header" data-filter-section>
                  <div class="filter-section-title">
                    <i class="mdi mdi-credit-card-outline"></i>
                    <span>Payment Method</span>
                  </div>
                  <i class="mdi mdi-chevron-down filter-chevron"></i>
                </div>
                <div class="filter-section-content">
                  <a class="filter-option {{ !request('pt_method_filter') || request('pt_method_filter') === 'all' ? 'active' : '' }}" href="{{ route('payments.history', request()->except(['pt_method_filter', 'pt_page'])) }}">
                    <i class="mdi mdi-filter-remove"></i> All Methods
                  </a>
                  @foreach(['Cash', 'Gcash', 'Card', 'Bank Transfer'] as $method)
                  <a class="filter-option {{ request('pt_method_filter') === $method ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_method_filter', 'pt_page']), ['pt_method_filter' => $method])) }}">
                    <i class="mdi mdi-cash"></i> {{ $method }}
                  </a>
                  @endforeach
                </div>
              </div>

              <div class="filter-section">
                <div class="filter-section-header" data-filter-section>
                  <div class="filter-section-title">
                    <i class="mdi mdi-progress-clock"></i>
                    <span>Session Status</span>
                  </div>
                  <i class="mdi mdi-chevron-down filter-chevron"></i>
                </div>
                <div class="filter-section-content">
                  <a class="filter-option {{ !request('pt_status_filter') || request('pt_status_filter') === 'all' ? 'active' : '' }}" href="{{ route('payments.history', request()->except(['pt_status_filter', 'pt_page'])) }}">
                    <i class="mdi mdi-filter-remove"></i> All Statuses
                  </a>
                  @php
                    $ptStatuses = [
                      'upcoming' => 'Upcoming',
                      'in_progress' => 'In Progress',
                      'done' => 'Done',
                      'cancelled' => 'Cancelled',
                      'expired' => 'Expired',
                    ];
                  @endphp
                  @foreach($ptStatuses as $statusKey => $statusLabel)
                  <a class="filter-option {{ request('pt_status_filter') === $statusKey ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['pt_status_filter', 'pt_page']), ['pt_status_filter' => $statusKey])) }}">
                    <i class="mdi mdi-circle-medium"></i> {{ $statusLabel }}
                  </a>
                  @endforeach
                </div>
              </div>

              <div class="filter-section">
                <div class="filter-section-header" data-filter-section>
                  <div class="filter-section-title">
                    <i class="mdi mdi-sort"></i>
                    <span>Sort Order</span>
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
                    <input type="checkbox" class="form-check-input" id="selectAllPT">
                  </label>
                </div>
              </th>
              <th class="text-left">Receipt #</th>
              <th class="text-left">Customer</th>
              <th class="text-left">Plan</th>
              <th class="text-left">Trainer</th>
              <th class="text-left">Session</th>
              <th class="text-left">Amount</th>
              <th class="text-left">Payment Method</th>
              <th class="text-left">Cashier</th>
              <th class="text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($ptPayments as $pt)
            <tr>
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input pt-checkbox" value="{{ $pt->id }}">
                  </label>
                </div>
              </td>
              <td>{{ $pt->receipt_number ?? '—' }}</td>
              <td>
                <div class="d-flex align-items-center history-name-cell">
                  @php
                    $ptAvatar = null;

                    if ($pt->customer_source === 'membership' && $pt->membership?->avatar) {
                      $ptAvatar = $pt->membership->avatar;
                    } elseif ($pt->customer_source === 'client' && $pt->client?->avatar) {
                      $ptAvatar = $pt->client->avatar;
                    }
                  @endphp

                  @if($ptAvatar)
                    <img src="{{ asset('storage/' . $ptAvatar) }}" class="avatar-circle mr-2" alt="{{ $pt->display_name }}">
                  @else
                    <div class="avatar-initial mr-2">
                      {{ strtoupper(substr($pt->display_name, 0, 1)) }}
                    </div>
                  @endif
                  <div class="history-stack">
                    <span class="history-stack-title">{{ $pt->display_name }}</span>
                    <span class="history-stack-subtitle">{{ ucfirst($pt->customer_source ?? 'walkin') }}</span>
                  </div>
                </div>
              </td>
              <td>
                <div class="history-stack">
                  <span class="history-stack-title">{{ $pt->plan_name ?? 'Personal Training' }}</span>
                  <span class="history-stack-subtitle">
                    @if($pt->plan_duration_days)
                      {{ $pt->plan_duration_days }} {{ $pt->plan_duration_days === 1 ? 'Session' : 'Sessions' }}
                    @else
                      Legacy Record
                    @endif
                  </span>
                </div>
              </td>
              <td>{{ $pt->trainer_name }}</td>
              <td>
                <div class="history-stack">
                  <span class="history-stack-title">{{ $pt->formatted_date }}</span>
                  <span class="history-stack-subtitle">{{ $pt->formatted_time }}</span>
                </div>
              </td>
              <td>{{ is_null($pt->amount) ? '—' : '₱' . number_format((float) $pt->amount, 2) }}</td>
              <td><span class="history-inline-text">{{ $pt->payment_type ?? '—' }}</span></td>
              <td>
                @php
                  $processedBy = $pt->processed_by ?: 'Admin';
                @endphp
                <div class="d-flex align-items-center history-meta-cell">
                  <div class="avatar-initial avatar-initial-sm mr-2">
                    {{ strtoupper(substr($processedBy, 0, 1)) }}
                  </div>
                  <span>{{ $processedBy }}</span>
                </div>
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('pt', {{ $pt->id }})">
                      <i class="mdi mdi-eye mr-2"></i> View Receipt
                    </button>
                    <button type="button" class="dropdown-item text-warning {{ is_null($pt->amount) ? 'disabled' : '' }}" {{ is_null($pt->amount) ? 'disabled' : '' }} onclick="openRefundModal('pt', {{ $pt->id }}, '{{ $pt->receipt_number }}', {{ is_null($pt->amount) ? 0 : (float) $pt->amount }}, '{{ addslashes($pt->display_name) }}')">
                      <i class="mdi mdi-cash-refund mr-2"></i> Refund
                    </button>
                    <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('pt', {{ $pt->id }}, '{{ addslashes($pt->display_name) }}')">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center">No PT payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <button type="button" onclick="bulkDeletePTs()" class="btn btn-sm btn-delete-selected" id="deletePtBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="ptCount">0</span>)
        </button>
        {{ $ptPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>
</div><!-- /ptPage -->