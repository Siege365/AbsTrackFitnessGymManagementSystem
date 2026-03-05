/**
 * Configuration Page � Plan & Category Management
 * Uses confirm-overlay pattern (consistent with Inventory/Memberships)
 * Event delegation for kebab menu actions
 */

// -- State --
let pendingDeleteId = null;
let pendingDeleteName = '';
let isEditMode = false;
let pendingCategoryDeleteName = '';
let pendingCategoryDeleteCount = 0;
let pendingToggleStatusId = null;
let pendingToggleAction = null;
let pendingTogglePlanName = '';
let pendingTogglePlanCategory = '';

// -- Section Toggle (dynamic) --
document.querySelectorAll('.config-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.config-toggle-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const sectionId = this.dataset.section;
        document.querySelectorAll('.config-section').forEach(s => s.classList.remove('active'));
        const target = document.getElementById(sectionId + 'Section');
        if (target) target.classList.add('active');
    });
});

// -- DOMContentLoaded � Wire Up Listeners --
document.addEventListener('DOMContentLoaded', function () {

    // Toggle helpers for student/buddy
    const studentToggle = document.getElementById('planRequiresStudent');
    const buddyToggle   = document.getElementById('planRequiresBuddy');
    const buddyCountGrp = document.getElementById('buddyCountGroup');

    if (studentToggle) {
        studentToggle.addEventListener('change', function () {
            document.getElementById('studentToggleLabel').textContent = this.checked ? 'Yes' : 'No';
            if (this.checked && buddyToggle) {
                buddyToggle.checked = false;
                document.getElementById('buddyToggleLabel').textContent = 'No';
                buddyCountGrp.style.display = 'none';
            }
        });
    }

    if (buddyToggle) {
        buddyToggle.addEventListener('change', function () {
            document.getElementById('buddyToggleLabel').textContent = this.checked ? 'Yes' : 'No';
            buddyCountGrp.style.display = this.checked ? '' : 'none';
            if (this.checked && studentToggle) {
                studentToggle.checked = false;
                document.getElementById('studentToggleLabel').textContent = 'No';
            }
        });
    }

    // Color picker grid
    document.querySelectorAll('.color-picker-swatch').forEach(swatch => {
        swatch.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.color-picker-swatch').forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('editCategoryColor').value = this.dataset.color;
        });
    });

    // Badge color picker sync
    const badgeColorPicker = document.getElementById('planBadgeColor');
    const badgeColorHex = document.getElementById('planBadgeColorHex');

    if (badgeColorPicker && badgeColorHex) {
        // Sync color picker to hex input
        badgeColorPicker.addEventListener('input', function () {
            badgeColorHex.value = this.value.toUpperCase();
        });

        // Clicking hex input opens color picker
        badgeColorHex.addEventListener('click', function () {
            badgeColorPicker.click();
        });
    }
    // ── Search Functionality ──
    // Membership Plans Search
    const membershipSearch = document.getElementById('membershipSearchInput');
    if (membershipSearch) {
        membershipSearch.addEventListener('input', function () {
            filterTable('membershipPlansTable', this.value.toLowerCase());
        });
    }

    // PT Plans Search
    const ptSearch = document.getElementById('ptSearchInput');
    if (ptSearch) {
        ptSearch.addEventListener('input', function () {
            filterTable('ptPlansTable', this.value.toLowerCase());
        });
    }

    // Categories Search
    const categoriesSearch = document.getElementById('categoriesSearchInput');
    if (categoriesSearch) {
        categoriesSearch.addEventListener('input', function () {
            filterTable('categoriesTable', this.value.toLowerCase());
        });
    }
    // -- Event Delegation for Kebab Menu Items --
    document.addEventListener('click', function (e) {
        // Edit Plan
        const editPlanBtn = e.target.closest('.edit-plan-btn');
        if (editPlanBtn) {
            const plan = JSON.parse(editPlanBtn.dataset.plan);
            openEditPlanModal(parseInt(editPlanBtn.dataset.planId), plan);
            return;
        }
        // Toggle Plan Status (Enable/Disable)
        const toggleStatusBtn = e.target.closest('.toggle-plan-status-btn');
        if (toggleStatusBtn) {
            openToggleStatusModal(
                parseInt(toggleStatusBtn.dataset.planId),
                toggleStatusBtn.dataset.action,
                toggleStatusBtn.dataset.planName,
                toggleStatusBtn.dataset.planCategory
            );
            return;
        }
        // Delete Plan
        const deletePlanBtn = e.target.closest('.delete-plan-btn');
        if (deletePlanBtn) {
            openDeletePlanModal(
                parseInt(deletePlanBtn.dataset.planId),
                deletePlanBtn.dataset.planName,
                deletePlanBtn.dataset.planCategory,
                deletePlanBtn.dataset.planPrice,
                deletePlanBtn.dataset.planStatus
            );
            return;
        }

        // Edit Category
        const editCatBtn = e.target.closest('.edit-category-btn');
        if (editCatBtn) {
            openEditCategoryModal(editCatBtn.dataset.categoryName, editCatBtn.dataset.categoryColor);
            return;
        }

        // Delete Category
        const deleteCatBtn = e.target.closest('.delete-category-btn');
        if (deleteCatBtn) {
            openDeleteCategoryModal(
                deleteCatBtn.dataset.categoryName,
                parseInt(deleteCatBtn.dataset.productCount)
            );
            return;
        }
    });
});


