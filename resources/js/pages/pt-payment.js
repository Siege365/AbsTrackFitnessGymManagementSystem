document.addEventListener('DOMContentLoaded', function() {
  // ========================================
  // PERSONAL TRAINING PAYMENT LOGIC
  // ========================================

  const checkPageActive = () => {
    return typeof window.isPageActive === 'function' ? window.isPageActive('pt') : true;
  };

  const ptPills = document.querySelectorAll('.pt-pill');
  const ptPaymentTypeInput = document.getElementById('ptPaymentType');
  const ptClientSearchSection = document.getElementById('ptClientSearchSection');
  const ptNewClientSection = document.getElementById('ptNewClientSection');
  const ptClientSearch = document.getElementById('ptClientSearch');
  const ptClientId = document.getElementById('ptClientId');
  const ptClientSource = document.getElementById('ptClientSource');
  const ptHasPtClient = document.getElementById('ptHasPtClient');
  const ptPlanCards = document.querySelectorAll('.pt-plan-card');
  const ptPlanTypeInput = document.getElementById('ptPlanType');
  const ptAmountInput = document.getElementById('ptAmount');
  const ptCurrentDueDateInput = document.getElementById('ptCurrentDueDate');
  const ptNewDueDateInput = document.getElementById('ptNewDueDate');
  const ptAdditionalDaysInput = document.getElementById('ptAdditionalDays');

  let ptSelectedCustomer = null;
  let isPtSubmitting = false;

  // ── PT Type Pill Toggle ────────────────────────
  if (ptPills && ptPills.length) {
    ptPills.forEach(pill => {
      pill.addEventListener('click', function() {
        if (!checkPageActive()) return;
        const type = this.dataset.type;
        ptPills.forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        ptPaymentTypeInput.value = type;

        if (type === 'new') {
          ptClientSearchSection.classList.remove('client-section-visible');
          ptNewClientSection.classList.add('client-section-visible');
          ptClientId.value = '';
          ptClientSource.value = 'walkin';
          ptHasPtClient.value = '';
          ptSelectedCustomer = null;
          ptCurrentDueDateInput.value = '';
        } else {
          ptClientSearchSection.classList.add('client-section-visible');
          ptNewClientSection.classList.remove('client-section-visible');
          ptClientSource.value = '';
          ptHasPtClient.value = '';
        }
        updatePtDueDates();
      });
    });
  }

  // ── Auto-switch pills based on client status ───
  function syncPtPaymentType(customer) {
    if (!customer || !customer.has_pt_client) return;
    const ptDue = customer.pt_due_date;
    const isActive = ptDue && new Date(ptDue) >= new Date(new Date().toDateString());
    const targetType = isActive ? 'extension' : 'renewal';
    ptPills.forEach(p => p.classList.remove('active'));
    const target = document.querySelector(`.pt-pill[data-type="${targetType}"]`);
    if (target) target.classList.add('active');
    ptPaymentTypeInput.value = targetType;
  }

  // ── PT Plan Card Selection ─────────────────────
  if (ptPlanCards && ptPlanCards.length) {
    ptPlanCards.forEach(card => {
      card.addEventListener('click', function() {
        if (!checkPageActive()) return;
        ptPlanCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        ptPlanTypeInput.value = this.dataset.plan;
        ptAmountInput.value = parseFloat(this.dataset.price).toFixed(2);
        ptAdditionalDaysInput.value = this.dataset.duration;
        updatePtDueDates();
      });
    });
  }

  // ── Due Date Calculation ───────────────────────
  function updatePtDueDates() {
    const paymentType = ptPaymentTypeInput.value;
    const activePlan = document.querySelector('.pt-plan-card.active');
    const duration = activePlan ? parseInt(activePlan.dataset.duration) : 0;
    ptAdditionalDaysInput.value = duration;

    if (paymentType === 'new') {
      ptCurrentDueDateInput.value = '';
      const newDue = new Date();
      newDue.setDate(newDue.getDate() + duration);
      ptNewDueDateInput.value = formatDate(newDue);
      return;
    }

    if (!ptSelectedCustomer) {
      ptCurrentDueDateInput.value = '';
      ptNewDueDateInput.value = '';
      return;
    }

    const currentDue = ptSelectedCustomer.pt_due_date || ptSelectedCustomer.due_date;
    if (currentDue) {
      ptCurrentDueDateInput.value = formatDate(new Date(currentDue));
    } else {
      ptCurrentDueDateInput.value = 'No due date';
    }

    if (paymentType === 'extension' && currentDue) {
      const dueDate = new Date(currentDue);
      const isFuture = dueDate >= new Date(new Date().toDateString());
      if (isFuture) {
        dueDate.setDate(dueDate.getDate() + duration);
        ptNewDueDateInput.value = formatDate(dueDate);
      } else {
        const newDue = new Date();
        newDue.setDate(newDue.getDate() + duration);
        ptNewDueDateInput.value = formatDate(newDue);
      }
    } else {
      // renewal or no current due
      const newDue = new Date();
      newDue.setDate(newDue.getDate() + duration);
      ptNewDueDateInput.value = formatDate(newDue);
    }
  }

  function formatDate(d) {
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  // ── Client Search Autocomplete ─────────────────
  let ptSearchTimeout;
  if (ptClientSearch) {
    ptClientSearch.addEventListener('input', function() {
      if (!checkPageActive()) { clearTimeout(ptSearchTimeout); return; }
      const query = this.value.trim();
      const resultsContainer = document.getElementById('ptClientResults');
      clearTimeout(ptSearchTimeout);
      ptClientId.value = '';
      ptClientSource.value = '';
      ptHasPtClient.value = '';
      ptSelectedCustomer = null;
      ptCurrentDueDateInput.value = '';
      ptNewDueDateInput.value = '';

      if (query.length < 2) { resultsContainer.classList.add('hidden'); return; }

      ptSearchTimeout = setTimeout(() => {
        if (!checkPageActive()) return;
        fetch('/pt-payment/search-clients?q=' + encodeURIComponent(query), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
          if (!checkPageActive()) return;
          if (!Array.isArray(data) || !data.length) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No clients found</div>';
            resultsContainer.classList.remove('hidden');
            return;
          }

          resultsContainer.innerHTML = data.map(c => {
            const label = c.has_pt_client ? 'PT Client' : 'Member';
            const statusBadge = c.has_pt_client
              ? `<span style="color:${c.pt_status === 'Active' ? '#00d25b' : '#fc424a'}; font-size:0.75rem;"> · PT ${c.pt_status || 'N/A'}</span>`
              : `<span style="color:${c.membership_status === 'Active' ? '#00d25b' : '#fc424a'}; font-size:0.75rem;"> · ${c.membership_status || 'N/A'}</span>`;
            const dueInfo = c.pt_due_date
              ? `<br><small style="color:#999;">PT Due: ${new Date(c.pt_due_date).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'})}</small>`
              : '';
            return `<div class="autocomplete-item" data-id="${c.id}" data-name="${c.name}" data-source="${c.source}"
              data-contact="${c.contact || ''}" data-has-pt="${c.has_pt_client ? '1' : '0'}"
              data-pt-due="${c.pt_due_date || ''}" data-pt-status="${c.pt_status || ''}"
              data-age="${c.age || ''}" data-sex="${c.sex || ''}">
              <strong>${c.name}</strong>${statusBadge}
              <div class="autocomplete-item-meta">${label} · ${c.contact || 'No contact'}${dueInfo}</div>
            </div>`;
          }).join('');
          resultsContainer.classList.remove('hidden');

          resultsContainer.querySelectorAll('.autocomplete-item[data-id]').forEach(item => {
            item.addEventListener('click', function() {
              if (!checkPageActive()) return;
              ptClientSearch.value = this.dataset.name || '';
              ptClientId.value = this.dataset.id || '';
              ptClientSource.value = this.dataset.source || '';
              ptHasPtClient.value = this.dataset.hasPt || '';
              ptSelectedCustomer = {
                id: this.dataset.id,
                name: this.dataset.name,
                source: this.dataset.source,
                has_pt_client: this.dataset.hasPt === '1',
                pt_due_date: this.dataset.ptDue || null,
                pt_status: this.dataset.ptStatus || null,
                contact: this.dataset.contact || '',
                age: this.dataset.age || '',
                sex: this.dataset.sex || '',
              };
              resultsContainer.classList.add('hidden');
              syncPtPaymentType(ptSelectedCustomer);
              updatePtDueDates();
              ToastUtils.showInfo(`Selected: ${ptSelectedCustomer.name}`);
            });
          });
        })
        .catch(err => {
          console.warn('PT search error:', err);
          resultsContainer.classList.add('hidden');
          ToastUtils.showError('Client search failed');
        });
      }, 300);
    });

    document.addEventListener('click', function(e) {
      if (!e.target.closest('#ptClientSearch') && !e.target.closest('#ptClientResults')) {
        const r = document.getElementById('ptClientResults');
        if (r) r.classList.add('hidden');
      }
    });
  }

  // ── Form Submission ────────────────────────────
  const ptForm = document.getElementById('ptPaymentForm');
  if (ptForm) {
    ptForm.addEventListener('submit', function(e) {
      e.preventDefault();
      if (isPtSubmitting) return;

      const errors = [];
      const ptType = ptPaymentTypeInput.value;

      if (ptType === 'new') {
        if (!document.getElementById('ptCustomerName')?.value.trim()) errors.push('Customer name is required.');
      } else {
        if (!ptClientId.value) errors.push('Please select a client.');
        if (ptType === 'extension' && ptSelectedCustomer && ptSelectedCustomer.has_pt_client) {
          const ptStatus = ptSelectedCustomer.pt_status;
          if (ptStatus !== 'Active') {
            errors.push('Extension is only available for active PT clients. Use Renewal instead.');
          }
        }
      }

      if (!document.getElementById('ptPaymentMethod')?.value) errors.push('Please select a payment method.');
      const amountVal = parseFloat(ptAmountInput.value);
      if (!amountVal || amountVal <= 0) errors.push('Payment amount is missing or invalid.');
      if (!ptNewDueDateInput.value || ptNewDueDateInput.value === '—') errors.push('New Due Date could not be calculated. Please select a plan.');

      if (errors.length) {
        ToastUtils.showError('Payment Error:\n' + errors.map(e => '• ' + e).join('\n'));
        return;
      }

      showPtConfirmation();
    });
  }

  // ── Confirmation Modal ─────────────────────────
  function showPtConfirmation() {
    const ptType = ptPaymentTypeInput.value;
    const customerName = ptType === 'new' ? document.getElementById('ptCustomerName').value : ptClientSearch.value;
    const method = document.getElementById('ptPaymentMethod').value;
    const amount = ptAmountInput.value;
    const plan = ptPlanTypeInput.value;
    const currentDue = ptCurrentDueDateInput.value || 'N/A';
    const newDue = ptNewDueDateInput.value || 'N/A';
    const notes = document.getElementById('ptNotes')?.value || '';

    document.getElementById('ptConfirmationDetails').innerHTML = `
      <div class="confirm-row"><span class="confirm-label">Client:</span><span class="confirm-value">${customerName}</span></div>
      <div class="confirm-row"><span class="confirm-label">Payment Type:</span><span class="confirm-value">${ptType.toUpperCase()}</span></div>
      <div class="confirm-row"><span class="confirm-label">Plan:</span><span class="confirm-value">${plan}</span></div>
      <div class="confirm-row"><span class="confirm-label">Amount:</span><span class="confirm-value">₱${parseFloat(amount).toFixed(2)}</span></div>
      <div class="confirm-row"><span class="confirm-label">Payment Method:</span><span class="confirm-value">${method}</span></div>
      ${ptType !== 'new' ? `<div class="confirm-row"><span class="confirm-label">Current Due:</span><span class="confirm-value">${currentDue}</span></div>` : ''}
      <div class="confirm-row"><span class="confirm-label">New Due Date:</span><span class="confirm-value success">${newDue}</span></div>
      ${notes ? `<div class="confirm-row"><span class="confirm-label">Notes:</span><span class="confirm-value">${notes}</span></div>` : ''}`;
    document.getElementById('ptConfirmationModal').classList.add('show');
  }

  window.closePtConfirmation = function() {
    document.getElementById('ptConfirmationModal').classList.remove('show');
  };

  // ── Confirm & Submit ───────────────────────────
  const ptConfirmBtn = document.getElementById('ptConfirmBtn');
  if (ptConfirmBtn) {
    ptConfirmBtn.addEventListener('click', function() {
      if (isPtSubmitting) return;
      isPtSubmitting = true;
      closePtConfirmation();

      const ptType = ptPaymentTypeInput.value;
      const isNewWalkin = ptType === 'new';
      const submitBtn = document.getElementById('ptSubmitBtn');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...'; }

      const csrfToken = document.querySelector('#ptPaymentForm input[name="_token"]').value;
      const isNewPtClient = ptClientSource.value === 'membership' || isNewWalkin;

      const body = {
        _token: csrfToken,
        payment_type: ptType,
        plan_type: ptPlanTypeInput.value,
        payment_method: document.getElementById('ptPaymentMethod').value,
        amount: parseFloat(ptAmountInput.value),
        notes: document.getElementById('ptNotes')?.value || '',
        is_new_pt_client: isNewPtClient ? 1 : 0,
      };

      if (isNewWalkin && ptNewClientMatchedId) {
        // User typed in New Client but picked an existing member from autocomplete
        body.member_id = ptNewClientMatchedId.id;
        body.member_name = ptNewClientMatchedId.name;
        body.member_contact = ptNewClientMatchedId.contact || '';
        body.member_age = ptNewClientMatchedId.age || '';
        body.member_sex = ptNewClientMatchedId.sex || '';
        body.is_new_pt_client = ptNewClientMatchedId.source === 'membership' ? 1 : 0;
        body.is_walkin = 0;
      } else if (isNewWalkin) {
        body.member_name = document.getElementById('ptCustomerName').value;
        body.customer_name = body.member_name;
        body.customer_contact = document.getElementById('ptCustomerContact')?.value || '';
        body.customer_age = document.getElementById('ptCustomerAge')?.value || '';
        body.customer_sex = document.getElementById('ptCustomerSex')?.value || '';
        body.is_walkin = 1;
      } else {
        body.member_id = ptClientId.value;
        if (ptSelectedCustomer) {
          body.member_name = ptSelectedCustomer.name;
          body.member_contact = ptSelectedCustomer.contact || '';
          body.member_age = ptSelectedCustomer.age || '';
          body.member_sex = ptSelectedCustomer.sex || '';
        }
      }

      fetch('/pt-payment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(body)
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          ToastUtils.showSuccess(data.message || 'PT Payment processed successfully');
          window._ptReloadAfterReceipt = true;
          resetPtForm();
          setTimeout(() => { viewPtReceipt(data.payment.id); }, 300);
        } else {
          const msg = data.errors
            ? Object.values(data.errors).flat().map(e => '• ' + e).join('\n')
            : (data.message || 'Payment processing failed.');
          ToastUtils.showError(msg);
        }
        isPtSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
      })
      .catch(err => {
        console.error('PT payment error:', err);
        ToastUtils.showError('Payment processing failed');
        isPtSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
      });
    });
  }

  // ── Reset Form ─────────────────────────────────
  function resetPtForm() {
    ptForm.reset();
    ptClientId.value = '';
    ptClientSource.value = '';
    ptHasPtClient.value = '';
    ptSelectedCustomer = null;
    ptNewClientMatchedId = null;
    ptCurrentDueDateInput.value = '';
    ptNewDueDateInput.value = '';
    // Reset pills to renewal
    ptPills.forEach(p => p.classList.remove('active'));
    const renewalPill = document.querySelector('.pt-pill[data-type="renewal"]');
    if (renewalPill) renewalPill.classList.add('active');
    ptPaymentTypeInput.value = 'renewal';
    ptClientSearchSection.classList.add('client-section-visible');
    ptNewClientSection.classList.remove('client-section-visible');
    // Reset plan to first
    ptPlanCards.forEach(c => c.classList.remove('active'));
    if (ptPlanCards[0]) {
      ptPlanCards[0].classList.add('active');
      ptPlanTypeInput.value = ptPlanCards[0].dataset.plan;
      ptAmountInput.value = parseFloat(ptPlanCards[0].dataset.price).toFixed(2);
      ptAdditionalDaysInput.value = ptPlanCards[0].dataset.duration;
    }
    isPtSubmitting = false;
    const submitBtn = document.getElementById('ptSubmitBtn');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
  }

  // ── New Client Name Autocomplete ──────────────
  let ptNewClientSearchTimeout;
  let ptNewClientMatchedId = null; // tracks if user picked an existing member
  const ptCustomerNameInput = document.getElementById('ptCustomerName');
  const ptNewClientResultsContainer = document.getElementById('ptNewClientResults');

  if (ptCustomerNameInput && ptNewClientResultsContainer) {
    ptCustomerNameInput.addEventListener('input', function() {
      if (!checkPageActive()) { clearTimeout(ptNewClientSearchTimeout); return; }
      const query = this.value.trim();
      ptNewClientMatchedId = null; // reset match on any further typing
      clearTimeout(ptNewClientSearchTimeout);

      if (query.length < 2) { ptNewClientResultsContainer.classList.add('hidden'); return; }

      ptNewClientSearchTimeout = setTimeout(() => {
        if (!checkPageActive()) return;
        fetch('/pt-payment/search-clients?q=' + encodeURIComponent(query), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
          if (!checkPageActive()) return;
          if (!Array.isArray(data) || !data.length) {
            ptNewClientResultsContainer.innerHTML = '<div class="autocomplete-item">No existing members found — will create new</div>';
            ptNewClientResultsContainer.classList.remove('hidden');
            return;
          }
          ptNewClientResultsContainer.innerHTML = data.map(c => {
            const label = c.has_pt_client ? 'PT Client' : 'Member';
            const statusColor = (c.has_pt_client ? c.pt_status : c.membership_status) === 'Active' ? '#00d25b' : '#fc424a';
            const statusText = c.has_pt_client ? (c.pt_status || 'N/A') : (c.membership_status || 'N/A');
            return `<div class="autocomplete-item" data-id="${c.id}" data-name="${c.name}"
              data-source="${c.source}" data-contact="${c.contact || ''}"
              data-age="${c.age || ''}" data-sex="${c.sex || ''}"
              data-has-pt="${c.has_pt_client ? '1' : '0'}"
              data-pt-due="${c.pt_due_date || ''}" data-pt-status="${c.pt_status || ''}">
              <strong>${c.name}</strong><span style="color:${statusColor}; font-size:0.75rem;"> · ${statusText}</span>
              <div class="autocomplete-item-meta">${label} · ${c.contact || 'No contact'}</div>
            </div>`;
          }).join('');
          ptNewClientResultsContainer.classList.remove('hidden');

          ptNewClientResultsContainer.querySelectorAll('.autocomplete-item[data-id]').forEach(item => {
            item.addEventListener('click', function() {
              // Auto-fill the form fields
              ptCustomerNameInput.value = this.dataset.name || '';
              const contactInput = document.getElementById('ptCustomerContact');
              const ageInput = document.getElementById('ptCustomerAge');
              const sexInput = document.getElementById('ptCustomerSex');
              if (contactInput) contactInput.value = this.dataset.contact || '';
              if (ageInput) ageInput.value = this.dataset.age || '';
              if (sexInput) sexInput.value = this.dataset.sex || '';

              // Store matched ID so submission uses existing record
              ptNewClientMatchedId = {
                id: this.dataset.id,
                source: this.dataset.source,
                name: this.dataset.name,
                has_pt_client: this.dataset.hasPt === '1',
                pt_due_date: this.dataset.ptDue || null,
                pt_status: this.dataset.ptStatus || null,
                contact: this.dataset.contact || '',
                age: this.dataset.age || '',
                sex: this.dataset.sex || '',
              };

              ptNewClientResultsContainer.classList.add('hidden');
              ToastUtils.showInfo(`Matched existing: ${this.dataset.name}`);
            });
          });
        })
        .catch(() => {
          ptNewClientResultsContainer.classList.add('hidden');
        });
      }, 300);
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
      if (!e.target.closest('#ptCustomerName') && !e.target.closest('#ptNewClientResults')) {
        ptNewClientResultsContainer.classList.add('hidden');
      }
    });
  }

  // PT Clear Form
  const ptClearBtn = document.getElementById('ptClearBtn');
  if (ptClearBtn) {
    ptClearBtn.addEventListener('click', function() {
      resetPtForm();
      ToastUtils.showInfo('Form cleared');
    });
  }

}); // DOMContentLoaded

