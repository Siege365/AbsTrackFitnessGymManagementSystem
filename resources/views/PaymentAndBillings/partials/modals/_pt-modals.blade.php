<!-- PT Confirmation Modal -->
<div id="ptConfirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-dumbbell"></i>
      <h5>Confirm PT Booking</h5>
      <button type="button" class="close" onclick="closePtConfirmation()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the session details before proceeding.</p>
      <div class="confirm-details" id="ptConfirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closePtConfirmation()">Cancel</button>
      <button type="button" class="btn btn-update" id="ptConfirmBtn">
        <i class="mdi mdi-check"></i> Confirm & Book
      </button>
    </div>
  </div>
</div>