// ============================================
// PLAN � ADD
// ============================================
window.openAddPlanModal = function (category) {
    isEditMode = false;
    resetPlanForm();
    document.getElementById('planCategory').value = category;
    document.getElementById('planModalTitle').textContent =
        category === 'membership' ? 'Add Membership Plan' : 'Add Personal Training Plan';

    const membershipOptions = document.getElementById('membershipOptions');
    if (membershipOptions) {
        membershipOptions.style.display = category === 'membership' ? '' : 'none';
    }

    // Auto-generate a random badge color for new plans
    const randomColor = generateRandomColor();
    document.getElementById('planBadgeColor').value = randomColor;
    document.getElementById('planBadgeColorHex').value = randomColor;

    $('#planModal').modal('show');
};

// Generate random vibrant color
function generateRandomColor() {
    const colors = [
        '#4CAF50', // Green
        '#2196F3', // Blue
        '#FF9800', // Orange
        '#F44336', // Red
        '#9C27B0', // Purple
        '#FF5722', // Deep Orange
        '#00BCD4', // Cyan
        '#FFEB3B', // Yellow
        '#E91E63', // Pink
        '#3F51B5', // Indigo
        '#FFC107', // Amber
        '#8BC34A', // Light Green
        '#009688', // Teal
        '#FF6F00', // Orange Accent
        '#6A1B9A'  // Deep Purple
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}


// ============================================
// PLAN � EDIT (via kebab delegation)
// ============================================
function openEditPlanModal(id, plan) {
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
    const badgeColor = plan.badge_color || '#FFA726';
    document.getElementById('planBadgeColor').value = badgeColor;
    document.getElementById('planBadgeColorHex').value = badgeColor;
    document.getElementById('planDescription').value = plan.description || '';
    document.getElementById('planIsActive').value = plan.is_active ? '1' : '0';

    const membershipOptions = document.getElementById('membershipOptions');
    if (membershipOptions) {
        membershipOptions.style.display = plan.category === 'membership' ? '' : 'none';
    }

    if (plan.category === 'membership') {
        const st = document.getElementById('planRequiresStudent');
        const bt = document.getElementById('planRequiresBuddy');
        const bcg = document.getElementById('buddyCountGroup');

        if (st) {
            st.checked = !!plan.requires_student;
            document.getElementById('studentToggleLabel').textContent = plan.requires_student ? 'Yes' : 'No';
        }
        if (bt) {
            bt.checked = !!plan.requires_buddy;
            document.getElementById('buddyToggleLabel').textContent = plan.requires_buddy ? 'Yes' : 'No';
            bcg.style.display = plan.requires_buddy ? '' : 'none';
        }
        if (plan.requires_buddy && plan.buddy_count) {
            document.getElementById('planBuddyCount').value = plan.buddy_count;
        }
    }

    $('#planModal').modal('show');
}


// ============================================
// PLAN � SAVE (confirm overlay)
// ============================================
window.confirmSavePlan = function () {
    const name     = document.getElementById('planName').value.trim();
    const price    = document.getElementById('planPrice').value;
    const duration = document.getElementById('planDurationDays').value;

    if (!name || !price || !duration) {
        showToast('error', 'Please fill in all required fields.');
        return;
    }

    const category = document.getElementById('planCategory').value;
    document.getElementById('confirmPlanNameText').textContent     = name;
    document.getElementById('confirmPlanCategoryText').textContent = category === 'membership' ? 'Membership' : 'Personal Training';
    document.getElementById('confirmPlanPriceText').textContent    = '\u20B1' + parseFloat(price).toFixed(2);
    document.getElementById('confirmPlanDurationText').textContent = duration + (duration == 1 ? ' day' : ' days');

    document.getElementById('planConfirmOverlay').style.display = 'flex';
};

window.backToPlanForm = function () {
    document.getElementById('planConfirmOverlay').style.display = 'none';
};

window.executeSavePlan = function () {
    const planId   = document.getElementById('planId').value;
    const category = document.getElementById('planCategory').value;

    const payload = {
        category:        category,
        plan_name:       document.getElementById('planName').value.trim(),
        price:           parseFloat(document.getElementById('planPrice').value),
        duration_days:   parseInt(document.getElementById('planDurationDays').value),
        duration_label:  document.getElementById('planDurationLabel').value.trim() || null,
        badge_text:      document.getElementById('planBadgeText').value.trim() || null,
        badge_color:     document.getElementById('planBadgeColor').value || null,
        description:     document.getElementById('planDescription').value.trim() || null,
        is_active:       document.getElementById('planIsActive').value === '1',
        requires_student: false,
        requires_buddy:   false,
        buddy_count:      1,
    };

    if (category === 'membership') {
        payload.requires_student = document.getElementById('planRequiresStudent')?.checked || false;
        payload.requires_buddy   = document.getElementById('planRequiresBuddy')?.checked || false;
        payload.buddy_count      = payload.requires_buddy
            ? parseInt(document.getElementById('planBuddyCount').value) || 2
            : 1;
    }

    const url    = isEditMode ? window.configRoutes.update + '/' + planId : window.configRoutes.store;
    const method = isEditMode ? 'PUT' : 'POST';

    const confirmBtn = document.getElementById('confirmSavePlanBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
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
        if (!response.ok) return response.json().then(err => { throw err; });
        return response.json();
    })
    .then(data => {
        document.getElementById('planConfirmOverlay').style.display = 'none';
        $('#planModal').modal('hide');
        showToast('success', data.message || 'Plan saved successfully.');
        setTimeout(() => { window.location.href = window.configRoutes.index; }, 600);
    })
    .catch(err => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
        }
        const message = err.message || (err.errors ? Object.values(err.errors).flat().join(', ') : 'An error occurred.');
        showToast('error', message);
    });
};


