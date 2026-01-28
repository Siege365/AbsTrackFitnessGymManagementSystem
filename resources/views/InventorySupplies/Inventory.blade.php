@extends('layouts.admin')

@section('title', 'Inventory Supplies')

@push('styles')
<style>
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
    background-color: #2A3038;
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
    background-color: #2A3038 !important;
    color: #495057 !important;
  }

  .table thead th,
  .table tbody td {
    color: #ffffff !important;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }

  /* Dropdown Styles (Updated to match membership) */
  .btn-action {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #ffffff;
    padding: 0.25rem 0.5rem;
    transition: all 0.2s;
  }

  .btn-action:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
  }

  .dropdown-menu {
    min-width: 180px;
    background: #2A3038;
    border: 1px solid #555;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    padding: 8px 0;
  }

  .dropdown-item {
    padding: 12px 20px;
    font-size: 14px;
    color: #ffffff;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
  }

  .dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
  }

  .dropdown-item i {
    margin-right: 0.5rem;
    font-size: 16px;
    width: 20px;
    text-align: center;
  }

  .dropdown-item.text-danger {
    color: #ff6b6b !important;
  }

  .dropdown-item.text-danger:hover {
    background-color: rgba(255, 107, 107, 0.1);
  }

  /* Bulk action button */
  #bulkActionBtn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Modal styles */
  .modal-content {
    background-color: #2A3038;
    color: #ffffff;
  }

  .modal-header {
    border-bottom: 1px solid #444;
  }

  .modal-footer {
    border-top: 1px solid #444;
  }

  .modal-header .close {
    color: #ffffff;
    opacity: 0.8;
  }

  .modal-header .close:hover {
    opacity: 1;
  }

  .form-control {
    background-color: #191C24;
    border-color: #444;
    color: #ffffff;
  }

  .form-control:focus {
    background-color: #191C24;
    border-color: #666;
    color: #ffffff;
  }

  .form-control:disabled {
    background-color: #1a1d24;
    color: #888;
  }

  .form-label {
    color: #cccccc;
    font-weight: 500;
  }

  #editModeToggle {
    cursor: pointer;
  }

  /* Filter dropdown styles */
  .dropdown-header {
    color: #999;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .filter-option {
    font-size: 0.875rem;
    transition: all 0.2s;
  }

  .filter-option i {
    width: 20px;
  }

  .filter-option.active {
    background-color: #191C24;
    color: #ffffff;
  }

  /* Alert styles */
  .alert {
    border-radius: 4px;
    margin-bottom: 1.5rem;
  }

  .invalid-feedback {
    display: block !important;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }

  .form-control.is-invalid {
    border-color: #dc3545;
  }

  .modal-body .alert {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
  }
