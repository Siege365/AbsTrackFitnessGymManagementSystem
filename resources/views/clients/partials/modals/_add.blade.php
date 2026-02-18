<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="addClientFormContent">
        <div class="modal-header">
          <h5 class="modal-title" id="addClientModalLabel">Add Client</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addClientForm">
            <!-- Row 1: Name and Age -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Name</label>
                <input type="text" name="name" id="newClientName" class="form-control" placeholder="John Doe" required>
              </div>
              <div class="form-group col-md-6">
                <label>Age</label>
                <input type="number" name="age" id="newClientAge" class="form-control" placeholder="24" min="1" max="120" required>
              </div>
            </div>

            <!-- Row 2: Sex (Full Width) -->
            <div class="form-group">
              <label>Sex</label>
              <select name="sex" id="newClientSex" class="form-control">
                <option value="">Select sex (optional)</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <!-- Row 3: Contact Number and Plan Type -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Contact Number</label>
                <input type="text" name="contact" id="newClientContact" class="form-control contact-input" placeholder="0912-345-6789" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" oninput="formatPhoneNumber(this)">
                <small class="form-text text-muted">Format: 09XX-XXX-XXXX</small>
                <div id="newClientContactError" class="invalid-feedback" style="display: none;"></div>
              </div>
              <div class="form-group col-md-6">
                <label>Plan Type</label>
                <select name="plan_type" id="newClientPlan" class="form-control" required onchange="calculateClientEndDate()">
                  <option value="">Select Plan</option>
                  @foreach($ptPlans as $plan)
                    <option value="{{ $plan->plan_key }}" data-duration="{{ $plan->duration_days }}">{{ $plan->plan_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- Row 4: Start Date and End Date -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Start Date</label>
                <input type="date" name="start_date" id="newClientStartDate" class="form-control" required onchange="calculateClientEndDate()">
              </div>
              <div class="form-group col-md-6">
                <label>End Date</label>
                <input type="date" name="due_date" id="newClientEndDate" class="form-control" readonly>
              </div>
            </div>

            <!-- Row 5: Avatar (Full Width) -->
            <div class="form-group">
              <label>Avatar (optional)</label>
              <div class="mb-2">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-sm btn-outline-primary active">
                    <input type="radio" name="clientAvatarInputType" value="file" checked onclick="toggleClientAvatarInput('file')"> Upload File
                  </label>
                  <label class="btn btn-sm btn-outline-primary">
                    <input type="radio" name="clientAvatarInputType" value="url" onclick="toggleClientAvatarInput('url')"> Image URL
                  </label>
                </div>
              </div>
              <input type="file" name="avatar" id="newClientAvatar" class="form-control" accept="image/*" onchange="previewNewClientAvatar()">
              <input type="text" name="avatar_url" id="newClientAvatarUrl" class="form-control" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewNewClientAvatar()">
              <div id="newClientAvatarPreview" class="mt-3 text-center"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showClientConfirmModal()">Submit</button>
        </div>
      </div>

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
