<?php
$page = 'coupons';
include('header.php');
include('menus.php');

$statusMetaMap = [
    1 => ['text' => 'Active', 'class' => 'categories-active'],
    2 => ['text' => 'Draft', 'class' => 'categories-review'],
    3 => ['text' => 'Inactive', 'class' => 'categories-inactive'],
];
?>

<div class="categories-page">
    <div class="categories-card shadow-sm">
        <div class="categories-card-header">
            <div>
                <h2 class="categories-title">Coupons Management</h2>
                <p class="categories-subtitle">Create and manage promotional coupon codes.</p>
            </div>
            <button type="button" class="categories-add-btn" id="openCouponFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Coupon</span>
            </button>
        </div>

        <div class="categories-card-body">
            <div id="couponAlert" class="category-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="couponsTable" class="table categories-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Coupon ID</th>
                            <th>Title</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Order</th>
                            <th>Validity</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($coupons)) : ?>
                            <?php foreach ($coupons as $coupon) : ?>
                                <?php
                                $id = (int) ($coupon['id'] ?? 0);
                                $title = trim((string) ($coupon['cp_title'] ?? ''));
                                $code = trim((string) ($coupon['cp_code'] ?? ''));
                                $type = (int) ($coupon['cp_type'] ?? 1);
                                $typeText = $type === 2 ? 'Fixed' : 'Percentage';
                                $value = (float) ($coupon['cp_value'] ?? 0);
                                $maxDiscount = isset($coupon['cp_max_discount']) && $coupon['cp_max_discount'] !== null
                                    ? (float) $coupon['cp_max_discount']
                                    : null;
                                $valueText = $type === 2
                                    ? $currencySymbol . ' ' . number_format($value, 2)
                                    : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';
                                if ($type === 1 && $maxDiscount !== null && $maxDiscount > 0) {
                                    $valueText .= ' (Max ' . $currencySymbol . ' ' . number_format($maxDiscount, 2) . ')';
                                }
                                $minOrder = (float) ($coupon['cp_min_order'] ?? 0);
                                $startDate = trim((string) ($coupon['cp_start_date'] ?? ''));
                                $endDate = trim((string) ($coupon['cp_end_date'] ?? ''));
                                $validityText = 'Always';
                                if ($startDate !== '' || $endDate !== '') {
                                    $startText = $startDate !== '' ? date('d-m-Y', strtotime($startDate)) : 'Now';
                                    $endText = $endDate !== '' ? date('d-m-Y', strtotime($endDate)) : 'No end';
                                    $validityText = $startText . ' - ' . $endText;
                                }
                                $usedCount = (int) ($coupon['cp_used_count'] ?? 0);
                                $usageLimit = isset($coupon['cp_usage_limit']) && $coupon['cp_usage_limit'] !== null
                                    ? (int) $coupon['cp_usage_limit']
                                    : null;
                                $usageText = $usageLimit === null ? ($usedCount . ' / Unlimited') : ($usedCount . ' / ' . $usageLimit);
                                $status = (int) ($coupon['cp_status'] ?? 1);
                                $statusMeta = $statusMetaMap[$status] ?? $statusMetaMap[1];
                                $updatedRaw = $coupon['cp_updated_at'] ?? '';
                                $updated = $updatedRaw !== '' ? date('d-m-Y', strtotime((string) $updatedRaw)) : date('d-m-Y');
                                ?>
                                <tr data-id="<?= esc((string) $id) ?>">
                                    <td><span class="categories-id">#<?= esc('CP' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)) ?></span></td>
                                    <td><?= esc($title) ?></td>
                                    <td><strong><?= esc($code) ?></strong></td>
                                    <td><?= esc($typeText) ?></td>
                                    <td><?= esc($valueText) ?></td>
                                    <td><?= esc($currencySymbol) ?> <?= esc(number_format($minOrder, 2)) ?></td>
                                    <td><?= esc($validityText) ?></td>
                                    <td><?= esc($usageText) ?></td>
                                    <td><span class="categories-status <?= esc($statusMeta['class']) ?>"><?= esc($statusMeta['text']) ?></span></td>
                                    <td><?= esc($updated) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn coupon-edit-btn"
                                            data-id="<?= esc((string) $id) ?>"
                                            data-title="<?= esc($title) ?>"
                                            data-code="<?= esc($code) ?>"
                                            data-type="<?= esc((string) $type) ?>"
                                            data-value="<?= esc(number_format($value, 2, '.', '')) ?>"
                                            data-min-order="<?= esc(number_format($minOrder, 2, '.', '')) ?>"
                                            data-max-discount="<?= esc($maxDiscount !== null ? number_format($maxDiscount, 2, '.', '') : '') ?>"
                                            data-start-date="<?= esc($startDate) ?>"
                                            data-end-date="<?= esc($endDate) ?>"
                                            data-usage-limit="<?= esc($usageLimit !== null ? (string) $usageLimit : '') ?>"
                                            data-status="<?= esc((string) $status) ?>"
                                        >
                                            Edit
                                        </button>
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

