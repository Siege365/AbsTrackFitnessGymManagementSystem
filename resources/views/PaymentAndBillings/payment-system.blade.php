@extends('layouts.admin')

@section('title', 'Payment System')

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
      <i class="mdi mdi-card-account-details-outline"></i>
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
<div id="membershipPaymentConfig" class="hidden"
     data-member-search-url="{{ url('/api/members/search') }}"
     data-duplicate-check-url="{{ url('/api/members/check-duplicate') }}"></div>
<div id="productPaymentConfig" class="hidden"
     data-inventory-items='@json($inventoryItems ?? [])'
     data-member-search-url="{{ url('/members/search') }}"></div>

<!-- Load payment-specific JS files -->
@vite(['resources/js/pages/membership-payment.js'])
@vite(['resources/js/pages/pt-payment.js'])
@vite(['resources/js/pages/product-payment.js'])

<script>
// ========================================
// PAGE TOGGLE (Membership / PT / Product)
// ========================================
document.addEventListener('DOMContentLoaded', function() {
  const pageToggleBtns = document.querySelectorAll('.page-toggle-btn');
  const pageMap = { 'membership': 'membershipPage', 'pt': 'ptPage', 'product': 'productPage' };
  const pageOrder = ['membership', 'pt', 'product'];

  pageToggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetPage = this.dataset.page;
      const targetPanelId = pageMap[targetPage];
      
      // Update button states
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      const currentActive = document.querySelector('.page-panel.active');
      const targetPanel = document.getElementById(targetPanelId);
      
      if (currentActive === targetPanel) return;
      
      // Determine slide direction
      const currentPageKey = Object.keys(pageMap).find(k => pageMap[k] === currentActive.id);
      const currentIndex = pageOrder.indexOf(currentPageKey);
      const targetIndex = pageOrder.indexOf(targetPage);
      const goingRight = targetIndex > currentIndex;
      
      // Apply slide animations
      currentActive.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');
      targetPanel.classList.add(goingRight ? 'slide-in-right' : 'slide-in-left');
      targetPanel.classList.add('active');
      
      // Cleanup animations after transition
      setTimeout(() => {
        currentActive.classList.remove('active', 'slide-out-left', 'slide-out-right');
        targetPanel.classList.remove('slide-in-right', 'slide-in-left');
      }, 400);
    });
  });

  // Auto-switch to tab from server-provided payment type
  const serverPaymentType = '{{ $paymentType ?? "membership" }}';
  if (serverPaymentType && pageMap[serverPaymentType]) {
    const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${serverPaymentType}"]`);
    if (targetBtn) {
      // Disable animations for initial load
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      targetBtn.classList.add('active');
      document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
      document.getElementById(pageMap[serverPaymentType]).classList.add('active');
    }
  }

  // Update browser URL when switching tabs (SPA navigation)
  pageToggleBtns.forEach(btn => {
    const originalClickHandler = btn.onclick;
    btn.addEventListener('click', function() {
      const page = this.dataset.page;
      const routes = {
        'membership': '{{ route("payment.system.membership") }}',
        'pt': '{{ route("payment.system.pt") }}',
        'product': '{{ route("payment.system.product") }}'
      };
      if (routes[page]) {
        // Update URL without page reload
        window.history.pushState({ paymentType: page }, '', routes[page]);
      }
    }, true);
  });

  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(event) {
    if (event.state && event.state.paymentType) {
      const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${event.state.paymentType}"]`);
      if (targetBtn) {
        targetBtn.click();
      }
    }
  });
});

// Global modal handlers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    // Close any open modals
    if (typeof closeModal === 'function') closeModal();
    if (typeof closeConfirmationModal === 'function') closeConfirmationModal();
    if (typeof closePtConfirmation === 'function') closePtConfirmation();
    if (typeof closeProdConfirmation === 'function') closeProdConfirmation();
    if (typeof closeProductReceiptModal === 'function') closeProductReceiptModal();
    if (typeof closeProductRefundModal === 'function') closeProductRefundModal();
  }
});

document.querySelectorAll('.modal-overlay').forEach(modal => {
  modal.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
  });
});

// Display flash messages
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
