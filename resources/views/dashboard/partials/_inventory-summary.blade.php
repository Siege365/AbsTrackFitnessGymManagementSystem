{{-- Inventory Subsystem Summary Card --}}
<div class="card subsystem-card">
    <div class="card-body">
        <div class="subsystem-header">
            <div class="subsystem-icon bg-warning-soft">
                <i class="mdi mdi-package-variant-closed text-warning"></i>
            </div>
            <div>
                <h5 class="subsystem-title mb-0">Inventory & Supplies</h5>
                <p class="text-muted mb-0 subsystem-subtitle">Stock management</p>
            </div>
            <a href="{{ route('inventory.index') }}" class="subsystem-link ml-auto">
                View All <i class="mdi mdi-arrow-right"></i>
            </a>
        </div>
        <div class="subsystem-metrics">
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $totalProducts }}">{{ $totalProducts }}</span>
                <span class="metric-label">Products</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value {{ $lowStockCount > 0 ? 'text-warning' : 'text-success' }}" data-kpi-value="{{ $lowStockCount }}">{{ $lowStockCount }}</span>
                <span class="metric-label">Low Stock</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value {{ $outOfStock > 0 ? 'text-danger' : 'text-success' }}" data-kpi-value="{{ $outOfStock }}">{{ $outOfStock }}</span>
                <span class="metric-label">Out of Stock</span>
            </div>
            <div class="metric-divider"></div>
            <div class="metric-item">
                <span class="metric-value" data-kpi-value="{{ $stockValue }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($stockValue, 2) }}</span>
                <span class="metric-label">Stock Value</span>
            </div>
        </div>
    </div>
</div>
