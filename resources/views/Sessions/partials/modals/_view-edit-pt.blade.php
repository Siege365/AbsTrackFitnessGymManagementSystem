<!-- View/Edit PT Schedule Modal -->
<div class="modal fade" id="viewEditPTModal" tabindex="-1" role="dialog" aria-labelledby="viewEditPTLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEditPTLabel">View PT Schedule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPTScheduleForm" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="pt_id" id="edit_pt_id">
                <input type="hidden" id="edit_last_updated_at" name="last_updated_at">
                <div class="modal-body">
                    <!-- Centered Avatar -->
                    <div class="text-center mb-4">
                        <div id="edit_pt_avatar_preview"
                            class="avatar-preview-container avatar-preview-lg mx-auto">
                            <i class="mdi mdi-account"></i>
                        </div>
                        <p id="edit_pt_display_name" class="text-muted mt-2 mb-0"></p>
                    </div>

                    <div class="form-row">
                        <!-- Left Column: Client Info -->
                        <div class="form-group col-md-6">
                            <label>Name</label>
                            <input type="text" class="form-control" id="edit_pt_name" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Trainer <span id="editableTag"  class="badge badge-sm" style="font-size:10px;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-weight:400;"><i class="mdi mdi-pencil"></i></span></label>
                            <select name="trainer_name" id="edit_trainer" class="form-control edit-field"
                                disabled>
                                @foreach ($trainers ?? [] as $trainer)
                                    <option value="{{ $trainer }}">{{ $trainer }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Age</label>
                            <input type="text" class="form-control" id="edit_pt_age" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Scheduled Date <span id="editableTag"  class="badge badge-sm" style="font-size:10px;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-weight:400;"><i class="mdi mdi-pencil"></i></span></label>
                            <input type="date" name="scheduled_date" id="edit_date" value=""
                                class="form-control edit-field" disabled>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Gender</label>
                            <input type="text" class="form-control" id="edit_pt_sex" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Time <span id="editableTag"  class="badge badge-sm" style="font-size:10px;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-weight:400;"><i class="mdi mdi-pencil"></i></span></label>
                            <select name="scheduled_time" id="edit_time" class="form-control edit-field"
                                disabled>
                                <option value="06:00">6:00 AM</option>
                                <option value="07:00">7:00 AM</option>
                                <option value="08:00">8:00 AM</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="13:00">1:00 PM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="17:00">5:00 PM</option>
                                <option value="18:00">6:00 PM</option>
                                <option value="19:00">7:00 PM</option>
                                <option value="20:00">8:00 PM</option>
                                <option value="21:00">9:00 PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Contact Number</label>
                            <input type="text" class="form-control" id="edit_pt_contact" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Payment Type <span id="editableTag"  class="badge badge-sm" style="font-size:10px;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-weight:400;"><i class="mdi mdi-pencil"></i></span></label>
                            <select name="payment_type" id="edit_payment" class="form-control edit-field"
                                disabled>
                                <option value="">Select Payment Type</option>
                                <option value="Cash">Cash</option>
                                <option value="Gcash">Gcash</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Subscription Type</label>
                            <input type="text" class="form-control" id="edit_pt_plan" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <input type="text" class="form-control" id="edit_pt_status" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-update" id="enableEditBtn"
                        onclick="SessionsPage.enableEdit()"><i class="mdi mdi-pencil"></i> Edit</button>
                    <button type="submit" class="btn btn-update d-none" id="saveEditBtn"><i
                            class="mdi mdi-check"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
