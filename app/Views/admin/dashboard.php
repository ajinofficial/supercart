<?php
$page = $page ?? 'dashboard';
$stats = is_array($stats ?? null) ? $stats : [];
$progress = is_array($progress ?? null) ? $progress : [];
$recentOrders = is_array($recentOrders ?? null) ? $recentOrders : [];
$dashboardUserName = trim((string) ($userName ?? 'Administrator'));
$dashboardDate = trim((string) ($currentDate ?? date('d M Y')));
$monthlyTarget = max(0, min(100, (int) ($progress['monthly_target'] ?? 0)));
$orderFulfillment = max(0, min(100, (int) ($progress['order_fulfillment'] ?? 0)));
$newCustomersProgress = max(0, min(100, (int) ($progress['new_customers'] ?? 0)));

$statusClassMap = [
    'completed' => 'status-complete',
    'delivered' => 'status-complete',
    'paid' => 'status-complete',
    'processing' => 'status-pending',
    'pending' => 'status-pending',
    'shipped' => 'status-pending',
    'on_hold' => 'status-hold',
    'cancelled' => 'status-hold',
    'failed' => 'status-hold',
];

$formatCount = static function ($value): string {
    return number_format((int) $value);
};

$formatPercentChange = static function (float $current, float $previous): array {
    if ($previous <= 0) {
        return [
            'class' => $current > 0 ? 'up' : 'down',
            'text' => $current > 0 ? 'New this week' : 'No change this week',
        ];
    }

    $change = (($current - $previous) / $previous) * 100;
    return [
        'class' => $change >= 0 ? 'up' : 'down',
        'text' => ($change >= 0 ? '+' : '') . number_format($change, 1) . '% vs last week',
    ];
};

include('header.php');
include('menus.php');
?>

