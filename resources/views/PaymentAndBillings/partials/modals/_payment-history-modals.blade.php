<!-- Refund Confirmation Modal -->
<div id="refundModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <h5 class="modal-title">Process Refund</h5>
      <button type="button" class="close" onclick="closeRefundModal()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3 refund-confirm-message">Are you sure you want to refund this payment? This will move the record to the refunded payments table.</p>
      <div class="refund-warning">
        <i class="mdi mdi-alert-circle-outline"></i>
        Product refunds will also restore stock automatically.
      </div>
      <div class="confirm-details" id="refundDetails"></div>
      <div class="form-group mt-3">
        <label for="refundReason">Reason for Refund</label>
        <textarea id="refundReason" class="form-control history-textarea" rows="4" placeholder="Add a short note for the refund receipt (optional)"></textarea>
      </div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeRefundModal()">Cancel</button>
      <button type="button" class="btn btn-refund" id="confirmRefundBtn">
        <i class="mdi mdi-cash-refund"></i><span>Process Refund</span>
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

<form id="bulkDeletePTForm" action="{{ route('pt.history.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <h5 class="modal-title" id="deleteModalTitle">Confirm Delete</h5>
      <button type="button" class="close" onclick="closeDeleteModal()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p id="deleteConfirmText" class="delete-confirm-text">Are you sure you want to delete this payment record?</p>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
      <button type="button" class="btn btn-delete" id="confirmDeleteBtn" onclick="executeDelete()">
        <i class="mdi mdi-delete"></i> Delete
      </button>
    </div>
  </div>
</div>
