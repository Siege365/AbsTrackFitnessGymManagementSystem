<!-- ====== MEMBERSHIP PAYMENTS PAGE ====== -->
<div class="page-panel active" id="membershipPage">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Membership Payments</h4>
        <div class="d-flex align-items-center">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" id="membershipSearchForm">
            @foreach(request()->except(['membership_search', 'membership_page']) as $key => $value)
              @if(!is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endif
            @endforeach
            <div class="search-wrapper mr-2">
              <input type="text" 
                name="membership_search" 
                class="form-control form-control-sm" 
                placeholder="Search..." 
                value="{{ request('membership_search') }}"
                id="membershipSearchInput">
              @if(request('membership_search'))
              <button type="button" class="search-clear-btn" onclick="clearSearch('membershipSearchInput', 'membershipSearchForm')">&times;</button>
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
                <a href="{{ route('payments.history', request()->except(['membership_type_filter', 'membership_plan_filter', 'membership_page'])) }}" class="filter-clear-all">Clear All</a>
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
                  <a class="filter-option {{ request('membership_type_filter') === 'new' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), ['membership_type_filter' => 'new'])) }}">
                    <i class="mdi mdi-account-plus"></i> New
                  </a>
                  <a class="filter-option {{ request('membership_type_filter') === 'renewal' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), ['membership_type_filter' => 'renewal'])) }}">
                    <i class="mdi mdi-autorenew"></i> Renewal
                  </a>
                  <a class="filter-option {{ request('membership_type_filter') === 'extension' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), ['membership_type_filter' => 'extension'])) }}">
                    <i class="mdi mdi-calendar-plus"></i> Extension
                  </a>
                </div>
              </div>

              <!-- Plan Type Filter -->
              <div class="filter-section">
                <div class="filter-section-header" onclick="PaymentHistoryPage.toggleFilterSection(this, event)">
                  <div class="filter-section-title">
                    <i class="mdi mdi-label-outline"></i>
                    <span>Subscription Type</span>
                  </div>
                  <i class="mdi mdi-chevron-down filter-chevron"></i>
                </div>
                <div class="filter-section-content">
                  <a class="filter-option {{ request('membership_plan_filter') === 'Regular' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'Regular'])) }}">
                    <i class="mdi mdi-dumbbell"></i> Regular
                  </a>
                  <a class="filter-option {{ request('membership_plan_filter') === 'Student' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'Student'])) }}">
                    <i class="mdi mdi-school"></i> Student
                  </a>
                  <a class="filter-option {{ request('membership_plan_filter') === 'GymBuddy' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'GymBuddy'])) }}">
                    <i class="mdi mdi-account-multiple"></i> Gym Buddy
                  </a>
                  <a class="filter-option {{ request('membership_plan_filter') === 'ThreeMonths' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'ThreeMonths'])) }}">
                    <i class="mdi mdi-calendar-range"></i> 3 Months
                  </a>
                  <a class="filter-option {{ request('membership_plan_filter') === 'Session' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'Session'])) }}">
                    <i class="mdi mdi-clock-outline"></i> Session
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
                    <input type="checkbox" class="form-check-input" id="selectAllMembership">
                  </label>
                </div>
              </th>
              <th class="text-left">Receipt #</th>
              <th class="text-left">Member</th>
              <th class="text-left">Subscription Type</th>
              <th class="text-left">Payment Type</th>
              <th class="text-left">Date</th>
              <th class="text-left">Amount</th>
              <th class="text-left">Cashier</th>
              <th class="text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($membershipPayments ?? [] as $m)
            <tr>
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input membership-checkbox" value="{{ $m->id }}">
                  </label>
                </div>
              </td>
              <td>{{ $m->receipt_number }}</td>
              <td>{{ $m->member_name }}</td>
              <td>
                @php
                  $planLabels = [
                    'Regular' => 'Regular',
                    'Student' => 'Student',
                    'GymBuddy' => 'Gym Buddy',
                    'ThreeMonths' => '3 Months',
                    'Session' => 'Session',
                  ];
                  $planBadgeColors = [
                    'Regular' => 'primary',
                    'Student' => 'info',
                    'GymBuddy' => 'success',
                    'ThreeMonths' => 'warning',
                    'Session' => 'light',
                  ];
                @endphp
                <span class="badge badge-{{ $planBadgeColors[$m->plan_type] ?? 'secondary' }}">
                  {{ $planLabels[$m->plan_type] ?? $m->plan_type }}
                </span>
              </td>
              <td>
                <span class="badge badge-{{ $m->payment_type === 'new' ? 'success' : ($m->payment_type === 'renewal' ? 'primary' : 'info') }}">
                  {{ ucfirst($m->payment_type) }}
                </span>
              </td>
              <td>{{ $m->created_at->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($m->amount,2) }}</td>
              <td>{{ $m->processed_by }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-display="static" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('membership', {{ $m->id }})">
                      <i class="mdi mdi-eye mr-2"></i> View Receipt
                    </button>
                    <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('membership', {{ $m->id }}, '{{ $m->receipt_number }}', {{ $m->amount }}, '{{ addslashes($m->member_name) }}')">
                      <i class="mdi mdi-cash-refund mr-2"></i> Refund
                    </button>
                    <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('membership', {{ $m->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center">No membership payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <button type="button" onclick="bulkDeleteMemberships()" class="btn btn-sm btn-delete-selected" id="deleteMembershipBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="membershipCount">0</span>)
        </button>
        {{ $membershipPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>
</div><!-- /membershipPage -->
