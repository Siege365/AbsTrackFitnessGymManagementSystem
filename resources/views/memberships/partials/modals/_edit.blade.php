<div class="modal fade" id="viewModal{{ $membership->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $membership->id }}">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <form id="editMemberForm{{ $membership->id }}" data-membership-id="{{ $membership->id }}" data-action="{{ route('memberships.update', $membership) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="viewModalLabel{{ $membership->id }}">Edit Member</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Centered Avatar -->
          <div class="text-center mb-4">
            <div id="avatarPreview{{ $membership->id }}" class="avatar-preview-container avatar-preview-lg mx-auto">
              @if($membership->avatar)
                <img src="{{ asset('storage/' . $membership->avatar) }}" alt="{{ $membership->name }}">
              @else
                <div class="avatar-initial">{{ strtoupper(substr($membership->name, 0, 1)) }}</div>
              @endif
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
              <input type="text" name="name" id="editName{{ $membership->id }}" class="form-control" value="{{ $membership->name }}" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Age</label>
              <input type="number" name="age" id="editAge{{ $membership->id }}" class="form-control" value="{{ $membership->age }}" min="1" max="120">
            </div>
          </div>

          <!-- Row 2: Sex and Contact Number -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Sex</label>
              <select name="sex" id="editSex{{ $membership->id }}" class="form-control">
                <option value="">Select Gender</option>
                <option value="Male" {{ $membership->sex == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ $membership->sex == 'Female' ? 'selected' : '' }}>Female</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact" id="editContact{{ $membership->id }}" class="form-control contact-input" value="{{ $membership->contact }}" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" oninput="formatPhoneNumber(this)">
              <div id="editContact{{ $membership->id }}Error" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 3: Membership Plan and Start Date -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Membership Plan <span class="text-danger">*</span></label>
              <select name="plan_type" id="editPlanType{{ $membership->id }}" class="form-control" required onchange="calculateEditEndDate({{ $membership->id }})">
                @foreach($membershipPlans as $plan)
                  <option value="{{ $plan->plan_key }}" data-duration="{{ $plan->duration_days }}" {{ $membership->plan_type == $plan->plan_key ? 'selected' : '' }}>{{ $plan->plan_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="editStartDate{{ $membership->id }}" class="form-control" value="{{ $membership->start_date->format('Y-m-d') }}" required onchange="calculateEditEndDate({{ $membership->id }})">
            </div>
          </div>

          <!-- Row 4: End Date and Avatar Upload -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>End Date</label>
              <input type="date" name="due_date" id="editEndDate{{ $membership->id }}" class="form-control" value="{{ $membership->due_date->format('Y-m-d') }}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label>Avatar</label>
              <input type="file" name="avatar" id="avatarInput{{ $membership->id }}" class="form-control mb-2" accept="image/*" onchange="previewAvatar({{ $membership->id }})">
              <input type="text" name="avatar_url" id="avatarUrl{{ $membership->id }}" class="form-control mb-2" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewAvatarUrl({{ $membership->id }})">
              <div class="btn-group btn-group-toggle btn-group-sm d-flex" data-toggle="buttons">
                <label class="btn btn-outline-secondary active flex-fill">
                  <input type="radio" name="editAvatarInputType{{ $membership->id }}" value="file" checked onclick="toggleEditAvatarInput({{ $membership->id }}, 'file')"> Upload File
                </label>
                <label class="btn btn-outline-secondary flex-fill">
                  <input type="radio" name="editAvatarInputType{{ $membership->id }}" value="url" onclick="toggleEditAvatarInput({{ $membership->id }}, 'url')"> Image URL
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showEditConfirmModal({{ $membership->id }})">
            <i class="mdi mdi-pencil"></i> Update
          </button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="editConfirmOverlay{{ $membership->id }}" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-pencil-outline"></i>
            <h5>Confirm Update</h5>
            <button type="button" class="close" onclick="backToEditForm({{ $membership->id }})">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to update this member?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmEditName{{ $membership->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmEditPlan{{ $membership->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmEditDuration{{ $membership->id }}"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToEditForm({{ $membership->id }})">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitEditForm({{ $membership->id }})">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
