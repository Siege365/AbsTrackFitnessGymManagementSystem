@extends('layouts.admin')

@section('title', 'Inventory Management')

@push('styles')
@vite(['resources/css/inventory.css'])
@endpush

@section('content')
    <!-- Page Header -->
    <div class="card page-header-card">
        <div class="card-body">
            <div>
                <h2 class="page-header-title">Inventory Management</h2>
                <p class="page-header-subtitle">Track, manage, and restock gym products and supplies.</p>
            </div>
            <button class="btn btn-page-action" data-toggle="modal" data-target="#addProductModal">
                <i class="mdi mdi-plus"></i> Add New Product
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
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
                    <th> Transaction History </th>
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
                    <td>
                        <a href="{{ route('inventory.transaction-history', $item->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-history"></i> View History
                        </a>
                    </td>
                    <td>
                        <div class="dropdown">
                          <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown">
                              <i class="mdi mdi-dots-vertical"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-right">
                              <button type="button" 
                                      class="dropdown-item text-success stock-in-btn"
                                      data-id="{{ $item->id }}"
                                      data-product-number="{{ $item->product_number }}"
                                      data-product-name="{{ $item->product_name }}"
                                      data-category="{{ $item->category }}"
                                      data-stock-qty="{{ $item->stock_qty }}"
                                      data-status="{{ $item->stock_qty == 0 ? 'Out of Stock' : ($item->stock_qty < $item->low_stock_threshold ? 'Low Stock' : 'In Stock') }}"
                                      data-status-class="{{ $item->stock_qty == 0 ? 'badge-danger' : ($item->stock_qty < $item->low_stock_threshold ? 'badge-warning' : 'badge-success') }}"
                                      data-toggle="modal" 
                                      data-target="#stockInModal">
                                  <i class="mdi mdi-plus-circle mr-2"></i> Stock In
                              </button>
                              <button type="button" 
                                      class="dropdown-item text-warning stock-out-btn"
                                      data-id="{{ $item->id }}"
                                      data-product-number="{{ $item->product_number }}"
                                      data-product-name="{{ $item->product_name }}"
                                      data-category="{{ $item->category }}"
                                      data-stock-qty="{{ $item->stock_qty }}"
                                      data-status="{{ $item->stock_qty == 0 ? 'Out of Stock' : ($item->stock_qty < $item->low_stock_threshold ? 'Low Stock' : 'In Stock') }}"
                                      data-status-class="{{ $item->stock_qty == 0 ? 'badge-danger' : ($item->stock_qty < $item->low_stock_threshold ? 'badge-warning' : 'badge-success') }}"
                                      data-toggle="modal" 
                                      data-target="#stockOutModal">
                                  <i class="mdi mdi-minus-circle mr-2"></i> Stock Out
                              </button>
                              <div class="dropdown-divider"></div>
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
                    <label class="form-label">Initial Stock Quantity <span class="text-danger">*</span></label>
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

    <!-- Stock In Modal -->
    <div class="modal fade" id="stockInModal" tabindex="-1" role="dialog" aria-labelledby="stockInModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content stock-modal">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="stockInModalLabel">
              <i class="mdi mdi-plus-circle"></i> Stock In
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="stockInForm" method="POST">
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
                      min="1"
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
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">
                <i class="mdi mdi-check"></i> Confirm Stock In
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Stock Out Modal -->
    <div class="modal fade" id="stockOutModal" tabindex="-1" role="dialog" aria-labelledby="stockOutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content stock-modal">
          <div class="modal-header bg-warning text-white">
            <h5 class="modal-title" id="stockOutModalLabel">
              <i class="mdi mdi-minus-circle"></i> Stock Out
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="stockOutForm" method="POST">
            @csrf
            <input type="hidden" name="transaction_type" value="stock_out">
            <div class="modal-body">
              <!-- Product Information -->
              <div class="info-card">
                <div class="info-row">
                  <span class="info-label">Product Number:</span>
                  <span class="info-value" id="stockOutProductNumber"></span>
                </div>
                <div class="info-row">
                  <span class="info-label">Product Name:</span>
                  <span class="info-value" id="stockOutProductName"></span>
                </div>
                <div class="info-row">
                  <span class="info-label">Category:</span>
                  <span class="info-value" id="stockOutCategory"></span>
                </div>
                <div class="info-row">
                  <span class="info-label">Current Stock:</span>
                  <span class="info-value"><span class="badge badge-info" id="stockOutCurrentStock"></span></span>
                </div>
                <div class="info-row">
                  <span class="info-label">Status:</span>
                  <span class="info-value"><span class="badge" id="stockOutStatus"></span></span>
                </div>
              </div>

              <!-- Quantity Input -->
              <div class="form-group">
                <label>Quantity to Remove <span class="text-danger">*</span></label>
                <input type="number" 
                      class="form-control quantity-input" 
                      name="quantity" 
                      id="stockOutQuantity"
                      placeholder="0" 
                      min="1"
                      required>
                <small class="text-muted">Available: <span id="availableStock"></span></small>
              </div>

              <!-- Notes -->
              <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea class="form-control" 
                          name="notes" 
                          rows="2" 
                          placeholder="e.g., Sales order #, department"></textarea>
              </div>

              <!-- Preview -->
              <div class="preview-box" id="stockOutPreview" style="display: none;">
                <div class="preview-row">
                  <span class="preview-label">Current Stock</span>
                  <span class="preview-value" id="previewCurrentOut">0</span>
                </div>
                <div class="preview-row">
                  <span class="preview-label">Removing</span>
                  <span class="preview-value text-warning" id="previewRemoveQuantity">-0</span>
                </div>
                <div class="preview-row">
                  <span class="preview-label">New Stock</span>
                  <span class="preview-value text-warning" id="previewNewOut">0</span>
                </div>
              </div>

              <!-- Warning -->
              <div class="alert alert-danger" id="insufficientStockWarning" style="display: none;">
                <i class="mdi mdi-alert"></i> Insufficient stock! Please enter a valid quantity.
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-warning" id="confirmStockOut">
                <i class="mdi mdi-check"></i> Confirm Stock Out
              </button>
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

    // Real-time search with debounce
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

    // Update bulk action button
    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        selectedCount.textContent = checkedCount;
        bulkActionBtn.disabled = checkedCount === 0;
    }

    // Bulk delete action
    bulkActionBtn.addEventListener('click', function() {
        if (this.disabled) return;
        
        const checkedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
            .map(cb => cb.value);
        
        if (checkedItems.length === 0) return;

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

    // Stock In Modal
    document.querySelectorAll('.stock-in-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.id;
            const productNumber = this.dataset.productNumber;
            const productName = this.dataset.productName;
            const category = this.dataset.category;
            const stockQty = parseInt(this.dataset.stockQty);
            const status = this.dataset.status;
            const statusClass = this.dataset.statusClass;
            
            document.getElementById('stockInProductNumber').textContent = productNumber;
            document.getElementById('stockInProductName').textContent = productName;
            document.getElementById('stockInCategory').textContent = category;
            document.getElementById('stockInCurrentStock').textContent = stockQty;
            document.getElementById('stockInStatus').textContent = status;
            document.getElementById('stockInStatus').className = 'badge ' + statusClass;
            
            document.getElementById('stockInForm').action = `/inventory/${itemId}/stock-transaction`;
            document.getElementById('stockInQuantity').value = '';
            document.getElementById('stockInPreview').style.display = 'none';
        });
    });

    // Stock In Quantity Preview
    document.getElementById('stockInQuantity').addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;
        const currentStock = parseInt(document.getElementById('stockInCurrentStock').textContent);
        const newStock = currentStock + quantity;
        
        if (quantity > 0) {
            document.getElementById('previewCurrentIn').textContent = currentStock;
            document.getElementById('previewAddQuantity').textContent = '+' + quantity;
            document.getElementById('previewNewIn').textContent = newStock;
            document.getElementById('stockInPreview').style.display = 'block';
        } else {
            document.getElementById('stockInPreview').style.display = 'none';
        }
    });

    // Stock Out Modal
    document.querySelectorAll('.stock-out-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.id;
            const productNumber = this.dataset.productNumber;
            const productName = this.dataset.productName;
            const category = this.dataset.category;
            const stockQty = parseInt(this.dataset.stockQty);
            const status = this.dataset.status;
            const statusClass = this.dataset.statusClass;
            
            document.getElementById('stockOutProductNumber').textContent = productNumber;
            document.getElementById('stockOutProductName').textContent = productName;
            document.getElementById('stockOutCategory').textContent = category;
            document.getElementById('stockOutCurrentStock').textContent = stockQty;
            document.getElementById('availableStock').textContent = stockQty;
            document.getElementById('stockOutStatus').textContent = status;
            document.getElementById('stockOutStatus').className = 'badge ' + statusClass;
            
            document.getElementById('stockOutForm').action = `/inventory/${itemId}/stock-transaction`;
            document.getElementById('stockOutQuantity').max = stockQty;
            document.getElementById('stockOutQuantity').value = '';
            document.getElementById('stockOutPreview').style.display = 'none';
            document.getElementById('insufficientStockWarning').style.display = 'none';
        });
    });

    // Stock Out Quantity Preview
    document.getElementById('stockOutQuantity').addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;
        const currentStock = parseInt(document.getElementById('stockOutCurrentStock').textContent);
        const newStock = currentStock - quantity;
        
        if (quantity > 0) {
            if (quantity > currentStock) {
                document.getElementById('insufficientStockWarning').style.display = 'block';
                document.getElementById('stockOutPreview').style.display = 'none';
                document.getElementById('confirmStockOut').disabled = true;
            } else {
                document.getElementById('insufficientStockWarning').style.display = 'none';
                document.getElementById('previewCurrentOut').textContent = currentStock;
                document.getElementById('previewRemoveQuantity').textContent = '-' + quantity;
                document.getElementById('previewNewOut').textContent = newStock;
                document.getElementById('stockOutPreview').style.display = 'block';
                document.getElementById('confirmStockOut').disabled = false;
            }
        } else {
            document.getElementById('stockOutPreview').style.display = 'none';
            document.getElementById('insufficientStockWarning').style.display = 'none';
            document.getElementById('confirmStockOut').disabled = false;
        }
    });

    // Delete form confirmation
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this item?')) {
                this.submit();
            }
        });
    });

    // Reset modals on close
    $('#addItemModal').on('hidden.bs.modal', function () {
        document.getElementById('addItemForm').reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.alert').remove();
    });

    $('#stockInModal').on('hidden.bs.modal', function () {
        document.getElementById('stockInForm').reset();
        document.getElementById('stockInPreview').style.display = 'none';
    });

    $('#stockOutModal').on('hidden.bs.modal', function () {
        document.getElementById('stockOutForm').reset();
        document.getElementById('stockOutPreview').style.display = 'none';
        document.getElementById('insufficientStockWarning').style.display = 'none';
        document.getElementById('confirmStockOut').disabled = false;
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