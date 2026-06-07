<?php
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$nav = is_array($pageTemplate['nav'] ?? null) ? $pageTemplate['nav'] : [];
$contactPage = is_array($pageTemplate['contact_page'] ?? null) ? $pageTemplate['contact_page'] : [];
$submitText = (string) ($contactPage['submit_text'] ?? 'Send Message');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc((string) ($nav['contacts'] ?? 'Contacts')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@700&family=Urbanist:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f0f5f2;
            --surface: #ffffff;
            --ink: #18252d;
            --muted: #5e6b73;
            --line: #dce6df;
            --accent: #1c8a6b;
            --accent-2: #f2a65a;
            --shadow: 0 16px 32px rgba(18, 34, 44, 0.1);
            --radius-xl: 26px;
            --radius-lg: 18px;
            --radius-md: 12px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Urbanist", sans-serif;
            background: radial-gradient(circle at top right, #f7fbf8 0%, var(--bg) 50%);
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
            font-family: "Libre Baskerville", serif;
            font-size: 1.35rem;
            color: #1d2a2b;
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
            background: linear-gradient(135deg, #1c8a6b, #3fb08d);
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
            grid-template-columns: 1.05fr 0.95fr;
            gap: 18px;
        }

        .hero-copy {
            background: var(--surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--line);
            padding: 26px;
            box-shadow: var(--shadow);
        }

        .hero-title {
            margin: 0;
            font-family: "Libre Baskerville", serif;
            font-size: 2.6rem;
        }

        .hero-sub {
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .info-card {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f4f8f6;
            border: 1px solid #e1ebe4;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
        }

        .info-row i {
            color: var(--accent);
        }

        .form-card {
            background: #fff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--line);
            padding: 22px;
            box-shadow: var(--shadow);
        }

        .form-title {
            margin: 0 0 12px;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field label {
            font-size: 0.85rem;
            font-weight: 800;
        }

        .field input,
        .field textarea {
            border: 1px solid #d8e0eb;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fbfcfe;
        }

        .field textarea {
            min-height: 130px;
            resize: vertical;
        }

        .field input:focus,
        .field textarea:focus {
            outline: 2px solid rgba(28, 138, 107, 0.2);
            border-color: #8fd0bc;
        }

        .field-error {
            color: #c43b2b;
            font-size: 0.78rem;
            min-height: 1em;
        }

        .full {
            grid-column: 1 / -1;
        }

        .actions {
            margin-top: 14px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
        }

        .btn.primary {
            background: linear-gradient(135deg, #1c8a6b, #3fb08d);
            color: #fff;
        }

        .btn.ghost {
            background: #f4f6fb;
            color: #22334b;
            border: 1px solid #dbe4ef;
        }

        .message {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #e9f7f2;
            border: 1px solid #cfeae0;
            color: #155a46;
            font-size: 0.9rem;
            display: none;
        }

        .message.error {
            background: #fff1f0;
            border-color: #f3c1bb;
            color: #8c1c13;
        }

        @media (max-width: 980px) {
            .hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .top-nav {
                align-items: flex-start;
                flex-direction: column;
            }
            .hero-title {
                font-size: 2.2rem;
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
                <h1 class="hero-title"><?= esc((string) ($contactPage['hero_title'] ?? 'Let us talk.')) ?></h1>
                <p class="hero-sub"><?= esc((string) ($contactPage['hero_description'] ?? '')) ?></p>
                <div class="info-card">
                    <div class="info-row"><i class="fa-solid fa-envelope"></i> <?= esc((string) ($contactPage['email'] ?? 'support@child.com')) ?></div>
                    <div class="info-row"><i class="fa-solid fa-phone"></i> <?= esc((string) ($contactPage['phone'] ?? '+91 90000 00000')) ?></div>
                    <div class="info-row"><i class="fa-solid fa-location-dot"></i> <?= esc((string) ($contactPage['address'] ?? '')) ?></div>
                </div>
            </div>

            <div class="form-card">
                <h2 class="form-title"><?= esc((string) ($contactPage['form_title'] ?? 'Send a message')) ?></h2>
                <form id="contactForm" action="<?= base_url('user/contact/submit') ?>" method="post">
                    <div class="grid">
                        <div class="field">
                            <label for="name"><?= esc((string) ($contactPage['name_label'] ?? 'Full Name')) ?></label>
                            <input id="name" name="name" type="text" required>
                            <small id="errorName" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="email"><?= esc((string) ($contactPage['email_label'] ?? 'Email')) ?></label>
                            <input id="email" name="email" type="email" required>
                            <small id="errorEmail" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="phone"><?= esc((string) ($contactPage['phone_label'] ?? 'Phone')) ?></label>
                            <input id="phone" name="phone" type="text" placeholder="<?= esc((string) ($contactPage['phone_placeholder'] ?? 'Optional')) ?>">
                            <small id="errorPhone" class="field-error"></small>
                        </div>
                        <div class="field full">
                            <label for="message"><?= esc((string) ($contactPage['message_label'] ?? 'Message')) ?></label>
                            <textarea id="message" name="message" required></textarea>
                            <small id="errorMessage" class="field-error"></small>
                        </div>
                    </div>
                    <div class="actions">
                        <button id="sendBtn" class="btn primary" type="submit"><?= esc($submitText) ?></button>
                        <button id="resetBtn" class="btn ghost" type="button"><?= esc((string) ($contactPage['reset_text'] ?? 'Reset')) ?></button>
                    </div>
                    <div id="formMessage" class="message"></div>
                </form>
            </div>
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

        (function () {
            const form = document.getElementById('contactForm');
            const sendBtn = document.getElementById('sendBtn');
            const resetBtn = document.getElementById('resetBtn');
            const messageBox = document.getElementById('formMessage');
            const submitText = <?= json_encode($submitText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

            function clearErrors() {
                ['Name', 'Email', 'Phone', 'Message'].forEach(function (key) {
                    const el = document.getElementById('error' + key);
                    if (el) {
                        el.textContent = '';
                    }
                });
            }

            function setMessage(text, isError) {
                if (!messageBox) {
                    return;
                }
                messageBox.textContent = text || '';
                if (!text) {
                    messageBox.style.display = 'none';
                    messageBox.classList.remove('error');
                    return;
                }
                messageBox.style.display = 'block';
                messageBox.classList.toggle('error', !!isError);
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    form.reset();
                    setMessage('', false);
                    clearErrors();
                });
            }

            if (!form) {
                return;
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearErrors();
                setMessage('', false);

                const formData = new FormData(form);
                sendBtn.disabled = true;
                sendBtn.textContent = 'Sending...';

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (payload) {
                        if (!payload || !payload.status) {
                            if (payload && payload.errors) {
                                if (payload.errors.name) document.getElementById('errorName').textContent = payload.errors.name;
                                if (payload.errors.email) document.getElementById('errorEmail').textContent = payload.errors.email;
                                if (payload.errors.phone) document.getElementById('errorPhone').textContent = payload.errors.phone;
                                if (payload.errors.message) document.getElementById('errorMessage').textContent = payload.errors.message;
                            }
                            setMessage(payload && payload.message ? payload.message : 'Unable to send your message.', true);
                            return;
                        }

                        setMessage(payload.message || 'Message sent.', false);
                        form.reset();
                    })
                    .catch(function () {
                        setMessage('Unable to send your message right now.', true);
                    })
                    .finally(function () {
                        sendBtn.disabled = false;
                        sendBtn.textContent = submitText;
                    });
            });
        })();
    </script>
</body>
</html>
