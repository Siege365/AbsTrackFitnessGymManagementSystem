const GYM_NAME = 'Abstrack Fitness Gym';
const GYM_ADDRESS = 'Toril, Davao Del Sur';

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

export function humanize(value, fallback = 'N/A') {
  if (!value) {
    return fallback;
  }

  return String(value)
    .replace(/[_-]+/g, ' ')
    .replace(/\b\w/g, function (char) {
      return char.toUpperCase();
    });
}

export function formatCurrency(value, fallback = 'N/A') {
  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  const amount = Number(value);
  if (!Number.isFinite(amount)) {
    return fallback;
  }

  return `PHP ${amount.toFixed(2)}`;
}

export function formatDateTime(value, fallback = 'N/A') {
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

function renderInfoSection(title, rows) {
  const validRows = (rows || []).filter(function (row) {
    return row && row.label;
  });

  if (!validRows.length) {
    return '';
  }

  const rowHtml = validRows.map(function (row) {
    const classNames = ['unified-receipt-row'];
    if (row.highlight) {
      classNames.push('is-highlight');
    }

    return `
      <div class="${classNames.join(' ')}">
        <span class="unified-receipt-row-label">${escapeHtml(row.label)}</span>
        <span class="unified-receipt-row-value">${escapeHtml(row.value || 'N/A')}</span>
      </div>`;
  }).join('');

  return `
    <section class="unified-receipt-section">
      <h4>${escapeHtml(title)}</h4>
      <div class="unified-receipt-grid">${rowHtml}</div>
    </section>`;
}

function renderLineItems(items) {
  const validItems = (items || []).filter(function (item) {
    return item && item.description;
  });

  if (!validItems.length) {
    return `
      <section class="unified-receipt-section">
        <h4>Payment Breakdown</h4>
        <div class="unified-receipt-empty">No line items available.</div>
      </section>`;
  }

  const rows = validItems.map(function (item) {
    return `
      <tr>
        <td>
          <div class="unified-receipt-item-name">${escapeHtml(item.description)}</div>
          ${item.meta ? `<div class="unified-receipt-item-meta">${escapeHtml(item.meta)}</div>` : ''}
        </td>
        <td class="text-right">${escapeHtml(item.qty || '1')}</td>
        <td class="text-right">${escapeHtml(item.rate || 'N/A')}</td>
        <td class="text-right">${escapeHtml(item.amount || 'N/A')}</td>
      </tr>`;
  }).join('');

  return `
    <section class="unified-receipt-section">
      <h4>Payment Breakdown</h4>
      <table class="unified-receipt-table">
        <thead>
          <tr>
            <th>Description</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Rate</th>
            <th class="text-right">Amount</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </section>`;
}

function renderTotals(totals) {
  const validTotals = (totals || []).filter(function (total) {
    return total && total.label;
  });

  if (!validTotals.length) {
    return '';
  }

  const rows = validTotals.map(function (row) {
    const classNames = ['unified-receipt-total-row'];
    if (row.emphasis) {
      classNames.push('is-emphasis');
    }
    if (row.danger) {
      classNames.push('is-danger');
    }

    return `
      <div class="${classNames.join(' ')}">
        <span>${escapeHtml(row.label)}</span>
        <strong>${escapeHtml(row.value || 'N/A')}</strong>
      </div>`;
  }).join('');

  return `<section class="unified-receipt-section unified-receipt-totals">${rows}</section>`;
}

function renderNotes(notes) {
  const validNotes = (notes || []).filter(function (note) {
    return note && note.label && note.value;
  });

  if (!validNotes.length) {
    return '';
  }

  const noteHtml = validNotes.map(function (note) {
    return `
      <div class="unified-receipt-note">
        <strong>${escapeHtml(note.label)}</strong>
        <p>${escapeHtml(String(note.value)).replace(/\n/g, '<br>')}</p>
      </div>`;
  }).join('');

  return `<section class="unified-receipt-section">${noteHtml}</section>`;
}

export function buildUnifiedReceiptHTML(options) {
  const transactionRows = options.transactionRows || [];
  const partyRows = options.partyRows || [];
  const paymentRows = options.paymentRows || [];

  return `
    <div class="unified-receipt-frame">
      <article class="unified-receipt-paper">
        <header class="unified-receipt-header">
          <p class="unified-receipt-gym">${escapeHtml(GYM_NAME)}</p>
          <p class="unified-receipt-address">${escapeHtml(GYM_ADDRESS)}</p>
          <h3>${escapeHtml(options.title || 'Receipt')}</h3>
          ${options.badge ? `<span class="unified-receipt-badge">${escapeHtml(options.badge)}</span>` : ''}
        </header>

        ${renderInfoSection('Transaction Details', transactionRows)}
        ${renderInfoSection(options.partyTitle || 'Client Information', partyRows)}
        ${renderInfoSection('Payment Details', paymentRows)}
        ${renderLineItems(options.lineItems || [])}
        ${renderTotals(options.totals || [])}
        ${renderNotes(options.notes || [])}

        <footer class="unified-receipt-footer">
          <p><strong>${escapeHtml(options.footerPrimary || 'Thank you for your payment.')}</strong></p>
          ${options.footerSecondary ? `<p>${escapeHtml(options.footerSecondary)}</p>` : ''}
        </footer>
      </article>
    </div>`;
}

export function fitReceiptToViewport(container) {
  if (!container) {
    return;
  }

  const frame = container.querySelector('.unified-receipt-frame');
  const paper = container.querySelector('.unified-receipt-paper');

  if (!frame || !paper) {
    return;
  }

  paper.style.transform = 'scale(1)';

  const frameHeight = frame.clientHeight;
  const frameWidth = frame.clientWidth;
  const paperHeight = paper.scrollHeight;
  const paperWidth = paper.scrollWidth;

  if (!frameHeight || !paperHeight || !frameWidth || !paperWidth) {
    return;
  }

  const scale = Math.min(1, frameHeight / paperHeight, frameWidth / paperWidth);
  paper.style.transform = `scale(${Math.max(scale, 0.7)})`;
}

export function printUnifiedReceipt(content, title = 'Receipt') {
  if (!content) {
    return;
  }

  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    return;
  }

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>${escapeHtml(title)}</title>
        <style>${getUnifiedReceiptPrintStyles()}</style>
      </head>
      <body>${content}</body>
    </html>`);

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}

export function getUnifiedReceiptPrintStyles() {
  return `
    body { margin: 0; padding: 20px; font-family: 'Segoe UI', Tahoma, sans-serif; color: #1b1f28; }
    .unified-receipt-frame { height: auto !important; overflow: visible !important; display: block !important; }
    .unified-receipt-paper { transform: scale(1) !important; margin: 0 auto; width: 100%; max-width: 760px; background: #fff; border: 1px solid #d8dde8; border-radius: 14px; padding: 14px; box-sizing: border-box; }
    .unified-receipt-header { text-align: center; border-bottom: 1px solid #d8dde8; padding-bottom: 10px; margin-bottom: 10px; }
    .unified-receipt-gym { margin: 0; font-size: 0.95rem; font-weight: 700; }
    .unified-receipt-address { margin: 2px 0 8px; font-size: 0.8rem; color: #4d5a73; }
    .unified-receipt-header h3 { margin: 0; font-size: 1.1rem; letter-spacing: 0.03em; }
    .unified-receipt-badge { display: inline-block; margin-top: 6px; background: #ffe6e8; color: #b4232f; padding: 3px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
    .unified-receipt-section { margin-bottom: 10px; }
    .unified-receipt-section h4 { margin: 0 0 6px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #4d5a73; }
    .unified-receipt-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; }
    .unified-receipt-row { border: 1px solid #e3e8f2; border-radius: 8px; padding: 5px 7px; background: #f8faff; min-height: 42px; }
    .unified-receipt-row.is-highlight { border-color: #9db6ff; background: #eef3ff; }
    .unified-receipt-row-label { display: block; font-size: 0.68rem; text-transform: uppercase; color: #657289; }
    .unified-receipt-row-value { display: block; margin-top: 2px; font-weight: 600; font-size: 0.8rem; }
    .unified-receipt-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
    .unified-receipt-table th, .unified-receipt-table td { padding: 6px; border-bottom: 1px solid #e5e9f2; vertical-align: top; }
    .unified-receipt-table th { font-size: 0.7rem; text-transform: uppercase; color: #4d5a73; background: #f3f6fd; }
    .unified-receipt-item-name { font-weight: 600; }
    .unified-receipt-item-meta { margin-top: 2px; font-size: 0.68rem; color: #667289; }
    .unified-receipt-totals { border-top: 1px solid #d8dde8; padding-top: 8px; }
    .unified-receipt-total-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 0.82rem; }
    .unified-receipt-total-row.is-emphasis { font-size: 0.95rem; }
    .unified-receipt-total-row.is-danger strong { color: #b4232f; }
    .unified-receipt-note { border: 1px solid #e3e8f2; border-radius: 8px; padding: 7px; background: #f8faff; margin-bottom: 6px; }
    .unified-receipt-note strong { display: block; font-size: 0.72rem; margin-bottom: 3px; color: #4d5a73; text-transform: uppercase; }
    .unified-receipt-note p { margin: 0; font-size: 0.78rem; }
    .unified-receipt-footer { border-top: 1px solid #d8dde8; padding-top: 8px; text-align: center; }
    .unified-receipt-footer p { margin: 2px 0; font-size: 0.74rem; color: #4d5a73; }
    .text-right { text-align: right; }
  `;
}
