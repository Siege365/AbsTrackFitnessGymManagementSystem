<!-- ========================================== -->
<!-- CATEGORY MANAGEMENT SECTION                -->
<!-- ========================================== -->
<div class="config-section" id="categoriesSection">

  <div class="row">
    <div class="col-12 grid-margin">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h4 class="card-title mb-1">
                Inventory Categories
              </h4>
              <p class="mb-0" style="font-size: 0.9375rem; color: #a3a4a7;">
                Manage product categories used across inventory. Rename, change color, or delete unused categories.
              </p>
            </div>
            <!-- Search Bar -->
            <div class="search-wrapper">
              <input type="text" 
                    id="categoriesSearchInput" 
                    class="form-control form-control-sm" 
                    placeholder="Search categories..." 
                    style="width: 100%; max-width: 450px;">
            </div>
          </div>

          @if(isset($categories) && $categories->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover" id="categoriesTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Category</th>
                  <th>Color</th>
                  <th>Icon</th>
                  <th>Products</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($categories as $index => $cat)
                <tr data-category-name="{{ $cat->name }}">
                  <td>{{ $index + 1 }}</td>
                  <td><strong>{{ $cat->name }}</strong></td>
                  <td>
                    <span class="category-color-swatch" style="background: {{ $cat->color ?? '#888' }};"></span>
                    <span style="font-family: monospace; color: #6c7293; font-size: 0.9375rem;">{{ $cat->color ?? 'None' }}</span>
                  </td>
                  <td>
                    <i class="mdi {{ $cat->icon }}" style="color: {{ $cat->color ?? '#FFA726' }};"></i>
                  </td>
                  <td>
                    <span class="badge badge-count">{{ $cat->product_count }}</span>
                  </td>
                  <td>
                    <div class="dropdown">
                            <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown"
                              data-display="static" data-boundary="window"
                              aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <button type="button" class="dropdown-item edit-category-btn"
                                data-category-name="{{ $cat->name }}"
                                data-category-color="{{ $cat->color ?? '' }}">
                          <i class="mdi mdi-pencil mr-2"></i> Edit Category
                        </button>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item text-danger delete-category-btn"
                                data-category-name="{{ $cat->name }}"
                                data-product-count="{{ $cat->product_count }}">
                          <i class="mdi mdi-delete mr-2"></i> Delete Category
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          @if($categories->hasPages())
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} categories
            </div>
            <div>
              {{ $categories->links() }}
            </div>
          </div>
          @endif
          @else
          <div class="text-center py-5">
            <i class="mdi mdi-shape-outline d-block mb-2" style="font-size: 3rem; color: #555;"></i>
            <p class="text-muted" style="font-size: 1rem;">No categories found. Categories are created automatically when you add inventory products.</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
