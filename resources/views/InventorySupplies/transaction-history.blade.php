@extends('layouts.admin')

@section('title', 'Transaction History')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inventory.css') }}?v={{ time() }}">
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid;
    }
    .timeline-item.stock-in::before {
        border-color: #28a745;
    }
    .timeline-item.stock-out::before {
        border-color: #ffc107;
    }
</style>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <!-- Product Information Card -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Product Information</h4>
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
                        <h4 class="card-title mb-0">Transaction History</h4>
                        <span class="text-muted">{{ $item->transactions->count() }} transaction(s)</span>
                    </div>

                    @if($item->transactions->isEmpty())
                        <div class="text-center py-5">
                            <i class="mdi mdi-history mdi-48px text-muted"></i>
                            <p class="text-muted mt-3">No transactions recorded yet</p>
                        </div>
                    @else
                        <div class="timeline">
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
@endsection