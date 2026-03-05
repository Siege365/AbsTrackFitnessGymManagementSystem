/**
 * Inventory Products Page
 * Handles product CRUD, stock in/out, stock history, search, filters, and delete operations.
 */
const InventoryPage = (function() {
    'use strict';

    function init() {
        initSearch();
        initCheckboxes();
        initViewProductModal();
        initEditProductModal();
        initStockHistoryModal();
        initStockInModal();
        initStockOutModal();
        initModalResets();
        initDropdownToggles();
        initStatsCardFilters();
        showAddModalOnValidationErrors();
    }

    // ============================================
    // Real-time Search with Debounce
    // ============================================
    function initSearch() {
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchForm.submit();
                }, 750);
            });
        }
    }

    // ============================================
    // Checkbox / Bulk Selection
    // ============================================
    function initCheckboxes() {
        const checkAll = document.getElementById('checkAll');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');

        if (!checkAll) return;

        checkAll.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButton();
        });

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                
                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
                
                updateBulkActionButton();
            });
        });
    }

    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const selectedCount = document.getElementById('selectedCount');
        if (selectedCount) selectedCount.textContent = checkedCount;
    }

    // ============================================
    // View Product Modal
    // ============================================
    function initViewProductModal() {
        document.querySelectorAll('.view-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('viewProductNumber').textContent = this.dataset.productNumber;
                document.getElementById('viewProductName').textContent = this.dataset.productName;
                document.getElementById('viewProductCategory').textContent = this.dataset.category;
                document.getElementById('viewProductPrice').textContent = '₱' + parseFloat(this.dataset.unitPrice).toFixed(2);
                document.getElementById('viewProductStock').textContent = this.dataset.stockQty;
                document.getElementById('viewProductThreshold').textContent = this.dataset.lowStockThreshold;
                document.getElementById('viewProductStatus').textContent = this.dataset.status;
                document.getElementById('viewProductRestocked').textContent = this.dataset.lastRestocked;
                document.getElementById('viewProductHistoryLink').href = '/inventory/' + this.dataset.id + '/transaction-history';
                
                // Display avatar
                const viewAvatarPreview = document.getElementById('viewProductAvatarPreview');
                if (viewAvatarPreview) {
                    if (this.dataset.avatar) {
                        viewAvatarPreview.style.backgroundImage = `url(/storage/${this.dataset.avatar})`;
                        viewAvatarPreview.innerHTML = '';
                    } else {
                        viewAvatarPreview.style.backgroundImage = '';
                        viewAvatarPreview.innerHTML = '<i class="mdi mdi-package-variant"></i>';
                    }
                }
            });
        });
    }

    // ============================================
    // Edit Product Modal
    // ============================================
    function initEditProductModal() {
        document.querySelectorAll('.edit-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.id;
                document.getElementById('editProductNumber').value = this.dataset.productNumber;
                document.getElementById('editProductName').value = this.dataset.productName;
                document.getElementById('editProductCategory').value = this.dataset.category;
                document.getElementById('editProductPrice').value = this.dataset.unitPrice;
                document.getElementById('editProductForm').action = '/inventory/' + itemId;
                
                // Display existing avatar
                const editAvatarPreview = document.getElementById('editProductAvatarPreview');
                if (editAvatarPreview) {
                    if (this.dataset.avatar) {
                        editAvatarPreview.style.backgroundImage = `url(/storage/${this.dataset.avatar})`;
                        editAvatarPreview.innerHTML = '';
                    } else {
                        editAvatarPreview.style.backgroundImage = '';
                        editAvatarPreview.innerHTML = '<i class="mdi mdi-package-variant"></i>';
                    }
                }
            });
        });

        const editForm = document.getElementById('editProductForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                // Handle new category for edit
                const isNewCategory = document.getElementById('editNewCategoryCheckbox').checked;
                if (isNewCategory) {
                    const newCategoryName = document.getElementById('editNewCategoryInput').value.trim();
                    if (!newCategoryName) {
                        e.preventDefault();
                        ToastUtils.showError('Please enter a new category name.', 'Validation Error');
                        document.getElementById('editNewCategoryInput').focus();
                        return;
                    }
                    // Block submission if exact match or high-similarity warning is showing
                    const warning = document.getElementById('editCategorySimilarityWarning');
                    if (warning && warning.querySelector('.similarity-exact')) {
                        e.preventDefault();
                        ToastUtils.showError('This category already exists. Please use the existing category or choose a different name.', 'Duplicate Category');
                        document.getElementById('editNewCategoryInput').focus();
                        return;
                    }
                    if (warning && warning.querySelector('.similarity-warning')) {
                        e.preventDefault();
                        ToastUtils.showError('This category is too similar to an existing one. Please use the suggested category or choose a different name.', 'Similar Category');
                        document.getElementById('editNewCategoryInput').focus();
                        return;
                    }
                    // Add the option to the select so it's submitted
                    const select = document.getElementById('editProductCategory');
                    let optionExists = false;
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === newCategoryName) {
                            optionExists = true;
                            select.selectedIndex = i;
                            break;
                        }
                    }
                    if (!optionExists) {
                        const newOption = document.createElement('option');
                        newOption.value = newCategoryName;
                        newOption.textContent = newCategoryName;
                        newOption.selected = true;
                        select.appendChild(newOption);
                    }
                    select.style.display = 'none';
                }

                const submitBtn = document.getElementById('editProductSubmitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';
            });
        }

        $('#editProductModal').on('hidden.bs.modal', function () {
            const submitBtn = document.getElementById('editProductSubmitBtn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Save Changes';
        });
    }

    // ============================================
    // View Stock History Modal (AJAX)
    // ============================================
    function initStockHistoryModal() {
        document.querySelectorAll('.stock-history-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.id;
                const productName = this.dataset.productName;
                
                document.getElementById('stockHistoryProductName').textContent = productName;
                document.getElementById('stockHistoryFullLink').href = '/inventory/' + itemId + '/transaction-history';
                
                // Show loading
                document.getElementById('stockHistoryTimeline').innerHTML = `
                    <div class="stock-history-loading">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2">Loading stock history...</p>
                    </div>
                `;
                document.getElementById('historyTotalIn').textContent = '0';
                document.getElementById('historyTotalOut').textContent = '0';
                document.getElementById('historyNetChange').textContent = '0';
                
                // Fetch stock history via AJAX
                fetch('/inventory/' + itemId + '/stock-history-json')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('historyTotalIn').textContent = '+' + data.totalIn;
                        document.getElementById('historyTotalOut').textContent = '-' + data.totalOut;
                        const netChange = data.totalIn - data.totalOut;
                        const netEl = document.getElementById('historyNetChange');
                        netEl.textContent = (netChange >= 0 ? '+' : '') + netChange;
                        netEl.className = 'summary-value ' + (netChange >= 0 ? 'text-success' : 'text-danger');
                        
                        const timeline = document.getElementById('stockHistoryTimeline');
                        if (data.transactions.length === 0) {
                            timeline.innerHTML = `
                                <div class="stock-history-empty">
                                    <i class="mdi mdi-history"></i>
                                    No stock transactions found for this product.
                                </div>
                            `;
                            return;
                        }
                        
                        let html = '';
                        data.transactions.forEach(t => {
                            const isIn = t.transaction_type === 'stock_in';
                            html += `
                                <div class="stock-history-item">
                                    <div class="stock-history-icon ${isIn ? 'stock-in' : 'stock-out'}">
                                        <i class="mdi mdi-${isIn ? 'plus' : 'minus'}-circle"></i>
                                    </div>
                                    <div class="stock-history-details">
                                        <div class="stock-history-type">${isIn ? 'Stock In' : 'Stock Out'}</div>
                                        <div class="stock-history-meta">
                                            ${t.date} &middot; by ${t.performed_by}
                                            ${t.notes ? ' &middot; ' + t.notes : ''}
                                        </div>
                                    </div>
                                    <div class="stock-history-qty ${isIn ? 'positive' : 'negative'}">
                                        ${isIn ? '+' : '-'}${t.quantity}
                                    </div>
                                </div>
                            `;
                        });
                        timeline.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching stock history:', error);
                        document.getElementById('stockHistoryTimeline').innerHTML = `
                            <div class="stock-history-empty">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                Failed to load stock history. Please try again.
                            </div>
                        `;
                    });
            });
        });
    }

    // ============================================
    // Stock In Modal
    // ============================================
    function initStockInModal() {
        document.querySelectorAll('.stock-in-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.dataset.id;
                const stockQty = parseInt(this.dataset.stockQty);
                const status = this.dataset.status;
                const statusClass = this.dataset.statusClass;
                
                document.getElementById('stockInProductNumber').textContent = this.dataset.productNumber;
                document.getElementById('stockInProductName').textContent = this.dataset.productName;
                document.getElementById('stockInCategory').textContent = this.dataset.category;
                document.getElementById('stockInCurrentStock').textContent = stockQty;
                document.getElementById('stockInStatus').textContent = status;
                document.getElementById('stockInStatus').className = 'badge ' + statusClass;
                
                document.getElementById('stockInForm').action = `/inventory/${itemId}/stock-transaction`;
                document.getElementById('stockInQuantity').value = '';
                document.getElementById('stockInPreview').style.display = 'none';
            });
        });

        // Stock In Quantity Preview
        const stockInQty = document.getElementById('stockInQuantity');
        if (stockInQty) {
            stockInQty.addEventListener('input', function() {
                const quantity = parseInt(this.value) || 0;
                const currentStock = parseInt(document.getElementById('stockInCurrentStock').textContent);
                const newStock = currentStock + quantity;
                const submitBtn = document.querySelector('#stockInForm button[type="submit"]');
                
                if (this.value !== '' && quantity <= 0) {
                    document.getElementById('stockInPreview').style.display = 'none';
                } else if (quantity > 0) {
                    submitBtn.disabled = false;
                    document.getElementById('previewCurrentIn').textContent = currentStock;
                    document.getElementById('previewAddQuantity').textContent = '+' + quantity;
                    document.getElementById('previewNewIn').textContent = newStock;
                    document.getElementById('stockInPreview').style.display = 'block';
                } else {
                    submitBtn.disabled = false;
                    document.getElementById('stockInPreview').style.display = 'none';
                }
            });
        }

        // Stock In Form submit validation
        const stockInForm = document.getElementById('stockInForm');
        if (stockInForm) {
            stockInForm.addEventListener('submit', function(e) {
                const quantity = parseInt(document.getElementById('stockInQuantity').value) || 0;
                const submitBtn = this.querySelector('button[type="submit"]');
                
                if (quantity <= 0) {
                    e.preventDefault();
                    ToastUtils.showWarning('Please enter a valid quantity greater than 0.', 'Invalid Quantity');
                    document.getElementById('stockInQuantity').focus();
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
            });
        }
    }

    // ============================================
    // Stock Out Modal
    // ============================================
    function initStockOutModal() {
        document.querySelectorAll('.stock-out-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const stockQty = parseInt(this.dataset.stockQty);

                if (stockQty === 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    ToastUtils.showError('This product is out of stock. Stock out is not allowed.', 'Out of Stock');
                    return;
                }

                const itemId = this.dataset.id;
                const status = this.dataset.status;
                const statusClass = this.dataset.statusClass;
                
                document.getElementById('stockOutProductNumber').textContent = this.dataset.productNumber;
                document.getElementById('stockOutProductName').textContent = this.dataset.productName;
                document.getElementById('stockOutCategory').textContent = this.dataset.category;
                document.getElementById('stockOutCurrentStock').textContent = stockQty;
                document.getElementById('availableStock').textContent = stockQty;
                document.getElementById('stockOutStatus').textContent = status;
                document.getElementById('stockOutStatus').className = 'badge ' + statusClass;
                
                document.getElementById('stockOutForm').action = `/inventory/${itemId}/stock-transaction`;
                document.getElementById('stockOutQuantity').max = stockQty;
                document.getElementById('stockOutQuantity').value = '';
                document.getElementById('stockOutPreview').style.display = 'none';
            });
        });

        // Stock Out Quantity Preview
        const stockOutQty = document.getElementById('stockOutQuantity');
        if (stockOutQty) {
            stockOutQty.addEventListener('input', function() {
                const quantity = parseInt(this.value) || 0;
                const currentStock = parseInt(document.getElementById('stockOutCurrentStock').textContent);
                const newStock = currentStock - quantity;
                
                if (this.value !== '' && quantity <= 0) {
                    document.getElementById('stockOutPreview').style.display = 'none';
                } else if (quantity > 0) {
                    if (quantity > currentStock) {
                        document.getElementById('stockOutPreview').style.display = 'none';
                        document.getElementById('confirmStockOut').disabled = true;
                        ToastUtils.showError('Insufficient stock! Current stock: ' + currentStock + '.', 'Insufficient Stock');
                    } else {
                        document.getElementById('previewCurrentOut').textContent = currentStock;
                        document.getElementById('previewRemoveQuantity').textContent = '-' + quantity;
                        document.getElementById('previewNewOut').textContent = newStock;
                        document.getElementById('stockOutPreview').style.display = 'block';
                        document.getElementById('confirmStockOut').disabled = false;
                    }
                } else {
                    document.getElementById('stockOutPreview').style.display = 'none';
                    document.getElementById('confirmStockOut').disabled = false;
                }
            });
        }

        // Stock Out Form submit validation
        const stockOutForm = document.getElementById('stockOutForm');
        if (stockOutForm) {
            stockOutForm.addEventListener('submit', function(e) {
                const quantity = parseInt(document.getElementById('stockOutQuantity').value) || 0;
                const currentStock = parseInt(document.getElementById('stockOutCurrentStock').textContent);
                const submitBtn = document.getElementById('confirmStockOut');
                
                if (quantity <= 0) {
                    e.preventDefault();
                    ToastUtils.showWarning('Please enter a valid quantity greater than 0.', 'Invalid Quantity');
                    document.getElementById('stockOutQuantity').focus();
                    return;
                } else if (quantity > currentStock) {
                    e.preventDefault();
                    ToastUtils.showError('Insufficient stock! Current stock: ' + currentStock + '.', 'Insufficient Stock');
                    document.getElementById('stockOutQuantity').focus();
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
            });
        }
    }

    // ============================================
    // Modal Reset Handlers
    // ============================================
    function initModalResets() {
        $('#addProductModal').on('hidden.bs.modal', function () {
            document.getElementById('addProductForm').reset();
            document.getElementById('autoProductNumber').value = '';
            document.getElementById('addProductConfirmOverlay').style.display = 'none';
            const confirmBtn = document.querySelector('#addProductConfirmOverlay .btn-update');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="mdi mdi-check"></i> Confirm';
            }
            $('#addProductModal .is-invalid').removeClass('is-invalid');
            $('#addProductModal .invalid-feedback').remove();
            $('#addProductModal .alert').remove();
        });

        $('#addProductModal').on('show.bs.modal', function () {
            const nextProductNumberUrl = document.querySelector('meta[name="next-product-number-url"]');
            const url = nextProductNumberUrl ? nextProductNumberUrl.content : '/inventory/next-product-number';
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('autoProductNumber').value = data.product_number;
                })
                .catch(error => {
                    console.error('Error fetching product number:', error);
                    document.getElementById('autoProductNumber').value = 'PRD-0001';
                });
        });

        $('#stockInModal').on('hidden.bs.modal', function () {
            const submitBtn = this.querySelector('#stockInForm button[type="submit"]');
            document.getElementById('stockInForm').reset();
            document.getElementById('stockInPreview').style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Confirm Stock In';
        });

        $('#stockOutModal').on('hidden.bs.modal', function () {
            document.getElementById('stockOutForm').reset();
            document.getElementById('stockOutPreview').style.display = 'none';
            document.getElementById('confirmStockOut').disabled = false;
            document.getElementById('confirmStockOut').innerHTML = 'Confirm Stock Out';
        });
    }

    // ============================================
    // Dropdown Toggle
    // ============================================
    function initDropdownToggles() {
        document.querySelectorAll('[data-toggle="dropdown"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.remove('show');
                    }
                });
                
                const menu = this.nextElementSibling;
                if (menu?.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('show');
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    // ============================================
    // Stats Card Click-to-Filter
    // ============================================
    function initStatsCardFilters() {
        document.querySelectorAll('.stats-card[data-filter]').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function() {
                const filter = this.dataset.filter;
                const url = new URL(window.location.href);
                if (filter === 'all') {
                    url.searchParams.delete('filter');
                } else {
                    url.searchParams.set('filter', filter);
                }
                window.location.href = url.toString();
            });
        });
    }

    // ============================================
    // Show Add Modal on Validation Errors
    // ============================================
    function showAddModalOnValidationErrors() {
        const hasErrors = document.querySelector('meta[name="has-validation-errors"]');
        if (hasErrors && hasErrors.content === '1') {
            $(document).ready(function() {
                $('#addProductModal').modal('show');
            });
        }
    }

    return { init };
})();

