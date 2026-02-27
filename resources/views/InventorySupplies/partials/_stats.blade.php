<!-- KPI Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="all">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">{{ $totalProducts ?? 0 }}</h2>
              <p class="text-muted mb-0">Total Products</p>
            </div>
            <div class="stats-icon bg-info">
              <i class="mdi mdi-package-variant text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="low_stock">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">{{ $lowStockItems ?? 0 }}</h2>
              <p class="text-muted mb-0">Low Stock Items</p>
            </div>
            <div class="stats-icon bg-warning">
              <i class="mdi mdi-alert-outline text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="out_of_stock">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">{{ $outOfStockItems ?? 0 }}</h2>
              <p class="text-muted mb-0">Out Of Stock Items</p>
            </div>
            <div class="stats-icon bg-danger">
              <i class="mdi mdi-close-circle-outline text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="all">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($stockValue ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Stock Value</p>
            </div>
            <div class="stats-icon bg-success">
              <i class="mdi mdi-cash-multiple text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
