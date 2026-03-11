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

  @include('PaymentAndBillings.partials.history._refunded-table')

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
  data-bulk-delete-pt-route="{{ route('pt.history.bulkDelete') }}"
  data-show-refunded-default="{{ request()->boolean('show_refunded') ? 'true' : 'false' }}"
     data-flash-success="{{ session('success') ?? '' }}"
     data-flash-error="{{ session('error') ?? '' }}"
     data-flash-errors="{{ $errors->any() ? $errors->first() : '' }}"></div>

@vite(['resources/js/pages/payment-history.js'])
@endpush