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
          <a class="dropdown-item quick-action-item" href="{{ route('memberships.create') }}">
            <div class="quick-action-icon" style="background: rgba(0, 123, 255, 0.15);">
              <i class="mdi mdi-account-plus" style="color: #4da3ff;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Add Member</span>
              <span class="quick-action-desc">Register new membership</span>
            </div>
          </a>
          <a class="dropdown-item quick-action-item" href="{{ route('clients.create') }}">
            <div class="quick-action-icon" style="background: rgba(23, 162, 184, 0.15);">
              <i class="mdi mdi-account-star" style="color: #3dd5f3;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Add PT Client</span>
              <span class="quick-action-desc">Register new PT client</span>
            </div>
          </a>
          {{-- Sessions Subsystem --}}
          <div class="quick-actions-group-label">Sessions</div>
          <a class="dropdown-item quick-action-item" href="#" data-toggle="modal" data-target="#addAttendanceModal">
            <div class="quick-action-icon" style="background: rgba(40, 167, 69, 0.15);">
              <i class="mdi mdi-account-check" style="color: #5dd879;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Log Attendance</span>
              <span class="quick-action-desc">Record customer attendance</span>
            </div>
          </a>
          <a class="dropdown-item quick-action-item" href="#" data-toggle="modal" data-target="#addPTScheduleModal">
            <div class="quick-action-icon" style="background: rgba(255, 167, 38, 0.15);">
              <i class="mdi mdi-calendar-plus" style="color: #FFB84D;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Schedule PT Session</span>
              <span class="quick-action-desc">Book a training session</span>
            </div>
          </a>
          {{-- Payments & Billing Subsystem --}}
          <div class="quick-actions-group-label">Payments & Billing</div>
          <a class="dropdown-item quick-action-item" href="{{ route('payment.system.membership') }}">
            <div class="quick-action-icon" style="background: rgba(111, 66, 193, 0.15);">
              <i class="mdi mdi-credit-card-plus" style="color: #a78bfa;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">New Payment</span>
              <span class="quick-action-desc">Process a new payment</span>
            </div>
          </a>
          {{-- Inventory Supplies Subsystem --}}
          <div class="quick-actions-group-label">Inventory</div>
          <a class="dropdown-item quick-action-item" href="{{ route('inventory.index') }}">
            <div class="quick-action-icon" style="background: rgba(220, 53, 69, 0.15);">
              <i class="mdi mdi-package-variant-closed" style="color: #f87171;"></i>
            </div>
            <div class="quick-action-content">
              <span class="quick-action-title">Manage Inventory</span>
              <span class="quick-action-desc">View & manage stock</span>
            </div>
          </a>
          {{-- Reports & Analytics Subsystem --}}
          <div class="quick-actions-group-label">Reports</div>
          <a class="dropdown-item quick-action-item" href="{{ route('reports.index') }}">
            <div class="quick-action-icon" style="background: rgba(255, 193, 7, 0.15);">
              <i class="mdi mdi-chart-bar" style="color: #fbbf24;"></i>
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
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
          <h6 class="p-3 mb-0">Notifications</h6>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-success">
                <i class="mdi mdi-account-plus"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal mb-1">New Member Registration</h6>
              <p class="font-weight-light small-text mb-0 text-muted">
                John Doe registered
              </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-info">
                <i class="mdi mdi-clock"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal mb-1">Membership Expiring Soon</h6>
              <p class="font-weight-light small-text mb-0 text-muted">
                5 members expiring this week
              </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-warning">
                <i class="mdi mdi-credit-card"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal mb-1">Payment Received</h6>
              <p class="font-weight-light small-text mb-0 text-muted">
                $150 from Jane Smith
              </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-danger">
                <i class="mdi mdi-package-variant"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal mb-1">Low Inventory Alert</h6>
              <p class="font-weight-light small-text mb-0 text-muted">
                Protein Powder stock is low
              </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <p class="p-3 mb-0 text-center">View all notifications</p>
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