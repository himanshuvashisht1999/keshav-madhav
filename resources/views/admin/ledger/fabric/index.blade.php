@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Fabric Stock Ledger
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.fabric.export-list-pdf', request()->all()) }}" class="btn-erp btn-erp-outline" title="Export to PDF">
                <i class="fas fa-file-pdf text-danger"></i> Export PDF
            </a>
            <a href="{{ route('admin.ledger.fabric.export-list-excel', request()->all()) }}" class="btn-erp btn-erp-primary" title="Export to Excel">
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
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Fabrics</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalFabricsCount ?? 0) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Fabrics
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Inward</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($overallTotalInward ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Inward
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Outward</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            {{ number_format($overallTotalOutward ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Outward
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Balance Stock</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            {{ number_format($overallTotalBalance ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Stock
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form method="GET" action="{{ route('admin.ledger.fabric.index') }}" class="m-0">
            <div class="row align-items-end">
                <div class="col-md-6 col-sm-8 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Search Fabric</label>
                    <input type="text" name="search" class="form-control erp-input" placeholder="Search by fabric name..." value="{{ $search ?? '' }}">
                </div>

                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.ledger.fabric.index') }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Fabric Name</th>
                        <th class="text-right" style="width: 170px;">Total Inward (Mtrs)</th>
                        <th class="text-right" style="width: 170px;">Total Outward (Mtrs)</th>
                        <th class="text-right" style="width: 170px;">Current Balance (Mtrs)</th>
                        <th style="width: 110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowIdx = 0; @endphp
                    @forelse($fabrics as $index => $fabric)
                        @php $rowIdx++; @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $rowIdx }}</td>
                            <td class="font-weight-bold" style="color: var(--erp-text-heading);">
                                {{ $fabric->name }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                {{ number_format($fabric->total_inward ?? 0, 2) }}
                            </td>
                            <td class="text-right font-weight-bold text-danger">
                                {{ number_format($fabric->total_outward ?? 0, 2) }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-text-heading);">
                                {{ number_format($fabric->current_balance ?? 0, 2) }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.ledger.fabric.show', $fabric->id) }}" 
                                   class="erp-action-btn erp-btn-view" 
                                   title="View Fabric Ledger">
                                    <i class="fas fa-book-open"></i>
                                </a>
                                <a href="{{ route('admin.ledger.fabric.show', $fabric->id) }}" 
                                   class="btn-erp btn-erp-primary btn-xs ml-1" 
                                   style="height: 24px; font-size: 11px; padding: 2px 8px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-layer-group fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No fabric records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($fabrics->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right font-weight-bold text-muted" style="letter-spacing: 0.5px;">PAGE SUB-TOTAL:</td>
                        <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">{{ number_format($pageTotalInward ?? 0, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">{{ number_format($pageTotalOutward ?? 0, 2) }}</td>
                        <td class="text-right font-weight-bold" style="color: var(--erp-text-heading);">{{ number_format($pageTotalBalance ?? 0, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr class="erp-table-grand-total">
                        <td colspan="2" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">OVERALL TOTAL (ALL RECORDS):</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($overallTotalInward ?? 0, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">{{ number_format($overallTotalOutward ?? 0, 2) }}</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($overallTotalBalance ?? 0, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($fabrics instanceof \Illuminate\Pagination\LengthAwarePaginator && $fabrics->hasPages())
        <div class="d-flex justify-content-between align-items-center p-2 border-top flex-wrap" style="font-size: var(--erp-font-sm);">
            <div class="text-muted">
                Showing {{ $fabrics->firstItem() ?? 0 }} to {{ $fabrics->lastItem() ?? 0 }} of {{ $fabrics->total() }} fabrics
            </div>
            <div>
                {{ $fabrics->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
