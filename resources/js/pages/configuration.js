/**
 * Configuration Page — Plan Management
 * Handles CRUD operations with double-confirmation modals.
 */

// ── State ──
let pendingDeleteId = null;
let pendingDeleteName = '';
let isEditMode = false;

// ── Section Toggle ──
document.querySelectorAll('.config-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.config-toggle-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const section = this.dataset.section;
        document.querySelectorAll('.config-section').forEach(s => s.classList.remove('active'));
        document.getElementById(section === 'manage' ? 'manageSection' : 'previewSection').classList.add('active');
    });
});

// ── Toggle Helpers ──
document.addEventListener('DOMContentLoaded', function () {
    const studentToggle = document.getElementById('planRequiresStudent');
    const buddyToggle = document.getElementById('planRequiresBuddy');
    const buddyCountGroup = document.getElementById('buddyCountGroup');

    if (studentToggle) {
        studentToggle.addEventListener('change', function () {
            document.getElementById('studentToggleLabel').textContent = this.checked ? 'Yes' : 'No';
            // Student and buddy are mutually exclusive
            if (this.checked && buddyToggle) {
                buddyToggle.checked = false;
                document.getElementById('buddyToggleLabel').textContent = 'No';
                buddyCountGroup.style.display = 'none';
            }
        });
    }

    if (buddyToggle) {
        buddyToggle.addEventListener('change', function () {
            document.getElementById('buddyToggleLabel').textContent = this.checked ? 'Yes' : 'No';
            buddyCountGroup.style.display = this.checked ? '' : 'none';
            // Mutually exclusive
            if (this.checked && studentToggle) {
                studentToggle.checked = false;
                document.getElementById('studentToggleLabel').textContent = 'No';
            }
        });
    }

    // Wire confirm input for plan deletion
    const deletePlanInput = document.getElementById('deletePlanConfirmInput');
    const deletePlanBtn = document.getElementById('deletePlanConfirmBtn');
    if (deletePlanInput && deletePlanBtn) {
        deletePlanInput.addEventListener('input', function () {
            deletePlanBtn.disabled = this.value.trim().toLowerCase() !== 'delete';
        });
    }
});

// ============================================
// ADD PLAN
// ============================================
window.openAddPlanModal = function (category) {
    isEditMode = false;
    resetPlanForm();
    document.getElementById('planCategory').value = category;
    document.getElementById('planModalTitle').textContent =
        category === 'membership' ? 'Add Membership Plan' : 'Add Personal Training Plan';

    // Show/hide membership-specific options
    const membershipOptions = document.getElementById('membershipOptions');
    if (membershipOptions) {
        membershipOptions.style.display = category === 'membership' ? '' : 'none';
    }

    $('#planModal').modal('show');
};

// ============================================
// EDIT PLAN
// ============================================
window.openEditPlanModal = function (id, plan) {
    isEditMode = true;
    resetPlanForm();

    document.getElementById('planId').value = id;
    document.getElementById('planCategory').value = plan.category;
    document.getElementById('planModalTitle').textContent = 'Edit Plan: ' + plan.plan_name;
    document.getElementById('planName').value = plan.plan_name;
    document.getElementById('planPrice').value = plan.price;
    document.getElementById('planDurationDays').value = plan.duration_days;
    document.getElementById('planDurationLabel').value = plan.duration_label || '';
    document.getElementById('planBadgeText').value = plan.badge_text || '';
    document.getElementById('planBadgeColor').value = plan.badge_color || '';
    document.getElementById('planDescription').value = plan.description || '';
    document.getElementById('planIsActive').value = plan.is_active ? '1' : '0';

    // Membership-specific
    const membershipOptions = document.getElementById('membershipOptions');
    if (membershipOptions) {
        membershipOptions.style.display = plan.category === 'membership' ? '' : 'none';
    }

    if (plan.category === 'membership') {
        const studentToggle = document.getElementById('planRequiresStudent');
        const buddyToggle = document.getElementById('planRequiresBuddy');
        const buddyCountGroup = document.getElementById('buddyCountGroup');

        if (studentToggle) {
            studentToggle.checked = !!plan.requires_student;
            document.getElementById('studentToggleLabel').textContent = plan.requires_student ? 'Yes' : 'No';
        }
        if (buddyToggle) {
            buddyToggle.checked = !!plan.requires_buddy;
            document.getElementById('buddyToggleLabel').textContent = plan.requires_buddy ? 'Yes' : 'No';
            buddyCountGroup.style.display = plan.requires_buddy ? '' : 'none';
        }
        if (plan.requires_buddy && plan.buddy_count) {
            document.getElementById('planBuddyCount').value = plan.buddy_count;
        }
    }

    $('#planModal').modal('show');
};

