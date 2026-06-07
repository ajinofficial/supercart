<?php
$page = 'banners';
include('header.php');
include('menus.php');

$statusMetaMap = [
    1 => ['text' => 'Active', 'class' => 'categories-active'],
    2 => ['text' => 'Draft', 'class' => 'categories-review'],
    3 => ['text' => 'Inactive', 'class' => 'categories-inactive'],
];
?>

<div class="products-page">
    <div class="products-card shadow-sm">
        <div class="products-card-header">
            <div>
                <h2 class="products-title">Banners Management</h2>
                <p class="products-subtitle">Design and control homepage promotion banners.</p>
            </div>
            <button type="button" class="products-add-btn" id="openBannerFormBtn">
                <i data-lucide="plus"></i>
                <span>Add Banner</span>
            </button>
        </div>

        <div class="products-card-body">
            <div id="bannerAlert" class="product-alert" role="alert"></div>
            <div class="table-responsive">
                <table id="bannersTable" class="table products-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Banner ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($banners)) : ?>
                            <?php foreach ($banners as $banner) : ?>
                                <?php
                                $id = (int) ($banner['id'] ?? 0);
                                $title = trim((string) ($banner['bn_title'] ?? ''));
                                $link = trim((string) ($banner['bn_link'] ?? ''));
                                $statusValue = (int) ($banner['bn_status'] ?? 1);
                                $statusMeta = $statusMetaMap[$statusValue] ?? $statusMetaMap[1];
                                $updatedRaw = $banner['bn_updated_at'] ?? '';
                                $updated = !empty($updatedRaw) ? date('d-m-Y', strtotime((string) $updatedRaw)) : date('d-m-Y');
                                $imageFile = trim((string) ($banner['bn_image'] ?? ''));
                                $imageUrl = $imageFile !== '' ? base_url('uploads/banners/' . $imageFile) : '';
                                ?>
                                <tr data-id="<?= esc((string) $id) ?>">
                                    <td><span class="products-id">#<?= esc('BN' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)) ?></span></td>
                                    <td class="product-image-cell">
                                        <?php if ($imageUrl !== '') : ?>
                                            <img src="<?= esc($imageUrl) ?>" alt="<?= esc($title) ?>" class="product-thumb">
                                        <?php else : ?>
                                            <span class="product-thumb-placeholder">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($title) ?></td>
                                    <td>
                                        <?php if ($link !== '') : ?>
                                            <a href="<?= esc($link) ?>" target="_blank" rel="noopener noreferrer"><?= esc($link) ?></a>
                                        <?php else : ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="categories-status <?= esc($statusMeta['class']) ?>"><?= esc($statusMeta['text']) ?></span></td>
                                    <td><?= esc($updated) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="product-edit-btn banner-edit-btn"
                                            data-id="<?= esc((string) $id) ?>"
                                            data-title="<?= esc($title) ?>"
                                            data-link="<?= esc($link) ?>"
                                            data-status="<?= esc((string) $statusValue) ?>"
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

<div class="product-modal" id="bannerModal" aria-hidden="true">
    <div class="product-modal-card">
        <div class="product-modal-head">
            <h3 id="bannerModalTitle">Add New Banner</h3>
            <button type="button" class="product-modal-close" id="closeBannerFormBtn" aria-label="Close">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="bannerForm" class="product-form" enctype="multipart/form-data">
            <input type="hidden" id="bannerRecordId" name="id" value="">
            <div class="product-form-grid">
                <div class="product-field">
                    <label for="bannerTitle">Banner Title</label>
                    <input type="text" id="bannerTitle" name="banner_title" placeholder="Enter banner title">
                    <span class="product-error" data-error-for="banner_title"></span>
                </div>

                <div class="product-field">
                    <label for="bannerLink">Banner Link</label>
                    <input type="text" id="bannerLink" name="banner_link" placeholder="https://example.com/page">
                    <span class="product-error" data-error-for="banner_link"></span>
                </div>

                <div class="product-field">
                    <label for="bannerImage">Banner Image</label>
                    <input type="file" id="bannerImage" name="banner_image" accept="image/png,image/jpeg,image/jpg,image/webp">
                    <span class="product-error" data-error-for="banner_image"></span>
                    <div class="product-current-image" id="bannerCurrentImageWrap">
                        <span>Current image:</span>
                        <img src="" id="bannerCurrentImage" alt="Current Banner Image">
                    </div>
                </div>

                <div class="product-field">
                    <label for="bannerStatus">Status</label>
                    <select id="bannerStatus" name="status">
                        <option value="">Select status</option>
                        <option value="1">Active</option>
                        <option value="2">Draft</option>
                        <option value="3">Inactive</option>
                    </select>
                    <span class="product-error" data-error-for="status"></span>
                </div>
            </div>

            <div class="product-form-actions">
                <button type="button" class="product-cancel-btn" id="cancelBannerFormBtn">Cancel</button>
                <button type="submit" class="product-submit-btn" id="submitBannerFormBtn">Save Banner</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script>
