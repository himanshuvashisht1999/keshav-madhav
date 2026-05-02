@extends('admin.layouts.app')
@section('title', 'Fabric Stock Report')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        {{-- Nav Breadcrumbs for Drill Down --}}
                        <h3 class="m-0">
                            <a href="{{ route('admin.report.stock') }}" class="text-dark text-decoration-none">Fabric Stock
                                Report</a>
                            @if($level != 'fabrics' && isset($fabric))
                                <span class="text-muted"> / {{ $fabric->name }}</span>
                            @endif
                            @if($level == 'receipts')
                                <span class="text-muted"> / Shipments</span>
                            @endif
                            @if($level == 'usages')
                                <span class="text-muted"> / Usages</span>
                            @endif
                        </h3>
                    </div>
                    <div class="report-meta d-flex align-items-center gap-2">
                        @if($level === 'fabrics')
                            <a href="{{ route('admin.report.stock.rolls') }}" class="btn btn-sm btn-dark me-2">
                                <i class="fas fa-barcode"></i> Stock Report (Rolls)
                            </a>
                        @endif

                        @if($level != 'fabrics')
                            <a href="{{ route('admin.report.stock') }}" class="btn btn-sm btn-secondary me-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        @endif

                        <a href="{{ route('admin.report.stock.export', request()->query()) }}" class="btn btn-sm btn-success me-1">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                        <a href="{{ route('admin.report.stock.pdf', request()->query()) }}" class="btn btn-sm btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                {{-- Filter Card --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('admin.report.stock') }}">
                            @if(request('fabric_id'))
                                <input type="hidden" name="fabric_id" value="{{ request('fabric_id') }}">
                            @endif
                            @if(request('type'))
                                <input type="hidden" name="type" value="{{ request('type') }}">
                            @endif

                            <div class="row align-items-end g-2">
                                @if($level === 'fabrics')
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Warehouse</label>
                                        <select name="warehouse_id" class="form-control form-control-sm select2">
                                            <option value="">All Warehouses</option>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                                    {{ $wh->cutting_master_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Search Fabric</label>
                                        <input type="text" name="search" class="form-control form-control-sm"
                                            placeholder="Name or Sku..." value="{{ request('search') }}">
                                    </div>
                                @endif

                                <div class="col-md-4">
                                    <label class="small fw-bold mb-1">Remaining Qty (Range)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0">From</span>
                                        <input type="number" step="0.01" name="qty_from" class="form-control" placeholder="0.00" value="{{ request('qty_from') }}">
                                        <span class="input-group-text bg-light border-x-0">To</span>
                                        <input type="number" step="0.01" name="qty_to" class="form-control" placeholder="Any" value="{{ request('qty_to') }}">
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex gap-1">
                                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                        <i class="fas fa-search"></i> Apply Filter
                                    </button>
                                    <a href="{{ route('admin.report.stock', request()->only(['fabric_id', 'type'])) }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

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
        </section>
    </div>

@endsection

@section('scripts')
    <style>
        .table-report th {
            background: #f8f9fa;
            font-weight: 600;
            vertical-align: middle;
        }

        .table-report td {
            vertical-align: middle;
        }
    </style>
@endsection