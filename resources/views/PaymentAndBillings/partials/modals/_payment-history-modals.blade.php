<!-- Refund Confirmation Modal -->
<div id="refundModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-alert-circle-outline" style="color: #FFC107;"></i>
      <h5>Process Refund</h5>
      <button type="button" class="close" onclick="closeRefundModal()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3" style="color: #FFC107;"><strong>Warning:</strong> This action will mark this transaction as refunded and restore inventory (for products).</p>
      <div class="confirm-details" id="refundDetails"></div>
      <div class="form-group mt-3">
        <label for="refundReason" style="color: #ccc; font-weight: 600; margin-bottom: 6px; display: block;">Reason for Refund</label>
        <textarea id="refundReason" class="form-control" rows="3" placeholder="Enter reason for refund (optional)" style="background: #282A36; color: #fff; border: 1px solid #444; resize: vertical;"></textarea>
      </div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeRefundModal()">Cancel</button>
      <button type="button" class="btn btn-refund" id="confirmRefundBtn">
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
    <div class="modal-footer" style="font-size: 1.125rem;">
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

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-alert-circle-outline" style="color: #dc3545;"></i>
      <h5>Confirm Delete</h5>
      <button type="button" class="close" onclick="closeDeleteModal()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3" style="color: #dc3545;"><strong>Warning:</strong> This action cannot be undone. The selected record(s) will be permanently deleted.</p>
      <div class="confirm-details">
        <div class="confirm-row">
          <span class="confirm-label">Items to delete:</span>
          <span class="confirm-value" id="deleteItemCount">1</span>
        </div>
      </div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
      <button type="button" class="btn btn-delete" id="confirmDeleteBtn" onclick="executeDelete()">
        <i class="mdi mdi-delete"></i> Delete
      </button>
    </div>
  </div>
</div>
