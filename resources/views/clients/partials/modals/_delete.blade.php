<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteClientConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteClientConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteClientConfirmModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this client? This action cannot be undone.
        </div>

        <div class="form-group">
          <label>Client Name</label>
          <div class="form-control" id="deleteClientName" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Subscription Plan</label>
          <div class="form-control" id="deleteClientPlan" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Status</label>
          <div class="form-control" id="deleteClientStatus" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <form id="deleteClientForm" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmDeleteClient()">Delete Client</button>
      </div>
    </div>
  </div>
</div>
