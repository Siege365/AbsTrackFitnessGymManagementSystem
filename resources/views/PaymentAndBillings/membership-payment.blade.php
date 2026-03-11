@extends('layouts.admin')

@section('title', 'Payments & Billing - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/membership-payment.css'])
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

  <!-- Membership Payment Form -->
  @include('PaymentAndBillings.partials._membership-form')

  <!-- Modals -->
  @include('PaymentAndBillings.partials.modals._membership-modals')

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

<div id="membershipPaymentConfig" class="hidden"
     data-member-search-url="{{ url('/api/members/search') }}"
     data-duplicate-check-url="{{ url('/api/members/check-duplicate') }}"></div>
@vite(['resources/js/pages/membership-payment.js'])

<script>
// Global modal handlers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (typeof closeModal === 'function') closeModal();
    if (typeof closeConfirmationModal === 'function') closeConfirmationModal();
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
