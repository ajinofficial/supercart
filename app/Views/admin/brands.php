<?php
$page = 'brands';
include('header.php');
include('menus.php');

$statusMetaMap = [
    1 => ['text' => 'Active', 'class' => 'categories-active'],
    2 => ['text' => 'In Review', 'class' => 'categories-review'],
    3 => ['text' => 'Inactive', 'class' => 'categories-inactive'],
];
?>

<div class="categories-page">
    <div class="categories-card shadow-sm">
        <div class="categories-card-header">
            <div>
                <h2 class="categories-title">Brands Management</h2>
                <p class="categories-subtitle">Manage available brands and monitor product mapping.</p>
            </div>
            <button type="button" class="categories-add-btn" id="openBrandFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Brand</span>
            </button>
        </div>

        <div class="categories-card-body">
            <div id="brandAlert" class="category-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="brandsTable" class="table categories-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Brand ID</th>
                            <th>Brand Name</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($brands)) : ?>
                            <?php foreach ($brands as $brand) : ?>
                                <?php
                                $statusValue = (int) ($brand['br_status'] ?? 1);
                                $statusMeta = $statusMetaMap[$statusValue] ?? $statusMetaMap[1];
                                $updatedDate = isset($brand['br_updated_at']) && !empty($brand['br_updated_at'])
                                    ? date('d-m-Y', strtotime($brand['br_updated_at']))
                                    : date('d-m-Y');
                                $brandCode = 'B' . str_pad((string) ($brand['id'] ?? 0), 4, '0', STR_PAD_LEFT);
                                $brandName = trim((string) ($brand['br_name'] ?? '-'));
                                $productsCount = (int) ($brand['br_products'] ?? 0);
                                ?>
                                <tr data-id="<?= esc((string) ($brand['id'] ?? 0)) ?>">
                                    <td><span class="categories-id">#<?= esc($brandCode) ?></span></td>
                                    <td><?= esc($brandName) ?></td>
                                    <td><?= esc((string) $productsCount) ?></td>
                                    <td><span class="categories-status <?= esc($statusMeta['class']) ?>"><?= esc($statusMeta['text']) ?></span></td>
                                    <td><?= esc($updatedDate) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn brand-edit-btn"
                                            data-id="<?= esc((string) ($brand['id'] ?? 0)) ?>"
                                            data-name="<?= esc($brandName) ?>"
                                            data-status="<?= esc((string) $statusValue) ?>"
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

<div class="category-modal" id="brandModal" aria-hidden="true">
    <div class="category-modal-card">
        <div class="category-modal-head">
            <h3 id="brandModalTitle">Add New Brand</h3>
            <button type="button" class="category-modal-close" id="closeBrandFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="brandForm" class="category-form">
            <input type="hidden" id="brandRecordId" name="id" value="">
            <div class="category-form-grid">
                <div class="category-field">
                    <label for="brandName">Brand Name</label>
                    <input type="text" id="brandName" name="brand_name" placeholder="Enter brand name">
                    <span class="category-error" data-error-for="brand_name"></span>
                </div>

                <div class="category-field">
                    <label for="brandStatus">Status</label>
                    <select id="brandStatus" name="status">
                        <option value="">Select status</option>
                        <option value="1">Active</option>
                        <option value="2">In Review</option>
                        <option value="3">Inactive</option>
                    </select>
                    <span class="category-error" data-error-for="status"></span>
                </div>
            </div>

            <div class="category-form-actions">
                <button type="button" class="category-cancel-btn" id="cancelBrandFormBtn">Cancel</button>
                <button type="submit" class="category-submit-btn" id="submitBrandFormBtn">Save Brand</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const brandsTable = initAdminDataTable('#brandsTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search brands...'
        }
    });

    const modal = $('#brandModal');
    const form = $('#brandForm');
    const alertBox = $('#brandAlert');
    const submitBtn = $('#submitBrandFormBtn');
    const modalTitle = $('#brandModalTitle');
    const recordIdInput = $('#brandRecordId');
    let editRow = null;

    function getStatusClass(status) {
        const code = Number(status);
        if (code === 2) return 'categories-review';
        if (code === 3) return 'categories-inactive';
        return 'categories-active';
    }

    function getStatusText(status) {
        const code = Number(status);
        if (code === 2) return 'In Review';
        if (code === 3) return 'Inactive';
        return 'Active';
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

    function openForm() {
        modal.addClass('active').attr('aria-hidden', 'false');
        modalTitle.text('Add New Brand');
        submitBtn.text('Save Brand');
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        modalTitle.text('Add New Brand');
        submitBtn.text('Save Brand');
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    $('#openBrandFormBtn').on('click', openForm);
    $('#closeBrandFormBtn, #cancelBrandFormBtn').on('click', closeForm);

    $(document).on('click', '.brand-edit-btn', function () {
        const btn = $(this);
        editRow = brandsTable && brandsTable.row ? brandsTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#brandName').val(btn.data('name'));
        $('#brandStatus').val(String(btn.data('status')));

        modalTitle.text('Edit Brand');
        submitBtn.text('Update Brand');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    modal.on('click', function (event) {
        if (event.target === this) closeForm();
    });

    form.on('submit', function (event) {
        event.preventDefault();
        clearErrors();
        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: recordIdInput.val()
                ? "<?= base_url('admin/brands/update') ?>"
                : "<?= base_url('admin/brands/add') ?>",
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Brand' : 'Save Brand');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save brand.');
                    }
                    return;
                }

                const brand = response.brand;
                const statusClass = getStatusClass(brand.status);
                const statusText = brand.status_text || getStatusText(brand.status);
                const rowData = [
                    '<span class="categories-id">#' + brand.brand_id + '</span>',
                    brand.brand_name,
                    brand.products,
                    '<span class="categories-status ' + statusClass + '">' + statusText + '</span>',
                    brand.updated,
                    '<button type="button" class="product-edit-btn brand-edit-btn" data-id="' + brand.id + '" data-name="' + $('<div>').text(brand.brand_name).html() + '" data-status="' + brand.status + '">Edit</button>'
                ];

                if (recordIdInput.val() && editRow && brandsTable && brandsTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (brandsTable && brandsTable.row) {
                    brandsTable.row.add(rowData).draw(false);
                } else {
                    $('#brandsTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Brand' : 'Save Brand');
                showAlert('error', 'Unexpected error occurred while saving brand.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
