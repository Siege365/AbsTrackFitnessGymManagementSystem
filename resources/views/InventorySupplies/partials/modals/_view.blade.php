<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewProductModalLabel">Product Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="product-detail-card">
          <div class="product-detail-row">
            <span class="product-detail-label">Product Number</span>
            <span class="product-detail-value" id="viewProductNumber"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Product Name</span>
            <span class="product-detail-value" id="viewProductName"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Category</span>
            <span class="product-detail-value" id="viewProductCategory"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Unit Price</span>
            <span class="product-detail-value" id="viewProductPrice"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Stock Quantity</span>
            <span class="product-detail-value" id="viewProductStock"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Low Stock Threshold</span>
            <span class="product-detail-value" id="viewProductThreshold"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Status</span>
            <span class="product-detail-value" id="viewProductStatus"></span>
          </div>
          <div class="product-detail-row">
            <span class="product-detail-label">Last Restocked</span>
            <span class="product-detail-value" id="viewProductRestocked"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Close</button>
        <a href="#" id="viewProductHistoryLink" class="btn btn-update">
          <i class="mdi mdi-history"></i> Full Transaction History
        </a>
      </div>
    </div>
  </div>
</div>
