<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg inventory-product-modal" role="document">
    <div class="modal-content" style="position: relative;">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addProductForm" action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <!-- Centered Product Avatar -->
          <div class="text-center mb-4">
            <div id="newProductAvatarPreview" class="avatar-preview-container avatar-preview-lg mx-auto">
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
              <input type="text" 
                    class="form-control" 
                    name="product_number" 
                    id="autoProductNumber"
                    value="{{ old('product_number') }}" 
                    readonly>
              <small class="text-muted">Auto-generated product number</small>
            </div>
            <div class="form-group col-md-6">
              <label>Product Name <span class="text-danger">*</span></label>
              <input type="text" 
                    class="form-control @error('product_name') is-invalid @enderror" 
                    name="product_name" 
                    id="productNameInput"
                    placeholder="Enter product name" 
                    value="{{ old('product_name') }}" 
                    required
                    autofocus>
              @error('product_name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Row 2: Category and Unit Price -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Category <span class="text-danger">*</span></label>
              <!-- Category Dropdown (default) -->
              <select class="form-control @error('category') is-invalid @enderror" 
                      name="category" 
                      id="addCategorySelect"
                      required>
                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>
                <option value="Supplement" {{ old('category') == 'Supplement' ? 'selected' : '' }}>Supplement</option>
                <option value="Equipment" {{ old('category') == 'Equipment' ? 'selected' : '' }}>Equipment</option>
                <option value="Apparel" {{ old('category') == 'Apparel' ? 'selected' : '' }}>Apparel</option>
                <option value="Beverages" {{ old('category') == 'Beverages' ? 'selected' : '' }}>Beverages</option>
                <option value="Snacks" {{ old('category') == 'Snacks' ? 'selected' : '' }}>Snacks</option>
                <option value="Accessories" {{ old('category') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
              </select>
              <!-- New Category Input (hidden by default) -->
              <div id="newCategoryInputGroup" style="display: none;">
                <input type="text" 
                      class="form-control" 
                      id="newCategoryInput"
                      placeholder="Enter new category name"
                      maxlength="50">
              </div>
              <!-- Checkbox below input field -->
              <div class="d-flex align-items-center mt-2">
                <input type="checkbox" id="newCategoryCheckbox" class="mr-2" onclick="toggleNewCategory(this)">
                <label for="newCategoryCheckbox" class="mb-0" style="font-size: 0.85rem; color: rgba(255,255,255,0.6); cursor: pointer;">New Category</label>
              </div>
              @error('category')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group col-md-6">
              <label>Unit Price <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">₱</span>
                </div>
                <input type="number" 
                      step="0.01" 
                      min="0" 
                      class="form-control @error('unit_price') is-invalid @enderror" 
                      name="unit_price" 
                      placeholder="0.00"
                      value="{{ old('unit_price') }}" 
                      required>
              </div>
              @error('unit_price')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Row 3: Product Image Upload -->
          <div class="form-row">
            <div class="form-group col-12">
              <label>Product Image</label>
              <input type="file" name="avatar" id="newProductAvatar" class="form-control mb-2" accept="image/*" onchange="previewNewProductAvatar()">
              <input type="text" name="avatar_url" id="newProductAvatarUrl" class="form-control mb-2" placeholder="https://example.com/product-image.jpg" style="display: none;" oninput="previewNewProductAvatar()">
              <div class="btn-group btn-group-toggle btn-group-sm d-flex" data-toggle="buttons">
                <label class="btn btn-outline-secondary active flex-fill">
                  <input type="radio" name="productAvatarInputType" value="file" checked onclick="toggleProductAvatarInput('file')"> Upload File
                </label>
                <label class="btn btn-outline-secondary flex-fill">
                  <input type="radio" name="productAvatarInputType" value="url" onclick="toggleProductAvatarInput('url')"> Image URL
                </label>
              </div>
              @error('avatar')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Row 4: Initial Stock Quantity and Low Stock Threshold -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Initial Stock Quantity <span class="text-danger">*</span></label>
              <input type="number" 
                    min="0" 
                    class="form-control @error('stock_qty') is-invalid @enderror" 
                    name="stock_qty" 
                    placeholder="0" 
                    value="{{ old('stock_qty', 0) }}" 
                    required>
              @error('stock_qty')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group col-md-6">
              <label>Low Stock Threshold</label>
              <input type="number" 
                    class="form-control" 
                    value="10" 
                    readonly>
              <input type="hidden" name="low_stock_threshold" value="10">
              <small class="text-muted">Fixed at 10 units</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" id="addProductSubmitBtn" onclick="showAddProductConfirm()">Submit</button>
        </div>
      </form>

      <!-- Add Product Confirmation Overlay -->
      <div id="addProductConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Product</h5>
            <button type="button" class="close" onclick="backToAddProductForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to add this product?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Product Number:</span>
                <span class="confirm-value" id="confirmProductNumberText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Product Name:</span>
                <span class="confirm-value" id="confirmProductNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Category:</span>
                <span class="confirm-value" id="confirmCategoryText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Unit Price:</span>
                <span class="confirm-value" id="confirmPriceText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Initial Stock:</span>
                <span class="confirm-value" id="confirmStockText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToAddProductForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitAddProductForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
