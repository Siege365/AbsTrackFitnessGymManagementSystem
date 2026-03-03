<!-- Staff KPI Stats -->
<div class="row stats-row">
    <div class="col-md-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <h2 class="mb-1" id="kpiTotalStaff">{{ $totalStaff }}</h2>
                <p class="text-muted mb-0">Total Staff</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <h2 class="mb-1" id="kpiTotalAdmin">{{ $totalAdmin }}</h2>
                <p class="text-muted mb-0">Admins</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <h2 class="mb-1" id="kpiNewThisMonth">{{ $newThisMonth }}</h2>
                <p class="text-muted mb-0">New This Month</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <h2 class="mb-1" id="kpiTotalAccounts">{{ $totalStaff + $totalAdmin }}</h2>
                <p class="text-muted mb-0">Total Accounts</p>
            </div>
        </div>
    </div>
</div>
