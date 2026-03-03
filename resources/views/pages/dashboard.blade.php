@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
@vite(['resources/css/dashboard.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h2 class="page-header-title mb-1">Dashboard</h2>
            <p class="page-header-subtitle mb-0">Welcome back, {{ auth()->user()->name }}! Here's what's happening at AbsTrack Fitness today.</p>
        </div>
        <div class="header-date text-right">
            <span class="badge badge-outline-light px-3 py-2">
                <i class="mdi mdi-calendar-today mr-1"></i>
                {{ \Carbon\Carbon::now()->format('l, F d, Y') }}
            </span>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
    ROW 1: Primary KPI Cards
    ══════════════════════════════════════════════════════ -->
<div class="row stats-row">
    <!-- Today's Revenue -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-success">
                        <i class="mdi mdi-currency-php"></i>
                    </div>
                    <span class="badge badge-outline-success">Today</span>
                </div>
                <h3 class="stats-value mb-1">₱{{ number_format($todayTotalRevenue, 2) }}</h3>
                <p class="text-muted mb-0 stats-label">Today's Revenue</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        Retail: ₱{{ number_format($todayRetailRevenue, 2) }} &bull;
                        Membership: ₱{{ number_format($todayMembershipRevenue, 2) }} &bull;
                        PT: ₱{{ number_format($todayPTRevenue, 2) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-info">
                        <i class="mdi mdi-chart-line"></i>
                    </div>
                    @if($revenueChange >= 0)
                        <span class="badge badge-outline-success">
                            <i class="mdi mdi-arrow-up-bold"></i> {{ $revenueChange }}%
                        </span>
                    @else
                        <span class="badge badge-outline-danger">
                            <i class="mdi mdi-arrow-down-bold"></i> {{ abs($revenueChange) }}%
                        </span>
                    @endif
                </div>
                <h3 class="stats-value mb-1">₱{{ number_format($monthlyTotalRevenue, 2) }}</h3>
                <p class="text-muted mb-0 stats-label">Monthly Revenue</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        Retail: ₱{{ number_format($monthlyRetailRevenue, 2) }} &bull;
                        Membership: ₱{{ number_format($monthlyMembershipRevenue, 2) }} &bull;
                        PT: ₱{{ number_format($monthlyPTRevenue, 2) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Attendance -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-warning">
                        <i class="mdi mdi-account-check"></i>
                    </div>
                    <span class="badge badge-outline-warning">Live</span>
                </div>
                <h3 class="stats-value mb-1">{{ $todayAttendance }}</h3>
                <p class="text-muted mb-0 stats-label">Today's Check-ins</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        <span class="text-success"><i class="mdi mdi-circle-medium"></i> {{ $activeInGym }} currently in gym</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Members -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-primary">
                        <i class="mdi mdi-account-group"></i>
                    </div>
                    <span class="badge badge-outline-primary">{{ $totalCustomers }} total</span>
                </div>
                <h3 class="stats-value mb-1">{{ $activeMemberships + $activeClients }}</h3>
                <p class="text-muted mb-0 stats-label">Active Subscriptions</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        {{ $activeMemberships }} members &bull; {{ $activeClients }} PT clients
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     ROW 2: Secondary KPI Cards
     ══════════════════════════════════════════════════════ -->
<div class="row stats-row">
    <!-- PT Sessions Today -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-purple">
                        <i class="mdi mdi-dumbbell"></i>
                    </div>
                    <span class="badge badge-outline-light">Today</span>
                </div>
                <h3 class="stats-value mb-1">{{ $todayPTSessions }}</h3>
                <p class="text-muted mb-0 stats-label">PT Sessions Today</p>
                <div class="stats-breakdown mt-2">
                    <small>
                        <span class="text-warning">{{ $upcomingPT }} upcoming</span> &bull;
                        <span class="text-info">{{ $inProgressPT }} in progress</span> &bull;
                        <span class="text-success">{{ $completedPTToday }} done</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Membership Health -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-danger">
                        <i class="mdi mdi-card-account-details"></i>
                    </div>
                    @if($dueSoonMemberships > 0)
                        <span class="badge badge-outline-warning">{{ $dueSoonMemberships }} due soon</span>
                    @else
                        <span class="badge badge-outline-success">Healthy</span>
                    @endif
                </div>
                <h3 class="stats-value mb-1">{{ $totalMemberships }}</h3>
                <p class="text-muted mb-0 stats-label">Total Memberships</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        <span class="text-success">{{ $activeMemberships }} active</span> &bull;
                        <span class="text-danger">{{ $expiredMemberships }} expired</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Status -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-teal">
                        <i class="mdi mdi-package-variant-closed"></i>
                    </div>
                    @if($outOfStockProducts > 0 || $lowStockProducts > 0)
                        <span class="badge badge-outline-warning">
                            {{ $lowStockProducts + $outOfStockProducts }} alert{{ ($lowStockProducts + $outOfStockProducts) > 1 ? 's' : '' }}
                        </span>
                    @else
                        <span class="badge badge-outline-success">All Good</span>
                    @endif
                </div>
                <h3 class="stats-value mb-1">{{ $totalProducts }}</h3>
                <p class="text-muted mb-0 stats-label">Inventory Products</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        <span class="text-warning">{{ $lowStockProducts }} low stock</span> &bull;
                        <span class="text-danger">{{ $outOfStockProducts }} out of stock</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Trainers -->
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="stats-icon bg-gradient-orange">
                        <i class="mdi mdi-human-handsup"></i>
                    </div>
                    <span class="badge badge-outline-light">Staff</span>
                </div>
                <h3 class="stats-value mb-1">{{ $totalTrainers }}</h3>
                <p class="text-muted mb-0 stats-label">Active Trainers</p>
                <div class="stats-breakdown mt-2">
                    <small class="text-muted">
                        {{ $totalClients }} PT clients total &bull; {{ $activeClients }} active
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <!-- Today's PT Schedule -->
    <div class="col-xl-4 col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-dumbbell mr-1" style="color: #AB47BC;"></i>
                        Today's PT Schedule
                    </h4>
                    <a href="{{ route('sessions.pt.index') }}" class="btn btn-sm btn-outline-light">View All</a>
                </div>
                @if($todaySchedules->isEmpty())
                    <div class="empty-state text-center py-4">
                        <i class="mdi mdi-calendar-blank-outline" style="font-size: 48px; color: rgba(255,255,255,0.15);"></i>
                        <p class="text-muted mt-2 mb-0">No PT sessions scheduled for today</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover dashboard-table" id="ptScheduleTable">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Trainer</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySchedules as $schedule)
                                <tr>
                                    <td>
                                        <span class="font-weight-medium">{{ $schedule->display_name }}</span>
                                    </td>
                                    <td class="text-muted">{{ $schedule->trainer_name ?? 'N/A' }}</td>
                                    <td class="text-muted">{{ $schedule->formatted_time }}</td>
                                    <td>
                                        <span class="badge {{ $schedule->status_badge_class }}">
                                            {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div id="ptSchedulePagination" class="dashboard-pagination mt-2"></div>
                @endif
            </div>
        </div>
    </div>

    <!-- Expiring Soon -->
    <div class="col-xl-4 col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-alert-circle-outline mr-1" style="color: #FFA726;"></i>
                        Expiring Soon (7 Days)
                    </h4>
                    <a href="{{ route('memberships.index') }}" class="btn btn-sm btn-outline-light">View All</a>
                </div>
                @if($expiringMemberships->isEmpty() && $expiringClients->isEmpty())
                    <div class="empty-state text-center py-4">
                        <i class="mdi mdi-check-circle-outline" style="font-size: 48px; color: rgba(102, 187, 106, 0.4);"></i>
                        <p class="text-muted mt-2 mb-0">No subscriptions expiring in the next 7 days</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover dashboard-table" id="expiringSoonTable">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Plan</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiringMemberships as $m)
                                <tr>
                                    <td><span class="font-weight-medium">{{ $m->name }}</span></td>
                                    <td><span class="badge badge-info badge-sm">Member</span></td>
                                    <td class="text-muted">{{ $m->formatted_plan_type }}</td>
                                    <td>
                                        <span class="text-warning">{{ $m->due_date->format('M d, Y') }}</span>
                                        <br><small class="text-muted">{{ $m->due_date->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                                @foreach($expiringClients as $c)
                                <tr>
                                    <td><span class="font-weight-medium">{{ $c->name }}</span></td>
                                    <td><span class="badge badge-purple badge-sm">Client</span></td>
                                    <td class="text-muted">{{ $c->formatted_plan_type }}</td>
                                    <td>
                                        <span class="text-warning">{{ $c->due_date->format('M d, Y') }}</span>
                                        <br><small class="text-muted">{{ $c->due_date->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div id="expiringSoonPagination" class="dashboard-pagination mt-2"></div>
                @endif
            </div>
        </div>
    </div>
    <!-- Low Stock Alerts -->
    <div class="col-xl-4 col-lg-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-alert-outline mr-1" style="color: #EF5350;"></i>
                        Low Stock Alerts
                    </h4>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-light">Manage</a>
                </div>
                @if($lowStockItems->isEmpty())
                    <div class="empty-state text-center py-4">
                        <i class="mdi mdi-check-all" style="font-size: 48px; color: rgba(102, 187, 106, 0.4);"></i>
                        <p class="text-muted mt-2 mb-0">All products are well stocked</p>
                    </div>
                @else
                    <div class="stock-alert-list" id="lowStockList">
                        @foreach($lowStockItems as $item)
                        <div class="stock-alert-item d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom-subtle' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="stock-indicator {{ $item->stock_qty == 0 ? 'bg-danger' : 'bg-warning' }}"></div>
                                <div>
                                    <p class="mb-0 font-weight-medium text-white">{{ $item->product_name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $item->stock_qty == 0 ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $item->stock_qty }} left
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div id="lowStockPagination" class="dashboard-pagination mt-2"></div>
                @endif
            </div>
        </div>
      </div>
</div>
<!-- ══════════════════════════════════════════════════════
     ROW 3: Charts - Attendance & Revenue
     ══════════════════════════════════════════════════════ -->
<div class="row">
    <!-- Attendance Chart -->
    <div class="col-xl-6 col-lg-6 grid-margin stretch-card">
        <div class="card chart-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Customer Attendance</h4>
                    <div class="chart-filter-group">
                        <button class="btn btn-sm btn-filter active" data-chart="attendance" data-period="today">Today</button>
                        <button class="btn btn-sm btn-filter" data-chart="attendance" data-period="this_week">Week</button>
                        <button class="btn btn-sm btn-filter" data-chart="attendance" data-period="this_month">Month</button>
                    </div>
                </div>
                <div class="chart-container" style="position: relative; height: 280px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="col-xl-6 col-lg-6 grid-margin stretch-card">
        <div class="card chart-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Revenue Trend</h4>
                    <div class="chart-filter-group">
                        <button class="btn btn-sm btn-filter active" data-chart="revenue" data-period="this_month">Month</button>
                        <button class="btn btn-sm btn-filter" data-chart="revenue" data-period="this_week">Week</button>
                        <button class="btn btn-sm btn-filter" data-chart="revenue" data-period="this_year">Year</button>
                    </div>
                </div>
                <div class="chart-container" style="position: relative; height: 280px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     ROW 4: Doughnut Charts - Membership & Revenue Split
     ══════════════════════════════════════════════════════ -->
<div class="row">
    <!-- Membership Status Doughnut -->
    <div class="col-xl-4 col-lg-4 grid-margin stretch-card">
        <div class="card chart-card">
            <div class="card-body">
                <h4 class="card-title mb-3">Membership Status</h4>
                <div class="chart-container d-flex align-items-center justify-content-space-between" style="position: relative; height: 240px;">
                    <canvas id="membershipStatusChart"></canvas>
                </div>
                <div class="doughnut-legend mt-3" style="justify-content: space-between;">
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <h5 class="mb-0 text-success">{{ $activeMemberships }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0 text-warning">{{ $dueSoonMemberships }}</h5>
                            <small class="text-muted">Due Soon</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0 text-danger">{{ $expiredMemberships }}</h5>
                            <small class="text-muted">Expired</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Distribution -->
    <div class="col-xl-4 col-lg-4 grid-margin stretch-card">
        <div class="card chart-card">
            <div class="card-body">
                <h4 class="card-title mb-3">Plan Distribution</h4>
                <div class="chart-container d-flex align-items-center justify-content-center" style="position: relative; height: 240px;">
                    <canvas id="planDistributionChart"></canvas>
                </div>
                <div class="plan-legend mt-3">
                    @foreach($planDistribution->take(5) as $plan)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">
                            <span class="legend-dot" style="background: {{ ['#42A5F5','#66BB6A','#FFA726','#AB47BC','#26C6DA','#EF5350','#EC407A'][$loop->index % 7] }};"></span>
                            {{ $plan->plan_type ? ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', $plan->plan_type)) : 'N/A' }}
                        </small>
                        <small class="text-white font-weight-bold">{{ $plan->count }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="col-xl-4 col-lg-4 grid-margin stretch-card">
        <div class="card chart-card">
            <div class="card-body">
                <h4 class="card-title mb-3">Revenue Breakdown</h4>
                <div class="chart-container d-flex align-items-center justify-content-center" style="position: relative; height: 240px;">
                    <canvas id="revenueBreakdownChart"></canvas>
                </div>
                <div class="doughnut-legend mt-3">
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <h6 class="mb-0" style="color: #42A5F5;">₱{{ number_format($monthlyRetailRevenue, 0) }}</h6>
                            <small class="text-muted">Retail</small>
                        </div>
                        <div class="text-center">
                            <h6 class="mb-0" style="color: #66BB6A;">₱{{ number_format($monthlyMembershipRevenue, 0) }}</h6>
                            <small class="text-muted">Membership</small>
                        </div>
                        <div class="text-center">
                            <h6 class="mb-0" style="color: #FFA726;">₱{{ number_format($monthlyPTRevenue, 0) }}</h6>
                            <small class="text-muted">Personal Training</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
    ROW 6: Recent Payments & Recent Activity
    ══════════════════════════════════════════════════════ -->
<div class="row">
  <!-- Recent Payments -->
    <div class="col-xl-8 col-lg-7 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-receipt mr-1" style="color: #42A5F5;"></i>
                        Recent Payments
                    </h4>
                    <div class="d-flex align-items-center">
                        <div class="dropdown mr-2" id="paymentFilterDropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-filter-outline mr-1"></i>
                                <span id="paymentFilterLabel">All</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right payment-filter-dropdown">
                                <a class="dropdown-item active" href="#" data-filter="all">All Types</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-filter="Membership">
                                    Membership
                                </a>
                                <a class="dropdown-item" href="#" data-filter="PT">
                                    Personal Training
                                </a>
                                <a class="dropdown-item" href="#" data-filter="Product">
                                    Product
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('payments.history') }}" class="btn btn-sm btn-outline-light">View History</a>
                    </div>
                </div>
                @if($recentPayments->isEmpty())
                    <div class="empty-state text-center py-4">
                        <i class="mdi mdi-cash-register" style="font-size: 48px; color: rgba(255,255,255,0.15);"></i>
                        <p class="text-muted mt-2 mb-0">No recent payments</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover dashboard-table" id="recentPaymentsTable">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Customer</th>
                                    <th>Category</th>
                                    <th>Plan / Type</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr data-payment-category="{{ $payment->category }}">
                                    <td class="font-weight-medium text-info">{{ $payment->receipt_number }}</td>
                                    <td>{{ $payment->name }}</td>
                                    <td>
                                        @if($payment->category === 'Membership')
                                            <span class="badge badge-outline-success">Membership</span>
                                        @elseif($payment->category === 'PT')
                                            <span class="badge badge-purple">Personal Training</span>
                                        @else
                                            <span class="badge badge-outline-info">Product</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ ucfirst($payment->plan_type) }}</td>
                                    <td class="text-muted">{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                    <td class="font-weight-medium text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-muted">{{ $payment->created_at->format('M d, g:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="empty-state text-center py-4" id="noFilteredPayments" style="display:none;">
                        <i class="mdi mdi-filter-remove-outline" style="font-size: 48px; color: rgba(255,255,255,0.15);"></i>
                        <p class="text-muted mt-2 mb-0">No payments match this filter</p>
                    </div>
                    <div id="paymentsPagination" class="dashboard-pagination mt-2"></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-history mr-1" style="color: #66BB6A;"></i>
                        Recent Activity
                    </h4>
                    <a href="{{ route('UserAndAdmin.CashierActivity') }}" class="btn btn-sm btn-outline-light">View All</a>
                </div>
                @if($recentActivities->isEmpty())
                    <div class="empty-state text-center py-4 flex-grow-1 d-flex flex-column align-items-center justify-content-center">
                        <i class="mdi mdi-clock-outline" style="font-size: 48px; color: rgba(255,255,255,0.15);"></i>
                        <p class="text-muted mt-2 mb-0">No recent activity</p>
                    </div>
                @else
                    <div class="activity-timeline flex-grow-1" id="activityTimeline">
                        @foreach($recentActivities as $activity)
                        <div class="activity-item d-flex align-items-start {{ !$loop->last ? 'mb-3' : '' }}">
                            <div class="activity-content flex-grow-1">
                                <p class="mb-1 text-white">
                                    <small class="badge badge-outline-light mr-1">{{ $activity->module }}</small>
                                    {{ $activity->description }}
                                </p>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted" style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 8px;">
                                        by {{ $activity->user_name }}
                                        @if($activity->reference_number)
                                            &bull; Ref: {{ $activity->reference_number }}
                                        @endif
                                    </small>
                                    <small class="text-muted" style="flex-shrink: 0; white-space: nowrap;">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
                <div id="activityPagination" class="dashboard-pagination mt-auto pt-2" style="align-self: flex-end;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for charts -->
<script id="planDistributionData" type="application/json">
    @json($planDistribution)
</script>

@endsection

@push('scripts')
@vite(['resources/js/pages/dashboard.js'])
@endpush

