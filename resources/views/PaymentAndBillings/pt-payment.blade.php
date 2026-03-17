@extends('layouts.admin')

@section('title', 'Payments & Billing - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/membership-payment.css'])
@vite(['resources/css/unified-receipt.css'])
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

  <!-- PT Payment Form -->
  @include('PaymentAndBillings.partials._pt-form')

  <!-- Modals -->
  @include('PaymentAndBillings.partials.modals._pt-modals')

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

@vite(['resources/js/pages/pt-payment.js'])

<script>
// Global modal handlers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (typeof closePtConfirmation === 'function') closePtConfirmation();
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
