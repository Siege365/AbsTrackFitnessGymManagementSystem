<nav class="navbar p-0 fixed-top d-flex flex-row">
  <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
    <a class="navbar-brand brand-logo-mini" href="{{ url('/') }}"><img src="{{ asset('template/assets/images/navbar logo mini.png') }}" alt="logo" /></a>
  </div>
  <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
      <span class="mdi mdi-menu"></span>
    </button>
    <ul class="navbar-nav navbar-nav-right">
      <!-- Quick Actions Dropdown (always visible) -->
      <li class="nav-item dropdown d-none d-lg-block">
        <a class="nav-link btn btn-success create-new-button" id="createbuttonDropdown" data-toggle="dropdown" aria-expanded="false" href="#">+ Quick Actions</a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown navbar-quick-actions" aria-labelledby="createbuttonDropdown">
          <div class="quick-actions-header">
            <i class="mdi mdi-lightning-bolt"></i>
            <span>Quick Actions</span>
          </div>
          {{-- Customers Subsystem --}}
          <div class="quick-actions-group-label">Customers</div>
          <a class="dropdown-item quick-action-item" href="{{ route('memberships.index', ['action' => 'add']) }}">
            <div class="quick-action-icon" style="background: rgba(255, 193, 7, 0.15);">
              <i class="mdi mdi-account-plus" style="color: #ffc107;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Add Member</span>
              <span class="quick-action-desc">Register new member</span>
            </div>
          </a>
          <a class="dropdown-item quick-action-item" href="{{ route('clients.index', ['action' => 'add']) }}">
            <div class="quick-action-icon" style="background: rgba(255, 193, 7, 0.15);">
              <i class="mdi mdi-account-multiple" style="color: #ffc107;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Add PT Client</span>
              <span class="quick-action-desc">Register PT client</span>
            </div>
          </a>
          {{-- Sessions Subsystem --}}
          <div class="quick-actions-group-label">Sessions</div>
          <a class="dropdown-item quick-action-item" href="{{ route('sessions.attendance.index', ['action' => 'add']) }}">
            <div class="quick-action-icon" style="background: rgba(33, 150, 243, 0.15);">
              <i class="mdi mdi-account-check" style="color: #2196f3;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Log Attendance</span>
              <span class="quick-action-desc">Check in customer</span>
            </div>
          </a>
          <a class="dropdown-item quick-action-item" href="{{ route('sessions.pt.index', ['action' => 'add']) }}">
            <div class="quick-action-icon" style="background: rgba(33, 150, 243, 0.15);">
              <i class="mdi mdi-calendar-clock" style="color: #2196f3;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Schedule PT Session</span>
              <span class="quick-action-desc">Book training session</span>
            </div>
          </a>
          {{-- Payments & Billing Subsystem --}}
          <div class="quick-actions-group-label">Payments & Billing</div>
          <a class="dropdown-item quick-action-item" href="{{ route('payment.system.membership') }}">
            <div class="quick-action-icon" style="background: rgba(244, 67, 54, 0.15);">
              <i class="mdi mdi-credit-card" style="color: #f44336;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">New Payment</span>
              <span class="quick-action-desc">Process payment</span>
            </div>
          </a>
          {{-- Inventory Supplies Subsystem --}}
          <div class="quick-actions-group-label">Inventory</div>
          <a class="dropdown-item quick-action-item" href="{{ route('inventory.index', ['action' => 'add']) }}">
            <div class="quick-action-icon" style="background: rgba(124, 77, 255, 0.15);">
              <i class="mdi mdi-package-variant-closed" style="color: #7c4dff;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Add Product</span>
              <span class="quick-action-desc">Create new product</span>
            </div>
          </a>
          {{-- Reports & Analytics Subsystem --}}
          <div class="quick-actions-group-label">Reports</div>
          <a class="dropdown-item quick-action-item" href="{{ route('reports.index') }}">
            <div class="quick-action-icon" style="background: rgba(0, 210, 91, 0.15);">
              <i class="mdi mdi-chart-bar" style="color: #00d25b;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">View Reports</span>
              <span class="quick-action-desc">Analytics & insights</span>
            </div>
          </a>
        </div>
      </li>
    
      <!-- Notification Bell Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
          <i class="mdi mdi-bell"></i>
          <span class="count bg-danger"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list notification-dropdown" aria-labelledby="notificationDropdown">
          <div class="notification-header">
            <h6 class="mb-0">Notifications</h6>
            <a href="#" class="notification-mark-all-read" title="Mark all as read">
              <i class="mdi mdi-check-all"></i>
            </a>
          </div>
          <div class="dropdown-divider"></div>
          <div id="notificationList" class="notification-list-container">
            <div class="notification-empty">
              <i class="mdi mdi-bell-off-outline"></i>
              <p>Loading notifications...</p>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <div class="notification-footer">
            <a href="{{ route('notifications.page') }}">View all notifications</a>
          </div>
        </div>
      </li>

      <!-- Profile Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link" id="profileDropdown" href="#" data-toggle="dropdown">
          <div class="navbar-profile">
            <img class="img-xs rounded-circle" src="{{ asset('template/assets/images/faces/face15.jpg') }}" alt="">
            <p class="mb-0 d-none d-sm-block navbar-profile-name">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</p>
            <i class="mdi mdi-menu-down d-none d-sm-block"></i>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown navbar-profile-dropdown" aria-labelledby="profileDropdown">
          <div class="profile-dropdown-header">
            <img class="profile-dropdown-avatar" src="{{ asset('template/assets/images/faces/face15.jpg') }}" alt="">
            <div class="profile-dropdown-info">
              <p class="profile-dropdown-name">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</p>
              <p class="profile-dropdown-email">{{ Auth::check() ? Auth::user()->email : '' }}</p>
            </div>
          </div>
          <div class="profile-dropdown-divider"></div>
          <a href="{{ route('account.settings') }}" class="dropdown-item profile-dropdown-item">
            <div class="profile-dropdown-icon" style="background: rgba(0, 123, 255, 0.15);">
              <i class="mdi mdi-account-edit" style="color: #4da3ff;"></i>
            </div>
            <span>Account Settings</span>
          </a>
          <div class="profile-dropdown-divider"></div>
          <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="dropdown-item profile-dropdown-item">
              <div class="profile-dropdown-icon" style="background: rgba(220, 53, 69, 0.15);">
                <i class="mdi mdi-logout" style="color: #f87171;"></i>
              </div>
              <span>Log Out</span>
            </button>
          </form>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="mdi mdi-format-line-spacing"></span>
    </button>
  </div>
</nav>