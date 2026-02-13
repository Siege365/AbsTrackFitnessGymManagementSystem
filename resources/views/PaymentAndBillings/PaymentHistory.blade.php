@extends('layouts.admin')

@section('title', 'Payments & Billing -> Payment History')

@section('content')
<div class="container-fluid">
  <h2>Payment History</h2>

  <!-- Product Payments Table -->
  <div class="card mt-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Product Payments</h4>
        <div class="d-flex align-items-center" style="gap: 0.5rem;">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" style="gap: 0.5rem;">
            <input type="text" name="product_search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('product_search') }}" style="width: 250px;">
            <input type="hidden" name="membership_search" value="{{ request('membership_search') }}">
            <input type="hidden" name="refund_search" value="{{ request('refund_search') }}">
            
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <h6 class="dropdown-header">Status</h6>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('product_filter'), ['product_search' => request('product_search'), 'product_filter' => 'all'])) }}">All</a>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('product_filter'), ['product_search' => request('product_search'), 'product_filter' => 'paid'])) }}">Paid Only</a>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('product_filter'), ['product_search' => request('product_search'), 'product_filter' => 'refunded'])) }}">Refunded Only</a>
              </div>
            </div>
            
            <button class="btn btn-sm btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
            @if(request('product_search'))
              <a href="{{ route('payments.history', array_merge(request()->except(['product_search', 'product_filter']))) }}" class="btn btn-sm btn-secondary"><i class="mdi mdi-close"></i></a>
            @endif
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" id="selectAllProduct">
                  </label>
                </div>
              </th>
              <th>Receipt #</th>
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
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input product-checkbox" value="{{ $p->id }}">
                  </label>
                </div>
              </td>
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
                    <button type="button" class="dropdown-item text-danger" onclick="deleteSingleTransaction('product', {{ $p->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center">No product payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="pagination-wrapper mt-4">
        <div class="row align-items-center">
          <div class="col-md-6">
            <button type="button" onclick="bulkDeleteProducts()" class="btn btn-sm btn-delete-selected" id="deleteProductBtn" disabled>
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="productCount">0</span>)
            </button>
          </div>
          <div class="col-md-6">
            {{ $productPayments->appends(request()->except('page'))->links('vendor.pagination.custom') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Membership Payments Table -->
  <div class="card mt-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Membership Payments</h4>
        <div class="d-flex align-items-center" style="gap: 0.5rem;">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" style="gap: 0.5rem;">
            <input type="text" name="membership_search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('membership_search') }}" style="width: 250px;">
            <input type="hidden" name="product_search" value="{{ request('product_search') }}">
            <input type="hidden" name="refund_search" value="{{ request('refund_search') }}">
            
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <h6 class="dropdown-header">Status</h6>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('membership_filter'), ['membership_search' => request('membership_search'), 'membership_filter' => 'all'])) }}">All</a>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('membership_filter'), ['membership_search' => request('membership_search'), 'membership_filter' => 'paid'])) }}">Paid Only</a>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('membership_filter'), ['membership_search' => request('membership_search'), 'membership_filter' => 'refunded'])) }}">Refunded Only</a>
              </div>
            </div>
            
            <button class="btn btn-sm btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
            @if(request('membership_search'))
              <a href="{{ route('payments.history', array_merge(request()->except(['membership_search', 'membership_filter']))) }}" class="btn btn-sm btn-secondary"><i class="mdi mdi-close"></i></a>
            @endif
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" id="selectAllMembership">
                  </label>
                </div>
              </th>
              <th>Receipt #</th>
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
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input membership-checkbox" value="{{ $m->id }}">
                  </label>
                </div>
              </td>
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
                    <button type="button" class="dropdown-item text-danger" onclick="deleteSingleTransaction('membership', {{ $m->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center">No membership payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="pagination-wrapper mt-4">
        <div class="row align-items-center">
          <div class="col-md-6">
            <button type="button" onclick="bulkDeleteMemberships()" class="btn btn-sm btn-delete-selected" id="deleteMembershipBtn" disabled>
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="membershipCount">0</span>)
            </button>
          </div>
          <div class="col-md-6">
            {{ $membershipPayments->appends(request()->except('page'))->links('vendor.pagination.custom') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Refunded Payments Table -->
  <div class="card mt-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Refunded Payments (Combined)</h4>
        <div class="d-flex align-items-center" style="gap: 0.5rem;">
          <form action="{{ route('payments.history') }}" method="GET" class="d-flex align-items-center" style="gap: 0.5rem;">
            <input type="text" name="refund_search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('refund_search') }}" style="width: 250px;">
            <input type="hidden" name="product_search" value="{{ request('product_search') }}">
            <input type="hidden" name="membership_search" value="{{ request('membership_search') }}">
            
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <h6 class="dropdown-header">Type</h6>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('refund_filter'), ['refund_search' => request('refund_search'), 'refund_filter' => 'all'])) }}">All</a>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('refund_filter'), ['refund_search' => request('refund_search'), 'refund_filter' => 'product'])) }}">Products Only</a>
                <a class="dropdown-item" href="{{ route('payments.history', array_merge(request()->except('refund_filter'), ['refund_search' => request('refund_search'), 'refund_filter' => 'membership'])) }}">Memberships Only</a>
              </div>
            </div>
            
            <button class="btn btn-sm btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
            @if(request('refund_search'))
              <a href="{{ route('payments.history', array_merge(request()->except(['refund_search', 'refund_filter']))) }}" class="btn btn-sm btn-secondary"><i class="mdi mdi-close"></i></a>
            @endif
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" id="selectAllRefund">
                  </label>
                </div>
              </th>
              <th>Receipt #</th>
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
              <td>
                <div class="form-check">
                  <label class="form-check-label">
                    <input type="checkbox" class="form-check-input refund-checkbox" value="{{ $cr->id }}" data-type="{{ strtolower($cr->type) }}">
                  </label>
                </div>
              </td>
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
                    <button type="button" class="dropdown-item text-danger" onclick="deleteSingleRefund('{{ strtolower($cr->type) }}', {{ $cr->id }})">
                      <i class="mdi mdi-delete mr-2"></i> Delete
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center">No refunded payments found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination-wrapper mt-4">
        <div class="row align-items-center">
          <div class="col-md-6">
            <button type="button" onclick="bulkDeleteRefunds()" class="btn btn-sm btn-delete-selected" id="deleteRefundBtn" disabled>
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="refundCount">0</span>)
            </button>
          </div>
          <div class="col-md-6">
            {{ $combinedRefunds->appends(request()->except('page'))->links('vendor.pagination.custom') }}
          </div>
        </div>
      </div>
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
      <button type="button" class="btn btn-primary" onclick="printViewReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>
