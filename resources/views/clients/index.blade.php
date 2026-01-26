@extends('layouts.admin')

@section('title', 'Customers → Clients')

@push('styles')
<style>
  .card {
    background: #2A3038;
    border: none;
  }

  .card-body {
    color: #ffffff;
  }

  .stats-card {
    background: #2A3038;
    border: none;
    border-radius: 8px;
    transition: transform 0.2s;
  }

  .stats-card:hover {
    transform: translateY(-2px);
  }

  .stats-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .table-responsive::-webkit-scrollbar {
    height: 8px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: #191C24;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background-color: #555;
    border-radius: 4px;
  }

  .table {
    color: #ffffff;
  }

  .table thead th {
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .table tbody td {
    color: rgba(255, 255, 255, 0.9);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
  }

  .form-control {
    background-color: #2A3038;
    border: 1px solid #555;
    color: #ffffff;
  }

  .form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
  }

  .form-control:focus {
    background-color: #343a46;
    border-color: #191C24;
    color: #ffffff;
    box-shadow: 0 0 0 0.2rem rgba(25, 28, 36, 0.25);
  }

  .btn-primary {
    background-color: #191C24;
    border-color: #191C24;
  }

  .btn-primary:hover {
    background-color: #0d0f14;
    border-color: #0d0f14;
  }

  .btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
  }

  .btn-info:hover {
    background-color: #138496;
    border-color: #138496;
  }

  .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
  }

  .badge-active {
    background: rgba(76, 175, 80, 0.2);
    color: #4CAF50;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
  }

  .badge-expired {
    background: rgba(244, 67, 54, 0.2);
    color: #F44336;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
  }

  .badge-warning {
    background: rgba(255, 193, 7, 0.2);
    color: #FFC107;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
  }

  .dropdown-menu {
    background: #2A3038;
    border: 1px solid #555;
  }

  .dropdown-item {
    color: #ffffff;
  }

  .dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
  }

  .pagination .page-item.active .page-link {
    background-color: #191C24;
    border-color: #191C24;
  }
  
  .pagination .page-link {
    color: #555;
  }
  
  .pagination .page-link:hover {
    background-color: #191C24;
    border-color: #191C24;
    color: #ffffff;
  }

  .pagination-wrapper .pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s ease-in-out;
  }

  .pagination-wrapper .pagination .page-item.disabled .page-link {
    background-color: #f8f9fa;
    border-color: #dee2e6;
  }

  .pagination-wrapper {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .modal-content {
    background: #2A3038;
    border: none;
  }

  .modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .modal-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .close {
    color: #ffffff;
    opacity: 0.8;
  }

  .close:hover {
    color: #ffffff;
    opacity: 1;
  }

  .alert {
    border: none;
    border-radius: 8px;
  }

  .alert-success {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
  }

  .alert-danger {
    background-color: rgba(220, 53, 69, 0.2);
    color: #dc3545;
  }

  .page-header {
    margin-bottom: 1.5rem;
  }

  .page-title {
    color: #ffffff;
    font-weight: 500;
  }

  .avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
  }

  .avatar-initial {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .btn-action {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #ffffff;
  }

  .btn-action:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
  }

  .btn-delete-selected {
    background: rgba(244, 67, 54, 0.2);
    border: none;
    color: #F44336;
  }

  .btn-delete-selected:hover {
    background: rgba(244, 67, 54, 0.3);
    color: #F44336;
  }

  .search-info {
    background: rgba(23, 162, 184, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  select.form-control option {
    background: #2A3038;
    color: white;
  }

  .modal-body label {
    color: rgba(255, 255, 255, 0.7);
    font-size: 13px;
    font-weight: 500;
  }

  .btn-update {
    background-color: #FFA500;
    border: none;
    color: white;
  }

  .btn-update:hover {
    background-color: #ff8c00;
    color: white;
  }

  .btn-cancel {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
  }

  .btn-cancel:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
  }

  .btn-upload {
    background: rgba(52, 152, 219, 0.8);
    border: none;
    color: white;
  }

  .btn-upload:hover {
    background: rgba(52, 152, 219, 1);
    color: white;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">Customers → Clients</h3>
    </div>
  </div>
</div>

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
            <h2 class="mb-0">{{ $totalClients }}</h2>
            <p class="text-muted mb-0">Total Clients</p>
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
            <h2 class="mb-0">{{ $activeClients }}</h2>
            <p class="text-muted mb-0">Active Clients</p>
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

<!-- Clients Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <!-- Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">List Of Clients</h4>
          <div class="d-flex">
            <form action="{{ route('clients.index') }}" method="GET" class="d-flex mr-2">
              <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search by name, contact, plan, or status..." value="{{ request('search') }}" style="width: 300px;">
              <button type="submit" class="btn btn-sm btn-info mr-2">
                <i class="mdi mdi-magnify"></i> Search
              </button>
              @if(request('search'))
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-secondary mr-2">
                  <i class="mdi mdi-close"></i> Clear
                </a>
              @endif
            </form>
            <button class="btn btn-sm btn-primary" onclick="window.location.href='{{ route('clients.create') }}'">
              <i class="mdi mdi-plus"></i> Add Client
            </button>
          </div>
        </div>

        <!-- Search Results Info -->
        @if(request('search'))
        <div class="search-info p-3 mb-3">
          <p class="mb-0">
            <i class="mdi mdi-information"></i> 
            Showing {{ $clients->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
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
              @forelse($clients as $client)
              <tr>
                <td>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input client-checkbox" name="client_ids[]" value="{{ $client->id }}">
                    </label>
                  </div>
                </td>
                <td>{{ str_pad($client->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    @if($client->avatar)
                      <img src="{{ asset('storage/' . $client->avatar) }}" class="avatar-circle mr-2">
                    @else
                      <div class="avatar-initial mr-2">
                        {{ strtoupper(substr($client->name, 0, 1)) }}
                      </div>
                    @endif
                    <span>{{ $client->name }}</span>
                  </div>
                </td>
                <td>{{ $client->plan_type }}</td>
                <td>{{ $client->start_date->format('d M Y') }}</td>
                <td>{{ $client->due_date->format('d M Y') }}</td>
                <td>
                  @if($client->status == 'Active')
                    <span class="badge badge-active">
                      <i class="mdi mdi-circle" style="font-size: 8px;"></i> Active
                    </span>
                  @elseif($client->status == 'Expired')
                    <span class="badge badge-expired">
                      <i class="mdi mdi-circle" style="font-size: 8px;"></i> Expired
                    </span>
                  @else
                    <span class="badge badge-warning">
                      <i class="mdi mdi-circle" style="font-size: 8px;"></i> Due soon
                    </span>
                  @endif
                </td>
                <td>{{ $client->contact }}</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown">
                      <i class="mdi mdi-dots-horizontal"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $client->id }}">
                        <i class="mdi mdi-eye mr-2"></i> View
                      </button>
                      <form action="{{ route('clients.renew', $client) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item text-success" onclick="return confirm('Are you sure you want to renew this subscription for another month?')">
                          <i class="mdi mdi-refresh mr-2"></i> Renew Subscription
                        </button>
                      </form>
                      <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this client?')">
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
                      <p class="mt-3">No clients found matching "{{ request('search') }}". <a href="{{ route('clients.index') }}" class="text-info">Clear search</a></p>
                    @else
                      <p class="mt-3">No clients found. <a href="{{ route('clients.create') }}" class="text-info">Add your first client</a></p>
                    @endif
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Edit Client Modals -->
        @foreach($clients as $client)
        <div class="modal fade" id="viewModal{{ $client->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel{{ $client->id }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title" id="viewModalLabel{{ $client->id }}">Edit Client</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <!-- Name -->
                  <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
                  </div>

                  <!-- Age -->
                  <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control" value="{{ $client->age }}" min="1" max="120">
                  </div>

                  <!-- Contact Number -->
                  <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact" class="form-control" value="{{ $client->contact }}" required pattern="^[+]?[0-9() ]+$" title="Please enter a valid contact number (only numbers, +, (), and spaces allowed. NO minus signs!)">
                  </div>

                  <!-- Plan Type -->
                  <div class="form-group">
                    <label>Plan Type</label>
                    <select name="plan_type" class="form-control" required>
                      <option value="Monthly" {{ $client->plan_type == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                      <option value="Session" {{ $client->plan_type == 'Session' ? 'selected' : '' }}>Session</option>
                    </select>
                  </div>

                  <!-- Start Date -->
                  <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $client->start_date->format('Y-m-d') }}" required>
                  </div>

                  <!-- End Date -->
                  <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $client->due_date->format('Y-m-d') }}" required>
                  </div>

                  <!-- Avatar (optional) -->
                  <div class="form-group">
                    <label>Avatar (optional)</label>
                    <div class="text-center">
                      <div id="avatarPreview{{ $client->id }}" class="mb-2">
                        @if($client->avatar)
                          <img src="{{ asset('storage/' . $client->avatar) }}" alt="{{ $client->name }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255, 255, 255, 0.2);">
                        @else
                          <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 48px; color: white; font-weight: 600;">
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                          </div>
                        @endif
                      </div>
                      <input type="file" name="avatar" id="avatarInput{{ $client->id }}" accept="image/*" style="display: none;" onchange="previewAvatar({{ $client->id }})">
                      <button type="button" class="btn btn-sm btn-upload" onclick="document.getElementById('avatarInput{{ $client->id }}').click()">
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
              <form id="bulkDeleteForm" action="{{ route('clients.bulk-delete') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" onclick="bulkDelete()" class="btn btn-sm btn-delete-selected">
                  <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
              </form>
            </div>
            <div class="col-md-6 col-sm-12">
              <nav aria-label="Page navigation">
                {{ $clients->links('pagination::bootstrap-4') }}
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
  function previewAvatar(clientId) {
    const input = document.getElementById('avatarInput' + clientId);
    const preview = document.getElementById('avatarPreview' + clientId);
    
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
    const clientCheckboxes = document.querySelectorAll('.client-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Select/Deselect All
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        clientCheckboxes.forEach(checkbox => {
          checkbox.checked = this.checked;
        });
        updateSelectedCount();
      });
    }

    // Update count when individual checkboxes change
    clientCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateSelectedCount();
        // Update select all checkbox state
        if (selectAllCheckbox) {
          const allChecked = Array.from(clientCheckboxes).every(cb => cb.checked);
          const someChecked = Array.from(clientCheckboxes).some(cb => cb.checked);
          selectAllCheckbox.checked = allChecked;
          selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
      });
    });

    function updateSelectedCount() {
      const count = document.querySelectorAll('.client-checkbox:checked').length;
      if (selectedCountSpan) {
        selectedCountSpan.textContent = count;
      }
    }
  });

  function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.client-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
      alert('Please select at least one client to delete.');
      return;
    }

    const count = checkedBoxes.length;
    const confirmation = confirm(`Are you sure you want to delete ${count} client(s)? This action cannot be undone.`);
    
    if (confirmation) {
      const form = document.getElementById('bulkDeleteForm');
      
      // Add selected IDs to form
      checkedBoxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'client_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
      });
      
      form.submit();
    }
  }
</script>
@endpush