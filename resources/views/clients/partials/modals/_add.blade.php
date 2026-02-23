<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <div class="modal-header">
        <h5 class="modal-title" id="addClientModalLabel">Add Client</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addClientForm">
        <div class="modal-body">
          <!-- Centered Avatar -->
          <div class="text-center mb-4">
            <div id="newClientAvatarPreview" class="avatar-preview-container avatar-preview-lg mx-auto">
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
              <input type="text" name="name" id="newClientName" class="form-control" placeholder="John Doe" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Age <span class="text-danger">*</span></label>
              <input type="number" name="age" id="newClientAge" class="form-control" placeholder="24" min="1" max="120" required>
            </div>
          </div>

          <!-- Row 2: Sex and Contact Number -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Sex</label>
              <select name="sex" id="newClientSex" class="form-control">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact" id="newClientContact" class="form-control contact-input" placeholder="0912-345-6789" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" oninput="formatPhoneNumber(this)">
              <div id="newClientContactError" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 3: Subscription Type and Start Date -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Subscription Type <span class="text-danger">*</span></label>
              <select name="plan_type" id="newClientPlan" class="form-control" required onchange="calculateClientEndDate()">
                <option value="">Select Plan</option>
                @foreach($ptPlans as $plan)
                  <option value="{{ $plan->plan_key }}" data-duration="{{ $plan->duration_days }}">{{ $plan->plan_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="newClientStartDate" class="form-control" required min="{{ date('Y-m-d') }}" onchange="calculateClientEndDate()">
            </div>
          </div>

          <!-- Row 4: End Date and Avatar Upload -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>End Date</label>
              <input type="date" name="due_date" id="newClientEndDate" class="form-control" readonly>
            </div>
            <div class="form-group col-md-6">
              <label>Avatar</label>
              <input type="file" name="avatar" id="newClientAvatar" class="form-control mb-2" accept="image/*" onchange="previewNewClientAvatar()">
              <input type="text" name="avatar_url" id="newClientAvatarUrl" class="form-control mb-2" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewNewClientAvatar()">
              <div class="btn-group btn-group-toggle btn-group-sm d-flex" data-toggle="buttons">
                <label class="btn btn-outline-secondary active flex-fill">
                  <input type="radio" name="clientAvatarInputType" value="file" checked onclick="toggleClientAvatarInput('file')"> Upload File
                </label>
                <label class="btn btn-outline-secondary flex-fill">
                  <input type="radio" name="clientAvatarInputType" value="url" onclick="toggleClientAvatarInput('url')"> Image URL
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showClientConfirmModal()">Submit</button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="addClientConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Client</h5>
            <button type="button" class="close" onclick="backToClientAddForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to add this client?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmClientNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmClientPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmClientDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToClientAddForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitClientForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
