@extends('layouts.admin')

@section('title', 'Memberships Management - AbsTrack Fitness Gym')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/memberships.css') }}?v={{ time() }}">
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Membership Management</h2>
            <p class="page-header-subtitle">View, add, edit, and manage gym memberships.</p>
        </div>
        <button class="btn btn-page-action" data-toggle="modal" data-target="#addMemberModal">
            <i class="mdi mdi-plus"></i> Add New Member
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card" data-filter="all">
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
    <div class="card stats-card" data-filter="active">
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
    <div class="card stats-card" data-filter="expiring">
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
    <div class="card stats-card" data-filter="new">
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
              <div class="dropdown d-inline-block mr-2">
                <button type="button" class="btn btn-sm filter-button dropdown-toggle" id="filterDropdown" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                  <i class="mdi mdi-filter-variant"></i> Filter
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="filterDropdown">
                  <h6 class="dropdown-header">Filter by Status</h6>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="MembershipsPage.applyStatusFilter('all')">
                    <i class="mdi mdi-account-multiple mr-2"></i> All Members
                  </a>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="MembershipsPage.applyStatusFilter('active')">
                    <i class="mdi mdi-check-circle mr-2 text-success"></i> Active Only
                  </a>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="MembershipsPage.applyStatusFilter('expired')">
                    <i class="mdi mdi-close-circle mr-2 text-danger"></i> Expired Only
                  </a>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="MembershipsPage.applyStatusFilter('due_soon')">
                    <i class="mdi mdi-clock-alert mr-2 text-warning"></i> Due Soon Only
                  </a>
                </div>
              </div>
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
              <tr data-status="{{ $membership->status }}" 
                  data-created="{{ $membership->created_at->format('Y-m') }}" 
                  data-expiring="{{ $membership->status == 'Due soon' ? 'yes' : 'no' }}">
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
                <td>@formatContact($membership->contact)</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                      <i class="mdi mdi-dots-horizontal"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $membership->id }}">
                        <i class="mdi mdi-eye mr-2"></i> View Details
                      </button>
                      <button type="button" class="dropdown-item text-success" 
                        onclick="openRenewModal({{ $membership->id }}, '{{ $membership->name }}', '{{ $membership->plan_type }}', '{{ $membership->start_date->format('Y-m-d') }}', '{{ $membership->due_date->format('Y-m-d') }}')">
                        <i class="mdi mdi-refresh mr-2"></i> Renew Subscription
                      </button>
                      <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteModal({{ $membership->id }}, '{{ $membership->name }}', '{{ $membership->plan_type }}', '{{ $membership->status }}')">
                        <i class="mdi mdi-delete mr-2"></i> Delete
                      </button>
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
            <div class="modal-content" style="position: relative;">
              <!-- Main Form Content -->
              <div id="editFormContent{{ $membership->id }}">
                <form id="editMemberForm{{ $membership->id }}" data-membership-id="{{ $membership->id }}" data-action="{{ route('memberships.update', $membership) }}">
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
                      <input type="text" name="name" id="editName{{ $membership->id }}" class="form-control" value="{{ $membership->name }}" required>
                    </div>

                    <!-- Age -->
                    <div class="form-group">
                      <label>Age</label>
                      <input type="number" name="age" id="editAge{{ $membership->id }}" class="form-control" value="{{ $membership->age }}" min="1" max="120">
                    </div>

                    <!-- Contact Number -->
                    <div class="form-group">
                      <label>Contact Number</label>
                      <input type="text" name="contact" id="editContact{{ $membership->id }}" class="form-control contact-input" value="{{ $membership->contact }}" required maxlength="13" pattern="^(\+63|0)[0-9]{10}$" title="Enter 11 digits (e.g., 09123456789) or +63 format (e.g., +639123456789)" oninput="validateContactInput(this)">
                      <small class="form-text text-muted">Format: 09XX-XXX-XXXX or +639XX-XXX-XXXX</small>                      <div id="editContact{{ $membership->id }}Error" class="invalid-feedback" style="display: none;"></div>                    </div>

                    <!-- Membership Plan -->
                    <div class="form-group">
                      <label>Membership Plan</label>
                      <select name="plan_type" id="editPlanType{{ $membership->id }}" class="form-control" required>
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
                    <button type="button" class="btn btn-update" onclick="showEditConfirmModal({{ $membership->id }})">
                      <i class="mdi mdi-pencil"></i> Update
                    </button>
                  </div>
                </form>
              </div>

              <!-- Confirmation Overlay -->
              <div id="editConfirmOverlay{{ $membership->id }}" class="confirm-overlay" style="display: none;">
                <div class="confirm-overlay-content">
                  <div class="confirm-overlay-header">
                    <i class="mdi mdi-pencil-outline"></i>
                    <h5>Confirm Update</h5>
                    <button type="button" class="close" onclick="backToEditForm({{ $membership->id }})">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="confirm-overlay-body">
                    <p class="mb-3">Are you sure you want to update this member?</p>
                    <div class="confirm-details">
                      <div class="confirm-row">
                        <span class="confirm-label">Name:</span>
                        <span class="confirm-value" id="confirmEditName{{ $membership->id }}"></span>
                      </div>
                      <div class="confirm-row">
                        <span class="confirm-label">Plan:</span>
                        <span class="confirm-value" id="confirmEditPlan{{ $membership->id }}"></span>
                      </div>
                      <div class="confirm-row">
                        <span class="confirm-label">Duration:</span>
                        <span class="confirm-value" id="confirmEditDuration{{ $membership->id }}"></span>
                      </div>
                    </div>
                  </div>
                  <div class="confirm-overlay-footer">
                    <button type="button" class="btn btn-cancel" onclick="backToEditForm({{ $membership->id }})">Cancel</button>
                    <button type="button" class="btn btn-update" onclick="submitEditForm({{ $membership->id }})">
                      <i class="mdi mdi-check"></i> Confirm
                    </button>
                  </div>
                </div>
              </div>
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
              {{ $memberships->links('vendor.pagination.custom') }}
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
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="addMemberFormContent">
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
              <input type="text" name="contact" id="newMemberContact" class="form-control contact-input" placeholder="09123456789" required maxlength="13" pattern="^(\+63|0)[0-9]{10}$" title="Enter 11 digits (e.g., 09123456789) or +63 format (e.g., +639123456789)" oninput="validateContactInput(this)">
              <small class="form-text text-muted">Format: 09XX-XXX-XXXX or +639XX-XXX-XXXX</small>              <div id="newMemberContactError" class="invalid-feedback" style="display: none;"></div>            </div>

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

      <!-- Confirmation Overlay -->
      <div id="addMemberConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Member</h5>
            <button type="button" class="close" onclick="backToAddForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to add this member?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToAddForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitMemberForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Renew Subscription Modal -->
