<!-- Bulk Delete Form -->
<form id="bulkDeleteInventoryForm" action="{{ route('inventory.bulk-delete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> This action cannot be undone. You are about to delete <strong id="deleteItemCount">0</strong>.
        </div>
        <div class="selected-products-list" id="selectedProductsList">
          <!-- Selected products will be listed here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="executeDelete()">Delete</button>
      </div>
    </div>
  </div>
</div>
