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
              <button type="button" class="pay-type-pill pt-pill" data-type="extension">
                  <i class="mdi mdi-calendar-plus"></i> Extension
              </button>
          </div>
          <input type="hidden" name="pt_payment_type" id="ptPaymentType" value="renewal">
      </div>

      <!-- ═══ ROW 2: Two-Column — Client + Plans ═══ -->
      <div class="pay-row-main">

          <!-- LEFT: Client Info -->
          <div class="pay-col-member">
              <!-- Renewal/Extension: Search existing PT clients + active members -->
              <div id="ptClientSearchSection" class="client-section-visible">
                  <label class="form-label">Select Client</label>
                  <div class="pos-relative">
                      <input type="text" class="form-control" id="ptClientSearch" placeholder="Search by name or contact..." autocomplete="off">
                      <div id="ptClientResults" class="autocomplete-results hidden"></div>
                      <input type="hidden" name="pt_client_id" id="ptClientId">
                      <input type="hidden" id="ptClientSource" value="">
                      <input type="hidden" id="ptHasPtClient" value="">
                  </div>
              </div>

              <!-- New: Walk-in client form -->
              <div id="ptNewClientSection">
                  <div class="member-card">
                      <div class="member-card-header">
                          <div class="member-card-icon"><i class="mdi mdi-account"></i></div>
                          <h4 class="member-card-title">New Client Details</h4>
                      </div>
                      <div class="member-card-body">
                          <div class="member-form-row">
                              <div class="member-form-col member-form-col-2">
                                  <label class="form-label">Full Name <span class="required-mark">*</span></label>
                                  <div class="pos-relative">
                                      <input type="text" class="form-control" name="pt_customer_name" id="ptCustomerName" placeholder="Search or enter full name" autocomplete="off">
                                      <div id="ptNewClientResults" class="autocomplete-results hidden"></div>
                                  </div>
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
                      <div class="plan-card-meta plan-card-desc">{{ $plan->description }}</div>
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
                      <span class="checkout-date-label">Current Due</span>
                      <input type="text" class="checkout-date-value" id="ptCurrentDueDate" readonly placeholder="—">
                  </div>
                  <i class="mdi mdi-arrow-right checkout-arrow"></i>
                  <div class="checkout-date-item">
                      <span class="checkout-date-label">New Due</span>
                      <input type="text" class="checkout-date-value checkout-date-new" id="ptNewDueDate" readonly placeholder="—">
                  </div>
                  <div class="checkout-date-item checkout-days">
                      <span class="checkout-date-label">Days</span>
                      <input type="number" class="checkout-date-value" id="ptAdditionalDays" readonly value="{{ $ptPlans->first()?->duration_days ?? 0 }}">
                  </div>
              </div>
          </div>
          <div class="checkout-payment">
              <label class="checkout-payment-label">Payment Method</label>
              <select class="form-select checkout-method" name="pt_payment_method" id="ptPaymentMethod" required>
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
                  <input type="number" name="pt_amount" id="ptAmount" step="0.01" value="{{ $ptPlans->first()?->price ?? '0.00' }}" readonly>
              </div>
          </div>
          <div class="checkout-actions">
              <button type="button" class="btn-checkout-clear" id="ptClearBtn" title="Clear Form">
                  <i class="mdi mdi-eraser"></i> Clear
              </button>
              <button type="submit" class="btn-checkout-pay" id="ptSubmitBtn">
                  <i class="mdi mdi-check-circle"></i> Process Payment
              </button>
          </div>
      </div><!-- /pay-checkout-bar -->

    </form>
    @else
    <div class="empty-state-content">
      <i class="mdi mdi-dumbbell empty-state-icon"></i>
      <p class="empty-state-text">No personal training plans configured yet.</p>
      <p class="empty-state-subtext">Go to <a href="{{ route('configuration.index') }}" class="link-accent">Configuration</a> to add PT plans.</p>
    </div>
    @endif

  </div>
</div>
