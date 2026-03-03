<!-- Trainer Table -->
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <!-- Table Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">List of Trainers</h4>
          <div class="d-flex align-items-center">
            <form action="{{ route('UserAndAdmin.TrainerManagement') }}" method="GET" class="d-flex align-items-center" id="searchFormTrainer">
              <input type="text" name="search" class="form-control form-control-sm mr-2" 
                placeholder="Search by name, specialization, contact, or address..." 
                value="{{ request('search') }}" style="width: 100%; max-width: 450px;" id="searchInputTrainer">
              @if(request('search'))
                <a href="{{ route('UserAndAdmin.TrainerManagement') }}" class="btn btn-sm btn-outline-secondary">
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
            Showing {{ $trainers->total() }} result(s) for "<strong>{{ request('search') }}</strong>"
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
                <th>Trainer ID#</th>
                <th>Full Name</th>
                <th>Specialization</th>
                <th>Contact #</th>
                <th>Emergency Contact #</th>
                <th>Address</th>
                <th>Date Added</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($trainers as $trainer)
              <tr>
                <td>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input trainer-checkbox" name="trainer_ids[]" value="{{ $trainer->id }}">
                    </label>
                  </div>
                </td>
                <td>{{ str_pad($trainer->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-initial mr-2">{{ strtoupper(substr($trainer->full_name, 0, 1)) }}</div>
                    <span>{{ $trainer->full_name }}</span>
                  </div>
                </td>
                <td>{{ $trainer->specialization ?? '—' }}</td>
                <td>{{ $trainer->contact_number ?? '—' }}</td>
                <td>{{ $trainer->emergency_contact ?? '—' }}</td>
                <td>{{ $trainer->address ? \Illuminate\Support\Str::limit($trainer->address, 30) : '—' }}</td>
                <td>{{ $trainer->created_at->format('d M Y') }}</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown" 
                      data-offset="0,2" data-flip="false" data-boundary="viewport" 
                      aria-haspopup="true" aria-expanded="false">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editTrainerModal{{ $trainer->id }}">
                        <i class="mdi mdi-pencil"></i> Edit Details
                      </button>
                      <button type="button" class="dropdown-item text-danger" 
                        onclick="openDeleteTrainerModal({{ $trainer->id }}, '{{ addslashes($trainer->full_name) }}')">
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
                      <p class="mt-3">No trainers found matching "{{ request('search') }}". <a href="{{ route('UserAndAdmin.TrainerManagement') }}" class="text-info">Clear search</a></p>
                    @else
                      <p class="mt-3">No trainers found. Click "Add New Trainer" to create one.</p>
                    @endif
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @foreach($trainers as $trainer)
        @include('UserAndAdmin.trainer-partials.modals._edit', ['trainer' => $trainer])
        @endforeach

        <!-- Pagination -->
        <div class="table-footer">
          <form id="bulkDeleteTrainerForm" action="{{ route('trainers.bulk-delete') }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button" onclick="bulkDeleteTrainers()" class="btn btn-sm btn-delete-selected">
              <i class="mdi mdi-delete"></i> Delete Selected (<span id="selectedTrainerCount">0</span>)
            </button>
          </form>
          {{ $trainers->links('vendor.pagination.custom') }}
        </div>
      </div>
    </div>
  </div>
</div>
