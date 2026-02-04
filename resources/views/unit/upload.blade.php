<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Unit Dashboard</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 90px;
        }

        .app-header {
            background: var(--bg-gradient);
            padding: 16px 20px;
            padding-top: max(16px, env(safe-area-inset-top));
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text {
            font-size: 20px;
            font-weight: 700;
        }

        .unit-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: -20px;
            padding: 0 20px;
        }

        .stat-card {
            background: white;
            padding: 16px;
            border-radius: 20px;
            text-align: center;
            box-shadow: var(--shadow);
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

        .app-content {
            padding: 24px 20px;
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
            box-shadow: var(--shadow);
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

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 20px 24px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
            border-top: 1px solid #f3f4f6;
        }

        .nav-item {
            text-decoration: none;
            color: #9ca3af;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            font-size: 11px;
        }

        .nav-item i {
            font-size: 22px;
        }

        .nav-item.active {
            color: var(--primary);
        }
    </style>
</head>

<body>

    <!-- Premium Hero Section (Header-less Dashboard) -->
    <div style="background: var(--bg-gradient); padding: 50px 20px 70px; color: white; position: relative;">
        <!-- Logout Button Integrated into Hero -->
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

    <div class="stats-grid" style="margin-top: -45px; position: relative; z-index: 10;">
        <div class="stat-card">
            <span class="stat-icon">🧵</span>
            <!-- <span class="stat-label">Stage</span> -->
            <span class="stat-value">{{ Str::limit($data->masterStage->name ?? 'N/A', 15) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🏭</span>
            <!-- <span class="stat-label">Warehouse</span> -->
            <span
                class="stat-value">{{ Str::limit($data->masterFabricWarehouse->cutting_master_name ?? 'N/A', 15) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📞</span>
            <!-- <span class="stat-label">Phone</span> -->
            <span class="stat-value">{{ substr($data->phone ?? '0000', -4) }}</span>
        </div>
    </div>

    <div class="app-content">
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

    <div class="bottom-nav">
        <a href="{{ route('unit.dashboard') }}" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('unit.assignments') }}" class="nav-item">
            <i class="fas fa-clipboard-list"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('unit.history') }}" class="nav-item">
            <i class="fas fa-clock"></i>
            <span>History</span>
        </a>
    </div>

</body>

</html>