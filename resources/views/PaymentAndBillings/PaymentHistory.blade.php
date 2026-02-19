@extends('layouts.admin')

@section('title', 'Payments & Billing -> Payment History')

@push('styles')
@vite(['resources/css/payment-history.css'])
@endpush

@section('content')
  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Payment History</h2>
              <p class="page-header-subtitle">View and manage all payment transaction records.</p>
          </div>
      </div>
  </div>

  <div class="mb-3">
    <form action="{{ route('payments.history') }}" method="GET" class="form-inline">
      <div style="display:flex; gap:0.5rem; align-items:center;">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search receipts, names..." value="{{ request('search') }}">
        <select name="filter_type" class="form-control form-control-sm">
          <option value="all" {{ request('filter_type') == 'all' ? 'selected' : '' }}>All Types</option>
          <option value="product" {{ request('filter_type') == 'product' ? 'selected' : '' }}>Product</option>
          <option value="membership" {{ request('filter_type') == 'membership' ? 'selected' : '' }}>Membership</option>
        </select>
        <button class="btn btn-sm btn-primary" type="submit"><i class="mdi mdi-magnify"></i> Search</button>
        @if(request('search') || request('filter_type') != 'all')
        <a href="{{ route('payments.history') }}" class="btn btn-sm btn-secondary"><i class="mdi mdi-refresh"></i> Clear</a>
        @endif
      </div>
    </form>
  </div>

  <!-- Stats Cards -->
  <div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="all">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($membershipIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Membership Income</p>
            </div>
            <div class="stats-icon bg-danger">
              <i class="mdi mdi-account-group" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="active">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($ptIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">PT Income</p>
            </div>
            <div class="stats-icon bg-primary">
              <i class="mdi mdi-dumbbell" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="expiring">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($productIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Product Income</p>
            </div>
            <div class="stats-icon bg-warning">
              <i class="mdi mdi-basket text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card stats-card" data-filter="new">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($refundedTotal ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Total Refunded</p>
            </div>
            <div class="stats-icon bg-info">
              <i class="mdi mdi-cash-refund text-white" style="font-size: 24px;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- PAGE TOGGLE: Membership / PT / Product     -->
  <!-- ========================================== -->
  <div class="page-toggle-container">
    <button class="page-toggle-btn active" data-page="membership">
      <i class="mdi mdi-account-group"></i>
      <span>Membership</span>
    </button>
    <button class="page-toggle-btn" data-page="pt">
      <i class="mdi mdi-dumbbell"></i>
      <span>Personal Training</span>
    </button>
    <button class="page-toggle-btn" data-page="product">
      <i class="mdi mdi-basket"></i>
      <span>Product</span>
    </button>
  </div>

  <!-- ========================================== -->
  <!-- SIBLING PAGES WRAPPER                      -->
  <!-- ========================================== -->
  <div class="pages-slider">

    <!-- ====== MEMBERSHIP PAYMENTS PAGE ====== -->
    <div class="page-panel active" id="membershipPage">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Membership Payments</h4>
            <div class="d-flex align-items-center">
              <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" id="membershipSearchForm">
                @foreach(request()->except(['membership_search', 'membership_page']) as $key => $value)
                  @if(!is_array($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                  @endif
                @endforeach
                <div class="search-wrapper mr-2">
                  <input type="text" 
                    name="membership_search" 
                    class="form-control form-control-sm" 
                    placeholder="Search..." 
                    value="{{ request('membership_search') }}"
                    style="width: 450px;"
                    id="membershipSearchInput">
                  @if(request('membership_search'))
                  <button type="button" class="search-clear-btn" onclick="clearSearch('membershipSearchInput', 'membershipSearchForm')">&times;</button>
                  @endif
                </div>
              </form>
              <div class="dropdown d-inline-block mr-2">
                <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                  <i class="mdi mdi-sort-variant"></i> Filter
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <h6 class="dropdown-header">Sort Order</h6>
                  <a class="dropdown-item {{ request('membership_sort', 'newest') === 'newest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_sort', 'membership_page']), ['membership_sort' => 'newest'])) }}"> <i class="mdi mdi-sort-descending mr-2"></i>Newest First</a>
                  <a class="dropdown-item {{ request('membership_sort') === 'oldest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_sort', 'membership_page']), ['membership_sort' => 'oldest'])) }}"> <i class="mdi mdi-sort-ascending mr-2"></i>Oldest First</a>
                  <div class="dropdown-divider"></div>
                  <h6 class="dropdown-header">Payment Type</h6>
                  <a class="dropdown-item {{ !request('membership_type_filter') ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), [])) }}"> <i class="mdi mdi-filter-remove mr-2"></i>All Types</a>
                  <a class="dropdown-item {{ request('membership_type_filter') === 'new' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), ['membership_type_filter' => 'new'])) }}"> <i class="mdi mdi-account-plus mr-2"></i>New</a>
                  <a class="dropdown-item {{ request('membership_type_filter') === 'renewal' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), ['membership_type_filter' => 'renewal'])) }}"> <i class="mdi mdi-autorenew mr-2"></i>Renewal</a>
                  <a class="dropdown-item {{ request('membership_type_filter') === 'extension' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_type_filter', 'membership_page']), ['membership_type_filter' => 'extension'])) }}"> <i class="mdi mdi-calendar-plus mr-2"></i>Extension</a>
                  <div class="dropdown-divider"></div>
                  <h6 class="dropdown-header">Plan Type</h6>
                  <a class="dropdown-item {{ !request('membership_plan_filter') ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), [])) }}"> <i class="mdi mdi-filter-remove mr-2"></i>All Plans</a>
                  <a class="dropdown-item {{ request('membership_plan_filter') === 'Regular' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'Regular'])) }}"> <i class="mdi mdi-dumbbell mr-2"></i>Regular</a>
                  <a class="dropdown-item {{ request('membership_plan_filter') === 'Student' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'Student'])) }}"> <i class="mdi mdi-school mr-2"></i>Student</a>
                  <a class="dropdown-item {{ request('membership_plan_filter') === 'GymBuddy' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'GymBuddy'])) }}"> <i class="mdi mdi-account-multiple mr-2"></i>Gym Buddy</a>
                  <a class="dropdown-item {{ request('membership_plan_filter') === 'ThreeMonths' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'ThreeMonths'])) }}"> <i class="mdi mdi-calendar-range mr-2"></i>3 Months</a>
                  <a class="dropdown-item {{ request('membership_plan_filter') === 'Session' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['membership_plan_filter', 'membership_page']), ['membership_plan_filter' => 'Session'])) }}"> <i class="mdi mdi-clock-outline mr-2"></i>Session</a>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th style="width: 50px;">
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" id="selectAllMembership">
                      </label>
                    </div>
                  </th>
                  <th class="text-left">Receipt #</th>
                  <th class="text-left">Member</th>
                  <th class="text-left">Plan Type</th>
                  <th class="text-left">Payment Type</th>
                  <th class="text-left">Date</th>
                  <th class="text-left">Amount</th>
                  <th class="text-left">Cashier</th>
                  <th class="text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($membershipPayments ?? [] as $m)
                <tr>
                  <td>
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input membership-checkbox" value="{{ $m->id }}">
                      </label>
                    </div>
                  </td>
                  <td>{{ $m->receipt_number }}</td>
                  <td>{{ $m->member_name }}</td>
                  <td>
                    @php
                      $planLabels = [
                        'Regular' => 'Regular',
                        'Student' => 'Student',
                        'GymBuddy' => 'Gym Buddy',
                        'ThreeMonths' => '3 Months',
                        'Session' => 'Session',
                      ];
                      $planBadgeColors = [
                        'Regular' => 'primary',
                        'Student' => 'info',
                        'GymBuddy' => 'success',
                        'ThreeMonths' => 'warning',
                        'Session' => 'light',
                      ];
                    @endphp
                    <span class="badge badge-{{ $planBadgeColors[$m->plan_type] ?? 'secondary' }}">
                      {{ $planLabels[$m->plan_type] ?? $m->plan_type }}
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-{{ $m->payment_type === 'new' ? 'success' : ($m->payment_type === 'renewal' ? 'primary' : 'info') }}">
                      {{ ucfirst($m->payment_type) }}
                    </span>
                  </td>
                  <td>{{ $m->created_at->format('M d, Y - h:i A') }}</td>
                  <td>₱{{ number_format($m->amount,2) }}</td>
                  <td>{{ $m->processed_by }}</td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                        <i class="mdi mdi-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('membership', {{ $m->id }})">
                          <i class="mdi mdi-eye mr-2"></i> View Receipt
                        </button>
                        <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('membership', {{ $m->id }}, '{{ $m->receipt_number }}', {{ $m->amount }}, '{{ addslashes($m->member_name) }}')">
                          <i class="mdi mdi-cash-refund mr-2"></i> Refund
                        </button>
                        <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('membership', {{ $m->id }})">
                          <i class="mdi mdi-delete mr-2"></i> Delete
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center">No membership payments found</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="table-footer">
            <button type="button" onclick="bulkDeleteMemberships()" class="btn btn-sm btn-delete-selected" id="deleteMembershipBtn" disabled>
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="membershipCount">0</span>)
            </button>
            {{ $membershipPayments->links('vendor.pagination.custom') }}
          </div>
        </div>
      </div>
    </div><!-- /membershipPage -->

    <!-- ====== PERSONAL TRAINING PAGE ====== -->
    <div class="page-panel" id="ptPage">
      <div class="card">
        <div class="card-body" style="text-align: center; padding: 4rem 2rem;">
          <i class="mdi mdi-dumbbell" style="font-size: 5rem; color: #555; margin-bottom: 1rem; display: block;"></i>
          <h2 style="color: #fff; margin-bottom: 0.5rem;">Personal Training Payment History</h2>
          <p style="color: #999; font-size: 1.125rem;">This section is coming soon. Personal training payment history will be available in a future update.</p>
          <div style="margin-top: 2rem; padding: 1.5rem; background: #191C24; border-radius: 8px; display: inline-block;">
            <p style="color: #ffc107; margin: 0;"><i class="mdi mdi-information"></i> You can manage PT schedules in the <strong>Sessions</strong> module.</p>
          </div>
        </div>
      </div>
    </div><!-- /ptPage -->

    <!-- ====== PRODUCT PAYMENTS PAGE ====== -->
    <div class="page-panel" id="productPage">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Product Payments</h4>
            <div class="d-flex align-items-center">
              <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" id="productSearchForm">
                @foreach(request()->except(['product_search', 'product_page']) as $key => $value)
                  @if(!is_array($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                  @endif
                @endforeach
                <div class="search-wrapper mr-2">
                  <input type="text" 
                    name="product_search" 
                    class="form-control form-control-sm" 
                    placeholder="Search..." 
                    value="{{ request('product_search') }}" 
                    style="width: 450px;"
                    id="productSearchInput">
                  @if(request('product_search'))
                  <button type="button" class="search-clear-btn" onclick="clearSearch('productSearchInput', 'productSearchForm')">&times;</button>
                  @endif
                </div>
              </form>
              <div class="dropdown d-inline-block mr-2">
                <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                  <i class="mdi mdi-sort-variant"></i> Filter
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <h6 class="dropdown-header">Filter By</h6>
                  <a class="dropdown-item {{ request('product_sort', 'newest') === 'newest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['product_sort', 'product_page']), ['product_sort' => 'newest'])) }}"> <i class="mdi mdi-sort-descending mr-2"></i>Newest First</a>
                  <a class="dropdown-item {{ request('product_sort') === 'oldest' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['product_sort', 'product_page']), ['product_sort' => 'oldest'])) }}"> <i class="mdi mdi-sort-ascending mr-2"></i>Oldest First</a>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th style="width: 50px;">
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" id="selectAllProduct">
                      </label>
                    </div>
                  </th>
                  <th class="text-left">Receipt #</th>
                  <th class="text-left">Customer</th>
                  <th class="text-left">Date</th>
                  <th class="text-left">Amount</th>
                  <th class="text-left">Cashier</th>
                  <th class="text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($productPayments ?? [] as $p)
                <tr>
                  <td>
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input product-checkbox" value="{{ $p->id }}">
                      </label>
                    </div>
                  </td>
                  <td>{{ $p->receipt_number }}</td>
                  <td>{{ $p->customer_name }}</td>
                  <td>{{ $p->created_at->format('M d, Y - h:i A') }}</td>
                  <td>₱{{ number_format($p->total_amount,2) }}</td>
                  <td>{{ $p->cashier_name }}</td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                        <i class="mdi mdi-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('product', {{ $p->id }})">
                          <i class="mdi mdi-eye mr-2"></i> View Receipt
                        </button>
                        <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('product', {{ $p->id }}, '{{ $p->receipt_number }}', {{ $p->total_amount }}, '{{ addslashes($p->customer_name) }}')">
                          <i class="mdi mdi-cash-refund mr-2"></i> Refund
                        </button>
                        <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('product', {{ $p->id }})">
                          <i class="mdi mdi-delete mr-2"></i> Delete
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center">No product payments found</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="table-footer">
            <button type="button" onclick="bulkDeleteProducts()" class="btn btn-sm btn-delete-selected" id="deleteProductBtn" disabled>
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="productCount">0</span>)
            </button>
            {{ $productPayments->links('vendor.pagination.custom') }}
          </div>
        </div>
      </div>
    </div><!-- /productPage -->

  </div><!-- /pages-slider -->

  <!-- Refunded Payments Table (always visible below tabs) -->
  <div class="card mt-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Refunded Payments</h4>
        <div class="d-flex align-items-center">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" id="refundSearchForm">
            @foreach(request()->except(['refund_search', 'refunded_page']) as $key => $value)
              @if(!is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endif
            @endforeach
            <div class="search-wrapper mr-2">
              <input type="text" 
                name="refund_search" 
                class="form-control form-control-sm" 
                placeholder="Search..." 
                value="{{ request('refund_search') }}" 
                style="width: 450px;"
                id="refundSearchInput">
              @if(request('refund_search'))
              <button type="button" class="search-clear-btn" onclick="clearSearch('refundSearchInput', 'refundSearchForm')">&times;</button>
              @endif
            </div>
          </form>
          <div class="dropdown d-inline-block mr-2">
            <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
              <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <div class="dropdown-menu dropdown-menu-right">
              <h6 class="dropdown-header">Type</h6>
              <a class="dropdown-item {{ request('refund_filter', 'all') === 'all' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'all'])) }}"> <i class="mdi mdi-account-multiple mr-2"></i>All</a>
              <a class="dropdown-item {{ request('refund_filter') === 'product' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'product'])) }}"> <i class="mdi mdi-basket mr-2"></i>Products Only</a>
              <a class="dropdown-item {{ request('refund_filter') === 'membership' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'membership'])) }}"> <i class="mdi mdi-account mr-2"></i>Memberships Only</a>
            </div>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" id="selectAllRefund">
                  </label>
                </div>
              </th>
              <th class="text-left">Receipt #</th>
              <th class="text-left">Name</th>
              <th class="text-center">Type</th>
              <th class="text-left">Refunded At</th>
              <th class="text-left">Amount</th>
              <th class="text-left">Refunded Amount</th>
              <th class="text-left">Cashier</th>
              <th class="text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($combinedRefunds ?? [] as $cr)
            <tr>
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input refund-checkbox" value="{{ $cr->id }}" data-type="{{ strtolower($cr->type) }}">
                  </label>
                </div>
              </td>
              <td>{{ $cr->receipt_number }}</td>
              <td>{{ $cr->name }}</td>
              <td>
                <span class="badge badge-{{ $cr->type == 'Product' ? 'primary' : 'info' }}">
                  {{ $cr->type }}
                </span>
              </td>
              <td>{{ optional($cr->refunded_at)->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($cr->amount,2) }}</td>
              <td>₱{{ number_format($cr->refunded_amount,2) }}</td>
              <td>{{ $cr->refunded_by }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewRefundReceipt('{{ strtolower($cr->type) }}', {{ $cr->id }})">
                      <i class="mdi mdi-receipt mr-2"></i> View Refund Receipt
                    </button>
                    <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle('{{ strtolower($cr->type) }}', {{ $cr->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center">No refunded payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        <button type="button" onclick="bulkDeleteRefunds()" class="btn btn-sm btn-delete-selected" id="deleteRefundBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="refundCount">0</span>)
        </button>
        {{ $combinedRefunds->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>
</div>

<!-- Refund Confirmation Modal -->
<div id="refundModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header bg-warning">
      <h3 class="modal-title">Process Refund</h3>
      <button class="modal-close" onclick="closeRefundModal()">&times;</button>
    </div>
    <div class="modal-body" style="font-size: 1.125rem;">
      <div class="refund-warning" style="color: #000;">
        <i class="mdi mdi-alert"></i>
        <strong>Warning:</strong> This action will mark this transaction as refunded and restore inventory (for products).
      </div>
      <div class="confirmation-details" id="refundDetails"></div>
    </div>
    <div class="modal-footer" style="font-size: 1.125rem;">
      <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">Cancel</button>
      <button type="button" class="btn btn-warning" id="confirmRefundBtn">
        <i class="mdi mdi-cash-refund"></i> Process Refund
      </button>
    </div>
  </div>
</div>

<!-- Refund Receipt Modal -->
<div id="refundReceiptModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Refund Receipt</h3>
      <button class="modal-close" onclick="closeRefundReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="refundReceiptContent">
      <!-- Receipt content will be loaded here -->
    </div>
    <div class="modal-footer" style="font-size: 1.125rem;">
      <button type="button" class="btn btn-secondary" onclick="closeRefundReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printRefundReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>
</div>

<!-- View Receipt Modal -->
<div id="viewReceiptModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Receipt Details</h3>
      <button class="modal-close" onclick="closeViewReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="viewReceiptContent">
      <!-- Receipt content will be loaded here -->
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeViewReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printViewReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>
</div>

<!-- Bulk Delete Forms -->
<form id="bulkDeleteProductForm" action="{{ route('payments.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<form id="bulkDeleteMembershipForm" action="{{ route('membership.payment.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header" style="background-color: #dc3545; color: #fff;">
      <h3 class="modal-title">Confirm Delete</h3>
      <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
    </div>
    <div class="modal-body" style="font-size: 1.125rem;">
      <div class="refund-warning" style="background: #f8d7da; border-color: #dc3545;">
        <i class="mdi mdi-alert" style="color: #dc3545;"></i>
        <div style="color: #000;">
          <strong>Warning:</strong> This action cannot be undone. The selected record(s) will be permanently deleted.
        </div>
      </div>
      <div class="confirmation-details" id="deleteDetails">
        <div class="confirmation-detail-row">
          <span class="confirmation-detail-label">Items to delete:</span>
          <span class="confirmation-detail-value" id="deleteItemCount">1</span>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="font-size: 1.125rem;">
      <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
      <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="executeDelete()">
        <i class="mdi mdi-delete"></i> Delete
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/common/table-dropdown.js'])
<script>
// Fallback ToastUtils if the main library fails to load
if (typeof ToastUtils === 'undefined') {
  window.ToastUtils = {
    showSuccess: function(msg) { console.log('✅ Success:', msg); alert('Success: ' + msg); },
    showError: function(msg) { console.error('❌ Error:', msg); alert('Error: ' + msg); },
    showWarning: function(msg) { console.warn('⚠️ Warning:', msg); alert('Warning: ' + msg); },
    showInfo: function(msg) { console.info('ℹ️ Info:', msg); }
  };
}
</script>
@vite(['resources/js/common/avatar-utils.js'])
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/bulk-selection.js'])
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
let currentRefundType = null;
let currentRefundId = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  initializeCheckboxes();
  initializePageToggle();
  
  // Display Laravel messages
  @if(session('success'))
    ToastUtils.showSuccess('{{ session('success') }}');
  @endif

  @if(session('error'))
    ToastUtils.showError('{{ session('error') }}');
  @endif

  @if($errors->any())
    ToastUtils.showError('{{ $errors->first() }}');
  @endif
});

// FIX #3: Checkbox Management
function initializePageToggle() {
  const pageToggleBtns = document.querySelectorAll('.page-toggle-btn');
  const pageMap = {
    'membership': 'membershipPage',
    'pt': 'ptPage',
    'product': 'productPage'
  };
  const pageOrder = ['membership', 'pt', 'product'];

  pageToggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetPage = this.dataset.page;
      const currentBtn = document.querySelector('.page-toggle-btn.active');
      const currentPage = currentBtn ? currentBtn.dataset.page : 'membership';

      if (targetPage === currentPage) return;

      const currentIdx = pageOrder.indexOf(currentPage);
      const targetIdx = pageOrder.indexOf(targetPage);
      const goingRight = targetIdx > currentIdx;

      const currentPanel = document.getElementById(pageMap[currentPage]);
      const targetPanel = document.getElementById(pageMap[targetPage]);

      // Animate out current
      if (currentPanel) {
        currentPanel.classList.remove('active');
        currentPanel.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');
      }

      setTimeout(() => {
        if (currentPanel) {
          currentPanel.classList.remove('slide-out-left', 'slide-out-right');
        }

        // Animate in target
        if (targetPanel) {
          targetPanel.classList.add('active', goingRight ? 'slide-in-right' : 'slide-in-left');
          setTimeout(() => {
            targetPanel.classList.remove('slide-in-right', 'slide-in-left');
          }, 400);
        }

        // Update active button
        pageToggleBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
      }, 250);
    });
  });

  // Auto-switch to tab from URL query parameter (?tab=product or ?tab=pt)
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get('tab');
  if (tabParam && pageMap[tabParam]) {
    const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${tabParam}"]`);
    if (targetBtn) {
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
      targetBtn.classList.add('active');
      document.getElementById(pageMap[tabParam]).classList.add('active');
    }
  }
}

function initializeCheckboxes() {
  // Product checkboxes
  const selectAllProduct = document.getElementById('selectAllProduct');
  const productCheckboxes = document.querySelectorAll('.product-checkbox');
  
  selectAllProduct?.addEventListener('change', function() {
    productCheckboxes.forEach(cb => cb.checked = this.checked);
    updateDeleteButton('product');
  });

  productCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => updateDeleteButton('product'));
  });

  // Membership checkboxes
  const selectAllMembership = document.getElementById('selectAllMembership');
  const membershipCheckboxes = document.querySelectorAll('.membership-checkbox');
  
  selectAllMembership?.addEventListener('change', function() {
    membershipCheckboxes.forEach(cb => cb.checked = this.checked);
    updateDeleteButton('membership');
  });

  membershipCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => updateDeleteButton('membership'));
  });

  // Refund checkboxes
  const selectAllRefund = document.getElementById('selectAllRefund');
  const refundCheckboxes = document.querySelectorAll('.refund-checkbox');
  
  selectAllRefund?.addEventListener('change', function() {
    refundCheckboxes.forEach(cb => cb.checked = this.checked);
    updateDeleteButton('refund');
  });

  refundCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => updateDeleteButton('refund'));
  });
}

