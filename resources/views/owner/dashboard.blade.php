@extends('owner.layouts.app')

@section('styles')
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --KM-purple: #6f42c1;
            --KM-purple-dark: #5a32a3;
            --KM-purple-light: #8b5cf6;
        }

        body {
            background-color: #f8fafc;
            color: var(--slate-800);
        }

        /* =========================================
                               MOBILE APP STYLES (Screen < 992px)
                            ========================================= */
        @media (max-width: 991.98px) {
            .app-hero {
                background: var(--KM-purple);
                padding: 40px 24px 80px;
                color: white;
                position: relative;
                overflow: hidden;
            }

            .app-hero::after {
                content: '';
                position: absolute;
                top: -50px;
                right: -50px;
                width: 150px;
                height: 150px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            .app-hero-label {
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 2px;
                font-weight: 800;
                opacity: 0.8;
                display: block;
                margin-bottom: 4px;
            }

            .app-hero-title {
                font-size: 28px;
                font-weight: 900;
                margin-bottom: 6px;
                letter-spacing: -1px;
            }

            .app-hero-subtitle {
                font-size: 14px;
                font-weight: 500;
                opacity: 0.9;
            }

            .app-stats-container {
                padding: 0 20px;
                margin-top: -45px;
                position: relative;
                z-index: 10;
            }

            .app-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }

            .app-stat-card {
                background: white;
                border-radius: 16px;
                padding: 16px 10px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                border: 1px solid var(--slate-100);
                text-decoration: none;
                transition: transform 0.2s ease;
            }

            .app-stat-card:active {
                transform: scale(0.95);
            }

            .app-stat-value {
                display: block;
                font-size: 20px;
                font-weight: 900;
                color: var(--slate-900);
                margin-bottom: 2px;
            }

            .app-stat-label {
                display: block;
                font-size: 9px;
                font-weight: 800;
                color: var(--slate-500);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .app-menu-section {
                padding: 35px 24px;
            }

            .section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }

            .section-title {
                font-size: 18px;
                font-weight: 900;
                color: var(--slate-900);
                margin: 0;
                letter-spacing: -0.5px;
            }

            .app-nav-card {
                background: white;
                border-radius: 18px;
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 18px;
                margin-bottom: 16px;
                text-decoration: none;
                border: 1px solid var(--slate-100);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            }

            .app-nav-icon {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                flex-shrink: 0;
            }

            .app-nav-content {
                flex-grow: 1;
            }

            .app-nav-title {
                display: block;
                font-size: 16px;
                font-weight: 800;
                color: var(--slate-900);
                margin-bottom: 2px;
            }

            .app-nav-desc {
                display: block;
                font-size: 12px;
                color: var(--slate-500);
                font-weight: 500;
            }

            .app-nav-arrow {
                color: var(--slate-300);
                font-size: 14px;
            }
        }

        /* =========================================
                               DESKTOP ADMIN STYLES (Screen >= 992px)
                            ========================================= */
        @media (min-width: 992px) {
            .desktop-wrapper {
                padding: 40px;
                max-width: 1400px;
                margin: 0 auto;
            }

            .desktop-welcome {
                margin-bottom: 40px;
            }

            .welcome-title {
                font-size: 32px;
                font-weight: 900;
                color: var(--slate-900);
                letter-spacing: -1px;
                margin-bottom: 8px;
            }

            .welcome-meta {
                color: var(--slate-500);
                font-weight: 600;
                font-size: 14px;
            }

            .dashboard-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
                margin-bottom: 40px;
            }

            .stat-card-desktop {
                background: white;
                border-radius: 20px;
                padding: 30px;
                border: 1px solid var(--slate-200);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .stat-icon-desktop {
                width: 64px;
                height: 64px;
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
            }

            .stat-info-desktop .stat-label {
                display: block;
                font-size: 13px;
                font-weight: 800;
                color: var(--slate-500);
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 4px;
            }

            .stat-info-desktop .stat-value {
                display: block;
                font-size: 28px;
                font-weight: 900;
                color: var(--slate-900);
            }

            .quick-actions-card {
                background: white;
                border-radius: 24px;
                border: 1px solid var(--slate-200);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
                overflow: hidden;
            }

            .card-header-new {
                padding: 30px 40px;
                border-bottom: 1px solid var(--slate-100);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .card-header-new h3 {
                margin: 0;
                font-size: 20px;
                font-weight: 900;
                color: var(--slate-900);
            }

            .card-body-new {
                padding: 40px;
            }

            .action-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }

            .action-btn-modern {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                border: 2px solid var(--slate-100);
                border-radius: 20px;
                text-decoration: none;
                transition: all 0.3s ease;
                background: var(--slate-50);
            }

            .action-btn-modern:hover {
                border-color: var(--KM-purple);
                background: white;
                transform: translateY(-5px);
                box-shadow: 0 15px 30px rgba(111, 66, 193, 0.1);
            }

            .action-btn-modern i {
                font-size: 32px;
                margin-bottom: 16px;
                color: var(--KM-purple);
            }

            .action-btn-modern span {
                font-weight: 800;
                color: var(--slate-800);
                font-size: 15px;
            }
        }
    </style>
