<!-- Reschedule Offer Modal -->
<div class="modal fade" id="rescheduleOfferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reschedule Session?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="mdi mdi-calendar-question text-info" style="font-size: 48px;"></i>
                </div>
                <p class="text-center">Would <strong id="rescheduleClientName"></strong> like to reschedule their booking?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">No, Thanks</button>
                <button type="button" class="btn btn-update" onclick="SessionsPage.openRescheduleBooking()"><i class="mdi mdi-calendar-plus"></i> Yes, Reschedule</button>
            </div>
        </div>
    </div>
</div>
