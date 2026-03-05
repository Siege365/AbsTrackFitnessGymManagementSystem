<!-- Activity Logs Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        {{-- Header row with title, search, and filter dropdown --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Activity Logs</h4>
          <div class="d-flex align-items-center">
            <form action="{{ route('UserAndAdmin.CashierActivity') }}" method="GET" class="d-flex align-items-center" id="activitySearchForm">
              {{-- Preserve existing filters --}}
              @foreach(request()->except(['search', 'page']) as $key => $value)
                @if(!is_array($value))
                  <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
              @endforeach
              <div class="search-wrapper mr-2">
                <input type="text" 
                  name="search" 
                  class="form-control form-control-sm" 
                  placeholder="Search logs..." 
                  value="{{ request('search') }}"
                  style="width: 100%; max-width: 450px;"
                  id="activitySearchInput">
                @if(request('search'))
                <button type="button" class="search-clear-btn" onclick="clearSearch('activitySearchInput', 'activitySearchForm')">&times;</button>
                @endif
              </div>
            </form>

            {{-- Filter Dropdown (Inventory style) --}}
            <div class="dropdown d-inline-block">
              <button type="button" 
                class="btn btn-sm filter-button dropdown-toggle {{ (request('module') && request('module') !== 'all') || (request('action') && request('action') !== 'all') ? 'active' : '' }}" 
                id="activityFilterDropdown" 
                data-toggle="dropdown" 
                data-flip="false" 
                data-display="static" 
                aria-haspopup="true" 
                aria-expanded="false">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right filter-accordion">
                <div class="filter-header">
                  <span class="filter-title">Filter By</span>
                  <a class="filter-clear-all" href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'action', 'page']), [])) }}">Clear All</a>
                </div>

                {{-- Module Section --}}
                <div class="filter-section {{ request('module') && request('module') !== 'all' ? 'active' : '' }}">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-tag-multiple"></i>
                      <span>Module</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    <a class="filter-option {{ request('module') == 'membership' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']), ['module' => 'membership'])) }}">
                      <i class="mdi mdi-account-group"></i> Membership
                    </a>
                    <a class="filter-option {{ request('module') == 'client' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']), ['module' => 'client'])) }}">
                      <i class="mdi mdi-account"></i> Client
                    </a>
                    <a class="filter-option {{ request('module') == 'pt' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']), ['module' => 'pt'])) }}">
                      <i class="mdi mdi-dumbbell"></i> PT Sessions
                    </a>
                    <a class="filter-option {{ request('module') == 'inventory' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']), ['module' => 'inventory'])) }}">
                      <i class="mdi mdi-package-variant"></i> Inventory
                    </a>
                    <a class="filter-option {{ request('module') == 'payment' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']), ['module' => 'payment'])) }}">
                      <i class="mdi mdi-cash-register"></i> Payment
                    </a>
                    <a class="filter-option {{ request('module') == 'attendance' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']), ['module' => 'attendance'])) }}">
                      <i class="mdi mdi-calendar-check"></i> Attendance
                    </a>
                  </div>
                </div>

                {{-- Action Section --}}
                <div class="filter-section {{ request('action') && request('action') !== 'all' ? 'active' : '' }}">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-flash"></i>
                      <span>Action</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    <a class="filter-option {{ request('action') == 'created' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'created'])) }}">
                      <i class="mdi mdi-plus-circle"></i> Created
                    </a>
                    <a class="filter-option {{ request('action') == 'updated' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'updated'])) }}">
                      <i class="mdi mdi-pencil"></i> Updated
                    </a>
                    <a class="filter-option {{ request('action') == 'deleted' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'deleted'])) }}">
                      <i class="mdi mdi-delete"></i> Deleted
                    </a>
                    <a class="filter-option {{ request('action') == 'refunded' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'refunded'])) }}">
                      <i class="mdi mdi-cash-refund"></i> Refunded
                    </a>
                    <a class="filter-option {{ request('action') == 'renewed' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'renewed'])) }}">
                      <i class="mdi mdi-autorenew"></i> Renewed
                    </a>
                    <a class="filter-option {{ request('action') == 'stock_in' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'stock_in'])) }}">
                      <i class="mdi mdi-arrow-down-circle"></i> Stock In
                    </a>
                    <a class="filter-option {{ request('action') == 'stock_out' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'stock_out'])) }}">
                      <i class="mdi mdi-arrow-up-circle"></i> Stock Out
                    </a>
                    <a class="filter-option {{ request('action') == 'status_updated' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'status_updated'])) }}">
                      <i class="mdi mdi-update"></i> Status Updated
                    </a>
                    <a class="filter-option {{ request('action') == 'bulk_deleted' ? 'active' : '' }}" 
                      href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']), ['action' => 'bulk_deleted'])) }}">
                      <i class="mdi mdi-delete-sweep"></i> Bulk Deleted
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Active filters tags --}}
        @if(request('module') && request('module') !== 'all' || request('action') && request('action') !== 'all')
        <div class="active-filters">
          @if(request('module') && request('module') !== 'all')
            <span class="active-filter-tag">
              <i class="mdi mdi-tag"></i> Module: {{ ucfirst(request('module')) }}
              <a href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['module', 'page']))) }}">&times;</a>
            </span>
          @endif
          @if(request('action') && request('action') !== 'all')
            <span class="active-filter-tag">
              <i class="mdi mdi-flash"></i> Action: {{ ucfirst(str_replace('_', ' ', request('action'))) }}
              <a href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['action', 'page']))) }}">&times;</a>
            </span>
          @endif
        </div>
        @endif

        {{-- Table --}}
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width: 50px;">
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" id="selectAllLogs">
                    </label>
                  </div>
                </th>
                <th>Log #</th>
                <th>User</th>
                <th>Action</th>
                <th style="min-width:220px; max-width:280px;">Description</th>
                <th>Customer</th>
                <th>Reference</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logs as $log)
              <tr>
                <td>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input log-checkbox" value="{{ $log->id }}">
                    </label>
                  </div>
                </td>
                <td>{{ $log->id }}</td>
                <td>{{ $log->user_name ?? 'System' }}</td>
                <td>
                  @php
                    $actionClasses = [
                      'created' => 'action-created',
                      'updated' => 'action-updated',
                      'deleted' => 'action-deleted',
                      'refunded' => 'action-refunded',
                      'renewed' => 'action-renewed',
                      'status_updated' => 'action-status_updated',
                      'stock_in' => 'action-stock_in',
                      'stock_out' => 'action-stock_out',
                      'bulk_deleted' => 'action-bulk_deleted',
                    ];
                  @endphp
                  <span class="action-badge {{ $actionClasses[$log->action] ?? 'action-created' }}">
                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                  </span>
                </td>
                <td style="min-width:220px; max-width:280px;">
                  <span class="log-description" title="{{ $log->description }}">{{ $log->description }}</span>
                </td>
                <td>
                  @if($log->customer_name)
                    <span class="customer-name">{{ $log->customer_name }}</span>
                  @else
                    <span style="color: rgba(255,255,255,0.3);">—</span>
                  @endif
                </td>
                <td>
                  @if($log->reference_number)
                    <span class="ref-number">{{ $log->reference_number }}</span>
                  @else
                    <span style="color: rgba(255,255,255,0.3);">—</span>
                  @endif
                </td>
                <td>
                  <div class="log-date">{{ $log->created_at->format('M d, Y') }}</div>
                  <div class="log-time">{{ $log->created_at->format('h:i:s A') }}</div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8">
                  <div class="empty-state">
                    <i class="mdi mdi-clipboard-text-outline"></i>
                    <h5>No Activity Logs Found</h5>
                    <p>
                      @if(request()->anyFilled(['search', 'module', 'action']))
                        No logs match your current filters. Try adjusting your search criteria.
                      @else
                        Activity logs will appear here as users perform actions in the system.
                      @endif
                    </p>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Footer: Delete Selected + Pagination --}}
        <div class="table-footer">
          <button type="button" onclick="bulkDeleteLogs()" class="btn btn-sm btn-delete-selected" id="deleteLogBtn">
            <i class="mdi mdi-delete"></i> Delete Selected (<span id="logCount">0</span>)
          </button>
          {{ $logs->links('vendor.pagination.custom') }}
        </div>

      </div>
    </div>
  </div>
</div>
