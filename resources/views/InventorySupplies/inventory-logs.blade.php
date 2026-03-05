@extends('layouts.admin')

@section('title', 'Inventory Logs - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/inventory.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Inventory Logs</h2>
            <p class="page-header-subtitle">Track all stock-in and stock-out transactions across your inventory.</p>
        </div>
    </div>
</div>

<!-- KPI Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0" data-kpi-value="{{ $totalTransactions ?? 0 }}">{{ $totalTransactions ?? 0 }}</h2>
              <p class="text-muted mb-0">Total Transactions</p>
            </div>
            <div class="stats-icon bg-info">
              <i class="mdi mdi-swap-vertical text-white" style="font-size: 24px;"></i>
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
              <h2 class="mb-0" data-kpi-value="{{ $totalStockIn ?? 0 }}">{{ $totalStockIn ?? 0 }}</h2>
              <p class="text-muted mb-0">Stock In Today</p>
            </div>
            <div class="stats-icon bg-success">
              <i class="mdi mdi-plus-circle text-white" style="font-size: 24px;"></i>
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
              <h2 class="mb-0" data-kpi-value="{{ $totalStockOut ?? 0 }}">{{ $totalStockOut ?? 0 }}</h2>
              <p class="text-muted mb-0">Stock Out Today</p>
            </div>
            <div class="stats-icon bg-warning">
              <i class="mdi mdi-minus-circle text-white" style="font-size: 24px;"></i>
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
              <h2 class="mb-0" data-kpi-value="{{ $transactionsThisMonth ?? 0 }}">{{ $transactionsThisMonth ?? 0 }}</h2>
              <p class="text-muted mb-0">This Month</p>
            </div>
            <div class="stats-icon bg-primary">
              <i class="mdi mdi-calendar-check text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- Logs Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Inventory Logs</h4>
          <div class="d-flex align-items-center">
            <!-- Search Bar -->
            <form action="{{ route('inventory.logs') }}" method="GET" id="logsSearchForm" class="d-flex align-items-center">
                <input type="hidden" name="activity_filter" value="{{ request('activity_filter') }}">
                <div class="search-wrapper mr-2">
                  <input type="text" 
                        id="logsSearchInput" 
                        name="search" 
                        class="form-control form-control-sm" 
                        placeholder="Search logs..." 
                        value="{{ request('search') }}"
                        style="width: 100%; max-width: 450px;">
                  @if(request('search'))
                  <button type="button" class="search-clear-btn" onclick="clearLogsSearch()">&times;</button>
                  @endif
                </div>
            </form>
            <!-- Filter Dropdown -->
            <div class="dropdown d-inline-block mr-2">
              <button class="btn btn-sm filter-button dropdown-toggle" type="button" id="activityFilterDropdown" data-toggle="dropdown" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right filter-accordion">
                <div class="filter-header">
                  <span class="filter-title">Filter By</span>
                  <a class="filter-clear-all" href="{{ route('inventory.logs') }}">Clear All</a>
                </div>
                <!-- Sort Section -->
                <div class="filter-section">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-sort"></i>
                      <span>Sort By</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    <a class="filter-option {{ ($activityFilter ?? 'newest') == 'newest' ? 'active' : '' }}" 
                      href="{{ route('inventory.logs', array_merge(request()->except('activity_filter'), ['activity_filter' => 'newest'])) }}">
                      <i class="mdi mdi-sort-calendar-descending"></i> Newest First
                    </a>
                    <a class="filter-option {{ ($activityFilter ?? '') == 'oldest' ? 'active' : '' }}" 
                      href="{{ route('inventory.logs', array_merge(request()->except('activity_filter'), ['activity_filter' => 'oldest'])) }}">
                      <i class="mdi mdi-sort-calendar-ascending"></i> Oldest First
                    </a>
                  </div>
                </div>
                <!-- Transaction Type Section -->
                <div class="filter-section">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-swap-vertical"></i>
                      <span>Transaction Type</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    <a class="filter-option filter-option-stock-in {{ ($activityFilter ?? '') == 'stock_in' ? 'active' : '' }}" 
                      href="{{ route('inventory.logs', array_merge(request()->except('activity_filter'), ['activity_filter' => 'stock_in'])) }}">
                      <i class="mdi mdi-plus-circle"></i> Stock In
                    </a>
                    <a class="filter-option filter-option-stock-out {{ ($activityFilter ?? '') == 'stock_out' ? 'active' : '' }}" 
                      href="{{ route('inventory.logs', array_merge(request()->except('activity_filter'), ['activity_filter' => 'stock_out'])) }}">
                      <i class="mdi mdi-minus-circle"></i> Stock Out
                    </a>
                  </div>
                </div>
                <!-- Category Section -->
                <div class="filter-section">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-tag-multiple"></i>
                      <span>Category</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    @if(isset($categories) && $categories->count() > 0)
                      @foreach($categories as $cat)
                        <a class="filter-option filter-option-category-{{ $cat->slug }} {{ ($activityFilter ?? '') == $cat->name ? 'active' : '' }}" 
                          href="{{ route('inventory.logs', array_merge(request()->except('activity_filter'), ['activity_filter' => $cat->name])) }}"
                          @if($cat->color) style="--cat-color: {{ $cat->color }};" @endif>
                          <i class="mdi {{ $cat->icon }}"></i> {{ $cat->name }}
                        </a>
                      @endforeach
                    @else
                      <span class="text-muted px-3 py-2 d-block" style="font-size: 0.85rem;">No categories yet</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Product#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Unit Price</th>
                <th>Status</th>
                <th>Quantity</th>
                <th>Date & Time</th>
              </tr>
            </thead>
            <tbody>
            @forelse($recentActivity ?? [] as $activity)
                <tr>
                  <td>{{ $activity->inventorySupply->product_number ?? 'N/A' }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                        @if ($activity->inventorySupply && $activity->inventorySupply->avatar)
                            <img src="{{ asset('storage/' . $activity->inventorySupply->avatar) }}"
                                alt="Product Image" class="avatar-circle mr-2">
                        @else
                            <div class="avatar-circle avatar-initial mr-2">
                                {{ $activity->inventorySupply ? strtoupper(substr($activity->inventorySupply->product_name, 0, 1)) : 'N' }}
                            </div>
                        @endif
                        <span>{{ $activity->inventorySupply->product_name ?? 'N/A' }}</span>
                    </div>
                  </td>
                  <td>
                    @if($activity->inventorySupply)
                      @php
                          $catSlug = strtolower(str_replace(' ', '-', $activity->inventorySupply->category));
                          $knownCats = ['supplement','supplements','equipment','apparel','beverages','drink','snacks','food','accessories'];
                          $badgeClass = in_array($catSlug, $knownCats) ? 'badge-category-'.$catSlug : 'badge-category-dynamic';
                          $catIcon = \App\Helpers\CategoryHelper::getIcon($activity->inventorySupply->category);
                          $dynamicStyle = !in_array($catSlug, $knownCats) && $activity->inventorySupply->category_color 
                              ? 'background: ' . $activity->inventorySupply->category_color . '20; color: ' . $activity->inventorySupply->category_color . ';' 
                              : '';
                      @endphp
                      <span class="badge-category {{ $badgeClass }}" @if($dynamicStyle) style="{{ $dynamicStyle }}" @endif>
                          <i class="mdi {{ $catIcon }}"></i>
                          {{ $activity->inventorySupply->category }}
                      </span>
                    @else
                      N/A
                    @endif
                  </td>
                  <td>₱{{ number_format($activity->inventorySupply->unit_price ?? 0, 2) }}</td>
                  <td>
                    @if($activity->transaction_type === 'stock_in')
                      <span class="badge badge-success"><i class="mdi mdi-plus-circle"></i> Stock In</span>
                    @else
                      <span class="badge badge-warning"><i class="mdi mdi-minus-circle"></i> Stock Out</span>
                    @endif
                  </td>
                  <td>
                    @if($activity->transaction_type === 'stock_in')
                      <span class="text-success font-weight-bold">+{{ $activity->quantity }}</span>
                    @else
                      <span class="text-warning font-weight-bold">-{{ $activity->quantity }}</span>
                    @endif
                  </td>
                  <td>{{ \Carbon\Carbon::parse($activity->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">No inventory logs found</td>
                </tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div class="table-footer" style="justify-content: flex-end;">
          @if(isset($recentActivity))
            {{ $recentActivity->links('vendor.pagination.custom') }}
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
@include('InventorySupplies.partials._logs-scripts')
@endpush