@endsection

@section('content')
    <!-- ================= MOBILE APP CONTENT ================= -->
    <div class="mobile-only">
        <div class="app-hero">
            <span class="app-hero-label">Owner Portal Dashboard</span>
            <h1 class="app-hero-title">Welcome Back</h1>
            <p class="app-hero-subtitle">Wednesday, 04 Feb 2026</p>
        </div>


        <div class="app-menu-section">
            <div class="section-header">
                <h2 class="section-title">Business Modules</h2>
            </div>

            <a href="{{ route('owner.order-summary.index') }}" class="app-nav-card">
                <div class="app-nav-icon" style="background: rgba(111, 66, 193, 0.1); color: var(--KM-purple);">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="app-nav-content">
                    <span class="app-nav-title">Order Summary</span>
                    <span class="app-nav-desc">Track manifests & production</span>
                </div>
                <i class="fas fa-chevron-right app-nav-arrow"></i>
            </a>

            <a href="{{ route('owner.stock') }}" class="app-nav-card">
                <div class="app-nav-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="app-nav-content">
                    <span class="app-nav-title">Fabric Stock</span>
                    <span class="app-nav-desc">Manage rolls & quantities</span>
                </div>
                <i class="fas fa-chevron-right app-nav-arrow"></i>
            </a>

            <a href="{{ route('owner.report.stock.rolls') }}" class="app-nav-card">
                <div class="app-nav-icon" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                    <i class="fas fa-barcode"></i>
                </div>
                <div class="app-nav-content">
                    <span class="app-nav-title">Stock by Roll</span>
                    <span class="app-nav-desc">Search and track individual rolls</span>
                </div>
                <i class="fas fa-chevron-right app-nav-arrow"></i>
            </a>

            <a href="{{ route('owner.lots') }}" class="app-nav-card">
                <div class="app-nav-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="app-nav-content">
                    <span class="app-nav-title">Production Lots</span>
                    <span class="app-nav-desc">Batch and lot tracking</span>
                </div>
                <i class="fas fa-chevron-right app-nav-arrow"></i>
            </a>
            <a href="{{ route('owner.ready-stock.index') }}" class="app-nav-card">
                <div class="app-nav-icon" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9;">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="app-nav-content">
                    <span class="app-nav-title">Ready Stock</span>
                    <span class="app-nav-desc">Domestic finished goods</span>
                </div>
                <i class="fas fa-chevron-right app-nav-arrow"></i>
            </a>

            <a href="{{ route('owner.payment-adjustment.index') }}" class="app-nav-card">
                <div class="app-nav-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="app-nav-content">
                    <span class="app-nav-title">Payment Adjustments</span>
                    <span class="app-nav-desc">Record & view adjustments</span>
                </div>
                <i class="fas fa-chevron-right app-nav-arrow"></i>
            </a>
        </div>
    </div>

    <!-- ================= DESKTOP CONTENT ================= -->
    <div class="desktop-only desktop-wrapper">
        <div class="desktop-welcome">
            <h1 class="welcome-title">Owner Dashboard</h1>
            <div class="welcome-meta">
                <i class="far fa-calendar-alt mr-2"></i> {{ now()->format('l, d F Y') }}
                <span class="mx-3">|</span>
                <i class="far fa-clock mr-2"></i> {{ now()->format('h:i A') }}
            </div>
        </div>


        <div class="quick-actions-card">
            <div class="card-header-new">
                <h3>Business Quick Links</h3>
                <span class="badge badge-pill badge-light px-3 py-2 font-weight-bold text-muted">Owner Portal v2.0</span>
            </div>
            <div class="card-body-new">
                <div class="action-grid">
                    <a href="{{ route('owner.order-summary.index') }}" class="action-btn-modern">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Order Summary</span>
                    </a>
                    <a href="{{ route('owner.stock') }}" class="action-btn-modern">
                        <i class="fas fa-warehouse"></i>
                        <span>Fabric Stock</span>
                    </a>
                    <a href="{{ route('owner.report.stock.rolls') }}" class="action-btn-modern">
                        <i class="fas fa-barcode text-primary"></i>
                        <span>Stock by Roll</span>
                    </a>
                    <a href="{{ route('owner.lots') }}" class="action-btn-modern">
                        <i class="fas fa-industry"></i>
                        <span>Production Lots</span>
                    </a>
                    <a href="{{ route('owner.ready-stock.index') }}" class="action-btn-modern">
                        <i class="fas fa-boxes text-info"></i>
                        <span>Ready Stock</span>
                    </a>
                    <a href="{{ route('owner.payment-adjustment.index') }}" class="action-btn-modern">
                        <i class="fas fa-balance-scale text-danger"></i>
                        <span>Adjustments</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection