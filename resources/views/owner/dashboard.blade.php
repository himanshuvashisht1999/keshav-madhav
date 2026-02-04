@extends('owner.layouts.app')

@section('content')
    <style>
        /* =========================================
                       MOBILE APP STYLES (Screen < 992px)
                    ========================================= */
        @media (max-width: 991.98px) {
            .app-hero {
                background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
                padding: 35px 20px 60px;
                color: white;
                position: relative;
                box-shadow: 0 4px 20px rgba(111, 66, 193, 0.3);
            }

            .app-hero-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                opacity: 0.85;
                margin-bottom: 8px;
                font-weight: 700;
            }

            .app-hero-title {
                font-size: 32px;
                font-weight: 900;
                margin-bottom: 8px;
                line-height: 1.1;
            }

            .app-hero-subtitle {
                opacity: 0.9;
                font-size: 14px;
                font-weight: 500;
            }

            .app-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
                margin-top: -35px;
                padding: 0 20px;
            }

            .app-stat-card {
                background: white;
                padding: 20px 12px;
                border-radius: 16px;
                text-align: center;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                text-decoration: none;
                border: 1px solid #f1f5f9;
            }

            .app-stat-value {
                font-size: 22px;
                font-weight: 900;
                color: var(--primary);
                display: block;
                margin-bottom: 4px;
            }

            .app-stat-label {
                font-size: 10px;
                color: #64748b;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .app-menu-section {
                padding: 30px 20px;
            }

            .section-title {
                font-size: 18px;
                font-weight: 900;
                color: #1e293b;
                margin-bottom: 20px;
                letter-spacing: -0.5px;
            }

            .app-report-card {
                background: white;
                border-radius: 16px;
                padding: 18px;
                display: flex;
                align-items: center;
                gap: 16px;
                text-decoration: none;
                color: var(--text-main);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
                margin-bottom: 15px;
                border: 1px solid #f1f5f9;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .app-report-card:active {
                transform: scale(0.98);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .app-report-icon {
                width: 56px;
                height: 56px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 22px;
                flex-shrink: 0;
            }

            .app-report-title {
                font-size: 16px;
                font-weight: 800;
                margin: 0;
                color: #1e293b;
            }

            .app-report-desc {
                font-size: 12px;
                color: #64748b;
                margin: 2px 0 0 0;
                font-weight: 500;
            }

            .app-report-arrow {
                margin-left: auto;
                color: #cbd5e1;
                font-size: 14px;
            }
        }

        /* =========================================
                       DESKTOP ADMIN STYLES (Screen >= 992px)
                    ========================================= */
        @media (min-width: 992px) {
            .desktop-header {
                padding: 20px;
                background: white;
                border-bottom: 1px solid #e5e7eb;
                margin-bottom: 25px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .desktop-stats {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                padding: 0 20px;
            }

            .info-box {
                box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
                border-radius: .25rem;
                background-color: #fff;
                display: flex;
                margin-bottom: 1rem;
                min-height: 80px;
                padding: .5rem;
                position: relative;
                width: 100%;
            }

            .info-box-icon {
                border-radius: .25rem;
                align-items: center;
                display: flex;
                font-size: 1.875rem;
                justify-content: center;
                text-align: center;
                width: 70px;
            }

            .info-box-content {
                display: flex;
                flex-direction: column;
                justify-content: center;
                line-height: 1.2;
                flex: 1;
                padding: 0 10px;
            }

            .info-box-text {
                text-transform: uppercase;
                font-size: 14px;
                font-weight: 600;
                color: #666;
            }

            .info-box-number {
                display: block;
                font-weight: 700;
                font-size: 22px;
            }
        }
    </style>

    <!-- ================= MOBILE APP CONTENT ================= -->
    <div class="mobile-only">
        <div class="app-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="app-hero-label">OWNER DASHBOARD</span>
                <span style="font-size: 11px; opacity: 0.8; font-weight: 600;">{{ now()->format('D, d M') }}</span>
            </div>
            <h1 class="app-hero-title">Welcome Back</h1>
            <p class="app-hero-subtitle">Here's your business at a glance</p>
        </div>

        <div class="app-stats-grid">
            <div class="app-stat-card">
                <span class="app-stat-value">{{ $total_orders }}</span>
                <span class="app-stat-label">Orders</span>
            </div>
            <div class="app-stat-card">
                <span class="app-stat-value">{{ number_format($total_stock, 0) }}</span>
                <span class="app-stat-label">Stock</span>
            </div>
            <div class="app-stat-card">
                <span class="app-stat-value">{{ $total_lots }}</span>
                <span class="app-stat-label">Lots</span>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="app-menu-section">
            <h2 class="section-title">Main Modules</h2>

            <a href="{{ route('owner.order-summary.index') }}" class="app-report-card">
                <div class="app-report-icon" style="background: #eff6ff; color: #1e3a8a;">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="app-report-title">Order Summary</h3>
                    <p class="app-report-desc">Deep tracking & manifest</p>
                </div>
                <i class="fas fa-chevron-right app-report-arrow"></i>
            </a>

            <a href="{{ route('owner.stock') }}" class="app-report-card">
                <div class="app-report-icon" style="background: #f0fdf4; color: #166534;">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="app-report-title">Fabric Stock</h3>
                    <p class="app-report-desc">Unique & roll details</p>
                </div>
                <i class="fas fa-chevron-right app-report-arrow"></i>
            </a>

            <a href="{{ route('owner.lots') }}" class="app-report-card">
                <div class="app-report-icon" style="background: #fff7ed; color: #9a3412;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="app-report-title">Production Lots</h3>
                    <p class="app-report-desc">Batch tracking status</p>
                </div>
                <i class="fas fa-chevron-right app-report-arrow"></i>
            </a>
        </div>
    </div>

    <!-- ================= DESKTOP ADMIN CONTENT ================= -->
    <div class="desktop-only">
        <div class="desktop-header">
            <h2 style="font-weight: 700; margin: 0;">Owner Dashboard</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="background:transparent; margin:0; padding:0;">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>

        <div class="desktop-stats">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Orders</span>
                    <span class="info-box-number">{{ $total_orders }}</span>
                </div>
            </div>
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Fabric Stock</span>
                    <span class="info-box-number">{{ number_format($total_stock, 2) }} Mtr</span>
                </div>
            </div>
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Lots</span>
                    <span class="info-box-number">{{ $total_lots }}</span>
                </div>
            </div>
        </div>

        <div class="row px-3 mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Business Quick Links</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('owner.stock') }}" class="btn btn-block btn-outline-primary p-4">
                                    <i class="fas fa-warehouse mb-2 d-block fa-2x"></i>
                                    View Fabric Stock
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('owner.orders') }}" class="btn btn-block btn-outline-info p-4">
                                    <i class="fas fa-shopping-bag mb-2 d-block fa-2x"></i>
                                    View Sales Orders
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('owner.lots') }}" class="btn btn-block btn-outline-success p-4">
                                    <i class="fas fa-layer-group mb-2 d-block fa-2x"></i>
                                    View Production Lots
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection