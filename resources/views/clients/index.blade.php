@extends('layouts.admin')

@section('title', 'Clients Management - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/clients.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Client Management</h2>
            <p class="page-header-subtitle">View, add, edit, and manage client records.</p>
        </div>
        <button class="btn btn-page-action" data-toggle="modal" data-target="#addClientModal">
            <i class="mdi mdi-plus"></i> Add New Client
        </button>
    </div>
</div>

@include('clients.partials._stats')

@include('clients.partials._table')

@include('clients.partials.modals._add')

@include('clients.partials.modals._renew')

@include('clients.partials.modals._delete')

@include('clients.partials.modals._bulk-delete')

@endsection

@push('scripts')
@include('clients.partials._scripts')
@endpush