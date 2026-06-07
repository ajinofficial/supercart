<?php
$page = 'sellers';
include('header.php');
include('menus.php');
?>

<div class="categories-page">
    <div class="categories-card shadow-sm">
        <div class="categories-card-header">
            <div>
                <h2 class="categories-title">Sellers Management</h2>
                <p class="categories-subtitle">Manage seller accounts and business contacts.</p>
            </div>
            <button type="button" class="categories-add-btn" id="openSellerFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Seller</span>
            </button>
        </div>

        <div class="categories-card-body">
            <div id="sellerAlert" class="category-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="sellersTable" class="table categories-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Seller ID</th>
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
                        <?php if (!empty($sellers)) : ?>
                            <?php foreach ($sellers as $seller) : ?>
                                <?php
                                $id = (int) ($seller['id'] ?? 0);
                                $name = trim((string) ($seller['us_name'] ?? ''));
                                $email = trim((string) ($seller['us_email'] ?? ''));
                                $phone = trim((string) ($seller['us_phone'] ?? ''));
                                $imageFile = trim((string) ($seller['us_image'] ?? ''));
                                if ($imageFile !== '' && is_file(FCPATH . 'uploads/customers/' . $imageFile)) {
                                    $imageUrl = base_url('uploads/customers/' . $imageFile);
                                } else {
                                    $imageUrl = base_url('uploads/customers/default-user.svg');
                                }
                                $joinedRaw = $seller['created_at'] ?? ($seller['us_created_at'] ?? '');
                                $joined = !empty($joinedRaw) ? date('d-m-Y', strtotime((string) $joinedRaw)) : date('d-m-Y');
                                ?>
                                <tr data-id="<?= esc((string) $id) ?>">
                                    <td><span class="categories-id">#<?= esc('SL' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)) ?></span></td>
                                    <td class="product-image-cell">
                                        <img src="<?= esc($imageUrl) ?>" alt="<?= esc($name) ?>" class="product-thumb">
                                    </td>
                                    <td><?= esc($name) ?></td>
                                    <td><?= esc($email) ?></td>
                                    <td><?= esc($phone) ?></td>
                                    <td><span class="categories-status categories-active">Seller</span></td>
                                    <td><?= esc($joined) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn seller-edit-btn"
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

