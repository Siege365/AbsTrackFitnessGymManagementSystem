<!-- PT Confirmation Modal -->
<div id="ptConfirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-dumbbell"></i>
      <h5>Confirm PT Payment</h5>
      <button type="button" class="close" onclick="closePtConfirmation()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the payment details before proceeding.</p>
      <div class="confirm-details" id="ptConfirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closePtConfirmation()">Cancel</button>
      <button type="button" class="btn btn-update" id="ptConfirmBtn">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- PT Receipt Modal -->
<div id="ptReceiptModal" class="modal-overlay" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">PT Payment Receipt</h3>
      <button class="modal-close" onclick="closePtReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="ptReceiptBody">
      <div class="loading-spinner"><div class="spinner"></div></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closePtReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printPtReceipt()"><i class="mdi mdi-printer"></i> Print</button>
    </div>
  </div>
</div>
