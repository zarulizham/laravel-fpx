<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} &middot; FPX Transactions</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
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

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-end mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="themeToggle">
                <label class="form-check-label" for="themeToggle">Dark mode</label>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 m-0">FPX Transactions</h1>
            <span class="text-muted">Total: {{ number_format($transactions->total()) }}</span>
        </div>

        @if (session('status'))
            <div class="alert alert-{{ session('status.type', 'info') }} alert-dismissible fade show" role="alert">
                {{ session('status.message') }}
                @if (session('status.order_number'))
                    <span class="fw-semibold">(Order Number: {{ session('status.order_number') }})</span>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order Number</th>
                                <th>Exchange Order Number</th>
                                <th>Txn ID</th>
                                <th>Bank</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                @php
                                    $statusCode = $transaction->debit_auth_code;
                                    $statusBadgeClass = $statusCode === '00' ? 'success' : ($statusCode === '09' ? 'warning text-dark' : 'danger');
                                @endphp
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->order_number }}</td>
                                    <td>{{ $transaction->exchange_order_number }}</td>
                                    <td>{{ $transaction->transaction_id ?: '-' }}</td>
                                    <td>{{ $transaction->request_payload?->targetBankId }}</td>
                                    <td class="text-end">{{ number_format((float) str_replace(',', '', $transaction->request_payload->amount ?? 0), 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadgeClass }}">{{ $statusCode }} - {{ $transaction->response_code_description ?? 'Unknown' }}</span>
                                    </td>
                                    <td>{{ $transaction->updated_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-end">
                                        <form id="requery-form-{{ $transaction->id }}" method="POST" action="{{ route('fpx.transactions.requery', $transaction) }}">
                                            @csrf
                                        </form>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#transactionDetailsModal"
                                                data-model="{{ $transaction }}">
                                                Details
                                            </button>
                                            <button type="submit" class="btn btn-sm btn-outline-primary" form="requery-form-{{ $transaction->id }}">Requery</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No FPX transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @include('laravel-fpx::partials.transaction-details-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
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

            var toggle = document.getElementById('themeToggle');
            if (toggle) {
                toggle.addEventListener('change', function() {
                    var selectedTheme = toggle.checked ? 'dark' : 'light';
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
        })();
    </script>
</body>

</html>
