<?php
$websiteName = trim((string) ($branding['website_name'] ?? 'Ebolt'));
$websiteTagline = trim((string) ($branding['website_tagline'] ?? 'Power your commerce'));
$fontFamilyMap = [
    'inter' => "'Inter', sans-serif",
    'manrope' => "'Manrope', sans-serif",
    'poppins' => "'Poppins', sans-serif",
    'roboto' => "'Roboto', sans-serif",
    'open_sans' => "'Open Sans', sans-serif",
    'lato' => "'Lato', sans-serif",
    'nunito' => "'Nunito', sans-serif",
];
$fontFamilyKey = strtolower(trim((string) ($branding['font_family'] ?? 'inter')));
$fontFamilyCss = $fontFamilyMap[$fontFamilyKey] ?? $fontFamilyMap['inter'];
$logoUrl = trim((string) ($branding['logo_url'] ?? base_url('materials/admin/images/logo.png')));
$authBackgroundUrl = trim((string) ($branding['auth_background_url'] ?? ''));
$themeColor = strtolower(trim((string) ($branding['theme_color'] ?? '#0f6cad')));
if (!preg_match('/^#([a-f0-9]{6})$/', $themeColor)) {
    $themeColor = '#0f6cad';
}
$themeRgb = [hexdec(substr($themeColor, 1, 2)), hexdec(substr($themeColor, 3, 2)), hexdec(substr($themeColor, 5, 2))];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | <?= esc($websiteName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('materials/admin/css/common.css');?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;500;600;700;800&family=Lato:wght@400;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --theme-color: <?= esc($themeColor) ?>;
            --theme-rgb: <?= (int) $themeRgb[0] ?>, <?= (int) $themeRgb[1] ?>, <?= (int) $themeRgb[2] ?>;
            --auth-bg-image: <?= $authBackgroundUrl !== '' ? "url('" . esc($authBackgroundUrl) . "')" : 'none' ?>;
            --app-font: <?= $fontFamilyCss ?>;
        }

        * {
            box-sizing: border-box;
            font-family: var(--app-font);
        }

        body {
            margin: 0;
            min-height: 100dvh;
            position: relative;
            isolation: isolate;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -2;
            background-image: var(--auth-bg-image);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transform: scale(1.08);
            transform-origin: center;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            background: linear-gradient(180deg, rgba(var(--theme-rgb), 0.24) 0%, rgba(255, 255, 255, 0.72) 100%);
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 18px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .logo img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 10px 0 25px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--theme-color);
            box-shadow: 0 0 0 3px rgba(var(--theme-rgb), 0.15);
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 44px;
        }

        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            color: #6b7280;
            user-select: none;
            line-height: 1;
        }

        .toggle-eye:hover {
            color: #111827;
        }

        .forgot {
            text-align: right;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .forgot a {
            text-decoration: none;
            color: #6b7280;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--theme-color);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-primary:hover {
            opacity: 0.95;
        }

        .btn-primary:disabled {
            opacity: 0.72;
            cursor: not-allowed;
        }

        .login-loader {
            position: fixed;
            inset: 0;
            z-index: 20;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(8px);
        }

        .login-loader.active {
            display: flex;
        }

        .login-loader-card {
            width: min(90vw, 320px);
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid rgba(var(--theme-rgb), 0.18);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.14);
            padding: 24px;
            text-align: center;
        }

        .login-spinner {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 4px solid rgba(var(--theme-rgb), 0.16);
            border-top-color: var(--theme-color);
            margin: 0 auto 14px;
            animation: loginSpin 0.8s linear infinite;
        }

        .login-loader-title {
            margin: 0;
            color: #111827;
            font-size: 16px;
            font-weight: 800;
        }

        .login-loader-text {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        @keyframes loginSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .login-card {
                padding: 24px 20px;
                border-radius: 14px;
            }

            .login-wrapper {
                padding: 12px;
            }

            h2 {
                font-size: 18px;
            }

            .subtitle {
                font-size: 13px;
                margin-bottom: 20px;
            }

            .form-group input,
            .btn-primary {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="logo">
            <img src="<?= esc($logoUrl) ?>" alt="<?= esc($websiteName) ?>">
            <span><?= esc($websiteName) ?></span>
        </div>

        <h2>Sign in with email</h2>
        <p class="subtitle">
            <?= esc($websiteTagline) ?>
        </p>

        <form id="loginForm">
            <span id="login_msg" style="color: red;font-weight: 400;font-size: 12px;"></span>
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <div class="form-group">
                <label>Email <span>*</span></label>
                <input type="email" id="email" name="email" placeholder="Enter email" onblur="errorValidate(this)" oninput="clearError('email_error')">
                <span id="email_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
            </div>

            <div class="form-group">
                <label>Password <span>*</span></label>
                <div class="password-field">
                    <input type="password" id="password" name="password" placeholder="Enter password" onblur="errorValidate(this)" oninput="clearError('password_error')">
                    <span class="material-icons toggle-eye" onclick="togglePassword('password', this)"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/></svg></span>
                </div>
                <span id="password_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
            </div>

            <div class="forgot">
                <a href="#">Forgot password?</a>
            </div>

            <button class="btn-primary" id="loginSubmitBtn" type="submit">Get Started</button>
        </form>

    </div>
</div>

<div class="login-loader" id="loginLoader" aria-hidden="true">
    <div class="login-loader-card">
        <div class="login-spinner"></div>
        <h3 class="login-loader-title">Loading dashboard</h3>
        <p class="login-loader-text">Please wait while we open your account.</p>
    </div>
</div>

<script>
async function loginValidate()
{
    var submitBtn = document.getElementById('loginSubmitBtn');
    var loader = document.getElementById('loginLoader');

    try {
        var email       = document.getElementById('email').value.trim();
        var password    = document.getElementById('password').value.trim();
        var csrfInput   = document.querySelector('input[name="<?= csrf_token() ?>"]');
        var emailRegex  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var errorCount  = 0;

        if (email === '')
        {
            showError("email_error", "Email is required");
            errorCount++;
        }
        else if (!emailRegex.test(email))
        {
            showError("email_error", "Invalid email format");
            errorCount++;
        }

        if (password === '')
        {
            showError("password_error", "Password is required");
            errorCount++;
        }

        if (errorCount == 0)
        {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Checking...';

            const response = await fetch('<?= base_url('login/loginValidate'); ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    email: email,
                    password: password,
                    '<?= csrf_token() ?>': csrfInput.value
                })
            });

            if (!response.ok) {
                throw new Error('Network or CSRF error');
            }

            const data = await response.json();

            csrfInput.value = data.csrfHash;

            if(data.status)
            {
                document.getElementById('login_msg').innerHTML = data.message;
                document.getElementById('login_msg').style.color = 'green';
                loader.classList.add('active');
                loader.setAttribute('aria-hidden', 'false');
                submitBtn.innerHTML = 'Loading...';
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                }, 350);
            }
            else
            {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Get Started';

                if(data.input == 'email')
                {
                    showError("email_error", "Invalid email");
                }

                if(data.input == 'password')
                {
                    showError("password_error", "Invalid password");
                }
            }
        }
    }
    catch (err) {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Get Started';
        document.getElementById('login_msg').innerHTML = 'Login failed. Try again.';
        document.getElementById('login_msg').style.color = 'red';
    }
}

