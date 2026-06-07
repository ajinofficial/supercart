<?php
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$nav = is_array($pageTemplate['nav'] ?? null) ? $pageTemplate['nav'] : [];
$aboutPage = is_array($pageTemplate['about_page'] ?? null) ? $pageTemplate['about_page'] : [];
$aboutTags = is_array($aboutPage['tags'] ?? null) ? array_slice($aboutPage['tags'], 0, 3) : [];
$aboutStats = is_array($aboutPage['stats'] ?? null) ? array_slice($aboutPage['stats'], 0, 3) : [];
$aboutValues = is_array($aboutPage['values'] ?? null) ? array_slice($aboutPage['values'], 0, 3) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($nav['about'] ?? 'About')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f7f3ee;
            --surface: #ffffff;
            --ink: #1c2533;
            --muted: #5a6475;
            --line: #e3dcd3;
            --accent: #d97939;
            --accent-2: #2f6a67;
            --shadow: 0 16px 32px rgba(30, 40, 60, 0.08);
            --radius-xl: 28px;
            --radius-lg: 18px;
            --radius-md: 12px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Plus Jakarta Sans", sans-serif;
            background: radial-gradient(circle at top left, #fdf7f0 0%, var(--bg) 55%);
            color: var(--ink);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 18px 48px;
        }

        .top-nav {
            background: rgba(255, 255, 255, 0.95);
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
            font-family: "DM Serif Display", serif;
            font-size: 1.4rem;
            color: #2d2f35;
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
            background: linear-gradient(135deg, #d97939, #f0b05a);
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

        .hero {
            margin-top: 22px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 18px;
            align-items: stretch;
        }

        .hero-copy {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            padding: 28px;
            box-shadow: var(--shadow);
        }

        .hero-title {
            margin: 0;
            font-family: "DM Serif Display", serif;
            font-size: 3rem;
            line-height: 1;
        }

        .hero-sub {
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 1.02rem;
            line-height: 1.6;
        }

        .hero-tags {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            padding: 6px 12px;
            border-radius: 999px;
            background: #f4efe8;
            border: 1px solid #e6dfd7;
            font-weight: 700;
            font-size: 0.8rem;
            color: #4a4f5b;
        }

        .hero-card {
            background: linear-gradient(155deg, #2f6a67, #3f8b7f);
            color: #fff;
            border-radius: var(--radius-xl);
            padding: 24px;
            display: grid;
            gap: 12px;
            box-shadow: var(--shadow);
        }

        .hero-card h3 {
            margin: 0;
            font-size: 1.4rem;
        }

        .hero-card p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.55;
        }

        .stats {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .stat {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 16px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .stat h4 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--accent-2);
        }

        .stat p {
            margin: 6px 0 0;
            font-size: 0.85rem;
            color: var(--muted);
        }

        .story {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .story-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .story-card h3 {
            margin: 0 0 8px;
            font-size: 1.25rem;
        }

        .story-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .values {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .value {
            background: #fff7ef;
            border: 1px solid #f1e2d3;
            border-radius: var(--radius-lg);
            padding: 16px;
            box-shadow: var(--shadow);
        }

        .value i {
            color: var(--accent);
        }

        .value h4 {
            margin: 10px 0 6px;
            font-size: 1.05rem;
        }

        .value p {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 980px) {
            .hero {
                grid-template-columns: 1fr;
            }
            .stats {
                grid-template-columns: 1fr;
            }
            .story {
                grid-template-columns: 1fr;
            }
            .values {
                grid-template-columns: 1fr;
            }
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
            .hero-title {
                font-size: 2.4rem;
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
                <a href="<?= base_url('user/dashboard') ?>"><?= esc((string) ($nav['home'] ?? 'Home')) ?></a>
                <a href="<?= base_url('user/catalog') ?>"><?= esc((string) ($nav['courses'] ?? 'Courses')) ?></a>
                <a href="<?= base_url('user/contact') ?>"><?= esc((string) ($nav['contacts'] ?? 'Contacts')) ?></a>
                <a href="<?= base_url('user/about') ?>"><?= esc((string) ($nav['about'] ?? 'About')) ?></a>
                <a href="<?= base_url('user/profile') ?>">Profile</a>
                <a href="<?= base_url('user/cart') ?>"><i class="fa-solid fa-cart-shopping"></i></a>
                <?= view('user/partials/notification_bell') ?>
                <a id="navRegister" class="nav-btn primary" href="<?= base_url('register') ?>"><?= esc((string) ($nav['register'] ?? 'Register')) ?></a>
                <a id="navLogin" class="nav-btn" href="<?= base_url('login') ?>"><?= esc((string) ($nav['login'] ?? 'Login')) ?></a>
                <a id="navProfile" class="nav-profile" href="<?= base_url('user/profile') ?>">
                    <img id="navProfileImage" src="<?= esc(base_url('uploads/customers/default-user.svg')) ?>" alt="Profile">
                    <span id="navProfileName">Profile</span>
                </a>
                <a id="navLogout" class="nav-btn" href="<?= base_url('logout') ?>" style="display:none;">Logout</a>
            </nav>
        </header>

        <section class="hero">
            <div class="hero-copy">
                <h1 class="hero-title"><?= esc((string) ($aboutPage['hero_title'] ?? 'Built for joyful childhoods.')) ?></h1>
                <p class="hero-sub"><?= esc((string) ($aboutPage['hero_description'] ?? '')) ?></p>
                <div class="hero-tags">
                    <?php foreach ($aboutTags as $tag): ?>
                        <span class="tag"><?= esc((string) $tag) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="hero-card">
                <h3><?= esc((string) ($aboutPage['promise_title'] ?? 'Our Promise')) ?></h3>
                <p><?= esc((string) ($aboutPage['promise_text_1'] ?? '')) ?></p>
                <p><?= esc((string) ($aboutPage['promise_text_2'] ?? '')) ?></p>
            </div>
        </section>

        <section class="stats">
            <?php foreach ($aboutStats as $stat): ?>
                <div class="stat">
                    <h4><?= esc((string) ($stat['value'] ?? '')) ?></h4>
                    <p><?= esc((string) ($stat['label'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="story">
            <article class="story-card">
                <h3><?= esc((string) ($aboutPage['story_title'] ?? 'Our Story')) ?></h3>
                <p><?= esc((string) ($aboutPage['story_description'] ?? '')) ?></p>
            </article>
            <article class="story-card">
                <h3><?= esc((string) ($aboutPage['work_title'] ?? 'How We Work')) ?></h3>
                <p><?= esc((string) ($aboutPage['work_description'] ?? '')) ?></p>
            </article>
        </section>

        <section class="values">
            <?php $valueIcons = ['fa-heart', 'fa-leaf', 'fa-star']; ?>
            <?php foreach ($aboutValues as $index => $value): ?>
                <div class="value">
                    <i class="fa-solid <?= esc($valueIcons[$index] ?? 'fa-star') ?>"></i>
                    <h4><?= esc((string) ($value['title'] ?? '')) ?></h4>
                    <p><?= esc((string) ($value['description'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </section>

        <?= view('user/partials/footer', ['websiteName' => $websiteName]) ?>
    </div>

    <script>
        (function () {
            const restApiToken = <?= json_encode((string) ($restApiToken ?? '')) ?>;

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
                .catch(function () {
                    // keep default nav controls
                });
        })();
    </script>
</body>
</html>
