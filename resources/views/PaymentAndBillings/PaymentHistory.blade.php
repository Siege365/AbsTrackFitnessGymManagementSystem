@extends('layouts.admin')

@section('title', 'Payments & Billing -> Payment History')

@section('content')
<div class="container-fluid">
  <h2>Payment History</h2>
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
      </div>
    </form>
  </div>

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
            @foreach($productPayments ?? [] as $p)
            <tr>
              <td>{{ $p->receipt_number }}</td>
              <td>{{ $p->customer_name }}</td>
              <td>{{ $p->created_at->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($p->total_amount,2) }}</td>
              <td>{{ $p->cashier_name }}</td>
              <td>{{ $p->refunded_at ? 'Refunded' : 'Paid' }}</td>
              <td>
                <button class="btn btn-sm btn-action action-btn" data-type="product" data-id="{{ $p->id }}" data-receipt="{{ $p->receipt_number }}" data-name="{{ $p->customer_name }}" data-amount="{{ $p->total_amount }}">
                  <i class="mdi mdi-dots-vertical"></i>
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $productPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>

  {{-- Refunded product payments removed - using combined refunded table below --}}

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
            @foreach($membershipPayments ?? [] as $m)
            <tr>
                  <td>{{ $m->receipt_number }}</td>
                  <td>{{ $m->member_name }}</td>
                  <td>{{ $m->created_at->format('M d, Y - h:i A') }}</td>
                  <td>₱{{ number_format($m->amount,2) }}</td>
                  <td>{{ $m->processed_by }}</td>
                  <td>{{ $m->refunded_at ? 'Refunded' : 'Paid' }}</td>
                  <td>
                    <button class="btn btn-sm btn-action action-btn" data-type="membership" data-id="{{ $m->id }}" data-receipt="{{ $m->receipt_number }}" data-name="{{ $m->member_name }}" data-amount="{{ $m->amount }}">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                  </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $membershipPayments->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>

  {{-- Refunded membership payments removed - using combined refunded table below --}}

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
              <th>Reason</th>
              <th>Processed By</th>
            </tr>
          </thead>
          <tbody>
            @foreach($combinedRefunds ?? [] as $cr)
            <tr>
              <td>{{ $cr->receipt_number }}</td>
              <td>{{ $cr->name }}</td>
              <td>{{ $cr->type }}</td>
              <td>{{ optional($cr->refunded_at)->format('M d, Y - h:i A') }}</td>
              <td>₱{{ number_format($cr->amount,2) }}</td>
              <td>{{ $cr->refund_reason }}</td>
              <td>{{ $cr->refunded_by }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        {{ $combinedRefunds->links('vendor.pagination.custom') }}
      </div>
    </div>
  </div>

</div>
@endsection
@push('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
// Action modal (opens when clicking three dots)
function showActionModal(type, id, receipt, name, amount) {
  // create modal if not exists
  let modal = document.getElementById('historyActionModal');
  if (!modal) {
    const html = `
      <div id="historyActionModal" class="modal-overlay">
        <div class="modal-content small">
          <div class="modal-header"><h3 class="modal-title">Transaction Actions</h3><button class="modal-close" onclick="closeActionModal()">&times;</button></div>
          <div class="modal-body" id="historyActionBody"></div>
          <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeActionModal()">Close</button>
          </div>
        </div>
      </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    modal = document.getElementById('historyActionModal');
  }

  const body = document.getElementById('historyActionBody');
  body.innerHTML = `
    <div style="margin-bottom:0.5rem;"><strong>#${receipt}</strong></div>
    <div style="margin-bottom:1rem;">${name} — ₱${parseFloat(amount).toFixed(2)}</div>
    <div style="display:flex; gap:0.5rem;">
      <button class="btn btn-sm btn-light" onclick="viewHistoryReceipt('${type}', ${id})">View</button>
      <button class="btn btn-sm btn-warning" onclick="openRefundModalFromHistory('${type}', ${id}, '${receipt}', ${amount}, '${addslashes("" + name)}')">Refund</button>
      <button class="btn btn-sm btn-danger" onclick="deleteHistoryTransaction('${type}', ${id})">Delete</button>
    </div>`;

  modal.classList.add('show');
}

function closeActionModal() {
  const modal = document.getElementById('historyActionModal');
  if (modal) modal.classList.remove('show');
}

function viewHistoryReceipt(type, id) {
  closeActionModal();
  // reuse existing receipt modal from layout if available
  const url = type === 'product' ? `/payments/${id}/receipt-data` : `/membership-payment/${id}/receipt`;
  const modalHtml = `
    <div id="historyReceiptModal" class="modal-overlay">
      <div class="modal-content" id="historyReceiptModalBody"></div>
      <button onclick="document.getElementById('historyReceiptModal').remove()" class="modal-close">Close</button>
    </div>`;
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  const body = document.getElementById('historyReceiptModalBody');
  body.innerHTML = '<div style="padding:2rem;text-align:center;">Loading...</div>';

  fetch(url)
    .then(r => r.json())
    .then(data => {
      body.innerHTML = '<pre style="white-space:pre-wrap;">' + JSON.stringify(data, null, 2) + '</pre>';
    })
    .catch(err => {
      console.error(err);
      body.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

function addslashes(str) { return (str+'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\0/g,'\\0'); }

function deleteHistoryTransaction(type, id) {
  if (!confirm('Delete this transaction? This action cannot be undone.')) return;
    if (type === 'product') {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/payments/${id}`;
      form.innerHTML = '<input type="hidden" name="_token" value="' + CSRF_TOKEN + '">'
        + '<input type="hidden" name="_method" value="DELETE">';
      document.body.appendChild(form);
      form.submit();
    } else {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/membership-payment/${id}`;
      form.innerHTML = '<input type="hidden" name="_token" value="' + CSRF_TOKEN + '">'
        + '<input type="hidden" name="_method" value="DELETE">';
      document.body.appendChild(form);
      form.submit();
    }
}

// Refund modal (reuses the membership modal style)
function openRefundModalFromHistory(type, id, receiptNumber, amount, name) {
  closeActionModal();
  // create refund modal if not exists
  if (!document.getElementById('historyRefundModal')) {
    const html = `
      <div id="historyRefundModal" class="modal-overlay">
        <div class="modal-content small">
          <div class="modal-header"><h3 class="modal-title">Process Refund</h3><button class="modal-close" onclick="closeRefundModalFromHistory()">&times;</button></div>
          <div class="modal-body">
            <div class="refund-warning"><i class="mdi mdi-alert"></i><strong>Warning:</strong> This will mark this transaction as refunded.</div>
            <div class="confirmation-details" id="historyRefundDetails"></div>
            <div class="form-group"><label class="form-label">Refund Reason (Optional)</label><textarea class="form-control" id="historyRefundReason" rows="3" placeholder="Enter reason for refund..."></textarea></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeRefundModalFromHistory()">Cancel</button>
            <button type="button" class="btn btn-warning" onclick="confirmRefundFromHistory('${type}', ${id})">Process Refund</button>
          </div>
        </div>
      </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
  }

  document.getElementById('historyRefundDetails').innerHTML = `
    <div class="confirmation-detail-row"><span class="confirmation-detail-label">Receipt:</span><span class="confirmation-detail-value">#${receiptNumber}</span></div>
    <div class="confirmation-detail-row"><span class="confirmation-detail-label">Name:</span><span class="confirmation-detail-value">${name}</span></div>
    <div class="confirmation-detail-row"><span class="confirmation-detail-label">Amount:</span><span class="confirmation-detail-value" style="color:#dc3545;">₱${parseFloat(amount).toFixed(2)}</span></div>`;

  document.getElementById('historyRefundModal').classList.add('show');
}

function closeRefundModalFromHistory() {
  const modal = document.getElementById('historyRefundModal');
  if (modal) modal.classList.remove('show');
}

function confirmRefundFromHistory(type, id) {
  const reason = document.getElementById('historyRefundReason').value;
  const url = type === 'product' ? `/payments/${id}/refund` : `/membership-payment/${id}/refund`;
  const formData = new FormData();
  formData.append('reason', reason || '');

  fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        alert(data.message || 'Refund processed');
        window.location.reload();
      } else {
        alert(data.message || 'Failed to refund');
      }
    })
    .catch(err => { console.error(err); alert('Failed to process refund'); });
}

// Attach click listeners to action buttons (delegated)
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const type = this.dataset.type;
      const id = this.dataset.id;
      const receipt = this.dataset.receipt;
      const name = this.dataset.name;
      const amount = this.dataset.amount;
      showActionModal(type, id, receipt, name, amount);
    });
  });
});
</script>
@endpush
