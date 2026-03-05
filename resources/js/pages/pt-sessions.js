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
      if (PTSessionsPage.validatePTScheduleDateTime()) {
        PTSessionsPage.submitPTSchedule();
      }
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

    // When confirm PT modal is dismissed (Go Back), re-open the Add PT form
    $('#confirmPTModal').on('hidden.bs.modal', function() {
      if (PTSessionsPage._ptConfirmSubmitted) {
        PTSessionsPage._ptConfirmSubmitted = false;
        return;
      }
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
        
        let ptPlan = 'Session';
        if (item.source === 'client') {
          ptPlan = item.formatted_plan_type || item.plan_type || 'Session';
        } else if (item.source === 'membership' && item.client_formatted_plan_type) {
          ptPlan = item.client_formatted_plan_type;
        }
        $('#pt_plan').val(ptPlan);

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
      if (enteredText && !PTSessionsPage._ptSelectedData) {
        PTSessionsPage._ptIsWalkIn = true;
        PTSessionsPage._ptSelectedData = null;

        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('walkin');
        $('#pt_age').val('').prop('readonly', false);
        $('#pt_sex').val('').prop('disabled', false);
        $('#pt_contact').val('').prop('readonly', false);
        $('#pt_plan').val('Session');

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

    // Reset when confirmation modal is closed
    $('#confirmPTModal').on('hidden.bs.modal', function() {
      PTSessionsPage._ptShowingConfirmation = false;
      if (!PTSessionsPage._ptConfirmSubmitted) {
        PTSessionsPage._resetPTFields();
        $('#pt_customer_select').val('');
        $('#pt_customer_id').val('');
        $('#pt_customer_type').val('');
        
        if ($('#pt_trainer').hasClass('select2-hidden-accessible')) {
          $('#pt_trainer').select2('destroy');
        }
      }
      PTSessionsPage._ptConfirmSubmitted = false;
    });
  },

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
    const dateInput = $('input[name="scheduled_date"]', '#bookNextForm').val();
    const timeInput = $('select[name="scheduled_time"]', '#bookNextForm').val();

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
    var customerName = $('#pt_customer_select').val();
    var trainerVal = $('#pt_trainer').val();
    var dateVal = $('[name="scheduled_date"]', '#addPTScheduleForm').val();
    var timeVal = $('[name="scheduled_time"]', '#addPTScheduleForm').val();
    var paymentVal = $('[name="payment_type"]', '#addPTScheduleForm').val();

    if (!customerName || !customerName.trim()) {
      PTSessionsPage.showToast('error', 'Please search and select a customer or type a walk-in name.');
      return;
    }
    if (!trainerVal) {
      PTSessionsPage.showToast('error', 'Please select a trainer.');
      return;
    }
    if (!dateVal) {
      PTSessionsPage.showToast('error', 'Please select a date.');
      return;
    }
    if (!timeVal) {
      PTSessionsPage.showToast('error', 'Please select a time.');
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
    $('#confirmPTModal').one('hidden.bs.modal', function() {
      $('#addPTScheduleModal').modal('show');
      PTSessionsPage._ptShowingConfirmation = false;
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
        PTSessionsPage.showToast('success', 'PT Schedule added successfully!');
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
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
        $('#edit_pt_name').val(displayName);
        $('#edit_pt_age').val(isWalkIn ? (data.customer_age || '') : (customerData?.age || ''));
        $('#edit_pt_sex').val(isWalkIn ? (data.customer_sex || '') : (customerData?.sex || ''));
        $('#edit_pt_contact').val(isWalkIn ? (data.customer_contact || '') : (customerData?.contact || ''));
        $('#edit_pt_plan').val(isWalkIn ? 'Walk-in' : (customerData?.plan_type || ''));
        $('#edit_trainer').val(data.trainer_name);
        $('#edit_date').val(data.scheduled_date);
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
        PTSessionsPage.showToast('success', 'PT Schedule updated successfully!');
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || 'Failed to update PT Schedule';
        PTSessionsPage.showToast('error', message);
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
        PTSessionsPage.showToast('success', 'Status updated to ' + status.charAt(0).toUpperCase() + status.slice(1));
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const message = xhr.responseJSON?.message || 'Failed to update status';
        PTSessionsPage.showToast('error', message);
      }
    });
  },

  // Open Book Next Session modal
  openBookNextModal: function(clientId, clientName) {
    $('#book_client_id').val(clientId);
    $('#book_client_name').val(clientName);
    
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateStr = tomorrow.toISOString().split('T')[0];
    $('#bookNextForm input[name="scheduled_date"]').val(dateStr);
    
    $('#bookNextModal').modal('show');
  },

  // Confirm cancel PT session
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
        PTSessionsPage.showToast('success', 'Session cancelled for ' + clientName);
        PTSessionsPage.refreshKPIs();

        setTimeout(function() {
          $('#rescheduleClientName').text(clientName);
          $('#rescheduleOfferModal').modal('show');
        }, 500);
      },
      error: function(xhr) {
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
      PTSessionsPage.openBookNextModal(clientId, clientName);
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
        setTimeout(() => location.reload(), 1000);
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
    $('#deleteConfirmModal').modal('show');
  },

  // Execute delete (first confirmation)
  executeDelete: function() {
    $('#deleteConfirmModal').modal('hide');
    
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
    
    let url = '/sessions/pt-schedule/' + id;

    $.ajax({
      url: url,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#doubleConfirmModal').modal('hide');
        PTSessionsPage.showToast('success', 'Record deleted successfully!');
        PTSessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function() {
        $('#doubleConfirmModal').modal('hide');
        PTSessionsPage.showToast('error', 'Failed to delete record');
      }
    });
  },

  // Show toast notification
  showToast: function(type, message) {
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
    
    setTimeout(() => {
      toast.fadeOut(300, function() {
        $(this).remove();
      });
    }, 4000);

    toast.find('.close').on('click', function() {
      toast.fadeOut(300, function() {
        $(this).remove();
      });
    });
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
