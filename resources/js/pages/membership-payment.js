document.addEventListener('DOMContentLoaded', function() {
  // ========================================
  // MEMBERSHIP PAYMENT LOGIC
  // ========================================

  // Wait for payment-system.js to load helper functions
  const checkPageActive = () => {
    return typeof window.isPageActive === 'function' ? window.isPageActive('membership') : true;
  };

  // Read server-provided URLs from data attributes
  const configEl = document.getElementById('membershipPaymentConfig');
  const MEMBER_SEARCH_URL = configEl ? configEl.dataset.memberSearchUrl : '/api/members/search';
  const DUPLICATE_CHECK_URL = configEl ? configEl.dataset.duplicateCheckUrl : '/api/members/check-duplicate';

  let selectedMemberStatus = '';
  let selectedMemberDueDate = '';
  let selectedMemberIsStudent = false;
  let selectedBuddyDueDate = '';
  let isMembershipSubmitting = false;

  const paymentTypePills = document.querySelectorAll('.membership-pill');
  const paymentTypeInput = document.getElementById('paymentType');
  const memberSelectionSection = document.getElementById('memberSelectionSection');
  const newMemberSection = document.getElementById('newMemberSection');
  const memberSearch = document.getElementById('memberSearch');
  const memberId = document.getElementById('memberId');

  // Null safety check before adding listeners
  if (paymentTypePills && paymentTypePills.length) {
    paymentTypePills.forEach(pill => {
      pill.addEventListener('click', function() {
        // Guard: Only run if page is active
        if (!checkPageActive()) return;
        
        // Null safety checks
        if (!paymentTypeInput || !memberSelectionSection || !newMemberSection || 
            !memberSearch || !memberId) {
          console.warn('Membership form elements not found');
          return;
        }
        
        const type = this.dataset.type;
        paymentTypePills.forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        paymentTypeInput.value = type;
        
        const newMemberName = document.getElementById('newMemberName');
        const newMemberContact = document.getElementById('newMemberContact');
        const currentDueDate = document.getElementById('currentDueDate');
        const newDueDate = document.getElementById('newDueDate');
        
        if (type === 'new') {
          memberSelectionSection.classList.remove('member-section-visible');
          newMemberSection.classList.add('member-section-visible');
          memberSearch.removeAttribute('required');
          memberId.removeAttribute('required');
          if (newMemberName) newMemberName.setAttribute('required', 'required');
          if (newMemberContact) newMemberContact.setAttribute('required', 'required');
        } else {
          memberSelectionSection.classList.add('member-section-visible');
          newMemberSection.classList.remove('member-section-visible');
          memberSearch.setAttribute('required', 'required');
          if (newMemberName) newMemberName.removeAttribute('required');
          if (newMemberContact) newMemberContact.removeAttribute('required');
        }
        
        if (currentDueDate) currentDueDate.value = '';
        if (newDueDate) newDueDate.value = '';
        updatePlanDependentFields();
        enforcePlanRestrictions();
        calculateNewDueDate();
      });
    });
  }

  // Student Toggles with null safety
  const member1IsStudent = document.getElementById('member1IsStudent');
  const member1StudentLabel = document.getElementById('member1StudentLabel');
  const buddyIsStudent = document.getElementById('buddyIsStudent');
  const buddyStudentLabel = document.getElementById('buddyStudentLabel');
  
  if (member1IsStudent && member1StudentLabel) {
    member1IsStudent.addEventListener('change', function() {
      if (!checkPageActive()) return;
      member1StudentLabel.textContent = this.checked ? 'Yes' : 'No';
      const studentIdSection = document.getElementById('studentIdSection');
      if (studentIdSection) {
        studentIdSection.style.display = this.checked ? '' : 'none';
        const studentIdInput = document.getElementById('studentIdInput');
        if (studentIdInput && !this.checked) studentIdInput.value = '';
      }
      if (paymentTypeInput && paymentTypeInput.value === 'new') enforcePlanRestrictions();
    });
  }
  
  if (buddyIsStudent && buddyStudentLabel) {
    buddyIsStudent.addEventListener('change', function() {
      if (!checkPageActive()) return;
      buddyStudentLabel.textContent = this.checked ? 'Yes' : 'No';
    });
  }

  // Subscription Type Selection
  const planTypeCards = document.querySelectorAll('.plan-card');
  const planTypeInput = document.getElementById('planType');
  const amountInput = document.getElementById('amount');
  const additionalDaysInput = document.getElementById('additionalDays');

  if (planTypeCards && planTypeCards.length) {
    planTypeCards.forEach(card => {
      card.addEventListener('click', function() {
        // Guard: Only run if page is active
        if (!checkPageActive()) return;
        
        // Null safety checks
        if (!planTypeInput || !amountInput || !additionalDaysInput || !paymentTypeInput || !memberId) {
          console.warn('Membership plan elements not found');
          return;
        }
        
        const planType = this.dataset.plan;
        const requiresStudent = this.dataset.requiresStudent === 'true';
        const isNewPayment = paymentTypeInput.value === 'new';
        const hasMemberSelected = !!memberId.value;
        const studentWarning = document.getElementById('studentWarning');
        
        if (requiresStudent) {
          const member1Student = document.getElementById('member1IsStudent');
          if (isNewPayment) {
            if (member1Student && !member1Student.checked) {
              if (studentWarning) {
                studentWarning.classList.remove('hidden');
                setTimeout(() => { studentWarning.classList.add('hidden'); }, 4000);
              }
              ToastUtils.showWarning('Student rate requires student membership');
              return;
            }
          } else if (hasMemberSelected && !selectedMemberIsStudent) {
            if (studentWarning) {
              studentWarning.classList.remove('hidden');
              setTimeout(() => { studentWarning.classList.add('hidden'); }, 4000);
            }
            ToastUtils.showWarning('Student rate requires student membership');
            return;
          }
        }
        
        if (planType === 'Regular') {
          const member1Student = document.getElementById('member1IsStudent');
          if (isNewPayment) {
            if (member1Student && member1Student.checked) { 
              ToastUtils.showWarning('Students must use student rate'); 
              return; 
            }
          } else if (hasMemberSelected && selectedMemberIsStudent) {
            ToastUtils.showWarning('Students must use student rate'); 
            return;
          }
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
  }

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
      buddyDueDateSection.classList.remove('hidden');
    } else {
      buddySelectSection.classList.remove('buddy-visible');
      buddyDueDateSection.classList.add('hidden');
      document.getElementById('buddyMemberId').value = '';
      document.getElementById('buddyMemberSearch').value = '';
      document.getElementById('buddyCurrentDueDate').value = '';
      document.getElementById('buddyNewDueDate').value = '';
      selectedBuddyDueDate = '';
    }
    document.getElementById('studentWarning').classList.add('hidden');
  }

  function enforcePlanRestrictions() {
    const isNewPayment = paymentTypeInput.value === 'new';
    const regularCard = document.querySelector('[data-plan="Regular"]');
    const studentCard = document.querySelector('[data-plan="Student"]');
    if (!regularCard || !studentCard) return;

    if (!isNewPayment && !memberId.value) {
      regularCard.classList.remove('disabled-card'); 
      studentCard.classList.remove('disabled-card');
      document.getElementById('studentWarning').classList.add('hidden');
      return;
    }

    const isStudent = isNewPayment ? document.getElementById('member1IsStudent').checked : selectedMemberIsStudent;
    if (isStudent) {
      regularCard.classList.add('disabled-card');
      studentCard.classList.remove('disabled-card');
      if (planTypeInput.value === 'Regular') {
        planTypeCards.forEach(c => c.classList.remove('active'));
        studentCard.classList.add('active');
        planTypeInput.value = 'Student';
        amountInput.value = parseFloat(studentCard.dataset.price).toFixed(2);
        additionalDaysInput.value = studentCard.dataset.duration;
        calculateNewDueDate();
        ToastUtils.showInfo('Switched to student rate');
      }
    } else {
      studentCard.classList.add('disabled-card');
      regularCard.classList.remove('disabled-card');
      if (planTypeInput.value === 'Student') {
        planTypeCards.forEach(c => c.classList.remove('active'));
        regularCard.classList.add('active');
        planTypeInput.value = 'Regular';
        amountInput.value = parseFloat(regularCard.dataset.price).toFixed(2);
        additionalDaysInput.value = regularCard.dataset.duration;
        calculateNewDueDate();
        ToastUtils.showInfo('Switched to regular rate');
      }
    }
  }

  // Auto-switch payment type based on member status
  function syncPaymentTypeWithMemberStatus() {
    if (!paymentTypeInput || paymentTypeInput.value === 'new') return;
    const isActive = selectedMemberStatus && selectedMemberStatus.toLowerCase() === 'active';
    const targetType = isActive ? 'extension' : 'renewal';
    setPaymentType(targetType);
  }

  function setPaymentType(type) {
    paymentTypePills.forEach(p => p.classList.remove('active'));
    const targetPill = document.querySelector('.membership-pill[data-type="' + type + '"]');
    if (targetPill) targetPill.classList.add('active');
    paymentTypeInput.value = type;
  }

  // Member Autocomplete
  let memberSearchTimeout;
  if (memberSearch) {
    memberSearch.addEventListener('input', function() {
      // Guard: Only run if page is active
      if (!checkPageActive()) {
        clearTimeout(memberSearchTimeout);
        return;
      }
      
      const query = this.value.trim();
      const resultsContainer = document.getElementById('memberResults');
      
      // Null safety check
      if (!resultsContainer || !memberId) {
        console.warn('Member search elements not found');
        return;
      }
      
      clearTimeout(memberSearchTimeout);
      if (query.length < 2) { 
        resultsContainer.classList.add('hidden'); 
        return; 
      }
      
      memberSearchTimeout = setTimeout(() => {
        // Double check page is still active before fetch
        if (!checkPageActive()) return;
        
        fetch(MEMBER_SEARCH_URL + '?q=' + encodeURIComponent(query), {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
          // Guard: Check if page is still active after fetch completes
          if (!checkPageActive()) return;
          
          // Null safety: Ensure data is array
          if (!Array.isArray(data)) {
            console.warn('Invalid member search response format');
            return;
          }
          
          if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>';
            resultsContainer.classList.remove('hidden');
            return;
          }
          
          // Filter out null/undefined entries and map safely
          resultsContainer.innerHTML = data
            .filter(member => member && member.id && member.name) // Null safety filter
            .map(member => `
              <div class="autocomplete-item" 
                   data-id="${member.id || ''}" 
                   data-name="${member.name || ''}" 
                   data-due-date="${member.due_date || ''}" 
                   data-plan="${member.plan_type || ''}" 
                   data-status="${member.status || ''}" 
                   data-is-student="${member.is_student ? '1' : '0'}">
                <strong>${member.name}</strong>
                ${member.is_student ? '<span class="badge badge-info badge-inline">STUDENT</span>' : ''}
                <div class="autocomplete-item-meta">
                  Contact: ${member.contact || 'N/A'} | Plan: ${member.plan_type || 'N/A'} | Status: ${member.status || 'N/A'}
                  ${member.due_date ? '| Due: ' + new Date(member.due_date).toLocaleDateString() : '| No due date'}
                </div>
              </div>
            `).join('');
          resultsContainer.classList.remove('hidden');
          
          resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
              // Guard: Check page is still active when clicking
              if (!checkPageActive()) return;
              
              // Null safety checks
              if (!memberSearch || !memberId) return;
              
              memberSearch.value = this.dataset.name || '';
              memberId.value = this.dataset.id || '';
              selectedMemberStatus = this.dataset.status || '';
              selectedMemberDueDate = this.dataset.dueDate || '';
              selectedMemberIsStudent = this.dataset.isStudent === '1' || this.dataset.plan === 'Student';
              
              const memberIsStudentInput = document.getElementById('memberIsStudent');
              if (memberIsStudentInput) {
                memberIsStudentInput.value = selectedMemberIsStudent ? '1' : '0';
              }
              
              const dueDate = this.dataset.dueDate;
              const currentDueDateDisplay = document.getElementById('currentDueDate');
              if (currentDueDateDisplay) {
                currentDueDateDisplay.value = dueDate ? new Date(dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No due date';
              }
              
              resultsContainer.classList.add('hidden');
              syncPaymentTypeWithMemberStatus();
              updatePlanDependentFields();
              enforcePlanRestrictions();
              calculateNewDueDate();
            });
          });
        })
        .catch(error => { 
          console.warn('Member search error:', error); 
          ToastUtils.showError('Member search failed'); 
        });
      }, 300);
    });
  }

  // Buddy Autocomplete
  let buddySearchTimeout;
  const buddyMemberSearch = document.getElementById('buddyMemberSearch');
  buddyMemberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('buddyMemberResults');
    clearTimeout(buddySearchTimeout);
    if (query.length < 2) { resultsContainer.classList.add('hidden'); return; }
    buddySearchTimeout = setTimeout(() => {
      fetch(MEMBER_SEARCH_URL + '?q=' + encodeURIComponent(query), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        const filtered = data.filter(m => String(m.id) !== memberId.value);
        if (filtered.length === 0) { resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>'; resultsContainer.classList.remove('hidden'); return; }
        resultsContainer.innerHTML = filtered.map(member => `
          <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-status="${member.status}">
            <strong>${member.name}</strong>
            <div class="autocomplete-item-meta">Contact: ${member.contact || 'N/A'} | Status: ${member.status} ${member.due_date ? '| Due: ' + new Date(member.due_date).toLocaleDateString() : '| No due date'}</div>
          </div>
        `).join('');
        resultsContainer.classList.remove('hidden');
        resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
          item.addEventListener('click', function() {
            buddyMemberSearch.value = this.dataset.name;
            document.getElementById('buddyMemberId').value = this.dataset.id;
            selectedBuddyDueDate = this.dataset.dueDate;
            document.getElementById('buddyCurrentDueDate').value = this.dataset.dueDate ? new Date(this.dataset.dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'No due date';
            calculateNewDueDate();
            resultsContainer.classList.add('hidden');
          });
        });
      });
    }, 300);
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#memberResults')) document.getElementById('memberResults').classList.add('hidden');
    if (!e.target.closest('#buddyMemberSearch') && !e.target.closest('#buddyMemberResults')) document.getElementById('buddyMemberResults').classList.add('hidden');
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
    else { startDate = today; }
    const newDueDate = new Date(startDate); newDueDate.setDate(newDueDate.getDate() + duration);
    document.getElementById('newDueDate').value = newDueDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    if (planTypeInput.value === 'GymBuddy' && paymentType !== 'new') {
      const bcdText = document.getElementById('buddyCurrentDueDate').value;
      let buddyStart = today;
      if (buddyStart) { const bnd = new Date(buddyStart); bnd.setDate(bnd.getDate() + duration); document.getElementById('buddyNewDueDate').value = bnd.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }); }
    } else { document.getElementById('buddyNewDueDate').value = ''; }
  }

  // Form Submission
  function showValidationErrors(errors) {
    const errorList = errors.map(e => '• ' + e).join('\n');
    ToastUtils.showError('Payment Error:\n' + errorList);
  }

  async function checkDuplicateName(name) {
    try {
      const resp = await fetch(DUPLICATE_CHECK_URL + '?name=' + encodeURIComponent(name), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const data = await resp.json();
      return data.exists;
    } catch (err) {
      console.error('Duplicate check failed:', err);
      return false;
    }
  }

  const paymentForm = document.getElementById('membershipPaymentForm');
  paymentForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (isMembershipSubmitting) return;

    const errors = [];
    const paymentType = paymentTypeInput.value;
    const planType = planTypeInput.value;

    if (paymentType === 'new') {
      const nameEl = document.getElementById('newMemberName');
      if (!nameEl.value.trim()) errors.push('Member Full Name is required.');
      const contactEl = document.getElementById('newMemberContact');
      if (!contactEl.value.trim()) errors.push('Member Contact Number is required.');
      else if (!contactEl.value.match(/^(09\d{9}|\+639\d{9})$/)) errors.push('Member Contact Number is invalid (use 09XXXXXXXXX or +639XXXXXXXXX).');
      const ageEl = document.getElementById('newMemberAge');
      if (!ageEl.value || parseInt(ageEl.value) <= 0) errors.push('Member Age is required.');
      const sexEl = document.getElementById('newMemberSex');
      if (!sexEl.value) errors.push('Member Sex is required.');
      if (planType === 'GymBuddy') {
        const buddyNameEl = document.getElementById('buddyName');
        if (!buddyNameEl.value.trim()) errors.push("Buddy's Full Name is required.");
        const buddyContactEl = document.getElementById('buddyContact');
        if (!buddyContactEl.value.trim()) errors.push("Buddy's Contact Number is required.");
        else if (!buddyContactEl.value.match(/^(09\d{9}|\+639\d{9})$/)) errors.push("Buddy's Contact Number is invalid (use 09XXXXXXXXX or +639XXXXXXXXX).");
        const buddyAgeEl = document.getElementById('buddyAge');
        if (!buddyAgeEl.value || parseInt(buddyAgeEl.value) <= 0) errors.push("Buddy's Age is required.");
        const buddySexEl = document.getElementById('buddySex');
        if (!buddySexEl.value) errors.push("Buddy's Sex is required.");
      }
    } else {
      if (!memberId.value) errors.push('Please select a member.');
      if (planType === 'GymBuddy') {
        if (!document.getElementById('buddyMemberId').value) errors.push('Please select a gym buddy member.');
      }
    }

    if (!planType) errors.push('Please select a subscription plan.');
    const paymentMethodEl = document.getElementById('paymentMethod');
    if (!paymentMethodEl.value) errors.push('Please select a payment method.');
    const amountVal = parseFloat(amountInput.value);
    if (!amountVal || amountVal <= 0) errors.push('Payment amount is missing or invalid.');
    const newDueDateEl = document.getElementById('newDueDate');
    if (!newDueDateEl.value || newDueDateEl.value.trim() === '') errors.push('New Due Date could not be calculated. Please select a plan.');

    if (errors.length > 0) { showValidationErrors(errors); return; }

    if (paymentType === 'new') {
      const submitBtn = paymentForm.querySelector('button[type="submit"]');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Checking...'; }
      const memberName = document.getElementById('newMemberName').value.trim();
      const memberDuplicate = await checkDuplicateName(memberName);
      if (memberDuplicate) errors.push('A member named "' + memberName + '" already exists. Please use a different name or select the existing member via Renewal.');
      if (planType === 'GymBuddy') {
        const buddyName = document.getElementById('buddyName').value.trim();
        const buddyDuplicate = await checkDuplicateName(buddyName);
        if (buddyDuplicate) errors.push('A member named "' + buddyName + '" already exists as a buddy. Please use a different name or select the existing member.');
      }
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
      if (errors.length > 0) { showValidationErrors(errors); return; }
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
      buddyInfo = `<div class="confirm-row"><span class="confirm-label">Gym Buddy:</span><span class="confirm-value">${bn}</span></div>
        ${pt !== 'new' && bcd ? `<div class="confirm-row"><span class="confirm-label">Buddy Current Due:</span><span class="confirm-value">${bcd}</span></div>
        <div class="confirm-row"><span class="confirm-label">Buddy New Due:</span><span class="confirm-value success">${bnd}</span></div>` : ''}`;
    }
    const planLabels = { 'Regular': 'Regular Gym Rate', 'Student': 'Student Rate', 'GymBuddy': 'Gym Buddy Rate (2 persons)', 'ThreeMonths': '3 Months Membership', 'Session': 'Session Pass' };
    document.getElementById('confirmationDetails').innerHTML = `
      <div class="confirm-row"><span class="confirm-label">Member:</span><span class="confirm-value">${mn}</span></div>
      ${buddyInfo}
      <div class="confirm-row"><span class="confirm-label">Payment Type:</span><span class="confirm-value">${pt.toUpperCase()}</span></div>
      <div class="confirm-row"><span class="confirm-label">Plan:</span><span class="confirm-value">${planLabels[pl] || pl}</span></div>
      <div class="confirm-row"><span class="confirm-label">Amount:</span><span class="confirm-value">₱${parseFloat(amt).toFixed(2)}</span></div>
      <div class="confirm-row"><span class="confirm-label">Payment Method:</span><span class="confirm-value">${pm}</span></div>
      <div class="confirm-row"><span class="confirm-label">New Due Date:</span><span class="confirm-value success">${nd}</span></div>`;
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
        ToastUtils.showSuccess(data.message || 'Payment completed');
        form.reset();
        window._reloadAfterReceipt = true;
        setTimeout(() => { viewReceipt(data.payment.id); }, 300);
      } else {
        ToastUtils.showError(data.message || 'Payment processing failed');
        isMembershipSubmitting = false;
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('Payment processing error');
      isMembershipSubmitting = false;
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
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
    document.getElementById('buddyDueDateSection').classList.add('hidden');
    document.getElementById('member1IsStudent').checked = false; document.getElementById('member1StudentLabel').textContent = 'No';
    document.getElementById('buddyIsStudent').checked = false; document.getElementById('buddyStudentLabel').textContent = 'No';
    planTypeCards.forEach(c => c.classList.remove('active'));
    const defaultPlanCard = document.querySelector('[data-plan="Regular"]');
    defaultPlanCard.classList.add('active');
    planTypeInput.value = 'Regular';
    amountInput.value = parseFloat(defaultPlanCard.dataset.price).toFixed(2);
    additionalDaysInput.value = defaultPlanCard.dataset.duration;
    isMembershipSubmitting = false;
    const submitBtn = document.getElementById('submitPaymentBtn');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="mdi mdi-check-circle"></i> Process Payment'; }
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
    .catch(error => { console.error('Error:', error); ToastUtils.showError('Receipt loading failed'); receiptBody.innerHTML = '<div class="receipt-error-state"><i class="mdi mdi-alert-circle"></i><p>Failed to load receipt.</p></div>'; });
}

