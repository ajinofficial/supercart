<?php
$displayName = trim((string) ($userName ?? 'User'));
$bannerItems = is_array($banners ?? null) ? $banners : [];
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$currencySymbol = trim((string) ($currencySymbol ?? '$'));
$currencySpacer = preg_match('/[A-Za-z]$/', $currencySymbol) ? ' ' : '';
$segmentImageUrl = '';
$segmentImageAltUrl = '';
if (!empty($bannerItems)) {
    $segmentImageFile = trim((string) ($bannerItems[0]['bn_image'] ?? ''));
    if ($segmentImageFile !== '') {
        $segmentImageUrl = base_url('uploads/banners/' . $segmentImageFile);
    }

    $segmentImageAltFile = trim((string) ($bannerItems[1]['bn_image'] ?? ($bannerItems[0]['bn_image'] ?? '')));
    if ($segmentImageAltFile !== '') {
        $segmentImageAltUrl = base_url('uploads/banners/' . $segmentImageAltFile);
    }
}

$categories = [
    ['icon' => 'fa-wrench', 'label' => 'Plumbing & Repair'],
    ['icon' => 'fa-palette', 'label' => 'Art and Creativity'],
    ['icon' => 'fa-futbol', 'label' => 'Hobby & Sport'],
    ['icon' => 'fa-puzzle-piece', 'label' => 'Games & Puzzles'],
    ['icon' => 'fa-shirt', 'label' => 'Clothes & Footwear'],
    ['icon' => 'fa-shield-heart', 'label' => 'Health & Safety'],
    ['icon' => 'fa-baby', 'label' => 'Feeding & Nutrition'],
    ['icon' => 'fa-burger', 'label' => 'Food & Drinks'],
    ['icon' => 'fa-store', 'label' => 'Boutiques & Shops'],
    ['icon' => 'fa-people-group', 'label' => 'Goods for Mothers'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --page-bg: #f4f7fb;
            --surface: #ffffff;
            --ink: #1b2432;
            --muted: #5f6f86;
            --hero-a: #79a9d1;
            --hero-b: #d4e9f7;
            --chip: #f9fbff;
            --line: #e1e7f0;
            --accent: #f15a3b;
            --font-main: "Nunito Sans", sans-serif;
            --content-max: 100%;
            --hero-min-height: 330px;
            --product-columns: 4;
            --radius-xl: 34px;
            --radius-lg: 22px;
            --radius-md: 16px;
            --shadow-soft: 0 14px 34px rgba(25, 41, 72, 0.09);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: radial-gradient(circle at top right, #e8f2fb 0%, var(--page-bg) 48%);
            color: var(--ink);
            font-family: var(--font-main);
        }

        .container {
            width: 100%;
            max-width: var(--content-max);
            margin: 0 auto;
            padding: 20px 18px 42px;
        }

        .top-nav {
            min-height: 56px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            border-radius: 0 0 18px 18px;
            border: 1px solid #e4eaf2;
            padding: 12px clamp(18px, 3vw, 44px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px 18px;
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            box-shadow: var(--shadow-soft);
        }

        .brand {
            font-family: "Baloo 2", cursive;
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #2a2d3b;
            text-decoration: none;
        }

        .brand img {
            display: block;
            height: 38px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
        }

        .brand small {
            color: #f17255;
            font-size: 0.85em;
            margin-right: 3px;
        }

        .menu-btn {
            border: 1px solid #dfe2e9;
            background: #fff;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: center;
            color: #283043;
            cursor: pointer;
        }

        .nav-links {
            display: flex;
            gap: 14px;
            font-weight: 700;
            font-size: 0.86rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #2a3750;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: #0f6eb8;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #d4dfeb;
            background: #fff;
            color: #22354d;
            font-weight: 800;
            line-height: 1;
        }

        .nav-btn.primary {
            background: #1b7cc8;
            border-color: #1b7cc8;
            color: #fff;
        }

        .nav-icon-link {
            font-size: 1.35rem;
            color: #1f3149;
            line-height: 1;
            padding: 4px 6px;
        }
        .nav-profile {
            display: none;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #23364d;
            font-weight: 800;
            font-size: 0.82rem;
        }
        .nav-profile img {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            border: 1px solid #d4deea;
            object-fit: cover;
            background: #eef3f9;
        }

        .hero {
            margin-top: 14px;
            border-radius: var(--radius-xl);
            overflow: hidden;
            min-height: var(--hero-min-height);
            position: relative;
            background: linear-gradient(120deg, var(--hero-a), var(--hero-b));
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            box-shadow: var(--shadow-soft);
            border: 1px solid #dce7f2;
        }

        .hero::before,
        .hero::after {
            content: "";
            position: absolute;
            width: 88px;
            height: 88px;
            border-radius: 999px;
            filter: blur(2px);
            opacity: 0.42;
            background: #76b2ae;
        }

        .hero::before { top: 22px; right: 86px; }
        .hero::after { left: 30px; bottom: 26px; }

        .hero-left {
            padding: 56px 30px 28px 38px;
            z-index: 2;
        }

        .hero-title {
            margin: 0;
            font-size: 3.05rem;
            line-height: 0.95;
            letter-spacing: -0.7px;
            font-weight: 800;
            color: #112339;
        }

        .hero-sub {
            margin: 10px 0 0;
            font-size: 1.95rem;
            line-height: 1.05;
            font-weight: 500;
            color: #223852;
        }

        .search {
            margin-top: 22px;
            width: 100%;
            max-width: 356px;
            height: 46px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid #d5dfeb;
            display: flex;
            align-items: center;
            padding: 0 14px;
            gap: 9px;
            box-shadow: 0 8px 20px rgba(32, 54, 88, 0.1);
        }

        .search i { color: var(--accent); font-size: 0.95rem; }
        .search input {
            border: 0;
            width: 100%;
            outline: none;
            background: transparent;
            font-size: 0.9rem;
            color: #32384a;
            font-family: inherit;
        }

        .hero-right {
            position: relative;
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .hero-slider {
            width: 100%;
            max-width: 345px;
            height: 290px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.38);
            border: 1px solid rgba(255, 255, 255, 0.45);
            overflow: hidden;
            position: relative;
        }

        .hero-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.6s ease;
        }

        .hero-slide {
            min-width: 100%;
            height: 100%;
        }

        .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-fallback {
            color: #2a6461;
            text-align: center;
            padding: 18px;
            font-size: 0.95rem;
            font-weight: 700;
            display: grid;
            height: 100%;
            place-items: center;
        }

        .hero-dots {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 10px;
            display: flex;
            justify-content: center;
            gap: 6px;
            z-index: 3;
        }

        .hero-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.55);
            border: 0;
            padding: 0;
            cursor: pointer;
        }

        .hero-dot.active {
            width: 20px;
            background: #fff;
        }

        .section-head {
            margin-top: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-title {
            margin: 0;
            font-size: 1.62rem;
            font-weight: 800;
            letter-spacing: -0.2px;
        }

        .section-link {
            text-decoration: none;
            color: #2f3446;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .categories {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 6px;
        }

        .cat-card {
            min-width: 102px;
            background: var(--chip);
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            padding: 18px 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(25, 33, 53, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 18px rgba(26, 44, 71, 0.12);
        }

        .cat-card.active {
            background: #fff;
            box-shadow: 0 7px 16px rgba(33, 45, 70, 0.08);
        }

        .cat-title {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1.3;
            color: #2b2f41;
        }

        .popular-grid {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(var(--product-columns), minmax(0, 1fr));
            gap: 16px;
        }

        .product {
            border-radius: var(--radius-lg);
            overflow: hidden hidden;
            background: #e6ebf3;
            border: 1px solid #d9e2ee;
            min-height: 0;
            box-shadow: 0 8px 18px rgba(30, 39, 58, 0.06);
            display: flex;
            flex-direction: column;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .product-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .product:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 30px rgba(24, 42, 74, 0.16);
        }

        .product img,
        .product-thumb {
            width: 100%;
            aspect-ratio: 4 / 3;
            height: auto;
            object-fit: cover;
            display: block;
            background: #d8dbe2;
        }

        .product-fallback {
            display: grid;
            place-items: center;
            color: #4c5467;
            font-weight: 800;
            text-align: center;
            padding: 12px;
            aspect-ratio: 4 / 3;
        }

        .product-body {
            background: #fff;
            padding: 10px;
            border-top: 1px solid #e4e7ee;
            flex: 1;
        }

        .product-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: #212737;
            line-height: 1.35;
        }

        .product-desc {
            margin: 6px 0 0;
            color: #5d6579;
            font-size: 0.82rem;
            line-height: 1.38;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-actions {
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .product-price {
            font-size: 0.9rem;
            font-weight: 800;
            color: #1b7cc8;
            white-space: nowrap;
        }
        .product-btn {
            border: 0;
            border-radius: 10px;
            padding: 6px 10px;
            font-size: 0.75rem;
            font-weight: 800;
            background: #1b7cc8;
            color: #fff;
            cursor: pointer;
        }

        .desc-box {
            margin-top: 20px;
            background: #fff;
            border: 1px solid #dfe3ea;
            border-radius: 18px;
            padding: 18px;
            box-shadow: var(--shadow-soft);
        }

        .desc-box h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .desc-box p {
            margin: 8px 0 0;
            color: #50586c;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .biz-section {
            margin-top: 24px;
            background: linear-gradient(180deg, #edf2f9 0%, #e5ebf5 100%);
            border-radius: 20px;
            padding: 34px 18px;
            border: 1px solid #d5deeb;
        }

        .biz-top {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: center;
        }

        .biz-top.reverse {
            grid-template-columns: 1fr 340px;
        }

        .biz-image {
            width: 100%;
            height: 220px;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #d3d6db;
            background: #d8dde2;
        }

        .biz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .biz-image-fallback {
            height: 100%;
            display: grid;
            place-items: center;
            color: #4b5568;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .biz-title {
            margin: 0;
            font-size: 2.35rem;
            line-height: 1.25;
            color: #25303e;
            font-weight: 800;
            max-width: 560px;
        }

        .biz-text {
            margin: 12px 0 0;
            max-width: 560px;
            color: #3e4958;
            font-size: 0.98rem;
            line-height: 1.55;
        }

        .biz-services-title {
            margin: 30px 0 12px;
            text-align: center;
            font-size: 2.2rem;
            color: #0d1017;
            font-weight: 800;
        }

        .biz-services {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .biz-card {
            background: #f8fbff;
            border: 1px solid #d4deea;
            border-radius: 12px;
            padding: 14px;
            min-height: 150px;
            box-shadow: 0 8px 16px rgba(24, 44, 76, 0.08);
        }

        .biz-card i {
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .biz-card:nth-child(1) i { color: #22a35a; }
        .biz-card:nth-child(2) i { color: #2d86e5; }
        .biz-card:nth-child(3) i { color: #f0a914; }

        .biz-card h4 {
            margin: 0 0 6px;
            font-size: 1.1rem;
            color: #1d2431;
            font-weight: 800;
        }

        .biz-card p {
            margin: 0;
            color: #444e5f;
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .testimonials-section {
            margin-top: 24px;
            padding: 34px 24px;
            border-radius: var(--radius-lg);
            background: var(--surface);
            border: 1px solid #dfe6ef;
            box-shadow: var(--shadow-soft);
        }

        .feature-heading {
            margin: 0;
            text-align: center;
            color: var(--ink);
            font-size: 2rem;
            font-weight: 900;
        }

        .feature-subtitle {
            max-width: 620px;
            margin: 8px auto 22px;
            text-align: center;
            color: var(--muted);
            line-height: 1.55;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .testimonial-card {
            padding: 20px;
            border-radius: var(--radius-md);
            background: var(--page-bg);
            border: 1px solid #dce4ee;
        }

        .testimonial-stars {
            color: #f4a623;
            letter-spacing: 2px;
        }

        .testimonial-quote {
            min-height: 66px;
            margin: 12px 0 18px;
            color: var(--ink);
            line-height: 1.55;
        }

        .testimonial-name {
            display: block;
            color: var(--ink);
            font-weight: 900;
        }

        .testimonial-role {
            color: var(--muted);
            font-size: 0.82rem;
        }

        .newsletter-section {
            display: grid;
            grid-template-columns: 1fr minmax(300px, 480px);
            gap: 28px;
            align-items: center;
            margin-top: 24px;
            padding: 32px;
            border-radius: var(--radius-lg);
            background: linear-gradient(125deg, var(--hero-a), var(--hero-b));
            color: var(--ink);
        }

        .newsletter-section h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 900;
        }

        .newsletter-section p {
            margin: 8px 0 0;
            line-height: 1.55;
        }

        .newsletter-form {
            display: flex;
            gap: 8px;
            padding: 6px;
            border-radius: 999px;
            background: #fff;
            box-shadow: var(--shadow-soft);
        }

        .newsletter-form input {
            min-width: 0;
            flex: 1;
            border: 0;
            outline: 0;
            padding: 10px 14px;
            font: inherit;
        }

        .newsletter-form button {
            border: 0;
            border-radius: 999px;
            padding: 10px 20px;
            background: var(--accent);
            color: #fff;
            font-weight: 900;
            cursor: pointer;
        }

        .newsletter-status {
            min-height: 20px;
            margin: 8px 12px 0;
            font-size: 0.84rem;
            font-weight: 800;
        }


        @media (max-width: 1024px) {
            .hero-title { font-size: 2.5rem; }
            .hero-sub { font-size: 1.65rem; }
        }

        @media (max-width: 820px) {
            .menu-btn {
                display: inline-flex;
            }
            .nav-links {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                left: 0;
                display: none;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                padding: 12px 14px;
                background: #fff;
                border: 1px solid #dde2ea;
                border-radius: 12px;
                box-shadow: var(--shadow-soft);
            }
            .top-nav.open .nav-links {
                display: flex;
            }
            .hero {
                grid-template-columns: 1fr;
                min-height: auto;
            }
            .hero-left {
                padding: 30px 22px 16px;
            }
            .hero-right {
                padding: 0 16px 18px;
            }
            .popular-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .categories {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
            .biz-top {
                grid-template-columns: 1fr;
            }
            .biz-top.reverse {
                grid-template-columns: 1fr;
            }
            .biz-title {
                font-size: 1.8rem;
            }
            .biz-services {
                grid-template-columns: 1fr;
            }
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            .newsletter-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .container { padding: 10px 10px 28px; }
            .hero-title { font-size: 2rem; }
            .hero-sub { font-size: 1.35rem; }
            .search { max-width: 100%; }
            .popular-grid { grid-template-columns: 1fr; }
            .categories { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .biz-services-title {
                font-size: 1.5rem;
            }
            .footer {
                min-height: 210px;
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
            <button class="menu-btn" id="menuBtn" aria-label="Toggle menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <nav class="nav-links">
                <a id="navHome" href="<?= base_url('user/dashboard') ?>">Home</a>
                <a id="navCourses" href="<?= base_url('user/catalog') ?>">Courses</a>
                <a id="navContacts" href="<?= base_url('user/contact') ?>">Contact</a>
                <a id="navAbout" href="<?= base_url('user/about') ?>">About</a>
                <a href="<?= base_url('user/profile') ?>">Profile</a>
                <a class="nav-icon-link cart-link" href="<?= base_url('user/cart') ?>" aria-label="Cart">
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

        <section class="hero">
            <div class="hero-left">
                <h1 id="heroTitle" class="hero-title">Store for children</h1>
                <p id="heroSubtitle" class="hero-sub">shopping with joy</p>
                <label class="search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input id="heroSearchInput" type="text" placeholder="Search">
                </label>
            </div>
            <div class="hero-right">
                <div class="hero-slider" id="heroSlider">
                    <?php if (! empty($bannerItems)) : ?>
                        <div class="hero-track" id="heroTrack">
                            <?php foreach ($bannerItems as $banner) : ?>
                                <?php
                                $heroFile = trim((string) ($banner['bn_image'] ?? ''));
                                $heroUrl = $heroFile !== '' ? base_url('uploads/banners/' . $heroFile) : '';
                                $heroTitle = trim((string) ($banner['bn_title'] ?? 'Banner'));
                                ?>
                                <div class="hero-slide">
                                    <?php if ($heroUrl !== '') : ?>
                                        <img src="<?= esc($heroUrl) ?>" alt="<?= esc($heroTitle) ?>">
                                    <?php else : ?>
                                        <div class="hero-fallback"><?= esc($heroTitle) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="hero-dots" id="heroDots"></div>
                    <?php else : ?>
                        <div class="hero-fallback">
                            <p style="margin:0 0 8px;">Hello, <?= esc($displayName) ?></p>
                            <p style="margin:0;">Add active banners to show artwork here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="section-head">
            <h2 id="categoriesTitle" class="section-title">Select Categories</h2>
            <!-- <a id="categoriesLink" class="section-link" href="#">Show All</a> -->
        </div>
        <section class="categories" id="categoriesGrid">
        </section>

        <div class="section-head">
            <h2 id="popularTitle" class="section-title">Most Popular</h2>
            <a id="popularLink" class="section-link" href="<?= base_url('user/catalog') ?>">Show All</a>
        </div>
        <section class="popular-grid" id="popularGrid">
        </section>

        <section class="biz-section" id="businessSectionMain">
            <div class="biz-top">
                <div class="biz-image" id="businessMainImage">
                    <?php if ($segmentImageUrl !== '') : ?>
                        <img src="<?= esc($segmentImageUrl) ?>" alt="Business collaboration">
                    <?php else : ?>
                        <div class="biz-image-fallback">Featured business image</div>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 id="businessTitle" class="biz-title">Elevating Business Performance Through Strategic Solutions</h3>
                    <p id="businessDescription" class="biz-text">Increasingly many people use digital media for learning and continuing education. E-learning content formats that are individually modified to meet each learner needs are fundamental for successful outcomes.</p>
                </div>
            </div>

            <h3 id="businessServicesTitle" class="biz-services-title">Featured Services</h3>
            <div class="biz-services">
                <article class="biz-card">
                    <i class="fa-solid fa-users-viewfinder"></i>
                    <h4 id="service1Title">Talent Management Strategy</h4>
                    <p id="service1Description">Build stronger teams with focused hiring, role mapping, and continuous capability development.</p>
                </article>
                <article class="biz-card">
                    <i class="fa-solid fa-lightbulb"></i>
                    <h4 id="service2Title">Innovation &amp; Digital Transformation</h4>
                    <p id="service2Description">Modernize operations through practical digital workflows, automation, and customer-first experiences.</p>
                </article>
                <article class="biz-card">
                    <i class="fa-solid fa-chart-column"></i>
                    <h4 id="service3Title">Market Expansion Advisory</h4>
                    <p id="service3Description">Scale into new regions with structured research, channel planning, and measurable growth strategy.</p>
                </article>
            </div>
        </section>

        <section class="biz-section" id="businessSectionAlt">
            <div class="biz-top reverse">
                <div>
                    <h3 id="businessAltTitle" class="biz-title">Driving Better Outcomes With Practical Business Execution</h3>
                    <p id="businessAltDescription" class="biz-text">From planning to delivery, structured execution and clear communication help teams move faster, reduce risk, and create long-term business value.</p>
                </div>
                <div class="biz-image" id="businessAltImage">
                    <?php if ($segmentImageAltUrl !== '') : ?>
                        <img src="<?= esc($segmentImageAltUrl) ?>" alt="Business execution">
                    <?php else : ?>
                        <div class="biz-image-fallback">Featured business image</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="desc-box">
            <h3 id="aboutTitle">About Our Store</h3>
            <p id="aboutDescription">We provide trusted products for children and families, including toys, clothing, nutrition, and daily essentials. The dashboard helps users explore featured banners, browse categories, and discover popular products quickly on mobile and desktop.</p>
        </section>

        <section class="testimonials-section" id="testimonialsSection">
            <h2 id="testimonialsTitle" class="feature-heading">Loved by families</h2>
            <p id="testimonialsSubtitle" class="feature-subtitle">Real feedback from customers who shop with us.</p>
            <div class="testimonials-grid" id="testimonialsGrid"></div>
        </section>

        <section class="newsletter-section" id="newsletterSection">
            <div>
                <h2 id="newsletterTitle">Get offers and new arrivals</h2>
                <p id="newsletterDescription">Join our newsletter for product updates, family shopping tips, and exclusive deals.</p>
            </div>
            <div>
                <form class="newsletter-form" id="newsletterForm">
                    <input id="newsletterEmail" type="email" placeholder="Enter your email address" required>
                    <button id="newsletterButton" type="submit">Subscribe</button>
                </form>
                <div class="newsletter-status" id="newsletterStatus" aria-live="polite"></div>
            </div>
        </section>

        <?= view('user/partials/footer', ['websiteName' => $websiteName]) ?>
    </div>
    <script>
        (function () {
            const nav = document.querySelector('.top-nav');
            const menuBtn = document.getElementById('menuBtn');
            if (menuBtn && nav) {
                menuBtn.addEventListener('click', function () {
                    nav.classList.toggle('open');
                });
            }

            const track = document.getElementById('heroTrack');
            const dotsWrap = document.getElementById('heroDots');
            if (!track || !dotsWrap) {
                return;
            }

            const slides = Array.from(track.children);
            if (slides.length < 2) {
                return;
            }

            let current = 0;
            const dots = slides.map(function (_, i) {
                const dot = document.createElement('button');
                dot.className = 'hero-dot' + (i === 0 ? ' active' : '');
                dot.type = 'button';
                dot.addEventListener('click', function () {
                    current = i;
                    updateSlider();
                    restartAuto();
                });
                dotsWrap.appendChild(dot);
                return dot;
            });

            function updateSlider() {
                track.style.transform = 'translateX(-' + (current * 100) + '%)';
                dots.forEach(function (dot, i) {
                    dot.classList.toggle('active', i === current);
                });
            }

            let timer = null;
            function restartAuto() {
                if (timer) {
                    clearInterval(timer);
                }
                timer = setInterval(function () {
                    current = (current + 1) % slides.length;
                    updateSlider();
                }, 3500);
            }

            restartAuto();
        })();

        (function () {
            let popularDescriptionText = 'High quality kids product with safe materials and joyful design for daily use.';
            let templateCategoryLabels = [];
            let newsletterSuccessMessage = 'Email saved on this device.';
            const currencySymbol = <?= json_encode($currencySymbol) ?>;
            const cartKey = 'cart_items';
            const cartBadge = document.getElementById('cartBadge');

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderPopularProducts(products) {
                const grid = document.getElementById('popularGrid');
                if (!grid) {
                    return;
                }

                const items = Array.isArray(products) ? products.slice(0, 8) : [];
                if (items.length === 0) {
                    for (let i = 1; i <= 4; i++) {
                        items.push({ name: 'Popular Item ' + i, image_url: '', description: '', price: 0 });
                    }
                }

                grid.innerHTML = items.map(function (item) {
                    const id = Number(item?.id || 0);
                    const title = String(item?.name || 'Popular Item').trim() || 'Popular Item';
                    const imageUrl = String(item?.image_url || '').trim();
                    const description = String(item?.description || '').trim() || popularDescriptionText;
                    const price = Number(item?.price || 0);
                    const priceText = formatPrice(price);
                    const href = <?= json_encode(base_url('user/product')) ?> + '/' + id;

                    const media = imageUrl !== ''
                        ? '<img class="product-thumb" src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(title) + '">'
                        : '<div class="product-fallback">' + escapeHtml(title) + '</div>';

                    return '' +
                        '<article class="product">' +
                            '<a class="product-link" href="' + escapeHtml(href) + '">' +
                                media +
                            '</a>' +
                            '<div class="product-body">' +
                                '<a class="product-link" href="' + escapeHtml(href) + '">' +
                                    '<h3 class="product-title">' + escapeHtml(title) + '</h3>' +
                                '</a>' +
                                '<p class="product-desc">' + escapeHtml(description) + '</p>' +
                                '<div class="product-actions">' +
                                    '<span class="product-price">' + escapeHtml(priceText) + '</span>' +
                                    '<button class="product-btn add-to-cart-btn" type="button" data-id="' + id + '" data-name="' + escapeHtml(title) + '" data-price="' + price + '" data-image="' + escapeHtml(imageUrl) + '">Add to Cart</button>' +
                                '</div>' +
                            '</div>' +
                        '</article>';
                }).join('');
            }

            function normalizeCategoryItem(item, index) {
                if (item && typeof item === 'object') {
                    const id = Number(item.id || 0);
                    const name = String(item.name || '').trim();
                    if (name !== '') {
                        return { id: id > 0 ? id : 0, name: name };
                    }
                }
                const label = String(item || '').trim();
                if (label !== '') {
                    return { id: 0, name: label };
                }
                return null;
            }

            function renderCategories(itemsInput) {
                const grid = document.getElementById('categoriesGrid');
                if (!grid) {
                    return;
                }

                const items = Array.isArray(itemsInput)
                    ? itemsInput.map(normalizeCategoryItem).filter(Boolean)
                    : [];

                if (items.length === 0) {
                    items.push(
                        { id: 0, name: 'Plumbing & Repair' },
                        { id: 0, name: 'Art and Creativity' },
                        { id: 0, name: 'Hobby & Sport' },
                        { id: 0, name: 'Games & Puzzles' }
                    );
                }

                grid.innerHTML = items.slice(0, 12).map(function (item, index) {
                    const label = item.name || '';
                    const id = item.id || 0;
                    return '' +
                        '<article class="cat-card ' + (index === 0 ? 'active' : '') + '" data-category-id="' + escapeHtml(id) + '" data-category-name="' + escapeHtml(label) + '">' +
                            '<p class="cat-title">' + escapeHtml(label) + '</p>' +
                        '</article>';
                }).join('');
            }

            function setCssVar(name, value) {
                if (typeof value !== 'string' || value.trim() === '') {
                    return;
                }
                document.documentElement.style.setProperty(name, value.trim());
            }

            function setCssSizeVar(name, value) {
                if (typeof value !== 'string') {
                    return;
                }
                const v = value.trim();
                if (/^\d+(\.\d+)?(px|rem|em|%|vh|vw)$/.test(v)) {
                    document.documentElement.style.setProperty(name, v);
                }
            }

            function setCssNumberVar(name, value, min, max) {
                const parsed = parseInt(String(value || '').trim(), 10);
                if (!Number.isFinite(parsed)) {
                    return;
                }
                document.documentElement.style.setProperty(name, String(Math.max(min, Math.min(max, parsed))));
            }

            function setCssTextVar(name, value) {
                if (typeof value !== 'string') {
                    return;
                }
                const v = value.trim();
                if (v !== '' && !/[{}<>;]/.test(v)) {
                    document.documentElement.style.setProperty(name, v);
                }
            }

            function setCustomCss(value) {
                if (typeof value !== 'string' || value.trim() === '') {
                    return;
                }
                let style = document.getElementById('dashboardTemplateCustomCss');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'dashboardTemplateCustomCss';
                    document.head.appendChild(style);
                }
                style.textContent = value;
            }

            function setText(id, value) {
                if (typeof value !== 'string' || value.trim() === '') {
                    return;
                }
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = value;
                }
            }

            function setPlaceholder(id, value) {
                if (typeof value !== 'string' || value.trim() === '') {
                    return;
                }
                const el = document.getElementById(id);
                if (el) {
                    el.setAttribute('placeholder', value);
                }
            }

            function setContentImage(id, value, altText) {
                if (typeof value !== 'string' || value.trim() === '') {
                    return;
                }
                const container = document.getElementById(id);
                if (!container) {
                    return;
                }
                const image = document.createElement('img');
                image.src = value.trim();
                image.alt = altText;
                container.replaceChildren(image);
            }

            function setNodeListText(selector, values) {
                if (!Array.isArray(values) || values.length === 0) {
                    return;
                }

                const nodes = document.querySelectorAll(selector);
                if (!nodes.length) {
                    return;
                }

                nodes.forEach(function (node, index) {
                    const value = values[index];
                    if (typeof value === 'string' && value.trim() !== '') {
                        node.textContent = value;
                    }
                });
            }

            function setNodeListTextSingle(selector, value) {
                if (typeof value !== 'string' || value.trim() === '') {
                    return;
                }

                document.querySelectorAll(selector).forEach(function (node) {
                    node.textContent = value;
                });
            }

            function setLayerVisible(selector, isVisible) {
                document.querySelectorAll(selector).forEach(function (node) {
                    node.style.display = isVisible ? '' : 'none';
                });
            }

            function renderTestimonials(items) {
                const grid = document.getElementById('testimonialsGrid');
                if (!grid || !Array.isArray(items)) {
                    return;
                }
                grid.innerHTML = items.slice(0, 6).map(function (item) {
                    return '<article class="testimonial-card">' +
                        '<div class="testimonial-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>' +
                        '<p class="testimonial-quote">&ldquo;' + escapeHtml(String(item?.quote || '')) + '&rdquo;</p>' +
                        '<strong class="testimonial-name">' + escapeHtml(String(item?.name || 'Customer')) + '</strong>' +
                        '<span class="testimonial-role">' + escapeHtml(String(item?.role || 'Verified customer')) + '</span>' +
                    '</article>';
                }).join('');
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

            function formatPrice(value) {
                const amount = Number(value || 0).toLocaleString();
                if (/[A-Za-z]$/.test(currencySymbol)) {
                    return currencySymbol + ' ' + amount;
                }
                return currencySymbol + amount;
            }

            function applyTemplate(t) {
                    if (!t || typeof t !== 'object') {
                        return;
                    }
                    const brand = t?.branding || {};
                    const brandNode = document.querySelector('.brand');
                    if (brandNode) {
                        const brandName = typeof brand.website_name === 'string' && brand.website_name.trim() !== ''
                            ? brand.website_name.trim()
                            : null;
                        const logoUrl = typeof brand.logo_url === 'string' && brand.logo_url.trim() !== ''
                            ? brand.logo_url.trim()
                            : null;

                        if (brandName) {
                            brandNode.setAttribute('aria-label', brandName);
                        }

                        if (logoUrl) {
                            let logoImg = brandNode.querySelector('img');
                            if (!logoImg) {
                                brandNode.textContent = '';
                                logoImg = document.createElement('img');
                                brandNode.appendChild(logoImg);
                            }
                            logoImg.src = logoUrl;
                            logoImg.alt = brandName || brandNode.getAttribute('aria-label') || 'Brand';
                        } else if (brandName) {
                            brandNode.textContent = brandName;
                        }
                    }

                    setCssVar('--page-bg', t?.design?.page_bg);
                    setCssVar('--surface', t?.design?.surface);
                    setCssVar('--ink', t?.design?.text_color);
                    setCssVar('--muted', t?.design?.muted_text);
                    setCssVar('--hero-a', t?.design?.hero_gradient_start);
                    setCssVar('--hero-b', t?.design?.hero_gradient_end);
                    setCssVar('--accent', t?.design?.accent);
                    setCssTextVar('--font-main', t?.design?.font_family);
                    setCssSizeVar('--content-max', t?.design?.content_max_width);
                    setCssSizeVar('--radius-xl', t?.design?.radius_xl);
                    setCssSizeVar('--radius-lg', t?.design?.radius_lg);
                    setCssSizeVar('--radius-md', t?.design?.radius_md);
                    setCssTextVar('--shadow-soft', t?.design?.shadow_soft);
                    setCssSizeVar('--hero-min-height', t?.design?.hero_min_height);
                    setCssNumberVar('--product-columns', t?.design?.product_columns, 1, 6);
                    setCustomCss(t?.design?.custom_css);

                    setText('navHome', t?.nav?.home);
                    setText('navCourses', t?.nav?.courses);
                    setText('navContacts', t?.nav?.contacts);
                    setText('navAbout', t?.nav?.about);
                    setText('navRegister', t?.nav?.register);
                    setText('navLogin', t?.nav?.login);

                    setText('heroTitle', t?.hero?.title);
                    setText('heroSubtitle', t?.hero?.subtitle);
                    setPlaceholder('heroSearchInput', t?.hero?.search_placeholder);
                    if (typeof t?.hero?.background_image === 'string' && t.hero.background_image.trim() !== '') {
                        const hero = document.querySelector('.hero');
                        if (hero) {
                            const bg = t.hero.background_image.trim().replace(/"/g, '\\"');
                            hero.style.backgroundImage = 'linear-gradient(120deg, rgba(30,55,82,0.45), rgba(30,55,82,0.22)), url("' + bg + '")';
                            hero.style.backgroundSize = 'cover';
                            hero.style.backgroundPosition = 'center';
                        }
                    }

                    setText('categoriesTitle', t?.sections?.categories_title);
                    setText('categoriesLink', t?.sections?.categories_link);
                    setText('popularTitle', t?.sections?.popular_title);
                    setText('popularLink', t?.sections?.popular_link);
                    templateCategoryLabels = Array.isArray(t?.sections?.categories) ? t.sections.categories : templateCategoryLabels;
                    renderCategories(templateCategoryLabels);
                    popularDescriptionText = (typeof t?.sections?.popular_description === 'string' && t.sections.popular_description.trim() !== '')
                        ? t.sections.popular_description.trim()
                        : popularDescriptionText;
                    setNodeListTextSingle('.product-desc', t?.sections?.popular_description);

                    setText('businessTitle', t?.business?.title);
                    setText('businessDescription', t?.business?.description);
                    setText('businessServicesTitle', t?.business?.services_title);
                    setText('businessAltTitle', t?.business_alt?.title);
                    setText('businessAltDescription', t?.business_alt?.description);
                    setContentImage('businessMainImage', t?.business?.image_url, 'Business collaboration');
                    setContentImage('businessAltImage', t?.business_alt?.image_url, 'Business execution');

                    const services = Array.isArray(t?.business?.services) ? t.business.services : [];
                    if (services[0]) {
                        setText('service1Title', services[0].title);
                        setText('service1Description', services[0].description);
                    }
                    if (services[1]) {
                        setText('service2Title', services[1].title);
                        setText('service2Description', services[1].description);
                    }
                    if (services[2]) {
                        setText('service3Title', services[2].title);
                        setText('service3Description', services[2].description);
                    }

                    setText('aboutTitle', t?.about?.title);
                    setText('aboutDescription', t?.about?.description);

                    setText('testimonialsTitle', t?.testimonials?.title);
                    setText('testimonialsSubtitle', t?.testimonials?.subtitle);
                    renderTestimonials(Array.isArray(t?.testimonials?.items) ? t.testimonials.items : []);

                    setText('newsletterTitle', t?.newsletter?.title);
                    setText('newsletterDescription', t?.newsletter?.description);
                    setText('newsletterButton', t?.newsletter?.button_text);
                    setPlaceholder('newsletterEmail', t?.newsletter?.placeholder);
                    if (typeof t?.newsletter?.success_message === 'string' && t.newsletter.success_message.trim() !== '') {
                        newsletterSuccessMessage = t.newsletter.success_message.trim();
                    }

                    setText('footerCol1Title', t?.footer?.column1_title);
                    setText('footerCol2Title', t?.footer?.column2_title);
                    setText('footerCol3Title', t?.footer?.column3_title);

                    const c1 = Array.isArray(t?.footer?.column1_links) ? t.footer.column1_links : [];
                    const c2 = Array.isArray(t?.footer?.column2_links) ? t.footer.column2_links : [];
                    if (c1[0]) setText('footerCol1Link1', c1[0]);
                    if (c1[1]) setText('footerCol1Link2', c1[1]);
                    if (c1[2]) setText('footerCol1Link3', c1[2]);
                    if (c2[0]) setText('footerCol2Link1', c2[0]);
                    if (c2[1]) setText('footerCol2Link2', c2[1]);
                    if (c2[2]) setText('footerCol2Link3', c2[2]);

                    if (t?.footer?.email) setText('footerEmail', 'Email: ' + t.footer.email);
                    if (t?.footer?.phone) setText('footerPhone', 'Phone: ' + t.footer.phone);
                    if (t?.footer?.copyright) {
                        const resolved = t.footer.copyright.replace('{year}', String(new Date().getFullYear()));
                        setText('footerCopyright', resolved);
                    }

                    if (t?.layers && typeof t.layers === 'object') {
                        setLayerVisible('.top-nav', t.layers.header !== false);
                        setLayerVisible('.hero', t.layers.hero !== false);
                        setLayerVisible('.section-head, .categories, .popular-grid, .desc-box', t.layers.section !== false);
                        setLayerVisible('#businessSectionMain', t.layers.business !== false);
                        setLayerVisible('#businessSectionAlt', t.layers.business_alt !== false);
                        setLayerVisible('#testimonialsSection', t.layers.testimonials !== false);
                        setLayerVisible('#newsletterSection', t.layers.newsletter !== false);
                        setLayerVisible('.footer', t.layers.footer !== false);
                    }
                }

            const initialTemplate = <?= json_encode(($dashboardTemplate ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;

            const newsletterForm = document.getElementById('newsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const email = document.getElementById('newsletterEmail');
                    const status = document.getElementById('newsletterStatus');
                    if (!email || !email.checkValidity()) {
                        if (email) email.reportValidity();
                        return;
                    }
                    try {
                        const storageKey = 'dashboard_newsletter_emails';
                        const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
                        const emails = Array.isArray(saved) ? saved : [];
                        const value = email.value.trim().toLowerCase();
                        if (value !== '' && !emails.includes(value)) {
                            emails.push(value);
                            localStorage.setItem(storageKey, JSON.stringify(emails));
                        }
                    } catch (e) {
                        // The success message still confirms the local interaction.
                    }
                    if (status) status.textContent = newsletterSuccessMessage;
                    email.value = '';
                });
            }

            function apiGet(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Api-Token': restApiToken
                    }
                });
            }

            function setActiveCategoryCard(card) {
                const grid = document.getElementById('categoriesGrid');
                if (!grid) {
                    return;
                }
                grid.querySelectorAll('.cat-card').forEach(function (node) {
                    node.classList.toggle('active', node === card);
                });
            }

            function loadDashboardProducts(categoryId) {
                const baseUrl = <?= json_encode(base_url('rest_api/dashboard-products')) ?>;
                const params = new URLSearchParams();
                params.set('limit', '8');
                if (Number(categoryId) > 0) {
                    params.set('category_id', String(Number(categoryId)));
                }
                apiGet(baseUrl + '?' + params.toString())
                    .then(function (res) {
                        return res.json();
                    })
                    .then(function (payload) {
                        if (!payload || !payload.status) {
                            return;
                        }
                        renderPopularProducts(payload.products || []);
                    })
                    .catch(function () {
                        // keep fallback product cards
                    });
            }

            applyTemplate(initialTemplate);
            renderCategories(templateCategoryLabels);
            renderPopularProducts([]);
            loadDashboardProducts(0);
            updateCartBadge();

            apiGet("<?= base_url('rest_api/dashboard-categories?limit=10') ?>")
                .then(function (res) {
                    return res.json();
                })
                .then(function (payload) {
                    if (!payload || !payload.status || !Array.isArray(payload.categories)) {
                        return;
                    }
                    if (payload.categories.length > 0) {
                        renderCategories(payload.categories);
                    }
                })
                .catch(function () {
                    // keep template/fallback categories
                });

            const categoriesGrid = document.getElementById('categoriesGrid');
            if (categoriesGrid) {
                categoriesGrid.addEventListener('click', function (event) {
                    const card = event.target.closest('.cat-card');
                    if (!card) {
                        return;
                    }
                    setActiveCategoryCard(card);
                    const categoryId = Number(card.getAttribute('data-category-id') || 0);
                    loadDashboardProducts(categoryId);
                });
            }

            const popularGrid = document.getElementById('popularGrid');
            if (popularGrid) {
                popularGrid.addEventListener('click', function (event) {
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
    </script>
</body>
</html>
