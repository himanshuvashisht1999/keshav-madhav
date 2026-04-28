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
                            <a href="{{ route('admin.report.stock.rolls') }}" class="btn btn-sm btn-dark me-3"><i class="fas fa-barcode"></i> Stock Report (Rolls)</a>
                            <form method="GET" action="{{ route('admin.report.stock') }}" class="d-flex me-3">
                                <select name="warehouse_id" class="form-control form-control-sm me-2 select2"
                                    style="min-width: 200px;">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->cutting_master_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" class="form-control form-control-sm me-2"
                                    placeholder="Search fabric..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                            </form>
                        @endif

                        @if($level != 'fabrics')
                            <a href="{{ route('admin.report.stock') }}" class="btn btn-sm btn-secondary me-2"><i
                                    class="fas fa-arrow-left"></i> Back to Main</a>
                        @endif
                        <a href="{{ route('admin.report.stock.export', request()->query()) }}"
                            class="btn btn-sm btn-success me-2">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                        <a href="{{ route('admin.report.stock.pdf', request()->query()) }}" class="btn btn-sm btn-danger"
                            target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

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