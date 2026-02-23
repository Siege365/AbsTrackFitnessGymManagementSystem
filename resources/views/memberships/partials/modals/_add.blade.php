<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <div class="modal-header">
        <h5 class="modal-title" id="addMemberModalLabel">Add Member</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addMemberForm">
        <div class="modal-body">
          <!-- Centered Avatar -->
          <div class="text-center mb-4">
            <div id="newAvatarPreview" class="avatar-preview-container avatar-preview-lg mx-auto">
              <i class="mdi mdi-account"></i>
            </div>
            <small class="text-muted">
              <i class="mdi mdi-information-outline"></i>
              Upload avatar or provide image URL (optional)
            </small>
          </div>

          <!-- Row 1: Name and Age -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="newMemberName" class="form-control" placeholder="John Doe" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Age <span class="text-danger">*</span></label>
              <input type="number" name="age" id="newMemberAge" class="form-control" placeholder="24" min="1" max="120" required>
            </div>
          </div>

          <!-- Row 2: Sex and Contact Number -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Sex</label>
              <select name="sex" id="newMemberSex" class="form-control">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact" id="newMemberContact" class="form-control contact-input" placeholder="0912-345-6789" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" oninput="formatPhoneNumber(this)">
              <div id="newMemberContactError" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 3: Membership Plan and Start Date -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Membership Plan <span class="text-danger">*</span></label>
              <select name="plan_type" id="newMemberPlan" class="form-control" required onchange="calculateEndDate()">
                <option value="">Select Plan</option>
                @foreach($membershipPlans as $plan)
                  <option value="{{ $plan->plan_key }}" data-duration="{{ $plan->duration_days }}">{{ $plan->plan_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="newMemberStartDate" class="form-control" required min="{{ date('Y-m-d') }}" onchange="calculateEndDate()">
            </div>
          </div>

          <!-- Row 4: End Date and Avatar Upload -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>End Date</label>
              <input type="date" name="due_date" id="newMemberEndDate" class="form-control" readonly>
            </div>
            <div class="form-group col-md-6">
              <label>Avatar</label>
              <input type="file" name="avatar" id="newMemberAvatar" class="form-control mb-2" accept="image/*" onchange="previewNewAvatar()">
              <input type="text" name="avatar_url" id="newMemberAvatarUrl" class="form-control mb-2" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewNewAvatar()">
              <div class="btn-group btn-group-toggle btn-group-sm d-flex" data-toggle="buttons">
                <label class="btn btn-outline-secondary active flex-fill">
                  <input type="radio" name="avatarInputType" value="file" checked onclick="toggleAvatarInput('file')"> Upload File
                </label>
                <label class="btn btn-outline-secondary flex-fill">
                  <input type="radio" name="avatarInputType" value="url" onclick="toggleAvatarInput('url')"> Image URL
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showConfirmModal()">Submit</button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="addMemberConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Member</h5>
            <button type="button" class="close" onclick="backToAddForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to add this member?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToAddForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitMemberForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