function updateDeleteButton(type) {
  const checkboxes = document.querySelectorAll(`.${type}-checkbox:checked`);
  const count = checkboxes.length;
  const countSpan = document.getElementById(`${type}Count`);
  const deleteBtn = document.getElementById(`delete${type.charAt(0).toUpperCase() + type.slice(1)}Btn`);
  
  if (countSpan) countSpan.textContent = count;
  if (deleteBtn) deleteBtn.disabled = count === 0;
}

function bulkDeleteProducts() {
  const checked = document.querySelectorAll('.product-checkbox:checked');
  if (checked.length === 0) {
    ToastUtils.showWarning('Please select at least one payment to delete');
    return;
  }
  
  pendingDeleteAction = function() {
    const form = document.getElementById('bulkDeleteProductForm');
    form.innerHTML = '@csrf @method("DELETE")';
    
    checked.forEach(cb => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = cb.value;
      form.appendChild(input);
    });
    
    form.submit();
  };
  
  showDeleteModal(checked.length + ' product payment(s)');
}

function bulkDeleteMemberships() {
  const checked = document.querySelectorAll('.membership-checkbox:checked');
  if (checked.length === 0) {
    ToastUtils.showWarning('Please select at least one payment to delete');
    return;
  }
  
  pendingDeleteAction = function() {
    const form = document.getElementById('bulkDeleteMembershipForm');
    form.innerHTML = '@csrf @method("DELETE")';
    
    checked.forEach(cb => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = cb.value;
      form.appendChild(input);
    });
    
    form.submit();
  };
  
  showDeleteModal(checked.length + ' membership payment(s)');
}