</style>
@endpush

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
      <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-9">
                <div class="d-flex align-items-center align-self-start">
                  <h3 class="mb-0">{{ $totalProducts ?? 0 }}</h3>
                </div>
              </div>
            </div>
            <h6 class="text-muted font-weight-normal">Total Products</h6>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-9">
                <div class="d-flex align-items-center align-self-start">
                  <h3 class="mb-0">{{ $lowStockItems ?? 0 }}</h3>
                </div>
              </div>
            </div>
            <h6 class="text-muted font-weight-normal">Low Stock Items</h6>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-9">
                <div class="d-flex align-items-center align-self-start">
                  <h3 class="mb-0">{{ $outOfStockItems ?? 0 }}</h3>
                </div>
              </div>
            </div>
            <h6 class="text-muted font-weight-normal">Out Of Stock Items</h6>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-9">
                <div class="d-flex align-items-center align-self-start">
                  <h3 class="mb-0">₱{{ number_format($stockValue ?? 0, 2) }}</h3>
                  <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
                </div>
              </div>
              <div class="col-3">
                <div class="icon icon-box-success ">
                  <span class="mdi mdi-arrow-top-right icon-item"></span>
                </div>
              </div>
            </div>
            <h6 class="text-muted font-weight-normal">Stock Value</h6>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3" style="white-space: nowrap;">
              <h4 class="card-title mb-0">Inventory</h4>
              <div class="d-flex align-items-center">
                <!-- Filter Dropdown -->
                <div class="dropdown mr-2">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-filter-variant"></i> Filter
                  </button>
                  <div class="dropdown-menu" aria-labelledby="filterDropdown">
                    <h6 class="dropdown-header">Sort By</h6>
                    <a class="dropdown-item filter-option {{ request('filter') == 'name_asc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'name_asc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-alphabetical-ascending"></i> Name (A-Z)
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'name_desc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'name_desc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-alphabetical-descending"></i> Name (Z-A)
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item filter-option {{ request('filter') == 'date_newest' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'date_newest', 'search' => request('search')]) }}">
                      <i class="mdi mdi-calendar-clock"></i> Date Added (Newest)
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'date_oldest' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'date_oldest', 'search' => request('search')]) }}">
                      <i class="mdi mdi-calendar"></i> Date Added (Oldest)
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item filter-option {{ request('filter') == 'stock_asc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'stock_asc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-numeric-ascending"></i> Stock (Low to High)
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'stock_desc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'stock_desc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-numeric-descending"></i> Stock (High to Low)
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item filter-option text-danger" 
                      href="{{ route('inventory.index') }}">
                      <i class="mdi mdi-close-circle"></i> Clear Filter
                    </a>
                  </div>
                </div>
                
                <!-- Search Bar -->
                <form action="{{ route('inventory.index') }}" method="GET" id="searchForm" class="d-inline">
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                    <input type="text" 
                          id="searchInput" 
                          name="search" 
                          class="form-control form-control-sm mr-2" 
                          placeholder="Search products..." 
                          value="{{ request('search') }}">
                </form>

                <!-- Add Item Button -->
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addItemModal">
                  <i class="mdi mdi-plus"></i> Add Item
                </button>
              </div>
            </div>
            <div class="table-responsive" style="min-height: 600px;">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th style="min-width: 50px;">
                      <div class="form-check form-check-muted m-0">
                        <label class="form-check-label">
                          <input type="checkbox" class="form-check-input" id="checkAll">
                        </label>
                      </div>
                    </th>
                    <th> Product# </th>
                    <th> Product Name </th>
                    <th> Category </th>
                    <th> Unit Price </th>
                    <th> Stock Qty</th>
                    <th> Status </th>
                    <th> Last Restocked </th>
                    <th> Actions </th>
                  </tr>
                </thead>
                <tbody>
                @forelse($inventoryItems as $item)
                    <tr>
                    <td>
                        <div class="form-check form-check-muted m-0">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input item-checkbox" value="{{ $item->id }}">
                        </label>
                        </div>
                    </td>
                    <td>{{ $item->product_number }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->category }}</td>
                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->stock_qty }}</td>
                    <td>
                        @if($item->stock_qty == 0)
                        <span class="badge badge-danger">Out of Stock</span>
                        @elseif($item->stock_qty < $item->low_stock_threshold)
                        <span class="badge badge-warning">Low Stock</span>
                        @else
                        <span class="badge badge-success">In Stock</span>
                        @endif
                    </td>
                    <td>{{ $item->last_restocked ? $item->last_restocked->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <div class="dropdown">
                          <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown">
                              <i class="mdi mdi-dots-vertical"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-right">
                              <button type="button" 
                                      class="dropdown-item view-item"
                                      data-id="{{ $item->id }}"
                                      data-product-number="{{ $item->product_number }}"
                                      data-product-name="{{ $item->product_name }}"
                                      data-category="{{ $item->category }}"
                                      data-unit-price="{{ $item->unit_price }}"
                                      data-stock-qty="{{ $item->stock_qty }}"
                                      data-low-stock-threshold="{{ $item->low_stock_threshold }}"
                                      data-last-restocked="{{ $item->last_restocked ? $item->last_restocked->format('Y-m-d') : '' }}"
                                      data-toggle="modal" 
                                      data-target="#viewEditModal">
                                  <i class="mdi mdi-eye mr-2"></i> View
                              </button>
                              <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="dropdown-item text-danger">
                                      <i class="mdi mdi-delete mr-2"></i> Delete
                                  </button>
                              </form>
                          </div>
                      </div>
                    </td>
                    </tr>
                @empty
                    <tr>
                    <td colspan="9" class="text-center text-muted">No inventory items found</td>
                    </tr>
                @endforelse
                </tbody>
              </table>
            </div>
            
            <!-- Pagination and Bulk Delete -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3">
                <!-- Bulk Delete Button (Left) -->
                <button id="bulkActionBtn" class="btn btn-sm btn-danger" disabled>
                    <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
                
                <!-- Pagination (Right) -->
                @if(isset($inventoryItems))
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- Previous Page Link --}}
                            @if ($inventoryItems->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" style="border-radius: 4px 0 0 4px;">
                                        <i class="mdi mdi-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $inventoryItems->previousPageUrl() }}" style="border-radius: 4px 0 0 4px;">
                                        <i class="mdi mdi-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements - Show only 3 pages at a time --}}
                            @php
                                $currentPage = $inventoryItems->currentPage();
                                $lastPage = $inventoryItems->lastPage();
                                
                                // Calculate start and end page for 3-page window
                                if ($currentPage == 1) {
                                    $start = 1;
                                    $end = min(3, $lastPage);
                                } elseif ($currentPage == $lastPage) {
                                    $start = max(1, $lastPage - 2);
                                    $end = $lastPage;
                                } else {
                                    $start = max(1, $currentPage - 1);
                                    $end = min($lastPage, $currentPage + 1);
                                }
                            @endphp

                            {{-- Show first page if not in range --}}
                            @if($start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $inventoryItems->url(1) }}">1</a>
                                </li>
                                @if($start > 2)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                            @endif

                            {{-- Show pages in range --}}
                            @for($page = $start; $page <= $end; $page++)
                                @if ($page == $currentPage)
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $inventoryItems->url($page) }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endfor

                            {{-- Show last page if not in range --}}
                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $inventoryItems->url($lastPage) }}">{{ $lastPage }}</a>
                                </li>
                            @endif

                            {{-- Next Page Link --}}
                            @if ($inventoryItems->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $inventoryItems->nextPageUrl() }}" style="border-radius: 0 4px 4px 0;">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link" style="border-radius: 0 4px 4px 0;">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addItemModalLabel">Add New Product</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="addItemForm" action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="modal-body">
              <!-- Show validation errors inside modal -->
              @if($errors->any() && old('product_number'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              @endif

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Product Number <span class="text-danger">*</span></label>
                    <input type="text" 
                          class="form-control @error('product_number') is-invalid @enderror" 
                          name="product_number" 
                          placeholder="e.g., PRD-001" 
                          value="{{ old('product_number') }}" 
                          required>
                    @error('product_number')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" 
                          class="form-control @error('product_name') is-invalid @enderror" 
                          name="product_name" 
                          placeholder="e.g., Protein Powder" 
                          value="{{ old('product_name') }}" 
                          required>
                    @error('product_name')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <input type="text" 
                          class="form-control @error('category') is-invalid @enderror" 
                          name="category" 
                          placeholder="e.g., Supplements" 
                          value="{{ old('category') }}" 
                          required>
                    @error('category')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                    <input type="number" 
                    step="0.01" 
                    min="0" 
                    class="form-control @error('unit_price') is-invalid @enderror" 
                    name="unit_price" 
                    placeholder="₱0.00"
                    value="{{ old('unit_price') }}" 
                    required>
                    @error('unit_price')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                    <input type="number" 
                          min="0" 
                          class="form-control @error('stock_qty') is-invalid @enderror" 
                          name="stock_qty" 
                          placeholder="0" 
                          value="{{ old('stock_qty') }}" 
                          required>
                    @error('stock_qty')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Low Stock Threshold <span class="text-danger">*</span></label>
                    <input type="number" 
                          min="0" 
                          class="form-control @error('low_stock_threshold') is-invalid @enderror" 
                          name="low_stock_threshold" 
                          placeholder="10" 
                          value="{{ old('low_stock_threshold') }}" 
                          required>
                    <small class="text-muted">Alert threshold for low stock</small>
                    @error('low_stock_threshold')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Last Restocked</label>
                    <input type="date" 
                          class="form-control @error('last_restocked') is-invalid @enderror" 
                          name="last_restocked" 
                          value="{{ old('last_restocked') }}">
                    @error('last_restocked')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-check"></i> Add Product
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View/Edit Modal -->
    <div class="modal fade" id="viewEditModal" tabindex="-1" role="dialog" aria-labelledby="viewEditModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewEditModalLabel">
              <span id="modalTitle">Product Details</span>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary" id="editModeToggle">
                  <i class="mdi mdi-pencil"></i> <span id="editButtonText">Edit</span>
                </button>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Product Number</label>
                    <input type="text" class="form-control" id="productNumber" name="product_number" readonly required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="product_name" readonly required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category" readonly required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Unit Price (₱)</label>
                    <input type="number" step="0.01" class="form-control" id="unitPrice" name="unit_price" readonly required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stockQty" name="stock_qty" readonly required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" class="form-control" id="lowStockThreshold" name="low_stock_threshold" readonly required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Last Restocked</label>
                    <input type="date" class="form-control" id="lastRestocked" name="last_restocked" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="pt-2">
                      <span class="badge" id="statusBadge"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="saveButton" style="display: none;">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>       
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    const selectedCount = document.getElementById('selectedCount');
    const editModeToggle = document.getElementById('editModeToggle');
    const editButtonText = document.getElementById('editButtonText');
    const saveButton = document.getElementById('saveButton');
    const editForm = document.getElementById('editForm');
    const modalTitle = document.getElementById('modalTitle');
    let isEditMode = false;
    let currentItemId = null;

    // Real-time search with debounce (NEW - REPLACES OLD SEARCH)
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 750);
        });
    }

    // Check/Uncheck all functionality
    checkAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });

    // Individual checkbox change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
            
            checkAll.checked = allChecked;
            checkAll.indeterminate = someChecked && !allChecked;
            
            updateBulkActionButton();
        });
    });

    // Update bulk action button visibility and count
    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        selectedCount.textContent = checkedCount;
        
        if (checkedCount > 0) {
            bulkActionBtn.disabled = false;
        } else {
            bulkActionBtn.disabled = true;
        }
    }

    // Bulk delete action
    bulkActionBtn.addEventListener('click', function() {
        if (this.disabled) {
            return;
        }
        
        const checkedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
            .map(cb => cb.value);
        
        if (checkedItems.length === 0) {
            return;
        }

        if (confirm(`Are you sure you want to delete ${checkedItems.length} item(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("inventory.bulk-delete") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            checkedItems.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });

    // View item modal
    document.querySelectorAll('.view-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Reset to view mode
            isEditMode = false;
            setEditMode(false);
            
            // Get data attributes
            currentItemId = this.dataset.id;
            const productNumber = this.dataset.productNumber;
            const productName = this.dataset.productName;
            const category = this.dataset.category;
            const unitPrice = this.dataset.unitPrice;
            const stockQty = this.dataset.stockQty;
            const lowStockThreshold = this.dataset.lowStockThreshold;
            const lastRestocked = this.dataset.lastRestocked;
            
            // Populate form
            document.getElementById('productNumber').value = productNumber;
            document.getElementById('productName').value = productName;
            document.getElementById('category').value = category;
            document.getElementById('unitPrice').value = unitPrice;
            document.getElementById('stockQty').value = stockQty;
            document.getElementById('lowStockThreshold').value = lowStockThreshold;
            document.getElementById('lastRestocked').value = lastRestocked;
            
            // Update status badge
            const statusBadge = document.getElementById('statusBadge');
            if (stockQty == 0) {
                statusBadge.className = 'badge badge-danger';
                statusBadge.textContent = 'Out of Stock';
            } else if (stockQty < lowStockThreshold) {
                statusBadge.className = 'badge badge-warning';
                statusBadge.textContent = 'Low Stock';
            } else {
                statusBadge.className = 'badge badge-success';
                statusBadge.textContent = 'In Stock';
            }
            
            // Set form action
            editForm.action = `/inventory/${currentItemId}`;
        });
    });

    // Toggle edit mode
    editModeToggle.addEventListener('click', function() {
        isEditMode = !isEditMode;
        setEditMode(isEditMode);
    });

    // Set edit mode
    function setEditMode(enabled) {
        const inputs = editForm.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]');
        
        inputs.forEach(input => {
            input.readOnly = !enabled;
        });
        
        if (enabled) {
            editButtonText.textContent = 'Cancel';
            editModeToggle.classList.remove('btn-outline-primary');
            editModeToggle.classList.add('btn-outline-secondary');
            saveButton.style.display = 'inline-block';
            modalTitle.textContent = 'Edit Product';
        } else {
            editButtonText.textContent = 'Edit';
            editModeToggle.classList.remove('btn-outline-secondary');
            editModeToggle.classList.add('btn-outline-primary');
            saveButton.style.display = 'none';
            modalTitle.textContent = 'Product Details';
        }
    }

    // Delete form confirmation
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this item?')) {
                this.submit();
            }
        });
    });

    // Reset modal on close
    $('#viewEditModal').on('hidden.bs.modal', function () {
        isEditMode = false;
        setEditMode(false);
        editForm.reset();
    });

    // Reset add item form when modal closes
    $('#addItemModal').on('hidden.bs.modal', function () {
        document.getElementById('addItemForm').reset();
        // Remove error styling and messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.alert').remove();
    });

    // Show add item modal if there are validation errors
    @if($errors->any() && old('product_number'))
        $(document).ready(function() {
            $('#addItemModal').modal('show');
        });
    @endif
});
</script>
@endpush