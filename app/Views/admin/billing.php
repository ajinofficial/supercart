<?php
$page = 'billing';
include('header.php');
include('menus.php');

$invoices = is_array($invoices ?? null) ? $invoices : [];
$orders = is_array($orders ?? null) ? $orders : [];
$products = is_array($products ?? null) ? $products : [];
$customers = is_array($customers ?? null) ? $customers : [];
?>

<style>
    .billing-page { display: grid; gap: 14px; }
    .billing-hero {
        display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        padding: 20px; border-radius: 17px; color: #fff;
        background: linear-gradient(125deg, #172554, var(--theme-color-dark), #0891b2);
        box-shadow: 0 14px 30px rgba(12, 74, 110, .22);
    }
    .billing-hero h2 { margin: 0; font-size: 1.25rem; font-weight: 800; }
    .billing-hero p { margin: 5px 0 0; color: rgba(255,255,255,.78); font-size: .83rem; }
    .billing-primary-btn {
        min-height: 41px; padding: 8px 14px; border: 1px solid rgba(255,255,255,.6);
        border-radius: 10px; display: inline-flex; align-items: center; gap: 7px;
        color: #15334a; background: #fff; font-size: .8rem; font-weight: 800;
    }
    .billing-primary-btn i { width: 17px; height: 17px; }
    .billing-metrics { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
    .billing-metric {
        padding: 16px; border: 1px solid #e1e9f0; border-radius: 14px; background: #fff;
        box-shadow: 0 6px 18px rgba(24,42,67,.055);
    }
    .billing-metric span { color: #738196; font-size: .73rem; font-weight: 750; }
    .billing-metric strong { display: block; margin-top: 5px; color: #172033; font-size: 1.35rem; }
    .billing-card { border: 1px solid #e1e9f0; border-radius: 15px; background: #fff; overflow: hidden; }
    .billing-card-head { padding: 14px 16px; display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; border-bottom: 1px solid #edf1f5; }
    .billing-card-head h3 { margin: 0; font-size: .95rem; font-weight: 800; }
    .billing-card-head p { margin: 3px 0 0; color: #7b8798; font-size: .74rem; }
    .billing-filter { min-height: 37px; padding: 6px 10px; border: 1px solid #d7e0e8; border-radius: 9px; background: #fff; }
    .billing-table-wrap { padding: 10px 14px 14px; }
    #billingTable thead th { background: #111827; color: #fff; border: 0; padding: .8rem; font-size: .72rem; text-transform: uppercase; white-space: nowrap; }
    #billingTable tbody td { padding: .78rem; border-bottom: 1px solid #edf1f5; font-size: .82rem; vertical-align: middle; }
    #billingTable_wrapper .dataTables_filter input { min-width: 220px; padding: .42rem .7rem; border: 1px solid #d7e0e8; border-radius: 9px; }
    .invoice-code { color: var(--theme-color-dark); font-weight: 850; }
    .billing-status { display: inline-flex; padding: 4px 9px; border-radius: 999px; font-size: .68rem; font-weight: 850; text-transform: capitalize; }
    .billing-status.draft { color: #475569; background: #e9eef3; }
    .billing-status.sent { color: #1d4ed8; background: #e7efff; }
    .billing-status.paid { color: #087f5b; background: #e1f7ed; }
    .billing-status.overdue { color: #b42318; background: #fee9e7; }
    .billing-status.cancelled { color: #7c3aed; background: #f0e9ff; }
    .billing-actions { display: inline-flex; gap: 6px; }
    .billing-icon-btn { width: 32px; height: 32px; display: inline-grid; place-items: center; border: 1px solid #dbe4ec; border-radius: 8px; background: #fff; color: #365268; }
    .billing-icon-btn:hover { color: var(--theme-color); background: #f1f8fd; }
    .billing-icon-btn.danger { color: #b42318; }
    .billing-icon-btn i { width: 15px; height: 15px; }
    .billing-alert { display: none; margin: 12px 14px 0; padding: 10px 12px; border-radius: 9px; font-size: .8rem; font-weight: 750; }
    .billing-alert.success { color: #087443; background: #e8f8ef; }
    .billing-alert.error { color: #a52a32; background: #fdebed; }
    .billing-modal .modal-content { border: 0; border-radius: 16px; overflow: hidden; }
    .billing-modal .modal-header { color: #fff; background: linear-gradient(125deg, #172554, var(--theme-color)); }
    .billing-form-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 12px; }
    .billing-field.full { grid-column: 1 / -1; }
    .billing-field label { display: block; margin-bottom: 5px; color: #3e5568; font-size: .75rem; font-weight: 800; }
    .billing-field .form-control, .billing-field .form-select { min-height: 41px; border-radius: 9px; font-size: .82rem; }
    .billing-items { margin-top: 15px; border: 1px solid #e1e8ef; border-radius: 12px; overflow: hidden; }
    .billing-items-head { padding: 10px 12px; display: flex; align-items: center; justify-content: space-between; background: #f7fafc; }
    .billing-items-head strong { font-size: .8rem; }
    .add-line-btn { border: 0; border-radius: 8px; padding: 7px 10px; color: #fff; background: var(--theme-color); font-size: .74rem; font-weight: 800; }
    .billing-line { display: grid; grid-template-columns: minmax(180px,1fr) 85px 120px 110px 36px; gap: 8px; align-items: center; padding: 9px 11px; border-top: 1px solid #edf1f5; }
    .billing-line input, .billing-line select { min-width: 0; height: 37px; border: 1px solid #d8e1e9; border-radius: 8px; padding: 6px 9px; font-size: .78rem; }
    .billing-line-total { text-align: right; color: #172033; font-size: .8rem; font-weight: 800; }
    .remove-line { width: 32px; height: 32px; border: 0; border-radius: 8px; color: #b42318; background: #feeceb; }
    .billing-totals { width: min(100%, 340px); margin: 15px 0 0 auto; display: grid; gap: 8px; }
    .billing-total-row { display: flex; align-items: center; justify-content: space-between; gap: 15px; color: #526173; font-size: .8rem; }
    .billing-total-row.grand { padding-top: 9px; border-top: 1px solid #dbe3ea; color: #172033; font-size: 1rem; font-weight: 850; }
    .invoice-sheet { color: #172033; }
    .invoice-sheet-head { display: flex; justify-content: space-between; gap: 20px; padding-bottom: 17px; border-bottom: 2px solid #172554; }
    .invoice-sheet h2 { margin: 0; color: #172554; font-weight: 900; }
    .invoice-sheet-meta { text-align: right; font-size: .8rem; }
    .invoice-parties { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 18px 0; }
    .invoice-parties small { color: #7b8798; font-weight: 800; text-transform: uppercase; }
    .invoice-detail-table { width: 100%; }
    .invoice-detail-table th { color: #fff; background: #172554; }
    .invoice-detail-table th, .invoice-detail-table td { padding: 9px; font-size: .8rem; }
    @media (max-width: 900px) { .billing-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); } }
    @media (max-width: 700px) {
        .billing-form-grid { grid-template-columns: 1fr; }
        .billing-field.full { grid-column: auto; }
        .billing-line { grid-template-columns: 1fr 70px 95px; }
        .billing-line-total { text-align: left; }
    }
    @media print {
        body * { visibility: hidden !important; }
        #invoiceDetailModal, #invoiceDetailModal * { visibility: visible !important; }
        #invoiceDetailModal { position: absolute; inset: 0; }
        #invoiceDetailModal .modal-dialog { max-width: none; margin: 0; }
        #invoiceDetailModal .modal-header, #invoiceDetailModal .modal-footer { display: none !important; }
        #invoiceDetailModal .modal-content { box-shadow: none; }
    }
</style>

<div class="billing-page">
    <section class="billing-hero">
        <div>
            <h2>Billing & Invoices</h2>
            <p>Create itemized invoices, import orders, track due dates, and record collection status.</p>
        </div>
        <button class="billing-primary-btn" type="button" id="newInvoiceBtn" data-bs-toggle="modal" data-bs-target="#invoiceFormModal">
            <i data-lucide="plus"></i> New Invoice
        </button>
    </section>

    <section class="billing-metrics">
        <div class="billing-metric"><span>Total billed</span><strong id="billingTotal"><?= esc($currencySymbol) ?> 0.00</strong></div>
        <div class="billing-metric"><span>Collected</span><strong id="billingPaid"><?= esc($currencySymbol) ?> 0.00</strong></div>
        <div class="billing-metric"><span>Outstanding</span><strong id="billingOutstanding"><?= esc($currencySymbol) ?> 0.00</strong></div>
        <div class="billing-metric"><span>Overdue invoices</span><strong id="billingOverdue">0</strong></div>
    </section>

    <section class="billing-card">
        <div class="billing-card-head">
            <div><h3>Invoice Register</h3><p>Review, print, update status, or remove draft invoices.</p></div>
            <select class="billing-filter" id="billingStatusFilter">
                <option value="">All statuses</option>
                <option value="Draft">Draft</option><option value="Sent">Sent</option>
                <option value="Paid">Paid</option><option value="Overdue">Overdue</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <div class="billing-alert" id="billingAlert" role="alert"></div>
        <div class="billing-table-wrap table-responsive">
            <table id="billingTable" class="table w-100">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Order</th><th>Issued</th><th>Due</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>

<div class="modal fade billing-modal" id="invoiceFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title fw-bold">Create Invoice</h5><small class="opacity-75">Totals are recalculated on the server before saving.</small></div><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="invoiceForm">
                    <div class="billing-form-grid">
                        <div class="billing-field">
                            <label for="invoiceOrder">Import order (optional)</label>
                            <select class="form-select" id="invoiceOrder" name="order_code">
                                <option value="">Manual invoice</option>
                                <?php foreach ($orders as $order): ?><option value="<?= esc($order['code']) ?>"><?= esc($order['code'] . ' - ' . $order['customer_name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="billing-field">
                            <label for="invoiceCustomerSelect">Saved customer (optional)</label>
                            <select class="form-select" id="invoiceCustomerSelect">
                                <option value="">Enter customer manually</option>
                                <?php foreach ($customers as $customer): ?><option value="<?= (int) ($customer['id'] ?? 0) ?>"><?= esc((string) ($customer['us_name'] ?? 'Customer')) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="billing-field"><label for="invoiceCustomerName">Customer name</label><input class="form-control" id="invoiceCustomerName" name="customer_name" required></div>
                        <div class="billing-field"><label for="invoiceCustomerEmail">Email</label><input class="form-control" id="invoiceCustomerEmail" name="customer_email" type="email"></div>
                        <div class="billing-field"><label for="invoiceCustomerPhone">Phone</label><input class="form-control" id="invoiceCustomerPhone" name="customer_phone"></div>
                        <div class="billing-field"><label for="invoiceStatus">Status</label><select class="form-select" id="invoiceStatus" name="status"><option value="draft">Draft</option><option value="sent">Sent</option><option value="paid">Paid</option></select></div>
                        <div class="billing-field"><label for="invoiceDate">Invoice date</label><input class="form-control" id="invoiceDate" name="invoice_date" type="date" required></div>
                        <div class="billing-field"><label for="invoiceDueDate">Due date</label><input class="form-control" id="invoiceDueDate" name="due_date" type="date" required></div>
                        <div class="billing-field full"><label for="invoiceAddress">Billing address</label><textarea class="form-control" id="invoiceAddress" name="billing_address" rows="2"></textarea></div>
                    </div>

                    <div class="billing-items">
                        <div class="billing-items-head"><strong>Invoice items</strong><button class="add-line-btn" id="addInvoiceLine" type="button">Add item</button></div>
                        <div id="invoiceLines"></div>
                    </div>

                    <div class="billing-form-grid mt-3">
                        <div class="billing-field"><label for="invoiceDiscount">Discount amount</label><input class="form-control" id="invoiceDiscount" name="discount" type="number" min="0" step=".01" value="0"></div>
                        <div class="billing-field"><label for="invoiceTax">Tax rate (%)</label><input class="form-control" id="invoiceTax" name="tax_rate" type="number" min="0" max="100" step=".01" value="0"></div>
                        <div class="billing-field full"><label for="invoiceNotes">Notes</label><textarea class="form-control" id="invoiceNotes" name="notes" rows="2" placeholder="Payment terms or customer note"></textarea></div>
                    </div>

                    <div class="billing-totals">
                        <div class="billing-total-row"><span>Subtotal</span><strong id="formSubtotal"><?= esc($currencySymbol) ?> 0.00</strong></div>
                        <div class="billing-total-row"><span>Discount</span><strong id="formDiscount"><?= esc($currencySymbol) ?> 0.00</strong></div>
                        <div class="billing-total-row"><span>Tax</span><strong id="formTax"><?= esc($currencySymbol) ?> 0.00</strong></div>
                        <div class="billing-total-row grand"><span>Total</span><strong id="formTotal"><?= esc($currencySymbol) ?> 0.00</strong></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="saveInvoiceBtn" type="button">Create Invoice</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="invoiceDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title fw-bold">Invoice Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="invoiceDetailBody"></div>
        <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" id="printInvoiceBtn"><i data-lucide="printer"></i> Print</button></div>
    </div></div>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    const currency = <?= json_encode($currencySymbol) ?>;
    const orders = <?= json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const products = <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const customers = <?= json_encode($customers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let invoices = <?= json_encode($invoices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const detailModal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));
    const formModalElement = document.getElementById('invoiceFormModal');

    function esc(value) { return $('<div>').text(String(value ?? '')).html(); }
    function money(value) { return currency + ' ' + Number(value || 0).toFixed(2); }
    function formatDate(value) {
        if (!value) return '-';
        return new Intl.DateTimeFormat('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value + 'T00:00:00'));
    }
    function actionButtons(row) {
        const remove = row.status === 'draft' ? '<button class="billing-icon-btn danger js-delete" title="Delete"><i data-lucide="trash-2"></i></button>' : '';
        return '<div class="billing-actions"><button class="billing-icon-btn js-view" title="View"><i data-lucide="eye"></i></button><button class="billing-icon-btn js-status" title="Update status"><i data-lucide="circle-check-big"></i></button>' + remove + '</div>';
    }

    const table = initAdminDataTable('#billingTable', {
        data: invoices, responsive: false, order: [[3, 'desc']], pageLength: 10,
        columns: [
            { data: 'invoice_number', render: (v,t) => t === 'display' ? '<span class="invoice-code">' + esc(v) + '</span>' : v },
            { data: 'customer_name' }, { data: 'order_code', defaultContent: '-' },
            { data: 'invoice_date', render: (v,t) => t === 'display' ? formatDate(v) : v },
            { data: 'due_date', render: (v,t) => t === 'display' ? formatDate(v) : v },
            { data: 'total', render: (v,t) => t === 'display' ? money(v) : Number(v || 0) },
            { data: 'status_text', render: (v,t,row) => t === 'display' ? '<span class="billing-status ' + esc(row.status) + '">' + esc(v) + '</span>' : v },
            { data: null, orderable: false, searchable: false, render: (v,t,row) => t === 'display' ? actionButtons(row) : '' }
        ],
        language: { emptyTable: 'No invoices yet. Create the first invoice.', searchPlaceholder: 'Search invoices...' }
    });

    function refreshTable() {
        table.clear().rows.add(invoices).draw(false);
        updateMetrics();
        lucide.createIcons();
    }
    function updateMetrics() {
        const active = invoices.filter(row => row.status !== 'cancelled');
        const total = active.reduce((sum,row) => sum + Number(row.total || 0), 0);
        const paid = active.filter(row => row.status === 'paid').reduce((sum,row) => sum + Number(row.total || 0), 0);
        $('#billingTotal').text(money(total));
        $('#billingPaid').text(money(paid));
        $('#billingOutstanding').text(money(total - paid));
        $('#billingOverdue').text(active.filter(row => row.status === 'overdue').length);
    }
    function showAlert(type, message) {
        $('#billingAlert').removeClass('success error').addClass(type).text(message).stop(true,true).fadeIn(120);
        setTimeout(() => $('#billingAlert').fadeOut(180), 4000);
    }
    function addressFromCustomer(customer) {
        return [customer.us_address_line1, customer.us_address_line2, customer.us_city, customer.us_state, customer.us_postal_code, customer.us_country].filter(Boolean).join(', ');
    }
    function addLine(item) {
        const value = item || {};
        const options = ['<option value="">Custom item</option>'].concat(products.map(product =>
            '<option value="' + product.id + '"' + (Number(value.id || 0) === Number(product.id) ? ' selected' : '') + '>' + esc(product.name) + '</option>'
        ));
        const line = $('<div class="billing-line"></div>');
        line.html(
            '<div><select class="line-product">' + options.join('') + '</select><input class="line-name mt-1" placeholder="Item description" value="' + esc(value.name || '') + '"></div>' +
            '<input class="line-qty" type="number" min="1" step="1" value="' + Number(value.quantity || value.qty || 1) + '">' +
            '<input class="line-price" type="number" min="0" step=".01" value="' + Number(value.price || 0).toFixed(2) + '">' +
            '<div class="billing-line-total">' + money(0) + '</div>' +
            '<button class="remove-line" type="button" aria-label="Remove item">&times;</button>'
        );
        $('#invoiceLines').append(line);
        calculateForm();
    }
    function collectItems() {
        return $('#invoiceLines .billing-line').map(function () {
            return {
                name: $(this).find('.line-name').val().trim(),
                quantity: Number($(this).find('.line-qty').val() || 0),
                price: Number($(this).find('.line-price').val() || 0)
            };
        }).get().filter(item => item.name && item.quantity > 0);
    }
    function calculateForm() {
        let subtotal = 0;
        $('#invoiceLines .billing-line').each(function () {
            const total = Number($(this).find('.line-qty').val() || 0) * Number($(this).find('.line-price').val() || 0);
            subtotal += total;
            $(this).find('.billing-line-total').text(money(total));
        });
        const discount = Math.min(Math.max(Number($('#invoiceDiscount').val() || 0), 0), subtotal);
        const rate = Math.min(Math.max(Number($('#invoiceTax').val() || 0), 0), 100);
        const tax = (subtotal - discount) * rate / 100;
        $('#formSubtotal').text(money(subtotal));
        $('#formDiscount').text(money(discount));
        $('#formTax').text(money(tax));
        $('#formTotal').text(money(subtotal - discount + tax));
    }
    function resetForm() {
        $('#invoiceForm')[0].reset();
        $('#invoiceLines').empty();
        const today = new Date();
        const due = new Date();
        due.setDate(today.getDate() + 14);
        const iso = date => new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0,10);
        $('#invoiceDate').val(iso(today));
        $('#invoiceDueDate').val(iso(due));
        $('#invoiceDiscount, #invoiceTax').val('0');
        addLine();
    }
    function invoiceSheet(row) {
        const itemRows = row.items.map((item,index) => '<tr><td>' + (index + 1) + '</td><td>' + esc(item.name) + '</td><td>' + Number(item.quantity) + '</td><td>' + money(item.price) + '</td><td>' + money(item.total) + '</td></tr>').join('');
        return '<div class="invoice-sheet"><div class="invoice-sheet-head"><div><h2>INVOICE</h2><strong>' + esc(row.invoice_number) + '</strong></div><div class="invoice-sheet-meta">Issued: ' + formatDate(row.invoice_date) + '<br>Due: ' + formatDate(row.due_date) + '<br>Status: ' + esc(row.status_text) + '</div></div>' +
            '<div class="invoice-parties"><div><small>Bill from</small><strong class="d-block mt-1"><?= esc($themeWebsiteName) ?></strong></div><div><small>Bill to</small><strong class="d-block mt-1">' + esc(row.customer_name) + '</strong><div>' + esc(row.customer_email) + '</div><div>' + esc(row.customer_phone) + '</div><div>' + esc(row.billing_address) + '</div></div></div>' +
            '<div class="table-responsive"><table class="invoice-detail-table"><thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>' + itemRows + '</tbody></table></div>' +
            '<div class="billing-totals"><div class="billing-total-row"><span>Subtotal</span><strong>' + money(row.subtotal) + '</strong></div><div class="billing-total-row"><span>Discount</span><strong>' + money(row.discount) + '</strong></div><div class="billing-total-row"><span>Tax (' + Number(row.tax_rate) + '%)</span><strong>' + money(row.tax_amount) + '</strong></div><div class="billing-total-row grand"><span>Total</span><strong>' + money(row.total) + '</strong></div></div>' +
            (row.notes ? '<div class="mt-4"><small class="text-muted fw-bold">NOTES</small><p>' + esc(row.notes) + '</p></div>' : '') + '</div>';
    }

    $('#billingStatusFilter').on('change', function () { table.column(6).search(this.value ? '^' + this.value + '$' : '', true, false).draw(); });
    $('#newInvoiceBtn').on('click', resetForm);
    $('#addInvoiceLine').on('click', () => addLine());
    $('#invoiceLines').on('input', 'input', calculateForm).on('click', '.remove-line', function () {
        $(this).closest('.billing-line').remove();
        if (!$('#invoiceLines .billing-line').length) addLine();
        calculateForm();
    }).on('change', '.line-product', function () {
        const product = products.find(row => Number(row.id) === Number(this.value));
        if (product) {
            const line = $(this).closest('.billing-line');
            line.find('.line-name').val(product.name);
            line.find('.line-price').val(Number(product.price).toFixed(2));
            calculateForm();
        }
    });
    $('#invoiceDiscount, #invoiceTax').on('input', calculateForm);
    $('#invoiceOrder').on('change', function () {
        const order = orders.find(row => row.code === this.value);
        if (!order) return;
        $('#invoiceCustomerName').val(order.customer_name);
        $('#invoiceCustomerPhone').val(order.customer_phone);
        $('#invoiceAddress').val(order.billing_address);
        $('#invoiceDiscount').val(order.discount || 0);
        $('#invoiceLines').empty();
        (order.items || []).forEach(addLine);
        if (!(order.items || []).length) addLine();
        calculateForm();
    });
    $('#invoiceCustomerSelect').on('change', function () {
        const customer = customers.find(row => Number(row.id) === Number(this.value));
        if (!customer) return;
        $('#invoiceCustomerName').val(customer.us_name || '');
        $('#invoiceCustomerEmail').val(customer.us_email || '');
        $('#invoiceCustomerPhone').val(customer.us_phone || '');
        $('#invoiceAddress').val(addressFromCustomer(customer));
    });
    $('#saveInvoiceBtn').on('click', function () {
        const items = collectItems();
        if (!items.length) { showAlert('error', 'Add at least one valid invoice item.'); return; }
        const button = $(this).prop('disabled', true).text('Creating...');
        const payload = Object.fromEntries(new FormData(document.getElementById('invoiceForm')).entries());
        payload.items = JSON.stringify(items);
        $.post("<?= base_url('admin/billing/save') ?>", payload).done(function (response) {
            if (!response?.status) {
                const message = response?.message || Object.values(response?.errors || {})[0] || 'Unable to create invoice.';
                showAlert('error', message);
                return;
            }
            invoices.unshift(response.invoice);
            refreshTable();
            bootstrap.Modal.getInstance(formModalElement).hide();
            showAlert('success', response.message);
        }).fail(() => showAlert('error', 'Unexpected error creating invoice.')).always(() => button.prop('disabled', false).text('Create Invoice'));
    });
    $('#billingTable tbody').on('click', '.js-view', function () {
        const row = table.row($(this).closest('tr')).data();
        $('#invoiceDetailBody').html(invoiceSheet(row));
        detailModal.show();
    }).on('click', '.js-status', function () {
        const row = table.row($(this).closest('tr')).data();
        const next = prompt('Set status: draft, sent, paid, overdue, cancelled', row.status);
        if (!next || !['draft','sent','paid','overdue','cancelled'].includes(next.toLowerCase())) return;
        $.post("<?= base_url('admin/billing/status') ?>", { id: row.id, status: next.toLowerCase() }).done(function (response) {
            if (!response?.status) { showAlert('error', response?.message || 'Unable to update status.'); return; }
            invoices = invoices.map(item => item.id === response.invoice.id ? response.invoice : item);
            refreshTable(); showAlert('success', response.message);
        });
    }).on('click', '.js-delete', async function () {
        const row = table.row($(this).closest('tr')).data();
        const confirmed = window.AdminConfirm ? await AdminConfirm.open({ title: 'Delete draft invoice?', message: row.invoice_number + ' will be permanently deleted.', confirmText: 'Delete' }) : confirm('Delete this draft invoice?');
        if (!confirmed) return;
        $.post("<?= base_url('admin/billing/delete') ?>", { id: row.id }).done(function (response) {
            if (!response?.status) { showAlert('error', response?.message || 'Unable to delete invoice.'); return; }
            invoices = invoices.filter(item => item.id !== row.id);
            refreshTable(); showAlert('success', response.message);
        });
    });
    $('#printInvoiceBtn').on('click', () => window.print());

    resetForm();
    updateMetrics();
    lucide.createIcons();
});
</script>

<?php include('footer.php'); ?>