<div class="modal fade" id="renewMembershipModal" tabindex="-1" role="dialog" aria-labelledby="renewMembershipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="renewFormContent">
        <div class="modal-header">
          <h5 class="modal-title" id="renewMembershipModalLabel">Renew Subscription</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="renewMembershipForm">
            <input type="hidden" id="renewMembershipId" name="membership_id">
            <input type="hidden" id="renewMembershipName" name="membership_name">
            <input type="hidden" id="renewPlanType" name="plan_type">

            <div class="form-group">
              <label>Member Name</label>
              <input type="text" class="form-control" id="renewMemberNameDisplay" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
            </div>

            <div class="form-group">
              <label>Current Plan</label>
              <input type="text" class="form-control" id="renewPlanTypeDisplay" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
            </div>

            <div class="form-group">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="renewStartDate" class="form-control" required onchange="calculateRenewEndDate()">
            </div>

            <div class="form-group">
              <label>End Date <span class="text-danger">*</span></label>
              <input type="date" name="due_date" id="renewEndDate" class="form-control" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
            </div>

            <div class="alert alert-info" style="background-color: rgba(66, 165, 245, 0.1); border: 1px solid rgba(66, 165, 245, 0.3); color: #42A5F5;">
              <i class="mdi mdi-information"></i> The end date will be automatically calculated based on the membership plan type.
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showRenewConfirmModal()">Submit</button>
        </div>
      </div>

      <!-- Confirmation Overlay -->
      <div id="renewConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-refresh"></i>
            <h5>Confirm Renewal</h5>
            <button type="button" class="close" onclick="backToRenewForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to renew this subscription?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Member:</span>
                <span class="confirm-value" id="confirmRenewNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmRenewPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmRenewDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToRenewForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitRenewForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this member? This action cannot be undone.
        </div>

        <div class="form-group">
          <label>Member Name</label>
          <div class="form-control" id="deleteMemberName" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Membership Plan</label>
          <div class="form-control" id="deleteMemberPlan" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Status</label>
          <div class="form-control" id="deleteMemberStatus" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <form id="deleteForm" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Member</button>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bulkDeleteConfirmModalLabel">Confirm Bulk Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> <strong>Warning:</strong> This action cannot be undone!
        </div>
        <p class="mb-0" style="font-size: 1rem;">
          Are you sure you want to delete <strong><span id="bulkDeleteCount">0</span> selected member(s)</strong>?
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmBulkDelete()">Delete Selected</button>
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
    
    // Setup midnight auto-refresh for KPIs
    MembershipsPage.setupMidnightRefresh();
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
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('newMemberContact');
    const contactValue = contactInput.value.trim();
    
    // Check if contact is valid
    if (contactValue.startsWith('+63')) {
      if (contactValue.replace(/\D/g, '').length !== 12) {
        ToastUtils.showError('Phone number with +63 must have exactly 12 digits', 'Invalid Contact');
        contactInput.focus();
        return;
      }
    } else {
      if (contactValue.length !== 11) {
        ToastUtils.showError('Phone number must have exactly 11 digits', 'Invalid Contact');
        contactInput.focus();
        return;
      }
      if (!contactValue.startsWith('09')) {
        ToastUtils.showError('Phone number must start with 09', 'Invalid Contact');
        contactInput.focus();
        return;
      }
    }
    
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
    const checkedBoxes = document.querySelectorAll('.membership-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
      ToastUtils.showWarning('Please select at least one member to delete.', 'Warning');
      return false;
    }
    
    // Update count in modal
    document.getElementById('bulkDeleteCount').textContent = checkedBoxes.length;
    
    // Show confirmation modal
    $('#bulkDeleteConfirmModal').modal('show');
  }
  
  /**
   * Confirm and execute bulk delete
   */
  function confirmBulkDelete() {
    const form = document.getElementById('bulkDeleteForm');
    const checkedBoxes = document.querySelectorAll('.membership-checkbox:checked');
    
    // Remove any existing hidden inputs
    form.querySelectorAll('input[name="membership_ids[]"]').forEach(el => el.remove());
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'membership_ids[]';
      input.value = checkbox.value;
      form.appendChild(input);
    });
    
    // Close modal and submit form
    $('#bulkDeleteConfirmModal').modal('hide');
    form.submit();
  }

  function openRenewModal(membershipId, memberName, planType, startDate, dueDate) {
    MembershipsPage.openRenewModal(membershipId, memberName, planType, startDate, dueDate);
  }

  function calculateRenewEndDate() {
    MembershipsPage.calculateRenewEndDate();
  }

  function showRenewConfirmModal() {
    MembershipsPage.showRenewConfirmModal();
  }

  function backToRenewForm() {
    MembershipsPage.backToRenewForm();
  }

  function submitRenewForm() {
    MembershipsPage.submitRenewForm();
  }

  function showEditConfirmModal(membershipId) {
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('editContact' + membershipId);
    const contactValue = contactInput.value.trim();
    
    // Check if contact is valid
    if (contactValue.startsWith('+63')) {
      if (contactValue.replace(/\D/g, '').length !== 12) {
        ToastUtils.showError('Phone number with +63 must have exactly 12 digits', 'Invalid Contact');
        contactInput.focus();
        return;
      }
    } else {
      if (contactValue.length !== 11) {
        ToastUtils.showError('Phone number must have exactly 11 digits', 'Invalid Contact');
        contactInput.focus();
        return;
      }
      if (!contactValue.startsWith('09')) {
        ToastUtils.showError('Phone number must start with 09', 'Invalid Contact');
        contactInput.focus();
        return;
      }
    }
    
    MembershipsPage.showEditConfirmModal(membershipId);
  }

  function backToEditForm(membershipId) {
    MembershipsPage.backToEditForm(membershipId);
  }

  function submitEditForm(membershipId) {
    MembershipsPage.submitEditForm(membershipId);
  }

  function openDeleteModal(membershipId, memberName, planType, status) {
    MembershipsPage.openDeleteModal(membershipId, memberName, planType, status);
  }

  function confirmDelete() {
    MembershipsPage.confirmDelete();
  }

  // Contact number validation
  function validateContactInput(input) {
    // Remove non-numeric characters except + at start
    let value = input.value;
    if (value.startsWith('+63')) {
      value = '+63' + value.substring(3).replace(/\D/g, '');
      if (value.length > 13) value = value.substring(0, 13);
    } else {
      value = value.replace(/\D/g, '');
      if (value.length > 11) value = value.substring(0, 11);
    }
    input.value = value;

    // Get error message element
    const errorDiv = document.getElementById(input.id + 'Error');
    let errorMessage = '';

    // Validate length
    const numericLength = value.replace(/\D/g, '').length;
    if (value.startsWith('+63')) {
      if (numericLength !== 12) {
        errorMessage = 'Phone number with +63 must have exactly 12 digits (e.g., +639123456789)';
        input.setCustomValidity(errorMessage);
      } else {
        input.setCustomValidity('');
      }
    } else {
      if (numericLength === 0) {
        // Empty - required validation will handle this
        input.setCustomValidity('');
      } else if (numericLength < 11) {
        errorMessage = `Phone number must have exactly 11 digits. Current: ${numericLength} digit${numericLength !== 1 ? 's' : ''}`;
        input.setCustomValidity(errorMessage);
      } else if (!value.startsWith('09')) {
        errorMessage = 'Phone number must start with 09';
        input.setCustomValidity(errorMessage);
      } else {
        input.setCustomValidity('');
      }
    }

    // Show/hide error message
    if (errorDiv) {
      if (errorMessage) {
        errorDiv.textContent = errorMessage;
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
      } else {
        errorDiv.style.display = 'none';
        input.classList.remove('is-invalid');
        if (numericLength > 0) {
          input.classList.add('is-valid');
        } else {
          input.classList.remove('is-valid');
        }
      }
    }
  }
</script>
@endpush