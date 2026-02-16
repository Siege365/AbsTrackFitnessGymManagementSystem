@extends('layouts.admin')

@section('title', 'Membership Payment System')

@push('styles')
@vite(['resources/css/membership-payment.css'])
@endpush

@section('content')
<div class="container-fluid">
  <!-- Stats Grid -->
  <div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">₱{{ number_format($monthlyRevenue ?? 0, 2) }}</h2>
                        <p class="text-muted mb-0">Monthly Revenue</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="mdi mdi-arrow-up"></i> +12.5%
                    </div>
                </div>
            </div>    
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                     <div>
                        <h2 class="mb-0">₱{{ number_format($refundedToday ?? 0, 2) }}</h2>
                        <p class="text-muted mb-0">Refunded Today</p>
                    </div>
                    <div class="stat-change neutral">
                        <i class="mdi mdi-cash-refund"></i> {{ $refundedTodayCount ?? 0 }} transactions
                    </div>
                </div>
            </div>    
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                     <div>
                        <h2 class="mb-0">₱{{ number_format($totalRefunded ?? 0, 2) }}</h2>
                        <p class="text-muted mb-0">Total Refunded</p>
                    </div>
                    <div class="stat-change negative">
                        <i class="mdi mdi-alert-circle"></i> {{ $totalRefundedCount ?? 0 }} all-time
                    </div>
                </div>
            </div>    
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                     <div>
                        <h2 class="mb-0">₱{{ number_format($todayRevenue ?? 0, 2) }}</h2>
                        <p class="text-muted mb-0">Today's Revenue</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="mdi mdi-arrow-up"></i> +15.3%
                    </div>
                </div>
            </div>    
        </div>
    </div>
  </div>
</div>

  <!-- Payment Form Card -->
  <div class="card">
    <div class="card-body">
        <h2 class="card-title">Process Membership Payment</h2>
        
        <form id="membershipPaymentForm" action="{{ route('membership.payment.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Payment Type Selector -->
        <div class="payment-type-selector">
        <div class="payment-type-pill" data-type="new">
            <span class="icon">
            <i class="mdi mdi-account-plus"></i>
            </span>
            <span class="label">New Membership</span>
        </div>

        <div class="payment-type-pill active" data-type="renewal">
            <span class="icon">
            <i class="mdi mdi-autorenew"></i>
            </span>
            <span class="label">Renewal</span>
        </div>

        <div class="payment-type-pill" data-type="extension" id="extensionPill">
            <span class="icon">
            <i class="mdi mdi-calendar-plus"></i>
            </span>
            <span class="label">Extension</span>
        </div>
        </div>
        
        <input type="hidden" name="payment_type" id="paymentType" value="renewal">

        <!-- Member Selection (Hidden for New Membership) -->
        <div id="memberSelectionSection">
            <div class="form-group">
            <label class="form-label">Select Member</label>
            <div style="position: relative;">
                <input 
                type="text" 
                class="form-control" 
                id="memberSearch" 
                name="member_search"
                placeholder="Search by name or contact..."
                autocomplete="off"
                >
              <div id="memberResults" class="autocomplete-results" style="display:none;"></div>
                <input type="hidden" name="member_id" id="memberId">
                <input type="hidden" id="memberStatus">
            </div>
            </div>
        </div>

        <!-- New Member Details (Shown only for New Membership) -->
        <div id="newMemberSection" style="display: none;">
            <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="new_member_name" id="newMemberName" placeholder="Enter full name">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control" name="new_member_contact" id="newMemberContact" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label">Avatar (Optional)</label>
                <input type="file" class="form-control" name="new_member_avatar" id="newMemberAvatar" accept="image/*">
            </div>
            </div>
        </div>

        <!-- Plan Type Selector -->
        <label class="form-label">Select Plan Type</label>
        <div class="plan-type-selector">
            <div class="plan-type-card active" data-plan="Monthly" data-price="500" data-duration="30">
            <div class="plan-name">Monthly Plan</div>
            <div class="plan-duration">30 Days Access</div>
            <div class="plan-price">₱500.00</div>
            </div>
            <div class="plan-type-card" data-plan="Session" data-price="50" data-duration="1">
            <div class="plan-name">Session Pass</div>
            <div class="plan-duration">1 Day Access</div>
            <div class="plan-price">₱50.00</div>
            </div>
        </div>
        <input type="hidden" name="plan_type" id="planType" value="Monthly">

        <!-- Payment Details -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
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

                            <div class="col-md-2">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" id="amount" placeholder="₱0.00" step="0.01" value="500.00" readonly>
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">Current Due Date</label>
                            <input type="text" class="form-control" id="currentDueDate" readonly placeholder="N/A">
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">New Due Date</label>
                            <input type="text" class="form-control" name="new_due_date" id="newDueDate" readonly placeholder="Will be calculated">
                            </div>

                            <div class="col-md-2">
                            <label class="form-label">Additional Days</label>
                            <input type="number" class="form-control" id="additionalDays" readonly placeholder="0" value="30">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
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

