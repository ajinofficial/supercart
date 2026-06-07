<?php
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$currencySymbol = trim((string) ($currencySymbol ?? '$'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f1f4f8;
            --surface: #ffffff;
            --ink: #162335;
            --muted: #5f6f86;
            --line: #dde4ee;
            --accent: #1e7ed7;
            --accent-2: #f9733f;
            --radius-xl: 26px;
            --radius-lg: 18px;
            --radius-md: 12px;
            --shadow: 0 14px 30px rgba(21, 39, 71, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Manrope", sans-serif;
            background: radial-gradient(circle at top right, #edf4ff 0%, var(--bg) 45%);
            color: var(--ink);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 18px 40px;
        }

        .top-nav {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid var(--line);
            border-radius: 0 0 18px 18px;
            padding: 12px clamp(18px, 3vw, 44px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px 18px;
            box-shadow: var(--shadow);
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }

        .brand {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1c2740;
            text-decoration: none;
        }

        .brand img {
            height: 38px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
            display: block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #1f2b3f;
        }

        .nav-btn {
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #d7e0ec;
            background: #fff;
            font-weight: 800;
            color: #1f2b3f;
        }

        .nav-btn.primary {
            background: linear-gradient(130deg, #1e7ed7, #56b0f1);
            color: #fff;
            border-color: transparent;
        }

        .nav-profile {
            display: none;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #1f2b3f;
            font-weight: 800;
            font-size: 0.85rem;
        }

        .nav-profile img {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            border: 1px solid #d6dce6;
            object-fit: cover;
            background: #eef1f5;
        }

        .page-head {
            margin: 26px 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .page-title {
            margin: 0;
            font-size: 2.2rem;
        }

        .page-sub {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.98rem;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .order-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .order-search {
            min-width: min(310px, 100%);
            height: 42px;
            padding: 0 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            outline: 0;
            background: #fff;
        }

        .order-search:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(30,126,215,.12); }

        .refresh-btn, .order-action {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px 13px;
            color: #23364d;
            background: #fff;
            font-weight: 800;
            cursor: pointer;
        }

        .refresh-btn:hover, .order-action:hover { border-color: var(--accent); color: var(--accent); }

        .stats {
            margin: 18px 0;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            background: #fff;
            box-shadow: 0 8px 20px rgba(21,39,71,.05);
        }

        .stat-card strong { display: block; font-size: 1.55rem; }
        .stat-card span { color: var(--muted); font-size: .82rem; font-weight: 700; }

        .chip {
            border-radius: 999px;
            border: 1px solid #dbe4ef;
            background: #fff;
            padding: 6px 12px;
            font-weight: 700;
            font-size: 0.82rem;
            color: #23364d;
            cursor: pointer;
        }

        .orders {
            display: grid;
            gap: 16px;
        }

        .order-card {
            background: var(--surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            padding: 18px;
            display: grid;
            gap: 12px;
        }

        .order-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .order-id {
            font-weight: 800;
            font-size: 1.05rem;
        }

        .order-date {
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .status {
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        .status.delivered { background: #e9f7f2; color: #155a46; }
        .status.processing { background: #fff4e6; color: #7a4a12; }
        .status.cancelled { background: #fff1f0; color: #8c1c13; }
        .status.out_for_delivery { background: #e9f2ff; color: #155aa8; }
        .status.delayed { background: #fff0dc; color: #a34c00; }

        .order-body {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 16px;
        }

        .items {
            display: grid;
            gap: 8px;
        }

        .item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f6f8fb;
            border: 1px solid #e4e9f2;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .summary {
            border: 1px solid #e4e9f2;
            border-radius: 14px;
            padding: 12px;
            display: grid;
            gap: 8px;
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            color: #2c3b52;
            font-size: 0.9rem;
        }

        .summary-total {
            font-size: 1.05rem;
            color: #111b2a;
        }

        .order-progress {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .progress-step {
            position: relative;
            padding-top: 18px;
            color: #8693a5;
            text-align: center;
            font-size: .7rem;
            font-weight: 800;
        }

        .progress-step::before {
            content: "";
            position: absolute;
            top: 4px;
            left: 0;
            width: 100%;
            height: 5px;
            border-radius: 99px;
            background: #dfe6ef;
        }

        .progress-step.done { color: #176f55; }
        .progress-step.done::before { background: #34a982; }
        .progress-step.current { color: var(--accent); }
        .progress-step.current::before { background: var(--accent); }
        .progress-step.cancelled { color: #9a2d26; }
        .progress-step.cancelled::before { background: #e85b52; }

        .order-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .order-action.primary { border-color: transparent; color: #fff; background: var(--accent); }
        .order-action.primary:hover { color: #fff; background: #1768b0; }

        .order-details {
            display: none;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .order-details.open { display: grid; }
        .detail-box { padding: 13px; border-radius: 12px; background: #f7f9fc; }
        .detail-box h3 { margin: 0 0 8px; font-size: .88rem; }
        .detail-box p { margin: 4px 0; color: var(--muted); font-size: .8rem; overflow-wrap: anywhere; }

        .state-panel {
            padding: 42px 24px;
            border: 1px dashed #cfd7e4;
            border-radius: 16px;
            text-align: center;
            color: var(--muted);
            background: rgba(255,255,255,.75);
        }

        .state-panel i { display: block; margin-bottom: 12px; font-size: 2rem; color: var(--accent); }

        .empty {
            padding: 24px;
            border: 1px dashed #cfd7e4;
            border-radius: 12px;
            text-align: center;
            color: #606b80;
            font-weight: 700;
        }

        @media (max-width: 980px) {
            .order-body {
                grid-template-columns: 1fr;
            }
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 680px) {
            .nav-links {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .top-nav {
                align-items: flex-start;
                flex-direction: column;
            }
            .stats { grid-template-columns: 1fr 1fr; }
            .order-details { grid-template-columns: 1fr; }
            .order-toolbar, .order-search { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="top-nav">
            <a class="brand" href="<?= base_url('user/dashboard') ?>" aria-label="<?= esc($websiteName) ?>">
                <?php if ($logoUrl !== '') : ?>
                    <img src="<?= esc($logoUrl) ?>" alt="<?= esc($websiteName) ?>">
                <?php else : ?>
                    <?= esc($websiteName) ?>
                <?php endif; ?>
            </a>
            <nav class="nav-links">
                <a href="<?= base_url('user/dashboard') ?>">Home</a>
                <a href="<?= base_url('user/catalog') ?>">Courses</a>
                <a href="<?= base_url('user/contact') ?>">Contact</a>
                <a href="<?= base_url('user/about') ?>">About</a>
                <a href="<?= base_url('user/profile') ?>">Profile</a>
                <a href="<?= base_url('user/cart') ?>"><i class="fa-solid fa-cart-shopping"></i></a>
                <?= view('user/partials/notification_bell') ?>
                <a id="navRegister" class="nav-btn primary" href="<?= base_url('register') ?>">Register</a>
                <a id="navLogin" class="nav-btn" href="<?= base_url('login') ?>">Login</a>
                <a id="navProfile" class="nav-profile" href="<?= base_url('user/profile') ?>">
                    <img id="navProfileImage" src="<?= esc(base_url('uploads/customers/default-user.svg')) ?>" alt="Profile">
                    <span id="navProfileName">Profile</span>
                </a>
                <a id="navLogout" class="nav-btn" href="<?= base_url('logout') ?>" style="display:none;">Logout</a>
            </nav>
        </header>

        <div class="page-head">
            <div>
                <h1 class="page-title">My Orders</h1>
                <p class="page-sub" id="ordersSubtitle">Track your recent purchases and delivery status.</p>
            </div>
            <div class="order-toolbar">
                <input class="order-search" id="orderSearch" type="search" placeholder="Search order ID or product">
                <button class="refresh-btn" id="refreshOrders" type="button"><i class="fa-solid fa-rotate"></i> Refresh</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card"><strong id="statAll">0</strong><span>Total orders</span></div>
            <div class="stat-card"><strong id="statActive">0</strong><span>Active deliveries</span></div>
            <div class="stat-card"><strong id="statDelivered">0</strong><span>Delivered</span></div>
            <div class="stat-card"><strong id="statSpent"><?= esc($currencySymbol) ?>0</strong><span>Total spent</span></div>
        </div>

        <div class="filters">
            <button class="chip" data-filter="all">All</button>
            <button class="chip" data-filter="processing">Processing</button>
            <button class="chip" data-filter="out_for_delivery">Out for delivery</button>
            <button class="chip" data-filter="delivered">Delivered</button>
            <button class="chip" data-filter="cancelled">Cancelled</button>
        </div>

        <div class="orders" id="ordersList"></div>
        <div class="state-panel" id="ordersState"><i class="fa-solid fa-spinner fa-spin"></i>Loading your orders...</div>

        <?= view('user/partials/footer', ['websiteName' => $websiteName]) ?>
    </div>

    <script>
        (function () {
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            function apiGet(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                });
            }
            apiGet("<?= base_url('rest_api/session') ?>")
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (!payload || !payload.status || !payload.user) return;
                    const loggedIn = payload.logged_in === true;
                    const user = payload.user || {};
                    const navRegister = document.getElementById('navRegister');
                    const navLogin = document.getElementById('navLogin');
                    const navProfile = document.getElementById('navProfile');
                    const navProfileName = document.getElementById('navProfileName');
                    const navProfileImage = document.getElementById('navProfileImage');
                    const navLogout = document.getElementById('navLogout');
                    if (!navRegister || !navLogin || !navProfile || !navLogout) return;
                    if (loggedIn) {
                        navRegister.style.display = 'none';
                        navLogin.style.display = 'none';
                        navProfile.style.display = 'inline-flex';
                        navLogout.style.display = 'inline-flex';
                        if (typeof user.name === 'string' && user.name.trim() !== '') {
                            navProfileName.textContent = user.name.trim();
                        }
                        if (typeof user.image_url === 'string' && user.image_url.trim() !== '') {
                            navProfileImage.src = user.image_url.trim();
                        }
                    } else {
                        navRegister.style.display = 'inline-flex';
                        navLogin.style.display = 'inline-flex';
                        navProfile.style.display = 'none';
                        navLogout.style.display = 'none';
                    }
                })
                .catch(function () {});
        })();

        (function () {
            return; // Replaced by the enhanced server-first order workflow below.
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;
            const ordersKey = 'orders';
            const list = document.getElementById('ordersList');
            const empty = document.getElementById('ordersEmpty');
            const subtitle = document.getElementById('ordersSubtitle');
            const filterButtons = Array.from(document.querySelectorAll('.chip[data-filter]'));

            function apiGet(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                });
            }

            function readOrders() {
                try {
                    const raw = localStorage.getItem(ordersKey);
                    const parsed = raw ? JSON.parse(raw) : [];
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }

            function currency(value) {
                const num = Number(value || 0);
                if (/[A-Za-z]$/.test(currencySymbol)) {
                    return currencySymbol + ' ' + num.toLocaleString();
                }
                return currencySymbol + num.toLocaleString();
            }

            function computeTotals(items) {
                return items.reduce(function (sum, item) {
                    return sum + (Number(item.price || 0) * Number(item.qty || 1));
                }, 0);
            }

            function normalizeStatus(value) {
                const raw = String(value ?? '').trim();
                if (raw === '') return 'processing';
                if (/^\d+$/.test(raw)) {
                    const num = Number(raw);
                    if (num === 2) return 'delivered';
                    if (num === 3) return 'cancelled';
                    return 'processing';
                }
                const text = raw.toLowerCase();
                if (text === 'delivered' || text === 'processing' || text === 'cancelled') {
                    return text;
                }
                return 'processing';
            }

            function normalizePaymentMethod(value) {
                const raw = String(value ?? '').trim();
                if (raw === '') return 'cod';
                if (/^\d+$/.test(raw)) {
                    const num = Number(raw);
                    if (num === 1) return 'cod';
                }
                const text = raw.toLowerCase();
                return text !== '' ? text : 'cod';
            }

            function paymentLabel(value) {
                const method = normalizePaymentMethod(value);
                if (method === 'cod') return 'Cash on Delivery';
                if (method === 'card') return 'Card';
                if (method === 'upi') return 'UPI';
                if (method === 'wallet') return 'Wallet';
                return method.toUpperCase();
            }

            function render(orders, activeFilter) {
                if (!list) return;
                const rows = orders.filter(function (order) {
                    const statusText = normalizeStatus(order.status);
                    if (!activeFilter || activeFilter === 'all') return true;
                    return statusText === activeFilter;
                });

                if (rows.length === 0) {
                    list.innerHTML = '';
                    if (empty) empty.style.display = 'block';
                    return;
                }

                if (empty) empty.style.display = 'none';
                list.innerHTML = rows.map(function (order) {
                    const statusText = normalizeStatus(order.status);
                    const paymentText = paymentLabel(order.payment_method);
                    const subtotal = typeof order.subtotal === 'number'
                        ? order.subtotal
                        : computeTotals(order.items || []);
                    const discount = typeof order.discount === 'number' ? order.discount : 0;
                    const total = typeof order.total === 'number' ? order.total : Math.max(subtotal - discount, 0);
                    return '' +
                        '<article class="order-card">' +
                            '<div class="order-head">' +
                                '<div>' +
                                    '<div class="order-id">Order ' + order.id + '</div>' +
                                    '<div class="order-date">Placed on ' + order.date + '</div>' +
                                '</div>' +
                                '<span class="status ' + statusText + '">' + statusText + '</span>' +
                            '</div>' +
                            '<div class="order-body">' +
                                '<div class="items">' +
                                    (order.items || []).map(function (item) {
                                        return '' +
                                            '<div class="item-row">' +
                                                '<span>' + item.qty + ' x ' + item.name + '</span>' +
                                                '<span>' + currency(item.price) + '</span>' +
                                            '</div>';
                                    }).join('') +
                                '</div>' +
                                '<div class="summary">' +
                                    '<div class="summary-row"><span>Subtotal</span><span>' + currency(subtotal) + '</span></div>' +
                                    '<div class="summary-row"><span>Shipping</span><span>' + currency(0) + '</span></div>' +
                                    '<div class="summary-row"><span>Payment</span><span>' + paymentText + '</span></div>' +
                                    (discount > 0
                                        ? '<div class="summary-row"><span>Discount</span><span>-' + currency(discount) + '</span></div>'
                                        : '') +
                                    '<div class="summary-row summary-total"><span>Total</span><span>' + currency(total) + '</span></div>' +
                                '</div>' +
                            '</div>' +
                        '</article>';
                }).join('');
            }

            let orders = [];
            const stored = readOrders();
            if (stored.length > 0) {
                orders = stored;
            }

            let active = 'all';
            function applyFilter(filter) {
                active = filter;
                filterButtons.forEach(function (btn) {
                    btn.style.background = btn.getAttribute('data-filter') === filter ? '#1e7ed7' : '#fff';
                    btn.style.color = btn.getAttribute('data-filter') === filter ? '#fff' : '#23364d';
                });
                render(orders, active);
            }

            filterButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    applyFilter(btn.getAttribute('data-filter'));
                });
            });

            applyFilter('all');

            apiGet("<?= base_url('rest_api/orders') ?>")
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (!payload || !payload.status || !Array.isArray(payload.orders)) {
                        orders = [];
                        if (subtitle) {
                            subtitle.textContent = 'No orders found yet.';
                        }
                        applyFilter(active);
                        return;
                    }
                    if (payload.orders.length === 0) {
                        orders = [];
                        if (subtitle) {
                            subtitle.textContent = 'No orders found yet.';
                        }
                        applyFilter(active);
                        return;
                    }
                    orders = payload.orders;
                    if (subtitle) {
                        subtitle.textContent = 'Showing your saved orders.';
                    }
                    applyFilter(active);
                })
                .catch(function () {});
        })();

        (function () {
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;
            const ordersUrl = <?= json_encode(base_url('rest_api/orders')) ?>;
            const cartUrl = <?= json_encode(base_url('user/cart')) ?>;
            const supportUrl = <?= json_encode(base_url('user/notifications')) ?>;
            const list = document.getElementById('ordersList');
            const state = document.getElementById('ordersState');
            const subtitle = document.getElementById('ordersSubtitle');
            const search = document.getElementById('orderSearch');
            const refreshButton = document.getElementById('refreshOrders');
            const filterButtons = Array.from(document.querySelectorAll('.chip[data-filter]'));
            let orders = [];
            let activeFilter = 'all';

            function escapeHtml(value) {
                const node = document.createElement('div');
                node.textContent = String(value ?? '');
                return node.innerHTML;
            }

            function currency(value) {
                const number = Number(value || 0);
                const formatted = number.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                return /[A-Za-z]$/.test(currencySymbol)
                    ? currencySymbol + ' ' + formatted
                    : currencySymbol + formatted;
            }

            function label(value) {
                return String(value || '')
                    .replaceAll('_', ' ')
                    .replace(/\b\w/g, function (letter) { return letter.toUpperCase(); });
            }

            function displayDate(value) {
                if (!value) return 'Not available';
                const date = new Date(String(value).replace(' ', 'T'));
                return Number.isNaN(date.getTime())
                    ? String(value)
                    : date.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
            }

            function effectiveStatus(order) {
                const orderStatus = String(order.status || 'processing').toLowerCase();
                if (orderStatus === 'cancelled' || orderStatus === 'delivered') return orderStatus;
                const deliveryStatus = String(order.delivery?.status || '').toLowerCase();
                return ['out_for_delivery', 'delayed', 'delivered'].includes(deliveryStatus)
                    ? deliveryStatus
                    : 'processing';
            }

            function paymentLabel(value) {
                const method = String(value || 'cod').toLowerCase();
                if (method === 'cod') return 'Cash on Delivery';
                if (method === 'upi') return 'UPI';
                if (method === 'gpay') return 'Google Pay';
                return label(method);
            }

            function progressHtml(order) {
                const status = effectiveStatus(order);
                if (status === 'cancelled') {
                    return '<div class="order-progress"><div class="progress-step cancelled">Order cancelled</div></div>';
                }
                const activeIndex = status === 'delivered' ? 3 : status === 'out_for_delivery' ? 2 : 0;
                return '<div class="order-progress">' +
                    ['processing', 'shipped', 'out_for_delivery', 'delivered'].map(function (stage, index) {
                        const className = index < activeIndex ? 'done' : (index === activeIndex ? 'current' : '');
                        return '<div class="progress-step ' + className + '">' + label(stage) + '</div>';
                    }).join('') +
                '</div>';
            }

            function updateStats() {
                document.getElementById('statAll').textContent = orders.length;
                document.getElementById('statActive').textContent = orders.filter(function (order) {
                    return ['processing', 'out_for_delivery', 'delayed'].includes(effectiveStatus(order));
                }).length;
                document.getElementById('statDelivered').textContent = orders.filter(function (order) {
                    return effectiveStatus(order) === 'delivered';
                }).length;
                document.getElementById('statSpent').textContent = currency(orders.reduce(function (sum, order) {
                    return effectiveStatus(order) === 'cancelled' ? sum : sum + Number(order.total || 0);
                }, 0));
            }

            function filteredOrders() {
                const term = String(search.value || '').trim().toLowerCase();
                return orders.filter(function (order) {
                    if (activeFilter !== 'all' && effectiveStatus(order) !== activeFilter) return false;
                    if (!term) return true;
                    const products = (order.items || []).map(function (item) { return item.name || ''; }).join(' ');
                    return (String(order.id || '') + ' ' + products).toLowerCase().includes(term);
                });
            }

            function render() {
                const rows = filteredOrders();
                if (!rows.length) {
                    list.innerHTML = '';
                    state.style.display = 'block';
                    state.innerHTML = '<i class="fa-regular fa-folder-open"></i>No orders match your search or filter.';
                    return;
                }

                state.style.display = 'none';
                list.innerHTML = rows.map(function (order) {
                    const status = effectiveStatus(order);
                    const delivery = order.delivery || {};
                    const payment = order.payment || {};
                    const customer = order.customer || {};
                    const itemRows = (order.items || []).map(function (item) {
                        return '<div class="item-row"><span>' + Number(item.qty || 1) + ' x ' +
                            escapeHtml(item.name || 'Item') + '</span><span>' +
                            currency(Number(item.price || 0) * Number(item.qty || 1)) + '</span></div>';
                    }).join('');

                    return '<article class="order-card" data-order-id="' + escapeHtml(order.id) + '">' +
                        '<div class="order-head"><div><div class="order-id">Order ' + escapeHtml(order.id) +
                        '</div><div class="order-date">Placed on ' + escapeHtml(displayDate(order.created_at || order.date)) +
                        '</div></div><span class="status ' + status + '">' + escapeHtml(label(status)) + '</span></div>' +
                        progressHtml(order) +
                        '<div class="order-body"><div class="items">' + itemRows + '</div><div class="summary">' +
                        '<div class="summary-row"><span>Subtotal</span><span>' + currency(order.subtotal) + '</span></div>' +
                        '<div class="summary-row"><span>Shipping</span><span>' + currency(0) + '</span></div>' +
                        '<div class="summary-row"><span>Payment</span><span>' + escapeHtml(paymentLabel(order.payment_method)) + '</span></div>' +
                        (Number(order.discount || 0) > 0
                            ? '<div class="summary-row"><span>Discount</span><span>-' + currency(order.discount) + '</span></div>'
                            : '') +
                        '<div class="summary-row summary-total"><span>Total</span><span>' + currency(order.total) + '</span></div>' +
                        '</div></div>' +
                        '<div class="order-actions"><button class="order-action" type="button" data-action="details">View details</button>' +
                        '<button class="order-action" type="button" data-action="invoice">Print invoice</button>' +
                        '<button class="order-action primary" type="button" data-action="reorder">Reorder</button>' +
                        '<a class="order-action" href="' + supportUrl + '">Get support</a></div>' +
                        '<div class="order-details">' +
                        '<div class="detail-box"><h3>Delivery</h3><p>Status: ' + escapeHtml(label(delivery.status || 'not_assigned')) +
                        '</p><p>Shipment: ' + escapeHtml(delivery.shipment_code || 'Not assigned') +
                        '</p><p>Hub: ' + escapeHtml(delivery.hub || 'Not assigned') +
                        '</p><p>Rider: ' + escapeHtml(delivery.rider_name || 'Not assigned') +
                        '</p><p>Expected: ' + escapeHtml(displayDate(delivery.eta_date)) + '</p></div>' +
                        '<div class="detail-box"><h3>Payment</h3><p>Status: ' + escapeHtml(label(payment.status || 'not_recorded')) +
                        '</p><p>Transaction: ' + escapeHtml(payment.transaction_code || 'Not available') +
                        '</p><p>Reference: ' + escapeHtml(payment.gateway_ref || 'Not available') +
                        '</p><p>Paid on: ' + escapeHtml(displayDate(payment.paid_on)) + '</p></div>' +
                        '<div class="detail-box"><h3>Delivery address</h3><p>' +
                        escapeHtml(customer.address || 'No address recorded') + '</p></div>' +
                        '<div class="detail-box"><h3>Order notes</h3><p>' + escapeHtml(customer.note || 'No notes') + '</p>' +
                        (order.coupon_code ? '<p>Coupon: ' + escapeHtml(order.coupon_code) + '</p>' : '') + '</div></div></article>';
                }).join('');
            }

            function reorder(order) {
                const items = (order.items || []).map(function (item) {
                    return {
                        id: Number(item.id || item.product_id || 0),
                        name: String(item.name || 'Item'),
                        price: Number(item.price || 0),
                        qty: Math.max(1, Number(item.qty || 1)),
                        image_url: String(item.image_url || '')
                    };
                });
                localStorage.setItem('cart_items', JSON.stringify(items));
                window.location.href = cartUrl;
            }

            function printInvoice(order) {
                const popup = window.open('', '_blank', 'width=820,height=720');
                if (!popup) return;
                const itemRows = (order.items || []).map(function (item) {
                    return '<tr><td>' + escapeHtml(item.name || 'Item') + '</td><td>' + Number(item.qty || 1) +
                        '</td><td>' + currency(item.price) + '</td><td>' +
                        currency(Number(item.price || 0) * Number(item.qty || 1)) + '</td></tr>';
                }).join('');
                popup.document.write('<!doctype html><html><head><title>Invoice ' + escapeHtml(order.id) +
                    '</title><style>body{font-family:Arial;padding:32px;color:#17212b}table{width:100%;border-collapse:collapse;margin:24px 0}th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}.total{text-align:right;font-size:20px;font-weight:bold}</style></head><body>' +
                    '<h1><?= esc($websiteName) ?></h1><h2>Invoice ' + escapeHtml(order.id) + '</h2><p>Order date: ' +
                    escapeHtml(displayDate(order.created_at || order.date)) + '</p><p>Customer: ' +
                    escapeHtml(order.customer?.name || '') + '</p><p>Address: ' +
                    escapeHtml(order.customer?.address || '') + '</p><table><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Amount</th></tr></thead><tbody>' +
                    itemRows + '</tbody></table><p class="total">Total: ' + currency(order.total) +
                    '</p><script>window.onload=function(){window.print()}<\/script></body></html>');
                popup.document.close();
            }

            function applyFilter(filter) {
                activeFilter = filter;
                filterButtons.forEach(function (button) {
                    const active = button.dataset.filter === filter;
                    button.style.background = active ? '#1e7ed7' : '#fff';
                    button.style.color = active ? '#fff' : '#23364d';
                });
                render();
            }

            function loadOrders() {
                state.style.display = 'block';
                state.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>Loading your orders...';
                refreshButton.disabled = true;
                fetch(ordersUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Api-Token': restApiToken }
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Unable to load orders.');
                        return response.json();
                    })
                    .then(function (payload) {
                        if (!payload || !payload.status || !Array.isArray(payload.orders)) {
                            throw new Error(payload?.message || 'Unable to load orders.');
                        }
                        orders = payload.orders;
                        subtitle.textContent = orders.length
                            ? 'Track delivery, review payment details, reorder, and print invoices.'
                            : 'No orders found yet.';
                        updateStats();
                        render();
                    })
                    .catch(function (error) {
                        list.innerHTML = '';
                        state.style.display = 'block';
                        state.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>' +
                            escapeHtml(error.message) + '<br><button class="refresh-btn" type="button" data-retry>Try again</button>';
                    })
                    .finally(function () { refreshButton.disabled = false; });
            }

            filterButtons.forEach(function (button) {
                button.addEventListener('click', function () { applyFilter(button.dataset.filter); });
            });
            search.addEventListener('input', render);
            refreshButton.addEventListener('click', loadOrders);
            state.addEventListener('click', function (event) {
                if (event.target.closest('[data-retry]')) loadOrders();
            });
            list.addEventListener('click', function (event) {
                const button = event.target.closest('[data-action]');
                if (!button) return;
                const card = button.closest('[data-order-id]');
                const order = orders.find(function (item) { return String(item.id) === String(card.dataset.orderId); });
                if (!order) return;
                if (button.dataset.action === 'details') {
                    const details = card.querySelector('.order-details');
                    details.classList.toggle('open');
                    button.textContent = details.classList.contains('open') ? 'Hide details' : 'View details';
                } else if (button.dataset.action === 'reorder') {
                    reorder(order);
                } else if (button.dataset.action === 'invoice') {
                    printInvoice(order);
                }
            });

            applyFilter('all');
            loadOrders();
        })();
    </script>
</body>
</html>
