@extends('layouts.admin')

@section('title', 'Settings')

@push('styles')
@vite(['resources/css/configuration.css'])
@endpush

@section('content')

  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Settings</h2>
              <p class="page-header-subtitle">Manage membership plans, personal training rates & gym settings</p>
          </div>
      </div>
  </div>

  <!-- ========================================== -->
  <!-- SECTION TOGGLE: Manage Plans / Client Rates -->
  <!-- ========================================== -->
  <div class="config-toggle-container">
    <button class="config-toggle-btn active" data-section="manage">
      <i class="mdi mdi-cog-outline"></i>
      <span>Manage Plans</span>
    </button>
    <button class="config-toggle-btn" data-section="preview">
      <i class="mdi mdi-eye-outline"></i>
      <span>Client Rate Preview</span>
    </button>
  </div>

  <!-- ========================================== -->
  <!-- MANAGE PLANS SECTION                       -->
  <!-- ========================================== -->
  <div class="config-section active" id="manageSection">

    <!-- ── Membership Plans ── -->
    <div class="card config-card">
      <div class="card-body">
        <div class="config-section-header">
          <div class="config-section-title">
            <i class="mdi mdi-card-account-details-outline"></i>
            <h3>Membership Plans</h3>
          </div>
          <button class="btn btn-add-plan" onclick="openAddPlanModal('membership')">
            <i class="mdi mdi-plus"></i> Add Plan
          </button>
        </div>

        <div class="config-table-wrapper">
          <table class="table config-table" id="membershipPlansTable">
            <thead>
              <tr>
                <th style="width: 40px;">#</th>
                <th>Plan Name</th>
                <th>Price (₱)</th>
                <th>Duration</th>
                <th>Duration Label</th>
                <th>Badge</th>
                <th>Special</th>
                <th>Status</th>
                <th style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($membershipPlans as $index => $plan)
              <tr data-plan-id="{{ $plan->id }}">
                <td>{{ $index + 1 }}</td>
                <td class="plan-name-cell">
                  <strong>{{ $plan->plan_name }}</strong>
                  @if($plan->description)
                    <small class="plan-description">{{ $plan->description }}</small>
                  @endif
                </td>
                <td class="plan-price-cell">₱{{ number_format($plan->price, 2) }}</td>
                <td>{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'day' : 'days' }}</td>
                <td>{{ $plan->duration_label ?? '—' }}</td>
                <td>
                  @if($plan->badge_text)
                    <span class="config-badge badge-{{ $plan->badge_color ?? 'secondary' }}">{{ $plan->badge_text }}</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  @if($plan->requires_student)
                    <span class="special-tag tag-student"><i class="mdi mdi-school"></i> Student</span>
                  @elseif($plan->requires_buddy)
                    <span class="special-tag tag-buddy"><i class="mdi mdi-account-multiple"></i> Buddy ({{ $plan->buddy_count }})</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  <span class="status-indicator {{ $plan->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button class="btn-action-icon btn-edit" onclick="openEditPlanModal({{ $plan->id }}, {{ json_encode($plan) }})" title="Edit">
                      <i class="mdi mdi-pencil"></i>
                    </button>
                    <button class="btn-action-icon btn-delete" onclick="confirmDeletePlan({{ $plan->id }}, '{{ addslashes($plan->plan_name) }}')" title="Delete">
                      <i class="mdi mdi-delete"></i>
                    </button>
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
      </div>
    </div>

    <!-- ── Personal Training Plans ── -->
    <div class="card config-card">
      <div class="card-body">
        <div class="config-section-header">
          <div class="config-section-title">
            <i class="mdi mdi-dumbbell"></i>
            <h3>Personal Training Rates</h3>
          </div>
          <button class="btn btn-add-plan" onclick="openAddPlanModal('personal_training')">
            <i class="mdi mdi-plus"></i> Add Plan
          </button>
        </div>

        <div class="config-table-wrapper">
          <table class="table config-table" id="ptPlansTable">
            <thead>
              <tr>
                <th style="width: 40px;">#</th>
                <th>Plan Name</th>
                <th>Price (₱)</th>
                <th>Duration</th>
                <th>Type Label</th>
                <th>Badge</th>
                <th>Status</th>
                <th style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ptPlans as $index => $plan)
              <tr data-plan-id="{{ $plan->id }}">
                <td>{{ $index + 1 }}</td>
                <td class="plan-name-cell">
                  <strong>{{ $plan->plan_name }}</strong>
                  @if($plan->description)
                    <small class="plan-description">{{ $plan->description }}</small>
                  @endif
                </td>
                <td class="plan-price-cell">₱{{ number_format($plan->price, 2) }}</td>
                <td>{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'day' : 'days' }}</td>
                <td>{{ $plan->duration_label ?? '—' }}</td>
                <td>
                  @if($plan->badge_text)
                    <span class="config-badge badge-{{ $plan->badge_color ?? 'secondary' }}">{{ $plan->badge_text }}</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  <span class="status-indicator {{ $plan->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button class="btn-action-icon btn-edit" onclick="openEditPlanModal({{ $plan->id }}, {{ json_encode($plan) }})" title="Edit">
                      <i class="mdi mdi-pencil"></i>
                    </button>
                    <button class="btn-action-icon btn-delete" onclick="confirmDeletePlan({{ $plan->id }}, '{{ addslashes($plan->plan_name) }}')" title="Delete">
                      <i class="mdi mdi-delete"></i>
                    </button>
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
      </div>
    </div>

  </div>

  <!-- ========================================== -->
  <!-- CLIENT RATE PREVIEW SECTION                -->
  <!-- ========================================== -->
  <div class="config-section" id="previewSection">

    <!-- ── Membership Rates Preview ── -->
    <div class="preview-category">
      <div class="preview-category-header">
        <i class="mdi mdi-card-account-details-outline"></i>
        <h3>Membership Plans</h3>
      </div>
      <div class="preview-cards-grid" id="membershipPreviewGrid">
        @forelse($membershipPlans->where('is_active', true) as $plan)
        <div class="preview-rate-card">
          @if($plan->badge_text)
            <div class="preview-badge badge-{{ $plan->badge_color ?? 'secondary' }}">{{ $plan->badge_text }}</div>
          @endif
          <div class="preview-plan-name">{{ $plan->plan_name }}</div>
          <div class="preview-plan-price">₱{{ number_format($plan->price, 2) }}</div>
          @if($plan->requires_buddy && $plan->buddy_count > 1)
            <div class="preview-per-person">₱{{ number_format($plan->per_person_price, 2) }}/person</div>
          @endif
          <div class="preview-plan-duration">{{ $plan->duration_label ?? $plan->duration_days . ' days' }}</div>
          @if($plan->description)
            <div class="preview-plan-desc">{{ $plan->description }}</div>
          @endif
          <div class="preview-plan-tags">
            @if($plan->requires_student)
              <span class="preview-tag tag-student"><i class="mdi mdi-school"></i> Student ID Required</span>
            @endif
            @if($plan->requires_buddy)
              <span class="preview-tag tag-buddy"><i class="mdi mdi-account-multiple"></i> {{ $plan->buddy_count }} People</span>
            @endif
          </div>
        </div>
        @empty
        <div class="preview-empty">
          <i class="mdi mdi-information-outline"></i>
          <p>No active membership plans to display.</p>
        </div>
        @endforelse
      </div>
    </div>

    <!-- ── Personal Training Rates Preview ── -->
    <div class="preview-category">
      <div class="preview-category-header">
        <i class="mdi mdi-dumbbell"></i>
        <h3>Personal Training Rates</h3>
      </div>
      <div class="preview-cards-grid" id="ptPreviewGrid">
        @forelse($ptPlans->where('is_active', true) as $plan)
        <div class="preview-rate-card preview-rate-card--pt">
          @if($plan->badge_text)
            <div class="preview-badge badge-{{ $plan->badge_color ?? 'secondary' }}">{{ $plan->badge_text }}</div>
          @endif
          <div class="preview-plan-name">{{ $plan->plan_name }}</div>
          <div class="preview-plan-price">₱{{ number_format($plan->price, 2) }}</div>
          <div class="preview-plan-duration">{{ $plan->duration_label ?? $plan->duration_days . ' days' }}</div>
          @if($plan->description)
            <div class="preview-plan-desc">{{ $plan->description }}</div>
          @endif
        </div>
        @empty
        <div class="preview-empty">
          <i class="mdi mdi-information-outline"></i>
          <p>No active personal training plans to display.</p>
        </div>
        @endforelse
      </div>
    </div>

  </div>


  <!-- ========================================== -->
  <!-- ADD / EDIT PLAN MODAL                      -->
  <!-- ========================================== -->
  <div class="modal fade" id="planModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="planModalTitle">Add Plan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
          </button>
        </div>
        <div class="modal-body">
          <form id="planForm">
            <input type="hidden" id="planId" value="">
            <input type="hidden" id="planCategory" value="membership">

            <div class="form-row">
              <div class="form-group col-md-8">
                <label class="form-label">Plan Name <span class="required-mark">*</span></label>
                <input type="text" class="form-control" id="planName" placeholder="e.g. Regular, Student Rate, Monthly" required>
              </div>
              <div class="form-group col-md-4">
                <label class="form-label">Price (₱) <span class="required-mark">*</span></label>
                <input type="number" class="form-control" id="planPrice" placeholder="0.00" step="0.01" min="0" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label class="form-label">Duration (Days) <span class="required-mark">*</span></label>
                <input type="number" class="form-control" id="planDurationDays" placeholder="30" min="1" required>
              </div>
              <div class="form-group col-md-4">
                <label class="form-label">Duration Label</label>
                <input type="text" class="form-control" id="planDurationLabel" placeholder="e.g. Monthly, 3 Months">
              </div>
              <div class="form-group col-md-4">
                <label class="form-label">Status</label>
                <select class="form-control" id="planIsActive">
                  <option value="1" selected>Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label class="form-label">Badge Text</label>
                <input type="text" class="form-control" id="planBadgeText" placeholder="e.g. Best Value">
              </div>
              <div class="form-group col-md-4">
                <label class="form-label">Badge Color</label>
                <select class="form-control" id="planBadgeColor">
                  <option value="">None</option>
                  <option value="success">Green</option>
                  <option value="info">Blue</option>
                  <option value="warning">Orange</option>
                  <option value="danger">Red</option>
                  <option value="primary">Purple</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" id="planDescription" placeholder="Brief description">
              </div>
            </div>

            <!-- Membership-only options -->
            <div id="membershipOptions">
              <hr class="modal-divider">
              <h6 class="modal-section-label">Membership-Specific Options</h6>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label class="form-label">Requires Student ID?</label>
                  <div class="toggle-switch-container">
                    <label class="toggle-switch">
                      <input type="checkbox" id="planRequiresStudent">
                      <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-text" id="studentToggleLabel">No</span>
                  </div>
                </div>
                <div class="form-group col-md-4">
                  <label class="form-label">Buddy Plan?</label>
                  <div class="toggle-switch-container">
                    <label class="toggle-switch">
                      <input type="checkbox" id="planRequiresBuddy">
                      <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-text" id="buddyToggleLabel">No</span>
                  </div>
                </div>
                <div class="form-group col-md-4" id="buddyCountGroup" style="display:none;">
                  <label class="form-label">Number of People</label>
                  <input type="number" class="form-control" id="planBuddyCount" value="2" min="2" max="10">
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-save-plan" id="savePlanBtn" onclick="confirmSavePlan()">
            <i class="mdi mdi-content-save"></i> Save Plan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- CONFIRM SAVE MODAL (Step 1)                -->
  <!-- ========================================== -->
  <div class="modal fade" id="confirmSaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Save</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="confirm-icon confirm-icon-save">
            <i class="mdi mdi-content-save-outline"></i>
          </div>
          <p class="confirm-text">Are you sure you want to save this plan?</p>
          <p class="confirm-plan-name" id="confirmSavePlanName"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-confirm-proceed" onclick="confirmSaveStep2()">Yes, Continue</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- FINAL SAVE CONFIRM MODAL (Step 2)          -->
  <!-- ========================================== -->
  <div class="modal fade" id="finalSaveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Final Confirmation</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="confirm-icon confirm-icon-final">
            <i class="mdi mdi-check-circle-outline"></i>
          </div>
          <p class="confirm-text" id="finalSaveText">This will save the plan. Confirm?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" onclick="goBackToSaveStep1()">Go Back</button>
          <button type="button" class="btn btn-confirm-final" onclick="executeSavePlan()">
            <i class="mdi mdi-check"></i> Yes, Save
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- DELETE CONFIRM MODAL (Step 1)              -->
  <!-- ========================================== -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Plan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="confirm-icon confirm-icon-delete">
            <i class="mdi mdi-delete-outline"></i>
          </div>
          <p class="confirm-text">Are you sure you want to delete this plan?</p>
          <p class="confirm-plan-name" id="confirmDeletePlanName"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-confirm-delete-proceed" onclick="confirmDeleteStep2()">Yes, Continue</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- FINAL DELETE CONFIRM MODAL (Step 2)        -->
  <!-- ========================================== -->
  <div class="modal fade" id="finalDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Final Confirmation</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="confirm-icon confirm-icon-danger">
            <i class="mdi mdi-alert-circle-outline"></i>
          </div>
          <p class="confirm-text">This action cannot be undone. Confirm deletion of:</p>
          <p class="confirm-plan-name confirm-plan-name--danger" id="finalDeletePlanName"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" onclick="goBackToDeleteStep1()">Go Back</button>
          <button type="button" class="btn btn-confirm-danger" onclick="executeDeletePlan()">
            <i class="mdi mdi-delete"></i> Yes, Delete
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/pages/configuration.js'])

<script>
  // Pass CSRF token and route URLs to JS
  window.configRoutes = {
    store:   "{{ route('configuration.plans.store') }}",
    update:  "{{ url('configuration/plans') }}",
    destroy: "{{ url('configuration/plans') }}",
    reorder: "{{ route('configuration.plans.reorder') }}",
    index:   "{{ route('configuration.index') }}"
  };
  window.csrfToken = "{{ csrf_token() }}";
</script>
@endpush
