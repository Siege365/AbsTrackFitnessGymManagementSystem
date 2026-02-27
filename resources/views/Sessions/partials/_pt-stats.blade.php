<!-- PT Sessions Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0" id="kpi_pt_sessions_today">{{ $ptSessionsToday ?? 0 }}</h2>
                        <p class="text-muted mb-0">PT Sessions Today</p>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="mdi mdi-calendar-clock text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0" id="kpi_upcoming_pt">{{ $upcomingPTSessions ?? 0 }}</h2>
                        <p class="text-muted mb-0">Upcoming Sessions</p>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="mdi mdi-clock-outline text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0" id="kpi_completed_sessions">{{ $completedSessions ?? 0 }}</h2>
                        <p class="text-muted mb-0">Completed Sessions</p>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="mdi mdi-check-circle text-white" style="font-size: 24px;"></i>
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
                        <h2 class="mb-0" id="kpi_pt_cancellations">{{ $ptCancellations ?? 0 }}</h2>
                        <p class="text-muted mb-0">PT Cancellations</p>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="mdi mdi-close-circle text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
