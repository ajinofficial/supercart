<?php
$page = 'orders';
include('header.php');
include('menus.php');
$orders = is_array($orders ?? null) ? $orders : [];
$stats = is_array($stats ?? null) ? $stats : [];
?>

<style>
    .orders-page { display: grid; gap: 18px; }
    .orders-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
    .orders-stat {
        padding: 16px;
        border: 1px solid #e3e8ef;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 7px 20px rgba(31, 47, 70, .05);
    }
    .orders-stat strong { display: block; color: #142033; font-size: 1.45rem; }
    .orders-stat span { color: #748094; font-size: .78rem; font-weight: 700; }
    .orders-header-actions, .orders-filters { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
    .orders-btn {
        min-height: 38px;
        padding: 8px 13px;
        border: 1px solid #dbe2eb;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #26364d;
        background: #fff;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }
    .orders-btn:hover { border-color: var(--theme-color); color: var(--theme-color); }
    .orders-btn.primary { border-color: transparent; color: #fff; background: var(--theme-color); }
    .orders-filter {
        min-height: 39px;
        min-width: 160px;
        padding: 7px 11px;
        border: 1px solid #dbe2eb;
        border-radius: 10px;
        background: #fff;
    }
    .order-code { color: var(--theme-color); font-weight: 800; }
    .order-customer strong, .order-customer small { display: block; }
    .order-customer small { color: #798698; margin-top: 3px; }
    .order-item-summary { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .status-badge, .payment-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 800;
    }
    .status-processing, .payment-pending { background: #fff4db; color: #956400; }
    .status-delivered, .payment-paid { background: #e7f7ed; color: #15713c; }
    .status-cancelled, .payment-failed, .payment-refunded { background: #fdebed; color: #a22933; }
    .payment-not-recorded { background: #edf1f5; color: #607083; }
    .order-status-select { min-width: 125px; border-radius: 8px; }
    .order-actions { display: flex; gap: 7px; }
    .order-icon-btn {
        width: 35px;
        height: 35px;
        border: 1px solid #dce3eb;
        border-radius: 9px;
        display: inline-grid;
        place-items: center;
        color: #34445a;
        background: #fff;
        cursor: pointer;
    }
    .order-icon-btn:hover { color: var(--theme-color); border-color: var(--theme-color); }
    .order-alert { display: none; padding: 10px 13px; border-radius: 10px; font-weight: 700; }
    .order-alert.show { display: block; }
    .order-alert.success { color: #136c3a; background: #e8f7ee; }
    .order-alert.error { color: #992932; background: #fdebed; }
    .order-drawer-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1055;
        display: none;
        justify-content: flex-end;
        background: rgba(15, 26, 41, .48);
    }
    .order-drawer-backdrop.open { display: flex; }
    .order-drawer {
        width: min(610px, 100%);
        height: 100%;
        overflow-y: auto;
        background: #f7f9fc;
        box-shadow: -15px 0 40px rgba(18, 34, 54, .18);
    }
    .order-drawer-head {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #fff;
        background: var(--theme-color);
    }
    .order-drawer-head h3 { margin: 0; font-size: 1.1rem; }
    .order-drawer-close { border: 0; color: #fff; background: transparent; cursor: pointer; }
    .order-drawer-body { padding: 18px; display: grid; gap: 13px; }
    .detail-card { padding: 15px; border: 1px solid #e0e6ee; border-radius: 14px; background: #fff; }
    .detail-card h4 { margin: 0 0 10px; font-size: .92rem; }
    .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .detail-row { min-width: 0; }
    .detail-row span { display: block; color: #7b8798; font-size: .69rem; font-weight: 700; text-transform: uppercase; }
    .detail-row strong { display: block; margin-top: 3px; overflow-wrap: anywhere; font-size: .84rem; }
    .drawer-item { padding: 9px 0; display: flex; justify-content: space-between; gap: 10px; border-bottom: 1px solid #edf0f4; }
    .drawer-item:last-child { border: 0; }
    .drawer-total { font-size: 1rem; font-weight: 800; }
    #ordersTable_wrapper .dataTables_filter input { border: 1px solid #d7dde4; border-radius: 10px; padding: .45rem .75rem; min-width: 220px; }
    @media (max-width: 1100px) { .orders-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 700px) {
        .orders-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-grid { grid-template-columns: 1fr; }
        .orders-filter { width: 100%; }
    }
</style>

<div class="orders-page">
    <div class="orders-stats">
        <div class="orders-stat"><strong data-stat="total"><?= (int) ($stats['total'] ?? 0) ?></strong><span>Total Orders</span></div>
        <div class="orders-stat"><strong data-stat="processing"><?= (int) ($stats['processing'] ?? 0) ?></strong><span>Processing</span></div>
        <div class="orders-stat"><strong data-stat="delivered"><?= (int) ($stats['delivered'] ?? 0) ?></strong><span>Delivered</span></div>
        <div class="orders-stat"><strong data-stat="cancelled"><?= (int) ($stats['cancelled'] ?? 0) ?></strong><span>Cancelled</span></div>
        <div class="orders-stat"><strong><?= esc($currencySymbol) ?> <?= number_format((float) ($stats['revenue'] ?? 0), 2) ?></strong><span>Active Revenue</span></div>
    </div>

    <div class="products-card">
        <div class="products-card-header">
            <div>
                <h5 class="products-title">Orders Management</h5>
                <div class="products-subtitle">Manage orders, customer details, payments, and fulfillment.</div>
            </div>
            <div class="orders-header-actions">
                <a class="orders-btn" href="<?= base_url('admin/payments') ?>"><i data-lucide="credit-card"></i> Payments</a>
                <a class="orders-btn" href="<?= base_url('admin/delivery') ?>"><i data-lucide="truck"></i> Delivery</a>
                <button class="orders-btn primary" id="syncOrdersBtn" type="button"><i data-lucide="refresh-cw"></i> Sync Records</button>
            </div>
        </div>

        <div class="products-card-body">
            <div class="order-alert" id="orderAlert"></div>
            <div class="orders-filters mb-3">
                <select class="orders-filter" id="statusFilter">
                    <option value="">All statuses</option>
                    <option value="processing">Processing</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select class="orders-filter" id="paymentFilter">
                    <option value="">All payments</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                    <option value="not recorded">Not recorded</option>
                </select>
            </div>

            <div class="table-responsive">
                <table id="ordersTable" class="table table-hover align-middle w-100 products-table">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Payment</th>
                        <th>Fulfillment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $itemLabels = [];
                        foreach ($order['items'] as $item) {
                            $itemLabels[] = max(1, (int) ($item['qty'] ?? 1)) . ' x ' . trim((string) ($item['name'] ?? 'Item'));
                        }
                        $paymentStatus = strtolower((string) ($order['payment']['status'] ?? 'Not recorded'));
                        ?>
                        <tr data-order-row="<?= (int) $order['id'] ?>"
                            data-status="<?= esc($order['status']) ?>"
                            data-payment="<?= esc($paymentStatus) ?>">
                            <td><span class="order-code"><?= esc($order['code']) ?></span></td>
                            <td class="order-customer">
                                <strong><?= esc($order['customer_name']) ?></strong>
                                <small><?= esc($order['customer_phone'] ?: 'No phone') ?></small>
                            </td>
                            <td>
                                <div class="order-item-summary" title="<?= esc(implode(', ', $itemLabels)) ?>">
                                    <?= esc(implode(', ', $itemLabels) ?: 'No items') ?>
                                </div>
                                <small class="text-muted"><?= (int) $order['item_count'] ?> item(s)</small>
                            </td>
                            <td>
                                <strong><?= esc($order['payment_method']) ?></strong><br>
                                <span class="payment-badge payment-<?= esc(str_replace(' ', '-', $paymentStatus)) ?>"><?= esc($order['payment']['status']) ?></span>
                            </td>
                            <td>
                                <strong><?= esc($order['delivery']['status']) ?></strong><br>
                                <small class="text-muted"><?= esc($order['delivery']['shipment_code'] ?: 'No shipment') ?></small>
                            </td>
                            <td>
                                <select class="form-select form-select-sm order-status-select"
                                        data-id="<?= (int) $order['id'] ?>"
                                        data-previous="<?= esc($order['status']) ?>">
                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <span class="status-badge status-<?= esc($order['status']) ?> mt-2" data-status-badge="<?= (int) $order['id'] ?>">
                                    <?= esc(ucfirst($order['status'])) ?>
                                </span>
                            </td>
                            <td><?= esc($order['created_at'] !== '' ? date('d M Y', strtotime($order['created_at'])) : '-') ?></td>
                            <td><span class="products-price"><?= esc($currencySymbol) ?> <?= number_format((float) $order['total'], 2) ?></span></td>
                            <td>
                                <div class="order-actions">
                                    <button class="order-icon-btn view-order-btn" type="button" data-id="<?= (int) $order['id'] ?>" title="View order">
                                        <i data-lucide="eye"></i>
                                    </button>
                                    <?php if ((int) $order['user_id'] > 0): ?>
                                        <a class="order-icon-btn" href="<?= base_url('admin/notifications') ?>" title="Message customer"><i data-lucide="message-circle"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="order-drawer-backdrop" id="orderDrawerBackdrop">
    <aside class="order-drawer" role="dialog" aria-modal="true" aria-labelledby="drawerOrderTitle">
        <div class="order-drawer-head">
            <h3 id="drawerOrderTitle">Order Details</h3>
            <button class="order-drawer-close" id="closeOrderDrawer" type="button" aria-label="Close"><i data-lucide="x"></i></button>
        </div>
        <div class="order-drawer-body" id="orderDrawerBody"></div>
    </aside>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js') ?>"></script>
<script>
$(document).ready(function () {
    const orders = <?= json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const currencySymbol = <?= json_encode($currencySymbol) ?>;
    const table = initAdminDataTable('#ordersTable', {
        pageLength: 10,
        order: [[6, 'desc']],
        language: { searchPlaceholder: 'Search order, customer, item...' }
    });

    function escapeHtml(value) {
        return $('<div>').text(String(value ?? '')).html();
    }
    function money(value) {
        return currencySymbol + ' ' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function statusClass(status) {
        return status === 'delivered' ? 'status-delivered' : (status === 'cancelled' ? 'status-cancelled' : 'status-processing');
    }
    function showAlert(message, type) {
        $('#orderAlert').removeClass('success error').addClass('show ' + type).text(message);
        window.setTimeout(function () { $('#orderAlert').removeClass('show'); }, 3500);
    }
    function updateStats(previous, current) {
        if (previous === current) return;
        const previousNode = $('[data-stat="' + previous + '"]');
        const currentNode = $('[data-stat="' + current + '"]');
        previousNode.text(Math.max(0, Number(previousNode.text()) - 1));
        currentNode.text(Number(currentNode.text()) + 1);
    }
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'ordersTable') return true;
        const row = $(table.row(dataIndex).node());
        const status = String($('#statusFilter').val() || '');
        const payment = String($('#paymentFilter').val() || '');
        return (!status || String(row.attr('data-status')) === status) &&
            (!payment || String(row.attr('data-payment')) === payment);
    });
    function applyFilters() { table.draw(); }
    $('#statusFilter, #paymentFilter').on('change', applyFilters);

    function performStatusUpdate(select, status) {
        const id = Number(select.data('id'));
        const previous = String(select.data('previous') || 'processing');
        select.prop('disabled', true);
        $.post("<?= base_url('admin/orders/update') ?>", { id: id, status: status })
            .done(function (response) {
                if (!response || !response.status) {
                    select.val(previous);
                    showAlert(response?.message || 'Unable to update order.', 'error');
                    return;
                }
                const storedStatus = String(response.status_value || status);
                select.val(storedStatus).data('previous', storedStatus);
                const row = $('[data-order-row="' + id + '"]');
                row.attr('data-status', storedStatus);
                row.find('[data-status-badge="' + id + '"]')
                    .removeClass('status-processing status-delivered status-cancelled')
                    .addClass(statusClass(storedStatus))
                    .text(response.status_text || storedStatus);
                const order = orders.find(function (item) { return Number(item.id) === id; });
                if (order) order.status = storedStatus;
                updateStats(previous, storedStatus);
                showAlert(response.message || 'Order updated.', 'success');
            })
            .fail(function () {
                select.val(previous);
                showAlert('Unable to update order.', 'error');
            })
            .always(function () { select.prop('disabled', false); });
    }

    $(document).on('change', '.order-status-select', function () {
        const select = $(this);
        const status = String(select.val() || '');
        const previous = String(select.data('previous') || 'processing');
        if (status === previous) return;
        const message = status === 'cancelled'
            ? 'Cancel this order? The customer will be notified.'
            : 'Change this order to ' + status + '? The customer will be notified.';

        if (window.AdminConfirm) {
            window.AdminConfirm.open({
                title: 'Update Order',
                message: message,
                confirmText: 'Update',
                cancelText: 'Keep ' + previous
            }).then(function (confirmed) {
                if (confirmed) {
                    performStatusUpdate(select, status);
                } else {
                    select.val(previous);
                }
            });
        } else if (window.confirm(message)) {
            performStatusUpdate(select, status);
        } else {
            select.val(previous);
        }
    });

    function detailRow(label, value) {
        return '<div class="detail-row"><span>' + escapeHtml(label) + '</span><strong>' + escapeHtml(value || '-') + '</strong></div>';
    }
    function openOrder(order) {
        const items = (order.items || []).map(function (item) {
            const quantity = Math.max(1, Number(item.qty || 1));
            return '<div class="drawer-item"><span>' + quantity + ' x ' + escapeHtml(item.name || 'Item') +
                '</span><strong>' + money(Number(item.price || 0) * quantity) + '</strong></div>';
        }).join('');
        $('#drawerOrderTitle').text('Order ' + order.code);
        $('#orderDrawerBody').html(
            '<section class="detail-card"><h4>Customer</h4><div class="detail-grid">' +
                detailRow('Name', order.customer_name) + detailRow('Phone', order.customer_phone) +
                detailRow('Address', order.customer_address) + detailRow('Note', order.customer_note || 'No note') +
            '</div></section>' +
            '<section class="detail-card"><h4>Items</h4>' + (items || '<p>No items recorded.</p>') +
                '<div class="drawer-item"><span>Subtotal</span><strong>' + money(order.subtotal) + '</strong></div>' +
                '<div class="drawer-item"><span>Discount</span><strong>-' + money(order.discount) + '</strong></div>' +
                '<div class="drawer-item drawer-total"><span>Total</span><strong>' + money(order.total) + '</strong></div>' +
            '</section>' +
            '<section class="detail-card"><h4>Payment</h4><div class="detail-grid">' +
                detailRow('Method', order.payment.method || order.payment_method) +
                detailRow('Status', order.payment.status) +
                detailRow('Transaction', order.payment.transaction_code) +
                detailRow('Gateway reference', order.payment.gateway_ref) +
                detailRow('Paid on', order.payment.paid_on) +
                detailRow('Coupon', order.coupon_code || 'None') +
            '</div></section>' +
            '<section class="detail-card"><h4>Fulfillment</h4><div class="detail-grid">' +
                detailRow('Status', order.delivery.status) +
                detailRow('Shipment', order.delivery.shipment_code) +
                detailRow('Hub', order.delivery.hub) +
                detailRow('Rider', order.delivery.rider_name) +
                detailRow('ETA', order.delivery.eta_date) +
                detailRow('Last updated', order.updated_at) +
            '</div></section>'
        );
        $('#orderDrawerBackdrop').addClass('open');
    }
    $(document).on('click', '.view-order-btn', function () {
        const id = Number($(this).data('id'));
        const order = orders.find(function (item) { return Number(item.id) === id; });
        if (order) openOrder(order);
    });
    $('#closeOrderDrawer').on('click', function () { $('#orderDrawerBackdrop').removeClass('open'); });
    $('#orderDrawerBackdrop').on('click', function (event) {
        if (event.target === this) $(this).removeClass('open');
    });
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') $('#orderDrawerBackdrop').removeClass('open');
    });

    $('#syncOrdersBtn').on('click', function () {
        const button = $(this).prop('disabled', true);
        Promise.all([
            fetch("<?= base_url('admin/payments/sync-orders') ?>", { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()),
            fetch("<?= base_url('admin/delivery/sync-orders') ?>", { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json())
        ])
            .then(function (results) {
                if (results.some(function (result) { return !result.status; })) throw new Error('Unable to sync all records.');
                showAlert('Payment and delivery records synchronized.', 'success');
                window.setTimeout(function () { window.location.reload(); }, 700);
            })
            .catch(function (error) { showAlert(error.message, 'error'); })
            .finally(function () { button.prop('disabled', false); });
    });
});
</script>

<?php include('footer.php'); ?>