// ============================================
// SAVE — Double Confirmation
// ============================================
window.confirmSavePlan = function () {
    // Basic validation
    const name = document.getElementById('planName').value.trim();
    const price = document.getElementById('planPrice').value;
    const duration = document.getElementById('planDurationDays').value;

    if (!name || !price || !duration) {
        showToast('error', 'Please fill in all required fields.');
        return;
    }

    // Show step 1
    document.getElementById('confirmSavePlanName').textContent = name;
    $('#planModal').modal('hide');
    setTimeout(() => {
        $('#confirmSaveModal').modal('show');
    }, 300);
};

window.confirmSaveStep2 = function () {
    const name = document.getElementById('planName').value.trim();
    const actionWord = isEditMode ? 'update' : 'create';
    document.getElementById('finalSaveText').textContent =
        `This will ${actionWord} the plan "${name}". Confirm?`;

    $('#confirmSaveModal').modal('hide');
    setTimeout(() => {
        $('#finalSaveModal').modal('show');
    }, 300);
};

window.goBackToSaveStep1 = function () {
    $('#finalSaveModal').modal('hide');
    setTimeout(() => {
        $('#confirmSaveModal').modal('show');
    }, 300);
};

window.executeSavePlan = function () {
    const planId = document.getElementById('planId').value;
    const category = document.getElementById('planCategory').value;

    const payload = {
        category: category,
        plan_name: document.getElementById('planName').value.trim(),
        price: parseFloat(document.getElementById('planPrice').value),
        duration_days: parseInt(document.getElementById('planDurationDays').value),
        duration_label: document.getElementById('planDurationLabel').value.trim() || null,
        badge_text: document.getElementById('planBadgeText').value.trim() || null,
        badge_color: document.getElementById('planBadgeColor').value || null,
        description: document.getElementById('planDescription').value.trim() || null,
        is_active: document.getElementById('planIsActive').value === '1',
        requires_student: false,
        requires_buddy: false,
        buddy_count: 1,
    };

    if (category === 'membership') {
        payload.requires_student = document.getElementById('planRequiresStudent')?.checked || false;
        payload.requires_buddy = document.getElementById('planRequiresBuddy')?.checked || false;
        payload.buddy_count = payload.requires_buddy
            ? parseInt(document.getElementById('planBuddyCount').value) || 2
            : 1;
    }

    const url = isEditMode
        ? window.configRoutes.update + '/' + planId
        : window.configRoutes.store;

    const method = isEditMode ? 'PUT' : 'POST';

    // Disable button to prevent double clicks
    const saveBtn = document.querySelector('#finalSaveModal .btn-confirm-final');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
    }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        $('#finalSaveModal').modal('hide');
        showToast('success', data.message || 'Plan saved successfully.');
        // Reload the page to reflect changes
        setTimeout(() => {
            window.location.href = window.configRoutes.index;
        }, 600);
    })
    .catch(err => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="mdi mdi-check"></i> Yes, Save';
        }
        const message = err.message || (err.errors ? Object.values(err.errors).flat().join(', ') : 'An error occurred.');
        showToast('error', message);
    });
};

