<?php
$page = 'customers';
include('header.php');
include('menus.php');
?>

<div class="categories-page">
    <div class="categories-card shadow-sm">
        <div class="categories-card-header">
            <div>
                <h2 class="categories-title">Customers Management</h2>
                <p class="categories-subtitle">Manage customer accounts and contact details.</p>
            </div>
            <button type="button" class="categories-add-btn" id="openCustomerFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Customer</span>
            </button>
        </div>

        <div class="categories-card-body">
            <div id="customerAlert" class="category-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="customersTable" class="table categories-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
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
                        <?php if (!empty($customers)) : ?>
                            <?php foreach ($customers as $customer) : ?>
                                <?php
                                $id = (int) ($customer['id'] ?? 0);
                                $name = trim((string) ($customer['us_name'] ?? ''));
                                $email = trim((string) ($customer['us_email'] ?? ''));
                                $phone = trim((string) ($customer['us_phone'] ?? ''));
                                $imageFile = trim((string) ($customer['us_image'] ?? ''));
                                if ($imageFile !== '' && is_file(FCPATH . 'uploads/customers/' . $imageFile)) {
                                    $imageUrl = base_url('uploads/customers/' . $imageFile);
                                } else {
                                    $imageUrl = base_url('uploads/customers/default-user.svg');
                                }
                                $joinedRaw = $customer['created_at'] ?? ($customer['us_created_at'] ?? '');
                                $joined = !empty($joinedRaw) ? date('d-m-Y', strtotime((string) $joinedRaw)) : date('d-m-Y');
                                ?>
                                <tr data-id="<?= esc((string) $id) ?>">
                                    <td><span class="categories-id">#<?= esc('CU' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)) ?></span></td>
                                    <td class="product-image-cell">
                                        <img src="<?= esc($imageUrl) ?>" alt="<?= esc($name) ?>" class="product-thumb">
                                    </td>
                                    <td><?= esc($name) ?></td>
                                    <td><?= esc($email) ?></td>
                                    <td><?= esc($phone) ?></td>
                                    <td><span class="categories-status categories-active">Customer</span></td>
                                    <td><?= esc($joined) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn customer-edit-btn"
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

<div class="category-modal" id="customerModal" aria-hidden="true">
    <div class="category-modal-card">
        <div class="category-modal-head">
            <h3 id="customerModalTitle">Add New Customer</h3>
            <button type="button" class="category-modal-close" id="closeCustomerFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="customerForm" class="category-form">
            <input type="hidden" id="customerRecordId" name="id" value="">
            <div class="category-form-grid">
                <div class="category-field">
                    <label for="customerImage">Customer Image</label>
                    <input type="file" id="customerImage" name="customer_image" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                    <span class="category-error" data-error-for="customer_image"></span>
                    <div class="product-current-image" id="customerCurrentImageWrap">
                        <span>Current image:</span>
                        <img src="<?= esc(base_url('uploads/customers/default-user.svg')) ?>" id="customerCurrentImage" alt="Customer Image">
                    </div>
                </div>

                <div class="category-field">
                    <label for="customerName">Customer Name</label>
                    <input type="text" id="customerName" name="name" placeholder="Enter customer name">
                    <span class="category-error" data-error-for="name"></span>
                </div>

                <div class="category-field">
                    <label for="customerEmail">Email</label>
                    <input type="email" id="customerEmail" name="email" placeholder="Enter email">
                    <span class="category-error" data-error-for="email"></span>
                </div>

                <div class="category-field">
                    <label for="customerPhone">Phone</label>
                    <input type="text" id="customerPhone" name="phone" placeholder="Enter phone number">
                    <span class="category-error" data-error-for="phone"></span>
                </div>

                <div class="category-field">
                    <label for="customerPassword">Password</label>
                    <input type="password" id="customerPassword" name="password" placeholder="Enter password">
                    <span class="category-error" data-error-for="password"></span>
                </div>
            </div>

            <div class="category-form-actions">
                <button type="button" class="category-cancel-btn" id="cancelCustomerFormBtn">Cancel</button>
                <button type="submit" class="category-submit-btn" id="submitCustomerFormBtn">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const customersTable = initAdminDataTable('#customersTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search customers...'
        }
    });

    const modal = $('#customerModal');
    const form = $('#customerForm');
    const alertBox = $('#customerAlert');
    const submitBtn = $('#submitCustomerFormBtn');
    const modalTitle = $('#customerModalTitle');
    const recordIdInput = $('#customerRecordId');
    const passwordInput = $('#customerPassword');
    const customerImageInput = $('#customerImage');
    const currentImageWrap = $('#customerCurrentImageWrap');
    const currentImage = $('#customerCurrentImage');
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
        modalTitle.text('Add New Customer');
        submitBtn.text('Save Customer');
        passwordInput.attr('placeholder', 'Enter password');
        customerImageInput.val('');
        currentImage.attr('src', defaultImageUrl);
        currentImageWrap.show();
        recordIdInput.val('');
        editRow = null;
        clearErrors();
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        modalTitle.text('Add New Customer');
        submitBtn.text('Save Customer');
        passwordInput.attr('placeholder', 'Enter password');
        recordIdInput.val('');
        customerImageInput.val('');
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

    function getActionButton(customer) {
        return '<button type="button" class="product-edit-btn customer-edit-btn" ' +
            'data-id="' + customer.id + '" ' +
            'data-name="' + $('<div>').text(customer.name).html() + '" ' +
            'data-email="' + $('<div>').text(customer.email).html() + '" ' +
            'data-phone="' + $('<div>').text(customer.phone).html() + '" ' +
            'data-image-url="' + (customer.image_url || defaultImageUrl) + '">' +
            'Edit</button>';
    }

    $('#openCustomerFormBtn').on('click', openForm);
    $('#closeCustomerFormBtn, #cancelCustomerFormBtn').on('click', closeForm);

    $(document).on('click', '.customer-edit-btn', function () {
        const btn = $(this);
        editRow = customersTable && customersTable.row ? customersTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#customerName').val(btn.data('name'));
        $('#customerEmail').val(btn.data('email'));
        $('#customerPhone').val(btn.data('phone'));
        customerImageInput.val('');
        currentImage.attr('src', btn.data('image-url') || defaultImageUrl);
        currentImageWrap.show();
        passwordInput.val('').attr('placeholder', 'Leave blank to keep current password');

        modalTitle.text('Edit Customer');
        submitBtn.text('Update Customer');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    if (window.AdminImageCropper) {
        window.AdminImageCropper.attach('#customerImage', {
            errorSelector: '[data-error-for="customer_image"]',
            maxSizeBytes: 2 * 1024 * 1024,
            aspectRatio: 1,
            outputType: 'image/png',
            outputFileName: 'customer-cropped.png',
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
                ? "<?= base_url('admin/customers/update') ?>"
                : "<?= base_url('admin/customers/add') ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Customer' : 'Save Customer');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save customer.');
                    }
                    return;
                }

                const customer = response.customer;
                const rowData = [
                    '<span class="categories-id">#' + customer.customer_id + '</span>',
                    getImageCell(customer.image_url, customer.name),
                    customer.name,
                    customer.email,
                    customer.phone,
                    '<span class="categories-status categories-active">Customer</span>',
                    customer.updated,
                    getActionButton(customer)
                ];

                if (recordIdInput.val() && editRow && customersTable && customersTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (customersTable && customersTable.row) {
                    customersTable.row.add(rowData).draw(false);
                } else {
                    $('#customersTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Customer' : 'Save Customer');
                showAlert('error', 'Unexpected error occurred while saving customer.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
