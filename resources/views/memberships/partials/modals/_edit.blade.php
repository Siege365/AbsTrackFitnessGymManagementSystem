<div class="modal fade" id="viewModal{{ $membership->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $membership->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="editFormContent{{ $membership->id }}">
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
            <!-- Row 1: Name and Age -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Name</label>
                <input type="text" name="name" id="editName{{ $membership->id }}" class="form-control" value="{{ $membership->name }}" required>
              </div>
              <div class="form-group col-md-6">
                <label>Age</label>
                <input type="number" name="age" id="editAge{{ $membership->id }}" class="form-control" value="{{ $membership->age }}" min="1" max="120">
              </div>
            </div>

            <!-- Row 2: Sex (Full Width) -->
            <div class="form-group">
              <label>Sex</label>
              <select name="sex" id="editSex{{ $membership->id }}" class="form-control">
                <option value="">Select sex (optional)</option>
                <option value="Male" {{ $membership->sex == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ $membership->sex == 'Female' ? 'selected' : '' }}>Female</option>
              </select>
            </div>

            <!-- Row 3: Contact Number and Membership Plan -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Contact Number</label>
                <input type="text" name="contact" id="editContact{{ $membership->id }}" class="form-control contact-input" value="{{ $membership->contact }}" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" oninput="formatPhoneNumber(this)">
                <small class="form-text text-muted">Format: 09XX-XXX-XXXX</small>
                <div id="editContact{{ $membership->id }}Error" class="invalid-feedback" style="display: none;"></div>
              </div>
              <div class="form-group col-md-6">
                <label>Membership Plan</label>
                <select name="plan_type" id="editPlanType{{ $membership->id }}" class="form-control" required onchange="calculateEditEndDate({{ $membership->id }})">
                  @foreach($membershipPlans as $plan)
                    <option value="{{ $plan->plan_key }}" data-duration="{{ $plan->duration_days }}" {{ $membership->plan_type == $plan->plan_key ? 'selected' : '' }}>{{ $plan->plan_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- Row 4: Start Date and End Date -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Start Date</label>
                <input type="date" name="start_date" id="editStartDate{{ $membership->id }}" class="form-control" value="{{ $membership->start_date->format('Y-m-d') }}" required onchange="calculateEditEndDate({{ $membership->id }})">
              </div>
              <div class="form-group col-md-6">
                <label>End Date</label>
                <input type="date" name="due_date" id="editEndDate{{ $membership->id }}" class="form-control" value="{{ $membership->due_date->format('Y-m-d') }}" readonly>
              </div>
            </div>

            <!-- Row 5: Avatar (Full Width) -->
            <div class="form-group">
              <label>Avatar (optional)</label>
              <div class="mb-2">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-sm btn-outline-primary active">
                    <input type="radio" name="editAvatarInputType{{ $membership->id }}" value="file" checked onclick="toggleEditAvatarInput({{ $membership->id }}, 'file')"> Upload File
                  </label>
                  <label class="btn btn-sm btn-outline-primary">
                    <input type="radio" name="editAvatarInputType{{ $membership->id }}" value="url" onclick="toggleEditAvatarInput({{ $membership->id }}, 'url')"> Image URL
                  </label>
                </div>
              </div>
              <div class="text-center">
                <div id="avatarPreview{{ $membership->id }}" class="mb-2">
                  @if($membership->avatar)
                    <img src="{{ asset('storage/' . $membership->avatar) }}" alt="{{ $membership->name }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255, 255, 255, 0.2);">
                  @else
                    <div style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: white; font-weight: 600;">
                      {{ strtoupper(substr($membership->name, 0, 1)) }}
                    </div>
                  @endif
                </div>
                <input type="file" name="avatar" id="avatarInput{{ $membership->id }}" class="form-control mb-2" accept="image/*" onchange="previewAvatar({{ $membership->id }})">
                <input type="text" name="avatar_url" id="avatarUrl{{ $membership->id }}" class="form-control" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewAvatarUrl({{ $membership->id }})">
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
      </div>

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
