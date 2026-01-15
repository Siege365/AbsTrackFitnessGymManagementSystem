@extends('layouts.admin')

@section('title', 'Customers → Membership')

@section('content')
<div class="content-wrapper" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); min-height: 100vh;">
    <div class="container-fluid">
        <!-- Breadcrumb Header -->
        <div class="row mb-4 pt-4">
            <div class="col-12">
                <h3 class="text-white mb-0" style="font-weight: 500;">Customers → Membership</h3>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if(session('success'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" style="background: rgba(40, 167, 69, 0.9); border: none; border-radius: 10px; color: white;" role="alert">
                    <i class="mdi mdi-check-circle mr-2"></i>
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" style="background: rgba(220, 53, 69, 0.9); border: none; border-radius: 10px; color: white;" role="alert">
                    <i class="mdi mdi-alert-circle mr-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="text-white mb-0" style="font-weight: 600;">{{ $totalMembers }}</h2>
                                <p class="text-white-50 mb-0" style="font-size: 14px;">Total Members</p>
                            </div>
                            <div class="bg-danger" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-account-multiple text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="text-white mb-0" style="font-weight: 600;">{{ $activeMembers }}</h2>
                                <p class="text-white-50 mb-0" style="font-size: 14px;">Active Members</p>
                            </div>
                            <div class="bg-success" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-check-circle text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="text-white mb-0" style="font-weight: 600;">{{ $expiringThisWeek }}</h2>
                                <p class="text-white-50 mb-0" style="font-size: 14px;">Expiring This Week</p>
                            </div>
                            <div class="bg-warning" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-clock-alert text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="text-white mb-0" style="font-weight: 600;">{{ $newSignupsThisMonth }}</h2>
                                <p class="text-white-50 mb-0" style="font-size: 14px;">New Signups This Month</p>
                            </div>
                            <div class="bg-info" style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-account-plus text-white" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memberships Table Card -->
        <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
            <div class="card-body p-0">
                <!-- Table Header -->
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                    <h4 class="text-white mb-0" style="font-weight: 500;">List Of Memberships</h4>
                    <div class="d-flex gap-2">
                        <form action="{{ route('memberships.index') }}" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, contact, plan, or status..." value="{{ request('search') }}" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; width: 300px;">
                            <button type="submit" class="btn btn-info" style="background: #17a2b8; border: none; padding: 8px 20px; border-radius: 8px;">
                                <i class="mdi mdi-magnify"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('memberships.index') }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 8px 20px; border-radius: 8px;">
                                    <i class="mdi mdi-close"></i> Clear
                                </a>
                            @endif
                        </form>
                        <button class="btn btn-primary" onclick="window.location.href='{{ route('memberships.create') }}'" style="background: #4CAF50; border: none; padding: 8px 20px; border-radius: 8px;">
                            <i class="mdi mdi-plus"></i> Add Member
                        </button>
                    </div>
                </div>

                <!-- Search Results Info -->
                @if(request('search'))
                <div class="p-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important; background: rgba(23, 162, 184, 0.1);">
                    <p class="mb-0 text-white">
                        <i class="mdi mdi-information"></i> 
                        Showing {{ $memberships->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
                    </p>
                </div>
                @endif

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem; width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Id#</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Name</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Plan Type</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Start Date</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Due Date</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Status</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Contact #</th>
                                <th style="color: rgba(255, 255, 255, 0.7); font-weight: 500; padding: 1rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($memberships as $membership)
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 1rem;">
                                    <input type="checkbox" class="form-check-input membership-checkbox" name="membership_ids[]" value="{{ $membership->id }}">
                                </td>
                                <td style="color: rgba(255, 255, 255, 0.9); padding: 1rem;">{{ str_pad($membership->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td style="padding: 1rem;">
                                    <div class="d-flex align-items-center">
                                        @if($membership->avatar)
                                            <img src="{{ asset('storage/' . $membership->avatar) }}" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600;">
                                                {{ strtoupper(substr($membership->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span class="text-white">{{ $membership->name }}</span>
                                    </div>
                                </td>
                                <td style="color: rgba(255, 255, 255, 0.9); padding: 1rem;">{{ $membership->plan_type }}</td>
                                <td style="color: rgba(255, 255, 255, 0.9); padding: 1rem;">{{ $membership->start_date->format('d M Y') }}</td>
                                <td style="color: rgba(255, 255, 255, 0.9); padding: 1rem;">{{ $membership->due_date->format('d M Y') }}</td>
                                <td style="padding: 1rem;">
                                    @if($membership->status == 'Active')
                                        <span class="badge" style="background: rgba(76, 175, 80, 0.2); color: #4CAF50; padding: 6px 12px; border-radius: 20px; font-weight: 500;">
                                            <i class="mdi mdi-circle" style="font-size: 8px;"></i> Active
                                        </span>
                                    @elseif($membership->status == 'Expired')
                                        <span class="badge" style="background: rgba(244, 67, 54, 0.2); color: #F44336; padding: 6px 12px; border-radius: 20px; font-weight: 500;">
                                            <i class="mdi mdi-circle" style="font-size: 8px;"></i> Expired
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(255, 193, 7, 0.2); color: #FFC107; padding: 6px 12px; border-radius: 20px; font-weight: 500;">
                                            <i class="mdi mdi-circle" style="font-size: 8px;"></i> Due soon
                                        </span>
                                    @endif
                                </td>
                                <td style="color: rgba(255, 255, 255, 0.9); padding: 1rem;">{{ $membership->contact }}</td>
                                <td style="padding: 1rem;">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-toggle="dropdown" style="background: rgba(255, 255, 255, 0.1); color: white; border: none; padding: 4px 12px;">
                                            <i class="mdi mdi-dots-horizontal"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" style="background: #2c3e50; border: none;">
                                            <button type="button" class="dropdown-item text-white" data-toggle="modal" data-target="#viewModal{{ $membership->id }}">
                                                <i class="mdi mdi-eye me-2"></i> View
                                            </button>
                                            <form action="{{ route('memberships.renew', $membership) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-success" onclick="return confirm('Are you sure you want to renew this membership for another month?')">
                                                    <i class="mdi mdi-refresh me-2"></i> Renew Subscription
                                                </button>
                                            </form>
                                            <form action="{{ route('memberships.destroy', $membership) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this membership?')">
                                                    <i class="mdi mdi-delete me-2"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-white-50">
                                        <i class="mdi mdi-{{ request('search') ? 'magnify-close' : 'account-off' }}" style="font-size: 48px; opacity: 0.5;"></i>
                                        @if(request('search'))
                                            <p class="mt-3">No memberships found matching "{{ request('search') }}". <a href="{{ route('memberships.index') }}" class="text-primary">Clear search</a></p>
                                        @else
                                            <p class="mt-3">No memberships found. <a href="{{ route('memberships.create') }}" class="text-primary">Add your first member</a></p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Edit Member Modals -->
                @foreach($memberships as $membership)
                <div class="modal fade" id="viewModal{{ $membership->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $membership->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); border: none; border-radius: 15px;">
                            <form action="{{ route('memberships.update', $membership) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header border-0" style="padding: 1.5rem;">
                                    <h5 class="modal-title text-white" id="viewModalLabel{{ $membership->id }}" style="font-weight: 600;">Edit Member</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="padding: 0 1.5rem 1.5rem 1.5rem;">
                                    <!-- Name -->
                                    <div class="form-group mb-3">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $membership->name }}" required style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px; border-radius: 8px;">
                                    </div>

                                    <!-- Age -->
                                    <div class="form-group mb-3">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">Age</label>
                                        <input type="number" name="age" class="form-control" value="{{ $membership->age }}" min="1" max="120" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px; border-radius: 8px;">
                                    </div>

                                    <!-- Contact Number -->
                                    <div class="form-group mb-3">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">Contact Number</label>
                                        <input type="text" name="contact" class="form-control" value="{{ $membership->contact }}" required pattern="^[+]?[0-9() ]+$" title="Please enter a valid contact number (only numbers, +, (), and spaces allowed. NO minus signs!)" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px; border-radius: 8px;">
                                    </div>

                                    <!-- Membership Plan -->
                                    <div class="form-group mb-3">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">Membership Plan</label>
                                        <select name="plan_type" class="form-control" required style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px; border-radius: 8px;">
                                            <option value="Monthly" {{ $membership->plan_type == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="Session" {{ $membership->plan_type == 'Session' ? 'selected' : '' }}>Session</option>
                                        </select>
                                    </div>

                                    <!-- Start Date -->
                                    <div class="form-group mb-3">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $membership->start_date->format('Y-m-d') }}" required style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px; border-radius: 8px;">
                                    </div>

                                    <!-- End Date -->
                                    <div class="form-group mb-3">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">End Date</label>
                                        <input type="date" name="due_date" class="form-control" value="{{ $membership->due_date->format('Y-m-d') }}" required style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px; border-radius: 8px;">
                                    </div>

                                    <!-- Avatar (optional) -->
                                    <div class="form-group mb-4">
                                        <label class="text-white-50 mb-2" style="font-size: 13px; font-weight: 500;">Avatar (optional)</label>
                                        <div style="text-align: center;">
                                            <div id="avatarPreview{{ $membership->id }}" class="mb-2">
                                                @if($membership->avatar)
                                                    <img src="{{ asset('storage/' . $membership->avatar) }}" alt="{{ $membership->name }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid rgba(255, 255, 255, 0.2);">
                                                @else
                                                    <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 48px; color: white; font-weight: 600;">
                                                        {{ strtoupper(substr($membership->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <input type="file" name="avatar" id="avatarInput{{ $membership->id }}" accept="image/*" style="display: none;" onchange="previewAvatar({{ $membership->id }})">
                                            <button type="button" class="btn btn-sm mt-2" onclick="document.getElementById('avatarInput{{ $membership->id }}').click()" style="background: rgba(52, 152, 219, 0.8); color: white; border: none; padding: 6px 20px; border-radius: 6px;">
                                                Upload
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn" data-dismiss="modal" style="background: rgba(255, 255, 255, 0.1); color: white; border: none; padding: 10px 24px; border-radius: 8px;">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn" style="background: #FFA500; color: white; border: none; padding: 10px 24px; border-radius: 8px;">
                                            <i class="mdi mdi-pencil"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-4 border-top" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                    <div>
                        <form id="bulkDeleteForm" action="{{ route('memberships.bulk-delete') }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="bulkDelete()" class="btn btn-sm text-danger" style="background: rgba(244, 67, 54, 0.2); border: none; padding: 8px 16px; border-radius: 8px;">
                                <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
                            </button>
                        </form>
                    </div>
                    <div>
                        {{ $memberships->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Pagination Styling */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-item .page-link {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        margin: 0 2px;
        border-radius: 8px;
        padding: 8px 14px;
    }
    .pagination .page-item.active .page-link {
        background: #4CAF50;
        color: white;
    }
    .pagination .page-item .page-link:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    /* Table Hover Effect */
    .table-hover tbody tr:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    
    /* Dropdown Menu Styling */
    .dropdown-menu {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1) !important;
    }
    
    /* Form Control Styling */
    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }
    
    /* Modal Styling */
    .modal-content {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    }
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.7);
    }
    .modal-dialog {
        max-width: 500px;
    }
    .form-control:disabled,
    .form-control[readonly] {
        background: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        opacity: 1;
    }
    select.form-control:disabled {
        background: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
</style>

<script>
    function previewAvatar(membershipId) {
        const input = document.getElementById('avatarInput' + membershipId);
        const preview = document.getElementById('avatarPreview' + membershipId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid rgba(255, 255, 255, 0.2);">';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Bulk Delete Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const membershipCheckboxes = document.querySelectorAll('.membership-checkbox');
        const selectedCountSpan = document.getElementById('selectedCount');

        // Select/Deselect All
        selectAllCheckbox.addEventListener('change', function() {
            membershipCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Update count when individual checkboxes change
        membershipCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                // Update select all checkbox state
                const allChecked = Array.from(membershipCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(membershipCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });

        function updateSelectedCount() {
            const count = document.querySelectorAll('.membership-checkbox:checked').length;
            selectedCountSpan.textContent = count;
        }
    });

    function bulkDelete() {
        const checkedBoxes = document.querySelectorAll('.membership-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one membership to delete.');
            return;
        }

        const count = checkedBoxes.length;
        const confirmation = confirm(`Are you sure you want to delete ${count} membership(s)? This action cannot be undone.`);
        
        if (confirmation) {
            const form = document.getElementById('bulkDeleteForm');
            
            // Add selected IDs to form
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'membership_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
            
            form.submit();
        }
    }
</script>
@endsection