</div>

<!-- Bulk Delete Forms -->
<form id="bulkDeleteProductForm" action="{{ route('payments.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<form id="bulkDeleteMembershipForm" action="{{ route('membership.payment.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

@endsection

@push('styles')
<style>
/* FIX #1: Adjusted colors to match membership management - KEPT ORIGINAL STRUCTURE */

/* Background */
body {
  background: #282A36 !important;
}

/* Cards - Changed colors */
.card {
  background: #191C24 !important;  /* Changed from default to match membership */
  border: none;
  margin-bottom: 2rem;
  border-radius: 8px;
}

.card-body {
  padding: 24px 28px !important;
}

.card-body h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #ffffff;  /* Changed to white */
}

/* Tables - Adjusted colors */
.table {
  color: #ffffff;  /* Changed to white */
  background: transparent;
}

.table thead th {
  color: rgba(255, 255, 255, 0.6);  /* Changed to white with opacity */
  font-weight: 700;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);  /* Changed */
  font-size: 1rem;
  padding: 20px 18px;
}

.table tbody td {
  color: rgba(255, 255, 255, 0.9);  /* Changed to white */
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);  /* Changed */
  padding: 20px 18px;
  font-size: 1rem;
}

.table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.03);  /* Changed */
}

.table-warning {
  background-color: #fff3cd !important;
}

