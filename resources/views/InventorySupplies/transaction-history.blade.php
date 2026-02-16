@extends('layouts.admin')

@section('title', 'Transaction History')

@push('styles')
@vite(['resources/css/inventory.css', 'resources/css/transaction-history.css'])
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <!-- Product Information Card -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Product Information</h4>
                        <button type="button" class="btn btn-lg btn-outline-info" data-toggle="modal" data-target="#editProductModal">
                            <i class="mdi mdi-pencil"></i> Edit Product
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Product Number:</strong></p>
                            <p class="text-muted">{{ $item->product_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Product Name:</strong></p>
                            <p class="text-muted">{{ $item->product_name }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Category:</strong></p>
                            <p class="text-muted">{{ $item->category }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Last Restocked:</strong></p>
                            <p class="text-muted">
                                @if($item->last_restocked)
                                    {{ \Carbon\Carbon::parse($item->last_restocked)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Unit Price:</strong></p>
                            <p class="text-muted">₱{{ number_format($item->unit_price, 2) }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Current Stock:</strong></p>
                            <h4 class="mb-0">{{ $item->stock_qty }}</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong>Status:</strong></p>
                            @if($item->stock_qty == 0)
                                <span class="badge badge-danger">Out of Stock</span>
                            @elseif($item->stock_qty < $item->low_stock_threshold)
                                <span class="badge badge-warning">Low Stock</span>
                            @else
                                <span class="badge badge-success">In Stock</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Stock History</h4>
                        <span class="text-muted">{{ $item->transactions->count() }} transaction(s)</span>
                    </div>

                    @if($item->transactions->isEmpty())
                        <div class="text-center py-5">
                            <i class="mdi mdi-history mdi-48px text-muted"></i>
                            <p class="text-muted mt-3">No transactions recorded yet</p>
                        </div>
                    @else
                        <div class="timeline">
                            <div class="timeline-inner">
                            @foreach($item->transactions as $transaction)
                                <div class="timeline-item {{ $transaction->transaction_type }}">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-center mb-2">
                                                        @if($transaction->transaction_type === 'stock_in')
                                                            <span class="badge badge-success mr-2">
                                                                <i class="mdi mdi-plus-circle"></i> Stock In
                                                            </span>
                                                        @else
                                                            <span class="badge badge-warning mr-2">
                                                                <i class="mdi mdi-minus-circle"></i> Stock Out
                                                            </span>
                                                        @endif
                                                        <span class="text-muted">
                                                            {{ \Carbon\Carbon::parse($transaction->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <p class="mb-1">
                                                            <strong>Quantity:</strong> 
                                                            @if($transaction->transaction_type === 'stock_in')
                                                                <span class="text-success">+{{ $transaction->quantity }}</span>
                                                            @else
                                                                <span class="text-warning">-{{ $transaction->quantity }}</span>
                                                            @endif
                                                        </p>
                                                        <p class="mb-1">
                                                            <strong>Stock Change:</strong> 
                                                            {{ $transaction->previous_stock }} → {{ $transaction->new_stock }}
                                                        </p>
                                                        @if($transaction->notes)
                                                            <p class="mb-1">
                                                                <strong>Notes:</strong> {{ $transaction->notes }}
                                                            </p>
                                                        @endif
                                                        @if($transaction->performed_by)
                                                            <p class="mb-0 text-muted">
                                                                <small>Performed by: {{ $transaction->performed_by }}</small>
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <div class="text-muted mb-2">
                                                        <small>Transaction #{{ $transaction->id }}</small>
                                                    </div>
                                                    <div>
                                                        @if($transaction->transaction_type === 'stock_in')
                                                            <i class="mdi mdi-package-variant mdi-48px text-success"></i>
                                                        @else
                                                            <i class="mdi mdi-package-down mdi-48px text-warning"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-md-4 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Stock In</h6>
                    <h3 class="text-success">
                        +{{ $item->transactions->where('transaction_type', 'stock_in')->sum('quantity') }}
                    </h3>
                    <p class="text-muted mb-0">
                        {{ $item->transactions->where('transaction_type', 'stock_in')->count() }} transaction(s)
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Stock Out</h6>
                    <h3 class="text-warning">
                        -{{ $item->transactions->where('transaction_type', 'stock_out')->sum('quantity') }}
                    </h3>
                    <p class="text-muted mb-0">
                        {{ $item->transactions->where('transaction_type', 'stock_out')->count() }} transaction(s)
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Net Change</h6>
                    <h3>
                        @php
                            $stockIn = $item->transactions->where('transaction_type', 'stock_in')->sum('quantity');
                            $stockOut = $item->transactions->where('transaction_type', 'stock_out')->sum('quantity');
                            $netChange = $stockIn - $stockOut;
                        @endphp
                        @if($netChange > 0)
                            <span class="text-success">+{{ $netChange }}</span>
                        @elseif($netChange < 0)
                            <span class="text-danger">{{ $netChange }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </h3>
                    <p class="text-muted mb-0">Overall movement</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editProductModalLabel">
                        <i class="mdi mdi-pencil"></i> Edit Product
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('inventory.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Product Number (Read-only) -->
                        <div class="form-group">
                            <label class="form-label">Product Number</label>
                            <input type="text" 
                                   class="form-control bg-light" 
                                   value="{{ $item->product_number }}" 
                                   readonly
                                   style="cursor: not-allowed;">
                            <small class="text-muted">Product number cannot be changed</small>
                        </div>

                        <!-- Product Name -->
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('product_name') is-invalid @enderror" 
                                   name="product_name" 
                                   value="{{ old('product_name', $item->product_name) }}" 
                                   required>
                            @error('product_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category Dropdown -->
                        <div class="form-group">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-control @error('category') is-invalid @enderror" 
                                    name="category" 
                                    required>
                                <option value="Food" {{ old('category', $item->category) == 'Food' ? 'selected' : '' }}>Food</option>
                                <option value="Drink" {{ old('category', $item->category) == 'Drink' ? 'selected' : '' }}>Drink</option>
                                <option value="Supplement" {{ old('category', $item->category) == 'Supplement' ? 'selected' : '' }}>Supplement</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

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
                                       value="{{ old('unit_price', $item->unit_price) }}" 
                                       required>
                            </div>
                            @error('unit_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-check"></i> Save Changes
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
    // Show edit modal if there are validation errors
    @if($errors->any())
        $('#editProductModal').modal('show');
    @endif
});
</script>
@endpush