// ========================================
// PT RECEIPT FUNCTIONS (global scope)
// ========================================
function viewPtReceipt(paymentId) {
  const modal = document.getElementById('ptReceiptModal');
  const receiptBody = document.getElementById('ptReceiptBody');
  modal.classList.add('show');
  receiptBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
  fetch('/pt-payment/' + paymentId + '/receipt')
    .then(r => r.json())
    .then(data => {
      if (data.success === false) {
        ToastUtils.showError(data.message || 'Failed to load receipt');
        receiptBody.innerHTML = '<div class="receipt-error-state"><i class="mdi mdi-alert-circle"></i><p>Failed to load receipt.</p></div>';
        return;
      }
      receiptBody.innerHTML = generatePtReceiptHTML(data);
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('Receipt loading failed');
      receiptBody.innerHTML = '<div class="receipt-error-state"><i class="mdi mdi-alert-circle"></i><p>Failed to load receipt.</p></div>';
    });
}

function generatePtReceiptHTML(data) {
  let refundSection = '';
  if (data.is_refunded) {
    refundSection = '<div class="receipt-notes-section" style="background:#fff3cd;border:1px solid #ffc107;">' +
      '<strong style="color:#856404;">⚠ REFUNDED</strong>' +
      '<p>Amount: ₱' + parseFloat(data.refunded_amount || 0).toFixed(2) + '</p>' +
      (data.refund_reason ? '<p>Reason: ' + data.refund_reason + '</p>' : '') +
      (data.refunded_at ? '<p>Date: ' + data.refunded_at + '</p>' : '') +
      (data.refunded_by ? '<p>By: ' + data.refunded_by + '</p>' : '') +
      '</div>';
  }

  return '<div class="receipt-container"><div class="receipt-header"><h2>PT PAYMENT RECEIPT</h2><p><strong>Abstrack Fitness Gym</strong></p><p>Toril, Davao Del Sur</p></div>' +
    '<div class="receipt-info-grid">' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Receipt Number</span><span class="receipt-info-value">#' + data.receipt_number + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Date & Time</span><span class="receipt-info-value">' + (data.formatted_date || 'N/A') + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Client Name</span><span class="receipt-info-value">' + (data.member_name || 'N/A') + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Contact</span><span class="receipt-info-value">' + (data.member_contact || 'N/A') + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Payment Type</span><span class="receipt-info-value">' + (data.payment_type || '').toUpperCase() + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Payment Method</span><span class="receipt-info-value">' + (data.payment_method || 'N/A') + '</span></div></div>' +
    '<table class="receipt-table"><thead><tr><th>Description</th><th style="text-align: right;">Amount</th></tr></thead><tbody>' +
    '<tr><td><strong>' + (data.plan_type || 'PT') + ' Plan</strong><br><small style="color: #666;">Duration: ' + (data.duration || 'N/A') + ' days</small></td>' +
    '<td style="text-align: right;">₱' + parseFloat(data.amount || 0).toFixed(2) + '</td></tr>' +
    '</tbody></table>' +
    '<div class="receipt-total"><div class="receipt-row grand"><span><strong>Total Paid:</strong></span><span><strong>₱' + parseFloat(data.amount || 0).toFixed(2) + '</strong></span></div></div>' +
    '<div class="receipt-due-section"><div class="receipt-info-grid">' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Previous Due Date</span><span class="receipt-info-value">' + (data.previous_due_date || 'N/A') + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">New Due Date</span><span class="receipt-info-value success">' + (data.new_due_date || 'N/A') + '</span></div></div></div>' +
    (data.notes ? '<div class="receipt-notes-section"><strong>Notes:</strong><p>' + data.notes + '</p></div>' : '') +
    refundSection +
    '<div class="receipt-footer"><p><strong>Thank you for choosing our Personal Training!</strong></p><p>Please keep this receipt for your records.</p></div></div>';
}

