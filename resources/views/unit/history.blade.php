<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Upload History</title>

    <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --success: #10b981;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        /* App Header */
        .app-header {
            background: var(--bg-gradient);
            padding: 16px 20px 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 20px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .unit-badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
        }

        /* Content */
        .app-content {
            padding: 20px;
        }

        .slip-grid {
            display: grid;
            gap: 16px;
        }

        .slip-card {
            background: white;
            border-radius: 20px;
            padding: 16px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 16px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .slip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--bg-gradient);
        }

        .slip-card:active {
            transform: scale(0.98);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .slip-thumbnail {
            width: 90px;
            height: 90px;
            border-radius: 12px;
            object-fit: cover;
            background: #f3f4f6;
            flex-shrink: 0;
        }

        .slip-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .slip-date {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .slip-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .badge-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .badge-approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .badge-type {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .slip-meta {
            font-size: 13px;
            color: #9ca3af;
            display: flex;
            gap: 12px;
        }

        .slip-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .chevron {
            color: #d1d5db;
            font-size: 18px;
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-text {
            font-size: 18px;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-subtext {
            font-size: 14px;
            color: #9ca3af;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 20px 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-around;
            z-index: 1000;
            border-top: 1px solid #f3f4f6;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #9ca3af;
            transition: all 0.3s;
            padding: 8px 16px;
            border-radius: 12px;
        }

        .nav-item.active {
            color: var(--primary);
            background: rgba(102, 126, 234, 0.1);
        }

        .nav-item i {
            font-size: 22px;
        }

        .nav-label {
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .app-header {
                padding: 16px 16px 24px;
            }

            .app-content {
                padding: 16px;
            }

            .slip-thumbnail {
                width: 80px;
                height: 80px;
            }
        }

        @supports (padding: max(0px)) {
            .app-header {
                padding-top: max(20px, env(safe-area-inset-top));
            }

            .bottom-nav {
                padding-bottom: max(20px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body>

<!-- App Header -->
<div class="app-header">
    <div class="header-top">
        <div class="page-title">
            <i class="fas fa-history"></i>
            <span>Upload History</span>
        </div>
    </div>
    <div class="unit-badge">
        <i class="fas fa-industry"></i> {{ $unit->name }}
    </div>
</div>

<!-- Main Content -->
<div class="app-content">
    <div class="slip-grid">
        @forelse($slips as $slip)
            <a href="{{ route('unit.view.slip', ['type' => $slip['type'], 'id' => $slip['id']]) }}" class="slip-card">
                <img 
                    src="{{ asset('assets/production_slips/' . $slip['slip_file']) }}" 
                    alt="Slip" 
                    class="slip-thumbnail"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22 font-size=%2214%22%3E📷%3C/text%3E%3C/svg%3E'"
                >
                <div class="slip-content">
                    <div class="slip-header">
                        <div>
                            <div class="slip-date">
                                <i class="far fa-clock"></i> {{ $slip['created_at']->format('d M Y, h:i A') }}
                            </div>
                            <div class="slip-badges">
                                <span class="badge badge-type">
                                    {{ $slip['type'] === 'fabric' ? '🧵 Fabric' : '📦 Production' }}
                                </span>
                                <span class="badge {{ $slip['status'] == 0 ? 'badge-pending' : 'badge-approved' }}">
                                    {{ $slip['status'] == 0 ? '⏳ Pending' : '✅ Done' }}
                                </span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right chevron"></i>
                    </div>
                    <div class="slip-meta">
                        @if($slip['type'] === 'fabric')
                            <span><i class="fas fa-tag"></i> {{ $slip['lot_no'] }}</span>
                            <span><i class="fas fa-shopping-cart"></i> {{ $slip['order_no'] }}</span>
                        @else
                            <span><i class="fas fa-tag"></i> {{ $slip['lot_no'] }}</span>
                            <span><i class="fas fa-layer-group"></i> {{ $slip['stage'] }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <div class="empty-text">No Slips Yet</div>
                <div class="empty-subtext">Your uploaded slips will appear here</div>
            </div>
        @endforelse
    </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="{{ route('unit.dashboard') }}" class="nav-item">
        <i class="fas fa-camera"></i>
        <span class="nav-label">Upload</span>
    </a>
    <a href="{{ route('unit.history') }}" class="nav-item active">
        <i class="fas fa-history"></i>
        <span class="nav-label">History</span>
    </a>
    <a href="{{ route('unit.logout') }}" class="nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span class="nav-label">Logout</span>
    </a>
</div>

</body>
</html>