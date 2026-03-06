<!-- Membership Confirmation Modal -->
<div id="confirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-check-circle-outline"></i>
      <h5>Confirm Payment</h5>
      <button type="button" class="close" onclick="closeConfirmationModal()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the payment details before proceeding.</p>
      <div class="confirm-details" id="confirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeConfirmationModal()">Cancel</button>
      <button type="button" class="btn btn-update" onclick="confirmPayment()">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Membership Receipt Modal -->
<div id="receiptModal" class="modal-overlay" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Receipt Details</h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body" id="receiptBody">
      <div class="loading-spinner"><div class="spinner"></div></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printReceipt()"><i class="mdi mdi-printer"></i> Print</button>
    </div>
  </div>
</div>
