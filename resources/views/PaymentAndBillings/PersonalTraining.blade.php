<!-- ====== PERSONAL TRAINING PAGE ====== -->
<div class="page-panel" id="ptPage">
  <div class="card">
    <div class="card-body">

      @if($ptPlans->count() > 0)
      <form id="ptPaymentForm" novalidate>
        @csrf

        <!-- ═══ ROW 1: Payment Type Toggle ═══ -->
        <div class="pay-row-type">
            <div class="pay-type-pills">
                <button type="button" class="pay-type-pill pt-pill" data-type="new">
                    <i class="mdi mdi-account-plus"></i> New Client
                </button>
                <button type="button" class="pay-type-pill pt-pill active" data-type="renewal">
                    <i class="mdi mdi-autorenew"></i> Renewal
                </button>
            </div>
            <input type="hidden" name="pt_payment_type" id="ptPaymentType" value="renewal">
        </div>

        <!-- ═══ ROW 2: Two-Column — Client + Plans ═══ -->
        <div class="pay-row-main">

            <!-- LEFT: Client Info + Scheduling -->
            <div class="pay-col-member">
                <!-- Renewal: Search existing customer -->
                <div id="ptClientSearchSection">
                    <label class="form-label">Select Customer</label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" id="ptClientSearch" placeholder="Search by name or contact..." autocomplete="off">
                        <div id="ptClientResults" class="autocomplete-results" style="display:none; z-index: 9999;"></div>
                        <input type="hidden" name="pt_client_id" id="ptClientId">
                        <input type="hidden" id="ptClientSource" value="">
                    </div>
                </div>

                <!-- New: Walk-in client form -->
                <div id="ptNewClientSection" style="display: none;">
                    <div class="member-card">
                        <div class="member-card-header">
                            <div class="member-card-icon"><i class="mdi mdi-account"></i></div>
                            <h4 class="member-card-title">New Client Details</h4>
                        </div>
                        <div class="member-card-body">
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                    <input type="text" class="form-control" name="pt_customer_name" id="ptCustomerName" placeholder="Enter full name">
                                </div>
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control" name="pt_customer_contact" id="ptCustomerContact" placeholder="09XXXXXXXXX">
                                </div>
                            </div>
                            <div class="member-form-row">
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="pt_customer_age" id="ptCustomerAge" placeholder="Age" min="1" max="120">
                                </div>
                                <div class="member-form-col member-form-col-2">
                                    <label class="form-label">Sex</label>
                                    <select class="form-select" name="pt_customer_sex" id="ptCustomerSex">
                                        <option value="" disabled selected>Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trainer + Schedule -->
                <div class="member-card" style="margin-top: 0.75rem;">
                    <div class="member-card-header">
                        <div class="member-card-icon"><i class="mdi mdi-calendar-clock"></i></div>
                        <h4 class="member-card-title">Schedule</h4>
                    </div>
                    <div class="member-card-body">
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Trainer <span class="required-mark">*</span></label>
                                <select class="form-select" name="trainer_name" id="ptTrainerSelect" required>
                                    <option value="" disabled selected>Select Trainer</option>
                                    @foreach($trainers ?? [] as $trainer)
                                        <option value="{{ $trainer }}">{{ $trainer }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Date <span class="required-mark">*</span></label>
                                <input type="date" class="form-control" name="scheduled_date" id="ptScheduleDate" required min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="member-form-row">
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Time <span class="required-mark">*</span></label>
                                <select class="form-select" name="scheduled_time" id="ptScheduleTime" required>
                                    <option value="" disabled selected>Select Time</option>
                                    <option value="06:00">6:00 AM</option>
                                    <option value="07:00">7:00 AM</option>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                    <option value="19:00">7:00 PM</option>
                                    <option value="20:00">8:00 PM</option>
                                    <option value="21:00">9:00 PM</option>
                                </select>
                            </div>
                            <div class="member-form-col member-form-col-2">
                                <label class="form-label">Notes</label>
                                <input type="text" class="form-control" name="pt_notes" id="ptNotes" placeholder="Optional notes...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: PT Plan Selection -->
            <div class="pay-col-plans">
                <label class="form-label">Select PT Plan</label>
                <div class="plan-grid">
                @foreach($ptPlans as $plan)
                <div class="plan-card pt-plan-card{{ $loop->first ? ' active' : '' }}"
                     data-plan="{{ $plan->plan_key }}"
                     data-price="{{ $plan->price }}"
                     data-duration="{{ $plan->duration_days }}">
                    <div class="plan-card-name">{{ $plan->plan_name }}</div>
                    <div class="plan-card-meta">{{ $plan->duration_label ?? ($plan->duration_days . ' ' . ($plan->duration_days === 1 ? 'Session' : 'Sessions')) }}</div>
                    <div class="plan-card-price">₱{{ number_format($plan->price, 2) }}</div>
                    @if($plan->description)
                        <div class="plan-card-meta" style="margin-top: 0.15rem; font-size: 0.65rem;">{{ $plan->description }}</div>
                    @endif
                </div>
                @endforeach
                </div>
                <input type="hidden" name="pt_plan_type" id="ptPlanType" value="{{ $ptPlans->first()?->plan_key ?? '' }}">
            </div>

        </div><!-- /pay-row-main -->

        <!-- ═══ ROW 3: Checkout Bar ═══ -->
        <div class="pay-checkout-bar">
            <div class="checkout-info">
                <div class="checkout-dates">
                    <div class="checkout-date-item">
                        <span class="checkout-date-label">Session</span>
                        <span class="checkout-date-value" id="ptSessionSummary" style="width: auto; color: #ccc;">—</span>
                    </div>
                </div>
            </div>
            <div class="checkout-payment">
                <select class="form-select checkout-method" name="pt_payment_method" id="ptPaymentMethod" required>
                    <option value="" disabled selected>Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Gcash">GCash</option>
                    <option value="Card">Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="checkout-total">
                <span class="checkout-total-label">Total</span>
                <div class="checkout-total-amount">
                    <span class="checkout-currency">₱</span>
                    <input type="number" name="pt_amount" id="ptAmount" step="0.01" value="{{ $ptPlans->first()?->price ?? '0.00' }}" readonly>
                </div>
            </div>
            <div class="checkout-actions">
                <button type="button" class="btn-checkout-clear" id="ptClearBtn" title="Clear Form">
                    <i class="mdi mdi-close"></i>
                </button>
                <button type="submit" class="btn-checkout-pay" id="ptSubmitBtn">
                    <i class="mdi mdi-check-circle"></i> Book & Pay
                </button>
            </div>
        </div><!-- /pay-checkout-bar -->

      </form>
      @else
      <div style="text-align: center; padding: 3rem 2rem;">
        <i class="mdi mdi-dumbbell" style="font-size: 4rem; color: #555; margin-bottom: 1rem; display: block;"></i>
        <p style="color: #999; font-size: 1rem;">No personal training plans configured yet.</p>
        <p style="color: #666; font-size: 0.875rem;">Go to <a href="{{ route('configuration.index') }}" style="color: #FFA726;">Configuration</a> to add PT plans.</p>
      </div>

    </div>
  </div>
</div><!-- /ptPage -->

<!-- PT Confirmation Modal -->
<div id="ptConfirmationModal" class="modal-overlay">
  <div class="confirm-overlay-content">
    <div class="confirm-overlay-header">
      <i class="mdi mdi-dumbbell"></i>
      <h5>Confirm PT Booking</h5>
      <button type="button" class="close" onclick="closePtConfirmation()">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="confirm-overlay-body">
      <p class="mb-3">Please review the session details before proceeding.</p>
      <div class="confirm-details" id="ptConfirmationDetails"></div>
    </div>
    <div class="confirm-overlay-footer">
      <button type="button" class="btn btn-cancel" onclick="closePtConfirmation()">Cancel</button>
      <button type="button" class="btn btn-update" id="ptConfirmBtn">
        <i class="mdi mdi-check"></i> Confirm & Book
      </button>
    </div>
  </div>
</div>
