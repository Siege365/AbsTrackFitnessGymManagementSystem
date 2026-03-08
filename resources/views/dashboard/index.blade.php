@extends('layouts.admin')

@section('title', 'Dashboard - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/dashboard.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Dashboard</h2>
            <p class="page-header-subtitle">Welcome back! Here's your gym overview for today.</p>
        </div>
        <div class="header-date">
            <i class="mdi mdi-calendar-today"></i>
            <span>{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>
</div>

{{-- Level 1: Critical KPIs at a glance --}}
@include('dashboard.partials._top-kpis')

{{-- Level 2: Subsystem summary cards --}}
<div class="dashboard-section">
    <h4 class="section-title">
        <i class="mdi mdi-view-dashboard-outline"></i> Subsystem Overview
    </h4>
    <div class="row">
        <div class="col-xl-4 col-md-6 grid-margin stretch-card">
            @include('dashboard.partials._clients-summary')
        </div>
        <div class="col-xl-4 col-md-6 grid-margin stretch-card">
            @include('dashboard.partials._memberships-summary')
        </div>
        <div class="col-xl-4 col-md-6 grid-margin stretch-card">
            @include('dashboard.partials._sessions-summary')
        </div>
        <div class="col-xl-6 col-md-6 grid-margin stretch-card">
            @include('dashboard.partials._payments-summary')
        </div>
        <div class="col-xl-6 col-md-6 grid-margin stretch-card">
            @include('dashboard.partials._inventory-summary')
        </div>
    </div>
</div>

{{-- Level 3: Recent activity & upcoming items --}}
@include('dashboard.partials._recent-activity')

@endsection

@push('scripts')
@include('dashboard.partials._scripts')
@endpush
