<script>
  // ========== Search clear ==========
  function clearSearch(inputId, formId) {
    document.getElementById(inputId).value = '';
    document.getElementById(formId).submit();
  }

  // ========== Filter accordion toggle ==========
  function toggleFilterSection(header, event) {
    event.preventDefault();
    event.stopPropagation();
    const section = header.parentElement;
    section.classList.toggle('active');
  }

  // ========== Select All / Checkbox Logic ==========
  const selectAllCheckbox = document.getElementById('selectAllLogs');
  const deleteBtn = document.getElementById('deleteLogBtn');
  const countSpan = document.getElementById('logCount');

  function updateDeleteButton() {
    const checked = document.querySelectorAll('.log-checkbox:checked');
    const count = checked.length;
    countSpan.textContent = count;
  }

  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      document.querySelectorAll('.log-checkbox').forEach(cb => {
        cb.checked = this.checked;
      });
      updateDeleteButton();
    });
  }

  document.querySelectorAll('.log-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
      const allBoxes = document.querySelectorAll('.log-checkbox');
      const allChecked = document.querySelectorAll('.log-checkbox:checked');
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = allBoxes.length === allChecked.length && allBoxes.length > 0;
      }
      updateDeleteButton();
    });
  });

  // ========== Bulk Delete ==========
  function bulkDeleteLogs() {
    const checked = document.querySelectorAll('.log-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    if (ids.length === 0) {
      ToastUtils.showError('Please select at least 1 row before proceeding.', 'No Selection');
      return;
    }

    document.getElementById('bulkDeleteCount').textContent = ids.length;
    document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);

    // Reset confirm input
    const inp = document.getElementById('bulkDeleteLogsConfirmInput');
    const btn = document.getElementById('bulkDeleteLogsConfirmBtn');
    const err = document.getElementById('bulkDeleteLogsConfirmError');
    if (inp) inp.value = '';
    if (btn) btn.disabled = true;
    if (err) err.style.display = 'none';

    document.getElementById('bulkDeleteModal').classList.add('show');
  }

  function submitBulkDeleteLogs() {
    const inp = document.getElementById('bulkDeleteLogsConfirmInput');
    if (!inp || inp.value.trim().toLowerCase() !== 'delete') {
      const err = document.getElementById('bulkDeleteLogsConfirmError');
      if (err) err.style.display = '';
      return;
    }
    document.getElementById('bulkDeleteForm').submit();
  }

  // Wire up type-to-confirm input
  document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('bulkDeleteLogsConfirmInput');
    const confirmBtn = document.getElementById('bulkDeleteLogsConfirmBtn');
    if (confirmInput && confirmBtn) {
      confirmInput.addEventListener('input', function() {
        confirmBtn.disabled = this.value.trim().toLowerCase() !== 'delete';
      });
    }
  });

  function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.remove('show');
  }

  // ========== Close modals ==========
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('show');
      }
    });
  });

  // Close modals on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
    }
  });
</script>
