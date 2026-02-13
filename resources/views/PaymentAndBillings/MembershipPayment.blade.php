@extends('layouts.admin')

@section('title', 'Membership Payment System')

@push('styles')
<style>
  /* CONSISTENT COLOR SCHEME FROM PRODUCT PAYMENT */
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

  /* Stats Cards - Consistent Style */
  .stat-change {
    font-size: 0.875rem;
    margin-top: 0.5rem;
  }

  .stat-change.positive {
    color: #28a745;
  }

  .stat-change.negative {
    color: #dc3545;
  }

  /* Card Styles */
  .card {
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 1.5rem;
  }

  /* Form Styles */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

  .form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #999;
    margin-bottom: 0.5rem;
  }

  .form-control, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    background: #191C24;
    border: 1px solid #555;
    border-radius: 4px;
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    outline: none;
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    background: #282A36;
  }

  .form-control::placeholder {
    color: #666;
  }

  /* Autocomplete Results */
  .autocomplete-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 250px;
    overflow-y: auto;
    background: #282A36;
    border: 1px solid #555;
    border-top: none;
    border-radius: 0 0 4px 4px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  }

  .autocomplete-item {
    padding: 1rem;
    cursor: pointer;
    border-bottom: 1px solid #555;
    transition: all 0.2s ease;
    color: #ffffff;
  }

  .autocomplete-item:hover {
    background: #191C24;
  }

  .autocomplete-item:last-child {
    border-bottom: none;
  }

  /* Buttons */
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
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  }

  .btn-primary {
    background: #0d6efd;
    color: white;
  }

  .btn-primary:hover {
    background: #138496;
  }

  .btn-secondary {
    background: #6c757d;
    color: white;
  }

  .btn-secondary:hover {
    background: #5a6268;
  }

  .btn-danger {
    background: #dc3545;
    color: white;
  }

  .btn-danger:hover {
    background: #c82333;
  }

  .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
  }

  /* Payment Type Pills */
  .payment-type-selector {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .payment-type-pill {
    flex: 1;
    padding: 1rem;
    background: #191C24;
    border: 2px solid #555;
    border-radius: 4px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .payment-type-pill:hover {
    border-color: #198754;
    transform: translateY(-3px);
  }

  .payment-type-pill.active {
    background: #282A36;
    border-color: #198754;
  }

  .payment-type-pill .icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
  }

  .payment-type-pill .label {
    font-weight: 600;
    font-size: 0.875rem;
    color: #ffffff;
  }

  /* Plan Type Selector */
  .plan-type-selector {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .plan-type-card {
    flex: 1;
    padding: 1.5rem;
    background: #191C24;
    border: 2px solid #555;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .plan-type-card:hover {
    border-color: #198754;
    transform: translateY(-3px);
  }

  .plan-type-card.active {
    background: #282A36;
    border-color: #198754;
  }

  .plan-type-card .plan-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
  }

  .plan-type-card .plan-duration {
    font-size: 0.875rem;
    color: #999;
  }

  .plan-type-card .plan-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
    margin-top: 0.5rem;
  }

  /* Table Styles */
  .table-responsive {
    overflow-x: auto;
    min-height: 600px;
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
  }

  .table tbody tr {
    transition: all 0.3s ease;
  }

  .table tbody tr:hover {
    background: rgba(255, 255, 255, 0.1) !important;
  }

  .table td {
    padding: 1rem;
    border-bottom: 1px solid #555;
    color: #ffffff !important;
  }

  /* Badge Styles */
  .badge {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
  }

  .badge-success {
    background: #28a745;
    color: white;
  }

  .badge-info {
    background: #0d6efd;
    color: white;
  }

  .badge-warning {
    background: #ffc107;
    color: #000;
  }

  /* Action Dropdown - FIXED Z-INDEX */
  .action-dropdown {
    position: relative;
  }

  .action-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #ffffff;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 180px;
    background: #282A36;
    border: 1px solid #555;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    padding: 8px 0;
    display: none;
    z-index: 10000 !important; /* FIXED: Above all components */
    animation: slideDown 0.3s ease;
  }

  .dropdown-menu.show {
    display: block;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .dropdown-item {
    padding: 12px 20px;
    font-size: 14px;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    background: none;
    border: none;
    width: 100%;
    text-align: left;
  }

  .dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
  }

  .dropdown-item i {
    margin-right: 0.5rem;
  }

  .dropdown-item.danger {
    color: #ff6b6b;
  }

  .dropdown-item.danger:hover {
    background: rgba(255, 107, 107, 0.1);
  }

  /* Pagination - ALWAYS VISIBLE */
  .pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #555;
  }

  .pagination {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .page-item {
    display: inline-block;
  }

  /* Checkbox Styles */
  .custom-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #dc3545;
    border-radius: 4px;
    background-color: transparent;
    cursor: pointer;
    appearance: none;
    transition: all 0.3s ease;
  }

  .custom-checkbox:checked {
    background-color: #dc3545;
    border-color: #dc3545;
  }

  .custom-checkbox:checked::after {
    content: '✓';
    display: block;
    text-align: center;
    color: white;
    font-weight: bold;
    font-size: 12px;
    line-height: 14px;
  }

  /* Modal Styles - FIXED CENTERING */
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
    align-items: center; /* FIXED: Center vertically */
    justify-content: center; /* FIXED: Center horizontally */
  }

  .modal-overlay.show {
    display: flex; /* FIXED: Use flex for centering */
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

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

  /* Receipt Styles */
  .receipt-container {
    background: white;
    color: #333;
  }

  .receipt-header {
    text-align: center;
    border-bottom: 3px solid #191C24;
    padding-bottom: 1.5rem;
    margin-bottom: 2rem;
  }

  .receipt-header h2 {
    font-size: 2rem;
    color: #191C24;
    margin-bottom: 0.5rem;
  }

  .receipt-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .receipt-info-item {
    padding: 1rem;
    background: #f5f5f5;
    border-radius: 4px;
  }

  .receipt-info-item strong {
    display: block;
    font-size: 0.75rem;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
  }

  .receipt-info-item span {
    display: block;
    font-size: 1rem;
    color: #333;
    font-weight: 600;
  }

  .receipt-table {
    width: 100%;
    margin-bottom: 2rem;
    border-collapse: collapse;
  }

  .receipt-table th {
    background: #191C24;
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
  }

  .receipt-table td {
    padding: 1rem;
    border-bottom: 1px solid #ddd;
    color: #333;
  }

  .receipt-total {
    text-align: right;
    padding-top: 1rem;
    border-top: 3px solid #191C24;
  }

  .receipt-total-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.5rem;
    font-size: 1.125rem;
  }

  .receipt-total-row strong {
    width: 200px;
  }

  .receipt-total-row span {
    width: 150px;
    text-align: right;
    font-weight: 700;
  }

  .receipt-total-row.grand-total {
    font-size: 1.5rem;
    color: #191C24;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #333;
  }

  /* Notifications */
  .notification {
    position: fixed;
    top: 2rem;
    right: 2rem;
    max-width: 400px;
    padding: 1.5rem;
    border-radius: 8px;
    border: 2px solid;
    display: none;
    z-index: 10001;
    animation: slideInRight 0.3s ease;
  }

  @keyframes slideInRight {
    from {
      transform: translateX(400px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  .notification.show {
    display: block;
  }

  .notification.success {
    background: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #28a745;
  }

  .notification.error {
    background: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #dc3545;
  }

  .notification.warning {
    background: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
  }

  /* Loading State */
  .loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #555;
    border-top-color: #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* Filter Dropdown */
  .filter-dropdown {
    position: relative;
  }

  .filter-btn {
    background: #282A36;
    border: 1px solid #555;
    color: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .filter-btn:hover {
    background: #191C24;
    border-color: #198754;
  }

  .filter-menu {
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 250px;
    background: #282A36;
    border: 1px solid #555;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    padding: 0.5rem 0;
    display: none;
    z-index: 1000;
    max-height: 400px;
    overflow-y: auto;
  }

  .filter-menu.show {
    display: block;
  }

  .filter-header {
    padding: 0.75rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    border-bottom: 1px solid #555;
  }

  .filter-menu-item {
    padding: 0.75rem 1.25rem;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
  }

  .filter-menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
  }

  .filter-menu-item.active {
    background: rgba(23, 162, 184, 0.2);
    color: #198754;
  }

  .filter-menu-divider {
    height: 1px;
    background: #555;
    margin: 0.5rem 0;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }

    .form-grid {
      grid-template-columns: 1fr;
    }

    .payment-type-selector,
    .plan-type-selector {
      flex-direction: column;
    }

    .pagination-container {
      flex-direction: column;
      gap: 1rem;
    }
  }

  /* Print Styles - FIXED */
  @media print {
    @page {
      margin: 0.5in;
    }

    body * {
      visibility: hidden;
    }

    .receipt-container,
    .receipt-container * {
      visibility: visible;
    }

    .receipt-container {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      background: white;
      color: black;
    }

    .modal-header,
    .modal-footer,
    .modal-close {
      display: none !important;
    }

    .modal-overlay {
      background: white !important;
      backdrop-filter: none !important;
    }

    .modal-content {
      box-shadow: none !important;
      max-height: none !important;
      overflow: visible !important;
    }

    .modal-body {
      padding: 0 !important;
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
</style>
@endpush

@section('content')
<div class="container-fluid">

  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Membership Payment</h2>
              <p class="page-header-subtitle">Process membership payments and manage billing records.</p>
          </div>
      </div>
  </div>

  <!-- Stats Grid -->
  <div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">₱{{ number_format($monthlyRevenue ?? 0, 2) }}</h2>
                        <p class="text-muted mb-0">Monthly Revenue</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="mdi mdi-arrow-up"></i> +12.5%
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
                        <h2 class="mb-0">{{ $activeMemberships ?? 0 }}</h2>
                        <p class="text-muted mb-0">Active Memberships</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="mdi mdi-arrow-up"></i> +8 this week
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
                        <h2 class="mb-0">{{ $expiringSoon ?? 0 }}</h2>
                        <p class="text-muted mb-0">Expiring Soon</p>
                    </div>
                    <div class="stat-change negative">
                        <i class="mdi mdi-alert-circle"></i> Within 7 days
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
                        <h2 class="mb-0">₱{{ number_format($todayRevenue ?? 0, 2) }}</h2>
                        <p class="text-muted mb-0">Today's Revenue</p>
                    </div>
                    <div class="stat-change positive">
                        <i class="mdi mdi-arrow-up"></i> +15.3%
                    </div>
                </div>
            </div>    
        </div>
    </div>
  </div>
</div>

  <!-- Payment Form Card -->
  <div class="card">
    <div class="card-body">
        <h2 class="card-title">Process Membership Payment</h2>
        
        <form id="membershipPaymentForm" action="{{ route('membership.payment.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Payment Type Selector -->
        <div class="payment-type-selector">
        <div class="payment-type-pill" data-type="new">
            <span class="icon">
            <i class="mdi mdi-account-plus"></i>
            </span>
            <span class="label">New Membership</span>
        </div>

        <div class="payment-type-pill active" data-type="renewal">
            <span class="icon">
            <i class="mdi mdi-autorenew"></i>
            </span>
            <span class="label">Renewal</span>
        </div>

        <div class="payment-type-pill" data-type="extension" id="extensionPill">
            <span class="icon">
            <i class="mdi mdi-calendar-plus"></i>
            </span>
            <span class="label">Extension</span>
        </div>
        </div>
        
        <input type="hidden" name="payment_type" id="paymentType" value="renewal">

        <!-- Member Selection (Hidden for New Membership) -->
        <div id="memberSelectionSection">
            <div class="form-group">
            <label class="form-label">Select Member</label>
            <div style="position: relative;">
                <input 
                type="text" 
                class="form-control" 
                id="memberSearch" 
                name="member_search"
                placeholder="Search by name or contact..."
                autocomplete="off"
                >
                <input type="hidden" name="member_id" id="memberId">
                <input type="hidden" id="memberStatus">
                <div id="memberResults" class="autocomplete-results"></div>
            </div>
            </div>
        </div>

        <!-- New Member Details (Shown only for New Membership) -->
        <div id="newMemberSection" style="display: none;">
            <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="new_member_name" id="newMemberName" placeholder="Enter full name">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control" name="new_member_contact" id="newMemberContact" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label">Avatar (Optional)</label>
                <input type="file" class="form-control" name="new_member_avatar" id="newMemberAvatar" accept="image/*">
            </div>
            </div>
        </div>

        <!-- Plan Type Selector -->
        <label class="form-label">Select Plan Type</label>
        <div class="plan-type-selector">
            <div class="plan-type-card active" data-plan="Monthly" data-price="500" data-duration="30">
            <div class="plan-name">Monthly Plan</div>
            <div class="plan-duration">30 Days Access</div>
            <div class="plan-price">₱500.00</div>
            </div>
            <div class="plan-type-card" data-plan="Session" data-price="50" data-duration="1">
            <div class="plan-name">Session Pass</div>
            <div class="plan-duration">1 Day Access</div>
            <div class="plan-price">₱50.00</div>
            </div>
        </div>
        <input type="hidden" name="plan_type" id="planType" value="Monthly">

        <!-- Payment Details -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" id="paymentMethod" required>
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="GCash">GCash</option>
                                <option value="PayMaya">PayMaya</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                            </div>

                            <div class="col-md-2">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" id="amount" placeholder="₱0.00" step="0.01" value="500.00" readonly>
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">Current Due Date</label>
                            <input type="text" class="form-control" id="currentDueDate" readonly placeholder="N/A">
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">New Due Date</label>
                            <input type="text" class="form-control" name="new_due_date" id="newDueDate" readonly placeholder="Will be calculated">
                            </div>

                            <div class="col-md-2">
                            <label class="form-label">Additional Days</label>
                            <input type="number" class="form-control" id="additionalDays" readonly placeholder="0" value="30">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
            <button type="button" class="btn btn-secondary" id="clearFormBtn">
            <i class="mdi mdi-close"></i> Clear
            </button>
            <button type="submit" class="btn btn-primary" id="submitPaymentBtn">
            <i class="mdi mdi-check"></i> Process Payment
            </button>
        </div>
        </form>
    </div>
  </div>

  <!-- Transaction History Card -->
  <div class="card">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 class="card-title" style="margin-bottom: 0;">Transaction History</h2>
        
        <div style="display: flex; gap: 1rem; align-items: center;">
            <!-- Filter Dropdown -->
            <div class="filter-dropdown">
            <button type="button" class="filter-btn" id="filterBtn">
                <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <div class="filter-menu" id="filterMenu">
                <div class="filter-header">Sort By</div>
                <a href="{{ route('membership.payment.index', ['sort' => 'date_newest', 'search' => request('search'), 'filter_plan' => request('filter_plan'), 'filter_method' => request('filter_method')]) }}" 
                class="filter-menu-item {{ request('sort') == 'date_newest' || !request('sort') ? 'active' : '' }}">
                <i class="mdi mdi-calendar-clock"></i> Date (Newest)
                </a>
                <a href="{{ route('membership.payment.index', ['sort' => 'date_oldest', 'search' => request('search'), 'filter_plan' => request('filter_plan'), 'filter_method' => request('filter_method')]) }}" 
                class="filter-menu-item {{ request('sort') == 'date_oldest' ? 'active' : '' }}">
                <i class="mdi mdi-calendar"></i> Date (Oldest)
                </a>
                <a href="{{ route('membership.payment.index', ['sort' => 'name_asc', 'search' => request('search'), 'filter_plan' => request('filter_plan'), 'filter_method' => request('filter_method')]) }}" 
                class="filter-menu-item {{ request('sort') == 'name_asc' ? 'active' : '' }}">
                <i class="mdi mdi-sort-alphabetical-ascending"></i> Name (A-Z)
                </a>
                <a href="{{ route('membership.payment.index', ['sort' => 'name_desc', 'search' => request('search'), 'filter_plan' => request('filter_plan'), 'filter_method' => request('filter_method')]) }}" 
                class="filter-menu-item {{ request('sort') == 'name_desc' ? 'active' : '' }}">
                <i class="mdi mdi-sort-alphabetical-descending"></i> Name (Z-A)
                </a>
                
                <div class="filter-menu-divider"></div>
                <div class="filter-header">Plan Type</div>
                <a href="{{ route('membership.payment.index', ['filter_plan' => 'Monthly', 'search' => request('search'), 'sort' => request('sort'), 'filter_method' => request('filter_method')]) }}" 
                class="filter-menu-item {{ request('filter_plan') == 'Monthly' ? 'active' : '' }}">
                <i class="mdi mdi-calendar-month"></i> Monthly
                </a>
                <a href="{{ route('membership.payment.index', ['filter_plan' => 'Session', 'search' => request('search'), 'sort' => request('sort'), 'filter_method' => request('filter_method')]) }}" 
                class="filter-menu-item {{ request('filter_plan') == 'Session' ? 'active' : '' }}">
                <i class="mdi mdi-clock-outline"></i> Session
                </a>
                
                <div class="filter-menu-divider"></div>
                <div class="filter-header">Payment Method</div>
                <a href="{{ route('membership.payment.index', ['filter_method' => 'Cash', 'search' => request('search'), 'sort' => request('sort'), 'filter_plan' => request('filter_plan')]) }}" 
                class="filter-menu-item {{ request('filter_method') == 'Cash' ? 'active' : '' }}">
                <i class="mdi mdi-cash"></i> Cash
                </a>
                <a href="{{ route('membership.payment.index', ['filter_method' => 'Credit Card', 'search' => request('search'), 'sort' => request('sort'), 'filter_plan' => request('filter_plan')]) }}" 
                class="filter-menu-item {{ request('filter_method') == 'Credit Card' ? 'active' : '' }}">
                <i class="mdi mdi-credit-card"></i> Credit Card
                </a>
                <a href="{{ route('membership.payment.index', ['filter_method' => 'Debit Card', 'search' => request('search'), 'sort' => request('sort'), 'filter_plan' => request('filter_plan')]) }}" 
                class="filter-menu-item {{ request('filter_method') == 'Debit Card' ? 'active' : '' }}">
                <i class="mdi mdi-credit-card-outline"></i> Debit Card
                </a>
                <a href="{{ route('membership.payment.index', ['filter_method' => 'GCash', 'search' => request('search'), 'sort' => request('sort'), 'filter_plan' => request('filter_plan')]) }}" 
                class="filter-menu-item {{ request('filter_method') == 'GCash' ? 'active' : '' }}">
                <i class="mdi mdi-cellphone"></i> GCash
                </a>
                <a href="{{ route('membership.payment.index', ['filter_method' => 'PayMaya', 'search' => request('search'), 'sort' => request('sort'), 'filter_plan' => request('filter_plan')]) }}" 
                class="filter-menu-item {{ request('filter_method') == 'PayMaya' ? 'active' : '' }}">
                <i class="mdi mdi-wallet"></i> PayMaya
                </a>
                <a href="{{ route('membership.payment.index', ['filter_method' => 'Bank Transfer', 'search' => request('search'), 'sort' => request('sort'), 'filter_plan' => request('filter_plan')]) }}" 
                class="filter-menu-item {{ request('filter_method') == 'Bank Transfer' ? 'active' : '' }}">
                <i class="mdi mdi-bank"></i> Bank Transfer
                </a>
                
                <div class="filter-menu-divider"></div>
                <a href="{{ route('membership.payment.index') }}" class="filter-menu-item" style="color: #dc3545;">
                <i class="mdi mdi-close-circle"></i> Clear Filters
                </a>
            </div>
            </div>
            
            <!-- Search Form -->
            <form action="{{ route('membership.payment.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="filter_plan" value="{{ request('filter_plan') }}">
            <input type="hidden" name="filter_method" value="{{ request('filter_method') }}">
            <input 
                type="text" 
                class="form-control" 
                name="search" 
                placeholder="Search transactions..." 
                value="{{ request('search') }}"
            >
            <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-magnify"></i>
            </button>
            </form>
        </div>
        </div>

        <div class="table-responsive">
        <table class="table table table-hover">
            <thead>
            <tr>
                <th style="width: 50px;">
                <input type="checkbox" class="custom-checkbox" id="selectAll">
                </th>
                <th>Receipt #</th>
                <th>Member Name</th>
                <th>Type</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
                <th>New Due Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @php
                $transactionCount = isset($transactions) ? $transactions->count() : 0;
                $maxRows = 10;
            @endphp
            
            @if(isset($transactions) && $transactions->count() > 0)
                @foreach($transactions as $transaction)
                <tr>
                <td>
                    <input type="checkbox" class="custom-checkbox transaction-checkbox" value="{{ $transaction->id }}">
                </td>
                <td><strong>#{{ $transaction->receipt_number }}</strong></td>
                <td>{{ $transaction->member_name }}</td>
                <td>
                    <span class="badge badge-{{ $transaction->payment_type == 'new' ? 'success' : ($transaction->payment_type == 'renewal' ? 'info' : 'warning') }}">
                    {{ ucfirst($transaction->payment_type) }}
                    </span>
                </td>
                <td>{{ $transaction->plan_type }}</td>
                <td><strong>₱{{ number_format($transaction->amount, 2) }}</strong></td>
                <td>{{ $transaction->payment_method }}</td>
                <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                <td>{{ $transaction->new_due_date ? date('M d, Y', strtotime($transaction->new_due_date)) : 'N/A' }}</td>
                <td>
                    <div class="action-dropdown">
                    <button type="button" class="action-btn" onclick="toggleDropdown(this)">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                        <button type="button" class="dropdown-item" onclick="viewReceipt({{ $transaction->id }})">
                        <i class="mdi mdi-eye"></i> View Receipt
                        </button>
                        <form action="{{ route('membership.payment.destroy', $transaction->id) }}" method="POST" class="delete-form" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item danger">
                            <i class="mdi mdi-delete"></i> Delete
                        </button>
                        </form>
                    </div>
                    </div>
                </td>
                </tr>
                @endforeach
            @endif
            
            @for($i = $transactionCount; $i < $maxRows; $i++)
            <tr>
                <td colspan="10" style="height: 53px; text-align: center; color: #999;">
                @if($i == 0)
                    No transactions found
                @endif
                </td>
            </tr>
            @endfor
            </tbody>
        </table>
        </div>

        <!-- Pagination - ALWAYS VISIBLE -->
        <div class="pagination-container">
        <button class="btn btn-danger" id="bulkDeleteBtn" disabled>
            <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
        </button>
        
        @if(isset($transactions) && $transactions->total() > 0)
        <nav>
            <ul class="pagination">
            @if ($transactions->onFirstPage())
                <li class="page-item disabled">
                <span class="page-link">‹</span>
                </li>
            @else
                <li class="page-item">
                <a class="page-link" href="{{ $transactions->appends(request()->query())->previousPageUrl() }}">‹</a>
                </li>
            @endif

            @foreach(range(1, min(5, $transactions->lastPage())) as $page)
                @if($page == $transactions->currentPage())
                <li class="page-item active">
                    <span class="page-link">{{ $page }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $transactions->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
                @endif
            @endforeach

            @if ($transactions->hasMorePages())
                <li class="page-item">
                <a class="page-link" href="{{ $transactions->appends(request()->query())->nextPageUrl() }}">›</a>
                </li>
            @else
                <li class="page-item disabled">
                <span class="page-link">›</span>
                </li>
            @endif
            </ul>
        </nav>

        <div class="pagination-info">
            Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
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

<!-- Receipt Modal -->
<div id="receiptModal" class="modal-overlay modal-overlay-centered" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Payment Receipt</h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body" id="receiptBody">
      <div style="text-align: center; padding: 2rem; color: #666;">
        <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
        <p>Loading receipt...</p>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">
        <i class="mdi mdi-close"></i> Close
      </button>
      <button type="button" class="btn btn-primary" onclick="printReceipt()">
        <i class="mdi mdi-printer"></i> Print Receipt
      </button>
    </div>
  </div>
</div>

<!-- Bulk Delete Form -->
<form id="bulkDeleteForm" action="{{ route('membership.payment.bulkDelete') }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
  <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Notification Container -->
<div id="notification" class="notification">
  <div id="notificationMessage"></div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  let selectedMemberStatus = '';
  let selectedMemberDueDate = '';

  // Payment Type Selection
  const paymentTypePills = document.querySelectorAll('.payment-type-pill');
  const paymentTypeInput = document.getElementById('paymentType');
  const memberSelectionSection = document.getElementById('memberSelectionSection');
  const newMemberSection = document.getElementById('newMemberSection');
  const memberSearch = document.getElementById('memberSearch');
  const memberId = document.getElementById('memberId');
  const extensionPill = document.getElementById('extensionPill');

  paymentTypePills.forEach(pill => {
    pill.addEventListener('click', function() {
      const type = this.dataset.type;
      
      // FIXED: Check if extension is clicked and member not selected
      if (type === 'extension' && !memberId.value) {
        // Don't show error, just don't activate
        return;
      }

      // FIXED: Check if renewal is clicked and member is active
      if (type === 'renewal' && selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
        showNotification('Member is active. Please use Extension instead.', 'warning');
        return;
      }

      paymentTypePills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      paymentTypeInput.value = type;

      if (type === 'new') {
        memberSelectionSection.style.display = 'none';
        newMemberSection.style.display = 'block';
        memberSearch.removeAttribute('required');
        memberId.removeAttribute('required');
        document.getElementById('newMemberName').setAttribute('required', 'required');
        document.getElementById('newMemberContact').setAttribute('required', 'required');
      } else {
        memberSelectionSection.style.display = 'block';
        newMemberSection.style.display = 'none';
        memberSearch.setAttribute('required', 'required');
        document.getElementById('newMemberName').removeAttribute('required');
        document.getElementById('newMemberContact').removeAttribute('required');
      }

      document.getElementById('currentDueDate').value = '';
      document.getElementById('newDueDate').value = '';
      calculateNewDueDate();
    });
  });

  // Plan Type Selection
  const planTypeCards = document.querySelectorAll('.plan-type-card');
  const planTypeInput = document.getElementById('planType');
  const amountInput = document.getElementById('amount');
  const additionalDaysInput = document.getElementById('additionalDays');

  planTypeCards.forEach(card => {
    card.addEventListener('click', function() {
      planTypeCards.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      
      const planType = this.dataset.plan;
      const price = this.dataset.price;
      const duration = this.dataset.duration;
      
      planTypeInput.value = planType;
      amountInput.value = parseFloat(price).toFixed(2);
      additionalDaysInput.value = duration;
      
      calculateNewDueDate();
    });
  });

  // Member Autocomplete
  let memberSearchTimeout;
  memberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('memberResults');

    clearTimeout(memberSearchTimeout);

    if (query.length < 2) {
      resultsContainer.style.display = 'none';
      return;
    }

    memberSearchTimeout = setTimeout(() => {
      fetch(`/api/members/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>';
            resultsContainer.style.display = 'block';
            return;
          }

          resultsContainer.innerHTML = data.map(member => `
            <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-plan="${member.plan_type}" data-status="${member.status}">
              <strong>${member.name}</strong>
              <div style="font-size: 0.875rem; color: #999;">
                Contact: ${member.contact || 'N/A'} | Plan: ${member.plan_type} | Status: ${member.status}
                ${member.due_date ? `| Due: ${new Date(member.due_date).toLocaleDateString()}` : '| No due date'}
              </div>
            </div>
          `).join('');
          resultsContainer.style.display = 'block';

          resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
              memberSearch.value = this.dataset.name;
              memberId.value = this.dataset.id;
              selectedMemberStatus = this.dataset.status;
              selectedMemberDueDate = this.dataset.dueDate;
              
              const dueDate = this.dataset.dueDate;
              document.getElementById('currentDueDate').value = dueDate ? 
                new Date(dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 
                'No due date';
              
              // FIXED: Enable extension pill when member selected
              extensionPill.style.opacity = '1';
              extensionPill.style.pointerEvents = 'auto';

              // FIXED: Check if renewal should be disabled
              const renewalPill = document.querySelector('[data-type="renewal"]');
              if (selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
                renewalPill.style.opacity = '0.5';
                renewalPill.style.pointerEvents = 'none';
                // Auto-select extension
                paymentTypePills.forEach(p => p.classList.remove('active'));
                extensionPill.classList.add('active');
                paymentTypeInput.value = 'extension';
              } else {
                renewalPill.style.opacity = '1';
                renewalPill.style.pointerEvents = 'auto';
              }
              
              resultsContainer.style.display = 'none';
              calculateNewDueDate();
            });
          });
        })
        .catch(error => {
          console.error('Error fetching members:', error);
          showNotification('Error searching for members', 'error');
        });
    }, 300);
  });

  // Close autocomplete when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#memberResults')) {
      document.getElementById('memberResults').style.display = 'none';
    }
  });

  // FIXED: Contact number validation (accepts both 09 and +63)
  document.getElementById('newMemberContact').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9+]/g, '');
    
    // Allow +63 format
    if (value.startsWith('+63')) {
      if (value.length > 13) {
        value = value.substring(0, 13);
      }
    }
    // Allow 09 format
    else if (value.startsWith('09')) {
      if (value.length > 11) {
        value = value.substring(0, 11);
      }
    }
    
    e.target.value = value;
  });

  // Calculate New Due Date
  function calculateNewDueDate() {
    const paymentType = paymentTypeInput.value;
    const duration = parseInt(additionalDaysInput.value) || 0;
    const currentDueDateText = document.getElementById('currentDueDate').value;

    if (duration === 0) {
      document.getElementById('newDueDate').value = '';
      return;
    }

    let startDate;
    const today = new Date();

    if (paymentType === 'new') {
      startDate = today;
    } else if (paymentType === 'renewal') {
      if (currentDueDateText && currentDueDateText !== 'No due date') {
        const currentDueDate = new Date(currentDueDateText);
        startDate = currentDueDate > today ? currentDueDate : today;
      } else {
        startDate = today;
      }
    } else if (paymentType === 'extension') {
      if (currentDueDateText && currentDueDateText !== 'No due date') {
        startDate = new Date(currentDueDateText);
      } else {
        return;
      }
    }

    const newDueDate = new Date(startDate);
    newDueDate.setDate(newDueDate.getDate() + duration);

    document.getElementById('newDueDate').value = newDueDate.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  }

  // Form Submission
  const paymentForm = document.getElementById('membershipPaymentForm');
  paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // FIXED: Validate contact number format
    if (paymentTypeInput.value === 'new') {
      const contact = document.getElementById('newMemberContact').value;
      if (contact && !contact.match(/^(09\d{9}|\+639\d{9})$/)) {
        showNotification('Invalid contact number. Use 09XXXXXXXXX or +639XXXXXXXXX format', 'error');
        return;
      }
    }

    const submitBtn = document.getElementById('submitPaymentBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';

    const formData = new FormData(this);

    fetch(this.action, {
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
        showNotification(data.message || 'Payment processed successfully!', 'success');
        // FIXED: Immediate reload without delay
        window.location.reload();
      } else {
        showNotification(data.message || 'An error occurred', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showNotification('Failed to process payment. Please try again.', 'error');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
  });

  // Clear Form
  document.getElementById('clearFormBtn').addEventListener('click', function() {
    paymentForm.reset();
    memberId.value = '';
    selectedMemberStatus = '';
    selectedMemberDueDate = '';
    document.getElementById('currentDueDate').value = '';
    document.getElementById('newDueDate').value = '';
    
    planTypeCards.forEach(c => c.classList.remove('active'));
    planTypeCards[0].classList.add('active');
    planTypeInput.value = 'Monthly';
    amountInput.value = '500.00';
    additionalDaysInput.value = '30';

    // Reset pills
    extensionPill.style.opacity = '0.5';
    extensionPill.style.pointerEvents = 'none';
    document.querySelector('[data-type="renewal"]').style.opacity = '1';
    document.querySelector('[data-type="renewal"]').style.pointerEvents = 'auto';
  });

  // Checkbox Selection
  const selectAllCheckbox = document.getElementById('selectAll');
  const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
  const selectedCountSpan = document.getElementById('selectedCount');

  selectAllCheckbox.addEventListener('change', function() {
    transactionCheckboxes.forEach(cb => {
      cb.checked = this.checked;
    });
    updateBulkDeleteButton();
  });

  transactionCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      updateBulkDeleteButton();
      const allChecked = Array.from(transactionCheckboxes).every(checkbox => checkbox.checked);
      selectAllCheckbox.checked = allChecked;
    });
  });

  function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
    const count = checkedBoxes.length;
    selectedCountSpan.textContent = count;
    bulkDeleteBtn.disabled = count === 0;
  }

  // Bulk Delete
  bulkDeleteBtn.addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.transaction-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);

    if (ids.length === 0) {
      showNotification('Please select at least one transaction to delete', 'warning');
      return;
    }

    if (confirm(`Are you sure you want to delete ${ids.length} transaction(s)? This action cannot be undone.`)) {
      document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
      document.getElementById('bulkDeleteForm').submit();
    }
  });

  // Delete Form Confirmation
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
        this.submit();
      }
    });
  });

  // Filter Dropdown
  const filterBtn = document.getElementById('filterBtn');
  const filterMenu = document.getElementById('filterMenu');

  filterBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    filterMenu.classList.toggle('show');
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.filter-dropdown')) {
      filterMenu.classList.remove('show');
    }
  });
});

// View Receipt
function viewReceipt(transactionId) {
  const modal = document.getElementById('receiptModal');
  const receiptBody = document.getElementById('receiptBody');

  modal.classList.add('show');
  receiptBody.innerHTML = `
    <div style="text-align: center; padding: 2rem; color: #666;">
      <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
      <p>Loading receipt...</p>
    </div>
  `;

  fetch(`/membership-payment/${transactionId}/receipt`)
    .then(response => response.json())
    .then(data => {
      receiptBody.innerHTML = generateReceiptHTML(data);
    })
    .catch(error => {
      console.error('Error loading receipt:', error);
      receiptBody.innerHTML = `
        <div style="text-align: center; padding: 2rem; color: #dc3545;">
          <i class="mdi mdi-alert-circle" style="font-size: 3rem;"></i>
          <p>Failed to load receipt. Please try again.</p>
        </div>
      `;
    });
}

function generateReceiptHTML(data) {
  return `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>MEMBERSHIP PAYMENT RECEIPT</h2>
        <p><strong>Abstrack Fitness Gym</strong></p>
        <p>Toril, Davao Del Sur</p>
        <p>Phone: (123) 456-7890</p>
      </div>

      <div class="receipt-info-grid">
        <div class="receipt-info-item">
          <strong>Receipt Number</strong>
          <span>#${data.receipt_number}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Date & Time</strong>
          <span>${data.formatted_date}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Member Name</strong>
          <span>${data.member_name}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Contact</strong>
          <span>${data.member_contact}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Payment Type</strong>
          <span>${data.payment_type.toUpperCase()}</span>
        </div>
        <div class="receipt-info-item">
          <strong>Payment Method</strong>
          <span>${data.payment_method}</span>
        </div>
      </div>

      <table class="receipt-table">
        <thead>
          <tr>
            <th>Description</th>
            <th style="text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <strong>${data.plan_type} Plan</strong><br>
              <small style="color: #666;">Duration: ${data.duration} days</small>
            </td>
            <td style="text-align: right;">₱${parseFloat(data.amount).toFixed(2)}</td>
          </tr>
        </tbody>
      </table>

      <div class="receipt-total">
        <div class="receipt-total-row grand-total">
          <strong>Total Paid:</strong>
          <span>₱${parseFloat(data.amount).toFixed(2)}</span>
        </div>
      </div>

      <div style="margin-top: 2rem; padding-top: 1rem; border-top: 2px dashed #ccc;">
        <div class="receipt-info-grid">
          <div class="receipt-info-item">
            <strong>Previous Due Date</strong>
            <span>${data.previous_due_date || 'N/A'}</span>
          </div>
          <div class="receipt-info-item">
            <strong>New Due Date</strong>
            <span style="color: #28a745; font-weight: 700;">${data.new_due_date}</span>
          </div>
        </div>
      </div>

      ${data.notes ? `
        <div style="margin-top: 1.5rem; padding: 1rem; background: #f5f5f5; border-radius: 4px;">
          <strong style="display: block; margin-bottom: 0.5rem; color: #666;">Notes:</strong>
          <p style="margin: 0; color: #333;">${data.notes}</p>
        </div>
      ` : ''}

      <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px dashed #999; color: #666;">
        <p><strong>Thank you for your membership!</strong></p>
        <p style="font-size: 0.875rem;">Please keep this receipt for your records.</p>
      </div>
    </div>
  `;
}

function closeModal() {
  document.getElementById('receiptModal').classList.remove('show');
}

// FIXED: Print receipt properly
function printReceipt() {
  window.print();
}

// Toggle Dropdown - FIXED Z-INDEX
function toggleDropdown(button) {
  const dropdown = button.nextElementSibling;
  const allDropdowns = document.querySelectorAll('.dropdown-menu');
  
  allDropdowns.forEach(d => {
    if (d !== dropdown) {
      d.classList.remove('show');
    }
  });
  
  dropdown.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('.action-dropdown')) {
    document.querySelectorAll('.dropdown-menu').forEach(d => {
      d.classList.remove('show');
    });
  }
});

// Show Notification
function showNotification(message, type = 'success') {
  const notification = document.getElementById('notification');
  const messageElement = document.getElementById('notificationMessage');
  
  messageElement.textContent = message;
  notification.className = `notification ${type} show`;
  
  setTimeout(() => {
    notification.classList.remove('show');
  }, 5000);
}

// Display Laravel validation errors or success messages
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