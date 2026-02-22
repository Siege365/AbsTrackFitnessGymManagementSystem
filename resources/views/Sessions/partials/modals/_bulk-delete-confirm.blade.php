<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Bulk Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="mdi mdi-delete-alert text-danger" style="font-size: 48px;"></i>
                </div>
                <p id="bulkDeleteText" class="text-center"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="bulkDeleteConfirmBtn"
                    onclick="SessionsPage.executeBulkDelete()">
                    <i class="mdi mdi-delete"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