// ============================================
// Global Functions (called from onclick attributes)
// ============================================

function toggleFilterSection(header, event) {
    event.preventDefault();
    event.stopPropagation();
    const section = header.parentElement;
    section.classList.toggle('active');
}

function showAddProductConfirm() {
    const form = document.getElementById('addProductForm');
    const productNumber = document.getElementById('autoProductNumber').value;
    const productName = form.querySelector('[name="product_name"]').value.trim();
    const unitPrice = form.querySelector('[name="unit_price"]').value;
    const stockQty = form.querySelector('[name="stock_qty"]').value;

    // Determine category value based on new category checkbox
    const isNewCategory = document.getElementById('newCategoryCheckbox').checked;
    let category;
    if (isNewCategory) {
        category = document.getElementById('newCategoryInput').value.trim();
        if (!category) {
            ToastUtils.showError('Please enter a new category name.', 'Validation Error');
            document.getElementById('newCategoryInput').focus();
            return;
        }
        // Block submission if exact match or high-similarity warning is showing
        const warning = document.getElementById('addCategorySimilarityWarning');
        if (warning && warning.querySelector('.similarity-exact')) {
            ToastUtils.showError('This category already exists. Please use the existing category or choose a different name.', 'Duplicate Category');
            document.getElementById('newCategoryInput').focus();
            return;
        }
        if (warning && warning.querySelector('.similarity-warning')) {
            ToastUtils.showError('This category is too similar to an existing one. Please use the suggested category or choose a different name.', 'Similar Category');
            document.getElementById('newCategoryInput').focus();
            return;
        }
        // Set the hidden select value to the custom category
        const select = document.getElementById('addCategorySelect');
        // Add the option if it doesn't exist
        let optionExists = false;
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === category) {
                optionExists = true;
                select.selectedIndex = i;
                break;
            }
        }
        if (!optionExists) {
            const newOption = document.createElement('option');
            newOption.value = category;
            newOption.textContent = category;
            newOption.selected = true;
            select.appendChild(newOption);
        }
    } else {
        category = form.querySelector('[name="category"]').value;
    }

    if (!productName) {
        ToastUtils.showError('Please enter a product name.', 'Validation Error');
        form.querySelector('[name="product_name"]').focus();
        return;
    }
    if (!category) {
        ToastUtils.showError('Please select a category.', 'Validation Error');
        return;
    }
    if (!unitPrice || parseFloat(unitPrice) < 0) {
        ToastUtils.showError('Please enter a valid unit price.', 'Validation Error');
        form.querySelector('[name="unit_price"]').focus();
        return;
    }
    if (stockQty === '' || parseInt(stockQty) < 0) {
        ToastUtils.showError('Please enter a valid stock quantity.', 'Validation Error');
        form.querySelector('[name="stock_qty"]').focus();
        return;
    }

    document.getElementById('confirmProductNumberText').textContent = productNumber;
    document.getElementById('confirmProductNameText').textContent = productName;
    document.getElementById('confirmCategoryText').textContent = category;
    document.getElementById('confirmPriceText').textContent = '₱' + parseFloat(unitPrice).toFixed(2);
    document.getElementById('confirmStockText').textContent = stockQty;

    document.getElementById('addProductConfirmOverlay').style.display = 'flex';
}

