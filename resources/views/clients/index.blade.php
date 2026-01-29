@extends('layouts.admin')

@section('title', 'Clients Management - AbsTrack Fitness Gym')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/clients.css') }}?v={{ time() }}">
@endpush

@section('content')

<!-- Statistics Cards -->
<div class="row">
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card stats-card" data-filter="all">
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
    <div class="card stats-card" data-filter="active">
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

<!-- Clients Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <!-- Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">List Of Clients</h4>
          <div class="d-flex align-items-center">
            <form action="{{ route('clients.index') }}" method="GET" class="d-flex align-items-center" id="searchFormClients">
              <input 
                type="text" 
                name="search" 
                class="form-control form-control-sm mr-2" 
                placeholder="Search by name, contact, plan, or status..." 
                value="{{ request('search') }}" 
                style="width: 450px;"
                id="searchInputClients">
              <div class="dropdown d-inline-block mr-2">
                <button type="button" class="btn btn-sm filter-button dropdown-toggle" id="filterDropdownClients" data-toggle="dropdown" data-offset="0,2" data-flip="false" data-display="static" aria-haspopup="true" aria-expanded="false">
                  <i class="mdi mdi-filter-variant"></i> Filter
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="filterDropdownClients">
                  <h6 class="dropdown-header">Filter by Status</h6>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="ClientsPage.applyStatusFilter('all')">
                    <i class="mdi mdi-account-multiple mr-2"></i> All Clients
                  </a>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="ClientsPage.applyStatusFilter('active')">
                    <i class="mdi mdi-check-circle mr-2 text-success"></i> Active Only
                  </a>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="ClientsPage.applyStatusFilter('expired')">
                    <i class="mdi mdi-close-circle mr-2 text-danger"></i> Expired Only
                  </a>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="ClientsPage.applyStatusFilter('due_soon')">
                    <i class="mdi mdi-clock-alert mr-2 text-warning"></i> Due Soon Only
                  </a>
                </div>
              </div>
              @if(request('search'))
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="mdi mdi-close"></i>
                </a>
              @endif
            </form>
          </div>
        </div>

        {{-- Search enter key handled by ClientsPage.init() --}}

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
              <tr data-status="{{ $client->status }}" 
                  data-created="{{ $client->created_at->format('Y-m') }}" 
                  data-expiring="{{ $client->status == 'Due soon' ? 'yes' : 'no' }}">
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
                <td>@formatContact($client->contact)</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" data-offset="-100,2" data-flip="false" data-display="static">
                      <i class="mdi mdi-dots-horizontal"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $client->id }}">
                        <i class="mdi mdi-eye mr-2"></i> View
                      </button>
                      <button type="button" class="dropdown-item text-success" 
                        onclick="openRenewClientModal({{ $client->id }}, '{{ $client->name }}', '{{ $client->plan_type }}', '{{ $client->start_date->format('Y-m-d') }}', '{{ $client->due_date->format('Y-m-d') }}')">
                        <i class="mdi mdi-refresh mr-2"></i> Renew Subscription
                      </button>
                      <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteClientModal({{ $client->id }}, '{{ $client->name }}', '{{ $client->plan_type }}', '{{ $client->status }}')">
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
            <div class="modal-content" style="position: relative;">
              <!-- Main Form Content -->
              <div id="editClientFormContent{{ $client->id }}">
                <form id="editClientForm{{ $client->id }}" data-action="{{ route('clients.update', $client) }}">
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
                      <input type="text" name="name" id="editClientName{{ $client->id }}" class="form-control" value="{{ $client->name }}" required>
                    </div>

                    <!-- Age -->
                    <div class="form-group">
                      <label>Age</label>
                      <input type="number" name="age" id="editClientAge{{ $client->id }}" class="form-control" value="{{ $client->age }}" min="1" max="120">
                    </div>

                    <!-- Contact Number -->
                    <div class="form-group">
                      <label>Contact Number</label>
                      <input type="text" name="contact" id="editClientContact{{ $client->id }}" class="form-control contact-input" value="{{ $client->contact }}" required maxlength="13" pattern="^(\+63|0)[0-9]{10}$" title="Enter 11 digits (e.g., 09123456789) or +63 format (e.g., +639123456789)" oninput="validateContactInput(this)">
                      <small class="form-text text-muted">Format: 09XX-XXX-XXXX or +639XX-XXX-XXXX</small>                      <div id="editClientContact{{ $client->id }}Error" class="invalid-feedback" style="display: none;"></div>                    </div>

                    <!-- Plan Type -->
                    <div class="form-group">
                      <label>Plan Type</label>
                      <select name="plan_type" id="editClientPlanType{{ $client->id }}" class="form-control" required>
                        <option value="Monthly" {{ $client->plan_type == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="Session" {{ $client->plan_type == 'Session' ? 'selected' : '' }}>Session</option>
                      </select>
                    </div>

                    <!-- Start Date -->
                    <div class="form-group">
                      <label>Start Date</label>
                      <input type="date" name="start_date" id="editClientStartDate{{ $client->id }}" class="form-control" value="{{ $client->start_date->format('Y-m-d') }}" onchange="calculateEditClientEndDate({{ $client->id }})" required>
                    </div>

                    <!-- End Date -->
                    <div class="form-group">
                      <label>End Date</label>
                      <input type="date" name="due_date" id="editClientEndDate{{ $client->id }}" class="form-control" value="{{ $client->due_date->format('Y-m-d') }}" readonly>
                    </div>

                    <!-- Avatar (optional) -->
                    <div class="form-group">
                      <label>Avatar (optional)</label>
                      <div class="mb-2">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                          <label class="btn btn-sm btn-outline-primary active">
                            <input type="radio" name="editClientAvatarInputType{{ $client->id }}" value="file" checked onclick="toggleEditClientAvatarInput({{ $client->id }}, 'file')"> Upload File
                          </label>
                          <label class="btn btn-sm btn-outline-primary">
                            <input type="radio" name="editClientAvatarInputType{{ $client->id }}" value="url" onclick="toggleEditClientAvatarInput({{ $client->id }}, 'url')"> Enter URL
                          </label>
                        </div>
                      </div>
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
                        <input type="file" name="avatar" id="avatarInput{{ $client->id }}" class="form-control mb-2" accept="image/*" onchange="previewAvatar({{ $client->id }})">
                        <input type="text" name="avatar_url" id="avatarUrl{{ $client->id }}" class="form-control" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewClientAvatarUrl({{ $client->id }})">
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-update" onclick="showEditClientConfirmModal({{ $client->id }})">
                      <i class="mdi mdi-pencil"></i> Update
                    </button>
                  </div>
                </form>
              </div>

              <!-- Confirmation Overlay -->
              <div id="editClientConfirmOverlay{{ $client->id }}" class="confirm-overlay" style="display: none;">
                <div class="confirm-overlay-content">
                  <div class="confirm-overlay-header">
                    <i class="mdi mdi-pencil-outline"></i>
                    <h5>Confirm Update</h5>
                    <button type="button" class="close" onclick="backToEditClientForm({{ $client->id }})">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="confirm-overlay-body">
                    <p class="mb-3">Are you sure you want to update this client?</p>
                    <div class="confirm-details">
                      <div class="confirm-row">
                        <span class="confirm-label">Name:</span>
                        <span class="confirm-value" id="confirmEditClientName{{ $client->id }}"></span>
                      </div>
                      <div class="confirm-row">
                        <span class="confirm-label">Plan:</span>
                        <span class="confirm-value" id="confirmEditClientPlan{{ $client->id }}"></span>
                      </div>
                      <div class="confirm-row">
                        <span class="confirm-label">Duration:</span>
                        <span class="confirm-value" id="confirmEditClientDuration{{ $client->id }}"></span>
                      </div>
                    </div>
                  </div>
                  <div class="confirm-overlay-footer">
                    <button type="button" class="btn btn-cancel" onclick="backToEditClientForm({{ $client->id }})">Cancel</button>
                    <button type="button" class="btn btn-update" onclick="submitEditClientForm({{ $client->id }})">
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
              <form id="bulkDeleteForm" action="{{ route('clients.bulk-delete') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" onclick="bulkDelete()" class="btn btn-sm btn-delete-selected">
                  <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
              </form>
            </div>
            <div class="col-md-6 col-sm-12">
              {{ $clients->links('vendor.pagination.custom') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="addClientFormContent">
        <div class="modal-header">
          <h5 class="modal-title" id="addClientModalLabel">Add Client</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addClientForm">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" id="newClientName" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-group">
              <label>Age</label>
              <input type="number" name="age" id="newClientAge" class="form-control" placeholder="24" min="1" max="120" required>
            </div>

            <div class="form-group">
              <label>Contact Number</label>
              <input type="text" name="contact" id="newClientContact" class="form-control contact-input" placeholder="09123456789" required maxlength="13" pattern="^(\+63|0)[0-9]{10}$" title="Enter 11 digits (e.g., 09123456789) or +63 format (e.g., +639123456789)" oninput="validateContactInput(this)">
              <small class="form-text text-muted">Format: 09XX-XXX-XXXX or +639XX-XXX-XXXX</small>              <div id="newClientContactError" class="invalid-feedback" style="display: none;"></div>            </div>

            <div class="form-group">
              <label>Membership Plan</label>
              <select name="plan_type" id="newClientPlan" class="form-control" required>
                <option value="">Select Plan</option>
                <option value="Monthly">Monthly</option>
                <option value="Session">Session</option>
              </select>
            </div>

            <div class="form-group">
              <label>Start Date</label>
              <input type="date" name="start_date" id="newClientStartDate" class="form-control" onchange="calculateClientEndDate()" required>
            </div>

            <div class="form-group">
              <label>End Date</label>
              <input type="date" name="due_date" id="newClientEndDate" class="form-control" readonly>
            </div>

            <div class="form-group">
              <label>Avatar (optional)</label>
              <div class="mb-2">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-sm btn-outline-primary active">
                    <input type="radio" name="clientAvatarInputType" value="file" checked onclick="toggleClientAvatarInput('file')"> Upload File
                  </label>
                  <label class="btn btn-sm btn-outline-primary">
                    <input type="radio" name="clientAvatarInputType" value="url" onclick="toggleClientAvatarInput('url')"> Enter URL
                  </label>
                </div>
              </div>
              <input type="file" name="avatar" id="newClientAvatar" class="form-control" accept="image/*" onchange="previewNewClientAvatar()">
              <input type="text" name="avatar_url" id="newClientAvatarUrl" class="form-control" placeholder="https://example.com/avatar.jpg" style="display: none;" oninput="previewNewClientAvatar()">
              <div id="newClientAvatarPreview" class="mt-3 text-center"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showClientConfirmModal()">Submit</button>
        </div>
      </div>

      <!-- Confirmation Overlay -->
      <div id="addClientConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-check-circle-outline"></i>
            <h5>Confirm Client</h5>
            <button type="button" class="close" onclick="backToClientAddForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to add this client?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Name:</span>
                <span class="confirm-value" id="confirmClientNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmClientPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmClientDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToClientAddForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitClientForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Renew Subscription Modal -->
<div class="modal fade" id="renewClientModal" tabindex="-1" role="dialog" aria-labelledby="renewClientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="position: relative;">
      <!-- Main Form Content -->
      <div id="renewClientFormContent">
        <div class="modal-header">
          <h5 class="modal-title" id="renewClientModalLabel">Renew Subscription</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="renewClientForm">
            <input type="hidden" id="renewClientId" name="client_id">
            <input type="hidden" id="renewClientName" name="client_name">
            <input type="hidden" id="renewClientPlanType" name="plan_type">

            <div class="form-group">
              <label>Client Name</label>
              <input type="text" class="form-control" id="renewClientNameDisplay" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
            </div>

            <div class="form-group">
              <label>Current Plan</label>
              <input type="text" class="form-control" id="renewClientPlanTypeDisplay" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
            </div>

            <div class="form-group">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="renewClientStartDate" class="form-control" required onchange="calculateRenewClientEndDate()">
            </div>

            <div class="form-group">
              <label>End Date <span class="text-danger">*</span></label>
              <input type="date" name="due_date" id="renewClientEndDate" class="form-control" readonly style="background-color: #191C24; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;">
            </div>

            <div class="alert alert-info" style="background-color: rgba(66, 165, 245, 0.1); border: 1px solid rgba(66, 165, 245, 0.3); color: #42A5F5;">
              <i class="mdi mdi-information"></i> The end date will be automatically calculated based on the subscription plan type.
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-update" onclick="showRenewClientConfirmModal()">Submit</button>
        </div>
      </div>

      <!-- Confirmation Overlay -->
      <div id="renewClientConfirmOverlay" class="confirm-overlay" style="display: none;">
        <div class="confirm-overlay-content">
          <div class="confirm-overlay-header">
            <i class="mdi mdi-refresh"></i>
            <h5>Confirm Renewal</h5>
            <button type="button" class="close" onclick="backToRenewClientForm()">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="confirm-overlay-body">
            <p class="mb-3">Are you sure you want to renew this subscription?</p>
            <div class="confirm-details">
              <div class="confirm-row">
                <span class="confirm-label">Client:</span>
                <span class="confirm-value" id="confirmRenewClientNameText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Plan:</span>
                <span class="confirm-value" id="confirmRenewClientPlanText"></span>
              </div>
              <div class="confirm-row">
                <span class="confirm-label">Duration:</span>
                <span class="confirm-value" id="confirmRenewClientDurationText"></span>
              </div>
            </div>
          </div>
          <div class="confirm-overlay-footer">
            <button type="button" class="btn btn-cancel" onclick="backToRenewClientForm()">Cancel</button>
            <button type="button" class="btn btn-update" onclick="submitRenewClientForm()">
              <i class="mdi mdi-check"></i> Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteClientConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteClientConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteClientConfirmModalLabel">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger" style="background-color: rgba(239, 83, 80, 0.1); border: 1px solid rgba(239, 83, 80, 0.3); color: #EF5350;">
          <i class="mdi mdi-alert-circle"></i> Are you sure you want to delete this client? This action cannot be undone.
        </div>

        <div class="form-group">
          <label>Client Name</label>
          <div class="form-control" id="deleteClientName" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Subscription Plan</label>
          <div class="form-control" id="deleteClientPlan" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <div class="form-group">
          <label>Status</label>
          <div class="form-control" id="deleteClientStatus" style="background-color: #282A36; border: 1px solid rgba(255, 255, 255, 0.1); color: #ffffff;"></div>
        </div>

        <form id="deleteClientForm" method="POST" style="display: none;">
          @csrf
          @method('DELETE')
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmDeleteClient()">Delete Client</button>
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
          Are you sure you want to delete <strong><span id="bulkDeleteCount">0</span> selected client(s)</strong>?
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
<script src="{{ asset('js/pages/clients.js') }}?v={{ time() }}"></script>
<script>
  // Initialize clients page with Laravel data
  document.addEventListener('DOMContentLoaded', function() {
    ClientsPage.init({
      csrfToken: '{{ csrf_token() }}',
      storeUrl: '{{ route("clients.store") }}'
    });
    
    // Setup midnight auto-refresh for KPIs
    ClientsPage.setupMidnightRefresh();
    
    // Setup client search enter key (uses different IDs than memberships)
    const searchInput = document.getElementById('searchInputClients');
    const searchForm = document.getElementById('searchFormClients');
    if (searchInput && searchForm) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchForm.submit();
        }
      });
    }
  });

  // Global function wrappers for onclick handlers in HTML
  function toggleClientAvatarInput(type) {
    ClientsPage.toggleClientAvatarInput(type);
  }

  function calculateClientEndDate() {
    ClientsPage.calculateClientEndDate();
  }

  function previewNewClientAvatar() {
    ClientsPage.previewNewClientAvatar();
  }

  function showClientConfirmModal() {
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('newClientContact');
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
    
    ClientsPage.showClientConfirmModal();
  }

  function backToClientAddForm() {
    ClientsPage.backToClientAddForm();
  }

  function submitClientForm() {
    ClientsPage.submitClientForm();
  }

  function toggleEditClientAvatarInput(clientId, type) {
    ClientsPage.toggleEditClientAvatarInput(clientId, type);
  }

  function calculateEditClientEndDate(clientId) {
    ClientsPage.calculateEditClientEndDate(clientId);
  }

  function previewAvatar(clientId) {
    ClientsPage.previewAvatar(clientId);
  }

  function previewClientAvatarUrl(clientId) {
    ClientsPage.previewClientAvatarUrl(clientId);
  }

  function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.client-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
      ToastUtils.showWarning('Please select at least one client to delete.', 'Warning');
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
    const checkedBoxes = document.querySelectorAll('.client-checkbox:checked');
    
    // Remove any existing hidden inputs
    form.querySelectorAll('input[name="client_ids[]"]').forEach(el => el.remove());
    
    // Add selected IDs to form
    checkedBoxes.forEach(checkbox => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'client_ids[]';
      input.value = checkbox.value;
      form.appendChild(input);
    });
    
    // Close modal and submit form
    $('#bulkDeleteConfirmModal').modal('hide');
    form.submit();
  }

  function openRenewClientModal(clientId, clientName, planType, startDate, dueDate) {
    ClientsPage.openRenewClientModal(clientId, clientName, planType, startDate, dueDate);
  }

  function calculateRenewClientEndDate() {
    ClientsPage.calculateRenewClientEndDate();
  }

  function showRenewClientConfirmModal() {
    ClientsPage.showRenewClientConfirmModal();
  }

  function backToRenewClientForm() {
    ClientsPage.backToRenewClientForm();
  }

  function submitRenewClientForm() {
    ClientsPage.submitRenewClientForm();
  }

  function showEditClientConfirmModal(clientId) {
    // Validate contact number before showing confirmation
    const contactInput = document.getElementById('editClientContact' + clientId);
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
    
    ClientsPage.showEditClientConfirmModal(clientId);
  }

  function backToEditClientForm(clientId) {
    ClientsPage.backToEditClientForm(clientId);
  }

  function submitEditClientForm(clientId) {
    ClientsPage.submitEditClientForm(clientId);
  }

  function openDeleteClientModal(clientId, clientName, planType, status) {
    ClientsPage.openDeleteClientModal(clientId, clientName, planType, status);
  }

  function confirmDeleteClient() {
    ClientsPage.confirmDeleteClient();
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