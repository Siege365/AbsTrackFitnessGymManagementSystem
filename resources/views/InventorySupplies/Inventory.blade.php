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

<meta name="next-product-number-url" content="{{ route('inventory.next-product-number') }}">
@if($errors->any() && old('product_name'))
<meta name="has-validation-errors" content="1">
@endif

@include('InventorySupplies.partials._stats')

@include('InventorySupplies.partials._table')

@include('InventorySupplies.partials.modals._add')

@include('InventorySupplies.partials.modals._edit')

@include('InventorySupplies.partials.modals._view')

@include('InventorySupplies.partials.modals._stock-in')

@include('InventorySupplies.partials.modals._stock-out')

@include('InventorySupplies.partials.modals._stock-history')

@include('InventorySupplies.partials.modals._delete')

@endsection

@push('scripts')
@include('InventorySupplies.partials._scripts')
@endpush
