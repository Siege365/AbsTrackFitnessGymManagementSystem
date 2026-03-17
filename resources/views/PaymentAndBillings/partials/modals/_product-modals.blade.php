<!-- Payment Confirmation Modal -->
<div id="prodConfirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-cart-check"></i>
      <h5>Confirm Payment</h5>
      <button type="button" class="close" onclick="closeProdConfirmation()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the payment details before proceeding.</p>
      <div class="confirm-details" id="prodConfirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closeProdConfirmation()">Cancel</button>
      <button type="button" class="btn btn-update" id="prodConfirmPaymentBtn">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Product Receipt Modal -->
<div id="productReceiptModal" class="modal-overlay receipt-overlay" role="document">
  <div class="modal-content receipt-modal-shell">
    <div class="modal-header">
      <h3 class="modal-title">Receipt</h3>
      <button class="modal-close" onclick="closeProductReceiptModal()">&times;</button>
    </div>
    <div class="modal-body receipt-modal-body" id="productReceiptBody">
      <div class="loading-spinner"><div class="spinner"></div></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeProductReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printProductReceipt()"><i class="mdi mdi-printer"></i> Print</button>
    </div>
  </div>
</div>
