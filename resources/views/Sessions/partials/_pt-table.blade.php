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
                                    id="filterDropdownPT" data-toggle="dropdown" data-offset="0,2" data-flip="false"
                                    data-display="static" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-filter-variant"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-right filter-accordion" aria-labelledby="filterDropdownPT">
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
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_status', 'all')">
                                                <i class="mdi mdi-check-all"></i> All
                                            </a>
                                            <a class="filter-option filter-option-done" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_status', 'done')">
                                                <i class="mdi mdi-check-circle"></i> Done
                                            </a>
                                            <a class="filter-option filter-option-in-progress" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_status', 'in_progress')">
                                                <i class="mdi mdi-progress-clock"></i> In Progress
                                            </a>
                                            <a class="filter-option filter-option-upcoming" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_status', 'upcoming')">
                                                <i class="mdi mdi-clock"></i> Upcoming
                                            </a>
                                            <a class="filter-option filter-option-cancelled" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_status', 'cancelled')">
                                                <i class="mdi mdi-close-circle"></i> Cancelled
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
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_sort', 'default')">
                                                <i class="mdi mdi-sort-variant"></i> Default
                                            </a>
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_sort', 'recent')">
                                                <i class="mdi mdi-sort-descending"></i> Recent First
                                            </a>
                                            <a class="filter-option" href="javascript:void(0)" onclick="SessionsPage.applyFilter('pt_sort', 'oldest')">
                                                <i class="mdi mdi-sort-ascending"></i> Oldest First
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Date Filter -->
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="SessionsPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-calendar"></i>
                                                <span>Date</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <div class="filter-date-picker">
                                                <input type="date" id="pt_date_filter" class="filter-date-input" 
                                                    value="{{ request('pt_date') }}" 
                                                    onchange="SessionsPage.applyDateFilter('pt_date', this.value)">
                                                @if(request('pt_date'))
                                                <button type="button" class="btn-clear-date" onclick="SessionsPage.clearDateFilter('pt_date')">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                                @endif
                                            </div>
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
                                <th style="width: 120px;">Actions</th>
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
                                            @php
                                                $avatar = null;
                                                if ($schedule->customer_source === 'membership' && $schedule->membership) {
                                                    $avatar = $schedule->membership->avatar;
                                                } elseif ($schedule->customer_source === 'client' && $schedule->client) {
                                                    $avatar = $schedule->client->avatar;
                                                }
                                            @endphp
                                            
                                            @if ($avatar)
                                                <img src="{{ asset('storage/' . $avatar) }}"
                                                    alt="Avatar" class="avatar-circle mr-2">
                                            @else
                                                <div class="avatar-circle avatar-initial mr-2">
                                                    {{ strtoupper(substr($schedule->display_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span>{{ $schedule->display_name }}</span>
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
                                                'in_progress' => 'badge-in-progress',
                                                'cancelled' => 'badge-expired',
                                                default => 'badge-due-soon',
                                            };
                                            $statusText = match ($schedule->status) {
                                                'in_progress' => 'In Progress',
                                                default => ucfirst($schedule->status),
                                            };
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
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="SessionsPage.viewPTSchedule({{ $schedule->id }})">
                                                    <i class="mdi mdi-eye mr-2"></i> View
                                                </a>
                                                <a class="dropdown-item text-info" href="javascript:void(0)"
                                                    onclick="SessionsPage.openBookNextModal({{ $schedule->client_id ?? 'null' }}, '{{ addslashes($schedule->display_name) }}')">
                                                    <i class="mdi mdi-calendar-plus mr-2"></i> Book Next Session
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                @php
                                                    $isFinalized = in_array($schedule->status, ['done', 'cancelled']);
                                                @endphp
                                                <a class="dropdown-item text-success {{ $isFinalized ? 'disabled' : '' }}" 
                                                    href="javascript:void(0)"
                                                    onclick="{{ $isFinalized ? 'return false;' : 'SessionsPage.updateStatus(' . $schedule->id . ', \'done\')' }}">
                                                    <i class="mdi mdi-check mr-2"></i> Mark as Done
                                                </a>
                                                <a class="dropdown-item text-primary {{ $isFinalized ? 'disabled' : '' }}" 
                                                    href="javascript:void(0)"
                                                    onclick="{{ $isFinalized ? 'return false;' : 'SessionsPage.updateStatus(' . $schedule->id . ', \'in_progress\')' }}">
                                                    <i class="mdi mdi-progress-clock mr-2"></i> Mark as In Progress
                                                </a>
                                                <a class="dropdown-item text-warning {{ $isFinalized ? 'disabled' : '' }}" 
                                                    href="javascript:void(0)"
                                                    onclick="{{ $isFinalized ? 'return false;' : 'SessionsPage.confirmCancelPT(' . $schedule->id . ', ' . ($schedule->client_id ?? 'null') . ', \'' . addslashes($schedule->display_name) . '\')' }}">
                                                    <i class="mdi mdi-close mr-2"></i> Mark as Cancelled
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                    onclick="SessionsPage.confirmDeletePT({{ $schedule->id }}, '{{ addslashes($schedule->display_name) }}')">
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
