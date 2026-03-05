<!-- Inventory Table Card -->
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
                        style="width: 100%; max-width: 450px;">
                  @if(request('search'))
                  <button type="button" class="search-clear-btn" onclick="clearSearch('searchInput', 'searchForm')">&times;</button>
                  @endif
                </div>
            </form> 
            <!-- Filter Dropdown (Accordion Style) -->
            <div class="dropdown d-inline-block mr-2">
              <button type="button" class="btn btn-sm filter-button dropdown-toggle" id="filterDropdown" data-toggle="dropdown" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              <div class="dropdown-menu dropdown-menu-right filter-accordion">
                <div class="filter-header">
                  <span class="filter-title">Filter By</span>
                  <a class="filter-clear-all" href="{{ route('inventory.index') }}">Clear All</a>
                </div>
                <!-- Sort By Section -->
                <div class="filter-section">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-sort"></i>
                      <span>Sort By</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    <a class="filter-option {{ request('filter') == 'name_asc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'name_asc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-alphabetical-ascending"></i> Name (A-Z)
                    </a>
                    <a class="filter-option {{ request('filter') == 'name_desc' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'name_desc', 'search' => request('search')]) }}">
                      <i class="mdi mdi-sort-alphabetical-descending"></i> Name (Z-A)
                    </a>
                    <a class="filter-option {{ request('filter') == 'date_newest' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'date_newest', 'search' => request('search')]) }}">
                      <i class="mdi mdi-calendar-clock"></i> Date (Newest)
                    </a>
                    <a class="filter-option {{ request('filter') == 'date_oldest' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'date_oldest', 'search' => request('search')]) }}">
                      <i class="mdi mdi-calendar"></i> Date (Oldest)
                    </a>
                  </div>
                </div>
                <!-- Stock Status Section -->
                <div class="filter-section">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-clipboard-check-outline"></i>
                      <span>Stock Status</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    <a class="filter-option filter-option-in-stock {{ request('filter') == 'in_stock' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'in_stock', 'search' => request('search')]) }}">
                      <i class="mdi mdi-check-circle"></i> In Stock
                    </a>
                    <a class="filter-option filter-option-low-stock {{ request('filter') == 'low_stock' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'low_stock', 'search' => request('search')]) }}">
                      <i class="mdi mdi-alert"></i> Low Stock
                    </a>
                    <a class="filter-option filter-option-out-of-stock {{ request('filter') == 'out_of_stock' ? 'active' : '' }}" 
                      href="{{ route('inventory.index', ['filter' => 'out_of_stock', 'search' => request('search')]) }}">
                      <i class="mdi mdi-close-circle"></i> Out of Stock
                    </a>
                  </div>
                </div>
                <!-- Category Section -->
                <div class="filter-section">
                  <div class="filter-section-header" onclick="toggleFilterSection(this, event)">
                    <div class="filter-section-title">
                      <i class="mdi mdi-tag-multiple"></i>
                      <span>Category</span>
                    </div>
                    <i class="mdi mdi-chevron-down filter-chevron"></i>
                  </div>
                  <div class="filter-section-content">
                    @if(isset($categories) && $categories->count() > 0)
                      @foreach($categories as $cat)
                        <a class="filter-option filter-option-category-{{ $cat->slug }} {{ request('filter') == $cat->name ? 'active' : '' }}" 
                          href="{{ route('inventory.index', ['filter' => $cat->name, 'search' => request('search')]) }}"
                          @if($cat->color) style="--cat-color: {{ $cat->color }};" @endif>
                          <i class="mdi {{ $cat->icon }}"></i> {{ $cat->name }}
                        </a>
                      @endforeach
                    @else
                      <span class="text-muted px-3 py-2 d-block" style="font-size: 0.85rem;">No categories yet</span>
                    @endif
                  </div>
                </div>
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
                               data-category="{{ $item->category }}"
                               data-unit-price="{{ $item->unit_price }}"
                               data-stock-qty="{{ $item->stock_qty }}"
                               data-low-stock-threshold="{{ $item->low_stock_threshold }}"
                               data-last-restocked="{{ $item->last_restocked ? \Carbon\Carbon::parse($item->last_restocked)->timezone('Asia/Manila')->format('M d, Y h:i A') : 'Never' }}">
                    </label>
                    </div>
                </td>
                <td>{{ $item->product_number }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        @if ($item->avatar)
                            <img src="{{ asset('storage/' . $item->avatar) }}"
                                alt="Product Image" class="avatar-circle mr-2">
                        @else
                            <div class="avatar-circle avatar-initial mr-2">
                                {{ strtoupper(substr($item->product_name, 0, 1)) }}
                            </div>
                        @endif
                        <span>{{ $item->product_name }}</span>
                    </div>
                </td>
                <td>
                    @php
                        $catSlug = strtolower(str_replace(' ', '-', $item->category));
                        $knownCats = ['supplement','supplements','equipment','apparel','beverages','drink','snacks','food','accessories'];
                        $badgeClass = in_array($catSlug, $knownCats) ? 'badge-category-'.$catSlug : 'badge-category-dynamic';
                        $catIcon = \App\Helpers\CategoryHelper::getIcon($item->category);
                        $dynamicStyle = !in_array($catSlug, $knownCats) && $item->category_color 
                            ? 'background: ' . $item->category_color . '20; color: ' . $item->category_color . ';' 
                            : '';
                    @endphp
                    <span class="badge-category {{ $badgeClass }}" @if($dynamicStyle) style="{{ $dynamicStyle }}" @endif>
                        <i class="mdi {{ $catIcon }}"></i>
                        {{ $item->category }}
                    </span>
                </td>
                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $item->stock_qty }}</td>
                <td>
                    @if($item->stock_qty == 0)
                    <span class="badge badge-danger"><span class="status-dot"></span>Out of Stock</span>
                    @elseif($item->stock_qty <= $item->low_stock_threshold)
                    <span class="badge badge-warning"><span class="status-dot"></span>Low Stock</span>
                    @else
                    <span class="badge badge-success"><span class="status-dot"></span>In Stock</span>
                    @endif
                </td>
                <td>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                          <i class="mdi mdi-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                          <button type="button" 
                                  class="dropdown-item view-product-btn"
                                  data-id="{{ $item->id }}"
                                  data-product-number="{{ $item->product_number }}"
                                  data-product-name="{{ $item->product_name }}"
                                  data-avatar="{{ $item->avatar }}"
                                  data-category="{{ $item->category }}"
                                  data-unit-price="{{ $item->unit_price }}"
                                  data-stock-qty="{{ $item->stock_qty }}"
                                  data-low-stock-threshold="{{ $item->low_stock_threshold }}"
                                  data-last-restocked="{{ $item->last_restocked ? \Carbon\Carbon::parse($item->last_restocked)->timezone('Asia/Manila')->format('M d, Y h:i A') : 'Never' }}"
                                  data-status="{{ $item->stock_qty == 0 ? 'Out of Stock' : ($item->stock_qty <= $item->low_stock_threshold ? 'Low Stock' : 'In Stock') }}"
                                  data-toggle="modal" 
                                  data-target="#viewProductModal">
                              <i class="mdi mdi-eye mr-2"></i> View Product
                          </button>
                          <button type="button" 
                                  class="dropdown-item edit-product-btn"
                                  data-id="{{ $item->id }}"
                                  data-product-number="{{ $item->product_number }}"
                                  data-product-name="{{ $item->product_name }}"
                                  data-avatar="{{ $item->avatar }}"
                                  data-category="{{ $item->category }}"
                                  data-unit-price="{{ $item->unit_price }}"
                                  data-toggle="modal" 
                                  data-target="#editProductModal">
                              <i class="mdi mdi-pencil mr-2"></i> Edit Product
                          </button>
                          <button type="button" 
                                  class="dropdown-item stock-history-btn"
                                  data-id="{{ $item->id }}"
                                  data-product-number="{{ $item->product_number }}"
                                  data-product-name="{{ $item->product_name }}"
                                  data-toggle="modal" 
                                  data-target="#stockHistoryModal">
                              <i class="mdi mdi-history mr-2"></i> View Stock History
                          </button>
                          <div class="dropdown-divider"></div>
                          <button type="button" 
                                  class="dropdown-item text-success stock-in-btn"
                                  data-id="{{ $item->id }}"
                                  data-product-number="{{ $item->product_number }}"
                                  data-product-name="{{ $item->product_name }}"
                                  data-category="{{ $item->category }}"
                                  data-stock-qty="{{ $item->stock_qty }}"
                                  data-status="{{ $item->stock_qty == 0 ? 'Out of Stock' : ($item->stock_qty <= $item->low_stock_threshold ? 'Low Stock' : 'In Stock') }}"
                                  data-status-class="{{ $item->stock_qty == 0 ? 'badge-danger' : ($item->stock_qty <= $item->low_stock_threshold ? 'badge-warning' : 'badge-success') }}"
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
                                  data-status="{{ $item->stock_qty == 0 ? 'Out of Stock' : ($item->stock_qty <= $item->low_stock_threshold ? 'Low Stock' : 'In Stock') }}"
                                  data-status-class="{{ $item->stock_qty == 0 ? 'badge-danger' : ($item->stock_qty <= $item->low_stock_threshold ? 'badge-warning' : 'badge-success') }}"
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
                <td colspan="8" class="text-center text-muted">No inventory items found</td>
                </tr>
            @endforelse
            </tbody>
          </table>
        </div>
        
        <!-- Pagination and Bulk Delete -->
        <div class="table-footer">
          <button type="button" id="bulkActionBtn" class="btn btn-sm btn-delete-selected" onclick="bulkDeleteInventory()">
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
