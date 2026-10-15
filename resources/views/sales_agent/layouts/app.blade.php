<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Sales Agent Portal' }}</title>
    <!-- Font: Roboto (same as the admin panel) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <style>
        /* Snapkid brand colours - the same 4 colours as the admin panel and the unit worker app */
        :root {
            --primary-color: #05421c;        /* brand dark green */
            --primary-light: #36b54a;        /* wordmark green */
            --primary-dark: #032b12;
            --accent-yellow: #fcee21;        /* sun yellow */
            --accent-yellow-light: #fffde0;
            --secondary-color: #8bc63e;      /* light (mascot) green */
            --bg-color: #f3f6f1;
            --card-shadow: 0 1px 3px rgba(5, 66, 28, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            --header-height: 60px;
            --nav-height: 64px;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: #1f2d1f;
            padding-bottom: calc(var(--nav-height) + 8px);
            -webkit-tap-highlight-color: transparent;
        }

        /* ---------- top bar (dark green + yellow strip, as the unit app) ---------- */
        .agent-header {
            background: var(--primary-color);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--accent-yellow);
            box-shadow: 0 2px 8px rgba(5, 66, 28, 0.25);
        }

        .agent-brand {
            display: flex;
            align-items: center;
            min-width: 0;
            text-decoration: none !important;
        }

        .agent-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 10px;
            box-shadow: 0 0 0 2px var(--accent-yellow);
            overflow: hidden;
        }

        .agent-logo img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .agent-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .agent-offline-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(252, 238, 33, 0.55);
            color: #fff !important;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none !important;
        }

        .agent-offline-pill i { color: var(--accent-yellow); }
        .agent-offline-pill:active { background: rgba(255, 255, 255, 0.25); }

        .agent-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 0;
            background: transparent;
            color: #fff;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .agent-icon-btn:hover, .agent-icon-btn:focus { color: var(--accent-yellow); outline: none; }

        /* ---------- bottom navigation (selected = yellow, as the unit app) ---------- */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            min-height: var(--nav-height);
            display: flex;
            justify-content: space-around;
            align-items: center;
            gap: 6px;
            padding: 6px 8px;
            padding-bottom: max(6px, env(safe-area-inset-bottom));
            border-top: 2px solid var(--primary-color);
            box-shadow: 0 -2px 10px rgba(5, 66, 28, 0.08);
            z-index: 1000;
        }

        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            padding: 6px 0 5px;
            border-radius: 12px;
            text-decoration: none !important;
            color: #5f6f63;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
            position: relative;
            transition: background 0.2s ease;
        }

        .nav-item i {
            font-size: 20px;
            color: var(--primary-color);
        }

        .nav-item.active {
            background: var(--accent-yellow);
            color: var(--primary-color) !important;
            box-shadow: inset 0 0 0 1px #e2d514;
        }

        .nav-item:active { background: #fff8b0; }

        .main-content {
            padding: 14px;
        }

        /* ---------- cards & buttons ---------- */
        .app-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid #dfe8da;
        }

        .btn-app {
            background: var(--primary-color);
            color: #fff;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 700;
            border: none;
            width: 100%;
            box-shadow: 0 3px 10px rgba(5, 66, 28, 0.25);
            transition: all 0.2s ease;
        }

        .btn-app:hover, .btn-app:focus {
            background: #0b6b31;
            color: var(--accent-yellow);
        }

        .btn-app:active {
            transform: scale(0.98);
        }

        /* Specific Mobile Tweaks */
        @media (max-width: 576px) {
            .container {
                padding: 0;
            }
        }

        @media (max-width: 360px) {
            .agent-offline-pill span { display: none; }
        }
    </style>
    @stack('styles')
    {{-- brand theme (same colours as admin and the unit app); loaded last so it wins over page styles --}}
    <link rel="stylesheet" href="{{ asset('agent_assets/agent-theme.css') }}?v={{ @filemtime(public_path('agent_assets/agent-theme.css')) }}">
</head>

