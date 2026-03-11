<!-- Book Next Session Modal -->
<div class="modal fade" id="bookNextModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Next Session</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bookNextForm" novalidate>
                @csrf
                <input type="hidden" name="source_session_id" id="book_source_session_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" class="form-control" id="book_client_name" readonly>
                    </div>
                    <div class="form-group">
                        <label>Trainer</label>
                        <select name="trainer_name" id="book_trainer_name" class="form-control">
                            <option value="">Select Trainer</option>
                            @foreach ($trainers ?? [] as $trainer)
                                <option value="{{ $trainer }}">{{ $trainer }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="scheduled_date" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" id="book_payment_type" class="form-control">
                            <option value="Cash">Cash</option>
                            <option value="Gcash">Gcash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <select name="scheduled_time" class="form-control">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-update">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
