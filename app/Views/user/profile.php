<?php
$websiteName = trim((string) (($branding['website_name'] ?? 'child.com')));
$logoUrl = trim((string) (($branding['logo_url'] ?? '')));
$profileName = trim((string) (($profile['name'] ?? '')));
$profileEmail = trim((string) (($profile['email'] ?? '')));
$profilePhone = trim((string) (($profile['phone'] ?? '')));
$profileImage = trim((string) (($profile['image_url'] ?? '')));
$profileAddressLine1 = trim((string) (($profile['address_line1'] ?? '')));
$profileAddressLine2 = trim((string) (($profile['address_line2'] ?? '')));
$profileCity = trim((string) (($profile['city'] ?? '')));
$profileState = trim((string) (($profile['state'] ?? '')));
$profilePostalCode = trim((string) (($profile['postal_code'] ?? '')));
$profileCountry = trim((string) (($profile['country'] ?? '')));
$profileAddressParts = array_filter([
    $profileAddressLine1,
    $profileAddressLine2,
    $profileCity,
    $profileState,
    $profilePostalCode,
    $profileCountry,
], static fn ($value) => $value !== '');
$profileAddressText = !empty($profileAddressParts) ? implode(', ', $profileAddressParts) : 'Not set';
$roleId = (int) (session()->get('us_role_id') ?? 0);
$roleLabel = $roleId === 2 ? 'Customer' : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
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
            --card-shadow: 0 14px 30px rgba(21, 39, 71, 0.08);
            --radius-xl: 26px;
            --radius-lg: 18px;
            --radius-md: 12px;
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
            box-shadow: var(--card-shadow);
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
        }

        .brand {
            font-family: "Fraunces", serif;
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

        .page-title {
            margin: 26px 0 6px;
            font-size: 2.3rem;
            font-family: "Fraunces", serif;
        }

        .page-sub {
            margin: 0 0 20px;
            color: var(--muted);
            font-size: 0.98rem;
        }

        .layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 18px;
        }

        .panel {
            background: var(--surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--line);
            box-shadow: var(--card-shadow);
        }

        .profile-card {
            padding: 18px;
            display: grid;
            gap: 14px;
        }

        .profile-hero {
            background: linear-gradient(140deg, rgba(30, 126, 215, 0.18), rgba(249, 115, 63, 0.16));
            border-radius: 20px;
            padding: 18px;
            display: grid;
            gap: 12px;
            justify-items: center;
        }

        .avatar-wrap {
            width: 108px;
            height: 108px;
            border-radius: 999px;
            background: #fff;
            padding: 4px;
            border: 1px solid #dbe4ef;
        }

        .avatar-wrap img {
            width: 100%;
            height: 100%;
            border-radius: 999px;
            object-fit: cover;
        }

        .profile-name {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .profile-email {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .profile-meta {
            display: grid;
            gap: 10px;
        }
        .order-link {
            margin-top: 10px;
        }

        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #2c3b52;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f6f8fb;
            border: 1px solid #e4e9f2;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            border-radius: 999px;
            padding: 4px 10px;
            border: 1px solid #dbe4ef;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .form-panel {
            padding: 22px;
        }

        .form-title {
            margin: 0 0 12px;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field label {
            font-size: 0.85rem;
            font-weight: 800;
            color: #243149;
        }

        .field input {
            border: 1px solid #d8e0eb;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fbfcfe;
        }
        .field textarea {
            border: 1px solid #d8e0eb;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fbfcfe;
            min-height: 96px;
            resize: vertical;
        }

        .field input:focus {
            outline: 2px solid rgba(30, 126, 215, 0.25);
            border-color: #7bb7ef;
        }
        .field textarea:focus {
            outline: 2px solid rgba(30, 126, 215, 0.25);
            border-color: #7bb7ef;
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
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
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
            background: linear-gradient(130deg, #1e7ed7, #56b0f1);
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
            background: #eef6ff;
            border: 1px solid #d6e6f8;
            color: #1d3d64;
            font-size: 0.9rem;
            display: none;
        }

        .message.error {
            background: #fff1f0;
            border-color: #f3c1bb;
            color: #8c1c13;
        }

        @media (max-width: 980px) {
            .layout {
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
                    <img id="navProfileImage" src="<?= esc($profileImage !== '' ? $profileImage : base_url('uploads/customers/default-user.svg')) ?>" alt="Profile">
                    <span id="navProfileName"><?= esc($profileName !== '' ? $profileName : 'Profile') ?></span>
                </a>
                <a id="navLogout" class="nav-btn" href="<?= base_url('logout') ?>" style="display:none;">Logout</a>
            </nav>
        </header>

        <h1 class="page-title">Your Profile</h1>
        <p class="page-sub">Update your personal details and manage account security.</p>

        <div class="layout">
            <section class="panel profile-card">
                <div class="profile-hero">
                    <div class="avatar-wrap">
                        <img id="profileAvatar" src="<?= esc($profileImage !== '' ? $profileImage : base_url('uploads/customers/default-user.svg')) ?>" alt="Profile">
                    </div>
                    <div>
                        <h2 id="profileNameText" class="profile-name"><?= esc($profileName !== '' ? $profileName : 'User') ?></h2>
                        <p id="profileEmailText" class="profile-email"><?= esc($profileEmail !== '' ? $profileEmail : 'email@domain.com') ?></p>
                    </div>
                </div>
                <div class="profile-meta">
                    <div class="meta-row">
                        <span>Account ID</span>
                        <span class="badge"><i class="fa-solid fa-id-badge"></i>#<?= esc((string) ($profile['id'] ?? 0)) ?></span>
                    </div>
                    <div class="meta-row">
                        <span>Role</span>
                        <span class="badge"><i class="fa-solid fa-shield"></i><?= esc($roleLabel) ?></span>
                    </div>
                    <div class="meta-row">
                        <span>Phone</span>
                        <span id="profilePhoneText"><?= esc($profilePhone !== '' ? $profilePhone : 'Not set') ?></span>
                    </div>
                    <div class="meta-row">
                        <span>Address</span>
                        <span id="profileAddressText"><?= esc($profileAddressText) ?></span>
                    </div>
                </div>
                <div class="order-link">
                    <a class="btn ghost" href="<?= base_url('user/orders') ?>">View My Orders</a>
                </div>
            </section>

            <section class="panel form-panel">
                <h2 class="form-title">Profile Details</h2>
                <form id="profileForm" action="<?= base_url('user/profile/update') ?>" method="post" enctype="multipart/form-data">
                    <div class="grid">
                        <div class="field">
                            <label for="name">Full Name</label>
                            <input id="name" name="name" type="text" value="<?= esc($profileName) ?>" required>
                            <small id="errorName" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="<?= esc($profileEmail) ?>" required>
                            <small id="errorEmail" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" type="text" value="<?= esc($profilePhone) ?>" placeholder="Enter phone">
                            <small id="errorPhone" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="password">New Password</label>
                            <input id="password" name="password" type="password" placeholder="Leave blank to keep">
                            <small id="errorPassword" class="field-error"></small>
                        </div>
                        <div class="field full">
                            <label for="profile_image">Profile Image</label>
                            <input id="profile_image" name="profile_image" type="file" accept="image/*">
                            <small id="errorProfileImage" class="field-error"></small>
                        </div>
                        <div class="field full">
                            <label for="address_line1">Address Line 1</label>
                            <input id="address_line1" name="address_line1" type="text" value="<?= esc($profileAddressLine1) ?>" placeholder="Street address">
                            <small id="errorAddressLine1" class="field-error"></small>
                        </div>
                        <div class="field full">
                            <label for="address_line2">Address Line 2</label>
                            <input id="address_line2" name="address_line2" type="text" value="<?= esc($profileAddressLine2) ?>" placeholder="Apartment, suite, etc.">
                            <small id="errorAddressLine2" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="city">City</label>
                            <input id="city" name="city" type="text" value="<?= esc($profileCity) ?>" placeholder="City">
                            <small id="errorCity" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="state">State</label>
                            <input id="state" name="state" type="text" value="<?= esc($profileState) ?>" placeholder="State">
                            <small id="errorState" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="postal_code">Postal Code</label>
                            <input id="postal_code" name="postal_code" type="text" value="<?= esc($profilePostalCode) ?>" placeholder="ZIP / Postal Code">
                            <small id="errorPostalCode" class="field-error"></small>
                        </div>
                        <div class="field">
                            <label for="country">Country</label>
                            <input id="country" name="country" type="text" value="<?= esc($profileCountry) ?>" placeholder="Country">
                            <small id="errorCountry" class="field-error"></small>
                        </div>
                    </div>

                    <div class="actions">
                        <button id="saveBtn" class="btn primary" type="submit">Save Changes</button>
                        <button id="resetBtn" class="btn ghost" type="button">Reset</button>
                    </div>
                    <div id="formMessage" class="message"></div>
                </form>
            </section>
        </div>

        <!-- <?//= view('user/partials/footer', ['websiteName' => $websiteName]) ?> -->
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
            const form = document.getElementById('profileForm');
            const saveBtn = document.getElementById('saveBtn');
            const resetBtn = document.getElementById('resetBtn');
            const messageBox = document.getElementById('formMessage');
            const avatar = document.getElementById('profileAvatar');
            const navAvatar = document.getElementById('navProfileImage');
            const navName = document.getElementById('navProfileName');
            const nameText = document.getElementById('profileNameText');
            const emailText = document.getElementById('profileEmailText');
            const phoneText = document.getElementById('profilePhoneText');
            const addressText = document.getElementById('profileAddressText');
            const imageInput = document.getElementById('profile_image');

            function clearErrors() {
                ['Name', 'Email', 'Phone', 'Password', 'ProfileImage', 'AddressLine1', 'AddressLine2', 'City', 'State', 'PostalCode', 'Country'].forEach(function (key) {
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

            if (imageInput) {
                imageInput.addEventListener('change', function () {
                    const file = imageInput.files && imageInput.files[0];
                    if (!file) {
                        return;
                    }
                    const url = URL.createObjectURL(file);
                    if (avatar) avatar.src = url;
                });
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
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';

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
                            if (payload.errors.password) document.getElementById('errorPassword').textContent = payload.errors.password;
                            if (payload.errors.profile_image) document.getElementById('errorProfileImage').textContent = payload.errors.profile_image;
                            if (payload.errors.address_line1) document.getElementById('errorAddressLine1').textContent = payload.errors.address_line1;
                            if (payload.errors.address_line2) document.getElementById('errorAddressLine2').textContent = payload.errors.address_line2;
                            if (payload.errors.city) document.getElementById('errorCity').textContent = payload.errors.city;
                            if (payload.errors.state) document.getElementById('errorState').textContent = payload.errors.state;
                            if (payload.errors.postal_code) document.getElementById('errorPostalCode').textContent = payload.errors.postal_code;
                            if (payload.errors.country) document.getElementById('errorCountry').textContent = payload.errors.country;
                        }
                        setMessage(payload && payload.message ? payload.message : 'Unable to update profile.', true);
                        return;
                    }

                        setMessage(payload.message || 'Profile updated.', false);
                        if (payload.user) {
                            if (nameText && payload.user.name) nameText.textContent = payload.user.name;
                            if (emailText && payload.user.email) emailText.textContent = payload.user.email;
                            if (phoneText && typeof payload.user.phone === 'string') {
                                phoneText.textContent = payload.user.phone !== '' ? payload.user.phone : 'Not set';
                            }
                            if (addressText) {
                                const addressParts = [
                                    payload.user.address_line1,
                                    payload.user.address_line2,
                                    payload.user.city,
                                    payload.user.state,
                                    payload.user.postal_code,
                                    payload.user.country
                                ].map(function (val) { return String(val || '').trim(); }).filter(Boolean);
                                addressText.textContent = addressParts.length > 0 ? addressParts.join(', ') : 'Not set';
                            }
                            if (payload.user.image_url) {
                                if (avatar) avatar.src = payload.user.image_url;
                                if (navAvatar) navAvatar.src = payload.user.image_url;
                            }
                            if (navName && payload.user.name) navName.textContent = payload.user.name;
                        }
                        form.querySelector('#password').value = '';
                    })
                    .catch(function () {
                        setMessage('Unable to update profile right now.', true);
                    })
                    .finally(function () {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Save Changes';
                    });
            });
        })();
    </script>
</body>
</html>
