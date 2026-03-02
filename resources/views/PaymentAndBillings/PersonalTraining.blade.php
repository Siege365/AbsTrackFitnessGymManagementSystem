<!-- ====== PERSONAL TRAINING PAGE ====== -->
<div class="page-panel" id="ptPage">
  <div class="card">
    <div class="card-body">
      <div class="section-header">
        <h2 class="card-title">Process Personal Training Payment</h2>
      </div>

      <form id="ptPaymentForm" action="{{ route('pt.payment.store') }}" method="POST" novalidate>
      @csrf

      <!-- Member Selection (search all gym members, active or expired) -->
      <div class="form-section" id="ptMemberSelectionSection">
        <div class="form-group">
          <label class="form-label">Select Member</label>
          <div style="position: relative;">
            <input type="text" class="form-control" id="ptMemberSearch" name="pt_member_search" placeholder="Search by name or contact..." autocomplete="off">
            <div id="ptMemberResults" class="autocomplete-results" style="display:none; z-index: 9999;"></div>
            <input type="hidden" name="member_id" id="ptMemberId">
            <input type="hidden" id="ptClientId">
            <input type="hidden" id="ptMemberStatus">
          </div>
        </div>
      </div>

      <!-- Member Info Card (shown after selection) -->
      <div class="form-section" id="ptMemberInfoSection" style="display: none;">
        <div class="member-card">
          <div class="member-card-header">
            <div class="member-card-icon"><i class="mdi mdi-account"></i></div>
            <h4 class="member-card-title">Selected Member</h4>
            <span id="ptMemberBadge" class="badge badge-success" style="margin-left: auto; font-size: 0.8rem;">Active Membership</span>
          </div>
          <div class="member-card-body">
            <div class="member-form-row" style="gap: 1rem;">
              <div class="member-form-col member-form-col-3">
                <label class="form-label" style="opacity: 0.7;">Name</label>
                <p id="ptSelectedName" style="margin: 0; font-weight: 600; color: #fff;">—</p>
              </div>
              <div class="member-form-col member-form-col-3">
                <label class="form-label" style="opacity: 0.7;">Contact</label>
                <p id="ptSelectedContact" style="margin: 0; font-weight: 600; color: #fff;">—</p>
              </div>
              <div class="member-form-col member-form-col-3">
                <label class="form-label" style="opacity: 0.7;">PT Status</label>
                <p id="ptSelectedStatus" style="margin: 0; font-weight: 600; color: #fff;">—</p>
              </div>
            </div>
            <div class="member-form-row" style="gap: 1rem; margin-top: 0.75rem;">
              <div class="member-form-col member-form-col-3">
                <label class="form-label" style="opacity: 0.7;">Current PT Plan</label>
                <p id="ptSelectedPlan" style="margin: 0; font-weight: 600; color: #fff;">—</p>
              </div>
              <div class="member-form-col member-form-col-3">
                <label class="form-label" style="opacity: 0.7;">PT Plan Due Date</label>
                <p id="ptSelectedDueDate" style="margin: 0; font-weight: 600; color: #fff;">—</p>
              </div>
              <div class="member-form-col member-form-col-3">
                <button type="button" class="btn btn-sm btn-secondary" id="ptClearMemberBtn" style="margin-top: 0.5rem;">
                  <i class="mdi mdi-close"></i> Change Member
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Type Selector (New / Renew / Extend) -->
      <div class="form-section">
        <label class="form-label">Payment Type</label>
        <div class="payment-type-selector">
          <div class="payment-type-pill" data-type="new" id="ptNewPill">
            <span class="icon"><i class="mdi mdi-account-plus"></i></span>
            <span class="label" style="font-size: 1.125rem;">New PT Client</span>
          </div>
          <div class="payment-type-pill active" data-type="renewal" id="ptRenewalPill">
            <span class="icon"><i class="mdi mdi-autorenew"></i></span>
            <span class="label" style="font-size: 1.125rem;">Renewal</span>
          </div>
          <div class="payment-type-pill" data-type="extension" id="ptExtensionPill">
            <span class="icon"><i class="mdi mdi-calendar-plus"></i></span>
            <span class="label" style="font-size: 1.125rem;">Extension</span>
          </div>
        </div>
      </div>

      <input type="hidden" name="payment_type" id="ptPaymentType" value="renewal">

      <!-- PT Plan Selection -->
      <div class="form-section">
        <label class="form-label">PT Plan Selection</label>
        @if($ptPlans->count() > 0)
        <div class="plan-type-selector" id="ptPlanSelector">
          @foreach($ptPlans as $plan)
          <div class="plan-type-card{{ $loop->first ? ' active' : '' }}"
              data-plan="{{ $plan->plan_key }}"
              data-price="{{ $plan->price }}"
              data-duration="{{ $plan->duration_days }}">
            <div class="plan-name">{{ $plan->plan_name }}</div>
            <div class="plan-duration">{{ $plan->duration_label ?? ($plan->duration_days . ' ' . ($plan->duration_days === 1 ? 'Day' : 'Days')) }}</div>
            <div class="plan-price">₱{{ number_format($plan->price, 2) }}</div>
            @if($plan->badge_text)
              <div class="plan-badge">{{ $plan->badge_text }}</div>
            @endif
            @if($plan->description)
              <div class="plan-description-text" style="font-size: 0.8125rem; color: #999; margin-top: 0.25rem;">{{ $plan->description }}</div>
            @endif
          </div>
          @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 2rem;">
          <p style="color: #999;">No PT plans configured. Go to <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a> to add PT plans.</p>
        </div>
        @endif
        <input type="hidden" name="plan_type" id="ptPlanType" value="{{ $ptPlans->first()?->plan_key ?? '' }}">
      </div>

      <!-- Payment Details -->
      <div class="form-section">
        <div class="payment-details-card">
          <div class="payment-details-header"><h4 class="payment-details-title">PT Duration</h4></div>
          <div class="payment-details-body">
            <div class="payment-details-row">
              <div class="payment-details-col payment-details-col-3">
                <label class="form-label">Current Due Date</label>
                <input type="text" class="form-control" id="ptCurrentDueDate" readonly placeholder="N/A">
              </div>
              <div class="payment-details-col payment-details-col-3">
                <label class="form-label">New Due Date</label>
                <input type="text" class="form-control" name="new_due_date" id="ptNewDueDate" readonly placeholder="Will be calculated">
              </div>
              <div class="payment-details-col payment-details-col-3">
                <label class="form-label">Additional Days</label>
                <input type="number" class="form-control" id="ptAdditionalDays" readonly placeholder="0" value="{{ $ptPlans->first()?->duration_days ?? 0 }}">
              </div>
            </div>
          </div>
        </div>

        <div class="payment-details-card">
          <div class="payment-details-body">
            <div class="payment-details-row">
              <div class="payment-details-col payment-details-col-2">
                <label class="form-label">Payment Method</label>
                <select class="form-select" name="payment_method" id="ptPaymentMethod" required>
                  <option value="" disabled selected>Select Payment Method</option>
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
                <input type="number" class="form-control" name="amount" id="ptAmount" placeholder="₱0.00" step="0.01" value="{{ $ptPlans->first()?->price ?? '0.00' }}" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-secondary" id="ptClearFormBtn">
          <i class="mdi mdi-close"></i> Clear
        </button>
        <button type="submit" class="btn btn-primary" id="ptSubmitPaymentBtn">
          <i class="mdi mdi-check"></i> Process PT Payment
        </button>
      </div>
      </form>
    </div>
  </div>
