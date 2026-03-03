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
    deleteBtn.disabled = count === 0;
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
    if (ids.length === 0) return;

    document.getElementById('bulkDeleteCount').textContent = ids.length;
    document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
    document.getElementById('bulkDeleteModal').classList.add('show');
  }

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
