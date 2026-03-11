// ========================================
// PAYMENT HISTORY - Page Controller
// ========================================

document.addEventListener('DOMContentLoaded', function () {
  const config = document.getElementById('paymentHistoryConfig');
  if (!config) {
    console.error('Payment history config element not found');
    return;
  }

  const CSRF_TOKEN = config.dataset.csrfToken;
  const bulkDeleteProductRoute = config.dataset.bulkDeleteProductRoute;
  const bulkDeleteMembershipRoute = config.dataset.bulkDeleteMembershipRoute;
  const bulkDeletePtRoute = config.dataset.bulkDeletePtRoute;
  const showRefundedDefault = config.dataset.showRefundedDefault === 'true';
  const flashSuccess = config.dataset.flashSuccess;
  const flashError = config.dataset.flashError;
  const flashErrors = config.dataset.flashErrors;
  const TAB_STORAGE_KEY = 'paymentHistoryActiveTab';

  let currentRefundType = null;
  let currentRefundId = null;
  let pendingDeleteAction = null;

  const confirmRefundButton = document.getElementById('confirmRefundBtn');

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeHtmlWithBreaks(value) {
    return escapeHtml(value).replace(/\n/g, '<br>');
  }

  function humanize(value, fallback = '—') {
    if (!value) {
      return fallback;
    }

    return String(value)
      .replace(/[_-]+/g, ' ')
      .replace(/\b\w/g, function (character) {
        return character.toUpperCase();
      });
  }

  function formatCurrency(value, fallback = '—') {
    if (value === null || value === undefined || value === '') {
      return fallback;
    }

    const amount = Number(value);
    if (!Number.isFinite(amount)) {
      return fallback;
    }

    return `₱${amount.toFixed(2)}`;
  }

  function formatDateTime(value, fallback = '—') {
    if (!value) {
      return fallback;
    }

    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
      return fallback;
    }

    return date.toLocaleString('en-US', {
      month: 'short',
      day: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
    });
  }

  function getPaymentTypeLabel(type) {
    if (type === 'product') return 'Product';
    if (type === 'membership') return 'Membership';
    if (type === 'pt') return 'Personal Training';
    return 'Payment';
  }

  function getDeleteRoute(type, id) {
    if (type === 'product') return `/payments/${id}`;
    if (type === 'membership') return `/membership-payment/${id}`;
    if (type === 'pt') return `/pt-payments/${id}`;
    return null;
  }

  function getRefundRoute(type, id) {
    if (type === 'product') return `/payments/${id}/refund`;
    if (type === 'membership') return `/membership-payment/${id}/refund`;
    if (type === 'pt') return `/pt-payments/${id}/refund`;
    return null;
  }

  function getReceiptRoute(type, id) {
    if (type === 'product') return `/payments/${id}/receipt-data`;
    if (type === 'membership') return `/membership-payment/${id}/receipt`;
    if (type === 'pt') return `/pt-payments/${id}/receipt`;
    return null;
  }

  function buildCsrfDeleteInputs() {
    return `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="_method" value="DELETE">`;
  }

  function renderLoadingState(message = 'Loading receipt...') {
    return `
      <div class="loading-spinner">
        <div class="spinner"></div>
        <p>${escapeHtml(message)}</p>
      </div>`;
  }

  function renderReceiptInfoCell(label, value, extraClass = '') {
    return `
      <div class="receipt-info-cell${extraClass ? ` ${extraClass}` : ''}">
        <span class="receipt-info-label">${escapeHtml(label)}</span>
        <span class="receipt-info-value">${escapeHtml(value || '—')}</span>
      </div>`;
  }

  function renderReceiptTable(headers, rowsHtml) {
    const headerHtml = headers.map(function (header) {
      return `<th${header.className ? ` class="${header.className}"` : ''}>${escapeHtml(header.label)}</th>`;
    }).join('');

    return `
      <table class="receipt-table">
        <thead>
          <tr>${headerHtml}</tr>
        </thead>
        <tbody>${rowsHtml}</tbody>
      </table>`;
  }

  function renderReceiptTotalRow(label, value, modifier = '') {
    return `
      <div class="receipt-total-row${modifier ? ` ${modifier}` : ''}">
        <strong>${escapeHtml(label)}</strong>
        <span>${escapeHtml(value || '—')}</span>
      </div>`;
  }

  function renderReceiptNotes(title, value, extraClass = '') {
    if (!value) {
      return '';
    }

    return `
      <div class="receipt-notes-section${extraClass ? ` ${extraClass}` : ''}">
        <strong>${escapeHtml(title)}</strong>
        <p>${escapeHtmlWithBreaks(value)}</p>
      </div>`;
  }

  function renderReceiptFooter(primaryText, secondaryText = '') {
    return `
      <div class="receipt-footer">
        <p><strong>${escapeHtml(primaryText)}</strong></p>
        ${secondaryText ? `<p>${escapeHtml(secondaryText)}</p>` : ''}
      </div>`;
  }

  function renderReceiptShell(options) {
    return `
      <div class="receipt-container">
        <div class="receipt-header">
          <h2>${escapeHtml(options.title)}</h2>
          <p><strong>Abstrack Fitness Gym</strong></p>
          <p>Toril, Davao Del Sur</p>
        </div>
        ${options.infoHtml}
        ${options.tableHtml}
        ${options.totalHtml}
        ${options.extraHtml || ''}
        ${renderReceiptFooter(options.footerTitle, options.footerText)}
      </div>`;
  }

  function buildProductReceiptTable(data) {
    const items = Array.isArray(data.items) ? data.items : [];

    if (!items.length) {
      return '<div class="receipt-empty-state">No items recorded for this payment.</div>';
    }

    const rows = items.map(function (item) {
      const quantity = Number(item.quantity || 0);
      const unitPrice = Number(item.unit_price || 0);
      const subtotal = item.subtotal ?? item.total_price ?? (unitPrice * quantity);

      return `
        <tr>
          <td>${escapeHtml(item.product_name || 'Product')}</td>
          <td class="text-right">${escapeHtml(String(quantity))}</td>
          <td class="text-right">${escapeHtml(formatCurrency(unitPrice))}</td>
          <td class="text-right">${escapeHtml(formatCurrency(subtotal))}</td>
        </tr>`;
    }).join('');

    return renderReceiptTable([
      { label: 'Item' },
      { label: 'Quantity', className: 'text-right' },
      { label: 'Unit Price', className: 'text-right' },
      { label: 'Subtotal', className: 'text-right' },
    ], rows);
  }

  function buildMembershipReceiptTable(data) {
    const planLabels = {
      Regular: 'Regular Gym Rate',
      Student: 'Student Rate',
      GymBuddy: 'Gym Buddy Rate',
      ThreeMonths: '3 Months Membership',
      Session: 'Session Pass',
      Monthly: 'Monthly Plan',
    };

    const amount = Number(data.amount || 0);
    const total = data.plan_type === 'GymBuddy' ? amount * 2 : amount;
    const duration = data.duration ? `${data.duration} day${Number(data.duration) === 1 ? '' : 's'}` : 'N/A';

    const description = data.plan_type === 'GymBuddy'
      ? `<strong>Gym Buddy Rate</strong><br><small style="color: #666;">Duration: ${escapeHtml(duration)} | 2 Persons</small><br><small style="color: #0d6efd;">Member 1: ${escapeHtml(data.member_name || 'N/A')}</small><br><small style="color: #0d6efd;">Member 2: ${escapeHtml(data.buddy_name || 'N/A')}</small>`
      : `<strong>${escapeHtml(planLabels[data.plan_type] || data.plan_type || 'Membership')} Plan</strong><br><small style="color: #666;">Duration: ${escapeHtml(duration)}</small>`;

    return renderReceiptTable([
      { label: 'Description' },
      { label: 'Amount', className: 'text-right' },
    ], `
      <tr>
        <td>${description}</td>
        <td class="text-right">${escapeHtml(formatCurrency(total))}</td>
      </tr>`);
  }

  function buildPTReceiptTable(data) {
    const details = [
      data.plan_duration_days ? `${data.plan_duration_days} ${Number(data.plan_duration_days) === 1 ? 'session' : 'sessions'}` : 'Legacy record',
      data.trainer_name ? `Trainer: ${data.trainer_name}` : '',
      data.status ? `Status: ${humanize(data.status)}` : '',
      data.session_schedule ? `Schedule: ${data.session_schedule}` : '',
    ].filter(Boolean).join(' | ');

    return renderReceiptTable([
      { label: 'Description' },
      { label: 'Amount', className: 'text-right' },
    ], `
      <tr>
        <td><strong>${escapeHtml(data.plan_name || 'Personal Training')}</strong>${details ? `<br><small style="color: #666;">${escapeHtml(details)}</small>` : ''}</td>
        <td class="text-right">${escapeHtml(formatCurrency(data.amount))}</td>
      </tr>`);
  }

  function generateOriginalReceipt(type, data) {
    if (type === 'product') {
      return renderReceiptShell({
        title: 'Receipt',
        infoHtml: `
          <div class="receipt-info-grid">
            ${renderReceiptInfoCell('Receipt Number', data.receipt_number ? `#${data.receipt_number}` : '—')}
            ${renderReceiptInfoCell('Date & Time', data.formatted_date || formatDateTime(data.created_at))}
            ${renderReceiptInfoCell('Customer Name', data.customer_name || 'N/A')}
            ${renderReceiptInfoCell('Cashier', data.cashier_name || 'Admin')}
            ${renderReceiptInfoCell('Payment Method', data.payment_method || 'N/A')}
          </div>`,
        tableHtml: buildProductReceiptTable(data),
        totalHtml: `
          <div class="receipt-total">
            ${renderReceiptTotalRow('Subtotal:', formatCurrency(data.total_amount))}
            ${renderReceiptTotalRow('Total:', formatCurrency(data.total_amount), 'grand-total')}
            ${renderReceiptTotalRow('Paid Amount:', formatCurrency(data.paid_amount), 'receipt-paid-section')}
            ${renderReceiptTotalRow('Change:', formatCurrency(data.return_amount))}
          </div>`,
        footerTitle: 'Thank you for your purchase!',
        footerText: 'Please come again!',
      });
    }

    if (type === 'membership') {
      const total = data.plan_type === 'GymBuddy'
        ? Number(data.amount || 0) * 2
        : Number(data.amount || 0);

      const extraSections = [
        `
          <div class="receipt-info-grid receipt-secondary-grid">
            ${renderReceiptInfoCell('Previous Due Date', data.previous_due_date || 'N/A')}
            ${renderReceiptInfoCell('New Due Date', data.new_due_date || 'N/A', 'success')}
          </div>`,
        renderReceiptNotes('Notes', data.notes),
      ].filter(Boolean).join('');

      return renderReceiptShell({
        title: 'Membership Payment Receipt',
        infoHtml: `
          <div class="receipt-info-grid">
            ${renderReceiptInfoCell('Receipt Number', data.receipt_number ? `#${data.receipt_number}` : '—')}
            ${renderReceiptInfoCell('Date & Time', data.formatted_date || formatDateTime(data.created_at))}
            ${renderReceiptInfoCell('Member Name', data.member_name || 'N/A')}
            ${renderReceiptInfoCell('Contact', data.member_contact || data.contact || 'N/A')}
            ${data.buddy_name ? renderReceiptInfoCell('Gym Buddy', data.buddy_name, 'buddy') : ''}
            ${data.buddy_name ? renderReceiptInfoCell('Buddy Contact', data.buddy_contact || 'N/A', 'buddy') : ''}
            ${renderReceiptInfoCell('Payment Type', humanize(data.payment_type, 'N/A'))}
            ${renderReceiptInfoCell('Payment Method', data.payment_method || 'N/A')}
            ${renderReceiptInfoCell('Cashier', data.processed_by || 'Admin')}
          </div>`,
        tableHtml: buildMembershipReceiptTable(data),
        totalHtml: `
          <div class="receipt-total">
            ${renderReceiptTotalRow('Total Paid:', formatCurrency(total), 'grand-total')}
          </div>`,
        extraHtml: extraSections,
        footerTitle: 'Thank you for your membership!',
        footerText: 'Please keep this receipt for your records.',
      });
    }

    return renderReceiptShell({
      title: 'Personal Training Receipt',
      infoHtml: `
        <div class="receipt-info-grid">
          ${renderReceiptInfoCell('Receipt Number', data.receipt_number ? `#${data.receipt_number}` : '—')}
          ${renderReceiptInfoCell('Date & Time', data.formatted_date || formatDateTime(data.created_at))}
          ${renderReceiptInfoCell('Customer Name', data.customer_name || 'N/A')}
          ${renderReceiptInfoCell('Customer Type', humanize(data.customer_source, 'Walkin'))}
          ${renderReceiptInfoCell('Trainer', data.trainer_name || 'N/A')}
          ${renderReceiptInfoCell('Session Schedule', data.session_schedule || 'N/A')}
          ${renderReceiptInfoCell('Payment Method', data.payment_method || 'N/A')}
          ${renderReceiptInfoCell('Cashier', data.processed_by || 'Admin')}
          ${data.customer_contact ? renderReceiptInfoCell('Contact', data.customer_contact) : ''}
        </div>`,
      tableHtml: buildPTReceiptTable(data),
      totalHtml: `
        <div class="receipt-total">
          ${renderReceiptTotalRow('Total Paid:', formatCurrency(data.amount), 'grand-total')}
          ${renderReceiptTotalRow('Paid Amount:', formatCurrency(data.paid_amount), 'receipt-paid-section')}
          ${renderReceiptTotalRow('Change:', formatCurrency(data.return_amount))}
        </div>`,
      extraHtml: renderReceiptNotes('Notes', data.notes),
      footerTitle: 'PT payment recorded successfully!',
      footerText: 'Please keep this receipt for verification.',
    });
  }

  function generateRefundReceipt(type, paymentData, refundData) {
    const payment = refundData?.payment || paymentData;
    const originalAmount = payment.total_amount ?? payment.amount;
    const refundedAmount = payment.refunded_amount ?? originalAmount;

    let tableHtml = '';
    if (type === 'product') {
      tableHtml = buildProductReceiptTable(payment);
    } else if (type === 'membership') {
      tableHtml = buildMembershipReceiptTable(payment);
    } else {
      tableHtml = buildPTReceiptTable(payment);
    }

    const extraSections = [
      renderReceiptNotes('Refund Reason', payment.refund_reason),
      '<div class="receipt-refund-stamp"><h3>Refunded</h3></div>',
    ].join('');

    return renderReceiptShell({
      title: 'Refund Receipt',
      infoHtml: `
        <div class="receipt-info-grid">
          ${renderReceiptInfoCell('Receipt Number', payment.receipt_number ? `#${payment.receipt_number}` : '—')}
          ${renderReceiptInfoCell('Customer', payment.customer_name || payment.member_name || 'N/A')}
          ${renderReceiptInfoCell('Original Payment Date', payment.formatted_date || formatDateTime(payment.created_at))}
          ${renderReceiptInfoCell('Refunded At', formatDateTime(payment.refunded_at))}
          ${renderReceiptInfoCell('Payment Type', `${getPaymentTypeLabel(type)} Payment`)}
          ${renderReceiptInfoCell('Processed By', payment.refunded_by || 'Admin')}
        </div>`,
      tableHtml: tableHtml,
      totalHtml: `
        <div class="receipt-total">
          ${renderReceiptTotalRow('Original Amount:', formatCurrency(originalAmount))}
          ${renderReceiptTotalRow('Refunded Amount:', formatCurrency(refundedAmount), 'grand-total refund-total')}
          ${renderReceiptTotalRow('Refund Status:', humanize(payment.refund_status, 'Full'))}
        </div>`,
      extraHtml: extraSections,
      footerTitle: 'This is a system-generated refund receipt.',
      footerText: 'Keep this copy for auditing and customer support.',
    });
  }

  function getReceiptPrintStyles() {
    return `
      body { font-family: "Courier New", monospace; }
      .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; background: #fff; color: #333; }
      .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
      .receipt-header h2 { margin: 0 0 10px; font-size: 30px; color: #000; }
      .receipt-header p { margin: 5px 0; color: #666; }
      .receipt-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px; }
      .receipt-info-cell { padding: 10px; background: #f8f9fa; border-radius: 4px; }
      .receipt-info-cell.buddy { background: #e8f5e9; border: 1px solid #a5d6a7; }
      .receipt-info-label { display: block; font-size: .75rem; color: #666; margin-bottom: 5px; font-weight: bold; text-transform: uppercase; }
      .receipt-info-value { display: block; font-weight: 600; color: #333; }
      .receipt-info-cell.success .receipt-info-value { color: #28a745; }
      .receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
      .receipt-table th { background: #333; color: #fff; padding: 10px; text-align: left; }
      .receipt-table td { padding: 10px; border-bottom: 1px solid #ddd; }
      .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
      .receipt-total-row { display: flex; justify-content: flex-end; margin-bottom: 8px; }
      .receipt-total-row strong { width: 200px; text-align: right; }
      .receipt-total-row span { width: 150px; text-align: right; font-weight: 700; }
      .receipt-total-row.grand-total { font-size: 1.3rem; }
      .receipt-total-row.refund-total strong, .receipt-total-row.refund-total span { color: #dc3545; }
      .receipt-notes-section { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px; }
      .receipt-notes-section strong { display: block; margin-bottom: 8px; color: #666; }
      .receipt-notes-section p { margin: 0; color: #333; }
      .receipt-refund-stamp { text-align: center; margin-top: 20px; padding: 14px; background: #fff3cd; border: 2px dashed #ffc107; border-radius: 8px; }
      .receipt-refund-stamp h3 { margin: 0; color: #856404; text-transform: uppercase; }
      .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; }
      .receipt-footer p { margin: 5px 0; }
      .receipt-empty-state { padding: 18px; border: 1px dashed #999; text-align: center; color: #666; }
      .text-right { text-align: right; }
    `;
  }

  function resetRefundButton() {
    if (!confirmRefundButton) {
      return;
    }

    confirmRefundButton.disabled = false;
    confirmRefundButton.innerHTML = '<i class="mdi mdi-cash-refund"></i><span>Process Refund</span>';
  }

  function showDeleteModal(title, message) {
    const titleEl = document.getElementById('deleteModalTitle');
    const textEl = document.getElementById('deleteConfirmText');

    if (titleEl) {
      titleEl.textContent = title;
    }

    if (textEl) {
      textEl.textContent = message;
    }

    document.getElementById('deleteConfirmModal')?.classList.add('show');
  }

  function submitBulkDeleteRequest(route, ids) {
    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('_method', 'DELETE');
    ids.forEach(function (id) {
      formData.append('ids[]', id);
    });

    return fetch(route, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    }).then(function (response) {
      if (!response.ok) {
        throw new Error(`Delete request failed with status ${response.status}`);
      }

      return response.text();
    });
  }

  function initializePageToggle() {
    const pageToggleButtons = document.querySelectorAll('.page-toggle-btn');
    const pageMap = {
      membership: 'membershipPage',
      pt: 'ptPage',
      product: 'productPage',
    };
    const pageOrder = ['membership', 'pt', 'product'];

    function persistActiveTab(tab) {
      try {
        sessionStorage.setItem(TAB_STORAGE_KEY, tab);
      } catch (error) {
        console.warn('Unable to store active payment history tab', error);
      }
    }

    function syncTabUrl(tab) {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tab);
      window.history.replaceState({}, '', url.toString());
    }

    function setActiveTab(targetPage, animate) {
      const currentPage = document.querySelector('.page-toggle-btn.active')?.dataset.page || 'membership';
      const currentPanel = document.getElementById(pageMap[currentPage]);
      const targetPanel = document.getElementById(pageMap[targetPage]);

      if (!targetPanel) {
        return;
      }

      if (!animate || currentPage === targetPage) {
        pageToggleButtons.forEach(function (button) {
          button.classList.toggle('active', button.dataset.page === targetPage);
        });

        document.querySelectorAll('.page-panel').forEach(function (panel) {
          panel.classList.remove('active', 'slide-out-left', 'slide-out-right', 'slide-in-left', 'slide-in-right');
        });

        targetPanel.classList.add('active');
        persistActiveTab(targetPage);
        syncTabUrl(targetPage);
        return;
      }

      const currentIndex = pageOrder.indexOf(currentPage);
      const targetIndex = pageOrder.indexOf(targetPage);
      const goingRight = targetIndex > currentIndex;

      currentPanel?.classList.remove('active');
      currentPanel?.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');

      window.setTimeout(function () {
        currentPanel?.classList.remove('slide-out-left', 'slide-out-right');
        targetPanel.classList.add('active', goingRight ? 'slide-in-right' : 'slide-in-left');
        window.setTimeout(function () {
          targetPanel.classList.remove('slide-in-right', 'slide-in-left');
        }, 400);

        pageToggleButtons.forEach(function (button) {
          button.classList.toggle('active', button.dataset.page === targetPage);
        });
      }, 250);

      persistActiveTab(targetPage);
      syncTabUrl(targetPage);
    }

    pageToggleButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        setActiveTab(this.dataset.page, true);
      });
    });

    const tabFromUrl = new URLSearchParams(window.location.search).get('tab');
    let initialTab = tabFromUrl && pageMap[tabFromUrl] ? tabFromUrl : 'membership';

    if (!tabFromUrl) {
      try {
        const storedTab = sessionStorage.getItem(TAB_STORAGE_KEY);
        if (storedTab && pageMap[storedTab]) {
          initialTab = storedTab;
        }
      } catch (error) {
        console.warn('Unable to restore active payment history tab', error);
      }
    }

    setActiveTab(initialTab, false);
  }

  function initializeRefundedToggle() {
    const refundedCard = document.getElementById('refundedPaymentsCard');
    const toggleButtons = document.querySelectorAll('[data-refund-visibility]');

    if (!refundedCard || !toggleButtons.length) {
      return;
    }

    function applyState(isVisible) {
      refundedCard.classList.toggle('is-hidden', !isVisible);
      refundedCard.classList.toggle('is-visible', isVisible);

      toggleButtons.forEach(function (button) {
        const shouldBeActive = button.dataset.refundVisibility === (isVisible ? 'show' : 'hide');
        button.classList.toggle('active', shouldBeActive);
        button.setAttribute('aria-pressed', shouldBeActive ? 'true' : 'false');
      });
    }

    applyState(showRefundedDefault);

    toggleButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        const isVisible = this.dataset.refundVisibility === 'show';
        applyState(isVisible);

        const url = new URL(window.location.href);
        if (isVisible) {
          url.searchParams.set('show_refunded', '1');
          window.history.replaceState({}, '', url.toString());
          window.setTimeout(function () {
            refundedCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }, 80);
        } else {
          url.searchParams.delete('show_refunded');
          window.history.replaceState({}, '', url.toString());
        }
      });
    });
  }

  function initializeFilterAccordion() {
    document.querySelectorAll('[data-filter-section]').forEach(function (header) {
      header.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        this.closest('.filter-section')?.classList.toggle('active');
      });
    });
  }

  function initializeCheckboxes() {
    const groups = [
      ['product', 'selectAllProduct'],
      ['membership', 'selectAllMembership'],
      ['pt', 'selectAllPT'],
      ['refund', 'selectAllRefund'],
    ];

    groups.forEach(function ([type, selectAllId]) {
      const selectAll = document.getElementById(selectAllId);
      const checkboxes = document.querySelectorAll(`.${type}-checkbox`);

      selectAll?.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) {
          checkbox.checked = selectAll.checked;
        });
        updateDeleteButton(type);
      });

      checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
          updateDeleteButton(type);
        });
      });
    });
  }

  function updateDeleteButton(type) {
    const count = document.querySelectorAll(`.${type}-checkbox:checked`).length;
    const countSpan = document.getElementById(`${type}Count`);
    const deleteButton = document.getElementById(`delete${type.charAt(0).toUpperCase() + type.slice(1)}Btn`);

    if (countSpan) {
      countSpan.textContent = String(count);
    }

    if (deleteButton) {
      deleteButton.disabled = count === 0;
    }
  }

  initializeCheckboxes();
  initializePageToggle();
  initializeRefundedToggle();
  initializeFilterAccordion();

  if (flashSuccess) {
    ToastUtils.showSuccess(flashSuccess);
  }
  if (flashError) {
    ToastUtils.showError(flashError);
  }
  if (flashErrors) {
    ToastUtils.showError(flashErrors);
  }

  window.bulkDeleteProducts = function () {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (!checked.length) {
      ToastUtils.showWarning('Please select at least one payment to delete');
      return;
    }

    pendingDeleteAction = function () {
      const form = document.getElementById('bulkDeleteProductForm');
      form.innerHTML = buildCsrfDeleteInputs();
      checked.forEach(function (checkbox) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
      });
      form.submit();
    };

    showDeleteModal(
      'Confirm Bulk Delete',
      `Are you sure you want to delete ${checked.length} selected product payment${checked.length === 1 ? '' : 's'}? This action cannot be undone.`
    );
  };

  window.bulkDeleteMemberships = function () {
    const checked = document.querySelectorAll('.membership-checkbox:checked');
    if (!checked.length) {
      ToastUtils.showWarning('Please select at least one payment to delete');
      return;
    }

    pendingDeleteAction = function () {
      const form = document.getElementById('bulkDeleteMembershipForm');
      form.innerHTML = buildCsrfDeleteInputs();
      checked.forEach(function (checkbox) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
      });
      form.submit();
    };

    showDeleteModal(
      'Confirm Bulk Delete',
      `Are you sure you want to delete ${checked.length} selected membership payment${checked.length === 1 ? '' : 's'}? This action cannot be undone.`
    );
  };

  window.bulkDeletePTs = function () {
    const checked = document.querySelectorAll('.pt-checkbox:checked');
    if (!checked.length) {
      ToastUtils.showWarning('Please select at least one PT payment to delete');
      return;
    }

    pendingDeleteAction = function () {
      const form = document.getElementById('bulkDeletePTForm');
      form.innerHTML = buildCsrfDeleteInputs();
      checked.forEach(function (checkbox) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
      });
      form.submit();
    };

    showDeleteModal(
      'Confirm Bulk Delete',
      `Are you sure you want to delete ${checked.length} selected PT payment${checked.length === 1 ? '' : 's'}? This action cannot be undone.`
    );
  };

  window.bulkDeleteRefunds = function () {
    const checked = document.querySelectorAll('.refund-checkbox:checked');
    if (!checked.length) {
      ToastUtils.showWarning('No refunds selected for deletion');
      return;
    }

    pendingDeleteAction = function () {
      const groupedIds = { product: [], membership: [], pt: [] };
      checked.forEach(function (checkbox) {
        if (groupedIds[checkbox.dataset.type]) {
          groupedIds[checkbox.dataset.type].push(checkbox.value);
        }
      });

      const requests = [];
      if (groupedIds.product.length) requests.push(submitBulkDeleteRequest(bulkDeleteProductRoute, groupedIds.product));
      if (groupedIds.membership.length) requests.push(submitBulkDeleteRequest(bulkDeleteMembershipRoute, groupedIds.membership));
      if (groupedIds.pt.length) requests.push(submitBulkDeleteRequest(bulkDeletePtRoute, groupedIds.pt));

      Promise.all(requests)
        .then(function () {
          window.location.reload();
        })
        .catch(function (error) {
          console.error(error);
          ToastUtils.showError('Failed to delete selected refund records');
        });
    };

    showDeleteModal(
      'Confirm Bulk Delete',
      `Are you sure you want to delete ${checked.length} selected refunded payment record${checked.length === 1 ? '' : 's'}? This action cannot be undone.`
    );
  };

  window.confirmDeleteSingle = function (type, id, name = '') {
    const route = getDeleteRoute(type, id);
    if (!route) {
      ToastUtils.showError('Unable to determine delete route');
      return;
    }

    pendingDeleteAction = function () {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = route;
      form.innerHTML = buildCsrfDeleteInputs();
      document.body.appendChild(form);
      form.submit();
    };

    const paymentLabel = `${getPaymentTypeLabel(type).toLowerCase()} payment`;
    const message = name
      ? `Are you sure you want to delete the ${paymentLabel} for ${name}?`
      : `Are you sure you want to delete this ${paymentLabel}?`;

    showDeleteModal('Confirm Delete', message);
  };

  window.closeDeleteModal = function () {
    document.getElementById('deleteConfirmModal')?.classList.remove('show');
    pendingDeleteAction = null;
  };

  window.executeDelete = function () {
    if (pendingDeleteAction) {
      const action = pendingDeleteAction;
      pendingDeleteAction = null;
      action();
    }

    closeDeleteModal();
  };

  window.clearSearch = function (inputId, formId) {
    const input = document.getElementById(inputId);
    if (!input) {
      return;
    }

    input.value = '';
    document.getElementById(formId)?.submit();
  };

  window.openRefundModal = function (type, id, receipt, amount, name) {
    currentRefundType = type;
    currentRefundId = id;

    const details = document.getElementById('refundDetails');
    if (details) {
      details.innerHTML = `
        <div class="confirm-row">
          <span class="confirm-label">Receipt</span>
          <span class="confirm-value">#${escapeHtml(receipt || '—')}</span>
        </div>
        <div class="confirm-row">
          <span class="confirm-label">Customer</span>
          <span class="confirm-value">${escapeHtml(name || '—')}</span>
        </div>
        <div class="confirm-row">
          <span class="confirm-label">Amount</span>
          <span class="confirm-value confirm-value-danger">${escapeHtml(formatCurrency(amount))}</span>
        </div>
        <div class="confirm-row">
          <span class="confirm-label">Type</span>
          <span class="confirm-value">${escapeHtml(getPaymentTypeLabel(type))} Payment</span>
        </div>`;
    }

    document.getElementById('refundModal')?.classList.add('show');
  };

  window.closeRefundModal = function () {
    document.getElementById('refundModal')?.classList.remove('show');
    const reasonEl = document.getElementById('refundReason');
    if (reasonEl) {
      reasonEl.value = '';
    }

    currentRefundType = null;
    currentRefundId = null;
    resetRefundButton();
  };

  confirmRefundButton?.addEventListener('click', function () {
    if (!currentRefundType || !currentRefundId) {
      ToastUtils.showError('Invalid refund request');
      return;
    }

    const route = getRefundRoute(currentRefundType, currentRefundId);
    if (!route) {
      ToastUtils.showError('Unable to determine refund route');
      return;
    }

    const refundType = currentRefundType;
    const refundId = currentRefundId;
    const reason = document.getElementById('refundReason')?.value || '';

    this.disabled = true;
    this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i><span>Processing...</span>';

    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('reason', reason);

    fetch(route, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data.success) {
          throw new Error(data.message || 'Refund processing failed');
        }

        closeRefundModal();
        ToastUtils.showSuccess('Refund completed');

        window.setTimeout(function () {
          showRefundReceipt(refundType, refundId, data);
        }, 180);
      })
      .catch(function (error) {
        console.error(error);
        ToastUtils.showError(error.message || 'Refund processing failed');
        resetRefundButton();
      });
  });

  function showRefundReceipt(type, id, refundData) {
    const content = document.getElementById('refundReceiptContent');
    if (!content) {
      return;
    }

    content.innerHTML = renderLoadingState();
    document.getElementById('refundReceiptModal')?.classList.add('show');

    if (refundData?.payment) {
      content.innerHTML = generateRefundReceipt(type, refundData.payment, refundData);
      return;
    }

    const route = getReceiptRoute(type, id);
    if (!route) {
      ToastUtils.showError('Unable to determine receipt route');
      content.innerHTML = '<div class="receipt-empty-state">Failed to resolve the receipt route.</div>';
      return;
    }

    const attemptFetch = function (attempt) {
      fetch(route)
        .then(function (response) {
          if (!response.ok) {
            throw new Error(`Network response not ok: ${response.status}`);
          }
          return response.json();
        })
        .then(function (data) {
          content.innerHTML = generateRefundReceipt(type, data, refundData);
        })
        .catch(function (error) {
          console.error('Receipt fetch attempt failed', attempt, error);
          if (attempt < 3) {
            window.setTimeout(function () {
              attemptFetch(attempt + 1);
            }, 300 * attempt);
            return;
          }

          ToastUtils.showError('Failed to load receipt');
          content.innerHTML = '<div class="receipt-empty-state">Failed to load the refund receipt.</div>';
        });
    };

    attemptFetch(1);
  }

  window.closeRefundReceiptModal = function () {
    document.getElementById('refundReceiptModal')?.classList.remove('show');
    window.setTimeout(function () {
      window.location.reload();
    }, 400);
  };

  window.printRefundReceipt = function () {
    const content = document.getElementById('refundReceiptContent')?.innerHTML;
    if (!content) {
      return;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Refund Receipt</title>
        <style>${getReceiptPrintStyles()}</style>
      </head>
      <body>${content}</body>
      </html>`);
    printWindow.document.close();
    printWindow.print();
  };

  window.viewRefundReceipt = function (type, id) {
    const content = document.getElementById('refundReceiptContent');
    if (!content) {
      return;
    }

    const route = getReceiptRoute(type, id);
    if (!route) {
      ToastUtils.showError('Unable to determine receipt route');
      return;
    }

    content.innerHTML = renderLoadingState();
    document.getElementById('refundReceiptModal')?.classList.add('show');

    fetch(route)
      .then(function (response) {
        if (!response.ok) {
          throw new Error(`Network response not ok: ${response.status}`);
        }
        return response.json();
      })
      .then(function (data) {
        content.innerHTML = generateRefundReceipt(type, data, { payment: data });
      })
      .catch(function (error) {
        console.error(error);
        ToastUtils.showError('Failed to load receipt');
        content.innerHTML = '<div class="receipt-empty-state">Failed to load the refund receipt.</div>';
      });
  };

  window.viewHistoryReceipt = function (type, id) {
    const content = document.getElementById('viewReceiptContent');
    if (!content) {
      return;
    }

    const route = getReceiptRoute(type, id);
    if (!route) {
      ToastUtils.showError('Unable to determine receipt route');
      return;
    }

    content.innerHTML = renderLoadingState();
    document.getElementById('viewReceiptModal')?.classList.add('show');

    fetch(route)
      .then(function (response) {
        if (!response.ok) {
          throw new Error(`Network response not ok: ${response.status}`);
        }
        return response.json();
      })
      .then(function (data) {
        content.innerHTML = generateOriginalReceipt(type, data);
      })
      .catch(function (error) {
        console.error(error);
        ToastUtils.showError('Failed to load receipt');
        content.innerHTML = '<div class="receipt-empty-state">Failed to load the receipt.</div>';
      });
  };

  window.closeViewReceiptModal = function () {
    document.getElementById('viewReceiptModal')?.classList.remove('show');
  };

  window.printViewReceipt = function () {
    const content = document.getElementById('viewReceiptContent')?.innerHTML;
    if (!content) {
      return;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Receipt</title>
        <style>${getReceiptPrintStyles()}</style>
      </head>
      <body>${content}</body>
      </html>`);
    printWindow.document.close();
    printWindow.print();
  };

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    closeRefundModal();
    closeRefundReceiptModal();
    closeViewReceiptModal();
    closeDeleteModal();
  });

  document.querySelectorAll('.modal-overlay').forEach(function (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === this) {
        this.classList.remove('show');
      }
    });
  });

  document.querySelectorAll('[data-toggle="dropdown"]').forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();

      document.querySelectorAll('.dropdown-menu.show').forEach(function (menu) {
        if (menu !== button.nextElementSibling) {
          menu.classList.remove('show');
        }
      });

      const menu = button.nextElementSibling;
      if (menu?.classList.contains('dropdown-menu')) {
        menu.classList.toggle('show');
      }
    });
  });

  document.addEventListener('click', function (event) {
    if (event.target.closest('.dropdown')) {
      return;
    }

    document.querySelectorAll('.dropdown-menu.show').forEach(function (menu) {
      menu.classList.remove('show');
    });
  });
});
