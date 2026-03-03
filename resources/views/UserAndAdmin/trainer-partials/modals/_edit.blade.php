<!-- Edit Trainer Modal -->
<div class="modal fade" id="editTrainerModal{{ $trainer->id }}" tabindex="-1" role="dialog" aria-labelledby="editTrainerModalLabel{{ $trainer->id }}">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <form id="editTrainerForm{{ $trainer->id }}" data-action="{{ route('trainers.update', $trainer->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editTrainerModalLabel{{ $trainer->id }}">Edit Trainer</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Row 1: Full Name and Specialization -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Full Name <span class="text-danger">*</span></label>
              <input type="text" name="full_name" id="editTrainerName{{ $trainer->id }}" class="form-control" 
                value="{{ $trainer->full_name }}" required pattern="[A-Za-z\s\-']+" 
                title="Only letters, spaces, hyphens, and apostrophes are allowed" 
                oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
            </div>
            <div class="form-group col-md-6">
              <label>Specialization</label>
              <input type="text" name="specialization" id="editTrainerSpecialization{{ $trainer->id }}" class="form-control" 
                value="{{ $trainer->specialization }}" placeholder="e.g., Weight Training, Yoga, Boxing">
            </div>
          </div>

          <!-- Row 2: Contact Number and Emergency Contact -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Contact Number</label>
              <input type="text" name="contact_number" id="editTrainerContact{{ $trainer->id }}" class="form-control contact-input" 
                value="{{ $trainer->contact_number }}" maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatTrainerPhone(this)">
              <div id="editTrainerContact{{ $trainer->id }}Error" class="invalid-feedback" style="display: none;"></div>
            </div>
            <div class="form-group col-md-6">
              <label>Emergency Contact Number</label>
              <input type="text" name="emergency_contact" id="editTrainerEmergencyContact{{ $trainer->id }}" class="form-control contact-input" 
                value="{{ $trainer->emergency_contact }}" maxlength="18" title="Enter format: 09XX-XXX-XXXX" 
                oninput="formatTrainerPhone(this)">
              <div id="editTrainerEmergencyContact{{ $trainer->id }}Error" class="invalid-feedback" style="display: none;"></div>
            </div>
          </div>

          <!-- Row 3: Address -->
          <div class="form-row">
            <div class="form-group col-12">
              <label>Address</label>
              <textarea name="address" id="editTrainerAddress{{ $trainer->id }}" class="form-control" rows="2" 
                placeholder="Enter full address">{{ $trainer->address }}</textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showEditTrainerConfirmModal({{ $trainer->id }})">
            <i class="mdi mdi-pencil"></i> Update
          </button>
        </div>
      </form>

      <!-- Confirmation Overlay -->
      <div id="editTrainerConfirmOverlay{{ $trainer->id }}" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Update</h5>
            <button type="button" class="close" onclick="backToEditTrainerForm({{ $trainer->id }})">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to update this trainer?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmEditTrainerName{{ $trainer->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Specialization:</span>
                <span class="confirm-value" id="confirmEditTrainerSpec{{ $trainer->id }}"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Contact:</span>
                <span class="confirm-value" id="confirmEditTrainerContact{{ $trainer->id }}"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToEditTrainerForm({{ $trainer->id }})">Cancel</button>
            <button type="button" class="btn btn-update" id="confirmEditTrainerBtn{{ $trainer->id }}" onclick="submitEditTrainerForm({{ $trainer->id }})">
              <i class="mdi mdi-check"></i> Confirm Update
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