function bulkDeleteRefunds() {
  const checked = document.querySelectorAll('.refund-checkbox:checked');
  if (checked.length === 0) {
    ToastUtils.showWarning('Please select at least one refund to delete');
    return;
  }
  
  pendingDeleteAction = function() {
    const products = [];
    const memberships = [];
    
    checked.forEach(cb => {
      const type = cb.dataset.type;
      const id = cb.value;
      if (type === 'product') products.push(id);
      else memberships.push(id);
    });
    
    if (products.length > 0) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '{{ route("payments.bulkDelete") }}';
      form.innerHTML = '@csrf @method("DELETE")';
      products.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
    }
    
    if (memberships.length > 0) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '{{ route("membership.payment.bulkDelete") }}';
      form.innerHTML = '@csrf @method("DELETE")';
      memberships.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
    }
  };
  
  showDeleteModal(checked.length + ' refunded payment(s)');
}

// Delete confirmation modal
let pendingDeleteAction = null;

function confirmDeleteSingle(type, id) {
  pendingDeleteAction = function() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = type === 'product' ? `/payments/${id}` : `/membership-payment/${id}`;
    form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(form);
    form.submit();
  };
  
  showDeleteModal('1 ' + type + ' payment');
}

function showDeleteModal(itemDescription) {
  document.getElementById('deleteItemCount').textContent = itemDescription;
  document.getElementById('deleteConfirmModal').classList.add('show');
}