/* Fixed Table Heights: keep fixed height but remove internal scrollbars (use server-side pagination) */
.table-responsive {
  min-height: 600px;
  max-height: 600px;
  /* remove internal vertical scrollbar — pagination will control rows */
  overflow-y: hidden;
  overflow-x: auto;
}

/* Form Controls - Adjusted colors */
.form-control {
  background-color: #282A36;  /* Changed */
  border: 1px solid rgba(255, 255, 255, 0.1);  /* Changed */
  color: #ffffff;  /* Changed */
  padding: 0.625rem 1rem;
  font-size: 1rem;
  min-height: 38px;
}

.form-control-sm {
  padding: 0.5rem 0.875rem;
  font-size: 0.875rem;
  min-height: 38px;
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.4);  /* Changed */
}

.form-control:focus {
  background-color: #282A36;  /* Changed */
  border-color: rgba(255, 255, 255, 0.2);  /* Changed */
  color: #ffffff;  /* Changed */
  box-shadow: none;
}

/* Buttons */
.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0b5ed7;
  color: white;
}

.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
  border-color: #545b62;
  color: white;
}

.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
}

.btn-warning:hover {
  background-color: #e0a800;
  border-color: #d39e00;
  color: #000;
}

/* Badges - Matching membership colors */
.badge-success {
  background: rgba(76, 175, 80, 0.2);  /* Changed */
  color: #4CAF50;  /* Changed */
  padding: 8px 14px;
  border-radius: 20px;
  font-weight: 500;
  font-size: 0.875rem;
}

.badge-warning {
  background: rgba(255, 193, 7, 0.2);  /* Changed */
  color: #FFC107;  /* Changed */
  padding: 8px 14px;
  border-radius: 20px;
  font-weight: 500;
  font-size: 0.875rem;
}

.badge-primary {
  background: rgba(13, 110, 253, 0.2);
  color: #0d6efd;
  padding: 8px 14px;
  border-radius: 20px;
  font-weight: 500;
  font-size: 0.875rem;
}

.badge-info {
  background: rgba(23, 162, 184, 0.2);
  color: #17a2b8;
  padding: 8px 14px;
  border-radius: 20px;
  font-weight: 500;
  font-size: 0.875rem;
}

/* Gray Checkboxes - MATCHING membership management */
input[type="checkbox"] {
  appearance: none;
  width: 18px;
  height: 18px;
  border: 2px solid #6c757d;
  border-radius: 3px;
  background: transparent;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
}

input[type="checkbox"]:checked {
  background: #6c757d;
  border-color: #6c757d;
}

input[type="checkbox"]:checked::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #ffffff;
  font-size: 12px;
  font-weight: bold;
}

input[type="checkbox"]:hover {
  border-color: #8c939d;
}

/* Dropdown - Changed colors */
.dropdown {
  position: relative;
}

