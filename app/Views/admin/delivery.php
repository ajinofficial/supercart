<?php
$page = 'delivery';
include('header.php');
include('menus.php');

$deliveries = is_array($deliveries ?? null) ? $deliveries : [];
$hubs = is_array($hubs ?? null) ? $hubs : [];
?>

<style>
    .delivery-shell {
        display: grid;
        gap: 14px;
    }

    .delivery-topbar {
        border: 0;
        border-radius: 14px;
        background: linear-gradient(130deg, #0f172a 0%, #1e3a8a 100%);
        color: #fff;
        box-shadow: 0 10px 22px rgba(30, 58, 138, 0.25);
    }

    .delivery-topbar .card-body {
        padding: 1.1rem 1.2rem;
    }

    .delivery-title {
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    .delivery-subtitle {
        opacity: 0.86;
        font-size: 0.86rem;
        margin-top: 2px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .summary-card {
        border: 0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(10, 38, 64, 0.08);
    }

    .summary-label {
        margin: 0;
        color: #6b7280;
        font-size: 0.79rem;
        font-weight: 600;
    }

    .summary-value {
        margin: 4px 0 0;
        font-size: 1.6rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .delivery-board {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .delivery-board .card-header {
        background: #fff;
        border-bottom: 1px solid #ebeff4;
        padding: 0.95rem 1.1rem;
    }

    .delivery-table-wrap {
        max-height: 430px;
        overflow: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(75, 94, 114, 0.35) transparent;
    }

    .delivery-table-wrap::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .delivery-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(75, 94, 114, 0.35);
        border-radius: 999px;
    }

    .delivery-table-wrap::-webkit-scrollbar-track {
        background: transparent;
    }

    .filters-wrap {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .delivery-sync-btn {
        border: 1px solid rgba(255, 255, 255, 0.36);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        padding: 0.52rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .delivery-sync-btn:hover {
        background: rgba(255, 255, 255, 0.24);
    }

    .filters-wrap .form-select {
        min-width: 170px;
        border-radius: 10px;
        border: 1px solid #d5deea;
        box-shadow: none;
    }

    #deliveryTable_wrapper .dataTables_filter input {
        border: 1px solid #d7dde4;
        border-radius: 10px;
        padding: 0.45rem 0.75rem;
        min-width: 220px;
    }

    #deliveryTable_wrapper .dataTables_length select {
        border: 1px solid #d7dde4;
        border-radius: 8px;
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
    }

    #deliveryTable {
        margin-top: 0.5rem !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    #deliveryTable thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #111827;
        color: #fff;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        border: 0;
        padding: 0.82rem 0.8rem;
        white-space: nowrap;
    }

    #deliveryTable tbody td {
        border-bottom: 1px solid #edf1f5;
        padding: 0.8rem;
        vertical-align: middle;
    }

    #deliveryTable tbody tr:hover {
        background: #f7fbff;
    }

    .shipment-id {
        font-weight: 800;
        color: #0b4fcf;
    }

    .priority-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .priority-normal {
        background: #e8f0ff;
        color: #284ea7;
    }

    .priority-high {
        background: #fff4da;
        color: #9b6800;
    }

    .priority-critical {
        background: #ffe5e5;
        color: #b91c1c;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .status-processing {
        background: #e8f4ff;
        color: #12519a;
    }

    .status-out {
        background: #fff4da;
        color: #9c6400;
    }

    .status-delivered {
        background: #e8f9ee;
        color: #177644;
    }

    .status-delayed {
        background: #ffecec;
        color: #a61d24;
    }

    .btn-update {
        border: 0;
        border-radius: 9px;
        padding: 0.36rem 0.65rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, var(--theme-color), var(--theme-color-dark));
        box-shadow: 0 7px 14px rgba(var(--theme-rgb), 0.24);
    }

    .offcanvas-header {
        border-bottom: 1px solid #e6edf4;
    }

    .offcanvas-title {
        font-weight: 700;
    }

    .delivery-alert {
        display: none;
        margin-bottom: 10px;
    }

    @media (max-width: 992px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .delivery-table-wrap {
            max-height: 360px;
        }
    }
</style>

