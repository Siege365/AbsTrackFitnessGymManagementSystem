<div class="modal fade" id="viewModal{{ $client->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $client->id }}">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <form id="editClientForm{{ $client->id }}" data-action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="viewModalLabel{{ $client->id }}">Edit Client</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Centered Avatar -->
          <div class="text-center mb-4">
            <div id="avatarPreview{{ $client->id }}" class="avatar-preview-container avatar-preview-lg mx-auto">
              @if($client->avatar)
                <img src="{{ asset('storage/' . $client->avatar) }}" alt="{{ $client->name }}">
              @else
                <div class="avatar-initial">{{ strtoupper(substr($client->name, 0, 1)) }}</div>
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
              <input type="text" name="name" id="editClientName{{ $client->id }}" class="form-control" value="{{ $client->name }}" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Age</label>
              <input type="number" name="age" id="editClientAge{{ $client->id }}" class="form-control" value="{{ $client->age }}" min="1" max="120">
            </div>
          </div>

          <!-- Row 2: Sex and Contact Number -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Sex</label>
              <select name="sex" id="editClientSex{{ $client->id }}" class="form-control">
                <option value="">Select Gender</option>
                <option value="Male" {{ $client->sex == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ $client->sex == 'Female' ? 'selected' : '' }}>Female</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact" id="editClientContact{{ $client->id }}" class="form-control contact-input" value="{{ $client->contact }}" required maxlength="18" title="Enter format: 09XX-XXX-XXXX" oninput="formatPhoneNumber(this)">
              <div id="editClientContact{{ $client->id }}Error" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 3: Subscription Type and Start Date -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Subscription Type <span class="text-danger">*</span></label>
              <select name="plan_type" id="editClientPlanType{{ $client->id }}" class="form-control" required onchange="calculateEditClientEndDate({{ $client->id }})">
                @foreach($ptPlans as $plan)
                  <option value="{{ $plan->plan_key }}" data-duration="{{ $plan->duration_days }}" {{ $client->plan_type == $plan->plan_key ? 'selected' : '' }}>{{ $plan->plan_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="editClientStartDate{{ $client->id }}" class="form-control" value="{{ $client->start_date->format('Y-m-d') }}" required onchange="calculateEditClientEndDate({{ $client->id }})">
            </div>
          </div>

          <!-- Row 4: End Date and Avatar Upload -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>End Date</label>
              <input type="date" name="due_date" id="editClientEndDate{{ $client->id }}" class="form-control" value="{{ $client->due_date->format('Y-m-d') }}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label>Avatar</label>
              <input type="file" name="avatar" id="avatarInput{{ $client->id }}" class="form-control mb-2" accept="image/*" onchange="previewAvatar({{ $client->id }})">
              <input type="text" name="avatar_url" id="avatarUrl{{ $client->id }}" class="form-control mb-2" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewClientAvatarUrl({{ $client->id }})">
              <div class="btn-group btn-group-toggle btn-group-sm d-flex" data-toggle="buttons">
                <label class="btn btn-outline-secondary active flex-fill">
                  <input type="radio" name="editClientAvatarInputType{{ $client->id }}" value="file" checked onclick="toggleEditClientAvatarInput({{ $client->id }}, 'file')"> Upload File
                </label>
                <label class="btn btn-outline-secondary flex-fill">
                  <input type="radio" name="editClientAvatarInputType{{ $client->id }}" value="url" onclick="toggleEditClientAvatarInput({{ $client->id }}, 'url')"> Image URL
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showEditClientConfirmModal({{ $client->id }})">
            <i class="mdi mdi-pencil"></i> Update
          </button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="editClientConfirmOverlay{{ $client->id }}" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-pencil-outline"></i>
            <h5>Confirm Update</h5>
            <button type="button" class="close" onclick="backToEditClientForm({{ $client->id }})">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to update this client?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmEditClientName{{ $client->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmEditClientPlan{{ $client->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmEditClientDuration{{ $client->id }}"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToEditClientForm({{ $client->id }})">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitEditClientForm({{ $client->id }})">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
