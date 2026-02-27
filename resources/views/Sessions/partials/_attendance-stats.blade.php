<!-- Attendance Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0" id="kpi_customers_today">{{ $customersEnteredToday ?? 0 }}</h2>
                        <p class="text-muted mb-0">Customers Today</p>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="mdi mdi-account-check text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0" id="kpi_members_today">{{ $membersToday ?? 0 }}</h2>
                        <p class="text-muted mb-0">Members Today</p>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="mdi mdi-account-star text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0" id="kpi_walkins_today">{{ $walkInsToday ?? 0 }}</h2>
                        <p class="text-muted mb-0">Walk-ins Today</p>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="mdi mdi-walk text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0" id="kpi_total_month">{{ $totalThisMonth ?? 0 }}</h2>
                        <p class="text-muted mb-0">Total This Month</p>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="mdi mdi-calendar-month text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
