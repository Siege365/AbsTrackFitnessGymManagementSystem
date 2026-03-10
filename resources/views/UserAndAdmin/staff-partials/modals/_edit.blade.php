<!-- View/Edit Staff Modal -->
<div class="modal fade" id="editStaffModal{{ $member->id }}" tabindex="-1" role="dialog" aria-labelledby="editStaffModalLabel{{ $member->id }}">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content js-staff-view-edit-modal is-view-mode" data-staff-id="{{ $member->id }}" style="position: relative;">
      <form id="editStaffForm{{ $member->id }}" data-action="{{ route('staff.update', $member->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editStaffModalLabel{{ $member->id }}">Staff Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Row 1: Full Name and Email -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editStaffName{{ $member->id }}" class="form-control" 
                value="{{ $member->name }}" required pattern="[A-Za-z\s\-']+" 
                title="Only letters, spaces, hyphens, and apostrophes are allowed" 
                oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')" data-editable-field>
            </div>
            <div class="form-group col-md-6">
              <label>Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="editStaffEmail{{ $member->id }}" class="form-control" 
                value="{{ $member->email }}" required data-editable-field>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-12">
              <label>Current Password</label>
              <input type="text" class="form-control" value="Encrypted and not viewable for security" readonly>
              <small class="text-muted">Use Edit mode to set a new password.</small>
            </div>
          </div>

          <!-- Row 2: Password (optional on edit) -->
          <div class="form-row" id="editStaffPasswordRow{{ $member->id }}" style="display: none;">
            <div class="form-group col-md-6">
              <label>New Password</label>
              <div class="input-group password-group">
                <input type="password" name="password" id="editStaffPassword{{ $member->id }}" class="form-control" 
                  placeholder="Leave blank to keep current" minlength="8">
                <div class="input-group-append">
                  <button class="btn password-toggle-btn toggle-password" type="button" 
                    onclick="togglePasswordVisibility('editStaffPassword{{ $member->id }}', this)">
                    <i class="mdi mdi-eye-off"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Confirm New Password</label>
              <div class="input-group password-group">
                <input type="password" name="password_confirmation" id="editStaffPasswordConfirm{{ $member->id }}" class="form-control" 
                  placeholder="Re-enter new password" minlength="8">
                <div class="input-group-append">
                  <button class="btn password-toggle-btn toggle-password" type="button" 
                    onclick="togglePasswordVisibility('editStaffPasswordConfirm{{ $member->id }}', this)">
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
              <input type="text" name="contact_number" id="editStaffContact{{ $member->id }}" class="form-control contact-input" 
                value="{{ $member->contact_number }}" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatPhoneNumber(this)" data-editable-field>
              <div id="editStaffContact{{ $member->id }}Error" class="invalid-feedback" style="display: none;"></div>
            </div>
            <div class="form-group col-md-6">
              <label>Emergency Contact Number</label>
              <input type="text" name="emergency_contact" id="editStaffEmergencyContact{{ $member->id }}" class="form-control contact-input" 
                value="{{ $member->emergency_contact }}" maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatPhoneNumber(this)" data-editable-field>
              <div id="editStaffEmergencyContact{{ $member->id }}Error" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 4: Address -->
          <div class="form-row">
            <div class="form-group col-12">
              <label>Address</label>
              <textarea name="address" id="editStaffAddress{{ $member->id }}" class="form-control" rows="2" 
                placeholder="Enter full address" data-editable-field>{{ $member->address }}</textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-update" id="editStaffToggleBtn{{ $member->id }}" onclick="enterStaffEditMode({{ $member->id }})">
            <i class="mdi mdi-pencil"></i> Edit
          </button>
          <button type="button" class="btn btn-update" id="saveStaffBtn{{ $member->id }}" onclick="showEditStaffConfirmModal({{ $member->id }})" style="display: none;">
            <i class="mdi mdi-content-save"></i> Save Changes
          </button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="editStaffConfirmOverlay{{ $member->id }}" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Update</h5>
            <button type="button" class="close" onclick="backToEditStaffForm({{ $member->id }})">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to update this staff account?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmEditStaffName{{ $member->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Email:</span>
                <span class="confirm-value" id="confirmEditStaffEmail{{ $member->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Password:</span>
                <span class="confirm-value" id="confirmEditStaffPassword{{ $member->id }}"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToEditStaffForm({{ $member->id }})">Cancel</button>
            <button type="button" class="btn btn-update" id="confirmEditStaffBtn{{ $member->id }}" onclick="submitEditStaffForm({{ $member->id }})">
              <i class="mdi mdi-check"></i> Confirm Update
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
