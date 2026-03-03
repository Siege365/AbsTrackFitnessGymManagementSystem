@extends('layouts.admin')

@section('title', 'Trainer Management - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/trainer.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Trainer Management</h2>
            <p class="page-header-subtitle">View, add, and manage gym trainers and their specializations.</p>
        </div>
        <button class="btn btn-page-action" data-toggle="modal" data-target="#addTrainerModal">
            <i class="mdi mdi-plus"></i> Add New Trainer
        </button>
    </div>
</div>

@include('UserAndAdmin.trainer-partials._stats')
@include('UserAndAdmin.trainer-partials._table')
@include('UserAndAdmin.trainer-partials.modals._add')
@include('UserAndAdmin.trainer-partials.modals._delete')
@include('UserAndAdmin.trainer-partials.modals._bulk-delete')

@endsection

@push('scripts')
@include('UserAndAdmin.trainer-partials._scripts')
@endpush