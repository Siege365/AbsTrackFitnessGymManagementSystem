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

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
          <div class="card stats-card" data-filter="all">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="mb-0">{{ $totalProducts ?? 0 }}</h2>
                  <p class="text-muted mb-0">Total Products</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
          <div class="card stats-card" data-filter="active">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="mb-0">{{ $lowStockItems ?? 0 }}</h2>
                  <p class="text-muted mb-0">Low Stock Items</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
          <div class="card stats-card" data-filter="expiring">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="mb-0">{{ $outOfStockItems ?? 0 }}</h2>
                  <p class="text-muted mb-0">Out Of Stock Items</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
          <div class="card stats-card" data-filter="new">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="mb-0">₱{{ number_format($stockValue ?? 0, 2) }}</h2>
                  <p class="text-muted mb-0">Stock Value</p>
                </div>
              </div>
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
                <!-- Search Bar -->
                <form action="{{ route('inventory.index') }}" method="GET" id="searchForm" class="d-flex align-items-center">
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                    <div class="search-wrapper mr-2">
                      <input type="text" 
                            id="searchInput" 
                            name="search" 
                            class="form-control form-control-sm" 
                            placeholder="Search products..." 
                            value="{{ request('search') }}"
                            style="width: 450px;">
                      @if(request('search'))
                      <button type="button" class="search-clear-btn" onclick="clearSearch('searchInput', 'searchForm')">&times;</button>
                      @endif
                    </div>
                </form> 
                <!-- Filter Dropdown -->
                <div class="dropdown d-inline-block mr-2">
                  <button type="button" class="btn btn-sm filter-button dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-filter-variant"></i> Filter
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    <h6 class="dropdown-header">Sort By</h6>
                    <a class="dropdown-item filter-option {{ request('filter') == 'name_asc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'name_asc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-alphabetical-ascending"></i> Name (A-Z)
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'name_desc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'name_desc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-alphabetical-descending"></i> Name (Z-A)
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'date_newest' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'date_newest', 'search' => request('search')]) }}">
                      <i class="mdi mdi-calendar-clock"></i> Date Added (Newest)
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'date_oldest' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'date_oldest', 'search' => request('search')]) }}">
                      <i class="mdi mdi-calendar"></i> Date Added (Oldest)
                    </a>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Stock Status</h6>
                    <a class="dropdown-item filter-option {{ request('filter') == 'in_stock' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'in_stock', 'search' => request('search')]) }}">
                      <i class="mdi mdi-check-circle text-success"></i> In Stock
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'low_stock' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'low_stock', 'search' => request('search')]) }}">
                      <i class="mdi mdi-alert text-warning"></i> Low Stock
                    </a>
                    <a class="dropdown-item filter-option {{ request('filter') == 'out_of_stock' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'out_of_stock', 'search' => request('search')]) }}">
                      <i class="mdi mdi-close-circle text-danger"></i> Out of Stock
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
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
                    <th> Stock History </th>
                    <th> Actions </th>
                  </tr>
                </thead>
                <tbody>
                @forelse($inventoryItems as $item)
                    <tr>
                    <td>
                        <div class="form-check form-check-muted m-0">
                        <label class="form-check-label">
                            <input type="checkbox" 
                                   class="form-check-input item-checkbox" 
                                   value="{{ $item->id }}"
                                   data-product-number="{{ $item->product_number }}"
                                   data-product-name="{{ $item->product_name }}"
                                   data-category="{{ $item->category }}">
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
                        <a href="{{ route('inventory.transaction-history', $item->id) }}" class="btn btn-outline-info">
                            <i class="mdi mdi-history" ></i> View
                        </a>
                    </td>
                    <td>
                        <div class="dropdown">
                          <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
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
                              <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteSingle({{ $item->id }})">
                                  <i class="mdi mdi-delete mr-2"></i> Delete
                              </button>
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
            <div class="table-footer">
              <button type="button" id="bulkActionBtn" class="btn btn-sm btn-delete-selected" disabled onclick="bulkDeleteInventory()">
                <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
              </button>
              @if(isset($inventoryItems))
                {{ $inventoryItems->links('vendor.pagination.custom') }}
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="card-title mb-0">Recent Activity</h4>
              <div class="dropdown">
                <button class="btn btn-sm filter-button dropdown-toggle" type="button" id="activityFilterDropdown" data-toggle="dropdown" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                  <i class="mdi mdi-filter-variant"></i> Filter
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <h6 class="dropdown-header">Sort By</h6>
                  <a class="dropdown-item {{ ($activityFilter ?? 'newest') == 'newest' ? 'active' : '' }}" 
                    href="{{ route('inventory.index', array_merge(request()->except('activity_filter'), ['activity_filter' => 'newest'])) }}">
                    <i class="mdi mdi-sort-calendar-descending"></i> Newest First
                  </a>
                  <a class="dropdown-item {{ ($activityFilter ?? '') == 'oldest' ? 'active' : '' }}" 
                    href="{{ route('inventory.index', array_merge(request()->except('activity_filter'), ['activity_filter' => 'oldest'])) }}">
                    <i class="mdi mdi-sort-calendar-ascending"></i> Oldest First
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item {{ ($activityFilter ?? '') == 'stock_in' ? 'active' : '' }}" 
                    href="{{ route('inventory.index', array_merge(request()->except('activity_filter'), ['activity_filter' => 'stock_in'])) }}">
                    <i class="mdi mdi-plus-circle text-success"></i> Stock In
                  </a>
                  <a class="dropdown-item {{ ($activityFilter ?? '') == 'stock_out' ? 'active' : '' }}" 
                    href="{{ route('inventory.index', array_merge(request()->except('activity_filter'), ['activity_filter' => 'stock_out'])) }}">
                    <i class="mdi mdi-minus-circle text-warning"></i> Stock Out
                  </a>
                </div>
              </div>
            </div>
            <div class="table-responsive activity-table-container" style="max-height: 480px;">
              <table class="table table-hover">
                <thead style="position: sticky; top: 0; background: #191C24; z-index: 1;">
                  <tr>
                    <th>Date & Time</th>
                    <th>Product#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Unit Price</th>
                    <th>Status</th>
                    <th>Quantity</th>
                  </tr>
                </thead>
                <tbody>
                @forelse($recentActivity ?? [] as $activity)
                    <tr>
                      <td>{{ \Carbon\Carbon::parse($activity->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                      <td>{{ $activity->inventorySupply->product_number ?? 'N/A' }}</td>
                      <td>{{ $activity->inventorySupply->product_name ?? 'N/A' }}</td>
                      <td>{{ $activity->inventorySupply->category ?? 'N/A' }}</td>
                      <td>₱{{ number_format($activity->inventorySupply->unit_price ?? 0, 2) }}</td>
                      <td>
                        @if($activity->transaction_type === 'stock_in')
                          <span class="badge badge-success"><i class="mdi mdi-plus-circle"></i> Stock In</span>
                        @else
                          <span class="badge badge-warning"><i class="mdi mdi-minus-circle"></i> Stock Out</span>
                        @endif
                      </td>
                      <td>
                        @if($activity->transaction_type === 'stock_in')
                          <span class="text-success font-weight-bold">+{{ $activity->quantity }}</span>
                        @else
                          <span class="text-warning font-weight-bold">-{{ $activity->quantity }}</span>
                        @endif
                      </td>
                    </tr>
                @empty
                    <tr>
                      <td colspan="7" class="text-center text-muted">No recent activity found</td>
                    </tr>
                @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="addProductModalLabel">
              <i class="mdi mdi-plus-box"></i> Add New Product
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="addProductForm" action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="modal-body">
              <!-- Product Number (Auto-generated, Read-only) -->
              <div class="form-group">
                <label class="form-label">Product Number</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="mdi mdi-barcode"></i></span>
                  </div>
                  <input type="text" 
                        class="form-control bg-light" 
                        name="product_number" 
                        id="autoProductNumber"
                        value="{{ old('product_number') }}" 
                        readonly
                        style="cursor: not-allowed;">
                </div>
                <small class="text-muted">Auto-generated product number</small>
              </div>

              <!-- Product Name -->
              <div class="form-group">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
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

              <div class="row">
                <div class="col-md-6">
                  <!-- Category Dropdown -->
                  <div class="form-group">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-control @error('category') is-invalid @enderror" 
                            name="category" 
                            required>
                      <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>
                      <option value="Food" {{ old('category') == 'Food' ? 'selected' : '' }}>Food</option>
                      <option value="Drink" {{ old('category') == 'Drink' ? 'selected' : '' }}>Drink</option>
                      <option value="Supplement" {{ old('category') == 'Supplement' ? 'selected' : '' }}>Supplement</option>
                    </select>
                    @error('category')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-md-6">
                  <!-- Unit Price -->
                  <div class="form-group">
                    <label class="form-label">Unit Price <span class="text-danger">*</span></label>
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
              </div>

              <div class="row">
                <div class="col-md-6">
                  <!-- Initial Stock Quantity -->
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
                  <!-- Low Stock Threshold (Fixed at 10, Read-only) -->
                  <div class="form-group">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" 
                          class="form-control bg-light" 
                          value="10" 
                          readonly
                          style="cursor: not-allowed;">
                    <input type="hidden" name="low_stock_threshold" value="10">
                    <small class="text-muted">Fixed at 10 units</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="mdi mdi-close"></i> Cancel
              </button>
              <button type="submit" class="btn btn-primary" id="addProductSubmitBtn">
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

    <!-- Bulk Delete Form -->
    <form id="bulkDeleteInventoryForm" action="{{ route('inventory.bulk-delete') }}" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
    </form>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay">
      <div class="modal-content small">
        <div class="modal-header" style="background-color: #dc3545; color: #fff;">
          <h5 class="modal-title"><i class="mdi mdi-alert-circle-outline"></i> Confirm Delete</h5>
          <button type="button" class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body" style="font-size: 1.125rem;">
          <div class="refund-warning" style="background: #f8d7da; border-color: #dc3545;">
            <i class="mdi mdi-alert" style="color: #dc3545;"></i>
            <div style="color: #000;">
              <strong>Warning:</strong> This action cannot be undone. You are about to delete <strong id="deleteItemCount">0</strong>.
            </div>
          </div>
          <div class="confirmation-details" id="deleteDetails">
            <div class="confirmation-detail-row" style="border-bottom: none;">
              <span class="confirmation-detail-label" style="color: #000; font-size: 1.125rem;">Are you sure to delete selected product?</span>
            </div>
          </div>
          <div id="selectedProductsList" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; max-height: 200px; overflow-y: auto; font-size: 1.125rem;">
            <!-- Selected products will be listed here -->
          </div>
        </div>
        <div class="modal-footer" style="font-size: 1.125rem;">
          <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
            <i class="mdi mdi-close"></i> Cancel
          </button>
          <button type="button" class="btn btn-danger" onclick="executeDelete()">
            <i class="mdi mdi-delete"></i> Delete
          </button>
        </div>
      </div>
    </div>
@endsection

@push('scripts')
@vite(['resources/js/common/form-utils.js'])
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

    // Reset modals on close
    $('#addProductModal').on('hidden.bs.modal', function () {
        document.getElementById('addProductForm').reset();
        document.getElementById('autoProductNumber').value = '';
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.alert').remove();
    });

    // Fetch next product number when modal opens
    $('#addProductModal').on('show.bs.modal', function () {
        fetch('{{ route("inventory.next-product-number") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('autoProductNumber').value = data.product_number;
            })
            .catch(error => {
                console.error('Error fetching product number:', error);
                document.getElementById('autoProductNumber').value = 'PRD-0001';
            });
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

    // Show add product modal if there are validation errors
    @if($errors->any() && old('product_name'))
        $(document).ready(function() {
            $('#addProductModal').modal('show');
        });
    @endif

    // Close delete modal on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });

    // Close delete modal on overlay click
    document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
});

// Dropdown toggle
document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      if (menu !== this.nextElementSibling) {
        menu.classList.remove('show');
      }
    });
    
    const menu = this.nextElementSibling;
    if (menu?.classList.contains('dropdown-menu')) {
      menu.classList.toggle('show');
    }
  });
});

