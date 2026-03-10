@extends('layouts.admin')

@section('title', 'Trainers')

@push('styles')
@vite(['resources/css/staff-management.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Trainers</h2>
            <p class="page-header-subtitle">View, add, and manage gym trainers and their specializations.</p>
        </div>
        <button class="btn btn-page-action" data-toggle="modal" data-target="#addTrainerModal">
            <i class="mdi mdi-plus"></i> Add New Trainer
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $totalTrainers }}</h2>
                        <p class="text-muted mb-0">Total Trainers</p>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="mdi mdi-account-multiple text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $activeTrainers }}</h2>
                        <p class="text-muted mb-0">Active Trainers</p>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="mdi mdi-check-circle text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $inactiveTrainers }}</h2>
                        <p class="text-muted mb-0">Inactive Trainers</p>
                    </div>
                    <div class="stats-icon bg-danger">
                        <i class="mdi mdi-close-circle text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trainers Table -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">List of Trainers</h4>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('trainers.index') }}" method="GET" class="d-flex align-items-center" id="searchFormTrainers">
                            <div class="search-wrapper mr-2">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search by name, specialization..."
                                    value="{{ request('search') }}"
                                    style="width: 100%; max-width: 350px;"
                                    id="searchInputTrainers">
                            </div>
                            <div class="dropdown d-inline-block mr-2">
                                <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    <i class="mdi mdi-filter-variant"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-right filter-accordion">
                                    <div class="filter-header">
                                        <span class="filter-title">Filter By</span>
                                        <a href="{{ route('trainers.index') }}" class="filter-clear-all">Clear All</a>
                                    </div>
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="TrainersPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-circle-outline"></i>
                                                <span>Status</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <a class="filter-option" href="{{ route('trainers.index', ['status' => 'active']) }}">
                                                <i class="mdi mdi-check-circle"></i> Active
                                            </a>
                                            <a class="filter-option" href="{{ route('trainers.index', ['status' => 'inactive']) }}">
                                                <i class="mdi mdi-close-circle"></i> Inactive
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(request('search') || request('status'))
                                <a href="{{ route('trainers.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-close"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if(request('search'))
                <div class="search-info p-3 mb-3">
                    <p class="mb-0">
                        <i class="mdi mdi-information"></i>
                        Showing {{ $trainers->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
                    </p>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID#</th>
                                <th>Full Name</th>
                                <th>Specialization</th>
                                <th>Contact #</th>
                                <th>Emergency Contact #</th>
                                <th>Birthdate</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trainers as $trainer)
                            <tr>
                                <td>{{ str_pad($trainer->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($trainer->avatar)
                                            <img src="{{ asset('storage/' . $trainer->avatar) }}" class="avatar-circle mr-2">
                                        @else
                                            <div class="avatar-initial mr-2">
                                                {{ strtoupper(substr($trainer->full_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span>{{ $trainer->full_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $trainer->specialization ?? '—' }}</td>
                                <td>{{ $trainer->contact_number }}</td>
                                <td>{{ $trainer->emergency_contact ?? '—' }}</td>
                                <td>{{ $trainer->birth_date ? $trainer->birth_date->format('d M Y') : '—' }}</td>
                                <td>{{ $trainer->address ? \Illuminate\Support\Str::limit($trainer->address, 30) : '—' }}</td>
                                <td>
                                    @if($trainer->status === 'active')
                                        <span class="badge badge-active">
                                            <i class="mdi mdi-circle" style="font-size: 8px;"></i> Active
                                        </span>
                                    @else
                                        <span class="badge badge-expired">
                                            <i class="mdi mdi-circle" style="font-size: 8px;"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-boundary="viewport">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button type="button" class="dropdown-item"
                                                onclick="TrainersPage.openEditModal({{ json_encode($trainer) }})">
                                                <i class="mdi mdi-pencil mr-2"></i> Edit Details
                                            </button>
                                            <button type="button" class="dropdown-item text-danger"
                                                onclick="TrainersPage.openDeleteModal({{ $trainer->id }}, '{{ addslashes($trainer->full_name) }}')">
                                                <i class="mdi mdi-delete mr-2"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="mdi mdi-account-off" style="font-size: 48px; color: #555;"></i>
                                    <p class="text-muted mt-2">No trainers found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($trainers->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $trainers->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Trainer Modal -->
<div class="modal fade" id="addTrainerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Trainer</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addTrainerForm" method="POST" action="{{ route('trainers.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="Full name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Specialization</label>
                            <input type="text" name="specialization" class="form-control" placeholder="e.g. Weight Training, Cardio">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="contact_number" class="form-control" placeholder="Contact number" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Emergency Contact #</label>
                            <input type="text" name="emergency_contact" class="form-control" placeholder="Emergency contact number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Complete address">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Birth Date</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Avatar <span class="text-muted">(optional)</span></label>
                            <div class="avatar-upload-wrapper">
                                <input type="file" name="avatar" class="form-control" accept="image/*" id="addTrainerAvatar"
                                    onchange="TrainersPage.previewAvatar(this, 'addTrainerAvatarPreview')">
                                <div class="avatar-preview mt-2" id="addTrainerAvatarPreview" style="display:none;">
                                    <img src="" alt="Preview" class="avatar-circle" style="width:60px; height:60px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Trainer Modal -->
<div class="modal fade" id="editTrainerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Trainer</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editTrainerForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="editTrainerName" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Specialization</label>
                            <input type="text" name="specialization" id="editTrainerSpecialization" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="contact_number" id="editTrainerContact" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Emergency Contact #</label>
                            <input type="text" name="emergency_contact" id="editTrainerEmergency" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="editTrainerAddress" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Birth Date</label>
                            <input type="date" name="birth_date" id="editTrainerBirthDate" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select name="status" id="editTrainerStatus" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Trainer Modal -->
<div class="modal fade" id="deleteTrainerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
                    <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this trainer? This action cannot be undone.
                </div>
                <div class="form-group">
                    <label>Trainer Name</label>
                    <div class="form-control" id="deleteTrainerName"></div>
                </div>
                <form id="deleteTrainerForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="TrainersPage.confirmDelete()">Delete Trainer</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite(['resources/js/pages/trainers.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    TrainersPage.init({ csrfToken: '{{ csrf_token() }}' });

    const searchInput = document.getElementById('searchInputTrainers');
    const searchForm = document.getElementById('searchFormTrainers');
    if (searchInput && searchForm) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); searchForm.submit(); }
        });
    }
});
</script>
@endpush