<style>
    .dashboard-shell {
        padding: 4px 4px 6px;
    } 

    .dashboard-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .dashboard-title {
        margin: 0;
        font-size: 1.28rem;
        font-weight: 800;
        color: #0f2740;
    }

    .dashboard-subtitle {
        margin-top: 3px;
        font-size: 0.84rem;
        color: #5f7283;
    }

    .dashboard-chip {
        background: #ffffff;
        border: 1px solid #dce7f1;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #23527a;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .kpi-grid {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
        margin-top: 4px;
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 14px;
        border: 1px solid #e4edf5;
        box-shadow: 0 8px 22px rgba(17, 64, 102, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .kpi-icon.orders { background: linear-gradient(135deg, var(--theme-color-light), var(--theme-color-darker)); }
    .kpi-icon.products { background: linear-gradient(135deg, #0d9a87, #107869); }
    .kpi-icon.customers { background: linear-gradient(135deg, #5d67d8, #3845b4); }
    .kpi-icon.revenue { background: linear-gradient(135deg, #d6841a, #bf5d0b); }

    .kpi-label {
        margin: 0;
        color: #607586;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .kpi-value {
        margin: 3px 0 0;
        font-size: 1.35rem;
        line-height: 1.05;
        font-weight: 800;
        color: #0e2a44;
    }

    .kpi-change {
        margin-top: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .kpi-change.up { color: #11894b; }
    .kpi-change.down { color: #bf3f33; }

    .dashboard-grid {
        margin-top: 14px;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 14px;
    }

    .panel-card {
        background: #ffffff;
        border: 1px solid #e4edf5;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(16, 65, 104, 0.07);
        overflow: hidden;
    }

    .panel-head {
        padding: 13px 14px;
        border-bottom: 1px solid #edf2f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .panel-title {
        margin: 0;
        font-size: 0.93rem;
        font-weight: 800;
        color: #14334d;
    }

    .panel-link {
        font-size: 0.77rem;
        text-decoration: none;
        font-weight: 700;
        color: var(--theme-color-dark);
    }

    .progress-wrap {
        padding: 14px;
    }

    .progress-item {
        margin-bottom: 13px;
    }

    .progress-item:last-child {
        margin-bottom: 0;
    }

    .progress-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 0.79rem;
        font-weight: 700;
        color: #506577;
    }

    .progress-track {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #e8eff5;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
    }

    .bar-1 { width: 82%; background: #1f6fb0; }
    .bar-2 { width: 64%; background: #13a18a; }
    .bar-3 { width: 46%; background: #e48d1f; }

    .recent-empty {
        padding: 24px 14px;
        color: #607587;
        font-size: 0.86rem;
        text-align: center;
    }

    .recent-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-table th,
    .recent-table td {
        text-align: left;
        font-size: 0.82rem;
        padding: 11px 14px;
        border-bottom: 1px solid #eef3f7;
    }

    .recent-table th {
        color: #607587;
        font-weight: 700;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 9px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .status-complete {
        background: #e7f8ee;
        color: #107c40;
    }

    .status-pending {
        background: #fff3dc;
        color: #986300;
    }

    .status-hold {
        background: #fce8ea;
        color: #a9323a;
    }

    .action-list {
        padding: 10px 14px 14px;
    }

    .action-btn {
        width: 100%;
        border: 1px solid #dce8f2;
        border-radius: 10px;
        background: #f8fbfe;
        color: #164564;
        padding: 10px 11px;
        font-size: 0.82rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        text-decoration: none;
    }

    .action-btn:first-child {
        margin-top: 0;
    }

    .action-btn:hover {
        background: #edf5fc;
    }

    @media (max-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-shell">
    <div class="dashboard-head">
        <div>
            <h2 class="dashboard-title">Dashboard Overview</h2>
            <div class="dashboard-subtitle">Welcome back, <?= esc($dashboardUserName) ?>. Here is your business snapshot.</div>
        </div>
        <div class="dashboard-chip">
            <i data-lucide="calendar-days"></i>
            <span><?= esc($dashboardDate) ?></span>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon orders"><i data-lucide="shopping-cart"></i></div>
            <div>
                <p class="kpi-label">Total Orders</p>
                <p class="kpi-value"><?= esc($formatCount($stats['orders'] ?? 0)) ?></p>
                <div class="kpi-change up">+<?= esc($formatCount($stats['orders_week'] ?? 0)) ?> this week</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon products"><i data-lucide="package"></i></div>
            <div>
                <p class="kpi-label">Products</p>
                <p class="kpi-value"><?= esc($formatCount($stats['products'] ?? 0)) ?></p>
                <div class="kpi-change up">+<?= esc($formatCount($stats['products_week'] ?? 0)) ?> new items</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon customers"><i data-lucide="users"></i></div>
            <div>
                <p class="kpi-label">Customers</p>
                <p class="kpi-value"><?= esc($formatCount($stats['customers'] ?? 0)) ?></p>
                <div class="kpi-change up">+<?= esc($formatCount($stats['customers_week'] ?? 0)) ?> this week</div>
            </div>
        </div>

        <?php $revenueChange = $formatPercentChange((float) ($stats['revenue_week'] ?? 0), (float) ($stats['revenue_previous_week'] ?? 0)); ?>
        <div class="kpi-card">
            <div class="kpi-icon revenue"><i data-lucide="indian-rupee"></i></div>
            <div>
                <p class="kpi-label">Total Revenue</p>
                <p class="kpi-value"><?= esc($currencySymbol) ?> <?= esc(number_format((float) ($stats['revenue'] ?? 0), 2)) ?></p>
                <div class="kpi-change <?= esc($revenueChange['class']) ?>"><?= esc($revenueChange['text']) ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="panel-card">
            <div class="panel-head">
                <h3 class="panel-title">Recent Orders</h3>
                <a class="panel-link" href="<?= base_url('admin/orders') ?>">View all</a>
            </div>
            <div class="table-responsive">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentOrders)) : ?>
                            <?php foreach ($recentOrders as $order) : ?>
                                <?php
                                $status = strtolower(trim((string) ($order['status'] ?? 'pending')));
                                $statusClass = $statusClassMap[$status] ?? 'status-pending';
                                $statusText = ucwords(str_replace('_', ' ', $status !== '' ? $status : 'pending'));
                                $orderCode = trim((string) ($order['order_code'] ?? ''));
                                $orderId = (int) ($order['id'] ?? 0);
                                $customerName = trim((string) ($order['customer_name'] ?? ''));
                                ?>
                                <tr>
                                    <td><?= esc($orderCode !== '' ? $orderCode : '#' . $orderId) ?></td>
                                    <td><?= esc($customerName !== '' ? $customerName : 'Customer') ?></td>
                                    <td><span class="status-pill <?= esc($statusClass) ?>"><?= esc($statusText) ?></span></td>
                                    <td><?= esc($currencySymbol) ?> <?= esc(number_format((float) ($order['total'] ?? 0), 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">
                                    <div class="recent-empty">No recent orders found.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-head">
                <h3 class="panel-title">Sales Progress</h3>
                <a class="panel-link" href="#">Details</a>
            </div>
            <div class="progress-wrap">
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Monthly Target</span>
                        <span><?= esc((string) $monthlyTarget) ?>%</span>
                    </div>
                    <div class="progress-track"><div class="progress-bar bar-1" style="width: <?= esc((string) $monthlyTarget) ?>%;"></div></div>
                </div>

                <div class="progress-item">
                    <div class="progress-label">
                        <span>Order Fulfillment</span>
                        <span><?= esc((string) $orderFulfillment) ?>%</span>
                    </div>
                    <div class="progress-track"><div class="progress-bar bar-2" style="width: <?= esc((string) $orderFulfillment) ?>%;"></div></div>
                </div>

                <div class="progress-item">
                    <div class="progress-label">
                        <span>New Customers</span>
                        <span><?= esc((string) $newCustomersProgress) ?>%</span>
                    </div>
                    <div class="progress-track"><div class="progress-bar bar-3" style="width: <?= esc((string) $newCustomersProgress) ?>%;"></div></div>
                </div>
            </div>

            <div class="panel-head">
                <h3 class="panel-title">Quick Actions</h3>
            </div>
            <div class="action-list">
                <a class="action-btn" href="<?= base_url('admin/orders') ?>">
                    <i data-lucide="plus-circle"></i>
                    <span>Manage Orders</span>
                </a>
                <a class="action-btn" href="<?= base_url('admin/products') ?>">
                    <i data-lucide="package-plus"></i>
                    <span>Add Product</span>
                </a>
                <a class="action-btn" href="<?= base_url('admin/reports') ?>">
                    <i data-lucide="file-text"></i>
                    <span>Export Sales Report</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
