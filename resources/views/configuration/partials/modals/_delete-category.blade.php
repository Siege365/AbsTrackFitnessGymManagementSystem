<!-- ========================================== -->
<!-- DELETE CATEGORY CONFIRMATION MODAL         -->
<!-- ========================================== -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteCategoryModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="max-width: 550px; margin: auto;">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle mr-1"></i> Are you sure you want to delete this category? This action cannot be undone.
        </div>

        <div class="delete-details">
          <div class="form-group">
            <label>Category Name</label>
            <div class="form-control" id="deleteCategoryName"></div>
          </div>
          <div class="form-group">
            <label>Products Affected</label>
            <div class="form-control" id="deleteCategoryCount"></div>
          </div>
        </div>

        <div id="categoryDeleteWarning" class="category-delete-warning" style="display: none;">
          <i class="mdi mdi-alert-outline"></i>
          <span id="categoryDeleteWarningText"></span>
        </div>

        <div id="categoryReassignGroup" class="form-group mt-3" style="display: none;">
          <label>Reassign products to:</label>
          <select class="form-control" id="categoryReassignTo">
            <option value="">— Leave uncategorized —</option>
            @if(isset($categories))
              @foreach($categories as $cat)
                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
              @endforeach
            @endif
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="executeDeleteCategory()">
          <i class="mdi mdi-delete"></i> Delete Category
        </button>
      </div>
    </div>
  </div>
</div>
