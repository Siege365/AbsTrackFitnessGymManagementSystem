<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
    <a class="sidebar-brand brand-logo" href="{{ url('/') }}"><img src="{{ asset('template/assets/images/logohorizontal.svg') }}" alt="logo" /></a>
    <a class="sidebar-brand brand-logo-mini" href="{{ url('/') }}"><img src="{{ asset('template/assets/images/abstractlogotransparent.svg') }}" alt="logo" /></a>
  </div>
  <ul class="nav">
    <li class="nav-item nav-category">
      <span class="nav-link">Navigation</span>
    </li>
    <li class="nav-item menu-items {{ request()->is('/') ? 'active' : '' }}">
      <a class="nav-link" href="{{ url('/') }}">
        <span class="menu-icon">
          <i class="mdi mdi-speedometer"></i>
        </span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#customers-menu" aria-expanded="false" aria-controls="customers-menu">
        <span class="menu-icon">
          <i class="mdi mdi-account-multiple"></i>
        </span>
        <span class="menu-title">Customers</span>
        <i class="menu-arrow mdi mdi-chevron-down"></i>
      </a>

      <div class="collapse" id="customers-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('memberships.index') }}">Memberships</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('clients.index') }}">PT Clients</a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#sessions-menu" aria-expanded="false" aria-controls="sessions-menu">
        <span class="menu-icon">
          <i class="mdi mdi-calendar-clock"></i>
        </span>
        <span class="menu-title">Sessions</span>
        <i class="menu-arrow mdi mdi-chevron-down"></i>
      </a>
      <div class="collapse" id="sessions-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('sessions.pt.index') }}">Training Sessions</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('sessions.attendance.index') }}">Customer Attendance</a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#payments-menu" aria-expanded="false" aria-controls="payments-menu">
        <span class="menu-icon">
          <i class="mdi mdi-credit-card"></i>
        </span>
        <span class="menu-title">Payments & Billing</span>
        <i class="menu-arrow mdi mdi-chevron-down"></i>
      </a>
      <div class="collapse" id="payments-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('membership.payment.index') }}">Payment System</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('payments.history') }}">Payment History</a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#inventory-menu" aria-expanded="false" aria-controls="inventory-menu">
        <span class="menu-icon">
          <i class="mdi mdi-package-variant-closed"></i>
        </span>
        <span class="menu-title">Inventory Supplies</span>
        <i class="menu-arrow mdi mdi-chevron-down"></i>
      </a>
      <div class="collapse" id="inventory-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory.index') }}">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory.logs') }}">Inventory Logs</a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ route('ReportAndBilling') }}">
        <span class="menu-icon">
          <i class="mdi mdi-chart-bar"></i>
        </span>
        <span class="menu-title">Reports & Analytics</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
        <span class="menu-icon">
          <i class="mdi mdi-account-key"></i>
        </span>
        <span class="menu-title">Staff Management</span>
        <i class="menu-arrow mdi mdi-chevron-down"></i>
      </a>
      <div class="collapse" id="auth">
        <ul class="nav flex-column sub-menu">
          @if(auth()->user() && auth()->user()->isAdmin())
          <li class="nav-item"> <a class="nav-link" href="{{ route('UserAndAdmin.UserManagement') }}"> Staff Accounts </a></li>
          <li class="nav-item"> <a class="nav-link" href="{{ route('UserAndAdmin.TrainerManagement') }}"> Trainers </a></li>
          @endif
          <li class="nav-item"> <a class="nav-link" href="{{ route('UserAndAdmin.CashierActivity') }}"> Activity Logs </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ route('configuration.index') }}">
        <span class="menu-icon">
          <i class="mdi mdi-settings"></i>
        </span>
        <span class="menu-title">Settings</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="documentation">
        <span class="menu-icon">
          <i class="mdi mdi-file-document-box"></i>
        </span>
        <span class="menu-title">User Manual</span>
      </a>
    </li>
  </ul>
</nav>