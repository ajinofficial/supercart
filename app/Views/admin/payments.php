<?php
$page = 'payments';
include('header.php');
include('menus.php');

$payments = is_array($payments ?? null) ? $payments : [];
$methods = is_array($methods ?? null) ? $methods : [];
?>

<style>
    .payments-shell {
        display: grid;
        gap: 14px;
    }

    .payments-topbar {
        border: 0;
        border-radius: 14px;
        background: linear-gradient(130deg, #0f172a 0%, #0f766e 100%);
        color: #fff;
        box-shadow: 0 10px 22px rgba(15, 118, 110, 0.25);
    }

    .payments-topbar .card-body {
        padding: 1.1rem 1.2rem;
    }

    .payments-title {
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.2px;
    }

    .payments-subtitle {
        opacity: 0.86;
        font-size: 0.86rem;
        margin-top: 2px;
    }

    .payments-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .payments-summary-card {
        border: 0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(10, 38, 64, 0.08);
    }

    .payments-summary-label {
        margin: 0;
        color: #6b7280;
        font-size: 0.79rem;
        font-weight: 600;
    }

    .payments-summary-value {
        margin: 4px 0 0;
        font-size: 1.6rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .payments-board {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .payments-board .card-header {
        background: #fff;
        border-bottom: 1px solid #ebeff4;
        padding: 0.95rem 1.1rem;
    }

    .payments-table-wrap {
        max-height: 430px;
        overflow: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(75, 94, 114, 0.35) transparent;
    }

    .payments-table-wrap::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .payments-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(75, 94, 114, 0.35);
        border-radius: 999px;
    }

    .payments-table-wrap::-webkit-scrollbar-track {
        background: transparent;
    }

    .payments-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .payments-sync-btn {
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

    .payments-sync-btn:hover {
        background: rgba(255, 255, 255, 0.24);
    }

    .payments-filters .form-select {
        min-width: 170px;
        border-radius: 10px;
        border: 1px solid #d5deea;
        box-shadow: none;
    }

    #paymentsTable_wrapper .dataTables_filter input {
        border: 1px solid #d7dde4;
        border-radius: 10px;
        padding: 0.45rem 0.75rem;
        min-width: 220px;
    }

    #paymentsTable_wrapper .dataTables_length select {
        border: 1px solid #d7dde4;
        border-radius: 8px;
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
    }

    #paymentsTable {
        margin-top: 0.5rem !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    #paymentsTable thead th {
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

    #paymentsTable tbody td {
        border-bottom: 1px solid #edf1f5;
        padding: 0.8rem;
        vertical-align: middle;
    }

    #paymentsTable tbody tr:hover {
        background: #f8fdff;
    }

    .txn-code {
        font-weight: 800;
        color: #0f766e;
    }

    .payment-status {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .status-pending {
        background: #fff4da;
        color: #9c6400;
    }

    .status-paid {
        background: #e8f9ee;
        color: #177644;
    }

    .status-failed {
        background: #ffecec;
        color: #a61d24;
    }

    .status-refunded {
        background: #e8f4ff;
        color: #12519a;
    }

    .payments-btn-update {
        border: 0;
        border-radius: 9px;
        padding: 0.36rem 0.65rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, var(--theme-color), var(--theme-color-dark));
        box-shadow: 0 7px 14px rgba(var(--theme-rgb), 0.24);
    }

    .payments-alert {
        display: none;
        margin-bottom: 10px;
    }

    @media (max-width: 992px) {
        .payments-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .payments-summary-grid {
            grid-template-columns: 1fr;
        }

        .payments-table-wrap {
            max-height: 360px;
        }
    }
</style>

