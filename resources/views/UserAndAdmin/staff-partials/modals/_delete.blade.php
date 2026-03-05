<!-- Delete Staff Confirmation Modal -->
<div class="modal fade" id="deleteStaffConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteStaffConfirmModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteStaffConfirmModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this staff account? This action cannot be undone.
        </div>

        <div class="delete-details">
          <div class="form-group">
            <label>Staff Name</label>
            <div class="form-control" id="deleteStaffName"></div>
          </div>

          <div class="form-group">
            <label>Email Address</label>
            <div class="form-control" id="deleteStaffEmail"></div>
          </div>
        </div>

        <form id="deleteStaffForm" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
        <p class="mt-3 mb-1">Type <strong>"delete"</strong> to confirm:</p>
        <input type="text" class="form-control" id="deleteStaffConfirmInput" placeholder="Type delete to confirm" autocomplete="off">
        <small class="text-danger d-none" id="deleteStaffConfirmError">Text doesn't match. Please type "delete".</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteStaffBtn" disabled onclick="confirmDeleteStaff()">Delete Staff</button>
      </div>
    </div>
  </div>
</div>