function closePtReceiptModal() {
  document.getElementById('ptReceiptModal').classList.remove('show');
  if (window._ptReloadAfterReceipt) { window._ptReloadAfterReceipt = false; window.location.reload(); }
}
window.closePtReceiptModal = closePtReceiptModal;

function printPtReceipt() {
  const content = document.getElementById('ptReceiptBody').innerHTML;
  const pw = window.open('', '_blank');
  pw.document.write('<!DOCTYPE html><html><head><title>PT Receipt</title><style>body{font-family:"Courier New",monospace}.receipt-container{max-width:600px;margin:0 auto;padding:20px}.receipt-header{text-align:center;margin-bottom:30px;padding-bottom:20px;border-bottom:2px dashed #333}.receipt-table{width:100%;border-collapse:collapse;margin:20px 0}.receipt-table th{background:#333;color:#fff;padding:10px;text-align:left}.receipt-table td{padding:10px;border-bottom:1px solid #ddd}.receipt-row{display:flex;justify-content:space-between;margin-bottom:8px}.receipt-total{margin-top:20px;padding-top:20px;border-top:2px solid #333}.receipt-footer{margin-top:30px;padding-top:20px;border-top:2px dashed #333;text-align:center}.receipt-info-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:20px}.receipt-info-cell{padding:10px;background:#f8f9fa;border-radius:4px}.receipt-info-label{display:block;font-size:.75rem;color:#666;margin-bottom:5px;font-weight:bold}.receipt-info-value{display:block;font-weight:600}.receipt-info-value.success{color:#28a745}.receipt-due-section{margin-top:20px;padding-top:20px;border-top:1px dashed #ccc}.receipt-notes-section{margin-top:20px;padding:15px;background:#f5f5f5;border-radius:4px}.receipt-notes-section strong{display:block;margin-bottom:8px;color:#666}.receipt-notes-section p{margin:0;color:#333}.receipt-row.grand{font-size:1.3rem}</style></head><body>' + content + '</body></html>');
  pw.document.close(); pw.print();
}
window.printPtReceipt = printPtReceipt;
