<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this member? This action cannot be undone.
        </div>

        <div class="delete-details">
          <div class="form-group">
            <label>Member Name</label>
            <div class="form-control" id="deleteMemberName"></div>
          </div>

          <div class="form-group">
            <label>Membership Plan</label>
            <div class="form-control" id="deleteMemberPlan"></div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <div class="form-control" id="deleteMemberStatus"></div>
          </div>
        </div>

        <form id="deleteForm" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Member</button>
      </div>
    </div>
  </div>
</div>
