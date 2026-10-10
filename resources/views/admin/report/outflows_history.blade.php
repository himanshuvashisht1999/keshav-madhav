@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

        {{-- HEADER BAR --}}
        <div class="erp-header-bar mb-3">
            <div>
                <h1 class="erp-header-title">Dispose History</h1>
            </div>
            <div class="d-flex align-items-center" style="gap: 12px;">
                <div class="erp-report-meta">
                    Date : {{ now()->format('d M Y h:i A') }}
                </div>
                <a href="{{ route('admin.reports.outflows') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Outflows
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- FILTERS CARD --}}
        <div class="erp-card mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.reports.outflows.history') }}">
                    <div class="row align-items-end">
                        <!-- Type -->
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Adjustment Type</label>
                            <select name="type" class="form-control select2">
                                <option value="">All Outflow Types</option>
                                <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="sampling" {{ request('type') === 'sampling' ? 'selected' : '' }}>Sampling</option>
                                <option value="dead" {{ request('type') === 'dead' ? 'selected' : '' }}>Dead / Damage</option>
                            </select>
                        </div>
                        <!-- Lot No -->
                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Lot Number</label>
                            <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control" placeholder="Search Lot Number">
                        </div>
                        <!-- Design No -->
                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Design Number</label>
                            <select name="design_no" class="form-control select2">
                                <option value="">All Designs</option>
                                @foreach($designs as $design)
                                    <option value="{{ $design }}" {{ request('design_no') == $design ? 'selected' : '' }}>{{ $design }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Slip ID -->
                        <div class="col-md-2 mb-2">
                            <label class="erp-form-label">Slip Reference ID</label>
                            <input type="number" name="slip_id" value="{{ request('slip_id') }}" class="form-control" placeholder="Search Slip ID">
                        </div>
                        <!-- Responsible Unit -->
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Responsible Unit</label>
                            <select name="unit_id" class="form-control select2">
                                <option value="">All Units</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Date Range -->
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-6 mb-2 d-flex justify-content-end align-items-center" style="gap: 8px;">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                            <a href="{{ route('admin.reports.outflows.history') }}" class="btn-erp btn-erp-secondary">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                            <a href="{{ route('admin.reports.outflows.export', request()->all()) }}" class="btn-erp btn-erp-success" title="Export Excel">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </a>
                            <a href="{{ route('admin.reports.outflows.pdf', request()->all()) }}" class="btn-erp btn-erp-danger" title="Export PDF">
                                <i class="fas fa-file-pdf mr-1"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- RESULTS CARD --}}
        <div class="erp-card">
            <div class="card-body p-0">
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <span class="text-muted small">Report Date: <strong>{{ now()->format('d M Y') }}</strong></span>
                    <span class="erp-stat-pill">Grand Total Quantity: <strong>{{ number_format($totalQuantity) }} pcs</strong></span>
                </div>
                <div class="table-responsive">
                    <table class="erp-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">Sr. No.</th>
                                <th>Log Date</th>
                                <th>Slip ID</th>
                                <th>Lot Number</th>
                                <th>Design Number</th>
                                <th>Type</th>
                                <th>Size & Color</th>
                                <th class="text-right">Outflow Quantity</th>
                                <th>Responsible Unit</th>
                                <th>Dispose Reason</th>
                                <th>Disposed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outflows as $out)
                                @php
                                    $badgeClass = 'erp-badge-status-info';
                                    if ($out->type === 'debit') $badgeClass = 'erp-badge-status-danger';
                                    if ($out->type === 'sampling') $badgeClass = 'erp-badge-status-open';
                                    if ($out->type === 'dead') $badgeClass = 'erp-badge-status-neutral';
                                @endphp
                                <tr>
                                    <td class="text-center text-muted font-weight-bold">
                                        {{ $loop->iteration + ($outflows->currentPage() - 1) * $outflows->perPage() }}
                                    </td>
                                    <td>
                                        {{ date('d-m-Y', strtotime($out->created_at)) }}
                                    </td>
                                    <td class="font-weight-bold">
                                        <a href="{{ route('admin.packing.view', $out->slip->packingMain->id ?? '#') }}" target="_blank">
                                            #{{ $out->slip_id }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">LOT-{{ str_pad($out->lot_no, 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $out->product->design_number ?? 'N/A' }}</strong>
                                        <span class="d-block text-muted small">{{ $out->product->name ?? 'Garment' }}</span>
                                    </td>
                                    <td>
                                        <span class="erp-badge-status {{ $badgeClass }} text-uppercase">{{ str_replace('_', ' ', $out->type) }}</span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">Size: {{ $out->size->size ?? 'N/A' }}</div>
                                        <div class="text-muted small">Color: {{ $out->color->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ $out->quantity }} <span class="text-muted small font-weight-normal">PCS</span>
                                    </td>
                                    <td>
                                        {{ $out->responsibleUnit->name ?? 'N/A' }}
                                    </td>
                                    <td class="text-muted small" style="max-width: 200px; white-space: normal; word-break: break-all;">
                                        {{ $out->disposeHistory->reason ?? '-' }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $out->disposeHistory->creator->first_name ?? 'Admin' }} {{ $out->disposeHistory->creator->last_name ?? '' }}
                                        <br>
                                        <small>{{ $out->disposeHistory->created_at ? $out->disposeHistory->created_at->format('d-m-Y H:i') : '' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-muted py-5 text-center">
                                        <h5>No Outflow Adjustments Found</h5>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- PAGINATION --}}
                @if($outflows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    <div class="card-footer bg-white border-top py-2 d-flex justify-content-end">
                        {{ $outflows->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