function backToAddProductForm() {
    document.getElementById('addProductConfirmOverlay').style.display = 'none';
}

function submitAddProductForm() {
    const confirmBtn = document.querySelector('#addProductConfirmOverlay .btn-update');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Processing...';
    document.getElementById('addProductForm').submit();
}

// ============================================
// Delete Confirmation Logic
// ============================================
let pendingDeleteAction = null;

function clearSearch(inputId, formId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.value = '';
        document.getElementById(formId).submit();
    }
}

function showDeleteModal(itemDescription) {
    // Close any open dropdowns first
    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        menu.classList.remove('show');
    });

    var countEl = document.getElementById('deleteItemCount');
    if (countEl) countEl.textContent = itemDescription;

    // Reset confirm input
    var confirmInput = document.getElementById('deleteInventoryConfirmInput');
    var confirmBtn = document.getElementById('deleteInventoryConfirmBtn');
    var confirmError = document.getElementById('deleteInventoryConfirmError');
    if (confirmInput) confirmInput.value = '';
    if (confirmBtn) confirmBtn.disabled = true;
    if (confirmError) confirmError.classList.add('d-none');

    var $modal = (window.jQuery || window.$);
    if ($modal) {
        $modal('#deleteConfirmModal').modal('show');
    }
}

function closeDeleteModal() {
    var $modal = (window.jQuery || window.$);
    if ($modal) {
        $modal('#deleteConfirmModal').modal('hide');
    }
    pendingDeleteAction = null;
}

