{{-- Reports & Analytics Subsystem Summary Card --}}
<div class="card subsystem-card">
    <div class="card-body">
        <div class="subsystem-header">
            <div class="subsystem-icon bg-green-soft">
                <i class="mdi mdi-chart-bar text-green"></i>
            </div>
            <div>
                <h5 class="subsystem-title mb-0">Reports & Analytics</h5>
                <p class="text-muted mb-0 subsystem-subtitle">Business analytics</p>
            </div>
            <a href="{{ route('reports.index') }}" class="subsystem-link ml-auto">
                View All <i class="mdi mdi-arrow-right"></i>
            </a>
        </div>
        <div class="subsystem-metrics">
            <div class="metric-item">
                <span class="metric-value text-success" data-kpi-value="{{ $totalRevenueThisMonth }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($totalRevenueThisMonth, 2) }}</span>
                <span class="metric-label">Total</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $retailSalesThisMonth }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($retailSalesThisMonth, 2) }}</span>
                <span class="metric-label">Retail</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $membershipRevenueThisMonth }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($membershipRevenueThisMonth, 2) }}</span>
                <span class="metric-label">Members</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $ptRevenueThisMonth }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($ptRevenueThisMonth, 2) }}</span>
                <span class="metric-label">PT</span>
            </div>
        </div>
    </div>
</div>
