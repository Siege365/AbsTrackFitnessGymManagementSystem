@extends('layouts.admin')

@section('title', 'Customer Attendance - AbsTrack Fitness Gym')

@push('styles')
    @vite(['resources/css/sessions.css'])
@endpush

@section('content')

    <!-- Attendance Flash Messages -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AttendancePage.showToast('success', '{{ session('success') }}');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AttendancePage.showToast('error', '{{ session('error') }}');
            });
        </script>
    @endif

    <!-- Page Header -->
    <div class="card page-header-card">
        <div class="card-body">
            <div>
                <h2 class="page-header-title">Customer Attendance</h2>
                <p class="page-header-subtitle">Track daily gym check-ins and customer visits.</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <button class="btn btn-page-action" data-toggle="modal" data-target="#addAttendanceModal">
                    <i class="mdi mdi-account-check"></i> Record Attendance
                </button>
            </div>
        </div>
    </div>

    @include('Sessions.partials._attendance-stats')

    @include('Sessions.partials._attendance-table')

    @include('Sessions.partials.modals._add-attendance')

    @include('Sessions.partials.modals._view-attendance')

    @include('Sessions.partials.modals._delete')

    @include('Sessions.partials.modals._double-confirm')

    @include('Sessions.partials.modals._bulk-delete-confirm')

@endsection

@push('scripts')
    @include('Sessions.partials._attendance-scripts')
@endpush