<div class="delivery-shell">
    <div class="card delivery-topbar">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="delivery-title">Delivery Operations Center</div>
                <div class="delivery-subtitle">Orders sync into delivery, riders update shipment flow, and delivered shipments update order status.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="delivery-sync-btn" id="syncDeliveryOrders">
                    <i data-lucide="refresh-cw"></i>
                    <span>Sync Orders</span>
                </button>
                <small id="deliveryDateLabel" class="opacity-75"></small>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="card summary-card">
            <div class="card-body">
                <p class="summary-label">Processing</p>
                <p class="summary-value" id="metricProcessing">0</p>
            </div>
        </div>
        <div class="card summary-card">
            <div class="card-body">
                <p class="summary-label">Out For Delivery</p>
                <p class="summary-value" id="metricOut">0</p>
            </div>
        </div>
        <div class="card summary-card">
            <div class="card-body">
                <p class="summary-label">Delivered</p>
                <p class="summary-value" id="metricDelivered">0</p>
            </div>
        </div>
        <div class="card summary-card">
            <div class="card-body">
                <p class="summary-label">Delayed</p>
                <p class="summary-value" id="metricDelayed">0</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm delivery-board">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1 fw-bold">Delivery Management</h5>
                <small class="text-muted">Use filters and quick updates to manage live shipment flow.</small>
            </div>
            <div class="filters-wrap">
                <select id="deliveryStatusFilter" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="Processing">Processing</option>
                    <option value="Out For Delivery">Out For Delivery</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Delayed">Delayed</option>
                </select>
                <select id="deliveryHubFilter" class="form-select form-select-sm">
                    <option value="">All Hubs</option>
                    <?php foreach ($hubs as $hub): ?>
                        <option value="<?= esc($hub) ?>"><?= esc($hub) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-success delivery-alert" id="deliveryAlert"></div>
            <div class="table-responsive delivery-table-wrap">
                <table id="deliveryTable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Shipment</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Hub</th>
                            <th>Rider</th>
                            <th>ETA</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($deliveries)): ?>
                            <tr class="delivery-no-record">
                                <td colspan="9" class="text-center text-muted py-4">No delivery records found. Click Sync Orders to create delivery records from orders.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($deliveries as $delivery): ?>
                                <tr data-id="<?= (int) $delivery['id'] ?>" data-shipment="<?= esc($delivery['shipment_code']) ?>">
                                    <td><span class="shipment-id"><?= esc($delivery['shipment_code']) ?></span></td>
                                    <td><?= esc($delivery['order_code']) ?></td>
                                    <td><?= esc($delivery['customer_name']) ?></td>
                                    <td class="delivery-hub"><?= esc($delivery['hub']) ?></td>
                                    <td class="delivery-rider"><?= esc($delivery['rider_name']) ?></td>
                                    <td class="delivery-eta" data-eta="<?= esc($delivery['eta_date']) ?>"><?= esc($delivery['eta_display']) ?></td>
                                    <td class="delivery-status" data-status="<?= esc($delivery['status_text']) ?>" data-status-id="<?= (int) $delivery['status'] ?>">
                                        <span class="status-badge <?= esc($delivery['status_class']) ?>"><?= esc($delivery['status_text']) ?></span>
                                    </td>
                                    <td class="delivery-priority" data-priority="<?= esc($delivery['priority_text']) ?>" data-priority-id="<?= (int) $delivery['priority'] ?>">
                                        <span class="priority-chip <?= esc($delivery['priority_class']) ?>"><?= esc($delivery['priority_text']) ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-update js-delivery-edit" data-bs-toggle="offcanvas" data-bs-target="#deliveryEditCanvas">Update</button>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="deliveryEditCanvas" aria-labelledby="deliveryEditCanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="deliveryEditCanvasLabel">Update Delivery</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="alert alert-info py-2 px-3 small mb-3" id="editShipmentHint">Select a shipment from the table.</div>
        <input type="hidden" id="editShipmentId" value="">

        <div class="mb-3">
            <label class="form-label">Rider Name</label>
            <input type="text" id="editRider" class="form-control" placeholder="Enter rider name">
        </div>
        <div class="mb-3">
            <label class="form-label">ETA</label>
            <input type="date" id="editEta" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select id="editStatus" class="form-select">
                <option value="1">Processing</option>
                <option value="2">Out For Delivery</option>
                <option value="3">Delivered</option>
                <option value="4">Delayed</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Priority</label>
            <select id="editPriority" class="form-select">
                <option value="1">Normal</option>
                <option value="2">High</option>
                <option value="3">Critical</option>
            </select>
        </div>

        <button type="button" class="btn btn-primary w-100" id="saveDeliveryChanges">Save Changes</button>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function () {
    const statusFilter = $('#deliveryStatusFilter');
    const hubFilter = $('#deliveryHubFilter');
    const alertBox = $('#deliveryAlert');
    const saveBtn = $('#saveDeliveryChanges');
    const syncBtn = $('#syncDeliveryOrders');

    const showAlert = (type, message) => {
        alertBox
            .removeClass('alert-success alert-danger')
            .addClass(type === 'error' ? 'alert-danger' : 'alert-success')
            .text(message)
            .stop(true, true)
            .fadeIn(120);
        setTimeout(() => alertBox.fadeOut(250), 2600);
    };

    const table = $('.delivery-no-record').length
        ? null
        : initAdminDataTable('#deliveryTable', {
            pageLength: 10,
            language: {
                searchPlaceholder: 'Search shipments...'
            }
        });

    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.nTable.id !== 'deliveryTable') {
            return true;
        }

        const selectedStatus = statusFilter.val();
        const selectedHub = hubFilter.val();
        const rowHub = data[3] || '';
        const rowStatus = $(data[6]).text().trim();
        const statusMatch = !selectedStatus || rowStatus === selectedStatus;
        const hubMatch = !selectedHub || rowHub === selectedHub;
        return statusMatch && hubMatch;
    });

    statusFilter.on('change', function () {
        if (table) table.draw();
    });

    hubFilter.on('change', function () {
        if (table) table.draw();
    });

    const updateSummary = () => {
        let processing = 0;
        let out = 0;
        let delivered = 0;
        let delayed = 0;

        $('#deliveryTable tbody tr').each(function () {
            const row = $(this);
            if (row.hasClass('delivery-no-record')) {
                return;
            }

            const status = String(row.find('.delivery-status').data('status') || '');
            if (status === 'Processing') processing++;
            if (status === 'Out For Delivery') out++;
            if (status === 'Delivered') delivered++;
            if (status === 'Delayed') delayed++;
        });

        $('#metricProcessing').text(processing);
        $('#metricOut').text(out);
        $('#metricDelivered').text(delivered);
        $('#metricDelayed').text(delayed);
    };

    const today = new Date();
    const todayLabel = String(today.getDate()).padStart(2, '0') + '-' +
        String(today.getMonth() + 1).padStart(2, '0') + '-' + today.getFullYear();
    $('#deliveryDateLabel').text('Today: ' + todayLabel);

    let activeRow = null;

    $(document).on('click', '.js-delivery-edit', function () {
        activeRow = $(this).closest('tr');
        const id = Number(activeRow.data('id') || 0);
        const shipment = String(activeRow.data('shipment') || '');
        const rider = String(activeRow.find('.delivery-rider').text()).trim();
        const eta = String(activeRow.find('.delivery-eta').data('eta') || '');
        const statusId = String(activeRow.find('.delivery-status').data('status-id') || '1');
        const priorityId = String(activeRow.find('.delivery-priority').data('priority-id') || '1');

        $('#editShipmentHint').text('Updating shipment ' + shipment);
        $('#editShipmentId').val(id);
        $('#editRider').val(rider);
        $('#editEta').val(eta);
        $('#editStatus').val(statusId);
        $('#editPriority').val(priorityId);
    });

    $('#saveDeliveryChanges').on('click', function () {
        if (!activeRow) {
            return;
        }

        const id = Number($('#editShipmentId').val() || 0);
        const rider = String($('#editRider').val() || '').trim();
        const eta = String($('#editEta').val() || '').trim();
        const status = String($('#editStatus').val() || '1');
        const priority = String($('#editPriority').val() || '1');

        if (!id || rider === '' || eta === '') {
            showAlert('error', 'Rider and ETA are required.');
            return;
        }

        saveBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: "<?= base_url('admin/delivery/update') ?>",
            method: 'POST',
            dataType: 'json',
            data: {
                id: id,
                rider_name: rider,
                eta_date: eta,
                status: status,
                priority: priority
            }
        }).done(function (response) {
            if (!response || response.status !== true || !response.delivery) {
                let msg = response && response.message ? response.message : 'Unable to update delivery.';
                if (response && response.errors) {
                    msg = Object.values(response.errors).join(' ');
                }
                showAlert('error', msg);
                return;
            }

            const item = response.delivery;
            activeRow.find('.delivery-rider').text(item.rider_name);
            activeRow.find('.delivery-eta').attr('data-eta', item.eta_date).data('eta', item.eta_date).text(item.eta_display);
            activeRow.find('.delivery-status')
                .attr('data-status', item.status_text)
                .attr('data-status-id', item.status)
                .data('status', item.status_text)
                .data('status-id', item.status)
                .html('<span class="status-badge ' + item.status_class + '">' + item.status_text + '</span>');
            activeRow.find('.delivery-priority')
                .attr('data-priority', item.priority_text)
                .attr('data-priority-id', item.priority)
                .data('priority', item.priority_text)
                .data('priority-id', item.priority)
                .html('<span class="priority-chip ' + item.priority_class + '">' + item.priority_text + '</span>');

            if (table) {
                table.row(activeRow).invalidate().draw(false);
            }

            updateSummary();
            showAlert('success', response.message || 'Delivery updated successfully.');

            const canvasEl = document.getElementById('deliveryEditCanvas');
            const canvas = bootstrap.Offcanvas.getInstance(canvasEl);
            if (canvas) {
                canvas.hide();
            }
        }).fail(function () {
            showAlert('error', 'Unexpected error occurred while saving delivery.');
        }).always(function () {
            saveBtn.prop('disabled', false).text('Save Changes');
        });
    });

    syncBtn.on('click', function () {
        syncBtn.prop('disabled', true).find('span').text('Syncing...');

        $.ajax({
            url: "<?= base_url('admin/delivery/sync-orders') ?>",
            method: 'POST',
            dataType: 'json'
        }).done(function (response) {
            if (!response || response.status !== true) {
                showAlert('error', response?.message || 'Unable to sync orders.');
                return;
            }

            showAlert('success', response.message || 'Delivery records synced.');
            if (Number(response.created || 0) > 0) {
                setTimeout(function () {
                    window.location.reload();
                }, 700);
            }
        }).fail(function () {
            showAlert('error', 'Unexpected error occurred while syncing orders.');
        }).always(function () {
            syncBtn.prop('disabled', false).find('span').text('Sync Orders');
        });
    });

    updateSummary();
});
</script>

<?php include('footer.php'); ?>
