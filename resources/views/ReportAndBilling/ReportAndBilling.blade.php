@extends('layouts.admin')

@section('title', 'Report & Billing')

@push('styles')
<style>
    .icon-box-success,
    .icon-box-danger {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-box-success {
        background: rgba(102, 187, 106, 0.2);
        color: #66BB6A;
    }

    .icon-box-danger {
        background: rgba(239, 83, 80, 0.2);
        color: #EF5350;
    }

    .icon-box-success .mdi,
    .icon-box-danger .mdi {
        font-size: 1.5rem;
    }

    .chart-card {
        background: #191C24;
        border: 1px solid #3a4048;
        border-radius: 8px;
    }

    .chart-card .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #fff;
    }

    .btn-filter {
        background: transparent;
        border: 1px solid #3a4048;
        color: #8b92a7;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .btn-filter:hover {
        border-color: #42A5F5;
        color: #42A5F5;
        background: rgba(66, 165, 245, 0.1);
    }

    .btn-filter i {
        font-size: 1rem;
        margin-right: 4px;
    }

    .badge-success {
        background: rgba(102, 187, 106, 0.2);
        color: #66BB6A;
        border: none;
    }

    .badge-danger {
        background: rgba(239, 83, 80, 0.2);
        color: #EF5350;
        border: none;
    }

    canvas {
        max-height: 300px;
    }

    /* Custom scrollbar for dark theme */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #191C24;
    }

    ::-webkit-scrollbar-thumb {
        background: #3a4048;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #4a5058;
    }
</style>
@endpush

@section('content')
    <div class="row">
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                <div class="row">
                    <div class="col-9">
                    <div class="d-flex align-items-center align-self-start">
                        <h3 class="mb-0">₱0.00</h3>
                    </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal">Total Products</h6>
                </div>
            </div>
            </div>
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                <div class="row">
                    <div class="col-9">
                    <div class="d-flex align-items-center align-self-start">
                        <h3 class="mb-0">₱0.00</h3>
                    </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal">Low Stock Items</h6>
                </div>
            </div>
            </div>
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                <div class="row">
                    <div class="col-9">
                    <div class="d-flex align-items-center align-self-start">
                        <h3 class="mb-0">₱0.00</h3>
                    </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal">Out Of Stock Items</h6>
                </div>
            </div>
            </div>
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                <div class="row">
                    <div class="col-9">
                    <div class="d-flex align-items-center align-self-start">
                        <h3 class="mb-0">₱0.00</h3>
                        <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
                    </div>
                    </div>
                    <div class="col-3">
                    <div class="icon icon-box-success ">
                        <span class="mdi mdi-arrow-top-right icon-item"></span>
                    </div>
                    </div>
                </div>
                <h6 class="text-muted font-weight-normal">Stock Value</h6>
                </div>
            </div>
            </div>
        </div>

    <!-- Revenue Over Time Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Revenue Over Time</h5>
                        <button class="btn btn-filter">
                            <i class="mdi mdi-filter-variant"></i> Filter
                        </button>
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
            <div class="card chart-card text-white border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Top Selling Products</h5>
                        <button class="btn btn-filter">
                            <i class="mdi mdi-filter-variant"></i> Filter
                        </button>
                    </div>
                    <canvas id="topSellingProductsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card text-white border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Revenue Breakdown</h5>
                        <button class="btn btn-filter">
                            <i class="mdi mdi-filter-variant"></i> Filter
                        </button>
                    </div>
                    <canvas id="revenueBreakdownChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Charts Row -->
    <div class="row">
        <!-- Transaction History -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card text-white border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Transaction History</h5>
                        <button class="btn btn-filter">
                            <i class="mdi mdi-filter-variant"></i> Filter
                        </button>
                    </div>
                    <canvas id="transactionHistoryChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Customer Attendance Trend -->
        <div class="col-xl-6 mb-3">
            <div class="card chart-card text-white border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Customer Attendance Trend</h5>
                        <button class="btn btn-filter">
                            <i class="mdi mdi-filter-variant"></i> Filter
                        </button>
                    </div>
                    <canvas id="customerAttendanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('js/pages/reports.js') }}?v={{ time() }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    ReportsPage.init();
  });
</script>
@endpush