<!-- Today's Customers Table -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Today's Customers</h4>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('Session') }}" method="GET" class="d-flex align-items-center"
                            id="attendanceSearchForm">
                            <input type="text" name="attendance_search" class="form-control form-control-sm mr-2"
                                placeholder="Search by name or contact..." value="{{ request('attendance_search') }}"
                                style="width: 450px;">
                            <div class="dropdown d-inline-block mr-2">
                                <button type="button" class="btn btn-sm filter-button dropdown-toggle"
                                    id="filterDropdown" data-toggle="dropdown" data-offset="0,2" data-flip="false"
                                    data-display="static">
                                    <i class="mdi mdi-filter-variant"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <h6 class="dropdown-header">Filter by Status</h6>
                                    <a class="dropdown-item" href="{{ route('Session') }}">All</a>
                                    <a class="dropdown-item"
                                        href="{{ route('Session', ['attendance_status' => 'active']) }}">
                                        <i class="mdi mdi-check-circle mr-2 text-success"></i> Active
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('Session', ['attendance_status' => 'expired']) }}">
                                        <i class="mdi mdi-close-circle mr-2 text-danger"></i> Expired
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('Session', ['attendance_status' => 'due_soon']) }}">
                                        <i class="mdi mdi-clock-alert mr-2 text-warning"></i> Due Soon
                                    </a>
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
                                <th>Name</th>
                                <th>Subscription Type</th>
                                <th>Customer Type</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Status</th>
                                <th>Contact #</th>
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
                                            @if ($attendance->client && $attendance->client->avatar)
                                                <img src="{{ asset('storage/' . $attendance->client->avatar) }}"
                                                    class="avatar-circle mr-2" alt="Avatar">
                                            @else
                                                <div class="avatar-initial mr-2">
                                                    {{ strtoupper(substr($attendance->display_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span>{{ $attendance->display_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $attendance->client->plan_type ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info" style="font-size: 0.85rem;">
                                            {{ $attendance->customer_type_display }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td>{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            $status = $attendance->client->status ?? 'Active';
                                            $statusClass = match ($status) {
                                                'Expired' => 'badge-expired',
                                                'Due soon' => 'badge-warning',
                                                default => 'badge-active',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            <i class="mdi mdi-circle" style="font-size: 8px;"></i> {{ $status }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->client->contact ?? $attendance->customer_contact ?? 'N/A' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-action" type="button"
                                                data-toggle="dropdown" data-offset="-100,2" data-flip="false"
                                                data-display="static">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="SessionsPage.viewAttendance({{ $attendance->id }})">
                                                    <i class="mdi mdi-eye mr-2"></i> View
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
