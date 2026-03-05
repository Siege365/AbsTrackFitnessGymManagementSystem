<!-- ========================================== -->
<!-- EDIT CATEGORY MODAL                        -->
<!-- ========================================== -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative; max-width: 550px; margin: auto;">
      <div class="modal-header">
        <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editCategoryForm">
          <input type="hidden" id="editCategoryOriginalName" value="">
          <div class="form-group">
            <label>Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="editCategoryName" placeholder="Category name" required>
          </div>
          <div class="form-group">
            <label>Category Color</label>
            <div class="category-color-picker-grid">
              <button type="button" class="color-picker-swatch active" data-color="#FF6384" style="background:#FF6384;" title="Red"></button>
              <button type="button" class="color-picker-swatch" data-color="#36A2EB" style="background:#36A2EB;" title="Blue"></button>
              <button type="button" class="color-picker-swatch" data-color="#FFCE56" style="background:#FFCE56;" title="Yellow"></button>
              <button type="button" class="color-picker-swatch" data-color="#4BC0C0" style="background:#4BC0C0;" title="Teal"></button>
              <button type="button" class="color-picker-swatch" data-color="#9966FF" style="background:#9966FF;" title="Purple"></button>
              <button type="button" class="color-picker-swatch" data-color="#FF9F40" style="background:#FF9F40;" title="Orange"></button>
              <button type="button" class="color-picker-swatch" data-color="#4CAF50" style="background:#4CAF50;" title="Green"></button>
              <button type="button" class="color-picker-swatch" data-color="#E91E63" style="background:#E91E63;" title="Pink"></button>
              <button type="button" class="color-picker-swatch" data-color="#00BCD4" style="background:#00BCD4;" title="Cyan"></button>
              <button type="button" class="color-picker-swatch" data-color="#8BC34A" style="background:#8BC34A;" title="Light Green"></button>
              <button type="button" class="color-picker-swatch" data-color="#FF5722" style="background:#FF5722;" title="Deep Orange"></button>
              <button type="button" class="color-picker-swatch" data-color="#607D8B" style="background:#607D8B;" title="Blue Grey"></button>
            </div>
            <input type="hidden" id="editCategoryColor" value="#FF6384">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-update" onclick="confirmSaveCategory()">
          <i class="mdi mdi-content-save"></i>  Save Changes
        </button>
      </div>

      <!-- Confirm Overlay (inside modal-content) -->
      <div id="categoryConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Changes</h5>
            <button type="button" class="close" onclick="backToCategoryForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to update this category?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Original Name:</span>
                <span class="confirm-value" id="confirmCategoryOriginalText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">New Name:</span>
                <span class="confirm-value" id="confirmCategoryNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Color:</span>
                <span class="confirm-value" id="confirmCategoryColorText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToCategoryForm()">Cancel</button>
            <button type="button" class="btn btn-update" id="confirmSaveCategoryBtn" onclick="executeSaveCategory()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
