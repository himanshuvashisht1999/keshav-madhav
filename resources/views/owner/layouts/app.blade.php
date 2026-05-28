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
            --primary: #3eb651;
            --secondary: #ffe820;
            --primary-gradient: linear-gradient(135deg, #3eb651 0%, #2da340 100%);
            --accent-gradient: linear-gradient(135deg, #ffe820 0%, #e5d010 100%);
            --bg-app: #f4f6f9;
            --text-main: #0a4d1f;
            --text-muted: rgba(10, 77, 31, 0.7);
            --card-shadow: 0 8px 32px rgba(10, 77, 31, 0.07);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(62, 182, 81, 0.2);
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: var(--bg-app) !important;
            color: var(--text-main) !important;
            margin: 0 !important;
        }

        .text-muted { color: var(--text-muted) !important; }
        .text-dark { color: var(--text-main) !important; }
        .text-primary { color: var(--primary) !important; }
        .text-secondary { color: var(--secondary) !important; }

        .btn-primary { 
            background: var(--primary-gradient) !important; 
            border: none !important; 
            color: white !important; 
        }
        .btn-primary:hover { 
            background: var(--primary) !important; 
            box-shadow: 0 4px 12px rgba(62, 182, 81, 0.3) !important;
        }
        .bg-primary, .badge-primary { 
            background: var(--primary-gradient) !important; 
            color: white !important; 
            border: none !important;
        }

        /* Minimalistic App Header - replacing the large green block */
        .app-header {
            background: transparent !important;
            padding: 20px 20px 10px !important;
            border-radius: 0 !important;
            margin-bottom: 0 !important;
        }

        .app-header h1 {
            color: var(--text-main) !important;
            text-shadow: none !important;
            font-size: 24px !important;
            margin-bottom: 0 !important;
            line-height: 1.2 !important;
            font-weight: 800 !important;
        }

        .app-header p {
            font-size: 13px !important;
            color: var(--text-muted) !important;
            margin-bottom: 0 !important;
            margin-top: 4px !important;
        }

        .app-header .mb-3, .app-header .mb-4 {
            margin-bottom: 10px !important;
        }

        .app-header a.text-white {
            color: var(--text-main) !important;
        }

        /* Fix breadcrumbs inside app-header */
        .breadcrumb-custom {
            margin-bottom: 8px !important;
        }
        .breadcrumb-custom a, .breadcrumb-custom span, .breadcrumb-custom i {
            color: var(--text-muted) !important;
        }

        /* Reset overlapping elements that used negative margin */
        .search-panel, .search-container, .summary-card-container, .roll-info-floating {
            margin-top: 5px !important;
        }

        .fab, .fab-add {
            background: var(--accent-gradient) !important;
            color: var(--text-main) !important;
            box-shadow: 0 8px 24px rgba(255, 232, 32, 0.3) !important;
        }

        .mobile-only {
            display: none !important;
        }

        .desktop-only {
            display: block !important;
        }
        
        .dashboard-mobile-view {
            display: none !important;
        }

        /* Classes for mobile app views that we WANT to show on desktop */
        .responsive-app-view {
            display: block !important;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
            min-height: 100vh;
            background: transparent;
        }

        @media (max-width: 991.98px) {
            .mobile-only {
                display: block !important;
            }
            .desktop-only {
                display: none !important;
            }
            .dashboard-mobile-view {
                display: block !important;
            }
            .responsive-app-view {
                padding: 0;
                max-width: 100%;
                background: #f8fafc;
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
            font-weight: 800 !important;
            color: var(--secondary) !important;
            letter-spacing: -0.5px !important;
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
            color: var(--text-muted) !important;
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
            color: var(--secondary) !important;
            background: var(--text-main) !important;
            border-radius: 16px !important;
            margin: 8px 4px !important;
            box-shadow: 0 4px 12px rgba(10, 77, 31, 0.2) !important;
        }

        .nav-link-mobile.active i {
            margin-bottom: 2px !important;
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
                <a href="{{ route('owner.logout') }}" style="color: var(--secondary); font-size: 20px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
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
            <a href="{{ route('owner.party-ledger.index') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.party-ledger.*') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i>
                <span>Payment</span>
            </a>
        </nav>

    </div>

    @include('admin.common.footer-js')
    @yield('scripts')
    @stack('scripts')
</body>

</html>
