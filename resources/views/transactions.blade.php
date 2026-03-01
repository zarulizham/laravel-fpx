<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} &middot; FPX Transactions</title>

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

        .btn-outline-primary {
            --bs-btn-color: #4188C6;
            --bs-btn-border-color: #4188C6;
            --bs-btn-hover-bg: #4188C6;
            --bs-btn-hover-border-color: #4188C6;
            --bs-btn-active-bg: #316da0;
            --bs-btn-active-border-color: #316da0;
            --bs-btn-disabled-color: #4188C6;
            --bs-btn-disabled-border-color: #4188C6;
        }

        body {
            font-family: 'Rubik', sans-serif;
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
    <div id="transactions-app" class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 m-0">FPX Transactions</h1>
            <span class="text-muted">Total: @{{ pagination.total }}</span>
        </div>

        <div v-if="alert.message" :class="['alert', 'alert-' + alert.type, 'alert-dismissible', 'fade', 'show']" role="alert">
            @{{ alert.message }}
            <span v-if="alert.orderNumber" class="fw-semibold">(Order Number: @{{ alert.orderNumber }})</span>
            <button type="button" class="btn-close" aria-label="Close" @click="clearAlert"></button>
        </div>

        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <form class="row g-2" @submit.prevent="applyFilters">
                    <div class="col-md-6">
                        <input type="text" class="form-control" v-model.trim="filters.search" placeholder="Search order number / Txn. ID">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" v-model="filters.status">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100" :disabled="loading">Search</button>
                        <button type="button" class="btn btn-outline-secondary w-100" :disabled="loading" @click="resetFilters">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
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
                            <tr v-if="loading">
                                <td colspan="9" class="text-center py-4 text-muted">Loading transactions...</td>
                            </tr>
                            <tr v-else-if="transactions.length === 0">
                                <td colspan="9" class="text-center py-4 text-muted">No FPX transactions found.</td>
                            </tr>
                            <tr v-else v-for="transaction in transactions" :key="transaction.id">
                                <td>@{{ transaction.id }}</td>
                                <td>@{{ transaction.order_number || '-' }}</td>
                                <td>@{{ transaction.exchange_order_number || '-' }}</td>
                                <td>@{{ transaction.transaction_id || '-' }}</td>
                                <td>@{{ transaction.bank || '-' }}</td>
                                <td class="text-end">@{{ transaction.amount }}</td>
                                <td>
                                    <span :class="['badge', 'bg-' + transaction.status_class]">
                                        @{{ transaction.status_code }} - @{{ transaction.status_text }}
                                    </span>
                                </td>
                                <td>@{{ transaction.updated_at || '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="openDetails(transaction)">
                                            Details
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" @click="requery(transaction)">
                                            Requery
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4" v-if="transactions.length > 0">
            <small class="text-muted">
                Showing @{{ pagination.from || 0 }}-@{{ pagination.to || 0 }} of @{{ pagination.total }}
            </small>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" :disabled="loading || pagination.currentPage <= 1" @click="previousPage">Previous</button>
                <button class="btn btn-outline-secondary" :disabled="loading || pagination.currentPage >= pagination.lastPage" @click="nextPage">Next</button>
            </div>
        </div>

        <div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transactionDetailsModalLabel">FPX Transaction Details #@{{ selectedTransaction.id || '-' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <h6>Transaction Info</h6>
                                <div class="border rounded p-3 bg-body-tertiary">
                                    <div><strong>Order Number:</strong> @{{ selectedTransaction.order_number || '-' }}</div>
                                    <div><strong>Exchange Order Number:</strong> @{{ selectedTransaction.exchange_order_number || '-' }}</div>
                                    <div><strong>Reference ID:</strong> @{{ selectedTransaction.reference_id || '-' }}</div>
                                    <div><strong>Reference Type:</strong> @{{ selectedTransaction.reference_type || '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Request Payload</h6>
                                <pre class="border rounded p-3 bg-body-secondary mb-0"><code>@{{ prettyJson(selectedTransaction.request_payload) }}</code></pre>
                            </div>
                            <div class="col-md-6">
                                <h6>Response Payload</h6>
                                <pre class="border rounded p-3 bg-body-secondary mb-0"><code>@{{ prettyJson(selectedTransaction.response_payload) }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
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

            function syncThemeToggle() {
                var toggle = document.getElementById('themeToggle');
                if (!toggle) {
                    return;
                }

                toggle.checked = (document.documentElement.getAttribute('data-bs-theme') || 'light') === 'dark';
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

            var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
            var detailsModal = null;

            var app = Vue.createApp({
                data: function() {
                    return {
                        loading: false,
                        transactions: [],
                        pagination: {
                            total: 0,
                            from: 0,
                            to: 0,
                            currentPage: 1,
                            lastPage: 1,
                            nextPageUrl: null,
                            prevPageUrl: null,
                        },
                        alert: {
                            type: 'info',
                            message: '',
                            orderNumber: '',
                        },
                        filters: {
                            search: '',
                            status: '',
                        },
                        selectedTransaction: {},
                    };
                },
                mounted: function() {
                    var modalElement = document.getElementById('transactionDetailsModal');
                    if (modalElement) {
                        detailsModal = new bootstrap.Modal(modalElement);
                    }

                    this.fetchTransactions(1);

                    this.$nextTick(function() {
                        syncThemeToggle();
                    });
                },
                methods: {
                    fetchTransactions: async function(page) {
                        if (page < 1) {
                            return;
                        }

                        this.loading = true;

                        try {
                            var params = new URLSearchParams({
                                page: String(page)
                            });

                            if (this.filters.search) {
                                params.set('search', this.filters.search);
                            }

                            if (this.filters.status) {
                                params.set('status', this.filters.status);
                            }

                            var response = await fetch("{{ route('fpx.transactions.data') }}?" + params.toString(), {
                                headers: {
                                    'Accept': 'application/json',
                                }
                            });

                            if (!response.ok) {
                                throw new Error('Failed to fetch transactions.');
                            }

                            var payload = await response.json();
                            this.transactions = payload.data || [];
                            this.pagination = {
                                total: payload.total || 0,
                                from: payload.from || 0,
                                to: payload.to || 0,
                                currentPage: payload.current_page || 1,
                                lastPage: payload.last_page || 1,
                                nextPageUrl: payload.next_page_url,
                                prevPageUrl: payload.prev_page_url,
                            };
                        } catch (error) {
                            this.alert = {
                                type: 'danger',
                                message: error.message || 'Unable to load transactions.',
                                orderNumber: '',
                            };
                        } finally {
                            this.loading = false;
                        }
                    },
                    requery: async function(transaction) {
                        try {
                            var response = await fetch(transaction.requery_url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({})
                            });

                            if (!response.ok) {
                                throw new Error('Unable to requery transaction status.');
                            }

                            var payload = await response.json();
                            this.alert = {
                                type: payload.type || 'info',
                                message: payload.message || 'Requery completed.',
                                orderNumber: payload.order_number || transaction.order_number || '',
                            };

                            if (payload.transaction && payload.transaction.id) {
                                var index = this.transactions.findIndex(function(row) {
                                    return row.id === payload.transaction.id;
                                });

                                if (index !== -1) {
                                    this.transactions.splice(index, 1, payload.transaction);
                                }

                                if (this.selectedTransaction && this.selectedTransaction.id === payload.transaction.id) {
                                    this.selectedTransaction = payload.transaction;
                                }
                            }
                        } catch (error) {
                            this.alert = {
                                type: 'danger',
                                message: error.message || 'Unable to requery transaction status.',
                                orderNumber: transaction.order_number || '',
                            };
                        }
                    },
                    openDetails: function(transaction) {
                        this.selectedTransaction = transaction || {};
                        if (detailsModal) {
                            detailsModal.show();
                        }
                    },
                    prettyJson: function(value) {
                        return JSON.stringify(value || {}, null, 2);
                    },
                    clearAlert: function() {
                        this.alert = {
                            type: 'info',
                            message: '',
                            orderNumber: '',
                        };
                    },
                    applyFilters: function() {
                        this.fetchTransactions(1);
                    },
                    resetFilters: function() {
                        this.filters.search = '';
                        this.filters.status = '';
                        this.fetchTransactions(1);
                    },
                    previousPage: function() {
                        if (this.pagination.currentPage <= 1 || this.loading) {
                            return;
                        }

                        this.fetchTransactions(this.pagination.currentPage - 1);
                    },
                    nextPage: function() {
                        if (this.pagination.currentPage >= this.pagination.lastPage || this.loading) {
                            return;
                        }

                        this.fetchTransactions(this.pagination.currentPage + 1);
                    }
                }
            });

            app.mount('#transactions-app');
        })();
    </script>
</body>

</html>
