@extends('layouts.admin')

@section('title', 'Refunds Management')

@push('styles')
<style>
  /* CONSISTENT COLOR SCHEME */
  .table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  
  .table-responsive::-webkit-scrollbar-track {
    background: #191C24;
    border-radius: 4px;
  }
  
  .table-responsive::-webkit-scrollbar-thumb {
    background: #555;
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
    margin-top: 1rem;
    font-size: 0.875rem;
    color: #999;
  }

  .form-control[readonly] {
    background-color: #282A36 !important;
    color: #495057 !important;
  }

  .table thead th,
  .table tbody td {
    padding: 0.75rem;
  }

  .table-hover tbody tr:hover {
    background-color: #f5f5f5;
  }

  /* Stats Cards - Membership Payment Style */
  .stat-card {
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    background: linear-gradient(135deg, #1a1d27 0%, #282A36 100%);
    border-left: 4px solid #17a2b8;
  }

  .stat-card h4 {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: #999;
    font-weight: 500;
  }

  .stat-card .amount {
    font-size: 1.75rem;
    font-weight: bold;
    color: #ffffff;
  }

  /* Card Styles */
  .card {
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    background: #191C24;
    margin-bottom: 2rem;
  }

  .card-title {
    color: #ffffff;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }

  /* Form Styles */
  .form-group {
    margin-bottom: 1rem;
  }

  .form-label {
    font-weight: 600;
    color: #999;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
  }

  .form-control, .form-select {
    border: 1px solid #555;
    border-radius: 4px;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    background: #282A36;
    color: #ffffff;
  }

  .form-control:focus, .form-select:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    outline: none;
    background: #282A36;
  }

  .form-control::placeholder {
    color: #666;
  }

  /* Autocomplete Results */
  .autocomplete-results {
    position: absolute;
    background: #282A36;
    border: 1px solid #555;
    border-radius: 4px;
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  }

  .autocomplete-results.show {
    display: block;
  }

  .autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #3a3d45;
    font-size: 0.875rem;
    color: #ffffff;
  }

  .autocomplete-item:hover {
    background-color: #3a3d45;
  }

  .autocomplete-item:last-child {
    border-bottom: none;
  }

  /* Buttons */
  .btn {
    border: none;
    border-radius: 4px;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  }

  .btn-primary {
    background-color: #17a2b8;
    color: white;
  }

  .btn-primary:hover {
    background-color: #138496;
  }

  .btn-secondary {
    background-color: #6c757d;
    color: white;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
  }

  .btn-danger {
    background-color: #dc3545;
    color: white;
  }

  .btn-danger:hover {
    background-color: #c82333;
  }

  .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }

  /* Filter */
  .filter-dropdown {
    position: relative;
  }

  .filter-btn {
    background-color: #282A36;
    border: 1px solid #555;
    padding: 0.625rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #ffffff;
  }

  .filter-btn:hover {
    background-color: #3a3d45;
  }

  .filter-menu {
    position: absolute;
    background: #282A36;
    border: 1px solid #555;
    border-radius: 4px;
    min-width: 200px;
    top: 100%;
    left: 0;
    display: none;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  }

  .filter-menu.show {
    display: block;
  }

  .filter-menu-item {
    display: block;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #ffffff;
    cursor: pointer;
    font-size: 0.875rem;
    border-bottom: 1px solid #3a3d45;
    transition: background-color 0.2s;
  }

  .filter-menu-item:hover {
    background-color: #3a3d45;
  }

  .filter-menu-item.active {
    background-color: #17a2b8;
    color: #ffffff;
    font-weight: 500;
  }

  .filter-menu-divider {
    height: 1px;
    background-color: #555;
    margin: 0.5rem 0;
  }

  /* Table Styles */
  .table-responsive {
    border-radius: 4px;
    overflow: hidden;
  }

  .table {
    margin-bottom: 0;
  }

  .table th {
    background-color: #282A36;
    border-bottom: 2px solid #555;
    font-weight: 600;
    color: #ffffff;
    font-size: 0.875rem;
  }

  .table tbody tr {
    border-bottom: 1px solid #333;
  }

  .table tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }

  .table td {
    vertical-align: middle;
    font-size: 0.875rem;
    color: #ffffff;
  }

  /* Badge Styles */
  .badge {
    padding: 0.4rem 0.8rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
  }

  .badge-success {
    background-color: #d4edda;
    color: #155724;
  }

  .badge-warning {
    background-color: #fff3cd;
    color: #856404;
  }

  .badge-danger {
    background-color: #f8d7da;
    color: #721c24;
  }

  .badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
  }

  /* Action Dropdown */
  .action-dropdown {
    position: relative;
  }

  .action-btn {
    background-color: #282A36;
    border: 1px solid #555;
    padding: 0.4rem 0.6rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #ffffff;
  }

  .action-btn:hover {
    background-color: #3a3d45;
  }

  .dropdown-menu {
    position: absolute;
    background: #282A36;
    border: 1px solid #555;
    border-radius: 4px;
    min-width: 150px;
    right: 0;
    top: 100%;
    display: none;
    z-index: 1100;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    margin-top: 0.25rem;
  }

  .dropdown-menu.show {
    display: block;
  }

  .dropdown-item {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    text-align: left;
    text-decoration: none;
    color: #ffffff;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    border-bottom: 1px solid #3a3d45;
    transition: background-color 0.2s;
  }

  .dropdown-item:hover {
    background-color: #3a3d45;
  }

  .dropdown-item i {
    margin-right: 0.5rem;
    width: 16px;
  }

  .dropdown-item.danger {
    color: #dc3545;
  }

  .dropdown-item.danger:hover {
    background-color: rgba(220, 53, 69, 0.2);
  }

  /* Pagination */
  .pagination-container {
    margin-top: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .pagination {
    display: flex;
    gap: 0.25rem;
    margin: 0;
  }

  .page-item {
    margin: 0;
  }

  /* Checkbox Styles */
  .custom-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #17a2b8;
  }

  /* Modal Styles */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
  }

  .modal-overlay.show {
    display: flex;
  }

  .modal-content {
    background: #191C24;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #555;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-close:hover {
    color: #ffffff;
  }

  .modal-body {
    padding: 1.5rem;
  }

  .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #555;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
  }

  /* Notification */
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    padding: 1rem 1.5rem;
    background: #282A36;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: none;
    z-index: 3000;
    animation: slideIn 0.3s ease-out;
    color: #ffffff;
  }

  .notification.show {
    display: block;
  }

  .notification.success {
    border-left: 4px solid #28a745;
  }

  .notification.error {
    border-left: 4px solid #dc3545;
  }

  .notification.info {
    border-left: 4px solid #17a2b8;
  }

  @keyframes slideIn {
    from {
      transform: translateX(500px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  /* Search Input Area */
  .search-area {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }

  .search-area input {
    flex: 1;
    min-width: 250px;
  }

  .relative {
    position: relative;
  }

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  /* Refund Form */
  .refund-form-section {
    background-color: #191C24;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
  }
</style>
@endpush

@section('content')
<div class="container-fluid">
  <!-- Header -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1 style="margin: 0; color: #ffffff; font-size: 2rem; font-weight: 600;">Refunds Management</h1>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <h4>Pending Refunds</h4>
      <div class="amount">{{ $pendingRefunds }}</div>
    </div>
    <div class="stat-card">
      <h4>Total Refunded (This Month)</h4>
      <div class="amount">₱{{ number_format($totalRefundedThisMonth, 2) }}</div>
    </div>
    <div class="stat-card">
      <h4>All Time Refunds</h4>
      <div class="amount">₱{{ number_format($totalRefunds, 2) }}</div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="card">
    <div class="card-body">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h2 class="card-title" style="margin: 0;">Process Refund</h2>
        <button class="btn btn-primary" onclick="openRefundModal()" style="margin-left: auto;">
          <i class="mdi mdi-plus"></i> New Refund
        </button>
      </div>

      <!-- Refund Form Section -->
      <div class="refund-form-section" id="refundFormSection" style="display: none;">
        <h5 style="margin-bottom: 1.5rem; color: #ffffff;">Search & Refund Product</h5>
        
        <!-- Search Customer -->
        <div class="form-group">
          <label class="form-label">Search Customer (ID or Name)</label>
          <div class="relative">
            <input 
              type="text" 
              id="customerSearch" 
              class="form-control" 
              placeholder="Search by customer name or receipt #..."
              autocomplete="off"
            >
            <div id="searchResults" class="autocomplete-results"></div>
          </div>
        </div>

        <!-- Selected Payment Info -->
        <div id="paymentInfo" style="display: none; margin-bottom: 1.5rem;">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Receipt #</label>
              <input type="text" class="form-control" id="selectedReceipt" readonly>
            </div>
            <div class="form-group">
              <label class="form-label">Customer Name</label>
              <input type="text" class="form-control" id="selectedCustomer" readonly>
            </div>
            <div class="form-group">
              <label class="form-label">Total Amount</label>
              <input type="text" class="form-control" id="selectedAmount" readonly>
            </div>
            <div class="form-group">
              <label class="form-label">Remaining Refundable</label>
              <input type="text" class="form-control" id="selectedRemainingAmount" readonly>
            </div>
            <div class="form-group">
              <label class="form-label">Payment Method</label>
              <input type="text" class="form-control" id="selectedMethod" readonly>
            </div>
          </div>

          <!-- Products List -->
          <div style="margin-top: 1.5rem;">
            <label class="form-label">Products</label>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>Product Name</th>
                    <th>Original Qty</th>
                    <th>Refunded Qty</th>
                    <th>Available</th>
                    <th>Unit Price</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="productsList"></tbody>
              </table>
            </div>
          </div>

          <!-- Refund Form Section -->
          <div id="productRefundFormSection" style="margin-top: 2rem; display: none; background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
            <h5 style="margin-bottom: 1.5rem; color: #333;">Refund Product</h5>
            
            <form id="productRefundForm" method="POST">
              @csrf
              <input type="hidden" name="type" id="refundType">
              <input type="hidden" name="payment_id" id="refundPaymentId">
              <input type="hidden" name="product_name" id="refundProductName">
              
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Product Name</label>
                  <input type="text" class="form-control" id="refundProductDisplay" readonly>
                </div>
                <div class="form-group">
                  <label class="form-label">Quantity to Refund</label>
                  <input type="number" class="form-control" name="refund_quantity" id="refundQuantity" 
                         min="1" max="100" step="1" placeholder="Enter quantity" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Refund Amount</label>
                  <input type="text" class="form-control" id="refundAmount" readonly>
                </div>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Refund Reason</label>
                  <textarea class="form-control" name="refund_reason" id="refundReason" 
                            placeholder="Enter reason for refund" rows="3" required></textarea>
                </div>
                <div class="form-group">
                  <label class="form-label">Refund Method</label>
                  <select class="form-select" name="refund_method" id="refundMethod" required>
                    <option value="">Select method...</option>
                    <option value="cash">Cash</option>
                    <option value="card_reversal">Card Reversal</option>
                    <option value="store_credit">Store Credit</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Processed By</label>
                  <input type="text" class="form-control" name="processed_by" id="processedBy" 
                         placeholder="Enter name of person processing refund" required>
                </div>
              </div>

              <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-danger" onclick="confirmRefund()">
                  <i class="mdi mdi-check"></i> Confirm Refund
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeRefundForm()">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>

        <button class="btn btn-secondary" onclick="closeRefundModal()">Close</button>
      </div>

      <!-- Refund Items Form (Hidden) -->
      <form id="refundForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="payment_item_id" id="selectedItemId">
        <input type="hidden" name="payment_id" id="selectedPaymentId">
        <input type="hidden" name="payment_type" id="selectedPaymentType">
        <input type="hidden" name="quantity" id="refundQuantity">
        <input type="hidden" name="reason" id="refundReason">
      </form>
    </div>
  </div>

  <!-- Refund History Card -->
  <div class="card" style="margin-top: 2rem;">
    <div class="card-body">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 class="card-title" style="margin-bottom: 0;">Refund History</h2>
        
        <div style="display: flex; gap: 1rem; align-items: center;">
          <!-- Filter Dropdown -->
          <div class="filter-dropdown">
            <button type="button" class="filter-btn" id="filterBtn">
              <i class="mdi mdi-filter"></i> Filter
            </button>
            <div class="filter-menu" id="filterMenu">
              <a href="{{ route('refunds.index') }}" class="filter-menu-item {{ !request('filter_status') ? 'active' : '' }}">
                <i class="mdi mdi-check-all"></i> All Refunds
              </a>
              <a href="{{ route('refunds.index', ['filter_status' => 'approved']) }}" 
                class="filter-menu-item {{ request('filter_status') == 'approved' ? 'active' : '' }}">
                <i class="mdi mdi-check-circle"></i> Approved
              </a>
              <a href="{{ route('refunds.index', ['filter_status' => 'pending']) }}" 
                class="filter-menu-item {{ request('filter_status') == 'pending' ? 'active' : '' }}">
                <i class="mdi mdi-clock"></i> Pending
              </a>
              <div class="filter-menu-divider"></div>
              <a href="{{ route('refunds.index') }}" class="filter-menu-item" style="color: #dc3545;">
                <i class="mdi mdi-close-circle"></i> Clear Filters
              </a>
            </div>
          </div>
          
          <!-- Search Form -->
          <form action="{{ route('refunds.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
            <input type="hidden" name="filter_status" value="{{ request('filter_status') }}">
            <input 
              type="text" 
              class="form-control" 
              name="search" 
              placeholder="Search refunds..." 
              value="{{ request('search') }}"
              style="min-width: 250px;"
            >
            <button type="submit" class="btn btn-primary">
              <i class="mdi mdi-magnify"></i>
            </button>
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">
                <input type="checkbox" class="custom-checkbox" id="selectAll">
              </th>
              <th>Receipt #</th>
              <th>Customer Name</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Refund Amount</th>
              <th>Status</th>
              <th>Processed By</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $refundCount = isset($refunds) ? $refunds->count() : 0;
              $maxRows = 10;
            @endphp
            
            @if(isset($refunds) && $refunds->count() > 0)
              @foreach($refunds as $refund)
              <tr>
                <td>
                  <input type="checkbox" class="custom-checkbox refund-checkbox" value="{{ $refund->id }}">
                </td>
                <td><strong>#{{ $refund->receipt_number }}</strong></td>
                <td>{{ $refund->customer_name }}</td>
                <td>{{ $refund->product_name }}</td>
                <td><strong>{{ $refund->quantity }}</strong></td>
                <td><strong style="color: #28a745;">₱{{ number_format($refund->refund_amount, 2) }}</strong></td>
                <td>
                  @if($refund->status == 'completed')
                    <span class="badge badge-success">Approved</span>
                  @elseif($refund->status == 'pending')
                    <span class="badge badge-warning">Pending</span>
                  @else
                    <span class="badge badge-danger">Rejected</span>
                  @endif
                </td>
                <td>{{ $refund->refunded_by }}</td>
                <td>{{ $refund->created_at->format('M d, Y h:i A') }}</td>
                <td>
                  <div class="action-dropdown">
                    <button class="action-btn" onclick="toggleDropdown(this)">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                      <button class="dropdown-item" onclick="viewRefundDetails({{ $refund->id }})" style="color: #007bff;">
                        <i class="mdi mdi-eye"></i> View Details
                      </button>
                      @if($refund->status != 'completed')
                      <button class="dropdown-item danger" onclick="deleteRefund({{ $refund->id }})">
                        <i class="mdi mdi-delete"></i> Delete
                      </button>
                      @endif
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            @endif
            
            @for($i = $refundCount; $i < $maxRows; $i++)
            <tr>
              <td colspan="10" style="height: 53px; text-align: center; color: #999;">
                @if($i == 0)
                  No refunds found
                @endif
              </td>
            </tr>
            @endfor
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-container">
        <button class="btn btn-danger" id="bulkDeleteBtn" disabled>
          <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
        </button>
        
        @if(isset($refunds) && $refunds->total() > 0)
        <nav>
          <ul class="pagination">
            @if ($refunds->onFirstPage())
              <li class="page-item disabled">
                <span class="page-link">‹</span>
              </li>
            @else
              <li class="page-item">
                <a class="page-link" href="{{ $refunds->appends(request()->query())->previousPageUrl() }}">‹</a>
              </li>
            @endif

            @foreach(range(1, min(5, $refunds->lastPage())) as $page)
              @if($page == $refunds->currentPage())
              <li class="page-item active">
                <span class="page-link">{{ $page }}</span>
              </li>
              @else
              <li class="page-item">
                <a class="page-link" href="{{ $refunds->appends(request()->query())->url($page) }}">{{ $page }}</a>
              </li>
              @endif
            @endforeach

            @if ($refunds->hasMorePages())
              <li class="page-item">
                <a class="page-link" href="{{ $refunds->appends(request()->query())->nextPageUrl() }}">›</a>
              </li>
            @else
              <li class="page-item disabled">
                <span class="page-link">›</span>
              </li>
            @endif
          </ul>
        </nav>

        <div class="pagination-info">
          Showing {{ $refunds->firstItem() ?? 0 }} to {{ $refunds->lastItem() ?? 0 }} of {{ $refunds->total() }} entries
        </div>
        @else
        <nav>
          <ul class="pagination">
            <li class="page-item disabled"><span class="page-link">‹</span></li>
            <li class="page-item active"><span class="page-link">1</span></li>
            <li class="page-item disabled"><span class="page-link">›</span></li>
          </ul>
        </nav>
        <div class="pagination-info">Showing 0 to 0 of 0 entries</div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Refund Item Modal -->
<div id="refundItemModal" class="modal-overlay" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Refund Product</h5>
      <button type="button" class="modal-close" onclick="closeRefundItemModal()">×</button>
    </div>
    <div class="modal-body">
      <form id="refundItemForm" style="display: none;">
        @csrf
      </form>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Product Name</label>
          <input type="text" id="itemProductName" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label class="form-label">Original Quantity</label>
          <input type="text" id="itemOriginalQty" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label class="form-label">Available for Refund</label>
          <input type="text" id="itemAvailableQty" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label class="form-label">Unit Price</label>
          <input type="text" id="itemUnitPrice" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label class="form-label">Refund Quantity</label>
          <input type="number" id="itemRefundQty" class="form-control" min="1" placeholder="Enter quantity">
        </div>
        <div class="form-group">
          <label class="form-label">Refund Amount</label>
          <input type="text" id="itemRefundAmount" class="form-control" readonly>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Reason for Refund (Optional)</label>
        <textarea id="itemRefundReason" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeRefundItemModal()">Cancel</button>
      <button type="button" class="btn btn-primary" onclick="submitRefund()">Process Refund</button>
    </div>
  </div>
</div>

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" action="{{ route('refunds.bulk-delete') }}" method="POST" style="display: none;">
  @csrf
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Notification Container -->
<div id="notification" class="notification">
  <span id="notificationMessage"></span>
</div>

@endsection

@push('scripts')
<script>
// Current selected payment data
let currentPayment = null;

document.addEventListener('DOMContentLoaded', function() {
  // Search customers with autocomplete
  const customerSearch = document.getElementById('customerSearch');
  const searchResults = document.getElementById('searchResults');
  let searchTimeout;

  customerSearch.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();

    if (query.length < 2) {
      searchResults.classList.remove('show');
      searchResults.innerHTML = '';
      return;
    }

    searchTimeout = setTimeout(() => {
      fetch(`{{ route('refunds.search-customer') }}?q=${encodeURIComponent(query)}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          searchResults.innerHTML = '';
          
          if (!data.results || data.results.length === 0) {
            searchResults.innerHTML = '<div class="autocomplete-item" style="cursor: default; color: #999;">No customers found</div>';
            searchResults.classList.add('show');
            return;
          }

          data.results.forEach(result => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            
            const typeLabel = result.type === 'pos' ? 'POS' : 'Membership';
            const statusColor = result.payment_status === 'completed' ? '#4CAF50' : 
                               result.payment_status === 'partially_refunded' ? '#FF9800' : '#F44336';
            
            item.innerHTML = `
              <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div>
                  <strong>${result.customer_name || 'Unknown'}</strong>
                  <div style="font-size: 0.85em; color: #999; margin-top: 3px;">
                    <span style="background: #e3e3e3; padding: 2px 8px; border-radius: 3px; margin-right: 8px;">${typeLabel}</span>
                    <span>${result.receipt_number ? 'Rcpt #' + result.receipt_number : 'ID: ' + result.id}</span>
                  </div>
                </div>
                <div style="text-align: right;">
                  <div style="font-weight: 500;">₱${(result.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                  <div style="font-size: 0.85em; color: #666; margin-top: 3px;">
                    Remaining: ₱${(result.remaining_refundable || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}
                  </div>
                </div>
              </div>
            `;
            
            item.onclick = () => selectCustomer(result);
            item.style.cursor = 'pointer';
            searchResults.appendChild(item);
          });

          searchResults.classList.add('show');
        })
        .catch(error => {
          console.error('Search error:', error);
          searchResults.innerHTML = '<div class="autocomplete-item" style="cursor: default; color: #F44336;">Error searching customers. Please try again.</div>';
          searchResults.classList.add('show');
        });
    }, 300);
  });

  // Close search results when clicking outside
  document.addEventListener('click', function(e) {
    if (e.target !== customerSearch && !searchResults.contains(e.target)) {
      searchResults.classList.remove('show');
    }
  });

  // Select All Checkboxes
  const selectAllCheckbox = document.getElementById('selectAll');
  const refundCheckboxes = document.querySelectorAll('.refund-checkbox');

  selectAllCheckbox.addEventListener('change', function() {
    refundCheckboxes.forEach(checkbox => {
      checkbox.checked = this.checked;
    });
    updateSelectedCount();
  });

  refundCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
  });

  function updateSelectedCount() {
    const selected = document.querySelectorAll('.refund-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
    document.getElementById('bulkDeleteBtn').disabled = selected === 0;
  }

  // Filter Menu Toggle
  const filterBtn = document.getElementById('filterBtn');
  const filterMenu = document.getElementById('filterMenu');

  filterBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    filterMenu.classList.toggle('show');
  });

  document.addEventListener('click', function() {
    filterMenu.classList.remove('show');
  });

  // Refund Quantity Calculator
  document.getElementById('itemRefundQty').addEventListener('input', function() {
    const unitPrice = parseFloat(document.getElementById('itemUnitPrice').value) || 0;
    const refundAmount = (this.value || 0) * unitPrice;
    document.getElementById('itemRefundAmount').value = '₱' + refundAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
  });
});

function openRefundModal() {
  document.getElementById('refundFormSection').style.display = 'block';
  document.getElementById('customerSearch').focus();
}

function closeRefundModal() {
  document.getElementById('refundFormSection').style.display = 'none';
  document.getElementById('customerSearch').value = '';
  document.getElementById('paymentInfo').style.display = 'none';
  document.getElementById('searchResults').classList.remove('show');
  currentPayment = null;
}

function openRefundForm(itemId, productName, originalQty, availableQty, unitPrice) {
  // Store current refund state
  const currentRefundData = {
    itemId: itemId,
    productName: productName,
    originalQty: originalQty,
    availableQty: availableQty,
    unitPrice: unitPrice
  };
  window.currentRefundData = currentRefundData;

  // Populate form
  document.getElementById('refundProductName').value = productName;
  document.getElementById('refundProductDisplay').value = productName;
  document.getElementById('refundQuantity').max = availableQty;
  document.getElementById('refundQuantity').value = '';
  document.getElementById('refundReason').value = '';
  document.getElementById('refundMethod').value = '';
  document.getElementById('processedBy').value = '{{ Auth::user()?->name ?? "" }}';
  document.getElementById('refundAmount').value = '₱0.00';
  document.getElementById('refundType').value = currentPayment.type;
  document.getElementById('refundPaymentId').value = currentPayment.id;

  // Show form
  document.getElementById('productRefundFormSection').style.display = 'block';
  document.getElementById('refundQuantity').focus();

  // Add event listener for quantity change
  document.getElementById('refundQuantity').addEventListener('change', function() {
    const qty = parseFloat(this.value) || 0;
    const amount = qty * unitPrice;
    document.getElementById('refundAmount').value = '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
  });
}

function closeRefundForm() {
  document.getElementById('productRefundFormSection').style.display = 'none';
  window.currentRefundData = null;
}

function confirmRefund() {
  const quantity = parseInt(document.getElementById('refundQuantity').value);
  const reason = document.getElementById('refundReason').value.trim();
  const method = document.getElementById('refundMethod').value;
  const processedBy = document.getElementById('processedBy').value.trim();

  if (!quantity || quantity <= 0) {
    showNotification('Please enter a valid quantity', 'error');
    return;
  }

  if (!reason) {
    showNotification('Please enter a refund reason', 'error');
    return;
  }

  if (!method) {
    showNotification('Please select a refund method', 'error');
    return;
  }

  if (!processedBy) {
    showNotification('Please enter the name of the person processing this refund', 'error');
    return;
  }

  // Calculate refund amount
  const refundAmount = quantity * window.currentRefundData.unitPrice;

  // Prepare form data
  const formData = new FormData(document.getElementById('productRefundForm'));
  formData.set('refund_amount', refundAmount);
  formData.set('refund_reason', reason);
  formData.set('refund_method', method);
  formData.set('processed_by', processedBy);
  formData.set('refund_quantity', quantity);

  // Submit refund
  fetch('{{ route("refunds.store") }}', {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Refund processed successfully!', 'success');
      setTimeout(() => {
        closeRefundForm();
        location.reload();
      }, 1500);
    } else {
      showNotification('Error: ' + (data.message || 'Failed to process refund'), 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('An error occurred while processing the refund', 'error');
  });
}

function selectCustomer(customer) {
  currentPayment = customer;
  
  // Update search input with customer name
  document.getElementById('customerSearch').value = customer.customer_name || '';
  document.getElementById('searchResults').classList.remove('show');

  // Populate payment info directly from search result
  document.getElementById('selectedReceipt').value = customer.receipt_number || '';
  document.getElementById('selectedCustomer').value = customer.customer_name || '';
  document.getElementById('selectedAmount').value = '₱' + (customer.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
  document.getElementById('selectedRemainingAmount').value = '₱' + (customer.remaining_refundable || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
  
  // Store payment details for form submission
  document.getElementById('selectedPaymentId').value = customer.id;
  document.getElementById('selectedPaymentType').value = customer.type;

  // Update payment info visibility and status
  document.getElementById('paymentInfo').style.display = 'block';
  
  // Fetch payment details (items and payment method)
  const detailsUrl = `{{ route('refunds.get-details') }}?type=${customer.type}&id=${customer.id}`;
  fetch(detailsUrl)
    .then(response => {
      if (!response.ok) throw new Error('Failed to fetch details');
      return response.json();
    })
    .then(data => {
      if (data.success) {
        // Set payment method
        document.getElementById('selectedMethod').value = data.payment_method || 'N/A';
        
        // Populate products table
        const productsList = document.getElementById('productsList');
        productsList.innerHTML = '';
        
        if (data.items && data.items.length > 0) {
          data.items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${item.product_name}</td>
              <td style="text-align: center;">${item.quantity}</td>
              <td style="text-align: center;">${item.refunded_quantity || 0}</td>
              <td style="text-align: center;"><strong>${item.available_quantity}</strong></td>
              <td>₱${(item.unit_price || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
              <td>
                <button type="button" class="btn btn-sm btn-success" onclick="openRefundForm(${item.id}, '${item.product_name}', ${item.quantity}, ${item.available_quantity}, ${item.unit_price})">
                  <i class="mdi mdi-cash-refund"></i> Refund
                </button>
              </td>
            `;
            productsList.appendChild(row);
          });
        } else {
          productsList.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #999;">No items found for this payment</td></tr>';
        }
      }
    })
    .catch(error => {
      console.log('Note: Using basic payment info from search result');
    });
}

function openRefundItemModal(itemId, productName, originalQty, availableQty, unitPrice, paymentId) {
  document.getElementById('itemProductName').value = productName;
  document.getElementById('itemOriginalQty').value = originalQty;
  document.getElementById('itemAvailableQty').value = availableQty;
  document.getElementById('itemUnitPrice').value = unitPrice;
  document.getElementById('itemRefundQty').value = '';
  document.getElementById('itemRefundAmount').value = '₱0.00';
  document.getElementById('itemRefundReason').value = '';

  document.getElementById('selectedItemId').value = itemId;
  document.getElementById('selectedPaymentId').value = paymentId;

  document.getElementById('refundItemModal').classList.add('show');
}

function closeRefundItemModal() {
  document.getElementById('refundItemModal').classList.remove('show');
}

function submitRefund() {
  const quantity = parseInt(document.getElementById('itemRefundQty').value) || 0;
  const availableQty = parseInt(document.getElementById('itemAvailableQty').value);

  if (quantity <= 0) {
    showNotification('Please enter a quantity', 'error');
    return;
  }

  if (quantity > availableQty) {
    showNotification(`Only ${availableQty} units available for refund`, 'error');
    return;
  }

  const paymentId = document.getElementById('selectedPaymentId').value;
  const itemId = document.getElementById('selectedItemId').value;
  const reason = document.getElementById('itemRefundReason').value;

  // Submit refund
  fetch(`/refunds/${paymentId}/process-refund`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '__CSRF__'
    },
    body: JSON.stringify({
      payment_item_id: itemId,
      quantity: quantity,
      reason: reason
    })
  })
  .then(response => {
    if (response.ok) {
      showNotification('Refund processed successfully!', 'success');
      setTimeout(() => location.reload(), 1500);
    } else {
      showNotification('Error processing refund', 'error');
    }
  })
  .catch(error => {
    console.error(error);
    showNotification('Error processing refund', 'error');
  });

  closeRefundItemModal();
}

function viewRefundDetails(id) {
  // Could implement a detail view here
  showNotification('Refund ID: ' + id, 'info');
}

function deleteRefund(id) {
  if (confirm('Are you sure you want to delete this refund?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/refunds/${id}`;
    form.innerHTML = `
      @csrf
      @method('DELETE')
    `;
    document.body.appendChild(form);
    form.submit();
  }
}

// Bulk Delete
document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
  const selected = Array.from(document.querySelectorAll('.refund-checkbox:checked')).map(cb => cb.value);
  
  if (selected.length === 0) {
    showNotification('Please select refunds to delete', 'error');
    return;
  }

  if (confirm(`Are you sure you want to delete ${selected.length} refund(s)?`)) {
    document.getElementById('bulkDeleteIds').value = JSON.stringify(selected);
    document.getElementById('bulkDeleteForm').submit();
  }
});

// Toggle Dropdown
function toggleDropdown(button) {
  event.stopPropagation();
  const menu = button.nextElementSibling;
  
  // Close all other dropdowns
  document.querySelectorAll('.dropdown-menu.show').forEach(m => {
    if (m !== menu) m.classList.remove('show');
  });

  menu.classList.toggle('show');
}

document.addEventListener('click', function() {
  document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
    menu.classList.remove('show');
  });
});

// Show Notification
function showNotification(message, type = 'success') {
  const notification = document.getElementById('notification');
  const notificationMessage = document.getElementById('notificationMessage');
  
  notification.className = `notification show ${type}`;
  notificationMessage.textContent = message;
  
  setTimeout(() => {
    notification.classList.remove('show');
  }, 3000);
}

// Display Laravel validation errors
@if(session('success'))
  showNotification('{{ session('success') }}', 'success');
@endif

@if(session('error'))
  showNotification('{{ session('error') }}', 'error');
@endif

@if($errors->any())
  showNotification('{{ $errors->first() }}', 'error');
@endif
</script>
@endpush