document.addEventListener('click', function(e) {
  if (!e.target.closest('.dropdown')) {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      menu.classList.remove('show');
    });
  }
});

// Delete confirmation modal logic
let pendingDeleteAction = null;

function clearSearch(inputId, formId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.value = '';
        document.getElementById(formId).submit();
    }
}

function showDeleteModal(itemDescription) {
    document.getElementById('deleteItemCount').textContent = itemDescription;
    document.getElementById('deleteConfirmModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.remove('show');
    pendingDeleteAction = null;
}

function executeDelete() {
    if (pendingDeleteAction) {
        pendingDeleteAction();
        pendingDeleteAction = null;
    }
    closeDeleteModal();
}

// Single item delete via dropdown
function confirmDeleteSingle(itemId) {
    // Find the row containing this item
    const checkbox = document.querySelector(`.item-checkbox[value="${itemId}"]`);
    let productInfo = '';
    
    if (checkbox) {
        const productNumber = checkbox.dataset.productNumber;
        const productName = checkbox.dataset.productName;
        const category = checkbox.dataset.category;
        productInfo = `
            <div style="padding: 8px; border-left: 3px solid #dc3545; margin-bottom: 8px; background: white;">
                <div style="color: #000; font-weight: 500;">${productName}</div>
                <div style="color: #666; font-size: 0.875rem;">Product #: ${productNumber} | Category: ${category}</div>
            </div>
        `;
    }
    
    document.getElementById('selectedProductsList').innerHTML = productInfo;
    
    pendingDeleteAction = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/inventory/' + itemId;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    };
    showDeleteModal('1 product');
}

// Bulk delete
function bulkDeleteInventory() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) return;

    // Build list of selected products
    let productsList = '';
    checked.forEach(cb => {
        const productNumber = cb.dataset.productNumber;
        const productName = cb.dataset.productName;
        const category = cb.dataset.category;
        productsList += `
            <div style="padding: 8px; border-left: 3px solid #dc3545; margin-bottom: 8px; background: white;">
                <div style="color: #000; font-weight: 500;">${productName}</div>
                <div style="color: #666; font-size: 0.875rem;">Product #: ${productNumber} | Category: ${category}</div>
            </div>
        `;
    });
    
    document.getElementById('selectedProductsList').innerHTML = productsList;

    pendingDeleteAction = function() {
        const form = document.getElementById('bulkDeleteInventoryForm');
        // Clear previous hidden inputs (keep csrf and method)
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });

        form.submit();
    };

    showDeleteModal(checked.length + ' product(s)');
}
</script>
@endpush