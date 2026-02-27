/**
 * Customer Attendance Page JavaScript
 * Handles all modals, AJAX operations, and interactions for the Customer Attendance page
 */

const AttendancePage = {
  // Track the current delete operation
  pendingDelete: {
    type: null,
    id: null,
    name: null
  },

  // Track walk-in state for Attendance form
  _attIsWalkIn: false,
  _attSelectedData: null,

  // Track bulk delete operation
  _bulkDeleteType: null,
  _bulkDeleteIds: [],

  // Initialize the page
  init: function() {
    this.bindEvents();
    this.initializeAttendanceModal();
    this.setupSearchForms();
    this.setupCheckboxes();
  },

  // Refresh KPI cards with latest data
  refreshKPIs: function() {
    $.ajax({
      url: '/sessions/kpis',
      method: 'GET',
      data: { type: 'attendance' },
      success: function(response) {
        if (response.success && response.data) {
          const data = response.data;
          
          // Update Customers Today
          $('#kpi_customers_today').text(data.customersEnteredToday);
          
          // Update Members Today
          $('#kpi_members_today').text(data.membersToday);
          
          // Update Walk-ins Today
          $('#kpi_walkins_today').text(data.walkInsToday);
          
          // Update Total This Month
          $('#kpi_total_month').text(data.totalThisMonth);
          
          // Update percentage change
          const percentChange = data.customerPercentChange;
          const $percentBadge = $('#kpi_customer_percent');
          
          if ($percentBadge.length) {
            const prefix = percentChange > 0 ? '+' : '';
            $percentBadge.text(prefix + Math.abs(percentChange) + '% Since yesterday');
            
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
    // Add Attendance form submission
    $('#addAttendanceForm').on('submit', function(e) {
      e.preventDefault();
      AttendancePage.submitAttendance();
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
    $('.modal').not('#bulkDeleteConfirmModal, #addAttendanceModal').on('hidden.bs.modal', function() {
      $(this).find('form')[0]?.reset();
      $(this).find('.is-invalid').removeClass('is-invalid');
      $(this).find('.invalid-feedback').remove();
      $(this).find('button[type="submit"]').prop('disabled', false);
      $('#confirmInput').val('');
      $('#confirmError').addClass('d-none');
      $('#finalDeleteBtn').prop('disabled', true);
    });

    // Reset bulk delete confirm button on close
    $('#bulkDeleteConfirmModal').on('hidden.bs.modal', function() {
      $('#bulkDeleteConfirmBtn').prop('disabled', false).html('<i class="mdi mdi-delete"></i> Delete');
    });
  },

  // Initialize attendance modal with Select2 AJAX search + walk-in support
  initializeAttendanceModal: function() {
    if (!window.AutocompleteUtils) return;

    const inputElement = document.getElementById('attendance_customer_select');
    if (!inputElement) return;

    this._attAutocomplete = AutocompleteUtils.init({
      inputElement: inputElement,
      apiUrl: '/sessions/customers/search',
      minChars: 1,
      debounceMs: 300,
      onSelect: (item) => {
        this._attIsWalkIn = false;
        this._attSelectedData = item;
        $('#attendance_customer_id').val(item.id);
        $('#attendance_customer_type').val(item.source);
      }
    });

    // Handle walk-in when user types a name not in the list
    $('#attendance_customer_select').on('blur', function() {
      var enteredText = $(this).val().trim();
      if (enteredText && !AttendancePage._attSelectedData) {
        AttendancePage._attIsWalkIn = true;
        AttendancePage._attSelectedData = null;
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
      AttendancePage._attIsWalkIn = false;
      AttendancePage._attSelectedData = null;
      $('#attendance_customer_select').val('');
      $('#attendance_customer_id').val('');
      $('#attendance_customer_type').val('');
    });
  },

  // Setup search forms to submit on enter
  setupSearchForms: function() {
    $('input[name="attendance_search"]').on('keypress', function(e) {
      if (e.which === 13) {
        $(this).closest('form').submit();
      }
    });
  },

  // Setup select all checkboxes
  setupCheckboxes: function() {
    $('#selectAllAttendance').on('change', function() {
      $('.attendance-checkbox').prop('checked', $(this).is(':checked'));
      AttendancePage.updateAttendanceCount();
    });

    $(document).on('change', '.attendance-checkbox', function() {
      AttendancePage.updateAttendanceCount();
    });
  },

  // Update attendance selected count
  updateAttendanceCount: function() {
    const count = $('.attendance-checkbox:checked').length;
    $('#selectedAttendanceCount').text(count);
  },

  // Submit new Attendance (supports clients, memberships, walk-ins)
  submitAttendance: function() {
    // Clear previous error states
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');

    var customerName = $('#attendance_customer_select').val();
    if (!customerName || !customerName.trim()) {
      AttendancePage.showToast('error', 'Please search and select a customer or type a walk-in name.');
      return;
    }

    const $submitBtn = $('#addAttendanceForm').find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Recording...');

    var data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      date: $('#attendance_date').val(),
      time_in: $('#attendance_time').val()
    };

    if (this._attIsWalkIn) {
      data.customer_name = customerName.replace(' (Walk-in)', '').trim();
    } else if (this._attSelectedData) {
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
        AttendancePage.showToast('success', 'Attendance recorded successfully!');
        AttendancePage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        $submitBtn.prop('disabled', false).html(originalText);

        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          const message = xhr.responseJSON?.message;

          if (message) {
            if (message.includes('already checked in')) {
              AttendancePage.showToast('error', message);
            } else if (message.includes('does not exist')) {
              AttendancePage.showToast('error', 'Selected customer is invalid. Please refresh and try again.');
            } else {
              AttendancePage.showToast('error', message);
            }
          } else if (Object.keys(errors).length > 0) {
            const firstError = Object.values(errors)[0]?.[0];
            AttendancePage.showToast('error', firstError || 'Validation failed');
          } else {
            AttendancePage.showToast('error', 'Please check your input and try again.');
          }
        } else if (xhr.status === 500) {
          AttendancePage.showToast('error', 'Server error occurred. Please try again later.');
        } else if (xhr.status === 0) {
          AttendancePage.showToast('error', 'Network error. Please check your connection.');
        } else {
          const message = xhr.responseJSON?.message || 'Failed to record attendance';
          AttendancePage.showToast('error', message);
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
          
          // Populate check-in info
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
          
          // Determine layout from DATA
          const showMembership = !!(data.membership && customerType === 'Member');
          const showClient = !!(data.client && (customerType === 'Client' || customerType === 'Member'));
          const hasBoth = showMembership && showClient;
          
          const $mSection = $('#view_att_membership_section');
          const $cSection = $('#view_att_client_section');
          const $row = $mSection.parent('.row');
          
          if (hasBoth) {
            $mSection.removeClass('col-md-8 col-md-12').addClass('col-md-6');
            $cSection.removeClass('col-md-8 col-md-12').addClass('col-md-6');
            $mSection.find('.form-group').removeClass('col-md-6').addClass('col-12');
            $cSection.find('.form-group').removeClass('col-md-6').addClass('col-12');
            $row.addClass('justify-content-center');
          } else {
            $mSection.removeClass('col-md-6 col-md-8').addClass('col-md-12');
            $cSection.removeClass('col-md-6 col-md-8').addClass('col-md-12');
            $mSection.find('.form-group').removeClass('col-12').addClass('col-md-6');
            $cSection.find('.form-group').removeClass('col-12').addClass('col-md-6');
            $row.removeClass('justify-content-center');
          }
          
          // Show modal
          $('#viewAttendanceModal').modal('show');
        } else {
          AttendancePage.showToast('error', response.message || 'Failed to load attendance details');
        }
      },
      error: function(xhr) {
        console.error('Attendance fetch error:', xhr);
        const errorMsg = xhr.responseJSON?.message || 'Failed to load attendance details';
        AttendancePage.showToast('error', errorMsg);
      }
    });
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
    
    $('#confirmName').text(this.pendingDelete.name);
    $('#confirmInput').val('');
    $('#confirmError').addClass('d-none');
    $('#finalDeleteBtn').prop('disabled', true);
    $('#doubleConfirmModal').modal('show');
  },

  // Final delete (double confirmation)
  finalDelete: function() {
    const id = this.pendingDelete.id;
    
    let url = '/sessions/attendance/' + id;

    $.ajax({
      url: url,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#doubleConfirmModal').modal('hide');
        AttendancePage.showToast('success', 'Record deleted successfully!');
        AttendancePage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function() {
        $('#doubleConfirmModal').modal('hide');
        AttendancePage.showToast('error', 'Failed to delete record');
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

  // Bulk delete attendance records
  bulkDeleteAttendance: function() {
    const checkedIds = $('.attendance-checkbox:checked').map(function() {
      return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
      AttendancePage.showToast('error', 'Please select at least one attendance record to delete.');
      return;
    }

    this._bulkDeleteType = 'attendance';
    this._bulkDeleteIds = checkedIds;
    $('#bulkDeleteText').html('Are you sure you want to delete <strong>' + checkedIds.length + '</strong> attendance record(s)? This action cannot be undone.');
    $('#bulkDeleteConfirmModal').modal('show');
  },

  // Execute bulk delete after modal confirmation
  executeBulkDelete: function() {
    var ids = this._bulkDeleteIds;
    var $btn = $('#bulkDeleteConfirmBtn');
    $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Deleting...');

    var url = $('#bulkDeleteAttendanceForm').attr('action');

    $.ajax({
      url: url,
      type: 'DELETE',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ids: ids
      },
      success: function(response) {
        $('#bulkDeleteConfirmModal').modal('hide');
        AttendancePage.showToast('success', response.message || 'Attendance records deleted successfully.');
        AttendancePage.refreshKPIs();
        setTimeout(() => location.reload(), 1000);
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i class="mdi mdi-delete"></i> Delete');
        var message = xhr.responseJSON?.message || 'An error occurred while deleting records.';
        AttendancePage.showToast('error', message);
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
    
    params.delete('attendance_status');
    params.delete('attendance_sort');
    params.delete('customer_type');
    
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
  AttendancePage.init();
});

// Make globally accessible for inline scripts
window.AttendancePage = AttendancePage;

// Backward compatibility alias - shared modals reference SessionsPage
window.SessionsPage = AttendancePage;
