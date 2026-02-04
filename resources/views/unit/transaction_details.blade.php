<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Process Assignment</title>
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
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 90px;
        }

        /* Header */
        .app-header {
            background: var(--bg-gradient);
            padding: 16px 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
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
            z-index: 1000;
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
            transition: all 0.3s;
        }

        .nav-item i {
            font-size: 22px;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            text-decoration: none;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        .app-content {
            padding: 20px;
            padding-bottom: 80px;
        }

        /* Information Card */
        .info-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-item {
            margin-bottom: 8px;
        }

        .info-label {
            display: block;
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
        }

        .view-slip-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f3f4f6;
            color: #4b5563;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
            width: 100%;
            justify-content: center;
        }

        .view-slip-btn:hover {
            background: #e5e7eb;
        }

        /* Upload Section */
        .upload-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 15px;
            background: #f9fafb;
        }

        .camera-container {
            position: relative;
            width: 100%;
            height: 300px;
            background: black;
            border-radius: 16px;
            overflow: hidden;
            display: none;
        }

        #camera-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #capture-preview {
            width: 100%;
            height: 250px;
            object-fit: contain;
            background: #f3f4f6;
            border-radius: 12px;
            display: none;
            margin-bottom: 16px;
        }

        .btn-capture {
            background: var(--bg-gradient);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit {
            background: #10b981;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 700;
            margin-top: 16px;
            cursor: pointer;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 8px;
        }

        /* Success Message */
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body>

    <div class="app-header">
        <div class="header-left">
            <a href="{{ route('unit.assignments') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="page-title">Process</div>
        </div>
        <a href="{{ route('unit.logout') }}" style="color: white; font-size: 20px;">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <div class="app-content">

        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- INCOMING ASSIGNMENT INFO --}}
        <div class="info-card">
            <div class="section-title">Incoming Details</div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Order No</span>
                    <span class="info-value">{{ $transaction->orderProduct->orderMain->sku ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lot No</span>
                    <span class="info-value">{{ $transaction->lot_no }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">From Stage</span>
                    <span class="info-value">{{ $transaction->from_stage->name ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Incoming Qty</span>
                    <span class="info-value">{{ $transaction->remaining_quantity }} Pcs</span>
                </div>
                <div class="info-item" style="grid-column: span 2;">
                    <span class="info-label">Sent By</span>
                    <span class="info-value">{{ $transaction->getFromUnitMaster->name ?? '-' }}</span>
                </div>
            </div>

            @if($transaction->production_slip_digitization_id)
                <div style="margin-top: 10px;">
                    <a href="{{ route('unit.download.slip', $transaction->production_slip_digitization_id) }}"
                        class="view-slip-btn" style="background: #e5e7eb;">
                        <i class="fas fa-file-pdf"></i> Download Slip
                    </a>
                </div>
            @endif
        </div>

    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="{{ route('unit.dashboard') }}" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('unit.assignments') }}" class="nav-item active">
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