@extends('layouts.admin')

@section('title', 'Payment System')

@push('styles')
@vite(['resources/css/membership-payment.css', 'resources/css/product-payment.css'])
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

  <!-- ========================================== -->
  <!-- PAGE TOGGLE: Membership / PT / Product     -->
  <!-- ========================================== -->
  <div class="page-toggle-container">
    <button class="page-toggle-btn active" data-page="membership">
      <i class="mdi mdi-card-account-details-outline"></i>
      <span>Membership Payment</span>
    </button>
    <button class="page-toggle-btn" data-page="pt">
      <i class="mdi mdi-dumbbell"></i>
      <span>Personal Training Payment</span>
    </button>
    <button class="page-toggle-btn" data-page="product">
      <i class="mdi mdi-cart-outline"></i>
      <span>Product Payment</span>
    </button>
  </div>

  <!-- ========================================== -->
  <!-- SIBLING PAGES WRAPPER                      -->
  <!-- ========================================== -->
  <div class="pages-slider">

    <!-- ====== MEMBERSHIP PAGE (inline) ====== -->
    <div class="page-panel active" id="membershipPage">
      <div class="card">
        <div class="card-body">
            <div class="section-header">
                <h2 class="card-title">Process Membership Payment</h2>
            </div>

            <form id="membershipPaymentForm" action="{{ route('membership.payment.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Member Selection -->
            <div class="form-section" id="memberSelectionSection">
                <div class="form-group">
                <label class="form-label">Select Member</label>
                <div style="position: relative;">
                    <input type="text" class="form-control" id="memberSearch" name="member_search" placeholder="Search by name or contact..." autocomplete="off">
                    <div id="memberResults" class="autocomplete-results" style="display:none; z-index: 9999;"></div>
                    <input type="hidden" name="member_id" id="memberId">
                    <input type="hidden" id="memberStatus">
                    <input type="hidden" id="memberIsStudent" value="0">
                </div>
                </div>
            </div>

            <!-- New Member Details -->
            <div class="form-section" id="newMemberSection" style="display: none;">
                <div class="member-card">
                    <div class="member-card-header">
                        <div class="member-card-icon"><i class="mdi mdi-account"></i></div>
                        <h4 class="member-card-title">Member Details</h4>
                    </div>
                    <div class="member-card-body">
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="new_member_name" id="newMemberName" placeholder="Enter full name">
                            </div>
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Contact Number <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="new_member_contact" id="newMemberContact" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                            </div>
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Avatar <span style="color:#666; font-weight:400;">(Optional)</span></label>
                                <input type="file" class="form-control" name="new_member_avatar" id="newMemberAvatar" accept="image/*">
                            </div>
                        </div>
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="new_member_age" id="newMemberAge" placeholder="Enter" min="1" max="120">
                            </div>
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Sex</label>
                                <select class="form-select" name="new_member_sex" id="newMemberSex">
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="member-form-row member-student-row">
                            <div class="member-form-col" style="flex: 0 0 auto;">
                                <label class="form-label"><i class="mdi mdi-school"></i> Student?</label>
                                <div class="student-toggle">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="member1_is_student" id="member1IsStudent" value="1">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="toggle-label" id="member1StudentLabel">No</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gym Buddy Card -->
                <div id="buddyNewSection">
                    <div class="member-card buddy-card">
                        <div class="member-card-header buddy-header">
                            <div class="member-card-icon buddy-icon"><i class="mdi mdi-account-multiple"></i></div>
                            <h4 class="member-card-title">Member Details</h4>
                            <span class="buddy-tag">Buddy</span>
                        </div>
                        <div class="member-card-body">
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="buddy_name" id="buddyName" placeholder="Enter full name">
                                </div>
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Contact Number <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="buddy_contact" id="buddyContact" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                                </div>
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Avatar <span style="color:#666; font-weight:400;">(Optional)</span></label>
                                    <input type="file" class="form-control" name="buddy_avatar" id="buddyAvatar" accept="image/*">
                                </div>
                            </div>
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="buddy_age" id="buddyAge" placeholder="Enter" min="1" max="120">
                                </div>
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Sex</label>
                                    <select class="form-select" name="buddy_sex" id="buddySex">
                                        <option value="" disabled selected>Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="member-form-row member-student-row">
                                <div class="member-form-col" style="flex: 0 0 auto;">
                                    <label class="form-label"><i class="mdi mdi-school"></i> Student?</label>
                                    <div class="student-toggle">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="buddy_is_student" id="buddyIsStudent" value="1">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-label" id="buddyStudentLabel">No</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buddy Selection for Renewal/Extension -->
            <div class="form-section" id="buddySelectSection">
                <div class="form-group">
                <label class="form-label">Search Buddy Member</label>
                <div style="position: relative;">
                    <input type="text" class="form-control" id="buddyMemberSearch" placeholder="Search buddy by name or contact..." autocomplete="off">
                    <div id="buddyMemberResults" class="autocomplete-results" style="display:none; z-index: 9999;"></div>
                    <input type="hidden" name="buddy_member_id" id="buddyMemberId">
                </div>
                </div>
            </div>

            <!-- Payment Type Selector -->
            <div class="form-section">
                <label class="form-label">Payment Type</label>
                <div class="payment-type-selector">
                    <div class="payment-type-pill" data-type="new">
                        <span class="icon"><i class="mdi mdi-account-plus"></i></span>
                        <span class="label" style="font-size: 1.125rem;">New Membership</span>
                    </div>
                    <div class="payment-type-pill active" data-type="renewal">
                        <span class="icon"><i class="mdi mdi-autorenew"></i></span>
                        <span class="label" style="font-size: 1.125rem;">Renewal</span>
                    </div>
                    <div class="payment-type-pill" data-type="extension" id="extensionPill">
                        <span class="icon"><i class="mdi mdi-calendar-plus"></i></span>
                        <span class="label" style="font-size: 1.125rem;">Extension</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="payment_type" id="paymentType" value="renewal">

            <!-- Subscription Type Selector -->
            <div class="form-section">
                <label class="form-label">Plan Selection</label>
                <div class="plan-type-selector">
                @foreach($membershipPlans as $plan)
                <div class="plan-type-card{{ $plan->plan_key === 'Regular' ? ' active' : '' }}"
                     data-plan="{{ $plan->plan_key }}"
                     data-price="{{ $plan->price }}"
                     data-duration="{{ $plan->duration_days }}"
                     data-requires-student="{{ $plan->requires_student ? 'true' : 'false' }}"
                     data-requires-buddy="{{ $plan->requires_buddy ? 'true' : 'false' }}">
                    <div class="plan-name">
                        @if($plan->requires_student)<i class="mdi mdi-school"></i> @endif
                        @if($plan->requires_buddy)<i class="mdi mdi-account-multiple"></i> @endif
                        {{ $plan->plan_name }}
                    </div>
                    <div class="plan-duration">{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'Day' : 'Days' }} Access{{ $plan->requires_buddy ? ' · ' . $plan->buddy_count . ' Persons' : '' }}</div>
                    <div class="plan-price">
                        @if($plan->requires_buddy && $plan->buddy_count > 1)
                            ₱{{ number_format($plan->per_person_price, 2) }} <small>/person</small>
                        @else
                            ₱{{ number_format($plan->price, 2) }}
                        @endif
                    </div>
                    @if($plan->badge_text)
                        <div class="plan-badge {{ $plan->requires_buddy ? 'buddy' : ($plan->badge_color === 'success' ? 'promo' : '') }}">{{ $plan->badge_text }}</div>
                    @endif
                </div>
                @endforeach
                </div>
                <input type="hidden" name="plan_type" id="planType" value="{{ $membershipPlans->firstWhere('plan_key', 'Regular')?->plan_key ?? $membershipPlans->first()?->plan_key ?? 'Regular' }}">
            </div>

            <!-- Student Warning -->
            <div class="form-section" id="studentWarning" style="display: none;">
                <div style="background: #f8d7da; border: 1px solid #dc3545; color: #721c24; padding: 1rem; border-radius: 6px;">
                    <i class="mdi mdi-alert-circle"></i>
                    <strong>Not Eligible:</strong> This member is not registered as a student. Student rate is only available for student members.
                </div>
            </div>

            <!-- Payment Details -->
            <div class="form-section">
                <div class="payment-details-card">
                    <div class="payment-details-header"><h4 class="payment-details-title">Membership Duration</h4></div>
                    <div class="payment-details-body">
                        <div class="payment-details-row">
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Current Due Date</label>
                                <input type="text" class="form-control" id="currentDueDate" readonly placeholder="N/A">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">New Due Date</label>
                                <input type="text" class="form-control" name="new_due_date" id="newDueDate" readonly placeholder="Will be calculated">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Additional Days</label>
                                <input type="number" class="form-control" id="additionalDays" readonly placeholder="0" value="30">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buddy Due Date Fields -->
                <div id="buddyDueDateSection" class="payment-details-card buddy-dates-card" style="display: none;">
                    <div class="payment-details-body">
                        <div class="payment-details-row">
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Buddy's Current Due Date</label>
                                <input type="text" class="form-control" id="buddyCurrentDueDate" readonly placeholder="N/A">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Buddy's New Due Date</label>
                                <input type="text" class="form-control" name="buddy_new_due_date" id="buddyNewDueDate" readonly placeholder="Will be calculated">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Additional Days</label>
                                <input type="number" class="form-control" readonly placeholder="0" value="30">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="payment-details-card">
                    <div class="payment-details-body">
                        <div class="payment-details-row">
                            <div class="payment-details-col payment-details-col-2">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="paymentMethod" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="GCash">GCash</option>
                                    <option value="PayMaya">PayMaya</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="payment-details-col payment-details-col-2">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" name="amount" id="amount" placeholder="₱0.00" step="0.01" value="{{ $membershipPlans->firstWhere('plan_key', 'Regular')?->price ?? $membershipPlans->first()?->price ?? '0.00' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="clearFormBtn">
                    <i class="mdi mdi-close"></i> Clear
                </button>
                <button type="submit" class="btn btn-primary" id="submitPaymentBtn">
                    <i class="mdi mdi-check"></i> Process Payment
                </button>
            </div>
            </form>
        </div>
      </div>
    </div><!-- /membershipPage -->

    <!-- ====== PERSONAL TRAINING PAGE ====== -->
    @include('PaymentAndBillings.PersonalTraining')

    <!-- ====== PRODUCT PAYMENT PAGE ====== -->
    @include('PaymentAndBillings.PaymentAndBilling')

  </div><!-- /pages-slider -->

  <!-- Membership Confirmation Modal -->
  <div id="confirmationModal" class="modal-overlay">
    <div class="modal-content small">
      <div class="modal-header">
        <h3 class="modal-title">Confirm Payment</h3>
        <button class="modal-close" onclick="closeConfirmationModal()">&times;</button>
      </div>
      <div class="modal-body" style="font-size: 1.125rem;">
        <div class="confirmation-icon warning"><i class="mdi mdi-alert-circle-outline"></i></div>
        <p class="confirmation-message">Please review the payment details before proceeding.</p>
        <div class="confirmation-details" id="confirmationDetails"></div>
      </div>
      <div class="modal-footer" style="font-size: 1.125rem;">
        <button type="button" class="btn btn-secondary" onclick="closeConfirmationModal()"><i class="mdi mdi-close"></i> Cancel</button>
        <button type="button" class="btn btn-primary" onclick="confirmPayment()"><i class="mdi mdi-check"></i> Confirm & Process</button>
      </div>
    </div>
  </div>

  <!-- Membership Receipt Modal -->
  <div id="receiptModal" class="modal-overlay" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Receipt Details</h3>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body" id="receiptBody">
        <div class="loading-spinner"><div class="spinner">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
        <button type="button" class="btn btn-primary" onclick="printReceipt()"><i class="mdi mdi-printer"></i> Print</button>
      </div>
    </div>
  </div>

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
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ========================================
  // PAGE TOGGLE (Membership / PT / Product)
  // ========================================
  const pageToggleBtns = document.querySelectorAll('.page-toggle-btn');
  const pageMap = { 'membership': 'membershipPage', 'pt': 'ptPage', 'product': 'productPage' };
  const pageOrder = ['membership', 'pt', 'product'];

  pageToggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetPage = this.dataset.page;
      const targetPanelId = pageMap[targetPage];
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const currentActive = document.querySelector('.page-panel.active');
      const targetPanel = document.getElementById(targetPanelId);
      if (currentActive === targetPanel) return;
      const currentPageKey = Object.keys(pageMap).find(k => pageMap[k] === currentActive.id);
      const currentIndex = pageOrder.indexOf(currentPageKey);
      const targetIndex = pageOrder.indexOf(targetPage);
      const goingRight = targetIndex > currentIndex;
      currentActive.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');
      targetPanel.classList.add(goingRight ? 'slide-in-right' : 'slide-in-left');
      targetPanel.classList.add('active');
      setTimeout(() => {
        currentActive.classList.remove('active', 'slide-out-left', 'slide-out-right');
        targetPanel.classList.remove('slide-in-right', 'slide-in-left');
      }, 400);
    });
  });

  // Auto-switch to tab from URL query parameter
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get('tab');
  if (tabParam && pageMap[tabParam]) {
    const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${tabParam}"]`);
    if (targetBtn) {
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      targetBtn.classList.add('active');
      document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
      document.getElementById(pageMap[tabParam]).classList.add('active');
    }
  }

  // ========================================
  // MEMBERSHIP PAYMENT LOGIC
  // ========================================
  let selectedMemberStatus = '';
  let selectedMemberDueDate = '';
  let selectedMemberIsStudent = false;
  let selectedBuddyDueDate = '';
  let isMembershipSubmitting = false;

  const paymentTypePills = document.querySelectorAll('.payment-type-pill');
  const paymentTypeInput = document.getElementById('paymentType');
  const memberSelectionSection = document.getElementById('memberSelectionSection');
  const newMemberSection = document.getElementById('newMemberSection');
  const memberSearch = document.getElementById('memberSearch');
  const memberId = document.getElementById('memberId');
  const extensionPill = document.getElementById('extensionPill');

  paymentTypePills.forEach(pill => {
    pill.addEventListener('click', function() {
      const type = this.dataset.type;
      if (type === 'extension' && !memberId.value) return;
      if (type === 'renewal' && selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
        ToastUtils.showWarning('Member is active. Please use Extension instead.');
        return;
      }
      paymentTypePills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      paymentTypeInput.value = type;
      if (type === 'new') {
        memberSelectionSection.style.display = 'none';
        newMemberSection.style.display = 'block';
        memberSearch.removeAttribute('required');
        memberId.removeAttribute('required');
        document.getElementById('newMemberName').setAttribute('required', 'required');
        document.getElementById('newMemberContact').setAttribute('required', 'required');
      } else {
        memberSelectionSection.style.display = 'block';
        newMemberSection.style.display = 'none';
        memberSearch.setAttribute('required', 'required');
        document.getElementById('newMemberName').removeAttribute('required');
        document.getElementById('newMemberContact').removeAttribute('required');
      }
      document.getElementById('currentDueDate').value = '';
      document.getElementById('newDueDate').value = '';
      updatePlanDependentFields();
      enforcePlanRestrictions();
      calculateNewDueDate();
    });
  });

  // Student Toggles
  document.getElementById('member1IsStudent').addEventListener('change', function() {
    document.getElementById('member1StudentLabel').textContent = this.checked ? 'Yes' : 'No';
    if (paymentTypeInput.value === 'new') enforcePlanRestrictions();
  });
  document.getElementById('buddyIsStudent').addEventListener('change', function() {
    document.getElementById('buddyStudentLabel').textContent = this.checked ? 'Yes' : 'No';
  });

  // Subscription Type Selection
  const planTypeCards = document.querySelectorAll('.plan-type-card');
  const planTypeInput = document.getElementById('planType');
  const amountInput = document.getElementById('amount');
  const additionalDaysInput = document.getElementById('additionalDays');

  planTypeCards.forEach(card => {
    card.addEventListener('click', function() {
      const planType = this.dataset.plan;
      const requiresStudent = this.dataset.requiresStudent === 'true';
      if (requiresStudent) {
        const isStudent = paymentTypeInput.value === 'new' ? document.getElementById('member1IsStudent').checked : selectedMemberIsStudent;
        if (!isStudent) {
          document.getElementById('studentWarning').style.display = 'block';
          setTimeout(() => { document.getElementById('studentWarning').style.display = 'none'; }, 4000);
          ToastUtils.showWarning('Student rate is only available for student members.');
          return;
        }
      }
      if (planType === 'Regular') {
        const isStudent = paymentTypeInput.value === 'new' ? document.getElementById('member1IsStudent').checked : selectedMemberIsStudent;
        if (isStudent) { ToastUtils.showWarning('Regular rate is not available for student members.'); return; }
      }
      planTypeCards.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      planTypeInput.value = planType;
      amountInput.value = parseFloat(this.dataset.price).toFixed(2);
      additionalDaysInput.value = this.dataset.duration;
      updatePlanDependentFields();
      calculateNewDueDate();
    });
  });

  /**
   * Show/hide fields that depend on the selected subscription type
   */
  function updatePlanDependentFields() {
    const currentPlan = planTypeInput.value;
    const currentPaymentType = paymentTypeInput.value;
    const requiresBuddy = currentPlan === 'GymBuddy';
    const buddyNewSection = document.getElementById('buddyNewSection');
    if (requiresBuddy && currentPaymentType === 'new') {
      buddyNewSection.classList.add('buddy-visible');
      document.getElementById('buddyName').setAttribute('required', 'required');
      document.getElementById('buddyContact').setAttribute('required', 'required');
    } else {
      buddyNewSection.classList.remove('buddy-visible');
      document.getElementById('buddyName').removeAttribute('required');
      document.getElementById('buddyContact').removeAttribute('required');
    }
    const buddySelectSection = document.getElementById('buddySelectSection');
    const buddyDueDateSection = document.getElementById('buddyDueDateSection');
    if (requiresBuddy && currentPaymentType !== 'new') {
      buddySelectSection.classList.add('buddy-visible');
      buddyDueDateSection.style.display = 'block';
    } else {
      buddySelectSection.classList.remove('buddy-visible');
      buddyDueDateSection.style.display = 'none';
      document.getElementById('buddyMemberId').value = '';
      document.getElementById('buddyMemberSearch').value = '';
      document.getElementById('buddyCurrentDueDate').value = '';
      document.getElementById('buddyNewDueDate').value = '';
      selectedBuddyDueDate = '';
    }
    document.getElementById('studentWarning').style.display = 'none';
  }

  function enforcePlanRestrictions() {
    const isStudent = paymentTypeInput.value === 'new' ? document.getElementById('member1IsStudent').checked : selectedMemberIsStudent;
    const regularCard = document.querySelector('#membershipPage [data-plan="Regular"]');
    const studentCard = document.querySelector('#membershipPage [data-plan="Student"]');
    if (isStudent) {
      regularCard.style.opacity = '0.4'; regularCard.style.pointerEvents = 'none';
      studentCard.style.opacity = '1'; studentCard.style.pointerEvents = 'auto';
      if (planTypeInput.value === 'Regular') {
        planTypeCards.forEach(c => c.classList.remove('active'));
        studentCard.classList.add('active');
        planTypeInput.value = 'Student'; amountInput.value = '500.00'; additionalDaysInput.value = '30';
        calculateNewDueDate();
        ToastUtils.showInfo('Switched to Student Rate for student member.');
      }
    } else {
      studentCard.style.opacity = '0.4'; studentCard.style.pointerEvents = 'none';
      regularCard.style.opacity = '1'; regularCard.style.pointerEvents = 'auto';
      if (planTypeInput.value === 'Student') {
        planTypeCards.forEach(c => c.classList.remove('active'));
        regularCard.classList.add('active');
        planTypeInput.value = 'Regular'; amountInput.value = '600.00'; additionalDaysInput.value = '30';
        calculateNewDueDate();
        ToastUtils.showInfo('Switched to Regular Rate.');
      }
    }
  }

  // Member Autocomplete
  let memberSearchTimeout;
  memberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('memberResults');
    clearTimeout(memberSearchTimeout);
    if (query.length < 2) { resultsContainer.style.display = 'none'; return; }
    memberSearchTimeout = setTimeout(() => {
      fetch('{{ url("/api/members/search") }}?q=' + encodeURIComponent(query), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.length === 0) {
          resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>';
          resultsContainer.style.display = 'block';
          return;
        }
        resultsContainer.innerHTML = data.map(member => `
          <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-plan="${member.plan_type}" data-status="${member.status}" data-is-student="${member.is_student ? '1' : '0'}">
            <strong>${member.name}</strong>
            ${member.is_student ? '<span class="badge badge-info" style="margin-left: 0.5rem; font-size: 0.7rem;">STUDENT</span>' : ''}
            <div style="font-size: 0.875rem; color: #999;">
              Contact: ${member.contact || 'N/A'} | Plan: ${member.plan_type} | Status: ${member.status}
              ${member.due_date ? '| Due: ' + new Date(member.due_date).toLocaleDateString() : '| No due date'}
            </div>
          </div>
        `).join('');
        resultsContainer.style.display = 'block';
        resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
          item.addEventListener('click', function() {
            memberSearch.value = this.dataset.name;
            memberId.value = this.dataset.id;
            selectedMemberStatus = this.dataset.status;
            selectedMemberDueDate = this.dataset.dueDate;
            selectedMemberIsStudent = this.dataset.isStudent === '1';
            document.getElementById('memberIsStudent').value = selectedMemberIsStudent ? '1' : '0';
            const dueDate = this.dataset.dueDate;
            document.getElementById('currentDueDate').value = dueDate ? new Date(dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No due date';
            extensionPill.style.opacity = '1'; extensionPill.style.pointerEvents = 'auto';
            const renewalPill = document.querySelector('[data-type="renewal"]');
            if (selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
              renewalPill.style.opacity = '0.5'; renewalPill.style.pointerEvents = 'none';
              paymentTypePills.forEach(p => p.classList.remove('active'));
              extensionPill.classList.add('active'); paymentTypeInput.value = 'extension';
            } else { renewalPill.style.opacity = '1'; renewalPill.style.pointerEvents = 'auto'; }
            enforcePlanRestrictions();
            resultsContainer.style.display = 'none';
            updatePlanDependentFields();
            calculateNewDueDate();
          });
        });
      })
      .catch(error => { console.error('Error fetching members:', error); ToastUtils.showError('Error searching for members'); });
    }, 300);
  });

  // Buddy Autocomplete
  let buddySearchTimeout;
  const buddyMemberSearch = document.getElementById('buddyMemberSearch');
  buddyMemberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('buddyMemberResults');
    clearTimeout(buddySearchTimeout);
    if (query.length < 2) { resultsContainer.style.display = 'none'; return; }
    buddySearchTimeout = setTimeout(() => {
      fetch('{{ url("/api/members/search") }}?q=' + encodeURIComponent(query), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        const filtered = data.filter(m => String(m.id) !== memberId.value);
        if (filtered.length === 0) { resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>'; resultsContainer.style.display = 'block'; return; }
        resultsContainer.innerHTML = filtered.map(member => `
          <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-status="${member.status}">
            <strong>${member.name}</strong>
            <div style="font-size: 0.875rem; color: #999;">Contact: ${member.contact || 'N/A'} | Status: ${member.status} ${member.due_date ? '| Due: ' + new Date(member.due_date).toLocaleDateString() : '| No due date'}</div>
          </div>
        `).join('');
        resultsContainer.style.display = 'block';
        resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
          item.addEventListener('click', function() {
            buddyMemberSearch.value = this.dataset.name;
            document.getElementById('buddyMemberId').value = this.dataset.id;
            selectedBuddyDueDate = this.dataset.dueDate;
            document.getElementById('buddyCurrentDueDate').value = this.dataset.dueDate ? new Date(this.dataset.dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No due date';
            calculateNewDueDate();
            resultsContainer.style.display = 'none';
          });
        });
      });
    }, 300);
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#memberResults')) document.getElementById('memberResults').style.display = 'none';
    if (!e.target.closest('#buddyMemberSearch') && !e.target.closest('#buddyMemberResults')) document.getElementById('buddyMemberResults').style.display = 'none';
  });

  // Contact validation
  function validateContactInput(e) {
    let value = e.target.value.replace(/[^0-9+]/g, '');
    if (value.startsWith('+63')) { if (value.length > 13) value = value.substring(0, 13); }
    else if (value.startsWith('09')) { if (value.length > 11) value = value.substring(0, 11); }
    e.target.value = value;
  }
  document.getElementById('newMemberContact').addEventListener('input', validateContactInput);
  document.getElementById('buddyContact').addEventListener('input', validateContactInput);

  function calculateNewDueDate() {
    const paymentType = paymentTypeInput.value;
    const duration = parseInt(additionalDaysInput.value) || 0;
    const currentDueDateText = document.getElementById('currentDueDate').value;
    if (duration === 0) { document.getElementById('newDueDate').value = ''; document.getElementById('buddyNewDueDate').value = ''; return; }
    let startDate; const today = new Date();
    if (paymentType === 'new') { startDate = today; }
    else if (paymentType === 'renewal') {
      if (currentDueDateText && currentDueDateText !== 'No due date') { const cd = new Date(currentDueDateText); startDate = cd > today ? cd : today; } else { startDate = today; }
    } else if (paymentType === 'extension') {
      if (currentDueDateText && currentDueDateText !== 'No due date') { startDate = new Date(currentDueDateText); } else { return; }
    }
    const newDueDate = new Date(startDate); newDueDate.setDate(newDueDate.getDate() + duration);
    document.getElementById('newDueDate').value = newDueDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    if (planTypeInput.value === 'GymBuddy' && paymentType !== 'new') {
      const bcdText = document.getElementById('buddyCurrentDueDate').value;
      let buddyStart;
      if (paymentType === 'renewal') {
        if (bcdText && bcdText !== 'No due date' && bcdText !== 'N/A') { const bc = new Date(bcdText); buddyStart = bc > today ? bc : today; } else { buddyStart = today; }
      } else if (paymentType === 'extension') {
        if (bcdText && bcdText !== 'No due date' && bcdText !== 'N/A') { buddyStart = new Date(bcdText); } else { document.getElementById('buddyNewDueDate').value = ''; return; }
      }
      if (buddyStart) { const bnd = new Date(buddyStart); bnd.setDate(bnd.getDate() + duration); document.getElementById('buddyNewDueDate').value = bnd.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }); }
    } else { document.getElementById('buddyNewDueDate').value = ''; }
  }

  // Form Submission
  const paymentForm = document.getElementById('membershipPaymentForm');
  paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (isMembershipSubmitting) return;
    if (paymentTypeInput.value === 'new') {
      if (!document.getElementById('newMemberName').value.trim()) { ToastUtils.showError("Please enter the member's name."); return; }
      const contact = document.getElementById('newMemberContact').value;
      if (contact && !contact.match(/^(09\d{9}|\+639\d{9})$/)) { ToastUtils.showError('Invalid contact number.'); return; }
      if (planTypeInput.value === 'GymBuddy') {
        if (!document.getElementById('buddyName').value.trim()) { ToastUtils.showError("Please enter the buddy's name."); return; }
        const bc = document.getElementById('buddyContact').value;
        if (!bc || !bc.match(/^(09\d{9}|\+639\d{9})$/)) { ToastUtils.showError('Invalid buddy contact number.'); return; }
      }
    }
    if (paymentTypeInput.value !== 'new') {
      if (!memberId.value) { ToastUtils.showError('Please select a member.'); return; }
      if (planTypeInput.value === 'GymBuddy' && !document.getElementById('buddyMemberId').value) { ToastUtils.showError('Please select a gym buddy member.'); return; }
    }
    showConfirmationModal();
  });

  function showConfirmationModal() {
    const pt = paymentTypeInput.value, pl = planTypeInput.value, amt = amountInput.value;
    const pm = document.getElementById('paymentMethod').value;
    const mn = pt === 'new' ? document.getElementById('newMemberName').value : memberSearch.value;
    const nd = document.getElementById('newDueDate').value;
    let buddyInfo = '';
    if (pl === 'GymBuddy') {
      const bn = pt === 'new' ? document.getElementById('buddyName').value : document.getElementById('buddyMemberSearch').value;
      const bcd = document.getElementById('buddyCurrentDueDate').value, bnd = document.getElementById('buddyNewDueDate').value;
      buddyInfo = `<div class="confirmation-detail-row"><span class="confirmation-detail-label">Gym Buddy:</span><span class="confirmation-detail-value">${bn}</span></div>
        ${pt !== 'new' && bcd ? `<div class="confirmation-detail-row"><span class="confirmation-detail-label">Buddy Current Due:</span><span class="confirmation-detail-value">${bcd}</span></div>
        <div class="confirmation-detail-row"><span class="confirmation-detail-label">Buddy New Due:</span><span class="confirmation-detail-value" style="color: #28a745;">${bnd}</span></div>` : ''}`;
    }
    const planLabels = { 'Regular': 'Regular Gym Rate', 'Student': 'Student Rate', 'GymBuddy': 'Gym Buddy Rate (2 persons)', 'ThreeMonths': '3 Months Membership', 'Session': 'Session Pass' };
    document.getElementById('confirmationDetails').innerHTML = `
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Member:</span><span class="confirmation-detail-value">${mn}</span></div>
      ${buddyInfo}
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Payment Type:</span><span class="confirmation-detail-value">${pt.toUpperCase()}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Plan:</span><span class="confirmation-detail-value">${planLabels[pl] || pl}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Amount:</span><span class="confirmation-detail-value">₱${parseFloat(amt).toFixed(2)}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Payment Method:</span><span class="confirmation-detail-value">${pm}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">New Due Date:</span><span class="confirmation-detail-value" style="color: #28a745;">${nd}</span></div>`;
    document.getElementById('confirmationModal').classList.add('show');
  }

  window.closeConfirmationModal = function() { document.getElementById('confirmationModal').classList.remove('show'); };

  window.confirmPayment = function() {
    if (isMembershipSubmitting) return;
    isMembershipSubmitting = true;
    closeConfirmationModal();
    const form = document.getElementById('membershipPaymentForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...'; }
    const formData = new FormData(form);
    fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ToastUtils.showSuccess(data.message || 'Payment processed successfully!');
        form.reset();
        window._reloadAfterReceipt = true;
        setTimeout(() => { viewReceipt(data.payment.id); }, 300);
      } else {
        ToastUtils.showError(data.message || 'Payment failed.');
        isMembershipSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process Payment'; }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An error occurred.');
      isMembershipSubmitting = false;
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process Payment'; }
    });
  };

  // Clear Form
  document.getElementById('clearFormBtn').addEventListener('click', function() {
    paymentForm.reset(); memberId.value = '';
    selectedMemberStatus = ''; selectedMemberDueDate = ''; selectedMemberIsStudent = false;
    document.getElementById('memberIsStudent').value = '0';
    document.getElementById('currentDueDate').value = ''; document.getElementById('newDueDate').value = '';
    document.getElementById('buddyMemberId').value = ''; document.getElementById('buddyMemberSearch').value = '';
    document.getElementById('buddyCurrentDueDate').value = ''; document.getElementById('buddyNewDueDate').value = '';
    selectedBuddyDueDate = '';
    document.getElementById('buddyDueDateSection').style.display = 'none';
    document.getElementById('member1IsStudent').checked = false; document.getElementById('member1StudentLabel').textContent = 'No';
    document.getElementById('buddyIsStudent').checked = false; document.getElementById('buddyStudentLabel').textContent = 'No';
    planTypeCards.forEach(c => c.classList.remove('active'));
    document.querySelector('#membershipPage [data-plan="Regular"]').classList.add('active');
    planTypeInput.value = 'Regular'; amountInput.value = '600.00'; additionalDaysInput.value = '30';
    extensionPill.style.opacity = '0.5'; extensionPill.style.pointerEvents = 'none';
    document.querySelector('[data-type="renewal"]').style.opacity = '1'; document.querySelector('[data-type="renewal"]').style.pointerEvents = 'auto';
    isMembershipSubmitting = false;
    const submitBtn = document.getElementById('submitPaymentBtn');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process Payment'; }
    updatePlanDependentFields(); enforcePlanRestrictions();
  });

  enforcePlanRestrictions();
}); // DOMContentLoaded

