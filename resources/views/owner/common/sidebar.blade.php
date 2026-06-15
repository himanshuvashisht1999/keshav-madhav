<?php
$page_url = $_SERVER['REQUEST_URI'];
$general_setting = App\Models\GeneralSettings::where('status', 1)->first();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('owner.dashboard') }}" class="brand-link">
        <img src="{{ $general_setting->logo }}" alt="Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">Owner Panel</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('owner.dashboard') }}"
                        class="nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('owner.party-ledger.index') }}"
                        class="nav-link {{ request()->routeIs('owner.party-ledger.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book-open"></i>
                        <p>Party Ledger</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('owner.bank-cash-ledger.index') }}"
                        class="nav-link {{ request()->routeIs('owner.bank-cash-ledger.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-university"></i>
                        <p>Bank & Cash Ledger</p>
                    </a>
                </li>
                <li class="nav-header">REPORTS</li>
                <li class="nav-item">
                    <a href="{{ route('owner.stock') }}"
                        class="nav-link {{ request()->routeIs('owner.stock') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Fabric Stock</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('owner.order-summary.index') }}"
                        class="nav-link {{ request()->routeIs('owner.order-summary.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>Order Summary</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('owner.lots') }}"
                        class="nav-link {{ request()->routeIs('owner.lots') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Lot Details</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('owner.ready-stock.index') }}"
                        class="nav-link {{ request()->routeIs('owner.ready-stock.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>Ready Stock</p>
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a href="{{ route('owner.logout') }}" class="nav-link text-danger">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>