function executeDelete() {
    const confirmInput = document.getElementById('deleteInventoryConfirmInput');
    if (!confirmInput || confirmInput.value.trim().toLowerCase() !== 'delete') {
        const err = document.getElementById('deleteInventoryConfirmError');
        if (err) err.classList.remove('d-none');
        return;
    }
    if (pendingDeleteAction) {
        pendingDeleteAction();
        pendingDeleteAction = null;
    }
    closeDeleteModal();
}

function confirmDeleteSingle(itemId) {
    const checkbox = document.querySelector(`.item-checkbox[value="${itemId}"]`);
    let productInfo = '';
    
    if (checkbox) {
        const productNumber = checkbox.dataset.productNumber;
        const productName = checkbox.dataset.productName;
        const category = checkbox.dataset.category;
        productInfo = `
            <div class="product-delete-item">
                <div class="product-name">${productName}</div>
                <div class="product-meta">Product #: ${productNumber} | Category: ${category}</div>
            </div>
        `;
    }
    
    document.getElementById('selectedProductsList').innerHTML = productInfo;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    pendingDeleteAction = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/inventory/' + itemId;
        form.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '"><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    };
    showDeleteModal('1 product');
}

function bulkDeleteInventory() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) {
        if (typeof ToastUtils !== 'undefined') {
            ToastUtils.showError('Please select at least 1 row before proceeding.', 'No Selection');
        }
        return;
    }

    let productsList = '';
    checked.forEach(cb => {
        const productNumber = cb.dataset.productNumber;
        const productName = cb.dataset.productName;
        const category = cb.dataset.category;
        productsList += `
            <div class="product-delete-item">
                <div class="product-name">${productName}</div>
                <div class="product-meta">Product #: ${productNumber} | Category: ${category}</div>
            </div>
        `;
    });
    
    document.getElementById('selectedProductsList').innerHTML = productsList;

    pendingDeleteAction = function() {
        const form = document.getElementById('bulkDeleteInventoryForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });

        form.submit();
    };

    showDeleteModal(checked.length + ' product(s)');
}

