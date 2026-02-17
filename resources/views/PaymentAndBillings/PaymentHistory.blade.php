@extends('layouts.admin')

@section('title', 'Payments & Billing -> Payment History')

@section('content')
  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Payment History</h2>
              <p class="page-header-subtitle">View and manage all payment transaction records.</p>
          </div>
      </div>
  </div>

  <div class="mb-3">
    <form action="{{ route('payments.history') }}" method="GET" class="form-inline">
      <div style="display:flex; gap:0.5rem; align-items:center;">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search receipts, names..." value="{{ request('search') }}">
        <select name="filter_type" class="form-control form-control-sm">
          <option value="all" {{ request('filter_type') == 'all' ? 'selected' : '' }}>All Types</option>
          <option value="product" {{ request('filter_type') == 'product' ? 'selected' : '' }}>Product</option>
          <option value="membership" {{ request('filter_type') == 'membership' ? 'selected' : '' }}>Membership</option>
        </select>
        <button class="btn btn-sm btn-primary" type="submit"><i class="mdi mdi-magnify"></i> Search</button>
        @if(request('search') || request('filter_type') != 'all')
        <a href="{{ route('payments.history') }}" class="btn btn-sm btn-secondary"><i class="mdi mdi-refresh"></i> Clear</a>
        @endif
      </div>
    </form>
  </div>

  <!-- Product Payments Table -->
  <div class="card mt-3">
    <div class="card-body">
      <h4>Product Payments</h4>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Receipt</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Total</th>
              <th>Cashier</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($productPayments ?? [] as $p)
            <tr class="{{ $p->is_refunded ? 'table-warning' : '' }}">
              <td>{{ $p->receipt_number }}</td>
              <td>{{ $p->customer_name }}</td>
              <td>{{ $p->created_at->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($p->total_amount,2) }}</td>
              <td>{{ $p->cashier_name }}</td>
              <td>
                @if($p->is_refunded)
                  <span class="badge badge-warning">{{ ucfirst($p->refund_status) }} Refund</span>
                @else
                  <span class="badge badge-success">Paid</span>
                @endif
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('product', {{ $p->id }})">
                      <i class="mdi mdi-eye mr-2"></i> View Receipt
                    </button>
                    @if(!$p->is_refunded)
                    <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('product', {{ $p->id }}, '{{ $p->receipt_number }}', {{ $p->total_amount }}, '{{ addslashes($p->customer_name) }}')">
                      <i class="mdi mdi-cash-refund mr-2"></i> Refund
                    </button>
                    @endif
                    <button type="button" class="dropdown-item text-danger" onclick="deleteHistoryTransaction('product', {{ $p->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center">No product payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $productPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>

  <!-- Membership Payments Table -->
  <div class="card mt-4">
    <div class="card-body">
      <h4>Membership Payments</h4>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Receipt</th>
              <th>Member</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Processed By</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($membershipPayments ?? [] as $m)
            <tr class="{{ $m->is_refunded ? 'table-warning' : '' }}">
              <td>{{ $m->receipt_number }}</td>
              <td>{{ $m->member_name }}</td>
              <td>{{ $m->created_at->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($m->amount,2) }}</td>
              <td>{{ $m->processed_by }}</td>
              <td>
                @if($m->is_refunded)
                  <span class="badge badge-warning">{{ ucfirst($m->refund_status) }} Refund</span>
                @else
                  <span class="badge badge-success">Paid</span>
                @endif
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewHistoryReceipt('membership', {{ $m->id }})">
                      <i class="mdi mdi-eye mr-2"></i> View Receipt
                    </button>
                    @if(!$m->is_refunded)
                    <button type="button" class="dropdown-item text-warning" onclick="openRefundModal('membership', {{ $m->id }}, '{{ $m->receipt_number }}', {{ $m->amount }}, '{{ addslashes($m->member_name) }}')">
                      <i class="mdi mdi-cash-refund mr-2"></i> Refund
                    </button>
                    @endif
                    <button type="button" class="dropdown-item text-danger" onclick="deleteHistoryTransaction('membership', {{ $m->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center">No membership payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $membershipPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>

  <!-- Refunded Payments Table -->
  <div class="card mt-4">
    <div class="card-body">
      <h4>Refunded Payments (Combined)</h4>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Receipt</th>
              <th>Name</th>
              <th>Type</th>
              <th>Refunded At</th>
              <th>Amount</th>
              <th>Refunded Amount</th>
              <th>Reason</th>
              <th>Processed By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($combinedRefunds ?? [] as $cr)
            <tr>
              <td>{{ $cr->receipt_number }}</td>
              <td>{{ $cr->name }}</td>
              <td>
                <span class="badge badge-{{ $cr->type == 'Product' ? 'primary' : 'info' }}">
                  {{ $cr->type }}
                </span>
              </td>
              <td>{{ optional($cr->refunded_at)->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($cr->amount,2) }}</td>
              <td>₱{{ number_format($cr->refunded_amount,2) }}</td>
              <td>{{ $cr->refund_reason ?? 'N/A' }}</td>
              <td>{{ $cr->refunded_by }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                    <i class="mdi mdi-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <button type="button" class="dropdown-item" onclick="viewRefundReceipt('{{ strtolower($cr->type) }}', {{ $cr->id }})">
                      <i class="mdi mdi-receipt mr-2"></i> View Refund Receipt
                    </button>
                    <button type="button" class="dropdown-item text-danger" onclick="deleteRefundedTransaction('{{ strtolower($cr->type) }}', {{ $cr->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center">No refunded payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        {{ $combinedRefunds->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>

<!-- Refund Confirmation Modal -->
<div id="refundModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Process Refund</h3>
      <button class="modal-close" onclick="closeRefundModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="refund-warning">
        <i class="mdi mdi-alert"></i>
        <strong>Warning:</strong> This action will mark this transaction as refunded and restore inventory (for products).
      </div>
      <div class="confirmation-details" id="refundDetails"></div>
      <div class="form-group">
        <label class="form-label">Refund Reason (Optional)</label>
        <textarea class="form-control" id="refundReason" rows="3" placeholder="Enter reason for refund..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">Cancel</button>
      <button type="button" class="btn btn-warning" id="confirmRefundBtn">
        <i class="mdi mdi-cash-refund"></i> Process Refund
      </button>
    </div>
  </div>
</div>

<!-- Refund Receipt Modal -->
<div id="refundReceiptModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Refund Receipt</h3>
      <button class="modal-close" onclick="closeRefundReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="refundReceiptContent">
      <!-- Receipt content will be loaded here -->
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeRefundReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printRefundReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>
</div>

<!-- View Receipt Modal -->
<div id="viewReceiptModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Receipt Details</h3>
      <button class="modal-close" onclick="closeViewReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="viewReceiptContent">
      <!-- Receipt content will be loaded here -->
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeViewReceiptModal()">Close</button>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
/* Modal Styles */
.modal-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  overflow-y: auto;
  padding: 20px;
}

.modal-overlay.show {
  display: flex;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-content.small {
  max-width: 500px;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #f0f0f0;
  color: #333;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e0e0e0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Refund Warning */
.refund-warning {
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 4px;
  padding: 12px;
  margin-bottom: 20px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.refund-warning i {
  color: #ffc107;
  font-size: 1.25rem;
  flex-shrink: 0;
}

/* Confirmation Details */
.confirmation-details {
  background: #f8f9fa;
  border-radius: 4px;
  padding: 15px;
  margin-bottom: 20px;
}

.confirmation-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #e0e0e0;
}

.confirmation-detail-row:last-child {
  border-bottom: none;
}

.confirmation-detail-label {
  font-weight: 600;
  color: #666;
}

.confirmation-detail-value {
  font-weight: 500;
  color: #333;
}

/* Receipt Styles */
.receipt-container {
  max-width: 600px;
  margin: 0 auto;
  background: white;
  padding: 30px;
  font-family: 'Courier New', monospace;
}

.receipt-header {
  text-align: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px dashed #333;
}

.receipt-header h2 {
  margin: 0 0 10px 0;
  font-size: 1.5rem;
}

.receipt-refund-badge {
  display: inline-block;
  background: #dc3545;
  color: white;
  padding: 5px 15px;
  border-radius: 4px;
  font-weight: bold;
  margin-top: 10px;
}

.receipt-info {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px dashed #666;
}

.receipt-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.receipt-items {
  margin-bottom: 20px;
}

.receipt-item {
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px dotted #ccc;
}

.receipt-total {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid #333;
}

.receipt-total .receipt-row {
  font-size: 1.1rem;
  font-weight: bold;
}

.receipt-footer {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 2px dashed #333;
  text-align: center;
  font-size: 0.9rem;
  color: #666;
}

/* Table row highlight for refunded items */
.table-warning {
  background-color: #fff3cd !important;
}

/* Loading spinner */
.loading-spinner {
  text-align: center;
  padding: 40px;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
@vite(['resources/js/common/table-dropdown.js'])
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
let currentRefundType = null;
let currentRefundId = null;

// Open refund modal
function openRefundModal(type, id, receipt, amount, name) {
  currentRefundType = type;
  currentRefundId = id;
  
  const details = document.getElementById('refundDetails');
  details.innerHTML = `
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Receipt:</span>
      <span class="confirmation-detail-value">#${receipt}</span>
    </div>
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Name:</span>
      <span class="confirmation-detail-value">${name}</span>
    </div>
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Amount:</span>
      <span class="confirmation-detail-value" style="color:#dc3545;">₱${parseFloat(amount).toFixed(2)}</span>
    </div>
    <div class="confirmation-detail-row">
      <span class="confirmation-detail-label">Type:</span>
      <span class="confirmation-detail-value">${type === 'product' ? 'Product Payment' : 'Membership Payment'}</span>
    </div>`;
  
  document.getElementById('refundReason').value = '';
  document.getElementById('refundModal').classList.add('show');
}

// Close refund modal
function closeRefundModal() {
  document.getElementById('refundModal').classList.remove('show');
  currentRefundType = null;
  currentRefundId = null;
}

// Confirm refund
document.getElementById('confirmRefundBtn').addEventListener('click', function() {
  if (!currentRefundType || !currentRefundId) {
    alert('Invalid refund request');
    return;
  }
  
  const reason = document.getElementById('refundReason').value;
  const url = currentRefundType === 'product' 
    ? `/payments/${currentRefundId}/refund` 
    : `/membership-payment/${currentRefundId}/refund`;
  
  // Disable button to prevent double clicks
  this.disabled = true;
  this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
  
  const formData = new FormData();
  formData.append('_token', CSRF_TOKEN);
  formData.append('reason', reason || '');
  
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
      // Show refund receipt
      showRefundReceipt(currentRefundType, currentRefundId, data);
    } else {
      alert(data.message || 'Failed to process refund');
      this.disabled = false;
      this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
    }
  })
  .catch(err => {
    console.error(err);
    alert('Failed to process refund');
    this.disabled = false;
    this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
  });
});

// Show refund receipt
function showRefundReceipt(type, id, refundData) {
  const content = document.getElementById('refundReceiptContent');
  content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  
  document.getElementById('refundReceiptModal').classList.add('show');
  
  // Fetch payment details to generate receipt
  const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = generateRefundReceipt(type, data, refundData);
    })
    .catch(err => {
      console.error(err);
      content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

// Generate refund receipt HTML
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
            <span>₱${parseFloat(item.total_price).toFixed(2)}</span>
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

// Close refund receipt modal
function closeRefundReceiptModal() {
  document.getElementById('refundReceiptModal').classList.remove('show');
  // Reload page to show updated status
  setTimeout(() => window.location.reload(), 500);
}

// Print refund receipt
function printRefundReceipt() {
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
}

// View refund receipt (for already refunded items)
function viewRefundReceipt(type, id) {
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
      content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

// View history receipt
function viewHistoryReceipt(type, id) {
  const content = document.getElementById('viewReceiptContent');
  content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  
  document.getElementById('viewReceiptModal').classList.add('show');
  
  const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = generateNormalReceipt(type, data);
    })
    .catch(err => {
      console.error(err);
      content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

// Generate normal receipt HTML
function generateNormalReceipt(type, data) {
  let html = `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>RECEIPT</h2>
        <p>Date: ${new Date(data.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })}</p>
      </div>
      
      <div class="receipt-info">
        <div class="receipt-row">
          <span>Receipt #:</span>
          <span><strong>${data.receipt_number}</strong></span>
        </div>
        <div class="receipt-row">
          <span>${type === 'product' ? 'Customer' : 'Member'}:</span>
          <span>${data.customer_name || data.member_name}</span>
        </div>
      </div>`;
  
  if (type === 'product' && data.items) {
    html += '<div class="receipt-items"><h4>Items:</h4>';
    data.items.forEach(item => {
      html += `
        <div class="receipt-item">
          <div class="receipt-row">
            <span>${item.product_name}</span>
            <span>₱${parseFloat(item.unit_price).toFixed(2)}</span>
          </div>
          <div class="receipt-row" style="font-size:0.9rem;color:#666;">
            <span>Qty: ${item.quantity}</span>
            <span>₱${parseFloat(item.total_price).toFixed(2)}</span>
          </div>
        </div>`;
    });
    html += '</div>';
  }
  
  html += `
      <div class="receipt-total">
        <div class="receipt-row">
          <span>Total:</span>
          <span>₱${parseFloat(data.total_amount || data.amount).toFixed(2)}</span>
        </div>
      </div>
      
      <div class="receipt-footer">
        <p>Thank you for your business!</p>
      </div>
    </div>`;
  
  return html;
}

// Close view receipt modal
function closeViewReceiptModal() {
  document.getElementById('viewReceiptModal').classList.remove('show');
}

// Delete transaction
function deleteHistoryTransaction(type, id) {
  if (!confirm('Delete this transaction? This action cannot be undone.')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = type === 'product' ? `/payments/${id}` : `/membership-payment/${id}`;
  form.innerHTML = `
    <input type="hidden" name="_token" value="${CSRF_TOKEN}">
    <input type="hidden" name="_method" value="DELETE">
  `;
  document.body.appendChild(form);
  form.submit();
}

// Delete refunded transaction
function deleteRefundedTransaction(type, id) {
  if (!confirm('Delete this refunded transaction? This will permanently remove the record.')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = type === 'product' ? `/payments/${id}` : `/membership-payment/${id}`;
  form.innerHTML = `
    <input type="hidden" name="_token" value="${CSRF_TOKEN}">
    <input type="hidden" name="_method" value="DELETE">
  `;
  document.body.appendChild(form);
  form.submit();
}

// Helper function for escaping strings
function addslashes(str) {
  return (str+'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\0/g,'\\0');
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeRefundModal();
    closeRefundReceiptModal();
    closeViewReceiptModal();
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
</script>
@endpush