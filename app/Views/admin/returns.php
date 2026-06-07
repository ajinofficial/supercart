<?php
$page = 'returns';
include('header.php');
include('menus.php');

$returns = is_array($returns ?? null) ? $returns : [];
?>

<style>
    .returns-shell {
        display: grid;
        gap: 14px;
    }

    .returns-topbar {
        border: 0;
        border-radius: 14px;
        background: linear-gradient(130deg, #0f172a 0%, #0e7490 100%);
        color: #fff;
        box-shadow: 0 10px 22px rgba(14, 116, 144, 0.25);
    }

    .returns-topbar .card-body {
        padding: 1.1rem 1.2rem;
    }

    .returns-title {
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    .returns-subtitle {
        opacity: 0.86;
        font-size: 0.86rem;
        margin-top: 2px;
    }

    .returns-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .returns-summary-card {
        border: 0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(10, 38, 64, 0.08);
    }

    .returns-summary-label {
        margin: 0;
        color: #6b7280;
        font-size: 0.79rem;
        font-weight: 600;
    }

    .returns-summary-value {
        margin: 4px 0 0;
        font-size: 1.6rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .returns-board {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .returns-board .card-header {
        background: #fff;
        border-bottom: 1px solid #ebeff4;
        padding: 0.95rem 1.1rem;
    }

    .returns-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .returns-filters .form-select {
        min-width: 170px;
        border-radius: 10px;
        border: 1px solid #d5deea;
        box-shadow: none;
    }

    #returnsTable_wrapper .dataTables_filter input {
        border: 1px solid #d7dde4;
        border-radius: 10px;
        padding: 0.45rem 0.75rem;
        min-width: 220px;
    }

    #returnsTable_wrapper .dataTables_length select {
        border: 1px solid #d7dde4;
        border-radius: 8px;
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
    }

    #returnsTable {
        margin-top: 0.5rem !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    #returnsTable thead th {
        background: #111827;
        color: #fff;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        border: 0;
        padding: 0.82rem 0.8rem;
        white-space: nowrap;
    }

    #returnsTable tbody td {
        border-bottom: 1px solid #edf1f5;
        padding: 0.8rem;
        vertical-align: middle;
    }

    #returnsTable tbody tr:hover {
        background: #f8fdff;
    }

    .return-code {
        font-weight: 800;
        color: #0b7285;
    }

    .returns-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .status-requested {
        background: #e8f4ff;
        color: #12519a;
    }

    .status-picked {
        background: #fff4da;
        color: #9c6400;
    }

    .status-inspection {
        background: #f2eaff;
        color: #6d28d9;
    }

    .status-completed {
        background: #e8f9ee;
        color: #177644;
    }

    .status-rejected {
        background: #ffecec;
        color: #a61d24;
    }

    .refund-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.23rem 0.56rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .refund-pending {
        background: #f0f4f8;
        color: #334155;
    }

    .refund-processing {
        background: #fff4da;
        color: #9b6800;
    }

    .refund-refunded {
        background: #e8f9ee;
        color: #177644;
    }

    .refund-declined {
        background: #ffe5e5;
        color: #b91c1c;
    }

    .returns-btn-update {
        border: 0;
        border-radius: 9px;
        padding: 0.36rem 0.65rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, var(--theme-color), var(--theme-color-dark));
        box-shadow: 0 7px 14px rgba(var(--theme-rgb), 0.24);
    }

    .returns-alert {
        display: none;
        margin-bottom: 10px;
    }

    @media (max-width: 992px) {
        .returns-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .returns-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="returns-shell">
    <div class="card returns-topbar">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="returns-title">Returns Resolution Desk</div>
                <div class="returns-subtitle">Track return requests, coordinate inspections, and manage refund progress.</div>
            </div>
            <small id="returnsDateLabel" class="opacity-75"></small>
        </div>
    </div>

    <div class="returns-summary-grid">
        <div class="card returns-summary-card">
            <div class="card-body">
                <p class="returns-summary-label">Requested</p>
                <p class="returns-summary-value" id="metricRequested">0</p>
            </div>
        </div>
        <div class="card returns-summary-card">
            <div class="card-body">
                <p class="returns-summary-label">In Inspection</p>
                <p class="returns-summary-value" id="metricInspection">0</p>
            </div>
        </div>
        <div class="card returns-summary-card">
            <div class="card-body">
                <p class="returns-summary-label">Completed</p>
                <p class="returns-summary-value" id="metricCompleted">0</p>
            </div>
        </div>
        <div class="card returns-summary-card">
            <div class="card-body">
                <p class="returns-summary-label">Pending Refund</p>
                <p class="returns-summary-value" id="metricPendingRefund">0</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm returns-board">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1 fw-bold">Returns Management</h5>
                <small class="text-muted">Filter by return status and refund stage, then update directly from the queue.</small>
            </div>
            <div class="returns-filters">
                <select id="returnsStatusFilter" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="Requested">Requested</option>
                    <option value="Picked Up">Picked Up</option>
                    <option value="In Inspection">In Inspection</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
                </select>
                <select id="refundStateFilter" class="form-select form-select-sm">
                    <option value="">All Refund States</option>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Refunded">Refunded</option>
                    <option value="Declined">Declined</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-success returns-alert" id="returnsAlert"></div>
            <div class="table-responsive">
                <table id="returnsTable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Return</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Reason</th>
                            <th>Requested On</th>
                            <th>Status</th>
                            <th>Refund</th>
                            <th>Refund State</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($returns)): ?>
                            <tr class="returns-no-record">
                                <td colspan="9" class="text-center text-muted py-4">No return records found. Run migrations to create sample data.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returns as $item): ?>
                                <tr data-id="<?= (int) $item['id'] ?>" data-return-code="<?= esc($item['return_code']) ?>" data-refund-mode="<?= esc($item['refund_mode']) ?>">
                                    <td><span class="return-code"><?= esc($item['return_code']) ?></span></td>
                                    <td><?= esc($item['order_code']) ?></td>
                                    <td><?= esc($item['customer_name']) ?></td>
                                    <td class="return-reason"><?= esc($item['reason']) ?></td>
                                    <td><?= esc($item['requested_on_display']) ?></td>
                                    <td class="return-status" data-status-id="<?= (int) $item['status'] ?>" data-status="<?= esc($item['status_text']) ?>">
                                        <span class="returns-badge <?= esc($item['status_class']) ?>"><?= esc($item['status_text']) ?></span>
                                    </td>
                                    <td class="return-refund-amount"><?= esc($item['refund_display']) ?></td>
                                    <td class="return-refund-state" data-refund-state-id="<?= (int) $item['refund_state'] ?>" data-refund-state="<?= esc($item['refund_state_text']) ?>">
                                        <span class="refund-chip <?= esc($item['refund_state_class']) ?>"><?= esc($item['refund_state_text']) ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="returns-btn-update js-return-edit" data-bs-toggle="offcanvas" data-bs-target="#returnEditCanvas">Update</button>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="returnEditCanvas" aria-labelledby="returnEditCanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="returnEditCanvasLabel">Update Return</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="alert alert-info py-2 px-3 small mb-3" id="editReturnHint">Select a return request from the table.</div>
        <input type="hidden" id="editReturnId" value="">

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select id="editReturnStatus" class="form-select">
                <option value="1">Requested</option>
                <option value="2">Picked Up</option>
                <option value="3">In Inspection</option>
                <option value="4">Completed</option>
                <option value="5">Rejected</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Refund Amount</label>
            <input type="number" id="editRefundAmount" class="form-control" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="mb-3">
            <label class="form-label">Refund Mode</label>
            <select id="editRefundMode" class="form-select">
                <option value="Original Payment">Original Payment</option>
                <option value="UPI">UPI</option>
                <option value="Wallet">Wallet</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Refund State</label>
            <select id="editRefundState" class="form-select">
                <option value="1">Pending</option>
                <option value="2">Processing</option>
                <option value="3">Refunded</option>
                <option value="4">Declined</option>
            </select>
        </div>

        <button type="button" class="btn btn-primary w-100" id="saveReturnChanges">Save Changes</button>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function () {
    const statusFilter = $('#returnsStatusFilter');
    const refundFilter = $('#refundStateFilter');
    const alertBox = $('#returnsAlert');
    const saveBtn = $('#saveReturnChanges');

    const showAlert = (type, message) => {
        alertBox
            .removeClass('alert-success alert-danger')
            .addClass(type === 'error' ? 'alert-danger' : 'alert-success')
            .text(message)
            .stop(true, true)
            .fadeIn(120);
        setTimeout(() => alertBox.fadeOut(250), 2600);
    };

    const table = $('.returns-no-record').length
        ? null
        : initAdminDataTable('#returnsTable', {
            pageLength: 10,
            language: {
                searchPlaceholder: 'Search returns...'
            }
        });

    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.nTable.id !== 'returnsTable') {
            return true;
        }

        const selectedStatus = statusFilter.val();
        const selectedRefund = refundFilter.val();
        const rowStatus = $(data[5]).text().trim();
        const rowRefund = $(data[7]).text().trim();
        const statusMatch = !selectedStatus || rowStatus === selectedStatus;
        const refundMatch = !selectedRefund || rowRefund === selectedRefund;
        return statusMatch && refundMatch;
    });

    statusFilter.on('change', function () {
        if (table) table.draw();
    });

    refundFilter.on('change', function () {
        if (table) table.draw();
    });

    const updateSummary = () => {
        let requested = 0;
        let inspection = 0;
        let completed = 0;
        let pendingRefund = 0;

        $('#returnsTable tbody tr').each(function () {
            const row = $(this);
            if (row.hasClass('returns-no-record')) {
                return;
            }

            const status = String(row.find('.return-status').data('status') || '');
            const refund = String(row.find('.return-refund-state').data('refund-state') || '');

            if (status === 'Requested') requested++;
            if (status === 'In Inspection') inspection++;
            if (status === 'Completed') completed++;
            if (refund === 'Pending') pendingRefund++;
        });

        $('#metricRequested').text(requested);
        $('#metricInspection').text(inspection);
        $('#metricCompleted').text(completed);
        $('#metricPendingRefund').text(pendingRefund);
    };

    const today = new Date();
    const todayLabel = String(today.getDate()).padStart(2, '0') + '-' +
        String(today.getMonth() + 1).padStart(2, '0') + '-' + today.getFullYear();
    $('#returnsDateLabel').text('Today: ' + todayLabel);

    let activeRow = null;

    $(document).on('click', '.js-return-edit', function () {
        activeRow = $(this).closest('tr');
        const id = Number(activeRow.data('id') || 0);
        const returnCode = String(activeRow.data('return-code') || '');
        const statusId = String(activeRow.find('.return-status').data('status-id') || '1');
        const refundStateId = String(activeRow.find('.return-refund-state').data('refund-state-id') || '1');
        const refundAmount = String(activeRow.find('.return-refund-amount').text() || '').replace(/[^\d.]/g, '');
        const refundMode = String(activeRow.data('refund-mode') || '').trim();

        $('#editReturnHint').text('Updating return ' + returnCode);
        $('#editReturnId').val(id);
        $('#editReturnStatus').val(statusId);
        $('#editRefundState').val(refundStateId);
        $('#editRefundAmount').val(refundAmount);
        $('#editRefundMode').val(refundMode || 'Original Payment');
    });

    $('#saveReturnChanges').on('click', function () {
        if (!activeRow) {
            return;
        }

        const id = Number($('#editReturnId').val() || 0);
        const status = String($('#editReturnStatus').val() || '1');
        const refundState = String($('#editRefundState').val() || '1');
        const refundAmount = String($('#editRefundAmount').val() || '').trim();
        const refundMode = String($('#editRefundMode').val() || '').trim();

        if (!id || refundAmount === '' || refundMode === '') {
            showAlert('error', 'Refund amount and mode are required.');
            return;
        }

        saveBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: "<?= base_url('admin/returns/update') ?>",
            method: 'POST',
            dataType: 'json',
            data: {
                id: id,
                status: status,
                refund_amount: refundAmount,
                refund_mode: refundMode,
                refund_state: refundState
            }
        }).done(function (response) {
            if (!response || response.status !== true || !response.item) {
                const msg = response && response.message ? response.message : 'Unable to update return request.';
                showAlert('error', msg);
                return;
            }

            const item = response.item;
            activeRow.find('.return-status')
                .attr('data-status-id', item.status)
                .attr('data-status', item.status_text)
                .html('<span class="returns-badge ' + item.status_class + '">' + item.status_text + '</span>');
            activeRow.find('.return-refund-amount').text(item.refund_display);
            activeRow.find('.return-refund-state')
                .attr('data-refund-state-id', item.refund_state)
                .attr('data-refund-state', item.refund_state_text)
                .html('<span class="refund-chip ' + item.refund_state_class + '">' + item.refund_state_text + '</span>');
            activeRow.attr('data-refund-mode', item.refund_mode);

            if (table) {
                table.row(activeRow).invalidate().draw(false);
            }

            updateSummary();
            showAlert('success', response.message || 'Return request updated successfully.');

            const canvasEl = document.getElementById('returnEditCanvas');
            const canvas = bootstrap.Offcanvas.getInstance(canvasEl);
            if (canvas) {
                canvas.hide();
            }
        }).fail(function () {
            showAlert('error', 'Unexpected error occurred while saving return request.');
        }).always(function () {
            saveBtn.prop('disabled', false).text('Save Changes');
        });
    });

    updateSummary();
});
</script>

<?php include('footer.php'); ?>