// ============================================
// New Category Toggle Functions
// ============================================

function toggleNewCategory(checkbox) {
    const select = document.getElementById('addCategorySelect');
    const inputGroup = document.getElementById('newCategoryInputGroup');
    
    if (checkbox.checked) {
        select.style.display = 'none';
        select.removeAttribute('required');
        inputGroup.style.display = 'block';
        document.getElementById('newCategoryInput').setAttribute('required', 'required');
    } else {
        select.style.display = 'block';
        select.setAttribute('required', 'required');
        inputGroup.style.display = 'none';
        document.getElementById('newCategoryInput').removeAttribute('required');
        document.getElementById('newCategoryInput').value = '';
    }
}

function toggleEditNewCategory(checkbox) {
    const select = document.getElementById('editProductCategory');
    const inputGroup = document.getElementById('editNewCategoryInputGroup');
    
    if (checkbox.checked) {
        select.style.display = 'none';
        select.removeAttribute('required');
        inputGroup.style.display = 'block';
        document.getElementById('editNewCategoryInput').setAttribute('required', 'required');
    } else {
        select.style.display = 'block';
        select.setAttribute('required', 'required');
        inputGroup.style.display = 'none';
        document.getElementById('editNewCategoryInput').removeAttribute('required');
        document.getElementById('editNewCategoryInput').value = '';
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    InventoryPage.init();

    // Reset new category state on Add modal close
    $('#addProductModal').on('hidden.bs.modal', function () {
        const checkbox = document.getElementById('newCategoryCheckbox');
        if (checkbox && checkbox.checked) {
            checkbox.checked = false;
            toggleNewCategory(checkbox);
        }
        // Clear similarity warning
        const warning = document.getElementById('addCategorySimilarityWarning');
        if (warning) { warning.style.display = 'none'; warning.innerHTML = ''; }
    });

    // Reset new category state on Edit modal close
    $('#editProductModal').on('hidden.bs.modal', function () {
        const checkbox = document.getElementById('editNewCategoryCheckbox');
        if (checkbox && checkbox.checked) {
            checkbox.checked = false;
            toggleEditNewCategory(checkbox);
        }
        // Clear similarity warning
        const warning = document.getElementById('editCategorySimilarityWarning');
        if (warning) { warning.style.display = 'none'; warning.innerHTML = ''; }
    });

});

