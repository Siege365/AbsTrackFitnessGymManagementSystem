@extends('layouts.admin')

@section('title', 'Activity Logs')

@push('styles')
@vite(['resources/css/activity-logs.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Activity Logs</h2>
            <p class="page-header-subtitle">Track and review staff activity logs and transactions.</p>
        </div>
    </div>
</div>

@include('UserAndAdmin.activity-partials._stats')
@include('UserAndAdmin.activity-partials._table')
@include('UserAndAdmin.activity-partials.modals._bulk-delete')

@endsection

@push('scripts')
@include('UserAndAdmin.activity-partials._scripts')
@endpush
