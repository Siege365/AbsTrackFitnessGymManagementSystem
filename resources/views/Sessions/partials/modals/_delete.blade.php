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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="SessionsPage.executeDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
