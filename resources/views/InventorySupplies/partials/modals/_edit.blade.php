<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg inventory-product-modal" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editProductForm" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <!-- Centered Product Avatar -->
          <div class="text-center mb-4">
            <div id="editProductAvatarPreview" class="avatar-preview-container avatar-preview-lg mx-auto">
              <i class="mdi mdi-package-variant"></i>
            </div>
            <small class="text-muted">
              <i class="mdi mdi-information-outline"></i>
              Upload product image or provide image URL (optional)
            </small>
          </div>

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
              <!-- Category Dropdown (default) -->
              <select class="form-control" name="category" id="editProductCategory" required>
                @if(isset($categories) && $categories->count() > 0)
                  @foreach($categories as $cat)
                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                  @endforeach
                @else
                  <option value="Supplement">Supplement</option>
                  <option value="Equipment">Equipment</option>
                  <option value="Apparel">Apparel</option>
                  <option value="Beverages">Beverages</option>
                  <option value="Snacks">Snacks</option>
                  <option value="Accessories">Accessories</option>
                @endif
              </select>
              <!-- New Category Input (hidden by default) -->
              <div id="editNewCategoryInputGroup" style="display: none;">
                <input type="text" 
                      class="form-control" 
                      id="editNewCategoryInput"
                      placeholder="Enter new category name"
                      maxlength="50"
                      oninput="checkCategorySimilarity(this.value, 'edit')">
                <!-- Similarity warning container -->
                <div id="editCategorySimilarityWarning" class="category-similarity-warning" style="display: none;"></div>
              </div>
              <!-- Checkbox below input field -->
              <div class="d-flex align-items-center mt-2">
                <input type="checkbox" id="editNewCategoryCheckbox" class="mr-2" onclick="toggleEditNewCategory(this)">
                <label for="editNewCategoryCheckbox" class="mb-0" style="font-size: 0.85rem; color: rgba(255,255,255,0.6); cursor: pointer;">New Category</label>
              </div>
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

          <!-- Row 3: Product Avatar Upload -->
          <div class="form-row">
            <div class="form-group col-12">
              <label>Product Image</label>
              <input type="file" name="avatar" id="editProductAvatar" class="form-control mb-2" accept="image/*" onchange="previewEditProductAvatar()">
              <input type="text" name="avatar_url" id="editProductAvatarUrl" class="form-control mb-2" placeholder="https://example.com/product-image.jpg" style="display: none;" oninput="previewEditProductAvatar()">
              <div class="btn-group btn-group-toggle btn-group-sm d-flex" data-toggle="buttons">
                <label class="btn btn-outline-secondary active flex-fill">
                  <input type="radio" name="editProductAvatarInputType" value="file" checked onclick="toggleEditProductAvatarInput('file')"> Upload File
                </label>
                <label class="btn btn-outline-secondary flex-fill">
                  <input type="radio" name="editProductAvatarInputType" value="url" onclick="toggleEditProductAvatarInput('url')"> Image URL
                </label>
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
