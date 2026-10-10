@extends('admin.layouts.app')
@section('title', 'Fabric Stock Report')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-boxes"></i> Fabric stock report
            </h1>
            <div class="erp-report-meta">
                <a href="{{ route('admin.report.stock') }}" class="text-muted text-decoration-none font-weight-bold">Fabric stock</a>
                @if($level != 'fabrics' && isset($fabric))
                    <span class="meta-divider">&bull;</span>
                    <span class="meta-item font-weight-bold text-dark">{{ $fabric->name }}</span>
                @endif
                @if($level == 'receipts')
                    <span class="meta-divider">&bull;</span>
                    <span class="meta-item text-success font-weight-bold">Shipments</span>
                @endif
                @if($level == 'usages')
                    <span class="meta-divider">&bull;</span>
                    <span class="meta-item text-danger font-weight-bold">Usages</span>
                @endif
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            @if($level === 'fabrics')
                <a href="{{ route('admin.report.stock.rolls') }}" class="btn-erp btn-erp-outline mr-1">
                    <i class="fas fa-barcode mr-1"></i> Stock report (rolls)
                </a>
            @endif

            @if($level != 'fabrics')
                <a href="{{ route('admin.report.stock') }}" class="btn-erp btn-erp-outline mr-1">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            @endif

            <a href="{{ route('admin.report.stock.export', request()->query()) }}" class="btn-erp btn-erp-outline mr-1" style="border-color: #22c55e; color: #166534;" title="Export to Excel">
                <i class="fas fa-file-excel mr-1 text-success"></i> Export
            </a>
            <a href="{{ route('admin.report.stock.export_with_rate', request()->query()) }}" class="btn-erp btn-erp-outline mr-1" style="border-color: #22c55e; color: #166534;" title="Export with Rate">
                <i class="fas fa-file-excel mr-1 text-success"></i> Export (rate)
            </a>
            <a href="{{ route('admin.report.stock.pdf', request()->query()) }}" class="btn-erp btn-erp-outline" style="border-color: #ef4444; color: #991b1b;" target="_blank" title="Download PDF">
                <i class="fas fa-file-pdf mr-1 text-danger"></i> PDF
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.stock') }}" class="mb-0">
            @if(request('fabric_id'))
                <input type="hidden" name="fabric_id" value="{{ request('fabric_id') }}">
            @endif
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            @if($level !== 'fabrics' && request('warehouse_id'))
                @foreach((array) request('warehouse_id') as $whId)
                    <input type="hidden" name="warehouse_id[]" value="{{ $whId }}">
                @endforeach
            @endif
            @if($level !== 'fabrics' && request('vendor_id'))
                @foreach((array) request('vendor_id') as $vId)
                    <input type="hidden" name="vendor_id[]" value="{{ $vId }}">
                @endforeach
            @endif

            <div class="form-row align-items-end">
                @if($level === 'fabrics')
                    <div class="col-md-3 col-sm-6 mb-1">
                        <label class="small text-muted font-weight-bold mb-1">Warehouse</label>
                        @php
                            $selectedWarehouses = (array) request('warehouse_id', []);
                        @endphp
                        <select name="warehouse_id[]" class="form-control form-control-sm select2 erp-filter-select" multiple data-placeholder="All warehouses">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ in_array($wh->id, $selectedWarehouses) ? 'selected' : '' }}>
                                    {{ $wh->cutting_master_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-1">
                        <label class="small text-muted font-weight-bold mb-1">Supplier / vendor</label>
                        @php
                            $selectedVendors = (array) request('vendor_id', []);
                        @endphp
                        <select name="vendor_id[]" class="form-control form-control-sm select2 erp-filter-select" multiple data-placeholder="All suppliers">
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ in_array($vendor->id, $selectedVendors) ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-1">
                        <label class="small text-muted font-weight-bold mb-1">Search fabric</label>
                        <input type="text" name="search" class="form-control form-control-sm erp-filter-input"
                            placeholder="Name or SKU..." value="{{ request('search') }}">
                    </div>
                @endif

                <div class="{{ $level === 'fabrics' ? 'col-md-3' : 'col-md-6' }} col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Remaining qty range</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">From</span>
                        </div>
                        <input type="number" step="0.01" name="qty_from" class="form-control erp-filter-input" placeholder="0.00" value="{{ request('qty_from') }}">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">To</span>
                        </div>
                        <input type="number" step="0.01" name="qty_to" class="form-control erp-filter-input" placeholder="Any" value="{{ request('qty_to') }}">
                    </div>
                </div>

                <div class="{{ $level === 'fabrics' ? 'col-md-1' : 'col-md-2' }} col-sm-6 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filters">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.stock', request()->only(['fabric_id', 'type'])) }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Totals Strip --}}
    @if(isset($totals))
        <div class="row mb-2">
            @if(isset($totals->sum_received))
            <div class="col-md-4 col-12 mb-1">
                <div class="erp-card p-2 mb-0 d-flex align-items-center justify-content-between" style="border-left: 4px solid var(--brand-dark-green, #05421c);">
                    <div>
                        <span class="small text-muted font-weight-bold text-uppercase" style="font-size: 11px;">Total received</span>
                        <h4 class="m-0 font-weight-bold text-dark" style="font-size: 18px;">{{ number_format($totals->sum_received ?? 0, 2) }} <span class="small font-weight-normal text-muted">m</span></h4>
                    </div>
                    <div class="rounded-circle p-2 bg-light text-success">
                        <i class="fas fa-boxes fa-lg"></i>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($totals->sum_issued))
            <div class="col-md-4 col-12 mb-1">
                <div class="erp-card p-2 mb-0 d-flex align-items-center justify-content-between" style="border-left: 4px solid #f59e0b;">
                    <div>
                        <span class="small text-muted font-weight-bold text-uppercase" style="font-size: 11px;">Total issued</span>
                        <h4 class="m-0 font-weight-bold text-dark" style="font-size: 18px;">{{ number_format($totals->sum_issued ?? 0, 2) }} <span class="small font-weight-normal text-muted">m</span></h4>
                    </div>
                    <div class="rounded-circle p-2 bg-light text-warning">
                        <i class="fas fa-dolly-flatbed fa-lg"></i>
                    </div>
                </div>
            </div>
            @endif
            @if(isset($totals->sum_remaining))
            <div class="col-md-4 col-12 mb-1">
                <div class="erp-card p-2 mb-0 d-flex align-items-center justify-content-between" style="border-left: 4px solid #10b981;">
                    <div>
                        <span class="small text-muted font-weight-bold text-uppercase" style="font-size: 11px;">Remaining qty</span>
                        <h4 class="m-0 font-weight-bold text-success" style="font-size: 18px;">{{ number_format($totals->sum_remaining ?? 0, 2) }} <span class="small font-weight-normal text-muted">m</span></h4>
                    </div>
                    <div class="rounded-circle p-2 bg-light text-success">
                        <i class="fas fa-cubes fa-lg"></i>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif

    {{-- Level Specific Content --}}
    @if($level === 'fabrics')
        @include('admin.report.partials.stock_level_fabrics')
    @elseif($level === 'warehouses')
        @include('admin.report.partials.stock_level_warehouses')
    @elseif($level === 'receipts')
        @include('admin.report.partials.stock_level_receipts')
    @elseif($level === 'usages')
        @include('admin.report.partials.stock_level_usages')
    @endif

</div>
@endsection