/**
 * Trainer Management Page Module
 * Handles CRUD operations for trainer management
 */
const TrainerPage = (function() {
  'use strict';

  let config = {
    csrfToken: '',
    storeUrl: ''
  };

  let state = {
    deleteTrainerId: null
  };

  // ============================================
  // Initialization
  // ============================================
  function init(options) {
    config = { ...config, ...options };

    // Setup bulk selection
    if (typeof BulkSelection !== 'undefined') {
      BulkSelection.init({
        selectAllId: 'selectAll',
        checkboxClass: 'trainer-checkbox',
        selectedCountId: 'selectedTrainerCount'
      });
    }

    // Reset modals on close
    $('.modal').on('hidden.bs.modal', function() {
      const form = $(this).find('form')[0];
      if (form && form.id === 'addTrainerForm') {
        form.reset();
      }
      // Hide all overlays
      $(this).find('.confirm-overlay').hide();
    });
  }

  // ============================================
  // Add Trainer
  // ============================================
  function showTrainerConfirmModal() {
    const name = document.getElementById('newTrainerName').value.trim();
    const specialization = document.getElementById('newTrainerSpecialization').value.trim();
    const contact = document.getElementById('newTrainerContact').value.trim();

    // Basic validation
    if (!name) {
      ToastUtils.showError('Please enter the trainer\'s full name', 'Validation Error');
      document.getElementById('newTrainerName').focus();
      return;
    }

    // Set confirm overlay text
    document.getElementById('confirmTrainerNameText').textContent = name;
    document.getElementById('confirmTrainerSpecText').textContent = specialization || '—';
    document.getElementById('confirmTrainerContactText').textContent = contact || '—';

    // Show overlay
    document.getElementById('addTrainerConfirmOverlay').style.display = 'flex';
  }

  function backToTrainerAddForm() {
    document.getElementById('addTrainerConfirmOverlay').style.display = 'none';
  }

  function submitTrainerForm() {
    const btn = document.getElementById('confirmAddTrainerBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Creating...';

    const formData = new FormData();
    formData.append('full_name', document.getElementById('newTrainerName').value.trim());
    formData.append('specialization', document.getElementById('newTrainerSpecialization').value.trim());
    formData.append('contact_number', document.getElementById('newTrainerContact').value.trim());
    formData.append('emergency_contact', document.getElementById('newTrainerEmergencyContact').value.trim());
    formData.append('address', document.getElementById('newTrainerAddress').value.trim());

    fetch(config.storeUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
      if (status === 200 || status === 201) {
        ToastUtils.showSuccess(data.message || 'Trainer added successfully!', 'Success');
        setTimeout(() => location.reload(), 1000);
      } else if (status === 422 && data.errors) {
        const firstError = Object.values(data.errors)[0][0];
        ToastUtils.showError(firstError, 'Validation Error');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
      } else {
        ToastUtils.showError(data.message || 'An error occurred', 'Error');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An unexpected error occurred', 'Error');
      btn.disabled = false;
      btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
    });
  }

  // ============================================
  // Edit Trainer
  // ============================================
  function showEditTrainerConfirmModal(trainerId) {
    const name = document.getElementById('editTrainerName' + trainerId).value.trim();
    const specialization = document.getElementById('editTrainerSpecialization' + trainerId).value.trim();
    const contact = document.getElementById('editTrainerContact' + trainerId).value.trim();

    if (!name) {
      ToastUtils.showError('Please enter the trainer\'s full name', 'Validation Error');
      return;
    }

    document.getElementById('confirmEditTrainerName' + trainerId).textContent = name;
    document.getElementById('confirmEditTrainerSpec' + trainerId).textContent = specialization || '—';
    document.getElementById('confirmEditTrainerContact' + trainerId).textContent = contact || '—';

    document.getElementById('editTrainerConfirmOverlay' + trainerId).style.display = 'flex';
  }

  function backToEditTrainerForm(trainerId) {
    document.getElementById('editTrainerConfirmOverlay' + trainerId).style.display = 'none';
  }

  function submitEditTrainerForm(trainerId) {
    const btn = document.getElementById('confirmEditTrainerBtn' + trainerId);
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Updating...';

    const form = document.getElementById('editTrainerForm' + trainerId);
    const actionUrl = form.dataset.action;

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('full_name', document.getElementById('editTrainerName' + trainerId).value.trim());
    formData.append('specialization', document.getElementById('editTrainerSpecialization' + trainerId).value.trim());
    formData.append('contact_number', document.getElementById('editTrainerContact' + trainerId).value.trim());
    formData.append('emergency_contact', document.getElementById('editTrainerEmergencyContact' + trainerId).value.trim());
    formData.append('address', document.getElementById('editTrainerAddress' + trainerId).value.trim());

    fetch(actionUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
      if (status === 200 || status === 201) {
        ToastUtils.showSuccess(data.message || 'Trainer updated successfully!', 'Success');
        setTimeout(() => location.reload(), 1000);
      } else if (status === 422 && data.errors) {
        const firstError = Object.values(data.errors)[0][0];
        ToastUtils.showError(firstError, 'Validation Error');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Update';
      } else {
        ToastUtils.showError(data.message || 'An error occurred', 'Error');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Update';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An unexpected error occurred', 'Error');
      btn.disabled = false;
      btn.innerHTML = '<i class="mdi mdi-check"></i> Confirm Update';
    });
  }

  // ============================================
  // Delete Trainer
  // ============================================
  function openDeleteTrainerModal(trainerId, trainerName) {
    state.deleteTrainerId = trainerId;
    document.getElementById('deleteTrainerName').textContent = trainerName;

    const form = document.getElementById('deleteTrainerForm');
    form.action = '/trainers/' + trainerId;

    $('#deleteTrainerConfirmModal').modal('show');
  }

  function confirmDeleteTrainer() {
    if (!state.deleteTrainerId) return;

    const btn = document.getElementById('confirmDeleteTrainerBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Deleting...';

    fetch('/trainers/' + state.deleteTrainerId, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': config.csrfToken,
        'Accept': 'application/json'
      }
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
      if (status === 200) {
        ToastUtils.showSuccess(data.message || 'Trainer deleted successfully!', 'Success');
        $('#deleteTrainerConfirmModal').modal('hide');
        setTimeout(() => location.reload(), 1000);
      } else {
        ToastUtils.showError(data.message || 'An error occurred', 'Error');
        btn.disabled = false;
        btn.innerHTML = 'Delete Trainer';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      ToastUtils.showError('An unexpected error occurred', 'Error');
      btn.disabled = false;
      btn.innerHTML = 'Delete Trainer';
    });
  }

  // ============================================
  // Public API
  // ============================================
  return {
    init,
    showTrainerConfirmModal,
    backToTrainerAddForm,
    submitTrainerForm,
    showEditTrainerConfirmModal,
    backToEditTrainerForm,
    submitEditTrainerForm,
    openDeleteTrainerModal,
    confirmDeleteTrainer
  };
})();

// Expose globally
window.TrainerPage = TrainerPage;
