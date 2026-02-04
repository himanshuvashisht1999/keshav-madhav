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

    <style>
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

            /* Reset AdminLTE force styles for mobile app feel */
            body.sidebar-mini.sidebar-collapse .content-wrapper,
            body.sidebar-mini .content-wrapper {
                margin-left: 0 !important;
                padding-bottom: 70px;
                /* Space for bottom nav */
            }

            .main-header {
                display: none !important;
            }

            .main-sidebar {
                display: none !important;
            }

            .content-wrapper {
                background: #f5f7fa !important;
            }
        }

        /* Bottom Nav Styling (Mobile Only) */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0 calc(10px + env(safe-area-inset-bottom));
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .nav-link-mobile {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #9ca3af;
            font-size: 11px;
            font-weight: 600;
        }

        .nav-link-mobile i {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .nav-link-mobile.active {
            color: #1e3a8a;
        }
    </style>
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Desktop Header & Sidebar -->
        <div class="desktop-only">
            @include('owner.common.header')
            @include('owner.common.sidebar')
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            @yield('content')
        </div>

        <!-- Mobile Bottom Nav -->
        <nav class="bottom-nav mobile-only">
            <a href="{{ route('owner.dashboard') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('owner.orders') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.orders') ? 'active' : '' }}">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
            </a>
            <a href="{{ route('owner.stock') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.stock') ? 'active' : '' }}">
                <i class="fas fa-database"></i>
                <span>Stock</span>
            </a>
            <a href="{{ route('owner.lots') }}"
                class="nav-link-mobile {{ request()->routeIs('owner.lots') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>Lots</span>
            </a>
            <a href="{{ route('owner.logout') }}" class="nav-link-mobile text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>

        <div class="desktop-only">
            @include('admin.common.footer')
        </div>
    </div>

    @include('admin.common.footer-js')
    @yield('scripts')
    @stack('scripts')
</body>

</html>