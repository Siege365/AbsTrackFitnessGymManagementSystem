@extends('layouts.admin')

@section('title', 'Reports & Analytics - AbsTrack Fitness Gym')

@push('styles')
    @vite(['resources/css/reports.css'])
@endpush

@section('content')
    <!-- Page Header -->
    <div class="card page-header-card">
        <div class="card-body">
            <div>
                <h2 class="page-header-title">Reports & Analytics</h2>
                <p class="page-header-subtitle">Monitor your gym's performance and revenue.</p>
            </div>
            <button class="btn btn-page-action" data-toggle="modal" data-target="#exportReportModal">
                <i class="mdi mdi-download"></i> Export Report
            </button>
        </div>
    </div>

    
    <!-- KPI Statistics Cards -->
    <div class="row">
        <!-- Monthly Revenue -->
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_monthly_revenue">₱0.00</h2>
                            <p class="text-muted mb-0">Monthly Revenue</p>
                        </div>
                        <div class="icon-box" id="kpi_revenue_icon">
                            <span class="mdi mdi-arrow-top-right"></span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge" id="kpi_revenue_badge">+0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Retail Sales -->
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_retail_sales">₱0.00</h2>
                            <p class="text-muted mb-0">Monthly Retail Sales</p>
                        </div>
                        <div class="icon-box" id="kpi_retail_icon">
                            <span class="mdi mdi-arrow-top-right"></span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge" id="kpi_retail_badge">+0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Membership Revenue -->
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_membership_revenue">₱0.00</h2>
                            <p class="text-muted mb-0">Monthly Membership Revenue</p>
                        </div>
                        <div class="icon-box" id="kpi_membership_icon">
                            <span class="mdi mdi-arrow-top-right"></span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge" id="kpi_membership_badge">+0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly PT Revenue -->
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0" id="kpi_pt_revenue">₱0.00</h2>
                            <p class="text-muted mb-0">Monthly PT Revenue</p>
                        </div>
                        <div class="icon-box" id="kpi_pt_icon">
                            <span class="mdi mdi-arrow-top-right"></span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge" id="kpi_pt_badge">+0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Over Time Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Revenue Over Time</h5>
                        <div class="dropdown">
                            <button class="btn btn-filter dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <div class="dropdown-menu dropdown-menu-right filter-dropdown">
                                <h6 class="dropdown-header">Time Period</h6>
                                <a class="dropdown-item filter-option active" href="#" data-chart="revenueOverTime" data-period="this_year">This Year</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="revenueOverTime" data-period="last_3_months">Last 3 Months</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="revenueOverTime" data-period="this_month">This Month</a>
                            </div>
                        </div>
                    </div>
                    <canvas id="revenueOverTimeChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Top Selling Products -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Top-Selling Products</h5>
                        <div class="dropdown">
                            <button class="btn btn-filter dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <div class="dropdown-menu dropdown-menu-right filter-dropdown">
                                <h6 class="dropdown-header">Time Period</h6>
                                <a class="dropdown-item filter-option active" href="#" data-chart="topSelling" data-period="this_week">This Week</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="topSelling" data-period="this_month">This Month</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="topSelling" data-period="last_month">Last Month</a>
                            </div>
                        </div>
                    </div>
                    <canvas id="topSellingProductsChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Revenue Breakdown</h5>
                        <div class="dropdown">
                            <button class="btn btn-filter dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <div class="dropdown-menu dropdown-menu-right filter-dropdown">
                                <h6 class="dropdown-header">Time Period</h6>
                                <a class="dropdown-item filter-option active" href="#" data-chart="revenueBreakdown" data-period="this_month">This Month</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="revenueBreakdown" data-period="last_month">Last Month</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="revenueBreakdown" data-period="this_year">This Year</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <canvas id="revenueBreakdownChart" height="200"></canvas>
                        </div>
                        <div class="col-md-5 d-flex align-items-center">
                            <div id="revenueBreakdownLegend" class="chart-legend"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Charts Row -->
    <div class="row">
        <!-- Transaction History -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Transaction History</h5>
                        <div class="dropdown">
                            <button class="btn btn-filter dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <div class="dropdown-menu dropdown-menu-right filter-dropdown">
                                <h6 class="dropdown-header">Time Period</h6>
                                <a class="dropdown-item filter-option active" href="#" data-chart="transactionHistory" data-period="this_month">This Month</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="transactionHistory" data-period="last_month">Last Month</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="transactionHistory" data-period="this_year">This Year</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <canvas id="transactionHistoryChart" height="200"></canvas>
                        </div>
                        <div class="col-md-5 d-flex align-items-center">
                            <div id="transactionHistoryLegend" class="chart-legend"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Attendance Trend -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Customer Attendance Trend</h5>
                        <div class="dropdown">
                            <button class="btn btn-filter dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="mdi mdi-filter-variant"></i> Filter
                            </button>
                            <div class="dropdown-menu dropdown-menu-right filter-dropdown">
                                <h6 class="dropdown-header">Time Period</h6>
                                <a class="dropdown-item filter-option active" href="#" data-chart="attendance" data-period="today">Today</a>
                                <a class="dropdown-item filter-option" href="#" data-chart="attendance" data-period="this_week">This Week</a>
                            </div>
                        </div>
                    </div>
                    <canvas id="customerAttendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Report Modal -->
    <div class="modal fade" id="exportReportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Report</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>File Format</label>
                        <div class="format-options">
                            <label class="format-option">
                                <input type="radio" name="export_format" value="pdf" checked>
                                <span class="format-label">PDF</span>
                            </label>
                            <label class="format-option">
                                <input type="radio" name="export_format" value="excel">
                                <span class="format-label">Excel</span>
                            </label>
                            <label class="format-option">
                                <input type="radio" name="export_format" value="csv">
                                <span class="format-label">CSV</span>
                            </label>
                            <label class="format-option">
                                <input type="radio" name="export_format" value="png">
                                <span class="format-label">PNG</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <select name="export_date_range" class="form-control">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Scope</label>
                        <select name="export_scope" class="form-control">
                            <option value="all">All Charts</option>
                            <option value="kpis">KPIs Only</option>
                            <option value="revenue">Revenue Over Time</option>
                            <option value="products">Top Selling Products</option>
                            <option value="breakdown">Revenue Breakdown</option>
                            <option value="transactions">Transaction History</option>
                            <option value="attendance">Customer Attendance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="ReportsPage.exportReport()">Export</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
@vite(['resources/js/pages/reports.js'])
<script>
  document.addEventListener('DOMContentLoaded', function() {
    ReportsPage.init();
  });
</script>
@endpush
