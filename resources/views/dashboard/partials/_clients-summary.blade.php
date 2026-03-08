{{-- Clients Subsystem Summary Card --}}
<div class="card subsystem-card">
    <div class="card-body">
        <div class="subsystem-header">
            <div class="subsystem-icon bg-danger-soft">
                <i class="mdi mdi-account-multiple text-danger"></i>
            </div>
            <div>
                <h5 class="subsystem-title mb-0">Clients</h5>
                <p class="text-muted mb-0 subsystem-subtitle">Walk-in & PT clients</p>
            </div>
            <a href="{{ route('clients.index') }}" class="subsystem-link ml-auto">
                View All <i class="mdi mdi-arrow-right"></i>
            </a>
        </div>
        <div class="subsystem-metrics">
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $totalClients }}">{{ $totalClients }}</span>
                <span class="metric-label">Total</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value text-success" data-kpi-value="{{ $activeClients }}">{{ $activeClients }}</span>
                <span class="metric-label">Active</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value {{ $expiringClients > 0 ? 'text-warning' : '' }}" data-kpi-value="{{ $expiringClients }}">{{ $expiringClients }}</span>
                <span class="metric-label">Expiring</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value text-info" data-kpi-value="{{ $newClientsThisMonth }}">{{ $newClientsThisMonth }}</span>
                <span class="metric-label">New</span>
            </div>
        </div>
    </div>
</div>