<body class="agent-app">

    @auth('sales_agent')
        <div class="agent-header">
            <a href="{{ route('agent.dashboard') }}" class="agent-brand">
                <span class="agent-logo"><img src="{{ admin_logo_url() }}" alt="SnapKid"></span>
                <h1 class="agent-title">{{ $title ?? 'SnapKid Agent' }}</h1>
            </a>
            <div class="header-actions">
                <a href="{{ route('agent.offline.index') }}" class="agent-offline-pill" title="Without Internet Mode">
                    <i class="fas fa-plane"></i> <span>Offline</span>
                </a>
                <form action="{{ route('agent.logout') }}" method="POST" id="logout-form" class="m-0">
                    @csrf
                    <button type="submit" class="agent-icon-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </div>
    @endauth

    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif
        @if(isset($errors) && $errors->any() && !request()->routeIs('agent.login'))
            <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>

    @auth('sales_agent')
        <div class="bottom-nav">
            <a href="{{ route('agent.dashboard') }}"
                class="nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            @if(!Auth::guard('sales_agent')->user()->is_master_agent)
            <a href="{{ route('agent.shops.index') }}"
                class="nav-item {{ request()->routeIs('agent.shops.*') ? 'active' : '' }}">
                <i class="fas fa-store"></i>
                <span>Shops</span>
            </a>
            @else
            <a href="{{ route('agent.customers.create') }}"
                class="nav-item {{ request()->routeIs('agent.customers.*') ? 'active' : '' }}">
                <i class="fas fa-user-plus"></i>
                <span>Add Customer</span>
            </a>
            @endif

            <a href="{{ route('agent.offline.index') }}"
                class="nav-item position-relative {{ request()->routeIs('agent.offline.*') ? 'active' : '' }}">
                <i class="fas fa-plane"></i>
                <span>Offline</span>
                <span id="navOfflineBadge" class="badge badge-danger rounded-pill position-absolute" style="top: 0; right: 18%; font-size: 9px; display: none;">0</span>
            </a>

            <a href="{{ route('agent.orders.index') }}"
                class="nav-item {{ request()->routeIs('agent.orders.index') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Orders</span>
            </a>
        </div>
    @endauth

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Prevent double form submission in Sales Agent portal
        $(function () {
            $(document).on('click', 'form button:not(.allow-multiple-submit), form input[type="submit"]:not(.allow-multiple-submit), form input[type="button"]:not(.allow-multiple-submit)', function (e) {
                var $btn = $(this);
                var $form = $btn.closest('form');

                if ($form.hasClass('allow-multiple-submit')) {
                    return;
                }

                if ($form.length && $form[0].checkValidity && !$form[0].checkValidity()) {
                    return;
                }

                if ($btn.data('clicked') === true) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }

                $btn.data('clicked', true);
                var originalPointerEvents = $btn.css('pointer-events');
                $btn.css('pointer-events', 'none');

                setTimeout(function() {
                    $btn.prop('disabled', true);
                }, 10);

                setTimeout(function() {
                    $btn.data('clicked', false);
                    $btn.prop('disabled', false);
                    $btn.css('pointer-events', originalPointerEvents || 'auto');
                }, 5000);
            });

            $(document).on('submit', 'form', function (e) {
                var $form = $(this);

                if ($form.hasClass('allow-multiple-submit')) {
                    return;
                }

                if (this.checkValidity && !this.checkValidity()) {
                    return;
                }

                if ($form.data('submitted') === true) {
                    e.preventDefault();
                    return false;
                }

                $form.data('submitted', true);
                var $buttons = $form.find('button, input[type="submit"], input[type="button"]');
                $buttons.css('pointer-events', 'none');

                setTimeout(function() {
                    if (e.isDefaultPrevented()) {
                        $form.data('submitted', false);
                        $buttons.css('pointer-events', 'auto');
                    } else {
                        $buttons.prop('disabled', true);
                        
                        setTimeout(function() {
                            $form.data('submitted', false);
                            $buttons.prop('disabled', false);
                            $buttons.css('pointer-events', 'auto');
                        }, 5000);
                    }
                }, 10);
            });
        });
    </script>
    
    <script src="{{ asset('assets/js/offline-db.js') }}?v={{ @filemtime(public_path('assets/js/offline-db.js')) }}"></script>
    <script>
        // Register Service Worker for offline capability
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('{{ asset("agent-sw.js") }}').catch(function(err) {
                    console.log('ServiceWorker note:', err);
                });
            });
        }

        // Price of one piece in the app's price style (same as sales_agent/partials/price.blade.php)
        window.skMoney = function (v) {
            v = Number(v) || 0;
            return '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: v % 1 ? 2 : 0, maximumFractionDigits: 2 });
        };
        window.skPriceHtml = function (price, mrp) {
            price = Number(price) || 0;
            mrp = Number(mrp) || 0;
            let html = '<span class="sk-price-now">' + skMoney(price) + '</span>';
            if (mrp > price) {
                const off = Math.round((mrp - price) / mrp * 100);
                html += '<span class="sk-price-mrp">' + skMoney(mrp) + '</span>';
                if (off > 0) html += '<span class="sk-price-off">' + off + '% OFF</span>';
            } else if (mrp > 0) {
                html += '<span class="sk-price-tag">MRP</span>';
            }
            return html;
        };

        // Global offline badge update
        $(function() {
            if (window.SnapKidOfflineDB) {
                SnapKidOfflineDB.getPendingOrders().then(function(orders) {
                    const pending = (orders || []).filter(o => o.status === 'pending_sync');
                    if (pending.length > 0) {
                        $('#navOfflineBadge').text(pending.length).show();
                    }
                }).catch(function() {});
            }
        });
    </script>
    
    @stack('scripts')
</body>

</html>