</div><!-- /ptPage -->

<!-- PT Confirmation Modal -->
<div id="ptConfirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-check-circle-outline"></i>
      <h5>Confirm PT Payment</h5>
      <button type="button" class="close" onclick="closePtConfirmationModal()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the PT payment details before proceeding.</p>
      <div class="confirm-details" id="ptConfirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closePtConfirmationModal()">Cancel</button>
      <button type="button" class="btn btn-update" onclick="confirmPtPayment()">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- PT Receipt Modal -->
<div id="ptReceiptModal" class="modal-overlay" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">PT Receipt Details</h3>
      <button class="modal-close" onclick="closePtReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="ptReceiptBody">
      <div class="loading-spinner"><div class="spinner"></div></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closePtReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printPtReceipt()"><i class="mdi mdi-printer"></i> Print</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // ========================================
  // PT PAYMENT LOGIC
  // ========================================
  let ptSelectedMemberStatus = '';
  let ptSelectedMemberDueDate = '';
  let ptSelectedClientId = '';
  let ptHasExistingPT = false;
  let isPtSubmitting = false;

  const ptPaymentTypePills = document.querySelectorAll('#ptPage .payment-type-pill');
  const ptPaymentTypeInput = document.getElementById('ptPaymentType');
  const ptMemberSearch = document.getElementById('ptMemberSearch');
  const ptMemberId = document.getElementById('ptMemberId');
  const ptNewPill = document.getElementById('ptNewPill');
  const ptExtensionPill = document.getElementById('ptExtensionPill');
  const ptRenewalPill = document.getElementById('ptRenewalPill');
  const ptPlanCards = document.querySelectorAll('#ptPlanSelector .plan-type-card');
  const ptPlanTypeInput = document.getElementById('ptPlanType');
  const ptAmountInput = document.getElementById('ptAmount');
  const ptAdditionalDaysInput = document.getElementById('ptAdditionalDays');

  // Payment Type Pill Toggle
  ptPaymentTypePills.forEach(pill => {
    pill.addEventListener('click', function() {
      const type = this.dataset.type;
      if (type === 'renewal' && ptSelectedMemberStatus === 'Active' && ptSelectedMemberDueDate && new Date(ptSelectedMemberDueDate) > new Date()) {
        ToastUtils.showWarning('PT plan is still active. Please use Extension instead.');
        return;
      }
      ptPaymentTypePills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      ptPaymentTypeInput.value = type;
      ptCalculateNewDueDate();
    });
  });

  // PT Plan Card Selection
  if (ptPlanCards) {
    ptPlanCards.forEach(card => {
      card.addEventListener('click', function() {
        ptPlanCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        ptPlanTypeInput.value = this.dataset.plan;
        ptAmountInput.value = parseFloat(this.dataset.price).toFixed(2);
        ptAdditionalDaysInput.value = this.dataset.duration;
        ptCalculateNewDueDate();
      });
    });
  }

  // Member Autocomplete (search all gym members)
  let ptSearchTimeout;
  ptMemberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('ptMemberResults');
    clearTimeout(ptSearchTimeout);
    if (query.length < 2) { resultsContainer.style.display = 'none'; return; }
    ptSearchTimeout = setTimeout(() => {
      fetch('{{ url("/api/pt/search-members") }}?q=' + encodeURIComponent(query), {
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
        resultsContainer.innerHTML = data.map(member => {
          const isExistingClient = member.source === 'client';
          // Badge for existing PT clients: show their PT status
          const ptBadgeClass = member.pt_status === 'Active' ? 'badge-success'
            : (member.pt_status === 'Due soon' ? 'badge-warning' : 'badge-danger');
          // Badge for gym members without a PT record: show membership status
          const mStatus = member.membership_status || 'Unknown';
          const mBadgeClass = mStatus === 'Active' ? 'badge-success' : 'badge-danger';
          // Secondary membership badge (shown alongside PT badge when client also has a membership)
          const mDue = member.membership_due_date
            ? '| Membership Due: ' + new Date(member.membership_due_date).toLocaleDateString()
            : '';
          return `
          <div class="autocomplete-item"
              data-id="${member.id}"
              data-source="${member.source}"
              data-name="${member.name}"
              data-contact="${member.contact || ''}"
              data-has-pt="${member.has_pt_client ? '1' : '0'}"
              data-pt-client-id="${member.pt_client_id || ''}"
              data-pt-plan="${member.pt_plan_type || ''}"
              data-pt-due-date="${member.pt_due_date || ''}"
              data-pt-status="${member.pt_status || 'N/A'}"
              data-membership-status="${member.membership_status || ''}"
              data-age="${member.age || ''}"
              data-sex="${member.sex || ''}">
            <strong>${member.name}</strong>
            ${isExistingClient
            ? `<span class="badge badge-info" style="margin-left:0.5rem;font-size:0.7rem;">PT CLIENT</span>
                <span class="badge ${ptBadgeClass}" style="margin-left:0.25rem;font-size:0.7rem;">${(member.pt_status || 'UNKNOWN').toUpperCase()}</span>
                ${member.membership_status ? `<span class="badge ${mBadgeClass}" style="margin-left:0.25rem;font-size:0.7rem;">${mStatus.toUpperCase()} MEMBER</span>` : ''}`
            : `<span class="badge ${mBadgeClass}" style="margin-left:0.5rem;font-size:0.7rem;">${mStatus.toUpperCase()} MEMBER</span>
                <span class="badge badge-secondary" style="margin-left:0.25rem;font-size:0.7rem;">NO PT PLAN</span>`
            }
            <div style="font-size:0.875rem;color:#999;">
              Contact: ${member.contact || 'N/A'}
              ${isExistingClient
                ? (member.pt_plan_type ? '| PT Plan: ' + member.pt_plan_type : '')
                  + (member.pt_due_date ? ' | PT Due: ' + new Date(member.pt_due_date).toLocaleDateString() : '')
                  + (member.membership_due_date ? ' ' + mDue : '')
                : (member.membership_due_date ? ' ' + mDue : '')
              }
            </div>
          </div>`;
        }).join('');
        resultsContainer.style.display = 'block';
        resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
          item.addEventListener('click', function() {
            const hasPT = this.dataset.hasPt === '1';
            const ptClientId = this.dataset.ptClientId;
            const ptDueDate = this.dataset.ptDueDate;
            const ptStatus = this.dataset.ptStatus;
            const ptPlan = this.dataset.ptPlan;
            const memberName = this.dataset.name;
            const memberContact = this.dataset.contact;

            const source = this.dataset.source; // 'client' | 'membership'

            ptMemberSearch.value = memberName;
            ptSelectedClientId = ptClientId || '';
            ptHasExistingPT = hasPT;
            ptSelectedMemberDueDate = ptDueDate;
            ptSelectedMemberStatus = ptStatus;

            // Store member details (used when creating a new PT client from a membership)
            ptMemberSearch.dataset.memberName = memberName;
            ptMemberSearch.dataset.memberContact = memberContact;
            ptMemberSearch.dataset.memberAge = this.dataset.age;
            ptMemberSearch.dataset.memberSex = this.dataset.sex;

            if (source === 'client') {
              // Existing PT client (standalone or previously enrolled gym member)
              // member_id must be a clients.id — no new client record needed
              ptMemberId.value = this.dataset.id;   // === pt_client_id
              ptMemberSearch.dataset.isNewPtClient = '0';
              ptMemberSearch.dataset.membershipId = '';
            } else {
              // Gym member with no PT record yet — new enrollment, member_id is memberships.id
              ptMemberId.value = this.dataset.id;
              ptMemberSearch.dataset.isNewPtClient = '1';
              ptMemberSearch.dataset.membershipId = this.dataset.id;
            }

            // Show member info card
            document.getElementById('ptMemberInfoSection').style.display = 'block';
            document.getElementById('ptMemberSelectionSection').style.display = 'none';

            // Update top-right badge based on actual PT status (or membership status for new clients)
            const badge = document.getElementById('ptMemberBadge');
            if (hasPT) {
              const badgeClass = ptStatus === 'Active' ? 'badge-success'
                : (ptStatus === 'Due soon' ? 'badge-warning' : 'badge-danger');
              badge.className = 'badge ' + badgeClass;
              badge.style.marginLeft = 'auto'; badge.style.fontSize = '0.8rem';
              badge.textContent = ptStatus + ' PT';
            } else {
              const mStat = this.dataset.membershipStatus || '';
              const badgeClass = mStat === 'Active' ? 'badge-success' : (mStat ? 'badge-danger' : 'badge-secondary');
              badge.className = 'badge ' + badgeClass;
              badge.style.marginLeft = 'auto'; badge.style.fontSize = '0.8rem';
              badge.textContent = mStat ? mStat + ' Membership' : 'No Membership';
            }

            document.getElementById('ptSelectedName').textContent = memberName;
            document.getElementById('ptSelectedContact').textContent = memberContact || 'N/A';
            document.getElementById('ptSelectedStatus').textContent = hasPT ? ptStatus : 'New PT Client';
            document.getElementById('ptSelectedPlan').textContent = hasPT ? (ptPlan || 'N/A') : 'None';
            document.getElementById('ptSelectedDueDate').textContent = ptDueDate ? new Date(ptDueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';

            // Update current due date field
            document.getElementById('ptCurrentDueDate').value = ptDueDate
              ? new Date(ptDueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
              : 'No due date';

            // Enable/disable pills based on status
            if (hasPT && ptStatus === 'Active' && ptDueDate && new Date(ptDueDate) > new Date()) {
              // Active PT: only Extension allowed
              ptNewPill.style.opacity = '0.5';
              ptNewPill.style.pointerEvents = 'none';
              ptRenewalPill.style.opacity = '0.5';
              ptRenewalPill.style.pointerEvents = 'none';
              ptExtensionPill.style.opacity = '1';
              ptExtensionPill.style.pointerEvents = 'auto';
              ptPaymentTypePills.forEach(p => p.classList.remove('active'));
              ptExtensionPill.classList.add('active');
              ptPaymentTypeInput.value = 'extension';
            } else if (hasPT) {
              // Expired/inactive PT: Renewal and Extension allowed
              ptNewPill.style.opacity = '0.5';
              ptNewPill.style.pointerEvents = 'none';
              ptRenewalPill.style.opacity = '1';
              ptRenewalPill.style.pointerEvents = 'auto';
              ptExtensionPill.style.opacity = '1';
              ptExtensionPill.style.pointerEvents = 'auto';
              ptPaymentTypePills.forEach(p => p.classList.remove('active'));
              ptRenewalPill.classList.add('active');
              ptPaymentTypeInput.value = 'renewal';
            } else {
              // No PT record: only New allowed
              ptNewPill.style.opacity = '1';
              ptNewPill.style.pointerEvents = 'auto';
              ptRenewalPill.style.opacity = '0.5';
              ptRenewalPill.style.pointerEvents = 'none';
              ptExtensionPill.style.opacity = '0.5';
              ptExtensionPill.style.pointerEvents = 'none';
              ptPaymentTypePills.forEach(p => p.classList.remove('active'));
              ptNewPill.classList.add('active');
              ptPaymentTypeInput.value = 'new';
            }

            resultsContainer.style.display = 'none';
            ptCalculateNewDueDate();
          });
        });
      })
      .catch(error => { console.error('Error:', error); ToastUtils.showError('Error searching for members'); });
    }, 300);
  });

  // Close autocomplete on outside click
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#ptMemberSearch') && !e.target.closest('#ptMemberResults')) {
      document.getElementById('ptMemberResults').style.display = 'none';
    }
  });

  // Change Member button
  document.getElementById('ptClearMemberBtn').addEventListener('click', function() {
    document.getElementById('ptMemberInfoSection').style.display = 'none';
    document.getElementById('ptMemberSelectionSection').style.display = 'block';
    ptMemberSearch.value = '';
    ptMemberId.value = '';
    ptSelectedClientId = '';
    ptHasExistingPT = false;
    ptSelectedMemberDueDate = '';
    ptSelectedMemberStatus = '';
    document.getElementById('ptCurrentDueDate').value = '';
    document.getElementById('ptNewDueDate').value = '';
    ptPaymentTypePills.forEach(p => { p.classList.remove('active'); p.style.opacity = '1'; p.style.pointerEvents = 'auto'; });
    ptRenewalPill.classList.add('active');
    ptPaymentTypeInput.value = 'renewal';
  });

  // Calculate new due date
  function ptCalculateNewDueDate() {
    const paymentType = ptPaymentTypeInput.value;
    const duration = parseInt(ptAdditionalDaysInput.value) || 0;
    if (duration === 0) { document.getElementById('ptNewDueDate').value = ''; return; }

    let startDate;
    const today = new Date();

    if (paymentType === 'new' || paymentType === 'renewal') {
      startDate = today;
    } else if (paymentType === 'extension') {
      const currentDueDateText = document.getElementById('ptCurrentDueDate').value;
      if (currentDueDateText && currentDueDateText !== 'No due date' && currentDueDateText !== 'N/A') {
        startDate = new Date(currentDueDateText);
      } else {
        document.getElementById('ptNewDueDate').value = '';
        return;
      }
    }

    const newDueDate = new Date(startDate);
    newDueDate.setDate(newDueDate.getDate() + duration);
    document.getElementById('ptNewDueDate').value = newDueDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  // Form Submission
  const ptPaymentForm = document.getElementById('ptPaymentForm');
  ptPaymentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (isPtSubmitting) return;

    const errors = [];

    if (!ptMemberId.value) {
      errors.push('Please select a member.');
    }

    if (!ptPlanTypeInput.value) {
      errors.push('Please select a PT plan.');
    }

    const paymentMethodEl = document.getElementById('ptPaymentMethod');
    if (!paymentMethodEl.value) {
      errors.push('Please select a payment method.');
    }

    const amountVal = parseFloat(ptAmountInput.value);
    if (!amountVal || amountVal <= 0) {
      errors.push('Payment amount is missing or invalid.');
    }

    const newDueDateEl = document.getElementById('ptNewDueDate');
    if (!newDueDateEl.value || newDueDateEl.value.trim() === '') {
      errors.push('New Due Date could not be calculated. Please select a plan and member.');
    }

    if (errors.length > 0) {
      const errorList = errors.map(e => '• ' + e).join('\n');
      ToastUtils.showError('Please fix the following:\n' + errorList);
      return;
    }

    showPtConfirmationModal();
  });

  function showPtConfirmationModal() {
    const pt = ptPaymentTypeInput.value;
    const pl = ptPlanTypeInput.value;
    const amt = ptAmountInput.value;
    const pm = document.getElementById('ptPaymentMethod').value;
    const mn = document.getElementById('ptSelectedName').textContent;
    const nd = document.getElementById('ptNewDueDate').value;
    const cd = document.getElementById('ptCurrentDueDate').value;

    document.getElementById('ptConfirmationDetails').innerHTML = `
      <div class="confirm-row"><span class="confirm-label">Member:</span><span class="confirm-value">${mn}</span></div>
      <div class="confirm-row"><span class="confirm-label">Payment Type:</span><span class="confirm-value">${pt.toUpperCase()}</span></div>
      <div class="confirm-row"><span class="confirm-label">PT Plan:</span><span class="confirm-value">${pl}</span></div>
      <div class="confirm-row"><span class="confirm-label">Amount:</span><span class="confirm-value">₱${parseFloat(amt).toFixed(2)}</span></div>
      <div class="confirm-row"><span class="confirm-label">Payment Method:</span><span class="confirm-value">${pm}</span></div>
      <div class="confirm-row"><span class="confirm-label">Current Due Date:</span><span class="confirm-value">${cd || 'N/A'}</span></div>
      <div class="confirm-row"><span class="confirm-label">New Due Date:</span><span class="confirm-value" style="color: #28a745;">${nd}</span></div>`;
    document.getElementById('ptConfirmationModal').classList.add('show');
  }

  window.closePtConfirmationModal = function() {
    document.getElementById('ptConfirmationModal').classList.remove('show');
  };

  window.confirmPtPayment = function() {
    if (isPtSubmitting) return;
    isPtSubmitting = true;
    closePtConfirmationModal();

    const form = document.getElementById('ptPaymentForm');
    const submitBtn = document.getElementById('ptSubmitPaymentBtn');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...'; }

    const formData = new FormData(form);

    // Add extra data for new PT client creation
    const isNewPtClient = ptMemberSearch.dataset.isNewPtClient === '1' || ptPaymentTypeInput.value === 'new';
    if (isNewPtClient) {
      formData.set('is_new_pt_client', '1');
      formData.set('membership_id', ptMemberSearch.dataset.membershipId || '');
      formData.set('member_name', ptMemberSearch.dataset.memberName || '');
      formData.set('member_contact', ptMemberSearch.dataset.memberContact || '');
      formData.set('member_age', ptMemberSearch.dataset.memberAge || '');
      formData.set('member_sex', ptMemberSearch.dataset.memberSex || '');
    }

    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ToastUtils.showSuccess(data.message || 'PT Payment processed successfully!');
        form.reset();
        window._ptReloadAfterReceipt = true;
        setTimeout(() => { viewPtReceipt(data.payment.id); }, 300);
      } else {
        ToastUtils.showError(data.message || 'PT Payment failed.');
        isPtSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process PT Payment'; }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An error occurred processing PT payment.');
      isPtSubmitting = false;
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process PT Payment'; }
    });
  };

  // Clear Form
  document.getElementById('ptClearFormBtn').addEventListener('click', function() {
    ptPaymentForm.reset();
    ptMemberId.value = '';
    ptSelectedClientId = '';
    ptHasExistingPT = false;
    ptSelectedMemberDueDate = '';
    ptSelectedMemberStatus = '';
    document.getElementById('ptMemberInfoSection').style.display = 'none';
    document.getElementById('ptMemberSelectionSection').style.display = 'block';
    document.getElementById('ptCurrentDueDate').value = '';
    document.getElementById('ptNewDueDate').value = '';
    if (ptPlanCards) {
      ptPlanCards.forEach(c => c.classList.remove('active'));
      const defaultPlanCard = document.querySelector('#ptPlanSelector .plan-type-card');
      if (defaultPlanCard) {
        defaultPlanCard.classList.add('active');
        ptPlanTypeInput.value = defaultPlanCard.dataset.plan;
        ptAmountInput.value = parseFloat(defaultPlanCard.dataset.price).toFixed(2);
        ptAdditionalDaysInput.value = defaultPlanCard.dataset.duration;
      }
    }
    ptNewPill.style.opacity = '1';
    ptNewPill.style.pointerEvents = 'auto';
    ptExtensionPill.style.opacity = '1';
    ptExtensionPill.style.pointerEvents = 'auto';
    ptRenewalPill.style.opacity = '1';
    ptRenewalPill.style.pointerEvents = 'auto';
    ptPaymentTypePills.forEach(p => p.classList.remove('active'));
    ptRenewalPill.classList.add('active');
    ptPaymentTypeInput.value = 'renewal';
    isPtSubmitting = false;
    const submitBtn = document.getElementById('ptSubmitPaymentBtn');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process PT Payment'; }
  });
}); // DOMContentLoaded (PT)

