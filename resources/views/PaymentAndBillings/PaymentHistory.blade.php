@extends('layouts.admin')

@section('title', 'Payments & Billing - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/payment-history.css'])
@endpush

@section('content')
  <!-- Page Header -->
    <div class="card page-header-card">
      <div class="card-body page-header-body">
        <div>
          <h2 class="page-header-title">Payment History</h2>
          <p class="page-header-subtitle">View and manage all payment transaction records.</p>
        </div>
        <div class="refund-toggle-group">
          <span class="refund-toggle-label">Refunded Payments</span>
          <div class="history-segmented-toggle" role="tablist" aria-label="Refunded payments visibility">
            <button type="button" class="history-segment-btn active" data-refund-visibility="hide" aria-pressed="true">
              <i class="mdi mdi-eye-off-outline"></i>
              <span>Hide</span>
            </button>
            <button type="button" class="history-segment-btn" data-refund-visibility="show" aria-pressed="false">
              <i class="mdi mdi-eye-outline"></i>
              <span>Show</span>
            </button>
          </div>
        </div>
      </div>
    </div>

  @include('PaymentAndBillings.partials.history._stats-cards')

  <!-- ========================================== -->
  <!-- PAGE TOGGLE: Membership / PT / Product     -->
  <!-- ========================================== -->
  <div class="page-toggle-container">
    <button class="page-toggle-btn active" data-page="membership">
      <i class="mdi mdi-account-group"></i>
      <span>Membership Payments</span>
    </button>
    <button class="page-toggle-btn" data-page="pt">
      <i class="mdi mdi-dumbbell"></i>
      <span>Personal Training Payments</span>
    </button>
    <button class="page-toggle-btn" data-page="product">
    <i class="mdi mdi-cart-outline"></i>
      <span>Product Payments</span>
    </button>
  </div>

  <!-- ========================================== -->
  <!-- SIBLING PAGES WRAPPER                      -->
  <!-- ========================================== -->
  <div class="pages-slider">
    @include('PaymentAndBillings.partials.history._membership-table')

    @include('PaymentAndBillings.partials.history._pt-table')

    @include('PaymentAndBillings.partials.history._product-table')
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
                style="width: 100%; max-width: 450px;"
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
            <div class="dropdown-menu dropdown-menu-right filter-accordion">
              <div class="filter-header">
                <span class="filter-title">Filter By</span>
                <a href="{{ route('payments.history', request()->except(['refund_filter', 'refunded_page'])) }}" class="filter-clear-all">Clear All</a>
              </div>

              <!-- Type Filter -->
              <div class="filter-section">
                <div class="filter-section-header" onclick="PaymentHistoryPage.toggleFilterSection(this, event)">
                  <div class="filter-section-title">
                    <i class="mdi mdi-tag-outline"></i>
                    <span>Type</span>
                  </div>
                  <i class="mdi mdi-chevron-down filter-chevron"></i>
                </div>
                <div class="filter-section-content">
                  <a class="filter-option {{ request('refund_filter', 'all') === 'all' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'all'])) }}">
                    <i class="mdi mdi-account-multiple"></i> All
                  </a>
                  <a class="filter-option {{ request('refund_filter') === 'product' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'product'])) }}">
                    <i class="mdi mdi-basket"></i> Products Only
                  </a>
                  <a class="filter-option {{ request('refund_filter') === 'membership' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'membership'])) }}">
                    <i class="mdi mdi-account"></i> Memberships Only
                  </a>
                  <a class="filter-option {{ request('refund_filter') === 'pt' ? 'active' : '' }}" href="{{ route('payments.history', array_merge(request()->except(['refund_filter', 'refunded_page']), ['refund_filter' => 'pt'])) }}">
                    <i class="mdi mdi-dumbbell"></i> PT Only
                  </a>
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
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-display="static" data-boundary="window" aria-haspopup="true" aria-expanded="false">
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

  @include('PaymentAndBillings.partials.modals._payment-history-modals')
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

<!-- Config element for JS data passing -->
<div id="paymentHistoryConfig" class="hidden"
     data-csrf-token="{{ csrf_token() }}"
     data-bulk-delete-product-route="{{ route('payments.bulkDelete') }}"
     data-bulk-delete-membership-route="{{ route('membership.payment.bulkDelete') }}"
     data-bulk-delete-pt-route="{{ route('pt.payment.bulkDelete') }}"
     data-flash-success="{{ session('success') ?? '' }}"
     data-flash-error="{{ session('error') ?? '' }}"
     data-flash-errors="{{ $errors->any() ? $errors->first() : '' }}"></div>

<!-- Hidden bulk-delete forms -->
<form id="bulkDeletePtForm" method="POST" style="display:none;"></form>

@vite(['resources/js/pages/payment-history.js'])
@endpush