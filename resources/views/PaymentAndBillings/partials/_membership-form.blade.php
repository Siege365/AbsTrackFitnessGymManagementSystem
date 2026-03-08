<div class="card">
  <div class="card-body">

    <form id="membershipPaymentForm" action="{{ route('membership.payment.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    <!-- ═══ ROW 1: Payment Type Toggle ═══ -->
    <div class="pay-row-type">
        <div class="pay-type-pills">
            <button type="button" class="pay-type-pill" data-type="new">
                <i class="mdi mdi-account-plus"></i> New Member
            </button>
            <button type="button" class="pay-type-pill active" data-type="renewal">
                <i class="mdi mdi-autorenew"></i> Renewal
            </button>
        </div>
        <input type="hidden" name="payment_type" id="paymentType" value="renewal">
    </div>

    <!-- ═══ ROW 2: Two-Column — Member + Plans ═══ -->
    <div class="pay-row-main">

        <!-- LEFT: Member Selection / New Member Form -->
        <div class="pay-col-member">
            <!-- Renewal: Search existing member -->
            <div id="memberSelectionSection" class="member-section-visible">
                <label class="form-label">Select Member</label>
                <div class="pos-relative">
                    <input type="text" class="form-control" id="memberSearch" name="member_search" placeholder="Search by name or contact..." autocomplete="off">
                    <div id="memberResults" class="autocomplete-results hidden"></div>
                    <input type="hidden" name="member_id" id="memberId">
                    <input type="hidden" id="memberStatus">
                    <input type="hidden" id="memberIsStudent" value="0">
                </div>
            </div>

            <!-- New: Member details form -->
            <div id="newMemberSection">
                <div class="member-card">
                    <div class="member-card-header">
                        <div class="member-card-icon"><i class="mdi mdi-account"></i></div>
                        <h4 class="member-card-title">New Member Details</h4>
                    </div>
                    <div class="member-card-body">
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="new_member_name" id="newMemberName" placeholder="Enter full name">
                            </div>
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Contact <span class="required-mark">*</span></label>
                                <input type="text" class="form-control" name="new_member_contact" id="newMemberContact" placeholder="09XXXXXXXXX">
                            </div>
                        </div>
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="new_member_age" id="newMemberAge" placeholder="Age" min="1" max="120">
                            </div>
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Sex</label>
                                <select class="form-select" name="new_member_sex" id="newMemberSex">
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="member-form-col member-form-col-3">
                                <label class="form-label">Avatar</label>
                                <input type="file" class="form-control" name="new_member_avatar" id="newMemberAvatar" accept="image/*">
                            </div>
                        </div>
                        <div class="member-form-row member-student-row">
                            <div class="member-form-col flex-no-shrink">
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

                <!-- Gym Buddy Card (new) -->
                <div id="buddyNewSection">
                    <div class="member-card buddy-card">
                        <div class="member-card-header buddy-header">
                            <div class="member-card-icon buddy-icon"><i class="mdi mdi-account-multiple"></i></div>
                            <h4 class="member-card-title">Buddy Details</h4>
                            <span class="buddy-tag">Buddy</span>
                        </div>
                        <div class="member-card-body">
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="buddy_name" id="buddyName" placeholder="Enter full name">
                                </div>
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Contact <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="buddy_contact" id="buddyContact" placeholder="09XXXXXXXXX">
                                </div>
                            </div>
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="buddy_age" id="buddyAge" placeholder="Age" min="1" max="120">
                                </div>
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Sex</label>
                                    <select class="form-select" name="buddy_sex" id="buddySex">
                                        <option value="" disabled selected>Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="member-form-col member-form-col-3">
                                    <label class="form-label">Avatar</label>
                                    <input type="file" class="form-control" name="buddy_avatar" id="buddyAvatar" accept="image/*">
                                </div>
                            </div>
                            <div class="member-form-row member-student-row">
                                <div class="member-form-col flex-no-shrink">
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

            <!-- Buddy Selection for Renewal -->
            <div id="buddySelectSection">
                <label class="form-label buddy-select-label">Search Buddy Member</label>
                <div class="pos-relative">
                    <input type="text" class="form-control" id="buddyMemberSearch" placeholder="Search buddy by name or contact..." autocomplete="off">
                    <div id="buddyMemberResults" class="autocomplete-results hidden"></div>
                    <input type="hidden" name="buddy_member_id" id="buddyMemberId">
                </div>
            </div>

            <!-- Student Warning -->
            <div id="studentWarning" class="hidden student-warning-container">
                <div class="student-warning-box">
                    <i class="mdi mdi-alert-circle"></i>
                    <strong>Not Eligible:</strong> Student rate is only for student members.
                </div>
            </div>
        </div>

        <!-- RIGHT: Plan Selection -->
        <div class="pay-col-plans">
            <label class="form-label">Select Plan</label>
            <div class="plan-grid">
            @foreach($membershipPlans as $plan)
            <div class="plan-card{{ $plan->plan_key === 'Regular' ? ' active' : '' }}"
                 data-plan="{{ $plan->plan_key }}"
                 data-price="{{ $plan->price }}"
                 data-duration="{{ $plan->duration_days }}"
                 data-requires-student="{{ $plan->requires_student ? 'true' : 'false' }}"
                 data-requires-buddy="{{ $plan->requires_buddy ? 'true' : 'false' }}">
                <div class="plan-card-name">
                    @if($plan->requires_student)<i class="mdi mdi-school"></i> @endif
                    @if($plan->requires_buddy)<i class="mdi mdi-account-multiple"></i> @endif
                    {{ $plan->plan_name }}
                </div>
                <div class="plan-card-meta">{{ $plan->duration_days }} {{ $plan->duration_days === 1 ? 'Day' : 'Days' }}{{ $plan->requires_buddy ? ' · ' . $plan->buddy_count . 'pax' : '' }}</div>
                <div class="plan-card-price">
                    @if($plan->requires_buddy && $plan->buddy_count > 1)
                        ₱{{ number_format($plan->per_person_price, 2) }}<small>/each</small>
                    @else
                        ₱{{ number_format($plan->price, 2) }}
                    @endif
                </div>
            </div>
            @endforeach
            </div>
            <input type="hidden" name="plan_type" id="planType" value="{{ $membershipPlans->firstWhere('plan_key', 'Regular')?->plan_key ?? $membershipPlans->first()?->plan_key ?? 'Regular' }}">
        </div>

    </div><!-- /pay-row-main -->

    <!-- ═══ ROW 3: Checkout Bar ═══ -->
    <div class="pay-checkout-bar">
        <div class="checkout-info">
            <div class="checkout-dates">
                <div class="checkout-date-item">
                    <span class="checkout-date-label">Current Due</span>
                    <input type="text" class="checkout-date-value" id="currentDueDate" readonly placeholder="—">
                </div>
                <i class="mdi mdi-arrow-right checkout-arrow"></i>
                <div class="checkout-date-item">
                    <span class="checkout-date-label">New Due</span>
                    <input type="text" class="checkout-date-value checkout-date-new" name="new_due_date" id="newDueDate" readonly placeholder="—">
                </div>
                <div class="checkout-date-item checkout-days">
                    <span class="checkout-date-label">Days</span>
                    <input type="number" class="checkout-date-value" id="additionalDays" readonly value="30">
                </div>
            </div>
            <!-- Buddy Due Dates (hidden by default) -->
            <div id="buddyDueDateSection" class="checkout-dates buddy-checkout-dates hidden">
                <div class="checkout-date-item">
                    <span class="checkout-date-label">Buddy Due</span>
                    <input type="text" class="checkout-date-value" id="buddyCurrentDueDate" readonly placeholder="—">
                </div>
                <i class="mdi mdi-arrow-right checkout-arrow"></i>
                <div class="checkout-date-item">
                    <span class="checkout-date-label">Buddy New</span>
                    <input type="text" class="checkout-date-value checkout-date-new" name="buddy_new_due_date" id="buddyNewDueDate" readonly placeholder="—">
                </div>
            </div>
        </div>
        <div class="checkout-payment">
            <label class="checkout-payment-label">Payment Method</label>
            <select class="form-select checkout-method" name="payment_method" id="paymentMethod" required>
                <option value="" disabled selected>Select payment method</option>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
                <option value="GCash">GCash</option>
                <option value="PayMaya">PayMaya</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>
        <div class="checkout-total">
            <span class="checkout-total-label">Total</span>
            <div class="checkout-total-amount">
                <span class="checkout-currency">₱</span>
                <input type="number" name="amount" id="amount" step="0.01" value="{{ $membershipPlans->firstWhere('plan_key', 'Regular')?->price ?? $membershipPlans->first()?->price ?? '0.00' }}" readonly>
            </div>
        </div>
        <div class="checkout-actions">
            <button type="button" class="btn-checkout-clear" id="clearFormBtn" title="Clear Form">
                <i class="mdi mdi-eraser"></i> Clear
            </button>
            <button type="submit" class="btn-checkout-pay" id="submitPaymentBtn">
                <i class="mdi mdi-check-circle"></i> Process Payment
            </button>
        </div>
    </div>

    </form>
  </div>
</div>
