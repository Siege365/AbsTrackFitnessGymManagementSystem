@extends('layouts.admin')

@section('title', 'Activity Logs')

@push('styles')
@vite(['resources/css/activity-logs.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Activity Logs</h2>
            <p class="page-header-subtitle">Track and review staff activity logs and transactions.</p>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
  <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0">{{ number_format($totalLogs) }}</h2>
            <p class="text-muted mb-0">Total Logs</p>
          </div>
          <div class="stats-icon bg-primary">
            <i class="mdi mdi-format-list-bulleted" style="font-size: 24px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0">{{ number_format($todayLogs) }}</h2>
            <p class="text-muted mb-0">Today's Activities</p>
          </div>
          <div class="stats-icon bg-success">
            <i class="mdi mdi-calendar-today" style="font-size: 24px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0">{{ $uniqueUsers }}</h2>
            <p class="text-muted mb-0">Active Users</p>
          </div>
          <div class="stats-icon bg-info">
            <i class="mdi mdi-account-multiple" style="font-size: 24px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Activity Logs Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        {{-- Header row with title and search --}}
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

            @if($totalLogs > 0)
            <button type="button" class="btn btn-sm btn-clear-all ml-2" onclick="confirmClearAll()">
              <i class="mdi mdi-delete-sweep"></i> Clear All
            </button>
            @endif
          </div>
        </div>

        {{-- Filters row --}}
        <form action="{{ route('UserAndAdmin.CashierActivity') }}" method="GET" id="filterForm">
          @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif
          <div class="filters-row">
            <select name="module" class="form-control" style="width: 160px;" onchange="document.getElementById('filterForm').submit()">
              <option value="all">All Modules</option>
              <option value="membership" {{ request('module') == 'membership' ? 'selected' : '' }}>Membership</option>
              <option value="client" {{ request('module') == 'client' ? 'selected' : '' }}>Client</option>
              <option value="pt" {{ request('module') == 'pt' ? 'selected' : '' }}>PT Sessions</option>
              <option value="inventory" {{ request('module') == 'inventory' ? 'selected' : '' }}>Inventory</option>
              <option value="payment" {{ request('module') == 'payment' ? 'selected' : '' }}>Payment</option>
              <option value="attendance" {{ request('module') == 'attendance' ? 'selected' : '' }}>Attendance</option>
            </select>

            <select name="action" class="form-control" style="width: 160px;" onchange="document.getElementById('filterForm').submit()">
              <option value="all">All Actions</option>
              <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
              <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
              <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
              <option value="refunded" {{ request('action') == 'refunded' ? 'selected' : '' }}>Refunded</option>
              <option value="renewed" {{ request('action') == 'renewed' ? 'selected' : '' }}>Renewed</option>
              <option value="stock_in" {{ request('action') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
              <option value="stock_out" {{ request('action') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
              <option value="status_updated" {{ request('action') == 'status_updated' ? 'selected' : '' }}>Status Updated</option>
              <option value="bulk_deleted" {{ request('action') == 'bulk_deleted' ? 'selected' : '' }}>Bulk Deleted</option>
            </select>

            <select name="user_id" class="form-control" style="width: 160px;" onchange="document.getElementById('filterForm').submit()">
              <option value="all">All Users</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
              @endforeach
            </select>

            <input type="date" name="date_from" class="form-control" style="width: 160px;" value="{{ request('date_from') }}" placeholder="From" onchange="document.getElementById('filterForm').submit()">
            <input type="date" name="date_to" class="form-control" style="width: 160px;" value="{{ request('date_to') }}" placeholder="To" onchange="document.getElementById('filterForm').submit()">

            @if(request('module') || request('action') || request('user_id') || request('date_from') || request('date_to') || request('search'))
            <a href="{{ route('UserAndAdmin.CashierActivity') }}" class="btn btn-reset">
              <i class="mdi mdi-filter-remove"></i> Reset
            </a>
            @endif
          </div>
        </form>

        {{-- Active filters tags --}}
        @if(request('module') && request('module') !== 'all' || request('action') && request('action') !== 'all' || request('user_id') && request('user_id') !== 'all' || request('date_from') || request('date_to'))
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
          @if(request('user_id') && request('user_id') !== 'all')
            @php $filterUser = $users->firstWhere('id', request('user_id')); @endphp
            <span class="active-filter-tag">
              <i class="mdi mdi-account"></i> User: {{ $filterUser->name ?? 'Unknown' }}
              <a href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['user_id', 'page']))) }}">&times;</a>
            </span>
          @endif
          @if(request('date_from'))
            <span class="active-filter-tag">
              <i class="mdi mdi-calendar"></i> From: {{ request('date_from') }}
              <a href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['date_from', 'page']))) }}">&times;</a>
            </span>
          @endif
          @if(request('date_to'))
            <span class="active-filter-tag">
              <i class="mdi mdi-calendar"></i> To: {{ request('date_to') }}
              <a href="{{ route('UserAndAdmin.CashierActivity', array_merge(request()->except(['date_to', 'page']))) }}">&times;</a>
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
                <th>Module</th>
                <th>Description</th>
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
                <td>
                  @php
                    $moduleIcons = [
                      'membership' => 'mdi-account-group',
                      'client' => 'mdi-account',
                      'pt' => 'mdi-dumbbell',
                      'inventory' => 'mdi-package-variant',
                      'payment' => 'mdi-cash-register',
                      'attendance' => 'mdi-calendar-check',
                    ];
                    $moduleClass = 'module-' . $log->module;
                    $moduleIcon = $moduleIcons[$log->module] ?? 'mdi-tag';
                  @endphp
                  <span class="module-badge {{ $moduleClass }}">
                    <i class="mdi {{ $moduleIcon }}"></i>
                    {{ ucfirst($log->module) }}
                  </span>
                </td>
                <td>
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
                <td colspan="9">
                  <div class="empty-state">
                    <i class="mdi mdi-clipboard-text-outline"></i>
                    <h5>No Activity Logs Found</h5>
                    <p>
                      @if(request()->anyFilled(['search', 'module', 'action', 'user_id', 'date_from', 'date_to']))
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
          <button type="button" onclick="bulkDeleteLogs()" class="btn btn-sm btn-delete-selected" id="deleteLogBtn" disabled>
            <i class="mdi mdi-delete"></i> Delete Selected (<span id="logCount">0</span>)
          </button>
          {{ $logs->links('vendor.pagination.custom') }}
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Confirm Clear All Modal --}}
<div class="modal-overlay" id="clearAllModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title"><i class="mdi mdi-alert-circle-outline mr-2"></i>Clear All Activity Logs</h5>
      <button type="button" class="modal-close" onclick="closeClearAllModal()">&times;</button>
    </div>
    <div class="modal-body">
      <p style="color: #333; font-size: 1rem;">Are you sure you want to <strong>permanently delete all</strong> activity logs? This action cannot be undone.</p>
      <p style="color: #666; font-size: 0.9rem;">Total logs to be deleted: <strong id="clearAllCount">{{ $totalLogs }}</strong></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeClearAllModal()">Cancel</button>
      <form action="{{ route('activity-logs.clear-all') }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete-sweep mr-1"></i> Clear All</button>
      </form>
    </div>
  </div>
