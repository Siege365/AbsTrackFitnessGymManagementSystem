@extends('layouts.admin')

@section('title', 'Account Settings')

@push('styles')
@vite(['resources/css/account-settings.css'])
@endpush

@section('content')

  <!-- Page Header -->
  <div class="card page-header-card">
    <div class="card-body">
      <div>
        <h2 class="page-header-title">Account Settings</h2>
        <p class="page-header-subtitle">Manage your profile information and security settings</p>
      </div>
      <div class="settings-nav">
        <button class="settings-nav-btn active" data-section="profile">
          <i class="mdi mdi-account-outline"></i>
          <span>Profile</span>
        </button>
        <button class="settings-nav-btn" data-section="security">
          <i class="mdi mdi-shield-lock-outline"></i>
          <span>Security</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Flash Messages -->
  @if(session('success'))
    <div class="settings-alert settings-alert-success">
      <i class="mdi mdi-check-circle"></i>
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="settings-alert settings-alert-error">
      <i class="mdi mdi-alert-circle"></i>
      {{ $errors->first() }}
    </div>
  @endif

  <!-- Profile Section -->
  <div class="settings-section active" id="section-profile">

    <!-- Profile Overview -->
    <div class="settings-card">
      <div class="settings-avatar-section">
        <img class="settings-avatar" src="{{ asset('template/assets/images/faces/face15.jpg') }}" alt="Profile Avatar">
        <div class="settings-avatar-info">
          <h4>{{ $user->name }}</h4>
          <p>{{ $user->email }}</p>
        </div>
      </div>

      <div class="settings-card-header">
        <div class="settings-card-icon" style="background: rgba(0, 123, 255, 0.15);">
          <i class="mdi mdi-account-edit" style="color: #4da3ff;"></i>
        </div>
        <div>
          <h3 class="settings-card-title">Profile Information</h3>
          <p class="settings-card-subtitle">Update your account name and email address</p>
        </div>
      </div>

      <form method="POST" action="{{ route('account.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="settings-form-row">
          <div class="settings-form-group">
            <label class="settings-form-label">
              Full Name <span class="required">*</span>
            </label>
            <input
              type="text"
              name="name"
              class="settings-form-input"
              value="{{ old('name', $user->name) }}"
              placeholder="Enter your full name"
              required
              pattern="[A-Za-z\s\-']+"
              title="Only letters, spaces, hyphens, and apostrophes"
            >
            @error('name')
              <span class="settings-error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
            @enderror
          </div>

          <div class="settings-form-group">
            <label class="settings-form-label">
              Email Address <span class="required">*</span>
            </label>
            <input
              type="email"
              name="email"
              class="settings-form-input"
              value="{{ old('email', $user->email) }}"
              placeholder="Enter your email"
              required
            >
            @error('email')
              <span class="settings-error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="settings-form-actions">
          <button type="button" class="settings-btn-cancel" onclick="window.location.reload()">Cancel</button>
          <button type="submit" class="settings-btn-save">
            <i class="mdi mdi-content-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>

    <!-- Account Info -->
    <div class="settings-card">
      <div class="settings-card-header">
        <div class="settings-card-icon" style="background: rgba(255, 167, 38, 0.15);">
          <i class="mdi mdi-information-outline" style="color: #FFB84D;"></i>
        </div>
        <div>
          <h3 class="settings-card-title">Account Information</h3>
          <p class="settings-card-subtitle">Details about your account</p>
        </div>
      </div>

      <div class="account-info-item">
        <span class="account-info-label">Account Created</span>
        <span class="account-info-value">{{ $user->created_at->format('F j, Y') }}</span>
      </div>
      <div class="account-info-item">
        <span class="account-info-label">Last Updated</span>
        <span class="account-info-value">{{ $user->updated_at->format('F j, Y \a\t g:i A') }}</span>
      </div>
      <div class="account-info-item">
        <span class="account-info-label">Account ID</span>
        <span class="account-info-value">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
      </div>
    </div>
  </div>

  <!-- Security Section -->
  <div class="settings-section" id="section-security">
    <div class="settings-card">
      <div class="settings-card-header">
        <div class="settings-card-icon" style="background: rgba(40, 167, 69, 0.15);">
          <i class="mdi mdi-lock-outline" style="color: #5dd879;"></i>
        </div>
        <div>
          <h3 class="settings-card-title">Change Password</h3>
          <p class="settings-card-subtitle">Ensure your account uses a strong, unique password</p>
        </div>
      </div>

      <form method="POST" action="{{ route('account.password.update') }}" id="passwordForm">
        @csrf
        @method('PUT')

        <div class="settings-form-row">
          <div class="settings-form-group full-width">
            <label class="settings-form-label">
              Current Password <span class="required">*</span>
            </label>
            <div class="password-input-wrapper">
              <input
                type="password"
                name="current_password"
                class="settings-form-input"
                placeholder="Enter your current password"
                required
              >
              <button type="button" class="toggle-password-btn" onclick="togglePassword(this)">
                <i class="mdi mdi-eye-off"></i>
              </button>
            </div>
            @error('current_password')
              <span class="settings-error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
            @enderror
          </div>
        </div>

        <div class="settings-form-row">
          <div class="settings-form-group">
            <label class="settings-form-label">
              New Password <span class="required">*</span>
            </label>
            <div class="password-input-wrapper">
              <input
                type="password"
                name="password"
                id="newPassword"
                class="settings-form-input"
                placeholder="Enter new password"
                required
                minlength="8"
                oninput="checkPasswordStrength(this.value)"
              >
              <button type="button" class="toggle-password-btn" onclick="togglePassword(this)">
                <i class="mdi mdi-eye-off"></i>
              </button>
            </div>
            <div class="password-strength">
              <div class="password-strength-bar" id="str-bar-1"></div>
              <div class="password-strength-bar" id="str-bar-2"></div>
              <div class="password-strength-bar" id="str-bar-3"></div>
              <div class="password-strength-bar" id="str-bar-4"></div>
            </div>
            <span class="password-strength-text" id="str-text"></span>
            @error('password')
              <span class="settings-error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</span>
            @enderror
          </div>

          <div class="settings-form-group">
            <label class="settings-form-label">
              Confirm New Password <span class="required">*</span>
            </label>
            <div class="password-input-wrapper">
              <input
                type="password"
                name="password_confirmation"
                class="settings-form-input"
                placeholder="Re-enter new password"
                required
                minlength="8"
              >
              <button type="button" class="toggle-password-btn" onclick="togglePassword(this)">
                <i class="mdi mdi-eye-off"></i>
              </button>
            </div>
          </div>
        </div>

        <p class="settings-form-hint" style="margin-top: -0.75rem;">
          <i class="mdi mdi-information-outline"></i> Password must be at least 8 characters long.
        </p>

        <div class="settings-form-actions">
          <button type="button" class="settings-btn-cancel" onclick="document.getElementById('passwordForm').reset(); resetPasswordStrength();">Cancel</button>
          <button type="submit" class="settings-btn-save">
            <i class="mdi mdi-lock-check"></i> Update Password
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Section toggle
    const navBtns = document.querySelectorAll('.settings-nav-btn');
    const sections = document.querySelectorAll('.settings-section');

    navBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');

            navBtns.forEach(function(b) { b.classList.remove('active'); });
            sections.forEach(function(s) { s.classList.remove('active'); });

            this.classList.add('active');
            document.getElementById('section-' + sectionId).classList.add('active');
        });
    });

    // If there are password errors, switch to security tab
    @if($errors->has('current_password') || $errors->has('password'))
        document.querySelector('[data-section="security"]').click();
    @endif
});

