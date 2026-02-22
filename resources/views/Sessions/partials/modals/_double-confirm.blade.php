<!-- Double Confirmation Modal -->
<div class="modal fade" id="doubleConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Final Confirmation</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Type "<strong id="confirmName"></strong>" to confirm deletion:</p>
                <input type="text" class="form-control" id="confirmInput" placeholder="Type name to confirm">
                <small class="text-danger d-none" id="confirmError">Name doesn't match. Please try again.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="finalDeleteBtn" disabled
                    onclick="SessionsPage.finalDelete()">Delete Permanently</button>
            </div>
        </div>
    </div>
</div>
