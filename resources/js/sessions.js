/**
 * Sessions Page JavaScript
 * Handles all modals, AJAX operations, and interactions for the Sessions page
 */

const SessionsPage = {
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

  // Track walk-in state for Attendance form
  _attIsWalkIn: false,
  _attSelectedData: null,

  // Track bulk delete operation
  _bulkDeleteType: null,
  _bulkDeleteIds: [],

  // Initialize the page
  init: function() {
    this.bindEvents();
    this.setupPTCustomerSearch();
    this.setupSearchForms();
    this.setupCheckboxes();
    this.initializeAttendanceModal();
    this.setupPTModal();
  },

  // Refresh KPI cards with latest data
  refreshKPIs: function() {
    $.ajax({
      url: '/sessions/kpis',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data) {
          const data = response.data;
          
          // Update PT Sessions Today
          $('#kpi_pt_sessions_today').text(data.ptSessionsToday);
          
          // Update Upcoming PT Sessions
          $('#kpi_upcoming_pt').text(data.upcomingPTSessions);
          
          // Update PT Cancellations
          $('#kpi_pt_cancellations').text(data.ptCancellations);
          
          // Update Customers Entered Today
          $('#kpi_customers_today').text(data.customersEnteredToday);
          
          // Update percentage change
          const percentChange = data.customerPercentChange;
          const $percentBadge = $('#kpi_customer_percent');
          
          if ($percentBadge.length) {
            // Update text content
            const prefix = percentChange > 0 ? '+' : '';
            $percentBadge.text(prefix + Math.abs(percentChange) + '% Since yesterday');
            
            // Update text color based on change
            $percentBadge.removeClass('text-success text-danger text-secondary');
            if (percentChange > 0) {
              $percentBadge.addClass('text-success');
            } else if (percentChange < 0) {
              $percentBadge.addClass('text-danger');
            } else {
              $percentBadge.addClass('text-secondary');
            }
          }
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
      if (SessionsPage.validatePTScheduleDateTime()) {
        SessionsPage.submitPTSchedule();
      }
    });

    // Edit PT Schedule form submission
    $('#editPTScheduleForm').on('submit', function(e) {
      e.preventDefault();
      SessionsPage.saveEditPTSchedule();
    });

    // Book Next Session form submission
    $('#bookNextForm').on('submit', function(e) {
      e.preventDefault();
      if (SessionsPage.validateBookNextDateTime()) {
        SessionsPage.submitBookNext();
      }
    });

    // Add Attendance form submission
    $('#addAttendanceForm').on('submit', function(e) {
      e.preventDefault();
      SessionsPage.submitAttendance();
    });

    // Double confirmation input validation
    $('#confirmInput').on('input', function() {
      const name = $('#confirmName').text();
      const input = $(this).val();
      const isMatch = input.toLowerCase() === name.toLowerCase();
      
      $('#finalDeleteBtn').prop('disabled', !isMatch);
      $('#confirmError').toggleClass('d-none', isMatch || input === '');
    });

    // Reset modals on close (excluding confirm/bulk-delete modals which have custom handlers)
    $('.modal').not('#confirmPTModal, #bulkDeleteConfirmModal, #addPTScheduleModal, #addAttendanceModal').on('hidden.bs.modal', function() {
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
      // If the user chose "Yes, Reschedule", the openRescheduleBooking flag is set
      if (SessionsPage._openingReschedule) {
        SessionsPage._openingReschedule = false;
        return; // don't reload — book next modal will open
      }
      // User chose "No, Thanks" — reload to reflect the cancellation
      setTimeout(() => location.reload(), 300);
    });

    // When confirm PT modal is dismissed (Go Back), re-open the Add PT form
    $('#confirmPTModal').on('hidden.bs.modal', function() {
      if (SessionsPage._ptConfirmSubmitted) {
        SessionsPage._ptConfirmSubmitted = false;
        return; // submission happened, don't re-open
      }
      // Re-open Add PT modal so user can edit
      $('#addPTScheduleModal').modal('show');
    });

    // Reset bulk delete confirm button on close
    $('#bulkDeleteConfirmModal').on('hidden.bs.modal', function() {
      $('#bulkDeleteConfirmBtn').prop('disabled', false).html('<i class="mdi mdi-delete"></i> Delete');
    });

    // Reset confirm PT button on close
    $('#confirmPTModal').on('hidden.bs.modal', function() {
      $('#confirmPTSubmitBtn').prop('disabled', false).html('<i class="mdi mdi-check"></i> Confirm');
    });
  },

  // Setup Select2 AJAX search for PT customer name (supports walk-in)
  setupPTCustomerSearch: function() {
    if (!window.AutocompleteUtils) return;

    const inputElement = document.getElementById('pt_customer_select');
    if (!inputElement) return;

    // Initialize autocomplete with walk-in support
    this._ptAutocomplete = AutocompleteUtils.init({
      inputElement: inputElement,
      apiUrl: '/sessions/customers/search',
      minChars: 1,
      debounceMs: 300,
      onSelect: (item) => {
        // Existing customer selected from dropdown
        this._ptIsWalkIn = false;
        this._ptSelectedData = item;

        $('#pt_customer_id').val(item.id);
        $('#pt_customer_type').val(item.source);
        $('#pt_age').val(item.age || '').prop('readonly', true);
        $('#pt_sex').val(item.sex || '').prop('disabled', true);
        $('#pt_contact').val(item.contact || '').prop('readonly', true);
        
        // For PT modal: Show ONLY client (PT) subscription, not membership
        let ptPlan = 'Session'; // Default if no client subscription
        if (item.source === 'client') {
          // Selected from client records directly
          ptPlan = item.formatted_plan_type || item.plan_type || 'Session';
        } else if (item.source === 'membership' && item.client_formatted_plan_type) {
          // Selected from membership but has client subscription
          ptPlan = item.client_formatted_plan_type;
        }
        $('#pt_plan').val(ptPlan);

        // Update avatar
        if (item.avatar) {
          $('#pt_avatar_preview').html('<img src="/storage/' + item.avatar + '" alt="Avatar">');
        } else {
          var initial = (item.name || '?').charAt(0).toUpperCase();
          $('#pt_avatar_preview').html('<div class="avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;">' + initial + '</div>');
        }
        $('#pt_avatar_label').text(item.name).removeClass('text-muted').addClass('text-white');
      }
    });

    // Handle walk-in when user types a name not in the list
    $('#pt_customer_select').on('blur', function() {
      var enteredText = $(this).val().trim();
      if (enteredText && !SessionsPage._ptSelectedData) {
        // User typed a name but didn't select from dropdown - treat as walk-in
        SessionsPage._ptIsWalkIn = true;
        SessionsPage._ptSelectedData = null;

        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('walkin');
        $('#pt_age').val('').prop('readonly', false);
        $('#pt_sex').val('').prop('disabled', false);
        $('#pt_contact').val('').prop('readonly', false);
        $('#pt_plan').val('Session'); // PT Session for walk-in customers

        var initial = (enteredText || '?').charAt(0).toUpperCase();
        $('#pt_avatar_preview').html('<div class="avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;">' + initial + '</div>');
        $('#pt_avatar_label').text(enteredText + ' (Walk-in)').removeClass('text-muted').addClass('text-white');
      }
    });

    // Reset on modal close
    $('#addPTScheduleModal').on('hidden.bs.modal', function() {
      // Don't reset if we're showing the confirmation modal
      if (!SessionsPage._ptShowingConfirmation) {
        SessionsPage._resetPTFields();
        $('#pt_customer_select').val('');
        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('');
        
        // Destroy Select2 instance on modal close
        if ($('#pt_trainer').hasClass('select2-hidden-accessible')) {
          $('#pt_trainer').select2('destroy');
        }
      }
    });

    // Reset when confirmation modal is closed
    $('#confirmPTModal').on('hidden.bs.modal', function() {
      SessionsPage._ptShowingConfirmation = false;
      // Only reset if not already submitted successfully
      if (!SessionsPage._ptConfirmSubmitted) {
        SessionsPage._resetPTFields();
        $('#pt_customer_select').val('');
        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('');
        
        // Destroy Select2 instance
        if ($('#pt_trainer').hasClass('select2-hidden-accessible')) {
          $('#pt_trainer').select2('destroy');
        }
      }
      SessionsPage._ptConfirmSubmitted = false;
    });
  },

  // Handle PT customer selection (existing or walk-in)
  // Reset PT form fields
  _resetPTFields: function() {
    this._ptIsWalkIn = false;
    this._ptSelectedData = null;
    $('#pt_age').val('').prop('readonly', true);
    $('#pt_sex').val('').prop('disabled', true);
    $('#pt_contact').val('').prop('readonly', true);
    $('#pt_plan').val('');
    $('#pt_avatar_preview').html('<i class="mdi mdi-account"></i>');
    $('#pt_avatar_label').text('No customer selected').removeClass('text-white').addClass('text-muted');
  },

  // Setup PT Modal - Initialize Select2 when modal is shown
  setupPTModal: function() {
    $('#addPTScheduleModal').on('shown.bs.modal', function() {
      if (typeof $.fn.select2 !== 'function') return;

      // Small delay to ensure modal is fully rendered
      setTimeout(function() {
        const $trainer = $('#pt_trainer');
        
        // Destroy existing Select2 if any
        if ($trainer.hasClass('select2-hidden-accessible')) {
          $trainer.select2('destroy');
        }

        // Initialize Select2 on trainer dropdown (custom dark theme via CSS)
        $trainer.select2({
          width: '100%',
          minimumResultsForSearch: Infinity, // Disable search box
          placeholder: 'Select Trainer',
          dropdownParent: $('#addPTScheduleModal')
        });
      }, 100);
    });
  },

  // Setup search forms to submit on enter
  setupSearchForms: function() {
    $('input[name="attendance_search"], input[name="pt_search"]').on('keypress', function(e) {
      if (e.which === 13) {
        $(this).closest('form').submit();
      }
    });
  },

  // Initialize attendance modal with Select2 AJAX search + walk-in support
  initializeAttendanceModal: function() {
    if (!window.AutocompleteUtils) return;

    const inputElement = document.getElementById('attendance_customer_select');
    if (!inputElement) return;

    // Initialize autocomplete with walk-in support
    this._attAutocomplete = AutocompleteUtils.init({
      inputElement: inputElement,
      apiUrl: '/sessions/customers/search',
      minChars: 1,
      debounceMs: 300,
      onSelect: (item) => {
        // Existing customer selected from dropdown
        this._attIsWalkIn = false;
        this._attSelectedData = item;
        $('#attendance_customer_id').val(item.id);
        $('#attendance_customer_type').val(item.source);
      }
    });

    // Handle walk-in when user types a name not in the list
    $('#attendance_customer_select').on('blur', function() {
      var enteredText = $(this).val().trim();
      if (enteredText && !SessionsPage._attSelectedData) {
        // User typed a name but didn't select from dropdown - treat as walk-in
        SessionsPage._attIsWalkIn = true;
        SessionsPage._attSelectedData = null;
        $('#attendance_customer_id').val('');
        $('#attendance_customer_type').val('walkin');
      }
    });

    // Update date and time when modal opens
    $('#addAttendanceModal').on('show.bs.modal', function() {
      var today = new Date();
      var dateStr = today.getFullYear() + '-' +
                    String(today.getMonth() + 1).padStart(2, '0') + '-' +
                    String(today.getDate()).padStart(2, '0');
      $('#attendance_date').val(dateStr);

      var timeStr = String(today.getHours()).padStart(2, '0') + ':' +
                    String(today.getMinutes()).padStart(2, '0');
      $('#attendance_time').val(timeStr);
    });

    // Reset on modal close
    $('#addAttendanceModal').on('hidden.bs.modal', function() {
      SessionsPage._attIsWalkIn = false;
      SessionsPage._attSelectedData = null;
      $('#attendance_customer_select').val('');
      $('#attendance_customer_id').val('');
      $('#attendance_customer_type').val('');
    });
  },

  // Setup select all checkboxes
  setupCheckboxes: function() {
    $('#selectAllAttendance').on('change', function() {
      $('.attendance-checkbox').prop('checked', $(this).is(':checked'));
      SessionsPage.updateAttendanceCount();
    });

    $('#selectAllPT').on('change', function() {
      $('.pt-checkbox').prop('checked', $(this).is(':checked'));
      SessionsPage.updatePTCount();
    });

    // Update counts when individual checkboxes change
    $(document).on('change', '.attendance-checkbox', function() {
      SessionsPage.updateAttendanceCount();
    });

    $(document).on('change', '.pt-checkbox', function() {
      SessionsPage.updatePTCount();
    });
  },

  // Update attendance selected count
  updateAttendanceCount: function() {
    const count = $('.attendance-checkbox:checked').length;
    $('#selectedAttendanceCount').text(count);
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

    // Parse the selected date and time
    const selectedDateTime = new Date(dateInput + 'T' + timeInput);
    const now = new Date();

    // Check if date/time is in the past
    if (selectedDateTime < now) {
      this.showToast('error', 'Cannot schedule a session in the past. Please select a future date and time.');
      return false;
    }

    // Extract hour from time (format: "HH:MM")
    const hour = parseInt(timeInput.split(':')[0]);

    // Check operating hours (6 AM to 9 PM)
    if (hour < 6 || hour >= 21) {
      this.showToast('error', 'Gym operating hours are 6:00 AM to 9:00 PM. Please select a time within operating hours.');
      return false;
    }

    return true;
  },

  // Validate Book Next Session Date and Time
  validateBookNextDateTime: function() {
    const dateInput = $('input[name="scheduled_date"]', '#bookNextForm').val();
    const timeInput = $('select[name="scheduled_time"]', '#bookNextForm').val();

    if (!dateInput || !timeInput) {
      this.showToast('error', 'Please select both date and time');
      return false;
    }

    // Parse the selected date and time
    const selectedDateTime = new Date(dateInput + 'T' + timeInput);
    const now = new Date();

    // Check if date/time is in the past
    if (selectedDateTime < now) {
      this.showToast('error', 'Cannot book a session in the past. Please select a future date and time.');
      return false;
    }

    // Extract hour from time (format: "HH:MM")
    const hour = parseInt(timeInput.split(':')[0]);

    // Check operating hours (6 AM to 9 PM)
    if (hour < 6 || hour >= 21) {
      this.showToast('error', 'Gym operating hours are 6:00 AM to 9:00 PM. Please select a time within operating hours.');
      return false;
    }

    return true;
  },

  // Submit new PT Schedule — show confirmation modal first
  submitPTSchedule: function() {
    // Validate required fields
    var customerName = $('#pt_customer_select').val();
    var trainerVal = $('#pt_trainer').val();
    var dateVal = $('[name="scheduled_date"]', '#addPTScheduleForm').val();
    var timeVal = $('[name="scheduled_time"]', '#addPTScheduleForm').val();
    var paymentVal = $('[name="payment_type"]', '#addPTScheduleForm').val();

    if (!customerName || !customerName.trim()) {
      SessionsPage.showToast('error', 'Please search and select a customer or type a walk-in name.');
      return;
    }
    if (!trainerVal) {
      SessionsPage.showToast('error', 'Please select a trainer.');
      return;
    }
    if (!dateVal) {
      SessionsPage.showToast('error', 'Please select a date.');
      return;
    }
    if (!timeVal) {
      SessionsPage.showToast('error', 'Please select a time.');
      return;
    }

    // Determine customer name for display
    var displayName = this._ptIsWalkIn
      ? customerName.replace(' (Walk-in)', '').trim()
      : (this._ptSelectedData?.name || customerName);

    // Format time for display
    var timeText = $('[name="scheduled_time"] option:selected', '#addPTScheduleForm').text();

    // Populate confirmation modal
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

    // Hide Add PT modal and show confirmation
    this._ptShowingConfirmation = true;
    $('#addPTScheduleModal').modal('hide');
    $('#addPTScheduleModal').one('hidden.bs.modal', function() {
      $('#confirmPTModal').modal('show');
    });
  },

  // Go back from confirmation to PT form modal
  goBackToPTForm: function() {
    // Keep the data intact by maintaining the flag
    this._ptShowingConfirmation = true;
    $('#confirmPTModal').modal('hide');
    $('#confirmPTModal').one('hidden.bs.modal', function() {
      $('#addPTScheduleModal').modal('show');
      // Reset the flag after showing the form again
      SessionsPage._ptShowingConfirmation = false;
    });
  },

  // Execute PT schedule submission after confirmation
  executeSubmitPT: function() {
    this._ptConfirmSubmitted = true;
    var $btn = $('#confirmPTSubmitBtn');
    $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Submitting...');

    var data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      trainer_name: $('#pt_trainer').val(),
      scheduled_date: $('[name="scheduled_date"]', '#addPTScheduleForm').val(),
      scheduled_time: $('[name="scheduled_time"]', '#addPTScheduleForm').val(),
      payment_type: $('[name="payment_type"]', '#addPTScheduleForm').val()
    };

    if (this._ptIsWalkIn) {
      var customerName = $('#pt_customer_select').val();
      data.customer_name = customerName.replace(' (Walk-in)', '').trim();
      data.customer_age = $('#pt_age').val() || null;
      data.customer_sex = $('#pt_sex').val() || null;
      data.customer_contact = $('#pt_contact').val() || null;
      data.customer_source = 'walkin';
    } else if (this._ptSelectedData) {
      // Send the appropriate ID based on customer source
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
        $('#confirmPTModal').modal('hide');
        SessionsPage.showToast('success', 'PT Schedule added successfully!');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        // Re-enable button
        $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Confirm');

        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || xhr.responseJSON?.message || 'Failed to add PT Schedule';
        SessionsPage.showToast('error', message);
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
        
        // Get display name based on source
        let displayName = 'N/A';
        if (isMembership && data.membership) {
          displayName = data.membership.name;
        } else if (data.client) {
          displayName = data.client.name;
        } else if (data.customer_name) {
          displayName = data.customer_name;
        }
        
        // Get customer details
        let customerData = null;
        if (isMembership && data.membership) {
          customerData = data.membership;
        } else if (data.client) {
          customerData = data.client;
        }

        $('#edit_pt_id').val(data.id);
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

        // Display name under avatar
        $('#edit_pt_display_name').text(displayName + (isWalkIn ? ' (Walk-in)' : ''));

        // Avatar (centered)
        if (customerData?.avatar) {
          $('#edit_pt_avatar_preview').html('<img src="/storage/' + customerData.avatar + '" alt="Avatar">');
        } else {
          var initial = (displayName || '?').charAt(0).toUpperCase();
          $('#edit_pt_avatar_preview').html('<div class="avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;">' + initial + '</div>');
        }

        if (enableEdit) {
          $('#viewEditPTTitle').text('Edit PT Schedule');
          SessionsPage.enableEdit();
        } else {
          $('#viewEditPTTitle').text('View PT Schedule');
          $('.edit-field').prop('disabled', true);
          $('#enableEditBtn').removeClass('d-none');
          $('#saveEditBtn').addClass('d-none');
        }

        $('#viewEditPTModal').modal('show');
      },
      error: function() {
        SessionsPage.showToast('error', 'Failed to load PT Schedule');
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
      payment_type: $('#edit_payment').val()
    };

    $.ajax({
      url: '/sessions/pt-schedule/' + id,
      method: 'PUT',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#viewEditPTModal').modal('hide');
        SessionsPage.showToast('success', 'PT Schedule updated successfully!');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || 'Failed to update PT Schedule';
        SessionsPage.showToast('error', message);
      }
    });
  },

  // Update PT Schedule status
  updateStatus: function(id, status) {
    $.ajax({
      url: '/sessions/pt-schedule/' + id + '/status',
      method: 'PATCH',
      data: { status: status },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        SessionsPage.showToast('success', 'Status updated to ' + status.charAt(0).toUpperCase() + status.slice(1));
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const message = xhr.responseJSON?.message || 'Failed to update status';
        SessionsPage.showToast('error', message);
      }
    });
  },

  // Open Book Next Session modal
  openBookNextModal: function(clientId, clientName) {
    $('#book_client_id').val(clientId);
    $('#book_client_name').val(clientName);
    
    // Set default date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateStr = tomorrow.toISOString().split('T')[0];
    $('#bookNextForm input[name="scheduled_date"]').val(dateStr);
    
    $('#bookNextModal').modal('show');
  },

  // Confirm cancel PT session — show first confirmation modal
  confirmCancelPT: function(id, clientId, clientName) {
    this.pendingCancel = { id: id, clientId: clientId, clientName: clientName };
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
      data: { status: 'cancelled' },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function() {
        SessionsPage.showToast('success', 'Session cancelled for ' + clientName);
        SessionsPage.refreshKPIs();

        // After a short delay, show the reschedule offer modal
        setTimeout(function() {
          $('#rescheduleClientName').text(clientName);
          $('#rescheduleOfferModal').modal('show');
        }, 500);
      },
      error: function(xhr) {
        const message = xhr.responseJSON?.message || 'Failed to cancel session';
        SessionsPage.showToast('error', message);
      }
    });
  },

  // Handle "Yes, Reschedule" — close offer modal then open Book Next modal
  openRescheduleBooking: function() {
    const clientId = this.pendingCancel.clientId;
    const clientName = this.pendingCancel.clientName;

    this._openingReschedule = true;
    $('#rescheduleOfferModal').modal('hide');

    // Wait for modal to close before opening the next one
    $('#rescheduleOfferModal').one('hidden.bs.modal', function() {
      SessionsPage.openBookNextModal(clientId, clientName);
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
        SessionsPage.showToast('success', 'Next session booked successfully!');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || 'Failed to book next session';
        SessionsPage.showToast('error', message);
      }
    });
  },

  // Submit new Attendance (supports clients, memberships, walk-ins)
  submitAttendance: function() {
    // Clear previous error states
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');

    // Validate customer selection
    var customerName = $('#attendance_customer_select').val();
    if (!customerName || !customerName.trim()) {
      SessionsPage.showToast('error', 'Please search and select a customer or type a walk-in name.');
      return;
    }

    // Disable submit button to prevent double submission
    const $submitBtn = $('#addAttendanceForm').find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Recording...');

    var data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      date: $('#attendance_date').val(),
      time_in: $('#attendance_time').val()
    };

    if (this._attIsWalkIn) {
      // Walk-in customer
      data.customer_name = customerName.replace(' (Walk-in)', '').trim();
    } else if (this._attSelectedData) {
      // Existing customer
      var source = this._attSelectedData.source;
      if (source === 'client') {
        data.client_id = this._attSelectedData.id;
      } else if (source === 'membership') {
        data.membership_id = this._attSelectedData.id;
      }
    }

    $.ajax({
      url: '/sessions/attendance',
      method: 'POST',
      data: data,
      success: function(response) {
        $('#addAttendanceModal').modal('hide');
        SessionsPage.showToast('success', 'Attendance recorded successfully!');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        // Re-enable submit button
        $submitBtn.prop('disabled', false).html(originalText);

        // Handle specific error cases
        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          const message = xhr.responseJSON?.message;

          // Display validation errors
          if (message) {
            if (message.includes('already checked in')) {
              SessionsPage.showToast('error', message);
            } else if (message.includes('does not exist')) {
              SessionsPage.showToast('error', 'Selected customer is invalid. Please refresh and try again.');
            } else {
              SessionsPage.showToast('error', message);
            }
          } else if (Object.keys(errors).length > 0) {
            const firstError = Object.values(errors)[0]?.[0];
            SessionsPage.showToast('error', firstError || 'Validation failed');
          } else {
            SessionsPage.showToast('error', 'Please check your input and try again.');
          }
        } else if (xhr.status === 500) {
          SessionsPage.showToast('error', 'Server error occurred. Please try again later.');
        } else if (xhr.status === 0) {
          SessionsPage.showToast('error', 'Network error. Please check your connection.');
        } else {
          const message = xhr.responseJSON?.message || 'Failed to record attendance';
          SessionsPage.showToast('error', message);
        }
      }
    });
  },

  // View attendance details
  viewAttendance: function(id) {
    $.ajax({
      url: '/sessions/attendance/' + id,
      method: 'GET',
      success: function(response) {
        if (response.success) {
          const data = response.data;
          
          // Populate avatar
          const avatarContainer = $('#view_att_avatar_preview');
          const avatar = data.active_avatar || data.membership?.avatar || data.client?.avatar;
          if (avatar) {
            avatarContainer.html(`<img src="/storage/${avatar}" alt="Avatar">`);
          } else {
            const initial = data.display_name ? data.display_name.charAt(0).toUpperCase() : '?';
            avatarContainer.html(`<div class="avatar-initial-lg">${initial}</div>`);
          }
          
          // Populate basic info
          $('#view_att_name').text(data.display_name || 'N/A');
          const contact = data.customer_contact || data.client?.contact || data.membership?.contact;
          $('#view_att_contact').text(contact ? `Contact: ${contact}` : '');
          
          // Populate check-in info (date and time only)
          $('#view_att_date').text(data.date ? new Date(data.date).toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric'
          }) : 'N/A');
          
          $('#view_att_time_in').text(data.time_in ? new Date('1970-01-01T' + data.time_in).toLocaleTimeString('en-US', {
            hour: 'numeric', minute: '2-digit', hour12: true
          }) : 'N/A');
          
          // Conditionally show sections based on customer type
          const customerType = data.customer_type;
          
          // Show/hide membership section
          if (data.membership && (customerType === 'Member')) {
            const m = data.membership;
            $('#view_att_membership_plan').text(m.formatted_plan_type || 'N/A');
            
            // Format status - match exact values from model: 'Active', 'Due soon', 'Expired'
            const mStatus = m.status || 'N/A';
            const mStatusClass = mStatus === 'Expired' ? 'badge-expired' :
                                 mStatus === 'Due soon' ? 'badge-warning' : 'badge-active';
            $('#view_att_membership_status').html(`<span class="badge ${mStatusClass}">${mStatus}</span>`);
            
            $('#view_att_membership_start').text(m.start_date ? new Date(m.start_date).toLocaleDateString('en-US', {
              year: 'numeric', month: 'short', day: 'numeric'
            }) : 'N/A');
            
            $('#view_att_membership_end').text(m.due_date ? new Date(m.due_date).toLocaleDateString('en-US', {
              year: 'numeric', month: 'short', day: 'numeric'
            }) : 'N/A');
            
            $('#view_att_membership_section').show();
          } else {
            $('#view_att_membership_section').hide();
          }
          
          // Show/hide client section  
          if (data.client && (customerType === 'Client' || customerType === 'Member')) {
            const c = data.client;
            $('#view_att_client_plan').text(c.formatted_plan_type || 'N/A');
            
            // Format status - match exact values from model: 'Active', 'Due soon', 'Expired'
            const cStatus = c.status || 'N/A';
            const cStatusClass = cStatus === 'Expired' ? 'badge-expired' :
                                 cStatus === 'Due soon' ? 'badge-warning' : 'badge-active';
            $('#view_att_client_status').html(`<span class="badge ${cStatusClass}">${cStatus}</span>`);
            
            $('#view_att_client_start').text(c.start_date ? new Date(c.start_date).toLocaleDateString('en-US', {
              year: 'numeric', month: 'short', day: 'numeric'
            }) : 'N/A');
            
            $('#view_att_client_end').text(c.due_date ? new Date(c.due_date).toLocaleDateString('en-US', {
              year: 'numeric', month: 'short', day: 'numeric'
            }) : 'N/A');
            
            $('#view_att_client_section').show();
          } else {
            $('#view_att_client_section').hide();
          }
          
          // Show walk-in notice only if customer type is Walk-in
          if (customerType === 'Walk-in') {
            $('#view_att_walkin_notice').show();
          } else {
            $('#view_att_walkin_notice').hide();
          }
          
          // Determine layout from DATA, not DOM visibility (modal is still hidden at this point)
          const showMembership = !!(data.membership && customerType === 'Member');
          const showClient = !!(data.client && (customerType === 'Client' || customerType === 'Member'));
          const hasBoth = showMembership && showClient;
          
          const $mSection = $('#view_att_membership_section');
          const $cSection = $('#view_att_client_section');
          const $row = $mSection.parent('.row');
          
          if (hasBoth) {
            // DUAL: Two cards side-by-side, fields stacked vertically inside each
            $mSection.removeClass('col-md-8 col-md-12').addClass('col-md-6');
            $cSection.removeClass('col-md-8 col-md-12').addClass('col-md-6');
            $mSection.find('.form-group').removeClass('col-md-6').addClass('col-12');
            $cSection.find('.form-group').removeClass('col-md-6').addClass('col-12');
            $row.addClass('justify-content-center');
          } else {
            // SINGLE: Full-width card (not centered), 2-column grid inside
            $mSection.removeClass('col-md-6 col-md-8').addClass('col-md-12');
            $cSection.removeClass('col-md-6 col-md-8').addClass('col-md-12');
            $mSection.find('.form-group').removeClass('col-12').addClass('col-md-6');
            $cSection.find('.form-group').removeClass('col-12').addClass('col-md-6');
            $row.removeClass('justify-content-center');
          }
          
          // Show modal
          $('#viewAttendanceModal').modal('show');
        } else {
          SessionsPage.showToast('error', response.message || 'Failed to load attendance details');
        }
      },
      error: function(xhr) {
        console.error('Attendance fetch error:', xhr);
        const errorMsg = xhr.responseJSON?.message || 'Failed to load attendance details';
        SessionsPage.showToast('error', errorMsg);
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

  // Confirm delete Attendance
  confirmDeleteAttendance: function(id, name) {
    this.pendingDelete = {
      type: 'attendance',
      id: id,
      name: name
    };
    $('#deleteType').val('attendance');
    $('#deleteId').val(id);
    $('#deleteConfirmText').html('Are you sure you want to delete the attendance record for <strong>' + name + '</strong>?');
    $('#deleteSessionConfirmInput').val('');
    $('#deleteSessionConfirmBtn').prop('disabled', true);
    $('#deleteSessionConfirmError').addClass('d-none');
    $('#deleteConfirmModal').modal('show');
  },

  // Execute delete (first confirmation)
  executeDelete: function() {
    const confirmInput = document.getElementById('deleteSessionConfirmInput');
    const confirmError = document.getElementById('deleteSessionConfirmError');
    if (!confirmInput || confirmInput.value.trim().toLowerCase() !== 'delete') {
      if (confirmError) confirmError.classList.remove('d-none');
      return;
    }
    $('#deleteConfirmModal').modal('hide');
    
    // Show double confirmation
    $('#confirmName').text(this.pendingDelete.name);
    $('#confirmInput').val('');
    $('#confirmError').addClass('d-none');
    $('#finalDeleteBtn').prop('disabled', true);
    $('#doubleConfirmModal').modal('show');
  },

  // Final delete (double confirmation)
  finalDelete: function() {
    const type = this.pendingDelete.type;
    const id = this.pendingDelete.id;
    
    let url = type === 'pt' 
      ? '/sessions/pt-schedule/' + id 
      : '/sessions/attendance/' + id;

    $.ajax({
      url: url,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#doubleConfirmModal').modal('hide');
        SessionsPage.showToast('success', 'Record deleted successfully!');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function() {
        $('#doubleConfirmModal').modal('hide');
        SessionsPage.showToast('error', 'Failed to delete record');
      }
    });
  },

  // Show toast notification
  showToast: function(type, message) {
    // Remove existing toasts
    $('.toast-container').remove();
    
    const bgClass = type === 'success' ? 'toast-success' : 
                    type === 'error' ? 'toast-error' : 'bg-info';
    const icon = type === 'success' ? 'mdi-check-circle' : 
                 type === 'error' ? 'mdi-alert-circle' : 'mdi-information';

    const toast = $(`
      <div class="toast-container">
        <div class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex align-items-center p-3">
            <i class="mdi ${icon} mr-2" style="font-size: 24px;"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="ml-2 close text-white" data-dismiss="toast" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
      </div>
    `);

    $('body').append(toast);
    
    // Auto-hide after 4 seconds
    setTimeout(() => {
      toast.fadeOut(300, function() {
        $(this).remove();
      });
    }, 4000);

    // Close button
    toast.find('.close').on('click', function() {
      toast.fadeOut(300, function() {
        $(this).remove();
      });
    });
  },

  // Bulk delete attendance records — show confirmation modal
  bulkDeleteAttendance: function() {
    const checkedIds = $('.attendance-checkbox:checked').map(function() {
      return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
      SessionsPage.showToast('error', 'Please select at least one attendance record to delete.');
      return;
    }

    this._bulkDeleteType = 'attendance';
    this._bulkDeleteIds = checkedIds;
    $('#bulkDeleteText').html('Are you sure you want to delete <strong>' + checkedIds.length + '</strong> attendance record(s)? This action cannot be undone.');
    $('#bulkDeleteConfirmModal').modal('show');
  },

  // Bulk delete PT schedules — show confirmation modal
  bulkDeletePT: function() {
    const checkedIds = $('.pt-checkbox:checked').map(function() {
      return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
      SessionsPage.showToast('error', 'Please select at least one PT schedule to delete.');
      return;
    }

    this._bulkDeleteType = 'pt';
    this._bulkDeleteIds = checkedIds;
    $('#bulkDeleteText').html('Are you sure you want to delete <strong>' + checkedIds.length + '</strong> PT schedule(s)? This action cannot be undone.');
    $('#bulkDeleteConfirmModal').modal('show');
  },

  // Execute bulk delete after modal confirmation
  executeBulkDelete: function() {
    var type = this._bulkDeleteType;
    var ids = this._bulkDeleteIds;
    var $btn = $('#bulkDeleteConfirmBtn');
    $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Deleting...');

    var url = type === 'attendance'
      ? $('#bulkDeleteAttendanceForm').attr('action')
      : $('#bulkDeletePTForm').attr('action');

    $.ajax({
      url: url,
      type: 'DELETE',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ids: ids
      },
      success: function(response) {
        $('#bulkDeleteConfirmModal').modal('hide');
        var label = type === 'attendance' ? 'Attendance records' : 'PT schedules';
        SessionsPage.showToast('success', response.message || label + ' deleted successfully.');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i class="mdi mdi-delete"></i> Delete');
        var message = xhr.responseJSON?.message || 'An error occurred while deleting records.';
        SessionsPage.showToast('error', message);
      }
    });
  },

  /**
   * Apply filter
   * @param {string} filterType - Type of filter: 'attendance_status' or 'pt_status'
   * @param {string} value - Filter value
   */
  applyFilter: function(filterType, value) {
    // Build URL with filter
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Update or remove filter parameter
    if (value !== 'all') {
      params.set(filterType, value);
    } else {
      params.delete(filterType);
    }
    
    // Navigate to filtered URL
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Clear all filters
   */
  clearAllFilters: function() {
    // Build URL without filter parameters
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Remove all filter parameters
    params.delete('attendance_status');
    params.delete('attendance_sort');
    params.delete('customer_type');
    params.delete('pt_status');
    params.delete('pt_sort');
    params.delete('pt_date');
    
    // Navigate to URL without filters
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Apply date filter
   * @param {string} filterType - Type of filter: 'pt_date'
   * @param {string} value - Date value (YYYY-MM-DD)
   */
  applyDateFilter: function(filterType, value) {
    if (!value) return;
    
    // Build URL with filter
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Set date parameter
    params.set(filterType, value);
    
    // Navigate to filtered URL
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Clear date filter
   * @param {string} filterType - Type of filter: 'pt_date'
   */
  clearDateFilter: function(filterType) {
    // Build URL without date parameter
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Remove date parameter
    params.delete(filterType);
    
    // Navigate to filtered URL
    window.location.href = `${url.pathname}?${params.toString()}`;
  },

  /**
   * Toggle filter section accordion
   * @param {HTMLElement} headerElement - The clicked filter section header
   * @param {Event} event - The click event
   */
  toggleFilterSection: function(headerElement, event) {
    // Prevent the dropdown from closing
    if (event) {
      event.stopPropagation();
    }
    const section = headerElement.closest('.filter-section');
    section.classList.toggle('active');
  }
};

// Initialize on document ready
$(document).ready(function() {
  SessionsPage.init();
});

// Make globally accessible for inline scripts
window.SessionsPage = SessionsPage;

// Wire up sessions single-delete confirm input
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
