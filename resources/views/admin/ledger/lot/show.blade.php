@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-industry text-primary"></i> Lot Movement Ledger: {{ $lot->lot_no }}
            </div>
            <div style="font-size: 11.5px; color: var(--erp-text-muted);">
                Order SKU: <strong style="color: var(--erp-text-heading);">{{ $lot->orderMain->sku ?? '-' }}</strong> | 
                Customer: <strong style="color: var(--erp-text-heading);">{{ $lot->orderMain->customer->name ?? '-' }}</strong> | 
                Fabric: <strong style="color: var(--erp-text-heading);">{{ $lot->orderProductSet->fabric->name ?? '-' }}</strong>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.lot.export-pdf', $lot->lot_no) }}" class="btn-erp btn-erp-outline" title="Export Statement to PDF">
                <i class="fas fa-file-pdf text-danger"></i> Export PDF
            </a>
            <a href="{{ route('admin.ledger.lot.export-excel', $lot->lot_no) }}" class="btn-erp btn-erp-primary" title="Export Statement to Excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.ledger.lot.index') }}" class="btn-erp btn-erp-outline" title="Back to Lots">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip (Exact Fabric Module Standard) -->
    <div class="row mb-2">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-cut"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Initial Assigned (Cut)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($initialQty ?? 0, 0) }} <small style="font-size: 11px;">Pcs</small>
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
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Packed (Finished Goods)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            {{ number_format($packedQtySum ?? 0, 0) }} <small style="font-size: 11px;">Pcs</small>
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Packed
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">WIP Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            {{ number_format($wipBalance ?? 0, 0) }} <small style="font-size: 11px;">Pcs</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Balance
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-step-forward"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Current Stage</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-md); color: var(--erp-text-heading); line-height: 1.2;">
                            {{ $lot->last_current_stage ?? 'N/A' }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-bg-header); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Stage
                </span>
            </div>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 150px;">Date & Time</th>
                        <th style="width: 120px;" class="text-center">Movement Type</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th>Transaction Particulars / Stage Flow</th>
                        <th class="text-right" style="width: 140px;">Quantity (Pcs)</th>
                        <th class="text-right" style="width: 140px;">WIP Balance (Pcs)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $index => $tx)
                        @php
                            $rowQty = 0;
                            if ($tx->type === 'Inward') {
                                $rowQty = (float)($tx->inward ?? 0);
                            } elseif ($tx->type === 'Outward') {
                                $rowQty = (float)($tx->outward ?? 0);
                            } else {
                                $rowQty = (float)($tx->process_qty ?? 0);
                            }
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                            <td class="font-weight-bold text-nowrap">
                                {{ \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A') }}
                            </td>
                            <td class="text-center">
                                @if($tx->type == 'Inward')
                                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 7px;">
                                        <i class="fas fa-arrow-down mr-1"></i> Inward (Cut)
                                    </span>
                                @elseif($tx->type == 'Outward')
                                    <span class="badge erp-badge-yellow" style="font-size: 11px; padding: 3px 7px;">
                                        <i class="fas fa-arrow-up mr-1"></i> Outward (Packed)
                                    </span>
                                @else
                                    <span class="badge badge-secondary" style="font-size: 11px; padding: 3px 7px;">
                                        <i class="fas fa-random mr-1"></i> Transfer
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($tx->status))
                                    @if($tx->status == 'completed')
                                        <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 10.5px; padding: 2px 7px;">Completed</span>
                                    @elseif($tx->status == 'progress')
                                        <span class="badge erp-badge-yellow" style="font-size: 10.5px; padding: 2px 7px;">In Progress</span>
                                    @else
                                        <span class="badge badge-secondary" style="font-size: 10.5px; padding: 2px 7px;">{{ ucfirst($tx->status) }}</span>
                                    @endif
                                @else
                                    <span class="badge badge-light border text-muted" style="font-size: 10.5px; padding: 2px 7px;">Recorded</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $tx->particulars }}</span>
                            </td>
                            <td class="text-right font-weight-bold" style="color: {{ $tx->type == 'Inward' ? 'var(--erp-green-primary)' : ($tx->type == 'Outward' ? '#dc2626' : 'var(--erp-text-heading)') }};">
                                {{ number_format($rowQty, 0) }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                {{ number_format($tx->running_balance ?? 0, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No transaction movements recorded for this production lot.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($transactions->count() > 0)
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="5" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">CURRENT WIP BALANCE REMAINING IN LOT:</td>
                        <td class="text-right font-weight-bold text-danger">Packed: {{ number_format($packedQtySum ?? 0, 0) }}</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($wipBalance ?? 0, 0) }} Pcs</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
