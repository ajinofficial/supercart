<?php
$page = 'products';
include('header.php');
include('menus.php');

$statusMetaMap = [
    1 => ['text' => 'Active', 'class' => 'products-active'],
    2 => ['text' => 'Low Stock', 'class' => 'products-low'],
    3 => ['text' => 'Out of Stock', 'class' => 'products-out'],
];
?>

<div class="products-page">
    <div class="products-card shadow-sm">
        <div class="products-card-header">
            <div>
                <h2 class="products-title">Products Management</h2>
                <p class="products-subtitle">Manage inventory, pricing, and stock availability.</p>
            </div>
            <div class="products-header-actions">
                <button type="button" class="products-bulk-delete-btn" id="deleteSelectedProductsBtn" disabled>
                    <i data-lucide="trash-2"></i>
                    <span>Delete Selected</span>
                </button>
                <button type="button" class="products-add-btn" id="openProductFormBtn">
                    <i data-lucide="plus"></i>
                    <span>Add Product</span>
                </button>
            </div>
        </div>

        <div class="products-card-body">
            <div id="productAlert" class="product-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="productsTable" class="table products-table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="products-select-col">
                                <input type="checkbox" id="selectAllProducts" class="products-select-checkbox" aria-label="Select all products">
                            </th>
                            <th>Product ID</th>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)) : ?>
                            <?php foreach ($products as $product) : ?>
                                <?php
                                $statusValue = (int) ($product['pr_status'] ?? ($product['status'] ?? 1));
                                $statusMeta = $statusMetaMap[$statusValue] ?? $statusMetaMap[1];
                                $statusClass = $statusMeta['class'];
                                $statusText = $statusMeta['text'];
                                $createdDate = isset($product['created_at']) && !empty($product['created_at'])
                                    ? date('d-m-Y', strtotime($product['created_at']))
                                    : date('d-m-Y');
                                $productName = $product['pr_name'] ?? ($product['product_name'] ?? '-');
                                $description = trim((string) ($product['pr_description'] ?? ($product['description'] ?? '')));
                                $descriptionText = $description !== '' ? $description : '-';
                                $categoryId = (int) ($product['category_id'] ?? 0);
                                $category = $product['category_name'] ?? ($product['category'] ?? '-');
                                $brandId = (int) ($product['brand_id'] ?? 0);
                                $brand = $product['brand_name'] ?? ($product['brand'] ?? '-');
                                $stock = $product['pr_stock'] ?? ($product['stock'] ?? 0);
                                $price = $product['pr_price'] ?? ($product['price'] ?? 0);
                                $discount = (float) ($product['pr_discount'] ?? ($product['discount'] ?? 0));
                                $discountText = $discount > 0
                                    ? rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') . '%'
                                    : '-';
                                $imageFile = $product['pr_image'] ?? ($product['product_image'] ?? '');
                                $imageUrl = !empty($imageFile)
                                    ? base_url('uploads/products/' . $imageFile)
                                    : '';
                                $optionsJson = (string) ($product['pr_options'] ?? '[]');
                                $optionsPayload = base64_encode($optionsJson);
                                $productCode = 'P' . str_pad((string) ($product['id'] ?? 0), 4, '0', STR_PAD_LEFT);
                                ?>
                                <tr data-id="<?= esc((string) ($product['id'] ?? 0)) ?>">
                                    <td class="products-select-col">
                                        <input
                                            type="checkbox"
                                            class="products-row-checkbox"
                                            value="<?= esc((string) ($product['id'] ?? 0)) ?>"
                                            data-status="<?= esc((string) $statusValue) ?>"
                                            aria-label="Select <?= esc($productName) ?>"
                                        >
                                    </td>
                                    <td><span class="products-id">#<?= esc($productCode) ?></span></td>
                                    <td class="product-image-cell">
                                        <?php if (!empty($imageUrl)) : ?>
                                            <img src="<?= esc($imageUrl) ?>" alt="<?= esc($productName) ?>" class="product-thumb">
                                        <?php else : ?>
                                            <span class="product-thumb-placeholder">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($productName) ?></td>
                                    <td class="product-desc-cell" title="<?= esc($descriptionText) ?>"><?= esc($descriptionText) ?></td>
                                    <td><?= esc($category) ?></td>
                                    <td><?= esc($brand) ?></td>
                                    <td><?= esc((string) $stock) ?></td>
                                    <td><span class="products-price"><?= esc($currencySymbol) ?> <?= esc(number_format((float) $price, 2)) ?></span></td>
                                    <td><?= esc($discountText) ?></td>
                                    <td><span class="products-status <?= esc($statusClass) ?>"><?= esc($statusText) ?></span></td>
                                    <td><?= esc($createdDate) ?></td>
                                    <td>
                                        <div class="product-action-group">
                                        <button
                                            type="button"
                                            class="product-edit-btn"
                                            data-id="<?= esc((string) ($product['id'] ?? 0)) ?>"
                                            data-name="<?= esc($productName) ?>"
                                            data-description="<?= esc($description) ?>"
                                            data-category-id="<?= esc((string) $categoryId) ?>"
                                            data-category-name="<?= esc($category) ?>"
                                            data-brand-id="<?= esc((string) $brandId) ?>"
                                            data-brand-name="<?= esc($brand) ?>"
                                            data-stock="<?= esc((string) $stock) ?>"
                                            data-price="<?= esc((string) $price) ?>"
                                            data-discount="<?= esc(number_format($discount, 2, '.', '')) ?>"
                                            data-status="<?= esc((string) $statusValue) ?>"
                                            data-image-url="<?= esc($imageUrl) ?>"
                                            data-options-payload="<?= esc($optionsPayload) ?>"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="product-delete-btn"
                                            data-id="<?= esc((string) ($product['id'] ?? 0)) ?>"
                                            data-status="<?= esc((string) $statusValue) ?>"
                                        >
                                            Delete
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="product-modal" id="productModal" aria-hidden="true">
    <div class="product-modal-card">
        <div class="product-modal-head">
            <h3 id="productModalTitle">Add New Product</h3>
            <button type="button" class="product-modal-close" id="closeProductFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="addProductForm" class="product-form" enctype="multipart/form-data">
            <input type="hidden" id="productRecordId" name="id" value="">
            <div class="product-form-grid">
                <div class="product-field">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="product_name" placeholder="Enter product name">
                    <span class="product-error" data-error-for="product_name"></span>
                </div>

                <div class="product-field">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">Select category</option>
                        <?php if (!empty($categories)) : ?>
                            <?php foreach ($categories as $categoryItem) : ?>
                                <?php $categoryId = (int) ($categoryItem['id'] ?? 0); ?>
                                <?php $categoryName = trim((string) ($categoryItem['ct_name'] ?? '')); ?>
                                <?php if ($categoryId > 0 && $categoryName !== '') : ?>
                                    <option value="<?= esc((string) $categoryId) ?>"><?= esc($categoryName) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="product-error" data-error-for="category"></span>
                </div>

                <div class="product-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Enter product description"></textarea>
                    <span class="product-error" data-error-for="description"></span>
                </div>

                <div class="product-field">
                    <label for="brand">Brand</label>
                    <select id="brand" name="brand">
                        <option value="">Select brand</option>
                        <?php if (!empty($brands)) : ?>
                            <?php foreach ($brands as $brandItem) : ?>
                                <?php $brandId = (int) ($brandItem['id'] ?? 0); ?>
                                <?php $brandName = trim((string) ($brandItem['br_name'] ?? '')); ?>
                                <?php if ($brandId > 0 && $brandName !== '') : ?>
                                    <option value="<?= esc((string) $brandId) ?>"><?= esc($brandName) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="product-error" data-error-for="brand"></span>
                </div>

                <div class="product-field">
                    <label for="productImage">Product Image</label>
                    <input type="file" id="productImage" name="product_image" accept="image/png,image/jpeg,image/jpg,image/webp">
                    <span class="product-error" data-error-for="product_image"></span>
                    <div class="product-current-image" id="productCurrentImageWrap">
                        <span>Current image:</span>
                        <img src="" id="productCurrentImage" alt="Current Product Image">
                    </div>
                </div>

                <div class="product-field">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" placeholder="0">
                    <span class="product-error" data-error-for="stock"></span>
                </div>

                <div class="product-field">
                    <label for="price">Price (<?= esc($currencySymbol) ?>)</label>
                    <input type="number" id="price" name="price" min="1" step="0.01" placeholder="0.00">
                    <span class="product-error" data-error-for="price"></span>
                </div>

                <div class="product-field">
                    <label for="discount">Discount (%)</label>
                    <input type="number" id="discount" name="discount" min="0" max="100" step="1" placeholder="0">
                    <span class="product-error" data-error-for="discount"></span>
                </div>

                <div class="product-field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Select status</option>
                        <option value="1">Active</option>
                        <option value="2">Low Stock</option>
                        <option value="3">Out of Stock</option>
                    </select>
                    <span class="product-error" data-error-for="status"></span>
                </div>
            </div>

            <div class="product-options">
                <div class="product-options-header">
                    <h4>Product Options</h4>
                    <button type="button" class="product-options-add" id="addProductOptionBtn">More Option</button>
                </div>
                <div id="productOptionsContainer"></div>
                <span class="product-error" data-error-for="options"></span>
            </div>

            <div class="product-form-actions">
                <button type="button" class="product-cancel-btn" id="cancelProductFormBtn">Cancel</button>
                <button type="submit" class="product-submit-btn" id="submitProductFormBtn">Save Product</button>
            </div>
        </form>
    </div>
