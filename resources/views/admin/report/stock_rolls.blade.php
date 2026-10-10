@extends('admin.layouts.app')
@section('title', 'Fabric Stock Report (Rolls)')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-barcode"></i> Fabric stock report (rolls)
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-cubes text-muted"></i> Individual roll tracking</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.report.stock') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Fabric summary report
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.stock.rolls') }}" class="mb-0">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Fabric</label>
                    <select name="fabric_id" class="form-control form-control-sm select2 erp-filter-select">
                        <option value="">All fabrics</option>
                        @foreach($fabrics as $f)
                            <option value="{{ $f->id }}" {{ request('fabric_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Warehouse</label>
                    <select name="warehouse_id" class="form-control form-control-sm select2 erp-filter-select">
                        <option value="">All warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Roll number</label>
                    <input type="text" name="roll_no" class="form-control form-control-sm erp-filter-input" placeholder="Search roll..." value="{{ request('roll_no') }}">
                </div>

                <div class="col-md-3 col-sm-6 mb-1">
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

                <div class="col-md-2 col-sm-12 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.stock.rolls') }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Fabric name</th>
                        <th>Warehouse</th>
                        <th>Supplier</th>
                        <th>Shipment / PO</th>
                        <th class="text-center">Roll number</th>
                        <th class="text-right">Received (m)</th>
                        <th class="text-right">Remaining (m)</th>
                        <th class="text-center" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = $rolls->firstItem() ?: 1; @endphp
                    @forelse($rolls as $r)
                    <tr>
                        <td class="text-center text-muted">{{ $sr++ }}</td>
                        <td><strong>{{ $r->fabric->name ?? 'N/A' }}</strong></td>
                        <td>{{ $r->master_fabric_warehouse?->cutting_master_name ?? 'N/A' }}</td>
                        <td>{{ $r->fabric_receipt->vendor->name ?? '-' }}</td>
                        <td><code>{{ $r->shipment_number ?? '-' }}</code> / <code>{{ $r->purchase_order?->sku ?? '-' }}</code></td>
                        <td class="text-center"><span class="badge badge-light border font-weight-bold">{{ $r->roll_number }}</span></td>
                        <td class="text-right font-weight-bold text-success">{{ number_format($r->meter, 2) }}</td>
                        <td class="text-right font-weight-bold {{ $r->remaining_quantity > 0 ? 'text-dark' : 'text-muted' }}">
                            {{ number_format($r->remaining_quantity, 2) }}
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('admin.report.stock.rolls.tracking', ['fabric_id' => $r->fabric_id, 'roll_no' => $r->roll_number]) }}" 
                               class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;" title="View tracking history">
                                <i class="fas fa-history mr-1"></i> History
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No rolls found matching parameters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rolls->hasPages())
            <div class="p-2 border-top bg-light">
                {{ $rolls->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