.dropdown-menu {
  background: #191C24;  /* Changed */
  border: 1px solid rgba(255, 255, 255, 0.1);  /* Changed */
  animation: fadeInDown 0.2s ease;
  z-index: 50;  /* Keep normal z-index for table dropdowns */
  min-width: 200px;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-menu.show {
  display: block;
}

.dropdown-header {
  color: rgba(255, 255, 255, 0.6);  /* Changed */
  font-size: 0.75rem;
  text-transform: uppercase;
  padding: 0.5rem 1rem;
}

.dropdown-item {
  color: #ffffff;  /* Changed */
  font-size: 1rem;
  padding: 0.625rem 1.25rem;
  white-space: nowrap;
}

.dropdown-item:hover {
  background-color: rgba(255, 255, 255, 0.05);  /* Changed */
  color: #ffffff;  /* Changed */
}

.dropdown-item.text-warning {
  color: #ffc107;
}

.dropdown-item.text-danger {
  color: #dc3545;
}

.btn-action {
  background: rgba(255, 255, 255, 0.1);  /* Changed */
  border: none;
  color: #ffffff;  /* Changed */
  padding: 0.375rem 0.625rem;
}

.btn-action:hover {
  background: rgba(255, 255, 255, 0.2);  /* Changed */
  color: #ffffff;  /* Changed */
}

/* Delete Selected Button - Matching membership */
.btn-delete-selected {
  background: rgba(244, 67, 54, 0.2);  /* Changed */
  border: none;
  color: #F44336;  /* Changed */
  font-size: 1rem;
  padding: 0.625rem 1.25rem;
  font-weight: 500;
}

.btn-delete-selected i {
  font-size: 1.125rem;
  margin-right: 0.5rem;
}

.btn-delete-selected:hover {
  background: rgba(244, 67, 54, 0.3);  /* Changed */
  color: #F44336;  /* Changed */
}

.btn-delete-selected:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Pagination - Matching membership */
.pagination-wrapper {
  border-top: 1px solid rgba(255, 255, 255, 0.1);  /* Changed */
  padding-top: 1.5rem;
}

.pagination .page-item.active .page-link {
  background-color: #ffffff;
  border-color: #ffffff;
  color: #000000;
}

.pagination .page-link {
  color: #ffffff;  /* Changed */
  background-color: #282A36;  /* Changed */
  border-color: #555;
  padding: 8px 12px;
  margin: 0 2px;
  border-radius: 4px;
}

.pagination .page-link:hover {
  background-color: #ffffff;
  border-color: #000000;
  color: #000000;
}

.pagination .page-item.disabled .page-link {
  background-color: #1a1d24;  /* Changed */
  border-color: #333;
  color: #666;
}

/* FIX #2: MODAL STYLES - PROPERLY POSITIONED AS POPUP */
.modal-overlay {
  display: none;  /* Hidden by default */
  position: fixed;  /* FIXED positioning - this is KEY */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);  /* Dark overlay */
  backdrop-filter: blur(5px);
  z-index: 100000 !important;  /* VERY high z-index to appear above everything */
  align-items: center;
  justify-content: center;
  overflow-y: auto;
  padding: 20px;
}

.modal-overlay.show {
  display: flex !important;  /* Shows as flexbox when active */
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  position: relative;
  margin: auto;
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
  background: #191C24;  /* Changed to match */
  color: white;
  border-radius: 8px 8px 0 0;
}

.modal-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: white;
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
  background: rgba(255, 255, 255, 0.1);
  color: #dc3545;
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
  background: #f8f9fa;
  border-radius: 0 0 8px 8px;
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
  color: #000;
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
  color: #000;
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
  color: #000;
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

.receipt-table {
  width: 100%;
  border-collapse: collapse;
  margin: 20px 0;
}

.receipt-table th {
  background: #333;
  color: white;
  padding: 10px;
  text-align: left;
}

.receipt-table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
  color: #000;
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
<script src="{{ asset('js/common/toast-utils.js') }}?v={{ time() }}"></script>
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
let currentRefundType = null;
let currentRefundId = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  initializeCheckboxes();
  
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
});

// FIX #3: Checkbox Management
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

function bulkDeleteProducts() {
  const checked = document.querySelectorAll('.product-checkbox:checked');
  if (checked.length === 0) {
    ToastUtils.showWarning('Please select at least one payment to delete');
    return;
  }
  
  if (!confirm(`Delete ${checked.length} product payment(s)? This cannot be undone.`)) return;
  
  const form = document.getElementById('bulkDeleteProductForm');
  form.innerHTML = '@csrf @method("DELETE")';
  
  checked.forEach(cb => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = cb.value;
    form.appendChild(input);
  });
  
  form.submit();
}

function bulkDeleteMemberships() {
  const checked = document.querySelectorAll('.membership-checkbox:checked');
  if (checked.length === 0) {
    ToastUtils.showWarning('Please select at least one payment to delete');
    return;
  }
  
  if (!confirm(`Delete ${checked.length} membership payment(s)? This cannot be undone.`)) return;
  
  const form = document.getElementById('bulkDeleteMembershipForm');
  form.innerHTML = '@csrf @method("DELETE")';
  
  checked.forEach(cb => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = cb.value;
    form.appendChild(input);
  });
  
  form.submit();
}

