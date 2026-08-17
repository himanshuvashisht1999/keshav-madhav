@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .content-wrapper {
            font-family: 'Outfit', sans-serif !important;
        }

        .table-report {
            border: 1px solid #dee2e6;
        }

        .table-report thead th {
            background: #1e293b;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
            font-size: 0.82rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Soft Badge styles */
        .badge-soft-primary {
            background: rgba(99, 102, 241, 0.1) !important;
            color: #6366f1 !important;
        }
        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
        }
        .badge-soft-success {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }
        .badge-soft-danger {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }
        .badge-soft-info {
            background: rgba(14, 165, 233, 0.1) !important;
            color: #0ea5e9 !important;
        }
        .badge-soft-dark {
            background: rgba(30, 41, 59, 0.1) !important;
            color: #1e293b !important;
        }
    </style>

    <div class="content-wrapper bg-light pb-5">
        <section class="content pt-3">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
                @endif

                {{-- FILTERS CARD (COMPACT ERP DESIGN) --}}
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('admin.reports.outflows') }}">
                            <div class="row align-items-end">
                                <!-- Type -->
                                <div class="col-md-3 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Adjustment Type</label>
                                    <select name="type" class="form-control form-control-sm select2">
                                        <option value="">All Outflow Types</option>
                                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit</option>
                                        <option value="sampling" {{ request('type') === 'sampling' ? 'selected' : '' }}>Sampling</option>
                                        <option value="dead" {{ request('type') === 'dead' ? 'selected' : '' }}>Dead / Damage</option>
                                    </select>
                                </div>
                                <!-- Lot No -->
                                <div class="col-md-2 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Lot No</label>
                                    <input type="text" name="lot_no" value="{{ request('lot_no') }}" class="form-control form-control-sm" placeholder="e.g. 26283">
                                </div>
                                <!-- Design No -->
                                <div class="col-md-2 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Design Number</label>
                                    <select name="design_no" class="form-control form-control-sm select2">
                                        <option value="">All Designs</option>
                                        @foreach($designs as $design)
                                            <option value="{{ $design }}" {{ request('design_no') == $design ? 'selected' : '' }}>{{ $design }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Slip ID -->
                                <div class="col-md-2 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Slip Reference ID</label>
                                    <input type="number" name="slip_id" value="{{ request('slip_id') }}" class="form-control form-control-sm" placeholder="e.g. 1629">
                                </div>
                                <!-- Responsible Unit -->
                                <div class="col-md-3 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Responsible Unit</label>
                                    <select name="unit_id" class="form-control form-control-sm select2">
                                        <option value="">All Units</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Date Range -->
                                <div class="col-md-3 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">Start Date</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="font-weight-bold text-xs text-uppercase text-muted mb-1">End Date</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-md-6 mb-2 d-flex justify-content-end align-items-center" style="gap: 8px;">
                                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">
                                        <i class="fas fa-search mr-1"></i> Search
                                    </button>
                                    <a href="{{ route('admin.reports.outflows') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3">
                                        Reset
                                    </a>
                                    <a href="{{ route('admin.reports.outflows.export', request()->all()) }}" class="btn btn-success btn-sm font-weight-bold shadow-sm px-3" title="Export Excel">
                                        <i class="fas fa-file-excel mr-1"></i> Excel
                                    </a>
                                    <a href="{{ route('admin.reports.outflows.pdf', request()->all()) }}" class="btn btn-danger btn-sm font-weight-bold shadow-sm px-3" title="Export PDF">
                                        <i class="fas fa-file-pdf mr-1"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- RESULTS CARD --}}
                <div class="card border-0 shadow-sm bg-white" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <span class="text-muted small">Report Date: <strong>{{ now()->format('d M Y') }}</strong></span>
                        <h5 class="mb-0 text-success font-weight-bold h6">Grand Total Quantity: {{ number_format($totalQuantity) }} pcs</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center text-sm table-report">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="py-3" style="width: 60px;">Sr No</th>
                                        <th class="py-3">Log Date</th>
                                        <th class="py-3">Slip ID</th>
                                        <th class="py-3">Lot No</th>
                                        <th class="py-3">Design Number</th>
                                        <th class="py-3">Type</th>
                                        <th class="py-3">Size & Color</th>
                                        <th class="py-3">Outflow Quantity</th>
                                        <th class="py-3">Responsible Unit</th>
                                        <th class="py-3 text-left">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($outflows as $out)
                                        @php
                                            $badgeClass = 'badge-soft-info';
                                            if ($out->type === 'debit') $badgeClass = 'badge-soft-danger';
                                            if ($out->type === 'sampling') $badgeClass = 'badge-soft-primary';
                                            if ($out->type === 'dead') $badgeClass = 'badge-soft-dark';
                                        @endphp
                                        <tr class="border-bottom">
                                            <td class="py-3 text-muted font-weight-bold">
                                                {{ $loop->iteration + ($outflows->currentPage() - 1) * $outflows->perPage() }}
                                            </td>
                                            <td class="py-3 text-muted">
                                                {{ date('d-m-Y', strtotime($out->created_at)) }}
                                            </td>
                                            <td class="py-3 font-weight-bold">
                                                <a href="{{ route('admin.packing.view', $out->slip->packingMain->id ?? '#') }}" target="_blank">
                                                    #{{ $out->slip_id }}
                                                </a>
                                            </td>
                                            <td class="py-3">
                                                <span class="badge badge-light border">LOT-{{ str_pad($out->lot_no, 4, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="py-3">
                                                <strong class="text-dark">{{ $out->product->design_number ?? 'N/A' }}</strong>
                                                <span class="d-block text-xs text-muted">{{ $out->product->name ?? 'Garment' }}</span>
                                            </td>
                                            <td class="py-3">
                                                <div class="text-dark font-weight-bold">Size: {{ $out->size->size ?? 'N/A' }}</div>
                                                <div class="text-muted text-xs">Color: {{ $out->color->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="py-3 font-weight-bold text-dark" style="font-size: 0.95rem;">
                                                {{ $out->quantity }} <span class="text-xs text-muted font-normal">PCS</span>
                                            </td>
                                            <td class="py-3 text-muted font-weight-bold">
                                                {{ $out->responsibleUnit->name ?? 'N/A' }}
                                            </td>
                                            <td class="py-3 text-left text-muted text-xs" style="max-width: 200px; white-space: normal; word-break: break-all;">
                                                {{ $out->remarks ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-muted py-5">
                                                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4816154-4017688.png" style="width: 120px; opacity: 0.4;" alt="Empty"><br>
                                                <span class="d-block mt-3 h6 font-weight-bold text-slate">No Outflow Adjustments Found</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- PAGINATION --}}
                        @if($outflows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
                                {{ $outflows->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
