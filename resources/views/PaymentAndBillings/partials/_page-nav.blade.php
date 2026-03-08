<!-- ========================================== -->
<!-- PAGE NAV: Membership / PT / Product       -->
<!-- ========================================== -->
<div class="page-toggle-container">
  <a class="page-toggle-btn{{ request()->routeIs('membership.payment.index') ? ' active' : '' }}" href="{{ route('membership.payment.index') }}">
    <i class="mdi mdi-account-group"></i>
    <span>Membership Payment</span>
  </a>
  <a class="page-toggle-btn{{ request()->routeIs('pt.payment.index') ? ' active' : '' }}" href="{{ route('pt.payment.index') }}">
    <i class="mdi mdi-dumbbell"></i>
    <span>Personal Training Payment</span>
  </a>
  <a class="page-toggle-btn{{ request()->routeIs('product.payment.index') ? ' active' : '' }}" href="{{ route('product.payment.index') }}">
    <i class="mdi mdi-cart-outline"></i>
    <span>Product Payment</span>
  </a>
</div>
