<?php
$themeColor = '#0f6cad';
$themeLogoUrl = base_url('materials/admin/images/logo.png');
$themeWebsiteName = 'Ebolt';
$themeFontKey = 'inter';
$currencySymbol = 'Rs';
$currencyCode = 'INR';
$notificationUnreadCount = 0;
$fontFamilyMap = [
    'inter' => "'Inter', sans-serif",
    'manrope' => "'Manrope', sans-serif",
    'poppins' => "'Poppins', sans-serif",
    'roboto' => "'Roboto', sans-serif",
    'open_sans' => "'Open Sans', sans-serif",
    'lato' => "'Lato', sans-serif",
    'nunito' => "'Nunito', sans-serif",
];

if (!function_exists('hexToRgbChannels')) {
    function hexToRgbChannels(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (!preg_match('/^[A-Fa-f0-9]{6}$/', $hex)) {
            return [15, 108, 173];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}

if (!function_exists('shiftHexColor')) {
    function shiftHexColor(string $hex, int $delta): string
    {
        [$r, $g, $b] = hexToRgbChannels($hex);
        $r = max(0, min(255, $r + $delta));
        $g = max(0, min(255, $g + $delta));
        $b = max(0, min(255, $b + $delta));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}

try {
    $db = db_connect();
    if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
        $settingsModel = new \App\Models\SettingsModel();
        $system = $settingsModel->getGroupSettings('system');
        $general = $settingsModel->getGroupSettings('general');
        $payment = $settingsModel->getGroupSettings('payment');

        $savedTheme = trim((string) ($general['theme_color'] ?? ($system['theme_color'] ?? '')));
        if (preg_match('/^#([A-Fa-f0-9]{6})$/', $savedTheme)) {
            $themeColor = strtolower($savedTheme);
        }

        $savedLogo = trim((string) ($general['website_logo'] ?? ''));
        if ($savedLogo !== '') {
            $themeLogoUrl = base_url('uploads/settings/' . $savedLogo);
        }

        $savedWebsiteName = trim((string) ($general['website_name'] ?? ''));
        if ($savedWebsiteName !== '') {
            $themeWebsiteName = $savedWebsiteName;
        }

        $savedFontKey = strtolower(trim((string) ($general['font_family'] ?? '')));
        if ($savedFontKey !== '' && isset($fontFamilyMap[$savedFontKey])) {
            $themeFontKey = $savedFontKey;
        }

        $savedCurrencySymbol = trim((string) ($payment['currency_symbol'] ?? ''));
        if ($savedCurrencySymbol !== '') {
            $currencySymbol = $savedCurrencySymbol;
        }

        $savedCurrencyCode = strtoupper(trim((string) ($payment['currency_code'] ?? '')));
        if ($savedCurrencyCode !== '') {
            $currencyCode = $savedCurrencyCode;
        }
    }

    if ($db->tableExists('notification_conversations')) {
        $notificationRow = $db->table('notification_conversations')
            ->selectSum('nc_unread_count', 'total')
            ->where('nc_status', 1)
            ->get()
            ->getRowArray();
        $notificationUnreadCount = (int) ($notificationRow['total'] ?? 0);
    }
} catch (\Throwable $e) {
    // Keep defaults.
}

[$themeR, $themeG, $themeB] = hexToRgbChannels($themeColor);
$themeColorDark = shiftHexColor($themeColor, -18);
$themeColorDarker = shiftHexColor($themeColor, -34);
$themeColorLight = shiftHexColor($themeColor, 18);

$roleId = (int) (session()->get('us_role_id') ?? 0);
$roleMap = [
    1 => 'Admin',
    2 => 'Customer',
    3 => 'Dealer',
    4 => 'Sales Executive',
    5 => 'Warehouse Staff',
];
$roleName = $roleMap[$roleId] ?? 'User';
$sessionImageFile = trim((string) (session()->get('us_image') ?? ''));
$profileImageUrl = base_url('uploads/customers/default-user.svg');
if ($sessionImageFile !== '' && is_file(FCPATH . 'uploads/customers/' . $sessionImageFile)) {
    $profileImageUrl = base_url('uploads/customers/' . $sessionImageFile);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($themeWebsiteName) ?></title>

<script src="https://unpkg.com/lucide@latest"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;500;600;700;800&family=Lato:wght@400;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link href="<?= base_url('materials/admin/css/common.css') . '?v=' . @filemtime(FCPATH . 'materials/admin/css/common.css') ?>" rel="stylesheet">
<script src="<?= base_url('materials/admin/js/imageCropper.js') . '?v=' . @filemtime(FCPATH . 'materials/admin/js/imageCropper.js') ?>"></script>

<style>
    :root {
        --theme-color: <?= esc($themeColor) ?>;
        --theme-color-dark: <?= esc($themeColorDark) ?>;
        --theme-color-darker: <?= esc($themeColorDarker) ?>;
        --theme-color-light: <?= esc($themeColorLight) ?>;
        --theme-rgb: <?= (int) $themeR ?>, <?= (int) $themeG ?>, <?= (int) $themeB ?>;
        --page-bg: #eef3f8;
        --header-grad-a: var(--theme-color-darker);
        --header-grad-b: var(--theme-color-light);
        --text-light: #f8fbff;
        --text-muted: #d7e9f6;
        --chip-bg: rgba(255, 255, 255, 0.16);
        --chip-border: rgba(255, 255, 255, 0.26);
        --chip-hover: rgba(255, 255, 255, 0.22);
        --app-font: <?= $fontFamilyMap[$themeFontKey] ?? "'Inter', sans-serif" ?>;
    }

    body {
        margin: 0;
        font-family: var(--app-font);
        background: radial-gradient(circle at top right, #e3f1fb 0%, var(--page-bg) 40%, #eaf0f6 100%);
        display: block;
        align-items: stretch;
        justify-content: flex-start;
        padding: 0;
        min-height: 100vh;
    }

    .appbar-wrap {
        padding: 14px 20px 8px;
    }

    .appbar {
        width: 100%;
        min-height: 78px;
        border-radius: 18px;
        background: linear-gradient(120deg, var(--header-grad-a), var(--header-grad-b));
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 12px 18px;
        box-shadow: 0 10px 22px rgba(10, 56, 95, 0.28);
        color: var(--text-light);
    }

    .app-left {
        min-width: 0;
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .brand-logo {
        width: clamp(96px, 14vw, 148px);
        height: clamp(40px, 5vw, 52px);
        max-width: 42vw;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
    }

    .brand-logo img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        display: block;
        object-fit: contain;
        object-position: center;
        background: #fff;
    }

    .brand-text {
        display: flex;
        flex-direction: column;
    }

    .brand-site-name {
        font-size: 1rem;
        line-height: 1.1;
        font-weight: 800;
        margin-bottom: 2px;
        color: #ffffff;
        letter-spacing: 0.2px;
    }

    .app-title {
        font-size: 1.45rem;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: 0.2px;
        margin: 0;
    }

    .app-subtitle {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .app-right {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .search-box {
        background: var(--chip-bg);
        border: 1px solid var(--chip-border);
        padding: 8px 12px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        width: 260px;
        transition: all 0.2s ease;
    }

    .search-box:focus-within {
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .search-box i {
        color: #e7f6ff;
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .search-box input {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        color: #ffffff;
        margin-left: 8px;
        font-size: 0.9rem;
    }

    .search-box input::placeholder {
        color: #d8e9f6;
    }

    .notification {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--chip-border);
        background: var(--chip-bg);
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: background 0.2s ease;
        color: inherit;
        text-decoration: none;
    }

    .notification:hover {
        background: var(--chip-hover);
    }

    .notification i {
        width: 20px;
        height: 20px;
        color: #f6fcff;
    }

    .notify-count {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #ff5c5c;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        border: 2px solid var(--theme-color-dark);
    }

    .profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 8px 4px 4px;
        border: 1px solid var(--chip-border);
        border-radius: 12px;
        background: var(--chip-bg);
        cursor: pointer;
    }

    .profile img {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.35);
    }

    .profile-info {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    #userName {
        font-size: 0.84rem;
        font-weight: 700;
        color: #fff;
    }

    .profile-role {
        font-size: 0.73rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .logout-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
        text-decoration: none;
        font-size: 0.86rem;
        font-weight: 700;
        transition: background 0.2s ease;
    }

    .logout-btn:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    .logout-btn i {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 992px) {
        .appbar {
            flex-wrap: wrap;
        }

        .app-left {
            width: 100%;
        }

        .app-right {
            width: 100%;
            justify-content: space-between;
        }

        .search-box {
            width: 100%;
            max-width: 420px;
        }
    }

    @media (max-width: 576px) {
        .appbar-wrap {
            padding: 10px 12px 6px;
        }

        .appbar {
            border-radius: 14px;
            padding: 12px;
            min-height: auto;
        }

        .app-left {
            min-width: auto;
        }

        .brand-logo {
            width: clamp(84px, 28vw, 110px);
            height: 38px;
            padding: 3px;
        }

        .app-title {
            font-size: 1.2rem;
        }

        .brand-site-name {
            font-size: 0.9rem;
        }

        .profile-info {
            display: none;
        }

        .logout-btn span {
            display: none;
        }
    }
</style>
</head>

<body>

<div class="appbar-wrap">
    <div class="appbar">
        <div class="app-left">
            <a class="brand-logo" href="<?= base_url('admin/dashboard') ?>" aria-label="Admin Home">
                <img id="brandLogo" src="<?= esc($themeLogoUrl) ?>" alt="Logo">
            </a>
            <div class="brand-text">
                <h1 class="app-title" id="appTitle"><?= esc($themeWebsiteName) ?></h1>
                <div class="app-subtitle">Operations center for today</div>
            </div>
        </div>

        <div class="app-right">
            <!-- <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="searchInput" placeholder="Search orders, customers, products...">
            </div> -->

            <a class="notification" href="<?= base_url('admin/notifications') ?>" aria-label="Open notifications">
                <i data-lucide="bell"></i>
                <span class="notify-count" id="notificationCount"<?= $notificationUnreadCount > 0 ? '' : ' hidden' ?>><?= $notificationUnreadCount ?></span>
            </a>

            <div class="profile" onclick="openProfile()">
                <img id="profileImage" src="<?= esc($profileImageUrl) ?>" alt="User">
                <div class="profile-info">
                    <span id="userName"></span>
                    <span class="profile-role" id="profileRole"></span>
                </div>
            </div>

            <a class="logout-btn" href="<?= base_url('logout') ?>" aria-label="Logout" onclick="return confirmLogout(event)">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<script>
    const appData = {
        title: <?= json_encode($themeWebsiteName) ?>,
        userName: <?= json_encode((string) (session()->get('us_name') ?: 'Administrator')) ?>,
        roleName: <?= json_encode($roleName) ?>,
        profileImage: <?= json_encode($profileImageUrl) ?>,
        notifications: <?= $notificationUnreadCount ?>,
        currencySymbol: <?= json_encode($currencySymbol) ?>,
        currencyCode: <?= json_encode($currencyCode) ?>
    };

    document.getElementById("appTitle").innerText = appData.title;
    document.getElementById("userName").innerText = appData.userName;
    document.getElementById("profileRole").innerText = appData.roleName;
    document.getElementById("profileImage").src = appData.profileImage;
    const notificationCount = document.getElementById("notificationCount");
    function updateNotificationCount(count) {
        const total = Math.max(0, Number(count || 0));
        notificationCount.innerText = total > 99 ? "99+" : total;
        notificationCount.hidden = total === 0;
    }
    updateNotificationCount(appData.notifications);

    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            console.log("Searching:", this.value);
        });
    }

    function refreshNotificationCount() {
        fetch(<?= json_encode(base_url('admin/notifications/unread-count')) ?>, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (payload) {
                if (payload && payload.status) {
                    updateNotificationCount(payload.unread_count);
                }
            })
            .catch(function () {});
    }
    window.setInterval(refreshNotificationCount, 15000);

    function openProfile() {
        alert("Open Profile");
    }

    function confirmLogout(event) {
        event.preventDefault();
        const logoutUrl = event.currentTarget.href;
        const message = 'Are you sure you want to logout?';

        if (window.AdminConfirm) {
            window.AdminConfirm.open({
                title: 'Logout',
                message: message,
                confirmText: 'Logout',
                cancelText: 'Cancel'
            }).then(function (confirmed) {
                if (confirmed) {
                    window.location.href = logoutUrl;
                }
            });

            return false;
        }

        if (confirm(message)) {
            window.location.href = logoutUrl;
        }

        return false;
    }

    lucide.createIcons();
</script>
