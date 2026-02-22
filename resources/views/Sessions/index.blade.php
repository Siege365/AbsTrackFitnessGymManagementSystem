@extends('layouts.admin')

@section('title', 'Sessions - AbsTrack Fitness Gym')

@push('styles')
    @vite(['resources/css/sessions.css'])
    <!-- Select2 CSS (for trainer dropdown only) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')

    <!-- Session Flash Messages -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                SessionsPage.showToast('success', '{{ session('success') }}');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                SessionsPage.showToast('error', '{{ session('error') }}');
            });
        </script>
    @endif

    <!-- Page Header -->
    <div class="card page-header-card">
        <div class="card-body">
            <div>
                <h2 class="page-header-title">Session Management</h2>
                <p class="page-header-subtitle">Manage personal training schedules and customer attendance.</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <button class="btn btn-page-action" data-toggle="modal" data-target="#addPTScheduleModal">
                    <i class="mdi mdi-calendar-plus"></i> Book PT Schedule
                </button>
                <button class="btn btn-page-action" data-toggle="modal" data-target="#addAttendanceModal">
                    <i class="mdi mdi-account-check"></i> Record Attendance
                </button>
            </div>
        </div>
    </div>

    @include('Sessions.partials._stats')

    @include('Sessions.partials._pt-table')

    @include('Sessions.partials._attendance-table')

    @include('Sessions.partials.modals._add-pt')

    @include('Sessions.partials.modals._view-edit-pt')

    @include('Sessions.partials.modals._book-next')

    @include('Sessions.partials.modals._add-attendance')

    @include('Sessions.partials.modals._view-attendance')

    @include('Sessions.partials.modals._delete')

    @include('Sessions.partials.modals._double-confirm')

    @include('Sessions.partials.modals._cancel-pt')

    @include('Sessions.partials.modals._reschedule')

    @include('Sessions.partials.modals._confirm-pt')

    @include('Sessions.partials.modals._bulk-delete-confirm')

@endsection

@push('scripts')
    @include('Sessions.partials._scripts')
@endpush
