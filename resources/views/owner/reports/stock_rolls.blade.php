@extends('owner.layouts.app')

@section('title', 'Rolls Inventory')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    body {
        background: #f8fafc;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 30px 20px 50px;
        border-radius: 0 0 35px 35px;
        color: white;
    }

    .app-header h1 {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .search-panel {
        margin-top: -30px;
        padding: 0 20px;
    }

    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .roll-card {
        background: white;
        border-radius: 20px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        display: block;
        text-decoration: none !important;
        color: inherit;
    }

    .roll-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .roll-no {
        font-size: 15px;
        font-weight: 800;
        color: #ef4444;
        background: #fef2f2;
        padding: 4px 12px;
        border-radius: 10px;
    }

    .fabric-name {
        font-weight: 700;
        color: #1e293b;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .qty-badge {
        text-align: right;
    }

    .qty-value {
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
        display: block;
    }

    .qty-label {
        font-size: 9px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 700;
    }

    .roll-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        font-size: 11px;
        color: #64748b;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-group label {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .form-control-custom {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        padding: 10px 15px;
        height: auto;
    }

    .btn-apply {
        background: var(--primary-gradient);
        border: none;
        color: white;
        padding: 12px;
        border-radius: 12px;
        font-weight: 800;
        width: 100%;
        margin-top: 15px;
    }
</style>
@endsection

@section('content')
<div class="mobile-only">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('owner.stock') }}" class="text-white"><i class="fas fa-arrow-left"></i></a>
            <span class="font-weight-bold" style="font-size: 14px; opacity: 0.8;">Inventory</span>
            <div style="width: 20px;"></div>
        </div>
        <h1>Rolls List</h1>
        <p class="opacity-75" style="font-size: 13px;">Browse all fabric rolls in stock</p>
    </div>

    <div class="search-panel">
        <div class="filter-card">
            <form action="{{ route('owner.report.stock.rolls') }}" method="GET">
                <div class="row g-2">
                    <div class="col-12 mb-2">
                        <div class="form-group mb-0">
                            <label>Fabric</label>
                            <select name="fabric_id" class="form-control form-control-custom select2">
                                <option value="">All Fabrics</option>
                                @foreach($fabrics as $f)
                                    <option value="{{ $f->id }}" {{ request('fabric_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-0">
                            <label>Warehouse</label>
                            <select name="warehouse_id" class="form-control form-control-custom">
                                <option value="">All</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-0">
                            <label>Roll No</label>
                            <input type="text" name="roll_no" class="form-control form-control-custom" placeholder="Search..." value="{{ request('roll_no') }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-apply">Filter Rolls</button>
            </form>
        </div>
    </div>

    <div class="container-fluid mt-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <span class="text-muted font-weight-bold" style="font-size: 12px;">{{ $rolls->total() }} Rolls Found</span>
            <a href="{{ route('owner.report.stock.rolls') }}" class="text-primary font-weight-bold" style="font-size: 12px;">Clear</a>
        </div>

        @foreach($rolls as $r)
        <a href="{{ route('owner.report.stock.rolls.tracking', ['fabric_id' => $r->fabric_id, 'roll_no' => $r->roll_number]) }}" class="roll-card">
            <div class="roll-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="roll-no">#{{ $r->roll_number }}</span>
                    <span class="fabric-name">{{ $r->fabric->name ?? 'N/A' }}</span>
                </div>
                <div class="qty-badge">
                    <span class="qty-value {{ $r->remaining_quantity > 0 ? 'text-success' : 'text-muted' }}">
                        {{ number_format($r->remaining_quantity, 1) }}m
                    </span>
                    <span class="qty-label">Available</span>
                </div>
            </div>
            
            <div class="roll-meta">
                <div class="meta-item">
                    <i class="fas fa-warehouse opacity-50"></i>
                    <span>{{ \Illuminate\Support\Str::limit($r->master_fabric_warehouse?->cutting_master_name ?? 'N/A', 15) }}</span>
                </div>
                <div class="meta-item justify-content-end">
                    <i class="fas fa-truck opacity-50"></i>
                    <span>{{ \Illuminate\Support\Str::limit($r->fabric_receipt->vendor->name ?? '-', 15) }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-file-invoice opacity-50"></i>
                    <span>PO: {{ $r->purchase_order?->sku ?? '-' }}</span>
                </div>
                <div class="meta-item justify-content-end">
                    <i class="fas fa-ship opacity-50"></i>
                    <span>Ship: {{ $r->shipment_number ?? '-' }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-arrow-down text-muted"></i>
                    <span>Recv: {{ number_format($r->meter, 1) }}m</span>
                </div>
                <div class="meta-item justify-content-end">
                    <i class="fas fa-history text-primary"></i>
                    <span class="text-primary font-weight-bold">View History</span>
                </div>
            </div>
        </a>
        @endforeach

        @if($rolls->isEmpty())
            <div class="text-center py-5 opacity-50">
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>No rolls found matching criteria</p>
            </div>
        @endif

        <div class="mt-4">
            {{ $rolls->links('pagination::simple-bootstrap-4') }}
        </div>
    </div>
</div>

<div class="desktop-only p-5 text-center">
    <h3>Switch to Mobile View</h3>
    <p>This page is optimized for the owner app experience.</p>
</div>
@endsection
