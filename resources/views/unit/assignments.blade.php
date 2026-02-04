<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Assignments</title>
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
            padding-bottom: 80px;
        }

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
            font-weight: 700;
        }

        .app-content {
            padding: 20px;
        }

        .assignment-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 12px;
        }

        .date-badge {
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-completed {
            background: #d1fae5;
            color: #059669;
        }

        .assignment-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .assignment-footer {
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: flex-end;
        }

        .btn-view {
            background: var(--bg-gradient);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
    </style>
</head>

<body>

    <div class="app-header">
        <div class="header-top">
            <div class="page-title">
                <i class="fas fa-clipboard-list"></i> Assignments
            </div>
            <a href="{{ route('unit.logout') }}" style="color: white; font-size: 20px;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <div class="app-content">
        @if($assignments->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <h3>No Assignments Found</h3>
                <p>You don't have any pending assignments at the moment.</p>
            </div>
        @else
            @if($type == 'cutting')
                <!-- CUTTING MASTER ASSIGNMENTS -->
                @foreach($assignments as $item)
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <span class="date-badge">{{ $item->created_at->format('d M Y') }}</span>
                            <span class="status-badge status-pending">
                                Assigned
                            </span>
                        </div>
                        <div class="assignment-body">
                            <div class="info-item">
                                <span class="info-label">Order No</span>
                                <span class="info-value">{{ $item->orderMain->sku ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Design No</span>
                                <span class="info-value">{{ $item->design_number ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fabric</span>
                                <span class="info-value">{{ $item->fabric->name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Color</span>
                                <span class="info-value">{{ $item->colors->name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Quantity</span>
                                <span class="info-value">{{ $item->total_quantity ?? 0 }} Pcs</span>
                            </div>
                        </div>
                        <div class="assignment-footer">
                            <a href="{{ route('unit.assignments.details', ['type' => 'cutting', 'id' => $item->id]) }}"
                                class="btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- OTHER STAGES ASSIGNMENTS (Incoming Slips) -->
                @foreach($assignments as $item)
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <span class="date-badge">{{ $item->created_at->format('d M Y') }}</span>
                            <span class="status-badge status-pending">Incoming Slip</span>
                        </div>
                        <div class="assignment-body">
                            <div class="info-item">
                                <span class="info-label">From Stage</span>
                                <span class="info-value">{{ $item->from_stage->name ?? $item->fromStage->name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Lot No</span>
                                <span class="info-value">{{ $item->lot_no ?? 'Pending' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sent By</span>
                                <span
                                    class="info-value">{{ $item->getFromUnitMaster->name ?? $item->getUnitMaster->name ?? '-' }}</span>
                            </div>
                            <!-- For Transactions, show quantity if available (remaining_quantity) -->
                            <div class="info-item">
                                <span class="info-label">Quantity</span>
                                <span class="info-value">{{ $item->remaining_quantity ?? '-' }} Pcs</span>
                            </div>
                        </div>
                        <div class="assignment-footer">
                            @if(isset($item->transaction_type))
                                <!-- Transaction Details Link -->
                                <a href="{{ route('unit.assignments.details', ['type' => $item->transaction_type, 'id' => $item->id]) }}"
                                    class="btn-view">
                                    <i class="fas fa-clipboard-check"></i> Process
                                </a>
                            @elseif($item->production_slip_digitization_id ?? false)
                                <a href="{{ route('unit.assignments.details', ['type' => 'production', 'id' => $item->production_slip_digitization_id]) }}"
                                    class="btn-view">
                                    <i class="fas fa-eye"></i> View Slip
                                </a>
                            @elseif(isset($item->slip_file))
                                <!-- Legacy/Direct Slip Support -->
                                <a href="{{ route('unit.assignments.details', ['type' => 'production', 'id' => $item->id]) }}"
                                    class="btn-view">
                                    <i class="fas fa-eye"></i> View Slip
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        @endif
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