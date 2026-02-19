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
                <div class="dropdown-menu dropdown-menu-right filter-accordion" aria-labelledby="filterDropdown">
                  <div class="filter-header">
                    <span class="filter-title">Filter By</span>
                    <a href="javascript:void(0)" class="filter-clear-all" onclick="MembershipsPage.clearAllFilters()">
                      Clear All
                    </a>
                  </div>
                  
                  <!-- Status Filter -->
                  <div class="filter-section">
                    <div class="filter-section-header" onclick="MembershipsPage.toggleFilterSection(this, event)">
                      <div class="filter-section-title">
                        <i class="mdi mdi-circle-outline"></i>
                        <span>Status</span>
                      </div>
                      <i class="mdi mdi-chevron-down filter-chevron"></i>
                    </div>
                    <div class="filter-section-content">
                      <a class="filter-option" href="javascript:void(0)" onclick="MembershipsPage.applyFilter('status', 'active')">
                        <i class="mdi mdi-check-circle text-success"></i> Active
                      </a>
                      <a class="filter-option" href="javascript:void(0)" onclick="MembershipsPage.applyFilter('status', 'expired')">
                        <i class="mdi mdi-close-circle text-danger"></i> Expired
                      </a>
                      <a class="filter-option" href="javascript:void(0)" onclick="MembershipsPage.applyFilter('status', 'due_soon')">
                        <i class="mdi mdi-clock-alert text-warning"></i> Due Soon
                      </a>
                    </div>
                  </div>
                  
                  <!-- Plan Type Filter -->
                  <div class="filter-section">
                    <div class="filter-section-header" onclick="MembershipsPage.toggleFilterSection(this, event)">
                      <div class="filter-section-title">
                        <i class="mdi mdi-card-account-details-outline"></i>
                        <span>Plan Type</span>
                      </div>
                      <i class="mdi mdi-chevron-down filter-chevron"></i>
                    </div>
                    <div class="filter-section-content">
                      @foreach($membershipPlans as $plan)
                      <a class="filter-option" href="javascript:void(0)" onclick="MembershipsPage.applyFilter('plan', '{{ $plan->plan_key }}')">
                        <i class="mdi mdi-label-outline"></i> {{ $plan->plan_name }}
                      </a>
                      @endforeach
                    </div>
                  </div>
                  
                  <!-- Gender Filter -->
                  <div class="filter-section">
                    <div class="filter-section-header" onclick="MembershipsPage.toggleFilterSection(this, event)">
                      <div class="filter-section-title">
                        <i class="mdi mdi-human-male-female"></i>
                        <span>Gender</span>
                      </div>
                      <i class="mdi mdi-chevron-down filter-chevron"></i>
                    </div>
                    <div class="filter-section-content">
                      <a class="filter-option" href="javascript:void(0)" onclick="MembershipsPage.applyFilter('gender', 'Male')">
                        <i class="mdi mdi-human-male text-info"></i> Male
                      </a>
                      <a class="filter-option" href="javascript:void(0)" onclick="MembershipsPage.applyFilter('gender', 'Female')">
                        <i class="mdi mdi-human-female text-info"></i> Female
                      </a>
                    </div>
                  </div>
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
                  data-expiring="{{ $membership->status == 'Due soon' ? 'yes' : 'no' }}"
                  data-plan-type="{{ $membership->plan_type }}"
                  data-gender="{{ $membership->sex ?? '' }}">
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
                <td>{{ $membership->gymPlan->plan_name ?? $membership->plan_type }}</td>
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
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $membership->id }}">
                        <i class="mdi mdi-eye mr-2"></i> View Details
                      </button>
                      <button type="button" class="dropdown-item text-success" 
                        onclick="openRenewModal({{ $membership->id }}, '{{ $membership->name }}', '{{ $membership->gymPlan->plan_name ?? $membership->plan_type }}', '{{ $membership->plan_type }}', {{ $membership->gymPlan->duration_days ?? 30 }}, '{{ $membership->start_date->format('Y-m-d') }}', '{{ $membership->due_date->format('Y-m-d') }}')">
                        <i class="mdi mdi-refresh mr-2"></i> Renew Subscription
                      </button>
                      <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteModal({{ $membership->id }}, '{{ $membership->name }}', '{{ $membership->gymPlan->plan_name ?? $membership->plan_type }}', '{{ $membership->status }}')">
                        <i class="mdi mdi-delete mr-2"></i> Delete
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="11" class="text-center py-5">
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
          @include('memberships.partials.modals._edit', ['membership' => $membership])
        @endforeach

        <!-- Pagination -->
        <div class="table-footer">
          <form id="bulkDeleteForm" action="{{ route('memberships.bulk-delete') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button" onclick="bulkDelete()" class="btn btn-sm btn-delete-selected">
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
          </form>
          {{ $memberships->links('vendor.pagination.custom') }}
        </div>
      </div>
    </div>
  </div>
</div>
