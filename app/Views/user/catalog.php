<?php
$productsList = is_array($products ?? null) ? $products : [];
$categoryList = is_array($categories ?? null) ? $categories : [];
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$currencySymbol = trim((string) ($currencySymbol ?? '$'));
$currencySpacer = preg_match('/[A-Za-z]$/', $currencySymbol) ? ' ' : '';
$productCount = count($productsList);
$template = is_array($dashboardTemplate ?? null) ? $dashboardTemplate : [];
$templateDesign = is_array($template['design'] ?? null) ? $template['design'] : [];
$templateLayers = is_array($template['layers'] ?? null) ? $template['layers'] : [];
$templateNav = is_array($template['nav'] ?? null) ? $template['nav'] : [];

$designValue = static function (string $key, string $fallback) use ($templateDesign): string {
    $value = trim((string) ($templateDesign[$key] ?? ''));
    return $value !== '' ? $value : $fallback;
};
$navLabel = static function (string $key, string $fallback) use ($templateNav): string {
    $value = trim((string) ($templateNav[$key] ?? ''));
    return $value !== '' ? $value : $fallback;
};

$showTemplateHeader = ($templateLayers['header'] ?? true) !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: <?= esc($designValue('page_bg', '#f2f3f5'), 'css') ?>;
            --surface: <?= esc($designValue('surface', '#ffffff'), 'css') ?>;
            --ink: <?= esc($designValue('text_color', '#1e2633'), 'css') ?>;
            --muted: <?= esc($designValue('muted_text', '#6a7282'), 'css') ?>;
            --line: #dde2ea;
            --accent: <?= esc($designValue('accent', '#f15a3b'), 'css') ?>;
            --font-main: <?= esc($designValue('font_family', '"Nunito Sans", sans-serif'), 'css') ?>;
            --content-max: <?= esc($designValue('content_max_width', '1400px'), 'css') ?>;
            --radius-lg: <?= esc($designValue('radius_lg', '22px'), 'css') ?>;
            --radius-md: <?= esc($designValue('radius_md', '16px'), 'css') ?>;
            --shadow-soft: <?= esc($designValue('shadow_soft', '0 14px 34px rgba(25, 41, 72, 0.09)'), 'css') ?>;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: var(--font-main);
            background: var(--bg);
            color: var(--ink);
        }
        .container {
            width: 100%;
            max-width: var(--content-max);
            margin: 0 auto;
            padding: 16px;
        }
        .top-nav {
            background: color-mix(in srgb, var(--surface) 94%, transparent);
            backdrop-filter: blur(8px);
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
            box-shadow: var(--shadow-soft);
        }
        .brand img {
            height: 38px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
            display: block;
        }
        .brand {
            color: #2a2d3b;
            font-family: "Baloo 2", cursive;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 14px;
            font-family: var(--font-main);
            font-size: 0.86rem;
            font-weight: 700;
        }
        .nav-links a {
            color: #2a3750;
            font-family: inherit;
            font-size: inherit;
            text-decoration: none;
            transition: color .2s ease, background-color .2s ease;
        }
        .nav-links > a:not(.nav-btn):not(.nav-profile):hover,
        .nav-links > a[aria-current="page"] {
            color: var(--accent);
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
            border-radius: var(--radius-md);
            padding: 8px 14px;
            border: 1px solid #d6dce6;
            background: var(--surface);
            font-weight: 800;
        }
        .nav-btn.primary {
            border-color: var(--accent);
            background: var(--accent);
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
        .nav-btn.primary {
            background: linear-gradient(135deg, #7b2cff, #a022f0);
            border-color: #7b2cff;
            color: #fff;
        }
        .catalog-layout {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 290px 1fr;
            gap: 16px;
        }
        .sidebar {
            position: sticky;
            top: 16px;
            background: #fff;
            border: 1px solid #d9dee6;
            border-radius: 18px;
            padding: 16px;
            align-self: start;
            box-shadow: 0 10px 28px rgba(25, 41, 72, .07);
        }
        .filter-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .filter-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
        }
        .filter-close { display: none; width: 34px; height: 34px; border: 0; border-radius: 9px; background: #eef3f8; color: #26364d; }
        .clear-filters { width: 100%; margin-top: 14px; border: 1px solid #d6dce6; border-radius: 10px; padding: 9px 12px; background: #f7f9fc; color: #34445a; font-weight: 800; cursor: pointer; }
        .filter-block {
            border-top: 1px solid #d9dee6;
            padding-top: 12px;
            margin-top: 12px;
        }
        .filter-head {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .cat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 0.92rem;
            margin-bottom: 8px;
            color: #333e52;
        }
        .cat-item label {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .price-range {
            width: 100%;
        }
        .color-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .dot {
            width: 16px;
            height: 16px;
            border-radius: 999px;
            border: 1px solid #ccd3df;
        }
        .content {
            min-width: 0;
        }
        .content-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .catalog-tools { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
        .catalog-search {
            min-width: min(330px, 70vw);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 11px;
            border: 1px solid #d5dde8;
            border-radius: 11px;
            background: #fff;
        }
        .catalog-search input { width: 100%; min-height: 40px; border: 0; outline: 0; background: transparent; font: inherit; }
        .filter-toggle { display: none; min-height: 40px; border: 1px solid #d5dde8; border-radius: 10px; padding: 8px 12px; background: #fff; color: #27374d; font-weight: 800; }
        .results {
            font-size: 0.95rem;
            color: #2f394b;
            font-weight: 700;
        }
        .sort {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
        }
        .sort select {
            border: 1px solid #d5dde8;
            border-radius: 8px;
            padding: 6px 8px;
            background: #fff;
        }
        .chips {
            margin-top: 8px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .chip {
            border: 1px solid #252e3e;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 12px;
            background: #fff;
            cursor: pointer;
        }
        .grid {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .card {
            background: #fff;
            border: 1px solid #dbe1ea;
            border-radius: 16px;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .card:hover { transform: translateY(-4px); border-color: #bfd8ec; box-shadow: 0 14px 30px rgba(25, 73, 113, .12); }
        .thumb {
            background: #eceef2;
            aspect-ratio: 4 / 4;
            display: grid;
            place-items: center;
            position: relative;
        }
        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .3s ease;
        }
        .card:hover .thumb img { transform: scale(1.035); }
        .thumb .fallback {
            color: #637086;
            font-weight: 800;
            padding: 10px;
            text-align: center;
        }
        .card-body {
            padding: 10px 10px 12px;
        }
        .name-price {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }
        .name {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #232d3d;
            line-height: 1.25;
            text-transform: none;
        }
        .product-category { margin: 0 0 5px; color: #7a8698; font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .45px; }
        .stock-badge { position: absolute; right: 10px; top: 10px; padding: 4px 8px; border-radius: 999px; color: #146c3b; background: #e5f7ec; font-size: .68rem; font-weight: 800; }
        .stock-badge.out { color: #a52a32; background: #fdebed; }
        .price {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 800;
            color: #6a6255;
            white-space: nowrap;
        }
        .price-group {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }
        .price-current {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: #1b7cc8;
            white-space: nowrap;
        }
        .price-original {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 700;
            color: #8a93a3;
            text-decoration: line-through;
            white-space: nowrap;
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #f15a3b;
            color: #fff;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 4px 8px;
            border-radius: 999px;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 14px rgba(241, 90, 59, 0.35);
        }
        .colors {
            margin-top: 8px;
            display: flex;
            gap: 6px;
        }
        .card-actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 8px 12px;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
        }
        .btn.primary {
            background: #1b7cc8;
            color: #fff;
        }
        .btn:disabled { opacity: .55; cursor: not-allowed; }
        .btn.ghost {
            background: #f3f6fb;
            border: 1px solid #d6dce6;
            color: #243148;
        }
        .empty {
            margin-top: 12px;
            background: #fff;
            border: 1px dashed #cfd7e4;
            border-radius: 12px;
            padding: 22px;
            text-align: center;
            color: #606b80;
            font-weight: 700;
        }
        .catalog-loading { display: none; padding: 34px 15px; text-align: center; color: #607086; font-weight: 800; }
        .catalog-loading.show { display: block; }
        .catalog-pagination { margin-top: 18px; display: flex; align-items: center; justify-content: center; gap: 7px; flex-wrap: wrap; }
        .page-btn { min-width: 38px; height: 38px; border: 1px solid #d5dde8; border-radius: 9px; background: #fff; color: #34445a; font-weight: 800; cursor: pointer; }
        .page-btn.active { color: #fff; border-color: #1b7cc8; background: #1b7cc8; }
        .page-btn:disabled { opacity: .45; cursor: not-allowed; }
        .filter-overlay { display: none; position: fixed; inset: 0; z-index: 90; background: rgba(17, 31, 48, .48); }
        .cart-toast { position: fixed; right: 18px; bottom: 18px; z-index: 120; display: none; align-items: center; gap: 9px; padding: 11px 14px; border-radius: 11px; color: #fff; background: #172033; box-shadow: 0 12px 28px rgba(0,0,0,.2); font-weight: 800; }
        @media (max-width: 1100px) {
            .catalog-layout {
                grid-template-columns: 1fr;
            }
            .grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .filter-toggle { display: inline-flex; align-items: center; gap: 7px; }
            .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; width: min(330px, 88vw); border-radius: 0 18px 18px 0; overflow-y: auto; transform: translateX(-105%); transition: transform .22s ease; }
            .sidebar.open { transform: translateX(0); }
            .filter-close { display: inline-grid; place-items: center; }
            .filter-overlay.open { display: block; }
        }
        @media (max-width: 700px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .nav-links {
                gap: 10px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .top-nav {
                align-items: flex-start;
                flex-direction: column;
            }
            .catalog-tools, .catalog-search { width: 100%; min-width: 0; }
            .sort { width: 100%; justify-content: space-between; }
            .sort select { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="top-nav"<?= $showTemplateHeader ? '' : ' hidden' ?>>
            <a class="brand" href="<?= base_url('user/dashboard') ?>" aria-label="<?= esc($websiteName) ?>">
                <?php if ($logoUrl !== '') : ?>
                    <img src="<?= esc($logoUrl) ?>" alt="<?= esc($websiteName) ?>">
                <?php else : ?>
                    <?= esc($websiteName) ?>
                <?php endif; ?>
            </a>
            <nav class="nav-links">
                <a href="<?= base_url('user/dashboard') ?>"><?= esc($navLabel('home', 'Home')) ?></a>
                <a href="<?= base_url('user/catalog') ?>" aria-current="page"><?= esc($navLabel('courses', 'Products')) ?></a>
                <a href="<?= base_url('user/contact') ?>"><?= esc($navLabel('contacts', 'Contact')) ?></a>
                <a href="<?= base_url('user/about') ?>"><?= esc($navLabel('about', 'About')) ?></a>
                <a href="<?= base_url('user/profile') ?>">Profile</a>
                <a class="cart-link" href="<?= base_url('user/cart') ?>" aria-label="Cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </a>
                <?= view('user/partials/notification_bell') ?>
                <a id="navRegister" class="nav-btn primary" href="<?= base_url('register') ?>"><?= esc($navLabel('register', 'Register')) ?></a>
                <a id="navLogin" class="nav-btn" href="<?= base_url('login') ?>"><?= esc($navLabel('login', 'Login')) ?></a>
                <a id="navProfile" class="nav-profile" href="<?= base_url('user/profile') ?>">
                    <img id="navProfileImage" src="<?= esc(base_url('uploads/customers/default-user.svg')) ?>" alt="Profile">
                    <span id="navProfileName">Profile</span>
                </a>
                <a id="navLogout" class="nav-btn" href="<?= base_url('logout') ?>" style="display:none;">Logout</a>
            </nav>
        </header>

        <div class="filter-overlay" id="filterOverlay"></div>
        <main class="catalog-layout">
            <aside class="sidebar" id="catalogSidebar">
                <div class="filter-bar">
                    <h2 class="filter-title">Filter Products</h2>
                    <button class="filter-close" id="filterClose" type="button" aria-label="Close filters"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <section class="filter-block" style="border-top:none; margin-top:0; padding-top:0;">
                    <div class="filter-head">Category</div>
                    <div id="catalogCategoryList">
                        <?php if (!empty($categoryList)) : ?>
                            <?php foreach ($categoryList as $index => $category) : ?>
                                <?php $catId = (string) ($category['id'] ?? $index + 1); ?>
                                <div class="cat-item">
                                    <label>
                                        <input type="checkbox" class="filter-category" value="<?= esc($catId) ?>">
                                        <span><?= esc((string) ($category['name'] ?? '')) ?></span>
                                    </label>
                                    <span><?= esc((string) ($category['total'] ?? 0)) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="cat-item">No categories available.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="filter-block">
                    <div class="filter-head">Price</div>
                    <input class="price-range" id="priceRange" type="range" min="0" max="500" value="500">
                    <div class="cat-item"><span>From</span><strong id="priceMinLabel"><?= esc($currencySymbol) ?>0</strong></div>
                    <div class="cat-item"><span>To</span><strong id="priceMaxLabel"><?= esc($currencySymbol) ?>500</strong></div>
                    <div class="cat-item">
                        <label>
                            <input id="priceMinInput" type="number" min="0" step="1" style="width:92px;">
                        </label>
                        <label>
                            <input id="priceMaxInput" type="number" min="0" step="1" style="width:92px;">
                        </label>
                    </div>
                </section>

                <button class="clear-filters" id="clearFilters" type="button">Clear all filters</button>
            </aside>

            <section class="content">
                <div class="content-top">
                    <div>
                        <button class="filter-toggle" id="filterToggle" type="button"><i class="fa-solid fa-sliders"></i> Filters</button>
                        <div class="results" id="resultsText">Showing <?= esc((string) $productCount) ?> products</div>
                    </div>
                    <div class="catalog-tools">
                        <label class="catalog-search" for="catalogSearch"><i class="fa-solid fa-magnifying-glass"></i><input id="catalogSearch" type="search" placeholder="Search products..." autocomplete="off"></label>
                        <div class="sort">
                            <span>Sort by</span>
                            <select id="sortSelect">
                                <option value="newest">Newest</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="name_asc">Name: A-Z</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="chips" id="activeChips"></div>

                <div class="grid" id="catalogGrid">
                    <?php if (!empty($productsList)) : ?>
                        <?php foreach ($productsList as $product) : ?>
                            <?php
                            $name = trim((string) ($product['pr_name'] ?? 'Product'));
                            $priceValue = (float) ($product['pr_price'] ?? 0);
                            $discountValue = (float) ($product['pr_discount'] ?? 0);
                            if ($discountValue < 0) {
                                $discountValue = 0;
                            }
                            if ($discountValue > 100) {
                                $discountValue = 100;
                            }
                            $discountedPrice = $discountValue > 0
                                ? $priceValue - ($priceValue * ($discountValue / 100))
                                : $priceValue;
                            $price = $currencySymbol . $currencySpacer . number_format($priceValue, 0);
                            $priceAfter = $currencySymbol . $currencySpacer . number_format($discountedPrice, 0);
                            $image = trim((string) ($product['pr_image'] ?? ''));
                            $imageUrl = $image !== '' ? base_url('uploads/products/' . $image) : '';
                            $stock = (int) ($product['pr_stock'] ?? 0);
                            ?>
                            <article class="card">
                                <div class="thumb">
                                    <?php if ($discountValue > 0) : ?>
                                        <span class="discount-badge">-<?= esc(rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.')) ?>%</span>
                                    <?php endif; ?>
                                    <span class="stock-badge <?= $stock <= 0 ? 'out' : '' ?>"><?= $stock > 0 ? esc($stock . ' in stock') : 'Out of stock' ?></span>
                                    <?php if ($imageUrl !== '') : ?>
                                        <img src="<?= esc($imageUrl) ?>" alt="<?= esc($name) ?>">
                                    <?php else : ?>
                                        <div class="fallback"><?= esc($name) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="name-price">
                                        <h3 class="name"><?= esc($name) ?></h3>
                                        <?php if ($discountValue > 0) : ?>
                                            <div class="price-group">
                                                <p class="price-current"><?= esc($priceAfter) ?></p>
                                                <p class="price-original"><?= esc($price) ?></p>
                                            </div>
                                        <?php else : ?>
                                            <p class="price"><?= esc($price) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-actions">
                                        <a class="btn ghost" href="<?= base_url('user/product/' . (int) ($product['id'] ?? 0)) ?>">View</a>
                                        <button
                                            class="btn primary add-to-cart-btn"
                                            type="button"
                                            data-id="<?= esc((string) ($product['id'] ?? 0)) ?>"
                                            data-name="<?= esc($name) ?>"
                                            data-price="<?= esc((string) $discountedPrice) ?>"
                                            data-image="<?= esc($imageUrl) ?>"
                                            <?= $stock <= 0 ? 'disabled' : '' ?>
                                        ><?= $stock <= 0 ? 'Out of Stock' : 'Add to Cart' ?></button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="catalog-loading" id="catalogLoading"><i class="fa-solid fa-circle-notch fa-spin"></i> Loading products...</div>
                <div class="empty" id="catalogEmpty" style="<?= empty($productsList) ? '' : 'display:none;' ?>">No products match the selected filters.</div>
                <nav class="catalog-pagination" id="catalogPagination" aria-label="Catalog pages"></nav>
            </section>
        </main>

        <?= view('user/partials/footer', ['websiteName' => $websiteName]) ?>
    </div>
    <div class="cart-toast" id="cartToast"><i class="fa-solid fa-circle-check"></i><span>Added to cart</span></div>
    <script>
        (function () {
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;

            function apiGet(url, signal) {
                return fetch(url, {
                    method: 'GET',
                    signal: signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                });
            }

            apiGet("<?= base_url('rest_api/session') ?>")
                .then(function (res) {
                    return res.json();
                })
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
                .catch(function () {
                    // keep default nav controls
                });
        })();

        (function () {
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            const categoriesWrap = document.getElementById('catalogCategoryList');
            const grid = document.getElementById('catalogGrid');
            const emptyState = document.getElementById('catalogEmpty');
            const resultsText = document.getElementById('resultsText');
            const chipsWrap = document.getElementById('activeChips');
            const sortSelect = document.getElementById('sortSelect');
            const priceRange = document.getElementById('priceRange');
            const priceMinLabel = document.getElementById('priceMinLabel');
            const priceMaxLabel = document.getElementById('priceMaxLabel');
            const priceMinInput = document.getElementById('priceMinInput');
            const priceMaxInput = document.getElementById('priceMaxInput');
            const searchInput = document.getElementById('catalogSearch');
            const pagination = document.getElementById('catalogPagination');
            const loading = document.getElementById('catalogLoading');
            const sidebar = document.getElementById('catalogSidebar');
            const filterToggle = document.getElementById('filterToggle');
            const filterClose = document.getElementById('filterClose');
            const filterOverlay = document.getElementById('filterOverlay');
            const clearFilters = document.getElementById('clearFilters');
            const cartToast = document.getElementById('cartToast');
            const cartKey = 'cart_items';
            const cartBadge = document.getElementById('cartBadge');
            const pageSize = 12;
            let currentPage = 1;
            let totalProducts = 0;
            let activeRequest = null;
            let catalogPriceMin = 0;
            let catalogPriceMax = 0;

            function apiGet(url, signal) {
                return fetch(url, {
                    method: 'GET',
                    signal: signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                });
            }

            function syncUrl() {
                const params = new URLSearchParams(buildQuery());
                params.delete('limit');
                const nextUrl = new URL(window.location.href);
                nextUrl.search = params.toString();
                history.replaceState(null, '', nextUrl);
            }

            function renderPagination() {
                if (!pagination) return;
                const pageCount = Math.ceil(totalProducts / pageSize);
                if (pageCount <= 1) {
                    pagination.innerHTML = '';
                    return;
                }
                const start = Math.max(1, currentPage - 2);
                const end = Math.min(pageCount, currentPage + 2);
                const buttons = ['<button class="page-btn" data-page="' + (currentPage - 1) + '"' + (currentPage === 1 ? ' disabled' : '') + ' aria-label="Previous page">&lsaquo;</button>'];
                for (let page = start; page <= end; page++) {
                    buttons.push('<button class="page-btn' + (page === currentPage ? ' active' : '') + '" data-page="' + page + '">' + page + '</button>');
                }
                buttons.push('<button class="page-btn" data-page="' + (currentPage + 1) + '"' + (currentPage === pageCount ? ' disabled' : '') + ' aria-label="Next page">&rsaquo;</button>');
                pagination.innerHTML = buttons.join('');
            }

            function closeFilters() {
                sidebar?.classList.remove('open');
                filterOverlay?.classList.remove('open');
                document.body.style.overflow = '';
            }

            function resetFilters() {
                categoriesWrap?.querySelectorAll('.filter-category').forEach(function (input) { input.checked = false; });
                if (searchInput) searchInput.value = '';
                if (sortSelect) sortSelect.value = 'newest';
                if (priceMinInput) priceMinInput.value = String(catalogPriceMin);
                if (priceMaxInput) priceMaxInput.value = String(catalogPriceMax);
                if (priceRange) priceRange.value = String(catalogPriceMax);
                applyPriceLabels(catalogPriceMin, catalogPriceMax);
                currentPage = 1;
                fetchProducts();
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

            function addToCart(item) {
                if (!item || !item.id) {
                    return;
                }
                const items = readCart();
                const existing = items.find(function (row) { return String(row.id) === String(item.id); });
                if (existing) {
                    existing.qty = Number(existing.qty || 1) + 1;
                } else {
                    items.push({
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        image_url: item.image_url,
                        qty: 1
                    });
                }
                saveCart(items);
                updateCartBadge();
                if (cartToast) {
                    cartToast.style.display = 'flex';
                    clearTimeout(cartToast.hideTimer);
                    cartToast.hideTimer = setTimeout(function () { cartToast.style.display = 'none'; }, 1800);
                }
            }

            function getCartCount(items) {
                return (items || []).reduce(function (sum, item) {
                    return sum + Number(item.qty || 1);
                }, 0);
            }

            function updateCartBadge() {
                if (!cartBadge) {
                    return;
                }
                const items = readCart();
                const count = getCartCount(items);
                cartBadge.textContent = String(count);
                cartBadge.style.display = count > 0 ? 'inline-flex' : 'none';
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getSelectedCategories() {
                if (!categoriesWrap) {
                    return [];
                }
                const checked = categoriesWrap.querySelectorAll('input.filter-category:checked');
                return Array.from(checked)
                    .map(function (input) { return String(input.value || '').trim(); })
                    .filter(function (value) { return value !== ''; });
            }

            function renderCategories(items) {
                if (!categoriesWrap) {
                    return;
                }

                const rows = Array.isArray(items) ? items : [];
                if (rows.length === 0) {
                    return;
                }

                const selectedFromUrl = new URLSearchParams(window.location.search).get('categories');
                const selectedIds = selectedFromUrl ? selectedFromUrl.split(',') : [];
                categoriesWrap.innerHTML = rows.map(function (category) {
                    const id = String(category?.id || '').trim();
                    const name = String(category?.name || '').trim();
                    const total = String(category?.total ?? 0);
                    if (id === '' || name === '') {
                        return '';
                    }
                    return '' +
                        '<div class="cat-item">' +
                            '<label>' +
                                '<input type="checkbox" class="filter-category" value="' + escapeHtml(id) + '"' + (selectedIds.includes(id) ? ' checked' : '') + '>' +
                                '<span>' + escapeHtml(name) + '</span>' +
                            '</label>' +
                            '<span>' + escapeHtml(total) + '</span>' +
                        '</div>';
                }).join('');
            }

            function renderProducts(items) {
                if (!grid) {
                    return;
                }
                const rows = Array.isArray(items) ? items : [];
                if (rows.length === 0) {
                    grid.innerHTML = '';
                    if (emptyState) {
                        emptyState.style.display = 'block';
                    }
                    return;
                }

                if (emptyState) {
                    emptyState.style.display = 'none';
                }

                grid.innerHTML = rows.map(function (product) {
                    const id = Number(product?.id || 0);
                    const name = String(product?.name || 'Product').trim() || 'Product';
                    const price = Number(product?.price || 0);
                    const discount = Number(product?.discount || 0);
                    const stock = Number(product?.stock || 0);
                    const category = String(product?.category || '').trim();
                    const imageUrl = String(product?.image_url || '').trim();
                    const discountSafe = Math.min(Math.max(discount, 0), 100);
                    const discountedPrice = discountSafe > 0
                        ? price - (price * (discountSafe / 100))
                        : price;
                    const priceText = formatPrice(price);
                    const priceAfterText = formatPrice(discountedPrice);
                    const href = <?= json_encode(base_url('user/product')) ?> + '/' + id;
                    const discountBadge = discountSafe > 0
                        ? '<span class="discount-badge">-' + escapeHtml(String(discountSafe % 1 === 0 ? discountSafe.toFixed(0) : discountSafe.toFixed(2)).replace(/\.0+$/, '')) + '%</span>'
                        : '';
                    const priceMarkup = discountSafe > 0
                        ? '<div class="price-group">' +
                            '<p class="price-current">' + escapeHtml(priceAfterText) + '</p>' +
                            '<p class="price-original">' + escapeHtml(priceText) + '</p>' +
                        '</div>'
                        : '<p class="price">' + escapeHtml(priceText) + '</p>';
                    const stockMarkup = '<span class="stock-badge' + (stock <= 0 ? ' out' : '') + '">' + (stock > 0 ? escapeHtml(stock + ' in stock') : 'Out of stock') + '</span>';
                    const cartButton = stock > 0
                        ? '<button class="btn primary add-to-cart-btn" type="button" data-id="' + id + '" data-name="' + escapeHtml(name) + '" data-price="' + discountedPrice + '" data-image="' + escapeHtml(imageUrl) + '">Add to Cart</button>'
                        : '<button class="btn primary" type="button" disabled>Out of Stock</button>';

                    return '' +
                        '<article class="card">' +
                            '<div class="thumb">' +
                                discountBadge +
                                stockMarkup +
                                (imageUrl
                                    ? '<img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(name) + '">'
                                    : '<div class="fallback">' + escapeHtml(name) + '</div>') +
                            '</div>' +
                            '<div class="card-body">' +
                                (category ? '<p class="product-category">' + escapeHtml(category) + '</p>' : '') +
                                '<div class="name-price">' +
                                    '<h3 class="name">' + escapeHtml(name) + '</h3>' +
                                    priceMarkup +
                                '</div>' +
                                '<div class="card-actions">' +
                                    '<a class="btn ghost" href="' + escapeHtml(href) + '">View</a>' +
                                    cartButton +
                                '</div>' +
                            '</div>' +
                        '</article>';
                }).join('');
            }

            function updateResults(total, shown) {
                if (!resultsText) {
                    return;
                }
                const start = total > 0 ? ((currentPage - 1) * pageSize) + 1 : 0;
                const end = Math.min((currentPage - 1) * pageSize + shown, total);
                resultsText.textContent = total > 0 ? 'Showing ' + start + '-' + end + ' of ' + total + ' products' : 'No products found';
            }

            function updateChips() {
                if (!chipsWrap) {
                    return;
                }
                const selected = getSelectedCategories();
                const chips = [];
                selected.forEach(function (id) {
                    const labelNode = categoriesWrap
                        ? categoriesWrap.querySelector('input.filter-category[value="' + id + '"] + span')
                        : null;
                    const label = labelNode ? labelNode.textContent.trim() : '';
                    if (label !== '') {
                        chips.push('<button class="chip" type="button" data-remove-category="' + escapeHtml(id) + '">' + escapeHtml(label) + ' &times;</button>');
                    }
                });

                const minValue = priceMinInput && priceMinInput.value !== '' ? Number(priceMinInput.value) : null;
                const maxValue = priceMaxInput && priceMaxInput.value !== '' ? Number(priceMaxInput.value) : null;
                if ((minValue !== null && minValue > catalogPriceMin) || (maxValue !== null && maxValue < catalogPriceMax)) {
                    const minText = minValue !== null ? minValue : 0;
                    const maxText = maxValue !== null ? maxValue : '';
                    chips.push('<button class="chip" type="button" data-remove-price="1">' + escapeHtml(formatPrice(minText)) + ' - ' + escapeHtml(formatPrice(maxText)) + ' &times;</button>');
                }
                const search = searchInput ? searchInput.value.trim() : '';
                if (search) chips.push('<button class="chip" type="button" data-remove-search="1">Search: ' + escapeHtml(search) + ' &times;</button>');

                chipsWrap.innerHTML = chips.length > 0 ? chips.join('') : '';
            }

            function applyPriceLabels(minPrice, maxPrice) {
                if (priceMinLabel) {
                    priceMinLabel.textContent = formatPrice(Math.round(minPrice));
                }
                if (priceMaxLabel) {
                    priceMaxLabel.textContent = formatPrice(Math.round(maxPrice));
                }
            }

            function formatPrice(value) {
                const amount = Number(value || 0).toLocaleString();
                if (/[A-Za-z]$/.test(currencySymbol)) {
                    return currencySymbol + ' ' + amount;
                }
                return currencySymbol + amount;
            }

            function syncPriceInputs(minPrice, maxPrice) {
                catalogPriceMin = Math.floor(minPrice);
                catalogPriceMax = Math.ceil(maxPrice);
                if (priceRange) {
                    priceRange.min = String(catalogPriceMin);
                    priceRange.max = String(catalogPriceMax);
                    priceRange.value = String(catalogPriceMax);
                }
                if (priceMinInput) {
                    priceMinInput.value = String(catalogPriceMin);
                }
                if (priceMaxInput) {
                    priceMaxInput.value = String(catalogPriceMax);
                }
                applyPriceLabels(minPrice, maxPrice);
            }

            function restoreUrlState() {
                const params = new URLSearchParams(window.location.search);
                if (searchInput) searchInput.value = params.get('search') || '';
                if (sortSelect && params.get('sort')) sortSelect.value = params.get('sort');
                currentPage = Math.max(1, Number(params.get('page') || 1));
                if (priceMinInput && params.has('min_price')) priceMinInput.value = params.get('min_price');
                if (priceMaxInput && params.has('max_price')) {
                    priceMaxInput.value = params.get('max_price');
                    if (priceRange) priceRange.value = params.get('max_price');
                }
                applyPriceLabels(Number(priceMinInput?.value || catalogPriceMin), Number(priceMaxInput?.value || catalogPriceMax));
            }

            function fetchFilters() {
                apiGet("<?= base_url('rest_api/catalog-filters') ?>")
                    .then(function (res) { return res.json(); })
                    .then(function (payload) {
                        if (!payload || !payload.status) {
                            return;
                        }
                        const filters = payload.filters || {};
                        if (Array.isArray(filters.categories) && filters.categories.length > 0) {
                            renderCategories(filters.categories);
                        }
                        const minPrice = Number(filters.price_min || 0);
                        const maxPrice = Number(filters.price_max || 0);
                        if (maxPrice > 0) {
                            syncPriceInputs(minPrice, maxPrice);
                        }
                        restoreUrlState();
                        bindCategoryEvents();
                        fetchProducts();
                    })
                    .catch(function () {
                        bindCategoryEvents();
                        fetchProducts();
                    });
            }

            function buildQuery() {
                const selected = getSelectedCategories();
                const params = new URLSearchParams();
                params.set('limit', String(pageSize));
                params.set('page', String(currentPage));
                if (selected.length > 0) {
                    params.set('categories', selected.join(','));
                }
                if (priceMinInput && priceMinInput.value !== '') {
                    params.set('min_price', priceMinInput.value);
                }
                if (priceMaxInput && priceMaxInput.value !== '') {
                    params.set('max_price', priceMaxInput.value);
                }
                if (sortSelect && sortSelect.value) {
                    params.set('sort', sortSelect.value);
                }
                if (searchInput && searchInput.value.trim()) params.set('search', searchInput.value.trim());
                return params.toString();
            }

            function fetchProducts() {
                const query = buildQuery();
                if (activeRequest) activeRequest.abort();
                const request = new AbortController();
                activeRequest = request;
                if (loading) loading.classList.add('show');
                grid.style.opacity = '.45';
                apiGet("<?= base_url('rest_api/catalog-products') ?>?" + query, request.signal)
                    .then(function (res) { return res.json(); })
                    .then(function (payload) {
                        if (!payload || !payload.status) {
                            return;
                        }
                        totalProducts = Number(payload.total || 0);
                        const pageCount = Math.ceil(totalProducts / pageSize);
                        if (pageCount > 0 && currentPage > pageCount) {
                            currentPage = pageCount;
                            fetchProducts();
                            return;
                        }
                        renderProducts(payload.products || []);
                        updateResults(totalProducts, (payload.products || []).length);
                        updateChips();
                        renderPagination();
                        syncUrl();
                    })
                    .catch(function (error) {
                        if (error.name === 'AbortError') return;
                        updateChips();
                    })
                    .finally(function () {
                        if (activeRequest === request) {
                            activeRequest = null;
                            if (loading) loading.classList.remove('show');
                            grid.style.opacity = '1';
                        }
                    });
            }

            if (grid) {
                grid.addEventListener('click', function (event) {
                    const btn = event.target.closest('.add-to-cart-btn');
                    if (!btn) {
                        return;
                    }
                    event.preventDefault();
                    const id = Number(btn.getAttribute('data-id') || 0);
                    const name = btn.getAttribute('data-name') || 'Product';
                    const price = Number(btn.getAttribute('data-price') || 0);
                    const imageUrl = btn.getAttribute('data-image') || '';
                    addToCart({ id: id, name: name, price: price, image_url: imageUrl });
                    btn.textContent = 'Added';
                    setTimeout(function () {
                        btn.textContent = 'Add to Cart';
                    }, 1200);
                });
            }

            let filterTimer = null;
            function scheduleFetch() {
                if (filterTimer) {
                    clearTimeout(filterTimer);
                }
                currentPage = 1;
                filterTimer = setTimeout(fetchProducts, 260);
            }

            function bindCategoryEvents() {
                if (!categoriesWrap) {
                    return;
                }
                categoriesWrap.querySelectorAll('input.filter-category').forEach(function (input) {
                    input.addEventListener('change', scheduleFetch);
                });
            }

            if (sortSelect) {
                sortSelect.addEventListener('change', scheduleFetch);
            }
            if (searchInput) searchInput.addEventListener('input', scheduleFetch);

            if (priceRange) {
                priceRange.addEventListener('input', function () {
                    if (priceMaxInput) {
                        priceMaxInput.value = priceRange.value;
                    }
                    applyPriceLabels(Number(priceRange.min || 0), Number(priceRange.value || 0));
                });
                priceRange.addEventListener('change', scheduleFetch);
            }

            if (priceMinInput) {
                priceMinInput.addEventListener('input', scheduleFetch);
            }

            if (priceMaxInput) {
                priceMaxInput.addEventListener('input', scheduleFetch);
            }

            chipsWrap?.addEventListener('click', function (event) {
                const button = event.target.closest('.chip');
                if (!button) return;
                if (button.dataset.removeCategory) {
                    const input = categoriesWrap.querySelector('.filter-category[value="' + CSS.escape(button.dataset.removeCategory) + '"]');
                    if (input) input.checked = false;
                }
                if (button.dataset.removePrice) {
                    priceMinInput.value = String(catalogPriceMin);
                    priceMaxInput.value = String(catalogPriceMax);
                    priceRange.value = String(catalogPriceMax);
                    applyPriceLabels(catalogPriceMin, catalogPriceMax);
                }
                if (button.dataset.removeSearch && searchInput) searchInput.value = '';
                currentPage = 1;
                fetchProducts();
            });

            pagination?.addEventListener('click', function (event) {
                const button = event.target.closest('[data-page]');
                if (!button || button.disabled) return;
                currentPage = Number(button.dataset.page || 1);
                fetchProducts();
                window.scrollTo({ top: document.querySelector('.content').offsetTop - 20, behavior: 'smooth' });
            });

            filterToggle?.addEventListener('click', function () {
                sidebar.classList.add('open');
                filterOverlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
            filterClose?.addEventListener('click', closeFilters);
            filterOverlay?.addEventListener('click', closeFilters);
            clearFilters?.addEventListener('click', resetFilters);
            document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeFilters(); });

            fetchFilters();
            updateCartBadge();
        })();
    </script>
</body>
</html>
