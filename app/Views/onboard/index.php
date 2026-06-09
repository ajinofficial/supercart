<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Onboard Account</title>
    <style>
        :root {
            --bg: #f6f8fb;
            --panel: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #dbe2ea;
            --primary: #0f766e;
            --primary-dark: #0b5f59;
            --danger: #b42318;
            --success: #067647;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            width: min(480px, calc(100vw - 32px));
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 28px;
            box-shadow: 0 16px 40px rgba(31, 41, 55, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 24px;
            line-height: 1.25;
        }

        p {
            margin: 0 0 24px;
            color: var(--muted);
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-weight: 700;
            font-size: 14px;
        }

        input {
            width: 100%;
            height: 44px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0 12px;
            font-size: 15px;
        }

        .domain-field {
            display: flex;
            width: 100%;
        }

        .domain-field input {
            border-radius: 6px 0 0 6px;
        }

        .domain-field span {
            display: inline-flex;
            align-items: center;
            height: 44px;
            border: 1px solid var(--line);
            border-left: 0;
            border-radius: 0 6px 6px 0;
            background: #eef2f6;
            padding: 0 18px;
            color: var(--text);
            font-size: 15px;
            white-space: nowrap;
        }

        input:focus {
            border-color: var(--primary);
            outline: 3px solid rgba(15, 118, 110, 0.14);
        }

        button {
            width: 100%;
            height: 44px;
            margin-top: 24px;
            border: 0;
            border-radius: 6px;
            background: var(--primary);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        button:hover {
            background: var(--primary-dark);
        }

        button:disabled {
            background: var(--primary-dark);
            cursor: wait;
            opacity: 0.86;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .is-loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .message {
            margin-bottom: 18px;
            border-radius: 6px;
            padding: 12px;
            font-size: 14px;
            line-height: 1.45;
        }

        .error {
            background: #fff1f0;
            color: var(--danger);
            border: 1px solid #ffdad6;
        }

        .success {
            background: #ecfdf3;
            color: var(--success);
            border: 1px solid #abefc6;
        }
    </style>
</head>
<body>
    <main>
        <h1>Onboard account</h1>
        <p>Enter the new domain and Gmail address. The account record and database will be created immediately.</p>

        <div id="response-message" class="message" hidden></div>

        <form id="onboard-form" method="post" action="<?= site_url('onboard/create') ?>">
            <?= csrf_field() ?>
            <?php
                $domainSuffix = \Config\GlobalSettings::TENANT_DOMAIN_SUFFIX;
                $oldDomain = (string) old('domain');
                $oldDomainName = (string) old('domain_name');

                if ($oldDomainName === '' && $oldDomain !== '' && str_ends_with($oldDomain, $domainSuffix)) {
                    $oldDomainName = substr($oldDomain, 0, -strlen($domainSuffix));
                }
            ?>

            <label for="domain">Domain</label>
            <div class="domain-field">
                <input id="domain" name="domain_name" type="text" value="<?= esc($oldDomainName) ?>" placeholder="Domain Name" required>
                <span><?= esc($domainSuffix) ?></span>
            </div>

            <label for="gmail">Gmail</label>
            <input id="gmail" name="gmail" type="email" value="<?= old('gmail') ?>" placeholder="name@gmail.com" required>

            <button id="submit-button" type="submit">
                <span class="spinner" aria-hidden="true"></span>
                <span class="button-text">Create Account</span>
            </button>
        </form>
    </main>
    <script>
        const form = document.getElementById('onboard-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = submitButton.querySelector('.button-text');

        const responseMessage = document.getElementById('response-message');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            submitButton.disabled = true;
            submitButton.classList.add('is-loading');
            buttonText.textContent = 'Creating...';
            responseMessage.hidden = true;
            responseMessage.className = 'message';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();

                if (result.csrf) {
                    const csrfInput = form.querySelector(`input[name="${result.csrf.name}"]`);

                    if (csrfInput) {
                        csrfInput.value = result.csrf.hash;
                    }
                }

                responseMessage.textContent = result.message || 'Unable to onboard account.';
                responseMessage.classList.add(result.success ? 'success' : 'error');
                responseMessage.hidden = false;

                if (result.success) {
                    form.reset();
                }
            } catch (error) {
                responseMessage.textContent = 'The request failed. Please try again.';
                responseMessage.classList.add('error');
                responseMessage.hidden = false;
            } finally {
                submitButton.disabled = false;
                submitButton.classList.remove('is-loading');
                buttonText.textContent = 'Create Account';
            }
        });
    </script>
</body>
</html>