<div class="category-modal" id="sellerModal" aria-hidden="true">
    <div class="category-modal-card">
        <div class="category-modal-head">
            <h3 id="sellerModalTitle">Add New Seller</h3>
            <button type="button" class="category-modal-close" id="closeSellerFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="sellerForm" class="category-form">
            <input type="hidden" id="sellerRecordId" name="id" value="">
            <div class="category-form-grid">
                <div class="category-field">
                    <label for="sellerImage">Seller Image</label>
                    <input type="file" id="sellerImage" name="seller_image" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                    <span class="category-error" data-error-for="seller_image"></span>
                    <div class="product-current-image" id="sellerCurrentImageWrap">
                        <span>Current image:</span>
                        <img src="<?= esc(base_url('uploads/customers/default-user.svg')) ?>" id="sellerCurrentImage" alt="Seller Image">
                    </div>
                </div>

                <div class="category-field">
                    <label for="sellerName">Seller Name</label>
                    <input type="text" id="sellerName" name="name" placeholder="Enter seller name">
                    <span class="category-error" data-error-for="name"></span>
                </div>

                <div class="category-field">
                    <label for="sellerEmail">Email</label>
                    <input type="email" id="sellerEmail" name="email" placeholder="Enter email">
                    <span class="category-error" data-error-for="email"></span>
                </div>

                <div class="category-field">
                    <label for="sellerPhone">Phone</label>
                    <input type="text" id="sellerPhone" name="phone" placeholder="Enter phone number">
                    <span class="category-error" data-error-for="phone"></span>
                </div>

                <div class="category-field">
                    <label for="sellerPassword">Password</label>
                    <input type="password" id="sellerPassword" name="password" placeholder="Enter password">
                    <span class="category-error" data-error-for="password"></span>
                </div>
            </div>

            <div class="category-form-actions">
                <button type="button" class="category-cancel-btn" id="cancelSellerFormBtn">Cancel</button>
                <button type="submit" class="category-submit-btn" id="submitSellerFormBtn">Save Seller</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const sellersTable = initAdminDataTable('#sellersTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search sellers...'
        }
    });

    const modal = $('#sellerModal');
    const form = $('#sellerForm');
    const alertBox = $('#sellerAlert');
    const submitBtn = $('#submitSellerFormBtn');
    const modalTitle = $('#sellerModalTitle');
    const recordIdInput = $('#sellerRecordId');
    const passwordInput = $('#sellerPassword');
    const sellerImageInput = $('#sellerImage');
    const currentImageWrap = $('#sellerCurrentImageWrap');
    const currentImage = $('#sellerCurrentImage');
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
        modalTitle.text('Add New Seller');
        submitBtn.text('Save Seller');
        passwordInput.attr('placeholder', 'Enter password');
        sellerImageInput.val('');
        currentImage.attr('src', defaultImageUrl);
        currentImageWrap.show();
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        modalTitle.text('Add New Seller');
        submitBtn.text('Save Seller');
        passwordInput.attr('placeholder', 'Enter password');
        recordIdInput.val('');
        sellerImageInput.val('');
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

    function getActionButton(seller) {
        return '<button type="button" class="product-edit-btn seller-edit-btn" ' +
            'data-id="' + seller.id + '" ' +
            'data-name="' + $('<div>').text(seller.name).html() + '" ' +
            'data-email="' + $('<div>').text(seller.email).html() + '" ' +
            'data-phone="' + $('<div>').text(seller.phone).html() + '" ' +
            'data-image-url="' + (seller.image_url || defaultImageUrl) + '">' +
            'Edit</button>';
    }

    $('#openSellerFormBtn').on('click', openForm);
    $('#closeSellerFormBtn, #cancelSellerFormBtn').on('click', closeForm);

    $(document).on('click', '.seller-edit-btn', function () {
        const btn = $(this);
        editRow = sellersTable && sellersTable.row ? sellersTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#sellerName').val(btn.data('name'));
        $('#sellerEmail').val(btn.data('email'));
        $('#sellerPhone').val(btn.data('phone'));
        sellerImageInput.val('');
        currentImage.attr('src', btn.data('image-url') || defaultImageUrl);
        currentImageWrap.show();
        passwordInput.val('').attr('placeholder', 'Leave blank to keep current password');

        modalTitle.text('Edit Seller');
        submitBtn.text('Update Seller');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    if (window.AdminImageCropper) {
        window.AdminImageCropper.attach('#sellerImage', {
            errorSelector: '[data-error-for="seller_image"]',
            maxSizeBytes: 2 * 1024 * 1024,
            aspectRatio: 1,
            outputType: 'image/png',
            outputFileName: 'seller-cropped.png',
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
                ? "<?= base_url('admin/sellers/update') ?>"
                : "<?= base_url('admin/sellers/add') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Seller' : 'Save Seller');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save seller.');
                    }
                    return;
                }

                const seller = response.seller;
                const rowData = [
                    '<span class="categories-id">#' + seller.seller_id + '</span>',
                    getImageCell(seller.image_url, seller.name),
                    seller.name,
                    seller.email,
                    seller.phone,
                    '<span class="categories-status categories-active">Seller</span>',
                    seller.updated,
                    getActionButton(seller)
                ];

                if (recordIdInput.val() && editRow && sellersTable && sellersTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (sellersTable && sellersTable.row) {
                    sellersTable.row.add(rowData).draw(false);
                } else {
                    $('#sellersTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Seller' : 'Save Seller');
                showAlert('error', 'Unexpected error occurred while saving seller.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
