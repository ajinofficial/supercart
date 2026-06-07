<?php
$page = 'settings';
include('header.php');
include('menus.php');

$logoFile = trim((string) ($general['website_logo'] ?? ''));
$logoUrl = $logoFile !== '' ? base_url('uploads/settings/' . $logoFile) : '';
$authBackgroundFile = trim((string) ($general['auth_background_image'] ?? ''));
$authBackgroundUrl = $authBackgroundFile !== '' ? base_url('uploads/settings/' . $authBackgroundFile) : '';
$timezoneList = timezone_identifiers_list();
$currencyCatalog = is_array($currency_catalog ?? null) ? $currency_catalog : [];
$themeColorOptions = [
    '#0f6cad' => 'Ocean Blue',
    '#0ea5a4' => 'Teal',
    '#2563eb' => 'Royal Blue',
    '#16a34a' => 'Green',
    '#dc2626' => 'Red',
    '#f59e0b' => 'Amber',
    '#7c3aed' => 'Violet',
    '#111827' => 'Charcoal',
];
$fontFamilyOptions = [
    'inter' => 'Inter',
    'manrope' => 'Manrope',
    'poppins' => 'Poppins',
    'roboto' => 'Roboto',
    'open_sans' => 'Open Sans',
    'lato' => 'Lato',
    'nunito' => 'Nunito',
];
$selectedThemeColor = strtolower((string) ($general['theme_color'] ?? ($system['theme_color'] ?? '#0f6cad')));
$gatewayConfigured = !empty($payment['razorpay_key_id']) && !empty($payment['razorpay_key_secret']);
$emailConfigured = !empty($email['smtp_host']) && !empty($email['smtp_username']) && !empty($email['smtp_password']);
?>

