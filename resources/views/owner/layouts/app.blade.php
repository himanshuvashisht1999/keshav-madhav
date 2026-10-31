<!DOCTYPE html>
<html lang="en">
@php
    $general_setting = App\Models\GeneralSettings::where('status', 1)->first();
@endphp

<head>
    @include('owner.common.meta')
    <title>@yield('title', 'Owner') | {{ $general_setting->title ?? 'Snapkid' }}</title>
    <meta name="theme-color" content="#05421c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="stylesheet" href="{{ asset('admin_assets/owner.css') }}?v={{ @filemtime(public_path('admin_assets/owner.css')) }}">
    @yield('styles')
</head>

<body class="owner-app">
    <header class="ow-topbar">
        <a href="{{ route('owner.dashboard') }}" class="ow-brand">
            @if(!empty($general_setting->logo))
                <img src="{{ $general_setting->logo }}" alt="">
            @endif
            <span>Owner</span>
        </a>
        <div class="ow-title">@yield('title', 'Owner')</div>
        <a href="{{ route('owner.logout') }}" class="ow-icon-btn" title="Log out" onclick="return confirm('Log out?')"><i class="fas fa-sign-out-alt"></i></a>
    </header>

    <main class="ow-content">
        @if(session('success'))
            <div class="ow-alert ok"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="ow-alert err"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <nav class="ow-bottomnav">
        <a href="{{ route('owner.dashboard') }}" class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i><span>Home</span>
        </a>
        <a href="{{ route('owner.order-summary.index') }}" class="{{ request()->routeIs('owner.order-summary.*', 'owner.orders', 'owner.wip-complete') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i><span>Orders</span>
        </a>
        <a href="{{ route('owner.lots') }}" class="{{ request()->routeIs('owner.lots', 'owner.lot-details', 'owner.reports.unit-assignments') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i><span>Lots</span>
        </a>
        <a href="{{ route('owner.stock') }}" class="{{ request()->routeIs('owner.stock', 'owner.stock.*', 'owner.report.stock.*', 'owner.ready-stock.*') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i><span>Stock</span>
        </a>
        <a href="{{ route('owner.party-ledger.index') }}" class="{{ request()->routeIs('owner.party-ledger.*', 'owner.bank-cash-ledger.*', 'owner.payment.*', 'owner.payment-adjustment.*', 'owner.reports.selling-items', 'owner.reports.delayed-payments') ? 'active' : '' }}">
            <i class="fas fa-book-open"></i><span>Ledger</span>
        </a>
    </nav>

    @include('owner.common.footer-js')
    @yield('scripts')
    @stack('scripts')
</body>

</html>
