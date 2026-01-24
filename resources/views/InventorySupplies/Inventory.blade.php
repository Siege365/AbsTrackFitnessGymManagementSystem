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
    background-color: #191C24;
    border-color: #191C24;
  }
  
  .pagination .page-link {
    color: #555;
  }
  
  .pagination .page-link:hover {
    background-color: #191C24;
    border-color: #191C24;
    color: #ffffff;
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

  .pagination-wrapper .pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s ease-in-out;
  }

  .pagination-wrapper .pagination .page-item.disabled .page-link {
    background-color: #f8f9fa;
    border-color: #dee2e6;
  }
</style>
@endpush

@section('content')
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
                          <h3 class="mb-0">${{ number_format($stockValue ?? 0, 2) }}</h3>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="card-title mb-0">Inventory</h4>
                      <div class="d-flex">
                        <button class="btn btn-sm btn-outline-secondary mr-2">
                          <i class="mdi mdi-filter-variant"></i> Filter
                        </button>
                        <input type="text" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
                      </div>
                    </div>
                    <div class="table-responsive" style="min-height: 600px;">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th style="min-width: 50px;">
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
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
                                    <input type="checkbox" class="form-check-input">
                                </label>
                                </div>
                            </td>
                            <td>{{ $item->product_number }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->category }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
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
                                <button class="btn btn-sm btn-outline-primary"><i class="mdi mdi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></button>
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
                    
                    <!-- Pagination -->
                    @if(isset($inventoryItems) && $inventoryItems->total() > 0)
                        <div class="pagination-wrapper mt-4 pt-3" style="border-top: 1px solid #e9ecef;">
                            <div class="row align-items-center">
                                <div class="col-md-4 col-sm-12">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm justify-content-md-start justify-content-center mb-0">
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

                                            {{-- Pagination Elements --}}
                                            @foreach ($inventoryItems->getUrlRange(1, $inventoryItems->lastPage()) as $page => $url)
                                                @if ($page == $inventoryItems->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    @if ($page == 1 || $page == $inventoryItems->lastPage() || abs($page - $inventoryItems->currentPage()) < 3)
                                                        <li class="page-item">
                                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                        </li>
                                                    @elseif (abs($page - $inventoryItems->currentPage()) == 3)
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    @endif
                                                @endif
                                            @endforeach

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
                                </div>
                            </div>
                        </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            
@endsection