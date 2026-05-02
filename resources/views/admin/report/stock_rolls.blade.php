@extends('admin.layouts.app')
@section('title', 'Fabric Stock Report (Rolls)')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="m-0">Fabric Stock Report (Rolls)</h3>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.report.stock') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Fabrics Summary</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.report.stock.rolls') }}">
                        <div class="row align-items-end g-2">
                            <div class="col-md-3">
                                <label class="small fw-bold mb-1">Fabric</label>
                                <select name="fabric_id" class="form-control form-control-sm select2">
                                    <option value="">All Fabrics</option>
                                    @foreach($fabrics as $f)
                                        <option value="{{ $f->id }}" {{ request('fabric_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">Warehouse</label>
                                <select name="warehouse_id" class="form-control form-control-sm select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="small fw-bold mb-1">Roll No</label>
                                <input type="text" name="roll_no" class="form-control form-control-sm" placeholder="Search Roll..." value="{{ request('roll_no') }}">
                            </div>

                            <div class="col-md-3">
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
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.report.stock.rolls') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Sr No</th>
                                    <th>Fabric Name</th>
                                    <th>Warehouse</th>
                                    <th>Supplier</th>
                                    <th>Shipment / PO</th>
                                    <th>Roll Number</th>
                                    <th class="text-end">Recv (m)</th>
                                    <th class="text-end">Remaining (m)</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr = $rolls->firstItem() ?: 1; @endphp
                                @forelse($rolls as $r)
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td class="fw-bold">{{ $r->fabric->name ?? 'N/A' }}</td>
                                    <td>{{ $r->master_fabric_warehouse?->cutting_master_name ?? 'N/A' }}</td>
                                    <td>{{ $r->fabric_receipt->vendor->name ?? '-' }}</td>
                                    <td>{{ $r->shipment_number ?? '-' }} / {{ $r->purchase_order?->sku ?? '-' }}</td>
                                    <td class="fw-bold text-danger">{{ $r->roll_number }}</td>
                                    <td class="text-end">{{ number_format($r->meter, 2) }}</td>
                                    <td class="text-end font-bold {{ $r->remaining_quantity > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ number_format($r->remaining_quantity, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.report.stock.rolls.tracking', ['fabric_id' => $r->fabric_id, 'roll_no' => $r->roll_number]) }}" 
                                           class="btn btn-xs btn-info" title="View Tracking details">
                                            <i class="fas fa-eye"></i> View History
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

                    <div class="mt-3">
                        {{ $rolls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
