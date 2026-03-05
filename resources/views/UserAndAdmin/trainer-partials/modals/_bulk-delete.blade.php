<!-- Bulk Delete Trainer Confirmation Modal -->
<div class="modal fade" id="bulkDeleteTrainerConfirmModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteTrainerConfirmModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bulkDeleteTrainerConfirmModalLabel">Confirm Bulk Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete <strong><span id="bulkDeleteTrainerCount">0</span></strong> selected trainer(s)? This action cannot be undone.
        </div>
        <p class="mt-3 mb-1">Type <strong>"delete"</strong> to confirm:</p>
        <input type="text" class="form-control" id="bulkDeleteTrainerConfirmInput" placeholder="Type delete to confirm" autocomplete="off">
        <small class="text-danger d-none" id="bulkDeleteTrainerConfirmError">Text doesn't match. Please type "delete".</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="bulkDeleteTrainerConfirmBtn" disabled onclick="confirmBulkDeleteTrainers()">Delete Selected</button>
      </div>
    </div>
  </div>
</div>
