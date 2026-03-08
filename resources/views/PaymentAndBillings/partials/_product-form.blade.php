<div class="card">
  <div class="card-body">

    <form action="{{ route('payments.store') }}" method="POST" id="prodPaymentForm">
      @csrf

      <!-- ═══ ROW 1: Product Search Bar ═══ -->
      <div class="pay-row-type mb-sm">
        <div class="prod-search-bar">
          <div class="prod-search-input-wrap">
            <input type="text" id="prodSearchItem" class="form-control" placeholder="Search products by name or number..." autocomplete="off">
            <i class="mdi mdi-magnify prod-search-icon"></i>
            <div id="prodSearchResults" class="autocomplete-results hidden"></div>
          </div>
          <button type="button" class="btn-add-item" id="prodAddItemBtn">
              + Add to Cart
          </button>
        </div>
      </div>

      <!-- ═══ ROW 2: Two-Column — Cart + Customer/Payment ═══ -->
      <div class="pay-row-main">

        <!-- LEFT: Cart Items Table -->
        <div class="pay-col-member">
          <label class="form-label">Cart Items</label>
          <div class="product-cart-table">
            <table class="table" id="prodItemsTable" style="margin-bottom: 0;">
              <thead>
                <tr>
                  <th>Item</th>
                  <th style="width: 70px;">Qty</th>
                  <th style="width: 100px;">Price</th>
                  <th style="width: 100px;">Subtotal</th>
                  <th style="width: 50px;"></th>
                </tr>
              </thead>
              <tbody id="prodItemsTableBody">
                <tr><td colspan="5" class="text-center text-muted" style="padding: 2rem; color: #666;">No items added</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- RIGHT: Customer + Payment Info -->
        <div class="pay-col-plans">
          <label class="form-label">Customer & Payment</label>
          <div class="member-card">
            <div class="member-card-body compact">
              <div class="member-form-row">
                <div class="member-form-col">
                  <label class="form-label form-label-sm">Customer Name</label>
                  <div class="pos-relative">
                    <input type="text" class="form-control" id="prodCustomerName" name="customer_name" placeholder="Search or enter name..." autocomplete="off">
                    <input type="hidden" id="prodCustomerId" name="customer_id">
                    <div id="prodCustomerResults" class="autocomplete-results hidden"></div>
                  </div>
                </div>
              </div>
              <div class="member-form-row">
                <div class="member-form-col">
                  <label class="form-label form-label-sm">Payment Method</label>
                  <select class="form-select" id="prodPaymentMethod" name="payment_method">
                    <option>Cash</option>
                    <option>Credit Card</option>
                    <option>Debit Card</option>
                    <option>GCash</option>
                    <option>Online Payment</option>
                  </select>
                </div>
              </div>
              <div class="member-form-row mb-0">
                <div class="member-form-col member-form-col-2">
                  <label class="form-label form-label-sm">Paid Amount</label>
                  <input type="number" class="form-control" id="prodPaidAmount" name="paid_amount" placeholder="₱0.00" step="0.01" min="0">
                </div>
                <div class="member-form-col member-form-col-2">
                  <label class="form-label form-label-sm">Change</label>
                  <input type="number" class="form-control change-amount" id="prodReturnAmount" placeholder="₱0.00" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /pay-row-main -->

      <input type="hidden" id="prodItemsData" name="items_data">
      <input type="hidden" id="prodTotalAmount" name="total_amount">

      <!-- ═══ ROW 3: Checkout Bar ═══ -->
      <div class="pay-checkout-bar">
        <div class="checkout-info">
          <div class="checkout-dates">
            <div class="checkout-date-item">
              <span class="checkout-date-label">Items</span>
              <span class="checkout-date-value checkout-summary-value" id="prodItemCount">0</span>
            </div>
          </div>
        </div>
        <div class="checkout-total">
          <span class="checkout-total-label">Total</span>
          <div class="checkout-total-amount">
            <span class="checkout-currency">₱</span>
            <input type="number" id="prodTotalDisplay" step="0.01" value="0.00" readonly>
          </div>
        </div>
        <div class="checkout-actions">
          <button type="button" class="btn-checkout-clear" id="prodClearBtn" title="Clear Cart">
            <i class="mdi mdi-eraser"></i> Clear
          </button>
          <button type="button" class="btn-checkout-pay" id="prodProcessPaymentBtn">
            <i class="mdi mdi-check-circle"></i> Process Payment
          </button>
        </div>
      </div><!-- /pay-checkout-bar -->

    </form>
  </div>
</div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulkDeleteForm" action="{{ route('payments.bulkDelete') }}" method="POST" class="hidden">
  @csrf
  @method('DELETE')
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Refund Form (Hidden) -->
<form id="productRefundForm" action="" method="POST" class="hidden">
  @csrf
  <input type="hidden" name="reason" id="productRefundReasonInput">
</form>
