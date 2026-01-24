<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
    <a class="sidebar-brand brand-logo" href="{{ url('/') }}"><img src="{{ asset('template/assets/images/navbar logo.png') }}" alt="logo" /></a>
    <a class="sidebar-brand brand-logo-mini" href="{{ url('/') }}"><img src="{{ asset('template/assets/images/navbar logo mini.png') }}" alt="logo" /></a>
  </div>
  <ul class="nav">
    <li class="nav-item profile">
      <div class="profile-desc">
        <div class="profile-pic">
          <div class="count-indicator">
            <img class="img-xs rounded-circle " src="{{ asset('template/assets/images/faces/face15.jpg') }}" alt="">
            <span class="count bg-success"></span>
          </div>
          <div class="profile-name">
            <h5 class="mb-0 font-weight-normal">{{ Auth::user()->name }}</h5>
          </div>
        </div>
      </div>
    </li>
    <li class="nav-item nav-category">
      <span class="nav-link">Navigation</span>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ url('/') }}">
        <span class="menu-icon">
          <i class="mdi mdi-speedometer"></i>
        </span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
        <span class="menu-icon">
          <i class="mdi mdi-account-multiple"></i>
        </span>
        <span class="menu-title">Customers</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="ui-basic">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="{{ route('memberships.index') }}">Membership</a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ route('clients.index') }}">Clients</a></li>
          <!--<li class="nav-item"> <a class="nav-link" href="{{ url('ui-features/typography') }}">Typography</a></li>-->
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ route('Session') }}">
        <span class="menu-icon">
          <i class="mdi mdi-table-large"></i>
        </span>
        <span class="menu-title">Sessions</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ route('PaymentAndBilling') }}">
        <span class="menu-icon">
          <i class="mdi mdi-playlist-play"></i>
        </span>
        <span class="menu-title">Payments & Billing</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ route('inventory.index') }}">
        <span class="menu-icon">
          <i class="mdi mdi-contacts" style="color: #8F5FE8;"></i>
        </span>
        <span class="menu-title">Inventory Supplies</span>
      </a>
    </li>
     <li class="nav-item menu-items">
      <a class="nav-link" href="{{ route('ReportAndBilling') }}">
        <span class="menu-icon">
          <i class="mdi mdi-chart-bar" style="color: #00D25B;"></i>
        </span>
        <span class="menu-title">Reports & Billing</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
        <span class="menu-icon">
          <i class="mdi mdi-security"></i>
        </span>
        <span class="menu-title">Users/Admin</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="auth">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="{{ route('UserAndAdmin.UserManagement') }}"> User Management </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ route('UserAndAdmin.TrainerManagement') }}"> Trainer Management </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ route('UserAndAdmin.CashierActivity') }}"> Cashier Activity </a></li>
          <!--<li class="nav-item"> <a class="nav-link" href="{{ url('samples/blank-page') }}"> Blank Page </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ url('samples/error-404') }}"> 404 </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ url('samples/error-500') }}"> 500 </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ url('samples/login') }}"> Login </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ url('samples/register') }}"> Register </a></li>-->
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="documentation">
        <span class="menu-icon">
          <i class="mdi mdi-file-document-box"></i>
        </span>
        <span class="menu-title">Documentation</span>
      </a>
    </li>
  </ul>
</nav>