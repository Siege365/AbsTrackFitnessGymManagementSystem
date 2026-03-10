{{-- Top-Level KPIs: The 4 most critical numbers at a glance --}}
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="kpi-label mb-1">Active Members & Clients</p>
                        <h2 class="kpi-value mb-0" data-kpi-value="{{ $totalActiveMembers }}">{{ $totalActiveMembers }}</h2>
                    </div>
                    <div class="kpi-icon bg-success-soft">
                        <i class="mdi mdi-account-group text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="kpi-label mb-1">Today's Revenue</p>
                        <h2 class="kpi-value mb-0" data-kpi-value="{{ $todayRevenue }}" data-kpi-type="currency" data-kpi-currency="₱">₱{{ number_format($todayRevenue, 2) }}</h2>
                    </div>
                    <div class="kpi-icon bg-primary-soft">
                        <i class="mdi mdi-cash-register text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="kpi-label mb-1">Today's Attendance</p>
                        <h2 class="kpi-value mb-0" data-kpi-value="{{ $todayAttendance }}">{{ $todayAttendance }}</h2>
                    </div>
                    <div class="kpi-icon bg-info-soft">
                        <i class="mdi mdi-login text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card kpi-card {{ $attentionItems > 0 ? 'kpi-attention' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="kpi-label mb-1">Action Required</p>
                        <h2 class="kpi-value mb-0 {{ $attentionItems > 0 ? 'text-warning' : 'text-success' }}" data-kpi-value="{{ $attentionItems }}">{{ $attentionItems }}</h2>
                    </div>
                    <div class="kpi-icon {{ $attentionItems > 0 ? 'bg-warning-soft' : 'bg-success-soft' }}">
                        <i class="mdi {{ $attentionItems > 0 ? 'mdi-alert-circle text-warning' : 'mdi-check-circle text-success' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