<div class="payments-shell">
    <div class="card payments-topbar">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="payments-title">Payments Control Room</div>
                <div class="payments-subtitle">Orders sync into payments, admins verify collections, and failed/refunded payments update order flow.</div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="payments-sync-btn" id="syncPaymentOrders">
                    <i data-lucide="refresh-cw"></i>
                    <span>Sync Orders</span>
                </button>
                <small id="paymentsDateLabel" class="opacity-75"></small>
            </div>
        </div>
    </div>

    <div class="payments-summary-grid">
        <div class="card payments-summary-card">
            <div class="card-body">
                <p class="payments-summary-label">Total Collected</p>
                <p class="payments-summary-value" id="metricCollected"><?= esc($currencySymbol) ?> 0.00</p>
            </div>
        </div>
        <div class="card payments-summary-card">
            <div class="card-body">
                <p class="payments-summary-label">Pending</p>
                <p class="payments-summary-value" id="metricPending">0</p>
            </div>
        </div>
        <div class="card payments-summary-card">
            <div class="card-body">
                <p class="payments-summary-label">Failed</p>
                <p class="payments-summary-value" id="metricFailed">0</p>
            </div>
        </div>
        <div class="card payments-summary-card">
            <div class="card-body">
                <p class="payments-summary-label">Refunded Value</p>
                <p class="payments-summary-value" id="metricRefunded"><?= esc($currencySymbol) ?> 0.00</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm payments-board">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1 fw-bold">Payments Management</h5>
                <small class="text-muted">Filter by payment status and method. Update transactions in one click.</small>
            </div>
            <div class="payments-filters">
                <select id="paymentStatusFilter" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Failed">Failed</option>
                    <option value="Refunded">Refunded</option>
                </select>
                <select id="paymentMethodFilter" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    <?php foreach ($methods as $method): ?>
                        <option value="<?= esc($method) ?>"><?= esc($method) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-success payments-alert" id="paymentsAlert"></div>
            <div class="table-responsive payments-table-wrap">
                <table id="paymentsTable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th>Gateway Ref</th>
                            <th>Amount</th>
                            <th>Paid On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr class="payments-no-record">
                                <td colspan="9" class="text-center text-muted py-4">No payment records found. Click Sync Orders to create payment records from orders.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr data-id="<?= (int) $payment['id'] ?>" data-transaction="<?= esc($payment['transaction_code']) ?>">
                                    <td><span class="txn-code"><?= esc($payment['transaction_code']) ?></span></td>
                                    <td><?= esc($payment['order_code']) ?></td>
                                    <td><?= esc($payment['customer_name']) ?></td>
                                    <td class="payment-method"><?= esc($payment['method']) ?></td>
                                    <td class="payment-gateway-ref"><?= esc($payment['gateway_ref']) ?></td>
                                    <td class="payment-amount" data-amount="<?= esc($payment['amount']) ?>"><?= esc($payment['amount_display']) ?></td>
                                    <td class="payment-paid-on" data-paid-on="<?= esc($payment['paid_on']) ?>"><?= esc($payment['paid_on_display']) ?></td>
                                    <td class="payment-status-cell" data-status-id="<?= (int) $payment['status'] ?>" data-status="<?= esc($payment['status_text']) ?>">
                                        <span class="payment-status <?= esc($payment['status_class']) ?>"><?= esc($payment['status_text']) ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="payments-btn-update js-payment-edit" data-bs-toggle="offcanvas" data-bs-target="#paymentEditCanvas">Update</button>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="paymentEditCanvas" aria-labelledby="paymentEditCanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="paymentEditCanvasLabel">Update Payment</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="alert alert-info py-2 px-3 small mb-3" id="editPaymentHint">Select a transaction from the table.</div>
        <input type="hidden" id="editPaymentId" value="">

        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select id="editPaymentMethod" class="form-select">
                <option value="UPI">UPI</option>
                <option value="Card">Card</option>
                <option value="Net Banking">Net Banking</option>
                <option value="Wallet">Wallet</option>
                <option value="COD">COD</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Gateway Reference</label>
            <input type="text" id="editGatewayRef" class="form-control" placeholder="Enter gateway reference">
        </div>
        <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" id="editPaymentAmount" class="form-control" min="0" step="0.01" placeholder="0.00">
        </div>
        <div class="mb-3">
            <label class="form-label">Paid On</label>
            <input type="date" id="editPaymentDate" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select id="editPaymentStatus" class="form-select">
                <option value="1">Pending</option>
                <option value="2">Paid</option>
                <option value="3">Failed</option>
                <option value="4">Refunded</option>
            </select>
        </div>

        <button type="button" class="btn btn-primary w-100" id="savePaymentChanges">Save Changes</button>
    </div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function () {
    const statusFilter = $('#paymentStatusFilter');
    const methodFilter = $('#paymentMethodFilter');
    const alertBox = $('#paymentsAlert');
    const saveBtn = $('#savePaymentChanges');
    const syncBtn = $('#syncPaymentOrders');

    const showAlert = (type, message) => {
        alertBox
            .removeClass('alert-success alert-danger')
            .addClass(type === 'error' ? 'alert-danger' : 'alert-success')
            .text(message)
            .stop(true, true)
            .fadeIn(120);
        setTimeout(() => alertBox.fadeOut(250), 2600);
    };

    const table = $('.payments-no-record').length
        ? null
        : initAdminDataTable('#paymentsTable', {
            pageLength: 10,
            language: {
                searchPlaceholder: 'Search transactions...'
            }
        });

    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.nTable.id !== 'paymentsTable') {
            return true;
        }

        const selectedStatus = statusFilter.val();
        const selectedMethod = methodFilter.val();
        const rowStatus = $(data[7]).text().trim();
        const rowMethod = data[3] || '';
        const statusMatch = !selectedStatus || rowStatus === selectedStatus;
        const methodMatch = !selectedMethod || rowMethod === selectedMethod;
        return statusMatch && methodMatch;
    });

    statusFilter.on('change', function () {
        if (table) table.draw();
    });

    methodFilter.on('change', function () {
        if (table) table.draw();
    });

    const updateSummary = () => {
        let pending = 0;
        let failed = 0;
        let collected = 0;
        let refundedValue = 0;

        $('#paymentsTable tbody tr').each(function () {
            const row = $(this);
            if (row.hasClass('payments-no-record')) {
                return;
            }

            const status = String(row.find('.payment-status-cell').data('status') || '');
            const amount = parseFloat(String(row.find('.payment-amount').data('amount') || '0')) || 0;

            if (status === 'Pending') pending++;
            if (status === 'Failed') failed++;
            if (status === 'Paid') collected += amount;
            if (status === 'Refunded') refundedValue += amount;
        });

        $('#metricPending').text(pending);
        $('#metricFailed').text(failed);
        const symbol = window.appData?.currencySymbol || <?= json_encode($currencySymbol) ?>;
        $('#metricCollected').text(symbol + ' ' + collected.toFixed(2));
        $('#metricRefunded').text(symbol + ' ' + refundedValue.toFixed(2));
    };

    const today = new Date();
    const todayLabel = String(today.getDate()).padStart(2, '0') + '-' +
        String(today.getMonth() + 1).padStart(2, '0') + '-' + today.getFullYear();
    $('#paymentsDateLabel').text('Today: ' + todayLabel);

    let activeRow = null;

    $(document).on('click', '.js-payment-edit', function () {
        activeRow = $(this).closest('tr');
        const id = Number(activeRow.data('id') || 0);
        const txn = String(activeRow.data('transaction') || '');
        const method = String(activeRow.find('.payment-method').text() || '').trim();
        const gatewayRef = String(activeRow.find('.payment-gateway-ref').text() || '').trim();
        const amount = String(activeRow.find('.payment-amount').data('amount') || '0');
        const paidOn = String(activeRow.find('.payment-paid-on').data('paid-on') || '');
        const statusId = String(activeRow.find('.payment-status-cell').data('status-id') || '1');

        $('#editPaymentHint').text('Updating transaction ' + txn);
        $('#editPaymentId').val(id);
        $('#editPaymentMethod').val(method || 'UPI');
        $('#editGatewayRef').val(gatewayRef);
        $('#editPaymentAmount').val(amount);
        $('#editPaymentDate').val(paidOn);
        $('#editPaymentStatus').val(statusId);
    });

    $('#savePaymentChanges').on('click', function () {
        if (!activeRow) {
            return;
        }

        const id = Number($('#editPaymentId').val() || 0);
        const method = String($('#editPaymentMethod').val() || '').trim();
        const gatewayRef = String($('#editGatewayRef').val() || '').trim();
        const amount = String($('#editPaymentAmount').val() || '').trim();
        const paidOn = String($('#editPaymentDate').val() || '').trim();
        const status = String($('#editPaymentStatus').val() || '1');

        if (!id || method === '' || amount === '' || paidOn === '') {
            showAlert('error', 'Method, amount, and paid date are required.');
            return;
        }

        saveBtn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: "<?= base_url('admin/payments/update') ?>",
            method: 'POST',
            dataType: 'json',
            data: {
                id: id,
                method: method,
                gateway_ref: gatewayRef,
                amount: amount,
                paid_on: paidOn,
                status: status
            }
        }).done(function (response) {
            if (!response || response.status !== true || !response.payment) {
                let msg = response && response.message ? response.message : 'Unable to update payment.';
                if (response && response.errors) {
                    msg = Object.values(response.errors).join(' ');
                }
                showAlert('error', msg);
                return;
            }

            const item = response.payment;
            const symbol = window.appData?.currencySymbol || <?= json_encode($currencySymbol) ?>;
            activeRow.find('.payment-method').text(item.method);
            activeRow.find('.payment-gateway-ref').text(item.gateway_ref);
            activeRow.find('.payment-amount').attr('data-amount', item.amount).data('amount', item.amount).text(symbol + ' ' + Number(item.amount || 0).toFixed(2));
            activeRow.find('.payment-paid-on').attr('data-paid-on', item.paid_on).data('paid-on', item.paid_on).text(item.paid_on_display);
            activeRow.find('.payment-status-cell')
                .attr('data-status-id', item.status)
                .attr('data-status', item.status_text)
                .data('status-id', item.status)
                .data('status', item.status_text)
                .html('<span class="payment-status ' + item.status_class + '">' + item.status_text + '</span>');

            if (table) {
                table.row(activeRow).invalidate().draw(false);
            }

            updateSummary();
            showAlert('success', response.message || 'Payment updated successfully.');

            const canvasEl = document.getElementById('paymentEditCanvas');
            const canvas = bootstrap.Offcanvas.getInstance(canvasEl);
            if (canvas) {
                canvas.hide();
            }
        }).fail(function () {
            showAlert('error', 'Unexpected error occurred while saving payment.');
        }).always(function () {
            saveBtn.prop('disabled', false).text('Save Changes');
        });
    });

    syncBtn.on('click', function () {
        syncBtn.prop('disabled', true).find('span').text('Syncing...');

        $.ajax({
            url: "<?= base_url('admin/payments/sync-orders') ?>",
            method: 'POST',
            dataType: 'json'
        }).done(function (response) {
            if (!response || response.status !== true) {
                showAlert('error', response?.message || 'Unable to sync orders.');
                return;
            }

            showAlert('success', response.message || 'Payment records synced.');
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
