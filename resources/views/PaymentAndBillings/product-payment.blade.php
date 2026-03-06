@extends('layouts.admin')

@section('title', 'Product Payment')

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

  <!-- Page Navigation -->
  @include('PaymentAndBillings.partials._page-nav')

  <!-- Product Payment Form -->
  @include('PaymentAndBillings.partials._product-form')

  <!-- Modals -->
  @include('PaymentAndBillings.partials.modals._product-modals')

@endsection

@push('scripts')
@vite(['resources/js/common/table-dropdown.js'])
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

<div id="productPaymentConfig" class="hidden"
     data-inventory-items='@json($inventoryItems ?? [])'
     data-member-search-url="{{ url('/members/search') }}"></div>
@vite(['resources/js/pages/product-payment.js'])

<script>
// Global modal handlers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (typeof closeProdConfirmation === 'function') closeProdConfirmation();
    if (typeof closeProductReceiptModal === 'function') closeProductReceiptModal();
    if (typeof closeProductRefundModal === 'function') closeProductRefundModal();
  }
});
document.querySelectorAll('.modal-overlay').forEach(modal => {
  modal.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
});

@if(session('success'))
  ToastUtils.showSuccess('{{ session('success') }}');
@endif
@if(session('error'))
  ToastUtils.showError('{{ session('error') }}');
@endif
@if($errors->any())
  ToastUtils.showError('{{ $errors->first() }}');
@endif
</script>
@endpush
