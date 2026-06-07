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
    <title>Create Account | <?= esc($websiteName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
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
            background: linear-gradient(90deg, rgba(var(--theme-rgb), 0.2), rgba(247, 251, 255, 0.75));
        }

        .register-card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            padding: 35px 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }

        .register-shell {
            width: 100%;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .logo img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        h2 {
            text-align: center;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin: 8px 0 25px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .form-group label span {
            color: red;
        }

        .form-control {
            width: 100%;
            height: 46px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--theme-color);
            box-shadow: 0 0 0 3px rgba(var(--theme-rgb), 0.15);
        }

        .password-field {
            position: relative;
        }

        .password-field .form-control {
            padding-right: 46px;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg fill='black' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--theme-color), rgba(var(--theme-rgb), 0.85));
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-submit:hover {
            opacity: 0.95;
        }

        .login-text {
            text-align: center;
            font-size: 14px;
            margin-top: 18px;
            color: #6b7280;
        }

        .login-text a {
            color: #111827;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .register-card {
                padding: 28px 22px;
                border-radius: 14px;
            }

            h2 {
                font-size: 20px;
            }

            .subtitle {
                font-size: 13px;
            }

            .register-shell {
                padding: 12px;
            }
        }
        .error{
            color: red;
            font-size: 13px;
            display: none;
            margin-top: 3px;
        }
        .error.show{
            display: block;
        }
        
        .field-label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 6px;
        }

        .mobile-wrapper {
            display: flex;
            align-items: center;
            border: 1px solid #d0d7de;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            height: 48px;
        }

        .country-box {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 0 10px;
            border-right: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .country-box img {
            width: 22px;
            height: 16px;
            object-fit: cover;
            border-radius: 2px;
        }

        .country-box select {
            border: none;
            background: transparent;
            font-size: 14px;
            cursor: pointer;
            max-width: 104px;
        }

        .country-box select:focus {
            outline: none;
        }

        .mobile-wrapper input {
            flex: 1;
            border: none;
            padding: 0 12px;
            font-size: 14px;
            height: 100%;
            background: transparent;
        }

        .mobile-wrapper .form-control {
            border: none;
            box-shadow: none;
            border-radius: 0;
        }

        .mobile-wrapper input:focus {
            outline: none;
        }

        .error-text {
            color: red;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
        .toggle-eye,
        .confirm-toggle-eye {
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

        .toggle-eye:hover,
        .confirm-toggle-eye:hover {
            color: #111827;
        }

    </style>
</head>
<body>

<div class="register-shell">
    <div class="register-card">
        <div class="logo">
            <img src="<?= esc($logoUrl) ?>" alt="<?= esc($websiteName) ?>">
            <span><?= esc($websiteName) ?></span>
        </div>

    <h2>Create your account</h2>
    <p class="subtitle">
        <?= esc($websiteTagline) ?>
    </p>

    <form id="register_form">
        <span id="success_msg"></span>
        <div class="form-group">
            <label>Username <span>*</span></label>
            <input type="text" name="username" id="username" class="form-control" onblur="validateUsername()" oninput="clearError('username_error')" placeholder="Enter username">
            <span id="username_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
        </div>

        <div class="form-group">
            <label>Email <span>*</span></label>
            <input type="email" name="email" id="email" class="form-control"  onblur="validateEmail()" oninput="clearError('email_error')" placeholder="Enter email">
            <span id="email_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
        </div>

        <div class="form-group">
           <label>Phone Number <span>*</span></label>
            <div class="mobile-wrapper">
                <div class="country-box">
                    <img id="countryFlag" src="https://flagcdn.com/w40/in.png" alt="India flag">
                    <select name="country_code" id="countryCode" aria-label="Country code"></select>
                </div>
                <input type="tel" name="phone_number" id="phone_number" class="form-control" onblur="errorValidate(this)" oninput="sanitizePhoneInput(this); clearError('phone_error')" placeholder="Enter phone number" inputmode="numeric" autocomplete="tel-national">
            </div>
            <span id="phone_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
        </div>

        <div class="form-group">
            <label>Password <span>*</span></label>
            <div class="password-field">
                <input type="password" name="password" id="password" class="form-control" onblur="errorValidate(this)" oninput="clearError('password_error')" placeholder="Enter password">
                <span class="material-icons toggle-eye" onclick="togglePassword('password', this)"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/></svg></span>
            </div>
            <span id="password_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
        </div>

        <div class="form-group">
            <label>Confirm Password <span>*</span></label>
            <div class="password-field">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" onblur="errorValidate(this)" oninput="clearError('cpassword_error')" placeholder="Enter confirm password">
                <span class="material-icons confirm-toggle-eye" onclick="togglePassword('confirm_password', this)"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/></svg></span>
            </div>
            <span id="cpassword_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
        </div>

        <div class="form-group">
            <label>Account Type <span>*</span></label>
            <select name="account_type" id="account_type" class="form-control" oninput="clearError('account_error')">
                <option value="">Select Account Type</option>
                <option value="2">Customer</option>
                <option value="3">Dealer</option>
                <option value="4">Sales Executive</option>
                <option value="5">Warehouse Staff</option>
            </select>
            <span id="account_error" style="color: red;font-weight: 400;font-size: 12px;"></span>
        </div>

        <button type="submit" class="btn-submit">Create Account</button>
    </form>

        <div class="login-text">
            Already have an account? <a href="<?= base_url('login') ?>">Sign in</a>
        </div>
    </div>
</div>

<script>
    var ___emailExists    = false;
    var ___usernameExists = false;
    var csrfName          = '<?= csrf_token() ?>';
    var csrfHash          = '<?= csrf_hash() ?>';
    var countryCodes      = [
        { name: 'India', code: '+91', flag: 'IN', min: 10, max: 10, placeholder: '10-digit mobile' },
        { name: 'United States', code: '+1', flag: 'US', min: 10, max: 10, placeholder: '10-digit phone' },
        { name: 'United Kingdom', code: '+44', flag: 'GB', min: 10, max: 10, placeholder: '10-digit phone' },
        { name: 'United Arab Emirates', code: '+971', flag: 'AE', min: 9, max: 9, placeholder: '9-digit mobile' },
        { name: 'Saudi Arabia', code: '+966', flag: 'SA', min: 9, max: 9, placeholder: '9-digit mobile' },
        { name: 'Qatar', code: '+974', flag: 'QA', min: 8, max: 8, placeholder: '8-digit mobile' },
        { name: 'Kuwait', code: '+965', flag: 'KW', min: 8, max: 8, placeholder: '8-digit mobile' },
        { name: 'Oman', code: '+968', flag: 'OM', min: 8, max: 8, placeholder: '8-digit mobile' },
        { name: 'Singapore', code: '+65', flag: 'SG', min: 8, max: 8, placeholder: '8-digit mobile' },
        { name: 'Malaysia', code: '+60', flag: 'MY', min: 9, max: 10, placeholder: '9-10 digit mobile' },
        { name: 'Australia', code: '+61', flag: 'AU', min: 9, max: 9, placeholder: '9-digit phone' },
        { name: 'Canada', code: '+1', flag: 'CA', min: 10, max: 10, placeholder: '10-digit phone' }
    ];

    function getFlagUrl(countryCode)
    {
        return 'https://flagcdn.com/w40/' + countryCode.toLowerCase() + '.png';
    }

    function selectedCountry()
    {
        var countrySelect = document.getElementById('countryCode');
        return countryCodes[Number(countrySelect.value)] || countryCodes[0];
    }

    function populateCountryCodes()
    {
        var countrySelect = document.getElementById('countryCode');
        countrySelect.innerHTML = '';

        countryCodes.forEach(function(country, index) {
            var option = document.createElement('option');
            option.value = String(index);
            option.textContent = country.flag + ' ' + country.code;
            option.dataset.code = country.code;
            option.dataset.name = country.name;
            option.dataset.min = String(country.min);
            option.dataset.max = String(country.max);
            countrySelect.appendChild(option);
        });

        countrySelect.value = '0';
        updateCountryCode();
    }

    function updateCountryCode()
    {
        var country = selectedCountry();
        var phoneInput = document.getElementById('phone_number');

        var countryFlag = document.getElementById('countryFlag');
        countryFlag.src = getFlagUrl(country.flag);
        countryFlag.alt = country.name + ' flag';
        phoneInput.placeholder = country.placeholder;
        clearError('phone_error');
    }

    function sanitizePhoneInput(input)
    {
        input.value = input.value.replace(/\D/g, '');
    }

    function validatePhoneNumber(phoneNumber)
    {
        var country = selectedCountry();
        var digits = phoneNumber.replace(/\D/g, '');

        if (digits === '') {
            return 'Phone number is required';
        }

        if (digits.length < country.min || digits.length > country.max) {
            return country.name + ' phone number must be ' + (country.min === country.max ? country.min : country.min + '-' + country.max) + ' digits';
        }

        return true;
    }

    async function saveData()
    {
        var username            = document.getElementById("username").value;
        var email               = document.getElementById("email").value;
        var password            = document.getElementById("password").value;
        var confirm_password    = document.getElementById("confirm_password").value;
        var phone_number        = document.getElementById("phone_number").value;
        var role_id             = document.getElementById("account_type").value;
        var password_check      = validatepassword(password);
        var phone_check         = validatePhoneNumber(phone_number);
        var errorCount          = 0;

        if(username == '')
        {
            showError("username_error", "Username is required");
            errorCount++;
        }
        else if(___usernameExists) 
        {
            showError("username_error", "Username already exists");
            errorCount++;
        }
                
        if(email == '')
        {
            showError("email_error", "Email is required");
            errorCount++;  
        }
        else if(!isValidEmail(email))
        {
            showError("email_error", "Invalid email");
            errorCount++;
        } 
        else if(___emailExists)
        {
            errorCount++;
            showError("email_error", "Email already exists");
        }

        if(password == '')
        {
            showError("password_error", "Password is required");
            errorCount++;   
        }
        else if(password_check !== true)
        {
            showError("password_error",password_check);
            errorCount++;  
        }
        if(confirm_password == '')
        {
            showError("cpassword_error", "Confirm password is required");
            errorCount++;  
        }
        else if(password != confirm_password)
        {
            showError("cpassword_error", "Passwords do not match");
            errorCount++;
        }

        if(phone_check !== true)
        {
            showError("phone_error", phone_check);
            errorCount++;  
        }

        if(role_id == '')
        {
            showError("account_error", "Account type is required");
            errorCount++;
        }
        
        if(errorCount == 0)
        {
           try {
                const form = document.getElementById('register_form');
                const formData = new FormData(form);
                formData.set('phone_number', document.getElementById('phone_number').value.replace(/\D/g, ''));
                formData.set('country_code', selectedCountry().code);

                const response = await fetch('<?= base_url('register/registerData'); ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams(formData)
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                // Update CSRF token
                const csrfInput = form.querySelector('input[name="<?= csrf_token() ?>"]');
                if (csrfInput && data.csrf) {
                    csrfInput.value = data.csrf;
                }

                if (data.status) {
                    document.getElementById('success_msg').innerHTML = data.message;
                    document.getElementById('success_msg').style.color = 'green';
                    form.reset();
                    setTimeout(() => {
                        window.location.href = "<?= base_url('login') ?>";
                    }, 1000);
                } else {
                    document.getElementById('success_msg').innerHTML = data.message;
                    document.getElementById('success_msg').style.color = 'red';
                }

            } catch (error) {
                console.error('Fetch error:', error);
                document.getElementById('success_msg').innerHTML = 'Something went wrong. Try again.';
                document.getElementById('success_msg').style.color = 'red';
            }  
        }
    }

    function validateUsername() 
    {
        var username = document.getElementById("username").value.trim();

        if(username === "") 
        {
            showError("username_error", "Username is required");
            return false;
        }

        if (username.length < 4)
        {
            showError("username_error", "Minimum 4 characters required");
            return false;
        }

        if (username.length > 15)
        {
            showError("username_error", "Maximum 15 characters only allowed");
            return false;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(username)) 
        {
            showError("username_error", "Only letters, numbers & underscore allowed");
            return false;
        }

        checkUsernameExists(username);
    }

    function checkUsernameExists(username) 
    {
        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append(csrfName, csrfHash);

        fetch("<?= base_url('register/usernameValidation') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            // Update CSRF token if returned (important)
            if (data.csrfHash) {
                window.csrfHash = data.csrfHash;
            }

            if (data.exists) {
                ___usernameExists = true;
                showError("username_error", "Username already exists");
            } else {
                ___usernameExists = false;
                clearError("username_error");
            }
        });
    }


    function validateEmail()
    {
        var email = document.getElementById("email").value.trim();

        if(email === "")
        {
            showError("email_error", "Email is required");
            return false;
        }

        if(!isValidEmail(email)) 
        {
            showError("email_error", "Invalid email");
            return false;
        }

        checkEmailExists(email);
    }

    function isValidEmail(email) 
    {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
    }

    function checkEmailExists(email) 
    {
        const formData = new URLSearchParams();
        formData.append('email', email);
        formData.append(csrfName, csrfHash);

        fetch("<?= base_url('register/emailValidation') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {

            if (data.csrfHash) {
                csrfHash = data.csrfHash;
            }

            if (data.exists) {
                ___emailExists = true;
                showError("email_error", "Email already exists");
            } else {
                ___emailExists = false;
                clearError("email_error");
            }
        });
    }

    function validatepassword(password)
    {
        if(password.length < 8) 
        {
            return "Password must be at least 8 characters";
        }
        if(password.length > 12) 
        {
            return "Password must not exceed 12 characters";
        }
        if(!/[A-Z]/.test(password)) 
        {
            return "Password must contain at least one uppercase letter";
        }
        if(!/[a-z]/.test(password)) 
        {
            return "Password must contain at least one lowercase letter";
        }
        if(!/[0-9]/.test(password)) 
        {
            return "Password must contain at least one number";
        }
        if(!/[@$!%*?&#]/.test(password)) 
        {
            return "Password must contain at least one special character (@$!%*?&#)";
        }

        return true;
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


    function errorValidate(input)
    {
        var inputValue              = input.value;
        var inputId                 = input.id;
        var passCheck               = validatepassword(inputValue);
        var confirmPasswordCheck    = validateConfirmPassword();
        var phoneCheck              = validatePhoneNumber(inputValue);
        var errorCount              = 0;

        if(inputId == 'username' && inputValue == '')
        {
            showError("username_error", "Username is required");
            errorCount++;
        } 

        if(inputId == 'password' && inputValue == '')
        {
            showError("password_error", "Password is required");
            errorCount++;   
        }
        else if(inputId == 'password' && passCheck !== true)
        {
            showError("password_error", passCheck);
            errorCount++;  
        }

        if(inputId == 'phone_number' && phoneCheck !== true)
        {
            showError("phone_error", phoneCheck);
            errorCount++;  
        }

        if(inputId == 'confirm_password' && inputValue == '')
        {
            showError("cpassword_error", "Confirm password is required");
            errorCount++;  
        }
        else if(inputId == 'confirm_password' && confirmPasswordCheck !== true)
        {
            showError("cpassword_error", "Passwords do not match");
            errorCount++;
        }
    }

    function validateConfirmPassword()
    {
        var password            = document.getElementById("password").value;
        var confirm_password    = document.getElementById("confirm_password").value;

        if(password != confirm_password)
        {
            return false;
        }

        return true;
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
   
    document.getElementById("register_form").addEventListener("submit", function(e){
        e.preventDefault();
        saveData();
    });

    document.getElementById('countryCode').addEventListener('change', updateCountryCode);
    populateCountryCodes();


</script>

</body>
</html>

