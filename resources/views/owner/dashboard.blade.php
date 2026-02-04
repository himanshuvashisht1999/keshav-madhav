@extends('owner.layouts.app')

@section('content')
<style>
    /* =========================================
       MOBILE APP STYLES (Screen < 992px)
    ========================================= */
    @media (max-width: 991.98px) {
        .app-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 50px 20px 80px;
            color: white;
            position: relative;
        }
        .app-hero-label { font-size: 14px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; margin-bottom: 8px; font-weight: 700; }
        .app-hero-title { font-size: 32px; font-weight: 800; margin-bottom: 5px; }
        
        .app-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: -40px;
            padding: 0 20px;
        }
        .app-stat-card { background: white; padding: 18px 10px; border-radius: 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-decoration: none; }
        .app-stat-value { font-size: 18px; font-weight: 800; color: #1e3a8a; display: block; margin-bottom: 4px; }
        .app-stat-label { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; }

        .app-menu-section { padding: 30px 20px; }
        .app-report-card {
            background: white; border-radius: 24px; padding: 20px; display: flex; align-items: center; gap: 18px;
            text-decoration: none; color: #1f2937; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 15px;
        }
        .app-report-icon { width: 52px; height: 52px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    }

    /* =========================================
       DESKTOP ADMIN STYLES (Screen >= 992px)
    ========================================= */
    @media (min-width: 992px) {
        .desktop-header { padding: 20px; background: white; border-bottom: 1px solid #e5e7eb; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .desktop-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 0 20px; }
        .info-box { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); border-radius: .25rem; background-color: #fff; display: flex; margin-bottom: 1rem; min-height: 80px; padding: .5rem; position: relative; width: 100%; }
        .info-box-icon { border-radius: .25rem; align-items: center; display: flex; font-size: 1.875rem; justify-content: center; text-align: center; width: 70px; }
        .info-box-content { display: flex; flex-direction: column; justify-content: center; line-height: 1.2; flex: 1; padding: 0 10px; }
        .info-box-text { text-transform: uppercase; font-size: 14px; font-weight: 600; color: #666; }
        .info-box-number { display: block; font-weight: 700; font-size: 22px; }
    }
</style>

<!-- ================= MOBILE APP CONTENT ================= -->
<div class="mobile-only">
    <div class="app-hero">
        <div class="app-hero-label">Owner Portal</div>
        <h1 class="app-hero-title">Hello,</h1>
        <div style="font-size: 20px; font-weight: 600; opacity: 0.95;">Business Overview</div>
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

    <div class="app-menu-section">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 20px;">Insights & Reports</h2>
        <a href="{{ route('owner.stock') }}" class="app-report-card">
            <div class="app-report-icon" style="background: #eff6ff; color: #3b82f6;"><i class="fas fa-warehouse"></i></div>
            <div><h3>Fabric Stock</h3><p>Real-time inventory levels</p></div>
        </a>
        <a href="{{ route('owner.orders') }}" class="app-report-card">
            <div class="app-report-icon" style="background: #fff7ed; color: #f59e0b;"><i class="fas fa-file-invoice"></i></div>
            <div><h3>Order Details</h3><p>Sales tracking</p></div>
        </a>
        <a href="{{ route('owner.lots') }}" class="app-report-card">
            <div class="app-report-icon" style="background: #f0fdf4; color: #10b981;"><i class="fas fa-tags"></i></div>
            <div><h3>Lot Reports</h3><p>Batch history</p></div>
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