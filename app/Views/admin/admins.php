<?php
$page = 'admins';
include('header.php');
include('menus.php');
?>

<div class="categories-page">
    <div class="categories-card shadow-sm">
        <div class="categories-card-header">
            <div>
                <h2 class="categories-title">Admins Management</h2>
                <p class="categories-subtitle">Manage administrator accounts and access contacts.</p>
            </div>
            <button type="button" class="categories-add-btn" id="openAdminFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Admin</span>
            </button>
        </div>

        <div class="categories-card-body">
            <div id="adminAlert" class="category-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="adminsTable" class="table categories-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Admin ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admins)) : ?>
                            <?php foreach ($admins as $admin) : ?>
                                <?php
                                $id = (int) ($admin['id'] ?? 0);
                                $name = trim((string) ($admin['us_name'] ?? ''));
                                $email = trim((string) ($admin['us_email'] ?? ''));
                                $phone = trim((string) ($admin['us_phone'] ?? ''));
                                $imageFile = trim((string) ($admin['us_image'] ?? ''));
                                if ($imageFile !== '' && is_file(FCPATH . 'uploads/customers/' . $imageFile)) {
                                    $imageUrl = base_url('uploads/customers/' . $imageFile);
                                } else {
                                    $imageUrl = base_url('uploads/customers/default-user.svg');
                                }
                                $joinedRaw = $admin['created_at'] ?? ($admin['us_created_at'] ?? '');
                                $joined = !empty($joinedRaw) ? date('d-m-Y', strtotime((string) $joinedRaw)) : date('d-m-Y');
                                ?>
                                <tr data-id="<?= esc((string) $id) ?>">
                                    <td><span class="categories-id">#<?= esc('AD' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)) ?></span></td>
                                    <td class="product-image-cell">
                                        <img src="<?= esc($imageUrl) ?>" alt="<?= esc($name) ?>" class="product-thumb">
                                    </td>
                                    <td><?= esc($name) ?></td>
                                    <td><?= esc($email) ?></td>
                                    <td><?= esc($phone) ?></td>
                                    <td><span class="categories-status categories-active">Admin</span></td>
                                    <td><?= esc($joined) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn admin-edit-btn"
                                            data-id="<?= esc((string) $id) ?>"
                                            data-name="<?= esc($name) ?>"
                                            data-email="<?= esc($email) ?>"
                                            data-phone="<?= esc($phone) ?>"
                                            data-image-url="<?= esc($imageUrl) ?>"
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

