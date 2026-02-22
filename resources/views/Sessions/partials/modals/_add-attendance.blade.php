<!-- Add Attendance Modal -->
<div class="modal fade" id="addAttendanceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Customer Attendance</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addAttendanceForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Customer Name <span class="text-danger">*</span></label>
                        <input type="text" id="attendance_customer_select" class="form-control" 
                            placeholder="Type customer name..." autocomplete="off" required
                            pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes are allowed"
                            oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')">
                        <input type="hidden" id="attendance_customer_id">
                        <input type="hidden" id="attendance_customer_type">
                        <small class="text-muted mt-1 d-block">
                            <i class="mdi mdi-information-outline"></i>
                            Type a new name for walk-in customers
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" id="attendance_date" class="form-control"
                            value="{{ date('Y-m-d') }}" readonly required>
                        <small class="text-muted">Auto-set to today's date</small>
                    </div>
                    <div class="form-group">
                        <label>Time In</label>
                        <input type="time" name="time_in" id="attendance_time" class="form-control"
                            value="{{ date('H:i') }}" readonly required>
                        <small class="text-muted">Auto-set to current time</small>
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
