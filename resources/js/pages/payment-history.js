// ========================================
// PAYMENT HISTORY - Page Controller
// ========================================
// Handles tab switching, checkboxes, bulk delete, refund modals, and receipt generation

document.addEventListener('DOMContentLoaded', function() {

  // ========================================
  // Read configuration from blade
  // ========================================
  const config = document.getElementById('paymentHistoryConfig');
  if (!config) {
    console.error('Payment history config element not found');
    return;
  }

  const CSRF_TOKEN = config.dataset.csrfToken;
  const bulkDeleteProductRoute = config.dataset.bulkDeleteProductRoute;
  const bulkDeleteMembershipRoute = config.dataset.bulkDeleteMembershipRoute;
  const flashSuccess = config.dataset.flashSuccess;
  const flashError = config.dataset.flashError;
  const flashErrors = config.dataset.flashErrors;

  // ========================================
  // State
  // ========================================
  let currentRefundType = null;
  let currentRefundId = null;
  let pendingDeleteAction = null;

  // ========================================
  // Initialize
  // ========================================
  initializeCheckboxes();
  initializePageToggle();

  // Display Laravel flash messages
  if (flashSuccess) {
    ToastUtils.showSuccess(flashSuccess);
  }
  if (flashError) {
    ToastUtils.showError(flashError);
  }
  if (flashErrors) {
    ToastUtils.showError(flashErrors);
  }

  // ========================================
  // PAGE TOGGLE (Membership / PT / Product)
  // ========================================
  function initializePageToggle() {
    const pageToggleBtns = document.querySelectorAll('.page-toggle-btn');
    const pageMap = {
      'membership': 'membershipPage',
      'pt': 'ptPage',
      'product': 'productPage'
    };
    const pageOrder = ['membership', 'pt', 'product'];

    pageToggleBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const targetPage = this.dataset.page;
        const currentBtn = document.querySelector('.page-toggle-btn.active');
        const currentPage = currentBtn ? currentBtn.dataset.page : 'membership';

        if (targetPage === currentPage) return;

        const currentIdx = pageOrder.indexOf(currentPage);
        const targetIdx = pageOrder.indexOf(targetPage);
        const goingRight = targetIdx > currentIdx;

        const currentPanel = document.getElementById(pageMap[currentPage]);
        const targetPanel = document.getElementById(pageMap[targetPage]);

        // Animate out current
        if (currentPanel) {
          currentPanel.classList.remove('active');
          currentPanel.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');
        }

        setTimeout(() => {
          if (currentPanel) {
            currentPanel.classList.remove('slide-out-left', 'slide-out-right');
          }

          // Animate in target
          if (targetPanel) {
            targetPanel.classList.add('active', goingRight ? 'slide-in-right' : 'slide-in-left');
            setTimeout(() => {
              targetPanel.classList.remove('slide-in-right', 'slide-in-left');
            }, 400);
          }

          // Update active button
          pageToggleBtns.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
        }, 250);
      });
    });

    // Auto-switch to tab from URL query parameter (?tab=product or ?tab=pt)
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam && pageMap[tabParam]) {
      const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${tabParam}"]`);
      if (targetBtn) {
        pageToggleBtns.forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
        targetBtn.classList.add('active');
        document.getElementById(pageMap[tabParam]).classList.add('active');
      }
    }
  }

  // ========================================
  // CHECKBOX MANAGEMENT
  // ========================================
  function initializeCheckboxes() {
    // Product checkboxes
    const selectAllProduct = document.getElementById('selectAllProduct');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');

    selectAllProduct?.addEventListener('change', function() {
      productCheckboxes.forEach(cb => cb.checked = this.checked);
      updateDeleteButton('product');
    });

    productCheckboxes.forEach(cb => {
      cb.addEventListener('change', () => updateDeleteButton('product'));
    });

    // Membership checkboxes
    const selectAllMembership = document.getElementById('selectAllMembership');
    const membershipCheckboxes = document.querySelectorAll('.membership-checkbox');

    selectAllMembership?.addEventListener('change', function() {
      membershipCheckboxes.forEach(cb => cb.checked = this.checked);
      updateDeleteButton('membership');
    });

    membershipCheckboxes.forEach(cb => {
      cb.addEventListener('change', () => updateDeleteButton('membership'));
    });

    // Refund checkboxes
    const selectAllRefund = document.getElementById('selectAllRefund');
    const refundCheckboxes = document.querySelectorAll('.refund-checkbox');

    selectAllRefund?.addEventListener('change', function() {
      refundCheckboxes.forEach(cb => cb.checked = this.checked);
      updateDeleteButton('refund');
    });

    refundCheckboxes.forEach(cb => {
      cb.addEventListener('change', () => updateDeleteButton('refund'));
    });
  }

  function updateDeleteButton(type) {
    const checkboxes = document.querySelectorAll(`.${type}-checkbox:checked`);
    const count = checkboxes.length;
    const countSpan = document.getElementById(`${type}Count`);
    const deleteBtn = document.getElementById(`delete${type.charAt(0).toUpperCase() + type.slice(1)}Btn`);

    if (countSpan) countSpan.textContent = count;
    if (deleteBtn) deleteBtn.disabled = count === 0;
  }

  // ========================================
  // BULK DELETE FUNCTIONS
  // ========================================
  function buildCsrfDeleteInputs() {
    return `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="_method" value="DELETE">`;
  }

  window.bulkDeleteProducts = function() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
      ToastUtils.showWarning('Please select at least one payment to delete');
      return;
    }

    pendingDeleteAction = function() {
      const form = document.getElementById('bulkDeleteProductForm');
      form.innerHTML = buildCsrfDeleteInputs();

      checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
      });

      form.submit();
    };

    showDeleteModal(checked.length + ' product payment(s)');
  };

  window.bulkDeleteMemberships = function() {
    const checked = document.querySelectorAll('.membership-checkbox:checked');
    if (checked.length === 0) {
      ToastUtils.showWarning('Please select at least one payment to delete');
      return;
    }

    pendingDeleteAction = function() {
      const form = document.getElementById('bulkDeleteMembershipForm');
      form.innerHTML = buildCsrfDeleteInputs();

      checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
      });

      form.submit();
    };

    showDeleteModal(checked.length + ' membership payment(s)');
  };

  window.bulkDeleteRefunds = function() {
    const checked = document.querySelectorAll('.refund-checkbox:checked');
    if (checked.length === 0) {
      ToastUtils.showWarning('No refunds selected for deletion');
      return;
    }

    pendingDeleteAction = function() {
      const products = [];
      const memberships = [];

      checked.forEach(cb => {
        const type = cb.dataset.type;
        const id = cb.value;
        if (type === 'product') products.push(id);
        else memberships.push(id);
      });

      if (products.length > 0) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = bulkDeleteProductRoute;
        form.innerHTML = buildCsrfDeleteInputs();
        products.forEach(id => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = id;
          form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
      }

      if (memberships.length > 0) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = bulkDeleteMembershipRoute;
        form.innerHTML = buildCsrfDeleteInputs();
        memberships.forEach(id => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = id;
          form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
      }
    };

    showDeleteModal(checked.length + ' refunded payment(s)');
  };

  // ========================================
  // DELETE CONFIRMATION MODAL
  // ========================================
  window.confirmDeleteSingle = function(type, id) {
    pendingDeleteAction = function() {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = type === 'product' ? `/payments/${id}` : `/membership-payment/${id}`;
      form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="_method" value="DELETE">`;
      document.body.appendChild(form);
      form.submit();
    };

    showDeleteModal('1 ' + type + ' payment');
  };

  function showDeleteModal(itemDescription) {
    document.getElementById('deleteItemCount').textContent = itemDescription;
    document.getElementById('deleteConfirmModal').classList.add('show');
  }

  window.closeDeleteModal = function() {
    document.getElementById('deleteConfirmModal').classList.remove('show');
    pendingDeleteAction = null;
  };

  window.executeDelete = function() {
    if (pendingDeleteAction) {
      pendingDeleteAction();
      pendingDeleteAction = null;
    }
    closeDeleteModal();
  };

  // ========================================
  // SEARCH
  // ========================================
  window.clearSearch = function(inputId, formId) {
    const input = document.getElementById(inputId);
    if (input) {
      input.value = '';
      document.getElementById(formId).submit();
    }
  };

  // ========================================
  // REFUND MODAL FUNCTIONS
  // ========================================
  window.openRefundModal = function(type, id, receipt, amount, name) {
    currentRefundType = type;
    currentRefundId = id;

    const details = document.getElementById('refundDetails');
    details.innerHTML = `
      <div class="confirm-row">
        <span class="confirm-label">Receipt:</span>
        <span class="confirm-value">#${receipt}</span>
      </div>
      <div class="confirm-row">
        <span class="confirm-label">Name:</span>
        <span class="confirm-value">${name}</span>
      </div>
      <div class="confirm-row">
        <span class="confirm-label">Amount:</span>
        <span class="confirm-value" style="color:#dc3545;">₱${parseFloat(amount).toFixed(2)}</span>
      </div>
      <div class="confirm-row">
        <span class="confirm-label">Type:</span>
        <span class="confirm-value">${type === 'product' ? 'Product Payment' : 'Membership Payment'}</span>
      </div>`;

    document.getElementById('refundModal').classList.add('show');
  };

  window.closeRefundModal = function() {
    document.getElementById('refundModal').classList.remove('show');
    const reasonEl = document.getElementById('refundReason');
    if (reasonEl) reasonEl.value = '';
    currentRefundType = null;
    currentRefundId = null;
  };

  // Refund Confirmation Handler
  document.getElementById('confirmRefundBtn')?.addEventListener('click', function() {
    if (!currentRefundType || !currentRefundId) {
      ToastUtils.showError('Invalid refund request');
      return;
    }

    const reason = document.getElementById('refundReason')?.value || '';
    const url = currentRefundType === 'product'
      ? `/payments/${currentRefundId}/refund`
      : `/membership-payment/${currentRefundId}/refund`;

    this.disabled = true;
    this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';

    const formData = new FormData();
    formData.append('_token', CSRF_TOKEN);
    formData.append('reason', reason);

    fetch(url, {
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
        closeRefundModal();
        ToastUtils.showSuccess('Refund completed');

        // Immediately show refund receipt modal
        setTimeout(() => {
          showRefundReceipt(currentRefundType, currentRefundId, data);
        }, 200);
      } else {
        ToastUtils.showError(data.message || 'Refund processing failed');
        this.disabled = false;
        this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
      }
    })
    .catch(err => {
      console.error(err);
      ToastUtils.showError('Refund processing failed');
      this.disabled = false;
      this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
    });
  });

  // ========================================
  // RECEIPT FUNCTIONS
  // ========================================
  function showRefundReceipt(type, id, refundData) {
    const content = document.getElementById('refundReceiptContent');
    content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div>';

    document.getElementById('refundReceiptModal').classList.add('show');

    // If the refund response already contains payment details, use it immediately
    try {
      if (refundData && refundData.payment) {
        content.innerHTML = generateRefundReceipt(type, refundData.payment, refundData);
        return;
      }
    } catch (e) {
      console.error('Error generating receipt from refundData:', e);
    }

    const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;

    // Try fetching the receipt, retrying a few times in case the server needs a moment to finalize
    const attemptFetch = (attempt = 1) => {
      fetch(url)
        .then(r => {
          if (!r.ok) throw new Error('Network response not ok: ' + r.status);
          return r.json();
        })
        .then(data => {
          content.innerHTML = generateRefundReceipt(type, data, refundData);
        })
        .catch(err => {
          console.error('Receipt fetch attempt', attempt, 'failed:', err);
          if (attempt < 3) {
            setTimeout(() => attemptFetch(attempt + 1), 300 * attempt);
          } else {
            ToastUtils.showError('Failed to load receipt');
            content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
          }
        });
    };

    attemptFetch();
  }

  function generateRefundReceipt(type, paymentData, refundData) {
    const payment = refundData.payment || paymentData;
    const now = new Date();

    let html = `
      <div class="receipt-container">
        <div class="receipt-header">
          <h2>REFUND RECEIPT</h2>
          <div class="receipt-refund-badge">REFUNDED</div>
          <p>Date: ${now.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}</p>
        </div>

        <div class="receipt-info">
          <div class="receipt-row">
            <span>Receipt #:</span>
            <span><strong>${payment.receipt_number}</strong></span>
          </div>
          <div class="receipt-row">
            <span>Customer:</span>
            <span>${payment.customer_name || payment.member_name}</span>
          </div>
          <div class="receipt-row">
            <span>Original Date:</span>
            <span>${new Date(payment.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}</span>
          </div>
          <div class="receipt-row">
            <span>Refund Type:</span>
            <span>${type === 'product' ? 'Product Payment' : 'Membership Payment'}</span>
          </div>
        </div>`;

    if (type === 'product' && payment.items) {
      html += '<div class="receipt-items"><h4>Items:</h4>';
      payment.items.forEach(item => {
        html += `
          <div class="receipt-item">
            <div class="receipt-row">
              <span>${item.product_name}</span>
              <span>₱${parseFloat(item.unit_price).toFixed(2)}</span>
            </div>
            <div class="receipt-row" style="font-size:0.9rem;color:#666;">
              <span>Qty: ${item.quantity}</span>
              <span>₱${parseFloat(item.subtotal || item.total_price || (item.unit_price * item.quantity)).toFixed(2)}</span>
            </div>
          </div>`;
      });
      html += '</div>';
    }

    html += `
        <div class="receipt-total">
          <div class="receipt-row">
            <span>Original Amount:</span>
            <span>₱${parseFloat(payment.total_amount || payment.amount).toFixed(2)}</span>
          </div>
          <div class="receipt-row" style="color:#dc3545;">
            <span>Refunded Amount:</span>
            <span>₱${parseFloat(payment.refunded_amount || payment.total_amount || payment.amount).toFixed(2)}</span>
          </div>
        </div>

        <div class="receipt-info" style="margin-top:20px;">
          <div class="receipt-row">
            <span>Refund Status:</span>
            <span><strong>${payment.refund_status ? payment.refund_status.toUpperCase() : 'FULL'}</strong></span>
          </div>
          ${payment.refund_reason ? `
          <div class="receipt-row">
            <span>Reason:</span>
            <span>${payment.refund_reason}</span>
          </div>` : ''}
          <div class="receipt-row">
            <span>Processed By:</span>
            <span>${payment.refunded_by || 'Admin'}</span>
          </div>
        </div>

        <div class="receipt-footer">
          <p>This is a computer-generated refund receipt.</p>
          <p>Thank you!</p>
        </div>
      </div>`;

    return html;
  }

  window.closeRefundReceiptModal = function() {
    document.getElementById('refundReceiptModal').classList.remove('show');
    setTimeout(() => window.location.reload(), 500);
  };

  window.printRefundReceipt = function() {
    const content = document.getElementById('refundReceiptContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Refund Receipt</title>
        <style>
          body { font-family: 'Courier New', monospace; }
          .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
          .receipt-refund-badge { display: inline-block; background: #dc3545; color: white; padding: 5px 15px; border-radius: 4px; font-weight: bold; margin-top: 10px; }
          .receipt-info { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #666; }
          .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
          .receipt-items { margin-bottom: 20px; }
          .receipt-item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dotted #ccc; }
          .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
          .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; font-size: 0.9rem; color: #666; }
        </style>
      </head>
      <body>${content}</body>
      </html>
    `);
    printWindow.document.close();
    printWindow.print();
  };

  // View refund receipt (from refunded table)
  window.viewRefundReceipt = function(type, id) {
    const content = document.getElementById('refundReceiptContent');
    content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';

    document.getElementById('refundReceiptModal').classList.add('show');

    const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;

    fetch(url)
      .then(r => r.json())
      .then(data => {
        content.innerHTML = generateRefundReceipt(type, data, {payment: data});
      })
      .catch(err => {
        console.error(err);
        ToastUtils.showError('Failed to load receipt');
        content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
      });
  };

  // View history receipt (original receipt)
  window.viewHistoryReceipt = function(type, id) {
    const content = document.getElementById('viewReceiptContent');
    content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';

    document.getElementById('viewReceiptModal').classList.add('show');

    const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;

    fetch(url)
      .then(r => r.json())
      .then(data => {
        content.innerHTML = generateOriginalReceipt(type, data);
      })
      .catch(err => {
        console.error(err);
        ToastUtils.showError('Failed to load receipt');
        content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
      });
  };

  function generateOriginalReceipt(type, data) {
    if (type === 'product') {
      const items = data.items || [];
      const itemsHTML = items.map(item => `
        <tr>
          <td>${item.product_name}</td>
          <td style="text-align: center;">${item.quantity}</td>
          <td style="text-align: right;">₱${parseFloat(item.unit_price).toFixed(2)}</td>
          <td style="text-align: right;">₱${parseFloat(item.subtotal || item.total_price || (item.unit_price * item.quantity)).toFixed(2)}</td>
        </tr>
      `).join('');

      return `
        <div class="receipt-container">
          <div class="receipt-header">
            <h2>RECEIPT</h2>
            <p><strong>Abstrack Fitness Gym</strong></p>
            <p>Toril, Davao Del Sur</p>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong>
              <span style="display: block; font-weight: 600;">#${data.receipt_number}</span>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong>
              <span style="display: block; font-weight: 600;">${data.formatted_date || new Date(data.created_at).toLocaleString()}</span>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Customer Name</strong>
              <span style="display: block; font-weight: 600;">${data.customer_name || 'N/A'}</span>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Cashier</strong>
              <span style="display: block; font-weight: 600;">${data.cashier_name || ''}</span>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong>
              <span style="display: block; font-weight: 600;">${data.payment_method || 'N/A'}</span>
            </div>
          </div>

          <table class="receipt-table">
            <thead>
              <tr>
                <th>Item</th>
                <th style="text-align: center;">Quantity</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              ${itemsHTML}
            </tbody>
          </table>

          <div class="receipt-total">
            <div class="receipt-row" style="font-size: 1.3rem;">
              <span><strong>Total:</strong></span>
              <span><strong>₱${parseFloat(data.total_amount || 0).toFixed(2)}</strong></span>
            </div>
            <div class="receipt-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
              <span>Paid Amount:</span>
              <span>₱${parseFloat(data.paid_amount || data.paid || 0).toFixed(2)}</span>
            </div>
            <div class="receipt-row">
              <span>Change:</span>
              <span>₱${parseFloat(data.return_amount || data.change || 0).toFixed(2)}</span>
            </div>
          </div>

          <div class="receipt-footer">
            <p><strong>Thank you for your purchase!</strong></p>
          </div>
        </div>
      `;
    }

    return `
      <div class="receipt-container">
        <div class="receipt-header">
          <h2>MEMBERSHIP PAYMENT RECEIPT</h2>
          <p><strong>Abstrack Fitness Gym</strong></p>
          <p>Toril, Davao Del Sur</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px;">
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong>
            <span style="display: block; font-weight: 600;">#${data.receipt_number}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong>
            <span style="display: block; font-weight: 600;">${data.formatted_date || new Date(data.created_at).toLocaleString()}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Member Name</strong>
            <span style="display: block; font-weight: 600;">${data.member_name || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Contact</strong>
            <span style="display: block; font-weight: 600;">${data.member_contact || data.contact || ''}</span>
          </div>
          ${data.buddy_name ? `
          <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;">
            <strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Gym Buddy</strong>
            <span style="display: block; font-weight: 600;">${data.buddy_name}</span>
          </div>
          <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;">
            <strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Contact</strong>
            <span style="display: block; font-weight: 600;">${data.buddy_contact || 'N/A'}</span>
          </div>
          ` : ''}
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Type</strong>
            <span style="display: block; font-weight: 600;">${(data.payment_type || '').toUpperCase()}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong>
            <span style="display: block; font-weight: 600;">${data.payment_method || 'N/A'}</span>
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
            ${data.plan_type === 'GymBuddy' ? `
            <tr>
              <td>
                <strong>Gym Buddy Rate</strong><br>
                <small style="color: #666;">Duration: ${data.duration || 'N/A'} days | 2 Persons</small><br>
                <small style="color: #0d6efd;">Member 1: ${data.member_name}</small><br>
                <small style="color: #0d6efd;">Member 2: ${data.buddy_name || 'N/A'}</small>
              </td>
              <td style="text-align: right;">
                <span style="display: block;">₱${parseFloat(data.amount || 0).toFixed(2)}/person</span>
                <strong style="display: block; margin-top: 4px;">Total: ₱${(parseFloat(data.amount || 0) * 2).toFixed(2)}</strong>
              </td>
            </tr>
            ` : `
            <tr>
              <td>
                <strong>${data.plan_type || 'Membership'} Plan</strong><br>
                <small style="color: #666;">Duration: ${data.duration || 'N/A'} days</small>
              </td>
              <td style="text-align: right;">₱${parseFloat(data.amount || 0).toFixed(2)}</td>
            </tr>
            `}
          </tbody>
        </table>

        <div class="receipt-total">
          <div class="receipt-row" style="font-size: 1.3rem;">
            <span><strong>Total Paid:</strong></span>
            <span><strong>₱${data.plan_type === 'GymBuddy' ? (parseFloat(data.amount || 0) * 2).toFixed(2) : parseFloat(data.amount || 0).toFixed(2)}</strong></span>
          </div>
        </div>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ccc;">
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Previous Due Date</strong>
              <span style="display: block; font-weight: 600;">${data.previous_due_date || 'N/A'}</span>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
              <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">New Due Date</strong>
              <span style="display: block; font-weight: 600; color: #28a745;">${data.new_due_date || 'N/A'}</span>
            </div>
          </div>
        </div>

        ${data.notes ? `
          <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
            <strong style="display: block; margin-bottom: 8px; color: #666;">Notes:</strong>
            <p style="margin: 0; color: #333;">${data.notes}</p>
          </div>
        ` : ''}

        <div class="receipt-footer">
          <p><strong>Thank you for your membership!</strong></p>
          <p style="font-size: 0.875rem;">Please keep this receipt for your records.</p>
        </div>
      </div>
    `;
  }

  window.closeViewReceiptModal = function() {
    document.getElementById('viewReceiptModal').classList.remove('show');
  };

  window.printViewReceipt = function() {
    const content = document.getElementById('viewReceiptContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Receipt</title>
        <style>
          body { font-family: 'Courier New', monospace; }
          .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
          .receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          .receipt-table th { background: #333; color: white; padding: 10px; text-align: left; }
          .receipt-table td { padding: 10px; border-bottom: 1px solid #ddd; }
          .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
          .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
          .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; }
        </style>
      </head>
      <body>${content}</body>
      </html>
    `);
    printWindow.document.close();
    printWindow.print();
  };

  // ========================================
  // MODAL CLOSE HANDLERS
  // ========================================
  // Close modals on escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeRefundModal();
      closeRefundReceiptModal();
      closeViewReceiptModal();
      closeDeleteModal();
    }
  });

  // Close modals on overlay click
  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('show');
      }
    });
  });

  // ========================================
  // DROPDOWN TOGGLE
  // ========================================
  document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        if (menu !== this.nextElementSibling) {
          menu.classList.remove('show');
        }
      });

      const menu = this.nextElementSibling;
      if (menu?.classList.contains('dropdown-menu')) {
        menu.classList.toggle('show');
      }
    });
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        menu.classList.remove('show');
      });
    }
  });

});
