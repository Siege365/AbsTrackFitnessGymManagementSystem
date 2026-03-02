@extends('layouts.admin')

@section('title', 'Transaction History')

@push('styles')
@vite(['resources/css/inventory.css', 'resources/css/transaction-history.css'])
@endpush

@section('content')
    <!-- Product Information Card -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-package-variant-closed mr-2"></i>Product Information
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('inventory.index') }}" class="btn btn-page-action-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Inventory
                            </a>
                            <button type="button" class="btn btn-page-action" id="txnEditProductBtn"
                                data-toggle="modal" 
                                data-target="#editProductModal"
                                data-id="{{ $item->id }}"
                                data-product-number="{{ $item->product_number }}"
                                data-product-name="{{ $item->product_name }}"
                                data-category="{{ $item->category }}"
                                data-unit-price="{{ $item->unit_price }}"
                                data-avatar="{{ $item->avatar }}">
                                <i class="mdi mdi-pencil"></i> Edit Product
                            </button>
                        </div>
                    </div>

                    <!-- Product Avatar + Info -->
                    <div class="d-flex align-items-start mb-4">
                        @if($item->avatar)
                            <img src="{{ asset('storage/' . $item->avatar) }}" 
                                 class="rounded-circle mr-3 txn-product-avatar">
                        @else
                            <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center txn-product-avatar-circle">
                                {{ strtoupper(substr($item->product_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-1 txn-product-name">{{ $item->product_name }}</h5>
                            <span class="text-muted">{{ $item->product_number }}</span>
                            @php
                                $catSlug = strtolower(str_replace(' ', '-', $item->category));
                                $knownCats = ['supplement','supplements','equipment','apparel','beverages','drink','snacks','food','accessories'];
                                $badgeClass = in_array($catSlug, $knownCats) ? 'badge-category-'.$catSlug : 'badge-category-other';
                            @endphp
                            <span class="badge-category {{ $badgeClass }} ml-2">
                                <i class="mdi mdi-tag-outline"></i>
                                {{ $item->category }}
                            </span>
                        </div>
                    </div>

                    <div class="product-detail-card">
                        <div class="product-detail-row">
                            <span class="product-detail-label">Unit Price</span>
                            <span class="product-detail-value">₱{{ number_format($item->unit_price, 2) }}</span>
                        </div>
                        <div class="product-detail-row">
                            <span class="product-detail-label">Current Stock</span>
                            <span class="product-detail-value txn-stock-value">{{ $item->stock_qty }}</span>
                        </div>
                        <div class="product-detail-row">
                            <span class="product-detail-label">Low Stock Threshold</span>
                            <span class="product-detail-value">{{ $item->low_stock_threshold }}</span>
                        </div>
                        <div class="product-detail-row">
                            <span class="product-detail-label">Status</span>
                            <span class="product-detail-value">
                                @if($item->stock_qty == 0)
                                    <span class="badge badge-danger"><span class="status-dot"></span>Out of Stock</span>
                                @elseif($item->stock_qty < $item->low_stock_threshold)
                                    <span class="badge badge-warning"><span class="status-dot"></span>Low Stock</span>
                                @else
                                    <span class="badge badge-success"><span class="status-dot"></span>In Stock</span>
                                @endif
                            </span>
                        </div>
                        <div class="product-detail-row">
                            <span class="product-detail-label">Last Restocked</span>
                            <span class="product-detail-value">
                                @if($item->last_restocked)
                                    {{ \Carbon\Carbon::parse($item->last_restocked)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </span>
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
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-history mr-2"></i>Stock History
                        </h4>
                        <span class="badge badge-pill txn-count-badge">
                            {{ $item->transactions->count() }} transaction(s)
                        </span>
                    </div>

                    @if($item->transactions->isEmpty())
                        <div class="text-center py-5">
                            <i class="mdi mdi-history mdi-48px text-muted"></i>
                            <p class="text-muted mt-3">No transactions recorded yet</p>
                        </div>
                    @else
                        <div class="timeline timeline-container">
                            <div class="timeline-inner">
                            @foreach($item->transactions as $transaction)
                                <div class="timeline-item {{ $transaction->transaction_type }}">
                                    <div class="card timeline-transaction-card">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="d-flex align-items-center mb-2">
                                                        @if($transaction->transaction_type === 'stock_in')
                                                            <span class="badge badge-success mr-2 timeline-badge-text">
                                                                <i class="mdi mdi-plus-circle"></i> Stock In
                                                            </span>
                                                        @else
                                                            <span class="badge badge-warning mr-2 timeline-badge-text">
                                                                <i class="mdi mdi-minus-circle"></i> Stock Out
                                                            </span>
                                                        @endif
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($transaction->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="d-flex align-items-center flex-wrap timeline-detail-gap">
                                                        <div>
                                                            <small class="text-muted d-block">Quantity</small>
                                                            @if($transaction->transaction_type === 'stock_in')
                                                                <span class="text-success font-weight-bold">+{{ $transaction->quantity }}</span>
                                                            @else
                                                                <span class="text-warning font-weight-bold">-{{ $transaction->quantity }}</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Stock Change</small>
                                                            <span>{{ $transaction->previous_stock }} → {{ $transaction->new_stock }}</span>
                                                        </div>
                                                        @if($transaction->notes)
                                                        <div>
                                                            <small class="text-muted d-block">Notes</small>
                                                            <span>{{ $transaction->notes }}</span>
                                                        </div>
                                                        @endif
                                                    </div>

                                                    @if($transaction->performed_by)
                                                        <small class="text-muted mt-2 d-block">
                                                            <i class="mdi mdi-account-outline"></i> {{ $transaction->performed_by }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="text-right">
                                                    <small class="text-muted">#{{ $transaction->id }}</small>
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
            <div class="card summary-card-stock-in">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Stock In</h6>
                            <h3 class="text-success mb-0">
                                +{{ $item->transactions->where('transaction_type', 'stock_in')->sum('quantity') }}
                            </h3>
                            <small class="text-muted">
                                {{ $item->transactions->where('transaction_type', 'stock_in')->count() }} transaction(s)
                            </small>
                        </div>
                        <i class="mdi mdi-package-variant mdi-36px text-success summary-icon-light"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin">
            <div class="card summary-card-stock-out">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Stock Out</h6>
                            <h3 class="text-warning mb-0">
                                -{{ $item->transactions->where('transaction_type', 'stock_out')->sum('quantity') }}
                            </h3>
                            <small class="text-muted">
                                {{ $item->transactions->where('transaction_type', 'stock_out')->count() }} transaction(s)
                            </small>
                        </div>
                        <i class="mdi mdi-package-down mdi-36px text-warning summary-icon-light"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin">
            <div class="card summary-card-net-change">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Net Change</h6>
                            @php
                                $stockIn = $item->transactions->where('transaction_type', 'stock_in')->sum('quantity');
                                $stockOut = $item->transactions->where('transaction_type', 'stock_out')->sum('quantity');
                                $netChange = $stockIn - $stockOut;
                            @endphp
                            <h3 class="mb-0">
                                @if($netChange > 0)
                                    <span class="text-success">+{{ $netChange }}</span>
                                @elseif($netChange < 0)
                                    <span class="text-danger">{{ $netChange }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </h3>
                            <small class="text-muted">Overall movement</small>
                        </div>
                        <i class="mdi mdi-swap-vertical mdi-36px summary-icon-lighter"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared Edit Product Modal -->
    @include('InventorySupplies.partials.modals._edit')
@endsection

@push('scripts')
@vite(['resources/js/pages/inventory.js'])
@if($errors->any())
<script>
    // Show edit modal if there are validation errors
    document.addEventListener('DOMContentLoaded', function() {
        $('#editProductModal').modal('show');
    });
</script>
@endif
@endpush