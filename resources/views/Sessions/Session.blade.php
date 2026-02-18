@extends('layouts.admin')

@section('title', 'Sessions - AbsTrack Fitness Gym')

@push('styles')
    @vite(['resources/css/sessions.css'])
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@section('content')

    <!-- Session Flash Messages -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                SessionsPage.showToast('success', '{{ session('success') }}');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                SessionsPage.showToast('error', '{{ session('error') }}');
            });
        </script>
    @endif

    <!-- Page Header -->
    <div class="card page-header-card">
        <div class="card-body">
            <div>
                <h2 class="page-header-title">Session Management</h2>
                <p class="page-header-subtitle">Manage personal training schedules and customer attendance.</p>
            </div>
            <div class="dropdown">
                <button class="btn btn-page-action dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="mdi mdi-plus"></i> Add Schedule
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addAttendanceModal">
                        <i class="mdi mdi-account-check mr-2 text-success"></i> Customer Attendance
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addPTScheduleModal">
                        <i class="mdi mdi-calendar-plus mr-2 text-primary"></i> PT Session Schedule
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_pt_sessions_today">{{ $ptSessionsToday ?? 0 }}</h2>
                            <p class="text-muted mb-0">PT Sessions Today</p>
                        </div>
                        <div class="stats-icon bg-primary">
                            <i class="mdi mdi-calendar-clock text-white" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_upcoming_pt">{{ $upcomingPTSessions ?? 0 }}</h2>
                            <p class="text-muted mb-0">Upcoming PT Sessions</p>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="mdi mdi-clock-outline text-white" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_pt_cancellations">{{ $ptCancellations ?? 0 }}</h2>
                            <p class="text-muted mb-0">PT Cancellations</p>
                        </div>
                        <div class="stats-icon bg-danger">
                            <i class="mdi mdi-close-circle text-white" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_customers_today">{{ $customersEnteredToday ?? 0 }}</h2>
                            <p class="text-muted mb-0">Customers Entered Today</p>
                            @if (isset($customerPercentChange) && $customerPercentChange != 0)
                                <small class="{{ $customerPercentChange > 0 ? 'text-success' : 'text-danger' }}" id="kpi_customer_percent">
                                    {{ $customerPercentChange > 0 ? '+' : '' }}{{ abs($customerPercentChange) }}% Since yesterday
                                </small>
                            @endif
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="mdi mdi-account-check text-white" style="font-size: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Training Schedules Table -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Personal Training Schedules</h4>
                        <div class="d-flex align-items-center">
                            <form action="{{ route('Session') }}" method="GET" class="d-flex align-items-center"
                                id="ptSearchForm">
                                <input type="text" name="pt_search" class="form-control form-control-sm mr-2"
                                    placeholder="Search by client or trainer..." value="{{ request('pt_search') }}"
                                    style="width: 450px;">
                                <div class="dropdown d-inline-block mr-2">
                                    <button type="button" class="btn btn-sm filter-button dropdown-toggle"
                                        data-toggle="dropdown" data-offset="0,2" data-flip="false"
                                        data-display="static">
                                        <i class="mdi mdi-filter-variant"></i> Filter
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <h6 class="dropdown-header">Filter by Status</h6>
                                        <a class="dropdown-item" href="{{ route('Session') }}">All</a>
                                        <a class="dropdown-item" href="{{ route('Session', ['pt_status' => 'done']) }}">
                                            <i class="mdi mdi-check-circle mr-2 text-success"></i> Done
                                        </a>
                                        <a class="dropdown-item"
                                            href="{{ route('Session', ['pt_status' => 'upcoming']) }}">
                                            <i class="mdi mdi-clock mr-2 text-warning"></i> Upcoming
                                        </a>
                                        <a class="dropdown-item"
                                            href="{{ route('Session', ['pt_status' => 'cancelled']) }}">
                                            <i class="mdi mdi-close-circle mr-2 text-danger"></i> Cancelled
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
                                                <input type="checkbox" class="form-check-input" id="selectAllPT">
                                            </label>
                                        </div>
                                    </th>
                                    <th>Client Name</th>
                                    <th>Trainer</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Payment Type</th>
                                    <th>Status</th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ptSchedules ?? [] as $schedule)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input pt-checkbox"
                                                        value="{{ $schedule->id }}">
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($schedule->client && $schedule->client->avatar)
                                                    <img src="{{ asset('storage/' . $schedule->client->avatar) }}"
                                                        alt="Avatar" class="avatar-circle mr-2">
                                                @else
                                                    <div class="avatar-circle avatar-initial mr-2">
                                                        {{ strtoupper(substr($schedule->client->name ?? 'N', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span>{{ $schedule->client->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $schedule->trainer_name }}</td>
                                        <td>{{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td>{{ $schedule->scheduled_time ? \Carbon\Carbon::parse($schedule->scheduled_time)->format('h:i A') : 'N/A' }}
                                        </td>
                                        <td>{{ $schedule->payment_type ?? 'Cash' }}</td>
                                        <td>
                                            @php
                                                $statusClass = match ($schedule->status) {
                                                    'done' => 'badge-active',
                                                    'cancelled' => 'badge-expired',
                                                    default => 'badge-due-soon',
                                                };
                                                $statusText = ucfirst($schedule->status);
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                <i class="mdi mdi-circle" style="font-size: 8px;"></i>
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-action" type="button"
                                                    data-toggle="dropdown" data-offset="-100,2" data-flip="false"
                                                    data-display="static">
                                                    <i class="mdi mdi-dots-horizontal"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        onclick="SessionsPage.viewPTSchedule({{ $schedule->id }})">
                                                        <i class="mdi mdi-eye mr-2"></i> View
                                                    </a>
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        onclick="SessionsPage.editPTSchedule({{ $schedule->id }})">
                                                        <i class="mdi mdi-pencil mr-2"></i> Reschedule
                                                    </a>
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        onclick="SessionsPage.openBookNextModal({{ $schedule->client_id }}, '{{ $schedule->client->name ?? '' }}')">
                                                        <i class="mdi mdi-calendar-plus mr-2"></i> Book Next Session
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        onclick="SessionsPage.updateStatus({{ $schedule->id }}, 'done')">
                                                        <i class="mdi mdi-check mr-2 text-success"></i> Mark as Done
                                                    </a>
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        onclick="SessionsPage.updateStatus({{ $schedule->id }}, 'cancelled')">
                                                        <i class="mdi mdi-close mr-2 text-danger"></i> Mark as Cancelled
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                        onclick="SessionsPage.confirmDeletePT({{ $schedule->id }}, '{{ $schedule->client->name ?? '' }}')">
                                                        <i class="mdi mdi-delete mr-2"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="mdi mdi-calendar-blank"
                                                style="font-size: 48px; color: #6c757d;"></i>
                                            <p class="text-muted mt-2">No PT schedules found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <form id="bulkDeletePTForm" action="{{ route('sessions.pt.bulk-delete') }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="SessionsPage.bulkDeletePT()"
                                class="btn btn-sm btn-delete-selected">
                                <i class="mdi mdi-delete"></i> Delete Selected (<span
                                    id="selectedPTCount">0</span>)
                            </button>
                        </form>
                        {{ $ptSchedules->appends(request()->except('pt_page'))->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                    <th>ID#</th>
                                    <th>Name</th>
                                    <th>Plan Type</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Status</th>
                                    <th>Contact #</th>
                                    <th style="width: 80px;">Actions</th>
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
                                        <td>{{ $attendance->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($attendance->client && $attendance->client->avatar)
                                                    <img src="{{ asset('storage/' . $attendance->client->avatar) }}"
                                                        class="avatar-circle mr-2" alt="Avatar">
                                                @else
                                                    <div class="avatar-initial mr-2">
                                                        {{ strtoupper(substr($attendance->client->name ?? 'N', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span>{{ $attendance->client->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $attendance->client->plan_type ?? 'N/A' }}</td>
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
                                        <td>{{ $attendance->client->contact ?? 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-action" type="button"
                                                    data-toggle="dropdown" data-offset="-100,2" data-flip="false"
                                                    data-display="static">
                                                    <i class="mdi mdi-dots-horizontal"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                        onclick="SessionsPage.viewAttendance({{ $attendance->id }})">
                                                        <i class="mdi mdi-eye mr-2"></i> View
                                                    </a>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                        onclick="SessionsPage.confirmDeleteAttendance({{ $attendance->id }}, '{{ $attendance->client->name ?? '' }}')">
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

    <!-- Add PT Schedule Modal -->
    <div class="modal fade" id="addPTScheduleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add PT Schedule</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addPTScheduleForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <select name="client_id" id="pt_client_id" class="form-control" required>
                                        <option value="">Select Client</option>
                                        @foreach ($clients ?? [] as $client)
                                            <option value="{{ $client->id }}" data-age="{{ $client->age }}"
                                                data-contact="{{ $client->contact }}"
                                                data-plan="{{ $client->plan_type }}"
                                                data-start="{{ $client->start_date ? $client->start_date->format('Y-m-d') : '' }}"
                                                data-end="{{ $client->due_date ? $client->due_date->format('Y-m-d') : '' }}"
                                                data-avatar="{{ $client->avatar }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="text" class="form-control" id="pt_age" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" class="form-control" id="pt_contact" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Membership Plan</label>
                                    <input type="text" class="form-control" id="pt_plan" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" class="form-control" id="pt_start_date" readonly>
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" class="form-control" id="pt_end_date" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Avatar (optional)</label>
                                    <div id="pt_avatar_preview" class="avatar-preview-container"></div>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trainer</label>
                                    <select name="trainer_name" class="form-control" required>
                                        <option value="">Select Trainer</option>
                                        @foreach ($trainers ?? [] as $trainer)
                                            <option value="{{ $trainer }}">{{ $trainer }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="scheduled_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Time</label>
                                    <select name="scheduled_time" class="form-control" required>
                                        <option value="">Select Time</option>
                                        <option value="08:00">8:00 AM</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Type</label>
                                    <select name="payment_type" class="form-control" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Gcash">Gcash</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View/Edit PT Schedule Modal -->
    <div class="modal fade" id="viewEditPTModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewEditPTTitle">View PT Schedule</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editPTScheduleForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="pt_id" id="edit_pt_id">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="edit_pt_name" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="text" class="form-control" id="edit_pt_age" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" class="form-control" id="edit_pt_contact" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Membership Plan</label>
                                    <input type="text" class="form-control" id="edit_pt_plan" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" class="form-control" id="edit_pt_start" readonly>
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" class="form-control" id="edit_pt_end" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Avatar</label>
                                    <div id="edit_pt_avatar_preview" class="avatar-preview-container"></div>
                                </div>
                            </div>
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trainer</label>
                                    <select name="trainer_name" id="edit_trainer" class="form-control edit-field"
                                        disabled>
                                        @foreach ($trainers ?? [] as $trainer)
                                            <option value="{{ $trainer }}">{{ $trainer }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="scheduled_date" id="edit_date"
                                        class="form-control edit-field" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Time</label>
                                    <select name="scheduled_time" id="edit_time" class="form-control edit-field"
                                        disabled>
                                        <option value="08:00">8:00 AM</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Type</label>
                                    <select name="payment_type" id="edit_payment" class="form-control edit-field"
                                        disabled>
                                        <option value="Cash">Cash</option>
                                        <option value="Gcash">Gcash</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <input type="text" class="form-control" id="edit_pt_status" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="enableEditBtn"
                            onclick="SessionsPage.enableEdit()">Edit</button>
                        <button type="submit" class="btn btn-success d-none" id="saveEditBtn">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Book Next Session Modal -->
    <div class="modal fade" id="bookNextModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Next Session</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="bookNextForm">
                    @csrf
                    <input type="hidden" name="client_id" id="book_client_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Client Name</label>
                            <input type="text" class="form-control" id="book_client_name" readonly>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="scheduled_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Time</label>
                            <select name="scheduled_time" class="form-control" required>
                                <option value="08:00">8:00 AM</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="17:00">5:00 PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="modal fade" id="addAttendanceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Customer Attendance</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addAttendanceForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Client <span class="text-danger">*</span></label>
                            <select name="client_id" id="attendance_client_id" class="form-control" required>
                                <option value="">Search and select client...</option>
                                @foreach ($clients ?? [] as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" id="attendance_date" class="form-control" 
                                value="{{ date('Y-m-d') }}" readonly required>
                            <small class="text-muted">Auto-set to today's date</small>
                        </div>
                        <div class="form-group">
                            <label>Time In</label>
                            <input type="time" name="time_in" id="attendance_time" class="form-control" 
                                value="{{ date('H:i') }}" readonly required>
                            <small class="text-muted">Auto-set to current time</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmText">Are you sure you want to delete this record?</p>
                    <input type="hidden" id="deleteType">
                    <input type="hidden" id="deleteId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="SessionsPage.executeDelete()">Delete</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Double Confirmation Modal -->
    <div class="modal fade" id="doubleConfirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Final Confirmation</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Type "<strong id="confirmName"></strong>" to confirm deletion:</p>
                    <input type="text" class="form-control" id="confirmInput" placeholder="Type name to confirm">
                    <small class="text-danger d-none" id="confirmError">Name doesn't match. Please try again.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="finalDeleteBtn" disabled
                        onclick="SessionsPage.finalDelete()">Delete Permanently</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite(['resources/js/common/form-utils.js'])
    @vite(['resources/js/common/table-dropdown.js'])
    @vite(['resources/js/sessions.js'])
@endpush
