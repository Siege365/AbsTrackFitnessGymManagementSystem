<!-- Stats Cards -->
<div class="row">
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0" data-kpi-value="{{ $membershipIncome ?? 0 }}" data-kpi-type="currency">₱{{ number_format($membershipIncome ?? 0, 2) }}</h2>
            <p class="text-muted mb-0">Membership Income</p>
          </div>
          <div class="stats-icon bg-danger">
            <i class="mdi mdi-account-group"></i>
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
            <h2 class="mb-0" data-kpi-value="{{ $ptIncome ?? 0 }}" data-kpi-type="currency">₱{{ number_format($ptIncome ?? 0, 2) }}</h2>
            <p class="text-muted mb-0">PT Income</p>
          </div>
          <div class="stats-icon bg-primary">
            <i class="mdi mdi-dumbbell"></i>
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
            <h2 class="mb-0" data-kpi-value="{{ $productIncome ?? 0 }}" data-kpi-type="currency">₱{{ number_format($productIncome ?? 0, 2) }}</h2>
            <p class="text-muted mb-0">Product Income</p>
          </div>
          <div class="stats-icon bg-warning">
            <i class="mdi mdi-basket text-white"></i>
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
            <h2 class="mb-0" data-kpi-value="{{ $refundedTotal ?? 0 }}" data-kpi-type="currency">₱{{ number_format($refundedTotal ?? 0, 2) }}</h2>
            <p class="text-muted mb-0">Total Refunded</p>
          </div>
          <div class="stats-icon bg-info">
            <i class="mdi mdi-cash-refund text-white"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
