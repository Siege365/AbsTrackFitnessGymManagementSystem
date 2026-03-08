{{-- Memberships Subsystem Summary Card --}}
<div class="card subsystem-card">
    <div class="card-body">
        <div class="subsystem-header">
            <div class="subsystem-icon bg-primary-soft">
                <i class="mdi mdi-card-account-details text-primary"></i>
            </div>
            <div>
                <h5 class="subsystem-title mb-0">Memberships</h5>
                <p class="text-muted mb-0 subsystem-subtitle">Gym membership plans</p>
            </div>
            <a href="{{ route('memberships.index') }}" class="subsystem-link ml-auto">
                View All <i class="mdi mdi-arrow-right"></i>
            </a>
        </div>
        <div class="subsystem-metrics">
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $totalMemberships }}">{{ $totalMemberships }}</span>
                <span class="metric-label">Total</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value text-success" data-kpi-value="{{ $activeMemberships }}">{{ $activeMemberships }}</span>
                <span class="metric-label">Active</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value {{ $expiringMemberships > 0 ? 'text-warning' : '' }}" data-kpi-value="{{ $expiringMemberships }}">{{ $expiringMemberships }}</span>
                <span class="metric-label">Expiring</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value text-info" data-kpi-value="{{ $newMembershipsThisMonth }}">{{ $newMembershipsThisMonth }}</span>
                <span class="metric-label">New</span>
            </div>
        </div>
    </div>
</div>