// ========================================
// PT RECEIPT FUNCTIONS
// ========================================
function viewPtReceipt(transactionId) {
  const modal = document.getElementById('ptReceiptModal');
  const receiptBody = document.getElementById('ptReceiptBody');
  modal.classList.add('show');
  receiptBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
  fetch('/pt-payment/' + transactionId + '/receipt')
    .then(response => response.json())
    .then(data => { receiptBody.innerHTML = generatePtReceiptHTML(data); })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('Failed to load PT receipt.');
      receiptBody.innerHTML = '<div style="padding:2rem;color:#dc3545;text-align:center;"><i class="mdi mdi-alert-circle" style="font-size:3rem;"></i><p>Failed to load receipt.</p></div>';
    });
}

function generatePtReceiptHTML(data) {
  return '<div class="receipt-container"><div class="receipt-header"><h2>PERSONAL TRAINING PAYMENT RECEIPT</h2><p><strong>Abstrack Fitness Gym</strong></p><p>Toril, Davao Del Sur</p></div>' +
    '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px;">' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong><span style="display: block; font-weight: 600;">#' + data.receipt_number + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong><span style="display: block; font-weight: 600;">' + (data.formatted_date || new Date(data.created_at).toLocaleString()) + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Member Name</strong><span style="display: block; font-weight: 600;">' + (data.member_name || 'N/A') + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Contact</strong><span style="display: block; font-weight: 600;">' + (data.member_contact || '') + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Type</strong><span style="display: block; font-weight: 600;">' + (data.payment_type || '').toUpperCase() + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong><span style="display: block; font-weight: 600;">' + (data.payment_method || 'N/A') + '</span></div></div>' +
    '<table class="receipt-table"><thead><tr><th>Description</th><th style="text-align: right;">Amount</th></tr></thead><tbody>' +
    '<tr><td><strong>' + (data.plan_type || 'PT') + ' Plan</strong><br><small style="color: #666;">Duration: ' + (data.duration || 'N/A') + ' days</small></td><td style="text-align: right;">₱' + parseFloat(data.amount || 0).toFixed(2) + '</td></tr>' +
    '</tbody></table>' +
    '<div class="receipt-total"><div class="receipt-row" style="font-size: 1.3rem;"><span><strong>Total Paid:</strong></span><span><strong>₱' + parseFloat(data.amount || 0).toFixed(2) + '</strong></span></div></div>' +
    '<div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ccc;"><div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Previous Due Date</strong><span style="display: block; font-weight: 600;">' + (data.previous_due_date || 'N/A') + '</span></div>' +
    '<div style="padding: 10px; background: #f8f9fa; border-radius: 4px;"><strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">New Due Date</strong><span style="display: block; font-weight: 600; color: #28a745;">' + (data.new_due_date || 'N/A') + '</span></div></div></div>' +
    (data.notes ? '<div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;"><strong style="display: block; margin-bottom: 8px; color: #666;">Notes:</strong><p style="margin: 0; color: #333;">' + data.notes + '</p></div>' : '') +
    '<div class="receipt-footer"><p><strong>Thank you for choosing our Personal Training service!</strong></p><p style="font-size: 0.875rem;">Please keep this receipt for your records.</p></div></div>';
}