function closeDeleteModal() {
  document.getElementById('deleteConfirmModal').classList.remove('show');
  pendingDeleteAction = null;
}

function executeDelete() {
  if (pendingDeleteAction) {
    pendingDeleteAction();
    pendingDeleteAction = null;
  }
  closeDeleteModal();
}

// Clear search input and submit form
function clearSearch(inputId, formId) {
  const input = document.getElementById(inputId);
  if (input) {
    input.value = '';
    document.getElementById(formId).submit();
  }
}

// Refund Modal Functions
function openRefundModal(type, id, receipt, amount, name) {
  currentRefundType = type;
  currentRefundId = id;
  
  const details = document.getElementById('refundDetails');
  details.innerHTML = `
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Receipt:</span>
      <span class="confirmation-detail-value">#${receipt}</span>
    </div>
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Name:</span>
      <span class="confirmation-detail-value">${name}</span>
    </div>
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Amount:</span>
      <span class="confirmation-detail-value" style="color:#dc3545;">₱${parseFloat(amount).toFixed(2)}</span>
    </div>
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Type:</span>
      <span class="confirmation-detail-value">${type === 'product' ? 'Product Payment' : 'Membership Payment'}</span>
    </div>`;
  
  document.getElementById('refundModal').classList.add('show');
}

