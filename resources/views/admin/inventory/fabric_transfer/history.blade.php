@extends('admin.layouts.app')
@section('title', 'Transfer History')

@section('content')
<style>
    :root {
        --primary: #6366f1;
        --bg-main: #f8fafc;
        --border: #e2e8f0;
    }
    .content-wrapper {
        background-color: var(--bg-main);
    }
    .premium-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    .table thead th {
        background: #fcfdfe;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 1px solid var(--border);
    }
    .barcode-display {
        font-family: monospace;
        background: #f1f5f9;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-0">Fabric Transfer History</h1>
                <p class="text-muted">Manage your fabric transfer history.</p>
            </div>
            <a href="{{ route('admin.inventory.fabric_transfer.index') }}" class="btn btn-primary px-4">
                <i class="fas fa-plus mr-2"></i> New Transfer
            </a>
        </div>

        <div class="premium-card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.inventory.fabric_transfer.history') }}" method="GET" class="row align-items-end">
                    <div class="col-md-2 mb-3">
                        <label class="form-label text-muted small text-uppercase font-weight-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label text-muted small text-uppercase font-weight-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label text-muted small text-uppercase font-weight-bold">From Warehouse</label>
                        <select name="from_warehouse_id" class="form-control select2">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label text-muted small text-uppercase font-weight-bold">To Warehouse</label>
                        <select name="to_warehouse_id" class="form-control select2">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label text-muted small text-uppercase font-weight-bold">Fabric Name</label>
                        <select name="fabric_id" class="form-control select2">
                            <option value="">All Fabrics</option>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->id }}" {{ request('fabric_id') == $fabric->id ? 'selected' : '' }}>{{ $fabric->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 text-right mt-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-search mr-2"></i> Filter</button>
                        <a href="{{ route('admin.inventory.fabric_transfer.history') }}" class="btn btn-outline-secondary ml-2">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">From Warehouse</th>
                            <th class="py-3">To Warehouse</th>
                            <th class="py-3">Fabric Name</th>
                            <th class="py-3 text-center">Total Rolls</th>
                            <th class="py-3 text-center">Total Meters</th>
                            <th class="py-3 text-right px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $index => $row)
                        @php
                            $fabricNames = $row->items->pluck('fabric.name')->filter()->unique()->toArray();
                            $fabricDetails = implode(', ', $fabricNames) ?: 'N/A';
                        @endphp
                        <tr>
                            <td class="px-4 align-middle text-muted small">
                                {{ $transfers->firstItem() + $index }}
                            </td>
                            <td class="align-middle text-muted small">
                                {{ \Carbon\Carbon::parse($row->transfer_date)->format('Y-m-d') }}
                            </td>
                            <td class="align-middle">
                                {{ $row->fromWarehouse->cutting_master_name ?? 'N/A' }}
                            </td>
                            <td class="align-middle">
                                {{ $row->toWarehouse->cutting_master_name ?? 'N/A' }}
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light px-2 py-1 border">{{ Str::limit($fabricDetails, 30) }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-info px-3 py-2">{{ $row->items->count() }} Rolls</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-secondary px-3 py-2">{{ number_format($row->items->sum('meter'), 2) }} M</span>
                            </td>
                            <td class="align-middle text-right px-4">
                                <a href="{{ route('admin.inventory.fabric_transfer.show', $row->id) }}" class="btn btn-sm btn-outline-primary" title="View Transfer">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <p>No transfer history found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $transfers->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    $('.select2').select2({
        width: '100%',
        theme: 'bootstrap4',
        allowClear: true
    });
});
</script>
@endsection