function closePtReceiptModal() {
  document.getElementById('ptReceiptModal').classList.remove('show');
  if (window._ptReloadAfterReceipt) { window._ptReloadAfterReceipt = false; window.location.reload(); }
}

function printPtReceipt() {
  const content = document.getElementById('ptReceiptBody').innerHTML;
  const pw = window.open('', '_blank');
  pw.document.write('<!DOCTYPE html><html><head><title>PT Receipt</title><style>body{font-family:"Courier New",monospace}.receipt-container{max-width:600px;margin:0 auto;padding:20px}.receipt-header{text-align:center;margin-bottom:30px;padding-bottom:20px;border-bottom:2px dashed #333}.receipt-table{width:100%;border-collapse:collapse;margin:20px 0}.receipt-table th{background:#333;color:#fff;padding:10px;text-align:left}.receipt-table td{padding:10px;border-bottom:1px solid #ddd}.receipt-row{display:flex;justify-content:space-between;margin-bottom:8px}.receipt-total{margin-top:20px;padding-top:20px;border-top:2px solid #333}.receipt-footer{margin-top:30px;padding-top:20px;border-top:2px dashed #333;text-align:center}</style></head><body>' + content + '</body></html>');
  pw.document.close(); pw.print();
}

// PT modal escape/overlay close handlers
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (document.getElementById('ptConfirmationModal')?.classList.contains('show')) closePtConfirmationModal();
    if (document.getElementById('ptReceiptModal')?.classList.contains('show')) closePtReceiptModal();
  }
});
document.getElementById('ptConfirmationModal')?.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
document.getElementById('ptReceiptModal')?.addEventListener('click', function(e) { if (e.target === this) closePtReceiptModal(); });
</script>
