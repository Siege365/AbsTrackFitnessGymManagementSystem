<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <div class="modal-header">
        <h5 class="modal-title" id="addStaffModalLabel">Add New Staff Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addStaffForm">
        <div class="modal-body">
          <!-- Row 1: Full Name and Email -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="newStaffName" class="form-control" placeholder="John Doe" required 
                pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed" 
                oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="newStaffEmail" class="form-control" placeholder="staff@abstrack.com" required>
            </div>
          </div>

          <!-- Row 2: Password and Confirm Password -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Password <span class="text-danger">*</span></label>
              <div class="input-group password-group">
                <input type="password" name="password" id="newStaffPassword" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                <div class="input-group-append">
                  <button class="btn password-toggle-btn toggle-password" type="button" onclick="togglePasswordVisibility('newStaffPassword', this)">
                    <i class="mdi mdi-eye-off"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Confirm Password <span class="text-danger">*</span></label>
              <div class="input-group password-group">
                <input type="password" name="password_confirmation" id="newStaffPasswordConfirm" class="form-control" placeholder="Re-enter password" required minlength="8">
                <div class="input-group-append">
                  <button class="btn password-toggle-btn toggle-password" type="button" onclick="togglePasswordVisibility('newStaffPasswordConfirm', this)">
                    <i class="mdi mdi-eye-off"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Row 3: Contact Number and Emergency Contact -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact_number" id="newStaffContact" class="form-control contact-input" 
                placeholder="0912-345-6789" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatPhoneNumber(this)">
              <div id="newStaffContactError" class="invalid-feedback" style="display: none;"></div>
            </div>
            <div class="form-group col-md-6">
              <label>Emergency Contact Number</label>
              <input type="text" name="emergency_contact" id="newStaffEmergencyContact" class="form-control contact-input" 
                placeholder="0912-345-6789" maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatPhoneNumber(this)">
              <div id="newStaffEmergencyContactError" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 4: Address -->
          <div class="form-row">
            <div class="form-group col-12">
              <label>Address</label>
              <textarea name="address" id="newStaffAddress" class="form-control" rows="2" placeholder="Enter full address"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showStaffConfirmModal()">Submit</button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="addStaffConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm New Staff</h5>
            <button type="button" class="close" onclick="backToStaffAddForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to create this staff account?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmStaffNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Email:</span>
                <span class="confirm-value" id="confirmStaffEmailText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Contact:</span>
                <span class="confirm-value" id="confirmStaffContactText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToStaffAddForm()">Cancel</button>
            <button type="button" class="btn btn-update" id="confirmAddStaffBtn" onclick="submitStaffForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
