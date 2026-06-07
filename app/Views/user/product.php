<?php
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$currencySymbol = trim((string) ($currencySymbol ?? '$'));
$currencySpacer = preg_match('/[A-Za-z]$/', $currencySymbol) ? ' ' : '';
$productData = is_array($product ?? null) ? $product : null;
$initialName = trim((string) ($productData['name'] ?? 'Product'));
$initialDescription = trim((string) ($productData['description'] ?? ''));
$initialImage = trim((string) ($productData['image_url'] ?? ''));
$initialPrice = (float) ($productData['price'] ?? 0);
$initialCategory = trim((string) ($productData['category'] ?? ''));
$initialBrand = trim((string) ($productData['brand'] ?? ''));
$initialStock = (int) ($productData['stock'] ?? 0);
$productIdValue = (int) ($productId ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
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
            max-width: 1300px;
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
        .product-layout {
            margin-top: 18px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 20px;
        }
        .media {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            min-height: 320px;
            display: grid;
            place-items: center;
        }
        .media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .media .fallback {
            color: #637086;
            font-weight: 800;
            padding: 16px;
            text-align: center;
        }
        .details {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 20px;
        }
        .details h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 800;
        }
        .price {
            font-size: 1.4rem;
            font-weight: 800;
            color: #4b5a72;
        }
        .product-rating {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #667085;
            font-size: 0.9rem;
            font-weight: 700;
        }
        .stars {
            color: #f5a623;
            letter-spacing: 1px;
            white-space: nowrap;
        }
        .meta {
            margin-top: 12px;
            display: grid;
            gap: 8px;
            color: #4b5a72;
            font-size: 0.95rem;
            font-weight: 700;
        }
        .desc {
            margin-top: 14px;
            color: #475163;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            border: 0;
            border-radius: 12px;
            padding: 10px 16px;
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
        .related {
            margin-top: 24px;
        }
        .related h2 {
            margin: 0 0 10px;
            font-size: 1.4rem;
            font-weight: 800;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
        }
        .thumb {
            background: #eceef2;
            aspect-ratio: 4 / 4;
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
        .reviews-section {
            margin-top: 24px;
            display: grid;
            gap: 16px;
        }
        .reviews-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .reviews-heading h2 {
            margin: 0;
            font-size: 1.4rem;
        }
        .reviews-heading p {
            margin: 4px 0 0;
            color: var(--muted);
        }
        .review-layout {
            display: grid;
            grid-template-columns: minmax(260px, .75fr) minmax(0, 1.5fr);
            gap: 16px;
            align-items: start;
        }
        .review-panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
        }
        .rating-score {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }
        .rating-score strong {
            font-size: 2.4rem;
            line-height: 1;
        }
        .rating-bars {
            display: grid;
            gap: 8px;
        }
        .rating-row {
            display: grid;
            grid-template-columns: 42px minmax(80px, 1fr) 30px;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 700;
        }
        .rating-track {
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: #edf0f5;
        }
        .rating-fill {
            display: block;
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: #f5a623;
        }
        .review-form {
            display: grid;
            gap: 12px;
        }
        .review-form h3 {
            margin: 0;
            font-size: 1.05rem;
        }
        .star-input {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            width: max-content;
        }
        .star-input input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .star-input label {
            padding: 0 3px;
            color: #cfd5df;
            font-size: 1.9rem;
            cursor: pointer;
            transition: color .15s ease, transform .15s ease;
        }
        .star-input label:hover,
        .star-input label:hover ~ label,
        .star-input input:checked ~ label {
            color: #f5a623;
        }
        .star-input label:hover {
            transform: translateY(-1px);
        }
        .review-form textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
            border: 1px solid #d7dde7;
            border-radius: 12px;
            padding: 11px 12px;
            color: var(--ink);
            font: inherit;
        }
        .review-form textarea:focus {
            outline: 2px solid color-mix(in srgb, var(--accent) 20%, transparent);
            border-color: var(--accent);
        }
        .review-help, .review-message {
            margin: 0;
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.45;
        }
        .review-message.success { color: #16803c; }
        .review-message.error { color: #c73838; }
        .review-list {
            display: grid;
            gap: 12px;
        }
        .review-item {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
        }
        .review-item-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .review-author {
            margin: 0;
            font-size: .95rem;
            font-weight: 800;
        }
        .review-date {
            color: var(--muted);
            font-size: .78rem;
        }
        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            color: #18794e;
            font-size: .76rem;
            font-weight: 800;
        }
        .review-copy {
            margin: 10px 0 0;
            color: #475163;
            line-height: 1.55;
            white-space: pre-wrap;
        }
        .review-empty {
            border: 1px dashed #ccd4df;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            color: var(--muted);
        }
        @media (max-width: 1100px) {
            .grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 900px) {
            .product-layout {
                grid-template-columns: 1fr;
            }
            .review-layout {
                grid-template-columns: 1fr;
            }
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

        <section class="product-layout">
            <div class="media">
                <?php if ($initialImage !== '') : ?>
                    <img id="productImage" src="<?= esc($initialImage) ?>" alt="<?= esc($initialName) ?>">
                <?php else : ?>
                    <div class="fallback" id="productImageFallback"><?= esc($initialName !== '' ? $initialName : 'Product') ?></div>
                <?php endif; ?>
            </div>
            <div class="details">
                <h1 id="productName"><?= esc($initialName !== '' ? $initialName : 'Product') ?></h1>
                <div class="price" id="productPrice"><?= esc($currencySymbol . $currencySpacer . number_format($initialPrice, 0)) ?></div>
                <div class="product-rating" id="productRating">
                    <span class="stars" aria-hidden="true">☆☆☆☆☆</span>
                    <span>No reviews yet</span>
                </div>
                <div class="meta">
                    <div id="productCategory">Category: <?= esc($initialCategory !== '' ? $initialCategory : 'General') ?></div>
                    <div id="productBrand">Brand: <?= esc($initialBrand !== '' ? $initialBrand : 'Standard') ?></div>
                    <div id="productStock">Stock: <?= esc((string) $initialStock) ?></div>
                </div>
                <div class="desc" id="productDescription">
                    <?= esc($initialDescription !== '' ? $initialDescription : 'Product details will appear here.') ?>
                </div>
                <div class="actions">
                    <button class="btn primary" type="button" id="addToCartBtn">Add to Cart</button>
                    <a class="btn ghost" href="<?= base_url('user/cart') ?>">Go to Cart</a>
                </div>
            </div>
        </section>

        <section class="reviews-section" aria-labelledby="reviewsTitle">
            <div class="reviews-heading">
                <div>
                    <h2 id="reviewsTitle">Ratings &amp; Reviews</h2>
                    <p>Feedback from customers who purchased this product.</p>
                </div>
            </div>
            <div class="review-layout">
                <div class="review-panel">
                    <div class="rating-score">
                        <strong id="reviewAverage">0.0</strong>
                        <div>
                            <div class="stars" id="reviewAverageStars" aria-label="No ratings">☆☆☆☆☆</div>
                            <div class="review-help" id="reviewCount">0 reviews</div>
                        </div>
                    </div>
                    <div class="rating-bars" id="ratingBars"></div>
                </div>
                <form class="review-panel review-form" id="reviewForm">
                    <h3 id="reviewFormTitle">Write a review</h3>
                    <div class="star-input" role="radiogroup" aria-label="Choose a rating">
                        <?php for ($star = 5; $star >= 1; $star--) : ?>
                            <input type="radio" name="rating" id="rating<?= $star ?>" value="<?= $star ?>">
                            <label for="rating<?= $star ?>" title="<?= $star ?> star<?= $star === 1 ? '' : 's' ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <textarea id="reviewText" maxlength="1000" placeholder="Share what you liked, product quality, and your experience."></textarea>
                    <p class="review-help" id="reviewEligibility">Checking review eligibility...</p>
                    <p class="review-message" id="reviewMessage" role="status"></p>
                    <button class="btn primary" id="reviewSubmit" type="submit" disabled>Submit Review</button>
                </form>
            </div>
            <div class="review-list" id="reviewList">
                <div class="review-empty">Loading reviews...</div>
            </div>
        </section>

        <section class="related">
            <h2>Related Products</h2>
            <div class="grid" id="relatedGrid"></div>
            <div class="empty" id="relatedEmpty" style="display:none;">No related products found.</div>
        </section>

        <?= view('user/partials/footer', ['websiteName' => $websiteName]) ?>
    </div>

    <script>
        (function () {
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;
            const productId = <?= (int) $productIdValue ?>;
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            const cartKey = 'cart_items';
            const cartBadge = document.getElementById('cartBadge');
            const initialProduct = <?= json_encode([
                'id' => (int) $productIdValue,
                'name' => $initialName,
                'price' => $initialPrice,
                'image_url' => $initialImage,
            ]) ?>;
            let currentProduct = Object.assign({}, initialProduct);

            function apiGet(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                });
            }

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

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderProduct(data) {
                if (!data) {
                    return;
                }
                const name = String(data?.name || 'Product').trim() || 'Product';
                const description = String(data?.description || '').trim() || 'Product details will appear here.';
                const price = Number(data?.price || 0);
                const imageUrl = String(data?.image_url || '').trim();
                const category = String(data?.category || '').trim() || 'General';
                const brand = String(data?.brand || '').trim() || 'Standard';
                const stock = Number(data?.stock || 0);

                const nameEl = document.getElementById('productName');
                const priceEl = document.getElementById('productPrice');
                const categoryEl = document.getElementById('productCategory');
                const brandEl = document.getElementById('productBrand');
                const stockEl = document.getElementById('productStock');
                const descEl = document.getElementById('productDescription');
                const imageEl = document.getElementById('productImage');
                const imageFallback = document.getElementById('productImageFallback');

                if (nameEl) nameEl.textContent = name;
                if (priceEl) priceEl.textContent = formatPrice(price);
                if (categoryEl) categoryEl.textContent = 'Category: ' + category;
                if (brandEl) brandEl.textContent = 'Brand: ' + brand;
                if (stockEl) stockEl.textContent = 'Stock: ' + stock;
                if (descEl) descEl.textContent = description;

                if (imageUrl !== '') {
                    if (imageEl) {
                        imageEl.src = imageUrl;
                        imageEl.alt = name;
                        imageEl.style.display = '';
                    }
                    if (imageFallback) {
                        imageFallback.style.display = 'none';
                    }
                } else if (imageFallback) {
                    imageFallback.textContent = name;
                }

                currentProduct = {
                    id: Number(data?.id || productId),
                    name: name,
                    price: price,
                    image_url: imageUrl
                };
            }

            function renderRelated(items) {
                const grid = document.getElementById('relatedGrid');
                const empty = document.getElementById('relatedEmpty');
                if (!grid) {
                    return;
                }
                const rows = Array.isArray(items) ? items : [];
                if (rows.length === 0) {
                    grid.innerHTML = '';
                    if (empty) empty.style.display = 'block';
                    return;
                }
                if (empty) empty.style.display = 'none';

                grid.innerHTML = rows.map(function (product) {
                    const id = Number(product?.id || 0);
                    const name = String(product?.name || 'Product').trim() || 'Product';
                    const price = Number(product?.price || 0);
                    const imageUrl = String(product?.image_url || '').trim();
                    const priceText = formatPrice(price);
                    const href = <?= json_encode(base_url('user/product')) ?> + '/' + id;

                    return '' +
                        '<a class="card-link" href="' + escapeHtml(href) + '">' +
                            '<article class="card">' +
                                '<div class="thumb">' +
                                    (imageUrl
                                        ? '<img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(name) + '">'
                                        : '<div class="fallback">' + escapeHtml(name) + '</div>') +
                                '</div>' +
                                '<div class="card-body">' +
                                    '<div class="name-price">' +
                                        '<h3 class="name">' + escapeHtml(name) + '</h3>' +
                                        '<p class="price">' + escapeHtml(priceText) + '</p>' +
                                    '</div>' +
                                '</div>' +
                            '</article>' +
                        '</a>';
                }).join('');
            }

            function starText(rating) {
                const value = Math.max(0, Math.min(5, Math.round(Number(rating || 0))));
                return '★'.repeat(value) + '☆'.repeat(5 - value);
            }

            function renderReviewSummary(summary) {
                const average = Number(summary?.average || 0);
                const count = Number(summary?.count || 0);
                const distribution = summary?.distribution || {};
                const averageEl = document.getElementById('reviewAverage');
                const starsEl = document.getElementById('reviewAverageStars');
                const countEl = document.getElementById('reviewCount');
                const productRating = document.getElementById('productRating');
                const bars = document.getElementById('ratingBars');

                averageEl.textContent = average.toFixed(1);
                starsEl.textContent = starText(average);
                starsEl.setAttribute('aria-label', average.toFixed(1) + ' out of 5 stars');
                countEl.textContent = count + (count === 1 ? ' review' : ' reviews');
                productRating.innerHTML =
                    '<span class="stars" aria-hidden="true">' + starText(average) + '</span>' +
                    '<span>' + (count ? average.toFixed(1) + ' (' + count + ')' : 'No reviews yet') + '</span>';

                bars.innerHTML = [5, 4, 3, 2, 1].map(function (rating) {
                    const value = Number(distribution[String(rating)] || 0);
                    const percent = count > 0 ? Math.round((value / count) * 100) : 0;
                    return '<div class="rating-row">' +
                        '<span>' + rating + ' ★</span>' +
                        '<span class="rating-track"><span class="rating-fill" style="width:' + percent + '%"></span></span>' +
                        '<span>' + value + '</span>' +
                    '</div>';
                }).join('');
            }

            function renderReviews(reviews) {
                const list = document.getElementById('reviewList');
                const rows = Array.isArray(reviews) ? reviews : [];
                if (!rows.length) {
                    list.innerHTML = '<div class="review-empty">No reviews yet. Delivered customers can be the first to review this product.</div>';
                    return;
                }

                list.innerHTML = rows.map(function (review) {
                    const dateValue = String(review?.updated_at || review?.created_at || '');
                    const date = dateValue ? new Date(dateValue.replace(' ', 'T')) : null;
                    const dateText = date && !Number.isNaN(date.getTime())
                        ? date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
                        : '';
                    const copy = String(review?.review_text || '').trim();
                    return '<article class="review-item">' +
                        '<div class="review-item-head">' +
                            '<div>' +
                                '<p class="review-author">' + escapeHtml(review?.reviewer_name || 'Customer') + '</p>' +
                                (review?.verified_purchase
                                    ? '<span class="verified-badge"><i class="fa-solid fa-circle-check"></i> Verified purchase</span>'
                                    : '') +
                            '</div>' +
                            '<div style="text-align:right">' +
                                '<div class="stars" aria-label="' + Number(review?.rating || 0) + ' out of 5 stars">' + starText(review?.rating) + '</div>' +
                                '<span class="review-date">' + escapeHtml(dateText) + '</span>' +
                            '</div>' +
                        '</div>' +
                        (copy ? '<p class="review-copy">' + escapeHtml(copy) + '</p>' : '') +
                    '</article>';
                }).join('');
            }

            function configureReviewForm(viewer) {
                const form = document.getElementById('reviewForm');
                const title = document.getElementById('reviewFormTitle');
                const help = document.getElementById('reviewEligibility');
                const submit = document.getElementById('reviewSubmit');
                const text = document.getElementById('reviewText');
                const review = viewer?.review || null;
                const canReview = viewer?.logged_in === true && viewer?.eligible === true;

                form.dataset.enabled = canReview ? '1' : '0';
                submit.disabled = !canReview;

                if (!viewer?.logged_in) {
                    help.innerHTML = 'Please <a href="<?= base_url('login') ?>">sign in</a> to review this product.';
                } else if (!viewer?.eligible) {
                    help.textContent = 'Reviews become available after this product is delivered.';
                } else {
                    help.textContent = 'Your review will be marked as a verified purchase.';
                }

                if (review) {
                    title.textContent = 'Edit your review';
                    submit.textContent = 'Update Review';
                    text.value = String(review.review_text || '');
                    const ratingInput = form.querySelector('input[name="rating"][value="' + Number(review.rating || 0) + '"]');
                    if (ratingInput) ratingInput.checked = true;
                }
            }

            function loadReviews() {
                return apiGet("<?= base_url('rest_api/product-reviews') ?>/" + productId)
                    .then(function (res) { return res.json(); })
                    .then(function (payload) {
                        if (!payload || !payload.status) {
                            throw new Error(payload?.message || 'Unable to load reviews.');
                        }
                        renderReviewSummary(payload.summary || {});
                        renderReviews(payload.reviews || []);
                        configureReviewForm(payload.viewer || {});
                    })
                    .catch(function () {
                        document.getElementById('reviewList').innerHTML =
                            '<div class="review-empty">Reviews could not be loaded right now.</div>';
                        document.getElementById('reviewEligibility').textContent =
                            'Review service is currently unavailable.';
                    });
            }

            document.getElementById('reviewForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const form = event.currentTarget;
                if (form.dataset.enabled !== '1') return;

                const selected = form.querySelector('input[name="rating"]:checked');
                const message = document.getElementById('reviewMessage');
                const submit = document.getElementById('reviewSubmit');
                if (!selected) {
                    message.className = 'review-message error';
                    message.textContent = 'Choose a star rating.';
                    return;
                }

                submit.disabled = true;
                message.className = 'review-message';
                message.textContent = 'Saving your review...';
                apiPost("<?= base_url('rest_api/product-reviews') ?>/" + productId, {
                    rating: Number(selected.value),
                    review_text: document.getElementById('reviewText').value.trim()
                })
                    .then(function (res) {
                        return res.json().then(function (payload) {
                            if (!res.ok || !payload?.status) {
                                throw new Error(payload?.message || 'Unable to save review.');
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        message.className = 'review-message success';
                        message.textContent = payload.message;
                        return loadReviews();
                    })
                    .catch(function (error) {
                        message.className = 'review-message error';
                        message.textContent = error.message || 'Unable to save review.';
                    })
                    .finally(function () {
                        submit.disabled = form.dataset.enabled !== '1';
                    });
            });

            apiGet("<?= base_url('rest_api/product') ?>/" + productId)
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (!payload || !payload.status) {
                        return;
                    }
                    renderProduct(payload.product);
                })
                .catch(function () {});

            apiGet("<?= base_url('rest_api/related-products?product_id=') ?>" + productId + "&limit=4")
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (!payload || !payload.status) {
                        return;
                    }
                    renderRelated(payload.products || []);
                })
                .catch(function () {});

            loadReviews();

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

            const addBtn = document.getElementById('addToCartBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function () {
                    addToCart(currentProduct);
                    addBtn.textContent = 'Added';
                    setTimeout(function () {
                        addBtn.textContent = 'Add to Cart';
                    }, 1200);
                });
            }

            function formatPrice(value) {
                const amount = Number(value || 0).toLocaleString();
                if (/[A-Za-z]$/.test(currencySymbol)) {
                    return currencySymbol + ' ' + amount;
                }
                return currencySymbol + amount;
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

            updateCartBadge();
        })();
    </script>
</body>
</html>