// ============================================
// PLAN � DELETE (single-step modal)
// ============================================
function openDeletePlanModal(id, name, category, price, status) {
    pendingDeleteId   = id;
    pendingDeleteName = name;

    document.getElementById('deletePlanName').textContent     = name;
    document.getElementById('deletePlanCategory').textContent = category;
    document.getElementById('deletePlanPrice').textContent    = price;
    document.getElementById('deletePlanStatus').textContent   = status;

    $('#deletePlanModal').modal('show');
}

window.executeDeletePlan = function () {
    if (!pendingDeleteId) return;

    const deleteBtn = document.querySelector('#deletePlanModal .btn-danger');
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
        if (!response.ok) return response.json().then(err => { throw err; });
        return response.json();
    })
    .then(data => {
        $('#deletePlanModal').modal('hide');
        showToast('success', data.message || 'Plan deleted successfully.');
        setTimeout(() => { window.location.href = window.configRoutes.index; }, 600);
    })
    .catch(err => {
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="mdi mdi-delete"></i> Delete Plan';
        }
        showToast('error', err.message || 'Failed to delete plan.');
    });
};


// ============================================
// PLAN — TOGGLE STATUS (Enable/Disable)
// ============================================
function openToggleStatusModal(planId, action, planName, planCategory) {
    pendingToggleStatusId = planId;
    pendingToggleAction = action;
    pendingTogglePlanName = planName;
    pendingTogglePlanCategory = planCategory;

    const isEnabling = action === 'enable';
    const actionText = isEnabling ? 'Enable' : 'Disable';
    const currentStatus = isEnabling ? 'Inactive' : 'Active';
    const newStatus = isEnabling ? 'Active' : 'Inactive';

    // Update modal title
    document.getElementById('toggleStatusModalLabel').textContent = `Confirm ${actionText} Plan`;

    // Update alert styling and message
    const alertEl = document.getElementById('toggleStatusAlert');
    const iconEl = document.getElementById('toggleStatusIcon');
    const messageEl = document.getElementById('toggleStatusMessage');

    if (isEnabling) {
        alertEl.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
        alertEl.style.borderColor = 'rgba(76, 175, 80, 0.3)';
        alertEl.style.color = '#4CAF50';
        iconEl.className = 'mdi mdi-check-circle';
        messageEl.textContent = `Are you sure you want to enable this plan? It will be available for purchase.`;
    } else {
        alertEl.style.backgroundColor = 'rgba(239, 83, 80, 0.1)';
        alertEl.style.borderColor = 'rgba(239, 83, 80, 0.3)';
        alertEl.style.color = '#EF5350';
        iconEl.className = 'mdi mdi-alert-circle';
        messageEl.textContent = `Are you sure you want to disable this plan? Existing members will keep their current memberships, but new purchases will be blocked.`;
    }

    // Update plan details
    document.getElementById('toggleStatusPlanName').textContent = planName;
    document.getElementById('toggleStatusPlanCategory').textContent = planCategory;
    document.getElementById('toggleStatusCurrentStatus').textContent = currentStatus;
    document.getElementById('toggleStatusNewStatus').textContent = newStatus;

    // Update confirmation button
    const confirmBtn = document.getElementById('confirmToggleStatusBtn');
    const btnIcon = confirmBtn.querySelector('.mdi');
    const btnText = document.getElementById('toggleStatusBtnText');

    if (isEnabling) {
        confirmBtn.className = 'btn btn-update';
        btnIcon.className = 'mdi mdi-check-circle';
        btnText.textContent = 'Enable Plan';
    } else {
        confirmBtn.className = 'btn btn-danger';
        btnIcon.className = 'mdi mdi-close-circle';
        btnText.textContent = 'Disable Plan';
    }

    $('#toggleStatusModal').modal('show');
}

