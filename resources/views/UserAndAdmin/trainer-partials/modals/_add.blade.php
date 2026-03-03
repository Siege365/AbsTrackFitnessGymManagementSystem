<!-- Add Trainer Modal -->
<div class="modal fade" id="addTrainerModal" tabindex="-1" role="dialog" aria-labelledby="addTrainerModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <div class="modal-header">
        <h5 class="modal-title" id="addTrainerModalLabel">Add New Trainer</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addTrainerForm">
        <div class="modal-body">
          <!-- Row 1: Full Name and Specialization -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Full Name <span class="text-danger">*</span></label>
              <input type="text" name="full_name" id="newTrainerName" class="form-control" placeholder="John Doe" required 
                pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed" 
                oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Specialization</label>
              <input type="text" name="specialization" id="newTrainerSpecialization" class="form-control" placeholder="e.g., Weight Training, Yoga, Boxing">
            </div>
          </div>

          <!-- Row 2: Contact Number and Emergency Contact -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Contact Number</label>
              <input type="text" name="contact_number" id="newTrainerContact" class="form-control contact-input" 
                placeholder="0912-345-6789" maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatTrainerPhone(this)">
              <div id="newTrainerContactError" class="invalid-feedback" style="display: none;"></div>
            </div>
            <div class="form-group col-md-6">
              <label>Emergency Contact Number</label>
              <input type="text" name="emergency_contact" id="newTrainerEmergencyContact" class="form-control contact-input" 
                placeholder="0912-345-6789" maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatTrainerPhone(this)">
              <div id="newTrainerEmergencyContactError" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 3: Address -->
          <div class="form-row">
            <div class="form-group col-12">
              <label>Address</label>
              <textarea name="address" id="newTrainerAddress" class="form-control" rows="2" placeholder="Enter full address"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showTrainerConfirmModal()">Submit</button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="addTrainerConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm New Trainer</h5>
            <button type="button" class="close" onclick="backToTrainerAddForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to add this trainer?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmTrainerNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Specialization:</span>
                <span class="confirm-value" id="confirmTrainerSpecText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Contact:</span>
                <span class="confirm-value" id="confirmTrainerContactText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToTrainerAddForm()">Cancel</button>
            <button type="button" class="btn btn-update" id="confirmAddTrainerBtn" onclick="submitTrainerForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
