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
                <div class="dropdown-menu dropdown-menu-right filter-accordion" aria-labelledby="filterDropdownClients">
                  <div class="filter-header">
                    <span class="filter-title">Filter By</span>
                    <a href="javascript:void(0)" class="filter-clear-all" onclick="ClientsPage.clearAllFilters()">
                      Clear All
                    </a>
                  </div>
                  
                  <!-- Status Filter -->
                  <div class="filter-section">
                    <div class="filter-section-header" onclick="ClientsPage.toggleFilterSection(this, event)">
                      <div class="filter-section-title">
                        <i class="mdi mdi-circle-outline"></i>
                        <span>Status</span>
                      </div>
                      <i class="mdi mdi-chevron-down filter-chevron"></i>
                    </div>
                    <div class="filter-section-content">
                      <a class="filter-option" href="javascript:void(0)" onclick="ClientsPage.applyFilter('status', 'active')">
                        <i class="mdi mdi-check-circle text-success"></i> Active
                      </a>
                      <a class="filter-option" href="javascript:void(0)" onclick="ClientsPage.applyFilter('status', 'expired')">
                        <i class="mdi mdi-close-circle text-danger"></i> Expired
                      </a>
                      <a class="filter-option" href="javascript:void(0)" onclick="ClientsPage.applyFilter('status', 'due_soon')">
                        <i class="mdi mdi-clock-alert text-warning"></i> Due Soon
                      </a>
                    </div>
                  </div>
                  
                  <!-- Plan Type Filter -->
                  <div class="filter-section">
                    <div class="filter-section-header" onclick="ClientsPage.toggleFilterSection(this, event)">
                      <div class="filter-section-title">
                        <i class="mdi mdi-card-account-details-outline"></i>
                        <span>Plan Type</span>
                      </div>
                      <i class="mdi mdi-chevron-down filter-chevron"></i>
                    </div>
                    <div class="filter-section-content">
                      @foreach($ptPlans as $plan)
                      <a class="filter-option" href="javascript:void(0)" onclick="ClientsPage.applyFilter('plan', '{{ $plan->plan_key }}')">
                        <i class="mdi mdi-label-outline"></i> {{ $plan->plan_name }}
                      </a>
                      @endforeach
                    </div>
                  </div>
                  
                  <!-- Gender Filter -->
                  <div class="filter-section">
                    <div class="filter-section-header" onclick="ClientsPage.toggleFilterSection(this, event)">
                      <div class="filter-section-title">
                        <i class="mdi mdi-human-male-female"></i>
                        <span>Gender</span>
                      </div>
                      <i class="mdi mdi-chevron-down filter-chevron"></i>
                    </div>
                    <div class="filter-section-content">
                      <a class="filter-option" href="javascript:void(0)" onclick="ClientsPage.applyFilter('gender', 'Male')">
                        <i class="mdi mdi-human-male text-info"></i> Male
                      </a>
                      <a class="filter-option" href="javascript:void(0)" onclick="ClientsPage.applyFilter('gender', 'Female')">
                        <i class="mdi mdi-human-female text-info"></i> Female
                      </a>
                    </div>
                  </div>
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
                  data-expiring="{{ $client->status == 'Due soon' ? 'yes' : 'no' }}"
                  data-plan-type="{{ $client->plan_type }}"
                  data-gender="{{ $client->sex ?? '' }}">
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
                <td>{{ $client->gymPlan->plan_name ?? $client->plan_type }}</td>
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
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#viewModal{{ $client->id }}">
                        <i class="mdi mdi-eye mr-2"></i> View
                      </button>
                      <button type="button" class="dropdown-item text-success" 
                        onclick="openRenewClientModal({{ $client->id }}, '{{ $client->name }}', '{{ $client->gymPlan->plan_name ?? $client->plan_type }}', '{{ $client->plan_type }}', {{ $client->gymPlan->duration_days ?? 30 }}, '{{ $client->start_date->format('Y-m-d') }}', '{{ $client->due_date->format('Y-m-d') }}')">
                        <i class="mdi mdi-refresh mr-2"></i> Renew Subscription
                      </button>
                      <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteClientModal({{ $client->id }}, '{{ $client->name }}', '{{ $client->gymPlan->plan_name ?? $client->plan_type }}', '{{ $client->status }}')">
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
        @include('clients.partials.modals._edit', ['client' => $client])
        @endforeach

        <!-- Pagination -->
        <div class="table-footer">
          <form id="bulkDeleteForm" action="{{ route('clients.bulk-delete') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button" onclick="bulkDelete()" class="btn btn-sm btn-delete-selected">
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
          </form>
          {{ $clients->links('vendor.pagination.custom') }}
        </div>
      </div>
    </div>
  </div>
</div>