window.executeToggleStatus = function () {
    if (!pendingToggleStatusId || !pendingToggleAction) return;

    const isEnabling = pendingToggleAction === 'enable';
    const actionText = isEnabling ? 'enable' : 'disable';
    const newStatus = isEnabling;

    const confirmBtn = document.getElementById('confirmToggleStatusBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
    }

    fetch(window.configRoutes.toggleStatus + '/' + pendingToggleStatusId, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: newStatus })
    })
    .then(response => {
        if (!response.ok) return response.json().then(err => { throw err; });
        return response.json();
    })
    .then(data => {
        $('#toggleStatusModal').modal('hide');
        showToast('success', data.message || `Plan ${actionText}d successfully.`);
        setTimeout(() => { window.location.href = window.configRoutes.index; }, 600);
    })
    .catch(err => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            const iconClass = isEnabling ? 'mdi-check-circle' : 'mdi-close-circle';
            const btnText = isEnabling ? 'Enable Plan' : 'Disable Plan';
            confirmBtn.innerHTML = `<i class="mdi ${iconClass}"></i> ${btnText}`;
        }
        showToast('error', err.message || `Failed to ${actionText} plan.`);
    });
};


// ============================================
// CATEGORY — EDIT (via kebab delegation)
// ============================================
function openEditCategoryModal(name, color) {
    document.getElementById('editCategoryOriginalName').value = name;
    document.getElementById('editCategoryName').value = name;

    const colorValue = color || '#FF6384';
    document.getElementById('editCategoryColor').value = colorValue;
    document.querySelectorAll('.color-picker-swatch').forEach(s => {
        s.classList.toggle('active', s.dataset.color === colorValue);
    });

    // Reset confirm overlay
    document.getElementById('categoryConfirmOverlay').style.display = 'none';

    $('#editCategoryModal').modal('show');
}

window.confirmSaveCategory = function () {
    const name = document.getElementById('editCategoryName').value.trim();
    if (!name) {
        showToast('error', 'Category name cannot be empty.');
        return;
    }

    const originalName = document.getElementById('editCategoryOriginalName').value;
    const color        = document.getElementById('editCategoryColor').value;

    document.getElementById('confirmCategoryOriginalText').textContent = originalName;
    document.getElementById('confirmCategoryNameText').textContent     = name;
    document.getElementById('confirmCategoryColorText').textContent    = color;

    document.getElementById('categoryConfirmOverlay').style.display = 'flex';
};

window.backToCategoryForm = function () {
    document.getElementById('categoryConfirmOverlay').style.display = 'none';
};

window.executeSaveCategory = function () {
    const originalName = document.getElementById('editCategoryOriginalName').value;
    const newName      = document.getElementById('editCategoryName').value.trim();
    const newColor     = document.getElementById('editCategoryColor').value;

    const confirmBtn = document.getElementById('confirmSaveCategoryBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
    }

    fetch(window.configRoutes.categoryUpdate + '/' + encodeURIComponent(originalName), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ new_name: newName, new_color: newColor })
    })
    .then(response => {
        if (!response.ok) return response.json().then(err => { throw err; });
        return response.json();
    })
    .then(data => {
        document.getElementById('categoryConfirmOverlay').style.display = 'none';
        $('#editCategoryModal').modal('hide');
        showToast('success', data.message || 'Category updated successfully.');
        setTimeout(() => { window.location.href = window.configRoutes.index; }, 600);
    })
    .catch(err => {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
        }
        showToast('error', err.message || 'An error occurred.');
    });
};