</div>

<template id="productOptionTemplate">
    <div class="product-option-row">
        <div class="product-field">
            <label>Option Name</label>
            <input type="text" class="product-option-name" name="options[__index__][name]" placeholder="e.g. Material">
        </div>
        <div class="product-field">
            <label>Option Value</label>
            <input type="text" class="product-option-value" name="options[__index__][value]" placeholder="e.g. Cotton">
        </div>
        <button type="button" class="product-option-remove">Remove</button>
    </div>
</template>

<style>
.product-options {
    margin-top: 18px;
    padding: 16px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(241, 245, 249, 0.6));
}
.product-options #productOptionsContainer {
    max-height: 260px;
    overflow-y: auto;
    padding-right: 6px;
}
.product-options #productOptionsContainer::-webkit-scrollbar {
    width: 8px;
}
.product-options #productOptionsContainer::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.6);
    border-radius: 999px;
}
.product-options #productOptionsContainer::-webkit-scrollbar-track {
    background: transparent;
}
.product-options-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}
.product-options-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
}
.product-options-add {
    border: none;
    background: #111827;
    color: #ffffff;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.2px;
    transition: transform 120ms ease, box-shadow 120ms ease, background 120ms ease;
}
.product-options-add:hover {
    background: #1f2937;
    box-shadow: 0 8px 18px rgba(17, 24, 39, 0.2);
    transform: translateY(-1px);
}
.product-option-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px;
    align-items: end;
    padding: 12px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, 0.35);
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}
.product-option-row .product-field label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
}
.product-option-row .product-field input {
    height: 40px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    font-size: 14px;
}
.product-option-remove {
    border: 1px solid rgba(239, 68, 68, 0.3);
    background: rgba(239, 68, 68, 0.1);
    color: #b91c1c;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    height: 40px;
    transition: background 120ms ease, border 120ms ease;
}
.product-option-remove:hover {
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.5);
}
.products-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}
.products-bulk-delete-btn {
    border: 1px solid rgba(220, 38, 38, 0.24);
    background: rgba(254, 242, 242, 0.92);
    color: #b91c1c;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 120ms ease, border 120ms ease, opacity 120ms ease;
}
.products-bulk-delete-btn svg {
    width: 16px;
    height: 16px;
}
.products-bulk-delete-btn:hover:not(:disabled) {
    background: #fee2e2;
    border-color: rgba(220, 38, 38, 0.42);
}
.products-bulk-delete-btn:disabled {
    opacity: 0.48;
    cursor: not-allowed;
}
.products-select-col {
    width: 44px;
    text-align: center;
}
.products-select-checkbox,
.products-row-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
@media (max-width: 768px) {
    .products-header-actions {
        width: 100%;
        justify-content: stretch;
    }
    .products-header-actions button {
        flex: 1 1 160px;
        justify-content: center;
    }
    .product-option-row {
        grid-template-columns: 1fr;
    }
    .product-option-remove {
        width: 100%;
    }
}
</style>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>

