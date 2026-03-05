<!-- ========================================== -->
<!-- TOGGLE PLAN STATUS CONFIRMATION MODAL      -->
<!-- ========================================== -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" role="dialog" aria-labelledby="toggleStatusModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="max-width: 500px; margin: auto;">
      <div class="modal-header">
        <h5 class="modal-title" id="toggleStatusModalLabel">Confirm Status Change</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert" id="toggleStatusAlert" style="border: 1px solid; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
          <i class="mdi" id="toggleStatusIcon"></i>
          <span id="toggleStatusMessage"></span>
        </div>
        <div class="toggle-status-details">
          <div class="form-group">
            <label>Plan Name</label>
            <div class="form-control" id="toggleStatusPlanName"></div>
          </div>
          <div class="form-group">
            <label>Category</label>
            <div class="form-control" id="toggleStatusPlanCategory"></div>
          </div>
          <div class="form-group">
            <label>Current Status</label>
            <div class="form-control" id="toggleStatusCurrentStatus"></div>
          </div>
          <div class="form-group">
            <label>New Status</label>
            <div class="form-control" id="toggleStatusNewStatus"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn" id="confirmToggleStatusBtn" onclick="executeToggleStatus()">
          <i class="mdi"></i> <span id="toggleStatusBtnText"></span>
        </button>
      </div>
    </div>
  </div>
</div>
