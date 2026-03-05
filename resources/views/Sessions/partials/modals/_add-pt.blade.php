<!-- Add PT Schedule Modal -->
<div class="modal fade" id="addPTScheduleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add PT Schedule</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addPTScheduleForm" novalidate>
                @csrf
                <div class="modal-body">
                    <!-- Centered Avatar -->
                    <div class="text-center mb-4">
                        <div id="pt_avatar_preview"
                            class="avatar-preview-container avatar-preview-lg mx-auto">
                            <i class="mdi mdi-account"></i>
                        </div>
                        <small class="text-muted">
                            <i class="mdi mdi-information-outline"></i>
                            Search existing customer or type a new name for walk-in
                        </small>
                    </div>

                    <div class="form-row">
                        <!-- Left Column -->
                        <div class="form-group col-md-6">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" id="pt_customer_select" class="form-control" 
                                placeholder="Enter customer name" autocomplete="off">
                            <input type="hidden" id="pt_customer_id" name="client_id">
                            <input type="hidden" id="pt_customer_type">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Trainer <span class="text-danger">*</span></label>
                            <select name="trainer_name" id="pt_trainer" class="form-control">
                                <option value=""></option>
                                @foreach ($trainers ?? [] as $trainer)
                                    <option value="{{ $trainer }}">{{ $trainer }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Age</label>
                            <input type="number" class="form-control" id="pt_age" min="1" max="120"
                                placeholder="Enter age" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Scheduled Date <span class="text-danger">*</span></label>
                            <input type="date" name="scheduled_date" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Gender</label>
                            <select class="form-control" id="pt_sex" disabled>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Time <span class="text-danger">*</span></label>
                            <select name="scheduled_time" class="form-control">
                                <option value="">Select Time</option>
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
                            <input type="text" class="form-control" id="pt_contact"
                                placeholder="0912-345-6789" readonly maxlength="13" oninput="formatPhoneNumber(this)">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-control">
                                <option value="">Select Payment Type</option>
                                <option value="Cash">Cash</option>
                                <option value="Gcash">Gcash</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Subscription Type</label>
                            <input type="text" class="form-control" id="pt_plan" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-update">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
