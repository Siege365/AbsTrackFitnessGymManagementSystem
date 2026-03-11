@extends('layouts.admin')

@section('title', 'Staff Accounts')

@push('styles')
@vite(['resources/css/staff-management.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Staff Accounts</h2>
            <p class="page-header-subtitle">View, add, and manage system users and admin accounts.</p>
        </div>
        <a href="{{ route('register') }}" class="btn btn-page-action">
            <i class="mdi mdi-plus"></i> Add New Staff
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $totalStaff }}</h2>
                        <p class="text-muted mb-0">Total Staff</p>
                    </div>
                    <div class="stats-icon bg-primary">
                        <i class="mdi mdi-account-multiple text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $activeStaff }}</h2>
                        <p class="text-muted mb-0">Active Staff</p>
                    </div>
                    <div class="stats-icon bg-success">
                        <i class="mdi mdi-check-circle text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $adminCount }}</h2>
                        <p class="text-muted mb-0">Administrators</p>
                    </div>
                    <div class="stats-icon bg-warning">
                        <i class="mdi mdi-shield-account text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $cashierCount }}</h2>
                        <p class="text-muted mb-0">Cashiers</p>
                    </div>
                    <div class="stats-icon bg-info">
                        <i class="mdi mdi-cash-register text-white" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Table -->
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">List of Staff</h4>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('staff.index') }}" method="GET" class="d-flex align-items-center" id="searchFormStaff">
                            <div class="search-wrapper mr-2">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search by name, email, contact..."
                                    value="{{ request('search') }}"
                                    style="width: 100%; max-width: 350px;"
                                    id="searchInputStaff">
                            </div>
                            <div class="dropdown d-inline-block mr-2">
                                <button type="button" class="btn btn-sm filter-button dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    <i class="mdi mdi-filter-variant"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-right filter-accordion">
                                    <div class="filter-header">
                                        <span class="filter-title">Filter By</span>
                                        <a href="{{ route('staff.index') }}" class="filter-clear-all">Clear All</a>
                                    </div>
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="StaffPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-shield-account"></i>
                                                <span>Role</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <a class="filter-option" href="{{ route('staff.index', ['role' => 'admin']) }}">
                                                <i class="mdi mdi-shield-account"></i> Admin
                                            </a>
                                            <a class="filter-option" href="{{ route('staff.index', ['role' => 'cashier']) }}">
                                                <i class="mdi mdi-cash-register"></i> Cashier
                                            </a>
                                        </div>
                                    </div>
                                    <div class="filter-section">
                                        <div class="filter-section-header" onclick="StaffPage.toggleFilterSection(this, event)">
                                            <div class="filter-section-title">
                                                <i class="mdi mdi-circle-outline"></i>
                                                <span>Status</span>
                                            </div>
                                            <i class="mdi mdi-chevron-down filter-chevron"></i>
                                        </div>
                                        <div class="filter-section-content">
                                            <a class="filter-option" href="{{ route('staff.index', ['status' => 'active']) }}">
                                                <i class="mdi mdi-check-circle"></i> Active
                                            </a>
                                            <a class="filter-option" href="{{ route('staff.index', ['status' => 'inactive']) }}">
                                                <i class="mdi mdi-close-circle"></i> Inactive
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(request('search') || request('role') || request('status'))
                                <a href="{{ route('staff.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        Showing {{ $staff->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
                    </p>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID#</th>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Role</th>
                                <th>Contact #</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $user)
                            <tr>
                                <td>{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" class="avatar-circle mr-2">
                                        @else
                                            <div class="avatar-initial mr-2">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-{{ $user->role === 'admin' ? 'warning' : 'info' }}">
                                        {{ ucfirst($user->role ?? 'cashier') }}
                                    </span>
                                </td>
                                <td>{{ $user->contact ?? '—' }}</td>
                                <td>{{ $user->address ? \Illuminate\Support\Str::limit($user->address, 30) : '—' }}</td>
                                <td>
                                    @if($user->status === 'active')
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
                                        <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-display="static" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <button type="button" class="dropdown-item"
                                                onclick="StaffPage.openEditModal({{ json_encode($user) }})">
                                                <i class="mdi mdi-pencil mr-2"></i> Edit Details
                                            </button>
                                            @if($user->id !== auth()->id())
                                            <button type="button" class="dropdown-item"
                                                onclick=\"StaffPage.toggleStatus({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->status }}')\">
                                                <i class="mdi mdi-{{ $user->status === 'active' ? 'close-circle' : 'check-circle' }} mr-2"></i>
                                                {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button type="button" class="dropdown-item text-danger"
                                                onclick="StaffPage.openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                <i class="mdi mdi-delete mr-2"></i> Delete
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="mdi mdi-account-off" style="font-size: 48px; color: #555;"></i>
                                    <p class="text-muted mt-2">No staff accounts found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($staff->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $staff->links('vendor.pagination.custom') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Staff Account</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editStaffForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Full Name</label>
                            <input type="text" name="name" id="editStaffName" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email Address</label>
                            <input type="email" name="email" id="editStaffEmail" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Contact #</label>
                            <input type="text" name="contact" id="editStaffContact" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Role</label>
                            <select name="role" id="editStaffRole" class="form-control">
                                <option value="cashier">Cashier</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="editStaffAddress" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="status" id="editStaffStatus" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
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

<!-- Delete Staff Modal -->
<div class="modal fade" id="deleteStaffModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
                    <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this staff account? This action cannot be undone.
                </div>
                <div class="form-group">
                    <label>Staff Name</label>
                    <div class="form-control" id="deleteStaffName"></div>
                </div>
                <form id="deleteStaffForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="StaffPage.confirmDelete()">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Status Confirmation Modal with Captcha -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toggleStatusTitle">Confirm Status Change</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert" id="toggleStatusAlert" style="background-color: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); color: #FFC107;">
                    <i class="mdi mdi-alert-circle"></i> <span id="toggleStatusMessage"></span>
                </div>
                <div class="form-group">
                    <label>Please verify you are human to continue:</label>
                    <div id="toggleStatusTurnstile" class="mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmToggleStatusBtn" onclick="StaffPage.confirmToggleStatus()" disabled>Confirm</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/pages/staff.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    StaffPage.init({
        csrfToken: '{{ csrf_token() }}',
        turnstileSiteKey: '{{ config("services.turnstile.site_key") }}'
    });

    const searchInput = document.getElementById('searchInputStaff');
    const searchForm = document.getElementById('searchFormStaff');
    if (searchInput && searchForm) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); searchForm.submit(); }
        });
    }
});
</script>
@endpush
