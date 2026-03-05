<!-- ========================================== -->
<!-- ADD / EDIT PLAN MODAL                      -->
<!-- Handles both Add and Edit (JS switches)    -->
<!-- ========================================== -->
<div class="modal fade" id="planModal" tabindex="-1" role="dialog" aria-labelledby="planModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <div class="modal-header">
        <h5 class="modal-title" id="planModalTitle">Add Plan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="planForm">
          <input type="hidden" id="planId" value="">
          <input type="hidden" id="planCategory" value="membership">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Plan Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="planName" placeholder="e.g. Regular, Student Rate, Monthly" required>
            </div>
            <div class="form-group col-md-6">
              <label>Price (₱) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="planPrice" placeholder="0.00" step="0.01" min="0" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Duration (Days) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="planDurationDays" placeholder="30" min="1" required>
            </div>
            <div class="form-group col-md-6">
              <label>Duration Label</label>
              <input type="text" class="form-control" id="planDurationLabel" placeholder="e.g. Monthly, 3 Months">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Description</label>
              <input type="text" class="form-control" id="planDescription" placeholder="Brief description">
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select class="form-control" id="planIsActive">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Badge Text</label>
              <input type="text" class="form-control" id="planBadgeText" placeholder="e.g. Best Value">
            </div>
            <div class="form-group col-md-6">
              <label>Badge Color</label>
              <div class="d-flex align-items-center" style="gap: 0.75rem;">
                <input type="color" class="form-control" id="planBadgeColor" value="#FFA726" style="width: 80px; height: 52px; padding: 0.25rem; cursor: pointer;">
                <input type="text" class="form-control" id="planBadgeColorHex" value="#FFA726" placeholder="#FFA726" readonly style="flex: 1; background-color: #2c2e3e; cursor: pointer;">
              </div>
            </div>
          </div>

          <!-- Membership-only options -->
          <div id="membershipOptions">
            <hr style="border-color: rgba(255,255,255,0.1); margin: 1.75rem 0;">
            <h6 class="text-muted mb-4" style="font-size: 0.9375rem; text-transform: uppercase; letter-spacing: 0.05em;">
              Membership-Specific Options
            </h6>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Requires Student ID?</label>
                <div class="toggle-switch-container">
                  <label class="toggle-switch">
                    <input type="checkbox" id="planRequiresStudent">
                    <span class="toggle-slider"></span>
                  </label>
                  <span class="toggle-text" id="studentToggleLabel">No</span>
                </div>
              </div>
              <div class="form-group col-md-6">
                <label>Buddy Plan?</label>
                <div class="toggle-switch-container">
                  <label class="toggle-switch">
                    <input type="checkbox" id="planRequiresBuddy">
                    <span class="toggle-slider"></span>
                  </label>
                  <span class="toggle-text" id="buddyToggleLabel">No</span>
                </div>
              </div>
            </div>
            <div class="form-row" id="buddyCountGroup" style="display:none;">
              <div class="form-group col-md-6">
                <label>Number of People</label>
                <input type="number" class="form-control" id="planBuddyCount" value="2" min="2" max="10">
              </div>
              <div class="form-group col-md-6">
                <!-- Empty column for alignment -->
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-update" id="savePlanBtn" onclick="confirmSavePlan()">
          <i class="mdi mdi-content-save"></i> Save Plan
        </button>
      </div>

      <!-- Confirm Overlay (inside modal-content) -->
      <div id="planConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Plan</h5>
            <button type="button" class="close" onclick="backToPlanForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to save this plan?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Plan Name:</span>
                <span class="confirm-value" id="confirmPlanNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Category:</span>
                <span class="confirm-value" id="confirmPlanCategoryText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Price:</span>
                <span class="confirm-value" id="confirmPlanPriceText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmPlanDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToPlanForm()">Cancel</button>
            <button type="button" class="btn btn-update" id="confirmSavePlanBtn" onclick="executeSavePlan()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
