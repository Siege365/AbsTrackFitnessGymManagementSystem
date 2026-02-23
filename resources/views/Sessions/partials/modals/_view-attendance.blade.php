<!-- View Attendance Details Modal -->
<div class="modal fade" id="viewAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="viewAttendanceLabel">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAttendanceLabel">Attendance Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Centered Avatar -->
                <div class="text-center mb-3">
                    <div id="view_att_avatar_preview"
                        class="avatar-preview-container avatar-preview-md mx-auto">
                        <i class="mdi mdi-account"></i>
                    </div>
                    <h5 id="view_att_name" class="mt-2 mb-1"></h5>
                    <p id="view_att_contact" class="text-muted mb-0 small"></p>
                </div>

                <!-- Check-in Information -->
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0"><i class="mdi mdi-clock-check-outline mr-2"></i>Check-in Information</h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-0">
                                <label class="text-muted small mb-1">Date</label>
                                <p id="view_att_date" class="mb-0"></p>
                            </div>
                            <div class="form-group col-md-6 mb-0">
                                <label class="text-muted small mb-1">Time In</label>
                                <p id="view_att_time_in" class="mb-0"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions Row (Side by Side) -->
                <div class="row">
                    <!-- Membership Subscription (if exists) -->
                    <div id="view_att_membership_section" class="col-md-12" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="mdi mdi-dumbbell mr-2"></i>Gym Membership</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label class="text-muted small mb-1">Subscription Type</label>
                                        <p id="view_att_membership_plan" class="mb-0"></p>
                                    </div>
                                    <div class="form-group col-md-6 mb-2">
                                        <label class="text-muted small mb-1">Status</label>
                                        <div id="view_att_membership_status"></div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-0">
                                        <label class="text-muted small mb-1">Start Date</label>
                                        <p id="view_att_membership_start" class="mb-0"></p>
                                    </div>
                                    <div class="form-group col-md-6 mb-0">
                                        <label class="text-muted small mb-1">End Date</label>
                                        <p id="view_att_membership_end" class="mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Subscription (if exists) -->
                    <div id="view_att_client_section" class="col-md-12" style="display: none;">
                        <div class="card mb-3">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="mdi mdi-account-supervisor mr-2"></i>Personal Training</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-2">
                                        <label class="text-muted small mb-1">Subscription Type</label>
                                        <p id="view_att_client_plan" class="mb-0"></p>
                                    </div>
                                    <div class="form-group col-md-6 mb-2">
                                        <label class="text-muted small mb-1">Status</label>
                                        <div id="view_att_client_status"></div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-0">
                                        <label class="text-muted small mb-1">Start Date</label>
                                        <p id="view_att_client_start" class="mb-0"></p>
                                    </div>
                                    <div class="form-group col-md-6 mb-0">
                                        <label class="text-muted small mb-1">End Date</label>
                                        <p id="view_att_client_end" class="mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Walk-in Notice (if no subscriptions) -->
                <div id="view_att_walkin_notice" class="alert alert-info mb-0" style="display: none;">
                    <i class="mdi mdi-information-outline mr-2"></i>
                    This is a walk-in customer with no active subscriptions.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