function generateReceiptHTML(data) {
  const planLabels = { 'Regular': 'Regular Gym Rate', 'Student': 'Student Rate', 'GymBuddy': 'Gym Buddy Rate', 'ThreeMonths': '3 Months Membership', 'Session': 'Session Pass', 'Monthly': 'Monthly Plan' };
  return '<div class="receipt-container"><div class="receipt-header"><h2>MEMBERSHIP PAYMENT RECEIPT</h2><p><strong>Abstrack Fitness Gym</strong></p><p>Toril, Davao Del Sur</p></div>' +
    '<div class="receipt-info-grid">' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Receipt Number</span><span class="receipt-info-value">#' + data.receipt_number + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Date & Time</span><span class="receipt-info-value">' + (data.formatted_date || new Date(data.created_at).toLocaleString()) + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Member Name</span><span class="receipt-info-value">' + (data.member_name || 'N/A') + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Contact</span><span class="receipt-info-value">' + (data.member_contact || data.contact || '') + '</span></div>' +
    (data.buddy_name ? '<div class="receipt-info-cell buddy"><span class="receipt-info-label">Gym Buddy</span><span class="receipt-info-value">' + data.buddy_name + '</span></div><div class="receipt-info-cell buddy"><span class="receipt-info-label">Buddy Contact</span><span class="receipt-info-value">' + (data.buddy_contact || 'N/A') + '</span></div>' : '') +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Payment Type</span><span class="receipt-info-value">' + (data.payment_type || '').toUpperCase() + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Payment Method</span><span class="receipt-info-value">' + (data.payment_method || 'N/A') + '</span></div></div>' +
    '<table class="receipt-table"><thead><tr><th>Description</th><th style="text-align: right;">Amount</th></tr></thead><tbody>' +
    (data.plan_type === 'GymBuddy' ?
      '<tr><td><strong>Gym Buddy Rate</strong><br><small style="color: #666;">Duration: ' + (data.duration || 'N/A') + ' days | 2 Persons</small><br><small style="color: #0d6efd;">Member 1: ' + data.member_name + '</small><br><small style="color: #0d6efd;">Member 2: ' + (data.buddy_name || 'N/A') + '</small></td><td style="text-align: right;"><span style="display: block;">₱' + parseFloat(data.amount || 0).toFixed(2) + '/person</span><strong style="display: block; margin-top: 4px;">Total: ₱' + (parseFloat(data.amount || 0) * 2).toFixed(2) + '</strong></td></tr>' :
      '<tr><td><strong>' + (planLabels[data.plan_type] || data.plan_type || 'Membership') + ' Plan</strong><br><small style="color: #666;">Duration: ' + (data.duration || 'N/A') + ' days</small></td><td style="text-align: right;">₱' + parseFloat(data.amount || 0).toFixed(2) + '</td></tr>') +
    '</tbody></table>' +
    '<div class="receipt-total"><div class="receipt-row grand"><span><strong>Total Paid:</strong></span><span><strong>₱' + (data.plan_type === 'GymBuddy' ? (parseFloat(data.amount || 0) * 2).toFixed(2) : parseFloat(data.amount || 0).toFixed(2)) + '</strong></span></div></div>' +
    '<div class="receipt-due-section"><div class="receipt-info-grid">' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">Previous Due Date</span><span class="receipt-info-value">' + (data.previous_due_date || 'N/A') + '</span></div>' +
    '<div class="receipt-info-cell"><span class="receipt-info-label">New Due Date</span><span class="receipt-info-value success">' + (data.new_due_date || 'N/A') + '</span></div></div></div>' +
    (data.notes ? '<div class="receipt-notes-section"><strong>Notes:</strong><p>' + data.notes + '</p></div>' : '') +
    '<div class="receipt-footer"><p><strong>Thank you for your membership!</strong></p><p>Please keep this receipt for your records.</p></div></div>';
}

