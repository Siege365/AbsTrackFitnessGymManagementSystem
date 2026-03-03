<!-- Common Utilities -->
@vite(['resources/js/common/table-dropdown.js'])
@vite(['resources/js/common/form-utils.js'])
@vite(['resources/js/common/bulk-selection.js'])
<!-- Page Module -->
@vite(['resources/js/pages/trainer.js'])
<script>
  // Initialize trainer page
  document.addEventListener('DOMContentLoaded', function() {
    TrainerPage.init({
      csrfToken: '{{ csrf_token() }}',
      storeUrl: '{{ route("trainers.store") }}'
    });

    const searchInput = document.getElementById('searchInputTrainer');
    const searchForm = document.getElementById('searchFormTrainer');
    if (searchInput && searchForm) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchForm.submit();
        }
      });
    }
  });

  // ============================================
  // Global function wrappers for onclick handlers
  // ============================================

  // --- Add Trainer ---
  function showTrainerConfirmModal() {
    // Validate contact before showing confirm
    const contactInput = document.getElementById('newTrainerContact');
    const contactValue = contactInput.value.trim();
    if (contactValue) {
      const digitsOnly = contactValue.replace(/\D/g, '');
      if (contactValue.startsWith('+63')) {
        if (digitsOnly.length !== 12) {
          ToastUtils.showError('Phone number with +63 must have exactly 12 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      } else {
        if (digitsOnly.length !== 11) {
          ToastUtils.showError('Phone number must have exactly 11 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
        if (!digitsOnly.startsWith('09')) {
          ToastUtils.showError('Phone number must start with 09', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      }
    }

    TrainerPage.showTrainerConfirmModal();
  }

  function backToTrainerAddForm() { TrainerPage.backToTrainerAddForm(); }
  function submitTrainerForm() { TrainerPage.submitTrainerForm(); }

  // --- Edit Trainer ---
  function showEditTrainerConfirmModal(trainerId) {
    const contactInput = document.getElementById('editTrainerContact' + trainerId);
    const contactValue = contactInput.value.trim();
    if (contactValue) {
      const digitsOnly = contactValue.replace(/\D/g, '');
      if (contactValue.startsWith('+63')) {
        if (digitsOnly.length !== 12) {
          ToastUtils.showError('Phone number with +63 must have exactly 12 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      } else {
        if (digitsOnly.length !== 11) {
          ToastUtils.showError('Phone number must have exactly 11 digits', 'Invalid Contact');
          contactInput.focus();
          return;
        }
        if (!digitsOnly.startsWith('09')) {
          ToastUtils.showError('Phone number must start with 09', 'Invalid Contact');
          contactInput.focus();
          return;
        }
      }
    }

    TrainerPage.showEditTrainerConfirmModal(trainerId);
  }

  function backToEditTrainerForm(trainerId) { TrainerPage.backToEditTrainerForm(trainerId); }
  function submitEditTrainerForm(trainerId) { TrainerPage.submitEditTrainerForm(trainerId); }

  // --- Delete Trainer ---
  function openDeleteTrainerModal(trainerId, trainerName) {
    TrainerPage.openDeleteTrainerModal(trainerId, trainerName);
  }
  function confirmDeleteTrainer() { TrainerPage.confirmDeleteTrainer(); }

  // --- Bulk Delete ---
  function bulkDeleteTrainers() {
    const checkedBoxes = document.querySelectorAll('.trainer-checkbox:checked');
    if (checkedBoxes.length === 0) {
      ToastUtils.showWarning('Please select at least one trainer to delete.', 'Warning');
      return false;
    }
    document.getElementById('bulkDeleteTrainerCount').textContent = checkedBoxes.length;
    $('#bulkDeleteTrainerConfirmModal').modal('show');
  }

  function confirmBulkDeleteTrainers() {
    const submitBtn = event.target;
    const form = document.getElementById('bulkDeleteTrainerForm');
    const checkedBoxes = document.querySelectorAll('.trainer-checkbox:checked');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
    form.querySelectorAll('input[name="trainer_ids[]"]').forEach(el => el.remove());
    checkedBoxes.forEach(checkbox => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'trainer_ids[]';
      input.value = checkbox.value;
      form.appendChild(input);
    });
    $('#bulkDeleteTrainerConfirmModal').modal('hide');
    form.submit();
  }

  // --- Phone Formatting ---
  function formatTrainerPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substring(0, 11);
    let formatted = '';
    if (value.length > 0) {
      formatted = value.substring(0, 4);
      if (value.length > 4) formatted += '-' + value.substring(4, 7);
      if (value.length > 7) formatted += '-' + value.substring(7, 11);
    }
    input.value = formatted;

    const errorDiv = document.getElementById(input.id + 'Error');
    let errorMessage = '';
    if (value.length === 0) {
      input.setCustomValidity('');
      if (errorDiv) { errorDiv.style.display = 'none'; input.classList.remove('is-invalid', 'is-valid'); }
    } else if (value.length < 11) {
      errorMessage = `Phone number must have exactly 11 digits. Current: ${value.length} digit${value.length !== 1 ? 's' : ''}`;
      input.setCustomValidity(errorMessage);
      if (errorDiv) { errorDiv.textContent = errorMessage; errorDiv.style.display = 'block'; input.classList.add('is-invalid'); input.classList.remove('is-valid'); }
    } else if (!value.startsWith('09')) {
      errorMessage = 'Phone number must start with 09';
      input.setCustomValidity(errorMessage);
      if (errorDiv) { errorDiv.textContent = errorMessage; errorDiv.style.display = 'block'; input.classList.add('is-invalid'); input.classList.remove('is-valid'); }
    } else {
      input.setCustomValidity('');
      if (errorDiv) { errorDiv.style.display = 'none'; input.classList.remove('is-invalid'); input.classList.add('is-valid'); }
    }
  }

  // Dropdown toggle
  document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        if (menu !== this.nextElementSibling) menu.classList.remove('show');
      });
      const menu = this.nextElementSibling;
      if (menu?.classList.contains('dropdown-menu')) menu.classList.toggle('show');
    });
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => menu.classList.remove('show'));
    }
  });
</script>
