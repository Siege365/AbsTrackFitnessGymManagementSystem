{{-- Confirm Bulk Delete Modal --}}
<div class="modal-overlay" id="bulkDeleteModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title"><i class="mdi mdi-alert-circle-outline mr-2"></i>Delete Selected Logs</h5>
      <button type="button" class="modal-close" onclick="closeBulkDeleteModal()">&times;</button>
    </div>
    <div class="modal-body">
      <p style="color: #333; font-size: 1rem;">Are you sure you want to delete the selected <strong id="bulkDeleteCount">0</strong> activity log(s)?</p>
      <p style="color: #666; font-size: 0.9rem;">This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeBulkDeleteModal()">Cancel</button>
      <form action="{{ route('activity-logs.bulk-delete') }}" method="POST" id="bulkDeleteForm">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulkDeleteIds" value="">
        <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete mr-1"></i> Delete</button>
      </form>
    </div>
  </div>
</div>
