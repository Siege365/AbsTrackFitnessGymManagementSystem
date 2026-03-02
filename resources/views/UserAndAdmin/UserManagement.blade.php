@extends('layouts.admin')

@section('title', 'Staff Accounts - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/staff.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Staff Accounts</h2>
            <p class="page-header-subtitle">View, add, and manage system users and admin accounts.</p>
        </div>
        <button class="btn btn-page-action" data-toggle="modal" data-target="#addStaffModal">
            <i class="mdi mdi-plus"></i> Add New Staff
        </button>
    </div>
</div>

@include('UserAndAdmin.partials._stats')
@include('UserAndAdmin.partials._table')
@include('UserAndAdmin.partials.modals._add')
@include('UserAndAdmin.partials.modals._delete')
@include('UserAndAdmin.partials.modals._bulk-delete')

@endsection

@push('scripts')
@include('UserAndAdmin.partials._scripts')
@endpush