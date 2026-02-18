@extends('layouts.admin')

@section('title', 'Memberships Management - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/memberships.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Membership Management</h2>
            <p class="page-header-subtitle">View, add, edit, and manage gym memberships.</p>
        </div>
        <button class="btn btn-page-action" data-toggle="modal" data-target="#addMemberModal">
            <i class="mdi mdi-plus"></i> Add New Member
        </button>
    </div>
</div>

@include('memberships.partials._stats')

@include('memberships.partials._table')

@include('memberships.partials.modals._add')

@include('memberships.partials.modals._renew')

@include('memberships.partials.modals._delete')

@include('memberships.partials.modals._bulk-delete')

@endsection

@push('scripts')
@include('memberships.partials._scripts')
@endpush