@extends('layouts.admin')

@section('title', 'Payments & Billing - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/membership-payment.css'])
@vite(['resources/css/product-payment.css'])
@endpush

@section('content')

  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Payment System</h2>
              <p class="page-header-subtitle">Process membership, personal training & product payments</p>
          </div>
      </div>
  </div>

  <!-- PAGE TOGGLE: Membership / PT / Product     -->
  <div class="page-toggle-container">
    <button class="page-toggle-btn active" data-page="membership" id="membershipTabBtn">
      <i class="mdi mdi-account-group"></i>
      <span>Membership Payment</span>
    </button>
    <button class="page-toggle-btn" data-page="pt" id="ptTabBtn">
      <i class="mdi mdi-dumbbell"></i>
      <span>Personal Training Payment</span>
    </button>
    <button class="page-toggle-btn" data-page="product" id="productTabBtn">
      <i class="mdi mdi-cart-outline"></i>
      <span>Product Payment</span>
    </button>
  </div>

  <!-- SIBLING PAGES WRAPPER                      -->
  <div class="pages-slider">

    <div class="page-panel active" id="membershipPage">
      @include('PaymentAndBillings.partials._membership-form')
    </div>

    <div class="page-panel" id="ptPage">
      @include('PaymentAndBillings.partials._pt-form')
    </div>

    <div class="page-panel" id="productPage">
      @include('PaymentAndBillings.partials._product-form')
    </div>

  </div>

  @include('PaymentAndBillings.partials.modals._membership-modals')
  @include('PaymentAndBillings.partials.modals._pt-modals')
  @include('PaymentAndBillings.partials.modals._product-modals')

@endsection

@push('scripts')
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/avatar-utils.js'])
@vite(['resources/js/common/form-utils.js'])

<script>
// Fallback ToastUtils
if (typeof ToastUtils === 'undefined') {
  window.ToastUtils = {
    showSuccess: function(msg) { console.log('Success:', msg); alert('Success: ' + msg); },
    showError: function(msg) { console.error('Error:', msg); alert('Error: ' + msg); },
    showWarning: function(msg) { console.warn('Warning:', msg); alert('Warning: ' + msg); },
    showInfo: function(msg) { console.info('Info:', msg); }
  };
}
</script>

<!-- Config elements for JS data passing -->
<div id="paymentSystemConfig" class="hidden"
     data-initial-tab="{{ $paymentType ?? 'membership' }}"
     data-membership-route="{{ route('payment.system.membership') }}"
     data-pt-route="{{ route('payment.system.pt') }}"
     data-product-route="{{ route('payment.system.product') }}"
     data-flash-success="{{ session('success') ?? '' }}"
     data-flash-error="{{ session('error') ?? '' }}"
     data-flash-errors="{{ $errors->any() ? $errors->first() : '' }}"></div>
<div id="membershipPaymentConfig" class="hidden"
     data-member-search-url="{{ url('/api/members/search') }}"
     data-duplicate-check-url="{{ url('/api/members/check-duplicate') }}"></div>
<div id="productPaymentConfig" class="hidden"
     data-inventory-items='@json($inventoryItems ?? [])'
     data-member-search-url="{{ url('/members/search') }}"></div>

<!-- Load payment-specific JS files -->
@vite(['resources/js/pages/payment-system.js'])
@vite(['resources/js/pages/membership-payment.js'])
@vite(['resources/js/pages/pt-payment.js'])
@vite(['resources/js/pages/product-payment.js'])
@endpush