function togglePassword(btn) {
    var input = btn.parentElement.querySelector('input');
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'mdi mdi-eye';
    } else {
        input.type = 'password';
        icon.className = 'mdi mdi-eye-off';
    }
}

function checkPasswordStrength(password) {
    var strength = 0;
    var bars = [
        document.getElementById('str-bar-1'),
        document.getElementById('str-bar-2'),
        document.getElementById('str-bar-3'),
        document.getElementById('str-bar-4')
    ];
    var textEl = document.getElementById('str-text');

    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    var levelClass = strength <= 1 ? 'weak' : (strength <= 2 ? 'medium' : 'strong');
    var labels = { weak: 'Weak', medium: 'Fair', strong: 'Strong' };
    var colors = { weak: '#dc3545', medium: '#ffc107', strong: '#28a745' };

    bars.forEach(function(bar, i) {
        bar.className = 'password-strength-bar';
        if (i < strength) {
            bar.classList.add('active', levelClass);
        }
    });

    if (password.length > 0) {
        textEl.textContent = labels[levelClass];
        textEl.style.color = colors[levelClass];
    } else {
        textEl.textContent = '';
    }
}

function resetPasswordStrength() {
    var bars = document.querySelectorAll('.password-strength-bar');
    bars.forEach(function(bar) { bar.className = 'password-strength-bar'; });
    document.getElementById('str-text').textContent = '';
}
</script>
@endpush