function errorValidate(input)
{
    var inputValue              = input.value;
    var inputId                 = input.id;
    var emailRegex              = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var errorCount              = 0;

    if (inputId === 'email' && inputValue === '')
    {
        showError("email_error", "Email is required");
        errorCount++;
    }
    else if (inputId == 'email' && !emailRegex.test(inputValue))
    {
        showError("email_error", "Invalid email format");
        errorCount++;
    }

    if (inputId === 'password' && inputValue === '')
    {
        showError("password_error", "Password is required");
        errorCount++;
    }
}

function togglePassword(inputId, icon)
{
    var input = document.getElementById(inputId);

    if(input.type === "password")
    {
        input.type = "text";
        icon.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" height="22" viewBox="0 -960 960 960" width="22" fill="#1f1f1f">
            <path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302ZM56-792l56-56 736 736-56 56-736-736Z"/>
        </svg>`;
    }
    else
    {
        input.type = "password";
        icon.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" height="22" viewBox="0 -960 960 960" width="22" fill="#1f1f1f">
            <path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Z"/>
        </svg>`;
    }
}

function showError(id, message)
{
    var el = document.getElementById(id);
    el.innerHTML = message;
    el.classList.add("show");
}

function clearError(id)
{
    var el = document.getElementById(id);
    el.innerHTML = "";
    el.classList.remove("show");
}

document.getElementById("loginForm").addEventListener("submit", function(e){
    e.preventDefault();
    loginValidate();
});

</script>

</body>
</html>
