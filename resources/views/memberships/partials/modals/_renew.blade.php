<!-- Renew Subscription Modal -->
<div class="modal fade" id="renewMembershipModal" tabindex="-1" role="dialog" aria-labelledby="renewMembershipModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="renewFormContent">
        <div class="modal-header">
          <h5 class="modal-title" id="renewMembershipModalLabel">Renew Subscription</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="renewMembershipForm">
            <input type="hidden" id="renewMembershipId" name="membership_id">
            <input type="hidden" id="renewMembershipName" name="membership_name">
            <input type="hidden" id="renewPlanType" name="plan_type">
            <input type="hidden" id="renewPlanKey" name="plan_key">
            <input type="hidden" id="renewDurationDays" name="duration_days">

            <!-- Row 1: Member Name and Current Plan -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Member Name</label>
                <input type="text" class="form-control" id="renewMemberNameDisplay" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
              </div>
              <div class="form-group col-md-6">
                <label>Current Plan</label>
                <input type="text" class="form-control" id="renewPlanTypeDisplay" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
              </div>
            </div>

            <!-- Row 2: Start Date and End Date -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="renewStartDate" class="form-control" required onchange="calculateRenewEndDate()">
              </div>
              <div class="form-group col-md-6">
                <label>End Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" id="renewEndDate" class="form-control" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
              </div>
            </div>

            <div class="alert alert-info" style="background-color: rgba(66, 165, 245, 0.1); border: 1px solid rgba(66, 165, 245, 0.3); color: #42A5F5;">
              <i class="mdi mdi-information"></i> The end date will be automatically calculated based on the membership subscription type.
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showRenewConfirmModal()">Renew</button>
        </div>
      </div>

      <!-- Confirmation Overlay -->
      <div id="renewConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-refresh"></i>
            <h5>Confirm Renewal</h5>
            <button type="button" class="close" onclick="backToRenewForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to renew this subscription?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Member:</span>
                <span class="confirm-value" id="confirmRenewNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmRenewPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmRenewDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToRenewForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitRenewForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
