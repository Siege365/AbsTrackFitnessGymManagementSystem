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

  // Initialize the page
  init: function() {
    this.bindEvents();
    this.setupClientDropdown();
    this.setupSearchForms();
    this.setupCheckboxes();
    this.initializeAttendanceModal();
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
      SessionsPage.submitPTSchedule();
    });

    // Edit PT Schedule form submission
    $('#editPTScheduleForm').on('submit', function(e) {
      e.preventDefault();
      SessionsPage.saveEditPTSchedule();
    });

    // Book Next Session form submission
    $('#bookNextForm').on('submit', function(e) {
      e.preventDefault();
      SessionsPage.submitBookNext();
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

    // Reset modals on close
    $('.modal').on('hidden.bs.modal', function() {
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
  },

  // Setup client dropdown to auto-fill fields
  setupClientDropdown: function() {
    $('#pt_client_id').on('change', function() {
      const selected = $(this).find(':selected');
      if (selected.val()) {
        $('#pt_age').val(selected.data('age') || '');
        $('#pt_contact').val(selected.data('contact') || '');
        $('#pt_plan').val(selected.data('plan') || '');
        $('#pt_start_date').val(selected.data('start') || '');
        $('#pt_end_date').val(selected.data('end') || '');
        
        // Avatar preview
        const avatar = selected.data('avatar');
        if (avatar) {
          $('#pt_avatar_preview').html('<img src="/storage/' + avatar + '" alt="Avatar">');
        } else {
          $('#pt_avatar_preview').html('<i class="mdi mdi-account"></i>');
        }
      } else {
        $('#pt_age, #pt_contact, #pt_plan, #pt_start_date, #pt_end_date').val('');
        $('#pt_avatar_preview').html('<i class="mdi mdi-account"></i>');
      }
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

  // Initialize attendance modal with Select2 and auto-date/time
  initializeAttendanceModal: function() {
    // Initialize Select2 for client search
    if ($.fn.select2) {
      $('#attendance_client_id').select2({
        placeholder: 'Search and select client...',
        allowClear: true,
        dropdownParent: $('#addAttendanceModal'),
        width: '100%'
      });
    }

    // Update date and time when modal opens
    $('#addAttendanceModal').on('show.bs.modal', function() {
      // Set current date
      const today = new Date();
      const dateStr = today.getFullYear() + '-' + 
                      String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(today.getDate()).padStart(2, '0');
      $('#attendance_date').val(dateStr);

      // Set current time
      const timeStr = String(today.getHours()).padStart(2, '0') + ':' + 
                      String(today.getMinutes()).padStart(2, '0');
      $('#attendance_time').val(timeStr);

      // Clear client selection
      if ($.fn.select2) {
        $('#attendance_client_id').val(null).trigger('change');
      } else {
        $('#attendance_client_id').val('');
      }
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

  // Submit new PT Schedule
  submitPTSchedule: function() {
    const form = $('#addPTScheduleForm');
    const formData = new FormData(form[0]);
    
    $.ajax({
      url: '/sessions/pt-schedule',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#addPTScheduleModal').modal('hide');
        SessionsPage.showToast('success', 'PT Schedule added successfully!');
        SessionsPage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        const message = Object.values(errors)[0]?.[0] || 'Failed to add PT Schedule';
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
        $('#edit_pt_id').val(data.id);
        $('#edit_pt_name').val(data.client?.name || '');
        $('#edit_pt_age').val(data.client?.age || '');
        $('#edit_pt_contact').val(data.client?.contact || '');
        $('#edit_pt_plan').val(data.client?.plan_type || '');
        $('#edit_pt_start').val(data.client?.start_date || '');
        $('#edit_pt_end').val(data.client?.due_date || '');
        $('#edit_trainer').val(data.trainer_name);
        $('#edit_date').val(data.scheduled_date);
        $('#edit_time').val(data.scheduled_time?.substring(0, 5));
        $('#edit_payment').val(data.payment_type);
        $('#edit_pt_status').val(data.status?.charAt(0).toUpperCase() + data.status?.slice(1));

        // Avatar
        if (data.client?.avatar) {
          $('#edit_pt_avatar_preview').html('<img src="/storage/' + data.client.avatar + '" alt="Avatar">');
        } else {
          $('#edit_pt_avatar_preview').html('<i class="mdi mdi-account"></i>');
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

  // Submit new Attendance
  submitAttendance: function() {
    // Clear previous error states
    $('#attendance_client_id').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    // Validate client selection
    const clientId = $('#attendance_client_id').val();
    if (!clientId) {
      $('#attendance_client_id').addClass('is-invalid');
      $('#attendance_client_id').parent().append(
        '<div class="invalid-feedback d-block">Please select a client before submitting.</div>'
      );
      SessionsPage.showToast('error', 'Please select a client');
      return;
    }

    // Disable submit button to prevent double submission
    const $submitBtn = $('#addAttendanceForm').find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Recording...');

    const form = $('#addAttendanceForm');
    const formData = new FormData(form[0]);

    $.ajax({
      url: '/sessions/attendance',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
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
            // Check for specific error messages
            if (message.includes('already checked in')) {
              SessionsPage.showToast('error', message);
              $('#attendance_client_id').addClass('is-invalid');
              $('#attendance_client_id').parent().append(
                '<div class="invalid-feedback d-block">' + message + '</div>'
              );
            } else if (message.includes('does not exist')) {
              SessionsPage.showToast('error', 'Selected client is invalid. Please refresh and try again.');
              $('#attendance_client_id').addClass('is-invalid');
            } else {
              SessionsPage.showToast('error', message);
            }
          } else if (Object.keys(errors).length > 0) {
            // Show first error
            const firstError = Object.values(errors)[0]?.[0];
            SessionsPage.showToast('error', firstError || 'Validation failed');
            
            // Highlight fields with errors
            Object.keys(errors).forEach(function(field) {
              const $field = $('[name="' + field + '"]');
              $field.addClass('is-invalid');
              $field.parent().append(
                '<div class="invalid-feedback d-block">' + errors[field][0] + '</div>'
              );
            });
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

  // View attendance (optional - just shows info)
  viewAttendance: function(id) {
    // For now, just show a message. Can be expanded later.
    SessionsPage.showToast('info', 'Viewing attendance #' + id);
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
    $('#deleteConfirmModal').modal('show');
  },

  // Execute delete (first confirmation)
  executeDelete: function() {
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

  // Bulk delete attendance records
  bulkDeleteAttendance: function() {
    const checkedIds = $('.attendance-checkbox:checked').map(function() {
      return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
      SessionsPage.showToast('error', 'Please select at least one attendance record to delete.');
      return;
    }

    if (!confirm(`Are you sure you want to delete ${checkedIds.length} attendance record(s)?`)) {
      return;
    }

    $.ajax({
      url: $('#bulkDeleteAttendanceForm').attr('action'),
      type: 'DELETE',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ids: checkedIds
      },
      success: function(response) {
        SessionsPage.showToast('success', response.message || 'Attendance records deleted successfully.');
        SessionsPage.refreshKPIs();
        setTimeout(() => {
          location.reload();
        }, 1000);
      },
      error: function(xhr) {
        const message = xhr.responseJSON?.message || 'An error occurred while deleting attendance records.';
        SessionsPage.showToast('error', message);
      }
    });
  },

  // Bulk delete PT schedules
  bulkDeletePT: function() {
    const checkedIds = $('.pt-checkbox:checked').map(function() {
      return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
      SessionsPage.showToast('error', 'Please select at least one PT schedule to delete.');
      return;
    }

    if (!confirm(`Are you sure you want to delete ${checkedIds.length} PT schedule(s)?`)) {
      return;
    }

    $.ajax({
      url: $('#bulkDeletePTForm').attr('action'),
      type: 'DELETE',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ids: checkedIds
      },
      success: function(response) {
        SessionsPage.showToast('success', response.message || 'PT schedules deleted successfully.');
        SessionsPage.refreshKPIs();
        setTimeout(() => {
          location.reload();
        }, 1000);
      },
      error: function(xhr) {
        const message = xhr.responseJSON?.message || 'An error occurred while deleting PT schedules.';
        SessionsPage.showToast('error', message);
      }
    });
  }
};

// Initialize on document ready
$(document).ready(function() {
  SessionsPage.init();
});
