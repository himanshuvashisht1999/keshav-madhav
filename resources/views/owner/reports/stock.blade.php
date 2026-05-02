@extends('owner.layouts.app')

@section('title', 'Fabric Stock')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
        --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    }

    body {
        background: #fdf2ff;
        background-image: radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 0.03) 0, transparent 50%), 
                          radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 0.03) 0, transparent 50%), 
                          radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 0.03) 0, transparent 50%);
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 40px 20px 60px;
        border-radius: 0 0 40px 40px;
        color: white;
        margin-bottom: -30px;
        position: relative;
        z-index: 1;
    }

    .app-header h1 {
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.5px;
        margin-bottom: 5px;
    }

    .breadcrumb-custom {
        display: flex;
        gap: 8px;
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 20px;
        align-items: center;
    }

    .breadcrumb-custom a {
        color: white;
        text-decoration: none;
    }

    .search-container {
        position: relative;
        z-index: 10;
        padding: 0 20px;
    }

    .search-box {
        background: white;
        border-radius: 20px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var-card-shadow;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .search-box input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
    }

    .stock-card {
        background: var(--glass-bg);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: var(--card-shadow);
        transition: transform 0.2s;
        text-decoration: none !important;
        display: block;
    }

    .stock-card:active {
        transform: scale(0.98);
    }

    .fabric-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .vendor-badge {
        font-size: 10px;
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
        padding: 4px 10px;
        border-radius: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-label {
        font-size: 9px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 14px;
        font-weight: 900;
        color: #1e293b;
    }

    .stat-value.remaining {
        color: #a855f7;
    }

    .wh-badge {
        background: #f1f5f9;
        color: #475569;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 15px;
    }

    .action-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-receipts {
        background: #ecfdf5;
        color: #059669;
    }

    .btn-usages {
        background: #fff7ed;
        color: #d97706;
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .fab-container {
        position: fixed;
        bottom: 30px;
        right: 20px;
        z-index: 100;
    }

    .fab {
        width: 56px;
        height: 56px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 10px 25px rgba(168, 85, 247, 0.4);
        text-decoration: none !important;
    }
</style>
@endsection

@section('content')
<div class="mobile-only">
    <div class="app-header">
        <div class="breadcrumb-custom">
            <a href="{{ route('owner.dashboard') }}">Home</a>
            <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
            <a href="{{ route('owner.stock') }}">Stock</a>
            @if(isset($fabric))
                <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
                <span>{{ $fabric->name }}</span>
            @endif
        </div>
        <h1>Fabric Stock</h1>
        <p class="mb-0 opacity-75">Real-time inventory management</p>
    </div>

    <div class="search-container">
        @if($level === 'fabrics')
        <form action="{{ route('owner.stock') }}" method="GET">
            <div class="search-box">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="search" placeholder="Search fabrics..." value="{{ request('search') }}">
                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
            </div>
        </form>
        <div class="mt-3 px-1">
            <a href="{{ route('owner.report.stock.rolls') }}" class="btn btn-block btn-primary shadow-sm" style="border-radius: 15px; font-weight: 800; padding: 12px; background: var(--primary-gradient); border: none;">
                <i class="fas fa-list-ul mr-2"></i> View Stock by Roll Number
            </a>
        </div>
        @else
        <div class="search-box justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-arrow-left text-muted" onclick="history.back()"></i>
                <span class="font-weight-bold" style="font-size: 14px;">Back to Summary</span>
            </div>
            <a href="{{ route('owner.stock') }}" class="text-primary font-weight-bold" style="font-size: 12px;">Reset</a>
        </div>
        @endif
    </div>

    <div class="container-fluid mt-4 pb-5">
        @if($level === 'fabrics')
            @foreach($data as $row)
                <a href="{{ route('owner.stock', ['fabric_id' => $row->id, 'warehouse_id' => request('warehouse_id')]) }}" class="stock-card">
                    <div class="fabric-title">
                        {{ $row->name }}
                        <span class="vendor-badge">{{ $row->vendor_name ?: 'Global' }}</span>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Received</span>
                            <span class="stat-value">{{ number_format($row->total_received, 1) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Issued</span>
                            <span class="stat-value">{{ number_format($row->total_issued, 1) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Stock</span>
                            <span class="stat-value remaining">{{ number_format($row->total_remaining, 1) }}</span>
                        </div>
                    </div>
                    <div class="mt-3 text-right">
                        <i class="fas fa-chevron-right text-muted opacity-50"></i>
                    </div>
                </a>
            @endforeach
            
            @if($data->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No fabrics found in stock</p>
                </div>
            @endif

            <div class="px-2">
                {{ $data->links('pagination::simple-bootstrap-4') }}
            </div>

        @elseif($level === 'warehouses')
            <div class="wh-badge">
                <i class="fas fa-info-circle"></i> Showing stock by warehouse
            </div>
            @foreach($data as $row)
                <div class="stock-card">
                    <div class="fabric-title">
                        {{ $row->master_fabric_warehouse?->cutting_master_name ?: 'Unknown' }}
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Received</span>
                            <span class="stat-value">{{ number_format($row->total_received, 1) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Issued</span>
                            <span class="stat-value">{{ number_format($row->total_issued, 1) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Stock</span>
                            <span class="stat-value remaining">{{ number_format($row->total_remaining, 1) }}</span>
                        </div>
                    </div>
                    <div class="action-row">
                        <a href="{{ route('owner.stock', ['fabric_id' => $fabric->id, 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'receipts']) }}" class="btn-action btn-receipts">
                            <i class="fas fa-file-import"></i> Shipments
                        </a>
                        <a href="{{ route('owner.stock', ['fabric_id' => $fabric->id, 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'usages']) }}" class="btn-action btn-usages">
                            <i class="fas fa-industry"></i> Usages
                        </a>
                    </div>
                </div>
            @endforeach

        @elseif($level === 'receipts')
            <div class="wh-badge">
                <i class="fas fa-ship"></i> Shipment History
            </div>
            @foreach($data as $row)
                <div class="stock-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="font-weight-bold" style="font-size: 16px;">{{ $row->fabric_receipt?->sku ?: 'Shipment #' . $row->id }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ $row->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <span class="vendor-badge">{{ $row->fabric_receipt?->vendor?->name ?: 'Direct' }}</span>
                    </div>
                    
                    <div class="p-3 rounded-xl bg-light mb-3 d-flex justify-content-around">
                        <div class="text-center">
                            <span class="stat-label">Roll No</span>
                            <span class="font-weight-black">{{ $row->roll_number }}</span>
                        </div>
                        <div class="text-center">
                            <span class="stat-label">Received</span>
                            <span class="font-weight-black">{{ number_format($row->meter, 1) }}m</span>
                        </div>
                        <div class="text-center">
                            <span class="stat-label">In Stock</span>
                            <span class="font-weight-black text-success">{{ number_format($row->remaining_quantity, 1) }}m</span>
                        </div>
                    </div>

                    <div style="font-size: 12px; color: #64748b;">
                        <i class="fas fa-warehouse mr-1"></i> {{ $row->master_fabric_warehouse?->cutting_master_name ?: '-' }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-barcode mr-1"></i> {{ $row->qrcode_number ?: '-' }}
                    </div>
                </div>
            @endforeach
            <div class="px-2">
                {{ $data->links('pagination::simple-bootstrap-4') }}
            </div>

        @elseif($level === 'usages')
            <div class="wh-badge">
                <i class="fas fa-history"></i> Usage Records
            </div>
            @foreach($data as $row)
                <div class="stock-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="font-weight-bold" style="font-size: 15px;">{{ $row->order_no }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-weight-black text-danger" style="font-size: 16px;">-{{ number_format($row->meter, 1) }}m</div>
                            <div class="stat-label">Consumed</div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-light" style="font-size: 12px;">
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="text-muted">Lot No:</span> <strong>{{ $row->lot_no }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Roll No:</span> <strong>{{ $row->roll_no }}</strong>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="text-muted">Stage:</span> <strong>{{ $row->stageMasterUnit?->name ?: '-' }}</strong>
                            </div>
                            <div class="col-12 mt-2 border-top pt-2">
                                <span class="text-muted">Details:</span> 
                                <span class="font-weight-bold">
                                    {{ $row->orderProductSet?->design_number ?: '' }} 
                                    ({{ $row->orderProductSet?->colors?->name ?: '' }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="px-2">
                {{ $data->links('pagination::simple-bootstrap-4') }}
            </div>
        @endif
    </div>

    <div class="fab-container">
        <a href="{{ route('owner.report.stock.rolls') }}" class="fab">
            <i class="fas fa-barcode"></i>
        </a>
    </div>
</div>

<div class="desktop-only p-5 text-center">
    <h3>Please use a mobile device or responsive view to see the app interface.</h3>
    <p>This module is optimized for mobile app experience.</p>
    <a href="{{ route('admin.report.stock') }}" class="btn btn-primary mt-3">Go to Admin Version</a>
</div>
@endsection