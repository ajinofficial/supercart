<?php
$page = 'reports';
include('header.php');
include('menus.php');
$filters = is_array($filters ?? null) ? $filters : ['from' => date('Y-m-d', strtotime('-89 days')), 'to' => date('Y-m-d'), 'source' => 'all'];
$summary = is_array($summary ?? null) ? $summary : [];
$statusChart = is_array($status_chart ?? null) ? $status_chart : ['labels' => [], 'values' => []];
$sourceChart = is_array($source_chart ?? null) ? $source_chart : ['labels' => [], 'values' => []];
$trendChart = is_array($trend_chart ?? null) ? $trend_chart : ['labels' => [], 'values' => []];
$rows = is_array($rows ?? null) ? $rows : [];
?>

<style>
    .reports-page { display: grid; gap: 16px; }
    .reports-head {
        padding: 22px;
        border-radius: 18px;
        color: #fff;
        background: linear-gradient(125deg, var(--theme-color-darker), var(--theme-color), #2c9fb7);
        box-shadow: 0 15px 34px rgba(21, 52, 76, .2);
    }
    .reports-head-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .reports-head h2 { margin: 0; font-size: 1.35rem; font-weight: 800; }
    .reports-head p { margin: 5px 0 0; opacity: .83; }
    .reports-actions, .reports-filters, .date-presets { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
    .reports-filters { margin-top: 18px; }
    .report-field { display: grid; gap: 5px; }
    .report-field label {
        color: rgba(255,255,255,.82);
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .report-input {
        min-height: 40px;
        min-width: 150px;
        padding: 8px 11px;
        border: 1px solid rgba(255,255,255,.4);
        border-radius: 10px;
        color: #fff;
        background: rgba(255,255,255,.13);
    }
    .report-input option { color: #172033; }
    .report-input:focus { outline: 0; box-shadow: 0 0 0 3px rgba(255,255,255,.18); }
    .report-btn {
        min-height: 40px;
        padding: 8px 13px;
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #fff;
        background: rgba(255,255,255,.12);
        font-weight: 750;
        cursor: pointer;
    }
    .report-btn.primary { border-color: #fff; color: #15334a; background: #fff; }
    .report-btn:disabled { opacity: .6; cursor: wait; }
    .report-btn .spin { animation: report-spin .8s linear infinite; }
    @keyframes report-spin { to { transform: rotate(360deg); } }
    .preset-btn {
        padding: 5px 10px;
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 999px;
        color: #fff;
        background: transparent;
        font-size: .72rem;
        font-weight: 700;
        cursor: pointer;
    }
    .preset-btn.active { color: #17384e; background: #fff; }
    .reports-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; }
    .report-kpi {
        padding: 16px;
        border: 1px solid #e3e9f0;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 7px 20px rgba(24, 42, 67, .055);
    }
    .report-kpi .icon {
        width: 34px; height: 34px; margin-bottom: 10px; border-radius: 10px;
        display: grid; place-items: center; color: var(--theme-color); background: rgba(var(--theme-rgb), .1);
    }
    .report-kpi strong { display: block; color: #162237; font-size: 1.35rem; }
    .report-kpi span { color: #758195; font-size: .74rem; font-weight: 700; }
    .reports-charts { display: grid; grid-template-columns: 1.4fr .8fr .8fr; gap: 12px; }
    .report-panel { border: 1px solid #e3e9f0; border-radius: 15px; background: #fff; box-shadow: 0 7px 20px rgba(24,42,67,.055); }
    .report-panel-head { padding: 14px 16px; display: flex; justify-content: space-between; gap: 10px; border-bottom: 1px solid #edf1f5; }
    .report-panel-head h3 { margin: 0; font-size: .9rem; font-weight: 800; }
    .report-panel-head small { color: #7b8798; }
    .chart-box { position: relative; height: 280px; padding: 14px; }
    .chart-empty {
        position: absolute;
        inset: 14px;
        display: none;
        place-items: center;
        border: 1px dashed #d9e1e9;
        border-radius: 12px;
        color: #7b8798;
        background: #fafcfe;
        font-size: .82rem;
        font-weight: 700;
        text-align: center;
    }
    .chart-box.is-empty canvas { visibility: hidden; }
    .chart-box.is-empty .chart-empty { display: grid; }
    .reports-table-card { border: 1px solid #e3e9f0; border-radius: 15px; background: #fff; overflow: hidden; }
    .reports-table-head { padding: 15px 17px 10px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .reports-table-head h3 { margin: 0; font-size: .95rem; }
    .reports-table-head p { margin: 3px 0 0; color: #7b8798; font-size: .76rem; }
    .table-filters { display: flex; gap: 8px; flex-wrap: wrap; }
    .table-filter { min-height: 37px; padding: 7px 10px; border: 1px solid #dce3eb; border-radius: 9px; background: #fff; }
    .source-chip, .status-chip { display: inline-flex; padding: 5px 9px; border-radius: 999px; font-size: .69rem; font-weight: 800; }
    .source-orders { color: #1d4ed8; background: #e8f0ff; }
    .source-payments { color: #087f5b; background: #e5f8ef; }
    .source-delivery { color: #8a4b0f; background: #fff2dc; }
    .source-returns { color: #7c3aed; background: #f0e9ff; }
    .status-chip { color: #35445a; background: #edf1f5; }
    .report-alert { display: none; margin: 0 16px 10px; padding: 10px 12px; border-radius: 9px; color: #9b2c35; background: #fdebed; font-weight: 700; }
    #reportsTable_wrapper { padding: 0 15px 15px; }
    #reportsTable_wrapper .dataTables_filter input { border: 1px solid #d7dde4; border-radius: 10px; padding: .45rem .75rem; min-width: 220px; }
    @media (max-width: 1250px) {
        .reports-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .reports-charts { grid-template-columns: 1fr 1fr; }
        .reports-charts .trend-panel { grid-column: 1 / -1; }
    }
    @media (max-width: 720px) {
        .reports-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .reports-charts { grid-template-columns: 1fr; }
        .reports-charts .trend-panel { grid-column: auto; }
        .report-field, .report-input { width: 100%; }
        .reports-actions { width: 100%; }
        .reports-actions .report-btn { flex: 1; justify-content: center; }
    }
</style>

<div class="reports-page">
    <section class="reports-head">
        <div class="reports-head-top">
            <div>
                <h2>Business Reports</h2>
                <p>Analyze orders, payments, delivery performance, and returns from one report.</p>
            </div>
            <div class="reports-actions">
                <button class="report-btn" id="resetReportBtn" type="button"><i data-lucide="rotate-ccw"></i> Reset</button>
                <button class="report-btn" id="exportCsvBtn" type="button"><i data-lucide="download"></i> Export CSV</button>
                <button class="report-btn primary" id="runReportBtn" type="button"><i data-lucide="play"></i> Run Report</button>
            </div>
        </div>
        <form class="reports-filters" id="reportFilters">
            <div class="report-field">
                <label for="reportFrom">From date</label>
                <input class="report-input" id="reportFrom" name="from" type="date" value="<?= esc($filters['from']) ?>" required>
            </div>
            <div class="report-field">
                <label for="reportTo">To date</label>
                <input class="report-input" id="reportTo" name="to" type="date" value="<?= esc($filters['to']) ?>" required>
            </div>
            <div class="report-field">
                <label for="reportSource">Data source</label>
                <select class="report-input" id="reportSource" name="source">
                    <option value="all" <?= $filters['source'] === 'all' ? 'selected' : '' ?>>All Sources</option>
                    <option value="orders" <?= $filters['source'] === 'orders' ? 'selected' : '' ?>>Orders</option>
                    <option value="payments" <?= $filters['source'] === 'payments' ? 'selected' : '' ?>>Payments</option>
                    <option value="delivery" <?= $filters['source'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
                    <option value="returns" <?= $filters['source'] === 'returns' ? 'selected' : '' ?>>Returns</option>
                </select>
            </div>
            <div class="date-presets">
                <button class="preset-btn" type="button" data-days="7">7 days</button>
                <button class="preset-btn" type="button" data-days="30">30 days</button>
                <button class="preset-btn active" type="button" data-days="90">90 days</button>
                <button class="preset-btn" type="button" data-days="365">1 year</button>
            </div>
        </form>
    </section>

    <section class="reports-kpis">
        <div class="report-kpi"><div class="icon"><i data-lucide="layers"></i></div><strong id="kpiTotal"><?= (int) ($summary['total'] ?? 0) ?></strong><span>Total events</span></div>
        <div class="report-kpi"><div class="icon"><i data-lucide="shopping-bag"></i></div><strong id="kpiOrders"><?= (int) ($summary['orders'] ?? 0) ?></strong><span>Orders</span></div>
        <div class="report-kpi"><div class="icon"><i data-lucide="badge-check"></i></div><strong id="kpiPaid"><?= (int) ($summary['paid'] ?? 0) ?></strong><span>Paid transactions</span></div>
        <div class="report-kpi"><div class="icon"><i data-lucide="circle-check"></i></div><strong id="kpiCompleted"><?= (int) ($summary['completed'] ?? 0) ?></strong><span>Completed events</span></div>
        <div class="report-kpi"><div class="icon"><i data-lucide="indian-rupee"></i></div><strong id="kpiRevenue"><?= esc($currencySymbol) ?> <?= esc((string) ($summary['revenue'] ?? '0.00')) ?></strong><span>Recorded revenue</span></div>
        <div class="report-kpi"><div class="icon"><i data-lucide="undo-2"></i></div><strong id="kpiRefund"><?= esc($currencySymbol) ?> <?= esc((string) ($summary['refund_total'] ?? '0.00')) ?></strong><span>Refunded value</span></div>
    </section>

    <section class="reports-charts">
        <article class="report-panel trend-panel">
            <div class="report-panel-head"><h3>Activity Trend</h3><small id="trendGranularity"><?= esc(ucfirst((string) ($trendChart['granularity'] ?? 'monthly'))) ?></small></div>
            <div class="chart-box" id="trendChartBox"><canvas id="trendChartCanvas"></canvas><div class="chart-empty">No activity in this date range.</div></div>
        </article>
        <article class="report-panel">
            <div class="report-panel-head"><h3>Source Mix</h3><small>All activity</small></div>
            <div class="chart-box" id="sourceChartBox"><canvas id="sourceChartCanvas"></canvas><div class="chart-empty">No source data available.</div></div>
        </article>
        <article class="report-panel">
            <div class="report-panel-head"><h3>Status Mix</h3><small id="completionRateLabel"><?= esc((string) ($summary['completion_rate'] ?? 0)) ?>% complete</small></div>
            <div class="chart-box" id="statusChartBox"><canvas id="statusChartCanvas"></canvas><div class="chart-empty">No status data available.</div></div>
        </article>
    </section>

    <section class="reports-table-card">
        <div class="reports-table-head">
            <div>
                <h3>Report Details</h3>
                <p id="reportResultSummary" aria-live="polite"><?= count($rows) ?> records for the selected period</p>
            </div>
            <div class="table-filters">
                <select class="table-filter" id="tableSourceFilter"><option value="">All sources</option></select>
                <select class="table-filter" id="tableStatusFilter"><option value="">All statuses</option></select>
            </div>
        </div>
        <div class="report-alert" id="reportsAlert" role="alert" aria-live="assertive"></div>
        <div class="table-responsive">
            <table id="reportsTable" class="table align-middle w-100">
                <thead><tr><th>Source</th><th>Reference</th><th>Order</th><th>Customer</th><th>Status</th><th>Amount</th><th>Channel</th><th>Date</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
</div>

<script src="<?= base_url('materials/admin/js/dataTable.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
$(function () {
    const currencySymbol = <?= json_encode($currencySymbol) ?>;
    let reportRows = <?= json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let trendChartData = <?= json_encode($trendChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let sourceChartData = <?= json_encode($sourceChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let statusChartData = <?= json_encode($statusChart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let trendChart;
    let sourceChart;
    let statusChart;
    let activeRequest = null;

    const table = initAdminDataTable('#reportsTable', {
        data: reportRows,
        pageLength: 10,
        order: [[7, 'desc']],
        responsive: false,
        columns: [
            {
                data: 'source',
                render: function (value, type) {
                    if (type !== 'display') return value || '';
                    return '<span class="source-chip ' + sourceClass(value) + '">' + escapeHtml(value) + '</span>';
                }
            },
            { data: 'reference', defaultContent: '-' },
            { data: 'order_code', defaultContent: '-' },
            { data: 'customer', defaultContent: '-' },
            {
                data: 'status',
                render: function (value, type) {
                    if (type !== 'display') return value || '';
                    return '<span class="status-chip">' + escapeHtml(value) + '</span>';
                }
            },
            {
                data: 'amount',
                defaultContent: '0.00',
                render: function (value, type, row) {
                    return type === 'sort' || type === 'type'
                        ? Number(row.amount_value || 0)
                        : escapeHtml(value);
                }
            },
            { data: 'channel', defaultContent: '-' },
            {
                data: 'event_date',
                defaultContent: '',
                render: function (value, type, row) {
                    return type === 'display' ? escapeHtml(row.event_date_display || '-') : (value || '');
                }
            }
        ],
        language: {
            searchPlaceholder: 'Search report records...',
            emptyTable: 'No records match this report.',
            zeroRecords: 'No records match the table filters.'
        }
    });

    function escapeHtml(value) { return $('<div>').text(String(value ?? '')).html(); }
    function sourceClass(value) { return 'source-' + String(value || '').toLowerCase(); }
    function renderTable(rows) {
        reportRows = Array.isArray(rows) ? rows : [];
        table.clear().rows.add(reportRows).draw();
        populateTableFilters();
    }
    function populateSelect(selector, values, placeholder) {
        const selected = $(selector).val();
        const options = ['<option value="">' + placeholder + '</option>'].concat(values.map(function (value) {
            return '<option value="' + escapeHtml(value) + '">' + escapeHtml(value) + '</option>';
        }));
        $(selector).html(options.join(''));
        if (values.includes(selected)) {
            $(selector).val(selected);
        }
    }
    function populateTableFilters() {
        populateSelect('#tableSourceFilter', [...new Set(reportRows.map(row => row.source))].sort(), 'All sources');
        populateSelect('#tableStatusFilter', [...new Set(reportRows.map(row => row.status))].sort(), 'All statuses');
    }
    $('#tableSourceFilter').on('change', function () {
        table.column(0).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
    });
    $('#tableStatusFilter').on('change', function () {
        table.column(4).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
    });
    table.on('draw', function () {
        const visible = table.rows({ search: 'applied' }).count();
        const total = table.rows().count();
        $('#reportResultSummary').text(visible === total
            ? total + ' record' + (total === 1 ? '' : 's') + ' for the selected period'
            : visible + ' of ' + total + ' records shown');
    });

    function chartColors(count) {
        const colors = ['#2563eb','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#64748b'];
        return Array.from({ length: count }, (_, index) => colors[index % colors.length]);
    }
    function renderCharts() {
        if (trendChart) trendChart.destroy();
        if (sourceChart) sourceChart.destroy();
        if (statusChart) statusChart.destroy();
        trendChart = new Chart(document.getElementById('trendChartCanvas'), {
            type: 'line',
            data: { labels: trendChartData.labels || [], datasets: [{ data: trendChartData.values || [], borderColor: '#0f79b9', backgroundColor: 'rgba(15,121,185,.13)', fill: true, tension: .32, pointRadius: 3 }] },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
        sourceChart = new Chart(document.getElementById('sourceChartCanvas'), {
            type: 'doughnut',
            data: { labels: sourceChartData.labels || [], datasets: [{ data: sourceChartData.values || [], backgroundColor: chartColors((sourceChartData.labels || []).length) }] },
            options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
        });
        statusChart = new Chart(document.getElementById('statusChartCanvas'), {
            type: 'bar',
            data: { labels: statusChartData.labels || [], datasets: [{ data: statusChartData.values || [], backgroundColor: chartColors((statusChartData.labels || []).length), borderRadius: 6 }] },
            options: { maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
        $('#trendChartBox').toggleClass('is-empty', !(trendChartData.values || []).some(Number));
        $('#sourceChartBox').toggleClass('is-empty', !(sourceChartData.values || []).some(Number));
        $('#statusChartBox').toggleClass('is-empty', !(statusChartData.values || []).some(Number));
        $('#trendGranularity').text(String(trendChartData.granularity || 'monthly').replace(/^./, c => c.toUpperCase()));
    }
    function updateSummary(summary) {
        $('#kpiTotal').text(Number(summary.total || 0));
        $('#kpiOrders').text(Number(summary.orders || 0));
        $('#kpiPaid').text(Number(summary.paid || 0));
        $('#kpiCompleted').text(Number(summary.completed || 0));
        $('#kpiRevenue').text(currencySymbol + ' ' + String(summary.revenue || '0.00'));
        $('#kpiRefund').text(currencySymbol + ' ' + String(summary.refund_total || '0.00'));
        $('#completionRateLabel').text(String(summary.completion_rate || 0) + '% complete');
    }
    function showError(message) {
        $('#reportsAlert').stop(true, true).text(message).fadeIn(120);
        setTimeout(function () { $('#reportsAlert').fadeOut(200); }, 5000);
    }
    function localIsoDate(date) {
        const offset = date.getTimezoneOffset();
        return new Date(date.getTime() - offset * 60000).toISOString().slice(0, 10);
    }
    function syncPresetState() {
        const fromValue = $('#reportFrom').val();
        const toValue = $('#reportTo').val();
        $('.preset-btn').each(function () {
            const days = Number(this.dataset.days);
            const to = new Date();
            const from = new Date();
            from.setDate(to.getDate() - days + 1);
            $(this).toggleClass('active', fromValue === localIsoDate(from) && toValue === localIsoDate(to));
        });
    }
    function validateFilters() {
        const from = $('#reportFrom').val();
        const to = $('#reportTo').val();
        if (!from || !to) {
            showError('Choose both a start date and an end date.');
            return false;
        }
        if (from > to) {
            showError('The start date must be on or before the end date.');
            return false;
        }
        return true;
    }
    function syncReportUrl() {
        const url = new URL(window.location.href);
        url.searchParams.set('from', $('#reportFrom').val());
        url.searchParams.set('to', $('#reportTo').val());
        url.searchParams.set('source', $('#reportSource').val());
        window.history.replaceState({}, '', url);
    }
    function runReport() {
        if (!validateFilters()) return;
        if (activeRequest) activeRequest.abort();
        const button = $('#runReportBtn').prop('disabled', true).html('<i class="spin" data-lucide="loader-circle"></i> Loading');
        $('#resetReportBtn, #exportCsvBtn').prop('disabled', true);
        $('#reportsAlert').hide();
        lucide.createIcons();
        activeRequest = $.post("<?= base_url('admin/reports/data') ?>", {
            from: $('#reportFrom').val(),
            to: $('#reportTo').val(),
            source: $('#reportSource').val()
        }).done(function (response) {
            if (!response || !response.status) {
                showError(response?.message || 'Unable to generate report.');
                return;
            }
            trendChartData = response.trend_chart || { labels: [], values: [] };
            sourceChartData = response.source_chart || { labels: [], values: [] };
            statusChartData = response.status_chart || { labels: [], values: [] };
            if (response.filters) {
                $('#reportFrom').val(response.filters.from || $('#reportFrom').val());
                $('#reportTo').val(response.filters.to || $('#reportTo').val());
                $('#reportSource').val(response.filters.source || 'all');
            }
            updateSummary(response.summary || {});
            renderCharts();
            renderTable(response.rows || []);
            syncPresetState();
            syncReportUrl();
        }).fail(function (xhr, status) {
            if (status === 'abort') return;
            showError('Unexpected error while generating the report.');
        }).always(function () {
            activeRequest = null;
            button.prop('disabled', false).html('<i data-lucide="play"></i> Run Report');
            $('#resetReportBtn, #exportCsvBtn').prop('disabled', false);
            lucide.createIcons();
        });
    }
    $('#runReportBtn').on('click', runReport);
    $('#reportFilters').on('submit', function (event) {
        event.preventDefault();
        runReport();
    });
    $('#reportFrom, #reportTo').on('change', syncPresetState);
    $('.preset-btn').on('click', function () {
        const days = Number(this.dataset.days || 90);
        const to = new Date();
        const from = new Date();
        from.setDate(to.getDate() - days + 1);
        $('#reportFrom').val(localIsoDate(from));
        $('#reportTo').val(localIsoDate(to));
        $('.preset-btn').removeClass('active');
        $(this).addClass('active');
        runReport();
    });
    $('#resetReportBtn').on('click', function () {
        const to = new Date();
        const from = new Date();
        from.setDate(to.getDate() - 89);
        $('#reportFrom').val(localIsoDate(from));
        $('#reportTo').val(localIsoDate(to));
        $('#reportSource').val('all');
        $('#tableSourceFilter, #tableStatusFilter').val('');
        table.search('').columns().search('').draw();
        syncPresetState();
        runReport();
    });
    $('#exportCsvBtn').on('click', function () {
        const exportRows = table.rows({ search: 'applied' }).data().toArray();
        if (!exportRows.length) {
            showError('There are no visible report records to export.');
            return;
        }
        const quote = value => '"' + String(value ?? '').replaceAll('"', '""') + '"';
        const lines = [['Source','Reference','Order','Customer','Status','Amount','Channel','Date'].map(quote).join(',')];
        exportRows.forEach(row => lines.push([row.source,row.reference,row.order_code,row.customer,row.status,row.amount,row.channel,row.event_date].map(quote).join(',')));
        const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'business-report-' + $('#reportFrom').val() + '-to-' + $('#reportTo').val() + '.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });

    populateTableFilters();
    renderCharts();
    syncPresetState();
    table.draw(false);
});
</script>

<?php include('footer.php'); ?>