// ============================================
// CATEGORY � DELETE (single-step modal)
// ============================================
function openDeleteCategoryModal(name, productCount) {
    pendingCategoryDeleteName  = name;
    pendingCategoryDeleteCount = productCount;

    document.getElementById('deleteCategoryName').textContent  = name;
    document.getElementById('deleteCategoryCount').textContent = productCount + ' product(s)';

    const warningEl    = document.getElementById('categoryDeleteWarning');
    const reassignGrp  = document.getElementById('categoryReassignGroup');

    if (productCount > 0) {
        warningEl.style.display = 'flex';
        document.getElementById('categoryDeleteWarningText').textContent =
            'This category has ' + productCount + ' product(s). You can reassign them to another category, or leave them uncategorized.';
        reassignGrp.style.display = 'block';

        const select = document.getElementById('categoryReassignTo');
        Array.from(select.options).forEach(opt => {
            opt.style.display = opt.value === name ? 'none' : '';
        });
        select.value = '';
    } else {
        warningEl.style.display  = 'none';
        reassignGrp.style.display = 'none';
    }

    $('#deleteCategoryModal').modal('show');
}

window.executeDeleteCategory = function () {
    if (!pendingCategoryDeleteName) return;

    const reassignTo = document.getElementById('categoryReassignTo')?.value || '';
    const deleteBtn  = document.querySelector('#deleteCategoryModal .btn-danger');

    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Deleting...';
    }

    fetch(window.configRoutes.categoryDestroy + '/' + encodeURIComponent(pendingCategoryDeleteName), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reassign_to: reassignTo || null })
    })
    .then(response => {
        if (!response.ok) return response.json().then(err => { throw err; });
        return response.json();
    })
    .then(data => {
        $('#deleteCategoryModal').modal('hide');
        showToast('success', data.message || 'Category deleted successfully.');
        setTimeout(() => { window.location.href = window.configRoutes.index; }, 600);
    })
    .catch(err => {
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="mdi mdi-delete"></i> Delete Category';
        }
        showToast('error', err.message || 'Failed to delete category.');
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
    document.getElementById('planBadgeColor').value = '#FFA726';
    document.getElementById('planBadgeColorHex').value = '#FFA726';
    document.getElementById('planDescription').value = '';
    document.getElementById('planIsActive').value = '1';

    const st  = document.getElementById('planRequiresStudent');
    const bt  = document.getElementById('planRequiresBuddy');
    const bcg = document.getElementById('buddyCountGroup');

    if (st) { st.checked = false; document.getElementById('studentToggleLabel').textContent = 'No'; }
    if (bt) { bt.checked = false; document.getElementById('buddyToggleLabel').textContent = 'No'; }
    if (bcg) { bcg.style.display = 'none'; document.getElementById('planBuddyCount').value = '2'; }

    // Reset confirm overlay
    const overlay = document.getElementById('planConfirmOverlay');
    if (overlay) overlay.style.display = 'none';

    const confirmBtn = document.getElementById('confirmSavePlanBtn');
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
    }
}

function showToast(type, message) {
    // Use global ToastUtils if available
    if (typeof ToastUtils !== 'undefined' && ToastUtils.show) {
        ToastUtils.show(type, message);
        return;
    }

    // Fallback toast
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const wrapper = document.createElement('div');
    wrapper.className = `alert ${alertClass} alert-dismissible fade show`;
    wrapper.setAttribute('role', 'alert');
    wrapper.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;min-width:300px;max-width:500px;box-shadow:0 4px 12px rgba(0,0,0,0.3);border-radius:8px;';
    wrapper.innerHTML = `${message}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>`;
    document.body.appendChild(wrapper);
    setTimeout(() => {
        wrapper.classList.remove('show');
        setTimeout(() => wrapper.remove(), 300);
    }, 4000);
}

// Filter table rows based on search input
function filterTable(tableId, searchValue) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    const rows = tbody.querySelectorAll('tr');
    const filterValue = searchValue.toLowerCase().trim();
    
    rows.forEach(row => {
        if (!filterValue) {
            row.style.display = '';
            return;
        }
        
        const cells = row.querySelectorAll('td');
        let found = false;
        
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(filterValue)) {
                found = true;
            }
        });
        
        row.style.display = found ? '' : 'none';
    });
}