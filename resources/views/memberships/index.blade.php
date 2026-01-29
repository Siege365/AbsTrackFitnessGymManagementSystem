@extends('layouts.admin')

@section('title', 'Memberships Management - AbsTrack Fitness Gym')

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

        {{-- Search enter key handled by MembershipsPage.init() --}}

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
                        <i class="mdi mdi-eye mr-2"></i> View Details
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
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
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
                    <input type="date" name="start_date" id="editStartDate{{ $membership->id }}" class="form-control" value="{{ $membership->start_date->format('Y-m-d') }}" onchange="calculateEditEndDate({{ $membership->id }})" required>
                  </div>

                  <!-- End Date -->
                  <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="due_date" id="editEndDate{{ $membership->id }}" class="form-control" value="{{ $membership->due_date->format('Y-m-d') }}" readonly>
                  </div>

                  <!-- Avatar (optional) -->
                  <div class="form-group">
                    <label>Avatar (optional)</label>
                    <div class="mb-2">
                      <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-outline-primary active">
                          <input type="radio" name="editAvatarInputType{{ $membership->id }}" value="file" checked onclick="toggleEditAvatarInput({{ $membership->id }}, 'file')"> Upload File
                        </label>
                        <label class="btn btn-sm btn-outline-primary">
                          <input type="radio" name="editAvatarInputType{{ $membership->id }}" value="url" onclick="toggleEditAvatarInput({{ $membership->id }}, 'url')"> Enter URL
                        </label>
                      </div>
                    </div>
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
                      <input type="file" name="avatar" id="avatarInput{{ $membership->id }}" class="form-control mb-2" accept="image/*" onchange="previewAvatar({{ $membership->id }})">
                      <input type="text" name="avatar_url" id="avatarUrl{{ $membership->id }}" class="form-control" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewAvatarUrl({{ $membership->id }})">
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

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addMemberModalLabel">Add Member</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addMemberForm">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" id="newMemberName" class="form-control" placeholder="John Doe" required>
          </div>

          <div class="form-group">
            <label>Age</label>
            <input type="number" name="age" id="newMemberAge" class="form-control" placeholder="24" min="1" max="120" required>
          </div>

          <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact" id="newMemberContact" class="form-control" placeholder="09123456789" required>
          </div>

          <div class="form-group">
            <label>Membership Plan</label>
            <select name="plan_type" id="newMemberPlan" class="form-control" required>
              <option value="">Select Plan</option>
              <option value="Monthly">Monthly</option>
              <option value="Session">Session</option>
            </select>
          </div>

          <div class="form-group">
            <label>Start Date</label>
            <input type="date" name="start_date" id="newMemberStartDate" class="form-control" onchange="calculateEndDate()" required>
          </div>

          <div class="form-group">
            <label>End Date</label>
            <input type="date" name="due_date" id="newMemberEndDate" class="form-control" readonly>
          </div>

          <div class="form-group">
            <label>Avatar (optional)</label>
            <div class="mb-2">
              <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-sm btn-outline-primary active">
                  <input type="radio" name="avatarInputType" value="file" checked onclick="toggleAvatarInput('file')"> Upload File
                </label>
                <label class="btn btn-sm btn-outline-primary">
                  <input type="radio" name="avatarInputType" value="url" onclick="toggleAvatarInput('url')"> Enter URL
                </label>
              </div>
            </div>
            <input type="file" name="avatar" id="newMemberAvatar" class="form-control" accept="image/*" onchange="previewNewAvatar()">
            <input type="text" name="avatar_url" id="newMemberAvatarUrl" class="form-control" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewNewAvatar()">
            <div id="newAvatarPreview" class="mt-3 text-center"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-update" onclick="showConfirmModal()">Submit</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmMemberModal" tabindex="-1" role="dialog" aria-labelledby="confirmMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmMemberModalLabel">Add Member</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Name</label>
          <div class="form-control" id="confirmName" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff; display: flex; align-items: center;">
            <span id="confirmNameText"></span>
            <img id="confirmAvatarSmall" src="" alt="" style="width: 30px; height: 30px; border-radius: 50%; margin-left: auto; display: none;">
          </div>
        </div>

        <div class="form-group">
          <label>Age</label>
          <div class="form-control" id="confirmAge" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Contact Number</label>
          <div class="form-control" id="confirmContact" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Membership Plan</label>
          <div class="form-control" id="confirmPlan" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Start Date</label>
          <div class="form-control" id="confirmStartDate" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>End Date</label>
          <div class="form-control" id="confirmEndDate" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Avatar (optional)</label>
          <div class="text-center">
            <img id="confirmAvatarLarge" src="" alt="Avatar Preview" style="max-width: 200px; max-height: 200px; border-radius: 10px; display: none;">
            <p id="noAvatarText" style="color: rgba(255, 255, 255, 0.6);">No avatar selected</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" onclick="backToAddForm()">Cancel</button>
        <button type="button" class="btn btn-update" onclick="submitMemberForm()">Submit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- Common Utilities -->
<script src="{{ asset('js/common/avatar-utils.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/common/form-utils.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/common/bulk-selection.js') }}?v={{ time() }}"></script>
<!-- Page Module -->
<script src="{{ asset('js/pages/memberships.js') }}?v={{ time() }}"></script>
<script>
  // Initialize memberships page with Laravel data
  document.addEventListener('DOMContentLoaded', function() {
    MembershipsPage.init({
      csrfToken: '{{ csrf_token() }}',
      storeUrl: '{{ route("memberships.store") }}'
    });
  });

  // Global function wrappers for onclick handlers in HTML
  function toggleAvatarInput(type) {
    MembershipsPage.toggleAvatarInput(type);
  }

  function calculateEndDate() {
    MembershipsPage.calculateEndDate();
  }

  function previewNewAvatar() {
    MembershipsPage.previewNewAvatar();
  }

  function showConfirmModal() {
    MembershipsPage.showConfirmModal();
  }

  function backToAddForm() {
    MembershipsPage.backToAddForm();
  }

  function submitMemberForm() {
    MembershipsPage.submitMemberForm();
  }

  function toggleEditAvatarInput(membershipId, type) {
    MembershipsPage.toggleEditAvatarInput(membershipId, type);
  }

  function calculateEditEndDate(membershipId) {
    MembershipsPage.calculateEditEndDate(membershipId);
  }

  function previewAvatar(membershipId) {
    MembershipsPage.previewAvatar(membershipId);
  }

  function previewAvatarUrl(membershipId) {
    MembershipsPage.previewAvatarUrl(membershipId);
  }

  function bulkDelete() {
    MembershipsPage.bulkDelete();
  }
</script>
@endpush