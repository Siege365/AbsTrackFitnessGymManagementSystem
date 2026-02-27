<!-- Stock In Modal -->
<div class="modal fade" id="stockInModal" tabindex="-1" role="dialog" aria-labelledby="stockInModalLabel">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content stock-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="stockInModalLabel">Stock In</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="stockInForm" method="POST" novalidate>
        @csrf
        <input type="hidden" name="transaction_type" value="stock_in">
        <div class="modal-body">
          <!-- Product Information -->
          <div class="info-card">
            <div class="info-row">
              <span class="info-label">Product Number:</span>
              <span class="info-value" id="stockInProductNumber"></span>
            </div>
            <div class="info-row">
              <span class="info-label">Product Name:</span>
              <span class="info-value" id="stockInProductName"></span>
            </div>
            <div class="info-row">
              <span class="info-label">Category:</span>
              <span class="info-value" id="stockInCategory"></span>
            </div>
            <div class="info-row">
              <span class="info-label">Current Stock:</span>
              <span class="info-value"><span class="badge badge-info" id="stockInCurrentStock"></span></span>
            </div>
            <div class="info-row">
              <span class="info-label">Status:</span>
              <span class="info-value"><span class="badge" id="stockInStatus"></span></span>
            </div>
          </div>

          <!-- Quantity Input -->
          <div class="form-group">
            <label>Quantity to Add <span class="text-danger">*</span></label>
            <input type="number" 
                  class="form-control quantity-input" 
                  name="quantity" 
                  id="stockInQuantity"
                  placeholder="0" 
                  required>
          </div>

          <!-- Notes -->
          <div class="form-group">
            <label>Notes (Optional)</label>
            <textarea class="form-control" 
                      name="notes" 
                      rows="2" 
                      placeholder="e.g., Supplier name, purchase order #"></textarea>
          </div>

          <!-- Preview -->
          <div class="preview-box" id="stockInPreview" style="display: none;">
            <div class="preview-row">
              <span class="preview-label">Current Stock</span>
              <span class="preview-value" id="previewCurrentIn">0</span>
            </div>
            <div class="preview-row">
              <span class="preview-label">Adding</span>
              <span class="preview-value text-success" id="previewAddQuantity">+0</span>
            </div>
            <div class="preview-row">
              <span class="preview-label">New Stock</span>
              <span class="preview-value text-success" id="previewNewIn">0</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-update">Confirm Stock In</button>
        </div>
      </form>
    </div>
  </div>
</div>
