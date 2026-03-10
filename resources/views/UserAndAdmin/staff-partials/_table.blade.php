<!-- Staff Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <!-- Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">List of Staff</h4>
          <div class="d-flex align-items-center">
            <form action="{{ route('UserAndAdmin.UserManagement') }}" method="GET" class="d-flex align-items-center" id="searchFormStaff">
              <input type="text" name="search" class="form-control form-control-sm mr-2" 
                placeholder="Search by name, email, contact, or address..." 
                value="{{ request('search') }}" style="width: 100%; max-width: 450px;" id="searchInputStaff">
              @if(request('search'))
                <a href="{{ route('UserAndAdmin.UserManagement') }}" class="btn btn-sm btn-outline-secondary">
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
                <th>User ID#</th>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Contact #</th>
                <th>Emergency Contact #</th>
                <th>Address</th>
                <th>Date Added</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($staff as $member)
              <tr>
                <td>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input staff-checkbox" name="staff_ids[]" value="{{ $member->id }}">
                    </label>
                  </div>
                </td>
                <td>{{ str_pad($member->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-initial mr-2">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                    <span>{{ $member->name }}</span>
                  </div>
                </td>
                <td>{{ $member->email }}</td>
                <td>{{ $member->contact_number ?? '—' }}</td>
                <td>{{ $member->emergency_contact ?? '—' }}</td>
                <td>{{ $member->address ? \Illuminate\Support\Str::limit($member->address, 30) : '—' }}</td>
                <td>{{ $member->created_at->format('d M Y') }}</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" 
                      data-display="static" data-boundary="window" 
                      aria-haspopup="true" aria-expanded="false">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" onclick="openStaffViewModal({{ $member->id }})">
                        <i class="mdi mdi-eye"></i> View Details
                      </button>
                      <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteStaffModal({{ $member->id }}, '{{ addslashes($member->name) }}', '{{ $member->email }}')">
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
                      <p class="mt-3">No staff found matching "{{ request('search') }}". <a href="{{ route('UserAndAdmin.UserManagement') }}" class="text-info">Clear search</a></p>
                    @else
                      <p class="mt-3">No staff accounts found. Click "Add New Staff" to create one.</p>
                    @endif
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @foreach($staff as $member)
        @include('UserAndAdmin.staff-partials.modals._edit', ['member' => $member])
        @endforeach

        <!-- Pagination -->
        <div class="table-footer">
          <form id="bulkDeleteForm" action="{{ route('staff.bulk-delete') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button" onclick="bulkDeleteStaff()" class="btn btn-sm btn-delete-selected">
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedCount">0</span>)
            </button>
          </form>
          {{ $staff->links('vendor.pagination.custom') }}
        </div>
      </div>
    </div>
  </div>
</div>
