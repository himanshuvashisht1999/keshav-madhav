<!DOCTYPE html>
<html lang="en">
@php
    $general_setting = App\Models\GeneralSettings::where('status', 1)->first();
@endphp

<head>
    @include('admin.common.meta')
    <title>Owner Portal | {{ $general_setting->title ?? 'Keshav Madhav' }}</title>

    <!-- Mobile App Meta -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #6f42c1;
            --secondary: #8e67d5;
            --bg-app: #f4f6f9;
            --text-main: #2d3436;
            --text-muted: #636e72;
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: var(--bg-app) !important;
            margin: 0 !important;
        }

        /* Responsive Visibility */
        .mobile-only {
            display: none !important;
        }

        .desktop-only {
            display: block !important;
        }

        @media (max-width: 991.98px) {
            .mobile-only {
                display: block !important;
            }

            .desktop-only {
                display: none !important;
            }
        }

        /* Universal UI Layout (Laptop + Mobile) - NO SIDEBAR */
        body.sidebar-mini.sidebar-collapse .content-wrapper,
        body.sidebar-mini .content-wrapper,
        .content-wrapper {
            margin-left: 0 !important;
            padding-top: 60px !important;
            padding-bottom: 80px !important;
            background: var(--bg-app) !important;
            min-height: 100vh !important;
        }

        .main-header,
        .main-sidebar,
        .main-footer {
            display: none !important;
        }

        .wrapper {
            height: auto !important;
            min-height: 100vh !important;
        }

        /* Mobile Top Bar - FORCE ROW */
        .mobile-top-bar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 60px !important;
            background: var(--primary) !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 15px !important;
            z-index: 9999 !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
        }

        .mobile-app-logo {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 12px !important;
            font-size: 19px !important;
            font-weight: 700 !important;
        }

        /* Bottom Nav - FORCE ROW */
        .bottom-nav {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: 75px !important;
            background: #ffffff !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            justify-content: space-around !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: 0 -2px 15px rgba(0, 0, 0, 0.1) !important;
            z-index: 9999 !important;
            border-top: 1px solid #edf2f7 !important;
        }

        .nav-link-mobile {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            color: #94a3b8 !important;
            flex: 1 !important;
            height: 100% !important;
            margin: 0 !important;
        }

        .nav-link-mobile i {
            font-size: 22px !important;
            margin-bottom: 4px !important;
        }

        .nav-link-mobile span {
            font-size: 11px !important;
            font-weight: 600 !important;
            display: block !important;
        }

        .nav-link-mobile.active {
            color: var(--primary) !important;
        }

        /* Content spacing */
        .content-wrapper {
            border: none !important;
        }
    </style>
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- App Top Bar -->
        <div class="mobile-top-bar">
            <div class="mobile-app-logo">
                <i class="fas fa-list-ul"></i>
                <span>Owner Portal</span>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('owner.logout') }}" style="color: white; font-size: 20px;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>

        <!-- App Bottom Nav -->
        <nav class="bottom-nav">
            <a href="{{ route('owner.dashboard') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('owner.order-summary.index') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.order-summary.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Summary</span>
            </a>
            <a href="{{ route('owner.stock') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.stock') ? 'active' : '' }}">
                <i class="fas fa-warehouse"></i>
                <span>Stock</span>
            </a>
            <a href="{{ route('owner.lots') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.lots') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>Lots</span>
            </a>
        </nav>

    </div>

    @include('admin.common.footer-js')
    @yield('scripts')
    @stack('scripts')
</body>

</html>