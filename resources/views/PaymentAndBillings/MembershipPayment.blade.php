@extends('layouts.admin')

@section('title', 'Payment System')

@push('styles')
@vite(['resources/css/membership-payment.css'])
@vite(['resources/css/product-payment.css'])
@endpush

@section('content')

  <!-- Page Header -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Payment System</h2>
              <p class="page-header-subtitle">Process membership, personal training & product payments</p>
          </div>
      </div>
  </div>

  <!-- ========================================== -->
  <!-- PAGE TOGGLE: Membership / Personal Training -->
  <!-- ========================================== -->
  <div class="page-toggle-container">
    <button class="page-toggle-btn active" data-page="membership">
      <i class="mdi mdi-card-account-details-outline"></i>
      <span>Membership Payment</span>
    </button>
    <button class="page-toggle-btn" data-page="pt">
      <i class="mdi mdi-dumbbell"></i>
      <span>Personal Training Payment</span>
    </button>
    <button class="page-toggle-btn" data-page="product">
      <i class="mdi mdi-cart-outline"></i>
      <span>Product Payment</span>
    </button>
  </div>

  <!-- ========================================== -->
  <!-- SIBLING PAGES WRAPPER                      -->
  <!-- ========================================== -->
  <div class="pages-slider">
    <!-- ====== MEMBERSHIP PAGE ====== -->
    <div class="page-panel active" id="membershipPage">
      <!-- Payment Form Card -->
      <div class="card">
        <div class="card-body">
            <div class="section-header">
                <h2 class="card-title">Process Membership Payment</h2>
            </div>
            
            <form id="membershipPaymentForm" action="{{ route('membership.payment.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Member Selection (Hidden for New Membership) -->
            <div class="form-section" id="memberSelectionSection">
                <div class="form-group">
                <label class="form-label">Select Member</label>
                <div style="position: relative;">
                    <input 
                    type="text" 
                    class="form-control" 
                    id="memberSearch" 
                    name="member_search"
                    placeholder="Search by name or contact..."
                    autocomplete="off"
                    >
                  <div id="memberResults" class="autocomplete-results" style="display:none; z-index: 9999;"></div>
                    <input type="hidden" name="member_id" id="memberId">
                    <input type="hidden" id="memberStatus">
                    <input type="hidden" id="memberIsStudent" value="0">
                </div>
                </div>
            </div>

            <!-- New Member Details (Shown only for New Membership) -->
            <div class="form-section" id="newMemberSection" style="display: none;">
                <!-- Person 1 Card -->
                <div class="member-card">
                    <div class="member-card-header">
                        <div class="member-card-icon"><i class="mdi mdi-account"></i></div>
                        <h4 class="member-card-title">Member Details</h4>
                    </div>
                    <div class="member-card-body">
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="new_member_name" id="newMemberName" placeholder="Enter full name">
                            </div>
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Contact Number <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="new_member_contact" id="newMemberContact" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                            </div>
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Avatar <span style="color:#666; font-weight:400;">(Optional)</span></label>
                                <input type="file" class="form-control" name="new_member_avatar" id="newMemberAvatar" accept="image/*">
                            </div>
                        </div>
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="new_member_age" id="newMemberAge" placeholder="Enter" min="1" max="120">
                            </div>
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Sex</label>
                                <select class="form-select" name="new_member_sex" id="newMemberSex">
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="member-form-row member-student-row">
                            <div class="member-form-col" style="flex: 0 0 auto;">
                                <label class="form-label"><i class="mdi mdi-school"></i> Student?</label>
                                <div class="student-toggle">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="member1_is_student" id="member1IsStudent" value="1">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="toggle-label" id="member1StudentLabel">No</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Person 2 (Gym Buddy) Card -->
                <div id="buddyNewSection">
                    <div class="member-card buddy-card">
                        <div class="member-card-header buddy-header">
                            <div class="member-card-icon buddy-icon"><i class="mdi mdi-account-multiple"></i></div>
                            <h4 class="member-card-title">Member Details</h4>
                            <span class="buddy-tag">Buddy</span>
                        </div>
                        <div class="member-card-body">
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="buddy_name" id="buddyName" placeholder="Enter full name">
                                </div>
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Contact Number <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="buddy_contact" id="buddyContact" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                                </div>
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Avatar <span style="color:#666; font-weight:400;">(Optional)</span></label>
                                    <input type="file" class="form-control" name="buddy_avatar" id="buddyAvatar" accept="image/*">
                                </div>
                            </div>
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="buddy_age" id="buddyAge" placeholder="Enter" min="1" max="120">
                                </div>
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Sex</label>
                                    <select class="form-select" name="buddy_sex" id="buddySex">
                                        <option value="" disabled selected>Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="member-form-row member-student-row">
                                <div class="member-form-col" style="flex: 0 0 auto;">
                                    <label class="form-label"><i class="mdi mdi-school"></i> Student?</label>
                                    <div class="student-toggle">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="buddy_is_student" id="buddyIsStudent" value="1">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-label" id="buddyStudentLabel">No</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buddy Selection for Renewal/Extension -->
            <div class="form-section" id="buddySelectSection">
                <div class="form-group">
                <label class="form-label">Search Buddy Member</label>
                <div style="position: relative;">
                    <input 
                    type="text" 
                    class="form-control" 
                    id="buddyMemberSearch" 
                    placeholder="Search buddy by name or contact..."
                    autocomplete="off"
                    >
                    <div id="buddyMemberResults" class="autocomplete-results" style="display:none; z-index: 9999;"></div>
                    <input type="hidden" name="buddy_member_id" id="buddyMemberId">
                </div>
                </div>
            </div>

            <!-- Payment Type Selector -->
            <div class="form-section">
                <label class="form-label">Payment Type</label>
                <div class="payment-type-selector">
            <div class="payment-type-pill" data-type="new">
                <span class="icon">
                <i class="mdi mdi-account-plus"></i>
                </span>
                <span class="label" style="font-size: 1.125rem;">New Membership</span>
            </div>

            <div class="payment-type-pill active" data-type="renewal">
                <span class="icon">
                <i class="mdi mdi-autorenew"></i>
                </span>
                <span class="label" style="font-size: 1.125rem;">Renewal</span>
            </div>

            <div class="payment-type-pill" data-type="extension" id="extensionPill">
                <span class="icon">
                <i class="mdi mdi-calendar-plus"></i>
                </span>
                <span class="label" style="font-size: 1.125rem;">Extension</span>
            </div>
                </div>
            </div>
            
            <input type="hidden" name="payment_type" id="paymentType" value="renewal">

            <!-- Plan Type Selector -->
            <div class="form-section">
                <label class="form-label">Plan Selection</label>
                <div class="plan-type-selector">

                @foreach($membershipPlans as $plan)
                <div class="plan-type-card{{ $plan->plan_key === 'Regular' ? ' active' : '' }}"
                     data-plan="{{ $plan->plan_key }}"
                     data-price="{{ $plan->price }}"
                     data-duration="{{ $plan->duration_days }}"
                     data-requires-student="{{ $plan->requires_student ? 'true' : 'false' }}"
                     data-requires-buddy="{{ $plan->requires_buddy ? 'true' : 'false' }}">
                    <div class="plan-name">
                        @if($plan->requires_student)<i class="mdi mdi-school"></i> @endif
                        @if($plan->requires_buddy)<i class="mdi mdi-account-multiple"></i> @endif
                        {{ $plan->plan_name }}
                    </div>
                    <div class="plan-duration">{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'Day' : 'Days' }} Access{{ $plan->requires_buddy ? ' · ' . $plan->buddy_count . ' Persons' : '' }}</div>
                    <div class="plan-price">
                        @if($plan->requires_buddy && $plan->buddy_count > 1)
                            ₱{{ number_format($plan->per_person_price, 2) }} <small>/person</small>
                        @else
                            ₱{{ number_format($plan->price, 2) }}
                        @endif
                    </div>
                    @if($plan->badge_text)
                        <div class="plan-badge {{ $plan->requires_buddy ? 'buddy' : ($plan->badge_color === 'success' ? 'promo' : '') }}">{{ $plan->badge_text }}</div>
                    @endif
                </div>
                @endforeach

            </div>
                <input type="hidden" name="plan_type" id="planType" value="{{ $membershipPlans->firstWhere('plan_key', 'Regular')?->plan_key ?? $membershipPlans->first()?->plan_key ?? 'Regular' }}">
            </div>

            <!-- Student Not Eligible Warning -->
            <div class="form-section" id="studentWarning" style="display: none;">
                <div style="background: #f8d7da; border: 1px solid #dc3545; color: #721c24; padding: 1rem; border-radius: 6px;">
                    <i class="mdi mdi-alert-circle"></i>
                    <strong>Not Eligible:</strong> This member is not registered as a student. Student rate is only available for student members.
                </div>
            </div>

            <!-- Payment Details -->
            <div class="form-section">
                <div class="payment-details-card">
                    <div class="payment-details-header">
                        <h4 class="payment-details-title">Membership Duration</h4>
                    </div>
                    <div class="payment-details-body">
                        <div class="payment-details-row">
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Current Due Date</label>
                                <input type="text" class="form-control" id="currentDueDate" readonly placeholder="N/A">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">New Due Date</label>
                                <input type="text" class="form-control" name="new_due_date" id="newDueDate" readonly placeholder="Will be calculated">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Additional Days</label>
                                <input type="number" class="form-control" id="additionalDays" readonly placeholder="0" value="30">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buddy Due Date Fields (shown only for GymBuddy renewal/extension) -->
                <div id="buddyDueDateSection" class="payment-details-card buddy-dates-card" style="display: none;">
                    <div class="payment-details-body">
                        <div class="payment-details-row">
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Buddy's Current Due Date</label>
                                <input type="text" class="form-control" id="buddyCurrentDueDate" readonly placeholder="N/A">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Buddy's New Due Date</label>
                                <input type="text" class="form-control" name="buddy_new_due_date" id="buddyNewDueDate" readonly placeholder="Will be calculated">
                            </div>
                            <div class="payment-details-col payment-details-col-3">
                                <label class="form-label">Additional Days</label>
                                <input type="number" class="form-control" id="additionalDays" readonly placeholder="0" value="30">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="payment-details-card">
                    <div class="payment-details-body">
                        <div class="payment-details-row">
                            <div class="payment-details-col payment-details-col-2">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="paymentMethod" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="GCash">GCash</option>
                                    <option value="PayMaya">PayMaya</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="payment-details-col payment-details-col-2">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" name="amount" id="amount" placeholder="₱0.00" step="0.01" value="{{ $membershipPlans->firstWhere('plan_key', 'Regular')?->price ?? $membershipPlans->first()?->price ?? '0.00' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="clearFormBtn">
                <i class="mdi mdi-close"></i> Clear
                </button>
                <button type="submit" class="btn btn-primary" id="submitPaymentBtn">
                <i class="mdi mdi-check"></i> Process Payment
                </button>
            </div>
            </form>
        </div>
      </div>

    </div><!-- /membershipPage -->

    <!-- ====== PERSONAL TRAINING PAGE ====== -->
    <div class="page-panel" id="ptPage">
      <div class="card">
        <div class="card-body">
          <div class="section-header">
            <h2 class="card-title">Personal Training Rates</h2>
          </div>

          @if($ptPlans->count() > 0)
          <div class="plan-type-selector">
            @foreach($ptPlans as $plan)
            <div class="plan-type-card{{ $loop->first ? ' active' : '' }}"
                 data-plan="{{ $plan->plan_key }}"
                 data-price="{{ $plan->price }}"
                 data-duration="{{ $plan->duration_days }}">
                <div class="plan-name">{{ $plan->plan_name }}</div>
                <div class="plan-duration">{{ $plan->duration_label ?? ($plan->duration_days . ' ' . ($plan->duration_days === 1 ? 'Day' : 'Days')) }}</div>
                <div class="plan-price">₱{{ number_format($plan->price, 2) }}</div>
                @if($plan->badge_text)
                  <div class="plan-badge">{{ $plan->badge_text }}</div>
                @endif
                @if($plan->description)
                  <div class="plan-description-text" style="font-size: 0.8125rem; color: #999; margin-top: 0.25rem;">{{ $plan->description }}</div>
                @endif
            </div>
            @endforeach
          </div>
          @else
          <div style="text-align: center; padding: 3rem 2rem;">
            <i class="mdi mdi-dumbbell" style="font-size: 4rem; color: #555; margin-bottom: 1rem; display: block;"></i>
            <p style="color: #999; font-size: 1rem;">No personal training plans configured yet.</p>
            <p style="color: #666; font-size: 0.875rem;">Go to <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a> to add PT plans.</p>
          </div>
          @endif

          <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255, 193, 7, 0.08); border: 1px solid rgba(255, 193, 7, 0.2); border-radius: 8px;">
            <p style="color: #ffc107; margin: 0; font-size: 0.875rem;"><i class="mdi mdi-information"></i> You can manage PT schedules in the <strong>Sessions</strong> module. Rates are managed in <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a>.</p>
          </div>
        </div>
      </div>
    </div><!-- /ptPage -->

    <!-- ====== PRODUCT PAYMENT PAGE ====== -->
    <div class="page-panel" id="productPage">
      <!-- Payment Form Card -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title mb-0">Product Payment</h4>
            <div class="d-flex">
              <input type="text" id="productSearchItem" class="form-control form-control-sm mr-2" placeholder="Search items..." style="width: 418px;">
              <button type="button" class="btn btn-lg btn-primary mr-2" style="white-space: nowrap;" id="productAddItemBtn">
                Add Item
              </button>
              <div id="productSearchResults" class="search-results" style="display: none;"></div>
            </div>
          </div>

          <form action="{{ route('payments.store') }}" method="POST" id="productPaymentForm">
            @csrf

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="productCustomerName" class="form-label">Customer Name</label>
                  <div style="position: relative;">
                    <input type="text" class="form-control" id="productCustomerName" name="customer_name" placeholder="Name" autocomplete="off" required>
                    <input type="hidden" id="productCustomerId" name="customer_id">
                    <div id="productCustomerResults" class="search-results" style="display:none;"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="productTotalAmount" class="form-label">Total Amount</label>
                  <input type="number" class="form-control" id="productTotalAmount" name="total_amount" placeholder="₱0.00" readonly>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="productTransactionType" class="form-label">Transaction Type</label>
                  <select class="form-control" id="productTransactionType" name="transaction_type">
                    <option>Mixed</option>
                    <option>Cash</option>
                    <option>Credit Card</option>
                    <option>Online Payment</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="productPaidAmount" class="form-label">Paid Amount</label>
                  <input type="number" class="form-control" id="productPaidAmount" name="paid_amount" placeholder="₱0.00" step="0.01">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="productPaymentMethod" class="form-label">Payment Method</label>
                  <select class="form-control" id="productPaymentMethod" name="payment_method">
                    <option>Cash</option>
                    <option>Credit Card</option>
                    <option>Debit Card</option>
                    <option>GCash</option>
                    <option>Online Payment</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="productReturnAmount" class="form-label">Return Amount</label>
                  <input type="number" class="form-control" id="productReturnAmount" placeholder="₱0.00" readonly>
                </div>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="mb-0">Items</h5>
                </div>
                <div class="table-responsive" style="min-height: 300px; overflow-y: auto;">
                  <table class="table table-bordered" id="productItemsTable">
                    <thead>
                      <tr>
                        <th style="min-width: 200px;">Item</th>
                        <th style="min-width: 80px;">Qty</th>
                        <th style="min-width: 120px;">Unit Price (₱)</th>
                        <th style="min-width: 120px;">Subtotal (₱)</th>
                        <th style="min-width: 100px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="productItemsTableBody">
                      <tr><td colspan="5" class="text-center text-muted">No items added</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <input type="hidden" id="productItemsData" name="items_data">

            <div class="row mt-3">
              <div class="col-12">
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                  <button type="button" class="btn btn-secondary" id="productClearBtn">
                    <i class="mdi mdi-close"></i> Clear
                  </button>
                  <button type="button" class="btn btn-primary" id="productProcessPaymentBtn">
                    <i class="mdi mdi-check"></i> Process Payment
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div><!-- /productPage -->
  </div><!-- /pages-slider -->

<!-- Payment Confirmation Modal -->
<div id="confirmationModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Confirm Payment</h3>
      <button class="modal-close" onclick="closeConfirmationModal()">&times;</button>
    </div>
    <div class="modal-body" style="font-size: 1.125rem;">
      <div class="confirmation-icon warning">
        <i class="mdi mdi-alert-circle-outline"></i>
      </div>
      <p class="confirmation-message">Please review the payment details before proceeding.</p>
      <div class="confirmation-details" id="confirmationDetails"></div>
    </div>
    <div class="modal-footer" style="font-size: 1.125rem;">
      <button type="button" class="btn btn-secondary" onclick="closeConfirmationModal()">
        <i class="mdi mdi-close"></i> Cancel
      </button>
      <button type="button" class="btn btn-primary" onclick="confirmPayment()">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Product Payment Confirmation Modal -->
<div id="productConfirmationModal" class="modal-overlay">
  <div class="modal-content small">
    <div class="modal-header">
      <h3 class="modal-title">Confirm Payment</h3>
      <button class="modal-close" onclick="closeProductConfirmation()">&times;</button>
    </div>
    <div class="modal-body" style="font-size: 1.125rem;">
      <div class="confirmation-icon warning">
        <i class="mdi mdi-alert-circle-outline"></i>
      </div>
      <p class="confirmation-message">Please review the payment details before proceeding.</p>
      <div class="confirmation-details" id="productConfirmationDetails"></div>
    </div>
    <div class="modal-footer" style="font-size: 1.125rem;">
      <button type="button" class="btn btn-secondary" onclick="closeProductConfirmation()">
        <i class="mdi mdi-close"></i> Cancel
      </button>
      <button type="button" class="btn btn-primary" id="confirmProductPaymentBtn">
        <i class="mdi mdi-check"></i> Confirm & Process
      </button>
    </div>
  </div>
</div>

<!-- Product Receipt Modal -->
<div id="productReceiptModal" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Receipt Details</h3>
      <button class="modal-close" onclick="closeProductReceiptModal()">&times;</button>
    </div>
    <div class="modal-body" id="productReceiptModalBody">
      <div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeProductReceiptModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printProductReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>

<!-- Receipt Modal -->
<div id="receiptModal" class="modal-overlay" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">Receipt Details</h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body" id="receiptBody">
      <div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
      <button type="button" class="btn btn-primary" onclick="printReceipt()">
        <i class="mdi mdi-printer"></i> Print
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/avatar-utils.js'])
@vite(['resources/js/common/form-utils.js'])
<script>
// Fallback ToastUtils if the main library fails to load
if (typeof ToastUtils === 'undefined') {
  window.ToastUtils = {
    showSuccess: function(msg) { console.log('Success:', msg); alert('Success: ' + msg); },
    showError: function(msg) { console.error('Error:', msg); alert('Error: ' + msg); },
    showWarning: function(msg) { console.warn('Warning:', msg); alert('Warning: ' + msg); },
    showInfo: function(msg) { console.info('Info:', msg); }
  };
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ========================================
  // PAGE TOGGLE (Membership / PT)
  // ========================================
  const pageToggleBtns = document.querySelectorAll('.page-toggle-btn');
  const pageMap = {
    'membership': 'membershipPage',
    'pt': 'ptPage',
    'product': 'productPage'
  };
  const pageOrder = ['membership', 'pt', 'product'];

  pageToggleBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const targetPage = this.dataset.page;
      const targetPanelId = pageMap[targetPage];

      pageToggleBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const currentActive = document.querySelector('.page-panel.active');
      const targetPanel = document.getElementById(targetPanelId);

      if (currentActive === targetPanel) return;

      // Determine direction based on page order
      const currentPageKey = Object.keys(pageMap).find(k => pageMap[k] === currentActive.id);
      const currentIndex = pageOrder.indexOf(currentPageKey);
      const targetIndex = pageOrder.indexOf(targetPage);
      const goingRight = targetIndex > currentIndex;

      currentActive.classList.add(goingRight ? 'slide-out-left' : 'slide-out-right');
      targetPanel.classList.add(goingRight ? 'slide-in-right' : 'slide-in-left');
      targetPanel.classList.add('active');

      setTimeout(() => {
        currentActive.classList.remove('active', 'slide-out-left', 'slide-out-right');
        targetPanel.classList.remove('slide-in-right', 'slide-in-left');
      }, 400);
    });
  });

  // Auto-switch to tab from URL query parameter (?tab=product or ?tab=pt)
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get('tab');
  if (tabParam && pageMap[tabParam]) {
    const targetBtn = document.querySelector(`.page-toggle-btn[data-page="${tabParam}"]`);
    if (targetBtn) {
      // Instant switch without animation on page load
      pageToggleBtns.forEach(b => b.classList.remove('active'));
      targetBtn.classList.add('active');
      document.querySelectorAll('.page-panel').forEach(p => p.classList.remove('active'));
      document.getElementById(pageMap[tabParam]).classList.add('active');
    }
  }

  // ========================================
  // MEMBERSHIP PAYMENT LOGIC
  // ========================================
  let selectedMemberStatus = '';
  let selectedMemberDueDate = '';
  let selectedMemberIsStudent = false;
  let selectedBuddyDueDate = '';

  // Payment Type Selection
  const paymentTypePills = document.querySelectorAll('.payment-type-pill');
  const paymentTypeInput = document.getElementById('paymentType');
  const memberSelectionSection = document.getElementById('memberSelectionSection');
  const newMemberSection = document.getElementById('newMemberSection');
  const memberSearch = document.getElementById('memberSearch');
  const memberId = document.getElementById('memberId');
  const extensionPill = document.getElementById('extensionPill');

  paymentTypePills.forEach(pill => {
    pill.addEventListener('click', function() {
      const type = this.dataset.type;
      
      if (type === 'extension' && !memberId.value) {
        return;
      }

      if (type === 'renewal' && selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
        ToastUtils.showWarning('Member is active. Please use Extension instead.');
        return;
      }

      paymentTypePills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      paymentTypeInput.value = type;

      if (type === 'new') {
        memberSelectionSection.style.display = 'none';
        newMemberSection.style.display = 'block';
        memberSearch.removeAttribute('required');
        memberId.removeAttribute('required');
        document.getElementById('newMemberName').setAttribute('required', 'required');
        document.getElementById('newMemberContact').setAttribute('required', 'required');
      } else {
        memberSelectionSection.style.display = 'block';
        newMemberSection.style.display = 'none';
        memberSearch.setAttribute('required', 'required');
        document.getElementById('newMemberName').removeAttribute('required');
        document.getElementById('newMemberContact').removeAttribute('required');
      }

      document.getElementById('currentDueDate').value = '';
      document.getElementById('newDueDate').value = '';
      updatePlanDependentFields();
      enforcePlanRestrictions();
      calculateNewDueDate();
    });
  });

  // ========================================
  // STUDENT TOGGLE SWITCHES
  // ========================================
  const member1StudentToggle = document.getElementById('member1IsStudent');
  const buddyStudentToggle = document.getElementById('buddyIsStudent');

  member1StudentToggle.addEventListener('change', function() {
    const label = document.getElementById('member1StudentLabel');
    if (this.checked) {
      label.textContent = 'Yes';
    } else {
      label.textContent = 'No';
    }
    // Update plan restrictions based on student toggle for new member
    if (paymentTypeInput.value === 'new') {
      enforcePlanRestrictions();
    }
  });

  buddyStudentToggle.addEventListener('change', function() {
    const label = document.getElementById('buddyStudentLabel');
    if (this.checked) {
      label.textContent = 'Yes';
    } else {
      label.textContent = 'No';
    }
  });

  // Plan Type Selection
  const planTypeCards = document.querySelectorAll('.plan-type-card');
  const planTypeInput = document.getElementById('planType');
  const amountInput = document.getElementById('amount');
  const additionalDaysInput = document.getElementById('additionalDays');

  planTypeCards.forEach(card => {
    card.addEventListener('click', function() {
      const planType = this.dataset.plan;
      const requiresStudent = this.dataset.requiresStudent === 'true';

      // Student plan: only allow if member is student
      if (requiresStudent) {
        const isStudent = paymentTypeInput.value === 'new'
          ? document.getElementById('member1IsStudent').checked
          : selectedMemberIsStudent;
        if (!isStudent) {
          document.getElementById('studentWarning').style.display = 'block';
          setTimeout(() => { document.getElementById('studentWarning').style.display = 'none'; }, 4000);
          ToastUtils.showWarning('Student rate is only available for student members.');
          return;
        }
      }

      // Regular plan: block if member has a student ID (for new, renewal, and extension)
      if (planType === 'Regular') {
        const isStudent = paymentTypeInput.value === 'new'
          ? document.getElementById('member1IsStudent').checked
          : selectedMemberIsStudent;
        if (isStudent) {
          ToastUtils.showWarning('Regular rate is not available for student members. Please select Student Rate instead.');
          return;
        }
      }

      planTypeCards.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      
      const price = this.dataset.price;
      const duration = this.dataset.duration;
      
      planTypeInput.value = planType;
      amountInput.value = parseFloat(price).toFixed(2);
      additionalDaysInput.value = duration;
      
      updatePlanDependentFields();
      calculateNewDueDate();
    });
  });

  /**
   * Show/hide fields that depend on the selected plan type
   */
  function updatePlanDependentFields() {
    const currentPlan = planTypeInput.value;
    const currentPaymentType = paymentTypeInput.value;
    const requiresBuddy = currentPlan === 'GymBuddy';

    // Buddy fields for new members
    const buddyNewSection = document.getElementById('buddyNewSection');
    if (requiresBuddy && currentPaymentType === 'new') {
      buddyNewSection.classList.add('buddy-visible');
      document.getElementById('buddyName').setAttribute('required', 'required');
      document.getElementById('buddyContact').setAttribute('required', 'required');
    } else {
      buddyNewSection.classList.remove('buddy-visible');
      document.getElementById('buddyName').removeAttribute('required');
      document.getElementById('buddyContact').removeAttribute('required');
    }

    // Buddy selection for renewal/extension
    const buddySelectSection = document.getElementById('buddySelectSection');
    const buddyDueDateSection = document.getElementById('buddyDueDateSection');
    if (requiresBuddy && currentPaymentType !== 'new') {
      buddySelectSection.classList.add('buddy-visible');
      buddyDueDateSection.style.display = 'block';
    } else {
      buddySelectSection.classList.remove('buddy-visible');
      buddyDueDateSection.style.display = 'none';
      document.getElementById('buddyMemberId').value = '';
      document.getElementById('buddyMemberSearch').value = '';
      document.getElementById('buddyCurrentDueDate').value = '';
      document.getElementById('buddyNewDueDate').value = '';
      selectedBuddyDueDate = '';
    }

    // Student warning
    document.getElementById('studentWarning').style.display = 'none';
  }

  /**
   * Enforce plan restrictions based on student status.
   * Disables Regular if student, disables Student if not student.
   * Auto-switches plan if current selection becomes invalid.
   */
  function enforcePlanRestrictions() {
    const isNewPayment = paymentTypeInput.value === 'new';
    const isStudent = isNewPayment
      ? document.getElementById('member1IsStudent').checked
      : selectedMemberIsStudent;

    const regularCard = document.querySelector('[data-plan="Regular"]');
    const studentCard = document.querySelector('[data-plan="Student"]');

    if (isStudent) {
      // Student: disable Regular, enable Student
      regularCard.style.opacity = '0.4';
      regularCard.style.pointerEvents = 'none';
      regularCard.title = 'Not available for students';
      studentCard.style.opacity = '1';
      studentCard.style.pointerEvents = 'auto';
      studentCard.title = '';

      // Auto-switch from Regular to Student
      if (planTypeInput.value === 'Regular') {
        planTypeCards.forEach(c => c.classList.remove('active'));
        studentCard.classList.add('active');
        planTypeInput.value = 'Student';
        amountInput.value = '500.00';
        additionalDaysInput.value = '30';
        calculateNewDueDate();
        ToastUtils.showInfo('Switched to Student Rate for student member.');
      }
    } else {
      // Not student: disable Student, enable Regular
      studentCard.style.opacity = '0.4';
      studentCard.style.pointerEvents = 'none';
      studentCard.title = 'Only available for students';
      regularCard.style.opacity = '1';
      regularCard.style.pointerEvents = 'auto';
      regularCard.title = '';

      // Auto-switch from Student to Regular
      if (planTypeInput.value === 'Student') {
        planTypeCards.forEach(c => c.classList.remove('active'));
        regularCard.classList.add('active');
        planTypeInput.value = 'Regular';
        amountInput.value = '600.00';
        additionalDaysInput.value = '30';
        calculateNewDueDate();
        ToastUtils.showInfo('Switched to Regular Rate – member is not a student.');
      }
    }
  }

  // Member Autocomplete
  let memberSearchTimeout;
  memberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('memberResults');

    clearTimeout(memberSearchTimeout);

    if (query.length < 2) {
      resultsContainer.style.display = 'none';
      return;
    }

    memberSearchTimeout = setTimeout(() => {
      fetch('{{ url('/api/members/search') }}?q=' + encodeURIComponent(query), {
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>';
            resultsContainer.style.display = 'block';
            return;
          }

          resultsContainer.innerHTML = data.map(member => `
            <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-plan="${member.plan_type}" data-status="${member.status}" data-is-student="${member.is_student ? '1' : '0'}">
              <strong>${member.name}</strong>
              ${member.is_student ? '<span class="badge badge-info" style="margin-left: 0.5rem; font-size: 0.7rem;">STUDENT</span>' : ''}
              <div style="font-size: 0.875rem; color: #999;">
                Contact: ${member.contact || 'N/A'} | Plan: ${member.plan_type} | Status: ${member.status}
                ${member.due_date ? `| Due: ${new Date(member.due_date).toLocaleDateString()}` : '| No due date'}
              </div>
            </div>
          `).join('');
          resultsContainer.style.display = 'block';

          resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
              memberSearch.value = this.dataset.name;
              memberId.value = this.dataset.id;
              selectedMemberStatus = this.dataset.status;
              selectedMemberDueDate = this.dataset.dueDate;
              selectedMemberIsStudent = this.dataset.isStudent === '1';
              document.getElementById('memberIsStudent').value = selectedMemberIsStudent ? '1' : '0';
              
              const dueDate = this.dataset.dueDate;
              document.getElementById('currentDueDate').value = dueDate ? 
                new Date(dueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 
                'No due date';
              
              extensionPill.style.opacity = '1';
              extensionPill.style.pointerEvents = 'auto';

              const renewalPill = document.querySelector('[data-type="renewal"]');
              if (selectedMemberStatus === 'Active' && selectedMemberDueDate && new Date(selectedMemberDueDate) > new Date()) {
                renewalPill.style.opacity = '0.5';
                renewalPill.style.pointerEvents = 'none';
                paymentTypePills.forEach(p => p.classList.remove('active'));
                extensionPill.classList.add('active');
                paymentTypeInput.value = 'extension';
              } else {
                renewalPill.style.opacity = '1';
                renewalPill.style.pointerEvents = 'auto';
              }

              // Enforce plan restrictions based on selected member's student status
              enforcePlanRestrictions();
              
              resultsContainer.style.display = 'none';
              updatePlanDependentFields();
              calculateNewDueDate();
            });
          });
        })
        .catch(error => {
          console.error('Error fetching members:', error);
          ToastUtils.showError('Error searching for members');
        });
    }, 300);
  });

  // Buddy Member Autocomplete
  let buddySearchTimeout;
  const buddyMemberSearch = document.getElementById('buddyMemberSearch');
  buddyMemberSearch.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsContainer = document.getElementById('buddyMemberResults');

    clearTimeout(buddySearchTimeout);

    if (query.length < 2) {
      resultsContainer.style.display = 'none';
      return;
    }

    buddySearchTimeout = setTimeout(() => {
      fetch('{{ url('/api/members/search') }}?q=' + encodeURIComponent(query), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
        .then(response => response.json())
        .then(data => {
          const filtered = data.filter(m => String(m.id) !== memberId.value);
          if (filtered.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item">No members found</div>';
            resultsContainer.style.display = 'block';
            return;
          }

          resultsContainer.innerHTML = filtered.map(member => `
            <div class="autocomplete-item" data-id="${member.id}" data-name="${member.name}" data-due-date="${member.due_date || ''}" data-status="${member.status}">
              <strong>${member.name}</strong>
              <div style="font-size: 0.875rem; color: #999;">
                Contact: ${member.contact || 'N/A'} | Status: ${member.status}
                ${member.due_date ? `| Due: ${new Date(member.due_date).toLocaleDateString()}` : '| No due date'}
              </div>
            </div>
          `).join('');
          resultsContainer.style.display = 'block';

          resultsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
              buddyMemberSearch.value = this.dataset.name;
              document.getElementById('buddyMemberId').value = this.dataset.id;
              
              // Store buddy due date and update buddy due date fields
              const buddyDueDate = this.dataset.dueDate;
              selectedBuddyDueDate = buddyDueDate;
              document.getElementById('buddyCurrentDueDate').value = buddyDueDate ? 
                new Date(buddyDueDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 
                'No due date';
              
              calculateNewDueDate();
              resultsContainer.style.display = 'none';
            });
          });
        });
    }, 300);
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#memberResults')) {
      document.getElementById('memberResults').style.display = 'none';
    }
    if (!e.target.closest('#buddyMemberSearch') && !e.target.closest('#buddyMemberResults')) {
      document.getElementById('buddyMemberResults').style.display = 'none';
    }
  });

  // Contact number validation
  function validateContactInput(e) {
    let value = e.target.value.replace(/[^0-9+]/g, '');
    if (value.startsWith('+63')) {
      if (value.length > 13) value = value.substring(0, 13);
    } else if (value.startsWith('09')) {
      if (value.length > 11) value = value.substring(0, 11);
    }
    e.target.value = value;
  }

  document.getElementById('newMemberContact').addEventListener('input', validateContactInput);
  document.getElementById('buddyContact').addEventListener('input', validateContactInput);

  function calculateNewDueDate() {
    const paymentType = paymentTypeInput.value;
    const duration = parseInt(additionalDaysInput.value) || 0;
    const currentDueDateText = document.getElementById('currentDueDate').value;

    if (duration === 0) {
      document.getElementById('newDueDate').value = '';
      document.getElementById('buddyNewDueDate').value = '';
      return;
    }

    let startDate;
    const today = new Date();

    if (paymentType === 'new') {
      startDate = today;
    } else if (paymentType === 'renewal') {
      if (currentDueDateText && currentDueDateText !== 'No due date') {
        const currentDueDate = new Date(currentDueDateText);
        startDate = currentDueDate > today ? currentDueDate : today;
      } else {
        startDate = today;
      }
    } else if (paymentType === 'extension') {
      if (currentDueDateText && currentDueDateText !== 'No due date') {
        startDate = new Date(currentDueDateText);
      } else {
        return;
      }
    }

    const newDueDate = new Date(startDate);
    newDueDate.setDate(newDueDate.getDate() + duration);

    document.getElementById('newDueDate').value = newDueDate.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });

    // Calculate buddy due date for GymBuddy renewal/extension
    if (planTypeInput.value === 'GymBuddy' && paymentType !== 'new') {
      const buddyCurrentDueDateText = document.getElementById('buddyCurrentDueDate').value;
      let buddyStartDate;

      if (paymentType === 'renewal') {
        if (buddyCurrentDueDateText && buddyCurrentDueDateText !== 'No due date' && buddyCurrentDueDateText !== 'N/A') {
          const buddyCurrent = new Date(buddyCurrentDueDateText);
          buddyStartDate = buddyCurrent > today ? buddyCurrent : today;
        } else {
          buddyStartDate = today;
        }
      } else if (paymentType === 'extension') {
        if (buddyCurrentDueDateText && buddyCurrentDueDateText !== 'No due date' && buddyCurrentDueDateText !== 'N/A') {
          buddyStartDate = new Date(buddyCurrentDueDateText);
        } else {
          document.getElementById('buddyNewDueDate').value = '';
          return;
        }
      }

      if (buddyStartDate) {
        const buddyNewDueDate = new Date(buddyStartDate);
        buddyNewDueDate.setDate(buddyNewDueDate.getDate() + duration);
        document.getElementById('buddyNewDueDate').value = buddyNewDueDate.toLocaleDateString('en-US', { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric' 
        });
      }
    } else {
      document.getElementById('buddyNewDueDate').value = '';
    }
  }

  // Form Submission with Confirmation
  const paymentForm = document.getElementById('membershipPaymentForm');
  paymentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (paymentTypeInput.value === 'new') {
      const name = document.getElementById('newMemberName').value.trim();
      if (!name) {
        ToastUtils.showError("Please enter the member's name.");
        return;
      }

      const contact = document.getElementById('newMemberContact').value;
      if (contact && !contact.match(/^(09\d{9}|\+639\d{9})$/)) {
        ToastUtils.showError('Invalid contact number. Use 09XXXXXXXXX or +639XXXXXXXXX format');
        return;
      }

      // Validate buddy fields if Gym Buddy
      if (planTypeInput.value === 'GymBuddy') {
        const buddyNameVal = document.getElementById('buddyName').value.trim();
        if (!buddyNameVal) {
          ToastUtils.showError("Please enter the buddy's name.");
          return;
        }
        const buddyContactVal = document.getElementById('buddyContact').value;
        if (!buddyContactVal || !buddyContactVal.match(/^(09\d{9}|\+639\d{9})$/)) {
          ToastUtils.showError('Invalid buddy contact number.');
          return;
        }
      }
    }

    // Ensure member selected for non-new payments
    if (paymentTypeInput.value !== 'new') {
      if (!memberId.value) {
        ToastUtils.showError('Please select a member before processing payment.');
        return;
      }
      // Validate buddy selection for Gym Buddy renewal/extension
      if (planTypeInput.value === 'GymBuddy') {
        if (!document.getElementById('buddyMemberId').value) {
          ToastUtils.showError('Please select a gym buddy member.');
          return;
        }
      }
    }

    showConfirmationModal();
  });

  function showConfirmationModal() {
    const paymentType = paymentTypeInput.value;
    const planType = planTypeInput.value;
    const amount = amountInput.value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const memberName = paymentType === 'new' 
      ? document.getElementById('newMemberName').value 
      : memberSearch.value;
    const newDueDate = document.getElementById('newDueDate').value;

    let buddyInfo = '';
    if (planType === 'GymBuddy') {
      const buddyNameVal = paymentType === 'new' 
        ? document.getElementById('buddyName').value 
        : document.getElementById('buddyMemberSearch').value;
      const buddyCurrentDue = document.getElementById('buddyCurrentDueDate').value;
      const buddyNewDue = document.getElementById('buddyNewDueDate').value;
      buddyInfo = `
        <div class="confirmation-detail-row">
          <span class="confirmation-detail-label">Gym Buddy:</span>
          <span class="confirmation-detail-value">${buddyNameVal}</span>
        </div>
        ${paymentType !== 'new' && buddyCurrentDue ? `
        <div class="confirmation-detail-row">
          <span class="confirmation-detail-label">Buddy Current Due:</span>
          <span class="confirmation-detail-value">${buddyCurrentDue}</span>
        </div>
        <div class="confirmation-detail-row">
          <span class="confirmation-detail-label">Buddy New Due:</span>
          <span class="confirmation-detail-value" style="color: #28a745;">${buddyNewDue}</span>
        </div>
        ` : ''}
      `;
    }

    const planLabels = {
      'Regular': 'Regular Gym Rate',
      'Student': 'Student Rate',
      'GymBuddy': 'Gym Buddy Rate (2 persons)',
      'ThreeMonths': '3 Months Membership',
      'Session': 'Session Pass'
    };

    const details = `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Member:</span>
        <span class="confirmation-detail-value">${memberName}</span>
      </div>
      ${buddyInfo}
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Type:</span>
        <span class="confirmation-detail-value">${paymentType.toUpperCase()}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Plan:</span>
        <span class="confirmation-detail-value">${planLabels[planType] || planType}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Amount:</span>
        <span class="confirmation-detail-value">₱${parseFloat(amount).toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Method:</span>
        <span class="confirmation-detail-value">${paymentMethod}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">New Due Date:</span>
        <span class="confirmation-detail-value" style="color: #28a745;">${newDueDate}</span>
      </div>
    `;

    document.getElementById('confirmationDetails').innerHTML = details;
    document.getElementById('confirmationModal').classList.add('show');
  }

  window.closeConfirmationModal = function() {
    document.getElementById('confirmationModal').classList.remove('show');
  };

  window.confirmPayment = function() {
    closeConfirmationModal();
    
    const form = document.getElementById('membershipPaymentForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
    }
    
    const formData = new FormData(form);
    
    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ToastUtils.showSuccess(data.message || 'Payment processed successfully!');
        
        window._reloadAfterReceipt = true;
        setTimeout(() => {
          viewReceipt(data.payment.id);
        }, 500);
        
        form.reset();
        
      } else {
        ToastUtils.showError(data.message || 'Payment failed. Please try again.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process Payment';
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An error occurred. Please try again.');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="mdi mdi-check"></i> Process Payment';
      }
    });
  };

  // Clear Form
  document.getElementById('clearFormBtn').addEventListener('click', function() {
    paymentForm.reset();
    memberId.value = '';
    selectedMemberStatus = '';
    selectedMemberDueDate = '';
    selectedMemberIsStudent = false;
    document.getElementById('memberIsStudent').value = '0';
    document.getElementById('currentDueDate').value = '';
    document.getElementById('newDueDate').value = '';
    document.getElementById('buddyMemberId').value = '';
    document.getElementById('buddyMemberSearch').value = '';
    document.getElementById('buddyCurrentDueDate').value = '';
    document.getElementById('buddyNewDueDate').value = '';
    selectedBuddyDueDate = '';
    document.getElementById('buddyDueDateSection').style.display = 'none';
    
    // Reset student toggles
    document.getElementById('member1IsStudent').checked = false;
    document.getElementById('member1StudentLabel').textContent = 'No';
    document.getElementById('member1StudentIdSection').style.display = 'none';
    document.getElementById('studentIdInput').value = '';
    document.getElementById('buddyIsStudent').checked = false;
    document.getElementById('buddyStudentLabel').textContent = 'No';
    document.getElementById('buddyStudentIdSection').style.display = 'none';
    document.getElementById('buddyStudentIdInput').value = '';

    planTypeCards.forEach(c => c.classList.remove('active'));
    document.querySelector('[data-plan="Regular"]').classList.add('active');
    planTypeInput.value = 'Regular';
    amountInput.value = '600.00';
    additionalDaysInput.value = '30';

    extensionPill.style.opacity = '0.5';
    extensionPill.style.pointerEvents = 'none';
    document.querySelector('[data-type="renewal"]').style.opacity = '1';
    document.querySelector('[data-type="renewal"]').style.pointerEvents = 'auto';

    updatePlanDependentFields();
    enforcePlanRestrictions();
  });

  // Enforce plan restrictions on initial page load
  enforcePlanRestrictions();

}); // DOMContentLoaded

