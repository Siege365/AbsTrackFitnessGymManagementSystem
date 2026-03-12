/**
 * PT Sessions Page JavaScript
 * Handles all modals, AJAX operations, and interactions for the Training Sessions page
 */

const PTSessionsPage = {
  // Track the current delete operation
  pendingDelete: {
    type: null,
    id: null,
    name: null
  },

  // Track the current cancel operation
  pendingCancel: {
    id: null,
    clientId: null,
    clientName: null
  },

  // Track walk-in state for PT form
  _ptIsWalkIn: false,
  _ptSelectedData: null,
  _ptAutocomplete: null,
  _ptConfirmSubmitted: false,
  _ptShowingConfirmation: false,

  // Track bulk delete operation
  _bulkDeleteType: null,
  _bulkDeleteIds: [],

  // Initialize the page
  init: function() {
    this.bindEvents();
    this.setupPTCustomerSearch();
    this.setupSearchForms();
    this.setupCheckboxes();
    this.setupPTModal();
    this.startAutoRefresh();
  },

  // Start automatic refresh of KPIs
  startAutoRefresh: function() {
    // Refresh KPIs every 60 seconds to reflect auto-completed sessions
    // The backend automatically marks 'in_progress' sessions as 'done' after 2 hours
    setInterval(() => {
      this.refreshKPIs();
    }, 60000); // 60 seconds
  },

  // Refresh KPI cards with latest data
  refreshKPIs: function() {
    $.ajax({
      url: '/sessions/kpis',
      method: 'GET',
      data: { type: 'pt' },
      success: function(response) {
        if (response.success && response.data) {
          const data = response.data;
          
          // Update PT Sessions Today
          $('#kpi_pt_sessions_today').text(formatKPINumber(data.ptSessionsToday));
          
          // Update Upcoming PT Sessions
          $('#kpi_upcoming_pt').text(formatKPINumber(data.upcomingPTSessions));
          
          // Update Completed Sessions
          $('#kpi_completed_sessions').text(formatKPINumber(data.completedSessions));
          
          // Update PT Cancellations
          $('#kpi_pt_cancellations').text(formatKPINumber(data.ptCancellations));
        }
      },
      error: function() {
        console.error('Failed to refresh KPI data');
      }
    });
  },

  // Bind all event handlers
  bindEvents: function() {
    // Add PT Schedule form submission
    $('#addPTScheduleForm').on('submit', function(e) {
      e.preventDefault();
      PTSessionsPage.submitPTSchedule();
    });

    // Edit PT Schedule form submission
    $('#editPTScheduleForm').on('submit', function(e) {
      e.preventDefault();
      PTSessionsPage.saveEditPTSchedule();
    });

    // Book Next Session form submission
    $('#bookNextForm').on('submit', function(e) {
      e.preventDefault();
      if (PTSessionsPage.validateBookNextDateTime()) {
        PTSessionsPage.submitBookNext();
      }
    });

    // Double confirmation input validation
    $('#confirmInput').on('input', function() {
      const name = $('#confirmName').text();
      const input = $(this).val();
      const isMatch = input.toLowerCase() === name.toLowerCase();
      
      $('#finalDeleteBtn').prop('disabled', !isMatch);
      $('#confirmError').toggleClass('d-none', isMatch || input === '');
    });

    // Bulk delete type-in validation
    $('#bulkDeleteConfirmInput').on('input', function() {
      const input = $(this).val();
      const isMatch = input.toLowerCase() === 'delete';
      $('#bulkDeleteConfirmBtn').prop('disabled', !isMatch);
      $('#bulkDeleteConfirmError').toggleClass('d-none', isMatch || input === '');
    });

    // Reset modals on close (excluding confirm/bulk-delete modals which have custom handlers)
    $('.modal').not('#confirmPTModal, #bulkDeleteConfirmModal, #addPTScheduleModal').on('hidden.bs.modal', function() {
      $(this).find('form')[0]?.reset();
      $(this).find('.edit-field').prop('disabled', true);
      $(this).find('.is-invalid').removeClass('is-invalid');
      $(this).find('.invalid-feedback').remove();
      $(this).find('button[type="submit"]').prop('disabled', false);
      $('#enableEditBtn').removeClass('d-none');
      $('#saveEditBtn').addClass('d-none');
      $('#confirmInput').val('');
      $('#confirmError').addClass('d-none');
      $('#finalDeleteBtn').prop('disabled', true);
    });

    // Reload page when reschedule offer modal is dismissed (cancel already executed)
    $('#rescheduleOfferModal').on('hidden.bs.modal', function() {
      if (PTSessionsPage._openingReschedule) {
        PTSessionsPage._openingReschedule = false;
        return;
      }
      setTimeout(() => location.reload(), 300);
    });

    // Single consolidated handler for confirm PT modal dismiss
    $('#confirmPTModal').on('hidden.bs.modal', function() {
      // Always reset the confirm button state
      $('#confirmPTSubmitBtn').prop('disabled', false).html('<i class="mdi mdi-check"></i> Confirm');

      // If submission was completed, just clean up flags and let the page reload
      if (PTSessionsPage._ptConfirmSubmitted) {
        PTSessionsPage._ptConfirmSubmitted = false;
        PTSessionsPage._ptShowingConfirmation = false;
        return;
      }

      // Going back to edit — preserve form data, re-open the Add PT modal
      if (PTSessionsPage._ptShowingConfirmation) {
        PTSessionsPage._ptShowingConfirmation = false;
        $('#addPTScheduleModal').modal('show');
        return;
      }

      // User dismissed (X / outside click) — reset form and re-open
      PTSessionsPage._ptShowingConfirmation = false;
      PTSessionsPage._resetPTFields();
      $('#pt_customer_select').val('');
      $('#pt_customer_id').val('');
      $('#pt_customer_type').val('');
      if ($('#pt_trainer').hasClass('select2-hidden-accessible')) {
        try { $('#pt_trainer').select2('destroy'); } catch(e) {}
      }
      $('#addPTScheduleModal').modal('show');
    });

    // Reset bulk delete confirm button on close
    $('#bulkDeleteConfirmModal').on('hidden.bs.modal', function() {
      $('#bulkDeleteConfirmInput').val('');
      $('#bulkDeleteConfirmError').addClass('d-none');
      $('#bulkDeleteConfirmBtn').prop('disabled', true).html('<i class="mdi mdi-delete"></i> Delete');
    });
  },

  // Setup Select2 AJAX search for PT customer name (supports walk-in)
  setupPTCustomerSearch: function() {
    if (!window.AutocompleteUtils) return;

    const inputElement = document.getElementById('pt_customer_select');
    if (!inputElement) return;

    this._ptAutocomplete = AutocompleteUtils.init({
      inputElement: inputElement,
      apiUrl: '/sessions/customers/search',
      minChars: 1,
      debounceMs: 300,
      onSelect: (item) => {
        this._ptIsWalkIn = false;
        this._ptSelectedData = item;

        $('#pt_customer_id').val(item.id);
        $('#pt_customer_type').val(item.source);
        $('#pt_age').val(item.age || '').prop('readonly', true);
        $('#pt_sex').val(item.sex || '').prop('disabled', true);
        $('#pt_contact').val(item.contact || '').prop('readonly', true);
        
        let ptPlan = 'Per Session';
        let ptPlanKey = 'PTSession';
        if (item.source === 'client') {
          ptPlan = item.formatted_plan_type || item.plan_type || 'Per Session';
          ptPlanKey = item.plan_key || item.plan_type || 'PTSession';
        } else if (item.source === 'membership' && item.client_formatted_plan_type) {
          ptPlan = item.client_formatted_plan_type;
          ptPlanKey = item.client_plan_key || 'PTSession';
        }
        $('#pt_plan').val(ptPlan);
        $('#pt_plan_key').val(ptPlanKey);

        if (item.avatar) {
          $('#pt_avatar_preview').html('<img src="/storage/' + item.avatar + '" alt="Avatar">');
        } else {
          var initial = (item.name || '?').charAt(0).toUpperCase();
          $('#pt_avatar_preview').html('<div class="avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;">' + initial + '</div>');
        }
        $('#pt_avatar_label').text(item.name).removeClass('text-muted').addClass('text-white');
      }
    });

    // Clear stale selection when user modifies the input text after selecting from autocomplete
    $('#pt_customer_select').on('input', function() {
      if (PTSessionsPage._ptSelectedData) {
        // User is typing over a previous selection — reset to walk-in state
        PTSessionsPage._ptSelectedData = null;
        PTSessionsPage._ptIsWalkIn = false;
        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('');
      }
    });

    // Handle walk-in when user types a name not in the list
    $('#pt_customer_select').on('blur', function() {
      var enteredText = $(this).val().trim();
      if (enteredText && !PTSessionsPage._ptSelectedData) {
        PTSessionsPage._ptIsWalkIn = true;
        PTSessionsPage._ptSelectedData = null;

        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('walkin');
        $('#pt_age').val('').prop('readonly', false);
        $('#pt_sex').val('').prop('disabled', false);
        $('#pt_contact').val('').prop('readonly', false);
        $('#pt_plan').val('Per Session');
        $('#pt_plan_key').val('PTSession');

        var initial = (enteredText || '?').charAt(0).toUpperCase();
        $('#pt_avatar_preview').html('<div class="avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;">' + initial + '</div>');
        $('#pt_avatar_label').text(enteredText + ' (Walk-in)').removeClass('text-muted').addClass('text-white');
      }
    });

    // Reset on modal close
    $('#addPTScheduleModal').on('hidden.bs.modal', function() {
      if (!PTSessionsPage._ptShowingConfirmation) {
        PTSessionsPage._resetPTFields();
        $('#pt_customer_select').val('');
        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('');
        
        if ($('#pt_trainer').hasClass('select2-hidden-accessible')) {
          $('#pt_trainer').select2('destroy');
        }
      }
    });

    // Note: confirmPTModal hidden handler is consolidated in bindEvents()
  },

  // Reset PT form fields
  _resetPTFields: function() {
    this._ptIsWalkIn = false;
    this._ptSelectedData = null;
    $('#pt_age').val('').prop('readonly', true);
    $('#pt_sex').val('').prop('disabled', true);
    $('#pt_contact').val('').prop('readonly', true);
    $('#pt_plan').val('');
    $('#pt_plan_key').val('');
    $('#pt_avatar_preview').html('<i class="mdi mdi-account"></i>');
    $('#pt_avatar_label').text('No customer selected').removeClass('text-white').addClass('text-muted');
  },

  // Setup PT Modal - Initialize Select2 when modal is shown
  setupPTModal: function() {
    $('#addPTScheduleModal').on('shown.bs.modal', function() {
      if (typeof $.fn.select2 !== 'function') return;

      setTimeout(function() {
        const $trainer = $('#pt_trainer');
        
        if ($trainer.hasClass('select2-hidden-accessible')) {
          $trainer.select2('destroy');
        }

        $trainer.select2({
          width: '100%',
          minimumResultsForSearch: Infinity,
          placeholder: 'Select Trainer',
          dropdownParent: $('#addPTScheduleModal')
        });
      }, 100);
    });
  },

  // Setup search forms to submit on enter
  setupSearchForms: function() {
    $('input[name="pt_search"]').on('keypress', function(e) {
      if (e.which === 13) {
        $(this).closest('form').submit();
      }
    });
  },

  // Setup select all checkboxes
  setupCheckboxes: function() {
    $('#selectAllPT').on('change', function() {
      $('.pt-checkbox').prop('checked', $(this).is(':checked'));
      PTSessionsPage.updatePTCount();
    });

    $(document).on('change', '.pt-checkbox', function() {
      PTSessionsPage.updatePTCount();
    });
  },

  // Update PT schedules selected count
  updatePTCount: function() {
    const count = $('.pt-checkbox:checked').length;
    $('#selectedPTCount').text(count);
  },

  // Validate PT Schedule Date and Time
  validatePTScheduleDateTime: function() {
    const dateInput = $('input[name="scheduled_date"]', '#addPTScheduleForm').val();
    const timeInput = $('select[name="scheduled_time"]', '#addPTScheduleForm').val();

    if (!dateInput || !timeInput) {
      this.showToast('error', 'Please select both date and time');
      return false;
    }

    const selectedDateTime = new Date(dateInput + 'T' + timeInput);
    const now = new Date();

    if (selectedDateTime < now) {
      this.showToast('error', 'Cannot schedule a session in the past. Please select a future date and time.');
      return false;
    }

    const hour = parseInt(timeInput.split(':')[0]);

    if (hour < 6 || hour >= 21) {
      this.showToast('error', 'Gym operating hours are 6:00 AM to 9:00 PM. Please select a time within operating hours.');
      return false;
    }

    return true;
  },

  // Validate Book Next Session Date and Time
  validateBookNextDateTime: function() {
    const trainerInput = $('#book_trainer_name').val();
    const dateInput = $('input[name="scheduled_date"]', '#bookNextForm').val();
    const timeInput = $('select[name="scheduled_time"]', '#bookNextForm').val();

    if (!trainerInput) {
      this.showToast('error', 'Please select a trainer.');
      return false;
    }

    if (!dateInput || !timeInput) {
      this.showToast('error', 'Please select both date and time');
      return false;
    }

    const selectedDateTime = new Date(dateInput + 'T' + timeInput);
    const now = new Date();

    if (selectedDateTime < now) {
      this.showToast('error', 'Cannot book a session in the past. Please select a future date and time.');
      return false;
    }

    const hour = parseInt(timeInput.split(':')[0]);

    if (hour < 6 || hour >= 21) {
      this.showToast('error', 'Gym operating hours are 6:00 AM to 9:00 PM. Please select a time within operating hours.');
      return false;
    }

    return true;
  },

  // Submit new PT Schedule — show confirmation modal first
  submitPTSchedule: function() {
    var $form = $('#addPTScheduleForm');
    var $clientInput   = $('#pt_customer_select');
    var $trainerInput  = $('#pt_trainer');
    var $dateInput     = $('[name="scheduled_date"]', $form);
    var $timeInput     = $('[name="scheduled_time"]', $form);
    var $paymentInput  = $('[name="payment_type"]', $form);

    var customerName = $clientInput.val();
    var trainerVal   = $trainerInput.val();
    var dateVal      = $dateInput.val();
    var timeVal      = $timeInput.val();
    var paymentVal   = $paymentInput.val();

    // Clear previous highlights
    [$clientInput, $trainerInput, $dateInput, $timeInput, $paymentInput]
      .forEach(function($el) { $el.removeClass('is-invalid'); });

    // Helper
    var self = this;
    function fail(msg, $el) {
      if ($el) { $el.addClass('is-invalid'); $el.trigger('focus'); }
      self.showToast('error', msg);
    }

    // 1. Client
    if (!customerName || !customerName.trim()) {
      fail('Please search and select a customer or type a walk-in name.', $clientInput);
      return;
    }

    // 2. Trainer
    if (!trainerVal) {
      fail('Please select a trainer.', $trainerInput);
      return;
    }

    // 3. Date — required
    if (!dateVal) {
      fail('Please select a scheduled date.', $dateInput);
      return;
    }

    // 3b. Date — not in the past (date-only check)
    var selectedDate = new Date(dateVal + 'T00:00:00');
    var todayStart = new Date(); todayStart.setHours(0, 0, 0, 0);
    if (selectedDate < todayStart) {
      fail('Cannot schedule a session in the past. Please select today or a future date.', $dateInput);
      return;
    }

    // 4. Time — required
    if (!timeVal) {
      fail('Please select a time.', $timeInput);
      return;
    }

    // 4b. Time — operating hours (6 AM – 9 PM)
    var hour = parseInt(timeVal.split(':')[0]);
    if (hour < 6 || hour >= 21) {
      fail('Gym operating hours are 6:00 AM to 9:00 PM. Please select a time within operating hours.', $timeInput);
      return;
    }

    // 4c. Date+time — not in the past (combined check)
    var selectedDateTime = new Date(dateVal + 'T' + timeVal);
    if (selectedDateTime < new Date()) {
      fail('Cannot schedule a session in the past. Please select a future date and time.', $dateInput);
      return;
    }

    // 5. Payment type
    if (!paymentVal) {
      fail('Please select a payment type.', $paymentInput);
      return;
    }

    var displayName = this._ptIsWalkIn
      ? customerName.replace(' (Walk-in)', '').trim()
      : (this._ptSelectedData?.name || customerName);

    var timeText = $('[name="scheduled_time"] option:selected', '#addPTScheduleForm').text();

    $('#confirmPT_name').text(displayName + (this._ptIsWalkIn ? ' (Walk-in)' : ''));
    $('#confirmPT_trainer').text(trainerVal);
    $('#confirmPT_date').text(dateVal);
    $('#confirmPT_time').text(timeText || timeVal);
    $('#confirmPT_payment').text(paymentVal);

    if (this._ptIsWalkIn) {
      $('#confirmPT_type_row').show();
      $('#confirmPT_type').text('Walk-in Customer');
    } else {
      var source = this._ptSelectedData?.source === 'membership' ? 'Membership' : 'Client';
      $('#confirmPT_type_row').show();
      $('#confirmPT_type').text(source);
    }

    this._ptShowingConfirmation = true;
    $('#addPTScheduleModal').modal('hide');
    $('#addPTScheduleModal').one('hidden.bs.modal', function() {
      $('#confirmPTModal').modal('show');
    });
  },

  // Go back from confirmation to PT form modal
  goBackToPTForm: function() {
    this._ptShowingConfirmation = true;
    $('#confirmPTModal').modal('hide');
  },

  // Execute PT schedule submission after confirmation
  executeSubmitPT: function() {
    var $btn = $('#confirmPTSubmitBtn');
    $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Submitting...');

    var data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      trainer_name: $('#pt_trainer').val(),
      scheduled_date: $('[name="scheduled_date"]', '#addPTScheduleForm').val(),
      scheduled_time: $('[name="scheduled_time"]', '#addPTScheduleForm').val(),
      payment_type: $('[name="payment_type"]', '#addPTScheduleForm').val(),
      plan_key: $('#pt_plan_key').val()
    };

    if (this._ptIsWalkIn) {
      var customerName = $('#pt_customer_select').val();
      data.customer_name = customerName.replace(' (Walk-in)', '').trim();
      data.customer_age = $('#pt_age').val() || null;
      data.customer_sex = $('#pt_sex').val() || null;
      data.customer_contact = $('#pt_contact').val() || null;
      data.customer_source = 'walkin';
    } else if (this._ptSelectedData) {
      if (this._ptSelectedData.source === 'membership') {
        data.membership_id = this._ptSelectedData.id;
        data.customer_source = 'membership';
      } else {
        data.client_id = this._ptSelectedData.id;
        data.customer_source = 'client';
      }
    }

    $.ajax({
      url: '/sessions/pt-schedule',
      method: 'POST',
      data: data,
      success: function(response) {
        PTSessionsPage._ptConfirmSubmitted = true;
        $('#confirmPTModal').modal('hide');
        PTSessionsPage.showToast('success', 'PT Schedule added successfully!');
        PTSessionsPage.refreshKPIs();
        // Force a fresh page load (avoids potential browser cache with location.reload)
        setTimeout(function() {
          window.location.href = window.location.pathname + window.location.search;
        }, 1000);
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Confirm');
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || xhr.responseJSON?.message || 'Failed to add PT Schedule';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // View PT Schedule
  viewPTSchedule: function(id) {
    this.loadPTSchedule(id, false);
  },

  // Edit PT Schedule
  editPTSchedule: function(id) {
    this.loadPTSchedule(id, true);
  },

  // Load PT Schedule data
  loadPTSchedule: function(id, enableEdit) {
    $.ajax({
      url: '/sessions/pt-schedule/' + id,
      method: 'GET',
      success: function(response) {
        const data = response.data;
        const isWalkIn = data.customer_source === 'walkin';
        const isMembership = data.customer_source === 'membership';
        
        let displayName = 'N/A';
        if (isMembership && data.membership) {
          displayName = data.membership.name;
        } else if (data.client) {
          displayName = data.client.name;
        } else if (data.customer_name) {
          displayName = data.customer_name;
        }
        
        let customerData = null;
        if (isMembership && data.membership) {
          customerData = data.membership;
        } else if (data.client) {
          customerData = data.client;
        }

        $('#edit_pt_id').val(data.id);
        $('#edit_last_updated_at').val(data.updated_at ? Math.floor(new Date(data.updated_at).getTime() / 1000) : '');
        $('#edit_pt_name').val(displayName);
        $('#edit_pt_age').val(isWalkIn ? (data.customer_age || '') : (customerData?.age || ''));
        $('#edit_pt_sex').val(isWalkIn ? (data.customer_sex || '') : (customerData?.sex || ''));
        $('#edit_pt_contact').val(isWalkIn ? (data.customer_contact || '') : (customerData?.contact || ''));
        $('#edit_pt_plan').val(isWalkIn ? 'Walk-in' : (customerData?.plan_type || ''));
        $('#edit_trainer').val(data.trainer_name);
        $('#edit_date').val(data.scheduled_date ? data.scheduled_date.substring(0, 10) : '');
        $('#edit_time').val(data.scheduled_time?.substring(0, 5));
        $('#edit_payment').val(data.payment_type);
        $('#edit_pt_status').val(data.status?.charAt(0).toUpperCase() + data.status?.slice(1).replace('_', ' '));

        $('#edit_pt_display_name').text(displayName + (isWalkIn ? ' (Walk-in)' : ''));

        if (customerData?.avatar) {
          $('#edit_pt_avatar_preview').html('<img src="/storage/' + customerData.avatar + '" alt="Avatar">');
        } else {
          var initial = (displayName || '?').charAt(0).toUpperCase();
          $('#edit_pt_avatar_preview').html('<div class="avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;">' + initial + '</div>');
        }

        if (enableEdit) {
          $('#viewEditPTTitle').text('Edit PT Schedule');
          PTSessionsPage.enableEdit();
        } else {
          $('#viewEditPTTitle').text('View PT Schedule');
          $('.edit-field').prop('disabled', true);
          $('#enableEditBtn').removeClass('d-none');
          $('#saveEditBtn').addClass('d-none');
        }

        $('#viewEditPTModal').modal('show');
      },
      error: function() {
        PTSessionsPage.showToast('error', 'Failed to load PT Schedule');
      }
    });
  },

  // Enable editing mode
  enableEdit: function() {
    $('.edit-field').prop('disabled', false);
    $('#enableEditBtn').addClass('d-none');
    $('#saveEditBtn').removeClass('d-none');
  },

  // Save edited PT Schedule
  saveEditPTSchedule: function() {
    const id = $('#edit_pt_id').val();
    const formData = {
      trainer_name: $('#edit_trainer').val(),
      scheduled_date: $('#edit_date').val(),
      scheduled_time: $('#edit_time').val(),
      payment_type: $('#edit_payment').val(),
      last_updated_at: $('#edit_last_updated_at').val()
    };

    // Validate required fields
    if (!formData.trainer_name) {
      this.showToast('error', 'Please select a trainer.');
      return;
    }
    if (!formData.scheduled_date) {
      this.showToast('error', 'Please select a date.');
      return;
    }

    // Validate date is not in the past
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(formData.scheduled_date + 'T00:00:00');
    if (selectedDate < today) {
      this.showToast('error', 'Cannot schedule a session in the past. Please select today or a future date.');
      return;
    }

    if (!formData.scheduled_time) {
      this.showToast('error', 'Please select a time.');
      return;
    }

    // Validate date+time is not in the past
    const selectedDateTime = new Date(formData.scheduled_date + 'T' + formData.scheduled_time);
    const now = new Date();
    if (selectedDateTime < now) {
      this.showToast('error', 'Cannot schedule a session in the past. Please select a future date and time.');
      return;
    }
    if (!formData.payment_type) {
      this.showToast('error', 'Please select a payment type.');
      return;
    }

    $.ajax({
      url: '/sessions/pt-schedule/' + id,
      method: 'PUT',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#viewEditPTModal').modal('hide');
        PTSessionsPage.showToast('success', 'PT Schedule updated successfully!');
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        if (xhr.status === 409) {
          PTSessionsPage.showToast('error', xhr.responseJSON?.message || 'This record was modified by someone else. Please refresh.');
          setTimeout(() => $('#viewEditPTModal').modal('hide'), 1500);
          return;
        }
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || 'Failed to update PT Schedule';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // Update PT Schedule status
  updateStatus: function(id, status, lastUpdatedAt) {
    $.ajax({
      url: '/sessions/pt-schedule/' + id + '/status',
      method: 'PATCH',
      data: { status: status, last_updated_at: lastUpdatedAt },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        PTSessionsPage.showToast('success', 'Status updated to ' + status.charAt(0).toUpperCase() + status.slice(1));
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        if (xhr.status === 409) {
          PTSessionsPage.showToast('error', xhr.responseJSON?.message || 'This record was already modified. Please refresh the page.');
          setTimeout(() => location.reload(), 1500);
          return;
        }
        const message = xhr.responseJSON?.message || 'Failed to update status';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // Open Book Next Session modal
  openBookNextModal: function(sessionId, clientName, trainerName, paymentType) {
    $('#book_source_session_id').val(sessionId);
    $('#book_client_name').val(clientName);

    // Pre-select the default trainer, allow user to override
    if (trainerName) {
      $('#book_trainer_name').val(trainerName);
    } else {
      $('#book_trainer_name').val('');
    }

    // Pre-select last used payment type, default to Cash
    $('#book_payment_type').val(paymentType || 'Cash');

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateStr = tomorrow.toISOString().split('T')[0];
    $('#bookNextForm input[name="scheduled_date"]').val(dateStr);
    
    $('#bookNextModal').modal('show');
  },

  // Confirm cancel PT session
  confirmCancelPT: function(id, clientId, clientName, trainerName, paymentType, lastUpdatedAt) {
    this.pendingCancel = { id: id, clientId: clientId, clientName: clientName, trainerName: trainerName || '', paymentType: paymentType || 'Cash', lastUpdatedAt: lastUpdatedAt || '' };
    $('#cancelPTClientName').text(clientName);
    $('#cancelPTId').val(id);
    $('#cancelPTClientId').val(clientId);
    $('#cancelPTClientNameHidden').val(clientName);
    $('#cancelPTConfirmModal').modal('show');
  },

  // Execute the cancellation via AJAX, then offer reschedule
  executeCancelPT: function() {
    const id = this.pendingCancel.id;
    const clientName = this.pendingCancel.clientName;

    $('#cancelPTConfirmModal').modal('hide');

    $.ajax({
      url: '/sessions/pt-schedule/' + id + '/status',
      method: 'PATCH',
      data: { status: 'cancelled', last_updated_at: this.pendingCancel.lastUpdatedAt },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function() {
        PTSessionsPage.showToast('success', 'Session cancelled for ' + clientName);
        PTSessionsPage.refreshKPIs();

        setTimeout(function() {
          $('#rescheduleClientName').text(clientName);
          $('#rescheduleOfferModal').modal('show');
        }, 500);
      },
      error: function(xhr) {
        if (xhr.status === 409) {
          PTSessionsPage.showToast('error', xhr.responseJSON?.message || 'This record was already modified. Please refresh the page.');
          setTimeout(() => location.reload(), 1500);
          return;
        }
        const message = xhr.responseJSON?.message || 'Failed to cancel session';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // Handle "Yes, Reschedule"
  openRescheduleBooking: function() {
    const clientId = this.pendingCancel.clientId;
    const clientName = this.pendingCancel.clientName;

    this._openingReschedule = true;
    $('#rescheduleOfferModal').modal('hide');

    $('#rescheduleOfferModal').one('hidden.bs.modal', function() {
      PTSessionsPage.openBookNextModal(PTSessionsPage.pendingCancel.id, clientName, PTSessionsPage.pendingCancel.trainerName, PTSessionsPage.pendingCancel.paymentType);
    });
  },

  // Submit Book Next Session
  submitBookNext: function() {
    const form = $('#bookNextForm');
    const formData = new FormData(form[0]);

    $.ajax({
      url: '/sessions/pt-schedule/book-next',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#bookNextModal').modal('hide');
        PTSessionsPage.showToast('success', 'Next session booked successfully!');
        PTSessionsPage.refreshKPIs();
        // Force a fresh page load (avoids potential browser cache with location.reload)
        setTimeout(function() {
          window.location.href = window.location.pathname + window.location.search;
        }, 1000);
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || 'Failed to book next session';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // Confirm delete PT Schedule
  confirmDeletePT: function(id, name) {
    this.pendingDelete = {
      type: 'pt',
      id: id,
      name: name
    };
    $('#deleteType').val('pt');
    $('#deleteId').val(id);
    $('#deleteConfirmText').html('Are you sure you want to delete the PT Schedule for <strong>' + name + '</strong>?');
    $('#deleteSessionConfirmInput').val('');
    $('#deleteSessionConfirmBtn').prop('disabled', true);
    $('#deleteSessionConfirmError').addClass('d-none');
    $('#deleteConfirmModal').modal('show');
  },

  // Execute delete
  executeDelete: function() {
    const confirmInput = document.getElementById('deleteSessionConfirmInput');
    const confirmError = document.getElementById('deleteSessionConfirmError');
    if (!confirmInput || confirmInput.value.trim().toLowerCase() !== 'delete') {
      if (confirmError) confirmError.classList.remove('d-none');
      return;
    }
    $('#deleteConfirmModal').modal('hide');
    this.finalDelete();
  },

  // Final delete
  finalDelete: function() {
    const type = this.pendingDelete.type;
    const id = this.pendingDelete.id;
    
    let url = '/sessions/pt-schedule/' + id;

    $.ajax({
      url: url,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        PTSessionsPage.showToast('success', 'Record deleted successfully!');
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const message = xhr.responseJSON?.message || 'Failed to delete record. Please try again.';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // Show toast notification using ToastUtils
  showToast: function(type, message) {
    if (typeof ToastUtils !== 'undefined') {
      switch (type) {
        case 'success':
          ToastUtils.showSuccess(message, 'Success');
          break;
        case 'error':
          ToastUtils.showError(message, 'Validation Error');
          break;
        case 'warning':
          ToastUtils.showWarning(message, 'Warning');
          break;
        default:
          ToastUtils.showInfo(message, 'Info');
          break;
      }
    }
  },

  // Bulk delete PT schedules
  bulkDeletePT: function() {
    const checkedIds = $('.pt-checkbox:checked').map(function() {
      return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
      PTSessionsPage.showToast('error', 'Please select at least one PT schedule to delete.');
      return;
    }

    this._bulkDeleteType = 'pt';
    this._bulkDeleteIds = checkedIds;
    $('#bulkDeleteText').html('Are you sure you want to delete <strong>' + checkedIds.length + '</strong> PT schedule(s)? This action cannot be undone.');
    $('#bulkDeleteConfirmInput').val('');
    $('#bulkDeleteConfirmError').addClass('d-none');
    $('#bulkDeleteConfirmBtn').prop('disabled', true).html('<i class="mdi mdi-delete"></i> Delete');
    $('#bulkDeleteConfirmModal').modal('show');
  },

  // Execute bulk delete after modal confirmation
  executeBulkDelete: function() {
    var ids = this._bulkDeleteIds;
    var $btn = $('#bulkDeleteConfirmBtn');
    $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Deleting...');

    var url = $('#bulkDeletePTForm').attr('action');

    $.ajax({
      url: url,
      type: 'DELETE',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ids: ids
      },
      success: function(response) {
        $('#bulkDeleteConfirmModal').modal('hide');
        PTSessionsPage.showToast('success', response.message || 'PT schedules deleted successfully.');
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i class="mdi mdi-delete"></i> Delete');
        var message = xhr.responseJSON?.message || 'An error occurred while deleting records.';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  /**
   * Apply filter
   */
  applyFilter: function(filterType, value) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    if (value !== 'all') {
      params.set(filterType, value);
    } else {
      params.delete(filterType);
    }
    
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Clear all filters
   */
  clearAllFilters: function() {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    params.delete('pt_status');
    params.delete('pt_sort');
    params.delete('pt_date');
    
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Apply date filter
   */
  applyDateFilter: function(filterType, value) {
    if (!value) return;
    
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    params.set(filterType, value);
    
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Clear date filter
   */
  clearDateFilter: function(filterType) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    params.delete(filterType);
    
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Toggle filter section accordion
   */
  toggleFilterSection: function(headerElement, event) {
    if (event) {
      event.stopPropagation();
    }
    const section = headerElement.closest('.filter-section');
    section.classList.toggle('active');
  }
};

// Initialize on document ready
$(document).ready(function() {
  PTSessionsPage.init();
});

// Make globally accessible for inline scripts
window.PTSessionsPage = PTSessionsPage;

// Backward compatibility alias - shared modals reference SessionsPage
window.SessionsPage = PTSessionsPage;

// Wire up single-delete confirm input
document.addEventListener('DOMContentLoaded', function() {
  const confirmInput = document.getElementById('deleteSessionConfirmInput');
  const confirmBtn = document.getElementById('deleteSessionConfirmBtn');
  if (confirmInput && confirmBtn) {
    confirmInput.addEventListener('input', function() {
      confirmBtn.disabled = this.value.trim().toLowerCase() !== 'delete';
    });
  }
  $('#deleteConfirmModal').on('hidden.bs.modal', function() {
    const inp = document.getElementById('deleteSessionConfirmInput');
    const btn = document.getElementById('deleteSessionConfirmBtn');
    const err = document.getElementById('deleteSessionConfirmError');
    if (inp) inp.value = '';
    if (btn) btn.disabled = true;
    if (err) err.classList.add('d-none');
  });
});