// ========================================
// MEMBERSHIP RECEIPT FUNCTIONS
// ========================================
function viewReceipt(transactionId) {
  const modal = document.getElementById('receiptModal');
  const receiptBody = document.getElementById('receiptBody');
  modal.classList.add('show');
  receiptBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
  fetch('/membership-payment/' + transactionId + '/receipt')
    .then(response => response.json())
    .then(data => { receiptBody.innerHTML = generateReceiptHTML(data); })
    .catch(error => { console.error('Error:', error); ToastUtils.showError('Failed to load receipt.'); receiptBody.innerHTML = '<div style="padding:2rem;color:#dc3545;text-align:center;"><i class="mdi mdi-alert-circle" style="font-size:3rem;"></i><p>Failed to load receipt.</p></div>'; });
}

function generateReceiptHTML(data) {
  const planLabels = { 'Regular': 'Regular Gym Rate', 'Student': 'Student Rate', 'GymBuddy': 'Gym Buddy Rate', 'ThreeMonths': '3 Months Membership', 'Session': 'Session Pass', 'Monthly': 'Monthly Plan' };
  return '<div class="receipt-container"><div class="receipt-header"><h2>MEMBERSHIP PAYMENT RECEIPT</h2><p><strong>Abstrack Fitness Gym</strong></p><p>Toril, Davao Del Sur</p></div>' +
    '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px;">' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong><span style="display: block; font-weight: 600;">#' + data.receipt_number + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong><span style="display: block; font-weight: 600;">' + (data.formatted_date || new Date(data.created_at).toLocaleString()) + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Member Name</strong><span style="display: block; font-weight: 600;">' + (data.member_name || 'N/A') + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Contact</strong><span style="display: block; font-weight: 600;">' + (data.member_contact || data.contact || '') + '</span></div>' +
    (data.buddy_name ? '<div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;"><strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Gym Buddy</strong><span style="display: block; font-weight: 600;">' + data.buddy_name + '</span></div><div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;"><strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Buddy Contact</strong><span style="display: block; font-weight: 600;">' + (data.buddy_contact || 'N/A') + '</span></div>' : '') +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Type</strong><span style="display: block; font-weight: 600;">' + (data.payment_type || '').toUpperCase() + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong><span style="display: block; font-weight: 600;">' + (data.payment_method || 'N/A') + '</span></div></div>' +
    '<table class="receipt-table"><thead><tr><th>Description</th><th style="text-align: right;">Amount</th></tr></thead><tbody>' +
    (data.plan_type === 'GymBuddy' ?
      '<tr><td><strong>Gym Buddy Rate</strong><br><small style="color: #666;">Duration: ' + (data.duration || 'N/A') + ' days | 2 Persons</small><br><small style="color: #0d6efd;">Member 1: ' + data.member_name + '</small><br><small style="color: #0d6efd;">Member 2: ' + (data.buddy_name || 'N/A') + '</small></td><td style="text-align: right;"><span style="display: block;">₱' + parseFloat(data.amount || 0).toFixed(2) + '/person</span><strong style="display: block; margin-top: 4px;">Total: ₱' + (parseFloat(data.amount || 0) * 2).toFixed(2) + '</strong></td></tr>' :
      '<tr><td><strong>' + (planLabels[data.plan_type] || data.plan_type || 'Membership') + ' Plan</strong><br><small style="color: #666;">Duration: ' + (data.duration || 'N/A') + ' days</small></td><td style="text-align: right;">₱' + parseFloat(data.amount || 0).toFixed(2) + '</td></tr>') +
    '</tbody></table>' +
    '<div class="receipt-total"><div class="receipt-row" style="font-size: 1.3rem;"><span><strong>Total Paid:</strong></span><span><strong>₱' + (data.plan_type === 'GymBuddy' ? (parseFloat(data.amount || 0) * 2).toFixed(2) : parseFloat(data.amount || 0).toFixed(2)) + '</strong></span></div></div>' +
    '<div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ccc;"><div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Previous Due Date</strong><span style="display: block; font-weight: 600;">' + (data.previous_due_date || 'N/A') + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">New Due Date</strong><span style="display: block; font-weight: 600; color: #28a745;">' + (data.new_due_date || 'N/A') + '</span></div></div></div>' +
    (data.notes ? '<div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;"><strong style="display: block; margin-bottom: 8px; color: #666;">Notes:</strong><p style="margin: 0; color: #333;">' + data.notes + '</p></div>' : '') +
    '<div class="receipt-footer"><p><strong>Thank you for your membership!</strong></p><p style="font-size: 0.875rem;">Please keep this receipt for your records.</p></div></div>';
}

