@extends('layouts.unit')

@section('title', 'Dashboard')
{{-- We hide the app-header because dashboard has its own hero section --}}
@push('styles')
    <style>
        .app-header {
            display: none !important;
        }

        body {
            padding-top: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: -45px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            background: white;
            padding: 16px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }

        .stat-card:active {
            transform: scale(0.95);
        }

        .stat-icon {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }

        .stat-label {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .menu-item {
            background: white;
            padding: 24px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            text-decoration: none;
            color: #1f2937;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .menu-item:active {
            transform: scale(0.98);
            background: #f9fafb;
        }

        .menu-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .menu-details h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .menu-details p {
            font-size: 14px;
            color: #6b7280;
        }
    </style>
@endpush

@section('content')
    <!-- Dashboard Hero Section -->
    <div
        style="background: var(--bg-gradient); padding: 50px 20px 70px; color: white; position: relative; margin: -20px -20px 0;">
        <!-- Logout Button -->
        <a href="{{ route('unit.logout') }}"
            style="position: absolute; top: 25px; right: 20px; color: white; font-size: 20px; background: rgba(255,255,255,0.15); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; text-decoration: none; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
            <i class="fas fa-sign-out-alt"></i>
        </a>

        <div
            style="font-size: 14px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; margin-bottom: 8px; font-weight: 700;">
            Dashboard</div>
        <div style="font-size: 32px; font-weight: 800; margin-bottom: 5px;">Hello,</div>
        <div style="font-size: 22px; font-weight: 600; opacity: 0.95;">{{ $data->name }}</div>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">🧵</span>
            <span class="stat-value">{{ Str::limit($data->masterStage->name ?? 'N/A', 15) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🏭</span>
            <span class="stat-value">{{ Str::limit($data->masterFabricWarehouse->cutting_master_name ?? 'N/A', 15) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📞</span>
            <span class="stat-value">{{ substr($data->phone ?? '0000', -4) }}</span>
        </div>
    </div>

    <!-- Menu Section -->
    <div style="margin-top: 24px;">
        <div class="menu-grid">
            <a href="{{ route('unit.assignments') }}" class="menu-item">
                <div class="menu-icon" style="background: rgba(102, 126, 234, 0.1); color: var(--primary);">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="menu-details">
                    <h3>Assignments</h3>
                    <p>View pending lots & orders</p>
                </div>
                <i class="fas fa-chevron-right" style="margin-left: auto; color: #d1d5db;"></i>
            </a>

            <a href="{{ route('unit.history') }}" class="menu-item">
                <div class="menu-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="fas fa-history"></i>
                </div>
                <div class="menu-details">
                    <h3>Work History</h3>
                    <p>Track your completed tasks</p>
                </div>
                <i class="fas fa-chevron-right" style="margin-left: auto; color: #d1d5db;"></i>
            </a>
        </div>
    </div>
@endsection