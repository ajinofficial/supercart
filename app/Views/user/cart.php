<?php
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$currencySymbol = trim((string) ($currencySymbol ?? '$'));
$currencySpacer = preg_match('/[A-Za-z]$/', $currencySymbol) ? ' ' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f2f3f5;
            --surface: #ffffff;
            --ink: #1e2633;
            --muted: #6a7282;
            --line: #dde2ea;
            --accent: #1b7cc8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Nunito Sans", sans-serif;
            background: var(--bg);
            color: var(--ink);
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px;
        }
        .top-nav {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 0 0 18px 18px;
            padding: 12px clamp(18px, 3vw, 44px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px 18px;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }
        .brand img {
            height: 36px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
            display: block;
        }
        .brand {
            color: #1f2f46;
            font-size: 1.2rem;
            font-weight: 800;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.95rem;
            font-weight: 700;
        }
        .nav-links a {
            color: #1d2a40;
            text-decoration: none;
        }
        .cart-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
        }
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            line-height: 18px;
            text-align: center;
            border: 2px solid #fff;
            display: none;
        }
        .nav-btn {
            border-radius: 10px;
            padding: 8px 14px;
            border: 1px solid #d6dce6;
            background: #fff;
            font-weight: 800;
        }
        .nav-btn.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .nav-profile {
            display: none;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #1d2a40;
            font-weight: 800;
            font-size: 0.85rem;
        }
        .nav-profile img {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 1px solid #d6dce6;
            object-fit: cover;
            background: #eef1f5;
        }
        .page-head {
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .page-title {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
        }
        .page-sub {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.95rem;
            font-weight: 600;
        }
        .cart-layout {
            margin-top: 16px;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 18px;
        }
        .cart-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
        }
        .cart-empty {
            padding: 24px;
            border: 1px dashed #cfd7e4;
            border-radius: 12px;
            text-align: center;
            color: #606b80;
            font-weight: 700;
        }
        .cart-row {
            display: grid;
            grid-template-columns: 80px 1fr 120px 120px 44px;
            gap: 12px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eef1f6;
        }
        .cart-row:last-child {
            border-bottom: 0;
        }
        .thumb {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            overflow: hidden;
            background: #eceef2;
            display: grid;
            place-items: center;
        }
        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .thumb .fallback {
            font-size: 0.75rem;
            font-weight: 800;
            color: #637086;
            text-align: center;
            padding: 6px;
        }
        .item-title {
            margin: 0 0 4px;
            font-size: 0.98rem;
            font-weight: 800;
        }
        .item-meta {
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 600;
        }
        .qty-input {
            width: 72px;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #d6dce6;
            font-weight: 700;
        }
        .price-tag {
            font-weight: 800;
            color: #4b5a72;
        }
        .icon-btn {
            border: 0;
            background: #f3f6fb;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            color: #2a3a52;
            cursor: pointer;
        }
        .summary-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            display: grid;
            gap: 12px;
        }
        .coupon-card {
            border: 1px dashed #d5deea;
            border-radius: 12px;
            background: #f8fafc;
            padding: 12px;
            display: grid;
            gap: 10px;
        }
        .coupon-row {
            display: flex;
            gap: 8px;
        }
        .coupon-input {
            flex: 1;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #cfd7e4;
            font-weight: 700;
            text-transform: uppercase;
        }
        .coupon-btn {
            border-radius: 10px;
            padding: 8px 12px;
            border: 1px solid #cfd7e4;
            background: #fff;
            font-weight: 800;
            cursor: pointer;
        }
        .coupon-btn.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .coupon-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--muted);
            gap: 10px;
        }
        .coupon-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 4px 10px;
            background: #ecfdf3;
            color: #0f766e;
            border: 1px solid #b7f3d4;
            font-weight: 800;
        }
        .coupon-remove {
            border: 0;
            background: transparent;
            color: #b42318;
            font-weight: 800;
            cursor: pointer;
        }
        .coupon-message {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f5132;
        }
        .coupon-message.error {
            color: #b42318;
        }
        .coupon-message.info {
            color: #385d89;
        }
        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            color: #2d3a4f;
        }
        .summary-discount {
            color: #b42318;
        }
        .summary-total {
            font-size: 1.15rem;
            color: #111b2a;
        }
        .summary-actions {
            display: grid;
            gap: 10px;
        }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 50;
        }
        .modal-card {
            background: #fff;
            border-radius: 16px;
            width: min(520px, 100%);
            padding: 18px;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.2);
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }
        .modal-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }
        .modal-close {
            border: 0;
            background: #f3f6fb;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 800;
        }
        .checkout-form {
            display: grid;
            gap: 12px;
        }
        .form-field {
            display: grid;
            gap: 6px;
        }
        .form-field label {
            font-weight: 700;
            font-size: 0.9rem;
        }
        .form-field input,
        .form-field textarea,
        .form-field select {
            border: 1px solid #d6dce6;
            border-radius: 10px;
            padding: 8px 10px;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .form-row {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            border: 0;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
        }
        .btn.primary {
            background: var(--accent);
            color: #fff;
        }
        .btn.ghost {
            background: #f3f6fb;
            border: 1px solid #d6dce6;
            color: #243148;
        }
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 980px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 720px) {
            .cart-row {
                grid-template-columns: 64px 1fr;
                grid-template-areas:
                    "thumb info"
                    "thumb qty"
                    "thumb price"
                    "thumb total"
                    "thumb remove";
            }
            .cart-row .thumb { grid-area: thumb; }
            .cart-row .info { grid-area: info; }
            .cart-row .qty { grid-area: qty; }
            .cart-row .price { grid-area: price; }
            .cart-row .total { grid-area: total; }
            .cart-row .remove { grid-area: remove; }
            .nav-links {
                gap: 10px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .top-nav {
                align-items: flex-start;
                flex-direction: column;
            }
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
                <a class="cart-link" href="<?= base_url('user/cart') ?>" aria-label="Cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </a>
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
                <h1 class="page-title">Your Cart</h1>
                <p class="page-sub" id="cartSubtitle">Review your selected items.</p>
            </div>
        </div>

        <div class="cart-layout">
            <section class="cart-card">
                <div id="cartEmpty" class="cart-empty" style="display:none;">Your cart is empty.</div>
                <div id="cartList"></div>
            </section>

            <aside class="summary-card">
                <div class="coupon-card">
                    <div class="coupon-row">
                        <input class="coupon-input" type="text" id="couponCodeInput" placeholder="Enter coupon code">
                        <button class="coupon-btn primary" type="button" id="applyCouponBtn">Apply</button>
                    </div>
                    <div class="coupon-meta">
                        <span class="coupon-badge" id="couponBadge" style="display:none;">Coupon applied</span>
                        <button class="coupon-remove" type="button" id="removeCouponBtn" style="display:none;">Remove</button>
                    </div>
                    <div class="coupon-message" id="couponMessage" aria-live="polite"></div>
                </div>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="summarySubtotal"><?= esc($currencySymbol) ?>0</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span id="summaryShipping"><?= esc($currencySymbol) ?>0</span>
                </div>
                <div class="summary-row summary-discount" id="summaryDiscountRow" style="display:none;">
                    <span>Discount</span>
                    <span id="summaryDiscount"><?= esc($currencySymbol) ?>0</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span id="summaryTotal"><?= esc($currencySymbol) ?>0</span>
                </div>
                <div class="summary-actions">
                    <button class="btn primary" type="button" id="checkoutBtn">Proceed to Checkout</button>
                    <button class="btn ghost" type="button" id="clearCartBtn">Clear Cart</button>
                </div>
            </aside>
        </div>

        <?= view('user/partials/footer', ['websiteName' => $websiteName]) ?>
    </div>

    <div class="modal-backdrop" id="checkoutModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="checkoutModalTitle">
            <div class="modal-header">
                <h3 class="modal-title" id="checkoutModalTitle">Checkout Details</h3>
                <button type="button" class="modal-close" id="closeCheckoutModal" aria-label="Close">×</button>
            </div>
            <form class="checkout-form" id="checkoutForm">
                <div class="form-row">
                    <div class="form-field">
                        <label for="checkoutName">Full Name</label>
                        <input type="text" id="checkoutName" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-field">
                        <label for="checkoutPhone">Phone</label>
                        <input type="tel" id="checkoutPhone" name="phone" placeholder="10-digit mobile" required>
                    </div>
                </div>
                <div class="form-field">
                    <label for="checkoutEmail">Email (optional)</label>
                    <input type="email" id="checkoutEmail" name="email" placeholder="Used for payment receipt">
                </div>
                <div class="form-field">
                    <label for="checkoutAddress">Delivery Address</label>
                    <textarea id="checkoutAddress" name="address" rows="3" placeholder="House no, street, city, pincode" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="checkoutPayment">Payment Method</label>
                        <select id="checkoutPayment" name="payment_method" required>
                            <option value="cod" selected>Cash on Delivery</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="checkoutNote">Order Note (optional)</label>
                        <input type="text" id="checkoutNote" name="note" placeholder="Any delivery note">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn ghost" type="button" id="cancelCheckout">Cancel</button>
                    <button class="btn primary" type="submit" id="submitCheckout">Place Order</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
                    if (!payload || !payload.status || !payload.user) {
                        return;
                    }
                    const loggedIn = payload.logged_in === true;
                    const user = payload.user || {};
                    const navRegister = document.getElementById('navRegister');
                    const navLogin = document.getElementById('navLogin');
                    const navProfile = document.getElementById('navProfile');
                    const navProfileName = document.getElementById('navProfileName');
                    const navProfileImage = document.getElementById('navProfileImage');
                    const navLogout = document.getElementById('navLogout');

                    if (!navRegister || !navLogin || !navProfile || !navLogout) {
                        return;
                    }

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
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;
            const cartKey = 'cart_items';
            const couponKey = 'cart_coupon_code';
            const ordersKey = 'orders';
            const list = document.getElementById('cartList');
            const empty = document.getElementById('cartEmpty');
            const subtotalEl = document.getElementById('summarySubtotal');
            const shippingEl = document.getElementById('summaryShipping');
            const discountRow = document.getElementById('summaryDiscountRow');
            const discountEl = document.getElementById('summaryDiscount');
            const totalEl = document.getElementById('summaryTotal');
            const clearBtn = document.getElementById('clearCartBtn');
            const subtitle = document.getElementById('cartSubtitle');
            const cartBadge = document.getElementById('cartBadge');
            const checkoutBtn = document.getElementById('checkoutBtn');
            const couponInput = document.getElementById('couponCodeInput');
            const couponApplyBtn = document.getElementById('applyCouponBtn');
            const couponRemoveBtn = document.getElementById('removeCouponBtn');
            const couponMessage = document.getElementById('couponMessage');
            const couponBadge = document.getElementById('couponBadge');
            const checkoutModal = document.getElementById('checkoutModal');
            const closeCheckoutModal = document.getElementById('closeCheckoutModal');
            const cancelCheckout = document.getElementById('cancelCheckout');
            const checkoutForm = document.getElementById('checkoutForm');
            const submitCheckout = document.getElementById('submitCheckout');
            const checkoutName = document.getElementById('checkoutName');
            const checkoutPhone = document.getElementById('checkoutPhone');
            const checkoutEmail = document.getElementById('checkoutEmail');
            const checkoutAddress = document.getElementById('checkoutAddress');
            const checkoutPayment = document.getElementById('checkoutPayment');
            const checkoutNote = document.getElementById('checkoutNote');
            let appliedCoupon = null;
            let paymentConfig = {
                cod_enabled: true,
                online_enabled: false,
                upi_enabled: false,
                google_pay_enabled: false
            };

            function apiPost(url, payload) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    },
                    body: JSON.stringify(payload || {})
                });
            }

            function loadPaymentMethods() {
                fetch("<?= base_url('rest_api/payment/config') ?>", {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (response) {
                        if (!response || !response.status || !checkoutPayment) {
                            return;
                        }
                        paymentConfig = response;
                        checkoutPayment.innerHTML = '';
                        if (response.cod_enabled) {
                            const codOption = document.createElement('option');
                            codOption.value = 'cod';
                            codOption.textContent = 'Cash on Delivery';
                            checkoutPayment.appendChild(codOption);
                        }
                        if (response.online_enabled && response.upi_enabled) {
                            const onlineOption = document.createElement('option');
                            onlineOption.value = 'razorpay';
                            onlineOption.textContent = 'UPI / Google Pay';
                            checkoutPayment.appendChild(onlineOption);
                        }
                        if (!checkoutPayment.options.length) {
                            const unavailable = document.createElement('option');
                            unavailable.value = '';
                            unavailable.textContent = 'No payment method available';
                            checkoutPayment.appendChild(unavailable);
                        }
                    })
                    .catch(function () {
                        // Keep COD as the safe fallback if configuration cannot load.
                    });
            }

            function setCheckoutBusy(isBusy, text) {
                if (!submitCheckout) {
                    return;
                }
                submitCheckout.disabled = isBusy;
                submitCheckout.textContent = text || 'Place Order';
            }

            function readCart() {
                try {
                    const raw = localStorage.getItem(cartKey);
                    const parsed = raw ? JSON.parse(raw) : [];
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }

            function saveCart(items) {
                localStorage.setItem(cartKey, JSON.stringify(items || []));
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

            function saveOrders(items) {
                localStorage.setItem(ordersKey, JSON.stringify(items || []));
            }

            function currency(value) {
                const num = Number(value || 0);
                if (/[A-Za-z]$/.test(currencySymbol)) {
                    return currencySymbol + ' ' + num.toLocaleString();
                }
                return currencySymbol + num.toLocaleString();
            }

            function normalizeCouponCode(code) {
                return String(code || '').trim().toUpperCase();
            }

            function computeSubtotal(items) {
                return items.reduce(function (sum, item) {
                    return sum + (Number(item.price || 0) * Number(item.qty || 1));
                }, 0);
            }

            function setCouponMessage(type, text) {
                if (!couponMessage) {
                    return;
                }
                couponMessage.className = 'coupon-message' + (type ? (' ' + type) : '');
                couponMessage.textContent = text || '';
            }

            function setCouponUiState() {
                if (couponInput) {
                    couponInput.value = appliedCoupon && appliedCoupon.code ? appliedCoupon.code : '';
                }
                if (couponBadge) {
                    if (appliedCoupon && appliedCoupon.valid) {
                        couponBadge.style.display = 'inline-flex';
                        couponBadge.textContent = 'Applied ' + appliedCoupon.code;
                    } else {
                        couponBadge.style.display = 'none';
                        couponBadge.textContent = 'Coupon applied';
                    }
                }
                if (couponRemoveBtn) {
                    couponRemoveBtn.style.display = appliedCoupon && appliedCoupon.valid ? 'inline-flex' : 'none';
                }
            }

            function clearCouponState() {
                appliedCoupon = null;
                localStorage.removeItem(couponKey);
                setCouponUiState();
                setCouponMessage('', '');
            }

            function getAppliedDiscount(subtotal) {
                if (!appliedCoupon || !appliedCoupon.valid) {
                    return 0;
                }
                if (typeof appliedCoupon.subtotal === 'number' && Math.abs(appliedCoupon.subtotal - subtotal) < 0.01) {
                    return Number(appliedCoupon.discount || 0);
                }
                return 0;
            }

            function updateSummary(items) {
                const subtotal = computeSubtotal(items);
                const discount = getAppliedDiscount(subtotal);
                subtotalEl.textContent = currency(subtotal);
                shippingEl.textContent = currency(0);
                if (discountRow && discountEl) {
                    if (discount > 0) {
                        discountRow.style.display = 'flex';
                        discountEl.textContent = '-' + currency(discount);
                    } else {
                        discountRow.style.display = 'none';
                        discountEl.textContent = currency(0);
                    }
                }
                totalEl.textContent = currency(Math.max(subtotal - discount, 0));
                if (subtitle) {
                    subtitle.textContent = items.length > 0
                        ? items.length + ' item(s) in your cart.'
                        : 'Review your selected items.';
                }
            }

            function getCartCount(items) {
                return (items || []).reduce(function (sum, item) {
                    return sum + Number(item.qty || 1);
                }, 0);
            }

            function updateCartBadge(items) {
                if (!cartBadge) {
                    return;
                }
                const count = getCartCount(items);
                cartBadge.textContent = String(count);
                cartBadge.style.display = count > 0 ? 'inline-flex' : 'none';
            }

            function validateCoupon(code, options) {
                const normalized = normalizeCouponCode(code);
                const silent = options && options.silent === true;
                if (!normalized) {
                    if (!silent) {
                        setCouponMessage('error', 'Enter a coupon code to apply.');
                    }
                    return;
                }
                const items = readCart();
                if (items.length === 0) {
                    if (!silent) {
                        setCouponMessage('info', 'Add items to your cart before applying a coupon.');
                    }
                    return;
                }
                if (couponApplyBtn) {
                    couponApplyBtn.disabled = true;
                    couponApplyBtn.textContent = 'Applying...';
                }
                const payload = {
                    coupon_code: normalized,
                    items: items.map(function (item) {
                        return {
                            id: item.id,
                            qty: item.qty
                        };
                    })
                };
                apiPost("<?= base_url('rest_api/coupon') ?>", payload)
                    .then(function (res) { return res.json(); })
                    .then(function (response) {
                        if (couponApplyBtn) {
                            couponApplyBtn.disabled = false;
                            couponApplyBtn.textContent = 'Apply';
                        }
                        if (!response || !response.status) {
                            clearCouponState();
                            if (!silent) {
                                setCouponMessage('error', response?.message || 'Unable to apply coupon.');
                            }
                            return;
                        }
                        appliedCoupon = {
                            valid: true,
                            code: response.coupon?.code || normalized,
                            title: response.coupon?.title || '',
                            type: response.coupon?.type || 1,
                            value: Number(response.coupon?.value || 0),
                            min_order: Number(response.coupon?.min_order || 0),
                            max_discount: response.coupon?.max_discount ?? null,
                            discount: Number(response.discount || 0),
                            subtotal: Number(response.subtotal || 0),
                            total: Number(response.total || 0)
                        };
                        localStorage.setItem(couponKey, appliedCoupon.code);
                        setCouponUiState();
                        updateSummary(items);
                        if (!silent) {
                            setCouponMessage('success', response.message || 'Coupon applied.');
                        }
                    })
                    .catch(function () {
                        if (couponApplyBtn) {
                            couponApplyBtn.disabled = false;
                            couponApplyBtn.textContent = 'Apply';
                        }
                        if (!silent) {
                            setCouponMessage('error', 'Unable to apply coupon. Please try again.');
                        }
                    });
            }

            function refreshCouponIfNeeded(items) {
                if (!items || items.length === 0) {
                    clearCouponState();
                    return;
                }
                const saved = normalizeCouponCode(localStorage.getItem(couponKey) || '');
                if (!saved) {
                    return;
                }
                if (appliedCoupon && appliedCoupon.valid) {
                    const subtotal = computeSubtotal(items);
                    if (typeof appliedCoupon.subtotal === 'number' && Math.abs(appliedCoupon.subtotal - subtotal) < 0.01) {
                        return;
                    }
                }
                validateCoupon(saved, { silent: true });
            }

            function render() {
                const items = readCart();
                if (!list) return;
                if (items.length === 0) {
                    list.innerHTML = '';
                    if (empty) empty.style.display = 'block';
                    updateSummary([]);
                    updateCartBadge([]);
                    clearCouponState();
                    return;
                }
                if (empty) empty.style.display = 'none';

                list.innerHTML = items.map(function (item) {
                    const name = String(item.name || 'Product');
                    const imageUrl = String(item.image_url || '');
                    const price = Number(item.price || 0);
                    const qty = Number(item.qty || 1);
                    const total = price * qty;
                    return '' +
                        '<div class="cart-row" data-id="' + String(item.id || '') + '">' +
                            '<div class="thumb">' +
                                (imageUrl
                                    ? '<img src="' + imageUrl + '" alt="' + name.replace(/\"/g, '&quot;') + '">'
                                    : '<div class="fallback">' + name + '</div>') +
                            '</div>' +
                            '<div class="info">' +
                                '<p class="item-title">' + name + '</p>' +
                                '<p class="item-meta">Unit price ' + currency(price) + '</p>' +
                            '</div>' +
                            '<div class="qty">' +
                                '<input class="qty-input" type="number" min="1" value="' + qty + '">' +
                            '</div>' +
                            '<div class="price price-tag total">' + currency(total) + '</div>' +
                            '<div class="remove"><button class="icon-btn" type="button"><i class="fa-solid fa-trash"></i></button></div>' +
                        '</div>';
                }).join('');

                updateSummary(items);
                updateCartBadge(items);
                refreshCouponIfNeeded(items);
            }

            function updateQty(id, qty) {
                const items = readCart();
                const next = items.map(function (item) {
                    if (String(item.id) === String(id)) {
                        return Object.assign({}, item, { qty: qty });
                    }
                    return item;
                });
                saveCart(next);
                render();
            }

            function removeItem(id) {
                const items = readCart().filter(function (item) {
                    return String(item.id) !== String(id);
                });
                saveCart(items);
                render();
            }

            if (list) {
                list.addEventListener('input', function (event) {
                    const input = event.target;
                    if (!input.classList.contains('qty-input')) return;
                    const row = input.closest('.cart-row');
                    if (!row) return;
                    let qty = parseInt(input.value, 10);
                    if (!qty || qty < 1) qty = 1;
                    updateQty(row.getAttribute('data-id'), qty);
                });
                list.addEventListener('click', function (event) {
                    const btn = event.target.closest('.icon-btn');
                    if (!btn) return;
                    const row = btn.closest('.cart-row');
                    if (!row) return;
                    removeItem(row.getAttribute('data-id'));
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    saveCart([]);
                    render();
                });
            }

            if (couponApplyBtn) {
                couponApplyBtn.addEventListener('click', function () {
                    validateCoupon(couponInput ? couponInput.value : '');
                });
            }

            if (couponInput) {
                couponInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        validateCoupon(couponInput.value);
                    }
                });
            }

            if (couponRemoveBtn) {
                couponRemoveBtn.addEventListener('click', function () {
                    clearCouponState();
                    updateSummary(readCart());
                });
            }

            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function () {
                    const items = readCart();
                    if (items.length === 0) {
                        if (subtitle) {
                            subtitle.textContent = 'Your cart is empty. Add items to continue checkout.';
                        }
                        return;
                    }
                    if (checkoutModal) {
                        checkoutModal.style.display = 'flex';
                        checkoutModal.setAttribute('aria-hidden', 'false');
                    }
                });
            }

            function closeModal() {
                if (!checkoutModal) {
                    return;
                }
                checkoutModal.style.display = 'none';
                checkoutModal.setAttribute('aria-hidden', 'true');
            }

            if (closeCheckoutModal) {
                closeCheckoutModal.addEventListener('click', closeModal);
            }
            if (cancelCheckout) {
                cancelCheckout.addEventListener('click', closeModal);
            }
            if (checkoutModal) {
                checkoutModal.addEventListener('click', function (event) {
                    if (event.target === checkoutModal) {
                        closeModal();
                    }
                });
            }

            function completeOrder(response, payload) {
                const orders = readOrders();
                orders.unshift({
                    id: response.order_code || ('ODR-' + Date.now()),
                    date: response.date || new Date().toISOString().slice(0, 10),
                    status: 1,
                    items: response.items || [],
                    subtotal: response.subtotal || 0,
                    discount: response.discount || 0,
                    total: response.total || 0,
                    coupon: response.coupon || null,
                    payment_method: response.payment_method || payload.payment_method,
                    customer: response.customer || payload.customer
                });
                saveOrders(orders);
                saveCart([]);
                clearCouponState();
                render();
                closeModal();

                if (subtitle) {
                    subtitle.textContent = response.message || 'Order placed successfully.';
                }
                window.location.href = <?= json_encode(base_url('user/orders')) ?>;
            }

            function startRazorpayPayment(payload) {
                if (typeof window.Razorpay !== 'function') {
                    setCheckoutBusy(false, 'Place Order');
                    setCouponMessage('error', 'Payment checkout could not load. Please refresh and try again.');
                    return;
                }

                apiPost("<?= base_url('rest_api/payment/create') ?>", payload)
                    .then(function (res) { return res.json(); })
                    .then(function (response) {
                        if (!response || !response.status) {
                            setCheckoutBusy(false, 'Place Order');
                            setCouponMessage('error', response?.message || 'Unable to start payment.');
                            return;
                        }

                        const checkout = new Razorpay({
                            key: response.key_id,
                            amount: response.amount,
                            currency: response.currency,
                            name: response.name,
                            description: response.description,
                            order_id: response.order_id,
                            prefill: response.prefill || {},
                            theme: { color: response.theme_color || '#0f6cad' },
                            retry: { enabled: true },
                            modal: {
                                confirm_close: true,
                                ondismiss: function () {
                                    setCheckoutBusy(false, 'Place Order');
                                    setCouponMessage('info', 'Payment was cancelled. Your cart is unchanged.');
                                }
                            },
                            handler: function (paymentResponse) {
                                setCheckoutBusy(true, 'Verifying...');
                                apiPost("<?= base_url('rest_api/payment/verify') ?>", paymentResponse)
                                    .then(function (res) { return res.json(); })
                                    .then(function (verified) {
                                        if (!verified || !verified.status) {
                                            setCheckoutBusy(false, 'Place Order');
                                            setCouponMessage('error', verified?.message || 'Payment verification failed.');
                                            return;
                                        }
                                        completeOrder(verified, payload);
                                    })
                                    .catch(function () {
                                        setCheckoutBusy(false, 'Place Order');
                                        setCouponMessage('error', 'Payment verification failed. Contact support if money was debited.');
                                    });
                            }
                        });

                        checkout.on('payment.failed', function (failure) {
                            setCheckoutBusy(false, 'Place Order');
                            const reason = failure?.error?.description || 'Payment failed. Please try again.';
                            setCouponMessage('error', reason);
                        });
                        checkout.open();
                    })
                    .catch(function () {
                        setCheckoutBusy(false, 'Place Order');
                        setCouponMessage('error', 'Unable to start payment. Please try again.');
                    });
            }

            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const items = readCart();
                    if (items.length === 0) {
                        if (subtitle) {
                            subtitle.textContent = 'Your cart is empty. Add items to continue checkout.';
                        }
                        closeModal();
                        return;
                    }

                    const payload = {
                        items: items.map(function (item) {
                            return {
                                id: item.id,
                                qty: item.qty
                            };
                        }),
                        customer: {
                            name: checkoutName ? checkoutName.value.trim() : '',
                            phone: checkoutPhone ? checkoutPhone.value.trim() : '',
                            email: checkoutEmail ? checkoutEmail.value.trim() : '',
                            address: checkoutAddress ? checkoutAddress.value.trim() : '',
                            note: checkoutNote ? checkoutNote.value.trim() : ''
                        },
                        payment_method: checkoutPayment ? checkoutPayment.value : 'cod'
                    };

                    if (!payload.customer.name || !payload.customer.phone || !payload.customer.address) {
                        setCouponMessage('error', 'Please fill name, phone, and address to place your order.');
                        return;
                    }

                    if (appliedCoupon && appliedCoupon.code) {
                        payload.coupon_code = appliedCoupon.code;
                    }

                    setCheckoutBusy(true, payload.payment_method === 'razorpay' ? 'Opening Payment...' : 'Placing...');

                    if (payload.payment_method === 'razorpay') {
                        startRazorpayPayment(payload);
                        return;
                    }

                    apiPost("<?= base_url('rest_api/checkout') ?>", payload)
                        .then(function (res) { return res.json(); })
                        .then(function (response) {
                            setCheckoutBusy(false, 'Place Order');
                            if (!response || !response.status) {
                                setCouponMessage('error', response?.message || 'Checkout failed. Please try again.');
                                return;
                            }
                            completeOrder(response, payload);
                        })
                        .catch(function () {
                            setCheckoutBusy(false, 'Place Order');
                            setCouponMessage('error', 'Checkout failed. Please try again.');
                        });
                });
            }

            loadPaymentMethods();

            const storedCoupon = normalizeCouponCode(localStorage.getItem(couponKey) || '');
            if (couponInput && storedCoupon) {
                couponInput.value = storedCoupon;
            }
            setCouponUiState();
            render();
        })();
    </script>
</body>
</html>