function bulkDeleteRefunds() {
  const checked = document.querySelectorAll('.refund-checkbox:checked');
  if (checked.length === 0) {
    ToastUtils.showWarning('Please select at least one refund to delete');
    return;
  }
  
  if (!confirm(`Delete ${checked.length} refunded payment(s)? This cannot be undone.`)) return;
  
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
    form.action = '{{ route("payments.bulkDelete") }}';
    form.innerHTML = '@csrf @method("DELETE")';
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
    form.action = '{{ route("membership.payment.bulkDelete") }}';
    form.innerHTML = '@csrf @method("DELETE")';
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
}

function deleteSingleTransaction(type, id) {
  if (!confirm('Delete this transaction? This cannot be undone.')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = type === 'product' ? `/payments/${id}` : `/membership-payment/${id}`;
  form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="_method" value="DELETE">`;
  document.body.appendChild(form);
  form.submit();
}

function deleteSingleRefund(type, id) {
  if (!confirm('Delete this refunded transaction? This cannot be undone.')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = type === 'product' ? `/payments/${id}` : `/membership-payment/${id}`;
  form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}"><input type="hidden" name="_method" value="DELETE">`;
  document.body.appendChild(form);
  form.submit();
}

// Refund Modal Functions
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

function closeRefundModal() {
  document.getElementById('refundModal').classList.remove('show');
  currentRefundType = null;
  currentRefundId = null;
}

// FIX #2: Refund Confirmation Handler
document.getElementById('confirmRefundBtn')?.addEventListener('click', function() {
  if (!currentRefundType || !currentRefundId) {
    ToastUtils.showError('Invalid refund request');
    return;
  }
  
  const reason = document.getElementById('refundReason').value;
  const url = currentRefundType === 'product' 
    ? `/payments/${currentRefundId}/refund` 
    : `/membership-payment/${currentRefundId}/refund`;
  
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
      ToastUtils.showSuccess('Refund processed successfully!');
      
      // FIX #2: Immediately show refund receipt modal
      setTimeout(() => {
        showRefundReceipt(currentRefundType, currentRefundId, data);
      }, 200);
    } else {
      ToastUtils.showError(data.message || 'Failed to process refund');
      this.disabled = false;
      this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
    }
  })
  .catch(err => {
    console.error(err);
    ToastUtils.showError('Failed to process refund');
    this.disabled = false;
    this.innerHTML = '<i class="mdi mdi-cash-refund"></i> Process Refund';
  });
});

// FIX #2: Show refund receipt in POPUP modal
function showRefundReceipt(type, id, refundData) {
  const content = document.getElementById('refundReceiptContent');
  content.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  
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
          // small backoff before retrying
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

function closeRefundReceiptModal() {
  document.getElementById('refundReceiptModal').classList.remove('show');
  setTimeout(() => window.location.reload(), 500);
}

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

// View refund receipt (from refunded table)
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
      ToastUtils.showError('Failed to load receipt');
      content.innerHTML = '<div style="padding:2rem;color:#dc3545;">Failed to load receipt</div>';
    });
}

// View history receipt (original receipt)
function viewHistoryReceipt(type, id) {
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
}

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
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Transaction Type</strong>
            <span style="display: block; font-weight: 600;">${data.transaction_type || 'N/A'}</span>
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
          <div class="receipt-row">
            <span>Subtotal:</span>
            <span>₱${parseFloat(data.total_amount || 0).toFixed(2)}</span>
          </div>
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
          <tr>
            <td>
              <strong>${data.plan_type || 'Membership'} Plan</strong><br>
              <small style="color: #666;">Duration: ${data.duration || 'N/A'} days</small>
            </td>
            <td style="text-align: right;">₱${parseFloat(data.amount || 0).toFixed(2)}</td>
          </tr>
        </tbody>
      </table>

      <div class="receipt-total">
        <div class="receipt-row" style="font-size: 1.3rem;">
          <span><strong>Total Paid:</strong></span>
          <span><strong>₱${parseFloat(data.amount || 0).toFixed(2)}</strong></span>
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

function closeViewReceiptModal() {
  document.getElementById('viewReceiptModal').classList.remove('show');
}

function printViewReceipt() {
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
}

function addslashes(str) {
  return (str+'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\0/g,'\\0');
}

// Close modals on escape
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

// Dropdown toggle
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
</script>
@endpush