function closeRefundModal() {
  document.getElementById('refundModal').classList.remove('show');
  currentRefundType = null;
  currentRefundId = null;
}

// FIX #2: Refund Confirmation Handler
document.getElementById('confirmRefundBtn')?.addEventListener('click', function() {
  if (!currentRefundType || !currentRefundId) {
    ToastUtils.showError('Invalid refund request');
    return;
  }
  
  const reason = document.getElementById('refundReason')?.value || '';
  const url = currentRefundType === 'product' 
    ? `/payments/${currentRefundId}/refund` 
    : `/membership-payment/${currentRefundId}/refund`;
  
  this.disabled = true;
  this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
  
  const formData = new FormData();
  formData.append('_token', CSRF_TOKEN);
  formData.append('reason', reason);
  
  fetch(url, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closeRefundModal();
      ToastUtils.showSuccess('Refund processed successfully!');
      
      // FIX #2: Immediately show refund receipt modal
      setTimeout(() => {
        showRefundReceipt(currentRefundType, currentRefundId, data);
      }, 200);
    } else {
      ToastUtils.showError(data.message || 'Failed to process refund');
      this.disabled = false;
      this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
    }
  })
  .catch(err => {
    console.error(err);
    ToastUtils.showError('Failed to process refund');
    this.disabled = false;
    this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
  });
});

