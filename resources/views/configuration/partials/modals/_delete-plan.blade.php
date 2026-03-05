<!-- ========================================== -->
<!-- DELETE PLAN CONFIRMATION MODAL             -->
<!-- ========================================== -->
<div class="modal fade" id="deletePlanModal" tabindex="-1" role="dialog" aria-labelledby="deletePlanModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="max-width: 500px; margin: auto;">
      <div class="modal-header">
        <h5 class="modal-title" id="deletePlanModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this plan? This action cannot be undone.
        </div>
        <div class="delete-details">
          <div class="form-group">
            <label>Plan Name</label>
            <div class="form-control" id="deletePlanName"></div>
          </div>
          <div class="form-group">
            <label>Category</label>
            <div class="form-control" id="deletePlanCategory"></div>
          </div>
          <div class="form-group">
            <label>Price</label>
            <div class="form-control" id="deletePlanPrice"></div>
          </div>
          <div class="form-group">
            <label>Status</label>
            <div class="form-control" id="deletePlanStatus"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="executeDeletePlan()">
          <i class="mdi mdi-delete"></i> Delete Plan
        </button>
      </div>
    </div>
  </div>
</div>
