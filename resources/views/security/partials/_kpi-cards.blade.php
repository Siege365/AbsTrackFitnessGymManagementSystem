<!-- KPI Statistics Cards (Native AbsTrack Clean Design) -->
<div class="row">
    <!-- Health Score -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card security-stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="security-stats-value mb-0" id="kpiHealthScore">{{ $healthScore }}%</h2>
                        <p class="security-stats-label">System Health Score</p>
                    </div>
                    <div class="security-stats-icon bg-success">
                        <i class="mdi mdi-shield-check text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hardening Checks -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card security-stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="security-stats-value mb-0">{{ $passedAuditsCount }} / {{ $totalAuditsCount }}</h2>
                        <p class="security-stats-label">Passed Hardening Audits</p>
                    </div>
                    <div class="security-stats-icon bg-primary">
                        <i class="mdi mdi-check-decagram text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Threat Alerts -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card security-stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="security-stats-value mb-0">{{ $activeAlertsCount }}</h2>
                        <p class="security-stats-label">Active Threat Alerts</p>
                    </div>
                    <div class="security-stats-icon bg-warning">
                        <i class="mdi mdi-alert-circle text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OWASP Compliance -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card security-stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="security-stats-value mb-0">{{ $owaspScore }}%</h2>
                        <p class="security-stats-label">OWASP Top 10 Compliance</p>
                    </div>
                    <div class="security-stats-icon bg-info">
                        <i class="mdi mdi-security text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
