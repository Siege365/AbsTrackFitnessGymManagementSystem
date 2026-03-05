<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteConfirmModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bulkDeleteConfirmModalLabel">Confirm Bulk Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> <strong>Warning:</strong> This action cannot be undone!
        </div>
        <p class="mb-0" style="font-size: 1rem;">
          Are you sure you want to delete <strong><span id="bulkDeleteCount">0</span> selected client(s)</strong>?
        </p>
        <p class="mt-3 mb-1">Type <strong>"delete"</strong> to confirm:</p>
        <input type="text" class="form-control" id="bulkDeleteConfirmInput" placeholder="Type delete to confirm" autocomplete="off">
        <small class="text-danger d-none" id="bulkDeleteConfirmError">Text doesn't match. Please type "delete".</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="bulkDeleteConfirmBtn" disabled onclick="confirmBulkDelete()">Delete Selected</button>
      </div>
    </div>
  </div>
</div>