</div>

{{-- Confirm Bulk Delete Modal --}}
<div class="modal-overlay" id="bulkDeleteModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title"><i class="mdi mdi-alert-circle-outline mr-2"></i>Delete Selected Logs</h5>
      <button type="button" class="modal-close" onclick="closeBulkDeleteModal()">&times;</button>
    </div>
    <div class="modal-body">
      <p style="color: #333; font-size: 1rem;">Are you sure you want to delete the selected <strong id="bulkDeleteCount">0</strong> activity log(s)?</p>
      <p style="color: #666; font-size: 0.9rem;">This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeBulkDeleteModal()">Cancel</button>
      <form action="{{ route('activity-logs.bulk-delete') }}" method="POST" id="bulkDeleteForm">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulkDeleteIds" value="">
        <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete mr-1"></i> Delete</button>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // ========== Search clear ==========
  function clearSearch(inputId, formId) {
    document.getElementById(inputId).value = '';
    document.getElementById(formId).submit();
  }

  // ========== Select All / Checkbox Logic ==========
  const selectAllCheckbox = document.getElementById('selectAllLogs');
  const deleteBtn = document.getElementById('deleteLogBtn');
  const countSpan = document.getElementById('logCount');

  function updateDeleteButton() {
    const checked = document.querySelectorAll('.log-checkbox:checked');
    const count = checked.length;
    countSpan.textContent = count;
    deleteBtn.disabled = count === 0;
  }

  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      document.querySelectorAll('.log-checkbox').forEach(cb => {
        cb.checked = this.checked;
      });
      updateDeleteButton();
    });
  }

  document.querySelectorAll('.log-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
      const allBoxes = document.querySelectorAll('.log-checkbox');
      const allChecked = document.querySelectorAll('.log-checkbox:checked');
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = allBoxes.length === allChecked.length && allBoxes.length > 0;
      }
      updateDeleteButton();
    });
  });

  // ========== Bulk Delete ==========
  function bulkDeleteLogs() {
    const checked = document.querySelectorAll('.log-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    if (ids.length === 0) return;

    document.getElementById('bulkDeleteCount').textContent = ids.length;
    document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
    document.getElementById('bulkDeleteModal').classList.add('show');
  }

  function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.remove('show');
  }

  // ========== Clear All ==========
  function confirmClearAll() {
    document.getElementById('clearAllModal').classList.add('show');
  }

  function closeClearAllModal() {
    document.getElementById('clearAllModal').classList.remove('show');
  }

  // Close modals on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('show');
      }
    });
  });

  // Close modals on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
    }
  });
</script>
@endpush