$(document).ready(function () {
    const bannersTable = initAdminDataTable('#bannersTable', {
        pageLength: 10,
        language: {
            searchPlaceholder: 'Search banners...'
        }
    });

    const modal = $('#bannerModal');
    const form = $('#bannerForm');
    const alertBox = $('#bannerAlert');
    const submitBtn = $('#submitBannerFormBtn');
    const modalTitle = $('#bannerModalTitle');
    const recordIdInput = $('#bannerRecordId');
    const currentImageWrap = $('#bannerCurrentImageWrap');
    const currentImage = $('#bannerCurrentImage');
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

    function showAlert(type, message) {
        alertBox.removeClass('success error').addClass(type).text(message).fadeIn(120);
        setTimeout(function () {
            alertBox.fadeOut(200);
        }, 2800);
    }

    function clearErrors() {
        form.find('.product-error').text('');
    }

    function openForm() {
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
        modalTitle.text('Add New Banner');
        submitBtn.text('Save Banner');
        recordIdInput.val('');
        currentImageWrap.hide();
        editRow = null;
    }

    function closeForm() {
        modal.removeClass('active').attr('aria-hidden', 'true');
        form[0].reset();
        clearErrors();
        modalTitle.text('Add New Banner');
        submitBtn.text('Save Banner');
        recordIdInput.val('');
        currentImageWrap.hide();
        editRow = null;
    }

    function getImageCell(imageUrl, title) {
        if (!imageUrl) {
            return '<span class="product-thumb-placeholder">No Image</span>';
        }

        return '<img src="' + imageUrl + '" alt="' + $('<div>').text(title).html() + '" class="product-thumb">';
    }

    function getActionButton(banner) {
        return '<button type="button" class="product-edit-btn banner-edit-btn" ' +
            'data-id="' + banner.id + '" ' +
            'data-title="' + $('<div>').text(banner.title).html() + '" ' +
            'data-link="' + $('<div>').text(banner.link || '').html() + '" ' +
            'data-status="' + banner.status + '" ' +
            'data-image-url="' + (banner.image_url || '') + '">' +
            'Edit</button>';
    }

    function getLinkCell(link) {
        const safeLink = $('<div>').text(link || '').html();
        if (!safeLink) return '<span>-</span>';
        return '<a href="' + safeLink + '" target="_blank" rel="noopener noreferrer">' + safeLink + '</a>';
    }

    $('#openBannerFormBtn').on('click', openForm);
    $('#closeBannerFormBtn, #cancelBannerFormBtn').on('click', closeForm);

    $(document).on('click', '.banner-edit-btn', function () {
        const btn = $(this);
        editRow = bannersTable && bannersTable.row ? bannersTable.row(btn.closest('tr')) : btn.closest('tr');

        recordIdInput.val(btn.data('id'));
        $('#bannerTitle').val(btn.data('title'));
        $('#bannerLink').val(btn.data('link'));
        $('#bannerStatus').val(String(btn.data('status')));

        const imageUrl = btn.data('image-url');
        if (imageUrl) {
            currentImage.attr('src', imageUrl);
            currentImageWrap.show();
        } else {
            currentImageWrap.hide();
        }

        modalTitle.text('Edit Banner');
        submitBtn.text('Update Banner');
        modal.addClass('active').attr('aria-hidden', 'false');
        clearErrors();
    });

    modal.on('click', function (event) {
        if (event.target === this) closeForm();
    });

    if (window.AdminImageCropper) {
        window.AdminImageCropper.attach('#bannerImage', {
            errorSelector: '[data-error-for="banner_image"]',
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
            maxSizeBytes: 2 * 1024 * 1024,
            aspectRatio: 16 / 7,
            outputType: 'image/jpeg',
            outputQuality: 0.9,
            outputFileName: 'banner-cropped.jpg',
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
                ? "<?= base_url('admin/banners/update') ?>"
                : "<?= base_url('admin/banners/add') ?>",
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Banner' : 'Save Banner');

                if (!response.status) {
                    if (response.errors) {
                        $.each(response.errors, function (field, message) {
                            $('[data-error-for="' + field + '"]').text(message);
                        });
                    } else {
                        showAlert('error', response.message || 'Unable to save banner.');
                    }
                    return;
                }

                const banner = response.banner;
                const statusClass = getStatusClass(banner.status);
                const statusText = banner.status_text || getStatusText(banner.status);
                const rowData = [
                    '<span class="products-id">#' + banner.banner_id + '</span>',
                    getImageCell(banner.image_url, banner.title),
                    banner.title,
                    getLinkCell(banner.link),
                    '<span class="categories-status ' + statusClass + '">' + statusText + '</span>',
                    banner.updated,
                    getActionButton(banner)
                ];

                if (recordIdInput.val() && editRow && bannersTable && bannersTable.row) {
                    editRow.data(rowData).draw(false);
                } else if (bannersTable && bannersTable.row) {
                    bannersTable.row.add(rowData).draw(false);
                } else {
                    $('#bannersTable tbody').append('<tr><td>' + rowData.join('</td><td>') + '</td></tr>');
                }

                closeForm();
                showAlert('success', response.message);
            },
            error: function () {
                submitBtn.prop('disabled', false).text(recordIdInput.val() ? 'Update Banner' : 'Save Banner');
                showAlert('error', 'Unexpected error occurred while saving banner.');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