// ============================================
// Category Similarity Check (AJAX)
// ============================================

let categorySimilarityTimeout = null;

/**
 * Debounced AJAX check for similar category names.
 * Called on input event of new category text fields.
 * @param {string} value - The category name typed by the user
 * @param {string} context - 'add' or 'edit' to target the correct warning container
 */
function checkCategorySimilarity(value, context) {
    const warningId = context === 'add' ? 'addCategorySimilarityWarning' : 'editCategorySimilarityWarning';
    const warningEl = document.getElementById(warningId);
    
    if (!warningEl) return;

    // Clear previous timeout
    if (categorySimilarityTimeout) {
        clearTimeout(categorySimilarityTimeout);
    }

    const trimmed = value.trim();
    
    // Hide warning if input is empty
    if (!trimmed || trimmed.length < 2) {
        warningEl.style.display = 'none';
        warningEl.innerHTML = '';
        return;
    }

    // Debounce: wait 400ms after user stops typing
    categorySimilarityTimeout = setTimeout(function() {
        fetch('/inventory/check-category?name=' + encodeURIComponent(trimmed), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.similar && data.similar.length > 0) {
                let html = '';
                const hasExact = data.similar.some(function(s) { return s.type === 'exact'; });

                if (hasExact) {
                    // Exact match found
                    const exact = data.similar.find(function(s) { return s.type === 'exact'; });
                    html = '<div class="similarity-alert similarity-exact">' +
                           '<i class="mdi mdi-alert-circle"></i> ' +
                           '<span>Category <strong>"' + escapeHtml(exact.name) + '"</strong> already exists. ' +
                           '<a href="#" class="similarity-use-link" onclick="useSuggestedCategory(\'' + escapeAttr(exact.name) + '\', \'' + context + '\'); return false;">Use it instead</a></span>' +
                           '</div>';
                } else {
                    // Similar matches found
                    html = '<div class="similarity-alert similarity-warning">' +
                           '<i class="mdi mdi-alert-outline"></i> ' +
                           '<span>Similar categories found:</span>' +
                           '</div>';
                    data.similar.forEach(function(s) {
                        const typeLabel = s.type === 'plural' ? 'plural form' : 
                                         s.type === 'phonetic' ? 'sounds similar' : 
                                         s.type === 'contains' ? 'similar name' : 'similar';
                        html += '<div class="similarity-suggestion">' +
                                '<span class="similarity-name">"' + escapeHtml(s.name) + '"</span>' +
                                '<span class="similarity-type">(' + typeLabel + ', ' + Math.round(s.score) + '% match)</span>' +
                                '<a href="#" class="similarity-use-link" onclick="useSuggestedCategory(\'' + escapeAttr(s.name) + '\', \'' + context + '\'); return false;">Use this</a>' +
                                '</div>';
                    });
                }
                warningEl.innerHTML = html;
                warningEl.style.display = 'block';
            } else {
                // No similar categories - show green "new category" indicator
                warningEl.innerHTML = '<div class="similarity-alert similarity-ok">' +
                    '<i class="mdi mdi-check-circle"></i> ' +
                    '<span>"' + escapeHtml(trimmed) + '" will be created as a new category</span>' +
                    '</div>';
                warningEl.style.display = 'block';
            }
        })
        .catch(function(err) {
            console.error('Category check failed:', err);
            warningEl.style.display = 'none';
        });
    }, 400);
}