<script>
$(document).ready(function () {
    const productsTable = initAdminDataTable('#productsTable', {
        pageLength: 10,
        columnDefs: [
            { orderable: false, searchable: false, targets: [0, 2, 12] }
        ],
        order: [[1, 'desc']],
        language: {
            searchPlaceholder: 'Search products...'
        }
    });

    const modal = $('#productModal');
    const form = $('#addProductForm');
    const alertBox = $('#productAlert');
    const submitBtn = $('#submitProductFormBtn');
    const modalTitle = $('#productModalTitle');
    const recordIdInput = $('#productRecordId');
    const currentImageWrap = $('#productCurrentImageWrap');
    const currentImage = $('#productCurrentImage');
    const optionsContainer = $('#productOptionsContainer');
    const optionTemplate = $('#productOptionTemplate').html();
    const selectAllProducts = $('#selectAllProducts');
    const deleteSelectedBtn = $('#deleteSelectedProductsBtn');
    let optionIndex = 0;
    let editRow = null;

    function getStatusClass(status) {
        const code = Number(status);
        if (code === 2) return 'products-low';
        if (code === 3) return 'products-out';
        return 'products-active';
    }

    function getStatusText(status) {
        const code = Number(status);
        if (code === 2) return 'Low Stock';
        if (code === 3) return 'Out of Stock';
        return 'Active';
    }

    function showAlert(type, message) {
        alertBox.removeClass('success error').addClass(type).text(message).fadeIn(120);
        setTimeout(function () {
            alertBox.fadeOut(200);
        }, 2800);
    }

    async function showWarning(message, title) {
        if (window.AdminConfirm) {
            await window.AdminConfirm.open({
                title: title || 'Warning',
                message: message,
                confirmText: 'OK',
                cancelText: ''
            });
            return;
        }

        window.alert(message);
    }

    function clearErrors() {
        form.find('.product-error').text('');
    }

    function openForm() {
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
        modalTitle.text('Add New Product');
        submitBtn.text('Save Product');
        recordIdInput.val('');
        currentImageWrap.hide();
        editRow = null;
        optionsContainer.empty();
        optionIndex = 0;
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        clearErrors();
        modalTitle.text('Add New Product');
        submitBtn.text('Save Product');
        recordIdInput.val('');
        currentImageWrap.hide();
        editRow = null;
        optionsContainer.empty();
        optionIndex = 0;
    }

    function getImageCell(imageUrl, productName) {
        if (!imageUrl) {
            return '<span class="product-thumb-placeholder">No Image</span>';
        }

        return '<img src="' + imageUrl + '" alt="' + productName + '" class="product-thumb">';
    }

    function getActionButton(product) {
        const safeName = $('<div>').text(product.product_name).html();
        const safeDescription = $('<div>').text(product.description || '').html();
        const safeCategory = $('<div>').text(product.category).html();
        const safeBrand = $('<div>').text(product.brand).html();
        const safeOptionsPayload = $('<div>').text(encodeProductOptionsPayload(product.options_json || JSON.stringify(product.options || []))).html();
        const categoryId = String(product.category_id || '');
        const brandId = String(product.brand_id || '');

        const editButton = '<button type="button" class="product-edit-btn" ' +
            'data-id="' + product.id + '" ' +
            'data-name="' + safeName + '" ' +
            'data-description="' + safeDescription + '" ' +
            'data-category-id="' + categoryId + '" ' +
            'data-category-name="' + safeCategory + '" ' +
            'data-brand-id="' + brandId + '" ' +
            'data-brand-name="' + safeBrand + '" ' +
            'data-stock="' + product.stock + '" ' +
            'data-price="' + product.price + '" ' +
            'data-discount="' + (product.discount || '0.00') + '" ' +
            'data-status="' + product.status + '" ' +
            'data-options-payload="' + safeOptionsPayload + '" ' +
            'data-image-url="' + (product.image_url || '') + '">' +
            'Edit</button>';

        const deleteButton = '<button type="button" class="product-delete-btn" ' +
            'data-id="' + product.id + '" ' +
            'data-status="' + product.status + '">' +
            'Delete</button>';

        return '<div class="product-action-group">' + editButton + deleteButton + '</div>';
    }

    function getSelectCheckbox(id, productName, status) {
        const safeName = $('<div>').text(productName || 'product').html();
        return '<input type="checkbox" class="products-row-checkbox" value="' + id + '" data-status="' + status + '" aria-label="Select ' + safeName + '">';
    }

    function getSelectedProductIds() {
        return $('.products-row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function hasSelectedActiveProducts() {
        return $('.products-row-checkbox:checked').filter(function () {
            return Number($(this).data('status')) === 1;
        }).length > 0;
    }

    function encodeProductOptionsPayload(optionsJson) {
        try {
            return btoa(unescape(encodeURIComponent(optionsJson || '[]')));
        } catch (error) {
            return btoa('[]');
        }
    }

    function decodeProductOptionsPayload(payload) {
        if (!payload) {
            return '[]';
        }

        try {
            return decodeURIComponent(escape(atob(payload)));
        } catch (error) {
            return '[]';
        }
    }

    function parseProductOptions(rawOptions) {
        if (!rawOptions) {
            return [];
        }

        if (Array.isArray(rawOptions)) {
            return rawOptions;
        }

        try {
            const parsed = JSON.parse(rawOptions);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function addProductOptionRow(option) {
        const markup = optionTemplate.replace(/__index__/g, optionIndex);
        const row = $(markup.trim());
        row.find('.product-option-name').val(option?.name || '');
        row.find('.product-option-value').val(option?.value || '');
        optionsContainer.append(row);
        optionIndex += 1;
    }

    function updateBulkControls() {
        const selectedCount = getSelectedProductIds().length;
        const rowCheckboxes = $('.products-row-checkbox');
        const checkedCount = rowCheckboxes.filter(':checked').length;

        deleteSelectedBtn.prop('disabled', selectedCount === 0);
        deleteSelectedBtn.find('span').text(selectedCount > 0 ? 'Delete Selected (' + selectedCount + ')' : 'Delete Selected');

        if (rowCheckboxes.length === 0 || checkedCount === 0) {
            selectAllProducts.prop({ checked: false, indeterminate: false });
        } else if (checkedCount === rowCheckboxes.length) {
            selectAllProducts.prop({ checked: true, indeterminate: false });
        } else {
            selectAllProducts.prop({ checked: false, indeterminate: true });
        }
    }

    function getDiscountText(discount) {
        const value = Number(discount || 0);
        if (value <= 0) return '-';
        return value.toFixed(2).replace(/\.?0+$/, '') + '%';
    }

    function ensureCategoryOption(categoryId, categoryName) {
        const idValue = String(categoryId || '').trim();
        const nameValue = String(categoryName || '').trim();
        if (!idValue) return;

        const categorySelect = $('#category');
        const exists = categorySelect.find('option').filter(function () {
            return $(this).val() === idValue;
        }).length > 0;

        if (!exists) {
            categorySelect.append($('<option>', {
                value: idValue,
                text: nameValue || ('Category #' + idValue)
            }));
        }
    }

    function ensureBrandOption(brandId, brandName) {
        const idValue = String(brandId || '').trim();
        const nameValue = String(brandName || '').trim();
        if (!idValue) return;

        const brandSelect = $('#brand');
        const exists = brandSelect.find('option').filter(function () {
            return $(this).val() === idValue;
        }).length > 0;

        if (!exists) {
            brandSelect.append($('<option>', {
                value: idValue,
                text: nameValue || ('Brand #' + idValue)
            }));
        }
    }

    $('#openProductFormBtn').on('click', openForm);
    $('#closeProductFormBtn, #cancelProductFormBtn').on('click', closeForm);

    selectAllProducts.on('change', function () {
        const checked = $(this).is(':checked');
        if (productsTable && productsTable.rows) {
            $(productsTable.rows({ search: 'applied' }).nodes()).find('.products-row-checkbox').prop('checked', checked);
        } else {
            $('.products-row-checkbox').prop('checked', checked);
        }
        updateBulkControls();
    });

    $(document).on('change', '.products-row-checkbox', updateBulkControls);

    if (productsTable && productsTable.on) {
        productsTable.on('draw', updateBulkControls);
    }

    $('#addProductOptionBtn').on('click', function () {
        addProductOptionRow();
    });

    $(document).on('click', '.product-option-remove', function () {
        $(this).closest('.product-option-row').remove();
    });

    $('#discount').on('input blur', function () {
        const rawValue = $(this).val();
        if (rawValue === '') return;

        const parsed = Number(rawValue);
        if (!Number.isFinite(parsed)) return;

        const clamped = Math.min(100, Math.max(0, Math.round(parsed)));
        $(this).val(clamped);
    });

    $(document).on('click', '.product-edit-btn', function () {
        const btn = $(this);
        editRow = productsTable && productsTable.row ? productsTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#productName').val(btn.data('name'));
        $('#description').val(btn.data('description'));
        ensureCategoryOption(btn.data('category-id'), btn.data('category-name'));
        $('#category').val(String(btn.data('category-id') || ''));
        ensureBrandOption(btn.data('brand-id'), btn.data('brand-name'));
        $('#brand').val(String(btn.data('brand-id') || ''));
        $('#stock').val(btn.data('stock'));
        $('#price').val(btn.data('price'));
        const editDiscount = Number(btn.data('discount'));
        $('#discount').val(Number.isFinite(editDiscount) ? Math.round(editDiscount) : 0);
        $('#status').val(String(btn.data('status')));

        const imageUrl = btn.data('image-url');
        if (imageUrl) {
            currentImage.attr('src', imageUrl);
            currentImageWrap.show();
        } else {
            currentImageWrap.hide();
        }

        optionsContainer.empty();
        optionIndex = 0;
        parseProductOptions(decodeProductOptionsPayload(btn.attr('data-options-payload'))).forEach(function (option) {
            addProductOptionRow(option);
        });

        modalTitle.text('Edit Product');
        submitBtn.text('Update Product');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    modal.on('click', function (event) {
        if (event.target === this) closeForm();
    });

    if (window.AdminImageCropper) {
        window.AdminImageCropper.attach('#productImage', {
            errorSelector: '[data-error-for="product_image"]',
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
            maxSizeBytes: 2 * 1024 * 1024,
            aspectRatio: 1,
            outputType: 'image/jpeg',
            outputQuality: 0.9,
            outputFileName: 'product-cropped.jpg',
            onCropped: function (blob) {
                const previewUrl = URL.createObjectURL(blob);
                currentImage.attr('src', previewUrl);
                currentImageWrap.show();
            }
        });
    }

    form.on('submit', function (event) {
        event.preventDefault();
        clearErrors();

        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: recordIdInput.val()
                ? "<?= base_url('admin/products/update') ?>"
                : "<?= base_url('admin/products/add') ?>",
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (!response.status) {
                    submitBtn.prop('disabled', false).text('Save Product');

                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save product.');
                    }
                    return;
                }

                const product = response.product;
                const statusClass = getStatusClass(product.status);
                const statusText = product.status_text || getStatusText(product.status);
                const safeDescription = $('<div>').text(product.description || '-').html();
                const rowData = [
                    getSelectCheckbox(product.id, product.product_name, product.status),
                    '<span class="products-id">#' + product.product_id + '</span>',
                    getImageCell(product.image_url, product.product_name),
                    product.product_name,
                    '<span class="product-desc-cell" title="' + safeDescription + '">' + safeDescription + '</span>',
                    product.category,
                    product.brand,
                    product.stock,
                    '<span class="products-price">' + (window.appData?.currencySymbol || <?= json_encode($currencySymbol) ?>) + ' ' + product.price + '</span>',
                    product.discount_text || getDiscountText(product.discount),
                    '<span class="products-status ' + statusClass + '">' + statusText + '</span>',
                    product.updated,
                    getActionButton(product)
                ];

                if (recordIdInput.val() && editRow && productsTable && productsTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (productsTable && productsTable.row) {
                    productsTable.row.add(rowData).draw(false);
                } else {
                    $('#productsTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                submitBtn.prop('disabled', false).text('Save Product');
                closeForm();
                showAlert('success', response.message);
                updateBulkControls();
            },
            error: function () {
                submitBtn.prop('disabled', false).text('Save Product');
                showAlert('error', 'Unexpected error occurred while adding product.');
            }
        });
    });

    async function deleteProductsByIds(ids, confirmMessage) {
        const confirmed = window.AdminConfirm
            ? await window.AdminConfirm.open({
                title: 'Delete Products',
                message: confirmMessage,
                confirmText: 'Delete',
                cancelText: 'Cancel'
            })
            : window.confirm(confirmMessage);

        if (!confirmed) {
            return;
        }

        deleteSelectedBtn.prop('disabled', true).find('span').text('Deleting...');

        $.ajax({
            url: "<?= base_url('admin/products/bulk-delete') ?>",
            type: 'POST',
            data: { ids: ids },
            dataType: 'json',
            success: function (response) {
                if (!response.status) {
                    if (response.type === 'warning') {
                        showWarning(response.message || 'Active product cannot be deleted.', 'Warning');
                    } else {
                        showAlert('error', response.message || 'Unable to delete selected products.');
                    }
                    updateBulkControls();
                    return;
                }

                const deletedIds = (response.deleted_ids || []).map(String);
                if (productsTable && productsTable.rows) {
                    productsTable.rows(function (idx, data, node) {
                        const checkbox = $(node).find('.products-row-checkbox');
                        return deletedIds.indexOf(String(checkbox.val())) !== -1;
                    }).remove().draw(false);
                } else {
                    deletedIds.forEach(function (id) {
                        $('.products-row-checkbox[value="' + id + '"]').closest('tr').remove();
                    });
                }

                selectAllProducts.prop({ checked: false, indeterminate: false });
                showAlert('success', response.message || 'Selected products deleted successfully.');
                updateBulkControls();
            },
            error: function () {
                showAlert('error', 'Unexpected error occurred while deleting products.');
                updateBulkControls();
            }
        });
    }

    $(document).on('click', '.product-delete-btn', async function () {
        const btn = $(this);
        const productId = String(btn.data('id') || '');
        const status = Number(btn.data('status'));

        if (!productId) {
            showAlert('error', 'Invalid product id.');
            return;
        }

        if (status === 1) {
            await showWarning('Active product cannot be deleted. Change status before deleting.', 'Warning');
            return;
        }

        await deleteProductsByIds([productId], 'Delete this product?');
    });

    deleteSelectedBtn.on('click', async function () {
        const selectedIds = getSelectedProductIds();
        if (selectedIds.length === 0) {
            showAlert('error', 'Select at least one product.');
            return;
        }

        if (hasSelectedActiveProducts()) {
            await showWarning('Active products cannot be deleted. Change their status before deleting.', 'Warning');
            return;
        }

        const confirmMessage = selectedIds.length === 1
            ? 'Delete the selected product?'
            : 'Delete ' + selectedIds.length + ' selected products?';

        await deleteProductsByIds(selectedIds, confirmMessage);
    });

    updateBulkControls();
});
</script>

<?php include('footer.php'); ?>