// View Receipt
function viewReceipt(transactionId) {
  const modal = document.getElementById('receiptModal');
  const receiptBody = document.getElementById('receiptBody');
  modal.classList.add('show');
  receiptBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';
  fetch(`/membership-payment/${transactionId}/receipt`)
    .then(response => response.json())
    .then(data => { receiptBody.innerHTML = generateReceiptHTML(data); })
    .catch(error => {
      console.error('Error loading receipt:', error);
      ToastUtils.showError('Failed to load receipt.');
      receiptBody.innerHTML = '<div style="padding:2rem;color:#dc3545;text-align:center;"><i class="mdi mdi-alert-circle" style="font-size:3rem;"></i><p>Failed to load receipt.</p></div>';
    });
}

function generateReceiptHTML(data) {
  const planLabels = {
    'Regular': 'Regular Gym Rate', 'Student': 'Student Rate', 'GymBuddy': 'Gym Buddy Rate',
    'ThreeMonths': '3 Months Membership', 'Session': 'Session Pass', 'Monthly': 'Monthly Plan'
  };

  return `
    <div class="receipt-container">
      <div class="receipt-header">
        <h2>MEMBERSHIP PAYMENT RECEIPT</h2>
        <p><strong>Abstrack Fitness Gym</strong></p>
        <p>Toril, Davao Del Sur</p>
      </div>
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px;">
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong>
          <span style="display: block; font-weight: 600;">#${data.receipt_number}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong>
          <span style="display: block; font-weight: 600;">${data.formatted_date || new Date(data.created_at).toLocaleString()}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Member Name</strong>
          <span style="display: block; font-weight: 600;">${data.member_name || 'N/A'}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Contact</strong>
          <span style="display: block; font-weight: 600;">${data.member_contact || data.contact || ''}</span>
        </div>
        ${data.buddy_name ? `
        <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;">
          <strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Gym Buddy</strong>
          <span style="display: block; font-weight: 600;">${data.buddy_name}</span>
        </div>
        <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; border: 1px solid #a5d6a7;">
          <strong style="display: block; font-size: 0.75rem; color: #2e7d32; margin-bottom: 5px;">Buddy Contact</strong>
          <span style="display: block; font-weight: 600;">${data.buddy_contact || 'N/A'}</span>
        </div>
        ` : ''}
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Type</strong>
          <span style="display: block; font-weight: 600;">${(data.payment_type || '').toUpperCase()}</span>
        </div>
        <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
          <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong>
          <span style="display: block; font-weight: 600;">${data.payment_method || 'N/A'}</span>
        </div>
      </div>
      <table class="receipt-table">
        <thead><tr><th>Description</th><th style="text-align: right;">Amount</th></tr></thead>
        <tbody>
          ${data.plan_type === 'GymBuddy' ? `
          <tr>
            <td>
              <strong>Gym Buddy Rate</strong><br>
              <small style="color: #666;">Duration: ${data.duration || 'N/A'} days | 2 Persons</small><br>
              <small style="color: #0d6efd;">Member 1: ${data.member_name}</small><br>
              <small style="color: #0d6efd;">Member 2: ${data.buddy_name || 'N/A'}</small>
            </td>
            <td style="text-align: right;">
              <span style="display: block;">₱${parseFloat(data.amount || 0).toFixed(2)}/person</span>
              <strong style="display: block; margin-top: 4px;">Total: ₱${(parseFloat(data.amount || 0) * 2).toFixed(2)}</strong>
            </td>
          </tr>
          ` : `
          <tr>
            <td>
              <strong>${planLabels[data.plan_type] || data.plan_type || 'Membership'} Plan</strong><br>
              <small style="color: #666;">Duration: ${data.duration || 'N/A'} days</small>
            </td>
            <td style="text-align: right;">₱${parseFloat(data.amount || 0).toFixed(2)}</td>
          </tr>
          `}
        </tbody>
      </table>
      <div class="receipt-total">
        <div class="receipt-row" style="font-size: 1.3rem;">
          <span><strong>Total Paid:</strong></span>
          <span><strong>₱${data.plan_type === 'GymBuddy' ? (parseFloat(data.amount || 0) * 2).toFixed(2) : parseFloat(data.amount || 0).toFixed(2)}</strong></span>
        </div>
      </div>
      <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ccc;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Previous Due Date</strong>
            <span style="display: block; font-weight: 600;">${data.previous_due_date || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">New Due Date</strong>
            <span style="display: block; font-weight: 600; color: #28a745;">${data.new_due_date || 'N/A'}</span>
          </div>
        </div>
      </div>
      ${data.notes ? `<div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
        <strong style="display: block; margin-bottom: 8px; color: #666;">Notes:</strong>
        <p style="margin: 0; color: #333;">${data.notes}</p>
      </div>` : ''}
      <div class="receipt-footer">
        <p><strong>Thank you for your membership!</strong></p>
        <p style="font-size: 0.875rem;">Please keep this receipt for your records.</p>
      </div>
    </div>`;
}

