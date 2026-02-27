<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editProductForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <!-- Row 1: Product Number and Product Name -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Product Number</label>
              <input type="text" class="form-control" id="editProductNumber" readonly>
              <small class="text-muted">Product number cannot be changed</small>
            </div>
            <div class="form-group col-md-6">
              <label>Product Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="product_name" id="editProductName" required>
            </div>
          </div>

          <!-- Row 2: Category and Unit Price -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Category <span class="text-danger">*</span></label>
              <div class="d-flex align-items-center mb-2">
                <input type="checkbox" id="editNewCategoryCheckbox" class="mr-2" onclick="toggleEditNewCategory(this)">
                <label for="editNewCategoryCheckbox" class="mb-0" style="font-size: 0.85rem; color: rgba(255,255,255,0.6); cursor: pointer;">New Category</label>
              </div>
              <!-- Category Dropdown (default) -->
              <select class="form-control" name="category" id="editProductCategory" required>
                <option value="Supplement">Supplement</option>
                <option value="Equipment">Equipment</option>
                <option value="Apparel">Apparel</option>
                <option value="Beverages">Beverages</option>
                <option value="Snacks">Snacks</option>
                <option value="Accessories">Accessories</option>
                <option value="Food">Food</option>
                <option value="Drink">Drink</option>
              </select>
              <!-- New Category Input (hidden by default) -->
              <div id="editNewCategoryInputGroup" style="display: none;">
                <input type="text" 
                      class="form-control mb-2" 
                      id="editNewCategoryInput"
                      placeholder="Enter new category name"
                      maxlength="50">
                <div class="d-flex align-items-center gap-2">
                  <label class="mb-0 mr-2" style="font-size: 0.85rem; color: rgba(255,255,255,0.6); white-space: nowrap;">Color:</label>
                  <input type="color" 
                        id="editNewCategoryColor" 
                        class="category-color-picker"
                        value="#FFA726"
                        title="Category color">
                  <span id="editNewCategoryColorHex" style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-left: 0.5rem;">#FFA726</span>
                </div>
              </div>
              <input type="hidden" name="category_color" id="editCategoryColorHidden" value="">
            </div>
            <div class="form-group col-md-6">
              <label>Unit Price <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">₱</span>
                </div>
                <input type="number" step="0.01" min="0" class="form-control" name="unit_price" id="editProductPrice" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-update" id="editProductSubmitBtn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