function closeModal() {
  document.getElementById('receiptModal').classList.remove('show');
  if (window._reloadAfterReceipt) { window._reloadAfterReceipt = false; window.location.reload(); }
}
window.closeModal = closeModal;

function printReceipt() {
  const content = document.getElementById('receiptBody').innerHTML;
  const pw = window.open('', '_blank');
  pw.document.write('<!DOCTYPE html><html><head><title>Receipt</title><style>body{font-family:"Courier New",monospace}.receipt-container{max-width:600px;margin:0 auto;padding:20px}.receipt-header{text-align:center;margin-bottom:30px;padding-bottom:20px;border-bottom:2px dashed #333}.receipt-table{width:100%;border-collapse:collapse;margin:20px 0}.receipt-table th{background:#333;color:#fff;padding:10px;text-align:left}.receipt-table td{padding:10px;border-bottom:1px solid #ddd}.receipt-row{display:flex;justify-content:space-between;margin-bottom:8px}.receipt-total{margin-top:20px;padding-top:20px;border-top:2px solid #333}.receipt-footer{margin-top:30px;padding-top:20px;border-top:2px dashed #333;text-align:center}.receipt-info-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:20px}.receipt-info-cell{padding:10px;background:#f8f9fa;border-radius:4px}.receipt-info-cell.buddy{background:#e8f5e9;border:1px solid #a5d6a7}.receipt-info-label{display:block;font-size:.75rem;color:#666;margin-bottom:5px;font-weight:bold}.receipt-info-cell.buddy .receipt-info-label{color:#2e7d32}.receipt-info-value{display:block;font-weight:600}.receipt-info-value.success{color:#28a745}.receipt-due-section{margin-top:20px;padding-top:20px;border-top:1px dashed #ccc}.receipt-notes-section{margin-top:20px;padding:15px;background:#f5f5f5;border-radius:4px}.receipt-notes-section strong{display:block;margin-bottom:8px;color:#666}.receipt-notes-section p{margin:0;color:#333}.receipt-row.grand{font-size:1.3rem}</style></head><body>' + content + '</body></html>');
  pw.document.close(); pw.print();
}
window.printReceipt = printReceipt;
