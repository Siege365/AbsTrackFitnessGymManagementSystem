document.addEventListener('DOMContentLoaded', function() {
  // ========================================
  // PERSONAL TRAINING PAYMENT LOGIC
  // ========================================
  const ptPills = document.querySelectorAll('.pt-pill');
  const ptPaymentTypeInput = document.getElementById('ptPaymentType');
  const ptClientSearchSection = document.getElementById('ptClientSearchSection');
  const ptNewClientSection = document.getElementById('ptNewClientSection');
  const ptClientSearch = document.getElementById('ptClientSearch');
  const ptClientId = document.getElementById('ptClientId');
  const ptClientSource = document.getElementById('ptClientSource');
  const ptPlanCards = document.querySelectorAll('.pt-plan-card');
  const ptPlanTypeInput = document.getElementById('ptPlanType');
  const ptAmountInput = document.getElementById('ptAmount');
  const ptSessionSummary = document.getElementById('ptSessionSummary');
  let ptSelectedCustomer = null;
  let isPtSubmitting = false;

  // PT Type Pill Toggle
  if (ptPills.length) {
    ptPills.forEach(pill => {
      pill.addEventListener('click', function() {
        const type = this.dataset.type;
        ptPills.forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        ptPaymentTypeInput.value = type;
        if (type === 'new') {
          ptClientSearchSection.classList.add('hidden');
          ptNewClientSection.classList.remove('hidden');
          ptClientId.value = '';
          ptClientSource.value = 'walkin';
          ptSelectedCustomer = null;
        } else {
          ptClientSearchSection.classList.remove('hidden');
          ptNewClientSection.classList.add('hidden');
          ptClientSource.value = '';
        }
        updatePtSessionSummary();
      });
    });
  }

  // PT Plan Card Selection
  if (ptPlanCards.length) {
    ptPlanCards.forEach(card => {
      card.addEventListener('click', function() {
        ptPlanCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        ptPlanTypeInput.value = this.dataset.plan;
        ptAmountInput.value = parseFloat(this.dataset.price).toFixed(2);
        updatePtSessionSummary();
      });
    });
  }

  // PT Client Search Autocomplete
  let ptSearchTimeout;
  if (ptClientSearch) {
    ptClientSearch.addEventListener('input', function() {
      const query = this.value.trim();
      const resultsContainer = document.getElementById('ptClientResults');
      clearTimeout(ptSearchTimeout);
      ptClientId.value = '';
      ptClientSource.value = '';
      ptSelectedCustomer = null;
      if (query.length < 2) { resultsContainer.classList.add('hidden'); return; }
      ptSearchTimeout = setTimeout(() => {
        fetch('/sessions/customers/search?q=' + encodeURIComponent(query), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
          if (!data.length) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No customers found</div>';
            resultsContainer.classList.remove('hidden');
            return;
          }
          resultsContainer.innerHTML = data.map(c => `
            <div class="autocomplete-item" data-id="${c.id}" data-name="${c.name}" data-source="${c.source}" data-contact="${c.contact || ''}">
              <strong>${c.name}</strong>
              <div class="autocomplete-item-meta">${c.source === 'membership' ? 'Member' : 'Client'} · ${c.contact || 'No contact'}</div>
            </div>
          `).join('');
          resultsContainer.classList.remove('hidden');
          resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
              ptClientSearch.value = this.dataset.name;
              ptClientId.value = this.dataset.id;
              ptClientSource.value = this.dataset.source;
              ptSelectedCustomer = { id: this.dataset.id, name: this.dataset.name, source: this.dataset.source };
              resultsContainer.classList.add('hidden');
              updatePtSessionSummary();
            });
          });
        })
        .catch(() => { resultsContainer.classList.add('hidden'); });
      }, 300);
    });

    document.addEventListener('click', function(e) {
      if (!e.target.closest('#ptClientSearch') && !e.target.closest('#ptClientResults')) {
        document.getElementById('ptClientResults').classList.add('hidden');
      }
    });
  }

  function updatePtSessionSummary() {
    const trainer = document.getElementById('ptTrainerSelect')?.value || '';
    const date = document.getElementById('ptScheduleDate')?.value || '';
    const time = document.getElementById('ptScheduleTime')?.value || '';
    const parts = [];
    if (trainer) parts.push(trainer);
    if (date) parts.push(new Date(date + 'T00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    if (time) {
      const [h, m] = time.split(':');
      const hr = parseInt(h);
      parts.push((hr > 12 ? hr - 12 : hr) + ':' + m + (hr >= 12 ? ' PM' : ' AM'));
    }
    if (ptSessionSummary) ptSessionSummary.textContent = parts.length ? parts.join(' · ') : '—';
  }

  // Update summary when schedule fields change
  ['ptTrainerSelect', 'ptScheduleDate', 'ptScheduleTime'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', updatePtSessionSummary);
  });

  // PT Form Submission
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
        if (!ptClientId.value) errors.push('Please select a customer.');
      }

      if (!document.getElementById('ptTrainerSelect')?.value) errors.push('Please select a trainer.');
      if (!document.getElementById('ptScheduleDate')?.value) errors.push('Please select a date.');
      if (!document.getElementById('ptScheduleTime')?.value) errors.push('Please select a time.');
      if (!document.getElementById('ptPaymentMethod')?.value) errors.push('Please select a payment method.');

      if (errors.length) {
        ToastUtils.showError(errors.map(e => '• ' + e).join('\n'));
        return;
      }

      showPtConfirmation();
    });
  }

  function showPtConfirmation() {
    const ptType = ptPaymentTypeInput.value;
    const customerName = ptType === 'new' ? document.getElementById('ptCustomerName').value : ptClientSearch.value;
    const trainer = document.getElementById('ptTrainerSelect').value;
    const date = document.getElementById('ptScheduleDate').value;
    const time = document.getElementById('ptScheduleTime');
    const timeLabel = time.options[time.selectedIndex]?.text || time.value;
    const method = document.getElementById('ptPaymentMethod').value;
    const amount = ptAmountInput.value;
    const plan = ptPlanTypeInput.value;

    document.getElementById('ptConfirmationDetails').innerHTML = `
      <div class="confirm-row"><span class="confirm-label">Customer:</span><span class="confirm-value">${customerName}</span></div>
      <div class="confirm-row"><span class="confirm-label">Type:</span><span class="confirm-value">${ptType.toUpperCase()}</span></div>
      <div class="confirm-row"><span class="confirm-label">Trainer:</span><span class="confirm-value">${trainer}</span></div>
      <div class="confirm-row"><span class="confirm-label">Date:</span><span class="confirm-value">${new Date(date + 'T00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>
      <div class="confirm-row"><span class="confirm-label">Time:</span><span class="confirm-value">${timeLabel}</span></div>
      <div class="confirm-row"><span class="confirm-label">Plan:</span><span class="confirm-value">${plan}</span></div>
      <div class="confirm-row"><span class="confirm-label">Amount:</span><span class="confirm-value">₱${parseFloat(amount).toFixed(2)}</span></div>
      <div class="confirm-row"><span class="confirm-label">Payment:</span><span class="confirm-value">${method}</span></div>`;
    document.getElementById('ptConfirmationModal').classList.add('show');
  }

  window.closePtConfirmation = function() {
    document.getElementById('ptConfirmationModal').classList.remove('show');
  };

  // PT Confirm & Submit
  const ptConfirmBtn = document.getElementById('ptConfirmBtn');
  if (ptConfirmBtn) {
    ptConfirmBtn.addEventListener('click', function() {
      if (isPtSubmitting) return;
      isPtSubmitting = true;
      closePtConfirmation();

      const ptType = ptPaymentTypeInput.value;
      const isWalkIn = ptType === 'new';
      const submitBtn = document.getElementById('ptSubmitBtn');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...'; }

      const body = {
        _token: document.querySelector('#ptPaymentForm input[name="_token"]').value,
        customer_source: isWalkIn ? 'walkin' : (ptClientSource.value || 'membership'),
        trainer_name: document.getElementById('ptTrainerSelect').value,
        scheduled_date: document.getElementById('ptScheduleDate').value,
        scheduled_time: document.getElementById('ptScheduleTime').value,
        payment_type: document.getElementById('ptPaymentMethod').value,
        notes: document.getElementById('ptNotes')?.value || '',
      };

      if (isWalkIn) {
        body.customer_name = document.getElementById('ptCustomerName').value;
        body.customer_contact = document.getElementById('ptCustomerContact')?.value || '';
        body.customer_age = document.getElementById('ptCustomerAge')?.value || '';
        body.customer_sex = document.getElementById('ptCustomerSex')?.value || '';
      } else if (ptClientSource.value === 'membership') {
        body.membership_id = ptClientId.value;
      } else {
        body.client_id = ptClientId.value;
      }

      fetch('/sessions/pt-schedule', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': body._token },
        body: JSON.stringify(body)
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          ToastUtils.showSuccess(data.message || 'PT session booked successfully!');
          ptForm.reset();
          ptClientId.value = '';
          ptClientSource.value = '';
          ptSelectedCustomer = null;
          // Reset pills to renewal
          ptPills.forEach(p => p.classList.remove('active'));
          const renewalPill = document.querySelector('.pt-pill[data-type="renewal"]');
          if (renewalPill) renewalPill.classList.add('active');
          ptPaymentTypeInput.value = 'renewal';
          ptClientSearchSection.classList.remove('hidden');
          ptNewClientSection.classList.add('hidden');
          // Reset plan to first
          ptPlanCards.forEach(c => c.classList.remove('active'));
          if (ptPlanCards[0]) { ptPlanCards[0].classList.add('active'); ptPlanTypeInput.value = ptPlanCards[0].dataset.plan; ptAmountInput.value = parseFloat(ptPlanCards[0].dataset.price).toFixed(2); }
          updatePtSessionSummary();
        } else {
          const msg = data.errors ? Object.values(data.errors).flat().map(e => '• ' + e).join('\n') : (data.message || 'Booking failed.');
          ToastUtils.showError(msg);
        }
        isPtSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Book & Pay'; }
      })
      .catch(err => {
        console.error('PT booking error:', err);
        ToastUtils.showError('An error occurred while booking.');
        isPtSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Book & Pay'; }
      });
    });
  }

  // PT Clear Form
  const ptClearBtn = document.getElementById('ptClearBtn');
  if (ptClearBtn) {
    ptClearBtn.addEventListener('click', function() {
      ptForm.reset();
      ptClientId.value = '';
      ptClientSource.value = '';
      ptSelectedCustomer = null;
      ptPills.forEach(p => p.classList.remove('active'));
      const renewalPill = document.querySelector('.pt-pill[data-type="renewal"]');
      if (renewalPill) renewalPill.classList.add('active');
      ptPaymentTypeInput.value = 'renewal';
      ptClientSearchSection.classList.remove('hidden');
      ptNewClientSection.classList.add('hidden');
      ptPlanCards.forEach(c => c.classList.remove('active'));
      if (ptPlanCards[0]) { ptPlanCards[0].classList.add('active'); ptPlanTypeInput.value = ptPlanCards[0].dataset.plan; ptAmountInput.value = parseFloat(ptPlanCards[0].dataset.price).toFixed(2); }
      updatePtSessionSummary();
      isPtSubmitting = false;
      const submitBtn = document.getElementById('ptSubmitBtn');
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Book & Pay'; }
    });
  }

}); // DOMContentLoaded
