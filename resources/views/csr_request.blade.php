<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} &middot; CSR Generation Form</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root,
        [data-bs-theme="light"],
        [data-bs-theme="dark"] {
            --bs-primary: #4188C6;
            --bs-primary-rgb: 65, 136, 198;
        }

        .btn-primary {
            --bs-btn-bg: #4188C6;
            --bs-btn-border-color: #4188C6;
            --bs-btn-hover-bg: #3677ae;
            --bs-btn-hover-border-color: #3677ae;
            --bs-btn-active-bg: #316da0;
            --bs-btn-active-border-color: #316da0;
            --bs-btn-disabled-bg: #4188C6;
            --bs-btn-disabled-border-color: #4188C6;
        }

        body {
            font-family: 'Rubik', sans-serif;
        }

        .csr-help-content {
            min-height: 100px;
        }

        .csr-command {
            min-height: 200px;
            font-size: 0.875rem;
            white-space: pre;
            overflow: auto;
            margin-bottom: 0;
        }

        .csr-copy-feedback {
            font-size: 0.875rem;
        }
    </style>

    <script>
        (function() {
            var themeStorageKey = 'laravel-fpx-theme';

            function getStoredTheme() {
                try {
                    return localStorage.getItem(themeStorageKey);
                } catch (error) {
                    return null;
                }
            }

            function getOsPreferredTheme() {
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            var initialTheme = getStoredTheme() || getOsPreferredTheme();
            document.documentElement.setAttribute('data-bs-theme', initialTheme);
        })();
    </script>
</head>

<body class="bg-body-tertiary">
    <nav class="navbar navbar-expand-lg border-bottom mb-4">
        <div class="container">
            <a class="navbar-brand m-0 p-0" href="#" aria-label="FPX">
                <img src="{{ asset('assets/vendor/fpx/images/fpx.svg') }}" alt="FPX" height="45">
            </a>
            <div class="form-check form-switch ms-auto mb-0 d-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" role="switch" id="themeToggle">
                <label class="form-check-label" for="themeToggle">Dark mode</label>
            </div>
        </div>
    </nav>

    <div class="container pb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 m-0">CSR Generation Form</h1>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ implode(', ', $errors->all()) }}
            </div>
        @endif

        <form class="needs-validation" novalidate method="POST" action="{{ route('fpx.payment.auth.request') }}" id="csrForm">
            @csrf
            <input type="hidden" name="flow" value="01" />
            <input type="hidden" name="order_number" value="{{ uniqid() }}" />
            <input type="hidden" name="datetime" value="{{ now() }}" />

            <div class="row g-3">
                <div class="col-lg-4 order-lg-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Information</h5>
                            <div id="csr-panel" class="text-body-secondary csr-help-content">
                                Fill the form and click on Generate to Generate your CSR.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 order-lg-1">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">Certificate Details</h5>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="common_name" class="form-label">Common name</label>
                                    <input type="text" class="form-control" id="common_name" name="common_name"
                                        placeholder="Enter common name(Exchange ID)"
                                        value="{{ $exchangeId = Config::get('fpx.exchange_id', '') }}"
                                        {{ !empty($exchangeId) ? 'readonly' : '' }} required>
                                    <div class="invalid-feedback">
                                        Valid common name is required.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="organization" class="form-label">Organization</label>
                                <input type="name" class="form-control" id="organization" name="organization"
                                    placeholder="Enter organization name" required>
                                <div class="invalid-feedback">
                                    Please enter a valid organization.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="org_unit" class="form-label">Organizational Unit</label>
                                <input type="text" readonly class="form-control" id="org_unit" name="org_unit"
                                    placeholder="PayNet FPX" required value="PayNet FPX">
                                <div class="invalid-feedback">
                                    Please enter a valid department.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                    placeholder="Enter city" required>
                                <div class="invalid-feedback">
                                    Please enter a valid city.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="state" class="form-label">State / Province</label>
                                <input type="text" class="form-control" id="state" name="state"
                                    placeholder="Enter state / province" required>
                                <div class="invalid-feedback">
                                    Please enter a valid state / province.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="country" class="form-label">Country Code</label>
                                <input type="text" class="form-control" id="country" name="country"
                                    placeholder="Enter country" required value="MY">
                                <div class="invalid-feedback">
                                    Please enter a valid country.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="key_size" class="form-label">Key Size</label>
                                <select name="key_size" id="key_size" class="form-select">
                                    <option value="2048" selected>RSA 2048 (recommended)</option>
                                    <option value="4096">RSA 4096</option>
                                    <option value="p256">P-256 (elliptic curve)</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select key size
                                </div>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">Generate</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="d-none">
        <div class="csrDescription" id="descriptionFor_common_name">
            <b>Common Name</b> (Exchange ID)<br><br>Please enter Exchange ID you received from FPX.
        </div>
        <div class="csrDescription" id="descriptionFor_org_unit">
            Default to "PayNet FPX" based on latest announcement from FPX. Please do not change this value.
        </div>
        <div class="csrDescription" id="descriptionFor_city">
            <b>City</b><br><br>The city where your organization is legally located.
        </div>
        <div class="csrDescription" id="descriptionFor_state">
            <b>State or Province</b><br><br>The state or province where your organization is legally located.
        </div>
        <div class="csrDescription" id="descriptionFor_country">
            <b>Country</b><br><br>We guessed your country based on your IP address, but if we guessed wrong, please
            choose the correct country. If your country does not appear in this list, there is a chance we cannot issue
            certificates to organizations in your country.
        </div>
        <div class="csrDescription" id="descriptionFor_organization">
            <b>Organization name</b><br><br>The exact legal name of your organization, (e.g., <i>DigiCert,
                Inc.</i>)<br><br>If you do not have a legal registered organization name, you should enter your own full
            name here.
        </div>
        <div class="csrDescription" id="descriptionFor_key_size">
            <b>Key</b><br><br>RSA Key sizes smaller than 2048 are considered unsecure.
        </div>
        <div class="csrDescription" id="descriptionFor_infotext">
            Now just copy and paste this command into a terminal session on your server. Your CSR and Private key will
            be written to
            ###FILE###.csr and ###FILE###.key respectively.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        (function() {
            'use strict';

            var themeStorageKey = 'laravel-fpx-theme';
            var csrPanel = document.getElementById('csr-panel');
            var generatedCsrCommand = '';

            function getStoredTheme() {
                try {
                    return localStorage.getItem(themeStorageKey);
                } catch (error) {
                    return null;
                }
            }

            function setStoredTheme(theme) {
                try {
                    localStorage.setItem(themeStorageKey, theme);
                } catch (error) {
                    // Ignore storage write errors.
                }
            }

            function getOsPreferredTheme() {
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            function getActiveTheme() {
                return getStoredTheme() || getOsPreferredTheme();
            }

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);

                var toggle = document.getElementById('themeToggle');
                if (toggle) {
                    toggle.checked = theme === 'dark';
                }
            }

            applyTheme(getActiveTheme());

            document.addEventListener('change', function(event) {
                if (!event.target || event.target.id !== 'themeToggle') {
                    return;
                }

                var selectedTheme = event.target.checked ? 'dark' : 'light';
                setStoredTheme(selectedTheme);
                applyTheme(selectedTheme);
            });

            var colorScheme = window.matchMedia('(prefers-color-scheme: dark)');
            var colorSchemeChangeHandler = function() {
                if (!getStoredTheme()) {
                    applyTheme(getOsPreferredTheme());
                }
            };

            if (typeof colorScheme.addEventListener === 'function') {
                colorScheme.addEventListener('change', colorSchemeChangeHandler);
            } else if (typeof colorScheme.addListener === 'function') {
                colorScheme.addListener(colorSchemeChangeHandler);
            }

            function updateCsrPanel(id) {
                var description = document.getElementById('descriptionFor_' + id);
                if (description && csrPanel) {
                    csrPanel.innerHTML = description.innerHTML;
                }
            }

            function escapeHtml(value) {
                return value
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function setCopyFeedback(message, isSuccess) {
                var feedback = document.getElementById('csr-copy-feedback');
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.classList.remove('text-success', 'text-danger');
                feedback.classList.add(isSuccess ? 'text-success' : 'text-danger');
            }

            function fallbackCopyCommand(command) {
                var textarea = document.createElement('textarea');
                textarea.value = command;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.top = '0';
                textarea.style.left = '0';
                textarea.style.opacity = '0';

                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                textarea.setSelectionRange(0, textarea.value.length);

                var copied = false;
                try {
                    copied = document.execCommand('copy');
                } catch (error) {
                    copied = false;
                }

                document.body.removeChild(textarea);

                if (copied) {
                    setCopyFeedback('Command copied to clipboard.', true);
                } else {
                    setCopyFeedback('Unable to copy automatically. Please copy manually.', false);
                }
            }

            function copyCommandToClipboard(command) {
                if (!command) {
                    setCopyFeedback('Nothing to copy yet.', false);
                    return;
                }

                if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
                    navigator.clipboard.writeText(command).then(function() {
                        setCopyFeedback('Command copied to clipboard.', true);
                    }).catch(function() {
                        fallbackCopyCommand(command);
                    });
                    return;
                }

                fallbackCopyCommand(command);
            }

            window.addEventListener('load', function() {
                var form = document.getElementById('csrForm');
                if (!form) {
                    return;
                }

                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (form.checkValidity() !== false) {
                        var commonName = document.getElementById('common_name');
                        var keySize = document.getElementById('key_size');
                        var country = document.getElementById('country');
                        var state = document.getElementById('state');
                        var city = document.getElementById('city');
                        var organization = document.getElementById('organization');
                        var orgUnit = document.getElementById('org_unit');
                        var infoText = document.getElementById('descriptionFor_infotext');

                        var exchangeId = commonName.value.trim().toUpperCase();
                        var req =
                            'openssl req -new -newkey rsa:' + keySize.value +
                            ' -nodes -out ' + exchangeId +
                            '.csr -keyout ' +
                            exchangeId + '.key -subj "/C=' +
                            country.value +
                            '/ST=' + state.value + '/L=' + city.value + '/O=' +
                            organization.value + '/OU=' + orgUnit.value +
                            '/CN=' + exchangeId + '"';

                        generatedCsrCommand = req;

                        if (csrPanel && infoText) {
                            csrPanel.innerHTML = infoText.innerHTML.replaceAll('###FILE###', exchangeId) +
                                '<div class="mt-3">' +
                                '<pre class="form-control csr-command text-wrap" id="csr-command-output">' +
                                escapeHtml(req) +
                                '</pre>' +
                                '<div class="d-flex justify-content-between align-items-center mt-2 gap-2">' +
                                '<button type="button" class="btn btn-sm btn-outline-primary text-nowrap" id="csr-copy-btn">Copy command</button>' +
                                '<span id="csr-copy-feedback" class="csr-copy-feedback text-body-secondary"></span>' +
                                '</div>' +
                                '</div>';
                        }
                    }

                    form.classList.add('was-validated');
                }, false);

                var fields = document.querySelectorAll('input, select');
                Array.prototype.forEach.call(fields, function(field) {
                    field.addEventListener('focus', function() {
                        updateCsrPanel(field.id);
                    });
                });

                document.addEventListener('click', function(event) {
                    if (event.target && event.target.id === 'csr-copy-btn') {
                        copyCommandToClipboard(generatedCsrCommand);
                    }
                });
            }, false);
        })();
    </script>
</body>

</html>
