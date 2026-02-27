<!-- Today's Customers Table -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Today's Customers</h4>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('sessions.attendance.index') }}" method="GET" class="d-flex align-items-center"
                            id="attendanceSearchForm">
                            <input type="text" name="attendance_search" class="form-control form-control-sm mr-2"
                                placeholder="Search by name or contact..." value="{{ request('attendance_search') }}"
                                style="width: 100%; max-width: 450px;">
                            <div class="dropdown d-inline-block mr-2">
                                <button type="button" class="btn btn-sm filter-button dropdown-toggle"
                                    id="filterDropdownAttendance" data-toggle="dropdown" data-offset="0,2" data-flip="false"
                                    data-display="static" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-filter-variant"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-right filter-accordion" aria-labelledby="filterDropdownAttendance">
                                    <div class="filter-header">
                                        <span class="filter-title">Filter By</span>
                                        <a href="javascript:void(0)" class="filter-clear-all" onclick="SessionsPage.clearAllFilters()">
                                            Clear All
                                        </a>
                                    </div>
                                    
                                    <!-- Status Filter -->
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="SessionsPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-circle-outline"></i>
                                                <span>Status</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('attendance_status', 'all')">
                                                <i class="mdi mdi-check-all"></i> All
                                            </a>
                                            <a class="filter-option filter-option-active" href="javascript:void(0)" onclick="SessionsPage.applyFilter('attendance_status', 'active')">
                                                <i class="mdi mdi-check-circle"></i> Active
                                            </a>
                                            <a class="filter-option filter-option-expired" href="javascript:void(0)" onclick="SessionsPage.applyFilter('attendance_status', 'expired')">
                                                <i class="mdi mdi-close-circle"></i> Expired
                                            </a>
                                            <a class="filter-option filter-option-due-soon" href="javascript:void(0)" onclick="SessionsPage.applyFilter('attendance_status', 'due_soon')">
                                                <i class="mdi mdi-clock-alert"></i> Due Soon
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Sort Order Filter -->
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="SessionsPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-sort"></i>
                                                <span>Sort Order</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('attendance_sort', 'recent')">
                                                <i class="mdi mdi-sort-descending"></i> Recent First
                                            </a>
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('attendance_sort', 'oldest')">
                                                <i class="mdi mdi-sort-ascending"></i> Oldest First
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Customer Type Filter -->
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="SessionsPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-account-group"></i>
                                                <span>Customer Type</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('customer_type', 'all')">
                                                <i class="mdi mdi-check-all"></i> All
                                            </a>
                                            <a class="filter-option filter-option-member" href="javascript:void(0)" onclick="SessionsPage.applyFilter('customer_type', 'member')">
                                                <i class="mdi mdi-account-badge"></i> Member
                                            </a>
                                            <a class="filter-option filter-option-client" href="javascript:void(0)" onclick="SessionsPage.applyFilter('customer_type', 'client')">
                                                <i class="mdi mdi-account-tie"></i> Client
                                            </a>
                                            <a class="filter-option filter-option-walkin" href="javascript:void(0)" onclick="SessionsPage.applyFilter('customer_type', 'walkin')">
                                                <i class="mdi mdi-walk"></i> Walk-in
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" id="selectAllAttendance">
                                        </label>
                                    </div>
                                </th>
                                <th style="width: 340px;">Customer Name</th>
                                <th>Customer Type</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances ?? [] as $attendance)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input attendance-checkbox"
                                                    value="{{ $attendance->id }}">
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($attendance->active_avatar)
                                                <img src="{{ asset('storage/' . $attendance->active_avatar) }}"
                                                    class="avatar-circle mr-2" alt="Avatar">
                                            @else
                                                <div class="avatar-initial mr-2">
                                                    {{ strtoupper(substr($attendance->display_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span>{{ $attendance->display_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $customerType = $attendance->customer_type;
                                            $badgeClass = match($customerType) {
                                                'Member' => 'badge-member',
                                                'Client' => 'badge-client',
                                                'Walk-in' => 'badge-walkin',
                                                default => 'badge-info'
                                            };
                                            $iconClass = match($customerType) {
                                                'Member' => 'mdi-account-badge',
                                                'Client' => 'mdi-account-tie',
                                                'Walk-in' => 'mdi-walk',
                                                default => 'mdi-account'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            <i class="mdi {{ $iconClass }}"></i> {{ $customerType }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : 'N/A' }}
                                    </td>
                                    <td>
                                        @if($attendance->active_status)
                                            @php
                                                $status = $attendance->active_status;
                                                $statusClass = match ($status) {
                                                    'Expired' => 'badge-expired',
                                                    'Due soon' => 'badge-warning',
                                                    default => 'badge-active',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                <i class="mdi mdi-circle" style="font-size: 8px;"></i> {{ $status }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-action" type="button"
                                                data-toggle="dropdown" data-offset="-100,2" data-flip="false"
                                                data-boundary="viewport">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="SessionsPage.viewAttendance({{ $attendance->id }})">
                                                    <i class="mdi mdi-eye mr-2"></i> View Details
                                                </a>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                    onclick="SessionsPage.confirmDeleteAttendance({{ $attendance->id }}, '{{ addslashes($attendance->display_name) }}')">
                                                    <i class="mdi mdi-delete mr-2"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="mdi mdi-account-off" style="font-size: 48px; color: #6c757d;"></i>
                                        <p class="text-muted mt-2">No customers checked in today</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <form id="bulkDeleteAttendanceForm"
                        action="{{ route('sessions.attendance.bulk-delete') }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="SessionsPage.bulkDeleteAttendance()"
                            class="btn btn-sm btn-delete-selected">
                            <i class="mdi mdi-delete"></i> Delete Selected (<span
                                id="selectedAttendanceCount">0</span>)
                        </button>
                    </form>
                    {{ $attendances->appends(request()->except('attendance_page'))->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>