/**
 * When user clicks "Use this" on a similarity suggestion, 
 * switch back to the dropdown and select the suggested category.
 */
function useSuggestedCategory(categoryName, context) {
    if (context === 'add') {
        const checkbox = document.getElementById('newCategoryCheckbox');
        const select = document.getElementById('addCategorySelect');
        const input = document.getElementById('newCategoryInput');
        const warning = document.getElementById('addCategorySimilarityWarning');

        // Check if option exists in the dropdown
        let found = false;
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === categoryName) {
                select.selectedIndex = i;
                found = true;
                break;
            }
        }
        // If not found in dropdown, add it
        if (!found) {
            const newOption = document.createElement('option');
            newOption.value = categoryName;
            newOption.textContent = categoryName;
            newOption.selected = true;
            select.appendChild(newOption);
        }

        // Uncheck "New Category" and toggle back to dropdown
        if (checkbox) {
            checkbox.checked = false;
            toggleNewCategory(checkbox);
        }
        // Clear warning
        if (warning) { warning.style.display = 'none'; warning.innerHTML = ''; }
        if (input) input.value = '';
    } else {
        const checkbox = document.getElementById('editNewCategoryCheckbox');
        const select = document.getElementById('editProductCategory');
        const input = document.getElementById('editNewCategoryInput');
        const warning = document.getElementById('editCategorySimilarityWarning');

        // Check if option exists in the dropdown
        let found = false;
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === categoryName) {
                select.selectedIndex = i;
                found = true;
                break;
            }
        }
        // If not found, add it
        if (!found) {
            const newOption = document.createElement('option');
            newOption.value = categoryName;
            newOption.textContent = categoryName;
            newOption.selected = true;
            select.appendChild(newOption);
        }

        // Uncheck "New Category" and toggle back to dropdown
        if (checkbox) {
            checkbox.checked = false;
            toggleEditNewCategory(checkbox);
        }
        // Clear warning
        if (warning) { warning.style.display = 'none'; warning.innerHTML = ''; }
        if (input) input.value = '';
    }
}

/**
 * Utility: HTML-escape a string
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

/**
 * Utility: Escape for use in HTML attribute (single-quoted)
 */