<div class="category-modal" id="adminModal" aria-hidden="true">
    <div class="category-modal-card">
        <div class="category-modal-head">
            <h3 id="adminModalTitle">Add New Admin</h3>
            <button type="button" class="category-modal-close" id="closeAdminFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="adminForm" class="category-form">
            <input type="hidden" id="adminRecordId" name="id" value="">
            <div class="category-form-grid">
                <div class="category-field">
                    <label for="adminImage">Admin Image</label>
                    <input type="file" id="adminImage" name="admin_image" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                    <span class="category-error" data-error-for="admin_image"></span>
                    <div class="product-current-image" id="adminCurrentImageWrap">
                        <span>Current image:</span>
                        <img src="<?= esc(base_url('uploads/customers/default-user.svg')) ?>" id="adminCurrentImage" alt="Admin Image">
                    </div>
                </div>

                <div class="category-field">
                    <label for="adminName">Admin Name</label>
                    <input type="text" id="adminName" name="name" placeholder="Enter admin name">
                    <span class="category-error" data-error-for="name"></span>
                </div>

                <div class="category-field">
                    <label for="adminEmail">Email</label>
                    <input type="email" id="adminEmail" name="email" placeholder="Enter email">
                    <span class="category-error" data-error-for="email"></span>
                </div>

                <div class="category-field">
                    <label for="adminPhone">Phone</label>
                    <input type="text" id="adminPhone" name="phone" placeholder="Enter phone number">
                    <span class="category-error" data-error-for="phone"></span>
                </div>

                <div class="category-field">
                    <label for="adminPassword">Password</label>
                    <input type="password" id="adminPassword" name="password" placeholder="Enter password">
                    <span class="category-error" data-error-for="password"></span>
                </div>
            </div>

            <div class="category-form-actions">
                <button type="button" class="category-cancel-btn" id="cancelAdminFormBtn">Cancel</button>
                <button type="submit" class="category-submit-btn" id="submitAdminFormBtn">Save Admin</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const adminsTable = initAdminDataTable('#adminsTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search admins...'
        }
    });

    const modal = $('#adminModal');
    const form = $('#adminForm');
    const alertBox = $('#adminAlert');
    const submitBtn = $('#submitAdminFormBtn');
    const modalTitle = $('#adminModalTitle');
    const recordIdInput = $('#adminRecordId');
    const passwordInput = $('#adminPassword');
    const adminImageInput = $('#adminImage');
    const currentImageWrap = $('#adminCurrentImageWrap');
    const currentImage = $('#adminCurrentImage');
    const defaultImageUrl = "<?= base_url('uploads/customers/default-user.svg') ?>";
    let editRow = null;

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
        modalTitle.text('Add New Admin');
        submitBtn.text('Save Admin');
        passwordInput.attr('placeholder', 'Enter password');
        adminImageInput.val('');
        currentImage.attr('src', defaultImageUrl);
        currentImageWrap.show();
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        modalTitle.text('Add New Admin');
        submitBtn.text('Save Admin');
        passwordInput.attr('placeholder', 'Enter password');
        recordIdInput.val('');
        adminImageInput.val('');
        currentImage.attr('src', defaultImageUrl);
        currentImageWrap.show();
        editRow = null;
        clearErrors();
    }

    function getImageCell(imageUrl, name) {
        const safeName = $('<div>').text(name).html();
        const resolved = imageUrl || defaultImageUrl;
        return '<img src="' + resolved + '" alt="' + safeName + '" class="product-thumb">';
    }

    function getActionButton(admin) {
        return '<button type="button" class="product-edit-btn admin-edit-btn" ' +
            'data-id="' + admin.id + '" ' +
            'data-name="' + $('<div>').text(admin.name).html() + '" ' +
            'data-email="' + $('<div>').text(admin.email).html() + '" ' +
            'data-phone="' + $('<div>').text(admin.phone).html() + '" ' +
            'data-image-url="' + (admin.image_url || defaultImageUrl) + '">' +
            'Edit</button>';
    }

    $('#openAdminFormBtn').on('click', openForm);
    $('#closeAdminFormBtn, #cancelAdminFormBtn').on('click', closeForm);

    $(document).on('click', '.admin-edit-btn', function () {
        const btn = $(this);
        editRow = adminsTable && adminsTable.row ? adminsTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#adminName').val(btn.data('name'));
        $('#adminEmail').val(btn.data('email'));
        $('#adminPhone').val(btn.data('phone'));
        adminImageInput.val('');
        currentImage.attr('src', btn.data('image-url') || defaultImageUrl);
        currentImageWrap.show();
        passwordInput.val('').attr('placeholder', 'Leave blank to keep current password');

        modalTitle.text('Edit Admin');
        submitBtn.text('Update Admin');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    if (window.AdminImageCropper) {
        window.AdminImageCropper.attach('#adminImage', {
            errorSelector: '[data-error-for="admin_image"]',
            maxSizeBytes: 2 * 1024 * 1024,
            aspectRatio: 1,
            outputType: 'image/png',
            outputFileName: 'admin-cropped.png',
            onCropped: function (blob) {
                const previewUrl = URL.createObjectURL(blob);
                currentImage.attr('src', previewUrl);
                currentImageWrap.show();
            }
        });
    }

    modal.on('click', function (event) {
        if (event.target === this) closeForm();
    });

    form.on('submit', function (event) {
        event.preventDefault();
        clearErrors();
        submitBtn.prop('disabled', true).text('Saving...');
        const formData = new FormData(form[0]);

        $.ajax({
            url: recordIdInput.val()
                ? "<?= base_url('admin/admins/update') ?>"
                : "<?= base_url('admin/admins/add') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Admin' : 'Save Admin');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save admin.');
                    }
                    return;
                }

                const admin = response.admin;
                const rowData = [
                    '<span class="categories-id">#' + admin.admin_id + '</span>',
                    getImageCell(admin.image_url, admin.name),
                    admin.name,
                    admin.email,
                    admin.phone,
                    '<span class="categories-status categories-active">Admin</span>',
                    admin.updated,
                    getActionButton(admin)
                ];

                if (recordIdInput.val() && editRow && adminsTable && adminsTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (adminsTable && adminsTable.row) {
                    adminsTable.row.add(rowData).draw(false);
                } else {
                    $('#adminsTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Admin' : 'Save Admin');
                showAlert('error', 'Unexpected error occurred while saving admin.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
