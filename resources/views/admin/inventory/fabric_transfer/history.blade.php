@extends('admin.layouts.app')
@section('title', 'Fabric Transfer History')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Fabric Transfer History</h1>
                <p class="erp-page-subtitle">Track and audit roll transfers across warehouses</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="hud-stat-box mr-3">
                    <span class="hud-stat-label">Total meters</span>
                    <span class="hud-stat-value text-success">{{ number_format($total_meters ?? 0, 2) }} M</span>
                </div>
                <a href="{{ route('admin.inventory.fabric_transfer.index') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> New Transfer
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="erp-card mb-2 p-2">
            <form action="{{ route('admin.inventory.fabric_transfer.history') }}" method="GET">
                <div class="form-row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">Start date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm erp-input" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">End date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm erp-input" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">From warehouse</label>
                        <select name="from_warehouse_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">To warehouse</label>
                        <select name="to_warehouse_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All warehouses</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">Fabric name</label>
                        <select name="fabric_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All fabrics</option>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->id }}" {{ request('fabric_id') == $fabric->id ? 'selected' : '' }}>{{ $fabric->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex">
                        <button type="submit" class="btn-erp btn-erp-primary flex-fill mr-1">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.fabric_transfer.history') }}" class="btn-erp btn-erp-secondary">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table class="table erp-table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="width: 110px;">Date</th>
                            <th>From warehouse</th>
                            <th>To warehouse</th>
                            <th>Fabric name</th>
                            <th class="text-center" style="width: 120px;">Total rolls</th>
                            <th class="text-center" style="width: 130px;">Total meters</th>
                            <th class="text-center" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $index => $row)
                        @php
                            $fabricNames = $row->items->pluck('fabric.name')->filter()->unique()->toArray();
                            $fabricDetails = implode(', ', $fabricNames) ?: 'N/A';
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">
                                {{ $transfers->firstItem() + $index }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($row->transfer_date)->format('Y-m-d') }}
                            </td>
                            <td>
                                {{ $row->fromWarehouse->cutting_master_name ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $row->toWarehouse->cutting_master_name ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="badge-erp-status badge-erp-secondary">{{ Str::limit($fabricDetails, 30) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge-erp-status badge-erp-info font-weight-bold">{{ $row->items->count() }} Rolls</span>
                            </td>
                            <td class="text-center">
                                <span class="badge-erp-status badge-erp-primary font-weight-bold">{{ number_format($row->items->sum('meter'), 2) }} M</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.inventory.fabric_transfer.show', $row->id) }}" class="erp-action-btn erp-btn-view" title="View Transfer">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-history fa-2x mb-2 text-secondary"></i>
                                <div>No transfer history found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
            <div class="p-2 border-top bg-white">
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
