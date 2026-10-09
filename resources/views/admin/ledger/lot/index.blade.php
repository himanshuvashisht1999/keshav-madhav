@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-industry text-primary"></i> Lot Production Ledger
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.lot.export-list-pdf', request()->all()) }}" class="btn-erp btn-erp-outline" title="Export to PDF">
                <i class="fas fa-file-pdf text-danger"></i> Export PDF
            </a>
            <a href="{{ route('admin.ledger.lot.export-list-excel', request()->all()) }}" class="btn-erp btn-erp-primary" title="Export to Excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip (Exact Fabric Module Standard) -->
    <div class="row mb-2">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Lots</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalLotsCount ?? 0) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Lots
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Page Assigned Qty</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($pageTotalAssigned ?? 0, 0) }} <small style="font-size: 11px;">Pcs</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Assigned
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">In Progress</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #d97706; line-height: 1.2;">
                            {{ $lots->where('status', '!=', 2)->count() }}
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Active
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Completed</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            {{ $lots->where('status', 2)->count() }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Finished
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form method="GET" action="{{ route('admin.ledger.lot.index') }}" class="m-0">
            <div class="row align-items-end">
                <div class="col-md-5 col-sm-12 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search text-muted mr-1"></i> Search Lot / Order SKU</label>
                    <input type="text" name="search" class="form-control erp-filter-input" placeholder="Search by Lot Number or Order SKU..." value="{{ $searchLot ?? request('search') }}">
                </div>
                <div class="col-md-3 col-sm-12 mb-1 d-flex">
                    <button type="submit" class="btn-erp btn-erp-primary mr-2" style="height: 31px;">
                        <i class="fas fa-filter mr-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('admin.ledger.lot.index') }}" class="btn-erp btn-erp-outline" style="height: 31px;" title="Reset filters">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 130px;">Lot No</th>
                        <th>Order SKU & Customer</th>
                        <th>Fabric & Garment Spec</th>
                        <th class="text-center" style="width: 140px;">Current Stage</th>
                        <th class="text-right" style="width: 140px;">Total Assigned (Pcs)</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowIdx = 0; @endphp
                    @forelse($lots as $lot)
                        @php $rowIdx++; @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $rowIdx }}</td>
                            <td>
                                <span class="badge erp-badge-yellow" style="font-size: 11.5px; font-weight: 700; padding: 3px 8px;">
                                    {{ $lot->lot_no }}
                                </span>
                            </td>
                            <td>
                                <div class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $lot->orderMain->sku ?? '-' }}</div>
                                <small class="text-muted"><i class="fas fa-user mr-1"></i> {{ $lot->orderMain->customer->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $lot->orderProductSet->fabric->name ?? '-' }}</div>
                                @if($lot->orderProductSet?->master_design_pattern)
                                    <small class="text-muted"><i class="fas fa-tag mr-1"></i> Pattern: {{ $lot->orderProductSet->master_design_pattern->name }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background: var(--erp-bg-header); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 7px;">
                                    {{ $lot->last_current_stage ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                {{ number_format($lot->lot_quantity, 0) }}
                            </td>
                            <td class="text-center">
                                @if($lot->status == 1)
                                    <span class="badge erp-badge-yellow" style="font-size: 10.5px; padding: 2px 7px;">Processing</span>
                                @elseif($lot->status == 2)
                                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 10.5px; padding: 2px 7px;">Completed</span>
                                @else
                                    <span class="badge badge-secondary" style="font-size: 10.5px; padding: 2px 7px;">Status {{ $lot->status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.ledger.lot.show', $lot->lot_no) }}" 
                                   class="erp-action-btn erp-btn-view" 
                                   title="View Lot Ledger">
                                    <i class="fas fa-book-open"></i>
                                </a>
                                <a href="{{ route('admin.ledger.lot.show', $lot->lot_no) }}" 
                                   class="btn-erp btn-erp-primary btn-xs ml-1" 
                                   style="height: 24px; font-size: 11px; padding: 2px 8px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-industry fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No production lots found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($lots->count() > 0)
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="5" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">PAGE SUB-TOTAL (ASSIGNED QUANTITY):</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($pageTotalAssigned ?? 0, 0) }} Pcs</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($lots instanceof \Illuminate\Pagination\LengthAwarePaginator && $lots->hasPages())
        <div class="d-flex justify-content-between align-items-center p-2 border-top flex-wrap" style="background: #fafafa;">
            <div class="text-muted small">
                Showing {{ $lots->firstItem() ?? 0 }} to {{ $lots->lastItem() ?? 0 }} of {{ $lots->total() }} production lots
            </div>
            <div>
                {{ $lots->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
