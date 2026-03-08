{{-- Payments Subsystem Summary Card --}}
<div class="card subsystem-card">
    <div class="card-body">
        <div class="subsystem-header">
            <div class="subsystem-icon bg-success-soft">
                <i class="mdi mdi-cash-multiple text-success"></i>
            </div>
            <div>
                <h5 class="subsystem-title mb-0">Payments & Billing</h5>
                <p class="text-muted mb-0 subsystem-subtitle">Revenue & transactions</p>
            </div>
            <a href="{{ route('payments.membership') }}" class="subsystem-link ml-auto">
                View All <i class="mdi mdi-arrow-right"></i>
            </a>
        </div>
        <div class="subsystem-metrics">
            <div class="metric-item">
                <span class="metric-value text-success" data-kpi-value="{{ $monthlyRevenue }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($monthlyRevenue, 2) }}</span>
                <span class="metric-label">Monthly Revenue</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $todayRevenue }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($todayRevenue, 2) }}</span>
                <span class="metric-label">Today</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $todayTransactions }}">{{ $todayTransactions }}</span>
                <span class="metric-label">Transactions</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value {{ $pendingRefunds > 0 ? 'text-warning' : '' }}" data-kpi-value="{{ $pendingRefunds }}">{{ $pendingRefunds }}</span>
                <span class="metric-label">Partial Refunds</span>
            </div>
        </div>
    </div>
</div>
