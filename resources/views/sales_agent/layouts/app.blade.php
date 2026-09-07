<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Sales Agent Portal' }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <style>
        :root {
            --primary-color: #26A744;
            --primary-light: #32c454;
            --primary-dark: #1a8733;
            --accent-yellow: #ffe600;
            --accent-yellow-light: #fef08a;
            --secondary-color: #84d232;
            --bg-color: #f8fafc;
            --card-shadow: 0 4px 12px -2px rgba(38, 167, 68, 0.1), 0 2px 6px -2px rgba(0, 0, 0, 0.05);
            --header-height: 62px;
            --nav-height: 65px;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: #1e293b;
            padding-bottom: var(--nav-height);
            /* Nav bar space */
            -webkit-tap-highlight-color: transparent;
        }

        .agent-header {
            background: #fff;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border-top: 4px solid var(--accent-yellow);
        }

        .agent-header h1 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            height: var(--nav-height);
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 -3px 12px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            border-top: 1px solid #f1f5f9;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.25s ease;
            width: 20%;
            position: relative;
        }

        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
            transition: transform 0.2s ease;
        }

        .nav-item.active {
            color: var(--primary-color) !important;
            font-weight: 700;
        }

        .nav-item.active i {
            transform: translateY(-2px);
            color: var(--primary-color);
        }

        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -6px;
            width: 20px;
            height: 3.5px;
            background: var(--accent-yellow);
            border-radius: 3px;
        }

        .main-content {
            padding: 18px;
        }

        .app-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(226, 232, 240, 0.7);
        }

        .btn-app {
            background: linear-gradient(135deg, #26A744 0%, #1a8733 100%);
            color: #fff;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 700;
            border: none;
            width: 100%;
            box-shadow: 0 4px 12px rgba(38, 167, 68, 0.25);
            transition: all 0.25s ease;
        }

        .btn-app:hover, .btn-app:focus {
            background: linear-gradient(135deg, #1a8733 0%, #136626 100%);
            color: #fff;
            box-shadow: 0 6px 16px rgba(38, 167, 68, 0.35);
        }

        .btn-app:active {
            transform: scale(0.98);
        }

        /* SnapKid Global Bootstrap Overrides */
        .btn-primary {
            background: linear-gradient(135deg, #26A744 0%, #1a8733 100%) !important;
            border-color: #1a8733 !important;
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(38, 167, 68, 0.25);
        }

        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, #1a8733 0%, #136626 100%) !important;
            border-color: #136626 !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(38, 167, 68, 0.35);
        }

        .text-primary {
            color: #26A744 !important;
        }

        .bg-primary {
            background: linear-gradient(135deg, #26A744 0%, #1a8733 100%) !important;
            color: #fff !important;
        }

        .badge-primary {
            background-color: #fef08a !important;
            color: #854d0e !important;
            border: 1px solid #fde047;
            font-weight: 700;
        }

        .border-primary {
            border-color: #26A744 !important;
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #26A744 !important;
            border-color: #26A744 !important;
        }

        .form-control:focus {
            border-color: #26A744 !important;
            box-shadow: 0 0 0 0.2rem rgba(38, 167, 68, 0.2) !important;
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: #26A744 !important;
            color: #fff !important;
        }

        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #26A744 !important;
            box-shadow: 0 0 0 0.2rem rgba(38, 167, 68, 0.2) !important;
        }

        /* Specific Mobile Tweaks */
        @media (max-width: 576px) {
            .container {
                padding: 0;
            }
        }
    </style>
    @stack('styles')
</head>

<body>

    @auth('sales_agent')
        <div class="agent-header">
            <h1 class="d-flex align-items-center">
                <img src="{{ asset('images/snapkid_logo.png') }}" alt="SnapKid" style="height: 36px; width: auto; object-fit: contain;" class="mr-2">
                <span>{{ $title ?? 'SnapKid Agent' }}</span>
            </h1>
            <div class="header-actions">
                <form action="{{ route('agent.logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
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
    
    @stack('scripts')
</body>

</html>