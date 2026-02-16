@extends('layouts.admin')

@section('title', 'Payments & Billing -> Product Payment')

@push('styles')
<style>
  /* CONSISTENT COLOR SCHEME MATCHING MEMBERSHIP PAYMENT */
  .table-responsive::-webkit-scrollbar {
    height: 8px;
  }
  
  .table-responsive::-webkit-scrollbar-track {
    background: #191C24;
  }
  
  .table-responsive::-webkit-scrollbar-thumb {
    background-color: #555;
    border-radius: 4px;
  }

  .pagination .page-item.active .page-link {
    background-color: #ffffff;
    border-color: #ffffff;
    color: #000000;
  }
  
  .pagination .page-link {
    color: #ffffff;
    background-color: #282A36;
    border-color: #555;
    padding: 8px 12px;
    margin: 0 2px;
    border-radius: 4px;
  }
  
  .pagination .page-link:hover {
    background-color: #ffffff;
    border-color: #000000;
    color: #000000;
  }

  .pagination .page-link:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
  }

  .pagination .page-item.disabled .page-link {
    background-color: #1a1d24;
    border-color: #333;
    color: #666;
  }

  .pagination-info {
    color: #999;
    font-size: 14px;
  }

  .form-control[readonly] {
    background-color: #282A36 !important;
    color: #495057 !important;
  }

  .table thead th,
  .table tbody td {
    color: #ffffff !important;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }

  /* Stats Cards - Enhanced */
  .stat-change {
    font-size: 0.875rem;
    margin-top: 0.5rem;
    font-weight: 600;
  }

  .stat-change.positive {
    color: #28a745;
  }

  .stat-change.negative {
    color: #dc3545;
  }

  /* Card Styles - Enhanced */
  .card {
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  }

  .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 1.5rem;
  }

  /* Form Styles - Enhanced */
  .form-group {
    margin-bottom: 1.5rem;
  }

  .form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #999;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .form-control, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    background: #191C24;
    border: 1px solid #555;
    border-radius: 6px;
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    background: #282A36;
  }

  .form-control::placeholder {
    color: #666;
  }

  /* Search Results - Enhanced */
  .search-results {
    position: absolute;
    background: #282A36;
    border: 1px solid #555;
    max-height: 250px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    border-radius: 6px;
    margin-top: 2px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .search-result-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #555;
    color: #ffffff;
    transition: all 0.2s ease;
  }

  .search-result-item:hover {
    background-color: #191C24;
    padding-left: 20px;
  }

  .search-result-item:last-child {
    border-bottom: none;
  }

  /* Buttons - Enhanced */
  .btn {
    padding: 0.875rem 2rem;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
  }

  .btn-primary {
    background: #0d6efd;
    color: white;
}

  .btn-primary:hover {
    background: #138496;
  }

  .btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
  }

  .btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
  }

  .btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #000;
  }

  .btn-warning:hover {
    background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
  }

  .btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  .btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
  }

  .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
  }

  .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  /* Table Styles - Enhanced */
  .table-responsive {
    overflow-x: auto;
    min-height: 300px;
    border-radius: 8px;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
  }

  .table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: #ffffff !important;
    border-bottom: 2px solid #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .table tbody tr {
    transition: all 0.3s ease;
    height: 53px;
  }

  .table tbody tr:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: scale(1.01);
  }

  .table td {
    padding: 1rem;
    border-bottom: 1px solid #555;
    color: #ffffff !important;
  }

  /* Modal Overlay - Base */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
    z-index: 9999;
    animation: fadeIn 0.3s ease;
    align-items: center;
    justify-content: center;
  }

  .modal-overlay.show {
    display: flex;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  /* Modal Content */
  .modal-content {
    background: #ffffff;
    border-radius: 8px;
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    animation: modalSlideIn 0.3s ease;
  }

  .modal-content.small {
    max-width: 500px;
  }

  @keyframes modalSlideIn {
    from {
      transform: scale(0.9) translateY(-50px);
      opacity: 0;
    }
    to {
      transform: scale(1) translateY(0);
      opacity: 1;
    }
  }

  .modal-header {
    padding: 2rem;
    background: #191C24;
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
  }

  .modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-close:hover {
    color: #dc3545;
    transform: rotate(90deg);
  }

  .modal-body {
    padding: 2rem;
  }

  .modal-footer {
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
  }

  /* Confirmation Modal Specific Styles */
  .confirmation-icon {
    font-size: 4rem;
    text-align: center;
    margin-bottom: 1rem;
  }

  .confirmation-icon.warning {
    color: #ffc107;
  }

  .confirmation-message {
    text-align: center;
    color: #333;
    font-size: 1.125rem;
    margin-bottom: 1.5rem;
  }

  .confirmation-details {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
  }

  .confirmation-detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
  }

  .confirmation-detail-row:last-child {
    border-bottom: none;
  }

  .confirmation-detail-label {
    font-weight: 600;
    color: #666;
  }

  .confirmation-detail-value {
    color: #333;
    font-weight: 700;
  }

  /* Receipt Modal - Enhanced */
  .receipt-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.9);
    backdrop-filter: blur(10px);
    animation: fadeIn 0.3s;
    align-items: center;
    justify-content: center;
  }

  .receipt-modal.show {
    display: flex;
  }

  .receipt-modal-content {
    background-color: #ffffff;
    padding: 0;
    max-width: 800px;
    width: 90%;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.8);
    animation: receiptSlideIn 0.5s ease;
    max-height: 90vh;
    overflow-y: auto;
  }

  @keyframes receiptSlideIn {
    from {
      transform: scale(0.8) translateY(-100px);
      opacity: 0;
    }
    to {
      transform: scale(1) translateY(0);
      opacity: 1;
    }
  }

  .receipt-modal-header {
    padding: 24px 30px;
    background: linear-gradient(135deg, #191C24 0%, #282A36 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .receipt-modal-header h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .receipt-modal-close {
    color: white;
    font-size: 36px;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border-radius: 50%;
  }

  .receipt-modal-close:hover {
    color: #dc3545;
    background: rgba(220, 53, 69, 0.2);
    transform: rotate(90deg) scale(1.1);
  }

  .receipt-modal-body {
    padding: 40px;
  }

  .receipt-container {
    background: white;
    color: #333;
  }

  .receipt-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #191C24;
  }

  .receipt-header h2 {
    color: #191C24;
    margin: 0 0 10px 0;
    font-size: 32px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
  }

  .receipt-header p {
    color: #666;
    margin: 5px 0;
    font-size: 14px;
  }

  .receipt-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 30px;
  }

  .receipt-info-item {
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #191C24;
  }

  .receipt-info-item strong {
    display: block;
    color: #666;
    font-size: 11px;
    text-transform: uppercase;
    margin-bottom: 6px;
    letter-spacing: 1px;
    font-weight: 700;
  }

  .receipt-info-item span {
    display: block;
    color: #191C24;
    font-size: 16px;
    font-weight: 700;
  }

  .receipt-table {
    width: 100%;
    margin-bottom: 30px;
    border-collapse: collapse;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .receipt-table th {
    background: #191C24;
    color: white;
    padding: 14px;
    text-align: left;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 1px;
  }

  .receipt-table td {
    padding: 14px;
    border-bottom: 1px solid #ddd;
    color: #333;
    font-size: 14px;
  }

  .receipt-table tr:last-child td {
    border-bottom: 2px solid #191C24;
  }

  .receipt-table tbody tr:hover {
    background-color: #f8f9fa;
  }

  .receipt-total {
    text-align: right;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 3px solid #191C24;
  }

  .receipt-total-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 12px;
    color: #333;
    font-size: 16px;
  }

  .receipt-total-row strong {
    width: 180px;
    text-align: right;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .receipt-total-row span {
    width: 180px;
    text-align: right;
    font-weight: 700;
  }

  .receipt-total-row.grand-total {
    font-size: 24px;
    color: #191C24;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 3px double #191C24;
  }

  .receipt-footer {
    text-align: center;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 2px dashed #999;
    color: #666;
  }

  .receipt-footer p {
    font-size: 18px;
    font-weight: 600;
    margin: 10px 0;
  }

  .receipt-modal-footer {
    padding: 24px 30px;
    background-color: #f8f9fa;
    border-radius: 0 0 12px 12px;
    text-align: center;
    display: flex;
    justify-content: center;
    gap: 12px;
  }

  .receipt-modal-footer button {
    margin: 0;
  }

  /* Loading Spinner */
  .loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .pagination-container {
      flex-direction: column;
      gap: 1rem;
    }

    .receipt-info {
      grid-template-columns: 1fr;
    }

    .modal-content.small {
      width: 95%;
    }

    .receipt-modal-content {
      width: 95%;
    }
  }

  /* Print Styles */
  @media print {
    @page {
      margin: 0.5in;
    }

    body * {
      visibility: hidden;
    }

    .receipt-modal-content,
    .receipt-modal-content * {
      visibility: visible;
    }

    .receipt-modal-content {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      background: white;
      color: black;
      margin: 0;
      box-shadow: none;
    }

    .receipt-modal-header,
    .receipt-modal-footer {
      display: none !important;
    }

    .receipt-modal-body {
      padding: 20px;
      max-height: none;
    }

    .receipt-table th {
      background: #333 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .receipt-info-item {
      background: #f5f5f5 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
  }

  /* Enhanced Stats Card */
  .card-body {
    padding: 1.5rem;
  }

  .card-body h2 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
  }

  .card-body .text-muted {
    color: #999 !important;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .modal-overlay.show, .receipt-modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 1rem;
    box-sizing: border-box;
  }

  .modal-overlay .modal-content,
  .modal-content,
  .receipt-modal-content,
  .receipt-container {
    margin: 0 auto !important;
    position: relative !important;
    max-height: 90vh;
    overflow: auto;
  }

  .modal-content.small {
    margin: auto !important;
  }

  @media (max-height: 600px) {
    .modal-overlay.show, .receipt-modal.show { align-items: flex-start !important; padding-top: 2rem; }
  }