function closeModal() {
  document.getElementById('receiptModal').classList.remove('show');
  if (window._reloadAfterReceipt) { window._reloadAfterReceipt = false; window.location.reload(); }
}

function printReceipt() {
  const content = document.getElementById('receiptBody').innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`<!DOCTYPE html><html><head><title>Receipt</title>
    <style>
      body { font-family: 'Courier New', monospace; }
      .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; }
      .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
      .receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
      .receipt-table th { background: #333; color: white; padding: 10px; text-align: left; }
      .receipt-table td { padding: 10px; border-bottom: 1px solid #ddd; }
      .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
      .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
      .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; }
    </style></head><body>${content}</body></html>`);
  printWindow.document.close();
  printWindow.print();
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') { closeModal(); closeConfirmationModal(); closeProductConfirmation(); closeProductReceiptModal(); }
});

document.querySelectorAll('.modal-overlay').forEach(modal => {
  modal.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
});

// ========================================
// PRODUCT PAYMENT LOGIC
// ========================================
(function() {
  let cartItems = [];
  let inventoryItems = @json($inventoryItems ?? []);
  let selectedSearchItem = null;
  let isProductSubmitting = false;
  const STORAGE_KEY = 'paymentFormState_v1';

  // Save state
  function saveState() {
    try {
      const state = {
        cartItems: cartItems,
        customer_name: document.getElementById('productCustomerName')?.value || '',
        customer_id: document.getElementById('productCustomerId')?.value || '',
        transaction_type: document.getElementById('productTransactionType')?.value || '',
        payment_method: document.getElementById('productPaymentMethod')?.value || '',
        paid_amount: document.getElementById('productPaidAmount')?.value || '',
        total_amount: document.getElementById('productTotalAmount')?.value || ''
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
      console.warn('Failed to save payment form state', e);
    }
  }

  // Load state
  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const state = JSON.parse(raw);
      if (state) {
        cartItems = Array.isArray(state.cartItems) ? state.cartItems : [];
        if (document.getElementById('productCustomerName')) document.getElementById('productCustomerName').value = state.customer_name || '';
        if (document.getElementById('productCustomerId')) document.getElementById('productCustomerId').value = state.customer_id || '';

        if ((state.customer_id || state.customer_name) && window.fetch) {
          fetch(`{{ url('/members/search') }}?q=${encodeURIComponent(state.customer_name || '')}`)
            .then(r => r.json())
            .then(data => {
              const exists = Array.isArray(data) && data.some(m => String(m.id) === String(state.customer_id) || String(m.name).toLowerCase() === String((state.customer_name||'')).toLowerCase());
              if (!exists) {
                if (document.getElementById('productCustomerName')) document.getElementById('productCustomerName').value = '';
                if (document.getElementById('productCustomerId')) document.getElementById('productCustomerId').value = '';
                state.customer_name = '';
                state.customer_id = '';
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
              }
            })
            .catch(() => {});
        }
        if (document.getElementById('productTransactionType')) document.getElementById('productTransactionType').value = state.transaction_type || '';
        if (document.getElementById('productPaymentMethod')) document.getElementById('productPaymentMethod').value = state.payment_method || '';
        if (document.getElementById('productPaidAmount')) document.getElementById('productPaidAmount').value = state.paid_amount || '';
        if (document.getElementById('productTotalAmount')) document.getElementById('productTotalAmount').value = state.total_amount || '';
        renderCart();
        calculateTotals();
      }
    } catch (e) {
      console.warn('Failed to load payment form state', e);
    }
  }

  function clearState() {
    try { localStorage.removeItem(STORAGE_KEY); } catch (e) { }
  }

  // Initialize on DOM ready
  loadState();

  window.addEventListener('beforeunload', function() {
    if (!isProductSubmitting) saveState();
  });

  console.log('Inventory Items Loaded:', inventoryItems.length);

  // Search functionality
  document.getElementById('productSearchItem').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const searchResults = document.getElementById('productSearchResults');

    if (searchTerm.length < 2) {
      searchResults.style.display = 'none';
      return;
    }

    const filtered = inventoryItems.filter(item =>
      item.product_name.toLowerCase().includes(searchTerm) ||
      item.product_number.toLowerCase().includes(searchTerm)
    );

    if (filtered.length > 0) {
      searchResults.innerHTML = filtered.map(item => `
        <div class="search-result-item product-search-result-item" data-id="${item.id}" data-name="${item.product_name}"
             data-price="${item.unit_price}" data-stock="${item.stock_qty}">
          <strong>${item.product_name}</strong> - ₱${parseFloat(item.unit_price).toFixed(2)}
          <span class="text-muted">(Stock: ${item.stock_qty})</span>
        </div>
      `).join('');
      searchResults.style.display = 'block';

      searchResults.querySelectorAll('.product-search-result-item').forEach(item => {
        item.addEventListener('click', function() {
          const stock = parseInt(this.dataset.stock);
          const name = this.dataset.name;
          const id = this.dataset.id;
          const price = parseFloat(this.dataset.price);

          if (isNaN(stock) || stock <= 0) {
            ToastUtils.showWarning('This item is out of stock and cannot be selected.');
            return;
          }

          selectedSearchItem = { id: id, name: name, price: price, stock: stock };
          document.getElementById('productSearchItem').value = name;
          searchResults.style.display = 'none';
        });
      });
    } else {
      searchResults.innerHTML = '<div class="search-result-item">No items found</div>';
      searchResults.style.display = 'block';
    }
  });

  function addItemToCart(item) {
    if (!item || typeof item.stock === 'undefined' || item.stock <= 0) {
      ToastUtils.showError('Cannot add item — Insufficient stock.');
      return;
    }

    const existingItem = cartItems.find(i => i.id == item.id);

    if (existingItem) {
      if (existingItem.qty < item.stock) {
        existingItem.qty++;
      } else {
        ToastUtils.showWarning('Cannot add more. Insufficient stock!');
        return;
      }
    } else {
      cartItems.push({
        id: item.id,
        name: item.name,
        price: item.price,
        qty: 1,
        stock: item.stock
      });
    }

    renderCart();
    calculateTotals();
    saveState();
  }

  function renderCart() {
    const tbody = document.getElementById('productItemsTableBody');

    if (cartItems.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items added</td></tr>';
      return;
    }

    tbody.innerHTML = cartItems.map((item, index) => `
      <tr>
        <td>${item.name}</td>
        <td>
          <input type="number" class="form-control form-control-sm" value="${item.qty}"
                 min="1" max="${item.stock}" onchange="window._productUpdateQty(${index}, this.value)">
        </td>
        <td>₱${item.price.toFixed(2)}</td>
        <td>₱${(item.price * item.qty).toFixed(2)}</td>
        <td>
          <button type="button" class="btn btn-sm btn-danger" onclick="window._productRemoveItem(${index})">
            <i class="mdi mdi-delete"></i>
          </button>
        </td>
      </tr>
    `).join('');
  }

  window._productUpdateQty = function(index, newQty) {
    newQty = parseInt(newQty);
    if (newQty > 0 && newQty <= cartItems[index].stock) {
      cartItems[index].qty = newQty;
      renderCart();
      calculateTotals();
    } else {
      ToastUtils.showWarning('Invalid quantity or insufficient stock!');
      renderCart();
    }
    saveState();
  };

  window._productRemoveItem = function(index) {
    cartItems.splice(index, 1);
    renderCart();
    calculateTotals();
    saveState();
  };

  function calculateTotals() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    document.getElementById('productTotalAmount').value = total.toFixed(2);

    const paidAmount = parseFloat(document.getElementById('productPaidAmount').value) || 0;
    const returnAmount = paidAmount - total;
    document.getElementById('productReturnAmount').value = returnAmount >= 0 ? returnAmount.toFixed(2) : '0.00';
  }

  document.getElementById('productPaidAmount').addEventListener('input', function() {
    calculateTotals();
    saveState();
  });

  // Clear form button
  document.getElementById('productClearBtn').addEventListener('click', function() {
    cartItems = [];
    renderCart();
    document.getElementById('productPaymentForm').reset();
    document.getElementById('productTotalAmount').value = '';
    document.getElementById('productReturnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  });

  document.getElementById('productAddItemBtn').addEventListener('click', function() {
    if (!selectedSearchItem) {
      const name = document.getElementById('productSearchItem').value.trim();
      if (!name) {
        ToastUtils.showWarning('Please select an item first from the search results.');
        return;
      }
      const found = inventoryItems.find(i => i.product_name.toLowerCase() === name.toLowerCase());
      if (!found) {
        ToastUtils.showError('Selected item not found. Please choose from the search results.');
        return;
      }
      if (found.stock_qty <= 0) {
        ToastUtils.showError('This item is out of stock and cannot be added.');
        return;
      }
      selectedSearchItem = { id: found.id, name: found.product_name, price: parseFloat(found.unit_price), stock: found.stock_qty };
    }

    addItemToCart(selectedSearchItem);
    const searchEl = document.getElementById('productSearchItem');
    if (searchEl) {
      searchEl.value = '';
      searchEl.focus();
    }
    const sr = document.getElementById('productSearchResults');
    if (sr) sr.style.display = 'none';
    selectedSearchItem = null;
  });

  // Process Payment Button Handler
  document.getElementById('productProcessPaymentBtn').addEventListener('click', function(e) {
    e.preventDefault();

    if (cartItems.length === 0) {
      ToastUtils.showWarning('Please add at least one item to the cart!');
      return;
    }

    const total = parseFloat(document.getElementById('productTotalAmount').value) || 0;
    const paid = parseFloat(document.getElementById('productPaidAmount').value) || 0;
    if (paid < total) {
      ToastUtils.showError('Payment incomplete: Paid amount must be equal to or greater than the total amount.');
      const paidEl = document.getElementById('productPaidAmount');
      if (paidEl) paidEl.focus();
      return;
    }

    // Prepare items data
    document.getElementById('productItemsData').value = JSON.stringify(cartItems);

    // Build confirmation details
    const itemsHtml = cartItems.map(i => `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">${i.name} x ${i.qty}</span>
        <span class="confirmation-detail-value">₱${(i.price * i.qty).toFixed(2)}</span>
      </div>
    `).join('');

    const customerName = document.getElementById('productCustomerName')?.value || 'Walk-in Customer';
    const paymentMethod = document.getElementById('productPaymentMethod')?.value || 'Cash';

    const details = `
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Customer:</span>
        <span class="confirmation-detail-value">${customerName}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Type:</span>
        <span class="confirmation-detail-value">PRODUCT</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Items:</span>
        <span class="confirmation-detail-value">${cartItems.length} item(s)</span>
      </div>
      ${itemsHtml}
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Payment Method:</span>
        <span class="confirmation-detail-value">${paymentMethod}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Total Amount:</span>
        <span class="confirmation-detail-value">₱${total.toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Paid Amount:</span>
        <span class="confirmation-detail-value">₱${paid.toFixed(2)}</span>
      </div>
      <div class="confirmation-detail-row">
        <span class="confirmation-detail-label">Change:</span>
        <span class="confirmation-detail-value">₱${(paid - total).toFixed(2)}</span>
      </div>
    `;

    document.getElementById('productConfirmationDetails').innerHTML = details;
    document.getElementById('productConfirmationModal').classList.add('show');
  });

  // Close search results when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#productSearchItem') && !e.target.closest('#productSearchResults')) {
      document.getElementById('productSearchResults').style.display = 'none';
    }
    if (!e.target.closest('#productCustomerName') && !e.target.closest('#productCustomerResults')) {
      const cr = document.getElementById('productCustomerResults');
      if (cr) cr.style.display = 'none';
    }
  });

  // Customer autocomplete for product payment
  (function() {
    const input = document.getElementById('productCustomerName');
    const resultsEl = document.getElementById('productCustomerResults');
    const customerIdEl = document.getElementById('productCustomerId');
    let debounceTimer;

    if (!input) return;

    input.addEventListener('input', function(e) {
      const q = this.value.trim();
      customerIdEl.value = '';

      clearTimeout(debounceTimer);
      if (q.length < 1) {
        resultsEl.style.display = 'none';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`{{ url('/members/search') }}?q=${encodeURIComponent(q)}`)
          .then(r => r.json())
          .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
              resultsEl.innerHTML = '<div class="search-result-item">No members found</div>';
              resultsEl.style.display = 'block';
              return;
            }

            resultsEl.innerHTML = data.map(m => `
              <div class="search-result-item" data-id="${m.id}" data-name="${m.name}">
                <strong>${m.name}</strong> <span class="text-muted">${m.contact || ''}</span>
              </div>
            `).join('');
            resultsEl.style.display = 'block';

            resultsEl.querySelectorAll('.search-result-item').forEach(el => {
              el.addEventListener('click', function() {
                input.value = this.dataset.name;
                if (customerIdEl) customerIdEl.value = this.dataset.id;
                resultsEl.style.display = 'none';
              });
            });
          })
          .catch(err => {
            console.error('Member search error', err);
            resultsEl.style.display = 'none';
          });
      }, 250);
    });
  })();

  // Confirm product payment
  window.closeProductConfirmation = function() {
    const m = document.getElementById('productConfirmationModal');
    if (m) m.classList.remove('show');
  };

  function confirmProductPayment() {
    const btn = document.getElementById('confirmProductPaymentBtn');
    if (!btn) return;

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span> Processing...';

    const form = document.getElementById('productPaymentForm');
    if (!form) return;
    const fd = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeProductConfirmation();

        setTimeout(() => {
          loadProductReceiptModal(data.payment.id);
        }, 300);

        clearFormData();
      } else {
        ToastUtils.showError(data.message || 'Failed to process payment');
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    })
    .catch(err => {
      console.error('Product payment error', err);
      ToastUtils.showError('Failed to process payment. Please try again.');
      btn.disabled = false;
      btn.innerHTML = originalText;
    });
  }

  document.getElementById('confirmProductPaymentBtn').addEventListener('click', confirmProductPayment);

  function clearFormData() {
    cartItems = [];
    renderCart();
    document.getElementById('productPaymentForm').reset();
    document.getElementById('productTotalAmount').value = '';
    document.getElementById('productReturnAmount').value = '';
    selectedSearchItem = null;
    clearState();
  }

  // Product Receipt Modal Functions
  function loadProductReceiptModal(paymentId) {
    const modal = document.getElementById('productReceiptModal');
    const modalBody = document.getElementById('productReceiptModalBody');

    modal.classList.add('show');
    modalBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading receipt...</p></div>';

    fetch(`/payments/${paymentId}/receipt-data`)
      .then(response => response.json())
      .then(data => {
        modalBody.innerHTML = generateProductReceiptHTML(data);
        ToastUtils.showSuccess('Payment processed successfully!');
      })
      .catch(error => {
        console.error('Error loading receipt:', error);
        ToastUtils.showError('Failed to load receipt. Please try again.');
        modalBody.innerHTML = '<div style="padding:2rem;color:#dc3545;text-align:center;"><i class="mdi mdi-alert-circle" style="font-size:48px;"></i><p>Failed to load receipt.</p></div>';
      });
  }

  function generateProductReceiptHTML(data) {
    const items = data.items || [];
    const itemsHTML = items.map(item => `
      <tr>
        <td>${item.product_name}</td>
        <td style="text-align: center;">${item.quantity}</td>
        <td style="text-align: right;">₱${parseFloat(item.unit_price).toFixed(2)}</td>
        <td style="text-align: right;">₱${parseFloat(item.subtotal || item.total_price || (item.unit_price * item.quantity)).toFixed(2)}</td>
      </tr>
    `).join('');

    return `
      <div class="receipt-container">
        <div class="receipt-header">
          <h2>RECEIPT</h2>
          <p><strong>Abstrack Fitness Gym</strong></p>
          <p>Toril, Davao Del Sur</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Receipt Number</strong>
            <span style="display: block; font-weight: 600;">#${data.receipt_number}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Date & Time</strong>
            <span style="display: block; font-weight: 600;">${data.formatted_date || new Date(data.created_at).toLocaleString()}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Customer Name</strong>
            <span style="display: block; font-weight: 600;">${data.customer_name || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Cashier</strong>
            <span style="display: block; font-weight: 600;">${data.cashier_name || ''}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Payment Method</strong>
            <span style="display: block; font-weight: 600;">${data.payment_method || 'N/A'}</span>
          </div>
          <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
            <strong style="display: block; font-size: 0.75rem; color: #666; margin-bottom: 5px;">Transaction Type</strong>
            <span style="display: block; font-weight: 600;">${data.transaction_type || 'N/A'}</span>
          </div>
        </div>

        <table class="receipt-table">
          <thead>
            <tr>
              <th>Item</th>
              <th style="text-align: center;">Quantity</th>
              <th style="text-align: right;">Unit Price</th>
              <th style="text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHTML}
          </tbody>
        </table>

        <div class="receipt-total">
          <div class="receipt-row" style="font-size: 1.3rem;">
            <span><strong>Total:</strong></span>
            <span><strong>₱${parseFloat(data.total_amount || 0).toFixed(2)}</strong></span>
          </div>
          <div class="receipt-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
            <span>Paid Amount:</span>
            <span>₱${parseFloat(data.paid_amount || data.paid || 0).toFixed(2)}</span>
          </div>
          <div class="receipt-row">
            <span>Change:</span>
            <span>₱${parseFloat(data.return_amount || data.change || 0).toFixed(2)}</span>
          </div>
        </div>

        <div class="receipt-footer">
          <p><strong>Thank you for your purchase!</strong></p>
        </div>
      </div>
    `;
  }

  window.closeProductReceiptModal = function() {
    const modal = document.getElementById('productReceiptModal');
    modal.classList.remove('show');
    setTimeout(() => { window.location.reload(); }, 300);
  };

  window.printProductReceipt = function() {
    const content = document.getElementById('productReceiptModalBody').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Receipt</title>
        <style>
          body { font-family: 'Courier New', monospace; }
          .receipt-container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px dashed #333; }
          .receipt-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          .receipt-table th { background: #333; color: white; padding: 10px; text-align: left; }
          .receipt-table td { padding: 10px; border-bottom: 1px solid #ddd; }
          .receipt-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
          .receipt-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
          .receipt-footer { margin-top: 30px; padding-top: 20px; border-top: 2px dashed #333; text-align: center; }
        </style>
      </head>
      <body>${content}</body>
      </html>
    `);
    printWindow.document.close();
    printWindow.print();
  };
})(); // End Product Payment IIFE

@if(session('success'))
  ToastUtils.showSuccess('{{ session('success') }}');
@endif
@if(session('error'))
  ToastUtils.showError('{{ session('error') }}');
@endif
@if($errors->any())
  ToastUtils.showError('{{ $errors->first() }}');
@endif
</script>
@endpush
