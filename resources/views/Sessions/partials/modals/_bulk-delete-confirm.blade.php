<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Confirm Bulk Delete</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="mdi mdi-delete-alert text-danger" style="font-size: 48px;"></i>
                </div>
                <p id="bulkDeleteText" class="text-center"></p>
                <p class="mt-3 mb-1">Type <strong>"delete"</strong> to confirm:</p>
                <input type="text" class="form-control" id="bulkDeleteConfirmInput" placeholder="Type delete to confirm">
                <small class="text-danger d-none" id="bulkDeleteConfirmError">Text doesn't match. Please type "delete".</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="bulkDeleteConfirmBtn" disabled
                    onclick="SessionsPage.executeBulkDelete()">
                    <i class="mdi mdi-delete"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
