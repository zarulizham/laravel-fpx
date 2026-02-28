<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} &middot; Sample Checkout Form</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-end pt-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="themeToggle">
                <label class="form-check-label" for="themeToggle">Dark mode</label>
            </div>
        </div>
        <div class="py-5 text-center">
            <h2>Checkout form</h2>
            <p class="lead">Below is an example form built entirely with Bootstrap's form controls to to demonstrate
                payment flow. Each required form group has a validation state that can be triggered by attempting to
                submit the form without completing it.</p>
        </div>

        <form class="needs-validation" novalidate method="POST" action="{{ route('fpx.payment.auth.request') }}">
            @csrf
            <input type="hidden" name="reference_id" value="1" />
            <input type="hidden" name="reference_type" value="App\\Models\\Bill" />
            <input type="hidden" name="response_format" value="{{ $response_format }}" />
            <input type="hidden" name="additional_params" value="{{ $request->additional_params ?? '' }}" />
            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ implode(',', $errors->all()) }}
                </div>
            @endif
            <div class="row">
                <div class="col-md-4 order-md-2 mb-4">
                    <div class="border p-3 mb-3 rounded">
                        <h4>Payment Details</h4>
                        <p class="mb-3 pt-1">Please select your payment details.</p>

                        <div id="flow-limit-alert" class="alert alert-info">Minimum RM 1.00 and maximum RM 30,000.00
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6 col-sm-12">
                                <div class="custom-control custom-radio">
                                    <img src="{{ asset('assets/vendor/fpx/images/fpx.svg') }}" height="64px">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="select_bank"></div>
                            <div class="col">
                                <select name="flow" id="flow" class="form-select" required>
                                    <option value="01">01 - B2C</option>
                                    <option value="02">02 - B2B</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="select_bank"></div>
                            <div class="col">
                                <select name="bank_id" class="form-select" required>
                                    <option value="">Select Bank</option>
                                    @foreach ($banks->toArray() as $bankId => $bankName)
                                        <option value="{{ $bankName['bank_id'] }}">
                                            {{ $bankName['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <div class="custom-control custom-checkbox">
                                    <label class="custom-control-label" for="agree">By clicking on "proceed", you agree
                                        to the <a href="https://www.mepsfpx.com.my/FPXMain/termsAndConditions.jsp"
                                            target="_blank">terms and conditions</a> of FPX.</label>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg w-100" type="submit">Proceed</button>
                    </div>
                </div>
                <div class="col-md-8 order-md-1">
                    <div class="border p-3 mb-3 rounded">
                        <h4 class="mb-3">Billing details</h4>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="customer_name">Buyer name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                    placeholder="Enter buyer name"
                                    value="{{ $test ? 'Test Buyer Name' : $request->customer_name }}" required>
                                <div class="invalid-feedback">
                                    Valid buyer name is required.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount"
                                placeholder="1.00" value="{{ $test ? '1.0' : $request->amount }}" required>
                            <div class="invalid-feedback">
                                Please enter a valid amount.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="customer_email">Email</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email"
                                value="{{ $test ? 'hello@example.com' : $request->customer_email }}"
                                placeholder="you@example.com" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="order_number">Order Number</label>
                            <input type="text" class="form-control" id="order_number" name="order_number"
                                placeholder="Enter Order Number" value="{{ $request->order_number ?? uniqid() }}" required>
                            <div class="invalid-feedback">
                                Please enter a valid order number
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="exchange_order_number">Exchange Order Number (Optional)</label>
                            <input type="text" class="form-control" id="exchange_order_number" name="exchange_order_number"
                                placeholder="Auto-generated if empty" value="{{ $request->exchange_order_number ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        (function() {
            'use strict';

            var themeStorageKey = 'laravel-fpx-theme';

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

            function updateFlowLimits() {
                var flow = document.getElementById('flow');
                var amount = document.getElementById('amount');
                var alertBox = document.getElementById('flow-limit-alert');

                if (!flow || !amount || !alertBox) {
                    return;
                }

                var limitsByFlow = {
                    '01': {
                        min: 1,
                        max: 30000
                    },
                    '02': {
                        min: 2,
                        max: 1000000
                    }
                };

                var selectedFlow = flow.value || '01';
                var limits = limitsByFlow[selectedFlow] || limitsByFlow['01'];

                amount.min = limits.min;
                amount.max = limits.max;
                alertBox.textContent = 'Minimum RM ' + limits.min.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' and maximum RM ' + limits.max.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            window.addEventListener('load', function() {
                applyTheme(getActiveTheme());

                var themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.addEventListener('change', function() {
                        var selectedTheme = themeToggle.checked ? 'dark' : 'light';
                        setStoredTheme(selectedTheme);
                        applyTheme(selectedTheme);
                    });
                }

                var colorScheme = window.matchMedia('(prefers-color-scheme: dark)');
                colorScheme.addEventListener('change', function() {
                    if (!getStoredTheme()) {
                        applyTheme(getOsPreferredTheme());
                    }
                });

                updateFlowLimits();

                var flow = document.getElementById('flow');
                if (flow) {
                    flow.addEventListener('change', updateFlowLimits);
                }

                var forms = document.getElementsByClassName('needs-validation');

                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>

</html>