// FIX #2: Show refund receipt in POPUP modal
function showRefundReceipt(type, id, refundData) {
  const content = document.getElementById('refundReceiptContent');
  content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  
  document.getElementById('refundReceiptModal').classList.add('show');
  
  // If the refund response already contains payment details, use it immediately
  try {
    if (refundData && refundData.payment) {
      content.innerHTML = generateRefundReceipt(type, refundData.payment, refundData);
      return;
    }
  } catch (e) {
    console.error('Error generating receipt from refundData:', e);
  }

  const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;

  // Try fetching the receipt, retrying a few times in case the server needs a moment to finalize
  const attemptFetch = (attempt = 1) => {
    fetch(url)
      .then(r => {
        if (!r.ok) throw new Error('Network response not ok: ' + r.status);
        return r.json();
      })
      .then(data => {
        content.innerHTML = generateRefundReceipt(type, data, refundData);
      })
      .catch(err => {
        console.error('Receipt fetch attempt', attempt, 'failed:', err);
        if (attempt < 3) {
          // small backoff before retrying
          setTimeout(() => attemptFetch(attempt + 1), 300 * attempt);
        } else {
          ToastUtils.showError('Failed to load receipt');
          content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
        }
      });
  };

  attemptFetch();
}

function generateRefundReceipt(type, paymentData, refundData) {
  const payment = refundData.payment || paymentData;
  const now = new Date();
  
  let html = `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>REFUND RECEIPT</h2>
        <div class="receipt-refund-badge">REFUNDED</div>
        <p>Date: ${now.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}</p>
      </div>
      
      <div class="receipt-info">
        <div class="receipt-row">
          <span>Receipt #:</span>
          <span><strong>${payment.receipt_number}</strong></span>
        </div>
        <div class="receipt-row">
          <span>Customer:</span>
          <span>${payment.customer_name || payment.member_name}</span>
        </div>
        <div class="receipt-row">
          <span>Original Date:</span>
          <span>${new Date(payment.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}</span>
        </div>
        <div class="receipt-row">
          <span>Refund Type:</span>
          <span>${type === 'product' ? 'Product Payment' : 'Membership Payment'}</span>
        </div>
      </div>`;
  
  if (type === 'product' && payment.items) {
    html += '<div class="receipt-items"><h4>Items:</h4>';
    payment.items.forEach(item => {
      html += `
        <div class="receipt-item">
          <div class="receipt-row">
            <span>${item.product_name}</span>
            <span>₱${parseFloat(item.unit_price).toFixed(2)}</span>
          </div>
          <div class="receipt-row" style="font-size:0.9rem;color:#666;">
            <span>Qty: ${item.quantity}</span>
            <span>₱${parseFloat(item.total_price).toFixed(2)}</span>
          </div>
        </div>`;
    });
    html += '</div>';
  }
  
  html += `
      <div class="receipt-total">
        <div class="receipt-row">
          <span>Original Amount:</span>
          <span>₱${parseFloat(payment.total_amount || payment.amount).toFixed(2)}</span>
        </div>
        <div class="receipt-row" style="color:#dc3545;">
          <span>Refunded Amount:</span>
          <span>₱${parseFloat(payment.refunded_amount || payment.total_amount || payment.amount).toFixed(2)}</span>
        </div>
      </div>
      
      <div class="receipt-info" style="margin-top:20px;">
        <div class="receipt-row">
          <span>Refund Status:</span>
          <span><strong>${payment.refund_status ? payment.refund_status.toUpperCase() : 'FULL'}</strong></span>
        </div>
        ${payment.refund_reason ? `
        <div class="receipt-row">
          <span>Reason:</span>
          <span>${payment.refund_reason}</span>
        </div>` : ''}
        <div class="receipt-row">
          <span>Processed By:</span>
          <span>${payment.refunded_by || 'Admin'}</span>
        </div>
      </div>
      
      <div class="receipt-footer">
        <p>This is a computer-generated refund receipt.</p>
        <p>Thank you!</p>
      </div>
    </div>`;
  
  return html;
}