<!-- Payment Confirmation Modal -->
<div id="confirmationModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Confirm Payment</h3>
      <button class="modal-close" onclick="closeConfirmationModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="confirmation-icon warning">
        <i class="mdi mdi-alert-circle-outline"></i>
      </div>
      <p class="confirmation-message">Please review the payment details before proceeding.</p>
      <div class="confirmation-details" id="confirmationDetails"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeConfirmationModal()">
        <i class="mdi mdi-close"></i> Cancel
      </button>
      <button type="button" class="btn btn-primary" onclick="confirmPayment()">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Refund Modal -->
<div id="refundModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Process Refund</h3>
      <button class="modal-close" onclick="closeRefundModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="refund-warning">
        <i class="mdi mdi-alert"></i>
        <strong>Warning:</strong> This will reverse the membership due date and mark this transaction as refunded.
      </div>
      <div class="confirmation-details" id="refundDetails"></div>
      <div class="form-group">
        <label class="form-label">Refund Reason (Optional)</label>
        <textarea class="form-control" id="refundReason" rows="3" placeholder="Enter reason for refund..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">
        <i class="mdi mdi-close"></i> Cancel
      </button>
      <button type="button" class="btn btn-warning" onclick="confirmRefund()">
        <i class="mdi mdi-cash-refund"></i> Process Refund
      </button>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="modal-overlay modal-overlay-centered" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Payment Receipt</h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body" id="receiptBody">
      <div style="text-align: center; padding: 2rem; color: #666;">
        <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
        <p>Loading receipt...</p>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">
        <i class="mdi mdi-close"></i> Close
      </button>
      <button type="button" class="btn btn-primary" onclick="printReceipt()">
        <i class="mdi mdi-printer"></i> Print Receipt
      </button>
    </div>
  </div>
