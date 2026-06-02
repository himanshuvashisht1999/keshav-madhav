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
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --bg-color: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --header-height: 60px;
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
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        }

        .agent-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary-color);
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
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
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
            transition: 0.3s;
            width: 20%;
        }

        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .nav-item.active {
            color: var(--primary-color);
        }

        .main-content {
            padding: 20px;
        }

        .app-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: none;
        }

        .btn-app {
            background: var(--primary-color);
            color: #fff;
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            border: none;
            width: 100%;
            transition: 0.3s;
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
    </style>
    @stack('styles')
</head>

<body>

    @auth('sales_agent')
        <div class="agent-header">
            <h1>{{ $title ?? 'SnapKid Sales' }}</h1>
            <div class="header-actions">
                <form action="{{ route('agent.logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted"><i class="fas fa-sign-out-alt"></i></button>
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
            $(document).on('submit', 'form', function (e) {
                var $form = $(this);

                if ($form.hasClass('allow-multiple-submit')) {
                    return;
                }

                if ($form.data('submitted') === true) {
                    e.preventDefault();
                    return false;
                }

                $form.data('submitted', true);
                $form.find('button[type="submit"], input[type="submit"]').prop('disabled', true);
            });
        });
    </script>
    
    @stack('scripts')
</body>

</html>