function closeRefundReceiptModal() {
  document.getElementById('refundReceiptModal').classList.remove('show');
  setTimeout(() => window.location.reload(), 500);
}

function printRefundReceipt() {
  const content = document.getElementById('refundReceiptContent').innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Refund Receipt</title>
      <style>
        body { font-family: 'Courier New', monospace; }
        .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
        .receipt-refund-badge { display: inline-block; background: #dc3545; color: white; padding: 5px 15px; border-radius: 4px; font-weight: bold; margin-top: 10px; }
        .receipt-info { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #666; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .receipt-items { margin-bottom: 20px; }
        .receipt-item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dotted #ccc; }
        .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
        .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; font-size: 0.9rem; color: #666; }
      </style>
    </head>
    <body>${content}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}

// View refund receipt (from refunded table)
function viewRefundReceipt(type, id) {
  const content = document.getElementById('refundReceiptContent');
  content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  
  document.getElementById('refundReceiptModal').classList.add('show');
  
  const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = generateRefundReceipt(type, data, {payment: data});
    })
    .catch(err => {
      console.error(err);
      ToastUtils.showError('Failed to load receipt');
      content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

// View history receipt (original receipt)
function viewHistoryReceipt(type, id) {
  const content = document.getElementById('viewReceiptContent');
  content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  
  document.getElementById('viewReceiptModal').classList.add('show');
  
  const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = generateOriginalReceipt(type, data);
    })
    .catch(err => {
      console.error(err);
      ToastUtils.showError('Failed to load receipt');
      content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

function generateOriginalReceipt(type, data) {
  if (type === 'product') {
    const items = data.items || [];
    const itemsHTML = items.map(item => `
      <tr>
        <td>${item.product_name}</td>
        <td style="text-align: center;">${item.quantity}</td>
        <td style="text-align: right;">₱${parseFloat(item.unit_price).toFixed(2)}</td>
        <td style="text-align: right;">₱${parseFloat(item.subtotal || item.total_price || (item.unit_price * item.quantity)).toFixed(2)}</td>
      </tr>
    `).join('');

    return `
      <div class="receipt-container">
        <div class="receipt-header">
          <h2>RECEIPT</h2>
          <p><strong>Abstrack Fitness Gym</strong></p>
          <p>Toril, Davao Del Sur</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong>
            <span style="display: block; font-weight: 600;">#${data.receipt_number}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong>
            <span style="display: block; font-weight: 600;">${data.formatted_date || new Date(data.created_at).toLocaleString()}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Customer Name</strong>
            <span style="display: block; font-weight: 600;">${data.customer_name || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Cashier</strong>
            <span style="display: block; font-weight: 600;">${data.cashier_name || ''}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong>
            <span style="display: block; font-weight: 600;">${data.payment_method || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Transaction Type</strong>
            <span style="display: block; font-weight: 600;">${data.transaction_type || 'N/A'}</span>
          </div>
        </div>

        <table class="receipt-table">
          <thead>
            <tr>
              <th>Item</th>
              <th style="text-align: center;">Quantity</th>
              <th style="text-align: right;">Unit Price</th>
              <th style="text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHTML}
          </tbody>
        </table>

        <div class="receipt-total">
          <div class="receipt-row" style="font-size: 1.3rem;">
            <span><strong>Total:</strong></span>
            <span><strong>₱${parseFloat(data.total_amount || 0).toFixed(2)}</strong></span>
          </div>
          <div class="receipt-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
            <span>Paid Amount:</span>
            <span>₱${parseFloat(data.paid_amount || data.paid || 0).toFixed(2)}</span>
          </div>
          <div class="receipt-row">
            <span>Change:</span>
            <span>₱${parseFloat(data.return_amount || data.change || 0).toFixed(2)}</span>
          </div>
        </div>

        <div class="receipt-footer">
          <p><strong>Thank you for your purchase!</strong></p>
        </div>
      </div>
    `;
  }

  return `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>MEMBERSHIP PAYMENT RECEIPT</h2>
        <p><strong>Abstrack Fitness Gym</strong></p>
        <p>Toril, Davao Del Sur</p>
      </div>

      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px;">
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong>
          <span style="display: block; font-weight: 600;">#${data.receipt_number}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong>
          <span style="display: block; font-weight: 600;">${data.formatted_date || new Date(data.created_at).toLocaleString()}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Member Name</strong>
          <span style="display: block; font-weight: 600;">${data.member_name || 'N/A'}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Contact</strong>
          <span style="display: block; font-weight: 600;">${data.member_contact || data.contact || ''}</span>
        </div>
        ${data.buddy_name ? `
        <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;">
          <strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Gym Buddy</strong>
          <span style="display: block; font-weight: 600;">${data.buddy_name}</span>
        </div>
        <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;">
          <strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Contact</strong>
          <span style="display: block; font-weight: 600;">${data.buddy_contact || 'N/A'}</span>
        </div>
        ` : ''}
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Type</strong>
          <span style="display: block; font-weight: 600;">${(data.payment_type || '').toUpperCase()}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong>
          <span style="display: block; font-weight: 600;">${data.payment_method || 'N/A'}</span>
        </div>
      </div>

      <table class="receipt-table">
        <thead>
          <tr>
            <th>Description</th>
            <th style="text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          ${data.plan_type === 'GymBuddy' ? `
          <tr>
            <td>
              <strong>Gym Buddy Rate</strong><br>
              <small style="color: #666;">Duration: ${data.duration || 'N/A'} days | 2 Persons</small><br>
              <small style="color: #0d6efd;">Member 1: ${data.member_name}</small><br>
              <small style="color: #0d6efd;">Member 2: ${data.buddy_name || 'N/A'}</small>
            </td>
            <td style="text-align: right;">
              <span style="display: block;">₱${parseFloat(data.amount || 0).toFixed(2)}/person</span>
              <strong style="display: block; margin-top: 4px;">Total: ₱${(parseFloat(data.amount || 0) * 2).toFixed(2)}</strong>
            </td>
          </tr>
          ` : `
          <tr>
            <td>
              <strong>${data.plan_type || 'Membership'} Plan</strong><br>
              <small style="color: #666;">Duration: ${data.duration || 'N/A'} days</small>
            </td>
            <td style="text-align: right;">₱${parseFloat(data.amount || 0).toFixed(2)}</td>
          </tr>
          `}
        </tbody>
      </table>

      <div class="receipt-total">
        <div class="receipt-row" style="font-size: 1.3rem;">
          <span><strong>Total Paid:</strong></span>
          <span><strong>₱${data.plan_type === 'GymBuddy' ? (parseFloat(data.amount || 0) * 2).toFixed(2) : parseFloat(data.amount || 0).toFixed(2)}</strong></span>
        </div>
      </div>

      <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ccc;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Previous Due Date</strong>
            <span style="display: block; font-weight: 600;">${data.previous_due_date || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">New Due Date</strong>
            <span style="display: block; font-weight: 600; color: #28a745;">${data.new_due_date || 'N/A'}</span>
          </div>
        </div>
      </div>

      ${data.notes ? `
        <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
          <strong style="display: block; margin-bottom: 8px; color: #666;">Notes:</strong>
          <p style="margin: 0; color: #333;">${data.notes}</p>
        </div>
      ` : ''}

      <div class="receipt-footer">
        <p><strong>Thank you for your membership!</strong></p>
        <p style="font-size: 0.875rem;">Please keep this receipt for your records.</p>
      </div>
    </div>
  `;
}

function closeViewReceiptModal() {
  document.getElementById('viewReceiptModal').classList.remove('show');
}

function printViewReceipt() {
  const content = document.getElementById('viewReceiptContent').innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Receipt</title>
      <style>
        body { font-family: 'Courier New', monospace; }
        .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
        .receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .receipt-table th { background: #333; color: white; padding: 10px; text-align: left; }
        .receipt-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
        .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; }
      </style>
    </head>
    <body>${content}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}

function addslashes(str) {
  return (str+'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\0/g,'\\0');
}

// Close modals on escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeRefundModal();
    closeRefundReceiptModal();
    closeViewReceiptModal();
    closeDeleteModal();
  }
});

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(modal => {
  modal.addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('show');
    }
  });
});

// Dropdown toggle
document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      if (menu !== this.nextElementSibling) {
        menu.classList.remove('show');
      }
    });
    
    const menu = this.nextElementSibling;
    if (menu?.classList.contains('dropdown-menu')) {
      menu.classList.toggle('show');
    }
  });
});

document.addEventListener('click', function(e) {
  if (!e.target.closest('.dropdown')) {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      menu.classList.remove('show');
    });
  }
});
</script>
@endpush