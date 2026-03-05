<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmText">Are you sure you want to delete this record?</p>
                <input type="hidden" id="deleteType">
                <input type="hidden" id="deleteId">
                <p class="mt-3 mb-1">Type <strong>"delete"</strong> to confirm:</p>
                <input type="text" class="form-control" id="deleteSessionConfirmInput" placeholder="Type delete to confirm" autocomplete="off">
                <small class="text-danger d-none" id="deleteSessionConfirmError">Text doesn't match. Please type "delete".</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteSessionConfirmBtn" disabled onclick="SessionsPage.executeDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
