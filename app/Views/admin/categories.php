<?php
$page = 'categories';
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
                <h2 class="categories-title">Categories Management</h2>
                <p class="categories-subtitle">Organize product groups and control visibility.</p>
            </div>
            <button type="button" class="categories-add-btn" id="openCategoryFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Category</span>
            </button>
        </div>

        <div class="categories-card-body">
            <div id="categoryAlert" class="category-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="categoriesTable" class="table categories-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)) : ?>
                            <?php foreach ($categories as $category) : ?>
                                <?php
                                $statusValue = (int) ($category['ct_status'] ?? 1);
                                $statusMeta = $statusMetaMap[$statusValue] ?? $statusMetaMap[1];
                                $updatedDate = isset($category['ct_updated_at']) && !empty($category['ct_updated_at'])
                                    ? date('d-m-Y', strtotime($category['ct_updated_at']))
                                    : date('d-m-Y');
                                $categoryCode = 'C' . str_pad((string) ($category['id'] ?? 0), 4, '0', STR_PAD_LEFT);
                                ?>
                                <tr data-id="<?= esc((string) ($category['id'] ?? 0)) ?>">
                                    <td><span class="categories-id">#<?= esc($categoryCode) ?></span></td>
                                    <td><?= esc($category['ct_name'] ?? '-') ?></td>
                                    <td><?= esc((string) ($category['ct_products'] ?? 0)) ?></td>
                                    <td><span class="categories-status <?= esc($statusMeta['class']) ?>"><?= esc($statusMeta['text']) ?></span></td>
                                    <td><?= esc($updatedDate) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn category-edit-btn"
                                            data-id="<?= esc((string) ($category['id'] ?? 0)) ?>"
                                            data-name="<?= esc($category['ct_name'] ?? '') ?>"
                                            data-status="<?= esc((string) ($statusValue)) ?>"
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

<div class="category-modal" id="categoryModal" aria-hidden="true">
    <div class="category-modal-card">
        <div class="category-modal-head">
            <h3 id="categoryModalTitle">Add New Category</h3>
            <button type="button" class="category-modal-close" id="closeCategoryFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="addCategoryForm" class="category-form">
            <input type="hidden" id="categoryRecordId" name="id" value="">
            <div class="category-form-grid">
                <div class="category-field">
                    <label for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" name="category_name" placeholder="Enter category name">
                    <span class="category-error" data-error-for="category_name"></span>
                </div>

                <div class="category-field">
                    <label for="categoryStatus">Status</label>
                    <select id="categoryStatus" name="status">
                        <option value="">Select status</option>
                        <option value="1">Active</option>
                        <option value="2">In Review</option>
                        <option value="3">Inactive</option>
                    </select>
                    <span class="category-error" data-error-for="status"></span>
                </div>
            </div>

            <div class="category-form-actions">
                <button type="button" class="category-cancel-btn" id="cancelCategoryFormBtn">Cancel</button>
                <button type="submit" class="category-submit-btn" id="submitCategoryFormBtn">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const categoriesTable = initAdminDataTable('#categoriesTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search categories...'
        }
    });

    const modal = $('#categoryModal');
    const form = $('#addCategoryForm');
    const alertBox = $('#categoryAlert');
    const submitBtn = $('#submitCategoryFormBtn');
    const modalTitle = $('#categoryModalTitle');
    const recordIdInput = $('#categoryRecordId');
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
        modalTitle.text('Add New Category');
        submitBtn.text('Save Category');
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        modalTitle.text('Add New Category');
        submitBtn.text('Save Category');
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    $('#openCategoryFormBtn').on('click', openForm);
    $('#closeCategoryFormBtn, #cancelCategoryFormBtn').on('click', closeForm);
    $(document).on('click', '.product-edit-btn', function () {
        const btn = $(this);
        editRow = categoriesTable && categoriesTable.row ? categoriesTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#categoryName').val(btn.data('name'));
        $('#categoryStatus').val(String(btn.data('status')));

        modalTitle.text('Edit Category');
        submitBtn.text('Update Category');
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
                ? "<?= base_url('admin/categories/update') ?>"
                : "<?= base_url('admin/categories/add') ?>",
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text('Save Category');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save category.');
                    }
                    return;
                }

                const category = response.category;
                const statusClass = getStatusClass(category.status);
                const statusText = category.status_text || getStatusText(category.status);
                const rowData = [
                    '<span class="categories-id">#' + category.category_id + '</span>',
                    category.category_name,
                    category.products,
                    '<span class="categories-status ' + statusClass + '">' + statusText + '</span>',
                    category.updated,
                    '<button type="button" class="product-edit-btn category-edit-btn" data-id="' + category.id + '" data-name="' + $('<div>').text(category.category_name).html() + '" data-status="' + category.status + '">Edit</button>'
                ];

                if (recordIdInput.val() && editRow && categoriesTable && categoriesTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (categoriesTable && categoriesTable.row) {
                    categoriesTable.row.add(rowData).draw(false);
                } else {
                    $('#categoriesTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text('Save Category');
                showAlert('error', 'Unexpected error occurred while adding category.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
