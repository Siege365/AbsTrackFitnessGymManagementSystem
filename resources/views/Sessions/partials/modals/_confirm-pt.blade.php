<!-- Confirm PT Schedule Modal -->
<div class="modal fade" id="confirmPTModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm PT Schedule</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Please confirm the following PT schedule details:</p>
                <table class="table table-borderless table-sm mb-0" id="confirmPTDetails">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 140px;">Customer</td>
                            <td><strong id="confirmPT_name"></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trainer</td>
                            <td id="confirmPT_trainer"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date</td>
                            <td id="confirmPT_date"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Time</td>
                            <td id="confirmPT_time"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment</td>
                            <td id="confirmPT_payment"></td>
                        </tr>
                        <tr id="confirmPT_type_row">
                            <td class="text-muted">Type</td>
                            <td id="confirmPT_type"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="SessionsPage.goBackToPTForm()">Go Back</button>
                <button type="button" class="btn btn-update" id="confirmPTSubmitBtn"
                    onclick="SessionsPage.executeSubmitPT()">
                    <i class="mdi mdi-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>
