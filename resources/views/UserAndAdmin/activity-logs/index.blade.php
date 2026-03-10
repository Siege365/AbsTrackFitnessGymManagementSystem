@extends('layouts.admin')

@section('title', 'Activity Logs')

@push('styles')
@vite(['resources/css/staff-management.css'])
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

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $totalLogs }}</h2>
                        <p class="text-muted mb-0">Total Logs</p>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="mdi mdi-file-document text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0">{{ $todayLogs }}</h2>
                        <p class="text-muted mb-0">Today's Activity</p>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="mdi mdi-calendar-today text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0">{{ $uniqueActions }}</h2>
                        <p class="text-muted mb-0">Unique Actions</p>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="mdi mdi-format-list-bulleted text-white" style="font-size: 24px;"></i>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Activity Logs</h4>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('activity-logs.index') }}" method="GET" class="d-flex align-items-center" id="searchFormLogs">
                            <div class="search-wrapper mr-2">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search by user, action, description..."
                                    value="{{ request('search') }}"
                                    style="width: 100%; max-width: 350px;"
                                    id="searchInputLogs">
                            </div>
                            <div class="dropdown d-inline-block mr-2">
                                <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-filter-variant"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-right filter-accordion">
                                    <div class="filter-header">
                                        <span class="filter-title">Filter By</span>
                                        <a href="{{ route('activity-logs.index') }}" class="filter-clear-all">Clear All</a>
                                    </div>
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="toggleFilterSection(this)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-account"></i>
                                                <span>Staff Member</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            @foreach($staffUsers as $staffUser)
                                            <a class="filter-option" href="{{ route('activity-logs.index', ['user' => $staffUser->id]) }}">
                                                <i class="mdi mdi-account"></i> {{ $staffUser->name }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(request('search') || request('action') || request('user'))
                                <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-close"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if(request('search'))
                <div class="search-info p-3 mb-3">
                    <p class="mb-0">
                        <i class="mdi mdi-information"></i>
                        Showing {{ $logs->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
                    </p>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Log#</th>
                                <th>Staff Name</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ str_pad($log->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial mr-2" style="width:30px; height:30px; font-size:12px;">
                                            {{ strtoupper(substr($log->user_name, 0, 1)) }}
                                        </div>
                                        <span>{{ $log->user_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-action-{{ $log->action }}">
                                        {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($log->description, 60) }}</td>
                                <td><code>{{ $log->ip_address }}</code></td>
                                <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="mdi mdi-file-document-outline" style="font-size: 48px; color: #555;"></i>
                                    <p class="text-muted mt-2">No activity logs found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInputLogs');
    const searchForm = document.getElementById('searchFormLogs');
    if (searchInput && searchForm) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); searchForm.submit(); }
        });
    }
});

function toggleFilterSection(header) {
    const section = header.closest('.filter-section');
    const chevron = header.querySelector('.filter-chevron');

    section.classList.toggle('active');

    if (section.classList.contains('active')) {
        chevron.style.transform = 'rotate(180deg)';
    } else {
        chevron.style.transform = 'rotate(0deg)';
    }
}
</script>
@endpush
