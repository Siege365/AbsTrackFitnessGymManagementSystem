@extends('layouts.admin')

@section('title', 'Customers → Membership')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/memberships.css') }}?v={{ time() }}">
@endpush

@section('content')

<!-- Success/Error Alerts -->
@if(session('success'))
<div class="row">
  <div class="col-12">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="mdi mdi-check-circle mr-2"></i>
      <strong>Success!</strong> {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
</div>
@endif

@if(session('error'))
<div class="row">
  <div class="col-12">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="mdi mdi-alert-circle mr-2"></i>
      <strong>Error!</strong> {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
</div>
@endif

<!-- Statistics Cards -->
<div class="row">
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0">{{ $totalMembers }}</h2>
            <p class="text-muted mb-0">Total Members</p>
          </div>
          <div class="stats-icon bg-danger">
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
            <h2 class="mb-0">{{ $activeMembers }}</h2>
            <p class="text-muted mb-0">Active Members</p>
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
            <h2 class="mb-0">{{ $expiringThisWeek }}</h2>
            <p class="text-muted mb-0">Expiring This Week</p>
          </div>
          <div class="stats-icon bg-warning">
            <i class="mdi mdi-clock-alert text-white" style="font-size: 24px;"></i>
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
            <h2 class="mb-0">{{ $newSignupsThisMonth }}</h2>
            <p class="text-muted mb-0">New Signups This Month</p>
          </div>
          <div class="stats-icon bg-info">
            <i class="mdi mdi-account-plus text-white" style="font-size: 24px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Memberships Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <!-- Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">List Of Memberships</h4>
          <div class="d-flex align-items-center">
            <form action="{{ route('memberships.index') }}" method="GET" class="d-flex align-items-center" id="searchForm">
              <input 
                type="text" 
                name="search" 
                class="form-control form-control-sm mr-2" 
                placeholder="Search by name, contact, plan, or status..." 
                value="{{ request('search') }}" 
                style="width: 450px;"
                id="searchInput">
              <button type="button" class="btn btn-sm filter-button mr-2" data-toggle="modal" data-target="#filterModal">
                <i class="mdi mdi-filter-variant"></i> Filter
              </button>
              @if(request('search'))
                <a href="{{ route('memberships.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="mdi mdi-close"></i>
                </a>
              @endif
            </form>
          </div>
        </div>

        <script>
          document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              document.getElementById('searchForm').submit();
            }
          });
        </script>

        <!-- Search Results Info -->
        @if(request('search'))
        <div class="search-info p-3 mb-3">
          <p class="mb-0">
            <i class="mdi mdi-information"></i> 
            Showing {{ $memberships->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
          </p>
        </div>
        @endif

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width: 50px;">
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" id="selectAll">
                    </label>
                  </div>
                </th>
                <th>Id#</th>
                <th>Name</th>
                <th>Plan Type</th>
                <th>Start Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Contact #</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($memberships as $membership)
              <tr>
                <td>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input membership-checkbox" name="membership_ids[]" value="{{ $membership->id }}">
                    </label>
                  </div>
                </td>
                <td>{{ str_pad($membership->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    @if($membership->avatar)
                      <img src="{{ asset('storage/' . $membership->avatar) }}" class="avatar-circle mr-2">
                    @else
                      <div class="avatar-initial mr-2">
                        {{ strtoupper(substr($membership->name, 0, 1)) }}
                      </div>
                    @endif
                    <span>{{ $membership->name }}</span>
                  </div>
                </td>
                <td>{{ $membership->plan_type }}</td>
                <td>{{ $membership->start_date->format('d M Y') }}</td>
                <td>{{ $membership->due_date->format('d M Y') }}</td>
                <td>
                  @if($membership->status == 'Active')
                    <span class="badge badge-active">
                      <i class="mdi mdi-circle" style="font-size: 8px;"></i> Active
                    </span>
                  @elseif($membership->status == 'Expired')
                    <span class="badge badge-expired">
                      <i class="mdi mdi-circle" style="font-size: 8px;"></i> Expired
                    </span>
                  @else
                    <span class="badge badge-warning">
                      <i class="mdi mdi-circle" style="font-size: 8px;"></i> Due soon
                    </span>
                  @endif
                </td>
                <td>{{ $membership->contact }}</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown">
                      <i class="mdi mdi-dots-horizontal"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $membership->id }}">
                        <i class="mdi mdi-eye mr-2"></i> View
                      </button>
                      <form action="{{ route('memberships.renew', $membership) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item text-success" onclick="return confirm('Are you sure you want to renew this membership for another month?')">
                          <i class="mdi mdi-refresh mr-2"></i> Renew Subscription
                        </button>
                      </form>
                      <form action="{{ route('memberships.destroy', $membership) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this membership?')">
                          <i class="mdi mdi-delete mr-2"></i> Delete
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center py-5">
                  <div class="text-muted">
                    <i class="mdi mdi-{{ request('search') ? 'magnify-close' : 'account-off' }}" style="font-size: 48px; opacity: 0.5;"></i>
                    @if(request('search'))
                      <p class="mt-3">No memberships found matching "{{ request('search') }}". <a href="{{ route('memberships.index') }}" class="text-info">Clear search</a></p>
                    @else
                      <p class="mt-3">No memberships found. <a href="{{ route('memberships.create') }}" class="text-info">Add your first member</a></p>
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
            <div class="modal-content">
              <form action="{{ route('memberships.update', $membership) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title" id="viewModalLabel{{ $membership->id }}">Edit Member</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <!-- Name -->
                  <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $membership->name }}" required>
                  </div>

                  <!-- Age -->
                  <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control" value="{{ $membership->age }}" min="1" max="120">
                  </div>

                  <!-- Contact Number -->
                  <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" class="form-control" value="{{ $membership->contact }}" required pattern="^[+]?[0-9() ]+$" title="Please enter a valid contact number (only numbers, +, (), and spaces allowed. NO minus signs!)">
                  </div>

                  <!-- Membership Plan -->
                  <div class="form-group">
                    <label>Membership Plan</label>
                    <select name="plan_type" class="form-control" required>
                      <option value="Monthly" {{ $membership->plan_type == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                      <option value="Session" {{ $membership->plan_type == 'Session' ? 'selected' : '' }}>Session</option>
                    </select>
                  </div>

                  <!-- Start Date -->
                  <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $membership->start_date->format('Y-m-d') }}" required>
                  </div>

                  <!-- End Date -->
                  <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $membership->due_date->format('Y-m-d') }}" required>
                  </div>

                  <!-- Avatar (optional) -->
                  <div class="form-group">
                    <label>Avatar (optional)</label>
                    <div class="text-center">
                      <div id="avatarPreview{{ $membership->id }}" class="mb-2">
                        @if($membership->avatar)
                          <img src="{{ asset('storage/' . $membership->avatar) }}" alt="{{ $membership->name }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255, 255, 255, 0.2);">
                        @else
                          <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 48px; color: white; font-weight: 600;">
                            {{ strtoupper(substr($membership->name, 0, 1)) }}
                          </div>
                        @endif
                      </div>
                      <input type="file" name="avatar" id="avatarInput{{ $membership->id }}" accept="image/*" style="display: none;" onchange="previewAvatar({{ $membership->id }})">
                      <button type="button" class="btn btn-sm btn-upload" onclick="document.getElementById('avatarInput{{ $membership->id }}').click()">
                        Upload
                      </button>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-update">
                    <i class="mdi mdi-pencil"></i> Update
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endforeach

        <!-- Pagination -->
        <div class="pagination-wrapper mt-4 pt-3" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
          <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
              <form id="bulkDeleteForm" action="{{ route('memberships.bulk-delete') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" onclick="bulkDelete()" class="btn btn-sm btn-delete-selected">
                  <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
              </form>
            </div>
            <div class="col-md-6 col-sm-12">
              <nav aria-label="Page navigation">
                {{ $memberships->links('pagination::bootstrap-4') }}
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function previewAvatar(membershipId) {
    const input = document.getElementById('avatarInput' + membershipId);
    const preview = document.getElementById('avatarPreview' + membershipId);
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255, 255, 255, 0.2);">';
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
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        membershipCheckboxes.forEach(checkbox => {
          checkbox.checked = this.checked;
        });
        updateSelectedCount();
      });
    }

    // Update count when individual checkboxes change
    membershipCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateSelectedCount();
        // Update select all checkbox state
        if (selectAllCheckbox) {
          const allChecked = Array.from(membershipCheckboxes).every(cb => cb.checked);
          const someChecked = Array.from(membershipCheckboxes).some(cb => cb.checked);
          selectAllCheckbox.checked = allChecked;
          selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
      });
    });

    function updateSelectedCount() {
      const count = document.querySelectorAll('.membership-checkbox:checked').length;
      if (selectedCountSpan) {
        selectedCountSpan.textContent = count;
      }
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
@endpush