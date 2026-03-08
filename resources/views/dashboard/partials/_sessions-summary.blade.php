{{-- Sessions Subsystem Summary Card --}}
<div class="card subsystem-card">
    <div class="card-body">
        <div class="subsystem-header">
            <div class="subsystem-icon bg-info-soft">
                <i class="mdi mdi-calendar-clock text-info"></i>
            </div>
            <div>
                <h5 class="subsystem-title mb-0">Sessions & Attendance</h5>
                <p class="text-muted mb-0 subsystem-subtitle">PT schedules & check-ins</p>
            </div>
            <a href="{{ route('sessions.pt.index') }}" class="subsystem-link ml-auto">
                View All <i class="mdi mdi-arrow-right"></i>
            </a>
        </div>
        <div class="subsystem-metrics">
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $ptSessionsToday }}">{{ $ptSessionsToday }}</span>
                <span class="metric-label">PT Today</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value text-success" data-kpi-value="{{ $completedPTToday }}">{{ $completedPTToday }}</span>
                <span class="metric-label">Completed</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value text-info" data-kpi-value="{{ $upcomingPT }}">{{ $upcomingPT }}</span>
                <span class="metric-label">Upcoming</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $walkInsToday }}">{{ $walkInsToday }}</span>
                <span class="metric-label">Walk-ins</span>
            </div>
        </div>
    </div>
</div>
