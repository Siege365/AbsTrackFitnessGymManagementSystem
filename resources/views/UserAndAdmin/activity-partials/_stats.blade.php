<!-- Stats Cards -->
<div class="row">
  <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0">{{ number_format($totalLogs) }}</h2>
            <p class="text-muted mb-0">Total Logs</p>
          </div>
          <div class="stats-icon bg-primary">
            <i class="mdi mdi-format-list-bulleted" style="font-size: 24px;"></i>
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
            <h2 class="mb-0">{{ number_format($todayLogs) }}</h2>
            <p class="text-muted mb-0">Today's Activities</p>
          </div>
          <div class="stats-icon bg-success">
            <i class="mdi mdi-calendar-today" style="font-size: 24px;"></i>
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
            <h2 class="mb-0">{{ $uniqueUsers }}</h2>
            <p class="text-muted mb-0">Active Users</p>
          </div>
          <div class="stats-icon bg-info">
            <i class="mdi mdi-account-multiple" style="font-size: 24px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