function closeModal() {
  document.getElementById('receiptModal').classList.remove('show');
  if (window._reloadAfterReceipt) { window._reloadAfterReceipt = false; window.location.reload(); }
}

function printReceipt() {
  const content = document.getElementById('receiptBody').innerHTML;
  const pw = window.open('', '_blank');
  pw.document.write('<!DOCTYPE html><html><head><title>Receipt</title><style>body{font-family:"Courier New",monospace}.receipt-container{max-width:600px;margin:0 auto;padding:20px}.receipt-header{text-align:center;margin-bottom:30px;padding-bottom:20px;border-bottom:2px dashed #333}.receipt-table{width:100%;border-collapse:collapse;margin:20px 0}.receipt-table th{background:#333;color:#fff;padding:10px;text-align:left}.receipt-table td{padding:10px;border-bottom:1px solid #ddd}.receipt-row{display:flex;justify-content:space-between;margin-bottom:8px}.receipt-total{margin-top:20px;padding-top:20px;border-top:2px solid #333}.receipt-footer{margin-top:30px;padding-top:20px;border-top:2px dashed #333;text-align:center}</style></head><body>' + content + '</body></html>');
  pw.document.close(); pw.print();
}

// Global modal handlers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (typeof closeModal === 'function') closeModal();
    if (typeof closeConfirmationModal === 'function') closeConfirmationModal();
    if (typeof closeProductConfirmation === 'function') closeProductConfirmation();
    if (typeof closeProductReceiptModal === 'function') closeProductReceiptModal();
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