// ============================================
// DELETE — Double Confirmation
// ============================================
window.confirmDeletePlan = function (id, name) {
    pendingDeleteId = id;
    pendingDeleteName = name;
    document.getElementById('confirmDeletePlanName').textContent = name;
    $('#confirmDeleteModal').modal('show');
};

window.confirmDeleteStep2 = function () {
    document.getElementById('finalDeletePlanName').textContent = pendingDeleteName;
    // Reset confirm input
    const confirmInput = document.getElementById('deletePlanConfirmInput');
    const confirmBtn = document.getElementById('deletePlanConfirmBtn');
    const confirmError = document.getElementById('deletePlanConfirmError');
    if (confirmInput) confirmInput.value = '';
    if (confirmBtn) confirmBtn.disabled = true;
    if (confirmError) confirmError.classList.add('d-none');
    $('#confirmDeleteModal').modal('hide');
    setTimeout(() => {
        $('#finalDeleteModal').modal('show');
    }, 300);
};

window.goBackToDeleteStep1 = function () {
    $('#finalDeleteModal').modal('hide');
    setTimeout(() => {
        $('#confirmDeleteModal').modal('show');
    }, 300);
};

window.executeDeletePlan = function () {
    if (!pendingDeleteId) return;

    const confirmInput = document.getElementById('deletePlanConfirmInput');
    const confirmError = document.getElementById('deletePlanConfirmError');
    if (!confirmInput || confirmInput.value.trim().toLowerCase() !== 'delete') {
        if (confirmError) confirmError.classList.remove('d-none');
        return;
    }

    const deleteBtn = document.getElementById('deletePlanConfirmBtn');
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Deleting...';
    }

    fetch(window.configRoutes.destroy + '/' + pendingDeleteId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        $('#finalDeleteModal').modal('hide');
        showToast('success', data.message || 'Plan deleted successfully.');
        setTimeout(() => {
            window.location.href = window.configRoutes.index;
        }, 600);
    })
    .catch(err => {
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="mdi mdi-delete"></i> Yes, Delete';
        }
        const message = err.message || 'Failed to delete plan.';
        showToast('error', message);
    });
};

// ============================================
// HELPERS
// ============================================
function resetPlanForm() {
    document.getElementById('planId').value = '';
    document.getElementById('planName').value = '';
    document.getElementById('planPrice').value = '';
    document.getElementById('planDurationDays').value = '';
    document.getElementById('planDurationLabel').value = '';
    document.getElementById('planBadgeText').value = '';
    document.getElementById('planBadgeColor').value = '';
    document.getElementById('planDescription').value = '';
    document.getElementById('planIsActive').value = '1';

    const studentToggle = document.getElementById('planRequiresStudent');
    const buddyToggle = document.getElementById('planRequiresBuddy');
    const buddyCountGroup = document.getElementById('buddyCountGroup');

    if (studentToggle) {
        studentToggle.checked = false;
        document.getElementById('studentToggleLabel').textContent = 'No';
    }
    if (buddyToggle) {
        buddyToggle.checked = false;
        document.getElementById('buddyToggleLabel').textContent = 'No';
    }
    if (buddyCountGroup) {
        buddyCountGroup.style.display = 'none';
        document.getElementById('planBuddyCount').value = '2';
    }
}

function showToast(type, message) {
    // Use the global ToastUtils if available
    if (typeof ToastUtils !== 'undefined' && ToastUtils.show) {
        ToastUtils.show(type, message);
        return;
    }

    // Fallback: use session flash style alert
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const wrapper = document.createElement('div');
    wrapper.className = `alert ${alertClass} alert-dismissible fade show`;
    wrapper.setAttribute('role', 'alert');
    wrapper.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;min-width:300px;max-width:500px;box-shadow:0 4px 12px rgba(0,0,0,0.3);border-radius:8px;';
    wrapper.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    `;
    document.body.appendChild(wrapper);
    setTimeout(() => {
        wrapper.classList.remove('show');
        setTimeout(() => wrapper.remove(), 300);
    }, 4000);
}
