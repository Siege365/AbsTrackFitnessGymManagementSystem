<!-- Cancel PT Confirmation Modal -->
<div class="modal fade" id="cancelPTConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Session</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 48px;"></i>
                </div>
                <p class="text-center">Are you sure you want to cancel the PT session for <strong id="cancelPTClientName"></strong>?</p>
                <p class="text-center text-muted" style="font-size: 0.9rem;">This action will mark the session as cancelled.</p>
                <input type="hidden" id="cancelPTId">
                <input type="hidden" id="cancelPTClientId">
                <input type="hidden" id="cancelPTClientNameHidden">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">No, Keep It</button>
                <button type="button" class="btn btn-danger" onclick="SessionsPage.executeCancelPT()">Yes, Cancel Session</button>
            </div>
        </div>
    </div>
</div>