<style>
    .settings-page {
        padding: 4px 4px 6px;
        width: 100%;
        display: grid;
        gap: 14px;
    }

    .settings-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        padding: 20px;
        border-radius: 17px;
        color: #fff;
        background: linear-gradient(125deg, var(--theme-color-darker), var(--theme-color), #2c9fb7);
        box-shadow: 0 14px 30px rgba(16, 65, 104, .2);
    }

    .settings-hero h2 { margin: 0; font-size: 1.25rem; font-weight: 800; }
    .settings-hero p { margin: 5px 0 0; color: rgba(255,255,255,.8); font-size: .82rem; }
    .settings-save-state {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid rgba(255,255,255,.25);
        border-radius: 999px;
        color: rgba(255,255,255,.9);
        background: rgba(255,255,255,.1);
        font-size: .72rem;
        font-weight: 800;
    }
    .settings-save-state i { width: 14px; height: 14px; }
    .settings-save-state.unsaved { background: rgba(245,158,11,.3); }

    .settings-health {
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 10px;
    }
    .settings-health-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 13px;
        border: 1px solid #e1e9f0;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 5px 16px rgba(24,42,67,.05);
    }
    .settings-health-icon {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        flex: 0 0 34px;
        border-radius: 10px;
        color: var(--theme-color);
        background: rgba(var(--theme-rgb),.1);
    }
    .settings-health-icon i { width: 17px; height: 17px; }
    .settings-health-item strong, .settings-health-item span { display: block; }
    .settings-health-item strong { color: #172033; font-size: .78rem; }
    .settings-health-item span { margin-top: 2px; color: #778397; font-size: .68rem; }

    .settings-card {
        border: 1px solid #e4edf5;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 8px 20px rgba(16, 65, 104, 0.07);
        display: flex;
        flex-direction: column;
    }

    .settings-card-header {
        display: none;
        border-bottom: 1px solid #edf2f6;
        padding: 14px 16px;
    }

    .settings-title {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 800;
        color: #14334d;
    }

    .settings-subtitle {
        margin: 4px 0 0;
        font-size: 0.82rem;
        color: #607587;
    }

    .settings-card-body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .settings-alert {
        display: none;
        margin-bottom: 12px;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .settings-alert.success {
        background: #e8f9ef;
        color: #107c40;
        border: 1px solid #bde6cb;
    }

    .settings-alert.error {
        background: #fdecee;
        color: #ab2932;
        border: 1px solid #f5c2c7;
    }

    .settings-steps {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .settings-step {
        display: flex;
        align-items: center;
        gap: 9px;
        border: 1px solid #d5e4ef;
        background: #f8fcff;
        color: #33566d;
        border-radius: 10px;
        padding: 10px 12px;
        text-align: left;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }
    .settings-step i { width: 17px; height: 17px; }
    .settings-step small { display: block; margin-top: 2px; opacity: .72; font-size: .64rem; font-weight: 600; }

    .settings-step.active {
        border-color: var(--theme-color);
        background: var(--theme-color);
        color: #fff;
    }

    .settings-panel {
        display: none;
        border: 1px solid #e4edf5;
        border-radius: 12px;
        padding: 14px;
        height: calc(100vh - 300px);
        min-height: 360px;
        max-height: calc(100vh - 300px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    .settings-panel.active {
        display: block;
    }

    .settings-panel-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #edf1f5;
    }
    .settings-panel-heading h3 { margin: 0; color: #183247; font-size: .95rem; font-weight: 800; }
    .settings-panel-heading p { margin: 4px 0 0; color: #748195; font-size: .74rem; }
    .settings-panel-badge { padding: 5px 9px; border-radius: 999px; color: #526173; background: #eef3f7; font-size: .66rem; font-weight: 800; white-space: nowrap; }

    .settings-panel::-webkit-scrollbar {
        width: 7px;
    }

    .settings-panel::-webkit-scrollbar-thumb {
        background: rgba(var(--theme-rgb), 0.35);
        border-radius: 999px;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .settings-field label {
        display: block;
        margin-bottom: 5px;
        font-size: 0.8rem;
        color: #355267;
        font-weight: 700;
    }

    .settings-field input,
    .settings-field select {
        width: 100%;
        border: 1px solid #d6e0e8;
        border-radius: 9px;
        height: 40px;
        padding: 0 11px;
        font-size: 0.82rem;
        color: #1b3448;
        outline: none;
        background: #fff;
    }

    .settings-field input[type="file"] {
        height: auto;
        padding: 8px 10px;
    }

    .settings-field-hint { display: block; margin-top: 5px; color: #8290a2; font-size: .68rem; }
    .settings-secret-wrap { position: relative; }
    .settings-secret-wrap input { padding-right: 42px; }
    .settings-secret-toggle {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 7px;
        color: #607587;
        background: #eef3f7;
    }
    .settings-secret-toggle i { width: 15px; height: 15px; }
    .settings-field.is-disabled { opacity: .55; }

    .settings-field input:focus,
    .settings-field select:focus {
        border-color: var(--theme-color);
        box-shadow: 0 0 0 3px rgba(var(--theme-rgb), 0.14);
    }

    .settings-error {
        display: block;
        min-height: 16px;
        color: #b2293b;
        font-size: 0.74rem;
        margin-top: 5px;
    }

    .settings-logo-preview {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: 0.75rem;
        color: #4f6b82;
        flex-wrap: wrap;
    }

    .settings-logo-preview img {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #d3e1ec;
        background: #fff;
    }

    .settings-bg-preview img {
        width: 72px;
        height: 42px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #d3e1ec;
        background: #fff;
    }

    .settings-actions {
        margin-top: 14px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .settings-btn {
        border: 0;
        border-radius: 9px;
        padding: 9px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
    }

    .settings-btn.secondary {
        background: #eaf1f7;
        color: #305168;
    }

    .settings-btn.primary {
        background: var(--theme-color);
        color: #fff;
    }

    .settings-btn:disabled {
        opacity: 0.72;
        cursor: not-allowed;
    }

    .settings-btn .spin { animation: settings-spin .8s linear infinite; }
    @keyframes settings-spin { to { transform: rotate(360deg); } }

    @media (max-width: 1200px) {
        .settings-card-header,
        .settings-card-body,
        .settings-panel {
            padding: 12px;
        }

        .settings-grid {
            gap: 10px;
        }
    }

    @media (max-width: 992px) {
        .settings-health { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .settings-steps {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(180px, 1fr);
            grid-template-columns: none;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 4px;
            margin-bottom: 12px;
            scrollbar-width: thin;
        }

        .settings-steps::-webkit-scrollbar {
            height: 6px;
        }

        .settings-steps::-webkit-scrollbar-thumb {
            background: rgba(var(--theme-rgb), 0.45);
            border-radius: 999px;
        }

        .settings-step {
            min-height: 42px;
        }

        .settings-panel {
            height: calc(100vh - 270px);
            max-height: calc(100vh - 270px);
            min-height: 320px;
        }
    }

    @media (max-width: 768px) {
        .settings-page {
            padding: 2px 0 6px;
        }

        .settings-card {
            border-radius: 12px;
        }

        .settings-grid {
            grid-template-columns: 1fr;
        }

        .settings-title {
            font-size: 1rem;
        }

        .settings-subtitle {
            font-size: 0.78rem;
        }

        .settings-step {
            font-size: 0.78rem;
            padding: 9px 10px;
        }

        .settings-field input,
        .settings-field select {
            height: 38px;
            font-size: 0.8rem;
        }

        .settings-actions {
            justify-content: stretch;
        }

        .settings-actions .settings-btn {
            flex: 1 1 calc(50% - 6px);
        }

        .settings-panel {
            height: calc(100vh - 240px);
            max-height: calc(100vh - 240px);
            min-height: 280px;
        }
    }

    @media (max-width: 576px) {
        .settings-health { grid-template-columns: 1fr; }
        .settings-card-header,
        .settings-card-body,
        .settings-panel {
            padding: 10px;
        }

        .settings-step {
            min-width: 170px;
        }

        .settings-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .settings-actions .settings-btn {
            width: 100%;
            flex: 1 1 100%;
        }

        .settings-panel {
            height: calc(100vh - 220px);
            max-height: calc(100vh - 220px);
            min-height: 250px;
        }
    }

    .settings-panel {
        height: auto;
        min-height: 390px;
        max-height: none;
        overflow: visible;
    }
</style>

<div class="settings-page">
    <section class="settings-hero">
        <div>
            <h2>Store Settings</h2>
            <p>Manage storefront identity, checkout, email delivery, and operational preferences.</p>
        </div>
        <div class="settings-save-state" id="settingsSaveState"><i data-lucide="cloud-check"></i><span>All changes saved</span></div>
    </section>

    <section class="settings-health">
        <div class="settings-health-item"><div class="settings-health-icon"><i data-lucide="palette"></i></div><div><strong>Theme</strong><span id="themeHealthLabel"><?= esc($themeColorOptions[$selectedThemeColor] ?? strtoupper($selectedThemeColor)) ?></span></div></div>
        <div class="settings-health-item"><div class="settings-health-icon"><i data-lucide="wallet-cards"></i></div><div><strong>Payments</strong><span id="paymentHealthLabel"><?= $gatewayConfigured ? 'Gateway configured' : 'Manual payments only' ?></span></div></div>
        <div class="settings-health-item"><div class="settings-health-icon"><i data-lucide="mail-check"></i></div><div><strong>Email</strong><span id="emailHealthLabel"><?= $emailConfigured ? 'SMTP configured' : 'Setup required' ?></span></div></div>
        <div class="settings-health-item"><div class="settings-health-icon"><i data-lucide="shield-check"></i></div><div><strong>Store status</strong><span id="systemHealthLabel"><?= (($system['maintenance_mode'] ?? '0') === '1') ? 'Maintenance mode' : 'Store online' ?></span></div></div>
    </section>
    <div class="settings-card">
        <div class="settings-card-header">
            <h2 class="settings-title">Settings</h2>
            <p class="settings-subtitle">Flow: General Settings → Payment Settings → Email Settings → System Config</p>
        </div>

        <div class="settings-card-body">
            <div id="settingsAlert" class="settings-alert" role="alert"></div>

            <div class="settings-steps">
                <button type="button" class="settings-step active" data-step="1" data-hash="general"><i data-lucide="store"></i><span>General<small>Brand and appearance</small></span></button>
                <button type="button" class="settings-step" data-step="2" data-hash="payment"><i data-lucide="credit-card"></i><span>Payment<small>Currency and checkout</small></span></button>
                <button type="button" class="settings-step" data-step="3" data-hash="email"><i data-lucide="mail"></i><span>Email<small>SMTP delivery</small></span></button>
                <button type="button" class="settings-step" data-step="4" data-hash="system"><i data-lucide="settings-2"></i><span>System<small>Store preferences</small></span></button>
            </div>

            <div class="settings-panel active" data-panel="1">
                <form id="generalSettingsForm" enctype="multipart/form-data">
                    <div class="settings-panel-heading"><div><h3>Brand & Appearance</h3><p>Update the identity and visual style used across the application.</p></div><span class="settings-panel-badge">General</span></div>
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="websiteName">Website Name</label>
                            <input type="text" id="websiteName" name="website_name" value="<?= esc((string) ($general['website_name'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="website_name"></span>
                        </div>

                        <div class="settings-field">
                            <label for="websiteTagline">Tagline</label>
                            <input type="text" id="websiteTagline" name="website_tagline" value="<?= esc((string) ($general['website_tagline'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="website_tagline"></span>
                        </div>

                        <div class="settings-field">
                            <label for="defaultTimezone">Default Timezone</label>
                            <select id="defaultTimezone" name="default_timezone">
                                <?php foreach ($timezoneList as $timezone) : ?>
                                    <option value="<?= esc($timezone) ?>" <?= (($general['default_timezone'] ?? 'Asia/Kolkata') === $timezone) ? 'selected' : '' ?>>
                                        <?= esc($timezone) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="settings-error" data-error-for="default_timezone"></span>
                        </div>

                        <div class="settings-field">
                            <label for="themeColor">Theme Color</label>
                            <select id="themeColor" name="theme_color">
                                <?php foreach ($themeColorOptions as $colorHex => $colorLabel) : ?>
                                    <option value="<?= esc($colorHex) ?>" <?= (strtolower($colorHex) === $selectedThemeColor) ? 'selected' : '' ?>>
                                        <?= esc($colorLabel . ' (' . strtoupper($colorHex) . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="settings-error" data-error-for="theme_color"></span>
                        </div>

                        <div class="settings-field">
                            <label for="websiteLogo">Website Logo</label>
                            <input type="file" id="websiteLogo" name="website_logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                            <span class="settings-error" data-error-for="website_logo"></span>
                            <div class="settings-logo-preview">
                                <span>Current logo:</span>
                                <img id="websiteLogoPreview" src="<?= esc($logoUrl !== '' ? $logoUrl : base_url('materials/admin/images/logo.png')) ?>" alt="Website Logo">
                            </div>
                        </div>

                        <div class="settings-field">
                            <label for="authBackgroundImage">Login/Register Background</label>
                            <input type="file" id="authBackgroundImage" name="auth_background_image" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <span class="settings-error" data-error-for="auth_background_image"></span>
                            <div class="settings-logo-preview settings-bg-preview">
                                <span>Current background:</span>
                                <img id="authBackgroundPreview" src="<?= esc($authBackgroundUrl) ?>" alt="Auth Background" style="<?= $authBackgroundUrl === '' ? 'display:none;' : '' ?>">
                                <span id="authBackgroundEmpty" style="<?= $authBackgroundUrl !== '' ? 'display:none;' : '' ?>">Not set</span>
                            </div>
                        </div>

                        <div class="settings-field">
                            <label for="fontFamily">Font Family</label>
                            <select id="fontFamily" name="font_family">
                                <?php $selectedFontFamily = strtolower((string) ($general['font_family'] ?? 'inter')); ?>
                                <?php foreach ($fontFamilyOptions as $fontKey => $fontLabel) : ?>
                                    <option value="<?= esc($fontKey) ?>" <?= ($fontKey === $selectedFontFamily) ? 'selected' : '' ?>>
                                        <?= esc($fontLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="settings-error" data-error-for="font_family"></span>
                        </div>
                    </div>

                    <div class="settings-actions">
                        <span></span>
                        <button type="submit" class="settings-btn primary">Save</button>
                    </div>
                </form>
            </div>

            <div class="settings-panel" data-panel="2">
                <form id="paymentSettingsForm">
                    <div class="settings-panel-heading"><div><h3>Payment & Checkout</h3><p>Configure currency, taxes, payment methods, and gateway credentials.</p></div><span class="settings-panel-badge">Financial</span></div>
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="currencyList">Currency List</label>
                            <select id="currencyList">
                                <option value="">Select currency</option>
                                <?php foreach ($currencyCatalog as $row) : ?>
                                    <?php
                                    $code = strtoupper(trim((string) ($row['code'] ?? '')));
                                    $symbol = trim((string) ($row['symbol'] ?? ''));
                                    if ($code === '') {
                                        continue;
                                    }
                                    ?>
                                    <option
                                        value="<?= esc($code) ?>"
                                        data-symbol="<?= esc($symbol) ?>"
                                        <?= (($payment['currency_code'] ?? '') === $code) ? 'selected' : '' ?>
                                    >
                                        <?= esc($code . ($symbol !== '' ? ' (' . $symbol . ')' : '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="settings-field">
                            <label for="currencyCode">Currency Code</label>
                            <input type="text" id="currencyCode" name="currency_code" value="<?= esc((string) ($payment['currency_code'] ?? 'INR')) ?>">
                            <span class="settings-error" data-error-for="currency_code"></span>
                        </div>

                        <div class="settings-field">
                            <label for="currencySymbol">Currency Symbol</label>
                            <input type="text" id="currencySymbol" name="currency_symbol" value="<?= esc((string) ($payment['currency_symbol'] ?? 'Rs')) ?>">
                            <span class="settings-error" data-error-for="currency_symbol"></span>
                        </div>

                        <div class="settings-field">
                            <label for="taxType">Tax Type</label>
                            <select id="taxType" name="tax_type">
                                <option value="exclusive" <?= (($payment['tax_type'] ?? '') === 'exclusive') ? 'selected' : '' ?>>Exclusive</option>
                                <option value="inclusive" <?= (($payment['tax_type'] ?? '') === 'inclusive') ? 'selected' : '' ?>>Inclusive</option>
                            </select>
                            <span class="settings-error" data-error-for="tax_type"></span>
                        </div>

                        <div class="settings-field">
                            <label for="taxRate">Tax Rate (%)</label>
                            <input type="number" id="taxRate" name="tax_rate" min="0" max="100" step="0.01" value="<?= esc((string) ($payment['tax_rate'] ?? '18')) ?>">
                            <span class="settings-error" data-error-for="tax_rate"></span>
                        </div>

                        <div class="settings-field">
                            <label for="gatewayEnabled">Razorpay Gateway</label>
                            <select id="gatewayEnabled" name="gateway_enabled">
                                <option value="0" <?= (($payment['gateway_enabled'] ?? '0') === '0') ? 'selected' : '' ?>>Disabled</option>
                                <option value="1" <?= (($payment['gateway_enabled'] ?? '0') === '1') ? 'selected' : '' ?>>Enabled</option>
                            </select>
                            <span class="settings-error" data-error-for="gateway_enabled"></span>
                        </div>

                        <div class="settings-field">
                            <label for="razorpayMode">Gateway Mode</label>
                            <select id="razorpayMode" name="razorpay_mode">
                                <option value="test" <?= (($payment['razorpay_mode'] ?? 'test') === 'test') ? 'selected' : '' ?>>Test</option>
                                <option value="live" <?= (($payment['razorpay_mode'] ?? 'test') === 'live') ? 'selected' : '' ?>>Live</option>
                            </select>
                            <span class="settings-error" data-error-for="razorpay_mode"></span>
                        </div>

                        <div class="settings-field">
                            <label for="razorpayKeyId">Razorpay Key ID</label>
                            <input type="text" id="razorpayKeyId" name="razorpay_key_id" autocomplete="off" value="<?= esc((string) ($payment['razorpay_key_id'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="razorpay_key_id"></span>
                            <small class="settings-field-hint">Key prefix must match the selected test or live mode.</small>
                        </div>

                        <div class="settings-field">
                            <label for="razorpayKeySecret">Razorpay Key Secret</label>
                            <div class="settings-secret-wrap"><input type="password" id="razorpayKeySecret" name="razorpay_key_secret" autocomplete="new-password" placeholder="<?= !empty($payment['razorpay_key_secret']) ? 'Configured - leave blank to keep current secret' : 'Enter Razorpay key secret' ?>"><button class="settings-secret-toggle" type="button" data-toggle-secret="#razorpayKeySecret" aria-label="Show secret"><i data-lucide="eye"></i></button></div>
                            <span class="settings-error" data-error-for="razorpay_key_secret"></span>
                        </div>

                        <div class="settings-field">
                            <label for="upiEnabled">UPI and Google Pay</label>
                            <select id="upiEnabled" name="upi_enabled">
                                <option value="1" <?= (($payment['upi_enabled'] ?? '1') === '1') ? 'selected' : '' ?>>Enabled</option>
                                <option value="0" <?= (($payment['upi_enabled'] ?? '1') === '0') ? 'selected' : '' ?>>Disabled</option>
                            </select>
                            <span class="settings-error" data-error-for="upi_enabled"></span>
                        </div>

                        <div class="settings-field">
                            <label for="codEnabled">Cash on Delivery</label>
                            <select id="codEnabled" name="cod_enabled">
                                <option value="1" <?= (($payment['cod_enabled'] ?? '1') === '1') ? 'selected' : '' ?>>Enabled</option>
                                <option value="0" <?= (($payment['cod_enabled'] ?? '1') === '0') ? 'selected' : '' ?>>Disabled</option>
                            </select>
                            <span class="settings-error" data-error-for="cod_enabled"></span>
                        </div>

                        <div class="settings-field">
                            <label for="checkoutName">Checkout Business Name</label>
                            <input type="text" id="checkoutName" name="checkout_name" value="<?= esc((string) ($payment['checkout_name'] ?? 'Ebolt')) ?>">
                            <span class="settings-error" data-error-for="checkout_name"></span>
                        </div>

                        <div class="settings-field">
                            <label for="checkoutThemeColor">Checkout Theme Color</label>
                            <input type="color" id="checkoutThemeColor" name="checkout_theme_color" value="<?= esc((string) ($payment['checkout_theme_color'] ?? '#0f6cad')) ?>">
                            <span class="settings-error" data-error-for="checkout_theme_color"></span>
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="button" class="settings-btn secondary" data-prev="1">Back</button>
                        <button type="submit" class="settings-btn primary">Save</button>
                    </div>
                </form>
            </div>

            <div class="settings-panel" data-panel="3">
                <form id="emailSettingsForm">
                    <div class="settings-panel-heading"><div><h3>Email Delivery</h3><p>Configure the SMTP account used for transactional messages.</p></div><span class="settings-panel-badge"><?= $emailConfigured ? 'Configured' : 'Setup required' ?></span></div>
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="smtpHost">SMTP Host</label>
                            <input type="text" id="smtpHost" name="smtp_host" value="<?= esc((string) ($email['smtp_host'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="smtp_host"></span>
                        </div>

                        <div class="settings-field">
                            <label for="smtpPort">SMTP Port</label>
                            <input type="number" id="smtpPort" name="smtp_port" min="1" max="65535" value="<?= esc((string) ($email['smtp_port'] ?? '587')) ?>">
                            <span class="settings-error" data-error-for="smtp_port"></span>
                        </div>

                        <div class="settings-field">
                            <label for="smtpUsername">SMTP Username</label>
                            <input type="text" id="smtpUsername" name="smtp_username" value="<?= esc((string) ($email['smtp_username'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="smtp_username"></span>
                        </div>

                        <div class="settings-field">
                            <label for="smtpPassword">SMTP Password</label>
                            <div class="settings-secret-wrap"><input type="password" id="smtpPassword" name="smtp_password" autocomplete="new-password" value="" placeholder="<?= $emailConfigured ? 'Configured - leave blank to keep current password' : 'Enter SMTP password' ?>"><button class="settings-secret-toggle" type="button" data-toggle-secret="#smtpPassword" aria-label="Show password"><i data-lucide="eye"></i></button></div>
                            <span class="settings-error" data-error-for="smtp_password"></span>
                        </div>

                        <div class="settings-field">
                            <label for="smtpEncryption">Encryption</label>
                            <select id="smtpEncryption" name="smtp_encryption">
                                <option value="none" <?= (($email['smtp_encryption'] ?? '') === 'none') ? 'selected' : '' ?>>None</option>
                                <option value="ssl" <?= (($email['smtp_encryption'] ?? '') === 'ssl') ? 'selected' : '' ?>>SSL</option>
                                <option value="tls" <?= (($email['smtp_encryption'] ?? '') === 'tls') ? 'selected' : '' ?>>TLS</option>
                            </select>
                            <span class="settings-error" data-error-for="smtp_encryption"></span>
                        </div>

                        <div class="settings-field">
                            <label for="fromEmail">From Email</label>
                            <input type="email" id="fromEmail" name="from_email" value="<?= esc((string) ($email['from_email'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="from_email"></span>
                        </div>

                        <div class="settings-field">
                            <label for="fromName">From Name</label>
                            <input type="text" id="fromName" name="from_name" value="<?= esc((string) ($email['from_name'] ?? '')) ?>">
                            <span class="settings-error" data-error-for="from_name"></span>
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="button" class="settings-btn secondary" data-prev="2">Back</button>
                        <button type="submit" class="settings-btn primary">Save</button>
                    </div>
                </form>
            </div>

            <div class="settings-panel" data-panel="4">
                <form id="systemSettingsForm">
                    <div class="settings-panel-heading"><div><h3>System Preferences</h3><p>Control availability, registration, language, and default page sizing.</p></div><span class="settings-panel-badge">Operations</span></div>
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="maintenanceMode">Maintenance Mode</label>
                            <select id="maintenanceMode" name="maintenance_mode">
                                <option value="0" <?= (($system['maintenance_mode'] ?? '0') === '0') ? 'selected' : '' ?>>Off</option>
                                <option value="1" <?= (($system['maintenance_mode'] ?? '0') === '1') ? 'selected' : '' ?>>On</option>
                            </select>
                            <span class="settings-error" data-error-for="maintenance_mode"></span>
                        </div>

                        <div class="settings-field">
                            <label for="allowRegistration">Allow Registration</label>
                            <select id="allowRegistration" name="allow_registration">
                                <option value="1" <?= (($system['allow_registration'] ?? '1') === '1') ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= (($system['allow_registration'] ?? '1') === '0') ? 'selected' : '' ?>>No</option>
                            </select>
                            <span class="settings-error" data-error-for="allow_registration"></span>
                        </div>

                        <div class="settings-field">
                            <label for="itemsPerPage">Default Items Per Page</label>
                            <input type="number" id="itemsPerPage" name="items_per_page" min="5" max="200" value="<?= esc((string) ($system['items_per_page'] ?? '10')) ?>">
                            <span class="settings-error" data-error-for="items_per_page"></span>
                        </div>

                        <div class="settings-field">
                            <label for="defaultLanguage">Default Language</label>
                            <select id="defaultLanguage" name="default_language">
                                <option value="en" <?= (($system['default_language'] ?? 'en') === 'en') ? 'selected' : '' ?>>English</option>
                                <option value="ta" <?= (($system['default_language'] ?? 'en') === 'ta') ? 'selected' : '' ?>>Tamil</option>
                                <option value="hi" <?= (($system['default_language'] ?? 'en') === 'hi') ? 'selected' : '' ?>>Hindi</option>
                            </select>
                            <span class="settings-error" data-error-for="default_language"></span>
                        </div>

                    </div>

                    <div class="settings-actions">
                        <button type="button" class="settings-btn secondary" data-prev="3">Back</button>
                        <button type="submit" class="settings-btn primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    const alertBox = $('#settingsAlert');
    const steps = $('.settings-step');
    const panels = $('.settings-panel');
    const saveState = $('#settingsSaveState');
    let hasUnsavedChanges = false;
    const fontFamilyCssMap = {
        inter: "'Inter', sans-serif",
        manrope: "'Manrope', sans-serif",
        poppins: "'Poppins', sans-serif",
        roboto: "'Roboto', sans-serif",
        open_sans: "'Open Sans', sans-serif",
        lato: "'Lato', sans-serif",
        nunito: "'Nunito', sans-serif"
    };

    function showStep(stepNo) {
        const no = String(stepNo);
        steps.removeClass('active').filter('[data-step="' + no + '"]').addClass('active');
        panels.removeClass('active').filter('[data-panel="' + no + '"]').addClass('active');
        const hash = steps.filter('[data-step="' + no + '"]').data('hash');
        if (hash) {
            history.replaceState(null, '', '#' + hash);
        }
    }

    function showAlert(type, message) {
        alertBox.removeClass('success error').addClass(type).text(message).fadeIn(120);
        setTimeout(function () {
            alertBox.fadeOut(200);
        }, 3000);
    }

    function clearErrors(form) {
        $(form).find('.settings-error').text('');
        $(form).find('input, select').removeAttr('aria-invalid');
    }

    function applyErrors(errors, form) {
        let firstField = null;
        $.each(errors || {}, function (field, message) {
            $('[data-error-for="' + field + '"]').text(message);
            const input = $(form).find('[name="' + field + '"]').attr('aria-invalid', 'true');
            if (!firstField && input.length) firstField = input;
        });
        if (firstField) firstField.trigger('focus');
    }

    function setUnsaved(value) {
        hasUnsavedChanges = value;
        saveState.toggleClass('unsaved', value);
        saveState.find('span').text(value ? 'Unsaved changes' : 'All changes saved');
        saveState.find('i').attr('data-lucide', value ? 'cloud-alert' : 'cloud-check');
        lucide.createIcons();
    }

    function submitForm(formSelector, url, nextStep) {
        const form = $(formSelector);
        clearErrors(form);

        const submitBtn = form.find('button[type="submit"]');
        const oldHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="spin" data-lucide="loader-circle"></i> Saving...');
        lucide.createIcons();

        $.ajax({
            url: url,
            type: 'POST',
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (!response.status) {
                    if (response.errors) {
                        applyErrors(response.errors, form);
                    } else {
                        showAlert('error', response.message || 'Unable to save settings.');
                    }
                    return;
                }

                if (response.logo_url) {
                    $('#websiteLogoPreview').attr('src', response.logo_url);
                }
                if (response.auth_background_url) {
                    $('#authBackgroundPreview').attr('src', response.auth_background_url).show();
                    $('#authBackgroundEmpty').hide();
                }
                if (typeof response.font_family === 'string' && fontFamilyCssMap[response.font_family]) {
                    document.documentElement.style.setProperty('--app-font', fontFamilyCssMap[response.font_family]);
                }

                setUnsaved(false);
                showAlert('success', response.message || 'Saved');
                updateHealth();
                if (nextStep) showStep(nextStep);
            },
            error: function (xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Unexpected error occurred while saving settings.');
            },
            complete: function () {
                submitBtn.prop('disabled', false).html(oldHtml);
                lucide.createIcons();
            }
        });
    }

    steps.on('click', function () {
        showStep($(this).data('step'));
    });

    $('[data-prev]').on('click', function () {
        showStep($(this).data('prev'));
    });

    $('.settings-panel form').on('input change', 'input, select', function () {
        setUnsaved(true);
        $(this).removeAttr('aria-invalid');
        $('[data-error-for="' + this.name + '"]').text('');
    });

    window.addEventListener('beforeunload', function (event) {
        if (!hasUnsavedChanges) return;
        event.preventDefault();
        event.returnValue = '';
    });

    $('#generalSettingsForm').on('submit', function (event) {
        event.preventDefault();
        submitForm('#generalSettingsForm', "<?= base_url('admin/settings/general') ?>");
    });

    $('#websiteLogo').on('change', function () {
        const file = this.files && this.files.length ? this.files[0] : null;
        $('[data-error-for="website_logo"]').text('');

        if (!file) {
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (allowedTypes.indexOf(file.type) === -1) {
            $('[data-error-for="website_logo"]').text('Only JPG, PNG, WEBP, SVG allowed.');
            $(this).val('');
            return;
        }

        if (file.size > (1024 * 1024)) {
            $('[data-error-for="website_logo"]').text('Logo size must be 1MB or less.');
            $(this).val('');
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        $('#websiteLogoPreview').attr('src', previewUrl);
    });

    $('#authBackgroundImage').on('change', function () {
        const file = this.files && this.files.length ? this.files[0] : null;
        $('[data-error-for="auth_background_image"]').text('');

        if (!file) {
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (allowedTypes.indexOf(file.type) === -1) {
            $('[data-error-for="auth_background_image"]').text('Only JPG, PNG, WEBP allowed.');
            $(this).val('');
            return;
        }

        if (file.size > (3 * 1024 * 1024)) {
            $('[data-error-for="auth_background_image"]').text('Background image size must be 3MB or less.');
            $(this).val('');
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        $('#authBackgroundPreview').attr('src', previewUrl).show();
        $('#authBackgroundEmpty').hide();
    });

    $('#paymentSettingsForm').on('submit', function (event) {
        event.preventDefault();
        submitForm('#paymentSettingsForm', "<?= base_url('admin/settings/payment') ?>");
    });

    $('#currencyList').on('change', function () {
        const selected = $(this).find('option:selected');
        const code = selected.val() || '';
        const symbol = selected.data('symbol') || '';
        if (code) {
            $('#currencyCode').val(String(code).toUpperCase());
        }
        if (symbol) {
            $('#currencySymbol').val(String(symbol));
        }
    });

    $('#currencyCode').on('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
    });

    function updateGatewayState() {
        const enabled = $('#gatewayEnabled').val() === '1';
        $('#razorpayMode, #razorpayKeyId, #razorpayKeySecret').closest('.settings-field').toggleClass('is-disabled', !enabled);
        $('#razorpayKeyId, #razorpayKeySecret').prop('readonly', !enabled);
        $('#paymentHealthLabel').text(enabled ? 'Gateway enabled' : 'Manual payments only');
    }

    function updateHealth() {
        const colorLabel = $('#themeColor option:selected').text().replace(/\s+\(.+\)$/, '');
        $('#themeHealthLabel').text(colorLabel);
        $('#systemHealthLabel').text($('#maintenanceMode').val() === '1' ? 'Maintenance mode' : 'Store online');
        if ($('#smtpHost').val() && $('#smtpUsername').val()) $('#emailHealthLabel').text('SMTP configured');
        updateGatewayState();
    }

    $('#gatewayEnabled').on('change', updateGatewayState);
    $('#themeColor').on('change', function () {
        document.documentElement.style.setProperty('--theme-color', this.value);
        updateHealth();
    });
    $('#fontFamily').on('change', function () {
        if (fontFamilyCssMap[this.value]) document.documentElement.style.setProperty('--app-font', fontFamilyCssMap[this.value]);
    });
    $('#maintenanceMode, #smtpHost, #smtpUsername').on('change input', updateHealth);

    $('[data-toggle-secret]').on('click', function () {
        const input = $($(this).data('toggle-secret'));
        const show = input.attr('type') === 'password';
        input.attr('type', show ? 'text' : 'password');
        $(this).attr('aria-label', show ? 'Hide secret' : 'Show secret').find('i').attr('data-lucide', show ? 'eye-off' : 'eye');
        lucide.createIcons();
    });

    $('#emailSettingsForm').on('submit', function (event) {
        event.preventDefault();
        submitForm('#emailSettingsForm', "<?= base_url('admin/settings/email') ?>");
    });

    $('#systemSettingsForm').on('submit', function (event) {
        event.preventDefault();
        submitForm('#systemSettingsForm', "<?= base_url('admin/settings/system') ?>");
    });

    if (window.AdminImageCropper) {
        window.AdminImageCropper.attach('#websiteLogo', {
            errorSelector: '[data-error-for="website_logo"]',
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'],
            maxSizeBytes: 1024 * 1024,
            aspectRatio: 1,
            outputType: 'image/png',
            outputFileName: 'website-logo-cropped.png',
            onCropped: function (blob) {
                const previewUrl = URL.createObjectURL(blob);
                $('#websiteLogoPreview').attr('src', previewUrl);
            }
        });

        window.AdminImageCropper.attach('#authBackgroundImage', {
            errorSelector: '[data-error-for="auth_background_image"]',
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
            maxSizeBytes: 3 * 1024 * 1024,
            aspectRatio: 16 / 9,
            outputType: 'image/jpeg',
            outputQuality: 0.9,
            outputFileName: 'auth-background-cropped.jpg',
            onCropped: function (blob) {
                const previewUrl = URL.createObjectURL(blob);
                $('#authBackgroundPreview').attr('src', previewUrl).show();
                $('#authBackgroundEmpty').hide();
            }
        });
    }

    const hashSteps = { general: 1, payment: 2, email: 3, system: 4 };
    const initialHash = window.location.hash.replace('#', '');
    showStep(hashSteps[initialHash] || 1);
    updateGatewayState();
    updateHealth();
});
</script>

<?php include('footer.php'); ?>
