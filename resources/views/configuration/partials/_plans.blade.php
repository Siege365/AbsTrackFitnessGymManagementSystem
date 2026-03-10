<!-- ========================================== -->
<!-- MANAGE PLANS SECTION                       -->
<!-- ========================================== -->
<div class="config-section active" id="manageSection">

  <!-- ── Membership Plans ── -->
  <div class="row">
    <div class="col-12 grid-margin">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h4 class="card-title mb-1">
                Membership Plans
              </h4>
              <p class="mb-0" style="font-size: 0.9375rem; color: #a3a4a7;">
                Manage membership plans offered by the gym. Edit plan details, set pricing, and control availability to members.
              </p>
            </div>
            <div class="d-flex align-items-center">
              <!-- Search Bar -->
              <div class="search-wrapper mr-2">
                <input type="text" 
                      id="membershipSearchInput" 
                      class="form-control form-control-sm" 
                      placeholder="Search membership plans..." 
                      style="width: 100%; max-width: 450px;">
              </div>
              <button class="btn btn-page-action" onclick="openAddPlanModal('membership')">
                <i class="mdi mdi-plus"></i> Add Plan
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover" id="membershipPlansTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Plan Name</th>
                  <th>Price (₱)</th>
                  <th>Duration</th>
                  <th>Duration Label</th>
                  <th>Badge</th>
                  <th>Special</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($membershipPlans as $index => $plan)
                <tr data-plan-id="{{ $plan->id }}">
                  <td>{{ $index + 1 }}</td>
                  <td>
                    <strong>{{ $plan->plan_name }}</strong>
                    @if($plan->description)
                      <br><small style="color: #a3a4a7;">{{ $plan->description }}</small>
                    @endif
                  </td>
                  <td class="text-warning font-weight-bold">₱{{ number_format($plan->price, 2) }}</td>
                  <td>{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'day' : 'days' }}</td>
                  <td>{{ $plan->duration_label ?? '—' }}</td>
                  <td>
                    @if($plan->badge_text)
                      @php
                        $bc = $plan->badge_color ?? '#6c757d';
                        $r = hexdec(substr($bc, 1, 2));
                        $g = hexdec(substr($bc, 3, 2));
                        $b = hexdec(substr($bc, 5, 2));
                      @endphp
                      <span class="badge badge-config" style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.2); color: {{ $bc }};">{{ $plan->badge_text }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($plan->requires_student)
                      <span class="badge badge-special"><i class="mdi mdi-school"></i> Student</span>
                    @elseif($plan->requires_buddy)
                      <span class="badge badge-special badge-special-warning"><i class="mdi mdi-account-multiple"></i> Buddy ({{ $plan->buddy_count }})</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge {{ $plan->is_active ? 'badge-active' : 'badge-inactive' }}">
                      {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>
                    <div class="dropdown">
                            <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown"
                              data-display="static" data-boundary="window"
                              aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <button type="button" class="dropdown-item edit-plan-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan='@json($plan)'>
                          <i class="mdi mdi-pencil mr-2"></i> Edit Plan
                        </button>
                        @if($plan->is_active)
                        <button type="button" class="dropdown-item text-danger toggle-plan-status-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-plan-category="Membership"
                                data-action="disable">
                          <i class="mdi mdi-close-circle mr-2"></i> Disable Plan
                        </button>
                        @else
                        <button type="button" class="dropdown-item text-success toggle-plan-status-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-plan-category="Membership"
                                data-action="enable">
                          <i class="mdi mdi-check-circle mr-2"></i> Enable Plan
                        </button>
                        @endif
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item text-danger delete-plan-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-plan-category="Membership"
                                data-plan-price="₱{{ number_format($plan->price, 2) }}"
                                data-plan-status="{{ $plan->is_active ? 'Active' : 'Inactive' }}">
                          <i class="mdi mdi-delete mr-2"></i> Delete Plan
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">No membership plans configured yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          @if($membershipPlans->hasPages())
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Showing {{ $membershipPlans->firstItem() ?? 0 }} to {{ $membershipPlans->lastItem() ?? 0 }} of {{ $membershipPlans->total() }} plans
            </div>
            <div>
              {{ $membershipPlans->links() }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- ── Personal Training Plans ── -->
  <div class="row">
    <div class="col-12 grid-margin">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h4 class="card-title mb-1">
                Personal Training Rates
              </h4>
              <p class="mb-0" style="font-size: 0.9375rem; color: #a3a4a7;">
                Manage personal training plans offered by the gym. Edit plan details, set pricing, and control availability to members.
              </p>
            </div>
            <div class="d-flex align-items-center">
              <!-- Search Bar -->
              <div class="search-wrapper mr-2">
                <input type="text" 
                      id="ptSearchInput" 
                      class="form-control form-control-sm" 
                      placeholder="Search PT plans..." 
                      style="width: 100%; max-width: 450px;">
              </div>
              <button class="btn btn-page-action" onclick="openAddPlanModal('personal_training')">
                <i class="mdi mdi-plus"></i> Add Plan
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover" id="ptPlansTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Plan Name</th>
                  <th>Price (₱)</th>
                  <th>Duration</th>
                  <th>Type Label</th>
                  <th>Badge</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($ptPlans as $index => $plan)
                <tr data-plan-id="{{ $plan->id }}">
                  <td>{{ $index + 1 }}</td>
                  <td>
                    <strong>{{ $plan->plan_name }}</strong>
                    @if($plan->description)
                      <br><small style="color: #a3a4a7;">{{ $plan->description }}</small>
                    @endif
                  </td>
                  <td class="text-warning font-weight-bold">₱{{ number_format($plan->price, 2) }}</td>
                  <td>{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'day' : 'days' }}</td>
                  <td>{{ $plan->duration_label ?? '—' }}</td>
                  <td>
                    @if($plan->badge_text)
                      @php
                        $bc = $plan->badge_color ?? '#6c757d';
                        $r = hexdec(substr($bc, 1, 2));
                        $g = hexdec(substr($bc, 3, 2));
                        $b = hexdec(substr($bc, 5, 2));
                      @endphp
                      <span class="badge badge-config" style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.2); color: {{ $bc }};">{{ $plan->badge_text }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge {{ $plan->is_active ? 'badge-active' : 'badge-inactive' }}">
                      {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>
                    <div class="dropdown">
                            <button class="btn btn-sm btn-action" type="button" data-toggle="dropdown"
                              data-display="static" data-boundary="window"
                              aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <button type="button" class="dropdown-item edit-plan-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan='@json($plan)'>
                          <i class="mdi mdi-pencil mr-2"></i> Edit Plan
                        </button>
                        @if($plan->is_active)
                        <button type="button" class="dropdown-item text-danger toggle-plan-status-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-plan-category="Personal Training"
                                data-action="disable">
                          <i class="mdi mdi-close-circle mr-2"></i> Disable Plan
                        </button>
                        @else
                        <button type="button" class="dropdown-item text-success toggle-plan-status-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-plan-category="Personal Training"
                                data-action="enable">
                          <i class="mdi mdi-check-circle mr-2"></i> Enable Plan
                        </button>
                        @endif
                        <div class="dropdown-divider"></div>
                        <button type="button" class="dropdown-item text-danger delete-plan-btn"
                                data-plan-id="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-plan-category="Personal Training"
                                data-plan-price="₱{{ number_format($plan->price, 2) }}"
                                data-plan-status="{{ $plan->is_active ? 'Active' : 'Inactive' }}">
                          <i class="mdi mdi-delete mr-2"></i> Delete Plan
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No personal training plans configured yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          @if($ptPlans->hasPages())
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
              Showing {{ $ptPlans->firstItem() ?? 0 }} to {{ $ptPlans->lastItem() ?? 0 }} of {{ $ptPlans->total() }} plans
            </div>
            <div>
              {{ $ptPlans->links() }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
