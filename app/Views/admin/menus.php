<style>
    .container {
        display: flex;
        gap: 16px;
        padding: 0 20px 20px;
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
    }

    .menu-toggle {
        display: none;
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 10px;
        background: var(--theme-color-dark);
        color: #fff;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(var(--theme-rgb), 0.24);
        margin: 0 0 10px 20px;
        cursor: pointer;
    }

    .menu-toggle i {
        width: 18px;
        height: 18px;
    }

    .sidebar {
        width: 260px;
        height: calc(100vh - 130px);
        max-height: calc(100vh - 130px);
        background: linear-gradient(180deg, var(--theme-color-darker) 0%, var(--theme-color-dark) 45%, var(--theme-color-light) 100%);
        border-radius: 16px;
        padding: 14px 10px;
        box-shadow: 0 12px 24px rgba(14, 65, 103, 0.22);
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.45) transparent;
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.4);
        border-radius: 999px;
    }

    .menu-header {
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.72rem;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        font-weight: 700;
        margin: 4px 10px 10px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar li {
        margin: 4px 0;
    }

    .sidebar li a,
    .sidebar li .menu-text {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        color: #e8f4fc;
        text-decoration: none;
        font-size: 0.93rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .sidebar li i {
        width: 17px;
        height: 17px;
        stroke-width: 2.2;
        flex-shrink: 0;
    }

    .sidebar li a:hover,
    .sidebar li .menu-text:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
    }

    .sidebar li.active a {
        background: #ffffff;
        color: var(--theme-color-dark);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.14);
    }

    .sidebar li.active a i {
        stroke: var(--theme-color-dark);
    }

    .menu-section {
        margin-top: 12px;
    }

    .main {
        flex: 1;
        min-width: 0;
        padding: 0;
    }

    @media (max-width: 992px) {
        .menu-toggle {
            display: inline-flex;
        }

        .container {
            padding: 0 12px 14px;
        }

        .sidebar {
            position: fixed;
            top: 96px;
            left: 12px;
            z-index: 1050;
            width: 250px;
            height: calc(100vh - 108px);
            max-height: calc(100vh - 108px);
            transform: translateX(-120%);
            transition: transform 0.24s ease;
        }

        .sidebar.open {
            transform: translateX(0);
        }
    }
</style>

<button class="menu-toggle" type="button" id="menuToggle" aria-label="Open menu">
    <i data-lucide="menu"></i>
</button>

<div class="container">
    <div class="sidebar" id="sidebarMenu">
        <div class="menu-header">Main Navigation</div>
        <ul>
            <li class="<?= ($page == 'dashboard') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/dashboard') ?>">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="<?= ($page == 'orders') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/orders') ?>">
                    <i data-lucide="shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
        </ul>

        <div class="menu-header menu-section">Product Menus</div>
        <ul>
            <li class="<?= ($page == 'products') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/products') ?>">
                    <i data-lucide="package"></i>
                    <span>Product List</span>
                </a>
            </li>
            <li class="<?= ($page == 'categories') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/categories') ?>">
                    <i data-lucide="layers"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="<?= ($page == 'brands') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/brands') ?>">
                    <i data-lucide="tag"></i>
                    <span>Brands</span>
                </a>
            </li>
            <li class="<?= ($page == 'banners') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/banners') ?>">
                    <i data-lucide="image"></i>
                    <span>Product Banners</span>
                </a>
            </li>
            <li class="<?= ($page == 'coupons') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/coupons') ?>">
                    <i data-lucide="ticket-percent"></i>
                    <span>Product Coupons</span>
                </a>
            </li>
        </ul>

        <div class="menu-header menu-section">Users & Operations</div>
        <ul>
            <li class="<?= ($page == 'sellers') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/sellers') ?>">
                    <i data-lucide="store"></i>
                    <span>Sellers</span>
                </a>
            </li>
            <li class="<?= ($page == 'customers') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/customers') ?>">
                    <i data-lucide="users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="<?= ($page == 'delivery') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/delivery') ?>">
                    <i data-lucide="truck"></i>
                    <span>Delivery</span>
                </a>
            </li>
            <li class="<?= ($page == 'payments') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/payments') ?>">
                    <i data-lucide="indian-rupee"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="<?= ($page == 'billing') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/billing') ?>">
                    <i data-lucide="receipt-text"></i>
                    <span>Billing</span>
                </a>
            </li>
            <li class="<?= ($page == 'returns') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/returns') ?>">
                    <i data-lucide="rotate-ccw"></i>
                    <span>Returns</span>
                </a>
            </li>
            <li class="<?= ($page == 'reports') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/reports') ?>">
                    <i data-lucide="bar-chart-3"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="<?= ($page == 'settings') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/settings') ?>">
                    <i data-lucide="settings"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="<?= ($page == 'dashboard_template') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/dashboard-template') ?>">
                    <i data-lucide="layout-template"></i>
                    <span>Dashboard Template</span>
                </a>
            </li>
            <li class="<?= ($page == 'admins') ? 'active' : '' ?>">
                <a href="<?= base_url('admin/admins') ?>">
                    <i data-lucide="shield"></i>
                    <span>Admins</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main">

<script>
    (function () {
        const toggleBtn = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebarMenu');

        if (!toggleBtn || !sidebar) return;

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', function (event) {
            if (window.innerWidth > 992) return;
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    })();
</script>