</style>

@endpush

@section('content')
<div class="container-fluid">
  <!-- Stats Grid -->
  <div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($totalRevenueMonth ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Total Revenue This Month</p>
            </div>
            <div class="stat-change positive">
              <i class="mdi mdi-arrow-up"></i> +3.5%
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($retailSalesRevenue ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Retail Sales Revenue</p>
            </div>
            <div class="stat-change positive">
              <i class="mdi mdi-arrow-up"></i> +11%
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($dailyIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Daily Income</p>
            </div>
            <div class="stat-change negative">
              <i class="mdi mdi-arrow-down"></i> -2.4%
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-0">₱{{ number_format($weeklyIncome ?? 0, 2) }}</h2>
              <p class="text-muted mb-0">Weekly Income</p>
            </div>
            <div class="stat-change positive">
              <i class="mdi mdi-arrow-up"></i> +3.5%
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment Form Card -->
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="card-title mb-0">Payment Details Form</h4>
        <div class="d-flex" style="position: relative; width: 420px;">
          <input type="text" id="searchItem" class="form-control form-control-sm mr-2" placeholder="Search items...">
          <button type="button" class="btn btn-sm btn-primary mr-2" id="addItemBtn">
            Add Item
          </button>
          <button type="button" class="btn btn-sm btn-warning" id="searchClearBtn">
            Clear
          </button>
          <div id="searchResults" class="search-results" style="display: none;"></div>
        </div>
      </div>

      <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
        @csrf

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="customerName" class="form-label">Customer Name</label>
              <div style="position: relative;">
                <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Name" autocomplete="off" required>
                <input type="hidden" id="customerId" name="customer_id">
                <div id="customerResults" class="search-results" style="display:none;"></div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="totalAmount" class="form-label">Total Amount</label>
              <input type="number" class="form-control" id="totalAmount" name="total_amount" placeholder="₱0.00" readonly>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="transactionType" class="form-label">Transaction Type</label>
              <select class="form-control" id="transactionType" name="transaction_type">
                <option>Mixed</option>
                <option>Cash</option>
                <option>Credit Card</option>
                <option>Online Payment</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="paidAmount" class="form-label">Paid Amount</label>
              <input type="number" class="form-control" id="paidAmount" name="paid_amount" placeholder="₱0.00" step="0.01">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="paymentMethod" class="form-label">Payment Method</label>
              <select class="form-control" id="paymentMethod" name="payment_method">
                <option>Cash</option>
                <option>Credit Card</option>
                <option>Debit Card</option>
                <option>GCash</option>
                <option>Online Payment</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="returnAmount" class="form-label">Return Amount</label>
              <input type="number" class="form-control" id="returnAmount" placeholder="₱0.00" readonly>
            </div>
          </div>
        </div>
        
        <div class="row mt-3">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Items</h5>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered" id="itemsTable">
                <thead>
                  <tr>
                    <th style="min-width: 200px;">Item</th>
                    <th style="min-width: 80px;">Qty</th>
                    <th style="min-width: 120px;">Unit Price (₱)</th>
                    <th style="min-width: 120px;">Subtotal (₱)</th>
                    <th style="min-width: 100px;">Actions</th>
                  </tr>
                </thead>
                <tbody id="itemsTableBody">
                  <tr><td colspan="5" class="text-center text-muted">No items added</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <input type="hidden" id="itemsData" name="items_data">
        
        <div class="row mt-3">
          <div class="col-12">
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
              <button type="button" class="btn btn-secondary" id="clearBtn">
                <i class="mdi mdi-close"></i> Clear
              </button>
              <button type="button" class="btn btn-primary" id="processPaymentBtn">
                <i class="mdi mdi-check"></i> Process Payment
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulkDeleteForm" action="{{ route('payments.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Refund Form (Hidden) -->
<form id="productRefundForm" action="" method="POST" style="display: none;">
  @csrf
  <input type="hidden" name="reason" id="productRefundReasonInput">
</form>

<!-- Payment Confirmation Modal -->
<div id="paymentConfirmationModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Confirm Payment</h3>
      <button class="modal-close" onclick="closePaymentConfirmation()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="confirmation-icon warning">
        <i class="mdi mdi-alert-circle-outline"></i>
      </div>
      <p class="confirmation-message">Please review the payment details before proceeding.</p>
      <div class="confirmation-details" id="paymentConfirmationDetails"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closePaymentConfirmation()">
        <i class="mdi mdi-close"></i> Cancel
      </button>
      <button type="button" class="btn btn-primary" id="confirmProductPaymentBtn">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="receipt-modal">
  <div class="receipt-modal-content">
    <div class="receipt-modal-header">
      <h3>Receipt</h3>
      <button class="receipt-modal-close" onclick="closeReceiptModal()">&times;</button>
    </div>
    <div class="receipt-modal-body" id="receiptModalBody">
      <div class="text-center" style="padding: 40px; color: #666;">
        <i class="mdi mdi-loading mdi-spin" style="font-size: 48px;"></i>
        <p>Loading receipt...</p>
      </div>
    </div>
    <div class="receipt-modal-footer">
      <button type="button" class="btn btn-primary" onclick="printReceipt()">
        <i class="mdi mdi-printer"></i> Print Receipt
      </button>
      <button type="button" class="btn btn-secondary" onclick="closeReceiptModal()">
        Close
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  let cartItems = [];
  let inventoryItems = @json($inventoryItems ?? []);
  let selectedSearchItem = null;
  let isSubmitting = false;
  const STORAGE_KEY = 'paymentFormState_v1';

  // Save state
  function saveState() {
    try {
      const state = {
        cartItems: cartItems,
        customer_name: document.getElementById('customerName')?.value || '',
        customer_id: document.getElementById('customerId')?.value || '',
        transaction_type: document.getElementById('transactionType')?.value || '',
        payment_method: document.getElementById('paymentMethod')?.value || '',
        paid_amount: document.getElementById('paidAmount')?.value || '',
        total_amount: document.getElementById('totalAmount')?.value || ''
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
      console.warn('Failed to save payment form state', e);
    }
  }

  // Load state
  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const state = JSON.parse(raw);
      if (state) {
        cartItems = Array.isArray(state.cartItems) ? state.cartItems : [];
        if (document.getElementById('customerName')) document.getElementById('customerName').value = state.customer_name || '';
        if (document.getElementById('customerId')) document.getElementById('customerId').value = state.customer_id || '';

        if ((state.customer_id || state.customer_name) && window.fetch) {
          fetch(`{{ url('/members/search') }}?q=${encodeURIComponent(state.customer_name || '')}`)
            .then(r => r.json())
            .then(data => {
              const exists = Array.isArray(data) && data.some(m => String(m.id) === String(state.customer_id) || String(m.name).toLowerCase() === String((state.customer_name||'')).toLowerCase());
              if (!exists) {
                if (document.getElementById('customerName')) document.getElementById('customerName').value = '';
                if (document.getElementById('customerId')) document.getElementById('customerId').value = '';
                state.customer_name = '';
                state.customer_id = '';
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
              }
            })
            .catch(() => {});
        }
        if (document.getElementById('transactionType')) document.getElementById('transactionType').value = state.transaction_type || '';
        if (document.getElementById('paymentMethod')) document.getElementById('paymentMethod').value = state.payment_method || '';
        if (document.getElementById('paidAmount')) document.getElementById('paidAmount').value = state.paid_amount || '';
        if (document.getElementById('totalAmount')) document.getElementById('totalAmount').value = state.total_amount || '';
        renderCart();
        calculateTotals();
      }
    } catch (e) {
      console.warn('Failed to load payment form state', e);
    }
  }

  function clearState() {
    try { localStorage.removeItem(STORAGE_KEY); } catch (e) { }
  }

  document.addEventListener('DOMContentLoaded', function() {
    loadState();
    initializeTransactionControls();
  });

  window.addEventListener('beforeunload', function() {
    if (!isSubmitting) saveState();
  });
  
  console.log('Inventory Items Loaded:', inventoryItems.length);
  
  // Search functionality
  document.getElementById('searchItem').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const searchResults = document.getElementById('searchResults');
    
    if (searchTerm.length < 2) {
      searchResults.style.display = 'none';
      return;
    }
    
    const filtered = inventoryItems.filter(item => 
      item.product_name.toLowerCase().includes(searchTerm) ||
      item.product_number.toLowerCase().includes(searchTerm)
    );
    
    if (filtered.length > 0) {
      searchResults.innerHTML = filtered.map(item => `
        <div class="search-result-item" data-id="${item.id}" data-name="${item.product_name}" 
             data-price="${item.unit_price}" data-stock="${item.stock_qty}">
          <strong>${item.product_name}</strong> - ₱${parseFloat(item.unit_price).toFixed(2)} 
          <span class="text-muted">(Stock: ${item.stock_qty})</span>
        </div>
      `).join('');
      searchResults.style.display = 'block';
      
      document.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('click', function() {
          const stock = parseInt(this.dataset.stock);
          const name = this.dataset.name;
          const id = this.dataset.id;
          const price = parseFloat(this.dataset.price);

          if (isNaN(stock) || stock <= 0) {
            alert('This item is out of stock and cannot be selected.');
            return;
          }

          selectedSearchItem = { id: id, name: name, price: price, stock: stock };
          document.getElementById('searchItem').value = name;
          searchResults.style.display = 'none';
        });
      });
    } else {
      searchResults.innerHTML = '<div class="search-result-item">No items found</div>';
      searchResults.style.display = 'block';
    }
  });

  function addItemToCart(item) {
    if (!item || typeof item.stock === 'undefined' || item.stock <= 0) {
      alert('Cannot add item — Insufficient stock.');
      return;
    }

    const existingItem = cartItems.find(i => i.id == item.id);

    if (existingItem) {
      if (existingItem.qty < item.stock) {
        existingItem.qty++;
      } else {
        alert('Cannot add more. Insufficient stock!');
        return;
      }
    } else {
      cartItems.push({
        id: item.id,
        name: item.name,
        price: item.price,
        qty: 1,
        stock: item.stock
      });
    }
    
    renderCart();
    calculateTotals();
    saveState();
  }
  
  function renderCart() {
    const tbody = document.getElementById('itemsTableBody');
    
    if (cartItems.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>';
      return;
    }
    
    tbody.innerHTML = cartItems.map((item, index) => `
      <tr>
        <td>${item.name}</td>
        <td>
          <input type="number" class="form-control form-control-sm" value="${item.qty}" 
                 min="1" max="${item.stock}" onchange="updateQty(${index}, this.value)">
        </td>
        <td>₱${item.price.toFixed(2)}</td>
        <td>₱${(item.price * item.qty).toFixed(2)}</td>
        <td>
          <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
            <i class="mdi mdi-delete"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }
  
  function updateQty(index, newQty) {
    newQty = parseInt(newQty);
    if (newQty > 0 && newQty <= cartItems[index].stock) {
      cartItems[index].qty = newQty;
      renderCart();
      calculateTotals();
    } else {
      alert('Invalid quantity or insufficient stock!');
      renderCart();
    }
    saveState();
  }
  
  function removeItem(index) {
    cartItems.splice(index, 1);
    renderCart();
    calculateTotals();
    saveState();
  }
  
  function calculateTotals() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    document.getElementById('totalAmount').value = total.toFixed(2);
    
    const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
    const returnAmount = paidAmount - total;
    document.getElementById('returnAmount').value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
  }
  
  document.getElementById('paidAmount').addEventListener('input', calculateTotals);
  document.getElementById('paidAmount').addEventListener('input', saveState);
  
  // Clear form button
  document.getElementById('clearBtn').addEventListener('click', function() {
    cartItems = [];
    renderCart();
    document.getElementById('paymentForm').reset();
    document.getElementById('totalAmount').value = '';
    document.getElementById('returnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  });

  document.getElementById('addItemBtn').addEventListener('click', function() {
    if (!selectedSearchItem) {
      const name = document.getElementById('searchItem').value.trim();
      if (!name) {
        alert('Please select an item first from the search results.');
        return;
      }
      const found = inventoryItems.find(i => i.product_name.toLowerCase() === name.toLowerCase());
      if (!found) {
        alert('Selected item not found. Please choose from the search results.');
        return;
      }
      if (found.stock_qty <= 0) {
        alert('This item is out of stock and cannot be added.');
        return;
      }
      selectedSearchItem = { id: found.id, name: found.product_name, price: parseFloat(found.unit_price), stock: found.stock_qty };
    }

    addItemToCart(selectedSearchItem);
    const searchEl = document.getElementById('searchItem');
    if (searchEl) {
      searchEl.value = '';
      searchEl.focus();
    }
    const sr = document.getElementById('searchResults');
    if (sr) sr.style.display = 'none';
    selectedSearchItem = null;
  });

  document.getElementById('searchClearBtn').addEventListener('click', function() {
    document.getElementById('searchItem').value = '';
    document.getElementById('searchResults').style.display = 'none';
    selectedSearchItem = null;
  });
  
  // Process Payment Button Handler
  document.getElementById('processPaymentBtn').addEventListener('click', function(e) {
    e.preventDefault();

    if (cartItems.length === 0) {
      alert('Please add at least one item to the cart!');
      return;
    }

    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    if (paid < total) {
      alert('Payment incomplete: Paid amount must be equal to or greater than the total amount.');
      const paidEl = document.getElementById('paidAmount');
      if (paidEl) paidEl.focus();
      return;
    }

    // Prepare items data
    document.getElementById('itemsData').value = JSON.stringify(cartItems);

    // Build confirmation details
    const itemsHtml = cartItems.map(i => `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">${i.name} x ${i.qty}</span>
        <span class="confirmation-detail-value">₱${(i.price * i.qty).toFixed(2)}</span>
      </div>
    `).join('');

    const customerName = document.getElementById('customerName')?.value || 'Walk-in Customer';
    const paymentMethod = document.getElementById('paymentMethod')?.value || 'Cash';

    const details = `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Customer:</span>
        <span class="confirmation-detail-value">${customerName}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Type:</span>
        <span class="confirmation-detail-value">PRODUCT</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Items:</span>
        <span class="confirmation-detail-value">${cartItems.length} item(s)</span>
      </div>
      ${itemsHtml}
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Method:</span>
        <span class="confirmation-detail-value">${paymentMethod}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Total Amount:</span>
        <span class="confirmation-detail-value">₱${total.toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Paid Amount:</span>
        <span class="confirmation-detail-value">₱${paid.toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Change:</span>
        <span class="confirmation-detail-value">₱${(paid - total).toFixed(2)}</span>
      </div>
    `;

    document.getElementById('paymentConfirmationDetails').innerHTML = details;
    document.getElementById('paymentConfirmationModal').classList.add('show');
  });
  
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#searchItem') && !e.target.closest('#searchResults')) {
      document.getElementById('searchResults').style.display = 'none';
    }
    if (!e.target.closest('#customerName') && !e.target.closest('#customerResults')) {
      const cr = document.getElementById('customerResults');
      if (cr) cr.style.display = 'none';
    }
  });

  // Membership autocomplete
  (function() {
    const input = document.getElementById('customerName');
    const resultsEl = document.getElementById('customerResults');
    const customerIdEl = document.getElementById('customerId');
    let debounceTimer;

    if (!input) return;

    input.addEventListener('input', function(e) {
      const q = this.value.trim();
      customerIdEl.value = '';

      clearTimeout(debounceTimer);
      if (q.length < 1) {
        resultsEl.style.display = 'none';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`{{ url('/members/search') }}?q=${encodeURIComponent(q)}`)
          .then(r => r.json())
          .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
              resultsEl.innerHTML = '<div class="search-result-item">No members found</div>';
              resultsEl.style.display = 'block';
              return;
            }

            resultsEl.innerHTML = data.map(m => `
              <div class="search-result-item" data-id="${m.id}" data-name="${m.name}">
                <strong>${m.name}</strong> <span class="text-muted">${m.contact || ''}</span>
              </div>
            `).join('');
            resultsEl.style.display = 'block';

            resultsEl.querySelectorAll('.search-result-item').forEach(el => {
              el.addEventListener('click', function() {
                input.value = this.dataset.name;
                if (customerIdEl) customerIdEl.value = this.dataset.id;
                resultsEl.style.display = 'none';
              });
            });
          })
          .catch(err => {
            console.error('Member search error', err);
            resultsEl.style.display = 'none';
          });
      }, 250);
    });
  })();

  // Transaction History Controls
  function initializeTransactionControls() {
    const selectAllCheckbox = document.getElementById('selectAllTransactions');
    const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        transactionCheckboxes.forEach(checkbox => {
          checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
      });
    }

    transactionCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateBulkDeleteButton();
        
        const allChecked = Array.from(transactionCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(transactionCheckboxes).some(cb => cb.checked);
        
        if (selectAllCheckbox) {
          selectAllCheckbox.checked = allChecked;
          selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
      });
    });

    function updateBulkDeleteButton() {
      const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
      const selectedCount = checkedBoxes.length;
      const countSpan = document.getElementById('selectedCount');
      
      if (countSpan) {
        countSpan.textContent = selectedCount;
      }
      
      if (selectedCount > 0) {
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.classList.remove('disabled');
      } else {
        bulkDeleteBtn.disabled = true;
        bulkDeleteBtn.classList.add('disabled');
      }
    }

    if (bulkDeleteBtn) {
      bulkDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);
        
        if (ids.length === 0) {
          alert('Please select at least one transaction to delete.');
          return;
        }

        if (confirm(`Are you sure you want to delete ${ids.length} transaction(s)? This action cannot be undone.`)) {
          document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
          document.getElementById('bulkDeleteForm').submit();
        }
      });
    }

    document.querySelectorAll('.delete-form-payment').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
  }

  function closePaymentConfirmation() {
    const m = document.getElementById('paymentConfirmationModal');
    if (m) m.classList.remove('show');
  }

  function confirmProductPayment() {
    const btn = document.getElementById('confirmProductPaymentBtn');
    if (!btn) return;
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Processing...';

    const form = document.getElementById('paymentForm');
    if (!form) return;
    const fd = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Close confirmation modal
        closePaymentConfirmation();
        
        // Show receipt modal immediately
        setTimeout(() => {
          loadReceiptModal(data.payment.id);
        }, 300);
        
        // Clear form and state after showing receipt
        clearFormData();
        
      } else {
        alert(data.message || 'Failed to process payment');
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    })
    .catch(err => {
      console.error('Product payment error', err);
      alert('Failed to process payment. Please try again.');
      btn.disabled = false;
      btn.innerHTML = originalText;
    });
  }

  // Attach confirm handler to button
  document.getElementById('confirmProductPaymentBtn').addEventListener('click', confirmProductPayment);

  function clearFormData() {
    cartItems = [];
    renderCart();
    document.getElementById('paymentForm').reset();
    document.getElementById('totalAmount').value = '';
    document.getElementById('returnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  }

  // Receipt Modal Functions
  function loadReceiptModal(paymentId) {
    const modal = document.getElementById('receiptModal');
    const modalBody = document.getElementById('receiptModalBody');
    
    modal.classList.add('show');
    modalBody.innerHTML = `
      <div class="text-center" style="padding: 40px; color: #666;">
        <i class="mdi mdi-loading mdi-spin" style="font-size: 48px;"></i>
        <p>Loading receipt...</p>
      </div>
    `;
    
    fetch(`/payments/${paymentId}/receipt-data`)
      .then(response => response.json())
      .then(data => {
        modalBody.innerHTML = generateReceiptHTML(data);
      })
      .catch(error => {
        console.error('Error loading receipt:', error);
        modalBody.innerHTML = `
          <div class="text-center" style="padding: 40px; color: #dc3545;">
            <i class="mdi mdi-alert-circle" style="font-size: 48px;"></i>
            <p>Failed to load receipt. Please try again.</p>
          </div>
        `;
      });
  }

  function generateReceiptHTML(payment) {
    const itemsHTML = payment.items.map(item => `
      <tr>
        <td>${item.product_name}</td>
        <td style="text-align: center;">${item.quantity}</td>
        <td style="text-align: right;">₱${parseFloat(item.unit_price).toFixed(2)}</td>
        <td style="text-align: right;">₱${parseFloat(item.subtotal).toFixed(2)}</td>
      </tr>
    `).join('');

    return `
      <div class="receipt-container">
        <div class="receipt-header">
          <h2>RECEIPT</h2>
          <p>Abstrack Fitness Gym</p>
          <p>Toril, Davao Del Sur</p>
          <p>Phone: (123) 456-7890</p>
        </div>

        <div class="receipt-info">
          <div class="receipt-info-item">
            <strong>Receipt Number</strong>
            <span>#${payment.receipt_number}</span>
          </div>
          <div class="receipt-info-item">
            <strong>Date & Time</strong>
            <span>${payment.formatted_date}</span>
          </div>
          <div class="receipt-info-item">
            <strong>Customer Name</strong>
            <span>${payment.customer_name}</span>
          </div>
          <div class="receipt-info-item">
            <strong>Cashier</strong>
            <span>${payment.cashier_name}</span>
          </div>
          <div class="receipt-info-item">
            <strong>Payment Method</strong>
            <span>${payment.payment_method}</span>
          </div>
          <div class="receipt-info-item">
            <strong>Transaction Type</strong>
            <span>${payment.transaction_type}</span>
          </div>
        </div>

        <table class="receipt-table">
          <thead>
            <tr>
              <th>Item</th>
              <th style="text-align: center;">Quantity</th>
              <th style="text-align: right;">Unit Price</th>
              <th style="text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHTML}
          </tbody>
        </table>

        <div class="receipt-total">
          <div class="receipt-total-row">
            <strong>Subtotal:</strong>
            <span>₱${parseFloat(payment.total_amount).toFixed(2)}</span>
          </div>
          <div class="receipt-total-row grand-total">
            <strong>Total:</strong>
            <span>₱${parseFloat(payment.total_amount).toFixed(2)}</span>
          </div>
          <div class="receipt-total-row" style="margin-top: 20px;">
            <strong>Paid Amount:</strong>
            <span>₱${parseFloat(payment.paid_amount).toFixed(2)}</span>
          </div>
          <div class="receipt-total-row">
            <strong>Change:</strong>
            <span>₱${parseFloat(payment.return_amount).toFixed(2)}</span>
          </div>
        </div>

        <div class="receipt-footer">
          <p>Thank you for your purchase!</p>
          <p style="font-size: 14px; margin-top: 10px;">Please come again!</p>
        </div>
      </div>
    `;
  }

  function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    modal.classList.remove('show');
    
    // Reload page after closing receipt to refresh the transaction list
    setTimeout(() => {
      window.location.reload();
    }, 300);
  }

  function printReceipt() {
    window.print();
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const confirmModal = document.getElementById('paymentConfirmationModal');
    const receiptModal = document.getElementById('receiptModal');
    
    if (event.target === confirmModal) {
      closePaymentConfirmation();
    }
    if (event.target === receiptModal) {
      closeReceiptModal();
    }
  }

  // Product Refund Modal Functions
  let currentProductRefundId = null;
  function openProductRefundModal(id, receiptNumber, amount, customerName) {
    currentProductRefundId = id;
    // create modal if not exists
    if (!document.getElementById('productRefundModal')) {
      const html = `
      <div id="productRefundModal" class="modal-overlay">
        <div class="modal-content small">
          <div class="modal-header">
            <h3 class="modal-title">Process Refund</h3>
            <button class="modal-close" onclick="closeProductRefundModal()">&times;</button>
          </div>
          <div class="modal-body">
            <div class="refund-warning" style="background: rgba(220, 53, 69, 0.1); padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
              <i class="mdi mdi-alert" style="color: #dc3545;"></i>
              <strong>Warning:</strong> This will mark this transaction as refunded.
            </div>
            <div class="confirmation-details" id="productRefundDetails"></div>
            <div class="form-group">
              <label class="form-label">Refund Reason (Optional)</label>
              <textarea class="form-control" id="productRefundReason" rows="3" placeholder="Enter reason for refund..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProductRefundModal()">Cancel</button>
            <button type="button" class="btn btn-warning" onclick="confirmProductRefund()">Process Refund</button>
          </div>
        </div>
      </div>`;
      document.body.insertAdjacentHTML('beforeend', html);
    }

    document.getElementById('productRefundDetails').innerHTML = `
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Receipt:</span><span class="confirmation-detail-value">#${receiptNumber}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Customer:</span><span class="confirmation-detail-value">${customerName}</span></div>
      <div class="confirmation-detail-row"><span class="confirmation-detail-label">Refund Amount:</span><span class="confirmation-detail-value" style="color:#dc3545;">₱${parseFloat(amount).toFixed(2)}</span></div>`;

    document.getElementById('productRefundModal').classList.add('show');
  }

  function closeProductRefundModal() {
    const m = document.getElementById('productRefundModal');
    if (m) m.classList.remove('show');
  }

  function confirmProductRefund() {
    if (!currentProductRefundId) return;
    const reason = document.getElementById('productRefundReason').value || '';
    const url = `/payments/${currentProductRefundId}/refund`;
    const fd = new FormData();
    fd.append('reason', reason);

    fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          alert(data.message || 'Refund processed');
          closeProductRefundModal();
          setTimeout(() => window.location.reload(), 500);
        } else {
          alert(data.message || 'Failed to process refund');
        }
      })
      .catch(err => { console.error(err); alert('Failed to process refund'); });
  }
</script>
@endpush