</div>

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" action="{{ route('membership.payment.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Refund Form -->
<form id="refundForm" action="" method="POST" style="display: none;">
  @csrf
  @method('POST')
  <input type="hidden" name="reason" id="refundReasonInput">
</form>

@endsection

@push('scripts')
<script>
// Fallback ToastUtils if the main library fails to load
if (typeof ToastUtils === 'undefined') {
  window.ToastUtils = {
    showSuccess: function(msg) { console.log('✅ Success:', msg); alert('Success: ' + msg); },
    showError: function(msg) { console.error('❌ Error:', msg); alert('Error: ' + msg); },
    showWarning: function(msg) { console.warn('⚠️ Warning:', msg); alert('Warning: ' + msg); },
    showInfo: function(msg) { console.info('ℹ️ Info:', msg); }
  };
}
</script>
@vite(['resources/js/common/avatar-utils.js'])
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/bulk-selection.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
  let selectedMemberStatus = '';
  let selectedMemberDueDate = '';
  let currentRefundId = null;

  // Payment Type Selection
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
      
      if (type === 'extension' && !memberId.value) {
        return;
      }

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
      calculateNewDueDate();
    });
  });

  // Plan Type Selection
  const planTypeCards = document.querySelectorAll('.plan-type-card');
  const planTypeInput = document.getElementById('planType');
  const amountInput = document.getElementById('amount');
  const additionalDaysInput = document.getElementById('additionalDays');

  planTypeCards.forEach(card => {
    card.addEventListener('click', function() {
      planTypeCards.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      
      const planType = this.dataset.plan;
      const price = this.dataset.price;
      const duration = this.dataset.duration;
      
      planTypeInput.value = planType;
      amountInput.value = parseFloat(price).toFixed(2);
      additionalDaysInput.value = duration;
      
      calculateNewDueDate();
    });
  });

  // Member Autocomplete
  let memberSearchTimeout;
  memberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('memberResults');

    clearTimeout(memberSearchTimeout);

    if (query.length < 2) {
      resultsContainer.style.display = 'none';
      return;
    }

    memberSearchTimeout = setTimeout(() => {
      fetch('{{ url('/api/members/search') }}?q=' + encodeURIComponent(query), {
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>';
            resultsContainer.style.display = 'block';
            return;
          }

          resultsContainer.innerHTML = data.map(member => `
            <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-plan="${member.plan_type}" data-status="${member.status}">
              <strong>${member.name}</strong>
              <div style="font-size: 0.875rem; color: #999;">
                Contact: ${member.contact || 'N/A'} | Plan: ${member.plan_type} | Status: ${member.status}
                ${member.due_date ? `| Due: ${new Date(member.due_date).toLocaleDateString()}` : '| No due date'}
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
              
              const dueDate = this.dataset.dueDate;
              document.getElementById('currentDueDate').value = dueDate ? 
                new Date(dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 
                'No due date';
              
              extensionPill.style.opacity = '1';
              extensionPill.style.pointerEvents = 'auto';

              const renewalPill = document.querySelector('[data-type="renewal"]');
              if (selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
                renewalPill.style.opacity = '0.5';
                renewalPill.style.pointerEvents = 'none';
                paymentTypePills.forEach(p => p.classList.remove('active'));
                extensionPill.classList.add('active');
                paymentTypeInput.value = 'extension';
              } else {
                renewalPill.style.opacity = '1';
                renewalPill.style.pointerEvents = 'auto';
              }
              
              resultsContainer.style.display = 'none';
              calculateNewDueDate();
            });
          });
        })
        .catch(error => {
          console.error('Error fetching members:', error);
          ToastUtils.showError('Error searching for members');
        });
    }, 300);
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#memberResults')) {
      document.getElementById('memberResults').style.display = 'none';
    }
  });

  document.getElementById('newMemberContact').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9+]/g, '');
    
    if (value.startsWith('+63')) {
      if (value.length > 13) {
        value = value.substring(0, 13);
      }
    } else if (value.startsWith('09')) {
      if (value.length > 11) {
        value = value.substring(0, 11);
      }
    }
    
    e.target.value = value;
  });

  function calculateNewDueDate() {
    const paymentType = paymentTypeInput.value;
    const duration = parseInt(additionalDaysInput.value) || 0;
    const currentDueDateText = document.getElementById('currentDueDate').value;

    if (duration === 0) {
      document.getElementById('newDueDate').value = '';
      return;
    }

    let startDate;
    const today = new Date();

    if (paymentType === 'new') {
      startDate = today;
    } else if (paymentType === 'renewal') {
      if (currentDueDateText && currentDueDateText !== 'No due date') {
        const currentDueDate = new Date(currentDueDateText);
        startDate = currentDueDate > today ? currentDueDate : today;
      } else {
        startDate = today;
      }
    } else if (paymentType === 'extension') {
      if (currentDueDateText && currentDueDateText !== 'No due date') {
        startDate = new Date(currentDueDateText);
      } else {
        return;
      }
    }

    const newDueDate = new Date(startDate);
    newDueDate.setDate(newDueDate.getDate() + duration);

    document.getElementById('newDueDate').value = newDueDate.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  }

  // Form Submission with Confirmation
  const paymentForm = document.getElementById('membershipPaymentForm');
  paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (paymentTypeInput.value === 'new') {
      const contact = document.getElementById('newMemberContact').value;
      if (contact && !contact.match(/^(09\d{9}|\+639\d{9})$/)) {
        ToastUtils.showError('Invalid contact number. Use 09XXXXXXXXX or +639XXXXXXXXX format');
        return;
      }
    }

    // Ensure member selected for non-new payments
    if (paymentTypeInput.value !== 'new') {
      if (!memberId.value) {
        ToastUtils.showError('Please select a member before processing payment.');
        return;
      }
    } else {
      // For new members ensure name provided
      const newName = document.getElementById('newMemberName').value.trim();
      if (!newName) {
        ToastUtils.showError('Please enter the new member\'s name.');
        return;
      }
    }

    // Show confirmation modal
    showConfirmationModal();
  });

  function showConfirmationModal() {
    const paymentType = paymentTypeInput.value;
    const planType = planTypeInput.value;
    const amount = amountInput.value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const memberName = paymentType === 'new' 
      ? document.getElementById('newMemberName').value 
      : memberSearch.value;
    const newDueDate = document.getElementById('newDueDate').value;

    const details = `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Member:</span>
        <span class="confirmation-detail-value">${memberName}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Type:</span>
        <span class="confirmation-detail-value">${paymentType.toUpperCase()}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Plan:</span>
        <span class="confirmation-detail-value">${planType}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Amount:</span>
        <span class="confirmation-detail-value">₱${parseFloat(amount).toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Method:</span>
        <span class="confirmation-detail-value">${paymentMethod}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">New Due Date:</span>
        <span class="confirmation-detail-value" style="color: #28a745;">${newDueDate}</span>
      </div>
    `;

    document.getElementById('confirmationDetails').innerHTML = details;
    document.getElementById('confirmationModal').classList.add('show');
  }

  window.closeConfirmationModal = function() {
    document.getElementById('confirmationModal').classList.remove('show');
  };

    // Find the confirmPayment function and replace it with this:

  window.confirmPayment = function() {
    closeConfirmationModal();
    
    const form = document.getElementById('membershipPaymentForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Disable button to prevent double submission
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
    }
    
    const formData = new FormData(form);
    
    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ToastUtils.showSuccess(data.message || 'Payment processed successfully!');
        
        // Show receipt modal
        window._reloadAfterReceipt = true;
        setTimeout(() => {
          viewReceipt(data.payment.id);
        }, 500);
        
        // Reset form
        form.reset();
        
      } else {
        ToastUtils.showError(data.message || 'Payment failed. Please try again.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="mdi mdi-cash-check"></i> Process Payment';
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An error occurred. Please try again.');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="mdi mdi-cash-check"></i> Process Payment';
      }
    });
  };

  // Refund Modal Functions
  window.openRefundModal = function(id, receiptNumber, amount, memberName) {
    currentRefundId = id;
    
    const details = `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Receipt Number:</span>
        <span class="confirmation-detail-value">#${receiptNumber}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Member:</span>
        <span class="confirmation-detail-value">${memberName}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Refund Amount:</span>
        <span class="confirmation-detail-value" style="color: #dc3545;">₱${parseFloat(amount).toFixed(2)}</span>
      </div>
    `;

    document.getElementById('refundDetails').innerHTML = details;
    document.getElementById('refundReason').value = '';
    document.getElementById('refundModal').classList.add('show');
  };

  window.closeRefundModal = function() {
    document.getElementById('refundModal').classList.remove('show');
    currentRefundId = null;
  };

  window.confirmRefund = function() {
    if (!currentRefundId) return;

    const reason = document.getElementById('refundReason').value;
    const refundForm = document.getElementById('refundForm');
    
    refundForm.action = `/membership-payment/${currentRefundId}/refund`;
    document.getElementById('refundReasonInput').value = reason;

    closeRefundModal();
    
    ToastUtils.showInfo('Processing refund...');
    
    fetch(refundForm.action, {
      method: 'POST',
      body: new FormData(refundForm),
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ToastUtils.showSuccess(data.message || 'Refund processed successfully!');
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        ToastUtils.showError(data.message || 'Failed to process refund');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('Failed to process refund. Please try again.');
    });
  };

  // Clear Form
  document.getElementById('clearFormBtn').addEventListener('click', function() {
    paymentForm.reset();
    memberId.value = '';
    selectedMemberStatus = '';
    selectedMemberDueDate = '';
    document.getElementById('currentDueDate').value = '';
    document.getElementById('newDueDate').value = '';
    
    planTypeCards.forEach(c => c.classList.remove('active'));
    planTypeCards[0].classList.add('active');
    planTypeInput.value = 'Monthly';
    amountInput.value = '500.00';
    additionalDaysInput.value = '30';

    extensionPill.style.opacity = '0.5';
    extensionPill.style.pointerEvents = 'none';
    document.querySelector('[data-type="renewal"]').style.opacity = '1';
    document.querySelector('[data-type="renewal"]').style.pointerEvents = 'auto';
  });

  // Checkbox Selection
  const selectAllCheckbox = document.getElementById('selectAll');
  const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
  const selectedCountSpan = document.getElementById('selectedCount');

  // Add null checks
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      transactionCheckboxes.forEach(cb => {
        cb.checked = this.checked;
      });
      updateBulkDeleteButton();
    });
  }

  if (transactionCheckboxes.length > 0) {
    transactionCheckboxes.forEach(cb => {
      cb.addEventListener('change', function() {
        updateBulkDeleteButton();
        if (selectAllCheckbox) {
          const allChecked = Array.from(transactionCheckboxes).every(checkbox => checkbox.checked);
          selectAllCheckbox.checked = allChecked;
        }
      });
    });
  }

  function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
    const count = checkedBoxes.length;
    if (selectedCountSpan) selectedCountSpan.textContent = count;
    if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
  }

  // Find this section and fix it:

  if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function() {
      const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
      const ids = Array.from(checkedBoxes).map(cb => cb.value);

      if (ids.length === 0) {
        ToastUtils.showWarning('Please select at least one transaction to delete');
        return;
      }

      if (confirm(`Are you sure you want to delete ${ids.length} transaction(s)? This action cannot be undone.`)) {
        document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
        document.getElementById('bulkDeleteForm').submit();
      }
    });
  } // ← This closing brace was missing!

  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
        this.submit();
      }
    });
  });

  // Filter Dropdown
  const filterBtn = document.getElementById('filterBtn');
  const filterMenu = document.getElementById('filterMenu');

  // Add null checks for filter elements too
  if (filterBtn && filterMenu) {
    filterBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      filterMenu.classList.toggle('show');
    });

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.filter-dropdown')) {
        filterMenu.classList.remove('show');
      }
    });
  }
}); // This closes the DOMContentLoaded listener

// View Receipt
function viewReceipt(transactionId) {
  const modal = document.getElementById('receiptModal');
  const receiptBody = document.getElementById('receiptBody');

  modal.classList.add('show');
  receiptBody.innerHTML = `
    <div style="text-align: center; padding: 2rem; color: #666;">
      <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
      <p>Loading receipt...</p>
    </div>
  `;

  fetch(`/membership-payment/${transactionId}/receipt`)
    .then(response => response.json())
    .then(data => {
      receiptBody.innerHTML = generateReceiptHTML(data);
    })
    .catch(error => {
      console.error('Error loading receipt:', error);
      receiptBody.innerHTML = `
        <div style="text-align: center; padding: 2rem; color: #dc3545;">
          <i class="mdi mdi-alert-circle" style="font-size: 3rem;"></i>
          <p>Failed to load receipt. Please try again.</p>
        </div>
      `;
    });
}

function generateReceiptHTML(data) {
  const refundStamp = data.refunded_at ? `
    <div class="receipt-refund-stamp">
      <h3>REFUNDED</h3>
      <p>Refunded on: ${data.refunded_at}</p>
      ${data.refund_reason ? `<p>Reason: ${data.refund_reason}</p>` : ''}
    </div>
  ` : '';

  return `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>MEMBERSHIP PAYMENT RECEIPT</h2>
        <p><strong>Abstrack Fitness Gym</strong></p>
        <p>Toril, Davao Del Sur</p>
        <p>Phone: (123) 456-7890</p>
      </div>

      <div class="receipt-info-grid">
        <div class="receipt-info-item">
          <strong>Receipt Number</strong>
          <span>#${data.receipt_number}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Date & Time</strong>
          <span>${data.formatted_date}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Member Name</strong>
          <span>${data.member_name}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Contact</strong>
          <span>${data.member_contact}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Payment Type</strong>
          <span>${data.payment_type.toUpperCase()}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Payment Method</strong>
          <span>${data.payment_method}</span>
        </div>
      </div>

      <table class="receipt-table">
        <thead>
          <tr>
            <th>Description</th>
            <th style="text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <strong>${data.plan_type} Plan</strong><br>
              <small style="color: #666;">Duration: ${data.duration} days</small>
            </td>
            <td style="text-align: right;">₱${parseFloat(data.amount).toFixed(2)}</td>
          </tr>
        </tbody>
      </table>

      <div class="receipt-total">
        <div class="receipt-total-row grand-total">
          <strong>Total Paid:</strong>
          <span>₱${parseFloat(data.amount).toFixed(2)}</span>
        </div>
      </div>

      <div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px dashed #ccc;">
        <div class="receipt-info-grid">
          <div class="receipt-info-item">
            <strong>Previous Due Date</strong>
            <span>${data.previous_due_date || 'N/A'}</span>
          </div>
          <div class="receipt-info-item">
            <strong>New Due Date</strong>
            <span style="color: #28a745; font-weight: 700;">${data.new_due_date}</span>
          </div>
        </div>
      </div>

      ${data.notes ? `
        <div style="margin-top: 1.5rem; padding: 1rem; background: #f5f5f5; border-radius: 4px;">
          <strong style="display: block; margin-bottom: 0.5rem; color: #666;">Notes:</strong>
          <p style="margin: 0; color: #333;">${data.notes}</p>
        </div>
      ` : ''}

      ${refundStamp}

      <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px dashed #999; color: #666;">
        <p><strong>Thank you for your membership!</strong></p>
        <p style="font-size: 0.875rem;">Please keep this receipt for your records.</p>
      </div>
    </div>
  `;
}

function closeModal() {
  document.getElementById('receiptModal').classList.remove('show');
  if (window._reloadAfterReceipt) {
    // reset flag and reload page after user closes receipt
    window._reloadAfterReceipt = false;
    window.location.reload();
  }
}

function printReceipt() {
  window.print();
}

function toggleDropdown(button) {
  const dropdown = button.nextElementSibling;
  const allDropdowns = document.querySelectorAll('.dropdown-menu');
  
  allDropdowns.forEach(d => {
    if (d !== dropdown) {
      d.classList.remove('show');
    }
  });
  
  dropdown.classList.toggle('show');
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.action-dropdown')) {
    document.querySelectorAll('.dropdown-menu').forEach(d => {
      d.classList.remove('show');
    });
  }
});

// Display Laravel messages
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