<div class="category-modal" id="couponModal" aria-hidden="true">
    <div class="category-modal-card">
        <div class="category-modal-head">
            <h3 id="couponModalTitle">Add New Coupon</h3>
            <button type="button" class="category-modal-close" id="closeCouponFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="couponForm" class="category-form">
            <input type="hidden" id="couponRecordId" name="id" value="">
            <div class="category-form-grid">
                <div class="category-field">
                    <label for="couponTitle">Coupon Title</label>
                    <input type="text" id="couponTitle" name="coupon_title" placeholder="Festival Offer">
                    <span class="category-error" data-error-for="coupon_title"></span>
                </div>

                <div class="category-field">
                    <label for="couponCode">Coupon Code</label>
                    <input type="text" id="couponCode" name="coupon_code" placeholder="SAVE20">
                    <span class="category-error" data-error-for="coupon_code"></span>
                </div>

                <div class="category-field">
                    <label for="couponType">Coupon Type</label>
                    <select id="couponType" name="coupon_type">
                        <option value="">Select type</option>
                        <option value="1">Percentage</option>
                        <option value="2">Fixed</option>
                    </select>
                    <span class="category-error" data-error-for="coupon_type"></span>
                </div>

                <div class="category-field">
                    <label for="couponValue">Coupon Value</label>
                    <input type="number" id="couponValue" name="coupon_value" min="0.01" step="0.01" placeholder="0.00">
                    <span class="category-error" data-error-for="coupon_value"></span>
                </div>

                <div class="category-field">
                    <label for="minOrder">Minimum Order (<?= esc($currencySymbol) ?>)</label>
                    <input type="number" id="minOrder" name="min_order" min="0" step="0.01" placeholder="0.00">
                    <span class="category-error" data-error-for="min_order"></span>
                </div>

                <div class="category-field" id="maxDiscountWrap">
                    <label for="maxDiscount">Max Discount (<?= esc($currencySymbol) ?>)</label>
                    <input type="number" id="maxDiscount" name="max_discount" min="0" step="0.01" placeholder="Only for percentage">
                    <span class="category-error" data-error-for="max_discount"></span>
                </div>

                <div class="category-field">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="start_date">
                    <span class="category-error" data-error-for="start_date"></span>
                </div>

                <div class="category-field">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="end_date">
                    <span class="category-error" data-error-for="end_date"></span>
                </div>

                <div class="category-field">
                    <label for="usageLimit">Usage Limit</label>
                    <input type="number" id="usageLimit" name="usage_limit" min="1" step="1" placeholder="Leave empty for unlimited">
                    <span class="category-error" data-error-for="usage_limit"></span>
                </div>

                <div class="category-field">
                    <label for="couponStatus">Status</label>
                    <select id="couponStatus" name="status">
                        <option value="">Select status</option>
                        <option value="1">Active</option>
                        <option value="2">Draft</option>
                        <option value="3">Inactive</option>
                    </select>
                    <span class="category-error" data-error-for="status"></span>
                </div>
            </div>

            <div class="category-form-actions">
                <button type="button" class="category-cancel-btn" id="cancelCouponFormBtn">Cancel</button>
                <button type="submit" class="category-submit-btn" id="submitCouponFormBtn">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const couponsTable = initAdminDataTable('#couponsTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search coupons...'
        }
    });

    const modal = $('#couponModal');
    const form = $('#couponForm');
    const alertBox = $('#couponAlert');
    const submitBtn = $('#submitCouponFormBtn');
    const modalTitle = $('#couponModalTitle');
    const recordIdInput = $('#couponRecordId');
    const couponType = $('#couponType');
    const maxDiscountWrap = $('#maxDiscountWrap');
    const currencySymbol = window.appData?.currencySymbol || <?= json_encode($currencySymbol) ?>;
    let editRow = null;

    function getStatusClass(status) {
        const code = Number(status);
        if (code === 2) return 'categories-review';
        if (code === 3) return 'categories-inactive';
        return 'categories-active';
    }

    function getStatusText(status) {
        const code = Number(status);
        if (code === 2) return 'Draft';
        if (code === 3) return 'Inactive';
        return 'Active';
    }

    function getTypeText(type) {
        return Number(type) === 2 ? 'Fixed' : 'Percentage';
    }

    function clearErrors() {
        form.find('.category-error').text('');
    }

    function showAlert(type, message) {
        alertBox.removeClass('success error').addClass(type).text(message).fadeIn(120);
        setTimeout(function () {
            alertBox.fadeOut(200);
        }, 2800);
    }

    function toggleMaxDiscount() {
        const type = Number(couponType.val() || 1);
        if (type === 2) {
            maxDiscountWrap.hide();
            $('#maxDiscount').val('');
        } else {
            maxDiscountWrap.show();
        }
    }

    function openForm() {
        modal.addClass('active').attr('aria-hidden', 'false');
        modalTitle.text('Add New Coupon');
        submitBtn.text('Save Coupon');
        recordIdInput.val('');
        editRow = null;
        form[0].reset();
        clearErrors();
        couponType.val('1');
        toggleMaxDiscount();
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        modalTitle.text('Add New Coupon');
        submitBtn.text('Save Coupon');
        recordIdInput.val('');
        editRow = null;
        clearErrors();
        couponType.val('1');
        toggleMaxDiscount();
    }

    function getActionButton(coupon) {
        return '<button type="button" class="product-edit-btn coupon-edit-btn" ' +
            'data-id="' + coupon.id + '" ' +
            'data-title="' + $('<div>').text(coupon.title).html() + '" ' +
            'data-code="' + $('<div>').text(coupon.code).html() + '" ' +
            'data-type="' + coupon.type + '" ' +
            'data-value="' + coupon.value + '" ' +
            'data-min-order="' + coupon.min_order + '" ' +
            'data-max-discount="' + (coupon.max_discount !== null ? coupon.max_discount : '') + '" ' +
            'data-start-date="' + (coupon.start_date || '') + '" ' +
            'data-end-date="' + (coupon.end_date || '') + '" ' +
            'data-usage-limit="' + (coupon.usage_limit || '') + '" ' +
            'data-status="' + coupon.status + '">' +
            'Edit</button>';
    }

    $('#openCouponFormBtn').on('click', openForm);
    $('#closeCouponFormBtn, #cancelCouponFormBtn').on('click', closeForm);
    couponType.on('change', toggleMaxDiscount);

    $(document).on('click', '.coupon-edit-btn', function () {
        const btn = $(this);
        editRow = couponsTable && couponsTable.row ? couponsTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#couponTitle').val(btn.data('title'));
        $('#couponCode').val(btn.data('code'));
        $('#couponType').val(String(btn.data('type')));
        $('#couponValue').val(btn.data('value'));
        $('#minOrder').val(btn.data('min-order'));
        $('#maxDiscount').val(btn.data('max-discount'));
        $('#startDate').val(btn.data('start-date'));
        $('#endDate').val(btn.data('end-date'));
        $('#usageLimit').val(btn.data('usage-limit'));
        $('#couponStatus').val(String(btn.data('status')));

        toggleMaxDiscount();
        modalTitle.text('Edit Coupon');
        submitBtn.text('Update Coupon');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    modal.on('click', function (event) {
        if (event.target === this) closeForm();
    });

    form.on('submit', function (event) {
        event.preventDefault();
        clearErrors();
        const upperCode = String($('#couponCode').val() || '').trim().toUpperCase();
        $('#couponCode').val(upperCode);
        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: recordIdInput.val()
                ? "<?= base_url('admin/coupons/update') ?>"
                : "<?= base_url('admin/coupons/add') ?>",
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Coupon' : 'Save Coupon');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save coupon.');
                    }
                    return;
                }

                const coupon = response.coupon;
                const statusClass = getStatusClass(coupon.status);
                const statusText = coupon.status_text || getStatusText(coupon.status);
                const rowData = [
                    '<span class="categories-id">#' + coupon.coupon_id + '</span>',
                    coupon.title,
                    '<strong>' + coupon.code + '</strong>',
                    coupon.type_text || getTypeText(coupon.type),
                    coupon.value_text,
                    currencySymbol + ' ' + Number(coupon.min_order || 0).toFixed(2),
                    coupon.validity_text,
                    coupon.usage_text,
                    '<span class="categories-status ' + statusClass + '">' + statusText + '</span>',
                    coupon.updated,
                    getActionButton(coupon)
                ];

                if (recordIdInput.val() && editRow && couponsTable && couponsTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (couponsTable && couponsTable.row) {
                    couponsTable.row.add(rowData).draw(false);
                } else {
                    $('#couponsTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Coupon' : 'Save Coupon');
                showAlert('error', 'Unexpected error occurred while saving coupon.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