function escapeAttr(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ============================================
// Avatar Preview Functions
// ============================================

// Toggle between file upload and URL input for new product avatar
function toggleProductAvatarInput(type) {
    const fileInput = document.getElementById('newProductAvatar');
    const urlInput = document.getElementById('newProductAvatarUrl');
    
    if (type === 'file') {
        fileInput.style.display = 'block';
        urlInput.style.display = 'none';
        urlInput.value = '';
    } else {
        fileInput.style.display = 'none';
        urlInput.style.display = 'block';
        fileInput.value = '';
    }
    // Reset preview
    const preview = document.getElementById('newProductAvatarPreview');
    if (preview) {
        preview.style.backgroundImage = '';
        preview.innerHTML = '<i class="mdi mdi-package-variant"></i>';
    }
}

// Preview new product avatar
function previewNewProductAvatar() {
    const fileInput = document.getElementById('newProductAvatar');
    const urlInput = document.getElementById('newProductAvatarUrl');
    const preview = document.getElementById('newProductAvatarPreview');
    
    if (!preview) return;
    
    // Check file input first
    if (fileInput && fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.backgroundImage = `url(${e.target.result})`;
            preview.innerHTML = '';
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
    // Check URL input
    else if (urlInput && urlInput.value) {
        preview.style.backgroundImage = `url(${urlInput.value})`;
        preview.innerHTML = '';
    }
    // Reset to default
    else {
        preview.style.backgroundImage = '';
        preview.innerHTML = '<i class="mdi mdi-package-variant"></i>';
    }
}

// Toggle between file upload and URL input for edit product avatar
function toggleEditProductAvatarInput(type) {
    const fileInput = document.getElementById('editProductAvatar');
    const urlInput = document.getElementById('editProductAvatarUrl');
    
    if (type === 'file') {
        fileInput.style.display = 'block';
        urlInput.style.display = 'none';
        urlInput.value = '';
    } else {
        fileInput.style.display = 'none';
        urlInput.style.display = 'block';
        fileInput.value = '';
    }
}

// Preview edit product avatar
function previewEditProductAvatar() {
    const fileInput = document.getElementById('editProductAvatar');
    const urlInput = document.getElementById('editProductAvatarUrl');
    const preview = document.getElementById('editProductAvatarPreview');
    
    if (!preview) return;
    
    // Check file input first
    if (fileInput && fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.backgroundImage = `url(${e.target.result})`;
            preview.innerHTML = '';
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
    // Check URL input
    else if (urlInput && urlInput.value) {
        preview.style.backgroundImage = `url(${urlInput.value})`;
        preview.innerHTML = '';
    }
}

// =====================================================
// TRANSACTION HISTORY PAGE FUNCTIONS
// =====================================================

/**
 * Initialize transaction history page edit modal
 */
function initTransactionHistoryPage() {
    const editBtn = document.getElementById('txnEditProductBtn');
    if (!editBtn) return; // Not on transaction history page

    editBtn.addEventListener('click', function() {
        const itemId = this.dataset.id;
        const editForm = document.getElementById('editProductForm');
        const editProductNumber = document.getElementById('editProductNumber');
        const editProductName = document.getElementById('editProductName');
        const editProductCategory = document.getElementById('editProductCategory');
        const editProductPrice = document.getElementById('editProductPrice');
        const editAvatarPreview = document.getElementById('editProductAvatarPreview');

        // Populate form fields
        if (editProductNumber) editProductNumber.value = this.dataset.productNumber;
        if (editProductName) editProductName.value = this.dataset.productName;
        if (editProductCategory) editProductCategory.value = this.dataset.category;
        if (editProductPrice) editProductPrice.value = this.dataset.unitPrice;
        if (editForm) editForm.action = '/inventory/' + itemId;

        // Display existing avatar
        if (editAvatarPreview) {
            if (this.dataset.avatar) {
                editAvatarPreview.style.backgroundImage = 'url(/storage/' + this.dataset.avatar + ')';
                editAvatarPreview.innerHTML = '';
            } else {
                editAvatarPreview.style.backgroundImage = '';
                editAvatarPreview.innerHTML = '<i class="mdi mdi-package-variant"></i>';
            }
        }
    });
}

// Expose to global scope for inline event handlers
window.InventoryPage = InventoryPage;
window.clearSearch = clearSearch;
window.confirmDeleteSingle = confirmDeleteSingle;
window.bulkDeleteInventory = bulkDeleteInventory;
window.executeDelete = executeDelete;
window.toggleNewCategory = toggleNewCategory;
window.toggleEditNewCategory = toggleEditNewCategory;
window.showAddProductConfirm = showAddProductConfirm;
window.backToAddProductForm = backToAddProductForm;
window.submitAddProductForm = submitAddProductForm;
window.toggleFilterSection = toggleFilterSection;
window.previewNewProductAvatar = previewNewProductAvatar;
window.toggleProductAvatarInput = toggleProductAvatarInput;
window.previewEditProductAvatar = previewEditProductAvatar;
window.toggleEditProductAvatarInput = toggleEditProductAvatarInput;
window.initTransactionHistoryPage = initTransactionHistoryPage;
window.checkCategorySimilarity = checkCategorySimilarity;
window.useSuggestedCategory = useSuggestedCategory;

// Initialize transaction history page on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initTransactionHistoryPage();

    // Wire up type-to-confirm for delete modal
    const confirmInput = document.getElementById('deleteInventoryConfirmInput');
    const confirmBtn = document.getElementById('deleteInventoryConfirmBtn');
    if (confirmInput && confirmBtn) {
        confirmInput.addEventListener('input', function() {
            confirmBtn.disabled = this.value.trim().toLowerCase() !== 'delete';
        });
    }

    // Reset confirm input when modal is closed
    var $ = window.jQuery || window.$;
    if ($) {
        $('#deleteConfirmModal').on('hidden.bs.modal', function() {
            var inp = document.getElementById('deleteInventoryConfirmInput');
            var btn = document.getElementById('deleteInventoryConfirmBtn');
            var err = document.getElementById('deleteInventoryConfirmError');
            if (inp) inp.value = '';
            if (btn) btn.disabled = true;
            if (err) err.classList.add('d-none');
        });
    }
});
